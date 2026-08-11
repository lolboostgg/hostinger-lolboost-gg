<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>
<?php
$rows = $rows ?? [];
$h = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Transactions</h5>
    <small class="text-muted">Your balance movements (sales credits, payouts, adjustments).</small>
  </div>
  <div class="table-responsive">
    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th class="text-end">Amount</th>
          <th>Ref</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet.</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td class="fw-semibold">#<?= (int)$r['id'] ?></td>
            <td><?= $h($r['type'] ?? '-') ?></td>
            <td class="text-end"><?= number_format(((int)($r['amount_cents'] ?? 0))/100, 2) ?> <?= $h($r['currency'] ?? 'EUR') ?></td>
            <td class="small text-muted"><?= $h(($r['ref_table'] ?? '').':'.($r['ref_id'] ?? '')) ?></td>
            <td><?= $h($r['created_at'] ?? '-') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
