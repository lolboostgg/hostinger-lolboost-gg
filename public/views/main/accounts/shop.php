<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'Blog.gif']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
    /* ====== Base ====== */
    [data-user-theme='dark'] .bg-grey {
        background-color: #101223 !important;
        color: #d1d5db !important;
    }

    [data-user-theme='blue'] .bg-grey {
        background-color: var(--blue-bg-accent) !important;
        color: #f3f4f6 !important;
    }

    .rank-icon {
        width: 36px;
        height: 36px;
    }

    .card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        transition: border-color .2s ease, transform .2s ease;
    }

    .card:hover {
        border-color: #d1d5db;
        transform: translateY(-2px);
    }

    .main-image {
        max-height: 250px;
        object-fit: cover;
    }

    .input-icon {
        position: relative;
        overflow: visible;
    }

    @media (min-width: 768px) {
        #accordionDefault .accordion-body {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
    }

    /* Accordion + Border je Theme */
    .accordion-item,
    .accordion-button,
    .accordion-body {
        background-color: #ffffff;
    }

    .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .accordion-button {
        border-bottom: 1px solid #e5e7eb;
    }

    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }

    [data-user-theme="blue"] .accordion-item,
    [data-user-theme="blue"] .accordion-button {
        background-color: #1c225b !important;
        color: #e9d1c2 !important;
        border-color: rgba(255, 255, 255, .15) !important;
    }

    [data-user-theme="blue"] .accordion-body {
        background-color: #1c225b !important;
        color: #e9d1c2 !important;
    }

    [data-user-theme="dark"] .accordion-item,
    [data-user-theme="dark"] .accordion-button {
        background-color: #1c1f38 !important;
        color: #cadde7 !important;
        border-color: rgba(255, 255, 255, .12) !important;
    }

    [data-user-theme="dark"] .accordion-body {
        background-color: #1c1f38 !important;
        color: #cadde7 !important;
    }

    /* Dropdowns nicht ins Listing laufen lassen */
    .filter-area {
        position: relative;
        z-index: 2;
        margin-bottom: 20px;
    }

    #shopItems {
        position: relative;
        z-index: 1;
    }

    .accordion-body {
        overflow: visible;
    }

    /* TomSelect – Theme-Erbigkeit */
    .ts-wrapper .ts-control {
        color: inherit;
    }

    .ts-wrapper .ts-control>input {
        color: inherit;
    }

    .ts-wrapper .ts-control>input::placeholder {
        opacity: .85;
    }

    [data-user-theme="blue"] .ts-wrapper .ts-control>input::placeholder,
    [data-user-theme="blue"] .ts-wrapper .ts-control .item {
        color: #e9d1c2 !important;
    }

    [data-user-theme="blue"] .ts-dropdown {
        background: #151a48;
        border-color: rgba(255, 255, 255, .15);
        color: #e9d1c2;
    }

    [data-user-theme="dark"] .ts-wrapper .ts-control>input::placeholder,
    [data-user-theme="dark"] .ts-wrapper .ts-control .item {
        color: #cadde7 !important;
    }

    [data-user-theme="dark"] .ts-dropdown {
        background: #12152c;
        border-color: rgba(255, 255, 255, .12);
        color: #cadde7;
    }

    /* Option-Layouts mit Icon + Text */
    .ts-rank-option,
    .ts-rank-item,
    .ts-role-option,
    .ts-role-item,
    .ts-delivery-option,
    .ts-delivery-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ts-rank-option img,
    .ts-rank-item img,
    .ts-role-option img,
    .ts-role-item img {
        width: 18px;
        height: 18px;
        display: inline-block;
    }

    .ts-delivery-option i,
    .ts-delivery-item i {
        width: 18px;
        text-align: center;
    }

    /* Icons fix: neben Feld + immer sichtbar */
    .input-icon .icon-left {
        position: absolute;
        left: .75rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        z-index: 3;
    }

    .input-icon .ts-wrapper.single .ts-control,
    .input-icon .form-control:not(.ts-control) {
        padding-left: 2.2rem !important;
    }

    /* Single-Select neutral – kein heller Chip */
    .ts-wrapper.single .ts-control .item {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .ts-wrapper.single .ts-control {
        background: transparent;
        border-color: #e5e7eb;
    }

    .ts-wrapper.single.focus .ts-control {
        border-color: #c7cdd6;
        box-shadow: none;
    }

    .ts-dropdown .active {
        background: rgba(0, 0, 0, .06) !important;
    }

    [data-user-theme="blue"] .ts-dropdown .active {
        background: rgba(255, 255, 255, .08) !important;
    }

    [data-user-theme="dark"] .ts-dropdown .active {
        background: rgba(255, 255, 255, .06) !important;
    }

    #loading-spinner {
        margin-top: 2rem;
        align-items: center;
        justify-content: center;
    }

    .loader {
        width: 35px;
        aspect-ratio: 1;
        border-radius: 50%;
        border: 4px solid;
        border-color: #5b67f0 #0000;
        animation: l1 1s infinite;
    }

    @keyframes l1 {
        to {
            transform: rotate(.5turn)
        }
    }

    .ts-dropdown .option[data-value=""] {
        display: none !important;
    }
