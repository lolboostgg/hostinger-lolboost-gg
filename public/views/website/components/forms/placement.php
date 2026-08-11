<?php
$uiGame = ($data['game'] ?? 'lol');
$isClassic = util_is_lol_classic($uiGame);
$formId = (int)($data['id'] ?? 0);
$placementMaxGames = ($formId === 32) ? 10 : 5;
if ($uiGame === 'tft') { $uiGame = 'lol'; }
$rankStart = $isClassic ? 1 : 1;
$rankEnd = $isClassic ? 7 : 8;
$placementRankStart = $isClassic ? 0 : 0;
$placementRankEnd = $isClassic ? 7 : 10;
$placementTiers = $isClassic ? range(0, 7) : range($placementRankStart, $placementRankEnd);
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
            <img src="<?= $isClassic ? lol_classic_rank_asset_url(0, 5) : util_rank_img($uiGame, 'mini', 3); ?>" alt="rank_icon"
                 class="card-header-rank current-rank-img">
            <div class="text">
                <h3><?= t('Last Season Rank') ?></h3>
                <p><?= t('Select your last season tier and division.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="ranks">
                <?php foreach ($placementTiers as $i): ?>
                    <label>
                        <input type="radio" name="start_tier" id="start_<?= $i; ?>" value="<?= $i; ?>"
                               class="custom-checkbox" <?= $i == ($isClassic ? 0 : 3) ? 'checked' : ''; ?>>
                        <div class="rank-btn">
                            <img src="<?= $isClassic ? lol_classic_rank_asset_url($i, ($i > 0 && $i < $apexFrom ? $defaultStartDivision : 1)) : util_rank_img($uiGame, 'mini', $i); ?>" alt="<?= $ranks[$i]; ?>">
                            <span class="tooltip"><?= $ranks[$i]; ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="divisions" id="start_divisions">
                <?php foreach ($divisionValues as $i): ?>
                    <label>
                        <input type="radio" name="start_division" id="start_div_<?= $i; ?>" value="<?= $i; ?>"
                               class="custom-checkbox" <?= $i == $defaultStartDivision ? 'checked' : ''; ?>>
                        <div class="division-btn"><?= $isClassic ? util_format_lol_classic_division($i) : util_format_lol_division($i); ?></div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="options">
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

                <?php if (($data['game'] ?? 'lol') !== 'tft' && !$isClassic): ?>
                    <div class="option">
                        <h6><?= t('Queue Type') ?></h6>
                        <select class="select2" name="queue_type">
                            <option value="solo_/_duo" selected=""><?= t('Solo/Duo') ?></option>
                            <option value="flexq"><?= t('Flex') ?></option>
                        </select>
                    </div>
                <?php else: ?>
                    <!-- LoL Classic and TFT placements stay Solo/Duo only for backend/pricing -->
                    <input type="hidden" name="queue_type" value="solo_/_duo">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card count-card">
        <div class="card-header">
            <div class="count win-count">5</div>
            <div class="text">
                <h3><?= t('Matches Amount') ?></h3>
                <p><?= t('Select your desired amount of matches.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="range-slider" id="matches_slider"></div>
            <input class="form-control range-slider-value-min" name="matches0" type="number" id="matches_count" value="5" min="1" max="<?= $placementMaxGames ?>" hidden>

            <div class="placement-guarantee-wrap">
                <div class="pg-head">
                    <h5><?= t('Placement Guarantee') ?></h5>
                </div>
                <div id="guaranteed-wins" class="pg-box"></div>
            </div>
        </div>
    </div>
</div>


<script>
const rankNames = <?= json_encode(array_values($ranks)) ?>;
const placementMaxGames = <?= (int)$placementMaxGames ?>;

function getPlacementsCount() {
  const input = document.getElementById('matches_count');
  if (input && input.value !== '') {
    const n = parseInt(input.value, 10);
    if (!Number.isNaN(n)) return n;
  }

  // Fallback: liest direkt vom Handle aria-valuenow
  const handle = document.querySelector('#matches_slider .noUi-handle');
  if (handle) {
    const n = parseInt(handle.getAttribute('aria-valuenow'), 10);
    if (!Number.isNaN(n)) return n;
  }

  const wc = document.querySelector('.win-count');
  if (wc) {
    const n = parseInt(wc.textContent, 10);
    if (!Number.isNaN(n)) return n;
  }

  return 5;
}

function updatePlacementGuarantee() {
  const checked = document.querySelector('input[name="start_tier"]:checked');
  if (!checked) return;

  const rank = parseInt(checked.value, 10);
  const rankName = rankNames[rank] || "Unknown";

  let placements = getPlacementsCount();
  placements = Math.max(1, Math.min(placementMaxGames, placements));

  const isLow = rank <= 6; // Unranked..Emerald
  const isHigh = rank >= 7; // Diamond, Master, Grandmaster, Challenger
  let guaranteedWins;

  if (isHigh) {
    // High ranks should not make 4 placements better value than 5 placements.
    // Diamond+ guarantee table: 1->1, 2->1, 3->2, 4->2, 5->3
    const highRankGuarantees = { 1: 1, 2: 1, 3: 2, 4: 2, 5: 3, 6: 4, 7: 4, 8: 5, 9: 5, 10: 6 };
    guaranteedWins = highRankGuarantees[placements] || 6;
  } else {
    // Unranked..Emerald guarantee table: 1->1, 2->1, 3->2, 4->3, 5->4
    const lowRankGuarantees = { 1: 1, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6, 8: 6, 9: 7, 10: 8 };
    guaranteedWins = lowRankGuarantees[placements] || 8;
  }

  const lossLimit = Math.max(0, placements - guaranteedWins);

  const box = document.getElementById("guaranteed-wins");
  if (!box) return;

  box.innerHTML = `
    <div class="pg-row">
      <div class="pg-left">
        <div class="pg-icon"><i class="fa-solid fa-shield-check"></i></div>
        <div class="pg-title">
          <div class="t1">Placement Guarantee</div>
          <div class="t2">Based on your <strong>${rankName}</strong> selection</div>
        </div>
      </div>
      <div class="pg-badge">${guaranteedWins}/${placements} WINS</div>
    </div>

    <div class="pg-divider"></div>

    <div class="pg-list">
      <div class="pg-item"><i class="fa-solid fa-circle-check"></i>
        <span>We guarantee <strong>${guaranteedWins}/${placements} wins</strong> in your <strong>${rankName}</strong> placements.</span>
      </div>
      <div class="pg-item"><i class="fa-solid fa-circle-check"></i>
        <span>If the booster loses more than <strong>${lossLimit}</strong> game(s), you get <strong>+2 extra wins</strong> for each additional loss.</span>
      </div>
      <div class="pg-item"><i class="fa-solid fa-circle-check"></i>
        <span>If your final placement lands <strong>one full division higher</strong>, the boost is considered completed.</span>
      </div>
    </div>
  `;
}

function bindSliderUpdates() {
  const sliderEl = document.getElementById('matches_slider');
  const matchesInput = document.getElementById('matches_count');
  const wc = document.querySelector('.win-count');

  if (!sliderEl) return false;

  // BEST: noUiSlider API
  if (sliderEl.noUiSlider) {
    sliderEl.noUiSlider.updateOptions({
      range: { min: 1, max: placementMaxGames },
      step: 1
    }, false);

    // Placements should open with 5 matches selected.
    if (!sliderEl.dataset.lbDefaultMatchesApplied) {
      sliderEl.dataset.lbDefaultMatchesApplied = '1';
      sliderEl.noUiSlider.set(5);
      if (matchesInput) matchesInput.value = 5;
      if (wc) wc.textContent = '5';
    }

    const handler = (values) => {
      const v = parseInt(values[0], 10);
      if (!Number.isNaN(v)) {
        if (matchesInput) matchesInput.value = v;
        if (wc) wc.textContent = v;
      }
      updatePlacementGuarantee();
    };

    sliderEl.noUiSlider.on('update', handler);
    sliderEl.noUiSlider.on('set', handler);
    updatePlacementGuarantee();
    return true;
  }

  // FALLBACK: observe aria-valuenow changes
  const handle = sliderEl.querySelector('.noUi-handle');
  if (handle) {
    const obs = new MutationObserver(() => {
      const v = parseInt(handle.getAttribute('aria-valuenow'), 10);
      if (!Number.isNaN(v)) {
        if (matchesInput) matchesInput.value = v;
        if (wc) wc.textContent = v;
      }
      updatePlacementGuarantee();
    });

    obs.observe(handle, { attributes: true, attributeFilter: ['aria-valuenow'] });
    updatePlacementGuarantee();
    return true;
  }

  return false;
}

// Rank listeners
document.querySelectorAll('input[name="start_tier"]').forEach(radio => {
  radio.addEventListener('change', updatePlacementGuarantee);
});

// Bind slider (retry bis init fertig ist)
if (!bindSliderUpdates()) {
  let tries = 0;
  const iv = setInterval(() => {
    tries++;
    if (bindSliderUpdates() || tries >= 50) clearInterval(iv);
  }, 100);
}

// extra: falls input manuell geändert wird
const matchesInput = document.getElementById('matches_count');
if (matchesInput) {
  matchesInput.addEventListener('input', updatePlacementGuarantee);
  matchesInput.addEventListener('change', updatePlacementGuarantee);
}

// initial
updatePlacementGuarantee();
</script>

<?php if ($isClassic): ?>
<style>
.boost-form.lol-classic-page .win-boost .card-header-rank,
.boost-form.lol-classic-page .win-boost .current-rank-img{width:92px!important;height:92px!important;object-fit:contain!important;max-width:none!important;}
.boost-form.lol-classic-page .win-boost .rank-btn img{width:50px!important;height:50px!important;min-width:50px!important;object-fit:contain!important;max-width:none!important;}
.boost-form.lol-classic-page .win-boost .rank-btn{min-width:68px!important;min-height:68px!important;}
.boost-form.lol-classic-page .summary-wrapper .rank-box .current-summary-rank-img{width:74px!important;height:74px!important;object-fit:contain!important;max-width:none!important;}
</style>
<script>
(function(){
    var base = '<?= ASSET_URL ?>/website/images/lol-classic/ranks/';
    var tierNames = {0:'Unranked',1:'Salt',2:'Wood',3:'Silver',4:'Gold',5:'Platinum',6:'Diamond',7:'Legend'};
    function file(tier, division){
        tier = parseInt(tier || 0, 10);
        var names = {0:'unranked',1:'salt',2:'wood',3:'silver',4:'gold',5:'platinum',6:'diamond',7:'legend'};
        return (names[tier] || 'salt') + '.webp';
    }
    function label(tier, division){
        tier = parseInt(tier || 0, 10);
        division = parseInt(division || 4, 10);
        var divs = {4:'IV',3:'III',2:'II',1:'I'};
        if (tier === 0 || tier === 7) return tierNames[tier] || 'Unranked';
        return (tierNames[tier] || 'Salt') + ' ' + (divs[division] || 'IV');
    }
    function checked(name, fallback){
        var input = document.querySelector('input[name="' + name + '"]:checked');
        return input ? input.value : fallback;
    }
    function syncClassicPlacementIcons(){
        var tier = checked('start_tier', 0), division = checked('start_division', 5);
        var src = base + file(tier, division);
        document.querySelectorAll('.current-rank-img, .current-summary-rank-img').forEach(function(img){ img.src = src; });
        document.querySelectorAll('.current-summary-rank-name').forEach(function(el){ el.textContent = label(tier, division); });
        var divBox = document.getElementById('start_divisions');
        if (divBox) divBox.style.display = (parseInt(tier, 10) === 0 || parseInt(tier, 10) === 7) ? 'none' : '';
    }
    ['change','click','input'].forEach(function(evt){
        document.addEventListener(evt, function(e){
            if (e.target && e.target.matches('input[name="start_tier"],input[name="start_division"]')) {
                requestAnimationFrame(syncClassicPlacementIcons);
            }
        }, true);
    });
    document.addEventListener('DOMContentLoaded', syncClassicPlacementIcons);
    setTimeout(syncClassicPlacementIcons, 150);
    setTimeout(syncClassicPlacementIcons, 700);
})();
</script>
<?php endif; ?>
