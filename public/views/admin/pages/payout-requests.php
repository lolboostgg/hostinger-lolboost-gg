<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Payout Requests - Admin Area | LoLBoost.gg', 'h1' => 'Payout Requests', 'description' => 'Review booster, seller and E-Girl payout requests in one table.']]) ?>

<style>
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid { max-width: min(1760px, calc(100vw - 48px)) !important; }
}
@media (min-width: 1400px) {
  body .container, body .container-lg, body .container-xl, body .container-xxl { max-width: min(1760px, calc(100vw - 48px)) !important; }
}
@media (max-width: 991.98px) {
  body .content.container, body .content .container, body main .container, body .main .container, body .page-content .container, body .container, body .container-fluid {
    max-width: 100% !important; padding-left: 1rem !important; padding-right: 1rem !important;
  }
}
</style>

<?= $this->start('styles') ?>
<style>
  :root { --pr-accent: rgba(124,92,255,1); --pr-border: rgba(255,255,255,.08); }
  .pr-summary { display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap:12px; margin-bottom:18px; }
  .pr-summary-card { border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.035); border-radius:16px; padding:14px 16px; }
  .pr-summary-card .k { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.52); }
  .pr-summary-card .v { margin-top:5px; font-size:1.25rem; font-weight:800; color:rgba(255,255,255,.92); }
  .pr-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
  .pr-card-title { margin:0; font-weight:800; color:rgba(255,255,255,.94); }
  .pr-card-sub { margin-top:4px; font-size:.78rem; color:rgba(255,255,255,.48); }
  .pr-toolbar { margin-top:16px; display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; }
  .pr-filters { display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; }
  .pr-filter-block { display:flex; flex-direction:column; align-items:flex-start; gap:.45rem; min-width:max-content; }
  .pr-filter-label { font-size:.64rem; letter-spacing:.11em; text-transform:uppercase; color:rgba(255,255,255,.46); display:flex; align-items:center; gap:.38rem; padding-left:.25rem; }
  .pr-pill-group { display:flex; gap:.25rem; padding:.28rem; border-radius:14px; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08); align-items:center; flex-wrap:nowrap; white-space:nowrap; }
  .pr-pill-group.is-emph { background:rgba(255,255,255,.035); border-color:rgba(255,255,255,.08); }
  .pr-pill { appearance:none; border:0; background:transparent; color:rgba(255,255,255,.78); padding:.42rem .78rem; border-radius:11px; font-size:.78rem; font-weight:700; line-height:1; cursor:pointer; transition:background .15s, box-shadow .15s, color .15s; white-space:nowrap; }
  .pr-pill:hover { background:rgba(255,255,255,.06); color:rgba(255,255,255,.94); }
  .pr-pill.is-active { background:rgba(124,92,255,.32); color:#fff; box-shadow:inset 0 0 0 1px rgba(124,92,255,.55); }
  .pr-search { min-width:300px; max-width:360px; }
  .pr-search .form-control { min-height:42px; border-radius:12px!important; background:rgba(255,255,255,.035)!important; border:1px solid rgba(255,255,255,.08)!important; }
  .pr-search .input-group-text { border-radius:12px!important; background:rgba(255,255,255,.035)!important; border:1px solid rgba(255,255,255,.08)!important; border-right:0!important; }
  #payout_requests_table thead th { font-weight:650; letter-spacing:.02em; text-transform:uppercase; font-size:.72rem; color:rgba(255,255,255,.62); white-space:nowrap; }
  #payout_requests_table tbody tr { border-top:1px solid var(--pr-border); }
  #payout_requests_table tbody tr:hover { background:rgba(255,255,255,.02); }
  .pr-type { display:inline-flex; align-items:center; gap:.42rem; padding:.26rem .55rem; border-radius:999px; font-weight:700; font-size:.72rem; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.045); white-space:nowrap; }
  .pr-type.is-booster { color:rgba(190,205,255,.96); border-color:rgba(90,120,255,.28); background:rgba(90,120,255,.12); }
  .pr-type.is-seller { color:rgba(180,255,220,.96); border-color:rgba(0,200,140,.26); background:rgba(0,200,140,.11); }
  .pr-type.is-egirl { color:rgba(255,190,235,.96); border-color:rgba(255,100,190,.28); background:rgba(255,100,190,.12); }
  .pr-status { display:inline-flex; align-items:center; gap:.45rem; padding:.34rem .70rem; border-radius:999px; font-weight:650; font-size:.72rem; letter-spacing:.03em; text-transform:uppercase; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); white-space:nowrap; }
  .pr-status::before { content:""; width:7px; height:7px; border-radius:999px; background:rgba(255,255,255,.45); box-shadow:0 0 0 3px rgba(255,255,255,.06); }
  .pr-status.is-pending, .pr-status.is-processing { border-color:rgba(255,180,0,.28); background:rgba(255,180,0,.12); color:rgba(255,220,140,.95); }
  .pr-status.is-pending::before, .pr-status.is-processing::before { background:rgba(255,180,0,.95); box-shadow:0 0 0 3px rgba(255,180,0,.18); }
  .pr-status.is-approved { border-color:rgba(90,160,255,.28); background:rgba(90,160,255,.12); color:rgba(180,210,255,.95); }
  .pr-status.is-approved::before { background:rgba(90,160,255,.95); box-shadow:0 0 0 3px rgba(90,160,255,.18); }
  .pr-status.is-completed, .pr-status.is-paid { border-color:rgba(0,200,140,.28); background:rgba(0,200,140,.12); color:rgba(160,255,220,.95); }
  .pr-status.is-completed::before, .pr-status.is-paid::before { background:rgba(0,200,140,.95); box-shadow:0 0 0 3px rgba(0,200,140,.18); }
  .pr-status.is-rejected { border-color:rgba(255,70,120,.28); background:rgba(255,70,120,.12); color:rgba(255,170,190,.95); }
  .pr-status.is-rejected::before { background:rgba(255,70,120,.95); box-shadow:0 0 0 3px rgba(255,70,120,.18); }
  .pr-money { font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; }
  .pr-amounts { line-height:1.15; }
  .pr-amt-chips { display:flex; justify-content:flex-end; gap:.35rem; margin-top:.30rem; flex-wrap:wrap; }
  .pr-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.14rem .42rem; border-radius:999px; font-size:.64rem; font-weight:650; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.70); white-space:nowrap; }
  .pr-chip.is-fee { background:rgba(255,70,120,.10); border-color:rgba(255,70,120,.20); color:rgba(255,170,190,.95); }
  .pr-chip.is-net { background:rgba(0,200,140,.10); border-color:rgba(0,200,140,.20); color:rgba(160,255,220,.95); }
  .pr-user-link { display:inline-block; color:rgba(255,255,255,.92); text-decoration:none; }
  .pr-user-link:hover { color:#fff; text-decoration:none; }
  .pr-user-link:hover .fw-semibold { text-decoration:underline; text-underline-offset:3px; }
  .pr-user-sub { color:rgba(255,255,255,.52); font-size:.78rem; margin-top:2px; }
  .pr-user-link:hover .pr-user-sub { color:rgba(255,255,255,.72); }
  .btn-compact { padding:.35rem .55rem; border-radius:12px; }
  .btn-approve { background:rgba(0,200,140,.18)!important; border:1px solid rgba(0,200,140,.28)!important; color:rgba(200,255,236,.95)!important; }
  .btn-approve:hover { background:rgba(0,200,140,.24)!important; }
  .badge-soft { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:rgba(255,255,255,.70); border-radius:10px; }
  .pr-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:1rem; }
  .pr-modal.is-open { display:flex; }
  .pr-modal__backdrop { position:absolute; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(4px); }
  .pr-modal__panel { position:relative; width:min(560px,100%); border-radius:18px; background:rgba(38,40,44,.96); border:1px solid rgba(255,255,255,.10); box-shadow:0 20px 60px rgba(0,0,0,.45); overflow:hidden; }
  .pr-modal__header { padding:1rem 1.25rem .75rem; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; border-bottom:1px solid rgba(255,255,255,.08); }
  .pr-modal__title { margin:0; font-size:1.05rem; color:rgba(255,255,255,.92); }
  .pr-modal__close { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:rgba(255,255,255,.86); cursor:pointer; }
  .pr-modal__body { padding:1rem 1.25rem; color:rgba(255,255,255,.70); }
  .pr-modal__footer { padding:.9rem 1.25rem 1.05rem; display:flex; justify-content:flex-end; gap:.65rem; border-top:1px solid rgba(255,255,255,.08); }
  @media (max-width: 992px) { .pr-summary{grid-template-columns:repeat(2,minmax(0,1fr));} .pr-toolbar{align-items:stretch;} .pr-search{width:100%; max-width:none; min-width:0;} .pr-filters{width:100%; overflow-x:auto; flex-wrap:nowrap; padding-bottom:6px;} .pr-filter-block{min-width:auto;} }
  @media (max-width: 576px) { .pr-summary{grid-template-columns:1fr;} .datatable-custom{overflow-x:auto;} .card-table th,.card-table td{padding:.65rem .75rem!important;font-size:12px;} }
