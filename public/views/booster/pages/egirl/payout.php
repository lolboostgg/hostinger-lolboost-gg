<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>
<?php $egSharedActiveTab = 'overview'; include __DIR__ . '/_shared.php'; ?>

<style>
:root {
    --eg-purple: #a855f7;
    --eg-pink: #ec4899;
}
.eg-balance-strip { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;padding:1.4rem 1.5rem;border-bottom:1px solid var(--eg-border);background:linear-gradient(120deg,rgba(168,85,247,.10),rgba(236,72,153,.07)); }
.eg-balance-icon { width:52px;height:52px;border-radius:14px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;background:linear-gradient(135deg,var(--eg-purple),var(--eg-pink));box-shadow:0 10px 26px rgba(168,85,247,.30); }
.eg-balance-num { font-size:1.9rem;font-weight:900;background:linear-gradient(135deg,#6ee7b7,#22c55e);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent; }
.eg-balance-lbl { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--eg-muted);margin-bottom:.2rem; }
.eg-btn-rp { display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.3rem;border-radius:10px;font-weight:800;font-size:.88rem;background:linear-gradient(135deg,var(--eg-purple),var(--eg-pink));border:none;color:#fff;cursor:pointer;transition:transform .15s,opacity .15s;box-shadow:0 10px 26px rgba(236,72,153,.22); }
.eg-btn-rp:hover { opacity:.92;transform:translateY(-1px); }
/* Method cards */
.pm-choice { cursor:pointer;user-select:none;border:1px solid var(--eg-border);background:var(--eg-bg);border-radius:14px;transition:border-color .15s,background .15s; }
.pm-choice:hover { border-color:rgba(168,85,247,.35); }
.pm-choice.active { border-color:var(--eg-purple)!important;background:rgba(168,85,247,.1)!important;box-shadow:0 0 0 .2rem rgba(168,85,247,.18); }
.pm-icon-wrap { width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.18);font-size:1.2rem;color:#c084fc; }
.pm-icon-wrap.crypto { background:rgba(245,202,153,.1);border-color:rgba(245,202,153,.2);color:#f5ca99; }
.pm-saved-badge { font-size:.72rem;font-weight:700;padding:.18rem .55rem;border-radius:20px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.28);color:#4ade80; }
/* Seller pr-status */
.pr-status { display:inline-flex;align-items:center;gap:.45rem;padding:.3rem .7rem;border-radius:999px;font-weight:700;font-size:.72rem;letter-spacing:.03em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);white-space:nowrap; }
.pr-status::before { content:"";width:7px;height:7px;border-radius:999px;background:rgba(255,255,255,.45);flex-shrink:0; }
.pr-status.is-pending  { border-color:rgba(255,180,0,.28);background:rgba(255,180,0,.12);color:rgba(255,220,140,.95); }
.pr-status.is-pending::before  { background:rgba(255,180,0,.95);box-shadow:0 0 0 3px rgba(255,180,0,.18); }
.pr-status.is-approved { border-color:rgba(0,200,140,.28);background:rgba(0,200,140,.12);color:rgba(160,255,220,.95); }
.pr-status.is-approved::before { background:rgba(0,200,140,.95);box-shadow:0 0 0 3px rgba(0,200,140,.18); }
.pr-status.is-paid     { border-color:rgba(34,197,94,.28);background:rgba(34,197,94,.12);color:#4ade80; }
.pr-status.is-paid::before     { background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.18); }
.pr-status.is-rejected { border-color:rgba(255,70,120,.28);background:rgba(255,70,120,.12);color:rgba(255,170,190,.95); }
.pr-status.is-rejected::before { background:rgba(255,70,120,.95);box-shadow:0 0 0 3px rgba(255,70,120,.18); }
/* filter bar */
.filter-bar { display:flex;align-items:center;gap:.4rem;padding:.75rem 1.3125rem;border-bottom:1px solid var(--eg-border);background:rgba(168,85,247,.03);flex-wrap:wrap; }
.filter-bar label { font-size:.75rem;color:var(--eg-muted);margin:0 .2rem 0 0; }
.fpill { display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .75rem;border-radius:50rem;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--eg-border);background:transparent;color:var(--eg-muted);transition:all .15s; }
.fpill:hover { color:var(--eg-text);border-color:rgba(168,85,247,.35); }
.fpill.active { color:#fff;background:var(--eg-purple);border-color:var(--eg-purple); }
.fpill.fpill-pend.active { color:rgba(255,220,140,.95);background:rgba(255,180,0,.18);border-color:rgba(255,180,0,.4); }
.fpill.fpill-appr.active { color:rgba(160,255,220,.95);background:rgba(0,200,140,.18);border-color:rgba(0,200,140,.4); }
.fpill.fpill-rej.active  { color:rgba(255,170,190,.95);background:rgba(255,70,120,.18);border-color:rgba(255,70,120,.4); }
.eg-payout-table th { font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--eg-muted);font-weight:700;border-bottom:1px solid var(--eg-border)!important; }
.eg-payout-table td { border-bottom:1px solid rgba(168,85,247,.06)!important;vertical-align:middle; }
.eg-payout-table tbody tr:hover td { background:rgba(168,85,247,.04); }
.pr-money { font-variant-numeric:tabular-nums;font-family:ui-monospace,monospace; }
/* Modal method buttons */
#payoutReqModal .fpill { padding:.5rem 1.1rem;font-size:.85rem; }
#payoutReqModal .fpill.active { box-shadow:0 6px 18px rgba(168,85,247,.25); }

/* ══ Rich Request Payout modal (booster-style, GG-Girl colours) ══ */
.eg-payout-dialog{ max-width: 860px; }
.eg-payout-modal{
  background: linear-gradient(180deg, rgba(38,40,46,.98), rgba(31,33,39,.98)) !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  border-radius: 22px !important;
  box-shadow: 0 28px 90px rgba(0,0,0,.62) !important;
  overflow: visible;
}
.eg-payout-modal__header, .eg-payout-modal__footer{ border-color: rgba(255,255,255,.08) !important; }
.eg-payout-modal__header{ padding: 1.15rem 1.35rem; }
.eg-payout-modal__body{ padding: 1.35rem; }
.eg-payout-modal__footer{ padding: 1rem 1.35rem; }
.eg-payout-modal__icon{
  width: 42px; height: 42px; border-radius: 14px;
  display: inline-flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, rgba(168,85,247,.22), rgba(236,72,153,.18)); border: 1px solid rgba(236,72,153,.28); color: #f0abfc;
}
.eg-payout-modal__subtitle{ color: rgba(255,255,255,.50); font-size: .82rem; margin-top: .15rem; }
.eg-payout-info{
  display: flex; gap: .9rem; align-items: flex-start;
  padding: 1rem; border-radius: 16px;
  background: linear-gradient(120deg, rgba(168,85,247,.10), rgba(236,72,153,.07)); border: 1px solid rgba(168,85,247,.18);
}
.eg-payout-info__icon{
  width: 36px; height: 36px; border-radius: 12px;
  display: inline-flex; align-items: center; justify-content: center;
  background: rgba(168,85,247,.16); color: #c084fc; flex: 0 0 auto;
}
.eg-payout-info__title{ font-weight: 750; color: rgba(255,255,255,.92); margin-bottom: .2rem; }
.eg-payout-info__text{ color: rgba(255,255,255,.72); font-size: .9rem; }
.eg-payout-info__fees{ color: rgba(255,255,255,.46); font-size: .8rem; margin-top: .2rem; }
.eg-payout-grid{ display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 767.98px){ .eg-payout-grid{ grid-template-columns: 1fr; } }
.eg-payout-section{
  background: rgba(255,255,255,.035); border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px; padding: 1rem;
}
.eg-payout-section__head{
  display:flex; align-items:center; justify-content:space-between; gap: .75rem;
  margin-bottom: .9rem; color: rgba(255,255,255,.90); font-weight: 700;
}
.eg-payout-mini{ color: rgba(255,255,255,.45); font-size: .76rem; font-weight: 600; }
.eg-money-input{
  display:grid; grid-template-columns: 46px 1fr 64px; align-items:center;
  border: 1px solid rgba(255,255,255,.10); border-radius: 12px;
  background: rgba(0,0,0,.18); overflow:hidden;
}
.eg-money-input span{
  height: 46px; display:flex; align-items:center; justify-content:center;
  color: rgba(255,255,255,.70); background: rgba(255,255,255,.035);
}
.eg-money-input input{
  height: 46px; border:0; outline:0; background: transparent; color:#fff; padding: 0 .9rem; font-weight: 700;
}
.eg-money-input:focus-within{ border-color: rgba(236,72,153,.55); box-shadow: 0 0 0 3px rgba(236,72,153,.12); }
.eg-check-row{ display:flex; gap:.7rem; align-items:flex-start; cursor:pointer; color: rgba(255,255,255,.82); }
.eg-check-row small{ display:block; color: rgba(255,255,255,.45); margin-top:.1rem; }
.eg-method-select{ position: relative; }
.eg-method-select__button{
  width:100%; border:1px solid rgba(255,255,255,.10); background: rgba(0,0,0,.18);
  color:#fff; border-radius: 12px; padding:.72rem .8rem;
  display:flex; align-items:center; justify-content:space-between; gap:.8rem; text-align:left;
}
.eg-method-select__selected{ display:flex; align-items:center; gap:.75rem; min-width:0; }
.eg-method-select__selected strong{ display:block; font-weight:750; }
.eg-method-select__selected small, .eg-method-option small{ display:block; color:rgba(255,255,255,.48); margin-top:.05rem; }
.eg-method-icon{
  width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center;
  background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:#c4b5fd; flex:0 0 auto;
}
.eg-method-icon.is-crypto{ color:#fcd34d; background:rgba(245,158,11,.12); border-color:rgba(245,158,11,.20); }
.eg-method-icon.is-bank{ color:#c084fc; background:rgba(168,85,247,.12); border-color:rgba(168,85,247,.22); }
.eg-method-chevron{ color: rgba(255,255,255,.42); transition: transform .15s ease; }
.eg-method-select.is-open .eg-method-chevron{ transform: rotate(180deg); }
.eg-method-select__menu{
  position:absolute; z-index:1058; left:0; right:0; top:calc(100% + 8px);
  display:none; padding:.45rem; border-radius:16px;
  background: rgba(29,31,37,.98); border:1px solid rgba(255,255,255,.10);
  box-shadow:0 18px 55px rgba(0,0,0,.55); max-height:280px; overflow:auto;
}
.eg-method-select.is-open .eg-method-select__menu{ display:block; }
.eg-method-option{
  width:100%; border:0; background:transparent; color:#fff; border-radius:12px; padding:.7rem;
  display:flex; align-items:center; gap:.75rem; text-align:left;
}
.eg-method-option:hover, .eg-method-option.is-active{ background: rgba(168,85,247,.14); }
.eg-method-option strong{ display:block; font-weight: 750; }
.eg-fee-chip{
  border-radius:999px; padding:.22rem .48rem; font-size:.68rem; font-weight:750;
  color:#fcd34d; background:rgba(245,158,11,.10); border:1px solid rgba(245,158,11,.18); white-space:nowrap;
}
.eg-note-input{ border-radius:12px !important; background:rgba(0,0,0,.18) !important; border-color:rgba(255,255,255,.10) !important; }
.eg-payout-summary{ border-radius: 18px; overflow:hidden; border:1px solid rgba(255,255,255,.08); background: rgba(0,0,0,.14); }
.eg-payout-summary__row{
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  padding:.78rem 1rem; border-bottom:1px solid rgba(255,255,255,.06); color:rgba(255,255,255,.62);
}
.eg-payout-summary__row:last-child{ border-bottom:0; }
.eg-payout-summary__row strong{ color:rgba(255,255,255,.88); font-variant-numeric: tabular-nums; }
.eg-payout-summary__row.is-total{ background: rgba(236,72,153,.10); color:rgba(255,255,255,.90); font-weight:750; }
.eg-payout-summary__row.is-total strong{ color:#f9a8d4; font-size:1.05rem; }
.eg-submit-btn{ border-radius:12px; padding:.65rem 1.05rem;background:linear-gradient(135deg,var(--eg-purple),var(--eg-pink));border:none;color:#fff;font-weight:700; }
.eg-submit-btn:disabled{ opacity:.5; }
</style>

<?php
$requests      = $requests ?? [];
$balance_cents = $balance_cents ?? (int)(BOOSTER_DATA['balance'] ?? 0);
global $db;
$savedMethods = [];
if ($db) {
    $rows = $db->run("SELECT * FROM booster_payout_methods WHERE booster_id = ? ORDER BY is_default DESC, id DESC", BOOSTER_ID) ?? [];
    foreach ($rows as $r) {
        $mk = str_contains(strtolower($r['method']??''), 'crypto') ? 'crypto' : 'bank';
        if (!isset($savedMethods[$mk])) $savedMethods[$mk] = $r;
    }
}
$bank   = $savedMethods['bank']   ?? null;
$crypto = $savedMethods['crypto'] ?? null;
$bankD   = $bank   ? (json_decode($bank['details']   ?? '{}', true) ?: []) : [];
$cryptoD = $crypto ? (json_decode($crypto['details'] ?? '{}', true) ?: []) : [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$activeTab = isset($_GET['active']) && $_GET['active']==='crypto' ? 'crypto' : 'bank';
$pendingTotal = array_sum(array_map(fn($r)=>strtoupper($r['status']??'')==='PENDING'?(int)($r['amount_cents']??0):0, $requests));

// Fee rules (must match backend ajax.php egirl_request_payout)
$feeBank = 3.0;   // Bank Transfer: 3%
$feeCrypto = 5.0; // Crypto: 5%

// Build a flat list of saved methods for the modal's method selector
$methods = [];
if ($bank)   { $methods[] = array_merge($bank,   ['__details' => $bankD,   '__is_crypto' => false]); }
if ($crypto) { $methods[] = array_merge($crypto, ['__details' => $cryptoD, '__is_crypto' => true]); }
?>

<div class="card mb-3">
    <div class="eg-balance-strip">
        <div class="d-flex align-items-center gap-3">
            <div class="eg-balance-icon"><i class="fa-duotone fa-sack-dollar"></i></div>
            <div>
                <div class="eg-balance-lbl">Available for Payout</div>
                <div class="eg-balance-num">€<?= number_format($balance_cents/100,2) ?></div>
                <?php if($pendingTotal>0): ?><div style="font-size:.75rem;color:rgba(255,180,0,.8);margin-top:.2rem"><i class="fa-solid fa-clock me-1"></i>€<?= number_format($pendingTotal/100,2) ?> pending</div><?php endif; ?>
            </div>
        </div>
        <button class="eg-btn-rp" data-bs-toggle="modal" data-bs-target="#payoutReqModal">
            <i class="fa-duotone fa-money-check-dollar"></i>Request Payout
        </button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-credit-card me-2" style="color:var(--eg-purple)"></i>Payout Methods</h4>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card pm-choice <?= $activeTab==='bank' ? 'active':'' ?>" data-pm="bank" onclick="switchPm('bank')">
                    <div class="card-body d-flex align-items-center justify-content-between" style="padding:.9rem 1rem">
                        <div class="d-flex align-items-center gap-3">
                            <div class="pm-icon-wrap"><i class="fa-duotone fa-building-columns"></i></div>
                            <div>
                                <div style="font-weight:800;color:var(--eg-text)">Bank Transfer</div>
                                <div style="font-size:.78rem;color:var(--eg-muted)">IBAN / SWIFT / Beneficiary</div>
                            </div>
                        </div>
                        <?php if($bank): ?><span class="pm-saved-badge"><i class="fa-solid fa-check me-1"></i>Saved</span><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card pm-choice <?= $activeTab==='crypto' ? 'active':'' ?>" data-pm="crypto" onclick="switchPm('crypto')">
                    <div class="card-body d-flex align-items-center justify-content-between" style="padding:.9rem 1rem">
                        <div class="d-flex align-items-center gap-3">
                            <div class="pm-icon-wrap crypto"><i class="fa-duotone fa-bitcoin-sign"></i></div>
                            <div>
                                <div style="font-weight:800;color:var(--eg-text)">Crypto</div>
                                <div style="font-size:.78rem;color:var(--eg-muted)">Wallet address / network</div>
                            </div>
                        </div>
                        <?php if($crypto): ?><span class="pm-saved-badge"><i class="fa-solid fa-check me-1"></i>Saved</span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="pm-panel-bank" style="<?= $activeTab!=='bank' ? 'display:none' : '' ?>">
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="booster_save_payout_method">
                <input type="hidden" name="method" value="bank_transfer">
                <input type="hidden" name="id" value="<?= (int)($bank['id']??0) ?>">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Beneficiary Name <span class="text-danger">*</span></label><input class="form-control" name="beneficiary" required value="<?= $h($bankD['beneficiary']??'') ?>" placeholder="Jane Doe"></div>
                    <div class="col-12"><label class="form-label">IBAN <span class="text-danger">*</span></label><input class="form-control" name="iban" required value="<?= $h($bankD['iban']??'') ?>" placeholder="DE00 0000 0000 0000 0000 00"></div>
                    <div class="col-md-6"><label class="form-label">SWIFT / BIC <span style="color:var(--eg-muted)">(optional)</span></label><input class="form-control" name="bic" value="<?= $h($bankD['bic']??'') ?>" placeholder="BANKDEFFXXX"></div>
                    <div class="col-md-6"><label class="form-label">Bank Name <span style="color:var(--eg-muted)">(optional)</span></label><input class="form-control" name="bank_name" value="<?= $h($bankD['bank_name']??'') ?>" placeholder="Postbank"></div>
                    <div class="col-md-4"><label class="form-label">Country <span class="text-danger">*</span></label><input class="form-control" name="country" required value="<?= $h($bankD['country']??'') ?>" placeholder="e.g. Germany"></div>
                    <div class="col-md-4"><label class="form-label">Currency <span class="text-danger">*</span></label><input class="form-control" name="currency" required value="<?= $h($bankD['currency']??'EUR') ?>" placeholder="EUR"></div>
                    <div class="col-md-4"><label class="form-label">Address <span class="text-danger">*</span></label><input class="form-control" name="address" required value="<?= $h($bankD['address']??'') ?>" placeholder="Street, ZIP, City"></div>
                    <div class="col-12 d-flex align-items-center justify-content-between pt-1">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="make_default" value="1" id="mdb" <?= !empty($bank['is_default'])?'checked':'' ?>><label class="form-check-label" for="mdb" style="color:var(--eg-muted);font-size:.85rem">Set as default</label></div>
                        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,var(--eg-purple),var(--eg-pink));border:none;font-weight:700"><i class="fa-duotone fa-floppy-disk me-1"></i>Save Changes</button>
                    </div>
                </div>
            </form>
        </div>

        <div id="pm-panel-crypto" style="<?= $activeTab!=='crypto' ? 'display:none' : '' ?>">
            <div class="mb-3" style="font-size:.82rem;color:var(--eg-muted)"><i class="fa-duotone fa-circle-info me-1"></i>We use USDC on Solana for crypto payouts.</div>
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="booster_save_payout_method">
                <input type="hidden" name="method" value="crypto">
                <input type="hidden" name="id" value="<?= (int)($crypto['id']??0) ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label><input class="form-control" name="name" required value="<?= $h($cryptoD['name']??$cryptoD['coin']??'') ?>" placeholder="Your name"></div>
                    <div class="col-md-6"><label class="form-label">Wallet / Exchange <span class="text-danger">*</span></label><input class="form-control" name="wallet" required value="<?= $h($cryptoD['wallet']??$cryptoD['network']??'') ?>" placeholder="e.g. Binance"></div>
                    <div class="col-md-6"><label class="form-label">Country <span class="text-danger">*</span></label><input class="form-control" name="country" required value="<?= $h($cryptoD['country']??'') ?>" placeholder="e.g. Germany"></div>
                    <div class="col-md-6"><label class="form-label">USDC (Solana) Address <span class="text-danger">*</span></label><input class="form-control" name="address" required value="<?= $h($cryptoD['address']??'') ?>" placeholder="Solana address"></div>
                    <div class="col-12 d-flex align-items-center justify-content-between pt-1">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="make_default" value="1" id="mdc" <?= !empty($crypto['is_default'])?'checked':'' ?>><label class="form-check-label" for="mdc" style="color:var(--eg-muted);font-size:.85rem">Set as default</label></div>
                        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,var(--eg-purple),var(--eg-pink));border:none;font-weight:700"><i class="fa-duotone fa-floppy-disk me-1"></i>Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-clock-rotate-left me-2" style="color:var(--eg-purple)"></i>Payout Requests</h4>
        <div class="input-group input-group-merge input-group-flush" style="max-width:210px">
            <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
            <input id="payoutSearch" type="search" class="form-control" placeholder="Search…">
        </div>
    </div>
    <div class="filter-bar">
        <label>Status</label>
        <button type="button" class="fpill active" data-pr-filter="">All</button>
        <button type="button" class="fpill fpill-pend" data-pr-filter="pending"><span style="width:6px;height:6px;border-radius:50%;background:rgba(255,180,0,.9);display:inline-block"></span>Pending</button>
        <button type="button" class="fpill fpill-appr" data-pr-filter="approved"><span style="width:6px;height:6px;border-radius:50%;background:rgba(0,200,140,.9);display:inline-block"></span>Approved</button>
        <button type="button" class="fpill fpill-rej" data-pr-filter="rejected"><span style="width:6px;height:6px;border-radius:50%;background:rgba(255,70,120,.9);display:inline-block"></span>Rejected</button>
        &nbsp;&nbsp;
        <label>Method</label>
        <button type="button" class="fpill active" data-pm-filter="">All</button>
        <button type="button" class="fpill" data-pm-filter="bank"><i class="fa-duotone fa-building-columns me-1" style="font-size:.75rem"></i>Bank</button>
        <button type="button" class="fpill" data-pm-filter="crypto"><i class="fa-duotone fa-bitcoin-sign me-1" style="font-size:.75rem"></i>Crypto</button>
    </div>

    <?php if(!empty($requests)): ?>
    <div class="table-responsive datatable-custom">
        <table class="js-datatable eg-payout-table table table-borderless table-nowrap table-align-middle card-table" id="payout_table"
               data-hs-datatables-options='{"order":[[0,"desc"]],"search":"#payoutSearch","isResponsive":false,"isShowPaging":false,"pagination":"payoutPagination","entries":"#payoutEntries","info":{"totalQty":"#payoutTotalQty"}}'>
            <thead class="thead-light">
                <tr><th>#</th><th>Method</th><th class="text-end">Amount</th><th>Status</th><th>Admin Note</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach($requests as $r):
                    $stRaw   = strtolower(trim($r['status']??'pending'));
                    $method  = strtolower(trim($r['method']??''));
                    $isCrypto= str_contains($method,'crypto');
                    $pmKey   = $isCrypto ? 'crypto' : 'bank';
                    $methodLabel = $isCrypto ? 'Crypto' : 'Bank Transfer';
                ?>
                <tr data-pr-status="<?= htmlspecialchars($stRaw) ?>" data-pm-type="<?= $pmKey ?>">
                    <td style="color:var(--eg-muted);font-size:.82rem" data-order="<?= (int)($r['id']??0) ?>">#<?= (int)($r['id']??0) ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;background:<?= $isCrypto?'rgba(245,202,153,.1)':'rgba(168,85,247,.1)' ?>;color:<?= $isCrypto?'#f5ca99':'#c084fc' ?>">
                                <i class="fa-duotone <?= $isCrypto?'fa-bitcoin-sign':'fa-building-columns' ?>"></i>
                            </div>
                            <span style="color:var(--eg-text);font-weight:600;font-size:.85rem"><?= $methodLabel ?></span>
                        </div>
                    </td>
                    <td class="text-end pr-money" style="font-weight:800;color:#4ade80" data-order="<?= (int)($r['amount_cents']??0) ?>">€<?= number_format((int)($r['amount_cents']??0)/100,2) ?></td>
                    <td><span class="pr-status is-<?= htmlspecialchars($stRaw) ?>"><?= ucfirst($stRaw) ?></span></td>
                    <td style="color:var(--eg-muted);max-width:220px;white-space:normal;font-size:.82rem"><?= htmlspecialchars($r['admin_note']??'—') ?></td>
                    <td style="color:var(--eg-muted)" data-order="<?= !empty($r['created_at'])?strtotime($r['created_at']):0 ?>"><?= !empty($r['created_at'])?date('d.m.y · H:i',strtotime($r['created_at'])):'—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex align-items-center gap-2">
                    <span style="color:var(--eg-muted)">Showing:</span>
                    <div class="tom-select-custom"><select id="payoutEntries" class="js-select form-select form-select-borderless w-auto" data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'><option value="10" selected>10</option><option value="25">25</option></select></div>
                    <span style="color:var(--eg-muted)">of</span>
                    <span id="payoutTotalQty" style="color:var(--eg-text)"></span>
                </div>
            </div>
            <div class="col-sm-auto"><nav id="payoutPagination"></nav></div>
        </div>
    </div>
    <?php else: ?>
    <div class="card-body text-center py-5" style="color:var(--eg-muted)">
        <i class="fa-duotone fa-money-check-dollar fa-3x d-block mb-3" style="color:rgba(168,85,247,.4)"></i>
        <h5 style="color:var(--eg-text)">No payout requests yet</h5>
        <p class="mb-0">Save your payout method above, then click <strong style="color:var(--eg-pink)">Request Payout</strong>.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Request Modal -->
<div class="modal fade" id="payoutReqModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg eg-payout-dialog">
    <div class="modal-content eg-payout-modal">
      <div class="modal-header eg-payout-modal__header">
        <div class="d-flex align-items-center gap-3">
          <span class="eg-payout-modal__icon"><i class="fa-solid fa-wallet"></i></span>
          <div>
            <h5 class="modal-title mb-0">Request Payout</h5>
            <div class="eg-payout-modal__subtitle">Normal payout only, processed every 1st and 15th.</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form class="ajax-form" action="<?= AJAX_URL ?>" method="post" id="egPayoutRequestForm">
        <input type="hidden" name="action" value="egirl_request_payout">
        <input type="hidden" name="full_balance" value="0" id="egFullBalanceHidden">
        <input type="hidden" name="payout_method_id" id="egPayoutMethodHidden" value="<?= !empty($methods[0]['id']) ? (int)$methods[0]['id'] : '' ?>">

        <div class="modal-body eg-payout-modal__body">
          <div class="eg-payout-info mb-4">
            <div class="eg-payout-info__icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div>
              <div class="eg-payout-info__title">Normal payout schedule</div>
              <div class="eg-payout-info__text">Payouts are processed twice a month, every <strong>1st</strong> and <strong>15th</strong>.</div>
              <div class="eg-payout-info__fees">Fees: Bank Transfer <strong><?= rtrim(rtrim(number_format($feeBank,1),'0'),'.') ?>%</strong>, Crypto <strong><?= rtrim(rtrim(number_format($feeCrypto,1),'0'),'.') ?>%</strong>.</div>
            </div>
          </div>

          <?php if (empty($methods)): ?>
            <div class="alert alert-warning" style="font-size:.85rem"><i class="fa-solid fa-triangle-exclamation me-1"></i>Save a payout method below before requesting a payout.</div>
          <?php endif; ?>

          <div class="eg-payout-grid">
            <div class="eg-payout-section">
              <div class="eg-payout-section__head">
                <span><i class="fa-solid fa-money-bill-transfer me-1"></i> Amount</span>
                <span class="eg-payout-mini">Available: €<?= number_format($balance_cents/100,2) ?> EUR</span>
              </div>

              <label class="form-label">Withdrawal amount</label>
              <div class="eg-money-input">
                <span>€</span>
                <input type="text" inputmode="decimal" name="amount" id="egPayoutAmount" placeholder="0.00" autocomplete="off">
                <span>EUR</span>
              </div>
              <div class="text-muted small mt-2">Min €5.00</div>

              <label class="eg-check-row mt-3" for="egFullBalanceCheck">
                <input class="form-check-input" type="checkbox" id="egFullBalanceCheck">
                <span>
                  <strong>Full available amount</strong>
                  <small>Use your complete available payout balance.</small>
                </span>
              </label>
            </div>

            <div class="eg-payout-section">
              <div class="eg-payout-section__head">
                <span><i class="fa-solid fa-credit-card me-1"></i> Method</span>
                <a href="#" onclick="bootstrap.Modal.getInstance(document.getElementById('payoutReqModal'))?.hide();return false;" class="eg-payout-mini text-decoration-none">Manage methods</a>
              </div>

              <label class="form-label">Payout method</label>
              <div class="eg-method-select" id="egPayoutMethodSelect">
                <button type="button" class="eg-method-select__button" id="egPayoutMethodButton" aria-expanded="false">
                  <span class="eg-method-select__selected">
                    <span class="eg-method-icon"><i class="fa-solid fa-building-columns"></i></span>
                    <span>
                      <strong>Select method</strong>
                      <small>Choose payout method</small>
                    </span>
                  </span>
                  <i class="fa-solid fa-chevron-down eg-method-chevron"></i>
                </button>

                <div class="eg-method-select__menu" id="egPayoutMethodMenu">
                  <?php foreach ($methods as $m):
                      $isCrypto = !empty($m['__is_crypto']);
                      $mD = $m['__details'] ?? [];
                      $label = $isCrypto ? 'Crypto' : 'Bank Transfer';
                      if (!empty($m['is_default'])) $label .= ' (Default)';
                      if ($isCrypto) {
                          $addr = $mD['address'] ?? '';
                          $small = 'USDC · Solana' . ($addr ? (' · ' . substr($addr,0,6) . '...' . substr($addr,-4)) : '');
                      } else {
                          $iban = preg_replace('/\s+/', '', (string)($mD['iban'] ?? ''));
                          $small = $iban ? ('IBAN · ****' . substr($iban, -6)) : 'IBAN / Bank Transfer';
                      }
                      $fee = $isCrypto ? $feeCrypto : $feeBank;
                  ?>
                    <button type="button"
                            class="eg-method-option"
                            data-value="<?= (int)$m['id'] ?>"
                            data-method="<?= $isCrypto ? 'crypto' : 'bank_transfer' ?>"
                            data-fee="<?= $fee ?>"
                            data-label="<?= $h($label) ?>"
                            data-small="<?= $h($small) ?>"
                            data-icon="<?= $isCrypto ? 'fa-coins' : 'fa-building-columns' ?>">
                      <span class="eg-method-icon <?= $isCrypto ? 'is-crypto' : 'is-bank' ?>"><i class="fa-solid <?= $isCrypto ? 'fa-coins' : 'fa-building-columns' ?>"></i></span>
                      <span class="flex-grow-1">
                        <strong><?= $h($label) ?></strong>
                        <small><?= $h($small) ?></small>
                      </span>
                      <span class="eg-fee-chip"><?= $fee ?>% fee</span>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="eg-payout-section mt-3">
            <label class="form-label">Note (optional)</label>
            <input class="form-control eg-note-input" name="note" placeholder="Optional note for admin">
          </div>

          <div class="eg-payout-summary mt-4">
            <div class="eg-payout-summary__row">
              <span>Original amount</span>
              <strong id="egCalcGross">€0.00</strong>
            </div>
            <div class="eg-payout-summary__row">
              <span>Payout fee (<span id="egCalcFeePercent">0%</span>)</span>
              <strong id="egCalcFee">-€0.00</strong>
            </div>
            <div class="eg-payout-summary__row is-total">
              <span>Amount you will receive</span>
              <strong id="egCalcNet">€0.00</strong>
            </div>
          </div>
        </div>

        <div class="modal-footer eg-payout-modal__footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn eg-submit-btn" id="btnSubmitPayout" <?= empty($methods)?'disabled':'' ?>>
            <i class="fa-solid fa-paper-plane me-1"></i> Request Payout
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
function switchPm(key) {
    document.querySelectorAll('.pm-choice').forEach(c=>c.classList.remove('active'));
    document.querySelector('[data-pm="'+key+'"]')?.classList.add('active');
    document.getElementById('pm-panel-bank').style.display   = key==='bank'   ? '' : 'none';
    document.getElementById('pm-panel-crypto').style.display = key==='crypto' ? '' : 'none';
}

(function () {
    const balance = <?= json_encode($balance_cents / 100) ?>;

    const amountEl = document.getElementById('egPayoutAmount');
    const methodHidden = document.getElementById('egPayoutMethodHidden');
    const methodSelect = document.getElementById('egPayoutMethodSelect');
    const methodButton = document.getElementById('egPayoutMethodButton');
    const methodMenu = document.getElementById('egPayoutMethodMenu');
    const fullCheck = document.getElementById('egFullBalanceCheck');
    const fullHidden = document.getElementById('egFullBalanceHidden');

    const grossOut = document.getElementById('egCalcGross');
    const feeOut = document.getElementById('egCalcFee');
    const netOut = document.getElementById('egCalcNet');
    const feePctOut = document.getElementById('egCalcFeePercent');

    if (!amountEl || !methodSelect) return;

    let selectedFee = 0;

    function fmt(n) {
        const v = (isNaN(n) ? 0 : n);
        return '€' + v.toFixed(2);
    }

    function parseAmount(raw) {
        if (!raw) return 0;
        const cleaned = String(raw).replace(',', '.').replace(/[^0-9.]/g, '');
        const v = parseFloat(cleaned);
        return isNaN(v) ? 0 : v;
    }

    function setSelectedMethod(option) {
        if (!option || !methodHidden || !methodButton) return;
        methodHidden.value = option.dataset.value || '';
        selectedFee = parseFloat(option.dataset.fee || '0') || 0;

        document.querySelectorAll('.eg-method-option').forEach(el => el.classList.toggle('is-active', el === option));

        const icon = option.dataset.icon || 'fa-building-columns';
        const isCrypto = option.dataset.method === 'crypto';
        const label = option.dataset.label || 'Payout method';
        const small = option.dataset.small || '';

        const selected = methodButton.querySelector('.eg-method-select__selected');
        if (selected) {
            selected.innerHTML =
                '<span class="eg-method-icon ' + (isCrypto ? 'is-crypto' : 'is-bank') + '"><i class="fa-solid ' + icon + '"></i></span>' +
                '<span><strong>' + label + '</strong><small>' + small + '</small></span>';
        }

        methodSelect.classList.remove('is-open');
        methodButton.setAttribute('aria-expanded', 'false');
        calc();
    }

    function effectiveAmount() {
        let v = parseAmount(amountEl.value);

        if (fullCheck.checked) {
            v = balance;
            amountEl.value = balance.toFixed(2);
            amountEl.setAttribute('disabled', 'disabled');
            fullHidden.value = '1';
            return v;
        }

        amountEl.removeAttribute('disabled');
        fullHidden.value = '0';

        if (v > balance) {
            v = balance;
            amountEl.value = balance.toFixed(2);
        }

        return v;
    }

    function calc() {
        const gross = effectiveAmount();
        const feePct = selectedFee;
        const fee = gross * (feePct / 100);
        const net = Math.max(0, gross - fee);

        grossOut.textContent = fmt(gross);
        feePctOut.textContent = feePct.toFixed(1).replace('.0','') + '%';
        feeOut.textContent = '-' + fmt(fee);
        netOut.textContent = fmt(net);
    }

    methodButton?.addEventListener('click', function (e) {
        e.preventDefault();
        const open = !methodSelect.classList.contains('is-open');
        methodSelect.classList.toggle('is-open', open);
        methodButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    methodMenu?.addEventListener('click', function (e) {
        const option = e.target.closest('.eg-method-option');
        if (!option) return;
        e.preventDefault();
        setSelectedMethod(option);
    });

    document.addEventListener('click', function (e) {
        if (!methodSelect || methodSelect.contains(e.target)) return;
        methodSelect.classList.remove('is-open');
        methodButton?.setAttribute('aria-expanded', 'false');
    });

    amountEl.addEventListener('focus', function () {
        if (!amountEl.value || amountEl.value === '0' || amountEl.value === '0.0' || amountEl.value === '0.00') {
            amountEl.value = '';
        } else {
            try { amountEl.select(); } catch (e) {}
        }
    });

    amountEl.addEventListener('blur', function () {
        if (fullCheck.checked) return;
        const v = parseAmount(amountEl.value);
        if (!amountEl.value) return;
        amountEl.value = Math.min(v, balance).toFixed(2);
    });

    amountEl.addEventListener('input', calc);
    fullCheck.addEventListener('change', calc);

    const firstOption = document.querySelector('.eg-method-option');
    if (firstOption) setSelectedMethod(firstOption);
    calc();

    const modal = document.getElementById('payoutReqModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function () { calc(); });
    }
})();

$(document).on('ready',function(){
    HSCore.components.HSDatatables.init($('#payout_table'));
    var dt=$('#payout_table').DataTable(),as='',am='';
    $.fn.dataTable.ext.search.push(function(s,d,i){
        if(s.nTable.id!=='payout_table')return true;
        var row=$(dt.row(i).node());
        return(!as||row.data('pr-status')===as)&&(!am||row.data('pm-type')===am);
    });
    $('[data-pr-filter]').on('click',function(){$('[data-pr-filter]').removeClass('active');$(this).addClass('active');as=$(this).data('pr-filter');dt.draw();});
    $('[data-pm-filter]').on('click',function(){$('[data-pm-filter]').removeClass('active');$(this).addClass('active');am=$(this).data('pm-filter');dt.draw();});
});
</script>
<?= $this->end() ?>
