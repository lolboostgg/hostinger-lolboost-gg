<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Review Management - Admin Area | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">

<style>
/* ── Theme tokens (matching dashboard: #1e2022 bg | #25282a card | #2f3235 border | #c5c8cc text)
   accent: #6d5cff / #5c4ae3  |  teal: #00c9a7  |  danger: #ed4c78  |  amber: #f5ca99
   ──────────────────────────────────────────────────────────────────────── */

/* ── Hero card ── */
.rv-hero {
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,.07);
  background: #25282a;
  padding: 28px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 14px;
  margin-bottom: 14px;
  box-shadow: 0 2px 20px rgba(0,0,0,.22);
}
.rv-hero-left { display: flex; align-items: center; gap: 18px; }
.rv-hero-icon {
  width: 52px; height: 52px; border-radius: 15px;
  background: linear-gradient(135deg, rgba(109,92,255,.25), rgba(176,92,255,.15));
  border: 1px solid rgba(109,92,255,.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; color: #c4b5fd; flex-shrink: 0;
}
.rv-hero-title { font-size: 1.2rem; font-weight: 950; color: rgba(255,255,255,.92); margin: 0; }
.rv-hero-sub   { font-size: .82rem; color: rgba(255,255,255,.4); margin: 5px 0 0; }

/* ── Stat chips ── */
.rv-stat-row { display: flex; gap: 10px; flex-wrap: wrap; }
.rv-stat {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 7px 16px; border-radius: 12px;
  border: 1px solid rgba(255,255,255,.07);
  background: rgba(255,255,255,.03);
  font-size: .78rem; color: rgba(255,255,255,.45);
}
.rv-stat { font-size: .82rem; }
.rv-stat strong { color: rgba(255,255,255,.85); font-weight: 800; }

/* ── Toolbar ── */
.rv-toolbar {
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,.07);
  background: #25282a;
  padding: 12px 16px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 10px;
  margin-bottom: 16px;
  box-shadow: 0 2px 16px rgba(0,0,0,.18);
}

