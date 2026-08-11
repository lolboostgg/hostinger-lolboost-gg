<?= $this->layout('main/layouts/main', ['meta' => $meta]); ?>

<?= $this->insert('main/components/heroes/three', ['title' => $meta['h1'], 'lead' => $meta['description'], 'banner' => 'lol.gif']) ?>

<div class="container my-5">
    <div class="row mt-3">
        <?php foreach ($prizes as $prize): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?= $prize['image'] ?>" class="card-img-top" alt="Prize Image">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"><?= $prize['name'] ?></h5>
                            <p class="text-primary">
                                <img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="Coin Icon" width="22" height="22"
                                    class="d-inline-block">
                                <span class="fw-bold"><?= $prize['points'] ?></span>
                            </p>
                        </div>
                        <p class="card-text"><?= $prize['description'] ?></p>
                        <?php if (CLIENT_DATA): ?>
                            <form class="ajax-form" action="<?= AJAX_URL ?>" autocomplete="off">
                                <input type="hidden" name="action" value="redeem_prize">
                                <input type="hidden" name="prize_id" value="<?= $prize['id'] ?>">
                                <input type="hidden" name="prize_points" value="<?= $prize['points'] ?>">
                                <input type="hidden" name="client_id" value="<?= CLIENT_DATA['id'] ?>">
                                <input type="hidden" name="client_points" value="<?= CLIENT_DATA['points'] ?>">

                                <button type="submit" class="btn btn-primary w-100 redeem-prize" data-id="<?= $prize['id'] ?>"
                                    data-points="<?= $prize['points'] ?>" <?php echo $prize['points'] > CLIENT_DATA['points'] ? 'disabled' : '' ?>>
                                    <i class="fa-duotone fa-gift me-2"></i>
                                    <?php echo $prize['points'] > CLIENT_DATA['points'] ? 'Not Enough Points' : 'Redeem' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#auth_modal"
                                class="btn btn-primary w-100">Login to Redeem</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>