</style>
<?= $this->end() ?>

<?php
$booster_requests = is_array($booster_requests ?? null) ? $booster_requests : [];
$seller_requests  = is_array($seller_requests  ?? null) ? $seller_requests  : [];
$egirl_requests   = is_array($egirl_requests   ?? null) ? $egirl_requests   : [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($cents) => '€' . number_format(((int)$cents) / 100, 2);
$rows = [];

foreach ($booster_requests as $r) {
  $id = (int)($r['id'] ?? 0);
  $statusRaw = strtolower(trim((string)($r['status'] ?? 'pending')));
  $grossCents = (int)($r['processed_amount'] ?? 0);
  if ($grossCents <= 0) $grossCents = (int)($r['requested_amount'] ?? 0);
  $feeCents = (int)($r['processed_fee_amount'] ?? 0);
  if ($feeCents <= 0) $feeCents = (int)($r['requested_fee_amount'] ?? 0);
  $netCents = (int)($r['processed_net_amount'] ?? 0);
  if ($netCents <= 0) $netCents = (int)($r['requested_net_amount'] ?? max(0, $grossCents - $feeCents));
  $methodRaw = strtolower(trim((string)($r['method_type'] ?? $r['method'] ?? '')));
  $methodLabel = $methodRaw === 'crypto' ? 'Crypto' : 'Bank Transfer';

  // Open requests created with “Full available amount” must always reflect
  // the booster’s current available payout balance, including later orders,
  // fines and manual balance corrections. Completed requests stay historical.
  $noteRaw = (string)($r['note'] ?? '');
  $isFullBalance = stripos($noteRaw, '[FULL_BALANCE]') !== false;
  $isOpenRequest = in_array($statusRaw, ['pending', 'processing'], true);

  if ($isFullBalance && $isOpenRequest) {
    $currentBalanceCents = max(0, (int)($r['booster_current_balance'] ?? 0));
    $insuranceRaw = $r['booster_insurance_required_amount'] ?? null;
    $insuranceCents = ($insuranceRaw !== null && $insuranceRaw !== '')
      ? max(0, (int)$insuranceRaw)
      : 2500;

    $grossCents = max(0, $currentBalanceCents - $insuranceCents);

    $feePercent = (float)($r['requested_fee_percent'] ?? 0);
    if ($feePercent <= 0) {
      $feePercent = str_contains($methodRaw, 'crypto') ? 5.0 : 3.0;
      if (strtolower((string)($r['payout_speed'] ?? 'normal')) === 'fast') {
        $feePercent += 5.0;
      }
    }

    $feeCents = (int)round($grossCents * ($feePercent / 100));
    $netCents = max(0, $grossCents - $feeCents);
  }
  $rows[] = [
    'source' => 'booster', 'source_label' => 'Booster', 'id' => $id,
    'person_id' => (int)($r['booster_id'] ?? 0), 'person_name' => (string)($r['booster_username'] ?? $r['username'] ?? 'Booster'), 'person_sub' => 'Booster ID #' . (int)($r['booster_id'] ?? 0),
    'profile_url' => ADMN_URL . '/booster/' . (int)($r['booster_id'] ?? 0) . '/profile',
    'method_raw' => $methodRaw, 'method_label' => $methodLabel, 'method_details' => (string)($r['method_details'] ?? $r['details'] ?? ''),
    'status_raw' => $statusRaw, 'status_label' => ucfirst($statusRaw), 'gross' => $grossCents, 'fee' => $feeCents, 'net' => $netCents, 'fee_percent' => (float)($r['requested_fee_percent'] ?? 0),
    'created_at' => (string)($r['created_at'] ?? ''), 'note' => trim(str_ireplace('[FULL_BALANCE]', '', $noteRaw)), 'is_full_balance' => $isFullBalance,
  ];
}

foreach ($seller_requests as $r) {
  $id = (int)($r['id'] ?? 0);
  $statusRaw = strtolower(trim((string)($r['status'] ?? 'PENDING')));
  // Older seller rows can contain an empty status because the legacy ENUM
  // rejected the newer COMPLETED value. Treat those requests as open so an
  // admin can complete them and perform the missing balance deduction.
  if ($statusRaw === '') $statusRaw = 'pending';
  if ($statusRaw === 'approved') $statusRaw = 'completed';
  $grossCents = (int)($r['amount_cents'] ?? 0);

  $methodRaw = strtolower(trim((string)($r['method'] ?? '')));
  $isCrypto = str_contains($methodRaw, 'crypto');
  $methodLabel = $isCrypto ? 'Crypto' : 'Bank Transfer';

  // Seller payouts use the same payout fees as booster payouts:
  // Bank transfer = 3%, Crypto = 5%.
  // Older seller payout rows may not have fee/net values saved, so calculate
  // a reliable fallback for the overview instead of showing €0.00.
  $defaultFeePercent = $isCrypto ? 5.0 : 3.0;
  $feePercent = (float)($r['fee_percent'] ?? 0);
  if ($feePercent <= 0) $feePercent = $defaultFeePercent;

  $feeCents = (int)($r['fee_cents'] ?? 0);
  if ($feeCents <= 0 && $grossCents > 0) {
    $feeCents = (int)round($grossCents * $feePercent / 100);
  }

  $netCents = (int)($r['net_cents'] ?? 0);
  if ($netCents <= 0 && $grossCents > 0) {
    $netCents = max(0, $grossCents - $feeCents);
  }

  $rows[] = [
    'source' => 'seller', 'source_label' => 'Seller', 'id' => $id,
    'person_id' => (int)($r['seller_id'] ?? 0), 'person_name' => (string)($r['seller_username'] ?? $r['username'] ?? 'Seller'), 'person_sub' => trim((string)($r['seller_email'] ?? '')) ?: ('Seller ID #' . (int)($r['seller_id'] ?? 0)),
    'profile_url' => ADMN_URL . '/seller/' . (int)($r['seller_id'] ?? 0) . '/profile',
    'method_raw' => $methodRaw, 'method_label' => $methodLabel, 'method_details' => (string)($r['details'] ?? ''),
    'status_raw' => $statusRaw, 'status_label' => ucfirst($statusRaw), 'gross' => $grossCents, 'fee' => $feeCents, 'net' => $netCents, 'fee_percent' => $feePercent,
    'created_at' => (string)($r['created_at'] ?? ''), 'note' => (string)($r['admin_note'] ?? ''),
  ];
}

foreach ($egirl_requests as $r) {
  $id = (int)($r['id'] ?? 0);
  $statusRaw = strtolower(trim((string)($r['status'] ?? 'PENDING')));
  $grossCents = (int)($r['amount_cents'] ?? 0);

  $requestDetails = [];
  if (!empty($r['details']) && is_string($r['details'])) {
    $decoded = json_decode((string)$r['details'], true);
    if (is_array($decoded)) $requestDetails = $decoded;
  }

  $feeCents = (int)($requestDetails['fee_amount_cents'] ?? 0);
  $netCents = (int)($requestDetails['net_amount_cents'] ?? max(0, $grossCents - $feeCents));
  $feePercent = (float)($requestDetails['fee_percent'] ?? 0);

  $methodRaw = strtolower(trim((string)($r['method_type'] ?? $r['method'] ?? '')));
  $methodLabel = trim((string)($r['method_label'] ?? ''));
  if ($methodLabel === '') $methodLabel = str_contains($methodRaw, 'crypto') ? 'Crypto' : 'Bank Transfer';

  $rows[] = [
    'source' => 'egirl', 'source_label' => 'E-Girl', 'id' => $id,
    'person_id' => (int)($r['egirl_id'] ?? 0), 'person_name' => (string)($r['egirl_username'] ?? $r['username'] ?? 'E-Girl'), 'person_sub' => 'E-Girl ID #' . (int)($r['egirl_id'] ?? 0),
    'profile_url' => ADMN_URL . '/egirl/' . (int)($r['egirl_id'] ?? 0) . '/profile',
    'method_raw' => $methodRaw, 'method_label' => $methodLabel, 'method_details' => (string)($r['method_details'] ?? ''),
    'status_raw' => $statusRaw, 'status_label' => ucfirst($statusRaw), 'gross' => $grossCents, 'fee' => $feeCents, 'net' => $netCents, 'fee_percent' => $feePercent,
    'created_at' => (string)($r['created_at'] ?? ''), 'note' => (string)($r['admin_note'] ?? $requestDetails['note'] ?? ''),
  ];
}

usort($rows, function($a, $b) {
  $ap = in_array($a['status_raw'], ['pending','processing'], true) ? 1 : 0;
  $bp = in_array($b['status_raw'], ['pending','processing'], true) ? 1 : 0;
  if ($ap !== $bp) return $bp <=> $ap;
  return strtotime($b['created_at'] ?: '1970-01-01') <=> strtotime($a['created_at'] ?: '1970-01-01');
});

$pendingCount = count(array_filter($rows, fn($r) => in_array($r['status_raw'], ['pending','processing'], true)));
$boosterCount = count($booster_requests);
$sellerCount = count($seller_requests);
$egirlCount = count($egirl_requests);
?>

<div class="pr-summary">
  <div class="pr-summary-card"><div class="k">Pending</div><div class="v"><?= (int)$pendingCount ?></div></div>
  <div class="pr-summary-card"><div class="k">Boosters</div><div class="v"><?= (int)$boosterCount ?></div></div>
  <div class="pr-summary-card"><div class="k">Sellers</div><div class="v"><?= (int)$sellerCount ?></div></div>
  <div class="pr-summary-card"><div class="k">E-Girls</div><div class="v"><?= (int)$egirlCount ?></div></div>
  <div class="pr-summary-card" id="payoutTotalCard">
    <div class="k" id="payoutTotalLabel">Total Payout</div>
    <div class="v pr-money" id="payoutTotalValue">€0.00</div>
    <div class="small text-muted mt-1" id="payoutTotalMeta">0 requests</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="pr-card-head">
      <div>
        <h5 class="pr-card-title">All Payout Requests</h5>
        <div class="pr-card-sub">Filter all booster, seller and E-Girl payout requests from one clean overview.</div>
      </div>
    </div>

    <div class="pr-toolbar">
      <div class="pr-filters">
        <div class="pr-filter-block">
          <div class="pr-filter-label"><i class="fa-solid fa-layer-group"></i> Type</div>
          <div class="pr-pill-group" role="tablist">
            <button type="button" class="pr-pill js-type-pill is-active" data-type="">All</button>
            <button type="button" class="pr-pill js-type-pill" data-type="booster">Boosters</button>
            <button type="button" class="pr-pill js-type-pill" data-type="seller">Sellers</button>
            <button type="button" class="pr-pill js-type-pill" data-type="egirl">E-Girls</button>
          </div>
        </div>
        <div class="pr-filter-block">
          <div class="pr-filter-label"><i class="fa-solid fa-circle-info"></i> Status</div>
          <div class="pr-pill-group" role="tablist">
            <button type="button" class="pr-pill js-status-pill" data-status="">Any</button>
            <button type="button" class="pr-pill js-status-pill is-active" data-status="open">Open</button>
            <button type="button" class="pr-pill js-status-pill" data-status="approved">Approved</button>
            <button type="button" class="pr-pill js-status-pill" data-status="done">Done</button>
            <button type="button" class="pr-pill js-status-pill" data-status="rejected">Rejected</button>
          </div>
        </div>
        <div class="pr-filter-block">
          <div class="pr-filter-label"><i class="fa-solid fa-credit-card"></i> Method</div>
          <div class="pr-pill-group" role="tablist">
            <button type="button" class="pr-pill js-method-pill is-active" data-method="">All</button>
            <button type="button" class="pr-pill js-method-pill" data-method="bank">Bank</button>
            <button type="button" class="pr-pill js-method-pill" data-method="crypto">Crypto</button>
          </div>
        </div>
      </div>

      <div class="input-group input-group-merge pr-search">
        <div class="input-group-prepend input-group-text"><i class="fa-solid fa-magnifying-glass"></i></div>
        <input id="payoutRequestsSearchInput" type="search" class="form-control" placeholder="Search payout requests">
      </div>
    </div>
  </div>

  <div class="table-responsive datatable-custom">
    <table class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table" id="payout_requests_table"
           data-hs-datatables-options='{
             "order": [[6,"desc"]],
             "info": {"totalQty":"#payoutRequestsInfoQty"},
             "entries": "#payoutRequestsEntries",
             "search": "#payoutRequestsSearchInput",
             "isResponsive": false,
             "pageLength": 25,
             "isShowPaging": true,
             "pagination": "payoutRequestsPagination"
           }'>
      <thead class="thead-light">
        <tr>
          <th style="width:120px;">Type</th>
          <th style="width:90px;">ID</th>
          <th style="width:260px;">User</th>
          <th style="width:175px;">Method</th>
          <th class="text-end" style="width:190px;">Amounts</th>
          <th style="width:140px;">Status</th>
          <th style="width:155px;">Created</th>
          <th class="text-end" style="width:250px;">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">No payout requests found.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r):
          $createdTs = !empty($r['created_at']) ? (int)strtotime($r['created_at']) : 0;
          $methodNorm = str_contains($r['method_raw'], 'crypto') ? 'crypto' : 'bank';
          $statusGroup = in_array($r['status_raw'], ['pending','processing'], true) ? 'open' : (in_array($r['status_raw'], ['completed','paid'], true) ? 'done' : $r['status_raw']);
          $isOpen = in_array($r['status_raw'], ['pending','processing'], true);
        ?>
        <tr data-type="<?= $h($r['source']) ?>" data-status="<?= $h($statusGroup) ?>" data-method="<?= $h($methodNorm) ?>" data-payout-cents="<?= (int)$r['net'] ?>" data-original-cents="<?= (int)$r['gross'] ?>">
          <td><span class="pr-type is-<?= $h($r['source']) ?>"><i class="fa-solid <?= $r['source'] === 'booster' ? 'fa-user-shield' : ($r['source'] === 'seller' ? 'fa-store' : 'fa-heart') ?>"></i><?= $h($r['source_label']) ?></span></td>
          <td class="fw-semibold text-muted">#<?= (int)$r['id'] ?></td>
          <td>
            <a class="pr-user-link" href="<?= $h($r['profile_url']) ?>">
              <div class="fw-semibold"><?= $h($r['person_name']) ?></div>
              <div class="pr-user-sub"><?= $h($r['person_sub']) ?></div>
            </a>
          </td>
          <td>
            <a href="javascript:void(0)" class="js-open-method text-decoration-none fw-semibold" style="color:rgba(255,255,255,.88)"
               data-method-label="<?= $h($r['method_label']) ?>"
               data-method-details="<?= $h($r['method_details']) ?>"
               data-user="<?= $h($r['person_name']) ?>">
              <?= $h($r['method_label']) ?>
            </a>
          </td>
          <td class="text-end" data-order="<?= (int)$r['net'] ?>">
            <div class="pr-amounts">
              <div class="fw-semibold pr-money"><?= $money($r['net']) ?></div>
              <?php if ((int)$r['fee'] > 0 || (int)$r['net'] !== (int)$r['gross']): ?>
                <div class="pr-amt-chips">
                  <span class="pr-chip is-fee">Fee <?= $money($r['fee']) ?><?= ((float)$r['fee_percent'] > 0 ? ' (' . $h(number_format((float)$r['fee_percent'], 0)) . '%)' : '') ?></span>
                  <span class="pr-chip is-net">Original <?= $money($r['gross']) ?></span>
                </div>
              <?php endif; ?>
            </div>
          </td>
          <td data-order="<?= $h($r['status_raw']) ?>"><span class="pr-status is-<?= $h($r['status_raw']) ?>"><?= $h($r['status_label']) ?></span></td>
          <td class="text-muted" data-order="<?= (int)$createdTs ?>"><?= $createdTs ? date('d.m.Y H:i', $createdTs) : '—' ?></td>
          <td class="text-end">
            <?php if ($r['source'] === 'booster' && $isOpen): ?>
              <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" onsubmit="return confirm('Mark booster payout request #<?= (int)$r['id'] ?> as completed?');">
                <input type="hidden" name="action" value="admin_process_payout_request">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn btn-sm btn-approve btn-compact"><i class="fa-solid fa-check me-1"></i>Complete</button>
              </form>
              <button type="button" class="btn btn-sm btn-outline-danger btn-compact js-open-reject" data-source="booster" data-id="<?= (int)$r['id'] ?>" data-user="<?= $h($r['person_name']) ?>" data-amount="<?= $money($r['gross']) ?>"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
            <?php elseif ($r['source'] === 'seller' && $isOpen): ?>
              <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" onsubmit="return confirm('Mark seller payout request #<?= (int)$r['id'] ?> as completed?');">
                <input type="hidden" name="action" value="admin_process_seller_payout">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="status" value="COMPLETED">
                <button type="submit" class="btn btn-sm btn-approve btn-compact"><i class="fa-solid fa-check me-1"></i>Complete</button>
              </form>
              <button type="button" class="btn btn-sm btn-outline-danger btn-compact js-open-reject" data-source="seller" data-id="<?= (int)$r['id'] ?>" data-user="<?= $h($r['person_name']) ?>" data-amount="<?= $money($r['gross']) ?>"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
            <?php elseif ($r['source'] === 'egirl' && $isOpen): ?>
              <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" onsubmit="return confirm('Mark E-Girl payout request #<?= (int)$r['id'] ?> as completed?');">
                <input type="hidden" name="action" value="admin_egirl_payout_update">
                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="status" value="COMPLETED">
                <button type="submit" class="btn btn-sm btn-approve btn-compact"><i class="fa-solid fa-check me-1"></i>Complete</button>
              </form>
              <button type="button" class="btn btn-sm btn-outline-danger btn-compact js-open-reject" data-source="egirl" data-id="<?= (int)$r['id'] ?>" data-user="<?= $h($r['person_name']) ?>" data-amount="<?= $money($r['gross']) ?>"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
            <?php elseif ($r['source'] === 'egirl' && $r['status_raw'] === 'approved'): ?>
              <form class="ajax-form d-inline" method="post" action="<?= AJAX_URL ?>" onsubmit="return confirm('Mark E-Girl payout request #<?= (int)$r['id'] ?> as paid?');">
                <input type="hidden" name="action" value="admin_egirl_payout_update">
                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="status" value="PAID">
                <button type="submit" class="btn btn-sm btn-primary btn-compact"><i class="fa-solid fa-check-double me-1"></i>Mark Paid</button>
              </form>
            <?php else: ?>
              <span class="badge badge-soft px-2"><i class="fa-solid fa-lock me-1"></i>Locked</span>
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
        <div class="d-flex align-items-center gap-2">
          <span>Showing:</span>
          <div class="tom-select-custom"><select id="payoutRequestsEntries" class="js-select form-select form-select-borderless w-auto" data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option></select></div>
          <span class="text-secondary">of</span><span id="payoutRequestsInfoQty"></span>
        </div>
      </div>
      <div class="col-sm-auto"><nav id="payoutRequestsPagination"></nav></div>
    </div>
  </div>
