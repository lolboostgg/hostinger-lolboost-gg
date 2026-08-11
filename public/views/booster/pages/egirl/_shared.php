<?php
// ── Active tab detection ──────────────────────────────────────────
if (!isset($egSharedActiveTab) || !is_string($egSharedActiveTab) || $egSharedActiveTab === '') {
    $uri = strtolower((string)($_SERVER['REQUEST_URI'] ?? ''));
    if (strpos($uri, 'services') !== false)        $egSharedActiveTab = 'services';
    elseif (strpos($uri, 'profile') !== false)     $egSharedActiveTab = 'profile';
    else                                            $egSharedActiveTab = 'overview';
}

// ── Data helpers ──────────────────────────────────────────────────
$egCoverRaw = trim((string)(BOOSTER_DATA['cover'] ?? ''));
$egCoverUrl = '';
if ($egCoverRaw !== '') {
    $egCoverUrl = preg_match('~^https?://~i', $egCoverRaw)
        ? $egCoverRaw
        : rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($egCoverRaw, '/');
}

$egAvatarRaw = trim((string)(BOOSTER_DATA['icon'] ?? ''));
$egAvatarSrc = $egAvatarRaw !== ''
    ? (preg_match('~^https?://~i', $egAvatarRaw) ? $egAvatarRaw : rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($egAvatarRaw, '/'))
    : '';
$egAvatarLetter = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)(BOOSTER_DATA['username'] ?? 'E')) ?: 'E', 0, 1));

global $db;
$egCompleted = (int)($db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'COMPLETED'", BOOSTER_ID) ?? 0);
$egOngoing   = (int)($db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'IN_PROGRESS'", BOOSTER_ID) ?? 0);
$egBalance   = number_format((int)(BOOSTER_DATA['balance'] ?? 0) / 100, 2);
$egIsOnline  = !empty(BOOSTER_DATA['is_online']);
?>

<style>
/* ════════════════════════════════════════════════════
   GG-GIRL DASHBOARD — SHARED HERO + DESIGN TOKENS
   ════════════════════════════════════════════════════ */
:root {
  --eg-border:  var(--bs-border-color);
  --eg-bg:      var(--bs-card-bg);
  --eg-text:    #ffffff;
  --eg-muted:   rgba(255,255,255,.70);
  --eg-accent:  var(--bs-primary);
  --eg-success: var(--bs-success);
}

/* ── Hero card ── */
.eg-hero-card {
  border-radius: var(--bs-card-border-radius) !important;
  overflow: hidden;
  border: 1px solid var(--bs-border-color) !important;
  background: var(--bs-card-bg) !important;
  box-shadow: var(--bs-card-box-shadow);
  margin-bottom: 1.5rem;
}

/* ── Banner ── */
.eg-hero-banner {
  min-height: 160px;
  background-color: var(--bs-card-bg);
  background-image: linear-gradient(180deg, rgba(255,255,255,.03) 0%, rgba(255,255,255,.01) 100%);
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid var(--bs-border-color);
}
.eg-hero-banner::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(30,32,34,.45) 100%);
  pointer-events: none;
}
.eg-hero-banner.no-cover::before,
.eg-hero-heart {
  display: none !important;
}

/* Banner edit button */
.eg-banner-edit-btn {
  position: absolute; bottom: 12px; right: 14px; z-index: 10;
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .4rem .9rem; font-size: .8rem; font-weight: 800;
  background: rgba(0,0,0,.45); border: 1px solid rgba(255,255,255,.18);
  border-radius: 8px; color: rgba(255,255,255,.85); cursor: pointer;
  backdrop-filter: blur(8px);
  transition: background .15s, border-color .15s, color .15s;
}
.eg-banner-edit-btn:hover {
  background: rgba(255,255,255,.10);
  border-color: rgba(255,255,255,.25);
  color: #fff;
}

/* ── Hero body ── */
.eg-hero-body { padding: 1rem 1.5rem 1rem; }

/* ── Avatar ── */
.eg-avatar-wrap {
  position: relative; cursor: pointer;
  margin-top: -40px; flex-shrink: 0;
  display: inline-block;
}
.eg-avatar {
  width: 80px; height: 80px; border-radius: 50%;
  border: 3px solid var(--bs-card-bg);
  box-shadow: 0 0 0 1px var(--bs-border-color);
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem; font-weight: 800; color: #fff;
  overflow: hidden; transition: transform .12s ease;
}
.eg-avatar-wrap:hover .eg-avatar { transform: translateY(-2px); }
.eg-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.eg-avatar-edit {
  position: absolute; bottom: 2px; right: 2px;
  width: 22px; height: 22px; border-radius: 50%;
  background: var(--bs-primary); border: 2px solid var(--bs-card-bg);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .6rem; pointer-events: none;
}

