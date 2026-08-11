<?php
$data['features'] = explode('|', $data['features']);
foreach ($data['features'] as $feature):
    ?>

    <div class="d-flex flex-wrap justify-content-start gap-2 align-items-center">
        <span class="fw-600 text-primary">
            <i class="fas fa-check-circle"></i>
        </span>
        <span class="fw-500"><?= $feature ?></span>
    </div>

<?php endforeach ?>