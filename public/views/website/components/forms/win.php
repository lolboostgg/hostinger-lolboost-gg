<?php
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
if ($uiGame === 'tft') { $uiGame = 'lol'; }
$rankStart = $isClassic ? 1 : 1;
$rankEnd = $isClassic ? 7 : 8;
$rankValues = $isClassic ? range(1, 7) : range($rankStart, $rankEnd);
$placementRankStart = $isClassic ? 0 : 0;
$placementRankEnd = $isClassic ? 7 : 10;
$divisionValues = $isClassic ? [4, 3, 2, 1] : [1, 2, 3, 4];
$defaultStartTier = $isClassic ? 1 : 3;
$defaultEndTier = $isClassic ? 2 : 4;
$defaultStartDivision = 4;
$defaultEndDivision = $isClassic ? 4 : 1;
$apexFrom = $isClassic ? 7 : 8;
if (!function_exists('lol_classic_rank_asset_url')) {
    function lol_classic_rank_asset_url($tier, $division = 5) {
        $tier = (int)$tier;
        $division = (int)$division;
        $tierNames = [
            0 => 'unranked',
            1 => 'bronze',
            2 => 'silver',
            3 => 'gold',
            4 => 'platinum',
            5 => 'diamond',
            7 => 'challenger',
        ];
        if ($tier === 0 || $tier === 7) {
            return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'unranked') . '.png';
        }
        $divisionNames = [5 => 'v', 4 => 'iv', 3 => 'iii', 2 => 'ii', 1 => 'i'];
        return ASSET_URL . '/website/images/lol-classic/ranks/' . ($tierNames[$tier] ?? 'bronze') . '-' . ($divisionNames[$division] ?? 'v') . '.png';
    }
}
?>
<div class="boost win-boost">
    <div class="card">
        <div class="card-header">
            <img src="<?= $isClassic ? lol_classic_rank_asset_url($defaultStartTier, $defaultStartDivision) : util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon"
                class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Current Rank') ?></h3>
                <p><?= t('Select your current tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php foreach ($rankValues as $i): ?>
                    <?php $rankImg = ($isClassic && $i < $apexFrom) ? ($i . '-' . $defaultStartDivision) : $i; ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>"
                            class="custom-checkbox" <?= $i == $defaultStartTier ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= $isClassic ? lol_classic_rank_asset_url($i, ($i < $apexFrom ? $defaultStartDivision : 1)) : util_rank_img($uiGame, 'mini', $rankImg); ?>" alt="<?= $ranks[$i]; ?>">
                            <span class="tooltip">
                                <?= $ranks[$i]; ?>
                            </span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="divisions" id="start_divisions">
                <?php foreach ($divisionValues as $i): ?>
                    <label>
                        <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>"
                            class="custom-checkbox" <?= $i == $defaultStartDivision ? 'checked' : ''; ?>>
                        <div class="division-btn">
                            <?= $isClassic ? util_format_lol_classic_division($i) : util_format_lol_division($i); ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="lp-selector" id="start_lp_full" style="display:none">
                <h6><?= t('Current LP:') ?></h6>
                <div class="input-container">
                    <button type="button" onclick="decrementValue(startLPInput)">
                        <i class="fas fa-circle-minus"></i>
                    </button>
                    <input type="text" name="start_lp_full" id="start_lp_input" value="0" min="0" max="1500" step="100">
                    <button type="button" onclick="incrementValue(startLPInput)">
                        <i class="fas fa-circle-plus"></i>
                    </button>
                </div>
            </div>

            <div class="options">
                <?php if (isset($data['id']) && (int)$data['id'] === 22): ?>
                    <input type="hidden" name="lp_gain" value="20-24">
                    <div class="option">
                    <h6><?= t('Server') ?></h6>
                    <select class="select2" name="server" data-no-search="true">
                        <option value="euw" selected=""><?= t('EU-West') ?></option>
                        <option value="na"><?= t('North America') ?></option>
                        <option value="me"><?= t('Middle East') ?></option>
                        <option value="eune"><?= t('EU-Nordic & East') ?></option>
                        <option value="br"><?= t('Brazil') ?></option>
                        <option value="oce"><?= t('Oceania') ?></option>
                        <option value="ru"><?= t('Russia') ?></option>
                        <option value="tr"><?= t('Turkey') ?></option>
                        <option value="lan"><?= t('Latin America North') ?></option>
                        <option value="las"><?= t('Latin America South') ?></option>
                        <option value="jp"><?= t('Japan') ?></option>
                        <option value="vn"><?= t('Vietnam') ?></option>
                        <option value="ph"><?= t('Philippines') ?></option>
                        <option value="sg"><?= t('Singapore') ?></option>
                        <option value="th"><?= t('Thailand') ?></option>
                        <option value="tw"><?= t('Taiwan') ?></option>
                    </select>
                </div>
                    <input type="hidden" name="queue_type" value="solo_/_duo">
                <?php else: ?>
                    <div class="option">
                    <h6><?= t('LP Gain') ?></h6>
                    <select class="select2" name="lp_gain" data-no-search="true">
                        <option value="10-19"><?= t('10-19 LP') ?></option>
                        <option value="20-24" selected><?= t('20-24 LP') ?></option>
                        <option value="25-29"><?= t('25-29 LP') ?></option>
                        <option value="30+"><?= t('30+ LP') ?></option>
                    </select>
                </div>
                    <div class="option">
                    <h6><?= t('Server') ?></h6>
                    <select class="select2" name="server" data-no-search="true">
                        <option value="euw" selected=""><?= t('EU-West') ?></option>
                        <option value="na"><?= t('North America') ?></option>
                        <option value="me"><?= t('Middle East') ?></option>
                        <option value="eune"><?= t('EU-Nordic & East') ?></option>
                        <option value="br"><?= t('Brazil') ?></option>
                        <option value="oce"><?= t('Oceania') ?></option>
                        <option value="ru"><?= t('Russia') ?></option>
                        <option value="tr"><?= t('Turkey') ?></option>
                        <option value="lan"><?= t('Latin America North') ?></option>
                        <option value="las"><?= t('Latin America South') ?></option>
                        <option value="jp"><?= t('Japan') ?></option>
                        <option value="vn"><?= t('Vietnam') ?></option>
                        <option value="ph"><?= t('Philippines') ?></option>
                        <option value="sg"><?= t('Singapore') ?></option>
                        <option value="th"><?= t('Thailand') ?></option>
                        <option value="tw"><?= t('Taiwan') ?></option>
                    </select>
                </div>
                    <?php if (!$isClassic): ?>
                    <div class="option">
                    <h6><?= t('Queue Type') ?></h6>
                    <select class="select2" name="queue_type" data-no-search="true">
                        <option value="solo_/_duo" selected=""><?= t('Solo/Duo') ?></option>
                        <option value="flexq"><?= t('Flex') ?></option>
                    </select>
                </div>
                    <?php else: ?>
                        <input type="hidden" name="queue_type" value="solo_/_duo">
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count"><?= t('2') ?></div>
            <div class="text">
                <h3><?= t('Wins Amount') ?></h3>
                <p><?= t('Select your desired amount of wins.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="matches_slider1"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" hidden>
        </div>
    </div>
</div>
<?php if ($isClassic): ?>
<style>
.boost-form.lol-classic-page .win-boost .card-header-rank,
.boost-form.lol-classic-page .win-boost .current-rank-img{width:92px!important;height:92px!important;object-fit:contain!important;max-width:none!important;}
.boost-form.lol-classic-page .summary-wrapper .rank-box .current-summary-rank-img{width:74px!important;height:74px!important;object-fit:contain!important;max-width:none!important;}
</style>
<script>
(function(){
    var base = '<?= ASSET_URL ?>/website/images/lol-classic/ranks/';
    var tierNames = {1:'Salt',2:'Wood',3:'Silver',4:'Gold',5:'Platinum',6:'Diamond',7:'Legend'};
    function file(tier, division){
        tier = parseInt(tier || 1, 10);
        var names = {1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[tier] || 'salt') + '.webp';
    }
    function label(tier, division){
        tier = parseInt(tier || 1, 10);
        division = parseInt(division || 4, 10);
        var divs = {4:'IV',3:'III',2:'II',1:'I'};
        return tier === 7 ? 'Legend' : (tierNames[tier] || 'Salt') + ' ' + (divs[division] || 'IV');
    }
    function checked(name, fallback){
        var input = document.querySelector('input[name="' + name + '"]:checked');
        return input ? input.value : fallback;
    }
    function syncClassicWinIcons(){
        var tier = checked('start_tier', 1), division = checked('start_division', 5);
        var src = base + file(tier, division);
        document.querySelectorAll('.current-rank-img, .current-summary-rank-img').forEach(function(img){ img.src = src; });
        document.querySelectorAll('.current-summary-rank-name').forEach(function(el){ el.textContent = label(tier, division); });
        var divisions = document.getElementById('start_divisions');
        var lpFull = document.getElementById('start_lp_full');
        var isChallenger = parseInt(tier, 10) === 7;
        if (divisions) divisions.style.display = isChallenger ? 'none' : '';
        if (lpFull) lpFull.style.display = isChallenger ? 'block' : 'none';
        if (isChallenger) {
            var divI = document.querySelector('input[name="start_division"][value="1"]');
            if (divI) divI.checked = true;
        }
    }
    ['change','click','input'].forEach(function(evt){
        document.addEventListener(evt, function(e){
            if (e.target && e.target.matches('input[name="start_tier"],input[name="start_division"]')) {
                requestAnimationFrame(syncClassicWinIcons);
            }
        }, true);
    });
    document.addEventListener('DOMContentLoaded', syncClassicWinIcons);
    setTimeout(syncClassicWinIcons, 150);
    setTimeout(syncClassicWinIcons, 700);
})();
</script>
<?php endif; ?>
