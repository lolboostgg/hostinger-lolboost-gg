<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => '', 'banner' => 'Contact.gif']) ?>
<section class="container1 py-5">
    <div class="row pb-5 justify-content-center text-center">
        <div class="col-lg-8">
            <h1 class="display-2 mb-4">Thanks for Ordering 🎉</h1>
            <?php if ($invoice['order_type'] == 'tip'): ?>
                <p class="fs-xl pb-4 mb-4">Thank you for choosing us. We've confirmed your payment, and we are glad that you
                    were satisfied with our service. You can click on the button below to go to your profile page.</p>
                <a href="<?= BASE_URL ?>/profile/billing" class="btn btn-lg btn-primary shadow-primary">Profile Page</a>
            <?php elseif ($invoice['order_type'] == 'invoice'): ?>
                <p class="fs-xl pb-4 mb-4">Thank you for your purchase. You can click on the button below to go to your
                    personal order dashboard.</p>
                <a href="<?= BASE_URL ?>/order/<?= $invoice['order_id'] ?? null ?>"
                    class="btn btn-lg btn-primary shadow-primary">View Order</a>
            <?php elseif ($invoice['order_type'] == 'lol_account'): ?>
                <p class="fs-xl pb-4 mb-4">Thank you for your purchase. You can click on the button below to go to your
                    personal order dashboard.</p>
                <a href="<?= BASE_URL ?>/profile/accounts" class="btn btn-lg btn-primary shadow-primary">My Accounts</a>
            <?php else: ?>
                <p class="fs-xl pb-4 mb-4">Thank you for choosing us. We've confirmed your payment. You can click on the
                    button below to go to your personal order dashboard.</p>
                <a href="<?= BASE_URL ?>/<?= $invoice['order_type'] ?? null ?>/<?= $invoice['order_id'] ?? null ?>"
                    class="btn btn-lg btn-primary shadow-primary">View Order</a>
            <?php endif; ?>
        </div>
    </div>
</section>



<hr>


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

