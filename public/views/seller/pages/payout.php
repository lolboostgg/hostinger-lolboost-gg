<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Payout - Seller Area | LoLBoost.gg']]) ?>

<?php
require_once __DIR__ . '/_seller_rank.php';
    $seller_data = defined('SELLER_DATA') ? SELLER_DATA : [];

    $spageActiveTab = 'payout';
    include __DIR__ . '/_shared.php';

    $methods = $methods ?? ($data['methods'] ?? ($data['payout_methods'] ?? ($payout_methods ?? [])));

    $bank = null; $crypto = null;
    if (!empty($methods) && is_array($methods)) {
        foreach ($methods as $mm) {
            if (($mm['method'] ?? '') === 'bank_transfer' && $bank === null) $bank = $mm;
            if (($mm['method'] ?? '') === 'crypto'        && $crypto === null) $crypto = $mm;
        }
    }
    $bankDetails   = [];
    if (!empty($bank['details']))   { $d = json_decode($bank['details'],   true); if (is_array($d)) $bankDetails   = $d; }
    $cryptoDetails = [];
    if (!empty($crypto['details'])) { $d = json_decode($crypto['details'], true); if (is_array($d)) $cryptoDetails = $d; }

    $active = 'bank_transfer';
    if (isset($_GET['m']) && $_GET['m'] === 'crypto') $active = 'crypto';

    $balanceCents = (int)($seller_data['balance'] ?? 0);
    $balanceEur   = $balanceCents / 100;

    $bankIsDefault   = !empty($bank['is_default']);
    $cryptoIsDefault = !empty($crypto['is_default']);

    $iban       = preg_replace('/\s+/', '', (string)($bankDetails['iban'] ?? ''));
    $ibanMasked = $iban ? ('****' . substr($iban, -6)) : null;
    $beneficiary = $bankDetails['beneficiary'] ?? null;

    $addr       = (string)($cryptoDetails['address'] ?? '');
    $addrMasked = $addr ? (substr($addr, 0, 6) . '...' . substr($addr, -4)) : null;
?>

<style>
:root {
  --po-purple:  #6d5cff;
  --po-purple2: #b05cff;
  --po-green:   #4ade80;
  --po-amber:   #fbbf24;
  --po-red:     #fb7185;
  --po-text:    rgba(255,255,255,.94);
  --po-sub:     rgba(255,255,255,.62);
  --po-muted:   rgba(255,255,255,.42);
  --po-border:  rgba(255,255,255,.07);
  --po-bg:      rgba(255,255,255,.025);
  --po-card-bg: var(--bs-card-bg, #181b26);
}

/* ── Layout ── */
.po-page { color: var(--po-text); }
.po-page .card { background: var(--bs-card-bg) !important; border: var(--bs-card-border-width) solid var(--bs-card-border-color) !important; border-radius: 22px !important; box-shadow: none !important; }
.po-page .card::before { display: none !important; }

/* ── Section label ── */
.po-label { font-size:.72rem;font-weight:900;color:#9f8cff;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px; }

/* ── Balance card ── */
.po-balance-card { background: var(--bs-card-bg) !important; border-color: var(--bs-card-border-color) !important; }
.po-balance-amount { font-size:2.2rem;font-weight:950;line-height:1;color:#fff;letter-spacing:-.02em; }
.po-balance-label  { font-size:.72rem;font-weight:800;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px; }
.po-balance-sub    { font-size:.82rem;color:rgba(255,255,255,.45);margin-top:6px; }
.po-payout-btn {
  display:inline-flex;align-items:center;gap:.5rem;
  background:linear-gradient(135deg,var(--po-purple),var(--po-purple2));
  border:none;border-radius:12px;padding:.55rem 1.2rem;
  font-weight:900;font-size:.88rem;color:#fff;cursor:pointer;
  transition:opacity .15s,transform .12s;text-decoration:none;
}
.po-payout-btn:hover { opacity:.88;transform:translateY(-1px);color:#fff; }

/* ── Method selector ── */
.po-method-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
@media(max-width:576px){ .po-method-grid { grid-template-columns:1fr; } }

.po-method-tile {
  position:relative;border:1px solid var(--bs-card-border-color);border-radius:16px;
  padding:16px;cursor:pointer;background:rgba(255,255,255,.025);
  transition:border-color .15s,background .15s,transform .1s;
  text-align:left;width:100%;
}
.po-method-tile:hover { border-color:rgba(109,92,255,.4);background:rgba(109,92,255,.05);transform:translateY(-1px); }
.po-method-tile.po-active { border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.06);box-shadow:0 0 0 3px rgba(109,92,255,.10); }

.po-tile-icon {
  width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;margin-bottom:12px;
  background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));
  border:1px solid rgba(109,92,255,.22);color:#fff;
}
.po-tile-name  { font-size:.95rem;font-weight:900;color:var(--po-text);margin-bottom:2px; }
.po-tile-sub   { font-size:.78rem;color:var(--po-sub); }
.po-tile-badges{ display:flex;flex-wrap:wrap;gap:5px;margin-top:10px; }

.po-badge { display:inline-flex;align-items:center;gap:.3rem;padding:3px 8px;border-radius:99px;font-size:.69rem;font-weight:800;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.05);color:rgba(255,255,255,.75); }
.po-badge-saved   { background:rgba(74,222,128,.10);border-color:rgba(74,222,128,.22);color:var(--po-green); }
.po-badge-default { background:rgba(109,92,255,.15);border-color:rgba(109,92,255,.30);color:#a78fff; }
.po-badge-fee     { background:rgba(245,158,11,.10);border-color:rgba(245,158,11,.22);color:var(--po-amber); }
.po-badge-notset  { background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.10);color:rgba(255,255,255,.45); }

