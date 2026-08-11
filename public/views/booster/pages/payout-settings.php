<?php
/**
 * Booster Payout Settings — renders in the SAME layout as Profile/Personal Details
 *
 * Expected (best case) from controller:
 *  - $data['default_method'] (array|null) from booster_payment_method_get_default(BOOSTER_ID)
 * Optional (for left Overview sidebar):
 *  - $data['games'] (array), $data['lol_tier_limit'], $data['lol_division_limit'], $data['val_tier_limit'],
 *    $data['val_division_limit'], $data['solo_order_limit'], $data['duo_order_limit']
 */

// Controller SHOULD pass $data['default_method'], but we also fall back to loading it here,
// so the form stays filled after Save even if the controller forgets.
$default = $data['default_method'] ?? null;
if (empty($default) && function_exists('booster_payment_method_get_default') && defined('BOOSTER_ID')) {
    $default = booster_payment_method_get_default((int)BOOSTER_ID);
}

$type = $default['type'] ?? 'paypal';
$payload = $default['payload'] ?? [];

$label = $default['label'] ?? '';

$paypal_email = $payload['email'] ?? '';
$bank_iban = $payload['iban'] ?? '';
$bank_swift = $payload['swift'] ?? '';
$bank_name = $payload['bank_name'] ?? '';

$crypto_currency = $payload['currency'] ?? 'USDT';
$crypto_network = $payload['network'] ?? 'TRC20';
$crypto_address = $payload['address'] ?? '';

// Make the Overview sidebar safe even if controller doesn't provide the full profile dataset
$data['games'] = (isset($data['games']) && is_array($data['games'])) ? $data['games'] : [];
$data['solo_order_limit'] = $data['solo_order_limit'] ?? 0;
$data['duo_order_limit'] = $data['duo_order_limit'] ?? 0;

?>

<style>
  .avatar { position: relative; }
  .edit-icon-container {
    width: 30px; height: 30px;
    background-color: #35383a;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    position: absolute; bottom: 5px; right: 5px;
    border: 1px solid #ccc;
    cursor: pointer;
    border: none; outline: none; padding: 0;
  }
  .edit-icon-container i { color: white; }
  .edit-cover-container {
    top: 10px; right: 10px;
    background: rgba(0,0,0,.6);
    border: none; color: #fff;
    border-radius: 50%;
    padding: 8px;
    cursor: pointer;
  }

  /* --- Payout settings cards --- */
  .lb-pay-methods { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
  @media (max-width: 991.98px) { .lb-pay-methods { grid-template-columns: 1fr; } }

  .lb-pay-method {
    position: relative;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    padding: 16px;
    background: rgba(255,255,255,.02);
    cursor: pointer;
    transition: .12s ease;
    min-height: 92px;
  }
  .lb-pay-method:hover { transform: translateY(-1px); border-color: rgba(102,86,255,.35); }
  .lb-pay-method.active { border-color: rgba(102,86,255,.55); box-shadow: 0 0 0 4px rgba(102,86,255,.15); }
  .lb-pay-badge {
    position: absolute;
    top: 10px; right: 10px;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(102,86,255,.18);
    color: #bdb6ff;
    border: 1px solid rgba(102,86,255,.25);
  }
  .lb-pay-method .lb-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center; justify-content: center;
    background: rgba(255,255,255,.05);
    margin-bottom: 10px;
  }
  .lb-pay-method h5 { margin: 0; font-size: 16px; }
  .lb-pay-method p { margin: 2px 0 0; color: rgba(255,255,255,.55); font-size: 13px; }

  .lb-fieldset { display: none; }
  .lb-fieldset.active { display: block; }
</style>

<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Payout Settings - Booster Area | LoLBoost.gg'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.css">
<style>
  .avatar-upload { backdrop-filter: blur(5px); cursor: pointer; }
</style>
<?= $this->end() ?>

