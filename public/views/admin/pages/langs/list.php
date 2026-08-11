<?php
$languageFlagMap = [
    'en'  => '/public/assets/core/main/img/flags/en.png',
    'de'  => '/public/assets/core/main/img/flags/de.png',
    'fr'  => '/public/assets/core/main/img/flags/fr.webp',
    'es'  => '/public/assets/core/main/img/flags/es.png',
    'pt'  => '/public/assets/core/main/img/flags/pt.png',
    'it'  => '/public/assets/core/main/img/flags/it.png',
    'nl'  => '/public/assets/core/main/img/flags/nl.png',
    'jp'  => '/public/assets/core/main/img/flags/jp.webp',
    'zh'  => '/public/assets/core/main/img/flags/ch.png',
    'ru'  => '/public/assets/core/main/img/flags/ru.webp',
    'pl'  => '/public/assets/core/main/img/flags/pl.webp',
    'sv'  => '/public/assets/core/main/img/flags/sv.webp',
    'ro'  => '/public/assets/core/main/img/flags/ro.webp',
    'cs'  => '/public/assets/core/main/img/flags/cz.webp',
    'el'  => '/public/assets/core/main/img/flags/gr.png',
    'no'  => '/public/assets/core/main/img/flags/no.webp',
    'da'  => '/public/assets/core/main/img/flags/da.webp',
    'fi'  => '/public/assets/core/main/img/flags/fi.webp',
    'bg'  => '/public/assets/core/main/img/flags/bg.webp',
    'hu'  => '/public/assets/core/main/img/flags/hu.webp',
    'hr'  => '/public/assets/core/main/img/flags/hr.webp',
    'ar'  => '/public/assets/core/main/img/flags/ar.png',
    'tr'  => '/public/assets/core/main/img/flags/tr.png',
];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$langs = is_array($langs ?? null) ? $langs : [];
$availableLanguages = function_exists('util_language_list') ? util_language_list() : [];
$languageFlag = static function (string $key) use ($languageFlagMap): string {
    $key = strtolower(trim($key));
    return $languageFlagMap[$key] ?? '/public/assets/core/main/img/flags/en.png';
};
$enabledCount = count($langs);
$availableCount = is_array($availableLanguages) ? count($availableLanguages) : 0;
$missingCount = max(0, $availableCount - $enabledCount);
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Manage Languages - Admin Area | LoLBoost.gg', 'h1' => 'Manage Languages', 'description' => 'Manage the Languages for the platform.']]) ?>

