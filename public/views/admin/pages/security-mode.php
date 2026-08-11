<?= $this->layout('admin/layouts/main', ['meta' => $meta ?? ['title' => 'Security Mode']]) ?>

<?php
$active = !empty($active);
$updated = $updated ?? '';
$statusText = $active ? 'ACTIVE' : 'INACTIVE';
$statusClass = $active ? 'is-active' : 'is-inactive';

$securityRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 4)), '/');
$securityDataDir = $securityRoot . '/security_data';

$readJson = static function (string $file, array $fallback = []): array {
    if (!is_file($file)) {
        return $fallback;
    }
    $raw = @file_get_contents($file);
    if (!is_string($raw) || $raw === '') {
        return $fallback;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $fallback;
};

$trafficSummary = static function (string $dir, int $minutes) use ($readJson): array {
    $requests = 0;
    $ips = [];
    $countries = [];
    $paths = [];
    $currentMinute = (int)floor(time() / 60);

    for ($i = 0; $i < $minutes; $i++) {
        $file = $dir . '/traffic_' . gmdate('YmdHi', ($currentMinute - $i) * 60) . '.json';
        $data = $readJson($file, []);
        $requests += (int)($data['requests'] ?? 0);
        foreach (($data['ips'] ?? []) as $ip => $count) {
            $ips[$ip] = ($ips[$ip] ?? 0) + (int)$count;
        }
        foreach (($data['countries'] ?? []) as $country => $count) {
            $countries[$country] = ($countries[$country] ?? 0) + (int)$count;
        }
        foreach (($data['paths'] ?? []) as $path => $count) {
            $paths[$path] = ($paths[$path] ?? 0) + (int)$count;
        }
    }

    arsort($countries);
    arsort($ips);
    arsort($paths);

    return [
        'requests' => $requests,
        'unique_ips' => count($ips),
        'countries' => array_slice($countries, 0, 8, true),
        'ips' => array_slice($ips, 0, 8, true),
        'paths' => array_slice($paths, 0, 8, true),
    ];
};

$traffic5 = $trafficSummary($securityDataDir, 5);
$traffic60 = $trafficSummary($securityDataDir, 60);
$metrics = $readJson($securityDataDir . '/metrics.json', ['challenges' => 0, 'verified' => 0, 'auto_activations' => 0]);
$autoState = $readJson($securityDataDir . '/auto_state.json', []);
$autoThresholdRequests = 15000;
$autoThresholdIps = 1000;
$threatLevel = 'Normal';
$threatClass = 'normal';
if (($traffic5['requests'] ?? 0) >= $autoThresholdRequests || ($traffic5['unique_ips'] ?? 0) >= $autoThresholdIps) {
    $threatLevel = 'High';
    $threatClass = 'high';
} elseif (($traffic5['requests'] ?? 0) >= (int)($autoThresholdRequests * .55) || ($traffic5['unique_ips'] ?? 0) >= (int)($autoThresholdIps * .55)) {
    $threatLevel = 'Elevated';
    $threatClass = 'elevated';
}
?>

<style>
.security-mode-page {
    max-width: 1180px;
    margin: 0 auto;
}
.security-mode-title {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.security-mode-title h1 {
    color: #fff;
    font-size: 1.6rem;
    font-weight: 800;
    margin: 0 0 .35rem;
}
.security-mode-title p {
    color: #b2b8c2;
    margin: 0;
    font-size: .95rem;
}
.security-alert {
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .85rem;
    padding: .95rem 1.1rem;
    margin-bottom: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: .7rem;
}
.security-alert.success {
    background: rgba(0,201,167,.12);
    color: #00c9a7;
    border-color: rgba(0,201,167,.28);
}
.security-alert.danger {
    background: rgba(237,76,120,.12);
    color: #ed4c78;
    border-color: rgba(237,76,120,.28);
}
.security-alert.warning {
    background: rgba(245,202,153,.12);
    color: #f5ca99;
    border-color: rgba(245,202,153,.28);
}
.security-hero {
    background: linear-gradient(135deg, rgba(92,74,227,.18), rgba(237,76,120,.08)), #25282a;
    border: .0625rem solid #2f3235;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 .6rem 1.4rem rgba(0,0,0,.25);
}
.security-hero-header {
    padding: 1.35rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.12);
}
.security-hero-header h2 {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0 0 .25rem;
}
.security-hero-header p {
    color: #aeb5bf;
    margin: 0;
    font-size: .9rem;
}
.security-status {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    border-radius: 99rem;
    padding: .55rem .95rem;
    font-weight: 800;
    font-size: .78rem;
    letter-spacing: .04em;
    white-space: nowrap;
}
.security-status.is-active {
    background: rgba(237,76,120,.16);
    color: #ed4c78;
    border: 1px solid rgba(237,76,120,.34);
}
.security-status.is-inactive {
    background: rgba(0,201,167,.14);
    color: #00c9a7;
    border: 1px solid rgba(0,201,167,.30);
}
.security-dot {
    width: .55rem;
    height: .55rem;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 0 .25rem rgba(255,255,255,.06);
}
.security-body {
    padding: 1.5rem;
}
.security-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .9rem;
    margin-bottom: 1.25rem;
}
.security-stat-card {
    background: rgba(30,32,34,.66);
    border: .0625rem solid #2f3235;
    border-radius: .85rem;
    padding: 1.1rem;
    display: flex;
    gap: .9rem;
    align-items: flex-start;
}
.security-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: .8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
}
.security-stat-icon.purple {
    background: rgba(92,74,227,.16);
    color: #9b8bf0;
}
.security-stat-icon.green {
    background: rgba(0,201,167,.13);
    color: #00c9a7;
}
.security-stat-icon.pink {
    background: rgba(237,76,120,.13);
    color: #ed4c78;
}
.security-stat-card span {
    display: block;
    color: #91989e;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .25rem;
}
.security-stat-card strong {
    display: block;
    color: #fff;
    font-size: 1rem;
    margin-bottom: .25rem;
}
.security-stat-card p {
    color: #aeb5bf;
    margin: 0;
    font-size: .86rem;
    line-height: 1.55;
}
.security-control-panel {
    background: rgba(30,32,34,.72);
    border: .0625rem solid #2f3235;
    border-radius: .95rem;
    padding: 1.25rem;
}
.security-control-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: .0625rem solid #2f3235;
}
.security-control-top h3 {
    color: #fff;
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0 0 .25rem;
}
.security-control-top p {
    color: #aeb5bf;
    margin: 0;
    font-size: .88rem;
}
.security-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .8rem;
    align-items: center;
}
.security-actions form {
    margin: 0;
}
.security-btn {
    border: 0;
    border-radius: .65rem;
    padding: .75rem 1rem;
    font-weight: 800;
    font-size: .9rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    transition: transform .15s ease, opacity .15s ease, box-shadow .15s ease;
}
.security-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 .45rem 1rem rgba(0,0,0,.25);
}
.security-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}
.security-btn.activate {
    background: #ed4c78;
    color: #fff;
}
.security-btn.deactivate {
    background: #00c9a7;
    color: #10201d;
}
.security-note {
    margin-top: 1rem;
    color: #aeb5bf;
    font-size: .88rem;
    line-height: 1.65;
}
.security-note strong {
    color: #fff;
}
.security-note code {
    color: #ed4c78;
    background: rgba(237,76,120,.10);
    border: 1px solid rgba(237,76,120,.18);
    border-radius: .35rem;
    padding: .1rem .35rem;
}