<!-- Profile Cover -->
<div class="profile-cover position-relative">
  <div class="profile-cover-img-wrapper">
    <img class="profile-cover-img" style="object-position: top;"
         src="<?= BOOSTER_DATA['cover'] ?? ASSET_URL . '/core/main/img/banners/leona.jpeg' ?>" alt="Banner">
  </div>

  <!-- Edit Cover Button -->
  <button class="edit-cover-container position-absolute" data-bs-toggle="modal" data-bs-target="#upload-cover-modal">
    <i class="fa-solid fa-pen"></i>
  </button>
</div>
<!-- End Profile Cover -->

<!-- Profile Header -->
<div class="text-center mb-5">
  <!-- Avatar -->
  <div class="avatar position-relative avatar-xxl avatar-circle profile-cover-avatar">
    <img class="avatar-img" src="<?= BOOSTER_DATA['icon'] ?>" alt="<?= BOOSTER_DATA['username'] ?>">

    <div class="d-none avatar-upload avatar-circle position-absolute text-center d-flex align-items-center justify-content-center w-100 h-100"
         data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
      <div>
        <i class="fa-duotone fa-camera"></i>
        <span class="d-block fs-4 fw-medium">Upload Icon</span>
      </div>
    </div>
    <button class="edit-icon-container position-absolute" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
      <i class="fa-solid fa-pen"></i>
    </button>
  </div>
  <!-- End Avatar -->

  <?php if ((BOOSTER_DATA['is_banned'] ?? 0) == 1): ?>
    <h1 class="page-header-title">
      <span class="text-danger">
        <i class="fa-solid fa-ban fs-2 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Banned"></i>
        <?= BOOSTER_DATA['username'] ?>
      </span>
    </h1>
  <?php else: ?>
    <?php if ((BOOSTER_DATA['rank_id'] ?? 0) == 3): ?>
      <h1 class="page-header-title">
        <span class="text-gradient-primary">
          <?= BOOSTER_DATA['username'] ?>
          <i class="fa-solid fa-badge-check fs-2 text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Active"></i>
        </span>
      </h1>
    <?php else: ?>
      <h1 class="page-header-title">
        <?= BOOSTER_DATA['username'] ?>
        <i class="fa-solid fa-badge-check fs-2 text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Active"></i>
      </h1>
    <?php endif; ?>
  <?php endif; ?>

  <ul class="list-inline list-px-2">
    <li class="list-inline-item">
      <i class="fa-duotone fa-fire-flame-curved me-1"></i>
      <span><?= function_exists('util_format_booster_rank') ? util_format_booster_rank(BOOSTER_DATA['rank_id'] ?? 0) : '' ?></span>
    </li>
  </ul>
</div>
<!-- End Profile Header -->

<div class="row mb-5">
  <div class="col-md-12">
    <ul class="nav nav-tabs align-items-center">
      <li class="nav-item">
        <a class="nav-link" href="/booster-area/profile">Profile</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/booster-area/personal-details">Personal Details</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="/booster-area/payout-settings">Payout Settings</a>
      </li>
    </ul>
  </div>
</div>

