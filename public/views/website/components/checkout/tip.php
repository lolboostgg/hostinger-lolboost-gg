<div class="order-options">
    <div class="option">
        <div class="title"><?= t('Tip for:') ?></div>
        <div class="value">
            <?= db_get_row('boosters', ['id' => $data['booster_id']])['username'] ?>
        </div>
    </div>

    <?php
    $invoice = db_get_row('invoices', ['order_id' => $data['order_id']]);

    if ($invoice['coins_used'] != 0.00): ?>
        <div class="option">
            <div class="title">
                <img src="<?= ASSET_URL ?>/core/main/img/coin.png"><?= t('LB Coins Spent') ?></div>
            <div class="value">
                <?= $invoice['coins_used'] ?>
            </div>
        </div>
    <?php endif; ?>
</div>