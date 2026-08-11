<?php
/**
 * Component: website/components/accounts/account-cards
 * 
 * SELLER SALES: Uses unified seller_sales_unified.php system
 * - seller_total_sales should be in SQL query (see ajax.php)
 * - Fallback: get_seller_total_sales() if not in query
 */


if (!function_exists('lb_seller_profile_slug_from_value')) {
    function lb_seller_profile_slug_from_value($slug = '', $username = ''): string
    {
        $value = trim((string)$slug);
        if ($value === '') {
            $value = trim((string)$username);
        }
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN_-]+/u', '', (string)$value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : trim((string)$username);
    }
}

if (!function_exists('lol_rank_hides_division')) {
    function lol_rank_hides_division($rank): bool
    {
        return in_array((int)$rank, [0, 8, 9, 10], true);
    }
}

if (!function_exists('lol_rank_display_text')) {
    function lol_rank_display_text($rank, $division, $lp): string
    {
        $label = util_get_lol_rank($rank);
        $lp = is_null($lp) ? null : (int)$lp;

        if ($lp !== null && $lp !== 0) {
            return $label . ' ' . $lp . 'LP';
        }

        if (lol_rank_hides_division($rank)) {
            return $label;
        }

        return $label . ' ' . util_format_lol_division($division);
    }
}

if (!function_exists('seller_card_rank_meta')) {
    function seller_card_rank_meta($rankName = '', $storedIcon = ''): array
    {
        $rankName = trim((string)$rankName);
        $storedIcon = trim((string)$storedIcon);
        $title = $rankName !== '' ? $rankName : 'Verified Seller';

        if ($storedIcon !== '') {
            $class = $storedIcon;
            if (strpos($class, 'fa-') !== false && strpos($class, 'fa-solid') === false && strpos($class, 'fa-regular') === false && strpos($class, 'fa-light') === false && strpos($class, 'fa-duotone') === false && strpos($class, 'fa-brands') === false) {
                $class = 'fa-solid ' . $class;
            }
            return [
                'class' => $class,
                'color' => '#22c55e',
                'title' => $title,
            ];
        }

        switch (strtolower($rankName)) {
            case 'mythic seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#fbbf24', 'title' => $title];
            case 'pro seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#8b5cf6', 'title' => $title];
            case 'expert seller':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#22c55e', 'title' => $title];
            case 'beginner':
                return ['class' => 'fa-solid fa-badge-check', 'color' => '#94a3b8', 'title' => $title];
            default:
                return ['class' => 'fa-solid fa-circle-check', 'color' => '#94a3b8', 'title' => $title];
        }
    }
}

if (!function_exists('account_card_rank_img')) {
    function account_card_rank_img(string $game, int $rank): string
    {
        $game = strtolower(trim($game));
        $rank = max(0, (int)$rank);

        if ($game === 'val') {
            return ASSET_URL . "/core/main/img/val/ranks/mini/{$rank}.png";
        }

        if (function_exists('util_get_rank_img')) {
            return util_get_rank_img($game, 'mini', $rank);
        }

        if (function_exists('util_rank_img')) {
            return util_rank_img($game === 'tft' ? 'lol' : $game, 'mini', $rank);
        }

        return ASSET_URL . "/core/main/img/lol/ranks/mini/{$rank}.png";
    }
}


if (!function_exists('account_card_seller_total_sales_fallback')) {
    function account_card_seller_total_sales_fallback(int $sellerId): int
    {
        global $db;

        if ($sellerId <= 0 || empty($db)) {
            return 0;
        }

        $adminSalesMap = [
            28 => 51,
            1  => 2,
        ];

        try {
            $baseSales = (int)($db->cell(
                "SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1 AND client_id IS NOT NULL",
                $sellerId
            ) ?: 0);

            $itemSales = (int)($db->cell(
                "SELECT COALESCE(SUM(sold_count), 0) FROM selling_items WHERE seller_id = ?",
                $sellerId
            ) ?: 0);

            $adminSales = 0;
            if (isset($adminSalesMap[$sellerId])) {
                $adminSales = (int)($db->cell(
                    "SELECT COUNT(*) FROM accounts WHERE admin_id = ? AND status = 1 AND client_id IS NOT NULL",
                    $adminSalesMap[$sellerId]
                ) ?: 0);
            }

            return $baseSales + $itemSales + $adminSales;
        } catch (Throwable $e) {
            return 0;
        }
    }
}

include_once __DIR__ . '/../seller/seller-footer.php';
?>

