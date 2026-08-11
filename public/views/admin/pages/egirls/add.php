<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add E-Girl - Admin Area | LoLBoost.gg', 'h1' => 'Add E-Girl', 'description' => 'Create new E-Girl account.'], 'contain' => true]) ?>

<style>
/* Wider egirl admin pages, matched to booster admin layout. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>


<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<?= $this->end() ?>

<div id="egirl-alert" class="alert alert-danger d-none mb-4" role="alert"></div>

<form id="egirl-add-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_add_egirl">

    <div class="card">
        <div class="card-header">
            <h3 class="card-header-title">New E-Girl Account</h3>
        </div>
        <div class="card-body">

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Username <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="username" id="f_username" placeholder="e.g. sakura_gg">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Email <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" name="email" id="f_email" placeholder="egirl@example.com">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Password <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="password" id="f_password" placeholder="Temporary password">
                    <small class="text-muted">E-Girl will use this to log in at /booster-area/auth/login</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Discord</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="discord" placeholder="username#0000 or @username">
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Games <span class="text-danger">*</span></label>
                <div class="col-sm-9 tom-select-custom">
                    <select class="js-select form-select" name="games[]" multiple autocomplete="off">
                        <option value="lol" selected>League of Legends</option>
                        <option value="val">Valorant</option>
                        <option value="tft">TFT</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Languages</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="languages" placeholder="e.g. English, German">
                    <small class="text-muted">Comma separated</small>
                </div>
            </div>

            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Bio</label>
                <div class="col-sm-9">
                    <textarea class="form-control" name="bio" rows="3" placeholder="Short introduction..."></textarea>
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <a href="<?= ADMN_URL ?>/egirls" class="btn btn-white">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" id="egirl-submit-btn" class="btn btn-primary">
                <span class="indicator-label"><i class="fa-solid fa-plus me-1"></i> Add E-Girl</span>
                <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle me-1"></span> Adding...</span>
            </button>
        </div>
    </div>
</form>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
$(document).ready(function () {
    HSCore.components.HSTomSelect.init('.js-select');

    $('#egirl-add-form').on('submit', function (e) {
        e.preventDefault();

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#egirl-alert').addClass('d-none').text('');

        let valid = true;
        ['f_username', 'f_email', 'f_password'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                $(el).addClass('is-invalid');
                $(el).siblings('.invalid-feedback').text('This field is required.');
                valid = false;
            }
        });
        if (!valid) return;

        $('#egirl-submit-btn .indicator-label').addClass('d-none');
        $('#egirl-submit-btn .indicator-progress').removeClass('d-none');
        $('#egirl-submit-btn').prop('disabled', true);

        $.ajax({
            url: '<?= AJAX_URL ?>',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }

                $('#egirl-submit-btn .indicator-label').removeClass('d-none');
                $('#egirl-submit-btn .indicator-progress').addClass('d-none');
                $('#egirl-submit-btn').prop('disabled', false);

                if (res.redirectUrl) { window.location.href = res.redirectUrl; return; }

                if (res.validationErrors) {
                    const fieldMap = { 'username': 'f_username', 'email': 'f_email', 'password': 'f_password' };
                    Object.keys(res.validationErrors).forEach(function (key) {
                        const msg = res.validationErrors[key];
                        const inputId = fieldMap[key];
                        if (inputId) {
                            const el = document.getElementById(inputId);
                            $(el).addClass('is-invalid');
                            $(el).siblings('.invalid-feedback').text(msg);
                        } else {
                            $('#egirl-alert').removeClass('d-none').text(msg);
                        }
                    });
                    return;
                }

                if (res.sendToast) {
                    const t = res.sendToast;
                    $('#egirl-alert')
                        .removeClass('d-none alert-danger alert-success')
                        .addClass('alert-' + (t.type === 'success' ? 'success' : 'danger'))
                        .text((t.title ? t.title + ': ' : '') + (t.message || ''));
                }
            },
            error: function () {
                $('#egirl-submit-btn .indicator-label').removeClass('d-none');
                $('#egirl-submit-btn .indicator-progress').addClass('d-none');
                $('#egirl-submit-btn').prop('disabled', false);
                $('#egirl-alert').removeClass('d-none').text('Server error. Please try again.');
            }
        });
    });
});
</script>
<?= $this->end() ?>
