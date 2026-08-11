<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<div class="card">
  <div class="card-header">
    <h5 class="card-header-title mb-0">Sales</h5>
  </div>

  <div class="table-responsive">
    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Status</th>
          <th>Total</th>
          <th>Created</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)) : ?>
          <tr><td colspan="6" class="text-center text-muted py-5">No sales yet.</td></tr>
        <?php else : ?>
          <?php foreach ($orders as $o) : ?>
            <tr>
              <td>#<?= (int)$o['id'] ?></td>
              <td><?= htmlspecialchars($o['client_username'] ?? $o['client_email'] ?? '-') ?></td>
              <td><?= htmlspecialchars($o['status'] ?? '-') ?></td>
              <td><?= number_format(((int)($o['total_cents'] ?? 0))/100, 2) ?> €</td>
              <td><?= htmlspecialchars($o['created_at'] ?? '-') ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-white" href="<?= BASE_URL ?>/seller-area/orders/<?= urlencode($o['id']) ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
