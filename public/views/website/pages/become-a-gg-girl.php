<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'become-gg-page']) ?>

<style>
/* BASE */
.become-gg-page { background: #050712; }

/* HERO */
.gg-hero {
  position: relative; min-height: 100vh;
  display: flex; align-items: center;
  padding: 140px 0 100px; overflow: hidden;
}
.gg-hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 55% at 65% 15%, rgba(99,102,241,.24) 0%, transparent 60%),
    radial-gradient(ellipse 40% 35% at 5% 85%,  rgba(59,130,246,.14) 0%, transparent 50%),
    radial-gradient(ellipse 30% 30% at 95% 75%, rgba(56,189,248,.10) 0%, transparent 50%),
    #050712;
}
.gg-hero-grid {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(129,140,248,.045) 1px, transparent 1px),
    linear-gradient(90deg, rgba(129,140,248,.045) 1px, transparent 1px);
  background-size: 70px 70px;
  -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 20%, transparent 75%);
  mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 20%, transparent 75%);
}
.gg-hero-orb { position: absolute; z-index: 0; border-radius: 50%; pointer-events: none; }
.gg-hero-orb--1 { top: -180px; right: 5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(99,102,241,.22) 0%, transparent 70%); }
.gg-hero-orb--2 { bottom: -120px; left: 0; width: 400px; height: 400px; background: radial-gradient(circle, rgba(56,189,248,.11) 0%, transparent 70%); }
.gg-hero-inner {
  position: relative; z-index: 1;
  max-width: 1240px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;
}
@media(max-width:960px) { .gg-hero-inner { grid-template-columns: 1fr; } .gg-hero-right { display: none !important; } }

