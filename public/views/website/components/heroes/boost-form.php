<header class="<?= $image ?>">
    <div class="content lb-boost-hero__inner">
        <?php if (!empty($icon)): ?>
        <div class="lb-boost-hero__icon" aria-hidden="true">
            <img src="<?= htmlspecialchars($icon, ENT_QUOTES) ?>" alt="">
        </div>
        <?php endif; ?>
        <div class="lb-boost-hero__text">
            <h1>
                <?= $h1 ?>
            </h1>

            <p>
                <?= $description ?>
            </p>
        </div>
    </div>
</header>