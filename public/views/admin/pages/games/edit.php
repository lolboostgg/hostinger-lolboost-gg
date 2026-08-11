<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<style>
/* ══ Game Edit — dashboard-native style ══ */
.ge-page { padding: 24px 0 64px; width:100%; max-width:none; }

/* Toast */
#geToast { position:fixed; bottom:24px; right:24px; z-index:99999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
.ge-toast { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; min-width:220px; max-width:340px; background:var(--bs-body-bg,#1e2130); border:1px solid rgba(255,255,255,.1); box-shadow:0 8px 28px rgba(0,0,0,.4); font-size:13px; font-weight:600; color:#fff; pointer-events:auto; animation:geIn .18s ease; }
.ge-toast.out { animation:geOut .18s ease forwards; }
.ge-toast i { font-size:14px; flex-shrink:0; }
.ge-toast--success i { color:#4ade80; }
.ge-toast--error   i { color:#f87171; }
.ge-toast--info    i { color:#818cf8; }
@keyframes geIn  { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
@keyframes geOut { to{opacity:0;transform:translateY(10px)} }

/* Game hero header */
.ge-hero { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.ge-hero__left { display:flex; align-items:center; gap:14px; }
.ge-hero__icon { width:48px; height:48px; border-radius:12px; overflow:hidden; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ge-hero__icon img { width:34px; height:34px; object-fit:contain; display:block; }
.ge-hero__fallback { font-size:11px; font-weight:800; display:none; }
.ge-hero__name { font-size:20px; font-weight:800; color:#fff; margin:0; line-height:1.1; }
.ge-hero__slug { font-size:12px; color:rgba(255,255,255,.38); font-family:monospace; margin-top:2px; }
.ge-hero__right { display:flex; align-items:center; gap:12px; }
.ge-live-label { font-size:13px; color:rgba(255,255,255,.5); font-weight:600; }

/* Two-column layout */
.ge-cols { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:20px; align-items:start; }
.ge-cols > div { min-width:0; }
@media(max-width:1100px) { .ge-cols { grid-template-columns:1fr; } }

/* Service cards */
.ge-svcs { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.ge-svc {
  display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:10px;
  border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03);
  cursor:pointer; user-select:none; transition:all .15s;
}
.ge-svc.on { background:rgba(99,102,241,.1); border-color:rgba(129,140,248,.3); }
.ge-svc.on.svc-accounts { background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.28); }
.ge-svc.on.svc-items    { background:rgba(245,158,11,.08); border-color:rgba(245,158,11,.28); }
.ge-svc.on.svc-topups  { background:rgba(59,130,246,.08); border-color:rgba(59,130,246,.32); }
.ge-svc.on.svc-coaching { background:rgba(59,130,246,.08); border-color:rgba(96,165,250,.28); }
.ge-svc.on.svc-egirl    { background:rgba(244,63,94,.08);  border-color:rgba(244,63,94,.28); }
.ge-svc:hover           { border-color:rgba(255,255,255,.15); }
.ge-svc__ico { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.svc-boosting .ge-svc__ico { background:rgba(99,102,241,.18); color:#a5b4fc; }
.svc-accounts .ge-svc__ico { background:rgba(34,197,94,.16);  color:#86efac; }
.svc-items    .ge-svc__ico { background:rgba(245,158,11,.16); color:#fcd34d; }
.svc-topups  .ge-svc__ico { background:rgba(59,130,246,.16); color:#93c5fd; }
.svc-coaching .ge-svc__ico { background:rgba(59,130,246,.16); color:#93c5fd; }
.svc-egirl    .ge-svc__ico { background:rgba(244,63,94,.14);  color:#fda4af; }
.ge-svc__body { flex:1; min-width:0; }
.ge-svc__name { font-size:13px; font-weight:800; color:#fff; }
.ge-svc__desc { font-size:11px; color:rgba(255,255,255,.38); margin-top:1px; }
.ge-svc__route { font-size:10px; color:rgba(255,255,255,.25); font-family:monospace; display:block; margin-top:2px; }
.ge-svc__spin { width:14px; height:14px; border:2px solid rgba(255,255,255,.12); border-top-color:#818cf8; border-radius:50%; animation:spin .6s linear infinite; display:none; flex-shrink:0; }
.ge-svc.saving .ge-svc__spin { display:block; }


/* Clear service enabled, disabled states */
.ge-svc{position:relative; padding-right:118px!important; opacity:.58; filter:grayscale(.75);}
.ge-svc:not(.on){background:rgba(15,23,42,.52)!important;border-color:rgba(148,163,184,.16)!important;}
.ge-svc:not(.on) .ge-svc__ico{background:rgba(148,163,184,.10)!important;color:#64748b!important;}
.ge-svc:not(.on) .ge-svc__name{color:#94a3b8!important;}
.ge-svc:not(.on) .ge-svc__desc,.ge-svc:not(.on) .ge-svc__route{color:rgba(148,163,184,.42)!important;}
.ge-svc.on{opacity:1;filter:none;outline:1px solid rgba(34,197,94,.35);}
.ge-svc.on:before{content:"";position:absolute;left:0;top:10px;bottom:10px;width:4px;border-radius:0 999px 999px 0;background:#22c55e;box-shadow:0 0 18px rgba(34,197,94,.75);}
.ge-svc:not(.on):before{content:"";position:absolute;left:0;top:10px;bottom:10px;width:4px;border-radius:0 999px 999px 0;background:#475569;}
.ge-svc__state{position:absolute;right:14px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;gap:7px;height:28px;padding:0 10px;border-radius:999px;font-size:10px;font-weight:950;letter-spacing:.075em;text-transform:uppercase;border:1px solid transparent;}
.ge-svc__state .dot{width:7px;height:7px;border-radius:999px;}
.ge-svc .state-on{display:none;background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.32);color:#86efac;}
.ge-svc .state-on .dot{background:#22c55e;box-shadow:0 0 12px rgba(34,197,94,.9);}
.ge-svc .state-off{display:inline-flex;background:rgba(100,116,139,.14);border-color:rgba(148,163,184,.18);color:#94a3b8;}
.ge-svc .state-off .dot{background:#64748b;}
.ge-svc.on .state-on{display:inline-flex;}
.ge-svc.on .state-off{display:none;}
.ge-svc:hover .ge-svc__state{transform:translateY(-50%) scale(1.02);}
@media(max-width:700px){.ge-svc{padding-right:16px!important;padding-bottom:48px!important}.ge-svc__state{left:70px;right:auto;top:auto;bottom:12px;transform:none}.ge-svc:hover .ge-svc__state{transform:none}}

@keyframes spin { to{transform:rotate(360deg)} }

/* Tag input */
.ge-tw { display:flex; flex-wrap:wrap; gap:6px; padding:9px 12px; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:rgba(255,255,255,.04); min-height:44px; align-items:center; cursor:text; transition:border-color .18s; }
.ge-tw:focus-within { border-color:rgba(129,140,248,.45); }
.ge-tag { display:inline-flex; align-items:center; gap:4px; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; background:rgba(99,102,241,.2); color:#c7d2fe; border:1px solid rgba(129,140,248,.28); }
.ge-tag__x { border:none; background:none; padding:0; color:inherit; opacity:.6; cursor:pointer; font-size:12px; line-height:1; }
.ge-tag__x:hover { opacity:1; }
.ge-tw-inp { border:none; outline:none; background:transparent; color:#fff; font-size:12px; flex:1; min-width:60px; }
.ge-tw-inp::placeholder { color:rgba(255,255,255,.25); }
.ge-presets { display:flex; flex-wrap:wrap; gap:4px; margin-top:6px; }
.ge-preset { padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; cursor:pointer; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.55); transition:all .12s; }
.ge-preset:hover { background:rgba(255,255,255,.1); color:#fff; }
.ge-preset.on { background:rgba(99,102,241,.2); color:#a5b4fc; border-color:rgba(129,140,248,.3); }

/* Sidebar stat card */
.ge-stat-card { border-radius:12px; padding:16px 18px; margin-bottom:14px; }
.ge-stat-card__label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.35); margin-bottom:8px; display:flex; align-items:center; gap:6px; }
.ge-stat-card__label i { font-size:12px; }

/* Account builder bottom layout */
#accountSchemaCard { width:100%; max-width:none; margin-top:20px; }
.acs-builder-card { width:100%; }
.acs-preview-shell { width:100%; }

.ge-icon-drop { border:1px dashed rgba(139,92,246,.45); background:rgba(139,92,246,.08); border-radius:12px; padding:14px; cursor:pointer; transition:.15s ease; display:flex; align-items:center; gap:12px; min-height:74px; }
.ge-icon-drop.dragover { border-color:#a78bfa; background:rgba(139,92,246,.18); transform:translateY(-1px); }
.ge-icon-drop__thumb { width:46px; height:46px; border-radius:12px; background:rgba(0,0,0,.22); border:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; overflow:hidden; flex:0 0 46px; }
.ge-icon-drop__thumb img { width:34px; height:34px; object-fit:contain; display:block; }
.ge-icon-drop__main { min-width:0; }
.ge-icon-drop__title { color:#fff; font-weight:800; font-size:13px; line-height:1.25; }
.ge-icon-drop__text { color:rgba(255,255,255,.55); font-size:12px; margin-top:2px; }
.ge-icon-drop__file { color:#c4b5fd; font-size:11px; margin-top:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
.ge-hidden-file { position:absolute; left:-9999px; width:1px; height:1px; opacity:0; }



/* ── V7 full visual redesign ─────────────────────────────── */
.gc-page,.ge-page{padding:22px 0 80px}.gc-page .breadcrumb,.ge-page .breadcrumb{background:transparent;margin-bottom:14px}.gc-page .card,.ge-page .card{border:1px solid rgba(255,255,255,.075)!important;border-radius:18px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.022))!important;box-shadow:0 18px 45px rgba(0,0,0,.20);overflow:hidden}.gc-page .card-header,.ge-page .card-header{padding:17px 20px!important;border-bottom:1px solid rgba(255,255,255,.07)!important;background:rgba(8,12,18,.28)!important}.gc-page .card-header-title,.ge-page .card-header-title{font-size:14px;font-weight:900;letter-spacing:.01em}.gc-page .card-body,.ge-page .card-body{padding:20px!important}.gc-page .form-control,.gc-page .form-select,.gc-page .input-group-text,.ge-page .form-control,.ge-page .form-select,.ge-page .input-group-text{height:42px;border-radius:12px!important;background:rgba(7,11,17,.48)!important;border:1px solid rgba(255,255,255,.10)!important;color:#f8fafc!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}.gc-page textarea.form-control,.ge-page textarea.form-control{height:auto}.gc-page .form-control:focus,.gc-page .form-select:focus,.ge-page .form-control:focus,.ge-page .form-select:focus{border-color:rgba(124,105,245,.65)!important;box-shadow:0 0 0 3px rgba(124,105,245,.14)!important;background:rgba(7,11,17,.62)!important}.gc-page .form-label,.ge-page .form-label{font-size:11px;text-transform:uppercase;letter-spacing:.075em;color:rgba(255,255,255,.52)!important}.gc-page .form-text,.ge-page .form-text{font-size:11px;color:rgba(255,255,255,.32)!important}.gc-page code,.ge-page code{color:#c4b5fd;background:rgba(124,105,245,.10);border:1px solid rgba(124,105,245,.12);padding:2px 6px;border-radius:7px}.gc-hero-v7{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 18px;padding:22px 24px;border-radius:20px;border:1px solid rgba(124,105,245,.18);background:radial-gradient(circle at 16% 0%,rgba(124,105,245,.25),transparent 34%),linear-gradient(135deg,rgba(124,105,245,.14),rgba(20,184,166,.06)),rgba(255,255,255,.025);box-shadow:0 22px 60px rgba(0,0,0,.24)}.gc-hero-v7__left{display:flex;align-items:center;gap:15px}.gc-hero-v7__badge{width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(124,105,245,.35),rgba(20,184,166,.18));border:1px solid rgba(255,255,255,.10);color:#fff;font-size:22px;box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}.gc-hero-v7__kicker{font-size:11px;font-weight:900;letter-spacing:.13em;text-transform:uppercase;color:#a5b4fc;margin-bottom:4px}.gc-hero-v7__title{margin:0;font-size:25px;font-weight:950;color:#fff;letter-spacing:-.03em}.gc-hero-v7__sub{margin-top:4px;color:rgba(255,255,255,.52);font-size:13px}.gc-hero-v7__actions{display:flex;gap:9px;flex-wrap:wrap}.gc-page .btn,.ge-page .btn{border-radius:12px!important;font-weight:850!important}.gc-icon-drop,.ge-icon-drop{border-radius:16px!important;padding:16px!important;background:linear-gradient(135deg,rgba(124,105,245,.13),rgba(20,184,166,.045))!important;border-color:rgba(124,105,245,.35)!important}.gc-icon-drop:hover,.ge-icon-drop:hover{transform:translateY(-1px);border-color:rgba(167,139,250,.65)!important;background:linear-gradient(135deg,rgba(124,105,245,.20),rgba(20,184,166,.07))!important}.gc-icon-drop__thumb,.ge-icon-drop__thumb{width:54px!important;height:54px!important;border-radius:15px!important;background:rgba(8,12,18,.55)!important}.gc-icon-drop__thumb img,.ge-icon-drop__thumb img{width:40px!important;height:40px!important}.gc-svc,.ge-svc{border-radius:15px!important;background:rgba(8,12,18,.34)!important;border-color:rgba(255,255,255,.08)!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.025)}.gc-svc:hover,.ge-svc:hover{transform:translateY(-1px);border-color:rgba(124,105,245,.28)!important;background:rgba(124,105,245,.07)!important}.gc-svc.on,.ge-svc.on{box-shadow:0 10px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.04)}.gc-svc__ico,.ge-svc__ico{width:42px!important;height:42px!important;border-radius:13px!important}.gc-preview,.ge-stat-card{border-radius:18px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02))!important;border:1px solid rgba(255,255,255,.08)!important;box-shadow:0 18px 45px rgba(0,0,0,.18)}.gc-preview__icon{width:62px!important;height:62px!important;border-radius:18px!important}.gc-preview__name{font-size:21px!important;letter-spacing:-.02em}.ge-hero{padding:6px}.ge-hero__icon{width:62px!important;height:62px!important;border-radius:18px!important;background:linear-gradient(135deg,rgba(124,105,245,.25),rgba(20,184,166,.10))!important}.ge-hero__icon img{width:43px!important;height:43px!important}.ge-hero__name{font-size:25px!important;font-weight:950!important;letter-spacing:-.03em}.ge-svcs{gap:12px!important}.ge-page table tbody tr{border-radius:14px;transition:.14s}.ge-page table tbody tr:hover{background:rgba(124,105,245,.06)}.ge-page table td{padding-top:14px!important;padding-bottom:14px!important}.ge-tw{border-radius:14px!important;background:rgba(8,12,18,.36)!important}.ge-tag,.ge-preset{border-radius:999px!important}.gc-sticky-save,.ge-sticky-save{position:sticky;bottom:16px;z-index:8;margin-top:18px;padding:12px;border-radius:16px;background:rgba(20,23,28,.86);border:1px solid rgba(124,105,245,.24);backdrop-filter:blur(12px);box-shadow:0 18px 45px rgba(0,0,0,.28);display:flex;justify-content:flex-end;gap:10px}
@media(max-width:900px){.gc-hero-v7{align-items:flex-start;flex-direction:column}.gc-hero-v7__title{font-size:21px}.ge-svcs{grid-template-columns:1fr!important}}

</style>

<div id="geToast"></div>

<div class="ge-page">
  <!-- Breadcrumb -->
  <nav class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin-area/games">Games</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($game['name']) ?></li>
    </ol>
  </nav>

  <!-- Hero -->
  <?php
    $_iconUrl = !empty($game['icon']) ? $game['icon'] : '/public/assets/website/images/icons/' . $game['slug'] . '.png';
    $_color   = $game['color_primary'] ?? '#8b5cf6';
    $_short   = strtoupper(substr($game['short_code'] ?: $game['slug'], 0, 3));
  ?>
  <div class="card mb-4">
    <div class="card-body ge-hero">
    <div class="ge-hero__left">
      <div class="ge-hero__icon">
        <img id="gameIconPreview" src="<?= htmlspecialchars($_iconUrl) ?>" alt=""
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <span class="ge-hero__fallback" style="color:<?= htmlspecialchars($_color) ?>"><?= $_short ?></span>
      </div>
      <div>
        <div class="ge-hero__name"><?= htmlspecialchars($game['name']) ?></div>
        <div class="ge-hero__slug">/<?= htmlspecialchars($game['slug']) ?></div>
      </div>
    </div>
    <div class="ge-hero__right">
      <span class="ge-live-label">Live</span>
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" id="toggleLive"
               <?= $game['status'] ? 'checked' : '' ?> data-game="<?= (int)$game['id'] ?>">
      </div>
      <a href="/<?= htmlspecialchars($game['slug']) ?>" target="_blank"
         class="btn btn-ghost-secondary btn-sm">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview
      </a>
    </div>
    </div>
  </div>

  <!-- Two-column layout -->
  <div class="ge-cols">

    <!-- LEFT: main content -->
    <div>

      <!-- Game Settings -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-header-title"><i class="fa-solid fa-sliders me-2"></i>Game Settings</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold small">Game Name</label>
              <input type="text" class="form-control" id="fName" value="<?= htmlspecialchars($game['name']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Nav Label</label>
              <input type="text" class="form-control" id="fCode" maxlength="6" value="<?= htmlspecialchars($game['short_code'] ?? '') ?>" placeholder="e.g. LoL">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold small">Sort Order</label>
              <input type="number" class="form-control text-center" id="fSort" value="<?= (int)($game['sort_order'] ?? 0) ?>" min="0">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold small">Colors</label>
              <div class="d-flex gap-2 align-items-center">
                <div class="gc-swatch-wrap">
                  <div class="gc-swatch" id="swatchP" style="background:<?= htmlspecialchars($game['color_primary'] ?? '#8b5cf6') ?>" onclick="document.getElementById('fColorP').click()"></div>
                  <input type="color" id="fColorP" value="<?= htmlspecialchars($game['color_primary'] ?? '#8b5cf6') ?>" oninput="gcSyncSwatch('fColorP','swatchP','fColorPHex')" style="opacity:0;position:absolute;width:0;height:0;">
                </div>
                <div class="gc-swatch-wrap">
                  <div class="gc-swatch" id="swatchA" style="background:<?= htmlspecialchars($game['color_accent'] ?? '#a78bfa') ?>" onclick="document.getElementById('fColorA').click()"></div>
                  <input type="color" id="fColorA" value="<?= htmlspecialchars($game['color_accent'] ?? '#a78bfa') ?>" oninput="gcSyncSwatch('fColorA','swatchA','fColorAHex')" style="opacity:0;position:absolute;width:0;height:0;">
                </div>
              </div>
            </div>

            <div class="col-md-5">
              <label class="form-label fw-semibold small">Game Icon</label>
              <input type="file" class="ge-hidden-file" id="fIconFile" accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml">
              <div class="ge-icon-drop" id="gameIconDrop" tabindex="0" role="button" aria-label="Upload game icon">
                <div class="ge-icon-drop__thumb">
                  <img id="gameIconDropPreview" src="<?= htmlspecialchars($_iconUrl) ?>" alt="">
                </div>
                <div class="ge-icon-drop__main">
                  <div class="ge-icon-drop__title">Click, drag an icon here, or press Ctrl V</div>
                  <div class="ge-icon-drop__text">PNG, JPG, WebP or SVG, max 2 MB.</div>
                  <div class="ge-icon-drop__file" id="gameIconFileName">No new file selected.</div>
                </div>
              </div>
              <div class="form-text">Upload goes to <code>/public_html/public/assets/website/images/icons/</code>. Leave empty to keep current icon.</div>
            </div>

            <div class="col-md-7">
              <label class="form-label fw-semibold small">Game Banner</label>
              <input type="file" class="ge-hidden-file" id="fBannerFile" accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml">
              <div class="ge-icon-drop" id="gameBannerDrop" tabindex="0" role="button" aria-label="Upload game banner">
                <div class="ge-icon-drop__thumb">
                  <?php if (!empty($game['banner'])): ?>
                    <img id="gameBannerDropPreview" src="<?= htmlspecialchars($game['banner']) ?>" alt="">
                  <?php else: ?>
                    <img id="gameBannerDropPreview" src="" alt="" style="display:none;">
                    <i class="fa-solid fa-image" id="gameBannerDropEmpty" style="color:rgba(255,255,255,.3);font-size:18px;"></i>
                  <?php endif; ?>
                </div>
                <div class="ge-icon-drop__main">
                  <div class="ge-icon-drop__title">Click, drag a banner here, or press Ctrl V</div>
                  <div class="ge-icon-drop__text">PNG, JPG, WebP or SVG, max 2 MB. Used on the landing page slider &amp; /services/ pages.</div>
                  <div class="ge-icon-drop__file" id="gameBannerFileName">No new file selected.</div>
                </div>
              </div>
              <div class="form-text">Upload goes to <code>/public_html/public/assets/website/images/banner/</code>. Leave empty to keep current banner.</div>
            </div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary btn-sm" id="saveSettingsBtn" onclick="saveSettings()">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Settings
            </button>
          </div>
        </div>
      </div>

      <!-- Active Services -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-header-title"><i class="fa-solid fa-puzzle-piece me-2"></i>Active Services</h5>
          <span class="text-muted small"><span style="color:#86efac;font-weight:800;">Enabled</span> and <span style="color:#94a3b8;font-weight:800;">Disabled</span> are now clearly visible, toggles save instantly</span>
        </div>
        <div class="card-body">
          <?php
          $svcMeta = [
            'boosting' => ['icon'=>'fa-rocket',    'label'=>'Boosting',     'desc'=>'Rank & win boosts',         'route'=>'/{slug}'],
            'accounts' => ['icon'=>'fa-user-circle','label'=>'Account Shop','desc'=>'Ranked account packages',   'route'=>'/{slug}/accounts'],
            'items'    => ['icon'=>'fa-gift',       'label'=>'Items',        'desc'=>'Skins, items, passes',      'route'=>'/{slug}/items'],
            'topups'  => ['icon'=>'fa-coins',      'label'=>'Top Ups',      'desc'=>'RP, diamonds, coins',       'route'=>'/{slug}/top-ups'],
            'coaching' => ['icon'=>'fa-headset',    'label'=>'Coaching',     'desc'=>'1-on-1 coaching sessions',  'route'=>'/{slug}/coaching'],
            'egirl'    => ['icon'=>'fa-users',      'label'=>'Companion',    'desc'=>'Gaming companion service',  'route'=>'/egirls'],
          ];
          $slug = $game['slug'];
          ?>
          <div class="ge-svcs">
            <?php foreach ($service_types as $type):
              $m = $svcMeta[$type] ?? ['icon'=>'fa-bolt','label'=>ucfirst($type),'desc'=>'','route'=>''];
              $on = in_array($type, $activeServices, true);
            ?>
            <div class="ge-svc svc-<?= $type ?> <?= $on ? 'on' : '' ?>"
                 id="svc-<?= $type ?>" onclick="toggleSvc('<?= $type ?>', <?= (int)$game['id'] ?>)">
              <span class="ge-svc__ico"><i class="fa-solid <?= $m['icon'] ?>"></i></span>
              <div class="ge-svc__body">
                <div class="ge-svc__name"><?= $m['label'] ?></div>
                <div class="ge-svc__desc"><?= $m['desc'] ?></div>
                <code class="ge-svc__route"><?= str_replace('{slug}', $slug, $m['route']) ?></code>
              </div>
              <span class="ge-svc__state state-on"><span class="dot"></span>Enabled</span>
              <span class="ge-svc__state state-off"><span class="dot"></span>Disabled</span>
              <div class="ge-svc__spin" id="svc-spin-<?= $type ?>"></div>
            </div>
            <?php endforeach ?>
          </div>
        </div>
      </div>

      <!-- Boost Forms -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-header-title">
            <i class="fa-solid fa-rocket me-2"></i>Boost Forms
            <span class="badge bg-soft-secondary text-secondary ms-2"><?= count($boostForms) ?></span>
          </h5>
          <a href="/admin-area/games/<?= (int)$game['id'] ?>/boost-forms/create"
             class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Form
          </a>
        </div>
        <?php if (!empty($boostForms)): ?>
        <div class="table-responsive">
          <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-0">
            <thead class="thead-light">
              <tr><th>#</th><th>Name</th><th>Slug</th><th>Type</th><th class="text-center">Status</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($boostForms as $form): ?>
              <tr>
                <td class="text-muted"><?= (int)$form['id'] ?></td>
                <td><?= htmlspecialchars($form['name'] ?? '') ?></td>
                <td><code class="small">/<?= $slug ?>/<?= htmlspecialchars($form['slug'] ?? '') ?></code></td>
                <td><span class="badge bg-soft-secondary text-secondary"><?= htmlspecialchars($form['type'] ?? '') ?></span></td>
                <td class="text-center">
                  <?php if ($form['status']==1): ?><span class="badge bg-soft-success text-success">Active</span>
                  <?php elseif ($form['status']==0): ?><span class="badge bg-soft-danger text-danger">Inactive</span>
                  <?php else: ?><span class="badge bg-soft-warning text-warning">Draft</span><?php endif ?>
                </td>
                <td class="text-end">
                  <a href="/admin-area/games/<?= (int)$game['id'] ?>/boost-form-edit?fid=<?= (int)$form['id'] ?>" class="btn btn-ghost-secondary btn-sm btn-icon"><i class="fa-solid fa-pen-to-square"></i></a>
                  <a href="/<?= $slug ?>/<?= htmlspecialchars($form['slug'] ?? '') ?>" target="_blank" class="btn btn-ghost-secondary btn-sm btn-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                </td>
              </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="card-body text-center py-4">
          <i class="fa-solid fa-rocket fa-2x text-muted mb-3 d-block"></i>
          <p class="text-muted mb-3">No boost forms yet. Add one to enable <code>/<?= htmlspecialchars($slug) ?>/rank-boost</code> and other routes.</p>
          <a href="/admin-area/games/<?= (int)$game['id'] ?>/boost-forms/create" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Add First Boost Form
          </a>
        </div>
        <?php endif ?>
      </div>

    </div><!-- /left -->

    <!-- RIGHT: sidebar -->
    <div>

      <!-- Quick stats -->
      <div class="card ge-stat-card">
        <div class="ge-stat-card__label"><i class="fa-solid fa-chart-bar"></i> Overview</div>
        <div class="d-flex flex-column gap-2">
          <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Boost forms</span>
            <span class="badge bg-soft-info text-info"><?= count($boostForms ?? []) ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Active services</span>
            <span class="badge bg-soft-success text-success"><?= count($activeServices) ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Sort position</span>
            <span class="badge bg-soft-secondary text-secondary">#<?= (int)$game['sort_order'] ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Status</span>
            <?php if ($game['status']): ?>
            <span class="badge bg-soft-success text-success"><i class="fa-solid fa-circle me-1" style="font-size:7px;vertical-align:1px"></i>Live</span>
            <?php else: ?>
            <span class="badge bg-soft-secondary text-secondary"><i class="fa-solid fa-circle me-1" style="font-size:7px;vertical-align:1px"></i>Hidden</span>
            <?php endif ?>
          </div>
        </div>
      </div>

      <!-- Color preview -->
      <div class="card ge-stat-card">
        <div class="ge-stat-card__label"><i class="fa-solid fa-palette"></i> Brand Colors</div>
        <div id="colorPreview" style="height:40px;border-radius:8px;background:linear-gradient(90deg,<?= htmlspecialchars($game['color_primary'] ?? '#8b5cf6') ?>,<?= htmlspecialchars($game['color_accent'] ?? '#a78bfa') ?>);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;letter-spacing:.04em;margin-bottom:10px;"><?= htmlspecialchars($game['short_code'] ?: strtoupper(substr($game['slug'],0,4))) ?></div>
        <div class="d-flex gap-2">
          <div class="flex-fill">
            <label class="form-label small text-muted mb-1">Primary</label>
            <input type="text" class="form-control form-control-sm" id="fColorPHex" value="<?= htmlspecialchars($game['color_primary'] ?? '#8b5cf6') ?>"
                   oninput="document.getElementById('fColorP').value=this.value;gcSyncSwatch('fColorP','swatchP','fColorPHex')">
          </div>
          <div class="flex-fill">
            <label class="form-label small text-muted mb-1">Accent</label>
            <input type="text" class="form-control form-control-sm" id="fColorAHex" value="<?= htmlspecialchars($game['color_accent'] ?? '#a78bfa') ?>"
                   oninput="document.getElementById('fColorA').value=this.value;gcSyncSwatch('fColorA','swatchA','fColorAHex')">
          </div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="card ge-stat-card">
        <div class="ge-stat-card__label"><i class="fa-solid fa-link"></i> Quick Links</div>
        <div class="d-flex flex-column gap-2">
          <a href="/<?= htmlspecialchars($slug) ?>" target="_blank" class="btn btn-ghost-secondary btn-sm text-start">
            <i class="fa-solid fa-house me-2"></i> Game page
          </a>
          <?php if (in_array('boosting', $activeServices)): ?>
          <a href="/<?= htmlspecialchars($slug) ?>/rank-boost" target="_blank" class="btn btn-ghost-secondary btn-sm text-start">
            <i class="fa-solid fa-rocket me-2"></i> Rank boost
          </a>
          <?php endif ?>
          <?php if (in_array('accounts', $activeServices)): ?>
          <a href="/<?= htmlspecialchars($slug) ?>/accounts" target="_blank" class="btn btn-ghost-secondary btn-sm text-start">
            <i class="fa-solid fa-user-circle me-2"></i> Accounts
          </a>
          <?php endif ?>
        </div>
      </div>

      <!-- Back -->
      <a href="/admin-area/games" class="btn btn-ghost-secondary w-100">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Games
      </a>

    </div><!-- /sidebar -->

  </div><!-- /ge-cols -->

  <!-- Accounts Config -->
  <?php if (in_array('accounts', $activeServices, true)): ?>
  <?php
    $accountsConfig = $accountsConfig ?? [];
    if (empty($accountsConfig) && !empty($game['slug']) && function_exists('util_get_accounts_page_config')) {
        $accountsConfig = util_get_accounts_page_config($game['slug']);
    }
    $cfg = $accountsConfig;
    $acTitle   = $cfg['page_title'] ?? '';
    $acDesc    = $cfg['page_description'] ?? '';
    $acTypeCards = !empty($cfg['show_type_cards']);

    $accountSchema = $accountSchema ?? [];
    if (empty($accountSchema) && function_exists('util_get_game_account_schema')) {
        $accountSchema = util_get_game_account_schema($game['slug']);
    }
    if (empty($accountSchema) || !is_array($accountSchema)) {
        $accountSchema = [
            'enabled' => true,
            'title_field' => 'title',
            'headline_icon_field' => '',
            'fields' => []
        ];
    }
    $schemaEnabled = !isset($accountSchema['enabled']) || !empty($accountSchema['enabled']);
    $schemaTitleField = $accountSchema['title_field'] ?? '';
    $schemaIconField  = $accountSchema['headline_icon_field'] ?? '';
    $schemaFields     = isset($accountSchema['fields']) && is_array($accountSchema['fields']) ? $accountSchema['fields'] : [];
  ?>
  <style>
    .acs-help{font-size:12px;color:rgba(255,255,255,.45);line-height:1.45}
    .acs-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.acs-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
    @media(max-width:1000px){.acs-grid,.acs-grid-3{grid-template-columns:1fr}}
    .acs-field{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);border-radius:12px;margin-bottom:10px;overflow:hidden}
    .acs-field-head{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025)}
    .acs-drag{color:rgba(255,255,255,.25);font-size:13px}.acs-title{font-weight:800;color:#fff;font-size:13px;flex:1}.acs-type{font-size:11px;color:#a5b4fc;background:rgba(99,102,241,.16);border:1px solid rgba(129,140,248,.25);border-radius:999px;padding:3px 8px}
    .acs-field-body{padding:14px}.acs-flags{display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:8px;margin-top:10px}
    @media(max-width:1100px){.acs-flags{grid-template-columns:repeat(2,1fr)}}
    .acs-flag{display:flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:8px;padding:8px 10px;font-size:12px;font-weight:700;color:rgba(255,255,255,.68)}
    .acs-flag input{margin:0}.acs-actions{display:flex;gap:8px;flex-wrap:wrap}.acs-json{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;min-height:180px}.acs-mini-label{font-size:11px;color:rgba(255,255,255,.42);font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
    .acs-options-editor{border:1px solid rgba(129,140,248,.16);background:rgba(99,102,241,.045);border-radius:12px;padding:12px;margin-top:8px}
    .acs-option-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}.acs-option-row input{width:100%}
    .acs-option-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;min-height:32px}
    .acs-option-chip{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(129,140,248,.38);background:rgba(99,102,241,.18);color:#fff;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800}
    .acs-option-chip button{border:0;background:rgba(255,255,255,.10);color:rgba(255,255,255,.72);padding:0;width:18px;height:18px;border-radius:999px;line-height:18px;cursor:pointer}.acs-option-chip button:hover{background:rgba(244,63,94,.35);color:#fff}
    .acs-option-empty{font-size:12px;color:rgba(255,255,255,.42);padding:8px 0;border:1px dashed rgba(255,255,255,.10);border-radius:10px;text-align:center}.acs-quick-options{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}.acs-quick-options button{font-size:11px;padding:5px 9px}.acs-options-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px}.acs-options-head strong{display:block;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:.06em}.acs-options-head span{display:block;color:rgba(255,255,255,.42);font-size:11px;margin-top:2px}.acs-options-count{border:1px solid rgba(129,140,248,.25);background:rgba(99,102,241,.14);color:#dbeafe;border-radius:999px;padding:4px 8px;font-size:11px;font-weight:900;white-space:nowrap}.acs-preset-label{font-size:11px;color:rgba(255,255,255,.45);margin-right:2px}
    .acs-intro{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}
    @media(max-width:1000px){.acs-intro{grid-template-columns:1fr 1fr}}
    .acs-step{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:12px;padding:12px}
    .acs-step b{display:block;color:#fff;font-size:13px;margin-bottom:3px}.acs-step span{display:block;color:rgba(255,255,255,.45);font-size:11px;line-height:1.35}
    .acs-step i{color:#818cf8;margin-right:6px}
    .acs-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:12px}
    @media(max-width:1000px){.acs-summary{grid-template-columns:1fr 1fr}}
    .acs-sum{border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.12);border-radius:10px;padding:10px 12px}
    .acs-sum strong{display:block;color:#fff;font-size:18px;line-height:1}.acs-sum span{display:block;color:rgba(255,255,255,.45);font-size:11px;font-weight:700;margin-top:4px}
    .acs-tools{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}.acs-tools .btn{font-size:12px}
    .acs-section{border:1px solid rgba(255,255,255,.07);border-radius:10px;background:rgba(0,0,0,.10);padding:12px;margin-bottom:12px}
    .acs-section-title{display:flex;align-items:center;gap:7px;color:#fff;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px}
    .acs-section-title i{color:#818cf8}.acs-section-help{font-size:11px;color:rgba(255,255,255,.38);margin-top:-4px;margin-bottom:10px}
    .acs-pills{display:flex;flex-wrap:wrap;gap:6px}.acs-pill-check{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);border-radius:999px;padding:7px 10px;color:rgba(255,255,255,.70);font-size:12px;font-weight:800}.acs-pill-check input{margin:0}.acs-pill-check:has(input:checked){background:rgba(99,102,241,.16);border-color:rgba(129,140,248,.35);color:#fff}
    .acs-field-hint{font-size:11px;color:rgba(255,255,255,.36);margin-top:5px}.acs-empty-state{border:1px dashed rgba(255,255,255,.16);background:rgba(255,255,255,.025);border-radius:12px;padding:22px;text-align:center;color:rgba(255,255,255,.55);margin-bottom:12px}.acs-empty-state i{font-size:22px;color:#818cf8;margin-bottom:8px}
    .acs-inline-note{font-size:12px;color:rgba(255,255,255,.55);background:rgba(99,102,241,.08);border:1px solid rgba(129,140,248,.18);border-radius:10px;padding:10px 12px;margin-bottom:12px}
  </style>

  <div id="accountSchemaCard" class="acs-builder-layout mb-4">
  <div class="card acs-builder-card">
    <style>
      .acs-builder-layout{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(440px,.8fr);gap:24px;align-items:start;grid-column:1 / -1;width:100%;max-width:none}
      .acs-builder-layout>.card{min-width:0}
      .acs-preview-shell{position:sticky;top:18px;border:1px solid rgba(129,140,248,.20);box-shadow:0 16px 38px rgba(0,0,0,.24);overflow:visible}
      .acs-preview-shell .card-header{background:rgba(99,102,241,.07);border-bottom:1px solid rgba(255,255,255,.08)}
      @media(max-width:1200px){.acs-builder-layout{grid-template-columns:1fr}.acs-preview-shell{position:static}}
      .acs-top-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
      @media(max-width:900px){.acs-top-grid{grid-template-columns:1fr}}
      .acs-status-row{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;padding:12px;border:1px solid rgba(255,255,255,.08);border-radius:12px;background:rgba(255,255,255,.025)}
      .acs-quickbar{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px}
      @media(max-width:900px){.acs-quickbar{grid-template-columns:1fr 1fr}}
      .acs-add-card{display:flex;align-items:center;gap:10px;text-align:left;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.035);border-radius:12px;padding:12px;color:rgba(255,255,255,.78);font-weight:900;min-height:62px;transition:.15s}
      .acs-add-card:hover{background:rgba(99,102,241,.13);border-color:rgba(129,140,248,.38);color:#fff;transform:translateY(-1px)}
      .acs-add-card i{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.15);color:#a5b4fc;font-size:15px;flex-shrink:0}
      .acs-add-card small{display:block;color:rgba(255,255,255,.42);font-weight:700;font-size:11px;margin-top:2px;line-height:1.2}
      .acs-add-card.acs-preset i{background:rgba(245,158,11,.14);color:#fbbf24}
      .acs-lane{border:1px solid rgba(255,255,255,.08);border-radius:14px;background:rgba(0,0,0,.10);overflow:hidden;margin-bottom:12px}
      .acs-lane-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 14px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025)}
      .acs-lane-title{display:flex;align-items:center;gap:8px;color:#fff;font-weight:900}.acs-lane-title i{color:#818cf8}.acs-lane-help{font-size:12px;color:rgba(255,255,255,.43);margin-top:2px}
      .acs-field-list{padding:10px}.acs-empty-state{border:1px dashed rgba(255,255,255,.14);border-radius:12px;padding:22px;text-align:center;color:rgba(255,255,255,.5);background:rgba(255,255,255,.025)}
      .acs-field{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:12px;margin-bottom:10px;overflow:hidden}
      .acs-field-main{display:grid;grid-template-columns:minmax(0,1fr) auto;grid-template-areas:"name actions" "meta actions";gap:8px 12px;align-items:center;padding:12px;overflow:visible}
      @media(max-width:760px){.acs-field-main{grid-template-columns:1fr;grid-template-areas:"name" "meta" "actions"}.acs-field-actions{justify-content:flex-start!important}}
      .acs-field-name{grid-area:name;display:flex;align-items:center;gap:10px;min-width:0}.acs-field-ico{width:34px;height:34px;border-radius:10px;background:rgba(99,102,241,.13);color:#a5b4fc;display:flex;align-items:center;justify-content:center;flex-shrink:0}
      .acs-field-title{font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.acs-field-key{font-size:11px;color:rgba(255,255,255,.38);font-family:ui-monospace,SFMono-Regular,monospace;margin-top:1px}
      .acs-field-meta{grid-area:meta;display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0}.acs-chip-row{display:flex;gap:7px;flex-wrap:wrap}.acs-chip{display:inline-flex;align-items:center;gap:6px;border-radius:12px;padding:6px 10px;font-size:11px;font-weight:900;border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.38);background:rgba(255,255,255,.035);box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
      .acs-chip i{font-size:11px}.acs-chip.on{color:#dbeafe;border-color:rgba(96,165,250,.32);background:rgba(59,130,246,.14)}.acs-chip.type{border-radius:999px;color:#bfdbfe;border-color:rgba(59,130,246,.32);background:rgba(37,99,235,.14);text-transform:lowercase}.acs-chip.upload.on{color:#bbf7d0;border-color:rgba(34,197,94,.42);background:linear-gradient(180deg,rgba(34,197,94,.22),rgba(34,197,94,.08))}.acs-chip.filter.on{color:#fde68a;border-color:rgba(245,158,11,.44);background:linear-gradient(180deg,rgba(245,158,11,.22),rgba(245,158,11,.08))}.acs-chip.card.on{color:#ddd6fe;border-color:rgba(139,92,246,.46);background:linear-gradient(180deg,rgba(139,92,246,.24),rgba(139,92,246,.08))}.acs-chip.view.on{color:#bae6fd;border-color:rgba(14,165,233,.42);background:linear-gradient(180deg,rgba(14,165,233,.22),rgba(14,165,233,.08))}
      .acs-field-actions{grid-area:actions;display:grid;grid-template-columns:repeat(2,minmax(78px,auto));gap:7px;justify-content:end;align-items:center}.acs-field-actions .btn{padding:8px 10px;font-weight:900;font-size:12px;border-radius:10px;white-space:nowrap;min-width:78px}
      .acs-field-actions .acs-action-delete{background:rgba(244,63,94,.14)!important;border:1px solid rgba(244,63,94,.42)!important;color:#fb7185!important}.acs-field-actions .acs-action-delete:hover{background:rgba(244,63,94,.24)!important;color:#fff!important}
      .acs-field-actions .acs-action-edit{background:rgba(99,102,241,.14)!important;border:1px solid rgba(129,140,248,.36)!important;color:#c7d2fe!important}
      .acs-field-actions .acs-action-move{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.10)!important;color:rgba(255,255,255,.70)!important}
      .acs-edit-panel{display:none;padding:14px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.12)}.acs-field.open .acs-edit-panel{display:block}
      .acs-edit-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}.acs-edit-grid.two{grid-template-columns:1fr 1fr}.acs-edit-grid.full{grid-template-columns:1fr}@media(max-width:900px){.acs-edit-grid,.acs-edit-grid.two{grid-template-columns:1fr}}
      .acs-mini-label{font-size:11px;color:rgba(255,255,255,.42);font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}.acs-help{font-size:12px;color:rgba(255,255,255,.45);line-height:1.45}
      .acs-toggle-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:10px}@media(max-width:900px){.acs-toggle-grid{grid-template-columns:1fr 1fr}}
      .acs-toggle{display:flex;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);border-radius:10px;padding:9px 10px;color:rgba(255,255,255,.68);font-size:12px;font-weight:800}.acs-toggle input{margin:0}.acs-toggle:has(input:checked){background:rgba(99,102,241,.15);border-color:rgba(129,140,248,.34);color:#fff}
      .acs-options-box{border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.12);border-radius:10px;padding:10px;margin-top:12px}.acs-option-row{display:flex;gap:8px}.acs-option-row input{flex:1}.acs-quick-options{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}.acs-option-chips{display:flex;gap:7px;flex-wrap:wrap;margin-top:9px}.acs-option-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(129,140,248,.34);background:rgba(99,102,241,.14);color:#fff;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:800}.acs-option-chip button{border:0;background:transparent;color:rgba(255,255,255,.65);padding:0;line-height:1}.acs-option-empty{font-size:12px;color:rgba(255,255,255,.36);padding:4px 0}
      .acs-preview{}.acs-preview-card{--pv:#818cf8;--pv-rgb:129,140,248;border:1px solid rgba(var(--pv-rgb),.25);border-radius:14px;background:linear-gradient(180deg,rgba(var(--pv-rgb),.12),rgba(0,0,0,.16));padding:12px;margin-bottom:10px;box-shadow:inset 3px 0 0 rgba(var(--pv-rgb),.7)}.acs-preview-card.filter{--pv:#f59e0b;--pv-rgb:245,158,11}.acs-preview-card.card{--pv:#8b5cf6;--pv-rgb:139,92,246}.acs-preview-card.upload{--pv:#22c55e;--pv-rgb:34,197,94}.acs-preview-card.view{--pv:#0ea5e9;--pv-rgb:14,165,233}.acs-preview-title{display:flex;align-items:center;gap:8px;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.68);font-weight:900;margin-bottom:10px}.acs-preview-title i{width:24px;height:24px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#fff;background:rgba(var(--pv-rgb),.26);border:1px solid rgba(var(--pv-rgb),.38)}
      .pv-filterbar{display:flex;gap:6px;flex-wrap:wrap}.pv-filter{border:1px solid rgba(245,158,11,.34);background:linear-gradient(180deg,rgba(245,158,11,.20),rgba(245,158,11,.08));border-radius:9px;padding:7px 9px;color:#fde68a;font-size:11px;font-weight:900}.pv-shop-card{border:1px solid rgba(139,92,246,.24);border-radius:13px;background:linear-gradient(180deg,rgba(139,92,246,.12),rgba(0,0,0,.18));padding:12px}.pv-shop-head{display:flex;align-items:center;gap:9px;margin-bottom:10px}.pv-iconbox{width:38px;height:38px;border-radius:10px;background:rgba(139,92,246,.13);border:1px solid rgba(139,92,246,.26);display:flex;align-items:center;justify-content:center;color:#a78bfa;font-size:20px;position:relative}.pv-game-badge{position:absolute;right:-4px;bottom:-4px;width:16px;height:16px;border-radius:999px;background:#222;border:1px solid rgba(255,255,255,.15);font-size:8px;display:flex;align-items:center;justify-content:center;color:#a3e635}.pv-shop-name{font-weight:900;color:#fff;line-height:1.15}.pv-shop-sub{font-size:11px;color:rgba(255,255,255,.43);margin-top:2px}.pv-img{height:64px;border-radius:10px;background:linear-gradient(135deg,rgba(139,92,246,.24),rgba(14,165,233,.08));border:1px solid rgba(139,92,246,.18);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.36);font-size:28px;margin-bottom:10px}.pv-badges{display:flex;gap:6px;flex-wrap:wrap}.pv-badge{display:inline-flex;align-items:center;gap:5px;border:1px solid rgba(139,92,246,.24);background:rgba(139,92,246,.14);border-radius:999px;padding:5px 8px;color:#ddd6fe;font-size:11px;font-weight:900}.pv-price{margin-top:10px;padding-top:9px;border-top:1px solid rgba(255,255,255,.08);font-size:18px;color:#fff;font-weight:900}.pv-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.pv-detail{border:1px solid rgba(14,165,233,.18);background:rgba(14,165,233,.08);border-radius:10px;padding:8px}.pv-detail b{display:block;color:#fff;font-size:12px}.pv-detail span{display:flex;align-items:center;gap:6px;color:#bae6fd;font-size:12px;margin-top:4px}.pv-upload-list{display:grid;gap:6px}.pv-upload-field{display:flex;justify-content:space-between;gap:8px;border:1px solid rgba(34,197,94,.22);background:rgba(34,197,94,.09);border-radius:9px;padding:7px 9px;font-size:11px;color:#dcfce7}.pv-upload-field small{color:#86efac}
      .acs-json{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;min-height:180px}.acs-hidden{display:none!important}
    </style>
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-header-title mb-1">
          <i class="fa-solid fa-user-circle me-2 text-success"></i>Accounts Builder
          <code class="fw-normal small ms-1 text-muted">/<?= htmlspecialchars($slug) ?>/accounts</code>
        </h5>
        <div class="acs-help">Set up seller upload fields, shop filters, card badges, and the detail page. Use the preview on the right to see the result.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="/<?= htmlspecialchars($slug) ?>/accounts" target="_blank" class="btn btn-ghost-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Shop</a>
        <button type="button" class="btn btn-primary btn-sm" onclick="saveAccountsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
      </div>
    </div>
    <div class="card-body">
          <div class="acs-status-row">
            <div class="d-flex gap-3 flex-wrap">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="acsEnabled" <?= $schemaEnabled ? 'checked' : '' ?>>
                <label class="form-check-label small" for="acsEnabled">Dynamic account setup active</label>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="acTypeCards" <?= $acTypeCards ? 'checked' : '' ?>>
                <label class="form-check-label small" for="acTypeCards">Show account type cards</label>
              </div>
            </div>
            <button type="button" class="btn btn-ghost-secondary btn-sm" onclick="acsToggleJson()"><i class="fa-solid fa-code me-1"></i> Advanced JSON</button>
          </div>

          <div class="acs-lane">
            <div class="acs-lane-head">
              <div>
                <div class="acs-lane-title"><i class="fa-solid fa-heading"></i> Page setup</div>
                <div class="acs-lane-help">Basic text and which attribute is used for the title/icon.</div>
              </div>
            </div>
            <div class="p-3">
              <div class="acs-top-grid">
                <div>
                  <label class="form-label fw-semibold small">Shop page title</label>
                  <input type="text" class="form-control" id="acTitle" value="<?= htmlspecialchars($acTitle) ?>" placeholder="e.g. Call of Duty Accounts">
                </div>
                <div>
                  <label class="form-label fw-semibold small">Account title attribute</label>
                  <input type="text" class="form-control" id="acsTitleField" value="<?= htmlspecialchars($schemaTitleField) ?>" placeholder="e.g. main_title">
                  <div class="acs-help mt-1">Usually <code>main_title</code>, <code>rank</code>, or <code>platform</code>.</div>
                </div>
                <div>
                  <label class="form-label fw-semibold small">Shop page description</label>
                  <textarea class="form-control" id="acDesc" rows="2" placeholder="Buy accounts..."><?= htmlspecialchars($acDesc) ?></textarea>
                </div>
                <div>
                  <label class="form-label fw-semibold small">Main icon attribute</label>
                  <input type="text" class="form-control" id="acsIconField" value="<?= htmlspecialchars($schemaIconField) ?>" placeholder="platform or rank">
                  <div class="acs-help mt-1">Use <code>platform</code> for platform icons or <code>rank</code> for rank icons.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="acs-lane">
            <div class="acs-lane-head">
              <div>
                <div class="acs-lane-title"><i class="fa-solid fa-cubes"></i> Attributes</div>
                <div class="acs-lane-help">Each row is one value the seller can enter, for example Platform, Rank, Level, Camos, or Points.</div>
              </div>
            </div>
            <div class="p-3 pb-0">
              <div class="acs-quickbar">
                <button type="button" class="acs-add-card" onclick="acsAddTemplate('platform')"><i class="fa-solid fa-desktop"></i><span>Platform <small>PC, PlayStation, Xbox, Steam, Switch, Android, iOS, Switch, Mobile</small></span></button>
                <button type="button" class="acs-add-card" onclick="acsAddTemplate('rank')"><i class="fa-solid fa-medal"></i><span>Rank <small>Rank icon, rank filter, view value</small></span></button>
                <button type="button" class="acs-add-card" onclick="acsAddTemplate('level')"><i class="fa-solid fa-arrow-up"></i><span>Level <small>Number field with range filter</small></span></button>
                <button type="button" class="acs-add-card" onclick="acsAddTemplate('number_badge')"><i class="fa-solid fa-hashtag"></i><span>Number badge <small>Camos, weapons, points, wins</small></span></button>
                <button type="button" class="acs-add-card" onclick="acsAddField()"><i class="fa-solid fa-plus"></i><span>Custom attribute <small>Build your own upload field</small></span></button>
                <button type="button" class="acs-add-card acs-preset" onclick="acsLoadPreset('cod')"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Load CoD preset <small>Replace fields with Call of Duty setup</small></span></button>
              </div>
            </div>
            <div id="acsFields" class="acs-field-list"></div>
          </div>

          <div id="acsJsonWrap" style="display:none" class="mt-3">
            <label class="form-label fw-semibold small">Schema JSON</label>
            <textarea class="form-control acs-json" id="acsJson"></textarea>
            <div class="acs-help mt-1">Advanced mode. When this is open, the JSON will be used on save.</div>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveAccountsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save Accounts Builder</button>
          </div>
    </div>
  </div>

  <div class="card acs-preview-shell">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div>
        <h5 class="card-header-title mb-1"><i class="fa-solid fa-eye me-2 text-primary"></i>Live Preview</h5>
        <div class="acs-help">Shows how this setup appears in the shop, upload modal, account card and view page.</div>
      </div>
    </div>
    <div class="card-body acs-preview">
          <div class="acs-preview-card filter">
            <div class="acs-preview-title"><i class="fa-solid fa-filter"></i> Shop filters preview</div>
            <div id="pvFilters" class="pv-filterbar"></div>
          </div>
          <div class="acs-preview-card card">
            <div class="acs-preview-title"><i class="fa-solid fa-id-card"></i> Account card preview</div>
            <div id="pvCard"></div>
          </div>
          <div class="acs-preview-card upload">
            <div class="acs-preview-title"><i class="fa-solid fa-list-check"></i> Seller upload preview</div>
            <div id="pvUpload" class="pv-upload-list"></div>
          </div>
          <div class="acs-preview-card view">
            <div class="acs-preview-title"><i class="fa-solid fa-eye"></i> View page preview</div>
            <div id="pvView" class="pv-detail-grid"></div>
          </div>
        </div>
    </div>
  </div>
  <?php endif ?>

</div>


  <!-- Items Config -->
  <?php if (in_array('items', $activeServices, true)): ?>
  <?php
    $itemsConfig = $itemsConfig ?? (function_exists('lb_get_items_page_config') ? lb_get_items_page_config($game['slug']) : []);
    $itemSchema = $itemSchema ?? (function_exists('lb_get_game_item_schema') ? lb_get_game_item_schema($game['slug']) : []);
    if (empty($itemSchema) || !is_array($itemSchema)) {
        $itemSchema = ['enabled'=>true,'title_field'=>'title','headline_icon_field'=>'type','fields'=>[]];
    }
    $itTitle = $itemsConfig['page_title'] ?? (($game['name'] ?? 'Game') . ' Items');
    $itDesc = $itemsConfig['page_description'] ?? '';
    $itTypeCards = !empty($itemsConfig['show_type_cards']);
    $itSchemaEnabled = !isset($itemSchema['enabled']) || !empty($itemSchema['enabled']);
    $itSchemaTitleField = $itemSchema['title_field'] ?? 'title';
    $itSchemaIconField = $itemSchema['headline_icon_field'] ?? 'type';
    $itSchemaFields = isset($itemSchema['fields']) && is_array($itemSchema['fields']) ? array_values($itemSchema['fields']) : [];
  ?>
  <style>
    .ibs-note{font-size:12px;color:rgba(255,255,255,.45);line-height:1.45}
    .ibs-mini{font-size:11px;color:rgba(255,255,255,.38);font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
    .ibs-tools{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.ibs-tools .btn{font-weight:900}
    .ibs-json{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;min-height:180px}
  </style>
  <div class="row g-4 mb-4" id="itemsConfigCard">
    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-header-title mb-1">
              <i class="fa-solid fa-gift me-2 text-success"></i>Items Builder
              <code class="fw-normal small ms-1 text-muted">/<?= htmlspecialchars($slug) ?>/items</code>
            </h5>
            <div class="ibs-note">Set up seller item fields, shop filters, item card badges, and the item detail page. You can build it visually without touching JSON.</div>
          </div>
          <div class="ibs-tools">
            <a href="/<?= htmlspecialchars($slug) ?>/items" target="_blank" class="btn btn-ghost-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Shop</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="saveItemsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
          </div>
        </div>
        <div class="card-body">
          <div class="acs-lane mb-3">
            <div class="acs-lane-head">
              <label class="acs-toggle m-0"><input type="checkbox" id="ibsEnabled" <?= $itSchemaEnabled ? 'checked' : '' ?>> Dynamic item setup active</label>
              <label class="acs-toggle m-0"><input type="checkbox" id="ibsTypeCards" <?= $itTypeCards ? 'checked' : '' ?>> Show item type cards</label>
              <button type="button" class="btn btn-ghost-secondary btn-sm ms-auto" onclick="ibsToggleJson()"><i class="fa-solid fa-code me-1"></i> Advanced JSON</button>
            </div>
          </div>

          <div class="acs-lane">
            <div class="acs-lane-head">
              <div>
                <div class="acs-lane-title"><i class="fa-solid fa-heading"></i> Page setup</div>
                <div class="acs-lane-help">Basic text and which item attribute is used for the title or icon.</div>
              </div>
            </div>
            <div class="p-3">
              <div class="acs-edit-grid two">
                <div>
                  <label class="form-label fw-semibold small">Shop page title</label>
                  <input type="text" class="form-control" id="ibsPageTitle" value="<?= htmlspecialchars($itTitle) ?>" placeholder="<?= htmlspecialchars(($game['name'] ?? 'Game') . ' Items') ?>">
                </div>
                <div>
                  <label class="form-label fw-semibold small">Item title attribute</label>
                  <input type="text" class="form-control" id="ibsTitleField" value="<?= htmlspecialchars((string)$itSchemaTitleField) ?>" placeholder="title">
                  <div class="ibs-note mt-1">Usually <code>title</code>, <code>type</code>, <code>skin_name</code>, or <code>topup_amount</code>.</div>
                </div>
                <div>
                  <label class="form-label fw-semibold small">Shop page description</label>
                  <textarea class="form-control" id="ibsPageDesc" rows="2" placeholder="Buy items... "><?= htmlspecialchars($itDesc) ?></textarea>
                </div>
                <div>
                  <label class="form-label fw-semibold small">Main icon attribute</label>
                  <input type="text" class="form-control" id="ibsIconField" value="<?= htmlspecialchars((string)$itSchemaIconField) ?>" placeholder="type or server">
                  <div class="ibs-note mt-1">Use <code>type</code> for item type icons, <code>server</code> for server badges, or your own attribute.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="acs-lane">
            <div class="acs-lane-head">
              <div>
                <div class="acs-lane-title"><i class="fa-solid fa-cubes"></i> Item attributes</div>
                <div class="acs-lane-help">Each row is one field the seller fills out, for example Item Type, Server, Amount, Delivery Time, Skin Name, or Platform.</div>
              </div>
            </div>
            <div class="p-3 pb-0">
              <div class="acs-quickbar">
                <button type="button" class="acs-add-card" onclick="ibsAddTemplate('type')"><i class="fa-solid fa-tags"></i><span>Item Type <small>Skins, bundles, top ups, gifting</small></span></button>
                <button type="button" class="acs-add-card" onclick="ibsAddTemplate('server')"><i class="fa-solid fa-globe"></i><span>Server <small>EUW, NA, EUNE, TR, KR</small></span></button>
                <button type="button" class="acs-add-card" onclick="ibsAddTemplate('delivery')"><i class="fa-solid fa-clock"></i><span>Delivery Time <small>Manual delivery info</small></span></button>
                <button type="button" class="acs-add-card" onclick="ibsAddTemplate('amount')"><i class="fa-solid fa-coins"></i><span>Amount / Value <small>RP, Robux, Coins, Gems</small></span></button>
                <button type="button" class="acs-add-card" onclick="ibsAddField()"><i class="fa-solid fa-plus"></i><span>Custom attribute <small>Build your own item field</small></span></button>
                <button type="button" class="acs-add-card acs-preset" onclick="ibsLoadPreset('lol')"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Load LoL items preset <small>Replace fields with gifting setup</small></span></button>
                <button type="button" class="acs-add-card acs-preset" onclick="ibsLoadPreset('topups')"><i class="fa-solid fa-bolt"></i><span>Load top ups preset <small>For RP, Robux, coins, gems</small></span></button>
              </div>
            </div>
            <div id="ibsFields" class="acs-field-list"></div>
          </div>

          <div id="ibsJsonWrap" style="display:none" class="mt-3">
            <label class="form-label fw-semibold small">Items Schema JSON</label>
            <textarea class="form-control ibs-json" id="ibsJson" spellcheck="false"></textarea>
            <div class="ibs-note mt-1">Advanced mode. When this is open, this JSON will be used on save.</div>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveItemsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save Items Builder</button>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card acs-preview-shell h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="card-header-title mb-1"><i class="fa-solid fa-eye me-2 text-primary"></i>Live Preview</h5>
            <div class="ibs-note">Shows how this setup appears in the shop, seller upload modal, item card and item view page.</div>
          </div>
        </div>
        <div class="card-body acs-preview">
          <div class="acs-preview-card filter">
            <div class="acs-preview-title"><i class="fa-solid fa-filter"></i> Shop filters preview</div>
            <div id="ibPvFilters" class="pv-filterbar"></div>
          </div>
          <div class="acs-preview-card card">
            <div class="acs-preview-title"><i class="fa-solid fa-id-card"></i> Item card preview</div>
            <div id="ibPvCard"></div>
          </div>
          <div class="acs-preview-card upload">
            <div class="acs-preview-title"><i class="fa-solid fa-list-check"></i> Seller upload preview</div>
            <div id="ibPvUpload" class="pv-upload-list"></div>
          </div>
          <div class="acs-preview-card view">
            <div class="acs-preview-title"><i class="fa-solid fa-eye"></i> View page preview</div>
            <div id="ibPvView" class="pv-detail-grid"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Top Ups Config -->
  <?php if (in_array('topups', $activeServices, true) || in_array('top-ups', $activeServices, true) || in_array('currencies', $activeServices, true)): ?>
  <?php
    $topupsConfig = function_exists('lb_get_topups_page_config') ? lb_get_topups_page_config($game['slug']) : [];
    $topupSchema = function_exists('lb_get_game_topup_schema') ? lb_get_game_topup_schema($game['slug']) : [];
    $tuServiceLabel = $topupsConfig['service_label'] ?? 'Top Up';
    $tuPageTitle = $topupsConfig['page_title'] ?? (($game['name'] ?? 'Game') . ' ' . $tuServiceLabel);
    $tuPageDesc = $topupsConfig['page_description'] ?? '';
    $tuAmountLabel = $topupsConfig['amount_label'] ?? 'Available Offers';
    $tuRegionLabel = $topupsConfig['region_label'] ?? 'Region';
    $tuCheckoutFields = isset($topupSchema['checkout_fields']) && is_array($topupSchema['checkout_fields']) ? array_values($topupSchema['checkout_fields']) : [];
    if (!$tuCheckoutFields) $tuCheckoutFields = [['key'=>'account_id','label'=>'Account ID','type'=>'text','required'=>true]];
  ?>
  <div class="row g-4 mb-4" id="topupsConfigCard">
    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-header-title mb-1"><i class="fa-solid fa-coins me-2 text-primary"></i>Top Ups Builder <code class="fw-normal small ms-1 text-muted">/<?= htmlspecialchars($slug) ?>/top-ups</code></h5>
            <div class="ibs-note">Set the service name, offer page, seller listing fields and checkout fields. Example, League of Legends can be named Riot Points.</div>
          </div>
          <div class="ibs-tools">
            <a href="/<?= htmlspecialchars($slug) ?>/top-ups" target="_blank" class="btn btn-ghost-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Page</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="saveTopupsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
          </div>
        </div>
        <div class="card-body">
          <div class="acs-lane mb-3"><div class="acs-lane-head"><label class="acs-toggle m-0"><input type="checkbox" id="tusEnabled" <?= (!isset($topupSchema['enabled']) || !empty($topupSchema['enabled'])) ? 'checked' : '' ?>> Dynamic top up setup active</label><label class="acs-toggle m-0"><input type="checkbox" id="tusOther" <?= !empty($topupsConfig['show_other_sellers']) || !isset($topupsConfig['show_other_sellers']) ? 'checked' : '' ?>> Show Other Sellers</label></div></div>
          <div class="acs-lane"><div class="acs-lane-head"><div><div class="acs-lane-title"><i class="fa-solid fa-heading"></i> Page setup</div><div class="acs-lane-help">Choose how the service appears in the game navbar and on the page.</div></div></div><div class="p-3"><div class="acs-edit-grid two"><div><label class="form-label fw-semibold small">Service label in game navbar</label><input class="form-control" id="tusServiceLabel" value="<?= htmlspecialchars((string)$tuServiceLabel) ?>" placeholder="Top Up"><div class="ibs-note mt-1">Example, Riot Points, Diamonds, V Bucks, Robux.</div></div><div><label class="form-label fw-semibold small">Offer section label</label><input class="form-control" id="tusAmountLabel" value="<?= htmlspecialchars((string)$tuAmountLabel) ?>" placeholder="Available Offers"></div><div><label class="form-label fw-semibold small">Page title</label><input class="form-control" id="tusPageTitle" value="<?= htmlspecialchars((string)$tuPageTitle) ?>"></div><div><label class="form-label fw-semibold small">Region label</label><input class="form-control" id="tusRegionLabel" value="<?= htmlspecialchars((string)$tuRegionLabel) ?>" placeholder="Region"></div><div style="grid-column:1/-1"><label class="form-label fw-semibold small">Page description</label><textarea class="form-control" id="tusPageDesc" rows="2"><?= htmlspecialchars((string)$tuPageDesc) ?></textarea></div></div></div></div>
          <div class="acs-lane"><div class="acs-lane-head"><div><div class="acs-lane-title"><i class="fa-solid fa-cart-shopping"></i> Checkout fields</div><div class="acs-lane-help">Fields the customer must enter before buying, for example User ID, Zone ID, Summoner Name, Server or Platform.</div></div></div><div class="p-3"><div class="acs-quickbar"><button type="button" class="acs-add-card" onclick="tusAddField('user_id','User ID')"><i class="fa-solid fa-user"></i><span>User ID <small>Mobile Legends, Robux etc</small></span></button><button type="button" class="acs-add-card" onclick="tusAddField('zone_id','Zone ID')"><i class="fa-solid fa-location-dot"></i><span>Zone ID <small>For games that require server zone</small></span></button><button type="button" class="acs-add-card" onclick="tusAddField('summoner_name','Summoner Name')"><i class="fa-solid fa-gamepad"></i><span>Summoner Name <small>League of Legends</small></span></button><button type="button" class="acs-add-card" onclick="tusAddField('platform','Platform')"><i class="fa-solid fa-desktop"></i><span>Platform <small>PC, Xbox, PlayStation</small></span></button></div><div id="tusFields" class="acs-field-list"></div></div></div>
          <div class="d-flex justify-content-end mt-3"><button type="button" class="btn btn-primary btn-sm" onclick="saveTopupsCfg(<?= (int)$game['id'] ?>)"><i class="fa-solid fa-floppy-disk me-1"></i> Save Top Ups Builder</button></div>
        </div>
      </div>
    </div>
    <div class="col-xl-4"><div class="card acs-preview-shell h-100"><div class="card-header"><h5 class="card-header-title mb-1"><i class="fa-solid fa-eye me-2 text-primary"></i>Live Preview</h5><div class="ibs-note">Shows offer cards, cheapest seller selection and customer fields.</div></div><div class="card-body acs-preview"><div class="acs-preview-card filter"><div class="acs-preview-title"><i class="fa-solid fa-coins"></i> Navbar label</div><div id="tuPvLabel" class="pv-filterbar"></div></div><div class="acs-preview-card card"><div class="acs-preview-title"><i class="fa-solid fa-box"></i> Offer card preview</div><div id="tuPvCard"></div></div><div class="acs-preview-card upload"><div class="acs-preview-title"><i class="fa-solid fa-list-check"></i> Checkout fields</div><div id="tuPvFields" class="pv-upload-list"></div></div><div class="acs-preview-card view"><div class="acs-preview-title"><i class="fa-solid fa-users"></i> Other Sellers preview</div><div class="pv-detail-grid"><div class="pv-detail"><b>Best offer</b><span>€6.42 · 10 min</span></div><div class="pv-detail"><b>Other Sellers · 2</b><span>€6.64 · 1 h</span></div></div></div></div></div></div>
  </div>
  <?php endif; ?>

<script>
var AJAX_URL = '<?= AJAX_URL ?>';
var GAME_ID  = <?= (int)$game['id'] ?>;

/* Toast */
function toast(msg, type) {
  var icons = {success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
  var el = document.createElement('div');
  el.className = 'ge-toast ge-toast--' + (type||'info');
  el.innerHTML = '<i class="fa-solid ' + (icons[type]||'fa-circle-info') + '"></i><span>' + msg + '</span>';
  document.getElementById('geToast').appendChild(el);
  setTimeout(function(){ el.classList.add('out'); setTimeout(function(){ el.remove(); }, 200); }, 3000);
}
<?php if (isset($_GET['saved'])): ?>
document.addEventListener('DOMContentLoaded', function(){ toast('Changes saved!', 'success'); });
<?php endif ?>

/* Color swatch + preview sync */
function gcSyncSwatch(inputId, swatchId, hexId) {
  var val = document.getElementById(inputId).value;
  var sw  = document.getElementById(swatchId);
  var hex = document.getElementById(hexId);
  if (sw)  sw.style.background = val;
  if (hex) hex.value = val;
  var c1 = document.getElementById('fColorP').value;
  var c2 = document.getElementById('fColorA').value;
  var p  = document.getElementById('colorPreview');
  if (p) p.style.background = 'linear-gradient(90deg,' + c1 + ',' + c2 + ')';
}

/* Live toggle — AJAX only status, no full save */
document.getElementById('toggleLive').addEventListener('change', function() {
  var body = new FormData();
  body.append('name',          document.getElementById('fName').value);
  body.append('short_code',    document.getElementById('fCode').value);
  body.append('color_primary', document.getElementById('fColorP').value);
  body.append('color_accent',  document.getElementById('fColorA').value);
  body.append('sort_order',    document.getElementById('fSort').value);
  if (this.checked) body.append('status','1');
  document.querySelectorAll('.ge-svc.on').forEach(function(el){
    body.append('services[]', el.id.replace('svc-',''));
  });
  fetch('/admin-area/games/' + GAME_ID + '/update', { method:'POST', body:body })
    .then(function(){ toast('Status updated: ' + (document.getElementById('toggleLive').checked ? 'Live' : 'Hidden'), 'info'); })
    .catch(function(){ toast('Save failed','error'); });
});

/* Game icon + banner upload, click, drag and paste */
function setupGameImageUpload(opts) {
  var input = document.getElementById(opts.inputId);
  var drop = document.getElementById(opts.dropId);
  var fileName = document.getElementById(opts.fileNameId);
  var dropImg = document.getElementById(opts.dropImgId);
  var heroImg = opts.heroImgId ? document.getElementById(opts.heroImgId) : null;
  var emptyEl = opts.emptyId ? document.getElementById(opts.emptyId) : null;
  if (!input || !drop) return;

  function isAllowed(file) {
    if (!file) return false;
    var name = (file.name || '').toLowerCase();
    var type = (file.type || '').toLowerCase();
    return type.indexOf('image/') === 0 || /\.(png|jpe?g|webp|svg)$/.test(name);
  }

  function setFile(file) {
    if (!isAllowed(file)) { toast('Please choose a valid image file.', 'error'); return; }
    if (file.size > 2 * 1024 * 1024) { toast(opts.label + ' must be max 2 MB.', 'error'); return; }
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    if (fileName) fileName.textContent = file.name || 'Pasted image';
    var reader = new FileReader();
    reader.onload = function(e){
      if (dropImg) { dropImg.src = e.target.result; dropImg.style.display = 'block'; }
      if (emptyEl) emptyEl.style.display = 'none';
      if (heroImg) { heroImg.style.display = 'block'; heroImg.src = e.target.result; }
    };
    reader.readAsDataURL(file);
    toast(opts.label + ' selected. Press Save Settings.', 'info');
  }

  drop.addEventListener('click', function(){ input.click(); });
  drop.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
  input.addEventListener('change', function(){ if (input.files && input.files[0]) setFile(input.files[0]); });

  ['dragenter','dragover'].forEach(function(evt){
    drop.addEventListener(evt, function(e){ e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
  });
  ['dragleave','drop'].forEach(function(evt){
    drop.addEventListener(evt, function(e){ e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', function(e){
    var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
    if (file) setFile(file);
  });
  /* Paste only applies while this specific drop zone is focused, so icon and
     banner paste don't both fire from a single Ctrl+V */
  document.addEventListener('paste', function(e){
    if (document.activeElement !== drop) return;
    var items = e.clipboardData && e.clipboardData.items ? Array.from(e.clipboardData.items) : [];
    for (var i = 0; i < items.length; i++) {
      if (items[i].kind === 'file') {
        var file = items[i].getAsFile();
        if (file && isAllowed(file)) { setFile(file); break; }
      }
    }
  });
}

setupGameImageUpload({
  inputId: 'fIconFile', dropId: 'gameIconDrop', fileNameId: 'gameIconFileName',
  dropImgId: 'gameIconDropPreview', heroImgId: 'gameIconPreview', label: 'Icon'
});
setupGameImageUpload({
  inputId: 'fBannerFile', dropId: 'gameBannerDrop', fileNameId: 'gameBannerFileName',
  dropImgId: 'gameBannerDropPreview', emptyId: 'gameBannerDropEmpty', label: 'Banner'
});

/* Save settings */
function saveSettings() {
  var btn = document.getElementById('saveSettingsBtn');
  if (btn) btn.disabled = true;

  var body = new FormData();
  body.append('action',        'admin_update_game_settings');
  body.append('game_id',       GAME_ID);
  body.append('name',          document.getElementById('fName').value);
  body.append('short_code',    document.getElementById('fCode').value);
  body.append('color_primary', document.getElementById('fColorP').value);
  body.append('color_accent',  document.getElementById('fColorA').value);
  body.append('sort_order',    document.getElementById('fSort').value);

  var iconInput = document.getElementById('fIconFile');
  var hasNewIcon = iconInput && iconInput.files && iconInput.files.length > 0;
  if (hasNewIcon) body.append('game_icon', iconInput.files[0]);

  var bannerInput = document.getElementById('fBannerFile');
  var hasNewBanner = bannerInput && bannerInput.files && bannerInput.files.length > 0;
  if (hasNewBanner) body.append('game_banner', bannerInput.files[0]);

  if (document.getElementById('toggleLive').checked) body.append('status','1');
  document.querySelectorAll('.ge-svc.on').forEach(function(el){
    body.append('services[]', el.id.replace('svc-',''));
  });

  fetch(AJAX_URL, { method:'POST', body:body })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data && data.success) {
        toast(data.message || 'Settings saved!', 'success');
        if (hasNewIcon && data.icon) {
          var freshIcon = data.icon + (data.icon.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
          var heroImg = document.getElementById('gameIconPreview');
          var dropImg = document.getElementById('gameIconDropPreview');
          if (heroImg) { heroImg.style.display = 'block'; heroImg.src = freshIcon; }
          if (dropImg) dropImg.src = freshIcon;
        }
        if (hasNewBanner && data.banner) {
          var freshBanner = data.banner + (data.banner.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
          var bannerDropImg = document.getElementById('gameBannerDropPreview');
          if (bannerDropImg) { bannerDropImg.style.display = 'block'; bannerDropImg.src = freshBanner; }
        }
        window.setTimeout(function(){ window.location.reload(); }, (hasNewIcon || hasNewBanner) ? 350 : 500);
      } else {
        toast((data && data.message) ? data.message : 'Save failed', 'error');
      }
    })
    .catch(function(){ toast('Save failed', 'error'); })
    .finally(function(){ if (btn) btn.disabled = false; });
}

/* Service toggle */
function toggleSvc(type, gameId) {
  var card = document.getElementById('svc-' + type);
  var spin = document.getElementById('svc-spin-' + type);
  if (card.classList.contains('saving')) return;
  var enable = !card.classList.contains('on');
  card.classList.add('saving'); spin.style.display = 'block';
  var fd = new FormData();
  fd.append('action','admin_toggle_game_service');
  fd.append('game_id', gameId);
  fd.append('service_type', type);
  fd.append('enable', enable ? '1' : '0');
  fetch(AJAX_URL, { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) {
        if (enable) card.classList.add('on'); else card.classList.remove('on');
        toast((enable ? 'Enabled: ' : 'Disabled: ') + type, enable ? 'success' : 'info');
      } else { toast('Toggle failed', 'error'); }
    })
    .catch(function(){ toast('Toggle failed', 'error'); })
    .finally(function(){ card.classList.remove('saving'); spin.style.display = 'none'; });
}

/* Tag input */
function getTags(wrapId) {
  return Array.from(document.querySelectorAll('#'+wrapId+' .ge-tag')).map(function(t){return t.dataset.val;});
}
function syncHid(hidId, wrapId) {
  document.getElementById(hidId).value = getTags(wrapId).join(',');
}
function addTag(wrapId, hidId, val) {
  val = val.trim(); if (!val) return;
  if (getTags(wrapId).map(function(v){return v.toLowerCase();}).includes(val.toLowerCase())) return;
  var wrap = document.getElementById(wrapId);
  var inp  = wrap.querySelector('.ge-tw-inp');
  var chip = document.createElement('span');
  chip.className = 'ge-tag'; chip.dataset.val = val;
  chip.innerHTML = (val.length<=5?val.toUpperCase():val)+'<button type="button" class="ge-tag__x" onclick="rmTag(this)">×</button>';
  wrap.insertBefore(chip, inp); syncHid(hidId, wrapId);
  document.querySelectorAll('[data-wrap="'+wrapId+'"][data-val]').forEach(function(p){
    if(p.dataset.val.toLowerCase()===val.toLowerCase()) p.classList.add('on');
  });
}
function rmTag(btn) {
  var chip=btn.closest('.ge-tag'), val=chip.dataset.val;
  var wrap=chip.closest('.ge-tw'), wrapId=wrap.id;
  var hidId=wrap.querySelector('.ge-tw-inp').dataset.hid;
  chip.remove(); syncHid(hidId, wrapId);
  document.querySelectorAll('[data-wrap="'+wrapId+'"][data-val]').forEach(function(p){
    if(p.dataset.val.toLowerCase()===val.toLowerCase()) p.classList.remove('on');
  });
}
function toggleP(el) {
  var val=el.dataset.val, wrapId=el.dataset.wrap, hidId=el.dataset.hid;
  if (el.classList.contains('on')) {
    document.querySelectorAll('#'+wrapId+' .ge-tag').forEach(function(c){
      if(c.dataset.val.toLowerCase()===val.toLowerCase()) c.remove();
    });
    el.classList.remove('on'); syncHid(hidId, wrapId);
  } else { addTag(wrapId, hidId, val); }
}
document.querySelectorAll('.ge-tw-inp').forEach(function(inp){
  inp.addEventListener('keydown', function(e){
    if (e.key==='Enter'||e.key===',') {
      e.preventDefault();
      addTag(this.dataset.wrap, this.dataset.hid, this.value.replace(/,/g,''));
      this.value='';
    } else if (e.key==='Backspace'&&this.value==='') {
      var chips=document.querySelectorAll('#'+this.dataset.wrap+' .ge-tag');
      if(chips.length) chips[chips.length-1].querySelector('.ge-tag__x').click();
    }
  });
  var wrap=document.getElementById(inp.dataset.wrap);
  if(wrap) wrap.addEventListener('click',function(e){ if(!e.target.closest('.ge-tag')) inp.focus(); });
});



/* Dynamic Top Ups Builder */
var TUS_FIELDS = <?= json_encode($tuCheckoutFields ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
function tusEsc(s){return String(s||'').replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];});}
function tusSlug(s){return String(s||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'')||'field';}
function tusRender(){
  var wrap=document.getElementById('tusFields'); if(!wrap) return;
  wrap.innerHTML=(TUS_FIELDS||[]).map(function(f,i){return '<div class="acs-field"><div class="acs-field-head"><div><b>'+tusEsc(f.label||f.key)+'</b><div class="acs-field-sub">'+tusEsc(f.key||'')+' · '+tusEsc(f.type||'text')+'</div></div><button type="button" class="btn btn-ghost-danger btn-sm" onclick="tusRemove('+i+')"><i class="fa-solid fa-trash"></i></button></div><div class="acs-field-edit" style="display:block"><div class="acs-edit-grid two"><div><label>Key</label><input class="form-control tus-in" data-i="'+i+'" data-k="key" value="'+tusEsc(f.key||'')+'"></div><div><label>Label</label><input class="form-control tus-in" data-i="'+i+'" data-k="label" value="'+tusEsc(f.label||'')+'"></div><div><label>Type</label><select class="form-control tus-in" data-i="'+i+'" data-k="type"><option value="text" '+((f.type||'text')==='text'?'selected':'')+'>Text</option><option value="number" '+((f.type||'')==='number'?'selected':'')+'>Number</option></select></div><div><label>Required</label><select class="form-control tus-in" data-i="'+i+'" data-k="required"><option value="1" '+(f.required?'selected':'')+'>Required</option><option value="0" '+(!f.required?'selected':'')+'>Optional</option></select></div></div></div></div>';}).join('') || '<div class="ibs-note p-3">No checkout fields yet.</div>';
  document.querySelectorAll('.tus-in').forEach(function(inp){inp.oninput=inp.onchange=function(){var i=parseInt(inp.dataset.i,10), k=inp.dataset.k; if(!TUS_FIELDS[i]) return; var v=inp.value; if(k==='key') v=tusSlug(v); if(k==='required') v=(v==='1'); TUS_FIELDS[i][k]=v; tusPreview();};});
  tusPreview();
}
function tusAddField(key,label){TUS_FIELDS.push({key:key,label:label,type:'text',required:true}); tusRender();}
function tusRemove(i){TUS_FIELDS.splice(i,1); tusRender();}
function tusPreview(){
  var label=(document.getElementById('tusServiceLabel')||{}).value||'Top Up';
  var amount=(document.getElementById('tusAmountLabel')||{}).value||'Available Offers';
  var pv=document.getElementById('tuPvLabel'); if(pv) pv.innerHTML='<span class="pv-filter"><i class="fa-solid fa-coins me-1"></i>'+tusEsc(label)+'</span>';
  var pc=document.getElementById('tuPvCard'); if(pc) pc.innerHTML='<div class="pv-shop-card"><div class="pv-shop-head"><div class="pv-iconbox"><i class="fa-solid fa-coins"></i></div><div><div class="pv-shop-name">'+tusEsc(amount)+'</div><div class="pv-shop-sub">Cheapest seller selected</div></div></div><div class="pv-price">€6.42 EUR</div><div class="pv-badges"><span class="pv-badge"><i class="fa-solid fa-clock"></i> 10 min</span><span class="pv-badge"><i class="fa-solid fa-box"></i> 999 stock</span></div></div>';
  var pf=document.getElementById('tuPvFields'); if(pf) pf.innerHTML=(TUS_FIELDS||[]).map(function(f){return '<div class="pv-upload-field"><span>'+tusEsc(f.label||f.key)+'</span><small>'+(f.required?'required':'optional')+'</small></div>';}).join('') || '<div class="ibs-note">No customer fields.</div>';
}
function saveTopupsCfg(gameId){
  var schema={enabled:document.getElementById('tusEnabled') && document.getElementById('tusEnabled').checked,checkout_fields:TUS_FIELDS,seller_fields:[]};
  var body=new FormData();
  body.append('service_label',(document.getElementById('tusServiceLabel')||{}).value||'Top Up');
  body.append('page_title',(document.getElementById('tusPageTitle')||{}).value||'');
  body.append('page_description',(document.getElementById('tusPageDesc')||{}).value||'');
  body.append('amount_label',(document.getElementById('tusAmountLabel')||{}).value||'Available Offers');
  body.append('region_label',(document.getElementById('tusRegionLabel')||{}).value||'Region');
  body.append('show_other_sellers',document.getElementById('tusOther') && document.getElementById('tusOther').checked ? '1':'0');
  body.append('schema_enabled',schema.enabled?'1':'0');
  body.append('schema_json',JSON.stringify(schema));
  fetch('/admin-area/games/'+gameId+'/topups-config',{method:'POST',body:body}).then(function(r){return r.text().then(function(t){var d=null;try{d=JSON.parse(t)}catch(e){} if(!r.ok || (d&&d.success===false)) throw new Error((d&&d.error)||t||'bad response'); return d||{};});}).then(function(){toast('Top Ups Builder saved!','success');}).catch(function(e){toast('Save failed: '+e.message,'error');});
}
document.addEventListener('DOMContentLoaded', function(){ tusRender(); ['tusServiceLabel','tusAmountLabel'].forEach(function(id){var el=document.getElementById(id); if(el) el.addEventListener('input',tusPreview);}); });

/* Dynamic Items Builder */
var IBS_FIELDS = <?= json_encode($itSchemaFields ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var IBS_OPEN_INDEX = null;
var IBS_PRESETS = {
  lol: [
    {key:'type',label:'Item Type',type:'select',options:['Skins','Chests & Keys','Orbs','Capsules','Event Pass','Bundles','Mystery Gift','TFT Item'],required:true,show_on_upload:true,show_on_view:true,show_on_card_header:true,filterable:true,filter_type:'multiselect',icon:'fa-solid fa-tags'},
    {key:'server',label:'Server',type:'select',options:['EUW','EUNE','NA','ME','BR','TR','KR','JP','OCE'],required:true,show_on_upload:true,show_on_view:true,show_on_card:true,filterable:true,filter_type:'multiselect',icon:'fa-solid fa-globe'},
    {key:'delivery_time',label:'Delivery Time',type:'number',min:0,max:30,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fa-solid fa-clock',suffix:'days'},
    {key:'friendship_days',label:'Friendship Required',type:'number',min:0,max:30,show_on_upload:true,show_on_view:true,icon:'fa-solid fa-user-clock',suffix:'days'},
    {key:'region_note',label:'Delivery Note',type:'textarea',show_on_upload:true,show_on_view:true,icon:'fa-solid fa-note-sticky'}
  ],
  topups: [
    {key:'type',label:'Top Up Type',type:'select',options:['RP','Robux','V-Bucks','Coins','Gems','Crystals','Gift Card'],required:true,show_on_upload:true,show_on_view:true,show_on_card_header:true,filterable:true,filter_type:'multiselect',icon:'fa-solid fa-bolt'},
    {key:'server',label:'Region / Server',type:'select',options:['Global','EU','NA','EUW','EUNE','TR','BR','KR','JP','OCE'],required:true,show_on_upload:true,show_on_view:true,filterable:true,filter_type:'multiselect',icon:'fa-solid fa-globe'},
    {key:'amount',label:'Amount',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,filterable:true,filter_type:'range',icon:'fa-solid fa-coins'},
    {key:'platform',label:'Platform',type:'select',options:['PC','PlayStation','Xbox','Nintendo Switch','Android','iOS'],show_on_upload:true,show_on_view:true,show_on_card:true,filterable:true,filter_type:'multiselect',icon_type:'platform',icon:'fa-solid fa-desktop'},
    {key:'delivery_time',label:'Delivery Time',type:'number',min:0,max:7,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fa-solid fa-clock',suffix:'hours'}
  ]
};
var IBS_OPTION_PRESETS={
  item_types:['Skins','Chests & Keys','Orbs','Capsules','Event Pass','Bundles','Mystery Gift','TFT Item','Top Up','Gift Card'],
  servers:['EUW','EUNE','NA','ME','BR','TR','KR','JP','OCE','Global'],
  platforms:['PC','PlayStation','Xbox','Nintendo Switch','Android','iOS'],
  yesno:['Yes','No']
};
function ibsEsc(v){return String(v==null?'':v).replace(/[&<>'"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c];});}
function ibsBool(v){return v===true || v===1 || v==='1' || v==='true';}
function ibsSlug(v){return String(v||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');}
function ibsOptionsToString(v){if(Array.isArray(v)) return v.map(function(o){return (typeof o==='object'?(o.label||o.value||''):o);}).join(', '); return String(v||'');}
function ibsStringToOptions(v){return String(v||'').split(',').map(function(x){return x.trim();}).filter(Boolean);}
function ibsTypeIcon(f){
  if((f.icon_type||'')==='platform') return 'fa-solid fa-desktop';
  if((f.key||'')==='server') return 'fa-solid fa-globe';
  if((f.key||'')==='type') return 'fa-solid fa-tags';
  if((f.key||'').indexOf('delivery')!==-1) return 'fa-solid fa-clock';
  return f.icon || (f.type==='number'?'fa-solid fa-hashtag':(f.type==='checkbox'?'fa-solid fa-toggle-on':'fa-solid fa-pen'));
}
function ibsBuildSchema(){return {enabled:document.getElementById('ibsEnabled')?document.getElementById('ibsEnabled').checked:true,title_field:(document.getElementById('ibsTitleField')||{}).value||'title',headline_icon_field:(document.getElementById('ibsIconField')||{}).value||'type',fields:IBS_FIELDS};}
function ibsSyncJson(){var j=document.getElementById('ibsJson'); if(j){j.value=JSON.stringify(ibsBuildSchema(),null,2);}}
function ibsFieldFlags(f){
  return '<span class="acs-chip upload '+(ibsBool(f.show_on_upload)?'on':'')+'"><i class="fa-solid fa-upload"></i> Upload</span>'+
         '<span class="acs-chip filter '+(ibsBool(f.filterable)?'on':'')+'"><i class="fa-solid fa-filter"></i> Filter</span>'+
         '<span class="acs-chip card '+(ibsBool(f.show_on_card_header)||ibsBool(f.show_on_card)||ibsBool(f.card)?'on':'')+'"><i class="fa-solid fa-id-card"></i> Card</span>'+
         '<span class="acs-chip view '+(ibsBool(f.show_on_view_header)||ibsBool(f.show_on_view)||ibsBool(f.card)?'on':'')+'"><i class="fa-solid fa-eye"></i> View</span>';
}
function ibsOptionValues(row){var hid=row.querySelector('.ibs-options-value'); return ibsStringToOptions(hid ? hid.value : '');}
function ibsSetOptionValues(row, values){
  var clean=[];(values||[]).forEach(function(v){v=String(v||'').trim(); if(v && clean.indexOf(v)===-1) clean.push(v);});
  var hid=row.querySelector('.ibs-options-value'); if(hid) hid.value=clean.join(', ');
  ibsRenderOptionChips(row, clean); ibsRead(false);
}
function ibsRenderOptionChips(row, values){
  var chips=row.querySelector('.ibs-option-chips'); if(!chips) return;
  var clean=Array.isArray(values)?values:ibsStringToOptions(values); chips.innerHTML='';
  var count=row.querySelector('.ibs-options-count'); if(count) count.textContent=clean.length+(clean.length===1?' option':' options');
  if(!clean.length){chips.innerHTML='<span class="acs-option-empty"><i class="fa-regular fa-circle-dot me-1"></i>No options yet. Add options or use a quick preset.</span>'; return;}
  clean.forEach(function(opt){var chip=document.createElement('span'); chip.className='acs-option-chip'; chip.innerHTML='<span>'+ibsEsc(opt)+'</span><button type="button" title="Remove option"><i class="fa-solid fa-xmark"></i></button>'; chip.querySelector('button').onclick=function(){ibsSetOptionValues(row, ibsOptionValues(row).filter(function(x){return x!==opt;}));}; chips.appendChild(chip);});
}
function ibsAddOptionFromInput(input){var row=input.closest('.acs-field'); if(!row) return; var val=String(input.value||'').trim().replace(/,+$/,'').trim(); if(!val) return; var values=ibsOptionValues(row); values.push(val); input.value=''; ibsSetOptionValues(row,values);}
function ibsFlag(k,label,f){return '<label class="acs-toggle"><input type="checkbox" class="ibs-in" data-k="'+k+'" '+(ibsBool(f[k])?'checked':'')+'> '+label+'</label>';}
function ibsRender(){
  var wrap=document.getElementById('ibsFields'); if(!wrap) return; wrap.innerHTML='';
  if(!Array.isArray(IBS_FIELDS) || !IBS_FIELDS.length){wrap.innerHTML='<div class="acs-empty-state"><i class="fa-solid fa-gift"></i><div class="fw-bold text-white mb-1">No item attributes yet</div><div>Add Item Type, Server, Delivery Time, Amount, or a custom field above.</div></div>'; ibsRefreshPreview(); ibsSyncJson(); return;}
  IBS_FIELDS.forEach(function(f,i){
    f=f||{}; var type=f.type||'text'; var isOpen=(IBS_OPEN_INDEX===i);
    var div=document.createElement('div'); div.className='acs-field'+(isOpen?' open':''); div.dataset.index=i;
    div.innerHTML=
      '<div class="acs-field-main">'+
        '<div class="acs-field-name"><div class="acs-field-ico"><i class="'+ibsEsc(ibsTypeIcon(f))+'"></i></div><div><div class="acs-field-title">'+ibsEsc(f.label||'New Item Attribute')+'</div><div class="acs-field-key">'+ibsEsc(f.key||'no_key')+'</div></div></div>'+ 
        '<div class="acs-field-meta"><span class="acs-chip type">'+ibsEsc(type)+'</span><div class="acs-chip-row">'+ibsFieldFlags(f)+'</div></div>'+ 
        '<div class="acs-field-actions"><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-edit" onclick="ibsToggleEdit('+i+')"><i class="fa-solid fa-pen me-1"></i>Edit</button><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-move" onclick="ibsMove('+i+',-1)"><i class="fa-solid fa-arrow-up me-1"></i>Up</button><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-move" onclick="ibsMove('+i+',1)"><i class="fa-solid fa-arrow-down me-1"></i>Down</button><button type="button" class="btn btn-ghost-danger btn-sm acs-action-delete" onclick="ibsRemove('+i+')"><i class="fa-solid fa-trash-can me-1"></i>Delete</button></div>'+ 
      '</div>'+ 
      '<div class="acs-edit-panel">'+
        '<div class="acs-edit-grid">'+
          '<div><div class="acs-mini-label">Key</div><input class="form-control form-control-sm ibs-in ibs-key-input" data-k="key" value="'+ibsEsc(f.key||'')+'" placeholder="server"></div>'+ 
          '<div><div class="acs-mini-label">Label</div><input class="form-control form-control-sm ibs-in" data-k="label" value="'+ibsEsc(f.label||'')+'" placeholder="Server"></div>'+ 
          '<div><div class="acs-mini-label">Input type</div><select class="form-select form-select-sm ibs-in" data-k="type"><option value="text">Text</option><option value="number">Number</option><option value="select">Select</option><option value="multiselect">Multi Select</option><option value="checkbox">Checkbox</option><option value="textarea">Textarea</option></select></div>'+ 
        '</div>'+ 
        '<div class="acs-toggle-grid">'+ibsFlag('required','Required',f)+ibsFlag('show_on_upload','Seller upload',f)+ibsFlag('filterable','Shop filter',f)+ibsFlag('show_on_card_header','Card header icon',f)+ibsFlag('show_on_card','Card badge',f)+ibsFlag('show_on_view_header','View header',f)+ibsFlag('show_on_view','View details',f)+'</div>'+ 
        '<div class="acs-edit-grid mt-3">'+
          '<div><div class="acs-mini-label">Filter style</div><select class="form-select form-select-sm ibs-in" data-k="filter_type"><option value="">Auto</option><option value="select">Select</option><option value="multiselect">Multi Select</option><option value="range">Range / Slider</option><option value="checkbox">Checkbox</option></select></div>'+ 
          '<div><div class="acs-mini-label">Icon type</div><select class="form-select form-select-sm ibs-in" data-k="icon_type"><option value="">FontAwesome</option><option value="platform">Platform Icons</option><option value="rank">Rank Icons</option></select></div>'+ 
          '<div><div class="acs-mini-label">FontAwesome icon</div><input class="form-control form-control-sm ibs-in" data-k="icon" value="'+ibsEsc(f.icon||'')+'" placeholder="fa-solid fa-gift"></div>'+ 
          '<div><div class="acs-mini-label">Suffix / unit</div><input class="form-control form-control-sm ibs-in" data-k="suffix" value="'+ibsEsc(f.suffix||'')+'" placeholder="RP"></div>'+ 
          '<div><div class="acs-mini-label">Min</div><input type="number" class="form-control form-control-sm ibs-in" data-k="min" value="'+ibsEsc(f.min??'')+'" placeholder="0"></div>'+ 
          '<div><div class="acs-mini-label">Max</div><input type="number" class="form-control form-control-sm ibs-in" data-k="max" value="'+ibsEsc(f.max??'')+'" placeholder="9999"></div>'+ 
        '</div>'+ 
        '<div class="acs-options-box"><div class="acs-options-head"><div><strong>Selectable options</strong><span>Used for dropdowns and filters.</span></div><div class="ibs-options-count">0 options</div></div><div class="acs-option-row"><input class="form-control form-control-sm ibs-opt-input" placeholder="Example: EUW"><button type="button" class="btn btn-primary btn-sm ibs-opt-add"><i class="fa-solid fa-plus"></i> Add option</button></div><div class="acs-quick-options"><span class="acs-preset-label">Quick add:</span><button type="button" class="btn btn-ghost-secondary btn-xs ibs-opt-preset" data-preset="item_types">Item Types</button><button type="button" class="btn btn-ghost-secondary btn-xs ibs-opt-preset" data-preset="servers">Servers</button><button type="button" class="btn btn-ghost-secondary btn-xs ibs-opt-preset" data-preset="platforms">Platforms</button><button type="button" class="btn btn-ghost-secondary btn-xs ibs-opt-preset" data-preset="yesno">Yes / No</button></div><div class="ibs-option-chips acs-option-chips"></div><input type="hidden" class="ibs-in ibs-options-value" data-k="options" value="'+ibsEsc(ibsOptionsToString(f.options))+'"></div>'+ 
      '</div>';
    wrap.appendChild(div);
    div.querySelector('[data-k="type"]').value=type;
    div.querySelector('[data-k="filter_type"]').value=f.filter_type||'';
    div.querySelector('[data-k="icon_type"]').value=f.icon_type||'';
    ibsRenderOptionChips(div, f.options || []);
  });
  ibsBind(); ibsRefreshPreview(); ibsSyncJson();
}
function ibsBind(){
  document.querySelectorAll('#ibsFields .ibs-in').forEach(function(inp){inp.oninput=inp.onchange=function(){ibsRead(false);};});
  document.querySelectorAll('#ibsFields .ibs-key-input').forEach(function(inp){inp.onblur=function(){inp.value=ibsSlug(inp.value); ibsRead(false); ibsRender();};});
  document.querySelectorAll('#ibsFields .ibs-opt-input').forEach(function(inp){inp.onkeydown=function(e){if(e.key==='Enter' || e.key===','){e.preventDefault(); ibsAddOptionFromInput(inp);}};});
  document.querySelectorAll('#ibsFields .ibs-opt-add').forEach(function(btn){btn.onclick=function(){var inp=btn.closest('.acs-field').querySelector('.ibs-opt-input'); if(inp) ibsAddOptionFromInput(inp);};});
  document.querySelectorAll('#ibsFields .ibs-opt-preset').forEach(function(btn){btn.onclick=function(){var row=btn.closest('.acs-field'); ibsSetOptionValues(row, IBS_OPTION_PRESETS[btn.dataset.preset]||[]);};});
}
function ibsRead(rerender){
  var fields=[];
  document.querySelectorAll('#ibsFields .acs-field').forEach(function(row){
    var obj={};
    row.querySelectorAll('.ibs-in').forEach(function(inp){
      var k=inp.dataset.k; if(!k) return;
      if(inp.type==='checkbox'){ if(inp.checked) obj[k]=true; return; }
      var v=inp.value;
      if(k==='options'){ var opts=ibsStringToOptions(v); if(opts.length) obj.options=opts; return; }
      if(k==='min'||k==='max'){ if(v!=='') obj[k]=parseInt(v,10); return; }
      if(v!=='') obj[k]=v;
    });
    if(obj.key) fields.push(obj);
  });
  IBS_FIELDS=fields; ibsRefreshPreview(); ibsSyncJson(); if(rerender) ibsRender();
}
function ibsSampleValue(f){
  if(f.type==='number') return (f.key||'').indexOf('delivery')!==-1 ? '7' : '500';
  if(f.type==='checkbox') return 'Yes';
  if(Array.isArray(f.options) && f.options.length) return f.options[0];
  if((f.key||'').indexOf('title')!==-1) return 'Premium Skin Bundle';
  if((f.key||'').indexOf('server')!==-1) return 'EUW';
  if((f.key||'').indexOf('type')!==-1) return 'Skins';
  return f.label || 'Value';
}
function ibsValueWithSuffix(f){var value=String(ibsSampleValue(f)||'').trim(); var suffix=String((f&&f.suffix)?f.suffix:'').trim(); return (value+(suffix?' '+suffix:'')).replace(/\s+/g,' ').trim();}
function ibsPreviewIconHtml(f){if((f.icon_type||'')==='platform') return '<i class="fa-brands fa-playstation"></i>'; return '<i class="'+ibsEsc(ibsTypeIcon(f))+'"></i>';}
function ibsRefreshPreview(){
  var fields=IBS_FIELDS||[];
  var titleKey=(document.getElementById('ibsTitleField')||{}).value||'title';
  var iconKey=(document.getElementById('ibsIconField')||{}).value||'type';
  var titleField=fields.find(function(f){return f.key===titleKey;}) || fields.find(function(f){return ibsBool(f.show_on_card_header);}) || fields[0] || {label:'Item',key:'title'};
  var iconField=fields.find(function(f){return f.key===iconKey;}) || fields.find(function(f){return ibsBool(f.show_on_card_header);}) || titleField;
  var filters=fields.filter(function(f){return ibsBool(f.filterable);});
  var badges=fields.filter(function(f){return ibsBool(f.show_on_card)||ibsBool(f.card);}).slice(0,6);
  var upload=fields.filter(function(f){return ibsBool(f.show_on_upload);});
  var view=fields.filter(function(f){return ibsBool(f.show_on_view)||ibsBool(f.card);}).slice(0,8);
  var pf=document.getElementById('ibPvFilters'); if(pf) pf.innerHTML=filters.length?filters.map(function(f){return '<span class="pv-filter"><i class="fa-solid fa-filter me-1"></i>'+ibsEsc(f.label||f.key)+'</span>';}).join(''):'<span class="ibs-note">No shop filters enabled.</span>';
  var pc=document.getElementById('ibPvCard'); if(pc) pc.innerHTML='<div class="pv-shop-card"><div class="pv-shop-head"><div class="pv-iconbox">'+ibsPreviewIconHtml(iconField)+'<span class="pv-game-badge">I</span></div><div><div class="pv-shop-name">'+ibsEsc(ibsSampleValue(titleField))+'</div><div class="pv-shop-sub">Seller item listing</div></div></div><div class="pv-img"><i class="fa-solid fa-image"></i></div><div class="pv-badges">'+(badges.length?badges.map(function(f){return '<span class="pv-badge">'+ibsPreviewIconHtml(f)+' '+ibsEsc(ibsValueWithSuffix(f))+'</span>';}).join(''):'<span class="ibs-note">No card badges enabled.</span>')+'</div><div class="pv-price">€15.00 EUR</div></div>';
  var pu=document.getElementById('ibPvUpload'); if(pu){var uploadShown=upload.slice(0,6); pu.innerHTML=upload.length?uploadShown.map(function(f){return '<div class="pv-upload-field"><span>'+ibsEsc(f.label||f.key)+'</span><small>'+ibsEsc(f.type||'text')+(ibsBool(f.required)?' · required':'')+'</small></div>';}).join('')+(upload.length>6?'<div class="ibs-note">+'+(upload.length-6)+' more upload fields</div>':''):'<div class="ibs-note">No seller upload fields enabled.</div>';}
  var pv=document.getElementById('ibPvView'); if(pv){var viewShown=view.slice(0,6); pv.innerHTML=view.length?viewShown.map(function(f){return '<div class="pv-detail"><b>'+ibsEsc(f.label||f.key)+'</b><span>'+ibsPreviewIconHtml(f)+' '+ibsEsc(ibsValueWithSuffix(f))+'</span></div>';}).join('')+(view.length>6?'<div class="ibs-note">+'+(view.length-6)+' more view fields</div>':''):'<div class="ibs-note">No view details enabled.</div>';}
}
function ibsToggleEdit(i){ibsRead(false); IBS_OPEN_INDEX=(IBS_OPEN_INDEX===i?null:i); ibsRender();}
function ibsAddField(){ibsRead(false); IBS_FIELDS.push({key:'new_attribute',label:'New Item Attribute',type:'text',show_on_upload:true,show_on_view:true}); IBS_OPEN_INDEX=IBS_FIELDS.length-1; ibsRender();}
function ibsAddTemplate(kind){
  ibsRead(false); var tpl={key:'new_attribute',label:'New Item Attribute',type:'text',show_on_upload:true,show_on_view:true};
  if(kind==='type') tpl={key:'type',label:'Item Type',type:'select',options:['Skins','Bundles','Top Up','Gift Card'],required:true,show_on_upload:true,filterable:true,filter_type:'multiselect',show_on_card_header:true,show_on_view_header:true,show_on_view:true,icon:'fa-solid fa-tags'};
  if(kind==='server') tpl={key:'server',label:'Server',type:'select',options:['EUW','EUNE','NA','ME','BR','TR','KR','JP','OCE','Global'],required:true,show_on_upload:true,filterable:true,filter_type:'multiselect',show_on_card:true,show_on_view:true,icon:'fa-solid fa-globe'};
  if(kind==='delivery') tpl={key:'delivery_time',label:'Delivery Time',type:'number',min:0,max:30,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fa-solid fa-clock',suffix:'days'};
  if(kind==='amount') tpl={key:'amount',label:'Amount',type:'number',min:0,show_on_upload:true,filterable:true,filter_type:'range',show_on_card:true,show_on_view:true,icon:'fa-solid fa-coins'};
  IBS_FIELDS.push(tpl); IBS_OPEN_INDEX=IBS_FIELDS.length-1; if(kind==='type'){var h=document.getElementById('ibsIconField'); if(h && !h.value) h.value='type';} ibsRender();
}
function ibsRemove(i){ibsRead(false); var f=IBS_FIELDS[i]||{}; if(!confirm('Delete item attribute "'+(f.label||f.key||'')+'"?')) return; IBS_FIELDS.splice(i,1); IBS_OPEN_INDEX=null; ibsRender();}
function ibsMove(i,d){ibsRead(false); var ni=i+d; if(ni<0||ni>=IBS_FIELDS.length) return; var tmp=IBS_FIELDS[i]; IBS_FIELDS[i]=IBS_FIELDS[ni]; IBS_FIELDS[ni]=tmp; IBS_OPEN_INDEX=ni; ibsRender();}
function ibsLoadPreset(name){if(!IBS_PRESETS[name]) return; IBS_FIELDS=JSON.parse(JSON.stringify(IBS_PRESETS[name])); IBS_OPEN_INDEX=null; var t=document.getElementById('ibsTitleField'); if(t)t.value='type'; var h=document.getElementById('ibsIconField'); if(h)h.value='type'; ibsRender();}
function ibsToggleJson(){ibsRead(false); var w=document.getElementById('ibsJsonWrap'); if(!w)return; w.style.display=(w.style.display==='none'||!w.style.display)?'block':'none'; ibsSyncJson();}
function saveItemsCfg(gameId) {
  ibsRead(false);
  var schema=ibsBuildSchema();
  var jsonWrap=document.getElementById('ibsJsonWrap');
  if(jsonWrap && jsonWrap.style.display!=='none'){
    try { schema=JSON.parse(document.getElementById('ibsJson').value); } catch(e){ toast('Items schema JSON is invalid','error'); return; }
  }
  if(!Array.isArray(schema.fields)) schema.fields=[];
  var body=new FormData();
  body.append('page_title', (document.getElementById('ibsPageTitle')||{}).value || '');
  body.append('page_description', (document.getElementById('ibsPageDesc')||{}).value || '');
  body.append('show_type_cards', document.getElementById('ibsTypeCards') && document.getElementById('ibsTypeCards').checked ? '1' : '0');
  body.append('schema_enabled', schema.enabled ? '1' : '0');
  body.append('schema_json', JSON.stringify(schema));
  fetch('/admin-area/games/'+gameId+'/items-config',{method:'POST',body:body})
    .then(function(r){return r.text().then(function(txt){var data=null;try{data=JSON.parse(txt);}catch(e){} if(!r.ok || (data && data.success===false)){throw new Error((data && data.error)?data.error:(txt||'bad response'));} return data||{success:true};});})
    .then(function(){ toast('Items Builder saved!','success'); try { history.replaceState(null, '', window.location.pathname + '?items_saved=1'); } catch(e) {} })
    .catch(function(err){ toast('Save failed: '+err.message,'error'); });
}
document.addEventListener('DOMContentLoaded', function(){ ibsRender(); });

/* Dynamic Accounts Builder */
var ACS_FIELDS = <?= json_encode($schemaFields ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var ACS_OPEN_INDEX = null;
var ACS_PRESETS = {
  cod: [
    {key:'main_title',label:'Main Game',type:'select',options:['Black Ops 6','Black Ops 7','Modern Warfare','Modern Warfare 2','Modern Warfare 3','Black Ops / Warzone 1','Warzone','Warzone 2'],required:true,show_on_upload:true,show_on_view:true,show_on_card_header:true,filterable:true,filter_type:'select',icon:'fa-solid fa-gamepad'},
    {key:'cod_titles',label:'Extra Games',type:'multiselect',options:['Black Ops 6','Black Ops 7','Modern Warfare','Modern Warfare 2','Modern Warfare 3','Black Ops / Warzone 1','Warzone','Warzone 2'],show_on_upload:true,show_on_view:true,filterable:true,filter_type:'multiselect',icon:'fa-solid fa-layer-group'},
    {key:'platform',label:'Platforms',type:'select',options:['PC (Game Pass)','PlayStation','Xbox One','BattleNet','Steam','Nintendo Switch','Android','iOS','All Platforms'],required:true,show_on_upload:true,show_on_view:true,show_on_card_header:true,show_on_view_header:true,filterable:true,filter_type:'multiselect',icon_type:'platform',icon:'fa-solid fa-desktop'},
    {key:'level',label:'Account Level',type:'number',min:0,max:1000,show_on_upload:true,show_on_card:true,show_on_view:true,filterable:true,filter_type:'range',icon:'fas fa-arrow-turn-up',suffix:' Level'},
    {key:'prestige',label:'Prestige Level',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-medal',suffix:' Prestige'},
    {key:'operators',label:'Operators',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-users',suffix:' Operators'},
    {key:'weapons',label:'Weapon Unlocks',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-gun',suffix:' Weapons'},
    {key:'camos',label:'Camos',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-palette',suffix:' Camos'},
    {key:'cod_points',label:'CoD Points',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-coins',suffix:' Points'},
    {key:'ranked_ready',label:'Ranked Ready',type:'checkbox',show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fas fa-circle-check'}
  ]
};
var ACS_OPTION_PRESETS={
  platforms:['PC (Game Pass)','PlayStation','Xbox One','BattleNet','Steam','Nintendo Switch','Android','iOS','All Platforms'],
  yesno:['Yes','No'],
  ranks:['Unranked','Bronze','Silver','Gold','Platinum','Diamond','Master','Grandmaster','Challenger']
};
function acsEsc(v){return String(v==null?'':v).replace(/[&<>'"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c];});}
function acsBool(v){return v===true || v===1 || v==='1' || v==='true';}
function acsSlug(v){return String(v||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');}
function acsOptionsToString(v){if(Array.isArray(v)) return v.map(function(o){return (typeof o==='object'?(o.label||o.value||''):o);}).join(', '); return String(v||'');}
function acsStringToOptions(v){return String(v||'').split(',').map(function(x){return x.trim();}).filter(Boolean);}
function acsTypeIcon(f){
  if((f.icon_type||'')==='platform') return 'fa-solid fa-desktop';
  if((f.icon_type||'')==='rank') return 'fa-solid fa-medal';
  return f.icon || (f.type==='number'?'fa-solid fa-hashtag':(f.type==='checkbox'?'fa-solid fa-toggle-on':'fa-solid fa-pen'));
}
function acsBuildSchema(){return {enabled:document.getElementById('acsEnabled')?document.getElementById('acsEnabled').checked:true,title_field:(document.getElementById('acsTitleField')||{}).value||'',headline_icon_field:(document.getElementById('acsIconField')||{}).value||'',fields:ACS_FIELDS};}
function acsSyncJson(){var j=document.getElementById('acsJson'); if(j){j.value=JSON.stringify(acsBuildSchema(),null,2);}}
function acsFieldFlags(f){
  return '<span class="acs-chip upload '+(acsBool(f.show_on_upload)?'on':'')+'"><i class="fa-solid fa-upload"></i> Upload</span>'+
         '<span class="acs-chip filter '+(acsBool(f.filterable)?'on':'')+'"><i class="fa-solid fa-filter"></i> Filter</span>'+
         '<span class="acs-chip card '+(acsBool(f.show_on_card_header)||acsBool(f.show_on_card)?'on':'')+'"><i class="fa-solid fa-id-card"></i> Card</span>'+
         '<span class="acs-chip view '+(acsBool(f.show_on_view_header)||acsBool(f.show_on_view)?'on':'')+'"><i class="fa-solid fa-eye"></i> View</span>';
}
function acsOptionValues(row){var hid=row.querySelector('.acs-options-value'); return acsStringToOptions(hid ? hid.value : '');}
function acsSetOptionValues(row, values){
  var clean=[];(values||[]).forEach(function(v){v=String(v||'').trim(); if(v && clean.indexOf(v)===-1) clean.push(v);});
  var hid=row.querySelector('.acs-options-value'); if(hid) hid.value=clean.join(', ');
  acsRenderOptionChips(row, clean); acsRead(false);
}
function acsRenderOptionChips(row, values){
  var chips=row.querySelector('.acs-option-chips'); if(!chips) return;
  var clean=Array.isArray(values)?values:acsStringToOptions(values); chips.innerHTML='';
  var count=row.querySelector('.acs-options-count');
  if(count) count.textContent=clean.length+(clean.length===1?' option':' options');
  if(!clean.length){chips.innerHTML='<span class="acs-option-empty"><i class="fa-regular fa-circle-dot me-1"></i>No options yet. Add options or use a quick preset.</span>'; return;}
  clean.forEach(function(opt){
    var chip=document.createElement('span'); chip.className='acs-option-chip';
    chip.innerHTML='<span>'+acsEsc(opt)+'</span><button type="button" title="Remove option"><i class="fa-solid fa-xmark"></i></button>';
    chip.querySelector('button').onclick=function(){acsSetOptionValues(row, acsOptionValues(row).filter(function(x){return x!==opt;}));};
    chips.appendChild(chip);
  });
}
function acsAddOptionFromInput(input){
  var row=input.closest('.acs-field'); if(!row) return;
  var val=String(input.value||'').trim().replace(/,+$/,'').trim(); if(!val) return;
  var values=acsOptionValues(row); values.push(val); input.value=''; acsSetOptionValues(row,values);
}
function acsFlag(k,label,f){return '<label class="acs-toggle"><input type="checkbox" class="acs-in" data-k="'+k+'" '+(acsBool(f[k])?'checked':'')+'> '+label+'</label>';}
function acsRender(){
  var wrap=document.getElementById('acsFields'); if(!wrap) return; wrap.innerHTML='';
  if(!Array.isArray(ACS_FIELDS) || !ACS_FIELDS.length){
    wrap.innerHTML='<div class="acs-empty-state"><i class="fa-solid fa-cubes"></i><div class="fw-bold text-white mb-1">No attributes yet</div><div>Add Platform, Rank, Level, or a custom attribute above.</div></div>';
    acsRefreshPreview(); acsSyncJson(); return;
  }
  ACS_FIELDS.forEach(function(f,i){
    f=f||{}; var type=f.type||'text'; var isOpen=(ACS_OPEN_INDEX===i);
    var div=document.createElement('div'); div.className='acs-field'+(isOpen?' open':''); div.dataset.index=i;
    div.innerHTML=
      '<div class="acs-field-main">'+
        '<div class="acs-field-name"><div class="acs-field-ico"><i class="'+acsEsc(acsTypeIcon(f))+'"></i></div><div><div class="acs-field-title">'+acsEsc(f.label||'New Attribute')+'</div><div class="acs-field-key">'+acsEsc(f.key||'no_key')+'</div></div></div>'+ 
        '<div class="acs-field-meta"><span class="acs-chip type">'+acsEsc(type)+'</span><div class="acs-chip-row">'+acsFieldFlags(f)+'</div></div>'+ 
        '<div class="acs-field-actions"><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-edit" onclick="acsToggleEdit('+i+')"><i class="fa-solid fa-pen me-1"></i>Edit</button><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-move" title="Move up" onclick="acsMove('+i+',-1)"><i class="fa-solid fa-arrow-up me-1"></i>Up</button><button type="button" class="btn btn-ghost-secondary btn-sm acs-action-move" title="Move down" onclick="acsMove('+i+',1)"><i class="fa-solid fa-arrow-down me-1"></i>Down</button><button type="button" class="btn btn-ghost-danger btn-sm acs-action-delete" onclick="acsRemove('+i+')"><i class="fa-solid fa-trash-can me-1"></i>Delete</button></div>'+ 
      '</div>'+ 
      '<div class="acs-edit-panel">'+
        '<div class="acs-edit-grid">'+
          '<div><div class="acs-mini-label">Key</div><input class="form-control form-control-sm acs-in acs-key-input" data-k="key" value="'+acsEsc(f.key||'')+'" placeholder="platform"></div>'+ 
          '<div><div class="acs-mini-label">Label</div><input class="form-control form-control-sm acs-in" data-k="label" value="'+acsEsc(f.label||'')+'" placeholder="Platform"></div>'+ 
          '<div><div class="acs-mini-label">Input type</div><select class="form-select form-select-sm acs-in" data-k="type"><option value="text">Text</option><option value="number">Number</option><option value="select">Select</option><option value="multiselect">Multi Select</option><option value="checkbox">Checkbox</option><option value="textarea">Textarea</option></select></div>'+ 
        '</div>'+ 
        '<div class="acs-toggle-grid">'+acsFlag('required','Required',f)+acsFlag('show_on_upload','Seller upload',f)+acsFlag('filterable','Shop filter',f)+acsFlag('show_on_card_header','Card header icon',f)+acsFlag('show_on_card','Card badge',f)+acsFlag('show_on_view_header','View header',f)+acsFlag('show_on_view','View details',f)+'</div>'+ 
        '<div class="acs-edit-grid mt-3">'+
          '<div><div class="acs-mini-label">Filter style</div><select class="form-select form-select-sm acs-in" data-k="filter_type"><option value="">Auto</option><option value="select">Select</option><option value="multiselect">Multi Select</option><option value="range">Range / Slider</option><option value="checkbox">Checkbox</option></select></div>'+ 
          '<div><div class="acs-mini-label">Icon type</div><select class="form-select form-select-sm acs-in" data-k="icon_type"><option value="">FontAwesome</option><option value="platform">Platform Icons</option><option value="rank">Rank Icons</option></select></div>'+ 
          '<div><div class="acs-mini-label">FontAwesome icon</div><input class="form-control form-control-sm acs-in" data-k="icon" value="'+acsEsc(f.icon||'')+'" placeholder="fa-solid fa-gamepad"></div>'+ 
          '<div><div class="acs-mini-label">Suffix / unit</div><input class="form-control form-control-sm acs-in" data-k="suffix" value="'+acsEsc(f.suffix||'')+'" placeholder="Emotes"><div class="acs-help">Space is added automatically: 5 Emotes</div></div>'+ 
          '<div><div class="acs-mini-label">Min</div><input type="number" class="form-control form-control-sm acs-in" data-k="min" value="'+acsEsc(f.min??'')+'" placeholder="0"></div>'+ 
          '<div><div class="acs-mini-label">Max</div><input type="number" class="form-control form-control-sm acs-in" data-k="max" value="'+acsEsc(f.max??'')+'" placeholder="1000"></div>'+ 
        '</div>'+ 
        '<div class="acs-options-box"><div class="acs-options-head"><div><strong>Selectable options</strong><span>Used for dropdowns and multi-select filters. Add one option per chip.</span></div><div class="acs-options-count">0 options</div></div><div class="acs-option-row"><input class="form-control form-control-sm acs-opt-input" placeholder="Example: PlayStation"><button type="button" class="btn btn-primary btn-sm acs-opt-add"><i class="fa-solid fa-plus"></i> Add option</button></div><div class="acs-quick-options"><span class="acs-preset-label">Quick add:</span><button type="button" class="btn btn-ghost-secondary btn-xs acs-opt-preset" data-preset="platforms">Platforms</button><button type="button" class="btn btn-ghost-secondary btn-xs acs-opt-preset" data-preset="yesno">Yes / No</button><button type="button" class="btn btn-ghost-secondary btn-xs acs-opt-preset" data-preset="ranks">Ranks</button></div><div class="acs-option-chips"></div><input type="hidden" class="acs-in acs-options-value" data-k="options" value="'+acsEsc(acsOptionsToString(f.options))+'"></div>'+ 
      '</div>';
    wrap.appendChild(div);
    div.querySelector('[data-k="type"]').value=type;
    div.querySelector('[data-k="filter_type"]').value=f.filter_type||'';
    div.querySelector('[data-k="icon_type"]').value=f.icon_type||'';
    acsRenderOptionChips(div, f.options || []);
  });
  acsBind(); acsRefreshPreview(); acsSyncJson();
}
function acsBind(){
  document.querySelectorAll('#acsFields .acs-in').forEach(function(inp){inp.oninput=inp.onchange=function(){acsRead(false);};});
  document.querySelectorAll('#acsFields .acs-key-input').forEach(function(inp){inp.onblur=function(){inp.value=acsSlug(inp.value); acsRead(false); acsRender();};});
  document.querySelectorAll('#acsFields .acs-opt-input').forEach(function(inp){inp.onkeydown=function(e){if(e.key==='Enter' || e.key===','){e.preventDefault(); acsAddOptionFromInput(inp);}};});
  document.querySelectorAll('#acsFields .acs-opt-add').forEach(function(btn){btn.onclick=function(){var inp=btn.closest('.acs-field').querySelector('.acs-opt-input'); if(inp) acsAddOptionFromInput(inp);};});
  document.querySelectorAll('#acsFields .acs-opt-preset').forEach(function(btn){btn.onclick=function(){var row=btn.closest('.acs-field'); acsSetOptionValues(row, ACS_OPTION_PRESETS[btn.dataset.preset]||[]);};});
}
function acsRead(rerender){
  var fields=[];
  document.querySelectorAll('#acsFields .acs-field').forEach(function(row){
    var obj={};
    row.querySelectorAll('.acs-in').forEach(function(inp){
      var k=inp.dataset.k; if(!k) return;
      if(inp.type==='checkbox'){ if(inp.checked) obj[k]=true; return; }
      var v=inp.value;
      if(k==='options'){ var opts=acsStringToOptions(v); if(opts.length) obj.options=opts; return; }
      if(k==='min'||k==='max'){ if(v!=='') obj[k]=parseInt(v,10); return; }
      if(v!=='') obj[k]=v;
    });
    if(obj.key) fields.push(obj);
  });
  ACS_FIELDS=fields; acsRefreshPreview(); acsSyncJson(); if(rerender) acsRender();
}
function acsSampleValue(f){
  if(f.type==='number') return '5';
  if(f.type==='checkbox') return 'Yes';
  if(Array.isArray(f.options) && f.options.length) return f.options[0];
  if((f.key||'').indexOf('title')!==-1) return 'Black Ops 7';
  if((f.key||'').indexOf('platform')!==-1) return 'PlayStation';
  if((f.key||'').indexOf('rank')!==-1) return 'Gold IV';
  return f.label || 'Value';
}

function acsValueWithSuffix(f){
  var value = String(acsSampleValue(f) || '').trim();
  var suffix = String((f && f.suffix) ? f.suffix : '').trim();
  var prefix = String((f && f.prefix) ? f.prefix : '').trim();
  var out = value;
  if (prefix) out = prefix + ' ' + out;
  if (suffix) out = out + ' ' + suffix;
  return out.replace(/\s+/g, ' ').trim();
}
function acsPreviewIconHtml(f){
  if((f.icon_type||'')==='platform') return '<i class="fa-brands fa-playstation"></i>';
  if((f.icon_type||'')==='rank') return '<i class="fa-solid fa-medal"></i>';
  return '<i class="'+acsEsc(acsTypeIcon(f))+'"></i>';
}
function acsRefreshPreview(){
  var fields=ACS_FIELDS||[];
  var titleKey=(document.getElementById('acsTitleField')||{}).value||'';
  var iconKey=(document.getElementById('acsIconField')||{}).value||'';
  var titleField=fields.find(function(f){return f.key===titleKey;}) || fields.find(function(f){return acsBool(f.show_on_card_header);}) || fields[0] || {label:'Account',key:'title'};
  var iconField=fields.find(function(f){return f.key===iconKey;}) || fields.find(function(f){return acsBool(f.show_on_card_header);}) || titleField;
  var filters=fields.filter(function(f){return acsBool(f.filterable);});
  var badges=fields.filter(function(f){return acsBool(f.show_on_card);}).slice(0,6);
  var upload=fields.filter(function(f){return acsBool(f.show_on_upload);});
  var view=fields.filter(function(f){return acsBool(f.show_on_view);}).slice(0,8);
  var pf=document.getElementById('pvFilters'); if(pf) pf.innerHTML=filters.length?filters.map(function(f){return '<span class="pv-filter"><i class="fa-solid fa-filter me-1"></i>'+acsEsc(f.label||f.key)+'</span>';}).join(''):'<span class="acs-help">No shop filters enabled.</span>';
  var pc=document.getElementById('pvCard'); if(pc) pc.innerHTML='<div class="pv-shop-card"><div class="pv-shop-head"><div class="pv-iconbox">'+acsPreviewIconHtml(iconField)+'<span class="pv-game-badge">G</span></div><div><div class="pv-shop-name">'+acsEsc(acsSampleValue(titleField))+'</div><div class="pv-shop-sub">Short description text</div></div></div><div class="pv-img"><i class="fa-solid fa-image"></i></div><div class="pv-badges">'+(badges.length?badges.map(function(f){return '<span class="pv-badge">'+acsPreviewIconHtml(f)+' '+acsEsc(acsValueWithSuffix(f))+'</span>';}).join(''):'<span class="acs-help">No card badges enabled.</span>')+'</div><div class="pv-price">€15.00 EUR</div></div>';
  var pu=document.getElementById('pvUpload'); if(pu){var uploadShown=upload.slice(0,6); pu.innerHTML=upload.length?uploadShown.map(function(f){return '<div class="pv-upload-field"><span>'+acsEsc(f.label||f.key)+'</span><small>'+acsEsc(f.type||'text')+(acsBool(f.required)?' · required':'')+'</small></div>';}).join('')+(upload.length>6?'<div class="acs-help">+'+(upload.length-6)+' more upload fields</div>':''):'<div class="acs-help">No seller upload fields enabled.</div>';}
  var pv=document.getElementById('pvView'); if(pv){var viewShown=view.slice(0,6); pv.innerHTML=view.length?viewShown.map(function(f){return '<div class="pv-detail"><b>'+acsEsc(f.label||f.key)+'</b><span>'+acsPreviewIconHtml(f)+' '+acsEsc(acsValueWithSuffix(f))+'</span></div>';}).join('')+(view.length>6?'<div class="acs-help">+'+(view.length-6)+' more view fields</div>':''):'<div class="acs-help">No view details enabled.</div>';}
}
function acsToggleEdit(i){acsRead(false); ACS_OPEN_INDEX=(ACS_OPEN_INDEX===i?null:i); acsRender();}
function acsAddField(){acsRead(false); ACS_FIELDS.push({key:'new_attribute',label:'New Attribute',type:'text',show_on_upload:true,show_on_view:true}); ACS_OPEN_INDEX=ACS_FIELDS.length-1; acsRender();}
function acsAddTemplate(kind){
  acsRead(false);
  var tpl={key:'new_attribute',label:'New Attribute',type:'text',show_on_upload:true,show_on_view:true};
  if(kind==='platform') tpl={key:'platform',label:'Platform',type:'select',options:['PC','PlayStation','Xbox','Steam','Nintendo Switch','Android','iOS'],required:true,show_on_upload:true,filterable:true,filter_type:'multiselect',show_on_card_header:true,show_on_view_header:true,show_on_view:true,icon_type:'platform',icon:'fa-solid fa-desktop'};
  if(kind==='rank') tpl={key:'rank',label:'Rank',type:'select',options:['Unranked','Bronze','Silver','Gold','Platinum','Diamond','Master'],required:true,show_on_upload:true,filterable:true,filter_type:'select',show_on_card_header:true,show_on_view_header:true,show_on_view:true,icon_type:'rank',icon:'fa-solid fa-medal'};
  if(kind==='level') tpl={key:'level',label:'Account Level',type:'number',min:0,max:1000,show_on_upload:true,filterable:true,filter_type:'range',show_on_card:true,show_on_view:true,icon:'fa-solid fa-arrow-up',suffix:' Level'};
  if(kind==='number_badge') tpl={key:'value',label:'Number Badge',type:'number',min:0,show_on_upload:true,show_on_card:true,show_on_view:true,icon:'fa-solid fa-hashtag'};
  ACS_FIELDS.push(tpl); ACS_OPEN_INDEX=ACS_FIELDS.length-1;
  if(kind==='platform'){var h=document.getElementById('acsIconField'); if(h && !h.value) h.value='platform';}
  if(kind==='rank'){var h2=document.getElementById('acsIconField'); if(h2 && !h2.value) h2.value='rank';}
  acsRender();
}
function acsRemove(i){acsRead(false); var f=ACS_FIELDS[i]||{}; if(!confirm('Delete attribute \"'+(f.label||f.key||'')+'\"?')) return; ACS_FIELDS.splice(i,1); ACS_OPEN_INDEX=null; acsRender();}
function acsMove(i,d){acsRead(false); var ni=i+d; if(ni<0||ni>=ACS_FIELDS.length) return; var tmp=ACS_FIELDS[i]; ACS_FIELDS[i]=ACS_FIELDS[ni]; ACS_FIELDS[ni]=tmp; ACS_OPEN_INDEX=ni; acsRender();}
function acsLoadPreset(name){if(!ACS_PRESETS[name]) return; ACS_FIELDS=JSON.parse(JSON.stringify(ACS_PRESETS[name])); ACS_OPEN_INDEX=null; if(name==='cod'){var t=document.getElementById('acsTitleField'); if(t)t.value='main_title'; var h=document.getElementById('acsIconField'); if(h)h.value='platform';} acsRender();}
function acsToggleJson(){acsRead(false); var w=document.getElementById('acsJsonWrap'); if(!w)return; w.style.display=(w.style.display==='none'||!w.style.display)?'block':'none'; acsSyncJson();}
function saveAccountsCfg(gameId) {
  acsRead(false);
  var schema=acsBuildSchema();
  var jsonWrap=document.getElementById('acsJsonWrap');
  if(jsonWrap && jsonWrap.style.display!=='none'){
    try { schema=JSON.parse(document.getElementById('acsJson').value); } catch(e){ toast('Schema JSON is invalid','error'); return; }
  }
  var body=new FormData();
  body.append('page_title',       (document.getElementById('acTitle')||{}).value || '');
  body.append('page_description', (document.getElementById('acDesc')||{}).value || '');
  body.append('show_type_cards',  document.getElementById('acTypeCards') && document.getElementById('acTypeCards').checked ? '1' : '0');
  body.append('schema_enabled',   schema.enabled ? '1' : '0');
  body.append('schema_json',      JSON.stringify(schema));
  fetch('/admin-area/games/'+gameId+'/accounts-config',{method:'POST',body:body})
    .then(function(r){return r.text().then(function(txt){var data=null;try{data=JSON.parse(txt);}catch(e){} if(!r.ok || (data && data.success===false)){throw new Error((data && data.error)?data.error:(txt||'bad response'));} return data||{success:true};});})
    .then(function(){ toast('Accounts Builder saved!','success'); try { history.replaceState(null, '', window.location.pathname + '?accounts_saved=1'); } catch(e) {} })
    .catch(function(err){ toast('Save failed: '+err.message,'error'); });
}
document.addEventListener('DOMContentLoaded', function(){ acsRender(); });
</script>
