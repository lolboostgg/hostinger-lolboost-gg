<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add Booster - Admin Area | LoLBoost.gg', 'h1' => 'Add Booster', 'description' => 'Create new booster account.'], 'contain' => false]) ?>
<style>
/* Wider booster admin pages: reduce the large left/right gutters on desktop. */
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


<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
<?= $this->end() ?>

<!-- Form -->
<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="text" name="action" value="admin_add_booster" hidden>
    <!-- Card -->
    <div class="card">
        <!-- Header -->
        <div class="card-header">
            <h3 class="card-header-title">New Booster Form</h3>
        </div>
        <!-- End Header -->
        <div class="card-body">
            <!-- Form Group -->
            <div class="row mb-4">
                <label for="usernameLabel" class="col-sm-3 col-form-label form-label">Username</label>

                <div class="col-sm-9">
                    <input type="text" class="form-control" name="username" id="usernameLabel" placeholder="Username" aria-label="Username">
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4">
                <label for="discordLabel" class="col-sm-3 col-form-label form-label">Discord</label>

                <div class="col-sm-9">
                    <input type="text" class="form-control" name="discord" id="discordLabel" placeholder="Discord#0000" aria-label="Discord">
                </div>
            </div>
            <div class="row mb-4">
                <label for="discordIdLabel" class="col-sm-3 col-form-label form-label">Discord ID</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="discord_id" id="discordIdLabel" placeholder="Discord ID" aria-label="Discord ID">
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4">
                <label for="emailLabel" class="col-sm-3 col-form-label form-label">Email</label>

                <div class="col-sm-9">
                    <input type="email" class="form-control" name="email" id="emailLabel" placeholder="Email address" aria-label="Email address">
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Games</label>

                <div class="col-sm-9 tom-select-custom">
                    <select class="js-select form-select" name="games[]" multiple autocomplete="off">
                        <option value="lol" selected>League of Legends</option>
                        <option value="val">Valorant</option>
                    </select>
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Booster Rank</label>

                <div class="col-sm-9">
                    <!-- Select -->
                    <div class="tom-select-custom">
                        <select class="js-select form-select" name="rank_id" autocomplete="off" data-hs-tom-select-options='{
                            "hideSearch": true
                            }'>
                            <?= util_load_booster_ranks() ?>
                        </select>
                    </div>
                    <!-- End Select -->
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4" id="lol-rank">
                <label class="col-sm-3 col-form-label form-label">LoL Rank Limit</label>

                <div class="col-sm-9 row mx-0">
                    <!-- Select -->
                    <div class="col-9 ps-0">
                        <select class="form-select" name="lol_tier_limit">
                            <option value="1">Iron</option>
                            <option value="2">Bronze</option>
                            <option value="3">Silver</option>
                            <option value="4">Gold</option>
                            <option value="5">Platinum</option>
                            <option value="6" selected> Emerald</option>
                            <option value="7">Diamond</option>
                            <option value="8">Master</option>
                            <option value="9">Grandmaster</option>
                            <option value="10">Challenger</option>
                        </select>
                    </div>
                    <div class="col-3 px-0">
                        <select class="form-select" name="lol_division_limit">
                            <option value="4">I</option>
                            <option value="3" selected>II</option>
                            <option value="2">III</option>
                            <option value="1">IV</option>
                        </select>
                    </div>
                    <!-- End Select -->
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Form Group -->
            <div class="row mb-4" id="val-rank">
                <label class="col-sm-3 col-form-label form-label">Valorant Rank Limit</label>

                <div class="col-sm-9 row mx-0">
                    <!-- Select -->
                    <div class="col-9 ps-0">
                        <select class="form-select" name="val_tier_limit">
                            <option value="1">Iron</option>
                            <option value="2">Bronze</option>
                            <option value="3">Silver</option>
                            <option value="4">Gold</option>
                            <option value="5">Platinum</option>
                            <option value="6">Diamond</option>
                            <option value="7" selected>Ascendent</option>
                            <option value="8">Immortal</option>
                            <option value="9">Radiant</option>
                        </select>
                    </div>
                    <div class="col-3 px-0">
                        <select class="form-select" name="val_division_limit">
                            <option value="3" selected>I</option>
                            <option value="2">II</option>
                            <option value="1">III</option>
                        </select>
                    </div>
                    <!-- End Select -->
                </div>
            </div>
            <!-- End Form Group -->

            <!-- Form Group -->
            <div class="row mb-4" id="tft-rank">
                <label class="col-sm-3 col-form-label form-label">TFT Rank Limit</label>

                <div class="col-sm-9 row mx-0">
                    <div class="col-9 ps-0">
                        <select class="form-select" name="tft_tier_limit">
                            <option value="1">Iron</option>
                            <option value="2">Bronze</option>
                            <option value="3">Silver</option>
                            <option value="4">Gold</option>
                            <option value="5">Platinum</option>
                            <option value="6" selected> Emerald</option>
                            <option value="7">Diamond</option>
                            <option value="8">Master</option>
                            <option value="9">Grandmaster</option>
                            <option value="10">Challenger</option>
                        </select>
                    </div>
                    <div class="col-3 px-0">
                        <select class="form-select" name="tft_division_limit">
                            <option value="4">I</option>
                            <option value="3" selected>II</option>
                            <option value="2">III</option>
                            <option value="1">IV</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- End Form Group -->
            <!-- Game Order Limits -->
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">LoL Solo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="3" class="form-control" name="lol_solo_order_limit" aria-label="LoL Solo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">LoL Duo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="2" class="form-control" name="lol_duo_order_limit" aria-label="LoL Duo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">Valorant Solo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="3" class="form-control" name="val_solo_order_limit" aria-label="Valorant Solo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">Valorant Duo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="2" class="form-control" name="val_duo_order_limit" aria-label="Valorant Duo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">TFT Order Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="3" class="form-control" name="tft_order_limit" aria-label="TFT Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">LoL Classic Solo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="3" class="form-control" name="lol_classic_solo_order_limit" aria-label="LoL Classic Solo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label form-label">LoL Classic Duo Limit</label>
                <div class="col-sm-9"><input type="number" min="0" value="2" class="form-control" name="lol_classic_duo_order_limit" aria-label="LoL Classic Duo Order Limit"></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-9 offset-sm-3"><small class="text-muted">0 means the booster cannot claim orders of that type. Coaching orders are not limited.</small></div>
            </div>
            <!-- End Game Order Limits -->
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">
                    Add Booster
                </span>
                <span class="indicator-progress">
                    <span class="spinner-border spinner-border-sm align-middle"></span>
                </span>
                <span class="indicator-success">
                    <i class="fa-regular fa-circle-check fs-3"></i>
                </span>
            </button>
        </div>
    </div>
    <!-- End Card -->
</form>
<!-- End Form -->

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    $(document).on('ready', function() {
        HSCore.components.HSTomSelect.init('.js-select');
        $('select[name="tier_limit"]').on('change', function() {
            if ($(this).val() >= 7) {
                $('select[name="division_limit"]').parent().addClass('d-none');
            } else {
                $('select[name="division_limit"]').parent().removeClass('d-none');
            }
        });

        $('#val-rank').addClass('d-none');
        $('#tft-rank').addClass('d-none');
        $('select[name="games[]"]').on('change', function() {
            if ($(this).val().includes('lol')) {
                $('#lol-rank').removeClass('d-none');
            } else {
                $('#lol-rank').addClass('d-none');
            }

            if ($(this).val().includes('val')) {
                $('#val-rank').removeClass('d-none');
            } else {
                $('#val-rank').addClass('d-none');
            }

            if ($(this).val().includes('tft')) {
                $('#tft-rank').removeClass('d-none');
            } else {
                $('#tft-rank').addClass('d-none');
            }
        });
    });
</script>
<?= $this->end() ?>