</style>
<?= $this->stop() ?>

<div class="container-fluid my-3 my-md-5">
    <div class="row px-3 px-md-5">

        <!-- NAV TABS -->
        <div class="col-12 mb-3">
            <ul class="nav nav-tabs justify-content-center">
                <!-- SMURF LEFT -->
                <li class="nav-item">
                    <a href="/lol/premium-accounts" class="nav-link justify-content-center">
                        <i class="fa-solid fa-user-ninja me-2" style="font-size:28px; width:28px; height:28px;"></i>
                        <span>Smurf Accounts</span>
                    </a>
                </li>
                <!-- RANKED RIGHT -->
                <li class="nav-item">
                    <a href="/lol/accounts" class="nav-link justify-content-center active">
                        <i class="fa-solid fa-trophy me-2" style="font-size:28px; width:28px; height:28px;"></i>
                        <span>Ranked Accounts</span>
                    </a>
                </li>

            </ul>
        </div>

        <!-- FILTER -->
        <div class="col-12 filter-area">
            <div class="card mb-3">
                <div class="accordion" id="accordionDefault">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-filter text-primary"></i>
                                    <span class="ms-1">Filters</span>
                                </h5>
                            </button>
                        </h3>
                        <div class="accordion-collapse collapse show" id="collapseOne" aria-labelledby="headingOne"
                            data-bs-parent="#accordionDefault">
                            <div class="accordion-body">
                                <form id="shopFilters">
                                    <input type="hidden" name="action" value="account_shop_filters">

                                    <!-- Reihe 1 -->
                                    <div class="row">
                                        <div class="col-12 col-md-3 mb-2">
                                            <div class="input-icon">
                                                <i class="fa-solid text-primary fa-medal icon-left"></i>
                                                <select id="rankFilter" class="js-select form-control" name="ranks[]"
                                                    placeholder="Select Rank" aria-label="Rank">
                                                    <!-- LEERE OPTION hinzugefügt -->
                                                    <option value=""></option>
                                                    <!-- Ranks 0..10 (Unranked .. Challenger) -->
                                                    <option value="0">Unranked</option>
                                                    <option value="1">Iron</option>
                                                    <option value="2">Bronze</option>
                                                    <option value="3">Silver</option>
                                                    <option value="4">Gold</option>
                                                    <option value="5">Platinum</option>
                                                    <option value="6">Emerald</option>
                                                    <option value="7">Diamond</option>
                                                    <option value="8">Master</option>
                                                    <option value="9">Grandmaster</option>
                                                    <option value="10">Challenger</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 mb-2">
                                            <div class="input-icon">
                                                <i class="fa-solid text-primary fa-globe icon-left"></i>
                                                <select id="serverFilter" class="js-select form-control"
                                                    name="servers[]" placeholder="Select Server" aria-label="Server">
                                                    <option value=""></option>
                                                    <?= util_load_server_select() ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 mb-2">
                                            <div class="input-icon">
                                                <i class="fa-solid text-primary fa-asterisk icon-left"></i>
                                                <select id="rolesFilter" class="js-select form-control" name="roles[]"
                                                    placeholder="Select Roles" aria-label="Roles">
                                                    <!-- LEERE OPTION hinzugefügt -->
                                                    <option value=""></option>
                                                    <option value="TopLane">TopLane</option>
                                                    <option value="Jungle">Jungle</option>
                                                    <option value="MidLane">MidLane</option>
                                                    <option value="AdCarry">AdCarry</option>
                                                    <option value="Support">Support</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 mb-2">
                                            <div class="input-icon">
                                                <i class="fa-solid text-primary fa-truck icon-left"></i>
                                                <select id="deliveryFilter" class="delivery-select form-control"
                                                    name="delivery_type" placeholder="Select Delivery Type"
                                                    aria-label="Delivery Type">
                                                    <option value=""></option>
                                                    <option value="instant">Instant Delivery</option>
                                                    <option value="manual">Manual Delivery</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reihe 2 -->
                                    <div class="row">
                                        <div class="col-12 col-md-9 mb-2">
                                            <div class="input-icon">
                                                <i class="fa-solid text-primary fa-magnifying-glass icon-left"></i>
                                                <input type="text" name="search" class="form-control"
                                                    placeholder="Search">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 mb-2">
                                            <button type="button" class="btn btn-primary w-100" id="resetFilters">
                                                <i class="fa-solid fa-rotate-left me-2"></i> Reset Filters
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- /.accordion-body -->
                        </div><!-- /.collapse -->
                    </div><!-- /.accordion-item -->
                </div><!-- /.accordion -->
            </div><!-- /.card -->
        </div><!-- /.filter-area -->

        <!-- LISTING -->
        <div class="col-12 order-2">
            <div class="row" id="shopItems">
                <?php foreach ($data as $account): ?>
                    <div class="col-12 col-md-4" data-slug="<?= $account['slug'] ?>">
                        <a href="/lol/account/<?= $account['slug'] ?>" class="text-decoration-none text-dark">
                            <div class="card mb-3">
                                <div class="card-body position-relative">
                                    <h5 class="mb-0">
                                        <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>"
                                            class="rank-icon" alt="Rank Icon">
                                        <?=
                                            strtoupper($account['server']) . ' - ' .
                                            util_get_lol_rank($account['current_rank']) .
                                            (
                                                $account['current_rank'] != 0
                                                ? (
                                                    $account['current_lp'] !== null && $account['current_lp'] != 0
                                                    ? ' ' . $account['current_lp'] . 'LP'
                                                    : ' ' . util_format_lol_division($account['current_division'])
                                                )
                                                : ''
                                            )
                                            ?>
                                    </h5>
                                    <p class="my-2 excerpt">
                                        <?= implode(' ', array_slice(explode(' ', $account['description']), 0, 20)) ?>
                                    </p>
                                    <div class="position-relative">
                                        <?php
                                        $images = json_decode($account['images'], true);
                                        $firstImage = !empty($images) ? $images[0] : '';
                                        $remainingCount = max(0, count($images) - 1);
                                        ?>
                                        <img src="<?= $firstImage ?: ASSET_URL . '/core/main/img/banners/account.jpg' ?>"
                                            class="img-fluid rounded d-block w-100 main-image" alt="Account image">
                                        <div class="position-absolute bottom-0 end-0">
                                            <span class="badge bg-grey mb-2 me-2">
                                                <i class="fas fa-images me-1"></i> +<?= $remainingCount ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 mt-3">
                                        <span class="badge bg-grey"><i class="fas fa-helmet-battle"></i>
                                            <?= count(explode('|', $account['champions'])) ?> Champions</span>
                                        <span class="badge bg-grey"><i class="fas fa-masks-theater"></i>
                                            <?= count(explode('|', $account['skins'])) ?> Skins</span>
                                        <span class="badge bg-grey"><i class="fas fa-arrow-turn-up"></i> Level
                                            <?= $account['level'] ?></span>
                                        <span class="badge bg-grey"><i class="fas fa-gem"></i>
                                            <?= $account['blue_essence'] ?> BE</span>
                                        <span class="badge bg-grey"><i class="fas fa-hand-back-fist"></i>
                                            <?= $account['riot_points'] ?> RP</span>
                                    </div>
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="d-flex align-items-end gap-2">
                                            <span
                                                class="fw-bold price-eur">€<?= util_format_price_display($account['price']) ?></span>
                                            <small class="text-muted fw-medium">EUR</small>
                                        </div>
                                        <a href="/lol/account/<?= $account['slug'] ?>"
                                            class="btn btn-primary btn-sm px-3">Buy Now</a>
                                    </div>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <?php if ($account['delivery_type'] === 'instant'): ?>
                                            <i class="fas fa-bolt" data-bs-toggle="tooltip" title="Instant Delivery"></i>
                                        <?php else: ?>
                                            <i class="fas fa-truck" data-bs-toggle="tooltip" title="Manual Delivery"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach ?>
            </div>

            <div id="loading-spinner" class="text-center" style="display: none;">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>
