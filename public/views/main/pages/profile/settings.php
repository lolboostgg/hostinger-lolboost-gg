<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1']]) ?>

<section class="container">
    <div class="row">

        <?= $this->insert('main/pages/profile/aside', ['active' => 'settings']) ?>

        <div class="col-lg-9 col-md-8 ps-lg-5 pb-5 mb-2 mb-lg-4 mt-n3 mt-md-0">
            <div class="ps-md-3 ps-lg-0 mt-md-2 py-md-4">
                <h2 class="h2 pt-xl-1 pb-3">Edit Settings</h2>
                <h2 class="h5 text-primary mb-4">Basic info</h2>
                <form class="ajax-form border-bottom pb-3 pb-lg-4" novalidate="" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="client_update_profile">
                    <div class="row pb-2">
                        <div class="col-sm-6 mb-4">
                            <label for="usrnm" class="form-label fs-base">Username</label>
                            <input type="text" id="usrnm" name="username" class="form-control form-control-lg" value="<?= CLIENT_DATA['username'] ?>">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label for="dsc" class="form-label fs-base">Discord</label>
                            <input type="text" name="discord" id="dsc" class="form-control form-control-lg" value="<?= CLIENT_DATA['discord'] ?>">
                        </div>
                        <div class="col-12 mb-4">
                            <label for="eml" class="form-label fs-base">Email address</label>
                            <input type="email" name="email" id="eml" class="form-control form-control-lg" value="<?= CLIENT_DATA['email'] ?>">
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                        </button>
                    </div>
                </form>
                <h2 class="h5 text-primary pt-1 mb-4 mt-4">Password</h2>
                <form class="ajax-form border-bottom pb-3 pb-lg-4" novalidate="" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="client_update_password">
                    <div class="row pb-2">
                        <div class="col-sm-6 mb-4">
                            <label for="np" class="form-label fs-base">Current password</label>
                            <div class="password-toggle">
                                <input type="password" name="password" id="np" class="form-control form-control-lg">
                                <label class="password-toggle-btn" aria-label="Show/hide password">
                                    <input class="password-toggle-check" type="checkbox">
                                    <span class="password-toggle-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label for="cnp" class="form-label fs-base">New password</label>
                            <div class="password-toggle">
                                <input type="password" name="new_password" id="cnp" class="form-control form-control-lg">
                                <label class="password-toggle-btn" aria-label="Show/hide password">
                                    <input class="password-toggle-check" type="checkbox">
                                    <span class="password-toggle-indicator"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

<hr>
<?= $this->insert('main/components/testimonials/one') ?>

<?= $this->insert('main/components/cta/two') ?>



<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<?= $this->stop() ?>


<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<style>
    td {
        font-weight: 500;
    }

    .legend-indicator {
        display: inline-block;
        width: 0.5rem;
        height: 0.5rem;
        background-color: #bdc5d1;
        border-radius: 50%;
        margin-right: 0.4375rem;
    }
</style>
<?= $this->stop() ?>