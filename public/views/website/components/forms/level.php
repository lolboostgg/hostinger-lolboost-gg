<div class="boost level-boost">
    <div class="card">
        <div class="card-header">
            <div class="text">
                <h3><?= t('Level Selection') ?></h3>
                <p><?= t('Select your current and desired level.') ?></p>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="levels">
                <input type="hidden" name="level_boost" value="1">
                <div class="lp-selector">
                    <h6><?= t('Current Level:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementLevel(startLevel)">
                            <i class="fas fa-circle-minus"></i>
                        </button>
                        <input type="text" name="start_tier" id="start_level" value="1" min="0" max="100">
                        <button type="button" onclick="incrementLevel(startLevel)">
                            <i class="fas fa-circle-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="lp-selector">
                    <h6><?= t('Desired Level:') ?></h6>
                    <div class="input-container">
                        <button type="button" onclick="decrementLevel(endLevel)">
                            <i class="fas fa-circle-minus"></i>
                        </button>
                        <input type="text" name="end_tier" id="end_level" value="30" min="0" max="100">
                        <button type="button" onclick="incrementLevel(endLevel)">
                            <i class="fas fa-circle-plus"></i>
                        </button>
                    </div>
                </div>
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
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function levelVal(id, fallback){
        var el = document.getElementById(id);
        var val = el ? parseInt(el.value || fallback, 10) : fallback;
        if (!Number.isFinite(val) || val < 1) val = fallback;
        return val;
    }
    function syncLevelSummary(){
        var start = levelVal('start_level', 1);
        var end = levelVal('end_level', 30);
        document.querySelectorAll('.level-current-summary-name').forEach(function(el){ el.textContent = 'Level ' + start; });
        document.querySelectorAll('.level-desired-summary-name').forEach(function(el){ el.textContent = 'Level ' + end; });
    }
    ['start_level','end_level'].forEach(function(id){
        var el = document.getElementById(id);
        if (!el) return;
        ['input','change','keyup'].forEach(function(evt){ el.addEventListener(evt, syncLevelSummary); });
    });
    document.addEventListener('click', function(e){
        if (e.target.closest('.level-boost button')) setTimeout(syncLevelSummary, 0);
    });
    syncLevelSummary();
    setTimeout(syncLevelSummary, 100);
    setTimeout(syncLevelSummary, 400);
})();
</script>
