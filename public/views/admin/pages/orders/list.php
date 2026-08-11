<?php
$allowedEmails = [
    'r.machmueller@gmx.de',
    'nimm2oder3@gmx.de',
    'hbilalshah@gmail.com',
    'duck_sauce@live.de',
    'lovely@lolboost.gg'
];

// Game filter options — only games that actually have at least one order, not the whole games catalog.
$_adminOrderGameOptions = [];
try {
    global $db;
    $_gameRows = $db->run(
        "SELECT DISTINCT game FROM boost_forms WHERE status = 1 AND game IS NOT NULL AND game != '' ORDER BY game ASC"
    ) ?: [];
    $_knownGameLabels = ['lol' => 'LoL', 'league-of-legends' => 'LoL', 'val' => 'VAL', 'valorant' => 'VAL', 'tft' => 'TFT', 'teamfight-tactics' => 'TFT'];
    $_knownGameIcons = [
        'lol' => ASSET_URL . '/website/images/icons/league-of-legends.png',
        'league-of-legends' => ASSET_URL . '/website/images/icons/league-of-legends.png',
        'val' => ASSET_URL . '/website/images/icons/valorant.png',
        'valorant' => ASSET_URL . '/website/images/icons/valorant.png',
        'tft' => ASSET_URL . '/website/images/icons/teamfight-tactics.png',
        'teamfight-tactics' => ASSET_URL . '/website/images/icons/teamfight-tactics.png',
    ];
    foreach ($_gameRows as $_gr) {
        $_gRaw = strtolower(trim((string)($_gr['game'] ?? '')));
        if ($_gRaw === '') continue;
        $_gIsClassic = $_gRaw === 'lol_classic' || $_gRaw === 'lol-classic' || str_contains($_gRaw, 'classic');
        $_gSlug = $_gIsClassic ? 'lol_classic' : (in_array($_gRaw, ['lol', 'league-of-legends'], true) ? 'lol' : (in_array($_gRaw, ['val', 'valorant'], true) ? 'val' : (in_array($_gRaw, ['tft', 'teamfight-tactics'], true) ? 'tft' : $_gRaw)));
        if (isset($_adminOrderGameOptions[$_gSlug])) continue;
        $_adminOrderGameOptions[$_gSlug] = [
            'label' => $_gIsClassic ? 'LoL Classic' : ($_knownGameLabels[$_gSlug] ?? (function_exists('util_game_display_name') ? util_game_display_name($_gSlug) : strtoupper($_gSlug))),
            'icon'  => $_gIsClassic ? (ASSET_URL . '/website/images/icons/lol-classic.png') : ($_knownGameIcons[$_gSlug] ?? (function_exists('util_game_icon_url') ? util_game_icon_url($_gSlug) : '')),
        ];
    }
} catch (Throwable $e) {}
uasort($_adminOrderGameOptions, fn($a, $b) => strcasecmp($a['label'], $b['label']));
?>



<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Orders List - Admin Area | LoLBoost.gg', 'h1' => 'Orders List', 'description' => 'Manage and edit the Orders List.']]) ?>
<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">

