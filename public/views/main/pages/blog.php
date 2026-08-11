<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', ['title' => 'Gaming Blog', 'lead' => 'Browse through a wide variety of gaming guides and articles', 'banner' => 'Blog.gif']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>

<div class="container text-center mt-3 mb-5 p-3">
    <div class="row d-flex">
        <?php foreach ($articles as $article) : ?>
            <div class="col-lg-4 col-md-6 col-sm-12 border border-1 rounded p-2 bg-secondary">
                <div class="card h-100">
                    <img src="<?= $article['image_url'] ?>" class="card-img-top rounded-top" alt="<?= $article['title'] ?>">
                    <div class="card-body text-start pb-1">
                        <a href="<?= BASE_URL ?>/blog/<?= $article['slug'] ?>" class="text-decoration-none">
                            <h2 class="card-title fs-5"><?= $article['title'] ?></h2>
                        </a>
                        <p class="card-text"><?= substr($article['excerpt'], 0, 150) . '...' ?></p>
                    </div>
                    <div class="d-flex card-footer justify-content-between align-items-start">
                        <div>
                            <i class="fa-duotone fa-calendar me-1"></i>
                            <time datetime="<?= $article['updated_at'] ?>" class="fw-normal"><?= util_format_date_display($article['updated_at']) ?></time>
                        </div>
                        <a href="<?= BASE_URL ?>/blog/<?= $article['slug'] ?>" class="link-info text-decoration-none fw-medium">Read Article</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pagination -->
<div class="container  mt-3 mb-5 p-3">
    <!-- <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">Previous</a>
            </li>
            <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">Next</a>
            </li>
        </ul>
    </nav> -->

    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end">
            <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++) : ?>
                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>



<hr>

<script>
    if (<?= $pagination['page'] ?> == 1) {
        window.history.replaceState({}, document.title, "<?= BASE_URL ?>/blog");
    }
</script>

<?= $this->insert('main/components/cta/two') ?>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js?v=<?= rand(0, 9340) ?>"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<?= $this->stop() ?>