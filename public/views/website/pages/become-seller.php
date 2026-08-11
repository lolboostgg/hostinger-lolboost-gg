<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'become-seller-page']) ?>

<style>
/* BASE */
.become-seller-page { background: #080a12; }

/* HERO */
.bs-hero {
  position: relative; min-height: 100vh;
  display: flex; align-items: center;
  padding: 140px 0 100px; overflow: hidden;
}
.bs-hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 55% at 65% 15%, rgba(99,102,241,.22) 0%, transparent 60%),
    radial-gradient(ellipse 40% 35% at 5% 85%,  rgba(99,102,241,.10) 0%, transparent 50%),
    radial-gradient(ellipse 30% 30% at 95% 75%, rgba(96,165,250,.08) 0%, transparent 50%),
    #080a12;
}
.bs-hero-grid {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
  background-size: 70px 70px;
  -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 20%, transparent 75%);
  mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 20%, transparent 75%);
}
.bs-hero-orb { position: absolute; z-index: 0; border-radius: 50%; pointer-events: none; }
.bs-hero-orb--1 { top: -180px; right: 5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(99,102,241,.18) 0%, transparent 70%); }
.bs-hero-orb--2 { bottom: -120px; left: 0; width: 400px; height: 400px; background: radial-gradient(circle, rgba(99,102,241,.08) 0%, transparent 70%); }
.bs-hero-inner {
  position: relative; z-index: 1;
  max-width: 1240px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;
}
@media(max-width:960px) { .bs-hero-inner { grid-template-columns: 1fr; } .bs-hero-right { display: none !important; } }

