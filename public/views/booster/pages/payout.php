<?php
// Booster Payout Settings — Seller-style layout
?>
<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Payout - Booster Area | LoLBoost.gg'], 'contain' => false]) ?>

<?php
$methods = $methods ?? ($data['methods'] ?? ($data['payout_methods'] ?? ($payout_methods ?? [])));

$bank = null; $crypto = null;
if (!empty($methods) && is_array($methods)) {
  foreach ($methods as $mm) {
    if (($mm['method'] ?? '') === 'bank_transfer' && $bank === null) $bank = $mm;
    if (($mm['method'] ?? '') === 'crypto'        && $crypto === null) $crypto = $mm;
  }
}
$bankDetails = [];
if (!empty($bank['details']))   { $d = json_decode($bank['details'],   true); if (is_array($d)) $bankDetails   = $d; }
$cryptoDetails = [];
if (!empty($crypto['details'])) { $d = json_decode($crypto['details'], true); if (is_array($d)) $cryptoDetails = $d; }

$active = 'bank_transfer';
if (isset($_GET['m']) && $_GET['m'] === 'crypto') $active = 'crypto';

$balanceCents         = (int)(BOOSTER_DATA['balance'] ?? 0);
$insuranceFrozenCents = booster_insurance_frozen_cents();
$availableCents       = booster_available_for_payout_cents();
$balanceEur           = $balanceCents / 100;
$insuranceEur         = $insuranceFrozenCents / 100;
$availableEur         = $availableCents / 100;
?>

<?php require_once __DIR__ . '/_shared.php'; lb_render_booster_area_profile_header('payout'); ?>

