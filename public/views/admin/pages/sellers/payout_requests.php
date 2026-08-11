<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Payout Requests - Admin Area | LoLBoost.gg', 'h1' => 'Seller Payout Requests', 'description' => 'Review and process seller payout requests.']]) ?>

<?php
$pending   = array_values(array_filter($data, fn($r) => $r['status'] === 'PENDING'));
$processed = array_values(array_filter($data, fn($r) => $r['status'] !== 'PENDING'));

// Fee helper (fallback for legacy rows without fee_cents stored)
function seller_payout_fee_display(array $r): array {
    $gross = (int)($r['amount_cents'] ?? 0);
    if (!empty($r['fee_cents']) || !empty($r['net_cents'])) {
        $feePct  = (float)($r['fee_percent'] ?? 0);
        $fee     = (int)($r['fee_cents']  ?? 0);
        $net     = (int)($r['net_cents']  ?? 0);
    } else {
        $feePct = ((string)($r['method'] ?? '') === 'crypto') ? 5.0 : 3.0;
        $fee    = (int)round($gross * ($feePct / 100));
        $net    = max(0, $gross - $fee);
    }
    return [$gross, $fee, $net, $feePct];
}
?>

<?php if (!empty($pending)): ?>
<div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-duotone fa-clock"></i>
    <strong><?= count($pending) ?></strong> pending payout request<?= count($pending) > 1 ? 's' : '' ?> awaiting review.
</div>

<div class="card mb-5">
    <div class="card-header">
        <h5 class="card-header-title">Pending Requests</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Seller</th>
                    <th>Method &amp; Details</th>
                    <th class="text-end">Requested</th>
                    <th class="text-end">Fee</th>
                    <th class="text-end">Net (Pays Out)</th>
                    <th>Requested At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $r):
                    $details = json_decode($r['details'] ?? '{}', true);
                    [$gross, $fee, $net, $feePct] = seller_payout_fee_display($r);
                    $methodLabel = ((string)($r['method'] ?? '') === 'crypto') ? 'Crypto' : 'Bank Transfer';
                ?>
                <tr>
                    <td class="text-muted">#<?= (int)$r['id'] ?></td>
                    <td>
                        <a href="<?= ADMN_URL ?>/seller/<?= (int)$r['seller_id'] ?>" class="fw-500 d-block">
                            <?= htmlspecialchars($r['seller_username'] ?? '') ?>
                        </a>
                        <span class="text-muted small"><?= htmlspecialchars($r['seller_email'] ?? '') ?></span>
                    </td>
                    <td>
                        <span class="badge bg-soft-secondary text-secondary mb-1"><?= htmlspecialchars($methodLabel) ?></span>
                        <div class="small text-muted" style="max-width:260px;">
                            <?php if (($r['method'] ?? '') === 'crypto'): ?>
                                <?= htmlspecialchars($details['coin'] ?? '') ?> / <?= htmlspecialchars($details['network'] ?? '') ?><br>
                                <code class="small"><?= htmlspecialchars($details['address'] ?? '') ?></code>
                            <?php else: ?>
                                <?= htmlspecialchars($details['beneficiary'] ?? '') ?><br>
                                <code class="small"><?= htmlspecialchars($details['iban'] ?? '') ?></code>
                                <?php if (!empty($details['bic'])): ?> &middot; <?= htmlspecialchars($details['bic']) ?><?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-end fw-500 fs-5">€<?= number_format($gross / 100, 2) ?></td>
                    <td class="text-end text-muted">
                        -€<?= number_format($fee / 100, 2) ?>
                        <div class="small"><?= number_format($feePct, 0) ?>%</div>
                    </td>
                    <td class="text-end fw-700 text-success fs-5">€<?= number_format($net / 100, 2) ?></td>
                    <td class="text-muted"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="d-flex flex-column gap-2 align-items-end" style="min-width:180px;">
                            <!-- Approve -->
                            <form class="ajax-form d-flex gap-2 w-100" action="<?= AJAX_URL ?>" method="POST">
                                <input type="hidden" name="action" value="admin_process_seller_payout">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="status" value="APPROVED">
                                <button type="submit" class="btn btn-sm btn-success w-100">
                                    <i class="fa-duotone fa-check me-1"></i> Approve
                                </button>
                            </form>
                            <!-- Reject with note -->
                            <form class="ajax-form w-100" action="<?= AJAX_URL ?>" method="POST">
                                <input type="hidden" name="action" value="admin_process_seller_payout">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="status" value="REJECTED">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="admin_note" class="form-control form-control-sm" placeholder="Reason (optional)">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-soft-success mb-4">
    <i class="fa-duotone fa-circle-check me-2"></i>No pending payout requests. All caught up!
</div>
<?php endif; ?>

<?php if (!empty($processed)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-header-title text-muted">Processed Requests</h5>
    </div>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{"order":[[0,"desc"]],"isShowPaging":true}'>
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Seller</th>
                    <th>Method</th>
                    <th class="text-end">Requested</th>
                    <th class="text-end">Fee</th>
                    <th class="text-end">Net (Paid Out)</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($processed as $r):
                    [$gross, $fee, $net, $feePct] = seller_payout_fee_display($r);
                    $methodLabel = ((string)($r['method'] ?? '') === 'crypto') ? 'Crypto' : 'Bank Transfer';
                ?>
                <tr>
                    <td class="text-muted">#<?= (int)$r['id'] ?></td>
                    <td>
                        <a href="<?= ADMN_URL ?>/seller/<?= (int)$r['seller_id'] ?>" class="fw-500">
                            <?= htmlspecialchars($r['seller_username'] ?? '') ?>
                        </a>
                        <div class="text-muted small"><?= htmlspecialchars($r['seller_email'] ?? '') ?></div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($methodLabel) ?></td>
                    <td class="text-end fw-500">€<?= number_format($gross / 100, 2) ?></td>
                    <td class="text-end text-muted">-€<?= number_format($fee / 100, 2) ?> <small>(<?= number_format($feePct, 0) ?>%)</small></td>
                    <td class="text-end fw-700">€<?= number_format($net / 100, 2) ?></td>
                    <td>
                        <?php if ($r['status'] === 'APPROVED'): ?>
                            <span class="badge bg-soft-success text-success">Approved</span>
                        <?php else: ?>
                            <span class="badge bg-soft-danger text-danger">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($r['admin_note'] ?? '—') ?></td>
                    <td class="text-muted"><?= date('d.m.Y', strtotime($r['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row align-items-center">
            <div class="col-auto">
                <nav id="datatableWithSearchPagination"></nav>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
