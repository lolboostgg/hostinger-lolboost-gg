<?php
$st = $setup ?? [];
$seller = $st['seller'] ?? ($seller_data ?? []);
$percent = (int)($st['percent'] ?? 0);
$steps = $st['steps'] ?? [];
$missing_keys = $st['missing'] ?? [];
$langs_selected = $st['languages'] ?? [];

$map = [
  'discord' => 1,
  'profile_picture' => 2,
  'banner' => 2,
  'languages' => 3,
  'description' => 3,
  'chat_requests' => 3,
  'payout' => 4,
];
$missing_steps = [];
foreach ($missing_keys as $k) if (isset($map[$k])) $missing_steps[] = $map[$k];
$missing_steps = array_values(array_unique($missing_steps));
sort($missing_steps);
$step = isset($_GET['step']) ? max(1, min(4, (int)$_GET['step'])) : (count($missing_steps) ? $missing_steps[0] : 1);

if (!function_exists('seller_setup_languages_select')) {
  function seller_setup_languages_select($select = []) {
    $languages = [
      'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español', 'pt' => 'Português',
      'it' => 'Italiano', 'nl' => 'Nederlands', 'pl' => 'Polski', 'ru' => 'Русский', 'jp' => '日本語',
      'zh' => '中文', 'sv' => 'Svenska', 'no' => 'Norsk', 'da' => 'Dansk', 'fi' => 'Suomi',
      'el' => 'Ελληνικά', 'hu' => 'Magyar', 'cs' => 'Čeština', 'bg' => 'Български', 'ro' => 'Română',
      'tr' => 'Türkçe', 'hr' => 'Hrvatski', 'ar' => 'العربية', 'fili' => 'Filipino',
    ];
    if (!is_array($select)) $select = [];
    $html = '';
    foreach ($languages as $code => $label) {
      $sel = in_array($code, $select, true) ? ' selected' : '';
      $html .= '<option value="' . htmlspecialchars($code, ENT_QUOTES) . '"' . $sel . '>' . htmlspecialchars($label, ENT_QUOTES) . '</option>';
    }
    return $html;
  }
}

$default_icon = 'https://lolboost.gg/public/uploads/icons/default.png';
$icon_url = trim((string)($seller['icon'] ?? $default_icon));
$icon_done = !empty($steps['profile_picture']['done']);
$banner_url = trim((string)($seller['banner'] ?? ''));
$banner_done = !empty($steps['banner']['done']);
$banner_preview = $banner_done ? $banner_url : (ASSET_URL . '/core/main/img/banners/leona.jpeg');
$discord_done = !empty($steps['discord']['done']);
$lang_done = !empty($steps['languages']['done']);
$desc_done = !empty($steps['description']['done']);
$chat_done = !empty($steps['chat_requests']['done']);
$payout_done = !empty($steps['payout']['done']);
$allow_chat = (int)($seller['allow_chat_requests'] ?? 1) === 1;
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Seller Setup | LoLBoost.gg'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  .seller-setup-wrap{max-width:1100px;margin:0 auto}.seller-setup-progress{width:58px;height:58px;border-radius:999px;display:flex;align-items:center;justify-content:center;border:2px solid rgba(115,103,240,.45);background:rgba(115,103,240,.10);font-weight:800;color:#fff}.seller-setup-card{border-radius:18px;overflow:visible}.seller-setup-card .card-body{overflow:visible}.seller-setup-step{padding:12px 14px;border-radius:13px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)}.seller-setup-step+.seller-setup-step{margin-top:10px}.seller-setup-dot{width:10px;height:10px;border-radius:99px;display:inline-block;margin-right:10px}.seller-setup-dot.ok{background:#22c55e}.seller-setup-dot.no{background:#ef4444}.seller-setup-media{width:100%;height:160px;border-radius:14px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.08);overflow:hidden}.seller-setup-media img{width:100%;height:100%;object-fit:cover;display:block}.seller-setup-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-size:.75rem;font-weight:800;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.78)}.seller-setup-pill.ok{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25);color:#86efac}.seller-setup-pill.no{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.25);color:#fca5a5}.seller-setup-choice{display:flex;align-items:center;justify-content:space-between;gap:16px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);border-radius:14px;padding:14px}.ts-control,.ts-wrapper.single .ts-control,.ts-wrapper.multi .ts-control{background:rgba(0,0,0,.25)!important;border-color:rgba(255,255,255,.10)!important;color:#e9e9ef!important;min-height:44px}.ts-control .item{background:rgba(115,103,240,.2)!important;border:1px solid rgba(115,103,240,.38)!important;color:#fff!important}.ts-control input{color:#e9e9ef!important}.ts-dropdown{background:rgba(18,18,22,.98)!important;border-color:rgba(255,255,255,.10)!important;color:#e9e9ef!important;z-index:9999!important}.ts-dropdown .option{color:#e9e9ef!important}.ts-dropdown .active{background:rgba(115,103,240,.25)!important}
</style>
<?= $this->end() ?>

