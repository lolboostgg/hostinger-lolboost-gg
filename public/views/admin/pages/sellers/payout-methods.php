<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Seller Payout Methods - Admin Area | LoLBoost.gg', 'h1' => 'Seller', 'description' => 'Manage seller payout methods.']]) ?>
<?php $activeTab = 'payouts'; include __DIR__ . '/_shared.php'; ?>

<?php
global $db;
$methods = $db ? ($db->run("SELECT * FROM seller_payout_methods WHERE seller_id = ? ORDER BY is_default DESC, id DESC", $sellerId) ?: []) : [];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$normalizeMethod = function($method) {
    $m = strtolower(trim((string)$method));
    if ($m === 'crypto' || str_contains($m, 'crypto')) return 'crypto';
    return 'bank';
};

$decodeDetails = function($row) {
    if (empty($row['details'])) return [];
    $tmp = json_decode($row['details'], true);
    return is_array($tmp) ? $tmp : [];
};

$firstOfType = function($needle) use ($methods, $normalizeMethod) {
    $needle = $normalizeMethod($needle);
    foreach ($methods as $m) {
        if ($normalizeMethod($m['method'] ?? '') === $needle) return $m;
    }
    return null;
};

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editMethod = null;
foreach ($methods as $m) {
    if ((int)($m['id'] ?? 0) === $editId) { $editMethod = $m; break; }
}

$bank   = $firstOfType('bank');
$crypto = $firstOfType('crypto');
if ($editMethod) {
    if ($normalizeMethod($editMethod['method'] ?? '') === 'bank')   $bank   = $editMethod;
    if ($normalizeMethod($editMethod['method'] ?? '') === 'crypto') $crypto = $editMethod;
}

$bankDetails   = $bank   ? $decodeDetails($bank)   : [];
$cryptoDetails = $crypto ? $decodeDetails($crypto) : [];

$active = isset($_GET['active']) ? ($_GET['active'] === 'crypto' ? 'crypto' : 'bank') : 'bank';
if ($editMethod && $normalizeMethod($editMethod['method'] ?? '') === 'crypto') $active = 'crypto';

$mask = function($str, $start = 6, $end = 4) use ($h) {
    $s = (string)$str;
    if ($s === '') return '';
    if (strlen($s) <= ($start + $end + 3)) return $h($s);
    return $h(substr($s, 0, $start) . '...' . substr($s, -$end));
};
?>

