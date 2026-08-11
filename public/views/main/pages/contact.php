<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', ['title' => 'Get in Touch', 'lead' => 'We\'re here to help, contact us through Live Chat or Discord.', 'banner' => 'Contact.gif']) ?>

<?= $this->insert('main/components/features/contact') ?>

<?= $this->insert('main/components/faq/one') ?>

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
<?= $this->stop() ?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');
</script>