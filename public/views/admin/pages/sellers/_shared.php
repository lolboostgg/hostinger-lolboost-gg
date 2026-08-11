<?php
$fee            = $data['fee_percent'] ?? null;
$effectiveFee   = ($fee !== null && $fee !== '') ? (float)$fee : (float)$default_fee;
$sellerId       = (int)($data['id'] ?? 0);
$username       = htmlspecialchars((string)($data['username'] ?? ''), ENT_QUOTES);
$email          = htmlspecialchars((string)($data['email'] ?? ''), ENT_QUOTES);
$discord        = htmlspecialchars((string)($data['discord'] ?? ''), ENT_QUOTES);
$applicationNote = trim((string)($data['application_note'] ?? ''));
$balanceEuro    = number_format(((int)($data['balance'] ?? 0)) / 100, 2);
$isBanned       = (int)($data['is_banned'] ?? 0) === 1;
$isActive       = (int)($data['is_active'] ?? 0) === 1 && !$isBanned;
$onboardingStatus = strtolower(trim((string)($data['onboarding_status'] ?? 'pending')));
$isApproved     = ($onboardingStatus === 'approved');
$clientId       = (int)($data['client_id'] ?? 0);
$sellerBaseUrl  = ADMN_URL . '/seller/' . $sellerId;

if (!function_exists('admin_seller_game_meta')) {
    function admin_seller_game_meta(string $raw, string $dbName = '', string $dbIcon = ''): array {
        $slug = strtolower(trim($raw));
        $aliases = [
            'lol' => ['league-of-legends', 'League of Legends'],
            'league' => ['league-of-legends', 'League of Legends'],
            'leagu' => ['league-of-legends', 'League of Legends'],
            'league-of-legends' => ['league-of-legends', 'League of Legends'],
            'val' => ['valorant', 'Valorant'],
            'valorant' => ['valorant', 'Valorant'],
            'tft' => ['teamfight-tactics', 'Teamfight Tactics'],
            'teamfight-tactics' => ['teamfight-tactics', 'Teamfight Tactics'],
        ];
        $canonical = $aliases[$slug][0] ?? ($slug ?: strtolower(trim($dbName)));
        $label = trim($dbName) ?: ($aliases[$slug][1] ?? ucwords(str_replace(['-', '_'], ' ', $canonical)));
        $icon = trim($dbIcon);
        if ($icon === '' && function_exists('util_game_icon_url')) $icon = util_game_icon_url($canonical);
        return ['key' => $canonical, 'label' => $label ?: 'Game', 'icon' => $icon];
    }
}

if (!function_exists('admin_seller_asset_url')) {
    function admin_seller_asset_url($path): string {
        $path = trim((string)($path ?? ''));
        if ($path === '') return '';
        if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
        $path = preg_replace('#^/public/assets#i', '', $path);
        $path = '/' . ltrim((string)$path, '/');
        return defined('ASSET_URL') ? rtrim((string)ASSET_URL, '/') . $path : $path;
    }
}

if (!function_exists('admin_seller_first_image')) {
    function admin_seller_first_image($images): string {
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) return '';
        foreach ($images as $img) {
            $img = trim((string)$img);
            if ($img !== '') return $img;
        }
        return '';
    }
}

$avatarLetters  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($data['username'] ?? 'S')) ?: 'S', 0, 1));
$iconRaw        = trim((string)($data['icon'] ?? ''));
$avatarSrc      = '';
if ($iconRaw !== '') {
    $avatarSrc = preg_match('~^https?://~i', $iconRaw)
        ? $iconRaw
        : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($iconRaw, '/');
}

// Banner image
$bannerRaw = trim((string)($data['banner'] ?? ''));
$bannerSrc = '';
if ($bannerRaw !== '') {
    $bannerSrc = preg_match('~^https?://~i', $bannerRaw)
        ? $bannerRaw
        : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($bannerRaw, '/');
}

