<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<?php
require_once __DIR__ . '/_seller_rank.php';
$seller_id      = (int)($seller_data['id'] ?? 0);
$username       = htmlspecialchars($seller_data['username'] ?? '', ENT_QUOTES);
$email          = htmlspecialchars($seller_data['email'] ?? '', ENT_QUOTES);
$seller_sales    = seller_total_sales(is_array($seller_data ?? null) ? $seller_data : []);
$seller_rank     = seller_resolved_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales);
$fee_pct         = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : [], $seller_sales);
$earn_rate       = 100 - $fee_pct;
$balance_eur    = number_format((int)($seller_data['balance'] ?? 0) / 100, 2, '.', '');
$avatar_icon_raw = trim((string)($seller_data['icon'] ?? ''));
$asset_base     = rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/');
$avatar_icon    = '';
if ($avatar_icon_raw !== '') {
    $avatar_icon = preg_match('~^https?://~i', $avatar_icon_raw)
        ? $avatar_icon_raw
        : $asset_base . '/' . ltrim($avatar_icon_raw, '/');
}
$avatar_initial = strtoupper(substr($seller_data['username'] ?? 'S', 0, 1));
?>

<?= $this->start('styles') ?>
<style>
/* ─── Cover / Hero ─── */
.seller-profile-cover {
  height: 160px; border-radius: 1rem 1rem 0 0;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 40%, #312e81 100%);
  position: relative;
}

/* Avatar */
.seller-profile-avatar-wrap {
  position: relative; margin-top: -44px; width: 88px; height: 88px;
}
.seller-profile-avatar {
  width: 88px; height: 88px; border-radius: 50%;
  border: 4px solid var(--bs-card-bg, #1e2028);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; font-weight: 800; color: #fff;
  box-shadow: 0 4px 20px rgba(99,102,241,.35);
  overflow: hidden; cursor: pointer; position: relative;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
}
.seller-profile-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.seller-avatar-overlay {
  position: absolute; inset: 0; border-radius: 50%;
  background: rgba(0,0,0,.55); display: flex; flex-direction: column;
  align-items: center; justify-content: center; opacity: 0; transition: opacity .2s;
  font-size: .75rem; font-weight: 700; color: #fff; gap: 2px;
}
.seller-profile-avatar:hover .seller-avatar-overlay { opacity: 1; }

/* Stat badges */
.seller-stat-badge {
  display: inline-flex; flex-direction: column; align-items: center;
  background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
  border-radius: .75rem; padding: .65rem 1.1rem; min-width: 90px;
}
[data-theme="light"] .seller-stat-badge { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.07); }
.seller-stat-badge__val { font-size: 1.15rem; font-weight: 800; line-height: 1.2; }
.seller-stat-badge__lbl { font-size: .7rem; font-weight: 600; opacity: .45; text-transform: uppercase; letter-spacing: .05em; margin-top: .15rem; }

/* Settings cards */
.seller-field-row { padding: .8rem 0; border-bottom: 1px solid rgba(255,255,255,.05); }
[data-theme="light"] .seller-field-row { border-bottom-color: rgba(0,0,0,.06); }
.seller-field-row:last-child { border-bottom: 0; }
.seller-field-label { font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.5); display: flex; align-items: center; gap: .5rem; margin-bottom: .4rem; }
[data-theme="light"] .seller-field-label { color: rgba(0,0,0,.45); }

.seller-info-row { display: flex; justify-content: space-between; align-items: center; padding: .7rem 0; border-bottom: 1px solid rgba(255,255,255,.05); }
[data-theme="light"] .seller-info-row { border-bottom-color: rgba(0,0,0,.06); }
.seller-info-row:last-child { border-bottom: 0; }
.seller-info-row__k { font-size: .82rem; color: rgba(255,255,255,.45); display: flex; align-items: center; gap: .5rem; }
[data-theme="light"] .seller-info-row__k { color: rgba(0,0,0,.45); }
.seller-info-row__v { font-size: .88rem; font-weight: 700; }

@media (max-width: 576px) {
  .seller-profile-cover { height: 120px; }
  .seller-stat-badge { min-width: 72px; padding: .5rem .7rem; }
}
</style>
<?= $this->end() ?>

