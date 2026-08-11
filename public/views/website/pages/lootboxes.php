<?= $this->layout('website/layouts/master', ['meta' => $meta ?? ['title' => 'Lootboxes | LoLBoost.gg'], 'bodyClass' => 'lootboxes-page']) ?>
<?php
$h = fn($v) => esc($v);
$isLoggedIn = !empty($is_client);
$coins = $isLoggedIn ? (float)($client['reward_points'] ?? 0) : 0;
$rarityClass = function($r){ $r = strtolower((string)$r); return in_array($r, ['common','uncommon','rare','epic','legendary'], true) ? $r : 'common'; };
$maskClientName = function($name){
    $name = trim((string)($name ?? 'Guest'));
    if ($name === '') $name = 'Guest';
    $first = mb_substr($name, 0, 1, 'UTF-8');
    $last = mb_substr($name, -1, 1, 'UTF-8');
    return $first . '*****' . $last;
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
$coinIconUrl = fn($value = 0) => $rewardPointsIconUrl;
$renderRewardIcon = function($item, string $class = 'lb-reward-img') use ($h, $rewardIcon, $rewardImageUrl){
    $img = $rewardImageUrl($item);
    if ($img !== '') {
        return '<img class="' . $h($class) . '" src="' . $h($img) . '" alt="Reward">';
    }
    return '<i class="fa-duotone ' . $h($rewardIcon($item)) . '"></i>';
};
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
$boxIcon = function($box){
    $slug = (string)($box['slug'] ?? '');
    if (str_contains($slug, 'daily')) return 'fa-gift';
    if (str_contains($slug, 'challenger')) return 'fa-trophy-star';
    if (str_contains($slug, 'diamond')) return 'fa-gem';
    if (str_contains($slug, 'gold')) return 'fa-crown';
    if (str_contains($slug, 'silver')) return 'fa-medal';
    return 'fa-box-open';
};
$renderBoxIcon = function($box, string $class = 'lb-box-img') use ($h, $boxImageUrl, $boxIcon){
    $img = $boxImageUrl($box);
    if ($img !== '') {
        return '<img class="' . $h($class) . '" src="' . $h($img) . '" alt="' . $h($box['name'] ?? 'Reward Box') . '">';
    }
    return '<i class="fa-duotone ' . $h($boxIcon($box)) . '"></i>';
};
?>
<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Lootboxes & Reward Boxes (full rebuild)
   ============================================================ */
.lootboxes-page main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.lb2-wrap{width:min(1400px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1;}

/* Dynamic backdrop */
.lb-loot-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden;background:
  radial-gradient(1200px 700px at 80% 6%, rgba(109,140,255,.20), transparent 60%),
  radial-gradient(900px 620px at 15% 12%, rgba(217,70,239,.10), transparent 58%),
  radial-gradient(1000px 700px at 50% 95%, rgba(56,189,248,.10), transparent 60%),
  linear-gradient(180deg,#0a0818 0%, #0e0c22 55%, #0a0818 100%);
}
.lb-loot-gridlines{position:fixed;inset:-2px;z-index:-1;pointer-events:none;opacity:.13;background-image:
  linear-gradient(to right, rgba(255,255,255,.06) 1px, transparent 1px),
  linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(closest-side at 50% 10%, black 0%, transparent 74%);
}
#lbLootStars{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;mix-blend-mode:screen;}
.lb-loot-star{position:absolute;left:var(--x,50vw);top:var(--y,50vh);width:var(--s,3px);height:var(--s,3px);border-radius:999px;background:rgba(255,255,255,.95);box-shadow:0 0 10px rgba(255,255,255,.65),0 0 22px rgba(109,140,255,.45);opacity:var(--o,.7);transform:translate3d(0,0,0) scale(.85);animation:lbLootStar var(--d,22s) linear infinite;animation-delay:var(--delay,0s);will-change:transform,opacity;}
@keyframes lbLootStar{
  0%{transform:translate3d(0,0,0) scale(.85);opacity:.1;}
  14%{opacity:var(--o,.7);}
  72%{opacity:var(--o,.7);}
  100%{transform:translate3d(var(--tx,-26vw),var(--ty,20vh),0) scale(1.12);opacity:.05;}
}
@media(max-width:820px){#lbLootStars{display:none;}}
@media(prefers-reduced-motion:reduce){.lb-loot-star{animation:none!important;}}

/* Card recipe, reused everywhere below */
.lb2-card{background:#13112a;border:1px solid rgba(109,140,255,.20);border-radius:20px;box-shadow:0 18px 46px rgba(0,0,0,.32);}

/* Buttons */
.lb2-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 22px;border-radius:12px;font-weight:800;font-size:.95rem;text-decoration:none;border:1px solid transparent;cursor:pointer;font-family:inherit;transition:transform .15s ease,filter .15s ease,border-color .15s ease,background .15s ease;}
.lb2-btn-primary{background:linear-gradient(135deg,#6d8cff,#7c5cff);color:#fff;box-shadow:0 14px 30px rgba(109,140,255,.3);}
.lb2-btn-primary:hover{color:#fff;filter:brightness(1.08);transform:translateY(-1px);}
.lb2-btn-ghost{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.85);}
.lb2-btn-ghost:hover{color:#fff;border-color:rgba(109,140,255,.4);background:rgba(109,140,255,.1);}
.lb2-btn.is-waiting{opacity:.55;pointer-events:none;}
.lb2-btn-full{width:100%;white-space:nowrap;padding-left:14px;padding-right:14px;}
.lb-cd{font-variant-numeric:tabular-nums;letter-spacing:.02em;}

/* Hero — centered, same visual DNA as the loyalty/landing pages */
.lb2-hero{position:relative;padding:8px 24px clamp(40px,5vw,60px);text-align:center;overflow:hidden;}
.lb2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 20px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.88);border:1px solid rgba(109,140,255,.4);background:rgba(15,14,32,.6);box-shadow:0 16px 40px rgba(0,0,0,.3),inset 0 1px 0 rgba(255,255,255,.08);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.lb2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);animation:lb2EyebrowPulse 2.2s ease-in-out infinite;}
@keyframes lb2EyebrowPulse{0%,100%{box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);}50%{box-shadow:0 0 0 9px rgba(109,140,255,.22),0 0 26px rgba(109,140,255,.85);}}
@media(prefers-reduced-motion:reduce){.lb2-eyebrow .dot{animation:none!important;}}
.lb2-hero-title{margin:0 auto 18px;max-width:820px;font-size:clamp(34px,5vw,58px);line-height:1.08;letter-spacing:-.02em;font-weight:950;color:#fff;text-transform:uppercase;text-shadow:0 20px 50px rgba(0,0,0,.5);}
.lb2-hero-title .accent{background-image:linear-gradient(92deg,#9db7ff 0%,#7c9bff 50%,#6d8cff 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6d8cff;}
.lb2-hero-sub{max-width:640px;margin:0 auto 28px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}
.lb2-hero-actions{display:flex;justify-content:center;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:34px;}
.lb2-balance{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(109,140,255,.3);background:rgba(109,140,255,.09);color:#fff;border-radius:12px;padding:0 16px;min-height:48px;font-weight:800;font-size:.92rem;}
.lb2-balance img{width:22px;height:22px;object-fit:contain;}
@media(max-width:640px){.lb2-hero-actions .lb2-btn{width:100%;max-width:340px;}}

/* Hero showcase — larger box art with an individual glow instead of a shared panel background. */
.lb2-hero-showcase{position:relative;display:flex;justify-content:center;flex-wrap:wrap;gap:20px;max-width:720px;margin:0 auto;padding:20px 8px;background:none;}
.lb2-hero-showcase-item{position:relative;width:88px;height:88px;flex:0 0 auto;display:flex;align-items:center;justify-content:center;animation:lb2Bob 4.6s ease-in-out infinite;}
.lb2-hero-showcase-item::before{content:"";position:absolute;inset:13%;border-radius:50%;background:rgba(109,140,255,.12);filter:blur(18px);box-shadow:0 0 28px rgba(109,140,255,.28),0 14px 34px rgba(0,0,0,.42);pointer-events:none;}
.lb2-hero-showcase-item:nth-child(2){animation-delay:.3s;}
.lb2-hero-showcase-item:nth-child(3){animation-delay:.6s;}
.lb2-hero-showcase-item:nth-child(4){animation-delay:.9s;}
.lb2-hero-showcase-item:nth-child(5){animation-delay:1.2s;}
.lb2-hero-showcase-item:nth-child(6){animation-delay:1.5s;}
.lb2-hero-showcase-item img,.lb2-hero-showcase-item i{position:relative;z-index:1;width:100%;height:100%;object-fit:contain;filter:drop-shadow(0 10px 10px rgba(0,0,0,.55)) drop-shadow(0 12px 24px rgba(109,140,255,.38));}
@keyframes lb2Bob{0%,100%{transform:translateY(0);}50%{transform:translateY(-7px);}}
@media(prefers-reduced-motion:reduce){.lb2-hero-showcase-item{animation:none!important;}}
@media(max-width:640px){.lb2-hero-showcase{gap:14px;padding-inline:0;}.lb2-hero-showcase-item{width:64px;height:64px;}}

/* Section rhythm + heading, reused for every section below the hero */
.lb2-section{padding:34px 0;scroll-margin-top:105px;}
.lb2-section + .lb2-section{border-top:1px solid rgba(255,255,255,.06);}
.lb2-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.lb2-section-head h2{margin:0;font-size:clamp(20px,2.2vw,26px);font-weight:900;color:#fff;letter-spacing:-.01em;display:flex;align-items:center;}
.lb2-section-sub{margin:6px 0 0;color:rgba(255,255,255,.5);font-size:.9rem;font-weight:650;}
.lb2-live-dot{display:inline-block;width:9px;height:9px;border-radius:50%;background:#22c55e;margin-right:10px;box-shadow:0 0 0 0 rgba(34,197,94,.55);animation:lb2Pulse 1.8s ease-out infinite;}
@keyframes lb2Pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.55);}70%{box-shadow:0 0 0 9px rgba(34,197,94,0);}100%{box-shadow:0 0 0 0 rgba(34,197,94,0);}}
@media(prefers-reduced-motion:reduce){.lb2-live-dot{animation:none!important;}}
.lb2-section-link{display:inline-flex;align-items:center;gap:8px;color:#9db7ff;font-weight:800;font-size:.88rem;text-decoration:none;}
.lb2-section-link:hover{color:#c7d6ff;}

/* Explainer — connected timeline */
.lb2-explain{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;}
.lb2-explain::before{content:"";position:absolute;top:29px;left:16%;right:16%;height:2px;z-index:0;background:linear-gradient(90deg,transparent,rgba(109,140,255,.6),rgba(157,183,255,.4),rgba(109,140,255,.6),transparent);}
.lb2-explain-item{position:relative;z-index:1;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px;}
.lb2-explain-item i{width:58px;height:58px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#0f0e22;border:1px solid rgba(109,140,255,.45);box-shadow:0 0 0 6px rgba(10,8,24,.9),0 14px 30px rgba(109,140,255,.24),inset 0 0 18px rgba(109,140,255,.28);color:#9db7ff;font-size:19px;animation:lb2Bob 4.4s ease-in-out infinite;transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease;}
.lb2-explain-item:nth-child(2) i{animation-delay:.35s;}
.lb2-explain-item:nth-child(3) i{animation-delay:.7s;}
.lb2-explain-item:hover i{transform:translateY(-6px) scale(1.08);border-color:rgba(109,140,255,.85);box-shadow:0 0 0 6px rgba(10,8,24,.9),0 0 32px rgba(109,140,255,.5);color:#fff;}
.lb2-explain-item b{display:block;color:#fff;font-size:.98rem;margin-bottom:4px;}
.lb2-explain-item p{margin:0;color:rgba(255,255,255,.55);font-size:.86rem;line-height:1.55;max-width:260px;}
@media(max-width:900px){.lb2-explain{grid-template-columns:1fr;gap:26px;}.lb2-explain::before{display:none;}}
@media(prefers-reduced-motion:reduce){.lb2-explain-item i{animation:none!important;}}

/* Recent wins strip */
.lb2-wins{display:flex;gap:14px;overflow-x:auto;padding:4px 4px 14px;scrollbar-width:thin;}
.lb2-win{flex:0 0 auto;width:200px;padding:16px;}
.lb2-win-visual{height:84px;border-radius:14px;background:rgba(0,0,0,.2);display:flex;align-items:center;justify-content:center;margin-bottom:12px;}
.lb2-win-visual i{font-size:2rem;color:#9db7ff;}
.lb2-win-visual img{width:76px;height:76px;object-fit:contain;filter:drop-shadow(0 10px 18px rgba(109,140,255,.24));}
.lb2-rarity{display:inline-flex;border-radius:999px;padding:4px 10px;font-size:.66rem;text-transform:uppercase;font-weight:900;letter-spacing:.04em;margin-bottom:8px;}
.lb2-rarity.common{background:rgba(148,163,184,.14);color:#cbd5e1;}
.lb2-rarity.uncommon{background:rgba(34,197,94,.14);color:#86efac;}
.lb2-rarity.rare{background:rgba(96,165,250,.15);color:#93c5fd;}
.lb2-rarity.epic{background:rgba(217,70,239,.16);color:#f0abfc;}
.lb2-rarity.legendary{background:rgba(250,204,21,.16);color:#fde68a;}
.lb2-win b{display:block;color:#fff;font-size:.95rem;line-height:1.2;}
.lb2-win small{display:block;color:rgba(255,255,255,.5);font-weight:700;margin-top:4px;font-size:.82rem;}
.lb2-win-who{display:flex;align-items:center;gap:9px;margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.09);}
.lb2-win-avatar{width:26px;height:26px;border-radius:50%;flex:0 0 26px;border:1px solid rgba(109,140,255,.35);background:rgba(109,140,255,.14);display:flex;align-items:center;justify-content:center;overflow:hidden;}
.lb2-win-avatar img{width:100%;height:100%;object-fit:cover;}
.lb2-win-avatar i{font-size:.7rem;color:#9db7ff;}
.lb2-win-who-name{font-size:.78rem;font-weight:800;color:rgba(255,255,255,.86);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.lb2-win-who-meta{font-size:.66rem;color:#8fb2ff;text-transform:uppercase;letter-spacing:.04em;font-weight:800;margin-top:1px;}

/* Reward box grid */
.lb2-box-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}
.lb2-box{position:relative;padding:20px;min-height:250px;display:flex;flex-direction:column;justify-content:space-between;transition:transform .18s ease,border-color .18s ease;}
.lb2-box:hover{transform:translateY(-3px);border-color:rgba(109,140,255,.45);}
.lb2-box-price{position:absolute;z-index:3;right:14px;top:14px;border:1px solid rgba(109,140,255,.38);background:rgba(4,8,18,.94);border-radius:999px;padding:6px 10px;font-weight:900;font-size:.7rem;color:#dbeafe;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 8px 20px rgba(0,0,0,.42);}
.lb2-box-price img{width:14px;height:14px;object-fit:contain;}
.lb2-box-price.free{color:#34d399;border-color:rgba(34,197,94,.3);background:rgba(7,30,18,.9);}
.lb2-box-visual{position:relative;z-index:1;height:110px;border-radius:16px;background:radial-gradient(circle at 50% 20%,rgba(109,140,255,.24),rgba(0,0,0,.1) 60%);display:flex;align-items:center;justify-content:center;margin-bottom:14px;}
.lb2-box-visual i{font-size:3.4rem;color:#8fb2ff;}
.lb2-box-visual img{width:112px;height:112px;object-fit:contain;filter:drop-shadow(0 16px 24px rgba(109,140,255,.26));}
.lb2-box-name{font-size:1.02rem;font-weight:900;color:#fff;margin:0 0 6px;}
.lb2-box-desc{font-size:.84rem;color:rgba(255,255,255,.55);min-height:36px;line-height:1.5;}
.lb2-box>.lb2-btn-full{margin-top:20px;}

/* My rewards grid */
.lb2-reward-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}
.lb2-reward{padding:16px;min-height:150px;}
.lb2-reward-visual{height:78px;border-radius:14px;background:rgba(0,0,0,.2);display:flex;align-items:center;justify-content:center;margin-bottom:10px;}
.lb2-reward-visual i{font-size:1.8rem;color:#9db7ff;}
.lb2-reward-visual img{width:70px;height:70px;object-fit:contain;filter:drop-shadow(0 10px 18px rgba(109,140,255,.22));}
.lb2-reward b{display:block;color:#fff;font-size:.96rem;}
.lb2-reward small{display:block;color:rgba(255,255,255,.5);font-weight:700;margin-top:4px;font-size:.82rem;}
.lb2-reward-meta{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px;}
.lb2-chip{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);border-radius:999px;padding:5px 9px;color:rgba(255,255,255,.62);font-size:.72rem;font-weight:750;}
.lb2-reward code{display:inline-flex;margin-top:10px;background:rgba(0,0,0,.25);border:1px solid rgba(109,140,255,.2);border-radius:9px;padding:6px 8px;color:#dbeafe;font-weight:900;font-size:.82rem;}

/* FAQ */
.lb2-faq-wrap{width:min(100%,980px);margin:0 auto;}
.lb2-faq-panel{position:relative;padding:0;background:none;border:0;box-shadow:none;}
.lb2-faq-head{position:relative;margin-bottom:20px;}
.lb2-faq-title{display:flex;align-items:center;gap:10px;margin:0;font-size:clamp(20px,2.2vw,26px);font-weight:900;letter-spacing:-.01em;color:#fff;}
.lb2-faq-title::before{content:"";width:9px;height:9px;border-radius:50%;background:#8fb2ff;box-shadow:0 0 0 5px rgba(109,140,255,.12),0 0 18px rgba(109,140,255,.48);}
.lb2-faq-sub{margin:7px 0 0;color:rgba(255,255,255,.48);font-size:.88rem;font-weight:650;}
.lb2-faq-list{position:relative;display:grid;grid-template-columns:1fr;gap:10px;}
.lb2-faq-item{border:1px solid rgba(109,140,255,.10);border-radius:20px;background:linear-gradient(145deg,rgba(10,16,36,.94),rgba(7,12,29,.96));overflow:hidden;margin:0;transition:border-color .18s ease,background .18s ease;}
.lb2-faq-item:hover{border-color:rgba(109,140,255,.24);}
.lb2-faq-item summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:76px;padding:20px 22px 20px 26px;color:#fff;font-weight:850;font-size:.95rem;}
.lb2-faq-item summary::-webkit-details-marker{display:none;}
.lb2-faq-chev{width:36px;height:36px;flex:0 0 36px;display:grid;place-items:center;border-radius:11px;border:1px solid rgba(143,178,255,.20);background:linear-gradient(180deg,rgba(143,178,255,.10),rgba(143,178,255,.045));box-shadow:inset 0 1px 0 rgba(255,255,255,.06);color:#9db7ff;font-size:12px;transition:border-color .2s,color .2s,background .2s,box-shadow .2s;}
.lb2-faq-chev i{transition:transform .3s cubic-bezier(.22,.61,.36,1);}
.lb2-faq-item p{margin:0;padding:0 70px 24px 26px;color:rgba(255,255,255,.60);font-size:.86rem;line-height:1.72;max-width:72ch;}
.lb2-faq-item[open]{border-color:rgba(109,140,255,.24);background:linear-gradient(145deg,rgba(12,20,46,.97),rgba(8,14,34,.98));box-shadow:inset 3px 0 0 rgba(109,140,255,.68);}
.lb2-faq-item[open] .lb2-faq-chev{border-color:rgba(109,140,255,.48);background:linear-gradient(135deg,rgba(52,92,246,.42),rgba(79,140,255,.25));box-shadow:0 8px 22px rgba(52,92,246,.18),inset 0 1px 0 rgba(255,255,255,.10);color:#fff;}
.lb2-faq-item[open] .lb2-faq-chev i{transform:rotate(180deg);}
@media(max-width:760px){.lb2-faq-head{margin-bottom:16px;}}
@media(max-width:640px){.lb2-section{padding:26px 0;}.lb2-faq-item summary{min-height:66px;padding:16px 14px 16px 18px;font-size:.88rem;}.lb2-faq-item p{padding:0 18px 20px;font-size:.82rem;}.lb2-faq-chev{width:34px;height:34px;flex-basis:34px;}}

/* Flat visual pass — aligned with the Jobs page */
.lootboxes-page{background:#030817!important;overflow-x:hidden;}
.lootboxes-page main{background:#030817!important;padding-bottom:96px;}
.lb2-wrap{width:min(1220px,calc(100% - 40px));}
.lb-loot-bg{background:#030817!important;}
.lb-loot-gridlines,#lbLootStars{display:none!important;}

.lb2-card{
  background:#090f24;
  border-color:rgba(255,255,255,.10);
  border-radius:18px;
  box-shadow:0 12px 34px rgba(0,0,0,.25);
}
.lb2-btn{border-radius:14px;box-shadow:none!important;}
.lb2-btn-primary{background:#5965e8;color:#fff;}
.lb2-btn-primary:hover{background:#6874f0;filter:none;}
.lb2-btn-ghost{background:#090f24;border-color:rgba(255,255,255,.11);}
.lb2-btn-ghost:hover{background:#0c1430;border-color:rgba(129,140,248,.4);}

.lb2-hero{padding:clamp(28px,4vw,52px) 24px clamp(48px,6vw,72px);}
.lb2-eyebrow{
  background:#090f24;
  border-color:rgba(129,140,248,.28);
  box-shadow:none;
}
.lb2-eyebrow .dot{background:#818cf8;box-shadow:none;animation:none;}
.lb2-hero-title{text-transform:none;text-shadow:none;max-width:880px;}
.lb2-hero-title .accent{
  background:none;
  color:#9ca5ff;
  -webkit-background-clip:initial;
  background-clip:initial;
  -webkit-text-fill-color:currentColor;
}
.lb2-hero-sub{color:rgba(238,244,255,.65);font-weight:500;max-width:700px;}
.lb2-balance{background:#090f24;border-color:rgba(255,255,255,.11);border-radius:14px;}
.lb2-hero-showcase{
  max-width:820px;
  gap:12px;
  padding:20px;
  border:1px solid rgba(255,255,255,.09);
  border-radius:24px;
  background:#090f24;
}
.lb2-hero-showcase-item{width:90px;height:90px;animation:none;}
.lb2-hero-showcase-item::before{display:none;}
.lb2-hero-showcase-item img,.lb2-hero-showcase-item i{filter:none;}

.lb2-section{padding:52px 0;}
.lb2-section + .lb2-section{border-top-color:rgba(255,255,255,.07);}
.lb2-section-head{margin-bottom:24px;}
.lb2-section-head h2{font-size:clamp(24px,2.6vw,34px);letter-spacing:-.025em;}
.lb2-section-sub{color:rgba(255,255,255,.5);}
.lb2-section-link{color:#a5b4fc;}
.lb2-live-dot{box-shadow:none;animation:none;}

.lb2-explain{gap:14px;}
.lb2-explain::before{display:none;}
.lb2-explain-item{
  align-items:flex-start;
  text-align:left;
  gap:9px;
  min-height:190px;
  padding:22px;
  border:1px solid rgba(255,255,255,.10);
  border-radius:18px;
  background:#090f24;
  box-shadow:0 12px 34px rgba(0,0,0,.25);
}
.lb2-explain-item i{
  width:44px;height:44px;
  border-radius:13px;
  background:#151d3b;
  border-color:rgba(129,140,248,.25);
  box-shadow:none;
  color:#a5b4fc;
  animation:none;
}
.lb2-explain-item:hover i{transform:none;box-shadow:none;border-color:rgba(129,140,248,.45);}
.lb2-explain-item b{font-size:1rem;margin-top:5px;}
.lb2-explain-item p{max-width:none;color:rgba(255,255,255,.52);}

.lb2-win{width:210px;}
.lb2-win-visual,.lb2-reward-visual{background:#060b1b;border:1px solid rgba(255,255,255,.06);}
.lb2-win-visual img,.lb2-reward-visual img{filter:none;}
.lb2-win-avatar{border-color:rgba(255,255,255,.14);background:#151d3b;}
.lb2-win-who-meta{color:#a5b4fc;}

.lb2-box-grid{grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;}
.lb2-box{padding:18px;min-height:300px;}
.lb2-box:hover{transform:translateY(-3px);border-color:rgba(129,140,248,.38);}
.lb2-box-price{background:#060b1b;border-color:rgba(255,255,255,.13);box-shadow:none;}
.lb2-box-price.free{background:#071a16;border-color:rgba(34,197,94,.28);}
.lb2-box-visual{
  height:142px;
  background:#060b1b;
  border:1px solid rgba(255,255,255,.06);
  border-radius:15px;
}
.lb2-box-visual img{width:126px;height:126px;filter:none;}
.lb2-box-name{font-size:1.08rem;}
.lb2-box-desc{color:rgba(255,255,255,.5);}

.lb2-reward-grid{gap:14px;}
.lb2-chip{background:#060b1b;}
.lb2-reward code{background:#060b1b;border-color:rgba(255,255,255,.1);}

.lb2-faq-wrap{max-width:900px;}
.lb2-faq-panel{
  padding:12px;
  border:1px solid rgba(96,165,250,.18);
  border-radius:26px;
  background:#080e21;
}
.lb2-faq-head{padding:12px 10px 4px;}
.lb2-faq-title::before{background:#818cf8;box-shadow:none;}
.lb2-faq-list{gap:10px;}
.lb2-faq-item,.lb2-faq-item[open]{
  background:#0b1228;
  border-color:rgba(255,255,255,.08);
  border-radius:18px;
  box-shadow:none;
}
.lb2-faq-item[open]{background:#0d1631;border-color:rgba(129,140,248,.28);}
.lb2-faq-chev,.lb2-faq-item[open] .lb2-faq-chev{
  background:#151d3b;
  border-color:rgba(255,255,255,.10);
  box-shadow:none;
  color:#a5b4fc;
}

@media(max-width:900px){
  .lb2-explain{grid-template-columns:1fr;}
  .lb2-explain-item{min-height:0;}
}
@media(max-width:640px){
  .lb2-wrap{width:min(100% - 24px,1220px);}
  .lb2-hero{padding-inline:0;}
  .lb2-hero-showcase{padding:14px 8px;}
  .lb2-hero-showcase-item{width:58px;height:58px;}
  .lb2-section{padding:36px 0;}
  .lb2-box-grid{grid-template-columns:1fr;}
}
</style>
<?= $this->end() ?>
<div class="lb-loot-bg" aria-hidden="true"></div>
<div class="lb-loot-gridlines" aria-hidden="true"></div>
<div id="lbLootStars" aria-hidden="true"></div>

<div class="lb2-wrap">

  <section class="lb2-hero">
    <div class="lb2-eyebrow"><span class="dot"></span><span>LoLBoost Rewards</span></div>
    <h1 class="lb2-hero-title">Open a box. <span class="accent">Win real rewards.</span></h1>
    <p class="lb2-hero-sub">Open LoLBoost lootboxes and win Reward Points, discount coupons, wallet credit and useful order perks. The Daily Gift is free every 24 hours after login.</p>
    <div class="lb2-hero-actions">
      <?php if ($isLoggedIn): ?>
        <div class="lb2-balance"><img src="<?= $h($coinIconUrl($coins)) ?>" alt="Reward Points"> <?= number_format($coins, 2) ?> Reward Points</div>
        <a class="lb2-btn lb2-btn-ghost" href="<?= BASE_URL ?>/profile/rewards/wins"><i class="fa-duotone fa-trophy-star"></i>My Wins</a>
      <?php else: ?>
        <button type="button" class="lb2-btn lb2-btn-primary" data-login-trigger="1"><i class="fa-duotone fa-right-to-bracket"></i>Login to open boxes</button>
      <?php endif; ?>
      <button type="button" class="lb2-btn lb2-btn-ghost" data-lb-scroll-target="lbRewardsFaq"><i class="fa-duotone fa-circle-question"></i>FAQ</button>
    </div>
    <div class="lb2-hero-showcase" aria-hidden="true">
      <?php foreach (($boxes ?? []) as $showcaseBox): ?>
        <div class="lb2-hero-showcase-item"><?= $renderBoxIcon($showcaseBox) ?></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="lb2-section">
    <div class="lb2-explain">
      <div class="lb2-explain-item">
        <i class="fa-solid fa-dice"></i>
        <b>What is a Lootbox?</b>
        <p>It's a mystery reward box. Open one and instantly get something useful — Reward Points, a discount coupon, wallet credit or a special order perk.</p>
      </div>
      <div class="lb2-explain-item">
        <i class="fa-solid fa-gift"></i>
        <b>Free every day</b>
        <p>Log in and claim the Daily Gift box for free every 24 hours. No purchase needed, no strings attached.</p>
      </div>
      <div class="lb2-explain-item">
        <i class="fa-solid fa-coins"></i>
        <b>Bigger boxes, bigger rewards</b>
        <p>Earn Reward Points automatically through the Loyalty Program, then spend them on Starter, Silver, Gold, Diamond or Challenger boxes with stronger rewards.</p>
      </div>
    </div>
  </section>

  <?php if (!empty($recent_wins)): ?>
  <section class="lb2-section">
    <div class="lb2-section-head">
      <h2><span class="lb2-live-dot"></span>Recent wins</h2>
      <p class="lb2-section-sub">Real players, real prizes — see who just won.</p>
    </div>
    <div class="lb2-wins">
      <?php foreach ($recent_wins as $win):
        $r = $rarityClass($win['rarity'] ?? 'common');
        $clientName = $maskClientName($win['username'] ?? ('Guest#' . (int)($win['client_id'] ?? 0)));
        $clientIcon = trim((string)($win['client_icon'] ?? ''));
      ?>
        <div class="lb2-card lb2-win">
          <span class="lb2-rarity <?= $r ?>"><?= $h($r) ?></span>
          <div class="lb2-win-visual"><?= $renderRewardIcon($win) ?></div>
          <b><?= $h($win['item_name'] ?? 'Reward') ?></b>
          <small><?= $h($typeLabel($win['reward_type'] ?? '', $win['reward_value'] ?? '')) ?></small>
          <div class="lb2-win-who">
            <div class="lb2-win-avatar">
              <?php if ($clientIcon !== ''): ?>
                <img src="<?= $h($clientIcon) ?>" alt="<?= $h($clientName) ?>">
              <?php else: ?>
                <i class="fa-duotone fa-user"></i>
              <?php endif; ?>
            </div>
            <div>
              <div class="lb2-win-who-name"><?= $h($clientName) ?></div>
              <div class="lb2-win-who-meta">opened <?= $h($win['box_name'] ?? 'Reward Box') ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="lb2-section">
    <div class="lb2-section-head">
      <h2>Choose a reward box</h2>
    </div>
    <div class="lb2-box-grid">
      <?php foreach (($boxes ?? []) as $box):
          $free=(float)($box['price_coins'] ?? 0)<=0;
          $can=!empty($box['can_open']);
          $boxNextRaw=(string)($box['next_available_at'] ?? '');
          $boxNextIso=$boxNextRaw !== '' ? date(DATE_ATOM, strtotime($boxNextRaw)) : '';
        ?>
        <div class="lb2-card lb2-box">
          <div class="lb2-box-price <?= $free ? 'free' : '' ?>">
            <?php if ($free): ?>
              Free
            <?php else: ?>
              <img src="<?= $h($rewardPointsIconUrl) ?>" alt="Reward Points">
              <?= number_format((float)$box['price_coins'], 0) ?> Reward Points
            <?php endif; ?>
          </div>
          <div>
            <div class="lb2-box-visual"><?= $renderBoxIcon($box) ?></div>
            <h3 class="lb2-box-name"><?= $h($box['name'] ?? 'Reward Box') ?></h3>
            <div class="lb2-box-desc"><?= $h($box['description'] ?? '') ?></div>
          </div>
          <a class="lb2-btn lb2-btn-primary lb2-btn-full <?= ($isLoggedIn && !$can) ? 'is-waiting' : '' ?>" href="<?= $isLoggedIn ? (BASE_URL . '/profile/rewards/' . $h($box['slug'] ?? '')) : '#' ?>" <?= $isLoggedIn ? '' : 'data-login-trigger="1"' ?>>
            <?php if (!$isLoggedIn): ?>
              Login to open <i class="fa-solid fa-right-to-bracket"></i>
            <?php elseif ($can): ?>
              View Reward Box <i class="fa-solid fa-arrow-right"></i>
            <?php elseif ($boxNextIso !== ''): ?>
              Waiting to open <span class="lb-cd" data-lb-countdown="<?= $h($boxNextIso) ?>">--:--:--</span>
            <?php else: ?>
              Available later
            <?php endif; ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($my_rewards)): ?>
  <section class="lb2-section">
    <div class="lb2-section-head">
      <h2>My latest rewards</h2>
      <a class="lb2-section-link" href="<?= BASE_URL ?>/profile/rewards/wins">View all wins <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="lb2-reward-grid">
      <?php foreach ($my_rewards as $reward): $r=$rarityClass($reward['rarity'] ?? 'common'); ?>
        <div class="lb2-card lb2-reward">
          <span class="lb2-rarity <?= $r ?>"><?= $h($r) ?></span>
          <div class="lb2-reward-visual"><?= $renderRewardIcon($reward) ?></div>
          <b><?= $h($reward['item_name'] ?? $reward['reward_type'] ?? 'Reward') ?></b>
          <small><?= $h($typeLabel($reward['reward_type'] ?? '', $reward['reward_value'] ?? '')) ?></small>
          <div class="lb2-reward-meta">
            <span class="lb2-chip"><i class="fa-duotone fa-circle-check"></i><?= $h(ucfirst((string)($reward['status'] ?? 'unused'))) ?></span>
            <?php if (!empty($reward['created_at'])): ?><span class="lb2-chip"><i class="fa-duotone fa-clock"></i><?= $h(date('d.m.Y', strtotime((string)$reward['created_at']))) ?></span><?php endif; ?>
          </div>
          <?php if (!empty($reward['coupon_code'])): ?><code><?= $h($reward['coupon_code']) ?></code><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="lb2-section" id="lbRewardsFaq">
    <div class="lb2-faq-wrap">
      <div class="lb2-faq-panel">
        <div class="lb2-faq-head">
          <div>
            <h2 class="lb2-faq-title">Frequently asked questions</h2>
            <p class="lb2-faq-sub">Everything you need to know before opening a reward box.</p>
          </div>
        </div>
        <div class="lb2-faq-list">
        <details class="lb2-faq-item" open>
          <summary><span>How often can I open the Daily Gift?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Once every 24 hours. If it is still on cooldown, the button shows the remaining time.</p>
        </details>
        <details class="lb2-faq-item">
          <summary><span>What can I win?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Reward Points, discount coupons, wallet credit and order perks such as Priority Order or Champion Preference.</p>
        </details>
        <details class="lb2-faq-item">
          <summary><span>Where are my previous wins?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Open My Wins at the top of the page. Wins are sorted by date and show reward, box, status and coupon code.</p>
        </details>
        <details class="lb2-faq-item">
          <summary><span>Are rewards added automatically?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Yes. Coins and wallet credit are credited instantly. Coupons and perks are saved to your account.</p>
        </details>
        <details class="lb2-faq-item">
          <summary><span>Can I open other boxes during Daily Gift cooldown?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Yes. The cooldown only blocks Daily Gift. Paid boxes stay available if you have enough Reward Points.</p>
        </details>
        <details class="lb2-faq-item">
          <summary><span>Do coupons expire?</span><span class="lb2-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></summary>
          <p>Some promotion coupons can expire. Check your reward details or contact support if a code does not work.</p>
        </details>
        </div>
      </div>
    </div>
  </section>

</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var holder = document.getElementById('lbLootStars');
  if (holder && !window.matchMedia('(max-width:820px)').matches) {
    var frag = document.createDocumentFragment();
    for (var i = 0; i < 28; i++) {
      var p = document.createElement('span');
      p.className = 'lb-loot-star';
      p.style.setProperty('--s', (Math.random() * 2.6 + 1.6).toFixed(2) + 'px');
      p.style.setProperty('--x', (Math.random() * 116 - 8).toFixed(2) + 'vw');
      p.style.setProperty('--y', (Math.random() * 70 - 4).toFixed(2) + 'vh');
      p.style.setProperty('--d', (Math.random() * 18 + 18).toFixed(2) + 's');
      p.style.setProperty('--delay', (-Math.random() * 34).toFixed(2) + 's');
      p.style.setProperty('--o', (Math.random() * .4 + .45).toFixed(2));
      p.style.setProperty('--tx', (Math.random() * 30 - 26).toFixed(2) + 'vw');
      p.style.setProperty('--ty', (Math.random() * 22 + 8).toFixed(2) + 'vh');
      frag.appendChild(p);
    }
    holder.appendChild(frag);
  }
})();
</script>
<script>
(function(){
  function setLootboxLoginReturnUrl(){
    const currentUrl = window.location.href;
    try {
      sessionStorage.setItem('lootboxes_after_login_url', currentUrl);
      localStorage.setItem('lootboxes_after_login_url', currentUrl);
    } catch (e) {}

    document.querySelectorAll('form').forEach(function(form){
      const actionInput = form.querySelector('input[name="action"]');
      const actionValue = actionInput ? String(actionInput.value || '') : '';
      const hasEmailPassword = !!(
        form.querySelector('input[name="email"], input[type="email"], input[name="login"]') &&
        form.querySelector('input[name="password"], input[type="password"]')
      );
      const isClientLogin = actionValue === 'auth_client_login' ||
        actionValue === 'auth_unified_login' ||
        form.id === 'clientLoginForm' ||
        form.classList.contains('client-login-form') ||
        hasEmailPassword;

      if (!isClientLogin) return;

      ['redirectUrl','redirect_after_login','redirect_url','return_url','returnUrl','back_url','current_url','stay_on_page'].forEach(function(name){
        let input = form.querySelector('input[name="' + name + '"]');
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          form.appendChild(input);
        }
        input.value = currentUrl;
      });

      let roleInput = form.querySelector('input[name="login_role"]');
      if (!roleInput) {
        roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'login_role';
        form.appendChild(roleInput);
      }
      roleInput.value = 'client';

      form.dataset.redirectUrl = currentUrl;
      form.dataset.redirect = currentUrl;
      form.setAttribute('data-redirect-url', currentUrl);
    });
  }

  function openLootboxLogin(event){
    if (event) event.preventDefault();
    setLootboxLoginReturnUrl();

    const headerButton = document.getElementById('login-btn') ||
      document.getElementById('login-btn-mobile-header') ||
      document.querySelector('[data-bs-target="#login_modal"], [data-target="#login_modal"], .login-btn, .js-login-btn');

    if (headerButton) {
      headerButton.click();
      window.setTimeout(setLootboxLoginReturnUrl, 80);
      window.setTimeout(setLootboxLoginReturnUrl, 300);
      return;
    }

    const loginModal = document.getElementById('login_modal') || document.getElementById('loginModal');
    if (loginModal) {
      loginModal.classList.add('show','active','is-open');
      loginModal.style.display = 'block';
      document.body.classList.add('modal-open','auth-modal-open','login-modal-open');
      window.setTimeout(setLootboxLoginReturnUrl, 80);
      return;
    }

    window.location.href = '<?= BASE_URL ?>/login?redirectUrl=' + encodeURIComponent(window.location.href);
  }

  setLootboxLoginReturnUrl();
  document.addEventListener('focusin', setLootboxLoginReturnUrl, true);
  document.addEventListener('click', function(event){
    const trigger = event.target.closest && event.target.closest('[data-login-trigger="1"]');
    if (trigger) {
      openLootboxLogin(event);
      return;
    }
    if (event.target.closest && event.target.closest('form')) setLootboxLoginReturnUrl();
  }, true);
  document.addEventListener('submit', function(event){
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const actionInput = form.querySelector('input[name="action"]');
    const hasEmailPassword = !!(
      form.querySelector('input[name="email"], input[type="email"], input[name="login"]') &&
      form.querySelector('input[name="password"], input[type="password"]')
    );
    if ((actionInput && (actionInput.value === 'auth_client_login' || actionInput.value === 'auth_unified_login')) || hasEmailPassword) {
      setLootboxLoginReturnUrl();
    }
  }, true);
})();
(function(){
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
})();

(function(){
  const btn = document.querySelector('[data-lb-scroll-target="lbRewardsFaq"]');
  if (!btn) return;
  btn.addEventListener('click', function(e){
    e.preventDefault();
    const target = document.getElementById('lbRewardsFaq');
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();
</script>
<?= $this->end() ?>
