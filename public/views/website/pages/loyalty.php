<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lb-loyalty-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Loyalty Program (matches the shop lol/val look)
   ============================================================ */
.lb-loyalty-page{ overflow-x:hidden; }
.lb-loy-wrap{ width:min(1180px, calc(100% - 40px)); margin:0 auto; }

/* Dynamic background — same visual family as the landing page hero */
.lb-loy-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden;background:
  radial-gradient(1200px 700px at 20% 8%, rgba(99,102,241,.22), transparent 60%),
  radial-gradient(900px 620px at 82% 15%, rgba(56,189,248,.13), transparent 58%),
  radial-gradient(1000px 700px at 50% 92%, rgba(217,70,239,.10), transparent 60%),
  linear-gradient(180deg,#0a0818 0%, #0e0c22 55%, #0a0818 100%);
}
.lb-loy-gridlines{position:fixed;inset:-2px;z-index:-1;pointer-events:none;opacity:.14;background-image:
  linear-gradient(to right, rgba(255,255,255,.06) 1px, transparent 1px),
  linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(closest-side at 50% 12%, black 0%, transparent 74%);
}
#lbLoyStars{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;mix-blend-mode:screen;}
.lb-loy-star{position:absolute;left:var(--x,50vw);top:var(--y,50vh);width:var(--s,3px);height:var(--s,3px);border-radius:999px;background:rgba(255,255,255,.95);box-shadow:0 0 10px rgba(255,255,255,.65),0 0 22px rgba(99,102,241,.45);opacity:var(--o,.7);transform:translate3d(0,0,0) scale(.85);animation:lbLoyStar var(--d,22s) linear infinite;animation-delay:var(--delay,0s);will-change:transform,opacity;}
@keyframes lbLoyStar{
  0%{transform:translate3d(0,0,0) scale(.85);opacity:.1;}
  14%{opacity:var(--o,.7);}
  72%{opacity:var(--o,.7);}
  100%{transform:translate3d(var(--tx,-26vw),var(--ty,20vh),0) scale(1.12);opacity:.05;}
}
@media(max-width:820px){#lbLoyStars{display:none;}}
@media(prefers-reduced-motion:reduce){.lb-loy-star{animation:none!important;}}

/* Hero */
.lb-loy-hero{position:relative;padding:calc(var(--lb-content-top, 96px) + clamp(48px,6vw,80px)) 24px clamp(56px,7vw,88px);text-align:center;overflow:hidden;}
.lb-loy-hero__eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 22px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.88);border:1px solid rgba(99,102,241,.4);background:rgba(15,14,32,.6);box-shadow:0 16px 40px rgba(0,0,0,.3),inset 0 1px 0 rgba(255,255,255,.08);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.lb-loy-hero__eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8b93ff;box-shadow:0 0 0 6px rgba(99,102,241,.16),0 0 18px rgba(99,102,241,.6);animation:lbLoyPulse 2.2s ease-in-out infinite;}
@keyframes lbLoyPulse{0%,100%{box-shadow:0 0 0 6px rgba(99,102,241,.16),0 0 18px rgba(99,102,241,.6);}50%{box-shadow:0 0 0 9px rgba(99,102,241,.22),0 0 26px rgba(99,102,241,.85);}}
@media(prefers-reduced-motion:reduce){.lb-loy-hero__eyebrow .dot{animation:none!important;}}
.lb-loy-hero h1{margin:0 auto 20px;max-width:920px;font-size:clamp(36px,5.4vw,66px);line-height:1.06;letter-spacing:-.03em;font-weight:950;color:#fff;text-transform:uppercase;text-shadow:0 20px 50px rgba(0,0,0,.5);}
.lb-loy-hero h1 .accent{background-image:linear-gradient(92deg,#a5b4fc 0%,#818cf8 50%,#6366f1 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6366f1;}
.lb-loy-hero__sub{max-width:640px;margin:0 auto 30px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}
.lb-loy-hero__actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-bottom:8px;}
.lb-loy-hero__actions .btn{min-height:52px;padding:0 26px;border-radius:14px;}
@media(max-width:640px){.lb-loy-hero__actions .btn{width:100%;max-width:340px;justify-content:center;}}

/* This page's buttons use icon-before-text markup; add a gap so the icon and label don't touch */
.lb-loyalty-page .btn{display:inline-flex;align-items:center;gap:10px;}
.lb-loyalty-page .btn i{margin:0;}

/* Soft hover glow (used on cards that should feel "alive" on hover) */
.lb-loy-glow{transition:transform .25s ease,border-color .25s ease,box-shadow .25s ease;}
.lb-loy-glow:hover{transform:translateY(-4px);border-color:rgba(99,102,241,.5);box-shadow:0 24px 60px rgba(0,0,0,.4),0 0 0 1px rgba(99,102,241,.3),0 0 30px rgba(99,102,241,.14);}
@keyframes lbLoyBob{0%,100%{transform:translateY(0);}50%{transform:translateY(-7px);}}
@media(prefers-reduced-motion:reduce){.lb-loy-glow{transition:none;}}

/* Plain-language explainer — a connected, floating step timeline instead of flat cards */
.lb-loy-explain{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;}
.lb-loy-explain::before{content:"";position:absolute;top:29px;left:16%;right:16%;height:2px;z-index:0;background:linear-gradient(90deg,transparent,rgba(99,102,241,.6),rgba(165,180,252,.4),rgba(99,102,241,.6),transparent);}
.lb-loy-explain-item{position:relative;z-index:1;text-align:center;display:flex;flex-direction:column;align-items:center;gap:12px;}
.lb-loy-explain-item i{width:58px;height:58px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:radial-gradient(circle at 35% 30%,rgba(99,102,241,.4),rgba(22,19,43,.95) 72%);border:1px solid rgba(99,102,241,.45);box-shadow:0 0 0 6px rgba(10,8,24,.9),0 14px 30px rgba(99,102,241,.22);color:#a5b4fc;font-size:19px;animation:lbLoyBob 4.4s ease-in-out infinite;transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease;}
.lb-loy-explain-item:nth-child(2) i{animation-delay:.35s;}
.lb-loy-explain-item:nth-child(3) i{animation-delay:.7s;}
.lb-loy-explain-item:hover i{transform:translateY(-6px) scale(1.08);border-color:rgba(99,102,241,.85);box-shadow:0 0 0 6px rgba(10,8,24,.9),0 0 32px rgba(99,102,241,.5);color:#fff;}
.lb-loy-explain-item b{color:#fff;font-size:14.5px;}
.lb-loy-explain-item p{margin:0;color:#a9adc4;font-size:13px;line-height:1.6;max-width:250px;}
@media(max-width:760px){.lb-loy-explain{grid-template-columns:1fr;gap:26px;}.lb-loy-explain::before{display:none;}}
@media(prefers-reduced-motion:reduce){.lb-loy-explain-item i{animation:none!important;}}

/* Section rhythm + heading */
.lb-loy-section{position:relative;padding:clamp(56px,6.5vw,92px) 0;}
.lb-loy-section + .lb-loy-section{border-top:1px solid rgba(255,255,255,.06);}
.lb-loy-head{max-width:720px;margin:0 auto 36px;text-align:center;}
.lb-loy-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:5px 14px;border-radius:999px;border:1px solid rgba(99,102,241,.45);background:rgba(99,102,241,.1);color:#a5b4fc;font-size:11px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;margin-bottom:14px;}
.lb-loy-head h2{margin:0 0 12px;font-size:clamp(26px,3vw,40px);font-weight:800;letter-spacing:-.02em;color:#fff;}
.lb-loy-head p{margin:0;color:#a9adc4;font-size:15px;line-height:1.7;}

/* Card recipe (same lifted-card style used across the boost pages) */
.lb-loy-card{background:#16132b;border:1px solid rgba(99,102,241,.22);border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.35);}

/* Trust badges + quick stats under the hero */
.lb-loy-trust{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin:22px 0 0;}
.lb-loy-trust .badge{display:inline-flex;align-items:center;gap:7px;font-size:12px;padding:7px 14px;}
.lb-loy-trust .badge i{color:#22c55e;}
.lb-loy-mini{margin:34px auto 0;max-width:820px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.lb-loy-mini-card{padding:18px 14px;text-align:center;}
.lb-loy-mini-card i{width:40px;height:40px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.24);color:#8b93ff;}
.lb-loy-mini-card b{display:block;font-size:22px;font-weight:800;letter-spacing:-.02em;color:#fff;}
.lb-loy-mini-card span{display:block;margin-top:4px;color:#726e8e;font-size:12px;font-weight:600;}

/* Tiers */
.lb-loy-tier-grid{display:grid;grid-template-columns:repeat(7,minmax(120px,1fr));gap:14px;}
.lb-loy-tier{--tier:#6366f1;padding:20px 12px;text-align:center;transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;}
.lb-loy-tier:hover{transform:translateY(-6px);border-color:color-mix(in srgb,var(--tier) 55%,rgba(99,102,241,.4));box-shadow:0 22px 60px rgba(0,0,0,.4),0 0 32px color-mix(in srgb,var(--tier) 22%,transparent);}
.lb-loy-tier-icon{width:56px;height:56px;margin:0 auto 12px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--tier) 16%,rgba(0,0,0,.2));border:1px solid color-mix(in srgb,var(--tier) 34%,rgba(255,255,255,.1));animation:lbLoyBob 5s ease-in-out infinite;}
.lb-loy-tier:nth-child(even) .lb-loy-tier-icon{animation-delay:.4s;}
@media(prefers-reduced-motion:reduce){.lb-loy-tier-icon{animation:none!important;}}
.lb-loy-tier-icon img{width:32px;height:32px;object-fit:contain;}
.lb-loy-tier-name{font-size:14px;font-weight:800;letter-spacing:-.01em;color:#fff;margin-bottom:6px;}
.lb-loy-tier-spend{display:inline-flex;align-items:center;justify-content:center;min-height:24px;padding:0 8px;border-radius:999px;background:color-mix(in srgb,var(--tier) 18%,rgba(0,0,0,.18));border:1px solid color-mix(in srgb,var(--tier) 32%,rgba(255,255,255,.1));font-size:11px;font-weight:700;color:rgba(255,255,255,.85);}
.lb-loy-tier-cashback{margin-top:12px;color:#726e8e;font-size:11px;font-weight:600;}
.lb-loy-tier-cashback b{display:block;font-size:18px;color:#fff;}

/* How it works */
.lb-loy-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;}
.lb-loy-step{padding:24px;position:relative;overflow:hidden;transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease;}
.lb-loy-step:hover{transform:translateY(-4px);border-color:rgba(99,102,241,.5);box-shadow:0 22px 60px rgba(0,0,0,.4),0 0 0 1px rgba(99,102,241,.2);}
.lb-loy-step-num{position:absolute;right:16px;top:8px;color:rgba(255,255,255,.05);font-size:56px;font-weight:900;line-height:1;letter-spacing:-.06em;}
.lb-loy-step-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.26);color:#8b93ff;font-size:18px;position:relative;z-index:1;animation:lbLoyBob 4.6s ease-in-out infinite;}
.lb-loy-step:nth-child(2) .lb-loy-step-icon{animation-delay:.3s;}
.lb-loy-step:nth-child(3) .lb-loy-step-icon{animation-delay:.6s;}
.lb-loy-step:nth-child(4) .lb-loy-step-icon{animation-delay:.9s;}
.lb-loy-step:hover .lb-loy-step-icon{background:rgba(99,102,241,.28);border-color:rgba(99,102,241,.6);}
@media(prefers-reduced-motion:reduce){.lb-loy-step-icon{animation:none!important;}}
.lb-loy-step h3{position:relative;z-index:1;margin:0 0 8px;font-size:17px;font-weight:800;letter-spacing:-.015em;color:#fff;}
.lb-loy-step p{position:relative;z-index:1;margin:0;color:#726e8e;font-size:13.5px;line-height:1.65;}

/* Lootboxes promo — the highlight section */
.lb-loy-lootbox{position:relative;overflow:hidden;padding:clamp(30px,4vw,52px);display:flex;align-items:center;gap:clamp(24px,3vw,42px);}
.lb-loy-lootbox:before{content:"";position:absolute;inset:0;background:
  radial-gradient(560px 260px at 12% 0%, rgba(99,102,241,.28), transparent 65%),
  radial-gradient(480px 260px at 100% 100%, rgba(217,70,239,.14), transparent 65%);
  pointer-events:none;}
.lb-loy-lootbox-icon{position:relative;z-index:1;width:96px;height:96px;min-width:96px;border-radius:26px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.16);border:1px solid rgba(99,102,241,.32);box-shadow:0 20px 50px rgba(99,102,241,.18);animation:lbLoyBob 4.2s ease-in-out infinite;}
@media(prefers-reduced-motion:reduce){.lb-loy-lootbox-icon{animation:none!important;}}
.lb-loy-lootbox-icon i{font-size:38px;color:#a5b4fc;}
.lb-loy-lootbox-body{position:relative;z-index:1;flex:1;min-width:0;}
.lb-loy-lootbox-body h2{margin:0 0 10px;font-size:clamp(22px,2.4vw,32px);font-weight:850;letter-spacing:-.02em;color:#fff;}
.lb-loy-lootbox-body p{margin:0 0 18px;color:#a9adc4;font-size:14.5px;line-height:1.7;max-width:620px;}
.lb-loy-lootbox-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
.lb-loy-lootbox-chips span{display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.8);font-size:12px;font-weight:650;}
.lb-loy-lootbox-chips i{color:#8b93ff;font-size:11px;}
.lb-loy-lootbox-actions{display:flex;gap:12px;flex-wrap:wrap;}
@media(max-width:760px){
  .lb-loy-lootbox{flex-direction:column;text-align:center;padding:28px 20px;}
  .lb-loy-lootbox-chips,.lb-loy-lootbox-actions{justify-content:center;}
  .lb-loy-lootbox-body p{max-width:none;}
}

/* Benefits table */
.lb-loy-benefits{padding:22px;}
.lb-loy-benefits-scroll{overflow-x:auto;padding-bottom:4px;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.5) rgba(255,255,255,.05);}
.lb-loy-benefits table{width:100%;min-width:920px;border-collapse:separate;border-spacing:0 8px;}
.lb-loy-benefits th,.lb-loy-benefits td{padding:12px;text-align:center;background:rgba(255,255,255,.03);border-top:1px solid rgba(255,255,255,.07);border-bottom:1px solid rgba(255,255,255,.07);}
.lb-loy-benefits th:first-child,.lb-loy-benefits td:first-child{text-align:left;border-left:1px solid rgba(255,255,255,.07);border-radius:14px 0 0 14px;min-width:200px;color:#fff;}
.lb-loy-benefits th:last-child,.lb-loy-benefits td:last-child{border-right:1px solid rgba(255,255,255,.07);border-radius:0 14px 14px 0;}
.lb-loy-benefits th{color:#fff;font-size:12.5px;font-weight:800;}
.lb-loy-benefits th img{width:28px;height:28px;object-fit:contain;margin:0 auto 6px;}
.lb-loy-benefits td:first-child i,.lb-loy-benefits th:first-child i{color:#8b93ff;margin-right:8px;}
.lb-loy-benefits td{color:#a9adc4;font-size:13px;font-weight:650;}
.lb-loy-check{color:#22c55e;font-size:15px;}
.lb-loy-x{color:rgba(255,255,255,.22);font-size:14px;}

/* Points store */
.lb-loy-store{display:grid;grid-template-columns:1fr .85fr;gap:16px;align-items:stretch;}
.lb-loy-store-main{padding:32px;}
.lb-loy-store-main h2{margin:0 0 10px;font-size:clamp(24px,2.6vw,34px);font-weight:850;letter-spacing:-.02em;color:#fff;}
.lb-loy-store-main p{margin:0 0 22px;color:#a9adc4;font-size:14.5px;line-height:1.7;max-width:520px;}
.lb-loy-store-actions{display:flex;gap:12px;flex-wrap:wrap;}
.lb-loy-store-list{display:grid;gap:10px;}
.lb-loy-reward{padding:16px;display:flex;align-items:center;gap:14px;transition:transform .2s ease,border-color .2s ease;}
.lb-loy-reward:hover{transform:translateX(4px);border-color:rgba(99,102,241,.45);}
.lb-loy-reward i{width:42px;height:42px;min-width:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.26);color:#8b93ff;font-size:15px;}
.lb-loy-reward b{display:block;color:#fff;font-size:14px;}
.lb-loy-reward span{display:block;margin-top:2px;color:#726e8e;font-size:12px;font-weight:600;}

/* FAQ */
.lb-loy-faq-tabs{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin:0 auto 26px;}
.lb-loy-faq-tab{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:rgba(255,255,255,.75);border-radius:999px;min-height:40px;padding:0 16px;font-weight:700;font-size:13px;cursor:pointer;transition:transform .15s ease,background .15s ease,border-color .15s ease,color .15s ease;}
.lb-loy-faq-tab:hover{transform:translateY(-1px);border-color:rgba(99,102,241,.4);}
.lb-loy-faq-tab.active{background:#6366f1;border-color:#6366f1;color:#fff;}
.lb-loy-faq-wrap{max-width:900px;margin:0 auto;border:1px solid rgba(255,255,255,.10);border-radius:22px;overflow:hidden;background:rgba(255,255,255,.02);box-shadow:0 20px 60px rgba(0,0,0,.35);}
.lb-loy-faq-wrap .accordion-item{padding:0 22px;border-bottom:1px solid rgba(255,255,255,.08);}
.lb-loy-faq-wrap .accordion-item:last-child{border-bottom:none;}
.lb-loy-faq-wrap .accordion-header h5{font-size:15px;}
.lb-loy-faq-wrap .accordion-content p,.lb-loy-faq-wrap .accordion-content li{font-size:13.5px;}

/* Final CTA */
.lb-loy-cta{padding:clamp(28px,3.6vw,48px) clamp(20px,4vw,52px);text-align:center;position:relative;overflow:hidden;}
.lb-loy-cta:before{content:"";position:absolute;inset:0;background:radial-gradient(700px 260px at 50% 0%,rgba(99,102,241,.24),transparent 70%);pointer-events:none;}
.lb-loy-cta h2{position:relative;z-index:1;margin:0 0 10px;font-size:clamp(24px,2.8vw,36px);font-weight:850;letter-spacing:-.02em;color:#fff;}
.lb-loy-cta p{position:relative;z-index:1;margin:0 auto 22px;max-width:560px;color:#a9adc4;font-size:14.5px;line-height:1.7;}
.lb-loy-cta-actions{position:relative;z-index:1;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;}

@media(max-width:980px){
  .lb-loy-tier-grid{grid-template-columns:repeat(4,minmax(0,1fr));}
  .lb-loy-store{grid-template-columns:1fr;}
}
@media(max-width:760px){
  .lb-loy-steps{grid-template-columns:1fr 1fr;}
  .lb-loy-mini{grid-template-columns:1fr;max-width:360px;}
}
@media(max-width:560px){
  .lb-loy-tier-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
  .lb-loy-steps{grid-template-columns:1fr;}
}
</style>
<?= $this->stop() ?>

<?php
$loyaltyTiers = [
  ['name' => 'Silver', 'key' => 'silver', 'level' => 1, 'cashback' => '2%', 'color' => '#a7b0c4'],
  ['name' => 'Gold', 'key' => 'gold', 'level' => 2, 'cashback' => '3%', 'color' => '#f59e0b'],
  ['name' => 'Platinum', 'key' => 'platinum', 'level' => 3, 'cashback' => '4%', 'color' => '#22d3ee'],
  ['name' => 'Diamond', 'key' => 'diamond', 'level' => 4, 'cashback' => '5%', 'color' => '#6366f1'],
  ['name' => 'Master', 'key' => 'master', 'level' => 5, 'cashback' => '6%', 'color' => '#a855f7'],
  ['name' => 'Grandmaster', 'key' => 'grandmaster', 'level' => 6, 'cashback' => '7%', 'color' => '#ef4444'],
  ['name' => 'Challenger', 'key' => 'challenger', 'level' => 7, 'cashback' => '8%', 'color' => '#facc15'],
];
?>

<div class="lb-loy-bg" aria-hidden="true"></div>
<div class="lb-loy-gridlines" aria-hidden="true"></div>
<div id="lbLoyStars" aria-hidden="true"></div>

<section class="lb-loy-hero">
  <div class="lb-loy-hero__eyebrow"><span class="dot" aria-hidden="true"></span><span><?= t('Free rewards program') ?></span></div>
  <h1><?= t('Get paid to play.') ?> <span class="accent"><?= t('Every order counts.') ?></span></h1>
  <p class="lb-loy-hero__sub"><?= t("The Loyalty Program gives you real cashback on everything you buy. No sign-up, no catch — just shop, earn Reward Points automatically, and spend them however you like.") ?></p>
  <div class="lb-loy-hero__actions">
    <a class="btn primary" href="/points-store"><i class="fa-solid fa-gift"></i><?= t('Explore rewards') ?></a>
    <a class="btn secondary" href="/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Open Lootboxes') ?></a>
    <a class="btn secondary" href="javascript:void(0)" data-login-trigger="1"><i class="fa-solid fa-user"></i><?= t('View my balance') ?></a>
  </div>
</section>

<section class="lb-loy-section" style="padding-top:0;">
  <div class="lb-loy-wrap">
    <div class="lb-loy-explain">
      <div class="lb-loy-explain-item">
        <i class="fa-solid fa-cart-shopping"></i>
        <b><?= t('1. You shop as usual') ?></b>
        <p><?= t('Buy boosting, accounts, items or digital goods — any eligible order automatically counts.') ?></p>
      </div>
      <div class="lb-loy-explain-item">
        <i class="fa-solid fa-coins"></i>
        <b><?= t('2. You earn Reward Points') ?></b>
        <p><?= t('A % of what you spend comes back as Reward Points — up to 8% once you rank up. No action needed.') ?></p>
      </div>
      <div class="lb-loy-explain-item">
        <i class="fa-solid fa-gift"></i>
        <b><?= t('3. You decide what to do with them') ?></b>
        <p><?= t('Spend points as credit, redeem them in the points store, or gamble them for bigger prizes in the Lootboxes.') ?></p>
      </div>
    </div>
    <div class="lb-loy-trust">
      <span class="badge success"><i class="fa-solid fa-check"></i><?= t('Permanent tiers') ?></span>
      <span class="badge success"><i class="fa-solid fa-check"></i><?= t('Up to 8% cashback') ?></span>
      <span class="badge success"><i class="fa-solid fa-check"></i><?= t('Points never expire') ?></span>
    </div>
    <div class="lb-loy-mini">
      <div class="lb-loy-card lb-loy-mini-card"><i class="fa-solid fa-crown"></i><b>7</b><span><?= t('loyalty tiers') ?></span></div>
      <div class="lb-loy-card lb-loy-mini-card"><i class="fa-solid fa-coins"></i><b>1:1</b><span><?= t('point value') ?></span></div>
      <div class="lb-loy-card lb-loy-mini-card"><i class="fa-solid fa-sparkles"></i><b>8%</b><span><?= t('max cashback') ?></span></div>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-head">
      <span class="lb-loy-eyebrow"><?= t('Loyalty tiers') ?></span>
      <h2><?= t('Seven ranks. Better rewards at every level.') ?></h2>
      <p><?= t('Your tier is based on total lifetime spend and stays with you permanently. The higher your rank, the more you earn back.') ?></p>
    </div>
    <div class="lb-loy-tier-grid">
      <?php foreach ($loyaltyTiers as $tier): ?>
        <article class="lb-loy-card lb-loy-glow lb-loy-tier" style="--tier: <?= $tier['color'] ?>;">
          <div class="lb-loy-tier-icon"><img src="<?= ASSET_URL ?>/core/main/img/loyalty/<?= $tier['key'] ?>_icon.svg" alt="<?= t($tier['name']) ?>"></div>
          <div class="lb-loy-tier-name"><?= t($tier['name']) ?></div>
          <div class="lb-loy-tier-spend">€<?= number_format(get_loyalty_target_price($tier['level']), 0) ?> <?= t('spent') ?></div>
          <div class="lb-loy-tier-cashback"><b><?= $tier['cashback'] ?></b><?= t('cashback') ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-card lb-loy-glow lb-loy-lootbox">
      <div class="lb-loy-lootbox-icon" aria-hidden="true"><i class="fa-solid fa-box-open"></i></div>
      <div class="lb-loy-lootbox-body">
        <h2><?= t('Turn your points into Lootboxes') ?></h2>
        <p><?= t('Open LoLBoost reward boxes and win Reward Points, discount coupons, wallet credit and order perks. Log in to claim a free Daily Gift every 24 hours — no purchase required.') ?></p>
        <div class="lb-loy-lootbox-chips">
          <span><i class="fa-solid fa-gift"></i><?= t('Free Daily Gift') ?></span>
          <span><i class="fa-solid fa-box-open"></i><?= t('Instant Rewards') ?></span>
          <span><i class="fa-solid fa-shield-check"></i><?= t('Saved to Account') ?></span>
          <span><i class="fa-solid fa-layer-group"></i><?= t('Starter to Challenger Box') ?></span>
        </div>
        <div class="lb-loy-lootbox-actions">
          <a class="btn primary" href="/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Open the Lootboxes page') ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-head">
      <span class="lb-loy-eyebrow"><?= t('How it works') ?></span>
      <h2><?= t('Simple. Permanent. Rewarding.') ?></h2>
      <p><?= t('Earn points automatically, level up through lifetime progress and redeem rewards whenever you want.') ?></p>
    </div>
    <div class="lb-loy-steps">
      <article class="lb-loy-card lb-loy-glow lb-loy-step"><span class="lb-loy-step-num">01</span><div class="lb-loy-step-icon"><i class="fa-solid fa-cart-shopping"></i></div><h3><?= t('Use LoLBoost') ?></h3><p><?= t('Buy accounts, digital goods, items, boosting or coaching through eligible marketplace orders.') ?></p></article>
      <article class="lb-loy-card lb-loy-glow lb-loy-step"><span class="lb-loy-step-num">02</span><div class="lb-loy-step-icon"><i class="fa-solid fa-coins"></i></div><h3><?= t('Earn Reward Points') ?></h3><p><?= t('Cashback is added based on your current loyalty tier, from Silver up to Challenger.') ?></p></article>
      <article class="lb-loy-card lb-loy-glow lb-loy-step"><span class="lb-loy-step-num">03</span><div class="lb-loy-step-icon"><i class="fa-solid fa-chart-line"></i></div><h3><?= t('Rank up forever') ?></h3><p><?= t('Your tier increases with lifetime progress and never drops, even if you take a break.') ?></p></article>
      <article class="lb-loy-card lb-loy-glow lb-loy-step"><span class="lb-loy-step-num">04</span><div class="lb-loy-step-icon"><i class="fa-solid fa-gift"></i></div><h3><?= t('Redeem rewards') ?></h3><p><?= t('Spend points in the points store, open Lootboxes for a chance at bigger prizes, or save them for a future order.') ?></p></article>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-head">
      <span class="lb-loy-eyebrow"><?= t('Tier benefits') ?></span>
      <h2><?= t('What you unlock at each level') ?></h2>
      <p><?= t('A clear breakdown of cashback and perks across all loyalty ranks.') ?></p>
    </div>
    <div class="lb-loy-card lb-loy-benefits">
      <div class="lb-loy-benefits-scroll">
        <table>
          <thead>
            <tr>
              <th><i class="fa-solid fa-chart-line"></i><?= t('Loyalty level') ?></th>
              <?php foreach ($loyaltyTiers as $tier): ?>
                <th><img src="<?= ASSET_URL ?>/core/main/img/loyalty/<?= $tier['key'] ?>_icon.svg" alt="<?= t($tier['name']) ?>"><?= t($tier['name']) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <tr><td><i class="fa-solid fa-coins"></i><?= t('Cashback') ?></td><?php foreach ($loyaltyTiers as $tier): ?><td><b><?= $tier['cashback'] ?></b></td><?php endforeach; ?></tr>
            <tr><td><i class="fa-solid fa-gift"></i><?= t('Community giveaways') ?></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><?php for ($i=0; $i<6; $i++): ?><td><i class="fa-solid fa-check lb-loy-check"></i></td><?php endfor; ?></tr>
            <tr><td><i class="fa-solid fa-gifts"></i><?= t('Exclusive giveaways') ?></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><?php for ($i=0; $i<5; $i++): ?><td><i class="fa-solid fa-check lb-loy-check"></i></td><?php endfor; ?></tr>
            <tr><td><i class="fa-solid fa-headset"></i><?= t('Priority support') ?></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><?php for ($i=0; $i<4; $i++): ?><td><i class="fa-solid fa-check lb-loy-check"></i></td><?php endfor; ?></tr>
            <tr><td><i class="fa-solid fa-bolt"></i><?= t('Free priority option') ?></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><?php for ($i=0; $i<3; $i++): ?><td><i class="fa-solid fa-check lb-loy-check"></i></td><?php endfor; ?></tr>
            <tr><td><i class="fa-solid fa-video"></i><?= t('Free streaming option') ?></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-xmark lb-loy-x"></i></td><td><i class="fa-solid fa-check lb-loy-check"></i></td><td><i class="fa-solid fa-check lb-loy-check"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap lb-loy-store">
    <div class="lb-loy-card lb-loy-store-main">
      <span class="lb-loy-eyebrow"><?= t('Points store') ?></span>
      <h2><?= t('Turn your points into real rewards') ?></h2>
      <p><?= t('Redeem your points for vouchers, digital products, gaming gear and special marketplace perks — or open a Lootbox for a chance at more.') ?></p>
      <div class="lb-loy-store-actions">
        <a class="btn primary" href="/points-store"><i class="fa-solid fa-bag-shopping"></i><?= t('Explore the points store') ?></a>
        <a class="btn secondary" href="/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Try the Lootboxes') ?></a>
      </div>
    </div>
    <div class="lb-loy-store-list">
      <div class="lb-loy-card lb-loy-reward"><i class="fa-solid fa-ticket"></i><div><b><?= t('Vouchers') ?></b><span><?= t('Steam, Amazon and LoLBoost credit') ?></span></div></div>
      <div class="lb-loy-card lb-loy-reward"><i class="fa-solid fa-box-open"></i><div><b><?= t('Lootboxes') ?></b><span><?= t('Open boxes for discounts, credit and more') ?></span></div></div>
      <div class="lb-loy-card lb-loy-reward"><i class="fa-solid fa-shield-check"></i><div><b><?= t('Marketplace perks') ?></b><span><?= t('Priority options, support and special benefits') ?></span></div></div>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-head">
      <span class="lb-loy-eyebrow"><?= t('FAQ') ?></span>
      <h2><?= t('Frequently asked questions') ?></h2>
      <p><?= t('Everything you need to know about cashback, loyalty tiers, the points store and Lootboxes.') ?></p>
    </div>
    <div class="lb-loy-faq-tabs" role="tablist" aria-label="<?= t('Loyalty FAQ categories') ?>">
      <button class="lb-loy-faq-tab active" type="button" data-cat="cashback"><?= t('Cashback') ?></button>
      <button class="lb-loy-faq-tab" type="button" data-cat="loyalty"><?= t('Loyalty') ?></button>
      <button class="lb-loy-faq-tab" type="button" data-cat="lootboxes"><?= t('Lootboxes') ?></button>
    </div>
    <div class="lb-loy-faq-wrap accordion" id="lb-loy-faq-list">
      <div class="accordion-item active" data-cat="cashback">
        <div class="accordion-header"><h5><?= t('What is the LoLBoost cashback system?') ?></h5></div>
        <div class="accordion-content"><p><?= t('The cashback system rewards eligible purchases with Reward Points. Points can be used as credit on future orders, exchanged for rewards in the points store, or spent opening Lootboxes.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="cashback">
        <div class="accordion-header"><h5><?= t('How many Reward Points do I earn?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Your cashback depends on your tier: Silver 2%, Gold 3%, Platinum 4%, Diamond 5%, Master 6%, Grandmaster 7% and Challenger 8%.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="cashback">
        <div class="accordion-header"><h5><?= t('Do Reward Points expire?') ?></h5></div>
        <div class="accordion-content"><p><?= t('No. Reward Points do not expire. They remain in your account until you decide to use them.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="loyalty">
        <div class="accordion-header"><h5><?= t('How do I reach a higher loyalty tier?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Simply keep ordering. Your tier is calculated from total lifetime spend, so every eligible purchase helps you move toward the next rank.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="loyalty">
        <div class="accordion-header"><h5><?= t('Can my loyalty rank go down?') ?></h5></div>
        <div class="accordion-content"><p><?= t('No. Your loyalty tier is permanent and never decreases, even if you take a long break.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="loyalty">
        <div class="accordion-header"><h5><?= t('Where can I see my current tier?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Log in and visit your account dashboard. Your current tier, cashback percentage and point balance are shown there.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="lootboxes">
        <div class="accordion-header"><h5><?= t('What are Lootboxes?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Lootboxes are reward boxes ranging from a free Daily Gift to Starter, Silver, Gold, Diamond and Challenger boxes. Each one can contain Reward Points, LB Coins, discount coupons, wallet credit or order perks.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="lootboxes">
        <div class="accordion-header"><h5><?= t('Can I open a Lootbox for free?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Yes. Every logged-in account can claim a free Daily Gift box every 24 hours on the Lootboxes page — no purchase required.') ?></p></div>
      </div>
      <div class="accordion-item" data-cat="lootboxes">
        <div class="accordion-header"><h5><?= t('How do I unlock the bigger boxes?') ?></h5></div>
        <div class="accordion-content"><p><?= t('Higher-value boxes are unlocked with Reward Points earned from your cashback. The more you spend and the higher your tier, the faster you can afford Silver, Gold, Diamond or Challenger boxes.') ?></p></div>
      </div>
    </div>
  </div>
</section>

<section class="lb-loy-section">
  <div class="lb-loy-wrap">
    <div class="lb-loy-card lb-loy-glow lb-loy-cta">
      <span class="lb-loy-eyebrow"><?= t('Start earning') ?></span>
      <h2><?= t('Ready to unlock your next reward?') ?></h2>
      <p><?= t('Log in, place eligible orders and build your permanent LoLBoost loyalty rank — then spend your points in the store or open a Lootbox.') ?></p>
      <div class="lb-loy-cta-actions">
        <a class="btn primary" href="/points-store"><i class="fa-solid fa-gift"></i><?= t('Open points store') ?></a>
        <a class="btn secondary" href="/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Open Lootboxes') ?></a>
        <a class="btn secondary" href="javascript:void(0)" data-login-trigger="1"><i class="fa-solid fa-right-to-bracket"></i><?= t('Log in') ?></a>
      </div>
    </div>
  </div>
</section>

<?= $this->start('scripts') ?>
<script>
(function(){
  var holder = document.getElementById('lbLoyStars');
  if (holder && !window.matchMedia('(max-width:820px)').matches) {
    var frag = document.createDocumentFragment();
    for (var i = 0; i < 28; i++) {
      var p = document.createElement('span');
      p.className = 'lb-loy-star';
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
  var tabs = document.querySelectorAll('.lb-loy-faq-tab');
  var items = document.querySelectorAll('#lb-loy-faq-list .accordion-item');

  function setCat(cat){
    tabs.forEach(function(tab){ tab.classList.toggle('active', tab.getAttribute('data-cat') === cat); });

    var first = true;
    items.forEach(function(item){
      var match = item.getAttribute('data-cat') === cat;
      item.style.display = match ? '' : 'none';
      item.classList.toggle('active', match && first);
      var content = item.querySelector('.accordion-content');
      if (content) content.style.display = (match && first) ? 'block' : 'none';
      if (match) first = false;
    });
  }

  tabs.forEach(function(tab){
    tab.addEventListener('click', function(){ setCat(tab.getAttribute('data-cat')); });
  });

  setCat('cashback');
})();
</script>
<script>
(function(){
  function openLoyaltyLogin(event){
    if (event) event.preventDefault();

    var headerButton = document.getElementById('login-btn') ||
      document.getElementById('login-btn-mobile-header') ||
      document.querySelector('[data-bs-target="#login_modal"], [data-target="#login_modal"], .login-btn, .js-login-btn');

    if (headerButton) {
      headerButton.click();
      return;
    }

    var loginModal = document.getElementById('login_modal') || document.getElementById('loginModal');
    if (loginModal) {
      loginModal.classList.add('show', 'active', 'is-open');
      loginModal.style.display = 'block';
      document.body.classList.add('modal-open', 'auth-modal-open', 'login-modal-open');
      return;
    }

    window.location.href = '<?= BASE_URL ?>/login?redirectUrl=' + encodeURIComponent(window.location.href);
  }

  document.addEventListener('click', function(event){
    var trigger = event.target.closest && event.target.closest('[data-login-trigger="1"]');
    if (trigger) openLoyaltyLogin(event);
  }, true);
})();
</script>
<?= $this->stop() ?>
