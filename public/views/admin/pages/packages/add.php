<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add Package - Admin Area | LoLBoost.gg', 'h1' => 'Add Package', 'description' => 'Create new LoL package.'], 'contain'=>true]) ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */
.pkg-form-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    align-items: center;
    gap: 1rem;
    padding: .9rem 0;
    border-bottom: 1px solid #2f3235;
}
.pkg-form-row.top-align { align-items: flex-start; padding-top: 1rem; }
.pkg-form-row:last-child { border-bottom: none; }
.pkg-form-label {
    font-size: .82rem; font-weight: 600; color: #91989e;
}
.pkg-form-label small {
    display: block; font-size: .72rem; color: #555d65; font-weight: 400; margin-top: .15rem;
}

/* ── Custom game dropdown ── */
.game-dropdown-wrap { position: relative; }
.game-dropdown-btn {
    display: flex; align-items: center; gap: .6rem;
    width: 100%; padding: .5rem .85rem;
    background: var(--bs-body-bg, #1e2022);
    border: 1px solid #2f3235; border-radius: .5rem;
    color: #c5c8cc; font-size: .88rem; cursor: pointer;
    transition: border-color .15s;
    text-align: left;
}
.game-dropdown-btn:hover, .game-dropdown-btn[aria-expanded="true"] { border-color: #5c4ae3; }
.game-dropdown-btn .chevron { margin-left: auto; color: #91989e; font-size: .75rem; transition: transform .2s; }
.game-dropdown-btn[aria-expanded="true"] .chevron { transform: rotate(180deg); }
.game-dropdown-menu {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #25282a; border: 1px solid #2f3235; border-radius: .5rem;
    overflow: hidden; z-index: 200; box-shadow: 0 8px 24px rgba(0,0,0,.35);
}
.game-dropdown-menu.show { display: block; }
.game-dropdown-item {
    display: flex; align-items: center; gap: .6rem;
    padding: .6rem .85rem; cursor: pointer; font-size: .88rem; color: #c5c8cc;
    border: none; background: transparent; width: 100%; text-align: left;
    transition: background .12s;
}
.game-dropdown-item:hover { background: rgba(92,74,227,.12); color: #fff; }
.game-dropdown-item img { width: 20px; height: 20px; border-radius: 5px; object-fit: contain; }

/* Ranked ready + custom features */
.pkg-ready-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.pkg-ready-option{display:flex;gap:.7rem;align-items:flex-start;padding:.8rem;border-radius:.7rem;background:#1e2022;border:1px solid #2f3235;cursor:pointer;}
.pkg-ready-option:hover{border-color:#5c4ae3;}
.pkg-ready-option input{margin-top:.18rem;}
.pkg-ready-option strong{display:block;color:#fff;font-size:.86rem;}
.pkg-ready-option small{display:block;color:#91989e;font-size:.72rem;line-height:1.35;margin-top:.15rem;}
.pkg-feature-row{display:flex;gap:.5rem;align-items:center;}
.pkg-feature-row .form-control{flex:1;}
.pkg-add-feature{width:max-content;}
@media(max-width:768px){.pkg-form-row{grid-template-columns:1fr}.pkg-ready-grid{grid-template-columns:1fr}}
</style>

<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_add_package">

    <div class="card">
        <div class="card-header">
            <h5 class="card-header-title mb-0">
                <i class="fa-duotone fa-plus me-2"></i>New Account Package
            </h5>
        </div>

        <div class="card-body pt-2 pb-0">

            <!-- Game -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Game</label>
                <div>
                    <!-- Hidden real select for form submit -->
                    <select name="game_id" id="game_id" class="d-none">
                        <option value="1" selected>League of Legends</option>
                        <option value="2">Valorant</option>
                    </select>

                    <div class="game-dropdown-wrap" id="gameDropdown">
                        <button type="button" class="game-dropdown-btn" id="gameDropdownBtn" aria-haspopup="listbox" aria-expanded="false">
                            <img id="gameDropdownIcon"
                                 src="/public/assets/website/images/icons/league-of-legends.png"
                                 alt="" style="width:22px;height:22px;border-radius:6px;object-fit:contain;">
                            <span id="gameDropdownText">League of Legends</span>
                            <i class="fa-duotone fa-chevron-down chevron"></i>
                        </button>
                        <div class="game-dropdown-menu" id="gameDropdownMenu" role="listbox">
                            <button type="button" class="game-dropdown-item"
                                    data-value="1" data-text="League of Legends"
                                    data-icon="/public/assets/website/images/icons/league-of-legends.png">
                                <img src="/public/assets/website/images/icons/league-of-legends.png" alt="">
                                League of Legends
                            </button>
                            <button type="button" class="game-dropdown-item"
                                    data-value="2" data-text="Valorant"
                                    data-icon="/public/assets/website/images/icons/valorant.png">
                                <img src="/public/assets/website/images/icons/valorant.png" alt="">
                                Valorant
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Name
                    <small>Displayed on the shop</small>
                </label>
                <input type="text" class="form-control" name="name"
                       placeholder="e.g. EUW | Level 30 Fresh MMR">
            </div>

            <!-- Rank -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">Rank</label>
                <select name="rank" class="form-select" id="rankLabel"></select>
            </div>

            <!-- Server -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Server
                    <small>LoL: EUW / EUNE / NA &nbsp;|&nbsp; Valorant: EU / NA</small>
                </label>
                <select name="server" class="form-select" id="serverLabel"></select>
            </div>


            <!-- Ranked Ready -->
            <div class="pkg-form-row top-align">
                <label class="pkg-form-label" style="padding-top:.35rem;">
                    Ranked Status
                    <small>Shown clearly on account package cards</small>
                </label>
                <div class="pkg-ready-grid">
                    <label class="pkg-ready-option">
                        <input type="radio" name="ranked_ready" value="1" checked>
                        <span>
                            <strong><i class="fa-duotone fa-circle-check me-1"></i>Ranked Ready</strong>
                            <small>Account can directly play ranked.</small>
                        </span>
                    </label>
                    <label class="pkg-ready-option">
                        <input type="radio" name="ranked_ready" value="0">
                        <span>
                            <strong><i class="fa-duotone fa-triangle-exclamation me-1"></i>Not Ranked Ready</strong>
                            <small>Shows: requires 10 normals.</small>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Price -->
            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Price (€)
                    <small>Base sale price</small>
                </label>
                <input type="number" step="0.01" min="0" class="form-control"
                       name="price" value="19.99" placeholder="19.99">
            </div>

            <!-- Features -->
            <div class="pkg-form-row top-align">
                <label class="pkg-form-label" style="padding-top:.35rem;">
                    Features
                    <small>Shown in package card</small>
                </label>
                <div class="d-flex flex-column gap-2" id="pkgFeaturesWrap">
                    <div class="pkg-feature-row"><input type="text" class="form-control" name="features[]" placeholder="Feature #1 – Blue Essence / RP"></div>
                    <div class="pkg-feature-row"><input type="text" class="form-control" name="features[]" placeholder="Feature #2 – Fresh MMR"></div>
                    <div class="pkg-feature-row"><input type="text" class="form-control" name="features[]" placeholder="Feature #3 – Warranty"></div>
                    <div class="pkg-feature-row"><input type="text" class="form-control" name="features[]" placeholder="Feature #4 – Full Access"></div>
                    <button type="button" class="btn btn-sm btn-soft-primary pkg-add-feature" id="pkgAddFeature">
                        <i class="fa-duotone fa-plus me-1"></i>Add custom feature
                    </button>
                </div>
            </div>

        </div><!-- /card-body -->

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label"><i class="fa-duotone fa-plus me-1"></i>Add Package</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
        </div>
    </div>
</form>

<script>
(function () {
    var gameSelect  = document.getElementById('game_id');
    var serverSel   = document.getElementById('serverLabel');
    var rankSel     = document.getElementById('rankLabel');
    var dd          = document.getElementById('gameDropdown');
    var ddBtn       = document.getElementById('gameDropdownBtn');
    var ddMenu      = document.getElementById('gameDropdownMenu');
    var ddIcon      = document.getElementById('gameDropdownIcon');
    var ddText      = document.getElementById('gameDropdownText');

    var LOL_TIERS = {0:'Unranked',1:'Iron',2:'Bronze',3:'Silver',4:'Gold',5:'Platinum',6:'Emerald',7:'Diamond',8:'Master',9:'Grandmaster',10:'Challenger'};
    var VAL_TIERS = {0:'Unranked',1:'Iron',2:'Bronze',3:'Silver',4:'Gold',5:'Platinum',6:'Diamond',7:'Ascended',8:'Immortal',9:'Radiant'};

    function closeMenu() { ddMenu.classList.remove('show'); ddBtn.setAttribute('aria-expanded','false'); }
    function openMenu()  { ddMenu.classList.add('show');    ddBtn.setAttribute('aria-expanded','true');  }

    ddBtn.addEventListener('click', function (e) { e.preventDefault(); ddMenu.classList.contains('show') ? closeMenu() : openMenu(); });
    document.addEventListener('click', function (e) { if (dd && !dd.contains(e.target)) closeMenu(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

    ddMenu.addEventListener('click', function (e) {
        var item = e.target.closest('[data-value]');
        if (!item) return;
        var val  = item.getAttribute('data-value');
        var text = item.getAttribute('data-text');
        var icon = item.getAttribute('data-icon');
        gameSelect.value   = val;
        ddIcon.src         = icon;
        ddText.textContent = text;
        gameSelect.dispatchEvent(new Event('change', {bubbles: true}));
        closeMenu();
    });

    function populateRanks(gameId) {
        rankSel.innerHTML = '';
        var tiers = parseInt(gameId, 10) === 2 ? VAL_TIERS : LOL_TIERS;
        Object.keys(tiers).forEach(function (k) {
            var o = document.createElement('option');
            o.value = k; o.textContent = tiers[k];
            rankSel.appendChild(o);
        });
        rankSel.value = '0';
    }

    function populateServers(gameId) {
        serverSel.innerHTML = '';
        if (parseInt(gameId, 10) === 2) {
            serverSel.insertAdjacentHTML('beforeend',
                '<option value="eu">EU</option><option value="na">North America</option>');
        } else {
            serverSel.insertAdjacentHTML('beforeend',
                '<option value="euw">EU-West</option>' +
                '<option value="eune">EU-Nordic & East</option>' +
                '<option value="na">North America</option>');
        }
    }

    function onGameChange(gameId) {
        populateRanks(gameId);
        populateServers(gameId);
        // sync dropdown icon/text
        if (parseInt(gameId, 10) === 2) {
            ddIcon.src = '/public/assets/website/images/icons/valorant.png';
            ddText.textContent = 'Valorant';
        } else {
            ddIcon.src = '/public/assets/website/images/icons/league-of-legends.png';
            ddText.textContent = 'League of Legends';
        }
    }

    gameSelect.addEventListener('change', function () { onGameChange(this.value); });
    onGameChange(gameSelect.value); // initial

    var addFeatureBtn = document.getElementById('pkgAddFeature');
    var featuresWrap = document.getElementById('pkgFeaturesWrap');
    if (addFeatureBtn && featuresWrap) {
        addFeatureBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'pkg-feature-row';
            row.innerHTML = '<input type="text" class="form-control" name="features[]" placeholder="Custom feature"><button type="button" class="btn btn-icon btn-sm btn-soft-danger pkg-remove-feature" aria-label="Remove"><i class="fa-duotone fa-xmark"></i></button>';
            featuresWrap.insertBefore(row, addFeatureBtn);
            row.querySelector('input').focus();
        });
        featuresWrap.addEventListener('click', function(e){
            var btn = e.target.closest('.pkg-remove-feature');
            if(btn) btn.closest('.pkg-feature-row').remove();
        });
    }
})();
</script>
