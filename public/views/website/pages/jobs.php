<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'lb-jobs-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Jobs / Work With Us (landing-page visual language)
   ============================================================ */
.lb-jobs-page{ overflow-x:hidden; }
.lb-jobs-wrap{ width:min(1220px, calc(100% - 40px)); margin:0 auto; }

.lb-jobs-page{background:#030817;}
.lb-jobs-bg{position:fixed;inset:0;z-index:-3;pointer-events:none;background:#030817;}
.lb-jobs-gridlines,
#lbJobsStars{display:none;}
.lb-jobs-star{position:absolute;left:var(--x,50vw);top:var(--y,50vh);width:var(--s,3px);height:var(--s,3px);border-radius:999px;background:rgba(255,255,255,.95);box-shadow:0 0 10px rgba(255,255,255,.65),0 0 22px rgba(99,102,241,.45);opacity:var(--o,.7);transform:translate3d(0,0,0) scale(.85);animation:lbJobsStar var(--d,22s) linear infinite;animation-delay:var(--delay,0s);will-change:transform,opacity;}
@keyframes lbJobsStar{0%{transform:translate3d(0,0,0) scale(.85);opacity:.1;}14%{opacity:var(--o,.7);}72%{opacity:var(--o,.7);}100%{transform:translate3d(var(--tx,-26vw),var(--ty,20vh),0) scale(1.12);opacity:.05;}}
@media(max-width:820px){#lbJobsStars{display:none;}}
@media(prefers-reduced-motion:reduce){.lb-jobs-star{animation:none!important;}}

.lb-jobs-page a{text-decoration:none;}
.lb-jobs-page .btn{display:inline-flex;align-items:center;gap:10px;}
.lb-jobs-page .btn i{margin:0;}

/* ── Hero ── */
.lb-jobs-hero{position:relative;padding:calc(var(--lb-content-top, 96px) + clamp(48px,6vw,80px)) 24px clamp(46px,6vw,72px);text-align:center;overflow:hidden;}
.lb-jobs-hero__eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 22px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.88);border:1px solid rgba(99,102,241,.4);background:rgba(15,14,32,.6);box-shadow:0 16px 40px rgba(0,0,0,.3),inset 0 1px 0 rgba(255,255,255,.08);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.lb-jobs-hero__eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8b93ff;box-shadow:0 0 0 6px rgba(99,102,241,.16),0 0 18px rgba(99,102,241,.6);animation:lbJobsPulse 2.2s ease-in-out infinite;}
@keyframes lbJobsPulse{0%,100%{box-shadow:0 0 0 6px rgba(99,102,241,.16),0 0 18px rgba(99,102,241,.6);}50%{box-shadow:0 0 0 9px rgba(99,102,241,.22),0 0 26px rgba(99,102,241,.85);}}
@media(prefers-reduced-motion:reduce){.lb-jobs-hero__eyebrow .dot{animation:none!important;}}
.lb-jobs-hero h1{margin:0 auto 20px;max-width:960px;font-size:clamp(38px,5.6vw,72px);line-height:1.04;letter-spacing:-.035em;font-weight:950;color:#fff;text-shadow:0 20px 50px rgba(0,0,0,.5);}
.lb-jobs-hero h1 .accent{background-image:linear-gradient(92deg,#60a5fa 0%,#818cf8 50%,#6366f1 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6366f1;}
.lb-jobs-hero__sub{max-width:660px;margin:0 auto 30px;color:rgba(238,244,255,.72);font-size:clamp(15px,1.1vw,19px);line-height:1.7;font-weight:500;}
.lb-jobs-hero__actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-bottom:30px;}
.lb-jobs-hero__actions .btn{min-height:54px;padding:0 28px;border-radius:15px;}
@media(max-width:640px){.lb-jobs-hero__actions .btn{width:100%;max-width:340px;justify-content:center;}}

/* Stat strip — single connected bar instead of 4 duplicate cards */
.lb-jobs-statbar{position:relative;max-width:920px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);border-radius:24px;border:1px solid rgba(129,140,248,.22);background:#090f24;box-shadow:0 18px 50px rgba(0,0,0,.28);overflow:hidden;}
.lb-jobs-stat{padding:22px 14px;text-align:center;border-right:1px solid rgba(255,255,255,.07);}
.lb-jobs-stat:last-child{border-right:none;}
.lb-jobs-stat b{display:block;font-size:26px;font-weight:950;letter-spacing:-.03em;background-image:linear-gradient(92deg,#a5b4fc,#818cf8);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.lb-jobs-stat span{display:block;margin-top:5px;color:rgba(255,255,255,.5);font-size:11.5px;font-weight:700;letter-spacing:.02em;}
@media(max-width:640px){.lb-jobs-statbar{grid-template-columns:repeat(2,1fr);}.lb-jobs-stat{border-bottom:1px solid rgba(255,255,255,.07);}}

/* ── Section rhythm ── */
.lb-jobs-section{position:relative;padding:clamp(64px,7vw,104px) 0;}
.lb-jobs-head{max-width:720px;margin:0 auto 40px;text-align:center;}
.lb-jobs-eyebrow{display:inline-flex;align-items:center;gap:9px;margin-bottom:14px;color:rgba(180,188,255,.85);font-size:12.5px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;}
.lb-jobs-eyebrow span{width:26px;height:2px;border-radius:999px;background:linear-gradient(90deg,#6366f1,#818cf8);}
.lb-jobs-head h2{margin:0 0 14px;font-size:clamp(28px,3.4vw,46px);font-weight:900;letter-spacing:-.03em;color:#fff;line-height:1.08;}
.lb-jobs-head p{margin:0;color:rgba(255,255,255,.56);font-size:16px;line-height:1.75;}

/* ── Compact path tiles ── */
.lb-jobs-path-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;align-items:stretch;}
.lb-jobs-path{position:relative;overflow:hidden;padding:20px;border-radius:18px;border:1px solid rgba(255,255,255,.10);background:#090f24;box-shadow:0 12px 34px rgba(0,0,0,.25);display:flex;flex-direction:column;gap:10px;height:100%;min-height:170px;transition:transform .24s cubic-bezier(.22,1,.36,1), border-color .24s ease, box-shadow .24s ease;}
.lb-jobs-path:before{display:none;}
.lb-jobs-path:hover{transform:translateY(-5px);border-color:rgba(129,140,248,.4);box-shadow:0 22px 60px rgba(0,0,0,.45), 0 0 0 1px rgba(129,140,248,.14);}
.lb-jobs-path > *{position:relative;z-index:1;}
.lb-jobs-path-icon{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;background:linear-gradient(135deg,#3b58e8,#6366f1);box-shadow:0 10px 24px rgba(99,102,241,.28);flex-shrink:0;}
.lb-jobs-path h3{margin:2px 0 0;font-size:15.5px;font-weight:850;letter-spacing:-.01em;color:#fff;}
.lb-jobs-path p{margin:0;color:rgba(255,255,255,.52);font-size:12.5px;line-height:1.6;}
.lb-jobs-path-cta{display:inline-flex;align-items:center;gap:6px;margin-top:auto;padding-top:4px;font-weight:850;color:#a5b4fc;font-size:12px;transition:gap .2s ease,color .2s ease;}
.lb-jobs-path:hover .lb-jobs-path-cta{gap:10px;color:#fff;}
@media(max-width:900px){.lb-jobs-path-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:520px){.lb-jobs-path-grid{grid-template-columns:1fr;}}

/* ── Trust pillars ── */
.lb-jobs-trust-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.lb-jobs-trust-item{display:flex;align-items:flex-start;gap:14px;padding:22px;border-radius:20px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);}
.lb-jobs-trust-item i{width:38px;height:38px;border-radius:999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(34,197,94,.3);background:rgba(34,197,94,.13);color:#22c55e;font-size:14px;}
.lb-jobs-trust-item strong{display:block;font-size:14.5px;color:#fff;font-weight:850;}
.lb-jobs-trust-item span{display:block;margin-top:4px;color:rgba(255,255,255,.5);font-size:13px;line-height:1.55;}
@media(max-width:760px){.lb-jobs-trust-row{grid-template-columns:1fr;}}

/* ── Connected process timeline (line runs only in the gaps, never under an icon) ── */
.lb-jobs-timeline{display:flex;align-items:flex-start;}
.lb-jobs-tl-item{position:relative;flex:1 1 0;text-align:center;padding:0 12px;min-width:0;}
.lb-jobs-tl-connector{flex:0 0 auto;width:clamp(24px,6vw,64px);height:2px;margin-top:29px;background:linear-gradient(90deg,rgba(129,140,248,.55),rgba(96,165,250,.45));border-radius:999px;}
.lb-jobs-tl-num{position:relative;z-index:1;width:58px;height:58px;margin:0 auto 20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:19px;font-weight:950;color:#fff;background:radial-gradient(circle at 35% 30%, rgba(99,102,241,.55), rgba(10,13,30,.98) 72%);border:1px solid rgba(129,140,248,.45);box-shadow:0 0 0 6px #04030e, 0 14px 32px rgba(99,102,241,.24);}
.lb-jobs-tl-item h3{margin:0 0 8px;font-size:16.5px;font-weight:850;color:#fff;letter-spacing:-.01em;}
.lb-jobs-tl-item p{margin:0;color:rgba(255,255,255,.52);font-size:13.5px;line-height:1.65;max-width:230px;margin-inline:auto;}
@media(max-width:820px){.lb-jobs-timeline{flex-direction:column;gap:26px;}.lb-jobs-tl-connector{display:none;}}

/* ── Role picker strip ── */
.lb-jobs-roles{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;}
.lb-jobs-role-pill{display:inline-flex;align-items:center;gap:11px;padding:14px 22px 14px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.10);background:#090f24;box-shadow:0 10px 30px rgba(0,0,0,.24);transition:transform .22s cubic-bezier(.22,1,.36,1),border-color .22s ease,box-shadow .22s ease,background .22s ease;}
.lb-jobs-role-pill i{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;background:linear-gradient(135deg,#3b58e8,#6366f1);flex-shrink:0;}
.lb-jobs-role-pill strong{color:#fff;font-size:14px;font-weight:850;}
.lb-jobs-role-pill:hover{transform:translateY(-4px);border-color:rgba(129,140,248,.4);box-shadow:0 18px 46px rgba(0,0,0,.28);background:#0c1430;}

/* ── FAQ, landing-style premium accordion ── */
.lb-jobs-faq-wrap{max-width:900px;margin:0 auto;border-radius:26px;padding:12px;background:#080e21;border:1px solid rgba(96,165,250,.18);box-shadow:0 20px 60px rgba(0,0,0,.28);}
.lb-jobs-faq-item{position:relative;overflow:hidden;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:#0b1228;margin-bottom:10px;}
.lb-jobs-faq-item:last-child{margin-bottom:0;}
.lb-jobs-faq-item:before{content:"";position:absolute;left:0;top:16px;bottom:16px;width:3px;border-radius:0 999px 999px 0;background:linear-gradient(180deg,#818cf8,#60a5fa);opacity:0;transition:opacity .2s ease;}
.lb-jobs-faq-item.open{border-color:rgba(129,140,248,.30);background:#0d1631;}
.lb-jobs-faq-item.open:before{opacity:1;}
.lb-jobs-faq-btn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 22px;background:none;border:none;color:rgba(255,255,255,.92);cursor:pointer;font-size:15px;font-weight:800;text-align:left;font-family:inherit;}
.lb-jobs-faq-chev{width:32px;height:32px;flex:0 0 32px;display:flex;align-items:center;justify-content:center;border-radius:11px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.55);font-size:12px;transition:transform .28s ease;}
.lb-jobs-faq-item.open .lb-jobs-faq-chev{transform:rotate(180deg);background:rgba(99,102,241,.18);border-color:rgba(129,140,248,.35);color:#fff;}
.lb-jobs-faq-panel{max-height:0;overflow:hidden;transition:max-height .32s ease;}
.lb-jobs-faq-item.open .lb-jobs-faq-panel{max-height:300px;}
.lb-jobs-faq-inner{padding:0 22px 20px;color:rgba(255,255,255,.58);line-height:1.7;font-size:14px;}

/* ── Final CTA ── */
.lb-jobs-cta{position:relative;overflow:hidden;padding:clamp(40px,5vw,64px) clamp(24px,4vw,56px);text-align:center;border-radius:32px;border:1px solid rgba(129,140,248,.24);background:#090f24;box-shadow:0 24px 70px rgba(0,0,0,.3);}
.lb-jobs-cta:before{display:none;}
.lb-jobs-cta > *{position:relative;z-index:1;}
.lb-jobs-cta h2{margin:0 0 12px;font-size:clamp(26px,3.2vw,42px);font-weight:900;letter-spacing:-.03em;color:#fff;}
.lb-jobs-cta p{margin:0 auto 26px;max-width:560px;color:rgba(255,255,255,.58);font-size:15px;line-height:1.75;}
.lb-jobs-cta-actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;}

@media(max-width:760px){
  .lb-jobs-section{padding:56px 0;}
}
</style>
<?= $this->stop() ?>

<div class="lb-jobs-bg" aria-hidden="true"></div>
<div class="lb-jobs-gridlines" aria-hidden="true"></div>
<div id="lbJobsStars" aria-hidden="true"></div>

<section class="lb-jobs-hero">
  <div class="lb-jobs-hero__eyebrow"><span class="dot" aria-hidden="true"></span><span><?= t('Work with LoLBoost.gg') ?></span></div>
  <h1><?= t('Work with the') ?> <span class="accent"><?= t('LoLBoost Marketplace') ?></span></h1>
  <p class="lb-jobs-hero__sub"><?= t('Join as a seller, digital goods provider, booster or coach. Bring accounts, items, services or expertise and work with a premium marketplace built around trust, speed and support.') ?></p>
  <div class="lb-jobs-hero__actions">
    <a class="btn primary" href="/jobs/apply"><i class="fa-solid fa-paper-plane"></i><?= t('Apply now') ?></a>
    <a class="btn secondary" href="/become-a-seller"><i class="fa-solid fa-store"></i><?= t('Become a seller') ?></a>
  </div>

  <div class="lb-jobs-statbar">
    <div class="lb-jobs-stat"><b>4</b><span><?= t('Partner paths') ?></span></div>
    <div class="lb-jobs-stat"><b>24/7</b><span><?= t('Marketplace support') ?></span></div>
    <div class="lb-jobs-stat"><b>Fast</b><span><?= t('Review process') ?></span></div>
    <div class="lb-jobs-stat"><b>0%</b><span><?= t('Hidden fees') ?></span></div>
  </div>
</section>

<section class="lb-jobs-section" style="padding-top:0;">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-head">
      <div class="lb-jobs-eyebrow" style="justify-content:center;"><span></span><?= t('Partner paths') ?><span></span></div>
      <h2><?= t('One page for every way to earn') ?></h2>
      <p><?= t('This page is the overview. The apply page is the step-by-step flow. Pick the path that matches what you can offer.') ?></p>
    </div>

    <div class="lb-jobs-path-grid">
      <article class="lb-jobs-path">
        <div class="lb-jobs-path-icon"><i class="fa-solid fa-user-shield"></i></div>
        <div>
          <h3><?= t('Sell accounts') ?></h3>
          <p><?= t('Verified gaming accounts with clear rank, region and delivery details.') ?></p>
        </div>
        <a class="lb-jobs-path-cta" href="/become-a-seller"><?= t('Start selling') ?> <i class="fa-solid fa-arrow-right"></i></a>
      </article>

      <article class="lb-jobs-path">
        <div class="lb-jobs-path-icon"><i class="fa-solid fa-gem"></i></div>
        <div>
          <h3><?= t('Digital goods') ?></h3>
          <p><?= t('Items, currencies, collectibles, bundles and codes with clean fulfillment rules.') ?></p>
        </div>
        <a class="lb-jobs-path-cta" href="/jobs/apply"><?= t('Apply as seller') ?> <i class="fa-solid fa-arrow-right"></i></a>
      </article>

      <article class="lb-jobs-path">
        <div class="lb-jobs-path-icon"><i class="fa-solid fa-bolt"></i></div>
        <div>
          <h3><?= t('Boosting services') ?></h3>
          <p><?= t('Rank progression, placements, missions, leveling and duo sessions.') ?></p>
        </div>
        <a class="lb-jobs-path-cta" href="/jobs/apply"><?= t('Apply as provider') ?> <i class="fa-solid fa-arrow-right"></i></a>
      </article>

      <article class="lb-jobs-path">
        <div class="lb-jobs-path-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div>
          <h3><?= t('Coaching') ?></h3>
          <p><?= t('Live sessions, VOD reviews, replay analysis and strategy calls.') ?></p>
        </div>
        <a class="lb-jobs-path-cta" href="/jobs/apply"><?= t('Apply as coach') ?> <i class="fa-solid fa-arrow-right"></i></a>
      </article>
    </div>
  </div>
</section>

<section class="lb-jobs-section" style="border-top:1px solid rgba(255,255,255,.06);">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-head">
      <div class="lb-jobs-eyebrow" style="justify-content:center;"><span></span><?= t('Why partners stay') ?><span></span></div>
      <h2><?= t('Built for serious marketplace partners') ?></h2>
      <p><?= t('LoLBoost.gg should feel premium from the first click: clean listings, clear expectations, trusted fulfillment and support when you need it.') ?></p>
    </div>

    <div class="lb-jobs-trust-row">
      <div class="lb-jobs-trust-item">
        <i class="fa-solid fa-check"></i>
        <div><strong><?= t('Clean repeatable offers') ?></strong><span><?= t('Turn accounts, items and service packages into offers buyers understand fast.') ?></span></div>
      </div>
      <div class="lb-jobs-trust-item">
        <i class="fa-solid fa-check"></i>
        <div><strong><?= t('Reputation-driven growth') ?></strong><span><?= t('Good delivery and communication help your profile stand out.') ?></span></div>
      </div>
      <div class="lb-jobs-trust-item">
        <i class="fa-solid fa-check"></i>
        <div><strong><?= t('Support behind the scenes') ?></strong><span><?= t('Our team helps with onboarding, order questions and disputes.') ?></span></div>
      </div>
    </div>
  </div>
</section>

<section class="lb-jobs-section" style="border-top:1px solid rgba(255,255,255,.06);">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-head">
      <div class="lb-jobs-eyebrow" style="justify-content:center;"><span></span><?= t('Process') ?><span></span></div>
      <h2><?= t('From application to first order') ?></h2>
      <p><?= t('This page explains the opportunity. The apply page handles the step-by-step form and collects the right details for your role.') ?></p>
    </div>

    <div class="lb-jobs-timeline">
      <div class="lb-jobs-tl-item"><div class="lb-jobs-tl-num">01</div><h3><?= t('Choose your path') ?></h3><p><?= t('Select seller, digital goods, boosting or coaching.') ?></p></div>
      <div class="lb-jobs-tl-connector" aria-hidden="true"></div>
      <div class="lb-jobs-tl-item"><div class="lb-jobs-tl-num">02</div><h3><?= t('Get reviewed') ?></h3><p><?= t('We check quality, delivery and marketplace fit.') ?></p></div>
      <div class="lb-jobs-tl-connector" aria-hidden="true"></div>
      <div class="lb-jobs-tl-item"><div class="lb-jobs-tl-num">03</div><h3><?= t('Go live') ?></h3><p><?= t('Create listings with clear pricing and delivery rules.') ?></p></div>
      <div class="lb-jobs-tl-connector" aria-hidden="true"></div>
      <div class="lb-jobs-tl-item"><div class="lb-jobs-tl-num">04</div><h3><?= t('Earn') ?></h3><p><?= t('Complete orders and receive payouts.') ?></p></div>
    </div>
  </div>
</section>

<section class="lb-jobs-section" style="border-top:1px solid rgba(255,255,255,.06);">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-head">
      <div class="lb-jobs-eyebrow" style="justify-content:center;"><span></span><?= t('Apply flow') ?><span></span></div>
      <h2><?= t('Pick a role to get started') ?></h2>
      <p><?= t('Every path leads into the same clean, role-based application flow.') ?></p>
    </div>

    <div class="lb-jobs-roles">
      <a class="lb-jobs-role-pill" href="/jobs/apply"><i class="fa-solid fa-user-shield"></i><strong><?= t('Seller') ?></strong></a>
      <a class="lb-jobs-role-pill" href="/jobs/apply"><i class="fa-solid fa-bolt"></i><strong><?= t('Booster') ?></strong></a>
      <a class="lb-jobs-role-pill" href="/jobs/apply"><i class="fa-solid fa-chalkboard-user"></i><strong><?= t('Coach') ?></strong></a>
      <a class="lb-jobs-role-pill" href="/jobs/apply"><i class="fa-solid fa-box-open"></i><strong><?= t('Digital goods') ?></strong></a>
      <a class="lb-jobs-role-pill" href="/jobs/apply"><i class="fa-solid fa-handshake"></i><strong><?= t('Partner') ?></strong></a>
    </div>
  </div>
</section>

<section class="lb-jobs-section" style="border-top:1px solid rgba(255,255,255,.06);">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-head">
      <div class="lb-jobs-eyebrow" style="justify-content:center;"><span></span><?= t('FAQ') ?><span></span></div>
      <h2><?= t('Frequently asked questions') ?></h2>
      <p><?= t('Everything you need to know before you apply.') ?></p>
    </div>

    <div class="lb-jobs-faq-wrap" id="lbJobsFaq">
      <div class="lb-jobs-faq-item open">
        <button type="button" class="lb-jobs-faq-btn"><span><?= t('What can I sell on LoLBoost.gg?') ?></span><span class="lb-jobs-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
        <div class="lb-jobs-faq-panel"><div class="lb-jobs-faq-inner"><?= t('Accounts, eligible digital goods, items, currencies, collectibles and service packages such as boosting or coaching, depending on approved games and categories.') ?></div></div>
      </div>
      <div class="lb-jobs-faq-item">
        <button type="button" class="lb-jobs-faq-btn"><span><?= t('Do I need to be a booster?') ?></span><span class="lb-jobs-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
        <div class="lb-jobs-faq-panel"><div class="lb-jobs-faq-inner"><?= t('No. LoLBoost.gg is built for marketplace partners too. Sellers with account inventory or digital goods can apply as well.') ?></div></div>
      </div>
      <div class="lb-jobs-faq-item">
        <button type="button" class="lb-jobs-faq-btn"><span><?= t('How do payouts work?') ?></span><span class="lb-jobs-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
        <div class="lb-jobs-faq-panel"><div class="lb-jobs-faq-inner"><?= t('Approved partners receive payout details during onboarding. Payout availability depends on role, order status, verification and payment method.') ?></div></div>
      </div>
      <div class="lb-jobs-faq-item">
        <button type="button" class="lb-jobs-faq-btn"><span><?= t('Can I offer coaching only?') ?></span><span class="lb-jobs-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
        <div class="lb-jobs-faq-panel"><div class="lb-jobs-faq-inner"><?= t('Yes. Coaches can apply with live coaching, VOD review, replay analysis, mechanics training or strategy sessions.') ?></div></div>
      </div>
      <div class="lb-jobs-faq-item">
        <button type="button" class="lb-jobs-faq-btn"><span><?= t('How long does review take?') ?></span><span class="lb-jobs-faq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
        <div class="lb-jobs-faq-panel"><div class="lb-jobs-faq-inner"><?= t('Most applications are reviewed within a few days. Strong booster applicants may be invited to a Discord trial before final approval.') ?></div></div>
      </div>
    </div>
  </div>
</section>

<section class="lb-jobs-section" style="border-top:1px solid rgba(255,255,255,.06);">
  <div class="lb-jobs-wrap">
    <div class="lb-jobs-cta">
      <h2><?= t('Ready to work with LoLBoost.gg?') ?></h2>
      <p><?= t('Apply as a seller, digital goods provider, booster or coach and build your marketplace income with LoLBoost.gg.') ?></p>
      <div class="lb-jobs-cta-actions">
        <a class="btn primary" href="/jobs/apply"><i class="fa-solid fa-paper-plane"></i><?= t('Apply now') ?></a>
        <a class="btn secondary" href="/login"><i class="fa-solid fa-right-to-bracket"></i><?= t('Already approved? Log in') ?></a>
      </div>
    </div>
  </div>
</section>

<?= $this->start('scripts') ?>
<script>
(function(){
  var holder = document.getElementById('lbJobsStars');
  if (holder && !window.matchMedia('(max-width:820px)').matches) {
    var frag = document.createDocumentFragment();
    for (var i = 0; i < 26; i++) {
      var p = document.createElement('span');
      p.className = 'lb-jobs-star';
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

  var faq = document.getElementById('lbJobsFaq');
  if (faq) {
    faq.querySelectorAll('.lb-jobs-faq-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var item = btn.closest('.lb-jobs-faq-item');
        var wasOpen = item.classList.contains('open');
        faq.querySelectorAll('.lb-jobs-faq-item').forEach(function(el){ el.classList.remove('open'); });
        if (!wasOpen) item.classList.add('open');
      });
    });
  }
})();
</script>
<?= $this->stop() ?>
