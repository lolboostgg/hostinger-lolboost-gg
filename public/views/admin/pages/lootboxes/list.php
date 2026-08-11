<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Lootboxes — Admin', 'h1' => 'Lootboxes']]) ?>
<?php
$data = is_array($data ?? null) ? $data : [];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$fmt = function($v){ $s = number_format((float)($v ?? 0), 2, '.', ''); return rtrim(rtrim($s, '0'), '.'); };
$rewardAssetBase = ASSET_URL . '/website/images/rewards/';
$coinAssetBase = BASE_URL . '/public/assets/website/images/coins/';
$rewardPointsIconUrl = $coinAssetBase . 'reward-points.png';
$lbCoinsIconUrl = $coinAssetBase . 'lbcoins.png';
$boxAssetBase = $rewardAssetBase . 'boxes/';
$boxImageUrl = function($row) use ($boxAssetBase) {
    $slug = strtolower(trim((string)($row['box_slug'] ?? '')));
    $map = [
        'daily-gift' => 'daily-gift.png',
        'starter-box' => 'starter-box.png',
        'silver-box' => 'silver-box.png',
        'gold-box' => 'gold-box.png',
        'diamond-box' => 'diamond-box.png',
        'challenger-box' => 'challenger-box.png',
    ];
    if ($slug === '') return '';
    if (isset($map[$slug])) return $boxAssetBase . $map[$slug];
    foreach ($map as $key => $file) {
        if (str_contains($slug, $key)) return $boxAssetBase . $file;
    }
    return $boxAssetBase . $slug . '.png';
};
$boxIcon = function($row) {
    $slug = strtolower((string)($row['box_slug'] ?? ''));
    $name = strtolower((string)($row['box_name'] ?? ''));
    $v = $slug . ' ' . $name;
    if (str_contains($v, 'daily')) return 'fa-gift';
    if (str_contains($v, 'challenger')) return 'fa-trophy-star';
    if (str_contains($v, 'diamond')) return 'fa-gem';
    if (str_contains($v, 'gold')) return 'fa-crown';
    if (str_contains($v, 'silver')) return 'fa-medal';
    if (str_contains($v, 'starter')) return 'fa-box-open';
    return 'fa-box-open';
};
$boxTierLabel = function($row) {
    $slug = strtolower(trim((string)($row['box_slug'] ?? '')));
    $name = trim((string)($row['box_name'] ?? ''));
    $value = $slug !== '' ? $slug : strtolower($name);
    if (str_contains($value, 'daily')) return 'Daily';
    if (str_contains($value, 'starter')) return 'Starter';
    if (str_contains($value, 'silver')) return 'Silver';
    if (str_contains($value, 'gold')) return 'Gold';
    if (str_contains($value, 'diamond')) return 'Diamond';
    if (str_contains($value, 'challenger')) return 'Challenger';
    return $name !== '' ? $name : 'Box';
};
$rewardText = function($type, $value, $coupon = '') use ($fmt) {
    $type = strtolower((string)($type ?? ''));
    $valueText = $fmt($value);
    if ($type === 'reward_points') return $valueText . ' Reward Points';
    if ($type === 'lb_coins' || $type === 'coins') return $valueText . ' LB Coins';
    if ($type === 'discount_coupon' || $type === 'coupon') return ($coupon ? $coupon . ' · ' : '') . $valueText . '% Coupon';
    if ($type === 'wallet_credit') return '€' . number_format((float)$value, 2) . ' Wallet Credit';
    if ($type === 'priority_boost') return 'Priority Boost';
    if ($type === 'champion_preference') return 'Champion Preference';
    return ($valueText !== '' && $valueText !== '0' ? $valueText . ' ' : '') . ucwords(str_replace('_', ' ', $type ?: 'Reward'));
};
$rewardImageUrl = function($item) use ($rewardAssetBase, $rewardPointsIconUrl, $lbCoinsIconUrl) {
    $custom = trim((string)($item['item_icon'] ?? $item['icon'] ?? ''));
    if ($custom !== '' && !preg_match('~(?:^|/)placeholder|broken|default~i', $custom)) {
        return $custom;
    }

    $type = strtolower((string)($item['reward_type'] ?? ''));
    $value = (float)($item['reward_value'] ?? 0);
    if ($type === 'reward_points') return $rewardPointsIconUrl;
    if ($type === 'lb_coins' || $type === 'coins') return $lbCoinsIconUrl;
    if ($type === 'discount_coupon' || $type === 'coupon') {
        $percent = (int)round($value);
        if ($percent >= 15) return $rewardAssetBase . '15discount.png';
        if ($percent >= 10) return $rewardAssetBase . '10discount.png';
        return $rewardAssetBase . '5discount.png';
    }
    if ($type === 'wallet_credit') return $value >= 5 ? $rewardAssetBase . '5storecredits.png' : $rewardAssetBase . '2storecredits.png';
    if ($type === 'priority_boost') return $rewardAssetBase . 'priority.png';
    if ($type === 'champion_preference') return $rewardAssetBase . 'champion.png';
    return '';
};
$rewardIcon = function($item){
    return match(strtolower((string)($item['reward_type'] ?? ''))) {
        'wallet_credit' => 'fa-wallet',
        'discount_coupon', 'coupon' => 'fa-ticket',
        'priority_boost' => 'fa-bolt',
        'champion_preference' => 'fa-user-crown',
        'reward_points' => 'fa-coins',
        'lb_coins', 'coins' => 'fa-coins',
        default => 'fa-gift'
    };
};
$rarityClass = fn($r) => in_array(strtolower((string)$r), ['common','uncommon','rare','epic','legendary'], true) ? strtolower((string)$r) : 'common';
$totalOpens = count($data);
$uniqueClients = count(array_unique(array_filter(array_map(fn($r) => (int)($r['client_id'] ?? 0), $data))));
$totalSpent = 0; foreach ($data as $row) $totalSpent += (float)($row['cost_coins'] ?? 0);
?>
<?= $this->start('styles') ?>
<style>
.lb-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px}.lb-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.18)}.lb-stat .ico{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd}.lb-stat .lbl{font-size:.68rem;font-weight:800;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.06em}.lb-stat .val{font-size:1.15rem;font-weight:950;color:rgba(255,255,255,.92)}.lb-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,.26)}.lb-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02)}.lb-title{font-size:1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0}.lb-search{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;padding:8px 12px;width:260px;max-width:100%;outline:none}.lb-search:focus{border-color:rgba(109,92,255,.45);box-shadow:0 0 0 3px rgba(109,92,255,.10)}.lb-table-wrap{overflow-x:auto}.lb-table{width:100%;border-collapse:collapse}.lb-table th{padding:12px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.07em;background:rgba(255,255,255,.025);white-space:nowrap;user-select:none}.lb-table th.sortable{cursor:pointer}.lb-table th.sortable:hover{color:rgba(255,255,255,.75)}.lb-sort-icon{margin-left:5px;font-size:.62rem;opacity:.35}.lb-table th.sort-asc .lb-sort-icon,.lb-table th.sort-desc .lb-sort-icon{opacity:1;color:#8f6dff}.lb-table td{padding:13px 16px;border-top:1px solid rgba(255,255,255,.045);vertical-align:middle;color:rgba(255,255,255,.78);font-size:.84rem}.lb-client,.lb-item,.lb-box-cell{display:flex;align-items:center;gap:10px;min-width:190px}.lb-box-cell{min-width:170px}.lb-box-visual{width:42px;height:42px;border-radius:12px;background:radial-gradient(circle at 50% 18%,rgba(109,140,255,.24),rgba(0,0,0,.16) 62%);border:1px solid rgba(109,140,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.05)}.lb-box-visual img{width:36px;height:36px;object-fit:contain;filter:drop-shadow(0 10px 16px rgba(109,140,255,.18))}.lb-box-visual i{font-size:1.15rem;color:#9db7ff}.lb-box-name{font-weight:950;color:rgba(255,255,255,.92);line-height:1.1}.lb-box-tier{font-size:.73rem;color:#8fa8ff;font-weight:850;margin-top:3px}.lb-client img,.lb-client .av{width:34px;height:34px;border-radius:9px;object-fit:cover;flex-shrink:0;background:rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-weight:900}.lb-client a{font-weight:900;color:rgba(255,255,255,.9);text-decoration:none}.lb-client a:hover{color:#c4b5fd}.lb-muted{font-size:.73rem;color:rgba(255,255,255,.35);margin-top:1px}.lb-reward-visual{width:48px;height:48px;border-radius:14px;background:radial-gradient(circle at 50% 18%,rgba(109,140,255,.24),rgba(0,0,0,.16) 62%);border:1px solid rgba(109,140,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.05)}.lb-reward-visual img{width:40px;height:40px;object-fit:contain;filter:drop-shadow(0 10px 16px rgba(109,140,255,.18))}.lb-reward-visual i{font-size:1.25rem;color:#9db7ff}.lb-rarity{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em;border:1px solid rgba(255,255,255,.1)}.lb-rarity.common{background:rgba(148,163,184,.14);color:#cbd5e1}.lb-rarity.uncommon{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.22);color:#86efac}.lb-rarity.rare{background:rgba(96,165,250,.15);border-color:rgba(96,165,250,.24);color:#93c5fd}.lb-rarity.epic{background:rgba(217,70,239,.16);border-color:rgba(217,70,239,.26);color:#f0abfc}.lb-rarity.legendary{background:rgba(250,204,21,.16);border-color:rgba(250,204,21,.28);color:#fde68a}.lb-reward{font-weight:900;color:rgba(255,255,255,.92);white-space:nowrap}.lb-date{font-size:.78rem;color:rgba(255,255,255,.42);white-space:nowrap}.lb-empty{text-align:center;padding:60px 20px;color:rgba(255,255,255,.38)}.lb-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.08);flex-wrap:wrap}.lb-count{font-size:.82rem;color:rgba(255,255,255,.42);font-weight:700}.lb-pager{display:flex;gap:5px;flex-wrap:wrap}.lb-page-btn{width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.68);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.lb-page-btn:hover:not(:disabled){background:rgba(255,255,255,.09);color:#fff}.lb-page-btn.is-active{background:rgba(109,92,255,.24);border-color:rgba(109,92,255,.42);color:#c4b5fd}.lb-page-btn:disabled{opacity:.35;cursor:not-allowed}.lb-dots{color:rgba(255,255,255,.3);line-height:32px;padding:0 4px}@media(max-width:1100px){.lb-table{min-width:980px}.lb-head{flex-wrap:wrap}.lb-search{width:100%}}
</style>
<?= $this->end() ?>
<div class="lb-stats">
  <div class="lb-stat"><div class="ico"><i class="fa-duotone fa-box-open"></i></div><div><div class="lbl">Total Opens</div><div class="val"><?= $totalOpens ?></div></div></div>
  <div class="lb-stat"><div class="ico"><i class="fa-duotone fa-users"></i></div><div><div class="lbl">Clients</div><div class="val"><?= $uniqueClients ?></div></div></div>
  <div class="lb-stat"><div class="ico"><i class="fa-duotone fa-gift"></i></div><div><div class="lbl">Reward Points Spent</div><div class="val"><?= $fmt($totalSpent) ?></div></div></div>
</div>
<div class="lb-card">
  <div class="lb-head"><h2 class="lb-title"><i class="fa-duotone fa-gift" style="color:#c4b5fd;"></i> Lootbox Openings</h2><input type="search" id="lootSearch" class="lb-search" placeholder="Search client, box, reward…"></div>
  <div class="lb-table-wrap"><table class="lb-table" id="lootTable"><thead><tr><th class="sortable" data-sort="id">ID <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="client">Client <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="box">Box <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="item">Won Item <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="rarity">Rarity <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="reward">Reward <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="cost">Cost <i class="fa-solid fa-sort lb-sort-icon"></i></th><th class="sortable" data-sort="date">Date <i class="fa-solid fa-sort lb-sort-icon"></i></th></tr></thead><tbody>
<?php if (empty($data)): ?><tr><td colspan="8"><div class="lb-empty"><i class="fa-duotone fa-box-open" style="font-size:2rem;display:block;margin-bottom:10px;"></i>No lootbox openings found.</div></td></tr><?php endif; ?>
<?php foreach ($data as $row):
  $clientName = $row['client_name'] ?? ('Client #' . ($row['client_id'] ?? ''));
  $clientIcon = $row['client_icon'] ?? '';
  $rarity = strtolower((string)($row['rarity'] ?? 'common'));
  $img = $rewardImageUrl($row);
  $boxImg = $boxImageUrl($row);
  $boxTier = $boxTierLabel($row);
  $rewardLabel = $rewardText($row['reward_type'] ?? '', $row['reward_value'] ?? 0, $row['coupon_code'] ?? '');
  $createdTs = !empty($row['created_at']) ? strtotime((string)$row['created_at']) : 0;
  $search = strtolower(trim($clientName . ' ' . ($row['client_email'] ?? '') . ' ' . ($row['box_name'] ?? '') . ' ' . ($row['item_name'] ?? '') . ' ' . ($row['reward_type'] ?? '') . ' ' . ($row['coupon_code'] ?? '')));
?>
<tr class="loot-row" data-search="<?= $h($search) ?>" data-id="<?= (int)($row['id'] ?? 0) ?>" data-client="<?= $h(strtolower($clientName)) ?>" data-box="<?= $h(strtolower(($row['box_name'] ?? '') . ' ' . $boxTier . ' ' . ($row['box_slug'] ?? ''))) ?>" data-item="<?= $h(strtolower($row['item_name'] ?? '')) ?>" data-rarity="<?= $h(strtolower($rarity ?: 'common')) ?>" data-reward="<?= $h(strtolower($rewardLabel)) ?>" data-cost="<?= (float)($row['cost_coins'] ?? 0) ?>" data-date="<?= (int)$createdTs ?>">
  <td><strong style="color:rgba(255,255,255,.45);">#<?= (int)($row['id'] ?? 0) ?></strong></td>
  <td><div class="lb-client"><?php if ($clientIcon): ?><img src="<?= $h($clientIcon) ?>" alt=""><?php else: ?><span class="av"><?= $h(strtoupper(substr($clientName,0,1))) ?></span><?php endif; ?><div><a href="<?= ADMN_URL ?>/client/<?= (int)($row['client_id'] ?? 0) ?>/profile"><?= $h($clientName) ?></a><div class="lb-muted"><?= $h($row['client_email'] ?? '') ?></div></div></div></td>
  <td><div class="lb-box-cell"><span class="lb-box-visual"><?php if ($boxImg): ?><img src="<?= $h($boxImg) ?>" alt="" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=&quot;fa-duotone <?= $h($boxIcon($row)) ?>&quot;></i>';"><?php else: ?><i class="fa-duotone <?= $h($boxIcon($row)) ?>"></i><?php endif; ?></span><div><div class="lb-box-name"><?= $h($row['box_name'] ?? 'Reward Box') ?></div><div class="lb-box-tier"><?= $h($boxTier) ?></div></div></div></td>
  <td><div class="lb-item"><span class="lb-reward-visual"><?php if ($img): ?><img src="<?= $h($img) ?>" alt="" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=&quot;fa-duotone <?= $h($rewardIcon($row)) ?>&quot;></i>';">
  <?php else: ?><i class="fa-duotone <?= $h($rewardIcon($row)) ?>"></i><?php endif; ?></span><div><strong><?= $h($row['item_name'] ?? 'Unknown Reward') ?></strong><?php if (!empty($row['coupon_code'])): ?><div class="lb-muted">Code: <?= $h($row['coupon_code']) ?></div><?php endif; ?></div></div></td>
  <td><span class="lb-rarity <?= $h($rarityClass($rarity)) ?>"><?= $h($rarity ?: 'common') ?></span></td>
  <td><span class="lb-reward"><?= $h($rewardLabel) ?></span></td>
  <td><?= $fmt($row['cost_coins'] ?? 0) ?> RP</td>
  <td><span class="lb-date"><?= $createdTs ? date('d.m.Y H:i', $createdTs) : '—' ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
  <div class="lb-footer"><div class="lb-count">Showing <span id="lootShowing">0</span> of <span id="lootTotal">0</span></div><div class="lb-pager" id="lootPager"></div></div>
</div>
<?= $this->start('scripts') ?>
<script>
(function(){
  var perPage = 25, page = 1, query = '', sortCol = 'date', sortDir = 'desc';
  var input = document.getElementById('lootSearch');
  var tbody = document.querySelector('#lootTable tbody');
  var rows = Array.from(document.querySelectorAll('#lootTable tbody tr.loot-row'));
  var showing = document.getElementById('lootShowing');
  var totalEl = document.getElementById('lootTotal');
  var pager = document.getElementById('lootPager');
  var heads = Array.from(document.querySelectorAll('#lootTable thead th.sortable'));
  var numericCols = {id:true, cost:true, date:true};

  function filtered(){
    return rows.filter(function(row){
      return !query || (row.dataset.search || '').indexOf(query) !== -1;
    });
  }

  function sorted(list){
    return list.slice().sort(function(a, b){
      var av = a.dataset[sortCol] || '', bv = b.dataset[sortCol] || '';
      var cmp;
      if (numericCols[sortCol]) {
        cmp = (parseFloat(av) || 0) - (parseFloat(bv) || 0);
      } else {
        cmp = String(av).localeCompare(String(bv), undefined, {numeric:true, sensitivity:'base'});
      }
      return sortDir === 'asc' ? cmp : -cmp;
    });
  }

  function pageButton(label, target, disabled, active){
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'lb-page-btn' + (active ? ' is-active' : '');
    b.innerHTML = label;
    b.disabled = !!disabled;
    if (!disabled) b.addEventListener('click', function(){ page = target; render(); });
    return b;
  }

  function render(){
    var list = sorted(filtered());
    var total = list.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    if (page > pages) page = pages;
    var start = (page - 1) * perPage, end = start + perPage;

    rows.forEach(function(row){ row.style.display = 'none'; });
    list.slice(start, end).forEach(function(row){ tbody.appendChild(row); row.style.display = ''; });

    if (showing) showing.textContent = total ? (start + 1) + '–' + Math.min(end, total) : '0';
    if (totalEl) totalEl.textContent = total;

    heads.forEach(function(th){
      th.classList.remove('sort-asc', 'sort-desc');
      if (th.dataset.sort === sortCol) th.classList.add('sort-' + sortDir);
    });

    if (!pager) return;
    pager.innerHTML = '';
    if (pages <= 1) return;
    pager.appendChild(pageButton('<i class="fa-solid fa-chevron-left"></i>', page - 1, page === 1, false));
    for (var i = 1; i <= pages; i++) {
      if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
        if (i === 3 || i === pages - 2) {
          var d = document.createElement('span');
          d.className = 'lb-dots';
          d.textContent = '…';
          pager.appendChild(d);
        }
        continue;
      }
      pager.appendChild(pageButton(String(i), i, false, i === page));
    }
    pager.appendChild(pageButton('<i class="fa-solid fa-chevron-right"></i>', page + 1, page === pages, false));
  }

  heads.forEach(function(th){
    th.addEventListener('click', function(){
      var col = th.dataset.sort;
      if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      else { sortCol = col; sortDir = numericCols[col] ? 'desc' : 'asc'; }
      page = 1;
      render();
    });
  });

  if (input) input.addEventListener('input', function(){
    query = input.value.trim().toLowerCase();
    page = 1;
    render();
  });
  render();
})();
</script>
<?= $this->end() ?>
