<?= $this->layout('client/layouts/main', ['meta' => $meta ?? ['title' => 'Reward Box']]) ?>
<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$coins = (float)($client['reward_points'] ?? 0);
$price = (float)($box['price_coins'] ?? 0);
$free = $price <= 0;
$rarityClass = function($r){ $r = strtolower((string)$r); return in_array($r, ['common','uncommon','rare','epic','legendary'], true) ? $r : 'common'; };
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
$rewardAssetBase = ASSET_URL . '/website/images/rewards/';
$coinAssetBase = BASE_URL . '/public/assets/website/images/coins/';
$rewardPointsIconUrl = $coinAssetBase . 'reward-points.png';
$lbCoinsIconUrl = $coinAssetBase . 'coin_purple.png';
$boxAssetBase = $rewardAssetBase . 'boxes/';
$boxImageUrl = function($box) use ($boxAssetBase){
    $slug = trim((string)($box['slug'] ?? ''));
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

    foreach ($map as $key => $file) {
        if (str_contains($slug, $key)) return $boxAssetBase . $file;
    }

    return $boxAssetBase . $slug . '.png';
};
$renderBoxIcon = function($box, string $class = 'lb-box-img') use ($h, $boxImageUrl){
    $img = $boxImageUrl($box);
    if ($img !== '') return '<img class="' . $h($class) . '" src="' . $h($img) . '" alt="' . $h($box['name'] ?? 'Reward Box') . '">';
    return '<i class="fa-duotone fa-gift"></i>';
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

    if ($type === 'wallet_credit') {
        return $amount >= 5 ? $rewardAssetBase . '5storecredits.png' : $rewardAssetBase . '2storecredits.png';
    }

    if ($type === 'priority_boost') return $rewardAssetBase . 'priority.png';
    if ($type === 'champion_preference') return $rewardAssetBase . 'champion.png';

    return '';
};
$coinIconUrl = fn($value = 0) => $rewardPointsIconUrl;
$rewardIconHtml = function($item, string $class = 'lb-reward-img') use ($h, $rewardImageUrl){
    $img = $rewardImageUrl($item);
    if ($img !== '') {
        return '<img class="' . $h($class) . '" src="' . $h($img) . '" alt="Reward">';
    }
    $icon = trim((string)($item['icon'] ?? '')) ?: 'fa-gift';
    return '<i class="fa-duotone ' . $h($icon) . '"></i>';
};
$itemsForJs = [];
foreach (($items ?? []) as $it) {
  $itemsForJs[] = [
    'id' => (int)($it['id'] ?? 0),
    'name' => (string)($it['name'] ?? 'Reward'),
    'rarity' => (string)($it['rarity'] ?? 'common'),
    'icon' => (string)($it['icon'] ?? 'fa-gift'),
    'reward_type' => (string)($it['reward_type'] ?? ''),
    'reward_value' => (string)($it['reward_value'] ?? ''),
    'coin_icon' => (string)$coinIconUrl($it['reward_value'] ?? 0),
    'reward_image' => (string)$rewardImageUrl($it),
  ];
}
$nextAvailableRaw = (string)($next_available_at ?? '');
$nextAvailableIso = $nextAvailableRaw !== '' ? date(DATE_ATOM, strtotime($nextAvailableRaw)) : '';
?>
<?= $this->start('styles') ?>
<style>
.lb-reward-view{--line:rgba(255,255,255,.08);--soft:rgba(255,255,255,.045);--text:rgba(255,255,255,.92);--muted:rgba(255,255,255,.55);--blue:#6d8cff}.lb-back{display:inline-flex;gap:7px;align-items:center;color:#8fb2ff;font-weight:900;text-decoration:none;margin-bottom:16px}.lb-box-head{border:1px solid rgba(109,140,255,.24);border-radius:24px;background:radial-gradient(900px 260px at 90% 0%,rgba(109,140,255,.15),transparent 60%),linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.018));padding:26px;display:grid;grid-template-columns:220px 1fr;gap:26px;align-items:center;box-shadow:0 18px 48px rgba(0,0,0,.24)}.lb-big-box{height:190px;border:1px solid rgba(109,140,255,.22);border-radius:22px;background:radial-gradient(circle at 50% 20%,rgba(109,140,255,.28),rgba(0,0,0,.14) 62%);display:flex;align-items:center;justify-content:center}.lb-big-box i{font-size:5rem;color:#8fb2ff;filter:drop-shadow(0 18px 30px rgba(109,140,255,.25))}.lb-box-img{width:160px;height:160px;object-fit:contain;filter:drop-shadow(0 20px 32px rgba(109,140,255,.30))}.lb-eye{font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;font-weight:950;color:#8fb2ff;margin-bottom:8px}.lb-title{font-size:2rem;font-weight:950;color:var(--text);margin:0 0 10px}.lb-desc{color:var(--muted);font-weight:650;max-width:720px}.lb-tags{display:flex;gap:8px;flex-wrap:wrap;margin:13px 0 18px}.lb-tag{border:1px solid var(--line);background:var(--soft);border-radius:999px;padding:7px 10px;font-size:.78rem;font-weight:900;color:rgba(255,255,255,.82)}.lb-open{border:0;border-radius:14px;background:linear-gradient(135deg,#6d8cff,#7c5cff);color:white;font-weight:950;padding:13px 28px;display:inline-flex;gap:10px;align-items:center;justify-content:center}.lb-open:disabled{opacity:.65;cursor:not-allowed;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.09)}.lb-cd{font-variant-numeric:tabular-nums;font-feature-settings:'tnum';letter-spacing:.02em}.lb-cooldown-note{display:inline-flex;align-items:center;gap:7px;margin-top:10px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.16);border-radius:999px;padding:7px 10px;color:rgba(255,255,255,.68);font-size:.8rem;font-weight:900}.lb-cooldown-note i{color:#9db7ff}.lb-pool-note{display:flex;align-items:center;gap:9px;margin:22px 0 12px;padding:11px 13px;border:1px solid rgba(109,140,255,.25);border-radius:13px;background:rgba(109,140,255,.08);color:rgba(255,255,255,.78);font-size:.86rem;font-weight:800}.lb-pool-note i{color:#9db7ff}.lb-items{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-top:12px}.lb-item{border:1px solid var(--line);background:var(--soft);border-radius:18px;padding:15px;min-height:145px;display:flex;flex-direction:column;justify-content:space-between}.lb-item-icon{height:62px;border-radius:14px;background:rgba(0,0,0,.18);display:flex;align-items:center;justify-content:center;margin-bottom:10px}.lb-item-icon i{font-size:1.7rem;color:#9db7ff}.lb-reward-img{width:70px;height:70px;object-fit:contain;filter:drop-shadow(0 12px 20px rgba(109,140,255,.18))}.lb-reward-img.big{width:120px;height:120px}.lb-reward-img.spin{width:84px;height:84px}.lb-rarity{display:inline-flex;border-radius:999px;padding:4px 8px;font-size:.65rem;text-transform:uppercase;font-weight:950;letter-spacing:.04em;margin-bottom:7px}.lb-rarity.common{background:rgba(148,163,184,.14);color:#cbd5e1}.lb-rarity.uncommon{background:rgba(34,197,94,.14);color:#86efac}.lb-rarity.rare{background:rgba(96,165,250,.15);color:#93c5fd}.lb-rarity.epic{background:rgba(217,70,239,.16);color:#f0abfc}.lb-rarity.legendary{background:rgba(250,204,21,.16);color:#fde68a}.lb-item b{color:var(--text)}.lb-item small{color:var(--muted)}.lb-result{display:none;border:1px solid rgba(109,140,255,.28);background:rgba(109,140,255,.08);border-radius:22px;padding:18px;margin-top:16px}.lb-result.show{display:block}.lb-result-title{font-size:1.35rem;font-weight:950;color:var(--text);margin:0 0 6px}.lb-result-code{display:inline-flex;margin-top:10px;background:rgba(0,0,0,.28);border:1px solid var(--line);border-radius:10px;padding:8px 10px;color:#dbeafe;font-weight:950}
.lb-spin-modal{position:fixed;inset:0;z-index:1040;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(5,10,18,.78);backdrop-filter:blur(8px)}.lb-spin-modal.show{display:flex}.lb-spin-card{width:min(1080px,96vw);border:1px solid rgba(109,140,255,.24);border-radius:26px;background:radial-gradient(1200px 420px at 50% 0%,rgba(109,140,255,.14),transparent 60%),linear-gradient(180deg,rgba(13,19,33,.98),rgba(7,11,20,.98));box-shadow:0 28px 90px rgba(0,0,0,.44);overflow:hidden}.lb-spin-top{padding:20px 22px 12px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;gap:16px;align-items:center}.lb-spin-title{margin:0;color:rgba(255,255,255,.96);font-size:1.45rem;font-weight:950}.lb-spin-sub{color:rgba(255,255,255,.58);font-weight:750}.lb-spin-close{display:none;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;border-radius:12px;padding:10px 14px;font-weight:900}.lb-spin-wrap{padding:22px}.lb-reel-viewport{position:relative;overflow:hidden;border:1px solid rgba(109,140,255,.22);border-radius:22px;padding:18px 0;background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.02))}.lb-reel-viewport:before,.lb-reel-viewport:after{content:'';position:absolute;top:0;bottom:0;width:13%;z-index:2;pointer-events:none}.lb-reel-viewport:before{left:0;background:linear-gradient(90deg,rgba(7,11,20,.95),rgba(7,11,20,0))}.lb-reel-viewport:after{right:0;background:linear-gradient(270deg,rgba(7,11,20,.95),rgba(7,11,20,0))}.lb-reel-center{position:absolute;top:0;left:50%;transform:translateX(-50%);width:0;height:0;z-index:3}.lb-reel-center:before{content:'';position:absolute;left:50%;top:0;transform:translateX(-50%);border-left:14px solid transparent;border-right:14px solid transparent;border-top:14px solid #6d8cff;filter:drop-shadow(0 6px 16px rgba(109,140,255,.25))}.lb-reel-track{display:flex;gap:14px;will-change:transform;padding:4px 16px}.lb-spin-item{width:210px;min-width:210px;min-height:220px;border:1px solid rgba(255,255,255,.09);background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.022));border-radius:20px;padding:18px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}.lb-spin-item .lb-item-icon{width:100%;height:88px;margin-bottom:14px}.lb-spin-item .lb-item-icon i{font-size:2rem}.lb-spin-name{color:#fff;font-weight:900;font-size:1.1rem;line-height:1.25;margin-top:8px}.lb-spin-type{color:rgba(255,255,255,.62);font-size:.88rem;font-weight:700;margin-top:6px}.lb-spin-item.is-winning{border-color:rgba(109,140,255,.75);box-shadow:0 0 0 1px rgba(109,140,255,.32),0 22px 44px rgba(109,140,255,.18);transform:scale(1.02);transition:border-color .25s ease, box-shadow .25s ease, transform .25s ease}.lb-spin-result{display:none;grid-template-columns:220px 1fr;gap:18px;align-items:center;margin-top:18px;padding:18px;border:1px solid rgba(109,140,255,.22);background:rgba(109,140,255,.08);border-radius:22px}.lb-spin-result.show{display:grid}.lb-spin-result-visual{display:flex;align-items:center;justify-content:center;min-height:190px;border-radius:20px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02)}.lb-spin-result-visual i{font-size:4rem;color:#9db7ff}.lb-spin-result-text h3{font-size:2rem;margin:4px 0 8px;color:#fff;font-weight:950}.lb-spin-result-text p{margin:0;color:rgba(255,255,255,.7);font-weight:750}.lb-spin-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}.lb-spin-btn{border:0;border-radius:12px;padding:11px 16px;font-weight:900}.lb-spin-btn.primary{background:linear-gradient(135deg,#6d8cff,#7c5cff);color:#fff}.lb-spin-btn.ghost{background:rgba(255,255,255,.05);color:#fff;border:1px solid rgba(255,255,255,.08)}.lb-spin-code{display:inline-flex;margin-top:10px;background:rgba(0,0,0,.28);border:1px solid var(--line);border-radius:10px;padding:8px 10px;color:#dbeafe;font-weight:950}.lb-open-pulse{animation:lbGlow 1.15s ease-in-out infinite alternate}@keyframes lbGlow{from{box-shadow:0 0 0 rgba(109,140,255,.18)}to{box-shadow:0 0 28px rgba(109,140,255,.3)}}
@media(max-width:768px){.lb-box-head{grid-template-columns:1fr}.lb-title{font-size:1.55rem}.lb-spin-result{grid-template-columns:1fr}.lb-spin-item{width:170px;min-width:170px;min-height:200px}.lb-spin-wrap{padding:16px}.lb-spin-title{font-size:1.2rem}.lb-spin-sub{font-size:.92rem}}
</style>
<?= $this->end() ?>
<div class="lb-reward-view">
  <a class="lb-back" href="<?= BASE_URL ?>/profile/rewards"><i class="fa-solid fa-arrow-left"></i> Back to reward boxes</a>
  <div class="lb-box-head">
    <div class="lb-big-box"><?= $renderBoxIcon($box) ?></div>
    <div>
      <div class="lb-eye">LoLBoost Rewards</div>
      <h1 class="lb-title"><?= $h($box['name'] ?? 'Reward Box') ?></h1>
      <p class="lb-desc"><?= $h($box['description'] ?? '') ?></p>
      <div class="lb-tags">
        <span class="lb-tag"><?= $free ? 'Free' : number_format($price, 0) . ' Reward Points' ?></span>
        <span class="lb-tag"><i class="fa-solid fa-dice me-1"></i> 1 random reward</span>
        <span class="lb-tag"><img src="<?= $h($coinIconUrl($coins)) ?>" alt="Reward Points" style="width:16px;height:16px;margin-right:5px;vertical-align:-3px;">Balance: <span id="lbRewardBalance"><?= number_format($coins, 2) ?></span> Reward Points</span>
      </div>
      <button class="lb-open <?= !empty($can_open) ? 'lb-open-pulse' : '' ?>" id="lbOpenBox" data-box-id="<?= (int)($box['id'] ?? 0) ?>" <?= !empty($can_open) ? '' : 'disabled' ?>>
        <?php if (!empty($can_open)): ?>
          <i class="fa-duotone fa-sparkles"></i> Open Reward Box
        <?php elseif ($nextAvailableIso !== ''): ?>
          <i class="fa-duotone fa-clock"></i> Waiting to open <span class="lb-cd" data-lb-countdown="<?= $h($nextAvailableIso) ?>">--:--:--</span>
        <?php else: ?>
          <i class="fa-duotone fa-clock"></i> Available later
        <?php endif; ?>
      </button>
    </div>
  </div>

  <div class="lb-result" id="lbRewardResult">
    <div class="lb-rarity common" id="lbRewardRarity">Common</div>
    <h2 class="lb-result-title" id="lbRewardName">Reward</h2>
    <div style="color:rgba(255,255,255,.65);font-weight:800;" id="lbRewardText">Your reward has been added.</div>
    <div class="lb-result-code" id="lbRewardCode" style="display:none;"></div>
  </div>

  <div class="lb-pool-note">
    <i class="fa-solid fa-circle-info"></i>
    <span>Opening this box awards <strong>one random reward</strong>. The items below are the possible rewards.</span>
  </div>
  <h3 style="color:rgba(255,255,255,.92);font-weight:950;margin:0 0 12px;">Possible rewards</h3>
  <div class="lb-items">
    <?php foreach (($items ?? []) as $item): $r=$rarityClass($item['rarity'] ?? 'common'); ?>
      <div class="lb-item">
        <div>
          <div class="lb-item-icon"><?= $rewardIconHtml($item) ?></div>
          <span class="lb-rarity <?= $r ?>"><?= $h($r) ?></span><br>
          <b><?= $h($item['name'] ?? 'Reward') ?></b>
        </div>
        <small><?= $h($typeLabel($item['reward_type'] ?? '', $item['reward_value'] ?? '')) ?></small>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="lb-spin-modal" id="lbSpinModal" aria-hidden="true">
  <div class="lb-spin-card">
    <div class="lb-spin-top">
      <div>
        <h2 class="lb-spin-title">Opening <?= $h($box['name'] ?? 'Reward Box') ?></h2>
        <div class="lb-spin-sub" id="lbSpinSub">The rewards are rolling, good luck.</div>
      </div>
      <button type="button" class="lb-spin-close" id="lbSpinCloseTop">Close</button>
    </div>
    <div class="lb-spin-wrap">
      <div class="lb-reel-viewport">
        <div class="lb-reel-center"></div>
        <div class="lb-reel-track" id="lbSpinTrack"></div>
      </div>
      <div class="lb-spin-result" id="lbSpinResult">
        <div class="lb-spin-result-visual"><i class="fa-duotone fa-gift" id="lbSpinResultIcon"></i></div>
        <div class="lb-spin-result-text">
          <div class="lb-rarity common" id="lbSpinResultRarity">Common</div>
          <h3 id="lbSpinResultName">Reward</h3>
          <p id="lbSpinResultText">Your reward has been added.</p>
          <div class="lb-spin-code" id="lbSpinResultCode" style="display:none;"></div>
          <div class="lb-spin-actions">
            <button type="button" class="lb-spin-btn primary" id="lbSpinClose">Awesome</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->start('scripts') ?>
<script>
(function(){
  const btn=document.getElementById('lbOpenBox');
  const result=document.getElementById('lbRewardResult');
  const modal=document.getElementById('lbSpinModal');
  const track=document.getElementById('lbSpinTrack');
  const spinResult=document.getElementById('lbSpinResult');
  const spinClose=document.getElementById('lbSpinClose');
  const spinCloseTop=document.getElementById('lbSpinCloseTop');
  const itemsData=<?= json_encode($itemsForJs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;
  let spinning=false;
  if(!btn) return;
  const ajax=(typeof AJAX_URL!=='undefined'&&AJAX_URL)?AJAX_URL:(typeof ajax_url!=='undefined'?ajax_url:'/ajax');

  function initCountdowns(){
    const nodes=[...document.querySelectorAll('[data-lb-countdown]')];
    if(!nodes.length) return;
    let didReload=false;
    function pad(n){ return String(Math.max(0,n)).padStart(2,'0'); }
    function parseTarget(value){
      if(!value) return null;
      const date=new Date(String(value).replace(' ', 'T'));
      return Number.isNaN(date.getTime()) ? null : date;
    }
    function tick(){
      const now=Date.now();
      let finished=false;
      nodes.forEach(function(node){
        const target=parseTarget(node.dataset.lbCountdown || '');
        if(!target){ node.textContent='--:--:--'; return; }
        const diff=Math.max(0, target.getTime()-now);
        const total=Math.floor(diff/1000);
        const h=Math.floor(total/3600);
        const m=Math.floor((total%3600)/60);
        const s=total%60;
        node.textContent=pad(h)+':'+pad(m)+':'+pad(s);
        if(diff <= 0) finished=true;
      });
      if(finished && !didReload){
        didReload=true;
        setTimeout(function(){ window.location.reload(); }, 900);
      }
    }
    tick();
    setInterval(tick,1000);
  }
  initCountdowns();

  function label(type,value){
    value=String(value||'');
    if(type==='discount_coupon') return value+'% coupon';
    if(type==='wallet_credit') return value+'€ wallet credit';
    if(type==='reward_points') return value+' Reward Points';
    if(type==='lb_coins') return value+' LB Coins';
    if(type==='priority_boost') return 'Priority Queue Boost';
    if(type==='champion_preference') return 'Champion Preference';
    return 'Reward';
  }

  function pickVisual(item){
    if(item && item.icon) return item.icon;
    if(item && item.reward_type==='discount_coupon') return 'fa-ticket';
    if(item && item.reward_type==='wallet_credit') return 'fa-wallet';
    if(item && item.reward_type==='priority_boost') return 'fa-bolt';
    if(item && item.reward_type==='champion_preference') return 'fa-swords';
    return 'fa-gift';
  }

  function rewardImageFor(item){
    item=item||{};
    const type=String(item.reward_type||'');
    const amount=Number(item.reward_value||0);
    const base='<?= ASSET_URL ?>/website/images/rewards/';
    const rewardPointsIcon='<?= BASE_URL ?>/public/assets/website/images/coins/reward-points.png';
    const lbCoinsIcon='<?= BASE_URL ?>/public/assets/website/images/coins/coin_purple.png';
    if(item.reward_image) return item.reward_image;
    if(type==='reward_points') return rewardPointsIcon;
    if(type==='lb_coins') return lbCoinsIcon;
    if(type==='discount_coupon'){
      if(amount>=15) return base+'15discount.png';
      if(amount>=10) return base+'10discount.png';
      return base+'5discount.png';
    }
    if(type==='wallet_credit') return amount>=5 ? base+'5storecredits.png' : base+'2storecredits.png';
    if(type==='priority_boost') return base+'priority.png';
    if(type==='champion_preference') return base+'champion.png';
    return '';
  }

  function iconHtml(item, cls){
    item=item||{};
    const img=rewardImageFor(item);
    if(img){
      return '<img class="lb-reward-img '+(cls||'')+'" src="'+escapeHtml(img)+'" alt="Reward">';
    }
    return '<i class="fa-duotone '+pickVisual(item)+'"></i>';
  }

  function normalizeItem(item){
    const base={id:0,name:'Reward',rarity:'common',icon:'fa-gift',reward_type:'',reward_value:''};
    return Object.assign(base,item||{});
  }

  function buildSpinItems(winner){
    const normalizedWinner=normalizeItem(winner);
    const source=(itemsData&&itemsData.length?itemsData:[normalizedWinner]).map(normalizeItem);
    const stopIndex=18;
    const total=26;
    const out=[];
    for(let i=0;i<total;i++){
      if(i===stopIndex){
        out.push(Object.assign({}, normalizedWinner, {__winning:true}));
      } else {
        const rand=source[Math.floor(Math.random()*source.length)] || normalizedWinner;
        out.push(Object.assign({}, rand, {__winning:false}));
      }
    }
    return {items:out, stopIndex:stopIndex};
  }

  function renderSpinItem(item){
    const rarity=(item.rarity||'common').toLowerCase();
    const el=document.createElement('div');
    el.className='lb-spin-item';
    el.innerHTML=''
      +'<div class="lb-item-icon">'+iconHtml(item,'spin')+'</div>'
      +'<span class="lb-rarity '+rarity+'">'+rarity+'</span>'
      +'<div class="lb-spin-name">'+escapeHtml(item.name||'Reward')+'</div>'
      +'<div class="lb-spin-type">'+escapeHtml(label(item.reward_type,item.reward_value))+'</div>';
    return el;
  }

  function escapeHtml(value){
    return String(value||'').replace(/[&<>"']/g,function(m){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);});
  }

  function updateInlineResult(item,balance){
    const rarity=(item.rarity||'common').toLowerCase();
    document.getElementById('lbRewardRarity').className='lb-rarity '+rarity;
    document.getElementById('lbRewardRarity').textContent=rarity;
    document.getElementById('lbRewardName').textContent=item.name||'Reward';
    document.getElementById('lbRewardText').textContent='You won '+label(item.reward_type,item.reward_value)+'.';
    const code=document.getElementById('lbRewardCode');
    if(item.coupon_code){ code.style.display='inline-flex'; code.textContent=item.coupon_code; } else { code.style.display='none'; code.textContent=''; }
    const bal=document.getElementById('lbRewardBalance');
    if(bal&&typeof balance!=='undefined') bal.textContent=Number(balance).toFixed(2);
    result.classList.add('show');
  }

  function updateModalResult(item){
    const rarity=(item.rarity||'common').toLowerCase();
    const code=document.getElementById('lbSpinResultCode');
    document.getElementById('lbSpinResultRarity').className='lb-rarity '+rarity;
    document.getElementById('lbSpinResultRarity').textContent=rarity;
    document.getElementById('lbSpinResultName').textContent=item.name||'Reward';
    document.getElementById('lbSpinResultText').textContent='You won '+label(item.reward_type,item.reward_value)+'.';
    document.querySelector('.lb-spin-result-visual').innerHTML=iconHtml(item,'big');
    if(item.coupon_code){ code.style.display='inline-flex'; code.textContent=item.coupon_code; } else { code.style.display='none'; code.textContent=''; }
    spinResult.classList.add('show');
    spinClose.style.display='inline-flex';
    spinCloseTop.style.display='inline-flex';
    document.getElementById('lbSpinSub').textContent='Congratulations, your reward is ready.';
  }

  function showModal(){
    modal.classList.add('show');
    modal.setAttribute('aria-hidden','false');
    document.body.style.overflow='hidden';
  }

  function hideModal(){
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden','true');
    document.body.style.overflow='';
    track.style.transform='translateX(0px)';
    track.style.transition='none';
    track.innerHTML='';
    spinResult.classList.remove('show');
    spinClose.style.display='none';
    spinCloseTop.style.display='none';
    document.getElementById('lbSpinSub').textContent='The rewards are rolling, good luck.';
    spinning=false;
  }

  function playAnimation(data){
    const item=normalizeItem(data.item||{});
    const built=buildSpinItems(item);
    track.innerHTML='';
    built.items.forEach(function(it){ track.appendChild(renderSpinItem(it)); });
    showModal();

    requestAnimationFrame(function(){
      const viewport=track.parentElement;
      const tiles=track.querySelectorAll('.lb-spin-item');
      if(!tiles.length){
        updateModalResult(item);
        updateInlineResult(item,data.balance);
        return;
      }
      const tileWidth=tiles[0].offsetWidth;
      const trackStyles=window.getComputedStyle(track);
      const gap=parseFloat(trackStyles.gap||14) || 14;
      const viewportWidth=viewport.clientWidth;
      const targetCenter=(built.stopIndex * (tileWidth + gap)) + (tileWidth / 2);
      const targetTranslate=Math.max(0, targetCenter - (viewportWidth / 2));
      const extraJitter=Math.min(18, tileWidth * 0.08);
      const finalTranslate=targetTranslate + extraJitter;
      track.style.transition='transform 7.2s cubic-bezier(.06,.78,.12,1)';
      track.style.transform='translateX(-'+finalTranslate+'px)';

      window.setTimeout(function(){
        const winningEl=tiles[built.stopIndex];
        if(winningEl){ winningEl.classList.add('is-winning'); }
        updateModalResult(item);
        updateInlineResult(item,data.balance);
        spinning=false;
      }, 7450);
    });
  }

  spinClose.addEventListener('click', function(){ hideModal(); result.scrollIntoView({behavior:'smooth',block:'center'}); });
  spinCloseTop.addEventListener('click', function(){ if(!spinning) hideModal(); });
  modal.addEventListener('click', function(e){ if(e.target===modal && !spinning) hideModal(); });

  btn.addEventListener('click',function(){
    if(btn.disabled || spinning) return;
    const old=btn.innerHTML;
    spinning=true;
    btn.disabled=true;
    btn.innerHTML='<i class="fa-duotone fa-spinner-third fa-spin"></i> Opening...';
    const fd=new FormData();
    fd.append('action','client_reward_open_box');
    fd.append('box_id',btn.dataset.boxId||'0');
    fetch(ajax,{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(async r=>{
        const responseText=await r.text();
        let data=null;
        try{
          data=JSON.parse(responseText);
        }catch(_e){
          const first=responseText.indexOf('{');
          const last=responseText.lastIndexOf('}');
          if(first !== -1 && last !== -1 && last > first){
            try{ data=JSON.parse(responseText.slice(first,last+1)); }catch(_e2){}
          }
        }
        if(!data){
          const clean=responseText.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim();
          const preview=clean ? clean.slice(0,220) : (r.ok ? 'Invalid server response' : ('Server returned HTTP '+r.status));
          throw new Error(preview);
        }
        if(!r.ok){ throw new Error((data&&data.message) ? data.message : ('Server returned HTTP '+r.status)); }
        return data;
      })
      .then(d=>{
        if(!d||!d.success){ throw new Error((d&&d.message)||'Could not open reward box.'); }
        playAnimation(d);
      })
      .catch(err=>{
        spinning=false;
        alert((err&&err.message)?err.message:'Could not open reward box.');
      })
      .finally(()=>{ btn.disabled=false; btn.innerHTML=old; });
  });
})();
</script>
<?= $this->end() ?>
