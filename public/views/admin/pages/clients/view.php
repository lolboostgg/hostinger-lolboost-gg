<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Client ' . $data['username'] . ' - Admin Area | LoLBoost.gg'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">

<style>
/* Client admin profile pages: use more horizontal space, less outer padding. */
@media (min-width: 992px) {
  .content.container {
    max-width: calc(100% - 28px) !important;
    padding-left: 14px !important;
    padding-right: 14px !important;
  }

  .content.container > .row.justify-content-lg-center > .col-lg-10 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }
}

@media (min-width: 1400px) {
  .content.container {
    max-width: calc(100% - 40px) !important;
  }
}

/* Keep the old table/card style, only tighten the side spacing a bit. */
.card-table th:first-child,
.card-table td:first-child {
  padding-left: 1rem !important;
}

.card-table th:last-child,
.card-table td:last-child {
  padding-right: 1rem !important;
}

.card-header,
.card-footer {
  padding-left: 1rem !important;
  padding-right: 1rem !important;
}
</style>
<?= $this->end() ?>

<?php
$isDeleted = !empty($data['is_deleted']);
$isBanned = !empty($data['is_banned']) && !$isDeleted;
$displayEmail = $isDeleted ? ($data['deleted_email'] ?: $data['email']) : $data['email'];
?>

<!-- Profile Cover -->
<div class="profile-cover">
  <div class="profile-cover-img-wrapper">
    <img class="profile-cover-img" style="
    object-position: top;" src="<?= ASSET_URL ?>/core/main/img/banners/leona.jpeg" alt="Banner">
  </div>
</div>
<!-- End Profile Cover -->

<!-- Profile Header -->
<div class="text-center mb-5">
  <!-- Avatar -->
  <div class="avatar avatar-xxl avatar-circle profile-cover-avatar">
    <img class="avatar-img" src="<?= $data['icon'] ?>" alt="<?= $data['username'] ?>">
  </div>
  <!-- End Avatar -->

  <?php if ($isDeleted): ?>
    <h1 class="page-header-title"><span class="text-danger"><i class="fa-solid fa-user-slash fs-2 text-danger"
          data-bs-toggle="tooltip" data-bs-placement="top" title="Deleted"></i> <?= $data['username'] ?></span></h1>
    <span class="badge bg-soft-danger text-danger">Account deleted</span>
  <?php elseif ($isBanned): ?>
    <h1 class="page-header-title"><span class="text-danger"><i class="fa-solid fa-ban fs-2 text-danger"
          data-bs-toggle="tooltip" data-bs-placement="top" title="Banned"></i> <?= $data['username'] ?></span></h1>
  <?php else: ?>
    <h1 class="page-header-title"><?= $data['username'] ?></h1>
  <?php endif; ?>
  <!-- List -->
  <ul class="list-inline list-px-2">
    <li class="list-inline-item">
      <span><?= $displayEmail ?><?= $isDeleted ? ' <span class="text-muted">(released)</span>' : '' ?></span>
    </li>
  </ul>
  <!-- End List -->
</div>
<!-- End Profile Header -->

<!-- Nav -->
<div class="hs-nav-scroller-horizontal mb-5">

  <ul class="nav nav-tabs align-items-center">
    <li class="nav-item">
      <a class="nav-link <?= $page == 'profile' ? 'active' : null ?>"
        href="<?= ADMN_URL ?>/client/<?= $data['id'] ?>/profile">Profile</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page == 'orders' ? 'active' : null ?>"
        href="<?= ADMN_URL ?>/client/<?= $data['id'] ?>/orders">Orders</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page == 'accounts' ? 'active' : null ?>"
        href="<?= ADMN_URL ?>/client/<?= $data['id'] ?>/accounts">Accounts</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page == 'payments' ? 'active' : null ?>"
        href="<?= ADMN_URL ?>/client/<?= $data['id'] ?>/payments">Payments</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page == 'coins-history' ? 'active' : null ?>"
        href="<?= ADMN_URL ?>/client/<?= $data['id'] ?>/coins-history">Coins History</a>
    </li>

    <li class="nav-item ms-auto">
      <div class="d-flex gap-2">

        <!-- Dropdown -->
        <div class="dropdown nav-scroller-dropdown">
          <button type="button" class="btn btn-white btn-icon btn-sm" id="profileDropdown" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fa-solid fa-ellipsis-vertical"></i>

          </button>

          <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="profileDropdown">


            <span class="dropdown-header">Danger Zone</span>
            <?php if (!$isDeleted): ?>
              <?php if (!$isBanned): ?>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#ban_client_md">
                  <i class="fa-duotone fa-ban dropdown-item-icon"></i> Ban Client
                </a>
              <?php else: ?>
                <a class="dropdown-item" href="#" data-id="<?= $data['id'] ?>" data-action="admin_unban_client">
                  <i class="fa-duotone fa-rotate-left dropdown-item-icon"></i> Unban Client
                </a>
              <?php endif; ?>
              <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#delete_client_md">
                <i class="fa-duotone fa-user-slash dropdown-item-icon"></i> Delete Account
              </a>
            <?php endif; ?>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#update_coins_modal">
              <img src="<?= ASSET_URL ?>/core/main/img/coin.png"
                style="width: 20px; height: 20px; margin-right: 0.8rem;">
              Update Coins
            </a>
          </div>
        </div>
        <!-- End Dropdown -->
      </div>
    </li>
  </ul>
</div>
<!-- End Nav -->
<?= $this->insert('admin/pages/clients/view/modals', ['data' => $data]) ?>

<!-- Content -->
<?= $this->insert('admin/pages/clients/view/' . $page, ['data' => $data]) ?>
<!-- End Content -->



<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-nav-scroller/dist/hs-nav-scroller.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
  $(document).on('ready', function () {

    <?php if ($page == 'profile'): ?>
      HSCore.components.HSTomSelect.init('.js-select');
      $('select[name="tier_limit"]').on('change', function () {
        if ($(this).val() >= 7) {
          $('select[name="division_limit"]').parent().addClass('d-none');
        } else {
          $('select[name="division_limit"]').parent().removeClass('d-none');
        }
      });
      $('select[name="tier_limit"]').trigger('change');

      // INITIALIZATION OF NAV SCROLLER
      // =======================================================
      new HsNavScroller('.js-nav-scroller');

      // INITIALIZATION OF STICKY BLOCKS
      // =======================================================
      new HSStickyBlock('.js-sticky-block', {
        targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
      });
    <?php elseif ($page == "orders"): ?>
      HSCore.components.HSDatatables.init($('#orders_table'), {
        language: {
          zeroRecords: `<div class="text-center p-4">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
                                          <p class="mb-0">No data to show</p>
                                          </div>`
        }
      });
    <?php elseif ($page == "payments"): ?>
      HSCore.components.HSDatatables.init($('#payments_table'), {
        language: {
          zeroRecords: `<div class="text-center p-4">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
                                          <p class="mb-0">No data to show</p>
                                          </div>`
        }
      });
    <?php elseif ($page == "coins-history"): ?>
      HSCore.components.HSDatatables.init($('#history_table'), {
        language: {
          zeroRecords: `<div class="text-center p-4">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
                                            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
                                          <p class="mb-0">No data to show</p>
                                          </div>`
        }
      });
    <?php endif; ?>

  });
</script>
<?= $this->end() ?>