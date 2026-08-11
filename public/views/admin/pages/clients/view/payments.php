
<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Payments List</h5>
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
                        <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search payments" aria-label="Search payments">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                   "order": [
                        [6, "desc"]
                    ],
                   "info": {
                     "totalQty": "#datatableEntriesInfoTotalQty"
                   },
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }' id="payments_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Processor</th>
                    <th>Invoice ID</th>
                    <th>Token</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data['payments'] as $row) : ?>
                    <tr>
                        <td class="fw-500">
                            #<?= $row['id'] ?>
                        </td>
                        <td class="fw-500">
                            <?= util_format_default_type($row['processor']) ?>
                        </td>
                        <td class="fw-500">
                            #<?= $row['invoice_id'] ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['token'] ?>
                        </td>
                        <td class="fw-500">
                            <?= util_format_tx_status($row['status']) ?>
                        </td>
                        <td class="fw-500 text-end">
                            <?= util_format_price_display($row['amount'])." ".$row['currency']  ?>
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