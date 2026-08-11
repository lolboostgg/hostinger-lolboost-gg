<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'lol.gif']) ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    [data-user-theme="blue"] .card {
        background-color: var(--blue-bg-primary);
    }

    [data-user-theme="blue"] .booster-name {
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .details-row {
        background-color: var(--blue-bg-accent);
    }

    [data-user-theme="blue"] .profile-photo {
        border-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] .sidebar {
        background-color: var(--blue-bg-secondary);
        border: 1px solid var(--blue-border-primary) !important;

        &::-webkit-scrollbar-thumb {
            background-color: var(--blue-bg-accent-hover);

            &:hover {
                background-color: var(--blue-bg-accent-hover);
            }

            &:active {
                background-color: var(--blue-bg-accent-hover);
            }
        }

        &::-webkit-scrollbar-track {
            background-color: var(--blue-bg-secondary);
        }

        &::-webkit-scrollbar-corner {
            background-color: var(--blue-bg-secondary);
        }
    }

    [data-user-theme="blue"] .rating-badge {
        background-color: var(--blue-bg-accent-hover);
        color: var(--blue-text-primary);
        padding: 0.2rem 0.5rem;
    }

    [data-user-theme="blue"] .champion-icon {
        border-color: var(--blue-border-primary) !important;
    }

    [data-user-theme="blue"] .more-champions-icon {
        background-color: var(--blue-bg-accent);
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-selection--single {
        background-color: var(--blue-bg-primary);
        color: var(--blue-text-primary);
        border: 1px solid var(--blue-border-primary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        background-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-selection--multiple {
        background-color: var(--blue-bg-primary);
        color: var(--blue-text-primary);
        border: 1px solid var(--blue-border-primary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--blue-bg-accent-hover);
        border: 1px solid var(--blue-border-primary);
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .select2-container--default.select2-container--disabled .select2-selection--single,
    [data-user-theme="blue"] .select2-container--default.select2-container--disabled .select2-selection--multiple {
        background-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-results>.select2-results__options {
        background-color: var(--blue-bg-primary);
    }

    [data-user-theme="blue"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: var(--blue-bg-accent-hover);
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .select2-container--open .select2-dropdown--below,
    [data-user-theme="blue"] .select2-container--open .select2-dropdown--above {
        border-color: var(--blue-border-primary);
    }

    [data-user-theme="blue"] #filterBooster {
        border-color: var(--blue-border-primary);
    }

    [data-user-theme="blue"] .select2-search__field {
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .filter-checkbox span {
        background-color: var(--blue-bg-primary);
        border-color: var(--blue-border-primary);
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .filter-checkbox span img {
        filter: brightness(0) invert(1);
    }

    [data-user-theme="blue"] .filter-checkbox span:hover {
        background-color: var(--blue-bg-accent-hover);
        border-color: var(--blue-border-primary);
    }

    [data-user-theme="blue"] .filter-checkbox input[type="checkbox"]:checked+span {
        background-color: var(--blue-bg-accent);
        color: var(--blue-text-primary);
        border-color: var(--blue-border-primary);
    }

    [data-user-theme="blue"] .filter-checkbox input[type="checkbox"]:checked+span img {
        filter: brightness(0) invert(1);
    }

    [data-user-theme="blue"] .role-icon {
        background-color: var(--blue-bg-accent);
    }

    [data-user-theme="blue"] .role-icon img {
        filter: contrast(0.5) brightness(2);
    }

    [data-user-theme="dark"] .card {
        background-color: var(--dark-bg-primary);
    }

    [data-user-theme="dark"] .booster-name {
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .details-row {
        background-color: var(--dark-bg-accent);
    }

    [data-user-theme="dark"] .profile-photo {
        border-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] .sidebar {
        background-color: var(--dark-bg-secondary);
        border: 1px solid var(--dark-border-primary) !important;

        &::-webkit-scrollbar-thumb {
            background-color: var(--dark-bg-accent-hover);

            &:hover {
                background-color: var(--dark-bg-accent-hover);
            }

            &:active {
                background-color: var(--dark-bg-accent-hover);
            }
        }

        &::-webkit-scrollbar-track {
            background-color: var(--dark-bg-secondary);
        }

        &::-webkit-scrollbar-corner {
            background-color: var(--dark-bg-secondary);
        }
    }

    [data-user-theme="dark"] .rating-badge {
        background-color: var(--dark-bg-accent-hover);
        color: var(--dark-text-primary);
        padding: 0.2rem 0.5rem;
    }

    [data-user-theme="dark"] .champion-icon {
        border-color: var(--dark-border-primary) !important;
    }

    [data-user-theme="dark"] .more-champions-icon {
        background-color: var(--dark-bg-accent);
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-selection--single {
        background-color: var(--dark-bg-primary);
        color: var(--dark-text-primary);
        border: 1px solid var(--dark-border-primary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        background-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-selection--multiple {
        background-color: var(--dark-bg-primary);
        color: var(--dark-text-primary);
        border: 1px solid var(--dark-border-primary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--dark-bg-accent-hover);
        border: 1px solid var(--dark-border-primary);
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .select2-container--default.select2-container--disabled .select2-selection--single,
    [data-user-theme="dark"] .select2-container--default.select2-container--disabled .select2-selection--multiple {
        background-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-results>.select2-results__options {
        background-color: var(--dark-bg-primary);
    }

    [data-user-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: var(--dark-bg-accent-hover);
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .select2-container--open .select2-dropdown--below,
    [data-user-theme="dark"] .select2-container--open .select2-dropdown--above {
        border-color: var(--dark-border-primary);
    }

    [data-user-theme="dark"] #filterBooster {
        border-color: var(--dark-border-primary);
    }

    [data-user-theme="dark"] .select2-search__field {
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .filter-checkbox span {
        background-color: var(--dark-bg-primary);
        border-color: var(--dark-border-primary);
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .filter-checkbox span img {
        filter: brightness(0) invert(1);
    }

    [data-user-theme="dark"] .filter-checkbox span:hover {
        background-color: var(--dark-bg-accent-hover);
        border-color: var(--dark-border-primary);
    }

    [data-user-theme="dark"] .filter-checkbox input[type="checkbox"]:checked+span {
        background-color: var(--dark-bg-accent);
        color: var(--dark-text-primary);
        border-color: var(--dark-border-primary);
    }

    [data-user-theme="dark"] .filter-checkbox input[type="checkbox"]:checked+span img {
        filter: brightness(0) invert(1);
    }

    [data-user-theme="dark"] .role-icon {
        background-color: var(--dark-bg-accent);
    }

    [data-user-theme="dark"] .role-icon img {
        filter: contrast(0.5) brightness(2);
    }

    .card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: relative;

        transition: transform 0.5s;
    }

    .card:hover {
        transform: translateY(-10px);
    }

    .cover {
        overflow: hidden;
        border-radius: 0.5rem;
    }

    .cover img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        object-position: top;
        transition: transform 0.5s;
    }

    .cover:hover img {
        transform: scale(1.1);
    }

    .profile-photo {
        position: absolute;
        top: 25%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 5px solid #fff;
    }

    .profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .booster-name {
        text-align: center;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 1.8rem;
    }

    .booster-name i {
        font-size: 1rem;
    }

    .icon-sm {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #f0f0f0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    .icon-sm img {
        width: 20px;
    }

    .details-row {
        margin-top: 1.5rem;
        background-color: #f0f0f0;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }

    .rank_icon {
        width: 30px;
    }

    .vertical {
        border-left: 2px solid #ccc;
        height: 10px;
    }

    .bpp {
        font-size: 0.8rem;
    }

    .champion-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 0.2rem;
        border: 3px solid #000;
    }

    .rating-badge {
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;

        font-size: 0.9rem;
        font-weight: 600;
    }

    .rating-badge i {
        font-size: 0.8rem;
    }

    .sidebar {
        background-color: #f3f6ff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

        position: sticky;
        top: 120px;

        &::-webkit-scrollbar {
            width: 0;
            transition: width 0.3s ease;
        }

        &:hover::-webkit-scrollbar {
            width: 8px;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #5b67f0;
            border-radius: 10px;

            &:hover {
                background-color: #3f4d9e;
            }

            &:active {
                background-color: #3f4d9e;
            }
        }

        &::-webkit-scrollbar-track {
            background-color: #f3f6ff;
        }

        &::-webkit-scrollbar-corner {
            background-color: #f3f6ff;
        }
    }

    .more-champions-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #000;
        color: #fff;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-size: 0.8rem;
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

    /* General Select2 container styles */
    .select2-container--default .select2-selection--single {
        display: block;
        width: 100%;
        padding: 0.625rem 3rem 0.625rem 1rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.6;
        color: #565973;
        background-color: #fff;
        border: 1px solid #d4d7e5;
        border-radius: 0.375rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        position: absolute;
        top: 0;
        right: 1rem;
        width: 16px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23565973' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 16px 12px;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow {
        background-image: none;
    }

    .select2-container--default .select2-selection--multiple {
        display: block;
        width: 100%;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.6;
        color: #565973;
        background-color: #fff;
        border: 1px solid #d4d7e5;
        border-radius: 0.375rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        min-height: 2.5rem;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: rgba(99, 102, 241, 0.35);
        outline: 0;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #ebedfd;
        border: 1px solid #5b67f0;
        color: #565973;
        cursor: default;
        margin: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        margin-right: 0.25rem;
        color: #5b67f0;
        cursor: pointer;
        height: 100%;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default .select2-selection--multiple:focus {
        border-color: rgba(99, 102, 241, 0.35);
        outline: 0;
        box-shadow: inset 0 0 0 transparent, 0 0 0 0 rgba(99, 102, 241, 0.25);
    }

    .select2-container--default.select2-container--disabled .select2-selection--single,
    .select2-container--default.select2-container--disabled .select2-selection--multiple {
        background-color: #f3f6ff;
        cursor: not-allowed;
    }

    .select2-container .select2-search--inline .select2-search__field {
        margin: 10px 16px;
        height: 22px;

        font-family: var(--si-body-font-family);
    }

    .select2-container .select2-search--inline .select2-search__field::placeholder {
        color: #b9bdcd;
    }

    .select2-container--default .select2-selection--multiple {
        padding: 0px;
    }

    .select-img {
        width: 30px;
        height: auto;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #ebedfd;
        color: #5b67f0;
    }

    .filter-checkbox {
        display: block;
        margin-bottom: 0.5rem;
        cursor: pointer;
    }

    .filter-checkbox input[type="checkbox"] {
        display: none;
    }

    .filter-checkbox span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background-color: #eef1f7;
        padding: 0.5rem;
        border-radius: 8px;
        border: 1px solid #d4d7e5;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .filter-checkbox span img {
        width: 25px;
        height: 25px;
    }

    .filter-checkbox span:hover {
        background-color: #5b67f020;
        border-color: #5b67f0;
    }

    .filter-checkbox input[type="checkbox"]:checked+span {
        background-color: #5b67f0;
        color: #fff;
        border-color: #5b67f0;
    }

    .filter-checkbox input[type="checkbox"]:checked+span img {
        filter: brightness(0) invert(1);
    }

    .role-icon img {
        filter: contrast(0.5) brightness(2) invert(1);
    }

    @keyframes l1 {
        to {
            transform: rotate(.5turn)
        }
    }

    @media only screen and (max-width: 1707px) {
        .sidebar {
            height: 38.563vw;
            overflow-x: hidden;
            overflow-y: auto;
        }
    }

    @media only screen and (max-width: 767px) {
        .sidebar {
            height: 100%;
        }

        .profile-photo {
            width: 18.605vw;
            height: 18.605vw;
            top: 16.279vw;
        }

        .cover img {
            height: 16.279vw;
        }

        .champions-mobile {
            flex-direction: column;
            justify-content: center !important;
            align-items: center !important;
            gap: 0.5rem;
        }
    }
</style>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="p-3 border rounded-3 mb-4 sidebar">
                <div class="d-flex justify-content-between align-items-start">
                    <h3 class="h3">Filters</h3>
                    <button class="btn btn-link p-0 mb-3" onclick="window.location.href = '/boosters'">Clear
                        Filters</button>
                </div>
                <div class="mb-3">
                    <label for="filterBooster" class="form-label">Search Booster</label>
                    <input type="text" class="form-control" id="filterBooster" placeholder="Search Booster">
                </div>
                <div class="mb-3">
                    <label for="filterGame" class="form-label">Game</label>
                    <select class="form-control" id="filterGame" multiple="multiple">
                        <option value="lol">League of Legends</option>
                        <option value="val">Valorant</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filterChampions" class="form-label">Champions</label>
                    <select class="form-control" id="filterChampions" multiple="multiple">
                        <?= util_load_champions_select($data['champions']) ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filterLanguages" class="form-label">Languages</label>
                    <select class="form-control" id="filterLanguages" multiple="multiple">
                        <?= util_load_languages_select($data['languages']) ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Roles</label>
                    <div>
                        <?php
                        $roles = [
                            'TopLane',
                            'Jungle',
                            'MidLane',
                            'AdCarry',
                            'Support',
                        ];

                        // foreach ($roles as $role) {
                        //     echo '<div class="form-check">';
                        //     echo '<input class="form-check-input filterRole" type="checkbox"' . $role . '" value="' . $role . '">';
                        //     echo '<label class="form-check-label"' . $role . '">' . $role . '</label>';
                        //     echo '</div>';
                        // }
                        
                        ?>

                        <?php foreach ($roles as $role): ?>
                            <label class="filter-checkbox">
                                <input type="checkbox" class="filterRole" value="<?= $role ?>">
                                <span>
                                    <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= $role ?>.png" alt="<?= $role ?>">
                                    <?= ucfirst($role) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="row g-3" id="boosters">
                <?php foreach ($boosters as $booster): ?>
                    <div class="col-6 col-sm-6 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="cover">
                                <img src="<?= $booster['cover'] ?? ASSET_URL . '/core/main/img/banners/leona.jpeg' ?>"
                                    class="card-img-top" alt="...">
                            </div>

                            <div class="card-body">
                                <div class="profile-photo">
                                    <img src="<?= $booster['icon'] ?>" alt="...">
                                </div>
                                <div class="booster-name">
                                    <?= $booster['username'] ?>
                                    <i class="fa-solid fa-badge-check text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="top" aria-label="Active"></i>
                                </div>
                                <small class="fw-700  mb-1 d-block text-center"><?= $booster['rank_name'] ?> Booster</small>
                                <div class="pt-2 text-center">
                                    <?php
                                    if ($booster['roles'] != null) {
                                        $roles = explode('|', $booster['roles']);

                                        foreach ($roles as $role) {
                                            echo '<a class="icon-sm me-1 text-white role-icon">';
                                            echo '<img src="' . ASSET_URL . '/core/main/img/lol/roles/' . $role . '.png" alt="' . $role . '">';
                                            echo '</a>';
                                        }
                                    } else {
                                        ?>
                                        <small class="text-muted" style="height: 29px;">No roles</small>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="d-flex align-items-center gap-3 justify-content-center mt-3 details-row">
                                    <?php
                                    if ($booster['lol_rank'] != null) {
                                        $rank = explode('|', $booster['lol_rank']);
                                        echo '<img class="rank_icon" src="' . ASSET_URL . '/core/main/img/lol/ranks/max/' . $rank[0] . '.png"
                                        alt="rank-icon">';
                                    } else {
                                        echo '<img class="rank_icon" src="' . ASSET_URL . '/core/main/img/lol/ranks/max/0.png" alt="rank-icon">';
                                    }
                                    ?>
                                    <!-- <hr class="vertical">
                                    <small class="bpp">0 BPP </small> -->
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-3 champions-mobile">
                                    <div class="pt-2">
                                        <?php
                                        if ($booster['champions'] != null) {
                                            $champions = explode('|', $booster['champions']);
                                            $max_display = 3;
                                            $displayed_champions = 0;
                                            $total_champions = count($champions);

                                            foreach ($champions as $champion) {
                                                if ($displayed_champions < $max_display) {
                                                    echo '<img class="champion-icon" src="' . LOL_CHAMP_URL . '/' . $champion . '.png" alt="' . $champion . '">';
                                                    $displayed_champions++;
                                                }
                                            }

                                            // Check if there are more than $max_display champions
                                            if ($total_champions > $max_display) {
                                                $remaining_champions = $total_champions - $max_display;
                                                echo '<span class="more-champions-icon">+' . $remaining_champions . '</span>';
                                            }
                                        } else {
                                            echo '<span class="more-champions-icon">N/A</span>';
                                        }
                                        ?>
                                    </div>
                                    <div class="rating-badge">
                                        <i class="fa fa-star text-primary"></i>
                                        <span>
                                            <?= $booster['rating'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <a href="/boosters/<?= $booster['booster_id'] ?>" class="stretched-link"></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="loading-spinner" class="text-center" style="display: none;">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let currentPage = 1;
    const totalPages = <?= $pagination['totalPages'] ?>;
    let isLoading = false;

    function loadMoreBoosters() {
        if (isLoading || currentPage >= totalPages) return;

        isLoading = true;
        currentPage++;
        $('#loading-spinner').css('display', 'flex');

        $.post('<?= AJAX_URL ?>', {
            action: 'load_boosters',
            page: currentPage
        }, (response) => {
            setTimeout(() => {
                $('#boosters').append(response);
                $('#loading-spinner').css('display', 'none');
                isLoading = false;
            }, 2000);
        });

    }

    $(window).on('scroll', function () {
        const boosterContainer = $('#boosters');
        const containerBottom = boosterContainer.offset().top + boosterContainer.outerHeight();
        const windowBottom = $(window).scrollTop() + $(window).height();

        if (windowBottom >= containerBottom + 100) {
            console.log('Load more boosters');
            loadMoreBoosters();
        }
    });

    $(document).ready(function () {
        function filterGameImages(option) {
            if (!option.id) {
                return option.text;
            }

            let imageURL = '<?= ASSET_URL ?>/core/main/img/icons/' + option.id;
            imageURL += option.id === 'lol' ? '.svg' : '.png';
            const optionWithImage = $(
                '<span><img src="' + imageURL + '" class="img-fluid select-img" /> ' + option.text + '</span>'
            );

            return optionWithImage;
        }

        function filterChampionsImages(option) {
            if (!option.id) {
                return option.text;
            }

            const imageURL = '<?= LOL_CHAMP_URL ?>' + '/' + option.id + '.png';
            const optionWithImage = $(
                '<span><img src="' + imageURL + '" class="img-fluid select-img" /> ' + option.text + '</span>'
            );

            return optionWithImage;
        }

        function filterLanguagesImages(option) {
            if (!option.id) {
                return option.text;
            }

            const imageURL = '<?= ASSET_URL ?>/core/main/img/languages/' + option.id + '.png';
            const optionWithImage = $(
                '<span><img src="' + imageURL + '" class="img-fluid select-img" /> ' + option.text + '</span>'
            );

            return optionWithImage;
        }

        $('#filterGame').select2({
            width: '100%',
            placeholder: 'Select a game',
            templateResult: filterGameImages,
            templateSelection: filterGameImages
        });

        $('#filterChampions').select2({
            width: '100%',
            placeholder: 'Select a champion',
            templateResult: filterChampionsImages,
            templateSelection: filterChampionsImages
        });

        $('#filterLanguages').select2({
            width: '100%',
            placeholder: 'Select a language',
            templateResult: filterLanguagesImages,
            templateSelection: filterLanguagesImages
        });

        $('#filterBooster, #filterGame, #filterChampions, #filterLanguages, .filterRole').on('change input', function () {
            const filterBooster = $('#filterBooster').val();
            const filterGame = $('#filterGame').val();
            const filterChampions = $('#filterChampions').val();
            const filterLanguages = $('#filterLanguages').val();
            const filterRoles = $('.filterRole:checked').map(function () {
                return $(this).val();
            }).get();

            $.post('<?= AJAX_URL ?>', {
                action: 'filter_boosters',
                name: filterBooster || '',
                games: filterGame || [],
                champions: filterChampions || [],
                languages: filterLanguages || null,
                roles: filterRoles || []
            }, (response) => {
                $(window).off('scroll');
                $('#boosters').html(response);
            });
        });
    });
</script>
<?= $this->end('scripts') ?>

<?= $this->insert('main/components/cta/two') ?>