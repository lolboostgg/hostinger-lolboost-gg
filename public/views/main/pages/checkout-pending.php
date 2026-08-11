<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => 'Your payment is being processed, thank you!']) ?>
<section class="container py-5 ">
    <div class="row pb-5 ">
        <div class="col-lg-6">
            <h1 class="display-2 mb-0">Payment Pending ⏳</h1>
        </div>
        <div class="col-lg-6 col-xl-5 offset-xl-1 pt-3 pt-sm-4 pt-lg-3">
            <p class="fs-xl pb-4 mb-1 mb-md-2 mb-xl-3">Thank you for choosing us. We're currently processing your
                payment, you will get an email once we're done.</p>
            <a href="<?= BASE_URL ?>/discord" class="btn btn-lg btn-primary shadow-primary">Join Discord</a>
        </div>
    </div>
</section>

<hr>


<?= $this->insert('main/components/sliders/hiw-1') ?>


<?= $this->insert('main/components/testimonials/one') ?>

<?= $this->insert('main/components/cta/two') ?>

<div class="py-5"></div>

<?= $this->insert('main/components/faq/one') ?>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<?= $this->stop() ?>



<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>

