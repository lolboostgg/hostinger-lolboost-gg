<?php
/**
 * Shared booster profile header for Booster Area pages.
 * Used by: profile.php, personal-details.php, payout.php
 */
if (!function_exists('lb_render_booster_area_profile_header')) {
    function lb_render_booster_area_profile_header(string $activeTab = 'profile'): void
    {
        $coverDefault = ASSET_URL . '/core/main/img/banners/leona.jpeg';
        $coverUrl = trim((string)(BOOSTER_DATA['cover'] ?? BOOSTER_DATA['banner_url'] ?? ''));
        $coverUrl = $coverUrl !== '' ? $coverUrl : $coverDefault;
        $coverPosition = trim((string)(BOOSTER_DATA['cover_position'] ?? BOOSTER_DATA['banner_position'] ?? '50% 50%'));
        if ($coverPosition === '') $coverPosition = '50% 50%';

        $iconUrl = trim((string)(BOOSTER_DATA['icon'] ?? ''));
        $username = (string)(BOOSTER_DATA['username'] ?? 'Booster');
        $rankId = (int)(BOOSTER_DATA['rank_id'] ?? 0);
        $isBanned = (int)(BOOSTER_DATA['is_banned'] ?? 0) === 1;
        ?>

<style>
.booster-profile-shell{
  --bp-bg:#25282a;--bp-card:#25282a;--bp-card-soft:#202325;--bp-border:#2f3235;--bp-muted:#91989e;--bp-text:#fff;--bp-primary:#5c4ae3;--bp-success:#00c9a7;--bp-danger:#ed4c78;
  width:min(100%,1580px);margin:0 auto 1.75rem;
}
.bp-hero-card{overflow:hidden;border-radius:1rem;background:var(--bp-card);border:1px solid var(--bp-border);box-shadow:0 .375rem .75rem rgba(30,32,34,.20)}
.bp-cover{position:relative;height:170px;background:#111a44;background-image:radial-gradient(circle at 8% 0%,rgba(255,255,255,.10),transparent 22%),radial-gradient(circle at 92% 100%,rgba(9,165,190,.16),transparent 26%),linear-gradient(90deg,#111a44 0%,#312e81 48%,#0f172a 100%);background-size:cover;background-position:center;overflow:hidden}
.bp-cover::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,14,29,.04),rgba(10,14,29,.34));pointer-events:none}
.bp-cover-edit{position:absolute;right:14px;bottom:12px;z-index:2;display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .85rem;border-radius:.5rem;border:1px solid rgba(255,255,255,.18);background:rgba(0,0,0,.42);color:rgba(255,255,255,.9);font-size:.8rem;font-weight:800;backdrop-filter:blur(8px);transition:background .15s,border-color .15s,color .15s}
.bp-cover-edit:hover{background:rgba(92,74,227,.36);border-color:rgba(92,74,227,.65);color:#fff}
.bp-body{position:relative;padding:0 2rem 1.25rem;background:linear-gradient(180deg,rgba(37,40,42,.98),#25282a 100%)}
.bp-main{display:flex;align-items:center;gap:1.25rem;min-height:108px}
.bp-avatar-wrap{position:relative;flex:0 0 auto;margin-top:-54px;cursor:pointer;align-self:flex-start}
.bp-avatar{width:112px;height:112px;border-radius:50%;overflow:hidden;border:5px solid var(--bp-card);background:#1e2022;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:900;box-shadow:0 1rem 2rem rgba(0,0,0,.35)}
.bp-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.bp-avatar-edit{position:absolute;right:2px;bottom:4px;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#34373a;border:1px solid rgba(255,255,255,.18);color:#fff;box-shadow:0 .5rem 1rem rgba(0,0,0,.25);font-size:.8rem}
.bp-info{min-width:0;flex:1;padding-top:.2rem}
.bp-name-row{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}
.bp-name{font-size:1.45rem;line-height:1.1;font-weight:900;color:#fff;margin:0}
.bp-status-icon{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:999px;background:rgba(0,201,167,.14);border:1px solid rgba(0,201,167,.28);color:var(--bp-success);font-size:.82rem}
.bp-status-icon.is-banned{background:rgba(237,76,120,.14);border-color:rgba(237,76,120,.3);color:var(--bp-danger)}
.bp-meta{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-top:.55rem;color:#c5c8cc;font-size:.86rem}
.bp-meta-item{display:inline-flex;align-items:center;gap:.38rem;color:#c5c8cc;line-height:1.25}
.bp-meta-item i{color:#91989e;font-size:.95rem}
.bp-tabs{margin-top:.2rem;border-top:1px solid var(--bp-border);display:flex;align-items:center;gap:1.35rem;flex-wrap:wrap;padding:0 0 0 0;min-height:54px}
.bp-tab{position:relative;display:inline-flex;align-items:center;height:54px;padding:0 .15rem;color:#c5c8cc;font-weight:700;text-decoration:none;white-space:nowrap;transition:color .15s ease}
.bp-tab:hover{color:#fff}
.bp-tab.active{color:#fff}
.bp-tab.active::after{content:"";position:absolute;left:0;right:0;bottom:-1px;height:2px;background:var(--bp-primary);border-radius:999px;box-shadow:0 0 10px rgba(92,74,227,.45)}
.bp-upload-modal{max-width:440px;width:calc(100% - 2rem)}
#upload-icon-modal .modal-body,#upload-cover-modal .modal-body{max-height:70vh;overflow:auto}
.bp-dropzone{display:block;border:1px dashed rgba(255,255,255,.22);border-radius:14px;padding:18px;cursor:pointer;background:rgba(255,255,255,.03);transition:transform .08s ease,border-color .12s ease,background .12s ease;user-select:none;text-align:center}
.bp-dropzone:hover{border-color:rgba(92,74,227,.70);background:rgba(92,74,227,.08);transform:translateY(-1px)}
.bp-dropzone-title{font-weight:950;color:#fff}.bp-dropzone-hint{opacity:.7;font-size:.9rem;margin-top:4px}
.bp-icon-preview-wrap{width:116px;height:116px;border-radius:50%;overflow:hidden;margin:0 auto 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center}
.bp-icon-preview-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.bp-cover-stage{position:relative;width:100%;aspect-ratio:4/1;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.03)}
.bp-cover-preview{position:absolute;inset:0;display:none;background-repeat:no-repeat;background-size:cover;background-position:50% 50%;cursor:grab;touch-action:none}
.bp-cover-preview.is-visible{display:block}.bp-cover-preview.is-dragging{cursor:grabbing}
.bp-cover-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.45);font-weight:700;border:1px dashed rgba(255,255,255,.18);border-radius:12px}
.bp-cover-help{color:rgba(255,255,255,.72);font-size:.92rem;display:flex;align-items:center;gap:.55rem}
.bp-cover-pos-row{display:flex;align-items:center;justify-content:space-between;gap:1rem}.bp-cover-pos-readout{color:rgba(255,255,255,.62);font-size:.9rem;white-space:nowrap}
@media (min-width:1400px){.bp-cover{height:190px}.bp-body{padding-left:2.25rem;padding-right:2.25rem}}
@media (max-width:767.98px){.bp-cover{height:125px}.bp-body{padding:0 1rem 1rem}.bp-main{align-items:center;gap:.85rem;min-height:92px}.bp-avatar-wrap{margin-top:-40px}.bp-avatar{width:82px;height:82px;border-width:4px}.bp-avatar-edit{width:26px;height:26px}.bp-name{font-size:1.1rem}.bp-meta{gap:.6rem;font-size:.8rem}.bp-tabs{gap:1rem;min-height:48px;overflow-x:auto;flex-wrap:nowrap}.bp-tab{height:48px}.bp-cover-edit span{display:none}}
</style>

<div class="booster-profile-shell">
  <div class="bp-hero-card">
    <div class="bp-cover" id="bpHeroCover" style="background-image:url('<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>');background-position:<?= htmlspecialchars($coverPosition, ENT_QUOTES, 'UTF-8') ?>;">
      <button type="button" class="bp-cover-edit" data-bs-toggle="modal" data-bs-target="#upload-cover-modal">
        <i class="fa-duotone fa-image"></i><span>Change Banner</span>
      </button>
    </div>
    <div class="bp-body">
      <div class="bp-main">
        <div class="bp-avatar-wrap" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
          <div class="bp-avatar">
            <?php if ($iconUrl !== ''): ?>
              <img src="<?= htmlspecialchars($iconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
              <?= htmlspecialchars(strtoupper(substr($username, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
          </div>
          <div class="bp-avatar-edit"><i class="fa-solid fa-pen"></i></div>
        </div>
        <div class="bp-info">
          <div class="bp-name-row">
            <h1 class="bp-name"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($isBanned): ?>
              <span class="bp-status-icon is-banned" data-bs-toggle="tooltip" title="Banned"><i class="fa-solid fa-ban"></i></span>
            <?php else: ?>
              <span class="bp-status-icon" data-bs-toggle="tooltip" title="Active"><i class="fa-solid fa-badge-check"></i></span>
            <?php endif; ?>
          </div>
          <div class="bp-meta">
            <?php if (!empty(BOOSTER_DATA['email'])): ?><span class="bp-meta-item"><i class="fa-solid fa-envelope"></i><?= htmlspecialchars((string)BOOSTER_DATA['email'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty(BOOSTER_DATA['discord'])): ?><span class="bp-meta-item"><i class="fa-brands fa-discord"></i><?= htmlspecialchars((string)BOOSTER_DATA['discord'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <span class="bp-meta-item"><i class="fa-duotone fa-fire-flame-curved"></i><?= htmlspecialchars(function_exists('util_format_booster_rank') ? util_format_booster_rank($rankId) : (string)$rankId, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
      </div>
      <nav class="bp-tabs" aria-label="Profile navigation">
        <a class="bp-tab<?= $activeTab === 'profile' ? ' active' : '' ?>" href="/booster-area/profile">Profile</a>
        <a class="bp-tab<?= $activeTab === 'personal-details' ? ' active' : '' ?>" href="/booster-area/personal-details">Personal Details</a>
        <a class="bp-tab<?= $activeTab === 'payout' ? ' active' : '' ?>" href="/booster-area/payout">Payout</a>
      </nav>
    </div>
  </div>
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="booster_upload_profile_picture">
  <div id="upload-icon-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered bp-upload-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Icon</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="bp-icon-preview-wrap">
            <img id="bpIconPreview" src="<?= htmlspecialchars($iconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Icon preview" style="<?= $iconUrl ? '' : 'display:none;' ?>">
          </div>
          <label class="bp-dropzone" for="bp_image_url_icon">
            <div class="bp-dropzone-title">Drag and drop or click to choose</div>
            <div class="bp-dropzone-hint">Recommended: square image</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="bp_image_url_icon">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="bpIconSubmit" type="submit" class="btn btn-primary" disabled><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
        </div>
      </div>
    </div>
  </div>
</form>

<form class="ajax-form" action="<?= AJAX_URL ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="booster_upload_cover">
  <div id="upload-cover-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable bp-upload-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Banner</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <div class="bp-cover-stage">
              <div id="bpCoverPreview" class="bp-cover-preview is-visible" data-default-src="<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>" data-default-position="<?= htmlspecialchars($coverPosition, ENT_QUOTES, 'UTF-8') ?>" style="background-image:url('<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>');background-position:<?= htmlspecialchars($coverPosition, ENT_QUOTES, 'UTF-8') ?>;"></div>
              <div id="bpCoverPreviewPlaceholder" class="bp-cover-placeholder d-none">No cover uploaded yet</div>
            </div>
          </div>
          <div class="bp-cover-help mb-3"><i class="fa-solid fa-up-down-left-right"></i> Drag the image to adjust banner position, then save.</div>
          <div class="bp-cover-pos-row mb-3">
            <button type="button" class="btn btn-outline-light btn-sm" id="bpCoverChangeBtn"><i class="fa-solid fa-arrow-left me-1"></i>Change image</button>
            <div class="bp-cover-pos-readout" id="bpCoverPositionReadout"><?= htmlspecialchars($coverPosition, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <input type="hidden" name="cover_position" id="bp_cover_position" value="<?= htmlspecialchars($coverPosition, ENT_QUOTES, 'UTF-8') ?>">
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="bp_image_url_cover">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="bpCoverSubmit" type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  function bindIconUpload(){
    const modal=document.getElementById('upload-icon-modal');
    const input=document.getElementById('bp_image_url_icon');
    const preview=document.getElementById('bpIconPreview');
    const submit=document.getElementById('bpIconSubmit');
    const dz=document.querySelector('#upload-icon-modal .bp-dropzone');
    if(!modal||!input||!preview||!submit||!dz) return;
    const defaultSrc=preview.getAttribute('src')||'';
    function setFile(file){
      if(!file||!file.type||!file.type.startsWith('image/')) return;
      const dt=new DataTransfer();dt.items.add(file);input.files=dt.files;
      preview.src=URL.createObjectURL(file);preview.style.display='';submit.disabled=false;
    }
    input.addEventListener('change',function(){ if(input.files&&input.files[0]) setFile(input.files[0]); });
    dz.addEventListener('dragover',function(e){ e.preventDefault();dz.style.borderColor='rgba(92,74,227,.9)'; });
    dz.addEventListener('dragleave',function(){ dz.style.borderColor='rgba(255,255,255,.22)'; });
    dz.addEventListener('drop',function(e){ e.preventDefault();dz.style.borderColor='rgba(255,255,255,.22)';const f=e.dataTransfer.files&&e.dataTransfer.files[0];if(f) setFile(f); });
    modal.addEventListener('hidden.bs.modal',function(){ input.value='';submit.disabled=true;preview.src=defaultSrc;preview.style.display=defaultSrc?'':'none'; });
  }
  function bindCoverUpload(){
    const modal=document.getElementById('upload-cover-modal');
    const input=document.getElementById('bp_image_url_cover');
    const preview=document.getElementById('bpCoverPreview');
    const placeholder=document.getElementById('bpCoverPreviewPlaceholder');
    const submit=document.getElementById('bpCoverSubmit');
    const hidden=document.getElementById('bp_cover_position');
    const readout=document.getElementById('bpCoverPositionReadout');
    const changeBtn=document.getElementById('bpCoverChangeBtn');
    if(!modal||!input||!preview||!submit||!hidden||!readout||!changeBtn) return;
    const defaultSrc=preview.getAttribute('data-default-src')||'';
    const defaultPosition=preview.getAttribute('data-default-position')||'50% 50%';
    let posX=50,posY=50,drag=null;
    function clamp(v,min,max){return Math.max(min,Math.min(max,v));}
    function parsePosition(value){const m=String(value||'').match(/(-?\d+(?:\.\d+)?)%\s+(-?\d+(?:\.\d+)?)%/);if(!m)return{x:50,y:50};return{x:clamp(parseFloat(m[1])||50,0,100),y:clamp(parseFloat(m[2])||50,0,100)};}
    function value(){return posX.toFixed(0)+'% '+posY.toFixed(0)+'%';}
    function sync(){const v=value();preview.style.backgroundPosition=v;hidden.value=v;readout.textContent=v;}
    function show(src){preview.style.backgroundImage=src?'url("'+src.replace(/"/g,'\\"')+'")':'';preview.classList.toggle('is-visible',!!src);placeholder.classList.toggle('d-none',!!src);}
    function setFile(file){if(!file||!file.type||!file.type.startsWith('image/')) return;const dt=new DataTransfer();dt.items.add(file);input.files=dt.files;show(URL.createObjectURL(file));submit.disabled=false;}
    function reset(){input.value='';const p=parsePosition(defaultPosition);posX=p.x;posY=p.y;sync();show(defaultSrc);submit.disabled=false;}
    input.addEventListener('change',function(){ if(input.files&&input.files[0]) setFile(input.files[0]); });
    changeBtn.addEventListener('click',function(){ input.click(); });
    preview.addEventListener('pointerdown',function(e){ if(!preview.classList.contains('is-visible')) return;drag={startX:e.clientX,startY:e.clientY,x:posX,y:posY,w:Math.max(preview.clientWidth,1),h:Math.max(preview.clientHeight,1)};preview.classList.add('is-dragging');if(preview.setPointerCapture) preview.setPointerCapture(e.pointerId); });
    preview.addEventListener('pointermove',function(e){ if(!drag) return;posX=clamp(drag.x+((e.clientX-drag.startX)/drag.w)*100,0,100);posY=clamp(drag.y+((e.clientY-drag.startY)/drag.h)*100,0,100);sync();submit.disabled=false; });
    function stop(e){ if(!drag) return;drag=null;preview.classList.remove('is-dragging');if(preview.releasePointerCapture){try{preview.releasePointerCapture(e.pointerId)}catch(err){}} }
    preview.addEventListener('pointerup',stop);preview.addEventListener('pointercancel',stop);preview.addEventListener('lostpointercapture',function(){drag=null;preview.classList.remove('is-dragging');});
    modal.addEventListener('hidden.bs.modal',reset);reset();
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',function(){bindIconUpload();bindCoverUpload();}); else {bindIconUpload();bindCoverUpload();}
})();
</script>
<?php
    }
}