.gg-eyebrow {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 7px 16px; border-radius: 99px;
  background: rgba(99,102,241,.16); border: 1px solid rgba(129,140,248,.35);
  font-size: clamp(10px, .75vw, 14px); font-weight: 900; color: #c7d2fe;
  text-transform: uppercase; letter-spacing: .11em; margin-bottom: 28px;
}
.gg-eyebrow img { width: 20px; height: 20px; object-fit: contain; }
.gg-hero-h1 {
  font-size: clamp(32px, 4.5vw, 68px); font-weight: 950;
  line-height: 1.08; color: #fff; margin: 0 0 24px; letter-spacing: -.02em;
}
.gg-hero-h1 .accent {
  background: linear-gradient(135deg, #c7d2fe 0%, #6366f1 48%, #38bdf8 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.gg-hero-sub { font-size: clamp(16px, 1.3vw, 22px); color: rgba(229,231,255,.66); line-height: 1.7; max-width: 520px; margin-bottom: 40px; }
.gg-cta-group { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 48px; }
.gg-btn-main {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 16px 32px; border-radius: 14px; font-size: clamp(14px, 1.1vw, 18px); font-weight: 900;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 55%, #38bdf8 100%);
  color: #fff; border: none; cursor: pointer; text-decoration: none;
  box-shadow: 0 6px 30px rgba(99,102,241,.42), 0 1px 0 rgba(255,255,255,.15) inset;
  transition: transform .12s, box-shadow .12s;
}
.gg-btn-main:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(99,102,241,.58); color: #fff; }
.gg-btn-outline {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 16px 24px; border-radius: 14px; font-size: clamp(13px, 1vw, 17px); font-weight: 700;
  background: transparent; border: 1px solid rgba(129,140,248,.25);
  color: rgba(229,231,255,.66); cursor: pointer; text-decoration: none;
  transition: border-color .12s, color .12s, background .12s;
}
.gg-btn-outline:hover { border-color: rgba(129,140,248,.55); color: #fff; background: rgba(99,102,241,.10); }
.gg-trust { display: flex; gap: 24px; flex-wrap: wrap; padding-top: 36px; border-top: 1px solid rgba(129,140,248,.14); }
.gg-trust-item { display: flex; align-items: center; gap: 8px; font-size: clamp(14px, 1vw, 17px); font-weight: 700; color: rgba(229,231,255,.62); }
.gg-trust-dot { width: 7px; height: 7px; border-radius: 50%; background: #6366f1; box-shadow: 0 0 8px rgba(99,102,241,.58); flex-shrink: 0; }

/* Float cards right side */
.gg-hero-right { display: flex; flex-direction: column; gap: 14px; }
.gg-float-card {
  background: rgba(99,102,241,.06); border: 1px solid rgba(129,140,248,.14);
  border-radius: 20px; padding: 22px 24px; display: flex; align-items: center; gap: 18px;
  backdrop-filter: blur(12px); animation: ggFloat .7s cubic-bezier(.22,1,.36,1) backwards;
}
.gg-float-card:nth-child(1) { animation-delay:.1s; }
.gg-float-card:nth-child(2) { animation-delay:.22s; margin-left:28px; }
.gg-float-card:nth-child(3) { animation-delay:.34s; margin-left:12px; }
@keyframes ggFloat { from { opacity:0; transform:translateY(28px) scale(.97); } to { opacity:1; transform:none; } }
.gg-float-icon { width:52px; height:52px; border-radius:15px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:clamp(16px, 1.4vw, 22px); }
.gg-float-icon--p { background:rgba(99,102,241,.20); border:1px solid rgba(129,140,248,.30); }
.gg-float-icon--p img { width: 30px; height: 30px; object-fit: contain; filter: drop-shadow(0 0 8px rgba(99,102,241,.58)); }
.gg-float-icon--g { background:rgba(74,222,128,.15); border:1px solid rgba(74,222,128,.25); color:#4ade80; }
.gg-float-icon--a { background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.25); color:#fbbf24; }
.gg-float-val   { font-size:clamp(20px, 2vw, 32px); font-weight:950; color:#fff; line-height:1; letter-spacing:-.02em; }
.gg-float-label { font-size:clamp(13px, .9vw, 16px); color:rgba(229,231,255,.56); margin-top:4px; font-weight:600; }

/* SECTIONS */
.gg-sec { max-width:1240px; margin:0 auto; padding:100px 32px; }
.gg-sec-sep { border:none; border-top:1px solid rgba(129,140,248,.10); margin:0; }
.gg-tag {
  display:inline-flex; align-items:center; gap:7px;
  font-size:clamp(10px, .62vw, 12px); font-weight:900; letter-spacing:.13em;
  text-transform:uppercase; color:rgba(129,140,248,.92); margin-bottom:16px;
}
.gg-tag::before { content:''; width:18px; height:2px; background:currentColor; border-radius:1px; }
.gg-h2 { font-size:clamp(26px, 3.2vw, 52px); font-weight:950; color:#fff; line-height:1.12; margin-bottom:16px; letter-spacing:-.02em; }
.gg-lead { font-size:clamp(15px, 1.2vw, 20px); color:rgba(229,231,255,.56); max-width:560px; line-height:1.7; }

/* STEPS */
.gg-steps { display:grid; grid-template-columns:repeat(4,1fr); gap:32px; margin-top:64px; }
@media(max-width:800px){ .gg-steps{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px){ .gg-steps{ grid-template-columns:1fr; } }
.gg-step { position:relative; }
.gg-step-bg-num { position:absolute; top:-10px; left:-6px; font-size:clamp(80px,8vw,120px); font-weight:950; color:rgba(99,102,241,.08); line-height:1; user-select:none; pointer-events:none; }
.gg-step-icon { width:52px; height:52px; border-radius:16px; background:rgba(99,102,241,.16); border:1px solid rgba(129,140,248,.25); display:flex; align-items:center; justify-content:center; font-size:clamp(18px,1.6vw,24px); color:#c7d2fe; margin-bottom:20px; position:relative; }
.gg-step-title { font-size:clamp(17px,1.4vw,22px); font-weight:800; color:#fff; margin-bottom:10px; }
.gg-step-text { font-size:clamp(14px,1vw,16px); color:rgba(229,231,255,.56); line-height:1.65; }

/* WHY GRID */
.gg-why-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:64px; }
@media(max-width:900px){ .gg-why-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .gg-why-grid{ grid-template-columns:1fr; } }
.gg-why-card {
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
  border-radius:20px; padding:28px; position:relative; overflow:hidden;
  transition:border-color .18s, background .18s;
}
.gg-why-card::before { content:''; position:absolute; inset:0; border-radius:20px; box-shadow:0 0 0 1px rgba(var(--c),.0) inset; transition:box-shadow .18s; }
.gg-why-card:hover { background:rgba(255,255,255,.05); border-color:rgba(129,140,248,.24); }
.gg-why-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:18px; }
.gg-why-title { font-size:clamp(16px,1.2vw,20px); font-weight:800; color:#fff; margin-bottom:10px; }
.gg-why-text { font-size:clamp(14px,1vw,16px); color:rgba(229,231,255,.56); line-height:1.65; }

/* REQUIREMENTS */
.gg-req-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:24px; margin-top:48px; }
@media(max-width:680px){ .gg-req-grid{ grid-template-columns:1fr; } }
.gg-req-card {
  background:rgba(99,102,241,.06); border:1px solid rgba(129,140,248,.14);
  border-radius:20px; padding:32px;
}
.gg-req-title { font-size:clamp(16px,1.2vw,20px); font-weight:800; color:#fff; margin-bottom:8px; }
.gg-req-sub { font-size:13px; color:rgba(229,231,255,.46); margin-bottom:20px; }
.gg-req-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; }
.gg-req-list li { display:flex; align-items:center; gap:10px; font-size:clamp(14px,1vw,17px); color:rgba(229,231,255,.80); }
.gg-req-list li::before { content:'✓'; width:20px; height:20px; border-radius:50%; background:rgba(99,102,241,.20); border:1px solid rgba(129,140,248,.40); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:900; color:#c7d2fe; flex-shrink:0; }

/* FAQ */
.gg-faq { display:flex; flex-direction:column; gap:0; margin-top:48px; border:1px solid rgba(129,140,248,.12); border-radius:20px; overflow:hidden; }
.gg-faq-item { border-bottom:1px solid rgba(129,140,248,.10); }
.gg-faq-item:last-child { border-bottom:none; }
.gg-faq-q { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:22px 28px; cursor:pointer; font-size:clamp(15px,1.15vw,20px); font-weight:700; color:rgba(255,255,255,.85); transition:color .12s, background .12s; }
.gg-faq-q:hover { background:rgba(99,102,241,.08); color:#fff; }
.gg-faq-toggle { width:28px; height:28px; border-radius:50%; background:rgba(129,140,248,.12); border:1px solid rgba(129,140,248,.24); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; color:#c7d2fe; transition:transform .2s, background .12s; }
.gg-faq-item.open .gg-faq-toggle { transform:rotate(45deg); background:rgba(99,102,241,.22); }
.gg-faq-a { display:none; padding:0 28px 22px; font-size:clamp(14px,1vw,17px); color:rgba(229,231,255,.66); line-height:1.75; }
.gg-faq-item.open .gg-faq-a { display:block; }

/* CTA */
.gg-cta-wrap { padding: 0 32px 120px; }
.gg-cta {
  max-width:900px; margin:0 auto;
  background: linear-gradient(135deg, rgba(220,40,200,.18) 0%, rgba(59,130,246,.14) 100%);
  border:1px solid rgba(129,140,248,.25); border-radius:28px;
  padding:64px 48px; text-align:center; position:relative; overflow:hidden;
}
.gg-cta::before { content:''; position:absolute; top:-80px; right:-80px; width:300px; height:300px; border-radius:50%; background:radial-gradient(circle, rgba(99,102,241,.20) 0%, transparent 70%); pointer-events:none; }
.gg-cta-tag { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; color:rgba(229,231,255,.66); margin-bottom:20px; }
.gg-cta-h2 { font-size:clamp(28px,3.5vw,56px); font-weight:950; color:#fff; margin-bottom:16px; letter-spacing:-.02em; }
.gg-cta-sub { font-size:clamp(14px,1.1vw,18px); color:rgba(255,200,255,.5); margin-bottom:36px; }
.gg-cta-btns { display:flex; align-items:center; justify-content:center; gap:14px; flex-wrap:wrap; }


/* LOLBOOST BLUE REFRESH */
.become-gg-page{
  --gg-blue:#6366f1;
  --gg-blue2:#4f46e5;
  --gg-cyan:#38bdf8;
  --gg-panel:rgba(17,20,38,.64);
  --gg-stroke:rgba(129,140,248,.18);
}
.gg-hero-bg{
  background:
    radial-gradient(900px 560px at 70% 6%, rgba(99,102,241,.26), transparent 62%),
    radial-gradient(760px 460px at 12% 86%, rgba(56,189,248,.12), transparent 60%),
    radial-gradient(620px 420px at 88% 80%, rgba(79,70,229,.16), transparent 60%),
    linear-gradient(180deg,#050712 0%,#080a18 100%);
}
.gg-eyebrow i,
.gg-cta-tag i{color:#a5b4fc;filter:drop-shadow(0 0 10px rgba(99,102,241,.52));}
.gg-btn-main{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 58%,#38bdf8 100%);box-shadow:0 12px 38px rgba(99,102,241,.38), inset 0 1px 0 rgba(255,255,255,.16);}
.gg-btn-main:hover{box-shadow:0 18px 50px rgba(99,102,241,.50);}
.gg-btn-outline{border-color:rgba(129,140,248,.22);color:rgba(229,231,255,.72);}
.gg-btn-outline:hover{border-color:rgba(129,140,248,.48);background:rgba(99,102,241,.10);}
.gg-trust-dot{background:#818cf8;box-shadow:0 0 0 5px rgba(99,102,241,.14),0 0 18px rgba(99,102,241,.56);}
.gg-float-card,.gg-req-card,.gg-why-card,.gg-cta{background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025));border-color:rgba(129,140,248,.16);box-shadow:0 18px 70px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.045);}
.gg-float-icon,.gg-step-icon,.gg-why-icon{background:rgba(99,102,241,.13)!important;border-color:rgba(129,140,248,.24)!important;color:#c7d2fe!important;}
.gg-step{padding:30px 26px;border-radius:22px;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02));border:1px solid rgba(129,140,248,.13);transition:transform .18s ease,border-color .18s ease,background .18s ease;}
.gg-step:hover{transform:translateY(-4px);border-color:rgba(129,140,248,.34);background:rgba(99,102,241,.075);}
.gg-step-bg-num{color:rgba(99,102,241,.10);}
.gg-req-title{display:flex;align-items:center;gap:10px;}
.gg-req-title i{color:#a5b4fc;}
.gg-req-list li::before{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;background:rgba(99,102,241,.18);border-color:rgba(129,140,248,.34);color:#c7d2fe;}
.gg-faq{border-color:rgba(129,140,248,.14);}
.gg-faq-item{border-bottom-color:rgba(129,140,248,.10);}
.gg-faq-q:hover{background:rgba(99,102,241,.08);}
.gg-faq-toggle{background:rgba(99,102,241,.14);border-color:rgba(129,140,248,.22);color:#c7d2fe;}
.gg-faq-item.open .gg-faq-toggle{background:rgba(99,102,241,.24);}
.gg-hero-h1 .accent{background:linear-gradient(135deg,#e0e7ff 0%,#6366f1 48%,#38bdf8 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}



/* GG GIRL POLISH, blue base with pink accents */
.become-gg-page{
  --gg-pink:#f472d0;
  --gg-pink2:#d946ef;
  --gg-blue:#6366f1;
  --gg-blue2:#4f46e5;
}
.gg-hero-bg{
  background:
    radial-gradient(900px 560px at 70% 6%, rgba(99,102,241,.24), transparent 62%),
    radial-gradient(760px 460px at 14% 84%, rgba(217,70,239,.15), transparent 60%),
    radial-gradient(620px 420px at 88% 80%, rgba(244,114,208,.12), transparent 60%),
    linear-gradient(180deg,#050712 0%,#080713 100%);
}
.gg-hero-grid{
  background-image:
    linear-gradient(rgba(129,140,248,.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(244,114,208,.035) 1px, transparent 1px);
}
.gg-eyebrow{
  background:linear-gradient(135deg,rgba(99,102,241,.14),rgba(217,70,239,.12));
  border-color:rgba(244,114,208,.26);
  color:#fbcfe8;
}
.gg-eyebrow i,.gg-cta-tag i{
  color:#f9a8d4;
  filter:drop-shadow(0 0 12px rgba(244,114,208,.48));
}
.gg-hero-h1 .accent{
  background:linear-gradient(135deg,#fbcfe8 0%,#f472d0 34%,#6366f1 72%,#93c5fd 100%);
  -webkit-background-clip:text;
  background-clip:text;
  -webkit-text-fill-color:transparent;
}
.gg-btn-main{
  background:linear-gradient(135deg,#6366f1 0%,#7c3aed 54%,#d946ef 100%);
  box-shadow:0 14px 44px rgba(99,102,241,.34),0 0 34px rgba(217,70,239,.14),inset 0 1px 0 rgba(255,255,255,.16);
}
.gg-btn-main:hover{
  box-shadow:0 18px 54px rgba(99,102,241,.42),0 0 42px rgba(217,70,239,.20);
}
.gg-trust-dot{
  background:#f472d0;
  box-shadow:0 0 0 5px rgba(244,114,208,.12),0 0 18px rgba(244,114,208,.48);
}
.gg-tag{color:rgba(244,114,208,.92);}
.gg-tag::before{background:linear-gradient(90deg,#6366f1,#f472d0);}
.gg-float-card,.gg-req-card,.gg-why-card,.gg-cta{
  border-color:rgba(244,114,208,.14);
  background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.024));
}
.gg-float-card:hover,.gg-why-card:hover,.gg-req-card:hover{
  border-color:rgba(244,114,208,.28);
}
.gg-float-icon,.gg-why-icon{
  background:linear-gradient(135deg,rgba(99,102,241,.16),rgba(217,70,239,.12))!important;
  border-color:rgba(244,114,208,.24)!important;
  color:#fbcfe8!important;
}

/* Process cards, cleaner numbering */
.gg-steps{gap:22px;}
.gg-step{
  min-height:255px;
  padding:34px 28px 30px;
  border-radius:24px;
  overflow:hidden;
  background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.020));
  border:1px solid rgba(244,114,208,.13);
}
.gg-step::before{
  content:'';
  position:absolute;
  inset:0;
  pointer-events:none;
  background:radial-gradient(260px 130px at 15% 0%,rgba(244,114,208,.10),transparent 68%);
  opacity:.75;
}
.gg-step-bg-num{
  left:auto!important;
  right:18px!important;
  top:12px!important;
  z-index:0;
  font-size:clamp(56px,5vw,82px)!important;
  color:rgba(244,114,208,.075)!important;
  line-height:.85;
}
.gg-step-icon,.gg-step-title,.gg-step-text{
  position:relative;
  z-index:1;
}
.gg-step-icon{
  width:50px;
  height:50px;
  margin-bottom:20px;
  background:linear-gradient(135deg,rgba(99,102,241,.20),rgba(217,70,239,.16))!important;
  border-color:rgba(244,114,208,.25)!important;
  color:#fbcfe8!important;
  box-shadow:0 12px 34px rgba(99,102,241,.16),0 0 24px rgba(244,114,208,.10);
}
.gg-step:hover{
  transform:translateY(-4px);
  border-color:rgba(244,114,208,.32);
  background:linear-gradient(180deg,rgba(99,102,241,.055),rgba(217,70,239,.035));
}
.gg-req-title i,.gg-req-list li::before{
  color:#fbcfe8;
}
.gg-req-list li::before{
  background:rgba(217,70,239,.15);
  border-color:rgba(244,114,208,.32);
}
.gg-faq-toggle{
  background:rgba(217,70,239,.13);
  border-color:rgba(244,114,208,.22);
  color:#fbcfe8;
}
.gg-faq-item.open .gg-faq-toggle{background:rgba(217,70,239,.22);}
.gg-cta{
  background:
    radial-gradient(520px 220px at 50% 0%,rgba(244,114,208,.18),transparent 70%),
    linear-gradient(135deg,rgba(99,102,241,.16),rgba(217,70,239,.13));
  border-color:rgba(244,114,208,.25);
}
@media(max-width:800px){
  .gg-step{min-height:230px;}
}
@media(max-width:480px){
  .gg-step{min-height:0;padding:28px 24px;}
  .gg-step-bg-num{font-size:58px!important;right:16px!important;top:12px!important;}
}
</style>

<!-- HERO -->
<div class="gg-hero">
  <div class="gg-hero-bg"></div>
  <div class="gg-hero-grid"></div>
  <div class="gg-hero-orb gg-hero-orb--1"></div>
  <div class="gg-hero-orb gg-hero-orb--2"></div>

  <div class="gg-hero-inner">
    <div class="gg-hero-left">
      <div class="gg-eyebrow">
        <i class="fa-solid fa-headset"></i>
        <?= t('Now Recruiting') ?>
      </div>
      <h1 class="gg-hero-h1"><?= t('Get Paid to') ?><br><span class="accent"><?= t('Play Games') ?></span></h1>
      <p class="gg-hero-sub"><?= t('Join our Gamer Girls team and earn money doing what you love. Set your own schedule, connect with players worldwide, and be part of something unique.') ?></p>
      <div class="gg-cta-group">
        <a href="/jobs/apply" class="gg-btn-main">
          <i class="fa-solid fa-paper-plane"></i> <?= t('Apply Now') ?>
        </a>
        <a href="/egirls" class="gg-btn-outline"><?= t('See Our GG Girls') ?></a>
      </div>
      <div class="gg-trust">
        <div class="gg-trust-item"><span class="gg-trust-dot"></span><?= t('Set your own schedule') ?></div>
        <div class="gg-trust-item"><span class="gg-trust-dot"></span><?= t('18+ only') ?></div>
        <div class="gg-trust-item"><span class="gg-trust-dot"></span><?= t('Fast approval') ?></div>
      </div>
    </div>

    <div class="gg-hero-right">
      <div class="gg-float-card">
        <div class="gg-float-icon gg-float-icon--p">
          <i class="fa-solid fa-coins"></i>
        </div>
        <div>
          <div class="gg-float-val">€500+</div>
          <div class="gg-float-label"><?= t('Monthly average earnings') ?></div>
        </div>
      </div>
      <div class="gg-float-card">
        <div class="gg-float-icon gg-float-icon--g">
          <i class="fas fa-clock"></i>
        </div>
        <div>
          <div class="gg-float-val"><?= t('Flexible') ?></div>
          <div class="gg-float-label"><?= t('Work when you want') ?></div>
        </div>
      </div>
      <div class="gg-float-card">
        <div class="gg-float-icon gg-float-icon--a">
          <i class="fas fa-bolt"></i>
        </div>
        <div>
          <div class="gg-float-val">24–48h</div>
          <div class="gg-float-label"><?= t('Application review time') ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr class="gg-sec-sep">

<!-- HOW IT WORKS -->
<section class="gg-sec">
  <div>
    <div class="gg-tag"><?= t('Process') ?></div>
    <h2 class="gg-h2" style="margin:0 0 14px;"><?= t('From application to first session') ?></h2>
    <p class="gg-lead"><?= t('Most GG Girls receive their first booking within 48 hours of approval.') ?></p>
  </div>
  <div class="gg-steps">
    <div class="gg-step">
      <div class="gg-step-bg-num">1</div>
      <div class="gg-step-icon"><i class="fas fa-file-pen"></i></div>
      <div class="gg-step-title"><?= t('Apply') ?></div>
      <div class="gg-step-text"><?= t('Fill out our quick application. Tell us about yourself, your games, and your availability. Takes about 3 minutes.') ?></div>
    </div>
    <div class="gg-step">
      <div class="gg-step-bg-num">2</div>
      <div class="gg-step-icon"><i class="fas fa-check-circle"></i></div>
      <div class="gg-step-title"><?= t('Get Approved') ?></div>
      <div class="gg-step-text"><?= t('Our team reviews your application within 24–48 hours. Once approved you get full access to your GG Girl dashboard.') ?></div>
    </div>
    <div class="gg-step">
      <div class="gg-step-bg-num">3</div>
      <div class="gg-step-icon"><i class="fas fa-gamepad"></i></div>
      <div class="gg-step-title"><?= t('Play Sessions') ?></div>
      <div class="gg-step-text"><?= t('Accept bookings from players, jump into voice chat, and play together. You choose which sessions you take.') ?></div>
    </div>
    <div class="gg-step">
      <div class="gg-step-bg-num">4</div>
      <div class="gg-step-icon"><i class="fas fa-money-bill-wave"></i></div>
      <div class="gg-step-title"><?= t('Get Paid') ?></div>
      <div class="gg-step-text"><?= t('Your balance updates after every completed session. Request a payout to your bank or PayPal whenever you want.') ?></div>
    </div>
  </div>
</section>

<hr class="gg-sec-sep">

<!-- REQUIREMENTS -->
<section class="gg-sec">
  <div>
    <div class="gg-tag"><?= t('Requirements') ?></div>
    <h2 class="gg-h2" style="margin:0 0 14px;"><?= t('What we look for') ?></h2>
    <p class="gg-lead"><?= t('We keep it simple. You don\'t need to be a pro player — just friendly, reliable and fun to play with.') ?></p>
  </div>
  <div class="gg-req-grid">
    <div class="gg-req-card">
      <div class="gg-req-title"><i class="fa-solid fa-clipboard-check"></i> <?= t('General Requirements') ?></div>
      <div class="gg-req-sub"><?= t('For all GG Girls') ?></div>
      <ul class="gg-req-list">
        <li><?= t('18+ years old') ?></li>
        <li><?= t('Fluent in English') ?></li>
        <li><?= t('Friendly, patient and professional attitude') ?></li>
        <li><?= t('Reliable internet connection (microphone optional but recommended)') ?></li>
        <li><?= t('Consistent availability — at least a few hours per week') ?></li>
      </ul>
    </div>
    <div class="gg-req-card">
      <div class="gg-req-title"><i class="fa-solid fa-gamepad"></i> <?= t('Gaming') ?></div>
      <div class="gg-req-sub"><?= t('No rank requirement — any level welcome') ?></div>
      <ul class="gg-req-list">
        <li><?= t('Familiar with at least one game (LoL, Valorant, TFT, or other)') ?></li>
        <li><?= t('Positive in-game attitude — no raging or negativity') ?></li>
        <li><?= t('You choose which services you offer (ranked, normals, just-for-fun...)') ?></li>
        <li><?= t('Voice chat is optional but recommended — players love it') ?></li>
        <li><?= t('You decide whether you play with or without voice') ?></li>
      </ul>
    </div>
  </div>
</section>

<hr class="gg-sec-sep">

<!-- WHY -->
<section class="gg-sec">
  <div>
    <div class="gg-tag"><?= t('Benefits') ?></div>
    <h2 class="gg-h2" style="margin:0 0 14px;"><?= t('Why join our GG Girls team?') ?></h2>
    <p class="gg-lead"><?= t('Built for gamers who want to earn on their own terms.') ?></p>
  </div>
  <div class="gg-why-grid">
    <div class="gg-why-card" style="--c:rgba(99,102,241,.58);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.20);color:#c7d2fe;"><i class="fas fa-clock"></i></div>
      <div class="gg-why-title"><?= t('Flexible Hours') ?></div>
      <div class="gg-why-text"><?= t('You decide when and how much you work. Accept bookings only when it suits you — no fixed schedule required.') ?></div>
    </div>
    <div class="gg-why-card" style="--c:rgba(99,102,241,.62);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(129,140,248,.22);color:#c7d2fe;"><i class="fas fa-money-bill-wave"></i></div>
      <div class="gg-why-title"><?= t('Real Earnings') ?></div>
      <div class="gg-why-text"><?= t('Earn money for every completed session. Active GG Girls make €500+ per month. Top performers earn significantly more.') ?></div>
    </div>
    <div class="gg-why-card" style="--c:rgba(99,102,241,.62);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(129,140,248,.22);color:#c7d2fe;"><i class="fas fa-users"></i></div>
      <div class="gg-why-title"><?= t('Built-in Audience') ?></div>
      <div class="gg-why-text"><?= t('LoLBoost.gg brings thousands of players every day. Your profile gets seen by real, paying customers — no self-promotion needed.') ?></div>
    </div>
    <div class="gg-why-card" style="--c:rgba(99,102,241,.62);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(129,140,248,.22);color:#c7d2fe;"><i class="fas fa-shield-halved"></i></div>
      <div class="gg-why-title"><?= t('Safe Platform') ?></div>
      <div class="gg-why-text"><?= t('We verify all customers before bookings. You\'re never alone — our support team is available 24/7 if anything feels off.') ?></div>
    </div>
    <div class="gg-why-card" style="--c:rgba(99,102,241,.62);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.20);"><img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt="" style="width:22px;height:22px;object-fit:contain;filter:drop-shadow(0 0 6px rgba(99,102,241,.58));"></div>
      <div class="gg-why-title"><?= t('GG Girl Community') ?></div>
      <div class="gg-why-text"><?= t('Private Discord with other GG Girls, team support and regular events. You\'re part of a community, not just a freelancer.') ?></div>
    </div>
    <div class="gg-why-card" style="--c:rgba(99,102,241,.62);">
      <div class="gg-why-icon" style="background:rgba(99,102,241,.12);border:1px solid rgba(129,140,248,.22);color:#c7d2fe;"><i class="fas fa-bolt"></i></div>
      <div class="gg-why-title"><?= t('Fast Payouts') ?></div>
      <div class="gg-why-text"><?= t('Your balance updates after every session. Request payouts anytime via bank transfer, PayPal or crypto — no minimum threshold.') ?></div>
    </div>
  </div>
</section>

<hr class="gg-sec-sep">

<!-- FAQ -->
<section class="gg-sec">
  <div>
    <div class="gg-tag">FAQ</div>
    <h2 class="gg-h2" style="margin:0 0 14px;"><?= t('Frequently asked questions') ?></h2>
    <p class="gg-lead"><?= t('Everything you need to know before applying.') ?></p>
  </div>
  <div class="gg-faq">
    <?php
    $faqs = [
      ['q' => t('Do I need to be a good player?'), 'a' => t('No rank requirement at all. You just need to know how to play the game and be fun to play with. The most important thing is that you\'re friendly, positive and reliable — not your rank.')],
      ['q' => t('What games do I need to play?'), 'a' => t('Mainly League of Legends, but sessions for Valorant, TFT and other games are also available. You can specify which games you\'re comfortable with in your application.')],
      ['q' => t('How do I get paid?'), 'a' => t('Your earnings are tracked automatically in your GG Girl dashboard. You can request a payout to your bank account, PayPal or crypto wallet at any time.')],
      ['q' => t('Is voice chat required?'), 'a' => t('Yes. Voice chat is a key part of the GG Girl experience for customers. You need a working microphone and should be comfortable speaking during sessions.')],
      ['q' => t('How many hours do I need to commit?'), 'a' => t('There\'s no minimum. You set your own availability in the dashboard. The more you\'re online, the more bookings you\'ll receive — but you\'re never forced to accept orders.')],
      ['q' => t('Is this safe and private?'), 'a' => t('Yes. All customers are verified before booking. You never share personal information directly with customers. Our support team is available 24/7 if you ever feel uncomfortable.')],
    ];
    foreach ($faqs as $faq): ?>
    <div class="gg-faq-item">
      <div class="gg-faq-q"><?= $faq['q'] ?><span class="gg-faq-toggle"><i class="fas fa-plus"></i></span></div>
      <div class="gg-faq-a"><?= $faq['a'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<div class="gg-cta-wrap">
  <div class="gg-cta">
    <div class="gg-cta-tag"><i class="fa-solid fa-star"></i> <?= t('Join the team') ?></div>
    <h2 class="gg-cta-h2"><?= t('Ready to get started?') ?></h2>
    <p class="gg-cta-sub"><?= t('Apply in minutes. We review applications within 24–48 hours.') ?></p>
    <div class="gg-cta-btns">
      <a href="/jobs/apply" class="gg-btn-main" style="font-size:clamp(14px,1.15vw,19px);padding:17px 38px;"><i class="fa-solid fa-paper-plane"></i> <?= t('Apply Now') ?></a>
      <a href="/egirls" class="gg-btn-outline" style="font-size:clamp(14px,1.1vw,18px);padding:17px 26px;"><?= t('Meet our GG Girls') ?></a>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.gg-faq-q').forEach(function(btn){
  btn.addEventListener('click',function(){
    var item=this.closest('.gg-faq-item'),wasOpen=item.classList.contains('open');
    document.querySelectorAll('.gg-faq-item.open').forEach(function(el){el.classList.remove('open');});
    if(!wasOpen)item.classList.add('open');
  });
});
</script>
