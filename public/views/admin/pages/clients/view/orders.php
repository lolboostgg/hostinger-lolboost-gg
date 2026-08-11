<?php
if (!function_exists('client_orders_status_badge')) {
    function client_orders_status_badge($rawStatus): string
    {
        $raw = strtoupper(trim((string)$rawStatus));
        $normalized = str_replace(['-', ' '], '_', $raw);
        $normalized = preg_replace('/_+/', '_', $normalized);

        $map = [
            'PROCESSING'           => ['Processing', 'status-processing'],
            'PAID'                 => ['Processing', 'status-processing'],
            'UNPAID'               => ['Unpaid', 'status-unpaid'],
            'IN_PROGRESS'          => ['In Progress', 'status-inprogress'],
            'INPROGRESS'           => ['In Progress', 'status-inprogress'],
            'WAITING_FOR_APPROVAL' => ['Waiting for Approval', 'status-waiting'],
            'WAITING_APPROVAL'     => ['Waiting for Approval', 'status-waiting'],
            'APPROVAL'             => ['Waiting for Approval', 'status-waiting'],
            'PAUSED'               => ['Paused', 'status-paused'],
            'COMPLETED'            => ['Completed', 'status-completed'],
            'REFUND'               => ['Refunded', 'status-refunded'],
            'REFUNDED'             => ['Refunded', 'status-refunded'],
            'REFUNDEDED'           => ['Refunded', 'status-refunded'],
            'CANCELLED'            => ['Cancelled', 'status-refunded'],
            'CANCELED'             => ['Cancelled', 'status-refunded'],
        ];

        if (isset($map[$normalized])) {
            [$label, $class] = $map[$normalized];
        } else {
            $label = trim(ucwords(strtolower(str_replace('_', ' ', $normalized)))) ?: 'Unknown';
            $class = 'status-processing';
        }

        return '<span class="client-order-status ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">' .
            '<span class="client-order-status__dot" aria-hidden="true"></span>' .
            '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>' .
        '</span>';
    }
}

if (!function_exists('client_orders_status_search_text')) {
    function client_orders_status_search_text($rawStatus): string
    {
        $raw = strtoupper(trim((string)$rawStatus));
        $normalized = str_replace(['-', ' '], '_', $raw);
        $normalized = preg_replace('/_+/', '_', $normalized);

        $labels = [
            'PROCESSING'           => 'Processing',
            'PAID'                 => 'Processing',
            'UNPAID'               => 'Unpaid',
            'IN_PROGRESS'          => 'In Progress',
            'INPROGRESS'           => 'In Progress',
            'WAITING_FOR_APPROVAL' => 'Waiting for Approval',
            'WAITING_APPROVAL'     => 'Waiting for Approval',
            'APPROVAL'             => 'Waiting for Approval',
            'PAUSED'               => 'Paused',
            'COMPLETED'            => 'Completed',
            'REFUND'               => 'Refunded',
            'REFUNDED'             => 'Refunded',
            'REFUNDEDED'           => 'Refunded',
            'CANCELLED'            => 'Cancelled',
            'CANCELED'             => 'Cancelled',
        ];

        $label = $labels[$normalized] ?? (trim(ucwords(strtolower(str_replace('_', ' ', $normalized)))) ?: 'Unknown');
        return trim($raw . ' ' . $normalized . ' ' . $label);
    }
}
?>

