<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Selling Smurfs | Admin Area']]) ?>

<?php
$data = $data ?? [];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
?>

<?= $this->start('styles') ?>
<style>
/* ── Search ── */
.al-search-wrap{position:relative;}
.al-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.al-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}

/* ── Table ── */
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);}
.al-table{width:100%;border-collapse:collapse;display:table;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;user-select:none;}
.al-table thead th.sortable{cursor:pointer;}
.al-table thead th.sortable:hover{color:rgba(255,255,255,.7);}
.al-table thead th .sort-icon{margin-left:4px;opacity:.35;font-size:.6rem;}
.al-table thead th.sort-asc .sort-icon,.al-table thead th.sort-desc .sort-icon{opacity:1;color:#c4b5fd;}
.al-table tbody .al-row{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;cursor:pointer;}
.al-table tbody .al-row:last-child{border-bottom:none;}
.al-table tbody .al-row:hover{background:rgba(109,92,255,.08);}
.al-table tbody td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}

/* ── Cols ── */
.al-col-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums;}
.al-col-price{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums;}
.al-col-date{font-size:.78rem;color:rgba(255,255,255,.38);}
.al-pkg-name{font-size:.87rem;font-weight:800;color:rgba(255,255,255,.9);}
.al-pkg-sub{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px;}

/* ── Credentials ── */
.al-creds-wrap{display:flex;flex-direction:column;gap:3px;}
.al-cred-row{display:inline-flex;align-items:center;gap:5px;font-size:.76rem;color:rgba(255,255,255,.5);}
.al-cred-row i{color:rgba(255,255,255,.25);font-size:.7rem;width:12px;text-align:center;flex-shrink:0;}
.al-cred-val{font-weight:700;color:rgba(255,255,255,.72);font-family:monospace;font-size:.77rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:filter .2s;}
body.al-creds-hidden .al-cred-val{filter:blur(5px);user-select:none;}

/* ── Badge ── */
.al-badge{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:99px;font-size:.71rem;font-weight:800;white-space:nowrap;}
.al-badge--sold{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}

/* ── User links ── */
.al-user-link{color:#4ade80;text-decoration:none;font-weight:700;font-size:.82rem;}
.al-user-link:hover{color:#86efac;text-decoration:underline;}
.al-admin-pill{display:inline-flex;align-items:center;gap:7px;color:#c4b5fd;text-decoration:none;font-weight:850;font-size:.82rem;white-space:nowrap;}
.al-admin-avatar{width:24px;height:24px;border-radius:999px;object-fit:cover;background:rgba(255,255,255,.08);border:1px solid rgba(196,181,253,.25);box-shadow:0 0 0 2px rgba(109,92,255,.08);}
.al-admin-fallback{width:24px;height:24px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:rgba(109,92,255,.16);border:1px solid rgba(196,181,253,.25);color:#c4b5fd;font-size:.72rem;}
.al-pkg-link{color:#c4b5fd;text-decoration:none;font-weight:700;}
.al-pkg-link:hover{color:#fff;text-decoration:underline;}

/* ── View btn ── */
.al-view-btn{display:inline-flex;align-items:center;gap:.35rem;padding:7px 14px;border-radius:9px;font-size:.79rem;font-weight:800;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;transition:background .12s,border-color .12s;text-decoration:none;white-space:nowrap;}
.al-view-btn:hover{background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff;}

/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(251,113,133,.2),rgba(239,68,68,.12));border:1px solid rgba(251,113,133,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fb7185;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}

/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}

/* ── Empty / Pagination ── */
.al-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35);}
.al-empty i{font-size:3rem;margin-bottom:12px;display:block;opacity:.3;}
.al-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;}
.al-pg-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s;}
.al-pg-btn:hover:not(:disabled){background:rgba(255,255,255,.09);}
.al-pg-btn.al-pg-active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pg-btn:disabled{opacity:.35;cursor:not-allowed;}

@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}.al-table{min-width:1050px;}}
</style>
<?= $this->end() ?>


