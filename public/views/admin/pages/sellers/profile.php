<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Profile - Admin Area | LoLBoost.gg', 'h1' => 'Seller', 'description' => 'Manage marketplace seller profile.']]) ?>
<?php $activeTab = 'profile'; include __DIR__ . '/_shared.php'; ?>

<div class="row g-4">
    <!-- Left column -->
    <div class="col-lg-4">
        <div class="d-grid gap-4">

            <!-- Overview -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Overview</h4></div>
                <div class="card-body">
                    <div class="form-section-title">Account</div>
                    <ul class="list-unstyled list-py-2 mb-3">
                        <li><i class="fa-duotone fa-hashtag dropdown-item-icon"></i> <?= $sellerId ?></li>
                        <li><i class="fa-duotone fa-user dropdown-item-icon"></i> <?= $username ?: '—' ?></li>
                        <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= $email ?: '—' ?></li>
                        <li><i class="fa-duotone fa-wallet dropdown-item-icon"></i> €<?= $balanceEuro ?></li>
                        <li><i class="fa-duotone fa-percent dropdown-item-icon"></i> <?= $effectiveFee ?>% platform fee</li>
                        <li><i class="fa-duotone <?= $statusIcon ?> dropdown-item-icon"></i> <?= $statusLabel ?></li>
                    </ul>
                    <div class="form-section-title">Contact</div>
                    <ul class="list-unstyled list-py-2 mb-0">
                        <?php if ($discord): ?>
                            <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= $discord ?></li>
                        <?php else: ?>
                            <li class="text-muted"><i class="fa-duotone fa-slash dropdown-item-icon"></i> No Discord</li>
                        <?php endif; ?>
                        <?php if ($clientId > 0): ?>
                            <li><i class="fa-duotone fa-user dropdown-item-icon"></i>
                                Linked client <a href="<?= ADMN_URL ?>/client/<?= $clientId ?>">#<?= $clientId ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Actions</h4></div>
                <div class="card-body">
                    <?php
                      $profIsApproved = $isApproved;
                    ?>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge <?= $profIsApproved ? 'bg-primary' : 'bg-soft-warning text-warning' ?>">
                            <i class="fa-duotone fa-badge-check me-1"></i>
                            <?= $profIsApproved ? 'Approved' : 'Pending' ?>
                        </span>
                        <span class="badge <?= $isBanned ? 'bg-soft-danger text-danger' : ($isActive ? 'bg-soft-success text-success' : 'bg-secondary') ?>">
                            <i class="fa-duotone <?= $isBanned ? 'fa-ban' : 'fa-circle-check' ?> me-1"></i>
                            <?= $isBanned ? 'Banned' : ($isActive ? 'Active' : 'Inactive') ?>
                        </span>
                    </div>

                    <?php if (!$profIsApproved && !$isBanned): ?>
                        <form class="ajax-form mb-2" action="<?= AJAX_URL ?>" method="POST">
                            <input type="hidden" name="action" value="admin_approve_seller">
                            <input type="hidden" name="id" value="<?= $sellerId ?>">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa-duotone fa-badge-check me-1"></i> Approve &amp; Send Credentials
                            </button>
                        </form>
                        <p class="text-muted small mb-3">
                            Approves the seller, unlocks the account, and sends login credentials by email.
                        </p>
                    <?php endif; ?>

                    <button type="button" id="openProfileSellerResendModal" class="btn btn-primary w-100 mb-2">
                        <i class="fa-duotone fa-key me-1"></i> Send new password
                    </button>
                    <p class="text-muted small mb-3">
                        Generates a new password and re-sends the welcome email.
                    </p>

                    <?php if (!$isBanned): ?>
                        <button type="button" class="btn btn-outline-danger w-100"
                                data-bs-toggle="modal" data-bs-target="#banSellerModal">
                            <i class="fa-duotone fa-ban me-1"></i> Ban Seller
                        </button>
                    <?php else: ?>
                        <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                            <input type="hidden" name="action" value="admin_unban_seller">
                            <input type="hidden" name="id" value="<?= $sellerId ?>">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa-duotone fa-circle-check me-1"></i> Unban Seller
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->

            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Quick Stats</h4></div>
                <div class="card-body">
                    <?php foreach ([
                        ['Listed accounts', $listedCount],
                        ['Items listed', $itemCount ?? 0],
                        ['Top Ups listed', $topupCount ?? 0],
                        ['Sold total', $totalSoldCount ?? $soldCount],
                        ['Payout requests', count($payouts ?? [])],
                        ['Payment entries', $paymentCount],
                    ] as [$label, $val]): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted"><?= $label ?></span>
                            <span class="fw-semibold"><?= $val ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($applicationNote !== ''): ?>
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Application Note</h4></div>
                <div class="card-body">
                    <div class="text-muted small" style="white-space:pre-wrap;">
                        <?= htmlspecialchars($applicationNote, ENT_QUOTES) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right column: Settings form -->
    <div class="col-lg-8">
        <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="admin_update_seller">
            <input type="hidden" name="id" value="<?= $sellerId ?>">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Account Settings</h4></div>
                <div class="card-body">
                    <div class="form-section-title">Identity</div>
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="username" value="<?= $username ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" name="email" value="<?= $email ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Discord</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="discord"
                                   value="<?= $discord ?>" placeholder="discordname or username">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">New Password</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="password"
                                   placeholder="Leave empty to not change">
                            <div class="form-text">Only this field saves the exact password you type.</div>
                        </div>
                    </div>

                    <div class="form-section-title mt-2">Access &amp; Billing</div>
                    <?php
                      $onboardingStatus2 = strtolower(trim((string)($data['onboarding_status'] ?? 'pending')));
                      $isApproved2 = ($onboardingStatus2 === 'approved');
                      $isActive2   = (int)($data['is_active'] ?? 0);
                      $isBanned2   = (int)($data['is_banned'] ?? 0);
                      if ($isBanned2)                             $statusHelp2 = 'Seller is banned and cannot access the seller area.';
                      elseif ($isApproved2 && $isActive2)         $statusHelp2 = 'Seller is approved and can log in normally.';
                      elseif ($isApproved2 && !$isActive2)        $statusHelp2 = 'Seller is approved but currently deactivated.';
                      else                                        $statusHelp2 = 'Seller cannot log in until the application is approved.';
                    ?>
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label form-label">Status</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="status_state">
                                <option value="pending"  <?= $onboardingStatus2 !== 'approved' && !$isBanned2 ? 'selected' : '' ?>>Pending Approval</option>
                                <option value="active"   <?= $isApproved2 && $isActive2 && !$isBanned2 ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $isApproved2 && !$isActive2 && !$isBanned2 ? 'selected' : '' ?>>Inactive</option>
                                <option value="banned"   <?= $isBanned2 ? 'selected' : '' ?>>Banned / Disabled</option>
                            </select>
                            <div class="form-text"><?= htmlspecialchars($statusHelp2, ENT_QUOTES) ?></div>
                        </div>
                    </div>
                    <div class="row mb-0">
                        <label class="col-sm-3 col-form-label form-label">
                            Fee percent
                            <div class="text-muted small">Leave empty = default (<?= (float)$default_fee ?>%)</div>
                        </label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                   name="fee_percent"
                                   value="<?= ($fee !== null && $fee !== '') ? htmlspecialchars((string)$fee, ENT_QUOTES) : '' ?>"
                                   placeholder="<?= (float)$default_fee ?>">
                        </div>
                    </div>
                    <div class="row mb-4 mt-4" id="allowedGamesRow">
                        <label class="col-sm-3 col-form-label form-label">
                            Allowed Games
                            <div class="text-muted small">Empty = all games</div>
                        </label>
                        <div class="col-sm-9">
                            <?php
                            $_allowedRaw  = trim((string)($data['allowed_games'] ?? ''));
                            $_allowedList = $_allowedRaw !== '' ? array_filter(array_map('trim', explode(',', $_allowedRaw))) : [];
                            $_allGames    = function_exists('util_get_all_games') ? util_get_all_games(true) : [];
                            $_slugToShort = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft'];
                            $_iconMap     = ['lol'=>'league-of-legends.png','val'=>'valorant.png','tft'=>'teamfight-tactics.png'];
                            ?>
                            <!-- Custom multi-select dropdown -->
                            <div class="ag-dropdown" id="agDropdown">
                                <div class="ag-dropdown__trigger form-control d-flex flex-wrap gap-1 align-items-center"
                                     id="agTrigger" style="min-height:38px;cursor:pointer;">
                                    <span class="ag-dropdown__placeholder text-muted" id="agPlaceholder"
                                          style="<?= empty($_allowedList) ? '' : 'display:none' ?>">
                                        All games allowed
                                    </span>
                                    <!-- Selected tags rendered here by JS -->
                                </div>
                                <div class="ag-dropdown__menu shadow border rounded mt-1 p-2"
                                     id="agMenu" style="display:none;position:absolute;z-index:999;background:#fff;min-width:260px;max-height:260px;overflow-y:auto;">
                                    <input type="text" class="form-control form-control-sm mb-2"
                                           id="agSearch" placeholder="Search games…">
                                    <div id="agOptions">
                                        <?php foreach ($_allGames as $_ag):
                                            $_ags  = $_slugToShort[$_ag['slug']] ?? $_ag['slug'];
                                            $_icon = !empty($_ag['icon']) ? $_ag['icon']
                                                   : (ASSET_URL . '/website/images/icons/' . ($_iconMap[$_ags] ?? $_ag['slug'] . '.png'));
                                            $_sel  = !empty($_allowedList) &&
                                                     (in_array($_ag['slug'], $_allowedList, true) || in_array($_ags, $_allowedList, true));
                                        ?>
                                        <div class="ag-option d-flex align-items-center gap-2 px-2 py-1 rounded"
                                             data-value="<?= htmlspecialchars($_ag['slug']) ?>"
                                             data-label="<?= htmlspecialchars($_ag['name'] ?? $_ag['slug']) ?>"
                                             data-icon="<?= htmlspecialchars($_icon) ?>"
                                             data-selected="<?= $_sel ? '1' : '0' ?>"
                                             style="cursor:pointer;">
                                            <img src="<?= htmlspecialchars($_icon) ?>" alt="" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                            <span><?= htmlspecialchars($_ag['name'] ?? $_ag['slug']) ?></span>
                                            <i class="fa-solid fa-check ms-auto text-primary ag-check" style="<?= $_sel ? '' : 'display:none' ?>"></i>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <!-- Hidden inputs submitted with form -->
                                <div id="agHiddenInputs">
                                    <?php foreach ($_allowedList as $_al): ?>
                                    <input type="hidden" name="allowed_games[]" value="<?= htmlspecialchars($_al) ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-text">Select games this seller can list. Leave empty for no restrictions.</div>
                        </div>
                    </div>

                    <div class="form-section-title mt-4">Permissions</div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label form-label">
                            Digital Goods
                            <div class="text-muted small">Allow seller to list digital goods (subscriptions, software, etc.)</div>
                        </label>
                        <div class="col-sm-9 d-flex align-items-center">
                            <?php $_canDg = (int)($data['can_list_digital_goods'] ?? 0); ?>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       name="can_list_digital_goods" id="canListDg"
                                       value="1" <?= $_canDg ? 'checked' : '' ?>>
                                <label class="form-check-label" for="canListDg">
                                    <?= $_canDg ? '<span class="text-success fw-semibold">Enabled</span>' : '<span class="text-muted">Disabled</span>' ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-0 mt-4">
                        <label class="col-sm-3 col-form-label form-label">
                            Link Client Account
                            <div class="text-muted small">Optional — leave empty to unlink</div>
                        </label>
                        <div class="col-sm-9">
                            <input type="number" min="1" class="form-control" name="client_id"
                                   value="<?= $clientId > 0 ? $clientId : '' ?>"
                                   placeholder="Client ID (e.g. 42)">
                            <?php if ($clientId > 0): ?>
                                <div class="form-text">
                                    Currently linked to <a href="<?= ADMN_URL ?>/client/<?= $clientId ?>" target="_blank">Client #<?= $clientId ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-duotone fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Must remain outside the seller edit form: nested forms submit the wrong action. -->
