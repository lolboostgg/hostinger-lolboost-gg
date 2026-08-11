<div class="d-flex flex-column align-items-center justify-content-center bg-secondary gap-2 px-5 py-2">
    <div class="d-flex gap-2 align-items-center fw-bold">
        <img src="<?php echo util_rank_img('lol', 'mini', $data['current_rank']); ?>" width="30px" alt="">
        <span><?= util_get_lol_rank($data['current_rank']) ?></span>
        -
        [<?= strtoupper($data['server']) ?>]
    </div>
    <?= $data['title'] ?>
</div>