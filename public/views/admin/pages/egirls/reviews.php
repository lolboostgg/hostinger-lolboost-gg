<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'E-Girl Reviews - Admin Area | LoLBoost.gg', 'h1' => 'E-Girl Reviews']]) ?>

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


<?php
$reviews  = $reviews ?? [];
$pending  = array_filter($reviews, fn($r) => !$r['approved']);
$approved = array_filter($reviews, fn($r) =>  $r['approved']);
?>

<?php if (!empty($pending)): ?>
<div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-clock fs-4"></i>
    <div><strong><?= count($pending) ?></strong> review<?= count($pending) > 1 ? 's' : '' ?> awaiting approval.</div>
</div>
<?php else: ?>
<div class="alert alert-soft-success d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-circle-check fs-4"></i>
    <div>No pending reviews — all caught up!</div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-header-title">E-Girl Reviews</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-soft-warning text-warning"><?= count($pending) ?> pending</span>
            <span class="badge bg-soft-success text-success"><?= count($approved) ?> approved</span>
        </div>
    </div>

    <?php if (!empty($reviews)): ?>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               id="reviews_table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets":[6],"orderable":false}],
                   "order": [[5,"desc"]],
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "reviewsPagination",
                   "entries": "#reviewsEntries",
                   "info": {"totalQty":"#reviewsTotalQty"}
               }'>
            <thead class="thead-light">
                <tr>
                    <th>Client</th><th>E-Girl</th><th>Rating</th>
                    <th style="max-width:260px">Comment</th><th>Status</th><th>Date</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td class="fw-500"><?= htmlspecialchars($r['client_username'] ?? '—') ?></td>
                    <td class="fw-500"><?= htmlspecialchars($r['egirl_username'] ?? '—') ?></td>
                    <td>
                        <span class="text-warning"><?= str_repeat('★', (int)$r['rating']) ?></span>
                        <span class="text-muted"><?= str_repeat('★', 5-(int)$r['rating']) ?></span>
                        <span class="text-muted ms-1"><?= (int)$r['rating'] ?>/5</span>
                    </td>
                    <td class="text-muted" style="max-width:260px;white-space:normal">
                        <?= htmlspecialchars(mb_strimwidth($r['comment'] ?? '—', 0, 80, '…')) ?>
                    </td>
                    <td>
                        <?php if ($r['approved']): ?>
                            <span class="badge bg-soft-success text-success"><i class="fa-duotone fa-circle-check me-1"></i>Approved</span>
                        <?php else: ?>
                            <span class="badge bg-soft-warning text-warning"><i class="fa-duotone fa-clock me-1"></i>Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted" data-order="<?= strtotime($r['created_at']) ?>">
                        <?= date('d.m.Y', strtotime($r['created_at'])) ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <?php if (!$r['approved']): ?>
                                <button class="btn btn-sm btn-success js-approve-review" data-id="<?= $r['id'] ?>">
                                    <i class="fa-duotone fa-check me-1"></i> Approve
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger js-delete-review" data-id="<?= $r['id'] ?>">
                                <i class="fa-duotone fa-trash"></i>
                            </button>
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
                <div class="d-flex align-items-center gap-2">
                    <span>Showing:</span>
                    <div class="tom-select-custom">
                        <select id="reviewsEntries" class="js-select form-select form-select-borderless w-auto"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="10" selected>10</option><option value="25">25</option>
                        </select>
                    </div>
                    <span class="text-secondary">of</span><span id="reviewsTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="reviewsPagination"></nav></div>
        </div>
    </div>
    <?php else: ?>
        <div class="card-body text-center text-muted py-5">
            <i class="fa-duotone fa-star fs-1 mb-3 d-block"></i>No reviews yet.
        </div>
    <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#reviews_table'));
    const AJAX = '<?= AJAX_URL ?>';
    document.querySelectorAll('.js-approve-review').forEach(b => b.addEventListener('click', function () {
        $.post(AJAX, { action: 'admin_egirl_review_update', review_id: this.dataset.id, approved: 1 }, () => location.reload());
    }));
    document.querySelectorAll('.js-delete-review').forEach(b => b.addEventListener('click', function () {
        if (!confirm('Delete this review permanently?')) return;
        $.post(AJAX, { action: 'admin_egirl_review_update', review_id: this.dataset.id, approved: -1 }, () => location.reload());
    }));
});
</script>
<?= $this->end() ?>