.security-live-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: .9rem;
    margin-bottom: 1.25rem;
}
.security-live-card {
    background: rgba(30,32,34,.72);
    border: .0625rem solid #2f3235;
    border-radius: .9rem;
    padding: 1rem;
}
.security-live-card span {
    color: #91989e;
    display: block;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .06em;
    margin-bottom: .3rem;
    text-transform: uppercase;
}
.security-live-card strong {
    color: #fff;
    display: block;
    font-size: 1.45rem;
    font-weight: 900;
    line-height: 1.1;
}
.security-live-card small {
    color: #aeb5bf;
    display: block;
    font-size: .78rem;
    margin-top: .35rem;
}
.security-threat.normal strong { color: #00c9a7; }
.security-threat.elevated strong { color: #f5ca99; }
.security-threat.high strong { color: #ed4c78; }
.security-table-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: .9rem;
    margin-bottom: 1.25rem;
}
.security-mini-table {
    background: rgba(30,32,34,.72);
    border: .0625rem solid #2f3235;
    border-radius: .95rem;
    overflow: hidden;
}
.security-mini-table h3 {
    color: #fff;
    font-size: .95rem;
    font-weight: 800;
    margin: 0;
    padding: .95rem 1rem;
    border-bottom: .0625rem solid #2f3235;
}
.security-mini-table table {
    width: 100%;
    border-collapse: collapse;
}
.security-mini-table td {
    border-bottom: .0625rem solid rgba(255,255,255,.06);
    color: #c8ced6;
    font-size: .82rem;
    padding: .75rem 1rem;
    vertical-align: middle;
}
.security-mini-table tr:last-child td {
    border-bottom: 0;
}
.security-mini-table td:last-child {
    color: #fff;
    font-weight: 800;
    text-align: right;
    white-space: nowrap;
}
.security-empty-row {
    color: #7f8791 !important;
    text-align: left !important;
}

.security-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(6,7,10,.74);
    backdrop-filter: blur(8px);
}
.security-confirm-overlay.is-open {
    display: flex;
}
.security-confirm-modal {
    width: min(460px, 100%);
    background: #25282a;
    border: .0625rem solid #3a3d42;
    border-radius: 1rem;
    box-shadow: 0 1.25rem 3rem rgba(0,0,0,.45);
    overflow: hidden;
}
.security-confirm-head {
    padding: 1.15rem 1.25rem;
    display: flex;
    gap: .85rem;
    align-items: center;
    border-bottom: .0625rem solid #2f3235;
    background: linear-gradient(135deg, rgba(92,74,227,.18), rgba(237,76,120,.08));
}
.security-confirm-icon {
    width: 42px;
    height: 42px;
    border-radius: .8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, #6e5cff, #ed4c78);
    box-shadow: 0 .5rem 1.25rem rgba(92,74,227,.25);
    flex-shrink: 0;
}
.security-confirm-head h4 {
    color: #fff;
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0 0 .2rem;
}
.security-confirm-head p {
    color: #aeb5bf;
    font-size: .86rem;
    margin: 0;
}
.security-confirm-body {
    padding: 1.25rem;
}
.security-confirm-body p {
    color: #c8ced6;
    font-size: .94rem;
    line-height: 1.6;
    margin: 0;
}
.security-confirm-actions {
    display: flex;
    justify-content: flex-end;
    gap: .75rem;
    padding: 0 1.25rem 1.25rem;
}
.security-modal-btn {
    border: 0;
    border-radius: .65rem;
    padding: .72rem 1rem;
    font-weight: 800;
    font-size: .88rem;
    display: inline-flex;
    align-items: center;
    gap: .45rem;
}
.security-modal-btn.cancel {
    background: #303337;
    color: #dce1e8;
    border: .0625rem solid #42464c;
}
.security-modal-btn.confirm {
    color: #fff;
    background: linear-gradient(135deg, #6e5cff, #ed4c78);
    box-shadow: 0 .45rem 1rem rgba(92,74,227,.22);
}

@media (max-width: 767px) {
    .security-mode-title,
    .security-hero-header,
    .security-control-top {
        flex-direction: column;
        align-items: flex-start;
    }
    .security-actions,
    .security-actions form,
    .security-btn {
        width: 100%;
    }
    .security-btn {
        justify-content: center;
    }
}
</style>

<div class="security-mode-page">
    <?php if ($updated === 'enabled'): ?>
        <div class="security-alert danger"><i class="fa-solid fa-shield-exclamation"></i>Security Mode is now active. Unverified visitors will see your LoLBoost security check first.</div>
    <?php elseif ($updated === 'disabled'): ?>
        <div class="security-alert success"><i class="fa-solid fa-circle-check"></i>Security Mode is now inactive. Visitors can access the website normally again.</div>
    <?php elseif ($updated === 'csrf'): ?>
        <div class="security-alert warning"><i class="fa-solid fa-triangle-exclamation"></i>The security token was invalid. Please reload the page and try again.</div>
    <?php endif; ?>

    <div class="security-hero">
        <div class="security-hero-header">
            <div>
                <h2>LoLBoost Security Mode</h2>
                <p>Track live traffic and control your custom Turnstile powered browser verification globally.</p>
            </div>
            <span class="security-status <?= $statusClass ?>"><span class="security-dot"></span><?= $statusText ?></span>
        </div>

        <div class="security-body">

            <div class="security-live-grid">
                <div class="security-live-card">
                    <span>Requests, last 5 minutes</span>
                    <strong><?= number_format((int)$traffic5['requests']) ?></strong>
                    <small>Auto activates at <?= number_format($autoThresholdRequests) ?></small>
                </div>
                <div class="security-live-card">
                    <span>Unique IPs, last 5 minutes</span>
                    <strong><?= number_format((int)$traffic5['unique_ips']) ?></strong>
                    <small>Auto activates at <?= number_format($autoThresholdIps) ?></small>
                </div>
                <div class="security-live-card security-threat <?= $threatClass ?>">
                    <span>Traffic status</span>
                    <strong><?= $threatLevel ?></strong>
                    <small>Measured directly on your website</small>
                </div>
                <div class="security-live-card">
                    <span>Requests, last 60 minutes</span>
                    <strong><?= number_format((int)$traffic60['requests']) ?></strong>
                    <small><?= number_format((int)$traffic60['unique_ips']) ?> unique IPs</small>
                </div>
                <div class="security-live-card">
                    <span>Challenges today</span>
                    <strong><?= number_format((int)($metrics['challenges'] ?? 0)) ?></strong>
                    <small><?= number_format((int)($metrics['verified'] ?? 0)) ?> successful verifications</small>
                </div>
                <div class="security-live-card">
                    <span>Auto activations today</span>
                    <strong><?= number_format((int)($metrics['auto_activations'] ?? 0)) ?></strong>
                    <small><?= !empty($autoState['reason']) ? esc((string)$autoState['reason']) : 'No auto action yet' ?></small>
                </div>
            </div>

            <div class="security-table-grid">
                <div class="security-mini-table">
                    <h3>Top countries, last 5 minutes</h3>
                    <table>
                        <?php if (!empty($traffic5['countries'])): ?>
                            <?php foreach ($traffic5['countries'] as $country => $count): ?>
                                <tr><td><?= esc((string)$country) ?></td><td><?= number_format((int)$count) ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td class="security-empty-row" colspan="2">No traffic recorded yet.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="security-mini-table">
                    <h3>Top IPs, last 5 minutes</h3>
                    <table>
                        <?php if (!empty($traffic5['ips'])): ?>
                            <?php foreach ($traffic5['ips'] as $ip => $count): ?>
                                <tr><td><?= esc((string)$ip) ?></td><td><?= number_format((int)$count) ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td class="security-empty-row" colspan="2">No IP data recorded yet.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="security-mini-table">
                    <h3>Top paths, last 5 minutes</h3>
                    <table>
                        <?php if (!empty($traffic5['paths'])): ?>
                            <?php foreach ($traffic5['paths'] as $path => $count): ?>
                                <tr><td><?= esc((string)$path) ?></td><td><?= number_format((int)$count) ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td class="security-empty-row" colspan="2">No path data recorded yet.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="security-stats-grid">
                <div class="security-stat-card">
                    <div class="security-stat-icon purple"><i class="fa-duotone fa-shield-halved"></i></div>
                    <div>
                        <span>Protection</span>
                        <strong>Custom verification page</strong>
                        <p>When active, visitors are checked with your branded LoLBoost security screen before entering the website.</p>
                    </div>
                </div>
                <div class="security-stat-card">
                    <div class="security-stat-icon green"><i class="fa-duotone fa-bolt"></i></div>
                    <div>
                        <span>Best use</span>
                        <strong>Bot traffic and fake requests</strong>
                        <p>Use this mode during crawling waves, automated requests or hosting protection events.</p>
                    </div>
                </div>
                <div class="security-stat-card">
                    <div class="security-stat-icon pink"><i class="fa-duotone fa-lock-keyhole"></i></div>
                    <div>
                        <span>Safety</span>
                        <strong>Admin stays accessible</strong>
                        <p>Admin pages, API routes, webhooks and payment callbacks should remain excluded from verification.</p>
                    </div>
                </div>
            </div>

            <div class="security-control-panel">
                <div class="security-control-top">
                    <div>
                        <h3>Mode controls</h3>
                        <p>Switch the security check on or off instantly. Auto Protection also activates it when traffic spikes are detected.</p>
                    </div>
                    <span class="security-status <?= $statusClass ?>"><span class="security-dot"></span><?= $statusText ?></span>
                </div>

                <div class="security-actions">
                    <form method="post" action="<?= ADMN_URL ?>/security-mode" id="securityEnableForm">
                        <?php if (function_exists('csrf_token')): ?>
                            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="action" value="enable">
                        <button type="button" class="security-btn activate" data-security-confirm="enable" <?= $active ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-shield-exclamation"></i>Activate Security Mode
                        </button>
                    </form>

                    <form method="post" action="<?= ADMN_URL ?>/security-mode" id="securityDisableForm">
                        <?php if (function_exists('csrf_token')): ?>
                            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="action" value="disable">
                        <button type="button" class="security-btn deactivate" data-security-confirm="disable" <?= !$active ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-circle-check"></i>Deactivate
                        </button>
                    </form>
                </div>

                <div class="security-note">
                    <strong>Important:</strong> Keep <code>admin area</code>, <code>API</code>, <code>webhooks</code> and <code>payment callbacks</code> excluded from the security check, so you do not lock yourself out.
                </div>
            </div>
        </div>
    </div>
</div>


<div class="security-confirm-overlay" id="securityConfirmModal" aria-hidden="true">
    <div class="security-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="securityConfirmTitle">
        <div class="security-confirm-head">
            <div class="security-confirm-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <h4 id="securityConfirmTitle">Confirm Security Mode change</h4>
                <p id="securityConfirmSubtitle">Please confirm this action.</p>
            </div>
        </div>
        <div class="security-confirm-body">
            <p id="securityConfirmText">This will change the current security mode.</p>
        </div>
        <div class="security-confirm-actions">
            <button type="button" class="security-modal-btn cancel" id="securityConfirmCancel">Cancel</button>
            <button type="button" class="security-modal-btn confirm" id="securityConfirmSubmit"><i class="fa-solid fa-check"></i>Confirm</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('securityConfirmModal');
    var title = document.getElementById('securityConfirmTitle');
    var subtitle = document.getElementById('securityConfirmSubtitle');
    var text = document.getElementById('securityConfirmText');
    var cancel = document.getElementById('securityConfirmCancel');
    var submit = document.getElementById('securityConfirmSubmit');
    var selectedForm = null;

    var content = {
        enable: {
            form: 'securityEnableForm',
            title: 'Activate Security Mode?',
            subtitle: 'Visitors will see the LoLBoost verification page first.',
            text: 'Use this during bot waves, fake requests or suspicious traffic spikes. Admin, API, webhook and payment callback routes remain excluded.',
            button: 'Activate now'
        },
        disable: {
            form: 'securityDisableForm',
            title: 'Deactivate Security Mode?',
            subtitle: 'Visitors will access the website normally again.',
            text: 'Turn this off when the traffic spike is over and you no longer need the browser verification page.',
            button: 'Deactivate now'
        }
    };

    function openModal(type) {
        var data = content[type];
        if (!data) return;
        selectedForm = document.getElementById(data.form);
        title.textContent = data.title;
        subtitle.textContent = data.subtitle;
        text.textContent = data.text;
        submit.innerHTML = '<i class="fa-solid fa-check"></i>' + data.button;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        selectedForm = null;
    }

    document.querySelectorAll('[data-security-confirm]').forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(button.getAttribute('data-security-confirm'));
        });
    });

    cancel.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
    submit.addEventListener('click', function () {
        if (selectedForm) selectedForm.submit();
    });
})();
</script>
