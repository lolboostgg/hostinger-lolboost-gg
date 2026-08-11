<style>
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
                        <img src="<?= util_rank_img('val', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <br>
                        <small class="current-summary-lp"><?= t('[ 0-20 RR ]') ?></small>
                        <?php break;
                    case 'win': ?>
                        <img src="<?= util_rank_img('val', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'placement': ?>
                        <img src="<?= util_rank_img('val', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'match': ?>
                        <img src="<?= util_rank_img('val', 'mini', 3) ?>" alt="rank_icon" class="current-summary-rank-img">
                        <span class="title current-summary-rank-name"><?= t('Silver I') ?></span>
                        <?php break;
                    case 'normal': ?>
                        <div class="game game-mode"><?= t('Summoner\'s Rift') ?></div>
                        <?php break;
                    case 'coaching': ?>
                        <div class="game" style="text-align: center; width: 100%;">
                            <span class="hour-count"><?= t('5') ?></span><?= t(' Coaching Hours') ?>
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
                            <span class="title current-summary-rank-name"><?= t('Silver') ?></span>
                        </div>
                        <?php break;
                    case 'level': ?>
                        <div class="game current-summary-rank-name"><?= t('Level 1') ?></div>
                        <?php break;
                    case 'clash': ?>
                        <div class="game current-summary-rank-name"><?= t('Tier 1 (1 Booster)') ?></div>
                        <?php break;
                    default: ?>
                        <img src="<?= util_rank_img('val', 'mini', 3) ?>" alt="rank_icon">
                        <span class="title"><?= t('Silver I') ?></span>
                        <?php break;
                } ?>
            </div>
            <?php if ($data['type'] != 'coaching') { ?>
                <img src="<?= ASSET_URL ?>/website/images/arrow-summary.svg" alt="arrow_icon">
            <?php } ?>
            <?php switch ($data['type']) {
                case 'rank': ?>
                    <div class="to">
                        <img src="<?= util_rank_img('val', 'mini', 4) ?>" alt="rank_icon" class="desired-summary-rank-img">
                        <span class="title desired-summary-rank-name"><?= t('Gold IV') ?></span>
                        <br>
                        <small class="desired-summary-lp"></small>
                    </div>
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
                        <div class="count current-summary-rank-name"><?= t('Level 2') ?></div>
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
                        <img src="<?= util_rank_img('val', 'mini', 4) ?>" alt="rank_icon">
                        <span class="title"><?= t('Gold IV') ?></span>
                    </div>
                    <?php break;
            } ?>
        </div>

        <?php
        
        // Forms / types that should never show the Solo/Duo toggle
        // Coaching is always Solo-only.
        $exclude_duo = [15, 18, 19, 20];

// TFT forms that should always be SOLO (no duo toggle)
$tft_solo_only = [21, 22, 23];

// TFT Coaching (form id 25) should be SOLO-only and must not show extra options like Champs & Roles
$tft_coaching_only = [25];

// TFT Double Up (form id 24) supports Solo + Duo
$tft_double_up = [24];

// Eindeutige IDs (optional, aber empfohlen)
$soloId = 'solo_' . $data['id'];
$duoId = 'duo_' . $data['id'];

// Coaching: force solo and hide the toggle completely
if (($data['type'] ?? null) === 'coaching'): ?>
    <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
<?php elseif (in_array($data['id'], $tft_solo_only) || in_array($data['id'], $tft_coaching_only)): ?>
    <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" checked hidden>