<script>
    // =================== GLOBAL STATE ===================
    let currentPage = 1;
    // Variante: totalPages entfernt – Ende wird über leere Server-Antwort erkannt
    let isLoading = false;
    let noMoreResults = false; // -> blockt Infinite-Scroll bei: keine Treffer / Ende erreicht

    // Prüft, ob aktuell Filter aktiv sind (liest ausschließlich TomSelect + Search)
    function hasActiveFilters() {
        const form = document.getElementById('shopFilters');
        if (!form) return false;

        const search = form.querySelector('input[name="search"]')?.value?.trim() || '';

        const rank = document.getElementById('rankFilter')?.tomselect?.getValue() || '';
        const server = document.getElementById('serverFilter')?.tomselect?.getValue() || '';
        const roles = document.getElementById('rolesFilter')?.tomselect?.getValue() || '';
        const delivery = document.getElementById('deliveryFilter')?.tomselect?.getValue() || '';

        return Boolean(search || rank || server || roles || delivery);
    }

    // =================== INFINITE SCROLL ===================
    function loadMoreAccounts() {
        if (isLoading || noMoreResults || hasActiveFilters()) return;

        isLoading = true;
        currentPage++;
        $('#loading-spinner').css('display', 'flex');

        $.post('<?= AJAX_URL ?>', { action: 'load_accounts', page: currentPage }, (response) => {
            const trimmed = (response || '').trim();
            if (!trimmed) {
                noMoreResults = true;
                $('#loading-spinner').css('display', 'none');
                isLoading = false;
                return;
            }
            const hasCard = /class="card\b/i.test(trimmed);
            if (!hasCard) {
                noMoreResults = true;
                $('#loading-spinner').css('display', 'none');
                isLoading = false;
                return;
            }
            setTimeout(() => {
                // Response in ein temporäres DOM packen
                const $wrap = $('<div>').html(response);

                // Alle Elemente mit data-slug prüfen – wenn der slug schon existiert: entfernen
                $wrap.find('[data-slug]').each(function () {
                    const slug = this.dataset.slug;
                    if ($('#shopItems [data-slug="' + slug + '"]').length) {
                        $(this).remove();
                    }
                });

                // Übrig gebliebene (neue) Karten anhängen
                const $children = $wrap.children();
                if ($children.length === 0) {
                    // Nichts Neues -> Infinite Scroll beenden
                    noMoreResults = true;
                    $('#loading-spinner').css('display', 'none');
                    isLoading = false;
                    return;
                }

                $('#shopItems').append($children);
                $('#loading-spinner').css('display', 'none');
                isLoading = false;
            }, 200);
        });
    }

    $(window).on('scroll', function () {
        if (noMoreResults || hasActiveFilters()) return;

        const boosterContainer = $('#shopItems');
        const containerBottom = boosterContainer.offset().top + boosterContainer.outerHeight();
        const windowBottom = $(window).scrollTop() + $(window).height();

        if (windowBottom >= containerBottom + 100) {
            loadMoreAccounts();
        }
    });

    /* ====== Ranks + Pfade ====== */
    const RANK_LABELS = {
        0: 'Unranked', 1: 'Iron', 2: 'Bronze', 3: 'Silver', 4: 'Gold',
        5: 'Platinum', 6: 'Emerald', 7: 'Diamond', 8: 'Master', 9: 'Grandmaster', 10: 'Challenger'
    };
    const RANK_INDEX = {
        '0': 0, '1': 1, '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9, '10': 10,
        'unranked': 0, 'iron': 1, 'bronze': 2, 'silver': 3, 'gold': 4, 'platinum': 5, 'emerald': 6, 'diamond': 7, 'master': 8, 'grandmaster': 9, 'challenger': 10
    };
    const RANK_IMG_BASE = "<?= ASSET_URL ?>/core/main/img/lol/ranks/mini/";
    const ROLES_IMG_BASE = "<?= ASSET_URL ?>/core/main/img/lol/roles/";

    function initTomSelect(el, opts) {
        const ts = new TomSelect(el, opts);
        const filterArea = document.querySelector('.filter-area');
        function addSpace() {
            const dd = ts.dropdown; if (!dd || !filterArea) return;
            const h = dd.scrollHeight || 0; filterArea.style.paddingBottom = Math.max(24, h + 16) + 'px';
        }
        function removeSpace() {
            const openCount = document.querySelectorAll('.ts-dropdown[style*="display: block"]').length;
            if (openCount <= 1 && filterArea) filterArea.style.paddingBottom = '';
        }
        ts.on('dropdown_open', addSpace);
        ts.on('dropdown_close', removeSpace);

        ts.clear(true); // keine Default-Selection

        ts.on('change', () => {
            const form = document.getElementById('shopFilters');
            if (form) form.dispatchEvent(new Event('change', { bubbles: true }));
        });

        return ts;
    }

    document.addEventListener('DOMContentLoaded', function () {
        /* ====== Server & Roles (Single) ====== */
        ['serverFilter', 'rolesFilter'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;

            const renderer = (id === 'rolesFilter')
                ? {
                    option: (data, escape) => {
                        const val = String(data.value || data.text || '');
                        const img = ROLES_IMG_BASE + val + '.png';
                        return `<div class="ts-role-option"><img src="${img}" alt="" />${escape(data.text)}</div>`;
                    },
                    item: (data, escape) => {
                        const val = String(data.value || data.text || '');
                        const img = ROLES_IMG_BASE + val + '.png';
                        return `<div class="ts-role-item"><img src="${img}" alt="" />${escape(data.text)}</div>`;
                    }
                }
                : {}; // Server ohne spezielle Icons

            initTomSelect(el, {
                maxItems: 1,
                hideSelected: true,
                allowEmptyOption: true,
                persist: false,
                create: false,
                items: [],
                dropdownParent: el.closest('.input-icon'),
                render: renderer
            });
        });

        /* ====== Delivery (Single) mit Icons ====== */
        const deliveryEl = document.getElementById('deliveryFilter');
        if (deliveryEl) {
            initTomSelect(deliveryEl, {
                maxItems: 1,
                hideSelected: true,
                allowEmptyOption: true,
                persist: false,
                create: false,
                items: [],
                dropdownParent: deliveryEl.closest('.input-icon'),
                render: {
                    option: function (data, escape) {
                        const v = (data.value || '').toLowerCase();
                        const icon = v === 'instant' ? 'bolt' : (v === 'manual' ? 'truck' : '');
                        const iconHtml = icon ? `<i class="fas fa-${icon}"></i>` : '';
                        return `<div class="ts-delivery-option">${iconHtml}${escape(data.text)}</div>`;
                    },
                    item: function (data, escape) {
                        const v = (data.value || '').toLowerCase();
                        const icon = v === 'instant' ? 'bolt' : (v === 'manual' ? 'truck' : '');
                        const iconHtml = icon ? `<i class="fas fa-${icon}"></i>` : '';
                        return `<div class="ts-delivery-item">${iconHtml}${escape(data.text)}</div>`;
                    }
                }
            });
        }

        /* ====== Rank (Single) mit Icons + Labels ====== */
        const rankEl = document.getElementById('rankFilter');
        if (rankEl) {
            initTomSelect(rankEl, {
                maxItems: 1,
                hideSelected: true,
                allowEmptyOption: true,
                persist: false,
                create: false,
                items: [],
                dropdownParent: rankEl.closest('.input-icon'),
                render: {
                    option: function (data, escape) {
                        const key = String((data.value || data.text || '')).toLowerCase();
                        const idx = (RANK_INDEX[key] !== undefined) ? RANK_INDEX[key] : 0;
                        const img = RANK_IMG_BASE + idx + '.png';
                        const label = RANK_LABELS[idx] || escape(data.text);
                        return `<div class="ts-rank-option"><img src="${img}" alt="" />${label}</div>`;
                    },
                    item: function (data, escape) {
                        const key = String((data.value || data.text || '')).toLowerCase();
                        const idx = (RANK_INDEX[key] !== undefined) ? RANK_INDEX[key] : 0;
                        const img = RANK_IMG_BASE + idx + '.png';
                        const label = RANK_LABELS[idx] || escape(data.text);
                        return `<div class="ts-rank-item"><img src="${img}" alt="" />${label}</div>`;
                    }
                }
            });
        }
    });

    // =================== AJAX & ACCORDION ===================
    $(document).ready(function () {
        function serializeWithoutEmpty($form) {
            const arr = $form.serializeArray().filter(p => String(p.value).trim() !== '');
            const params = new URLSearchParams();
            arr.forEach(p => params.append(p.name, p.value));
            return params.toString();
        }

        // Filter ändern -> Ergebnisliste + Paging neu setzen
        $('#shopFilters').on('change', function () {
            const formQS = serializeWithoutEmpty($(this));
            currentPage = 1;
            noMoreResults = false;

            $.ajax({
                url: '<?= AJAX_URL ?>',
                type: 'POST',
                data: formQS,
                success: function (response) {
                    const trimmed = (response || '').trim();
                    if (!trimmed) {
                        $('#shopItems').html(`
                            <div class="col-12">
                              <div class="text-center py-5">
                                <i class="fa-regular fa-face-frown fa-2xl mb-3"></i>
                                <p class="mb-0">Keine Accounts gefunden. Bitte die Filter anpassen.</p>
                              </div>
                            </div>
                        `);
                        noMoreResults = true; // Blocke Infinite-Scroll
                        return;
                    }
                    $('#shopItems').html(response);
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching filtered accounts:', error);
                }
            });
        });

        // RESET: alles wirklich leeren + eine (einzige) Anfrage auslösen
        $('#resetFilters').on('click', function () {
            const $form = $('#shopFilters');

            // HTML-Formular zurücksetzen (setzt Inputs auf ihre Defaultwerte)
            $form[0].reset();

            // Alle Selects explizit auf leere Option setzen (wichtig bei rank/roles)
            $form.find('select').each(function () {
                this.value = ''; // wählt die leere <option value="">
            });

            $form.find('.js-select, .delivery-select').each(function () {
                if (this.tomselect) {
                    this.tomselect.clear(true);          // NICHTS ausgewählt lassen
                    this.tomselect.setTextboxValue('');  // Eingabefeld leeren
                    this.tomselect.blur();               // Placeholder sofort zeigen
                }
            });

            // Padding vom Filter-Bereich zurücksetzen
            $('.filter-area').css('padding-bottom', '');

            // Paging zurücksetzen
            currentPage = 1;
            noMoreResults = false;

            // Eine Änderung triggern -> lädt default (ohne Filter) vom Server
            $form.trigger('change');
        });

        function adjustAccordion() {
            const collapse = document.getElementById('collapseOne');
            if (!collapse) return;
            const acc = bootstrap.Collapse.getInstance(collapse) || new bootstrap.Collapse(collapse, { toggle: false });
            if (window.innerWidth < 768) acc.hide(); else acc.show();
        }
        window.addEventListener('load', adjustAccordion);
        window.addEventListener('resize', adjustAccordion);
    });
</script>
<?= $this->stop() ?>