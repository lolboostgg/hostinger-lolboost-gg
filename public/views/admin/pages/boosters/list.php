<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Boosters List - Admin Area | LoLBoost.gg', 'h1' => 'Boosters List', 'description' => 'View the Boosters List.']]) ?>
<style>
/* Wider booster admin pages: reduce the large left/right gutters on desktop. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>


<style>
/* ─── Theme tokens (from theme-dark_min.css) ───────────────────
   body bg:       #1e2022
   card bg:       #25282a
   border:        #2f3235
   body text:     #c5c8cc
   success/teal:  #00c9a7
   danger:        #ed4c78
   primary:       #5c4ae3
   warning:       #f5ca99
   info:          #09a5be
   muted:         #91989e
──────────────────────────────────────────────────────────────── */

/* ── Summary Cards ── */
.booster-stats-grid {
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
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,.35);
}
.stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.stat-icon.c-teal   { background: rgba(0,201,167,.13);   color: #00c9a7; }
.stat-icon.c-blue   { background: rgba(9,165,190,.13);   color: #09a5be; }
.stat-icon.c-red    { background: rgba(237,76,120,.13);  color: #ed4c78; }
.stat-icon.c-orange { background: rgba(245,166,35,.13);  color: #f5a623; }
.stat-icon.c-amber  { background: rgba(245,202,153,.13); color: #f5ca99; }

.stat-label { font-size: .7rem; color: #91989e; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .1rem; }
.stat-value { font-size: 1.3rem; font-weight: 700; color: #c5c8cc; line-height: 1.2; }

/* ── Rank Badges ── */
.rank-badge {
    display: inline-flex; align-items: center;
    padding: .22rem .6rem;
    border-radius: 20px;
    font-size: .73rem;
    font-weight: 600;
    white-space: nowrap;
}
.rank-challenger  { background: rgba(255,215,0,.12);    color: #ffd700; border: 1px solid rgba(255,215,0,.28); }
.rank-grandmaster { background: rgba(237,76,120,.13);   color: #ed4c78; border: 1px solid rgba(237,76,120,.28); }
.rank-master      { background: rgba(180,120,255,.12);  color: #c09bff; border: 1px solid rgba(180,120,255,.28); }
.rank-mythic      { background: rgba(92,74,227,.13);    color: #9b8bf0; border: 1px solid rgba(92,74,227,.30); }
.rank-diamond     { background: rgba(85,170,255,.12);   color: #55aaff; border: 1px solid rgba(85,170,255,.28); }
.rank-emerald     { background: rgba(0,201,167,.12);    color: #00c9a7; border: 1px solid rgba(0,201,167,.28); }
.rank-platinum    { background: rgba(9,165,190,.12);    color: #09a5be; border: 1px solid rgba(9,165,190,.28); }
.rank-gold        { background: rgba(245,202,153,.12);  color: #f5ca99; border: 1px solid rgba(245,202,153,.28); }
.rank-silver      { background: rgba(150,170,190,.10);  color: #96aabe; border: 1px solid rgba(150,170,190,.25); }
.rank-bronze      { background: rgba(180,110,60,.12);   color: #c07840; border: 1px solid rgba(180,110,60,.25); }
.rank-iron        { background: rgba(120,120,120,.10);  color: #909090; border: 1px solid rgba(120,120,120,.25); }
.rank-elite       { background: rgba(9,165,190,.12);    color: #09a5be; border: 1px solid rgba(9,165,190,.28); }
.rank-rookie      { background: rgba(109,116,123,.13);  color: #8c98a4; border: 1px solid rgba(109,116,123,.30); }
.rank-default     { background: rgba(109,116,123,.10);  color: #6d747b; border: 1px solid rgba(109,116,123,.20); }

/* ── Status pill filter buttons ── */
.status-filter-wrap {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    row-gap: .5rem;
    gap: .4rem;
    padding: .75rem 1.3125rem;
    border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.10);
}
.status-filter-wrap label {
    font-size: .75rem;
    color: #91989e;
    margin: 0;
    margin-right: .2rem;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    white-space: nowrap;
    gap: .3rem;
    padding: .28rem .75rem;
    border-radius: 50rem;
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .15s ease;
    background: transparent;
    color: #91989e;
    border-color: #2f3235;
}
.status-pill:hover { color: #c5c8cc; border-color: #4b5055; }
.status-pill.active-pill {
    color: #1e2022;
    background: #00c9a7;
    border-color: #00c9a7;
}
.status-pill.banned-pill.active-pill {
    color: #fff;
    background: #ed4c78;
    border-color: #ed4c78;
}
.status-pill.inactive-pill.active-pill {
    color: #1e2022;
    background: #f5a623;
    border-color: #f5a623;
}
.booster-status-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .25rem .55rem;
    border-radius: .35rem;
    font-size: .72rem;
    font-weight: 700;
    line-height: 1;
}
.booster-status-active { background: #00c9a7; color: #fff; }
.booster-status-inactive { background: #f5a623; color: #1e2022; }
.booster-status-banned { background: #ed4c78; color: #fff; }
.booster-reactivate-form {
    display: inline-flex;
    margin-left: .45rem;
}
.booster-reactivate-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .55rem;
    border: 1px solid rgba(0,201,167,.45);
    border-radius: .4rem;
    background: rgba(0,201,167,.12);
    color: #00c9a7;
    font-size: .7rem;
    font-weight: 700;
    line-height: 1.1;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}
.booster-reactivate-btn:hover,
.booster-reactivate-btn:focus {
    background: #00c9a7;
    border-color: #00c9a7;
    color: #1e2022;
}

/* Game filter — compact dropdown instead of a pill wall (game catalog can be large) */
.game-filter { position: relative; min-width: 200px; }
.game-filter-toggle {
    width: 100%; display: flex; align-items: center; justify-content: space-between; gap: .65rem;
    min-height: 34px; padding: .32rem .75rem; border-radius: .7rem; border: 1px solid #2f3235;
    background: linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.015));
    color: #c5c8cc; font-size: .78rem; font-weight: 700; cursor: pointer;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.04); transition: all .15s ease;
}
.game-filter-toggle:hover, .game-filter.open .game-filter-toggle { border-color: #5c4ae3; color: #a99bff; }
.game-filter-toggle i.fa-chevron-down { font-size: .72rem; transition: transform .15s ease; }
.game-filter.open .game-filter-toggle i.fa-chevron-down { transform: rotate(180deg); }
.game-filter-choice { display: flex; align-items: center; gap: .5rem; min-width: 0; }
.game-filter-choice img { width: 16px; height: 16px; object-fit: contain; border-radius: 3px; flex: 0 0 auto; }
.game-filter-choice span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.game-filter-menu {
    position: absolute; top: calc(100% + 8px); left: 0; min-width: 260px; z-index: 25; display: none;
    border-radius: .85rem; border: 1px solid #34383c; background: #1e2022; box-shadow: 0 18px 45px rgba(0,0,0,.42);
}
.game-filter.open .game-filter-menu { display: block; }
.game-filter-search { display: flex; align-items: center; gap: .5rem; padding: .6rem .75rem; border-bottom: 1px solid #2f3235; color: #6d747b; }
.game-filter-search input { flex: 1; min-width: 0; border: 0; background: transparent; color: #c5c8cc; font-size: .8rem; outline: none; }
.game-filter-options { max-height: 280px; overflow: auto; padding: .4rem; }
.game-filter-option {
    width: 100%; display: flex; align-items: center; gap: .55rem; padding: .5rem .6rem;
    border: 0; border-radius: .62rem; background: transparent; color: #91989e;
    font-size: .78rem; font-weight: 700; text-align: left; cursor: pointer; transition: all .15s ease;
}
.game-filter-option:hover { background: rgba(124,92,255,.08); color: #c5c8cc; }
.game-filter-option.active { background: rgba(124,92,255,.14); color: #a99bff; }
@media (max-width: 575.98px) { .game-filter { width: 100%; } }
.status-pill .pill-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: .7;
}

/* ── Game icons in table ── */
.game-icons {
    display: flex;
    align-items: center;
    gap: .35rem;
}
.game-icon-wrap {
    position: relative;
    display: inline-flex;
}
.game-icon-wrap img {
    width: 22px;
    height: 22px;
    object-fit: contain;
    border-radius: 4px;
    opacity: .55;
    transition: opacity .15s;
}
.game-icon-wrap img.active-game {
    opacity: 1;
}
.game-icon-wrap .game-count {
    position: absolute;
    bottom: -4px;
    right: -5px;
    background: #2f3235;
    color: #c5c8cc;
    font-size: .5rem;
    font-weight: 700;
    line-height: 1;
    padding: 1px 3px;
    border-radius: 20px;
    border: 1px solid #25282a;
}
</style>

<?php
/* ── rank → CSS class ── */
function rank_css_class(string $rank): string {
    static $map = [
        'challenger'  => 'rank-challenger',
        'grandmaster' => 'rank-grandmaster',
        'master'      => 'rank-master',
        'mythic'      => 'rank-mythic',
        'diamond'     => 'rank-diamond',
        'emerald'     => 'rank-emerald',
        'platinum'    => 'rank-platinum',
        'gold'        => 'rank-gold',
        'silver'      => 'rank-silver',
        'bronze'      => 'rank-bronze',
        'iron'        => 'rank-iron',
        'elite'       => 'rank-elite',
        'rookie'      => 'rank-rookie',
    ];
    return $map[strtolower(trim($rank))] ?? 'rank-default';
}

/* ── Balance: Cent → Euro ── */
function cents_to_euro(int $cents): string {
    return number_format($cents / 100, 2, '.', ',');
}

/* ── Summary stats ── */
$totalBoosters      = count($data);
$activeBoosters     = 0;
$inactiveBoosters   = 0;
$bannedBoosters     = 0;
$totalBalanceCents  = 0;
$newThisMonth       = 0;
$monthStart         = strtotime(date('Y-m-01'));
$inactiveThreshold  = strtotime('-14 days');

foreach ($data as $row) {
    $isBanned = !empty($row['is_banned']);
    $lastCheckTs = !empty($row['last_order_check'])
        ? strtotime($row['last_order_check'])
        : false;
    $isInactive = !$isBanned && ($lastCheckTs === false || $lastCheckTs < $inactiveThreshold);

    if ($isBanned) {
        $bannedBoosters++;
    } elseif ($isInactive) {
        $inactiveBoosters++;
    } else {
        $activeBoosters++;
    }

    $totalBalanceCents += (int)($row['balance'] ?? 0);
    if (!empty($row['created_at']) && strtotime($row['created_at']) >= $monthStart) {
        $newThisMonth++;
    }
}
?>

<!-- ── Summary Cards ── -->
<div class="booster-stats-grid">
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-users"></i></div>
        <div>
            <div class="stat-label">Total Boosters</div>
            <div class="stat-value"><?= $totalBoosters ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-blue"><i class="fa-duotone fa-circle-check"></i></div>
        <div>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?= $activeBoosters ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-orange"><i class="fa-duotone fa-clock"></i></div>
        <div>
            <div class="stat-label">Inactive</div>
            <div class="stat-value"><?= $inactiveBoosters ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-red"><i class="fa-duotone fa-ban"></i></div>
        <div>
            <div class="stat-label">Banned</div>
            <div class="stat-value"><?= $bannedBoosters ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-teal"><i class="fa-duotone fa-euro-sign"></i></div>
        <div>
            <div class="stat-label">Total Balance</div>
            <div class="stat-value">€<?= cents_to_euro($totalBalanceCents) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon c-amber"><i class="fa-duotone fa-user-plus"></i></div>
        <div>
            <div class="stat-label">New this Month</div>
            <div class="stat-value"><?= $newThisMonth ?></div>
        </div>
    </div>
</div>

<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <h5 class="card-header-title">Boosters List</h5>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-merge input-group-flush">
                    <div class="input-group-prepend input-group-text">
                        <i class="fa-duotone fa-search"></i>
                    </div>
                    <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search Boosters" aria-label="Search Boosters">
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="status-filter-wrap">
        <label>Status</label>
        <button type="button" class="status-pill active-pill" data-status="">All</button>
        <button type="button" class="status-pill" data-status="active">
            <span class="pill-dot" style="background:#00c9a7;opacity:1"></span>Active
        </button>
        <button type="button" class="status-pill inactive-pill" data-status="inactive">
            <span class="pill-dot" style="background:#f5a623;opacity:1"></span>Inactive
        </button>
        <button type="button" class="status-pill banned-pill" data-status="banned">
            <span class="pill-dot" style="background:#ed4c78;opacity:1"></span>Banned
        </button>

        <span style="width:.0625rem;height:1.2rem;background:#2f3235;margin:0 .4rem;display:inline-block;align-self:center;"></span>

        <label>Game</label>
        <?php
        // Only offer games that at least one booster actually has assigned — not the full games
        // catalog (which can hold hundreds of entries unrelated to boosting).
        $_boosterGameSlugs = [];
        foreach ($data as $_bRow) {
            $_gRaw = strtolower(trim($_bRow['games'] ?? ''));
            if ($_gRaw === '') continue;
            foreach (preg_split('/[,|]+/', $_gRaw, -1, PREG_SPLIT_NO_EMPTY) as $_gs) {
                $_gs = trim($_gs);
                if ($_gs !== '') $_boosterGameSlugs[$_gs] = true;
            }
        }
        $_gameFilterOptions = [];
        foreach (array_keys($_boosterGameSlugs) as $_gs) {
            $_gameFilterOptions[$_gs] = [
                'label' => function_exists('util_game_display_name') ? util_game_display_name($_gs) : strtoupper($_gs),
                'icon'  => function_exists('util_game_icon_url') ? util_game_icon_url($_gs) : '',
            ];
        }
        uasort($_gameFilterOptions, fn($a, $b) => strcasecmp($a['label'], $b['label']));
        ?>
        <div class="game-filter" id="boosterGameFilter">
            <button type="button" class="game-filter-toggle" aria-haspopup="true" aria-expanded="false">
                <span id="boosterGameFilterLabel" class="game-filter-choice"><i class="fa-duotone fa-gamepad-modern"></i><span>All Games</span></span>
                <i class="fa-duotone fa-chevron-down"></i>
            </button>
            <div class="game-filter-menu" role="menu">
                <div class="game-filter-search"><i class="fa-duotone fa-search"></i><input type="text" placeholder="Search game..." id="boosterGameFilterSearch"></div>
                <div class="game-filter-options">
                    <button type="button" class="game-filter-option active" data-game="" data-label="All Games" role="menuitem">
                        <span class="game-filter-choice"><i class="fa-duotone fa-gamepad-modern"></i><span>All Games</span></span>
                    </button>
                    <?php foreach ($_gameFilterOptions as $_gs => $_opt): ?>
                    <button type="button" class="game-filter-option" data-game="<?= htmlspecialchars($_gs, ENT_QUOTES, 'UTF-8') ?>" data-label="<?= htmlspecialchars($_opt['label'], ENT_QUOTES, 'UTF-8') ?>" data-icon="<?= htmlspecialchars($_opt['icon'], ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                        <span class="game-filter-choice">
                            <?php if ($_opt['icon'] !== ''): ?><img src="<?= htmlspecialchars($_opt['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="fa-duotone fa-gamepad-modern"></i><?php endif; ?>
                            <span><?= htmlspecialchars($_opt['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
                    "columnDefs": [{"targets": [7], "orderable": false}],
                    "order": [[5, "desc"]],
                    "info": {"totalQty": "#datatableEntriesInfoTotalQty"},
                    "entries": "#datatableEntries",
                    "search": "#datatableWithSearchInput",
                    "isResponsive": false,
                    "isShowPaging": false,
                    "pagination": "datatableWithSearchPagination"
               }' id="boosters_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Discord</th>
                    <th>Status</th>
                    <th class="text-end">Balance</th>
                    <th class="text-end">Joined At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row):
                    $rankName       = htmlspecialchars($row['name'] ?? '');
                    $rankClass      = rank_css_class($row['name'] ?? '');
                    $isBanned       = !empty($row['is_banned']);
                    $lastCheckTs    = !empty($row['last_order_check'])
                        ? strtotime($row['last_order_check'])
                        : false;
                    $isInactive     = !$isBanned && ($lastCheckTs === false || $lastCheckTs < $inactiveThreshold);
                    $statusAttr     = $isBanned ? 'banned' : ($isInactive ? 'inactive' : 'active');
                    $statusLabel    = $isBanned ? 'Banned' : ($isInactive ? 'Inactive' : 'Active');
                    $statusCssClass = $isBanned
                        ? 'booster-status-banned'
                        : ($isInactive ? 'booster-status-inactive' : 'booster-status-active');
                    $joinedTs       = strtotime($row['created_at'] ?? '');
                    $joinedDate   = $joinedTs ? date('Y-m-d', $joinedTs) : '';
                    $balanceCents = (int)($row['balance'] ?? 0);

                    // Parse games column: e.g. "lol", "lol,tft", "val|lol|tft"
                    $gamesRaw  = strtolower(trim($row['games'] ?? ''));
                    $gamesList = preg_split('/[,|]+/', $gamesRaw, -1, PREG_SPLIT_NO_EMPTY);
                    $gamesList = array_map('trim', $gamesList);
                    $hasLol = in_array('lol', $gamesList);
                    $hasVal = in_array('val', $gamesList) || in_array('valorant', $gamesList);
                    $hasTft = in_array('tft', $gamesList);
                    $hasClassic = in_array('lol_classic', $gamesList) || in_array('lol-classic', $gamesList);
                    $dynamicGamesForRow = array_diff($gamesList, ['lol', 'val', 'valorant', 'tft', 'lol_classic', 'lol-classic']);
                ?>
                    <tr data-status="<?= $statusAttr ?>" data-games="<?= htmlspecialchars($gamesRaw) ?>">
                        <td class="fw-500">
                            <a href="<?= ADMN_URL ?>/booster/<?= $row['id'] ?>">
                                #<?= $row['id'] ?>
                            </a>
                        </td>
                        <td>
                            <span class="rank-badge <?= $rankClass ?>">
                                <?= $rankName ?>
                            </span>
                        </td>
                        <td class="fw-500">
                            <div class="d-flex align-items-center gap-2">
                                <?= util_format_user($row['username'], $row['icon']) ?>
                                <?php if ($isInactive): ?>
                                    <form class="ajax-form booster-reactivate-form"
                                          action="<?= AJAX_URL ?>"
                                          method="POST"
                                          onsubmit="return confirm('Verify this booster and reactivate order claiming?');">
                                        <input type="hidden" name="action" value="admin_mark_booster_checked">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit"
                                                class="booster-reactivate-btn"
                                                title="Verify booster and reactivate order claiming">
                                            <i class="fa-duotone fa-user-check"></i>
                                            Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <div class="game-icons">
                                    <?php if ($hasLol): ?>
                                    <div class="game-icon-wrap" title="League of Legends">
                                        <img src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="LoL" class="active-game">
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($hasVal): ?>
                                    <div class="game-icon-wrap" title="Valorant">
                                        <img src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt="VAL" class="active-game">
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($hasTft): ?>
                                    <div class="game-icon-wrap" title="Teamfight Tactics">
                                        <img src="<?= ASSET_URL ?>/website/images/icons/teamfight-tactics.png" alt="TFT" class="active-game">
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($hasClassic): ?>
                                    <div class="game-icon-wrap" title="LoL Classic">
                                        <img src="<?= htmlspecialchars(util_game_icon_url('lol_classic'), ENT_QUOTES, 'UTF-8') ?>" alt="LoL Classic" class="active-game">
                                    </div>
                                    <?php endif; ?>
                                    <?php foreach ($dynamicGamesForRow as $_dg): $_dgIcon = util_game_icon_url($_dg); if ($_dgIcon === '') continue; ?>
                                    <div class="game-icon-wrap" title="<?= htmlspecialchars(util_game_display_name($_dg), ENT_QUOTES, 'UTF-8') ?>">
                                        <img src="<?= htmlspecialchars($_dgIcon, ENT_QUOTES, 'UTF-8') ?>" alt="" class="active-game">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </td>
                        <td class="fw-500">
                            <?= htmlspecialchars($row['discord'] ?? '') ?>
                        </td>
                        <td class="fw-500">
                            <span class="booster-status-badge <?= $statusCssClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="fw-500 text-end" data-order="<?= $balanceCents ?>">
                            €<?= cents_to_euro($balanceCents) ?>
                        </td>
                        <td class="fw-500 text-end" data-order="<?= $row['created_at'] ?>">
                            <?= util_format_date_display($row['created_at']) ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= ADMN_URL ?>/booster/<?= $row['id'] ?>" class="btn btn-white btn-sm">
                                <i class="fa-duotone fa-eye me-1 fs-6"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                                data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {

    // ── 1. Init DataTables ──────────────────────────────────────
    HSCore.components.HSDatatables.init($('#boosters_table'), {
        language: {
            zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg"
                   alt="" style="width:10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg"
                   alt="" style="width:10rem;" data-hs-theme-appearance="dark">
              <p class="mb-0">No data to show</p>
            </div>`
        }
    });

    var dt = $('#boosters_table').DataTable();

    // ── 2. Custom filter ────────────────────────────────────────
    var activeStatus = '';
    var activeGame   = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'boosters_table') return true;
        var $row = $(dt.row(dataIndex).node());

        // Status filter
        if (activeStatus && ($row.data('status') || '') !== activeStatus) return false;

        // Game filter
        if (activeGame) {
            var games = ($row.data('games') || '').split(/[,|]+/).map(function(g){ return g.trim(); });
            if (games.indexOf(activeGame) === -1) return false;
        }

        return true;
    });

    // ── 3. Status pill clicks ───────────────────────────────────
    $('.status-pill').on('click', function () {
        $('.status-pill').removeClass('active-pill');
        $(this).addClass('active-pill');
        activeStatus = $(this).data('status');
        dt.draw();
    });

    // ── 4. Game filter dropdown ──────────────────────────────────
    var $gameFilter = $('#boosterGameFilter');
    var $gameToggle = $gameFilter.find('.game-filter-toggle');
    var $gameLabel  = $('#boosterGameFilterLabel');

    $gameToggle.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $gameFilter.toggleClass('open');
        $gameToggle.attr('aria-expanded', $gameFilter.hasClass('open') ? 'true' : 'false');
    });

    $('.game-filter-option').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.game-filter-option').removeClass('active');
        $(this).addClass('active');
        activeGame = $(this).data('game') || '';
        var label = $(this).data('label') || 'All Games';
        var icon  = $(this).data('icon') || '';
        $gameLabel.html((icon ? '<img src="' + icon + '" alt="">' : '<i class="fa-duotone fa-gamepad-modern"></i>') + '<span>' + label + '</span>');
        $gameFilter.removeClass('open');
        $gameToggle.attr('aria-expanded', 'false');
        dt.draw();
    });

    $('#boosterGameFilterSearch').on('input', function () {
        var q = this.value.trim().toLowerCase();
        $('.game-filter-option').each(function () {
            var label = ($(this).data('label') || '').toString().toLowerCase();
            $(this).toggle(label.indexOf(q) !== -1);
        });
    });

    $(document).on('click', function () {
        $gameFilter.removeClass('open');
        $gameToggle.attr('aria-expanded', 'false');
    });
});
</script>
<?= $this->end() ?>
