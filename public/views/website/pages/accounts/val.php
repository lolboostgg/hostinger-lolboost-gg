<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'premium-accounts-page']) ?>

<section class="lb-shop-hero">
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon" aria-hidden="true"><i class="fa-solid fa-helmet-battle"></i></div>
        <div>
            <div class="lb-shop-hero__kicker">Accounts</div>
            <h1 class="lb-shop-hero__title"><?= t('Valorant Smurf Accounts') ?></h1>
            <p class="lb-shop-hero__desc"><?= t('Buy Valorant smurf accounts ready for instant play. Fast delivery, secure login details, and trusted support after purchase.') ?></p>
        </div>
    </div>
</section>
<style id="lb-premium-smurf-shop-hero-final">
body.premium-accounts-page{background:#0e0c1c!important;}
body.premium-accounts-page .lb-shop-hero{
  position:relative!important;
  overflow:hidden!important;
  background:#0e0c1c!important;
  border:0!important;
  border-bottom:0!important;
  margin:0!important;
  padding:0!important;
}
body.premium-accounts-page .lb-shop-hero::before{display:none!important;content:none!important;}
body.premium-accounts-page .lb-shop-hero__inner{
  width:100%!important;
  max-width:1500px!important;
  min-height:170px!important;
  margin:0 auto!important;
  padding:36px 28px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:flex-start!important;
  gap:22px!important;
  border-radius:0!important;
  background:transparent!important;
  border:0!important;
  box-shadow:none!important;
  overflow:visible!important;
}
body.premium-accounts-page .lb-shop-hero__icon{
  width:74px!important;
  height:74px!important;
  min-width:74px!important;
  border-radius:20px!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(255,255,255,.10)!important;
  box-shadow:0 18px 50px rgba(0,0,0,.28)!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  overflow:hidden!important;
}
body.premium-accounts-page .lb-shop-hero__icon i{font-size:30px!important;color:#7c6cff!important;}
body.premium-accounts-page .lb-shop-hero__kicker{display:block!important;font-size:12px!important;letter-spacing:.13em!important;text-transform:uppercase!important;color:#8b9bff!important;font-weight:900!important;margin:0 0 8px!important;line-height:1.15!important;}
body.premium-accounts-page .lb-shop-hero__title{margin:0!important;font-size:29px!important;line-height:1.12!important;font-weight:950!important;letter-spacing:-.03em!important;color:#fff!important;text-align:left!important;text-transform:none!important;}
body.premium-accounts-page .lb-shop-hero__desc{margin:8px 0 0!important;max-width:640px!important;font-size:15px!important;line-height:1.45!important;color:#a9adc4!important;font-weight:600!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;text-align:left!important;}
body.premium-accounts-page .container{padding-top:34px!important;}
body.premium-accounts-page .account-type-cards{margin-top:0!important;margin-bottom:26px!important;position:relative!important;z-index:1!important;}
body.premium-accounts-page .shop-filterbar{margin-top:0!important;}
main > .lb-shop-hero:first-child,
.page-zoom > main > .lb-shop-hero:first-child{
  margin-top:var(--lb-content-top, 0px)!important;
}
@media(max-width:760px){
  body.premium-accounts-page .lb-shop-hero{overflow:visible!important;background:#0e0c1c!important;border-bottom:0!important;}
  body.premium-accounts-page .lb-shop-hero__inner{width:100%!important;max-width:100%!important;min-width:0!important;display:grid!important;grid-template-columns:42px minmax(0,1fr)!important;align-items:flex-start!important;gap:10px!important;min-height:0!important;padding:14px 16px 24px!important;margin:0!important;overflow:visible!important;}
  body.premium-accounts-page .lb-shop-hero__inner > div:last-child{min-width:0!important;width:100%!important;max-width:100%!important;overflow:visible!important;}
  body.premium-accounts-page .lb-shop-hero__icon{width:40px!important;height:40px!important;min-width:40px!important;border-radius:12px!important;margin-top:2px!important;}
  body.premium-accounts-page .lb-shop-hero__icon i{font-size:19px!important;}
  body.premium-accounts-page .lb-shop-hero__kicker{display:block!important;margin:0 0 4px!important;font-size:10px!important;line-height:1.15!important;white-space:normal!important;overflow:visible!important;}
  body.premium-accounts-page .lb-shop-hero__title{display:block!important;width:100%!important;max-width:none!important;margin:0!important;font-size:18px!important;line-height:1.22!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;overflow-wrap:break-word!important;word-break:normal!important;text-align:left!important;}
  body.premium-accounts-page .lb-shop-hero__desc{display:block!important;width:100%!important;max-width:none!important;margin:5px 0 0!important;font-size:12.5px!important;line-height:1.35!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important;text-align:left!important;}
  body.premium-accounts-page .container{padding-top:22px!important;}
  main > .lb-shop-hero:first-child,
  .page-zoom > main > .lb-shop-hero:first-child{margin-top:var(--lb-content-top, 0px)!important;}
}
@media(max-width:380px){
  body.premium-accounts-page .lb-shop-hero__inner{grid-template-columns:38px minmax(0,1fr)!important;padding-left:14px!important;padding-right:14px!important;}
  body.premium-accounts-page .lb-shop-hero__icon{width:36px!important;height:36px!important;min-width:36px!important;}
  body.premium-accounts-page .lb-shop-hero__title{font-size:17px!important;}
  body.premium-accounts-page .lb-shop-hero__desc{font-size:12px!important;}
}
</style>
<div class="container">
    <div class="account-type-cards account-type-cards--compact" role="navigation" aria-label="Account type">
        <a href="/val/premium-accounts" class="type-card is-active" aria-current="page">
            <span class="type-card__icon" aria-hidden="true">
                <img src="/public/uploads/icons/default2.png" alt="">
            </span>
            <span class="type-card__title">Smurf Accounts</span>
        </a>

        <a href="/val/accounts" class="type-card">
            <span class="type-card__icon" aria-hidden="true">
                <img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/9.png" alt="">
            </span>
            <span class="type-card__title">Ranked Accounts</span>
        </a>
    </div>

    <?php
        // Server groups (Valorant typically: EU / NA / AP).
        // We also support LoL-like codes that you might be reusing in the admin (euw/eune/na),
        // and will still bucket them correctly.
        $serverGroups = [
            'eu' => [
                'label' => 'EU',
                'icon'  => 'fa-earth-europe',
                'codes' => ['eu', 'euw', 'eune', 'tr', 'ru'],
            ],
            'na' => [
                'label' => 'NA',
                'icon'  => 'fa-earth-americas',
                'codes' => ['na', 'lan', 'las', 'br'],
            ],
            'ap' => [
                'label' => 'AP',
                'icon'  => 'fa-earth-asia',
                'codes' => ['ap', 'asia', 'kr', 'jp', 'oce'],
            ],
        ];

        // Normalize raw server code -> group key
        $serverToGroup = function ($server) use ($serverGroups) {
            $s = strtolower(trim((string)$server));
            foreach ($serverGroups as $gKey => $g) {
                if (in_array($s, $g['codes'], true)) return $gKey;
            }
            return $s; // fallback: use raw as its own tab
        };

        // Build groups from $data
        $grouped = [];
        foreach ($data as $server => $packages) {
            $gKey = $serverToGroup($server);
            if (!isset($grouped[$gKey])) {
                $grouped[$gKey] = [
                    'servers' => [],
                    'packages' => [],
                ];
            }
            $grouped[$gKey]['servers'][] = $server;
            $grouped[$gKey]['packages'] = array_merge($grouped[$gKey]['packages'], $packages);
        }

        // Choose first tab as active
        $firstGroupKey = array_key_first($grouped);
    ?>

    <div class="accounts-toolhead" id="valAccountsToolhead">
    <div class="nav-tabs">
        <?php foreach ($grouped as $gKey => $_g): ?>
            <?php
                $label = strtoupper($gKey);
                $icon  = 'fa-globe';
                if (isset($serverGroups[$gKey])) {
                    $label = $serverGroups[$gKey]['label'];
                    $icon  = $serverGroups[$gKey]['icon'];
                }
            ?>
            <a href="#<?= $gKey ?>-list" class="<?= $gKey === $firstGroupKey ? 'active' : '' ?>">
                <i class="fa-solid <?= $icon ?>"></i> <?= t($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

        <div class="trust-banner" role="note">
            <span><i class="fa-solid fa-shield-check"></i> <?= t('Trusted Seller') ?></span>
            <span class="trust-banner__dot" aria-hidden="true">•</span>
            <span><i class="fa-solid fa-bolt"></i> <?= t('Instant Delivery') ?></span>
            <span class="trust-banner__dot" aria-hidden="true">•</span>
            <span><i class="fa-solid fa-badge-check"></i> <?= t('Lifetime Warranty') ?></span>
        </div>

        <div class="shop-filterbar" role="region" aria-label="<?= htmlspecialchars(t('Filter and sort accounts'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="shop-filterbar__row">
                <div class="shop-filterbar__search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="text" id="valAccountsSearch" placeholder="<?= htmlspecialchars(t('Search...'), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="shop-pill" id="valRankPill">
                    <button type="button" class="shop-pill__btn" id="valRankTrigger" aria-expanded="false">
                        <i class="fa-solid fa-medal"></i>
                        <span class="shop-pill__value" id="valRankValue"><?= t('All Ranks') ?></span>
                        <i class="fa-solid fa-chevron-down shop-pill__caret"></i>
                    </button>
                    <ul class="shop-pill__menu" id="valRankMenu" hidden>
                        <li data-value="all" class="is-selected"><?= t('All Ranks') ?></li>
                        <?php
                        $valFilterTiers = [
                            0 => 'Unranked',
                            1 => 'Iron',
                            2 => 'Bronze',
                            3 => 'Silver',
                            4 => 'Gold',
                            5 => 'Platinum',
                            6 => 'Diamond',
                            7 => 'Ascended',
                            8 => 'Immortal',
                            9 => 'Radiant',
                        ];
                        ?>
                        <?php foreach ($valFilterTiers as $tierId => $tierLabel): ?>
                            <li data-value="<?= strtolower($tierLabel) ?>">
                                <img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/<?= (int)$tierId ?>.png" alt="">
                                <span><?= t($tierLabel) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <button type="button" class="shop-pill--toggle" id="valRankedReady" aria-pressed="false">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= t('Ranked Ready only') ?></span>
                </button>

                <div class="shop-pill" id="valSortPill">
                    <button type="button" class="shop-pill__btn" id="valSortTrigger" aria-expanded="false">
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <span class="shop-pill__value" id="valSortValue"><?= t('Featured') ?></span>
                        <i class="fa-solid fa-chevron-down shop-pill__caret"></i>
                    </button>
                    <ul class="shop-pill__menu" id="valSortMenu" hidden>
                        <li data-value="default" class="is-selected"><?= t('Featured') ?></li>
                        <li data-value="price-asc"><?= t('Price: Low to High') ?></li>
                        <li data-value="price-desc"><?= t('Price: High to Low') ?></li>
                        <li data-value="rank-asc"><?= t('Rank: Low to High') ?></li>
                        <li data-value="rank-desc"><?= t('Rank: High to Low') ?></li>
                    </ul>
                </div>

                <button type="button" class="shop-clear-all" id="valClearFilters"><?= t('Clear All') ?></button>
            </div>

            <div class="shop-popular" id="valPopularRanks">
                <span class="shop-popular__label"><?= t('Popular:') ?></span>
                <?php foreach ([1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascended',8=>'Immortal'] as $tierId => $tierLabel): ?>
                    <button type="button" class="shop-popular__chip" data-rank="<?= strtolower($tierLabel) ?>">
                        <img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/<?= $tierId ?>.png" alt="">
                        <span><?= t($tierLabel) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
        // Valorant rank tiers mapping (db values -> labels + filter keys)
        $tiers = [
            0 => 'Unranked',
            1 => 'Iron',
            2 => 'Bronze',
            3 => 'Silver',
            4 => 'Gold',
            5 => 'Platinum',
            6 => 'Diamond',
            7 => 'Ascended',
            8 => 'Immortal',
            9 => 'Radiant',
        ];

        $normalizePackageFeatures = function ($raw): array {
            if (is_array($raw)) {
                $items = $raw;
            } else {
                $raw = trim((string)$raw);
                if ($raw === '') {
                    $items = [];
                } else {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $items = $decoded;
                    } else {
                        $items = preg_split('/[|\n\r]+/', $raw) ?: [];
                    }
                }
            }

            $out = [];
            foreach ($items as $item) {
                $item = trim((string)$item);
                if ($item !== '') {
                    $out[] = $item;
                }
            }
            return $out;
        };


        $accountLastSoldByPackage = [];
        $accountPackageIds = [];
        foreach ($grouped as $_serverOrGroup => $_packages) {
            $_packageList = isset($_packages['packages']) && is_array($_packages['packages']) ? $_packages['packages'] : $_packages;
            foreach ($_packageList as $_package) {
                if (isset($_package['id'])) {
                    $accountPackageIds[] = (int)$_package['id'];
                }
            }
        }
        $accountPackageIds = array_values(array_unique(array_filter($accountPackageIds)));

        if (!empty($accountPackageIds)) {
            $accountIdsSql = implode(',', $accountPackageIds);
            $accountLastSoldSql = "SELECT package_id, MAX(sold_at) AS last_sold_at FROM accounts WHERE package_id IN ($accountIdsSql) AND sold_at IS NOT NULL AND sold_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) GROUP BY package_id";
            $accountDbCandidates = [];
            foreach (['pdo', 'db', 'database', 'conn', 'mysqli'] as $_dbVar) {
                if (isset($$_dbVar)) {
                    $accountDbCandidates[] = $$_dbVar;
                }
                if (isset($GLOBALS[$_dbVar])) {
                    $accountDbCandidates[] = $GLOBALS[$_dbVar];
                }
            }

            foreach ($accountDbCandidates as $_dbCandidate) {
                try {
                    if ($_dbCandidate instanceof PDO) {
                        $_stmt = $_dbCandidate->query($accountLastSoldSql);
                        if ($_stmt) {
                            foreach ($_stmt->fetchAll(PDO::FETCH_ASSOC) as $_row) {
                                $accountLastSoldByPackage[(int)$_row['package_id']] = $_row['last_sold_at'];
                            }
                            break;
                        }
                    } elseif ($_dbCandidate instanceof mysqli) {
                        $_result = $_dbCandidate->query($accountLastSoldSql);
                        if ($_result) {
                            while ($_row = $_result->fetch_assoc()) {
                                $accountLastSoldByPackage[(int)$_row['package_id']] = $_row['last_sold_at'];
                            }
                            break;
                        }
                    } elseif (is_object($_dbCandidate) && method_exists($_dbCandidate, 'query')) {
                        $_result = $_dbCandidate->query($accountLastSoldSql);
                        if ($_result instanceof PDOStatement) {
                            foreach ($_result->fetchAll(PDO::FETCH_ASSOC) as $_row) {
                                $accountLastSoldByPackage[(int)$_row['package_id']] = $_row['last_sold_at'];
                            }
                            break;
                        } elseif ($_result instanceof mysqli_result) {
                            while ($_row = $_result->fetch_assoc()) {
                                $accountLastSoldByPackage[(int)$_row['package_id']] = $_row['last_sold_at'];
                            }
                            break;
                        } elseif (is_array($_result)) {
                            foreach ($_result as $_row) {
                                if (isset($_row['package_id'], $_row['last_sold_at'])) {
                                    $accountLastSoldByPackage[(int)$_row['package_id']] = $_row['last_sold_at'];
                                }
                            }
                            break;
                        }
                    }
                } catch (Throwable $_e) {
                    $accountLastSoldByPackage = [];
                }
            }
        }

        $buildAccountUrgency = function (array $package) use (&$accountLastSoldByPackage): array {
            $available = (int)($package['available'] ?? 0);
            $packageId = (int)($package['id'] ?? 0);
            $items = [];

            if ($available > 0 && $available <= 3) {
                $items[] = [
                    'type' => 'left',
                    'icon' => 'fa-fire-flame-curved',
                    'label' => sprintf(t('Only %s left!'), max(1, $available)),
                ];
            }

            if ($packageId > 0 && isset($accountLastSoldByPackage[$packageId])) {
                $soldTimestamp = strtotime((string)$accountLastSoldByPackage[$packageId]);
                if ($soldTimestamp !== false) {
                    $diffMinutes = max(1, (int)floor((time() - $soldTimestamp) / 60));

                    if ($diffMinutes < 60) {
                        $label = sprintf(t('Last sold %s minutes ago'), $diffMinutes);
                    } else {
                        $hours = min(24, (int)floor($diffMinutes / 60));
                        $label = sprintf(t('Last sold %s hour%s ago'), $hours, $hours === 1 ? '' : 's');
                    }

                    $items[] = [
                        'type' => 'sold',
                        'icon' => 'fa-bolt',
                        'label' => $label,
                    ];
                }
            }

            return $items;
        };

        $packageIsRankedReady = function (array $package): bool {
            foreach (['ranked_ready', 'is_ranked_ready', 'rankedReady'] as $key) {
                if (array_key_exists($key, $package)) {
                    return (int)$package[$key] === 1;
                }
            }

            $haystack = strtolower(trim((string)($package['name'] ?? '') . ' ' . (string)($package['features'] ?? '') . ' ' . (string)($package['custom_features'] ?? '')));
            if (strpos($haystack, 'not ranked ready') !== false || strpos($haystack, 'requires 10 normals') !== false || strpos($haystack, 'need 10 normals') !== false) {
                return false;
            }
            if (strpos($haystack, 'ranked ready') !== false) {
                return true;
            }

            return true;
        };
    ?>
<style>
        .account .ranked-ready-status {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: fit-content;
            margin: 12px auto 0;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .01em;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.055);
            color: rgba(255,255,255,.92);
            cursor: help;
        }
        .account .ranked-ready-status__tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%) translateY(4px);
            min-width: 210px;
            max-width: 260px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(15,15,25,.96);
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 12px 34px rgba(0,0,0,.42);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
            text-align: center;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            z-index: 50;
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
            white-space: normal;
        }
        .account .ranked-ready-status__tooltip::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: rgba(15,15,25,.96);
        }
        .account .ranked-ready-status:hover .ranked-ready-status__tooltip,
        .account .ranked-ready-status:focus-within .ranked-ready-status__tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }
        .account .ranked-ready-status i {
            font-size: 12px;
        }
        .account .ranked-ready-status--ready {
            color: #34d399;
            border-color: rgba(52,211,153,.34);
            background: linear-gradient(135deg, rgba(52,211,153,.16), rgba(52,211,153,.06));
            box-shadow: 0 0 18px rgba(52,211,153,.12);
        }
        .account .ranked-ready-status--not-ready {
            color: #fbbf24;
            border-color: rgba(251,191,36,.34);
            background: linear-gradient(135deg, rgba(251,191,36,.17), rgba(251,191,36,.055));
            box-shadow: 0 0 18px rgba(251,191,36,.11);
        }
        .account .ranked-ready-status--not-ready small {
            color: rgba(255,255,255,.66);
            font-size: 10px;
            font-weight: 700;
            margin-left: 2px;
        }

        .account .account-urgency {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin: 12px auto 0;
        }
        .account .account-urgency__pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            max-width: 100%;
            padding: 9px 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: .01em;
            color: rgba(255,255,255,.96);
            border: 1px solid rgba(255,255,255,.13);
            background: rgba(255,255,255,.06);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
            white-space: nowrap;
        }
        .account .account-urgency__pill i {
            font-size: 12px;
        }
        .account .account-urgency__pill--left {
            color: #ff6b6b;
            border-color: rgba(255,82,82,.52);
            background: linear-gradient(135deg, rgba(255,82,82,.25), rgba(255,82,82,.08));
            box-shadow: 0 0 0 0 rgba(255,82,82,.32), 0 0 20px rgba(255,82,82,.18), inset 0 1px 0 rgba(255,255,255,.08);
            animation: accountUrgencyPulse 1.25s ease-in-out infinite;
        }
        .account .account-urgency__pill--sold {
            color: #fbbf24;
            border-color: rgba(251,191,36,.42);
            background: linear-gradient(135deg, rgba(251,191,36,.18), rgba(251,191,36,.06));
            box-shadow: 0 0 18px rgba(251,191,36,.12), inset 0 1px 0 rgba(255,255,255,.06);
        }
        @keyframes accountUrgencyPulse {
            0%, 100% {
                transform: translateY(0) scale(1);
                box-shadow: 0 0 0 0 rgba(255,82,82,.32), 0 0 20px rgba(255,82,82,.18), inset 0 1px 0 rgba(255,255,255,.08);
            }
            50% {
                transform: translateY(-1px) scale(1.035);
                box-shadow: 0 0 0 6px rgba(255,82,82,0), 0 0 26px rgba(255,82,82,.30), inset 0 1px 0 rgba(255,255,255,.10);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .account .account-urgency__pill--left {
                animation: none;
            }
        }

        @media (max-width: 767px) {
            .account .ranked-ready-status {
                margin-top: 10px;
                padding: 8px 10px;
                font-size: 11px;
                max-width: 100%;
                white-space: normal;
                text-align: center;
            }
        }
    </style>

    <div class="tab-content">
        <?php foreach ($grouped as $gKey => $g): ?>
            <div id="<?= $gKey ?>-list" class="tab-pane <?= $gKey === $firstGroupKey ? 'active' : '' ?>">
                <div class="accounts-grid">
                    <?php foreach ($g['packages'] as $package): ?>
                        <?php
                            $rankId = (int)($package['rank'] ?? 0);
                            $rankLabel = $tiers[$rankId] ?? 'Unranked';
                            $rankTier = strtolower($rankLabel);

                            $baseFeatures = $normalizePackageFeatures($package['features'] ?? '');
                            $customFeatures = $normalizePackageFeatures($package['custom_features'] ?? '');
                            $package['features'] = array_values(array_unique(array_merge($baseFeatures, $customFeatures)));

                            $isRankedReady = $packageIsRankedReady($package);
                            $rankedReadyClass = $isRankedReady ? 'ranked-ready-status--ready' : 'ranked-ready-status--not-ready';
                            $rankedReadyLabel = $isRankedReady ? t('Ranked Ready') : t('Not Ranked Ready');
                            $rankedReadyText = $isRankedReady
                                ? t('This Valorant account is ranked ready. You can start ranked games immediately after purchase.')
                                : t('This Valorant account is not ranked ready yet. You need to play 10 normal games before ranked is available.');
                            $accountUrgency = $buildAccountUrgency($package);

                            $rawServer = $package['server'] ?? ($package['server_code'] ?? '');
                            $rawServer = $rawServer !== '' ? $rawServer : ($g['servers'][0] ?? $gKey);
                        ?>
                        <div class="account <?= $package['available'] == 0 ? 'is-sold-out' : '' ?>" data-rank="<?= $rankTier ?>" data-rank-id="<?= $rankId ?>" data-ranked-ready="<?= $isRankedReady ? '1' : '0' ?>" data-price="<?= (float)util_format_price_display($package['price']) ?>" data-search="<?= htmlspecialchars(strtolower(strip_tags((string)($package['name'] ?? '') . ' ' . implode(' ', $package['features']) . ' ' . $rankLabel . ' ' . $rawServer)), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="head">
                                <div class="corner-meta">
                                    <span class="server-badge">
                                        <?php
                                            $icon = 'fa-globe';
                                            if (isset($serverGroups[$gKey])) $icon = $serverGroups[$gKey]['icon'];
                                        ?>
                                        <i class="fa-solid <?= $icon ?>"></i>
                                        <?= strtoupper($rawServer) ?>
                                    </span>

                                    <span class="rank-badge" aria-hidden="true" title="<?= htmlspecialchars($rankLabel) ?>">
                                        <img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/<?= $rankId ?>.png" alt="">
                                    </span>
                                </div>

                                <h4>
                                    <?= $package['name'] ?>
                                </h4>

                                <div class="ranked-ready-status <?= $rankedReadyClass ?>" tabindex="0" aria-label="<?= htmlspecialchars($rankedReadyText, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($isRankedReady): ?>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span><?= $rankedReadyLabel ?></span>
                                    <?php else: ?>
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span><?= $rankedReadyLabel ?></span>
                                        <small><?= t('(10 normals)') ?></small>
                                    <?php endif; ?>
                                    <span class="ranked-ready-status__tooltip" role="tooltip"><?= $rankedReadyText ?></span>
                                </div>
                            </div>
                            <ul>
                                <?php foreach ($package['features'] as $feature): ?>
                                    <li>
                                        <span><?= $feature ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="foot">
                                <div class="price <?= $package['available'] == 0 ? 'price--soldout' : '' ?>">
                                    <span class="price__value">
                                        <?php if ($_SESSION['currency'] == 'USD'): ?>
                                            $<?= round(util_format_price_display($package['price']) * get_exchange_rate(), 2) ?>
                                        <?php else: ?>
                                            €<?= util_format_price_display($package['price']) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <?php if ($package['available'] == 0): ?>
                                    <button class="sold sold-cta" disabled><?= t('SOLD OUT') ?></button>
                                <?php else: ?>
                                    <form class="ajax-form" action="<?= AJAX_URL ?>">
                                        <input type="hidden" name="action" value="prepare_account_purchase">
                                        <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                        <button type="submit" class="buy-now"><?= t('Buy Now') ?></button>
                                    </form>
                                    <?php if (!empty($accountUrgency)): ?>
                                        <div class="account-urgency" aria-label="<?= htmlspecialchars(t('Account availability update'), ENT_QUOTES, 'UTF-8') ?>">
                                            <?php foreach ($accountUrgency as $urgencyItem): ?>
                                                <span class="account-urgency__pill account-urgency__pill--<?= htmlspecialchars($urgencyItem['type'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fa-solid <?= htmlspecialchars($urgencyItem['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                                    <span><?= $urgencyItem['label'] ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="trust-row" aria-label="Trust badges">
                                    <span class="trust-badge is-accent"><i class="fa-solid fa-shield-check"></i> <?= t('Trusted') ?></span>
                                    <span class="trust-badge"><i class="fa-solid fa-bolt"></i> <?= t('Instant') ?></span>
                                    <span class="trust-badge"><i class="fa-solid fa-badge-check"></i> <?= t('Warranty') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function () {
    'use strict';

    var ACCOUNT_CARDS_LIFT_UP = -30;

    function isVisible(el) {
        if (!el) return false;
        var style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return false;
        var rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function getZoomFactor() {
        var zoom = parseFloat(window.getComputedStyle(document.documentElement).zoom || '1');
        return zoom && zoom > 0 ? zoom : 1;
    }

    function getAccountCardsFixedTopOffset() {
        var selectors = [
            '#lbSaleBanner',
            '#lbGiveawayBanner',
            '.navbar-top',
            '.navbar-mobile',
            '.lb-game-subnav',
            '.lb-mobile-gamebar'
        ];

        var zoom = getZoomFactor();
        var bottom = 0;

        selectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (el) {
                if (!isVisible(el)) return;

                var style = window.getComputedStyle(el);
                var position = style.position;
                if (position !== 'fixed' && position !== 'sticky') return;

                var rect = el.getBoundingClientRect();
                if (rect.bottom <= 0) return;

                bottom = Math.max(bottom, rect.bottom / zoom);
            });
        });

        return bottom || 90;
    }

    function scrollToAccountTypeCards() {
        var target = document.querySelector('.account-type-cards') || document.querySelector('.container');
        if (!target) return;

        var offset = getAccountCardsFixedTopOffset();
        var y = Math.max(0, target.getBoundingClientRect().top + window.scrollY - offset + ACCOUNT_CARDS_LIFT_UP);
        window.scrollTo({ top: y, behavior: 'auto' });
    }

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    // scrollToAccountTypeCards() used to run unconditionally here on every page
    // load, forcing every fresh visit to jump down to the card grid instead of
    // loading at the top. Removed — nothing else calls it, so it's dead code now.
})();
</script>



<style id="lb-premium-account-type-buttons-v2">
body.premium-accounts-page .account-type-cards.account-type-cards--compact{
  width:max-content!important;
  max-width:100%!important;
  margin:28px auto 24px!important;
  display:flex!important;
  align-items:stretch!important;
  justify-content:center!important;
  gap:14px!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card{
  width:154px!important;
  min-width:154px!important;
  min-height:128px!important;
  padding:16px 14px!important;
  display:flex!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
  gap:12px!important;
  border-radius:18px!important;
  border:1px solid rgba(255,255,255,.09)!important;
  background:linear-gradient(180deg,rgba(17,20,39,.98),rgba(10,13,27,.98))!important;
  color:#fff!important;
  text-decoration:none!important;
  box-shadow:none!important;
  transition:transform .18s ease,border-color .18s ease,background .18s ease!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card:hover{
  transform:translateY(-2px)!important;
  border-color:rgba(99,102,241,.36)!important;
  background:linear-gradient(180deg,rgba(22,26,50,.98),rgba(12,15,31,.98))!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card.is-active{
  border-color:rgba(99,102,241,.82)!important;
  background:linear-gradient(180deg,rgba(99,102,241,.22),rgba(20,24,51,.98))!important;
  box-shadow:0 0 0 1px rgba(99,102,241,.18),0 14px 34px rgba(47,54,170,.18)!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card__icon{
  width:58px!important;
  height:58px!important;
  min-width:58px!important;
  min-height:58px!important;
  display:grid!important;
  place-items:center!important;
  border-radius:17px!important;
  background:rgba(7,9,20,.72)!important;
  border:1px solid rgba(255,255,255,.09)!important;
  overflow:hidden!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card.is-active .type-card__icon{
  background:rgba(99,102,241,.13)!important;
  border-color:rgba(124,146,255,.30)!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card__icon img{
  width:42px!important;
  height:42px!important;
  max-width:42px!important;
  max-height:42px!important;
  object-fit:contain!important;
  border-radius:12px!important;
  display:block!important;
}
body.premium-accounts-page .account-type-cards--compact .type-card__title{
  display:block!important;
  margin:0!important;
  color:#fff!important;
  font-size:15px!important;
  line-height:1.2!important;
  font-weight:900!important;
  text-align:center!important;
  white-space:nowrap!important;
}
@media(max-width:767px){
  body.premium-accounts-page .account-type-cards.account-type-cards--compact{
    width:100%!important;
    margin:18px auto 18px!important;
    gap:10px!important;
  }
  body.premium-accounts-page .account-type-cards--compact .type-card{
    width:auto!important;
    min-width:0!important;
    flex:1 1 0!important;
    min-height:108px!important;
    padding:13px 10px!important;
    border-radius:16px!important;
  }
  body.premium-accounts-page .account-type-cards--compact .type-card__icon{
    width:50px!important;
    height:50px!important;
    min-width:50px!important;
    min-height:50px!important;
    border-radius:15px!important;
  }
  body.premium-accounts-page .account-type-cards--compact .type-card__icon img{
    width:36px!important;
    height:36px!important;
    max-width:36px!important;
    max-height:36px!important;
  }
  body.premium-accounts-page .account-type-cards--compact .type-card__title{
    font-size:13px!important;
  }
}
</style>


<style id="lb-fluid-filter-design-v14">
/* Softer, more dynamic filter system with fewer visible borders. */
body.premium-accounts-page .accounts-toolhead{
  position:relative!important;
  overflow:visible!important;
  border:0!important;
  border-radius:22px!important;
  background:
    radial-gradient(700px 180px at 18% 0%,rgba(99,102,241,.11),transparent 62%),
    linear-gradient(180deg,rgba(18,21,39,.92),rgba(10,12,25,.94))!important;
  box-shadow:0 18px 50px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.045)!important;
}
body.premium-accounts-page .accounts-toolhead::before{
  content:''!important;
  position:absolute!important;
  inset:0!important;
  pointer-events:none!important;
  border-radius:inherit!important;
  background:linear-gradient(115deg,rgba(99,102,241,.07),transparent 34%,transparent 68%,rgba(79,110,247,.035))!important;
}
body.premium-accounts-page .accounts-toolhead>.nav-tabs,
body.premium-accounts-page .accounts-toolhead>.trust-banner,
body.premium-accounts-page .accounts-toolhead>.shop-filterbar{
  position:relative!important;
  z-index:1!important;
  border:0!important;
}
body.premium-accounts-page .accounts-toolhead>.nav-tabs{
  padding:18px 20px 12px!important;
  gap:9px!important;
}
body.premium-accounts-page .nav-tabs a{
  min-height:46px!important;
  padding:0 22px!important;
  border:0!important;
  border-radius:13px!important;
  background:rgba(255,255,255,.045)!important;
  color:rgba(224,228,245,.72)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
  transition:transform .18s ease,background .18s ease,color .18s ease,box-shadow .18s ease!important;
}
body.premium-accounts-page .nav-tabs a:hover{
  transform:translateY(-1px)!important;
  background:rgba(255,255,255,.075)!important;
  color:#fff!important;
}
body.premium-accounts-page .nav-tabs a.active{
  border:0!important;
  background:linear-gradient(135deg,#7068f6,#585bea)!important;
  color:#fff!important;
  box-shadow:0 10px 26px rgba(88,91,234,.28),inset 0 1px 0 rgba(255,255,255,.16)!important;
}
body.premium-accounts-page .trust-banner{
  padding:8px 20px 14px!important;
  background:transparent!important;
  color:rgba(205,211,232,.58)!important;
}
body.premium-accounts-page .trust-banner__dot{opacity:.18!important;}
body.premium-accounts-page .shop-filterbar{
  padding:12px 20px 18px!important;
  background:transparent!important;
}
body.premium-accounts-page .shop-filterbar__row{
  gap:9px!important;
  padding:10px!important;
  border:0!important;
  border-radius:17px!important;
  background:rgba(5,7,18,.34)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
}
body.premium-accounts-page .shop-filterbar__search{
  min-height:48px!important;
  border:0!important;
  border-radius:13px!important;
  background:rgba(255,255,255,.055)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
  transition:background .18s ease,box-shadow .18s ease!important;
}
body.premium-accounts-page .shop-filterbar__search:focus-within{
  background:rgba(255,255,255,.075)!important;
  box-shadow:0 0 0 2px rgba(99,102,241,.20),inset 0 1px 0 rgba(255,255,255,.05)!important;
}
body.premium-accounts-page .shop-pill__btn,
body.premium-accounts-page .shop-pill--toggle,
body.premium-accounts-page .shop-clear-all{
  min-height:48px!important;
  border:0!important;
  border-radius:13px!important;
  background:rgba(255,255,255,.055)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
  transition:transform .18s ease,background .18s ease,color .18s ease,box-shadow .18s ease!important;
}
body.premium-accounts-page .shop-pill__btn:hover,
body.premium-accounts-page .shop-pill--toggle:hover{
  transform:translateY(-1px)!important;
  background:rgba(255,255,255,.085)!important;
}
body.premium-accounts-page .shop-pill.is-open .shop-pill__btn,
body.premium-accounts-page .shop-pill--toggle[aria-pressed="true"]{
  background:rgba(99,102,241,.17)!important;
  color:#fff!important;
  box-shadow:0 0 0 2px rgba(99,102,241,.18),inset 0 1px 0 rgba(255,255,255,.06)!important;
}
body.premium-accounts-page .shop-clear-all{
  padding-left:22px!important;
  padding-right:22px!important;
  background:linear-gradient(135deg,#7068f6,#5c5fea)!important;
  color:#fff!important;
  box-shadow:0 10px 24px rgba(92,95,234,.22)!important;
}
body.premium-accounts-page .shop-clear-all:hover{
  transform:translateY(-1px)!important;
  filter:brightness(1.06)!important;
}
body.premium-accounts-page .shop-popular{
  margin:0 20px 18px!important;
  padding:0!important;
  border:0!important;
  background:transparent!important;
  gap:8px!important;
}
body.premium-accounts-page .shop-popular__chip{
  min-height:36px!important;
  padding:0 14px!important;
  border:0!important;
  border-radius:999px!important;
  background:rgba(255,255,255,.05)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.035)!important;
  transition:transform .16s ease,background .16s ease,color .16s ease!important;
}
body.premium-accounts-page .shop-popular__chip:hover{
  transform:translateY(-1px)!important;
  background:rgba(255,255,255,.085)!important;
}
body.premium-accounts-page .shop-popular__chip.is-active{
  background:rgba(99,102,241,.18)!important;
  color:#fff!important;
  box-shadow:0 0 0 2px rgba(99,102,241,.16)!important;
}
/* Valorant currently renders its server tabs without the LoL wrapper. Give them the same fluid treatment. */
body.premium-accounts-page>.container>.nav-tabs,
body.premium-accounts-page main>.container>.nav-tabs{
  width:max-content!important;
  max-width:100%!important;
  margin:20px auto 24px!important;
  padding:9px!important;
  gap:8px!important;
  border:0!important;
  border-radius:18px!important;
  background:linear-gradient(180deg,rgba(18,21,39,.90),rgba(10,12,25,.94))!important;
  box-shadow:0 16px 42px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.04)!important;
}
@media(max-width:767px){
  body.premium-accounts-page .accounts-toolhead{border-radius:18px!important;}
  body.premium-accounts-page .accounts-toolhead>.nav-tabs{padding:12px 12px 8px!important;}
  body.premium-accounts-page .trust-banner{padding:6px 12px 10px!important;}
  body.premium-accounts-page .shop-filterbar{padding:10px 12px 14px!important;}
  body.premium-accounts-page .shop-filterbar__row{padding:8px!important;border-radius:15px!important;}
  body.premium-accounts-page .shop-popular{margin:0 12px 14px!important;}
  body.premium-accounts-page>.container>.nav-tabs,
  body.premium-accounts-page main>.container>.nav-tabs{
    width:100%!important;
    justify-content:center!important;
    margin:16px auto 20px!important;
  }
}
</style>


<style id="lb-val-functional-filters-v15">
body.premium-accounts-page #valAccountsToolhead{margin:22px auto 26px!important;}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu{
  position:absolute;top:calc(100% + 9px);right:0;z-index:500;
  min-width:220px;max-height:330px;padding:8px;margin:0;overflow:auto;
  list-style:none;border:0;border-radius:14px;background:rgba(12,14,29,.98);
  box-shadow:0 20px 55px rgba(0,0,0,.48),inset 0 1px 0 rgba(255,255,255,.05);
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li{
  min-height:39px;display:flex;align-items:center;gap:9px;padding:8px 10px;
  border-radius:10px;color:rgba(232,235,247,.78);font-size:13px;font-weight:800;cursor:pointer;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li:hover,
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li.is-selected{
  color:#fff;background:rgba(99,102,241,.16);
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu img,
body.premium-accounts-page #valPopularRanks img{width:22px;height:22px;object-fit:contain;}
body.premium-accounts-page #valPopularRanks{display:flex!important;align-items:center;flex-wrap:wrap;}
body.premium-accounts-page #valPopularRanks .shop-popular__chip{display:inline-flex;align-items:center;gap:7px;cursor:pointer;color:rgba(235,238,249,.78);}
body.premium-accounts-page #valPopularRanks .shop-popular__chip.is-active{color:#fff;}
body.premium-accounts-page .tab-pane .accounts-grid .account.is-filter-hidden{display:none!important;}
body.premium-accounts-page .val-filter-empty{display:none;padding:38px 20px;text-align:center;color:rgba(255,255,255,.62);font-weight:800;}
body.premium-accounts-page .val-filter-empty.is-visible{display:block;}
@media(max-width:767px){
 body.premium-accounts-page #valAccountsToolhead .shop-filterbar__row{display:grid!important;grid-template-columns:1fr 1fr!important;}
 body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search{grid-column:1/-1!important;width:100%!important;}
 body.premium-accounts-page #valAccountsToolhead .shop-pill,body.premium-accounts-page #valAccountsToolhead .shop-pill__btn,body.premium-accounts-page #valAccountsToolhead .shop-pill--toggle{width:100%!important;}
 body.premium-accounts-page #valAccountsToolhead .shop-clear-all{grid-column:1/-1!important;width:100%!important;}
 body.premium-accounts-page #valAccountsToolhead .shop-pill__menu{left:0;right:auto;min-width:100%;}
}
</style>
<script id="lb-val-functional-filters-v15-js">
(function(){
  'use strict';
  var root=document.getElementById('valAccountsToolhead');
  if(!root || root.dataset.filtersReady==='1') return;
  root.dataset.filtersReady='1';
  var state={search:'',rank:'all',ready:false,sort:'default'};
  var search=document.getElementById('valAccountsSearch');
  var ready=document.getElementById('valRankedReady');
  var rankValue=document.getElementById('valRankValue');
  var sortValue=document.getElementById('valSortValue');
  function activePane(){return document.querySelector('.tab-pane.active') || document.querySelector('.tab-pane');}
  function cards(pane){return Array.prototype.slice.call((pane||activePane()).querySelectorAll('.account'));}
  function closeMenus(except){
    ['valRankMenu','valSortMenu'].forEach(function(id){var m=document.getElementById(id);if(m&&m!==except){m.hidden=true;m.parentElement.classList.remove('is-open');}});
  }
  function sortCards(list,pane){
    var grid=pane&&pane.querySelector('.accounts-grid');if(!grid)return;
    list.sort(function(a,b){
      if(state.sort==='price-asc')return (+a.dataset.price||0)-(+b.dataset.price||0);
      if(state.sort==='price-desc')return (+b.dataset.price||0)-(+a.dataset.price||0);
      if(state.sort==='rank-asc')return (+a.dataset.rankId||0)-(+b.dataset.rankId||0);
      if(state.sort==='rank-desc')return (+b.dataset.rankId||0)-(+a.dataset.rankId||0);
      return (+a.dataset.originalOrder||0)-(+b.dataset.originalOrder||0);
    });
    list.forEach(function(c){grid.appendChild(c);});
  }
  document.querySelectorAll('.tab-pane .account').forEach(function(c,i){c.dataset.originalOrder=String(i);});
  document.querySelectorAll('.tab-pane').forEach(function(p){var e=document.createElement('div');e.className='val-filter-empty';e.textContent='No accounts match your filters.';p.appendChild(e);});
  function apply(){
    var pane=activePane();if(!pane)return;
    var list=cards(pane),shown=0,q=state.search.toLowerCase();
    list.forEach(function(c){
      var okSearch=!q||(c.dataset.search||c.textContent.toLowerCase()).indexOf(q)!==-1;
      var okRank=state.rank==='all'||c.dataset.rank===state.rank;
      var okReady=!state.ready||c.dataset.rankedReady==='1';
      var show=okSearch&&okRank&&okReady;c.classList.toggle('is-filter-hidden',!show);if(show)shown++;
    });
    sortCards(list,pane);
    var empty=pane.querySelector('.val-filter-empty');if(empty)empty.classList.toggle('is-visible',shown===0);
  }
  function bindMenu(triggerId,menuId,key,valueId){
    var t=document.getElementById(triggerId),m=document.getElementById(menuId),v=document.getElementById(valueId);if(!t||!m)return;
    t.addEventListener('click',function(e){e.stopPropagation();var open=m.hidden;closeMenus();m.hidden=!open;m.parentElement.classList.toggle('is-open',open);t.setAttribute('aria-expanded',open?'true':'false');});
    m.addEventListener('click',function(e){var li=e.target.closest('li[data-value]');if(!li)return;state[key]=li.dataset.value;m.querySelectorAll('li').forEach(function(x){x.classList.toggle('is-selected',x===li);});if(v)v.textContent=li.textContent.trim();m.hidden=true;m.parentElement.classList.remove('is-open');apply();});
  }
  bindMenu('valRankTrigger','valRankMenu','rank','valRankValue');
  bindMenu('valSortTrigger','valSortMenu','sort','valSortValue');
  if(search)search.addEventListener('input',function(){state.search=this.value.trim();apply();});
  if(ready)ready.addEventListener('click',function(){state.ready=!state.ready;this.setAttribute('aria-pressed',state.ready?'true':'false');apply();});
  document.querySelectorAll('#valPopularRanks [data-rank]').forEach(function(b){b.addEventListener('click',function(){state.rank=this.dataset.rank;rankValue.textContent=this.textContent.trim();document.querySelectorAll('#valPopularRanks [data-rank]').forEach(function(x){x.classList.toggle('is-active',x===b);});apply();});});
  var clear=document.getElementById('valClearFilters');if(clear)clear.addEventListener('click',function(){state={search:'',rank:'all',ready:false,sort:'default'};if(search)search.value='';if(rankValue)rankValue.textContent='All Ranks';if(sortValue)sortValue.textContent='Featured';if(ready)ready.setAttribute('aria-pressed','false');document.querySelectorAll('#valPopularRanks .is-active').forEach(function(x){x.classList.remove('is-active');});document.querySelectorAll('#valRankMenu li,#valSortMenu li').forEach(function(x){x.classList.toggle('is-selected',x.dataset.value==='all'||x.dataset.value==='default');});apply();});
  document.querySelectorAll('#valAccountsToolhead .nav-tabs a').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();var target=document.querySelector(this.getAttribute('href'));if(!target)return;document.querySelectorAll('#valAccountsToolhead .nav-tabs a').forEach(function(x){x.classList.remove('active');});document.querySelectorAll('.tab-pane').forEach(function(x){x.classList.remove('active');});this.classList.add('active');target.classList.add('active');apply();});});
  document.addEventListener('click',function(){closeMenus();});
  apply();
})();
</script>


<style id="lb-val-filter-lol-layout-v16">
/* Final Valorant filter layout, structurally matched to the LoL smurf shop. */
body.premium-accounts-page #valAccountsToolhead{
  width:100%!important;
  margin:22px auto 26px!important;
  padding:0!important;
  overflow:visible!important;
  border-radius:20px!important;
  background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.018))!important;
  border:1px solid rgba(255,255,255,.08)!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead::before{display:none!important;content:none!important;}
body.premium-accounts-page #valAccountsToolhead>.nav-tabs{
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  flex-wrap:wrap!important;
  gap:10px!important;
  width:100%!important;
  margin:0!important;
  padding:18px 20px!important;
  border:0!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
  background:transparent!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead>.nav-tabs a{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:8px!important;
  min-height:50px!important;
  padding:0 25px!important;
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,.11)!important;
  background:rgba(255,255,255,.04)!important;
  color:rgba(255,255,255,.72)!important;
  font-size:15px!important;
  font-weight:850!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead>.nav-tabs a.active{
  background:linear-gradient(135deg,#7b70ff,#5d59eb)!important;
  border-color:transparent!important;
  color:#fff!important;
  box-shadow:0 8px 22px rgba(99,102,241,.30)!important;
}
body.premium-accounts-page #valAccountsToolhead>.trust-banner{
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  flex-wrap:wrap!important;
  gap:12px!important;
  width:100%!important;
  margin:0!important;
  padding:12px 20px!important;
  border:0!important;
  border-bottom:1px solid rgba(255,255,255,.07)!important;
  background:transparent!important;
  color:rgba(255,255,255,.60)!important;
  font-size:13.5px!important;
  font-weight:700!important;
}
body.premium-accounts-page #valAccountsToolhead>.shop-filterbar{
  width:100%!important;
  margin:0!important;
  padding:16px 20px 18px!important;
  border:0!important;
  background:transparent!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-filterbar__row{
  display:flex!important;
  align-items:center!important;
  flex-wrap:wrap!important;
  gap:10px!important;
  width:100%!important;
  padding:0!important;
  border:0!important;
  border-radius:0!important;
  background:transparent!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search{
  display:flex!important;
  align-items:center!important;
  gap:11px!important;
  flex:1 1 320px!important;
  min-width:220px!important;
  height:52px!important;
  padding:0 17px!important;
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,.12)!important;
  background:rgba(255,255,255,.05)!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search i{
  flex:0 0 auto!important;
  color:rgba(255,255,255,.48)!important;
  font-size:15px!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search input{
  display:block!important;
  width:100%!important;
  min-width:0!important;
  height:auto!important;
  margin:0!important;
  padding:0!important;
  border:0!important;
  outline:0!important;
  background:transparent!important;
  color:#fff!important;
  font-family:inherit!important;
  font-size:15px!important;
  font-weight:650!important;
  line-height:1.2!important;
  appearance:none!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search input::placeholder{color:rgba(255,255,255,.42)!important;}
body.premium-accounts-page #valAccountsToolhead .shop-pill{
  position:relative!important;
  flex:0 0 auto!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__btn,
body.premium-accounts-page #valAccountsToolhead .shop-pill--toggle,
body.premium-accounts-page #valAccountsToolhead .shop-clear-all{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:10px!important;
  width:auto!important;
  min-height:52px!important;
  margin:0!important;
  padding:0 17px!important;
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,.12)!important;
  background:rgba(255,255,255,.05)!important;
  color:rgba(255,255,255,.90)!important;
  font-family:inherit!important;
  font-size:15px!important;
  font-weight:800!important;
  line-height:1!important;
  white-space:nowrap!important;
  cursor:pointer!important;
  box-shadow:none!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__btn:hover,
body.premium-accounts-page #valAccountsToolhead .shop-pill--toggle:hover{
  background:rgba(255,255,255,.08)!important;
  border-color:rgba(255,255,255,.20)!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__btn[aria-expanded="true"],
body.premium-accounts-page #valAccountsToolhead .shop-pill--toggle[aria-pressed="true"]{
  color:#fff!important;
  background:rgba(99,102,241,.18)!important;
  border-color:rgba(99,102,241,.42)!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-clear-all{
  margin-left:auto!important;
  padding:0 24px!important;
  border-color:transparent!important;
  background:linear-gradient(135deg,#7a70ff,#5c5eea)!important;
  color:#fff!important;
  box-shadow:0 8px 22px rgba(99,102,241,.24)!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu{
  position:absolute!important;
  top:calc(100% + 9px)!important;
  left:0!important;
  right:auto!important;
  z-index:500!important;
  min-width:230px!important;
  max-height:340px!important;
  margin:0!important;
  padding:8px!important;
  overflow:auto!important;
  list-style:none!important;
  border:1px solid rgba(255,255,255,.10)!important;
  border-radius:14px!important;
  background:rgba(12,14,29,.98)!important;
  box-shadow:0 20px 55px rgba(0,0,0,.48)!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu[hidden]{display:none!important;}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li{
  display:flex!important;
  align-items:center!important;
  gap:10px!important;
  min-height:42px!important;
  padding:8px 10px!important;
  border-radius:10px!important;
  color:rgba(232,235,247,.78)!important;
  font-size:13px!important;
  font-weight:800!important;
  cursor:pointer!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li:hover,
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu li.is-selected{
  color:#fff!important;
  background:rgba(99,102,241,.16)!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-pill__menu img{
  width:22px!important;
  height:22px!important;
  object-fit:contain!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-popular{
  display:flex!important;
  align-items:center!important;
  flex-wrap:wrap!important;
  gap:8px!important;
  width:100%!important;
  margin:15px 0 0!important;
  padding:15px 0 0!important;
  border:0!important;
  border-top:1px solid rgba(255,255,255,.07)!important;
  background:transparent!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-popular__label{
  color:rgba(255,255,255,.62)!important;
  font-size:13px!important;
  font-weight:800!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-popular__chip{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:7px!important;
  min-height:36px!important;
  margin:0!important;
  padding:0 13px!important;
  border-radius:999px!important;
  border:1px solid rgba(255,255,255,.09)!important;
  background:rgba(255,255,255,.045)!important;
  color:rgba(235,238,249,.78)!important;
  font-size:13px!important;
  font-weight:750!important;
  cursor:pointer!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-popular__chip img{
  width:21px!important;
  height:21px!important;
  object-fit:contain!important;
}
body.premium-accounts-page #valAccountsToolhead .shop-popular__chip:hover,
body.premium-accounts-page #valAccountsToolhead .shop-popular__chip.is-active{
  color:#fff!important;
  background:rgba(99,102,241,.15)!important;
  border-color:rgba(99,102,241,.30)!important;
}
@media(max-width:900px){
  body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search{flex-basis:100%!important;}
  body.premium-accounts-page #valAccountsToolhead .shop-clear-all{margin-left:0!important;}
}
@media(max-width:767px){
  body.premium-accounts-page #valAccountsToolhead{border-radius:17px!important;}
  body.premium-accounts-page #valAccountsToolhead>.nav-tabs{padding:12px!important;}
  body.premium-accounts-page #valAccountsToolhead>.trust-banner{padding:10px 12px!important;font-size:12px!important;}
  body.premium-accounts-page #valAccountsToolhead>.shop-filterbar{padding:12px!important;}
  body.premium-accounts-page #valAccountsToolhead .shop-filterbar__row{
    display:grid!important;
    grid-template-columns:1fr 1fr!important;
    gap:8px!important;
  }
  body.premium-accounts-page #valAccountsToolhead .shop-filterbar__search{
    grid-column:1/-1!important;
    width:100%!important;
    min-width:0!important;
  }
  body.premium-accounts-page #valAccountsToolhead .shop-pill,
  body.premium-accounts-page #valAccountsToolhead .shop-pill__btn,
  body.premium-accounts-page #valAccountsToolhead .shop-pill--toggle{
    width:100%!important;
  }
  body.premium-accounts-page #valAccountsToolhead .shop-clear-all{
    grid-column:1/-1!important;
    width:100%!important;
  }
  body.premium-accounts-page #valAccountsToolhead .shop-pill__menu{
    left:0!important;
    right:auto!important;
    min-width:100%!important;
  }
  body.premium-accounts-page #valAccountsToolhead .shop-popular{
    margin-top:12px!important;
    padding-top:12px!important;
  }
}
</style>
