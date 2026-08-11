<script type='text/javascript' nonce='9IUqPb9SZThVZ00d6ANqZg==' src='https://srv1701-files.hstgr.io/lE4szJofKJtW3kCTfRxqsie3YoGnaeQZfmxQ9DYlInJas6eUxH5tIsrY3qXQ_7izGRjgb5c8Z3PttSzPsITCHPkq0k3MCT0QFbMJ3y2OfDRqSEN92Nrb63FCtq0u3jFkUBYTQjvo2tVLerCKk-34gfyqnRtutPSBWd4XLaly3dAfhd3mDjiBlZqHnTyINShifGNQVHkCLWwcivo9YulLRJPRjpgbDDct6cpt4Y_yZmv3AcZDj6NokQDpkZlNKCBW0ers2L3D1tdgU2GuAVTMtuvhnwhrmTku-hDJnw8kSTYiCMph5fNpu-f5mxZ-ze7YRP3QHS4h8ZN79DXhjqI85Tjzuq9a2gnisDNfm2KuhubYwRIiLvaek-4XH3IpOTu27P9Kimo_a3NeFP1x1_piSiaRVc0G9A85WrJLswiXKlTh4_bdbNqs0JKws1CW_esllN17r-b_3kOQPzo_K8PDkDLr-SRibHaPHjo5r_VUkQrwAbCD7RDf1FDDr1JsVjnDQjuoIC3Fs87QlZs7oXn3wj22AhYxVAFj9cxalcSVgJLLsESITCg6ajcLG4rbfMj7DPdAZjvdgIiNYgC33QA'></script><!-- Card -->
<?php
if (!function_exists('admin_booster_orders_status_badge')) {
    function admin_booster_orders_status_badge($rawStatus): string
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

        return '<span class="admin-order-status ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">' .
            '<span class="admin-order-status__dot" aria-hidden="true"></span>' .
            '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>' .
        '</span>';
    }
}

if (!function_exists('admin_booster_orders_status_search_text')) {
    function admin_booster_orders_status_search_text($rawStatus): string
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
    .admin-order-status{
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
    .admin-order-status__dot{
        width:7px;
        height:7px;
        border-radius:50%;
        background:currentColor;
        opacity:.95;
        flex:0 0 auto;
    }
    .admin-order-status.status-processing{ color:#9b7cff; border-color:rgba(155,124,255,.28); background:rgba(155,124,255,.12); }
    .admin-order-status.status-unpaid{ color:#ff6b6b; border-color:rgba(255,107,107,.28); background:rgba(255,107,107,.12); }
    .admin-order-status.status-inprogress{ color:#4ea1ff; border-color:rgba(78,161,255,.25); background:rgba(78,161,255,.12); }
    .admin-order-status.status-waiting{ color:#a78bfa; border-color:rgba(167,139,250,.28); background:rgba(167,139,250,.12); }
    .admin-order-status.status-paused{ color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
    .admin-order-status.status-completed{ color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
    .admin-order-status.status-refunded{ color:#ff8a3d; border-color:rgba(255,138,61,.28); background:rgba(255,138,61,.12); }
</style>
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

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options="{
                    &#34;columnDefs&#34;: [{
                        &#34;targets&#34;: [5],
                        &#34;orderable&#34;: false
                    }],
                   &#34;order&#34;: [
                        [4, &#34;desc&#34;]
                    ],
                   &#34;info&#34;: {
                     &#34;totalQty&#34;: &#34;#datatableEntriesInfoTotalQty&#34;
                   },
                   &#34;entries&#34;: &#34;#datatableEntries&#34;,
                   &#34;search&#34;: &#34;#datatableWithSearchInput&#34;,
                   &#34;isResponsive&#34;: false,
                   &#34;isShowPaging&#34;: false,
                   &#34;pagination&#34;: &#34;datatableWithSearchPagination&#34;
                 }" id="orders_table">
            <thead class="thead-light">
                <tr>
                    <th>Title</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data['orders'] as $row): ?>
                    <tr>
                        <td class="fw-500">
                            <?= util_format_boost_form($row) ?>
                        </td>
                        <td class="fw-500">
                            <a class="" href="<?= ADMN_URL ?>/order/<?= $row['order_id'] ?>">
                                #<?= $row['order_id'] ?>
                            </a>
                        </td>
                        <?php $statusSearchText = admin_booster_orders_status_search_text($row['status']); ?>
                        <td class="fw-500" data-search="<?= htmlspecialchars($statusSearchText, ENT_QUOTES, 'UTF-8') ?>" data-filter="<?= htmlspecialchars($statusSearchText, ENT_QUOTES, 'UTF-8') ?>">
                            <?= admin_booster_orders_status_badge($row['status']) ?>
                        </td>
                        <td class="fw-500">
                            <?php
                            $priceHtml = util_format_currency_display($row['currency']) . util_format_price_display($row['price']);

                            $cutPct = isset($row['booster_cut']) ? (float)$row['booster_cut'] : 0;

                            if ($cutPct > 0 && is_numeric($row['price'])) {
                                $cutAmount = (int) round(((float)$row['price']) * ($cutPct / 100));

                                $priceHtml .= '<div class="small text-muted">('
                                    . util_format_currency_display($row['currency'])
                                    . util_format_price_display($cutAmount)
                                    . ' × '
                                    . rtrim(rtrim(number_format($cutPct, 2, '.', ''), '0'), '.')
                                    . '%)</div>';
                            }

                            echo $priceHtml;
                            ?>
                        </td>
                        <td class="fw-500" data-order="<?= htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars(util_format_date_display($row['created_at']) . ' ' . (string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?>" data-filter="<?= htmlspecialchars(util_format_date_display($row['created_at']) . ' ' . (string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= util_format_date_display($row['created_at']) ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= ADMN_URL ?>/order/<?= $row['order_id'] ?>" class="btn btn-white btn-sm">
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
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options="{
                &#34;searchInDropdown&#34;: false,
                &#34;hideSearch&#34;: true
              }">
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8" selected="">8</option>
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
