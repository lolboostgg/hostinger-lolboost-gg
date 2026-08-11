<?php
$translations = is_array($data['translations'] ?? null) ? $data['translations'] : [];
$masterKeys = is_array($masterKeys ?? null) ? $masterKeys : [];
$allKeys = array_values(array_unique(array_merge($masterKeys, array_keys($translations))));
$totalStrings = count($allKeys);
$translatedCount = 0;
foreach ($allKeys as $__key) {
    if (trim((string)($translations[$__key] ?? '')) !== '') $translatedCount++;
}
$missingCount = max(0, $totalStrings - $translatedCount);
$progress = $totalStrings > 0 ? round(($translatedCount / $totalStrings) * 100) : 0;
$lang = (string)($lang ?? 'en');
$langName = (string)($data['name'] ?? strtoupper($lang));
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$languageFlagMap = [
    'en' => '/public/assets/core/main/img/flags/en.png',
    'de' => '/public/assets/core/main/img/flags/de.png',
    'fr' => '/public/assets/core/main/img/flags/fr.webp',
    'es' => '/public/assets/core/main/img/flags/es.png',
    'pt' => '/public/assets/core/main/img/flags/pt.png',
    'it' => '/public/assets/core/main/img/flags/it.png',
    'nl' => '/public/assets/core/main/img/flags/nl.png',
    'jp' => '/public/assets/core/main/img/flags/jp.webp',
    'ja' => '/public/assets/core/main/img/flags/jp.webp',
    'zh' => '/public/assets/core/main/img/flags/ch.png',
    'cn' => '/public/assets/core/main/img/flags/ch.png',
    'ru' => '/public/assets/core/main/img/flags/ru.webp',
    'pl' => '/public/assets/core/main/img/flags/pl.webp',
    'sv' => '/public/assets/core/main/img/flags/sv.webp',
    'ro' => '/public/assets/core/main/img/flags/ro.webp',
    'cs' => '/public/assets/core/main/img/flags/cz.webp',
    'cz' => '/public/assets/core/main/img/flags/cz.webp',
    'el' => '/public/assets/core/main/img/flags/gr.png',
    'gr' => '/public/assets/core/main/img/flags/gr.png',
    'no' => '/public/assets/core/main/img/flags/no.webp',
    'da' => '/public/assets/core/main/img/flags/da.webp',
    'fi' => '/public/assets/core/main/img/flags/fi.webp',
    'bg' => '/public/assets/core/main/img/flags/bg.webp',
    'hu' => '/public/assets/core/main/img/flags/hu.webp',
    'hr' => '/public/assets/core/main/img/flags/hr.webp',
    'ar' => '/public/assets/core/main/img/flags/ar.png',
    'tr' => '/public/assets/core/main/img/flags/tr.png',
];
$flagSrc = $languageFlagMap[strtolower($lang)] ?? $languageFlagMap['en'];
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Manage Translations - Admin Area | LoLBoost.gg', 'h1' => 'Manage Translations', 'description' => 'Manage translations for the platform.']]) ?>