/* active indicator dot */
.po-method-tile.po-active::after {
  content:''; position:absolute;top:12px;right:12px;
  width:8px;height:8px;border-radius:50%;background:var(--po-purple);
  box-shadow:0 0 6px rgba(109,92,255,.7);
}

/* ── Form card ── */
.po-form-hd { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--bs-card-border-color); }
.po-form-title { font-size:1.05rem;font-weight:900;color:var(--po-text); }
.po-form-sub   { font-size:.8rem;color:var(--po-muted);margin-top:2px; }

.po-field-label { display:block;font-size:.72rem;font-weight:800;color:var(--po-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px; }
.po-field-label span { color:var(--po-red); }

.po-input {
  width:100%;background:rgba(255,255,255,.03) !important;
  border:1px solid rgba(255,255,255,.09) !important;
  border-radius:11px !important;color:var(--po-text) !important;
  padding:10px 14px !important;font-size:.88rem !important;
  transition:border-color .15s,box-shadow .15s !important;
}
.po-input:focus { border-color:rgba(109,92,255,.5) !important;box-shadow:0 0 0 3px rgba(109,92,255,.10) !important;outline:none !important; }
.po-input::placeholder { color:rgba(255,255,255,.2) !important; }
.po-input:disabled { opacity:.38;cursor:not-allowed; }
.po-input.po-invalid { border-color:rgba(239,68,68,.55) !important;box-shadow:0 0 0 3px rgba(239,68,68,.10) !important; }
.po-error { color:var(--po-red);font-size:.74rem;margin-top:4px; }

.po-save-btn {
  display:inline-flex;align-items:center;gap:.45rem;
  background:linear-gradient(135deg,var(--po-purple),var(--po-purple2));
  border:none;border-radius:12px;padding:.6rem 1.4rem;
  font-weight:900;font-size:.88rem;color:#fff;cursor:pointer;
  transition:opacity .15s,transform .12s;
}
.po-save-btn:hover { opacity:.88;transform:translateY(-1px); }
.po-save-btn:disabled { opacity:.5;transform:none;cursor:not-allowed; }

.po-ghost-btn { background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.7);font-size:.82rem;border-radius:10px;padding:6px 13px;cursor:pointer;transition:background .12s,color .12s; }
.po-ghost-btn:hover { background:rgba(255,255,255,.09);color:#fff; }
.po-danger-btn { background:rgba(251,113,133,.07);border:1px solid rgba(251,113,133,.20);color:var(--po-red);font-size:.82rem;border-radius:10px;padding:6px 13px;cursor:pointer;transition:background .12s; }
.po-danger-btn:hover { background:rgba(251,113,133,.14); }

/* ── Saved Methods ── */
.po-saved-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
@media(max-width:768px){ .po-saved-grid { grid-template-columns:1fr; } }

.po-saved-item {
  border:1px solid var(--bs-card-border-color);border-radius:16px;padding:16px;
  background:rgba(255,255,255,.025);display:flex;flex-direction:column;gap:10px;
}
.po-saved-item-hd { display:flex;align-items:flex-start;justify-content:space-between;gap:8px; }
.po-saved-icon { width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.12));border:1px solid rgba(109,92,255,.2);color:#fff; }
.po-saved-name { font-size:.9rem;font-weight:900;color:var(--po-text); }
.po-saved-detail { font-size:.78rem;color:var(--po-muted);margin-top:2px; }
.po-saved-actions { display:flex;gap:6px;flex-wrap:wrap;padding-top:8px;border-top:1px solid var(--bs-card-border-color); }

