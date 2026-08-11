<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<?php
$_currentAccountGameRaw = (string)($account['game'] ?? 'league-of-legends');
$_currentAccountGame = function_exists('util_account_normalize_game_slug') ? util_account_normalize_game_slug($_currentAccountGameRaw) : strtolower(trim($_currentAccountGameRaw));
$_currentAccountShort = function_exists('util_account_short_game_code') ? util_account_short_game_code($_currentAccountGame) : $_currentAccountGame;
$_sellerAccountGames = function_exists('util_get_all_games') ? util_get_all_games(true) : [];
$_allowedRaw = trim((string)($seller_data['allowed_games'] ?? ($seller['allowed_games'] ?? '')));
$_allowedSlugs = $_allowedRaw !== '' ? array_filter(array_map('trim', explode(',', $_allowedRaw))) : [];
if (!empty($_allowedSlugs)) {
    $_sellerAccountGames = array_values(array_filter($_sellerAccountGames, function($g) use ($_allowedSlugs, $_currentAccountGame) {
        $slug = (string)($g['slug'] ?? '');
        return in_array($slug, $_allowedSlugs, true) || $slug === $_currentAccountGame;
    }));
}
if (empty($_sellerAccountGames)) {
    $_sellerAccountGames = [
        ['slug'=>'league-of-legends','name'=>'League of Legends'],
        ['slug'=>'valorant','name'=>'Valorant'],
    ];
}
$_hasCurrentGame = false;
foreach ($_sellerAccountGames as $_g) { if ((string)($_g['slug'] ?? '') === $_currentAccountGame) { $_hasCurrentGame = true; break; } }
if (!$_hasCurrentGame && $_currentAccountGame !== '') {
    $_sellerAccountGames[] = ['slug'=>$_currentAccountGame, 'name'=>ucwords(str_replace('-', ' ', $_currentAccountGame))];
}
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
    .ts-wrapper {
        min-height: 42px !important;
    }

    .custom-offcanvas {
        width: 50vw !important;
    }

    .toggle-group {
        display: flex;
        border-radius: 8px;
        padding: 5px;
        width: 200px;
        position: relative;
    }

    .toggle-group input {
        display: none;
    }

    .toggle-label {
        flex: 1;
        text-align: center;
        padding: 10px;
        color: #bbb;
        font-weight: 500;
        cursor: pointer;
        transition: 0.3s;
    }

    input:checked+.toggle-label {
        background-color: #6366f1;
        color: #fff;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    /* Checked state */
    input:checked+.slider {
        background-color: #6366f1;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }

    .ts-control .item {
        color: #fff;
    }

    .ts-wrapper.multi .ts-control>div {
        background: #35383bff !important;
        color: #fff !important;
    }

    .ts-wrapper.plugin-remove_button .item .remove {
        border-left-color: #35383bff !important;
    }

    .gallery-image {
        position: relative;
        overflow: hidden;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .gallery-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
        border-radius: inherit;
    }

    .gallery-image:hover img {
        transform: scale(1.05);
    }

    .gallery-image .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(220, 53, 69, 0.3);
        /* Bootstrap danger w/opacity */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-image:hover .overlay {
        opacity: 1;
    }

    .gallery-image .btn {
        opacity: 0.9;
    }

    @media only screen and (max-width: 576px) {
        .custom-offcanvas {
            width: 100vw !important;
        }
    }

    /* Pretty validation (disable browser bubbles via novalidate) */
    .js-validation-alert { border-radius: 12px; }
    form.was-validated .form-control:invalid,
    form.was-validated .form-select:invalid {
        border-color: var(--bs-danger) !important;
    }
    .ts-wrapper.is-invalid .ts-control {
        border-color: var(--bs-danger) !important;
        box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
    }
    .form-check.is-invalid .form-check-input {
        border-color: var(--bs-danger) !important;
    }


    /* Upload box: dashboard style (click / drop / paste) */
    .account-upload-box {
        border: 2px dashed rgba(255,255,255,.12);
        border-radius: 12px;
        transition: all .2s ease;
        background: rgba(255,255,255,.02);
        cursor: pointer;
    }
    .account-upload-box:hover {
        border-color: #6366f1;
        background: rgba(99,102,241,.05);
    }
    .account-upload-box.dragover {
        border-color: #6366f1;
        background: rgba(99,102,241,.08);
    }

</style>
<?= $this->end() ?>

<?php
    // Seller ownership check (seller can only edit own accounts)
    if (defined('SELLER_ID') && isset($account['seller_id']) && (int)$account['seller_id'] !== (int)SELLER_ID) {
        ?>
        <div class="alert alert-danger mb-4">
            <strong>Access denied.</strong> You can only view and edit accounts you uploaded.
        </div>
        <a href="<?= BASE_URL ?>/seller-area/accounts" class="btn btn-primary">
            <i class="fa-duotone fa-arrow-left me-1"></i> Back to My Accounts
        </a>
        <?php
        return;
    }

    // For display only
    $uploadedBy = $account['uploaded_by'] ?? $account['seller_username'] ?? (defined('SELLER_DATA') && is_array(SELLER_DATA) ? (SELLER_DATA['username'] ?? '—') : '—');
?>
<div class="container">


    <form action="<?= BASE_URL ?>/app/core/ajax.php" method="POST" class="form ajax-form js-pretty-validate" novalidate>
        <input type="hidden" name="action" value="seller_update_selling_account">
        <input type="hidden" name="id" value="<?= $account['id'] ?>">
                <div class="alert alert-danger js-validation-alert d-none mb-3" role="alert"></div>
<div class="row">
            <div class="col-12 col-md-6">
                <h1 class="h3 mb-0">
                    <?= strtoupper($account['server']) . ' - ' . util_get_lol_rank($account['current_rank']) . ' ' . ($account['current_lp'] !== null && $account['current_lp'] != 0 ? $account['current_lp'] . 'LP' : util_format_lol_division($account['current_division'])) ?>
                </h1>
                <p class="text-muted"><?= $account['title'] ?></p>
            </div>
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="button" id="sellerUpdateBtn" class="btn btn-primary" onclick="return window.__sellerUpdateAccount && window.__sellerUpdateAccount(event);">
                        <i class="fas fa-save me-1"></i> Update Account
                    </button>
                    <button type="button" class="btn btn-danger" data-id="<?= $account['id'] ?>"
                        data-action="seller_delete_account">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-lg-2 mb-3 mb-md-0">
                <!-- Navbar -->
                <div class="navbar-expand-md">
                    <!-- Navbar Collapse -->
                    <div id="navbarVerticalNavMenuEg2" class="collapse navbar-collapse">
                        <ul id="navbarSettingsEg2"
                            class="js-sticky-block js-scrollspy nav nav-tabs nav-link-gray nav-vertical"
                            data-hs-sticky-block-options='{
                                    "parentSelector": "#navbarVerticalNavMenuEg2",
                                    "targetSelector": "#header",
                                    "breakpoint": "md",
                                    "startPoint": "#navbarVerticalNavMenuEg2",
                                    "endPoint": "#stickyBlockEndPointEg2",
                                    "stickyOffsetTop": 20
                                }'>
                            <li class="nav-item">
                                <a class="nav-link active" href="#listingInfo">1. Listing Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#gameDataInfo">2. Game Data</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#credentialsInfo">3. Credentials</a>
                            </li>
                        </ul>
                    </div>
                    <!-- End Navbar Collapse -->
                </div>
                <!-- End Navbar -->
            </div>
            <!-- End Col -->

            <div class="col-md-9 col-lg-10">
                <div class="card mb-3" id="listingInfo">
                    <div class="card-header">
                        <h4 class="mb-0">Listing Info</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($_sellerAccountGames as $_g): ?>
                            <?php
                                $_slug = (string)($_g['slug'] ?? '');
                                $_short = function_exists('util_account_short_game_code') ? util_account_short_game_code($_slug) : $_slug;
                                $_submit = in_array($_short, ['lol','val'], true) ? $_short : $_slug;
                                if (in_array($_submit, ['lol','val','league-of-legends','valorant'], true)) continue;
                                $_schemaHtml = function_exists('util_render_account_edit_fields') ? util_render_account_edit_fields($_slug, $account) : '';
                                if (trim($_schemaHtml) === '') continue;
                                $_safeClass = preg_replace('/[^a-z0-9_-]/i', '-', $_submit);
                                $_isActive = ($_slug === $_currentAccountGame || $_submit === $_currentAccountGameRaw || $_submit === $_currentAccountGame);
                            ?>
                            <div class="game-data-dynamic game-data-dynamic-<?= htmlspecialchars($_safeClass, ENT_QUOTES, 'UTF-8') ?>" data-game="<?= htmlspecialchars($_submit, ENT_QUOTES, 'UTF-8') ?>" style="<?= $_isActive ? '' : 'display:none' ?>">
                                <?= $_schemaHtml ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="row game-data-lol" style="<?= in_array($_currentAccountShort, ['lol','league-of-legends'], true) ? '' : 'display:none' ?>">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Game</label>
                                    <select class="form-select" name="game" id="sellerEditGame" onchange="switchSellerEditGame(this.value)" required>
                                        <?php foreach ($_sellerAccountGames as $_g): ?>
                                            <?php
                                                $_slug = (string)($_g['slug'] ?? 'league-of-legends');
                                                $_short = function_exists('util_account_short_game_code') ? util_account_short_game_code($_slug) : $_slug;
                                                $_submit = in_array($_short, ['lol','val'], true) ? $_short : $_slug;
                                                $_selected = ($_slug === $_currentAccountGame || $_short === $_currentAccountShort || $_submit === $_currentAccountGameRaw) ? ' selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars($_submit, ENT_QUOTES, 'UTF-8') ?>"<?= $_selected ?>><?= htmlspecialchars((string)($_g['name'] ?? $_slug), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-secondary">Dynamic account fields are loaded from game_account_schemas.</small>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Account Title</label>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="EUW - Platinum I" value="<?= $account['title'] ?>" required>
                                    <small class="text-secondary">Uploaded by: <?= htmlspecialchars((string) $uploadedBy) ?></small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="text" class="form-control" placeholder="0.00" name="price"
                                            value="<?= util_format_price_display($account['price']) ?>" required>
                                        <span class="input-group-text">EUR</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" rows="3" name="description"
                                        placeholder="Mention the details of the account here..." required><?= $account['description'] ?></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Image Gallery</label>

                                    <div id="galleryDropzone" class="account-upload-box text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa-duotone fa-images fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="mb-2">Upload Account Images</h5>
                                        <p class="text-muted mb-3">
                                            Click to select images, drag &amp; drop them here, or paste with <strong>Ctrl + V</strong>.
                                        </p>
                                        <button type="button" class="btn btn-primary btn-sm" id="selectImagesBtn">
                                            Select Images
                                        </button>

                                        <input class="form-control d-none" name="images[]" type="file" id="galleryUpload" multiple accept="image/*">
                                    </div>

                                    <small class="d-block mt-2">
                                        (PNG, JPEG, WEBP, GIF | Max 1MB Each)
                                    </small>

                                    <div id="previewGallery" class="row mt-3 g-2">
                                        <?php foreach (json_decode($account['images']) as $image): ?>
                                        <div class="gallery-image col-6 col-md-3">
                                            <img src="<?= $image ?>" alt="Image" class="img-fluid">
                                            <div class="overlay">
                                                <?php
                                                $data = json_encode(['id' => $account['id'], 'image' => $image]);
                                                ?>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    data-action="seller_delete_account_image"
                                                    data-id="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fa-duotone fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3" id="gameDataInfo">
                    <div class="card-header">
                        <h4 class="mb-0">Game Data</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Current Rank & Division -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Current Rank & Division</label>
                                    <div class="row g-2">
                                        <div class="col-12 current-rank">
                                            <select class="form-select" name="current_rank"
                                                data-placeholder="Select Current Rank" required>
                                                <?= util_load_lol_tier_select(0, 10, $account['current_rank']) ?>
                                            </select>
                                        </div>
                                        <div class="col-3 current-division d-none">
                                            <select class="form-select" name="current_division"
                                                data-placeholder="Select Current Division">
                                                <?= util_load_lol_division_select($account['current_division']) ?>
                                            </select>
                                        </div>
                                        <div class="col-3 current-lp d-none">
                                            <input type="text" class="form-control" name="current_lp"
                                                placeholder="Current LP" value="<?= $account['current_lp'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Flex Rank & Division -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Flex Rank & Division</label>
                                    <div class="row g-2">
                                        <div class="col-12 flex-rank">
                                            <select class="form-select" name="flex_rank"
                                                data-placeholder="Select Flex Rank" required>
                                                <?= util_load_lol_tier_select(0, 10, $account['flex_rank']) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 flex-division d-none">
                                            <select class="form-select" name="flex_division"
                                                data-placeholder="Select Flex Division">
                                                <?= util_load_lol_division_select($account['flex_division']) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 flex-lp d-none">
                                            <input type="text" class="form-control" name="flex_lp"
                                                placeholder="Flex LP" value="<?= $account['flex_lp'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Rank & Division -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Previous Rank & Division</label>
                                    <div class="row g-2">
                                        <div class="col-12 previous-rank">
                                            <select class="form-select" name="previous_rank"
                                                data-placeholder="Select Previous Rank" required>
                                                <?= util_load_lol_tier_select(0, 10, $account['previous_rank']) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 previous-division d-none">
                                            <select class="form-select" name="previous_division"
                                                data-placeholder="Select Previous Division">
                                                <?= util_load_lol_division_select($account['previous_division']) ?>
                                            </select>
                                        </div>
                                        <div class="col-4 previous-lp d-none">
                                            <input type="text" class="form-control" name="previous_lp"
                                                placeholder="Prev LP" value="<?= $account['previous_lp'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Server -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Server</label>
                                    <select class="form-select" name="server" required>
                                        <?= util_load_server_select($account['server']) ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Level Up Method -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Level Up Method</label>
                                    <select class="form-select" name="level_up_method" required>
                                        <option value="by_hand"
                                            <?= $account['level_up_method'] === 'by_hand' ? 'selected' : '' ?>>By Hand
                                        </option>
                                        <option value="botted"
                                            <?= $account['level_up_method'] === 'botted' ? 'selected' : '' ?>>Botted
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Level -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Level</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i
                                                class="fa-duotone fa-level-up-alt"></i></span>
                                        <input type="number" class="form-control" name="level"
                                            value="<?= $account['level'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Blue Essence -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Blue Essence</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-duotone fa-gem"></i></span>
                                        <input type="number" class="form-control" name="blue_essence"
                                            value="<?= $account['blue_essence'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Riot Points -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Riot Points</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i
                                                class="fa-duotone fa-hand-back-fist"></i></span>
                                        <input type="number" class="form-control" name="riot_points"
                                            value="<?= $account['riot_points'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Win Percentage -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Win Percentage</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-duotone fa-percent"></i></span>
                                        <input type="number" class="form-control" name="winrate_percent"
                                            value="<?= $account['winrate_percent'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Champions -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Champions</label>
                                    <select class="form-select" name="champions[]" id="champions"
                                        data-placeholder="Select Champions" multiple>
                                        <?= util_load_champions_select(explode('|', $account['champions'])) ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Skins -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Skins</label>
                                    <select class="form-select" name="skins[]" id="skins"
                                        data-placeholder="Select Skins" multiple>
                                        <?= util_get_lol_skins(explode('|', $account['skins'])) ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Roles -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Roles</label>
                                    <select class="form-select" name="roles[]" data-placeholder="Select Roles"
                                        multiple>
                                        <?= util_load_roles_select(explode('|', $account['roles'])) ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" id="credentialsInfo">
                    <div class="card-header">
                        <h4 class="mb-0">Credentials</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Delivery Type Toggle -->
                            <div class="col-12">
                                <div class="toggle-group bg-light border w-100 rounded mb-3">
                                    <input type="radio" id="instant" name="delivery_type" value="instant"
                                        <?= $account['delivery_type'] === 'instant' ? 'checked' : '' ?>>
                                    <label for="instant" class="toggle-label rounded">
                                        <i class="fa-duotone fa-bolt me-2"></i>
                                        Instant Delivery
                                    </label>

                                    <input type="radio" id="manual" name="delivery_type" value="manual"
                                        <?= $account['delivery_type'] === 'manual' ? 'checked' : '' ?>>
                                    <label for="manual" class="toggle-label rounded">
                                        <i class="fa-duotone fa-truck me-2"></i>
                                        Manual Delivery
                                    </label>
                                </div>
                            </div>

                            <!-- Login -->
                            <div class="col-12 login-details"
                                <?= $account['delivery_type'] === 'manual' ? 'style="display: none;"' : '' ?>>
                                <div class="row ">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Login</label>
                                            <input type="text" class="form-control" name="login"
                                                placeholder="Enter the account username"
                                                value="<?= $account['login'] ?>"
                                                <?= $account['delivery_type'] === 'manual' ? '' : 'required' ?>>
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="text" class="form-control" name="password"
                                                placeholder="Enter the account password"
                                                value="<?= $account['password'] ?>"
                                                <?= $account['delivery_type'] === 'manual' ? '' : 'required' ?>>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="Enter the account email" value="<?= $account['email'] ?>"
                                                <?= $account['delivery_type'] === 'manual' ? '' : 'required' ?>>
                                        </div>
                                    </div>

                                    <!-- Email Password -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email Password</label>
                                            <input type="text" class="form-control" name="email_password"
                                                placeholder="Enter the email password"
                                                value="<?= $account['email_password'] ?>"
                                                <?= $account['delivery_type'] === 'manual' ? '' : 'required' ?>>
                                        </div>
                                    </div>

                                    <!-- In-Game Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">In-Game Name</label>
                                            <input type="text" class="form-control" name="in_game_name"
                                                placeholder="Enter the in-game name"
                                                value="<?= $account['in_game_name'] ?>"
                                                <?= $account['delivery_type'] === 'manual' ? '' : 'required' ?>>
                                        </div>
                                    </div>

                                    <!-- Has 2FA -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Has 2FA</label>
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="has_2fa" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                    id="has_2fa" name="has_2fa" value="1"
                                                    <?= $account['2fa'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="has_2fa">Enable</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="login-details"
                            <?= $account['delivery_type'] === 'instant' ? 'style="display: none;"' : '' ?>>

                        <!-- Delivery Instructions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Delivery Instructions</label>
                                    <textarea class="form-control" rows="3" name="delivery_instructions"
                                        placeholder="Mention the delivery instructions here..."><?= $account['delivery_instructions'] ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Sticky End Point -->
                <div id="stickyBlockEndPointEg2"></div>
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </form>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-scrollspy/dist/hs-scrollspy.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-step-form/dist/hs-step-form.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>

<!-- JS Plugins Init. -->
<script>
    (function() {
        // INITIALIZATION OF STICKY BLOCKS
        // =======================================================
        Promise.all(Array.from(document.images)
                .filter(img => !img.complete)
                .map(img => new Promise(resolve => {
                    img.onload = img.onerror = resolve;
                })))
            .then(() => {
                new HSStickyBlock('.js-sticky-block', {
                    targetSelector: document.getElementById('header').classList.contains(
                        'navbar-fixed') ? '#header' : null
                });
            });


        // INITIALIZATION OF SCROLLSPY
        // =======================================================
        new bootstrap.ScrollSpy(document.body, {
            target: '#navbarSettingsEg2',
            offset: 10
        });

        new HSScrollspy('#navbarVerticalNavMenuEg2', {
            breakpoint: 'lg'
        });

        HSCore.components.HSTomSelect.init('select:not(#champions):not(#skins)', {
            maxOptions: null,
            shouldLoad: function() {
                return true;
            },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,
        });

        HSCore.components.HSTomSelect.init('#champions', {
            maxOptions: null,
            shouldLoad: function() {
                return true;
            },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,

            onInitialize: function() {
                let select = this;
                this.options = {};
                document.querySelectorAll("#champions option").forEach(function(option) {
                    let value = option.value;
                    let text = option.textContent;
                    let img = option.getAttribute("data-image");
                    select.addOption({
                        value: value,
                        text: text,
                        img: img
                    });
                });
            },

            render: {
                option: function(data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: 30px; height: 30px; border-radius: 5px; margin-right: 8px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                },
                item: function(data, escape) {
                    let img = data.image;

                    if (!img) {
                        // fallback from DOM
                        const optionEl = document.querySelector(
                            `#champions option[value="${data.value}"]`);
                        img = optionEl?.getAttribute('data-image') || '';
                    }

                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(img)}" style="width: 20px; height: 20px; border-radius: 5px; margin-right: 5px;">
                        <span>${escape(data.text)}</span>
                        </div>`;
                }
            }
        });

        HSCore.components.HSTomSelect.init('#skins', {
            maxOptions: null,
            shouldLoad: function() {
                return true;
            },
            loadThrottle: 0,
            firstUrl: null,
            hideSelected: true,

            onInitialize: function() {
                let select = this;
                this.options = {};
                document.querySelectorAll("#skins option").forEach(function(option) {
                    let value = option.value;
                    let text = option.textContent;
                    let img = option.getAttribute("data-image");
                    select.addOption({
                        value: value,
                        text: text,
                        img: img
                    });
                });
            },

            render: {
                option: function(data, escape) {
                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(data.img)}" style="width: auto; height: 30px; border-radius: 5px; margin-right: 8px;">
                        <span>${escape(data.text)}</span>
                    </div>`;
                },
                item: function(data, escape) {
                    let img = data.image;

                    if (!img) {
                        // fallback from DOM
                        const optionEl = document.querySelector(
                            `#champions option[value="${data.value}"]`);
                        img = optionEl?.getAttribute('data-image') || '';
                    }

                    return `<div style="display: flex; align-items: center;">
                        <img src="${escape(img)}" style="width: 20px; height: 20px; border-radius: 5px; margin-right: 5px;">
                        <span>${escape(data.text)}</span>
                        </div>`;
                }
            }
        });

        // $('#galleryUpload').on('change', function (e) {
        //     const files = e.target.files;
        //     const $preview = $('#previewGallery');
        //     $preview.empty(); // Clear previous previews

        //     $.each(files, function (i, file) {
        //         if (!file.type.startsWith('image/')) return;

        //         const reader = new FileReader();
        //         reader.onload = function (event) {
        //             const $col = $(`
        //             <div class="col-6 col-md-3">
        //                 <div class="card shadow-sm">
        //                 <img src="${event.target.result}" class="card-img-top" style="object-fit: cover; height: 150px;">
        //                 </div>
        //             </div>
        //             `);
        //             $preview.append($col);
        //         };
        //         reader.readAsDataURL(file);
        //     });
        // });

        // Upload UX: click/drop/paste (Ctrl+V) into the gallery dropzone
        (function () {
            const dropzone = document.getElementById('galleryDropzone');
            const input = document.getElementById('galleryUpload');
            const btn = document.getElementById('selectImagesBtn');
            const preview = document.getElementById('previewGallery');

            if (!dropzone || !input) return;

            const openPicker = () => input.click();

            if (btn) btn.addEventListener('click', function (e) {
                e.preventDefault();
                openPicker();
            });

            dropzone.addEventListener('click', function (e) {
                if (e.target && (e.target.id === 'selectImagesBtn' || e.target.closest('#selectImagesBtn'))) return;
                openPicker();
            });

            function addFilesToInput(files) {
                const dt = new DataTransfer();
                if (input.files && input.files.length) {
                    for (const f of input.files) dt.items.add(f);
                }
                for (const f of files) dt.items.add(f);
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // preview newly selected files (append after existing images)
            input.addEventListener('change', function () {
                if (!preview) return;

                const files = Array.from(input.files || []).filter(f => f.type && f.type.startsWith('image/'));
                if (!files.length) return;

                files.forEach(function (file) {
                    const url = URL.createObjectURL(file);

                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-3';

                    const wrap = document.createElement('div');
                    wrap.className = 'card shadow-sm';

                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'card-img-top';
                    img.style.objectFit = 'cover';
                    img.style.height = '150px';
                    img.onload = () => URL.revokeObjectURL(url);

                    wrap.appendChild(img);
                    col.appendChild(wrap);
                    preview.appendChild(col);
                });
            });

            // Drag & drop
            ['dragenter', 'dragover'].forEach(function (evtName) {
                dropzone.addEventListener(evtName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (evtName) {
                dropzone.addEventListener(evtName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                const dropped = e.dataTransfer && e.dataTransfer.files ? Array.from(e.dataTransfer.files) : [];
                const images = dropped.filter(f => f.type && f.type.startsWith('image/'));
                if (images.length) addFilesToInput(images);
            });

            // Paste (Ctrl+V)
            document.addEventListener('paste', function (e) {
                const items = e.clipboardData && e.clipboardData.items ? e.clipboardData.items : [];
                const files = [];

                for (const item of items) {
                    if (item.kind === 'file') {
                        const blob = item.getAsFile();
                        if (blob && blob.type && blob.type.startsWith('image/')) {
                            const ext = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                            files.push(new File([blob], `paste-${Date.now()}.${ext}`, { type: blob.type }));
                        }
                    }
                }

                if (files.length) {
                    e.preventDefault();
                    addFilesToInput(files);
                }
            });
        })();



        $('[name="current_rank"]').on('change', function() {
            const val = $(this).val();
            const rank = $('.current-rank');
            const division = $('.current-division');
            const lp = $('.current-lp');

            const division_select = $('[name="current_division"]');
            const lp_input = $('[name="current_lp"]');

            if (val == 0) {
                rank.removeClass('col-9').addClass('col-12');
                division.addClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-9');
                division.removeClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', true);
                lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-9');
                division.addClass('d-none');
                lp.removeClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', true);
            }
        });

        $('[name="flex_rank"]').on('change', function() {
            const val = $(this).val();
            const rank = $('.flex-rank');
            const division = $('.flex-division');
            const lp = $('.flex-lp');

            const division_select = $('[name="flex_division"]');
            const lp_input = $('[name="flex_lp"]');

            if (val == 0) {
                rank.removeClass('col-8').addClass('col-12');
                division.addClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.removeClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', true);
                lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.addClass('d-none');
                lp.removeClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', true);
            }
        });

        $('[name="previous_rank"]').on('change', function() {
            const val = $(this).val();
            const rank = $('.previous-rank');
            const division = $('.previous-division');
            const lp = $('.previous-lp');

            const division_select = $('[name="previous_division"]');
            const lp_input = $('[name="previous_lp"]');

            if (val == 0) {
                rank.removeClass('col-8').addClass('col-12');
                division.addClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', false);
            } else if (val > 0 && val < 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.removeClass('d-none');
                lp.addClass('d-none');

                division_select.attr('required', true);
                lp_input.attr('required', false);
            } else if (val >= 8) {
                rank.removeClass('col-12').addClass('col-8');
                division.addClass('d-none');
                lp.removeClass('d-none');

                division_select.attr('required', false);
                lp_input.attr('required', true);
            }
        });

        $('[name="delivery_type"]').on('change', function() {
            const deliveryType = $(this).val();
            const loginDetails = $('.login-details');

            if (deliveryType === 'instant') {
                // Only the credential text fields should be required.
                // A required checkbox would force the user to enable 2FA.
                loginDetails
                    .find('input[type="text"], input[type="email"], input[type="password"]')
                    .attr('required', true);
                loginDetails.show();
            } else {
                loginDetails.find('input[type="text"], input[type="email"], input[type="password"]').val('');
                loginDetails.find('input[type="checkbox"]').prop('checked', false);
                loginDetails.find('input[type="hidden"][name="has_2fa"]').val('0');
                loginDetails
                    .find('input[type="text"], input[type="email"], input[type="password"]')
                    .removeAttr('required');

                loginDetails.hide();
            }
        });
    })()

        // Pretty validation for update form (English messages, no browser bubbles)
        function getFieldLabel(el) {
            const $el = $(el);

            // For TomSelect, label is usually the previous <label>
            if (el.id) {
                const $lbl = $('label[for="' + el.id + '"]');
                if ($lbl.length) return $lbl.first().text().trim();
            }

            // Closest label within form group
            const $groupLabel = $el.closest('.mb-3, .form-group, .col-md-6, .col-12').find('label.form-label, label').first();
            if ($groupLabel.length) return $groupLabel.text().trim();

            // Fallback to placeholder/name
            return (el.getAttribute('placeholder') || el.name || 'This field').toString();
        }

        function showValidationAlert($form, invalidEls) {
            let $alert = $form.find('.js-validation-alert').first();
            if (!$alert.length) {
                $alert = $('<div class="alert alert-danger js-validation-alert mb-3" role="alert"></div>');
                $form.prepend($alert);
            }

            const items = [];
            invalidEls.forEach(el => {
                const label = getFieldLabel(el);
                if (label && !items.includes(label)) items.push(label);
            });

            const html = items.length
                ? '<div class="fw-semibold mb-1">Please complete the following fields:</div><ul class="mb-0 ps-3"><li>' + items.join('</li><li>') + '</li></ul>'
                : '<div class="fw-semibold">Please fill out all required fields.</div>';

            $alert.html(html).removeClass('d-none');

            // Mark fields
            invalidEls.forEach(el => {
                const $el = $(el);

                if (el.tagName === 'SELECT') {
                    const $ts = $el.next('.ts-wrapper');
                    if ($ts.length) $ts.addClass('is-invalid');
                    else $el.addClass('is-invalid');
                    return;
                }

                if ($el.hasClass('form-check-input')) {
                    $el.closest('.form-check').addClass('is-invalid');
                    return;
                }

                $el.addClass('is-invalid');
            });

            // Focus first invalid field
            if (invalidEls.length) {
                const first = invalidEls[0];
                // If TomSelect, focus the control
                if (first.tagName === 'SELECT') {
                    const $ts = $(first).next('.ts-wrapper').find('input, .ts-control').first();
                    if ($ts.length) $ts.trigger('focus');
                    else $(first).trigger('focus');
                } else {
                    $(first).trigger('focus');
                }
            }

            if (typeof create_toast === 'function') {
                create_toast('danger', 'Missing information', 'Please check the highlighted fields in the form.');
            }
        }

        $(document).on('submit', 'form.js-pretty-validate', function (e) {
            const form = this;
            const $form = $(form);

            // Reset UI
            $form.find('.js-validation-alert').addClass('d-none');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.removeClass('was-validated');

            // checkValidity uses required/constraints but no browser tooltip because novalidate
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopImmediatePropagation();

                $form.addClass('was-validated');

                const invalidEls = Array.from(form.querySelectorAll(':invalid'))
                    .filter(el => !el.disabled && $(el).is(':visible'));

                showValidationAlert($form, invalidEls);
                return false;
            }
        });

        // Clear invalid UI on change
        $(document).on('input change', 'form.js-pretty-validate :input', function () {
            const $el = $(this);
            const $form = $el.closest('form.js-pretty-validate');

            $el.removeClass('is-invalid');
            if (this.tagName === 'SELECT') {
                $el.next('.ts-wrapper').removeClass('is-invalid');
            }
            $el.closest('.form-check').removeClass('is-invalid');
            $form.find('.js-validation-alert').addClass('d-none');
        });

</script>

<script>
// Seller Update (no dependencies)
window.__sellerUpdateAccount = async function(e){
  try { if(e) e.preventDefault(); } catch(_){}
  const form = document.querySelector("form.js-pretty-validate");
  if(!form) return false;
  // HTML5 validation
  if(!form.checkValidity()){
    form.classList.add("was-validated");
    const alertEl = form.querySelector(".js-validation-alert");
    if(alertEl){ alertEl.textContent = "Please check required fields."; alertEl.classList.remove("d-none"); }
    return false;
  }

  const fd = new FormData(form);
  // ensure action/id are set
  if(!fd.get("action")) fd.set("action","seller_update_selling_account");

  // Price: backend expects EUR float, then *100
  // If user typed cents/integers, still ok.
  const priceEl = form.querySelector("[name=price]");
  if(priceEl){
    const p = String(priceEl.value).replace(",",".").trim();
    fd.set("price", p);
  }

  const btn = document.getElementById("sellerUpdateBtn");
  if(btn){ btn.disabled = true; btn.classList.add("disabled"); }

  const url = form.getAttribute("action") || (window.BASE_URL ? window.BASE_URL + "/app/core/ajax.php" : "/app/core/ajax.php");
  const res = await fetch(url, { method: "POST", body: fd, credentials: "same-origin" });
  const text = await res.text();
  let json = null;
  // Try normal JSON first
  try { json = JSON.parse(text); } catch(_) { json = null; }
  // Some servers echo warnings/notices around JSON. Try to extract the JSON object.
  if(!json){
    const first = text.indexOf('{');
    const last = text.lastIndexOf('}');
    if(first !== -1 && last !== -1 && last > first){
      const candidate = text.slice(first, last+1);
      try { json = JSON.parse(candidate); } catch(_) { json = null; }
    }
  }

  // If backend printed raw json without correct header, still handled
  const handle = (payload)=>{
    if(typeof window.ajax_response_handler === "function"){
      window.ajax_response_handler(payload);
      return;
    }
    // minimal fallback
    if(payload && payload.sendToast){
      alert((payload.sendToast.title||"Info")+": "+(payload.sendToast.message||""));
    }
    if(payload && (payload.redirectUrl || payload.redirect)){
      window.location.href = payload.redirectUrl || payload.redirect;
      return;
    }
    if(payload && (payload.refreshPage || payload.reloadPage)){
      window.location.reload();
      return;
    }
  };

  if(json){
    handle(json);
    // show validation errors inline
    if(json.validationErrors){
      const alertEl = form.querySelector(".js-validation-alert");
      if(alertEl){
        alertEl.innerHTML = Object.values(json.validationErrors).map(function(v){ return "<div>"+String(v)+"</div>"; }).join("");
        alertEl.classList.remove("d-none");
      }
    }
  } else {
    // Not JSON => likely PHP error page or login redirect. Provide a helpful message.
    console.warn("Non-JSON response:", text);
    const looksHtml = /<!doctype html|<html|<body/i.test(text);
    if(looksHtml){
      // If the server redirected us, go there; otherwise show a short message.
      if(res && res.url && res.url !== window.location.href){
        window.location.href = res.url;
        return false;
      }
      alert("Update failed (HTML response). You might be logged out or there is a PHP error. Check Network tab + server logs.");
    } else {
      alert("Update failed (non-JSON response). Check Network tab + server logs.");
    }
  }

  if(btn){ btn.disabled = false; btn.classList.remove("disabled"); }
  return false;
};
</script>

<?= $this->stop() ?>

<script>
function switchSellerEditGame(game) {
    var isLol = (game === 'lol' || game === 'league-of-legends');
    var isVal = (game === 'val' || game === 'valorant');

    document.querySelectorAll('.game-data-lol').forEach(function (el) {
        el.style.display = isLol ? '' : 'none';
        el.querySelectorAll('select, input, textarea').forEach(function (field) { field.disabled = !isLol; });
    });
    document.querySelectorAll('.game-data-dynamic').forEach(function (el) {
        var active = (el.getAttribute('data-game') === game);
        el.style.display = active ? '' : 'none';
        el.querySelectorAll('select, input, textarea').forEach(function (field) { field.disabled = !active; });
    });

    var serverSelect = document.querySelector('.game-data-lol select[name="server"]');
    if (serverSelect) serverSelect.disabled = !isLol;
}
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('sellerEditGame');
    if (select) switchSellerEditGame(select.value);
});
</script>