</div>

<div class="pr-modal" id="prMethodModal">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel">
    <div class="pr-modal__header"><div><h3 class="pr-modal__title">Payout Method Details</h3><div class="small text-muted" id="methodUser">—</div></div><button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button></div>
    <div class="pr-modal__body"><div class="mb-3"><div class="small text-muted">Method</div><div class="fw-semibold" id="methodName">—</div></div><div class="border rounded-3" style="border-color:rgba(255,255,255,.08)!important;background:rgba(255,255,255,.05)"><div class="p-3" id="methodDetails">—</div></div></div>
    <div class="pr-modal__footer"><button type="button" class="btn btn-white" data-pr-close>Close</button></div>
  </div>
</div>

<div class="pr-modal" id="prRejectModal">
  <div class="pr-modal__backdrop" data-pr-close></div>
  <div class="pr-modal__panel">
    <form class="ajax-form" method="post" action="<?= AJAX_URL ?>" id="rejectForm">
      <div class="pr-modal__header"><h3 class="pr-modal__title">Reject Payout Request</h3><button type="button" class="pr-modal__close" data-pr-close><i class="fa-solid fa-xmark"></i></button></div>
      <div class="pr-modal__body">
        <input type="hidden" name="action" id="rejectAction" value="">
        <input type="hidden" name="id" id="rejectId" value="">
        <input type="hidden" name="request_id" id="rejectRequestId" value="">
        <input type="hidden" name="status" id="rejectStatus" value="">
        <div class="row g-2 mb-3"><div class="col-6"><div class="small text-muted">User</div><div class="fw-semibold" id="rejectUser">—</div></div><div class="col-6"><div class="small text-muted">Amount</div><div class="fw-semibold" id="rejectAmount">—</div></div></div>
        <div class="mb-0"><label class="form-label">Rejection note</label><textarea class="form-control" name="reason" id="rejectReason" rows="3" placeholder="Reason for rejection…"></textarea><textarea class="d-none" name="admin_note" id="rejectAdminNote"></textarea><textarea class="d-none" name="note" id="rejectEgirlNote"></textarea></div>
      </div>
      <div class="pr-modal__footer"><button type="button" class="btn btn-white" data-pr-close>Cancel</button><button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban me-1"></i>Reject</button></div>
    </form>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(function () {
  HSCore.components.HSDatatables.init($('#payout_requests_table'));
  let dt = null;
  const payoutFilterStorageKey = 'admin_payout_requests_filters_v1';

  function getDt(){ try { return $('#payout_requests_table').DataTable(); } catch(e) { return null; } }

  function readSavedFilters(){
    try {
      const saved = JSON.parse(localStorage.getItem(payoutFilterStorageKey) || '{}');
      return saved && typeof saved === 'object' ? saved : {};
    } catch (e) {
      return {};
    }
  }

  function saveFilters(){
    try {
      localStorage.setItem(payoutFilterStorageKey, JSON.stringify({
        type: String($('.js-type-pill.is-active').data('type') || ''),
        status: String($('.js-status-pill.is-active').data('status') || ''),
        method: String($('.js-method-pill.is-active').data('method') || ''),
        search: String($('#payoutRequestsSearchInput').val() || '')
      }));
    } catch (e) {}
  }

  function restoreFilters(){
    const saved = readSavedFilters();

    const activate = function(selector, dataName, value, fallbackValue){
      const normalized = String(value ?? fallbackValue ?? '');
      let $pill = $(selector).filter(function(){
        return String($(this).data(dataName) || '') === normalized;
      }).first();

      if (!$pill.length) {
        $pill = $(selector).filter(function(){
          return String($(this).data(dataName) || '') === String(fallbackValue || '');
        }).first();
      }

      $(selector).removeClass('is-active');
      $pill.addClass('is-active');
    };

    activate('.js-type-pill', 'type', saved.type, '');
    activate('.js-status-pill', 'status', saved.status, 'open');
    activate('.js-method-pill', 'method', saved.method, '');

    if (typeof saved.search === 'string') {
      $('#payoutRequestsSearchInput').val(saved.search);
    }
  }

  restoreFilters();

  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
    if (!settings.nTable || settings.nTable.id !== 'payout_requests_table') return true;
    const row = settings.aoData[dataIndex]?.nTr;
    if (!row) return true;
    const type = $('.js-type-pill.is-active').data('type') || '';
    const status = $('.js-status-pill.is-active').data('status') || '';
    const method = $('.js-method-pill.is-active').data('method') || '';
    if (type && row.dataset.type !== type) return false;
    if (status && row.dataset.status !== status) return false;
    if (method && row.dataset.method !== method) return false;
    return true;
  });

  function moneyFromCents(cents){
    return new Intl.NumberFormat('de-DE', { style:'currency', currency:'EUR' }).format((Number(cents) || 0) / 100);
  }

  function updatePayoutTotal(){
    dt = dt || getDt();
    if (!dt) return;

    let total = 0;
    let original = 0;
    let count = 0;
    const nodes = dt.rows({ search: 'applied' }).nodes();

    $(nodes).each(function(){
      total += Number(this.dataset.payoutCents || 0);
      original += Number(this.dataset.originalCents || 0);
      count++;
    });

    const status = String($('.js-status-pill.is-active').data('status') || '');
    const labels = {
      open: 'Total Payout',
      approved: 'Total Approved',
      done: 'Total Paid',
      rejected: 'Total Rejected',
      '': 'Total Payouts'
    };

    $('#payoutTotalLabel').text(labels[status] || 'Total Payouts');
    $('#payoutTotalValue').text(moneyFromCents(total));

    let meta = count + (count === 1 ? ' request' : ' requests');
    if (original !== total && count > 0) meta += ' · Original ' + moneyFromCents(original);
    $('#payoutTotalMeta').text(meta);
  }

  function applyFilters(){
    saveFilters();
    dt = dt || getDt();
    if (dt) {
      const searchValue = String($('#payoutRequestsSearchInput').val() || '');
      if (dt.search() !== searchValue) dt.search(searchValue);
      dt.draw();
      updatePayoutTotal();
    }
  }

  setTimeout(function(){
    dt = getDt();
    if (dt) {
      dt.on('draw.dt', updatePayoutTotal);
      applyFilters();
    }
  }, 300);
  $(document).on('click','.js-type-pill', function(){ $('.js-type-pill').removeClass('is-active'); $(this).addClass('is-active'); applyFilters(); });
  $(document).on('click','.js-status-pill', function(){ $('.js-status-pill').removeClass('is-active'); $(this).addClass('is-active'); applyFilters(); });
  $(document).on('click','.js-method-pill', function(){ $('.js-method-pill').removeClass('is-active'); $(this).addClass('is-active'); applyFilters(); });
  $(document).on('input','#payoutRequestsSearchInput', function(){ saveFilters(); });

  const methodModal = document.getElementById('prMethodModal');
  const rejectModal = document.getElementById('prRejectModal');
  function openModal(el){ if(el){ el.classList.add('is-open'); document.body.style.overflow='hidden'; } }
  function closeModal(el){ if(el){ el.classList.remove('is-open'); document.body.style.overflow=''; } }
  $(document).on('click','[data-pr-close]', function(){ closeModal(methodModal); closeModal(rejectModal); });
  $(document).on('keydown', function(e){ if(e.key === 'Escape'){ closeModal(methodModal); closeModal(rejectModal); } });

  function escHtml(value){ return $('<div>').text(value == null ? '' : String(value)).html(); }
  function formatDetails(raw){
    if (!raw) return '<span class="text-muted">No payout details saved.</span>';
    try {
      const obj = JSON.parse(raw);
      if (obj && typeof obj === 'object') {
        const hidden = new Set(['payout_method_id','fee_percent','fee_amount_cents','net_amount_cents','full_balance','note']);
        const labels = {
          account_holder: 'Account holder', iban: 'IBAN', bic: 'BIC', bank_name: 'Bank name', country: 'Country',
          coin: 'Coin', network: 'Network', wallet: 'Wallet', address: 'Address', name: 'Name', paypal_email: 'PayPal email', email: 'Email'
        };
        const preferred = ['account_holder','iban','bic','bank_name','country','coin','network','name','wallet','address','paypal_email','email'];
        const keys = [...preferred.filter(k => Object.prototype.hasOwnProperty.call(obj, k)), ...Object.keys(obj).filter(k => !preferred.includes(k))];
        let html = '<div class="d-grid" style="gap:.55rem">';
        let count = 0;
        keys.forEach(k => {
          if (hidden.has(k)) return;
          const v = obj[k] != null ? String(obj[k]) : '';
          if (!v) return;
          count++;
          const label = labels[k] || k.replaceAll('_',' ').replace(/\w/g, c => c.toUpperCase());
          html += `<div><div class="small text-muted">${escHtml(label)}</div><div class="fw-semibold" style="word-break:break-word">${escHtml(v)}</div></div>`;
        });
        if (!count) return '<span class="text-muted">No payout details saved.</span>';
        return html + '</div>';
      }
    } catch(e) {}
    return `<div style="white-space:pre-wrap;word-break:break-word">${escHtml(raw)}</div>`;
  }
  $(document).on('click','.js-open-method', function(e){ e.preventDefault(); $('#methodUser').text($(this).data('user') || '—'); $('#methodName').text($(this).data('method-label') || '—'); $('#methodDetails').html(formatDetails($(this).attr('data-method-details') || '')); openModal(methodModal); });

  $(document).on('click','.js-open-reject', function(){
    const source = String($(this).data('source') || '');
    const id = String($(this).data('id') || '');
    $('#rejectUser').text($(this).data('user') || '—');
    $('#rejectAmount').text($(this).data('amount') || '—');
    $('#rejectReason').val(''); $('#rejectAdminNote').val(''); $('#rejectEgirlNote').val('');
    $('#rejectId').val(''); $('#rejectRequestId').val(''); $('#rejectStatus').val('');
    if (source === 'booster') { $('#rejectAction').val('admin_reject_payout_request'); $('#rejectId').val(id); }
    if (source === 'seller') { $('#rejectAction').val('admin_process_seller_payout'); $('#rejectId').val(id); $('#rejectStatus').val('REJECTED'); }
    if (source === 'egirl') { $('#rejectAction').val('admin_egirl_payout_update'); $('#rejectRequestId').val(id); $('#rejectStatus').val('REJECTED'); }
    openModal(rejectModal);
  });

  $('#rejectForm').on('submit', function(){
    const reason = $('#rejectReason').val() || '';
    $('#rejectAdminNote').val(reason);
    $('#rejectEgirlNote').val(reason);
  });
});
</script>
<?= $this->end() ?>
