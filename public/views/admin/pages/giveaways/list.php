<?php
// Local fallbacks (do not require editing core config)
if (!defined('ADMN_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('ADMN_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/admin-area');
}
?>

<?= $this->layout('admin/layouts/main', ['meta' => [
    'title' => 'Giveaways - Admin Area | LoLBoost.gg',
    'h1' => 'Giveaways',
    'description' => 'Create and manage giveaway campaigns.',
]]) ?>

<div class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Giveaways</h5>
                </div>
                <div class="small text-muted mt-1">
                    One ticket per <span class="fw-600">PAID</span> invoice. If an order becomes <span class="fw-600">UNPAID</span> or <span class="fw-600">REFUNDED</span>, the ticket is revoked.
                </div>
            </div>

            <div class="col-auto">
                <form>
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                               placeholder="Search giveaways" aria-label="Search giveaways">
                    </div>
                </form>
            </div>

            <div class="col-auto">
                <a href="<?= ADMN_URL ?>/giveaways/edit" class="btn btn-primary btn-sm">
                    <i class="fa-duotone fa-plus me-1 fs-6"></i> New Giveaway
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
                "columnDefs": [{"targets":[6],"orderable":false}],
                "info": {"totalQty":"#datatableEntriesInfoTotalQty"},
                "entries":"#datatableEntries",
                "search":"#datatableWithSearchInput",
                "isResponsive": false,
                "isShowPaging": true,
                "pagination":"datatableWithSearchPagination"
            }'
            id="giveaways_table">
            <thead class="thead-light">
                <tr>
                    <th style="width:80px;">ID</th>
                    <th>Title</th>
                    <th style="width:120px;">Status</th>
                    <th style="width:200px;">Start</th>
                    <th style="width:200px;">End</th>
                    <th style="width:120px;" class="text-center">Winners</th>
                    <th style="width:170px;" class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php $giveaways = $giveaways ?? []; ?>
            <?php if (empty($giveaways)): ?>
                <tr>
                    <td colspan="7" class="text-center py-6 text-muted">
                        No giveaways yet. Click <span class="fw-600">New Giveaway</span> to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($giveaways as $g): ?>
                    <?php
                        $id = (int)($g['id'] ?? 0);
                        $status = strtoupper((string)($g['status'] ?? 'DRAFT'));
                        $badge = 'secondary';
                        if ($status === 'ACTIVE') $badge = 'success';
                        if ($status === 'DRAFT') $badge = 'warning';
                        if ($status === 'ENDED') $badge = 'info';
                        if ($status === 'DRAWN') $badge = 'primary';
                    ?>
                    <tr>
                        <td class="fw-500">#<?= $id ?></td>
                        <td>
                            <div class="fw-500"><?= htmlspecialchars((string)($g['title'] ?? '')) ?></div>
                            <?php if (!empty($g['description'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars((string)$g['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $badge ?>"><?= $status ?></span>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars((string)($g['starts_at'] ?? '-')) ?></td>
                        <td class="text-muted"><?= htmlspecialchars((string)($g['ends_at'] ?? '-')) ?></td>
                        <td class="text-center fw-500"><?= (int)($g['winners_count'] ?? 0) ?></td>
                        <td class="text-end">
                            <a href="<?= ADMN_URL ?>/giveaways/edit?id=<?= $id ?>" class="btn btn-white btn-sm">
                                <i class="fa-duotone fa-pencil me-1 fs-6"></i> Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                                autocomplete="off" data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <nav id="datatableWithSearchPagination" aria-label="Pagination"></nav>
            </div>
        </div>
    </div>
</div>