<div class="seller-setup-wrap">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="page-header-title mb-1">Seller Setup</h1>
      <div class="text-muted">Complete your seller profile before you start listing accounts and items.</div>
    </div>
    <div class="seller-setup-progress"><?= $percent ?>%</div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card seller-setup-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title mb-0">Setup Checklist</h4>
          <span class="badge bg-primary"><?= count($missing_keys) ?> missing</span>
        </div>
        <div class="card-body">
          <?php foreach ($steps as $key => $item): ?>
            <?php $done = !empty($item['done']); ?>
            <div class="seller-setup-step">
              <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center"><span class="seller-setup-dot <?= $done ? 'ok' : 'no' ?>"></span><span class="fw-semibold"><?= htmlspecialchars($item['label'] ?? $key) ?></span></div>
                <span class="seller-setup-pill <?= $done ? 'ok' : 'no' ?>"><?= $done ? 'Done' : 'Missing' ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="alert alert-soft-primary mt-3 mb-0">This setup is only needed once. You can update everything later in your seller settings.</div>
    </div>

    <div class="col-lg-8">
      <div class="card seller-setup-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title mb-0">
            <?php $titles = [1 => 'Step 1: Connect Discord', 2 => 'Step 2: Profile Picture & Banner', 3 => 'Step 3: Public Profile', 4 => 'Step 4: Payout Method']; echo htmlspecialchars($titles[$step] ?? 'Seller Setup'); ?>
          </h4>
          <span class="badge bg-primary"><?= (int)$step ?>/4</span>
        </div>
        <div class="card-body">
          <?php if ($step === 1): ?>
            <p class="text-muted mb-4">Connect Discord so admins can contact you and so notifications work correctly.</p>
            <?php if ($discord_done): ?>
              <div class="alert alert-soft-success mb-3"><i class="fa-brands fa-discord me-2"></i>Discord is connected.</div>
              <div class="d-flex justify-content-end"><a class="btn btn-primary" href="?step=2">Next</a></div>
            <?php else: ?>
              <a class="btn btn-primary" href="<?= BASE_URL ?>/auth/discord/connect?seller_id=<?= (int)($seller['id'] ?? 0) ?>"><i class="fa-brands fa-discord me-2"></i>Connect Discord</a>
            <?php endif; ?>

          <?php elseif ($step === 2): ?>
            <p class="text-muted mb-4">Upload a profile picture and a banner. Both are shown on your public seller profile.</p>
            <div class="row g-4">
              <div class="col-md-6">
                <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between"><span>Profile picture</span><span class="seller-setup-pill <?= $icon_done ? 'ok' : 'no' ?>"><?= $icon_done ? 'Done' : 'Missing' ?></span></div>
                <div class="seller-setup-media mb-3" style="height:140px"><img src="<?= htmlspecialchars($icon_url ?: $default_icon) ?>" alt="Profile picture" style="object-fit:contain;background:rgba(0,0,0,.22)"></div>
                <input id="iconFile" class="form-control" accept="image/*" type="file">
                <button id="uploadIconBtn" type="button" class="btn btn-primary mt-3 w-100">Upload profile picture</button>
              </div>
              <div class="col-md-6">
                <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between"><span>Banner</span><span class="seller-setup-pill <?= $banner_done ? 'ok' : 'no' ?>"><?= $banner_done ? 'Done' : 'Missing' ?></span></div>
                <div class="seller-setup-media mb-3"><img src="<?= htmlspecialchars($banner_preview) ?>" alt="Banner"></div>
                <input id="bannerFile" class="form-control" accept="image/*" type="file">
                <button id="uploadBannerBtn" type="button" class="btn btn-primary mt-3 w-100">Upload banner</button>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="?step=1">Back</a><a class="btn btn-primary <?= ($icon_done && $banner_done) ? '' : 'disabled' ?>" href="?step=3">Next</a></div>

          <?php elseif ($step === 3): ?>
            <p class="text-muted mb-4">Set your public seller info and decide if clients can send direct chat requests.</p>
            <label class="form-label fw-semibold">Languages <span class="text-danger">*</span></label>
            <select id="setupLanguages" multiple class="form-select mb-4"><?= seller_setup_languages_select($langs_selected) ?></select>

            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea id="setupDescription" class="form-control mb-4" rows="5" placeholder="Write a short introduction about your accounts, delivery style and experience."><?= htmlspecialchars((string)($seller['description'] ?? '')) ?></textarea>

            <div class="seller-setup-choice">
              <div>
                <div class="fw-semibold">Chat Requests</div>
                <div class="text-muted small">Allow clients to contact you before buying or for account questions.</div>
              </div>
              <div class="form-check form-switch m-0">
                <input id="setupChatRequests" class="form-check-input" type="checkbox" <?= $allow_chat ? 'checked' : '' ?>>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="?step=2">Back</a><button id="step3Next" type="button" class="btn btn-primary">Save & Next</button></div>

          <?php else: ?>
            <p class="text-muted mb-4">Add at least one payout method. You can change this later in Payout Settings.</p>
            <?php if ($payout_done): ?><div class="alert alert-soft-success">A payout method is already saved.</div><?php endif; ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Method</label>
                <select id="payoutMethod" class="form-select"><option value="crypto">Crypto</option><option value="bank_transfer">Bank transfer</option></select>
              </div>
              <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch m-0"><input id="payoutDefault" class="form-check-input" type="checkbox" checked><label class="form-check-label ms-2" for="payoutDefault">Set as default</label></div></div>
            </div>
            <div id="cryptoFields" class="row g-3 mt-1">
              <div class="col-md-4"><label class="form-label fw-semibold">Coin</label><input class="form-control" data-payout-field="coin" value="USDC"></div>
              <div class="col-md-4"><label class="form-label fw-semibold">Network</label><input class="form-control" data-payout-field="network" value="Solana"></div>
              <div class="col-md-4"><label class="form-label fw-semibold">Wallet Name</label><input class="form-control" data-payout-field="name" placeholder="Main wallet"></div>
              <div class="col-12"><label class="form-label fw-semibold">Wallet Address <span class="text-danger">*</span></label><input class="form-control" data-payout-field="wallet" data-required="1" placeholder="Wallet address"></div>
            </div>
            <div id="bankFields" class="row g-3 mt-1 d-none">
              <div class="col-md-6"><label class="form-label fw-semibold">Beneficiary <span class="text-danger">*</span></label><input class="form-control" data-payout-field="beneficiary" data-required="1" placeholder="Full name"></div>
              <div class="col-md-6"><label class="form-label fw-semibold">Bank Name</label><input class="form-control" data-payout-field="bank_name" placeholder="Bank name"></div>
              <div class="col-md-6"><label class="form-label fw-semibold">IBAN <span class="text-danger">*</span></label><input class="form-control" data-payout-field="iban" data-required="1" placeholder="IBAN"></div>
              <div class="col-md-6"><label class="form-label fw-semibold">BIC / SWIFT</label><input class="form-control" data-payout-field="bic" placeholder="BIC or SWIFT"></div>
            </div>
            <div id="payoutFeedback" class="alert mt-3 d-none"></div>
            <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="?step=3">Back</a><button id="savePayoutBtn" type="button" class="btn btn-primary">Save & Finish</button></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
