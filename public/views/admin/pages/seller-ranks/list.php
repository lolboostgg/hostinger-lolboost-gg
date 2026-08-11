<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Ranks - Admin Area | LoLBoost.gg', 'h1' => 'Seller Ranks', 'description' => 'Manage seller ranks.']]) ?>

<?php
$rows = is_array($data ?? null) ? $data : [];
$rankColors = [
    'text-slate-400'   => '#94a3b8',
    'text-emerald-500' => '#10b981',
    'text-violet-500'  => '#8b5cf6',
    'text-amber-400'   => '#fbbf24',
    'text-sky-400'     => '#38bdf8',
    'text-rose-500'    => '#f43f5e',
    'text-orange-500'  => '#f97316',
];

foreach ($rows as $k => $row) {
    $rows[$k]['icon_class'] = trim((string)($row['icon_class'] ?? 'fa-solid fa-badge-check')) ?: 'fa-solid fa-badge-check';
    $rows[$k]['icon_color'] = trim((string)($row['icon_color'] ?? 'text-slate-400')) ?: 'text-slate-400';
    $rows[$k]['seller_count'] = (int)($row['seller_count'] ?? $row['sellers'] ?? 0);
    $rows[$k]['status'] = isset($row['status']) ? (int)$row['status'] : 1;
}
?>

<?php $renderSellerRankFields = function ($prefix = 'add') { ?>
    <div class="row mb-4">
        <label for="<?= $prefix ?>NameLabel" class="col-sm-4 col-form-label form-label">Name</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="name" id="<?= $prefix ?>NameLabel" placeholder="Expert Seller" aria-label="Name">
        </div>
    </div>

    <div class="row mb-4">
        <label for="<?= $prefix ?>MinSalesLabel" class="col-sm-4 col-form-label form-label">Min. Sales</label>
        <div class="col-sm-8">
            <input type="number" class="form-control" name="min_sales" id="<?= $prefix ?>MinSalesLabel" placeholder="20" aria-label="Min. Sales">
        </div>
    </div>

    <div class="row mb-4">
        <label for="<?= $prefix ?>FeePercentLabel" class="col-sm-4 col-form-label form-label">Fee %</label>
        <div class="col-sm-8">
            <input type="number" step="0.01" class="form-control" name="fee_percent" id="<?= $prefix ?>FeePercentLabel" placeholder="12.00" aria-label="Fee Percentage">
        </div>
    </div>

    <div class="row mb-4">
        <label for="<?= $prefix ?>IconClassLabel" class="col-sm-4 col-form-label form-label">Icon Class</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="icon_class" id="<?= $prefix ?>IconClassLabel" value="fa-solid fa-badge-check" placeholder="fa-solid fa-badge-check" aria-label="Icon Class">
            <small class="text-body">Use Font Awesome class, e.g. <code>fa-solid fa-badge-check</code></small>
        </div>
    </div>

    <div class="row mb-4">
        <label for="<?= $prefix ?>IconColorLabel" class="col-sm-4 col-form-label form-label">Icon Color</label>
        <div class="col-sm-8">
            <select class="form-select" name="icon_color" id="<?= $prefix ?>IconColorLabel">
                <option value="text-slate-400">Gray</option>
                <option value="text-emerald-500">Green</option>
                <option value="text-violet-500">Violet</option>
                <option value="text-amber-400">Amber</option>
                <option value="text-sky-400">Sky</option>
                <option value="text-rose-500">Rose</option>
                <option value="text-orange-500">Orange</option>
            </select>
        </div>
    </div>

    <div class="row mb-4">
        <label for="<?= $prefix ?>SortOrderLabel" class="col-sm-4 col-form-label form-label">Sort Order</label>
        <div class="col-sm-8">
            <input type="number" class="form-control" name="sort_order" id="<?= $prefix ?>SortOrderLabel" placeholder="1" aria-label="Sort Order">
        </div>
    </div>

    <div class="row mb-2">
        <label for="<?= $prefix ?>StatusLabel" class="col-sm-4 col-form-label form-label">Status</label>
        <div class="col-sm-8">
            <select class="form-select" name="status" id="<?= $prefix ?>StatusLabel">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>
<?php }; ?>

