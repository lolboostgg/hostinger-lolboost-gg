<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Boost Type</span>
    <span class="fw-500 text-primary"><?= $data['name'] ?></span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Server</span>
    <span class="fw-500 text-primary"><?= util_format_server($data['server']) ?></span>
</div>
<?php if (!empty($data['queue_type'])): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <span class="fw-600">Queue</span>
        <span class="fw-500 text-primary"><?= util_format_default_type($data['queue_type']) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($data['coach_type'])): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <span class="fw-600">Coach Type</span>
        <span class="fw-500 text-primary"><?= util_format_default_type($data['coach_type']) ?></span>
    </div>
<?php endif; ?>

<?php if (isset($data['form_id']) && !in_array($data['form_id'], [15, 16])): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <span class="fw-600">Is Duo</span>
        <span class="fw-500 text-primary"><?= util_format_yes_no($data['is_duo']) ?></span>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Coupon</span>
    <span class="fw-500 text-primary"><?= util_format_discount_display($data['id']) ?></span>
</div>

<?php
$invoice = db_get_row('invoices', ['order_id' => $data['order_id']]);
?>

<?php if ($invoice['coins_used'] != 0.00): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center coins-list">
        <span class="fw-600">LB Coins Spent</span>
        <span class="fw-500 text-primary"><?= $invoice['coins_used'] ?></span>
    </div>
<?php else: ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center coins-list">
    </div>
<?php endif; ?>

<?php if (isset($data['form_id']) && !in_array($data['form_id'], [15, 16])): ?>
    <div class="mt-2">
        <div class="d-flex gap-2 align-items-center">
            <span class="fw-600 d-block">Request Booster</span>
            <span class="fw-500 fs-xs text-secondary-dark">(Optional)</span>
        </div>
        <select class="form-select" name="booster_id" id="booster_id">
            <option value="" selected>Search for a Booster...</option>
            <?php foreach ($data['boosters'] as $booster): ?>
                <option value="<?= $booster['id'] ?>"><?= $booster['username'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="">
        <label for="order-note" class="fw-600 d-block">
            Note for the Booster
            <span class="fw-500 fs-xs text-secondary-dark ms-2">(Optional)</span>
        </label>
        <div class="d-flex gap-1">
            <textarea class="form-control" type="text" name="order_note" id="order-note"
                placeholder="What should the booster know before he claims your order?"><?= db_get_row('order_notes', ['order_id' => $data['order_id']])['order_note'] ?? '' ?></textarea>
        </div>
    </div>
    
<?php endif; ?>

<?php if (isset($data['form_id']) && in_array($data['form_id'], [15, 16])): ?>
    <div class="mt-2">
        <div class="d-flex gap-2 align-items-center">
            <span class="fw-600 d-block">Request Coach</span>
            <span class="fw-500 fs-xs text-secondary-dark">(Optional)</span>
        </div>
        <select class="form-select" name="booster_id" id="booster_id">
            <option value="" selected>Search for a Coach...</option>
            <?php foreach ($data['boosters'] as $booster): ?>
                <option value="<?= $booster['id'] ?>"><?= $booster['username'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="">
        <label for="order-note" class="fw-600 d-block">
            Note for the Booster
            <span class="fw-500 fs-xs text-secondary-dark ms-2">(Optional)</span>
        </label>
        <div class="d-flex gap-1">
            <textarea class="form-control" type="text" name="order_note" id="order-note"
                placeholder="What should the Coach know before he claims your order?"><?= db_get_row('order_notes', ['order_id' => $data['order_id']])['order_note'] ?? '' ?></textarea>
        </div>
    </div>
    
<?php endif; ?>