/* ── Modal ── */
.po-modal-overlay { position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.6);backdrop-filter:blur(8px);z-index:2000;padding:18px; }
.po-modal-overlay.is-open { display:flex; }
.po-modal-win { width:min(480px,100%);background:var(--bs-card-bg);border:var(--bs-card-border-width) solid var(--bs-card-border-color);border-radius:20px;overflow:hidden;box-shadow:0 32px 64px rgba(0,0,0,.6); }
.po-modal-hd  { display:flex;align-items:center;justify-content:space-between;padding:16px 20px 14px;border-bottom:var(--bs-card-border-width) solid var(--bs-card-border-color); }
.po-modal-title { font-weight:900;font-size:1rem;color:var(--po-text); }
.po-modal-sub   { font-size:.78rem;color:var(--po-muted);margin-top:2px; }
.po-modal-body  { padding:16px 20px;color:rgba(255,255,255,.78);font-size:.88rem;line-height:1.6; }
.po-modal-ft    { display:flex;justify-content:flex-end;gap:8px;padding:12px 20px 16px;border-top:var(--bs-card-border-width) solid var(--bs-card-border-color); }
.po-modal-x     { width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s; }
.po-modal-x:hover { background:rgba(255,255,255,.09); }
.po-modal-icon-wrap { width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0; }
</style>

