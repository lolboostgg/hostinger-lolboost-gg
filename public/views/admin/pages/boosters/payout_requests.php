<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Payout Requests - Admin Area | LoLBoost.gg', 'h1' => 'Payout Requests', 'description' => 'View and manage booster payout requests.']]) ?>
<style>
/* Wider booster admin pages: reduce the large left/right gutters on desktop. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>


<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  :root{
    --pr-accent: rgba(124,92,255,1);
    --pr-accent-soft: rgba(124,92,255,.22);
    --pr-accent-ring: rgba(124,92,255,.45);
    --pr-border: rgba(255,255,255,.08);
    --pr-surface: rgba(255,255,255,.04);
  }

  /* --- Toolbar layout (closer to Orders/Clients) --- */
  .pr-toolbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 14px;
    flex-wrap: nowrap; /* keep everything in one row on desktop */
  }
  .pr-filters{
    display:flex;
    align-items:center;
    gap: 14px;
    flex-wrap: nowrap; /* keep groups in one row */
    max-width: 100%;
  }
  .pr-filter-block{
    display:flex;
    flex-direction:row;
    align-items:center;
    gap: .55rem;
    min-width: max-content;
  }
  .pr-filter-label{
    font-size: .65rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    color: rgba(255,255,255,.55);
    padding-left: 0;
    display:flex;
    align-items:center;
    gap:.4rem;
  }
  .pr-filter-label i{ opacity:.9; }

  .pr-search{ min-width: 300px; }
  @media (max-width: 992px){
    .pr-toolbar{ flex-wrap: wrap; }
    .pr-filters{ flex-wrap: wrap; }
    .pr-search{ width: 100%; min-width: 0; }
    .pr-filter-block{ flex: 1 1 auto; }
  }

  /* Table: fit without horizontal scrolling on desktop */
  #invoices_table{ table-layout: fixed; width: 100%; }
  #invoices_table th{ white-space: nowrap; }
  #invoices_table td{ white-space: normal; }
  #invoices_table td.text-end{ white-space: nowrap; }
  #invoices_table .booster-note{ max-width: 280px; }

  /* Filter pills (more visible, like Orders/Clients tabs) */
  .pr-pill-group{
    display:flex;
    gap:.45rem;
    padding:.32rem;
    border-radius:999px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.10);
    align-items:center;
    flex-wrap: nowrap;
    white-space: nowrap;
  }

  /* Emphasize important pill groups (speed/method) */
  .pr-pill-group.is-emph{
    background: rgba(124,92,255,.10);
    border-color: rgba(124,92,255,.20);
  }

  .pr-pill{
    appearance:none;
    border:0;
    background: transparent;
    color: rgba(255,255,255,.86);
    padding:.30rem .70rem;
    border-radius:999px;
    font-size:.80rem;
    line-height:1;
    opacity:.95;
    cursor:pointer;
    transition: background .15s ease, box-shadow .15s ease, transform .05s ease;
    white-space:nowrap;
  }
  .pr-pill:hover{ background: rgba(255,255,255,.06); }
  .pr-pill:active{ transform: translateY(1px); }
  .pr-pill.is-active{
    background: rgba(124,92,255,.26);
    box-shadow: inset 0 0 0 1px rgba(124,92,255,.55);
  }

  /* Table polish (same vibe as reviews) */
  #invoices_table thead th{
    font-weight: 650;
    letter-spacing: .02em;
    text-transform: uppercase;
    font-size: .72rem;
    color: rgba(255,255,255,.62);
  }
  #invoices_table tbody tr{ border-top: 1px solid var(--pr-border); }
  #invoices_table tbody tr:hover{ background: rgba(255,255,255,.02); }

  /* ID cell */
  .pr-id{
    display:inline-flex;
    font-size: .78rem;
    font-weight: 650;
    letter-spacing: .01em;
    color: rgba(255,255,255,.72);
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: clip !important;
  }
  .pr-idwrap{
    display:flex;
    align-items:center;
    gap:.45rem;
    white-space: nowrap !important;
  }
  /* Force single-line IDs (prevents breaking like "#1\n6") */
  #invoices_table td.pr-col-id,
  #invoices_table th.pr-col-id{
    white-space: nowrap !important;
    word-break: keep-all !important;
    overflow: visible;
    text-overflow: clip;
    width: 92px;
    min-width: 92px;
    max-width: 92px;
  }
  #invoices_table td.pr-col-id .pr-id{ word-break: keep-all !important; }


  .badge{ border-radius: 10px; }
  .badge-soft{
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.70);
  }
  .badge-soft-success{ background: rgba(0,200,140,.16); border-color: rgba(0,200,140,.22); color: rgba(160,255,220,.95); }
  .badge-soft-danger{ background: rgba(255,70,120,.16); border-color: rgba(255,70,120,.22); color: rgba(255,170,190,.95); }
  .badge-soft-warning{ background: rgba(255,180,0,.16); border-color: rgba(255,180,0,.22); color: rgba(255,220,140,.95); }
  .badge-soft-primary{ background: rgba(90,160,255,.16); border-color: rgba(90,160,255,.22); color: rgba(180,210,255,.95); }

  /* Boosting-like status pills (dot + outlined pill) */
  .pr-status{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .34rem .70rem;
    border-radius: 999px;
    font-weight: 650;
    font-size: .72rem;
    letter-spacing: .03em;
    text-transform: uppercase;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    white-space: nowrap;
  }
  .pr-status::before{
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: rgba(255,255,255,.45);
    box-shadow: 0 0 0 3px rgba(255,255,255,.06);
  }
  .pr-status.is-pending{ border-color: rgba(255,180,0,.28); background: rgba(255,180,0,.12); color: rgba(255,220,140,.95); }
  .pr-status.is-pending::before{ background: rgba(255,180,0,.95); box-shadow: 0 0 0 3px rgba(255,180,0,.18); }
  .pr-status.is-completed{ border-color: rgba(0,200,140,.28); background: rgba(0,200,140,.12); color: rgba(160,255,220,.95); }
  .pr-status.is-completed::before{ background: rgba(0,200,140,.95); box-shadow: 0 0 0 3px rgba(0,200,140,.18); }
  .pr-status.is-rejected{ border-color: rgba(255,70,120,.28); background: rgba(255,70,120,.12); color: rgba(255,170,190,.95); }
  .pr-status.is-rejected::before{ background: rgba(255,70,120,.95); box-shadow: 0 0 0 3px rgba(255,70,120,.18); }

  /* Complete button (green tone like boosting) */
  .btn-complete{
    background: rgba(0,200,140,.18) !important;
    border: 1px solid rgba(0,200,140,.28) !important;
    color: rgba(200,255,236,.95) !important;
  }
  .btn-complete:hover{ background: rgba(0,200,140,.24) !important; }

  /* Amounts styling */
  .pr-amounts{ line-height: 1.1; }
  .pr-money{
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  }
  .pr-amounts .received{ color: rgba(160,255,220,.95); }
  .pr-amt-chips{ display:flex; justify-content:flex-end; gap:.35rem; margin-top:.30rem; flex-wrap:wrap; }
  .pr-chip{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding:.14rem .42rem;
    border-radius:999px;
    font-size:.64rem;
    font-weight:650;
    letter-spacing:.01em;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.09);
    color: rgba(255,255,255,.70);
    white-space: nowrap;
  }
  .pr-chip .k{ opacity:.75; font-weight:700; }
  .pr-chip.is-fee{ background: rgba(255,70,120,.10); border-color: rgba(255,70,120,.20); color: rgba(255,170,190,.95); }
  .pr-chip.is-req{ background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.10); }

  /* Note: preview is clamped; full text is shown in modal */
  .pr-note{
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    white-space: normal;
    max-width: 280px;
  }

  .pr-note-link{ color: rgba(124,92,255,.95); text-decoration: none; }
  .pr-note-link:hover{ text-decoration: underline; }
  .pr-full-balance{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding:.20rem .50rem;
    border-radius: 999px;
    background: rgba(124,92,255,.18);
    border: 1px solid rgba(124,92,255,.30);
    color: rgba(210,200,255,.95);
    font-size: .70rem;
    font-weight: 650;
    letter-spacing: .02em;
    white-space: nowrap;
    margin-right: .35rem;
  }

  /* Compact action buttons */
  .btn-compact{
    padding: .35rem .55rem;
    border-radius: 12px;
  }

  /* Reviews-like custom modal (no Bootstrap dependency) */
  .pr-modal{
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .pr-modal.is-open{ display:flex; }
  .pr-modal__backdrop{
    position:absolute;
    inset:0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(4px);
  }
  .pr-modal__panel{
    position: relative;
    width: min(560px, 100%);
    border-radius: 18px;
    background: rgba(38, 40, 44, .96);
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: 0 20px 60px rgba(0,0,0,.45);
    overflow: hidden;
  }
  .pr-modal__header{
    padding: 1rem 1.25rem .75rem;
    display:flex;
    align-items:flex-start;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
  }
  .pr-modal__title{ margin:0; font-size: 1.05rem; color: rgba(255,255,255,.92); }
  .pr-modal__close{
    width: 40px;
    height: 40px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.86);
  }
  .pr-modal__close:hover{ background: rgba(255,255,255,.06); }
  .pr-modal__body{ padding: 1rem 1.25rem; color: rgba(255,255,255,.70); }
  .pr-modal__footer{
    padding: .9rem 1.25rem 1.05rem;
    display:flex;
    justify-content: flex-end;
    gap: .65rem;
    border-top: 1px solid rgba(255,255,255,.08);
  }