/* ── Pills (Any / Pending / Approved) ── */
.rv-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.rv-pill {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: 5px 14px; border-radius: 99px;
  font-size: .78rem; font-weight: 800; cursor: pointer;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.6);
  transition: background .12s, border-color .12s, color .12s;
  user-select: none;
}
.rv-pill { text-decoration: none; }
.rv-pill:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.85); text-decoration: none; }
.rv-pill.active { background: rgba(109,92,255,.18); border-color: rgba(109,92,255,.45); color: #c4b5fd; }
.rv-pill[data-status=""].active    { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: rgba(255,255,255,.85); }
.rv-pill[data-status="Pending"].active { background: rgba(245,202,153,.12); border-color: rgba(245,202,153,.30); color: #f5ca99; }
.rv-pill[data-status="Approved"].active       { background: rgba(0,201,167,.12);   border-color: rgba(0,201,167,.28);   color: #00c9a7; }

/* ── Search ── */
.rv-search-wrap { position: relative; }
.rv-search-wrap input {
  background: rgba(255,255,255,.04) !important;
  border: 1px solid rgba(255,255,255,.09) !important;
  border-radius: 10px !important;
  color: rgba(255,255,255,.85) !important;
  padding: 7px 12px 7px 34px !important;
  font-size: .84rem !important;
  width: 220px;
  transition: border-color .15s, box-shadow .15s;
}
.rv-search-wrap input:focus {
  border-color: rgba(109,92,255,.45) !important;
  box-shadow: 0 0 0 3px rgba(109,92,255,.10) !important;
  outline: none !important;
}
.rv-search-wrap input::placeholder { color: rgba(255,255,255,.25) !important; }
.rv-search-icon {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  color: rgba(255,255,255,.35); font-size: .8rem; pointer-events: none;
}

/* ── Table wrapper ── */
.rv-table-wrap {
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 20px;
  overflow: visible;
  background: #25282a;
  box-shadow: 0 4px 32px rgba(0,0,0,.28);
}
.rv-table { width: 100%; border-collapse: collapse; border-radius: 20px; overflow: hidden; display: table; }
.rv-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.06); }
.rv-table thead th {
  padding: 11px 16px;
  font-size: .68rem; font-weight: 900; color: rgba(255,255,255,.35);
  text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; user-select: none;
}
.rv-table tbody .rv-row { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .12s; }
.rv-table tbody .rv-row:last-child { border-bottom: none; }
.rv-table tbody .rv-row:hover { background: rgba(109,92,255,.06); }
.rv-table tbody td { padding: 11px 16px; vertical-align: middle; font-size: .84rem; color: rgba(255,255,255,.8); }

/* ── Order / booster links ── */
.rv-order-link { color: rgba(255,255,255,.5); text-decoration: none; font-size: .78rem; font-weight: 700; }
.rv-order-link:hover { color: #c4b5fd; }
.rv-booster-link { color: #c4b5fd; text-decoration: none; font-weight: 700; font-size: .82rem; }
.rv-booster-link:hover { color: #fff; text-decoration: underline; }

/* ── Partner cell (avatar + name) ── */
.rv-partner {
  display: inline-flex; align-items: center; gap: .5rem;
  max-width: 190px; text-decoration: none;
}
.rv-partner > span {
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.rv-partner img {
  width: 26px; height: 26px; border-radius: 50%;
  object-fit: cover; flex: 0 0 auto;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.08);
}

/* ── Rating chips (2×2 grid) ── */
.rv-rating-group {
  display: grid;
  grid-template-columns: repeat(2, minmax(0,1fr));
  gap: .4rem .5rem;
  max-width: 170px;
}
.rv-rchip {
  display: inline-flex; align-items: center; justify-content: center;
  gap: .3rem; height: 28px; padding: 0 .6rem;
  border-radius: 99px;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04);
  font-size: .78rem; white-space: nowrap; color: rgba(255,255,255,.8);
}
.rv-rchip .rv-rkey  { font-size: .68rem; letter-spacing: .04em; text-transform: uppercase; opacity: .7; }
.rv-rchip .rv-rval  { font-weight: 800; }
.rv-rchip i         { font-size: .74rem; opacity: .9; }
.rv-rchip.is-good   { background: rgba(0,201,167,.12);   border-color: rgba(0,201,167,.28); }
.rv-rchip.is-mid    { background: rgba(245,202,153,.12); border-color: rgba(245,202,153,.28); }
.rv-rchip.is-bad    { background: rgba(237,76,120,.12);  border-color: rgba(237,76,120,.28); }

/* ── Highlight chips ── */
.rv-hl-wrap { display: flex; flex-wrap: wrap; gap: .35rem; }
.rv-hl-chip {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: .25rem .65rem; border-radius: 99px;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.75); font-size: .78rem; white-space: nowrap;
}
.rv-hl-chip::before {
  content: ""; width: 5px; height: 5px; border-radius: 99px;
  background: #6d5cff; opacity: .9; flex-shrink: 0;
}

/* ── Status badges ── */
.rv-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .22rem .65rem; border-radius: 20px;
  font-size: .73rem; font-weight: 700; white-space: nowrap;
}
.rv-badge-approved { background: rgba(0,201,167,.12);   color: #00c9a7; border: 1px solid rgba(0,201,167,.28); }
.rv-badge-pending  { background: rgba(245,202,153,.12); color: #f5ca99; border: 1px solid rgba(245,202,153,.28); }

/* ── Review text clamp ── */
.rv-review-text {
  max-width: 480px; white-space: normal; overflow: hidden;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
  font-size: .82rem; color: rgba(255,255,255,.65); line-height: 1.5;
}
.rv-review-text.expanded { -webkit-line-clamp: unset; }
.rv-toggle {
  padding: 0; border: 0; background: transparent;
  color: #9b8bf0; font-weight: 700; font-size: .78rem; cursor: pointer;
}
.rv-toggle:hover { text-decoration: underline; }

/* ── Date ── */
.rv-date { font-size: .78rem; color: rgba(255,255,255,.38); font-variant-numeric: tabular-nums; }

/* ── Action buttons ── */
.rv-act-btn {
  width: 32px; height: 32px; border-radius: 8px;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: .78rem; cursor: pointer; transition: background .12s, border-color .12s;
}
.rv-act-approve { border: 1px solid rgba(0,201,167,.2);   background: rgba(0,201,167,.07);   color: #00c9a7; }
.rv-act-approve:hover { background: rgba(0,201,167,.18); border-color: rgba(0,201,167,.4); }
.rv-act-hide    { border: 1px solid rgba(245,202,153,.2); background: rgba(245,202,153,.07); color: #f5ca99; }
.rv-act-hide:hover { background: rgba(245,202,153,.18); border-color: rgba(245,202,153,.4); }
.rv-act-delete  { border: 1px solid rgba(237,76,120,.2);  background: rgba(237,76,120,.07);  color: #ed4c78; }
.rv-act-delete:hover { background: rgba(237,76,120,.18); border-color: rgba(237,76,120,.4); }

/* ── Empty state ── */
.rv-empty { text-align: center; padding: 64px 24px; color: rgba(255,255,255,.35); }
.rv-empty i { font-size: 2.8rem; margin-bottom: 12px; display: block; opacity: .3; }

/* ── Footer / pagination ── */
.rv-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 14px;
  padding: 16px 20px;
}
.rv-footer-left,
.rv-footer-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.rv-entries-box,
.rv-page-summary,
.rv-page-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 40px;
  padding: 6px 12px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.035);
}
.rv-footer-label,
.rv-footer-info {
  font-size: .72rem;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: rgba(255,255,255,.4);
  font-weight: 800;
}
.rv-page-summary {
  color: rgba(255,255,255,.8);
  font-size: .82rem;
  font-weight: 700;
}
.rv-page-summary strong,
.rv-page-chip strong { color: rgba(255,255,255,.96); }
.rv-page-chip {
  color: #c4b5fd;
  font-size: .8rem;
  font-weight: 800;
  border-color: rgba(109,92,255,.22);
  background: rgba(109,92,255,.10);
}
.rv-pagination {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.rv-pg-btn {
  min-width: 38px;
  height: 38px;
  padding: 0 12px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.74);
  font-size: .82rem;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  cursor: pointer;
  transition: background .12s, border-color .12s, transform .12s;
  user-select: none;
}
.rv-pg-btn { text-decoration: none; }
.rv-pg-btn:hover:not(.rv-pg-disabled) {
  text-decoration: none;
  background: rgba(255,255,255,.09);
  border-color: rgba(255,255,255,.16);
  transform: translateY(-1px);
}
.rv-pg-btn.rv-pg-active {
  background: rgba(109,92,255,.22);
  border-color: rgba(109,92,255,.40);
  color: #c4b5fd;
  box-shadow: inset 0 0 0 1px rgba(109,92,255,.12);
}
.rv-pg-btn.rv-pg-disabled {
  opacity: .35;
  cursor: not-allowed;
  pointer-events: none;
}
.rv-pg-btn.rv-pg-nav {
  padding-inline: 14px;
  background: rgba(255,255,255,.055);
}
.rv-pg-btn.rv-pg-nav span {
  font-size: .76rem;
  letter-spacing: .03em;
  text-transform: uppercase;
}
.rv-pg-ellipsis {
  color: rgba(255,255,255,.3);
  font-size: .85rem;
  padding: 0 2px;
  line-height: 38px;
  user-select: none;
}

/* ── Modal ── */
.rv-modal {
  position: fixed; inset: 0; z-index: 9999;
  display: none; align-items: center; justify-content: center; padding: 1rem;
}
.rv-modal.is-open { display: flex; }
.rv-modal__backdrop {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
}
.rv-modal__panel {
  position: relative; width: min(500px, 100%);
  border-radius: 18px; background: rgba(20,22,30,.96);
  border: 1px solid rgba(255,255,255,.10);
  box-shadow: 0 20px 60px rgba(0,0,0,.50); overflow: hidden;
}
.rv-modal__header {
  padding: 1rem 1.25rem .5rem;
  display: flex; gap: .75rem; align-items: flex-start;
}
.rv-modal__icon {
  width: 38px; height: 38px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); flex: 0 0 auto;
}
.rv-modal__title { margin: 0; font-size: 1.05rem; font-weight: 800; color: rgba(255,255,255,.92); }
.rv-modal__body  { padding: .25rem 1.25rem 1rem; color: rgba(255,255,255,.68); line-height: 1.5; font-size: .9rem; }
.rv-modal__footer {
  padding: .85rem 1.25rem 1.1rem;
  display: flex; gap: .6rem; justify-content: flex-end;
  border-top: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.02);
}
.rv-modal__btn {
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.06); color: rgba(255,255,255,.88);
  padding: .5rem 1rem; border-radius: 12px; font-weight: 700; cursor: pointer;
  transition: background .15s;
}
.rv-modal__btn:hover { background: rgba(255,255,255,.11); }
.rv-modal__btn--primary { background: rgba(109,92,255,.20); border-color: rgba(109,92,255,.40); }
.rv-modal__btn--primary:hover { background: rgba(109,92,255,.32); }
.rv-modal__btn--danger  { background: rgba(237,76,120,.16); border-color: rgba(237,76,120,.35); color: #ed4c78; }
.rv-modal__btn--danger:hover  { background: rgba(237,76,120,.25); }
.rv-modal__close {
  position: absolute; top: 12px; right: 12px; width: 34px; height: 34px;
  border-radius: 10px; border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.06); color: rgba(255,255,255,.75);
  display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.rv-modal__close:hover { background: rgba(255,255,255,.12); }

/* tom-select entries select */
.rv-entries-select {
  border-radius: 8px !important; color: rgba(255,255,255,.75) !important;
  padding: 4px 28px 4px 10px !important; font-size: .8rem !important; cursor: pointer;
  -webkit-appearance: none; appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='rgba(255,255,255,.3)'/%3E%3C/svg%3E") !important;
  background-repeat: no-repeat !important; background-position: right 8px center !important;
}
.rv-entries-select option { background: #1e2124; color: #fff; }

/* Fix TomSelect dark styling inside pagination footer */
.rv-entries-box .ts-wrapper,
.rv-footer .ts-wrapper {
  min-width: 82px;
}

.rv-entries-box .ts-control,
.rv-footer .ts-control {
  min-height: 34px !important;
  border-radius: 9px !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  background: rgba(255,255,255,.045) !important;
  color: rgba(255,255,255,.86) !important;
  box-shadow: none !important;
  padding: 6px 30px 6px 11px !important;
}

.rv-entries-box .ts-control input,
.rv-footer .ts-control input {
  color: rgba(255,255,255,.86) !important;
}

.rv-entries-box .ts-wrapper.single .ts-control:after,
.rv-footer .ts-wrapper.single .ts-control:after {
  border-color: rgba(255,255,255,.45) transparent transparent transparent !important;
}

.rv-entries-box .ts-dropdown,
.rv-footer .ts-dropdown {
  border-radius: 10px !important;
  border: 1px solid rgba(255,255,255,.10) !important;
  background: #25282a !important;
  color: rgba(255,255,255,.86) !important;
  box-shadow: 0 12px 28px rgba(0,0,0,.35) !important;
  overflow: hidden;
}

.rv-entries-box .ts-dropdown .option,
.rv-footer .ts-dropdown .option {
  color: rgba(255,255,255,.78) !important;
  background: transparent !important;
  padding: 8px 11px !important;
}

.rv-entries-box .ts-dropdown .option:hover,
.rv-entries-box .ts-dropdown .active,
.rv-footer .ts-dropdown .option:hover,
.rv-footer .ts-dropdown .active {
  background: rgba(109,92,255,.18) !important;
  color: #c4b5fd !important;
}


@media only screen and (max-width:1200px) {
  .rv-table-wrap { overflow-x: auto; }
  .rv-table { min-width: 980px; }
}

@media only screen and (max-width:768px) {
  .rv-footer,
  .rv-footer-left,
  .rv-footer-right {
    align-items: stretch;
  }
  .rv-footer-left,
  .rv-footer-right {
    width: 100%;
    justify-content: space-between;
  }
  .rv-page-summary,
  .rv-page-chip,
  .rv-entries-box {
    width: 100%;
    justify-content: space-between;
  }
  .rv-pagination {
    width: 100%;
  }
  .rv-pg-btn.rv-pg-nav {
    flex: 1 1 auto;
    justify-content: center;
  }
}
</style>
<?= $this->end() ?>

<div class="al-page">

  <!-- ── Hero ── -->
  <div class="rv-hero">
    <div class="rv-hero-left">
      <div class="rv-hero-icon"><i class="fa-duotone fa-star"></i></div>
      <div>
        <h2 class="rv-hero-title">Review Management</h2>
        <p class="rv-hero-sub">Auto publish reviews, low ratings stay for admin review</p>
      </div>
    </div>
    <?php
      // Filtering, sorting and paging are done in SQL by the route — this page only
      // renders the current page of rows.
      $stats   = $stats   ?? ['total' => 0, 'approved' => 0, 'pending' => 0];
      $filters = $filters ?? [];
      $fStatus = (string)($filters['status'] ?? 'pending');
      $fType   = (string)($filters['type'] ?? '');
      $fSearch = (string)($filters['q'] ?? '');
      $fPage   = (int)($filters['page'] ?? 1);
      $fPages  = (int)($filters['total_pages'] ?? 1);
      $fTotal  = (int)($filters['total_rows'] ?? 0);
      $fPer    = (int)($filters['per_page'] ?? 25);
      $fCounts = (array)($filters['counts'] ?? []);

      $rvUrl = function (array $overrides = []) use ($fStatus, $fType, $fSearch, $fPage) {
          $params = array_merge([
              'status' => $fStatus,
              'type'   => $fType,
              'q'      => $fSearch,
              'page'   => $fPage,
          ], $overrides);
          $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
          return ADMN_URL . '/reviews' . (empty($params) ? '' : ('?' . http_build_query($params)));
      };
    ?>
    <div class="rv-stat-row">
      <div class="rv-stat"><i class="fa-solid fa-list" style="color:#6d5cff;"></i> Total: <strong><?= (int)$stats['total'] ?></strong></div>
      <div class="rv-stat"><i class="fa-solid fa-check" style="color:#00c9a7;"></i> Approved: <strong><?= (int)$stats['approved'] ?></strong></div>
      <div class="rv-stat"><i class="fa-solid fa-clock" style="color:#f5ca99;"></i> Pending: <strong><?= (int)$stats['pending'] ?></strong></div>
    </div>
  </div>

  <!-- ── Toolbar ── -->
  <div id="rvContent">

  <div class="rv-toolbar">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1;">
      <div class="rv-pills" id="rvStatusPills">
        <a class="rv-pill <?= $fStatus === 'any' ? 'active' : '' ?>" data-status="" href="<?= htmlspecialchars($rvUrl(['status' => 'any', 'page' => 1]), ENT_QUOTES) ?>">Any</a>
        <a class="rv-pill <?= $fStatus === 'pending' ? 'active' : '' ?>" data-status="Pending" href="<?= htmlspecialchars($rvUrl(['status' => 'pending', 'page' => 1]), ENT_QUOTES) ?>"><i class="fa-solid fa-clock" style="font-size:.65rem;"></i> Pending</a>
        <a class="rv-pill <?= $fStatus === 'approved' ? 'active' : '' ?>" data-status="Approved" href="<?= htmlspecialchars($rvUrl(['status' => 'approved', 'page' => 1]), ENT_QUOTES) ?>"><i class="fa-solid fa-check" style="font-size:.65rem;"></i> Approved</a>
      </div>

      <div class="rv-pills" id="rvSourcePills">
        <a class="rv-pill <?= $fType === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($rvUrl(['type' => '', 'page' => 1]), ENT_QUOTES) ?>">All types</a>
        <?php foreach (['boost' => 'Boosting', 'seller' => 'Seller', 'egirl' => 'GG-Girl'] as $srcKey => $srcLabel): ?>
          <a class="rv-pill <?= $fType === $srcKey ? 'active' : '' ?>" href="<?= htmlspecialchars($rvUrl(['type' => $srcKey, 'page' => 1]), ENT_QUOTES) ?>">
            <?= $srcLabel ?> <?= (int)($fCounts[$srcKey] ?? 0) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <form class="rv-search-wrap" method="get" action="<?= ADMN_URL ?>/reviews">
      <input type="hidden" name="status" value="<?= htmlspecialchars($fStatus, ENT_QUOTES) ?>">
      <?php if ($fType !== ''): ?><input type="hidden" name="type" value="<?= htmlspecialchars($fType, ENT_QUOTES) ?>"><?php endif; ?>
      <i class="fa-solid fa-magnifying-glass rv-search-icon"></i>
      <input name="q" type="search" placeholder="Search reviews…" aria-label="Search reviews" value="<?= htmlspecialchars($fSearch, ENT_QUOTES) ?>">
    </form>
  </div>

  <!-- ── Table ── -->
  <div class="rv-table-wrap">
    <?php // No DataTables here on purpose: search, sort and paging run in SQL. ?>
    <table class="rv-table" id="invoices_table">

      <thead>
        <tr>
          <th>Type</th>
          <th>Reference</th>
          <th>Partner</th>
          <th>Ratings</th>
          <th>Highlights</th>
          <th>Review</th>
          <th>Date</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($data)): ?>
          <tr><td colspan="9">
            <div class="rv-empty">
              <i class="fa-duotone fa-star-slash"></i>
              <p class="mb-0" style="font-size:.88rem;">No reviews found</p>
            </div>
          </td></tr>
        <?php endif; ?>
        <?php foreach ($data as $row): ?>
          <?php
            $highlights = [];
            if (!empty($row['highlights'])) {
              $decoded = json_decode($row['highlights'], true);
              if (is_array($decoded)) $highlights = $decoded;
            }

            $reviewText  = trim((string)($row['comments'] ?? ''));
            $createdAt   = (string)($row['created_at'] ?? '');
            $createdAtTs = $createdAt ? strtotime($createdAt) : null;
            $isApproved  = ((int)($row['approved'] ?? 0) === 1);
            $rowKey      = (string)($row['row_key'] ?? '');
            $source      = (string)($row['source'] ?? '');

            $comm    = (int)($row['communication'] ?? 0);
            $skill   = (int)($row['skill'] ?? 0);
            $speed   = (int)($row['speed'] ?? 0);
            $overall = (int)($row['overall'] ?? 0);

            $tone = fn($v) => $v >= 4 ? 'is-good' : ($v == 3 ? 'is-mid' : 'is-bad');
            $enc  = fn($payload) => htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8');
          ?>

          <tr class="rv-row" data-review-row="<?= htmlspecialchars($rowKey, ENT_QUOTES) ?>" data-review-source="<?= htmlspecialchars($source, ENT_QUOTES) ?>">

            <td>
              <span class="rv-hl-chip"><i class="fa-duotone <?= htmlspecialchars((string)($row['source_icon'] ?? 'fa-star'), ENT_QUOTES) ?>"></i> <?= htmlspecialchars((string)($row['source_label'] ?? ''), ENT_QUOTES) ?></span>
            </td>

            <td>
              <?php if (!empty($row['ref_url'])): ?>
                <a href="<?= htmlspecialchars((string)$row['ref_url'], ENT_QUOTES) ?>" class="rv-order-link"><?= htmlspecialchars((string)($row['ref_label'] ?? ''), ENT_QUOTES) ?></a>
              <?php else: ?>
                <span style="color:rgba(255,255,255,.45);font-size:.82rem;"><?= htmlspecialchars((string)($row['ref_label'] ?? '—'), ENT_QUOTES) ?></span>
              <?php endif; ?>
            </td>

            <td>
              <?php
                $partnerIcon = trim((string)($row['partner_icon'] ?? ''));
                $partnerName = (string)($row['partner_name'] ?? '—');
                $partnerUrl  = trim((string)($row['partner_url'] ?? ''));
              ?>
              <?php if ($partnerUrl !== ''): ?><a class="rv-partner rv-booster-link" href="<?= htmlspecialchars($partnerUrl, ENT_QUOTES) ?>"><?php else: ?><span class="rv-partner" style="color:rgba(255,255,255,.45);"><?php endif; ?>
                <?php if ($partnerIcon !== ''): ?>
                  <img src="<?= htmlspecialchars($partnerIcon, ENT_QUOTES) ?>" alt="" loading="lazy" onerror="this.style.display='none'">
                <?php endif; ?>
                <span><?= htmlspecialchars($partnerName, ENT_QUOTES) ?></span>
              <?php if ($partnerUrl !== ''): ?></a><?php else: ?></span><?php endif; ?>
            </td>

            <td>
              <div class="rv-rating-group">
                <?php if ($source === 'boost'): ?>
                  <span class="rv-rchip <?= $tone($comm) ?>"  title="Communication"><span class="rv-rkey">C</span><span class="rv-rval"><?= $comm ?></span></span>
                  <span class="rv-rchip <?= $tone($skill) ?>" title="Skill"><span class="rv-rkey">S</span><span class="rv-rval"><?= $skill ?></span></span>
                  <span class="rv-rchip <?= $tone($speed) ?>" title="Speed"><span class="rv-rkey">Sp</span><span class="rv-rval"><?= $speed ?></span></span>
                <?php endif; ?>
                <span class="rv-rchip <?= $tone($overall) ?>" title="Overall"><i class="fas fa-star"></i><span class="rv-rval"><?= $overall ?></span></span>
              </div>
            </td>

            <td>
              <?php if (!empty($highlights)): ?>
                <div class="rv-hl-wrap">
                  <?php foreach ($highlights as $hl): ?>
                    <span class="rv-hl-chip"><?= htmlspecialchars(ucwords(str_replace('-', ' ', (string)$hl))) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <span style="color:rgba(255,255,255,.25);font-size:.8rem;">—</span>
              <?php endif; ?>
            </td>

            <td>
              <?php if ($reviewText !== ''): ?>
                <div class="rv-review-text" data-review-text><?= nl2br(htmlspecialchars($reviewText)) ?></div>
                <button type="button" class="rv-toggle mt-1 js-toggle-review">Show more</button>
              <?php else: ?>
                <span style="color:rgba(255,255,255,.25);font-size:.8rem;">—</span>
              <?php endif; ?>
            </td>

            <td data-order="<?= $createdAtTs ?: 0 ?>">
              <span class="rv-date"><?= $createdAtTs ? date('d.m.Y H:i', $createdAtTs) : '—' ?></span>
            </td>

            <td>
              <?php if ($isApproved): ?>
                <span class="rv-badge rv-badge-approved"><i class="fas fa-check" style="font-size:.65rem;"></i> Approved</span>
              <?php else: ?>
                <span class="rv-badge rv-badge-pending"><i class="fas fa-clock" style="font-size:.65rem;"></i> Pending</span>
              <?php endif; ?>
            </td>

            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;align-items:center;">
                <?php // Each source has its own endpoints, so the payload travels with the button. ?>
                <?php if (!$isApproved): ?>
                  <button type="button" class="rv-act-btn rv-act-approve js-review-action"
                          data-row="<?= htmlspecialchars($rowKey, ENT_QUOTES) ?>"
                          data-next="approved"
                          data-post="<?= $enc($row['act_approve'] ?? []) ?>"
                          data-post-approve="<?= $enc($row['act_approve'] ?? []) ?>"
                          data-post-hide="<?= $enc($row['act_hide'] ?? []) ?>"
                          title="Approve">
                    <i class="fas fa-check"></i>
                  </button>
                <?php else: ?>
                  <button type="button" class="rv-act-btn rv-act-hide js-review-action"
                          data-row="<?= htmlspecialchars($rowKey, ENT_QUOTES) ?>"
                          data-next="pending"
                          data-post="<?= $enc($row['act_hide'] ?? []) ?>"
                          data-post-approve="<?= $enc($row['act_approve'] ?? []) ?>"
                          data-post-hide="<?= $enc($row['act_hide'] ?? []) ?>"
                          title="Hide">
                    <i class="fas fa-eye-slash"></i>
                  </button>
                <?php endif; ?>
                <button type="button" class="rv-act-btn rv-act-delete js-delete-review"
                        data-row="<?= htmlspecialchars($rowKey, ENT_QUOTES) ?>"
                        data-post="<?= $enc($row['act_delete'] ?? []) ?>"
                        title="Delete permanently">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Empty state (hidden by default, DataTables handles zeroRecords) -->
  </div>

  <!-- ── Footer ── -->
  <?php
    $fFrom = $fTotal > 0 ? (($fPage - 1) * $fPer + 1) : 0;
    $fTo   = min($fPage * $fPer, $fTotal);

    // Page numbers with ellipsis around the current page.
    $pageList = [];
    for ($i = 1; $i <= $fPages; $i++) {
        if ($i === 1 || $i === $fPages || ($i >= $fPage - 1 && $i <= $fPage + 1)) $pageList[] = $i;
    }
  ?>
  <div class="rv-footer">
    <div class="rv-footer-left">
      <div class="rv-page-summary">
        <?php if ($fTotal === 0): ?>
          No reviews found
        <?php else: ?>
          Showing <strong><?= $fFrom ?>–<?= $fTo ?></strong> of <strong><?= $fTotal ?></strong> reviews
        <?php endif; ?>
      </div>
    </div>
    <div class="rv-footer-right">
      <div class="rv-page-chip">Page <strong><?= $fTotal > 0 ? $fPage : 0 ?> / <?= $fTotal > 0 ? $fPages : 0 ?></strong></div>
      <?php if ($fPages > 1): ?>
        <div class="rv-pagination">
          <?php if ($fPage > 1): ?>
            <a class="rv-pg-btn rv-pg-nav" href="<?= htmlspecialchars($rvUrl(['page' => $fPage - 1]), ENT_QUOTES) ?>"><i class="fas fa-chevron-left" style="font-size:.65rem;"></i><span>Prev</span></a>
          <?php else: ?>
            <span class="rv-pg-btn rv-pg-nav rv-pg-disabled"><i class="fas fa-chevron-left" style="font-size:.65rem;"></i><span>Prev</span></span>
          <?php endif; ?>

          <?php $prevPage = null; foreach ($pageList as $p): ?>
            <?php if ($prevPage !== null && $p - $prevPage > 1): ?><span class="rv-pg-ellipsis">…</span><?php endif; ?>
            <?php if ($p === $fPage): ?>
              <span class="rv-pg-btn rv-pg-active"><?= $p ?></span>
            <?php else: ?>
              <a class="rv-pg-btn" href="<?= htmlspecialchars($rvUrl(['page' => $p]), ENT_QUOTES) ?>"><?= $p ?></a>
            <?php endif; ?>
            <?php $prevPage = $p; ?>
          <?php endforeach; ?>

          <?php if ($fPage < $fPages): ?>
            <a class="rv-pg-btn rv-pg-nav" href="<?= htmlspecialchars($rvUrl(['page' => $fPage + 1]), ENT_QUOTES) ?>"><span>Next</span><i class="fas fa-chevron-right" style="font-size:.65rem;"></i></a>
          <?php else: ?>
            <span class="rv-pg-btn rv-pg-nav rv-pg-disabled"><span>Next</span><i class="fas fa-chevron-right" style="font-size:.65rem;"></i></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  </div><!-- /#rvContent -->

</div><!-- /.al-page -->

<!-- ── Modal ── -->
<div class="rv-modal" id="reviewsModal" aria-hidden="true">
  <div class="rv-modal__backdrop" data-modal-close></div>
  <div class="rv-modal__panel" role="dialog" aria-modal="true" aria-labelledby="reviewsModalTitle">
    <button type="button" class="rv-modal__close" title="Close" data-modal-close>
      <i class="fas fa-times"></i>
    </button>
    <div class="rv-modal__header">
      <div class="rv-modal__icon" id="reviewsModalIcon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div>
        <h3 class="rv-modal__title" id="reviewsModalTitle">Modal title</h3>
      </div>
    </div>
    <div class="rv-modal__body" id="reviewsModalBody">Modal body</div>
    <div class="rv-modal__footer" id="reviewsModalFooter">
      <button type="button" class="rv-modal__btn" data-modal-cancel>Cancel</button>
      <button type="button" class="rv-modal__btn rv-modal__btn--danger" data-modal-confirm>Delete</button>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<?php // tom-select is no longer needed: the rows-per-page dropdown was replaced by server-side paging. ?>

<script>
  $(function () {
    // Search, sorting, filtering and paging are all handled server-side by the route,
    // so this page no longer boots DataTables. Only row actions stay client-side.

    // ── Filters without changing the URL ──
    // Pills, pagination and search are plain links/forms so they work without JS,
    // but here we fetch the target page and swap #rvContent instead of navigating.
    // The address bar keeps showing /admin-area/reviews.
    var $content = $('#rvContent');

    function loadFiltered(url) {
      if (!url || $content.hasClass('is-loading')) return;
      $content.addClass('is-loading').css('opacity', .5);
      $.get(url)
        .done(function (html) {
          var fresh = $('<div>').append($.parseHTML(html)).find('#rvContent');
          if (fresh.length) {
            $content.replaceWith(fresh);
            $content = fresh;
            // The header counters live outside #rvContent, refresh them too.
            var stats = $('<div>').append($.parseHTML(html)).find('.rv-stat-row');
            if (stats.length) $('.rv-stat-row').replaceWith(stats);
          } else {
            window.location.href = url;
          }
        })
        .fail(function () { window.location.href = url; })
        .always(function () { $content.removeClass('is-loading').css('opacity', ''); });
    }

    $(document).on('click', '#rvContent .rv-pill, #rvContent .rv-pagination a.rv-pg-btn', function (e) {
      var href = $(this).attr('href');
      if (!href) return;
      e.preventDefault();
      loadFiltered(href);
    });

    var searchTimer = null;
    $(document).on('submit', '#rvContent .rv-search-wrap', function (e) {
      e.preventDefault();
      loadFiltered($(this).attr('action') + '?' + $(this).serialize());
    });
    $(document).on('input', '#rvContent .rv-search-wrap input[name="q"]', function () {
      var $form = $(this).closest('form');
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () {
        loadFiltered($form.attr('action') + '?' + $form.serialize());
      }, 400);
    });

    // ── Modal helpers ──
    const $modal   = $('#reviewsModal');
    const $title   = $('#reviewsModalTitle');
    const $body    = $('#reviewsModalBody');
    const $icon    = $('#reviewsModalIcon');
    const $btnCancel  = $modal.find('[data-modal-cancel]');
    const $btnConfirm = $modal.find('[data-modal-confirm]');
    let modalConfirmHandler = null;
    let modalCancelHandler  = null;

    function openModal(opts) {
      const o = opts || {};
      $title.text(o.title || 'Notice');
      $body.html(o.body || '');
      $icon.html(o.iconHtml || '<i class="fas fa-exclamation-triangle"></i>');
      $btnCancel.text(o.cancelText || 'Cancel').toggle(!!o.showCancel);
      $btnConfirm.text(o.confirmText || 'OK');
      $btnConfirm.removeClass('rv-modal__btn--danger rv-modal__btn--primary');
      if (o.confirmVariant === 'danger')   $btnConfirm.addClass('rv-modal__btn--danger');
      if (o.confirmVariant === 'primary')  $btnConfirm.addClass('rv-modal__btn--primary');
      modalConfirmHandler = typeof o.onConfirm === 'function' ? o.onConfirm : null;
      modalCancelHandler  = typeof o.onCancel  === 'function' ? o.onCancel  : null;
      $modal.addClass('is-open').attr('aria-hidden', 'false');
    }

    function closeModal() {
      $modal.removeClass('is-open').attr('aria-hidden', 'true');
      modalConfirmHandler = null;
      modalCancelHandler  = null;
    }

    $modal.on('click', '[data-modal-close]', function () {
      if (modalCancelHandler) modalCancelHandler();
      closeModal();
    });
    $btnCancel.on('click',  function () { if (modalCancelHandler) modalCancelHandler(); closeModal(); });
    $btnConfirm.on('click', function () { const fn = modalConfirmHandler; closeModal(); if (fn) fn(); });

    // ── Notifications ──
    function notify(res, fallbackMessage) {
      if (res && res.sendToast && typeof window.sendToast === 'function') {
        window.sendToast(res.sendToast);
        if (res.playSound && typeof window.playSound === 'function') window.playSound(res.playSound);
        return;
      }
      const msg = (res && res.message) ? res.message : (fallbackMessage || 'Request completed.');
      openModal({ title: 'Success', body: msg, iconHtml: '<i class="fas fa-check"></i>', showCancel: false, confirmText: 'OK', confirmVariant: 'primary' });
    }

    function notifyError(message) {
      openModal({ title: 'Error', body: message || 'Something went wrong. Please try again.', iconHtml: '<i class="fas fa-times"></i>', showCancel: false, confirmText: 'OK', confirmVariant: 'primary' });
    }

    // ── Robust AJAX ──
    const AJAX_ENDPOINTS = ['<?= BASE_URL ?>/ajax', '<?= ADMN_URL ?>/ajax', '/ajax'];

    function extractJson(text) {
      if (!text) return null;
      try { return JSON.parse(text); } catch (e) {}
      const start = text.indexOf('{'), end = text.lastIndexOf('}');
      if (start !== -1 && end > start) {
        try { return JSON.parse(text.slice(start, end + 1)); } catch (e) {}
      }
      return null;
    }

    function postAjax(payload) {
      let i = 0;
      return new Promise(function (resolve, reject) {
        function tryNext() {
          if (i >= AJAX_ENDPOINTS.length) return reject(new Error('All AJAX endpoints failed'));
          $.ajax({
            url: AJAX_ENDPOINTS[i++], method: 'POST', dataType: 'text', data: payload,
            success: function (text) { resolve(extractJson(text) || { success: true, message: (text || '').trim() }); },
            error: function () { tryNext(); }
          });
        }
        tryNext();
      });
    }

    // ── Review text toggle ──
    $(document).on('click', '.js-toggle-review', function () {
      const $btn  = $(this);
      const $text = $btn.closest('td').find('[data-review-text]');
      const expanded = $text.hasClass('expanded');
      $text.toggleClass('expanded', !expanded);
      $btn.text(expanded ? 'Show more' : 'Show less');
    });

    // ── Approve / Hide ──
    $(document).on('click', '.js-review-action', function (e) {
      e.preventDefault();
      const $btn = $(this);
      // Boosting, seller and GG-Girl reviews use different endpoints, so the payload
      // is rendered onto the button instead of being reconstructed here.
      const payload = $btn.data('post');
      const rowKey  = $btn.data('row');
      const next    = $btn.data('next');
      if (!payload || !rowKey) return;

      postAjax(payload)
        .then(function (res) {
          const $row        = $('tr[data-review-row="' + rowKey + '"]');
          const $statusCell = $row.find('td').eq(7);

          if (next === 'approved') {
            $statusCell.html('<span class="rv-badge rv-badge-approved"><i class="fas fa-check" style="font-size:.65rem;"></i> Approved</span>');
            $btn.removeClass('rv-act-approve').addClass('rv-act-hide')
                .attr('title', 'Hide')
                .data('next', 'pending')
                .data('post', $btn.data('post-hide'))
                .find('i').attr('class', 'fas fa-eye-slash');
          } else {
            $statusCell.html('<span class="rv-badge rv-badge-pending"><i class="fas fa-clock" style="font-size:.65rem;"></i> Pending</span>');
            $btn.removeClass('rv-act-hide').addClass('rv-act-approve')
                .attr('title', 'Approve')
                .data('next', 'approved')
                .data('post', $btn.data('post-approve'))
                .find('i').attr('class', 'fas fa-check');
          }

          // The row no longer matches the active status filter — drop it from the
          // current page instead of leaving a stale entry behind.
          const active = $('.rv-pill[data-status].active').data('status') || '';
          if (active && active !== (next === 'approved' ? 'Approved' : 'Pending')) {
            $row.fadeOut(150, function () { $(this).remove(); });
          }
          notify(res, 'Updated.');
        })
        .catch(function () { notifyError('Could not update the review. Please try again.'); });
    });

    // ── Delete ──
    $(document).on('click', '.js-delete-review', function (e) {
      e.preventDefault();
      const payload = $(this).data('post');
      const rowKey  = $(this).data('row');
      if (!payload || !rowKey) return;

      openModal({
        title: 'Delete review?',
        body: 'This will permanently delete the review. This action can\'t be undone.',
        iconHtml: '<i class="fas fa-trash"></i>',
        showCancel: true, cancelText: 'Cancel', confirmText: 'Delete', confirmVariant: 'danger',
        onConfirm: function () {
          postAjax(payload)
            .then(function (res) {
              if (res && res.success === false) {
                notifyError(res.message || 'Could not delete the review. Please try again.');
                return;
              }
              $('tr[data-review-row="' + rowKey + '"]').remove();

              if (res && res.sendToast && typeof window.sendToast === 'function') {
                window.sendToast(res.sendToast);
                if (res.playSound && typeof window.playSound === 'function') window.playSound(res.playSound);
              } else {
                notify({ message: 'Review deleted permanently.' }, 'Review deleted permanently.');
              }
            })
            .catch(function () { notifyError('Could not delete the review. Please try again.'); });
        }
      });
    });
  });
</script>
<?= $this->end() ?>
