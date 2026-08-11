<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', ['title' => '', 'lead' => '']) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<?= $this->stop() ?>
<?php
$h2 = function ($text) {
    return "<h2 class=\"h4 mb-1 mt-5\">$text</h2>";
};

$p = function ($text) {
    return "<p class=\"mb-2\">$text</p>";
};

?>


<section class="container mt-4 pt-lg-2 pb-3">
    <h2 class="pb-1 mb-2" style="max-width: 970px;">Imprint</h2>
    <div class="d-flex flex-md-row flex-column align-items-md-center justify-content-md-between mb-3">
        <div class="d-flex align-items-center flex-wrap text-muted mb-md-0 mb-4">
            <div class="fs-xs border-end pe-3 me-3 mb-2">
                <span class="badge bg-faded-primary text-primary fs-base">Legal</span>
            </div>
            <div class="fs-sm pe-3 me-3 mb-2">5 January, 2023</div>
        </div>
    </div>
</section>

<section class="container mb-5 pt-4 pb-2 py-mg-4">
    <div class="row gy-4">
        <div class="col-12">
            <?= $h2('Registered Company Name') ?>
            <?= $p('LB Gaming Services LTD') ?>
            <?= $h2('Registered Address') ?>
            <?= $p('71-75 Shelton Street <br>Covent Garden<br>London <br>WC2H 9JQ<br>United Kingdom') ?>
            <?= $h2('Company Number') ?>
            <?= $p('14571562') ?>
            <?= $h2('Contact') ?>
            <?= $p('Email: <a href="mailto:admin@lolboost.gg">admin@lolboost.gg</a> <br>Phone: +49 1522 3458817') ?>

        </div>
    </div>
</section>