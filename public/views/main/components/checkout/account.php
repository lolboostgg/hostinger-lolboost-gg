<div class="d-flex align-items-center justify-content-center gap-2 bg-secondary px-5 py-2">


    <div class="d-flex flex-column gap-2 align-items-center">
        <img src="<?= ASSET_URL.'/core/main/img/'.$data['icon'].'.svg' ?>" width="30px" alt="">
        <span class="fw-500"><span class="fw-bold"><?= strtoupper($data['server']) ?> </span> <?= $data['name'] ?></span>
    </div>

</div>