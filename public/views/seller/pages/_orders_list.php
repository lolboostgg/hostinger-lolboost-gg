<?php
/**
 * Shared seller "orders" table (Items, Top Ups, Digital Goods).
 *
 * Every marketplace had its own copy of this markup, which is why the three
 * order lists slowly drifted apart visually. They now all render through this
 * one partial and are byte-for-byte identical apart from their data.
 *
 * Expects $olCfg:
 *   icon, title, subtitle, empty            — header + empty state text
 *   link  => ['href','label','icon']        — top-right button (optional)
 *   search_placeholder                      — search box placeholder
 *   columns => ['item','game','game_data']  — optional per-column header labels
 *   rows  => list of:
 *     id, url, cover, name, sub, game_name, game_icon, meta,
 *     price, earnings, stock, sold, status => ['label','tone'], buyer, created
 */
$olCfg  = is_array($olCfg ?? null) ? $olCfg : [];
$olRows = is_array($olCfg['rows'] ?? null) ? $olCfg['rows'] : [];
$olCols = is_array($olCfg['columns'] ?? null) ? $olCfg['columns'] : [];
$olLink = is_array($olCfg['link'] ?? null) ? $olCfg['link'] : [];
$olH = function ($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
};
$olMoney = function ($v) {
    return '€' . number_format((float)$v, 2);
};
// Listings without artwork (or with a dead image URL) fall back to a tinted
// tile instead of an empty gap, so every row keeps the same visual rhythm.
$olFallbackIcon = (string)($olCfg['fallback_icon'] ?? 'fa-solid fa-tag');
?>
<?= $this->start('styles') ?>
<style>
.io-page .card{background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important}
.io-hero{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:20px 22px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.io-hero-left{display:flex;align-items:center;gap:14px}
.io-icon{width:44px;height:44px;border-radius:13px;background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;color:#a78bfa}
.io-title{font-size:1.2rem;font-weight:950;color:#fff;margin:0}
.io-sub{font-size:.8rem;color:rgba(255,255,255,.42);margin-top:2px}
.io-link{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:12px;background:linear-gradient(135deg,#6d5cff,#b05cff);color:#fff;text-decoration:none;font-size:.82rem;font-weight:900}
.io-toolbar{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.io-pills{display:flex;gap:6px}
.io-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.55);font-size:.78rem;font-weight:900;text-decoration:none;cursor:pointer}
.io-pill.is-active{border-color:rgba(109,92,255,.42);background:rgba(109,92,255,.15);color:#c4b5fd}
.io-search{position:relative}
.io-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3)}
.io-search input{width:250px;height:36px;border-radius:11px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;padding:0 12px 0 36px;outline:none}
.io-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28)}
.io-table{width:100%;border-collapse:collapse}
.io-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06)}
.io-table th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap}
.io-row{border-bottom:1px solid rgba(255,255,255,.04);cursor:pointer;transition:background .12s}
.io-row:last-child{border-bottom:0}
.io-row:hover{background:rgba(109,92,255,.08)}
.io-table td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8)}
.io-id{color:rgba(255,255,255,.35);font-weight:900}
.io-item{display:flex;align-items:center;gap:10px;min-width:220px}
.io-img{width:34px;height:34px;border-radius:9px;object-fit:contain;background:rgba(255,255,255,.05);flex:0 0 34px}
.io-ph{position:relative;overflow:hidden;width:34px;height:34px;border-radius:9px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.2);color:#a78bfa;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;flex:0 0 34px}
.io-ph img{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;background:rgba(255,255,255,.05)}
.io-ph--sm{width:22px;height:22px;border-radius:7px;font-size:.7rem;flex:0 0 22px}
.io-buyer{display:flex;align-items:center;gap:8px;min-width:0}
.io-buyer img{width:26px;height:26px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.12);flex:0 0 26px}
.io-buyer-ph{position:relative;overflow:hidden;width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.6);display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:900;flex:0 0 26px}
.io-buyer-ph img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%}
.io-name{font-weight:950;color:#fff}
.io-muted{font-size:.72rem;color:rgba(255,255,255,.38);margin-top:2px}
.io-game{display:flex;align-items:center;gap:8px;font-weight:800}
.io-game img{width:22px;height:22px;object-fit:contain}
.io-money{font-weight:950;color:#fff}
.io-earn{font-weight:950;color:#4ade80}
.io-badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 10px;font-size:.72rem;font-weight:950;background:rgba(34,197,94,.13);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.io-badge.is-warn{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.3);color:#facc15}
.io-badge.is-info{background:rgba(109,92,255,.14);border-color:rgba(109,92,255,.32);color:#c4b5fd}
.io-badge.is-paid{background:rgba(59,130,246,.14);border-color:rgba(96,165,250,.38);color:#93c5fd}
.io-badge.is-delivered{background:rgba(20,184,166,.14);border-color:rgba(45,212,191,.38);color:#5eead4}
.io-badge.is-danger,.io-badge.refunded{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.3);color:#fb7185}
.io-action{display:inline-flex;align-items:center;gap:.35rem;padding:7px 14px;border-radius:10px;border:1px solid rgba(109,92,255,.28);background:rgba(109,92,255,.15);color:#c4b5fd;font-size:.79rem;font-weight:800;text-decoration:none;transition:background .12s,border-color .12s,color .12s}
.io-action:hover{background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff}
.io-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 0}
.io-page-btn{min-width:34px;height:34px;border-radius:9px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff}
.io-page-btn.active{background:#6d5cff;border-color:#6d5cff}
@media(max-width:1100px){.io-wrap{overflow:auto}.io-table{min-width:1150px}.io-toolbar{align-items:stretch}.io-search input{width:100%}}
</style>
<?= $this->stop() ?>

<div class="io-page">
  <div class="io-hero">
    <div class="io-hero-left">
      <div class="io-icon"><i class="<?= $olH($olCfg['icon'] ?? 'fa-duotone fa-gift') ?>"></i></div>
      <div>
        <h1 class="io-title"><?= $olH($olCfg['title'] ?? 'Orders') ?></h1>
        <div class="io-sub"><?= $olH($olCfg['subtitle'] ?? '') ?></div>
      </div>
    </div>
    <?php if (!empty($olLink['href'])): ?>
      <a class="io-link" href="<?= $olH($olLink['href']) ?>">
        <i class="<?= $olH($olLink['icon'] ?? 'fa-solid fa-store') ?>"></i> <?= $olH($olLink['label'] ?? 'My Listings') ?>
      </a>
    <?php endif; ?>
  </div>

  <div class="io-toolbar">
    <div class="io-pills" id="ioPills">
      <?php foreach (($olCfg['filters'] ?? [['key' => '', 'label' => 'Sold', 'icon' => 'fa-solid fa-check']]) as $olIdx => $olFilter): ?>
        <button type="button" class="io-pill<?= $olIdx === 0 ? ' is-active' : '' ?>" data-filter="<?= $olH($olFilter['key'] ?? '') ?>">
          <i class="<?= $olH($olFilter['icon'] ?? 'fa-solid fa-check') ?>"></i> <?= $olH($olFilter['label'] ?? 'All') ?>
        </button>
      <?php endforeach; ?>
    </div>
    <div class="io-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input id="ioSearch" type="search" placeholder="<?= $olH($olCfg['search_placeholder'] ?? 'Search orders...') ?>">
    </div>
  </div>

  <div class="io-wrap">
    <table class="io-table">
      <thead>
        <tr>
          <th>ID</th>
          <th><?= $olH($olCols['item'] ?? 'Item') ?></th>
          <th><?= $olH($olCols['game'] ?? 'Game') ?></th>
          <th><?= $olH($olCols['game_data'] ?? 'Game Data') ?></th>
          <th>Price</th>
          <th>Earnings</th>
          <th>Stock</th>
          <th>Sold</th>
          <th>Status</th>
          <th>Buyer</th>
          <th>Created</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody id="ioBody">
        <?php foreach ($olRows as $olRow): ?>
          <?php
            $olStatus = is_array($olRow['status'] ?? null) ? $olRow['status'] : ['label' => 'Sold', 'tone' => ''];
            $olUrl    = (string)($olRow['url'] ?? '');
            $olSearch = strtolower(trim(implode(' ', array_filter([
                (string)($olRow['name'] ?? ''), (string)($olRow['sub'] ?? ''),
                (string)($olRow['game_name'] ?? ''), (string)($olRow['meta'] ?? ''),
                (string)($olRow['buyer'] ?? ''), (string)($olStatus['label'] ?? ''),
            ]))));
          ?>
          <tr class="io-row"
              data-search="<?= $olH($olSearch) ?>"
              data-status="<?= $olH(strtolower((string)($olStatus['key'] ?? $olStatus['label'] ?? ''))) ?>"
              onclick="window.location='<?= $olH($olUrl) ?>'">
            <td><span class="io-id">#<?= (int)($olRow['id'] ?? 0) ?></span></td>
            <td>
              <div class="io-item">
                <?php // Icon tile as backdrop, artwork layered on top. A dead image URL just
                      // removes itself instead of leaving a broken-image glyph. ?>
                <span class="io-ph"><i class="<?= $olH($olFallbackIcon) ?>"></i><?php if (!empty($olRow['cover'])): ?><img src="<?= $olH($olRow['cover']) ?>" alt="" loading="lazy" onerror="this.remove()"><?php endif; ?></span>
                <div>
                  <div class="io-name"><?= $olH($olRow['name'] ?? '—') ?></div>
                  <div class="io-muted"><?= $olH($olRow['sub'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td>
              <div class="io-game">
                <span class="io-ph io-ph--sm"><i class="<?= $olH($olFallbackIcon) ?>"></i><?php if (!empty($olRow['game_icon'])): ?><img src="<?= $olH($olRow['game_icon']) ?>" alt="" loading="lazy" onerror="this.remove()"><?php endif; ?></span>
                <span><?= $olH($olRow['game_name'] ?? '—') ?></span>
              </div>
            </td>
            <td><span class="io-muted" style="font-size:.78rem"><?= $olH($olRow['meta'] ?? '—') ?></span></td>
            <td><span class="io-money"><?= $olMoney($olRow['price'] ?? 0) ?></span></td>
            <td><span class="io-earn"><?= $olMoney($olRow['earnings'] ?? 0) ?></span></td>
            <td><?= (int)($olRow['stock'] ?? 0) ?></td>
            <td><?= (int)($olRow['sold'] ?? 0) ?></td>
            <td>
              <span class="io-badge <?= $olH($olStatus['tone'] ?? '') ?>">
                <i class="<?= $olH($olStatus['icon'] ?? 'fa-solid fa-check') ?>"></i><?= $olH($olStatus['label'] ?? 'Sold') ?>
              </span>
            </td>
            <td>
              <?php
                $olBuyer = (string)($olRow['buyer'] ?? 'Unknown buyer');
                // Accept every spelling the three lists have used for this field, and fall
                // back to the default avatar. Guest checkouts have no clients.icon at all,
                // so without this the Buyer column renders the bare initial.
                $olBuyerIcon = '';
                foreach (['buyer_icon', 'client_icon', 'buyer_avatar', 'client_avatar'] as $olIconKey) {
                    $olCandidate = trim((string)($olRow[$olIconKey] ?? ''));
                    if ($olCandidate !== '') { $olBuyerIcon = $olCandidate; break; }
                }
                $olBuyerIcon = trim(str_replace('\\', '/', $olBuyerIcon));
                $olBaseUrl = rtrim((string)(defined('BASE_URL') ? BASE_URL : ''), '/');
                if ($olBuyerIcon === '') {
                    $olBuyerIcon = $olBaseUrl . '/public/uploads/icons/default.png';
                } elseif (strpos($olBuyerIcon, '//') === 0) {
                    $olBuyerIcon = 'https:' . $olBuyerIcon;
                } elseif (!preg_match('~^https?://~i', $olBuyerIcon) && strpos($olBuyerIcon, 'data:') !== 0) {
                    if (strpos($olBuyerIcon, '/') === 0) {
                        $olBuyerIcon = $olBaseUrl . $olBuyerIcon;
                    } elseif (strpos($olBuyerIcon, 'uploads/') === 0) {
                        $olBuyerIcon = $olBaseUrl . '/public/' . $olBuyerIcon;
                    } elseif (strpos($olBuyerIcon, '/') !== false) {
                        $olBuyerIcon = $olBaseUrl . '/' . ltrim($olBuyerIcon, '/');
                    } else {
                        $olBuyerIcon = $olBaseUrl . '/public/uploads/icons/' . $olBuyerIcon;
                    }
                }
                $olInitial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $olBuyer) ?: '?', 0, 1));
              ?>
              <div class="io-buyer">
                <?php // The initial is the backdrop, the avatar sits on top of it. A broken
                      // image just removes itself, so a bad clients.icon can never leave a
                      // broken-image glyph behind (the old onerror attribute was unquotable). ?>
                <span class="io-buyer-ph"><?= $olH($olInitial) ?><?php if ($olBuyerIcon !== ''): ?><img src="<?= $olH($olBuyerIcon) ?>" alt="" loading="lazy" onerror="this.remove()"><?php endif; ?></span>
                <span><?= $olH($olBuyer) ?></span>
              </div>
            </td>
            <td><span class="io-muted" style="font-size:.78rem"><?= $olH($olRow['created'] ?? '—') ?></span></td>
            <td style="text-align:right">
              <a class="io-action" href="<?= $olH($olUrl) ?>" onclick="event.stopPropagation()"><i class="fa-regular fa-eye"></i> View</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($olRows)): ?>
          <tr><td colspan="12" style="padding:50px;text-align:center;color:rgba(255,255,255,.35)"><?= $olH($olCfg['empty'] ?? 'No orders yet.') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="io-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4)">Showing <span id="ioShowing">0</span> of <span id="ioTotal"><?= count($olRows) ?></span></div>
    <div id="ioPages" style="display:flex;gap:5px"></div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  var rows = [].slice.call(document.querySelectorAll('.io-row'));
  var search = document.getElementById('ioSearch');
  var showing = document.getElementById('ioShowing');
  var total = document.getElementById('ioTotal');
  var pages = document.getElementById('ioPages');
  var pills = [].slice.call(document.querySelectorAll('#ioPills .io-pill'));
  var page = 1, perPage = 20, statusFilter = '';

  function matches(row) {
    var term = (search.value || '').trim().toLowerCase();
    if (term && (row.dataset.search || '').indexOf(term) === -1) return false;
    if (statusFilter && (row.dataset.status || '') !== statusFilter) return false;
    return true;
  }

  function render() {
    var visible = rows.filter(matches);
    var pageCount = Math.max(1, Math.ceil(visible.length / perPage));
    if (page > pageCount) page = pageCount;

    rows.forEach(function (row) { row.style.display = 'none'; });
    visible.slice((page - 1) * perPage, page * perPage).forEach(function (row) { row.style.display = ''; });

    showing.textContent = visible.length ? ((page - 1) * perPage + 1) + '–' + Math.min(page * perPage, visible.length) : '0';
    total.textContent = visible.length;

    pages.innerHTML = '';
    for (var i = 1; i <= pageCount && pageCount > 1; i++) {
      var btn = document.createElement('button');
      btn.className = 'io-page-btn' + (i === page ? ' active' : '');
      btn.textContent = i;
      (function (n) { btn.onclick = function () { page = n; render(); }; })(i);
      pages.appendChild(btn);
    }
  }

  search.addEventListener('input', function () { page = 1; render(); });
  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      pills.forEach(function (p) { p.classList.remove('is-active'); });
      pill.classList.add('is-active');
      statusFilter = (pill.dataset.filter || '').toLowerCase();
      page = 1;
      render();
    });
  });

  render();
})();
</script>
<?= $this->stop() ?>