/* ── Online dot ── */
.eg-online-dot {
  position: absolute; bottom: 4px; left: 4px;
  width: 14px; height: 14px; border-radius: 50%;
  background: var(--eg-success); border: 2px solid var(--bs-card-bg);
  animation: eg-pulse 2s ease-in-out infinite;
}
@keyframes eg-pulse {
  0%,100% { box-shadow: 0 0 6px rgba(34,197,94,.7); }
  50%      { box-shadow: 0 0 14px rgba(34,197,94,1); }
}

/* ── Name + role ── */
.eg-hero-name {
  font-size: 1.25rem; font-weight: 800; color: var(--eg-text);
  background: none;
  -webkit-text-fill-color: initial;
}
.eg-hero-sub { font-size: .8rem; color: var(--eg-muted); margin-top: 2px; }

/* ── Stat badges ── */
.eg-stat-badge {
  display: inline-flex; flex-direction: column; align-items: center;
  background: rgba(255,255,255,.02); border: 1px solid var(--bs-border-color);
  border-radius: 12px; padding: .6rem 1rem; min-width: 82px;
}
.eg-stat-val { font-size: 1rem; font-weight: 900; line-height: 1.2; color: var(--eg-text); }
.eg-stat-lbl { font-size: .65rem; font-weight: 700; color: var(--eg-muted); text-transform: uppercase; letter-spacing: .06em; margin-top: .2rem; }

