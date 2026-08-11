<!-- Modal with tabs and forms -->
<div class="modal fade" id="auth_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <!-- Modal header with nav tabs -->
      <div class="modal-header">
        <ul class="nav nav-tabs mb-0" role="tablist">
          <li class="nav-item fs-sm mb-0">
            <a class="nav-link active" href="#signin" data-bs-toggle="tab" role="tab" aria-selected="true">
              <i class="fa-duotone fa-lock-keyhole-open me-2"></i>
              Login
            </a>
          </li>
          <li class="nav-item fs-sm mb-0">
            <a class="nav-link" href="#signup" data-bs-toggle="tab" role="tab" aria-selected="false">
              <i class="fa-duotone fa-user-plus me-2"></i>
              Register
            </a>
          </li>
        </ul>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal body with tab panes -->
      <div class="modal-body tab-content py-4">

        <!-- Sign in form -->
        <form class="tab-pane ajax-form fade show active" action="<?= AJAX_URL ?>" autocomplete="off" id="signin">
          <input type="hidden" name="action" value="auth_client_login">
          <div class="mb-3">
            <label class="form-label" for="email1">Email address</label>
            <input class="form-control" type="email" name="email" id="email1" placeholder="mail@example.com">
          </div>
          <div class="mb-3">
            <label class="form-label" for="pass1">Password</label>
            <div class="password-toggle">
              <input class="form-control" type="password" name="password" id="pass1" placeholder="••••••••">
              <label class="password-toggle-btn">
                <input class="password-toggle-check" type="checkbox"><span class="password-toggle-indicator"></span>
              </label>
            </div>
          </div>
          <div class="alert alert-danger form-error" style="display:none" role="alert">
          </div>
          <div class="mb-3 d-flex flex-wrap justify-content-between">
            <div class="form-check mb-2">
              <input class="form-check-input" name="remember_me" type="checkbox" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a class="fs-sm text-decoration-none" href="#" data-bs-toggle="modal" data-bs-target="#forgot_password_md">Forgot password?</a>
          </div>
          <button class="btn btn-primary d-block w-100" type="submit">
            <span class="indicator-label">Sign in</span>
            <span class="indicator-progress">
              <span class="spinner-border spinner-border-sm align-middle"></span>
            </span>
          </button>
        </form>

        <!-- Sign up form -->
        <form class="tab-pane ajax-form fade" autocomplete="off" action="<?= AJAX_URL ?>" id="signup">
          <input type="hidden" name="action" value="auth_client_register">
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" type="text" name="username" id="username" placeholder="HideOnBush">
          </div>
          <div class="mb-3">
            <label class="form-label" for="email2">Email address</label>
            <input class="form-control" type="email" name="email" id="email2" placeholder="mail@example.com">
          </div>
          <div class="mb-3">
            <label class="form-label" for="pass2">Password</label>
            <div class="password-toggle">
              <input class="form-control" type="password" name="password" id="pass2" placeholder="••••••••">
              <label class="password-toggle-btn">
                <input class="password-toggle-check" type="checkbox"><span class="password-toggle-indicator"></span>
              </label>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" name="tos" type="checkbox" id="tos">
              <label class="form-check-label" for="tos">I agree to the <a href="<?= BASE_URL ?>/legal/terms" class="text-decoration-none">terms of service</a> and <a href="<?= BASE_URL ?>/legal/privacy" class="text-decoration-none">privacy policy</a></label>
            </div>
          </div>
          <div class="alert alert-danger form-error" style="display:none" role="alert">
          </div>
          <button class="btn btn-primary d-block w-100" type="submit">
            <span class="indicator-label">Sign Up</span>
            <span class="indicator-progress">
              <span class="spinner-border spinner-border-sm align-middle"></span>
            </span>
          </button>
        </form>
        <div class="separator"></div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= BASE_URL ?>/auth/google" class="btn btn-google-show" style="flex:1 0 auto">
            <i class="fa-brands fa-google me-2"></i>
            <span>Login with Google</span>
          </a>
          <a href="<?= BASE_URL ?>/auth/discord" class="btn btn-discord-show" style="flex:1 0 auto">
            <i class="fa-brands fa-discord me-2"></i>
            <span>Login with Discord</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Forgot password modal -->
<div class="modal fade" id="forgot_password_md" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Forgot password</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="ajax-form" action="<?= AJAX_URL ?>" autocomplete="off">
          <input type="hidden" name="action" value="client_forgot_password">
          <div class="mb-3">
            <label class="form-label" for="email3">Email address</label>
            <input class="form-control" type="email" name="email" id="email3" placeholder="email@example.com">
          </div>
          <div class="alert alert-danger form-error" style="display:none" role="alert">
          </div>
          <button class="btn btn-primary d-block w-100" type="submit">
            <span class="indicator-label">Send reset link</span>
            <span class="indicator-progress">
              <span class="spinner-border spinner-border-sm align-middle"></span>
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if(isset($reset_password)): ?>
<!-- Reset password modal -->
<div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="reset_password_md" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset password</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="ajax-form" action="<?= AJAX_URL ?>" autocomplete="off">
          <input type="hidden" name="action" value="client_reset_password">
          <input type="hidden" name="recovery_id" value="<?= $reset_password ?>">
          <div class="mb-3">
            <label class="form-label required" for="pass3">New password</label>
            <div class="password-toggle">
              <input class="form-control" type="password" name="password" id="pass3" placeholder="••••••••">
              <label class="password-toggle-btn">
                <input class="password-toggle-check" type="checkbox"><span class="password-toggle-indicator"></span>
              </label>
            </div>
          </div>
          <div class="alert alert-danger form-error" style="display:none" role="alert">
          </div>
          <button class="btn btn-primary d-block w-100" type="submit">
            <span class="indicator-label">Reset password</span>
            <span class="indicator-progress">
              <span class="spinner-border spinner-border-sm align-middle"></span>
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>


<?php endif; ?>