<!-- Content -->
<div class="row">
  <div class="col-lg-4">

    <!-- Sticky Block Start Point -->
    <div id="accountSidebarNav"></div>

    <!-- Card -->
    <div class="js-sticky-block card mb-3 mb-lg-5" data-hs-sticky-block-options='{
      "parentSelector": "#accountSidebarNav",
      "breakpoint": "lg",
      "startPoint": "#accountSidebarNav",
      "endPoint": "#stickyBlockEndPoint",
      "stickyOffsetTop": 20
    }'>
      <div class="card-header">
        <h4 class="card-header-title">Overview</h4>
      </div>

      <div class="card-body">
        <ul class="list-unstyled list-py-2 text-dark mb-0">
          <li class="pb-0"><span class="card-subtitle">Account</span></li>
          <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= defined('BOOSTER_ID') ? BOOSTER_ID : '' ?></li>
          <?php
            $lb_balance_cents = (int)(BOOSTER_DATA['balance'] ?? 0);
            $lb_frozen_cents = function_exists('booster_insurance_frozen_cents') ? booster_insurance_frozen_cents(BOOSTER_DATA) : 0;
            $lb_available_cents = function_exists('booster_available_for_payout_cents') ? booster_available_for_payout_cents(BOOSTER_DATA) : max($lb_balance_cents - $lb_frozen_cents, 0);
          ?>
          <li><i class="fa-duotone fa-wallet dropdown-item-icon"></i>
            <span class="text-muted">Available for payout:</span>
            <span class="fw-semibold"><?= function_exists('util_format_price_display') ? util_format_price_display($lb_available_cents) : number_format($lb_available_cents / 100, 2) ?> EUR</span>
          </li>
          <li><i class="fa-duotone fa-shield-check dropdown-item-icon"></i>
            <span class="text-muted">Insurance:</span>
            <span class="fw-semibold" data-bs-toggle="tooltip" data-bs-placement="right" title="Insurance is held as security and paid out when you leave the company."><?= function_exists('util_format_price_display') ? util_format_price_display($lb_frozen_cents) : number_format($lb_frozen_cents / 100, 2) ?> EUR</span>
          </li>

          <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
          <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= BOOSTER_DATA['email'] ?? '' ?></li>
          <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= !empty(BOOSTER_DATA['discord']) ? BOOSTER_DATA['discord'] : 'N/A' ?></li>
          <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= !empty(BOOSTER_DATA['discord_id']) ? BOOSTER_DATA['discord_id'] : 'N/A' ?></li>

          <li class="pt-4 pb-0"><span class="card-subtitle">Limits</span></li>

          <?php if (!empty($data['games']) && in_array('lol', $data['games'], true)): ?>
            <li><i class="fa-duotone fa-road-barrier dropdown-item-icon"></i>LoL:
              <?= function_exists('util_format_rank_advanced') ? util_format_rank_advanced($data['lol_tier_limit'] ?? null, $data['lol_division_limit'] ?? null, 'lol') : '—' ?>
            </li>
          <?php endif; ?>

          <?php if (!empty($data['games']) && in_array('val', $data['games'], true)): ?>
            <li><i class="fa-duotone fa-road-barrier dropdown-item-icon"></i>VAL:
              <?= function_exists('util_format_rank_advanced') ? util_format_rank_advanced($data['val_tier_limit'] ?? null, $data['val_division_limit'] ?? null, 'val') : '—' ?>
            </li>
          <?php endif; ?>

          <li><i class="fa-duotone fa-timer dropdown-item-icon"></i> <?= (int)($data['solo_order_limit'] ?? 0) ?> Solo, <?= (int)($data['duo_order_limit'] ?? 0) ?> Duo</li>
        </ul>

        <?php if (empty(BOOSTER_DATA['discord'])): ?>
          <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?? '' ?>" class="btn btn-primary btn-sm mt-4 btn-block w-100">
            <i class="fa-brands fa-discord me-1"></i> Connect to Discord
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?? '' ?>" class="btn btn-primary btn-sm mt-4 btn-block w-100">
            <i class="fa-brands fa-discord me-1"></i> Reconnect to Discord
          </a>
        <?php endif; ?>

      </div>
    </div>
    <!-- End Card -->

  </div>

  <div class="col-lg-8">
    <div class="d-grid gap-3 gap-lg-5">

      <!-- NOTE: We handle this form submission ourselves so the values don't get cleared by the global ajax-form handler. -->
      <form id="lbPayoutSettingsForm" class="form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="booster_save_payout_method">

        <div class="card">
          <div class="card-header">
            <h4 class="card-header-title">Payout Settings</h4>
            <p class="text-muted mb-0">Select your payout method. Admins will use it for your withdrawals.</p>
          </div>

          <div class="card-body">

            <!-- Fallback message box (for pages where toastr isn't loaded) -->
            <div id="lbPayoutAlert" class="alert d-none" role="alert"></div>

            <input type="hidden" name="method_type" id="lb_method_type" value="<?= esc($type) ?>">

            <div class="lb-pay-methods mb-4">
              <div class="lb-pay-method <?= $type === 'bank' ? 'active' : '' ?>" data-type="bank">
                <span class="lb-pay-badge">SEPA</span>
                <div class="lb-icon"><i class="fa-solid fa-building-columns"></i></div>
                <h5>Bank Transfer</h5>
                <p>IBAN / SWIFT</p>
              </div>

              <div class="lb-pay-method <?= $type === 'paypal' ? 'active' : '' ?>" data-type="paypal">
                <span class="lb-pay-badge">Email</span>
                <div class="lb-icon"><i class="fa-brands fa-paypal"></i></div>
                <h5>PayPal / Skrill</h5>
                <p>Email payout</p>
              </div>

              <div class="lb-pay-method <?= $type === 'crypto' ? 'active' : '' ?>" data-type="crypto">
                <span class="lb-pay-badge">Wallet</span>
                <div class="lb-icon"><i class="fa-brands fa-bitcoin"></i></div>
                <h5>Crypto</h5>
                <p>USDT / BTC / etc.</p>
              </div>
            </div>

            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">Label (optional)</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="label" value="<?= esc($label) ?>" placeholder="e.g. Main wallet">
              </div>
            </div>

            <!-- PAYPAL -->
            <div id="lb_field_paypal" class="lb-fieldset <?= $type === 'paypal' ? 'active' : '' ?>">
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" name="paypal_email" value="<?= esc($paypal_email) ?>" placeholder="name@mail.com">
                </div>
              </div>
            </div>

            <!-- BANK -->
            <div id="lb_field_bank" class="lb-fieldset <?= $type === 'bank' ? 'active' : '' ?>">
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">IBAN / Account Number</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="bank_iban" value="<?= esc($bank_iban) ?>" placeholder="DE...">
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Bank Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="bank_name" value="<?= esc($bank_name) ?>" placeholder="e.g. N26 Bank">
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">SWIFT Code</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="bank_swift" value="<?= esc($bank_swift) ?>" placeholder="ABCDEF...">
                </div>
              </div>
            </div>

            <!-- CRYPTO -->
            <div id="lb_field_crypto" class="lb-fieldset <?= $type === 'crypto' ? 'active' : '' ?>">
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Currency</label>
                <div class="col-sm-9">
                  <select class="form-select" name="crypto_currency">
                    <?php foreach (['USDT','BTC','ETH'] as $c): ?>
                      <option value="<?= $c ?>" <?= strtoupper($crypto_currency) === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Network</label>
                <div class="col-sm-9">
                  <select class="form-select" name="crypto_network">
                    <?php foreach (['TRC20','ERC20','BEP20'] as $n): ?>
                      <option value="<?= $n ?>" <?= strtoupper($crypto_network) === $n ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Wallet Address</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="crypto_address" value="<?= esc($crypto_address) ?>" placeholder="0x...">
                </div>
              </div>
            </div>

          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-primary">
              <span class="indicator-label">Save Changes</span>
              <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
              <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
            </button>
          </div>

        </div>
      </form>

    </div>
    <div id="stickyBlockEndPoint"></div>
  </div>
</div>
<!-- End Content -->

<!-- Upload icon -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="booster_upload_profile_picture">
  <div id="upload-icon-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header"><h5>Upload Icon</h5></div>
        <div class="modal-body">
          <label for="image_url" class="js-file-attach form-label" data-hs-file-attach-options='{"textTarget":"[for=\"customFile\"]"}'>Upload your file</label>
          <input class="form-control" accept="image/*" type="file" name="image_url" id="image_url">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Upload cover -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="booster_upload_cover">
  <div id="upload-cover-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header"><h5>Upload Cover Photo</h5></div>
        <div class="modal-body">
          <label for="cover_image_url" class="js-file-attach form-label" data-hs-file-attach-options='{"textTarget":"[for=\"customFile\"]"}'>Select your file</label>
          <input class="form-control" accept="image/*" type="file" name="image_url" id="cover_image_url">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
  // Use jQuery ready so the handler always runs (some pages don't trigger a custom "ready" event)
  $(function () {

    function lbToast(type, message) {
      // Prefer toastr if available
      if (window.toastr) {
        if (type === 'success' && toastr.success) return toastr.success(message);
        if (type !== 'success' && toastr.error) return toastr.error(message);
      }

      // Fallback: inline bootstrap alert
      var $a = $('#lbPayoutAlert');
      if (!$a.length) {
        // last-resort fallback
        return alert(message);
      }
      $a.removeClass('d-none alert-success alert-danger').addClass(type === 'success' ? 'alert-success' : 'alert-danger');
      $a.text(message);

      // auto-hide
      clearTimeout(window.__lbPayoutAlertTimer);
      window.__lbPayoutAlertTimer = setTimeout(function () {
        $a.addClass('d-none');
      }, 4500);
    }
    // Sticky
    new HSStickyBlock('.js-sticky-block', {
      targetSelector: document.getElementById('header') && document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
    });

    // avatar hover
    $('.avatar').mouseover(function () {
      $('.avatar-upload').stop().fadeIn(100);
      $('.avatar-upload').removeClass('d-none');
    });
    $('.avatar').mouseout(function () {
      $('.avatar-upload').stop().fadeOut(200, function () { $(this).addClass('d-none'); });
    });

    // method switching
    function setMethod(t) {
      $('#lb_method_type').val(t);
      $('.lb-pay-method').removeClass('active');
      $('.lb-pay-method[data-type="' + t + '"]').addClass('active');
      $('.lb-fieldset').removeClass('active');
      if (t === 'paypal') $('#lb_field_paypal').addClass('active');
      if (t === 'bank') $('#lb_field_bank').addClass('active');
      if (t === 'crypto') $('#lb_field_crypto').addClass('active');
    }

    $('.lb-pay-method').on('click', function () {
      setMethod($(this).data('type'));
    });

    // Save payout settings (keep values)
    $('#lbPayoutSettingsForm').on('submit', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      var $form = $(this);
      var $btn = $('#lbPayoutSaveBtn');

      // Button indicators
      $btn.prop('disabled', true);
      $btn.find('.indicator-label').addClass('d-none');
      $btn.find('.indicator-progress').removeClass('d-none');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json'
      }).done(function (r) {
        // Normalize common response shapes
        var toast = (r && (r.sendToast || r.toast)) ? (r.sendToast || r.toast) : null;
        var msg = (toast && (toast.message || toast.text)) || (r && (r.message || r.msg || r.error)) || '';
        var ok = false;

        if (r) {
          if (r.success === true || r.ok === true) ok = true;
          if (typeof r.status === 'string' && (r.status === 'success' || r.status === 'ok')) ok = true;
          if (typeof r.type === 'string' && (r.type === 'success' || r.type === 'ok')) ok = true;
          if (toast && typeof toast.type === 'string' && toast.type === 'success') ok = true;
        }

        if (ok) {
          showToast('success', msg || 'Saved successfully.');
        } else {
          showToast('error', msg || 'Something went wrong. Please try again.');
        }
      }).fail(function (xhr) {
        var msg = 'Something went wrong. Please try again.';
        try {
          var r = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
          var toast = (r && (r.sendToast || r.toast)) ? (r.sendToast || r.toast) : null;
          msg = (toast && (toast.message || toast.text)) || r.message || r.msg || r.error || msg;
        } catch (e) { }
        showToast('error', msg);
      }).always(function () {
        $btn.prop('disabled', false);
        $btn.find('.indicator-label').removeClass('d-none');
        $btn.find('.indicator-progress').addClass('d-none');
      });
    });

    setMethod($('#lb_method_type').val() || 'paypal');
  });
</script>
<?= $this->end() ?>