/* ── Tabs ── */
.eg-tabs.nav-tabs {
  border-bottom: 1px solid var(--bs-border-color);
  margin-top: 16px; gap: 0;
}
.eg-tabs .nav-link {
  color: var(--eg-muted); font-weight: 700; font-size: .88rem;
  border: 0; border-bottom: 2px solid transparent;
  background: transparent; padding: .6rem 0; margin-right: 1.75rem;
  display: flex; align-items: center; gap: .4rem;
  transition: color .15s;
}
.eg-tabs .nav-link:hover { color: #fff; }
.eg-tabs .nav-link.active {
  color: #fff; background: transparent;
  border-bottom-color: var(--bs-primary);
}
.eg-tabs .nav-link i { font-size: .82rem; }

/* ── Upload modal dropzone ── */
.eg-dropzone {
  display: block; border: 1px dashed rgba(255,255,255,.18);
  border-radius: 14px; padding: 22px 18px;
  cursor: pointer; background: rgba(255,255,255,.02);
  text-align: center; transition: border-color .12s, background .12s;
}
.eg-dropzone:hover { border-color: rgba(255,255,255,.28); background: rgba(255,255,255,.04); }
.eg-preview-circle { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--bs-border-color); }
.eg-preview-banner { width: 100%; height: 140px; border-radius: 14px; object-fit: cover; border: 1px solid var(--bs-border-color); }
.eg-banner-preview-wrap { border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
.eg-banner-preview-placeholder { aspect-ratio:4/1; min-height:80px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.5rem; color:rgba(255,255,255,.25); font-size:.85rem; background:rgba(255,255,255,.02); }
.eg-banner-preview-placeholder i { font-size:2rem; }

/* ── Reposition stage ── */
.eg-reposition-stage { position:relative;width:100%;aspect-ratio:4/1;min-height:80px;border-radius:12px;overflow:hidden;border:1px solid var(--bs-border-color);cursor:grab;user-select:none;background:var(--bs-card-bg); }
.eg-reposition-stage:active { cursor:grabbing; }
.eg-reposition-img { position:absolute;width:100%;height:100%;object-fit:cover;pointer-events:none; }
.eg-reposition-crosshair { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:32px;height:32px; }
.eg-reposition-crosshair::before,.eg-reposition-crosshair::after { content:'';position:absolute;background:rgba(255,255,255,.55);border-radius:1px; }
.eg-reposition-crosshair::before { width:1px;height:100%;left:50%;top:0; }
.eg-reposition-crosshair::after  { height:1px;width:100%;top:50%;left:0; }
</style>

<!-- ════ HERO CARD ════ -->
<div class="eg-hero-card card border-0 p-0">

  <!-- Banner -->
  <div class="eg-hero-banner <?= $egCoverUrl ? '' : 'no-cover' ?>"
       id="egHeroBanner"
       <?php if ($egCoverUrl): ?>style="background-image:url('<?= htmlspecialchars($egCoverUrl, ENT_QUOTES) ?>')"<?php endif; ?>>
    <?php if (!$egCoverUrl): ?>
      <span class="eg-hero-heart">💜</span>
      <span class="eg-hero-heart">🩷</span>
      <span class="eg-hero-heart">✨</span>
      <span class="eg-hero-heart">💜</span>
      <span class="eg-hero-heart">🩷</span>
    <?php endif; ?>
    <button type="button" class="eg-banner-edit-btn" data-bs-toggle="modal" data-bs-target="#egUploadCoverModal">
      <i class="fa-duotone fa-image me-1"></i>Change Banner
    </button>
  </div>

  <!-- Body -->
  <div class="eg-hero-body">
    <div class="d-flex flex-wrap align-items-flex-start justify-content-between gap-3">

      <!-- Left: Avatar + Name -->
      <div class="d-flex align-items-flex-start gap-3">
        <div class="eg-avatar-wrap" data-bs-toggle="modal" data-bs-target="#egUploadAvatarModal" title="Change profile picture">
          <div class="eg-avatar">
            <?php if ($egAvatarSrc): ?><img src="<?= htmlspecialchars($egAvatarSrc, ENT_QUOTES) ?>" alt=""><?php else: ?><span><?= $egAvatarLetter ?></span><?php endif; ?>
          </div>
          <?php if ($egIsOnline): ?><span class="eg-online-dot"></span><?php endif; ?>
          <span class="eg-avatar-edit"><i class="fa-solid fa-pen"></i></span>
        </div>
        <div class="pt-2">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="eg-hero-name"><?= htmlspecialchars(BOOSTER_DATA['username'] ?? 'GG-Girl', ENT_QUOTES) ?></span>
            <span style="font-size:.7rem;font-weight:800;background:rgba(255,255,255,.04);border:1px solid var(--bs-border-color);color:rgba(255,255,255,.7);border-radius:999px;padding:2px 8px;">
              <i class="fa-solid fa-star" style="color:var(--bs-primary);font-size:.75em;margin-right:3px;"></i>GG-Girl
            </span>
          </div>
          <div class="eg-hero-sub"><i class="fa-solid fa-hashtag me-1"></i><?= (int)BOOSTER_ID ?></div>
        </div>
      </div>

      <!-- Right: Stats -->
      <div class="d-flex flex-wrap gap-2 align-items-center pt-2">
        <div class="eg-stat-badge">
          <span class="eg-stat-val"><?= $egCompleted ?></span>
          <span class="eg-stat-lbl">Completed</span>
        </div>
        <?php if ($egOngoing > 0): ?>
        <div class="eg-stat-badge" style="border-color:rgba(0,201,167,.25);background:rgba(0,201,167,.08);">
          <span class="eg-stat-val" style="color:var(--bs-success);"><?= $egOngoing ?></span>
          <span class="eg-stat-lbl">Ongoing</span>
        </div>
        <?php endif; ?>
        <div class="eg-stat-badge">
          <span class="eg-stat-val"><?= $egBalance ?> €</span>
          <span class="eg-stat-lbl">Available for Payout</span>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <?php if (!isset($egSharedHideNav) || !$egSharedHideNav): ?>
    <ul class="nav nav-tabs align-items-center eg-tabs mb-0">
      <li class="nav-item">
        <a class="nav-link <?= $egSharedActiveTab === 'overview' ? 'active' : '' ?>" href="<?= BASE_URL ?>/booster-area/egirl-dashboard">
          <i class="fa-solid fa-gauge-high"></i> Overview
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $egSharedActiveTab === 'services' ? 'active' : '' ?>" href="<?= BASE_URL ?>/booster-area/egirl-services">
          <i class="fa-solid fa-layer-group"></i> Services
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $egSharedActiveTab === 'profile' ? 'active' : '' ?>" href="<?= BASE_URL ?>/booster-area/egirl-profile">
          <i class="fa-solid fa-user-pen"></i> Profile
        </a>
      </li>
    </ul>
    <?php endif; ?>
  </div>
</div>
<!-- ════ END HERO CARD ════ -->

<!-- ── Avatar Upload Modal ── -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="action" value="booster_upload_profile_picture">
  <div class="modal fade" id="egUploadAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;width:calc(100% - 2rem);">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-image me-2"></i>Change Profile Picture</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div style="flex-shrink:0;">
              <?php if ($egAvatarSrc): ?>
                <img id="egAvatarPreview" src="<?= htmlspecialchars($egAvatarSrc, ENT_QUOTES) ?>" class="eg-preview-circle">
              <?php else: ?>
                <div id="egAvatarPreviewFallback" style="width:60px;height:60px;border-radius:50%;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:2px solid var(--bs-border-color);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#fff;"><?= $egAvatarLetter ?></div>
              <?php endif; ?>
            </div>
            <div>
              <div style="font-weight:900;color:rgba(255,255,255,.92);">Upload your file</div>
              <div style="font-size:.88rem;color:rgba(255,255,255,.5);">PNG / JPG / WEBP — max 5 MB</div>
            </div>
          </div>
          <label class="eg-dropzone" for="egAvatarFile">
            <i class="fa-duotone fa-cloud-arrow-up" style="font-size:1.6rem;color:var(--bs-primary);display:block;margin-bottom:7px;"></i>
            <div style="font-weight:900;color:rgba(255,255,255,.92);">Drag &amp; drop or click to choose</div>
            <div style="font-size:.83rem;color:rgba(255,255,255,.5);margin-top:4px;">Recommended: square image</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="egAvatarFile">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="egAvatarSubmit" type="submit" class="btn btn-primary" disabled>
            <i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- ── Cover/Banner Upload Modal ── -->
<div class="modal fade" id="egUploadCoverModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px;width:calc(100% - 2rem);">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-panorama me-2"></i>Change Banner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Step 1: Upload -->
        <div id="egCoverStep1">
          <div class="eg-banner-preview-wrap mb-4" id="egCoverPreviewWrap">
            <?php if ($egCoverUrl): ?>
              <img src="<?= htmlspecialchars($egCoverUrl, ENT_QUOTES) ?>" class="eg-preview-banner" style="object-position:center;">
            <?php else: ?>
              <div class="eg-banner-preview-placeholder"><i class="fa-duotone fa-panorama"></i><span>No banner set</span></div>
            <?php endif; ?>
          </div>
          <label class="eg-dropzone" for="egCoverFile">
            <i class="fa-duotone fa-cloud-arrow-up" style="font-size:1.6rem;color:var(--bs-primary);display:block;margin-bottom:7px;"></i>
            <div style="font-weight:900;color:rgba(255,255,255,.92);">Drag &amp; drop or click to choose</div>
            <div style="font-size:.83rem;color:rgba(255,255,255,.5);margin-top:4px;">PNG / JPG / WEBP — Recommended: 1400×350px</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" id="egCoverFile">
        </div>
        <!-- Step 2: Reposition -->
        <div id="egCoverStep2" style="display:none;">
          <div style="font-size:.82rem;color:rgba(255,255,255,.55);font-weight:600;display:flex;align-items:center;margin-bottom:.75rem;">
            <i class="fa-duotone fa-arrows-up-down-left-right me-2"></i>Drag to adjust position, then save.
          </div>
          <div class="eg-reposition-stage" id="egReposStage">
            <img id="egReposImg" src="" alt="" class="eg-reposition-img" draggable="false">
            <div style="position:absolute;inset:0;pointer-events:none;"><div class="eg-reposition-crosshair"></div></div>
          </div>
          <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-sm" id="egCoverBack"
              style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.85);">
              <i class="fa-solid fa-arrow-left me-1"></i>Change image
            </button>
            <span class="text-muted small ms-auto" id="egReposCoords">50% 50%</span>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="gap:8px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="egCoverNextBtn"  type="button" class="btn btn-primary" disabled style="display:none;"><i class="fa-solid fa-arrow-right me-1"></i>Adjust Position</button>
        <button id="egCoverSavePos" type="button" class="btn btn-primary" style="display:none;background:var(--bs-primary);border-color:var(--bs-primary);"><i class="fa-duotone fa-crosshairs me-1"></i>Save Position</button>
        <button id="egCoverSaveAll" type="button" class="btn btn-success" style="display:none;"><i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Hidden forms -->
