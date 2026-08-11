<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<?php
require_once __DIR__ . '/_seller_rank.php';
$seller_id   = (int)($seller_data['id']         ?? 0);
$username    = htmlspecialchars($seller_data['username']   ?? '', ENT_QUOTES);
$email       = htmlspecialchars($seller_data['email']      ?? '', ENT_QUOTES);
$discord     = htmlspecialchars($seller_data['discord']    ?? '', ENT_QUOTES);
$discord_id  = htmlspecialchars($seller_data['discord_id'] ?? '', ENT_QUOTES);
$seller_sales = seller_total_sales(is_array($seller_data ?? null) ? $seller_data : []);
$seller_rank  = seller_resolved_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales);
$fee_pct      = (float)($seller_rank['fee_percent'] ?? seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales));
$earn_rate    = round(100 - $fee_pct, 2);
$balance_eur = number_format((int)($seller_data['balance'] ?? 0) / 100, 2);
$is_banned   = (int)($seller_data['is_banned'] ?? 0) === 1;
$onboarding  = strtolower(trim((string)($seller_data['onboarding_status'] ?? 'pending')));
$is_approved = ($onboarding === 'approved');
$is_active   = (int)($seller_data['is_active'] ?? 0) === 1 && !$is_banned;

$description = (string)($seller_data['description'] ?? '');
$languagesRaw = $seller_data['languages'] ?? [];
if (is_string($languagesRaw)) {
  $decoded = json_decode($languagesRaw, true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    $languagesRaw = $decoded;
  } else {
    $languagesRaw = array_values(array_filter(array_map('trim', explode(',', $languagesRaw))));
  }
}
if (!is_array($languagesRaw)) {
  $languagesRaw = [];
}
$languages = array_values(array_filter(array_map('strval', $languagesRaw)));

if (!function_exists('util_load_languages_select')) {
  function util_load_languages_select($select = [], $separator = '|')
  {
      $languages = [
          'en' => 'English',
          'de' => 'Deutsch',
          'fr' => 'Français',
          'es' => 'Español',
          'pt' => 'Português',
          'it' => 'Italiano',
          'nl' => 'Nederlands',
          'pl' => 'Polski',
          'ru' => 'Русский',
          'jp' => '日本語',
          'zh' => '中文',
          'sv' => 'Svenska',
          'no' => 'Norsk',
          'da' => 'Dansk',
          'fi' => 'Suomi',
          'el' => 'Ελληνικά',
          'hu' => 'Magyar',
          'cs' => 'Čeština',
          'bg' => 'Български',
          'ro' => 'Română',
          'tr' => 'Türkçe',
          'hr' => 'Hrvatski',
          'ar' => 'العربية',
          'fili' => 'Filipino',
      ];

      $flagMap = [
          'el' => 'gr',
          'cs' => 'cz',
          'zh' => 'ch',
      ];

      $flagUrlBase = ASSET_URL . '/core/main/img/flags/';
      $flagDiskBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/public/assets/core/main/img/flags/';

      if (!is_array($select)) {
          $select = array_filter(array_map('trim', explode($separator, (string) ($select ?? ''))));
      }

      $html = '';

      foreach ($languages as $code => $label) {
          $fileCode = $flagMap[$code] ?? $code;

          $flagUrl = '';
          if (is_file($flagDiskBase . $fileCode . '.webp')) {
              $flagUrl = $flagUrlBase . $fileCode . '.webp';
          } elseif (is_file($flagDiskBase . $fileCode . '.png')) {
              $flagUrl = $flagUrlBase . $fileCode . '.png';
          }

          $selected = in_array($code, $select, true) ? ' selected=""' : '';
          $dataFlag = $flagUrl ? ' data-flag="' . htmlspecialchars($flagUrl, ENT_QUOTES, 'UTF-8') . '"' : '';

          $html .= '<option value="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"' . $selected . $dataFlag . '>'
              . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
              . '</option>';
      }

      return $html;
  }
}
?>