<form id="profileSellerResendForm" class="ajax-form d-none" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_resend_seller_login">
    <input type="hidden" name="id" value="<?= $sellerId ?>">
</form>

<!-- Resend password modal -->
<div class="modal fade" id="profileSellerResendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content card border-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-header-title mb-0">Send a new password?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="card-body">
                <p class="mb-0">This will generate a new password and resend the login email. The old password will stop working.</p>
            </div>
            <div class="card-footer border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmProfileSellerResend" class="btn btn-primary">
                    <i class="fa-duotone fa-paper-plane me-1"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    function ready(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
    ready(function () {
        var openBtn    = document.getElementById('openProfileSellerResendModal');
        var form       = document.getElementById('profileSellerResendForm');
        var modalEl    = document.getElementById('profileSellerResendModal');
        var confirmBtn = document.getElementById('confirmProfileSellerResend');
        if (!openBtn || !form || !modalEl || !confirmBtn || typeof bootstrap === 'undefined') return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        openBtn.addEventListener('click', function (e) { e.preventDefault(); modal.show(); });
        confirmBtn.addEventListener('click', function () {
            modal.hide();
            if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
        });
    });
})();
</script>
<?= $this->end() ?>

<?= $this->start('styles') ?>
<style>
.ag-dropdown { position: relative; }
.ag-dropdown__trigger {
    cursor: pointer;
    background: #1e2028;
    border-color: rgba(255,255,255,.12);
}
.ag-dropdown__trigger:focus-within {
    border-color: rgba(255,255,255,.25);
    box-shadow: none;
}
.ag-tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 6px; padding: 2px 8px;
    font-size: .78rem; color: rgba(255,255,255,.75); font-weight: 600;
}
.ag-tag img { width: 13px; height: 13px; object-fit: contain; }
.ag-tag__remove { cursor: pointer; opacity: .45; line-height: 1; }
.ag-tag__remove:hover { opacity: .9; }
.ag-dropdown__menu {
    background: #1e2028 !important;
    border-color: rgba(255,255,255,.12) !important;
}
.ag-dropdown__menu input.form-control {
    background: rgba(255,255,255,.06);
    border-color: rgba(255,255,255,.12);
    color: rgba(255,255,255,.8);
}
.ag-dropdown__menu input.form-control::placeholder { color: rgba(255,255,255,.3); }
.ag-option { color: rgba(255,255,255,.75); border-radius: 5px; }
.ag-option:hover { background: rgba(255,255,255,.06) !important; }
.ag-option[data-selected="1"] { background: rgba(255,255,255,.05) !important; }
.ag-check { color: rgba(255,255,255,.5) !important; }
</style>
<?= $this->stop() ?>