<style>
  /* Desktop status pills (filter) */

  :root{
    --lb-control-bg: rgba(255,255,255,.06);
    --lb-control-border: rgba(255,255,255,.10);
    --lb-control-inset: 0 10px 26px rgba(0,0,0,.35) inset;
  }
  /* Status pills — always visible for quick one-click filtering (admin uses this constantly) */
  .order-status-pills{
    display:inline-flex;
    align-items:center;
    gap:.25rem;
    padding:.28rem;
    border-radius:999px;
    background:var(--lb-control-bg);
    border:1px solid var(--lb-control-border);
    box-shadow:var(--lb-control-inset);
    max-width:100%;
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
  }
  .order-status-pills::-webkit-scrollbar{height:0}
  .order-status-pills .pill{
    appearance:none;
    border:0;
    background:transparent;
    color:rgba(255,255,255,.70);
    border-radius:999px;
    padding:.42rem .78rem;
    font-size:.80rem;
    line-height:1;
    font-weight:700;
    white-space:nowrap;
    cursor:pointer;
    transition:background-color .15s ease,color .15s ease,box-shadow .15s ease,transform .05s ease;
  }
  .order-status-pills .pill::before{
    content:"";
    width:7px;
    height:7px;
    border-radius:999px;
    display:inline-block;
    margin-right:.45rem;
    background:var(--pill-dot, rgba(255,255,255,.35));
    box-shadow:0 0 0 2px rgba(0,0,0,.25);
    transform:translateY(-.5px);
  }
  .order-status-pills .pill.pill-any::before{ display:none; }
  .order-status-pills .pill:hover{ background:rgba(255,255,255,.06); color:rgba(255,255,255,.92); }
  .order-status-pills .pill:active{ transform:translateY(1px); }
  .order-status-pills .pill:focus-visible{ outline:none; box-shadow:0 0 0 .2rem rgba(var(--bs-primary-rgb), .22); }
  .order-status-pills .pill.is-active{
    color:#fff;
    background:linear-gradient(180deg, rgba(var(--bs-primary-rgb), .55), rgba(var(--bs-primary-rgb), .22));
    box-shadow:0 0 0 1px rgba(var(--bs-primary-rgb), .45) inset, 0 10px 26px rgba(0,0,0,.30);
  }
  .order-status-pills .pill.is-active::before{ background:var(--pill-dot, #fff); }

  /* Compact dropdown filter (Game only — the games catalog can be large) */
  .lb-dropfilter{ position:relative; min-width:180px; }
  .lb-dropfilter-toggle{
    width:100%; display:flex; align-items:center; justify-content:space-between; gap:.6rem;
    height:2.35rem; padding:0 .85rem; border-radius:999px;
    background:var(--lb-control-bg); border:1px solid var(--lb-control-border); box-shadow:var(--lb-control-inset);
    color:rgba(255,255,255,.86); font-size:.80rem; font-weight:700; cursor:pointer; transition:.15s ease;
  }
  .lb-dropfilter-toggle:hover,
  .lb-dropfilter.open .lb-dropfilter-toggle{ border-color:rgba(var(--bs-primary-rgb),.45); color:#fff; }
  .lb-dropfilter-toggle i.fa-chevron-down{ font-size:.68rem; color:rgba(255,255,255,.45); transition:transform .15s ease; }
  .lb-dropfilter.open .lb-dropfilter-toggle i.fa-chevron-down{ transform:rotate(180deg); }
  .lb-dropfilter-choice{ display:flex; align-items:center; gap:.5rem; min-width:0; }
  .lb-dropfilter-choice img{ width:16px; height:16px; object-fit:contain; border-radius:3px; flex:0 0 auto; }
  .lb-dropfilter-choice .lb-dropfilter-dot{ width:8px; height:8px; border-radius:50%; flex:0 0 auto; background:var(--dot,rgba(255,255,255,.4)); }
  .lb-dropfilter-choice span{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .lb-dropfilter-menu{
    position:absolute; top:calc(100% + 8px); left:0; min-width:240px; z-index:60; display:none;
    border-radius:14px; border:1px solid rgba(255,255,255,.10); background:#25282a; box-shadow:0 20px 50px rgba(0,0,0,.48);
  }
  .lb-dropfilter.open .lb-dropfilter-menu{ display:block; }
  .lb-dropfilter-options{ max-height:300px; overflow:auto; padding:.4rem; }
  .lb-dropfilter-option{
    width:100%; display:flex; align-items:center; gap:.55rem; padding:.5rem .6rem;
    border:0; border-radius:9px; background:transparent; color:rgba(255,255,255,.7);
    font-size:.78rem; font-weight:700; text-align:left; cursor:pointer; transition:.15s ease;
  }
  .lb-dropfilter-option:hover{ background:rgba(255,255,255,.06); color:#fff; }
  .lb-dropfilter-option.is-active{ background:rgba(var(--bs-primary-rgb),.18); color:#fff; }
  .lb-dropfilter-option img{ width:16px; height:16px; object-fit:contain; border-radius:3px; flex:0 0 auto; }
  .lb-dropfilter-option .lb-dropfilter-dot{ width:8px; height:8px; border-radius:50%; flex:0 0 auto; }
  @media (max-width:575.98px){ .lb-dropfilter{ width:100%; } }

  /* Status badge (like Customer Orders List: outlined pill + dot + uppercase) */
  .lb-status{
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
  }
  .lb-status__dot{
    width:7px;height:7px;border-radius:50%;
    background:currentColor;opacity:.95;flex:0 0 auto;
  }
  .lb-status.status-inprogress{ color:#4ea1ff; border-color:rgba(78,161,255,.25); background:rgba(78,161,255,.12); }
  .lb-status.status-completed { color:#1fe6c6; border-color:rgba(31,230,198,.22); background:rgba(31,230,198,.10); }
  .lb-status.status-paused    { color:#ffc44d; border-color:rgba(255,196,77,.22); background:rgba(255,196,77,.10); }
  .lb-status.status-unpaid    { color:#ff6b6b; border-color:rgba(255,107,107,.20); background:rgba(255,107,107,.10); }


/* Delete icon inside UNPAID status (visible on hover only) */
.lb-status{ position:relative; }
.lb-status__delete{
  margin-left:.35rem;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:22px;height:22px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.14);
  background:rgba(0,0,0,.10);
  color:currentColor;
  opacity:0;
  transform:scale(.95);
  transition:opacity .12s ease, transform .12s ease, background .12s ease;
  cursor:pointer;
  line-height:1;
}
.lb-status__delete i{ font-size:.95rem; }
.lb-status.status-unpaid:hover .lb-status__delete{
  opacity:1;
  transform:scale(1);
  background:rgba(0,0,0,.22);
}
.lb-status__delete:focus{ outline:none; box-shadow:0 0 0 2px rgba(255,255,255,.14); opacity:1; }
  .lb-status.status-paid      { color:#b18cff; border-color:rgba(177,140,255,.22); background:rgba(177,140,255,.10); }
  .lb-status.status-processing{ color:#b18cff; border-color:rgba(177,140,255,.22); background:rgba(177,140,255,.10); }
  .lb-status.status-refund   { color:#ff8a4c; border-color:rgba(255,138,76,.22); background:rgba(255,138,76,.10); }

  .lb-status.status-waitingapproval{ color:#a78bfa; border-color:rgba(167,139,250,.24); background:rgba(167,139,250,.10); }

  /* Game badge overlay on boost-form icon (bottom-right) */
  .lb-iconhost{ position:relative !important; overflow:visible !important; display:inline-flex; }
  .lb-game-badge{
    position:absolute;
    right:-6px;
    bottom:-6px;
    width:20px;
    height:20px;
    border-radius:50%;
    border:2px solid rgba(25,28,33,.98);
    background:rgba(0,0,0,.22);
    box-shadow:0 10px 22px rgba(0,0,0,.45);
    object-fit:cover;
    pointer-events:none;
  }

  /* Table polish */
  #orders_table thead th{
    font-size:.72rem;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:rgba(255,255,255,.70);
  }
  #orders_table tbody td{ padding-top:1rem; padding-bottom:1rem; }
  #orders_table tbody tr{ transition:background-color .15s ease; }
  #orders_table tbody tr:hover{ background:rgba(255,255,255,.03); }

  /* Search input (rounded like the pills) */
.orders-search{
  display:flex;
  align-items:center;
  gap:.35rem;

  border:1px solid var(--lb-control-border);
  border-radius:999px !important;

  background:var(--lb-control-bg);
  box-shadow:var(--lb-control-inset);

  padding:.18rem .55rem;
  min-height:2.35rem;

  overflow:hidden;
}
.orders-search .input-group-text{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.55);
  padding:0 .35rem 0 .55rem;
}
.orders-search .form-control{
  border:0 !important;
  background:transparent !important;
  color:rgba(255,255,255,.90);
  padding:0;
  min-width:12rem;
  border-radius:999px !important;
}
.orders-search .form-control::placeholder{ color:rgba(255,255,255,.45); }
.orders-search:focus-within{
  border-color:rgba(var(--bs-primary-rgb), .55);
  box-shadow:
    var(--lb-control-inset),
    0 0 0 .20rem rgba(var(--bs-primary-rgb), .15);
}


  /* ===== LoLBoost-style option pills (Admin Orders List) ===== */
  .lb-meta-row{ display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.5rem; }
  .lb-pill{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.18rem .6rem;
    border-radius:999px;
    font-size:.72rem;
    line-height:1;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.12);
    color:rgba(255,255,255,.88);
    white-space:nowrap;
  }
  .lb-pill .lb-dot{ width:.38rem; height:.38rem; border-radius:999px; background:rgba(255,255,255,.55); }
  .lb-pill-opt i{ font-size:.86rem; opacity:.9; }
  .lb-svgico{ width:14px; height:14px; display:inline-block; object-fit:contain; }
  .lb-pill-more{ opacity:.85; }

  .lb-oid-row{ display:flex; align-items:center; gap:.45rem; }
  .lb-oid-link{ color:#fff; text-decoration:none; font-weight:700; }
  .lb-oid-link:hover{ text-decoration:underline; }
  .lb-copy-btn{
    width:28px; height:28px;
    display:inline-flex; align-items:center; justify-content:center;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.85);
  }
  .lb-copy-btn:hover{ background:rgba(255,255,255,.08); }

  .lb-orderid-sub{ display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.55rem; }

  .lb-hoverwrap{ position:relative; z-index: 20; }
  .lb-hovercard{
    position:absolute;
    left:0;
    top:calc(100% + 10px);
    min-width:220px;
    max-width:360px;
    padding:.75rem .8rem;
    border-radius:14px;
    background:rgba(25,28,33,.98);
    border:1px solid rgba(255,255,255,.10);
    box-shadow:0 18px 40px rgba(0,0,0,.55);
    display:none;
    z-index:200000; /* above pagination/footer */
  }
  .lb-hoverwrap:hover .lb-hovercard{ display:block; }

  /* Table: keep horizontal scroll on mobile, but allow hovercards to escape vertically */
  .table-responsive.datatable-custom{
    overflow-x:auto !important;
    overflow-y:visible !important;
    -webkit-overflow-scrolling:touch;
  }
  #orders_table{ position:relative; z-index:1; }
  #orders_table tbody{ position:relative; z-index:2; }
  .card-footer{ position:relative; z-index:0; }

  /* Mobile: show extras as blocks inside the card */
  .lb-mobile-extra{ margin-top:.6rem; }
  .lb-mobile-extra-label{ font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.65); margin-bottom:.25rem; }
  .lb-mobile-extra-body{ display:flex; flex-wrap:wrap; gap:.35rem; }

      /* --- Page tweaks: wider content on desktop (reduce left/right gutters) --- */
    @media (min-width: 992px) {
        .content.container {
           max-width: 100%;
           padding-right: 2rem;
           padding-left: 2rem;
        }
    }

  .lb-admin-r5s-boosters {
    display:flex;
    flex-direction:column;
    gap:.35rem;
    min-width:0;
  }
  .lb-admin-r5s-booster {
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    width:max-content;
    max-width:100%;
    color:inherit;
    text-decoration:none;
  }
  .lb-admin-r5s-booster small {
    display:inline-flex;
    align-items:center;
    padding:.12rem .45rem;
    border-radius:999px;
    background:rgba(124,92,255,.16);
    border:1px solid rgba(124,92,255,.24);
    color:rgba(255,255,255,.72);
    font-size:.68rem;
    font-weight:900;
  }

</style>
<?= $this->end() ?>
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
                    <div class="input-group input-group-merge orders-search">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                            placeholder="Search orders" aria-label="Search orders">
                    </div>
                    <!-- End Search -->
                </form>
                <!-- End Filter -->
            </div>
            <div class="col-auto">
                <div class="d-flex align-items-center flex-wrap gap-2">

                    <!-- Status filter — always-visible pills for quick one-click access -->
                    <div class="order-status-pills js-order-status-pills"
                         role="tablist"
                         aria-label="Filter by status">
                        <button type="button" class="pill pill-any is-active" data-value="null" aria-pressed="true">Any</button>
                        <button type="button" class="pill" style="--pill-dot:#b18cff" data-value="Processing" aria-pressed="false">Processing</button>
                        <button type="button" class="pill" style="--pill-dot:#ff6b6b" data-value="Unpaid" aria-pressed="false">Unpaid</button>
                        <button type="button" class="pill" style="--pill-dot:#ff8a4c" data-value="Refunded" aria-pressed="false">Refunded</button>
                        <button type="button" class="pill" style="--pill-dot:#4ea1ff" data-value="In Progress" aria-pressed="false">In Progress</button>
                        <button type="button" class="pill" style="--pill-dot:#a78bfa" data-value="WAITING_FOR_APPROVAL" aria-pressed="false">Waiting for Approval</button>
                        <button type="button" class="pill" style="--pill-dot:#ffc44d" data-value="Paused" aria-pressed="false">Paused</button>
                        <button type="button" class="pill" style="--pill-dot:#1fe6c6" data-value="Completed" aria-pressed="false">Completed</button>
                    </div>

                    <!-- Game filter — only games that actually have orders -->
                    <div class="lb-dropfilter" id="adminOrdersGameFilter" data-default-icon="fa-duotone fa-gamepad-modern">
                        <button type="button" class="lb-dropfilter-toggle" aria-haspopup="true" aria-expanded="false">
                            <span class="lb-dropfilter-choice" id="adminOrdersGameFilterLabel"><i class="fa-duotone fa-gamepad-modern"></i><span>All Games</span></span>
                            <i class="fa-duotone fa-chevron-down"></i>
                        </button>
                        <div class="lb-dropfilter-menu" role="menu">
                            <div class="lb-dropfilter-options">
                                <button type="button" class="lb-dropfilter-option is-active" data-value="null" data-label="All Games" role="menuitem">All Games</button>
                                <?php foreach ($_adminOrderGameOptions as $_gSlug => $_gOpt): ?>
                                <button type="button" class="lb-dropfilter-option" data-value="<?= htmlspecialchars($_gSlug, ENT_QUOTES, 'UTF-8') ?>" data-label="<?= htmlspecialchars($_gOpt['label'], ENT_QUOTES, 'UTF-8') ?>" data-icon="<?= htmlspecialchars($_gOpt['icon'], ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                                    <?php if ($_gOpt['icon'] !== ''): ?><img src="<?= htmlspecialchars($_gOpt['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
                                    <?= htmlspecialchars($_gOpt['label'], ENT_QUOTES, 'UTF-8') ?>
                                </button>
                                <?php endforeach; ?>
                                <button type="button" class="lb-dropfilter-option" data-value="egirl" data-label="E-Girl" data-icon="<?= ASSET_URL ?>/website/images/gg-girl.svg" role="menuitem">
                                    <img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt=""> E-Girl
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End Header -->



    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
            data-hs-datatables-options='{
                                        "processing": true,
                                        "serverSide": true,
                                        "ajax": "<?= ADMN_URL ?>/orders/data",
                                        "columnDefs": [{
                                                "targets": [6],
                                                "orderable": false
                                        }],
                                     "order": [
                                                [5, "desc"]
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
                    <th>Title</th>
                    <th>Order ID</th>
                    <th>Booster</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Created At</th>
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
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
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
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    $(document).on('ready', function () {
        // INITIALIZATION OF DATATABLES
        // =======================================================
        HSCore.components.HSTomSelect.init('.js-select');

        HSCore.components.HSDatatables.init($('#orders_table'), {
            language: {
                zeroRecords: `<div class="text-center p-4">
                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
                        <p class="mb-0">No data to show</p>
                        </div>`
            }
        });

        // Extra server-side filter handled via the game dropdown's preXhr handler below
        window.__waitingForApproval = 0;

        // --- Filters (compact dropdowns) ---
        const targetTable = 'orders_table';
        let activeGame = 'null';

        function applyStatusFilter(val) {
            const dt = HSCore.components.HSDatatables.getItem(targetTable);
            if (!dt) return;

            // Special: Waiting for Approval is NOT a status string in DB.
            // We filter server-side via extra POST param: waiting_for_approval=1
            if (val === 'WAITING_FOR_APPROVAL') {
                window.__waitingForApproval = 1;
                // clear status column search so it doesn't conflict
                dt.column(3).search('').draw();
                return;
            }

            window.__waitingForApproval = 0;
            dt.column(3).search(val !== 'null' ? val : '').draw();
        }

        function initDropFilter(rootId, onSelect) {
            const root = document.getElementById(rootId);
            if (!root) return;
            const toggle = root.querySelector('.lb-dropfilter-toggle');
            const label  = root.querySelector('.lb-dropfilter-choice[id]');

            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.lb-dropfilter.open').forEach(x => { if (x !== root) x.classList.remove('open'); });
                root.classList.toggle('open');
                toggle.setAttribute('aria-expanded', root.classList.contains('open') ? 'true' : 'false');
            });

            root.querySelectorAll('.lb-dropfilter-option').forEach(function (opt) {
                opt.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    root.querySelectorAll('.lb-dropfilter-option').forEach(o => o.classList.remove('is-active'));
                    opt.classList.add('is-active');

                    const val  = opt.dataset.value || 'null';
                    const lbl  = opt.dataset.label || 'All';
                    const icon = opt.dataset.icon || '';
                    const dot  = opt.dataset.dot || '';
                    let html = '';
                    if (icon) html = '<img src="' + icon + '" alt="">';
                    else if (dot) html = '<span class="lb-dropfilter-dot" style="background:' + dot + '"></span>';
                    else html = '<i class="' + (root.dataset.defaultIcon || 'fa-duotone fa-circle') + '"></i>';
                    if (label) label.innerHTML = html + '<span>' + lbl + '</span>';

                    root.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                    onSelect(val);
                });
            });
        }

        document.addEventListener('click', function () {
            document.querySelectorAll('.lb-dropfilter.open').forEach(x => x.classList.remove('open'));
        });

        // Status pills — always visible, one click to filter
        const statusPillsWrap = document.querySelector('.js-order-status-pills');
        if (statusPillsWrap) {
            statusPillsWrap.addEventListener('click', function (e) {
                const btn = e.target.closest('button.pill');
                if (!btn) return;
                statusPillsWrap.querySelectorAll('button.pill').forEach(b => {
                    const isActive = (b === btn);
                    b.classList.toggle('is-active', isActive);
                    b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
                applyStatusFilter(btn.getAttribute('data-value') || 'null');
            });
        }

        initDropFilter('adminOrdersGameFilter', function (val) {
            activeGame = val;
            const dt = HSCore.components.HSDatatables.getItem(targetTable);
            if (dt) dt.draw();
        });

        // Pass active game to server on every request
        $('#orders_table').on('preXhr.dt', function (e, settings, data) {
            data.waiting_for_approval = window.__waitingForApproval ? 1 : 0;
            data.game_filter = (activeGame && activeGame !== 'null') ? activeGame : '';
        });

        // --- Status badge rendering (match Customer Orders List) ---
        function normalizeStatus(raw) {
            let s = (raw ?? '').toString();
            s = s.replace(/<[^>]*>/g, '').trim(); // strip html

            // normalize: underscores/hyphens -> spaces, collapse whitespace
            const u = s
                .toUpperCase()
                .replace(/[_-]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            const map = {
                'IN PROGRESS': ['In Progress', 'status-inprogress'],
                'WAITING FOR APPROVAL': ['Waiting for Approval', 'status-waitingapproval'],
                'AWAITING APPROVAL': ['Waiting for Approval', 'status-waitingapproval'],
                'PENDING APPROVAL': ['Waiting for Approval', 'status-waitingapproval'],
                'PROCESSING': ['Processing', 'status-processing'],
                'UNPAID': ['Unpaid', 'status-unpaid'],
                'REFUND': ['Refunded', 'status-refund'],
                'REFUNDED': ['Refunded', 'status-refund'],
                'PAUSED': ['Paused', 'status-paused'],
                'COMPLETED': ['Completed', 'status-completed'],

                // show paid orders as "Processing" in this list (per filter pills)
                'PAID': ['Processing', 'status-processing'],
            };

            if (map[u]) return { label: map[u][0], cls: map[u][1] };

            // fallback pretty label
            const label = u
                .toLowerCase()
                .replace(/(^|\s)[a-z]/g, m => m.toUpperCase());

            return { label: label || 'Unknown', cls: 'status-processing' };
        }

        
function applyStatusBadges() {
    const dt = HSCore.components.HSDatatables.getItem(targetTable);
    if (!dt) return;

    dt.rows({ page: 'current' }).every(function () {
        const rowNode = this.node();
        if (!rowNode) return;

        const statusTd = rowNode.querySelector('td:nth-child(4)');
        if (!statusTd) return;

        const raw = statusTd.textContent || '';
        const { label, cls } = normalizeStatus(raw);

        // Render pill
        statusTd.innerHTML =
            '<span class="lb-status ' + cls + '">' +
                '<span class="lb-status__dot" aria-hidden="true"></span>' +
                '<span>' + label + '</span>' +
            '</span>';

        // If UNPAID: move the delete control into the status pill (visible on hover only)
        if (cls === 'status-unpaid' || String(label).toUpperCase() === 'UNPAID') {
            const actionTd = rowNode.querySelector('td:nth-child(7)');
            if (!actionTd) return;

            const del = actionTd.querySelector('.js-admin-delete-order');
            if (!del) return;

            const orderId = del.getAttribute('data-order-id') || (del.dataset ? del.dataset.orderId : null);
            const orderStatus = del.getAttribute('data-order-status') || (del.dataset ? del.dataset.orderStatus : null) || 'UNPAID';

            // Remove the original delete button from the Action column
            del.remove();

            // Add compact delete icon button into the status pill
            const pill = statusTd.querySelector('.lb-status');
            if (pill && orderId && !pill.querySelector('.lb-status__delete')) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'lb-status__delete js-admin-delete-order';
                btn.setAttribute('title', 'Delete');
                btn.setAttribute('aria-label', 'Delete order #' + orderId);
                btn.dataset.orderId = orderId;
                btn.dataset.orderStatus = orderStatus;
                btn.innerHTML = '<i class="fa-duotone fa-trash-can"></i>';
                pill.appendChild(btn);
            }
        }
    });
}


function applyGameBadges() {
    const dt = HSCore.components.HSDatatables.getItem(targetTable);
    if (!dt) return;

    // Map game key -> icon
    const pickIcon = (gameRaw) => {
        const g = String(gameRaw || '').toLowerCase();
        if (!g || g === 'egirl' || g === 'gamer-girl' || g.includes('e-girl') || g.includes('gamer girl')) return null;

        // Exact matches only. Substring checks like g.includes('league') used to misfire on
        // "rocket-league" (contains "league") and render the LoL icon for Rocket League orders.
        const isClassic = g === 'lol_classic' || g === 'lol-classic' || g === 'league-of-legends-classic';
        const isTft = g === 'tft' || g === 'teamfight-tactics';
        const isVal = g === 'val' || g === 'valorant';
        const isLol = g === 'lol' || g === 'league-of-legends' || g === 'league' || g === 'leagu' || g === 'leag';

        if (isClassic) return { url: '/public/assets/website/images/icons/lol-classic.png', alt: 'League of Legends Classic' };
        if (isTft) return { url: '/public/assets/website/images/icons/teamfight-tactics.png', alt: 'Teamfight Tactics' };
        if (isVal) return { url: '/public/assets/website/images/icons/valorant.png', alt: 'Valorant' };
        if (isLol) return { url: '/public/assets/website/images/icons/league-of-legends.png', alt: 'League of Legends' };

        return null;
    };

    dt.rows({ page: 'current' }).every(function () {
        const rowNode = this.node();
        if (!rowNode) return;

        const titleTd = rowNode.querySelector('td:nth-child(1)');
        if (!titleTd) return;

        const wrap = titleTd.querySelector('.lb-titlewrap[data-game]');
        if (!wrap || wrap.dataset.badgeApplied === '1') return;

        // Known games (LoL/VAL/TFT/Classic) use the hardcoded assets; any other game
        // (dynamically added via the admin Games area) falls back to its own icon URL,
        // rendered server-side into data-game-icon.
        let icon = pickIcon(wrap.dataset.game);
        if (!icon && wrap.dataset.gameIcon) {
            icon = { url: wrap.dataset.gameIcon, alt: wrap.dataset.game || 'Game' };
        }
        if (!icon) { wrap.dataset.badgeApplied = '1'; return; }

        // Find the icon host (try common patterns)
        let host =
            wrap.querySelector('.avatar') ||
            wrap.querySelector('.avatar-sm') ||
            wrap.querySelector('.media') ||
            null;

        // Fallback: parent of the first <img> inside the title cell
        if (!host) {
            const img = wrap.querySelector('img');
            if (img && img.parentElement) host = img.parentElement;
        }

        if (!host) { wrap.dataset.badgeApplied = '1'; return; }

        host.classList.add('lb-iconhost');

        // Avoid duplicates
        if (!host.querySelector('.lb-game-badge')) {
            const badge = document.createElement('img');
            badge.className = 'lb-game-badge';
            badge.src = icon.url;
            badge.alt = icon.alt;
            badge.title = icon.alt;
            host.appendChild(badge);
        }

        wrap.dataset.badgeApplied = '1';
    });
}


// run on initial load + every draw + every draw
        const dtInstance = HSCore.components.HSDatatables.getItem(targetTable);
        if (dtInstance) {
            applyStatusBadges();
            applyGameBadges();
            dtInstance.on('draw', function () { applyStatusBadges(); applyGameBadges(); });
        }

        // Quick delete (UNPAID only) from Orders List
        $(document).on('click', '.js-admin-delete-order', function () {
            const orderId = $(this).data('order-id');
            const status  = String($(this).data('order-status') || '').toUpperCase();
            const isEgirl = String(orderId).toLowerCase().startsWith('eg_');

            if (!orderId) return;

            if (status !== 'UNPAID') {
                alert('This order cannot be deleted.');
                return;
            }

            const label = isEgirl ? 'E-Girl order #' + orderId : 'order #' + orderId;
            const ok = confirm('Delete ' + label + '?\n\nOnly UNPAID orders can be deleted.');
            if (!ok) return;

            $.ajax({
                url: '/ajax',
                method: 'POST',
                dataType: 'text',
                data: {
                    action: isEgirl ? 'admin_delete_egirl_order' : 'admin_delete_order',
                    order_id: orderId
                }
            }).done(function (raw) {
                let res = null;

                // Try to parse JSON, but don't break the page if the response isn't JSON
                try {
                    res = JSON.parse(raw);
                } catch (e) {
                    // If we got HTML (redirect/login), just reload to let the browser handle it
                    window.location.reload();
                    return;
                }

                if (res && res.redirectUrl) {
                    window.location.href = res.redirectUrl;
                    return;
                }

                const dt = HSCore.components.HSDatatables.getItem(targetTable);
                if (dt) dt.ajax.reload(null, false);

                if (res && res.sendToast && res.sendToast.message) {
                    alert(res.sendToast.message);
                }
            }).fail(function (xhr) {
                // Show a bit more info for debugging
                console.error('Delete failed:', xhr.status, xhr.responseText);
                alert('Could not delete order.');
            });
});


    });
</script>
<?= $this->end() ?>
<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('.lb-copy-btn');
  if(!btn) return;
  const val = btn.getAttribute('data-copy') || '';
  if(!val) return;
  navigator.clipboard.writeText(val).then(()=>{
    btn.classList.add('copied');
    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    setTimeout(()=>{ btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; btn.classList.remove('copied'); }, 900);
  }).catch(()=>{});
});
</script>