<?php $spageActiveTab = 'profile'; ?>
<?php include __DIR__ . '/_shared.php'; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div id="accountSidebarNav"></div>
    <div class="card s-card seller-profile-overview-sticky mb-3 mb-lg-5">
      <div class="card-header"><h4 class="card-header-title">Overview</h4></div>
      <div class="card-body">

        <div class="s-section-title">Account</div>
        <div class="s-item">
          <div class="s-item-icon"><i class="fa-solid fa-hashtag"></i></div>
          <div><div class="s-item-key">Seller ID</div><div class="s-item-val">#<?= $seller_id ?></div></div>
        </div>
        <div class="s-item">
          <div class="s-item-icon"><i class="fa-duotone fa-wallet"></i></div>
          <div><div class="s-item-key">Balance</div><div class="s-item-val"><?= $balance_eur ?> EUR</div></div>
        </div>

        <div class="s-item">
          <div class="s-item-icon"><i class="<?= htmlspecialchars($seller_rank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?>"></i></div>
          <div><div class="s-item-key">Rank</div><div class="s-item-val" style="color:<?= htmlspecialchars($seller_rank['color'] ?? '#94a3b8', ENT_QUOTES) ?>"><?= htmlspecialchars($seller_rank['label'] ?? 'Beginner', ENT_QUOTES) ?></div></div>
        </div>
        <div class="s-item">
          <div class="s-item-icon"><i class="fa-solid fa-percent"></i></div>
          <div><div class="s-item-key">Fee Benefit</div><div class="s-item-val"><?= number_format($fee_pct, 1) ?>% fee · <?= (int)$seller_sales ?> sales</div></div>
        </div>

        <div class="s-divider"></div>
        <div class="s-section-title">Contact</div>
        <div class="s-item">
          <div class="s-item-icon"><i class="fa-duotone fa-envelope"></i></div>
          <div style="min-width:0;flex:1;"><div class="s-item-key">Email</div><div class="s-item-val" style="font-size:.76rem;font-family:monospace;word-break:break-all;"><?= $email ?: '—' ?></div></div>
        </div>
        <div class="s-item" style="margin-bottom:0;">
          <div class="s-item-icon"><i class="fa-brands fa-discord"></i></div>
          <div style="flex:1;min-width:0;"><div class="s-item-key">Discord</div><div class="s-item-val"><?= $discord ?: '—' ?></div></div>
          <?php if ($discord): ?>
            <span class="s-badge s-badge-ok">Linked</span>
          <?php else: ?>
            <span class="s-badge s-badge-warn">Not linked</span>
          <?php endif; ?>
        </div>

        <div class="s-divider"></div>
        <div class="s-section-title">Status</div>
        <div style="margin-bottom:16px;">
          <?php if ($is_banned): ?>
            <span class="s-badge s-badge-red"><i class="fa-duotone fa-ban me-1"></i>Banned</span>
          <?php elseif ($is_approved && $is_active): ?>
            <span class="s-badge s-badge-ok"><i class="fa-duotone fa-badge-check me-1"></i>Active</span>
          <?php else: ?>
            <span class="s-badge s-badge-warn"><i class="fa-duotone fa-clock me-1"></i>Pending Approval</span>
          <?php endif; ?>
        </div>

        <?php if (empty($discord)): ?>
          <a href="<?= BASE_URL ?>/auth/discord/connect?seller_id=<?= $seller_id ?>" class="btn btn-primary btn-sm w-100">
            <i class="fa-brands fa-discord me-1"></i>Connect Discord
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/discord/connect?seller_id=<?= $seller_id ?>" class="btn btn-sm s-discord-reconnect w-100">
            <i class="fa-brands fa-discord me-1"></i>Reconnect Discord
          </a>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="d-grid gap-3">

      <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="seller_update_profile">
        <div class="card s-card">
          <div class="card-header"><h4 class="card-header-title">Account Settings</h4></div>
          <div class="card-body s-form">
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">Username</label>
              <div class="col-sm-9"><input type="text" class="form-control" name="username" value="<?= $username ?>" placeholder="Username" required></div>
            </div>
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">Email</label>
              <div class="col-sm-9"><input type="email" class="form-control" name="email" value="<?= $email ?>" placeholder="Email address" required></div>
            </div>
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">Discord</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="discord" value="<?= $discord ?>" placeholder="discordname or username">
                <div class="form-text" style="color:var(--s-muted);">Your Discord handle for support communication.</div>
              </div>
            </div>
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">Discord ID</label>
              <div class="col-sm-9"><input type="text" class="form-control" name="discord_id" value="<?= $discord_id ?>" placeholder="Discord user ID (numeric)"></div>
            </div>

            <div class="row mb-4">
              <label for="languagesLabel" class="col-sm-3 col-form-label">Languages</label>
              <div class="col-sm-9 tom-select-custom">
                <select class="js-select form-select" id="languagesLabel" name="languages[]" multiple autocomplete="off">
                  <?= util_load_languages_select($languages) ?>
                </select>
              </div>
            </div>

            <div class="row mb-0">
              <label for="description" class="col-sm-3 col-form-label">Description</label>
              <div class="col-sm-9">
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description"><?= htmlspecialchars($description, ENT_QUOTES) ?></textarea>
              </div>
            </div>

            <div class="row mt-4 mb-0" id="chat-settings">
              <label for="allowChatRequests" class="col-sm-3 col-form-label">Chat Requests</label>
              <div class="col-sm-9">
                <div class="form-check form-switch mb-1">
                  <input class="form-check-input" type="checkbox" role="switch" id="allowChatRequests" name="allow_chat_requests" value="1" <?= ((int)($seller_data['allow_chat_requests'] ?? 1) === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="allowChatRequests">Allow chat requests from clients</label>
                </div>
                <div class="form-text" style="color:var(--s-muted);">
                  When enabled, clients can contact you directly from account and item listing pages before purchase.
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer" style="background:transparent;border-top:1px solid var(--s-border);padding:14px 20px;">
            <button type="submit" class="btn btn-primary">
              <span class="indicator-label"><i class="fa-duotone fa-floppy-disk me-1"></i>Update Settings</span>
              <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
              <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Saved</span>
            </button>
          </div>
        </div>
      </form>

      <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="seller_change_password">
        <div class="card s-card">
          <div class="card-header"><h4 class="card-header-title">Change Password</h4></div>
          <div class="card-body s-form">
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">Current Password</label>
              <div class="col-sm-9"><input type="password" class="form-control" name="current_password" placeholder="Enter current password" required></div>
            </div>
            <div class="row mb-4">
              <label class="col-sm-3 col-form-label">New Password</label>
              <div class="col-sm-9"><input type="password" class="form-control" id="spNewPw" name="new_password" placeholder="Min. 8 characters" required minlength="8"></div>
            </div>
            <div class="row mb-0">
              <label class="col-sm-3 col-form-label">Confirm Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control" id="spConfirmPw" name="confirm_password" placeholder="Repeat new password" required minlength="8">
                <div id="spMatchHint" style="font-size:.78rem;margin-top:4px;"></div>
              </div>
            </div>
          </div>
          <div class="card-footer" style="background:transparent;border-top:1px solid var(--s-border);padding:14px 20px;">
            <button type="submit" class="btn btn-primary">
              <span class="indicator-label"><i class="fa-duotone fa-lock me-1"></i>Save Password</span>
              <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
              <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Saved</span>
            </button>
          </div>
        </div>
      </form>

    </div>
    <div id="stickyBlockEndPoint"></div>
  </div>
</div>

<?= $this->start('scripts') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.css">
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
(function(){
  var n = document.getElementById('spNewPw');
  var c = document.getElementById('spConfirmPw');
  var h = document.getElementById('spMatchHint');
  function check(){
    if(!c||!h) return;
    if(!c.value){ h.textContent=''; return; }
    var ok = n && c.value === n.value;
    h.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
    h.style.color = ok ? 'var(--s-green)' : 'var(--s-red)';
  }
  if(n) n.addEventListener('input', check);
  if(c) c.addEventListener('input', check);

  if (window.TomSelect) {
    document.querySelectorAll('.js-select').forEach(function(el){
      if (!el.tomselect) {
        new TomSelect(el, {
          plugins: ['remove_button'],
          maxItems: null,
          closeAfterSelect: false,
          hideSelected: true,
          placeholder: 'Select languages...',
          render: {
            option: function(data, escape) {
              var flag = data.flag ? '<img src="' + escape(data.flag) + '" alt="" class="ts-flag">' : '';
              return '<div class="ts-option-row">' + flag + '<span>' + escape(data.text) + '</span></div>';
            },
            item: function(data, escape) {
              var flag = data.flag ? '<img src="' + escape(data.flag) + '" alt="" class="ts-flag">' : '';
              return '<div class="ts-item-row">' + flag + '<span>' + escape(data.text) + '</span></div>';
            }
          }
        });
      }
    });
  }
})();
</script>
<style>
.tom-select-custom .ts-wrapper {
  min-height: 48px;
}
@media (min-width: 992px) {
  .seller-profile-overview-sticky {
    position: sticky;
    top: 82px;
    z-index: 2;
  }
}

.s-discord-reconnect {
  background: linear-gradient(135deg, rgba(88,101,242,.18), rgba(124,92,255,.18)) !important;
  border: 1px solid rgba(124,92,255,.28) !important;
  color: #f3f4ff !important;
  border-radius: 12px !important;
  font-weight: 600;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.04), 0 10px 24px rgba(10,12,18,.16);
  transition: .2s ease;
}
.s-discord-reconnect:hover {
  background: linear-gradient(135deg, rgba(88,101,242,.26), rgba(124,92,255,.24)) !important;
  border-color: rgba(124,92,255,.42) !important;
  color: #fff !important;
  transform: translateY(-1px);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.06), 0 14px 28px rgba(10,12,18,.22);
}
.s-discord-reconnect:focus,
.s-discord-reconnect:active {
  color: #fff !important;
  box-shadow: 0 0 0 2px rgba(124,92,255,.16) !important;
}