<div class="al-page">

  <!-- Hero -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-helmet-battle"></i></div>
      <div>
        <h2 class="al-hero-title">Selling Smurfs</h2>
        <p class="al-hero-sub"><?= count($data) ?> sold smurf account<?= count($data) !== 1 ? 's' : '' ?></p>
      </div>
    </div>
    <span class="al-badge al-badge--sold"><i class="fa-solid fa-check"></i> Sold only</span>
  </div>

  <!-- Toolbar -->
  <div class="al-toolbar-card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <!-- Credentials toggle -->
      <button id="alCredsToggle"
        onclick="(function(btn){var h=document.body.classList.toggle('al-creds-hidden');btn.innerHTML=h?'<i class=\'fa-solid fa-eye\'></i> Show Creds':'<i class=\'fa-solid fa-eye-slash\'></i> Hide Creds';})(this)"
        style="display:inline-flex;align-items:center;gap:.4rem;padding:5px 12px;border-radius:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.55);font-size:.78rem;font-weight:800;cursor:pointer;white-space:nowrap;">
        <i class="fa-solid fa-eye-slash"></i> Hide Creds
      </button>
    </div>
    <div class="al-search-wrap">
      <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
      <input type="search" id="alSearch" placeholder="Search smurfs…">
    </div>
  </div>

  <!-- Table -->
  <div class="al-table-wrap">
    <table class="al-table" id="alGrid">
      <thead>
        <tr>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Package</th>
          <th>
            <span style="display:inline-flex;align-items:center;gap:6px;">Credentials</span>
          </th>
          <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Status</th>
          <th>Customer</th>
          <th>Uploaded By</th>
          <th class="sortable" data-col="sold">Sold At <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="created">Created At <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="alTbody">
        <?php if (!empty($data)): foreach ($data as $row):
          $id            = (int)($row['id'] ?? 0);
          $packageId     = (int)($row['package_id'] ?? 0);
          $packageName   = $row['package_name'] ?? ($packageId ? 'Package #' . $packageId : '—');
          $soldPrice     = $row['sold_price'] ?? null;
          $packagePrice  = $row['package_price'] ?? null;
          $priceToShow   = ($soldPrice !== null && $soldPrice !== '') ? $soldPrice : $packagePrice;
          $soldCurrency  = strtoupper($row['sold_currency'] ?? 'EUR');
          $clientId      = (int)($row['client_id'] ?? 0);
          $clientUsername= $row['client_username'] ?? '—';
          $adminId       = (int)($row['admin_id'] ?? 0);
          $adminName     = $row['uploaded_by_admin'] ?? ($adminId ? ('Admin #' . $adminId) : '—');
          $adminIcon     = trim((string)($row['uploaded_by_admin_icon'] ?? ''));
          $soldAt        = $row['sold_at'] ?? null;
          $createdAt     = $row['created_at'] ?? null;
          $soldAtTs      = $soldAt   ? strtotime($soldAt)   : 0;
          $createdAtTs   = $createdAt ? strtotime($createdAt) : 0;
          $soldAtFmt     = $soldAtTs   ? date('d.m.Y H:i', $soldAtTs)   : '—';
          $createdAtFmt  = $createdAtTs ? date('d.m.Y H:i', $createdAtTs) : '—';
          $login         = $row['login']    ?? '';
          $password      = $row['password'] ?? '';
          $priceRaw      = (int)($priceToShow ?? 0);
          $searchStr     = strtolower($packageName . ' ' . $clientUsername . ' ' . $adminName . ' ' . $login);
        ?>
        <tr class="al-row"
            data-search="<?= $h($searchStr) ?>"
            data-pkg="<?= $packageId ?>"
            data-id="<?= $id ?>"
            data-price="<?= $priceRaw ?>"
            data-sold="<?= $soldAtTs ?>"
            data-created="<?= $createdAtTs ?>"
            onclick="window.location='<?= ADMN_URL ?>/account/<?= $id ?>'">
          <td><span class="al-col-id">#<?= $id ?></span></td>
          <td>
            <?php if ($packageId): ?>
              <a class="al-pkg-link" href="<?= ADMN_URL ?>/account-package/<?= $packageId ?>" onclick="event.stopPropagation()">
                <?= $h($packageName) ?>
              </a>
            <?php else: ?>
              <span class="al-pkg-name"><?= $h($packageName) ?></span>
            <?php endif; ?>
          </td>
          <td onclick="event.stopPropagation()">
            <?php if ($login || $password): ?>
              <div class="al-creds-wrap">
                <?php if ($login):    ?><div class="al-cred-row"><i class="fa-solid fa-user"></i><span class="al-cred-val"><?= $h($login) ?></span></div><?php endif; ?>
                <?php if ($password): ?><div class="al-cred-row"><i class="fa-solid fa-key"></i><span class="al-cred-val"><?= $h($password) ?></span></div><?php endif; ?>
              </div>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($priceToShow !== null && $priceToShow !== ''): ?>
              <span class="al-col-price">€<?= function_exists('util_format_price_display') ? util_format_price_display($priceToShow) : number_format((float)$priceToShow / 100, 2) ?></span>
              <?php if ($soldCurrency !== 'EUR'): ?><span style="font-size:.7rem;color:rgba(255,255,255,.3);margin-left:3px;"><?= $h($soldCurrency) ?></span><?php endif; ?>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>
          <td><span class="al-badge al-badge--sold"><i class="fa-solid fa-check"></i> Sold</span></td>
          <td onclick="event.stopPropagation()">
            <?php if ($clientId): ?>
              <a class="al-user-link" href="<?= ADMN_URL ?>/client/<?= $clientId ?>">
                <?= $h($clientUsername) ?>
              </a>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>
          <td onclick="event.stopPropagation()">
            <?php if ($adminId || $adminName !== '—'): ?>
              <span class="al-admin-pill" title="Uploaded by <?= $h($adminName) ?>">
                <?php if ($adminIcon !== ''): ?>
                  <img class="al-admin-avatar" src="<?= $h($adminIcon) ?>" alt="<?= $h($adminName) ?>">
                <?php else: ?>
                  <span class="al-admin-fallback"><i class="fa-solid fa-user-shield"></i></span>
                <?php endif; ?>
                <span><?= $h($adminName) ?></span>
              </span>
            <?php else: ?>
              <span style="color:rgba(255,255,255,.2);">—</span>
            <?php endif; ?>
          </td>
          <td><span class="al-col-date"><?= $h($soldAtFmt) ?></span></td>
          <td><span class="al-col-date"><?= $h($createdAtFmt) ?></span></td>
          <td class="text-end" onclick="event.stopPropagation()">
            <a class="al-view-btn" href="<?= ADMN_URL ?>/account/<?= $id ?>">
              <i class="fa-duotone fa-eye"></i> View
            </a>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="10">
          <div class="al-empty">
            <i class="fa-duotone fa-helmet-battle"></i>
            <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No sold smurfs yet</div>
            <div style="font-size:.85rem;">Sold smurf accounts will appear here.</div>
          </div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Footer / Pagination -->
  <div class="al-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
      Showing <span id="alShowing">—</span> of <span id="alTotal">—</span>
    </div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
  </div>

