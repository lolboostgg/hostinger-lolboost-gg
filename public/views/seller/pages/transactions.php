<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-header-title mb-0">Latest transactions</h5>
    <span class="badge bg-soft-primary text-primary">Balance: <?= number_format(((int)(SELLER_DATA['balance_cents'] ?? 0))/100, 2) ?> EUR</span>
  </div>

  <div class="table-responsive">
    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Amount</th>
          <th>Ref</th>
          <th>Note</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tx)) : ?>
          <tr><td colspan="6" class="text-center text-muted py-5">No transactions yet.</td></tr>
        <?php else : ?>
          <?php foreach ($tx as $t) : ?>
            <tr>
              <td>#<?= (int)$t['id'] ?></td>
              <td><?= htmlspecialchars($t['type'] ?? '-') ?></td>
              <td><?= number_format(((int)($t['amount_cents'] ?? 0))/100, 2) ?> <?= htmlspecialchars($t['currency'] ?? 'EUR') ?></td>
              <td><?= htmlspecialchars(($t['ref_table'] ?? '') . ' ' . ($t['ref_id'] ?? '')) ?></td>
              <td><?= htmlspecialchars($t['note'] ?? '-') ?></td>
              <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
