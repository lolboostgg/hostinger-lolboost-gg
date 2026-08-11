<?php
/**
 * Partial template: admin/pages/boosters/view/payout-methods
 * This file is included from the admin booster "view" page.
 *
 * Requires:
 * - $data['id'] (booster id)
 * - $data['payout_methods'] (array)
 */

$methods   = $data['payout_methods'] ?? [];
$boosterId = (int)($data['id'] ?? 0);

$h = function ($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

$mask = function ($str, $start = 6, $end = 4) use ($h) {
  $s = (string)$str;
  if ($s === '') return '';
  if (strlen($s) <= ($start + $end + 3)) return $h($s);
  return $h(substr($s, 0, $start) . '...' . substr($s, -$end));
};

$decodeDetails = function ($row) {
  if (empty($row['details'])) return [];
  $tmp = json_decode($row['details'], true);
  return is_array($tmp) ? $tmp : [];
};


$normalizeMethod = function ($method) {
  $m = strtolower(trim((string)$method));
  if ($m === 'crypto') return 'crypto';
  if (in_array($m, ['bank', 'bank_transfer', 'bank-transfer', 'banktransfer'], true)) return 'bank';
  // fallback: treat anything non-crypto as bank (we only support Bank + Crypto here)
  if (strpos($m, 'crypto') !== false) return 'crypto';
  return 'bank';
};

$firstOfType = function ($needle) use ($methods, $normalizeMethod) {
  $needle = $normalizeMethod($needle);
  if (!is_array($methods)) return null;
  foreach ($methods as $m) {
    if ($normalizeMethod($m['method'] ?? '') === $needle) return $m;
  }
  return null;
};

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editMethod = null;
if ($editId > 0 && is_array($methods)) {
  foreach ($methods as $m) {
    if ((int)($m['id'] ?? 0) === $editId) {
      $editMethod = $m;
      break;
    }
  }
}

$bank   = $firstOfType('bank');
$crypto = $firstOfType('crypto');

// If editing a specific method, override that type's prefill
if ($editMethod) {
  if ($normalizeMethod($editMethod['method'] ?? '') === 'bank') $bank = $editMethod;
  if ($normalizeMethod($editMethod['method'] ?? '') === 'crypto') $crypto = $editMethod;
}

$bankDetails   = $bank ? $decodeDetails($bank) : [];
$cryptoDetails = $crypto ? $decodeDetails($crypto) : [];

$active = isset($_GET['active']) ? ($_GET['active'] === 'crypto' ? 'crypto' : 'bank') : null;
if ($active === null) $active = 'bank';
if ($editMethod && $normalizeMethod($editMethod['method'] ?? '') === 'crypto') $active = 'crypto';
if ($editMethod && $normalizeMethod($editMethod['method'] ?? '') === 'bank') $active = 'bank';
?>

<style>
  :root{
    --pm-bg: rgba(255,255,255,.025);
    --pm-surface: rgba(255,255,255,.04);
    --pm-surface-2: rgba(255,255,255,.065);
    --pm-border: rgba(255,255,255,.09);
    --pm-border-strong: rgba(124,92,255,.34);
    --pm-text-muted: rgba(255,255,255,.58);
    --pm-purple: #7c5cff;
    --pm-purple-soft: rgba(124,92,255,.16);
    --pm-green: #1fe6c6;
    --pm-red: #ff6b8a;
    --pm-yellow: #f8c76a;
  }

  .pm-admin-card{
    border:1px solid var(--pm-border) !important;
    background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.02)) !important;
    border-radius:18px !important;
    overflow:hidden;
  }
  .pm-admin-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    padding:1.15rem 1.35rem;
  }
  .pm-title-wrap{display:flex;align-items:center;gap:.85rem;}
  .pm-title-icon{
    width:42px;height:42px;border-radius:14px;
    display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg, rgba(124,92,255,.28), rgba(31,230,198,.10));
    border:1px solid rgba(124,92,255,.30);
    color:#c6b8ff;
    box-shadow:0 14px 30px rgba(0,0,0,.20);
  }
  .pm-title-eyebrow{
    font-size:.68rem;
    letter-spacing:.10em;
    text-transform:uppercase;
    color:var(--pm-text-muted);
    font-weight:800;
    margin-bottom:.15rem;
  }
  .pm-count-pill{
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.38rem .72rem;border-radius:999px;
    background:rgba(124,92,255,.14);
    border:1px solid rgba(124,92,255,.28);
    color:#c8bcff;font-weight:800;font-size:.78rem;
    white-space:nowrap;
  }

  .pm-insurance-card{
    border:1px solid var(--pm-border);
    background:radial-gradient(circle at top right, rgba(124,92,255,.15), transparent 35%), var(--pm-bg);
    border-radius:18px;
    padding:1.2rem;
    margin-bottom:1.35rem;
  }
  .pm-insurance-top{
    display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;
    margin-bottom:1.1rem;
  }
  .pm-section-title{display:flex;align-items:center;gap:.65rem;font-weight:900;color:#fff;}
  .pm-section-title .pm-mini-icon{
    width:32px;height:32px;border-radius:10px;
    display:inline-flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#b8a9ff;
  }
  .pm-section-sub{color:var(--pm-text-muted);font-size:.875rem;margin-top:.25rem;}
  .pm-metric-row{display:grid;grid-template-columns:repeat(3,minmax(120px,1fr));gap:.75rem;min-width:min(100%,430px);}
  .pm-metric{
    padding:.8rem .9rem;border-radius:14px;
    background:rgba(0,0,0,.12);border:1px solid rgba(255,255,255,.08);
  }
  .pm-metric-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:var(--pm-text-muted);font-weight:800;}
  .pm-metric-value{font-size:1.05rem;font-weight:900;color:#fff;margin-top:.2rem;}
  .pm-metric-value.is-hold{color:var(--pm-yellow);}
  .pm-metric-value.is-withdrawable{color:var(--pm-green);}
  .pm-divider{border-color:rgba(255,255,255,.08);margin:1rem 0;}

  .pm-method-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-bottom:1.25rem;}
  .pm-choice,
  .pm-method-card{
    cursor:pointer;user-select:none;border:1px solid var(--pm-border);background:var(--pm-bg);
    border-radius:18px;transition:border-color .15s ease, background .15s ease, box-shadow .15s ease, transform .12s ease;
    overflow:hidden;
  }
  .pm-choice:hover,.pm-method-card:hover{border-color:rgba(255,255,255,.16);background:var(--pm-surface);transform:translateY(-1px);}
  .pm-choice.active,.pm-method-card.active{
    border-color:rgba(124,92,255,.55);
    background:radial-gradient(circle at top right, rgba(124,92,255,.20), transparent 38%), rgba(124,92,255,.055);
    box-shadow:0 0 0 3px rgba(124,92,255,.10), 0 18px 40px rgba(0,0,0,.18);
  }
  .pm-method-body{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.15rem;}
  .pm-method-main{display:flex;align-items:center;gap:.85rem;min-width:0;}
  .pm-choice .iconwrap,.pm-method-icon{
    width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:14px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#d5ccff;flex:0 0 auto;
  }
  .pm-method-icon.is-bank{color:#d6ccff;background:rgba(124,92,255,.12);border-color:rgba(124,92,255,.22);}
  .pm-method-icon.is-crypto{color:#ffe08a;background:rgba(248,199,106,.10);border-color:rgba(248,199,106,.22);}
  .pm-method-name{font-size:1rem;font-weight:900;color:#fff;}
  .pm-muted,.pm-method-sub{color:var(--pm-text-muted);font-size:.82rem;}
  .pm-method-state{
    padding:.28rem .58rem;border-radius:999px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
    color:rgba(255,255,255,.78);font-size:.74rem;font-weight:800;white-space:nowrap;
  }
  .pm-method-state.is-default{background:rgba(31,230,198,.12);border-color:rgba(31,230,198,.25);color:var(--pm-green);}

  .pm-form-panel{
    border:1px solid var(--pm-border);
    background:rgba(0,0,0,.10);
    border-radius:18px;
    padding:1.15rem;
  }
  .pm-form-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;}
  .pm-form-title{display:flex;align-items:center;gap:.6rem;font-weight:900;color:#fff;}
  .pm-note{
    border:1px solid rgba(124,92,255,.22);background:rgba(124,92,255,.08);color:rgba(220,215,255,.92);
    border-radius:14px;padding:.85rem 1rem;font-size:.875rem;
  }
  .pm-admin-card .form-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.62);font-weight:850;}
  .pm-admin-card .form-control{
    border-radius:12px !important;background:rgba(0,0,0,.18) !important;border-color:rgba(255,255,255,.08) !important;
    color:#fff !important;min-height:42px;
  }
  .pm-admin-card .form-control:focus{border-color:rgba(124,92,255,.55) !important;box-shadow:0 0 0 .2rem rgba(124,92,255,.13) !important;}
  .pm-admin-card .btn-primary{border-radius:12px;font-weight:800;background:linear-gradient(135deg,#6d5efc,#8b5cf6);border:0;}

  .pm-table-card{border:1px solid var(--pm-border);background:rgba(0,0,0,.10);border-radius:18px;overflow:hidden;margin-top:1.35rem;}
  .pm-table-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.15rem;border-bottom:1px solid rgba(255,255,255,.08);}
  .pm-table-title{display:flex;align-items:center;gap:.6rem;font-weight:900;color:#fff;}
  .pm-detail-list{display:flex;flex-wrap:wrap;gap:.35rem;}
  .pm-detail-chip{display:inline-flex;align-items:center;gap:.3rem;padding:.18rem .55rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.68);font-size:.74rem;white-space:nowrap;}
  .pm-table-card table thead th{font-size:.70rem;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.62);}
  .pm-table-card table tbody tr:hover{background:rgba(255,255,255,.025);}
  .pm-actions{display:flex;justify-content:flex-end;gap:.35rem;flex-wrap:wrap;}
  .pm-actions .btn{border-radius:10px;}

  @media (max-width: 991.98px){
    .pm-method-grid{grid-template-columns:1fr;}
    .pm-metric-row{grid-template-columns:1fr;min-width:100%;}
    .pm-admin-header{align-items:flex-start;}
  }
</style>

<div class="card card-bordered pm-admin-card">
  <div class="card-header p-0">
    <div class="pm-admin-header">
      <div class="pm-title-wrap">
        <div class="pm-title-icon"><i class="fa-solid fa-money-check-dollar"></i></div>
        <div>
          <div class="pm-title-eyebrow">Booster payout settings</div>
          <h4 class="card-header-title mb-0">Payout Methods</h4>
          <p class="text-muted mb-0 mt-1">Manage insurance, bank transfer and crypto payout details for this booster.</p>
        </div>
      </div>
      <span class="pm-count-pill">
        <i class="fa-solid fa-layer-group"></i>
        <?= is_array($methods) ? count($methods) : 0 ?> method<?= (is_array($methods) && count($methods) === 1) ? '' : 's' ?>
      </span>
    </div>
  </div>

  <div class="card-body">

    <?php
      // Insurance (flexible)
      $balanceCents = (int)($data['balance'] ?? 0);
      $paidCents = (int)($data['insurance_paid_amount'] ?? 0);
      // Backward compat: if old boolean insurance_paid exists and amount isn't set, assume default 25€
      if ($paidCents <= 0 && !empty($data['insurance_paid'])) $paidCents = 2500;

      $requiredOverride = isset($data['insurance_required_amount']) ? (int)$data['insurance_required_amount'] : 0;
      $defaultRequired = 2500; // 25€
      $requiredCents = ($requiredOverride > 0) ? $requiredOverride : $defaultRequired;

      $holdCents = max($requiredCents - $paidCents, 0);
      $withdrawableCents = max($balanceCents - $holdCents, 0);

      $fmtEur = function($cents){ return number_format(((int)$cents)/100, 2, '.', ''); };
    ?>
    <div class="pm-insurance-card">
      <div class="pm-insurance-top">
        <div>
          <div class="pm-section-title">
            <span class="pm-mini-icon"><i class="fa-solid fa-shield-check"></i></span>
            <span>Insurance</span>
          </div>
          <div class="pm-section-sub">Required insurance can be overridden per booster. Hold is calculated from required minus paid.</div>
        </div>
        <div class="pm-metric-row">
          <div class="pm-metric">
            <div class="pm-metric-label">Balance</div>
            <div class="pm-metric-value"><?= $fmtEur($balanceCents) ?> €</div>
          </div>
          <div class="pm-metric">
            <div class="pm-metric-label">Hold</div>
            <div class="pm-metric-value is-hold"><?= $fmtEur($holdCents) ?> €</div>
          </div>
          <div class="pm-metric">
            <div class="pm-metric-label">Withdrawable</div>
            <div class="pm-metric-value is-withdrawable"><?= $fmtEur($withdrawableCents) ?> €</div>
          </div>
        </div>
      </div>

      <hr class="pm-divider">

      <form action="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/insurance/save" method="post" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Required insurance (EUR)</label>
          <input class="form-control" name="insurance_required" value="<?= $requiredOverride > 0 ? $fmtEur($requiredOverride) : '' ?>" placeholder="default: <?= $fmtEur($defaultRequired) ?>">
          <div class="text-muted small mt-1">Leave empty to use default/policy.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Already paid (EUR)</label>
          <input class="form-control" name="insurance_paid" value="<?= $fmtEur($paidCents) ?>" placeholder="0.00">
          <div class="text-muted small mt-1">For legacy boosters, set to 25.00 etc.</div>
        </div>
        <div class="col-md-4 d-flex justify-content-md-end">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Insurance
          </button>
        </div>
      </form>
    </div>



    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-soft-success">Saved successfully.</div>
    <?php endif; ?>

    <!-- Method selector -->
    <div class="pm-method-grid">
      <div class="pm-choice pm-method-card <?= $active === 'bank' ? 'active' : '' ?>" data-pm="bank">
        <div class="pm-method-body">
          <div class="pm-method-main">
            <div class="pm-method-icon is-bank"><i class="fa-solid fa-building-columns fs-3"></i></div>
            <div>
              <div class="pm-method-name">Bank Transfer</div>
              <div class="pm-method-sub">IBAN / SWIFT / Beneficiary / Country</div>
            </div>
          </div>
          <div class="pm-method-state <?= ($bank && !empty($bank['is_default'])) ? 'is-default' : '' ?>">
            <?= $bank ? ($bank['is_default'] ? 'Default' : 'Saved') : 'Not set' ?>
          </div>
        </div>
      </div>

      <div class="pm-choice pm-method-card <?= $active === 'crypto' ? 'active' : '' ?>" data-pm="crypto">
        <div class="pm-method-body">
          <div class="pm-method-main">
            <div class="pm-method-icon is-crypto"><i class="fa-brands fa-bitcoin fs-3"></i></div>
            <div>
              <div class="pm-method-name">Crypto</div>
              <div class="pm-method-sub">Coin / Network / Wallet / Country</div>
            </div>
          </div>
          <div class="pm-method-state <?= ($crypto && !empty($crypto['is_default'])) ? 'is-default' : '' ?>">
            <?= $crypto ? ($crypto['is_default'] ? 'Default' : 'Saved') : 'Not set' ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Bank panel -->
    <div id="pm-panel-bank" class="pm-form-panel <?= $active === 'bank' ? '' : 'd-none' ?>">
      <div class="pm-form-head"><div class="pm-form-title"><i class="fa-solid fa-building-columns"></i> Bank Transfer Details</div><span class="pm-method-state <?= ($bank && !empty($bank['is_default'])) ? 'is-default' : '' ?>"><?= $bank ? ($bank['is_default'] ? 'Default' : 'Saved') : 'Not set' ?></span></div>
      <form action="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/payout-methods/save" method="post">
        <input type="hidden" name="method" value="bank">
        <input type="hidden" name="method_id" value="<?= (int)($bank['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-12">
            <div class="pm-note">
              <i class="fa-solid fa-circle-info me-1"></i>
              Beneficiary name must match the bank account holder exactly, character by character.
            </div>
            <input type="hidden" name="name_match_notice" value="The beneficiary name must match the bank account holder exactly.">
          </div>

          <div class="col-md-6">
            <label class="form-label">Beneficiary name</label>
            <input class="form-control" name="beneficiary" value="<?= $h($bankDetails['beneficiary'] ?? '') ?>" placeholder="John Doe">
          </div>

          <div class="col-md-6">
            <label class="form-label">Country</label>
            <input class="form-control" name="country" value="<?= $h($bankDetails['country'] ?? '') ?>" placeholder="Germany">
          </div>

          <div class="col-md-6">
            <label class="form-label">Currency</label>
            <input class="form-control" name="currency" value="<?= $h($bankDetails['currency'] ?? 'EUR') ?>" placeholder="EUR">
          </div>

          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input class="form-control" name="address" value="<?= $h($bankDetails['address'] ?? '') ?>" placeholder="Street, ZIP, City">
          </div>

          <div class="col-12">
            <label class="form-label">IBAN</label>
            <input class="form-control" name="iban" value="<?= $h($bankDetails['iban'] ?? '') ?>" placeholder="DE00 0000 0000 0000 0000 00">
          </div>

          <div class="col-md-6">
            <label class="form-label">SWIFT / BIC</label>
            <input class="form-control" name="swift" value="<?= $h($bankDetails['swift'] ?? ($bankDetails['bic'] ?? '')) ?>" placeholder="BANKDEFFXXX">
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
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Crypto panel -->
    <div id="pm-panel-crypto" class="pm-form-panel <?= $active === 'crypto' ? '' : 'd-none' ?>">
      <div class="pm-form-head"><div class="pm-form-title"><i class="fa-brands fa-bitcoin"></i> Crypto Details</div><span class="pm-method-state <?= ($crypto && !empty($crypto['is_default'])) ? 'is-default' : '' ?>"><?= $crypto ? ($crypto['is_default'] ? 'Default' : 'Saved') : 'Not set' ?></span></div>
      <form action="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/payout-methods/save" method="post">
        <input type="hidden" name="method" value="crypto">
        <input type="hidden" name="method_id" value="<?= (int)($crypto['id'] ?? 0) ?>">

        <div class="row g-3">
          <div class="col-12">
            <div class="pm-note">
              <i class="fa-solid fa-circle-info me-1"></i>
              The name must match the wallet or exchange account name exactly, otherwise payout may fail or be rejected.
            </div>
            <input type="hidden" name="name_match_notice" value="The payout name must match the wallet or exchange account name exactly.">
          </div>

          <div class="col-md-6">
            <label class="form-label">Coin</label>
            <input class="form-control" name="coin" value="<?= $h($cryptoDetails['coin'] ?? 'USDC') ?>" placeholder="USDC">
          </div>

          <div class="col-md-6">
            <label class="form-label">Network</label>
            <input class="form-control" name="network" value="<?= $h($cryptoDetails['network'] ?? 'Solana') ?>" placeholder="Solana">
          </div>

          <div class="col-md-6">
            <label class="form-label">Country</label>
            <input class="form-control" name="country" value="<?= $h($cryptoDetails['country'] ?? '') ?>" placeholder="Germany">
          </div>

          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?= $h($cryptoDetails['name'] ?? '') ?>" placeholder="Exact wallet / exchange name">
          </div>

          <div class="col-md-6">
            <label class="form-label">Wallet / Exchange</label>
            <input class="form-control" name="wallet" value="<?= $h($cryptoDetails['wallet'] ?? '') ?>" placeholder="e.g. Binance">
          </div>

          <div class="col-md-6">
            <label class="form-label">Wallet address</label>
            <input class="form-control" name="address" value="<?= $h($cryptoDetails['address'] ?? '') ?>" placeholder="0x...">
          </div>

          <div class="col-12 d-flex align-items-center justify-content-between pt-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="make_default" value="1" id="makeDefaultCrypto" <?= !empty($crypto['is_default']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="makeDefaultCrypto">Set as default payout method</label>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- All methods table (admin actions) -->
    <div class="pm-table-card">
      <div class="pm-table-head">
        <div>
          <div class="pm-table-title"><i class="fa-solid fa-table-list"></i> All saved methods</div>
          <div class="text-muted small mt-1">Saved payout methods, defaults and quick actions.</div>
        </div>
        <span class="pm-count-pill"><?= is_array($methods) ? count($methods) : 0 ?> total</span>
      </div>

      <?php if (empty($methods)): ?>
        <div class="alert alert-soft-secondary m-3">No payout methods found.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-borderless table-thead-bordered align-middle mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:80px;">ID</th>
              <th style="width:140px;">Type</th>
              <th>Details</th>
              <th style="width:110px;">Default</th>
              <th style="width:180px;">Created</th>
              <th class="text-end" style="width:220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($methods as $m): ?>
              <?php
                $id = (int)($m['id'] ?? 0);
                $type = ($normalizeMethod($m['method'] ?? '') === 'crypto') ? 'Crypto' : 'Bank Transfer';
                $d = $decodeDetails($m);
                $detailText = '-';

                $detailChips = [];
                if ($normalizeMethod($m['method'] ?? '') === 'crypto') {
                  $coin = $d['coin'] ?? '';
                  $net  = $d['network'] ?? '';
                  $addr = $d['address'] ?? '';
                  $country = $d['country'] ?? '';
                  $name = $d['name'] ?? '';
                  $wallet = $d['wallet'] ?? '';
                  $parts = [];
                  if ($coin) $parts[] = strtoupper((string)$coin);
                  if ($net) $parts[] = '(' . $net . ')';
                  if ($addr) $parts[] = $mask($addr);
                  $detailText = $parts ? implode(' ', $parts) : '-';
                  if ($country) $detailChips[] = 'Country: ' . $country;
                  if ($name) $detailChips[] = 'Name: ' . $name;
                  if ($wallet) $detailChips[] = 'Wallet: ' . $wallet;
                } else {
                  $ben = $d['beneficiary'] ?? '';
                  $iban = preg_replace('/\s+/', '', (string)($d['iban'] ?? ''));
                  $ibanMasked = $iban ? ('****' . substr($iban, -6)) : '';
                  $country = $d['country'] ?? '';
                  $currency = $d['currency'] ?? '';
                  $address = $d['address'] ?? '';
                  $parts = [];
                  if ($ben) $parts[] = $ben;
                  if ($ibanMasked) $parts[] = $ibanMasked;
                  $detailText = $parts ? implode(' • ', $parts) : '-';
                  if ($country) $detailChips[] = 'Country: ' . $country;
                  if ($currency) $detailChips[] = 'Currency: ' . $currency;
                  if ($address) $detailChips[] = 'Address: ' . $address;
                }

                $created = $m['created_at'] ?? $m['created'] ?? '-';
                $isDefault = !empty($m['is_default']);
              ?>
              <tr>
                <td class="text-muted">#<?= $id ?></td>
                <td><?= $h($type) ?></td>
                <td>
                  <div class="text-muted"><?= $h($detailText) ?></div>
                  <?php if (!empty($detailChips)): ?>
                    <div class="pm-detail-list mt-1">
                      <?php foreach ($detailChips as $chip): ?>
                        <span class="pm-detail-chip"><?= $h($chip) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($isDefault): ?>
                    <span class="badge bg-soft-success text-success">Yes</span>
                  <?php else: ?>
                    <span class="badge bg-soft-secondary text-secondary">No</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted"><?= $h($created) ?></td>
                <td class="text-end">
                  <div class="pm-actions">
                  <a class="btn btn-sm btn-white"
                     href="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/payout-methods?edit=<?= $id ?>&active=<?= ($normalizeMethod($m['method'] ?? '') === 'crypto') ? 'crypto' : 'bank' ?>">
                    Edit
                  </a>

                  <form class="d-inline" action="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/payout-methods/set-default" method="post">
                    <input type="hidden" name="method_id" value="<?= $id ?>">
                    <button class="btn btn-sm btn-white" type="submit">Set default</button>
                  </form>

                  <form class="d-inline" action="<?= ADMN_URL ?>/booster/<?= $boosterId ?>/payout-methods/delete" method="post"
                        onsubmit="return confirm('Delete this payout method?');">
                    <input type="hidden" name="method_id" value="<?= $id ?>">
                    <button class="btn btn-sm btn-soft-danger" type="submit">Delete</button>
                  </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
(function () {
  const cards = document.querySelectorAll('.pm-choice');
  const bankPanel = document.getElementById('pm-panel-bank');
  const cryptoPanel = document.getElementById('pm-panel-crypto');

  function setActive(which) {
    cards.forEach(c => c.classList.toggle('active', c.dataset.pm === which));
    if (bankPanel) bankPanel.classList.toggle('d-none', which !== 'bank');
    if (cryptoPanel) cryptoPanel.classList.toggle('d-none', which !== 'crypto');

    try {
      const url = new URL(window.location.href);
      url.searchParams.set('active', which);
      // keep edit param as-is
      window.history.replaceState({}, '', url.toString());
    } catch (e) {}
  }

  cards.forEach(card => {
    card.addEventListener('click', () => setActive(card.dataset.pm));
  });
})();
</script>
