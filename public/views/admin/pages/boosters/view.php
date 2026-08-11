<?php
$allowedEmails = [
  'r.machmueller@gmx.de',
  'nimm2oder3@gmx.de',
  'hbilalshah@gmail.com',
  'duck_sauce@live.de',
  'lovely@lolboost.gg'
];

?>



<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Booster ' . $data['username'] . ' - Admin Area | LoLBoost.gg'], 'contain' => false]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  .edit-icon-container {
    width: 30px;
    height: 30px;
    background-color: rgb(29, 188, 42);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    bottom: 5px;
    right: 5px;
    border: 1px solid #ccc;
    border: none;
    outline: none;
    padding: 0;
  }

  .edit-icon-container i {
    color: white;
  }

  .edit-cover-container {
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.6);
    border: none;
    color: #fff;
    border-radius: 50%;
    padding: 8px;
    cursor: pointer;
  }

  /* Wider booster detail pages: reduce large desktop gutters. */
  @media (min-width: 992px) {
    body .content.container,
    body .content .container,
    body main .container,
    body .main .container,
    body .page-content .container,
    body .container-fluid {
      max-width: min(1760px, calc(100vw - 48px)) !important;
    }
  }
  @media (min-width: 1400px) {
    body .container,
    body .container-lg,
    body .container-xl,
    body .container-xxl {
      max-width: min(1760px, calc(100vw - 48px)) !important;
    }
  }
  @media (max-width: 991.98px) {
    body .content.container,
    body .content .container,
    body main .container,
    body .main .container,
    body .page-content .container,
    body .container,
    body .container-fluid {
      max-width: 100% !important;
      padding-left: 1rem !important;
      padding-right: 1rem !important;
    }
  }
</style>
<?= $this->end() ?>

<?php
$activeTab = $page ?? 'profile';
include __DIR__ . '/view/_shared.php';
?>



<?= $this->insert('admin/pages/boosters/view/modals', ['data' => $data]) ?>

<!-- Content -->
<?= $this->insert('admin/pages/boosters/view/' . $page, ['data' => $data]) ?>
<!-- End Content -->



<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-nav-scroller/dist/hs-nav-scroller.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
  $(document).on('ready', function () {

    <?php if ($page == 'profile'): ?>
      HSCore.components.HSTomSelect.init('.js-select');
      $('select[name="tier_limit"]').on('change', function () {
        if ($(this).val() >= 8) {
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
    <?php endif; ?>


    $('#dobLabel').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      locale: {
        format: 'DD-MM-YYYY',
      }
    });
  });
</script>
<?= $this->end() ?>