// Seller rank from DB
$sellerRank = trim((string)($data['rank'] ?? ''));
$rankConfigs = [
    'Beginner' => ['color' => '#8c98a4', 'bg' => 'rgba(109,116,123,.10)', 'border' => 'rgba(109,116,123,.22)', 'icon' => 'fa-seedling'],
    'Trusted'  => ['color' => '#09a5be', 'bg' => 'rgba(9,165,190,.12)',   'border' => 'rgba(9,165,190,.28)',   'icon' => 'fa-shield-check'],
    'Pro'      => ['color' => '#f5ca99', 'bg' => 'rgba(245,202,153,.12)', 'border' => 'rgba(245,202,153,.28)', 'icon' => 'fa-bolt'],
    'Elite'    => ['color' => '#55aaff', 'bg' => 'rgba(85,170,255,.12)',  'border' => 'rgba(85,170,255,.28)',  'icon' => 'fa-gem'],
    'Legend'   => ['color' => '#ffd700', 'bg' => 'rgba(255,215,0,.12)',   'border' => 'rgba(255,215,0,.28)',   'icon' => 'fa-crown'],
];
$rankCfg    = $rankConfigs[$sellerRank] ?? null;
$tierLabel  = $sellerRank;
$tierColor  = $rankCfg ? $rankCfg['color'] : '#8c98a4';
$tierBg     = $rankCfg ? $rankCfg['bg']    : 'rgba(109,116,123,.10)';
$tierBorder = $rankCfg ? $rankCfg['border']: 'rgba(109,116,123,.22)';
$tierIcon   = $rankCfg ? $rankCfg['icon']  : 'fa-seedling';

// Sales without buyer feedback after 24h appear as 5-star "No Feedback left."
// reviews — same source of truth as the public seller profile.
if (is_array($reviews ?? null) && $sellerId > 0 && function_exists('seller_no_feedback_entries')) {
    foreach (seller_no_feedback_entries($sellerId, 24) as $entry) {
        $reviews[] = [
            'id'              => 0,
            'seller_id'       => $sellerId,
            'client_username' => $entry['client_username'] ?? 'Guest',
            'client_icon'     => $entry['client_icon'] ?? '',
            'rating'          => 5,
            'comment'         => 'No Feedback left.',
            'approved'        => 1,
            'created_at'      => $entry['created_at'] ?? '',
            'is_placeholder'  => 1,
        ];
    }
    usort($reviews, static fn($a, $b) => strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? '')));
}

$accountCount       = is_array($accounts ?? null) ? count($accounts) : 0;
$itemCount          = is_array($items ?? null) ? count($items) : 0;
$topupCount         = is_array($topups ?? null) ? count($topups) : 0;
$digitalGoodCount   = is_array($digitalGoods ?? null) ? count($digitalGoods) : 0;
$soldCount          = is_array($accounts ?? null) ? count(array_filter($accounts, fn($a) => (int)($a['sold'] ?? 0) === 1)) : 0;
$listedCount        = max(0, $accountCount - $soldCount);
$itemSoldCount      = is_array($items ?? null) ? array_sum(array_map(fn($i) => (int)($i['sold_count'] ?? 0), $items)) : 0;
$topupSoldCount     = is_array($topups ?? null) ? array_sum(array_map(fn($t) => (int)($t['sold_count'] ?? 0), $topups)) : 0;
$totalListedCount   = $accountCount + $itemCount + $topupCount + $digitalGoodCount;
$totalSoldCount     = $soldCount + $itemSoldCount + $topupSoldCount;
$paymentCount       = is_array($payments ?? null) ? count($payments) : 0;
$pendingPayoutCount = is_array($payouts ?? null) ? count(array_filter($payouts, fn($r) => strtoupper((string)($r['status'] ?? '')) === 'PENDING')) : 0;
$reviewCount        = is_array($reviews ?? null) ? count($reviews) : 0;
$hiddenReviewCount  = is_array($reviews ?? null) ? count(array_filter($reviews, fn($r) => !(int)($r['approved'] ?? 1))) : 0;