</div>


<?= $this->start('scripts') ?>
<script>
(function () {
  var PER_PAGE = 25;
  var search  = '';
  var page    = 1;
  var sortCol = 'sold';
  var sortDir = 'desc';

  var tbody   = document.getElementById('alTbody');
  var allRows = tbody ? Array.from(tbody.querySelectorAll('.al-row')) : [];
  var showEl  = document.getElementById('alShowing');
  var totEl   = document.getElementById('alTotal');
  var pageEl  = document.getElementById('alPagination');
  var srchEl  = document.getElementById('alSearch');
  var ths     = document.querySelectorAll('.al-table thead th.sortable');

  function getSorted(arr) {
    return arr.slice().sort(function (a, b) {
      var av = a.dataset[sortCol] || '', bv = b.dataset[sortCol] || '';
      var an = parseFloat(av), bn = parseFloat(bv);
      var cmp = (isNaN(an) || isNaN(bn)) ? String(av).localeCompare(String(bv), undefined, {numeric:true}) : an - bn;
      return sortDir === 'asc' ? cmp : -cmp;
    });
  }
  function getFiltered() {
    return allRows.filter(function (c) {
      return !search || (c.dataset.search || '').indexOf(search) !== -1;
    });
  }
  function render() {
    var filtered = getSorted(getFiltered());
    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / PER_PAGE));
    if (page > pages) page = pages;
    var start = (page - 1) * PER_PAGE, end = start + PER_PAGE;

    allRows.forEach(function (c) { c.style.display = 'none'; });
    filtered.slice(start, end).forEach(function (c) { tbody.appendChild(c); c.style.display = ''; });

    if (showEl) showEl.textContent = total > 0 ? (start + 1) + '–' + Math.min(end, total) : '0';
    if (totEl)  totEl.textContent  = total;

    ths.forEach(function (th) {
      th.classList.remove('sort-asc', 'sort-desc');
      if (th.dataset.col === sortCol) th.classList.add('sort-' + sortDir);
    });

    if (!pageEl) return;
    pageEl.innerHTML = '';
    if (pages <= 1) return;
    function btn(label, p, disabled, active) {
      var b = document.createElement('button');
      b.className = 'al-pg-btn' + (active ? ' al-pg-active' : '');
      b.innerHTML = label; b.disabled = !!disabled;
      if (!disabled) b.addEventListener('click', function () { page = p; render(); });
      return b;
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>', page - 1, page === 1, false));
    for (var i = 1; i <= pages; i++) {
      if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
        if (i === 3 || i === pages - 2) { var d = document.createElement('span'); d.style.cssText = 'color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;'; d.textContent = '…'; pageEl.appendChild(d); }
        continue;
      }
      pageEl.appendChild(btn(i, i, false, i === page));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>', page + 1, page === pages, false));
  }

  ths.forEach(function (th) {
    th.addEventListener('click', function () {
      var col = th.dataset.col;
      if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      else { sortCol = col; sortDir = 'desc'; }
      page = 1; render();
    });
  });
  if (srchEl) srchEl.addEventListener('input', function () {
    search = srchEl.value.trim().toLowerCase(); page = 1; render();
  });

  render();
})();
</script>
<?= $this->end() ?>
