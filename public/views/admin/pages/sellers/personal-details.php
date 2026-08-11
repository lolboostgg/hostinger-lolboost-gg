<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Personal Details - Admin Area | LoLBoost.gg', 'h1' => 'Seller', 'description' => 'Seller onboarding identity details and documents.']]) ?>
<?php $activeTab = 'personal-details'; include __DIR__ . '/_shared.php'; ?>

<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$fullName = trim((string)($data['fullname'] ?? ''));
$dob = trim((string)($data['dob'] ?? ''));
$address = trim((string)($data['address'] ?? ''));
$country = trim((string)($data['country'] ?? ''));
$idFront = trim((string)($data['id_front'] ?? ''));
$idBack = trim((string)($data['id_back'] ?? ''));
$selfie = trim((string)($data['selfie'] ?? ''));
$createdAt = trim((string)($data['created_at'] ?? ''));
$updatedAt = trim((string)($data['updated_at'] ?? ''));

$formatDateTime = function($value) {
    if (empty($value)) return '—';
    $ts = strtotime((string)$value);
    return $ts ? date('d.m.Y H:i', $ts) : (string)$value;
};

$assetLink = function($path) use ($h) {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    return '/' . ltrim($path, '/');
};

$docCard = function($title, $description, $path, $icon) use ($h, $assetLink) {
    $url = $assetLink($path);
    ?>
    <div class="col-md-4">
        <div class="card h-100 seller-doc-card">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="seller-doc-icon"><i class="fa-duotone <?= $h($icon) ?>"></i></div>
                    <div>
                        <h5 class="mb-1"><?= $h($title) ?></h5>
                        <div class="text-muted small"><?= $h($description) ?></div>
                    </div>
                </div>

                <?php if ($url !== ''): ?>
                    <div class="seller-doc-preview mb-3">
                        <a href="<?= $h($url) ?>" target="_blank" rel="noopener">
                            <img src="<?= $h($url) ?>" alt="<?= $h($title) ?>">
                        </a>
                    </div>
                    <div class="mt-auto d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-primary" href="<?= $h($url) ?>" target="_blank" rel="noopener">
                            <i class="fa-duotone fa-up-right-from-square me-1"></i> Open
                        </a>
                        <a class="btn btn-sm btn-white" href="<?= $h($url) ?>" download>
                            <i class="fa-duotone fa-download me-1"></i> Download
                        </a>
                    </div>
                <?php else: ?>
                    <div class="seller-doc-empty mt-auto">
                        <i class="fa-duotone fa-file-slash me-2"></i> No document uploaded
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
};
?>

<style>
.seller-info-row {
    display: grid;
    grid-template-columns: 210px 1fr;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.seller-info-row:last-child { border-bottom: 0; }
.seller-info-label {
    color: rgba(255,255,255,.55);
    font-weight: 700;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.seller-info-value { color: rgba(255,255,255,.88); word-break: break-word; }
.seller-doc-card { border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.02); }
.seller-doc-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    font-size: 18px;
}
.seller-doc-preview {
    height: 220px;
    border-radius: 14px;
    overflow: hidden;
    background: rgba(0,0,0,.22);
    border: 1px solid rgba(255,255,255,.08);
}
.seller-doc-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.seller-doc-empty {
    border-radius: 14px;
    padding: 1rem;
    background: rgba(255,255,255,.03);
    border: 1px dashed rgba(255,255,255,.14);
    color: rgba(255,255,255,.55);
}
@media (max-width: 767.98px) {
    .seller-info-row { grid-template-columns: 1fr; gap: .35rem; }
}
</style>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-header-title mb-0">Personal Details</h4>
                    <p class="text-muted small mb-0 mt-1">Submitted during seller onboarding.</p>
                </div>
                <span class="badge <?= $statusClass ?>"><i class="fa-duotone <?= $statusIcon ?> me-1"></i><?= $statusLabel ?></span>
            </div>
            <div class="card-body">
                <div class="seller-info-row">
                    <div class="seller-info-label">Full Name</div>
                    <div class="seller-info-value"><?= $fullName !== '' ? $h($fullName) : '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Date of Birth</div>
                    <div class="seller-info-value"><?= $dob !== '' ? $h($dob) : '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Country</div>
                    <div class="seller-info-value"><?= $country !== '' ? $h($country) : '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Address</div>
                    <div class="seller-info-value" style="white-space:pre-wrap;"><?= $address !== '' ? $h($address) : '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Email</div>
                    <div class="seller-info-value"><?= $email ?: '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Discord</div>
                    <div class="seller-info-value"><?= $discord ?: '—' ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Submitted</div>
                    <div class="seller-info-value"><?= $h($formatDateTime($createdAt)) ?></div>
                </div>
                <div class="seller-info-row">
                    <div class="seller-info-label">Last Updated</div>
                    <div class="seller-info-value"><?= $h($formatDateTime($updatedAt)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-header-title mb-0">Review Notes</h4>
            </div>
            <div class="card-body">
                <?php if ($applicationNote !== ''): ?>
                    <div class="text-muted small" style="white-space:pre-wrap;"><?= $h($applicationNote) ?></div>
                <?php else: ?>
                    <div class="text-muted">No application note saved.</div>
                <?php endif; ?>
                <div class="alert alert-soft-info mt-4 mb-0">
                    <i class="fa-duotone fa-shield-check me-2"></i>
                    Use these details only for identity verification and seller account review.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="card-header-title mb-0">Identity Documents</h4>
            <p class="text-muted small mb-0 mt-1">Uploaded ID photos and selfie from onboarding.</p>
        </div>
        <span class="badge bg-soft-secondary text-secondary">
            <?= (int)($idFront !== '') + (int)($idBack !== '') + (int)($selfie !== '') ?>/3 uploaded
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php $docCard('ID Card / Passport Front', 'Readable front side', $idFront, 'fa-id-card'); ?>
            <?php $docCard('ID Card / Passport Back', 'Readable back side', $idBack, 'fa-address-card'); ?>
            <?php $docCard('Selfie Holding ID', 'Selfie next to the ID', $selfie, 'fa-camera'); ?>
        </div>
    </div>
</div>