.tom-select-custom .ts-control,
.tom-select-custom .ts-wrapper.single .ts-control,
.tom-select-custom .ts-wrapper.multi .ts-control {
  background: rgba(255,255,255,.03) !important;
  border: 1px solid #2a2f3a !important;
  color: #fff !important;
  border-radius: 10px !important;
  min-height: 48px;
  padding: 10px 12px !important;
  box-shadow: none !important;
}
.tom-select-custom .ts-control input {
  color: #fff !important;
}
.tom-select-custom .ts-control > input::placeholder {
  color: var(--s-muted) !important;
}
.tom-select-custom .ts-wrapper.focus .ts-control,
.tom-select-custom .ts-control.focus {
  border-color: #7c5cff !important;
  box-shadow: 0 0 0 2px rgba(124, 92, 255, 0.15) !important;
}
.tom-select-custom .ts-dropdown {
  background: #20242c !important;
  border: 1px solid #2a2f3a !important;
  border-radius: 10px !important;
  margin-top: 6px !important;
  overflow: hidden;
}
.tom-select-custom .ts-dropdown .option,
.tom-select-custom .ts-dropdown .active,
.tom-select-custom .ts-dropdown .create {
  padding: 10px 12px !important;
  color: #cfd3dc !important;
  background: transparent;
}
.tom-select-custom .ts-dropdown .option:hover,
.tom-select-custom .ts-dropdown .active {
  background: #2a2f3a !important;
  color: #fff !important;
}
.tom-select-custom .ts-dropdown .no-results,
.tom-select-custom .ts-dropdown .optgroup-header {
  color: var(--s-muted) !important;
  padding: 10px 12px !important;
}
.tom-select-custom .item {
  background: rgba(124, 92, 255, 0.15) !important;
  border: 1px solid rgba(124, 92, 255, 0.24) !important;
  color: #b9aaff !important;
  border-radius: 6px !important;
  padding: 3px 8px !important;
  margin: 2px 6px 2px 0;
  line-height: 1.2;
}
.tom-select-custom .item .remove {
  border-left: 0;
  color: #aaa !important;
  margin-left: 6px;
}
.tom-select-custom .item .remove:hover {
  color: #fff !important;
  background: transparent !important;
}
.tom-select-custom select.form-select {
  visibility: hidden;
  position: absolute;
  pointer-events: none;
}

.tom-select-custom .ts-option-row,
.tom-select-custom .ts-item-row {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.tom-select-custom .ts-dropdown .option .ts-option-row {
  display: flex;
}
.tom-select-custom .ts-flag {
  width: 16px;
  height: 12px;
  object-fit: cover;
  border-radius: 2px;
  box-shadow: 0 0 0 1px rgba(255,255,255,.08);
  flex: 0 0 auto;
}
</style>
<?= $this->end() ?>