<div class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1 g-3">
            <div class="col-12 col-md">
                <div>
                    <h5 class="card-header-title mb-1">Seller Ranks</h5>
                    <span class="text-body fs-6">Dynamic seller badges with automatic fee and rank updates based on total sales.</span>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
                    <form>
                        <div class="input-group input-group-merge input-group-flush">
                            <div class="input-group-prepend input-group-text">
                                <i class="fa-duotone fa-search"></i>
                            </div>
                            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search Seller Ranks" aria-label="Search Seller Ranks">
                        </div>
                    </form>

                    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addSellerRankOffcanvas" aria-controls="addSellerRankOffcanvas">
                        <i class="fa-duotone fa-plus me-1"></i> Add Rank
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
                "columnDefs": [{"targets": [6], "orderable": false}],
                "info": {"totalQty": "#datatableEntriesInfoTotalQty"},
                "entries": "#datatableEntries",
                "search": "#datatableWithSearchInput",
                "isResponsive": false,
                "isShowPaging": false,
                "pagination": "datatableWithSearchPagination"
            }' id="seller_ranks_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Rank</th>
                    <th class="text-center">Min. Sales</th>
                    <th class="text-center">Fee</th>
                    <th class="text-center">Assigned Sellers</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <?php $previewHex = $rankColors[$row['icon_color']] ?? '#94a3b8'; ?>
                    <tr>
                        <td class="fw-500"><?= (int)($row['id'] ?? ++$index) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm avatar-soft-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="background: <?= $previewHex ?>22; color: <?= $previewHex ?>; width: 2.2rem; height: 2.2rem;">
                                    <i class="<?= htmlspecialchars($row['icon_class']) ?>"></i>
                                </span>
                                <div>
                                    <div class="fw-600"><?= htmlspecialchars($row['name'] ?? '-') ?></div>
                                    <small class="text-body"><?= htmlspecialchars($row['icon_class']) ?> · <?= htmlspecialchars($row['icon_color']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-500 text-center"><?= (int)($row['min_sales'] ?? 0) ?></td>
                        <td class="fw-500 text-center"><?= number_format((float)($row['fee_percent'] ?? 0), 2) ?>%</td>
                        <td class="fw-500 text-center"><?= (int)$row['seller_count'] ?></td>
                        <td class="text-center">
                            <?php if ((int)$row['status'] === 1): ?>
                                <span class="badge bg-soft-success text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-white btn-sm js-edit-seller-rank"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#editSellerRankOffcanvas"
                                    data-id="<?= (int)($row['id'] ?? 0) ?>"
                                    data-name="<?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES) ?>"
                                    data-min-sales="<?= (int)($row['min_sales'] ?? 0) ?>"
                                    data-icon-class="<?= htmlspecialchars($row['icon_class'] ?? 'fa-solid fa-badge-check', ENT_QUOTES) ?>"
                                    data-icon-color="<?= htmlspecialchars($row['icon_color'] ?? 'text-slate-400', ENT_QUOTES) ?>"
                                    data-fee-percent="<?= htmlspecialchars((string)($row['fee_percent'] ?? '0.00'), ENT_QUOTES) ?>"
                                    data-sort-order="<?= (int)($row['sort_order'] ?? 0) ?>"
                                    data-status="<?= (int)($row['status'] ?? 1) ?>">
                                    <i class="fa-duotone fa-pen-to-square me-1"></i> Edit
                                </button>
                                <form class="ajax-form d-inline-block" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Delete this seller rank?');">
                                    <input type="hidden" name="action" value="admin_delete_seller_rank">
                                    <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">
                                    <button type="submit" class="btn btn-white btn-sm text-danger">
                                        <i class="fa-duotone fa-trash-can me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>

            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end gap-2">
                    <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                        <input type="hidden" name="action" value="admin_recalculate_seller_ranks">
                        <button type="submit" class="btn btn-white btn-sm">
                            <i class="fa-duotone fa-rotate me-1"></i> Recalculate Seller Ranks
                        </button>
                    </form>
                    <nav id="datatableWithSearchPagination" aria-label="Seller ranks pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="addSellerRankOffcanvas" aria-labelledby="addSellerRankOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="addSellerRankOffcanvasLabel">Add Seller Rank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="admin_add_seller_rank">
            <?php $renderSellerRankFields('add'); ?>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">Add Seller Rank</span>
                    <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                    <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="editSellerRankOffcanvas" aria-labelledby="editSellerRankOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="editSellerRankOffcanvasLabel">Edit Seller Rank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="admin_update_seller_rank">
            <input type="hidden" name="id" id="editSellerRankId" value="">
            <?php $renderSellerRankFields('edit'); ?>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">Update Seller Rank</span>
                    <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                    <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
    $(document).on('ready', function () {
        HSCore.components.HSDatatables.init($('#seller_ranks_table'), {
            language: {
                zeroRecords: `<div class="text-center p-4">
                    <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
                    <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
                    <p class="mb-0">No seller ranks found</p>
                </div>`
            }
        });

        $(document).on('click', '.js-edit-seller-rank', function () {
            const btn = $(this);
            const offcanvas = $('#editSellerRankOffcanvas');

            $('#editSellerRankId').val(btn.data('id'));
            offcanvas.find('[name="name"]').val(btn.data('name'));
            offcanvas.find('[name="min_sales"]').val(btn.data('min-sales'));
            offcanvas.find('[name="icon_class"]').val(btn.data('icon-class'));
            offcanvas.find('[name="icon_color"]').val(btn.data('icon-color')).trigger('change');
            offcanvas.find('[name="fee_percent"]').val(btn.data('fee-percent'));
            offcanvas.find('[name="sort_order"]').val(btn.data('sort-order'));
            offcanvas.find('[name="status"]').val(btn.data('status')).trigger('change');
        });
    });
</script>
<?= $this->end() ?>
