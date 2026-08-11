<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1']]) ?>

<section class="container">
    <div class="row">

        <?= $this->insert('main/pages/profile/aside', ['active' => 'accounts']) ?>

        <div class="col-lg-9 col-md-8 ps-lg-5 pb-5 mb-2 mb-lg-4 mt-n3 mt-md-0">
            <div class="ps-md-3 ps-lg-0 mt-md-2 py-md-4">
                <h2 class="h2 pt-xl-1">Accounts List</h2>
                <div class="table-responsive border rounded">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="bg-faded-primary">ID</th>
                                <th scope="col" class="bg-faded-primary">Account</th>
                                <th scope="col" class="bg-faded-primary">Login</th>
                                <th scope="col" class="bg-faded-primary">Password</th>
                                <th scope="col" class="bg-faded-primary">Data</th>
                                <th scope="col" class="bg-faded-primary">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account) : ?>
                                <tr>
                                    <td>#<?= $account['id'] ?></td>
                                    <td><?= util_format_default_type($account['name']) ?></td>
                                    <td><?= $account['login'] ?></td>
                                    <td><?= $account['password'] ?></td>
                                    <td><?= $account['data'] ?></td>
                                    <td><?= util_format_currency_display('EUR') . util_format_price_display($account['price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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