<style>
.pm-choice { cursor:pointer; user-select:none; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.pm-choice:hover { border-color:rgba(255,255,255,.14); }
.pm-choice.active { outline:2px solid rgba(59,130,246,.45); box-shadow:0 0 0 .25rem rgba(59,130,246,.10); }
.pm-choice .iconwrap { width:44px; height:44px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); }
.pm-muted { color:rgba(255,255,255,.55); }
</style>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-soft-success mb-4"><i class="fa-duotone fa-check me-2"></i>Saved successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="card-header-title mb-0">Payout Methods</h4>
                <p class="text-muted mb-0 mt-1 small">Bank Transfer and Crypto payout details for this seller.</p>
            </div>
            <span class="badge bg-soft-secondary text-secondary"><?= count($methods) ?> method<?= count($methods) === 1 ? '' : 's' ?></span>
        </div>
    </div>

    <div class="card-body">

        <!-- Method type selector -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card pm-choice <?= $active === 'bank' ? 'active' : '' ?>" data-pm="bank">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="iconwrap"><i class="fa-solid fa-building-columns fs-3"></i></div>
                            <div>
                                <div class="fw-semibold">Bank Transfer</div>
                                <div class="pm-muted small">IBAN / SWIFT / Beneficiary</div>
                            </div>
                        </div>
                        <?php if ($bank): ?>
                            <span class="badge bg-soft-success text-success">Saved</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card pm-choice <?= $active === 'crypto' ? 'active' : '' ?>" data-pm="crypto">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="iconwrap"><i class="fa-brands fa-bitcoin fs-3"></i></div>
                            <div>
                                <div class="fw-semibold">Crypto</div>
                                <div class="pm-muted small">Wallet address / network</div>
                            </div>
                        </div>
                        <?php if ($crypto): ?>
                            <span class="badge bg-soft-success text-success">Saved</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank panel -->
        <div id="pm-panel-bank" class="<?= $active === 'bank' ? '' : 'd-none' ?>">
            <form action="<?= ADMN_URL ?>/seller/<?= $sellerId ?>/payout-methods/save" method="post">
                <input type="hidden" name="method" value="bank_transfer">
                <input type="hidden" name="method_id" value="<?= (int)($bank['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Beneficiary name</label>
                        <input class="form-control" name="beneficiary" value="<?= $h($bankDetails['beneficiary'] ?? '') ?>" placeholder="John Doe">
                    </div>
                    <div class="col-12">
                        <label class="form-label">IBAN</label>
                        <input class="form-control" name="iban" value="<?= $h($bankDetails['iban'] ?? '') ?>" placeholder="DE00 0000 0000 0000 0000 00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SWIFT / BIC</label>
                        <input class="form-control" name="swift" value="<?= $h($bankDetails['swift'] ?? '') ?>" placeholder="BANKDEFFXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank name (optional)</label>
                        <input class="form-control" name="bank_name" value="<?= $h($bankDetails['bank_name'] ?? '') ?>" placeholder="Postbank">
                    </div>
                    <div class="col-12 d-flex align-items-center justify-content-between pt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="make_default" value="1" id="makeDefaultBank" <?= !empty($bank['is_default']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="makeDefaultBank">Set as default payout method</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Crypto panel -->
        <div id="pm-panel-crypto" class="<?= $active === 'crypto' ? '' : 'd-none' ?>">
            <form action="<?= ADMN_URL ?>/seller/<?= $sellerId ?>/payout-methods/save" method="post">
                <input type="hidden" name="method" value="crypto">
                <input type="hidden" name="method_id" value="<?= (int)($crypto['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Coin</label>
                        <input class="form-control" name="coin" value="<?= $h($cryptoDetails['coin'] ?? '') ?>" placeholder="USDT">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Network</label>
                        <input class="form-control" name="network" value="<?= $h($cryptoDetails['network'] ?? '') ?>" placeholder="TRC-20">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Wallet address</label>
                        <input class="form-control" name="address" value="<?= $h($cryptoDetails['address'] ?? '') ?>" placeholder="0x...">
                    </div>
                    <div class="col-12 d-flex align-items-center justify-content-between pt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="make_default" value="1" id="makeDefaultCrypto" <?= !empty($crypto['is_default']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="makeDefaultCrypto">Set as default payout method</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <hr class="my-4" style="border-color:rgba(255,255,255,.08)">

        <!-- All methods table -->
        <h5 class="mb-3">All saved methods</h5>
        <?php if (empty($methods)): ?>
            <div class="alert alert-soft-secondary mb-0">No payout methods found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:80px">ID</th>
                            <th style="width:140px">Type</th>
                            <th>Details</th>
                            <th style="width:110px">Default</th>
                            <th style="width:180px">Created</th>
                            <th class="text-end" style="width:220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($methods as $m):
                            $id     = (int)($m['id'] ?? 0);
                            $type   = $normalizeMethod($m['method'] ?? '') === 'crypto' ? 'Crypto' : 'Bank Transfer';
                            $d      = $decodeDetails($m);

                            if ($normalizeMethod($m['method'] ?? '') === 'crypto') {
                                $parts = array_filter([
                                    strtoupper((string)($d['coin'] ?? '')),
                                    (string)($d['network'] ?? ''),
                                    $d['address'] ? $mask($d['address']) : '',
                                ]);
                                $detailText = implode(' ', $parts) ?: '—';
                            } else {
                                $ben  = (string)($d['beneficiary'] ?? '');
                                $iban = preg_replace('/\s+/', '', (string)($d['iban'] ?? ''));
                                $parts = array_filter([$ben, $iban ? '****'.substr($iban,-6) : '']);
                                $detailText = implode(' • ', $parts) ?: '—';
                            }

                            $isDefault = !empty($m['is_default']);
                            $created   = $m['created_at'] ?? '—';
                        ?>
                        <tr>
                            <td class="text-muted">#<?= $id ?></td>
                            <td><?= $h($type) ?></td>
                            <td class="text-muted"><?= $h($detailText) ?></td>
                            <td>
                                <?= $isDefault
                                    ? '<span class="badge bg-soft-success text-success">Yes</span>'
                                    : '<span class="badge bg-soft-secondary text-secondary">No</span>' ?>
                            </td>
                            <td class="text-muted"><?= $h($created) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-white"
                                   href="<?= ADMN_URL ?>/seller/<?= $sellerId ?>/payout-methods?edit=<?= $id ?>&active=<?= $normalizeMethod($m['method'] ?? '') === 'crypto' ? 'crypto' : 'bank' ?>">
                                    Edit
                                </a>
                                <form class="d-inline" action="<?= ADMN_URL ?>/seller/<?= $sellerId ?>/payout-methods/set-default" method="post">
                                    <input type="hidden" name="method_id" value="<?= $id ?>">
                                    <button class="btn btn-sm btn-white" type="submit">Set default</button>
                                </form>
                                <form class="d-inline" action="<?= ADMN_URL ?>/seller/<?= $sellerId ?>/payout-methods/delete" method="post"
                                      onsubmit="return confirm('Delete this payout method?');">
                                    <input type="hidden" name="method_id" value="<?= $id ?>">
                                    <button class="btn btn-sm btn-soft-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const cards      = document.querySelectorAll('.pm-choice');
    const bankPanel  = document.getElementById('pm-panel-bank');
    const cryptoPanel = document.getElementById('pm-panel-crypto');

    function setActive(which) {
        cards.forEach(c => c.classList.toggle('active', c.dataset.pm === which));
        if (bankPanel)   bankPanel.classList.toggle('d-none',   which !== 'bank');
        if (cryptoPanel) cryptoPanel.classList.toggle('d-none', which !== 'crypto');
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('active', which);
            window.history.replaceState({}, '', url.toString());
        } catch(e) {}
    }

    cards.forEach(c => c.addEventListener('click', () => setActive(c.dataset.pm)));
})();
</script>
