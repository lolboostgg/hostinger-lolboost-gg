<?= $this->layout('seller/layouts/auth', ['meta' => ['title' => 'Login - Seller Area | LoLBoost.gg', 'description' => 'Login to your seller dashboard.']]) ?>

<form class="ajax-form" novalidate action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="auth_seller_login">

    <h2 class="lb-auth-title">Seller Sign in</h2>
    <p class="lb-auth-subtitle">Login to your seller dashboard</p>

    <div class="lb-field">
        <label class="lb-label" for="email">Email</label>
        <input class="lb-input" type="email" name="email" id="email" tabindex="1" placeholder="seller@example.com" required>
        <span class="invalid-feedback">Please enter a valid email</span>
    </div>

    <div class="lb-field">
        <label class="lb-label" for="password">Password</label>
        <div class="lb-input-wrap">
            <input class="lb-input" type="password" name="password" id="password" placeholder="Password" required>
            <button class="lb-password-toggle" type="button" data-lb-toggle-password="#password" aria-label="Toggle password visibility">
                <i class="fa-duotone fa-eye"></i>
            </button>
        </div>
        <span class="invalid-feedback">Please enter your password.</span>
    </div>

    <label class="lb-check" for="remember_me">
        <input type="checkbox" value="1" id="remember_me" name="remember_me">
        <span>Remember me</span>
    </label>

    <div class="lb-error" role="alert" id="form-error"></div>

    <button type="submit" class="lb-submit">
        <span class="indicator-label">Sign in</span>
        <span class="indicator-progress">Signing in...</span>
        <span class="indicator-success"><i class="fa-regular fa-circle-check"></i></span>
    </button>

    <div class="lb-auth-footer-link">
        Want to sell accounts? <a href="<?= BASE_URL ?>/seller-area/auth/register">Apply as seller</a>
    </div>
</form>
