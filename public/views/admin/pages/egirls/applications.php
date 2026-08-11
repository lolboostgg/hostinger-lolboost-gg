<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'eGirl Applications , Admin Area | LoLBoost.gg', 'h1' => 'eGirl Applications', 'description' => 'View the eGirl Applications.']]) ?>

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


<?php $data = $data ?? []; ?>

<?php if (!empty($data)): ?>
<div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-clock fs-4"></i>
    <div><strong><?= count($data) ?></strong> eGirl application<?= count($data) > 1 ? 's' : '' ?> awaiting review.</div>
</div>
<?php else: ?>
<div class="alert alert-soft-success d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-circle-check fs-4"></i>
    <div>No pending applications. Use the <strong>Generate Onboarding Link</strong> button to invite a new E,Girl.</div>
</div>
<?php endif; ?>

<div class="card mb-5">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-header-title">eGirl Applications</h5>
        <button type="button" class="btn btn-sm btn-primary" id="generateOnboardingLinkBtn">
            <i class="fa-duotone fa-link me-1"></i> Generate Onboarding Link
        </button>
    </div>

    <?php if (!empty($data)): ?>
    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Full Name</th><th>Username</th><th>Country</th><th>Address</th><th>Applied</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td class="text-muted">#<?= (int)$row['booster_id'] ?></td>
                    <td class="fw-500"><?= htmlspecialchars($row['fullname'] ?? '—') ?></td>
                    <td class="fw-500"><?= htmlspecialchars($row['username'] ?? '—') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($row['country'] ?? '—') ?></td>
                    <td class="text-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars($row['address'] ?? '—') ?>
                    </td>
                    <td class="text-muted"><?= isset($row['created_at']) ? date('d.m.Y H:i', strtotime($row['created_at'])) : '—' ?></td>
                    <td class="text-end">
                        <a href="<?= ADMN_URL ?>/booster/<?= $row['booster_id'] ?>" class="btn btn-sm btn-white">
                            <i class="fa-duotone fa-eye me-1"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="card-body text-center text-muted py-5">
            <i class="fa-duotone fa-inbox fs-1 mb-3 d-block"></i>
            No pending applications. Use the <strong>Generate Onboarding Link</strong> button to invite a new E,Girl.
        </div>
    <?php endif; ?>
</div>

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

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {
    let latestOnboardingUrl = '';
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

    $('#generateOnboardingLinkBtn').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-duotone fa-spinner fa-spin me-1"></i> Generating...');

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: { action: 'get_egirl_onboarding_url' },
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
});
</script>
<?= $this->end() ?>
