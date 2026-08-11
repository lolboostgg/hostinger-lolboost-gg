<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>
<?php $orders = $orders ?? []; ?>
<div class="content container-fluid">
    <div class="page-header"><div class="row align-items-center"><div class="col"><h1 class="page-header-title">Item Orders</h1><p class="page-header-text">Your purchased gifting and item service orders.</p></div></div></div>
    <div class="card">
        <div class="card-header"><h4 class="card-header-title">Item Orders</h4></div>
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-align-middle card-table">
                <thead class="thead-light"><tr><th>Order</th><th>Item</th><th>Seller</th><th class="text-end">Price</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                <?php if (!empty($orders)): foreach ($orders as $row): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/profile/item/<?= (int)$row['id'] ?>">#<?= (int)$row['id'] ?></a></td>
                        <td><?= htmlspecialchars($row['item_title'] ?? 'Item') ?></td>
                        <td><?= htmlspecialchars($row['seller_username'] ?? 'Seller') ?></td>
                        <td class="text-end">€<?= number_format(((int)$row['price'])/100,2) ?></td>
                        <td><span class="badge bg-soft-primary text-primary"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td><?= !empty($row['created_at']) ? date('d.m.Y H:i', strtotime($row['created_at'])) : '—' ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No item orders found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