<style>
.account-card {
    overflow: visible;
}
.account-card__recommended-icon {
    right: 56px !important;
    z-index: 8;
    color: #ffd54a !important;
}
.account-card__recommended-icon:hover {
    color: #ffe27a !important;
}
.account-card__recommended-icon .recommended-tooltip {
    position: absolute;
    right: 0;
    bottom: calc(100% + 10px);
    transform: translateY(6px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    white-space: nowrap;
    padding: 8px 12px;
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(26,31,59,0.98) 0%, rgba(11,14,30,0.98) 100%);
    border: 1px solid rgba(255,255,255,0.16);
    box-shadow: 0 18px 34px rgba(0,0,0,0.34);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
    z-index: 20;
}
.account-card__recommended-icon .recommended-tooltip::after {
    content: "";
    position: absolute;
    right: 12px;
    top: 100%;
    border-width: 6px;
    border-style: solid;
    border-color: rgba(15,18,38,0.98) transparent transparent transparent;
}
.account-card__recommended-icon:hover .recommended-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.seller-online-dot{width:9px;height:9px;min-width:9px;min-height:9px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95);animation:seller-online-pulse 1.45s ease-out infinite;display:inline-block;transform:translateY(1px);flex:0 0 9px;}
@keyframes seller-online-pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95)}70%{box-shadow:0 0 0 7px rgba(34,197,94,0),0 0 18px rgba(34,197,94,.9)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0),0 0 14px rgba(34,197,94,.95)}}
.seller-info {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    min-height: 78px;
    margin-top: 14px;
    padding: 16px 18px;
    border-top: 1px solid rgba(255,255,255,0.09);
    border-radius: 0 0 18px 18px;
    background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.045) 100%);
    cursor: default;
    overflow: visible;
}
.seller-info__left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
    flex: 1 1 auto;
    overflow: visible;
}
.seller-info__avatar {
    width: 46px;
    height: 46px;
    min-width: 46px;
    min-height: 46px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    display: block;
    flex: 0 0 46px;
    aspect-ratio: 1 / 1;
    border: 2px solid rgba(150, 109, 255, 0.35);
    box-shadow: 0 0 0 4px rgba(122, 92, 255, 0.08);
}
.seller-info__name {
    position: relative;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    gap: 9px;
    cursor: help;
    overflow: visible !important;
    z-index: 30;
    isolation: isolate;
}
.seller-info__name-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1vw;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.015em;
    text-transform: uppercase;
    color: #ffffff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.28);
}
@media (max-width: 767px) {
.seller-info__name-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 4vw;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.015em;
    text-transform: uppercase;
    color: #ffffff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.28);
}
}
.seller-info__verified {
    position: relative;
    z-index: 2;
    flex: 0 0 auto;
    font-size: 18px;
    line-height: 1;
    transform: translateY(1px);
    filter: drop-shadow(0 0 10px currentColor);
}
.seller-rank-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    gap: 9px;
    overflow: visible !important;
}
.seller-info__right {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex: 0 0 auto;
    white-space: nowrap;
}
.seller-info__sold,
.seller-info__rating {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 10px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.06);
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
}
.seller-info__rating i {
    font-size: 12px;
}
.seller-rank-tooltip {
    position: absolute;
    left: 0;
    bottom: calc(100% + 10px);
    transform: translateY(8px) scale(.96);
    transform-origin: left bottom;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    padding: 9px 13px;
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(20,24,48,0.98) 0%, rgba(10,13,30,0.98) 100%);
    border: 1px solid rgba(255,255,255,0.14);
    box-shadow: 0 14px 30px rgba(0,0,0,0.32);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
    z-index: 999;
}
.seller-rank-tooltip::after {
    content: "";
    position: absolute;
    left: 18px;
    top: 100%;
    border-width: 6px;
    border-style: solid;
    border-color: rgba(10,13,30,0.98) transparent transparent transparent;
}
.seller-rank-trigger:hover .seller-rank-tooltip,
.seller-rank-trigger:focus-within .seller-rank-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}
.seller-rank-tooltip__dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    flex: 0 0 8px;
    box-shadow: 0 0 12px currentColor;
}
</style>





