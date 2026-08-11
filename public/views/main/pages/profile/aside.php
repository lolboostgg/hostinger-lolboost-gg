<aside class="col-lg-3 col-md-4 border-end pb-5 mt-n5">
    <div class="position-sticky top-0">
        <div class="text-center pt-5">
            <div class="d-table position-relative mx-auto mt-2 mt-lg-4 pt-5 mb-3">
                <img src="<?= 'https://ui-avatars.com/api/?background=0C0F19&color=B5BBC0&name=' . str_replace('#', ' ', CLIENT_DATA['username']) ?>" class="d-block rounded-circle" width="120" alt="<?= CLIENT_DATA['username'] ?>">
            </div>
            <h2 class="h5 mb-1"><?= CLIENT_DATA['username'] ?></h2>
            <p class="mb-3 pb-3"><?= CLIENT_DATA['email'] ?></p>
            <button type="button" class="btn btn-secondary w-100 d-md-none mt-n2 mb-3" data-bs-toggle="collapse" data-bs-target="#account-menu">

                Account Menu
                <i class="fa-solid fa-caret-down ms-1"></i>
            </button>
            <div id="account-menu" class="list-group list-group-flush collapse d-md-block">
                <a href="<?=BASE_URL?>/profile/orders" class="list-group-item list-group-item-action d-flex align-items-center <?= $active == 'orders' ? 'active' : null ?>">
                <i class="fa-duotone fa-files opacity-60 me-2"></i>
                    Orders
                </a>
                <a href="<?=BASE_URL?>/profile/accounts" class="list-group-item list-group-item-action d-flex align-items-center <?= $active == 'accounts' ? 'active' : null ?>">
                <i class="fa-solid fa-shield opacity-60 me-2"></i>
                    Accounts
                </a>
                <a href="<?=BASE_URL?>/profile/billing" class="list-group-item list-group-item-action d-flex align-items-center <?= $active == 'billing' ? 'active' : null ?>">
                <i class="fa-duotone fa-credit-card opacity-60 me-2"></i>
                    Billing
                </a>
                <a href="<?=BASE_URL?>/profile/settings" class="list-group-item list-group-item-action d-flex align-items-center <?= $active == 'settings' ? 'active' : null ?>">
                    <i class="fa-duotone fa-gear opacity-60 me-2"></i>
                    Settings
                </a>
                <a href="<?=BASE_URL?>/logout" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="fa-duotone fa-right-from-bracket opacity-60 me-2"></i>
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</aside>