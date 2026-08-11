<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Applications - Admin Area | LoLBoost.gg', 'h1' => 'Seller Applications', 'description' => 'Review and approve seller onboarding applications.']]) ?>

<?php
$pending  = $pending ?? [];
$rejected = $rejected ?? [];
?>

<?php if (!empty($pending)): ?>
<div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-clock fs-4"></i>
    <div><strong><?= count($pending) ?></strong> pending seller application<?= count($pending) > 1 ? 's' : '' ?> awaiting review.</div>
</div>
<?php else: ?>
<div class="alert alert-soft-success d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-circle-check fs-4"></i>
    <div>No pending applications — all caught up!</div>
</div>
<?php endif; ?>

<div class="card mb-5">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-header-title">Pending Applications</h5>
        <button type="button" class="btn btn-sm btn-primary" id="generateSellerOnboardingLinkBtn">
            <i class="fa-duotone fa-link me-1"></i> Generate Onboarding Link
        </button>
    </div>

    <?php if (!empty($pending)): ?>
    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Documents</th>
                    <th>Applied</th>
                    <th class="text-end" style="min-width:220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $r): ?>
                <tr id="row-<?= (int)$r['id'] ?>">
                    <td class="text-muted">#<?= (int)$r['id'] ?></td>
                    <td>
                        <span class="fw-600"><?= htmlspecialchars($r['username'] ?? '') ?></span>
                    </td>
                    <td><?= htmlspecialchars($r['fullname'] ?? '—') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($r['email'] ?? '') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($r['country'] ?? '—') ?></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if (!empty($r['id_front'])): ?>
                                <a href="<?= htmlspecialchars($r['id_front']) ?>" target="_blank" class="btn btn-xs btn-white" title="ID Front">
                                    <i class="fa-duotone fa-id-card me-1"></i> Front
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($r['id_back'])): ?>
                                <a href="<?= htmlspecialchars($r['id_back']) ?>" target="_blank" class="btn btn-xs btn-white" title="ID Back">
                                    <i class="fa-duotone fa-id-card me-1"></i> Back
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($r['selfie'])): ?>
                                <a href="<?= htmlspecialchars($r['selfie']) ?>" target="_blank" class="btn btn-xs btn-white" title="Selfie">
                                    <i class="fa-duotone fa-camera me-1"></i> Selfie
                                </a>
                            <?php endif; ?>
                            <?php if (empty($r['id_front']) && empty($r['id_back']) && empty($r['selfie'])): ?>
                                <span class="text-muted small">No docs</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-muted"><?= date('d.m.Y H:i', strtotime($r['created_at'] ?? 'now')) ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-2 align-items-center justify-content-end">
                            <button type="button" class="btn btn-sm btn-white"
                                data-bs-toggle="modal" data-bs-target="#detailsModal"
                                data-id="<?= (int)$r['id'] ?>"
                                data-username="<?= htmlspecialchars($r['username'] ?? '', ENT_QUOTES) ?>"
                                data-fullname="<?= htmlspecialchars($r['fullname'] ?? '', ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES) ?>"
                                data-dob="<?= htmlspecialchars($r['dob'] ?? '', ENT_QUOTES) ?>"
                                data-address="<?= htmlspecialchars($r['address'] ?? '', ENT_QUOTES) ?>"
                                data-country="<?= htmlspecialchars($r['country'] ?? '', ENT_QUOTES) ?>"
                                data-idfront="<?= htmlspecialchars($r['id_front'] ?? '', ENT_QUOTES) ?>"
                                data-idback="<?= htmlspecialchars($r['id_back'] ?? '', ENT_QUOTES) ?>"
                                data-selfie="<?= htmlspecialchars($r['selfie'] ?? '', ENT_QUOTES) ?>">
                                <i class="fa-duotone fa-eye me-1"></i> View
                            </button>
                            <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                                <input type="hidden" name="action" value="admin_approve_seller">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa-duotone fa-check me-1"></i> Approve
                                </button>
                            </form>
                            <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                                <input type="hidden" name="action" value="admin_decline_seller">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa-duotone fa-xmark me-1"></i> Decline
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card-body text-center text-muted py-5">
        <i class="fa-duotone fa-inbox fs-1 mb-3 d-block"></i>
        No pending applications. Use the <strong>Generate Onboarding Link</strong> button to invite a new seller.
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($rejected)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-header-title text-muted">Declined Applications</h5>
    </div>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{"order":[[0,"desc"]],"isShowPaging":true}'>
            <thead class="thead-light">
                <tr><th>#</th><th>Username</th><th>Email</th><th>Country</th><th>Applied</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rejected as $r): ?>
                <tr>
                    <td class="text-muted">#<?= (int)$r['id'] ?></td>
                    <td class="fw-500"><?= htmlspecialchars($r['username'] ?? '') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($r['email'] ?? '') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($r['country'] ?? '—') ?></td>
                    <td class="text-muted"><?= date('d.m.Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="row"><div class="col-auto"><nav id="datatableWithSearchPagination"></nav></div></div></div>
</div>
<?php endif; ?>

<div class="modal fade" id="onboardingLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Copy Onboarding Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Automatic copying was blocked on this device. Tap the button below to copy the link.</p>
                <input type="text" class="form-control" id="onboardingLinkField" readonly>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="copyOnboardingLinkBtn">
                    <i class="fa-duotone fa-copy me-1"></i> Copy Link
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seller Application — <span id="modalUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6"><div class="text-muted small mb-1">Full Name</div><div class="fw-600" id="modalFullname">—</div></div>
                    <div class="col-sm-6"><div class="text-muted small mb-1">Email</div><div class="fw-600" id="modalEmail">—</div></div>
                    <div class="col-sm-6"><div class="text-muted small mb-1">Date of Birth</div><div class="fw-600" id="modalDob">—</div></div>
                    <div class="col-sm-6"><div class="text-muted small mb-1">Country</div><div class="fw-600" id="modalCountry">—</div></div>
                    <div class="col-12"><div class="text-muted small mb-1">Address</div><div class="fw-600" id="modalAddress">—</div></div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small mb-2">ID Front</div>
                        <a id="modalIdFront" href="#" target="_blank" class="d-block" style="display:none!important;">
                            <img id="modalIdFrontImg" src="" alt="ID Front" class="img-fluid rounded" style="border:1px solid rgba(255,255,255,0.1);">
                        </a>
                        <span id="modalIdFrontNone" class="text-muted small">Not uploaded</span>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-2">ID Back</div>
                        <a id="modalIdBack" href="#" target="_blank" class="d-block" style="display:none!important;">
                            <img id="modalIdBackImg" src="" alt="ID Back" class="img-fluid rounded" style="border:1px solid rgba(255,255,255,0.1);">
                        </a>
                        <span id="modalIdBackNone" class="text-muted small">Not uploaded</span>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-2">Selfie</div>
                        <a id="modalSelfie" href="#" target="_blank" class="d-block" style="display:none!important;">
                            <img id="modalSelfieImg" src="" alt="Selfie" class="img-fluid rounded" style="border:1px solid rgba(255,255,255,0.1);">
                        </a>
                        <span id="modalSelfieNone" class="text-muted small">Not uploaded</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalApproveBtn"><i class="fa-duotone fa-check me-1"></i> Approve</button>
                <button type="button" class="btn btn-danger" id="modalDeclineBtn"><i class="fa-duotone fa-xmark me-1"></i> Decline</button>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {
    let latestOnboardingUrl = '';
    let modalCurrentId = null;
    const onboardingModalElement = document.getElementById('onboardingLinkModal');
    const onboardingModal = onboardingModalElement && typeof bootstrap !== 'undefined'
        ? new bootstrap.Modal(onboardingModalElement)
        : null;

    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) ||
            (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function legacyCopy(text) {
        const input = document.createElement('input');
        input.type = 'text';
        input.value = text;
        input.setAttribute('readonly', 'readonly');
        input.style.position = 'fixed';
        input.style.top = '10px';
        input.style.left = '-9999px';
        input.style.opacity = '0';

        document.body.appendChild(input);
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);

        let copied = false;

        try {
            copied = document.execCommand('copy');
        } catch (err) {
            copied = false;
        }

        document.body.removeChild(input);
        return copied;
    }

    function copyToClipboard(text) {
        if (!text) {
            return Promise.resolve(false);
        }

        if (isIOS()) {
            return Promise.resolve(legacyCopy(text));
        }

        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text)
                .then(function () {
                    return true;
                })
                .catch(function () {
                    return legacyCopy(text);
                });
        }

        return Promise.resolve(legacyCopy(text));
    }

    function showCopyModal(url) {
        latestOnboardingUrl = url;
        $('#onboardingLinkField').val(url);

        if (onboardingModal) {
            onboardingModal.show();
        } else {
            create_toast('warning', 'Copy manually', 'Automatic copying is blocked on this device.');
        }
    }

    $('#copyOnboardingLinkBtn').on('click', function () {
        const btn = $(this);
        const originalHtml = btn.html();
        const input = document.getElementById('onboardingLinkField');

        if (input) {
            input.focus();
            input.select();
            input.setSelectionRange(0, input.value.length);
        }

        btn.prop('disabled', true).html('<i class="fa-duotone fa-spinner fa-spin me-1"></i> Copying...');

        copyToClipboard(latestOnboardingUrl).then(function (copied) {
            btn.prop('disabled', false).html(originalHtml);

            if (copied) {
                create_toast('success', 'Copied!', 'Onboarding link copied to clipboard.');
                if (onboardingModal) {
                    onboardingModal.hide();
                }
                return;
            }

            create_toast('danger', 'Copy failed', 'Please tap and hold the link field to copy it manually.');
        });
    });

    $('#generateSellerOnboardingLinkBtn').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-duotone fa-spinner fa-spin me-1"></i> Generating...');

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: { action: 'get_seller_onboarding_url' },
            success: function (response) {
                btn.prop('disabled', false).html('<i class="fa-duotone fa-link me-1"></i> Generate Onboarding Link');

                let res;

                try {
                    res = typeof response === 'string' ? JSON.parse(response) : response;
                } catch (e) {
                    create_toast('danger', 'Error', 'Invalid server response.');
                    return;
                }

                const url = res && res.url ? res.url : '';

                if (!url) {
                    create_toast('danger', 'Error', 'Something went wrong.');
                    return;
                }

                latestOnboardingUrl = url;

                copyToClipboard(url).then(function (copied) {
                    if (copied) {
                        create_toast('success', 'Copied!', 'Onboarding link copied to clipboard.');
                        return;
                    }

                    showCopyModal(url);
                });
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="fa-duotone fa-link me-1"></i> Generate Onboarding Link');
                create_toast('danger', 'Error', 'An error occurred.');
            }
        });
    });

    $('#detailsModal').on('show.bs.modal', function (e) {
        const btn = $(e.relatedTarget);
        modalCurrentId = btn.data('id');
        $('#modalUsername').text(btn.data('username') || '—');
        $('#modalFullname').text(btn.data('fullname') || '—');
        $('#modalEmail').text(btn.data('email') || '—');
        $('#modalDob').text(btn.data('dob') || '—');
        $('#modalCountry').text(btn.data('country') || '—');
        $('#modalAddress').text(btn.data('address') || '—');

        function setDoc(linkId, imgId, noneId, url) {
            if (url) {
                $('#' + linkId).attr('href', url).css('display', 'block');
                $('#' + imgId).attr('src', url);
                $('#' + noneId).hide();
            } else {
                $('#' + linkId).css('display', 'none');
                $('#' + noneId).show();
            }
        }

        setDoc('modalIdFront', 'modalIdFrontImg', 'modalIdFrontNone', btn.data('idfront'));
        setDoc('modalIdBack', 'modalIdBackImg', 'modalIdBackNone', btn.data('idback'));
        setDoc('modalSelfie', 'modalSelfieImg', 'modalSelfieNone', btn.data('selfie'));
    });

    $('#modalApproveBtn').on('click', function () {
        if (!modalCurrentId) return;
        $.ajax({
            url: '<?= AJAX_URL ?>', type: 'POST',
            data: { action: 'admin_approve_seller', id: modalCurrentId },
            success: function (response) {
                const res = JSON.parse(response);
                ajax_response_handler(res);
            }
        });
        $('#detailsModal').modal('hide');
    });

    $('#modalDeclineBtn').on('click', function () {
        if (!modalCurrentId) return;
        $.ajax({
            url: '<?= AJAX_URL ?>', type: 'POST',
            data: { action: 'admin_decline_seller', id: modalCurrentId },
            success: function (response) {
                const res = JSON.parse(response);
                ajax_response_handler(res);
            }
        });
        $('#detailsModal').modal('hide');
    });
});
</script>
<?= $this->end() ?>
