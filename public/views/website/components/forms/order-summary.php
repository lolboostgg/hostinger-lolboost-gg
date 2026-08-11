<?php
$summaryGame = ($data['game'] ?? 'lol');
$isClassicSummary = function_exists('util_is_lol_classic') && util_is_lol_classic($summaryGame);
if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        $tier = (int)$tier;
        $division = (int)$division;
        $tierNames = [0 => 'unranked', 1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 7 => 'challenger'];
        if ($tier === 0 || $tier === 7) {
            return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'unranked') . '.png';
        }
        $divisionNames = [5 => 'v', 4 => 'iv', 3 => 'iii', 2 => 'ii', 1 => 'i'];
        return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'bronze') . '-' . ($divisionNames[$division] ?? 'v') . '.png';
    }
}
$summaryType = $data['type'] ?? '';
$summaryDefaultTier = $isClassicSummary ? ($summaryType === 'placement' ? 0 : 1) : 3;
$summaryDesiredTier = $isClassicSummary ? 2 : 4;
$summaryDefaultDivision = 4;
$summaryDesiredDivision = $isClassicSummary ? 4 : 1;
$summaryDefaultRankIcon = $isClassicSummary ? ($summaryDefaultTier > 0 ? ($summaryDefaultTier . '-' . $summaryDefaultDivision) : 0) : $summaryDefaultTier;
$summaryDesiredRankIcon = $isClassicSummary ? ($summaryDesiredTier . '-' . $summaryDesiredDivision) : $summaryDesiredTier;
$summaryDefaultRankLabel = $isClassicSummary ? ($summaryType === 'placement' ? 'Unranked' : 'Salt IV') : 'Silver I';
$summaryDesiredRankLabel = $isClassicSummary ? 'Wood IV' : 'Gold IV';
?>
<style>
/* lol-classic-summary-rank-size-fix */
.boost-form.lol-classic-page .summary-wrapper .rank-box .current-summary-rank-img,
.boost-form.lol-classic-page .summary-wrapper .rank-box .desired-summary-rank-img{width:74px!important;height:74px!important;object-fit:contain!important;max-width:none!important;}
.trust-badges-summary {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin-top: 14px;
}

.lb-shield-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: rgba(255, 255, 255, .78);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.2;
    cursor: help;
    outline: none;
}

.lb-shield-trigger i {
    color: #00e6a8;
    font-size: 15px;
}

.lb-shield-trigger__chev {
    color: rgba(255, 255, 255, .42);
    font-size: 11px;
    transition: transform .16s ease;
}

.lb-shield-trigger:hover,
.lb-shield-trigger:focus-visible {
    color: rgba(255, 255, 255, .96);
}

.lb-shield-trigger:hover .lb-shield-trigger__chev,
.lb-shield-trigger:focus-visible .lb-shield-trigger__chev {
    transform: translateY(1px);
}

.lb-shield-tooltip {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 12px);
    width: min(340px, calc(100vw - 34px));
    transform: translateX(-50%) translateY(6px);
    padding: 18px;
    border-radius: 18px;
    background: #10111b;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 18px 50px rgba(0, 0, 0, .38);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
    z-index: 50;
    text-align: left;
}

.lb-shield-tooltip:after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -7px;
    width: 14px;
    height: 14px;
    transform: translateX(-50%) rotate(45deg);
    background: #10111b;
    border-right: 1px solid rgba(255, 255, 255, .08);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.lb-shield-trigger:hover .lb-shield-tooltip,
.lb-shield-trigger:focus-visible .lb-shield-tooltip,
.lb-shield-trigger:focus-within .lb-shield-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.lb-shield-tooltip__brand {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #00e6a8;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .16em;
    text-transform: uppercase;
}

.lb-shield-tooltip__title {
    color: #fff;
    font-size: 14px;
    font-weight: 900;
    line-height: 1.35;
    margin-bottom: 14px;
}

.lb-shield-tooltip__item {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.lb-shield-tooltip__itemIcon {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 230, 168, .12);
    color: #00e6a8;
    flex: 0 0 28px;
    font-size: 12px;
}

.lb-shield-tooltip__itemTitle {
    display: block;
    color: rgba(255, 255, 255, .96);
    font-size: 13px;
    font-weight: 900;
    line-height: 1.25;
}

.lb-shield-tooltip__itemText {
    display: block;
    margin-top: 2px;
    color: rgba(255, 255, 255, .54);
    font-size: 11px;
    font-weight: 600;
    line-height: 1.35;
}

.trustpilot-banner--chip {
    display: flex;
    justify-content: center;
    text-decoration: none;
    width: 100%;
}

.trustpilot-banner--chip:hover,
.trustpilot-banner--chip:active,
.trustpilot-banner--chip:visited {
    text-decoration: none;
    color: inherit;
}

.tpBadge--summary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px 15px;
    min-height: 42px;
    width: auto;
    max-width: 100%;
    border-radius: 999px;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
    transition: transform .16s ease, border-color .16s ease, background .16s ease;
}

.trustpilot-banner--chip:hover .tpBadge--summary {
    transform: translateY(-1px);
    border-color: rgba(0, 182, 122, .30);
    background: rgba(0, 182, 122, .08);
}

.tpBadge__excellent {
    font-weight: 900;
    color: rgba(255, 255, 255, .96);
    font-size: 13px;
    line-height: 1;
    white-space: nowrap;
}

.tpBadge__stars {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
}

.tpBadge__stars i {
    font-size: 11px;
    color: #00b67a;
}

.tpBadge__reviews {
    color: rgba(255, 255, 255, .72);
    font-weight: 800;
    font-size: 12px;
    line-height: 1;
    white-space: nowrap;
}

.tpBadge__tpIcon {
    width: 24px;
    height: 24px;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #00b67a;
    color: #fff;
    font-size: 13px;
    font-weight: 900;
    line-height: 1;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .trust-badges-summary {
        margin-top: 16px;
        gap: 11px;
    }

    .lb-shield-trigger {
        font-size: 12px;
    }

    .tpBadge--summary {
        min-height: 42px;
        padding: 10px 13px;
        gap: 8px;
    }

    .tpBadge__excellent {
        font-size: 12px;
    }

    .tpBadge__reviews {
        font-size: 11px;
    }

    .tpBadge__stars i {
        font-size: 10px;
    }

    .tpBadge__tpIcon {
        width: 24px;
        height: 24px;
        font-size: 13px;
    }
}


.cashback_info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.3vw 0.25vw;
    margin-top: -0.35vw;
    margin-bottom: 0.6vw;
    background: transparent;
    border: 0;
}

.cashback_info p {
    display: flex;
    align-items: center;
    gap: 0.4vw;
    margin: 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9vw;
    font-weight: 600;
    line-height: 1;
}

.cashback_info img {
    width: 1vw;
    height: auto;
    object-fit: contain;
    flex-shrink: 0;
}

.cashback_info small {
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.72vw;
    font-weight: 600;
    margin-left: 0.12vw;
}

.cashback_info span {
    color: #00e6a8;
    font-size: 0.95vw;
    font-weight: 700;
    line-height: 1;
}

@media (max-width: 768px) {
    .cashback_info {
        padding: 0 2.326vw;
        margin-top: 1.163vw;
        margin-bottom: 0;
    }

    .cashback_info p {
        gap: 2.326vw;
        font-size: 3.721vw;
        font-weight: 600;
        line-height: 4.651vw;
    }

    .cashback_info img {
        width: 4.186vw;
    }

    .cashback_info small {
        font-size: 3.023vw;
        margin-left: 0.698vw;
    }

    .cashback_info span {
        font-size: 3.721vw;
        font-weight: 700;
        line-height: 4.651vw;
    }

    .cashback_info + .buy-now {
        margin-top: 4.651vw;
    }
}



/* ── GGirls Order Summary — pink/purple theme ──
   Scoped to .boost-form.ggirls-page so every other boost form
   (rank, win, pro-games, coaching, ...) keeps its original look. */
html.ggl-modal-open, body.ggl-modal-open { overflow: hidden !important; height: 100% !important; }

