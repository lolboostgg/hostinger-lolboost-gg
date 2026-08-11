<?= $this->layout('client/layouts/main', ['meta' => $meta ?? ['title' => 'Referrals - Client Area | LoLBoost.gg']]) ?>

<?php
$lb_ref_settings = function_exists('lb_referral_get_settings')
    ? lb_referral_get_settings()
    : ['client_reward_percent' => 5];

$lb_client_allowed = function_exists('lb_referral_client_is_allowed')
    ? lb_referral_client_is_allowed((int) CLIENT_ID)
    : false;

$lb_client_ref = ($lb_client_allowed && function_exists('lb_referral_get_dashboard_data'))
    ? lb_referral_get_dashboard_data('client', (int) CLIENT_ID)
    : [
        'link' => false,
        'share_url' => '',
        'earnings_points' => 0.00,
        'clicks' => 0,
        'signups' => 0,
        'purchases' => 0,
    ];

$lb_client_reward_percent = function_exists('lb_referral_get_client_reward_percent')
    ? (float) lb_referral_get_client_reward_percent((int) CLIENT_ID)
    : (float) ($lb_ref_settings['client_reward_percent'] ?? 5);
$lb_client_share_url = (string) ($lb_client_ref['share_url'] ?? '');
$lb_current_code = (string) (($lb_client_ref['link']['code'] ?? '') ?: '');
?>

<?php if (!$lb_client_allowed): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fa-duotone fa-link-slash fa-3x text-muted mb-3"></i>
            <h3 class="mb-2">Referrals are not enabled for your account</h3>
            <p class="text-muted mb-0">Referral access is available for selected clients only.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header">
            <div>
                <h4 class="card-header-title mb-1">Referral Dashboard</h4>
                <p class="card-text text-muted mb-0">Share your personal referral link and earn LB Coins when purchases come in through your link.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-success small mb-1">Earnings</div>
                        <div class="fs-2 fw-bold"><?= number_format((float)($lb_client_ref['earnings_points'] ?? 0), 2) ?></div>
                        <div class="text-muted small">LB Coins</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-primary small mb-1">Clicks</div>
                        <div class="fs-2 fw-bold"><?= (int)($lb_client_ref['clicks'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-danger small mb-1">Signups</div>
                        <div class="fs-2 fw-bold"><?= (int)($lb_client_ref['signups'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-warning small mb-1">Purchases</div>
                        <div class="fs-2 fw-bold"><?= (int)($lb_client_ref['purchases'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <label class="form-label">Your referral link</label>
            <div class="d-flex gap-2 flex-column flex-md-row mb-3">
                <input type="text" readonly class="form-control" id="clientReferralLink" value="<?= htmlspecialchars($lb_client_share_url, ENT_QUOTES, 'UTF-8') ?>">
                <button type="button" class="btn btn-primary px-4" onclick="lbCopyReferralLink('clientReferralLink', this)">
                    <i class="fa-regular fa-copy me-2"></i>Copy Link
                </button>
            </div>

            <div class="alert alert-soft-primary mb-0">
                Your reward: <strong><?= rtrim(rtrim(number_format($lb_client_reward_percent, 2, '.', ''), '0'), '.') ?>%</strong> of each completed referred order is credited as LB Coins.
            </div>
        </div>
    </div>

    <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="client_update_referral_code">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="card-header-title mb-1">Custom Referral Link</h4>
                    <p class="card-text text-muted mb-0">Choose a short link that is easy to share.</p>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label" for="clientReferralCustomCode">Custom link name</label>
                <div class="input-group">
                    <span class="input-group-text"><?= htmlspecialchars(rtrim((string)BASE_URL, '/'), ENT_QUOTES, 'UTF-8') ?>/?ref=</span>
                    <input type="text" class="form-control" name="custom_code" id="clientReferralCustomCode" value="<?= htmlspecialchars($lb_current_code, ENT_QUOTES, 'UTF-8') ?>" placeholder="your-name">
                </div>
                <small class="text-muted d-block mt-2">Use 3-64 characters: letters, numbers, dashes or underscores.</small>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">Save Custom Link</span>
                    <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                    <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                </button>
            </div>
        </div>
    </form>

    <script>
        function lbCopyReferralLink(inputId, btn) {
            var input = document.getElementById(inputId);
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
            if (btn) {
                var old = btn.innerHTML;
                btn.innerHTML = '<i class="fa-regular fa-circle-check me-2"></i>Copied';
                setTimeout(function () { btn.innerHTML = old; }, 1600);
            }
        }
    </script>
<?php endif; ?>
