<?= $this->layout('seller/layouts/auth', ['meta' => $meta]) ?>

<?php if (isset($_GET['applied'])): ?>
<div class="lb-success-state">
    <div class="lb-success-icon"><i class="fa-solid fa-check"></i></div>
    <h3>Application Submitted!</h3>
    <p class="lb-auth-subtitle">Thanks for applying. We'll review your application and get back to you via email within 24–48 hours.</p>
    <a href="<?= BASE_URL ?>/seller-area/auth/login" class="lb-submit" style="display:inline-flex;align-items:center;justify-content:center;">Back to Login</a>
</div>
<?php else: ?>
<form class="ajax-form" action="<?= AJAX_URL ?>" id="sellerApplyForm">
    <input type="hidden" name="action" value="seller_register">

    <h2 class="lb-auth-title" style="font-size:40px;">Become a Seller</h2>
    <p class="lb-auth-subtitle">List your League of Legends accounts on our marketplace. Apply below and we'll review your account.</p>

    <div class="lb-mini-panel">
        <div class="lb-mini-panel-title">Why sell with us?</div>
        <ul class="lb-benefit-list">
            <li><i class="fa-solid fa-check"></i><span>Keep up to 85% of every sale</span></li>
            <li><i class="fa-solid fa-check"></i><span>Access to thousands of active buyers</span></li>
            <li><i class="fa-solid fa-check"></i><span>Payouts twice a month via bank or crypto</span></li>
            <li><i class="fa-solid fa-check"></i><span>Dedicated seller dashboard & support</span></li>
        </ul>
    </div>

    <div class="lb-field">
        <label class="lb-label">Username <span class="lb-required">*</span></label>
        <input type="text" name="username" class="lb-input" placeholder="Your seller username" required autocomplete="username">
    </div>

    <div class="lb-field">
        <label class="lb-label">Email address <span class="lb-required">*</span></label>
        <input type="email" name="email" class="lb-input" placeholder="you@example.com" required autocomplete="email">
    </div>

    <div class="lb-field">
        <label class="lb-label">Password <span class="lb-required">*</span></label>
        <div class="lb-input-wrap">
            <input type="password" name="password" id="applyPassword" class="lb-input" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
            <button class="lb-password-toggle" type="button" data-lb-toggle-password="#applyPassword" aria-label="Toggle password visibility">
                <i class="fa-duotone fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="lb-field">
        <label class="lb-label">Discord <span style="color:var(--lb-muted);font-weight:700;">(optional but recommended)</span></label>
        <input type="text" name="discord" class="lb-input" placeholder="YourUsername#0000 or @username">
    </div>

    <div class="lb-field">
        <label class="lb-label">Why do you want to sell?</label>
        <textarea name="note" class="lb-input" rows="3" placeholder="Tell us about yourself, your accounts, servers and experience."></textarea>
    </div>

    <label class="lb-check" for="applyTos" style="align-items:flex-start;line-height:1.45;">
        <input type="checkbox" id="applyTos" name="tos" required style="margin-top:2px;">
        <span>I agree to the <a href="<?= BASE_URL ?>/terms" target="_blank" class="lb-link">Terms of Service</a> and confirm that I own the accounts I will be listing.</span>
    </label>

    <button type="submit" class="lb-submit">
        <span class="indicator-label"><i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i> Submit Application</span>
        <span class="indicator-progress">Submitting...</span>
        <span class="indicator-success"><i class="fa-regular fa-circle-check"></i></span>
    </button>

    <div class="lb-auth-footer-link">
        Already have an account? <a href="<?= BASE_URL ?>/seller-area/auth/login">Sign in</a>
    </div>
</form>
<?php endif ?>