<form class="ajax-form" id="egCoverUploadForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" style="display:none;">
  <input type="hidden" name="action" value="booster_upload_cover">
  <input type="hidden" name="banner_position" id="egCoverPosInput" value="50% 50%">
  <input type="file" name="image_url" id="egCoverFileTransfer" accept="image/*">
</form>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // ── Avatar upload ──
  (function(){
    var input  = document.getElementById('egAvatarFile');
    var submit = document.getElementById('egAvatarSubmit');
    if(!input||!submit) return;
    function applyFile(file){
      if(!file||!file.type.startsWith('image/')) return;
      try{ var dt=new DataTransfer(); dt.items.add(file); input.files=dt.files; }catch(e){}
      var img=document.getElementById('egAvatarPreview');
      var fb =document.getElementById('egAvatarPreviewFallback');
      if(img){ img.src=URL.createObjectURL(file); img.classList.remove('d-none'); }
      if(fb)  fb.classList.add('d-none');
      submit.disabled=false;
    }
    input.addEventListener('change',function(){ if(input.files&&input.files[0]) applyFile(input.files[0]); });
    var dz=document.querySelector('label[for="egAvatarFile"]');
    if(dz){
      dz.addEventListener('click',    function(e){ e.preventDefault(); input.click(); });
      dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='rgba(168,85,247,.9)'; });
      dz.addEventListener('dragleave',function(){  dz.style.borderColor=''; });
      dz.addEventListener('drop',     function(e){ e.preventDefault(); dz.style.borderColor=''; var f=e.dataTransfer.files&&e.dataTransfer.files[0]; if(f) applyFile(f); });
    }
  })();

  // ── Banner/Cover upload + reposition ──
  (function(){
    var modalEl     = document.getElementById('egUploadCoverModal');
    var fileInput   = document.getElementById('egCoverFile');
    var step1       = document.getElementById('egCoverStep1');
    var step2       = document.getElementById('egCoverStep2');
    var nextBtn     = document.getElementById('egCoverNextBtn');
    var backBtn     = document.getElementById('egCoverBack');
    var saveAllBtn  = document.getElementById('egCoverSaveAll');
    var savePosBtn  = document.getElementById('egCoverSavePos');
    var reposStage  = document.getElementById('egReposStage');
    var reposImg    = document.getElementById('egReposImg');
    var coordsLbl   = document.getElementById('egReposCoords');
    var uploadForm  = document.getElementById('egCoverUploadForm');
    var posInput    = document.getElementById('egCoverPosInput');
    var fileTransfer= document.getElementById('egCoverFileTransfer');
    var heroBanner  = document.getElementById('egHeroBanner');
    if(!modalEl) return;

    var currentPos={x:50,y:50}, selectedFile=null, isDragging=false;
    var dragStart={x:0,y:0}, posAtStart={x:50,y:50};
    var phpCoverUrl='<?= addslashes($egCoverUrl) ?>';

    function posStr(){ return Math.round(currentPos.x)+'% '+Math.round(currentPos.y)+'%'; }
    function applyPosToImg(){ if(reposImg) reposImg.style.objectPosition=posStr(); if(coordsLbl) coordsLbl.textContent=posStr(); }
    function hasCover(){ return !!(phpCoverUrl||(heroBanner&&heroBanner.style.backgroundImage&&heroBanner.style.backgroundImage!=='none')); }
    function getCoverSrc(){
      if(heroBanner&&heroBanner.style.backgroundImage&&heroBanner.style.backgroundImage!=='none'&&heroBanner.style.backgroundImage!==''){
        return heroBanner.style.backgroundImage.replace(/^url\(["']?/,'').replace(/["']?\)$/,'');
      }
      return phpCoverUrl;
    }

    function goStep1(){
      if(step1) step1.style.display='';
      if(step2) step2.style.display='none';
      if(nextBtn){ nextBtn.style.display=selectedFile?'':'none'; nextBtn.disabled=false; }
      if(saveAllBtn) saveAllBtn.style.display='none';
      if(savePosBtn) savePosBtn.style.display=hasCover()?'':'none';
      if(hasCover()){ var src=getCoverSrc(); if(reposImg&&src){ reposImg.src=src; applyPosToImg(); } }
    }
    function goStep2(src){
      if(reposImg){ reposImg.src=src; reposImg.style.objectPosition=posStr(); }
      if(coordsLbl) coordsLbl.textContent=posStr();
      if(step1) step1.style.display='none';
      if(step2) step2.style.display='';
      if(nextBtn) nextBtn.style.display='none';
      if(saveAllBtn) saveAllBtn.style.display=selectedFile?'':'none';
      if(savePosBtn) savePosBtn.style.display=hasCover()?'':'none';
    }
    function applyFile(file){
      if(!file||!file.type.startsWith('image/')) return;
      selectedFile=file;
      var url=URL.createObjectURL(file);
      var pw=document.getElementById('egCoverPreviewWrap');
      if(pw) pw.innerHTML='<img src="'+url+'" class="eg-preview-banner" style="object-fit:cover;">';
      if(nextBtn){ nextBtn.style.display=''; nextBtn.disabled=false; }
    }
    if(fileInput) fileInput.addEventListener('change',function(){ if(fileInput.files[0]) applyFile(fileInput.files[0]); });
    var dz=document.querySelector('label[for="egCoverFile"]');
    if(dz){
      dz.addEventListener('click',    function(e){ e.preventDefault(); fileInput.click(); });
      dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='rgba(168,85,247,.9)'; });
      dz.addEventListener('dragleave',function(){  dz.style.borderColor=''; });
      dz.addEventListener('drop',     function(e){ e.preventDefault(); dz.style.borderColor=''; var f=e.dataTransfer.files&&e.dataTransfer.files[0]; if(f) applyFile(f); });
    }
    if(nextBtn) nextBtn.addEventListener('click',function(){ if(selectedFile) goStep2(URL.createObjectURL(selectedFile)); });
    if(backBtn) backBtn.addEventListener('click', goStep1);

    // Drag reposition
    function startDrag(cx,cy){ isDragging=true; dragStart={x:cx,y:cy}; posAtStart={x:currentPos.x,y:currentPos.y}; }
    function moveDrag(cx,cy){
      if(!isDragging||!reposStage||!reposImg) return;
      var sw=reposStage.offsetWidth, sh=reposStage.offsetHeight;
      var iw=reposImg.naturalWidth||sw*1.5, ih=reposImg.naturalHeight||sh*1.5;
      var sc=Math.max(sw/iw,sh/ih), rw=iw*sc, rh=ih*sc;
      currentPos.x=Math.max(0,Math.min(100,posAtStart.x-(cx-dragStart.x)/Math.max(rw-sw,1)*100));
      currentPos.y=Math.max(0,Math.min(100,posAtStart.y-(cy-dragStart.y)/Math.max(rh-sh,1)*100));
      applyPosToImg();
    }
    function endDrag(){ isDragging=false; }
    if(reposStage){
      reposStage.addEventListener('mousedown',function(e){ e.preventDefault(); startDrag(e.clientX,e.clientY); });
      window.addEventListener('mousemove',function(e){ if(isDragging) moveDrag(e.clientX,e.clientY); });
      window.addEventListener('mouseup', endDrag);
      reposStage.addEventListener('touchstart',function(e){ var t=e.touches[0]; startDrag(t.clientX,t.clientY); },{passive:true});
      reposStage.addEventListener('touchmove', function(e){ var t=e.touches[0]; moveDrag(t.clientX,t.clientY); e.preventDefault(); },{passive:false});
      reposStage.addEventListener('touchend', endDrag);
    }
    if(saveAllBtn){
      saveAllBtn.addEventListener('click',function(){
        if(!selectedFile||!uploadForm||!posInput||!fileTransfer) return;
        posInput.value=posStr();
        try{ var dt=new DataTransfer(); dt.items.add(selectedFile); fileTransfer.files=dt.files; }catch(e){}
        if(heroBanner){ heroBanner.style.backgroundImage='url('+URL.createObjectURL(selectedFile)+')'; heroBanner.style.backgroundPosition=posStr(); }
        if(typeof uploadForm.requestSubmit==='function') uploadForm.requestSubmit(); else uploadForm.submit();
      });
    }
    if(savePosBtn){
      savePosBtn.addEventListener('click',function(){
        // Save position only via AJAX directly
        if(heroBanner) heroBanner.style.backgroundPosition=posStr();
        var bm=typeof bootstrap!=='undefined'&&bootstrap.Modal?bootstrap.Modal.getInstance(modalEl):null;
        if(bm) bm.hide();
      });
    }
    if(modalEl){
      modalEl.addEventListener('show.bs.modal',function(){
        goStep1();
        if(hasCover()&&!selectedFile) goStep2(getCoverSrc());
      });
      modalEl.addEventListener('hidden.bs.modal',function(){
        selectedFile=null; isDragging=false;
        if(fileInput) fileInput.value='';
        goStep1();
      });
    }
  })();
});
</script>