<style>
  /* ── Seller-style two-column top row ── */
  .po-top-row { display: grid; grid-template-columns: 1fr 1.6fr; gap: 16px; margin-bottom: 20px; }
  @media (max-width: 768px) { .po-top-row { grid-template-columns: 1fr; } }

  /* Balance card */
  .po-balance-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 22px 22px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  .po-balance-label {
    font-size: 10.5px; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; color: rgba(255,255,255,.38);
  }
  .po-balance-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(109,94,252,.15); border: 1px solid rgba(109,94,252,.25);
    color: #a78bfa; font-size: 18px;
  }
  .po-balance-val { font-size: 32px; font-weight: 800; letter-spacing: -.8px; color: rgba(255,255,255,.95); line-height: 1; }
  .po-balance-sub { font-size: 12.5px; color: rgba(255,255,255,.42); }
  .po-balance-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 10px; border-radius: 10px;
    background: linear-gradient(135deg, #6d5efc, #8b5cf6);
    border: none; color: #fff; font-size: 14px; font-weight: 600;
    text-decoration: none; transition: opacity .15s; cursor: pointer;
  }
  .po-balance-btn:hover { opacity: .88; color: #fff; }

  /* Methods card */
  .po-methods-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 22px 22px 20px;
  }
  .po-methods-label {
    font-size: 10.5px; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; color: rgba(255,255,255,.38);
    margin-bottom: 14px;
  }
  .po-methods-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 480px) { .po-methods-grid { grid-template-columns: 1fr; } }

  .po-method-tile {
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
    border-radius: 13px;
    padding: 14px 16px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    text-align: left; width: 100%;
    color: rgba(255,255,255,.88);
  }
  .po-method-tile:hover { background: rgba(255,255,255,.065); }
  .po-method-tile.active {
    border-color: rgba(109,94,252,.6);
    background: rgba(109,94,252,.08);
    box-shadow: 0 0 0 3px rgba(109,94,252,.12);
    position: relative;
  }
  .po-method-tile.active::after {
    content: '';
    position: absolute; top: 10px; right: 10px;
    width: 8px; height: 8px; border-radius: 50%;
    background: #a78bfa;
  }
  .po-tile-icon {
    width: 42px; height: 42px; border-radius: 11px; margin-bottom: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; border: 1px solid rgba(255,255,255,.08);
  }
  .po-tile-icon.bank   { background: rgba(109,94,252,.15); color: #a78bfa; }
  .po-tile-icon.crypto { background: rgba(245,158,11,.12); color: #fcd34d; }
  .po-tile-name  { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
  .po-tile-sub   { font-size: 12px; color: rgba(255,255,255,.42); margin-bottom: 8px; }
  .po-tile-pills { display: flex; flex-wrap: wrap; gap: 5px; }

  .po-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 600;
  }
  .po-chip-fee     { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.22); color: #fcd34d; }
  .po-chip-saved   { background: rgba(16,185,129,.1);  border: 1px solid rgba(16,185,129,.22); color: #6ee7b7; }
  .po-chip-default { background: rgba(109,94,252,.12); border: 1px solid rgba(109,94,252,.25); color: #c4b5fd; }
  .po-chip-notset  { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.4); }

  /* ── Form section ── */
  .po-form-section {
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 16px;
  }
  .po-form-head {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 10px; margin-bottom: 20px;
  }
  .po-form-title { font-size: 16px; font-weight: 700; color: rgba(255,255,255,.92); margin: 0 0 3px; }
  .po-form-sub   { font-size: 12.5px; color: rgba(255,255,255,.42); margin: 0; }

  .po-label {
    display: block; font-size: 11.5px; font-weight: 700;
    letter-spacing: .05em; text-transform: uppercase;
    color: rgba(255,255,255,.45); margin-bottom: 7px;
  }
  .po-req { color: rgba(239,68,68,.8); margin-left: 2px; }

  /* Use existing form-control but style labels uppercase like seller */
  .payout-settings-page .form-label {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    letter-spacing: .05em !important;
    text-transform: uppercase !important;
    color: rgba(255,255,255,.45) !important;
  }
  .payout-settings-page input.form-control,
  .payout-settings-page select.form-select {
    background: rgba(255,255,255,.04) !important;
    border-color: rgba(255,255,255,.1) !important;
    color: rgba(255,255,255,.88) !important;
    border-radius: 9px !important;
  }
  .payout-settings-page input.form-control:focus,
  .payout-settings-page select.form-select:focus {
    border-color: rgba(109,94,252,.45) !important;
    box-shadow: 0 0 0 3px rgba(109,94,252,.1) !important;
  }
  .payout-settings-page input.form-control::placeholder { color: rgba(255,255,255,.25) !important; }
  .payout-settings-page input.form-control:disabled { opacity: .4 !important; }
  .payout-settings-page { color: rgba(255,255,255,.88); }

  .pm-invalid { border-color: rgba(239,68,68,.55)!important; box-shadow: 0 0 0 3px rgba(239,68,68,.1)!important; }
  .pm-error-text { color: rgba(252,165,165,1); font-size: 12px; margin-top: 5px; }

  /* Action buttons */
  .po-btn-save {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 20px; border-radius: 9px; border: none;
    background: linear-gradient(135deg, #6d5efc, #8b5cf6);
    color: #fff; font-size: 14px; font-weight: 600; cursor: pointer;
    transition: opacity .15s;
  }
  .po-btn-save:hover { opacity: .88; }

  .po-btn-sm {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 7px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; border: 1px solid transparent; transition: background .12s;
  }
  .po-btn-default { background: rgba(109,94,252,.1); border-color: rgba(109,94,252,.25); color: #a78bfa; }
  .po-btn-default:hover { background: rgba(109,94,252,.16); }
  .po-btn-danger  { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.2); color: #fca5a5; }
  .po-btn-danger:hover  { background: rgba(239,68,68,.14); }
  .po-btn-ghost {
    background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.7);
  }
  .po-btn-ghost:hover { background: rgba(255,255,255,.07); }



  .po-info-note {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 11px 13px; margin-bottom: 16px;
    border-radius: 12px;
    background: rgba(109,94,252,.08);
    border: 1px solid rgba(109,94,252,.18);
    color: rgba(255,255,255,.68);
    font-size: 12.5px;
    line-height: 1.45;
  }
  .po-info-note i { color: #a78bfa; margin-top: 2px; }

  /* Modal */
  .pm-modal-overlay { position:fixed;inset:0;display:none;align-items:center;justify-content:center;
    background:rgba(0,0,0,.6);backdrop-filter:blur(6px);z-index:2000;padding:18px; }
  .pm-modal-overlay.is-open { display:flex; }
  .pm-modal-window { width:min(520px,100%);background:rgba(32,34,40,.98);border:1px solid rgba(255,255,255,.1);
    box-shadow:0 20px 60px rgba(0,0,0,.6);border-radius:18px;overflow:hidden; }
  .pm-modal-header { display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:16px 20px 14px;border-bottom:1px solid rgba(255,255,255,.07); }
  .pm-modal-title  { font-size:16px;font-weight:700;color:rgba(255,255,255,.92); }
  .pm-modal-body   { padding:16px 20px;font-size:14px;color:rgba(255,255,255,.62); }
  .pm-modal-footer { display:flex;justify-content:flex-end;gap:10px;
    padding:14px 20px 18px;border-top:1px solid rgba(255,255,255,.07); }
  .pm-modal-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;
    justify-content:center;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7); }
  .pm-modal-close { width:36px;height:36px;border-radius:9px;border:1px solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);display:flex;align-items:center;
    justify-content:center;cursor:pointer; }
  .pm-modal-close:hover { background:rgba(255,255,255,.08); }
</style>

<div class="payout-settings-page">

  <!-- ── Top row: Balance + Methods ── -->
  <div class="po-top-row">

    <!-- Balance card -->
    <div class="po-balance-card">
      <div class="po-balance-label">Available for Payout</div>
      <div class="d-flex align-items-center gap-3">
        <div class="po-balance-icon"><i class="fa-solid fa-wallet"></i></div>
        <div>
          <div class="po-balance-val"
               data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom"
               title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
            <?= number_format($availableEur, 2) ?> €
          </div>
          <div class="po-balance-sub"><?= number_format($availableEur, 2) ?> EUR ready for payout</div>
        </div>
      </div>
      <?php if ($insuranceEur > 0): ?>
        <div class="po-balance-sub">
          <i class="fa-solid fa-shield-check me-1"></i>Insurance: <?= number_format($insuranceEur, 2) ?> EUR
        </div>
        <div class="po-balance-sub" style="margin-top:4px;">Held as security and paid out when you leave the company.</div>
      <?php endif; ?>
      <a class="po-balance-btn" href="/booster-area/payout-requests">
        <i class="fa-solid fa-arrow-right-long"></i> Payout Requests
      </a>
    </div>

    <!-- Payment methods card -->
    <div class="po-methods-card">
      <div class="po-methods-label">Payment Methods</div>
      <div class="po-methods-grid">

        <!-- Bank Transfer tile -->
        <button type="button" class="po-method-tile <?= $active === 'bank_transfer' ? 'active' : '' ?>"
                data-method="bank_transfer">
          <div class="po-tile-icon bank"><i class="fa-solid fa-building-columns"></i></div>
          <div class="po-tile-name">Bank Transfer</div>
          <div class="po-tile-sub">IBAN / SWIFT</div>
          <div class="po-tile-pills">
            <span class="po-chip po-chip-fee"><i class="fa-solid fa-percent" style="font-size:9px;"></i> 3% Fee</span>
            <?php if (!empty($bank['id'])): ?>
              <span class="po-chip po-chip-saved"><i class="fa-solid fa-check" style="font-size:9px;"></i> Saved</span>
              <?php if (!empty($bank['is_default'])): ?>
                <span class="po-chip po-chip-default"><i class="fa-solid fa-star" style="font-size:9px;"></i> Default</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </button>

        <!-- Crypto tile -->
        <button type="button" class="po-method-tile <?= $active === 'crypto' ? 'active' : '' ?>"
                data-method="crypto">
          <div class="po-tile-icon crypto"><i class="fa-solid fa-coins"></i></div>
          <div class="po-tile-name">Crypto</div>
          <div class="po-tile-sub">USDC · Solana</div>
          <div class="po-tile-pills">
            <span class="po-chip po-chip-fee"><i class="fa-solid fa-percent" style="font-size:9px;"></i> 5% Fee</span>
            <?php if (!empty($crypto['id'])): ?>
              <span class="po-chip po-chip-saved"><i class="fa-solid fa-check" style="font-size:9px;"></i> Saved</span>
              <?php if (!empty($crypto['is_default'])): ?>
                <span class="po-chip po-chip-default"><i class="fa-solid fa-star" style="font-size:9px;"></i> Default</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </button>

      </div>
    </div>
  </div>

  <!-- ── Bank form ── -->
  <div class="po-form-section" id="bankFormWrapper" style="<?= $active === 'bank_transfer' ? '' : 'display:none;' ?>">
    <div class="po-form-head">
      <div>
        <p class="po-form-title"><i class="fa-solid fa-building-columns me-2" style="color:#a78bfa;"></i>Bank Transfer</p>
        <p class="po-form-sub">Enter your IBAN / SWIFT payout details.</p>
      </div>
      <?php if (!empty($bank['id'])): ?>
      <div class="d-flex gap-2 flex-wrap">
        <?php if (empty($bank['is_default'])): ?>
          <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Bank Transfer">
            <input type="hidden" name="action"      value="booster_save_payout_method">
            <input type="hidden" name="method"      value="bank_transfer">
            <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
            <input type="hidden" name="id"          value="<?= (int)$bank['id'] ?>">
            <input type="hidden" name="beneficiary" value="<?= esc($bankDetails['beneficiary'] ?? '') ?>">
            <input type="hidden" name="iban"        value="<?= esc($bankDetails['iban'] ?? '') ?>">
            <input type="hidden" name="bic"         value="<?= esc($bankDetails['bic'] ?? '') ?>">
            <input type="hidden" name="bank_name"   value="<?= esc($bankDetails['bank_name'] ?? '') ?>">
            <input type="hidden" name="country"     value="<?= esc($bankDetails['country'] ?? '') ?>">
            <input type="hidden" name="currency"    value="<?= esc($bankDetails['currency'] ?? 'EUR') ?>">
            <input type="hidden" name="address"     value="<?= esc($bankDetails['address'] ?? '') ?>">
            <input type="hidden" name="is_default"  value="1">
            <button class="po-btn-sm po-btn-default" type="submit">
              <i class="fa-solid fa-star" style="font-size:11px;"></i> Set default
            </button>
          </form>
        <?php else: ?>
          <span class="po-chip po-chip-default"><i class="fa-solid fa-star" style="font-size:10px;"></i> Default</span>
        <?php endif; ?>
        <button class="po-btn-sm po-btn-danger" type="button" data-delete-method="<?= (int)$bank['id'] ?>">
          <i class="fa-solid fa-trash" style="font-size:11px;"></i> Remove
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="po-info-note">
      <i class="fa-solid fa-circle-info"></i>
      <div>The beneficiary name must match the account holder name exactly. Incorrect or shortened names can delay or block payouts.</div>
    </div>

    <form class="ajax-form" method="POST" action="<?= AJAX_URL ?>" novalidate>
      <input type="hidden" name="action"      value="booster_save_payout_method">
      <input type="hidden" name="method"      value="bank_transfer">
      <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
      <?php if (!empty($bank['id'])): ?><input type="hidden" name="id" value="<?= (int)$bank['id'] ?>"><?php endif; ?>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
          <input class="form-control" name="beneficiary" required
                 value="<?= esc($bankDetails['beneficiary'] ?? '') ?>"
                 placeholder="Your full name / company name">
        </div>
        <div class="col-12">
          <label class="form-label">IBAN / Account Number <span class="text-danger">*</span></label>
          <input class="form-control" name="iban" required
                 value="<?= esc($bankDetails['iban'] ?? '') ?>"
                 placeholder="DE00 0000 0000 0000 0000 00">
        </div>
        <div class="col-md-6">
          <label class="form-label">Swift / BIC</label>
          <input class="form-control" name="bic"
                 value="<?= esc($bankDetails['bic'] ?? '') ?>"
                 placeholder="BANKDEFFXXX">
        </div>
        <div class="col-md-6">
          <label class="form-label">Bank Name</label>
          <input class="form-control" name="bank_name"
                 value="<?= esc($bankDetails['bank_name'] ?? '') ?>"
                 placeholder="e.g. Postbank">
        </div>
        <div class="col-md-4">
          <label class="form-label">Country <span class="text-danger">*</span></label>
          <input class="form-control" name="country" required
                 value="<?= esc($bankDetails['country'] ?? '') ?>"
                 placeholder="e.g. Germany">
        </div>
        <div class="col-md-4">
          <label class="form-label">Currency <span class="text-danger">*</span></label>
          <input class="form-control" name="currency" required
                 value="<?= esc($bankDetails['currency'] ?? 'EUR') ?>"
                 placeholder="EUR">
        </div>
        <div class="col-md-4">
          <label class="form-label">Address <span class="text-danger">*</span></label>
          <input class="form-control" name="address" required
                 value="<?= esc($bankDetails['address'] ?? '') ?>"
                 placeholder="Street, ZIP, City">
        </div>
        <div class="col-12 d-flex justify-content-end pt-1">
          <button type="submit" class="po-btn-save">
            <i class="fa-solid fa-floppy-disk"></i> Save
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- ── Crypto form ── -->
  <div class="po-form-section" id="cryptoFormWrapper" style="<?= $active === 'crypto' ? '' : 'display:none;' ?>">
    <div class="po-form-head">
      <div>
        <p class="po-form-title"><i class="fa-solid fa-coins me-2" style="color:#fcd34d;"></i>Crypto</p>
        <p class="po-form-sub">USDC on Solana (fixed network).</p>
      </div>
      <?php if (!empty($crypto['id'])): ?>
      <div class="d-flex gap-2 flex-wrap">
        <?php if (empty($crypto['is_default'])): ?>
          <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Crypto (USDC · Solana)">
            <input type="hidden" name="action"      value="booster_save_payout_method">
            <input type="hidden" name="method"      value="crypto">
            <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
            <input type="hidden" name="id"          value="<?= (int)$crypto['id'] ?>">
            <input type="hidden" name="coin"        value="USDC">
            <input type="hidden" name="network"     value="Solana">
            <input type="hidden" name="name"        value="<?= esc($cryptoDetails['name'] ?? '') ?>">
            <input type="hidden" name="wallet"      value="<?= esc($cryptoDetails['wallet'] ?? '') ?>">
            <input type="hidden" name="address"     value="<?= esc($cryptoDetails['address'] ?? '') ?>">
            <input type="hidden" name="country"     value="<?= esc($cryptoDetails['country'] ?? '') ?>">
            <input type="hidden" name="is_default"  value="1">
            <button class="po-btn-sm po-btn-default" type="submit">
              <i class="fa-solid fa-star" style="font-size:11px;"></i> Set default
            </button>
          </form>
        <?php else: ?>
          <span class="po-chip po-chip-default"><i class="fa-solid fa-star" style="font-size:10px;"></i> Default</span>
        <?php endif; ?>
        <button class="po-btn-sm po-btn-danger" type="button" data-delete-method="<?= (int)$crypto['id'] ?>">
          <i class="fa-solid fa-trash" style="font-size:11px;"></i> Remove
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="po-info-note">
      <i class="fa-solid fa-circle-info"></i>
      <div>The name must match the name on your wallet or exchange account exactly. Incorrect or shortened names can delay or block payouts.</div>
    </div>

    <form class="ajax-form" method="POST" action="<?= AJAX_URL ?>" novalidate>
      <input type="hidden" name="action"      value="booster_save_payout_method">
      <input type="hidden" name="method"      value="crypto">
      <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
      <?php if (!empty($crypto['id'])): ?><input type="hidden" name="id" value="<?= (int)$crypto['id'] ?>"><?php endif; ?>
      <input type="hidden" name="coin"    value="USDC">
      <input type="hidden" name="network" value="Solana">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Coin</label>
          <input class="form-control" value="USDC" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label">Network</label>
          <input class="form-control" value="Solana" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input class="form-control" name="name" required
                 value="<?= esc($cryptoDetails['name'] ?? '') ?>"
                 placeholder="Your name">
        </div>
        <div class="col-md-6">
          <label class="form-label">Wallet / Exchange <span class="text-danger">*</span></label>
          <input class="form-control" name="wallet" required
                 value="<?= esc($cryptoDetails['wallet'] ?? '') ?>"
                 placeholder="e.g. Binance">
        </div>
        <div class="col-12">
          <label class="form-label">Country <span class="text-danger">*</span></label>
          <input class="form-control" name="country" required
                 value="<?= esc($cryptoDetails['country'] ?? '') ?>"
                 placeholder="e.g. Germany">
        </div>
        <div class="col-12">
          <label class="form-label">Wallet Address <span class="text-danger">*</span></label>
          <input class="form-control" name="address" required
                 value="<?= esc($cryptoDetails['address'] ?? '') ?>"
                 placeholder="3fneiyNMAEWAtUbwmcBuBwS7Brp49dXTfZyTj16xBNnv"
                 style="font-family:monospace;font-size:13px;">
        </div>
        <div class="col-12 d-flex justify-content-end pt-1">
          <button type="submit" class="po-btn-save">
            <i class="fa-solid fa-floppy-disk"></i> Save
          </button>
        </div>
      </div>
    </form>
  </div>

</div><!-- /payout-settings-page -->


<!-- Action Modal -->
<div class="pm-modal-overlay" id="pmActionOverlay" aria-hidden="true">
  <div class="pm-modal-window" role="dialog" aria-modal="true">
    <div class="pm-modal-header">
      <div class="d-flex align-items-center gap-3">
        <div class="pm-modal-icon" id="pmActionIcon"></div>
        <div>
          <div class="pm-modal-title" id="pmActionTitle">Confirm</div>
          <div style="font-size:12.5px;color:rgba(255,255,255,.42);" id="pmActionSubtitle"></div>
        </div>
      </div>
      <button type="button" class="pm-modal-close" id="pmActionCloseBtn">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="pm-modal-body" id="pmActionBody"></div>
    <div class="pm-modal-footer">
      <button type="button" class="btn btn-outline-secondary" id="pmActionCancelBtn">Cancel</button>
      <button type="button" class="btn btn-primary" id="pmActionConfirmBtn">Confirm</button>
    </div>
  </div>
</div>


<script>
(function () {
  // ── Tab switching ──
  const tileBtns = document.querySelectorAll('.po-method-tile');
  const bankW    = document.getElementById('bankFormWrapper');
  const cryptoW  = document.getElementById('cryptoFormWrapper');

  function setActive(method) {
    tileBtns.forEach(b => b.classList.toggle('active', b.dataset.method === method));
    if (bankW)   bankW.style.display   = method === 'bank_transfer' ? '' : 'none';
    if (cryptoW) cryptoW.style.display = method === 'crypto'        ? '' : 'none';
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('m', method === 'crypto' ? 'crypto' : 'bank');
      history.replaceState({}, '', url.toString());
    } catch (e) {}
  }

  tileBtns.forEach(b => b.addEventListener('click', () => setActive(b.dataset.method)));

  // ── Validation ──
  document.querySelectorAll('form.ajax-form[novalidate]').forEach(form => {
    form.addEventListener('submit', e => {
      form.querySelectorAll('.pm-error-text').forEach(n => n.remove());
      form.querySelectorAll('.pm-invalid').forEach(i => i.classList.remove('pm-invalid'));
      let firstInvalid = null;
      form.querySelectorAll('[required]').forEach(input => {
        if (!(input.value || '').trim()) {
          input.classList.add('pm-invalid');
          const msg = document.createElement('div');
          msg.className = 'pm-error-text';
          msg.textContent = 'Please fill out this field.';
          input.insertAdjacentElement('afterend', msg);
          if (!firstInvalid) firstInvalid = input;
        }
      });
      if (firstInvalid) {
        e.preventDefault(); e.stopImmediatePropagation();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
      }
    }, true);
  });

  // ── Modal ──
  const overlay    = document.getElementById('pmActionOverlay');
  const titleEl    = document.getElementById('pmActionTitle');
  const subEl      = document.getElementById('pmActionSubtitle');
  const bodyEl     = document.getElementById('pmActionBody');
  const iconEl     = document.getElementById('pmActionIcon');
  const confirmBtn = document.getElementById('pmActionConfirmBtn');
  const cancelBtn  = document.getElementById('pmActionCancelBtn');
  const closeBtn   = document.getElementById('pmActionCloseBtn');
  let pending = null;

  function openModal()  { overlay.classList.add('is-open');    overlay.setAttribute('aria-hidden','false'); }
  function closeModal() { overlay.classList.remove('is-open'); overlay.setAttribute('aria-hidden','true'); }

  cancelBtn?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click',  closeModal);
  overlay?.addEventListener('click',   e => { if (e.target === overlay) closeModal(); });

  function showModal({ title, subtitle, body, icon, confirmText, confirmClass, onConfirm }) {
    if (!overlay) return;
    titleEl.textContent    = title || 'Confirm';
    subEl.textContent      = subtitle || '';
    bodyEl.innerHTML       = body || '';
    iconEl.innerHTML       = icon || '<i class="fa-solid fa-circle-question"></i>';
    confirmBtn.textContent = confirmText || 'Confirm';
    confirmBtn.className   = 'btn ' + (confirmClass || 'btn-primary');
    confirmBtn.disabled    = false;
    pending = onConfirm;
    openModal();
  }

  confirmBtn?.addEventListener('click', async () => {
    if (!pending) return;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
    try { await pending(); }
    finally { confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm'; }
  });

  async function postAjax(fd) {
    const res  = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
    const json = await res.json().catch(() => ({}));
    return { res, json };
  }

  function emitToast(p) {
    if (!p) return;
    if (typeof window.sendToast === 'function') return window.sendToast(p);
    window.dispatchEvent(new CustomEvent('lolboost:toast', { detail: p }));
  }

  async function handleJson(json) {
    if (!json) return;
    if (json.sendToast)   emitToast(json.sendToast);
    if (json.redirectUrl) { window.location.href = json.redirectUrl; return; }
    if (json.refreshPage) { window.location.reload(); }
  }

  document.querySelectorAll('[data-delete-method]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-delete-method');
      showModal({
        title: 'Remove payout method', subtitle: 'This cannot be undone.',
        body:  '<div style="color:rgba(255,255,255,.55);">Are you sure you want to remove this payout method?</div>',
        icon:  '<i class="fa-solid fa-trash"></i>',
        confirmText: 'Remove', confirmClass: 'btn-danger',
        onConfirm: async () => {
          const fd = new FormData();
          fd.append('action',      'booster_delete_payout_method');
          fd.append('id',          id);
          fd.append('redirectUrl', '<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>');
          const { json } = await postAjax(fd);
          closeModal();
          await handleJson(json);
        }
      });
    });
  });

  document.querySelectorAll('form.pm-default-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const pretty = form.getAttribute('data-pretty') || 'payout method';
      showModal({
        title: 'Set default payout method', subtitle: pretty,
        body:  '<div style="color:rgba(255,255,255,.55);">Set <strong style="color:rgba(255,255,255,.8);">' + pretty + '</strong> as your default?</div>',
        icon:  '<i class="fa-solid fa-star"></i>',
        confirmText: 'Set default', confirmClass: 'btn-primary',
        onConfirm: async () => {
          const fd = new FormData(form);
          const { json } = await postAjax(fd);
          closeModal();
          await handleJson(json);
        }
      });
    });
  });
})();
</script>
