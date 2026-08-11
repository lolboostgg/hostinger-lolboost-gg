<?php
/**
 * Client → Accounts tab.
 *
 * Premium (package) accounts and purchased marketplace accounts used to live in two
 * separate tables with different columns. They share one table now, laid out exactly
 * like the Orders tab. Fields that only exist for one source (email, 2FA, delivery,
 * the premium data blob) are folded into the Details line under the title.
 */
$accountRows = $data['account_rows'] ?? [];

$kindCounts = [];
foreach ($accountRows as $r) {
    $kindCounts[$r['kind']] = ($kindCounts[$r['kind']] ?? 0) + 1;
}
arsort($kindCounts);
?>

<style>
    .acc-kind{
        display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .6rem;border-radius:999px;
        font-size:.72rem;font-weight:800;white-space:nowrap;
        border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);
    }
    .acc-kind i{opacity:.8;}
    .acc-filterbar{
        display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;
        padding:.85rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.06);
    }
    .acc-filterbar > label{
        margin:0 .25rem 0 0;font-size:.72rem;font-weight:800;letter-spacing:.08em;
        text-transform:uppercase;opacity:.5;
    }
    .acc-pill{
        display:inline-flex;align-items:center;gap:.4rem;border:1px solid rgba(255,255,255,.10);
        background:rgba(255,255,255,.03);color:rgba(255,255,255,.68);padding:.42rem .8rem;
        border-radius:999px;font-size:.78rem;font-weight:700;line-height:1;cursor:pointer;transition:all .15s ease;
    }
    .acc-pill:hover{ background:rgba(108,92,231,.14);border-color:rgba(108,92,231,.32);color:#fff; }
    .acc-pill.active{ background:rgba(108,92,231,.22);border-color:rgba(108,92,231,.5);color:#fff; }
    .acc-pill__count{
        padding:.1rem .38rem;border-radius:999px;background:rgba(255,255,255,.08);
        font-size:.68rem;font-weight:800;
    }
    .acc-title{
        display:flex;align-items:center;gap:.45rem;max-width:340px;
        color:inherit;text-decoration:none;
    }
    .acc-title:hover{ color:#9b7cff; }
    .acc-title > span{ overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .acc-game{ width:18px;height:18px;object-fit:contain;border-radius:5px;flex:0 0 auto; }
    .acc-sub{
        display:block;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    }
    .acc-partner{
        display:inline-flex;align-items:center;gap:.45rem;color:inherit;text-decoration:none;max-width:180px;
    }
    .acc-partner:hover{ color:#9b7cff; }
    .acc-partner > span{ overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .acc-partner img{
        width:24px;height:24px;border-radius:50%;object-fit:cover;flex:0 0 auto;background:rgba(255,255,255,.06);
    }
    .acc-status{
        display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .70rem;border-radius:999px;
        font-weight:950;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;
        border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);white-space:nowrap;line-height:1;
    }
    .acc-status__dot{ width:7px;height:7px;border-radius:50%;background:currentColor;opacity:.95;flex:0 0 auto; }
    .acc-status.is-completed{ color:#1fe6c6;border-color:rgba(31,230,198,.22);background:rgba(31,230,198,.10); }
    .acc-status.is-refunded{ color:#ff8a3d;border-color:rgba(255,138,61,.28);background:rgba(255,138,61,.12); }
</style>

<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Accounts</h5>
                </div>
            </div>

            <div class="col-auto">
                <form>
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search accounts" aria-label="Search accounts">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Header -->

    <?php if (!empty($kindCounts)): ?>
        <div class="acc-filterbar">
            <label>Type</label>
            <button type="button" class="acc-pill active" data-account-kind="">
                All <span class="acc-pill__count"><?= count($accountRows) ?></span>
            </button>
            <?php foreach ($kindCounts as $kindLabel => $kindCount): ?>
                <button type="button" class="acc-pill" data-account-kind="<?= htmlspecialchars($kindLabel, ENT_QUOTES) ?>">
                    <?= htmlspecialchars($kindLabel, ENT_QUOTES) ?> <span class="acc-pill__count"><?= (int)$kindCount ?></span>
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
                 }' id="accounts_table">
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
                <?php foreach ($accountRows as $row):
                    $url = (string)($row['url'] ?? '#');

                    // Marketplace titles are marketing strings with emojis and 100+ chars.
                    // Decode first (stored escaped) so apostrophes render, then clip.
                    $titleFull  = html_entity_decode((string)($row['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $titleShort = mb_strlen($titleFull) > 58 ? (mb_substr($titleFull, 0, 55) . '…') : $titleFull;

                    $detailsFull = html_entity_decode((string)($row['details'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $subtitle = trim((string)($row['subtitle'] ?? ''));
                    $subLine  = trim($subtitle . ($subtitle !== '' && $detailsFull !== '' ? ' · ' : '') . $detailsFull);

                    $rowGame      = trim((string)($row['game'] ?? ''));
                    $rowGameIcon  = ($rowGame !== '' && function_exists('util_game_icon_url')) ? util_game_icon_url($rowGame) : '';
                    $rowGameLabel = ($rowGame !== '' && function_exists('util_game_display_name')) ? util_game_display_name($rowGame) : $rowGame;

                    $status      = strtoupper((string)($row['status'] ?? ''));
                    $statusClass = $status === 'REFUNDED' ? 'is-refunded' : 'is-completed';
                    $statusLabel = $status === 'REFUNDED' ? 'Refunded' : 'Completed';

                    $partner     = trim((string)($row['partner'] ?? ''));
                    $partnerIcon = trim((string)($row['partner_icon'] ?? ''));
                    $partnerUrl  = trim((string)($row['partner_url'] ?? ''));
                    $createdAt   = (string)($row['created_at'] ?? '');
                    $createdLabel = $createdAt !== '' ? util_format_date_display($createdAt) : '—';
                ?>
                    <tr data-account-kind="<?= htmlspecialchars($row['kind'], ENT_QUOTES) ?>">
                        <td class="fw-500">
                            <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>">#<?= (int)$row['id'] ?></a>
                        </td>

                        <td class="fw-500" data-search="<?= htmlspecialchars($titleFull . ' ' . $rowGameLabel . ' ' . $subLine, ENT_QUOTES) ?>">
                            <a class="acc-title" href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" title="<?= htmlspecialchars($titleFull, ENT_QUOTES) ?>">
                                <?php if ($rowGameIcon !== ''): ?>
                                    <img class="acc-game" src="<?= htmlspecialchars($rowGameIcon, ENT_QUOTES) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($titleShort, ENT_QUOTES) ?></span>
                            </a>
                            <?php if ($subLine !== ''): ?>
                                <small class="text-muted acc-sub" title="<?= htmlspecialchars($subLine, ENT_QUOTES) ?>"><?= htmlspecialchars($subLine, ENT_QUOTES) ?></small>
                            <?php endif; ?>
                        </td>

                        <td class="fw-500" data-order="<?= (int)($row['price'] ?? 0) ?>">
                            <?= util_format_currency_display('EUR') . util_format_price_display((int)($row['price'] ?? 0)) ?>
                        </td>

                        <td class="fw-500" data-search="<?= htmlspecialchars($statusLabel, ENT_QUOTES) ?>">
                            <span class="acc-status <?= $statusClass ?>"><span class="acc-status__dot"></span><?= $statusLabel ?></span>
                        </td>

                        <td class="fw-500">
                            <?php if ($partner === ''): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <?php if ($partnerUrl !== ''): ?><a class="acc-partner" href="<?= htmlspecialchars($partnerUrl, ENT_QUOTES) ?>"><?php else: ?><span class="acc-partner"><?php endif; ?>
                                    <?php if ($partnerIcon !== ''): ?>
                                        <img src="<?= htmlspecialchars($partnerIcon, ENT_QUOTES) ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($partner, ENT_QUOTES) ?></span>
                                <?php if ($partnerUrl !== ''): ?></a><?php else: ?></span><?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td class="fw-500">
                            <span class="acc-kind">
                                <i class="fa-duotone <?= htmlspecialchars($row['kind_icon'], ENT_QUOTES) ?>"></i>
                                <?= htmlspecialchars($row['kind'], ENT_QUOTES) ?>
                            </span>
                        </td>

                        <td class="fw-500" data-order="<?= htmlspecialchars($createdAt, ENT_QUOTES) ?>" data-search="<?= htmlspecialchars($createdLabel . ' ' . $createdAt, ENT_QUOTES) ?>">
                            <?= $createdLabel ?>
                        </td>

                        <td class="text-end">
                            <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" class="btn btn-white btn-sm">
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
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>

                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>

            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Accounts pagination"></nav>
                </div>
            </div>
        </div>
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
        var table = document.getElementById('accounts_table');
        if (!table) { clearInterval(timer); return; }

        var ready = window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#accounts_table');
        if (!ready && ++attempts < 40) return;
        clearInterval(timer);

        var activeKind = '';
        var pills = document.querySelectorAll('.acc-pill');

        if (ready) {
            var dt = $('#accounts_table').DataTable();
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'accounts_table') return true;
                if (!activeKind) return true;
                return ($(dt.row(dataIndex).node()).data('account-kind') || '') === activeKind;
            });
            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(function (p) { p.classList.remove('active'); });
                    pill.classList.add('active');
                    activeKind = pill.dataset.accountKind || '';
                    dt.draw();
                });
            });
            return;
        }

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');
                activeKind = pill.dataset.accountKind || '';
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    var kind = tr.getAttribute('data-account-kind') || '';
                    tr.style.display = (!activeKind || kind === activeKind) ? '' : 'none';
                });
            });
        });
    }, 100);
})();
</script>