<?php foreach ($accounts as $account): ?>
    <?php
        // price is already converted to session currency (cents) by controller/ajax
        $price_cents = (int)($account['price'] ?? 0);
        $price_float = round($price_cents / 100, 2);
        $game        = strtolower(trim((string)($account['game'] ?? 'lol')));

        // Universal rank fields (prefer new columns, fall back to legacy)
        $universalRank  = (int)($account['rank'] ?? $account['current_rank'] ?? 0);
        $rankScore      = $universalRank * 100000;

        $gameData = json_decode((string)($account['game_data'] ?? '{}'), true);
        if (!is_array($gameData)) {
            $gameData = [];
        }

        $div = 0;
        $lp = 0;

        // LoL-spezifisch: division/lp für Sortierung & Anzeige aus game_data oder legacy-Spalten
        if ($game === 'lol') {
            $div = (int)($account['current_division'] ?? $gameData['division'] ?? 0);
            $lp  = (int)($account['current_lp']       ?? $gameData['lp']       ?? 0);
            $rankScore = ($universalRank * 100000) + ($div * 1000) + $lp;
        }

        // Use manual list count when available, fall back to count-only field
        $lolChampionsList  = array_filter(explode('|', (string)($account['champions'] ?? '')));
        $lolSkinsList      = array_filter(explode('|', (string)($account['skins'] ?? '')));
        $lolChampionsCount = count($lolChampionsList) > 0
            ? count($lolChampionsList)
            : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
               ? (int)$account['champion_count'] : 0);
        $lolSkinsCount = count($lolSkinsList) > 0
            ? count($lolSkinsList)
            : (isset($account['skin_count']) && $account['skin_count'] !== null && $account['skin_count'] !== ''
               ? (int)$account['skin_count'] : 0);
        $valAgents         = $gameData['agents'] ?? [];
        if (is_string($valAgents) && $valAgents !== '') {
            $valAgents = array_filter(array_map('trim', explode('|', $valAgents)));
        }
        if (!is_array($valAgents)) {
            $valAgents = [];
        }
        $valAgentsManualCount = count(array_filter($valAgents, static fn($v) => $v !== null && $v !== ''));
        $valAgentsCount       = $valAgentsManualCount > 0
            ? $valAgentsManualCount
            : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
               ? (int)$account['val_agent_count'] : 0);
        $valWeaponSkinsCount = (int)($gameData['val_weapon_skins'] ?? 0);
        $valPoints           = (int)($gameData['val_points'] ?? 0);
        $valRadianite        = (int)($gameData['val_radianite'] ?? 0);
        $valWinrate          = (int)($gameData['val_winrate'] ?? 0);
        $valRankedReady      = !empty($gameData['val_ranked_ready']);

        $images = json_decode($account['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        $firstImage = !empty($images) ? $images[0] : '';
        $remainingCount = max(0, count($images) - 1);

        // ═══════════════════════════════════════════════════════════════════
        // SELLER SALES - UNIFIED SYSTEM
        // ═══════════════════════════════════════════════════════════════════
        // Prefer seller_total_sales from SQL query (see ajax.php)
        // Fallback to function call if not present
        // ═══════════════════════════════════════════════════════════════════
        
        $sellerName     = $account['seller_username'] ?? null;
        $sellerOnline   = !empty($account['seller_is_online']);
        $sellerIcon     = $account['seller_icon'] ?? null;
        $sellerSlug     = lb_seller_profile_slug_from_value($account['seller_slug'] ?? '', $sellerName ?? '');
        $sellerLink     = '/sellers/' . rawurlencode($sellerSlug);
        
        // Get total sales from query, then verify with the unified fallback.
        // This keeps old account listing queries from showing outdated values such as 5 Sold
        // when special admin account sales should be counted for seller profiles.
        $sellerSold = (int)($account['seller_total_sales'] ?? 0);
        if (!empty($account['seller_id'])) {
            $sellerIdForSales = (int)$account['seller_id'];
            $fallbackSellerSold = 0;

            if (function_exists('account_card_seller_total_sales_fallback')) {
                $fallbackSellerSold = account_card_seller_total_sales_fallback($sellerIdForSales);
            } elseif (function_exists('get_seller_total_sales')) {
                $fallbackSellerSold = (int)get_seller_total_sales($sellerIdForSales);
            }

            if ($fallbackSellerSold > $sellerSold) {
                $sellerSold = $fallbackSellerSold;
            }
        }
        
        $sellerRating   = $account['seller_rating'] ?? null;
        $sellerRank     = trim((string)($account['seller_rank'] ?? ''));
        $sellerRankIconStored = trim((string)($account['seller_rank_icon'] ?? ''));
        $sellerRecommended = $sellerSold >= 10;
        $sellerVerified = !empty($account['seller_is_active']);

        $sellerRankMeta = seller_card_rank_meta($sellerRank, $sellerRankIconStored);
        $sellerCheckColor = $sellerRankMeta['color'];
        $sellerRankTooltip = $sellerRankMeta['title'];
        $sellerRankIcon = $sellerRankMeta['class'];
    ?>

    <div class="account-card" data-price="<?= $price_float ?>" data-rank="<?= $rankScore ?>">
        <a href="/<?= $game === 'val' ? 'val' : 'lol' ?>/account/<?= $account['slug'] ?>" class="cover-link">
            <h3 class="title">
                <img src="<?= account_card_rank_img($game, $universalRank) ?>" class="rank-icon">
                <?php if ($game === 'lol'): ?>
                    <?= strtoupper($account['server'] ?? '') . ' - ' . lol_rank_display_text($account['current_rank'] ?? $universalRank, $div, $lp) ?>
                <?php else: ?>
                    <?= strtoupper($account['server'] ?? '') . ' - ' . htmlspecialchars($account['rank_label'] ?? util_get_rank_label($game, $universalRank)) ?>
                <?php endif; ?>
            </h3>

            <p class="excerpt">
                <?= implode(' ', array_slice(explode(' ', $account['title']), 0, 40)) ?>
            </p>

            <div class="image-box">
                <img src="<?= $firstImage ?: ASSET_URL . '/core/main/img/banners/account.jpg' ?>">

                <?php if ($remainingCount > 0): ?>
                    <span class="badge ">
                        <i class="fas fa-images"></i> +<?= $remainingCount ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="highlights">
                <?php if ($game === 'val'): ?>
                    <span class="badge bg-grey">
                        <i class="fas fa-user-ninja"></i>
                        <?= $valAgentsCount ?> Agents
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gun"></i>
                        <?= $valWeaponSkinsCount ?> Skins
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-arrow-turn-up"></i> Level <?= (int)($account['level'] ?? 0) ?>
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-coins"></i> <?= $valPoints ?> VP
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gem"></i> <?= $valRadianite ?> Rad
                    </span>
                    <?php if ($valWinrate > 0): ?>
                        <span class="badge bg-grey">
                            <i class="fas fa-chart-line"></i> <?= $valWinrate ?>% WR
                        </span>
                    <?php endif; ?>
                    <?php if ($valRankedReady): ?>
                        <span class="badge bg-grey">
                            <i class="fas fa-circle-check"></i> Ranked Ready
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge bg-grey">
                        <i class="fas fa-helmet-battle"></i>
                        <?= $lolChampionsCount ?> Champions
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-masks-theater"></i>
                        <?= $lolSkinsCount ?> Skins
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-arrow-turn-up"></i> Level <?= (int)($account['level'] ?? 0) ?>
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-gem"></i> <?= (int)($account['blue_essence'] ?? 0) ?> BE
                    </span>
                    <span class="badge bg-grey">
                        <i class="fas fa-hand-back-fist"></i> <?= (int)($account['riot_points'] ?? 0) ?> RP
                    </span>
                <?php endif; ?>
            </div>

            <div class="totals">
                <span class="fw-bold price-eur">
                    <?= util_format_currency_display($_SESSION['currency']) . util_format_price_display($account['price']) ?>
                    <?= $_SESSION['currency'] ?>
                </span>
                <span class="btn primary"><?= t('Buy Now') ?> <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <?php if ($sellerName): ?>
            <?php
                echo lb_render_seller_footer([
                    'id' => $account['seller_id'] ?? null,
                    'username' => $sellerName,
                    'slug' => $sellerSlug,
                    'icon' => $sellerIcon,
                    'rank' => $sellerRank,
                    'rank_icon' => $sellerRankIconStored,
                    'is_active' => $sellerVerified ? 1 : 0,
                    'is_online' => $sellerOnline ? 1 : 0,
                    'total_sold' => $sellerSold,
                    'seller_total_sales' => $sellerSold,
                    'seller_rating' => $sellerRating,
                ], ['variant' => 'account-card']);
            ?>
        <?php endif; ?>

        <?php if ($sellerRecommended): ?>
            <i class="fas fa-star delivery-type account-card__recommended-icon">
                <span class="recommended-tooltip">Recommended Seller</span>
            </i>
        <?php endif; ?>

        <?php if (($account['delivery_type'] ?? '') === 'instant'): ?>
            <i class="fas fa-bolt delivery-type" data-tooltip="Instant Delivery"></i>
        <?php else: ?>
            <i class="fas fa-truck delivery-type" data-tooltip="Manual Delivery"></i>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<style>
/* Final account card size fix: wider cards, but still controlled. */
.ranked-accounts-page #accountsGrid.accounts-grid{
  display:grid!important;
  grid-template-columns:repeat(auto-fill,minmax(340px,400px))!important;
  justify-content:start!important;
  align-items:stretch!important;
  gap:26px!important;
  min-width:0!important;
  max-width:100%!important;
  box-sizing:border-box!important;
}

.ranked-accounts-page #accountsGrid .account-card{
  width:100%!important;
  max-width:400px!important;
  min-width:0!important;
  box-sizing:border-box!important;
  overflow:hidden!important;
}

