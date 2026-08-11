<style>
.client-coins-card.card{border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,.07);}
.client-coins-card .card-header{padding:16px 20px;}
.client-coins-card .card-header-title{font-weight:900;}
.client-coins-card .table thead th{font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.42);}
.client-coins-card .table tbody tr{transition:background .12s ease;}
.client-coins-card .table tbody tr:hover{background:rgba(109,92,255,.06);}
.client-coins-card .coins-amount-pill{display:inline-flex;align-items:center;justify-content:center;min-width:38px;padding:4px 10px;border-radius:999px;font-weight:900;font-size:.78rem;font-variant-numeric:tabular-nums;}
.client-coins-card .coins-amount-pill.is-plus{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.24);color:#4ade80;}
.client-coins-card .coins-amount-pill.is-minus{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.24);color:#fb7185;}
.client-coins-card .input-group{border-radius:10px;}
.client-coins-card .card-footer{border-top:1px solid rgba(255,255,255,.06);}
</style>

<!-- Card -->
<div class="card client-coins-card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Coins History</h5>
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
                        <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search"
                            aria-label="Search">
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
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
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
                 }' id="history_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Reason</th>
                    <th class="text-center">Amount</th>
                    <th class="text-end">Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data['coins_history'] as $row): ?>
                    <tr>
                        <td class="fw-500">
                            #<?= $row['id'] ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['reason'] ?>
                        </td>
                        <td class="fw-500 text-center">
                            <?php
                                $coinAmount = (float)($row['amount'] ?? 0);
                                $coinType = strtolower((string)($row['type'] ?? ''));
                                $coinReason = strtolower(strip_tags((string)($row['reason'] ?? '')));

                                if ($coinType !== '') {
                                    $coinIsPlus = in_array($coinType, ['increment', 'credit', 'add', 'added', 'bonus'], true);
                                } else {
                                    $coinIsPlus = $coinAmount > 0
                                        && !preg_match('/deduct|removed|spent|debit|subtract/', $coinReason);
                                }

                                $coinAmountFormatted = number_format(abs($coinAmount), 2, ',', '.');
                                $coinAmountFormatted = rtrim(rtrim($coinAmountFormatted, '0'), ',');
                            ?>
                            <span class="coins-amount-pill <?= $coinIsPlus ? 'is-plus' : 'is-minus' ?>">
                                <?= $coinAmountFormatted ?>
                            </span>
                        </td>
                        <td class="fw-500 text-end" data-order="<?= $row['created_at'] ?>">
                            <?= util_format_date_display($row['created_at']) ?>
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