.bs-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 7px 16px; border-radius: 99px;
  background: rgba(99,102,241,.14); border: 1px solid rgba(99,102,241,.3);
  font-size: clamp(10px, .75vw, 14px); font-weight: 900; color: #c7d2fe;
  text-transform: uppercase; letter-spacing: .11em; margin-bottom: 28px;
}
.bs-hero-h1 {
  font-size: clamp(32px, 4.5vw, 70px); font-weight: 950;
  line-height: 1.08; color: #fff; margin: 0 0 24px; letter-spacing: -.02em;
}
.bs-hero-h1 .accent {
  background: linear-gradient(135deg, #c7d2fe 0%, #818cf8 42%, #6366f1 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.bs-hero-sub { font-size: clamp(16px, 1.3vw, 22px); color: rgba(255,255,255,.55); line-height: 1.7; max-width: 520px; margin-bottom: 40px; }
.bs-cta-group { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 48px; }
.bs-btn-main {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 16px 32px; border-radius: 14px; font-size: clamp(14px, 1.1vw, 18px); font-weight: 900;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 55%, #38bdf8 100%);
  color: #fff; border: none; cursor: pointer; text-decoration: none;
  box-shadow: 0 6px 30px rgba(99,102,241,.4), 0 1px 0 rgba(255,255,255,.15) inset;
  transition: transform .12s, box-shadow .12s;
}
.bs-btn-main:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(99,102,241,.55); color: #fff; }
.bs-btn-outline {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 16px 24px; border-radius: 14px; font-size: clamp(13px, 1vw, 17px); font-weight: 700;
  background: transparent; border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.65); cursor: pointer; text-decoration: none;
  transition: border-color .12s, color .12s, background .12s;
}
.bs-btn-outline:hover { border-color: rgba(255,255,255,.28); color: #fff; background: rgba(255,255,255,.05); }
.bs-trust { display: flex; gap: 24px; flex-wrap: wrap; padding-top: 36px; border-top: 1px solid rgba(255,255,255,.07); }
.bs-trust-item { display: flex; align-items: center; gap: 8px; font-size: clamp(14px, 1vw, 17px); font-weight: 700; color: rgba(255,255,255,.55); }
.bs-trust-dot { width: 7px; height: 7px; border-radius: 50%; background: #818cf8; box-shadow: 0 0 8px rgba(99,102,241,.6); flex-shrink: 0; }

/* Float cards */
.bs-hero-right { display: flex; flex-direction: column; gap: 14px; }
.bs-float-card {
  background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09);
  border-radius: 20px; padding: 22px 24px; display: flex; align-items: center; gap: 18px;
  backdrop-filter: blur(12px); animation: bsFloat .7s cubic-bezier(.22,1,.36,1) backwards;
}
.bs-float-card:nth-child(1) { animation-delay:.1s; }
.bs-float-card:nth-child(2) { animation-delay:.22s; margin-left:28px; }
.bs-float-card:nth-child(3) { animation-delay:.34s; margin-left:12px; }
@keyframes bsFloat { from { opacity:0; transform:translateY(28px) scale(.97); } to { opacity:1; transform:none; } }
.bs-float-icon { width:52px; height:52px; border-radius:15px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:clamp(16px, 1.4vw, 22px); }
.bs-float-icon--p { background:rgba(99,102,241,.2); border:1px solid rgba(99,102,241,.3); color:#c7d2fe; }
.bs-float-icon--g { background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.25); color:#818cf8; }
.bs-float-icon--a { background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.25); color:#93c5fd; }
.bs-float-val   { font-size:clamp(20px, 2vw, 32px); font-weight:950; color:#fff; line-height:1; letter-spacing:-.02em; }
.bs-float-label { font-size:clamp(13px, .9vw, 16px); color:rgba(255,255,255,.5); margin-top:4px; font-weight:600; }

/* SECTIONS */
.bs-sec { max-width:1240px; margin:0 auto; padding:100px 32px; }
.bs-sec-sep { border:none; border-top:1px solid rgba(255,255,255,.06); margin:0; }
.bs-tag {
  display:inline-flex; align-items:center; gap:7px;
  font-size:clamp(10px, .62vw, 12px); font-weight:900; letter-spacing:.13em;
  text-transform:uppercase; color:rgba(99,102,241,.9); margin-bottom:16px;
}
.bs-tag::before { content:''; width:18px; height:2px; background:currentColor; border-radius:1px; }
.bs-h2 { font-size:clamp(26px, 3.2vw, 52px); font-weight:950; color:#fff; line-height:1.12; margin-bottom:16px; letter-spacing:-.02em; }
.bs-lead { font-size:clamp(15px, 1.2vw, 20px); color:rgba(255,255,255,.5); max-width:560px; line-height:1.7; }

/* STEPS */
.bs-steps { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:60px; }
@media(max-width:860px) { .bs-steps { grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px) { .bs-steps { grid-template-columns:1fr; } }
.bs-step {
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 20px;
  padding: 32px 28px 28px;
  position: relative;
  overflow: hidden;
  transition: background .18s, border-color .18s, transform .18s;
}
.bs-step:hover {
  background: rgba(99,102,241,.07);
  border-color: rgba(99,102,241,.28);
  transform: translateY(-4px);
}
.bs-step-bg-num {
  position: absolute;
  top: -12px; right: 16px;
  font-size: clamp(72px, 7vw, 110px);
  font-weight: 950;
  line-height: 1;
  color: rgba(99,102,241,.08);
  pointer-events: none;
  user-select: none;
  letter-spacing: -.04em;
}
.bs-step-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  margin-bottom: 22px;
  display: flex; align-items: center; justify-content: center;
  font-size: clamp(15px, 1.2vw, 20px);
  background: rgba(99,102,241,.16);
  border: 1px solid rgba(99,102,241,.28);
  color: #c7d2fe;
  position: relative;
}
.bs-step-title { font-size:clamp(16px, 1.2vw, 20px); font-weight:900; color:#fff; margin-bottom:10px; }
.bs-step-text  { font-size:clamp(13px, .95vw, 16px); color:rgba(255,255,255,.5); line-height:1.65; }

/* WHY CARDS */
.bs-why-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:60px; }
@media(max-width:860px) { .bs-why-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:480px) { .bs-why-grid { grid-template-columns:1fr; } }
.bs-why-card {
  background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
  border-radius:20px; padding:28px 24px; position:relative; overflow:hidden;
  transition:border-color .15s, transform .15s, background .15s;
}
.bs-why-card::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:var(--c,rgba(99,102,241,.5)); opacity:0; transition:opacity .15s; }
.bs-why-card:hover { transform:translateY(-4px); border-color:rgba(255,255,255,.12); }
.bs-why-card:hover::before { opacity:1; }
.bs-why-icon { width:50px; height:50px; border-radius:14px; margin-bottom:18px; display:flex; align-items:center; justify-content:center; font-size:clamp(15px, 1.2vw, 20px); }
.bs-why-title { font-size:clamp(16px, 1.2vw, 20px); font-weight:900; color:#fff; margin-bottom:9px; }
.bs-why-text  { font-size:clamp(13px, .95vw, 16px); color:rgba(255,255,255,.5); line-height:1.65; }

/* RANKS */
.bs-ranks { display:flex; gap:12px; margin-top:60px; flex-wrap:wrap; align-items:stretch; }
.bs-rank {
  flex:1; min-width:150px; border:1px solid rgba(255,255,255,.07);
  border-radius:20px; padding:32px 20px; text-align:center;
  position:relative; overflow:hidden; background:rgba(255,255,255,.025);
  transition:transform .15s, border-color .15s;
}
.bs-rank:hover { transform:translateY(-5px); }
.bs-rank--legend { background:rgba(99,102,241,.05); border-color:rgba(99,102,241,.25); box-shadow:0 0 40px rgba(99,102,241,.06); }
.bs-rank-emoji { font-size:clamp(28px, 2.6vw, 42px); margin-bottom:14px; display:block; }
.bs-rank-name  { font-size:clamp(13px, 1vw, 17px); font-weight:950; margin-bottom:5px; }
.bs-rank-req   { font-size:clamp(10px, .65vw, 12px); color:rgba(255,255,255,.38); font-weight:600; }
.bs-rank-glow  { position:absolute; bottom:-30px; left:50%; transform:translateX(-50%); width:80px; height:80px; border-radius:50%; background:radial-gradient(circle,var(--c,rgba(99,102,241,.3)) 0%,transparent 70%); pointer-events:none; }

/* FAQ */
.bs-faq { max-width:100%; margin:48px 0 0; }
.bs-faq-item { border-bottom:1px solid rgba(255,255,255,.07); }
.bs-faq-q { display:flex; align-items:center; justify-content:space-between; padding:22px 4px; cursor:pointer; font-size:clamp(15px, 1.15vw, 20px); font-weight:800; color:rgba(255,255,255,.85); transition:color .12s; user-select:none; gap:16px; }
.bs-faq-q:hover { color:#fff; }
.bs-faq-toggle { width:28px; height:28px; border-radius:50%; flex-shrink:0; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.4); font-size:clamp(10px, .65vw, 12px); transition:background .12s,color .12s,transform .2s; }
.bs-faq-item.open .bs-faq-toggle { background:rgba(99,102,241,.2); color:#c7d2fe; border-color:rgba(99,102,241,.3); transform:rotate(45deg); }
.bs-faq-a { display:none; padding:0 4px 22px; font-size:clamp(14px, 1vw, 17px); color:rgba(255,255,255,.55); line-height:1.75; }
.bs-faq-item.open .bs-faq-a { display:block; }

/* CTA BANNER */
.bs-cta-wrap { padding:0 32px 100px; max-width:1240px; margin:0 auto; }
.bs-cta {
  position:relative; overflow:hidden; border-radius:28px; padding:80px 48px;
  background:linear-gradient(135deg,rgba(99,102,241,.20) 0%,rgba(79,70,229,.14) 52%,rgba(56,189,248,.08) 100%);
  border:1px solid rgba(99,102,241,.25); text-align:center;
}
.bs-cta::before { content:''; position:absolute; top:-150px; left:50%; transform:translateX(-50%); width:500px; height:500px; border-radius:50%; background:radial-gradient(circle,rgba(99,102,241,.18) 0%,transparent 65%); pointer-events:none; }
.bs-cta::after { content:''; position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:48px 48px; -webkit-mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 80%); mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 80%); }
.bs-cta-inner { position:relative; z-index:1; }
.bs-cta-tag { display:inline-flex; align-items:center; gap:7px; font-size:clamp(10px, .62vw, 12px); font-weight:900; letter-spacing:.13em; text-transform:uppercase; color:#c7d2fe; margin-bottom:18px; }
.bs-cta-h2  { font-size:clamp(24px, 2.8vw, 45px); font-weight:950; color:#fff; margin-bottom:12px; letter-spacing:-.02em; }
.bs-cta-sub { font-size:clamp(14px, 1.1vw, 18px); color:rgba(255,255,255,.45); margin-bottom:36px; }
.bs-cta-btns { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; }

/* ANIMATIONS */
@keyframes bsFadeUp { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:none; } }
.bs-animate { animation:bsFadeUp .55s cubic-bezier(.22,1,.36,1) backwards; }
.bs-animate--2 { animation-delay:.1s; }
.bs-animate--3 { animation-delay:.18s; }

/* LoLBoost blue polish */
.become-seller-page {
  --lb-blue:#6366f1;
  --lb-blue2:#818cf8;
  --lb-blue3:#38bdf8;
}
.bs-hero-bg {
  background:
    radial-gradient(ellipse 70% 55% at 65% 15%, rgba(99,102,241,.26) 0%, transparent 60%),
    radial-gradient(ellipse 42% 36% at 8% 82%, rgba(59,130,246,.14) 0%, transparent 54%),
    radial-gradient(ellipse 32% 30% at 95% 74%, rgba(56,189,248,.10) 0%, transparent 52%),
    #080a12 !important;
}
.bs-hero-orb--1 { background: radial-gradient(circle, rgba(99,102,241,.22) 0%, transparent 70%) !important; }
.bs-hero-orb--2 { background: radial-gradient(circle, rgba(56,189,248,.10) 0%, transparent 70%) !important; }
.bs-eyebrow,
.bs-tag,
.bs-cta-tag { color:#c7d2fe !important; }
.bs-btn-main {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 58%, #38bdf8 100%) !important;
  box-shadow: 0 10px 34px rgba(99,102,241,.34), inset 0 1px 0 rgba(255,255,255,.15) !important;
}
.bs-btn-main:hover { box-shadow: 0 16px 44px rgba(99,102,241,.48) !important; }
.bs-float-icon,
.bs-step-icon,
.bs-why-icon {
  background: rgba(99,102,241,.14) !important;
  border-color: rgba(129,140,248,.26) !important;
  color:#c7d2fe !important;
}
.bs-trust-dot {
  background:#6366f1 !important;
  box-shadow:0 0 12px rgba(99,102,241,.75) !important;
}
.bs-step:hover {
  background: rgba(99,102,241,.075) !important;
  border-color: rgba(129,140,248,.30) !important;
}
.bs-step-bg-num { color: rgba(99,102,241,.10) !important; }
.bs-why-card::before { background: linear-gradient(90deg, rgba(99,102,241,.75), rgba(56,189,248,.45)) !important; }
.bs-rank-icon {
  width:58px;
  height:58px;
  border-radius:18px;
  margin:0 auto 14px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-size:24px;
  background:linear-gradient(180deg, rgba(99,102,241,.20), rgba(99,102,241,.08));
  border:1px solid rgba(129,140,248,.28);
  color:#c7d2fe;
  box-shadow:0 18px 44px rgba(99,102,241,.14), inset 0 1px 0 rgba(255,255,255,.08);
}
.bs-rank-icon--beginner { color:#cbd5e1; background:rgba(148,163,184,.10); border-color:rgba(148,163,184,.20); }
.bs-rank-icon--trusted { color:#93c5fd; background:rgba(59,130,246,.13); border-color:rgba(96,165,250,.26); }
.bs-rank-icon--pro { color:#c7d2fe; background:rgba(99,102,241,.18); border-color:rgba(129,140,248,.30); }
.bs-rank-icon--elite { color:#a5b4fc; background:rgba(129,140,248,.16); border-color:rgba(165,180,252,.28); }
.bs-rank-icon--legend { color:#bfdbfe; background:linear-gradient(180deg, rgba(56,189,248,.18), rgba(99,102,241,.12)); border-color:rgba(147,197,253,.32); }
.bs-rank--legend {
  background:rgba(99,102,241,.055) !important;
  border-color:rgba(129,140,248,.26) !important;
  box-shadow:0 0 42px rgba(99,102,241,.08) !important;
}
.bs-faq-item.open .bs-faq-toggle {
  background:rgba(99,102,241,.20) !important;
  color:#c7d2fe !important;
  border-color:rgba(129,140,248,.30) !important;
}
.bs-cta {
  background:linear-gradient(135deg,rgba(99,102,241,.20) 0%,rgba(79,70,229,.14) 52%,rgba(56,189,248,.08) 100%) !important;
  border-color:rgba(129,140,248,.28) !important;
}
.bs-cta::before { background:radial-gradient(circle,rgba(99,102,241,.22) 0%,transparent 65%) !important; }

</style>

<!-- HERO -->
<section class="bs-hero">
  <div class="bs-hero-bg"></div>
  <div class="bs-hero-grid"></div>
  <div class="bs-hero-orb bs-hero-orb--1"></div>
  <div class="bs-hero-orb bs-hero-orb--2"></div>
  <div class="bs-hero-inner">
    <div>
      <div class="bs-eyebrow bs-animate"><i class="fa-solid fa-store"></i> Seller Program</div>
      <h1 class="bs-hero-h1 bs-animate bs-animate--2">Turn your accounts<br>into <span class="accent">real income</span></h1>
      <p class="bs-hero-sub bs-animate bs-animate--3">List your LoL accounts on LoLBoost.gg's marketplace. Reach thousands of verified buyers, get paid instantly, and grow your seller rank.</p>
      <div class="bs-cta-group bs-animate" style="animation-delay:.24s;">
        <a href="/jobs/apply" class="bs-btn-main"><i class="fa-solid fa-rocket"></i> Apply to sell</a>
        <a href="#how-it-works" class="bs-btn-outline"><i class="fa-solid fa-play fa-xs"></i> How it works</a>
      </div>
      <div class="bs-trust bs-animate" style="animation-delay:.3s;">
        <div class="bs-trust-item"><div class="bs-trust-dot"></div> Instant delivery system</div>
        <div class="bs-trust-item"><div class="bs-trust-dot"></div> Keep up to 90% per sale</div>
        <div class="bs-trust-item"><div class="bs-trust-dot"></div> Verified buyer base</div>
      </div>
    </div>
    <div class="bs-hero-right">
      <div class="bs-float-card">
        <div class="bs-float-icon bs-float-icon--p"><i class="fa-solid fa-sack-dollar"></i></div>
        <div><div class="bs-float-val">90%</div><div class="bs-float-label">Max earnings per sale</div></div>
      </div>
      <div class="bs-float-card">
        <div class="bs-float-icon bs-float-icon--g"><i class="fa-solid fa-bolt"></i></div>
        <div><div class="bs-float-val">Instant</div><div class="bs-float-label">Automatic delivery to buyers</div></div>
      </div>
      <div class="bs-float-card">
        <div class="bs-float-icon bs-float-icon--a"><i class="fa-solid fa-shield-check"></i></div>
        <div><div class="bs-float-val">Verified</div><div class="bs-float-label">All buyers are checked &amp; trusted</div></div>
      </div>
    </div>
  </div>
</section>

<hr class="bs-sec-sep">

<!-- HOW IT WORKS -->
<section class="bs-sec" id="how-it-works">
  <div>
    <div class="bs-tag">Process</div>
    <h2 class="bs-h2" style="margin:0 0 14px;">From application to first sale</h2>
    <p class="bs-lead">Most sellers make their first sale within 24 hours of approval.</p>
  </div>
  <div class="bs-steps">
    <div class="bs-step"><div class="bs-step-bg-num">1</div><div class="bs-step-icon"><i class="fa-solid fa-file-pen"></i></div><div class="bs-step-title">Apply</div><div class="bs-step-text">Fill out our quick application form. Tell us about yourself and the accounts you want to sell. Takes 2 minutes.</div></div>
    <div class="bs-step"><div class="bs-step-bg-num">2</div><div class="bs-step-icon"><i class="fa-solid fa-badge-check"></i></div><div class="bs-step-title">Get approved</div><div class="bs-step-text">Our team reviews your application within 24-48 hours. Once approved you get instant access to your seller dashboard.</div></div>
    <div class="bs-step"><div class="bs-step-bg-num">3</div><div class="bs-step-icon"><i class="fa-solid fa-store"></i></div><div class="bs-step-title">List accounts</div><div class="bs-step-text">Add your accounts with rank, server and credentials. Instant Delivery handles the rest - fully automatic.</div></div>
    <div class="bs-step"><div class="bs-step-bg-num">4</div><div class="bs-step-icon"><i class="fa-solid fa-money-bill-wave"></i></div><div class="bs-step-title">Get paid</div><div class="bs-step-text">Balance updates the moment a sale completes. Request a payout to your bank or crypto wallet whenever you want.</div></div>
  </div>
</section>

<hr class="bs-sec-sep">

<!-- WHY LOLBOOST -->
<section class="bs-sec">
  <div>
    <div class="bs-tag">Benefits</div>
    <h2 class="bs-h2" style="margin:0 0 14px;">Why sell on LoLBoost.gg?</h2>
    <p class="bs-lead">Built specifically for account sellers - not a bolt-on feature.</p>
  </div>
  <div class="bs-why-grid">
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-solid fa-bolt"></i></div><div class="bs-why-title">Instant Delivery</div><div class="bs-why-text">Credentials are sent to the buyer automatically the moment they pay. Zero manual work after listing.</div></div>
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-solid fa-sack-dollar"></i></div><div class="bs-why-title">Keep up to 90%</div><div class="bs-why-text">Platform fee starts at just 10%. The more you sell and the higher your rank, the better your conditions.</div></div>
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-solid fa-users"></i></div><div class="bs-why-title">Real buyer traffic</div><div class="bs-why-text">LoLBoost.gg serves thousands of customers daily. Your listings get seen by verified, paying buyers - not bots.</div></div>
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-solid fa-chart-line-up"></i></div><div class="bs-why-title">Seller dashboard</div><div class="bs-why-text">Track every sale, manage listings, chat with buyers, and request payouts from one clean dashboard.</div></div>
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-solid fa-shield-halved"></i></div><div class="bs-why-title">Buyer protection</div><div class="bs-why-text">We verify buyers and handle disputes. You focus on selling - we handle the trust layer so you don't have to.</div></div>
    <div class="bs-why-card" style="--c:rgba(99,102,241,.70);"><div class="bs-why-icon" style="background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.24);color:#c7d2fe;"><i class="fa-brands fa-discord"></i></div><div class="bs-why-title">Seller community</div><div class="bs-why-text">Private seller Discord with direct team access. Get notified via DM when your accounts sell.</div></div>
  </div>
</section>

<hr class="bs-sec-sep">

<!-- RANKS -->
<section class="bs-sec">
  <div>
    <div class="bs-tag">Ranks</div>
    <h2 class="bs-h2" style="margin:0 0 14px;">Earn your rank, unlock rewards</h2>
    <p class="bs-lead">Sell more to climb ranks and unlock better visibility and perks.</p>
  </div>
  <div class="bs-ranks">
    <div class="bs-rank"><div class="bs-rank-glow" style="--c:rgba(148,163,184,.25);"></div><span class="bs-rank-icon bs-rank-icon--beginner"><i class="fa-solid fa-seedling"></i></span><div class="bs-rank-name" style="color:#cbd5e1;">Beginner</div><div class="bs-rank-req">Starting rank</div></div>
    <div class="bs-rank"><div class="bs-rank-glow" style="--c:rgba(96,165,250,.30);"></div><span class="bs-rank-icon bs-rank-icon--trusted"><i class="fa-solid fa-shield-halved"></i></span><div class="bs-rank-name" style="color:#93c5fd;">Trusted</div><div class="bs-rank-req">&euro;1,000 earned</div></div>
    <div class="bs-rank"><div class="bs-rank-glow" style="--c:rgba(99,102,241,.32);"></div><span class="bs-rank-icon bs-rank-icon--pro"><i class="fa-solid fa-bolt"></i></span><div class="bs-rank-name" style="color:#818cf8;">Pro</div><div class="bs-rank-req">&euro;5,000 earned</div></div>
    <div class="bs-rank"><div class="bs-rank-glow" style="--c:rgba(129,140,248,.35);"></div><span class="bs-rank-icon bs-rank-icon--elite"><i class="fa-solid fa-gem"></i></span><div class="bs-rank-name" style="color:#a5b4fc;">Elite</div><div class="bs-rank-req">&euro;15,000 earned</div></div>
    <div class="bs-rank bs-rank--legend"><div class="bs-rank-glow" style="--c:rgba(56,189,248,.32);"></div><span class="bs-rank-icon bs-rank-icon--legend"><i class="fa-solid fa-crown"></i></span><div class="bs-rank-name" style="color:#bfdbfe;">Legend</div><div class="bs-rank-req">&euro;50,000 earned</div></div>
  </div>
</section>

<hr class="bs-sec-sep">

<!-- FAQ -->
<section class="bs-sec">
  <div>
    <div class="bs-tag">FAQ</div>
    <h2 class="bs-h2" style="margin:0 0 14px;">Frequently asked questions</h2>
  </div>
  <div class="bs-faq">
    <?php
    $faqs = [
      ['q'=>'How do I apply to become a seller?','a'=>'Click "Apply to sell" on this page. You\'ll be taken to our application form. Fill in your details and our team reviews your application within 24-48 hours.'],
      ['q'=>'What accounts can I sell?','a'=>'Currently we support League of Legends accounts on all servers. Accounts must not be permanently banned and all credentials must be correct and transferable to the buyer.'],
      ['q'=>'How does the platform fee work?','a'=>'Our platform fee is as low as 1% per sale. The exact fee depends on your seller agreement. It\'s deducted automatically - you always see what you\'ll earn before listing.'],
      ['q'=>'When and how do I get paid?','a'=>'Your balance updates the instant an account sells. You can request a payout anytime from your seller dashboard - to your bank account or a crypto wallet.'],
      ['q'=>'What happens if a buyer has a problem?','a'=>'You\'ll be notified via Discord DM and the in-platform chat. Sellers who respond promptly keep their good standing. Fines only apply for clear violations - see our Seller Rules page for details.'],
      ['q'=>'Is there a minimum number of accounts I need to list?','a'=>'No minimum at all. Start with one account and scale from there. There is no upper limit either.'],
    ];
    foreach ($faqs as $faq): ?>
    <div class="bs-faq-item">
      <div class="bs-faq-q"><?= htmlspecialchars($faq['q']) ?><span class="bs-faq-toggle"><i class="fa-solid fa-plus"></i></span></div>
      <div class="bs-faq-a"><?= htmlspecialchars($faq['a']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<div class="bs-cta-wrap">
  <div class="bs-cta">
    <div class="bs-cta-inner">
      <div class="bs-cta-tag"><i class="fa-solid fa-rocket"></i> Get started today</div>
      <h2 class="bs-cta-h2">Ready to start selling?</h2>
      <p class="bs-cta-sub">Apply in minutes. Our team reviews applications fast.</p>
      <div class="bs-cta-btns">
        <a href="/jobs/apply" class="bs-btn-main" style="font-size:clamp(14px, 1.15vw, 19px);padding:17px 38px;"><i class="fa-solid fa-rocket"></i> Apply to sell now</a>
        <a href="<?= BASE_URL ?>/seller-area/auth/login" class="bs-btn-outline" style="font-size:clamp(14px, 1.1vw, 18px);padding:17px 26px;">Already a seller? Log in</a>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.bs-faq-q').forEach(function(btn){
  btn.addEventListener('click',function(){
    var item=this.closest('.bs-faq-item'),wasOpen=item.classList.contains('open');
    document.querySelectorAll('.bs-faq-item.open').forEach(function(el){el.classList.remove('open');});
    if(!wasOpen)item.classList.add('open');
  });
});
document.querySelectorAll('a[href^="#"]').forEach(function(a){
  a.addEventListener('click',function(e){
    var t=document.querySelector(this.getAttribute('href'));
    if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});}
  });
});
</script>
