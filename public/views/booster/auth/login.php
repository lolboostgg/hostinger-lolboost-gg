<?= $this->layout('booster/layouts/auth', ['meta' => ['title'=>'Login - Booster Area | LoLBoost.gg','description'=>'Overview and statistics of the website.']]) ?>

<form class="ajax-form" novalidate action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="auth_booster_login">

    <h2 class="lb-auth-title">Sign in</h2>
    <p class="lb-auth-subtitle">Login to your booster account</p>

    <div class="lb-field">
        <label class="lb-label" for="login">Email</label>
        <input class="lb-input" type="email" name="email" id="login" tabindex="1" placeholder="example@gmail.com" aria-label="example@gmail.com" required>
        <span class="invalid-feedback">Please enter a valid login</span>
    </div>

    <div class="lb-field">
        <label class="lb-label" for="password">Password</label>
        <div class="lb-input-wrap">
            <input class="lb-input" type="password" name="password" id="password" placeholder="Password" aria-label="Password" required>
            <button class="lb-password-toggle" type="button" data-lb-toggle-password="#password" aria-label="Toggle password visibility">
                <i class="fa-duotone fa-eye"></i>
            </button>
        </div>
        <span class="invalid-feedback">Please enter a valid password.</span>
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
</form>
