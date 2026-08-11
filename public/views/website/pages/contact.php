<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'gm-contact-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Contact (same visual system as Lootboxes/Loyalty)
   ============================================================ */
.gm-contact-page main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.ct2-wrap{width:min(1300px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1;}

/* Dynamic backdrop */
.ct2-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden;background:
  radial-gradient(1200px 700px at 80% 6%, rgba(109,140,255,.20), transparent 60%),
  radial-gradient(900px 620px at 15% 12%, rgba(217,70,239,.10), transparent 58%),
  radial-gradient(1000px 700px at 50% 95%, rgba(56,189,248,.10), transparent 60%),
  linear-gradient(180deg,#0a0818 0%, #0e0c22 55%, #0a0818 100%);
}
.ct2-gridlines{position:fixed;inset:-2px;z-index:-1;pointer-events:none;opacity:.13;background-image:
  linear-gradient(to right, rgba(255,255,255,.06) 1px, transparent 1px),
  linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(closest-side at 50% 10%, black 0%, transparent 74%);
}
#ctStars{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;mix-blend-mode:screen;}
.ct2-star{position:absolute;left:var(--x,50vw);top:var(--y,50vh);width:var(--s,3px);height:var(--s,3px);border-radius:999px;background:rgba(255,255,255,.95);box-shadow:0 0 10px rgba(255,255,255,.65),0 0 22px rgba(109,140,255,.45);opacity:var(--o,.7);transform:translate3d(0,0,0) scale(.85);animation:ct2Star var(--d,22s) linear infinite;animation-delay:var(--delay,0s);will-change:transform,opacity;}
@keyframes ct2Star{
  0%{transform:translate3d(0,0,0) scale(.85);opacity:.1;}
  14%{opacity:var(--o,.7);}
  72%{opacity:var(--o,.7);}
  100%{transform:translate3d(var(--tx,-26vw),var(--ty,20vh),0) scale(1.12);opacity:.05;}
}
@media(max-width:820px){#ctStars{display:none;}}
@media(prefers-reduced-motion:reduce){.ct2-star{animation:none!important;}}

/* Card recipe */
.ct2-card{background:#13112a;border:1px solid rgba(109,140,255,.20);border-radius:20px;box-shadow:0 18px 46px rgba(0,0,0,.32);}

/* Buttons */
.ct2-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 22px;border-radius:12px;font-weight:800;font-size:.95rem;text-decoration:none;border:1px solid transparent;cursor:pointer;font-family:inherit;transition:transform .15s ease,filter .15s ease,border-color .15s ease,background .15s ease;}
.ct2-btn-primary{background:linear-gradient(135deg,#6d8cff,#7c5cff);color:#fff;box-shadow:0 14px 30px rgba(109,140,255,.3);}
.ct2-btn-primary:hover{color:#fff;filter:brightness(1.08);transform:translateY(-1px);}
.ct2-btn-ghost{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.85);}
.ct2-btn-ghost:hover{color:#fff;border-color:rgba(109,140,255,.4);background:rgba(109,140,255,.1);}
@media(max-width:640px){.ct2-hero-actions .ct2-btn{width:100%;max-width:340px;}}

/* Hero — centered, same DNA as loyalty/lootboxes/landing */
.ct2-hero{position:relative;padding:8px 24px clamp(34px,4.5vw,54px);text-align:center;overflow:hidden;}
.ct2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 20px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.88);border:1px solid rgba(109,140,255,.4);background:rgba(15,14,32,.6);box-shadow:0 16px 40px rgba(0,0,0,.3),inset 0 1px 0 rgba(255,255,255,.08);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.ct2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);animation:ct2Pulse 2.2s ease-in-out infinite;}
@keyframes ct2Pulse{0%,100%{box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);}50%{box-shadow:0 0 0 9px rgba(109,140,255,.22),0 0 26px rgba(109,140,255,.85);}}
@media(prefers-reduced-motion:reduce){.ct2-eyebrow .dot{animation:none!important;}}
.ct2-hero-title{margin:0 auto 18px;max-width:860px;font-size:clamp(34px,5vw,58px);line-height:1.08;letter-spacing:-.02em;font-weight:950;color:#fff;text-transform:uppercase;text-shadow:0 20px 50px rgba(0,0,0,.5);}
.ct2-hero-title .accent{background-image:linear-gradient(92deg,#9db7ff 0%,#7c9bff 50%,#6d8cff 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6d8cff;}
.ct2-hero-sub{max-width:680px;margin:0 auto 28px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}
.ct2-hero-actions{display:flex;justify-content:center;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:26px;}
.ct2-hero-chips{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;}
.ct2-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.76);font-size:12px;font-weight:800;}
.ct2-chip i{color:#8fb2ff;}

/* Section rhythm */
.ct2-section{padding:34px 0;}
.ct2-section + .ct2-section{border-top:1px solid rgba(255,255,255,.06);}
.ct2-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.ct2-section-head h2{margin:0;font-size:clamp(20px,2.2vw,26px);font-weight:900;color:#fff;letter-spacing:-.01em;}
.ct2-section-sub{margin:6px 0 0;color:rgba(255,255,255,.5);font-size:.9rem;font-weight:650;max-width:520px;}

/* Contact options */
.ct2-contact-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;align-items:stretch;}
.ct2-lead-card{padding:26px;display:flex;flex-direction:column;}
.ct2-lead-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:rgba(109,140,255,.14);border:1px solid rgba(109,140,255,.26);color:#8fb2ff;font-size:22px;margin-bottom:18px;}
.ct2-lead-kicker{color:#9db7ff;font-weight:900;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;margin-bottom:8px;}
.ct2-lead-card h3{margin:0 0 12px;font-size:clamp(20px,2.4vw,28px);font-weight:900;color:#fff;letter-spacing:-.01em;}
.ct2-lead-card p{margin:0 0 22px;color:rgba(255,255,255,.55);font-size:.92rem;line-height:1.65;flex:1;}
.ct2-lead-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.ct2-status{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.24);color:#86efac;font-size:.76rem;font-weight:900;}
.ct2-status:before{content:"";width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 5px rgba(34,197,94,.14);}

.ct2-support-list{display:grid;gap:12px;}
.ct2-support-item{padding:16px;}
.ct2-support-item h4{margin:0 0 8px;font-size:.98rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:9px;}
.ct2-support-item h4 i{width:28px;height:28px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(109,140,255,.14);border:1px solid rgba(109,140,255,.26);color:#8fb2ff;font-size:.78rem;flex:0 0 auto;}
.ct2-support-item p{margin:0;color:rgba(255,255,255,.55);font-size:.84rem;line-height:1.55;}
@media(max-width:900px){.ct2-contact-grid{grid-template-columns:1fr;}}

/* Topics */
.ct2-topic-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;}
.ct2-topic{padding:20px;transition:transform .18s ease,border-color .18s ease;}
.ct2-topic:hover{transform:translateY(-3px);border-color:rgba(109,140,255,.45);}
.ct2-topic span{width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:14px;background:rgba(109,140,255,.14);border:1px solid rgba(109,140,255,.26);color:#8fb2ff;font-size:19px;margin-bottom:16px;}
.ct2-topic h3{margin:0 0 8px;font-size:1rem;font-weight:900;color:#fff;}
.ct2-topic p{margin:0;color:rgba(255,255,255,.55);font-size:.84rem;line-height:1.55;}
@media(max-width:900px){.ct2-topic-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:560px){.ct2-topic-grid{grid-template-columns:1fr;}}

/* FAQ */
.ct2-faq-item{border:1px solid rgba(255,255,255,.07);border-radius:13px;background:rgba(0,0,0,.13);overflow:hidden;margin-bottom:8px;}
.ct2-faq-item summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;color:rgba(255,255,255,.88);font-weight:800;font-size:.95rem;}
.ct2-faq-item summary::-webkit-details-marker{display:none;}
.ct2-faq-item summary:after{content:"\f078";font-family:"Font Awesome 6 Pro","Font Awesome 6 Free";font-weight:900;color:rgba(143,178,255,.8);font-size:.78rem;transition:transform .16s ease;}
.ct2-faq-item[open] summary:after{transform:rotate(180deg);}
.ct2-faq-item p{margin:0;padding:0 18px 17px;color:rgba(255,255,255,.5);font-size:.86rem;line-height:1.65;}
.ct2-faq-item[open]{border-color:rgba(109,140,255,.28);background:rgba(109,140,255,.06);}

/* Final CTA */
.ct2-cta{padding:clamp(28px,3.6vw,48px) clamp(20px,4vw,52px);text-align:center;position:relative;overflow:hidden;}
.ct2-cta:before{content:"";position:absolute;inset:0;background:radial-gradient(700px 260px at 50% 0%,rgba(109,140,255,.24),transparent 70%);pointer-events:none;}
.ct2-cta h2{position:relative;z-index:1;margin:0 0 10px;font-size:clamp(24px,2.8vw,36px);font-weight:900;color:#fff;letter-spacing:-.02em;}
.ct2-cta p{position:relative;z-index:1;margin:0 auto 22px;max-width:560px;color:rgba(255,255,255,.55);font-size:.92rem;line-height:1.65;}
.ct2-cta-actions{position:relative;z-index:1;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;}
</style>
<?= $this->end() ?>

<div class="ct2-bg" aria-hidden="true"></div>
<div class="ct2-gridlines" aria-hidden="true"></div>
<div id="ctStars" aria-hidden="true"></div>

<div class="ct2-wrap">

  <section class="ct2-hero">
    <div class="ct2-eyebrow"><span class="dot"></span><span><?= t('LoLBoost.gg Support') ?></span></div>
    <h1 class="ct2-hero-title"><?= t('Need help with') ?> <span class="accent"><?= t('orders, sellers or services?') ?></span></h1>
    <p class="ct2-hero-sub"><?= t('Contact LoLBoost.gg support for marketplace orders, account purchases, digital goods, boosting, coaching, seller questions and payment help. We route your request to the right team fast.') ?></p>
    <div class="ct2-hero-actions">
      <a href="#" id="liveChatBtn" class="ct2-btn ct2-btn-primary"><i class="fa-solid fa-comments"></i><?= t('Open Live Chat') ?></a>
      <a href="https://discord.gg/lolboost" target="_blank" rel="noopener" class="ct2-btn ct2-btn-ghost"><i class="fa-brands fa-discord"></i><?= t('Join Discord') ?></a>
    </div>
    <div class="ct2-hero-chips">
      <span class="ct2-chip"><i class="fa-solid fa-headset"></i><?= t('24/7 support') ?></span>
      <span class="ct2-chip"><i class="fa-solid fa-box-open"></i><?= t('Order help') ?></span>
      <span class="ct2-chip"><i class="fa-solid fa-store"></i><?= t('Seller assistance') ?></span>
      <span class="ct2-chip"><i class="fa-solid fa-shield-halved"></i><?= t('Buyer protection') ?></span>
    </div>
  </section>

  <section class="ct2-section">
    <div class="ct2-section-head">
      <div>
        <h2><?= t('Choose the fastest path') ?></h2>
        <p class="ct2-section-sub"><?= t('Live chat is best for active orders. Discord is best for community updates, quick questions and staying close to LoLBoost.gg.') ?></p>
      </div>
    </div>
    <div class="ct2-contact-grid">
      <article class="ct2-card ct2-lead-card">
        <div class="ct2-lead-icon"><i class="fa-solid fa-comments"></i></div>
        <div class="ct2-lead-kicker"><?= t('Fastest reply') ?></div>
        <h3><?= t('Live chat with our team') ?></h3>
        <p><?= t('Have a question about an order, delivery, refund, account purchase, seller payout or service status? Start a live chat and we will guide you through the next step.') ?></p>
        <div class="ct2-lead-actions">
          <a href="#" id="liveChatBtn2" class="ct2-btn ct2-btn-primary"><i class="fa-solid fa-message"></i><?= t('Start live chat') ?></a>
          <span class="ct2-status"><?= t('Online support') ?></span>
        </div>
      </article>
      <div class="ct2-support-list">
        <article class="ct2-card ct2-support-item">
          <h4><i class="fa-solid fa-cart-shopping"></i><?= t('Orders & delivery') ?></h4>
          <p><?= t('Get help with purchases, order status, delivery details, disputes and post-purchase questions.') ?></p>
        </article>
        <article class="ct2-card ct2-support-item">
          <h4><i class="fa-solid fa-store"></i><?= t('Seller support') ?></h4>
          <p><?= t('Questions about listings, verification, payouts, marketplace rules or becoming a seller.') ?></p>
        </article>
        <article class="ct2-card ct2-support-item">
          <h4><i class="fa-solid fa-gamepad"></i><?= t('Services') ?></h4>
          <p><?= t('Boosting, coaching, VOD reviews, custom orders and provider communication in one place.') ?></p>
        </article>
      </div>
    </div>
  </section>

  <section class="ct2-section">
    <div class="ct2-section-head">
      <div>
        <h2><?= t('Marketplace support for every category') ?></h2>
      </div>
    </div>
    <div class="ct2-topic-grid">
      <article class="ct2-card ct2-topic">
        <span><i class="fa-solid fa-user-shield"></i></span>
        <h3><?= t('Accounts') ?></h3>
        <p><?= t('Account delivery, login details, account quality, warranty questions and buyer protection.') ?></p>
      </article>
      <article class="ct2-card ct2-topic">
        <span><i class="fa-solid fa-gem"></i></span>
        <h3><?= t('Digital goods') ?></h3>
        <p><?= t('Items, skins, currencies, collectibles and digital product delivery questions.') ?></p>
      </article>
      <article class="ct2-card ct2-topic">
        <span><i class="fa-solid fa-bolt"></i></span>
        <h3><?= t('Boosting') ?></h3>
        <p><?= t('Rank boosts, leveling, mission progress, ETA questions and order communication.') ?></p>
      </article>
      <article class="ct2-card ct2-topic">
        <span><i class="fa-solid fa-headset"></i></span>
        <h3><?= t('Coaching') ?></h3>
        <p><?= t('Session scheduling, coach matching, VOD reviews, mechanics training and progress plans.') ?></p>
      </article>
    </div>
  </section>

  <section class="ct2-section">
    <div class="ct2-section-head">
      <div>
        <h2><?= t('Frequently asked questions') ?></h2>
      </div>
    </div>
    <div class="ct2-card" style="padding:10px 14px;">
      <details class="ct2-faq-item" open>
        <summary><?= t('What is the fastest way to get help?') ?></summary>
        <p><?= t('Use live chat for active orders, delivery issues, payment questions and urgent support. Our team can check your request and route it to the right department.') ?></p>
      </details>
      <details class="ct2-faq-item">
        <summary><?= t('Can you help with account or item delivery?') ?></summary>
        <p><?= t('Yes. Contact us with your order details if you need help with account access, item delivery, delivery timing, missing information or any issue after purchase.') ?></p>
      </details>
      <details class="ct2-faq-item">
        <summary><?= t('I want to sell on LoLBoost.gg. Where do I start?') ?></summary>
        <p><?= t('You can apply as a seller from the work with us page. Our team reviews your category, inventory quality, delivery process and marketplace fit before approval.') ?></p>
      </details>
      <details class="ct2-faq-item">
        <summary><?= t('Do you support boosting and coaching orders?') ?></summary>
        <p><?= t('Yes. We can help with boosting order status, coaching scheduling, VOD review questions, provider communication and service-related requests.') ?></p>
      </details>
      <details class="ct2-faq-item">
        <summary><?= t('When can I request a refund?') ?></summary>
        <p><?= t('Refund eligibility depends on the product, order status and marketplace rules. Contact support as early as possible so we can review the case and explain the available options.') ?></p>
      </details>
      <details class="ct2-faq-item">
        <summary><?= t('How do seller payouts and disputes work?') ?></summary>
        <p><?= t('Seller payouts, disputes and buyer protection are handled through our marketplace process. Support can review the order, collect context and guide both sides toward a clean resolution.') ?></p>
      </details>
    </div>
  </section>

  <section class="ct2-section">
    <div class="ct2-card ct2-cta">
      <h2><?= t('Talk to LoLBoost.gg support') ?></h2>
      <p><?= t('Tell us what happened, include your order context if available, and we will help you find the right solution.') ?></p>
      <div class="ct2-cta-actions">
        <a href="#" id="liveChatBtn3" class="ct2-btn ct2-btn-primary"><i class="fa-solid fa-comments"></i><?= t('Open live chat') ?></a>
        <a href="/jobs" class="ct2-btn ct2-btn-ghost"><i class="fa-solid fa-briefcase"></i><?= t('Work with us') ?></a>
      </div>
    </div>
  </section>

</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var holder = document.getElementById('ctStars');
  if (holder && !window.matchMedia('(max-width:820px)').matches) {
    var frag = document.createDocumentFragment();
    for (var i = 0; i < 28; i++) {
      var p = document.createElement('span');
      p.className = 'ct2-star';
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
<script type="text/javascript">
    // --- Tawk.to Live Chat ---
    // Replace YOUR_PROPERTY_ID/YOUR_WIDGET_ID with your Tawk embed IDs from the Tawk dashboard.
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function () {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/YOUR_PROPERTY_ID/YOUR_WIDGET_ID';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();

    // Open the widget when clicking any Live Chat button
    document.addEventListener("DOMContentLoaded", function () {
        var buttons = document.querySelectorAll("#liveChatBtn, #liveChatBtn2, #liveChatBtn3");
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();

                if (window.Tawk_API && typeof window.Tawk_API.maximize === "function") {
                    window.Tawk_API.maximize();
                    return;
                }

                var iv = setInterval(function () {
                    if (window.Tawk_API && typeof window.Tawk_API.maximize === "function") {
                        window.Tawk_API.maximize();
                        clearInterval(iv);
                    }
                }, 200);

                setTimeout(function () { clearInterval(iv); }, 5000);
            });
        });
    });
</script>
<?= $this->end() ?>
