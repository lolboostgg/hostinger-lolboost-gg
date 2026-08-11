<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'lol.gif']) ?>

<style>
    button,
.btn {
    border-radius: 999px !important; /* pill-style */
    font-weight: 600;
    padding: 0.6rem 1.2rem;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

button:hover,
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}
.top-buttons button {
    font-size: 0.8rem;
    padding: 0.3rem 0.75rem;
    height: auto;
    line-height: 1.2;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem; /* Abstand zwischen Icon und Text */
}

.top-buttons button i {
    font-size: 0.9rem;
    margin: 0; /* Entferne rechtes Margin */
    order: -1; /* Icon vor Text */
}
.tip-btn {
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.tip-btn:hover {
    background-color: #f0f0f0;
}

@media only screen and (max-width: 767px) {
    /* Allgemein alle Buttons kleiner */
    .btn,
    button,
    .top-buttons button,
    .tip-btn,
    .modal-footer .btn {
        font-size: 0.7rem !important;
        padding: 0.35rem 0.8rem !important;
        line-height: 1.1 !important;
        border-radius: 999px !important;
    }

    /* Optional: Icons in Buttons kleiner */
    .btn i,
    button i,
    .top-buttons button i,
    .tip-btn i {
        font-size: 0.85rem !important;
        margin-left: 0.3rem;
    }

    /* Optional: Volle Breite bei vertikal gestapelten Button-Gruppen */
    .top-buttons {
        flex-direction: column;
        gap: 0.4rem;
    }

    .top-buttons button {
        width: 100%;
    }
}

</style>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="cover">
                <img src="<?= $booster['cover'] ?? ASSET_URL . '/core/main/img/banners/leona.jpeg' ?>"
                    class="card-img-top" alt="...">

                <div class="profile-photo">
                    <img src="<?= $booster['icon'] ?>" alt="...">
                </div>

                <div class="d-flex justify-content-between mt-0 mobile-align">
                    <span style="margin-left: 185px;" class="mobile-margin">
                        <div class="booster-name">
                            <?= $booster['username'] ?>
                            <i class="fa-solid fa-badge-check text-primary" data-bs-toggle="tooltip"
                                data-bs-placement="top" aria-label="Active"></i>
                        </div>
                        <div>
                            <span class="rating-badge bg-primary text-white">
                                <i class="fa-solid fa-star"></i>
                                <?= $booster['rating'] ?>
                            </span>

                            <span class="rating-badge bg-primary text-white ms-1">
                                <p class="fw-700 m-0 d-block text-center"><?= $booster['rank_name'] ?> Booster</p>
                            </span>
                        </div>
                    </span>

                    <div class="top-buttons mt-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#send_tip_md">
                            Send Tip
                            <i class="fa-solid fa-regular fa-money-bill"></i>
                        </button>

                        <?php if ($booster['discord_id'] == null || $booster['boost_requests'] != 1): ?>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Currently not accepting Boosting requests.">
                                <button type="button" class="btn btn-primary h-100" id="requestBoost" disabled>
                                    Play With <?= $booster['username'] ?>
                                    <i class="fa-solid fa-regular fa-bolt"></i>
                                </button>
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" id="requestBoost">
                                Play With <?= $booster['username'] ?>
                                <i class="fa-solid fa-regular fa-rocket"></i>
                            </button>
                         <?php if ($booster['discord_id'] == null || $booster['boost_requests'] != 1): ?>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Currently not accepting Coaching requests.">
                                <button type="button" class="btn btn-primary h-100" id="buyCoaching" disabled>
                                    Buy Coaching
                                    <i class="fa-solid fa-regular fa-coins"></i>
                                </button>
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" id="buyCoaching">
                                Get Coached by <?= $booster['username'] ?>
                                <i class="fa-duotone fa-graduation-cap"></i>
                            </button>
                        <?php endif; ?>    
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 7rem;">
    <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                    type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="fa-duotone fa-house me-1"></i> Overview
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button"
                    role="tab" aria-controls="orders" aria-selected="false">
                    <i class="fa-duotone fa-medal me-1"></i> Orders
                </button>
            </li>
            <li class="nav-item">
                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Coming Soon">
                    <button class="nav-link disabled" id="feedback-tab" type="button" aria-controls="feedback"
                        aria-selected="false">
                        <i class="fa-duotone fa-star me-1"></i> Feedback
                    </button>
                </span>
            </li>
        </ul>
    </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row mt-3 mobile-hide">
                <div class="col-md-8">
                    <h4>
                        Details
                    </h4>
                </div>
                <div class="col-md-4">
                    <h4>
                        Recent Orders
                    </h4>
                </div>
            </div>
            <div class="row" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="col-md-8">
                    <div class="description text-muted">
                        <?php
                        if ($booster['description'] != null) {
                            echo $booster['description'];
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                    <div class="details">
                        <div class="detail-card">
                            <div class="icon">
                                <i class="fa-duotone fa-solid fa-medal"></i>
                            </div>
                            <div class="title">
                                <small class="text-muted">GAME RANK</small>
                            </div>
                            <div class="value mt-1">
                                <?php
                                $lolranks = [
                                    1 => 'Iron',
                                    2 => 'Bronze',
                                    3 => 'Silver',
                                    4 => 'Gold',
                                    5 => 'Platinum',
                                    6 => 'Emerald',
                                    7 => 'Diamond',
                                    8 => 'Master',
                                    9 => 'Grandmaster',
                                    10 => 'Challenger'
                                ];
                                ?>

                                <?php
                                if ($booster['lol_rank'] != null) {
                                    $rank = explode('|', $booster['lol_rank']);
                                    echo '<img class="rank_icon" src="' . ASSET_URL . '/core/main/img/lol/ranks/max/' . $rank[0] . '.png"
                                        alt="rank-icon">';
                                } else {
                                    echo '<img class="rank_icon" src="' . ASSET_URL . '/core/main/img/lol/ranks/max/0.png" alt="rank-icon">';
                                }
                                ?>
                            </div>
                        </div>
                        <!-- <div class="detail-card">
                            <div class="icon">
                                <i class="fad fa-star"></i>
                            </div>
                            <div class="title">
                                <small class="text-muted">BPP SCORE</small>
                            </div>
                            <div class="value">
                                0
                            </div>
                        </div> -->
                        <div class="detail-card">
                            <div class="icon">
                                <i class="fa-duotone fa-regular fa-swords"></i>
                            </div>
                            <div class="title">
                                <small class="text-muted">ROLES</small>
                            </div>
                            <div class="value roles mt-1">
                                <?php
                                if ($booster['roles'] != null) {
                                    $roles = explode('|', $booster['roles']);

                                    foreach ($roles as $role) {
                                        echo '<span class="icon-sm"><img src="' . ASSET_URL . '/core/main/img/lol/roles/' . $role . '.png" alt="' . $role . '"></span>';
                                    }
                                } else {
                                    ?>
                                    <small class="text-muted" style="height: 29px;">N/A</small>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="icon">
                                <i class="fa-duotone fa-light fa-crown"></i>
                            </div>
                            <div class="title">
                                <small class="text-muted">CHAMPIONS</small>
                            </div>
                            <div class="value mt-1">
                                <?php
                                if ($booster['champions'] != null) {
                                    $champions = explode('|', $booster['champions']);

                                    foreach ($champions as $champion) {
                                        echo '<img class="champion-icon" src="' . LOL_CHAMP_URL . '/' . $champion . '.png" alt="' . $champion . '">';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="icon">
                                <i class="fa-duotone fa-regular fa-language"></i>
                            </div>
                            <div class="title">
                                <small class="text-muted">LANGUAGES</small>
                            </div>
                            <div class="value mt-1">
                                <?php
                                if ($booster['languages'] != null) {
                                    $languages = explode('|', $booster['languages']);

                                    foreach ($languages as $language) {
                                        echo '<img class="flag" src="' . ASSET_URL . '/core/main/img/languages/' . $language . '.png" alt="flag" data-bs-toggle="tooltip" data-bs-placement="top" title="' . ucfirst($language) . '">';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h3 class="mobile-show mt-3" style="display: none;">
                        Recent Orders
                    </h3>

                    <div class="sidebar">
                        <div class="orders">
                            <?php
                            if (empty($booster['orders'])) {
                                ?>
                                <p class="text-muted text-center mb-0">
                                    This booster has no orders yet.
                                </p>
                                <?php
                            } else {
                                for ($i = 0; $i < (count($booster['orders']) >= 4 ? '4' : count($booster['orders'])); $i++) {
                                    $order = $booster['orders'][$i];

                                    if ($order != null) {
                                        ?>
                                        <div class="order">
                                            <div class="title">
                                                <span class="icon">
                                                    <i class="fa-duotone text-body <?= $order['icon'] ?>"></i>
                                                </span>
                                                <p class="fw-600 text-primary">
                                                    <?= $order['name'] ?>
                                                    <br>
                                                    <small class="text-muted fw-500 mt-1 d-block">
                                                        <?= util_format_boost_overview($order['game'], $order['type'], $order) ?>
                                                    </small>
                                                </p>
                                            </div>
                                            <div class="status">
                                                <span class="badge bg-primary">
                                                    <?= str_replace('_', ' ', $order['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="order">
                                            <div class="fw-500">
                                                <a href="#">
                                                    #N/A
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
            <div class="row" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                <?php if (empty($booster['orders'])): ?>
                    <div class="col-md-12 text-center no-orders-sec"
                        style="padding: 50px 20px; background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <i class="fa-duotone fa-regular fa-box-alt" style="font-size: 80px; color: #6366f1;"
                            class="text-center"></i>
                        <h3 style="margin-top: 20px; color: #333; font-size: 1.8rem; font-weight: 600;"
                            class="no-orders-text">No Orders Yet
                        </h3>
                        <p style="color: #666; font-size: 1.2rem; margin-top: 10px;"
                            class="text-muted text-center no-orders-text">
                            No boosting history available for this booster.
                        </p>
                    </div>
                <?php else: ?>
                    <div class=" col-md-12">
                        <table id="ordersTable">
                            <thead>
                                <tr>
                                    <th>Boost Type</th>
                                    <th>Boost</th>
                                    <th style="text-align: right;">Status</th>
                                </tr>
                            </thead>
                            <?php
                            foreach ($booster['orders'] as $order) {
                                ?>
                                <tr>
                                    <td>
                                        <div class="type">
                                            <span class="icon">
                                                <i class="fa-duotone text-body <?= $order['icon'] ?>"></i>
                                            </span>
                                            <p class="mb-0">
                                                <?= $order['name'] ?>
                                                <small class="text-primary d-block">
                                                    <?= util_format_boost_overview($order['game'], $order['type'], $order) ?>
                                                </small>
                                            </p>
                                        </div>
                                    </td>
                                    <td style="width: 570px;">
                                        <?= util_format_boost_icons($order['game'], $order['type'], $order) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-75 d-block p-3">
                                            <?= str_replace('_', ' ', $order['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                        <div id="loading-spinner" class="d-none">
                            <div class="loader mx-auto"></div>
                        </div>
                        <?php
                        if ($pagination['totalPages'] > 1) {
                            ?>
                            <button type="button" class="btn btn-primary d-block w-25 mt-4 mx-auto" id="loadMoreOrders">
                                Load More Orders
                            </button>
                            <?php
                        }
                        ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <div class="tab-pane fade" id="feedback" role="tabpanel" aria-labelledby="feedback-tab">

            <div class="row" id="feedback" role="tabpanel" aria-labelledby="feedback-tab">
                <div class="col-md-12">
                    <div class="description">
                        <h3>Feedback</h3>
                        <p>
                            Feedback is coming soon...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="client_send_tip">
    <input type="hidden" name="order_id" value="">
    <input type="hidden" name="booster_id" value="<?= $booster['booster_id'] ?>">

    <div id="send_tip_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0 flex-column align-items-center position-relative p-0 pt-4">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                
                    <h4 class="fw-bold text-center mb-1" style="font-size: 1.65rem;">
                        Tip <?= $booster['username'] ?? 'Booster' ?>
                    </h4>
                </div>

                <div class="modal-body text-center pt-0">
                    <p class="text-muted mb-4">Do you want to leave a tip?</p>

                    <div class="text-start mb-3">
                        <label class="form-label fw-bold">Tip Amount <span class="text-danger">*</span></label>
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <button type="button" class="btn btn-outline-dark w-100 fw-semibold tip-btn" data-value="5.00">€5.00</button>
                            <button type="button" class="btn btn-outline-dark w-100 fw-semibold tip-btn" data-value="10.00">€10.00</button>
                            <button type="button" class="btn btn-outline-dark w-100 fw-semibold tip-btn" data-value="20.00">€20.00</button>
                        </div>

                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary px-3" id="tip-minus">−</button>
                            <input type="text" class="form-control text-center fw-bold" name="amount" id="tip-amount" value="5.00">
                            <button type="button" class="btn btn-outline-secondary px-3" id="tip-plus">+</button>
                        </div>
                    </div>

                    <div class="text-start">
                        <label class="form-label fw-bold">Message</label>
                        <textarea class="form-control" name="note" placeholder="Leave a message for the booster..." rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Send Tip</button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->start('scripts') ?>
<script>

    let currentPage = 1;
    let totalPages = <?= $pagination['totalPages'] ?>;
    let isLoading = false;

    function loadMoreOrders() {
        if (isLoading || currentPage >= totalPages) return;

        isLoading = true;
        currentPage++;

        $('#loading-spinner').removeClass('d-none');
        $('#loadMoreOrders').attr('disabled', true);

        $.post('<?= AJAX_URL ?>', {
            action: 'load_booster_orders',
            id: <?= $booster['booster_id'] ?>,
            page: currentPage,
        }, (response) => {
            setTimeout(() => {
                isLoading = false;
                $('#loading-spinner').addClass('d-none');
                $('#loadMoreOrders').attr('disabled', false);
                $('#ordersTable tbody').append(response);

                if (currentPage >= totalPages) {
                    $('#loadMoreOrders').remove();
                }
            }, 1000);
        });
    }

    $('#loadMoreOrders').on('click', loadMoreOrders);

    $('#requestBoost').on('click', () => {
        let booster = <?= $booster['booster_id'] ?>;
        sessionStorage.setItem('booster', booster);

        window.location.href = '/lol/rank-boost';
    });

    $('#buyCoaching').on('click', () => {
        let booster = <?= $booster['booster_id'] ?>;
        sessionStorage.setItem('booster', booster);

        window.location.href = '/lol/coaching';
    });
</script>
<?= $this->end('scripts') ?>

<?= $this->start('styles') ?>
<style>
    [data-user-theme="dark"] .card {
        box-shadow: 0 4px 8px var(--dark-border-secondary);
        background-color: var(--dark-bg-primary);
    }

    [data-user-theme="dark"] .cover {
        background-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] .profile-photo {
        border: 5px solid var(--dark-border-secondary);
    }

    [data-user-theme="dark"] .booster-name {
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .icon-sm {
        background-color: white;
        border-color: var(--dark-border-primary) !important;
    }

    [data-user-theme="dark"] .no-orders-sec {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border-primary) !important;
    }

    [data-user-theme="dark"] .no-orders-text {
        color: var(--dark-text-primary) !important;
    }

    [data-user-theme="dark"] .champion-icon {
        border: 2px solid var(--dark-border-primary);
    }

    [data-user-theme="dark"] .more-champions-icon {
        background-color: var(--dark-bg-accent);
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .description {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border: 1px solid var(--dark-border-primary);
    }

    [data-user-theme="dark"] .description h3 {
        color: var(--dark-text-primary);
    }

    [data-user-theme="dark"] .detail-card {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-secondary);
        border: 1px solid var(--dark-border-primary);
    }

    [data-user-theme="dark"] .detail-card .roles a {
        background-color: var(--dark-bg-accent-hover);
    }

    [data-user-theme="dark"] .sidebar {
        border: 2px solid var(--dark-border-primary);
        background-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] .order {
        background-color: var(--dark-bg-secondary);
        border: 1px solid var(--dark-border-secondary);
    }

    [data-user-theme="dark"] .order:hover {
        background-color: var(--dark-transparent);
    }

    [data-user-theme="dark"] .order .title .icon {
        background-color: var(--dark-bg-primary);
    }

    [data-user-theme="dark"] .order .title .icon i {
        color: var(--dark-text-primary) !important;
    }

    [data-user-theme="dark"] #ordersTable tbody tr {
        background-color: var(--dark-bg-secondary);
    }

    [data-user-theme="dark"] #ordersTable tbody tr td {
        color: var(--dark-text-secondary);
    }

    [data-user-theme="dark"] #ordersTable tbody tr td span.icon {
        background-color: var(--dark-bg-accent-hover);
    }

    [data-user-theme="dark"] #ordersTable tbody tr td span.icon i {
        color: var(--dark-text-primary) !important;
    }

    [data-user-theme="dark"] #loading-spinner .loader {
        border-color: var(--dark-transparent) var(--dark-bg-accent-hover);
    }

    [data-user-theme="blue"] .card {
        box-shadow: 0 4px 8px var(--blue-border-secondary);
        background-color: var(--blue-bg-primary);
    }

    [data-user-theme="blue"] .cover {
        background-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] .profile-photo {
        border: 5px solid var(--blue-border-secondary);
    }

    [data-user-theme="blue"] .booster-name {
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .icon-sm {
        background-color: white;
        border-color: var(--blue-border-primary) !important;
    }

    [data-user-theme="blue"] .no-orders-sec {
        background-color: var(--blue-bg-secondary) !important;
        border-color: var(--blue-border-primary) !important;
    }

    [data-user-theme="blue"] .no-orders-text {
        color: var(--blue-text-primary) !important;
    }

    [data-user-theme="blue"] .champion-icon {
        border: 2px solid var(--blue-border-primary);
    }

    [data-user-theme="blue"] .more-champions-icon {
        background-color: var(--blue-bg-accent);
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .description {
        background-color: var(--blue-bg-secondary);
        color: var(--blue-text-primary);
        border: 1px solid var(--blue-border-primary);
    }

    [data-user-theme="blue"] .description h3 {
        color: var(--blue-text-primary);
    }

    [data-user-theme="blue"] .detail-card {
        background-color: var(--blue-bg-secondary);
        color: var(--blue-text-secondary);
        border: 1px solid var(--blue-border-primary);
    }

    [data-user-theme="blue"] .detail-card .roles a {
        background-color: var(--blue-bg-accent-hover);
    }

    [data-user-theme="blue"] .sidebar {
        border: 2px solid var(--blue-border-primary);
        background-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] .order {
        background-color: var(--blue-bg-secondary);
        border: 1px solid var(--blue-border-secondary);
    }

    [data-user-theme="blue"] .order:hover {
        background-color: var(--blue-transparent);
    }

    [data-user-theme="blue"] .order .title .icon {
        background-color: var(--blue-bg-primary);
    }

    [data-user-theme="blue"] .order .title .icon i {
        color: var(--blue-text-primary) !important;
    }

    [data-user-theme="blue"] #ordersTable tbody tr {
        background-color: var(--blue-bg-secondary);
    }

    [data-user-theme="blue"] #ordersTable tbody tr td {
        color: var(--blue-text-secondary);
    }

    [data-user-theme="blue"] #ordersTable tbody tr td span.icon {
        background-color: var(--blue-bg-accent-hover);
    }

    [data-user-theme="blue"] #ordersTable tbody tr td span.icon i {
        color: var(--blue-text-primary) !important;
    }

    [data-user-theme="blue"] #loading-spinner .loader {
        border-color: var(--blue-transparent) var(--blue-bg-accent-hover);
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
        position: relative;
        border-radius: 0.5rem;
        padding: 0;
        height: 200px;
    }

    .cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top;
        border-radius: 1rem;
        transition: transform 0.5s;
    }

    .profile-photo {
        position: absolute;
        top: 200px;
        left: 20px;
        transform: translate(0, -50%);

        width: 150px;
        height: 150px;
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
        font-size: 1.6rem;
        font-weight: 600;
        color: #000;
    }

    .booster-name i {
        font-size: 1.4rem;
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

    .rank_icon {
        width: 50px;
    }

    .champion-icon {
        width: 35px;
        height: auto;
        border-radius: 5px;
        margin-right: 0.2rem;
        margin-bottom: 0.2rem;
        border: 2px solid #000;
    }

    .rating-badge i {
        font-size: 0.8rem;
    }

    .more-champions-icon {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        background-color: #000;
        color: #fff;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-size: 0.8rem;
    }

    .rating-badge {
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem 0.5rem;

        font-size: 0.7rem;
        font-weight: 600;
    }

    .description {
        font-size: 1.1rem;
        line-height: 1.2;

        text-align: justify;
        background-color: #f0f2f5;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
    }

    .description h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-align: center;
    }

    .details {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .detail-card {
        background-color: #f0f2f5;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
    }

    .detail-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
    }

    .detail-card .icon i {
        font-size: 2rem;
    }

    .detail-card .title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .detail-card .value {
        font-size: 1.2rem;
        font-weight: 600;
    }

    .detail-card .roles {
        display: flex;
        gap: 0.3rem;
    }

    .detail-card .roles a {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        background-color: #f0f0f0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    .detail-card .roles .icon-sm {
        border: 2px solid #ccc;
        border-radius: 5px;
    }

    .flag {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .top-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .top-buttons button {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
    }

    .top-buttons button i {
        font-size: 1rem;
        margin-left: 0.5rem;
    }

    .sidebar {
        border: 2px solid #f0f0f0;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }

    .orders {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.5rem;
    }

    .order {
        background-color: #f0f2f5;
        padding: 1rem;
        border-radius: 0.5rem;

        display: grid;
        cursor: pointer;
    }

    .order:hover {
        background-color: #d7dade;
    }

    .order .title {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .order .title .icon {
        width: 35px;
        height: 35px;
        border-radius: 0.5rem;
        background-color: #fff;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    .order .title p {
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1;
    }

    .order .status {
        text-align: right;
    }

    #ordersTable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    #ordersTable thead th {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0 0.5rem;
    }

    #ordersTable tbody tr {
        background-color: #f0f2f5;
    }

    #ordersTable tbody tr td {
        font-size: 0.9rem;
        padding: 0.5rem;
    }

    #ordersTable tbody tr .type {
        display: flex;
        gap: 0.7rem;
    }

    td:first-child {
        border-top-left-radius: 10px;
    }

    td:first-child {
        border-bottom-left-radius: 10px;
    }

    td:last-child {
        border-top-right-radius: 10px;
    }

    td:last-child {
        border-bottom-right-radius: 10px;
    }

    #ordersTable tbody tr .type p {
        font-size: 1rem;
        font-weight: 600;
    }

    #ordersTable tbody tr .type p small {
        font-size: 0.8rem;
        font-weight: 500;
    }

    #ordersTable tbody tr td span.icon {
        width: 50px;
        height: 50px;
        aspect-ratio: 1/1;
        border-radius: 0.5rem;
        background-color: #e5e5e5;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    #ordersTable tbody tr td span.icon i {
        font-size: 1.5rem;
    }

    #ordersTable tbody tr td .boost_detail {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    #ordersTable tbody tr td .boost_detail .icon {
        width: 50px;
        height: auto;
        border-radius: 0;
        background-color: transparent;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        object-fit: contain;
    }

    #loading-spinner {
        margin-top: 1rem;

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

    @media only screen and (max-width: 767px) {
        .mobile-hide {
            display: none !important;
        }

        .mobile-show {
            display: block !important;
        }

        .nav.nav-tabs {
            margin-top: 1rem;
        }

        .card {
            border-radius: 0.25rem;
            overflow: hidden;
            box-shadow: 0 0.465vw 0.930vw rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-1.163vw);
        }

        .cover {
            height: 34.884vw;
        }

        .cover img {
            height: 100%;
            border-radius: 0.5rem;
        }

        .profile-photo {
            top: 34.884vw;
            left: 0;
            transform: translate(0, -50%);
            width: 24.605vw;
            height: 24.605vw;
            border: 0.930vw solid #fff;
        }

        .booster-name {
            font-size: 1.4rem;
        }

        .booster-name i {
            font-size: 1.2rem;
        }

        .mobile-margin {
            margin-left: 0 !important;
        }

        .mobile-align {
            align-items: flex-end !important;
        }

        .icon-sm {
            width: 5.814vw;
            height: 5.814vw;
        }

        .rank_icon {
            width: 10.465vw;
        }

        .champion-icon {
            width: 6.977vw;
            height: auto;
        }

        .rating-badge {
            font-size: 0.6rem;
            padding: 0.2rem 0.4rem;
        }

        .description {
            font-size: 1rem;
            padding: 0.8rem;
        }

        .description h3 {
            font-size: 1.3rem;
        }

        .details {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .detail-card {
            padding: 0.8rem;
        }

        .detail-card .icon {
            width: 9.302vw;
            height: 9.302vw;
        }

        .detail-card .title {
            font-size: 1rem;
        }

        .detail-card .value {
            font-size: 1.1rem;
        }

        .detail-card .roles a {
            width: 6.977vw;
            height: 6.977vw;
        }

        .flag {
            width: 6.977vw;
            height: 6.977vw;
        }

        .top-buttons {
            flex-direction: column;
            gap: 0.3rem;
        }

        .top-buttons button {
            font-size: 0.7rem;
            padding: 0.4rem 0.8rem;
        }

        .sidebar {
            padding: 0.4rem;
            margin-top: 1rem;
        }

        .orders {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .order {
            padding: 0.8rem;
        }

        #ordersTable {
            width: 100%;
            overflow-x: auto;
            display: block;
            /* Allow horizontal scrolling if needed */
        }

        #ordersTable thead {
            display: none;
            /* Hide table headers on smaller screens */
        }

        #ordersTable tbody tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Two columns for the first two cells */
            grid-template-rows: auto auto;
            /* Third cell spans a new row */
            gap: 0;
            /* Add spacing between cells */
            background-color: #f0f2f5;
            /* Maintain row background */
            border-radius: 10px;
            /* Add rounded corners to rows */
            padding: 1rem;
            /* Add spacing around row content */
            margin-bottom: 1rem;
        }

        #ordersTable tbody tr td:first-child {
            grid-column: 1;
            /* Place in the first column */
            grid-row: 1;
            /* Place in the first row */
        }

        #ordersTable tbody tr td:nth-child(2) {
            grid-column: 2;
            /* Place in the second column */
            grid-row: 1;
            /* Place in the first row */
            width: auto !important;
            justify-self: flex-end;
        }

        #ordersTable tbody tr td:last-child {
            grid-column: 1 / -1;
            /* Span across both columns */
            grid-row: 2;
            /* Place in the second row */
            text-align: center;
            /* Center-align for better visibility */
            margin-top: 0;
            /* Add spacing from the previous row */
        }

        #ordersTable tbody tr td {
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        #ordersTable tbody tr td span.icon {
            width: 40px;
            height: 40px;
            aspect-ratio: 1 / 1;
            font-size: 1.2rem;
        }

        #ordersTable tbody tr .type {
            display: flex;
            gap: 0.5rem;
            flex-direction: column;
        }

        #ordersTable tbody tr .type p {
            font-size: 0.9rem;
        }

        #ordersTable tbody tr td span.badge {
            font-size: 0.8rem;
        }

        #loading-spinner {
            display: flex;
            justify-content: center;
        }

        .loader {
            width: 6.977vw;
            border-width: 0.698vw;
        }
    }
</style>
<?= $this->end('styles') ?>



<?= $this->insert('main/components/cta/two') ?>

<script>
    // Quick-select tip buttons
    document.querySelectorAll('.tip-btn').forEach(button => {
        button.addEventListener('click', () => {
            const value = button.getAttribute('data-value');
            document.getElementById('tip-amount').value = value;
        });
    });

    // Plus / Minus tip adjustment
    document.getElementById('tip-plus').addEventListener('click', () => {
        let input = document.getElementById('tip-amount');
        let current = parseFloat(input.value) || 0;
        input.value = (current + 1).toFixed(2);
    });

    document.getElementById('tip-minus').addEventListener('click', () => {
        let input = document.getElementById('tip-amount');
        let current = parseFloat(input.value) || 0;
        if (current > 1) input.value = (current - 1).toFixed(2);
    });
</script>