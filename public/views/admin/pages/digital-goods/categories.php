<?php
/* ── Admin: Digital Goods Categories — /admin-area/digital-goods/categories ── */
$categories = is_array($categories ?? null) ? $categories : [];
?>
<?= $this->layout('admin/layouts/main', ['meta' => $meta ?? ['title' => 'DG Categories | Admin']]) ?>

<?= $this->start('styles') ?>
<style>
.al-page .card{background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important;}
.al-page .card::before{display:none!important;}
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:var(--bs-card-bg,#141720);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(139,60,247,.25),rgba(192,38,211,.15));border:1px solid rgba(139,60,247,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.al-add-btn{display:inline-flex;align-items:center;gap:.45rem;padding:8px 18px;border-radius:12px;background:linear-gradient(135deg,#8b3cf7,#c026d3);border:none;color:rgba(255,255,255,.9);font-weight:900;font-size:.85rem;cursor:pointer;text-decoration:none;}
.al-add-btn:hover{opacity:.88;color:rgba(255,255,255,.9);}
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:var(--bs-card-bg,#141720);}
.al-table{width:100%;border-collapse:collapse;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;}
.al-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody tr:last-child{border-bottom:none;}
.al-table tbody tr:hover{background:rgba(139,60,247,.07);}
.al-table tbody td{padding:12px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}
.al-cat-icon-cell{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;background:linear-gradient(135deg,rgba(139,60,247,.2),rgba(192,38,211,.1));border:1px solid rgba(139,60,247,.22);overflow:hidden;flex-shrink:0;}
.al-cat-icon-cell i{color:#c084fc;}
.al-cat-icon-cell img{width:100%;height:100%;object-fit:cover;}
.al-cat-banner{width:80px;height:46px;border-radius:10px;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);}
.al-toggle{appearance:none;width:36px;height:20px;border-radius:99px;background:rgba(255,255,255,.12);cursor:pointer;position:relative;border:none;transition:background .2s;flex-shrink:0;}
.al-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:rgba(255,255,255,.9);transition:transform .2s;}
.al-toggle:checked{background:linear-gradient(135deg,#8b3cf7,#c026d3);}
.al-toggle:checked::after{transform:translateX(16px);}
.al-btn{display:inline-flex;align-items:center;gap:.3rem;padding:6px 13px;border-radius:9px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);text-decoration:none;}
.al-btn:hover{background:rgba(255,255,255,.10);color:rgba(255,255,255,.9);}
.al-btn--edit{background:rgba(139,60,247,.14);border-color:rgba(139,60,247,.28);color:#c084fc;}
.al-btn--edit:hover{background:rgba(139,60,247,.25);color:rgba(255,255,255,.9);}
.al-btn--delete{background:rgba(251,113,133,.09);border-color:rgba(251,113,133,.22);color:#fb7185;}
.al-btn--delete:hover{background:rgba(251,113,133,.18);}

/* ── Offcanvas form ── */
.dgcat-canvas{width:min(980px,96vw)!important;display:flex!important;flex-direction:column!important;height:100%!important;}
.dgcat-canvas .offcanvas-header{flex-shrink:0;border-bottom:1px solid rgba(255,255,255,.07)!important;padding:18px 22px;}
.dgcat-canvas .offcanvas-body{flex:1!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;padding:0!important;}
.dgcat-canvas form{height:100%;display:flex;flex-direction:column;overflow:hidden;min-height:0;}
.dgcat-oc-scroll{flex:1;overflow-y:auto;padding:18px 22px;}
.dgcat-oc-footer{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 22px;border-top:1px solid rgba(255,255,255,.07);background:var(--bs-offcanvas-bg,#1e2028);}
.dgcat-oc-actions{display:flex;align-items:center;gap:10px;}
.dgcat-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.dgcat-grid .dgcat-full{grid-column:1 / -1;}
.dgcat-section-label{display:flex;align-items:center;gap:6px;font-size:.68rem;font-weight:950;text-transform:uppercase;letter-spacing:.09em;color:rgba(255,255,255,.35);margin:16px 0 9px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);}
.dgcat-section-label:first-child{margin-top:0;}
.dgcat-section-label i{color:#8b5cf6;font-size:.68rem;}
@media(max-width:991px){.dgcat-canvas{width:100vw!important}.dgcat-grid{grid-template-columns:1fr}.dgcat-grid .dgcat-full{grid-column:auto}.dgcat-oc-footer{align-items:stretch;flex-direction:column}.dgcat-oc-actions{width:100%}.dgcat-oc-actions .al-add-btn{flex:1;justify-content:center}}
.dgf-label{font-size:.78rem;font-weight:700;color:rgba(255,255,255,.5);margin-bottom:4px;display:block;}
.dgf-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);border-radius:10px;color:rgba(255,255,255,.9);padding:9px 13px;font-size:.88rem;outline:none;transition:border-color .15s;}
.dgf-input:focus{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dgf-input::placeholder{color:rgba(255,255,255,.2);}
select.dgf-input option{background:#1a1a2e;}
.dgf-help{font-size:.73rem;color:rgba(255,255,255,.3);margin-top:4px;}

/* ── Icon widget (identisch zu Rank-Icon-Upload) ── */
.dg-icon-widget{display:flex;flex-direction:column;gap:8px;}
.dg-icon-zone{
    width:72px;height:72px;border-radius:16px;
    background:rgba(139,60,247,.10);
    border:2px dashed rgba(139,60,247,.38);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;position:relative;overflow:hidden;
    transition:border-color .18s,background .18s;
    flex-shrink:0;
}
.dg-icon-zone:hover,.dg-icon-zone.drag-over{border-color:rgba(139,60,247,.75);background:rgba(139,60,247,.18);}
.dg-icon-zone img{width:100%;height:100%;object-fit:contain;display:none;border-radius:14px;}
.dg-icon-zone .riz-ph{font-size:24px;color:rgba(139,60,247,.55);display:flex;align-items:center;justify-content:center;}
.dg-icon-zone .riz-overlay{
    position:absolute;inset:0;background:rgba(0,0,0,.62);
    display:flex;align-items:center;justify-content:center;
    font-size:9px;font-weight:900;color:rgba(255,255,255,.9);text-align:center;line-height:1.4;letter-spacing:.04em;
    opacity:0;transition:opacity .15s;pointer-events:none;
}
.dg-icon-zone:hover .riz-overlay{opacity:1;}
.dg-icon-row{display:flex;align-items:center;gap:12px;}
.dg-icon-right{flex:1;display:flex;flex-direction:column;gap:6px;}
.dg-icon-url{
    width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);
    border-radius:8px;color:rgba(255,255,255,.85);padding:7px 11px;font-size:.8rem;outline:none;
}
.dg-icon-url:focus{border-color:rgba(139,60,247,.45);}
.dg-icon-url::placeholder{color:rgba(255,255,255,.22);}
.dg-icon-tabs{display:flex;gap:4px;}
.dg-icon-tab{padding:4px 10px;border-radius:7px;font-size:.73rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);transition:background .12s;}
.dg-icon-tab.active{background:rgba(139,60,247,.2);border-color:rgba(139,60,247,.4);color:#c084fc;}
.dg-fa-wrap{display:flex;align-items:center;gap:8px;}
.dg-fa-prev{width:38px;height:38px;border-radius:10px;background:rgba(139,60,247,.18);border:1px solid rgba(139,60,247,.28);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.dg-fa-prev i{font-size:18px;color:#c084fc;}
.dg-mode-image,.dg-mode-fa{display:none;}
.dg-mode-image.show,.dg-mode-fa.show{display:block;}

/* ── Banner zone (same style, wider) ── */
.dg-banner-zone{
    width:100%;height:180px;border-radius:14px;
    background:rgba(139,60,247,.07);
    border:2px dashed rgba(139,60,247,.3);
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
    cursor:pointer;position:relative;overflow:hidden;
    transition:border-color .18s,background .18s;
}
.dg-banner-zone:hover,.dg-banner-zone.drag-over{border-color:rgba(139,60,247,.65);background:rgba(139,60,247,.13);}
.dg-banner-zone .riz-overlay{position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;color:rgba(255,255,255,.9);opacity:0;transition:opacity .15s;}
.dg-banner-zone:hover .riz-overlay{opacity:1;}
.dg-banner-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;display:none;border-radius:12px;}
.dg-banner-placeholder{display:flex;flex-direction:column;align-items:center;gap:4px;pointer-events:none;}
.dg-banner-placeholder i{font-size:22px;color:rgba(139,60,247,.5);}
.dg-banner-placeholder span{font-size:.75rem;color:rgba(255,255,255,.35);}
.dg-banner-placeholder strong{color:rgba(255,255,255,.65);font-size:.82rem;}
</style>
<?= $this->stop() ?>

<div class="al-page">

  <!-- Hero -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-solid fa-layer-group"></i></div>
      <div>
        <h1 class="al-hero-title">Digital Goods — Categories</h1>
        <div class="al-hero-sub">Manage categories shown on the public Digital Goods page.</div>
      </div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/admin-area/digital-goods/listings" class="al-btn"><i class="fa-solid fa-list"></i> All Listings</a>
      <button type="button" class="al-add-btn" id="dgCatAddBtn"><i class="fa-solid fa-plus"></i> New Category</button>
    </div>
  </div>

  <!-- Table -->
  <div class="al-table-wrap">
    <?php if (empty($categories)): ?>
    <div style="text-align:center;padding:56px 24px;color:rgba(255,255,255,.35);">
      <i class="fa-solid fa-folder-open" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:12px;"></i>
      No categories yet. Create one above.
    </div>
    <?php else: ?>
    <table class="al-table">
      <thead>
        <tr>
          <th>Icon</th><th>Banner</th><th>Name</th><th>Slug</th><th>Sort</th><th>Listings</th><th>Active</th><th>Actions</th>
        </tr>
      </thead>
      <tbody id="dgCatTableBody">
        <?php foreach ($categories as $cat):
          $iconVal  = htmlspecialchars($cat['icon']??'', ENT_QUOTES);
          $banner   = htmlspecialchars($cat['banner']??'', ENT_QUOTES);
          $isFaIcon = preg_match('/^fa[-\s]/', $cat['icon']??'');
        ?>
        <tr id="dgcat-row-<?= (int)$cat['id'] ?>">
          <td>
            <div class="al-cat-icon-cell">
              <?php if ($isFaIcon): ?>
                <i class="<?= $iconVal ?>"></i>
              <?php elseif ($cat['icon']): ?>
                <img src="<?= $iconVal ?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <i class="fa-solid fa-layer-group" style="display:none;color:#c084fc;"></i>
              <?php else: ?>
                <i class="fa-solid fa-layer-group"></i>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php if ($banner): ?>
            <img class="al-cat-banner" src="<?= $banner ?>" alt="">
            <?php else: ?>
            <span style="color:rgba(255,255,255,.25);font-size:.78rem;">—</span>
            <?php endif; ?>
          </td>
          <td style="font-weight:800;color:#fff;"><?= htmlspecialchars($cat['name']??'', ENT_QUOTES) ?></td>
          <td style="font-size:.8rem;color:rgba(255,255,255,.45);font-family:monospace;"><?= htmlspecialchars($cat['slug']??'', ENT_QUOTES) ?></td>
          <td style="color:rgba(255,255,255,.5);"><?= (int)$cat['sort_order'] ?></td>
          <td>
            <a href="<?= BASE_URL ?>/admin-area/digital-goods/listings?category_id=<?= (int)$cat['id'] ?>" style="color:#c084fc;font-weight:700;text-decoration:none;">
              <?= (int)($cat['listing_count']??0) ?> listings
            </a>
          </td>
          <td>
            <input type="checkbox" class="al-toggle dgcat-toggle" <?= (int)$cat['active']?'checked':'' ?> data-id="<?= (int)$cat['id'] ?>">
          </td>
          <td>
            <div style="display:flex;gap:6px;">
              <button type="button" class="al-btn al-btn--edit dgcat-edit-btn"
                data-id="<?= (int)$cat['id'] ?>"
                data-name="<?= htmlspecialchars($cat['name']??'', ENT_QUOTES) ?>"
                data-slug="<?= htmlspecialchars($cat['slug']??'', ENT_QUOTES) ?>"
                data-icon="<?= $iconVal ?>"
                data-banner="<?= $banner ?>"
                data-description="<?= htmlspecialchars($cat['description']??'', ENT_QUOTES) ?>"
                data-sort="<?= (int)$cat['sort_order'] ?>"
                data-active="<?= (int)$cat['active'] ?>"
              ><i class="fa-solid fa-pen"></i> Edit</button>
              <button type="button" class="al-btn al-btn--delete dgcat-delete-btn" data-id="<?= (int)$cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']??'', ENT_QUOTES) ?>">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>

<!-- ══ Offcanvas: Create/Edit Category ══ -->
<div class="offcanvas offcanvas-end dgcat-canvas" tabindex="-1" id="dgCatCanvas" aria-labelledby="dgCatCanvasLabel" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="offcanvas-header">
    <div>
      <h5 class="offcanvas-title mb-0" id="dgCatCanvasLabel" style="font-weight:950;font-size:1.02rem;color:#fff;">New Category</h5>
      <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:2px;" id="dgCatCanvasSub">Create or update a digital goods category</div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <form id="dgCatForm" enctype="multipart/form-data">
      <input type="hidden" name="action" value="admin_dg_save_category">
      <input type="hidden" name="id"     id="dgCatId"    value="0">
      <input type="hidden" name="icon"   id="dgCatIconVal" value="">
      <input type="hidden" name="banner" id="dgCatBannerVal" value="">

      <div class="dgcat-oc-scroll">
        <div class="dgcat-section-label"><i class="fa-solid fa-circle-info"></i> Category Details</div>
        <div class="dgcat-grid">

      <!-- Name -->
      <div class="mb-3">
        <label class="dgf-label">Name <span style="color:#fb7185;">*</span></label>
        <input type="text" id="dgCatName" name="name" class="dgf-input" required placeholder="e.g. Streaming & Music">
      </div>

      <!-- Slug -->
      <div class="mb-3">
        <label class="dgf-label">Slug <span style="font-size:.7rem;color:rgba(255,255,255,.3);">(auto if blank)</span></label>
        <input type="text" id="dgCatSlug" name="slug" class="dgf-input" placeholder="streaming-music">
        <div class="dgf-help">URL: /digital-goods/<strong id="dgSlugPreview">slug</strong></div>
      </div>
        </div>

        <div class="dgcat-section-label"><i class="fa-solid fa-icons"></i> Icon</div>

      <!-- ── Icon widget ── -->
      <div class="mb-3">
        <label class="dgf-label">Category Icon</label>

        <!-- Mode tabs -->
        <div class="dg-icon-tabs mb-2">
          <button type="button" class="dg-icon-tab active" id="dgTabImg" onclick="dgIconMode('image')">
            <i class="fa-solid fa-image"></i> Image / URL
          </button>
          <button type="button" class="dg-icon-tab" id="dgTabFa" onclick="dgIconMode('fa')">
            <i class="fa-solid fa-icons"></i> FontAwesome
          </button>
        </div>

        <!-- Image mode -->
        <div class="dg-mode-image show" id="dgModeImage">
          <div class="dg-icon-row">
            <!-- Click / drag drop zone -->
            <div class="dg-icon-zone" id="dgIconZone"
                 onclick="dgPickIconFile()"
                 ondragover="event.preventDefault();this.classList.add('drag-over')"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="dgIconDrop(event)"
                 title="Click to upload · Drag & drop image · Or paste URL">
              <img id="dgIconImg" src="" alt="">
              <span class="riz-ph" id="dgIconPh"><i class="fa-solid fa-image"></i></span>
              <div class="riz-overlay">Upload<br>/Drop<br>/URL</div>
            </div>
            <div class="dg-icon-right">
              <input type="text" id="dgIconUrl" class="dg-icon-url"
                     placeholder="Paste image URL or drag an image…"
                     oninput="dgApplyIconUrl(this.value)">
              <div style="font-size:.72rem;color:rgba(255,255,255,.3);">
                Click zone to browse files · Drag & drop · Or paste any image URL
              </div>
            </div>
          </div>
        </div>

        <!-- FontAwesome mode -->
        <div class="dg-mode-fa" id="dgModeFa">
          <div class="dg-fa-wrap">
            <div class="dg-fa-prev" id="dgFaPrev"><i class="fa-solid fa-layer-group"></i></div>
            <div style="flex:1;">
              <input type="text" id="dgCatFaClass" class="dgf-input"
                     placeholder="fa-solid fa-play-circle"
                     oninput="dgApplyFa(this.value)">
              <div class="dgf-help" style="margin-top:4px;">
                Any FA6 class &nbsp;·&nbsp;
                <a href="https://fontawesome.com/search" target="_blank" style="color:#c084fc;">Browse icons ↗</a>
              </div>
            </div>
          </div>
        </div>
      </div>

        <div class="dgcat-section-label"><i class="fa-solid fa-align-left"></i> Description</div>
      <!-- Description -->
      <div class="mb-3">
        <label class="dgf-label">Description</label>
        <textarea id="dgCatDesc" name="description" class="dgf-input" rows="4" placeholder="Short description shown on the category card…"></textarea>
      </div>

        <div class="dgcat-section-label"><i class="fa-solid fa-image"></i> Banner</div>
      <!-- ── Banner image ── -->
      <div class="mb-3">
        <label class="dgf-label">Banner Image <span style="font-size:.7rem;color:rgba(255,255,255,.3);">(card background)</span></label>

        <div class="dg-banner-zone" id="dgBannerZone"
             onclick="dgPickBannerFile()"
             ondragover="event.preventDefault();this.classList.add('drag-over')"
             ondragleave="this.classList.remove('drag-over')"
             ondrop="dgBannerDrop(event)">
          <img id="dgBannerImg" class="dg-banner-img" src="" alt="">
          <div class="dg-banner-placeholder" id="dgBannerPh">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <strong>Click or drag &amp; drop banner image</strong>
            <span>JPG · PNG · WEBP — recommended 600 × 400 px</span>
          </div>
          <div class="riz-overlay">Change image</div>
        </div>

        <!-- URL fallback for banner -->
        <input type="text" id="dgBannerUrl" class="dg-icon-url" style="margin-top:8px;"
               placeholder="Or paste banner image URL here…"
               oninput="dgApplyBannerUrl(this.value)">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
          <div class="dgf-help">Leave blank = icon-only card (no background image)</div>
          <button type="button" onclick="dgClearBanner()" style="font-size:.73rem;color:#fb7185;background:none;border:none;cursor:pointer;padding:0;">Clear</button>
        </div>
      </div>

        <div class="dgcat-section-label"><i class="fa-solid fa-gear"></i> Settings</div>
      <!-- Sort + Active -->
      <div class="dgcat-grid">
        <div class="mb-3">
          <label class="dgf-label">Sort Order</label>
          <input type="number" id="dgCatSort" name="sort_order" class="dgf-input" min="0" value="0" placeholder="0 = first">
        </div>

        <div class="mb-4" style="display:flex;align-items:end;">
          <div style="display:flex;align-items:center;gap:10px;min-height:42px;">
            <input type="checkbox" name="active" id="dgCatActive" class="al-toggle" value="1" checked>
            <label for="dgCatActive" style="font-size:.88rem;font-weight:700;color:rgba(255,255,255,.7);cursor:pointer;">Active (visible to buyers)</label>
          </div>
        </div>
      </div>
      </div>

      <div class="dgcat-oc-footer">
        <div id="dgCatMsg" style="font-size:.82rem;min-height:18px;"></div>
        <div class="dgcat-oc-actions">
          <button type="button" class="al-btn" data-bs-dismiss="offcanvas">Cancel</button>
          <button type="submit" class="al-add-btn" id="dgCatSaveBtn" style="justify-content:center;">
            <i class="fa-solid fa-save"></i> Save Category
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    var AJAX_URL = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= addslashes(AJAX_URL) ?>';
    var canvas   = document.getElementById('dgCatCanvas');
    var bsCanvas = new bootstrap.Offcanvas(canvas);
    var form     = document.getElementById('dgCatForm');
    var msgEl    = document.getElementById('dgCatMsg');

    /* ──────────────────────────────────────────────────────
       ICON WIDGET
    ────────────────────────────────────────────────────── */

    // Track current mode: 'image' | 'fa'
    var iconMode = 'image';

    window.dgIconMode = function(mode) {
        iconMode = mode;
        document.getElementById('dgModeImage').classList.toggle('show', mode === 'image');
        document.getElementById('dgModeFa').classList.toggle('show',    mode === 'fa');
        document.getElementById('dgTabImg').classList.toggle('active',  mode === 'image');
        document.getElementById('dgTabFa').classList.toggle('active',   mode === 'fa');
        // Sync hidden field
        if (mode === 'fa') {
            var cls = document.getElementById('dgCatFaClass').value.trim();
            document.getElementById('dgCatIconVal').value = cls;
        } else {
            document.getElementById('dgCatIconVal').value = document.getElementById('dgIconUrl').value.trim();
        }
    };

    // Apply an image URL to the icon zone
    window.dgApplyIconUrl = function(url) {
        url = url.trim();
        var img = document.getElementById('dgIconImg');
        var ph  = document.getElementById('dgIconPh');
        if (url) {
            img.src = url; img.style.display = 'block'; ph.style.display = 'none';
        } else {
            img.style.display = 'none'; ph.style.display = 'flex';
        }
        document.getElementById('dgIconUrl').value = url;
        document.getElementById('dgCatIconVal').value = url;
    };

    // Apply FA class
    window.dgApplyFa = function(cls) {
        cls = cls.trim();
        document.getElementById('dgFaPrev').innerHTML = cls ? '<i class="' + cls + '"></i>' : '<i class="fa-solid fa-layer-group"></i>';
        document.getElementById('dgCatIconVal').value = cls;
    };

    // Click to pick file
    window.dgPickIconFile = function() {
        var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/*';
        inp.onchange = function() {
            if (this.files[0]) {
                var r = new FileReader();
                r.onload = function(e) { dgApplyIconUrl(e.target.result); };
                r.readAsDataURL(this.files[0]);
            }
        };
        inp.click();
    };

    // Drag & drop icon
    window.dgIconDrop = function(ev) {
        ev.preventDefault();
        document.getElementById('dgIconZone').classList.remove('drag-over');
        if (ev.dataTransfer.files && ev.dataTransfer.files[0]) {
            var r = new FileReader();
            r.onload = function(e) { dgApplyIconUrl(e.target.result); };
            r.readAsDataURL(ev.dataTransfer.files[0]);
        } else {
            var url = ev.dataTransfer.getData('text/uri-list') || ev.dataTransfer.getData('text/plain');
            if (url) dgApplyIconUrl(url.trim());
        }
    };

    /* ──────────────────────────────────────────────────────
       BANNER WIDGET
    ────────────────────────────────────────────────────── */

    window.dgApplyBannerUrl = function(url) {
        url = url.trim();
        var img = document.getElementById('dgBannerImg');
        var ph  = document.getElementById('dgBannerPh');
        document.getElementById('dgBannerUrl').value = url;
        document.getElementById('dgCatBannerVal').value = url;
        if (url) {
            img.src = url; img.style.display = 'block'; ph.style.display = 'none';
        } else {
            img.style.display = 'none'; ph.style.display = 'flex';
        }
    };

    window.dgPickBannerFile = function() {
        var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/*';
        inp.onchange = function() {
            if (this.files[0]) dgReadBannerFile(this.files[0]);
        };
        inp.click();
    };

    function dgReadBannerFile(file) {
        var r = new FileReader();
        r.onload = function(e) { dgApplyBannerUrl(e.target.result); };
        r.readAsDataURL(file);
    }

    window.dgBannerDrop = function(ev) {
        ev.preventDefault();
        document.getElementById('dgBannerZone').classList.remove('drag-over');
        if (ev.dataTransfer.files && ev.dataTransfer.files[0]) {
            dgReadBannerFile(ev.dataTransfer.files[0]);
        } else {
            var url = ev.dataTransfer.getData('text/uri-list') || ev.dataTransfer.getData('text/plain');
            if (url) dgApplyBannerUrl(url.trim());
        }
    };

    window.dgClearBanner = function() {
        dgApplyBannerUrl('');
        document.getElementById('dgBannerUrl').value = '';
    };

    /* ──────────────────────────────────────────────────────
       SLUG preview
    ────────────────────────────────────────────────────── */
    document.getElementById('dgCatName').addEventListener('input', function() {
        var slug = document.getElementById('dgCatSlug').value.trim();
        if (!slug) {
            var auto = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
            document.getElementById('dgSlugPreview').textContent = auto || 'slug';
        }
    });
    document.getElementById('dgCatSlug').addEventListener('input', function() {
        document.getElementById('dgSlugPreview').textContent = this.value || 'slug';
    });

    /* ──────────────────────────────────────────────────────
       OPEN CANVAS  — Create
    ────────────────────────────────────────────────────── */
    document.getElementById('dgCatAddBtn').addEventListener('click', function() {
        document.getElementById('dgCatCanvasLabel').textContent = 'New Category';
        document.getElementById('dgCatCanvasSub').textContent = 'Create a new digital goods category';
        form.reset();
        document.getElementById('dgCatId').value = '0';
        document.getElementById('dgCatIconVal').value = '';
        document.getElementById('dgCatBannerVal').value = '';
        document.getElementById('dgIconImg').style.display = 'none';
        document.getElementById('dgIconPh').style.display = 'flex';
        document.getElementById('dgIconUrl').value = '';
        document.getElementById('dgFaPrev').innerHTML = '<i class="fa-solid fa-layer-group"></i>';
        document.getElementById('dgCatFaClass').value = '';
        document.getElementById('dgBannerImg').style.display = 'none';
        document.getElementById('dgBannerPh').style.display = 'flex';
        document.getElementById('dgBannerUrl').value = '';
        document.getElementById('dgSlugPreview').textContent = 'slug';
        msgEl.textContent = '';
        dgIconMode('image');
        bsCanvas.show();
    });

    /* ──────────────────────────────────────────────────────
       OPEN CANVAS  — Edit (pre-fill)
    ────────────────────────────────────────────────────── */
    document.querySelectorAll('.dgcat-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('dgCatCanvasLabel').textContent = 'Edit Category';
            document.getElementById('dgCatCanvasSub').textContent = 'Update category details, icon, banner and visibility';
            var d = this.dataset;
            document.getElementById('dgCatId').value   = d.id    || '0';
            document.getElementById('dgCatName').value = d.name  || '';
            document.getElementById('dgCatSlug').value = d.slug  || '';
            document.getElementById('dgCatDesc').value = d.description || '';
            document.getElementById('dgCatSort').value = d.sort  || '0';
            document.getElementById('dgCatActive').checked = d.active === '1';
            document.getElementById('dgSlugPreview').textContent = d.slug || 'slug';
            msgEl.textContent = '';

            // Detect icon type
            var iconVal = d.icon || '';
            var isFa = /^fa[-\s]/.test(iconVal);
            if (isFa) {
                dgIconMode('fa');
                document.getElementById('dgCatFaClass').value = iconVal;
                dgApplyFa(iconVal);
                document.getElementById('dgCatIconVal').value = iconVal;
            } else {
                dgIconMode('image');
                dgApplyIconUrl(iconVal);
            }

            // Banner
            var bannerVal = d.banner || '';
            dgApplyBannerUrl(bannerVal);

            bsCanvas.show();
        });
    });

    /* ──────────────────────────────────────────────────────
       TOGGLE ACTIVE
    ────────────────────────────────────────────────────── */
    document.querySelectorAll('.dgcat-toggle').forEach(function(tog) {
        tog.addEventListener('change', function() {
            fetch(AJAX_URL, {
                method: 'POST',
                body: new URLSearchParams({ action: 'admin_dg_toggle_category', id: this.dataset.id, active: this.checked ? 1 : 0 })
            }).catch(function(){});
        });
    });

    /* ──────────────────────────────────────────────────────
       DELETE
    ────────────────────────────────────────────────────── */
    document.querySelectorAll('.dgcat-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete category "' + this.dataset.name + '"? This cannot be undone.')) return;
            var id = this.dataset.id;
            fetch(AJAX_URL, {
                method: 'POST',
                body: new URLSearchParams({ action: 'admin_dg_delete_category', id: id })
            })
            .then(function(r){ return r.json(); })
            .then(function(r) {
                if (r && r.success) {
                    var row = document.getElementById('dgcat-row-' + id);
                    if (row) row.remove();
                } else if (r && r.sendToast) {
                    alert(r.sendToast.message);
                }
            });
        });
    });

    /* ──────────────────────────────────────────────────────
       SUBMIT FORM
    ────────────────────────────────────────────────────── */
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Sync icon value from active mode before submit
        if (iconMode === 'fa') {
            document.getElementById('dgCatIconVal').value = document.getElementById('dgCatFaClass').value.trim();
        } else {
            document.getElementById('dgCatIconVal').value = document.getElementById('dgIconUrl').value.trim();
        }

        var saveBtn = document.getElementById('dgCatSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        msgEl.textContent = '';

        var fd = new FormData(form);
        if (!document.getElementById('dgCatActive').checked) fd.set('active', '0');
        // Remove the file inputs (we send URLs as hidden fields, not raw files for now)
        fd.delete('banner_image');
        fd.delete('icon_file');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', AJAX_URL, true);
        xhr.onload = function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Category';
            try {
                var r = JSON.parse(xhr.responseText);
                if (r && r.success) {
                    msgEl.textContent = 'Saved!'; msgEl.style.color = '#4ade80';
                    setTimeout(function() { bsCanvas.hide(); location.reload(); }, 700);
                } else {
                    var m = (r && r.sendToast && r.sendToast.message) ? r.sendToast.message : 'Error saving.';
                    msgEl.textContent = m; msgEl.style.color = '#fb7185';
                }
            } catch (err) {
                msgEl.textContent = 'Server error — check console.';
                msgEl.style.color = '#fb7185';
                console.error(xhr.responseText);
            }
        };
        xhr.onerror = function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Category';
            msgEl.textContent = 'Network error.';
        };
        xhr.send(fd);
    });
})();
</script>
<?= $this->stop() ?>
