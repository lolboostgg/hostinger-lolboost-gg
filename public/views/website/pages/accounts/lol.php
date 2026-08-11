<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'premium-accounts-page']) ?>

<section class="lb-shop-hero">
    <div class="lb-shop-hero__inner">
        <div class="lb-shop-hero__icon" aria-hidden="true"><i class="fa-solid fa-helmet-battle"></i></div>
        <div>
            <div class="lb-shop-hero__kicker">Accounts</div>
            <h1 class="lb-shop-hero__title"><?= t('LoL Smurf Accounts') ?></h1>
            <p class="lb-shop-hero__desc"><?= t('Buy hand-leveled League of Legends smurf accounts, ranked or unranked, ready to play instantly. Get rare skins, strong champion pools, fast delivery, and a minimum 14-day warranty.') ?></p>
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
        <a href="/lol/premium-accounts" class="type-card is-active" aria-current="page">
            <span class="type-card__icon" aria-hidden="true">
                <img src="/public/uploads/icons/default2.png" alt="">
            </span>
            <span class="type-card__title">Smurf Accounts</span>
        </a>

        <a href="/lol/accounts" class="type-card">
            <span class="type-card__icon" aria-hidden="true">
                <img src="/public/uploads/icons/challenger.png" alt="">
            </span>
            <span class="type-card__title">Ranked Accounts</span>
        </a>
    </div>

    <div class="accounts-toolhead">
        <div class="nav-tabs">
            <a href="#euw-list" class="active"><i class="fa-solid fa-earth-europe"></i> <?= t('EUW') ?></a>
            <a href="#eune-list"><i class="fa-solid fa-earth-europe"></i> <?= t('EUNE') ?></a>
            <a href="#na-list"><i class="fa-solid fa-earth-americas"></i> <?= t('NA') ?></a>
        </div>

        <?php
            // League of Legends rank tiers mapping (db values -> labels + filter keys)
            // Defined here so the filter toolbar can use it; redeclared further below
            // for the existing rendering logic (harmless, identical values).
            $tiers = [
                0  => 'Unranked',
                1  => 'Iron',
                2  => 'Bronze',
                3  => 'Silver',
                4  => 'Gold',
                5  => 'Platinum',
                6  => 'Emerald',
                7  => 'Diamond',
                8  => 'Master',
                9  => 'Grandmaster',
                10 => 'Challenger',
            ];

            // Same loyalty cashback logic as the LoL order summary.
            // Default is guest cashback, logged-in users use their current loyalty rank.
            $accountCashbackPercent = 1;
            if (defined('CLIENT_DATA') && CLIENT_DATA != false && !empty(CLIENT_DATA['loyalty_rank_id'])) {
                $accountCashbackRank = db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']], 1);
                if (!empty($accountCashbackRank['cashback']) && is_numeric($accountCashbackRank['cashback'])) {
                    $accountCashbackPercent = (float)$accountCashbackRank['cashback'];
                }
            }
        ?>

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
                    <input type="text" id="accountsSearch" placeholder="<?= htmlspecialchars(t('Search...'), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars(t('Search accounts'), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <button type="button" class="shop-mobile-filter-trigger" id="mobileFilterTrigger" aria-haspopup="true" aria-expanded="false" aria-controls="mobileFilterPanel">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    <span><?= t('Filters') ?></span>
                </button>

                <div class="shop-filterbar__panel" id="mobileFilterPanel">
                    <div class="shop-filterbar__panelhead">
                        <span class="shop-filterbar__paneltitle"><?= t('Filters') ?></span>
                        <div class="shop-filterbar__panelactions">
                            <button type="button" id="filterSheetClear"><?= t('Clear Filters') ?></button>
                            <button type="button" id="mobileFilterClose" class="shop-filterbar__close" aria-label="<?= htmlspecialchars(t('Close filters'), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="shop-pill" id="serverFilterPill">
                        <button type="button" class="shop-pill__btn" id="serverFilterTrigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="serverFilterLabel serverFilterValue">
                            <i class="fa-solid fa-globe" aria-hidden="true"></i>
                            <span class="sr-only" id="serverFilterLabel"><?= t('Server') ?></span>
                            <span class="shop-pill__value" id="serverFilterValue"><?= t('EUW') ?></span>
                            <i class="fa-solid fa-chevron-down shop-pill__caret" aria-hidden="true"></i>
                        </button>
                        <ul class="shop-pill__menu" role="listbox" id="serverFilterMenu" aria-labelledby="serverFilterLabel" tabindex="-1" hidden>
                            <li role="option" data-server-filter="euw" data-pane-id="euw-list" aria-selected="true" tabindex="0"><i class="fa-solid fa-earth-europe" aria-hidden="true"></i><span><?= t('EUW') ?></span></li>
                            <li role="option" data-server-filter="eune" data-pane-id="eune-list" aria-selected="false" tabindex="0"><i class="fa-solid fa-earth-europe" aria-hidden="true"></i><span><?= t('EUNE') ?></span></li>
                            <li role="option" data-server-filter="na" data-pane-id="na-list" aria-selected="false" tabindex="0"><i class="fa-solid fa-earth-americas" aria-hidden="true"></i><span><?= t('NA') ?></span></li>
                        </ul>
                    </div>

                    <div class="shop-pill" id="rankFilterPill">
                        <button type="button" class="shop-pill__btn" id="rankFilterTrigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="rankFilterLabel rankFilterValue">
                            <i class="fa-solid fa-medal" aria-hidden="true"></i>
                            <span class="sr-only" id="rankFilterLabel"><?= t('Rank') ?></span>
                            <span class="shop-pill__value" id="rankFilterValue"><?= t('All Ranks') ?></span>
                            <i class="fa-solid fa-chevron-down shop-pill__caret" aria-hidden="true"></i>
                        </button>
                        <ul class="shop-pill__menu" role="listbox" id="rankFilterMenu" aria-labelledby="rankFilterLabel" tabindex="-1" hidden>
                            <li role="option" data-rank-filter="all" aria-selected="true" tabindex="0"><?= t('All Ranks') ?></li>
                            <?php foreach ($tiers as $tid => $tlabel): if ($tid === 0) continue; ?>
                                <li role="option" data-rank-filter="<?= strtolower($tlabel) ?>" data-rank-id="<?= $tid ?>" aria-selected="false" tabindex="0">
                                    <img src="<?= ASSET_URL ?>/core/main/img/lol/ranks/mini/<?= $tid ?>.png" alt="" aria-hidden="true">
                                    <span><?= t($tlabel) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button type="button" class="shop-pill--toggle" id="rankedReadyPill" aria-pressed="false">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span><?= t('Ranked Ready only') ?></span>
                    </button>

                    <div class="shop-pill" id="sortPill">
                        <button type="button" class="shop-pill__btn" id="sortSelectTrigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="sortSelectLabel sortSelectValue">
                            <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                            <span class="sr-only" id="sortSelectLabel"><?= t('Sort by') ?></span>
                            <span class="shop-pill__value" id="sortSelectValue"><?= t('Featured') ?></span>
                            <i class="fa-solid fa-chevron-down shop-pill__caret" aria-hidden="true"></i>
                        </button>
                        <ul class="shop-pill__menu" role="listbox" id="sortSelectMenu" aria-labelledby="sortSelectLabel" tabindex="-1" hidden>
                            <li role="option" data-value="default" aria-selected="true" tabindex="0"><?= t('Featured') ?></li>
                            <li role="option" data-value="price-asc" aria-selected="false" tabindex="0"><?= t('Price: Low to High') ?></li>
                            <li role="option" data-value="price-desc" aria-selected="false" tabindex="0"><?= t('Price: High to Low') ?></li>
                            <li role="option" data-value="rank-asc" aria-selected="false" tabindex="0"><?= t('Rank: Low to High') ?></li>
                            <li role="option" data-value="rank-desc" aria-selected="false" tabindex="0"><?= t('Rank: High to Low') ?></li>
                        </ul>
                    </div>

                    <button type="button" class="shop-clear-all" id="clearAllBtn"><?= t('Clear All') ?></button>

                    <button type="button" class="shop-mobile-filter-apply" id="mobileFilterApply"><?= t('Show results') ?></button>
                </div>
            </div>

            <div class="shop-popular" id="popularRanksRow" aria-label="<?= htmlspecialchars(t('Quick filters'), ENT_QUOTES, 'UTF-8') ?>" hidden>
                <span class="shop-popular__label"><?= t('Popular:') ?></span>
                <!-- populated by JS: only shows ranks that actually have accounts in the current view -->
            </div>
        </div>
    </div>

    <p class="accounts-empty-state" id="accountsEmptyState" hidden>
        <i class="fa-regular fa-face-frown" aria-hidden="true"></i>
        <span><?= t('No accounts match your filters. Try clearing a filter.') ?></span>
        <button type="button" id="accountsEmptyReset"><?= t('Clear filters') ?></button>
    </p>


    <?php
        // $tiers (rank id -> label) is already defined above for the filter toolbar.

        // Normalizes rank (numeric id or string) into a numeric tier id used by the badge assets
        $getRankId = function ($rank) {
            if ($rank === null || $rank === '') {
                return 0;
            }

            if (is_numeric($rank)) {
                return (int)$rank;
            }

            $rankKey = strtolower((string)$rank);
            $map = [
                10 => 'challenger',
                9  => 'grandmaster',
                8  => 'master',
                7  => 'diamond',
                6  => 'emerald',
                5  => 'platinum',
                4  => 'gold',
                3  => 'silver',
                2  => 'bronze',
                1  => 'iron',
            ];

            foreach ($map as $id => $key) {
                if (strpos($rankKey, $key) !== false) {
                    return $id;
                }
            }

            return 0;
        };


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
        foreach ($data as $_serverOrGroup => $_packages) {
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
                        $soldLabel = sprintf(t('Last sold %s minutes ago'), $diffMinutes);
                    } else {
                        $hours = min(24, (int)floor($diffMinutes / 60));
                        $soldLabel = sprintf(t('Last sold %s hour%s ago'), $hours, $hours === 1 ? '' : 's');
                    }

                    $items[] = [
                        'type' => 'sold',
                        'icon' => 'fa-bolt',
                        'label' => $soldLabel,
                    ];
                }
            }

            return $items;
        };

        $packageIsRankedReady = function (array $package) use ($normalizePackageFeatures): bool {
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
        /* --- Unified panel: region tabs + trust line + filter bar all in
           one cohesive card instead of three disconnected floating pieces --- */
        .accounts-toolhead {
            margin: 22px auto 24px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.018));
            border: 1px solid rgba(255,255,255,.08);
            overflow: visible;
            position: relative;
            z-index: 120;
        }

        /* --- Region tabs (EUW/EUNE/NA), now the top section of that panel --- */
        .premium-accounts-page .container .nav-tabs,
        .ranked-accounts-page .container .nav-tabs,
        .nav-tabs {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px !important;
            margin: 0 !important;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .nav-tabs a {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.13);
            background: rgba(255,255,255,.045);
            color: rgba(255,255,255,.8) !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none !important;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .nav-tabs a i { font-size: 14px; color: rgba(255,255,255,.55); }
        .nav-tabs a:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.24);
        }
        .nav-tabs a.active {
            background: linear-gradient(135deg, #8a7dff, #6354e8) !important;
            border-color: transparent;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(123,107,255,.38);
        }
        .nav-tabs a.active i { color: #fff; }

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

        /* --- Trust line: now a quiet sub-section of the unified panel
           instead of its own loud floating pill --- */
        .trust-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 0 !important;
            padding: 12px 20px;
            border-radius: 0;
            width: 100%;
            background: transparent;
            border: 0;
            border-bottom: 1px solid rgba(255,255,255,.07);
            font-size: 13.5px;
            font-weight: 700;
            color: rgba(255,255,255,.62);
        }
        .trust-banner i { color: rgba(138,125,255,.85); margin-right: 5px; font-size: 13px; }
        .trust-banner__dot { opacity: .3; }
        @media (max-width: 600px) {
            .trust-banner { font-size: 12px; gap: 8px; padding: 10px 14px; }
            .trust-banner i { font-size: 11px; }
        }

        /* --- Compact per-card trust pill (replaces 3 repeated badges) --- */
        .premium-accounts-page .container .accounts-grid .account .foot .trust-row.trust-row--compact,
        .ranked-accounts-page .container .accounts-grid .account .foot .trust-row.trust-row--compact,
        .foot .trust-row.trust-row--compact {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 14px auto 0 !important;
            text-align: center !important;
            float: none !important;
        }
        .trust-row--compact .trust-badge {
            font-size: 11.5px;
            padding: 6px 13px;
            opacity: .85;
            margin: 0 auto;
        }

        /* --- Uniform card heights & bottom-aligned footers, across every row --- */
        .accounts-grid {
            align-items: stretch;
        }
        .accounts-grid .account {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 560px;
        }
        .accounts-grid .account .foot {
            margin-top: auto;
        }
        @media (max-width: 900px) {
            .accounts-grid .account { min-height: 0; }
        }

        /* --- Feature list: always a tight, fixed-gap vertical stack ---
           (previously the <ul> was a CSS grid that stretched its row gaps
           to fill leftover card height, so shorter cards got much bigger
           gaps between bullet points than longer ones. Flex with no grow
           keeps every card's list spacing identical.) */
        .accounts-grid .account ul {
            display: flex !important;
            flex-direction: column !important;
            flex: 0 0 auto !important;
            gap: 10px !important;
            align-content: flex-start !important;
        }
        .accounts-grid .account ul li {
            margin: 0 !important;
        }

        /* --- Shop-style filter bar (search + dropdown pills + clear all) ---
           No longer its own standalone card: it's nested inside
           .accounts-toolhead now, which provides the shared border/background. --- */
        .shop-filterbar {
            margin: 0;
            padding: 16px 20px 18px;
            border-radius: 0;
            background: transparent;
            border: 0;
            position: relative;
            z-index: 130;
        }
        .shop-filterbar__row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .shop-filterbar__search {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1 1 220px;
            min-width: 160px;
            padding: 12px 16px;
            border-radius: 11px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.14);
        }
        .shop-filterbar__search i { color: rgba(255,255,255,.45); font-size: 14px; }
        .shop-filterbar__search input {
            flex: 1 1 auto;
            min-width: 0;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
        }
        .shop-filterbar__search input::placeholder { color: rgba(255,255,255,.4); }

        .shop-pill { position: relative; flex: 0 0 auto; }
        .shop-pill__btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 11px;
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.05);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
        }
        .shop-pill__btn:hover,
        .shop-pill__btn[aria-expanded="true"] {
            background: rgba(255,255,255,.09);
            border-color: rgba(255,255,255,.28);
        }
        .shop-pill__btn i:first-child { color: rgba(255,255,255,.55); font-size: 14px; }
        .shop-pill__caret { font-size: 12px; color: rgba(255,255,255,.5); transition: transform .15s ease; }
        .shop-pill__btn[aria-expanded="true"] .shop-pill__caret { transform: rotate(180deg); }
        .shop-pill__value { max-width: 170px; overflow: hidden; text-overflow: ellipsis; }

        /* Toggle-style pill ("Ranked Ready only") */
        .shop-pill--toggle {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 12px 16px;
            border-radius: 11px;
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.05);
            color: rgba(255,255,255,.78);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .shop-pill--toggle i { font-size: 14px; color: rgba(255,255,255,.4); transition: color .15s ease; }
        .shop-pill--toggle:hover { background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.26); }
        .shop-pill--toggle[aria-pressed="true"] {
            background: linear-gradient(135deg, #8a7dff, #6354e8);
            border-color: transparent;
            color: #fff;
        }
        .shop-pill--toggle[aria-pressed="true"] i { color: #fff; }

        /* Dropdown menu, shared by the Rank pill + Sort pill */
        .shop-pill__menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 230px;
            max-height: 320px;
            overflow-y: auto;
            list-style: none;
            margin: 0;
            padding: 8px;
            border-radius: 13px;
            background: #15131f;
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 18px 40px rgba(0,0,0,.5);
            z-index: 9999;
        }
        .shop-pill__menu[hidden] { display: none; }
        .shop-pill__menu li[role="option"] {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            color: rgba(255,255,255,.82);
            cursor: pointer;
            white-space: nowrap;
        }
        .shop-pill__menu li[role="option"] img { width: 18px; height: 18px; display: block; }
        .shop-pill__menu li[role="option"] > i { width: 18px; text-align: center; color: rgba(255,255,255,.56); font-size: 14px; }
        .shop-pill__menu li[role="option"]:hover,
        .shop-pill__menu li[role="option"]:focus {
            background: rgba(255,255,255,.08);
            color: #fff;
            outline: none;
        }
        .shop-pill__menu li[role="option"][aria-selected="true"] {
            background: linear-gradient(135deg, #8a7dff, #6354e8);
            color: #fff;
        }
        /* Ranks with zero matching accounts in the current view are hidden
           from the dropdown entirely (set by JS via data-unavailable). */
        .shop-pill__menu li[role="option"][data-unavailable="1"] {
            display: none;
        }

        .tab-content {
            position: relative;
            z-index: 1;
        }

        .shop-clear-all {
            flex: 0 0 auto;
            padding: 12px 22px;
            border-radius: 11px;
            border: none;
            background: linear-gradient(135deg, #8a7dff, #6354e8);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(123,107,255,.38);
            transition: opacity .15s ease, transform .12s ease;
        }
        .shop-clear-all:hover { opacity: .92; }
        .shop-clear-all:active { transform: scale(.97); }

        /* Popular quick-filter chips — only ranks that currently have
           matching accounts are rendered here (built by JS). */
        .shop-popular {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .shop-popular[hidden] { display: none; }
        .shop-popular__label {
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,.5);
            margin-right: 2px;
        }
        .shop-popular__chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 15px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.13);
            background: rgba(255,255,255,.045);
            color: rgba(255,255,255,.78);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .shop-popular__chip img { width: 16px; height: 16px; display: block; }
        .shop-popular__chip:hover {
            background: rgba(255,255,255,.09);
            border-color: rgba(255,255,255,.24);
        }
        .shop-popular__chip.is-active {
            background: linear-gradient(135deg, #8a7dff, #6354e8);
            border-color: transparent;
            color: #fff;
        }

        .sr-only {
            position: absolute;
            width: 1px; height: 1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
        }

        /* --- Mobile: Account type cards compact nebeneinander wie im Shop --- */
        @media (max-width: 767px) {
            .account-type-cards {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px !important;
                margin: 10px 0 14px !important;
            }
            .account-type-cards .type-card {
                min-width: 0 !important;
                padding: 12px 13px !important;
                border-radius: 16px !important;
                background: rgba(255,255,255,.045) !important;
            }
            .account-type-cards .type-card.is-active {
                border-color: rgba(123,107,255,.75) !important;
                background: linear-gradient(180deg, rgba(123,107,255,.18), rgba(255,255,255,.045)) !important;
                box-shadow: 0 0 24px rgba(123,107,255,.16) !important;
            }
            .account-type-cards .type-card__top {
                display: flex !important;
                align-items: center !important;
                gap: 10px !important;
            }
            .account-type-cards .type-card__icon {
                flex: 0 0 32px !important;
                width: 32px !important;
                height: 32px !important;
            }
            .account-type-cards .type-card__titles {
                min-width: 0 !important;
            }
            .account-type-cards .type-card__title {
                font-size: 12px !important;
                line-height: 1.15 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            .account-type-cards .type-card__subtitle,
            .account-type-cards .type-card__badge,
            .account-type-cards .type-card__pills,
            .account-type-cards .type-card__cta {
                display: none !important;
            }
        }

        /* --- Mobile: hide the site's fixed mobile header bars once the
           person scrolls (same pattern as the shop_lol page), and keep the
           filter bar pinned flush at the very top of the viewport, always —
           same as shop_lol's own final behavior. --- */
        @media (max-width: 767px) {
            .premium-accounts-page .navbar-mobile,
            .premium-accounts-page .lb-mobile-gamebar {
                transition: transform .22s ease, opacity .18s ease, visibility .18s ease;
                will-change: transform, opacity;
            }
            .premium-accounts-page.lol-mobile-bars-hidden .navbar-mobile,
            .premium-accounts-page.lol-mobile-bars-hidden .lb-mobile-gamebar {
                transform: translateY(-115%) !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            /* Letting the filter bar's rounded corners get clipped by the
               panel only matters on desktop — on mobile it needs to be able
               to stick above its own panel while scrolling. */
            .accounts-toolhead { overflow: visible; }

            .shop-filterbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 500;
                margin: 0;
                padding: calc(10px + env(safe-area-inset-top)) 14px 10px;
                border-radius: 0 0 16px 16px;
                background: #15131f;
                border: 0;
                border-bottom: 1px solid rgba(255,255,255,.10);
                box-shadow: 0 10px 30px rgba(0,0,0,.35);
                transform: translateY(-110%);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: transform .22s ease, opacity .18s ease, visibility .18s ease;
            }
            .premium-accounts-page.lol-mobile-bars-hidden .shop-filterbar {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            body.lol-accounts-grid-focus .lb-mobile-bottomnav,
            body.lol-accounts-grid-focus [class*="lb-mobile-bottomnav--"] {
                display: none !important;
            }
        }


        /* --- Mobile: collapse the filter bar to a search box + a single
           "Filters" button (like the GameBoost reference). The bar itself
           stays put — like a real navbar — and tapping "Filters" drops a
           panel down directly below it. The panel only covers the area
           below the bar; nothing above it is dimmed or hidden. --- */
        .shop-mobile-filter-trigger { display: none; }
        .shop-filterbar__panel { display: contents; }
        .shop-filterbar__panelhead,
        .shop-mobile-filter-apply {
            display: none;
        }

        @media (max-width: 767px) {
            .shop-filterbar__row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 10px;
                align-items: center;
            }
            .shop-filterbar__search { height: 48px; }
            .shop-mobile-filter-trigger {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                height: 48px;
                padding: 0 16px;
                border-radius: 12px;
                border: 1px solid rgba(255,255,255,.16);
                background: rgba(255,255,255,.06);
                color: #fff;
                font-size: 14px;
                font-weight: 800;
                white-space: nowrap;
                cursor: pointer;
            }
            .shop-mobile-filter-trigger[aria-expanded="true"] {
                background: linear-gradient(135deg, #8a7dff, #6354e8);
                border-color: transparent;
            }

            /* Collapsed by default: the panel (rank/ready/sort/clear-all)
               doesn't take up any space until it's opened. */
            .shop-filterbar__panel {
                display: none;
            }

            /* --- Open state: a panel anchored right below the bar, not a
               fullscreen takeover. The bar (and everything above it) stays
               completely normal/visible — only the area below the bar,
               which the panel opaquely covers, changes. --- */
            body.lol-filters-sheet-open { overflow: hidden; }
            /* The site's mobile bottom nav (Boosting/Accounts/Items) would
               otherwise sit on top of the panel and clip its bottom portion
               — hide it while the panel is open. */
            body.lol-filters-sheet-open .lb-mobile-bottomnav,
            body.lol-filters-sheet-open [class*="lb-mobile-bottomnav--"] {
                display: none !important;
            }
            body.lol-filters-sheet-open .shop-popular { display: none !important; }

            body.lol-filters-sheet-open .shop-filterbar__panel {
                display: flex;
                flex-direction: column;
                gap: 14px;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 700;
                height: 100dvh;
                overflow-y: auto;
                padding: calc(18px + env(safe-area-inset-top)) 18px calc(18px + env(safe-area-inset-bottom));
                background: #15131f;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
            body.lol-filters-sheet-open .shop-filterbar__panelhead {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .shop-filterbar__panelactions {
                display: inline-flex;
                align-items: center;
                gap: 10px;
            }
            .shop-filterbar__close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 999px;
                border: 1px solid rgba(255,255,255,.14);
                background: rgba(255,255,255,.07);
                color: #fff;
                font-size: 17px;
                cursor: pointer;
            }
            .shop-filterbar__close:hover {
                background: rgba(255,255,255,.12);
            }


            /* Mobile: normale Quick-Filter-Zeile wie im Shop, aber platzsparend einzeilig scrollbar. */
            .premium-accounts-page .shop-popular,
            .ranked-accounts-page .shop-popular,
            .shop-popular {
                display: flex !important;
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                gap: 8px;
                margin-top: 10px;
                padding: 10px 1px 2px;
                border-top: 1px solid rgba(255,255,255,.07);
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }
            .shop-popular::-webkit-scrollbar { display: none; }
            .shop-popular__label,
            .shop-popular__chip {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            /* Mobile Filter Sheet: sauberer vertikaler Aufbau, Dropdowns öffnen unterhalb des Feldes. */
            body.lol-filters-sheet-open .shop-filterbar__panel {
                gap: 12px;
            }
            body.lol-filters-sheet-open .shop-pill {
                flex-direction: column;
                align-items: stretch;
            }
            body.lol-filters-sheet-open .shop-pill__btn {
                display: grid;
                grid-template-columns: 22px minmax(0, 1fr) 18px;
                align-items: center;
                gap: 10px;
                min-height: 40px;
                padding: 10px 14px;
            }
            body.lol-filters-sheet-open .shop-pill__value {
                max-width: none;
                text-align: left;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            body.lol-filters-sheet-open .shop-pill--toggle {
                justify-content: flex-start;
                align-items: center;
                gap: 10px;
                min-height: 40px;
                padding: 10px 14px;
                text-align: left;
                white-space: nowrap;
            }
            body.lol-filters-sheet-open .shop-pill--toggle span {
                flex: 1 1 auto;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            body.lol-filters-sheet-open .shop-pill__menu {
                position: static;
                width: 100%;
                min-width: 0;
                max-height: 260px;
                margin-top: 8px;
                padding: 6px;
                border-radius: 12px;
                box-shadow: none;
            }
            body.lol-filters-sheet-open .shop-pill__menu li[role="option"] {
                min-height: 38px;
                padding: 9px 11px;
            }
            body.lol-filters-sheet-open .shop-clear-all {
                justify-content: center;
                min-height: 42px;
                padding: 11px 14px;
            }
            .shop-filterbar__paneltitle {
                font-size: 19px;
                font-weight: 900;
                color: #fff;
            }
            #filterSheetClear {
                border: 0;
                background: transparent;
                color: rgba(255,255,255,.6);
                font-size: 14px;
                font-weight: 800;
                padding: 6px 4px;
                cursor: pointer;
            }
            body.lol-filters-sheet-open .shop-pill,
            body.lol-filters-sheet-open .shop-pill--toggle,
            body.lol-filters-sheet-open .shop-clear-all {
                display: flex;
                width: 100%;
            }
            body.lol-filters-sheet-open .shop-pill__btn,
            body.lol-filters-sheet-open .shop-pill--toggle {
                width: 100%;
                justify-content: space-between;
            }
            body.lol-filters-sheet-open .shop-pill__menu {
                position: static;
                width: 100%;
                margin-top: 8px;
                box-shadow: none;
            }
            body.lol-filters-sheet-open .shop-mobile-filter-apply {
                display: block;
                margin-top: auto;
                width: 100%;
                padding: 15px 0;
                border-radius: 12px;
                border: 0;
                background: linear-gradient(135deg, #8a7dff, #6354e8);
                color: #fff;
                font-size: 16px;
                font-weight: 800;
                cursor: pointer;
                box-shadow: 0 6px 18px rgba(123,107,255,.4);
            }
        }


        /* --- Empty state when filters match nothing ---
           IMPORTANT: default is display:none here, and we only switch to
           flex when the [hidden] attribute is absent. Previously this rule
           set display:flex unconditionally, which (since author CSS always
           outranks the browser's built-in "[hidden]{display:none}" rule)
           made the message show permanently regardless of what the JS set
           the hidden attribute to. */
        .accounts-empty-state {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-align: center;
            padding: 48px 20px;
            color: rgba(255,255,255,.75);
            font-size: 14px;
        }
        .accounts-empty-state:not([hidden]) {
            display: flex;
        }
        .accounts-empty-state i { font-size: 26px; color: rgba(255,255,255,.45); }
        .accounts-empty-state button {
            margin-top: 4px;
            padding: 8px 16px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.18);
            background: rgba(255,255,255,.06);
            color: #fff;
            font-weight: 700;
            font-size: 12.5px;
            cursor: pointer;
        }
        .accounts-empty-state button:hover { background: rgba(255,255,255,.12); }

        /* --- Notify-me CTA for sold-out cards --- */
        .notify-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            margin-top: 8px;
            padding: 9px 0;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.16);
            background: transparent;
            color: rgba(255,255,255,.8);
            font-weight: 700;
            font-size: 12.5px;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }
        .notify-cta:hover { background: rgba(255,255,255,.08); color: #fff; }

        /* --- Slightly larger, easier-to-scan rank badge in the card header --- */
        .account .corner-meta .rank-badge img {
            width: 26px;
            height: 26px;
        }

        /* --- V7: larger typography for 0.88 page zoom, especially filter and quick chips --- */
        .accounts-toolhead .nav-tabs a {
            font-size: 17px !important;
            padding: 14px 30px !important;
        }
        .accounts-toolhead .trust-banner {
            font-size: 15px !important;
        }
        .shop-filterbar {
            padding: 20px 22px 22px !important;
        }
        .shop-filterbar__row {
            gap: 12px !important;
        }
        .shop-filterbar__search {
            padding: 15px 18px !important;
            border-radius: 13px !important;
        }
        .shop-filterbar__search i {
            font-size: 17px !important;
        }
        .shop-filterbar__search input {
            font-size: 17px !important;
            font-weight: 800 !important;
        }
        .shop-pill__btn,
        .shop-pill--toggle {
            min-height: 48px !important;
            padding: 14px 18px !important;
            border-radius: 13px !important;
            font-size: 17px !important;
            font-weight: 900 !important;
        }
        .shop-pill__btn i:first-child,
        .shop-pill--toggle i {
            font-size: 17px !important;
        }
        .shop-pill__caret {
            font-size: 14px !important;
        }
        .shop-pill__value {
            max-width: 210px !important;
        }
        .shop-clear-all {
            min-height: 48px !important;
            padding: 14px 26px !important;
            border-radius: 13px !important;
            font-size: 17px !important;
            font-weight: 900 !important;
        }
        .shop-pill__menu {
            min-width: 270px !important;
            padding: 10px !important;
            border-radius: 15px !important;
        }
        .shop-pill__menu li[role="option"] {
            padding: 13px 14px !important;
            border-radius: 10px !important;
            font-size: 16px !important;
            font-weight: 850 !important;
        }
        .shop-pill__menu li[role="option"] img {
            width: 21px !important;
            height: 21px !important;
        }
        .shop-popular {
            gap: 11px !important;
            margin-top: 16px !important;
            padding-top: 16px !important;
        }
        .shop-popular__label {
            font-size: 15px !important;
            font-weight: 900 !important;
        }
        .shop-popular__chip {
            gap: 9px !important;
            padding: 10px 18px !important;
            font-size: 15.5px !important;
            font-weight: 900 !important;
        }
        .shop-popular__chip img {
            width: 19px !important;
            height: 19px !important;
        }

        @media (max-width: 767px) {
            .account-type-cards .type-card__title {
                font-size: 15px !important;
            }
            .shop-filterbar {
                padding: calc(12px + env(safe-area-inset-top)) 14px 12px !important;
            }
            .shop-filterbar__search,
            .shop-mobile-filter-trigger {
                height: 52px !important;
            }
            .shop-mobile-filter-trigger {
                font-size: 16px !important;
                font-weight: 900 !important;
                padding: 0 18px !important;
            }
            .shop-filterbar__search input {
                font-size: 16px !important;
            }
            .shop-popular__label {
                font-size: 14px !important;
            }
            .shop-popular__chip {
                padding: 9px 16px !important;
                font-size: 14.5px !important;
            }
            body.lol-filters-sheet-open .shop-pill__btn,
            body.lol-filters-sheet-open .shop-pill--toggle {
                min-height: 54px !important;
                font-size: 17px !important;
            }
            body.lol-filters-sheet-open .shop-mobile-filter-apply {
                padding: 17px 0 !important;
                font-size: 17px !important;
            }
        }


        /* Cashback moved into the trust badge so the price stays clean */
        .trust-row--compact .trust-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
            gap: 5px !important;
            line-height: 1.2 !important;
        }
        .trust-row--compact .cashback-trust {
            color: #00e6a8;
            font-weight: 900;
            white-space: nowrap;
            text-shadow: 0 0 14px rgba(0, 230, 168, .16);
        }
        .account.is-sold-out .trust-row--compact .cashback-trust {
            opacity: .65;
            filter: grayscale(.15);
        }
        @media (max-width: 767px) {
            .trust-row--compact .trust-badge {
                max-width: 92% !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }

    </style>

    <div class="tab-content">
        <?php foreach ($data as $server => $packages): ?>
            <div id="<?= $server ?>-list" class="tab-pane <?= $server == 'euw' ? 'active' : '' ?>">
                <div class="accounts-grid">
                    <?php foreach ($packages as $package): ?>
                        <?php
                            $rankId    = $getRankId($package['rank'] ?? 0);
                            $rankLabel = $tiers[$rankId] ?? 'Unranked';
                            $rankTier  = strtolower($rankLabel);

                            $baseFeatures = $normalizePackageFeatures($package['features'] ?? '');
                            $customFeatures = $normalizePackageFeatures($package['custom_features'] ?? '');
                            $package['features'] = array_values(array_unique(array_merge($baseFeatures, $customFeatures)));

                            $isRankedReady = $packageIsRankedReady($package);
                            $rankedReadyClass = $isRankedReady ? 'ranked-ready-status--ready' : 'ranked-ready-status--not-ready';
                            $rankedReadyLabel = $isRankedReady ? t('Ranked Ready') : t('Not Ranked Ready');
                            $rankedReadyText = $isRankedReady
                                ? t('This account is ranked ready. You can start ranked games immediately after purchase.')
                                : t('This account is not ranked ready yet. You need to play 10 normal games before ranked is available.');
                            $accountUrgency = $buildAccountUrgency($package);
                        ?>

                        <div class="account <?= $package['available'] == 0 ? 'is-sold-out' : '' ?>" data-rank="<?= $rankTier ?>" data-rank-id="<?= $rankId ?>" data-ranked-ready="<?= $isRankedReady ? '1' : '0' ?>" data-price="<?= (float)$package['price'] ?>" data-available="<?= (int)$package['available'] ?>">
                            <div class="head">
                                <div class="corner-meta">
                                    <span class="server-badge">
                                        <?php if (in_array(strtolower($server), ['euw','eune'])): ?>
                                            <i class="fa-solid fa-earth-europe"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-earth-americas"></i>
                                        <?php endif; ?>
                                        <?= strtoupper($server) ?>
                                    </span>

                                    <span class="rank-badge" aria-hidden="true" title="<?= htmlspecialchars($rankLabel) ?>">
                                        <img src="<?= ASSET_URL ?>/core/main/img/lol/ranks/mini/<?= $rankId ?>.png" alt="<?= htmlspecialchars($rankLabel) ?>">
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
                                <?php
                                    $cashbackBase = (float) util_format_price_display($package['price']);
                                    $cashbackAmount = $cashbackBase * ($accountCashbackPercent / 100);
                                ?>
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
                                    <button type="button" class="notify-cta" onclick="window.Tawk_API && (Tawk_API.maximize ? Tawk_API.maximize() : Tawk_API.toggle())">
                                        <i class="fa-solid fa-bell"></i> <?= t('Notify me when back') ?>
                                    </button>
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

                            <div class="trust-row trust-row--compact" aria-label="Trust badges">
                                <span class="trust-badge is-accent"><i class="fa-solid fa-shield-check"></i> <?= t('Trusted') ?> · <?= t('Instant') ?> · <?= t('Warranty') ?> · <span class="cashback-trust"><?= t('Earn') ?> <?php if ($_SESSION['currency'] == 'USD'): ?>$<?= number_format(round($cashbackAmount * get_exchange_rate(), 2), 2) ?><?php else: ?>€<?= number_format(round($cashbackAmount, 2), 2) ?><?php endif; ?> <?= t('Cashback') ?></span></span>
                            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="sec-faqs">
    <div class="top">
        <h4><?= t('Frequently Asked Questions 🤔') ?></h4>
    </div>

    <div class="accordion">
        <div class="accordion-item active">
            <div class="accordion-header">
                <h5><?= t('Can my Premium Account get banned?') ?></h5>
            </div>
            <div class="accordion-content" style="display: block;">
                <p><?= t('All of our accounts are 100% ban safe. We sell only Handleveled accounts with unverified e-mail and
                    lifetime warranty.') ?></p>
            </div>
        </div>
        <div class="accordion-item">
            <div class="accordion-header">
                <h5><?= t('How long it takes to deliver my Premium account?') ?></h5>
            </div>
            <div class="accordion-content">
                <p><?= t('The delivery of your Premium account is instant. As soon as you complete the purchase, you will
                    receive immediate access and you can start playing on your ready fresh, unranked account.') ?></p>
            </div>
        </div>
        <div class="accordion-item">
            <div class="accordion-header">
                <h5><?= t('What is the warranty on Premium accounts?') ?></h5>
            </div>
            <div class="accordion-content">
                <p><?= t('We offer lifetime warranty and 24/7 human support for every Premium account!') ?></p>
            </div>
        </div>
        <div class="accordion-item">
            <div class="accordion-header">
                <h5><?= t('Can I change the e-mail of the account?') ?></h5>
            </div>
            <div class="accordion-content">
                <p><?= t('Yes! Once you have purchased a Premium account, you can change yourself the Email or contact us on
                    Live Chat and we will help you to change anything you need.') ?></p>
            </div>
        </div>
        <div class="accordion-item">
            <div class="accordion-header">
                <h5><?= t('What is the difference between a Smurf Account and a Premium Account?') ?></h5>
            </div>
            <div class="accordion-content">
                <p><?= t('Smurf Accounts are accounts that are made in large quantities, these are riskier than Premium
                    Accounts. These are leveled entirely by humans, without the use of third-party software. Our
                    accounts are tested by European/American High-ELO players who have reached Master Tier+ without any
                    problems.') ?></p>
            </div>
        </div>
        <div class="accordion-item">
            <div class="accordion-header">
                <h5><?= t('How are the Premium Accounts leveled?') ?></h5>
            </div>
            <div class="accordion-content">
                <p><?= t('The Premium Accounts are leveled exclusively in the Aram game mode. This means that the normal MMR
                    remains unaffected and you start higher after the ranked placements.') ?></p>
            </div>
        </div>
    </div>
</div>


    <section class="how-it-works">
        <div class="how-inner">
            <h2><?= t('How It Works') ?></h2>
            <p class="how-sub"><?= t('Secure, fast, and simple — from picking your account to your first game.') ?></p>

            <div class="how-grid">
                <div class="how-card">
                    <div class="how-top">
                        <span class="how-icon"><i class="fa-solid fa-list-check"></i></span>
                        <span class="how-step">1</span>
                    </div>
                    <h3><?= t('Choose Your Account') ?></h3>
                    <p><?= t('Browse verified accounts by region, rank, price and more. Use filters to find your perfect fit.') ?></p>
                </div>

                <div class="how-card">
                    <div class="how-top">
                        <span class="how-icon"><i class="fa-solid fa-lock"></i></span>
                        <span class="how-step">2</span>
                    </div>
                    <h3><?= t('Checkout Securely') ?></h3>
                    <p><?= t('Pay through encrypted providers. Your order is created instantly and you’ll receive updates via email.') ?></p>
                </div>

                <div class="how-card">
                    <div class="how-top">
                        <span class="how-icon"><i class="fa-solid fa-key"></i></span>
                        <span class="how-step">3</span>
                    </div>
                    <h3><?= t('Get Credentials') ?></h3>
                    <p><?= t('Access details on the confirmation page and in your email. Recovery info is included when available.') ?></p>
                </div>

                <div class="how-card">
                    <div class="how-top">
                        <span class="how-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <span class="how-step">4</span>
                    </div>
                    <h3><?= t('Secure & Play') ?></h3>
                    <p><?= t('Change password and email immediately, enable 2FA where possible — then jump into your first match.') ?></p>
                </div>
            </div>

            <div class="how-bottom">
                <div class="how-note">
                    <i class="fa-regular fa-circle-check"></i>
                    <span><?= t('Need help with a purchase or transfer? Our support is online 24/7.') ?></span>
                </div>

                <button class="how-cta" type="button" onclick="window.Tawk_API && (Tawk_API.maximize ? Tawk_API.maximize() : Tawk_API.toggle())">
                    <i class="fa-solid fa-message"></i>
                    <?= t('Request Custom Account') ?>
                </button>
            </div>
        </div>
    </section>



<div class="container">
    <section class="seo-hero" aria-label="SEO: About LoLBoostGG">
        <div class="seo-hero__content">
            <h2><?= t('About Us') ?></h2>

            <p><?= t('Unlock the next level of your League of Legends journey with a unique opportunity just a click away! At') ?> <b><?= t('LoLBoostGG') ?></b><?= t(', we\'ve curated a selection of premium accounts that are your golden ticket to soaring through the ranks with ease and style.') ?></p>

            <p><?= t('Imagine stepping into the arena, equipped with a powerhouse account that reflects your true gaming spirit and ambition. That\'s what we offer on our premium accounts page; a chance to Boost My League of Legends Account and embrace the game like never before.') ?></p>

            <p><?= t('Be sure that our highly specialised team will provide an unmatched experience. Precision, competence, and a profound understanding of game dynamics assure your successful and enriching rise. Learn and excel from the best.') ?></p>
        </div>
    </section>

    <section class="seo-below" aria-label="SEO: More information">
        <h3 class="seo-question"><?= t('Have you ever thought, I wish I could Boost My Lol Account effortlessly?') ?></h3>

        <div class="seo-block">
            <p><?= t('Well, your wish is our command. Our premium accounts are handpicked, ensuring you get access to an experience that\'s not just about higher ranks but also about diving deep into the more prosperous, more competitive aspects of League of Legends.') ?></p>
            <p><?= t('This isn\'t just a shortcut; it is a soar right into a realm where your abilities can shine, supported via an account that mirrors your passion for the game.') ?></p>
            <p><?= t('With') ?> <b><?= t('LoLBoostGG') ?></b><?= t(', you\'re not just getting an account; you\'re unlocking a treasure chest of possibilities, where every match is a step closer to the pinnacle of League of Legends glory.') ?></p>
            <p><?= t('Dive into our collection and select the key to your future triumphs. Don\'t let the grind hold you back any longer. It\'s time to elevate your recreation, show off your natural ability, and dominate the battlefield with self-belief and delight. Your premium League of Legends account awaits you; grab it now and transform your gaming journey into an epic saga of victory and courage!') ?></p>
        </div>
    </section>
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

    // --- Mobile: hide the fixed mobile bars (like shop_lol does) once the
    // person scrolls. The filter bar itself just sticks flat at top:0 via
    // CSS now, so no offset calculation is needed for it anymore. ---
    function updateMobileBarsHidden() {
        if (window.innerWidth > 767) {
            document.body.classList.remove('lol-mobile-bars-hidden');
            return;
        }
        document.body.classList.toggle('lol-mobile-bars-hidden', window.scrollY > 8);
    }

    function getActiveAccountsGrid() {
        var activePane = document.querySelector('.tab-pane.active');
        return (activePane && activePane.querySelector('.accounts-grid')) || document.querySelector('.accounts-grid');
    }

    function updateAccountsGridFocus() {
        if (window.innerWidth > 767) {
            document.body.classList.remove('lol-accounts-grid-focus');
            return;
        }

        var grid = getActiveAccountsGrid();
        if (!grid) {
            document.body.classList.remove('lol-accounts-grid-focus');
            return;
        }

        var rect = grid.getBoundingClientRect();
        var reachedGrid = rect.top <= window.innerHeight * 0.82 && rect.bottom > 80;
        document.body.classList.toggle('lol-accounts-grid-focus', reachedGrid);
    }

    function updateMobileChrome() {
        updateMobileBarsHidden();
        updateAccountsGridFocus();
    }

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    // scrollToAccountTypeCards() used to run unconditionally here on every page
    // load, forcing every fresh visit to jump down to the card grid instead of
    // loading at the top. Removed — nothing else calls it, so it's dead code now.

    updateMobileChrome();
    window.addEventListener('scroll', updateMobileChrome, { passive: true });
    window.addEventListener('resize', updateMobileChrome);
    document.querySelectorAll('.nav-tabs a').forEach(function (tab) {
        tab.addEventListener('click', function () {
            setTimeout(updateMobileChrome, 80);
        });
    });
})();
</script>

<script>
(function () {
    'use strict';

    var filterbar = document.querySelector('.shop-filterbar');
    if (!filterbar) return;

    var emptyState      = document.getElementById('accountsEmptyState');
    var emptyResetBtn   = document.getElementById('accountsEmptyReset');
    var searchInput     = document.getElementById('accountsSearch');
    var rankedReadyPill = document.getElementById('rankedReadyPill');
    var clearAllBtn     = document.getElementById('clearAllBtn');
    var popularRow      = document.getElementById('popularRanksRow');

    // Mobile filter panel (search + a single "Filters" button on small
    // screens; tapping it drops a panel down directly below the bar — see CSS).
    var mobileFilterTrigger = document.getElementById('mobileFilterTrigger');
    var mobileFilterPanel   = document.getElementById('mobileFilterPanel');
    var mobileFilterApply   = document.getElementById('mobileFilterApply');
    var mobileFilterClose   = document.getElementById('mobileFilterClose');
    var filterSheetClear    = document.getElementById('filterSheetClear');

    var serverFilterTrigger = document.getElementById('serverFilterTrigger');
    var serverFilterMenu    = document.getElementById('serverFilterMenu');
    var serverFilterValue   = document.getElementById('serverFilterValue');

    var rankFilterTrigger = document.getElementById('rankFilterTrigger');
    var rankFilterMenu    = document.getElementById('rankFilterMenu');
    var rankFilterValue   = document.getElementById('rankFilterValue');

    var sortTrigger = document.getElementById('sortSelectTrigger');
    var sortMenu    = document.getElementById('sortSelectMenu');
    var sortValueEl = document.getElementById('sortSelectValue');

    // We track which region tab is active ourselves (via its href -> matching
    // pane id) instead of trusting computed CSS visibility — the site's own
    // tab-switch script may show/hide panes in a way we can't reliably detect
    // from outside, which was the actual cause of the empty-state always
    // showing. Our own click-tracking is 100% reliable since href="#xxx-list"
    // always matches the pane's id="xxx-list" in this template.
    var initialActiveTab = document.querySelector('.nav-tabs a.active') || document.querySelector('.nav-tabs a');
    var state = {
        rank: 'all',
        rankedReadyOnly: false,
        sort: 'default',
        search: '',
        server: initialActiveTab ? (initialActiveTab.getAttribute('href') || '').replace('#', '').replace('-list', '') : 'euw',
        activePaneId: initialActiveTab ? (initialActiveTab.getAttribute('href') || '').replace('#', '') : null
    };

    // --- Generic accessible dropdown, shared by the Rank pill and Sort pill ---
    function setupDropdown(trigger, menu, onSelect) {
        if (!trigger || !menu) return { options: [], close: function () {}, open: function () {} };
        var options = Array.prototype.slice.call(menu.querySelectorAll('li[role="option"]'));

        function availableOptions() {
            return options.filter(function (o) { return o.getAttribute('data-unavailable') !== '1'; });
        }

        function close() {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        }

        function open() {
            document.querySelectorAll('.shop-pill__menu').forEach(function (m) {
                if (m !== menu) m.hidden = true;
            });
            document.querySelectorAll('.shop-pill__btn[aria-expanded="true"]').forEach(function (b) {
                if (b !== trigger) b.setAttribute('aria-expanded', 'false');
            });
            menu.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            var avail = availableOptions();
            var selected = menu.querySelector('li[aria-selected="true"]:not([data-unavailable="1"])') || avail[0] || options[0];
            if (selected) selected.focus();
        }

        trigger.addEventListener('click', function () {
            var isOpen = trigger.getAttribute('aria-expanded') === 'true';
            if (isOpen) { close(); } else { open(); }
        });
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                open();
            }
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                if (option.getAttribute('data-unavailable') === '1') return;
                onSelect(option);
                close();
                trigger.focus();
            });
            option.addEventListener('keydown', function (e) {
                var avail = availableOptions();
                var vIdx = avail.indexOf(option);
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (option.getAttribute('data-unavailable') !== '1') {
                        onSelect(option);
                        close();
                        trigger.focus();
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    (avail[vIdx + 1] || avail[0] || option).focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    (avail[vIdx - 1] || avail[avail.length - 1] || option).focus();
                } else if (e.key === 'Escape') {
                    close();
                    trigger.focus();
                } else if (e.key === 'Tab') {
                    close();
                }
            });
        });

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                close();
            }
        });

        return { options: options, close: close, open: open };
    }

    function setActiveServerPane(paneId) {
        if (!paneId) return;

        document.querySelectorAll('.nav-tabs a').forEach(function (tab) {
            var isActive = (tab.getAttribute('href') || '').replace('#', '') === paneId;
            tab.classList.toggle('active', isActive);
            if (isActive) {
                tab.setAttribute('aria-current', 'page');
            } else {
                tab.removeAttribute('aria-current');
            }
        });

        document.querySelectorAll('.tab-pane').forEach(function (pane) {
            pane.classList.toggle('active', pane.id === paneId);
        });

        state.activePaneId = paneId;
        state.server = paneId.replace('-list', '');
    }

    function applyServerSelection(option) {
        if (!option) return;
        serverDropdown.options.forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
        option.setAttribute('aria-selected', 'true');

        var labelSpan = option.querySelector('span');
        if (serverFilterValue) serverFilterValue.textContent = labelSpan ? labelSpan.textContent.trim() : option.textContent.trim();

        setActiveServerPane(option.getAttribute('data-pane-id') || ((option.getAttribute('data-server-filter') || 'euw') + '-list'));
        refresh();
    }

    function applyRankSelection(option) {
        if (!option) return;
        rankDropdown.options.forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
        option.setAttribute('aria-selected', 'true');
        if (rankFilterValue) rankFilterValue.textContent = option.textContent.trim();
        state.rank = option.getAttribute('data-rank-filter') || 'all';
        refresh();
    }

    function applySortSelection(option) {
        if (!option) return;
        sortDropdown.options.forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
        option.setAttribute('aria-selected', 'true');
        if (sortValueEl) sortValueEl.textContent = option.textContent.trim();
        state.sort = option.getAttribute('data-value') || 'default';
        refresh();
    }

    var serverDropdown = setupDropdown(serverFilterTrigger, serverFilterMenu, applyServerSelection);
    var rankDropdown   = setupDropdown(rankFilterTrigger, rankFilterMenu, applyRankSelection);
    var sortDropdown   = setupDropdown(sortTrigger, sortMenu, applySortSelection);

    // --- Figures out, for the currently active region tab + the other
    // active filters (search / ranked-ready — deliberately NOT the rank
    // filter itself, so the list always reflects what's actually reachable),
    // which ranks still have at least one matching account. Used to grey
    // out empty options in the Rank dropdown and to build the "Popular"
    // quick-chip row from only the ranks that are actually available. ---
    function renderAvailableRanks() {
        var pane = state.activePaneId && document.getElementById(state.activePaneId);
        var available = {};

        if (pane) {
            Array.prototype.slice.call(pane.querySelectorAll('.account')).forEach(function (card) {
                var matchesReady = !state.rankedReadyOnly || card.getAttribute('data-ranked-ready') === '1';
                var matchesSearch = !state.search || card.textContent.toLowerCase().indexOf(state.search) !== -1;
                if (matchesReady && matchesSearch) {
                    available[card.getAttribute('data-rank')] = true;
                }
            });
        }

        rankDropdown.options.forEach(function (option) {
            var key = option.getAttribute('data-rank-filter');
            if (key === 'all') return;
            if (available[key]) {
                option.removeAttribute('data-unavailable');
            } else {
                option.setAttribute('data-unavailable', '1');
            }
        });

        if (!popularRow) return;
        Array.prototype.slice.call(popularRow.querySelectorAll('.shop-popular__chip')).forEach(function (chip) {
            chip.remove();
        });

        var anyAvailable = false;
        rankDropdown.options.forEach(function (option) {
            var key = option.getAttribute('data-rank-filter');
            if (key === 'all' || option.getAttribute('data-unavailable') === '1') return;
            anyAvailable = true;

            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'shop-popular__chip' + (state.rank === key ? ' is-active' : '');
            chip.setAttribute('data-rank-filter', key);

            var img = option.querySelector('img');
            if (img) chip.appendChild(img.cloneNode(true));

            var labelSpan = option.querySelector('span');
            var label = document.createElement('span');
            label.textContent = labelSpan ? labelSpan.textContent : option.textContent.trim();
            chip.appendChild(label);

            chip.addEventListener('click', function () { applyRankSelection(option); });
            popularRow.appendChild(chip);
        });

        popularRow.hidden = !anyAvailable;
    }

    function applyFilters() {
        var panes = document.querySelectorAll('.tab-pane');
        var visibleCountInActivePane = 0;
        var activePaneFound = false;

        panes.forEach(function (pane) {
            var cards = Array.prototype.slice.call(pane.querySelectorAll('.account'));
            var visibleInPane = 0;

            cards.forEach(function (card) {
                var matchesRank = state.rank === 'all' || card.getAttribute('data-rank') === state.rank;
                var matchesReady = !state.rankedReadyOnly || card.getAttribute('data-ranked-ready') === '1';
                var matchesSearch = !state.search || card.textContent.toLowerCase().indexOf(state.search) !== -1;
                var visible = matchesRank && matchesReady && matchesSearch;
                card.style.display = visible ? '' : 'none';
                if (visible) visibleInPane++;
            });

            if (pane.id === state.activePaneId) {
                visibleCountInActivePane = visibleInPane;
                activePaneFound = true;
            }
        });

        if (emptyState) {
            emptyState.hidden = !activePaneFound || visibleCountInActivePane !== 0;
        }

        renderAvailableRanks();
    }

    function applySort() {
        var panes = document.querySelectorAll('.tab-pane');

        panes.forEach(function (pane) {
            var grid = pane.querySelector('.accounts-grid');
            if (!grid) return;
            var cards = Array.prototype.slice.call(grid.querySelectorAll('.account'));

            if (state.sort === 'default') {
                return; // keep original markup order
            }

            cards.sort(function (a, b) {
                var pa = parseFloat(a.getAttribute('data-price')) || 0;
                var pb = parseFloat(b.getAttribute('data-price')) || 0;
                var ra = parseInt(a.getAttribute('data-rank-id'), 10) || 0;
                var rb = parseInt(b.getAttribute('data-rank-id'), 10) || 0;

                switch (state.sort) {
                    case 'price-asc':  return pa - pb;
                    case 'price-desc': return pb - pa;
                    case 'rank-asc':   return ra - rb;
                    case 'rank-desc':  return rb - ra;
                    default: return 0;
                }
            });

            cards.forEach(function (card) { grid.appendChild(card); });
        });
    }

    function refresh() {
        applyFilters();
        applySort();
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            state.search = searchInput.value.trim().toLowerCase();
            refresh();
        });
    }

    if (rankedReadyPill) {
        rankedReadyPill.addEventListener('click', function () {
            state.rankedReadyOnly = !state.rankedReadyOnly;
            rankedReadyPill.setAttribute('aria-pressed', state.rankedReadyOnly ? 'true' : 'false');
            refresh();
        });
    }

    function clearAllFilters() {
        state.rank = 'all';
        state.rankedReadyOnly = false;
        state.sort = 'default';
        state.search = '';

        if (serverDropdown.options[0]) {
            serverDropdown.options.forEach(function (o, i) { o.setAttribute('aria-selected', i === 0 ? 'true' : 'false'); });
            if (serverFilterValue) {
                var serverLabel = serverDropdown.options[0].querySelector('span');
                serverFilterValue.textContent = serverLabel ? serverLabel.textContent.trim() : serverDropdown.options[0].textContent.trim();
            }
            setActiveServerPane(serverDropdown.options[0].getAttribute('data-pane-id') || 'euw-list');
        }

        rankDropdown.options.forEach(function (o, i) { o.setAttribute('aria-selected', i === 0 ? 'true' : 'false'); });
        if (rankFilterValue && rankDropdown.options[0]) rankFilterValue.textContent = rankDropdown.options[0].textContent.trim();

        sortDropdown.options.forEach(function (o, i) { o.setAttribute('aria-selected', i === 0 ? 'true' : 'false'); });
        if (sortValueEl && sortDropdown.options[0]) sortValueEl.textContent = sortDropdown.options[0].textContent.trim();

        if (rankedReadyPill) rankedReadyPill.setAttribute('aria-pressed', 'false');
        if (searchInput) searchInput.value = '';

        refresh();
    }

    if (clearAllBtn) clearAllBtn.addEventListener('click', clearAllFilters);
    if (emptyResetBtn) emptyResetBtn.addEventListener('click', clearAllFilters);

    // --- Mobile filter sheet ---
    function setBottomNavHidden(hide) {
        // Belt-and-suspenders: also toggle an inline style directly, in case
        // some other rule on the site out-specifies our stylesheet rule for
        // .lb-mobile-bottomnav (same class of bug as the empty-state fix).
        // Only the bottom nav actually overlaps the sheet — the top bars are
        // left alone so the page stays visible (dimmed) behind the sheet.
        document.querySelectorAll('.lb-mobile-bottomnav, [class*="lb-mobile-bottomnav--"]').forEach(function (el) {
            el.style.display = hide ? 'none' : '';
        });
    }

    function updateFilterPanelTop() {
        // Kept as a no-op for older calls. The mobile filter panel is now
        // a true fullscreen sheet, so it no longer needs a dynamic top offset.
        document.documentElement.style.removeProperty('--lol-filter-panel-top');
    }

    function openFilterSheet() {
        updateFilterPanelTop();
        document.body.classList.add('lol-filters-sheet-open');
        if (mobileFilterTrigger) mobileFilterTrigger.setAttribute('aria-expanded', 'true');
        setBottomNavHidden(true);
    }
    function closeFilterSheet() {
        document.body.classList.remove('lol-filters-sheet-open');
        if (mobileFilterTrigger) mobileFilterTrigger.setAttribute('aria-expanded', 'false');
        rankDropdown.close();
        sortDropdown.close();
        setBottomNavHidden(false);
    }

    if (mobileFilterTrigger) {
        mobileFilterTrigger.addEventListener('click', function () {
            var isOpen = document.body.classList.contains('lol-filters-sheet-open');
            if (isOpen) { closeFilterSheet(); } else { openFilterSheet(); }
        });
    }
    if (mobileFilterApply) mobileFilterApply.addEventListener('click', closeFilterSheet);
    if (mobileFilterClose) mobileFilterClose.addEventListener('click', closeFilterSheet);
    if (filterSheetClear) {
        // Clearing inside the panel resets filters but keeps the panel open,
        // so the person can immediately see/adjust the now-empty state.
        filterSheetClear.addEventListener('click', clearAllFilters);
    }

    window.addEventListener('resize', function () {
        if (document.body.classList.contains('lol-filters-sheet-open')) updateFilterPanelTop();
    });

    // Tapping anything outside the bar/panel (i.e. the page behind it) closes it.
    document.addEventListener('click', function (e) {
        if (!document.body.classList.contains('lol-filters-sheet-open')) return;
        if (e.target.closest('.shop-filterbar')) return;
        closeFilterSheet();
    }, true);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            serverDropdown.close();
            rankDropdown.close();
            sortDropdown.close();
            closeFilterSheet();
        }
    });

    // Update our own "which region tab is active" tracking the moment a tab
    // is clicked, then re-run the filters so the empty state + available
    // ranks reflect the newly shown pane right away.
    document.querySelectorAll('.nav-tabs a').forEach(function (tabLink) {
        tabLink.addEventListener('click', function () {
            var href = tabLink.getAttribute('href') || '';
            var paneId = href.replace('#', '');
            setActiveServerPane(paneId);

            serverDropdown.options.forEach(function (option) {
                var isSelected = option.getAttribute('data-pane-id') === paneId;
                option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                if (isSelected && serverFilterValue) {
                    var serverLabel = option.querySelector('span');
                    serverFilterValue.textContent = serverLabel ? serverLabel.textContent.trim() : option.textContent.trim();
                }
            });

            setTimeout(applyFilters, 30);
        });
    });

    refresh();
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