<?= $this->start('scripts') ?>
<script>
(function () {
    var dropdown = document.getElementById('agDropdown');
    if (!dropdown) return;

    var trigger   = document.getElementById('agTrigger');
    var menu      = document.getElementById('agMenu');
    var search    = document.getElementById('agSearch');
    var placeholder = document.getElementById('agPlaceholder');
    var hiddenDiv = document.getElementById('agHiddenInputs');
    var options   = Array.from(document.querySelectorAll('#agOptions .ag-option'));

    // ── Open / Close ──────────────────────────────────────────────────────
    trigger.addEventListener('click', function (e) {
        if (e.target.closest('.ag-tag__remove')) return;
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        if (menu.style.display === 'block') setTimeout(function(){ search.focus(); }, 50);
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) menu.style.display = 'none';
    });

    // ── Search ────────────────────────────────────────────────────────────
    search.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        options.forEach(function (opt) {
            opt.style.display = opt.dataset.label.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // ── Select / Deselect ─────────────────────────────────────────────────
    function renderTags() {
        // Remove old tags
        trigger.querySelectorAll('.ag-tag').forEach(function(t){ t.remove(); });

        var selected = options.filter(function(o){ return o.dataset.selected === '1'; });
        placeholder.style.display = selected.length === 0 ? '' : 'none';

        selected.forEach(function (opt) {
            var tag = document.createElement('span');
            tag.className = 'ag-tag';
            tag.innerHTML =
                '<img src="' + opt.dataset.icon + '" alt="">' +
                '<span>' + opt.dataset.label + '</span>' +
                '<span class="ag-tag__remove" data-val="' + opt.dataset.value + '">×</span>';
            trigger.appendChild(tag);
        });

        // Rebuild hidden inputs
        hiddenDiv.innerHTML = '';
        selected.forEach(function (opt) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'allowed_games[]';
            inp.value = opt.dataset.value;
            hiddenDiv.appendChild(inp);
        });
    }

    options.forEach(function (opt) {
        opt.addEventListener('click', function () {
            opt.dataset.selected = opt.dataset.selected === '1' ? '0' : '1';
            opt.querySelector('.ag-check').style.display = opt.dataset.selected === '1' ? '' : 'none';
            opt.style.background = opt.dataset.selected === '1' ? 'rgba(55,125,255,.08)' : '';
            renderTags();
        });
    });

    // Remove tag by clicking ×
    trigger.addEventListener('click', function (e) {
        var btn = e.target.closest('.ag-tag__remove');
        if (!btn) return;
        var val = btn.dataset.val;
        var opt = options.find(function(o){ return o.dataset.value === val; });
        if (opt) {
            opt.dataset.selected = '0';
            opt.querySelector('.ag-check').style.display = 'none';
        }
        renderTags();
    });

    // Initial render
    renderTags();
})();
</script>
<?= $this->stop() ?>