<?php elseif (!in_array($data['id'], $exclude_duo)): ?>
    <div class="toggle-group">
        <input type="radio" id="<?= $soloId ?>" name="is_duo" value="0" <?= ($data['id'] == 24 ? 'checked' : '') ?>>
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
                'tooltip' => t('Your boost order will be completed around 2x faster than regular ones.'),
            ],
            'bonus' => [
                'label' => '+1 Bonus Win',
                'icon' => ASSET_URL . '/website/images/boost-forms/bonus-win1.svg',
                'badge' => 'AUTO',
                'badgeClass' => 'primary',
                'input' => ['id' => 'is_bonus_win', 'name' => 'is_bonus_win', 'value' => 1],
                'tooltip' => t('Your booster will win an additional game after you reach your desired rank.'),
            ],
            'solo_queue' => [
                'label' => 'Solo Queue',
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
                'tooltip' => t('Your booster will be in a call with you and point out your mistakes and give you guidance.'),
            ],
            'hidden_duo' => [
                'label' => 'Hidden Duo',
                'icon' => ASSET_URL . '/website/images/boost-forms/hidden_duo3.svg',
                'badge' => '+40%',
                'badgeClass' => 'primary',
                'class' => 'duo-option',
                'input' => ['id' => 'is_hidden_duo', 'name' => 'is_hidden_duo', 'value' => 1],
                'tooltip' => t('Your Booster will use multiple accounts to play with you.'),
                'font-awesome-icon' => 'fa-duotone fa-user-secret',
            ],
            'champs_roles' => [
                'label' => 'Agents',
                'icon' => ASSET_URL . '/website/images/boost-forms/champs-roles1.svg',
                'badge' => 'FREE',
                'badgeClass' => 'success',
                'class' => 'solo-option',
                'input' => ['id' => 'is_agents', 'name' => 'is_agents', 'value' => 1],
                'tooltip' => t('Select which agents the booster can play free of charge.'),
            ],

        ];
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
                case 'rank':
                    $enabledOptions = ['priority', 'bonus', 'solo_queue', 'stream', 'coaching', 'hidden_duo', 'champs_roles'];
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
        ?>


        <div class="extra-options">
            <?php foreach ($enabledOptions as $optKey):
                if ($optKey === 'hidden_duo') { continue; }
                $opt = $allOptions[$optKey]; ?>
                <div class="option <?= $opt['class'] ?? '' ?>">
                    <div class="text" data-tooltip="<?= $opt['tooltip'] ?? '' ?>">
                        <?php if (isset($opt['font-awesome-icon'])): ?>
                            <i class="<?= $opt['font-awesome-icon'] ?>"></i>
                        <?php else: ?>
                            <img src="<?= $opt['icon'] ?>" alt="option_icon">
                        <?php endif; ?>
                        <?= $opt['label'] ?>
                        <span class="badge <?= $opt['badgeClass'] ?>">
                            <?= $opt['badge'] ?>
                        </span>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="<?= $opt['input']['id'] ?>" name="<?= $opt['input']['name'] ?>"
                            value="<?= $opt['input']['value'] ?>">
                        <span class="slider"></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

       


        <?php
        // ── Booster Selector Row + Modal ──────────────────────────────────────
        $__smFormId2    = (int)($data['id'] ?? $data['form_id'] ?? 0);
        $__smHide       = in_array($__smFormId2, [15, 16, 25, 26]); // coaching / pro-games
        if (!$__smHide):
            global $db;
            $__smRaw = [];
            try {
                $__smGame2 = $data['game'] ?? '';
                $__smRaw   = db_get_rows('boosters', [
                    'select'         => 'id,username,icon,rating,languages',
                    'is_banned'      => 0,
                    'boost_requests' => 1,
                ] + (!empty($__smGame2) ? ['games' => ['s' => $__smGame2]] : [])) ?: [];
            } catch (Throwable $e) {}

            $__smOnline = function_exists('lb_booster_online_map') ? lb_booster_online_map() : [];

            $__smProf2 = [];
            foreach ($__smRaw as $__pb2) {
                $__pid2 = (int)($__pb2['id']??0); if(!$__pid2) continue;
                try {
                    $__pr2 = db_get_row('booster_profiles', ['booster_id'=>$__pid2], true);
                    if (!empty($__pr2)) $__smProf2[$__pid2] = (array)$__pr2;
                } catch (Throwable $e) {}
            }

            $__smOng2 = [];
            try {
                $__og2 = $db->run("SELECT booster_id, COUNT(*) as cnt FROM orders WHERE status='IN_PROGRESS' AND booster_id IS NOT NULL AND booster_id>0 GROUP BY booster_id");
                if (!empty($__og2)) foreach ($__og2 as $__o2) { $__o2=(array)$__o2; $__oid2=(int)($__o2['booster_id']??0); if($__oid2>0) $__smOng2[$__oid2]=(int)($__o2['cnt']??0); }
            } catch (Throwable $e) {}

            $__smCompleted2 = [];
            try {
                $__co2 = $db->run("SELECT booster_id, COUNT(*) as cnt FROM orders WHERE status='COMPLETED' AND booster_id IS NOT NULL AND booster_id>0 GROUP BY booster_id");
                if (!empty($__co2)) foreach ($__co2 as $__c2) { $__c2=(array)$__c2; $__cid2=(int)($__c2['booster_id']??0); if($__cid2>0) $__smCompleted2[$__cid2]=(int)($__c2['cnt']??0); }
            } catch (Throwable $e) {}

            $__smRating2 = [];
            try {
                $__rv2 = $db->run("SELECT booster_id, AVG(rating) as avg_rating FROM reviews WHERE approved=1 AND booster_id IS NOT NULL AND booster_id>0 GROUP BY booster_id");
                if (!empty($__rv2)) foreach ($__rv2 as $__rvr2) { $__rvr2=(array)$__rvr2; $__rid2=(int)($__rvr2['booster_id']??0); if($__rid2>0) $__smRating2[$__rid2]=round((float)($__rvr2['avg_rating']??0), 1); }
            } catch (Throwable $e) {}

            $__smGameKey2 = strtolower(trim((string)($__smGame2 ?? 'lol')));
            if (str_contains($__smGameKey2, 'val')) $__smGameKey2 = 'val';
            elseif (str_contains($__smGameKey2, 'tft')) $__smGameKey2 = 'tft';
            else $__smGameKey2 = 'lol';

            $__smLolRanks2 = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
            $__smLolDivs2  = [1=>'IV',2=>'III',3=>'II',4=>'I'];
            $__smValRanks2 = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'];
            $__smValDivs2  = [1=>'I',2=>'II',3=>'III'];
            $__smTftRanks2 = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Master',8=>'Grandmaster',9=>'Challenger'];

            $__smRankPayload2 = function(array $__pr) use ($__smGameKey2, $__smLolRanks2, $__smLolDivs2, $__smValRanks2, $__smValDivs2, $__smTftRanks2) {
                $__field = $__smGameKey2 === 'val' ? 'val_rank' : ($__smGameKey2 === 'tft' ? 'tft_rank' : 'lol_rank');
                $__raw = trim((string)($__pr[$__field] ?? ''));
                $__tier = 0; $__div = 0;
                if ($__raw !== '') {
                    $__parts = explode('|', $__raw);
                    $__tier = (int)($__parts[0] ?? 0);
                    $__div  = (int)($__parts[1] ?? 0);
                }

                if ($__smGameKey2 === 'val') {
                    $__name = $__smValRanks2[$__tier] ?? 'Unranked';
                    $__label = $__name . (($__tier > 0 && $__tier < 8 && $__div > 0) ? ' ' . ($__smValDivs2[$__div] ?? '') : '');
                    $__img = ASSET_URL . '/core/main/img/val/ranks/mini/' . $__tier . '.png';
                } elseif ($__smGameKey2 === 'tft') {
                    $__label = ($__smTftRanks2[$__tier] ?? 'Unranked') . ' TFT';
                    $__img = ASSET_URL . '/core/main/img/lol/ranks/mini/' . $__tier . '.png';
                } else {
                    $__name = $__smLolRanks2[$__tier] ?? 'Unranked';
                    $__label = $__name . (($__tier > 0 && $__tier < 8 && $__div > 0) ? ' ' . ($__smLolDivs2[$__div] ?? '') : '');
                    $__img = ASSET_URL . '/core/main/img/lol/ranks/mini/' . $__tier . '.png';
                }

                $__fallback = ucfirst(strtolower(trim((string)($__pr['rank'] ?? ''))));
                if (($__tier <= 0 || trim($__label) === 'Unranked') && $__fallback !== '') $__label = $__fallback;
                return ['label' => trim($__label), 'img' => $__img, 'tier' => $__tier];
            };

            $__smAll2 = [];
            foreach ($__smRaw as $__b2) {
                $__bid2 = (int)($__b2['id']??0); if(!$__bid2) continue;
                $__pr   = $__smProf2[$__bid2] ?? [];
                $__tz2  = trim($__pr['timezone']??'');
                if (empty($__tz2)) continue;
                $__roles2 = array_values(array_filter(array_map('trim', explode('|', (string)($__pr['roles'] ?? '')))));
                $__langsRaw2 = (string)($__b2['languages'] ?? ($__pr['languages'] ?? ''));
                $__langs2 = array_values(array_filter(array_map('trim', explode('|', $__langsRaw2))));
                $__rank2 = $__smRankPayload2($__pr);
                $__smAll2[] = [
                    'id'         => $__bid2,
                    'name'       => $__b2['username'] ?? '',
                    'icon'       => $__b2['icon'] ?? '',
                    'online'     => !empty($__smOnline[$__bid2]),
                    'rank'       => $__rank2['label'],
                    'rank_img'   => $__rank2['img'],
                    'rank_tier'  => $__rank2['tier'],
                    'roles'      => $__roles2,
                    'roles_text' => implode(' / ', array_map('ucfirst', $__roles2)),
                    'languages'  => $__langs2,
                    'languages_text' => implode(' / ', array_map('ucfirst', $__langs2)),
                    'game'       => $__smGameKey2,
                    'tz'         => $__tz2,
                    'ongoing'    => $__smOng2[$__bid2] ?? 0,
                    'completed'  => $__smCompleted2[$__bid2] ?? 0,
                    'rating'     => $__smRating2[$__bid2] ?? (is_numeric($__b2['rating'] ?? null) ? round((float)$__b2['rating'], 1) : 5.0),
                    'profile'    => BASE_URL . '/boosters/' . $__bid2,
                ];
            }
            usort($__smAll2, function($a, $b) {
                return ($b['online'] <=> $a['online'])
                    ?: ((int)($b['completed'] ?? 0) <=> (int)($a['completed'] ?? 0))
                    ?: ((float)($b['rating'] ?? 0) <=> (float)($a['rating'] ?? 0))
                    ?: ((int)($a['ongoing'] ?? 0) <=> (int)($b['ongoing'] ?? 0))
                    ?: strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
            });
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
        <input type="hidden" name="booster_id" id="bsr-hidden" value="">

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
                width:min(1080px,calc(100vw - 44px));
                max-width:1080px;
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
                box-shadow:0 10px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.035);
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
            #bsr-panel{width:min(1160px,calc(100vw - 56px));max-width:1160px;}
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
            #bsr-panel{width:min(1180px,calc(100vw - 72px));max-width:1180px;}
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
            var allData  = <?= json_encode(array_values($__smAll2), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
            var hasOnline = allData.some(function(b){ return b.online; });
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
                var items    = []; // all .bsr-item elements

                function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
                function openModal()  { overlayEl.classList.add('bsr-open'); document.body.style.overflow='hidden'; }
                function closeModal() { overlayEl.classList.remove('bsr-open'); document.body.style.overflow=''; }

                rowEl.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                anyBtn.addEventListener('click', function(){ selId=''; hidInp.value=''; deselectAll(); updateRow(null); closeModal(); });
                // overlay click disabled \u2013 close via X only
                // escape key disabled \u2013 close via X only

                /* ── Helpers ── */
                function getLocalTime(tz){
                    try { return new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',timeZone:tz}); }
                    catch(e){ return '—'; }
                }
                function updateRow(b){
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
                        var langs = Array.isArray(b.languages) ? b.languages : [];
                        var langsText = b.languages_text || langs.map(function(l){ return l ? l.charAt(0).toUpperCase()+l.slice(1) : ''; }).filter(Boolean).join(' / ');
                        var langIcons = langs.slice(0, 3).map(function(l){
                            return '<img src="<?= ASSET_URL ?>/core/main/img/languages/'+esc(l)+'.png" alt="'+esc(l)+'" title="'+esc(l ? l.charAt(0).toUpperCase()+l.slice(1) : '')+'" onerror="this.style.display=\'none\'">';
                        }).join('');
                        if (langs.length > 3) { langIcons += '<span class="bsr-pill-icon" title="'+esc(langsText)+'">+'+esc(String(langs.length - 3))+'</span>'; }
                        var rankHtml = b.rank ? '<span class="bsr-rank-pill" title="'+esc(b.rank)+'">'+(b.rank_img ? '<img src="'+esc(b.rank_img)+'" alt="" onerror="this.style.display=\'none\'">' : '')+'<span class="bsr-rank-label">'+esc(b.rank)+'</span></span>' : '';
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
                        el.dataset.name = ((b.name||'')+' '+(b.rank||'')+' '+langsText).toLowerCase();
                        el.dataset.status = b.online ? 'online' : 'offline';
                        el.innerHTML =
                            '<div class="bsr-item-check">&#10003;</div>'+
                            '<div class="bsr-item-av">'+
                                '<img src="'+esc(b.icon||defaultAv)+'" alt="">'+
                                '<span class="bsr-item-dot'+(b.online?' on':'')+'"></span>'+
                            '</div>'+
                            '<div class="bsr-item-name" title="'+esc(b.name)+'">'+esc(b.name)+'</div>'+
                            '<div class="bsr-item-meta">'+rankHtml+langHtml+ordersHtml+ratingHtml+timeHtml+profileHtml+'</div>';
                        var profileLink = el.querySelector('.bsr-profile-btn');
                        if (profileLink) {
                            profileLink.addEventListener('click', function(ev){ ev.stopPropagation(); });
                        }
                        el.addEventListener('click', function(){
                            if(el.classList.contains('sel')){ selId=''; hidInp.value=''; deselectAll(); updateRow(null); return; }
                            selectBooster(b.id, b);
                        });
                        grid.appendChild(el);
                        items.push(el);
                    });
                    wrapper.appendChild(grid);
                }

                var onlineBoosters  = allData.filter(function(b){ return  b.online; });
                var offlineBoosters = allData.filter(function(b){ return !b.online; });
                buildSection(onlineBoosters,  onlineWrap,  L.onlSec, true);
                buildSection(offlineBoosters, offlineWrap, L.offSec, false);
                filterPills.forEach(function(btn){
                    var count = allData.length;
                    if (btn.dataset.filter === 'online') count = onlineBoosters.length;
                    if (btn.dataset.filter === 'offline') count = offlineBoosters.length;
                    btn.appendChild(document.createTextNode(' ('+count+')'));
                });

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

        <div class="discount-box" id="discount-box">
            <button class="remove-btn" id="remove-discount"><?= t('⨯') ?></button>
            <div class="left">
                <img src="<?= ASSET_URL ?>/website/images/boost-forms/discount1.svg" alt="discount_icon">
                <div class="text">
                    <h5 id="discount-message"><?= t('40% Discount Applied!') ?></h5>
                    <p><?= t('Special promotion active') ?></p>
                </div>
            </div>
            <div class="right">
                <h5><?= t('Original Price') ?></h5>
                <div class="amounts">
                    <span class="old" id="old-price"><?= t('€0.00') ?></span>
                    <span class="new total-price" id="new-price"><?= t('€0.00') ?></span>
                </div>
                <h5 class="saved"><?= t('You save') ?><span id="saved-price"><?= t('€0.00') ?></span>
                </h5>
            </div>
        </div>

        <div class="discount-input" id="discount-input">
            <input type="text" placeholder="Enter Discount Code" name="discount_code" id="discount_code">
        </div>

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
</div>

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