/* Mobile-friendly filter pills */
.payout-filter-bar{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.payout-filter-pills{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.payout-filter-pill{font-size:12px;line-height:1;padding:.35rem .55rem;border-radius:999px;white-space:nowrap}
@media (max-width: 576px){
  .payout-filter-bar{gap:10px}
  .payout-filter-pills{flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;padding-bottom:2px;max-width:100%}
  .payout-filter-pills::-webkit-scrollbar{height:0}
  .payout-filter-pill{font-size:11px;padding:.32rem .5rem}
}


  /* --- Mobile responsiveness tweaks (Admin Payout Requests) --- */
  .pr-pill-group{display:flex;align-items:center;gap:8px;flex-wrap:wrap;max-width:100%}
  .pr-pill{font-size:12px;line-height:1;padding:.35rem .55rem;border-radius:999px;white-space:nowrap}
  .pr-pill-sep{opacity:.6}
  @media (max-width: 576px){
    /* Pills: single row, swipeable */
    .pr-pill-group{flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;padding-bottom:4px}
    .pr-pill-group::-webkit-scrollbar{height:0}
    .pr-pill{font-size:11px;padding:.30rem .5rem}
    .pr-pill-sep{flex:0 0 auto}

    /* Search input: full width under pills */
    .pr-pill-group + .input-group{min-width:0 !important;width:100%}
    .pr-pill-group + .input-group .form-control{font-size:13px}

    /* Table: tighter spacing + allow horizontal scroll */
    .datatable-custom{overflow-x:auto}
    .card-table th, .card-table td{padding:.65rem .75rem !important;font-size:12px}
    .card-table .avatar{width:32px;height:32px}
  }

/* Mobile: stack filter pill rows like Orders List (rows under each other) */
@media (max-width: 576px){
  .pr-toolbar{display:flex;flex-direction:column;align-items:stretch;gap:10px}
  .pr-pill-group{display:flex;flex-wrap:wrap;gap:8px;overflow:visible;white-space:normal}
  .pr-pill-row{display:flex;flex-direction:column;gap:8px} /* wrapper for multiple groups */
  .pr-search{width:100%}
}

</style>
<?= $this->end() ?>

<?php
// In this view, payout requests are passed as $data (array of rows).
$rows = is_array($data ?? null) ? $data : [];

// Booster icon lookup (routes query doesn't include icon by default)
global $db;
$__boosterIconCache = [];
function __pr_icon_url($path){
  if (!$path) return '';
  $p = (string)$path;
  if (preg_match('~^https?://~i', $p) || str_starts_with($p, '//')) return $p;
  // Most installs store upload paths like "icons/xyz.png".
  if (defined('BASE_URL')) return rtrim(BASE_URL,'/') . '/' . ltrim($p,'/');
  if (defined('SITE_URL')) return rtrim(SITE_URL,'/') . '/' . ltrim($p,'/');
  return '/' . ltrim($p,'/');
}
?>

<div class="card">
  <div class="card-header">
    <div class="row justify-content-between align-items-center flex-grow-1">
      <div class="col-12 col-md">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-header-title">Payout Requests</h5>
        </div>
      </div>

      <div class="col-12 col-md-auto mt-3 mt-md-0">
        <div class="pr-toolbar">
          <div class="pr-filters">

            <div class="pr-filter-block">
              <div class="pr-filter-label"><i class="fa-solid fa-circle-info"></i> Status</div>
              <div class="pr-pill-group" role="tablist" aria-label="Payout status filter">
                <button type="button" class="pr-pill js-status-pill" data-status="">Any</button>
                <button type="button" class="pr-pill js-status-pill is-active" data-status="Pending">Pending</button>
                <button type="button" class="pr-pill js-status-pill" data-status="Completed">Completed</button>
                <button type="button" class="pr-pill js-status-pill" data-status="Rejected">Rejected</button>
              </div>
            </div>

            <div class="pr-filter-block">
              <div class="pr-filter-label"><i class="fa-solid fa-bolt"></i> Speed</div>
              <div class="pr-pill-group is-emph" role="tablist" aria-label="Payout speed filter">
                <button type="button" class="pr-pill js-speed-pill is-active" data-speed="">All</button>
                <button type="button" class="pr-pill js-speed-pill" data-speed="Fast">Fast</button>
                <button type="button" class="pr-pill js-speed-pill" data-speed="Normal">Normal</button>
              </div>
            </div>

            <div class="pr-filter-block">
              <div class="pr-filter-label"><i class="fa-solid fa-credit-card"></i> Method</div>
              <div class="pr-pill-group is-emph" role="tablist" aria-label="Payout method filter">
                <button type="button" class="pr-pill js-method-pill is-active" data-method="">All</button>
                <button type="button" class="pr-pill js-method-pill" data-method="Bank Transfer">Bank</button>
                <button type="button" class="pr-pill js-method-pill" data-method="Crypto">Crypto</button>
              </div>
            </div>

          </div>

          <div class="input-group input-group-merge input-group-flush pr-search">
            <div class="input-group-prepend input-group-text"><i class="fa-solid fa-magnifying-glass"></i></div>
            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search payout requests" aria-label="Search payout requests">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table"
      data-hs-datatables-options='{
        "order": [[6, "desc"]],
        "info": {"totalQty": "#datatableEntriesInfoTotalQty"},
        "entries": "#datatableEntries",
        "search": "#datatableWithSearchInput",
        "isResponsive": false,
        "pageLength": 25,
        "isShowPaging": true,
        "pagination": "datatableWithSearchPagination"
      }'
      id="invoices_table">

      <thead class="thead-light">
        <tr>
          <th class="pr-col-id">ID</th>
          <th style="width:280px;">Booster</th>
          <th style="width:150px;">Method</th>
          <th style="width:110px;">Speed</th>
          <th style="width:130px;">Status</th>
          <th class="text-end" style="width:170px;">Amounts</th>
          <th style="width:155px;">Created</th>
          <th class="text-end" style="width:210px;">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" class="text-muted py-4">No payout requests found.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $id = (int)($r['id'] ?? 0);

              $boosterId = (int)($r['booster_id'] ?? 0);
              $boosterName = (string)($r['booster_username'] ?? $r['username'] ?? 'Booster');

              // icon
              $boosterAvatar = (string)($r['booster_icon'] ?? $r['icon'] ?? '');
              if ($boosterAvatar === '' && $boosterId > 0 && isset($db)) {
                if (!array_key_exists($boosterId, $__boosterIconCache)) {
                  $rowIcon = $db->row("SELECT icon FROM boosters WHERE id=? LIMIT 1", $boosterId);
                  $__boosterIconCache[$boosterId] = (string)($rowIcon['icon'] ?? '');
                }
                $boosterAvatar = $__boosterIconCache[$boosterId];
              }
              $boosterAvatar = __pr_icon_url($boosterAvatar);

              // method
              // Some older rows may store the *label* inside `method` (e.g. "d") instead of a normalized type.
              // We normalize to either Bank Transfer or Crypto and keep the label as an optional "custom label".
              $rawMethodType = strtolower(trim((string)($r['method_type'] ?? '')));
              $rawMethod = trim((string)($r['method'] ?? ''));
              $methodDetails = (string)($r['method_details'] ?? '');
              $methodDetailsArray = [];
              if ($methodDetails !== '') {
                $decodedMethodDetails = json_decode($methodDetails, true);
                if (is_array($decodedMethodDetails)) $methodDetailsArray = $decodedMethodDetails;
              }

              $methodLabelCustom = trim((string)($r['method_label'] ?? ''));
              $methodType = $rawMethodType !== '' ? $rawMethodType : strtolower($rawMethod);

              // If method looks like a free label ("d") and not a real type, treat it as a custom label.
              $allowedTypes = ['bank', 'bank_transfer', 'banktransfer', 'crypto', 'crypto_transfer', 'cryptotransfer'];
              if ($methodType === '' || !in_array($methodType, $allowedTypes, true)) {
                if ($methodLabelCustom === '' && $rawMethod !== '' && strlen($rawMethod) <= 40) {
                  $methodLabelCustom = $rawMethod;
                }
                $methodType = 'bank_transfer';
              }

              $methodTypeLabel = (str_contains($methodType, 'crypto')) ? 'Crypto' : 'Bank Transfer';

              if ($methodLabelCustom !== '' && strcasecmp($methodLabelCustom, $methodTypeLabel) === 0) {
                $methodLabelCustom = '';
              }

              $methodMeta = [];
              if (!empty($methodDetailsArray['country'])) $methodMeta[] = 'Country: ' . $methodDetailsArray['country'];
              if ($methodTypeLabel === 'Bank Transfer' && !empty($methodDetailsArray['currency'])) $methodMeta[] = 'Currency: ' . $methodDetailsArray['currency'];
              if ($methodTypeLabel === 'Crypto') {
                if (!empty($methodDetailsArray['name'])) $methodMeta[] = 'Name: ' . $methodDetailsArray['name'];
                if (!empty($methodDetailsArray['wallet'])) $methodMeta[] = 'Wallet: ' . $methodDetailsArray['wallet'];
              }

              // speed
              $speedRaw = strtolower((string)($r['payout_speed'] ?? $r['speed'] ?? 'normal'));
              $speedLabel = $speedRaw === 'fast' ? 'Fast' : 'Normal';

              $statusRaw = strtolower((string)($r['status'] ?? 'pending'));
              $statusLabel = ucfirst($statusRaw);

              // amounts are stored as cents in booster_payout_requests
              $requestedCents = (int)($r['requested_amount'] ?? 0);
              $feeCents = (int)($r['requested_fee_amount'] ?? $r['fee_amount'] ?? $r['fee'] ?? $r['fee_cents'] ?? 0);
              $netCents = (int)($r['requested_net_amount'] ?? $r['net_amount'] ?? $r['net'] ?? $r['net_cents'] ?? 0);
              $feePercent = (float)($r['requested_fee_percent'] ?? $r['fee_percent'] ?? 0);


              // If COMPLETED and processed_* amounts exist, show FINAL paid values
              if ($statusRaw === 'completed' && (int)($r['processed_amount'] ?? 0) > 0) {
                $requestedCents = (int)($r['processed_amount'] ?? $requestedCents);
                $feeCents = (int)($r['processed_fee_amount'] ?? $feeCents);
                $netCents = (int)($r['processed_net_amount'] ?? $netCents);
              }

              if ($feeCents <= 0 && $feePercent > 0 && $requestedCents > 0) {
                $feeCents = (int) round($requestedCents * ($feePercent / 100));
              }
              if ($netCents <= 0 && $requestedCents > 0) {
                $netCents = max(0, $requestedCents - $feeCents);
              }

              $requested = $requestedCents / 100;
              $fee = $feeCents / 100;
              $net = $netCents / 100;

              $created = (string)($r['created_at'] ?? $r['created'] ?? '-');

              // created display: dd-mm-yy · HH:MM (and correct sorting)
              $createdTs = $created && $created !== '-' ? (int)strtotime($created) : 0;
              $createdDisplay = $createdTs ? date('d-m-y · H:i', $createdTs) : '-';

              // note handling (FULL_BALANCE badge + optional note text)
              $noteRaw = trim((string)($r['note'] ?? ''));
              $isFullBalance = stripos($noteRaw, '[FULL_BALANCE]') !== false;
              $note = trim(str_ireplace('[FULL_BALANCE]', '', $noteRaw));
              $noteIsLong = mb_strlen($note) > 70;


// Dynamic amounts for FULL_BALANCE (still open): show CURRENT available-for-payout
// This keeps the admin table up-to-date if booster balance changes after request creation.
if ($isFullBalance && in_array($statusRaw, ['pending', 'processing'], true)) {
  $b = null;
  if ($boosterId > 0 && isset($db)) {
    $b = $db->row("SELECT balance, insurance_required_amount FROM boosters WHERE id=? LIMIT 1", $boosterId);
  }
  if ($b) {
    // current "available for payout" in cents (balance - insurance reserve)
    if (function_exists('booster_available_for_payout_cents')) {
      $requestedCents = (int)booster_available_for_payout_cents($b);
    } else {
      $balanceNow = (int)($b['balance'] ?? 0);
      $reqInsurance = (isset($b['insurance_required_amount']) && $b['insurance_required_amount'] !== null && $b['insurance_required_amount'] !== '')
        ? max(0, (int)$b['insurance_required_amount'])
        : 2500; // default 25€
      $requestedCents = $balanceNow - $reqInsurance;
      if ($requestedCents < 0) $requestedCents = 0;
    }

    // Fee percent fallback (older rows might not store it)
    if ($feePercent <= 0) {
      $baseFee = (str_contains($methodType, 'crypto')) ? 5.0 : 3.0;
      $feePercent = $baseFee + (($speedRaw === 'fast') ? 5.0 : 0.0);
    }

    $feeCents = ($requestedCents > 0 && $feePercent > 0) ? (int) round($requestedCents * ($feePercent / 100)) : 0;
    $netCents = ($requestedCents > 0) ? max(0, $requestedCents - $feeCents) : 0;

    $requested = $requestedCents / 100;
    $fee = $feeCents / 100;
    $net = $netCents / 100;
  }
}

              // badges
              $speedBadge = $speedRaw === 'fast' ? 'badge-soft-warning' : 'badge-soft-primary';

              $statusBadge = 'badge-soft';
              if ($statusRaw === 'pending') $statusBadge = 'badge-soft-warning';
              if ($statusRaw === 'completed') $statusBadge = 'badge-soft-success';
              if ($statusRaw === 'rejected') $statusBadge = 'badge-soft-danger';
            ?>
            <tr>
              <td class="pr-col-id">
                <div class="pr-idwrap">
                  <span class="pr-id">#<?= $id ?></span>
</div>
              </td>

              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm avatar-circle me-3">
                    <?php if ($boosterAvatar): ?>
                      <img class="avatar-img" src="<?= htmlspecialchars($boosterAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
                    <?php else: ?>
                      <span class="avatar-initials"><?= htmlspecialchars(strtoupper(substr($boosterName, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="flex-grow-1">
                    <a class="d-block h5 mb-0" href="<?= ADMN_URL ?>/booster/<?= $boosterId ?>">
                      <?= htmlspecialchars($boosterName, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <div class="mt-1" style="display:flex;flex-wrap:wrap;gap:.35rem;align-items:center;">
                      <?php if (!empty($isFullBalance)): ?>
                        <span class="pr-badge-full"><i class="fa-solid fa-wallet"></i> Full balance</span>
                      <?php endif; ?>
                    </div>
                    <?php if ($note !== ''): ?>
                      <div class="pr-note text-muted small" title="<?= htmlspecialchars($note, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($note, ENT_QUOTES, 'UTF-8') ?>
                      </div>
                      <div class="small mt-1">
                        <a href="javascript:void(0)"
                           class="pr-note-link js-open-note"
                           data-booster="<?= htmlspecialchars($boosterName, ENT_QUOTES, 'UTF-8') ?>"
                           data-note="<?= htmlspecialchars($note, ENT_QUOTES, 'UTF-8') ?>">
                          View note
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td>
                <a href="javascript:void(0)"
                   class="js-open-method text-decoration-none"
                   data-method-type="<?= htmlspecialchars($methodType, ENT_QUOTES, 'UTF-8') ?>"
                   data-method-label="<?= htmlspecialchars($methodTypeLabel, ENT_QUOTES, 'UTF-8') ?>"
                   data-method-label-custom="<?= htmlspecialchars($methodLabelCustom, ENT_QUOTES, 'UTF-8') ?>"
                   data-method-details="<?= htmlspecialchars($methodDetails, ENT_QUOTES, 'UTF-8') ?>"
                   data-booster="<?= htmlspecialchars($boosterName, ENT_QUOTES, 'UTF-8') ?>">
                  <span class="fw-semibold" style="color: rgba(255,255,255,.88);">
                    <?= htmlspecialchars($methodTypeLabel, ENT_QUOTES, 'UTF-8') ?>
                  </span>
                  <?php if ($methodLabelCustom !== ''): ?>
                    <span class="d-block text-muted small" style="max-width: 210px;">
                      <?= htmlspecialchars($methodLabelCustom, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  <?php endif; ?>
                </a>
                <?php if (!empty($methodMeta)): ?>
                  <div class="mt-1" style="display:flex;flex-wrap:wrap;gap:.25rem;">
                    <?php foreach ($methodMeta as $meta): ?>
                      <span class="pr-chip" style="font-size:.62rem;"><?= htmlspecialchars($meta, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>

              <td>
              <?php if ($speedRaw === 'fast'): ?>
                <span class="badge <?= $speedBadge ?>"><i class="fa-solid fa-bolt me-1"></i><?= $speedLabel ?></span>
              <?php else: ?>
                <span class="badge <?= $speedBadge ?>"><i class="fa-solid fa-gauge-high me-1"></i><?= $speedLabel ?></span>
              <?php endif; ?>
              </td>

              <td>
                <span class="pr-status is-<?= htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
              </td>

              <td class="text-end">
                <div class="pr-amounts">
                  <div class="fw-semibold received pr-money"><?= number_format($net, 2, ",", ".") ?>€</div>
                  <div class="pr-amt-chips">
                    <span class="pr-chip is-req"><span class="k">Req</span> <span class="pr-money"><?= number_format($requested, 2, ",", ".") ?>€</span></span>
                    <span class="pr-chip is-fee"><span class="k">Fee</span> <span class="pr-money"><?= number_format($fee, 2, ",", ".") ?>€</span></span>
                  </div>
                </div>
              </td>
              <td class="text-muted" data-order="<?= (int)$createdTs ?>"><?= htmlspecialchars($createdDisplay, ENT_QUOTES, 'UTF-8') ?></td>

              <td class="text-end">
                <?php if ($statusRaw === 'pending'): ?>
                  <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" style="margin-right:.35rem;" onsubmit="return confirm('Mark this payout request as completed?');">
                    <input type="hidden" name="action" value="admin_process_payout_request">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-sm btn-complete btn-compact">
                      <i class="fa-solid fa-check me-1"></i> Complete
                    </button>
                  </form>

                  <button type="button"
                          class="btn btn-sm btn-outline-danger btn-compact js-open-reject"
                          data-id="<?= $id ?>"
                          data-booster="<?= htmlspecialchars($boosterName, ENT_QUOTES, 'UTF-8') ?>"
                          data-method="<?= htmlspecialchars($methodTypeLabel, ENT_QUOTES, 'UTF-8') ?>"
                          data-speed="<?= $speedLabel ?>"
                          data-amount="<?= number_format($requested, 2) ?> EUR">
                    <i class="fa-solid fa-xmark me-1"></i> Reject
                  </button>
                <?php else: ?>
                  <span class="badge badge-soft" style="padding:.38rem .65rem; border-radius: 999px;">
                    <i class="fa-solid fa-lock me-1"></i> Locked
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-footer">
    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
      <div class="col-sm mb-2 mb-sm-0">
        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
          <span class="me-2">Showing:</span>
          <div class="tom-select-custom">
            <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto" autocomplete="off" data-hs-tom-select-options='{"searchInDropdown": false, "hideSearch": true}'>
              <option value="10">10</option>
              <option value="25" selected>25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <span class="text-secondary me-2">of</span>
          <span id="datatableEntriesInfoTotalQty"></span>
        </div>
      </div>

      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <nav id="datatableWithSearchPagination" aria-label="Payout requests pagination"></nav>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Method details modal (quick access) -->
<div class="pr-modal" id="prMethodModal" aria-hidden="true">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="prMethodTitle">
    <div class="pr-modal__header">
      <div>
        <h3 class="pr-modal__title" id="prMethodTitle">Payout Method Details</h3>
        <div class="small text-muted" id="prMethodSub" style="margin-top:.15rem;">—</div>
      </div>
      <button type="button" class="pr-modal__close" data-pr-close aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="pr-modal__body">
      <div class="mb-3">
        <div class="small text-muted">Booster</div>
        <div class="fw-semibold" id="methodBooster">—</div>
      </div>
      <div class="mb-3">
        <div class="small text-muted">Method</div>
        <div class="fw-semibold" id="methodName">—</div>
      </div>
      <div class="border rounded-3" style="border-color: rgba(255,255,255,.08) !important; background: rgba(255,255,255,.05);">
        <div class="p-3" id="methodDetails">—</div>
      </div>
    </div>

    <div class="pr-modal__footer">
      <button type="button" class="btn btn-white" data-pr-close>Close</button>
    </div>
  </div>
</div>

<!-- Note modal (full note text) -->
<div class="pr-modal" id="prNoteModal" aria-hidden="true">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="prNoteTitle">
    <div class="pr-modal__header">
      <div>
        <h3 class="pr-modal__title" id="prNoteTitle">Booster Note</h3>
        <div class="small text-muted" id="prNoteSub" style="margin-top:.15rem;">—</div>
      </div>
      <button type="button" class="pr-modal__close" data-pr-close aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="pr-modal__body">
      <div class="border rounded-3" style="border-color: rgba(255,255,255,.08) !important; background: rgba(255,255,255,.05);">
        <div class="p-3" id="prNoteBody" style="white-space: pre-wrap; word-break: break-word;">—</div>
      </div>
    </div>

    <div class="pr-modal__footer">
      <button type="button" class="btn btn-white" data-pr-close>Close</button>
    </div>
  </div>
</div>

<!-- Reject modal (with note) -->
<div class="pr-modal" id="prRejectModal" aria-hidden="true">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="prRejectTitle">
    <form class="ajax-form" method="post" action="<?= AJAX_URL ?>">
      <div class="pr-modal__header">
        <div>
          <h3 class="pr-modal__title" id="prRejectTitle">Reject payout request</h3>
          <div class="small text-muted" id="prRejectSub" style="margin-top:.15rem;">—</div>
        </div>
        <button type="button" class="pr-modal__close" data-pr-close aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="pr-modal__body">
        <input type="hidden" name="action" value="admin_reject_payout_request">
        <input type="hidden" name="id" id="rejectId" value="">

        <div class="mb-3">
          <div class="small text-muted">Booster</div>
          <div class="fw-semibold" id="rejectBooster">—</div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <div class="small text-muted">Method</div>
            <div class="fw-semibold" id="rejectMethod">—</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Speed</div>
            <div class="fw-semibold" id="rejectSpeed">—</div>
          </div>
        </div>

        <div class="mb-3">
          <div class="small text-muted">Amount</div>
          <div class="fw-semibold" id="rejectAmount">—</div>
        </div>

        <div class="mb-0">
          <label class="form-label">Rejection note (optional)</label>
          <textarea class="form-control" name="reason" rows="3" placeholder="E.g. Missing payout details, suspicious activity, etc."></textarea>
        </div>
      </div>

      <div class="pr-modal__footer">
        <button type="button" class="btn btn-white" data-pr-close>Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fa-solid fa-ban me-1"></i> Reject
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
  $(function () {
    // TomSelect (entries dropdown)
    HSCore.components.HSTomSelect.init('.js-select');

    // DataTables init via HSCore (same as Reviews)
    HSCore.components.HSDatatables.init($('#invoices_table'), {
      language: {
        zeroRecords: `<div class="text-center p-4">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width: 10rem;" data-hs-theme-appearance="default">
          <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width: 10rem;" data-hs-theme-appearance="dark">
          <p class="mb-0">No data to show</p>
        </div>`
      }
    });

    let dt = null;
    try { dt = $('#invoices_table').DataTable(); } catch (e) { dt = null; }

    function getDtInstance() {
      try { return $('#invoices_table').DataTable(); } catch (e) { return null; }
    }

    const prFilterStorageKey = 'lb_admin_payout_requests_filters:' + window.location.pathname;

    function readSavedFilters() {
      const filters = { status: 'Pending', speed: '', method: '' };

      try {
        const stored = JSON.parse(localStorage.getItem(prFilterStorageKey) || '{}');
        if (Object.prototype.hasOwnProperty.call(stored, 'status')) filters.status = stored.status || '';
        if (Object.prototype.hasOwnProperty.call(stored, 'speed')) filters.speed = stored.speed || '';
        if (Object.prototype.hasOwnProperty.call(stored, 'method')) filters.method = stored.method || '';
      } catch (e) {}

      const params = new URLSearchParams(window.location.search);
      if (params.has('pr_status')) filters.status = params.get('pr_status') || '';
      if (params.has('pr_speed')) filters.speed = params.get('pr_speed') || '';
      if (params.has('pr_method')) filters.method = params.get('pr_method') || '';

      return filters;
    }

    function persistFilters(filters) {
      try { localStorage.setItem(prFilterStorageKey, JSON.stringify(filters)); } catch (e) {}

      const params = new URLSearchParams(window.location.search);
      const pairs = {
        pr_status: filters.status || '',
        pr_speed: filters.speed || '',
        pr_method: filters.method || ''
      };

      Object.keys(pairs).forEach(function (key) {
        if (pairs[key]) params.set(key, pairs[key]);
        else params.delete(key);
      });

      const nextUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
      window.history.replaceState({}, '', nextUrl);
    }

    function setActivePill(selector, el) {
      $(selector).removeClass('is-active');
      $(el).addClass('is-active');
    }

    function setActivePillByValue(selector, dataName, value) {
      const safeValue = value || '';
      let $target = $(selector).filter(function () {
        return String($(this).data(dataName) || '') === String(safeValue);
      }).first();

      if (!$target.length) {
        $target = $(selector).filter(function () {
          return String($(this).data(dataName) || '') === '';
        }).first();
      }

      if ($target.length) setActivePill(selector, $target[0]);
    }

    function getActiveFilters() {
      return {
        status: $('.js-status-pill.is-active').data('status') || '',
        speed: $('.js-speed-pill.is-active').data('speed') || '',
        method: $('.js-method-pill.is-active').data('method') || ''
      };
    }

    function applyFilters(shouldPersist) {
      const filters = getActiveFilters();

      if (shouldPersist !== false) persistFilters(filters);

      if (dt) {
        // Method column index = 2, Speed = 3, Status = 4
        dt.column(4).search(filters.status || '');
        dt.column(3).search(filters.speed || '');
        dt.column(2).search(filters.method || '');
        dt.draw();
      }
    }

    const initialFilters = readSavedFilters();
    setActivePillByValue('.js-status-pill', 'status', initialFilters.status);
    setActivePillByValue('.js-speed-pill', 'speed', initialFilters.speed);
    setActivePillByValue('.js-method-pill', 'method', initialFilters.method);

    // Apply saved filters on page load.
    (function bootstrapInitialFilters(){
      // If HSCore initialized DataTables asynchronously, poll briefly until instance exists.
      if (!dt) dt = getDtInstance();

      if (dt) {
        // Apply after the first paint so DataTables has rendered the rows.
        requestAnimationFrame(function(){ applyFilters(false); });
        return;
      }

      let tries = 0;
      const t = setInterval(function(){
        dt = getDtInstance();
        if (dt) {
          clearInterval(t);
          applyFilters(false);
        } else if (++tries >= 20) {
          clearInterval(t);
        }
      }, 150);
    })();

    $(document).on('click', '.js-status-pill', function () {
      setActivePill('.js-status-pill', this);
      applyFilters(true);
    });
    $(document).on('click', '.js-speed-pill', function () {
      setActivePill('.js-speed-pill', this);
      applyFilters(true);
    });
    $(document).on('click', '.js-method-pill', function () {
      setActivePill('.js-method-pill', this);
      applyFilters(true);
    });

    // Modals (custom, like Reviews)
    const methodModalEl = document.getElementById('prMethodModal');
    const noteModalEl = document.getElementById('prNoteModal');
    const rejectModalEl = document.getElementById('prRejectModal');

    function openPrModal(el){
      if (!el) return;
      el.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      el.setAttribute('aria-hidden', 'false');
    }

    function closePrModal(el){
      if (!el) return;
      el.classList.remove('is-open');
      document.body.style.overflow = '';
      el.setAttribute('aria-hidden', 'true');
    }

    // Close handlers
    $(document).on('click', '[data-pr-close]', function(){
      closePrModal(methodModalEl);
      closePrModal(noteModalEl);
      closePrModal(rejectModalEl);
    });

    // ESC closes
    $(document).on('keydown', function(e){
      if (e.key === 'Escape'){
        closePrModal(methodModalEl);
        closePrModal(noteModalEl);
        closePrModal(rejectModalEl);
      }
    });

    // Open full note modal
    $(document).on('click', '.js-open-note', function(){
      const booster = $(this).data('booster') || 'Booster';
      const note = $(this).data('note') || '';
      $('#prNoteSub').text(booster);
      $('#prNoteBody').text(note || '—');
      openPrModal(noteModalEl);
    });

    function safeText(s) {
      if (s === null || typeof s === 'undefined') return '';
      if (typeof s === 'object') {
        try { return JSON.stringify(s); } catch (e) { return String(s); }
      }
      return String(s);
    }

    function formatDetails(raw) {
      const txt = safeText(raw).trim();
      if (!txt) return '<span class="text-muted">No payout details saved.</span>';

      // If JSON, render key/value
      try {
        const obj = JSON.parse(txt);
        if (obj && typeof obj === 'object') {
          let html = '<div class="d-grid" style="gap:.55rem;">';
          Object.keys(obj).forEach(k => {
            const v = (obj[k] === null || typeof obj[k] === 'undefined') ? '' : String(obj[k]);
            html += `
              <div>
                <div class="small text-muted">${$('<div>').text(k).html()}</div>
                <div class="fw-semibold" style="word-break: break-word;">${$('<div>').text(v).html()}</div>
              </div>`;
          });
          html += '</div>';
          return html;
        }
      } catch (e) {}

      // Plain text fallback
      return '<pre class="mb-0" style="white-space: pre-wrap; color: rgba(255,255,255,.85);">' + $('<div>').text(txt).html() + '</pre>';
    }

    $(document).on('click', '.js-open-method', function (e) {
      e.preventDefault();
      const booster = $(this).data('booster') || '—';
      const methodLabel = $(this).data('method-label') || '—';
      const custom = $(this).data('method-label-custom') || '';
      const details = $(this).attr('data-method-details') || $(this).data('method-details') || '';
      $('#methodBooster').text(booster);
      $('#methodName').text(custom ? (methodLabel + ' — ' + custom) : methodLabel);
      $('#methodDetails').html(formatDetails(details));
      openPrModal(methodModalEl);
    });

    $(document).on('click', '.js-open-reject', function () {
      $('#rejectId').val($(this).data('id') || '');
      $('#rejectBooster').text($(this).data('booster') || '—');
      $('#rejectMethod').text($(this).data('method') || '—');
      $('#rejectSpeed').text($(this).data('speed') || '—');
      $('#rejectAmount').text($(this).data('amount') || '—');
      openPrModal(rejectModalEl);
    });

    // Loading state for action forms (prevents double submits)
    $(document).on('submit', '.ajax-form', function(){
      const $form = $(this);
      const $btn = $form.find('button[type="submit"]').first();
      if (!$btn.length) return true;
      if ($btn.data('loading') === true) return false;
      $btn.data('loading', true);
      const original = $btn.html();
      $btn.data('original', original);

      let label = 'Processing';
      if ($btn.hasClass('btn-complete')) label = 'Completing';
      if ($btn.hasClass('btn-danger')) label = 'Rejecting';

      $btn.prop('disabled', true);
      $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-1"></i> ' + label);
      return true;
    });
  });
</script>
<?= $this->end() ?>
