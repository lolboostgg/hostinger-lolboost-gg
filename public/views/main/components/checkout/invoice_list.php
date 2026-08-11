<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Client ID: </span>
    <span class="fw-500 text-primary"><?= $data['client_id'] ?></span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Username: </span>
    <span class="fw-500 text-primary"><?= db_get_row('clients', ['id' => $data['client_id']])['username'] ?></span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <span class="fw-600">Description: </span>
    <span class="fw-500 text-primary"><?= $data['description'] ?></span>
</div>