<div class="po-page">

  <!-- ── Top row: Balance + quick action ── -->
  <div class="row g-3 mb-4">
    <div class="col-md-5">
      <div class="card po-balance-card h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="po-label mb-3">Available Balance</div>
          <div class="flex-grow-1 d-flex align-items-center">
            <div style="display:flex;align-items:center;gap:14px;">
              <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;">
                <i class="fa-solid fa-wallet"></i>
              </div>
              <div>
                <div class="po-balance-amount"><?= number_format($balanceEur, 2) ?> €</div>
                <div class="po-balance-sub" style="margin-top:2px;"><?= number_format($balanceEur, 2) ?> EUR ready for payout</div>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <a href="<?= BASE_URL ?>/seller-area/payout-requests" class="po-payout-btn" style="width:100%;justify-content:center;">
              <i class="fa-solid fa-arrow-right-long"></i> Payout Requests
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-body p-4">
          <div class="po-label mb-3">Payment Methods</div>

          <?php if (empty($bank['id']) && empty($crypto['id'])): ?>
          <!-- Empty state -->
          <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 0;gap:12px;text-align:center;">
            <div style="width:52px;height:52px;border-radius:16px;background:rgba(109,92,255,.10);border:1px solid rgba(109,92,255,.20);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#9f8cff;">
              <i class="fa-duotone fa-wallet"></i>
            </div>
            <div>
              <div style="font-weight:900;color:rgba(255,255,255,.88);font-size:.95rem;">No payment method added</div>
              <div style="font-size:.8rem;color:rgba(255,255,255,.45);margin-top:4px;">Add a bank account or crypto wallet to receive payouts.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-center mt-1">
              <button type="button" class="po-payout-btn po-method-tile-trigger" id="emptyBankBtn" data-method="bank_transfer" style="font-size:.82rem;padding:.45rem 1rem;">
                <i class="fa-solid fa-building-columns"></i> Bank Transfer
              </button>
              <button type="button" class="po-ghost-btn po-method-tile-trigger" id="emptyCryptoBtn" data-method="crypto" style="border-radius:12px;transition:background .15s,border-color .15s,color .15s;">
                <i class="fa-solid fa-coins me-1"></i> Crypto
              </button>
            </div>
          </div>
          <?php else: ?>
          <div class="po-method-grid">

            <!-- Bank Transfer -->
            <button type="button" class="po-method-tile <?= $active==='bank_transfer' ? 'po-active' : '' ?>" data-method="bank_transfer">
              <div class="po-tile-icon"><i class="fa-solid fa-building-columns"></i></div>
              <div class="po-tile-name">Bank Transfer</div>
              <div class="po-tile-sub">IBAN / SWIFT</div>
              <div class="po-tile-badges">
                <span class="po-badge po-badge-fee"><i class="fa-solid fa-percent"></i> 3% Fee</span>
                <?php if (!empty($bank['id'])): ?>
                  <span class="po-badge po-badge-saved"><i class="fa-solid fa-check"></i> Saved</span>
                  <?php if ($bankIsDefault): ?><span class="po-badge po-badge-default"><i class="fa-solid fa-star"></i> Default</span><?php endif; ?>
                <?php else: ?>
                  <span class="po-badge po-badge-notset">Not set</span>
                <?php endif; ?>
              </div>
            </button>

            <!-- Crypto -->
            <button type="button" class="po-method-tile <?= $active==='crypto' ? 'po-active' : '' ?>" data-method="crypto">
              <div class="po-tile-icon" style="background:linear-gradient(135deg,rgba(245,158,11,.25),rgba(251,191,36,.12));border-color:rgba(245,158,11,.25);"><i class="fa-solid fa-coins"></i></div>
              <div class="po-tile-name">Crypto</div>
              <div class="po-tile-sub">USDC · Solana</div>
              <div class="po-tile-badges">
                <span class="po-badge po-badge-fee"><i class="fa-solid fa-percent"></i> 5% Fee</span>
                <?php if (!empty($crypto['id'])): ?>
                  <span class="po-badge po-badge-saved"><i class="fa-solid fa-check"></i> Saved</span>
                  <?php if ($cryptoIsDefault): ?><span class="po-badge po-badge-default"><i class="fa-solid fa-star"></i> Default</span><?php endif; ?>
                <?php else: ?>
                  <span class="po-badge po-badge-notset">Not set</span>
                <?php endif; ?>
              </div>
            </button>

          </div>
          <?php endif; // end empty state check ?>
        </div>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-body p-4">

      <!-- BANK FORM -->
      <div id="bankFormWrapper" <?= $active !== 'bank_transfer' ? 'style="display:none;"' : '' ?>>
        <div class="po-form-hd">
          <div>
            <div class="po-form-title"><i class="fa-solid fa-building-columns me-2" style="color:var(--po-purple);"></i>Bank Transfer</div>
            <div class="po-form-sub">Enter your IBAN / SWIFT payout details.</div>
          </div>
          <?php if (!empty($bank['id'])): ?>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <?php if ($bankIsDefault): ?>
              <span class="po-badge po-badge-default"><i class="fa-solid fa-star me-1"></i>Default</span>
            <?php else: ?>
              <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Bank Transfer">
                <input type="hidden" name="action" value="seller_save_payout_method">
                <input type="hidden" name="method" value="bank_transfer">
                <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$bank['id'] ?>">
                <input type="hidden" name="beneficiary" value="<?= esc($bankDetails['beneficiary'] ?? '') ?>">
                <input type="hidden" name="iban" value="<?= esc($bankDetails['iban'] ?? '') ?>">
                <input type="hidden" name="bic" value="<?= esc($bankDetails['bic'] ?? '') ?>">
                <input type="hidden" name="bank_name" value="<?= esc($bankDetails['bank_name'] ?? '') ?>">
                <input type="hidden" name="is_default" value="1">
                <button class="po-ghost-btn" type="submit"><i class="fa-solid fa-star me-1"></i>Set default</button>
              </form>
            <?php endif; ?>
            <button class="po-danger-btn" type="button" data-delete-method="<?= (int)$bank['id'] ?>">
              <i class="fa-solid fa-trash me-1"></i>Remove
            </button>
          </div>
          <?php endif; ?>
        </div>

        <form id="bankSaveForm" method="POST" action="<?= AJAX_URL ?>" novalidate>
          <input type="hidden" name="action" value="seller_save_payout_method">
          <input type="hidden" name="method" value="bank_transfer">
          <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
          <?php if (!empty($bank['id'])): ?><input type="hidden" name="id" value="<?= (int)$bank['id'] ?>"><?php endif; ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="po-field-label">Beneficiary name <span>*</span></label>
              <input class="form-control po-input" name="beneficiary" required value="<?= esc($bankDetails['beneficiary'] ?? '') ?>" placeholder="Your full name or company name">
            </div>
            <div class="col-12">
              <label class="po-field-label">IBAN / Account number <span>*</span></label>
              <input class="form-control po-input" name="iban" required value="<?= esc($bankDetails['iban'] ?? '') ?>" placeholder="DE00 0000 0000 0000 0000 00">
            </div>
            <div class="col-md-6">
              <label class="po-field-label">SWIFT / BIC</label>
              <input class="form-control po-input" name="bic" value="<?= esc($bankDetails['bic'] ?? '') ?>" placeholder="BANKDEFFXXX">
            </div>
            <div class="col-md-6">
              <label class="po-field-label">Bank name</label>
              <input class="form-control po-input" name="bank_name" value="<?= esc($bankDetails['bank_name'] ?? '') ?>" placeholder="e.g. Postbank">
            </div>
            <div class="col-12 d-flex justify-content-end pt-1">
              <button type="submit" id="bankSaveBtn" class="po-save-btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

      <!-- CRYPTO FORM -->
      <div id="cryptoFormWrapper" <?= $active !== 'crypto' ? 'style="display:none;"' : '' ?>>
        <div class="po-form-hd">
          <div>
            <div class="po-form-title"><i class="fa-solid fa-coins me-2" style="color:var(--po-amber);"></i>Crypto</div>
            <div class="po-form-sub">USDC on Solana — fixed network.</div>
          </div>
          <?php if (!empty($crypto['id'])): ?>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <?php if ($cryptoIsDefault): ?>
              <span class="po-badge po-badge-default"><i class="fa-solid fa-star me-1"></i>Default</span>
            <?php else: ?>
              <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Crypto (USDC · Solana)">
                <input type="hidden" name="action" value="seller_save_payout_method">
                <input type="hidden" name="method" value="crypto">
                <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$crypto['id'] ?>">
                <input type="hidden" name="coin" value="USDC">
                <input type="hidden" name="network" value="Solana">
                <input type="hidden" name="name" value="<?= esc($cryptoDetails['name'] ?? '') ?>">
                <input type="hidden" name="wallet" value="<?= esc($cryptoDetails['wallet'] ?? '') ?>">
                <input type="hidden" name="address" value="<?= esc($cryptoDetails['address'] ?? '') ?>">
                <input type="hidden" name="is_default" value="1">
                <button class="po-ghost-btn" type="submit"><i class="fa-solid fa-star me-1"></i>Set default</button>
              </form>
            <?php endif; ?>
            <button class="po-danger-btn" type="button" data-delete-method="<?= (int)$crypto['id'] ?>">
              <i class="fa-solid fa-trash me-1"></i>Remove
            </button>
          </div>
          <?php endif; ?>
        </div>

        <form id="cryptoSaveForm" method="POST" action="<?= AJAX_URL ?>" novalidate>
          <input type="hidden" name="action" value="seller_save_payout_method">
          <input type="hidden" name="method" value="crypto">
          <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
          <?php if (!empty($crypto['id'])): ?><input type="hidden" name="id" value="<?= (int)$crypto['id'] ?>"><?php endif; ?>
          <input type="hidden" name="coin" value="USDC">
          <input type="hidden" name="network" value="Solana">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="po-field-label">Coin</label>
              <input class="form-control po-input" value="USDC" disabled>
            </div>
            <div class="col-md-6">
              <label class="po-field-label">Network</label>
              <input class="form-control po-input" value="Solana" disabled>
            </div>
            <div class="col-md-6">
              <label class="po-field-label">Name <span>*</span></label>
              <input class="form-control po-input" name="name" required value="<?= esc($cryptoDetails['name'] ?? '') ?>" placeholder="Your name">
            </div>
            <div class="col-md-6">
              <label class="po-field-label">Wallet / Exchange <span>*</span></label>
              <input class="form-control po-input" name="wallet" required value="<?= esc($cryptoDetails['wallet'] ?? '') ?>" placeholder="e.g. Binance">
            </div>
            <div class="col-12">
              <label class="po-field-label">Wallet address <span>*</span></label>
              <input class="form-control po-input" name="address" required value="<?= esc($cryptoDetails['address'] ?? '') ?>" placeholder="3fneiyNMAEWAtUbwmcBuBwS7Brp49dXTfZyTj16xBNnv">
            </div>
            <div class="col-12 d-flex justify-content-end pt-1">
              <button type="submit" id="cryptoSaveBtn" class="po-save-btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>

  <!-- ── Saved Methods ── -->
  <?php if (!empty($bank['id']) || !empty($crypto['id'])): ?>
  <div class="card mb-4">
    <div class="card-body p-4">
      <div class="po-label mb-3">Saved Methods</div>
      <div class="po-saved-grid">

        <?php if (!empty($bank['id'])): ?>
        <div class="po-saved-item">
          <div class="po-saved-item-hd">
            <div class="d-flex align-items-center gap-3">
              <div class="po-saved-icon"><i class="fa-solid fa-building-columns"></i></div>
              <div>
                <div class="po-saved-name">Bank Transfer</div>
                <?php if ($beneficiary): ?><div class="po-saved-detail"><?= esc($beneficiary) ?></div><?php endif; ?>
                <?php if ($ibanMasked):  ?><div class="po-saved-detail" style="font-family:monospace;"><?= esc($ibanMasked) ?></div><?php endif; ?>
              </div>
            </div>
            <div class="d-flex flex-column gap-1 align-items-end">
              <?php if ($bankIsDefault): ?><span class="po-badge po-badge-default"><i class="fa-solid fa-star"></i> Default</span><?php endif; ?>
              <span class="po-badge po-badge-saved"><i class="fa-solid fa-check"></i> Saved</span>
            </div>
          </div>
          <div class="po-saved-actions">
            <button type="button" class="po-ghost-btn" data-edit-method="bank_transfer"><i class="fa-solid fa-pen me-1"></i>Edit</button>
            <?php if (!$bankIsDefault): ?>
              <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Bank Transfer">
                <input type="hidden" name="action" value="seller_save_payout_method">
                <input type="hidden" name="method" value="bank_transfer">
                <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$bank['id'] ?>">
                <input type="hidden" name="beneficiary" value="<?= esc($bankDetails['beneficiary'] ?? '') ?>">
                <input type="hidden" name="iban" value="<?= esc($bankDetails['iban'] ?? '') ?>">
                <input type="hidden" name="bic" value="<?= esc($bankDetails['bic'] ?? '') ?>">
                <input type="hidden" name="bank_name" value="<?= esc($bankDetails['bank_name'] ?? '') ?>">
                <input type="hidden" name="is_default" value="1">
                <button type="submit" class="po-ghost-btn"><i class="fa-solid fa-star me-1"></i>Set default</button>
              </form>
            <?php endif; ?>
            <button type="button" class="po-danger-btn ms-auto" data-delete-method="<?= (int)$bank['id'] ?>"><i class="fa-solid fa-trash me-1"></i>Remove</button>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($crypto['id'])): ?>
        <div class="po-saved-item">
          <div class="po-saved-item-hd">
            <div class="d-flex align-items-center gap-3">
              <div class="po-saved-icon" style="background:linear-gradient(135deg,rgba(245,158,11,.22),rgba(251,191,36,.12));border-color:rgba(245,158,11,.22);"><i class="fa-solid fa-coins"></i></div>
              <div>
                <div class="po-saved-name">Crypto <span style="font-size:.75rem;color:var(--po-muted);font-weight:600;">USDC · Solana</span></div>
                <?php if (!empty($cryptoDetails['name'])): ?><div class="po-saved-detail"><?= esc($cryptoDetails['name']) ?> · <?= esc($cryptoDetails['wallet'] ?? '') ?></div><?php endif; ?>
                <?php if ($addrMasked): ?><div class="po-saved-detail" style="font-family:monospace;"><?= esc($addrMasked) ?></div><?php endif; ?>
              </div>
            </div>
            <div class="d-flex flex-column gap-1 align-items-end">
              <?php if ($cryptoIsDefault): ?><span class="po-badge po-badge-default"><i class="fa-solid fa-star"></i> Default</span><?php endif; ?>
              <span class="po-badge po-badge-saved"><i class="fa-solid fa-check"></i> Saved</span>
            </div>
          </div>
          <div class="po-saved-actions">
            <button type="button" class="po-ghost-btn" data-edit-method="crypto"><i class="fa-solid fa-pen me-1"></i>Edit</button>
            <?php if (!$cryptoIsDefault): ?>
              <form class="d-inline pm-default-form" method="POST" action="<?= AJAX_URL ?>" data-pretty="Crypto (USDC · Solana)">
                <input type="hidden" name="action" value="seller_save_payout_method">
                <input type="hidden" name="method" value="crypto">
                <input type="hidden" name="redirectUrl" value="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$crypto['id'] ?>">
                <input type="hidden" name="coin" value="USDC">
                <input type="hidden" name="network" value="Solana">
                <input type="hidden" name="name" value="<?= esc($cryptoDetails['name'] ?? '') ?>">
                <input type="hidden" name="wallet" value="<?= esc($cryptoDetails['wallet'] ?? '') ?>">
                <input type="hidden" name="address" value="<?= esc($cryptoDetails['address'] ?? '') ?>">
                <input type="hidden" name="is_default" value="1">
                <button type="submit" class="po-ghost-btn"><i class="fa-solid fa-star me-1"></i>Set default</button>
              </form>
            <?php endif; ?>
            <button type="button" class="po-danger-btn ms-auto" data-delete-method="<?= (int)$crypto['id'] ?>"><i class="fa-solid fa-trash me-1"></i>Remove</button>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Action Modal -->
