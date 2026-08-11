<?= $this->layout('client/layouts/main', ['meta' => $meta ?? ['title' => 'My Wins']]) ?>
<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$coins = (float)($client['reward_points'] ?? 0);
$rarityClass = function($r){ $r = strtolower((string)$r); return in_array($r, ['common','uncommon','rare','epic','legendary'], true) ? $r : 'common'; };
$rewardAssetBase = ASSET_URL . '/website/images/rewards/';
$coinAssetBase = BASE_URL . '/public/assets/website/images/coins/';
$rewardPointsIconUrl = $coinAssetBase . 'reward-points.png';
$lbCoinsIconUrl = $coinAssetBase . 'coin_purple.png';
$boxAssetBase = $rewardAssetBase . 'boxes/';
$coinIconUrl = fn($value = 0) => $rewardPointsIconUrl;
$typeLabel = function($type, $value){
  $type=(string)$type; $value=(string)$value;
  return match($type){
    'reward_points' => rtrim(rtrim(number_format((float)$value,2,'.',''), '0'), '.') . ' Reward Points',
    'lb_coins' => rtrim(rtrim(number_format((float)$value,2,'.',''), '0'), '.') . ' LB Coins',
    'wallet_credit' => rtrim(rtrim(number_format((float)$value,2,'.',''), '0'), '.') . '€ Wallet Credit',
    'discount_coupon' => (int)$value . '% Coupon',
    'priority_boost' => 'Priority Queue Boost',
    'champion_preference' => 'Champion Preference',
    default => 'Reward',
  };
};
$rewardImageUrl = function($item) use ($rewardAssetBase, $rewardPointsIconUrl, $lbCoinsIconUrl){
    $type = (string)($item['reward_type'] ?? '');
    $value = $item['reward_value'] ?? 0;
    $amount = is_numeric($value) ? (float)$value : 0.0;
    if ($type === 'reward_points') return $rewardPointsIconUrl;
    if ($type === 'lb_coins') return $lbCoinsIconUrl;
    if ($type === 'discount_coupon') {
        $percent = (int)$amount;
        if ($percent >= 15) return $rewardAssetBase . '15discount.png';
        if ($percent >= 10) return $rewardAssetBase . '10discount.png';
        return $rewardAssetBase . '5discount.png';
    }
    if ($type === 'wallet_credit') return $amount >= 5 ? $rewardAssetBase . '5storecredits.png' : $rewardAssetBase . '2storecredits.png';
    if ($type === 'priority_boost') return $rewardAssetBase . 'priority.png';
    if ($type === 'champion_preference') return $rewardAssetBase . 'champion.png';
    return '';
};
$boxImageUrl = function($win) use ($boxAssetBase){
    $slug = trim((string)($win['box_slug'] ?? ''));
    if ($slug === '') return '';
    $map = [
        'daily-gift' => 'daily-gift.png',
        'starter-box' => 'starter-box.png',
        'silver-box' => 'silver-box.png',
        'gold-box' => 'gold-box.png',
        'diamond-box' => 'diamond-box.png',
        'challenger-box' => 'challenger-box.png',
    ];
    if (isset($map[$slug])) return $boxAssetBase . $map[$slug];
    return $boxAssetBase . $slug . '.png';
};
$rewardIcon = function($item){
    $icon = trim((string)($item['item_icon'] ?? $item['icon'] ?? ''));
    if ($icon !== '') return $icon;
    return match((string)($item['reward_type'] ?? '')) {
        'discount_coupon' => 'fa-ticket',
        'wallet_credit' => 'fa-wallet',
        'lb_coins' => 'fa-coins',
        'reward_points' => 'fa-coins',
        'priority_boost' => 'fa-bolt',
        'champion_preference' => 'fa-user-helmet-safety',
        default => 'fa-gift',
    };
};
$renderRewardIcon = function($item, string $class = 'lb-win-img') use ($h, $rewardImageUrl, $rewardIcon){
    $img = $rewardImageUrl($item);
    if ($img !== '') return '<img class="' . $h($class) . '" src="' . $h($img) . '" alt="Reward">';
    return '<i class="fa-duotone ' . $h($rewardIcon($item)) . '"></i>';
};
?>
<?= $this->start('styles') ?>
<style>
.lb-wins{--line:rgba(255,255,255,.08);--soft:rgba(255,255,255,.045);--text:rgba(255,255,255,.92);--muted:rgba(255,255,255,.55);--blue:#6d8cff}.lb-wins-top{border:1px solid rgba(109,140,255,.22);background:radial-gradient(900px 260px at 90% 0%,rgba(109,140,255,.18),transparent 60%),linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02));border-radius:24px;padding:26px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 18px 48px rgba(0,0,0,.24)}.lb-wins-eyebrow{font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;font-weight:950;color:#8fb2ff;margin-bottom:8px}.lb-wins-title{font-size:2rem;font-weight:950;color:var(--text);margin:0}.lb-wins-sub{color:var(--muted);font-weight:700;margin:8px 0 0}.lb-wins-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.lb-wins-balance,.lb-wins-back{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(109,140,255,.28);background:rgba(109,140,255,.09);color:rgba(255,255,255,.9);border-radius:12px;padding:9px 12px;font-weight:900;text-decoration:none}.lb-wins-back:hover{color:#fff;border-color:rgba(143,178,255,.6)}.lb-balance-coin{width:22px;height:22px;object-fit:contain}.lb-wins-tools{display:grid;grid-template-columns:minmax(240px,1.4fr) repeat(3,minmax(150px,.75fr));gap:10px;margin:0 0 16px}.lb-wins-field{position:relative}.lb-wins-field i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.45);font-size:.9rem}.lb-wins-input,.lb-wins-select{width:100%;min-height:44px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.035);color:rgba(255,255,255,.88);border-radius:14px;padding:10px 13px;font-weight:800;outline:none}.lb-wins-input{padding-left:38px}.lb-wins-select{display:none}.lb-custom-select{position:relative}.lb-custom-select-btn{width:100%;min-height:44px;border:1px solid rgba(255,255,255,.09);background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025));color:rgba(255,255,255,.92);border-radius:14px;padding:10px 42px 10px 14px;font-weight:900;outline:none;text-align:left;display:flex;align-items:center;justify-content:space-between;gap:10px;box-shadow:inset 0 1px 0 rgba(255,255,255,.04);cursor:pointer}.lb-custom-select-btn:after{content:"\f078";font-family:"Font Awesome 6 Pro","Font Awesome 6 Free";font-weight:900;position:absolute;right:15px;top:50%;transform:translateY(-50%);font-size:.78rem;color:rgba(143,178,255,.85);transition:.18s}.lb-custom-select.open .lb-custom-select-btn{border-color:rgba(109,140,255,.55);background:linear-gradient(180deg,rgba(109,140,255,.16),rgba(255,255,255,.035));box-shadow:0 0 0 3px rgba(109,140,255,.10)}.lb-custom-select.open .lb-custom-select-btn:after{transform:translateY(-50%) rotate(180deg)}.lb-custom-options{position:absolute;left:0;right:0;top:calc(100% + 8px);z-index:50;display:none;max-height:260px;overflow:auto;border:1px solid rgba(109,140,255,.30);background:rgba(24,27,31,.98);backdrop-filter:blur(16px);border-radius:16px;padding:7px;box-shadow:0 24px 60px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.03)}.lb-custom-select.open .lb-custom-options{display:block}.lb-custom-option{width:100%;border:0;background:transparent;color:rgba(255,255,255,.78);border-radius:11px;padding:10px 11px;text-align:left;font-weight:850;display:flex;align-items:center;justify-content:space-between;gap:10px;cursor:pointer}.lb-custom-option:hover{background:rgba(109,140,255,.12);color:#fff}.lb-custom-option.active{background:linear-gradient(90deg,rgba(109,140,255,.28),rgba(124,92,255,.16));color:#fff}.lb-custom-option.active:after{content:"\f00c";font-family:"Font Awesome 6 Pro","Font Awesome 6 Free";font-weight:900;color:#8fb2ff;font-size:.78rem}.lb-wins-select option{background:#1f2326;color:#fff}.lb-wins-count{color:var(--muted);font-size:.82rem;font-weight:800;margin:0 0 10px}.lb-wins-table{border:1px solid rgba(255,255,255,.08);border-radius:22px;overflow:hidden;background:rgba(255,255,255,.025);box-shadow:0 16px 34px rgba(0,0,0,.20)}.lb-win-row{display:grid;grid-template-columns:92px minmax(220px,1fr) 190px 150px minmax(220px,260px);gap:14px;align-items:center;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.07)}.lb-win-row:last-child{border-bottom:0}.lb-win-row:hover{background:rgba(109,140,255,.055)}.lb-win-visual{width:74px;height:74px;border-radius:16px;background:radial-gradient(circle at 50% 35%,rgba(109,140,255,.24),rgba(0,0,0,.18) 65%);display:flex;align-items:center;justify-content:center}.lb-win-visual i{font-size:2rem;color:#9db7ff}.lb-win-img{width:72px;height:72px;object-fit:contain;filter:drop-shadow(0 12px 18px rgba(109,140,255,.22))}.lb-win-main b{display:block;color:var(--text);font-size:1rem;line-height:1.18}.lb-win-main small{display:block;color:var(--muted);font-weight:750;margin-top:4px}.lb-rarity{display:inline-flex;border-radius:999px;padding:4px 8px;font-size:.65rem;text-transform:uppercase;font-weight:950;letter-spacing:.04em;margin-bottom:7px}.lb-rarity.common{background:rgba(148,163,184,.14);color:#cbd5e1}.lb-rarity.uncommon{background:rgba(34,197,94,.14);color:#86efac}.lb-rarity.rare{background:rgba(96,165,250,.15);color:#93c5fd}.lb-rarity.epic{background:rgba(217,70,239,.16);color:#f0abfc}.lb-rarity.legendary{background:rgba(250,204,21,.16);color:#fde68a}.lb-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);border-radius:999px;padding:7px 10px;color:rgba(255,255,255,.68);font-size:.78rem;font-weight:900;white-space:nowrap}.lb-status-chip{min-width:92px;justify-content:center}.lb-status-chip.unused{border-color:rgba(34,197,94,.32);background:rgba(34,197,94,.12);color:#86efac}.lb-status-chip.used{border-color:rgba(239,68,68,.34);background:rgba(239,68,68,.12);color:#fca5a5}.lb-status-chip.credited{border-color:rgba(96,165,250,.32);background:rgba(96,165,250,.12);color:#bfdbfe}.lb-status-chip.done{border-color:rgba(148,163,184,.22);background:rgba(148,163,184,.10);color:#cbd5e1}.lb-status-chip.unused i{color:#22c55e}.lb-status-chip.used i{color:#ef4444}.lb-status-chip.credited i{color:#60a5fa}.lb-status-chip.done i{color:#94a3b8}.lb-box-chip img{width:28px;height:28px;object-fit:contain}.lb-date{color:rgba(255,255,255,.72);font-weight:900;font-size:.86rem}.lb-date small{display:block;color:var(--muted);font-weight:750;margin-top:2px}.lb-code-stack{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0}.lb-code-wrap{display:flex;align-items:center;gap:8px;min-width:0}.lb-code{display:inline-flex;align-items:center;min-height:38px;background:rgba(0,0,0,.25);border:1px solid rgba(109,140,255,.20);border-radius:10px;padding:8px 10px;color:#dbeafe;font-weight:950;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px}.lb-copy-code{min-height:38px;border:1px solid rgba(109,140,255,.28);background:rgba(109,140,255,.12);color:#dbeafe;border-radius:10px;padding:8px 10px;font-weight:950;cursor:pointer;white-space:nowrap}.lb-copy-code:hover{border-color:rgba(143,178,255,.65);background:rgba(109,140,255,.20);color:#fff}.lb-wins-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;flex-wrap:wrap}.lb-page-info{color:var(--muted);font-weight:800;font-size:.84rem}.lb-page-buttons{display:flex;align-items:center;gap:8px}.lb-page-btn{border:1px solid rgba(109,140,255,.24);background:rgba(109,140,255,.08);color:rgba(255,255,255,.86);border-radius:11px;padding:9px 12px;font-weight:950;cursor:pointer}.lb-page-btn:disabled{opacity:.45;cursor:not-allowed}.lb-page-num{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:rgba(255,255,255,.72);border-radius:11px;padding:9px 12px;font-weight:950}.lb-empty{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:22px;padding:30px;text-align:center;color:var(--muted);font-weight:750}.lb-empty i{display:block;font-size:2.4rem;color:#9db7ff;margin-bottom:12px}@media(max-width:1180px){.lb-win-row{grid-template-columns:82px minmax(0,1fr);gap:12px}.lb-win-extra{grid-column:2}.lb-wins-tools{grid-template-columns:1fr 1fr}.lb-code{max-width:100%}}@media(max-width:620px){.lb-code-stack{flex-direction:column;align-items:flex-start}.lb-wins-title{font-size:1.55rem}.lb-wins-top{align-items:flex-start;flex-direction:column}.lb-wins-tools{grid-template-columns:1fr}.lb-win-row{padding:13px;grid-template-columns:72px minmax(0,1fr)}.lb-win-visual{width:64px;height:64px}.lb-win-img{width:64px;height:64px}.lb-code-wrap{flex-direction:column;align-items:flex-start}.lb-copy-code{width:100%}}
</style>
<?= $this->end() ?>
<div class="lb-wins" id="lbWinsApp">
  <div class="lb-wins-top">
    <div>
      <div class="lb-wins-eyebrow">LoLBoost Rewards</div>
      <h1 class="lb-wins-title">My Wins</h1>
      <p class="lb-wins-sub">All reward box wins sorted by newest first.</p>
    </div>
    <div class="lb-wins-actions">
      <div class="lb-wins-balance"><img class="lb-balance-coin" src="<?= $h($coinIconUrl($coins)) ?>" alt="Reward Points"> Your Reward Points: <?= number_format($coins, 2) ?></div>
      <a class="lb-wins-back" href="<?= BASE_URL ?>/profile/rewards"><i class="fa-solid fa-arrow-left"></i> Back to rewards</a>
    </div>
  </div>

  <?php
    $boxesForFilter = [];
    $typesForFilter = [];
    foreach (($wins ?? []) as $w) {
      $bn = trim((string)($w['box_name'] ?? ''));
      if ($bn !== '') $boxesForFilter[$bn] = $bn;
      $rt = trim((string)($w['reward_type'] ?? ''));
      if ($rt !== '') $typesForFilter[$rt] = $rt;
    }
    ksort($boxesForFilter); ksort($typesForFilter);
    $typeFilterLabel = fn($t) => match((string)$t) {
      'reward_points' => 'Reward Points',
      'lb_coins' => 'LB Coins',
      'discount_coupon' => 'Coupons',
      'wallet_credit' => 'Wallet Credit',
      'priority_boost' => 'Priority',
      'champion_preference' => 'Champion Preference',
      default => ucwords(str_replace('_', ' ', (string)$t)),
    };
  ?>

  <div class="lb-wins-tools">
    <label class="lb-wins-field">
      <i class="fa-solid fa-search"></i>
      <input id="lbWinsSearch" class="lb-wins-input" type="search" placeholder="Search reward, code, box..." autocomplete="off">
    </label>
    <label class="lb-wins-field">
      <select id="lbWinsBoxFilter" class="lb-wins-select">
        <option value="">All boxes</option>
        <?php foreach ($boxesForFilter as $boxName): ?><option value="<?= $h(mb_strtolower($boxName)) ?>"><?= $h($boxName) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="lb-wins-field">
      <select id="lbWinsTypeFilter" class="lb-wins-select">
        <option value="">All rewards</option>
        <?php foreach ($typesForFilter as $typeName): ?><option value="<?= $h($typeName) ?>"><?= $h($typeFilterLabel($typeName)) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="lb-wins-field">
      <select id="lbWinsStatusFilter" class="lb-wins-select">
        <option value="">All status</option>
        <option value="credited">Credited</option>
        <option value="unused">Unused</option>
        <option value="used">Used</option>
      </select>
    </label>
  </div>
  <p class="lb-wins-count" id="lbWinsCount"></p>

  <?php if (!empty($wins)): ?>
    <div class="lb-wins-table" id="lbWinsTable">
      <?php foreach ($wins as $win):
        $r = $rarityClass($win['rarity'] ?? 'common');
        $wonAt = !empty($win['won_at']) ? strtotime((string)$win['won_at']) : false;
        $boxImg = $boxImageUrl($win);
        $searchBlob = trim(($win['item_name'] ?? 'Reward') . ' ' . $typeLabel($win['reward_type'] ?? '', $win['reward_value'] ?? '') . ' ' . ($win['box_name'] ?? '') . ' ' . ($win['coupon_code'] ?? '') . ' ' . ($win['status'] ?? '') . ' ' . $r);
      ?>
        <div class="lb-win-row" data-search="<?= $h(mb_strtolower($searchBlob)) ?>" data-box="<?= $h(mb_strtolower((string)($win['box_name'] ?? ''))) ?>" data-type="<?= $h((string)($win['reward_type'] ?? '')) ?>" data-status="<?= $h(mb_strtolower((string)($win['status'] ?? ''))) ?>">
          <div class="lb-win-visual"><?= $renderRewardIcon($win) ?></div>
          <div class="lb-win-main">
            <span class="lb-rarity <?= $h($r) ?>"><?= $h($r) ?></span>
            <b><?= $h($win['item_name'] ?? 'Reward') ?></b>
            <small><?= $h($typeLabel($win['reward_type'] ?? '', $win['reward_value'] ?? '')) ?></small>
          </div>
          <div class="lb-win-extra">
            <span class="lb-chip lb-box-chip">
              <?php if ($boxImg !== ''): ?><img src="<?= $h($boxImg) ?>" alt="Box"><?php else: ?><i class="fa-duotone fa-box-open"></i><?php endif; ?>
              <?= $h($win['box_name'] ?? 'Reward Box') ?>
            </span>
          </div>
          <div class="lb-win-extra lb-date">
            <?= $wonAt ? $h(date('d.m.Y', $wonAt)) : 'Unknown date' ?>
            <?php if ($wonAt): ?><small><?= $h(date('H:i', $wonAt)) ?></small><?php endif; ?>
          </div>
          <div class="lb-win-extra">
            <?php
              $statusRaw = mb_strtolower(trim((string)($win['status'] ?? 'done')));
              $statusClass = in_array($statusRaw, ['unused','used','credited'], true) ? $statusRaw : 'done';
              $statusIcon = match($statusClass) {
                'unused' => 'fa-circle-check',
                'used' => 'fa-circle-xmark',
                'credited' => 'fa-circle-check',
                default => 'fa-circle-check',
              };
            ?>
            <?php if (!empty($win['coupon_code'])): ?>
              <div class="lb-code-stack">
                <div class="lb-code-wrap">
                  <span class="lb-code" title="<?= $h($win['coupon_code']) ?>"><?= $h($win['coupon_code']) ?></span>
                  <?php if ($statusClass === 'unused'): ?>
                    <button type="button" class="lb-copy-code" data-copy="<?= $h($win['coupon_code']) ?>"><i class="fa-regular fa-copy"></i> Copy</button>
                  <?php endif; ?>
                </div>
                <span class="lb-chip lb-status-chip <?= $h($statusClass) ?>"><i class="fa-duotone <?= $h($statusIcon) ?>"></i><?= $h(ucfirst($statusRaw !== '' ? $statusRaw : 'done')) ?></span>
              </div>
            <?php else: ?>
              <span class="lb-chip lb-status-chip <?= $h($statusClass) ?>"><i class="fa-duotone <?= $h($statusIcon) ?>"></i><?= $h(ucfirst($statusRaw !== '' ? $statusRaw : 'done')) ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="lb-wins-pagination" id="lbWinsPagination">
      <div class="lb-page-info" id="lbWinsPageInfo"></div>
      <div class="lb-page-buttons">
        <button type="button" class="lb-page-btn" id="lbWinsPrev"><i class="fa-solid fa-chevron-left"></i> Prev</button>
        <span class="lb-page-num" id="lbWinsPageNum">1</span>
        <button type="button" class="lb-page-btn" id="lbWinsNext">Next <i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>
  <?php else: ?>
    <div class="lb-empty"><i class="fa-duotone fa-gift"></i>You do not have any reward wins yet.</div>
  <?php endif; ?>
</div>
<?= $this->start('scripts') ?>
<script>
(function(){
  var root=document.getElementById('lbWinsApp');
  if(!root) return;
  var rows=[].slice.call(root.querySelectorAll('.lb-win-row'));
  var search=document.getElementById('lbWinsSearch');
  var box=document.getElementById('lbWinsBoxFilter');
  var type=document.getElementById('lbWinsTypeFilter');
  var status=document.getElementById('lbWinsStatusFilter');
  var count=document.getElementById('lbWinsCount');
  var prev=document.getElementById('lbWinsPrev');
  var next=document.getElementById('lbWinsNext');
  var pageNum=document.getElementById('lbWinsPageNum');
  var pageInfo=document.getElementById('lbWinsPageInfo');
  var pagination=document.getElementById('lbWinsPagination');
  var perPage=10;
  var page=1;
  var filtered=rows.slice();
  function buildCustomSelect(select){
    if(!select || select.dataset.customized==='1') return;
    select.dataset.customized='1';
    var wrap=document.createElement('div');
    wrap.className='lb-custom-select';
    var btn=document.createElement('button');
    btn.type='button';
    btn.className='lb-custom-select-btn';
    var label=document.createElement('span');
    label.className='lb-custom-select-label';
    btn.appendChild(label);
    var menu=document.createElement('div');
    menu.className='lb-custom-options';
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    wrap.appendChild(btn);
    wrap.appendChild(menu);
    function refresh(){
      menu.innerHTML='';
      var selected=select.options[select.selectedIndex];
      label.textContent=selected ? selected.textContent : '';
      [].slice.call(select.options).forEach(function(opt){
        var item=document.createElement('button');
        item.type='button';
        item.className='lb-custom-option' + (opt.selected ? ' active' : '');
        item.textContent=opt.textContent;
        item.addEventListener('click', function(){
          select.value=opt.value;
          select.dispatchEvent(new Event('change', {bubbles:true}));
          wrap.classList.remove('open');
          refresh();
        });
        menu.appendChild(item);
      });
    }
    btn.addEventListener('click', function(e){
      e.preventDefault();
      document.querySelectorAll('.lb-custom-select.open').forEach(function(open){ if(open!==wrap) open.classList.remove('open'); });
      wrap.classList.toggle('open');
    });
    select.addEventListener('change', refresh);
    refresh();
  }
  [box,type,status].forEach(buildCustomSelect);
  document.addEventListener('click', function(e){
    if(!e.target.closest('.lb-custom-select')) document.querySelectorAll('.lb-custom-select.open').forEach(function(open){ open.classList.remove('open'); });
  });
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') document.querySelectorAll('.lb-custom-select.open').forEach(function(open){ open.classList.remove('open'); });
  });
  function val(el){return (el && el.value ? el.value : '').toLowerCase().trim();}
  function apply(){
    var q=val(search), b=val(box), t=type ? type.value : '', s=val(status);
    filtered=rows.filter(function(row){
      var ok=true;
      if(q) ok=ok && (row.dataset.search||'').indexOf(q)!==-1;
      if(b) ok=ok && (row.dataset.box||'')===b;
      if(t) ok=ok && (row.dataset.type||'')===t;
      if(s) ok=ok && (row.dataset.status||'')===s;
      return ok;
    });
    page=1;
    render();
  }
  function render(){
    var total=filtered.length;
    var pages=Math.max(1, Math.ceil(total/perPage));
    if(page>pages) page=pages;
    rows.forEach(function(row){ row.style.display='none'; });
    filtered.slice((page-1)*perPage, page*perPage).forEach(function(row){ row.style.display='grid'; });
    if(count) count.textContent=total + ' win' + (total===1?'':'s') + ' found';
    if(pageNum) pageNum.textContent=page + ' / ' + pages;
    if(pageInfo){
      var start=total ? ((page-1)*perPage+1) : 0;
      var end=Math.min(page*perPage,total);
      pageInfo.textContent= total ? ('Showing ' + start + ' to ' + end + ' of ' + total) : 'No wins match your filters';
    }
    if(prev) prev.disabled=page<=1;
    if(next) next.disabled=page>=pages;
    if(pagination) pagination.style.display=rows.length ? 'flex' : 'none';
  }
  [search,box,type,status].forEach(function(el){ if(el) el.addEventListener('input', apply); if(el) el.addEventListener('change', apply); });
  if(prev) prev.addEventListener('click', function(){ if(page>1){page--; render();} });
  if(next) next.addEventListener('click', function(){ var pages=Math.max(1, Math.ceil(filtered.length/perPage)); if(page<pages){page++; render();} });
  root.addEventListener('click', function(e){
    var btn=e.target.closest('.lb-copy-code');
    if(!btn) return;
    var code=btn.getAttribute('data-copy')||'';
    if(!code) return;
    var old=btn.innerHTML;
    var done=function(){ btn.innerHTML='<i class="fa-solid fa-check"></i> Copied'; setTimeout(function(){btn.innerHTML=old;},1400); };
    if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(code).then(done).catch(function(){ prompt('Copy code', code); }); }
    else { prompt('Copy code', code); }
  });
  render();
})();
</script>
<?= $this->end() ?>
