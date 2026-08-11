<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', ['title' => 'Gaming Blog', 'lead' => 'Browse through a wide variety of gaming guides and articles', 'banner' => 'Blog.gif']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>

<article class="container my-5">

    <div>
        <i class="fa-duotone fa-calendar me-1"></i>
        <span class="fw-normal">Published at: </span>
        <time datetime="<?= $article['updated_at'] ?>"
            class="fw-normal"><?= util_format_date_display_hm($article['updated_at']) ?></time>
    </div>

    <div class="w-75 h-75 text-center">

    </div>

    <hr>

    <div class="mt-3">
        <?php
        if (strtotime($article['created_at']) <= strtotime('2025-09-13')) {
            echo parse_article_content($article['content']);
        } else {
            echo $article['content'];
        }
        ?>
    </div>
</article>

<hr>


<?= $this->insert('main/components/cta/two') ?>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<?= $this->stop() ?>