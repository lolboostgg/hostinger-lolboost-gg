<section class="dark-mode bg-overlay position-relative pt-5 bg-dark zindex-4 bg-size-cover bg-repeat-0 skew-scroll" <?= isset($banner) ? 'style="background-image: url(' . ASSET_URL . '/core/main/img/banners/' . $banner . '?v'.rand(0,5454).');background-position: center top;"' : null ?>>

    <!-- Content -->
    <div class="container position-relative zindex-2 py-5 ">

        <div class="row justify-content-center pt-3">
            <div class="col-xl-6 col-lg-7 col-md-8 col-sm-10 text-center">
                <h1 class="mb-4 line-rotate-anim"><?= $title ?></h1>
                <?php if (isset($lead)) : ?>
                    <p class="fs-lg pb-3 mb-3 text-secondary line-rotate-anim"><?= $lead ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>