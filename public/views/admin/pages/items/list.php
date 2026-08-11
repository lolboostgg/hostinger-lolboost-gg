<?php
$data = $data ?? [];
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Items | Admin Area']]) ?>

<?php echo $this->start('styles'); ?>
<style>
.al-page .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.al-page .card::before { display:none!important; }
.al-pills { display:flex;gap:6px;flex-wrap:wrap; }
.al-type-pills { display:flex;gap:6px;flex-wrap:wrap; }
.al-type-divider { width:1px;height:22px;background:rgba(255,255,255,.08);margin:0 2px; }
.al-pill[data-type].active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-search-wrap { position:relative; }
.al-search-wrap input { background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s; }
.al-search-wrap input:focus { border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important; }
.al-search-wrap input::placeholder { color:rgba(255,255,255,.25)!important; }
.al-search-icon { position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none; }
.al-pill { display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none; }
.al-pill:hover { background:rgba(255,255,255,.08);color:rgba(255,255,255,.85); }
.al-pill.active { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pill[data-status="Active"].active { background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80; }
.al-pill[data-status="Hidden"].active { background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15; }
.al-table-wrap { border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);position:relative; }
.al-table { width:100%; border-collapse:collapse; border-radius:20px; overflow:hidden; display:table; }
.al-table thead tr { background:rgba(255,255,255,.03); border-bottom:1px solid rgba(255,255,255,.06); }
.al-table thead th { padding:11px 16px; font-size:.68rem; font-weight:900; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; user-select:none; }
.al-table thead th.sortable { cursor:pointer; }
.al-table thead th.sortable:hover { color:rgba(255,255,255,.7); }
.al-table thead th .sort-icon { margin-left:4px; opacity:.35; font-size:.6rem; }
.al-table thead th.sort-asc .sort-icon, .al-table thead th.sort-desc .sort-icon { opacity:1;color:#c4b5fd; }
.al-table tbody .al-row { border-bottom:1px solid rgba(255,255,255,.04); transition:background .12s; }
.al-table tbody .al-row:last-child { border-bottom:none; }
.al-table tbody .al-row:hover { background:rgba(109,92,255,.08); }
.al-table tbody td { padding:13px 16px; vertical-align:middle; font-size:.85rem; color:rgba(255,255,255,.8); }
.al-col-id { font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums; }
.al-listing-wrap { display:flex;align-items:center;gap:11px; }
.al-listing-img { width:42px;height:42px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0; }
.al-listing-name { font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2; }
.al-listing-sub { font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px; }
.al-col-price { font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums; }
.al-badge { display:inline-flex;align-items:center;gap:.3rem; padding:4px 10px;border-radius:99px; font-size:.71rem;font-weight:800;white-space:nowrap; }
.al-badge--active { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80; }
.al-badge--hidden { background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15; }
.al-badge--sold { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185; }
.al-hero { border-radius:20px; border:1px solid rgba(255,255,255,.07); background:#25282a; padding:20px 24px; display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px; margin-bottom:14px; box-shadow:0 2px 20px rgba(0,0,0,.22); }
.al-hero-left { display:flex;align-items:center;gap:14px; }
.al-hero-icon { width:44px;height:44px;border-radius:13px; background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15)); border:1px solid rgba(109,92,255,.25); display:flex;align-items:center;justify-content:center; font-size:1.1rem;color:#c4b5fd;flex-shrink:0; }
.al-hero-title { font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0; }
.al-hero-sub { font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0; }
.al-toolbar-card { border-radius:16px; border:1px solid rgba(255,255,255,.07); background:#25282a; padding:12px 16px; display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px; margin-bottom:16px; box-shadow:0 2px 16px rgba(0,0,0,.18); }
.al-empty { text-align:center;padding:64px 24px;color:rgba(255,255,255,.35); }
.al-pg-btn { width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s; }
.al-pg-btn:hover:not(:disabled) { background:rgba(255,255,255,.09); }
.al-pg-btn.al-pg-active { background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd; }
.al-pg-btn:disabled { opacity:.35;cursor:not-allowed; }
.al-seller-link { color:#c4b5fd;text-decoration:none;font-weight:700; }
.al-seller-link:hover { color:#fff;text-decoration:underline; }
.al-chk { appearance:none;-webkit-appearance:none;width:17px;height:17px;border-radius:5px;border:1.5px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);cursor:pointer;flex-shrink:0;position:relative;transition:background .12s,border-color .12s;display:inline-block;vertical-align:middle; }
.al-chk:hover { border-color:rgba(109,92,255,.6);background:rgba(109,92,255,.12); }
.al-chk:checked { background:#6d5cff;border-color:#6d5cff; }
.al-chk:checked::after { content:'';position:absolute;left:4px;top:1.5px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg); }
.al-chk:indeterminate { background:rgba(109,92,255,.4);border-color:rgba(109,92,255,.7); }
.al-chk:indeterminate::after { content:'';position:absolute;left:3px;top:6.5px;width:9px;height:2px;background:#fff;border-radius:1px; }
.al-chk:disabled { opacity:.3;cursor:not-allowed; }
.al-actions-wrap { position:relative;display:inline-block; }
.al-actions-btn { width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .12s,color .12s; }
.al-actions-btn:hover { background:rgba(255,255,255,.09);color:rgba(255,255,255,.9); }
.al-actions-btn.is-open { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.4);color:#c4b5fd; }
.al-actions-menu { display:none;position:fixed;min-width:190px;z-index:9999;background:#2a2d35;border:1px solid rgba(255,255,255,.1);border-radius:13px;padding:5px;box-shadow:0 8px 32px rgba(0,0,0,.6);animation:alMenuIn .12s ease; }
.al-actions-menu.is-open { display:block; }
@keyframes alMenuIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }
.al-action-item { display:flex;align-items:center;gap:9px;width:100%;padding:8px 11px;border-radius:8px;font-size:.8rem;font-weight:700;color:rgba(255,255,255,.72);background:none;border:none;cursor:pointer;text-decoration:none;text-align:left;transition:background .1s,color .1s; }
.al-action-item:hover { background:rgba(255,255,255,.06);color:#fff; }
.al-action-item i { width:14px;text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;flex-shrink:0; }
.al-action-item:hover i { color:rgba(255,255,255,.6); }
.al-action-danger { color:#fb7185 !important; }
.al-action-danger:hover { background:rgba(251,113,133,.08) !important; }
.al-action-danger i { color:#fb7185 !important; }
.al-action-divider { height:1px;background:rgba(255,255,255,.06);margin:4px 0; }
.item-canvas.custom-offcanvas{ width:50vw !important; display:flex !important; flex-direction:column !important; height:100% !important; }
.item-canvas .offcanvas-header{flex-shrink:0;padding:18px 22px;border-bottom:1px solid var(--bs-card-border-color);}
.item-canvas .oc-steps{ flex-shrink:0; display:flex; align-items:center; padding:14px 22px; border-bottom:1px solid var(--bs-card-border-color); gap:0; }
.item-canvas .oc-step{ display:flex; align-items:center; gap:8px; flex:1; }
.item-canvas .oc-step-num{ width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:900; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.5); }
.item-canvas .oc-step.active .oc-step-num{ background:linear-gradient(135deg,#6d5cff,#b05cff); border-color:transparent; color:#fff; }
.item-canvas .oc-step.done .oc-step-num{ background:rgba(74,222,128,.15); border-color:rgba(74,222,128,.3); color:#4ade80; }
.item-canvas .oc-step-label{ font-size:.8rem; font-weight:700; color:rgba(255,255,255,.4); }
.item-canvas .oc-step.active .oc-step-label{color:#c4b5fd;font-weight:900;}
.item-canvas .oc-step.done .oc-step-label{color:rgba(255,255,255,.6);}
.item-canvas .oc-step-line{ flex:1; height:1px; background:rgba(255,255,255,.08); margin:0 8px; }
.item-canvas .oc-step-line.done{background:rgba(74,222,128,.3);}
.item-canvas .offcanvas-body{ flex:1 !important; overflow:hidden !important; display:flex !important; flex-direction:column !important; padding:0 !important; }
.item-canvas .offcanvas-body > form{ flex:1; display:flex; flex-direction:column; overflow:hidden; min-height:0; }
.item-canvas .oc-scroll{ flex:1; overflow-y:auto; padding:18px 22px; }
.item-canvas .oc-footer{ flex-shrink:0; display:flex; align-items:center; justify-content:space-between; padding:12px 22px; border-top:1px solid var(--bs-card-border-color); background:var(--bs-offcanvas-bg, #1e2028); }
.item-canvas .oc-btn-next{ display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#6d5cff,#b05cff); border:none; border-radius:11px; padding:8px 20px; font-size:.87rem; font-weight:900; color:#fff; cursor:pointer; }
.item-canvas .oc-btn-prev{ display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10); border-radius:11px; padding:8px 16px; font-size:.87rem; font-weight:700; color:rgba(255,255,255,.65); cursor:pointer; }
.item-canvas .oc-section-label{ display:flex; align-items:center; gap:6px; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.09em; color:rgba(255,255,255,.3); margin:14px 0 8px; padding-bottom:6px; border-bottom:1px solid rgba(255,255,255,.06); }
.item-canvas .oc-required{color:#f87171;font-size:.75rem;vertical-align:super;}
.item-canvas .account-upload-box{ border:2px dashed rgba(255,255,255,.12); border-radius:12px; background:rgba(255,255,255,.02); cursor:pointer; }
.item-canvas .account-upload-box.dragover{ border-color:#6366f1; background:rgba(99,102,241,.08); }
.item-canvas .gallery-preview-tile{ position:relative; overflow:hidden; border-radius:.5rem; background:rgba(255,255,255,.02); cursor:grab; }
.item-canvas .gallery-preview-tile img{ width:100%!important; height:150px!important; object-fit:cover; display:block; }
.item-canvas .gallery-preview-tile.is-main{ outline:2px solid rgba(99,102,241,.9); outline-offset:2px; }
.item-canvas .gallery-preview-badge{ position:absolute; top:.5rem; left:.5rem; padding:.25rem .5rem; border-radius:999px; background:rgba(99,102,241,.95); color:#fff; font-size:.75rem; font-weight:600; z-index:2; }
.item-canvas .gallery-preview-hint{ position:absolute; bottom:.5rem; left:.5rem; right:.5rem; padding:.25rem .5rem; border-radius:.5rem; background:rgba(0,0,0,.35); color:rgba(255,255,255,.9); font-size:.75rem; z-index:2; }
.item-canvas .gallery-preview-overlay{ position:absolute; inset:0; background-color:rgba(220,53,69,.30); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .25s ease; }
.item-canvas .gallery-preview-tile:hover .gallery-preview-overlay{ opacity:1; }
.item-canvas .gallery-preview-remove{ border:0; background:rgba(220,53,69,.95); color:#fff; width:44px; height:44px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; }
.item-canvas .is-invalid,.item-canvas .form-control.is-invalid,.item-canvas .form-select.is-invalid{ border-color:#dc3545 !important; box-shadow:0 0 0 0.2rem rgba(220,53,69,.12) !important; }
.item-canvas .invalid-feedback{ display:none; color:#ff8b95; font-size:.75rem; margin-top:6px; }
.item-canvas .is-invalid + .invalid-feedback,.item-canvas .form-control.is-invalid ~ .invalid-feedback,.item-canvas .form-select.is-invalid ~ .invalid-feedback{ display:block; }
@media only screen and (max-width:576px){ .item-canvas.custom-offcanvas{width:100vw!important} }
@media only screen and (max-width:1200px){ .al-table-wrap{overflow-x:auto}.al-table{min-width:900px} }
</style>
<?php echo $this->end(); ?>

<?php
function admin_item_type_label($type) {
    $map = [
        'skins' => 'Skins', 'skin' => 'Skins',
        'chests-keys' => 'Chests & Keys', 'chest-key' => 'Chests & Keys',
        'orbs' => 'Orbs', 'orb' => 'Orbs',
        'capsules' => 'Capsules', 'capsule' => 'Capsules',
        'event-pass' => 'Event Pass', 'pass' => 'Event Pass',
        'bundles' => 'Bundles', 'bundle' => 'Bundles',
        'tft-item' => 'TFT Item', 'tft' => 'TFT Item',
    ];
    return $map[$type] ?? ucwords(str_replace(['_', '-'], ' ', (string)$type));
}
?>

<div class="al-page">
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-gift"></i></div>
      <div>
        <h2 class="al-hero-title">Items</h2>
        <p class="al-hero-sub"><?= count($data) ?> item<?= count($data) !== 1 ? 's' : '' ?> total</p>
      </div>
    </div>
  </div>

  <div class="al-toolbar-card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1;">
      <div class="al-pills" id="alStatusFilters">
        <span class="al-pill active" data-status="all">All</span>
        <span class="al-pill" data-status="Active">Active</span>
        <span class="al-pill" data-status="Hidden">Hidden</span>
      </div>

      <?php
      $typeFilterMap = [];
      foreach ($data as $typeFilterItem) {
          $typeKeyRaw = (string)($typeFilterItem['type'] ?? '');
          if ($typeKeyRaw === '') continue;
          $typeLabel = admin_item_type_label($typeKeyRaw);
          $typeKey = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $typeLabel), '-'));
          if ($typeKey === '') continue;
          $typeFilterMap[$typeKey] = $typeLabel;
      }
      asort($typeFilterMap);
      ?>
      <?php if (!empty($typeFilterMap)): ?>
      <div class="al-type-divider"></div>
      <div class="al-type-pills" id="alTypeFilters">
        <?php foreach ($typeFilterMap as $typeKey => $typeLabel): ?>
          <span class="al-pill" data-type="<?= htmlspecialchars($typeKey) ?>"><?= htmlspecialchars($typeLabel) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <button type="button" id="alBulkDeleteBtn" style="display:none;align-items:center;gap:.4rem;padding:6px 14px;border-radius:10px;background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;font-size:.8rem;font-weight:800;cursor:pointer;transition:background .12s;">
        <i class="fa-solid fa-trash"></i> Delete selected (<span id="alBulkCount">0</span>)
      </button>
      <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="alSearch" placeholder="Search items…">
      </div>
    </div>
  </div>

  <div class="al-table-wrap" id="alTableWrap">
    <table class="al-table" id="alGrid">
      <thead>
        <tr>
          <th style="width:36px;padding:10px 8px;"><input type="checkbox" id="alChkAll" class="al-chk" aria-label="Select all"></th>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Item</th>
          <th>Seller</th>
          <th>Type</th>
          <th>Server</th>
          <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="stock">Stock <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Status</th>
          <th class="sortable" data-col="date">Created <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="alTbody">
        <?php if (!empty($data)): foreach ($data as $it):
          $images = json_decode((string)($it['images'] ?? '[]'), true); if (!is_array($images)) $images = [];
          $cover = $images[0] ?? (defined('ASSET_URL') ? ASSET_URL . '/public/uploads/icons/default2.png' : '');
          $priceRaw = ((int)($it['price'] ?? 0)) / 100;
          $soldCount = (int)($it['sold_count'] ?? 0);
          $activeState = (int)($it['active'] ?? 1) === 1;
          $status = $soldCount > 0 ? 'Sold' : ($activeState ? 'Active' : 'Hidden');
          $canDelete = $soldCount === 0;
          $createdAtTs = !empty($it['created_at']) ? strtotime((string)$it['created_at']) : 0;
          $createdAtFmt = $createdAtTs ? date('d.m.Y', $createdAtTs) : '—';
        ?>
        <tr class="al-row"
            data-status="<?= htmlspecialchars($status) ?>"
            data-type="<?= htmlspecialchars(strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', admin_item_type_label($it['type'] ?? '')), '-'))) ?>"
            data-search="<?= htmlspecialchars(strtolower(($it['title'] ?? '') . ' ' . ($it['seller_username'] ?? '') . ' ' . ($it['type'] ?? '') . ' ' . admin_item_type_label($it['type'] ?? '') . ' ' . ($it['server'] ?? ''))) ?>"
            data-id="<?= (int)$it['id'] ?>"
            data-price="<?= $priceRaw ?>"
            data-stock="<?= (int)($it['stock'] ?? 1) ?>"
            data-date="<?= $createdAtTs ?>">
          <td style="padding:10px 8px;vertical-align:middle;">
            <input type="checkbox" class="al-row-chk al-chk" value="<?= (int)$it['id'] ?>" <?= $canDelete ? '' : 'disabled title="Sold items cannot be deleted"' ?>>
          </td>
          <td><span class="al-col-id">#<?= (int)$it['id'] ?></span></td>
          <td>
            <div class="al-listing-wrap">
              <img class="al-listing-img" src="<?= htmlspecialchars($cover) ?>" alt="">
              <div>
                <div class="al-listing-name"><?= htmlspecialchars($it['title'] ?? 'Untitled Item') ?></div>
                <div class="al-listing-sub"><?= htmlspecialchars(admin_item_type_label($it['type'] ?? '')) ?></div>
              </div>
            </div>
          </td>
          <td>
            <?php if (!empty($it['seller_id'])): ?>
              <a class="al-seller-link" href="<?= BASE_URL ?>/admin-area/seller/<?= (int)$it['seller_id'] ?>" onclick="event.stopPropagation()">
                <?= htmlspecialchars($it['seller_username'] ?? 'Unknown') ?>
              </a>
            <?php else: ?>
              <?= htmlspecialchars($it['seller_username'] ?? 'Unknown') ?>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars(admin_item_type_label($it['type'] ?? '')) ?></td>
          <td><?= htmlspecialchars(strtoupper($it['server'] ?? 'EUW')) ?></td>
          <td><span class="al-col-price">€<?= number_format($priceRaw, 2) ?></span></td>
          <td><?= (int)($it['stock'] ?? 1) ?></td>
          <td>
            <span class="al-badge <?= $status === 'Sold' ? 'al-badge--sold' : ($status === 'Active' ? 'al-badge--active' : 'al-badge--hidden') ?>">
              <?= $status ?>
            </span>
          </td>
          <td><?= $createdAtFmt ?></td>
          <td class="text-end">
            <div class="al-actions-wrap">
              <button type="button" class="al-actions-btn" onclick="event.stopPropagation();alToggleMenu(this)" title="Actions"><i class="fa-solid fa-ellipsis"></i></button>
              <div class="al-actions-menu">
                <button type="button" class="al-action-item" data-bs-toggle="offcanvas" data-bs-target="#editItemCanvas<?= (int)$it['id'] ?>"><i class="fa-solid fa-pen"></i> Edit Item</button>
                <?php if ($status !== 'Sold'): ?>
                <button type="button" class="al-action-item js-toggle-item" data-id="<?= (int)$it['id'] ?>" data-active="<?= $activeState ? '1' : '0' ?>">
                  <?= $activeState ? '<i class="fa-solid fa-eye-slash"></i> Hide Item' : '<i class="fa-solid fa-eye"></i> Activate Item' ?>
                </button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <div class="al-action-divider"></div>
                <button type="button" class="al-action-item al-action-danger js-delete-item" data-id="<?= (int)$it['id'] ?>"><i class="fa-solid fa-trash"></i> Delete Item</button>
                <?php endif; ?>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="11"><div class="al-empty">No items found.</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">Showing <span id="alShowing">—</span> of <span id="alTotal">—</span></div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
  </div>
</div>

<?php if (!empty($data)): foreach ($data as $canvasItem):
$itemId = (int)$canvasItem['id'];
$title = $canvasItem['title'] ?? '';
$description = $canvasItem['description'] ?? '';
$type = $canvasItem['type'] ?? 'skins';
$server = $canvasItem['server'] ?? 'EUW';
$priceCents = (int)($canvasItem['price'] ?? 0);
$priceEur = number_format($priceCents / 100, 2, '.', '');
$stock = (int)($canvasItem['stock'] ?? 999);
$minQty = (int)($canvasItem['min_purchase_qty'] ?? 1);
$friendshipDays = (int)($canvasItem['requires_friendship_days'] ?? 7);
$itemImages = [];
try { $itemImages = json_decode($canvasItem['images'] ?? '[]', true) ?: []; } catch (Throwable $e) { $itemImages = []; }
$canvasId = 'editItemCanvas' . $itemId;
$fileInputId = 'galleryUploadItem' . $itemId;
$dropzoneId = 'galleryDropzoneItem' . $itemId;
$previewId = 'previewGalleryItem' . $itemId;
$orderInputId = 'imagesOrderInputItem' . $itemId;
$existingInputId = 'existingImagesJsonItem' . $itemId;
$priceInputId = 'itemPriceInput' . $itemId;
$formId = 'itemForm' . $itemId;
if (!function_exists('admin_item_safe_json')) {
    function admin_item_safe_json($value) {
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="offcanvas offcanvas-end custom-offcanvas item-canvas" tabindex="-1" id="<?= $canvasId ?>" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="offcanvas-header"><div><h5 class="offcanvas-title mb-0">Edit Item #<?= $itemId ?></h5><div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:2px;"><?= htmlspecialchars($title) ?></div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
  <div class="oc-steps">
    <div class="oc-step active" id="<?= $canvasId ?>Step1"><div class="oc-step-num">1</div><div class="oc-step-label">Listing Info</div></div>
    <div class="oc-step-line" id="<?= $canvasId ?>Line1"></div>
    <div class="oc-step" id="<?= $canvasId ?>Step2"><div class="oc-step-num">2</div><div class="oc-step-label">Stock & Delivery</div></div>
    <div class="oc-step-line" id="<?= $canvasId ?>Line2"></div>
    <div class="oc-step" id="<?= $canvasId ?>Step3"><div class="oc-step-num">3</div><div class="oc-step-label">Images</div></div>
  </div>
  <div class="offcanvas-body">
    <form action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" class="ajax-form" id="<?= $formId ?>" novalidate>
      <input type="hidden" name="action" value="admin_update_item">
      <input type="hidden" name="id" value="<?= $itemId ?>">
      <input type="hidden" name="images_order" id="<?= $orderInputId ?>" value='<?= admin_item_safe_json($itemImages) ?>'>
      <input type="hidden" name="existing_images_json" id="<?= $existingInputId ?>" value='<?= admin_item_safe_json($itemImages) ?>'>
      <div class="oc-scroll">
        <div class="js-item-step active" data-step="1">
          <div class="oc-section-label">Basic Info</div>
          <div class="row g-2 mb-3">
            <div class="col-12"><label class="form-label">Item Title <span class="oc-required">*</span></label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars($title) ?>" required><div class="invalid-feedback">Please enter an item title.</div></div>
            <div class="col-md-6"><label class="form-label">Item Type <span class="oc-required">*</span></label><select class="form-select" name="type" required><option value="">Choose item type</option>
              <option value="skins" <?= $type === 'skins' ? 'selected' : '' ?>>Skins</option>
              <option value="chests-keys" <?= $type === 'chests-keys' ? 'selected' : '' ?>>Chests & Keys</option>
              <option value="orbs" <?= $type === 'orbs' ? 'selected' : '' ?>>Orbs</option>
              <option value="capsules" <?= $type === 'capsules' ? 'selected' : '' ?>>Capsules</option>
              <option value="event-pass" <?= $type === 'event-pass' ? 'selected' : '' ?>>Event Pass</option>
              <option value="bundles" <?= $type === 'bundles' ? 'selected' : '' ?>>Bundles</option>
              <option value="tft-item" <?= $type === 'tft-item' ? 'selected' : '' ?>>TFT Item</option>
            </select><div class="invalid-feedback">Please choose an item type.</div></div>
            <div class="col-md-6"><label class="form-label">Server <span class="oc-required">*</span></label><select class="form-select" name="server" required><option value="">Choose server</option><?php foreach (['EUW','EUNE','NA','TR','BR','LAS','LAN','RU','OCE'] as $srv): ?><option value="<?= $srv ?>" <?= $server === $srv ? 'selected' : '' ?>><?= $srv ?></option><?php endforeach; ?></select><div class="invalid-feedback">Please choose a server.</div></div>
            <div class="col-12"><label class="form-label">Description <span class="oc-required">*</span></label><textarea class="form-control" rows="5" name="description" required><?= htmlspecialchars($description) ?></textarea><div class="invalid-feedback">Please enter a description.</div></div>
          </div>
        </div>
        <div class="js-item-step" data-step="2" style="display:none;">
          <div class="oc-section-label">Pricing & Stock</div>
          <div class="row g-2 mb-3">
            <div class="col-md-6"><label class="form-label">Price per unit <span class="oc-required">*</span></label><div class="input-group"><span class="input-group-text">€</span><input type="text" class="form-control" id="<?= $priceInputId ?>" name="price" value="<?= htmlspecialchars($priceEur) ?>" required><span class="input-group-text">EUR</span></div><div class="invalid-feedback">Please enter a valid price.</div></div>
            <div class="col-md-6"><label class="form-label">Stock <span class="oc-required">*</span></label><input type="number" class="form-control" name="stock" min="1" step="1" value="<?= $stock ?>" required><div class="invalid-feedback">Please enter stock.</div></div>
            <div class="col-md-6"><label class="form-label">Minimum quantity per order <span class="oc-required">*</span></label><input type="number" class="form-control" name="min_purchase_qty" min="1" step="1" value="<?= $minQty ?>" required><div class="invalid-feedback">Please enter a minimum quantity.</div></div>
            <div class="col-md-6"><label class="form-label">Delivery time in days <span class="oc-required">*</span></label><input type="number" class="form-control" name="requires_friendship_days" min="0" step="1" value="<?= $friendshipDays ?: 7 ?>" required><div class="invalid-feedback">Please enter delivery time in days.</div></div>
          </div>
        </div>
        <div class="js-item-step" data-step="3" style="display:none;">
          <div class="oc-section-label">Delivery Instructions</div>
          <div class="row g-2 mb-3">
            <div class="col-12"><label class="form-label">Delivery instructions for buyer email</label><textarea class="form-control" rows="4" name="delivery_instructions" placeholder="Instructions sent to buyer after purchase."><?= htmlspecialchars((string)($canvasItem['delivery_instructions'] ?? '')) ?></textarea></div>
          </div>
          <div class="oc-section-label">Image Gallery</div>
          <div id="<?= $dropzoneId ?>" class="account-upload-box text-center p-3">
            <div class="mb-2"><i class="fa-duotone fa-images fa-xl text-primary"></i></div>
            <h6 class="mb-1" style="font-size:.88rem;font-weight:800;">Upload Item Images</h6>
            <p class="text-muted small mb-2" style="font-size:.78rem;">Click, drag & drop, or paste with <strong>Ctrl+V</strong></p>
            <button type="button" class="btn btn-primary btn-sm" id="<?= $fileInputId ?>Btn">Select Images</button>
            <input class="form-control d-none" name="images[]" type="file" id="<?= $fileInputId ?>" multiple accept="image/*">
          </div>
          <div id="<?= $previewId ?>" class="row mt-3 g-2"></div>
        </div>
      </div>
      <div class="oc-footer">
        <button type="button" class="oc-btn-prev js-item-prev" style="display:none;">Previous</button>
        <div class="ms-auto d-flex gap-2"><button type="button" class="oc-btn-next js-item-next">Next</button><button type="submit" class="oc-btn-next js-item-submit" style="display:none;">Save Item</button></div>
      </div>
    </form>
  </div>
</div>
<?php endforeach; endif; ?>

<?php echo $this->start('scripts'); ?>
<script>
// ── Canvas init ───────────────────────────────────────────────
document.querySelectorAll('.item-canvas').forEach(function(canvas){
  if (canvas.dataset.itemCanvasInit === '1') return;
  canvas.dataset.itemCanvasInit = '1';
  var form = canvas.querySelector('form');
  if (!form) return;
  var steps = Array.from(canvas.querySelectorAll('.js-item-step'));
  var prevBtn = canvas.querySelector('.js-item-prev');
  var nextBtn = canvas.querySelector('.js-item-next');
  var submitBtn = canvas.querySelector('.js-item-submit');
  var scrollArea = canvas.querySelector('.oc-scroll');
  var currentStep = 1;
  function markStepState(){ for (var i=1;i<=3;i++){ var stepEl=canvas.querySelector('#'+canvas.id+'Step'+i); if(!stepEl) continue; stepEl.classList.remove('active','done'); var num=stepEl.querySelector('.oc-step-num'); if(i<currentStep){stepEl.classList.add('done');if(num)num.innerHTML='<i class="fa-solid fa-check" style="font-size:.7rem;"></i>';}else if(i===currentStep){stepEl.classList.add('active');if(num)num.textContent=i;}else{if(num)num.textContent=i;}} var l1=canvas.querySelector('#'+canvas.id+'Line1'); var l2=canvas.querySelector('#'+canvas.id+'Line2'); if(l1)l1.classList.toggle('done',currentStep>1); if(l2)l2.classList.toggle('done',currentStep>2); }
  function showStep(n){ currentStep=n; steps.forEach(function(s){ s.style.display=parseInt(s.dataset.step,10)===n?'':'none'; }); if(prevBtn)prevBtn.style.display=n>1?'':'none'; if(nextBtn)nextBtn.style.display=n<3?'':'none'; if(submitBtn)submitBtn.style.display=n===3?'':'none'; markStepState(); if(scrollArea)scrollArea.scrollTop=0; }
  function validateStep(stepNumber){ var step=steps.find(function(s){return parseInt(s.dataset.step,10)===stepNumber;}); if(!step) return true; var fields=Array.from(step.querySelectorAll('input,select,textarea')).filter(function(el){return el.type!=='hidden'&&!el.disabled&&el.required;}); fields.forEach(function(el){ el.classList.remove('is-invalid'); if(el.name==='price'){var raw=String(el.value||'').replace(',','.').trim();var num=parseFloat(raw);if(!raw||isNaN(num)||num<=0){el.setCustomValidity('Please enter a valid price.');}else{el.setCustomValidity('');}}else if(el.name==='stock'||el.name==='min_purchase_qty'||el.name==='requires_friendship_days'){el.setCustomValidity(String(el.value||'').trim()?'':'This field is required.');}else{el.setCustomValidity(el.value?'':'This field is required.');}}); for(var i=0;i<fields.length;i++){var el=fields[i];if(!el.checkValidity()){el.classList.add('is-invalid');if(typeof el.reportValidity==='function')el.reportValidity();try{el.focus({preventScroll:false});}catch(e){el.focus();}return false;}} return true; }
  if(nextBtn) nextBtn.addEventListener('click',function(){ if(validateStep(currentStep)) showStep(Math.min(3,currentStep+1)); });
  if(prevBtn) prevBtn.addEventListener('click',function(){ showStep(Math.max(1,currentStep-1)); });
  form.querySelectorAll('input,select,textarea').forEach(function(el){ el.addEventListener('input',function(){el.classList.remove('is-invalid');el.setCustomValidity('');}); el.addEventListener('change',function(){el.classList.remove('is-invalid');el.setCustomValidity('');}); });
  var fileInput=form.querySelector('input[type="file"]');
  var fileBtn=canvas.querySelector('[id$="Btn"]');
  var dropzone=canvas.querySelector('.account-upload-box');
  var preview=canvas.querySelector('[id^="previewGalleryItem"]');
  var orderInput=form.querySelector('input[name="images_order"]');
  var existingInput=form.querySelector('input[name="existing_images_json"]');
  var galleryItems=[]; var dragFromIndex=null; var tempSeq=0;
  function parseExistingImages(raw){ try{var p=JSON.parse(raw||'[]');return Array.isArray(p)?p:[];}catch(e){return[];} }
  function fileKey(f){ return f.name+'__'+f.size+'__'+f.lastModified; }
  function escHtml(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
  function syncInputAndRender(){ if(!preview) return; var dt=new DataTransfer(); galleryItems.filter(function(x){return x.type==='new'&&x.file;}).forEach(function(x){dt.items.add(x.file);}); if(fileInput)fileInput.files=dt.files; if(orderInput)orderInput.value=JSON.stringify(galleryItems.map(function(x){return x.type==='existing'?x.url:x.tempId;})); if(existingInput)existingInput.value=JSON.stringify(galleryItems.filter(function(x){return x.type==='existing';}).map(function(x){return x.url;})); preview.innerHTML=''; galleryItems.forEach(function(item,i){ var src=item.type==='existing'?item.url:URL.createObjectURL(item.file); var col=document.createElement('div'); col.className='col-6 col-md-3'; col.innerHTML='<div class="gallery-preview-tile '+(i===0?'is-main':'')+'" draggable="true" data-index="'+i+'">'+'<div class="gallery-preview-badge" style="left:auto;right:.5rem;background:rgba(0,0,0,.45)">#'+(i+1)+'</div>'+(i===0?'<div class="gallery-preview-badge">MAIN</div>':'')+'<img src="'+escHtml(src)+'" alt="Preview">'+'<div class="gallery-preview-hint">Drag to reorder</div>'+'<div class="gallery-preview-overlay"><button type="button" class="gallery-preview-remove" data-remove-index="'+i+'">×</button></div>'+'</div>'; preview.appendChild(col); }); }
  function addFiles(files){ var incoming=Array.from(files||[]).filter(function(f){return f&&f.type&&f.type.indexOf('image/')===0;}); var existing=new Set(galleryItems.filter(function(x){return x.type==='new';}).map(function(x){return fileKey(x.file);})); incoming.forEach(function(f){var k=fileKey(f);if(!existing.has(k)){galleryItems.push({type:'new',file:f,tempId:'__new__'+(tempSeq++)});existing.add(k);}}); syncInputAndRender(); }
  if(fileInput) fileInput.addEventListener('change',function(){addFiles(fileInput.files);});
  if(fileBtn) fileBtn.addEventListener('click',function(e){e.preventDefault();if(fileInput)fileInput.click();});
  if(dropzone){ dropzone.addEventListener('click',function(e){if(e.target.closest('button'))return;if(fileInput)fileInput.click();}); ['dragenter','dragover'].forEach(function(evt){dropzone.addEventListener(evt,function(e){e.preventDefault();dropzone.classList.add('dragover');});}); ['dragleave','drop'].forEach(function(evt){dropzone.addEventListener(evt,function(e){e.preventDefault();dropzone.classList.remove('dragover');});}); dropzone.addEventListener('drop',function(e){addFiles((e.dataTransfer||{}).files||[]);}); }
  if(preview){ preview.addEventListener('click',function(e){var btn=e.target.closest('.gallery-preview-remove');if(!btn)return;var idx=parseInt(btn.getAttribute('data-remove-index'),10);if(!isNaN(idx)){galleryItems.splice(idx,1);syncInputAndRender();}}); preview.addEventListener('dragstart',function(e){var tile=e.target.closest('.gallery-preview-tile');if(!tile)return;dragFromIndex=parseInt(tile.getAttribute('data-index'),10);}); preview.addEventListener('dragover',function(e){if(dragFromIndex!==null)e.preventDefault();}); preview.addEventListener('drop',function(e){if(dragFromIndex===null)return;e.preventDefault();var tile=e.target.closest('.gallery-preview-tile');if(!tile)return;var to=parseInt(tile.getAttribute('data-index'),10);if(!isNaN(to)&&to!==dragFromIndex){var moved=galleryItems.splice(dragFromIndex,1)[0];galleryItems.splice(to,0,moved);syncInputAndRender();}dragFromIndex=null;}); preview.addEventListener('dragend',function(){dragFromIndex=null;}); }
  canvas.addEventListener('paste',function(e){var items=(e.clipboardData||{}).items||[];var files=[];for(var i=0;i<items.length;i++){if(items[i].kind==='file'){var blob=items[i].getAsFile();if(blob&&blob.type&&blob.type.indexOf('image/')===0)files.push(blob);}}if(files.length){e.preventDefault();addFiles(files);}});
  galleryItems=parseExistingImages(existingInput?existingInput.value:'').map(function(url){return{type:'existing',url:url};});
  syncInputAndRender();
  showStep(1);
});

// ── Actions menu ──────────────────────────────────────────────
document.addEventListener('click', function(e) {
  if (!e.target.closest('.al-actions-wrap')) {
    document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
      m.classList.remove('is-open');
      if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
    });
  }
});
window.alToggleMenu = function(btn) {
  var menu = btn.nextElementSibling;
  var isOpen = menu.classList.contains('is-open');
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open'); });
  if (!isOpen) {
    var rect = btn.getBoundingClientRect();
    menu.style.top  = (rect.bottom + 6) + 'px';
    menu.style.left = Math.max(8, rect.right - 190) + 'px';
    menu.classList.add('is-open');
    btn.classList.add('is-open');
  }
};
window.addEventListener('scroll', function() {
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
    var btn = m.previousElementSibling;
    var rect = btn.getBoundingClientRect();
    m.style.top  = (rect.bottom + 6) + 'px';
    m.style.left = Math.max(8, rect.right - 190) + 'px';
  });
}, true);
function alCloseMenus() {
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){
    m.classList.remove('is-open');
    if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
  });
}

// ── Toggle active (hide/activate) ────────────────────────────
window.alToggleItem = function(btn, e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  alCloseMenus();
  var id = btn.getAttribute('data-id');
  var isActive = btn.getAttribute('data-active') === '1';
  if (!confirm((isActive ? 'Hide' : 'Activate') + ' this item?')) return false;
  btn.disabled = true;
  $.post('<?= AJAX_URL ?>', { action: 'admin_toggle_item_active', id: id, active: isActive ? 0 : 1 }, function(resp) {
    var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
    if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
    if (d && d.refreshPage) window.location.reload();
    else btn.disabled = false;
  }).fail(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not update the item.'); btn.disabled = false; });
  return false;
};

// ── Delete single ─────────────────────────────────────────────
window.alDeleteItem = function(btn, e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  alCloseMenus();
  if (!confirm('Delete this item? This cannot be undone.')) return false;
  btn.disabled = true;
  $.post('<?= AJAX_URL ?>', { action: 'admin_delete_item', id: btn.getAttribute('data-id') }, function(resp) {
    var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(err) {}
    if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
    if (d && d.refreshPage) window.location.reload();
    else btn.disabled = false;
  }).fail(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete the item.'); btn.disabled = false; });
  return false;
};

// Event delegation
document.addEventListener('click', function(e) {
  var btn;
  btn = e.target.closest('.js-toggle-item');
  if (btn) return alToggleItem(btn, e);
  btn = e.target.closest('.js-delete-item');
  if (btn) return alDeleteItem(btn, e);
});

// ── Bulk select & delete ──────────────────────────────────────
(function () {
  var selected = new Set();
  var $bulkBtn = $('#alBulkDeleteBtn');
  var $bulkCnt = $('#alBulkCount');
  var $chkAll  = $('#alChkAll');

  function updateUI() {
    var n = selected.size;
    $bulkCnt.text(n);
    if (n > 0) { $bulkBtn.css('display','inline-flex'); } else { $bulkBtn.hide(); }
    var $rows = $('.al-row-chk:not(:disabled)');
    var total = $rows.length;
    var checked = $rows.filter(function(){ return selected.has(String(this.value)); }).length;
    if (total === 0 || checked === 0) $chkAll.prop('checked', false).prop('indeterminate', false);
    else if (checked === total) $chkAll.prop('checked', true).prop('indeterminate', false);
    else $chkAll.prop('checked', false).prop('indeterminate', true);
  }

  $(document).on('change', '.al-row-chk', function(e) {
    e.stopPropagation();
    var id = String(this.value);
    if (this.checked) selected.add(id); else selected.delete(id);
    updateUI();
  });

  $chkAll.on('change', function() {
    var shouldCheck = this.checked;
    $('.al-row-chk:not(:disabled)').each(function() {
      var id = String(this.value);
      if ($(this).closest('tr.al-row').is(':visible')) {
        this.checked = shouldCheck;
        if (shouldCheck) selected.add(id); else selected.delete(id);
      }
    });
    updateUI();
  });

  var observer = new MutationObserver(function() {
    $('.al-row-chk').each(function() { this.checked = !this.disabled && selected.has(String(this.value)); });
    updateUI();
  });
  var tbody = document.getElementById('alTbody');
  if (tbody) observer.observe(tbody, { childList: true, subtree: false });

  $bulkBtn.on('click', function() {
    if (!selected.size) return;
    var ids = Array.from(selected).map(function(v){ return parseInt(v,10); }).filter(function(n){ return isFinite(n); });
    if (!ids.length) return;
    if (!confirm('Delete ' + ids.length + ' selected item(s)? This cannot be undone.')) return;
    $bulkBtn.prop('disabled', true);
    $.ajax({
      type: 'post', url: '<?= AJAX_URL ?>',
      data: { action: 'admin_bulk_delete_items', ids: ids },
      dataType: 'text',
      success: function(response) {
        var d = response; try { if (typeof response === 'string') d = JSON.parse(response); } catch(err) {}
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
        selected.clear();
        if (d && d.refreshPage) window.location.reload();
        else { $bulkBtn.prop('disabled', false); updateUI(); }
      },
      error: function() { if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete items.'); $bulkBtn.prop('disabled', false); }
    });
  });
  updateUI();
})();

// ── Filter / sort / paginate ──────────────────────────────────
(function(){
  var PER_PAGE = 25;
  var filter   = 'all';
  var typeFilter = '';
  var search   = '';
  var page     = 1;
  var sortCol  = 'id';
  var sortDir  = 'desc';

  var tbody    = document.getElementById('alTbody');
  var allRows  = tbody ? Array.from(tbody.querySelectorAll('.al-row')) : [];
  var showEl   = document.getElementById('alShowing');
  var totEl    = document.getElementById('alTotal');
  var pageEl   = document.getElementById('alPagination');
  var srchEl   = document.getElementById('alSearch');
  var pills    = document.querySelectorAll('#alStatusFilters .al-pill');
  var typePills= document.querySelectorAll('#alTypeFilters .al-pill');
  var ths      = document.querySelectorAll('.al-table thead th.sortable');

  function getSorted(arr){
    return arr.slice().sort(function(a,b){
      var av=a.dataset[sortCol]||'', bv=b.dataset[sortCol]||'';
      var an=parseFloat(av), bn=parseFloat(bv);
      var cmp = isNaN(an)||isNaN(bn) ? String(av).localeCompare(String(bv), undefined, {numeric:true}) : an-bn;
      return sortDir==='asc' ? cmp : -cmp;
    });
  }
  function getFiltered(){
    return allRows.filter(function(c){
      var okStatus = filter === 'all' || c.dataset.status === filter;
      var okType   = !typeFilter || (c.dataset.type||'') === typeFilter;
      var okSearch = !search || (c.dataset.search||'').indexOf(search) !== -1;
      return okStatus && okType && okSearch;
    });
  }
  function render(){
    var filtered = getSorted(getFiltered());
    var total    = filtered.length;
    var pages    = Math.max(1, Math.ceil(total / PER_PAGE));
    if(page > pages) page = pages;
    var start = (page-1)*PER_PAGE, end = start+PER_PAGE;
    allRows.forEach(function(c){ c.style.display='none'; });
    filtered.slice(start,end).forEach(function(c){ tbody.appendChild(c); c.style.display=''; });
    if(showEl) showEl.textContent = total>0 ? (start+1)+'–'+Math.min(end,total) : '0';
    if(totEl)  totEl.textContent  = total;
    ths.forEach(function(th){
      th.classList.remove('sort-asc','sort-desc');
      if(th.dataset.col===sortCol) th.classList.add('sort-'+sortDir);
    });
    if(!pageEl) return;
    pageEl.innerHTML='';
    if(pages<=1) return;
    function btn(label,p,disabled,active){
      var b=document.createElement('button');
      b.className='al-pg-btn'+(active?' al-pg-active':'');
      b.innerHTML=label; b.disabled=!!disabled;
      if(!disabled) b.addEventListener('click',function(){page=p;render();});
      return b;
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>',page-1,page===1,false));
    for(var i=1;i<=pages;i++){
      if(pages>7&&i>2&&i<pages-1&&Math.abs(i-page)>1){
        if(i===3||i===pages-2){ var d=document.createElement('span'); d.style.cssText='color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;'; d.textContent='…'; pageEl.appendChild(d); }
        continue;
      }
      pageEl.appendChild(btn(i,i,false,i===page));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>',page+1,page===pages,false));
  }
  pills.forEach(function(p){
    p.addEventListener('click',function(){
      pills.forEach(function(x){x.classList.remove('active');});
      p.classList.add('active');
      filter = p.dataset.status || 'all';
      page = 1; render();
    });
  });
  typePills.forEach(function(p){
    p.addEventListener('click',function(){
      var isActive = p.classList.contains('active');
      typePills.forEach(function(x){x.classList.remove('active');});
      if(isActive){ typeFilter=''; } else { p.classList.add('active'); typeFilter=p.dataset.type||''; }
      page=1; render();
    });
  });
  if(srchEl) srchEl.addEventListener('input',function(){
    search=srchEl.value.trim().toLowerCase(); page=1; render();
  });
  ths.forEach(function(th){
    th.addEventListener('click',function(){
      var col=th.dataset.col;
      if(sortCol===col) sortDir=sortDir==='asc'?'desc':'asc';
      else { sortCol=col; sortDir='desc'; }
      page=1; render();
    });
  });
  render();
})();
</script>
<?php echo $this->end(); ?>