.boost-form.ggirls-page .order-summary{
    background:linear-gradient(165deg,#150726 0%,#1f0a3a 100%)!important;
    border:1px solid rgba(168,85,247,.22)!important;
    box-shadow:0 .6vw 2.6vw rgba(168,85,247,.10)!important;
}
.boost-form.ggirls-page .order-summary > h3{color:#fff!important}
.boost-form.ggirls-page .order-summary > h3 img{filter:drop-shadow(0 0 6px rgba(236,72,153,.35))}

.boost-form.ggirls-page .rank-box{
    display:flex!important;
    align-items:center!important;
    background:linear-gradient(135deg,rgba(168,85,247,.12),rgba(236,72,153,.05))!important;
    border:1px solid rgba(168,85,247,.22)!important;
    border-radius:14px!important;
    padding:14px 16px!important;
    position:relative!important;
    overflow:hidden!important;
}
.boost-form.ggirls-page .rank-box::before{
    content:'';position:absolute;left:0;top:10px;bottom:10px;width:3px;border-radius:8px;
    background:linear-gradient(180deg,#a855f7,#ec4899);
}
.boost-form.ggirls-page .rank-box .from{width:100%;padding-left:6px;text-align:left!important}
.boost-form.ggirls-page .ggl-summary-compact{width:100%;display:flex;align-items:center;gap:10px;text-align:left!important}
.boost-form.ggirls-page .ggl-summary-mode-icon{
    width:48px;height:48px;flex:0 0 48px;border-radius:13px;
    display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,rgba(168,85,247,.35),rgba(236,72,153,.2))!important;
    border:1px solid rgba(168,85,247,.4)!important;
    color:#fff!important;
    box-shadow:0 4px 16px rgba(168,85,247,.22);
}
.boost-form.ggirls-page .ggl-summary-mode-icon i{font-size:20px;line-height:1}
.boost-form.ggirls-page .ggl-summary-compact-copy{display:flex;flex-direction:column;gap:6px;min-width:0;text-align:left!important;align-items:flex-start!important}
.boost-form.ggirls-page .ggl-summary-compact-copy .title{
    font-size:18px!important;font-weight:900!important;line-height:1.2;color:#fff;
    text-align:left!important;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.boost-form.ggirls-page #ggl-summary-sub{
    display:inline-flex!important;align-items:center;width:fit-content;
    text-align:left!important;
    font-size:12px!important;font-weight:800!important;
    color:#e9d5ff!important;
    background:rgba(168,85,247,.18)!important;
    padding:3px 10px!important;border-radius:999px!important;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;
}
.boost-form.ggirls-page #completion-time::after{content:''}

@media(max-width:768px){
    .boost-form.ggirls-page .rank-box{padding:12px 14px!important;border-radius:12px!important}
    .boost-form.ggirls-page .ggl-summary-compact{gap:10px!important}
    .boost-form.ggirls-page .ggl-summary-mode-icon{width:40px!important;height:40px!important;flex-basis:40px!important;border-radius:11px!important}
    .boost-form.ggirls-page .ggl-summary-mode-icon i{font-size:17px!important}
    .boost-form.ggirls-page .ggl-summary-compact-copy .title{font-size:15px!important}
    .boost-form.ggirls-page #ggl-summary-sub{font-size:11px!important;padding:3px 9px!important}
}

/* Cashback row intentionally left without its own border/background — uses the page-wide default style. */

.boost-form.ggirls-page .buy-now{
    background:linear-gradient(135deg,#a855f7,#ec4899)!important;
    border:none!important;
    box-shadow:0 .5vw 1.8vw rgba(168,85,247,.35)!important;
    transition:opacity .15s,box-shadow .2s!important;
}
.boost-form.ggirls-page .buy-now:hover{opacity:.92!important;box-shadow:0 .65vw 2.4vw rgba(236,72,153,.4)!important}

.boost-form.lol-classic-page .lb-classic-coming-soon,
.lol-classic-boost .lb-classic-coming-soon{
    cursor:not-allowed!important;
    opacity:.72!important;
    color:rgba(255,255,255,.78)!important;
    background:linear-gradient(135deg,#444853,#343741)!important;
    border:1px solid rgba(255,255,255,.12)!important;
    box-shadow:none!important;
    pointer-events:none!important;
}
.boost-form.lol-classic-page .lb-classic-coming-soon i,
.lol-classic-boost .lb-classic-coming-soon i{
    color:#f6c453!important;
}

.boost-form.ggirls-page .payment-gateways{
    background:#1f0a3a!important;
    border:1px solid rgba(168,85,247,.18)!important;
    border-radius:14px!important;
}
.boost-form.ggirls-page .payment-gateways .text h5{color:#fff!important}
.boost-form.ggirls-page .payment-gateways .text p{color:rgba(255,255,255,.5)!important}
.boost-form.ggirls-page .payment-gateways .top img{filter:drop-shadow(0 0 6px rgba(236,72,153,.3))}

.boost-form.ggirls-page .lb-shield-trigger:hover,
.boost-form.ggirls-page .lb-shield-trigger:focus-visible{color:#f472b6!important}
.boost-form.ggirls-page .lb-shield-tooltip{border-color:rgba(168,85,247,.22)!important}


.boost-form.ranked-5s-page .rank-box .from,
.boost-form.ranked-5s-page .rank-box .to{
    min-width:0;
}
.boost-form.ranked-5s-page .r5s-current-summary-lp{
    display:none;
}
.boost-form.ranked-5s-page.r5s-show-current-lp .r5s-current-summary-lp{
    display:inline-block;
}
.boost-form.ranked-5s-page .r5s-summary-count{
    text-align:right;
    color:#fff;
    font-size:14px;
    line-height:1.15;
    font-weight:800;
    white-space:nowrap;
}
.boost-form.ranked-5s-page .r5s-summary-main{
    display:inline-flex;
    align-items:baseline;
    justify-content:flex-end;
    gap:5px;
}
.boost-form.ranked-5s-page .r5s-summary-main .win-count,
.boost-form.ranked-5s-page .r5s-summary-main .r5s-boosters-count{
    color:#fff;
    font-size:15px;
    font-weight:900;
    line-height:1;
}
.boost-form.ranked-5s-page .r5s-summary-label,
.boost-form.ranked-5s-page .r5s-booster-label{
    color:#fff;
    font-size:14px;
    font-weight:800;
}
.boost-form.ranked-5s-page .r5s-summary-separator{
    color:rgba(255,255,255,.45);
    font-size:13px;
    font-weight:700;
    padding:0 3px;
}


/* Ranked 5s slider spacing: keeps the handle and tooltip away from the card edges. */
.boost-form.ranked-5s-page .ranked-5s-boost .count-card .card-body{
    padding-left:clamp(30px,4vw,52px)!important;
    padding-right:clamp(30px,4vw,52px)!important;
    overflow:visible!important;
}
.boost-form.ranked-5s-page .ranked-5s-boost .count-card .range-slider{
    width:100%!important;
    max-width:100%!important;
    margin-left:auto!important;
    margin-right:auto!important;
}
@media (max-width:560px){
    .boost-form.ranked-5s-page .ranked-5s-boost .count-card .card-body{
        padding-left:28px!important;
        padding-right:28px!important;
    }
}

</style>

<div class="summary-wrapper">
    <div class="order-summary">
        <h3>
            <img src="<?= ASSET_URL ?>/website/images/cart.svg" alt="cart_icon"><?= t('Order Summary') ?>
        </h3>

        <div class="rank-box" <?php if ($data['type'] == 'coaching') {
            echo 'style="justify-content: center;"';
        } ?>>
            <div class="from">
                <?php switch ($data['type']) {
                    case 'rank': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <br>
                        <small class="current-summary-lp"><?= t('[ 0-20 LP ]') ?></small>
                        <?php break;
                    case 'win': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php if ($isClassicSummary): ?><br><small class="current-summary-lp" style="display:none"></small><?php endif; ?>
                        <?php break;
                    case 'placement': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php break;
                    case 'match': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php break;
                    case 'pro-games': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php break;
                    case 'ranked-5s': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <br>
                        <small class="current-summary-lp r5s-current-summary-lp"><?= t('[ 0 LP ]') ?></small>
                        <?php break;
                    case 'duo-pass': ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name" id="dp-sticky-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php break;
                    case 'ggirls': ?>
                        <div class="ggl-summary-compact">
                            <span class="ggl-summary-mode-icon"><i id="ggl-summary-mode-icon" class="fa-solid fa-gamepad"></i></span>
                            <span class="ggl-summary-compact-copy">
                                <span class="title current-summary-rank-name" id="ggl-summary-title"><?= t('Normal Draft Game') ?></span>
                                <small class="current-summary-lp" id="ggl-summary-sub"><?= t('Unranked · 1 Game') ?></small>
                            </span>
                        </div>
                        <?php break;
                    case 'normal': ?>
                        <div class="game game-mode"><?= t('Summoner\'s Rift') ?></div>
                        <?php break;
                    case 'coaching': ?>
                        <div class="game" style="text-align: center; width: 100%;">
                            <div style="font-size:16px;font-weight:800;color:#fff;line-height:1.2;">
                                <span class="hour-count"><?= t('5') ?></span><?= t(' Coaching Hours') ?>
                            </div>
                            <small style="display:inline-flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;color:rgba(255,255,255,.72);font-size:13px;font-weight:800;">
                                <img id="coaching-current-rank-img" src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon" style="width:44px;height:44px;object-fit:contain;">
                                <span id="coaching-current-rank-summary"><?= t($summaryDefaultRankLabel) ?></span>
                            </small>
                        </div>
                        <?php break;
                    case 'mastery': ?>
                        <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/2.webp" alt="rank_icon"
                            class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Level 1') ?></span>
                        <?php break;
                    case 'arena': ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?= ASSET_URL ?>/core/main/img/lol/arenas/3.webp" alt="rank_icon"
                                class="current-summary-rank-img">
                            <span class="title current-summary-rank-name"><?= t($summaryDefaultRankLabel) ?></span>
                        </div>
                        <?php break;
                    case 'level': ?>
                        <div class="game level-current-summary-name"><?= t('Level 1') ?></div>
                        <?php break;
                    case 'clash': ?>
                        <div class="game current-summary-rank-name"><?= t('Tier 1 (1 Booster)') ?></div>
                        <?php break;
                    default: ?>
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDefaultTier, $summaryDefaultDivision) : util_rank_img($summaryGame, 'mini', $summaryDefaultRankIcon) ?>" alt="rank_icon">
                        <span class="title"><?= t($summaryDefaultRankLabel) ?></span>
                        <?php break;
                } ?>
            </div>
            <?php if ($data['type'] != 'coaching' && $data['type'] != 'ggirls') { ?>
                <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <?php } ?>
            <?php switch ($data['type']) {
                case 'ggirls':
                    $enabledOptions = [];
                    break;
                case 'rank': ?>
                    <div class="to">
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDesiredTier, $summaryDesiredDivision) : util_rank_img($summaryGame, 'mini', $summaryDesiredRankIcon) ?>" alt="rank_icon" class="desired-summary-rank-img">
                        <span class="title desired-summary-rank-name"><?= t($summaryDesiredRankLabel) ?></span>
                        <br>
                        <small class="desired-summary-lp"></small>
                    </div>
                    <?php break;

                case 'ggirls': ?>
                    <?php break;

                case 'win': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Wins') ?>
                        </div>
                    </div>
                    <?php break;

                case 'placement': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'match': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'ranked-5s': ?>
                    <div class="to">
                        <div class="r5s-summary-count">
                            <div class="r5s-summary-main">
                                <span class="win-count r5s-games-count"><?= t('3') ?></span><span class="r5s-summary-label"><?= t('Games') ?></span>
                                <span class="r5s-summary-separator">|</span>
                                <span class="r5s-boosters-count">1</span><span class="r5s-booster-label"><?= t('Booster') ?></span>
                            </div>
                        </div>
                    </div>
                    <?php break;

                case 'pro-games': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count" id="pg-sticky-games"><?= t('3') ?></span><?= t('Games') ?>
                        </div>
                    </div>
                    <?php break;

                case 'duo-pass': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count" id="dp-sticky-hours">3</span><?= t('Hours') ?>
                        </div>
                    </div>
                    <?php break;

                case 'normal': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('3') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                case 'coaching': ?>
                    <?php break;

                case 'mastery': ?>
                    <div class="to">
                        <img src="<?= ASSET_URL ?>/core/main/img/lol/mastery/6.webp" alt="rank_icon"
                            class="desired-summary-rank-img">
                        <span class="title desired-summary-rank-name"><?= t('Level 6') ?></span>
                    </div>
                    <?php break;

                case 'arena': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Wins') ?>
                        </div>
                    </div>
                    <?php break;

                case 'level': ?>
                    <div class="to">
                        <div class="count level-desired-summary-name"><?= t('Level 30') ?></div>
                    </div>
                    <?php break;

                case 'clash': ?>
                    <div class="to">
                        <div class="count">
                            <span class="win-count"><?= t('2') ?></span><?= t('Matches') ?>
                        </div>
                    </div>
                    <?php break;

                default: ?>
                    <div class="to">
                        <img src="<?= $isClassicSummary ? lol_classic_rank_asset_url($summaryDesiredTier, $summaryDesiredDivision) : util_rank_img($summaryGame, 'mini', $summaryDesiredRankIcon) ?>" alt="rank_icon">
                        <span class="title"><?= t($summaryDesiredRankLabel) ?></span>
                    </div>
                    <?php break;
            } ?>
        </div>

        <?php if (($data['type'] ?? '') === 'ggirls'): ?>
            <button type="button" class="ggl-summary-picker" id="ggl-summary-row">
                <span class="ggl-picker-left">
                    <span class="ggl-picker-avatar-wrap">
                        <img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" id="ggl-summary-selected-avatar" alt="">
                        <span class="ggl-picker-dot" id="ggl-picker-dot"></span>
                    </span>
                    <span class="ggl-picker-copy">
                        <span class="ggl-picker-label"><?= t('Request GGirl') ?></span>
                        <strong id="ggl-summary-selected-name"><?= t('Any Available') ?></strong>
                    </span>
                </span>
                <span class="ggl-picker-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </button>
            <div id="ggl-summary-overlay" class="ggl-summary-overlay">
                <div class="ggl-summary-panel">
                    <div class="ggl-summary-panel-head">
                        <strong><?= t('Select your GGirl') ?></strong>
                        <button type="button" id="ggl-summary-close">×</button>
                    </div>
                    <div class="ggl-summary-list" id="ggl-summary-list"></div>
                </div>
            </div>
            <style>
            /* ── GGirl picker row + modal — pink/purple theme ──
               (.ggl-summary-compact / .ggl-summary-mode-icon / #ggl-summary-sub
               are fully styled in the "GGirls Order Summary" block above) */

            .ggl-summary-picker{
                width:100%;margin-top:12px;padding:14px 15px;border-radius:14px;
                border:1px solid rgba(168,85,247,.22);background:rgba(168,85,247,.06);
                color:#fff;display:flex;align-items:center;justify-content:space-between;gap:12px;
                cursor:pointer;text-align:left;transition:border-color .18s,background .18s;
            }
            .ggl-summary-picker:hover{border-color:rgba(236,72,153,.55);background:rgba(168,85,247,.1)}
            .ggl-picker-left{display:flex;align-items:center;gap:11px;min-width:0}
            .ggl-picker-avatar-wrap{position:relative;width:44px;height:44px;border-radius:50%;background:rgba(168,85,247,.14);overflow:hidden;flex:0 0 44px;border:1px solid rgba(168,85,247,.25)}
            .ggl-picker-avatar-wrap img{width:100%;height:100%;object-fit:cover}
            .ggl-picker-dot{position:absolute;right:1px;bottom:1px;width:11px;height:11px;border-radius:50%;background:rgba(255,255,255,.2);border:2px solid #150726;display:none}
            .ggl-picker-dot.on{display:block;background:#18e0a7}
            .ggl-picker-copy{display:flex;flex-direction:column;min-width:0}
            .ggl-picker-label{font-size:11px;font-weight:900;text-transform:uppercase;color:#c084fc;line-height:1.15}
            .ggl-picker-copy strong{margin-top:3px;color:#fff;font-size:15px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .ggl-picker-arrow{width:29px;height:29px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(168,85,247,.14);color:#f472b6;flex:0 0 29px}
            .ggl-picker-arrow i{font-size:12px;line-height:1}

            .ggl-summary-overlay{display:none;position:fixed;inset:0;z-index:2147483000;background:rgba(8,2,18,.78);backdrop-filter:blur(8px);align-items:flex-start;justify-content:center;padding:5vh 18px 18px}
            .ggl-summary-overlay.open{display:flex}
            .ggl-summary-panel{
                width:min(820px,calc(100vw - 36px));max-height:86vh;overflow:hidden;border-radius:18px;
                background:linear-gradient(165deg,#150726 0%,#1f0a3a 100%);
                border:1px solid rgba(168,85,247,.3);
                box-shadow:0 25px 90px rgba(0,0,0,.6),0 0 0 1px rgba(236,72,153,.06);
                position:relative;z-index:2147483001;
            }
            .ggl-summary-panel-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid rgba(168,85,247,.16);color:#fff;font-size:19px}
            .ggl-summary-panel-head button{width:36px;height:36px;border:0;border-radius:50%;background:rgba(255,255,255,.08);color:#fff;font-size:25px;line-height:1;cursor:pointer;transition:background .15s,color .15s}
            .ggl-summary-panel-head button:hover{background:rgba(236,72,153,.22);color:#f472b6}
            .ggl-summary-list{display:grid;grid-template-columns:1fr;gap:10px;padding:16px;overflow:auto;max-height:calc(86vh - 72px)}
            .ggl-summary-girl{display:grid;grid-template-columns:50px minmax(120px,160px) minmax(0,1fr) auto;align-items:center;gap:12px;padding:13px 14px;border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,.055),rgba(255,255,255,.025));border:1px solid rgba(168,85,247,.16);cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s,transform .15s;min-height:86px}
            .ggl-summary-girl.active,.ggl-summary-girl:hover{border-color:#a855f7;background:linear-gradient(135deg,rgba(168,85,247,.18),rgba(236,72,153,.1));box-shadow:0 0 0 1px rgba(168,85,247,.24);transform:translateY(-1px)}
            .ggl-summary-girl > img{width:50px;height:50px;border-radius:50%;object-fit:cover;background:rgba(255,255,255,.07);flex:0 0 50px;border:2px solid rgba(255,255,255,.08)}
            .ggl-summary-girl strong{display:block;color:#fff;font-size:15px;font-weight:900;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .ggl-summary-girl small{display:block;margin-top:4px;color:rgba(255,255,255,.52);font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .ggl-summary-info{display:contents;min-width:0}
            .ggl-summary-head{display:flex;flex-direction:column;min-width:0}
            .ggl-summary-meta{display:flex;flex-wrap:nowrap;align-items:center;gap:8px;min-width:0;overflow:hidden}
            .ggl-summary-meta + .ggl-summary-meta{display:none}
            .ggl-summary-pill{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 10px;border-radius:11px;background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.11);color:rgba(255,255,255,.84);font-size:11px;font-weight:850;line-height:1;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:0 0 auto;box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
            .ggl-summary-pill i{color:#c084fc;font-size:12px;line-height:1;flex:0 0 auto}
            .ggl-summary-pill img{width:20px;height:20px;object-fit:contain;border-radius:0;flex:0 0 auto;background:transparent}
            .ggl-summary-pill.flags{padding-left:9px;padding-right:9px;gap:6px}
            .ggl-summary-pill.flags img{width:21px;height:21px;border-radius:50%;object-fit:cover}
            .ggl-summary-pill.rank img{width:22px;height:22px}
            .ggl-summary-pill.time{max-width:260px}
            .ggl-summary-profile-btn{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:7px 14px;border-radius:11px;border:1px solid rgba(99,102,241,.6);background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(99,102,241,.07));color:#a5b4fc!important;text-decoration:none!important;font-size:10px;font-weight:950;white-space:nowrap;letter-spacing:.03em;text-transform:uppercase;transition:border-color .15s,background .15s,color .15s;flex:0 0 auto}
            .ggl-summary-profile-btn:hover{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.9);color:#c7d2fe!important}

            /* "Any Available" card — the random / default option */
            .ggl-summary-any-icon{
                width:44px;height:44px;border-radius:50%;flex:0 0 44px;
                display:flex;align-items:center;justify-content:center;
                background:linear-gradient(135deg,rgba(168,85,247,.32),rgba(236,72,153,.2));
                border:1px solid rgba(168,85,247,.4);color:#f472b6;font-size:17px;
            }
            .ggl-summary-girl.ggl-summary-any{grid-column:1 / -1;grid-template-columns:50px minmax(120px,1fr) auto;border-style:dashed;border-color:rgba(168,85,247,.3)}
            .ggl-summary-girl.ggl-summary-any.active,.ggl-summary-girl.ggl-summary-any:hover{border-style:solid;border-color:#a855f7}

            body.ggl-modal-open .select2-container--open .select2-dropdown{display:none!important}
            @media(max-width:720px){
                .ggl-summary-list{grid-template-columns:1fr}
                .ggl-summary-girl{grid-template-columns:46px minmax(80px,1fr);align-items:flex-start}
                .ggl-summary-meta{grid-column:1 / -1;flex-wrap:wrap;overflow:visible}
                .ggl-summary-profile-btn{grid-column:1 / -1;width:100%;margin-top:2px}
                .ggl-summary-girl.ggl-summary-any{grid-template-columns:46px 1fr}
                .ggl-summary-overlay{align-items:flex-end;padding:0}
                .ggl-summary-panel{border-radius:18px 18px 0 0;max-height:88vh}
            }
            </style>
        <?php endif; ?>

        <?php
        $exclude_duo = [15, 18, 19, 20, 29, 33, 34];

// Pro Games and Duo Pass are always played with a booster.
$pro_games_duo_only = [26, 27, 35, 36];

// TFT forms that should always be SOLO (no duo toggle)
$tft_solo_only = [21, 22, 23];

// TFT Coaching (form id 25) should be SOLO-only and must not show extra options like Champs & Roles
$tft_coaching_only = [25];

// TFT Double Up (form id 24) supports Solo + Duo
$tft_double_up = [24];

// Eindeutige IDs (optional, aber empfohlen)
$soloId = 'solo_' . $data['id'];
$duoId = 'duo_' . $data['id'];

if (($data['type'] ?? '') === 'ggirls'): ?>
    <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
<?php elseif (in_array($data['id'], $pro_games_duo_only)): ?>
    <input type="radio" id="<?= $duoId ?>" name="is_duo" value="1" checked hidden>
<?php elseif (in_array($data['id'], $tft_solo_only) || in_array($data['id'], $tft_coaching_only)): ?>
    <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
<?php elseif (!in_array($data['id'], $exclude_duo)): ?>
    <div class="toggle-group">
        <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" <?= ($data['id']==24 ? 'checked' : '') ?>>
        <label for="<?= $soloId ?>" class="toggle-label" data-tooltip="<?= t('Booster plays on your Account') ?>" tabindex="0">
            <i class="fa-duotone fa-user me"></i> <?= t('Solo (Pro on your Account)') ?>
        </label>

        <input type="radio" id="<?= $duoId ?>" name="is_duo" value="1" <?= ($data['id']==24 ? '' : 'checked') ?>>
        <label for="<?= $duoId ?>" class="toggle-label" data-tooltip="<?= t('Play with the Booster') ?>" tabindex="0">
            <i class="fa-duotone fa-user-group"></i> <?= t('Duo (Play with Pro)') ?>
        </label>
    </div>
<?php else: ?>
    <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
<?php endif; ?>
<?php
// define all options once
        $allOptions = [
            'priority' => [
                'label' => 'Priority Boost',
                'icon' => ASSET_URL . '/website/images/boost-forms/priority.svg',
                'badge' => '25%',
                'badgeClass' => 'primary',
                'input' => ['id' => 'is_priority', 'name' => 'is_priority', 'value' => 1],
                'tooltip' => t('Your order will be finished about twice as fast as a normal order.'),
            ],
            'bonus' => [
                'label' => '+1 Bonus Win',
                'icon' => ASSET_URL . '/website/images/boost-forms/bonus-win1.svg',
                'badge' => 'AUTO',
                'badgeClass' => 'primary',
                'input' => ['id' => 'is_bonus_win', 'name' => 'is_bonus_win', 'value' => 1],
                'tooltip' => t('Once your target rank is reached, your booster will win an extra match for you.'),
            ],
            'solo_queue' => [
                'label' => 'Solo Only',
                'icon' => ASSET_URL . '/website/images/boost-forms/solo-queue1.svg',
                'badge' => '+20%',
                'badgeClass' => 'primary',
                'class' => 'solo-option',
                'input' => ['id' => 'is_solo_only', 'name' => 'is_solo_only', 'value' => 1],
                'tooltip' => t('Your booster will play solo only on your account and will not duo with any other account.'),
            ],
            'stream' => [
                'label' => 'Stream Games',
                'icon' => ASSET_URL . '/website/images/boost-forms/stream-games1.svg',
                'badge' => '+15%',
                'badgeClass' => 'primary',
                'class' => 'solo-option',
                'input' => ['id' => 'is_streaming', 'name' => 'is_streaming', 'value' => 1],
                'tooltip' => t('Your booster will privately stream you the games while he is playing.'),
            ],
            'coaching' => [
                'label' => 'Voice Chat',
                'icon' => ASSET_URL . '/website/images/boost-forms/champs-roles1.svg',
                'badge' => '+20%',
                'badgeClass' => 'primary',
                'class' => 'duo-option',
                'input' => ['id' => 'is_coaching', 'name' => 'is_coaching', 'value' => 1],
                'tooltip' => t('Your booster will join a call, give you feedback, and help you play better.'),
            ],
            'voice' => [
                'label' => 'Voice Chat',
                'icon' => ASSET_URL . '/website/images/boost-forms/champs-roles1.svg',
                'badge' => 'FREE',
                'badgeClass' => 'success',
                'class' => 'duo-option',
                'input' => ['id' => 'is_voice', 'name' => 'is_voice', 'value' => 1],
                'tooltip' => t('Select this if you want voice chat with your booster.'),
            ],
            'hidden_duo' => [
                'label' => 'Hidden Duo',
                'icon' => ASSET_URL . '/website/images/boost-forms/hidden_duo3.svg',
                'badge' => '+50%',
                'badgeClass' => 'primary',
                'class' => 'duo-option',
                'input' => ['id' => 'is_hidden_duo', 'name' => 'is_hidden_duo', 'value' => 1],
                'tooltip' => t('Your booster will play with you using more than one account.'),
                'font-awesome-icon' => 'fa-duotone fa-user-secret',
            ],
            'champs_roles' => [
                'label' => 'Champs & Roles',
                'icon' => ASSET_URL . '/website/images/boost-forms/champs-roles1.svg',
                'badge' => '',
                'badgeClass' => '',
                'class' => 'solo-option',
                'input' => ['id' => 'is_champions_roles', 'name' => 'is_champions_roles', 'value' => 1],
                'tooltip' => in_array((int)($data['id'] ?? 0), [15, 16, 25]) ? t('Select your preferred champions and roles so your coach knows how to tailor the session for you.') : t('Select which champions and roles the booster can play.'),
            ],
            'undercover_winrate' => [
                'label' => 'Undercover Winrate',
                'icon' => ASSET_URL . '/website/images/boost-forms/undercover-winrate.svg',
                'badge' => '+25%',
                'badgeClass' => 'primary',
                'input' => ['id' => 'is_undercover_winrate', 'name' => 'is_undercover_winrate', 'value' => 1],
                'tooltip' => t('The booster will try to keep the win rate at 65% or below so it doesn’t look suspicious.'),
                'font-awesome-icon' => 'fa-duotone fa-user-secret',
            ],
            'moderate_kda' => [
                'label' => 'Moderate KDA',
                'icon' => ASSET_URL . '/website/images/boost-forms/moderate-kda.svg',
                'badge' => '+20%',
                'badgeClass' => 'primary',
                'input' => ['id' => 'is_moderate_kda', 'name' => 'is_moderate_kda', 'value' => 1],
                'tooltip' => t('The booster will play through your order and keep the average KDA at 4.5 or below overall.'),
                'font-awesome-icon' => 'fa-duotone fa-chart-line',
            ],

        ];

        // Reward Box perks: show free reward badges when the logged-in client has an unused perk.
        $lbRewardOrderPerks = [];
        if (defined('CLIENT_ID') && (int)CLIENT_ID > 0) {
            global $db;
            try {
                $perkRows = $db->run(
                    "SELECT reward_type FROM client_rewards
                     WHERE client_id = ?
                       AND status = 'unused'
                       AND reward_type IN ('priority_boost','champion_preference','offline_mode','vpn_addon')
                       AND (expires_at IS NULL OR expires_at = '0000-00-00 00:00:00' OR expires_at >= NOW())
                     GROUP BY reward_type",
                    (int)CLIENT_ID
                ) ?: [];
                foreach ($perkRows as $perkRow) {
                    $lbRewardOrderPerks[(string)($perkRow['reward_type'] ?? '')] = true;
                }
            } catch (\Throwable $e) {}
        }
        $lbApplyRewardPerkBadge = static function(array $keys, string $rewardType) use (&$allOptions, $lbRewardOrderPerks) {
            if (empty($lbRewardOrderPerks[$rewardType])) { return; }
            foreach ($keys as $key) {
                if (!isset($allOptions[$key])) { continue; }
                if (strtoupper((string)($allOptions[$key]['badge'] ?? '')) === 'FREE') { continue; }
                $allOptions[$key]['badge'] = 'FREE Reward';
                $allOptions[$key]['badgeClass'] = 'success';
                $allOptions[$key]['tooltip'] = trim((string)($allOptions[$key]['tooltip'] ?? '') . ' ' . t('You have an unused reward for this option. It will be applied for free at checkout.'));
            }
        };

        $lbApplyRewardPerkBadge(['priority'], 'priority_boost');
        $lbApplyRewardPerkBadge(['champs_roles'], 'champion_preference');

        // TFT solo-only forms: ensure options are visible in solo mode
        if (in_array($data['id'], $tft_solo_only)) {
            // Voice Chat should be selectable even though TFT is solo-only
            if (isset($allOptions['coaching'])) {
                unset($allOptions['coaching']['class']);
            }
            // Stream is already solo-option; keep as is
        }


        
        // decide which options to show depending on type / form
        if (in_array($data['id'], $tft_coaching_only)) {
            // ✅ TFT Coaching: no Solo/Duo toggle and no Champs & Roles option in order summary
            $enabledOptions = [];

        } elseif (in_array($data['id'], $tft_solo_only)) {
            // TFT SOLO-only (Rank / Win / Placements etc.)
            switch ($data['type']) {
                case 'ggirls':
                    $enabledOptions = [];
                    break;
                case 'rank':
                    $enabledOptions = ['priority', 'bonus', 'stream', 'coaching'];
                    break;
                case 'win':
                    $enabledOptions = ['priority', 'stream', 'coaching'];
                    break;
                default:
                    $enabledOptions = ['priority', 'stream', 'coaching'];
                    break;
            }

            // ✅ Form ID 23: never show Bonus Win
            if ($data['id'] == 23) {
                $enabledOptions = array_values(array_diff($enabledOptions, ['bonus']));
            }

        } elseif (in_array($data['id'], $tft_double_up)) {
            // TFT Double Up (form id 24) - Solo + Duo
            // Solo options: Priority Boost, Stream Games
            // Duo options: Priority Boost, Voice Chat
            $enabledOptions = ['priority', 'stream', 'coaching'];

        } else {
            switch ($data['type']) {
                case 'ggirls':
                    $enabledOptions = [];
                    break;
                case 'rank':
                    // Undercover Winrate & Moderate KDA for LoL forms 1, 2 and 3
                    if (in_array((int)($data['id'] ?? 0), [1, 2, 3], true)) {
                        $enabledOptions = ['priority', 'bonus', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles', 'undercover_winrate', 'moderate_kda'];
                    } else {
                        $enabledOptions = ['priority', 'bonus', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    }
                    break;
                case 'win':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    break;
                case 'placement':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    break;
                case 'match':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    break;
                case 'ranked-5s':
                    $enabledOptions = ['priority'];
                    break;
                case 'pro-games':
                    $enabledOptions = []; // no extra options for pro-games
                    break;
                case 'duo-pass':
                    $enabledOptions = ['voice'];
                    break;
                case 'normal':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    break;
                case 'coaching':
                    $enabledOptions = ['champs_roles'];
                    break;
                case 'mastery':
                    $enabledOptions = ['priority', 'solo_queue', 'stream'];
                    break;
                case 'arena':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
                    break;
                case 'level':
                    $enabledOptions = ['priority', 'solo_queue', 'stream', 'champs_roles'];
                    break;
                case 'clash':
                    $enabledOptions = [];
                    break;
                default:
                    $enabledOptions = ['priority', 'champs_roles'];
                    break;
            }
        }
        // Also show these privacy options on form IDs 2 and 3, even if their type is not "rank".
        if (in_array((int)($data['id'] ?? 0), [2, 3], true)) {
            foreach (['undercover_winrate', 'moderate_kda'] as $lbExtraPrivacyOpt) {
                if (!in_array($lbExtraPrivacyOpt, $enabledOptions, true)) {
                    $enabledOptions[] = $lbExtraPrivacyOpt;
                }
            }
        }

        // Split options: privacy options get their own section
        $privacyKeys    = ['champs_roles', 'undercover_winrate', 'moderate_kda', 'hidden_duo'];
        $mainOptions    = array_values(array_diff($enabledOptions, $privacyKeys));
        $privacyOptions = array_values(array_intersect($enabledOptions, $privacyKeys));
        // keep privacy in a fixed display order
        $privacyOrder   = array_values(array_intersect($privacyKeys, $enabledOptions));
        ?>


        <div class="extra-options">
            <?php foreach ($mainOptions as $optKey):
                $opt = $allOptions[$optKey]; ?>
                <div class="option <?= $opt['class'] ?? '' ?>" data-tooltip="<?= $opt['tooltip'] ?? '' ?>">
                    <div class="text">
                        <?php if (isset($opt['font-awesome-icon'])): ?>
                            <i class="<?= $opt['font-awesome-icon'] ?>"></i>
                        <?php else: ?>
                            <img src="<?= $opt['icon'] ?>" alt="option_icon">
                        <?php endif; ?>
                        <?= $opt['label'] ?>
                        <?php if (!empty($opt['badge'])): ?>
                            <span class="badge <?= $opt['badgeClass'] ?>">
                                <?= $opt['badge'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="<?= $opt['input']['id'] ?>" name="<?= $opt['input']['name'] ?>"
                            value="<?= $opt['input']['value'] ?>">
                        <span class="slider"></span>
                    </label>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($privacyOrder)): ?>
                <div class="privacy-settings-header"><?= in_array((int)($data['id'] ?? 0), [15, 16, 25]) ? t('Your Champions & Roles') : t('Privacy Settings') ?></div>
                <?php foreach ($privacyOrder as $optKey):
                    $opt = $allOptions[$optKey]; ?>
                    <div class="option <?= $opt['class'] ?? '' ?>" data-tooltip="<?= $opt['tooltip'] ?? '' ?>">
                        <div class="text">
                            <?php if (isset($opt['font-awesome-icon'])): ?>
                                <i class="<?= $opt['font-awesome-icon'] ?>"></i>
                            <?php else: ?>
                                <img src="<?= $opt['icon'] ?>" alt="option_icon">
                            <?php endif; ?>
                            <?= $opt['label'] ?>
                            <?php if (!empty($opt['badge'])): ?>
                                <span class="badge <?= $opt['badgeClass'] ?>">
                                    <?= $opt['badge'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="<?= $opt['input']['id'] ?>" name="<?= $opt['input']['name'] ?>"
                                value="<?= $opt['input']['value'] ?>">
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (in_array((int)($data['id'] ?? $data['form_id'] ?? 0), [26, 35], true)): ?>
        <div id="pg-selected-booster-summary" style="display:none;"></div>
        <?php endif; ?>

        <?php
        // ── Booster Selector Row + Modal ──────────────────────────────────────
        $__smFormId2    = (int)($data['id'] ?? $data['form_id'] ?? 0);
        $__smHide       = in_array($__smFormId2, [15, 16, 25, 26, 35], true) || (($data['type'] ?? '') === 'ggirls'); // coaching / pro-games / ggirls
        $__smMultiRequest = in_array($__smFormId2, [4, 19, 29], true);
        if (!$__smHide):
            $__smGame2 = $data['game'] ?? 'lol';
            $__smGameKey2 = strtolower(trim((string)($__smGame2 ?? 'lol')));
            if (str_contains($__smGameKey2, 'val')) $__smGameKey2 = 'val';
            elseif (str_contains($__smGameKey2, 'tft')) $__smGameKey2 = 'tft';
            else $__smGameKey2 = 'lol';
            $__smAll2 = []; // Loaded on demand via Ajax after the booster selector is opened.
        ?>

        <!-- ── Booster Row ── -->
        <div class="bsr-row" id="bsr-row">
            <div class="bsr-left">
                <div class="bsr-icon-wrap">
                    <img src="https://lolboost.gg/public/uploads/icons/default.png" id="bsr-avatar" alt="">
                    <span class="bsr-dot" id="bsr-dot"></span>
                </div>
                <div class="bsr-info">
                    <span class="bsr-label"><?= t('Request Booster') ?></span>
                    <span class="bsr-name" id="bsr-name"><?= t('Any Available') ?></span>
                </div>
            </div>
            <svg class="bsr-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
        </div>
        <input type="hidden" name="<?= $__smMultiRequest ? 'selected_boosters' : 'booster_id' ?>" id="bsr-hidden" value="">

        <style>
        /* ── Row ── */
        .bsr-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; margin-top:8px;
            background:#0A0B17; border:1.5px solid rgba(255,255,255,.045);
            border-radius:12px; cursor:pointer;
            transition:border-color .18s, background .18s; user-select:none;
        }
        .bsr-row:hover,.bsr-row.has-sel{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.07);}
        .bsr-left{display:flex;align-items:center;gap:10px;}
        .bsr-icon-wrap{position:relative;flex-shrink:0;}
        .bsr-icon-wrap img{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.1);display:block;}
        .bsr-dot{position:absolute;bottom:1px;right:1px;width:9px;height:9px;border-radius:50%;border:2px solid #0d0d1a;background:rgba(255,255,255,.15);display:none;}
        .bsr-dot.on{background:#35d07f;display:block;}
        .bsr-info{display:flex;flex-direction:column;gap:1px;}
        .bsr-label{font-size:10px;color:rgba(255,255,255,.38);font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
        .bsr-name{font-size:13px;font-weight:700;color:rgba(255,255,255,.88);}
        .bsr-chev{width:14px;height:14px;color:rgba(255,255,255,.3);flex-shrink:0;}

        /* ── Overlay ── */
        #bsr-overlay{
            display:none;position:fixed;inset:0;z-index:9999999!important;
            align-items:flex-end;justify-content:center;
            background:rgba(0,0,0,0);transition:background .25s;
        }
        #bsr-overlay.bsr-open{display:flex;background:rgba(0,0,0,.78);}

        /* ── Panel ── */
        #bsr-panel{
            width:100%;max-width:720px;box-sizing:border-box;
            background:#0A0B17;border:1px solid rgba(255,255,255,.045);
            border-radius:20px 20px 0 0;padding:0;
            transform:translateY(100%);transition:transform .32s cubic-bezier(.4,0,.2,1);
            height:100%;max-height:100%;display:flex;flex-direction:column;overflow:hidden;
        }
        #bsr-overlay.bsr-open #bsr-panel{transform:translateY(0);}
        /* Desktop: centered dialog */
        @media(min-width:720px){
            #bsr-overlay{align-items:center;}
            #bsr-panel{border-radius:18px;height:auto;max-height:82vh;}
        }

        /* ── Panel header ── */
        .bsr-ph{
            display:flex;align-items:center;justify-content:space-between;
            padding:max(20px,env(safe-area-inset-top)) 20px 0;flex-shrink:0;
        }
        .bsr-ph-title{font-size:17px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px;}
        .bsr-pulse{width:8px;height:8px;border-radius:50%;background:#35d07f;flex-shrink:0;
            animation:_bsrpulse 1.5s ease-out infinite;display:none;}
        .bsr-pulse.vis{display:inline-block;}
        @keyframes _bsrpulse{0%{box-shadow:0 0 0 0 rgba(53,208,127,.5)}70%{box-shadow:0 0 0 8px rgba(53,208,127,0)}100%{box-shadow:0 0 0 0 rgba(53,208,127,0)}}
        .bsr-filter-pills{display:flex;align-items:center;gap:8px;margin-top:10px;flex-wrap:wrap;}
        .bsr-filter-pill{
            display:inline-flex;align-items:center;gap:7px;min-height:28px;padding:6px 12px;border-radius:999px;
            border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.045);
            color:rgba(255,255,255,.54);font-size:11px;font-weight:850;line-height:1;cursor:pointer;
            transition:background .15s,border-color .15s,color .15s,transform .15s;font-family:inherit;
        }
        .bsr-filter-pill:hover{border-color:rgba(99,102,241,.35);color:rgba(255,255,255,.82);background:rgba(99,102,241,.08);}
        .bsr-filter-pill.active{border-color:rgba(99,102,241,.62);color:#fff;background:rgba(99,102,241,.18);}
        .bsr-filter-pill-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.32);flex-shrink:0;}
        .bsr-filter-pill[data-filter=all] .bsr-filter-pill-dot{background:rgba(99,102,241,.9);}
        .bsr-filter-pill[data-filter=online] .bsr-filter-pill-dot{background:#35d07f;}
        .bsr-filter-pill[data-filter=offline] .bsr-filter-pill-dot{background:rgba(255,255,255,.32);}
        .bsr-close{width:32px;height:32px;border-radius:50%;
            border:1px solid rgba(255,255,255,.1)!important;background:rgba(255,255,255,.05)!important;
            color:rgba(255,255,255,.6)!important;display:flex;align-items:center;justify-content:center;
            cursor:pointer;transition:.15s;padding:0!important;flex-shrink:0;}
        .bsr-close svg{width:14px;height:14px;pointer-events:none;}
        .bsr-close:hover{background:rgba(255,255,255,.12)!important;color:#fff!important;}

        /* ── Search bar ── */
        .bsr-search-wrap{
            margin:14px 20px 10px;
            background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);
            border-radius:10px;display:flex;align-items:center;gap:10px;padding:10px 14px;
            transition:border-color .15s;flex-shrink:0;
        }
        .bsr-search-wrap:focus-within{border-color:rgba(99,102,241,.5);background:rgba(99,102,241,.05);}
        .bsr-search-wrap svg{width:14px;height:14px;color:rgba(255,255,255,.35);flex-shrink:0;}
        .bsr-search-inp{
            flex:1;background:transparent;border:none!important;outline:none!important;
            color:#fff!important;font-size:16px;height:20px!important;font-family:inherit;
            box-shadow:none!important;padding:0!important;
        }
        @media(min-width:720px){
            .bsr-search-inp{font-size:13px;}
        }
        .bsr-search-inp::placeholder{color:rgba(255,255,255,.3);}
        .bsr-search-clear{background:none!important;border:none!important;padding:0!important;
            color:rgba(255,255,255,.3)!important;cursor:pointer;font-size:16px;line-height:1;
            display:none;flex-shrink:0;}
        .bsr-search-clear.vis{display:block;}

        /* ── Grid scroll area ── */
        .bsr-grid-wrap{flex:1;overflow-y:auto;padding:14px 20px 8px;}
        .bsr-grid-wrap::-webkit-scrollbar{width:4px;}
        .bsr-grid-wrap::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}

        /* Online section header */
        .bsr-sec-hd{
            font-size:11px;font-weight:700;color:rgba(255,255,255,.38);
            text-transform:uppercase;letter-spacing:.07em;
            margin-bottom:10px;display:flex;align-items:center;gap:6px;
        }
        .bsr-sec-hd-dot{width:7px;height:7px;border-radius:50%;background:#35d07f;
            animation:_bsrpulse 1.5s ease-out infinite;}

        /* ── Booster grid (like champion picker) ── */
        .bsr-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(88px,1fr));
            gap:8px;margin-bottom:16px;
        }
        .bsr-item{
            display:flex;flex-direction:column;align-items:center;gap:5px;
            padding:10px 6px 8px;border-radius:12px;cursor:pointer;
            border:1.5px solid rgba(255,255,255,.045);background:rgba(255,255,255,.022);
            transition:border-color .16s,background .16s,transform .14s;
            position:relative;
        }
        .bsr-item:hover{border-color:rgba(99,102,241,.4);background:rgba(99,102,241,.07);transform:translateY(-1px);}
        .bsr-item.sel{border-color:rgba(99,102,241,.85)!important;background:rgba(99,102,241,.15)!important;}
        .bsr-item.hidden{display:none!important;}

        .bsr-item-av{position:relative;flex-shrink:0;}
        .bsr-item-av img{
            width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;
            border:2px solid rgba(255,255,255,.08);transition:border-color .16s;
        }
        .bsr-item.sel .bsr-item-av img{border-color:rgba(99,102,241,.7);}
        .bsr-item-dot{
            position:absolute;bottom:2px;right:2px;width:11px;height:11px;
            border-radius:50%;border:2px solid #0d0d18;background:rgba(255,255,255,.15);
        }
        .bsr-item-dot.on{background:#35d07f;}

        .bsr-item-name{
            font-size:11px;font-weight:700;color:rgba(255,255,255,.78);
            text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
            width:100%;max-width:78px;line-height:1.2;
        }
        .bsr-item.sel .bsr-item-name{color:#a5b4fc;}
        .bsr-item-meta{display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;min-width:0;}
        .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill{
            max-width:100%;display:inline-flex;align-items:center;justify-content:center;gap:4px;
            min-height:20px;padding:3px 6px;border-radius:999px;
            background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.07);
            color:rgba(255,255,255,.58);font-size:10px;font-weight:700;line-height:1;
            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
        }
        .bsr-rank-pill img{width:15px;height:15px;object-fit:contain;flex-shrink:0;}
        .bsr-role-icons,.bsr-lang-icons{display:inline-flex;align-items:center;gap:3px;flex-shrink:0;}
        .bsr-role-icons img{width:13px;height:13px;object-fit:contain;opacity:.82;}
        .bsr-lang-icons img{width:16px;height:16px;object-fit:cover;border-radius:50%;opacity:.95;}
        .bsr-roles-label{display:none;}
        .bsr-rank-label{overflow:hidden;text-overflow:ellipsis;}
        .bsr-pill-icon{font-size:10px;color:rgba(255,255,255,.48);line-height:1;}
        .bsr-profile-btn{display:none;}

        @media(min-width:720px){
            #bsr-panel{
                width:min(1140px,calc(100vw - 44px));
                max-width:1140px;
                border-radius:22px;
            }
            .bsr-ph{padding:22px 28px 0;}
            .bsr-ph-title{font-size:18px;}
            .bsr-filter-pills{margin-top:11px;}
            .bsr-search-wrap{margin:16px 28px 14px;padding:12px 15px;border-radius:12px;}
            .bsr-search-inp{font-size:14px;height:24px!important;}
            .bsr-grid-wrap{padding:15px 28px 10px;overflow-x:hidden;}
            .bsr-sec-hd{font-size:12px;margin-bottom:12px;}
            .bsr-grid{display:flex;flex-direction:column;gap:9px;width:100%;}
            .bsr-item{
                width:100%;box-sizing:border-box;
                display:grid;
                grid-template-columns:58px minmax(105px,145px) minmax(0,1fr);
                align-items:center;gap:12px;
                padding:11px 14px;min-height:70px;border-radius:15px;
                background:rgba(255,255,255,.022);
                border:1.5px solid rgba(255,255,255,.045);
                box-shadow:0 10px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.02);
            }
            .bsr-item:hover{
                background:linear-gradient(135deg,rgba(99,102,241,.16),rgba(255,255,255,.035));
                border-color:rgba(99,102,241,.42);
                transform:translateY(-1px);
            }
            .bsr-item-av img{width:50px;height:50px;border-width:2px;}
            .bsr-item-dot{width:12px;height:12px;bottom:3px;right:3px;}
            .bsr-item-name{
                text-align:left;max-width:none;width:auto;
                font-size:14px;font-weight:900;color:rgba(255,255,255,.94);
                letter-spacing:-.01em;
            }
            .bsr-item-meta{
                display:flex;
                flex-direction:row;
                align-items:center;
                justify-content:flex-start;
                gap:8px;
                width:100%;
                min-width:0;
                overflow:hidden;
                white-space:nowrap;
            }
            .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill{
                justify-content:flex-start;font-size:11px;font-weight:850;padding:8px 10px;min-height:38px;border-radius:11px;
                color:rgba(255,255,255,.84);box-sizing:border-box;width:max-content;max-width:none;gap:7px;flex:0 0 auto;
                background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.11);
                box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
            }
            .bsr-rank-pill{max-width:none;}
            .bsr-rank-label{overflow:hidden;text-overflow:ellipsis;}
            .bsr-roles-pill{padding-left:9px;padding-right:9px;}
            .bsr-lang-pill{padding-left:9px;padding-right:9px;}
            .bsr-rank-pill img{width:21px;height:21px;}
            .bsr-role-icons,.bsr-lang-icons{gap:6px;flex-wrap:nowrap;}
            .bsr-role-icons img{width:19px;height:19px;opacity:.92;flex:0 0 auto;}
            .bsr-lang-icons img{width:20px;height:20px;border-radius:50%;}
            .bsr-pill-icon{font-size:14px;color:rgba(255,255,255,.58);width:15px;text-align:center;flex-shrink:0;}
            .bsr-profile-btn{
                display:inline-flex;align-items:center;justify-content:center;
                min-height:38px;padding:8px 16px;border-radius:11px;width:auto;box-sizing:border-box;margin-left:auto;flex:0 0 auto;
                border:1px solid rgba(99,102,241,.6);background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(99,102,241,.07));
                color:#a5b4fc!important;text-decoration:none!important;
                font-size:10px;font-weight:950;white-space:nowrap;letter-spacing:.03em;text-transform:uppercase;
            }
            .bsr-profile-btn:hover{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.9);color:#c7d2fe!important;}
            .bsr-footer{padding:12px 28px 18px;}
            .bsr-any-btn{min-height:42px;border-radius:12px;font-size:13px;}
            .bsr-item-check{top:50%;right:12px;transform:translateY(-50%);}
        }
        @media(min-width:1180px){
            #bsr-panel{width:min(1240px,calc(100vw - 56px));max-width:1240px;}
            .bsr-ph{padding-left:36px;padding-right:36px;}
            .bsr-search-wrap{margin-left:36px;margin-right:36px;}
            .bsr-grid-wrap{padding-left:36px;padding-right:36px;}
            .bsr-footer{padding-left:36px;padding-right:36px;}
            .bsr-item{grid-template-columns:62px minmax(120px,160px) minmax(0,1fr);gap:14px;padding:12px 16px;min-height:74px;}
            .bsr-item-av img{width:54px;height:54px;}
            .bsr-item-name{font-size:14px;}
            .bsr-item-meta{gap:8px;}
            .bsr-rank-pill,.bsr-roles-pill,.bsr-lang-pill,.bsr-time-pill,.bsr-orders-pill,.bsr-rating-pill,.bsr-profile-btn{font-size:11px;min-height:39px;border-radius:12px;}
            .bsr-rank-pill img{width:22px;height:22px;}
            .bsr-role-icons img{width:20px;height:20px;flex:0 0 auto;}
            .bsr-lang-icons img{width:21px;height:21px;}
        }
        @media(min-width:1500px){
            #bsr-panel{width:min(1280px,calc(100vw - 72px));max-width:1280px;}
            .bsr-item{grid-template-columns:62px minmax(130px,175px) minmax(0,1fr);}
            .bsr-item-meta{gap:9px;}
        }

        /* selected checkmark badge */
        .bsr-item-check{
            position:absolute;top:6px;right:6px;width:16px;height:16px;border-radius:50%;
            background:rgba(99,102,241,.95);display:none;align-items:center;justify-content:center;
            font-size:9px;color:#fff;
        }
        .bsr-item.sel .bsr-item-check{display:flex;}

        /* no results */
        .bsr-no-results{
            text-align:center;padding:28px;color:rgba(255,255,255,.3);font-size:13px;display:none;
        }

        /* ── Footer ── */
        .bsr-footer{
            padding:12px 20px max(20px,env(safe-area-inset-bottom));flex-shrink:0;
            border-top:1px solid rgba(255,255,255,.06);
            display:flex;gap:10px;
        }
        .bsr-any-btn{
            flex:1;padding:11px;border-radius:10px;
            border:1px solid rgba(255,255,255,.045)!important;background:rgba(255,255,255,.022)!important;
            color:rgba(255,255,255,.5)!important;font-size:13px;font-weight:600;
            cursor:pointer;transition:.15s;font-family:inherit;
        }
        .bsr-any-btn:hover{border-color:rgba(255,255,255,.09)!important;color:rgba(255,255,255,.8)!important;background:rgba(255,255,255,.04)!important;}
        </style>

        <script>
        (function(){
            var allData  = [];
            var boostersLoaded = false;
            var boostersLoading = false;
            var orderSummaryGame = <?= json_encode($__smGameKey2) ?>;
            var orderSummaryFormId = <?= (int)$__smFormId2 ?>;
            var multiRequest = <?= $__smMultiRequest ? 'true' : 'false' ?>;
            var defaultAv = 'https://lolboost.gg/public/uploads/icons/default.png';
            var L = {
                title:  <?= json_encode(t('Available Boosters')) ?>,
                online: <?= json_encode(t('Available Boosters')) ?>,
                top:    <?= json_encode(t('Top Boosters')) ?>,
                filterAll: <?= json_encode(t('All')) ?>,
                filterOnline: <?= json_encode(t('Online')) ?>,
                filterOffline: <?= json_encode(t('Offline')) ?>,
                srch:   <?= json_encode(t('Search boosters...')) ?>,
                any:    <?= json_encode(t('Any Available')) ?>,
                anyBtn: <?= json_encode(t('Any Available Booster')) ?>,
                none:   <?= json_encode(t('No boosters found')) ?>,
                onlSec: <?= json_encode(t('Online')) ?>,
                offSec: <?= json_encode(t('Offline')) ?>,
            };

            /* ── Build overlay and teleport to <body> ── */
            var overlayEl = document.createElement('div');
            overlayEl.id = 'bsr-overlay';
            overlayEl.innerHTML =
                '<div id="bsr-panel">'+
                    '<div class="bsr-ph">'+
                        '<div>'+
                            '<div class="bsr-ph-title">'+
                                '<span class="bsr-pulse" id="bsr-pulse"></span>'+
                                L.title+
                            '</div>'+
                            '<div class="bsr-filter-pills" id="bsr-filter-pills">'+
                                '<button type="button" class="bsr-filter-pill active" data-filter="all"><span class="bsr-filter-pill-dot"></span>'+L.filterAll+'</button>'+ 
                                '<button type="button" class="bsr-filter-pill" data-filter="online"><span class="bsr-filter-pill-dot"></span>'+L.filterOnline+'</button>'+ 
                                '<button type="button" class="bsr-filter-pill" data-filter="offline"><span class="bsr-filter-pill-dot"></span>'+L.filterOffline+'</button>'+ 
                            '</div>'+
                        '</div>'+
                        '<button type="button" class="bsr-close" id="bsr-close-btn">'+
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>'+
                        '</button>'+
                    '</div>'+
                    '<div class="bsr-search-wrap">'+
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>'+
                        '<input type="text" class="bsr-search-inp" id="bsr-search-inp" placeholder="'+L.srch+'">'+
                        '<button type="button" class="bsr-search-clear" id="bsr-search-clear">&times;</button>'+
                    '</div>'+
                    '<div class="bsr-grid-wrap" id="bsr-grid-wrap">'+
                        '<div id="bsr-grid-online"></div>'+
                        '<div id="bsr-grid-offline"></div>'+
                        '<div class="bsr-no-results" id="bsr-no-results">'+L.none+'</div>'+
                    '</div>'+
                    '<div class="bsr-footer">'+
                        '<button type="button" class="bsr-any-btn" id="bsr-any-btn">'+L.anyBtn+'</button>'+
                    '</div>'+
                '</div>';

            function init(){
                document.body.appendChild(overlayEl);

                var hidInp   = document.getElementById('bsr-hidden');
                var rowEl    = document.getElementById('bsr-row');
                var rowAv    = document.getElementById('bsr-avatar');
                var rowName  = document.getElementById('bsr-name');
                var rowDot   = document.getElementById('bsr-dot');
                var closeBtn = document.getElementById('bsr-close-btn');
                var srchInp  = document.getElementById('bsr-search-inp');
                var srchClr  = document.getElementById('bsr-search-clear');
                var anyBtn   = document.getElementById('bsr-any-btn');
                var noRes    = document.getElementById('bsr-no-results');
                var onlineWrap  = document.getElementById('bsr-grid-online');
                var offlineWrap = document.getElementById('bsr-grid-offline');
                var filterPills = Array.from(document.querySelectorAll('.bsr-filter-pill'));
                var activeFilter = 'all';
                var selId    = '';
                var selectedIds = [];
                var items    = []; // all .bsr-item elements

                function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
                function openModal()  {
                    overlayEl.classList.add('bsr-open');
                    document.body.style.overflow='hidden';
                    loadBoostersOnce();
                }
                function closeModal() { overlayEl.classList.remove('bsr-open'); document.body.style.overflow=''; }

                rowEl.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                anyBtn.addEventListener('click', function(){ selId=''; selectedIds=[]; hidInp.value=''; deselectAll(); updateRow(null); closeModal(); });
                // overlay click disabled \u2013 close via X only
                // escape key disabled \u2013 close via X only

                /* ── Helpers ── */
                function getLocalTime(tz){
                    try { return new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',timeZone:tz}); }
                    catch(e){ return '—'; }
                }
                function updateRow(b){
                    if(multiRequest && selectedIds.length){
                        var first = allData.find(function(item){ return String(item.id)===String(selectedIds[0]); });
                        rowAv.src=(first && first.icon)||defaultAv;
                        rowName.textContent=selectedIds.length+' '+(selectedIds.length===1?'booster requested':'boosters requested');
                        rowDot.className='bsr-dot'+(first && first.online?' on':'');
                        rowEl.classList.add('has-sel');
                        return;
                    }
                    if(!b){
                        rowAv.src=defaultAv; rowName.textContent=L.any;
                        rowDot.className='bsr-dot'; rowEl.classList.remove('has-sel');
                    } else {
                        rowAv.src=b.icon||defaultAv; rowName.textContent=b.name;
                        rowDot.className='bsr-dot'+(b.online?' on':''); rowEl.classList.add('has-sel');
                    }
                }
                function deselectAll(){
                    items.forEach(function(el){ el.classList.remove('sel'); });
                }
                function selectBooster(id, b){
                    if(multiRequest){
                        var stringId=String(id);
                        var pos=selectedIds.indexOf(stringId);
                        if(pos>=0){
                            selectedIds.splice(pos,1);
                        } else {
                            var countInput=document.querySelector('input[name="boosters"], select[name="boosters"]');
                            var maxSelected=Math.max(1,Math.min(4,parseInt(countInput && countInput.value || '1',10)||1));
                            if(selectedIds.length>=maxSelected){
                                if(window.toastr) toastr.warning('You can request up to '+maxSelected+' boosters for this order.');
                                return;
                            }
                            selectedIds.push(stringId);
                        }
                        hidInp.value=selectedIds.join(',');
                        items.forEach(function(el){ el.classList.toggle('sel',selectedIds.indexOf(String(el.dataset.id))>=0); });
                        updateRow(null);
                        return;
                    }
                    selId=String(id); hidInp.value=selId;
                    deselectAll(); updateRow(b);
                    items.forEach(function(el){ if(String(el.dataset.id)===selId) el.classList.add('sel'); });
                    setTimeout(closeModal, 220);
                }

                /* ── Build section ── */
                function buildSection(arr, wrapper, label, showDot){
                    if(!arr.length){ wrapper.style.display='none'; return; }
                    var hd = document.createElement('div');
                    hd.className = 'bsr-sec-hd';
                    hd.innerHTML = (showDot ? '<span class="bsr-sec-hd-dot"></span>' : '') + esc(label) + ' <span style="color:rgba(255,255,255,.5);font-weight:500;">('+arr.length+')</span>';
                    wrapper.appendChild(hd);
                    var grid = document.createElement('div');
                    grid.className = 'bsr-grid';
                    arr.forEach(function(b){
                        var lt = b.tz ? getLocalTime(b.tz) : '';
                        var showRoles = b.game !== 'tft' && b.game !== 'val';
                        var roles = showRoles && Array.isArray(b.roles) ? b.roles : [];
                        var rolesText = showRoles ? (b.roles_text || roles.map(function(r){ return r ? r.charAt(0).toUpperCase()+r.slice(1) : ''; }).filter(Boolean).join(' / ')) : '';
                        var langs = Array.isArray(b.languages) ? b.languages : [];
                        var langsText = b.languages_text || langs.map(function(l){ return l ? l.charAt(0).toUpperCase()+l.slice(1) : ''; }).filter(Boolean).join(' / ');
                        var roleIcons = roles.map(function(r){
                            return '<img src="<?= ASSET_URL ?>/core/main/img/lol/roles/'+esc(r)+'.png" alt="'+esc(r)+'" title="'+esc(r ? r.charAt(0).toUpperCase()+r.slice(1) : '')+'">';
                        }).join('');
                        var langIcons = langs.slice(0, 3).map(function(l){
                            return '<img src="<?= ASSET_URL ?>/core/main/img/languages/'+esc(l)+'.png" alt="'+esc(l)+'" title="'+esc(l ? l.charAt(0).toUpperCase()+l.slice(1) : '')+'" onerror="this.style.display=\'none\'">';
                        }).join('');
                        if (langs.length > 3) { langIcons += '<span class="bsr-pill-icon" title="'+esc(langsText)+'">+'+esc(String(langs.length - 3))+'</span>'; }
                        var rankHtml = b.rank ? '<span class="bsr-rank-pill" title="'+esc(b.rank)+'">'+(b.rank_img ? '<img src="'+esc(b.rank_img)+'" alt="" onerror="this.style.display=\'none\'">' : '')+'<span class="bsr-rank-label">'+esc(b.rank)+'</span></span>' : '';
                        var rolesHtml = showRoles ? (roleIcons ? '<span class="bsr-roles-pill" title="'+esc(rolesText)+'"><span class="bsr-role-icons">'+roleIcons+'</span><span class="bsr-roles-label">'+esc(rolesText)+'</span></span>' : '') : '';
                        var langHtml = langIcons ? '<span class="bsr-lang-pill" title="'+esc(langsText)+'"><span class="bsr-lang-icons">'+langIcons+'</span></span>' : '<span class="bsr-lang-pill" title="No languages">—</span>';
                        var ordersHtml = '<span class="bsr-orders-pill" title="Completed Orders"><i class="fa-duotone fa-medal bsr-pill-icon"></i>'+esc(String(b.completed || 0))+' orders</span>';
                        var ratingValRaw = (b.rating !== undefined && b.rating !== null && b.rating !== '') ? b.rating : 5.0;
                        var ratingNum = parseFloat(ratingValRaw);
                        var ratingVal = isNaN(ratingNum) ? '5.0' : ratingNum.toFixed(1);
                        var ratingHtml = '<span class="bsr-rating-pill" title="Rating"><i class="fa-solid fa-star bsr-pill-icon"></i>'+esc(ratingVal)+'</span>';
                        var timeHtml = lt ? '<span class="bsr-time-pill" title="Local time"><i class="fa-duotone fa-clock bsr-pill-icon"></i>'+esc(lt)+'</span>' : '';
                        var profileHtml = '<a class="bsr-profile-btn" href="'+esc(b.profile || ('<?= BASE_URL ?>/boosters/'+b.id))+'" target="_blank" rel="noopener">View Profile</a>';
                        var el = document.createElement('div');
                        el.className = 'bsr-item';
                        el.dataset.id = b.id;
                        el.dataset.name = ((b.name||'')+' '+(b.rank||'')+' '+rolesText+' '+langsText).toLowerCase();
                        el.dataset.status = b.online ? 'online' : 'offline';
                        el.innerHTML =
                            '<div class="bsr-item-check">&#10003;</div>'+
                            '<div class="bsr-item-av">'+
                                '<img src="'+esc(b.icon||defaultAv)+'" alt="">'+
                                '<span class="bsr-item-dot'+(b.online?' on':'')+'"></span>'+
                            '</div>'+
                            '<div class="bsr-item-name" title="'+esc(b.name)+'">'+esc(b.name)+'</div>'+
                            '<div class="bsr-item-meta">'+rankHtml+rolesHtml+langHtml+ordersHtml+ratingHtml+timeHtml+profileHtml+'</div>';
                        var profileLink = el.querySelector('.bsr-profile-btn');
                        if (profileLink) {
                            profileLink.addEventListener('click', function(ev){ ev.stopPropagation(); });
                        }
                        el.addEventListener('click', function(){
                            if(!multiRequest && el.classList.contains('sel')){ selId=''; hidInp.value=''; deselectAll(); updateRow(null); return; }
                            selectBooster(b.id, b);
                        });
                        grid.appendChild(el);
                        items.push(el);
                    });
                    wrapper.appendChild(grid);
                }

                function renderBoosters(){
                    items = [];
                    onlineWrap.innerHTML = '';
                    offlineWrap.innerHTML = '';
                    onlineWrap.style.display = '';
                    offlineWrap.style.display = '';
                    noRes.style.display = 'none';

                    var onlineBoosters  = allData.filter(function(b){ return  b.online; });
                    var offlineBoosters = allData.filter(function(b){ return !b.online; });
                    buildSection(onlineBoosters,  onlineWrap,  L.onlSec, true);
                    buildSection(offlineBoosters, offlineWrap, L.offSec, false);

                    filterPills.forEach(function(btn){
                        var baseLabel = btn.getAttribute('data-base-label');
                        if (!baseLabel) {
                            baseLabel = btn.textContent.replace(/\s*\(\d+\)\s*$/, '');
                            btn.setAttribute('data-base-label', baseLabel);
                        }
                        var count = allData.length;
                        if (btn.dataset.filter === 'online') count = onlineBoosters.length;
                        if (btn.dataset.filter === 'offline') count = offlineBoosters.length;
                        btn.innerHTML = '<span class="bsr-filter-pill-dot"></span>' + esc(baseLabel) + ' (' + count + ')';
                    });

                    filterItems(srchInp.value || '');
                }

                function loadBoostersOnce(){
                    if (boostersLoaded || boostersLoading) return;
                    boostersLoading = true;
                    noRes.style.display = 'block';
                    noRes.textContent = 'Loading boosters...';

                    var fd = new FormData();
                    fd.append('action', 'load_order_summary_boosters');
                    fd.append('game', orderSummaryGame);
                    fd.append('form_id', orderSummaryFormId);

                    fetch('<?= BASE_URL ?>/ajax', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    }).then(function(r){ return r.json(); })
                      .then(function(res){
                          allData = (res && res.success && Array.isArray(res.boosters)) ? res.boosters : [];
                          boostersLoaded = true;
                          boostersLoading = false;
                          noRes.textContent = L.none;
                          renderBoosters();
                      }).catch(function(){
                          boostersLoading = false;
                          noRes.textContent = L.none;
                      });
                }

                /* ── Search / filter ── */
                function filterItems(q){
                    var any = false;
                    q = (q || '').toLowerCase();
                    items.forEach(function(el){
                        var matchesSearch = !q || (el.dataset.name||'').includes(q);
                        var matchesStatus = activeFilter === 'all' || (el.dataset.status || '') === activeFilter;
                        var m = matchesSearch && matchesStatus;
                        el.classList.toggle('hidden', !m);
                        if(m) any=true;
                    });
                    noRes.style.display = any ? 'none' : 'block';
                    [onlineWrap, offlineWrap].forEach(function(w){
                        var vis = Array.from(w.querySelectorAll('.bsr-item')).some(function(e){ return !e.classList.contains('hidden'); });
                        w.style.display = vis ? '' : 'none';
                    });
                }
                filterPills.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        activeFilter = btn.dataset.filter || 'all';
                        filterPills.forEach(function(b){ b.classList.toggle('active', b === btn); });
                        filterItems(srchInp.value);
                    });
                });
                srchInp.addEventListener('input', function(){
                    var q = this.value;
                    srchClr.classList.toggle('vis', q.length > 0);
                    filterItems(q);
                });
                srchClr.addEventListener('click', function(){
                    srchInp.value=''; srchClr.classList.remove('vis'); filterItems('');
                });
                filterItems('');
            }

            if(document.readyState==='loading'){
                document.addEventListener('DOMContentLoaded', init);
            } else { init(); }
        })();
        </script>

        <?php endif; // !$__smHide ?>



        <div class="completion-box" id="hide-sticky" style="margin-top:16px;">
            <img src="<?= ASSET_URL ?>/website/images/boost-forms/estimate-clock.svg" alt="completion_icon">
            <span class="text"><?= t('Completion Time:') ?> <span id="completion-time"> <?= t('~ 0') ?></span>
            </span>
        </div>


        <?php
                $lbRewardCoupons = [];
                if (defined('CLIENT_DATA') && CLIENT_DATA != false && !empty(CLIENT_DATA['id'])) {
                    try {
                        global $db;
                        $lbRewardCoupons = $db->run(
                            "SELECT cr.id, cr.coupon_code, cr.reward_value, cr.expires_at, rb.name AS box_name
                             FROM client_rewards cr
                             LEFT JOIN reward_boxes rb ON rb.id = cr.box_id
                             WHERE cr.client_id = ?
                               AND cr.reward_type = 'discount_coupon'
                               AND cr.status = 'unused'
                               AND cr.coupon_code IS NOT NULL
                               AND cr.coupon_code <> ''
                               AND (cr.expires_at IS NULL OR cr.expires_at > NOW())
                             ORDER BY cr.created_at DESC
                             LIMIT 12",
                            (int)CLIENT_DATA['id']
                        ) ?: [];
                    } catch (\Throwable $e) {
                        $lbRewardCoupons = [];
                    }
                }
                ?>
                <style>
                .lb-coupon-chooser-open #discount-input{display:block!important;visibility:visible!important;opacity:1!important;height:auto!important;overflow:visible!important;}
                .discount-input.lb-coupon-open{display:block!important;padding:14px!important;border:1px solid rgba(109,92,255,.32)!important;background:linear-gradient(180deg,rgba(109,92,255,.10),rgba(255,255,255,.025))!important;border-radius:16px!important;box-shadow:0 16px 40px rgba(0,0,0,.28),0 0 0 1px rgba(255,255,255,.03) inset!important;}
                .discount-input .lb-coupon-row{display:flex;gap:10px;align-items:center;width:100%;}
                .discount-input .lb-coupon-row input{flex:1;min-width:0;}
                .lb-coupon-apply-btn{min-height:42px;border:0;border-radius:12px;padding:0 16px;background:linear-gradient(135deg,#6d8cff,#7c4dff);color:#fff;font-weight:950;white-space:nowrap;box-shadow:0 10px 24px rgba(109,92,255,.28);}
                .lb-coupon-panel{display:none;margin-top:12px;border-top:1px solid rgba(255,255,255,.08);padding-top:12px;}
                .lb-coupon-panel.is-open{display:block;}
                .lb-coupon-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;}
                .lb-coupon-title{font-weight:950;color:rgba(255,255,255,.92);font-size:.92rem;line-height:1.2;}
                .lb-coupon-sub{font-size:.78rem;color:rgba(255,255,255,.55);font-weight:750;margin-top:2px;}
                .lb-coupon-close{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.72);border-radius:10px;width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;}
                .lb-coupon-list{display:grid;gap:8px;max-height:340px;overflow:auto;padding-right:2px;}
                .lb-coupon-list::-webkit-scrollbar{width:7px;}
                .lb-coupon-list::-webkit-scrollbar-thumb{background:rgba(143,178,255,.24);border-radius:999px;}
                .lb-coupon-choice{width:100%;border:1px solid rgba(255,255,255,.09);background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025));border-radius:14px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;gap:10px;color:#fff;text-align:left;cursor:pointer;transition:.16s ease;box-shadow:0 10px 22px rgba(0,0,0,.16);}
                .lb-coupon-choice:hover{border-color:rgba(143,178,255,.45);background:linear-gradient(180deg,rgba(109,140,255,.13),rgba(255,255,255,.035));transform:translateY(-1px);}
                .lb-coupon-choice-main{display:flex;align-items:center;gap:11px;min-width:0;flex:1;}
                .lb-coupon-icon{width:36px;height:36px;border-radius:11px;background:rgba(109,92,255,.12);border:1px solid rgba(143,178,255,.14);display:flex;align-items:center;justify-content:center;color:#9db7ff;flex:0 0 auto;font-size:.88rem;}
                .lb-coupon-choice-main > span:last-child{min-width:0;display:block;}
                .lb-coupon-code{display:block;font-weight:950;color:rgba(255,255,255,.94);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:210px;font-size:.86rem;line-height:1.1;}
                .lb-coupon-meta{display:block;font-size:.68rem;color:rgba(255,255,255,.48);font-weight:800;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:225px;}
                .lb-coupon-badge{border-radius:999px;padding:5px 8px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:#8cf0b3;font-weight:950;font-size:.72rem;white-space:nowrap;box-shadow:none;}
                .lb-coupon-empty{border:1px dashed rgba(255,255,255,.12);background:rgba(255,255,255,.025);border-radius:14px;padding:12px;color:rgba(255,255,255,.58);font-weight:750;font-size:.82rem;}
                html body .lol-boost .form-content .discount-input.lb-coupon-open button.lb-coupon-choice,
                html body .form-content .discount-input.lb-coupon-open button.lb-coupon-choice,
                html body .discount-input.lb-coupon-open button.lb-coupon-choice{background:#25282d!important;background-image:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025))!important;border:1px solid rgba(255,255,255,.10)!important;border-radius:14px!important;color:#fff!important;padding:10px 12px!important;font-size:inherit!important;font-weight:inherit!important;white-space:normal!important;}
                html body .lol-boost .form-content .discount-input.lb-coupon-open button.lb-coupon-choice:hover,
                html body .form-content .discount-input.lb-coupon-open button.lb-coupon-choice:hover,
                html body .discount-input.lb-coupon-open button.lb-coupon-choice:hover{background:#2b2f38!important;background-image:linear-gradient(180deg,rgba(109,140,255,.14),rgba(255,255,255,.035))!important;border-color:rgba(143,178,255,.45)!important;}
                html body .lol-boost .form-content .discount-input.lb-coupon-open button.lb-coupon-choice .lb-coupon-badge,
                html body .form-content .discount-input.lb-coupon-open button.lb-coupon-choice .lb-coupon-badge,
                html body .discount-input.lb-coupon-open button.lb-coupon-choice .lb-coupon-badge{background:#243d36!important;background-image:none!important;border:1px solid rgba(34,197,94,.30)!important;color:#8cf0b3!important;}
                @media(max-width:520px){.discount-input .lb-coupon-row{flex-direction:column;align-items:stretch}.lb-coupon-code{max-width:175px}.lb-coupon-meta{max-width:185px}.lb-coupon-choice{align-items:center}.lb-coupon-badge{margin-top:0}}
                </style>
                <script>
                window.LB_CLIENT_REWARD_COUPONS = <?= json_encode(array_map(function($row){
                    return [
                        'code' => (string)($row['coupon_code'] ?? ''),
                        'amount' => (float)($row['reward_value'] ?? 0),
                        'expires_at' => (string)($row['expires_at'] ?? ''),
                        'box_name' => (string)($row['box_name'] ?? 'Reward Box'),
                    ];
                }, $lbRewardCoupons), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;
                </script>

        <?php $hideSummaryDiscount = in_array((string)($data['type'] ?? ''), ['pro-games', 'duo-pass'], true); ?>
        <?php if (!$hideSummaryDiscount): ?>
        <div class="discount-box" id="discount-box">
            <button class="remove-btn" id="remove-discount"><?= t('⨯') ?></button>
            <div class="left">
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/discount1.svg" alt="discount_icon">
                <div class="text">
                    <h5 id="discount-message"><?= t('20% Discount Applied!') ?></h5>
                    <p><?= t('Special promotion active') ?></p>
                </div>
            </div>
            <div class="right">
                <h5><?= t('Original Price') ?></h5>
                <div class="amounts">
                    <span class="old" id="old-price"><?= t('€0.00') ?></span>
                    <span class="new total-price" id="new-price"><?= t('€0.00') ?></span>
                </div>
               <h5 class="saved">
                    <?= t('You save') ?> <span id="saved-price"><?= t('€0.00') ?></span>
                </h5>
            </div>
        </div>

        <div class="discount-input" id="discount-input">
            <div class="lb-coupon-row">
                <input type="text" placeholder="Enter Discount Code" name="discount_code" id="discount_code" autocomplete="off">
                <button type="button" class="lb-coupon-apply-btn" id="lbApplyCouponBtn"><?= t('Apply') ?></button>
            </div>
            <div class="lb-coupon-panel" id="lbCouponPanel">
                <div class="lb-coupon-head">
                    <div>
                        <div class="lb-coupon-title"><?= t('Use a reward coupon') ?></div>
                        <div class="lb-coupon-sub"><?= t('Choose one of your unused reward coupons or enter your own code.') ?></div>
                    </div>
                    <button type="button" class="lb-coupon-close" id="lbCouponClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php if (!empty($lbRewardCoupons)): ?>
                    <div class="lb-coupon-list">
                        <?php foreach ($lbRewardCoupons as $coupon):
                            $couponCode = (string)($coupon['coupon_code'] ?? '');
                            if ($couponCode === '') continue;
                            $couponAmount = (float)($coupon['reward_value'] ?? 0);
                            $couponExpires = !empty($coupon['expires_at']) ? strtotime((string)$coupon['expires_at']) : false;
                        ?>
                        <button type="button" class="lb-coupon-choice" data-coupon-code="<?= htmlspecialchars($couponCode, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="lb-coupon-choice-main">
                                <span class="lb-coupon-icon"><i class="fa-duotone fa-ticket"></i></span>
                                <span>
                                    <span class="lb-coupon-code"><?= htmlspecialchars($couponCode, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="lb-coupon-meta"><?= htmlspecialchars((string)($coupon['box_name'] ?? 'Reward Box'), ENT_QUOTES, 'UTF-8') ?><?php if ($couponExpires): ?> · <?= t('expires') ?> <?= date('d.m.Y', $couponExpires) ?><?php endif; ?></span>
                                </span>
                            </span>
                            <span class="lb-coupon-badge">+<?= rtrim(rtrim(number_format($couponAmount, 2, '.', ''), '0'), '.') ?>%</span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="lb-coupon-empty"><?= t('No unused reward coupons yet. You can still enter your own code.') ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

<script>
(function(){
  if (window.__lbCouponChooserBound) return;
  window.__lbCouponChooserBound = true;
  function el(id){ return document.getElementById(id); }
  function dispatchCouponInput(input){
    ['input','change','keyup'].forEach(function(type){
      try { input.dispatchEvent(new Event(type, {bubbles:true})); } catch(e) {}
    });
    if (window.jQuery) { try { window.jQuery(input).trigger('input').trigger('change').trigger('keyup'); } catch(e) {} }
  }
  function openChooser(){
    var wrap = el('discount-input');
    var panel = el('lbCouponPanel');
    var input = el('discount_code');
    if (!wrap || !input) return;
    document.body.classList.add('lb-coupon-chooser-open');
    wrap.classList.add('lb-coupon-open');
    wrap.style.display = 'block';
    if (panel) panel.classList.add('is-open');
    setTimeout(function(){ input.focus(); if (typeof input.select === 'function') input.select(); }, 30);
  }
  function closeChooser(){
    var wrap = el('discount-input');
    var panel = el('lbCouponPanel');
    document.body.classList.remove('lb-coupon-chooser-open');
    if (wrap) wrap.classList.remove('lb-coupon-open');
    if (panel) panel.classList.remove('is-open');
  }
  function applyCoupon(code){
    var input = el('discount_code');
    if (!input || !code) return;
    input.value = String(code).trim().toUpperCase();
    dispatchCouponInput(input);
    setTimeout(function(){ dispatchCouponInput(input); }, 80);
    setTimeout(closeChooser, 240);
  }
  window.lbOpenCouponChooser = openChooser;
  window.lbApplyCouponCode = applyCoupon;
  document.addEventListener('click', function(e){
    var remove = e.target && e.target.closest ? e.target.closest('#remove-discount') : null;
    if (remove) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      openChooser();
      return false;
    }
    var choice = e.target && e.target.closest ? e.target.closest('.lb-coupon-choice') : null;
    if (choice) {
      e.preventDefault();
      applyCoupon(choice.getAttribute('data-coupon-code') || '');
      return;
    }
    var apply = e.target && e.target.closest ? e.target.closest('#lbApplyCouponBtn') : null;
    if (apply) {
      e.preventDefault();
      var input = el('discount_code');
      applyCoupon(input ? input.value : '');
      return;
    }
    var close = e.target && e.target.closest ? e.target.closest('#lbCouponClose') : null;
    if (close) {
      e.preventDefault();
      closeChooser();
    }
  }, true);
})();
</script>


        <hr>

        <?php
        $cashback_percent = 2;

        if (defined('CLIENT_DATA') && CLIENT_DATA != false && !empty(CLIENT_DATA['loyalty_rank_id'])) {
            $cashback_rank = db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']], 1);
            if (!empty($cashback_rank['cashback']) && is_numeric($cashback_rank['cashback'])) {
                $cashback_percent = (float)$cashback_rank['cashback'];
            }
        }
        ?>

        <div class="totals">
            <p>
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/total.svg"
                    alt="total_icon"><?= t('Total Price') ?>
            </p>

            <span class="price total-price" id="total-price"><?= t('€0.00') ?></span>
        </div>


        <div class="cashback_info" data-cashback-percent="<?= htmlspecialchars((string)$cashback_percent, ENT_QUOTES, 'UTF-8') ?>">
            <p>
                <img src="https://lolboost.gg/public/assets/core/main/img/coin.png" alt="LB Coins">
                <?= t('Cashback') ?> <small>(<?= rtrim(rtrim(number_format($cashback_percent, 2, '.', ''), '0'), '.') ?>%)</small>
            </p>
            <span id="cashback_amount"><?= t('€0.00') ?></span>
        </div>


        <button type="submit" class="btn primary buy-now" id="start_boost"><?= t('Buy Now') ?></button>

        <div class="trust-badges-summary">
            <div class="lb-shield-trigger" tabindex="0" aria-label="<?= t('Your payment is safe with LOLBOOST.GG Shield') ?>">
                <i class="fa-duotone fa-shield-check" aria-hidden="true"></i>
                <span><?= t('Your payment is safe with LOLBOOST.GG Shield') ?></span>
                <i class="fa-solid fa-chevron-down lb-shield-trigger__chev" aria-hidden="true"></i>

                <div class="lb-shield-tooltip" role="tooltip">
                    <div class="lb-shield-tooltip__brand">
                        <i class="fa-duotone fa-shield-check" aria-hidden="true"></i>
                        <span><?= t('LOLBOOST.GG Shield') ?></span>
                    </div>
                    <div class="lb-shield-tooltip__title"><?= t('Your payment stays safe until the service is completed.') ?></div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Secure payment & refund') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Refunded if the service cannot be delivered.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-user-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Verified boosters') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Performance, identity and trust checks.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('Private & encrypted') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Secure checkout and encrypted payment handling.') ?></span>
                        </span>
                    </div>

                    <div class="lb-shield-tooltip__item">
                        <span class="lb-shield-tooltip__itemIcon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>
                        <span>
                            <span class="lb-shield-tooltip__itemTitle"><?= t('24/7 support') ?></span>
                            <span class="lb-shield-tooltip__itemText"><?= t('Live chat and Discord support around the clock.') ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <a href="https://www.trustpilot.com/review/lolboost.gg" target="_blank" rel="noopener noreferrer" class="trustpilot-banner trustpilot-banner--chip" aria-label="Trustpilot reviews">
                <span class="tpBadge tpBadge--summary">
                    <span class="tpBadge__excellent"><?= t('Excellent') ?></span>
                    <span class="tpBadge__stars" aria-hidden="true">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </span>
                    <span class="tpBadge__reviews"><?= t('510 Reviews on') ?></span>
                    <span class="tpBadge__tpIcon" aria-hidden="true">★</span>
                </span>
            </a>
        </div>
    </div>

    <div class="payment-gateways">
        <div class="top">
            <img src="<?= ASSET_URL ?>/website/images/boost-forms/secure-payments.svg" alt="secure-payments-icon">

            <div class="text">
                <h5><?= t('Safe & Secure Payments') ?></h5>
                <p><?= t('100% secure checkout powered by Stripe & Paypal') ?></p>
            </div>
        </div>

        <img src="<?= ASSET_URL ?>/website/images/boost-forms/gateways.png" alt="gateway-logos">
    </div>

    <?php if (($data['type'] ?? '') === 'ggirls'): ?>
    <style>
    /* FAQ on the GGirls page should otherwise look exactly like on
       every other boost form — no custom theming here, just an
       explicit background color so it's consistent with the rest of
       the GGirls page. .accordion stays transparent so it shows
       through to the same color instead of creating a visible
       "box inside a box" seam. Covers both the desktop FAQ block
       (.boost-faqs) and the separate mobile one (.boost-faqs-mobile). */
    .boost-faqs,
    .boost-faqs-mobile{background:#1f0a3a!important}
    .boost-faqs .accordion,
    .boost-faqs-mobile .accordion{background:transparent!important}

    /* Sticky bottom bar (mobile) — re-skinned to match. We don't have
       this component's markup, so this only recolors the container;
       its own "Buy Now" button already picks up the .buy-now rule
       defined above if it reuses that class. */
    .sticky-overview{
        background:linear-gradient(165deg,#150726 0%,#1f0a3a 100%)!important;
        border-top:1px solid rgba(168,85,247,.25)!important;
    }

    /* Width safety net — keep every GGirls section inside the phone
       screen, no matter what's causing things to stick out. */
    @media(max-width:767px){
        html,body{overflow-x:hidden!important;max-width:100%!important;}
        .ggirl-boost-form,
        .order-summary,
        .summary-wrapper,
        .boost-faqs,
        .boost-faqs-mobile,
        .sticky-overview,
        .payment-gateways{
            max-width:100%!important;
            width:100%!important;
            box-sizing:border-box!important;
            overflow-x:hidden!important;
        }
    }
    </style>
    <?php endif; ?>
</div>
<script>
(function(){
    var rankNames = <?= json_encode($isClassicSummary ? ['Unranked','Salt','Wood','Silver','Gold','Platinum','Diamond','Legend'] : ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger']) ?>;
    var rankBase = '<?= $isClassicSummary ? ASSET_URL . '/website/images/lol-classic/ranks/' : ASSET_URL . '/core/main/img/lol/ranks/mini/' ?>';
    function rankImgFile(rank){
        if (!<?= $isClassicSummary ? 'true' : 'false' ?>) return rank + '.png';
        var names = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[rank] || 'salt') + '.webp';
    }
    function updateCoachingCurrentRankSummary(){
        var checked = document.querySelector('input[name="current_rank"]:checked');
        var target = document.getElementById('coaching-current-rank-summary');
        var img = document.getElementById('coaching-current-rank-img');
        if (!checked || !target) return;
        var rank = parseInt(checked.value, 10);
        if (isNaN(rank)) rank = 3;
        target.textContent = rankNames[rank] || checked.value;
        if (img) img.src = rankBase + rankImgFile(rank);
    }
    document.addEventListener('change', function(e){
        if (e.target && e.target.matches('input[name="current_rank"]')) {
            updateCoachingCurrentRankSummary();
        }
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateCoachingCurrentRankSummary);
    } else {
        updateCoachingCurrentRankSummary();
    }
})();
</script>



<?php if ($isClassicSummary && in_array(($data['type'] ?? ''), ['rank','win','placement','match','pro-games','duo-pass','ranked-5s'], true)): ?>
<script>
(function(){
    var base = '<?= ASSET_URL ?>/website/images/lol-classic/ranks/';
    var names = {0:'Unranked',1:'Salt',2:'Wood',3:'Silver',4:'Gold',5:'Platinum',6:'Diamond',7:'Legend'};
    function file(tier, division){
        tier = parseInt(tier || 0, 10);
        var slugs = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (slugs[tier] || 'salt') + '.webp';
    }
    function label(tier, division){
        tier = parseInt(tier || 0, 10);
        division = parseInt(division || 4, 10);
        var divs = {4:'IV',3:'III',2:'II',1:'I'};
        if (tier === 0 || tier === 7) return names[tier] || 'Unranked';
        return (names[tier] || 'Salt') + ' ' + (divs[division] || 'IV');
    }
    function checked(name, fallback){
        var input = document.querySelector('input[name="' + name + '"]:checked');
        return input ? input.value : fallback;
    }
    function classicSummarySync(){
        var startTier = checked('start_tier', <?= (int)$summaryDefaultTier ?>);
        var startDivision = checked('start_division', <?= (int)$summaryDefaultDivision ?>);
        var endTier = checked('end_tier', <?= (int)$summaryDesiredTier ?>);
        var endDivision = checked('end_division', <?= (int)$summaryDesiredDivision ?>);
        var startSrc = base + file(startTier, startDivision);
        var endSrc = base + file(endTier, endDivision);
        document.querySelectorAll('.current-summary-rank-img:not(#coaching-current-rank-img)').forEach(function(img){ img.src = startSrc; });
        document.querySelectorAll('.current-summary-rank-name').forEach(function(el){ el.textContent = label(startTier, startDivision); });
        document.querySelectorAll('.desired-summary-rank-img').forEach(function(img){ img.src = endSrc; });
        document.querySelectorAll('.desired-summary-rank-name').forEach(function(el){ el.textContent = label(endTier, endDivision); });

        var type = <?= json_encode((string)($data['type'] ?? '')) ?>;
        var startLpInput = document.getElementById('start_lp_input');
        var endLpInput = document.getElementById('end_lp_input');
        var startLp = startLpInput ? parseInt(startLpInput.value || '0', 10) : 0;
        var endLp = endLpInput ? parseInt(endLpInput.value || '0', 10) : 0;
        if (!Number.isFinite(startLp)) startLp = 0;
        if (!Number.isFinite(endLp)) endLp = 0;

        document.querySelectorAll('.current-summary-lp:not(.r5s-current-summary-lp)').forEach(function(el){
            if (type === 'win') {
                if (parseInt(startTier, 10) === 7) {
                    el.textContent = '[ ' + startLp + ' LP ]';
                    el.style.display = '';
                } else {
                    el.textContent = '';
                    el.style.display = 'none';
                }
            }
        });
        document.querySelectorAll('.desired-summary-lp').forEach(function(el){
            if (type === 'rank' && parseInt(endTier, 10) === 7) {
                el.textContent = '[ ' + endLp + ' LP ]';
                el.style.display = '';
            } else {
                el.textContent = '';
                el.style.display = 'none';
            }
        });
    }
    ['change','click','input'].forEach(function(evt){
        document.addEventListener(evt, function(e){
            if (e.target && e.target.matches('input[name="start_tier"],input[name="start_division"],input[name="end_tier"],input[name="end_division"],#start_lp_input,#end_lp_input')) {
                requestAnimationFrame(classicSummarySync);
            }
        }, true);
    });
    document.addEventListener('DOMContentLoaded', classicSummarySync);
    setTimeout(classicSummarySync, 150);
    setTimeout(classicSummarySync, 700);
})();
</script>
<?php endif; ?>
<script>
(function () {
    function parsePrice(text) {
        return parseFloat(String(text).replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
    }

    function updateCashback() {
        var box = document.querySelector('.cashback_info');
        var total = document.getElementById('total-price');
        var amount = document.getElementById('cashback_amount');

        if (!box || !total || !amount) return;

        var percent = parseFloat(box.getAttribute('data-cashback-percent')) || 2;
        var price = parsePrice(total.textContent);
        var cashback = price * percent / 100;

        var currencyMatch = total.textContent.match(/[€$£]/);
        var currency = currencyMatch ? currencyMatch[0] : '€';

        amount.textContent = currency + cashback.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateCashback();

        var total = document.getElementById('total-price');
        if (total) {
            new MutationObserver(updateCashback).observe(total, {
                childList: true,
                characterData: true,
                subtree: true
            });
        }
    });
})();
</script>

<?php if (($data['type'] ?? '') === 'ggirls'): ?>
<script>
(function(){
    function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
    ready(function(){
        var row = document.getElementById('ggl-summary-row');
        var overlay = document.getElementById('ggl-summary-overlay');
        var close = document.getElementById('ggl-summary-close');
        var list = document.getElementById('ggl-summary-list');
        var source = document.getElementById('ggl_girls_source');
        var hidden = document.getElementById('selected_egirl_id');
        var assignmentHidden = document.getElementById('ggirl_assignment');
        var dot = document.getElementById('ggl-picker-dot');
        var avatarEl = document.getElementById('ggl-summary-selected-avatar');
        var defaultAvatar = avatarEl ? avatarEl.getAttribute('src') : '';
        if(!row || !overlay || !list || !source) return;

        function openModal(){
            if(overlay.parentNode !== document.body){ document.body.appendChild(overlay); }
            refreshModalServer();
            overlay.classList.add('open');
            document.documentElement.classList.add('ggl-modal-open');
            document.body.classList.add('ggl-modal-open');
        }
        function closeModal(){
            overlay.classList.remove('open');
            document.documentElement.classList.remove('ggl-modal-open');
            document.body.classList.remove('ggl-modal-open');
        }

        function esc(value){
            return String(value || '').replace(/[&<>"']/g, function(ch){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
            });
        }
        function currentServerLabel(){
            var el = document.querySelector('#ggl_server_trigger .ggl-drop-text');
            return el ? el.textContent.trim() : 'EUW';
        }
        function refreshModalServer(){
            /* server badges were removed from the GGirl cards, kept as a no-op for compatibility */
        }
        var langFlagMap = {
            en:'en.png',de:'de.png',fr:'fr.png',es:'es.png',tr:'tr.png',pt:'pt.png',it:'it.png',pl:'pl.png',ru:'ru.webp',nl:'nl.png',
            sv:'sv.png',da:'da.webp',no:'no.webp',fi:'fi.webp',cs:'cz.webp',ro:'ro.png',hu:'hu.webp',uk:'uk.png',ar:'ar.png',zh:'chinese.png',
            ja:'ja.webp',ko:'ko.png',el:'el.png',hr:'hr.png',bg:'bg.webp',vn:'vn.webp',ph:'ph.webp',th:'th.webp'
        };
        var langBase = <?= json_encode(ASSET_URL . '/core/main/img/languages/') ?>;
        var lolIcon = <?= json_encode(ASSET_URL . '/website/images/icons/league-of-legends.png') ?>;
        function flagHtml(code){
            code = String(code || '').trim().toLowerCase();
            if(!code || code.indexOf('/') !== -1 || !langFlagMap[code]) return '';
            return '<img src="'+esc(langBase + langFlagMap[code])+'" alt="'+esc(code.toUpperCase())+'" title="'+esc(code.toUpperCase())+'">';
        }

        function pick(card){
            list.querySelectorAll('.ggl-summary-girl').forEach(function(x){x.classList.remove('active');});
            card.classList.add('active');
            var isAny = card.classList.contains('ggl-summary-any');
            var id = isAny ? '' : (card.getAttribute('data-id') || '');
            var name = card.getAttribute('data-name') || '';
            var avatar = card.getAttribute('data-avatar') || defaultAvatar;
            if(hidden) hidden.value = id;
            if(assignmentHidden) assignmentHidden.value = isAny ? 'any_available' : 'selected';
            var n = document.getElementById('ggl-summary-selected-name'); if(n) n.textContent = name;
            if(avatarEl) avatarEl.src = avatar;
            var sumA = document.getElementById('ggl-summary-avatar'); if(sumA) sumA.src = avatar;
            if(dot) dot.classList.toggle('on', !isAny);
            source.querySelectorAll('.ggl-girl').forEach(function(x){x.classList.remove('active');});
            source.querySelectorAll('input[name="egirl_choice"]').forEach(function(r){ r.checked = false; });
            if(!isAny){
                var srcRadio = source.querySelector('input[value="'+id+'"]');
                if(srcRadio){ srcRadio.checked = true; var srcCard = srcRadio.closest('.ggl-girl'); if(srcCard) srcCard.classList.add('active'); }
            }
            window.dispatchEvent(new Event('ggirls-summary-select'));
            var amount = document.getElementById('ggirl_amount'); if(amount) amount.dispatchEvent(new Event('input'));
        }

        var anyLabel = <?= json_encode(t('Any Available')) ?>;
        var anyDesc = <?= json_encode(t('We pick a great GGirl for you')) ?>;

        /* "Any Available" — the default, random-assignment option, always first */
        var anyCard = document.createElement('div');
        anyCard.className = 'ggl-summary-girl ggl-summary-any active';
        anyCard.setAttribute('data-id', '');
        anyCard.setAttribute('data-name', anyLabel);
        anyCard.setAttribute('data-avatar', defaultAvatar);
        anyCard.innerHTML = '<span class="ggl-summary-any-icon"><i class="fa-solid fa-shuffle"></i></span><span class="ggl-summary-info"><span class="ggl-summary-head"><strong></strong><small></small></span><span class="ggl-summary-meta"><span class="ggl-summary-pill"><i class="fa-solid fa-wand-magic-sparkles"></i><?= t('Best Match') ?></span></span></span>';
        anyCard.querySelector('strong').textContent = anyLabel;
        anyCard.querySelector('small').textContent = anyDesc;
        anyCard.addEventListener('click', function(){ pick(anyCard); closeModal(); });
        list.appendChild(anyCard);

        source.querySelectorAll('.ggl-girl').forEach(function(src){
            var radio = src.querySelector('input[name="egirl_choice"]');
            var name = src.getAttribute('data-name') || '';
            var avatar = src.getAttribute('data-avatar') || '';
            var meta = src.querySelector('small') ? src.querySelector('small').textContent : '';
            var game = src.getAttribute('data-game') || 'League of Legends';
            var rank = src.getAttribute('data-rank') || 'Unranked';
            var timezone = src.getAttribute('data-timezone') || '';
            var languages = src.getAttribute('data-languages') || '';
            var langCodes = Array.prototype.map.call(src.querySelectorAll('.ggl-girl-tags span'), function(x){ return x.textContent.trim(); })
                .filter(function(x){ return x && x.indexOf('/') === -1 && x.length <= 3; })
                .slice(0, 4);
            var flags = langCodes.map(flagHtml).filter(Boolean).join('');

            var card = document.createElement('div');
            card.className = 'ggl-summary-girl';
            card.setAttribute('data-id', radio ? radio.value : '');
            card.setAttribute('data-name', name);
            card.setAttribute('data-avatar', avatar);
            card.innerHTML =
                '<img src="'+esc(avatar)+'" alt="">'+
                '<span class="ggl-summary-info">'+
                    '<span class="ggl-summary-head"><strong>'+esc(name)+'</strong><small>'+esc(meta)+'</small></span>'+
                    '<span class="ggl-summary-meta">'+
                        (flags ? '<span class="ggl-summary-pill flags">'+flags+'</span>' : '')+
                        '<span class="ggl-summary-pill rank"><img src="'+esc(lolIcon)+'" alt="">'+esc(rank)+'</span>'+
                        (timezone ? '<span class="ggl-summary-pill time"><i class="fa-regular fa-clock"></i>'+esc(timezone)+'</span>' : '')+
                    '</span>'+
                '</span>'+
                '<a class="ggl-summary-profile-btn" href="<?= BASE_URL ?>/egirls/'+encodeURIComponent(radio ? radio.value : '')+'" target="_blank" rel="noopener">View Profile</a>';
            var profileBtn = card.querySelector('.ggl-summary-profile-btn');
            if(profileBtn){ profileBtn.addEventListener('click', function(e){ e.stopPropagation(); }); }
            card.addEventListener('click', function(){ pick(card); closeModal(); });
            list.appendChild(card);
        });

        /* Keep an existing selection; otherwise default to "Any Available". */
        var initialId = hidden ? String(hidden.value || '').trim() : '';
        var initialCard = null;
        if(initialId){
            Array.prototype.some.call(list.querySelectorAll('.ggl-summary-girl:not(.ggl-summary-any)'), function(candidate){
                if(String(candidate.getAttribute('data-id') || '') === initialId){
                    initialCard = candidate;
                    return true;
                }
                return false;
            });
        }
        pick(initialCard || anyCard);

        row.addEventListener('click', openModal);
        if(close) close.addEventListener('click', closeModal);
        overlay.addEventListener('click', function(e){ if(e.target === overlay) closeModal(); });
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && overlay.classList.contains('open')) closeModal(); });
    });
})();
</script>
<?php endif; ?>

<script>
(function(){
    if (!document.querySelector('.boost-form.ranked-5s-page')) return;

    function r5sReadNumber(selectors, fallback){
        for (var i = 0; i < selectors.length; i++) {
            var el = document.querySelector(selectors[i]);
            if (!el) continue;
            var val = parseInt((el.value || el.textContent || '').replace(/[^0-9]/g, ''), 10);
            if (!isNaN(val) && val > 0) return val;
        }
        return fallback;
    }

    function r5sSyncSummary(){
        var games = r5sReadNumber(['input[name="matches0"]', '.win-count', '.count.win-count'], 3);
        var boosters = r5sReadNumber(['input[name="boosters"]', '.booster-count'], 1);
        var tierInput = document.querySelector('input[name="start_tier"]:checked');
        var tier = tierInput ? parseInt(tierInput.value, 10) : 3;
        var lpInput = document.querySelector('input[name="start_lp_full"], #start_lp_input');
        var lp = lpInput ? parseInt((lpInput.value || '0').replace(/[^0-9]/g, ''), 10) : 0;
        if (isNaN(lp)) lp = 0;

        document.querySelectorAll('.r5s-games-count').forEach(function(el){ el.textContent = games; });
        document.querySelectorAll('.r5s-boosters-count').forEach(function(el){ el.textContent = boosters; });
        document.querySelectorAll('.r5s-booster-label').forEach(function(el){ el.textContent = boosters === 1 ? '<?= t('Booster') ?>' : '<?= t('Boosters') ?>'; });

        if (tier === 0) {
            document.querySelectorAll('.current-summary-rank-name').forEach(function(el){ el.textContent = '<?= t('Unranked') ?>'; });
            document.querySelectorAll('.current-summary-lp, .r5s-current-summary-lp').forEach(function(el){ el.style.display = 'none'; });
        } else {
            document.querySelectorAll('.r5s-current-summary-lp').forEach(function(el){ el.textContent = '[ ' + lp + ' LP ]'; });
        }

        document.querySelectorAll('.boost-form.ranked-5s-page').forEach(function(form){ form.classList.toggle('r5s-show-current-lp', tier >= 8); });
    }

    ['input', 'change', 'click'].forEach(function(evt){
        document.addEventListener(evt, function(){ setTimeout(r5sSyncSummary, 40); }, true);
    });
    document.addEventListener('DOMContentLoaded', r5sSyncSummary);
    setTimeout(r5sSyncSummary, 250);
    setTimeout(r5sSyncSummary, 900);
})();
</script>

<?php /* Summary rank enforcer.
        The shared boost JS (lol.js) fills the order summary using the non-classic tier map,
        which is wrong on LoL-Classic pages (e.g. shows "Iron"/"Diamond IV" instead of
        "Bronze"/"Challenger") and never shows LP for apex. This enforcer recomputes the
        correct label/icon/LP from the checked inputs and re-applies it via a MutationObserver,
        so it wins regardless of load order. Non-classic non-apex is left to lol.js. */ ?>
<?php if (in_array($summaryGame, ['lol', 'lol_classic', 'tft'], true)): ?>
<script>
(function () {
    var TYPE = <?= json_encode((string)($data['type'] ?? '')) ?>;
    if (['rank', 'win', 'placement', 'duo-pass'].indexOf(TYPE) === -1) return;

    var IS_CLASSIC = <?= $isClassicSummary ? 'true' : 'false' ?>;
    var ASSET = <?= json_encode(ASSET_URL) ?>;

    var TIER = IS_CLASSIC
        ? { 0: 'Unranked', 1: 'Salt', 2: 'Wood', 3: 'Silver', 4: 'Gold', 5: 'Platinum', 6: 'Diamond', 7: 'Legend' }
        : { 0: 'Unranked', 1: 'Iron', 2: 'Bronze', 3: 'Silver', 4: 'Gold', 5: 'Platinum', 6: 'Emerald', 7: 'Diamond', 8: 'Master', 9: 'Grandmaster', 10: 'Challenger' };
    var DIV = IS_CLASSIC ? { 4: 'IV', 3: 'III', 2: 'II', 1: 'I' } : { 4: 'I', 3: 'II', 2: 'III', 1: 'IV' };

    function isApex(t) { t = parseInt(t, 10); if (t === 0) return false; return IS_CLASSIC ? (t === 7) : (t >= 8); }
    function nameOf(t, d) {
        t = parseInt(t, 10); d = parseInt(d, 10);
        if (t === 0) return TIER[0];
        if (isApex(t)) return TIER[t] || '';
        return (TIER[t] || '') + ' ' + (DIV[d] || '');
    }
    function imgOf(t, d) {
        t = parseInt(t, 10); d = parseInt(d, 10);
        if (IS_CLASSIC) {
            var slug = { 0: 'unranked', 1: 'salt', 2: 'wood', 3: 'silver', 4: 'gold', 5: 'platinum', 6: 'diamond', 7: 'legend' };
            var f = (slug[t] || 'salt') + '.webp';
            return ASSET + '/website/images/lol-classic/ranks/' + f;
        }
        return ASSET + '/core/main/img/lol/ranks/mini/' + t + '.png';
    }
    function chk(name) { var i = document.querySelector('input[name="' + name + '"]:checked'); return i ? parseInt(i.value, 10) : null; }
    function lpVal(id) { var el = document.getElementById(id); if (!el) return 0; var n = parseInt((el.value || '0').replace(/[^0-9]/g, ''), 10); return isNaN(n) ? 0 : n; }
    function setText(sel, txt) { document.querySelectorAll(sel).forEach(function (el) { if (el.textContent !== txt) el.textContent = txt; }); }
    function setImg(sel, src) { document.querySelectorAll(sel).forEach(function (el) { if (el.getAttribute('src') !== src) el.setAttribute('src', src); }); }
    function setLp(sel, txt, show) {
        document.querySelectorAll(sel).forEach(function (el) {
            var t = show ? txt : '';
            if (el.textContent !== t) el.textContent = t;
            el.style.display = show ? '' : 'none';
        });
    }

    function applySide(tierName, divName, lpId, nameSel, imgSel, lpSel) {
        var t = chk(tierName);
        if (t === null) return;
        var d = chk(divName);
        var apex = isApex(t);
        var hasLp = !!document.getElementById(lpId);
        if (IS_CLASSIC) {
            setText(nameSel, nameOf(t, d));
            setImg(imgSel, imgOf(t, d));
            setLp(lpSel, '[ ' + lpVal(lpId) + ' LP ]', apex && hasLp);
        } else if (apex) {
            setText(nameSel, TIER[t] || '');
            setLp(lpSel, '[ ' + lpVal(lpId) + ' LP ]', hasLp);
        }
    }

    var busy = false;
    function enforce() {
        if (busy) return;
        busy = true;
        try {
            applySide('start_tier', 'start_division', 'start_lp_input',
                '.current-summary-rank-name',
                '.current-summary-rank-img:not(#coaching-current-rank-img)',
                '.current-summary-lp:not(.r5s-current-summary-lp)');
            if (TYPE === 'rank') {
                applySide('end_tier', 'end_division', 'end_lp_input',
                    '.desired-summary-rank-name',
                    '.desired-summary-rank-img',
                    '.desired-summary-lp');
            }
        } finally { busy = false; }
    }

    ['change', 'click', 'input'].forEach(function (evt) {
        document.addEventListener(evt, function (e) {
            if (e.target && e.target.matches('input[name="start_tier"],input[name="start_division"],input[name="end_tier"],input[name="end_division"],#start_lp_input,#end_lp_input')) {
                requestAnimationFrame(enforce);
            }
        }, true);
    });

    function startObserver() {
        var boxes = document.querySelectorAll('.rank-box');
        if (!boxes.length) { setTimeout(startObserver, 200); return; }
        var obs = new MutationObserver(function () { enforce(); });
        boxes.forEach(function (box) { obs.observe(box, { childList: true, characterData: true, subtree: true }); });
        enforce();
    }

    document.addEventListener('DOMContentLoaded', function () { enforce(); startObserver(); });
    startObserver();
    setTimeout(enforce, 150);
    setTimeout(enforce, 700);
})();
</script>
<?php endif; ?>
