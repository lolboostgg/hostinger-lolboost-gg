<?= $this->layout('client/layouts/main', ['meta' => $meta ?? ['title' => 'LB Rewards']]) ?>
<?php
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$coins = (float)($client['reward_points'] ?? 0);
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
$dailyBox = null;
foreach (($boxes ?? []) as $boxCandidate) {
    $slug = (string)($boxCandidate['slug'] ?? '');
    if (str_contains($slug, 'daily')) { $dailyBox = $boxCandidate; break; }
}
$dailyBoxUrl = BASE_URL . '/profile/rewards/' . $h($dailyBox['slug'] ?? 'daily-gift');
$dailyCanOpen = !empty($dailyBox['can_open']);
$dailyNextRaw = (string)($dailyBox['next_available_at'] ?? '');
$dailyNextIso = $dailyNextRaw !== '' ? date(DATE_ATOM, strtotime($dailyNextRaw)) : '';
?>
<?= $this->start('styles') ?>
<style>
.lb-rewards{--bg:#25282a;--line:rgba(255,255,255,.08);--soft:rgba(255,255,255,.055);--text:rgba(255,255,255,.92);--muted:rgba(255,255,255,.55);--blue:#6d8cff;--green:#22c55e;--gold:#facc15;--pink:#d946ef;}
.lb-r-hero{border:1px solid rgba(109,140,255,.22);background:radial-gradient(900px 260px at 90% 0%,rgba(109,140,255,.18),transparent 60%),linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02));border-radius:24px;padding:28px;margin-bottom:18px;overflow:hidden;position:relative;box-shadow:0 18px 48px rgba(0,0,0,.24);display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:center}
.lb-r-hero-main{position:relative;z-index:1}.lb-daily-quick{position:relative;z-index:1;border:1px solid rgba(109,140,255,.26);border-top:2px solid rgba(143,178,255,.92);border-radius:22px;background:linear-gradient(180deg,rgba(109,140,255,.105),rgba(255,255,255,.028));padding:16px;box-shadow:0 18px 40px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.05)}.lb-daily-quick:before{content:"";position:absolute;left:16px;right:16px;top:0;height:1px;background:linear-gradient(90deg,transparent,rgba(143,178,255,.95),transparent)}.lb-daily-icon{height:78px;border-radius:18px;background:radial-gradient(circle at 50% 35%,rgba(109,140,255,.34),rgba(0,0,0,.14) 64%);display:flex;align-items:center;justify-content:center;margin-bottom:12px}.lb-daily-icon i{font-size:2.55rem;color:#9db7ff;filter:drop-shadow(0 14px 22px rgba(109,140,255,.25))}.lb-daily-icon img{width:88px;height:88px;object-fit:contain;filter:drop-shadow(0 14px 24px rgba(109,140,255,.28))}.lb-daily-kicker{font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;font-weight:950;color:#8fb2ff;margin-bottom:5px}.lb-daily-title{color:var(--text);font-size:1.05rem;font-weight:950;margin:0 0 5px}.lb-daily-text{color:var(--muted);font-size:.82rem;font-weight:700;margin:0 0 12px;line-height:1.35}.lb-daily-btn{display:flex;align-items:center;justify-content:center;gap:8px;border-radius:12px;padding:10px 12px;text-decoration:none;font-weight:950;background:linear-gradient(135deg,#6d8cff,#7c5cff);color:#fff}.lb-daily-btn:hover{color:#fff;filter:brightness(1.06)}.lb-daily-btn.is-waiting{opacity:.58;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)}.lb-cd{font-variant-numeric:tabular-nums;font-feature-settings:'tnum';letter-spacing:.02em}.lb-daily-cooldown{display:inline-flex;align-items:center;gap:7px;margin-top:10px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.16);border-radius:999px;padding:7px 10px;color:rgba(255,255,255,.72);font-size:.78rem;font-weight:900}.lb-daily-cooldown i{color:#9db7ff}

/* Reward overview and box cards */
.lb-r-eyebrow{font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;font-weight:950;color:#8fb2ff;margin-bottom:8px}
.lb-r-title{font-size:2rem;font-weight:950;color:var(--text);margin:0}
.lb-r-sub{max-width:720px;color:var(--muted);font-weight:650;margin:10px 0 18px}
.lb-r-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.lb-r-balance,.lb-mywins-btn,.lb-how-jump{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:9px 13px;font-weight:900}
.lb-r-balance{border:1px solid rgba(109,140,255,.28);background:rgba(109,140,255,.09);color:rgba(255,255,255,.9)}
.lb-mywins-btn{border:1px solid rgba(109,140,255,.30);background:linear-gradient(135deg,rgba(109,140,255,.18),rgba(124,92,255,.13));color:#dbe7ff;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.06)}
.lb-mywins-btn:hover{color:#fff;border-color:rgba(143,178,255,.62);filter:brightness(1.08)}
.lb-how-jump{border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.045);color:rgba(255,255,255,.82);cursor:pointer;font:inherit}
.lb-how-jump:hover{color:#fff;border-color:rgba(143,178,255,.48);background:rgba(109,140,255,.12)}
.lb-balance-coin{width:22px;height:22px;object-fit:contain;filter:drop-shadow(0 6px 12px rgba(109,140,255,.25))}
.lb-section-title{font-size:1.1rem;font-weight:950;color:var(--text);margin:22px 0 12px}
.lb-r-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
.lb-box-card{position:relative;border:1px solid rgba(109,140,255,.18);background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.018));border-radius:22px;padding:18px;min-height:286px;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 16px 34px rgba(0,0,0,.20);transition:.16s}
.lb-box-card:hover{transform:translateY(-2px);border-color:rgba(109,140,255,.45)}
.lb-box-visual{position:relative;z-index:1;overflow:hidden;height:112px;border-radius:18px;background:radial-gradient(circle at 50% 20%,rgba(109,140,255,.26),rgba(0,0,0,.10) 60%),rgba(0,0,0,.15);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.lb-box-visual i{font-size:3.6rem;color:#8fb2ff;filter:drop-shadow(0 18px 25px rgba(109,140,255,.24))}
.lb-box-img{position:relative;z-index:1;width:118px;height:118px;object-fit:contain;filter:drop-shadow(0 18px 26px rgba(109,140,255,.28))}
.lb-box-name{font-size:1.05rem;font-weight:950;color:var(--text);margin:0 0 5px}
.lb-box-desc{font-size:.82rem;color:var(--muted);min-height:38px;line-height:1.45;margin-bottom:12px}
.lb-box-footer{display:flex;flex-direction:column;gap:8px;margin-top:14px}
.lb-box-one{display:flex;align-items:center;justify-content:center;gap:6px;margin:0;padding:5px 8px;border:1px solid rgba(109,140,255,.20);border-radius:10px;background:rgba(109,140,255,.08);color:#b9c9ff;font-size:.70rem;font-weight:900}
.lb-price{position:absolute;right:14px;top:14px;z-index:20;max-width:calc(100% - 28px);border:1px solid rgba(109,140,255,.38);background:rgba(4,8,18,.94);backdrop-filter:blur(10px);border-radius:999px;padding:6px 10px;font-weight:950;font-size:.70rem;line-height:1;color:#dbeafe;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 10px 24px rgba(0,0,0,.36)}
.lb-price.free{color:#34d399;border-color:rgba(34,197,94,.30);background:rgba(7,30,18,.90)}
.lb-price-coin{width:14px;height:14px;object-fit:contain;flex:0 0 14px}
.lb-open-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;border:0;border-radius:12px;background:linear-gradient(135deg,#6d8cff,#7c5cff);color:white;font-weight:950;padding:11px 14px;text-decoration:none}
.lb-open-btn:hover{color:white;filter:brightness(1.06)}
.lb-open-btn.is-waiting{opacity:.62;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.08)}

/* Recent and personal rewards */
.lb-reward-img{width:70px;height:70px;object-fit:contain;filter:drop-shadow(0 12px 20px rgba(109,140,255,.18))}
.lb-reward-img.small{width:46px;height:46px}
.lb-win-strip{display:flex;gap:12px;overflow:auto;padding:10px 4px 14px;scrollbar-width:thin}
.lb-win{position:relative;min-width:214px;border:1px solid rgba(255,255,255,.09);border-top:3px solid rgba(143,178,255,.95);border-radius:18px;padding:13px;background:linear-gradient(180deg,rgba(109,140,255,.105),rgba(255,255,255,.025));overflow:hidden}
.lb-win-visual,.lb-my-visual{height:86px;border-radius:14px;background:rgba(0,0,0,.18);display:flex;align-items:center;justify-content:center;margin-bottom:11px}
.lb-win b,.lb-reward-row b{display:block;color:var(--text);font-size:.95rem;line-height:1.18}
.lb-win small,.lb-reward-row small{display:block;color:var(--muted);font-weight:750;margin-top:4px}
.lb-win-client{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:9px;background:rgba(7,11,20,.94);opacity:0;transform:translateY(8px);transition:.16s;pointer-events:none}
.lb-win:hover .lb-win-client{opacity:1;transform:translateY(0)}
.lb-win-client-avatar{width:50px;height:50px;border-radius:50%;border:1px solid rgba(109,140,255,.35);background:rgba(109,140,255,.14);display:flex;align-items:center;justify-content:center;overflow:hidden}
.lb-win-client-avatar img{width:100%;height:100%;object-fit:cover}
.lb-win-client-name{font-weight:950;color:rgba(255,255,255,.92)}
.lb-win-client-meta{font-size:.72rem;text-transform:uppercase;color:#8fb2ff;font-weight:950}
.lb-rarity{display:inline-flex;border-radius:999px;padding:4px 8px;font-size:.65rem;text-transform:uppercase;font-weight:950;letter-spacing:.04em;margin-bottom:7px}
.lb-rarity.common{background:rgba(148,163,184,.14);color:#cbd5e1}.lb-rarity.uncommon{background:rgba(34,197,94,.14);color:#86efac}.lb-rarity.rare{background:rgba(96,165,250,.15);color:#93c5fd}.lb-rarity.epic{background:rgba(217,70,239,.16);color:#f0abfc}.lb-rarity.legendary{background:rgba(250,204,21,.16);color:#fde68a}
.lb-reward-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
.lb-reward-row{border:1px solid rgba(255,255,255,.09);border-top:2px solid rgba(109,140,255,.62);background:rgba(255,255,255,.028);border-radius:18px;padding:13px;min-height:158px}
.lb-reward-meta{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}
.lb-reward-chip{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);border-radius:999px;padding:5px 8px;color:rgba(255,255,255,.62);font-size:.72rem;font-weight:850}

/* Shared FAQ foundations used by the section overrides below */
.lb-faq-item summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:12px}
.lb-faq-item summary::-webkit-details-marker{display:none}
.lb-faq-item summary:after{content:"\f078";font-family:"Font Awesome 6 Pro","Font Awesome 6 Free";font-weight:900;transition:transform .16s}
.lb-faq-item[open] summary:after{transform:rotate(180deg)}
.lb-how-num{display:flex!important;align-items:center!important;justify-content:center!important;padding:0!important;text-align:center!important}

@media(max-width:980px){.lb-r-hero{grid-template-columns:1fr}.lb-daily-quick{max-width:420px}}
@media(max-width:768px){.lb-r-hero{padding:22px}.lb-r-title{font-size:1.55rem}}


/* Better How it works + FAQ */
.lb-info-section{margin-top:38px!important;border:0!important;border-radius:0!important;background:transparent!important;padding:8px 0 26px!important;box-shadow:none!important;overflow:visible!important;scroll-margin-top:105px}.lb-info-section:before{display:none!important}.lb-info-shell{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(330px,.72fr);gap:22px;align-items:start}.lb-info-main{position:relative;padding:2px 0}.lb-info-head{display:block!important;margin:0 0 22px!important}.lb-info-kicker{display:inline-flex!important;align-items:center;gap:8px;margin-bottom:10px!important;color:#8fb2ff!important;font-size:.72rem!important}.lb-info-kicker:before{content:"";width:22px;height:2px;border-radius:999px;background:#8fb2ff;box-shadow:0 0 16px rgba(143,178,255,.42)}.lb-info-title{font-size:1.45rem!important;line-height:1.15!important;letter-spacing:-.03em!important;margin:0!important;max-width:680px}.lb-info-sub{max-width:690px!important;margin-top:9px!important;color:rgba(255,255,255,.54)!important;font-size:.92rem!important;line-height:1.55!important}.lb-how-grid{position:relative;display:grid!important;grid-template-columns:1fr!important;gap:16px!important;border:0!important;background:transparent!important;overflow:visible!important}.lb-how-grid:before{display:none!important}.lb-how-card{position:relative!important;display:grid;grid-template-columns:46px minmax(0,1fr);gap:14px;align-items:start;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;min-height:0!important;padding:0 0 2px!important;transition:opacity .16s ease}.lb-how-card:not(:last-child){border:0!important}.lb-how-card:not(:last-child)::after{content:"";position:absolute;left:19px;top:39px;height:calc(100% - 22px);width:2px;border-radius:999px;background:linear-gradient(180deg,rgba(143,178,255,.26),rgba(143,178,255,.08));box-shadow:0 0 0 1px rgba(255,255,255,.015);opacity:.65;transition:background .18s ease,box-shadow .18s ease,opacity .18s ease}.lb-how-num{position:relative!important;left:auto!important;top:auto!important;width:32px!important;height:32px!important;border-radius:50%!important;margin:2px 0 0 4px!important;background:linear-gradient(135deg,rgba(143,178,255,.16),rgba(124,92,255,.10))!important;border:1px solid rgba(143,178,255,.30)!important;color:#c9d8ff!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.07),0 8px 18px rgba(0,0,0,.14)!important;font-size:.78rem!important;line-height:1!important;z-index:2;transition:background .18s ease,border-color .18s ease,box-shadow .18s ease,color .18s ease,transform .18s ease}.lb-how-content{padding:0}.lb-how-card b{font-size:1rem!important;margin:0 0 5px!important;color:rgba(255,255,255,.92)!important;transition:color .18s ease}.lb-how-card p{font-size:.86rem!important;line-height:1.55!important;color:rgba(255,255,255,.52)!important;max-width:720px}.lb-how-card:hover .lb-how-num,.lb-how-grid:has(.lb-how-card:nth-child(2):hover) .lb-how-card:nth-child(-n+2) .lb-how-num,.lb-how-grid:has(.lb-how-card:nth-child(3):hover) .lb-how-card:nth-child(-n+3) .lb-how-num{background:linear-gradient(135deg,rgba(143,178,255,.34),rgba(124,92,255,.24))!important;border-color:rgba(143,178,255,.72)!important;color:#fff!important;box-shadow:0 0 0 5px rgba(109,140,255,.08),0 0 24px rgba(109,140,255,.34),inset 0 1px 0 rgba(255,255,255,.12)!important;transform:translateY(-1px)}.lb-how-card:hover b,.lb-how-grid:has(.lb-how-card:nth-child(2):hover) .lb-how-card:nth-child(-n+2) b,.lb-how-grid:has(.lb-how-card:nth-child(3):hover) .lb-how-card:nth-child(-n+3) b{color:#fff!important}.lb-how-grid:has(.lb-how-card:nth-child(2):hover) .lb-how-card:nth-child(1)::after,.lb-how-grid:has(.lb-how-card:nth-child(3):hover) .lb-how-card:nth-child(-n+2)::after{background:linear-gradient(180deg,rgba(143,178,255,.95),rgba(124,92,255,.62))!important;box-shadow:0 0 18px rgba(109,140,255,.36);opacity:1}.lb-info-aside{position:relative;border:1px solid rgba(143,178,255,.16);background:linear-gradient(180deg,rgba(109,140,255,.065),rgba(255,255,255,.018));border-radius:20px;padding:16px;box-shadow:0 18px 42px rgba(0,0,0,.18)}.lb-faq-title{margin:0 0 6px!important;display:flex!important;align-items:center!important;gap:9px!important;font-size:1.02rem!important}.lb-faq-title:before{content:"";width:9px;height:9px;border-radius:50%;background:#8fb2ff;box-shadow:0 0 18px rgba(143,178,255,.6)}.lb-faq-sub{color:rgba(255,255,255,.45);font-size:.8rem;font-weight:750;margin:0 0 10px}.lb-faq{margin-top:0!important;display:grid!important;grid-template-columns:1fr!important;gap:7px!important;max-width:none!important}.lb-faq-item{border:1px solid rgba(255,255,255,.07)!important;border-radius:13px!important;background:rgba(0,0,0,.13)!important;overflow:hidden}.lb-faq-item summary{padding:12px 13px!important;color:rgba(255,255,255,.84)!important;font-size:.84rem!important;line-height:1.25!important}.lb-faq-item summary:after{color:rgba(143,178,255,.82)!important;font-size:.75rem!important}.lb-faq-item p{padding:0 13px 13px!important;color:rgba(255,255,255,.50)!important;font-size:.79rem!important;line-height:1.5!important;max-width:none!important}.lb-faq-item[open]{background:rgba(109,140,255,.06)!important;border-color:rgba(143,178,255,.24)!important}.lb-faq-item[open] summary{color:#fff!important}.lb-info-note{display:flex;align-items:center;gap:10px;margin-top:17px;padding:12px 14px;border-radius:16px;background:rgba(0,0,0,.16);border:1px solid rgba(255,255,255,.07);color:rgba(255,255,255,.56);font-size:.82rem;font-weight:800}.lb-info-note i{color:#8fb2ff}.lb-faq-mobile-title{display:none}@media(max-width:1100px){.lb-info-shell{grid-template-columns:1fr}.lb-info-aside{max-width:760px}.lb-faq{grid-template-columns:1fr!important}}@media(max-width:768px){.lb-info-title{font-size:1.22rem!important}.lb-info-shell{gap:18px}.lb-info-aside{padding:14px;border-radius:18px}.lb-how-card{grid-template-columns:40px 1fr}.lb-how-card:not(:last-child)::after{left:18px;top:37px;height:calc(100% - 18px)}.lb-how-num{width:30px!important;height:30px!important;border-radius:50%!important;margin-left:3px!important}.lb-how-card b{font-size:.94rem!important}.lb-how-card p{font-size:.8rem!important}}

</style>
<?= $this->end() ?>
<div class="lb-rewards">
  <div class="lb-r-hero">
    <div class="lb-r-hero-main">
      <div class="lb-r-eyebrow">LoLBoost Rewards</div>
      <h1 class="lb-r-title">LB Reward Boxes</h1>
      <p class="lb-r-sub">Spend Reward Points to open reward boxes and win bonus Reward Points, coupons, wallet credit and order perks. Daily Gift is free every 24 hours.</p>
      <div class="lb-r-actions">
        <div class="lb-r-balance"><img class="lb-balance-coin" src="<?= $h($coinIconUrl($coins)) ?>" alt="Reward Points"> Your Reward Points: <span id="lbRewardBalance"><?= number_format($coins, 2) ?></span></div>
        <a class="lb-mywins-btn" href="<?= BASE_URL ?>/profile/rewards/wins"><i class="fa-duotone fa-trophy-star"></i> My Wins</a>
        <button type="button" class="lb-how-jump" data-lb-scroll-target="lbRewardsHow"><i class="fa-duotone fa-circle-question"></i> How it works?</button>
      </div>
    </div>
    <div class="lb-daily-quick">
      <div class="lb-daily-icon"><?= $dailyBox ? $renderBoxIcon($dailyBox, 'lb-box-img') : '<i class="fa-duotone fa-gift"></i>' ?></div>
      <div class="lb-daily-kicker">Quick action</div>
      <h3 class="lb-daily-title">Open your Daily Gift</h3>
      <p class="lb-daily-text"><?= $dailyCanOpen ? 'Your free reward box is ready now.' : 'Already opened, your next Daily Gift unlocks soon.' ?></p>
      <a class="lb-daily-btn <?= $dailyCanOpen ? '' : 'is-waiting' ?>" href="<?= $h($dailyBoxUrl) ?>" aria-label="View Daily Gift">
        <?php if ($dailyCanOpen): ?>
          Open Daily Gift <i class="fa-solid fa-arrow-right"></i>
        <?php else: ?>
          Waiting to open <span class="lb-cd" data-lb-countdown="<?= $h($dailyNextIso) ?>">--:--:--</span>
        <?php endif; ?>
      </a>
    </div>
  </div>

  <?php if (!empty($recent_wins)): ?>
    <div class="lb-section-title">Recent wins</div>
    <div class="lb-win-strip">
      <?php foreach ($recent_wins as $win):
        $r = $rarityClass($win['rarity'] ?? 'common');
        $clientName = $maskClientName($win['username'] ?? ('Guest#' . (int)($win['client_id'] ?? 0)));
        $clientIcon = trim((string)($win['client_icon'] ?? ''));
      ?>
        <div class="lb-win">
          <div class="lb-win-visual"><?= $renderRewardIcon($win) ?></div>
          <span class="lb-rarity <?= $r ?>"><?= $h($r) ?></span>
          <b><?= $h($win['item_name'] ?? 'Reward') ?></b>
          <small><?= $h($typeLabel($win['reward_type'] ?? '', $win['reward_value'] ?? '')) ?></small>
          <div class="lb-win-client" aria-hidden="true">
            <div class="lb-win-client-avatar">
              <?php if ($clientIcon !== ''): ?>
                <img src="<?= $h($clientIcon) ?>" alt="<?= $h($clientName) ?>">
              <?php else: ?>
                <i class="fa-duotone fa-user"></i>
              <?php endif; ?>
            </div>
            <div class="lb-win-client-name"><?= $h($clientName) ?></div>
            <div class="lb-win-client-meta">opened <?= $h($win['box_name'] ?? 'Reward Box') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="lb-section-title">Choose a reward box</div>
  <div class="lb-r-grid">
    <?php foreach (($boxes ?? []) as $box):
        $free=(float)($box['price_coins'] ?? 0)<=0;
        $can=!empty($box['can_open']);
        $boxNextRaw=(string)($box['next_available_at'] ?? '');
        $boxNextIso=$boxNextRaw !== '' ? date(DATE_ATOM, strtotime($boxNextRaw)) : '';
      ?>
      <div class="lb-box-card">
        <div class="lb-price <?= $free ? 'free' : '' ?>">
          <?php if ($free): ?>
            Free
          <?php else: ?>
            <img class="lb-price-coin" src="<?= $h($rewardPointsIconUrl) ?>" alt="Reward Points">
            <?= number_format((float)$box['price_coins'], 0) . ' Reward Points' ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="lb-box-visual"><?= $renderBoxIcon($box) ?></div>
          <h3 class="lb-box-name"><?= $h($box['name'] ?? 'Reward Box') ?></h3>
          <div class="lb-box-desc"><?= $h($box['description'] ?? '') ?></div>
        </div>
        <div class="lb-box-footer">
          <div class="lb-box-one"><i class="fa-solid fa-dice"></i> Includes 1 random reward</div>
          <a class="lb-open-btn <?= $can ? '' : 'is-waiting' ?>" href="<?= BASE_URL ?>/profile/rewards/<?= $h($box['slug'] ?? '') ?>">
            <?php if ($can): ?>
              View Reward Box <i class="fa-solid fa-arrow-right"></i>
            <?php elseif ($boxNextIso !== ''): ?>
              Waiting to open <span class="lb-cd" data-lb-countdown="<?= $h($boxNextIso) ?>">--:--:--</span>
            <?php else: ?>
              Available later
            <?php endif; ?>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($my_rewards)): ?>
    <div class="lb-section-title" style="display:flex;align-items:center;justify-content:space-between;gap:12px;"><span>My latest rewards</span><a class="lb-mywins-btn" href="<?= BASE_URL ?>/profile/rewards/wins"><i class="fa-duotone fa-list"></i> View all wins</a></div>
    <div class="lb-reward-list">
      <?php foreach ($my_rewards as $reward): $r=$rarityClass($reward['rarity'] ?? 'common'); ?>
        <div class="lb-reward-row">
          <div class="lb-my-visual"><?= $renderRewardIcon($reward) ?></div>
          <span class="lb-rarity <?= $r ?>"><?= $h($r) ?></span>
          <b><?= $h($reward['item_name'] ?? $reward['reward_type'] ?? 'Reward') ?></b>
          <small><?= $h($typeLabel($reward['reward_type'] ?? '', $reward['reward_value'] ?? '')) ?></small>
          <div class="lb-reward-meta">
            <span class="lb-reward-chip"><i class="fa-duotone fa-circle-check"></i><?= $h(ucfirst((string)($reward['status'] ?? 'unused'))) ?></span>
            <?php if (!empty($reward['created_at'])): ?><span class="lb-reward-chip"><i class="fa-duotone fa-clock"></i><?= $h(date('d.m.Y', strtotime((string)$reward['created_at']))) ?></span><?php endif; ?>
          </div>
          <?php if (!empty($reward['coupon_code'])): ?><code><?= $h($reward['coupon_code']) ?></code><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="lb-info-section" id="lbRewardsHow">
    <div class="lb-info-shell">
      <div class="lb-info-main">
        <div class="lb-info-head">
          <div class="lb-info-kicker">How it works</div>
          <h2 class="lb-info-title">A simple rewards loop for active customers</h2>
          <p class="lb-info-sub">Earn Reward Points, open boxes, then use every reward directly from your account. No extra steps, no manual claiming after the box is opened.</p>
        </div>

        <div class="lb-how-grid">
          <div class="lb-how-card">
            <span class="lb-how-num">1</span>
            <div class="lb-how-content">
              <b>Earn Reward Points</b>
              <p>Reward Points come from Daily Gift, manual rewards, promotions and special events. Your Reward Points balance is always visible at the top.</p>
            </div>
          </div>
          <div class="lb-how-card">
            <span class="lb-how-num">2</span>
            <div class="lb-how-content">
              <b>Open reward boxes</b>
              <p>Claim your free Daily Gift every 24 hours or spend Reward Points on higher tier boxes with stronger reward pools.</p>
            </div>
          </div>
          <div class="lb-how-card">
            <span class="lb-how-num">3</span>
            <div class="lb-how-content">
              <b>Use your rewards</b>
              <p>Reward Points, wallet credit, coupons and order perks are saved automatically and can be checked anytime under My Wins.</p>
            </div>
          </div>
        </div>

        <div class="lb-info-note"><i class="fa-duotone fa-shield-check"></i><span>Every opening is saved to your account, so you can always review what you won later.</span></div>
      </div>

      <aside class="lb-info-aside">
        <div class="lb-section-title lb-faq-title">FAQ</div>
        <p class="lb-faq-sub">Quick answers before opening a box.</p>
        <div class="lb-faq">
          <details class="lb-faq-item" open>
            <summary>How often can I open the Daily Gift?</summary>
            <p>Once every 24 hours. If it is still on cooldown, the button shows the remaining time.</p>
          </details>
          <details class="lb-faq-item">
            <summary>What can I win?</summary>
            <p>Reward Points, discount coupons, wallet credit and order perks such as Priority Order or Champion Preference.</p>
          </details>
          <details class="lb-faq-item">
            <summary>Where are my previous wins?</summary>
            <p>Open My Wins at the top of the page. Wins are sorted by date and show reward, box, status and coupon code.</p>
          </details>
          <details class="lb-faq-item">
            <summary>Are rewards added automatically?</summary>
            <p>Yes. Coins and wallet credit are credited instantly. Coupons and perks are saved to your account.</p>
          </details>
          <details class="lb-faq-item">
            <summary>Can I open other boxes during Daily Gift cooldown?</summary>
            <p>Yes. The cooldown only blocks Daily Gift. Paid boxes stay available if you have enough Reward Points.</p>
          </details>
          <details class="lb-faq-item">
            <summary>Do coupons expire?</summary>
            <p>Some promotion coupons can expire. Check your reward details or contact support if a code does not work.</p>
          </details>
        </div>
      </aside>
    </div>
  </section>
</div>

<?= $this->start('scripts') ?>
<script>
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
  const btn = document.querySelector('[data-lb-scroll-target="lbRewardsHow"]');
  if (!btn) return;
  btn.addEventListener('click', function(e){
    e.preventDefault();
    const target = document.getElementById('lbRewardsHow');
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();
</script>
<?= $this->end() ?>