.ranked-accounts-page #accountsGrid .account-card .image-box{
  max-height:235px!important;
  overflow:hidden!important;
}

.ranked-accounts-page #accountsGrid .account-card .image-box img{
  width:100%!important;
  max-height:235px!important;
  object-fit:cover!important;
  display:block!important;
}

.ranked-accounts-page #accountsGrid .account-card .totals{
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  gap:14px!important;
}

.ranked-accounts-page #accountsGrid .account-card .price,
.ranked-accounts-page #accountsGrid .account-card [class*="price"]{
  white-space:nowrap!important;
}

.ranked-accounts-page #accountsGrid .account-card .totals .btn.primary{
  flex:0 0 auto!important;
  white-space:nowrap!important;
}

@media (max-width:1199px){
  .ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr))!important;
  }
  .ranked-accounts-page #accountsGrid .account-card{
    max-width:none!important;
  }
}

@media (max-width:640px){
  .ranked-accounts-page #accountsGrid.accounts-grid{
    grid-template-columns:1fr!important;
    gap:18px!important;
  }
}
</style>

<style>
/* Seller footer final fix: keep seller profile, sold badge and trusted badge readable on account cards. */
.ranked-accounts-page #accountsGrid .account-card .seller-info{
  min-height:64px!important;
  margin-top:14px!important;
  padding:12px 14px!important;
  gap:10px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  overflow:hidden!important;
  box-sizing:border-box!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__left{
  flex:1 1 auto!important;
  min-width:0!important;
  max-width:calc(100% - 158px)!important;
  display:flex!important;
  align-items:center!important;
  gap:9px!important;
  overflow:hidden!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{
  width:38px!important;
  height:38px!important;
  min-width:38px!important;
  min-height:38px!important;
  flex:0 0 38px!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__name,
.ranked-accounts-page #accountsGrid .account-card .seller-rank-trigger{
  flex:1 1 auto!important;
  min-width:0!important;
  max-width:100%!important;
  display:inline-flex!important;
  align-items:center!important;
  gap:5px!important;
  overflow:hidden!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{
  flex:1 1 auto!important;
  min-width:0!important;
  max-width:100%!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
  white-space:nowrap!important;
  font-size:14px!important;
  line-height:1.05!important;
  font-weight:850!important;
  letter-spacing:0!important;
  text-transform:none!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-online-dot,
.ranked-accounts-page #accountsGrid .account-card .seller-info__verified{
  flex:0 0 auto!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__verified{
  font-size:14px!important;
  transform:none!important;
  filter:none!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__right{
  flex:0 0 auto!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:flex-end!important;
  gap:6px!important;
  min-width:148px!important;
  max-width:148px!important;
  white-space:nowrap!important;
  overflow:hidden!important;
  position:relative!important;
  z-index:4!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
  height:26px!important;
  min-height:26px!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:5px!important;
  padding:0 8px!important;
  border-radius:7px!important;
  font-size:11px!important;
  font-weight:850!important;
  line-height:1!important;
  flex:0 0 auto!important;
  white-space:nowrap!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
  width:auto!important;
  min-width:auto!important;
  color:#22c55e!important;
  background:rgba(34,197,94,.10)!important;
  border-color:rgba(34,197,94,.32)!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__rating i{
  font-size:11px!important;
  margin:0!important;
}

@media (max-width:420px){
  .ranked-accounts-page #accountsGrid .account-card .seller-info{
    padding:11px 12px!important;
    gap:8px!important;
  }
  .ranked-accounts-page #accountsGrid .account-card .seller-info__left{
    max-width:calc(100% - 128px)!important;
  }
  .ranked-accounts-page #accountsGrid .account-card .seller-info__right{
    min-width:120px!important;
    max-width:120px!important;
    gap:4px!important;
  }
  .ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
  .ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
    padding:0 6px!important;
    font-size:10px!important;
  }
}
</style>


<style>
/* Seller footer badge/trusted final adjustment */
.ranked-accounts-page #accountsGrid .account-card,
.ranked-accounts-page #accountsGrid .account-card .seller-info{
  overflow:visible!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info{
  position:relative!important;
  z-index:5!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__left{
  max-width:calc(100% - 118px)!important;
  overflow:visible!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__name,
.ranked-accounts-page #accountsGrid .account-card .seller-rank-trigger{
  overflow:visible!important;
  position:relative!important;
  gap:6px!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{
  flex:0 1 auto!important;
  width:auto!important;
  max-width:132px!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
  white-space:nowrap!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-online-dot{
  width:8px!important;
  height:8px!important;
  min-width:8px!important;
  min-height:8px!important;
  flex:0 0 8px!important;
  margin-left:1px!important;
  transform:none!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__verified{
  cursor:help!important;
  margin-left:1px!important;
  font-size:14px!important;
  line-height:1!important;
  position:relative!important;
  z-index:30!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-rank-tooltip{
  left:auto!important;
  right:0!important;
  bottom:calc(100% + 10px)!important;
  z-index:99999!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-rank-tooltip::after{
  left:auto!important;
  right:12px!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__verified:hover + .seller-rank-tooltip,
.ranked-accounts-page #accountsGrid .account-card .seller-info__verified:focus + .seller-rank-tooltip{
  opacity:1!important;
  visibility:visible!important;
  transform:translateY(0) scale(1)!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__right{
  min-width:108px!important;
  max-width:108px!important;
  gap:6px!important;
  overflow:visible!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
  min-width:70px!important;
  max-width:70px!important;
  padding:0 7px!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
  width:28px!important;
  min-width:28px!important;
  max-width:28px!important;
  padding:0!important;
  gap:0!important;
  font-size:0!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__rating i{
  font-size:12px!important;
}

@media (min-width:1300px){
  .ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{max-width:150px!important;}
}

@media (max-width:420px){
  .ranked-accounts-page #accountsGrid .account-card .seller-info__left{max-width:calc(100% - 104px)!important;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__right{min-width:98px!important;max-width:98px!important;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__sold{min-width:64px!important;max-width:64px!important;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__rating{width:26px!important;min-width:26px!important;max-width:26px!important;}
  .ranked-accounts-page #accountsGrid .account-card .seller-info__name-text{max-width:105px!important;}
}
</style>


<style>
/* Seller footer final polish: badge and online dot stay directly next to seller name, trusted shows icon only. */
.account-card .seller-rank-trigger,
.account-card .seller-info__name{
  display:inline-flex!important;
  align-items:center!important;
  gap:7px!important;
  min-width:0!important;
}
.account-card .seller-info__name-text{
  min-width:0!important;
  max-width:150px!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
  white-space:nowrap!important;
}
.account-card .seller-online-dot{
  flex:0 0 8px!important;
  width:8px!important;
  height:8px!important;
  min-width:8px!important;
  min-height:8px!important;
  margin-left:0!important;
}
.account-card .seller-info__verified{
  flex:0 0 auto!important;
  margin-left:0!important;
  cursor:help!important;
}
.account-card .seller-info__rating{
  width:30px!important;
  min-width:30px!important;
  padding:0!important;
  justify-content:center!important;
  font-size:0!important;
}
.account-card .seller-info__rating i{
  font-size:13px!important;
  margin:0!important;
}
</style>


<style>
/* Seller footer badge spacing fix */
.ranked-accounts-page #accountsGrid .account-card .seller-info__right{
  min-width:124px!important;
  max-width:124px!important;
  gap:12px!important;
  overflow:visible!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
  min-width:74px!important;
  max-width:74px!important;
  margin-right:0!important;
}

.ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
  width:30px!important;
  min-width:30px!important;
  max-width:30px!important;
  margin-left:0!important;
}

@media (max-width:420px){
  .ranked-accounts-page #accountsGrid .account-card .seller-info__right{
    min-width:108px!important;
    max-width:108px!important;
    gap:8px!important;
  }
  .ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
    min-width:68px!important;
    max-width:68px!important;
  }
  .ranked-accounts-page #accountsGrid .account-card .seller-info__rating{
    width:28px!important;
    min-width:28px!important;
    max-width:28px!important;
  }
}
</style>
