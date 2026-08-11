<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Reviews - Admin Area | LoLBoost.gg', 'h1' => 'Seller Reviews', 'description' => 'View seller reviews.']]) ?>
<?php
/**
 * admin/pages/sellers/reviews.php
 * Variables: $data, $reviews, $page (+ everything from _shared.php)
 */
$activeTab = 'reviews';
include __DIR__ . '/_shared.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h4 class="card-header-title mb-0">
                Reviews
                <span class="badge bg-soft-secondary text-secondary ms-2"><?= (int)$reviewCount ?></span>
                <?php if (!empty($hiddenReviewCount)): ?>
                    <span class="badge bg-soft-danger text-danger ms-1"><?= (int)$hiddenReviewCount ?> hidden</span>
                <?php endif; ?>
            </h4>

            <div class="input-group input-group-merge input-group-flush" style="max-width:220px">
                <div class="input-group-prepend input-group-text">
                    <i class="fa-duotone fa-search"></i>
                </div>
                <input id="reviewsSearch" type="search" class="form-control" placeholder="Search reviews">
            </div>
        </div>
    </div>

    <div class="filter-bar">
        <label>Status</label>
        <button type="button" class="fpill active" data-review-filter="">All</button>
        <button type="button" class="fpill" data-review-filter="visible">
            <span class="fpill-dot" style="background:#00c9a7"></span>Visible
        </button>
        <button type="button" class="fpill fpill-deduct" data-review-filter="hidden">
            <span class="fpill-dot" style="background:#ed4c78"></span>Hidden
        </button>
    </div>

    <?php if (!empty($reviews)): ?>
        <div class="table-responsive datatable-custom">
            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                   id="reviews_table"
                   data-hs-datatables-options='{
                       "order": [[3, "desc"]],
                       "search": "#reviewsSearch",
                       "isResponsive": false,
                       "isShowPaging": false,
                       "pagination": "reviewsPagination",
                       "entries": "#reviewsEntries",
                       "info": {"totalQty": "#reviewsTotalQty"}
                   }'>
                <thead class="thead-light">
                    <tr>
                        <th>Client</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev):
                        $isHidden   = !(int)($rev['approved'] ?? 1);
                        $status     = $isHidden ? 'hidden' : 'visible';
                        $rating     = (int)($rev['rating'] ?? 0);
                        $clientName = (string)($rev['client_username'] ?? 'Unknown');
                        $comment    = (string)($rev['comment'] ?? '');
                        $createdAt  = !empty($rev['created_at']) ? strtotime($rev['created_at']) : 0;
                    ?>
                        <tr data-review-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>">
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-500"><?= htmlspecialchars($clientName, ENT_QUOTES) ?></span>
                                </div>
                            </td>

                            <td data-order="<?= $rating ?>">
                                <div class="d-flex align-items-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-solid fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted opacity-25' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2 fw-500"><?= $rating ?>/5</span>
                                </div>
                            </td>

                            <td class="text-muted" style="max-width:420px;white-space:normal;">
                                <?= $comment !== '' ? nl2br(htmlspecialchars($comment, ENT_QUOTES)) : '—' ?>
                            </td>

                            <td class="text-muted" data-order="<?= $createdAt ?>">
                                <?= $createdAt ? date('d.m.Y H:i', $createdAt) : '—' ?>
                            </td>

                            <td class="text-center">
                                <?php if ($isHidden): ?>
                                    <span class="badge bg-soft-danger text-danger">Hidden</span>
                                <?php else: ?>
                                    <span class="badge bg-soft-success text-success">Visible</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <?php if (!empty($rev['is_placeholder'])): ?>
                                    <span class="text-muted small">Auto</span>
                                <?php else: ?>
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                            class="btn btn-white btn-sm ajax-btn"
                                            data-action="admin_hide_seller_review"
                                            data-id="<?= (int)$rev['id'] ?>"
                                            title="<?= $isHidden ? 'Show review' : 'Hide review' ?>">
                                        <i class="fa-duotone <?= $isHidden ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger btn-sm ajax-btn"
                                            data-action="admin_delete_seller_review"
                                            data-id="<?= (int)$rev['id'] ?>"
                                            data-confirm="Permanently delete this review?"
                                            title="Delete review">
                                        <i class="fa-duotone fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center gap-2">
                        <span>Showing:</span>
                        <div class="tom-select-custom">
                            <select id="reviewsEntries" class="js-select form-select form-select-borderless w-auto"
                                    data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <span class="text-secondary">of</span>
                        <span id="reviewsTotalQty"></span>
                    </div>
                </div>

                <div class="col-sm-auto">
                    <nav id="reviewsPagination"></nav>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body text-muted">No reviews available.</div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#reviews_table'), {
        language: {
            zeroRecords: '<div class="text-center p-4 text-muted">No reviews match the current filter.</div>'
        }
    });

    var dt = $('#reviews_table').DataTable();
    var activeFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'reviews_table') return true;
        if (!activeFilter) return true;
        return ($(dt.row(dataIndex).node()).data('review-status') || '') === activeFilter;
    });

    $('[data-review-filter]').on('click', function () {
        $('[data-review-filter]').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('review-filter');
        dt.draw();
    });
});
</script>
<?= $this->end() ?>