if ($isBanned) {
    $statusLabel = 'Banned';   $statusClass = 'bg-soft-danger text-danger';     $statusIcon = 'fa-ban';
} elseif (!$isApproved) {
    $statusLabel = 'Pending';  $statusClass = 'bg-soft-warning text-warning';   $statusIcon = 'fa-clock';
} elseif ($isActive) {
    $statusLabel = 'Active';   $statusClass = 'bg-soft-success text-success';   $statusIcon = 'fa-circle-check';
} else {
    $statusLabel = 'Inactive'; $statusClass = 'bg-soft-secondary text-secondary'; $statusIcon = 'fa-circle-minus';
}
$primaryActionLabel = ($isApproved && $isActive && !$isBanned) ? 'Deactivate' : 'Approve';
$primaryActionIcon  = ($isApproved && $isActive && !$isBanned) ? 'fa-ban' : 'fa-check';
?>

<style>
.seller-profile-banner {
    min-height: 130px; position: relative;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,.12), transparent 24%),
        radial-gradient(circle at bottom right, rgba(59,130,246,.20), transparent 28%),
        linear-gradient(90deg, #0b1020 0%, #1d2b64 52%, #111827 100%);
}
.seller-profile-banner-glow {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, rgba(99,102,241,.32), rgba(124,58,237,.15), rgba(14,165,233,.22));
}
.seller-profile-meta { margin-top: -50px; }
.seller-profile-avatar {
    width: 96px; height: 96px; border-radius: 50%; margin: 0 auto;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 800; color: #60a5fa;
    background: linear-gradient(180deg, #2b3146 0%, #232938 100%);
    border: 4px solid #25282a; box-shadow: 0 12px 30px rgba(0,0,0,.28); overflow: hidden;
}
.seller-profile-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.seller-stat-pill {
    border: 1px solid rgba(255,255,255,.08); border-radius: .85rem;
    background: rgba(255,255,255,.03); padding: .85rem 1rem; text-align: center;
}
.seller-stat-value { font-size: 1.2rem; font-weight: 700; line-height: 1.1; }
.seller-stat-label { font-size: .72rem; letter-spacing: .04em; text-transform: uppercase; color: #91989e; margin-top: .25rem; }
/* Tabs */
.seller-tab-wrap { border-bottom: .0625rem solid #2f3235; margin-bottom: 1.5rem; }
.seller-nav-tabs { gap: .5rem; border-bottom: 0; }
.seller-nav-tabs .nav-link {
    border: 0; border-bottom: 2px solid transparent; color: #91989e;
    padding: .75rem .25rem; border-radius: 0; font-weight: 500; background: transparent;
    transition: color .15s, border-color .15s;
}
.seller-nav-tabs .nav-link:hover { color: #fff; border-bottom-color: rgba(92,74,227,.5); }
.seller-nav-tabs .nav-link.active { color: #fff; border-bottom-color: #5c4ae3; }
/* Action btns */
.seller-action-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .38rem .75rem; border-radius: .5rem; border: 1px solid transparent;
    font-size: .82rem; font-weight: 600; cursor: pointer;
    transition: background .15s, border-color .15s, color .15s; background: transparent;
}
.seller-action-btn--ghost { border-color: rgba(255,255,255,.12); color: rgba(255,255,255,.65); background: rgba(255,255,255,.04); }
.seller-action-btn--ghost:hover { border-color: rgba(255,255,255,.22); color: #fff; background: rgba(255,255,255,.08); }
.seller-action-btn--success { border-color: rgba(0,201,167,.35); color: #00c9a7; background: rgba(0,201,167,.08); }
.seller-action-btn--success:hover { border-color: rgba(0,201,167,.6); background: rgba(0,201,167,.15); }
.seller-action-btn--danger { border-color: rgba(237,76,120,.30); color: #ed4c78; background: rgba(237,76,120,.07); }
.seller-action-btn--danger:hover { border-color: rgba(237,76,120,.55); background: rgba(237,76,120,.13); }
.seller-action-divider { width: 1px; height: 22px; background: rgba(255,255,255,.10); margin: 0 .15rem; flex-shrink: 0; }
/* Filter pills */
.filter-bar {
    display: flex; align-items: center; gap: .4rem;
    padding: .75rem 1.3125rem; border-bottom: .0625rem solid #2f3235;
    background: rgba(0,0,0,.08); flex-wrap: wrap;
}
.filter-bar label { font-size: .75rem; color: #91989e; margin: 0 .2rem 0 0; }
.fpill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: 50rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1px solid #2f3235; background: transparent; color: #91989e; transition: all .15s;
}
.fpill:hover { color: #c5c8cc; border-color: #4b5055; }
.fpill.active { color: #1e2022; background: #00c9a7; border-color: #00c9a7; }
.fpill.fpill-sold.active { color: #fff; background: #5c4ae3; border-color: #5c4ae3; }
.fpill.fpill-listed.active { color: #1e2022; background: #f5ca99; border-color: #f5ca99; }
.fpill.fpill-deduct.active { color: #fff; background: #ed4c78; border-color: #ed4c78; }
.fpill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.form-section-title {
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
    color: #91989e; padding-bottom: .5rem; border-bottom: .0625rem solid #2f3235; margin-bottom: 1rem;
}
.seller-tier-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .65rem; border-radius: 20px;
    font-size: .72rem; font-weight: 700;
}
@media (max-width:991.98px) {
    .seller-profile-meta { margin-top: -36px; }
    .seller-profile-avatar { width: 84px; height: 84px; font-size: 1.7rem; }
}
.seller-game-filter{position:relative}.seller-game-trigger{min-width:210px;display:flex;align-items:center;gap:9px;justify-content:flex-start;background:#191d21;border:1px solid #343a40;color:#dce2ea}.seller-game-trigger:after{content:"\f078";font-family:"Font Awesome 6 Pro";font-size:11px;margin-left:auto;color:#82909f}.seller-game-menu{position:absolute;z-index:1080;left:0;top:calc(100% + 6px);min-width:240px;padding:7px;background:#202428;border:1px solid #3a4148;border-radius:10px;box-shadow:0 14px 35px rgba(0,0,0,.35);display:none}.seller-game-filter.open .seller-game-menu{display:block}.seller-game-option{width:100%;border:0;background:transparent;color:#cbd3dc;padding:9px 10px;border-radius:7px;display:flex;align-items:center;gap:10px;text-align:left}.seller-game-option:hover,.seller-game-option.active{background:#5b46e8;color:#fff}.seller-game-icon{width:22px;height:22px;object-fit:cover;border-radius:6px;flex:0 0 22px}.seller-game-placeholder{width:22px;height:22px;border-radius:6px;background:#30363c;display:inline-flex;align-items:center;justify-content:center;flex:0 0 22px}.seller-game-cell{display:flex;align-items:center;gap:9px;white-space:nowrap}.dataTables_paginate{display:block!important}
</style>

<!-- ── Profile Header Card ── -->
<div class="card mb-4 overflow-hidden">
    <div class="seller-profile-banner" <?php if ($bannerSrc): ?>style="background-image:url('<?= htmlspecialchars($bannerSrc, ENT_QUOTES) ?>');background-size:cover;background-position:center;"<?php endif; ?>>
        <div class="seller-profile-banner-glow"></div>
    </div>
    <div class="card-body pt-0">
        <!-- Action buttons -->
        <div class="d-flex justify-content-end mt-3 mb-0">
            <div class="d-flex align-items-center gap-1">
                <?php if (!$isBanned): ?>
                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                        <input type="hidden" name="action" value="admin_toggle_seller_status">
                        <input type="hidden" name="id" value="<?= $sellerId ?>">
                        <button type="submit" class="seller-action-btn <?= $isActive ? 'seller-action-btn--danger' : 'seller-action-btn--success' ?>">
                            <i class="fa-duotone <?= $primaryActionIcon ?>"></i>
                            <span><?= $primaryActionLabel ?></span>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!$isBanned): ?>
                    <button type="button" class="seller-action-btn seller-action-btn--danger"
                            data-bs-toggle="modal" data-bs-target="#banSellerModal">
                        <i class="fa-duotone fa-ban"></i><span>Ban</span>
                    </button>
                <?php else: ?>
                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                        <input type="hidden" name="action" value="admin_unban_seller">
                        <input type="hidden" name="id" value="<?= $sellerId ?>">
                        <button type="submit" class="seller-action-btn seller-action-btn--success">
                            <i class="fa-duotone fa-circle-check"></i><span>Unban</span>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="seller-action-divider"></div>

                <button type="button" class="seller-action-btn seller-action-btn--success"
                        data-bs-toggle="modal" data-bs-target="#addMoneyModal">
                    <i class="fa-duotone fa-circle-plus"></i><span>Add Money</span>
                </button>
                <button type="button" class="seller-action-btn seller-action-btn--danger"
                        data-bs-toggle="modal" data-bs-target="#fineAccountModal">
                    <i class="fa-duotone fa-triangle-exclamation"></i><span>Fine</span>
                </button>
            </div>
        </div>

        <!-- Avatar + name + stats -->
        <div class="text-center seller-profile-meta">
            <div class="seller-profile-avatar">
                <?php if ($avatarSrc): ?>
                    <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES) ?>" alt="<?= $username ?>">
                <?php else: ?>
                    <span><?= $avatarLetters ?></span>
                <?php endif; ?>
            </div>
            <div class="d-inline-flex align-items-center justify-content-center gap-2 flex-wrap mt-3">
                <h2 class="page-header-title mb-0"><?= $username ?: 'Seller' ?></h2>
                <span class="badge <?= $statusClass ?>">
                    <i class="fa-duotone <?= $statusIcon ?> me-1"></i><?= $statusLabel ?>
                </span>
                <?php if ($tierLabel !== ''): ?>
                <span class="seller-tier-badge" style="background:<?= $tierBg ?>;color:<?= $tierColor ?>;border:1px solid <?= $tierBorder ?>;">
                    <i class="fa-duotone <?= $tierIcon ?>"></i><?= htmlspecialchars($tierLabel, ENT_QUOTES) ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="text-muted mt-2 small d-flex align-items-center justify-content-center gap-3 flex-wrap">
                <span><i class="fa-duotone fa-store me-1"></i>Seller Account</span>
                <span><i class="fa-duotone fa-hashtag me-1"></i><?= $sellerId ?></span>
                <?php if ($email): ?>
                    <span><i class="fa-duotone fa-envelope me-1"></i><?= $email ?></span>
                <?php endif; ?>
                <?php if ($clientId > 0): ?>
                    <span><i class="fa-duotone fa-user me-1"></i>Client
                        <a href="<?= ADMN_URL ?>/client/<?= $clientId ?>">#<?= $clientId ?></a>
                    </span>
                <?php endif; ?>
            </div>
            <div class="row g-2 justify-content-center mt-4">
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value">€<?= $balanceEuro ?></div>
                        <div class="seller-stat-label">Balance</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value text-info"><?= $accountCount ?></div>
                        <div class="seller-stat-label">Accounts</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value text-warning"><?= $itemCount ?></div>
                        <div class="seller-stat-label">Items</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value text-primary"><?= $topupCount ?></div>
                        <div class="seller-stat-label">Top Ups</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value text-success"><?= $totalSoldCount ?></div>
                        <div class="seller-stat-label">Sold</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="seller-stat-pill">
                        <div class="seller-stat-value"><?= $effectiveFee ?>%</div>
                        <div class="seller-stat-label">Platform Fee</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Navigation Tabs ── -->
<div class="seller-tab-wrap">
    <ul class="nav seller-nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'profile'   ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/profile">Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'accounts'  ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/accounts">
                Accounts <span class="badge bg-soft-secondary text-secondary ms-1"><?= $accountCount ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'items' ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/items">
                Items <span class="badge bg-soft-secondary text-secondary ms-1"><?= $itemCount ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'topups' ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/topups">
                Top Ups <span class="badge bg-soft-secondary text-secondary ms-1"><?= $topupCount ?></span>
            </a>
        </li>
        <?php if ($digitalGoodCount > 0): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($activeTab ?? '') === 'digital-goods' ? 'active' : '' ?>"
                   href="<?= $sellerBaseUrl ?>/digital-goods">
                    Digital Goods <span class="badge bg-soft-secondary text-secondary ms-1"><?= $digitalGoodCount ?></span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'payouts'   ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/payouts">
                Payouts <span class="badge bg-soft-warning text-warning ms-1"><?= $pendingPayoutCount ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'payout-methods' ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/payout-methods">
                Payout Methods
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'payments'  ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/payments">
                Payments <span class="badge bg-soft-secondary text-secondary ms-1"><?= $paymentCount ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($activeTab ?? '') === 'reviews' ? 'active' : '' ?>"
               href="<?= $sellerBaseUrl ?>/reviews">
                Reviews <span class="badge bg-soft-secondary text-secondary ms-1"><?= $reviewCount ?></span>
            </a>
        </li>
    </ul>
</div>

<!-- ── Adjust Balance Modal ── -->
<div class="modal fade" id="addMoneyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="admin_adjust_seller_balance">
                <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
                <input type="hidden" name="mode" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Money</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select class="form-select" name="reason">
                            <option value="order_completion">Completed Order</option>
                            <option value="private_order">Private Order</option>
                            <option value="payment_error">Payment Error</option>
                            <option value="client_tip">Client Tip</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="alert alert-soft-info mb-4">
                        Current balance: <strong>€<?= $balanceEuro ?></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (€)</label>
                        <input type="number" min="0.01" step="0.01" class="form-control" name="amount"
                               placeholder="0.00" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" name="note" placeholder="Reason for adjustment">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Money</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Ban Seller Modal ── -->
<div class="modal fade" id="fineAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="admin_adjust_seller_balance">
                <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
                <input type="hidden" name="mode" value="fine">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-duotone fa-triangle-exclamation me-2 text-danger"></i>Fine Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-soft-danger mb-4">The fine is deducted from the current balance immediately.</div>
                    <div class="mb-3"><label class="form-label">Reason</label><input type="text" class="form-control" name="reason" placeholder="Reason for the fine" required></div>
                    <div class="mb-3"><label class="form-label">Note <span class="text-muted">(optional)</span></label><textarea class="form-control" name="note" rows="2" placeholder="Additional details"></textarea></div>
                    <div><label class="form-label">Fine Amount (€)</label><input type="number" min="0.01" step="0.01" class="form-control" name="amount" placeholder="0.00" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-gavel me-1"></i>Apply Fine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="banSellerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="admin_ban_seller">
                <input type="hidden" name="id" value="<?= $sellerId ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-duotone fa-ban me-2 text-danger"></i>Ban Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-soft-danger mb-4">
                        Banning this seller will immediately block access to the seller dashboard.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <select class="form-select" name="reason" required>
                            <option value="">Select a reason…</option>
                            <option value="fraud">Fraud / Scam</option>
                            <option value="tos_violation">Terms of Service Violation</option>
                            <option value="chargebacks">Chargebacks / Payment Abuse</option>
                            <option value="inactivity">Inactivity / Account Cleanup</option>
                            <option value="duplicate">Duplicate Account</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Details / Note</label>
                        <textarea class="form-control" name="details" rows="3"
                                  placeholder="Optional additional context…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-duotone fa-ban me-1"></i>Ban Seller
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