<?= $this->start('styles') ?>
<style>
.lt-page{--lt-panel:#25282a;--lt-panel-2:#202326;--lt-border:rgba(255,255,255,.075);--lt-muted:rgba(255,255,255,.48);--lt-soft:rgba(255,255,255,.055);}
.lt-page .card{background:var(--lt-panel)!important;border:1px solid var(--lt-border)!important;border-radius:22px!important;box-shadow:none!important;}
.lt-page .card::before{display:none!important;}
.lt-hero{position:relative;overflow:hidden;border-radius:24px;border:1px solid rgba(255,255,255,.08);background:radial-gradient(circle at 12% 0%,rgba(99,102,241,.18),transparent 30%),linear-gradient(135deg,#25282a,#1c1f22);padding:22px 24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;}
.lt-hero-left{display:flex;align-items:center;gap:16px;min-width:0;}
.lt-flag{width:54px;height:54px;border-radius:16px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;box-shadow:0 12px 26px rgba(0,0,0,.22);}
.lt-flag img{width:100%;height:100%;object-fit:cover;display:block;}
.lt-kicker{display:inline-flex;align-items:center;gap:7px;color:#a78bfa;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;font-weight:950;margin-bottom:5px;}
.lt-title{margin:0;font-size:1.4rem;font-weight:950;color:#fff;letter-spacing:-.035em;line-height:1.1;}
.lt-sub{margin-top:5px;color:rgba(255,255,255,.46);font-size:.86rem;}
.lt-hero-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap;}
.lt-stat{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.62);font-size:.77rem;font-weight:850;}
.lt-stat strong{color:#fff;font-weight:950;}
.lt-stat--ok strong{color:#4ade80}.lt-stat--warn strong{color:#facc15}.lt-stat--progress strong{color:#93c5fd}
.lt-progress-wrap{width:min(100%,320px);height:8px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden;margin-top:10px;}
.lt-progress{height:100%;border-radius:999px;background:linear-gradient(90deg,#8b5cf6,#38bdf8);width:<?= (int)$progress ?>%;}
.lt-toolbar{border-radius:20px;border:1px solid var(--lt-border);background:#25282a;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.lt-search{position:relative;width:min(430px,100%);}
.lt-search input{width:100%;height:42px;border-radius:13px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.055);color:#fff;padding:0 14px 0 39px;font-size:.88rem;outline:none;}
.lt-search input:focus{border-color:rgba(139,92,246,.52);box-shadow:0 0 0 3px rgba(139,92,246,.12);}
.lt-search input::placeholder{color:rgba(255,255,255,.28);}
.lt-search i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.34);font-size:.82rem;}
.lt-filters{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.lt-filter{border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.045);color:rgba(255,255,255,.62);border-radius:999px;padding:8px 12px;font-size:.78rem;font-weight:900;cursor:pointer;}
.lt-filter:hover,.lt-filter.active{background:rgba(139,92,246,.18);border-color:rgba(139,92,246,.42);color:#c4b5fd;}
.lt-helper{font-size:.76rem;color:rgba(255,255,255,.36);width:100%;margin-top:2px;}
.lt-grid{display:grid;grid-template-columns:1fr;gap:12px;}
.lt-card{border-radius:18px;border:1px solid var(--lt-border);background:#25282a;overflow:hidden;transition:border-color .15s,background .15s,transform .12s;}
.lt-card:hover{border-color:rgba(139,92,246,.28);background:#272a2d;}
.lt-card.is-hidden{display:none;}
.lt-card-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 15px;border-bottom:1px solid rgba(255,255,255,.055);background:rgba(255,255,255,.018);}
.lt-card-title{display:flex;align-items:center;gap:9px;min-width:0;color:rgba(255,255,255,.62);font-size:.78rem;font-weight:950;text-transform:uppercase;letter-spacing:.08em;}
.lt-status{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:5px 9px;font-size:.7rem;font-weight:950;white-space:nowrap;}
.lt-status.done{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.24);color:#4ade80;}
.lt-status.missing{background:rgba(250,204,21,.10);border:1px solid rgba(250,204,21,.24);color:#facc15;}
.lt-form{margin:0;}
.lt-card-body{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:15px;}
.lt-field-label{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:7px;color:rgba(255,255,255,.48);font-size:.72rem;font-weight:950;text-transform:uppercase;letter-spacing:.08em;}
.lt-textarea{width:100%;min-height:112px;resize:vertical;border-radius:14px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:#fff;padding:12px 13px;font-size:.88rem;line-height:1.45;outline:none;}
.lt-textarea[readonly]{color:rgba(255,255,255,.76);background:rgba(255,255,255,.032);}
.lt-textarea:focus{border-color:rgba(139,92,246,.52);box-shadow:0 0 0 3px rgba(139,92,246,.12);}
.lt-textarea::placeholder{color:rgba(255,255,255,.24);}
.lt-card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 15px;border-top:1px solid rgba(255,255,255,.055);background:rgba(0,0,0,.08);}
.lt-left-actions,.lt-right-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.lt-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.72);border-radius:11px;min-height:34px;padding:0 12px;font-size:.78rem;font-weight:900;cursor:pointer;text-decoration:none;}
.lt-btn:hover{background:rgba(255,255,255,.1);color:#fff;}
.lt-btn-primary{border-color:rgba(139,92,246,.42);background:linear-gradient(135deg,#8b5cf6,#6d5dfc);color:#fff;box-shadow:0 10px 22px rgba(109,93,252,.18);}
.lt-btn-primary:hover{filter:brightness(1.05);color:#fff;}
.lt-save-state{font-size:.75rem;font-weight:850;color:rgba(255,255,255,.36);min-width:90px;}
.lt-save-state.ok{color:#4ade80}.lt-save-state.err{color:#fb7185}.lt-save-state.saving{color:#7dd3fc}
.lt-empty{display:none;border-radius:18px;border:1px dashed rgba(255,255,255,.12);background:rgba(255,255,255,.03);padding:38px 20px;text-align:center;color:rgba(255,255,255,.42);}
.lt-empty.is-visible{display:block;}
.lt-empty i{font-size:2rem;display:block;margin-bottom:10px;opacity:.45;}
@media(max-width:900px){.lt-card-body{grid-template-columns:1fr}.lt-card-footer{align-items:flex-start;flex-direction:column}.lt-right-actions{width:100%;justify-content:flex-end}.lt-hero{align-items:flex-start}.lt-hero-actions{width:100%;}}
</style>
<?= $this->end() ?>

<div class="lt-page">
  <div class="lt-hero">
    <div class="lt-hero-left">
      <div class="lt-flag"><img src="<?= $h($flagSrc) ?>" alt="<?= $h($langName) ?>"></div>
      <div>
        <div class="lt-kicker"><i class="fa-solid fa-language"></i> Translation editor</div>
        <h1 class="lt-title"><?= $h($langName) ?> Translations</h1>
        <div class="lt-sub">Edit strings directly on the page. Use <strong>Ctrl + Enter</strong> inside a translation box to save quickly.</div>
        <div class="lt-progress-wrap" aria-label="Translation progress"><div class="lt-progress"></div></div>
      </div>
    </div>
    <div class="lt-hero-actions">
      <span class="lt-stat"><strong><?= (int)$totalStrings ?></strong> total</span>
      <span class="lt-stat lt-stat--ok"><strong><?= (int)$translatedCount ?></strong> translated</span>
      <span class="lt-stat lt-stat--warn"><strong><?= (int)$missingCount ?></strong> missing</span>
      <span class="lt-stat lt-stat--progress"><strong><?= (int)$progress ?>%</strong> done</span>
      <a href="<?= ADMN_URL ?>/manage-languages" class="lt-btn"><i class="fa-solid fa-arrow-left"></i> All languages</a>
    </div>
  </div>

  <div class="lt-toolbar">
    <div>
      <div class="lt-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input id="ltSearch" type="search" placeholder="Search original or translation..." autocomplete="off">
      </div>
      <div class="lt-helper">Tip: leave a translation empty to mark it as missing. “Copy original” is useful for brand names, URLs and unchanged terms.</div>
    </div>
    <div class="lt-filters" role="group" aria-label="Translation filter">
      <button type="button" class="lt-filter active" data-filter="all">All</button>
      <button type="button" class="lt-filter" data-filter="missing">Missing</button>
      <button type="button" class="lt-filter" data-filter="translated">Translated</button>
    </div>
  </div>

  <div class="lt-grid" id="ltGrid">
    <?php foreach ($allKeys as $index => $string):
      $translation = (string)($translations[$string] ?? '');
      $isTranslated = trim($translation) !== '';
      $searchBlob = strtolower($string . ' ' . $translation);
      $fieldId = 'lt-translation-' . $index;
    ?>
    <article class="lt-card" data-status="<?= $isTranslated ? 'translated' : 'missing' ?>" data-search="<?= $h($searchBlob) ?>">
      <form class="lt-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_translation">
        <input type="hidden" name="original_string" value="<?= $h($string) ?>">
        <input type="hidden" name="language" value="<?= $h($lang) ?>">
        <div class="lt-card-head">
          <div class="lt-card-title"><i class="fa-solid fa-quote-left"></i> String #<?= (int)($index + 1) ?></div>
          <span class="lt-status <?= $isTranslated ? 'done' : 'missing' ?>"><i class="fa-solid <?= $isTranslated ? 'fa-check' : 'fa-triangle-exclamation' ?>"></i> <?= $isTranslated ? 'Translated' : 'Missing' ?></span>
        </div>
        <div class="lt-card-body">
          <div>
            <div class="lt-field-label"><span>English source</span></div>
            <textarea class="lt-textarea" name="string" readonly><?= $h($string) ?></textarea>
          </div>
          <div>
            <div class="lt-field-label"><span><?= $h($langName) ?> translation</span></div>
            <textarea class="lt-textarea lt-translation-input" id="<?= $h($fieldId) ?>" name="translation" placeholder="Enter <?= $h($langName) ?> translation..." required><?= $h($translation) ?></textarea>
          </div>
        </div>
        <div class="lt-card-footer">
          <div class="lt-left-actions">
            <button type="button" class="lt-btn lt-copy-original"><i class="fa-solid fa-copy"></i> Copy original</button>
            <button type="button" class="lt-btn lt-clear"><i class="fa-solid fa-eraser"></i> Clear</button>
          </div>
          <div class="lt-right-actions">
            <span class="lt-save-state"></span>
            <button type="submit" class="lt-btn lt-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
          </div>
        </div>
      </form>
    </article>
    <?php endforeach; ?>
  </div>
  <div class="lt-empty" id="ltEmpty"><i class="fa-solid fa-magnifying-glass"></i>No strings match your filter.</div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var activeFilter = 'all';
  var searchInput = document.getElementById('ltSearch');
  var empty = document.getElementById('ltEmpty');

  function parseAjaxResponse(raw){
    if (raw && typeof raw === 'object') return raw;
    raw = String(raw || '').trim();
    if (!raw) return null;
    try { return JSON.parse(raw); } catch(e) {}
    var first = raw.indexOf('{'), last = raw.lastIndexOf('}');
    if (first >= 0 && last > first) {
      try { return JSON.parse(raw.slice(first, last + 1)); } catch(e) {}
    }
    return null;
  }
  function toast(type, title, message){
    if (typeof create_toast === 'function') create_toast(type || 'primary', title || '', message || '');
  }
  function updateStatus(card){
    var input = card.querySelector('.lt-translation-input');
    var status = card.querySelector('.lt-status');
    var translated = input && input.value.trim() !== '';
    card.dataset.status = translated ? 'translated' : 'missing';
    if (status) {
      status.className = 'lt-status ' + (translated ? 'done' : 'missing');
      status.innerHTML = '<i class="fa-solid ' + (translated ? 'fa-check' : 'fa-triangle-exclamation') + '"></i> ' + (translated ? 'Translated' : 'Missing');
    }
  }
  function applyFilter(){
    var q = String(searchInput && searchInput.value || '').toLowerCase().trim();
    var visible = 0;
    document.querySelectorAll('.lt-card').forEach(function(card){
      var filterOk = activeFilter === 'all' || card.dataset.status === activeFilter;
      var searchOk = !q || String(card.dataset.search || '').indexOf(q) !== -1;
      var show = filterOk && searchOk;
      card.classList.toggle('is-hidden', !show);
      if (show) visible++;
    });
    if (empty) empty.classList.toggle('is-visible', visible === 0);
  }

  if (searchInput) searchInput.addEventListener('input', applyFilter);
  document.querySelectorAll('.lt-filter').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.lt-filter').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      activeFilter = btn.dataset.filter || 'all';
      applyFilter();
    });
  });

  document.querySelectorAll('.lt-copy-original').forEach(function(btn){
    btn.addEventListener('click', function(){
      var form = btn.closest('.lt-form');
      var source = form ? form.querySelector('textarea[name="string"]') : null;
      var target = form ? form.querySelector('textarea[name="translation"]') : null;
      if (source && target) {
        target.value = source.value;
        target.focus();
        updateStatus(form.closest('.lt-card'));
        applyFilter();
      }
    });
  });
  document.querySelectorAll('.lt-clear').forEach(function(btn){
    btn.addEventListener('click', function(){
      var form = btn.closest('.lt-form');
      var target = form ? form.querySelector('textarea[name="translation"]') : null;
      if (target) {
        target.value = '';
        target.focus();
        updateStatus(form.closest('.lt-card'));
        applyFilter();
      }
    });
  });

  document.querySelectorAll('.lt-translation-input').forEach(function(input){
    input.addEventListener('input', function(){
      var card = input.closest('.lt-card');
      updateStatus(card);
      card.dataset.search = (card.querySelector('textarea[name="string"]').value + ' ' + input.value).toLowerCase();
      applyFilter();
    });
    input.addEventListener('keydown', function(e){
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        var form = input.closest('.lt-form');
        if (form) form.requestSubmit();
      }
    });
  });

  document.querySelectorAll('.lt-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var btn = form.querySelector('button[type="submit"]');
      var state = form.querySelector('.lt-save-state');
      var card = form.closest('.lt-card');
      var fd = new FormData(form);
      if (btn) btn.disabled = true;
      if (state) { state.className = 'lt-save-state saving'; state.textContent = 'Saving...'; }
      fetch(form.action, { method:'POST', body: fd, credentials:'same-origin' })
        .then(function(r){ return r.text(); })
        .then(function(raw){
          var res = parseAjaxResponse(raw);
          var ok = !res || res.success !== false;
          if (ok) {
            if (state) { state.className = 'lt-save-state ok'; state.textContent = 'Saved'; }
            updateStatus(card);
            toast('success','Saved','Translation updated.');
            setTimeout(function(){ if (state) state.textContent = ''; }, 1600);
          } else {
            var msg = (res && (res.message || (res.sendToast && res.sendToast.message))) || 'Could not save translation.';
            if (state) { state.className = 'lt-save-state err'; state.textContent = 'Error'; }
            toast('danger','Error',msg);
          }
        })
        .catch(function(){
          if (state) { state.className = 'lt-save-state err'; state.textContent = 'Network error'; }
          toast('danger','Error','Could not reach the server.');
        })
        .finally(function(){ if (btn) btn.disabled = false; });
    });
  });
})();
</script>
<?= $this->end() ?>
