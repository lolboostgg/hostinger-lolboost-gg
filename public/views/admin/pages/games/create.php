<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<style>
.gc-page { padding: 24px 0 64px; }
.gc-cols { display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start; }
@media(max-width: 1000px) { .gc-cols { grid-template-columns: 1fr; } }

/* Service select cards */
.gc-svc {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  border-radius: 10px; border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03); cursor: pointer; user-select: none; transition: all .15s;
}
.gc-svc.on { background: rgba(99,102,241,.1); border-color: rgba(129,140,248,.3); }
.gc-svc.on.svc-accounts { background: rgba(34,197,94,.08);   border-color: rgba(34,197,94,.28); }
.gc-svc.on.svc-items    { background: rgba(245,158,11,.08);  border-color: rgba(245,158,11,.28); }
.gc-svc.on.svc-coaching { background: rgba(59,130,246,.08);  border-color: rgba(96,165,250,.28); }
.gc-svc.on.svc-egirl    { background: rgba(244,63,94,.08);   border-color: rgba(244,63,94,.28); }
.gc-svc:hover           { border-color: rgba(255,255,255,.15); }
.gc-svc__ico { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.svc-boosting .gc-svc__ico { background:rgba(99,102,241,.18); color:#a5b4fc; }
.svc-accounts .gc-svc__ico { background:rgba(34,197,94,.16);  color:#86efac; }
.svc-items    .gc-svc__ico { background:rgba(245,158,11,.16); color:#fcd34d; }
.svc-coaching .gc-svc__ico { background:rgba(59,130,246,.16); color:#93c5fd; }
.svc-egirl    .gc-svc__ico { background:rgba(244,63,94,.14);  color:#fda4af; }
.gc-svc__body { flex:1; min-width:0; }
.gc-svc__name { font-size:13px; font-weight:800; color:#fff; }
.gc-svc__desc { font-size:11px; color:rgba(255,255,255,.38); margin-top:1px; }
.gc-svc input[type=checkbox] { display:none; }
.gc-check { width:18px; height:18px; border-radius:5px; border:2px solid rgba(255,255,255,.2); flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.gc-svc.on .gc-check { background:#6366f1; border-color:#6366f1; }
.gc-check::after { content:''; width:10px; height:6px; border-left:2px solid #fff; border-bottom:2px solid #fff; transform:rotate(-45deg) translate(1px,-1px); opacity:0; transition:opacity .12s; }
.gc-svc.on .gc-check::after { opacity:1; }



/* Clear service enabled, disabled states */
.gc-svc{position:relative; padding-right:118px!important; opacity:.58; filter:grayscale(.75);}
.gc-svc:not(.on){background:rgba(15,23,42,.52)!important;border-color:rgba(148,163,184,.16)!important;}
.gc-svc:not(.on) .gc-svc__ico{background:rgba(148,163,184,.10)!important;color:#64748b!important;}
.gc-svc:not(.on) .gc-svc__name{color:#94a3b8!important;}
.gc-svc:not(.on) .gc-svc__desc,.gc-svc:not(.on) .gc-svc__route{color:rgba(148,163,184,.42)!important;}
.gc-svc.on{opacity:1;filter:none;outline:1px solid rgba(34,197,94,.35);}
.gc-svc.on:before{content:"";position:absolute;left:0;top:10px;bottom:10px;width:4px;border-radius:0 999px 999px 0;background:#22c55e;box-shadow:0 0 18px rgba(34,197,94,.75);}
.gc-svc:not(.on):before{content:"";position:absolute;left:0;top:10px;bottom:10px;width:4px;border-radius:0 999px 999px 0;background:#475569;}
.gc-svc__state{position:absolute;right:14px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;gap:7px;height:28px;padding:0 10px;border-radius:999px;font-size:10px;font-weight:950;letter-spacing:.075em;text-transform:uppercase;border:1px solid transparent;}
.gc-svc__state .dot{width:7px;height:7px;border-radius:999px;}
.gc-svc .state-on{display:none;background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.32);color:#86efac;}
.gc-svc .state-on .dot{background:#22c55e;box-shadow:0 0 12px rgba(34,197,94,.9);}
.gc-svc .state-off{display:inline-flex;background:rgba(100,116,139,.14);border-color:rgba(148,163,184,.18);color:#94a3b8;}
.gc-svc .state-off .dot{background:#64748b;}
.gc-svc.on .state-on{display:inline-flex;}
.gc-svc.on .state-off{display:none;}
.gc-svc:hover .gc-svc__state{transform:translateY(-50%) scale(1.02);}
@media(max-width:700px){.gc-svc{padding-right:16px!important;padding-bottom:48px!important}.gc-svc__state{left:70px;right:auto;top:auto;bottom:12px;transform:none}.gc-svc:hover .gc-svc__state{transform:none}}

/* Preview card sidebar */
.gc-preview {
  border-radius: 12px; padding: 0; position: sticky; top: 24px;
}
.gc-preview__title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.35); margin-bottom:14px; display:flex; align-items:center; gap:6px; }
.gc-preview__icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; color:#fff; margin-bottom:10px; transition:background .3s; }
.gc-preview__name { font-size:18px; font-weight:800; color:#fff; margin-bottom:2px; }
.gc-preview__slug { font-size:12px; color:rgba(255,255,255,.38); font-family:monospace; }
.gc-preview__routes { margin-top:12px; display:flex; flex-direction:column; gap:4px; }
.gc-preview__route { font-size:11px; font-family:monospace; color:rgba(255,255,255,.5); background:rgba(255,255,255,.04); border-radius:5px; padding:3px 8px; }

.gc-icon-drop { border:1px dashed rgba(139,92,246,.45); background:rgba(139,92,246,.08); border-radius:12px; padding:14px; cursor:pointer; transition:.15s ease; display:flex; align-items:center; gap:12px; min-height:74px; }
.gc-icon-drop.dragover { border-color:#a78bfa; background:rgba(139,92,246,.18); transform:translateY(-1px); }
.gc-icon-drop__thumb { width:46px; height:46px; border-radius:12px; background:rgba(0,0,0,.22); border:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; overflow:hidden; flex:0 0 46px; color:#fff; font-weight:800; font-size:11px; }
.gc-icon-drop__thumb img { width:34px; height:34px; object-fit:contain; display:block; }
.gc-icon-drop__main { min-width:0; }
.gc-icon-drop__title { color:#fff; font-weight:800; font-size:13px; line-height:1.25; }
.gc-icon-drop__text { color:rgba(255,255,255,.55); font-size:12px; margin-top:2px; }
.gc-icon-drop__file { color:#c4b5fd; font-size:11px; margin-top:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
.gc-hidden-file { position:absolute; left:-9999px; width:1px; height:1px; opacity:0; }



/* ── V7 full visual redesign ─────────────────────────────── */
.gc-page,.ge-page{padding:22px 0 80px}.gc-page .breadcrumb,.ge-page .breadcrumb{background:transparent;margin-bottom:14px}.gc-page .card,.ge-page .card{border:1px solid rgba(255,255,255,.075)!important;border-radius:18px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.022))!important;box-shadow:0 18px 45px rgba(0,0,0,.20);overflow:hidden}.gc-page .card-header,.ge-page .card-header{padding:17px 20px!important;border-bottom:1px solid rgba(255,255,255,.07)!important;background:rgba(8,12,18,.28)!important}.gc-page .card-header-title,.ge-page .card-header-title{font-size:14px;font-weight:900;letter-spacing:.01em}.gc-page .card-body,.ge-page .card-body{padding:20px!important}.gc-page .form-control,.gc-page .form-select,.gc-page .input-group-text,.ge-page .form-control,.ge-page .form-select,.ge-page .input-group-text{height:42px;border-radius:12px!important;background:rgba(7,11,17,.48)!important;border:1px solid rgba(255,255,255,.10)!important;color:#f8fafc!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}.gc-page textarea.form-control,.ge-page textarea.form-control{height:auto}.gc-page .form-control:focus,.gc-page .form-select:focus,.ge-page .form-control:focus,.ge-page .form-select:focus{border-color:rgba(124,105,245,.65)!important;box-shadow:0 0 0 3px rgba(124,105,245,.14)!important;background:rgba(7,11,17,.62)!important}.gc-page .form-label,.ge-page .form-label{font-size:11px;text-transform:uppercase;letter-spacing:.075em;color:rgba(255,255,255,.52)!important}.gc-page .form-text,.ge-page .form-text{font-size:11px;color:rgba(255,255,255,.32)!important}.gc-page code,.ge-page code{color:#c4b5fd;background:rgba(124,105,245,.10);border:1px solid rgba(124,105,245,.12);padding:2px 6px;border-radius:7px}.gc-hero-v7{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 18px;padding:22px 24px;border-radius:20px;border:1px solid rgba(124,105,245,.18);background:radial-gradient(circle at 16% 0%,rgba(124,105,245,.25),transparent 34%),linear-gradient(135deg,rgba(124,105,245,.14),rgba(20,184,166,.06)),rgba(255,255,255,.025);box-shadow:0 22px 60px rgba(0,0,0,.24)}.gc-hero-v7__left{display:flex;align-items:center;gap:15px}.gc-hero-v7__badge{width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(124,105,245,.35),rgba(20,184,166,.18));border:1px solid rgba(255,255,255,.10);color:#fff;font-size:22px;box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}.gc-hero-v7__kicker{font-size:11px;font-weight:900;letter-spacing:.13em;text-transform:uppercase;color:#a5b4fc;margin-bottom:4px}.gc-hero-v7__title{margin:0;font-size:25px;font-weight:950;color:#fff;letter-spacing:-.03em}.gc-hero-v7__sub{margin-top:4px;color:rgba(255,255,255,.52);font-size:13px}.gc-hero-v7__actions{display:flex;gap:9px;flex-wrap:wrap}.gc-page .btn,.ge-page .btn{border-radius:12px!important;font-weight:850!important}.gc-icon-drop,.ge-icon-drop{border-radius:16px!important;padding:16px!important;background:linear-gradient(135deg,rgba(124,105,245,.13),rgba(20,184,166,.045))!important;border-color:rgba(124,105,245,.35)!important}.gc-icon-drop:hover,.ge-icon-drop:hover{transform:translateY(-1px);border-color:rgba(167,139,250,.65)!important;background:linear-gradient(135deg,rgba(124,105,245,.20),rgba(20,184,166,.07))!important}.gc-icon-drop__thumb,.ge-icon-drop__thumb{width:54px!important;height:54px!important;border-radius:15px!important;background:rgba(8,12,18,.55)!important}.gc-icon-drop__thumb img,.ge-icon-drop__thumb img{width:40px!important;height:40px!important}.gc-svc,.ge-svc{border-radius:15px!important;background:rgba(8,12,18,.34)!important;border-color:rgba(255,255,255,.08)!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.025)}.gc-svc:hover,.ge-svc:hover{transform:translateY(-1px);border-color:rgba(124,105,245,.28)!important;background:rgba(124,105,245,.07)!important}.gc-svc.on,.ge-svc.on{box-shadow:0 10px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.04)}.gc-svc__ico,.ge-svc__ico{width:42px!important;height:42px!important;border-radius:13px!important}.gc-preview,.ge-stat-card{border-radius:18px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02))!important;border:1px solid rgba(255,255,255,.08)!important;box-shadow:0 18px 45px rgba(0,0,0,.18)}.gc-preview__icon{width:62px!important;height:62px!important;border-radius:18px!important}.gc-preview__name{font-size:21px!important;letter-spacing:-.02em}.ge-hero{padding:6px}.ge-hero__icon{width:62px!important;height:62px!important;border-radius:18px!important;background:linear-gradient(135deg,rgba(124,105,245,.25),rgba(20,184,166,.10))!important}.ge-hero__icon img{width:43px!important;height:43px!important}.ge-hero__name{font-size:25px!important;font-weight:950!important;letter-spacing:-.03em}.ge-svcs{gap:12px!important}.ge-page table tbody tr{border-radius:14px;transition:.14s}.ge-page table tbody tr:hover{background:rgba(124,105,245,.06)}.ge-page table td{padding-top:14px!important;padding-bottom:14px!important}.ge-tw{border-radius:14px!important;background:rgba(8,12,18,.36)!important}.ge-tag,.ge-preset{border-radius:999px!important}.gc-sticky-save,.ge-sticky-save{position:sticky;bottom:16px;z-index:8;margin-top:18px;padding:12px;border-radius:16px;background:rgba(20,23,28,.86);border:1px solid rgba(124,105,245,.24);backdrop-filter:blur(12px);box-shadow:0 18px 45px rgba(0,0,0,.28);display:flex;justify-content:flex-end;gap:10px}
@media(max-width:900px){.gc-hero-v7{align-items:flex-start;flex-direction:column}.gc-hero-v7__title{font-size:21px}.ge-svcs{grid-template-columns:1fr!important}}

</style>

<div class="gc-page">
  <!-- Breadcrumb -->
  <nav class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin-area/games">Games</a></li>
      <li class="breadcrumb-item active">Add Game</li>
    </ol>
  </nav>

  <?php if (isset($_GET['error'])): ?>
  <div class="alert alert-soft-danger mb-4">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    A game with this slug already exists. Please choose a different slug.
  </div>
  <?php endif ?>



  <div class="gc-hero-v7">
    <div class="gc-hero-v7__left">
      <div class="gc-hero-v7__badge"><i class="fa-solid fa-gamepad"></i></div>
      <div>
        <div class="gc-hero-v7__kicker">Games & Services</div>
        <h1 class="gc-hero-v7__title">Create a new game</h1>
        <div class="gc-hero-v7__sub">Upload icon, choose colors, enable services and create the first clean game setup.</div>
      </div>
    </div>
    <div class="gc-hero-v7__actions">
      <a href="/admin-area/games" class="btn btn-ghost-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
      <button type="submit" form="createForm" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Create Game</button>
    </div>
  </div>

  <form method="POST" action="/admin-area/games/create" id="createForm" enctype="multipart/form-data">
  <div class="gc-cols">

    <!-- LEFT -->
    <div>

      <!-- Game Info -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-header-title"><i class="fa-solid fa-gamepad me-2"></i>Game Info</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Game Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="gcName" class="form-control"
                     placeholder="e.g. Call of Duty" required oninput="gcAutoSlug(this.value)">
              <div class="form-text">Full display name shown to users.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">URL Slug <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text text-muted">/</span>
                <input type="text" name="slug" id="gcSlug" class="form-control"
                       placeholder="call-of-duty" required pattern="[a-z0-9\-]+"
                       oninput="gcUpdatePreview()">
              </div>
              <div class="form-text">Lowercase, hyphens only.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Nav Label</label>
              <input type="text" name="short_code" id="gcCode" class="form-control"
                     placeholder="COD" maxlength="6" oninput="gcUpdatePreview()">
              <div class="form-text">Short label in nav (e.g. LoL, VAL).</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small">Game Icon</label>
              <input type="file" name="game_icon" id="gcIconFile" class="gc-hidden-file" accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml" onchange="gcPreviewIconFile(this)">
              <div class="gc-icon-drop" id="gcIconDrop" tabindex="0" role="button" aria-label="Upload game icon">
                <div class="gc-icon-drop__thumb" id="gcIconDropThumb">GAME</div>
                <div class="gc-icon-drop__main">
                  <div class="gc-icon-drop__title">Click, drag an icon here, or press Ctrl V</div>
                  <div class="gc-icon-drop__text">PNG, JPG, WebP or SVG, max 2 MB.</div>
                  <div class="gc-icon-drop__file" id="gcIconFileName">No file selected.</div>
                </div>
              </div>
              <div class="form-text">Upload goes to <code>/public_html/public/assets/website/images/icons/</code>. Recommended: PNG/WebP, square icon.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Brand Colors -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-header-title"><i class="fa-solid fa-palette me-2"></i>Brand Colors</h5>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold small">Primary Color</label>
              <div class="d-flex align-items-center gap-2">
                <div style="position:relative">
                  <div id="gcSwatchP" onclick="document.getElementById('gcColorP').click()"
                       style="width:38px;height:38px;border-radius:9px;background:#8b5cf6;border:2px solid rgba(255,255,255,.15);cursor:pointer;transition:transform .12s;flex-shrink:0;"
                       onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'"></div>
                  <input type="color" name="color_primary" id="gcColorP" value="#8b5cf6"
                         oninput="gcSyncColor('gcColorP','gcSwatchP','gcColorPHex')"
                         style="opacity:0;position:absolute;width:0;height:0;">
                </div>
                <input type="text" id="gcColorPHex" class="form-control" value="#8b5cf6"
                       placeholder="#8b5cf6"
                       oninput="document.getElementById('gcColorP').value=this.value;gcSyncColor('gcColorP','gcSwatchP','gcColorPHex')">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold small">Accent Color</label>
              <div class="d-flex align-items-center gap-2">
                <div style="position:relative">
                  <div id="gcSwatchA" onclick="document.getElementById('gcColorA').click()"
                       style="width:38px;height:38px;border-radius:9px;background:#a78bfa;border:2px solid rgba(255,255,255,.15);cursor:pointer;transition:transform .12s;flex-shrink:0;"
                       onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'"></div>
                  <input type="color" name="color_accent" id="gcColorA" value="#a78bfa"
                         oninput="gcSyncColor('gcColorA','gcSwatchA','gcColorAHex')"
                         style="opacity:0;position:absolute;width:0;height:0;">
                </div>
                <input type="text" id="gcColorAHex" class="form-control" value="#a78bfa"
                       placeholder="#a78bfa"
                       oninput="document.getElementById('gcColorA').value=this.value;gcSyncColor('gcColorA','gcSwatchA','gcColorAHex')">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold small">Preview</label>
              <div id="gcColorPreviewBar"
                   style="height:38px;border-radius:9px;background:linear-gradient(90deg,#8b5cf6,#a78bfa);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;"
                   id="gcColorPreviewBadge">GAME</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Services -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-header-title"><i class="fa-solid fa-sliders me-2"></i>Activate Services</h5>
          <span class="text-muted small"><span style="color:#86efac;font-weight:800;">Enabled</span> oder <span style="color:#94a3b8;font-weight:800;">Disabled</span></span>
          <p class="card-header-subtitle mt-1 mb-0 small">Each activated service creates routes and DB entries automatically.</p>
        </div>
        <div class="card-body">
          <?php
          $svcMeta = [
            'boosting' => ['icon'=>'fa-rocket',    'label'=>'Boosting',     'desc'=>'Rank, Win, Placement boost forms'],
            'accounts' => ['icon'=>'fa-user-circle','label'=>'Account Shop','desc'=>'Account packages (premium + ranked)'],
            'items'    => ['icon'=>'fa-gift',       'label'=>'Items',        'desc'=>'Marketplace items, skins, passes'],
            'coaching' => ['icon'=>'fa-headset',    'label'=>'Coaching',     'desc'=>'1-on-1 coaching sessions'],
            'egirl'    => ['icon'=>'fa-users',      'label'=>'Companion',    'desc'=>'Gaming companion / egirl service'],
          ];
          $defaultOn = ['boosting','accounts'];
          ?>
          <div class="row g-2">
            <?php foreach ($service_types as $type):
              $m = $svcMeta[$type] ?? ['icon'=>'fa-bolt','label'=>ucfirst($type),'desc'=>''];
              $on = in_array($type, $defaultOn);
            ?>
            <div class="col-md-6">
              <div class="gc-svc svc-<?= $type ?> <?= $on ? 'on' : '' ?>"
                   onclick="gcToggleSvc(this, '<?= $type ?>')">
                <span class="gc-svc__ico"><i class="fa-solid <?= $m['icon'] ?>"></i></span>
                <div class="gc-svc__body">
                  <div class="gc-svc__name"><?= $m['label'] ?></div>
                  <div class="gc-svc__desc"><?= $m['desc'] ?></div>
                </div>
                <span class="gc-svc__state state-on"><span class="dot"></span>Enabled</span>
                <span class="gc-svc__state state-off"><span class="dot"></span>Disabled</span>
                <span class="gc-check"></span>
                <input type="checkbox" name="services[]" value="<?= $type ?>" <?= $on ? 'checked' : '' ?>>
              </div>
            </div>
            <?php endforeach ?>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="/admin-area/games" class="btn btn-ghost-secondary">
          <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-check me-1"></i> Create Game
        </button>
      </div>

    </div><!-- /left -->

    <!-- RIGHT: live preview -->
    <div>
      <div class="card gc-preview"><div class="card-body">
        <div class="gc-preview__title"><i class="fa-solid fa-eye"></i> Live Preview</div>
        <div class="gc-preview__icon" id="gcPrevIcon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)">GAME</div>
        <div class="gc-preview__name" id="gcPrevName">New Game</div>
        <div class="gc-preview__slug" id="gcPrevSlug">/your-slug</div>
        <div class="gc-preview__routes" id="gcPrevRoutes"></div>
      </div>
    </div>

  </div><!-- /gc-cols -->
  </form>
</div>

<script>

function gcSetIconFile(file) {
  if (!file) return;
  var allowed = ['image/png','image/jpeg','image/webp','image/svg+xml'];
  var extOk = /\.(png|jpe?g|webp|svg)$/i.test(file.name || '');
  if ((allowed.indexOf(file.type) === -1 && !extOk) || file.size > 2 * 1024 * 1024) {
    alert('Please upload a PNG, JPG, WebP or SVG file up to 2 MB.');
    return;
  }

  var input = document.getElementById('gcIconFile');
  if (input && window.DataTransfer) {
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
  }

  gcPreviewIcon(file);
}

function gcPreviewIcon(file) {
  var prev = document.getElementById('gcPrevIcon');
  var thumb = document.getElementById('gcIconDropThumb');
  var fileName = document.getElementById('gcIconFileName');
  if (!file || !prev) return;
  if (fileName) fileName.textContent = file.name || 'Selected icon';

  var reader = new FileReader();
  reader.onload = function(e) {
    var img = '<img src="' + e.target.result + '" alt="" style="width:34px;height:34px;object-fit:contain;display:block;">';
    prev.style.background = 'rgba(255,255,255,.06)';
    prev.innerHTML = img;
    if (thumb) thumb.innerHTML = img;
  };
  reader.readAsDataURL(file);
}

function gcPreviewIconFile(input) {
  var file = input && input.files ? input.files[0] : null;
  gcPreviewIcon(file);
}
function gcAutoSlug(name) {
  var slug = name.toLowerCase().replace(/[^a-z0-9\s\-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');
  document.getElementById('gcSlug').value = slug;
  var code = name.replace(/[^a-zA-Z0-9]/g,'').toUpperCase().slice(0,4);
  if (!document.getElementById('gcCode').value) document.getElementById('gcCode').value = code;
  gcUpdatePreview(); gcUpdateColors();
}

function gcSyncColor(inputId, swatchId, hexId) {
  var val = document.getElementById(inputId).value;
  document.getElementById(swatchId).style.background = val;
  document.getElementById(hexId).value = val;
  gcUpdateColors();
}
function gcUpdateColors() {
  var c1 = document.getElementById('gcColorP').value;
  var c2 = document.getElementById('gcColorA').value;
  var bar  = document.getElementById('gcColorPreviewBar');
  var icon = document.getElementById('gcPrevIcon');
  if (bar)  bar.style.background  = 'linear-gradient(90deg,' + c1 + ',' + c2 + ')';
  if (icon) icon.style.background = 'linear-gradient(135deg,' + c1 + ',' + c2 + ')';
}

function gcUpdatePreview() {
  var slug    = document.getElementById('gcSlug').value || 'your-slug';
  var name    = document.getElementById('gcName').value || 'New Game';
  var code    = document.getElementById('gcCode').value || slug.toUpperCase().slice(0,4);
  document.getElementById('gcPrevName').textContent = name;
  document.getElementById('gcPrevSlug').textContent = '/' + slug;
  var iconInput = document.getElementById('gcIconFile');
  if (!iconInput || !iconInput.files || !iconInput.files.length) {
    document.getElementById('gcPrevIcon').textContent = code.toUpperCase().slice(0,4);
    var thumb = document.getElementById('gcIconDropThumb');
    if (thumb) thumb.textContent = code.toUpperCase().slice(0,4);
  }
  var checked = Array.from(document.querySelectorAll('.gc-svc.on input[type=checkbox]')).map(function(i){return i.value;});
  var routes = ['/' + slug];
  if (checked.includes('boosting')) routes.push('/' + slug + '/{boost}');
  if (checked.includes('accounts')) routes.push('/' + slug + '/accounts');
  if (checked.includes('items'))    routes.push('/' + slug + '/items');
  if (checked.includes('coaching')) routes.push('/' + slug + '/coaching');
  document.getElementById('gcPrevRoutes').innerHTML = routes.map(function(r){
    return '<div class="gc-preview__route">' + r + '</div>';
  }).join('');
}

function gcToggleSvc(el, type) {
  el.classList.toggle('on');
  var cb = el.querySelector('input[type=checkbox]');
  cb.checked = el.classList.contains('on');
  gcUpdatePreview();
}

function gcInitIconDrop() {
  var drop = document.getElementById('gcIconDrop');
  var input = document.getElementById('gcIconFile');
  if (!drop || !input) return;

  drop.addEventListener('click', function(){ input.click(); });
  drop.addEventListener('keydown', function(e){
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      input.click();
    }
  });
  ['dragenter','dragover'].forEach(function(evt){
    drop.addEventListener(evt, function(e){
      e.preventDefault();
      e.stopPropagation();
      drop.classList.add('dragover');
    });
  });
  ['dragleave','drop'].forEach(function(evt){
    drop.addEventListener(evt, function(e){
      e.preventDefault();
      e.stopPropagation();
      drop.classList.remove('dragover');
    });
  });
  drop.addEventListener('drop', function(e){
    var file = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
    gcSetIconFile(file);
  });

  document.addEventListener('paste', function(e){
    var active = document.activeElement;
    var tag = active && active.tagName ? active.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || (active && active.isContentEditable)) return;
    var items = e.clipboardData && e.clipboardData.items ? e.clipboardData.items : [];
    for (var i = 0; i < items.length; i++) {
      if (items[i].type && items[i].type.indexOf('image/') === 0) {
        var file = items[i].getAsFile();
        if (file) {
          gcSetIconFile(file);
          e.preventDefault();
          break;
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', function(){ gcInitIconDrop(); gcUpdatePreview(); gcUpdateColors(); });
</script>
