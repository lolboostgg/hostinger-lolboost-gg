<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Clients List - Admin Area | LoLBoost.gg', 'h1' => 'Clients List', 'description' => 'View the Clients List.']]) ?>

<style>
.client-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
    gap: .85rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #25282a;
    border: .0625rem solid #2f3235;
    border-radius: .75rem;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    transition: transform .15s ease, box-shadow .15s ease;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.35); }
.stat-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.stat-icon.c-teal  { background:rgba(0,201,167,.13);  color:#00c9a7; }
.stat-icon.c-red   { background:rgba(237,76,120,.13); color:#ed4c78; }
.stat-icon.c-gray  { background:rgba(109,116,123,.13); color:#91989e; }
.stat-icon.c-blue  { background:rgba(9,165,190,.13);  color:#09a5be; }
.stat-icon.c-amber { background:rgba(245,202,153,.13);color:#f5ca99; }
.stat-label { font-size:.7rem;color:#91989e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.1rem; }
.stat-value { font-size:1.3rem;font-weight:700;color:#c5c8cc;line-height:1.2; }

.status-filter-wrap {
    display:flex;align-items:center;gap:.4rem;
    padding:.75rem 1.3125rem;
    border-bottom:.0625rem solid #2f3235;
    background:rgba(0,0,0,.10);
    flex-wrap:wrap;
}
.status-filter-wrap label { font-size:.75rem;color:#91989e;margin:0;margin-right:.2rem; }
.status-pill {
    display:inline-flex;align-items:center;gap:.3rem;
    padding:.28rem .75rem;border-radius:50rem;
    font-size:.78rem;font-weight:600;cursor:pointer;
    border:1px solid #2f3235;transition:all .15s ease;
    background:transparent;color:#91989e;
}
.status-pill:hover { color:#c5c8cc;border-color:#4b5055; }
.status-pill.active-pill { color:#1e2022;background:#00c9a7;border-color:#00c9a7; }
.status-pill.banned-pill.active-pill { color:#fff;background:#ed4c78;border-color:#ed4c78; }
.status-pill .pill-dot { width:7px;height:7px;border-radius:50%;background:currentColor;opacity:.7; }
.filter-divider { width:1px;height:22px;background:#2f3235;margin:0 .35rem; }
.loyalty-filter { position:relative; min-width:190px; }
.loyalty-filter-toggle {
    width:100%; display:flex; align-items:center; justify-content:space-between; gap:.65rem;
    min-height:34px; padding:.32rem .75rem; border-radius:.7rem; border:1px solid #2f3235;
    background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.015));
    color:#c5c8cc; font-size:.78rem; font-weight:700; cursor:pointer;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04); transition:all .15s ease;
}
.loyalty-filter-toggle:hover, .loyalty-filter.open .loyalty-filter-toggle { border-color:#f5ca99; color:#f5ca99; }
.loyalty-filter-toggle i { font-size:.72rem; transition:transform .15s ease; }
.loyalty-filter.open .loyalty-filter-toggle i { transform:rotate(180deg); }
.loyalty-filter-menu {
    position:absolute; top:calc(100% + 8px); left:0; right:0; z-index:25; display:none;
    padding:.4rem; border-radius:.85rem; border:1px solid #34383c;
    background:#1e2022; box-shadow:0 18px 45px rgba(0,0,0,.42);
    max-height:280px; overflow:auto;
}
.loyalty-filter.open .loyalty-filter-menu { display:block; }
.loyalty-filter-option {
    width:100%; display:flex; align-items:center; gap:.55rem; padding:.5rem .6rem;
    border:0; border-radius:.62rem; background:transparent; color:#91989e;
    font-size:.78rem; font-weight:700; text-align:left; cursor:pointer; transition:all .15s ease;
}
.loyalty-filter-option:hover { background:rgba(245,202,153,.08); color:#f5ca99; }
.loyalty-filter-option.active { background:rgba(245,202,153,.14); color:#f5ca99; }
.loyalty-rank-choice { display:flex; align-items:center; gap:.55rem; min-width:0; }
.loyalty-rank-choice img { width:21px; height:21px; object-fit:contain; flex:0 0 auto; filter:drop-shadow(0 3px 8px rgba(0,0,0,.28)); }
.loyalty-rank-choice span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.loyalty-filter-toggle .loyalty-rank-choice img { width:22px; height:22px; }
.loyalty-filter-option .rank-dot { width:8px;height:8px;border-radius:50%;background:currentColor;opacity:.8;flex:0 0 auto; }
.loyalty-filter-option.active .loyalty-rank-choice img,
.loyalty-filter-option:hover .loyalty-rank-choice img { transform:scale(1.05); }
@media (max-width: 575.98px) { .loyalty-filter { width:100%; } }

</style>

<?php
$stats = $stats ?? [];
$loyaltyRanks = $loyaltyRanks ?? [];
$loyaltyIconBase = '/public/assets/core/main/img/loyalty/';
$loyaltyIconForName = function ($name) use ($loyaltyIconBase) {
    $slug = strtolower(trim((string)$name));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    $slug = trim((string)$slug, '_');
    $allowed = ['silver', 'gold', 'platinum', 'diamond', 'master', 'grandmaster', 'challenger'];

    if (!in_array($slug, $allowed, true)) {
        $slug = 'silver';
    }

    return $loyaltyIconBase . $slug . '_icon.svg';
};
?>

<!-- ── Summary Cards ── -->
<div class="client-stats-grid">
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-users"></i></div>
        <div><div class="stat-label">Total Clients</div><div class="stat-value"><?= number_format((int)($stats['total'] ?? 0)) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-red"><i class="fa-duotone fa-ban"></i></div>
        <div><div class="stat-label">Banned</div><div class="stat-value"><?= number_format((int)($stats['banned'] ?? 0)) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-gray"><i class="fa-duotone fa-user-slash"></i></div>
        <div><div class="stat-label">Deleted</div><div class="stat-value"><?= number_format((int)($stats['deleted'] ?? 0)) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-blue"><i class="fa-duotone fa-coins"></i></div>
        <div><div class="stat-label">Total LB Coins</div><div class="stat-value"><?= number_format((float)($stats['total_coins'] ?? 0), 0) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-amber"><i class="fa-duotone fa-user-plus"></i></div>
        <div><div class="stat-label">New this Month</div><div class="stat-value"><?= number_format((int)($stats['new_month'] ?? 0)) ?></div></div>
    </div>
</div>

<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header-title">Clients List</h5>
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
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                            placeholder="Search Clients" aria-label="Search Clients">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
            <div class="col-auto">

            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Status Filter Bar -->
    <div class="status-filter-wrap">
        <label>Status</label>
        <button type="button" class="status-pill active-pill" data-status="">All</button>
        <button type="button" class="status-pill" data-status="active"><span class="pill-dot"></span> Active</button>
        <button type="button" class="status-pill banned-pill" data-status="banned"><span class="pill-dot"></span> Banned</button>
        <button type="button" class="status-pill banned-pill" data-status="deleted"><span class="pill-dot"></span> Deleted</button>

        <span class="filter-divider" aria-hidden="true"></span>
        <label>Loyalty Rank</label>
        <div class="loyalty-filter" id="loyaltyRankFilter">
            <button type="button" class="loyalty-filter-toggle" aria-haspopup="true" aria-expanded="false">
                <span id="loyaltyRankFilterLabel" class="loyalty-rank-choice"><i class="fa-duotone fa-chart-line text-primary"></i><span>All Loyalty Ranks</span></span>
                <i class="fa-duotone fa-chevron-down"></i>
            </button>
            <div class="loyalty-filter-menu" role="menu">
                <button type="button" class="loyalty-filter-option active" data-loyalty="" data-label="All Loyalty Ranks" role="menuitem">
                    <span class="loyalty-rank-choice"><i class="fa-duotone fa-chart-line text-primary"></i><span>All Loyalty Ranks</span></span>
                </button>
                <?php foreach ($loyaltyRanks as $rank): ?>
                    <?php
                        $rankId = (int)($rank['id'] ?? 0);
                        $rankName = trim((string)($rank['name'] ?? ''));
                        if ($rankId <= 0 || $rankName === '') {
                            continue;
                        }
                    ?>
                    <?php $rankIcon = $loyaltyIconForName($rankName); ?>
                    <button type="button" class="loyalty-filter-option" data-loyalty="<?= $rankId ?>" data-label="<?= htmlspecialchars($rankName, ENT_QUOTES, 'UTF-8') ?>" data-icon="<?= htmlspecialchars($rankIcon, ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                        <span class="loyalty-rank-choice">
                            <img src="<?= htmlspecialchars($rankIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($rankName, ENT_QUOTES, 'UTF-8') ?>">
                            <span><?= htmlspecialchars($rankName, ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
                                        "processing": true,
                                        "serverSide": true,
                                        "ajax": "<?= ADMN_URL ?>/clients/data",
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
                 }' id="clients_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Discord</th>
                    <th>Loyalty Rank</th>
                    <th>LB Coins</th>
                    <th class="text-end">Joined At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
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
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                            autocomplete="off" data-hs-tom-select-options='{
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

<?= $this->start('scripts') ?>
<script>
    $(document).on('ready', function () {
        // INITIALIZATION OF DATATABLES
        // =======================================================
        HSCore.components.HSDatatables.init($('#clients_table'), {
            language: {
                zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
            <p class="mb-0">No data to show</p>
            </div>`
            }
        });

        var dt = $('#clients_table').DataTable();
        var activeStatus = '';
        var activeLoyaltyRank = '';

        // Pass custom filters to every AJAX request
        $.fn.dataTable.ext.search.push(function() { return true; });
        dt.on('preXhr.dt', function(e, settings, data) {
            data.status = activeStatus;
            data.loyalty_rank = activeLoyaltyRank;
        });

        // Status pill clicks
        $('.status-filter-wrap .status-pill[data-status]').on('click', function() {
            $('.status-filter-wrap .status-pill[data-status]').removeClass('active-pill');
            $(this).addClass('active-pill');
            activeStatus = $(this).data('status') || '';
            dt.ajax.reload();
        });

        // Loyalty rank custom dropdown
        var $loyaltyFilter = $('#loyaltyRankFilter');
        var $loyaltyToggle = $loyaltyFilter.find('.loyalty-filter-toggle');
        var $loyaltyLabel = $('#loyaltyRankFilterLabel');

        $loyaltyToggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $loyaltyFilter.toggleClass('open');
            $loyaltyToggle.attr('aria-expanded', $loyaltyFilter.hasClass('open') ? 'true' : 'false');
        });

        $('.loyalty-filter-option').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.loyalty-filter-option').removeClass('active');
            $(this).addClass('active');
            activeLoyaltyRank = $(this).data('loyalty') || '';
            var selectedLabel = $(this).data('label') || 'All Loyalty Ranks';
            var selectedIcon = $(this).data('icon') || '';
            if (selectedIcon) {
                $loyaltyLabel.html('<img src="' + selectedIcon + '" alt="' + selectedLabel + '"><span>' + selectedLabel + '</span>');
            } else {
                $loyaltyLabel.html('<i class="fa-duotone fa-chart-line text-primary"></i><span>' + selectedLabel + '</span>');
            }
            $loyaltyFilter.removeClass('open');
            $loyaltyToggle.attr('aria-expanded', 'false');
            dt.ajax.reload();
        });

        $(document).on('click', function() {
            $loyaltyFilter.removeClass('open');
            $loyaltyToggle.attr('aria-expanded', 'false');
        });
    });
</script>
<?= $this->end() ?>