<div class="seller-profile-page">

  <!-- Hero card -->
  <div class="card mb-4 border-0 overflow-hidden p-0">
    <div class="seller-profile-cover"></div>
    <div class="card-body pt-0">
      <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mt-2">
        <div class="d-flex align-items-end gap-3">
          <!-- Avatar (clickable → modal) -->
          <div class="seller-profile-avatar-wrap">
            <div class="seller-profile-avatar" data-bs-toggle="modal" data-bs-target="#uploadAvatarModal">
              <?php if (!empty($avatar_icon)): ?>
                <img src="<?= htmlspecialchars($avatar_icon, ENT_QUOTES) ?>" alt="avatar">
              <?php else: ?>
                <?= $avatar_initial ?>
              <?php endif ?>
              <div class="seller-avatar-overlay">
                <i class="fa-solid fa-camera"></i>
                <span>Change</span>
              </div>
            </div>
          </div>
          <div class="pb-1">
            <h2 class="mb-0 fw-800" style="font-size:1.25rem;"><?= $username ?></h2>
            <div class="small opacity-50 mt-1">
              <i class="fa-solid fa-store fa-fw me-1"></i> Seller Account
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2 pb-1">
          <div class="seller-stat-badge">
            <span class="seller-stat-badge__val"><?= $balance_eur ?> €</span>
            <span class="seller-stat-badge__lbl">Balance</span>
          </div>
          <div class="seller-stat-badge">
            <span class="seller-stat-badge__val text-success"><?= number_format($earn_rate, 0) ?>%</span>
            <span class="seller-stat-badge__lbl">Earn Rate</span>
          </div>
          <div class="seller-stat-badge">
            <span class="seller-stat-badge__val"><?= number_format($fee_pct, 0) ?>%</span>
            <span class="seller-stat-badge__lbl">Platform Fee</span>
          </div>
          <div class="seller-stat-badge">
            <span class="seller-stat-badge__val" style="color:<?= htmlspecialchars($seller_rank['color'] ?? '#94a3b8', ENT_QUOTES) ?>"><i class="<?= htmlspecialchars($seller_rank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?> me-1"></i><?= htmlspecialchars($seller_rank['label'] ?? 'Beginner', ENT_QUOTES) ?></span>
            <span class="seller-stat-badge__lbl">Rank · <?= (int)$seller_sales ?> Sales</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2-col layout -->
  <div class="row g-4">

    <!-- LEFT: Account info + Change password -->
    <div class="col-12 col-lg-6">

      <div class="card mb-4">
        <div class="card-header">
          <h4 class="card-header-title mb-0">
            <i class="fa-solid fa-circle-info fa-fw me-2 text-primary"></i>Account Info
          </h4>
        </div>
        <div class="card-body">
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-user fa-fw"></i> Username</span>
            <span class="seller-info-row__v"><?= $username ?></span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-envelope fa-fw"></i> Email</span>
            <span class="seller-info-row__v"><?= $email ?: '—' ?></span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-shield fa-fw"></i> Seller Rank</span>
            <span class="seller-info-row__v" style="color:<?= htmlspecialchars($seller_rank['color'] ?? '#94a3b8', ENT_QUOTES) ?>"><i class="<?= htmlspecialchars($seller_rank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?> me-1"></i><?= htmlspecialchars($seller_rank['label'] ?? 'Beginner', ENT_QUOTES) ?></span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-bag-shopping fa-fw"></i> Total Sales</span>
            <span class="seller-info-row__v"><?= (int)$seller_sales ?></span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-percent fa-fw"></i> Platform Fee</span>
            <span class="seller-info-row__v"><?= number_format($fee_pct, 1) ?>%</span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-wallet fa-fw"></i> Balance</span>
            <span class="seller-info-row__v text-success"><?= $balance_eur ?> €</span>
          </div>
          <div class="seller-info-row">
            <span class="seller-info-row__k"><i class="fa-solid fa-id-badge fa-fw"></i> Seller ID</span>
            <span class="seller-info-row__v opacity-50">#<?= $seller_id ?></span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4 class="card-header-title mb-0">
            <i class="fa-solid fa-lock fa-fw me-2 text-primary"></i>Change Password
          </h4>
        </div>
        <div class="card-body">
          <form id="sellerChangePasswordForm" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="seller_change_password">
            <div class="seller-field-row">
              <label class="seller-field-label" for="current_password"><i class="fa-solid fa-lock-keyhole fa-fw"></i> Current Password</label>
              <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password" required>
            </div>
            <div class="seller-field-row">
              <label class="seller-field-label" for="new_password"><i class="fa-solid fa-key fa-fw"></i> New Password</label>
              <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Min. 8 characters" required minlength="8">
            </div>
            <div class="seller-field-row">
              <label class="seller-field-label" for="confirm_password"><i class="fa-solid fa-key fa-fw"></i> Confirm New Password</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat new password" required minlength="8">
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Password
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>

    <!-- RIGHT: How it works -->
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header">
          <h4 class="card-header-title mb-0">
            <i class="fa-solid fa-circle-question fa-fw me-2 text-primary"></i>How It Works
          </h4>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0 small">
            <li class="mb-3 d-flex gap-2">
              <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
              <span>Add accounts via <strong>My Accounts → Add Account</strong>.</span>
            </li>
            <li class="mb-3 d-flex gap-2">
              <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
              <span>When an account sells you earn <strong class="text-success"><?= number_format($earn_rate, 1) ?>%</strong> of the sale price.</span>
            </li>
            <li class="mb-3 d-flex gap-2">
              <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
              <span>Request payouts from the <a href="<?= BASE_URL ?>/seller-area/payout" class="text-primary">Payout</a> page.</span>
            </li>
            <li class="d-flex gap-2">
              <i class="fa-solid fa-headset text-primary mt-1 flex-shrink-0"></i>
              <span>Questions? Use the admin chat on your dashboard.</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Discord -->
      <div class="card mt-4">
        <div class="card-header">
          <h4 class="card-header-title mb-0">
            <i class="fa-brands fa-discord fa-fw me-2" style="color:#5865F2;"></i>Discord
          </h4>
        </div>
        <div class="card-body">
          <?php
            $discord_tag = $seller_data['discord'] ?? null;
            $discord_id  = $seller_data['discord_id'] ?? null;
          ?>
          <?php if (!empty($discord_tag)): ?>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div style="width:38px;height:38px;border-radius:50%;background:rgba(88,101,242,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-brands fa-discord" style="color:#5865F2;font-size:1.15rem;"></i>
              </div>
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($discord_tag) ?></div>
                <?php if (!empty($discord_id)): ?>
                  <div class="small opacity-50">ID: <?= htmlspecialchars($discord_id) ?></div>
                <?php endif ?>
              </div>
              <span class="badge bg-soft-success text-success ms-auto">Connected</span>
            </div>
            <a href="<?= BASE_URL ?>/auth/discord/connect?seller_id=<?= (int)($seller_data['id'] ?? 0) ?>"
               class="btn btn-outline-secondary btn-sm">
              <i class="fa-brands fa-discord me-1"></i> Reconnect
            </a>
          <?php else: ?>
            <p class="small text-muted mb-3">Connect your Discord account to receive order notifications and team updates.</p>
            <a href="<?= BASE_URL ?>/auth/discord/connect?seller_id=<?= (int)($seller_data['id'] ?? 0) ?>"
               class="btn btn-primary">
              <i class="fa-brands fa-discord me-2"></i> Connect Discord
            </a>
          <?php endif ?>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- Avatar Upload Modal — form wraps modal (matches booster pattern) -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="seller_upload_profile_picture">
  <div class="modal fade" id="uploadAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-camera me-2"></i>Update Profile Picture</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="seller_avatar_file" class="form-label">Choose image (JPG, PNG, GIF)</label>
          <input class="form-control" accept="image/*" type="file" name="image_url" id="seller_avatar_file">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-upload me-1"></i> Upload
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<?= $this->start('scripts') ?>
<script>
(function () {
  'use strict';
  function showToast(msg, type) {
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-white ' + (type === 'success' ? 'bg-success' : 'bg-danger') + ' border-0 position-fixed bottom-0 end-0 m-3';
    el.style.zIndex = 9999;
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body fw-semibold">' + msg + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    document.body.appendChild(el);
    new bootstrap.Toast(el, { delay: 3500 }).show();
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
  }

  var pwForm = document.getElementById('sellerChangePasswordForm');
  if (pwForm) {
    pwForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var np = pwForm.querySelector('[name="new_password"]').value;
      var cp = pwForm.querySelector('[name="confirm_password"]').value;
      if (np !== cp) { showToast('Passwords do not match.', 'error'); return; }
      var btn = pwForm.querySelector('[type="submit"]');
      var orig = btn.innerHTML;
      btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
      fetch(pwForm.action, { method: 'POST', body: new FormData(pwForm) })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false; btn.innerHTML = orig;
          if (res && res.success) { showToast(res.message || 'Password updated!', 'success'); pwForm.reset(); }
          else { showToast((res && res.message) || 'Error saving password.', 'error'); }
        })
        .catch(function () { btn.disabled = false; btn.innerHTML = orig; showToast('Network error.', 'error'); });
    });
  }
})();
</script>
<?= $this->end() ?>
