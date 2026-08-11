<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Boosters Applications - Admin Area | LoLBoost.gg', 'h1' => 'Boosters Applications', 'description' => 'View the Boosters Applications.']]) ?>
<style>
/* Wider booster admin pages: reduce the large left/right gutters on desktop. */
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


<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Boosters Applications</h5>

                    <button type="button" class="btn btn-primary btn-sm" id="generateOnboardingLinkBtn">
                        Generate Onboarding Link
                    </button>
                </div>
            </div>

            <div class="col-auto">
                <!-- Filter -->
                <form>
                    <!-- Search -->
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                            placeholder="Search Applications" aria-label="Search Applications">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
            <div class="col-auto">

            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
                    "columnDefs": [{
                        "targets": [6],
                        "orderable": false
                    }],
                   "order": [
                        [5, "desc"]
                    ],
                   "info": {
                     "totalQty": "#datatableEntriesInfoTotalQty"
                   },
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }' id="boosters_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Country</th>
                    <th>Address</th>
                    <th>Application Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td class="fw-500">
                            <a class="" href="<?= ADMN_URL ?>/booster/<?= $row['booster_id'] ?>">
                                #<?= $row['booster_id'] ?>
                            </a>
                        </td>
                        <td class="fw-500">
                            <?= $row['fullname'] ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['username'] ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['country'] ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['address'] ?>
                        </td>
                        <td class="fw-500">
                            <?= util_format_date_display($row['created_at']) ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= ADMN_URL ?>/booster/<?= $row['booster_id'] ?>" class="btn btn-white btn-sm">
                                <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <!-- End Table -->

    <!-- Footer -->
    <div class="card-footer">
        <!-- Pagination -->
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>

                    <!-- Select -->
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                            autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                        </select>
                    </div>
                    <!-- End Select -->

                    <span class="text-secondary me-2">of</span>

                    <!-- Pagination Quantity -->
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>

            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <!-- Pagination -->
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
        <!-- End Pagination -->
    </div>
    <!-- End Footer -->
</div>
<!-- End Card -->


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

    $('#copyOnboardingLinkBtn').off('click').on('click', function () {
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

    $('#generateOnboardingLinkBtn').off('click').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-duotone fa-spinner fa-spin me-1"></i> Generating...');

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: {
                action: 'get_onboarding_url'
            },
            success: function (response) {
                btn.prop('disabled', false).html('Generate Onboarding Link');

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
                btn.prop('disabled', false).html('Generate Onboarding Link');
                create_toast('danger', 'Error', 'An error occurred.');
            }
        });
    });
});
</script>
<?= $this->end() ?>