<?= $this->start('styles') ?>
<style>
.lang-page{--lang-panel:#25282a;--lang-panel-2:#202326;--lang-border:rgba(255,255,255,.075);--lang-muted:rgba(255,255,255,.45);--lang-soft:rgba(255,255,255,.055);}
.lang-page .card{background:transparent!important;border:0!important;box-shadow:none!important;}
.lang-page .card::before{display:none!important;}
.lang-hero{border:1px solid var(--lang-border);background:linear-gradient(135deg,rgba(139,92,246,.10),rgba(56,189,248,.045)),var(--lang-panel);border-radius:24px;padding:22px 24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;overflow:hidden;position:relative;}
.lang-hero::after{content:'';position:absolute;right:-70px;top:-90px;width:240px;height:240px;border-radius:999px;background:radial-gradient(circle,rgba(139,92,246,.18),transparent 68%);pointer-events:none;}
.lang-hero-left{display:flex;align-items:center;gap:15px;position:relative;z-index:1;}
.lang-hero-icon{width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,rgba(139,92,246,.28),rgba(56,189,248,.14));border:1px solid rgba(139,92,246,.28);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.1rem;box-shadow:0 14px 34px rgba(0,0,0,.16);}
.lang-hero-title{font-size:1.28rem;font-weight:950;color:#fff;margin:0;letter-spacing:-.025em;}
.lang-hero-sub{font-size:.84rem;color:var(--lang-muted);margin-top:3px;}
.lang-hero-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;position:relative;z-index:1;}
.lang-stat{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.085);background:rgba(255,255,255,.04);border-radius:999px;padding:7px 12px;font-size:.76rem;font-weight:900;color:rgba(255,255,255,.7);}
.lang-stat strong{color:#fff;font-weight:950;}
.lang-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.055);color:rgba(255,255,255,.76);border-radius:12px;padding:9px 14px;font-size:.82rem;font-weight:900;text-decoration:none;cursor:pointer;transition:transform .12s,background .12s,border-color .12s,color .12s;}
.lang-btn:hover{transform:translateY(-1px);background:rgba(255,255,255,.10);color:#fff;}
.lang-btn-primary{background:linear-gradient(135deg,#7c3aed,#4f46e5);border-color:rgba(139,92,246,.52);color:#fff;box-shadow:0 14px 28px rgba(79,70,229,.20);}
.lang-btn-primary:hover{background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;}
.lang-btn-danger:hover{background:rgba(251,113,133,.14);border-color:rgba(251,113,133,.28);color:#fb7185;}
.lang-panel{border:1px solid var(--lang-border);background:var(--lang-panel);border-radius:24px;overflow:hidden;}
.lang-panel-top{padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.018);}
.lang-panel-title{display:flex;align-items:center;gap:9px;font-size:.98rem;font-weight:950;color:#fff;}
.lang-tools{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.lang-search{position:relative;width:min(340px,72vw);}
.lang-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:.82rem;color:rgba(255,255,255,.34);pointer-events:none;}
.lang-search input{width:100%;height:40px;border:1px solid rgba(255,255,255,.105);background:rgba(255,255,255,.055);border-radius:13px;color:#fff;outline:0;padding:0 13px 0 36px;font-size:.86rem;font-weight:700;}
.lang-search input:focus{border-color:rgba(139,92,246,.55);box-shadow:0 0 0 3px rgba(139,92,246,.11);}
.lang-search input::placeholder{color:rgba(255,255,255,.28);}
.lang-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(255px,1fr));gap:12px;padding:18px;}
.lang-card{position:relative;border:1px solid rgba(255,255,255,.075);background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.023));border-radius:18px;padding:14px;display:flex;align-items:center;gap:12px;transition:transform .13s,border-color .13s,background .13s;}
.lang-card:hover{transform:translateY(-2px);border-color:rgba(139,92,246,.35);background:linear-gradient(180deg,rgba(139,92,246,.08),rgba(255,255,255,.025));}
.lang-flag{width:42px;height:42px;border-radius:999px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#111827;border:1px solid rgba(255,255,255,.10);box-shadow:0 8px 20px rgba(0,0,0,.20);}
.lang-flag img{width:100%;height:100%;object-fit:cover;display:block;}
.lang-info{min-width:0;flex:1;}
.lang-name{font-size:.95rem;font-weight:950;color:#fff;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.lang-key{display:inline-flex;align-items:center;margin-top:4px;padding:3px 8px;border-radius:999px;background:rgba(255,255,255,.055);color:rgba(255,255,255,.43);font-size:.68rem;font-weight:950;text-transform:uppercase;letter-spacing:.08em;}
.lang-actions{display:flex;align-items:center;gap:7px;flex-shrink:0;}
.lang-icon-btn{width:34px;height:34px;border-radius:11px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.045);color:rgba(255,255,255,.62);display:inline-flex;align-items:center;justify-content:center;text-decoration:none;cursor:pointer;transition:background .12s,border-color .12s,color .12s;}
.lang-icon-btn:hover{background:rgba(139,92,246,.16);border-color:rgba(139,92,246,.34);color:#c4b5fd;}
.lang-icon-btn.delete:hover{background:rgba(251,113,133,.13);border-color:rgba(251,113,133,.28);color:#fb7185;}
.lang-empty{display:none;text-align:center;padding:50px 24px;color:rgba(255,255,255,.38);}
.lang-empty.is-visible{display:block;}
.lang-empty i{font-size:2.4rem;opacity:.28;display:block;margin-bottom:12px;}
.lang-modal .modal-content{border-radius:24px;background:#202326;border:1px solid rgba(255,255,255,.08);box-shadow:0 28px 90px rgba(0,0,0,.65);overflow:hidden;}
.lang-modal .modal-header{border-bottom:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.025);padding:18px 20px;}
.lang-modal .modal-title{font-weight:950;color:#fff;font-size:1rem;}
.lang-modal .modal-body{padding:18px 20px;color:rgba(255,255,255,.72);}
.lang-modal .btn-close{filter:invert(1);opacity:.65;}
.lang-add-search{position:relative;margin-bottom:12px;}
.lang-add-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);}
.lang-add-search input{width:100%;height:42px;border-radius:13px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.055);color:#fff;outline:0;padding:0 13px 0 37px;font-weight:750;}
.lang-option-list{max-height:365px;overflow:auto;border:1px solid rgba(255,255,255,.07);border-radius:16px;background:rgba(255,255,255,.025);padding:6px;}
.lang-option{width:100%;border:0;background:transparent;border-radius:12px;color:rgba(255,255,255,.76);padding:9px 10px;display:flex;align-items:center;gap:10px;text-align:left;cursor:pointer;font-weight:850;}
.lang-option:hover,.lang-option.is-selected{background:rgba(139,92,246,.16);color:#fff;}
.lang-option .lang-flag{width:32px;height:32px;}
.lang-option-code{margin-left:auto;color:rgba(255,255,255,.35);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;}
.lang-selected-pill{display:none;align-items:center;gap:8px;margin-top:12px;padding:10px 12px;border-radius:14px;background:rgba(139,92,246,.10);border:1px solid rgba(139,92,246,.24);font-size:.84rem;font-weight:900;color:#fff;}
.lang-selected-pill.is-visible{display:flex;}
.lang-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px;}
.lang-confirm-card{border-radius:18px;background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.20);padding:14px;color:rgba(255,255,255,.75);}
@media(max-width:767px){.lang-hero{padding:18px}.lang-grid{grid-template-columns:1fr;padding:12px}.lang-panel-top{align-items:stretch}.lang-tools,.lang-search{width:100%;}.lang-card{padding:12px}.lang-actions{gap:5px}.lang-icon-btn{width:32px;height:32px}}
</style>
<?= $this->end() ?>

<div class="lang-page">
  <div class="lang-hero">
    <div class="lang-hero-left">
      <div class="lang-hero-icon"><i class="fa-duotone fa-language"></i></div>
      <div>
        <h1 class="lang-hero-title">Manage Languages</h1>
        <div class="lang-hero-sub">Manage platform translations with the same flags used in the website header.</div>
      </div>
    </div>
    <div class="lang-hero-actions">
      <span class="lang-stat"><strong><?= (int)$enabledCount ?></strong> enabled</span>
      <?php if ($availableCount > 0): ?><span class="lang-stat"><strong><?= (int)$missingCount ?></strong> available to add</span><?php endif; ?>
      <a href="<?= ADMN_URL ?>/sync-languages" class="lang-btn"><i class="fa-duotone fa-rotate"></i> Sync Languages</a>
      <button type="button" class="lang-btn lang-btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal"><i class="fa-duotone fa-plus"></i> Add Language</button>
    </div>
  </div>

  <?php if (isset($_GET['sync']) && $_GET['sync'] === 'ok'): ?>
    <div class="alert alert-success mb-3" style="border-radius:16px;">
      Sync done. Updated files: <?= (int)($_GET['updated'] ?? 0) ?>
    </div>
  <?php endif; ?>

  <div class="lang-panel">
    <div class="lang-panel-top">
      <div class="lang-panel-title"><i class="fa-duotone fa-globe"></i> Active Languages</div>
      <div class="lang-tools">
        <div class="lang-search">
          <i class="fa-duotone fa-search"></i>
          <input id="languageSearchInput" type="search" placeholder="Search languages..." autocomplete="off">
        </div>
      </div>
    </div>

    <div class="lang-grid" id="languageGrid">
      <?php foreach ($langs as $key => $name):
        $key = (string)$key;
        $name = (string)$name;
        $flag = $languageFlag($key);
        $search = strtolower($key . ' ' . $name);
      ?>
        <div class="lang-card" data-search="<?= $h($search) ?>">
          <a class="lang-flag" href="<?= ADMN_URL ?>/manage-language/<?= $h($key) ?>" title="<?= $h($name) ?>">
            <img src="<?= $h($flag) ?>" alt="<?= $h($name) ?> flag" loading="lazy">
          </a>
          <div class="lang-info">
            <div class="lang-name"><?= $h($name) ?></div>
            <div class="lang-key"><?= $h($key) ?></div>
          </div>
          <div class="lang-actions">
            <a href="<?= ADMN_URL ?>/manage-language/<?= $h($key) ?>" class="lang-icon-btn" title="Manage <?= $h($name) ?>"><i class="fa-duotone fa-eye"></i></a>
            <button type="button" class="lang-icon-btn delete" title="Delete <?= $h($name) ?>" data-bs-toggle="modal" data-bs-target="#deleteLanguageModal" data-lang-key="<?= $h($key) ?>" data-lang-name="<?= $h($name) ?>"><i class="fa-duotone fa-trash"></i></button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="lang-empty" id="languageEmpty">
      <i class="fa-duotone fa-magnifying-glass"></i>
      <div style="font-weight:900;color:rgba(255,255,255,.62);">No languages found</div>
      <div style="font-size:.85rem;margin-top:4px;">Try another search term.</div>
    </div>
  </div>
</div>

<div id="addLanguageModal" class="modal fade lang-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_language">
        <input type="hidden" name="language" id="selectedLanguageInput" value="">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-plus me-1"></i> Add Language</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="lang-add-search">
            <i class="fa-duotone fa-search"></i>
            <input type="search" id="addLanguageSearch" placeholder="Search available languages..." autocomplete="off">
          </div>
          <div class="lang-option-list" id="addLanguageList">
            <?php foreach ($availableLanguages as $key => $name): ?>
              <?php if (!array_key_exists($key, $langs)):
                $flag = $languageFlag((string)$key);
                $search = strtolower((string)$key . ' ' . (string)$name);
              ?>
              <button type="button" class="lang-option" data-key="<?= $h($key) ?>" data-name="<?= $h($name) ?>" data-search="<?= $h($search) ?>">
                <span class="lang-flag"><img src="<?= $h($flag) ?>" alt="<?= $h($name) ?> flag" loading="lazy"></span>
                <span><?= $h($name) ?></span>
                <span class="lang-option-code"><?= $h($key) ?></span>
              </button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="lang-selected-pill" id="selectedLanguagePill"><i class="fa-duotone fa-circle-check"></i> <span></span></div>
        </div>
        <div class="lang-modal-footer">
          <button type="button" class="lang-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="lang-btn lang-btn-primary" id="addLanguageSubmit" disabled><i class="fa-duotone fa-floppy-disk"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="deleteLanguageModal" class="modal fade lang-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="delete_language">
        <input type="hidden" name="language_key" id="delete_language_key" value="">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-trash me-1"></i> Delete Language</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="lang-confirm-card">
            Are you sure you want to delete <strong id="delete_language_name">this language</strong>?<br>
            This action cannot be undone.
          </div>
        </div>
        <div class="lang-modal-footer">
          <button type="button" class="lang-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="lang-btn lang-btn-danger"><i class="fa-duotone fa-trash"></i> Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var searchInput = document.getElementById('languageSearchInput');
  var grid = document.getElementById('languageGrid');
  var empty = document.getElementById('languageEmpty');

  function filterLanguages(){
    var q = String(searchInput && searchInput.value || '').toLowerCase().trim();
    var visible = 0;
    document.querySelectorAll('.lang-card').forEach(function(card){
      var show = !q || String(card.dataset.search || '').indexOf(q) !== -1;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (empty) empty.classList.toggle('is-visible', visible === 0);
    if (grid) grid.style.display = visible === 0 ? 'none' : 'grid';
  }
  if (searchInput) searchInput.addEventListener('input', filterLanguages);

  var selectedInput = document.getElementById('selectedLanguageInput');
  var selectedPill = document.getElementById('selectedLanguagePill');
  var addSubmit = document.getElementById('addLanguageSubmit');
  var addSearch = document.getElementById('addLanguageSearch');

  function selectLanguage(btn){
    document.querySelectorAll('.lang-option').forEach(function(opt){ opt.classList.remove('is-selected'); });
    btn.classList.add('is-selected');
    if (selectedInput) selectedInput.value = btn.dataset.key || '';
    if (selectedPill) {
      selectedPill.classList.add('is-visible');
      selectedPill.querySelector('span').textContent = (btn.dataset.name || '') + ' selected';
    }
    if (addSubmit) addSubmit.disabled = !btn.dataset.key;
  }

  document.querySelectorAll('.lang-option').forEach(function(btn){
    btn.addEventListener('click', function(){ selectLanguage(btn); });
  });

  function filterAddLanguages(){
    var q = String(addSearch && addSearch.value || '').toLowerCase().trim();
    document.querySelectorAll('.lang-option').forEach(function(opt){
      opt.style.display = (!q || String(opt.dataset.search || '').indexOf(q) !== -1) ? '' : 'none';
    });
  }
  if (addSearch) addSearch.addEventListener('input', filterAddLanguages);

  var addModal = document.getElementById('addLanguageModal');
  if (addModal) {
    addModal.addEventListener('shown.bs.modal', function(){ if (addSearch) addSearch.focus(); });
    addModal.addEventListener('hidden.bs.modal', function(){
      if (selectedInput) selectedInput.value = '';
      if (selectedPill) { selectedPill.classList.remove('is-visible'); selectedPill.querySelector('span').textContent = ''; }
      if (addSubmit) addSubmit.disabled = true;
      if (addSearch) addSearch.value = '';
      document.querySelectorAll('.lang-option').forEach(function(opt){ opt.classList.remove('is-selected'); opt.style.display = ''; });
    });
  }

  var deleteModal = document.getElementById('deleteLanguageModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function(event){
      var btn = event.relatedTarget;
      var key = btn ? (btn.getAttribute('data-lang-key') || '') : '';
      var name = btn ? (btn.getAttribute('data-lang-name') || 'this language') : 'this language';
      var keyInput = document.getElementById('delete_language_key');
      var nameEl = document.getElementById('delete_language_name');
      if (keyInput) keyInput.value = key;
      if (nameEl) nameEl.textContent = name;
    });
  }
})();
</script>
<?= $this->end() ?>