<style>
    .client-order-kind{
        display:inline-flex;
        align-items:center;
        gap:.4rem;
        padding:.28rem .6rem;
        border-radius:999px;
        font-size:.72rem;
        font-weight:800;
        white-space:nowrap;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.04);
    }
    .client-order-kind i{opacity:.8;}
    .client-order-filterbar{
        display:flex;
        align-items:center;
        gap:.5rem;
        flex-wrap:wrap;
        padding:.85rem 1.25rem;
        border-bottom:1px solid rgba(255,255,255,.06);
    }
    .client-order-filterbar > label{
        margin:0 .25rem 0 0;
        font-size:.72rem;
        font-weight:800;
        letter-spacing:.08em;
        text-transform:uppercase;
        opacity:.5;
    }
    .client-order-pill{
        display:inline-flex;
        align-items:center;
        gap:.4rem;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.03);
        color:rgba(255,255,255,.68);
        padding:.42rem .8rem;
        border-radius:999px;
        font-size:.78rem;
        font-weight:700;
        line-height:1;
        cursor:pointer;
        transition:all .15s ease;
    }
    .client-order-pill:hover{ background:rgba(108,92,231,.14); border-color:rgba(108,92,231,.32); color:#fff; }
    .client-order-pill.active{ background:rgba(108,92,231,.22); border-color:rgba(108,92,231,.5); color:#fff; }
    .client-order-pill__count{
        padding:.1rem .38rem;
        border-radius:999px;
        background:rgba(255,255,255,.08);
        font-size:.68rem;
        font-weight:800;
    }
    .client-order-title{
        display:flex;
        align-items:center;
        gap:.45rem;
        max-width:340px;
        color:inherit;
        text-decoration:none;
    }
    .client-order-title:hover{ color:#9b7cff; }
    .client-order-title > span{
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .client-order-partner{
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        color:inherit;
        text-decoration:none;
        max-width:180px;
    }
    .client-order-partner:hover{ color:#9b7cff; }
    .client-order-partner > span{
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .client-order-partner img{
        width:24px;
        height:24px;
        border-radius:50%;
        object-fit:cover;
        flex:0 0 auto;
        background:rgba(255,255,255,.06);
    }
    .client-order-game{
        width:18px;
        height:18px;
        object-fit:contain;
        border-radius:5px;
        flex:0 0 auto;
    }
    .client-order-status{
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        padding:.34rem .70rem;
        border-radius:999px;
        font-weight:950;
        font-size:.72rem;
        letter-spacing:.08em;
        text-transform:uppercase;
        border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.04);
        color:rgba(255,255,255,.85);
        white-space:nowrap;
        line-height:1;
    }
    .client-order-status__dot{
        width:7px;
        height:7px;
        border-radius:50%;
        background:currentColor;
        opacity:.95;
        flex:0 0 auto;
    }
    .client-order-status.status-processing{ color:#9b7cff; border-color:rgba(155,124,255,.28); background:rgba(155,124,255,.12); }
    .client-order-status.status-unpaid{ color:#ff6b6b; border-color:rgba(255,107,107,.28); background:rgba(255,107,107,.12); }
    .client-order-status.status-inprogress{ color:#4ea1ff; border-color:rgba(78,161,255,.25); background:rgba(78,161,255,.12); }
    .client-order-status.status-waiting{ color:#a78bfa; border-color:rgba(167,139,250,.28); background:rgba(167,139,250,.12); }
    .client-order-status.status-paused{ color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
    .client-order-status.status-completed{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
    .client-order-status.status-refunded{ color:#ff8a3d; border-color:rgba(255,138,61,.28); background:rgba(255,138,61,.12); }
</style>
<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Orders List</h5>
                </div>
            </div>

            <div class="col-auto">
                <!-- Filter -->
                <form>
                    <!-- Search -->
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search orders" aria-label="Search orders">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
        </div>
    </div>
    <!-- End Header -->

    <?php
      // One pill per purchase type that this client actually has, so the bar never
      // shows an option that would filter the table down to nothing.
      $kindCounts = [];
      foreach (($data['all_orders'] ?? []) as $kindRow) {
          $k = trim((string)($kindRow['kind'] ?? ''));
          if ($k === '') continue;
          $kindCounts[$k] = ($kindCounts[$k] ?? 0) + 1;
      }
      arsort($kindCounts);
    ?>
    <?php if (!empty($kindCounts)): ?>
        <div class="client-order-filterbar">
            <label>Type</label>
            <button type="button" class="client-order-pill active" data-order-kind="">
                All <span class="client-order-pill__count"><?= count($data['all_orders'] ?? []) ?></span>
            </button>
            <?php foreach ($kindCounts as $kindLabel => $kindCount): ?>
                <button type="button" class="client-order-pill" data-order-kind="<?= htmlspecialchars($kindLabel, ENT_QUOTES) ?>">
                    <?= htmlspecialchars($kindLabel, ENT_QUOTES) ?> <span class="client-order-pill__count"><?= (int)$kindCount ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                    "columnDefs": [{
                        "targets": [7],
                        "orderable": false
                    }],
                   "order": [
                        [6, "desc"]
                    ],
                   "info": {
                     "totalQty": "#datatableEntriesInfoTotalQty"
                   },
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }' id="orders_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Partner</th>
                    <th>Type</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                  // One row per purchase of any kind — boosting, GG-Girl bookings, accounts,
                  // items, top ups and digital goods, already merged and sorted by the route.
                  $allOrders = $data['all_orders'] ?? [];
                ?>
                <?php foreach ($allOrders as $row) :
                    $statusSearchText = client_orders_status_search_text($row['status'] ?? '');
                    $createdAt = (string)($row['created_at'] ?? '');
                    $createdLabel = $createdAt !== '' ? util_format_date_display($createdAt) : '—';

                    // Account titles are marketing strings with emojis and 100+ chars, which
                    // blew the table width apart. Decode first (they are stored escaped, so
                    // escaping again would show "&#039;"), then clip and keep the full text
                    // in the tooltip.
                    $titleFull = html_entity_decode((string)($row['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $titleShort = mb_strlen($titleFull) > 58 ? (mb_substr($titleFull, 0, 55) . '…') : $titleFull;
                ?>
                    <tr data-order-kind="<?= htmlspecialchars((string)($row['kind'] ?? ''), ENT_QUOTES) ?>">
                        <td class="fw-500">
                            <a href="<?= htmlspecialchars((string)($row['url'] ?? '#'), ENT_QUOTES) ?>"><?= htmlspecialchars((string)($row['ref'] ?? ''), ENT_QUOTES) ?></a>
                        </td>
                        <?php
                          // Digital goods have no game, everything else resolves through the
                          // shared helper so newly added games get an icon automatically.
                          $rowGame = trim((string)($row['game'] ?? ''));
                          $rowGameIcon = ($rowGame !== '' && function_exists('util_game_icon_url')) ? util_game_icon_url($rowGame) : '';
                          $rowGameLabel = ($rowGame !== '' && function_exists('util_game_display_name')) ? util_game_display_name($rowGame) : $rowGame;
                        ?>
                        <td class="fw-500" data-search="<?= htmlspecialchars($titleFull . ' ' . $rowGameLabel, ENT_QUOTES) ?>">
                            <a class="client-order-title" href="<?= htmlspecialchars((string)($row['url'] ?? '#'), ENT_QUOTES) ?>" title="<?= htmlspecialchars($titleFull, ENT_QUOTES) ?>">
                                <?php if ($rowGameIcon !== ''): ?>
                                    <img class="client-order-game" src="<?= htmlspecialchars($rowGameIcon, ENT_QUOTES) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($titleShort, ENT_QUOTES) ?></span>
                            </a>
                            <?php if (trim((string)($row['subtitle'] ?? '')) !== ''): ?>
                                <small class="text-muted"><?= htmlspecialchars(html_entity_decode((string)$row['subtitle'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="fw-500" data-order="<?= (int)($row['price'] ?? 0) ?>">
                            <?= util_format_currency_display($row['currency'] ?? 'EUR') . util_format_price_display((int)($row['price'] ?? 0)) ?>
                        </td>
                        <td class="fw-500" data-search="<?= htmlspecialchars($statusSearchText, ENT_QUOTES, 'UTF-8') ?>" data-filter="<?= htmlspecialchars($statusSearchText, ENT_QUOTES, 'UTF-8') ?>">
                            <?= client_orders_status_badge($row['status'] ?? '') ?>
                        </td>
                        <td class="fw-500">
                            <?php
                              $partner = trim((string)($row['partner'] ?? ''));
                              $partnerIcon = trim((string)($row['partner_icon'] ?? ''));
                              if ($partnerIcon !== '' && strpos($partnerIcon, 'http') !== 0) {
                                  $partnerIcon = rtrim(BASE_URL, '/') . '/' . ltrim($partnerIcon, '/');
                              }
                              // Boosters and sellers without their own avatar still get one,
                              // otherwise the column jumps between icon and plain text.
                              if ($partnerIcon === '') {
                                  $partnerIcon = (defined('ICON_URL') ? ICON_URL : '') . '/default1.png';
                              }
                            ?>
                            <?php if ($partner === ''): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <?php $partnerUrl = trim((string)($row['partner_url'] ?? '')); ?>
                                <?php if ($partnerUrl !== ''): ?><a class="client-order-partner" href="<?= htmlspecialchars($partnerUrl, ENT_QUOTES) ?>"><?php else: ?><span class="client-order-partner"><?php endif; ?>
                                    <img src="<?= htmlspecialchars($partnerIcon, ENT_QUOTES) ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
                                    <span><?= htmlspecialchars($partner, ENT_QUOTES) ?></span>
                                <?php if ($partnerUrl !== ''): ?></a><?php else: ?></span><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="fw-500">
                            <span class="client-order-kind">
                                <i class="fa-duotone <?= htmlspecialchars((string)($row['kind_icon'] ?? 'fa-receipt'), ENT_QUOTES) ?>"></i>
                                <?= htmlspecialchars((string)($row['kind'] ?? ''), ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td class="fw-500" data-order="<?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars($createdLabel . ' ' . $createdAt, ENT_QUOTES, 'UTF-8') ?>" data-filter="<?= htmlspecialchars($createdLabel . ' ' . $createdAt, ENT_QUOTES, 'UTF-8') ?>">
                            <?= $createdLabel ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= htmlspecialchars((string)($row['url'] ?? '#'), ENT_QUOTES) ?>" class="btn btn-white btn-sm">
                                <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <!-- End Table -->

    <!-- Footer -->
    <div class="card-footer">
        <!-- Pagination -->
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>

                    <!-- Select -->
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <!-- End Select -->

                    <span class="text-secondary me-2">of</span>

                    <!-- Pagination Quantity -->
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>

            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <!-- Pagination -->
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
        <!-- End Pagination -->
    </div>
    <!-- End Footer -->
</div>
<!-- End Card -->

<script>
(function () {
    // The DataTable is initialised by the parent view, so wait for it before hooking
    // the type filter in. Falls back to plain row hiding if DataTables never shows up.
    var attempts = 0;
    var timer = setInterval(function () {
        var table = document.getElementById('orders_table');
        if (!table) { clearInterval(timer); return; }

        var ready = window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#orders_table');
        if (!ready && ++attempts < 40) return;
        clearInterval(timer);

        var activeKind = '';
        var pills = document.querySelectorAll('.client-order-pill');

        if (ready) {
            var dt = $('#orders_table').DataTable();
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'orders_table') return true;
                if (!activeKind) return true;
                return ($(dt.row(dataIndex).node()).data('order-kind') || '') === activeKind;
            });
            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(function (p) { p.classList.remove('active'); });
                    pill.classList.add('active');
                    activeKind = pill.dataset.orderKind || '';
                    dt.draw();
                });
            });
            return;
        }

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');
                activeKind = pill.dataset.orderKind || '';
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    var kind = tr.getAttribute('data-order-kind') || '';
                    tr.style.display = (!activeKind || kind === activeKind) ? '' : 'none';
                });
            });
        });
    }, 100);
})();
</script>