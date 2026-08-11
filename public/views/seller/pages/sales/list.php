<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Sales - Seller Area | LoLBoost.gg']]) ?>

<?php
  $rows = $rows ?? [];
  $pagination = $pagination ?? ['page' => 1, 'totalPages' => 1];

  // simple column sorting by query params
  $sort = $_GET['sort'] ?? 'sold_at';
  $dir  = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
  $toggleDir = $dir === 'asc' ? 'desc' : 'asc';

  $sortLink = function(string $key, string $label) use ($sort, $dir, $toggleDir) {
    $nextDir = ($sort === $key) ? $toggleDir : 'asc';
    $icon = '';
    if ($sort === $key) {
      $icon = $dir === 'asc' ? ' <i class="fa-duotone fa-arrow-up-small"></i>' : ' <i class="fa-duotone fa-arrow-down-small"></i>';
    }
    $q = $_GET;
    $q['sort'] = $key;
    $q['dir'] = $nextDir;
    $href = '/seller-area/sales?' . http_build_query($q);
    return '<a class="text-decoration-none text-body" href="' . $href . '">' . $label . $icon . '</a>';
  };

  $formatCents = function($cents) {
    $c = (int)($cents ?? 0);
    return $c > 0 ? util_format_price_display($c) . ' EUR' : '—';
  };
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header-title mb-0">Sales</h5>
            <div class="text-muted small">Only sold accounts are shown</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th><?= $sortLink('sale_id', 'ID') ?></th>
                    <th><?= $sortLink('package_name', 'Package') ?></th>
                    <th><?= $sortLink('client_username', 'Customer') ?></th>
                    <th><?= $sortLink('sold_at', 'Sold at') ?></th>
                    <th class="text-end"><?= $sortLink('total_price', 'Price') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No sales yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="fw-semibold">#<?= (int)$r['sale_id'] ?></td>
                            <td><?= esc($r['package_name'] ?? '—') ?></td>
                            <td><?= esc($r['client_username'] ?? '—') ?></td>
                            <td><?= !empty($r['sold_at']) ? esc($r['sold_at']) : '—' ?></td>
                            <td class="text-end fw-semibold"><?= $formatCents($r['total_price'] ?? 0) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="/seller-area/sale/<?= (int)$r['sale_id'] ?>">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <?php
            $page = (int)($pagination['page'] ?? 1);
            $totalPages = (int)($pagination['totalPages'] ?? 1);
            $prev = max(1, $page - 1);
            $next = min($totalPages, $page + 1);
            $baseQuery = $_GET;
        ?>

        <nav aria-label="Sales pagination">
            <ul class="pagination mb-0 justify-content-end">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <?php $baseQuery['page'] = $prev; ?>
                    <a class="page-link" href="/seller-area/sales?<?= http_build_query($baseQuery) ?>">Previous</a>
                </li>

                <li class="page-item disabled"><span class="page-link">Page <?= $page ?> / <?= $totalPages ?></span></li>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <?php $baseQuery['page'] = $next; ?>
                    <a class="page-link" href="/seller-area/sales?<?= http_build_query($baseQuery) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
