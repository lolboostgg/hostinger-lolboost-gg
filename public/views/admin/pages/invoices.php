<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Transactions List - Admin Area | LoLBoost.gg', 'h1' => 'Transactions List', 'description' => 'View the Transactions List.']]) ?>
<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<?= $this->end() ?>

<!-- Card -->

<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Invoices List</h5>
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
                        <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search invoices" aria-label="Search invoices">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
            <div class="col-auto">
                <!-- Select -->
                <div class="tom-select-custom">
                    <select class="js-select js-datatable-filter form-select form-select-sm form-select-borderless" autocomplete="off" data-target-column-index="3" data-target-table="invoices_table" data-hs-tom-select-options='{
            "searchInDropdown": false,
            "hideSearch": true,
            "dropdownWidth": "10rem"
          }'>
                        <option value="null" selected>Any</option>
                        <option value="Paid">Paid</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>
                <!-- End Select -->
            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                   "order": [
                        [0, "desc"]
                    ],
                   "info": {
                     "totalQty": "#datatableEntriesInfoTotalQty"
                   },
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }' id="invoices_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Description</th>
                    <th class="text-end">Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td class="fw-500">
                            #<?= $row['id'] ?>
                        </td>
                        <td class="fw-500">
                            <a href="<?= ADMN_URL ?>/client/<?= $row['client_id'] ?>">
                                <?= $row['username'] ?>
                            </a>
                        </td>
                        <td class="fw-500">
                            <?= util_format_price_display($row['price']) . " " . $row['currency'] ?>
                        </td>
                        <td class="fw-500">
                            <?= util_format_tx_status($row['status']) ?>
                        </td>
                        <td class="fw-500 text-end">
                            <?= $row['description']  ?>
                        </td>
                        <td class="fw-500 text-end" data-order="<?= $row['created_at'] ?>"><?= util_format_date_display($row['created_at']) ?></td>
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
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
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
<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
    $(document).on('ready', function() {
        // INITIALIZATION OF DATATABLES
        // =======================================================

        HSCore.components.HSTomSelect.init('.js-select');

        HSCore.components.HSDatatables.init($('#invoices_table'), {
            language: {
                zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
            <p class="mb-0">No data to show</p>
            </div>`
            }
        });

        document.querySelectorAll('.js-datatable-filter').forEach(function(item) {
            item.addEventListener('change', function(e) {
                const elVal = e.target.value,
                    targetColumnIndex = e.target.getAttribute('data-target-column-index'),
                    targetTable = e.target.getAttribute('data-target-table');

                HSCore.components.HSDatatables.getItem(targetTable).column(targetColumnIndex).search(elVal !== 'null' ? elVal : '').draw()
            })
        })


    });
</script>
<?= $this->end() ?>