<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Tip for: </span>
    <span class="fw-500 text-primary"><?= db_get_row('boosters', ['id' => $data['booster_id']])['username'] ?></span>
</div>