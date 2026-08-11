<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Payments List - Booster Area | LoLBoost.gg', 'h1' => 'Booster Payments List', 'description' => 'View Your Payments History.']]) ?>

<?php
$totalEarned    = 0;
$totalWithdrawn = 0;
$typeMap        = [];

foreach ($data as $row) {
    $amt = (int)($row['amount'] ?? 0);
    if ($amt > 0) $totalEarned    += $amt;
    else          $totalWithdrawn += $amt;

    $typeKey = (string)($row['type'] ?? '');
    if ($typeKey !== '' && !isset($typeMap[$typeKey])) {
        $typeMap[$typeKey] = strip_tags(util_format_default_type($typeKey));
    }
}
$txCount = count($data);
?>

<style>
  /* ── Stat cards ── */
  .pay-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 22px; }
  @media (max-width: 640px) { .pay-stats { grid-template-columns: 1fr; } }

  /* ── Filter pills ── */
  .pay-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 13px; border-radius: 8px;
    font-size: 13px; font-weight: 500;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.5);
    cursor: pointer; transition: all .13s;
    white-space: nowrap;
  }
  .pay-pill:hover {
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.82);
    border-color: rgba(255,255,255,.16);
  }
  .pay-pill.active {
    background: rgba(109,94,252,.18);
    border-color: rgba(109,94,252,.42);
    color: rgba(255,255,255,.92);
    font-weight: 600;
  }
  .pay-pill-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 18px; padding: 0 5px;
    border-radius: 5px; font-size: 11px; font-weight: 700;
    background: rgba(255,255,255,.08); color: rgba(255,255,255,.5);
  }
  .pay-pill.active .pay-pill-count {
    background: rgba(109,94,252,.32); color: rgba(255,255,255,.92);
  }
</style>

<!-- Stat Cards -->
<div class="pay-stats">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <span class="text-success me-2"><i class="fa-solid fa-arrow-down"></i></span>
                <span class="text-uppercase text-muted small fw-600" style="letter-spacing:.06em;">Total Earned</span>
            </div>
            <div class="h3 mb-0 text-success fw-700">+<?= number_format($totalEarned / 100, 2) ?> €</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <span class="text-danger me-2"><i class="fa-solid fa-arrow-up"></i></span>
                <span class="text-uppercase text-muted small fw-600" style="letter-spacing:.06em;">Total Withdrawn</span>
            </div>
            <div class="h3 mb-0 text-danger fw-700"><?= number_format($totalWithdrawn / 100, 2) ?> €</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <span class="text-muted me-2"><i class="fa-solid fa-list"></i></span>
                <span class="text-uppercase text-muted small fw-600" style="letter-spacing:.06em;">Transactions</span>
            </div>
            <div class="h3 mb-0 fw-700"><?= $txCount ?></div>
        </div>
    </div>
</div>

<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Payments History</h5>
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
                            placeholder="Search payments" aria-label="Search payments">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
        </div>

        <!-- Filter pills -->
        <div class="mt-3 d-flex align-items-center flex-wrap gap-2" id="payTypeFilters">
            <button class="pay-pill active" data-type="">
                All <span class="pay-pill-count"><?= $txCount ?></span>
            </button>
            <?php foreach ($typeMap as $typeKey => $typeLabel):
                $cnt = count(array_filter($data, fn($r) => ($r['type'] ?? '') === $typeKey));
            ?>
            <button class="pay-pill" data-type="<?= esc($typeKey) ?>">
                <?= esc($typeLabel) ?> <span class="pay-pill-count"><?= $cnt ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- End Header -->

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
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
                 }' id="payments_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Sender</th>
                    <th>Note</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Created At</th>
                    <th style="display:none;">TypeKey</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td class="fw-500">
                            #<?= $row['id'] ?>
                        </td>
                        <td class="fw-500">
                            <?= util_format_default_type($row['type']) ?>
                        </td>
                        <td class="fw-500">
                            <?= empty($row['sender']) ? 'System' : util_format_user($row['sender']['username'], $row['sender']['icon']) ?>
                        </td>
                        <td class="fw-500">
                            <?= $row['note'] ?>
                        </td>
                        <td class="fw-500 text-end <?= $row['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                            <?= util_format_price_display($row['amount']) . " " . $row['currency'] ?>
                        </td>
                        <td class="fw-500 text-end" data-order="<?= $row['created_at'] ?>">
                            <?= util_format_date_display($row['created_at']) ?></td>
                        <td style="display:none;"><?= esc($row['type'] ?? '') ?></td>
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

<?= $this->start('scripts') ?>
<script>
    $(document).on('ready', function () {
        // INITIALIZATION OF DATATABLES
        // =======================================================
        HSCore.components.HSDatatables.init($('#payments_table'), {
            columnDefs: [
                { targets: 6, visible: false, searchable: true }
            ],
            language: {
                zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
            <p class="mb-0">No data to show</p>
            </div>`
            }
        });

        // ── Type filter pills ──────────────────────────────────
        const dtApi = $('#payments_table').DataTable();

        $('#payTypeFilters').on('click', '.pay-pill', function () {
            const $pill = $(this);
            const type  = $pill.data('type'); // empty string = All

            // Reset all pills
            $('#payTypeFilters .pay-pill').removeClass('active');

            // Activate clicked
            $pill.addClass('active');

            // Filter on hidden TypeKey column (index 6)
            if (type === '' || type === undefined) {
                dtApi.column(6).search('').draw();
            } else {
                dtApi.column(6).search('^' + $.fn.dataTable.util.escapeRegex(type) + '$', true, false).draw();
            }
        });
    });
</script>
<?= $this->end() ?>