(function(){
  const tsEl = document.getElementById('setupLanguages');
  if (tsEl) new TomSelect(tsEl, {plugins:['remove_button'], persist:false, create:false});

  async function postAjax(fd){
    const res = await fetch('<?= AJAX_URL ?>', {method:'POST', body:fd, credentials:'same-origin'});
    const txt = await res.text();
    try { return JSON.parse(txt || '{}'); } catch(e) { throw new Error(txt || 'Invalid server response'); }
  }

  async function upload(action, fieldName, inputId){
    const file = document.getElementById(inputId)?.files?.[0];
    if (!file) { alert('Please select a file first.'); return; }
    const fd = new FormData();
    fd.append('action', action);
    fd.append(fieldName, file);
    try { await postAjax(fd); window.location.href='?step=2'; } catch(e) { console.error(e); window.location.reload(); }
  }
  document.getElementById('uploadIconBtn')?.addEventListener('click', () => upload('seller_upload_profile_picture','image_url','iconFile'));
  document.getElementById('uploadBannerBtn')?.addEventListener('click', () => upload('seller_upload_banner','banner_image','bannerFile'));

  document.getElementById('step3Next')?.addEventListener('click', async () => {
    const langSel = document.getElementById('setupLanguages');
    const languages = langSel ? Array.from(langSel.selectedOptions).map(o => o.value) : [];
    const desc = document.getElementById('setupDescription')?.value || '';
    const chat = document.getElementById('setupChatRequests')?.checked ? '1' : '0';
    if (!languages.length) { alert('Please select at least one language.'); return; }
    if (!desc.trim()) { alert('Please enter a description.'); return; }
    try {
      let fd = new FormData();
      fd.append('action','seller_setup_quick_update'); fd.append('field','languages');
      languages.forEach(v => fd.append('languages[]', v));
      let res = await postAjax(fd); if (res.success === false) throw new Error('languages');
      fd = new FormData(); fd.append('action','seller_setup_quick_update'); fd.append('field','description'); fd.append('value', desc);
      res = await postAjax(fd); if (res.success === false) throw new Error('description');
      fd = new FormData(); fd.append('action','seller_setup_quick_update'); fd.append('field','allow_chat_requests'); fd.append('value', chat);
      res = await postAjax(fd); if (res.success === false) throw new Error('chat');
      window.location.href='?step=4';
    } catch(e) { console.error(e); window.location.reload(); }
  });

  const payoutMethod = document.getElementById('payoutMethod');
  function syncPayoutFields(){
    const crypto = payoutMethod?.value !== 'bank_transfer';
    document.getElementById('cryptoFields')?.classList.toggle('d-none', !crypto);
    document.getElementById('bankFields')?.classList.toggle('d-none', crypto);
  }
  payoutMethod?.addEventListener('change', syncPayoutFields);
  syncPayoutFields();

  function feedback(message, type){
    const el = document.getElementById('payoutFeedback');
    if (!el) return;
    el.className = 'alert alert-' + (type || 'danger') + ' mt-3';
    el.textContent = message || '';
    el.classList.toggle('d-none', !message);
  }

  document.getElementById('savePayoutBtn')?.addEventListener('click', async () => {
    const method = payoutMethod?.value || 'crypto';
    const scope = method === 'bank_transfer' ? document.getElementById('bankFields') : document.getElementById('cryptoFields');
    const required = Array.from(scope?.querySelectorAll('[data-required="1"]') || []);
    const missing = required.find(el => !String(el.value || '').trim());
    if (missing) { missing.focus(); feedback('Please fill in all required payout fields.'); return; }
    const btn = document.getElementById('savePayoutBtn');
    const fd = new FormData();
    fd.append('action','seller_save_payout_method');
    fd.append('method', method);
    fd.append('is_default', document.getElementById('payoutDefault')?.checked ? '1' : '0');
    Array.from(scope?.querySelectorAll('[data-payout-field]') || []).forEach(el => fd.append(el.dataset.payoutField, el.value || ''));
    if (method === 'crypto') fd.append('address', scope?.querySelector('[data-payout-field="wallet"]')?.value || '');
    try {
      btn?.setAttribute('disabled','disabled'); feedback('');
      const res = await postAjax(fd);
      if (res.sendToast?.type === 'danger' || res.success === false) {
        feedback(res.sendToast?.message || 'Payout method could not be saved.'); btn?.removeAttribute('disabled'); return;
      }
      window.location.href='<?= BASE_URL ?>/seller-area/dashboard';
    } catch(e) { console.error(e); feedback('Connection error. Please try again.'); btn?.removeAttribute('disabled'); }
  });
})();
</script>
<?= $this->end() ?>