<div class="po-modal-overlay" id="pmActionOverlay" aria-hidden="true">
  <div class="po-modal-win" role="dialog" aria-modal="true" aria-labelledby="pmActionTitle">
    <div class="po-modal-hd">
      <div class="d-flex align-items-center">
        <div class="po-modal-icon-wrap" id="pmActionIcon"><i class="fa-solid fa-circle-question"></i></div>
        <div>
          <div class="po-modal-title" id="pmActionTitle">Confirm</div>
          <div class="po-modal-sub" id="pmActionSubtitle"></div>
        </div>
      </div>
      <button type="button" class="po-modal-x" id="pmActionCloseBtn"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="po-modal-body" id="pmActionBody"></div>
    <div class="po-modal-ft">
      <button type="button" class="po-ghost-btn" id="pmActionCancelBtn">Cancel</button>
      <button type="button" class="po-save-btn" id="pmActionConfirmBtn">Confirm</button>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  const tiles  = document.querySelectorAll('.po-method-tile');
  const bankW  = document.getElementById('bankFormWrapper');
  const cryptoW= document.getElementById('cryptoFormWrapper');

  function setActive(method) {
    tiles.forEach(t => t.classList.toggle('po-active', t.dataset.method === method));
    if (bankW)   bankW.style.display   = method === 'bank_transfer' ? '' : 'none';
    if (cryptoW) cryptoW.style.display = method === 'crypto'        ? '' : 'none';
    try {
      const url = new URL(window.location.href);
      method === 'crypto' ? url.searchParams.set('m','crypto') : url.searchParams.delete('m');
      window.history.replaceState({}, '', url.toString());
    } catch(e) {}
  }

  tiles.forEach(t => t.addEventListener('click', () => setActive(t.dataset.method)));

  // Empty state trigger buttons
  const emptyBankBtn   = document.getElementById('emptyBankBtn');
  const emptyCryptoBtn = document.getElementById('emptyCryptoBtn');

  function setEmptyActiveStyle(method) {
    if (!emptyBankBtn || !emptyCryptoBtn) return;
    if (method === 'bank_transfer') {
      emptyBankBtn.style.cssText   = 'font-size:.82rem;padding:.45rem 1rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;opacity:1;';
      emptyCryptoBtn.style.cssText = 'border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.7);';
    } else {
      emptyCryptoBtn.style.cssText = 'border-radius:12px;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;color:#fff;padding:6px 13px;font-weight:900;';
      emptyBankBtn.style.cssText   = 'font-size:.82rem;padding:.45rem 1rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.7);border-radius:12px;';
    }
  }

  document.querySelectorAll('.po-method-tile-trigger').forEach(b => {
    b.addEventListener('click', () => {
      setActive(b.dataset.method);
      setEmptyActiveStyle(b.dataset.method);
      const f = document.querySelector('.po-form-hd');
      if (f) window.scrollTo({ top: f.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
    });
  });

  document.querySelectorAll('[data-edit-method]').forEach(b => {
    b.addEventListener('click', () => {
      setActive(b.getAttribute('data-edit-method'));
      const f = document.querySelector('.card .po-form-hd');
      if (f) window.scrollTo({ top: f.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
    });
  });

  // Modal
  const overlay    = document.getElementById('pmActionOverlay');
  const titleEl    = document.getElementById('pmActionTitle');
  const subEl      = document.getElementById('pmActionSubtitle');
  const bodyEl     = document.getElementById('pmActionBody');
  const iconEl     = document.getElementById('pmActionIcon');
  const confirmBtn = document.getElementById('pmActionConfirmBtn');
  const cancelBtn  = document.getElementById('pmActionCancelBtn');
  const closeBtn   = document.getElementById('pmActionCloseBtn');

  function closeModal() { overlay?.classList.remove('is-open'); overlay?.setAttribute('aria-hidden','true'); }
  function openModal()  { overlay?.classList.add('is-open');    overlay?.setAttribute('aria-hidden','false'); }

  cancelBtn?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click',  closeModal);
  overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

  let pendingAction = null;

  function openActionModal({title, subtitle, body, icon, confirmText, confirmClass, onConfirm}) {
    if (!overlay) return;
    titleEl.textContent    = title || 'Confirm';
    subEl.textContent      = subtitle || '';
    bodyEl.innerHTML       = body || '';
    iconEl.innerHTML       = icon || '<i class="fa-solid fa-circle-question"></i>';
    confirmBtn.textContent = confirmText || 'Confirm';
    confirmBtn.className   = confirmClass === 'btn-danger' ? 'po-danger-btn' : 'po-save-btn';
    pendingAction = onConfirm;
    confirmBtn.disabled = false;
    openModal();
  }

  confirmBtn?.addEventListener('click', async () => {
    if (!pendingAction) return;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
    try { await pendingAction(); }
    finally { confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm'; }
  });

  async function postAjax(fd) {
    const res  = await fetch('<?= AJAX_URL ?>', { method:'POST', body:fd, credentials:'same-origin' });
    const text = await res.text();
    let json = {};
    try { json = JSON.parse(text); }
    catch(e) { json = text.trim() === '1' ? { refreshPage:true } : { raw:text }; }
    return { res, json };
  }

  function emitToast(p) {
    if (!p) return;
    try {
      if (typeof window.sendToast === 'function') return window.sendToast(p);
      window.dispatchEvent(new CustomEvent('sendToast',      { detail:p }));
      window.dispatchEvent(new CustomEvent('lolboost:toast', { detail:p }));
    } catch(e) {}
  }

  async function handleAjaxJson(json) {
    if (!json) return;
    if (json.sendToast)   emitToast(json.sendToast);
    if (json.redirectUrl) { window.location.href = json.redirectUrl; return; }
    if (json.refreshPage) { window.location.reload(); return; }
  }

  document.querySelectorAll('[data-delete-method]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-delete-method');
      if (!id) return;
      openActionModal({
        title:'Remove payout method', subtitle:'This action cannot be undone.',
        body:'<div style="color:rgba(255,255,255,.65);">Are you sure you want to remove this payout method?</div>',
        icon:'<i class="fa-solid fa-trash"></i>',
        confirmText:'Remove', confirmClass:'btn-danger',
        onConfirm: async () => {
          const fd = new FormData();
          fd.append('action','seller_delete_payout_method');
          fd.append('id', id);
          fd.append('redirectUrl','<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>');
          const { json } = await postAjax(fd);
          closeModal();
          await handleAjaxJson(json);
        }
      });
    });
  });

  document.querySelectorAll('form.pm-default-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const pretty = form.getAttribute('data-pretty') || 'payout method';
      openActionModal({
        title:'Set default payout method', subtitle:pretty,
        body:'<div style="color:rgba(255,255,255,.65);">Set <strong style="color:#fff;">' + pretty + '</strong> as your default payout method?</div>',
        icon:'<i class="fa-solid fa-star"></i>',
        confirmText:'Set default', confirmClass:'btn-primary',
        onConfirm: async () => {
          const { json } = await postAjax(new FormData(form));
          closeModal();
          await handleAjaxJson(json);
        }
      });
    });
  });

  function saveForm(formId, btnId) {
    const form = document.getElementById(formId);
    const btn  = document.getElementById(btnId);
    if (!form || !btn) return;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      form.querySelectorAll('.po-error').forEach(n => n.remove());
      form.querySelectorAll('.po-invalid').forEach(i => i.classList.remove('po-invalid'));
      let first = null;
      form.querySelectorAll('[required]:not([disabled])').forEach(input => {
        if (!(input.value || '').trim()) {
          input.classList.add('po-invalid');
          const msg = document.createElement('div');
          msg.className = 'po-error'; msg.textContent = 'Please fill out this field.';
          input.insertAdjacentElement('afterend', msg);
          if (!first) first = input;
        }
      });
      if (first) { first.scrollIntoView({ behavior:'smooth', block:'center' }); return; }
      const orig = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
      try {
        const { json } = await postAjax(new FormData(form));
        await handleAjaxJson(json);
      } catch(err) {
        emitToast({ type:'danger', title:'Error', message:'Something went wrong.' });
      } finally {
        btn.disabled = false; btn.innerHTML = orig;
      }
    });
  }

  saveForm('bankSaveForm',   'bankSaveBtn');
  saveForm('cryptoSaveForm', 'cryptoSaveBtn');
})();
</script>
<?= $this->end() ?>
