<?= $this->layout('website/layouts/master', ['meta' => $meta ?? [], 'bodyClass' => 'landing gamingmarket lolboost-hybrid']) ?>

<?= $this->start('styles') ?>
<style>
:root {
  --gm-blue:#4f6ef7; --gm-blueH:#3b58e8; --gm-blue2:#7c9fff;
  --gm-pink:#6366f1; --gm-bg:#04050f; --gm-bg2:#070a1a;
  --gm-card-bg:#0a0d1e; --gm-card-bg2:#0d1124;
  --gm-card:rgba(255,255,255,.06); --gm-border:rgba(255,255,255,.10);
  --gm-muted:rgba(255,255,255,.58); --gm-wrap:1300px; --text:#fff;
}body.landing {
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(60,80,247,.18), rgba(0,0,0,0) 60%),
    radial-gradient(900px  600px at 80% 18%, rgba(99,102,241,.14), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,100,220,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #04050f 0%, #070a1a 60%, #04050f 100%);
  background-attachment:scroll; background-color:#04050f;
  overflow-x:hidden; overflow-y:auto; touch-action:pan-y;
}@media(max-width:820px){body.landing{ background-attachment:scroll; }}.gm-bg {
  position:fixed; inset:0; z-index:-2; pointer-events:none; overflow:hidden;
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(50,70,240,.16), rgba(0,0,0,0) 60%),
    radial-gradient(900px  600px at 80% 18%, rgba(79,102,220,.12), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,90,230,.08), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #04050f 0%, #070a1a 60%, #04050f 100%);
}.gm-gridlines {
  position:fixed; inset:-2px; z-index:-1; pointer-events:none; opacity:.14;
  background-image:
    linear-gradient(to right,  rgba(255,255,255,.06) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(closest-side at 50% 18%, black 0%, transparent 76%);
}#gmStars { position:fixed; inset:0; z-index:-1; pointer-events:none; }.gm-star {
  position:absolute; border-radius:999px;
  background:rgba(255,255,255,.95);
  animation:gmStar linear infinite;
  filter:drop-shadow(0 0 10px rgba(79,102,241,.25));
}@keyframes gmStar {
  0%  { transform:translate3d(0,0,0);       opacity:.45 }
  60% {                                      opacity:.92 }
  100%{ transform:translate3d(-18vw,14vh,0); opacity:.18 }
}@media(max-width:820px){#gmStars{ display:none; }}footer, .footer-main, .footer-games, .footer-bottom,
[class*="footer"] { position:relative; z-index:10; }.gm-wrap     { max-width:var(--gm-wrap); margin:0 auto; padding:0 24px; }.gm-wrapWide { max-width:1460px; margin:0 auto; padding:0 28px; }.gm-section  { padding:110px 0; background:#04050f; }#boosting    { overflow:visible; }.gm-sectionTag { display:inline-flex; align-items:center; gap:10px; margin-bottom:14px; color:rgba(180,188,255,.80); font-size:13px; font-weight:900; letter-spacing:.18em; text-transform:uppercase; }.gm-sectionTag span { width:28px; height:2px; border-radius:999px; background:linear-gradient(90deg,var(--gm-blue),var(--gm-blue2)); }.gm-headRow  { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:18px; margin-bottom:32px; }.gm-h2       { margin:0; font-size:clamp(32px,4vw,56px); letter-spacing:-.025em; line-height:1.1; }.gm-sub      { margin:12px 0 0; color:var(--gm-muted); max-width:72ch; line-height:1.7; font-size:19px; }.gm-btn { display:inline-flex; align-items:center; gap:9px; padding:13px 22px; border-radius:16px; font-weight:700; font-size:15px; border:1px solid var(--gm-border); background:var(--gm-card); color:#fff; text-decoration:none; cursor:pointer; transition:transform .18s,background .18s,border-color .18s; }.gm-btn:hover { background:rgba(255,255,255,.10); border-color:rgba(255,255,255,.22); transform:translateY(-1px); }.gm-btnPrimary { background:linear-gradient(135deg,var(--gm-blue),var(--gm-blueH)); border-color:rgba(120,122,255,.45); }.gm-btnPrimary:hover { filter:brightness(1.09); box-shadow:0 12px 32px rgba(99,102,241,.32); }.gm-btnGhost  { background:transparent; border-color:rgba(255,255,255,.16); }.gm-btnSmall  { padding:8px 14px; font-size:13px; border-radius:11px; }.gm-boostSlider { position:relative; }.gm-boostGrid {
  display:flex; gap:14px;
  overflow-x:auto; overflow-y:visible;

  padding:0 24px 20px; scroll-padding-inline:24px;
  scroll-snap-type:x mandatory; scroll-behavior:smooth;
  scrollbar-width:none; cursor:grab;

}.gm-boostGrid::-webkit-scrollbar { display:none; }.gm-boostGrid.is-dragging { cursor:grabbing; user-select:none; }.gm-boostGrid > * { flex:0 0 auto; scroll-snap-align:start; }@media(max-width:640px){.gm-boostGrid{ padding:0 14px 18px; gap:10px; }}.gm-sliderBtn { position:absolute; top:50%; transform:translateY(-50%); width:42px; height:42px; border-radius:14px; border:1px solid rgba(255,255,255,.10); background:rgba(10,16,30,.55); backdrop-filter:blur(10px); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:3; transition:.18s transform,.18s background,.18s border-color; }.gm-sliderBtn:hover { background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.18); transform:translateY(-50%) scale(1.03); }.gm-sliderBtn.prev { left:12px; }.gm-sliderBtn.next { right:12px; }@media(max-width:768px){.gm-sliderBtn{ display:none; }}.gm-boostCard { position:relative; display:flex; align-items:center; justify-content:center; border-radius:22px; overflow:hidden; background:#0a0d1e; border:none; box-shadow:none; transition:transform .18s; height:380px; width:260px; flex:0 0 260px; cursor:pointer; }.gm-boostCard:hover { transform:none; }.gm-boostCardLink  { position:absolute; inset:0; z-index:0; border-radius:inherit; }.gm-boostThumb     { position:relative; width:100%; height:100%; border-radius:0; background:transparent; }.gm-boostImg       { display:block; height:100%; width:100%; object-fit:cover; border-radius:0; border:none; background:#0a0d1e; box-shadow:none; filter:saturate(1.05) contrast(1.02); transition:transform .18s,box-shadow .18s; }.gm-boostCard:hover .gm-boostImg { box-shadow:0 18px 55px rgba(0,0,0,.40); transform:scale(1.04); }.gm-boostOverlay   { position:absolute; inset:0; z-index:1; pointer-events:none; opacity:0; transition:opacity .18s; border-radius:22px; background:linear-gradient(180deg,rgba(0,0,0,.10) 0%,rgba(0,0,0,.55) 58%,rgba(0,0,0,.78) 100%); }.gm-boostCard:hover .gm-boostOverlay, .gm-boostCard:focus-within .gm-boostOverlay { opacity:1; }.gm-boostContent   { position:absolute; inset:0; z-index:2; display:flex; flex-direction:column; justify-content:flex-end; padding:22px; gap:14px; border-radius:22px; background:linear-gradient(180deg,rgba(4,7,18,0) 28%,rgba(4,7,18,.70) 70%,rgba(4,7,18,.96) 100%); opacity:0; transform:translateY(10px); transition:opacity .18s,transform .18s; pointer-events:none; }.gm-boostCard:hover .gm-boostContent, .gm-boostCard:focus-within .gm-boostContent { opacity:1; transform:none; pointer-events:auto; }.gm-boostTitle     { display:flex; align-items:center; gap:10px; font-weight:900; font-size:20px; line-height:1.15; color:#fff; text-shadow:0 6px 24px rgba(0,0,0,.55); }.gm-boostGameBadge { width:36px; height:36px; border-radius:14px; display:grid; place-items:center; background:rgba(0,0,0,.30); border:1px solid rgba(255,255,255,.14); overflow:hidden; flex:0 0 auto; }.gm-boostGameBadge img { width:100%; height:100%; object-fit:cover; display:block; }.gm-boostGameFallback { font-weight:950; color:rgba(255,255,255,.90); text-shadow:0 6px 18px rgba(0,0,0,.55); }.gm-boostPills     { display:flex; flex-wrap:wrap; gap:8px; max-width:92%; opacity:0; transform:translateY(6px); transition:opacity .18s,transform .18s; }.gm-boostCard:hover .gm-boostPills, .gm-boostCard:focus-within .gm-boostPills { opacity:1; transform:none; }@media(max-width:768px){.gm-boostCard{ height:62vw; width:44vw; flex:0 0 44vw; }}@media(max-width:480px){.gm-boostCard{ height:75vw; width:54vw; flex:0 0 54vw; }}.gm-pill { display:inline-flex; align-items:center; height:36px; padding:0 14px; border-radius:999px; font-size:13px; font-weight:800; color:rgba(255,255,255,.88); background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); text-decoration:none; gap:6px; white-space:nowrap; transition:background .16s,border-color .16s,transform .16s; }.gm-pill:hover { background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.20); transform:translateY(-1px); }.gm-marketSection { position:relative; overflow:hidden; background:#04050f; }.gm-marketHeadGrid { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:end; gap:24px; margin-bottom:28px; }.gm-marketTitle { font-size:clamp(32px,3.8vw,52px); letter-spacing:-.03em; margin:0; }.gm-marketBtn   { min-height:48px; white-space:nowrap; }.gm-serviceTiles { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }.gm-serviceTile  { position:relative; min-height:240px; display:flex; flex-direction:column; justify-content:space-between; gap:18px; padding:22px; border-radius:24px; border:1px solid rgba(255,255,255,.10); background:linear-gradient(160deg,#0c1022,#0a0d1e); box-shadow:0 12px 40px rgba(0,0,0,.40),inset 0 1px 0 rgba(255,255,255,.06); color:rgba(255,255,255,.92); text-decoration:none; overflow:hidden; transition:transform .22s,border-color .22s; }.gm-serviceTile:hover { transform:translateY(-5px); border-color:rgba(99,102,241,.32); }.gm-tileIcon { width:56px; height:56px; display:grid; place-items:center; border-radius:15px; color:#fff; font-size:20px; background:linear-gradient(135deg,#3b58e8,#6366f1); box-shadow:0 12px 30px rgba(99,102,241,.24); flex-shrink:0; }.gm-serviceTile h3 { margin:0 0 8px; font-size:24px; letter-spacing:-.02em; }.gm-serviceTile p  { margin:0; color:rgba(255,255,255,.58); line-height:1.62; font-size:15px; }.gm-serviceTile > span:last-child { display:inline-flex; align-items:center; gap:8px; font-weight:900; color:rgba(255,255,255,.85); font-size:13px; }@media(max-width:1100px){.gm-serviceTiles{ grid-template-columns:repeat(2,1fr); }}@media(max-width:640px) {.gm-serviceTiles{ grid-template-columns:1fr; }}.gm-reviewsSection { overflow:hidden; padding:90px 0; background:#04050f; }.gm-reviewsHead    { text-align:center; margin-bottom:40px; }.gm-reviewsHead .gm-sectionTag { justify-content:center; }.gm-reviewsHead .gm-sub { margin-left:auto; margin-right:auto; }.gm-marquee { position:relative; width:100%; padding:6px 0; overflow:hidden; }.gm-marquee+.gm-marquee { margin-top:14px; }.gm-fadeL,.gm-fadeR { position:absolute; top:0; bottom:0; width:160px; z-index:2; pointer-events:none; }.gm-fadeL { left:0;  background:linear-gradient(to right, var(--gm-bg) 0%, transparent 100%); }.gm-fadeR { right:0; background:linear-gradient(to left,  var(--gm-bg) 0%, transparent 100%); }.gm-row   { overflow:hidden; }.gm-track { display:flex; gap:14px; width:max-content; animation:gmMarquee 55s linear infinite; will-change:transform; }.gm-track.reverse { animation-direction:reverse; }@keyframes gmMarquee { from{transform:translateX(0)} to{transform:translateX(-50%)} }@media(prefers-reduced-motion:reduce){.gm-track{ animation:none; }}.gm-reviewCard { flex:0 0 320px; padding:22px 24px; border-radius:22px; background:linear-gradient(160deg,#0c1022,#0a0d1e); border:1px solid rgba(255,255,255,.10); box-shadow:0 8px 32px rgba(0,0,0,.35); }.gm-reviewAvatarWrap { display:flex; align-items:center; gap:10px; margin-bottom:12px; }.gm-avatar { position:relative; width:40px; height:40px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:15px; color:#fff; overflow:hidden; }.gm-avatar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; border-radius:50%; }.gm-reviewAvatarWrap b    { display:block; font-size:13px; font-weight:800; }.gm-reviewAvatarWrap span { display:block; font-size:11px; color:var(--gm-muted); }.gm-reviewStars { display:flex; gap:3px; color:#fbbf24; font-size:11px; margin-bottom:10px; }.gm-reviewCard p { font-size:14px; line-height:1.65; color:rgba(255,255,255,.74); margin:0 0 13px; }.lbHybridSteps   { padding:90px 0; background:#04050f; }.lbHybridLive    { padding:90px 0; background:#04050f; }.lbHybridBoosters{ padding:90px 0; background:#04050f; }.lbHybridWrap    { max-width:var(--gm-wrap); margin:0 auto; padding:0 24px; }.lbHybridCenter  { text-align:center; margin-bottom:40px; }.lbHybridCenter .gm-sub { margin-left:auto; margin-right:auto; }.lbHybridSplit   { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:18px; margin-bottom:28px; }.lbHybridStepGrid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; }.lbHybridStep    { padding:28px; border-radius:22px; background:linear-gradient(160deg,#0c1022,#0a0d1e); border:1px solid rgba(79,110,241,.20); box-shadow:0 8px 32px rgba(0,0,0,.35); text-align:center; }.lbHybridStepIcon { width:62px; height:62px; border-radius:18px; margin:0 auto 18px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#3b58e8,#6366f1); color:#fff; font-size:22px; }.lbHybridStep strong { display:block; font-size:20px; font-weight:800; margin-bottom:10px; }.lbHybridStep p  { margin:0; color:var(--gm-muted); line-height:1.65; font-size:16px; }@media(max-width:768px){.lbHybridStepGrid{ grid-template-columns:1fr; }}.lbHybridOrdersGrid  { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }.lbHybridOrder       { padding:24px; border-radius:22px; background:linear-gradient(160deg,#0c1022,#0a0d1e); border:1px solid rgba(79,110,241,.22); box-shadow:0 8px 32px rgba(0,0,0,.35); display:flex; flex-direction:column; gap:10px; }.lbHybridOrderTop    { display:flex; align-items:center; justify-content:space-between; }.lbHybridOrderIcon   { width:32px; height:32px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(99,102,241,.18); color:var(--gm-blue2); font-size:13px; }.lbHybridOrderStatus { font-size:10px; font-weight:900; padding:3px 9px; border-radius:999px; background:rgba(99,102,241,.14); border:1px solid rgba(99,102,241,.28); color:var(--gm-blue2); }.lbHybridOrderStatus.done { background:rgba(34,197,94,.12); border-color:rgba(34,197,94,.28); color:#4ade80; }.lbHybridOrder strong { font-size:17px; font-weight:800; line-height:1.3; }.order-game          { font-size:11px; color:var(--gm-muted); }.lbHybridOrderRanks  { display:flex; align-items:center; gap:8px; }.lbHybridOrderRank   { display:flex; align-items:center; gap:5px; }.lbHybridOrderRank img { width:22px; height:22px; object-fit:contain; }.lbHybridOrderRank span { font-size:12px; font-weight:700; color:rgba(255,255,255,.80); }.lbHybridOrderArrow  { color:rgba(255,255,255,.30); font-size:11px; }.lbHybridProgress    { height:6px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden; margin-top:auto; }.lbHybridProgress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--gm-blue),var(--gm-blue2)); }.lbHybridOrderBooster { display:flex; align-items:center; gap:7px; }.lbHybridOrderBooster img { width:24px; height:24px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.14); }.lbHybridOrderBooster span { font-size:11px; color:var(--gm-muted); }.lbHybridPct { font-size:11px; font-weight:900; color:var(--gm-blue2); margin-left:auto; }@media(max-width:1100px){.lbHybridOrdersGrid{ grid-template-columns:repeat(2,1fr); }}@media(max-width:480px) {.lbHybridOrdersGrid{ grid-template-columns:1fr; }}.lbHybridBoosterGrid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }.lbHybridBooster     { padding:24px; border-radius:22px; background:linear-gradient(160deg,#0c1022,#0a0d1e); border:1px solid rgba(255,255,255,.10); box-shadow:0 8px 32px rgba(0,0,0,.35); display:flex; flex-direction:column; gap:12px; text-decoration:none; color:inherit; transition:transform .2s,border-color .2s; }.lbHybridBooster:hover { transform:translateY(-4px); border-color:rgba(99,102,241,.32); }.lbHybridBoosterHead { display:flex; align-items:center; gap:12px; }.lbHybridBoosterAvatar { width:46px; height:46px; border-radius:50%; flex-shrink:0; overflow:hidden; border:2px solid rgba(79,110,241,.35); display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#3b58e8,#6366f1); font-weight:900; font-size:18px; color:#fff; }.lbHybridBoosterAvatar img { width:100%; height:100%; object-fit:cover; }.lbHybridBoosterName { font-size:17px; font-weight:800; line-height:1.2; }.lbHybridBoosterRole { font-size:11px; color:var(--gm-muted); display:block; margin-top:2px; }.lbHybridBoosterOnline { display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:800; padding:3px 9px; border-radius:999px; background:rgba(34,197,94,.10); border:1px solid rgba(34,197,94,.24); color:#4ade80; width:fit-content; }.lbHybridBoosterOnline.offline { background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.10); color:var(--gm-muted); }.lbHybridBoosterOnline .dot { width:6px; height:6px; border-radius:50%; background:#4ade80; }.lbHybridBoosterOnline.offline .dot { background:rgba(255,255,255,.28); }.lbHybridBoosterRating { display:flex; align-items:center; gap:6px; }.lbHybridBoosterRating i { color:#fbbf24; font-size:13px; }.lbHybridBoosterRating span { font-size:14px; font-weight:800; }.lbHybridBoosterRank { display:flex; align-items:center; gap:6px; }.lbHybridBoosterRank img { width:22px; height:22px; object-fit:contain; }.lbHybridBoosterChamps { display:flex; flex-wrap:wrap; gap:4px; }.lbHybridBoosterChamps img { width:28px; height:28px; border-radius:7px; object-fit:cover; border:1px solid rgba(255,255,255,.10); }@media(max-width:1100px){.lbHybridBoosterGrid{ grid-template-columns:repeat(2,1fr); }}@media(max-width:480px) {.lbHybridBoosterGrid{ grid-template-columns:1fr; }}.gm-supportfaq   { padding:90px 0; background:#04050f; }.gm-helpCenterShell { max-width:820px; margin:0 auto; text-align:center; }.gm-sectionTag.gm-helpCenterTag { justify-content:center; }.gm-helpCenterTitle { margin:16px 0 16px; font-size:clamp(38px,4.8vw,64px); letter-spacing:-.04em; line-height:1.06; }.gm-helpCenterLead  { max-width:640px; margin:0 auto; color:rgba(240,246,255,.68); font-size:clamp(17px,1.7vw,22px); line-height:1.7; }.gm-helpCenterActions { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin:26px 0 24px; }.gm-helpTabs { display:flex; justify-content:center; gap:8px; flex-wrap:wrap; margin:0 0 24px; }.gm-helpTab  { padding:7px 16px; border-radius:999px; font-size:13px; font-weight:700; cursor:pointer; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:rgba(255,255,255,.65); transition:background .15s,color .15s; }.gm-helpTab:hover { background:rgba(255,255,255,.10); color:#fff; }.gm-helpTab.is-active { background:rgba(99,102,241,.18); border-color:rgba(99,102,241,.38); color:#fff; }.gm-faqPremium { border-radius:24px; border:1px solid rgba(79,110,241,.18); background:#0a0d1e; box-shadow:0 12px 40px rgba(0,0,0,.40); overflow:hidden; text-align:left; }.gm-faqitem  { border-bottom:1px solid rgba(255,255,255,.07); }.gm-faqitem:last-child { border-bottom:0; }.gm-faqbtn   { width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:22px 26px; background:none; border:none; color:#fff; cursor:pointer; font-size:17px; font-weight:700; text-align:left; transition:background .15s; }.gm-faqbtn:hover { background:rgba(255,255,255,.03); }.gm-faqpanel { max-height:0; overflow:hidden; transition:max-height .3s ease; }.gm-faqitem.open .gm-faqpanel { max-height:400px; }.gm-faqitem.open .chev i { transform:rotate(180deg); }.chev i { display:block; transition:transform .3s; color:rgba(255,255,255,.45); font-size:13px; }.gm-faqinner { padding:0 26px 22px; color:rgba(255,255,255,.62); line-height:1.7; font-size:16px; }body.landing.gamingmarket,
body.landing{
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(59,130,246,.18), rgba(0,0,0,0) 60%),
    radial-gradient(900px 600px at 80% 18%, rgba(56,189,248,.10), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,189,248,.08), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #04030e 0%, #07051a 60%, #04030e 100%) !important;
  background-attachment:scroll !important;
  background-color:#04030e !important;
}.gm-section, .gm-supportfaq{
  background:transparent !important;
}.gm-bg{
  z-index:-3 !important;
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(59,130,246,.16), rgba(0,0,0,0) 60%),
    radial-gradient(900px 600px at 80% 18%, rgba(56,189,248,.10), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(14,165,233,.06), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #05040f 0%, #09071d 60%, #05040f 100%) !important;
}.gm-gridlines,
#gmStars{
  z-index:-2 !important;
}@media(max-width:820px){body.landing.gamingmarket,
  body.landing{background-attachment:scroll !important;}#gmStars{display:block !important; opacity:.42;}}.gm-helpCenterShell{
  max-width:1180px !important;
  padding:0 24px !important;
}.gm-helpCenterLead{
  max-width:820px !important;
}.gm-faqPremium{
  max-width:1120px !important;
  margin:0 auto !important;
  background:linear-gradient(180deg, rgba(20,17,42,.88), rgba(9,10,24,.76)) !important;
  border-color:rgba(96,165,250,.18) !important;
  box-shadow:0 24px 90px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.07) !important;
}.lbHybridLive,
.lbHybridBoosters{
  background:transparent !important;
  position:relative;
  overflow:hidden;
}.lbHybridLive::before,
.lbHybridBoosters::before{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background:
    radial-gradient(720px 340px at 12% 18%, rgba(99,102,241,.15), transparent 62%),
    radial-gradient(820px 380px at 86% 30%, rgba(56,189,248,.10), transparent 64%),
    linear-gradient(180deg, rgba(255,255,255,.015), transparent 36%, rgba(255,255,255,.012));
  opacity:.95;
}.lbHybridLive .lbHybridWrap,
.lbHybridBoosters .lbHybridWrap{position:relative;z-index:1;}.lbHybridOrdersGrid{
  grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
  gap:18px !important;
}.lbHybridOrder{
  position:relative;
  min-height:210px;
  padding:24px 24px 22px !important;
  border-radius:28px !important;
  overflow:hidden;
  isolation:isolate;
  background:
    linear-gradient(145deg, rgba(13,17,38,.96), rgba(7,9,21,.94)) !important;
  border:1px solid rgba(108,132,255,.24) !important;
  box-shadow:
    0 24px 70px rgba(0,0,0,.42),
    inset 0 1px 0 rgba(255,255,255,.07) !important;
  transform:translateZ(0);
  transition:transform .22s ease, border-color .22s ease, box-shadow .22s ease;
}.lbHybridOrder::before{
  content:"";
  position:absolute;
  inset:-1px;
  z-index:-2;
  background:
    radial-gradient(420px 180px at 18% -8%, rgba(99,102,241,.28), transparent 62%),
    radial-gradient(360px 180px at 96% 18%, rgba(96,165,250,.18), transparent 62%),
    linear-gradient(120deg, rgba(255,255,255,.10), transparent 28%, transparent 72%, rgba(255,255,255,.06));
  opacity:.92;
}.lbHybridOrder::after{
  content:"";
  position:absolute;
  right:-70px;
  top:-80px;
  width:210px;
  height:210px;
  border-radius:50%;
  background:conic-gradient(from 180deg, rgba(99,102,241,.0), rgba(99,102,241,.26), rgba(96,165,250,.14), rgba(99,102,241,.0));
  filter:blur(1px);
  opacity:.75;
  z-index:-1;
}.lbHybridOrder:hover{
  transform:translateY(-5px);
  border-color:rgba(129,140,248,.48) !important;
  box-shadow:0 30px 92px rgba(0,0,0,.54), 0 0 0 1px rgba(129,140,248,.16), inset 0 1px 0 rgba(255,255,255,.10) !important;
}.lbHybridOrderTop{margin-bottom:2px;}.lbHybridOrderIcon{
  width:38px !important;
  height:38px !important;
  border-radius:14px !important;
  background:linear-gradient(135deg, rgba(79,110,247,.35), rgba(99,102,241,.16)) !important;
  border:1px solid rgba(129,140,248,.24);
  box-shadow:0 14px 34px rgba(79,110,247,.18);
}.lbHybridOrderStatus{
  border-radius:12px !important;
  padding:6px 10px !important;
  background:rgba(79,110,247,.12) !important;
  border-color:rgba(129,140,248,.32) !important;
  letter-spacing:.02em;
}.lbHybridOrder strong{
  font-size:19px !important;
  letter-spacing:-.02em;
}.order-game{color:rgba(230,236,255,.62) !important;}.lbHybridOrderRanks{
  gap:10px !important;
  padding:8px 0 2px;
}.lbHybridOrderRank img{
  width:28px !important;
  height:28px !important;
  filter:drop-shadow(0 8px 14px rgba(0,0,0,.35));
}.lbHybridOrderRank span{font-size:12px !important;color:rgba(255,255,255,.86) !important;}.lbHybridOrderBooster{
  margin-top:4px;
  padding-top:10px;
  border-top:1px solid rgba(255,255,255,.07);
}.lbHybridOrderBooster img{
  width:30px !important;
  height:30px !important;
  border:2px solid rgba(129,140,248,.35) !important;
  box-shadow:0 0 0 4px rgba(99,102,241,.10);
}.lbHybridProgress{
  height:8px !important;
  background:rgba(255,255,255,.08) !important;
  box-shadow:inset 0 1px 4px rgba(0,0,0,.35);
}.lbHybridProgress span{
  background:linear-gradient(90deg,#60a5fa,#818cf8,#a78bfa) !important;
  box-shadow:0 0 22px rgba(129,140,248,.55);
}.lbHybridPct{color:#c7d2fe !important;}.lbHybridBoosterGrid{
  gap:18px !important;
}.lbHybridBooster{
  position:relative;
  min-height:260px;
  padding:24px !important;
  border-radius:30px !important;
  overflow:hidden;
  isolation:isolate;
  background:
    linear-gradient(150deg, rgba(14,18,39,.98), rgba(7,9,22,.95)) !important;
  border:1px solid rgba(255,255,255,.11) !important;
  box-shadow:0 22px 72px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.07) !important;
  gap:14px !important;
}.lbHybridBooster::before{
  content:"";
  position:absolute;
  inset:-1px;
  z-index:-2;
  background:
    radial-gradient(260px 170px at 22% 8%, rgba(96,165,250,.18), transparent 68%),
    radial-gradient(280px 190px at 92% 18%, rgba(96,165,250,.14), transparent 72%),
    radial-gradient(260px 220px at 50% 120%, rgba(99,102,241,.14), transparent 70%);
}.lbHybridBooster::after{
  content:"";
  position:absolute;
  left:24px;
  right:24px;
  top:86px;
  height:1px;
  background:linear-gradient(90deg, transparent, rgba(255,255,255,.14), transparent);
  opacity:.9;
}.lbHybridBooster:hover{
  transform:translateY(-7px) scale(1.01) !important;
  border-color:rgba(129,140,248,.42) !important;
  box-shadow:0 34px 96px rgba(0,0,0,.56), 0 0 0 1px rgba(129,140,248,.12), inset 0 1px 0 rgba(255,255,255,.10) !important;
}.lbHybridBoosterHead{
  align-items:flex-start !important;
  gap:14px !important;
  min-height:54px;
}.lbHybridBoosterAvatar{
  width:56px !important;
  height:56px !important;
  border-radius:18px !important;
  border:1px solid rgba(129,140,248,.42) !important;
  box-shadow:0 16px 34px rgba(0,0,0,.34), 0 0 0 5px rgba(99,102,241,.10);
}.lbHybridBoosterName{
  font-size:18px !important;
  letter-spacing:-.02em;
}.lbHybridBoosterRole{color:rgba(230,236,255,.58) !important;}.lbHybridBoosterOnline{
  position:absolute;
  top:24px;
  right:24px;
  padding:0 !important;
  width:10px;
  height:10px;
  border-radius:999px !important;
  background:#22c55e !important;
  border:0 !important;
  box-shadow:0 0 0 6px rgba(34,197,94,.10), 0 0 18px rgba(34,197,94,.45);
  overflow:hidden;
  color:transparent !important;
}.lbHybridBoosterOnline.offline{
  background:rgba(148,163,184,.48) !important;
  box-shadow:0 0 0 6px rgba(148,163,184,.08);
}.lbHybridBoosterOnline .dot{display:none !important;}.lbHybridBoosterRating{
  margin-top:18px;
  width:fit-content;
  padding:8px 10px;
  border-radius:14px;
  background:rgba(251,191,36,.10);
  border:1px solid rgba(251,191,36,.18);
}.lbHybridBoosterRank{
  min-height:38px;
  padding:8px 0;
  font-weight:900;
}.lbHybridBoosterRank img{
  width:30px !important;
  height:30px !important;
  filter:drop-shadow(0 8px 14px rgba(0,0,0,.32));
}.lbHybridBoosterChamps{
  margin-top:auto;
  display:grid !important;
  grid-template-columns:repeat(6, 32px);
  gap:7px !important;
}.lbHybridBoosterChamps img{
  width:32px !important;
  height:32px !important;
  border-radius:10px !important;
  border-color:rgba(255,255,255,.14) !important;
  box-shadow:0 8px 18px rgba(0,0,0,.28);
  transition:transform .18s ease, border-color .18s ease;
}.lbHybridBooster:hover .lbHybridBoosterChamps img{border-color:rgba(129,140,248,.32) !important;}.lbHybridBoosterChamps img:hover{transform:translateY(-2px) scale(1.06);}@media(max-width:1100px){.lbHybridOrdersGrid{grid-template-columns:1fr !important;}.lbHybridBoosterGrid{grid-template-columns:repeat(2, minmax(0, 1fr)) !important;}}@media(max-width:560px){.lbHybridLive,
  .lbHybridBoosters{padding:66px 0 !important;}.lbHybridBoosterGrid{grid-template-columns:1fr !important;}.lbHybridOrder,
  .lbHybridBooster{border-radius:24px !important;padding:20px !important;}.lbHybridBoosterChamps{grid-template-columns:repeat(6, 30px);}.lbHybridBoosterChamps img{width:30px !important;height:30px !important;}}.gm-supportfaq{
  padding:110px 0 96px !important;
  overflow:hidden !important;
}.gm-helpCenterShell{
  max-width:1320px !important;
  margin:0 auto !important;
  padding:0 28px !important;
  display:grid !important;
  grid-template-columns:minmax(280px, .78fr) minmax(0, 1.22fr) !important;
  gap:34px !important;
  align-items:start !important;
}.gm-helpCenterShell > .gm-sectionTag,
.gm-helpCenterShell > .gm-helpCenterTitle,
.gm-helpCenterShell > .gm-helpCenterLead,
.gm-helpCenterShell > .gm-helpCenterActions,
.gm-helpCenterShell > .gm-helpTabs{
  grid-column:1 !important;
}.gm-helpCenterShell > .gm-faqPremium{
  grid-column:2 !important;
  grid-row:1 / span 5 !important;
}.gm-helpCenterTag{
  justify-self:start !important;
  margin:0 0 10px !important;
}.gm-helpCenterTitle{
  margin:0 !important;
  font-size:clamp(42px, 4.6vw, 76px) !important;
  line-height:.96 !important;
  letter-spacing:-.055em !important;
  text-align:left !important;
  text-shadow:0 22px 70px rgba(0,0,0,.58), 0 0 42px rgba(59,130,246,.14) !important;
}.gm-helpCenterLead{
  max-width:430px !important;
  margin:4px 0 0 !important;
  text-align:left !important;
  color:rgba(244,240,255,.68) !important;
  font-size:18px !important;
  line-height:1.72 !important;
}.gm-helpCenterActions{
  margin:8px 0 0 !important;
  display:grid !important;
  grid-template-columns:1fr !important;
  gap:12px !important;
  max-width:360px !important;
}.gm-helpCenterActions .gm-btn{
  width:100% !important;
  min-height:56px !important;
  border-radius:18px !important;
  justify-content:center !important;
}.gm-helpTabs{
  margin:14px 0 0 !important;
  display:grid !important;
  grid-template-columns:1fr 1fr !important;
  gap:10px !important;
  max-width:360px !important;
}.gm-helpTab{
  border-radius:16px !important;
  min-height:46px !important;
  padding:0 14px !important;
  background:linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.028)) !important;
  border:1px solid rgba(255,255,255,.105) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06) !important;
  color:rgba(244,240,255,.66) !important;
  font-size:12px !important;
  font-weight:950 !important;
  letter-spacing:.04em !important;
}.gm-helpTab.is-active,
.gm-helpTab:hover{
  background:linear-gradient(135deg, rgba(99,102,241,.30), rgba(96,165,250,.14)) !important;
  border-color:rgba(129,140,248,.46) !important;
  color:#fff !important;
  box-shadow:0 14px 38px rgba(99,102,241,.16), inset 0 1px 0 rgba(255,255,255,.12) !important;
}.gm-faqPremium{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:14px !important;
  border-radius:30px !important;
  background:linear-gradient(180deg, rgba(18,18,42,.92), rgba(8,8,22,.74)) !important;
  border:1px solid rgba(96,165,250,.20) !important;
  box-shadow:0 30px 100px rgba(0,0,0,.46), 0 0 0 1px rgba(255,255,255,.035) inset !important;
  overflow:visible !important;
}.gm-faqitem{
  position:relative !important;
  overflow:hidden !important;
  border:1px solid rgba(255,255,255,.085) !important;
  border-radius:20px !important;
  background:linear-gradient(180deg, rgba(255,255,255,.050), rgba(255,255,255,.022)) !important;
  margin:0 0 12px !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.055) !important;
}.gm-faqitem:last-child{margin-bottom:0 !important;}.gm-faqitem::before{
  content:"" !important;
  position:absolute !important;
  left:0 !important;
  top:18px !important;
  bottom:18px !important;
  width:3px !important;
  border-radius:0 999px 999px 0 !important;
  background:linear-gradient(180deg, #818cf8, #60a5fa) !important;
  opacity:.0 !important;
  transition:.2s ease !important;
}.gm-faqitem.open{
  border-color:rgba(129,140,248,.32) !important;
  background:radial-gradient(520px 160px at 14% 0%, rgba(129,140,248,.13), transparent 62%), linear-gradient(180deg, rgba(255,255,255,.072), rgba(255,255,255,.030)) !important;
  box-shadow:0 18px 54px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.08) !important;
}.gm-faqitem.open::before{opacity:1 !important;}.gm-faqbtn{
  min-height:68px !important;
  padding:20px 24px !important;
  border-radius:20px !important;
  font-size:16px !important;
  line-height:1.35 !important;
  font-weight:950 !important;
  color:rgba(255,255,255,.94) !important;
}.gm-faqbtn:hover{background:rgba(255,255,255,.025) !important;}.gm-faqbtn .chev{
  width:36px !important;
  height:36px !important;
  flex:0 0 36px !important;
  display:grid !important;
  place-items:center !important;
  border-radius:13px !important;
  background:rgba(255,255,255,.055) !important;
  border:1px solid rgba(255,255,255,.10) !important;
  color:rgba(244,240,255,.62) !important;
}.gm-faqinner{
  padding:0 24px 24px !important;
  max-width:86ch !important;
  color:rgba(244,240,255,.67) !important;
  font-size:15.5px !important;
  line-height:1.72 !important;
}@media(max-width:1100px){.gm-helpCenterShell{grid-template-columns:1fr !important; max-width:920px !important;}.gm-helpCenterShell > .gm-sectionTag,
  .gm-helpCenterShell > .gm-helpCenterTitle,
  .gm-helpCenterShell > .gm-helpCenterLead,
  .gm-helpCenterShell > .gm-helpCenterActions,
  .gm-helpCenterShell > .gm-helpTabs,
  .gm-helpCenterShell > .gm-faqPremium{grid-column:1 !important; grid-row:auto !important;}.gm-helpCenterTitle,.gm-helpCenterLead{text-align:center !important; margin-left:auto !important; margin-right:auto !important;}.gm-helpCenterTag{justify-self:center !important;}.gm-helpCenterActions,.gm-helpTabs{margin-left:auto !important; margin-right:auto !important;}}@media(max-width:640px){.gm-supportfaq{padding:76px 0 !important;}.gm-helpCenterShell{padding:0 14px !important;}.gm-helpTabs{grid-template-columns:1fr !important; max-width:none !important; width:100% !important;}.gm-helpCenterActions{max-width:none !important; width:100% !important;}.gm-faqPremium{padding:10px !important; border-radius:24px !important;}.gm-faqbtn{padding:18px 16px !important; font-size:15px !important;}.gm-faqinner{padding:0 16px 20px !important; font-size:14.5px !important;}}.lbHybridSteps,
.lbHybridSteps.gm-section{
  background:transparent !important;
  background-color:transparent !important;
}.lbHybridSteps::before,
.lbHybridSteps::after{
  pointer-events:none !important;
}.gm-supportfaq,
.gm-supportfaq .gm-wrap,
.gm-helpCenterShell{
  overflow:visible !important;
}.gm-helpTabs{
  display:flex !important;
  align-items:flex-end !important;
  gap:26px !important;
  max-width:none !important;
  width:100% !important;
  margin:24px 0 4px !important;
  padding:0 0 14px !important;
  border-bottom:1px solid rgba(255,255,255,.10) !important;
  overflow-x:auto !important;
  overflow-y:hidden !important;
  -webkit-overflow-scrolling:touch !important;
  scrollbar-width:none !important;
  overscroll-behavior-x:contain !important;
  touch-action:pan-x !important;
}.gm-helpTabs::-webkit-scrollbar{display:none !important;}.gm-helpTab{
  position:relative !important;
  flex:0 0 auto !important;
  min-height:0 !important;
  padding:0 0 13px !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  color:rgba(244,240,255,.52) !important;
  font-size:13px !important;
  font-weight:950 !important;
  letter-spacing:.08em !important;
  text-transform:uppercase !important;
  cursor:pointer !important;
  -webkit-tap-highlight-color:transparent !important;
}.gm-helpTab::after{
  content:"" !important;
  position:absolute !important;
  left:0 !important;
  right:0 !important;
  bottom:-15px !important;
  height:3px !important;
  border-radius:999px !important;
  background:linear-gradient(90deg, #818cf8, #60a5fa) !important;
  opacity:0 !important;
  transform:scaleX(.42) !important;
  transform-origin:left center !important;
  transition:opacity .2s ease, transform .2s ease !important;
  box-shadow:0 0 24px rgba(129,140,248,.38) !important;
}.gm-helpTab.is-active,
.gm-helpTab:hover{
  background:transparent !important;
  border-color:transparent !important;
  box-shadow:none !important;
  color:#fff !important;
}.gm-helpTab.is-active::after{
  opacity:1 !important;
  transform:scaleX(1) !important;
}.gm-faqitem[data-faq-cat]{display:none;}.gm-faqitem[data-faq-cat="general"]{display:block;}.gm-faqPremium{
  overflow:hidden !important;
}.gm-faqitem{
  will-change:transform !important;
  transform:translateZ(0) !important;
}.gm-faqbtn,
.gm-btn,
.gm-helpTab{
  -webkit-appearance:none !important;
  appearance:none !important;
}@media(max-width:1100px){.gm-helpTabs{justify-content:flex-start !important; max-width:720px !important; margin-left:auto !important; margin-right:auto !important;}}@media(max-width:640px){.gm-supportfaq{padding:70px 0 74px !important;}.gm-helpCenterShell{gap:22px !important; padding:0 16px !important;}.gm-helpCenterTitle{font-size:clamp(42px, 13vw, 58px) !important; line-height:.98 !important;}.gm-helpCenterLead{font-size:15.5px !important; line-height:1.65 !important;}.gm-helpCenterActions .gm-btn{min-height:54px !important; border-radius:16px !important;}.gm-helpTabs{
    gap:22px !important;
    width:calc(100vw - 32px) !important;
    max-width:none !important;
    margin-top:10px !important;
    padding-bottom:12px !important;
  }.gm-helpTab{font-size:11.5px !important; letter-spacing:.07em !important; padding-bottom:12px !important;}.gm-helpTab::after{bottom:-13px !important; height:2px !important;}.gm-faqPremium{border-radius:22px !important; padding:8px !important;}.gm-faqitem{border-radius:17px !important; margin-bottom:9px !important;}.gm-faqbtn{min-height:60px !important; padding:16px 14px !important; font-size:14.5px !important;}.gm-faqbtn .chev{width:32px !important; height:32px !important; flex-basis:32px !important; border-radius:11px !important;}.gm-faqinner{padding:0 14px 18px !important; font-size:14px !important; line-height:1.65 !important;}}body.landing,
body.landing.gamingmarket{
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(59,130,246,.18), rgba(0,0,0,0) 60%),
    radial-gradient(900px 600px at 80% 18%, rgba(56,189,248,.10), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,189,248,.08), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #04030e 0%, #07051a 60%, #04030e 100%) !important;
  background-color:#04030e !important;
}.gm-section, .gm-marketSection, .gm-reviewsSection, .lbHybridSteps, .lbHybridLive, .lbHybridBoosters, .gm-supportfaq, .gm-heroSearch{
  background:transparent !important;
  background-color:transparent !important;
  border-top:0 !important;
  border-bottom:0 !important;
}.lbHybridLive::before, .lbHybridBoosters::before, .gm-marketSection::before, .gm-reviewsSection::before, .lbHybridSteps::before{
  opacity:0 !important;
  background:none !important;
}.gm-helpCenterShell{
  grid-template-columns:minmax(300px, .72fr) minmax(0, 1.28fr) !important;
  column-gap:48px !important;
  row-gap:16px !important;
}.gm-helpCenterShell > .gm-sectionTag{grid-column:1 !important; grid-row:1 !important;}.gm-helpCenterShell > .gm-helpCenterTitle{grid-column:1 !important; grid-row:2 !important;}.gm-helpCenterShell > .gm-helpCenterLead{grid-column:1 !important; grid-row:3 !important;}.gm-helpCenterShell > .gm-helpCenterActions{grid-column:1 !important; grid-row:4 !important;}.gm-helpCenterShell > .gm-helpTabs{
  grid-column:2 !important;
  grid-row:1 !important;
  align-self:end !important;
  justify-self:stretch !important;
}.gm-helpCenterShell > .gm-faqPremium{
  grid-column:2 !important;
  grid-row:2 / span 5 !important;
}.gm-helpTabs{
  margin:0 0 12px !important;
  padding:0 0 12px !important;
  justify-content:flex-end !important;
  gap:28px !important;
  border-bottom:1px solid rgba(255,255,255,.10) !important;
  max-width:none !important;
  width:100% !important;
}.gm-helpTab{
  white-space:nowrap !important;
}.gm-faqPremium{
  margin-top:0 !important;
}@media(max-width:1100px){.gm-helpCenterShell{
    grid-template-columns:1fr !important;
    row-gap:18px !important;
  }.gm-helpCenterShell > .gm-sectionTag,
  .gm-helpCenterShell > .gm-helpCenterTitle,
  .gm-helpCenterShell > .gm-helpCenterLead,
  .gm-helpCenterShell > .gm-helpCenterActions,
  .gm-helpCenterShell > .gm-helpTabs,
  .gm-helpCenterShell > .gm-faqPremium{
    grid-column:1 !important;
    grid-row:auto !important;
  }.gm-helpTabs{
    justify-content:center !important;
    margin-top:8px !important;
  }}@media(max-width:640px){body.landing,
  body.landing.gamingmarket{background-attachment:scroll !important;}.gm-helpTabs{
    justify-content:flex-start !important;
    width:100% !important;
    gap:22px !important;
    overflow-x:auto !important;
    -webkit-overflow-scrolling:touch !important;
    touch-action:pan-x !important;
  }.gm-supportfaq, .lbHybridSteps, .lbHybridLive, .lbHybridBoosters, .gm-reviewsSection{
    overflow:hidden !important;
  }}html,
body.landing,
body.landing.gamingmarket{
  width:100% !important;
  max-width:100% !important;
  overflow-x:hidden !important;
  background-color:#04030e !important;
}@supports (overflow-x:clip){html,
  body.landing,
  body.landing.gamingmarket{overflow-x:clip !important;}}body.landing,
body.landing.gamingmarket{
  background:
    radial-gradient(1200px 720px at 18% 6%, rgba(99,102,241,.16), transparent 62%),
    radial-gradient(1000px 620px at 86% 20%, rgba(56,189,248,.10), transparent 58%),
    radial-gradient(980px 720px at 48% 88%, rgba(96,165,250,.06), transparent 64%),
    linear-gradient(180deg, #04030e 0%, #060418 48%, #04030e 100%) !important;
  background-attachment:scroll !important;
}body.landing.gamingmarket main,
body.landing.gamingmarket .site-main,
body.landing.gamingmarket .page-content,
body.landing.gamingmarket .content,
body.landing.gamingmarket .gamingmarket{
  background:transparent !important;
  background-color:transparent !important;
}.gm-section::before, .gm-section::after, .gm-marketSection::before, .gm-marketSection::after, .gm-reviewsSection::before, .gm-reviewsSection::after, .lbHybridSteps::before, .lbHybridSteps::after, .lbHybridLive::before, .lbHybridLive::after, .lbHybridBoosters::before, .lbHybridBoosters::after, .gm-supportfaq::before, .gm-supportfaq::after, .gm-heroSearch::before, .gm-heroSearch::after{
  background:transparent !important;
  background-color:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}.gm-supportfaq{
  padding:112px 0 108px !important;
  position:relative !important;
  isolation:isolate !important;
}.gm-supportfaq .gm-wrap{
  max-width:1280px !important;
  padding:0 28px !important;
}.gm-helpCenterShell{
  display:grid !important;
  grid-template-columns:minmax(300px, 390px) minmax(0, 1fr) !important;
  column-gap:72px !important;
  row-gap:0 !important;
  align-items:start !important;
  width:100% !important;
  padding:0 !important;
  overflow:visible !important;
}.gm-helpCenterShell > .gm-sectionTag{
  grid-column:1 !important;
  grid-row:1 !important;
  margin:0 0 34px !important;
}.gm-helpCenterShell > .gm-helpCenterTitle{
  grid-column:1 !important;
  grid-row:2 !important;
  margin:0 !important;
  font-size:clamp(58px, 5.1vw, 82px) !important;
  line-height:.94 !important;
  letter-spacing:-.06em !important;
}.gm-helpCenterShell > .gm-helpCenterLead{
  grid-column:1 !important;
  grid-row:3 !important;
  max-width:32ch !important;
  margin:30px 0 0 !important;
  color:rgba(244,240,255,.66) !important;
  font-size:18px !important;
  line-height:1.7 !important;
}.gm-helpCenterShell > .gm-helpCenterActions{
  grid-column:1 !important;
  grid-row:4 !important;
  display:grid !important;
  grid-template-columns:1fr !important;
  gap:12px !important;
  width:100% !important;
  max-width:330px !important;
  margin:42px 0 0 !important;
}.gm-helpCenterActions .gm-btn{
  width:100% !important;
  min-height:56px !important;
  border-radius:18px !important;
}.gm-helpCenterShell > .gm-helpTabs{
  grid-column:2 !important;
  grid-row:1 !important;
  justify-self:start !important;
  align-self:start !important;
  width:100% !important;
  max-width:720px !important;
  margin:0 0 28px !important;
  padding:0 0 14px !important;
  display:flex !important;
  justify-content:flex-start !important;
  align-items:flex-end !important;
  gap:30px !important;
  border-bottom:1px solid rgba(255,255,255,.10) !important;
  overflow-x:auto !important;
  overflow-y:hidden !important;
  scrollbar-width:none !important;
  -webkit-overflow-scrolling:touch !important;
  overscroll-behavior-x:contain !important;
}.gm-helpTabs::-webkit-scrollbar{display:none !important;}.gm-helpTab{
  flex:0 0 auto !important;
  padding:0 0 13px !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  color:rgba(244,240,255,.50) !important;
  font-size:13px !important;
  font-weight:950 !important;
  letter-spacing:.09em !important;
  text-transform:uppercase !important;
  white-space:nowrap !important;
}.gm-helpTab::after{
  bottom:-15px !important;
  height:3px !important;
  background:linear-gradient(90deg,#6982ff,#60a5fa) !important;
}.gm-helpTab.is-active,
.gm-helpTab:hover{color:#fff !important;}.gm-helpCenterShell > .gm-faqPremium{
  grid-column:2 !important;
  grid-row:2 / span 5 !important;
  width:100% !important;
  max-width:720px !important;
  margin:0 !important;
  padding:12px !important;
  border-radius:28px !important;
  background:linear-gradient(180deg, rgba(24,23,45,.78), rgba(10,8,24,.58)) !important;
  border:1px solid rgba(139,92,246,.26) !important;
  box-shadow:0 28px 90px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.06) !important;
  backdrop-filter:blur(10px) !important;
  -webkit-backdrop-filter:blur(10px) !important;
}.gm-faqitem{
  margin:0 0 12px !important;
  border:1px solid rgba(255,255,255,.10) !important;
  border-radius:20px !important;
  background:linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.025)) !important;
  overflow:hidden !important;
}.gm-faqitem:last-child{margin-bottom:0 !important;}.gm-faqitem.open{
  border-color:rgba(129,140,248,.38) !important;
  background:linear-gradient(180deg, rgba(38,39,68,.72), rgba(16,14,34,.62)) !important;
}.gm-faqbtn{
  min-height:72px !important;
  padding:20px 22px !important;
  font-size:17px !important;
  line-height:1.25 !important;
}.gm-faqbtn .chev{
  width:36px !important;
  height:36px !important;
  flex:0 0 36px !important;
  border-radius:13px !important;
}.gm-faqinner{
  padding:0 22px 24px !important;
  color:rgba(244,240,255,.66) !important;
  font-size:15.5px !important;
  line-height:1.75 !important;
}@media(max-width:1180px){.gm-helpCenterShell{
    grid-template-columns:1fr !important;
    max-width:820px !important;
    margin:0 auto !important;
  }.gm-helpCenterShell > .gm-sectionTag,
  .gm-helpCenterShell > .gm-helpCenterTitle,
  .gm-helpCenterShell > .gm-helpCenterLead,
  .gm-helpCenterShell > .gm-helpCenterActions,
  .gm-helpCenterShell > .gm-helpTabs,
  .gm-helpCenterShell > .gm-faqPremium{
    grid-column:1 !important;
    grid-row:auto !important;
  }.gm-helpCenterShell > .gm-helpTabs{
    margin-top:42px !important;
    max-width:100% !important;
  }.gm-helpCenterShell > .gm-faqPremium{
    max-width:100% !important;
  }}@media(max-width:768px){body.landing,
  body.landing.gamingmarket{
    background-attachment:scroll !important;
    -webkit-text-size-adjust:100% !important;
  }.gm-wrap,
  .gm-wrapWide,
  .gamingmarket .gm-wrap,
  .gamingmarket .gm-wrapWide,
  .gm-supportfaq .gm-wrap{
    width:100% !important;
    max-width:100% !important;
    padding-left:16px !important;
    padding-right:16px !important;
    box-sizing:border-box !important;
  }.gm-headRow,
  .gamingmarket .gm-headRow{
    display:flex !important;
    flex-direction:column !important;
    align-items:flex-start !important;
    gap:16px !important;
    text-align:left !important;
  }.gm-h2,
  .gamingmarket .gm-h2{
    font-size:clamp(30px, 9vw, 42px) !important;
    line-height:1.08 !important;
  }.gm-sub,
  .gamingmarket .gm-sub{
    font-size:15px !important;
    line-height:1.65 !important;
  }.gm-grid,
  .gm-grid3,
  .gamingmarket .gm-grid,
  .gamingmarket .gm-grid3,
  .lbStepsGrid,
  .lbLiveGrid,
  .lbBoostersGrid{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:14px !important;
  }.gm-helpCenterShell{
    display:block !important;
    max-width:100% !important;
  }.gm-helpCenterShell > .gm-sectionTag{margin-bottom:22px !important;}.gm-helpCenterTitle{
    font-size:clamp(46px, 15vw, 64px) !important;
    line-height:.96 !important;
  }.gm-helpCenterLead{
    max-width:100% !important;
    margin-top:22px !important;
    font-size:15.5px !important;
    line-height:1.65 !important;
  }.gm-helpCenterActions{
    max-width:none !important;
    margin-top:28px !important;
  }.gm-helpTabs{
    width:100% !important;
    max-width:100% !important;
    margin:34px 0 18px !important;
    padding-bottom:12px !important;
    gap:24px !important;
    justify-content:flex-start !important;
    overflow-x:auto !important;
    scroll-snap-type:x proximity !important;
  }.gm-helpTab{
    font-size:12px !important;
    letter-spacing:.075em !important;
    scroll-snap-align:start !important;
  }.gm-faqPremium{
    width:100% !important;
    max-width:100% !important;
    padding:8px !important;
    border-radius:22px !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
  }.gm-faqitem{
    border-radius:16px !important;
    margin-bottom:9px !important;
  }.gm-faqbtn{
    min-height:58px !important;
    padding:15px 12px 15px 14px !important;
    font-size:14.5px !important;
    gap:10px !important;
  }.gm-faqbtn span:first-child{
    min-width:0 !important;
    overflow-wrap:anywhere !important;
  }.gm-faqbtn .chev{
    width:32px !important;
    height:32px !important;
    flex:0 0 32px !important;
    border-radius:11px !important;
  }.gm-faqinner{
    padding:0 14px 18px !important;
    font-size:14px !important;
    line-height:1.65 !important;
  }}@media(max-width:420px){.gm-wrap,
  .gm-wrapWide,
  .gamingmarket .gm-wrap,
  .gamingmarket .gm-wrapWide,
  .gm-supportfaq .gm-wrap{
    padding-left:12px !important;
    padding-right:12px !important;
  }.gm-helpTabs{gap:18px !important;}.gm-helpTab{font-size:11px !important;}.gm-faqbtn{font-size:13.5px !important;}}@media(max-width:768px){html, body.landing, body.landing.gamingmarket{
    width:100% !important;
    max-width:100% !important;
    overflow-x:hidden !important;
    background-attachment:scroll !important;
  }body.landing.gamingmarket{
    background:
      radial-gradient(680px 520px at 18% 4%, rgba(99,102,241,.18), transparent 62%),
      radial-gradient(620px 520px at 96% 22%, rgba(56,189,248,.10), transparent 60%),
      linear-gradient(180deg, #04030e 0%, #060418 52%, #04030e 100%) !important;
  }.gm-wrap,
  .gm-wrapWide,
  .gamingmarket .gm-wrap,
  .gamingmarket .gm-wrapWide,
  .gm-supportfaq .gm-wrap{
    width:100% !important;
    max-width:100% !important;
    padding-left:16px !important;
    padding-right:16px !important;
    box-sizing:border-box !important;
  }.gm-marketHeadGrid{
    display:block !important;
    margin-bottom:18px !important;
  }.gm-marketTitle{
    font-size:clamp(32px, 10vw, 42px) !important;
    line-height:1.06 !important;
    max-width:10.5em !important;
  }.gm-marketSub{
    max-width:31ch !important;
    font-size:15px !important;
    line-height:1.58 !important;
    margin-top:12px !important;
  }.gm-marketBtn{
    display:none !important;
  }.gm-serviceTiles{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:10px !important;
    margin-top:18px !important;
  }.gm-serviceTile{
    min-height:78px !important;
    padding:14px !important;
    border-radius:22px !important;
    display:grid !important;
    grid-template-columns:44px 1fr !important;
    align-items:center !important;
    gap:12px !important;
  }.gm-tileIcon{
    width:44px !important;
    height:44px !important;
    border-radius:15px !important;
  }.gm-serviceTile h3{font-size:15px !important; margin:0 0 4px !important;}.gm-serviceTile p{font-size:12px !important; line-height:1.4 !important; margin:0 !important;}.gm-serviceTile > span{display:none !important;}.gm-supportfaq{padding-top:52px !important; padding-bottom:52px !important;}.gm-helpCenterShell{
    display:block !important;
    max-width:100% !important;
    padding:0 !important;
  }.gm-helpCenterTag{margin-bottom:18px !important;}.gm-helpCenterTitle{
    font-size:clamp(42px, 13vw, 58px) !important;
    line-height:.98 !important;
    letter-spacing:-.055em !important;
    text-align:left !important;
  }.gm-helpCenterLead{
    max-width:31ch !important;
    margin:18px 0 0 !important;
    font-size:15px !important;
    line-height:1.58 !important;
    text-align:left !important;
  }.gm-helpCenterActions{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:10px !important;
    max-width:none !important;
    width:100% !important;
    margin:22px 0 0 !important;
  }.gm-helpCenterActions .gm-btn{
    min-height:48px !important;
    border-radius:16px !important;
  }.gm-helpTabs{
    display:grid !important;
    grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
    gap:0 !important;
    width:100% !important;
    max-width:100% !important;
    margin:26px 0 14px !important;
    padding:0 !important;
    border:1px solid rgba(255,255,255,.10) !important;
    border-radius:18px !important;
    overflow:hidden !important;
    background:linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.020)) !important;
  }.gm-helpTab{
    min-height:44px !important;
    padding:0 8px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    border-radius:0 !important;
    border:0 !important;
    border-right:1px solid rgba(255,255,255,.08) !important;
    background:transparent !important;
    color:rgba(244,240,255,.58) !important;
    font-size:10.5px !important;
    letter-spacing:.055em !important;
    text-align:center !important;
    white-space:nowrap !important;
  }.gm-helpTab:nth-child(3n){border-right:0 !important;}.gm-helpTab:nth-child(n/**/+4){border-top:1px solid rgba(255,255,255,.08) !important;}.gm-helpTab::after{display:none !important;}.gm-helpTab.is-active{
    background:linear-gradient(135deg, rgba(99,102,241,.32), rgba(96,165,250,.14)) !important;
    color:#fff !important;
  }.gm-faqPremium{
    padding:8px !important;
    border-radius:22px !important;
    background:linear-gradient(180deg, rgba(20,20,42,.84), rgba(8,8,22,.66)) !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
  }.gm-faqitem{border-radius:16px !important; margin-bottom:8px !important;}.gm-faqbtn{
    min-height:56px !important;
    padding:14px 12px 14px 14px !important;
    font-size:13.8px !important;
    line-height:1.28 !important;
  }.gm-faqbtn .chev{
    width:30px !important;
    height:30px !important;
    flex:0 0 30px !important;
    border-radius:10px !important;
  }.gm-faqinner{
    padding:0 14px 16px !important;
    font-size:13.5px !important;
    line-height:1.6 !important;
  }}@media(max-width:390px){.gm-helpTab{font-size:9.8px !important; padding-left:4px !important; padding-right:4px !important;}.gm-marketTitle{font-size:clamp(30px, 10vw, 38px) !important;}}@media (max-width:768px){.gm-helpTabs{
    display:flex !important;
    flex-wrap:wrap !important;
    align-items:center !important;
    justify-content:center !important;
    gap:10px 8px !important;
    width:100% !important;
    max-width:340px !important;
    margin:28px auto 20px !important;
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    background:transparent !important;
    overflow:visible !important;
    box-shadow:none !important;
  }.gm-helpTab{
    flex:0 0 auto !important;
    min-width:0 !important;
    min-height:38px !important;
    padding:0 18px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    border-radius:999px !important;
    border:1px solid rgba(255,255,255,.13) !important;
    border-right:1px solid rgba(255,255,255,.13) !important;
    background:linear-gradient(180deg, rgba(255,255,255,.075), rgba(255,255,255,.030)) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.07), 0 10px 28px rgba(0,0,0,.18) !important;
    color:rgba(244,240,255,.72) !important;
    font-size:12px !important;
    font-weight:950 !important;
    letter-spacing:.01em !important;
    text-transform:none !important;
    text-align:center !important;
    white-space:nowrap !important;
  }.gm-helpTab:nth-child(3n),
  .gm-helpTab:nth-child(n/**/+4){
    border-right:1px solid rgba(255,255,255,.13) !important;
    border-top:1px solid rgba(255,255,255,.13) !important;
  }.gm-helpTab::after{
    display:none !important;
  }.gm-helpTab.is-active,
  .gm-helpTab:hover{
    background:linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
    border-color:rgba(96,165,250,.55) !important;
    color:#fff !important;
    box-shadow:0 14px 36px rgba(96,165,250,.22), inset 0 1px 0 rgba(255,255,255,.18) !important;
  }.gm-helpCenterActions{
    max-width:340px !important;
    margin-left:auto !important;
    margin-right:auto !important;
  }.gm-helpCenterTitle,
  .gm-helpCenterLead,
  .gm-helpCenterTag{
    text-align:center !important;
    margin-left:auto !important;
    margin-right:auto !important;
    justify-self:center !important;
  }.gm-supportfaq{
    padding-top:50px !important;
    padding-bottom:54px !important;
  }.gm-faqPremium{
    margin-top:0 !important;
  }}@media (max-width:768px){html,
  body.landing,
  body.landing.gamingmarket{
    width:100% !important;
    max-width:100% !important;
    overflow-x:hidden !important;
  }}.gm-ctaBand.lbCtaNew {
  background:
    linear-gradient(90deg, rgba(4,7,18,.97) 0%, rgba(7,12,32,.86) 38%, rgba(8,14,38,.52) 68%, rgba(4,7,18,.84) 100%),
    linear-gradient(180deg, rgba(4,7,18,.98) 0%, rgba(4,7,18,.20) 46%, rgba(4,7,18,.98) 100%),
    url("/public/assets/website/images/landing/cta-background-footer.png") center center / cover no-repeat !important;
}.lbCtaNew::before {
  background:
    radial-gradient(760px 420px at 16% 30%, rgba(79,110,247,.24), transparent 64%),
    radial-gradient(720px 420px at 86% 42%, rgba(96,165,250,.16), transparent 66%) !important;
}.lbCtaNew::after {
  background:linear-gradient(90deg, rgba(4,7,18,.84) 0%, rgba(4,7,18,.44) 52%, rgba(4,7,18,.18) 100%) !important;
}.lbCtaNewBadge {
  background:rgba(79,110,247,.13) !important;
  border-color:rgba(96,165,250,.28) !important;
  color:#93c5fd !important;
}.lbCtaNewBadge span {
  background:#60a5fa !important;
  box-shadow:0 0 18px rgba(96,165,250,.90) !important;
}.lbCtaNewTitle strong {
  background:linear-gradient(95deg, #7dd3fc 0%, #60a5fa 38%, #4f6ef7 72%, #93c5fd 100%) !important;
  -webkit-background-clip:text !important;
  background-clip:text !important;
}.lbCtaNewBtnPrimary {
  background:linear-gradient(135deg, #4f6ef7 0%, #4567f4 52%, #3f5fe9 100%) !important;
  border-color:rgba(147,197,253,.42) !important;
  box-shadow:0 18px 56px rgba(79,110,247,.38), inset 0 1px 0 rgba(255,255,255,.17) !important;
}.lbCtaNewBtnPrimary:hover {
  box-shadow:0 24px 70px rgba(79,110,247,.48), inset 0 1px 0 rgba(255,255,255,.20) !important;
}.lbCtaNewBtnGhost {
  border-color:rgba(96,165,250,.18) !important;
}.lbCtaNewMetaItem i,
.lbCtaNewMini i {
  color:#93c5fd !important;
}.lbCtaNewPanel {
  border-color:rgba(96,165,250,.22) !important;
}.lbCtaNewPanel::before {
  background:radial-gradient(330px 160px at 20% 0%, rgba(79,110,247,.22), transparent 68%) !important;
}.gm-helpTabs {
  width:100% !important;
  max-width:760px !important;
  margin:26px auto 28px !important;
  padding:0 0 14px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  flex-wrap:wrap !important;
  gap:14px 24px !important;
  border-bottom:1px solid rgba(129,140,248,.18) !important;
  overflow:visible !important;
}.gm-helpTab {
  min-width:92px !important;
  min-height:42px !important;
  padding:0 22px !important;
  border-radius:999px !important;
  border:1px solid rgba(129,140,248,.26) !important;
  background:linear-gradient(180deg, rgba(22,27,52,.88), rgba(14,18,38,.74)) !important;
  color:rgba(226,232,255,.68) !important;
  font-size:13px !important;
  font-weight:900 !important;
  letter-spacing:0 !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.07) !important;
}.gm-helpTab:hover {
  color:#fff !important;
  border-color:rgba(147,197,253,.44) !important;
  background:linear-gradient(180deg, rgba(36,47,92,.92), rgba(20,26,58,.82)) !important;
}.gm-helpTab.is-active {
  color:#fff !important;
  border-color:rgba(147,197,253,.62) !important;
  background:linear-gradient(135deg, #5b7cff 0%, #4f6ef7 100%) !important;
  box-shadow:0 14px 34px rgba(79,110,247,.32), inset 0 1px 0 rgba(255,255,255,.18) !important;
}@media(max-width:640px) {.gm-ctaBand.lbCtaNew {
    background:
      linear-gradient(180deg, rgba(8,13,38,.72) 0%, rgba(8,14,42,.48) 42%, rgba(5,7,22,.94) 100%),
      linear-gradient(90deg, rgba(18,42,96,.56) 0%, rgba(10,18,52,.48) 45%, rgba(5,7,22,.76) 100%),
      url("/public/assets/website/images/landing/cta-background-footer.png") center center / cover no-repeat !important;
  }.lbCtaNew::before {
    background:radial-gradient(420px 300px at 22% 18%, rgba(79,110,247,.26), transparent 70%) !important;
  }.lbCtaNewBadge {
    color:#7dd3fc !important;
  }.lbCtaNewBadge span {
    background:#60a5fa !important;
    box-shadow:0 0 18px rgba(96,165,250,.95) !important;
  }.lbCtaNewMetaItem i {
    color:#60a5fa !important;
  }.gm-helpTabs {
    width:100% !important;
    max-width:100% !important;
    margin:24px 0 24px !important;
    padding:0 0 13px !important;
    justify-content:flex-start !important;
    flex-wrap:nowrap !important;
    gap:12px !important;
    overflow-x:auto !important;
    overflow-y:hidden !important;
    -webkit-overflow-scrolling:touch !important;
    scrollbar-width:none !important;
  }.gm-helpTabs::-webkit-scrollbar { display:none !important; }.gm-helpTab {
    min-width:auto !important;
    height:38px !important;
    min-height:38px !important;
    padding:0 18px !important;
    font-size:12px !important;
    flex:0 0 auto !important;
  }}.gm-supportfaq.gm-faqRebuild{
  padding:clamp(84px, 9vw, 130px) 0 !important;
  background:
    radial-gradient(900px 460px at 18% 0%, rgba(79,110,247,.18), transparent 65%),
    radial-gradient(760px 420px at 84% 34%, rgba(59,130,246,.13), transparent 62%),
    linear-gradient(180deg, rgba(4,7,18,.08), rgba(4,7,18,.48)) !important;
  overflow:hidden;
}.gm-faqRebuild .gm-wrap{
  max-width:1240px !important;
}.gm-faqRebuildHead{
  max-width:860px;
  margin:0 auto 34px;
  text-align:center;
}.gm-faqRebuild .gm-helpCenterTag{
  justify-content:center !important;
  margin-bottom:16px !important;
}.gm-faqRebuild .gm-helpCenterTitle{
  margin:0 !important;
  font-size:clamp(42px, 5vw, 72px) !important;
  line-height:1.02 !important;
  letter-spacing:-.055em !important;
  text-shadow:0 18px 54px rgba(0,0,0,.44) !important;
}.gm-faqRebuild .gm-helpCenterLead{
  max-width:690px !important;
  margin:18px auto 0 !important;
  color:rgba(226,234,255,.66) !important;
  font-size:clamp(16px, 1.35vw, 19px) !important;
  line-height:1.75 !important;
}.gm-faqRebuild .gm-helpTabs{
  display:flex !important;
  justify-content:center !important;
  align-items:center !important;
  flex-wrap:wrap !important;
  gap:10px !important;
  margin:28px auto 0 !important;
  padding:8px !important;
  width:fit-content !important;
  max-width:100% !important;
  border:1px solid rgba(96,165,250,.16) !important;
  border-radius:999px !important;
  background:rgba(7,12,30,.56) !important;
  box-shadow:0 18px 54px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.06) !important;
}.gm-faqRebuild .gm-helpTab{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  min-height:42px !important;
  padding:0 18px !important;
  border-radius:999px !important;
  border:1px solid transparent !important;
  background:transparent !important;
  color:rgba(226,234,255,.62) !important;
  font-size:13px !important;
  font-weight:900 !important;
  line-height:1 !important;
  box-shadow:none !important;
}.gm-faqRebuild .gm-helpTab i{
  font-size:12px;
  color:rgba(147,197,253,.72);
}.gm-faqRebuild .gm-helpTab:hover{
  color:#fff !important;
  background:rgba(255,255,255,.06) !important;
  border-color:rgba(147,197,253,.18) !important;
}.gm-faqRebuild .gm-helpTab.is-active{
  color:#fff !important;
  background:linear-gradient(135deg,#4f6ef7,#3b82f6) !important;
  border-color:rgba(147,197,253,.44) !important;
  box-shadow:0 14px 34px rgba(79,110,247,.32), inset 0 1px 0 rgba(255,255,255,.18) !important;
}.gm-faqRebuild .gm-helpTab.is-active i{
  color:#fff;
}.gm-faqRebuildGrid{
  display:grid;
  grid-template-columns:minmax(300px, .82fr) minmax(0, 1.18fr);
  gap:22px;
  align-items:start;
}.gm-faqContactCard,
.gm-faqRebuild .gm-faqPremium{
  position:relative !important;
  border-radius:30px !important;
  background:linear-gradient(160deg, rgba(13,20,44,.92), rgba(6,10,25,.90)) !important;
  border:1px solid rgba(96,165,250,.20) !important;
  box-shadow:0 28px 84px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.07) !important;
  overflow:hidden !important;
}.gm-faqContactCard{
  padding:30px !important;
  min-height:520px;
  display:flex;
  flex-direction:column;
}.gm-faqContactGlow{
  position:absolute;
  inset:-1px;
  pointer-events:none;
  background:
    radial-gradient(360px 220px at 18% 10%, rgba(79,110,247,.24), transparent 64%),
    radial-gradient(360px 260px at 100% 84%, rgba(56,189,248,.14), transparent 66%);
}.gm-faqContactIcon,
.gm-faqContactCard h3,
.gm-faqContactCard p,
.gm-faqContactCard .gm-helpCenterActions,
.gm-faqSupportStats{
  position:relative;
  z-index:1;
}.gm-faqContactIcon{
  width:58px;
  height:58px;
  border-radius:18px;
  display:grid;
  place-items:center;
  color:#fff;
  background:linear-gradient(135deg,#4f6ef7,#3b82f6);
  border:1px solid rgba(147,197,253,.42);
  box-shadow:0 18px 44px rgba(79,110,247,.28);
  margin-bottom:22px;
}.gm-faqContactCard h3{
  margin:0;
  font-size:clamp(28px, 2.8vw, 42px);
  line-height:1.06;
  letter-spacing:-.04em;
}.gm-faqContactCard p{
  margin:16px 0 0;
  color:rgba(226,234,255,.62);
  line-height:1.72;
  font-size:16px;
}.gm-faqRebuild .gm-helpCenterActions{
  margin:28px 0 0 !important;
  display:grid !important;
  gap:12px !important;
  justify-content:stretch !important;
}.gm-faqRebuild .gm-helpCenterActions .gm-btn{
  width:100% !important;
  min-height:54px !important;
  border-radius:17px !important;
  justify-content:center !important;
  font-weight:900 !important;
}.gm-faqRebuild .gm-helpCenterActions .gm-btnPrimary{
  background:linear-gradient(135deg,#4f6ef7,#3b82f6) !important;
  border-color:rgba(147,197,253,.42) !important;
  box-shadow:0 16px 38px rgba(79,110,247,.32), inset 0 1px 0 rgba(255,255,255,.16) !important;
}.gm-faqRebuild .gm-helpCenterActions .gm-btnGhost{
  background:rgba(255,255,255,.045) !important;
  border-color:rgba(147,197,253,.18) !important;
  color:rgba(244,248,255,.86) !important;
}.gm-faqSupportStats{
  margin-top:auto;
  padding-top:28px;
  display:grid;
  grid-template-columns:1fr;
  gap:10px;
}.gm-faqSupportStats div{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:14px 16px;
  border-radius:16px;
  background:rgba(255,255,255,.045);
  border:1px solid rgba(147,197,253,.14);
}.gm-faqSupportStats strong{
  font-size:16px;
  color:#fff;
}.gm-faqSupportStats span{
  color:rgba(226,234,255,.56);
  font-size:13px;
  font-weight:800;
}.gm-faqRebuild .gm-faqPremium{
  padding:10px !important;
  text-align:left !important;
}.gm-faqRebuild .gm-faqPremium::before,
.gm-faqRebuild .gm-faqPremium::after,
.gm-faqRebuild .gm-faqitem::before,
.gm-faqRebuild .gm-faqitem::after{
  display:none !important;
  content:none !important;
}.gm-faqRebuild .gm-faqitem{
  margin:0 0 10px !important;
  border:1px solid rgba(147,197,253,.14) !important;
  border-radius:20px !important;
  background:rgba(255,255,255,.04) !important;
  box-shadow:none !important;
  overflow:hidden !important;
}.gm-faqRebuild .gm-faqitem:last-child{
  margin-bottom:0 !important;
}.gm-faqRebuild .gm-faqitem.open{
  background:linear-gradient(180deg, rgba(79,110,247,.12), rgba(255,255,255,.045)) !important;
  border-color:rgba(96,165,250,.32) !important;
  box-shadow:0 16px 42px rgba(0,0,0,.22) !important;
}.gm-faqRebuild .gm-faqbtn{
  min-height:64px !important;
  padding:20px 20px !important;
  border:0 !important;
  background:transparent !important;
  color:#fff !important;
  font-size:16px !important;
  font-weight:900 !important;
  letter-spacing:-.01em !important;
}.gm-faqRebuild .gm-faqbtn:hover{
  background:rgba(255,255,255,.025) !important;
}.gm-faqRebuild .gm-faqbtn .chev{
  width:34px;
  height:34px;
  border-radius:12px;
  display:grid;
  place-items:center;
  flex:0 0 auto;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(147,197,253,.14);
}.gm-faqRebuild .gm-faqbtn .chev i{
  color:rgba(226,234,255,.66) !important;
  font-size:12px !important;
}.gm-faqRebuild .gm-faqinner{
  padding:0 20px 22px !important;
  color:rgba(226,234,255,.66) !important;
  font-size:15px !important;
  line-height:1.74 !important;
  max-width:760px;
}@media(max-width:980px){.gm-faqRebuildGrid{grid-template-columns:1fr;}.gm-faqContactCard{min-height:auto;}.gm-faqSupportStats{grid-template-columns:repeat(3, 1fr);}}@media(max-width:640px){.gm-supportfaq.gm-faqRebuild{padding:72px 0 !important;}.gm-faqRebuild .gm-wrap{padding:0 16px !important;}.gm-faqRebuild .gm-helpTabs{width:100% !important; border-radius:24px !important; padding:8px !important; display:grid !important; grid-template-columns:repeat(2, minmax(0,1fr)) !important;}.gm-faqRebuild .gm-helpTab{width:100% !important; padding:0 12px !important;}.gm-faqRebuild .gm-helpTab:first-child{grid-column:1 / -1;}.gm-faqContactCard{padding:24px !important; border-radius:26px !important;}.gm-faqSupportStats{grid-template-columns:1fr;}.gm-faqRebuild .gm-faqPremium{border-radius:26px !important;}.gm-faqRebuild .gm-faqbtn{font-size:15px !important; padding:18px 16px !important;}.gm-faqRebuild .gm-faqinner{padding:0 16px 20px !important;}}.gm-faqRebuildHead{
  text-align:center !important;
  max-width:860px !important;
  margin:0 auto 38px !important;
}.gm-faqRebuild .gm-helpCenterTag{
  display:flex !important;
  justify-content:center !important;
  align-items:center !important;
  width:fit-content !important;
  margin:0 auto 16px !important;
  text-align:center !important;
}.gm-faqRebuild .gm-helpCenterTag span{
  display:none !important;
}.gm-faqRebuild .gm-helpCenterTitle,
.gm-faqRebuild .gm-helpCenterLead{
  text-align:center !important;
  margin-left:auto !important;
  margin-right:auto !important;
}.gm-faqSupportStats{
  margin-top:auto !important;
  padding-top:28px !important;
  display:grid !important;
  grid-template-columns:1fr !important;
  gap:12px !important;
}.gm-faqSupportStat{
  display:flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  gap:14px !important;
  padding:16px !important;
  border-radius:18px !important;
  background:linear-gradient(180deg, rgba(255,255,255,.060), rgba(255,255,255,.028)) !important;
  border:1px solid rgba(147,197,253,.18) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06) !important;
}.gm-faqSupportStatIcon{
  width:42px !important;
  height:42px !important;
  border-radius:14px !important;
  display:grid !important;
  place-items:center !important;
  flex:0 0 42px !important;
  background:linear-gradient(135deg, rgba(79,110,247,.32), rgba(59,130,246,.18)) !important;
  border:1px solid rgba(147,197,253,.24) !important;
  color:#93c5fd !important;
  box-shadow:0 12px 28px rgba(79,110,247,.16) !important;
}.gm-faqSupportStatIcon i{
  font-size:15px !important;
}.gm-faqSupportStatText{
  display:flex !important;
  flex-direction:column !important;
  gap:4px !important;
  min-width:0 !important;
}.gm-faqSupportStatText strong{
  display:block !important;
  color:#fff !important;
  font-size:15px !important;
  line-height:1.15 !important;
  letter-spacing:-.01em !important;
}.gm-faqSupportStatText small{
  display:block !important;
  color:rgba(226,234,255,.56) !important;
  font-size:12.5px !important;
  line-height:1.35 !important;
  font-weight:700 !important;
}@media(max-width:980px){.gm-faqSupportStats{
    grid-template-columns:repeat(3, minmax(0, 1fr)) !important;
  }.gm-faqSupportStat{
    align-items:flex-start !important;
    flex-direction:column !important;
    gap:12px !important;
  }}@media(max-width:640px){.gm-faqRebuildHead{margin-bottom:28px !important;}.gm-faqSupportStats{grid-template-columns:1fr !important;}.gm-faqSupportStat{
    flex-direction:row !important;
    align-items:center !important;
  }}.lbHybridLive::before,
.lbHybridBoosters::before{
  background:
    radial-gradient(900px 420px at 50% 0%, rgba(79,110,247,.10), transparent 64%),
    linear-gradient(180deg, rgba(255,255,255,.008), transparent 42%) !important;
  opacity:1 !important;
}.lbHybridOrder,
.lbHybridBooster{
  background:linear-gradient(160deg,#0c1022,#0a0d1e) !important;
  border:1px solid rgba(255,255,255,.10) !important;
  box-shadow:0 8px 32px rgba(0,0,0,.35) !important;
}.lbHybridOrder::before,
.lbHybridOrder::after,
.lbHybridBooster::before{
  display:none !important;
}.lbHybridBooster::after{
  background:rgba(255,255,255,.07) !important;
  opacity:1 !important;
}.lbHybridOrder:hover,
.lbHybridBooster:hover{
  border-color:rgba(79,110,247,.28) !important;
  box-shadow:0 12px 42px rgba(0,0,0,.42) !important;
}.lbHybridOrderIcon,
.lbHybridBoosterAvatar{
  background:linear-gradient(135deg,#4f6ef7,#6366f1) !important;
  border-color:rgba(255,255,255,.10) !important;
  box-shadow:none !important;
}.lbHybridOrderIcon{
  overflow:hidden !important;
  padding:0 !important;
}.lbHybridOrderIcon img{
  width:100% !important;
  height:100% !important;
  display:block !important;
  object-fit:cover !important;
  border-radius:inherit !important;
}.lbHybridOrderStatus{
  background:rgba(255,255,255,.07) !important;
  border-color:rgba(255,255,255,.12) !important;
  color:rgba(255,255,255,.72) !important;
}.lbHybridOrderStatus.done{
  background:rgba(255,255,255,.07) !important;
  border-color:rgba(255,255,255,.12) !important;
  color:rgba(255,255,255,.72) !important;
}.lbHybridProgress{
  background:rgba(255,255,255,.08) !important;
  box-shadow:none !important;
}.lbHybridProgress span{
  background:linear-gradient(90deg,#4f6ef7,#7c9fff) !important;
  box-shadow:none !important;
}.lbHybridOrderBooster,
.lbHybridBoosterRating{
  border-color:rgba(255,255,255,.08) !important;
}.lbHybridBoosterRating{
  background:rgba(255,255,255,.06) !important;
}.lbHybridBoosterChamps img,
.lbHybridOrderBooster img{
  border-color:rgba(255,255,255,.12) !important;
  box-shadow:none !important;
}@media (max-width: 820px) {body.landing,
  body.landing.gamingmarket {
    background-attachment: scroll !important;
    background-image: linear-gradient(180deg, #04030e 0%, #07051a 62%, #04030e 100%) !important;
  }}@media (max-width: 767px) {body.landing .gm-supportfaq.gm-faqRebuild {
    padding: 54px 0 68px !important;
    background: transparent !important;
  }body.landing .gm-faqRebuild .gm-wrap {
    width: 100% !important;
    max-width: none !important;
    padding: 0 14px !important;
  }body.landing .gm-faqRebuild .gmFaq-head {
    margin: 0 0 14px !important;
    padding: 0 !important;
    text-align: left !important;
  }body.landing .gm-faqRebuild .gmFaq-head .gm-sectionTag,
  body.landing .gm-faqRebuild .gmFaq-title,
  body.landing .gm-faqRebuild .gmFaq-lead {
    display: none !important;
  }body.landing .gm-faqRebuild .gmFaq-tabs {
    display: flex !important;
    flex-wrap: nowrap !important;
    justify-content: flex-start !important;
    gap: 8px !important;
    width: calc(100vw - 28px) !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 0 12px !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    scrollbar-width: none !important;
    -webkit-overflow-scrolling: touch !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
  }body.landing .gm-faqRebuild .gmFaq-tabs::-webkit-scrollbar {
    display: none !important;
  }body.landing .gm-faqRebuild .gmFaq-tab {
    flex: 0 0 auto !important;
    min-width: auto !important;
    height: 38px !important;
    padding: 0 15px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    font-size: 12.5px !important;
    font-weight: 900 !important;
    color: rgba(207,219,255,.72) !important;
    background: rgba(255,255,255,.045) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    box-shadow: none !important;
    white-space: nowrap !important;
  }body.landing .gm-faqRebuild .gmFaq-tab.is-active {
    color: #fff !important;
    background: linear-gradient(135deg, #2f5bea, #3b82f6) !important;
    border-color: rgba(120,158,255,.55) !important;
    box-shadow: 0 12px 30px rgba(47,91,234,.28) !important;
  }body.landing .gm-faqRebuild .gmFaq-grid {
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
    margin: 0 !important;
  }body.landing .gm-faqRebuild .gmFaq-accordion {
    order: 1 !important;
    width: 100% !important;
    padding: 0 !important;
    border-radius: 20px !important;
    background: rgba(5,9,22,.62) !important;
    border: 1px solid rgba(72,111,255,.18) !important;
    box-shadow: 0 18px 46px rgba(0,0,0,.32) !important;
    overflow: hidden !important;
  }body.landing .gm-faqRebuild .gmFaq-item {
    border-bottom: 1px solid rgba(255,255,255,.075) !important;
    background: transparent !important;
  }body.landing .gm-faqRebuild .gmFaq-item:last-child {
    border-bottom: 0 !important;
  }body.landing .gm-faqRebuild .gmFaq-btn {
    min-height: 58px !important;
    padding: 16px 17px !important;
    font-size: 14.5px !important;
    line-height: 1.35 !important;
    font-weight: 850 !important;
    color: rgba(248,250,255,.95) !important;
    background: transparent !important;
    border: 0 !important;
    text-align: left !important;
  }body.landing .gm-faqRebuild .gmFaq-chev {
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
    border-radius: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(255,255,255,.055) !important;
    color: rgba(190,207,255,.70) !important;
  }body.landing .gm-faqRebuild .gmFaq-inner {
    padding: 0 17px 17px !important;
    font-size: 13.5px !important;
    line-height: 1.65 !important;
    color: rgba(201,214,255,.68) !important;
  }body.landing .gm-faqRebuild .gmFaq-contact {
    order: 2 !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 22px 20px 20px !important;
    border-radius: 24px !important;
    background:
      radial-gradient(420px 180px at 18% 0%, rgba(47,91,234,.24), transparent 66%),
      linear-gradient(180deg, rgba(18,32,80,.92), rgba(7,12,29,.94)) !important;
    border: 1px solid rgba(74,126,255,.28) !important;
    box-shadow: 0 24px 68px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.08) !important;
  }body.landing .gm-faqRebuild .gmFaq-contactIcon {
    width: 52px !important;
    height: 52px !important;
    border-radius: 16px !important;
    margin: 0 0 18px !important;
    font-size: 21px !important;
    background: linear-gradient(135deg, #2f5bea, #3b82f6) !important;
    color: #fff !important;
    box-shadow: 0 16px 36px rgba(47,91,234,.36) !important;
  }body.landing .gm-faqRebuild .gmFaq-contactTitle {
    margin: 0 0 10px !important;
    font-size: 22px !important;
    line-height: 1.12 !important;
    letter-spacing: -.035em !important;
    color: #fff !important;
  }body.landing .gm-faqRebuild .gmFaq-contactText {
    margin: 0 0 20px !important;
    font-size: 14.5px !important;
    line-height: 1.65 !important;
    color: rgba(183,203,255,.76) !important;
  }body.landing .gm-faqRebuild .gmFaq-contactBtns {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }body.landing .gm-faqRebuild .gmFaq-btnPrimary,
  body.landing .gm-faqRebuild .gmFaq-btnGhost {
    width: 100% !important;
    min-height: 48px !important;
    border-radius: 15px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 9px !important;
    font-size: 14px !important;
    font-weight: 900 !important;
    text-decoration: none !important;
  }body.landing .gm-faqRebuild .gmFaq-btnPrimary {
    background: linear-gradient(135deg, #2f5bea, #3b82f6) !important;
    border: 1px solid rgba(144,178,255,.38) !important;
    color: #fff !important;
    box-shadow: 0 16px 34px rgba(47,91,234,.32) !important;
  }body.landing .gm-faqRebuild .gmFaq-btnGhost {
    background: rgba(255,255,255,.055) !important;
    border: 1px solid rgba(255,255,255,.13) !important;
    color: #fff !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.06) !important;
  }body.landing .gm-faqRebuild .gmFaq-stats {
    display: none !important;
  }}.lbHybridBooster{
  position:relative !important;
  padding:24px !important;
  padding-right:82px !important;
}.lbHybridBoosterHead{
  padding-right:0 !important;
}.lbHybridBoosterIdentity{
  min-width:0 !important;
}.lbHybridBoosterRankBadge{
  position:absolute !important;
  top:22px !important;
  right:22px !important;
  width:48px !important;
  height:48px !important;
  border-radius:16px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  background:linear-gradient(180deg, rgba(96,165,250,.14), rgba(15,23,42,.72)) !important;
  border:1px solid rgba(96,165,250,.28) !important;
  box-shadow:0 16px 36px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.08) !important;
  z-index:3 !important;
}.lbHybridBoosterRankBadge img{
  width:38px !important;
  height:38px !important;
  object-fit:contain !important;
  filter:drop-shadow(0 8px 14px rgba(0,0,0,.38)) !important;
}.lbHybridBoosterOnline{
  position:static !important;
  width:fit-content !important;
  height:auto !important;
  min-height:24px !important;
  padding:4px 9px !important;
  margin-top:2px !important;
  border-radius:999px !important;
  display:inline-flex !important;
  align-items:center !important;
  gap:6px !important;
  overflow:visible !important;
  color:rgba(134,239,172,.92) !important;
  background:rgba(34,197,94,.10) !important;
  border:1px solid rgba(34,197,94,.20) !important;
  box-shadow:none !important;
  font-size:10px !important;
  font-weight:900 !important;
  line-height:1 !important;
}.lbHybridBoosterOnline.offline{
  color:rgba(203,213,225,.72) !important;
  background:rgba(148,163,184,.08) !important;
  border-color:rgba(148,163,184,.16) !important;
}.lbHybridBoosterOnline .dot{
  display:block !important;
  width:6px !important;
  height:6px !important;
  border-radius:999px !important;
  background:#22c55e !important;
  flex:0 0 auto !important;
}.lbHybridBoosterOnline.offline .dot{
  background:rgba(148,163,184,.62) !important;
}.lbHybridBoosterRank{
  display:none !important;
}.lbHybridBoosterRoles{
  display:flex !important;
  align-items:center !important;
  flex-wrap:wrap !important;
  gap:7px !important;
  min-height:32px !important;
  margin:2px 0 0 !important;
}.lbHybridBoosterRolePill{
  width:30px !important;
  height:30px !important;
  min-width:30px !important;
  padding:0 !important;
  border-radius:10px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  background:rgba(96,165,250,.07) !important;
  border:1px solid rgba(96,165,250,.18) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 8px 18px rgba(0,0,0,.16) !important;
  font-size:0 !important;
  line-height:0 !important;
  overflow:hidden !important;
}.lbHybridBoosterRoleIcon{
  width:18px !important;
  height:18px !important;
  object-fit:contain !important;
  flex:0 0 18px !important;
  display:block !important;
  opacity:.86 !important;
  filter:drop-shadow(0 4px 8px rgba(0,0,0,.32)) !important;
}.lbHybridBoosterRolePill--jungle{background:rgba(34,197,94,.09) !important;border-color:rgba(34,197,94,.18) !important;}.lbHybridBoosterRolePill--midlane,
.lbHybridBoosterRolePill--mid,
.lbHybridBoosterRolePill--middle{background:rgba(96,165,250,.09) !important;border-color:rgba(96,165,250,.18) !important;}.lbHybridBoosterRolePill--adcarry,
.lbHybridBoosterRolePill--adc,
.lbHybridBoosterRolePill--bot{background:rgba(96,165,250,.09) !important;border-color:rgba(96,165,250,.18) !important;}.lbHybridBoosterRolePill--support{background:rgba(236,72,153,.09) !important;border-color:rgba(236,72,153,.18) !important;}.lbHybridBoosterRolePill--toplane,
.lbHybridBoosterRolePill--top{background:rgba(96,165,250,.09) !important;border-color:rgba(96,165,250,.18) !important;}.lbHybridBoosterChamps{
  margin-top:auto !important;
}@media(max-width:560px){.lbHybridBooster{
    padding:20px !important;
    padding-right:76px !important;
  }.lbHybridBoosterRankBadge{
    top:20px !important;
    right:20px !important;
    width:44px !important;
    height:44px !important;
  }.lbHybridBoosterRankBadge img{
    width:34px !important;
    height:34px !important;
  }}body.landing.gamingmarket .gm-bg,
body.landing.gamingmarket .gm-gridlines,
body.landing.gamingmarket #gmStars{
  opacity:.42 !important;
}@keyframes lbScrollArrow{
  0%,100%{transform:translateX(-50%) translateY(0); opacity:.52;}
  50%{transform:translateX(-50%) translateY(8px); opacity:1;}
}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}html,
body.landing,
body.landing.gamingmarket{
  width:100% !important;
  max-width:100% !important;
  overflow-x:hidden !important;
  background-color:#04050f !important;
}@supports (overflow-x:clip){html,
  body.landing,
  body.landing.gamingmarket{overflow-x:clip !important;}}body.landing,
body.landing.gamingmarket{
  background:
    radial-gradient(1200px 720px at 18% 5%, rgba(79,110,241,.17), transparent 62%),
    radial-gradient(950px 620px at 84% 18%, rgba(99,102,241,.13), transparent 58%),
    radial-gradient(900px 720px at 50% 88%, rgba(59,130,246,.08), transparent 64%),
    linear-gradient(180deg, #04050f 0%, #070a1a 52%, #04050f 100%) !important;
  background-attachment:scroll !important;
}.gm-section::before,.gm-section::after,
.gm-marketSection::before,.gm-marketSection::after,
.gm-reviewsSection::before,.gm-reviewsSection::after,
.lbHybridSteps::before,.lbHybridSteps::after,
.lbHybridLive::before,.lbHybridLive::after,
.lbHybridBoosters::before,.lbHybridBoosters::after,
.gm-supportfaq::before,.gm-supportfaq::after,
.gm-heroSearch::before,.gm-heroSearch::after{
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}.gm-helpCenterShell{
  grid-template-columns:minmax(300px,.72fr) minmax(0,1.28fr) !important;
  column-gap:44px !important;
  row-gap:18px !important;
}.gm-helpCenterShell > .gm-helpTabs{
  grid-column:2 !important;
  grid-row:1 !important;
  align-self:end !important;
  justify-self:start !important;
  width:auto !important;
  max-width:100% !important;
}.gm-helpCenterShell > .gm-faqPremium{
  grid-column:2 !important;
  grid-row:2 / span 5 !important;
}.gm-helpTabs{
  display:flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  flex-wrap:wrap !important;
  gap:10px !important;
  margin:0 0 14px !important;
  padding:0 !important;
  border:0 !important;
  overflow:visible !important;
  touch-action:auto !important;
}.gm-helpTab{
  position:relative !important;
  flex:0 0 auto !important;
  min-height:42px !important;
  padding:0 20px !important;
  border-radius:999px !important;
  border:1px solid rgba(129,140,248,.24) !important;
  background:linear-gradient(180deg, rgba(255,255,255,.070), rgba(255,255,255,.030)) !important;
  color:rgba(226,232,255,.68) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.075) !important;
  font-size:12px !important;
  line-height:1 !important;
  font-weight:950 !important;
  letter-spacing:.035em !important;
  text-transform:none !important;
  cursor:pointer !important;
  -webkit-tap-highlight-color:transparent !important;
}.gm-helpTab::after{display:none !important;}.gm-helpTab.is-active,
.gm-helpTab:hover{
  color:#fff !important;
  background:linear-gradient(135deg, #4f6ef7 0%, #6366f1 100%) !important;
  border-color:rgba(147,197,253,.48) !important;
  box-shadow:0 16px 36px rgba(79,110,247,.26), inset 0 1px 0 rgba(255,255,255,.18) !important;
}.gm-faqPremium{
  background:linear-gradient(180deg, rgba(13,17,38,.92), rgba(7,9,22,.78)) !important;
  border-color:rgba(79,110,247,.24) !important;
}.gm-faqitem.open::before{
  background:linear-gradient(180deg, #60a5fa, #6366f1) !important;
}.gm-faqitem.open{
  border-color:rgba(96,165,250,.36) !important;
  background:radial-gradient(520px 160px at 14% 0%, rgba(79,110,247,.16), transparent 62%), linear-gradient(180deg, rgba(255,255,255,.072), rgba(255,255,255,.030)) !important;
}@media(max-width:768px){body.landing,
  body.landing.gamingmarket{
    background-attachment:scroll !important;
    -webkit-text-size-adjust:100% !important;
  }.gm-helpCenterShell{
    grid-template-columns:1fr !important;
    padding:0 16px !important;
    gap:20px !important;
  }.gm-helpCenterShell > .gm-sectionTag,
  .gm-helpCenterShell > .gm-helpCenterTitle,
  .gm-helpCenterShell > .gm-helpCenterLead,
  .gm-helpCenterShell > .gm-helpCenterActions,
  .gm-helpCenterShell > .gm-helpTabs,
  .gm-helpCenterShell > .gm-faqPremium{
    grid-column:1 !important;
    grid-row:auto !important;
  }.gm-helpTabs{
    width:100% !important;
    display:flex !important;
    justify-content:flex-start !important;
    flex-wrap:wrap !important;
    gap:10px !important;
    margin:10px 0 2px !important;
    padding:0 !important;
    overflow:visible !important;
  }.gm-helpTab{
    min-height:38px !important;
    padding:0 17px !important;
    border-radius:999px !important;
    font-size:12px !important;
    letter-spacing:0 !important;
  }.gm-helpTab.is-active{
    background:linear-gradient(135deg, #4f6ef7, #60a5fa) !important;
    border-color:rgba(147,197,253,.58) !important;
  }.lbHybridSteps{
    padding:62px 0 !important;
  }.lbHybridSteps .lbHybridCenter{
    text-align:left !important;
    align-items:flex-start !important;
    margin-bottom:18px !important;
  }.lbHybridSteps .gm-h2{
    font-size:clamp(30px, 9vw, 40px) !important;
    line-height:1.08 !important;
  }.lbHybridSteps .gm-sub{
    max-width:34ch !important;
    font-size:15px !important;
    line-height:1.62 !important;
  }.lbHybridStepGrid{
    display:grid !important;
    grid-template-columns:1fr !important;
    gap:12px !important;
  }.lbHybridStep{
    min-height:0 !important;
    padding:18px !important;
    border-radius:22px !important;
    display:grid !important;
    grid-template-columns:48px minmax(0, 1fr) !important;
    column-gap:14px !important;
    row-gap:4px !important;
    align-items:center !important;
    text-align:left !important;
    background:linear-gradient(180deg, rgba(14,22,48,.92), rgba(8,10,24,.78)) !important;
    border:1px solid rgba(79,110,247,.24) !important;
    box-shadow:0 18px 45px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.06) !important;
  }.lbHybridStep::before{
    display:none !important;
  }.lbHybridStepIcon{
    grid-row:1 / span 2 !important;
    width:48px !important;
    height:48px !important;
    border-radius:16px !important;
    background:linear-gradient(135deg, rgba(79,110,247,.95), rgba(96,165,250,.72)) !important;
    box-shadow:0 14px 34px rgba(79,110,247,.28) !important;
  }.lbHybridStep strong{
    margin:0 !important;
    font-size:16px !important;
    line-height:1.2 !important;
  }.lbHybridStep p{
    grid-column:2 !important;
    margin:0 !important;
    font-size:13.5px !important;
    line-height:1.55 !important;
    color:rgba(226,232,255,.64) !important;
  }}@media(max-width:380px){.gm-helpTab{padding:0 14px !important; font-size:11.5px !important;}.lbHybridStep{padding:16px !important; grid-template-columns:44px minmax(0,1fr) !important;}.lbHybridStepIcon{width:44px !important; height:44px !important; border-radius:15px !important;}}@media (max-width: 768px){.gm-helpCenterShell > .gm-helpTabs,
  .gm-helpTabs{
    width:100% !important;
    max-width:100% !important;
    margin:24px 0 22px !important;
    padding:0 2px 4px !important;
    display:flex !important;
    flex-wrap:nowrap !important;
    justify-content:flex-start !important;
    align-items:center !important;
    gap:8px !important;
    overflow-x:auto !important;
    overflow-y:hidden !important;
    -webkit-overflow-scrolling:touch !important;
    scrollbar-width:none !important;
    border-bottom:0 !important;
  }.gm-helpTabs::-webkit-scrollbar{display:none !important;}.gm-helpTab{
    flex:0 0 auto !important;
    min-width:auto !important;
    height:38px !important;
    padding:0 16px !important;
    border-radius:999px !important;
    border:1px solid rgba(96,165,250,.18) !important;
    background:linear-gradient(180deg, rgba(255,255,255,.075), rgba(255,255,255,.035)) !important;
    color:rgba(219,234,254,.70) !important;
    font-size:12px !important;
    font-weight:900 !important;
    letter-spacing:.02em !important;
    line-height:38px !important;
    text-align:center !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.07) !important;
  }.gm-helpTab.is-active,
  .gm-helpTab:hover{
    color:#fff !important;
    background:linear-gradient(135deg, #4f6ef7 0%, #60a5fa 100%) !important;
    border-color:rgba(147,197,253,.55) !important;
    box-shadow:0 12px 28px rgba(79,110,247,.28), inset 0 1px 0 rgba(255,255,255,.18) !important;
  }.gm-helpTab::after,
  .gm-helpTab:nth-child(3n),
  .gm-helpTab:nth-child(n/**/+4){
    border:0 !important;
  }}.gm-ctaBand.lbCtaNew {
  position:relative;
  z-index:1;
  overflow:hidden;
  padding:120px 0;
  background:
    linear-gradient(90deg, rgba(4,5,15,.96) 0%, rgba(7,7,24,.84) 40%, rgba(7,8,28,.50) 68%, rgba(4,5,15,.82) 100%),
    linear-gradient(180deg, rgba(4,5,15,.98) 0%, rgba(4,5,15,.22) 46%, rgba(4,5,15,.98) 100%),
    url("/public/assets/website/images/landing/cta-background-footer.png") center center / cover no-repeat;
  border-top:1px solid rgba(255,255,255,.08);
  border-bottom:1px solid rgba(255,255,255,.08);
}.lbCtaNew::before {
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background:
    radial-gradient(760px 420px at 16% 30%, rgba(96,165,250,.18), transparent 64%),
    radial-gradient(720px 420px at 86% 42%, rgba(79,110,241,.18), transparent 66%);
  opacity:1;
  mask-image:linear-gradient(180deg, transparent 0%, black 14%, black 86%, transparent 100%);
}.lbCtaNew::after {
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background:linear-gradient(90deg, rgba(4,5,15,.82) 0%, rgba(4,5,15,.42) 52%, rgba(4,5,15,.16) 100%);
}.lbCtaNewWrap { position:relative; z-index:2; max-width:var(--gm-wrap); margin:0 auto; padding:0 24px; display:grid; grid-template-columns:minmax(0, 1fr) 420px; gap:56px; align-items:center; }.lbCtaNewCopy { max-width:720px; }.lbCtaNewBadge { display:inline-flex; align-items:center; gap:10px; margin:0 0 18px; padding:8px 15px; border-radius:999px; background:rgba(99,102,241,.13); border:1px solid rgba(129,140,248,.30); color:#c7d2fe; font-size:11px; font-weight:950; letter-spacing:.16em; text-transform:uppercase; }.lbCtaNewBadge span { width:7px; height:7px; border-radius:999px; background:#a78bfa; box-shadow:0 0 18px rgba(167,139,250,.85); }.lbCtaNewTitle { margin:0; max-width:760px; font-size:clamp(42px, 5vw, 76px); line-height:1.02; letter-spacing:-.055em; color:#fff; }.lbCtaNewTitle strong { font-weight:950; color:transparent; background:linear-gradient(95deg, #7dd3fc, #a78bfa 45%, #f0abfc 100%); -webkit-background-clip:text; background-clip:text; }.lbCtaNewText { max-width:62ch; margin:20px 0 0; color:rgba(244,240,255,.68); font-size:18px; line-height:1.7; }.lbCtaNewActions { display:flex; flex-wrap:wrap; gap:14px; margin-top:34px; }.lbCtaNewBtn { min-height:56px; padding:0 25px; border-radius:17px; }.lbCtaNewBtnPrimary { background:linear-gradient(135deg, #4f6ef7, #3b82f6 52%, #60a5fa); border-color:rgba(96,165,250,.42); box-shadow:0 18px 56px rgba(99,102,241,.34), inset 0 1px 0 rgba(255,255,255,.16); }.lbCtaNewBtnGhost { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.14); color:rgba(255,255,255,.86); }.lbCtaNewMeta { display:flex; flex-wrap:wrap; gap:10px; margin-top:22px; }.lbCtaNewMetaItem { display:inline-flex; align-items:center; gap:8px; padding:9px 12px; border-radius:12px; background:rgba(255,255,255,.055); border:1px solid rgba(255,255,255,.10); color:rgba(255,255,255,.62); font-size:13px; font-weight:800; }.lbCtaNewMetaItem i { color:#a5b4fc; }.lbCtaNewPanel { position:relative; padding:28px; border-radius:30px; background:linear-gradient(160deg, rgba(17,20,44,.92), rgba(8,10,24,.88)); border:1px solid rgba(129,140,248,.24); box-shadow:0 30px 90px rgba(0,0,0,.46), inset 0 1px 0 rgba(255,255,255,.08); backdrop-filter:blur(14px); }.lbCtaNewPanel::before { content:""; position:absolute; inset:-1px; border-radius:inherit; pointer-events:none; background:radial-gradient(330px 160px at 20% 0%, rgba(96,165,250,.16), transparent 68%); }.lbCtaNewPanelTop, .lbCtaNewMiniGrid { position:relative; z-index:1; }.lbCtaNewPanelTop { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:22px; }.lbCtaNewPanelTop span { color:rgba(255,255,255,.55); font-size:12px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }.lbCtaNewPanelTop b { color:#fff; font-size:22px; }.lbCtaNewMiniGrid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }.lbCtaNewMini { min-height:112px; padding:17px; border-radius:20px; background:rgba(255,255,255,.055); border:1px solid rgba(255,255,255,.10); }.lbCtaNewMini i { width:34px; height:34px; display:grid; place-items:center; margin-bottom:14px; border-radius:12px; background:rgba(129,140,248,.16); color:#c4b5fd; }.lbCtaNewMini b { display:block; color:#fff; font-size:15px; margin-bottom:5px; }.lbCtaNewMini span { display:block; color:rgba(255,255,255,.50); font-size:12px; line-height:1.45; }@media(max-width:980px) {.gm-ctaBand.lbCtaNew { background-position:center center; }.lbCtaNewWrap { grid-template-columns:1fr; gap:34px; }.lbCtaNewPanel { max-width:620px; }}@media(max-width:640px) {.gm-ctaBand.lbCtaNew {
    padding:74px 0 70px;
    min-height:560px;
    display:flex;
    align-items:center;
    background:
      linear-gradient(180deg, rgba(31,12,66,.76) 0%, rgba(10,8,30,.52) 42%, rgba(6,5,18,.94) 100%),
      linear-gradient(90deg, rgba(71,19,116,.70) 0%, rgba(16,13,45,.56) 45%, rgba(6,5,18,.72) 100%),
      url("/public/assets/website/images/landing/cta-background-footer.png") center center / cover no-repeat;
  }.lbCtaNew::before { background:radial-gradient(420px 300px at 22% 18%, rgba(96,165,250,.18), transparent 70%); mask-image:none; }.lbCtaNew::after { background:linear-gradient(180deg, rgba(5,4,18,.42) 0%, rgba(5,4,18,.10) 45%, rgba(5,4,18,.72) 100%); }.lbCtaNewWrap { width:100%; padding:0 24px; text-align:center; display:block; }.lbCtaNewCopy { max-width:390px; margin:0 auto; }.lbCtaNewBadge { margin-left:auto; margin-right:auto; margin-bottom:22px; background:transparent; border:none; padding:0; color:#7dd3fc; font-size:12px; letter-spacing:.22em; }.lbCtaNewBadge span { background:#60a5fa; box-shadow:0 0 18px rgba(96,165,250,.85); }.lbCtaNewTitle { max-width:360px; margin:0 auto; font-size:clamp(30px, 8.2vw, 42px); line-height:1.12; letter-spacing:-.05em; }.lbCtaNewText { max-width:340px; margin:18px auto 0; font-size:17px; line-height:1.58; color:rgba(244,240,255,.72); }.lbCtaNewActions { display:grid; grid-template-columns:1fr; max-width:280px; margin:32px auto 0; gap:14px; }.lbCtaNewBtn { width:100%; justify-content:center; min-height:54px; border-radius:999px; }.lbCtaNewBtnGhost { background:rgba(255,255,255,.10); backdrop-filter:blur(10px); }.lbCtaNewMeta { justify-content:center; gap:18px 20px; margin:28px auto 0; max-width:330px; }.lbCtaNewMetaItem { padding:0; border:0; background:transparent; font-size:13px; color:rgba(244,240,255,.70); }.lbCtaNewMetaItem i { color:#60a5fa; }.lbCtaNewPanel { display:none; }}body.landing.gamingmarket,
body.landing{
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(79,110,247,.20), rgba(0,0,0,0) 60%),
    radial-gradient(900px 600px at 80% 18%, rgba(96,165,250,.14), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,189,248,.09), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #040716 0%, #071026 60%, #040716 100%) !important;
  background-attachment:scroll !important;
  background-color:#040716 !important;
}.gm-bg{
  background:
    radial-gradient(1200px 700px at 20% 10%, rgba(79,110,247,.18), rgba(0,0,0,0) 60%),
    radial-gradient(900px 600px at 80% 18%, rgba(96,165,250,.13), rgba(0,0,0,0) 55%),
    radial-gradient(1000px 700px at 55% 85%, rgba(56,189,248,.08), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, #040716 0%, #071026 60%, #040716 100%) !important;
}.gm-helpCenterShell > .gm-helpTabs{
  grid-column:1 / -1 !important;
  grid-row:auto !important;
  justify-self:center !important;
  align-self:center !important;
  width:100% !important;
  max-width:760px !important;
}.gm-helpTabs{
  justify-content:center !important;
  margin:22px auto 24px !important;
  padding:0 !important;
  border:0 !important;
  border-bottom:0 !important;
}.gm-helpTab{
  background:linear-gradient(180deg, rgba(20,29,58,.90), rgba(12,18,40,.78)) !important;
  border-color:rgba(96,165,250,.24) !important;
}.gm-helpTab.is-active,
.gm-helpTab:hover{
  background:linear-gradient(135deg,#4f6ef7 0%,#5b7cff 100%) !important;
  border-color:rgba(147,197,253,.52) !important;
  box-shadow:0 16px 36px rgba(79,110,247,.28), inset 0 1px 0 rgba(255,255,255,.18) !important;
}.gm-faqitem::before,
.gm-faqitem.open::before{
  display:none !important;
  content:none !important;
}.gm-faqitem.open{
  border-left:0 !important;
}@media(max-width:768px){.gm-helpCenterShell > .gm-helpTabs{
    grid-column:1 !important;
    justify-self:center !important;
    max-width:100% !important;
  }.gm-helpTabs{
    justify-content:center !important;
    flex-wrap:wrap !important;
    overflow:visible !important;
    margin:12px auto 4px !important;
    padding:0 !important;
    border:0 !important;
  }}@media(max-width:640px){.gm-helpTabs{
    justify-content:center !important;
    flex-wrap:wrap !important;
    overflow:visible !important;
    gap:10px !important;
  }}html,
body {
  background:#04060f !important;
  background-color:#04060f !important;
}body.landing,
body.landing.gamingmarket {
  background:
    radial-gradient(ellipse 1100px 850px at 7% 26%,  rgba(30,80,220,.55),   transparent 52%),
    radial-gradient(ellipse 700px 580px  at 16% 58%, rgba(20,60,180,.30),   transparent 48%),
    radial-gradient(ellipse 850px 620px  at 82% 76%, rgba(10,40,140,.22),   transparent 52%),
    radial-gradient(ellipse 500px 380px  at 90% 8%,  rgba(10,30,100,.18),   transparent 52%),
    linear-gradient(175deg, #03050e 0%, #050818 30%, #04060f 65%, #030408 100%) !important;
  background-color:#04060f !important;
  background-attachment:scroll !important;
}@media(max-width:820px){body.landing,
  body.landing.gamingmarket { background-attachment:scroll !important; }}.gm-bg {
  background:
    radial-gradient(ellipse 1100px 850px at 7% 26%,  rgba(30,80,220,.50),   transparent 52%),
    radial-gradient(ellipse 700px 580px  at 16% 58%, rgba(20,60,180,.26),   transparent 48%),
    radial-gradient(ellipse 850px 620px  at 82% 76%, rgba(10,40,140,.18),   transparent 52%),
    linear-gradient(175deg, #03050e 0%, #050818 30%, #04060f 65%, #030408 100%) !important;
  z-index:-3 !important;
}#gmStars {
  position:fixed !important;
  inset:0 !important;
  z-index:0 !important;
  pointer-events:none !important;
  overflow:visible !important;
}.gm-star {
  position:fixed !important;
  border-radius:50% !important;
  pointer-events:none !important;
}.gm-faqRebuildGrid {
  gap:28px !important;
}.gm-faqContactCard {
  background:linear-gradient(145deg, rgba(8,14,36,.96), rgba(5,9,24,.98)) !important;
  border:1px solid rgba(96,165,250,.20) !important;
  box-shadow:0 24px 70px rgba(0,0,0,.50), inset 0 1px 0 rgba(255,255,255,.06) !important;
  border-radius:28px !important;
}.gm-faqContactGlow {
  background:
    radial-gradient(360px 220px at 18% 10%, rgba(30,80,220,.28), transparent 64%),
    radial-gradient(360px 260px at 100% 84%, rgba(20,60,200,.18), transparent 66%) !important;
}.gm-faqContactIcon {
  background:linear-gradient(135deg,#2563eb,#3b82f6) !important;
  border-color:rgba(96,165,250,.42) !important;
  box-shadow:0 14px 36px rgba(37,99,235,.32) !important;
}.gm-faqPremium {
  background:linear-gradient(160deg, rgba(8,14,36,.96), rgba(5,9,24,.96)) !important;
  border:1px solid rgba(96,165,250,.18) !important;
  box-shadow:0 24px 70px rgba(0,0,0,.50), inset 0 1px 0 rgba(255,255,255,.05) !important;
}.gm-faqitem {
  background:rgba(255,255,255,.04) !important;
  border-color:rgba(96,165,250,.14) !important;
}.gm-faqitem.open {
  background:rgba(30,80,220,.10) !important;
  border-color:rgba(96,165,250,.30) !important;
}.gm-faqSupportStat {
  background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03)) !important;
  border-color:rgba(96,165,250,.16) !important;
}.gm-faqSupportStatIcon {
  background:linear-gradient(135deg, rgba(30,80,220,.40), rgba(37,99,235,.22)) !important;
  border-color:rgba(96,165,250,.28) !important;
  color:#93c5fd !important;
}.gm-helpTabs {
  display:flex !important;
  justify-content:center !important;
  flex-wrap:wrap !important;
  gap:10px !important;
  margin:0 auto 28px !important;
  padding:8px !important;
  width:fit-content !important;
  max-width:100% !important;
  border:1px solid rgba(96,165,250,.16) !important;
  border-radius:999px !important;
  background:rgba(5,9,22,.60) !important;
  box-shadow:0 18px 54px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.06) !important;
}.gm-helpTab {
  min-height:40px !important;
  padding:0 18px !important;
  border-radius:999px !important;
  border:1px solid transparent !important;
  background:transparent !important;
  color:rgba(191,219,254,.62) !important;
  font-size:13px !important;
  font-weight:900 !important;
  box-shadow:none !important;
}.gm-helpTab.is-active,
.gm-helpTab:hover {
  background:linear-gradient(135deg,#1d4ed8,#3b82f6) !important;
  border-color:rgba(96,165,250,.48) !important;
  color:#fff !important;
  box-shadow:0 10px 28px rgba(29,78,216,.30), inset 0 1px 0 rgba(255,255,255,.18) !important;
}.gm-helpTab::after { display:none !important; }.gm-sectionTag span {
  background:linear-gradient(90deg,#60a5fa,#3b82f6) !important;
}.gm-gridlines {
  opacity:.08 !important;
}.gm-supportfaq.gm-faqRebuild {
  padding:100px 0 110px !important;
  background:transparent !important;
}.gm-supportfaq .gm-wrap {
  max-width:1200px !important;
}.gmFaq-head {
  text-align:center;
  margin-bottom:52px;
}.gmFaq-title {
  margin:0 0 14px;
  font-size:clamp(48px,5.5vw,80px);
  font-weight:900;
  letter-spacing:-.05em;
  line-height:1.0;
  color:#fff;
}.gmFaq-lead {
  margin:0 auto 32px;
  max-width:60ch;
  color:rgba(191,219,254,.68);
  font-size:17px;
  line-height:1.65;
}.gmFaq-tabs {
  display:inline-flex;
  align-items:center;
  gap:4px;
  padding:5px;
  border-radius:999px;
  background:rgba(8,14,32,.70);
  border:1px solid rgba(96,165,250,.18);
  box-shadow:0 8px 32px rgba(0,0,0,.30);
}.gmFaq-tab {
  display:inline-flex;
  align-items:center;
  gap:7px;
  height:38px;
  padding:0 18px;
  border-radius:999px;
  border:none;
  background:transparent;
  color:rgba(191,219,254,.60);
  font-size:13px;
  font-weight:800;
  cursor:pointer;
  transition:background .18s, color .18s, box-shadow .18s;
  white-space:nowrap;
}.gmFaq-tab i { font-size:12px; }.gmFaq-tab:hover { color:rgba(191,219,254,.90); background:rgba(255,255,255,.06); }.gmFaq-tab.is-active {
  background:linear-gradient(135deg,#1d4ed8,#3b82f6);
  color:#fff;
  box-shadow:0 8px 24px rgba(29,78,216,.35);
}@media(max-width:680px){.gmFaq-tabs { flex-wrap:wrap; border-radius:20px; padding:8px; width:100%; justify-content:center; }.gmFaq-tab  { height:36px; padding:0 14px; font-size:12px; }}.gmFaq-grid {
  display:grid;
  grid-template-columns:340px 1fr;
  gap:28px;
  align-items:start;
}@media(max-width:1000px){.gmFaq-grid { grid-template-columns:1fr; }}.gmFaq-contact {
  background:linear-gradient(150deg, rgba(8,16,40,.95), rgba(4,8,22,.95));
  border:1px solid rgba(96,165,250,.20);
  border-radius:24px;
  padding:32px 28px;
  box-shadow:0 20px 60px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.06);
  position:relative;
  overflow:hidden;
}.gmFaq-contact::before {
  content:"";
  position:absolute;
  inset:-1px;
  pointer-events:none;
  border-radius:inherit;
  background:radial-gradient(380px 220px at 10% 0%, rgba(30,80,220,.22), transparent 60%);
}.gmFaq-contactIcon {
  position:relative;
  width:52px; height:52px;
  border-radius:16px;
  display:grid; place-items:center;
  background:linear-gradient(135deg,#1d4ed8,#3b82f6);
  border:1px solid rgba(96,165,250,.40);
  box-shadow:0 12px 30px rgba(29,78,216,.30);
  font-size:20px; color:#fff;
  margin-bottom:20px;
}.gmFaq-contactTitle {
  position:relative;
  margin:0 0 12px;
  font-size:clamp(22px,2.2vw,28px);
  font-weight:900;
  letter-spacing:-.03em;
  line-height:1.15;
  color:#fff;
}.gmFaq-contactText {
  position:relative;
  margin:0 0 24px;
  color:rgba(191,219,254,.62);
  font-size:15px;
  line-height:1.65;
}.gmFaq-contactBtns {
  position:relative;
  display:flex;
  flex-direction:column;
  gap:10px;
  margin-bottom:28px;
}.gmFaq-btnPrimary,
.gmFaq-btnGhost {
  display:flex; align-items:center; justify-content:center;
  gap:9px; height:48px; border-radius:14px;
  font-size:14px; font-weight:800;
  text-decoration:none; cursor:pointer;
  transition:filter .18s, transform .18s, box-shadow .18s;
}.gmFaq-btnPrimary {
  background:linear-gradient(135deg,#1d4ed8,#3b82f6);
  color:#fff;
  border:1px solid rgba(96,165,250,.40);
  box-shadow:0 10px 30px rgba(29,78,216,.30);
}.gmFaq-btnPrimary:hover { filter:brightness(1.10); transform:translateY(-1px); }.gmFaq-btnGhost {
  background:rgba(255,255,255,.06);
  color:rgba(255,255,255,.82);
  border:1px solid rgba(255,255,255,.12);
}.gmFaq-btnGhost:hover { background:rgba(255,255,255,.10); transform:translateY(-1px); }.gmFaq-stats {
  position:relative;
  list-style:none;
  margin:0; padding:0;
  border-top:1px solid rgba(255,255,255,.08);
  padding-top:22px;
  display:flex;
  flex-direction:column;
  gap:16px;
}.gmFaq-stats li {
  display:flex;
  align-items:center;
  gap:14px;
}.gmFaq-stats li > i {
  width:36px; height:36px; flex:0 0 36px;
  display:grid; place-items:center;
  border-radius:12px;
  background:rgba(30,80,220,.20);
  border:1px solid rgba(96,165,250,.22);
  color:#93c5fd;
  font-size:14px;
}.gmFaq-stats li > span { display:flex; flex-direction:column; gap:2px; }.gmFaq-stats li b    { font-size:14px; font-weight:800; color:#fff; }.gmFaq-stats li small{ font-size:12px; color:rgba(191,219,254,.55); }.gmFaq-accordion {
  background:linear-gradient(150deg, rgba(8,16,40,.95), rgba(4,8,22,.95));
  border:1px solid rgba(96,165,250,.18);
  border-radius:24px;
  padding:10px;
  box-shadow:0 20px 60px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.05);
}.gmFaq-item {
  border-radius:16px;
  margin-bottom:6px;
  border:1px solid rgba(255,255,255,.07);
  background:rgba(255,255,255,.03);
  overflow:hidden;
  transition:border-color .2s, background .2s;
}.gmFaq-item:last-child { margin-bottom:0; }.gmFaq-item.open {
  border-color:rgba(96,165,250,.28);
  background:rgba(30,80,220,.08);
}.gmFaq-btn {
  width:100%; display:flex; align-items:center;
  justify-content:space-between; gap:12px;
  padding:18px 20px;
  background:none; border:none; color:#fff;
  font-size:15.5px; font-weight:800;
  text-align:left; cursor:pointer;
  transition:background .15s;
}.gmFaq-btn:hover { background:rgba(255,255,255,.03); }.gmFaq-chev {
  width:30px; height:30px; flex:0 0 30px;
  display:grid; place-items:center;
  border-radius:10px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.10);
  color:rgba(191,219,254,.60);
  font-size:11px;
  transition:transform .25s;
}.gmFaq-item.open .gmFaq-chev { transform:rotate(180deg); }.gmFaq-item.open .gmFaq-chev i { color:#93c5fd; }.gmFaq-panel {
  max-height:0; overflow:hidden;
  transition:max-height .30s ease;
}.gmFaq-item.open .gmFaq-panel { max-height:300px; }.gmFaq-inner {
  padding:0 20px 20px;
  color:rgba(191,219,254,.65);
  font-size:15px;
  line-height:1.72;
}@media(max-width:680px){.gm-supportfaq.gm-faqRebuild { padding:72px 0 80px !important; }.gmFaq-head { margin-bottom:36px; }.gmFaq-title { font-size:clamp(40px,12vw,56px); }.gmFaq-contact { padding:24px 20px; }.gmFaq-accordion { padding:8px; }.gmFaq-btn { font-size:14.5px; padding:16px 14px; }}@media(max-width:768px){.gm-reviewCard {
    flex:0 0 280px !important;
    padding:18px 20px !important;
  }.gm-marquee .gm-fadeL,
  .gm-marquee .gm-fadeR {
    width:40px !important;
  }.gm-reviewsSection {
    overflow:hidden !important;
  }.gm-track {
    gap:10px !important;
  }.lbHybridStep {
    position:relative !important;
  }.lbHybridStep::before {
    display:none !important;
    content:none !important;
  }}.gm-tpStars {
  display:flex;
  align-items:center;
  flex-shrink:0;
}.gm-tpInfo {
  display:inline-flex;
  align-items:center;
  gap:5px;
  font-size:12.5px;
  font-weight:700;
}.gm-tpExcellent { color:#fff; font-weight:800; }.gm-tpDivider   { color:rgba(255,255,255,.35); font-weight:400; }.gm-tpReviews   { color:rgba(255,255,255,.65); }.gm-tpLogo {
  display:inline-flex;
  align-items:center;
  gap:4px;
  color:#00b67a;
  font-weight:800;
}.gm-tpLogo b { color:#00b67a; }@media(max-width:480px){.gm-tpReviews, .gm-tpDivider:last-of-type { display:none; }}.gm-tpStars  { color:#00b67a; font-size:11px; letter-spacing:1px; }.gm-tpExcellent { color:#fff; font-weight:800; }.gm-tpSep    { color:rgba(255,255,255,.30); font-weight:400; }.gm-tpCount  { color:rgba(255,255,255,.55); }.gm-tpName   { color:#00b67a; }.gm-tpStars   { color:#00b67a !important; font-size:10px !important; letter-spacing:1px !important; }.gm-tpExcellent { color:#fff !important; font-weight:800 !important; }.gm-tpSep     { color:rgba(255,255,255,.28) !important; }.gm-tpCount   { color:rgba(255,255,255,.50) !important; }.gm-tpName    { color:#00b67a !important; font-weight:800 !important; }@media(max-width:768px){.gm-tpCount { display:none !important; }}@keyframes gmScrollBounce {
  0%,100% { transform:translateY(0); }
  50%      { transform:translateY(8px); }
}@keyframes gmScrollDot {
  0%   { top:6px; opacity:1; }
  75%  { top:22px; opacity:0; }
  100% { top:6px; opacity:0; }
}@media (max-width: 640px) {body.landing.gamingmarket .gm-tpStars,
  body.landing .gm-tpStars { color: #00b67a !important; letter-spacing: 1px !important; }}@media (max-width: 640px) {body.landing.gamingmarket .gm-tpStars,
  body.landing .gm-tpStars {
    color: #00e090 !important;
    font-size: 11px !important;
    line-height: 1 !important;
    letter-spacing: .8px !important;
    text-shadow: 0 0 12px rgba(0, 224, 144, .28) !important;
  }body.landing.gamingmarket .gm-tpExcellent,
  body.landing .gm-tpExcellent {
    color: rgba(255, 255, 255, .90) !important;
    font-size: 11.5px !important;
    font-weight: 900 !important;
  }body.landing.gamingmarket .gm-tpSep,
  body.landing .gm-tpSep {
    display: none !important;
  }body.landing.gamingmarket .gm-tpName,
  body.landing .gm-tpName {
    color: rgba(255, 255, 255, .92) !important;
    font-size: 11.5px !important;
    font-weight: 900 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (max-width:768px){html,
  body.landing,
  body.landing.gamingmarket{
    overflow-x:hidden !important;
    background:
      radial-gradient(520px 420px at 50% 0%, rgba(168,85,247,.34), transparent 68%),
      radial-gradient(420px 360px at 50% 34%, rgba(217,70,239,.14), transparent 72%),
      linear-gradient(180deg,#10051f 0%,#160927 46%,#080716 100%) !important;
    background-attachment:scroll !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (max-width:768px){html,
  body.landing,
  body.landing.gamingmarket{
    background:
      radial-gradient(680px 520px at 18% 4%, rgba(79,110,247,.18), transparent 62%),
      radial-gradient(620px 520px at 96% 22%, rgba(56,189,248,.10), transparent 60%),
      linear-gradient(180deg,#04050f 0%,#070a1a 55%,#04050f 100%) !important;
    background-attachment:scroll !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (max-width:640px){body.landing.gamingmarket .gm-tpStars,
  body.landing .gm-tpStars{
    color:#00b67a !important;
    font-size:12px !important;
    letter-spacing:.03em !important;
    line-height:1 !important;
  }body.landing.gamingmarket .gm-tpExcellent,
  body.landing .gm-tpExcellent,
  body.landing.gamingmarket .gm-tpName,
  body.landing .gm-tpName{
    color:rgba(245,255,250,.92) !important;
    font-size:12.5px !important;
    font-weight:950 !important;
    line-height:1 !important;
  }body.landing.gamingmarket .gm-tpSep,
  body.landing .gm-tpSep{
    color:rgba(245,255,250,.38) !important;
  }}.gm-supportfaq.gm-faqRebuild{
  padding:110px 0 120px !important;
  background:
    radial-gradient(900px 520px at 50% 0%, rgba(37,99,235,.18), transparent 64%),
    radial-gradient(720px 420px at 12% 18%, rgba(96,165,250,.10), transparent 62%),
    radial-gradient(720px 420px at 88% 22%, rgba(14,165,233,.09), transparent 62%),
    linear-gradient(180deg,#050817 0%,#071022 52%,#050817 100%) !important;
  overflow:hidden !important;
  position:relative !important;
}.gm-supportfaq.gm-faqRebuild::before{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background-image:radial-gradient(circle at 1px 1px, rgba(191,219,254,.28) 0, rgba(191,219,254,0) 2px);
  background-size:74px 74px;
  opacity:.10;
  -webkit-mask-image:radial-gradient(closest-side at 50% 28%, #000 0%, transparent 78%);
  mask-image:radial-gradient(closest-side at 50% 28%, #000 0%, transparent 78%);
}.gm-supportfaq.gm-faqRebuild .gm-wrap{
  position:relative !important;
  z-index:1 !important;
  max-width:980px !important;
}.gmFaq-head{
  text-align:center !important;
  margin:0 auto 22px !important;
}.gmFaq-head .gm-sectionTag{
  justify-content:center !important;
  margin-bottom:18px !important;
  color:rgba(125,211,252,.86) !important;
  letter-spacing:.20em !important;
  font-size:10px !important;
}.gmFaq-head .gm-sectionTag span{
  background:linear-gradient(90deg, transparent, #38bdf8) !important;
}.gmFaq-title{
  margin:0 0 14px !important;
  font-size:clamp(42px,5.2vw,66px) !important;
  line-height:.98 !important;
  letter-spacing:-.055em !important;
  color:#f8fbff !important;
  text-shadow:0 18px 60px rgba(0,0,0,.55), 0 0 42px rgba(59,130,246,.16) !important;
}.gmFaq-lead{
  max-width:640px !important;
  margin:0 auto 24px !important;
  color:rgba(219,234,254,.66) !important;
  font-size:17px !important;
  line-height:1.65 !important;
}.gmFaq-topActions{
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:12px !important;
  margin:0 auto 22px !important;
  flex-wrap:wrap !important;
}.gmFaq-topBtn{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  min-height:42px !important;
  padding:0 22px !important;
  border-radius:999px !important;
  text-decoration:none !important;
  font-size:13px !important;
  font-weight:900 !important;
  transition:transform .18s ease, filter .18s ease, border-color .18s ease !important;
}.gmFaq-topBtnPrimary{
  color:#fff !important;
  background:linear-gradient(135deg,#1d4ed8,#3b82f6,#38bdf8) !important;
  border:1px solid rgba(96,165,250,.46) !important;
  box-shadow:0 16px 42px rgba(37,99,235,.32) !important;
}.gmFaq-topBtnGhost{
  color:rgba(239,246,255,.88) !important;
  background:rgba(255,255,255,.055) !important;
  border:1px solid rgba(191,219,254,.16) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.07) !important;
}.gmFaq-topBtn:hover{
  transform:translateY(-1px) !important;
  filter:brightness(1.08) !important;
}.gmFaq-tabs{
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  padding:0 !important;
  margin:0 auto 30px !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
  flex-wrap:wrap !important;
}.gmFaq-tab{
  height:32px !important;
  padding:0 16px !important;
  border-radius:999px !important;
  background:rgba(255,255,255,.055) !important;
  border:1px solid rgba(191,219,254,.14) !important;
  color:rgba(219,234,254,.72) !important;
  font-size:11.5px !important;
  font-weight:900 !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06) !important;
}.gmFaq-tab i{
  font-size:11px !important;
  color:#93c5fd !important;
}.gmFaq-tab:hover,
.gmFaq-tab.is-active{
  color:#fff !important;
  background:linear-gradient(135deg,#2563eb,#3b82f6) !important;
  border-color:rgba(96,165,250,.48) !important;
  box-shadow:0 16px 36px rgba(37,99,235,.28), inset 0 1px 0 rgba(255,255,255,.12) !important;
}.gmFaq-tab.is-active i{ color:#fff !important; }.gmFaq-grid{
  display:block !important;
  max-width:820px !important;
  margin:0 auto !important;
}.gmFaq-contact{
  display:none !important;
}.gmFaq-accordion{
  width:100% !important;
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  padding:0 !important;
  box-shadow:none !important;
}.gmFaq-item{
  margin:0 0 12px !important;
  border-radius:18px !important;
  background:linear-gradient(180deg, rgba(10,16,38,.88), rgba(7,11,28,.84)) !important;
  border:1px solid rgba(191,219,254,.12) !important;
  box-shadow:0 18px 48px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.045) !important;
  overflow:hidden !important;
}.gmFaq-item.open{
  border-color:rgba(96,165,250,.48) !important;
  background:linear-gradient(180deg, rgba(12,24,58,.95), rgba(8,14,34,.90)) !important;
  box-shadow:0 24px 70px rgba(0,0,0,.38), 0 0 0 1px rgba(37,99,235,.12), inset 0 1px 0 rgba(255,255,255,.07) !important;
}.gmFaq-btn{
  min-height:88px !important;
  padding:26px 38px !important;
  font-size:16px !important;
  font-weight:900 !important;
  color:#f8fbff !important;
}.gmFaq-inner{
  padding:0 38px 32px !important;
  max-width:620px !important;
  color:rgba(219,234,254,.68) !important;
  font-size:14.5px !important;
  line-height:1.75 !important;
}.gmFaq-chev{
  width:34px !important;
  height:34px !important;
  flex:0 0 34px !important;
  border-radius:12px !important;
  background:rgba(255,255,255,.06) !important;
  border:1px solid rgba(191,219,254,.14) !important;
  color:rgba(219,234,254,.70) !important;
}.gmFaq-item.open .gmFaq-chev{
  background:rgba(37,99,235,.24) !important;
  border-color:rgba(96,165,250,.32) !important;
}.gmFaq-item.open .gmFaq-panel{
  max-height:360px !important;
}@media(max-width:680px){.gm-supportfaq.gm-faqRebuild{
    padding:72px 0 82px !important;
  }.gm-supportfaq.gm-faqRebuild .gm-wrap{
    padding:0 16px !important;
  }.gmFaq-head{
    margin-bottom:18px !important;
  }.gmFaq-title{
    font-size:clamp(38px,12vw,52px) !important;
  }.gmFaq-lead{
    font-size:15px !important;
    line-height:1.62 !important;
    margin-bottom:20px !important;
  }.gmFaq-lead br{ display:none !important; }.gmFaq-topActions{
    gap:10px !important;
    margin-bottom:18px !important;
  }.gmFaq-topBtn{
    min-height:40px !important;
    padding:0 16px !important;
    font-size:11.5px !important;
  }.gmFaq-tabs{
    justify-content:flex-start !important;
    flex-wrap:nowrap !important;
    overflow-x:auto !important;
    -webkit-overflow-scrolling:touch !important;
    scrollbar-width:none !important;
    padding:0 2px 8px !important;
    margin-bottom:16px !important;
  }.gmFaq-tabs::-webkit-scrollbar{display:none !important;}.gmFaq-tab{
    flex:0 0 auto !important;
    height:34px !important;
    padding:0 14px !important;
    font-size:11px !important;
  }.gmFaq-item{
    margin-bottom:10px !important;
    border-radius:16px !important;
  }.gmFaq-btn{
    min-height:70px !important;
    padding:20px 16px !important;
    font-size:14.5px !important;
  }.gmFaq-inner{
    padding:0 16px 22px !important;
    font-size:13.5px !important;
  }.gmFaq-chev{
    width:30px !important;
    height:30px !important;
    flex-basis:30px !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}:root{
  --lb-blue-main:#3b82f6;
  --lb-blue-soft:#60a5fa;
  --lb-blue-cyan:#7dd3fc;
  --lb-blue-dark:#071225;
  --lb-blue-panel:#0a1022;
  --lb-blue-border:rgba(96,165,250,.34);
  --lb-blue-glow:rgba(59,130,246,.24);
}.navbar-top,
.navbar-mobile,
.navbar-container,
.navbar-menu,
.mobile-menu,
.navbar-actions{
  --gm-accent:#3b82f6;
  --gm-accent2:#60a5fa;
}.navbar-mobile,
.mobile-navbar,
.lb-mobile-gamebar,
.lb-mobile-bottomnav{
  background:linear-gradient(180deg, rgba(8,14,30,.94), rgba(5,9,22,.90)) !important;
  border-color:rgba(96,165,250,.14) !important;
  box-shadow:0 18px 46px rgba(0,0,0,.34), 0 0 32px rgba(59,130,246,.10) !important;
}.navbar-top a:hover,
.navbar-top button:hover,
.navbar-mobile a:hover,
.navbar-mobile button:hover{
  border-color:rgba(96,165,250,.32) !important;
}.gmUnifiedSearchMini,
.gmHeaderSearchTrigger,
.lb-header-search__box{
  background:linear-gradient(180deg, rgba(12,20,42,.92), rgba(7,12,28,.86)) !important;
  border-color:rgba(96,165,250,.20) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05), 0 0 0 1px rgba(59,130,246,.08) !important;
}.gmUnifiedSearchMini:hover,
.gmHeaderSearchTrigger:hover,
.lb-header-search__box:focus-within{
  border-color:rgba(96,165,250,.48) !important;
  box-shadow:0 0 0 3px rgba(59,130,246,.14), inset 0 1px 0 rgba(255,255,255,.06) !important;
}.gmUnifiedSearchMini i,
.gmHeaderSearchTriggerIcon,
.lb-header-search__box i{
  color:#bfdbfe !important;
  background:rgba(59,130,246,.14) !important;
}.gmHeaderSearchOverlay{
  background:rgba(3,8,20,.82) !important;
}.gmHeaderSearchModal{
  background:linear-gradient(180deg, rgba(9,17,38,.98), rgba(5,10,24,.98)) !important;
  border-color:rgba(96,165,250,.34) !important;
  box-shadow:0 36px 120px rgba(0,0,0,.74), 0 0 90px rgba(59,130,246,.16) !important;
}.gmHeaderSearchTop{
  background:linear-gradient(180deg, rgba(96,165,250,.08), rgba(255,255,255,.02)) !important;
  border-bottom-color:rgba(96,165,250,.13) !important;
}.gmHeaderSearchInputWrap{
  background:#071225 !important;
  border-color:rgba(96,165,250,.40) !important;
  box-shadow:0 0 0 4px rgba(59,130,246,.13), inset 0 1px 0 rgba(255,255,255,.05) !important;
}.gmHeaderSearchInputWrap i,
.gmHeaderResultIcon{
  color:#bfdbfe !important;
  background:rgba(59,130,246,.16) !important;
  border-color:rgba(96,165,250,.18) !important;
}.gmHeaderSearchClose:hover,
.gmHeaderResult:hover,
.gmHeaderResult.is-active{
  background:rgba(59,130,246,.17) !important;
  border-color:rgba(96,165,250,.52) !important;
}.gmHeaderSearchResults::-webkit-scrollbar-thumb{
  background:#3b82f6 !important;
}.gmHeaderSearchSection h3 small{
  color:#7dd3fc !important;
}.gmHeaderGameAction{
  background:linear-gradient(135deg, rgba(37,99,235,.92), rgba(14,165,233,.82)) !important;
  border-color:rgba(125,211,252,.34) !important;
}.gmHeaderGameAction:hover{
  background:linear-gradient(135deg, #2563eb, #38bdf8) !important;
}.gmHeaderResultTag,
.lb-header-search__item:hover,
.lb-header-search__item.is-active{
  background:rgba(59,130,246,.12) !important;
  border-color:rgba(96,165,250,.28) !important;
}.mega-dropdown,
.mega-dropdown.gmBoostDropdownV2,
.navbar-top .mega-dropdown{
  background:linear-gradient(180deg, rgba(9,16,34,.98), rgba(5,9,22,.98)) !important;
  border-color:rgba(96,165,250,.20) !important;
  box-shadow:0 34px 100px rgba(0,0,0,.58), 0 0 70px rgba(59,130,246,.12) !important;
}.mega-dropdown.gmBoostDropdownV2 .gmBoostGameItem.is-active,
.mega-dropdown.gmBoostDropdownV2 .mega-pill:hover,
.mega-dropdown.gmBoostDropdownV2 .gmTrustItem:hover{
  background:rgba(59,130,246,.14) !important;
  border-color:rgba(96,165,250,.38) !important;
  box-shadow:0 16px 34px rgba(0,0,0,.32), 0 0 28px rgba(59,130,246,.14) !important;
}.mega-dropdown.gmBoostDropdownV2 .gmSectionChip i,
.mega-dropdown.gmBoostDropdownV2 .gmBoostRightHead i,
.mega-dropdown.gmBoostDropdownV2 .gmBoostLeftHead i{
  color:#93c5fd !important;
  filter:drop-shadow(0 0 8px rgba(96,165,250,.55)) !important;
}.mega-dropdown.gmBoostDropdownV2 .gmSectionChip--left::after,
.mega-dropdown.gmBoostDropdownV2 .gmSectionChip--center::before,
.mega-dropdown.gmBoostDropdownV2 .gmSectionChip--center::after{
  background:linear-gradient(90deg, transparent, rgba(96,165,250,.38), transparent) !important;
}.site-settings-overlay{
  background:rgba(3,8,20,.76) !important;
}.site-settings-modal{
  background:linear-gradient(180deg, #0a1022, #060b19) !important;
  border-color:rgba(96,165,250,.24) !important;
  box-shadow:0 28px 90px rgba(0,0,0,.78), 0 0 70px rgba(59,130,246,.13) !important;
}.settings-icon,
.settings-select,
.currency-segment,
.settings-options.is-open{
  background:rgba(59,130,246,.10) !important;
  border-color:rgba(96,165,250,.22) !important;
}.currency-btn.active,
.settings-options .settings-option.active,
.settings-btn.primary{
  background:linear-gradient(135deg, #2563eb, #3b82f6) !important;
  border-color:rgba(96,165,250,.36) !important;
  color:#fff !important;
}.settings-pill{
  background:linear-gradient(180deg, rgba(9,16,34,.96), rgba(5,9,22,.94)) !important;
  border-color:rgba(96,165,250,.22) !important;
}@media(max-width:900px){.gmHeaderSearchOverlay{
    background:#050b18 !important;
  }.gmHeaderSearchModal{
    background:linear-gradient(180deg, #081226 0%, #050b18 100%) !important;
    border-color:rgba(96,165,250,.22) !important;
    box-shadow:none !important;
  }.gmHeaderSearchTop{
    background:rgba(8,18,38,.96) !important;
  }.gmHeaderResult{
    background:linear-gradient(180deg, rgba(96,165,250,.075), rgba(255,255,255,.025)) !important;
    border-color:rgba(96,165,250,.13) !important;
  }}.lbHybridBoosterRoles{
  display:flex !important;
  align-items:center !important;
  flex-wrap:wrap !important;
  gap:7px !important;
  min-height:32px !important;
  margin:2px 0 0 !important;
}.lbHybridBoosterRolePill,
.lbHybridBoosterRolePill--jungle,
.lbHybridBoosterRolePill--midlane,
.lbHybridBoosterRolePill--mid,
.lbHybridBoosterRolePill--middle,
.lbHybridBoosterRolePill--adcarry,
.lbHybridBoosterRolePill--adc,
.lbHybridBoosterRolePill--bot,
.lbHybridBoosterRolePill--support,
.lbHybridBoosterRolePill--toplane,
.lbHybridBoosterRolePill--top{
  width:30px !important;
  height:30px !important;
  min-width:30px !important;
  padding:0 !important;
  border-radius:10px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  background:rgba(96,165,250,.085) !important;
  border:1px solid rgba(96,165,250,.22) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.07), 0 8px 18px rgba(0,0,0,.18) !important;
  font-size:0 !important;
  line-height:0 !important;
  overflow:hidden !important;
}.lbHybridBoosterRoleIcon{
  width:18px !important;
  height:18px !important;
  object-fit:contain !important;
  flex:0 0 18px !important;
  display:block !important;
  opacity:.92 !important;
  filter:brightness(0) saturate(100%) invert(71%) sepia(60%) saturate(1075%) hue-rotate(184deg) brightness(104%) contrast(101%) drop-shadow(0 4px 8px rgba(0,0,0,.35)) !important;
}.lbHybridBoosterRolePill:hover{
  background:rgba(96,165,250,.13) !important;
  border-color:rgba(96,165,250,.34) !important;
}.lbHybridBoostersMobileMore{
  display:none;
}@media (max-width:767px){body.landing.gamingmarket .gm-marketSection,
  body.landing .gm-marketSection{
    padding:58px 0 64px !important;
  }body.landing.gamingmarket .gm-marketSection .gm-wrap,
  body.landing .gm-marketSection .gm-wrap{
    padding:0 18px !important;
  }body.landing.gamingmarket .gm-marketHeader,
  body.landing .gm-marketHeader{
    margin-bottom:20px !important;
  }body.landing.gamingmarket .gm-marketSection .gm-sectionTag,
  body.landing .gm-marketSection .gm-sectionTag{
    margin-bottom:12px !important;
    font-size:11px !important;
    letter-spacing:.16em !important;
  }body.landing.gamingmarket .gm-marketHeadGrid,
  body.landing .gm-marketHeadGrid{
    display:block !important;
    margin-bottom:20px !important;
  }body.landing.gamingmarket .gm-marketTitle,
  body.landing .gm-marketTitle{
    max-width:330px !important;
    font-size:clamp(30px, 8.8vw, 38px) !important;
    line-height:1.08 !important;
    letter-spacing:-.045em !important;
    margin:0 0 12px !important;
  }body.landing.gamingmarket .gm-marketSub,
  body.landing .gm-marketSub{
    max-width:32ch !important;
    margin:0 !important;
    font-size:15px !important;
    line-height:1.55 !important;
    color:rgba(220,232,255,.68) !important;
  }body.landing.gamingmarket .gm-marketBtn,
  body.landing .gm-marketBtn{
    display:none !important;
  }body.landing.gamingmarket .gm-serviceTiles,
  body.landing .gm-serviceTiles{
    gap:10px !important;
  }body.landing.gamingmarket .gm-serviceTile,
  body.landing .gm-serviceTile{
    min-height:0 !important;
    padding:16px !important;
    border-radius:20px !important;
    gap:12px !important;
  }body.landing.gamingmarket .gm-serviceTile h3,
  body.landing .gm-serviceTile h3{
    font-size:19px !important;
    margin-bottom:5px !important;
  }body.landing.gamingmarket .gm-serviceTile p,
  body.landing .gm-serviceTile p{
    font-size:13.5px !important;
    line-height:1.5 !important;
  }body.landing.gamingmarket .gm-tileIcon,
  body.landing .gm-tileIcon{
    width:46px !important;
    height:46px !important;
    border-radius:14px !important;
    font-size:17px !important;
  }body.landing.gamingmarket .lbHybridBoosters,
  body.landing .lbHybridBoosters{
    padding:62px 0 !important;
  }body.landing.gamingmarket .lbHybridBoosters .lbHybridSplit,
  body.landing .lbHybridBoosters .lbHybridSplit{
    margin-bottom:22px !important;
  }body.landing.gamingmarket .lbHybridBoosters .lbHybridSplit > .gm-btn,
  body.landing .lbHybridBoosters .lbHybridSplit > .gm-btn{
    display:none !important;
  }body.landing.gamingmarket .lbHybridBoosterGrid,
  body.landing .lbHybridBoosterGrid{
    grid-template-columns:1fr !important;
    gap:14px !important;
  }body.landing.gamingmarket .lbHybridBoosterGrid .lbHybridBooster:nth-child(n/**/+4),
  body.landing .lbHybridBoosterGrid .lbHybridBooster:nth-child(n/**/+4){
    display:none !important;
  }body.landing.gamingmarket .lbHybridBooster,
  body.landing .lbHybridBooster{
    min-height:0 !important;
    padding:18px !important;
    border-radius:22px !important;
  }body.landing.gamingmarket .lbHybridBoostersMobileMore,
  body.landing .lbHybridBoostersMobileMore{
    display:flex !important;
    justify-content:center !important;
    margin-top:18px !important;
  }body.landing.gamingmarket .lbHybridBoostersMobileMore .gm-btn,
  body.landing .lbHybridBoostersMobileMore .gm-btn{
    width:100% !important;
    max-width:340px !important;
    min-height:52px !important;
    justify-content:center !important;
    border-radius:17px !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){body.landing.gamingmarket #boosting,
  body.landing #boosting{
    margin-top:0 !important;
  }}@media (min-width:769px){html body.landing.gamingmarket #boosting,
  html body.landing #boosting{
    margin-top:0 !important;
  }}html{scroll-behavior:smooth;}@media (min-width:769px){html body.landing.gamingmarket .gm-section#boosting,
  html body.landing #boosting.gm-section{
    margin-top:-1px !important;
    padding-top:96px !important;
    background:#030817 !important;
    position:relative !important;
    overflow:hidden !important;
  }html body.landing.gamingmarket .gm-section#boosting:before,
  html body.landing #boosting.gm-section:before{
    content:"" !important;
    position:absolute !important;
    left:0 !important;
    right:0 !important;
    top:0 !important;
    height:150px !important;
    background:linear-gradient(180deg,rgba(2,8,22,.96),rgba(2,8,22,.48) 55%,transparent) !important;
    pointer-events:none !important;
    z-index:0 !important;
  }html body.landing.gamingmarket .gm-section#boosting > *,
  html body.landing #boosting.gm-section > *{
    position:relative !important;
    z-index:1 !important;
  }}@media (max-width:768px){html body.landing.gamingmarket .gm-section#boosting,
  html body.landing #boosting.gm-section{
    margin-top:-1px !important;
    background:linear-gradient(180deg,#020816 0%,#040819 22%,#05091b 100%) !important;
  }}@media(max-width:820px){body.landing.gamingmarket .gm-bg,
  body.landing .gm-bg,
  body.landing.gamingmarket .gm-gridlines,
  body.landing .gm-gridlines,
  body.landing.gamingmarket #gmStars,
  body.landing #gmStars{
    display:none !important;
  }}

/* LOLBOOST HERO, single source of truth */
html body.landing.gamingmarket{
  overflow-x:hidden;
}
body.landing.gamingmarket .gm-heroV4,
body.landing.gamingmarket .lb-visualHero{
  position:relative !important;
  min-height:calc(114svh - var(--lb-sale-h,0px)) !important;
  height:calc(114svh - var(--lb-sale-h,0px)) !important;
  padding-top:var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,86px))) !important;
  padding-bottom:34px !important;
  overflow:visible !important;
  isolation:isolate !important;
  background:#020814 !important;
  box-sizing:border-box !important;
}
body.landing.gamingmarket .gm-heroV4::before,
body.landing.gamingmarket .gm-heroV4::after{
  display:none !important;
  content:none !important;
}
body.landing.gamingmarket .lb-visualHero__bg{
  position:absolute !important;
  inset:0 !important;
  z-index:-3 !important;
  background-image:url('/public/assets/website/images/landing/lolboost-hero-multigame6.webp') !important;
  background-size:cover !important;
  background-position:center top !important;
  background-repeat:no-repeat !important;
  transform:scale(1.01) !important;
  filter:saturate(1.06) contrast(1.04) !important;
}
body.landing.gamingmarket .lb-visualHero__shade{
  position:absolute !important;
  inset:0 !important;
  z-index:-2 !important;
  pointer-events:none !important;
  background:radial-gradient(760px 420px at 50% 42%, rgba(18,90,200,.18), transparent 70%), linear-gradient(90deg, rgba(2,8,20,.50) 0%, rgba(2,8,20,.25) 26%, rgba(2,8,20,.25) 74%, rgba(2,8,20,.54) 100%), linear-gradient(180deg, rgba(2,8,20,.62) 0%, rgba(2,8,20,.12) 34%, rgba(2,8,20,.34) 68%, #020814 100%) !important;
}
body.landing.gamingmarket .lb-visualHero__sparks{
  position:absolute !important;
  inset:-18% !important;
  z-index:-1 !important;
  pointer-events:none !important;
  background:radial-gradient(circle at 18% 28%, rgba(47,140,255,.16), transparent 16%), radial-gradient(circle at 82% 32%, rgba(47,140,255,.12), transparent 18%), radial-gradient(circle at 50% 74%, rgba(47,140,255,.12), transparent 18%) !important;
  filter:blur(18px) !important;
  opacity:.58 !important;
}
body.landing.gamingmarket .gm-heroV4Inner,
body.landing.gamingmarket .lb-visualHero__inner{
  position:relative !important;
  z-index:2 !important;
  width:min(100%,1560px) !important;
  min-height:calc(114svh - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,86px))) - 34px) !important;
  height:calc(114svh - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,86px))) - 34px) !important;
  margin:0 auto !important;
  padding:0 clamp(28px,4.2vw,68px) !important;
  display:flex !important;
  flex-direction:column !important;
  align-items:center !important;
  justify-content:center !important;
  text-align:center !important;
  gap:0 !important;
  transform:translateY(2.5svh) !important;
  box-sizing:border-box !important;
}
body.landing.gamingmarket .gm-heroV4Left,
body.landing.gamingmarket .lb-visualHero__content{
  width:100% !important;
  max-width:1180px !important;
  margin:0 auto !important;
  padding:0 !important;
  display:flex !important;
  flex-direction:column !important;
  align-items:center !important;
  text-align:center !important;
}
body.landing.gamingmarket .gm-heroV4Badge,
body.landing.gamingmarket .lb-visualHero__eyebrow{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  width:auto !important;
  margin:0 auto 22px !important;
  padding:9px 18px !important;
  gap:9px !important;
  border-radius:999px !important;
  color:rgba(255,255,255,.88) !important;
  border:1px solid rgba(96,165,250,.36) !important;
  background:rgba(5,18,42,.58) !important;
  box-shadow:0 16px 40px rgba(0,0,0,.26), inset 0 1px 0 rgba(255,255,255,.08) !important;
  letter-spacing:.18em !important;
  font-size:12px !important;
  line-height:1 !important;
  font-weight:900 !important;
  text-transform:uppercase !important;
  white-space:nowrap !important;
}
body.landing.gamingmarket .gm-heroV4Badge::before,
body.landing.gamingmarket .lb-visualHero__eyebrow::before{
  display:none !important;
  content:none !important;
}
body.landing.gamingmarket .gm-heroV4Badge .gm-dot,
body.landing.gamingmarket .lb-visualHero__eyebrow .gm-dot{
  width:7px !important;
  height:7px !important;
  flex:0 0 7px !important;
  border-radius:999px !important;
  background:#38bdf8 !important;
  box-shadow:0 0 0 6px rgba(56,189,248,.14), 0 0 18px rgba(56,189,248,.58) !important;
}
body.landing.gamingmarket .gm-heroV4H1,
body.landing.gamingmarket .lb-visualHero__title{
  width:100% !important;
  max-width:1240px !important;
  margin:0 auto 20px !important;
  display:flex !important;
  flex-wrap:nowrap !important;
  align-items:baseline !important;
  justify-content:center !important;
  gap:.20em !important;
  font-size:clamp(50px,4.45vw,78px) !important;
  line-height:1.04 !important;
  letter-spacing:-.052em !important;
  font-weight:950 !important;
  color:#fff !important;
  text-align:center !important;
  text-transform:uppercase !important;
  white-space:nowrap !important;
  text-shadow:0 20px 58px rgba(0,0,0,.56), 0 0 34px rgba(59,130,246,.12) !important;
}
body.landing.gamingmarket .gm-heroV4TitleMain,
body.landing.gamingmarket .gm-heroV4TitleAccent{
  display:inline-block !important;
  width:auto !important;
  max-width:none !important;
  font:inherit !important;
  line-height:inherit !important;
  letter-spacing:inherit !important;
  white-space:nowrap !important;
}
body.landing.gamingmarket .gm-heroV4TitleMain{
  color:#fff !important;
  -webkit-text-fill-color:#fff !important;
}
body.landing.gamingmarket .gm-heroV4TitleAccent{
  color:#38bdf8 !important;
  background-image:linear-gradient(92deg,#60a5fa 0%,#3b82f6 56%,#38bdf8 100%) !important;
  -webkit-background-clip:text !important;
  background-clip:text !important;
  -webkit-text-fill-color:transparent !important;
}
body.landing.gamingmarket .gm-heroV4Sub,
body.landing.gamingmarket .lb-visualHero__sub{
  width:100% !important;
  max-width:920px !important;
  margin:0 auto 32px !important;
  display:block !important;
  text-align:center !important;
  color:rgba(238,244,255,.82) !important;
  font-size:clamp(16px,1.02vw,19px) !important;
  line-height:1.58 !important;
  font-weight:800 !important;
  letter-spacing:-.015em !important;
  white-space:normal !important;
  text-shadow:0 10px 28px rgba(0,0,0,.48) !important;
}
body.landing.gamingmarket .gm-heroV4Actions,
body.landing.gamingmarket .lb-visualHero__actions{
  width:auto !important;
  max-width:none !important;
  margin:0 auto 22px !important;
  display:flex !important;
  flex-wrap:wrap !important;
  align-items:center !important;
  justify-content:center !important;
  gap:14px !important;
}
body.landing.gamingmarket .gm-heroV4Action,
body.landing.gamingmarket .lb-visualHero__btn{
  min-width:210px !important;
  width:auto !important;
  min-height:56px !important;
  padding:0 30px !important;
  border-radius:14px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:10px !important;
  box-sizing:border-box !important;
  border:1px solid rgba(96,165,250,.42) !important;
  color:#fff !important;
  text-decoration:none !important;
  font-size:14px !important;
  line-height:1 !important;
  font-weight:950 !important;
  text-transform:uppercase !important;
  box-shadow:0 16px 42px rgba(0,0,0,.26), inset 0 1px 0 rgba(255,255,255,.14) !important;
  transition:transform .18s ease, filter .18s ease, border-color .18s ease !important;
}
body.landing.gamingmarket .gm-heroV4Action:hover,
body.landing.gamingmarket .lb-visualHero__btn:hover{
  transform:translateY(-2px) !important;
  filter:brightness(1.08) !important;
}
body.landing.gamingmarket .gm-heroV4ActionPrimary,
body.landing.gamingmarket .lb-visualHero__btnPrimary{
  background:linear-gradient(135deg,#22a8f8 0%,#3b82f6 52%,#6d5dfc 100%) !important;
}
body.landing.gamingmarket .gm-heroV4ActionGhost,
body.landing.gamingmarket .lb-visualHero__btnGhost{
  background:rgba(3,13,31,.58) !important;
  border-color:rgba(96,165,250,.42) !important;
}
body.landing.gamingmarket .lb-visualHero__trustpilot{
  margin:2px auto 0 !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  color:#fff !important;
  font-size:12px !important;
  font-weight:900 !important;
}
body.landing.gamingmarket .lb-visualHero__tpStars{
  display:inline-flex !important;
  gap:3px !important;
  text-decoration:none !important;
}
body.landing.gamingmarket .lb-visualHero__tpStars span{
  width:22px !important;
  height:22px !important;
  display:grid !important;
  place-items:center !important;
  background:#00b67a !important;
  color:#fff !important;
  font-size:12px !important;
}
body.landing.gamingmarket .lb-visualHero__tpName{
  display:inline-flex !important;
  align-items:center !important;
  gap:5px !important;
  color:#fff !important;
  white-space:nowrap !important;
}
body.landing.gamingmarket .lb-visualHero__tpName i{
  color:#00b67a !important;
}
body.landing.gamingmarket .lb-visualHero__featureRow{
  width:min(100%,900px) !important;
  margin:78px auto 0 !important;
  display:grid !important;
  grid-template-columns:repeat(4,minmax(0,1fr)) !important;
  gap:0 !important;
}
body.landing.gamingmarket .lb-visualHero__feature{
  min-height:62px !important;
  padding:0 26px !important;
  display:grid !important;
  grid-template-columns:46px minmax(0,1fr) !important;
  grid-template-rows:auto auto !important;
  column-gap:16px !important;
  align-items:center !important;
  text-align:left !important;
  border-right:1px solid rgba(96,165,250,.22) !important;
}
body.landing.gamingmarket .lb-visualHero__feature:last-child{
  border-right:0 !important;
}
body.landing.gamingmarket .lb-visualHero__feature i{
  grid-row:1 / 3 !important;
  width:46px !important;
  height:46px !important;
  display:grid !important;
  place-items:center !important;
  color:#2ea7ff !important;
  font-size:30px !important;
  filter:drop-shadow(0 0 16px rgba(46,167,255,.48)) !important;
}
body.landing.gamingmarket .lb-visualHero__feature strong{
  display:block !important;
  color:#fff !important;
  font-size:12px !important;
  line-height:1.15 !important;
  font-weight:950 !important;
  text-transform:uppercase !important;
}
body.landing.gamingmarket .lb-visualHero__feature span{
  display:block !important;
  margin-top:4px !important;
  color:rgba(238,244,255,.62) !important;
  font-size:11px !important;
  line-height:1.35 !important;
  font-weight:600 !important;
}
body.landing.gamingmarket .gm-heroScrollDown,
body.landing.gamingmarket .lb-visualHero__scroll{
  position:absolute !important;
  left:50% !important;
  bottom:18px !important;
  width:36px !important;
  height:36px !important;
  transform:translateX(-50%) !important;
  display:grid !important;
  place-items:center !important;
  border-radius:999px !important;
  border:1px solid rgba(255,255,255,.16) !important;
  background:rgba(2,8,20,.44) !important;
  color:#fff !important;
  text-decoration:none !important;
}
body.landing.gamingmarket .gm-heroScrollDot{
  width:13px !important;
  height:13px !important;
  border-right:2px solid rgba(255,255,255,.62) !important;
  border-bottom:2px solid rgba(255,255,255,.62) !important;
  transform:rotate(45deg) translate(-2px,-2px) !important;
}
@media(max-width:1200px){
  body.landing.gamingmarket .gm-heroV4H1,
  body.landing.gamingmarket .lb-visualHero__title{
    font-size:clamp(44px,4.55vw,66px) !important;
  }
  body.landing.gamingmarket .lb-visualHero__featureRow{
    width:min(100%,820px) !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature{
    padding:0 18px !important;
  }
}
@media(max-width:820px){
  body.landing.gamingmarket .gm-bg,
  body.landing.gamingmarket .gm-gridlines,
  body.landing.gamingmarket #gmStars{
    display:none !important;
  }
  body.landing.gamingmarket .gm-heroV4,
  body.landing.gamingmarket .lb-visualHero{
    min-height:100svh !important;
    height:auto !important;
    padding-top:var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,74px))) !important;
    padding-bottom:30px !important;
  }
  body.landing.gamingmarket .lb-visualHero__bg{
    background-position:center top !important;
    transform:scale(1) !important;
  }
  body.landing.gamingmarket .lb-visualHero__shade{
    background:linear-gradient(180deg, rgba(2,8,20,.36) 0%, rgba(2,8,20,.16) 34%, rgba(2,8,20,.48) 72%, #020814 100%), linear-gradient(90deg, rgba(2,8,20,.34) 0%, rgba(2,8,20,.20) 54%, rgba(2,8,20,.38) 100%) !important;
  }
  body.landing.gamingmarket .gm-heroV4Inner,
  body.landing.gamingmarket .lb-visualHero__inner{
    min-height:calc(100svh - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,74px))) - 30px) !important;
    width:100% !important;
    padding:24px 22px !important;
    justify-content:center !important;
    transform:none !important;
  }
  body.landing.gamingmarket .gm-heroV4Left,
  body.landing.gamingmarket .lb-visualHero__content{
    max-width:420px !important;
  }
  body.landing.gamingmarket .gm-heroV4Badge,
  body.landing.gamingmarket .lb-visualHero__eyebrow{
    margin-bottom:18px !important;
    padding:8px 14px !important;
    font-size:10px !important;
    letter-spacing:.14em !important;
  }
  body.landing.gamingmarket .gm-heroV4H1,
  body.landing.gamingmarket .lb-visualHero__title{
    max-width:360px !important;
    margin-bottom:14px !important;
    display:flex !important;
    flex-direction:column !important;
    align-items:center !important;
    justify-content:center !important;
    gap:0 !important;
    font-size:clamp(42px,13.4vw,58px) !important;
    line-height:.92 !important;
    letter-spacing:-.06em !important;
    white-space:normal !important;
  }
  body.landing.gamingmarket .gm-heroV4TitleMain,
  body.landing.gamingmarket .gm-heroV4TitleAccent{
    display:block !important;
    white-space:nowrap !important;
  }
  body.landing.gamingmarket .gm-heroV4Sub,
  body.landing.gamingmarket .lb-visualHero__sub{
    max-width:330px !important;
    margin-bottom:22px !important;
    font-size:15px !important;
    line-height:1.46 !important;
    font-weight:750 !important;
  }
  body.landing.gamingmarket .gm-heroV4Actions,
  body.landing.gamingmarket .lb-visualHero__actions{
    width:100% !important;
    margin-bottom:14px !important;
    display:flex !important;
    flex-direction:column !important;
    align-items:center !important;
    justify-content:center !important;
    gap:10px !important;
  }
  body.landing.gamingmarket .gm-heroV4Action,
  body.landing.gamingmarket .lb-visualHero__btn{
    min-width:0 !important;
    width:min(305px,86vw) !important;
    min-height:45px !important;
    padding:0 24px !important;
    border-radius:12px !important;
    font-size:13px !important;
  }
  body.landing.gamingmarket .lb-visualHero__trustpilot{
    margin-top:4px !important;
    font-size:11px !important;
    gap:6px !important;
  }
  body.landing.gamingmarket .lb-visualHero__tpStars span{
    width:19px !important;
    height:19px !important;
    font-size:11px !important;
  }
  body.landing.gamingmarket .lb-visualHero__featureRow{
    width:min(100%,330px) !important;
    margin:24px auto 0 !important;
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    gap:16px 12px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature{
    min-height:46px !important;
    padding:0 !important;
    grid-template-columns:36px minmax(0,1fr) !important;
    column-gap:9px !important;
    border-right:0 !important;
    text-align:left !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature i{
    width:36px !important;
    height:36px !important;
    font-size:24px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature strong{
    font-size:10px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature span{
    font-size:10px !important;
    line-height:1.25 !important;
  }
  body.landing.gamingmarket .lb-visualHero__scroll{
    bottom:10px !important;
  }
}
@media(max-width:390px){
  body.landing.gamingmarket .gm-heroV4Inner,
  body.landing.gamingmarket .lb-visualHero__inner{
    padding-left:18px !important;
    padding-right:18px !important;
  }
  body.landing.gamingmarket .gm-heroV4H1,
  body.landing.gamingmarket .lb-visualHero__title{
    font-size:clamp(38px,12.6vw,50px) !important;
  }
  body.landing.gamingmarket .gm-heroV4Action,
  body.landing.gamingmarket .lb-visualHero__btn{
    width:min(292px,86vw) !important;
  }
}


/* ============================================================
   FINAL 1:1 HERO FULLSCREEN + SCROLL ANIMATION RESTORE
   ============================================================ */
@keyframes lbHeroScrollBounceClean{
  0%,100%{transform:translateX(-50%) translateY(0);}
  50%{transform:translateX(-50%) translateY(8px);}
}
@keyframes lbHeroChevronGlowClean{
  0%,100%{opacity:.62; filter:drop-shadow(0 0 0 rgba(46,167,255,0));}
  50%{opacity:1; filter:drop-shadow(0 0 10px rgba(46,167,255,.55));}
}
@media(min-width:821px){
  html body.landing.gamingmarket .gm-heroV4,
  html body.landing.gamingmarket .lb-visualHero{
    min-height:114svh !important;
    min-height:114dvh !important;
    height:114svh !important;
    height:114dvh !important;
    padding-top:var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,92px))) !important;
    padding-bottom:0 !important;
    overflow:visible !important;
  }
  html body.landing.gamingmarket .gm-heroV4Inner,
  html body.landing.gamingmarket .lb-visualHero__inner{
    min-height:calc(114svh - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,92px)))) !important;
    min-height:calc(114dvh - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,92px)))) !important;
    justify-content:center !important;
    padding-top:clamp(18px,2.4vh,34px) !important;
    padding-bottom:clamp(92px,10vh,138px) !important;
    transform:none !important;
  }
  html body.landing.gamingmarket .gm-heroV4Left,
  html body.landing.gamingmarket .lb-visualHero__content{
    transform:translateY(8px) !important;
  }
  html body.landing.gamingmarket .lb-visualHero__featureRow{
    margin-top:72px !important;
  }
  html body.landing.gamingmarket .gm-heroScrollDown,
  html body.landing.gamingmarket .lb-visualHero__scroll{
    display:grid !important;
    visibility:visible !important;
    opacity:1 !important;
    pointer-events:auto !important;
    position:absolute !important;
    left:50% !important;
    top:auto !important;
    bottom:22px !important;
    width:38px !important;
    height:38px !important;
    transform:translateX(-50%) !important;
    z-index:8 !important;
    place-items:center !important;
    border-radius:999px !important;
    border:1px solid rgba(255,255,255,.17) !important;
    background:rgba(2,8,20,.46) !important;
    box-shadow:0 10px 30px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.07) !important;
    animation:lbHeroScrollBounceClean 1.85s ease-in-out infinite !important;
  }
  html body.landing.gamingmarket .gm-heroScrollDot{
    width:13px !important;
    height:13px !important;
    border-right:2px solid rgba(255,255,255,.72) !important;
    border-bottom:2px solid rgba(255,255,255,.72) !important;
    transform:rotate(45deg) translate(-2px,-2px) !important;
    animation:lbHeroChevronGlowClean 1.85s ease-in-out infinite !important;
  }
}
@media(max-width:820px){
  html body.landing.gamingmarket .gm-heroScrollDown,
  html body.landing.gamingmarket .lb-visualHero__scroll{
    display:grid !important;
    visibility:visible !important;
    opacity:1 !important;
    bottom:10px !important;
    animation:lbHeroScrollBounceClean 1.85s ease-in-out infinite !important;
  }
  html body.landing.gamingmarket .gm-heroScrollDot{
    animation:lbHeroChevronGlowClean 1.85s ease-in-out infinite !important;
  }
}

/* ============================================================
   FINAL HERO TYPOGRAPHY REFINEMENT
   ============================================================ */
@media(min-width:821px){
  html body.landing.gamingmarket .gm-heroV4H1,
  html body.landing.gamingmarket .lb-visualHero__title{
    width:min(100%,1120px) !important;
    max-width:1120px !important;
    margin:0 auto 24px !important;
    display:flex !important;
    flex-direction:row !important;
    flex-wrap:nowrap !important;
    align-items:center !important;
    justify-content:center !important;
    gap:.24em !important;
    font-size:clamp(54px,4.15vw,78px) !important;
    line-height:.98 !important;
    letter-spacing:-.048em !important;
    font-weight:950 !important;
    text-align:center !important;
    text-transform:uppercase !important;
    white-space:nowrap !important;
    color:#fff !important;
    text-shadow:0 18px 54px rgba(0,0,0,.58), 0 0 34px rgba(59,130,246,.16) !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleMain,
  html body.landing.gamingmarket .gm-heroV4TitleAccent{
    display:inline-block !important;
    width:auto !important;
    max-width:none !important;
    font:inherit !important;
    line-height:inherit !important;
    letter-spacing:inherit !important;
    white-space:nowrap !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleMain{
    color:#fff !important;
    -webkit-text-fill-color:#fff !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleAccent{
    color:#4aa3ff !important;
    background-image:linear-gradient(92deg,#77c3ff 0%,#3b82f6 48%,#2563eb 100%) !important;
    background-clip:text !important;
    -webkit-background-clip:text !important;
    -webkit-text-fill-color:transparent !important;
    text-shadow:none !important;
  }

  html body.landing.gamingmarket .gm-heroV4Sub,
  html body.landing.gamingmarket .lb-visualHero__sub{
    width:min(900px,88vw) !important;
    max-width:900px !important;
    margin:0 auto 34px !important;
    padding:0 !important;
    text-align:center !important;
    font-size:clamp(16px,1.05vw,19px) !important;
    line-height:1.52 !important;
    font-weight:800 !important;
    letter-spacing:-.018em !important;
    color:rgba(241,247,255,.86) !important;
    text-wrap:balance !important;
    text-shadow:0 12px 34px rgba(0,0,0,.64) !important;
  }
}

@media(max-width:820px){
  html body.landing.gamingmarket .gm-heroV4H1,
  html body.landing.gamingmarket .lb-visualHero__title{
    width:min(100%,350px) !important;
    max-width:350px !important;
    margin:0 auto 15px !important;
    display:flex !important;
    flex-direction:column !important;
    align-items:center !important;
    justify-content:center !important;
    gap:0 !important;
    font-size:clamp(42px,12.6vw,56px) !important;
    line-height:.93 !important;
    letter-spacing:-.055em !important;
    text-align:center !important;
    white-space:normal !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleMain,
  html body.landing.gamingmarket .gm-heroV4TitleAccent{
    display:block !important;
    font:inherit !important;
    line-height:inherit !important;
    letter-spacing:inherit !important;
    white-space:nowrap !important;
  }

  html body.landing.gamingmarket .gm-heroV4Sub,
  html body.landing.gamingmarket .lb-visualHero__sub{
    width:min(320px,88vw) !important;
    max-width:320px !important;
    margin:0 auto 22px !important;
    text-align:center !important;
    font-size:14.5px !important;
    line-height:1.44 !important;
    font-weight:760 !important;
    color:rgba(241,247,255,.84) !important;
    text-wrap:balance !important;
  }
}



/* ============================================================
   FINAL HERO TITLE/SUBTITLE BALANCE V2
   softer desktop typography, cleaner subtitle rhythm
   ============================================================ */
@media(min-width:821px){
  html body.landing.gamingmarket .gm-heroV4H1,
  html body.landing.gamingmarket .lb-visualHero__title{
    width:min(100%,1040px) !important;
    max-width:1040px !important;
    margin:0 auto 18px !important;
    display:flex !important;
    flex-direction:row !important;
    flex-wrap:nowrap !important;
    align-items:center !important;
    justify-content:center !important;
    gap:.16em !important;
    font-size:clamp(36px,2.75vw,50px) !important;
    line-height:1.04 !important;
    letter-spacing:-.038em !important;
    font-weight:950 !important;
    text-align:center !important;
    text-transform:uppercase !important;
    white-space:nowrap !important;
    color:#fff !important;
    text-shadow:0 18px 48px rgba(0,0,0,.60), 0 0 26px rgba(59,130,246,.14) !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleMain,
  html body.landing.gamingmarket .gm-heroV4TitleAccent{
    display:inline-block !important;
    width:auto !important;
    max-width:none !important;
    font:inherit !important;
    line-height:inherit !important;
    letter-spacing:inherit !important;
    white-space:nowrap !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleMain{
    color:#fff !important;
    -webkit-text-fill-color:#fff !important;
  }

  html body.landing.gamingmarket .gm-heroV4TitleAccent{
    background-image:linear-gradient(92deg,#79c7ff 0%,#4b97ff 46%,#3474f6 100%) !important;
    background-clip:text !important;
    -webkit-background-clip:text !important;
    -webkit-text-fill-color:transparent !important;
    text-shadow:none !important;
  }

  html body.landing.gamingmarket .gm-heroV4Sub,
  html body.landing.gamingmarket .lb-visualHero__sub{
    width:min(760px,82vw) !important;
    max-width:760px !important;
    margin:0 auto 30px !important;
    padding:0 !important;
    display:block !important;
    text-align:center !important;
    font-size:clamp(15.5px,.96vw,17px) !important;
    line-height:1.58 !important;
    font-weight:720 !important;
    letter-spacing:-.008em !important;
    color:rgba(238,245,255,.80) !important;
    text-wrap:balance !important;
    text-shadow:0 10px 26px rgba(0,0,0,.58) !important;
  }
}

@media(min-width:821px) and (max-width:1280px){
  html body.landing.gamingmarket .gm-heroV4H1,
  html body.landing.gamingmarket .lb-visualHero__title{
    font-size:clamp(32px,3vw,44px) !important;
    max-width:980px !important;
  }

  html body.landing.gamingmarket .gm-heroV4Sub,
  html body.landing.gamingmarket .lb-visualHero__sub{
    max-width:720px !important;
  }
}


/* Feature row clean highlight, no card/button look */
body.landing.gamingmarket .lb-visualHero__featureRow{
  width:min(100%,940px) !important;
  margin:76px auto 0 !important;
  display:grid !important;
  grid-template-columns:repeat(4,minmax(0,1fr)) !important;
  gap:0 !important;
  align-items:center !important;
  padding:0 !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}
body.landing.gamingmarket .lb-visualHero__feature{
  min-height:58px !important;
  padding:0 22px !important;
  display:grid !important;
  grid-template-columns:42px minmax(0,1fr) !important;
  grid-template-rows:auto auto !important;
  column-gap:13px !important;
  align-items:center !important;
  text-align:left !important;
  border-radius:0 !important;
  border:0 !important;
  border-right:1px solid rgba(96,165,250,.22) !important;
  background:transparent !important;
  box-shadow:none !important;
  backdrop-filter:none !important;
  -webkit-backdrop-filter:none !important;
  transform:none !important;
}
body.landing.gamingmarket .lb-visualHero__feature:last-child{
  border-right:0 !important;
}
body.landing.gamingmarket .lb-visualHero__feature:hover{
  transform:none !important;
  background:transparent !important;
  border-color:rgba(96,165,250,.22) !important;
  box-shadow:none !important;
}
body.landing.gamingmarket .lb-visualHero__feature i{
  grid-row:1 / 3 !important;
  width:42px !important;
  height:42px !important;
  display:grid !important;
  place-items:center !important;
  color:#32adff !important;
  font-size:27px !important;
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  filter:drop-shadow(0 0 18px rgba(46,167,255,.58)) !important;
}
body.landing.gamingmarket .lb-visualHero__feature strong{
  display:block !important;
  color:#fff !important;
  font-size:11.5px !important;
  line-height:1.05 !important;
  font-weight:950 !important;
  text-transform:uppercase !important;
  letter-spacing:.015em !important;
  white-space:nowrap !important;
}
body.landing.gamingmarket .lb-visualHero__feature span{
  display:block !important;
  margin-top:5px !important;
  color:rgba(235,244,255,.68) !important;
  font-size:10.5px !important;
  line-height:1.05 !important;
  font-weight:650 !important;
  white-space:nowrap !important;
}
@media(max-width:1100px){
  body.landing.gamingmarket .lb-visualHero__featureRow{
    width:min(100%,780px) !important;
    margin-top:52px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature{
    padding:0 16px !important;
    grid-template-columns:38px minmax(0,1fr) !important;
    column-gap:10px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature i{
    width:38px !important;
    height:38px !important;
    font-size:25px !important;
  }
}
@media(max-width:768px){
  body.landing.gamingmarket .lb-visualHero__featureRow{
    width:min(100%,330px) !important;
    margin:22px auto 0 !important;
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    gap:14px 12px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature{
    min-height:42px !important;
    padding:0 !important;
    grid-template-columns:32px minmax(0,1fr) !important;
    column-gap:8px !important;
    border-right:0 !important;
    background:transparent !important;
    box-shadow:none !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature i{
    width:32px !important;
    height:32px !important;
    font-size:21px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature strong{
    font-size:9px !important;
    letter-spacing:0 !important;
    white-space:nowrap !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature span{
    margin-top:4px !important;
    font-size:8.5px !important;
    line-height:1.05 !important;
    font-weight:650 !important;
    white-space:nowrap !important;
  }
}
@media(max-width:380px){
  body.landing.gamingmarket .lb-visualHero__featureRow{
    width:min(100%,315px) !important;
    gap:12px 8px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature{
    grid-template-columns:29px minmax(0,1fr) !important;
    column-gap:6px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature i{
    width:29px !important;
    height:29px !important;
    font-size:19px !important;
  }
  body.landing.gamingmarket .lb-visualHero__feature strong{font-size:8.4px !important;}
  body.landing.gamingmarket .lb-visualHero__feature span{font-size:7.8px !important;}
}


/* Final refinement: bigger Trustpilot badge + wider centered subtitle */
@media(min-width:821px){
  html body.landing.gamingmarket .gm-heroV4Sub,
  html body.landing.gamingmarket .lb-visualHero__sub{
    width:min(980px,88vw) !important;
    max-width:980px !important;
    margin:0 auto 32px !important;
    font-size:clamp(16px,1.02vw,18px) !important;
    line-height:1.55 !important;
    font-weight:760 !important;
    letter-spacing:-.012em !important;
    color:rgba(240,247,255,.84) !important;
    text-align:center !important;
    text-wrap:balance !important;
  }

  html body.landing.gamingmarket .lb-visualHero__trustpilot{
    margin:6px auto 0 !important;
    gap:10px !important;
    font-size:14px !important;
    line-height:1 !important;
    font-weight:950 !important;
    color:#fff !important;
    text-shadow:0 8px 24px rgba(0,0,0,.55) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars{
    gap:4px !important;
    filter:drop-shadow(0 8px 18px rgba(0,182,122,.24)) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars span{
    width:26px !important;
    height:26px !important;
    font-size:14px !important;
    border-radius:1px !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.20) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpScore,
  html body.landing.gamingmarket .lb-visualHero__tpName{
    font-size:14px !important;
    font-weight:950 !important;
    white-space:nowrap !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpName{
    display:inline-flex !important;
    align-items:center !important;
    gap:5px !important;
    color:#fff !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpName i{
    color:#00b67a !important;
    font-size:13px !important;
    filter:drop-shadow(0 0 9px rgba(0,182,122,.55)) !important;
  }
}

@media(max-width:820px){
  html body.landing.gamingmarket .lb-visualHero__trustpilot{
    margin-top:7px !important;
    gap:7px !important;
    font-size:11.5px !important;
    font-weight:950 !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars{
    gap:3px !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars span{
    width:20px !important;
    height:20px !important;
    font-size:11px !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpScore,
  html body.landing.gamingmarket .lb-visualHero__tpName{
    font-size:11.5px !important;
    font-weight:950 !important;
    white-space:nowrap !important;
  }
}



/* Final polish: Trustpilot centered highlight + real scroll pulse */
@media(min-width:821px){
  html body.landing.gamingmarket .gm-heroV4Actions,
  html body.landing.gamingmarket .lb-visualHero__actions{
    margin-bottom:0 !important;
  }

  html body.landing.gamingmarket .lb-visualHero__trustpilot{
    width:max-content !important;
    max-width:100% !important;
    margin:24px auto 0 !important;
    padding:9px 16px 9px 12px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:12px !important;
    border-radius:9px !important;
    background:linear-gradient(180deg, rgba(3,16,36,.72), rgba(3,9,24,.48)) !important;
    border:1px solid rgba(0,182,122,.34) !important;
    box-shadow:0 18px 46px rgba(0,0,0,.30), 0 0 32px rgba(0,182,122,.11), inset 0 1px 0 rgba(255,255,255,.10) !important;
    backdrop-filter:blur(10px) !important;
    -webkit-backdrop-filter:blur(10px) !important;
    color:#fff !important;
    font-size:14px !important;
    line-height:1 !important;
    font-weight:950 !important;
    text-shadow:0 8px 24px rgba(0,0,0,.55) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars{
    display:inline-flex !important;
    gap:4px !important;
    filter:drop-shadow(0 8px 18px rgba(0,182,122,.26)) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars span{
    width:25px !important;
    height:25px !important;
    border-radius:3px !important;
    font-size:13px !important;
    background:#00b67a !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.20) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__trustpilot > strong{
    font-size:14px !important;
    font-weight:950 !important;
    white-space:nowrap !important;
    color:#fff !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpName{
    display:inline-flex !important;
    align-items:center !important;
    gap:6px !important;
    font-size:14px !important;
    font-weight:950 !important;
    white-space:nowrap !important;
    color:#fff !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpName i{
    color:#00b67a !important;
    font-size:13px !important;
    filter:drop-shadow(0 0 10px rgba(0,182,122,.60)) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__featureRow{
    margin-top:52px !important;
  }
}

@keyframes lbHeroScrollBounce{
  0%,100%{transform:translate(-50%,0); opacity:.58; box-shadow:0 0 0 0 rgba(96,165,250,.00), 0 0 0 rgba(96,165,250,.00);}
  35%{transform:translate(-50%,10px); opacity:1; box-shadow:0 0 0 7px rgba(96,165,250,.10), 0 0 28px rgba(96,165,250,.28);}
  70%{transform:translate(-50%,0); opacity:.86; box-shadow:0 0 0 14px rgba(96,165,250,.00), 0 0 16px rgba(96,165,250,.16);}
}

@keyframes lbHeroScrollDot{
  0%,100%{opacity:.55; transform:rotate(45deg) translate(-2px,-2px);}
  45%{opacity:1; transform:rotate(45deg) translate(4px,4px);}
}

html body.landing.gamingmarket .gm-heroScrollDown,
html body.landing.gamingmarket .lb-visualHero__scroll{
  animation:lbHeroScrollBounce 1.55s ease-in-out infinite !important;
  will-change:transform, opacity, box-shadow !important;
  border-color:rgba(96,165,250,.30) !important;
  background:rgba(3,13,31,.48) !important;
}

html body.landing.gamingmarket .gm-heroScrollDot{
  animation:lbHeroScrollDot 1.55s ease-in-out infinite !important;
  border-right-color:rgba(255,255,255,.82) !important;
  border-bottom-color:rgba(255,255,255,.82) !important;
}

@media(max-width:820px){
  html body.landing.gamingmarket .lb-visualHero__trustpilot{
    width:max-content !important;
    max-width:calc(100vw - 34px) !important;
    margin:13px auto 0 !important;
    padding:6px 9px !important;
    gap:6px !important;
    border-radius:9px !important;
    background:rgba(3,16,36,.54) !important;
    border:1px solid rgba(0,182,122,.24) !important;
    box-shadow:0 12px 30px rgba(0,0,0,.24), 0 0 22px rgba(0,182,122,.08) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__trustpilot > strong,
  html body.landing.gamingmarket .lb-visualHero__tpName{
    font-size:10.5px !important;
    white-space:nowrap !important;
  }

  html body.landing.gamingmarket .lb-visualHero__tpStars span{
    width:18px !important;
    height:18px !important;
    border-radius:2px !important;
    font-size:10px !important;
  }

  html body.landing.gamingmarket .lb-visualHero__featureRow{
    margin-top:20px !important;
  }

  html body.landing.gamingmarket .gm-heroScrollDown,
  html body.landing.gamingmarket .lb-visualHero__scroll{
    animation:lbHeroScrollBounce 1.55s ease-in-out infinite !important;
  }
}



/* Dual hero banner images, desktop plus mobile */
html body.landing.gamingmarket .lb-visualHero__bg{
  background:none !important;
  overflow:hidden !important;
  transform:none !important;
  filter:none !important;
}
html body.landing.gamingmarket .lb-visualHero__bgImg{
  position:absolute !important;
  inset:0 !important;
  display:block !important;
  width:100% !important;
  height:100% !important;
  object-fit:cover !important;
  object-position:center top !important;
  transform:scale(1.01) !important;
  filter:saturate(1.06) contrast(1.04) !important;
  pointer-events:none !important;
  user-select:none !important;
}
html body.landing.gamingmarket .lb-visualHero__bgImgMobile{
  display:none !important;
}
@media(max-width:768px){
  html body.landing.gamingmarket .lb-visualHero__bgImgDesktop{
    display:none !important;
  }
  html body.landing.gamingmarket .lb-visualHero__bgImgMobile{
    display:block !important;
    object-position:center top !important;
    transform:scale(1) !important;
  }
}


/* Final mobile readability and compact hero actions */
@media(max-width:768px){
  html body.landing.gamingmarket .lb-visualHero__bgImgMobile{
    filter:saturate(1.02) contrast(1.05) brightness(.72) !important;
  }

  html body.landing.gamingmarket .lb-visualHero__shade{
    background:
      radial-gradient(360px 300px at 50% 38%, rgba(0,0,0,.18), transparent 62%),
      linear-gradient(90deg, rgba(2,8,20,.48) 0%, rgba(2,8,20,.22) 50%, rgba(2,8,20,.48) 100%),
      linear-gradient(180deg, rgba(2,8,20,.58) 0%, rgba(2,8,20,.20) 22%, rgba(2,8,20,.44) 52%, rgba(2,8,20,.72) 100%) !important;
  }

  html body.landing.gamingmarket .gm-heroV4Actions,
  html body.landing.gamingmarket .lb-visualHero__actions{
    width:auto !important;
    max-width:280px !important;
    margin-left:auto !important;
    margin-right:auto !important;
    gap:10px !important;
  }

  html body.landing.gamingmarket .gm-heroV4Action,
  html body.landing.gamingmarket .lb-visualHero__btn{
    width:268px !important;
    max-width:74vw !important;
    min-width:0 !important;
    min-height:43px !important;
    padding:0 22px !important;
    border-radius:13px !important;
  }
}

@media(max-width:390px){
  html body.landing.gamingmarket .gm-heroV4Action,
  html body.landing.gamingmarket .lb-visualHero__btn{
    width:258px !important;
    max-width:72vw !important;
  }
}



/* ============================================================
   FINAL OVERRIDE: Hero action buttons, compact width
   ============================================================ */
body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions,
body.landing .gm-heroV4Actions.lb-visualHero__actions{
  width:100% !important;
  max-width:520px !important;
  margin-left:auto !important;
  margin-right:auto !important;
  display:flex !important;
  flex-direction:row !important;
  align-items:center !important;
  justify-content:center !important;
  gap:12px !important;
  flex-wrap:nowrap !important;
}

body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
body.landing .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn,
body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn{
  width:auto !important;
  min-width:0 !important;
  max-width:none !important;
  flex:0 0 auto !important;
  min-height:54px !important;
  padding:0 28px !important;
  white-space:nowrap !important;
}

body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnPrimary,
body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnPrimary{
  min-width:196px !important;
}

body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost,
body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost{
  min-width:216px !important;
}

@media (max-width:768px){
  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions,
  body.landing .gm-heroV4Actions.lb-visualHero__actions{
    width:100% !important;
    max-width:292px !important;
    display:flex !important;
    flex-direction:column !important;
    align-items:center !important;
    justify-content:center !important;
    gap:12px !important;
    margin-left:auto !important;
    margin-right:auto !important;
  }

  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn{
    width:252px !important;
    max-width:252px !important;
    min-width:0 !important;
    min-height:48px !important;
    padding:0 20px !important;
    display:inline-flex !important;
    justify-content:center !important;
    align-items:center !important;
    box-sizing:border-box !important;
  }

  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost{
    width:270px !important;
    max-width:270px !important;
  }
}

@media (max-width:380px){
  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions,
  body.landing .gm-heroV4Actions.lb-visualHero__actions{
    max-width:270px !important;
  }

  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .gm-heroV4Action,
  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btn{
    width:236px !important;
    max-width:236px !important;
  }

  body.landing.gamingmarket .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost,
  body.landing .gm-heroV4Actions.lb-visualHero__actions .lb-visualHero__btnGhost{
    width:254px !important;
    max-width:254px !important;
  }
}


/* Mobile hero copy refinement */
.lb-visualHero__titleDesktop,
.lb-visualHero__subDesktop{display:inline !important;}
.lb-visualHero__titleMobile,
.lb-visualHero__subMobile{display:none !important;}
@media(max-width:768px){
  html body.landing.gamingmarket .lb-visualHero__titleDesktop,
  html body.landing.gamingmarket .lb-visualHero__subDesktop{
    display:none !important;
  }
  html body.landing.gamingmarket .lb-visualHero__titleMobile{
    display:flex !important;
    flex-direction:column !important;
    align-items:center !important;
    justify-content:center !important;
    gap:0 !important;
    width:100% !important;
  }
  html body.landing.gamingmarket .lb-visualHero__titleMobile .gm-heroV4TitleMain,
  html body.landing.gamingmarket .lb-visualHero__titleMobile .gm-heroV4TitleAccent{
    display:block !important;
    width:100% !important;
    text-align:center !important;
    white-space:nowrap !important;
  }
  html body.landing.gamingmarket .lb-visualHero__title,
  html body.landing.gamingmarket .gm-heroV4H1{
    max-width:360px !important;
    margin-left:auto !important;
    margin-right:auto !important;
    font-size:clamp(35px,10.2vw,47px) !important;
    line-height:.96 !important;
    letter-spacing:-.055em !important;
    text-align:center !important;
  }
  html body.landing.gamingmarket .lb-visualHero__subMobile{
    display:block !important;
  }
  html body.landing.gamingmarket .lb-visualHero__sub,
  html body.landing.gamingmarket .gm-heroV4Sub{
    width:min(300px,88vw) !important;
    max-width:300px !important;
    margin:0 auto 18px !important;
    font-size:13.5px !important;
    line-height:1.42 !important;
    font-weight:800 !important;
    letter-spacing:-.015em !important;
    color:rgba(247,250,255,.88) !important;
    text-align:center !important;
    text-shadow:0 3px 14px rgba(0,0,0,.65) !important;
  }
}
@media(max-width:380px){
  html body.landing.gamingmarket .lb-visualHero__title,
  html body.landing.gamingmarket .gm-heroV4H1{
    font-size:clamp(32px,9.8vw,41px) !important;
    max-width:320px !important;
  }
  html body.landing.gamingmarket .lb-visualHero__sub,
  html body.landing.gamingmarket .gm-heroV4Sub{
    width:min(282px,88vw) !important;
    max-width:282px !important;
    font-size:12.8px !important;
  }
}



/* Command Center Search Modal, final landing override */
body .gmHeaderSearchOverlay{background:radial-gradient(900px 520px at 22% 8%, rgba(37,99,235,.22), transparent 62%),radial-gradient(760px 520px at 86% 20%, rgba(99,102,241,.18), transparent 60%),rgba(2,6,18,.76) !important;}
body .gmHeaderSearchModal.gmHeaderCommandCenter{border-radius:30px !important;border-color:rgba(125,166,255,.34) !important;background:linear-gradient(180deg, rgba(14,23,48,.94), rgba(5,10,24,.96)) !important;box-shadow:0 46px 150px rgba(0,0,0,.72), 0 0 0 1px rgba(255,255,255,.045) inset, 0 0 90px rgba(59,130,246,.14) !important;}
body .gmHeaderCommandQuickCard{background:linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.025)) !important;border-color:rgba(148,163,184,.13) !important;}
body .gmHeaderCommandQuickCard.is-active,body .gmHeaderCommandQuickCard:hover{background:linear-gradient(180deg, rgba(59,130,246,.22), rgba(14,165,233,.07)) !important;border-color:rgba(96,165,250,.46) !important;}
body .gmHeaderCommandGame{background:linear-gradient(180deg, rgba(15,23,42,.72), rgba(8,13,30,.72)) !important;border-color:rgba(148,163,184,.13) !important;}
body .gmHeaderCommandGame:hover{background:radial-gradient(280px 120px at 20% 0%, rgba(59,130,246,.14), transparent 68%), linear-gradient(180deg, rgba(15,23,42,.86), rgba(8,13,30,.78)) !important;border-color:rgba(96,165,250,.38) !important;}
@media(max-width:760px){body .gmHeaderSearchOverlay{background:#050916 !important;}body .gmHeaderSearchModal.gmHeaderCommandCenter{border-radius:0 !important;border:0 !important;background:linear-gradient(180deg,#071126 0%,#050916 100%) !important;box-shadow:none !important;}body .gmHeaderCommandQuickCard{min-width:128px !important;}}

/* Mobile service cards, compact icon layout */
@media(max-width:640px){
  .lbMkt2Grid{
    display:grid !important;
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
    gap:9px !important;
    align-items:stretch !important;
  }

  .lbMkt2Tile{
    width:100% !important;
    min-width:0 !important;
    min-height:58px !important;
    height:58px !important;
    padding:12px 13px !important;
    border-radius:15px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:flex-start !important;
    gap:10px !important;
    background:
      linear-gradient(180deg, rgba(255,255,255,.046), rgba(255,255,255,.016)),
      rgba(8,12,28,.52) !important;
    border:1px solid rgba(120,158,255,.16) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.055) !important;
  }

  .lbMkt2Tile::before,
  .lbMkt2Tile::after,
  .lbMkt2Ghost{
    display:none !important;
  }

  .lbMkt2Tile:hover{
    transform:none !important;
    border-color:rgba(147,180,255,.32) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08) !important;
  }

  .lbMkt2Head{
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    min-width:0 !important;
    width:100% !important;
    flex:1 1 auto !important;
  }

  .lbMkt2Head .lbMkt2Icon{
    display:grid !important;
  }

  .lbMkt2Icon{
    width:34px !important;
    height:34px !important;
    flex:0 0 34px !important;
    border-radius:12px !important;
    font-size:14px !important;
    margin:0 !important;
  }

  .lbMkt2Head h3{
    margin:0 !important;
    font-size:14px !important;
    line-height:1.15 !important;
    letter-spacing:-.015em !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
  }

  .lbMkt2Tile p,
  .lbMkt2Cta{
    display:none !important;
  }
}

@media(max-width:380px){
  .lbMkt2Grid{
    grid-template-columns:1fr !important;
  }
}



</style>


<style>
/* Mobile hero scroll indicator, keep it inside the visible viewport */
@media (max-width:820px){
  html body.landing.gamingmarket .gm-heroV4,
  html body.landing.gamingmarket .lb-visualHero{
    position:relative !important;
    overflow:hidden !important;
  }

  html body.landing.gamingmarket .gm-heroV4Inner,
  html body.landing.gamingmarket .lb-visualHero__inner{
    padding-bottom:54px !important;
  }

  html body.landing.gamingmarket .gm-heroScrollDown,
  html body.landing.gamingmarket .lb-visualHero__scroll{
    position:fixed !important;
    left:50% !important;
    bottom:max(14px, env(safe-area-inset-bottom)) !important;
    z-index:1002 !important;
    width:34px !important;
    height:34px !important;
    display:grid !important;
    place-items:center !important;
    visibility:visible !important;
    opacity:1 !important;
    pointer-events:auto !important;
    border-radius:999px !important;
    border:1px solid rgba(96,165,250,.34) !important;
    background:rgba(3,13,31,.62) !important;
    box-shadow:0 0 0 6px rgba(96,165,250,.06), 0 14px 34px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.10) !important;
    animation:lbHeroScrollBounce 1.55s ease-in-out infinite !important;
  }

  html body.landing.gamingmarket .gm-heroScrollDot{
    width:12px !important;
    height:12px !important;
    border-right:2px solid rgba(255,255,255,.86) !important;
    border-bottom:2px solid rgba(255,255,255,.86) !important;
    animation:lbHeroScrollDot 1.55s ease-in-out infinite !important;
  }
}
</style>

<style id="lb-hero-scrolldot-in-viewport">
/* The hero is 114svh tall, so the scroll dot anchored to the inner's bottom
   ended up ~14svh below the fold. Pin it to the 100svh line instead: the inner
   starts at --lb-navbar-bottom and is shifted down by 2.5svh, so both have to
   be subtracted to land on the visible viewport edge. */
@media (min-width:821px){
  html body.landing.gamingmarket .gm-heroScrollDown,
  html body.landing.gamingmarket .lb-visualHero__scroll{
    top:calc(100svh
             - var(--lb-navbar-bottom, calc(var(--lb-sale-h,0px) + var(--gm-nav-h,86px)))
             - 2.5svh
             - 58px) !important;
    bottom:auto !important;
  }
}
</style>


<style>
/* FAQ glass background, final override */
html body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild{
  position:relative !important;
  overflow:hidden !important;
}

html body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild::before{
  content:"" !important;
  position:absolute !important;
  inset:42px max(18px, calc((100vw - 980px) / 2)) 44px !important;
  pointer-events:none !important;
  border-radius:34px !important;
  background:
    radial-gradient(720px 260px at 18% 0%, rgba(96,165,250,.16), transparent 64%),
    radial-gradient(620px 280px at 92% 14%, rgba(129,140,248,.12), transparent 66%),
    linear-gradient(180deg, rgba(255,255,255,.070), rgba(255,255,255,.026)) !important;
  border:1px solid rgba(191,219,254,.14) !important;
  box-shadow:
    0 28px 90px rgba(0,0,0,.34),
    inset 0 1px 0 rgba(255,255,255,.10),
    inset 0 0 60px rgba(96,165,250,.035) !important;
  backdrop-filter:blur(14px) saturate(1.18) !important;
  -webkit-backdrop-filter:blur(14px) saturate(1.18) !important;
  z-index:0 !important;
}

html body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild .gm-wrap{
  position:relative !important;
  z-index:2 !important;
}

html body.landing.gamingmarket .gmFaq-tabs{
  width:fit-content !important;
  max-width:100% !important;
  padding:7px !important;
  margin:0 auto 34px !important;
  border-radius:999px !important;
}

html body.landing.gamingmarket .gmFaq-tab{
  height:38px !important;
  padding:0 20px !important;
  border-radius:999px !important;
  background:rgba(255,255,255,.035) !important;
  border:1px solid rgba(255,255,255,.055) !important;
}

html body.landing.gamingmarket .gmFaq-tab:hover,
html body.landing.gamingmarket .gmFaq-tab.is-active{
  background:linear-gradient(135deg, rgba(59,130,246,.92), rgba(99,102,241,.84)) !important;
  border-color:rgba(191,219,254,.32) !important;
  box-shadow:0 12px 30px rgba(59,130,246,.30), inset 0 1px 0 rgba(255,255,255,.22) !important;
}

html body.landing.gamingmarket .gmFaq-accordion{
  position:relative !important;
}

html body.landing.gamingmarket .gmFaq-btn{
  min-height:72px !important;
  padding:22px 26px !important;
}

html body.landing.gamingmarket .gmFaq-chev{
  background:rgba(255,255,255,.075) !important;
  border-color:rgba(191,219,254,.16) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08) !important;
}

@media(max-width:680px){
  html body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild::before{
    inset:28px 10px 34px !important;
    border-radius:26px !important;
    backdrop-filter:blur(10px) saturate(1.1) !important;
    -webkit-backdrop-filter:blur(10px) saturate(1.1) !important;
  }

  html body.landing.gamingmarket .gmFaq-tabs{
    width:100% !important;
    justify-content:flex-start !important;
    padding:7px !important;
    margin-bottom:18px !important;
    border-radius:22px !important;
  }

  html body.landing.gamingmarket .gmFaq-btn{
    min-height:64px !important;
    padding:18px 14px !important;
  }
}

/* Skip layout and painting work for sections far below the viewport — desktop only.
   Mobile never gets this: it was fighting the mobile-instant override below and
   is exactly the kind of "renders in late while scrolling" behavior that must not
   happen on mobile at all. */
@media (min-width:821px) {
  @supports (content-visibility:auto) {
    body.landing main > section:not(:first-of-type),
    body.landing .gm-supportfaq,
    body.landing .lbHybridLive,
    body.landing .lbHybridBoosters {
      content-visibility:auto;
      contain-intrinsic-size:auto 760px;
    }
  }
}
</style>

<!-- Moved up from deep in the body (was ~7000 lines further down): on a slow mobile
     connection the browser is still streaming/parsing the page when the user starts
     scrolling, so this override needs to be active from first paint, not only once the
     parser eventually reaches it. It disables content-visibility/reveal-on-scroll hiding
     that otherwise leaves not-yet-parsed sections blank while scrolling. -->
<style id="lb-mobile-instant">
@media (max-width:820px){

  /* Leichter, statischer Hintergrund (keine teuren Mehrschicht-Gradienten) */
  html,
  body.landing,
  body.landing.gamingmarket{
    background:#050813 !important;
    background-image:none !important;
    background-attachment:scroll !important;
    /* overflow-x:clip statt hidden -> erzeugt keinen eigenen Scroll-/
       Kompositions-Container, der auf iOS Kacheln verwirft. */
    overflow-x:clip !important;
    overflow-y:visible !important;
    height:auto !important;
    /* Kein momentum-scrolling-Container (haeufigste Ursache dafuer, dass
       Inhalt beim Hoch-/Runterscrollen auf iOS verschwindet). */
    -webkit-overflow-scrolling:auto !important;
    -webkit-text-size-adjust:100% !important;
    transform:none !important;
    perspective:none !important;
  }

  /* iOS/Mobile-Repaint-Bug abschalten: keine Layer-Promotion, die dazu
     fuehrt, dass abgescrollte Bereiche leer/dunkel zurueckkommen. */
  body.landing *{
    -webkit-overflow-scrolling:auto !important;
    -webkit-backface-visibility:visible !important;
    backface-visibility:visible !important;
    perspective:none !important;
  }

  /* KERN: jede Form von Render-Verzoegerung global abschalten.
     content-visibility/contain sind der Grund, warum Sektionen erst beim
     Scrollen gezeichnet werden -> hier fuer JEDES Element aufgehoben.
     backdrop-filter/will-change verursachen die dunklen Flashes. */
  body.landing *,
  body.landing *::before,
  body.landing *::after{
    content-visibility:visible !important;
    contain:none !important;
    contain-intrinsic-size:auto !important;
    will-change:auto !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
    animation:none !important;
    transition:none !important;
  }

  /* Jede Sektion + gaengige Reveal-Wrapper sofort sichtbar */
  body.landing main,
  body.landing section,
  body.landing section > *,
  body.landing [data-lbrv],
  body.landing [class*="reveal"],
  body.landing [class*="Reveal"],
  body.landing [class*="fade"],
  body.landing [class*="aos"],
  body.landing .wow,
  body.landing .gm-section,
  body.landing .gm-marketSection,
  body.landing .gm-reviewsSection,
  body.landing .lbHybridSteps,
  body.landing .lbHybridLive,
  body.landing .lbHybridBoosters,
  body.landing .gm-supportfaq,
  body.landing .gm-heroSearch,
  body.landing .lbGames2,
  body.landing .lbMkt2,
  body.landing .lbSteps3,
  body.landing .lbRev3,
  body.landing .lbCta5,
  body.landing .gm-wrap,
  body.landing .gm-wrapWide,
  body.landing .lbHybridWrap{
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    filter:none !important;
  }

  /* WICHTIG: die konkreten Ziele des Reveal-Systems (Header, Steps,
     Game-Karten, Marketplace-Tiles, Reviews, Booster/Orders).
     Diese liegen tiefer als "section > *" und wurden bisher NICHT
     erzwungen -> deshalb blieben sie beim Scrollen ausgeblendet. */
  body.landing .gm-sectionTag,
  body.landing .gm-headRow,
  body.landing .gm-h2,
  body.landing .gm-sub,
  body.landing .gm-marketHeader,
  body.landing .gm-marketHeadGrid,
  body.landing .gm-reviewsHead,
  body.landing .lbHybridCenter,
  body.landing .lbHybridSplit,
  body.landing .gm-helpCenterShell,
  body.landing .lbHybridStep,
  body.landing .lbHybridOrder,
  body.landing .lbHybridBooster,
  body.landing .lbSteps3Row,
  body.landing .lbSteps3Item,
  body.landing .gm-boostCard,
  body.landing .lbGames2Card,
  body.landing .lbGames2Grid > *,
  body.landing .gm-boostGrid > *,
  body.landing .lbMkt2Tile,
  body.landing .gm-serviceTile,
  body.landing .gm-reviewCard,
  body.landing .lbRev3Card,
  body.landing .gm-track > *,
  body.landing .lbRev3Track > *{
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    transition:none !important;
    animation:none !important;
    filter:none !important;
  }

  /* Grosse Schatten in Sektionen entfernen -> kein dunkles Nachzeichnen */
  body.landing section *,
  body.landing .gm-section *{
    box-shadow:none !important;
  }

  /* Dekorative Hintergrund-Layer aus (Sterne/Grid/Blur-Blobs) */
  body.landing .gm-bg,
  body.landing .gm-gridlines,
  body.landing #gmStars,
  body.landing .gm-star,
  body.landing .lb-visualHero__sparks{
    display:none !important;
  }

  /* Laufende Marquees ruhigstellen */
  body.landing .gm-track,
  body.landing .lbRev3Track,
  body.landing .gm-marquee,
  body.landing .lbRev3Row{
    animation:none !important;
    transform:none !important;
  }

  /* Bilder/Medien sofort und an Ort und Stelle */
  body.landing img,
  body.landing picture,
  body.landing video{
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    filter:none !important;
  }
}
</style>

<script id="lb-mobile-instant-js">
(function(){
  var isMobile = window.matchMedia && window.matchMedia('(max-width:820px)').matches;

  function apply(){
    var b = document.body;
    if(!b || !b.classList.contains('landing')) return;

    // Bilder immer sofort laden (schadet auf Desktop nicht)
    b.querySelectorAll('img').forEach(function(img, i){
      var isDeferredGameImg = isMobile && img.classList && img.classList.contains('lbGames2Img') && img.getAttribute('data-src');
      if(isDeferredGameImg) return;
      img.loading = 'eager';
      img.decoding = 'async';
      if(img.dataset && img.dataset.src && img.getAttribute('src') === null) img.setAttribute('src', img.dataset.src);
      if(img.dataset && img.dataset.bg){ img.style.backgroundImage = 'url("'+img.dataset.bg+'")'; }
      if(i < 16 && !img.getAttribute('fetchpriority')) img.setAttribute('fetchpriority','high');
    });

    // Nur auf Mobile: jedes Reveal-/Lazy-System hart deaktivieren,
    // egal ob es aus dem Layout, AOS o.ae. kommt.
    if(!isMobile) return;

    var sel = '[data-lbrv],[class*="reveal"],[class*="Reveal"],[class*="aos"],'
            + '.wow,.fade-in,.animate,section,section > *,'
            + '.gm-sectionTag,.gm-headRow,.gm-h2,.gm-sub,.gm-marketHeader,'
            + '.gm-reviewsHead,.lbHybridCenter,.lbHybridSplit,.gm-helpCenterShell,'
            + '.lbHybridStep,.lbHybridOrder,.lbHybridBooster,.lbSteps3Row,'
            + '.gm-boostCard,.lbGames2Card,.lbGames2Grid > *,.gm-boostGrid > *,'
            + '.lbMkt2Tile,.gm-serviceTile,.gm-reviewCard,.lbRev3Card,'
            + '.gm-track > *,.lbRev3Track > *';
    b.querySelectorAll(sel).forEach(function(el){
      el.classList.add('lbrv-in','in','aos-animate','animated','is-visible');
      var s = el.style;
      s.setProperty('opacity','1','important');
      s.setProperty('visibility','visible','important');
      s.setProperty('transform','none','important');
      s.setProperty('content-visibility','visible','important');
      s.setProperty('contain','none','important');
    });
  }

  // NOTE: this used to also run a permanent scroll/resize/orientationchange watchdog
  // plus a MutationObserver that re-wrote inline "!important" styles onto every reveal
  // element on every scroll frame. That's redundant now: the CSS media query above
  // already forces opacity/visibility/transform/content-visibility on these selectors
  // via stylesheet !important rules, which persist on their own without any JS help.
  // Keeping the watchdog running was pure overhead (visible jank/reflow while scrolling)
  // and it also fought unrelated components that toggle class/style — e.g. it kept
  // resetting the mobile menu drawer's open transform, so the hamburger menu button
  // stopped working. apply() below still runs once to eager-load images and add the
  // reveal classes for any CSS elsewhere that keys off them, but nothing runs on scroll.
  apply();
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', apply, {once:true});
  }
  window.addEventListener('load', apply, {once:true});
})();
</script>

<?= $this->stop() ?>


<?php
  /* ============================================================
     Helper: game data & icon resolution (server-side only)
     ============================================================ */
  $gmBase      = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
  $docroot     = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');

  $gmGet = function($row, array $keys, $default = '') {
    foreach ($keys as $key) {
      if (is_array($row)  && array_key_exists($key, $row)  && $row[$key]  !== null && $row[$key]  !== '') return $row[$key];
      if (is_object($row) && isset($row->{$key})           && $row->{$key} !== null && $row->{$key} !== '') return $row->{$key};
    }
    return $default;
  };

  $gmSlugify = function($value) {
    $value = trim((string)$value); if ($value === '') return '';
    $value = preg_replace('~[^\pL\d]+~u', '-', $value);
    $value = trim($value, '-');
    $value = function_exists('iconv') ? iconv('utf-8', 'us-ascii//TRANSLIT', $value) : $value;
    $value = strtolower((string)$value);
    $value = preg_replace('~[^-a-z0-9]+~', '', $value);
    return trim($value, '-');
  };

  /* Collect available games from various possible view variables */
  $gmAvailableGames = [];
  $gmGameSources    = [];
  foreach (['games','allGames','dbGames','availableGames','gameList'] as $sn) {
    if (isset(${$sn}) && is_iterable(${$sn})) $gmGameSources[] = ${$sn};
  }
  foreach ($gmGameSources as $src) {
    foreach ($src as $row) {
      $status = $gmGet($row, ['status','is_active','active'], 1);
      if ((string)$status !== '' && (string)$status !== '1' && strtolower((string)$status) !== 'active') continue;
      $name = trim((string)$gmGet($row, ['name','title','game_name','label'], ''));
      $slug = trim((string)$gmGet($row, ['slug','game_slug','url_slug'], ''));
      if ($slug === '') $slug = $gmSlugify($name);
      if ($name === '') $name = ucwords(str_replace('-', ' ', $slug));
      if ($slug === '' || $name === '') continue;
      $sort = (int)$gmGet($row, ['sort_order','order','position','id'], 999);
      $gmAvailableGames[$slug] = ['slug'=>$slug,'name'=>$name,'short'=>(string)$gmGet($row,['short_code','code','abbr'],''),'iconRaw'=>(string)$gmGet($row,['icon','icon_url','image','logo'],''),'bannerRaw'=>(string)$gmGet($row,['banner','banner_url','cover'],''),'sort'=>$sort];
    }
  }
  if (!count($gmAvailableGames) && isset($boostForms) && is_iterable($boostForms)) {
    foreach ($boostForms as $bf) {
      $slug = trim((string)$gmGet($bf, ['slug','game_slug','url_slug'], ''));
      $name = trim((string)$gmGet($bf, ['name','title','game_name'], ''));
      if ($slug === '') $slug = $gmSlugify($name);
      if ($name === '') $name = ucwords(str_replace('-', ' ', $slug));
      if ($slug === '' || $name === '') continue;
      $gmAvailableGames[$slug] = ['slug'=>$slug,'name'=>$name,'short'=>'','iconRaw'=>'','bannerRaw'=>'','sort'=>999];
    }
  }
  uasort($gmAvailableGames, function($a,$b){ return ($a['sort']<=>$b['sort']) ?: strcasecmp($a['name'],$b['name']); });

  /* Fallback games if DB empty */
  if (!count($gmAvailableGames)) {
    if (function_exists('util_get_all_games')) {
      $_dg = util_get_all_games(); $i = 1;
      foreach ($_dg as $_g) { $s=$_g['slug']??''; if($s==='')continue; $gmAvailableGames[$s]=['slug'=>$s,'name'=>$_g['name']??ucwords(str_replace('-',' ',$s)),'short'=>$_g['short_code']??'','iconRaw'=>$_g['icon']??'','bannerRaw'=>$_g['banner']??'','sort'=>(int)($_g['sort_order']??$i)]; $i++; }
    }
    if (!count($gmAvailableGames)) {
      $gmAvailableGames = [
        'league-of-legends' => ['slug'=>'league-of-legends','name'=>'League of Legends','short'=>'LoL','iconRaw'=>'','bannerRaw'=>'','sort'=>1],
        'valorant'          => ['slug'=>'valorant','name'=>'Valorant','short'=>'VAL','iconRaw'=>'','bannerRaw'=>'','sort'=>2],
        'teamfight-tactics' => ['slug'=>'teamfight-tactics','name'=>'Teamfight Tactics','short'=>'TFT','iconRaw'=>'','bannerRaw'=>'','sort'=>3],
      ];
    }
  }

  /* Icon resolver */
  $iDirs = ['/public/assets/website/images/icons/games/','/public/assets/website/images/icons/','/public/assets/website/images/game-icons/','/public/assets/website/images/games/icons/'];
  $iExts = ['svg','webp','png','jpg','jpeg'];
  $resolveIcon = function(string $slug) use ($gmBase,$docroot,$iDirs,$iExts): string {
    foreach ($iDirs as $d) { foreach ($iExts as $e) { $f=$docroot.$d.$slug.'.'.$e; if ($docroot!==''&&@file_exists($f)) return $gmBase.$d.$slug.'.'.$e; } }
    return '';
  };
  $gmAU = function(string $raw, string $slug, bool $banner=false) use ($gmBase,$docroot,$resolveIcon): string {
    $raw = trim($raw);
    if ($raw !== '') {
      if (preg_match('~^https?://~i',$raw) || strpos($raw,'/')===0) return $raw;
      $dirs = $banner
        ? ['/public/assets/website/images/games/','/public/assets/website/images/games/banners/','/public/assets/website/images/landing/games/']
        : ['/public/assets/website/images/icons/games/','/public/assets/website/images/icons/','/public/assets/website/images/game-icons/','/public/assets/website/images/games/icons/'];
      foreach ($dirs as $d) { $f=$docroot.$d.$raw; if ($docroot!==''&&@file_exists($f)) return $gmBase.$d.$raw; }
    }
    return $banner ? '' : $resolveIcon($slug);
  };

  /* Build search index for JS */
  $_gmNavCfg = function_exists('util_game_nav_config') ? util_game_nav_config() : [];
  $gmSG = [];
  foreach ($gmAvailableGames as $slug => $g) {
    $_gmCats        = $_gmNavCfg[$slug]['categories'] ?? [];
    $_gmBoostHref   = $_gmCats['boosting']['href']  ?? '';
    $_gmAccountHref = $_gmCats['accounts']['href']  ?? '';
    $_gmItemHref    = $_gmCats['items']['href']     ?? '';
    $gmSG[] = [
      'name'       => $g['name'],
      'slug'       => $slug,
      'url'        => $gmBase.'/'.$slug.'/',
      'short'      => $g['short'] ?? '',
      'icon'       => $gmAU($g['iconRaw'] ?? '', $slug, false),
      'boosting'   => $_gmBoostHref !== '',
      'boostUrl'   => $_gmBoostHref,
      'accounts'   => $_gmAccountHref !== '',
      'accountsUrl'=> $_gmAccountHref,
      'items'      => $_gmItemHref !== '',
      'itemsUrl'   => $_gmItemHref,
    ];
  }

  /* Default service links per game */
  $gmDefaultServices = function(string $slug, string $baseHref) use ($gmBase) {
    $slug    = trim($slug, '/');
    $navCfg  = function_exists('util_game_nav_config') ? util_game_nav_config() : [];
    $cats    = $navCfg[$slug]['categories'] ?? [];
    $services = [];
    if (!empty($cats['boosting']['href'])) $services[] = ['label'=>t('Boosting'),      'href'=>$cats['boosting']['href'], 'icon'=>'fa-rocket'];
    if (!empty($cats['accounts']['href'])) $services[] = ['label'=>t('Accounts'),      'href'=>$cats['accounts']['href'], 'icon'=>'fa-user'];
    if (!empty($cats['items']['href']))    $services[] = ['label'=>t('Items & skins'), 'href'=>$cats['items']['href'],    'icon'=>'fa-box-open'];
    if (!empty($cats['coaching']['href'])) $services[] = ['label'=>t('Coaching'),      'href'=>$cats['coaching']['href'], 'icon'=>'fa-graduation-cap'];
    return $services;
  };

  /* Game cards for slider */
  $bannerDirRel = '/public/assets/website/images/banner/';
  $bannerDirFs  = $docroot . $bannerDirRel;
  $gmFindGameIcon = function(string $slug) use ($gmBase, $docroot): ?string {
    $slug = trim($slug); if ($slug === '') return null;
    $iconDirsRel = ['/public/assets/website/images/games/icons/','/public/assets/website/images/game-icons/','/public/assets/website/images/icons/games/','/public/assets/website/images/games/','/public/assets/website/images/icons/'];
    $candidates  = [$slug.'.svg',$slug.'.webp',$slug.'.png',$slug.'.jpg',$slug.'.jpeg'];
    foreach ($iconDirsRel as $dirRel) { $dirFs=$docroot.$dirRel; foreach ($candidates as $file) { if ($docroot!==''&&@file_exists($dirFs.$file)) return $gmBase.$dirRel.$file; } }
    $bRel=$docroot.'/public/assets/website/images/banner/';
    foreach ([$slug.'.webp',$slug.'.jpg',$slug.'.png',$slug.'.jpeg'] as $file) { if ($docroot!==''&&@file_exists($bRel.$file)) return $gmBase.'/public/assets/website/images/banner/'.$file; }
    return null;
  };

  $sliderGames = [];
  if (isset($boostForms) && is_iterable($boostForms)) {
    foreach ($boostForms as $bf) {
      $slug  = $bf['slug']  ?? ($bf->slug  ?? null);
      $title = $bf['name']  ?? ($bf->name  ?? ($bf['title'] ?? ($bf->title ?? null)));
      if (!$slug || !$title) continue;
      $baseHref     = rtrim($gmBase,'/').'/'.trim($slug,'/').'/';
      $href         = $bf['url'] ?? ($bf->url ?? $baseHref);
      $svcList      = $bf['services'] ?? ($bf->services ?? []);
      $services     = [];
      if (is_iterable($svcList)) { foreach ($svcList as $svc) { $label=$svc['name']??($svc->name??($svc['label']??($svc->label??null))); if(!$label)continue; $svcHref=$svc['url']??($svc->url??($baseHref.'rank-boost/')); $services[]=['label'=>$label,'href'=>$svcHref]; } }
      $sliderGames[] = ['title'=>$title,'slug'=>$slug,'href'=>$href,'services'=>$services];
    }
  }
  /* Apply default service nav if available */
  if (!empty($sliderGames)) {
    foreach ($sliderGames as &$gmGame) { $gmSlug=$gmGame['slug']??''; $gmHref=$gmGame['href']??($gmBase.'/'.trim($gmSlug,'/').'/'); if ($gmSlug!=='') $gmGame['services']=$gmDefaultServices($gmSlug,$gmHref); }
    unset($gmGame);
  }
  if (!empty($gmAvailableGames)) {
    $sliderGames = [];
    foreach ($gmAvailableGames as $slug=>$g) { $sliderGames[] = ['title'=>$g['name'],'slug'=>$slug,'href'=>$gmBase.'/'.$slug.'/','services'=>$gmDefaultServices($slug,$gmBase.'/'.$slug.'/'),'banner'=>$g['bannerRaw'] ?? '']; }
  }
  if (empty($sliderGames)) {
    $fallbackGameData = [['title'=>'League of Legends','slug'=>'league-of-legends'],['title'=>'Valorant','slug'=>'valorant'],['title'=>'Teamfight Tactics','slug'=>'teamfight-tactics'],['title'=>'Fortnite','slug'=>'fortnite'],['title'=>'Apex Legends','slug'=>'apex-legends'],['title'=>'Overwatch 2','slug'=>'overwatch-2'],['title'=>'Call of Duty','slug'=>'call-of-duty'],['title'=>'Rocket League','slug'=>'rocket-league'],['title'=>'Genshin Impact','slug'=>'genshin-impact'],['title'=>'Marvel Rivals','slug'=>'marvel-rivals']];
    foreach ($fallbackGameData as $fg) { $href=$gmBase.'/'.$fg['slug'].'/'; $sliderGames[] = ['title'=>$fg['title'],'slug'=>$fg['slug'],'href'=>$href,'services'=>$gmDefaultServices($fg['slug'],$href)]; }
  }

  /* Reviews */
  if (!function_exists('gm_render_stars')) {
    function gm_render_stars($n=5){ $o=''; for($i=0;$i<5;$i++) $o.='<i class="fa-solid fa-star'.($i<$n?'':' empty').'" aria-hidden="true"></i>'; return $o; }
  }
  $gmReviewsRow1 = [
    ['u'=>'A','name'=>'Alex M.','title'=>t('Valorant Account'),'rating'=>5,'txt'=>t('Super fast delivery and the account was exactly as described. Highly recommend.'),'tag'=>'Valorant'],
    ['u'=>'S','name'=>'Sophie K.','title'=>t('LoL Boosting'),'rating'=>5,'txt'=>t('Climbed from Gold to Diamond in two weeks. The booster was a pro and very communicative.'),'tag'=>'League of Legends'],
    ['u'=>'J','name'=>'Jake R.','title'=>t('Fortnite Skins'),'rating'=>5,'txt'=>t('Got rare skins at a great price. Account transfer was smooth and quick.'),'tag'=>'Fortnite'],
    ['u'=>'M','name'=>'Maria T.','title'=>t('V-Bucks Top-up'),'rating'=>5,'txt'=>t('Instant delivery, no issues. Will buy again without hesitation.'),'tag'=>'Fortnite'],
    ['u'=>'L','name'=>'Lukas B.','title'=>t('CS2 Account'),'rating'=>5,'txt'=>t('Prime account with a lot of hours. Exactly what I needed for competitive play.'),'tag'=>'CS2'],
  ];
  $gmReviewsRow2 = [
    ['u'=>'P','name'=>'Paula N.','title'=>t('Coaching Session'),'rating'=>5,'txt'=>t('The coach improved my positioning and game sense drastically. Worth every cent.'),'tag'=>'Coaching'],
    ['u'=>'T','name'=>'Tom C.','title'=>t('TFT Boosting'),'rating'=>5,'txt'=>t('Hit Challenger on TFT faster than I expected. Booster explained their strategy too.'),'tag'=>'TFT'],
    ['u'=>'E','name'=>'Eva H.','title'=>t('Riot Points'),'rating'=>5,'txt'=>t('RP showed up in seconds. Cheapest and fastest place I\'ve found.'),'tag'=>'Valorant'],
    ['u'=>'N','name'=>'Noah W.','title'=>t('Apex Account'),'rating'=>5,'txt'=>t('Great selection of accounts. Found one with all the heirlooms I wanted.'),'tag'=>'Apex Legends'],
    ['u'=>'C','name'=>'Clara Z.','title'=>t('Gift Card'),'rating'=>5,'txt'=>t('PSN code worked instantly. Super smooth experience from start to finish.'),'tag'=>'PSN'],
  ];

  /* Auth check for CTA */
  $gmIsLoggedIn = false;
  if (defined('CLIENT_ID') && (int) CLIENT_ID > 0)        { $gmIsLoggedIn = true; }
  elseif (defined('CLIENT_DATA') && is_array(CLIENT_DATA) && !empty(CLIENT_DATA['id'])) { $gmIsLoggedIn = true; }
  elseif (function_exists('auth') && auth()->check())      { $gmIsLoggedIn = true; }
  elseif (isset($currentUser) && $currentUser)             { $gmIsLoggedIn = true; }
  elseif (isset($user) && !empty($user['id']))             { $gmIsLoggedIn = true; }
  elseif (!empty($_SESSION['client_id']) || !empty($_SESSION['user_id']) || !empty($_SESSION['uid'])) { $gmIsLoggedIn = true; }
?>

<!-- ============================================================
     BACKGROUND LAYERS
     ============================================================ -->

<!-- Background layers — exact GamingMarket -->
<div class="gm-bg" aria-hidden="true"></div>
<div class="gm-gridlines" aria-hidden="true"></div>
<div id="gmStars" aria-hidden="true"></div>

<!-- Removed forced scroll reset for faster, stable page loading. -->

<script>window.GM_SEARCH_GAMES = <?= json_encode($gmSG, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<style id="lb-hero-quick-navigation">
body.landing.gamingmarket .lb-visualHero__content{
  transform:translateY(-46px);
}
body.landing.gamingmarket .lb-visualHero__trustpilotAfterNav{
  margin:18px auto 0 !important;
}
body.landing.gamingmarket .gm-heroV4ActionPrimary,
body.landing.gamingmarket .lb-visualHero__btnPrimary{
  background:#6366F1 !important;
  background-image:none !important;
  border-color:#7477f5 !important;
  box-shadow:0 12px 28px rgba(99,102,241,.30),inset 0 1px 0 rgba(255,255,255,.15) !important;
}
body.landing.gamingmarket .gm-heroV4ActionPrimary:hover,
body.landing.gamingmarket .lb-visualHero__btnPrimary:hover{
  background:#5558e8 !important;
  background-image:none !important;
  box-shadow:0 15px 34px rgba(99,102,241,.40),inset 0 1px 0 rgba(255,255,255,.15) !important;
}
body.landing.gamingmarket .gm-heroV4ActionGhost,
body.landing.gamingmarket .lb-visualHero__btnGhost{
  background:rgba(3,13,31,.58) !important;
  background-image:none !important;
  border-color:rgba(96,165,250,.42) !important;
  box-shadow:0 12px 28px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.06) !important;
}
body.landing.gamingmarket .lb-visualHero__quickNav{
  width:min(980px,calc(100% - 40px)) !important;
  margin:34px auto 0 !important;
  display:grid !important;
  grid-template-columns:repeat(7,minmax(0,1fr)) !important;
  gap:8px !important;
  padding:0 !important;
  border:0 !important;
  background:transparent !important;
  box-shadow:none !important;
}
body.landing.gamingmarket .lb-visualHero__quickLink{
  min-width:0;
  min-height:88px;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:8px;
  padding:7px 10px;
  border:1px solid transparent;
  border-radius:14px;
  background:transparent;
  color:#fff;
  text-decoration:none;
  box-shadow:none;
  transition:transform .16s ease,border-color .16s ease,background .16s ease;
}
body.landing.gamingmarket .lb-visualHero__quickLink:hover{
  transform:translateY(-2px);
  border-color:rgba(255,255,255,.12) !important;
  background:rgba(255,255,255,.06) !important;
}
body.landing.gamingmarket .lb-visualHero__quickLink i{
  width:58px;
  height:58px;
  display:grid;
  place-items:center;
  border-radius:17px;
  background:rgba(3,13,31,.58) !important;
  color:#6366F1;
  font-size:24px;
  box-shadow:0 8px 20px rgba(83,100,223,.28);
}
body.landing.gamingmarket .lb-visualHero__quickLink span{
  max-width:100%;
  overflow:hidden;
  color:#f6f7ff;
  font-size:12px;
  font-weight:850;
  line-height:1.1;
  text-overflow:ellipsis;
  white-space:nowrap;
}
body.landing.gamingmarket .lb-visualHero__quickLink--pink i{
  background:rgba(3,13,31,.58) !important;
  color:#ec4899;
}
/* Scroll hint at the end of the mobile quick nav — desktop grid never shows it. */
body.landing.gamingmarket .lb-visualHero__quickMore{ display:none; }
@media(max-width:980px){
  body.landing.gamingmarket .lb-visualHero__quickNav{
    width:100% !important;
    max-width:none !important;
    margin-top:28px !important;
    padding:0 20px 8px !important;
    display:flex !important;
    grid-template-columns:none !important;
    gap:12px !important;
    overflow-x:auto !important;
    overflow-y:hidden !important;
    scroll-snap-type:x mandatory;
    scroll-padding-inline:20px;
    overscroll-behavior-x:contain;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
  }
  body.landing.gamingmarket .lb-visualHero__quickNav::-webkit-scrollbar{
    display:none;
  }
  body.landing.gamingmarket .lb-visualHero__quickLink{
    flex:0 0 124px;
    width:124px;
    min-width:124px;
    scroll-snap-align:start;
  }
}
@media(max-width:600px){
  body.landing.gamingmarket .lb-visualHero__content{ transform:translateY(-10px); }
  body.landing.gamingmarket .lb-visualHero__quickNav{
    width:100% !important;
    max-width:none !important;
    margin:20px 0 0 !important;
    padding:0 16px 8px !important;
    display:flex !important;
    grid-template-columns:none !important;
    gap:12px !important;
    overflow-x:auto !important;
    overflow-y:hidden !important;
    scroll-snap-type:x mandatory;
    scroll-padding-inline:16px;
    overscroll-behavior-x:contain;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
  }
  body.landing.gamingmarket .lb-visualHero__quickNav::-webkit-scrollbar{ display:none; }
  body.landing.gamingmarket .lb-visualHero__quickNav{
    gap:2px !important;
    padding:0 10px 6px !important;
    scroll-padding-inline:10px;
  }
  body.landing.gamingmarket .lb-visualHero__quickLink{
    flex:0 0 68px;
    width:68px;
    min-width:68px;
    min-height:70px;
    padding:5px 4px;
    gap:7px;
    border-radius:13px;
    scroll-snap-align:start;
  }
  body.landing.gamingmarket .lb-visualHero__quickLink i{
    width:44px;
    height:44px;
    font-size:20px;
    border-radius:13px;
    box-shadow:0 6px 14px rgba(83,100,223,.22);
  }
  body.landing.gamingmarket .lb-visualHero__quickLink span{
    font-size:9px;
    font-weight:800;
    letter-spacing:-.01em;
  }
  /* Swipe affordance: a plain chevron parked after the last tile, so it never
     sits on top of a button. It scrolls away once the user starts swiping. */
  body.landing.gamingmarket .lb-visualHero__quickMore{
    display:grid;
    place-items:center;
    flex:0 0 18px;
    width:18px;
    align-self:center;
    border:0;
    background:none;
    color:rgba(207,224,255,.55);
    font-size:12px;
    pointer-events:none;
  }
  body.landing.gamingmarket .lb-visualHero__trustpilotAfterNav{
    margin-top:16px !important;
  }
}
</style>
<section class="gm-heroV4 lb-visualHero" id="top">
  <div class="lb-visualHero__bg" aria-hidden="true">
    <picture>
      <source media="(max-width: 820px)" srcset="/public/assets/website/images/landing/lolboost-hero-multigame7.webp">
      <source media="(min-width: 821px)" srcset="/public/assets/website/images/landing/lolboost-hero-multigame6.webp">
      <img class="lb-visualHero__bgImg" src="/public/assets/website/images/landing/lolboost-hero-multigame6.webp" alt="" loading="eager" decoding="async" fetchpriority="high">
    </picture>
  </div>
  <div class="lb-visualHero__shade" aria-hidden="true"></div>
  <div class="lb-visualHero__sparks" aria-hidden="true"></div>

  <div class="gm-heroV4Inner lb-visualHero__inner">
    <div class="gm-heroV4Left lb-visualHero__content">
      <h1 class="gm-heroV4H1 lb-visualHero__title">
        <span class="lb-visualHero__titleDesktop">
          <span class="gm-heroV4TitleMain"><?= t('Buy Accounts,') ?></span>
          <span class="gm-heroV4TitleAccent"><?= t('Skins & Boosts') ?></span>
        </span>
        <span class="lb-visualHero__titleMobile" aria-hidden="true">
          <span class="gm-heroV4TitleMain"><?= t('Boosts, Accounts') ?></span>
          <span class="gm-heroV4TitleAccent"><?= t('& Skins') ?></span>
        </span>
      </h1>

      <p class="gm-heroV4Sub lb-visualHero__sub">
        <span class="lb-visualHero__subDesktop"><?= t('Rank Boost, Coaching, verified Accounts, Items and digital Goods - one secure platform with 24/7 support.') ?></span>
        <span class="lb-visualHero__subMobile"><?= t('Secure boosts, coaching, accounts and items with 24/7 support.') ?></span>
      </p>

      <div class="gm-heroV4Actions lb-visualHero__actions" aria-label="Hero actions">
        <button class="gm-heroV4Action gm-heroV4ActionPrimary lb-visualHero__btn lb-visualHero__btnPrimary" type="button" onclick="gmOpenNavSearch()">
          <i class="fa-solid fa-bolt" aria-hidden="true"></i>
          <span><?= t('Order Now') ?></span>
        </button>
        <a class="gm-heroV4Action gm-heroV4ActionGhost lb-visualHero__btn lb-visualHero__btnGhost" href="<?= $gmBase ?>/services/accounts">
          <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
          <span><?= t('Browse Marketplace') ?></span>
        </a>
      </div>

    </div>

    <nav class="lb-visualHero__featureRow lb-visualHero__quickNav" aria-label="<?= t('Quick service navigation') ?>">
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/services/boosting"><i class="fa-solid fa-rocket" aria-hidden="true"></i><span><?= t('Boosting') ?></span></a>
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/services/accounts"><i class="fa-solid fa-user-shield" aria-hidden="true"></i><span><?= t('Accounts') ?></span></a>
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/services/items"><i class="fa-solid fa-gem" aria-hidden="true"></i><span><?= t('Items & Skins') ?></span></a>
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/services/coaching"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i><span><?= t('Coaching') ?></span></a>
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/services/top-ups"><i class="fa-solid fa-coins" aria-hidden="true"></i><span><?= t('Top-ups') ?></span></a>
      <a class="lb-visualHero__quickLink lb-visualHero__quickLink--pink" href="<?= $gmBase ?>/egirls"><i class="fa-solid fa-headset" aria-hidden="true"></i><span><?= t('Gamer Girls') ?></span></a>
      <a class="lb-visualHero__quickLink" href="<?= $gmBase ?>/digital-goods"><i class="fa-solid fa-box-open" aria-hidden="true"></i><span><?= t('Digital Goods') ?></span></a>
      <span class="lb-visualHero__quickMore" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
    </nav>

    <div class="lb-visualHero__trustpilot lb-visualHero__trustpilotAfterNav">
      <a href="https://www.trustpilot.com/review/<?= defined('TRUSTPILOT_DOMAIN') ? TRUSTPILOT_DOMAIN : 'lolboost.gg' ?>" target="_blank" rel="noopener noreferrer" class="lb-visualHero__tpStars" aria-label="Trustpilot">
        <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
      </a>
      <strong>4.9 out of 5</strong>
      <span class="lb-visualHero__tpName"><i class="fa-solid fa-star" aria-hidden="true"></i> Trustpilot</span>
    </div>

    <a class="gm-heroScrollDown lb-visualHero__scroll" href="javascript:void(0)" data-scroll-target="#boosting" aria-label="Scroll to services">
      <span class="gm-heroScrollDot"></span>
    </a>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════════
     GAME SLIDER
     ══════════════════════════════════════════════════════════════ -->
<section class="gm-section" id="boosting">
  <div class="gm-wrap">
    <div class="gm-headRow">
      <div>
        <div class="gm-sectionTag"><span aria-hidden="true"></span><?= t('Game marketplace hub') ?></div>
        <h2 class="gm-h2"><?= t('Pick a game and choose your service') ?></h2>
        <p class="gm-sub"><?= t('Boosting, coaching, accounts, items and marketplace offers grouped by game.') ?></p>
      </div>
      <a class="gm-btn gm-btnSmall" href="<?= htmlspecialchars($gmBase . '/services/accounts') ?>">
        <span><?= t('Browse all games') ?></span><i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="lbGames2Clip" id="lbGames2Clip">
      <div class="lbGames2Grid">
        <?php $lbGi = 0; foreach ($sliderGames as $g):
          $slug      = $g['slug'] ?? '';
          $bannerSrc = $gmAU($g['banner'] ?? '', $slug, true);
          if ($bannerSrc === '') {
            foreach ([$slug.'.webp',$slug.'.jpg',$slug.'.png',$slug.'.jpeg'] as $file) {
              if (!empty($bannerDirFs) && @file_exists($bannerDirFs.$file)) { $bannerSrc = $gmBase.$bannerDirRel.$file; break; }
            }
          }
          $lbGi++;
        ?>
        <article class="lbGames2Card" data-game-card data-game-index="<?= (int)$lbGi ?>">
          <a class="lbGames2MainLink" href="<?= htmlspecialchars($g['href']) ?>" aria-label="<?= htmlspecialchars($g['title']) ?>">
            <div class="lbGames2Thumb">
              <?php if ($bannerSrc !== ''): ?>
                <img class="lbGames2Img"
                     src="<?= htmlspecialchars($bannerSrc) ?>"
                     alt="<?= htmlspecialchars($g['title']) ?>"
                     loading="eager"
                     decoding="async"
                     fetchpriority="<?= $lbGi <= 6 ? 'high' : 'low' ?>">
              <?php else: ?>
                <span class="lbGames2Fallback" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($g['title'],0,2))) ?></span>
              <?php endif; ?>
            </div>
            <h3 class="lbGames2Name"><?= htmlspecialchars($g['title']) ?></h3>
          </a>
          <div class="lbGames2Tags">
            <?php foreach (array_slice($g['services'] ?? [], 0, 4) as $s): ?>
              <a href="<?= htmlspecialchars($s['href'] ?? $g['href']) ?>"><?= htmlspecialchars($s['label']) ?></a>
            <?php endforeach; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

    </div>

    <div class="lbGames2More" id="lbGames2More">
      <button type="button" class="lbGames2MoreBtn" id="lbGames2MoreBtn">
        <span><?= t('Show more popular games') ?></span>
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <style>
  /* ── Games grid ─────────────────────────────────────────── */
  .lbGames2Clip{
    position:relative;
    max-height:820px;
    overflow:hidden;
    transition:max-height .55s cubic-bezier(.22,.61,.36,1);
    /* cards fade into the page background — no colored overlay, no edges */
    -webkit-mask-image:linear-gradient(180deg,#000 0%,#000 calc(100% - 190px),transparent 100%);
    mask-image:linear-gradient(180deg,#000 0%,#000 calc(100% - 190px),transparent 100%);
  }
  .lbGames2Clip.is-open,
  .lbGames2Clip.no-clip{
    max-height:none;
    -webkit-mask-image:none;
    mask-image:none;
  }
  .lbGames2Grid{
    display:grid;
    grid-template-columns:repeat(6,minmax(0,1fr));
    gap:22px 18px;
    padding-bottom:8px;
  }
  .lbGames2Card{ position:relative; }
  .lbGames2MainLink{
    position:relative;
    z-index:1;
    display:block;
    color:inherit;
    text-decoration:none;
    border-radius:16px;
    outline:none;
  }
  .lbGames2MainLink:focus-visible .lbGames2Thumb{
    border-color:rgba(99,102,241,.62);
    box-shadow:0 0 0 3px rgba(99,102,241,.22),0 16px 44px rgba(0,0,0,.45);
  }
  .lbGames2Thumb{
    aspect-ratio:3/4;
    border-radius:16px;
    overflow:hidden;
    background:linear-gradient(160deg,#0c1022,#0a0d1e);
    border:1px solid rgba(255,255,255,.08);
    transition:border-color .18s,transform .18s,box-shadow .18s;
  }
  .lbGames2Card:hover .lbGames2Thumb{
    border-color:rgba(99,102,241,.45);
    transform:translateY(-4px);
    box-shadow:0 16px 44px rgba(0,0,0,.45);
  }
  .lbGames2Thumb img{
    width:100%; height:100%;
    object-fit:cover; display:block;
    transition:transform .3s;
  }
  .lbGames2Card:hover .lbGames2Thumb img{ transform:scale(1.04); }
  .lbGames2Fallback{
    width:100%; height:100%;
    display:grid; place-items:center;
    font-size:32px; font-weight:900;
    color:rgba(255,255,255,.16);
  }
  .lbGames2Name{
    margin:12px 0 8px;
    font-size:16px; font-weight:800;
    letter-spacing:-.01em;
  }
  .lbGames2Tags{
    position:relative; z-index:2;
    display:flex; flex-wrap:wrap; gap:6px;
  }
  .lbGames2Tags a{
    font-size:12px; font-weight:700;
    color:rgba(255,255,255,.62);
    text-decoration:none;
    padding:4px 10px;
    border-radius:999px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.10);
    transition:color .15s,border-color .15s,background .15s;
  }
  .lbGames2Tags a:hover{
    color:#fff;
    border-color:rgba(99,102,241,.5);
    background:rgba(99,102,241,.14);
  }
  .lbGames2More{
    position:relative;
    display:flex; justify-content:center;
    margin-top:-46px;              /* button floats over the faded card edge */
    z-index:2;
  }
  .lbGames2Clip.is-open + .lbGames2More,
  .lbGames2Clip.no-clip + .lbGames2More{ display:none; }
  .lbGames2MoreBtn{
    display:inline-flex; align-items:center; gap:10px;
    padding:14px 26px;
    border-radius:999px;
    font-size:15px; font-weight:800;
    color:#fff; cursor:pointer;
    background:linear-gradient(135deg,var(--gm-blue),var(--gm-blueH));
    border:1px solid rgba(120,122,255,.45);
    box-shadow:0 14px 40px rgba(99,102,241,.35);
    transition:transform .18s,filter .18s;
  }
  .lbGames2MoreBtn:hover{ transform:translateY(-2px); filter:brightness(1.08); }
  @media(max-width:1100px){
    .lbGames2Grid{ grid-template-columns:repeat(4,1fr); }
    .lbGames2Clip{ max-height:760px; }
  }
  @media(max-width:760px){
    .lbGames2Grid{ grid-template-columns:repeat(3,1fr); gap:16px 12px; }
    .lbGames2Clip{ max-height:640px; }
    .lbGames2Name{ font-size:14px; }
    .lbGames2Tags a{ font-size:11px; padding:3px 8px; }
  }
  @media(max-width:480px){
    .lbGames2Grid{ grid-template-columns:repeat(2,1fr); }
    .lbGames2Clip{ max-height:455px; }
  }

  /* mobile: fewer games before Show More */
  @media(max-width:760px){
    .lbGames2Clip{
      max-height:520px;
      -webkit-mask-image:linear-gradient(180deg,#000 0%,#000 calc(100% - 125px),transparent 100%);
      mask-image:linear-gradient(180deg,#000 0%,#000 calc(100% - 125px),transparent 100%);
    }
    .lbGames2More{ margin-top:-34px; }
  }
  @media(max-width:480px){
    .lbGames2Clip{ max-height:455px; }
  }

  /* Mobile: kein max-height/mask clipping mehr.
     Stattdessen werden Karten wirklich ausgeblendet, damit iOS nichts
     halb gerendert oder beim Zurueckscrollen wieder dunkel zeichnet. */
  @media(max-width:760px){
    .lbGames2Clip{
      max-height:none !important;
      overflow:visible !important;
      -webkit-mask-image:none !important;
      mask-image:none !important;
      transition:none !important;
    }
    .lbGames2Card.is-mobile-hidden{
      display:none !important;
    }
    .lbGames2More{
      margin-top:22px !important;
    }
    .lbGames2MoreBtn{
      width:100%;
      justify-content:center;
    }
  }
  </style>

  <script>
  (function(){
    var clip = document.getElementById('lbGames2Clip');
    var btn  = document.getElementById('lbGames2MoreBtn');
    if(!clip || !btn) return;

    var grid  = clip.querySelector('.lbGames2Grid');
    var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('[data-game-card]')) : [];
    var mq    = window.matchMedia ? window.matchMedia('(max-width:760px)') : {matches:false};
    var INITIAL_MOBILE = 6;
    var STEP_MOBILE    = 6;
    var visibleMobile  = INITIAL_MOBILE;

    function loadCard(card){
      if(!card) return;
      card.querySelectorAll('img[data-src]').forEach(function(img){
        var src = img.getAttribute('data-src');
        if(src){
          img.setAttribute('src', src);
          img.removeAttribute('data-src');
          img.loading = 'eager';
          img.decoding = 'async';
        }
      });
    }

    function loadAllCards(){
      cards.forEach(loadCard);
    }

    function updateMobileCards(){
      cards.forEach(function(card, idx){
        var isVisible = idx < visibleMobile;
        card.classList.toggle('is-mobile-hidden', !isVisible);
        if(isVisible) loadCard(card);
      });
      if(visibleMobile >= cards.length){
        clip.classList.add('is-open');
        btn.parentElement.style.display = 'none';
      }else{
        clip.classList.remove('is-open','no-clip');
        btn.parentElement.style.display = '';
      }
    }

    function checkDesktop(){
      if (mq.matches) return;
      loadAllCards();
      cards.forEach(function(card){ card.classList.remove('is-mobile-hidden'); });
      btn.parentElement.style.display = '';
      if (clip.classList.contains('is-open')) return;
      if (grid && grid.scrollHeight <= clip.clientHeight + 40) clip.classList.add('no-clip');
      else clip.classList.remove('no-clip');
    }

    function init(){
      if(mq.matches){
        clip.style.maxHeight = '';
        clip.classList.remove('no-clip');
        updateMobileCards();
      }else{
        checkDesktop();
      }
    }

    init();
    window.addEventListener('resize', init, {passive:true});
    if(mq.addEventListener) mq.addEventListener('change', init);
    else if(mq.addListener) mq.addListener(init);

    btn.addEventListener('click', function(){
      if(mq.matches){
        visibleMobile += STEP_MOBILE;
        updateMobileCards();
        return;
      }

      var card = grid && grid.firstElementChild;
      if(!card) return;
      loadAllCards();
      var gap  = parseFloat(getComputedStyle(grid).rowGap) || 22;
      var rowH = card.getBoundingClientRect().height + gap;
      var next = clip.clientHeight + rowH * 2;
      if (next >= grid.scrollHeight - rowH * 0.5) {
        clip.style.maxHeight = grid.scrollHeight + 'px';
        clip.classList.add('is-open');
        clip.style.maxHeight = '';
      } else {
        clip.style.maxHeight = next + 'px';
      }
    });
  })();
  </script>
</section>


<!-- ══════════════════════════════════════════════════════════════
     REVIEW MARQUEE — full width
     ══════════════════════════════════════════════════════════════ -->
<section class="gm-section gm-reviewsSection" id="reviews">
  <div class="gm-wrap">
    <div class="lbRev2Head">
      <div>
        <div class="gm-sectionTag"><span aria-hidden="true"></span><?= t('Reviews') ?></div>
        <h2 class="gm-h2"><?= t('Community Trust') ?></h2>
        <p class="gm-sub"><?= t('Real feedback from customers who ordered boosts, accounts, coaching and marketplace items.') ?></p>
      </div>
      <a class="lbRev2Tp" href="https://www.trustpilot.com/review/<?= defined('TRUSTPILOT_DOMAIN') ? TRUSTPILOT_DOMAIN : 'lolboost.gg' ?>" target="_blank" rel="noopener noreferrer">
        <span class="lbRev2TpStars" aria-hidden="true">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </span>
        <strong>4.9 <?= t('out of 5') ?></strong>
        <span class="lbRev2TpLabel"><i class="fa-solid fa-star" aria-hidden="true"></i> Trustpilot</span>
      </a>
    </div>
  </div>

  <!-- Row 1: right -> left -->
  <div class="lbRev3Marquee">
    <span class="lbRev3Fade lbRev3Fade--l" aria-hidden="true"></span>
    <span class="lbRev3Fade lbRev3Fade--r" aria-hidden="true"></span>
    <div class="lbRev3Track">
      <?php foreach ([false, true] as $lbDup): ?>
      <div class="lbRev3Set"<?= $lbDup ? ' aria-hidden="true"' : '' ?>>
        <?php foreach ($gmReviewsRow1 as $r): ?>
        <article class="lbRev2Card lbRev3Card">
          <div class="lbRev2Stars" aria-label="<?= (int)$r['rating'] ?>/5"><?= gm_render_stars($r['rating']) ?></div>
          <p>&ldquo;<?= htmlspecialchars($r['txt']) ?>&rdquo;</p>
          <div class="lbRev2Meta">
            <div class="gm-avatar"><img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy"></div>
            <div class="lbRev2Who">
              <b><?= htmlspecialchars($r['name']) ?></b>
              <span><?= htmlspecialchars($r['title']) ?></span>
            </div>
            <span class="lbRev2Tag"><?= htmlspecialchars($r['tag']) ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Row 2: left -> right -->
  <div class="lbRev3Marquee">
    <span class="lbRev3Fade lbRev3Fade--l" aria-hidden="true"></span>
    <span class="lbRev3Fade lbRev3Fade--r" aria-hidden="true"></span>
    <div class="lbRev3Track lbRev3Track--reverse">
      <?php foreach ([false, true] as $lbDup): ?>
      <div class="lbRev3Set"<?= $lbDup ? ' aria-hidden="true"' : '' ?>>
        <?php foreach ($gmReviewsRow2 as $r): ?>
        <article class="lbRev2Card lbRev3Card">
          <div class="lbRev2Stars" aria-label="<?= (int)$r['rating'] ?>/5"><?= gm_render_stars($r['rating']) ?></div>
          <p>&ldquo;<?= htmlspecialchars($r['txt']) ?>&rdquo;</p>
          <div class="lbRev2Meta">
            <div class="gm-avatar"><img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy"></div>
            <div class="lbRev2Who">
              <b><?= htmlspecialchars($r['name']) ?></b>
              <span><?= htmlspecialchars($r['title']) ?></span>
            </div>
            <span class="lbRev2Tag"><?= htmlspecialchars($r['tag']) ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <style>
  /* ── Reviews: full-width dual marquee ───────────────────── */
  .lbRev2Head{
    display:flex; align-items:flex-end; justify-content:space-between;
    flex-wrap:wrap; gap:20px;
    margin-bottom:38px;
  }
  .lbRev2Tp{
    display:inline-flex; align-items:center; gap:12px;
    padding:12px 18px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.04);
    text-decoration:none;
    color:#fff;
    transition:border-color .18s,background .18s;
  }
  .lbRev2Tp:hover{ border-color:rgba(0,182,122,.5); background:rgba(0,182,122,.06); }
  .lbRev2TpStars{ display:flex; gap:3px; color:#00b67a; font-size:13px; }
  .lbRev2Tp strong{ font-size:14px; font-weight:800; }
  .lbRev2TpLabel{
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:700;
    color:rgba(255,255,255,.65);
  }
  .lbRev2TpLabel i{ color:#00b67a; font-size:12px; }

  .lbRev3Marquee{
    position:relative;
    width:100%;
    overflow:hidden;
    padding:8px 0;
  }
  .lbRev3Marquee + .lbRev3Marquee{ margin-top:8px; }
  .lbRev3Fade{
    position:absolute; top:0; bottom:0;
    width:clamp(70px,12vw,200px);
    z-index:2;
    pointer-events:none;
  }
  .lbRev3Fade--l{ left:0;  background:linear-gradient(to right, var(--gm-bg,#04050f) 12%, transparent 100%); }
  .lbRev3Fade--r{ right:0; background:linear-gradient(to left,  var(--gm-bg,#04050f) 12%, transparent 100%); }
  .lbRev3Track{
    display:flex;
    width:max-content;
    animation:lbRev3Scroll 48s linear infinite;
    will-change:transform;
  }
  .lbRev3Track--reverse{ animation-direction:reverse; }
  .lbRev3Marquee:hover .lbRev3Track{ animation-play-state:paused; }
  @keyframes lbRev3Scroll{
    from{ transform:translateX(0); }
    to  { transform:translateX(-50%); }
  }
  .lbRev3Set{
    display:flex;
    gap:16px;
    padding-right:16px;   /* keeps gap seamless at the loop point */
    flex:0 0 auto;
  }
  .lbRev2Card{
    box-sizing:border-box;
    display:flex; flex-direction:column; gap:14px;
    padding:24px;
    border-radius:20px;
    border:1px solid rgba(255,255,255,.09);
    background:linear-gradient(160deg,#0c1022,#0a0d1e);
    transition:transform .2s,border-color .2s;
  }
  .lbRev3Card{
    flex:0 0 380px;
    width:380px;
  }
  .lbRev2Card:hover{ transform:translateY(-3px); border-color:rgba(99,102,241,.35); }
  .lbRev2Stars{ display:flex; gap:3px; color:#fbbf24; font-size:12px; }
  .lbRev2Stars .empty{ color:rgba(255,255,255,.15); }
  .lbRev2Card p{
    margin:0; flex:1;
    font-size:14.5px; line-height:1.68;
    color:rgba(255,255,255,.78);
  }
  .lbRev2Meta{
    display:flex; align-items:center; gap:10px;
    padding-top:14px;
    border-top:1px solid rgba(255,255,255,.07);
  }
  .lbRev2Who{ flex:1; min-width:0; }
  .lbRev2Who b{ display:block; font-size:13px; font-weight:800; }
  .lbRev2Who span{ display:block; font-size:11px; color:var(--gm-muted); }
  .lbRev2Tag{
    font-size:11px; font-weight:800;
    padding:4px 10px;
    border-radius:999px;
    color:var(--gm-blue2,#7c9fff);
    background:rgba(99,102,241,.12);
    border:1px solid rgba(99,102,241,.26);
    white-space:nowrap;
  }
  @media(max-width:640px){
    .lbRev3Card{ flex:0 0 300px; width:300px; }
    .lbRev3Track{ animation-duration:38s; }
  }
  @media(prefers-reduced-motion:reduce){
    .lbRev3Track{ animation:none; }
  }
  </style>
</section>

<!-- ══════════════════════════════════════════════════════════════
     HOW IT WORKS
     ══════════════════════════════════════════════════════════════ -->
<section class="lbHybridSteps" id="how-it-works">
  <div class="lbHybridWrap">
    <div class="lbHybridCenter">
      <div class="gm-sectionTag"><span aria-hidden="true"></span><?= t('How it works') ?></div>
      <h2 class="gm-h2"><?= t('Simple, safe and fully tracked') ?></h2>
      <p class="gm-sub"><?= t('Choose a game, configure your service and follow every update directly in your dashboard.') ?></p>
    </div>

    <div class="lbSteps3">
      <div class="lbSteps3Row">
        <div class="lbSteps3Text">
          <span class="lbSteps3Ghost" aria-hidden="true">01</span>
          <span class="lbSteps3Kicker"><?= t('Step') ?> 01</span>
          <h3><?= t('Choose your product') ?></h3>
          <p><?= t('Pick from dozens of games, services and products, then choose the offer that fits you best.') ?></p>
        </div>
        <div class="lbSteps3Visual">
          <div class="lbSteps3Glow" aria-hidden="true"></div>
          <img class="lbSteps3Img" src="/public/assets/website/images/landing/illustrations/step-1.png" alt="<?= t('Account cards illustration') ?>" loading="lazy" decoding="async">
        </div>
      </div>
      <div class="lbSteps3Row lbSteps3Row--flip">
        <div class="lbSteps3Text">
          <span class="lbSteps3Ghost" aria-hidden="true">02</span>
          <span class="lbSteps3Kicker"><?= t('Step') ?> 02</span>
          <h3><?= t('Secure checkout') ?></h3>
          <p><?= t('Pay safely with 30+ payment methods, including PayPal, credit card, Apple Pay and more.') ?></p>
        </div>
        <div class="lbSteps3Visual">
          <div class="lbSteps3Glow" aria-hidden="true"></div>
          <img class="lbSteps3Img" src="/public/assets/website/images/landing/illustrations/step-2.png" alt="<?= t('Account details illustration') ?>" loading="lazy" decoding="async">
        </div>
      </div>
      <div class="lbSteps3Row">
        <div class="lbSteps3Text">
          <span class="lbSteps3Ghost" aria-hidden="true">03</span>
          <span class="lbSteps3Kicker"><?= t('Step') ?> 03</span>
          <h3><?= t('Receive your product on time') ?></h3>
          <p><?= t('Track your order progress easily from your personal client dashboard until everything is completed.') ?></p>
        </div>
        <div class="lbSteps3Visual">
          <div class="lbSteps3Glow" aria-hidden="true"></div>
          <img class="lbSteps3Img" src="/public/assets/website/images/landing/illustrations/step-3.png" alt="<?= t('Secure delivery illustration') ?>" loading="lazy" decoding="async">
        </div>
      </div>
    </div>
  </div>

  <style>
  /* ── How it works: zig-zag, boxless ─────────────────────── */
  .lbSteps3{ max-width:1260px; margin:0 auto; }
  .lbSteps3Row{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    gap:clamp(38px,5.2vw,82px);
    align-items:center;
    padding:46px 0;
  }
  .lbSteps3Row--flip .lbSteps3Text{ order:2; }
  .lbSteps3Row--flip .lbSteps3Visual{ order:1; }
  .lbSteps3Text{ position:relative; }
  .lbSteps3Ghost{
    position:absolute;
    top:-58px; left:-18px;
    font-size:clamp(90px,10vw,140px);
    font-weight:900;
    letter-spacing:-.05em;
    line-height:1;
    color:transparent;
    -webkit-text-stroke:1px rgba(124,159,255,.16);
    pointer-events:none;
    user-select:none;
  }
  .lbSteps3Kicker{
    display:inline-block;
    margin-bottom:12px;
    font-size:12px; font-weight:900;
    letter-spacing:.18em; text-transform:uppercase;
    color:var(--gm-blue2,#7c9fff);
  }
  .lbSteps3Text h3{
    margin:0 0 14px;
    font-size:clamp(24px,2.6vw,34px);
    letter-spacing:-.02em;
    line-height:1.15;
  }
  .lbSteps3Text p{
    margin:0;
    color:var(--gm-muted,rgba(255,255,255,.58));
    line-height:1.72;
    font-size:16.5px;
    max-width:44ch;
  }
  .lbSteps3Visual{
    position:relative;
    padding:8px;
  }
  .lbSteps3Glow{
    position:absolute; inset:-8%;
    background:radial-gradient(58% 58% at 50% 50%, rgba(79,110,247,.20), transparent 72%);
    filter:blur(6px);
    pointer-events:none;
    transition:opacity .4s;
    opacity:.75;
  }
  .lbSteps3Row:hover .lbSteps3Glow{ opacity:1; }
  .lbSteps3Visual svg,
  .lbSteps3Visual img{
    position:relative;
    display:block;
    width:100%; height:auto;
    transition:transform .45s cubic-bezier(.22,.61,.36,1);
  }
  .lbSteps3Img{
    display:block;
    width:min(600px,100%);
    height:auto;
    object-fit:contain;
    position:relative;
    z-index:1;
    transition:transform .22s ease;
  }

  .lbSteps3Row:hover .lbSteps3Visual svg,
  .lbSteps3Row:hover .lbSteps3Visual img{ transform:translateY(-6px) scale(1.015); }
  /* The illustrations already carry their own lighting — no extra glow,
     background or drop-shadow behind them. */
  .lbSteps3Glow{ display:none !important; }
  .lbSteps3Visual{
    background:none !important;
    background-image:none !important;
    box-shadow:none !important;
    filter:none !important;
    backdrop-filter:none !important;
    border:0 !important;
  }
  .lbSteps3Visual::before,
  .lbSteps3Visual::after{ content:none !important; display:none !important; }
  .lbSteps3Visual svg,
  .lbSteps3Visual img,
  .lbSteps3Img{
    background:none !important;
    box-shadow:none !important;
    filter:none !important;
  }
  @media(max-width:860px){
    .lbSteps3Row,
    .lbSteps3Row--flip{ grid-template-columns:1fr; gap:18px; padding:34px 0; }
    .lbSteps3Row--flip .lbSteps3Text{ order:1; }
    .lbSteps3Row--flip .lbSteps3Visual{ order:2; }
    .lbSteps3Ghost{ top:-40px; left:-8px; }
    .lbSteps3Visual{ max-width:420px; }
  }
  

  /* ── How it works: richer gradient background + polished visuals ── */
  .lbHybridSteps{
    position:relative !important;
    overflow:hidden !important;
    padding:110px 0 !important;
    background:
      radial-gradient(760px 420px at 12% 10%, rgba(96,165,250,.16), transparent 66%),
      radial-gradient(760px 420px at 88% 42%, rgba(129,140,248,.14), transparent 68%),
      linear-gradient(180deg, rgba(255,255,255,.018), rgba(99,102,241,.045) 44%, rgba(255,255,255,.014)) !important;
    isolation:isolate;
  }
  .lbHybridSteps::before{
    content:"";
    position:absolute;
    inset:0;
    z-index:-1;
    pointer-events:none;
    background:
      linear-gradient(to right, rgba(255,255,255,.045) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size:78px 78px;
    mask-image:radial-gradient(closest-side at 50% 32%, #000 0%, transparent 78%);
    opacity:.28;
  }
  .lbHybridSteps::after{
    content:"";
    position:absolute;
    left:50%;
    top:210px;
    bottom:130px;
    width:1px;
    z-index:-1;
    pointer-events:none;
    transform:translateX(-50%);
    background:linear-gradient(180deg, transparent, rgba(129,140,248,.30), rgba(96,165,250,.18), transparent);
  }
   .lbSteps3Row{
    position:relative;
    border-radius:34px;
    padding:58px 28px !important;
  }
  .lbSteps3Row::before{
    content:"";
    position:absolute;
    inset:0;
    border-radius:inherit;
    pointer-events:none;
    opacity:.82;
  }
  .lbSteps3Row > *{ position:relative; z-index:1; }
  .lbSteps3Visual{
    min-height:320px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:28px;
  }
  .lbSteps3Glow{
    inset:-18% !important;
    background:
      radial-gradient(42% 42% at 50% 48%, rgba(96,165,250,.28), transparent 72%),
      radial-gradient(52% 52% at 56% 52%, rgba(167,139,250,.18), transparent 78%) !important;
    filter:blur(16px) !important;
  }
  .lbSteps3Visual svg,
  .lbSteps3Visual img{ max-width:600px; filter:drop-shadow(0 24px 48px rgba(0,0,0,.34));  }
  @media(min-width:861px){
    .lbSteps3Row:nth-child(1) .lbSteps3Visual img{ max-width:620px; }
    .lbSteps3Row:nth-child(2) .lbSteps3Visual img{ max-width:580px; }
    .lbSteps3Row:nth-child(3) .lbSteps3Visual img{ max-width:640px; }
    .lbSteps3Row:nth-child(1) .lbSteps3Visual,
    .lbSteps3Row:nth-child(3) .lbSteps3Visual{ justify-content:flex-start; }
    .lbSteps3Row:nth-child(2) .lbSteps3Visual{ justify-content:flex-end; }
  }
  @media(max-width:860px){
    .lbHybridSteps{ padding:76px 0 !important; }
    .lbHybridSteps::after{ display:none; }
    .lbSteps3Row,
    .lbSteps3Row--flip{ padding:30px 18px !important; border-radius:26px; }
    .lbSteps3Row::before{ opacity:.70; }
    .lbSteps3Visual{ min-height:190px; }
  }
  @media(max-width:480px){
    .lbSteps3Visual{ min-height:166px; padding:0; }
    .lbSteps3Visual svg,
  .lbSteps3Visual img{ max-width:330px; }
  }

  /* ── XL desktop illustration sizing ─────────────────────── */
  @media(min-width:861px){
    .lbHybridSteps{
      padding:130px 0 138px !important;
    }
    .lbHybridSteps .lbHybridWrap{
      max-width:1540px !important;
    }
    .lbSteps3{
      max-width:1500px !important;
    }
    .lbSteps3Row{
      grid-template-columns:minmax(360px,.82fr) minmax(0,1.38fr) !important;
      gap:clamp(52px,6.5vw,116px) !important;
      padding:74px 18px !important;
      min-height:520px;
    }
    .lbSteps3Row--flip{
      grid-template-columns:minmax(0,1.38fr) minmax(360px,.82fr) !important;
    }
    .lbSteps3Visual{
      min-height:460px !important;
      padding:0 !important;
    }
    .lbSteps3Img,
    .lbSteps3Visual img,
    .lbSteps3Visual svg{
      width:100% !important;
      max-width:820px !important;
    }
    .lbSteps3Row:nth-child(1) .lbSteps3Visual img{ max-width:850px !important; }
    .lbSteps3Row:nth-child(2) .lbSteps3Visual img{ max-width:800px !important; }
    .lbSteps3Row:nth-child(3) .lbSteps3Visual img{ max-width:880px !important; }
    .lbSteps3Text h3{
      font-size:clamp(30px,2.9vw,42px) !important;
    }
    .lbSteps3Text p{
      font-size:17.5px !important;
      max-width:46ch !important;
    }
  }

  @media(min-width:1280px){
    .lbSteps3Row:nth-child(1) .lbSteps3Visual img{ transform:scale(1.08); transform-origin:left center; }
    .lbSteps3Row:nth-child(2) .lbSteps3Visual img{ transform:scale(1.08); transform-origin:right center; }
    .lbSteps3Row:nth-child(3) .lbSteps3Visual img{ transform:scale(1.10); transform-origin:left center; }
    .lbSteps3Row:hover .lbSteps3Visual img{ transform:translateY(-6px) scale(1.12) !important; }
  }
</style>
</section>

<!-- ══════════════════════════════════════════════════════════════
     LOOTBOXES / LOYALTY REWARDS
     ══════════════════════════════════════════════════════════════ -->
<section class="gm-section gm-lootSection" id="lootboxes">
  <div class="gm-wrap">
    <div class="gm-headRow">
      <div>
        <div class="gm-sectionTag"><span aria-hidden="true"></span><?= t('Free rewards program') ?></div>
        <h2 class="gm-h2"><?= t('Earn points. Open Lootboxes. Win real rewards.') ?></h2>
        <p class="gm-sub"><?= t('Every order earns you Reward Points through the Loyalty Program — spend them to open Lootboxes for Reward Points, discount coupons, wallet credit and order perks. The Daily Gift box is free for everyone, every 24 hours.') ?></p>
      </div>
      <a class="gm-btn gm-btnPrimary gm-btnSmall" href="<?= $gmBase ?>/lootboxes">
        <span><?= t('Open the Lootboxes') ?></span><i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="gm-lootGrid">
      <?php
      $gmLootBoxes = [
        ['file' => 'daily-gift.png', 'name' => t('Daily Gift'), 'tag' => t('Free')],
        ['file' => 'starter-box.png', 'name' => t('Starter Box'), 'tag' => null],
        ['file' => 'silver-box.png', 'name' => t('Silver Box'), 'tag' => null],
        ['file' => 'gold-box.png', 'name' => t('Gold Box'), 'tag' => null],
        ['file' => 'diamond-box.png', 'name' => t('Diamond Box'), 'tag' => null],
        ['file' => 'challenger-box.png', 'name' => t('Challenger Box'), 'tag' => null],
      ];
      foreach ($gmLootBoxes as $gmLb):
      ?>
        <a class="gm-lootTile" href="<?= $gmBase ?>/lootboxes">
          <span class="gm-lootTile__img"><img src="<?= ASSET_URL ?>/website/images/rewards/boxes/<?= $gmLb['file'] ?>" alt="<?= htmlspecialchars($gmLb['name'], ENT_QUOTES) ?>" loading="lazy"></span>
          <span class="gm-lootTile__name"><?= $gmLb['name'] ?></span>
          <?php if ($gmLb['tag']): ?><span class="gm-lootTile__tag"><?= $gmLb['tag'] ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="gm-lootPerks">
      <span><i class="fa-solid fa-coins" aria-hidden="true"></i><?= t('Up to 8% cashback per order') ?></span>
      <span><i class="fa-solid fa-gift" aria-hidden="true"></i><?= t('Free Daily Gift, no purchase needed') ?></span>
      <span><i class="fa-solid fa-crown" aria-hidden="true"></i><?= t('7 permanent loyalty tiers') ?></span>
      <a href="<?= $gmBase ?>/loyalty"><?= t('See the Loyalty Program') ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>
<style>
.gm-lootSection{ position:relative; overflow:hidden; }
.gm-lootSection::before{ content:""; position:absolute; inset:0; pointer-events:none; background:
    radial-gradient(700px 340px at 85% 10%, rgba(99,102,241,.14), transparent 62%),
    radial-gradient(600px 320px at 10% 90%, rgba(56,189,248,.08), transparent 60%); }
.gm-lootGrid{ position:relative; z-index:1; display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; margin-bottom:22px; perspective:900px; }
.gm-lootTile{ position:relative; display:flex; flex-direction:column; align-items:center; gap:10px; padding:22px 14px; border-radius:22px; border:1px solid rgba(255,255,255,.10); background:linear-gradient(160deg,#0c1022,#0a0d1e); box-shadow:0 12px 34px rgba(0,0,0,.35); text-decoration:none; transition:transform .25s ease,border-color .25s ease,box-shadow .25s ease; }
.gm-lootTile:hover{ transform:translateY(-5px); border-color:rgba(99,102,241,.4); box-shadow:0 24px 60px rgba(0,0,0,.5),0 0 30px rgba(99,102,241,.16); }
.gm-lootTile__img{ display:flex; align-items:center; justify-content:center; width:88px; height:88px; animation:gmLootBob 4.2s ease-in-out infinite; }
.gm-lootTile:nth-child(2) .gm-lootTile__img{ animation-delay:.25s; }
.gm-lootTile:nth-child(3) .gm-lootTile__img{ animation-delay:.5s; }
.gm-lootTile:nth-child(4) .gm-lootTile__img{ animation-delay:.75s; }
.gm-lootTile:nth-child(5) .gm-lootTile__img{ animation-delay:1s; }
.gm-lootTile:nth-child(6) .gm-lootTile__img{ animation-delay:1.25s; }
.gm-lootTile__img img{ width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 12px 14px rgba(0,0,0,.42)) drop-shadow(0 14px 24px rgba(99,102,241,.34)); }
.gm-lootTile__name{ position:relative; z-index:1; font-size:13px; font-weight:800; color:rgba(255,255,255,.86); text-align:center; line-height:1.25; }
.gm-lootTile__tag{ position:absolute; top:10px; right:10px; z-index:1; padding:3px 8px; border-radius:999px; background:rgba(34,197,94,.16); border:1px solid rgba(34,197,94,.32); color:#4ade80; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.04em; animation:gmLootPulse 2.2s ease-in-out infinite; }
.gm-lootPerks{ position:relative; z-index:1; display:flex; align-items:center; flex-wrap:wrap; gap:12px 20px; padding-top:18px; border-top:1px solid rgba(255,255,255,.08); }
.gm-lootPerks span{ display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,.68); font-size:14px; font-weight:700; }
.gm-lootPerks span i{ color:var(--gm-blue2,#7c9fff); }
/* Reads as a real CTA, not as another perk line */
.gm-lootPerks a{
  margin-left:auto;
  display:inline-flex; align-items:center; gap:10px;
  min-height:44px; padding:0 20px;
  border-radius:999px;
  color:#fff; font-weight:800; font-size:14px; text-decoration:none;
  white-space:nowrap;
  background:linear-gradient(135deg, rgba(79,110,247,.22), rgba(99,102,241,.10));
  border:1px solid rgba(129,140,248,.34);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.09);
  transition:background .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.gm-lootPerks a i{ font-size:12px; transition:transform .2s ease; }
.gm-lootPerks a:hover{
  color:#fff;
  transform:translateY(-2px);
  background:linear-gradient(135deg, rgba(79,110,247,.38), rgba(99,102,241,.18));
  border-color:rgba(147,180,255,.52);
  box-shadow:0 14px 32px rgba(79,110,247,.24), inset 0 1px 0 rgba(255,255,255,.14);
}
.gm-lootPerks a:hover i{ transform:translateX(4px); }
@keyframes gmLootBob{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-6px); } }
@keyframes gmLootPulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(34,197,94,.35); } 50%{ box-shadow:0 0 0 5px rgba(34,197,94,0); } }
@media(prefers-reduced-motion:reduce){
  .gm-lootTile__img, .gm-lootTile__tag{ animation:none!important; }
}
@media(max-width:980px){
  .gm-lootGrid{ grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@media(max-width:640px){
  .gm-lootGrid{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
  .gm-lootTile{ padding:16px 10px; }
  .gm-lootTile__img{ width:74px; height:74px; }
  .gm-lootPerks{ gap:14px; }
  .gm-lootPerks a{
    margin:6px 0 0;
    width:100%;
    justify-content:center;
    min-height:50px;
    border-radius:16px;
    font-size:14.5px;
    background:linear-gradient(135deg, #4f6ef7, #6366f1);
    border-color:rgba(147,180,255,.42);
    box-shadow:0 14px 34px rgba(79,110,247,.28), inset 0 1px 0 rgba(255,255,255,.16);
  }
}
</style>

<!-- ══════════════════════════════════════════════════════════════
     FAQ / SUPPORT
     ══════════════════════════════════════════════════════════════ -->
<section class="gm-supportfaq gm-faqRebuild lbFaq4" id="support">
  <div class="gm-wrap">
    <div class="lbFaq4Shell">

      <div class="lbFaq4Head">
        <div class="gm-sectionTag"><span aria-hidden="true"></span><?= t('FAQ') ?></div>
        <h2 class="gm-h2"><?= t('Frequently asked questions') ?></h2>
      </div>

      <div class="gmFaq-tabs lbFaq4Tabs" role="tablist">
        <button type="button" class="gmFaq-tab is-active" data-faq-cat="general"><?= t('General') ?></button>
        <button type="button" class="gmFaq-tab" data-faq-cat="boosting"><?= t('Boosting') ?></button>
        <button type="button" class="gmFaq-tab" data-faq-cat="accounts"><?= t('Accounts') ?></button>
        <button type="button" class="gmFaq-tab" data-faq-cat="payments"><?= t('Payments') ?></button>
        <button type="button" class="gmFaq-tab" data-faq-cat="security"><?= t('Security') ?></button>
      </div>

      <div class="gmFaq-accordion" id="faq">

        <div class="gmFaq-item open" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('What is LoLBoost and how does it work?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('LoLBoost.gg is an all-in-one platform for rank boosts, smurf accounts, coaching and marketplace items. Choose a game, configure your order, pay securely and track everything from your dashboard.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('How do I place an order?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Pick the service you want, select your options, add it to checkout and complete payment. After that, your order appears in your account area with live status updates.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('Can I track my order?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Yes. Every order can be tracked from your dashboard. You can see progress, delivery notes and support messages in one place.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('Which games are supported?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('We support multiple competitive games and marketplace categories. The available games and services are shown directly on the landing page and in the order pages.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('Do I need an account to order?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('You can browse services without an account. For order tracking, delivery details and support communication, creating an account is recommended.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="general">
          <button class="gmFaq-btn" type="button"><span><?= t('How can I contact support?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('You can contact us through live chat or Discord. For active orders, always include your order number so support can help faster.') ?></div></div>
        </div>

        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('How does boosting work?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('After checkout, a verified booster receives your order and starts according to the selected options. You can follow the progress from your dashboard.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Is boosting safe?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Boosts are completed manually by real players. No bots or scripts are used. Security options such as offline mode and VPN handling are available where needed.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Can I choose Duo boosting?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Yes. With Duo boosting, you keep access to your account and play together with the booster instead of sharing login details.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('How long does boosting take?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Start time and completion time depend on rank, queue, game mode and selected extras. Most standard boosts start quickly after payment.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Can I play while my boost is active?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('For Solo boosting, avoid playing ranked games during the order unless support confirms it is safe. For Duo boosting, you can play with the booster directly.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="boosting" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('What happens if LP gains change?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('If your LP gains change heavily during the order, support reviews the case and adjusts the next steps fairly based on the order progress.') ?></div></div>
        </div>

        <div class="gmFaq-item" data-faq-cat="accounts" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Are accounts hand leveled?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Accounts are prepared with quality and account safety in mind. Details such as level, region, champions, skins or extras are shown on the product page.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="accounts" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Can I change the email?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('If email change is included, the product page or delivery message will explain the steps. Support can help if you have trouble securing the account.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="accounts" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('How is account delivery handled?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Account products are delivered through your dashboard or order page. Instant products are usually available shortly after successful payment.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="accounts" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Are accounts region locked?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Some accounts are tied to a specific region. The region is listed in the product details before checkout.') ?></div></div>
        </div>

        <div class="gmFaq-item" data-faq-cat="payments" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('What payment methods do you accept?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Available payment methods can include PayPal, card payments, crypto and local options depending on your region and checkout provider.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="payments" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Are payments secure?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Payments are processed through secure checkout providers. Sensitive payment details are not handled manually by boosters or sellers.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="payments" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Do you offer refunds?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('If an order has not started, support can review a refund request. Orders already in progress are checked individually.') ?></div></div>
        </div>

        <div class="gmFaq-item" data-faq-cat="security" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Is my personal data protected?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Your data is handled carefully and only used for order processing and support. Login or payment details are never shared publicly.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="security" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Are boosters verified?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('Boosters are reviewed before they can work on orders. Performance, reliability and customer feedback are monitored.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="security" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('Do you use bots or scripts?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('No. Orders are completed manually by real players. Bots, scripts or automated gameplay tools are not part of our service.') ?></div></div>
        </div>
        <div class="gmFaq-item" data-faq-cat="security" style="display:none">
          <button class="gmFaq-btn" type="button"><span><?= t('What should I do after completion?') ?></span><span class="gmFaq-chev"><i class="fa-solid fa-chevron-down"></i></span></button>
          <div class="gmFaq-panel"><div class="gmFaq-inner"><?= t('After completion, change your password, enable two factor authentication where available and contact support if anything looks unusual.') ?></div></div>
        </div>

      </div><!-- /gmFaq-accordion -->

      <p class="lbFaq4Foot">
        <?= t('Still stuck?') ?>
        <a href="#" onclick="return window.lbOpenLiveChat ? window.lbOpenLiveChat() : false;"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i> <?= t('Open live chat') ?></a>
        <span aria-hidden="true">&middot;</span>
        <a href="/discord"><i class="fa-brands fa-discord" aria-hidden="true"></i> <?= t('Join Discord') ?></a>
      </p>

    </div>
  </div>

  <style>
  /* ── FAQ: pure, centered, boxless ───────────────────────── */
  .lbFaq4 .lbFaq4Shell{
    max-width:860px;
    margin:0 auto;
  }
  .lbFaq4Head{
    text-align:center;
    margin-bottom:30px;
  }
  .lbFaq4Head .gm-sectionTag{ justify-content:center; }

  /* tabs: compact premium pills */
  .lbFaq4 .gmFaq-tabs.lbFaq4Tabs{
    position:relative;
    display:flex !important;
    gap:8px !important;
    flex-wrap:wrap !important;
    justify-content:center !important;
    align-items:center !important;
    width:fit-content !important;
    max-width:100% !important;
    margin:0 auto 24px !important;
    padding:7px !important;
    background:none !important;
    border:none !important;
    box-shadow:none !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
    overflow:visible !important;
  }
  .lbFaq4 .gmFaq-tab{
    position:relative;
    isolation:isolate;
    min-height:42px;
    padding:0 18px !important;
    border:1px solid transparent !important;
    border-radius:999px !important;
    background:transparent !important;
    color:rgba(224,232,255,.68) !important;
    font-size:13px !important;
    font-weight:900 !important;
    letter-spacing:.01em !important;
    line-height:1 !important;
    cursor:pointer !important;
    appearance:none !important;
    -webkit-appearance:none !important;
    white-space:nowrap !important;
    transition:color .16s ease, background .16s ease, border-color .16s ease, transform .16s ease, box-shadow .16s ease !important;
    pointer-events:auto !important;
  }
  .lbFaq4 .gmFaq-tab::after{ display:none !important; }
  .lbFaq4 .gmFaq-tab::before{
    content:"";
    position:absolute;
    inset:5px;
    z-index:-1;
    border-radius:inherit;
    background:linear-gradient(135deg, rgba(79,110,247,.0), rgba(96,165,250,.0));
    opacity:0;
    transition:opacity .16s ease;
  }
  .lbFaq4 .gmFaq-tab:hover{
    color:#fff !important;
    background:rgba(255,255,255,.055) !important;
    border-color:rgba(255,255,255,.10) !important;
    transform:translateY(-1px);
  }
  .lbFaq4 .gmFaq-tab.is-active{
    color:#fff !important;
    background:linear-gradient(135deg, #345cf6, #4f8cff) !important;
    border-color:rgba(151,184,255,.55) !important;
    box-shadow:0 12px 30px rgba(59,130,246,.28), inset 0 1px 0 rgba(255,255,255,.18) !important;
  }
  .lbFaq4 .gmFaq-tab.is-active::before{ opacity:1; }
  .lbFaq4 .gmFaq-tab:focus-visible{
    outline:2px solid rgba(124,159,255,.70) !important;
    outline-offset:3px !important;
  }

  /* accordion: separate dark rows, matching the site's card language */
  html body.landing.gamingmarket .lbFaq4 .gmFaq-accordion{
    background:none !important;
    border:none !important;
    box-shadow:none !important;
    border-radius:0 !important;
    padding:0 !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item{
    background:linear-gradient(145deg,rgba(10,16,36,.94),rgba(7,12,29,.96)) !important;
    border:1px solid rgba(109,140,255,.10) !important;
    border-radius:20px !important;
    box-shadow:none !important;
    margin:0 0 10px !important;
    overflow:hidden !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
    transition:border-color .2s ease,background .2s ease,transform .2s ease !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item:last-child{margin-bottom:0 !important;}
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item:hover{
    border-color:rgba(109,140,255,.24) !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item.open{
    background:linear-gradient(145deg,rgba(12,20,46,.97),rgba(8,14,34,.98)) !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-btn{
    width:100%;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
    min-height:76px !important;
    padding:20px 22px 20px 26px !important;
    background:none !important;
    border:none;
    color:#fff;
    font-size:16px; font-weight:700;
    text-align:left; cursor:pointer;
    transition:color .18s,padding-left .25s cubic-bezier(.22,.61,.36,1);
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-btn:hover{color:#fff !important;}
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item.open .gmFaq-btn{color:#fff !important;}
  html body.landing.gamingmarket .lbFaq4 .gmFaq-chev{
    width:36px; height:36px; flex:0 0 36px;
    display:grid; place-items:center;
    border-radius:11px;
    border:1px solid rgba(143,178,255,.20) !important;
    background:linear-gradient(180deg,rgba(143,178,255,.10),rgba(143,178,255,.045)) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.06) !important;
    color:#9db7ff;
    font-size:12px;
    transition:border-color .2s,color .2s,background .2s,box-shadow .2s !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-chev i{
    transition:transform .3s cubic-bezier(.22,.61,.36,1) !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item.open .gmFaq-chev{
    transform:none !important;
    border-color:rgba(109,140,255,.48) !important;
    background:linear-gradient(135deg,rgba(52,92,246,.42),rgba(79,140,255,.25)) !important;
    box-shadow:0 8px 22px rgba(52,92,246,.18),inset 0 1px 0 rgba(255,255,255,.10) !important;
    color:#fff !important;
  }
  html body.landing.gamingmarket .lbFaq4 .gmFaq-item.open .gmFaq-chev i{transform:rotate(180deg) !important;}
  .lbFaq4 .gmFaq-panel{
    max-height:0 !important;
    overflow:hidden;
    transition:max-height .35s ease;
  }
  .lbFaq4 .gmFaq-item.open .gmFaq-panel{ max-height:320px !important; }
  .lbFaq4 .gmFaq-inner{
    padding:0 70px 24px 26px !important;
    color:rgba(255,255,255,.60);
    line-height:1.72;
    font-size:15px;
    max-width:68ch;
  }

  /* quiet support line */
  .lbFaq4Foot{
    margin:30px 0 0;
    text-align:center;
    font-size:14.5px;
    color:var(--gm-muted,rgba(255,255,255,.58));
    display:flex; align-items:center; justify-content:center;
    gap:14px; flex-wrap:wrap;
  }
  .lbFaq4Foot a{
    display:inline-flex; align-items:center; gap:7px;
    color:var(--gm-blue2,#7c9fff);
    font-weight:700;
    text-decoration:none;
    transition:color .15s;
  }
  .lbFaq4Foot a:hover{ color:#fff; }
  .lbFaq4Foot span{ color:rgba(255,255,255,.22); }
  @media(max-width:640px){
    .lbFaq4 .gmFaq-tabs.lbFaq4Tabs{
      width:100% !important;
      justify-content:flex-start !important;
      flex-wrap:nowrap !important;
      margin-bottom:18px !important;
      padding:6px !important;
      overflow-x:auto !important;
      overflow-y:hidden !important;
      scrollbar-width:none !important;
      -webkit-overflow-scrolling:touch !important;
    }
    .lbFaq4 .gmFaq-tabs.lbFaq4Tabs::-webkit-scrollbar{ display:none !important; }
    .lbFaq4 .gmFaq-tab{
      min-height:38px !important;
      padding:0 15px !important;
      font-size:12.5px !important;
      flex:0 0 auto !important;
    }
  }
  </style>

  <script>
  (function(){
    function initLbFaq4(){
      var root = document.querySelector('.lbFaq4');
      if(!root || root.dataset.faqReady === '1') return;
      root.dataset.faqReady = '1';

      var tabs = Array.prototype.slice.call(root.querySelectorAll('.gmFaq-tab[data-faq-cat]'));
      var items = Array.prototype.slice.call(root.querySelectorAll('.gmFaq-item[data-faq-cat]'));

      function setCategory(cat){
        var firstVisible = null;

        tabs.forEach(function(tab){
          var active = tab.getAttribute('data-faq-cat') === cat;
          tab.classList.toggle('is-active', active);
          tab.setAttribute('aria-selected', active ? 'true' : 'false');
          tab.setAttribute('tabindex', active ? '0' : '-1');
        });

        items.forEach(function(item){
          var visible = item.getAttribute('data-faq-cat') === cat;
          item.style.display = visible ? '' : 'none';
          if(!visible) item.classList.remove('open');
          if(visible && !firstVisible) firstVisible = item;
        });

        if(firstVisible) firstVisible.classList.add('open');
      }

      tabs.forEach(function(tab){
        tab.setAttribute('role', 'tab');
        tab.addEventListener('click', function(e){
          e.preventDefault();
          setCategory(tab.getAttribute('data-faq-cat'));
        });
      });

      items.forEach(function(item){
        var btn = item.querySelector('.gmFaq-btn');
        if(!btn) return;
        btn.addEventListener('click', function(){
          if(item.style.display === 'none') return;
          var wasOpen = item.classList.contains('open');
          items.forEach(function(other){
            if(other.getAttribute('data-faq-cat') === item.getAttribute('data-faq-cat')) {
              other.classList.remove('open');
            }
          });
          if(!wasOpen) item.classList.add('open');
        });
      });

      var active = root.querySelector('.gmFaq-tab.is-active[data-faq-cat]');
      setCategory(active ? active.getAttribute('data-faq-cat') : 'general');
    }

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', initLbFaq4);
    }else{
      initLbFaq4();
    }
  })();
  </script>

</section>

<!-- ══════════════════════════════════════════════════════════════
     CTA BAND
     ══════════════════════════════════════════════════════════════ -->
<section class="gm-ctaBand lbCtaNew lbCta5" id="gmCta">
  <div class="lbCta5Glow" aria-hidden="true"></div>
  <div class="gm-wrap lbCta5Wrap">
    <h2 class="lbCta5Title">
      <?= t('Your next rank is') ?><br>
      <em><?= t('one order away.') ?></em>
    </h2>
    <p class="lbCta5Sub"><?= t('Set up an order in under two minutes — protected from checkout to completion.') ?></p>
    <div class="lbCta5Actions">
      <button class="lbCta5Btn lbCta5BtnPrimary" type="button" onclick="gmOpenNavSearch()">
        <?= t('Explore Marketplace') ?><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
      </button>
      <?php if (!$gmIsLoggedIn): ?>
      <button type="button" class="lbCta5Btn lbCta5BtnGhost" id="gmCtaLoginBtn" data-login-trigger="1" aria-controls="login_modal"><?= t('Create Account') ?></button>
      <?php endif; ?>
    </div>
    <div class="lbCta5Trust" aria-label="<?= t('Trust points') ?>">
      <span class="lbCta5TrustItem"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= t('Protected checkout') ?></span>
      <span class="lbCta5TrustSep" aria-hidden="true"></span>
      <span class="lbCta5TrustItem"><i class="fa-solid fa-headset" aria-hidden="true"></i><?= t('24/7 support') ?></span>
      <span class="lbCta5TrustSep" aria-hidden="true"></span>
      <a class="lbCta5TrustItem lbCta5TrustTp" href="https://www.trustpilot.com/review/<?= defined('TRUSTPILOT_DOMAIN') ? TRUSTPILOT_DOMAIN : 'lolboost.gg' ?>" target="_blank" rel="noopener noreferrer">
        <i class="fa-solid fa-star" aria-hidden="true"></i>4.9 <?= t('on Trustpilot') ?>
      </a>
    </div>
  </div>

  <style>
  /* ── Final CTA: quiet, centered ─────────────────────────── */
  .lbCta5{
    position:relative;
    padding:130px 0 150px !important;
    overflow:hidden;
    background:transparent !important;
    border:none !important;
    box-shadow:none !important;
  }
  .lbCta5Glow{
    position:absolute;
    left:50%; top:58%;
    width:min(880px,120vw); height:520px;
    transform:translate(-50%,-50%);
    background:radial-gradient(50% 50% at 50% 50%, rgba(79,110,247,.16), transparent 70%);
    filter:blur(10px);
    pointer-events:none;
  }
  .lbCta5Wrap{
    position:relative;
    z-index:5;                     /* above the section's dark ::before/::after overlays */
    text-align:center;
    max-width:760px !important;
  }
  .lbCta5Title{
    margin:0;
    color:#fff;
    font-size:clamp(36px,4.6vw,60px);
    letter-spacing:-.03em;
    line-height:1.08;
    text-shadow:0 4px 30px rgba(0,0,0,.5);
  }
  .lbCta5Title em{
    font-style:normal;
    background:linear-gradient(90deg,#7c9fff,#a5c4ff);
    -webkit-background-clip:text; background-clip:text;
    color:transparent;
    text-shadow:none;
    filter:drop-shadow(0 4px 24px rgba(79,110,247,.35));
  }
  .lbCta5Sub{
    margin:20px auto 0;
    color:rgba(235,240,255,.82);
    font-size:17px; line-height:1.7;
    text-shadow:0 2px 18px rgba(0,0,0,.45);
  }
  .lbCta5Actions{
    display:flex; justify-content:center; gap:12px; flex-wrap:wrap;
    margin-top:34px;
  }
  .lbCta5Btn{
    display:inline-flex; align-items:center; gap:10px;
    padding:15px 30px;
    border-radius:999px;
    font-size:15.5px; font-weight:800;
    cursor:pointer;
    transition:transform .2s,filter .2s,border-color .2s,background .2s;
  }
  .lbCta5BtnPrimary{
    color:#fff;
    background:linear-gradient(135deg,var(--gm-blue,#4f6ef7),var(--gm-blueH,#3b58e8));
    border:1px solid rgba(120,122,255,.45);
    box-shadow:0 16px 44px rgba(99,102,241,.35);
  }
  .lbCta5BtnPrimary:hover{ transform:translateY(-2px); filter:brightness(1.1); }
  .lbCta5BtnPrimary i{ font-size:13px; transition:transform .2s; }
  .lbCta5BtnPrimary:hover i{ transform:translateX(4px); }
  .lbCta5BtnGhost{
    color:#fff;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.28);
    -webkit-backdrop-filter:blur(8px);
    backdrop-filter:blur(8px);
  }
  .lbCta5BtnGhost:hover{
    border-color:rgba(255,255,255,.5);
    background:rgba(255,255,255,.14);
    transform:translateY(-2px);
  }
  .lbCta5Trust{
    margin-top:30px;
    display:flex; align-items:center; justify-content:center;
    gap:16px; flex-wrap:wrap;
  }
  .lbCta5TrustItem{
    display:inline-flex; align-items:center; gap:9px;
    font-size:13.5px; font-weight:600;
    color:rgba(235,240,255,.85);
    text-decoration:none;
    text-shadow:0 2px 14px rgba(0,0,0,.5);
    transition:color .15s;
  }
  .lbCta5TrustItem i{
    width:26px; height:26px;
    display:grid; place-items:center;
    border-radius:9px;
    font-size:11px;
    color:#9db9ff;
    background:rgba(124,159,255,.12);
    border:1px solid rgba(124,159,255,.22);
  }
  .lbCta5TrustTp:hover{ color:#fff; }
  .lbCta5TrustTp i{
    color:#00b67a;
    background:rgba(0,182,122,.12);
    border-color:rgba(0,182,122,.28);
  }
  .lbCta5TrustSep{
    width:4px; height:4px; border-radius:50%;
    background:rgba(255,255,255,.22);
  }
  @media(max-width:560px){
    /* Keep the three trust points on one line instead of stacking them. */
    .lbCta5TrustSep{ display:none; }
    .lbCta5Trust{
      flex-direction:row;
      flex-wrap:nowrap;
      justify-content:center;
      gap:10px;
    }
    .lbCta5TrustItem{
      gap:6px;
      font-size:11px;
      white-space:nowrap;
    }
    .lbCta5TrustItem i{
      width:20px; height:20px;
      border-radius:7px;
      font-size:9px;
    }
  }
  @media(max-width:640px){
    .lbCta5{ padding:96px 0 110px !important; }
    .lbCta5Btn{ width:100%; justify-content:center; }
  }
  </style>
</section>

<!-- ── Scroll reveal (global) ─────────────────────────────── -->
<style>
[data-lbrv]{ opacity:1 !important; transform:none !important; transition:none !important; will-change:auto !important; }
[data-lbrv].lbrv-in{ opacity:1; transform:none; }
@media(prefers-reduced-motion:reduce){
  [data-lbrv]{ opacity:1 !important; transform:none !important; transition:none !important; }
}

/* ── iOS Safari / small-screen hardening ─────────────────── */
.lbGames2Thumb img, .lbSteps3Visual svg, .lbSteps3Visual img, .lbRev2Card, .lbMkt2Tile{ max-width:100%; }
/* aspect-ratio fallback for older iOS (<15) */
@supports not (aspect-ratio: 1){
  .lbGames2Thumb{ height:0; padding-bottom:133.33%; position:relative; }
  .lbGames2Thumb img, .lbGames2Thumb .lbGames2Fallback{ position:absolute; inset:0; height:100%; }
}
@media(max-width:480px){
  .lbSteps3Ghost{ font-size:72px; top:-30px; }
  .lbSteps3Text h3{ font-size:22px; }
  .lbSteps3Text p{ font-size:15px; }
  .lbGames2Name{ font-size:13.5px; }
  .lbFaq4 .gmFaq-btn{ font-size:14.5px; }
  .lbFaq4 .gmFaq-inner{ padding-right:8px !important; }
  .lbCta5Title{ font-size:clamp(30px,9vw,38px); }
  .lbMkt2Tile{ padding:20px; }
  .lbMkt2Head h3{ font-size:19px; }
  .lbRev3Card{ padding:20px; }
}
/* prevent iOS tap highlight flashes on interactive cards */
.lbGames2MainLink, .lbGames2Tags a, .lbMkt2Tile, .lbFaq4 .gmFaq-btn, .lbCta5Btn, .lbGames2MoreBtn{
  -webkit-tap-highlight-color:transparent;
}
</style>
<!-- Removed scroll reveal observer so content is visible immediately. -->

<!-- Landing JS removed: all landing content is rendered directly, no scroll reveal loader. -->

<!-- Removed animated DOM starfield for faster first paint. -->



<script>
(function(){
  function scrollToHeroTarget(evt){
    var trigger = evt.currentTarget;
    var selector = trigger && trigger.getAttribute('data-scroll-target') || '#boosting';
    var target = document.querySelector(selector);
    if(!target) return;

    evt.preventDefault();
    evt.stopPropagation();

    var top = target.getBoundingClientRect().top + window.pageYOffset;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
  }

  document.querySelectorAll('.gm-heroScrollDown, .lb-visualHero__scroll').forEach(function(el){
    el.addEventListener('click', scrollToHeroTarget, false);
  });
})();
</script>




<style id="gm-command-center-modal-v5-zoom-fix">
/* Final Command Center polish, built for site zoom 0.88 and reliable tab filtering */
.gmHeaderCommandHidden{display:none !important;}
.gmHeaderSearchOverlay.is-open{display:flex !important;}
.gmHeaderSearchModal.gmHeaderCommandCenter{
  width:min(1420px, calc(100vw - 44px)) !important;
  max-height:min(900px, calc(100dvh - 34px)) !important;
  border-radius:32px !important;
  grid-template-rows:auto auto auto minmax(0,1fr) !important;
}
.gmHeaderCommandTop{padding:18px !important;grid-template-columns:minmax(0,1fr) 60px !important;gap:14px !important;}
.gmHeaderCommandInputWrap{height:72px !important;border-radius:24px !important;padding:0 18px !important;gap:14px !important;}
.gmHeaderCommandInputWrap i{width:44px !important;height:44px !important;border-radius:16px !important;font-size:17px !important;}
.gmHeaderSearchInput{font-size:20px !important;font-weight:900 !important;}
.gmHeaderCommandClose{width:60px !important;height:60px !important;border-radius:20px !important;font-size:24px !important;}
.gmHeaderCommandHero{padding:22px 28px !important;}
.gmHeaderCommandIcon{width:52px !important;height:52px !important;border-radius:18px !important;font-size:17px !important;}
.gmHeaderCommandIntro strong{font-size:18px !important;}
.gmHeaderCommandIntro span span,.gmHeaderCommandIntro div > span{font-size:13.5px !important;line-height:1.45 !important;}
.gmHeaderCommandTrust span{height:34px !important;padding:0 14px !important;font-size:12px !important;}
.gmHeaderCommandQuick{gap:16px !important;padding:22px 28px !important;}
.gmHeaderCommandQuickCard{min-height:96px !important;border-radius:26px !important;padding:18px !important;gap:16px !important;}
.gmHeaderCommandQuickIcon{width:52px !important;height:52px !important;border-radius:18px !important;font-size:18px !important;}
.gmHeaderCommandQuickCard strong{font-size:17px !important;}
.gmHeaderCommandQuickCard small{font-size:13px !important;margin-top:6px !important;}
.gmHeaderCommandBody{padding:26px 28px 30px !important;}
.gmHeaderCommandSectionHead{margin-bottom:18px !important;padding-bottom:16px !important;}
.gmHeaderCommandSectionHead small{font-size:11.5px !important;}
.gmHeaderCommandSectionHead h3{font-size:28px !important;}
.gmHeaderCommandHint{font-size:13.5px !important;}
.gmHeaderCommandGameGrid{gap:16px !important;grid-template-columns:repeat(3,minmax(0,1fr)) !important;}
.gmHeaderCommandGame{min-height:172px !important;border-radius:26px !important;padding:20px !important;gap:14px !important;}
.gmHeaderCommandGameIcon{width:66px !important;height:66px !important;border-radius:21px !important;}
.gmHeaderResultTitle{font-size:18px !important;line-height:1.15 !important;}
.gmHeaderResultMeta{font-size:13px !important;margin-top:6px !important;}
.gmHeaderCommandActions{gap:9px !important;}
.gmHeaderCommandActions .gmHeaderGameAction{height:36px !important;padding:0 14px !important;font-size:12px !important;border-radius:999px !important;}
.gmHeaderCommandDigitalGrid{gap:14px !important;}
.gmHeaderCommandDigitalCard{min-height:104px !important;border-radius:22px !important;padding:18px !important;}
.gmHeaderNoResults{font-size:16px !important;padding:28px !important;}
@media(max-width:1180px){
  .gmHeaderCommandGameGrid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;}
}
@media(max-width:900px){
  .gmHeaderSearchOverlay{padding:0 !important;align-items:stretch !important;justify-content:stretch !important;background:#060a16 !important;}
  .gmHeaderSearchModal.gmHeaderCommandCenter{width:100vw !important;height:100dvh !important;max-height:none !important;border-radius:0 !important;border:0 !important;display:grid !important;grid-template-rows:auto auto auto minmax(0,1fr) !important;background:#060a16 !important;}
  .gmHeaderCommandTop{padding:calc(14px + env(safe-area-inset-top)) 14px 14px !important;grid-template-columns:minmax(0,1fr) 52px !important;gap:10px !important;}
  .gmHeaderCommandInputWrap{height:58px !important;border-radius:18px !important;padding:0 12px !important;}
  .gmHeaderCommandInputWrap i{width:38px !important;height:38px !important;border-radius:14px !important;font-size:15px !important;}
  .gmHeaderSearchInput{font-size:17px !important;}
  .gmHeaderCommandClose{width:52px !important;height:52px !important;border-radius:17px !important;}
  .gmHeaderCommandHero{padding:14px 16px !important;align-items:flex-start !important;}
  .gmHeaderCommandIcon{width:42px !important;height:42px !important;border-radius:15px !important;}
  .gmHeaderCommandIntro strong{font-size:15px !important;}
  .gmHeaderCommandIntro span span,.gmHeaderCommandIntro div > span{font-size:12px !important;}
  .gmHeaderCommandTrust{display:none !important;}
  .gmHeaderCommandQuick{display:flex !important;overflow-x:auto !important;gap:10px !important;padding:14px 16px !important;scrollbar-width:none !important;}
  .gmHeaderCommandQuick::-webkit-scrollbar{display:none !important;}
  .gmHeaderCommandQuickCard{flex:0 0 154px !important;min-height:70px !important;border-radius:19px !important;padding:12px !important;gap:10px !important;}
  .gmHeaderCommandQuickIcon{width:38px !important;height:38px !important;border-radius:14px !important;font-size:14px !important;}
  .gmHeaderCommandQuickCard strong{font-size:14px !important;}
  .gmHeaderCommandQuickCard small{font-size:11px !important;}
  .gmHeaderCommandBody{padding:16px 14px calc(24px + env(safe-area-inset-bottom)) !important;overflow-y:auto !important;}
  .gmHeaderCommandSectionHead{align-items:flex-start !important;margin-bottom:14px !important;}
  .gmHeaderCommandSectionHead small{font-size:10px !important;}
  .gmHeaderCommandSectionHead h3{font-size:22px !important;}
  .gmHeaderCommandHint{display:none !important;}
  .gmHeaderCommandGameGrid{grid-template-columns:1fr !important;gap:12px !important;}
  .gmHeaderCommandGame{min-height:auto !important;border-radius:22px !important;padding:16px !important;gap:12px !important;}
  .gmHeaderCommandGameIcon{width:58px !important;height:58px !important;border-radius:18px !important;}
  .gmHeaderResultTitle{font-size:17px !important;}
  .gmHeaderResultMeta{font-size:12.5px !important;}
  .gmHeaderCommandActions{width:100% !important;margin-left:70px !important;justify-content:flex-start !important;gap:8px !important;}
  .gmHeaderCommandActions .gmHeaderGameAction{height:34px !important;font-size:11.5px !important;padding:0 13px !important;}
}
</style>


<style id="gm-command-center-landing-v6-alias-digital-polish">
body .gmHeaderCommandQuickIcon--digital,
body .gmHeaderCommandQuickIcon--items,
body .gmHeaderCommandQuickIcon--boost,
body .gmHeaderCommandQuickIcon--accounts,
body .gmHeaderCommandQuickIcon{
  background:rgba(96,165,250,.14) !important;
  border-color:rgba(96,165,250,.24) !important;
  color:#93c5fd !important;
}
body .gmHeaderCommandActions .gmHeaderGameAction,
body .gmHeaderCommandActions .gmHeaderGameAction[data-cat="accounts"],
body .gmHeaderCommandActions .gmHeaderGameAction[data-cat="items"]{
  color:#eaf4ff !important;
  background:linear-gradient(135deg, rgba(37,99,235,.96), rgba(14,165,233,.78)) !important;
  border:1px solid rgba(125,211,252,.30) !important;
  box-shadow:0 10px 22px rgba(14,165,233,.12), inset 0 1px 0 rgba(255,255,255,.13) !important;
}
body .gmHeaderCommandQuickCard strong{white-space:nowrap !important;}
body .gmHeaderSearchInput{font-size:21px !important;}
@media(max-width:900px){
  body .gmHeaderSearchInput{font-size:17px !important;}
  body .gmHeaderCommandQuickCard{min-width:178px !important;}
}
</style>


<style id="gm-command-center-compact-dropdown-v7">
/* Compact search dropdown, desktop should feel attached to the header search field */
@media (min-width:901px){
  .gmHeaderSearchOverlay{
    inset:0 !important;
    padding:0 !important;
    align-items:flex-start !important;
    justify-content:flex-start !important;
    background:transparent !important;
    -webkit-backdrop-filter:none !important;
    backdrop-filter:none !important;
    pointer-events:none !important;
  }
  .gmHeaderSearchOverlay.is-open{display:block !important;opacity:1 !important;pointer-events:none !important;}
  .gmHeaderSearchModal.gmHeaderCommandCenter{
    position:fixed !important;
    left:var(--gm-search-left, 50%) !important;
    top:var(--gm-search-top, 108px) !important;
    width:var(--gm-search-width, min(900px, calc(100vw - 32px))) !important;
    max-width:calc(100vw - 32px) !important;
    height:auto !important;
    max-height:min(620px, calc(100dvh - var(--gm-search-top, 108px) - 18px)) !important;
    border-radius:22px !important;
    display:grid !important;
    grid-template-rows:auto auto minmax(0,1fr) !important;
    pointer-events:auto !important;
    background:linear-gradient(180deg, rgba(10,16,34,.985), rgba(5,9,22,.985)) !important;
    border:1px solid rgba(96,165,250,.30) !important;
    box-shadow:0 28px 80px rgba(0,0,0,.58), 0 0 0 1px rgba(255,255,255,.045) inset, 0 0 46px rgba(59,130,246,.12) !important;
    overflow:hidden !important;
  }
  .gmHeaderSearchModal.gmHeaderCommandCenter::before{
    content:"" !important;
    position:absolute !important;
    top:-7px !important;
    left:48px !important;
    width:14px !important;
    height:14px !important;
    background:rgba(10,16,34,.985) !important;
    border-left:1px solid rgba(96,165,250,.30) !important;
    border-top:1px solid rgba(96,165,250,.30) !important;
    transform:rotate(45deg) !important;
  }
  .gmHeaderCommandTop{
    grid-template-columns:minmax(0,1fr) 44px !important;
    gap:10px !important;
    padding:10px !important;
    background:linear-gradient(180deg, rgba(96,165,250,.07), rgba(255,255,255,.018)) !important;
  }
  .gmHeaderCommandInputWrap{
    height:48px !important;
    border-radius:16px !important;
    padding:0 12px !important;
    gap:10px !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.06), 0 0 0 3px rgba(59,130,246,.08) !important;
  }
  .gmHeaderCommandInputWrap i{width:32px !important;height:32px !important;border-radius:12px !important;font-size:13px !important;}
  .gmHeaderSearchInput{font-size:15px !important;font-weight:850 !important;}
  .gmHeaderCommandClose{width:44px !important;height:44px !important;border-radius:14px !important;font-size:18px !important;}
  .gmHeaderCommandHero{display:none !important;}
  .gmHeaderCommandQuick{
    display:flex !important;
    gap:8px !important;
    padding:10px 12px !important;
    border-bottom:1px solid rgba(148,163,184,.10) !important;
    overflow-x:auto !important;
    scrollbar-width:none !important;
  }
  .gmHeaderCommandQuick::-webkit-scrollbar{display:none !important;}
  .gmHeaderCommandQuickCard{
    flex:1 1 0 !important;
    min-width:0 !important;
    min-height:48px !important;
    border-radius:15px !important;
    padding:8px 10px !important;
    gap:9px !important;
    transform:none !important;
  }
  .gmHeaderCommandQuickCard:hover,.gmHeaderCommandQuickCard.is-active{transform:none !important;box-shadow:0 10px 24px rgba(0,0,0,.20) !important;}
  .gmHeaderCommandQuickIcon,
  .gmHeaderCommandQuickIcon--boost,
  .gmHeaderCommandQuickIcon--accounts,
  .gmHeaderCommandQuickIcon--items,
  .gmHeaderCommandQuickIcon--digital{
    width:30px !important;
    height:30px !important;
    border-radius:11px !important;
    font-size:12px !important;
    color:#93c5fd !important;
    background:rgba(96,165,250,.14) !important;
    border-color:rgba(96,165,250,.24) !important;
  }
  .gmHeaderCommandQuickCard strong{font-size:13px !important;white-space:nowrap !important;}
  .gmHeaderCommandQuickCard small{display:none !important;}
  .gmHeaderCommandBody{padding:12px !important;max-height:100% !important;overflow:auto !important;}
  .gmHeaderCommandSection{margin:0 0 14px !important;}
  .gmHeaderCommandSectionHead{margin:0 0 10px !important;padding:0 0 9px !important;}
  .gmHeaderCommandSectionHead small{font-size:9px !important;letter-spacing:.15em !important;}
  .gmHeaderCommandSectionHead h3{font-size:17px !important;letter-spacing:-.02em !important;}
  .gmHeaderCommandHint{font-size:11px !important;}
  .gmHeaderCommandGameGrid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:9px !important;}
  .gmHeaderCommandGame{
    min-height:74px !important;
    border-radius:16px !important;
    padding:10px !important;
    gap:8px !important;
    display:grid !important;
    grid-template-columns:minmax(0,1fr) auto !important;
    align-items:center !important;
  }
  .gmHeaderCommandGame:hover{transform:none !important;}
  .gmHeaderCommandGameMain{gap:10px !important;min-width:0 !important;}
  .gmHeaderCommandGameIcon{width:42px !important;height:42px !important;border-radius:13px !important;}
  .gmHeaderResultTitle{font-size:13px !important;line-height:1.12 !important;}
  .gmHeaderResultMeta{font-size:10.5px !important;margin-top:3px !important;}
  .gmHeaderCommandActions{justify-content:flex-end !important;gap:5px !important;}
  .gmHeaderCommandActions .gmHeaderGameAction,
  .gmHeaderCommandActions .gmHeaderGameAction[data-cat="accounts"],
  .gmHeaderCommandActions .gmHeaderGameAction[data-cat="items"]{
    height:27px !important;
    padding:0 9px !important;
    font-size:10px !important;
    border-radius:999px !important;
    background:linear-gradient(135deg, rgba(37,99,235,.96), rgba(14,165,233,.78)) !important;
    border-color:rgba(125,211,252,.28) !important;
    box-shadow:none !important;
  }
  .gmHeaderCommandDigitalGrid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:9px !important;}
  .gmHeaderCommandDigitalCard{min-height:62px !important;border-radius:15px !important;padding:10px !important;}
  .gmHeaderCommandDigitalCard .gmHeaderResultIcon{width:38px !important;height:38px !important;border-radius:13px !important;}
}

/* Mobile, marketplace item opens the real search modal, with compact tabs */
@media (max-width:900px){
  .sidenav-mob .mob-main-item[data-mobile-marketplace-search="1"] .mob-main-chevron i::before{content:"\\f002" !important;}
  .gmHeaderSearchOverlay{z-index:2147483647 !important;}
  .gmHeaderSearchModal.gmHeaderCommandCenter{grid-template-rows:auto auto minmax(0,1fr) !important;}
  .gmHeaderCommandHero{display:none !important;}
  .gmHeaderCommandQuick{position:sticky !important;top:0 !important;z-index:2 !important;background:#071126 !important;border-bottom:1px solid rgba(255,255,255,.08) !important;}
  .gmHeaderCommandQuickCard{flex:0 0 auto !important;min-width:auto !important;width:auto !important;min-height:44px !important;padding:8px 12px !important;border-radius:999px !important;}
  .gmHeaderCommandQuickIcon{display:none !important;}
  .gmHeaderCommandQuickCard strong{font-size:13px !important;white-space:nowrap !important;}
  .gmHeaderCommandQuickCard small{display:none !important;}
  .gmHeaderCommandSectionHead h3{font-size:20px !important;}
  .gmHeaderCommandActions .gmHeaderGameAction,
  .gmHeaderCommandActions .gmHeaderGameAction[data-cat="accounts"],
  .gmHeaderCommandActions .gmHeaderGameAction[data-cat="items"]{
    background:linear-gradient(135deg, rgba(37,99,235,.96), rgba(14,165,233,.78)) !important;
    border-color:rgba(125,211,252,.30) !important;
  }
}
</style>


<style id="gm-command-center-dropdown-wide-final">
@media (min-width:901px){
  .gmHeaderSearchModal.gmHeaderCommandCenter{
    width:var(--gm-search-width, min(1180px, calc(100vw - 32px))) !important;
    max-width:calc(100vw - 32px) !important;
    max-height:min(620px, calc(100dvh - var(--gm-search-top, 108px) - 18px)) !important;
  }
  .gmHeaderCommandGameGrid{grid-template-columns:repeat(3,minmax(0,1fr)) !important;}
  .gmHeaderCommandGame{min-height:78px !important;}
  .gmHeaderCommandBody{padding:14px 16px 16px !important;}
  .gmHeaderCommandQuick{padding:12px 16px !important;}
}
@media (max-width:900px){
  .sidenav-mob .mob-main-item[data-mobile-marketplace-search="1"]{cursor:pointer !important;}
  .sidenav-mob .mob-main-item[data-mobile-marketplace-search="1"] .mob-main-chevron{
    color:#93c5fd !important;
    background:rgba(37,99,235,.14) !important;
    border-color:rgba(96,165,250,.25) !important;
  }
  .sidenav-mob .mob-main-item[data-mobile-marketplace-search="1"] .mob-main-chevron i::before{content:"\f002" !important;}
}


/* ============================================================
   FINAL BACKGROUND NORMALIZE, removes hard horizontal section cuts
   ============================================================ */
html,
body.landing,
body.landing.gamingmarket{
  background:#030817 !important;
  background-image:
    radial-gradient(1100px 760px at 12% 12%, rgba(37,99,235,.14), transparent 62%),
    radial-gradient(980px 680px at 90% 18%, rgba(56,189,248,.08), transparent 64%),
    linear-gradient(180deg,#030817 0%,#040a18 42%,#030817 100%) !important;
  background-attachment:scroll !important;
}

body.landing.gamingmarket main,
body.landing.gamingmarket .site-main,
body.landing.gamingmarket .page-content,
body.landing.gamingmarket .content,
body.landing.gamingmarket .gamingmarket{
  background:#030817 !important;
  background-image:none !important;
}

body.landing.gamingmarket .gm-section,
body.landing.gamingmarket .gm-marketSection,
body.landing.gamingmarket .gm-supportfaq,
body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild,
body.landing.gamingmarket .gm-reviewsSection,
body.landing.gamingmarket .lbHybridSteps,
body.landing.gamingmarket .lbHybridLive,
body.landing.gamingmarket .lbHybridBoosters,
body.landing.gamingmarket .gm-heroSearch{
  position:relative !important;
  margin-top:-1px !important;
  background:#030817 !important;
  background-image:none !important;
  border-top:0 !important;
  border-bottom:0 !important;
  box-shadow:none !important;
  isolation:auto !important;
}

body.landing.gamingmarket .gm-section::before,
body.landing.gamingmarket .gm-section::after,
body.landing.gamingmarket .gm-marketSection::before,
body.landing.gamingmarket .gm-marketSection::after,
body.landing.gamingmarket .gm-supportfaq::before,
body.landing.gamingmarket .gm-supportfaq::after,
body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild::before,
body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild::after,
body.landing.gamingmarket .gm-reviewsSection::before,
body.landing.gamingmarket .gm-reviewsSection::after,
body.landing.gamingmarket .lbHybridSteps::before,
body.landing.gamingmarket .lbHybridSteps::after,
body.landing.gamingmarket .lbHybridLive::before,
body.landing.gamingmarket .lbHybridLive::after,
body.landing.gamingmarket .lbHybridBoosters::before,
body.landing.gamingmarket .lbHybridBoosters::after,
body.landing.gamingmarket .gm-heroSearch::before,
body.landing.gamingmarket .gm-heroSearch::after{
  content:none !important;
  display:none !important;
  background:none !important;
  box-shadow:none !important;
  border:0 !important;
}

body.landing.gamingmarket .gm-supportfaq,
body.landing.gamingmarket .gm-supportfaq.gm-faqRebuild{
  padding-top:clamp(86px, 8vw, 116px) !important;
  padding-bottom:clamp(86px, 8vw, 116px) !important;
}

body.landing.gamingmarket .gm-marketSection{
  padding-top:clamp(86px, 8vw, 118px) !important;
  padding-bottom:clamp(92px, 8vw, 124px) !important;
}

body.landing.gamingmarket .gm-faqPremium,
body.landing.gamingmarket .gm-faqRebuildPanel,
body.landing.gamingmarket .gm-serviceTile,
body.landing.gamingmarket .lbHybridStep,
body.landing.gamingmarket .lbHybridOrder,
body.landing.gamingmarket .lbHybridBooster,
body.landing.gamingmarket .gm-reviewCard{
  background-color:rgba(8,13,30,.74) !important;
}

@media(max-width:820px){
  html,
  body.landing,
  body.landing.gamingmarket{
    background:#030817 !important;
    background-image:linear-gradient(180deg,#030817 0%,#050b1c 50%,#030817 100%) !important;
    background-attachment:scroll !important;
  }
}

</style>
<script id="gm-command-center-mobile-open-final">
(function(){
  function closeMobileMenu(){
    Array.prototype.slice.call(document.querySelectorAll('.sidenav-mob, .mobile-menu, .mobile-nav, .offcanvas, .navbar-mobile-menu')).forEach(function(el){
      el.classList.remove('active','open','is-open','show');
    });
    Array.prototype.slice.call(document.querySelectorAll('.sidenav-backdrop, .mobile-menu-backdrop, .nav-backdrop, .sidebar-backdrop, .offcanvas-backdrop, .menu-backdrop, .sidenav-overlay, .mobile-menu-overlay, .nav-overlay, .sidebar-overlay, .offcanvas-overlay, .menu-overlay')).forEach(function(el){
      el.classList.remove('active','open','is-open','show');
    });
    document.documentElement.classList.remove('sidenav-open','mobile-menu-open','menu-open');
    document.body.classList.remove('sidenav-open','mobile-menu-open','menu-open','no-scroll');
  }
  function openCommandCenter(){
    var overlay=document.getElementById('gmHeaderSearchOverlay');
    if(!overlay)return;
    closeMobileMenu();
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden','false');
    document.documentElement.classList.add('gmHeaderSearchOpen');
    document.body.classList.add('gmHeaderSearchOpen');
    var input=document.getElementById('gmHeaderSearchInput');
    if(input){setTimeout(function(){try{input.focus({preventScroll:true});}catch(e){input.focus();}},60);}
  }
  window.gmHeaderOpenCommandCenter=openCommandCenter;
  document.addEventListener('click',function(e){
    var item=e.target && e.target.closest ? e.target.closest('[data-mobile-marketplace-search="1"]') : null;
    if(!item)return;
    e.preventDefault();
    e.stopPropagation();
    if(e.stopImmediatePropagation)e.stopImmediatePropagation();
    openCommandCenter();
  },true);
})();
</script>

<style id="landing-order-now-centered-command-center">
@media (min-width:901px){
  .gmHeaderSearchOverlay.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter{
    left:var(--gm-search-left, calc((100vw - min(1180px, calc(100vw - 96px))) / 2)) !important;
    top:var(--gm-search-top, 104px) !important;
    width:var(--gm-search-width, min(1180px, calc(100vw - 96px))) !important;
    max-width:calc(100vw - 96px) !important;
  }
  .gmHeaderSearchOverlay.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter::before{
    display:none !important;
  }
}
</style>


<style id="gm-command-center-visibility-and-hero-fix-final">
/* Hard guard, command center must be invisible until it is really opened */
body:not(.gmHeaderSearchOpen) .gmHeaderSearchOverlay,
html:not(.gmHeaderSearchOpen) .gmHeaderSearchOverlay:not(.is-open),
.gmHeaderSearchOverlay:not(.is-open){
  display:none !important;
  opacity:0 !important;
  visibility:hidden !important;
  pointer-events:none !important;
}
body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open,
html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open{
  visibility:visible !important;
}

/* Hero Order Now opens the command center as a centered modal, not as the header dropdown */
@media (min-width:901px){
  body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open,
  html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    inset:0 !important;
    padding:32px !important;
    background:rgba(3,8,23,.76) !important;
    -webkit-backdrop-filter:blur(12px) !important;
    backdrop-filter:blur(12px) !important;
    pointer-events:auto !important;
  }
  body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter,
  html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter{
    position:relative !important;
    left:auto !important;
    right:auto !important;
    top:auto !important;
    bottom:auto !important;
    margin:0 auto !important;
    width:min(1180px, calc(100vw - 96px)) !important;
    max-width:calc(100vw - 96px) !important;
    max-height:min(680px, calc(100dvh - 96px)) !important;
    transform:none !important;
  }
}

@media (max-width:900px){
  body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open,
  html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open{
    display:block !important;
    opacity:1 !important;
    visibility:visible !important;
    pointer-events:auto !important;
  }
}
</style>
<script id="gm-command-center-open-close-final">
(function(){
  function getOverlay(){return document.getElementById('gmHeaderSearchOverlay');}
  function getInput(){return document.getElementById('gmHeaderSearchInput');}
  function markOpen(overlay){
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden','false');
    document.documentElement.classList.add('gmHeaderSearchOpen');
    document.body.classList.add('gmHeaderSearchOpen');
  }
  function markClosed(overlay){
    if(!overlay)return;
    overlay.classList.remove('is-open','is-hero-open');
    overlay.setAttribute('aria-hidden','true');
    document.documentElement.classList.remove('gmHeaderSearchOpen');
    document.body.classList.remove('gmHeaderSearchOpen');
  }
  function focusInput(){
    var input=getInput();
    if(input){setTimeout(function(){try{input.focus({preventScroll:true});}catch(e){input.focus();}},40);}
  }
  function openHeroCommandCenter(){
    var overlay=getOverlay();
    if(!overlay)return;
    overlay.classList.add('is-hero-open');
    overlay.style.removeProperty('--gm-search-left');
    overlay.style.removeProperty('--gm-search-top');
    overlay.style.removeProperty('--gm-search-width');
    markOpen(overlay);
    focusInput();
  }
  function closeCommandCenter(){markClosed(getOverlay());}

  window.gmOpenNavSearch=openHeroCommandCenter;
  window.gmCloseHeaderSearch=closeCommandCenter;

  document.addEventListener('DOMContentLoaded',function(){
    var overlay=getOverlay();
    if(overlay && !document.documentElement.classList.contains('gmHeaderSearchOpen') && !document.body.classList.contains('gmHeaderSearchOpen')){
      markClosed(overlay);
    }
  });

  document.addEventListener('click',function(e){
    var overlay=getOverlay();
    if(!overlay)return;
    var closeBtn=e.target && e.target.closest ? e.target.closest('.gmHeaderSearchClose,.gmHeaderCommandClose,[data-gm-header-search-close]') : null;
    if(closeBtn){e.preventDefault();closeCommandCenter();return;}
    if(overlay.classList.contains('is-open') && e.target === overlay){closeCommandCenter();}
  },true);

  document.addEventListener('keydown',function(e){
    if(e.key === 'Escape') closeCommandCenter();
  });
})();
</script>


<style id="lb-command-game-name-actions-fix">
/* Command Center game cards: full game names and service buttons below the title */
.gmHeaderCommandGame .gmHeaderCommandGameMain{
  align-items:flex-start !important;
}
.gmHeaderCommandGame .gmHeaderCommandGameText{
  flex:1 1 auto !important;
  min-width:0 !important;
  max-width:100% !important;
}
.gmHeaderCommandGame .gmHeaderResultTitle{
  display:block !important;
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
  word-break:normal !important;
  overflow-wrap:anywhere !important;
  line-height:1.18 !important;
}
.gmHeaderCommandGame .gmHeaderResultMeta{
  display:none !important;
}
.gmHeaderCommandGame .gmHeaderCommandActions,
.gmHeaderCommandGame .gmHeaderGameActions{
  width:auto !important;
  margin-left:78px !important;
  margin-top:-28px !important;
  justify-content:flex-start !important;
  align-self:stretch !important;
  display:flex !important;
  flex-wrap:wrap !important;
}
@media(max-width:900px){
  .gmHeaderCommandGame .gmHeaderCommandActions,
  .gmHeaderCommandGame .gmHeaderGameActions{
    margin-left:68px !important;
    margin-top:-20px !important;
  }
}
@media(max-width:560px){
  .gmHeaderCommandGame .gmHeaderCommandActions,
  .gmHeaderCommandGame .gmHeaderGameActions{
    margin-left:52px !important;
    margin-top:-16px !important;
  }
  .gmHeaderCommandGame .gmHeaderResultTitle{
    font-size:13.5px !important;
  }
}
</style>


<style id="lb-command-game-card-layout-final-fix">
/* Final Command Center game card layout, prevents vertical letter wrapping */
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,
.gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,
.gmHeaderResult.gmHeaderCommandGame{
  display:flex !important;
  flex-direction:column !important;
  align-items:stretch !important;
  justify-content:flex-start !important;
  gap:8px !important;
  min-height:92px !important;
  padding:12px 14px !important;
  overflow:hidden !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameMain,
.gmHeaderCommandCenter .gmHeaderCommandGameMain,
.gmHeaderCommandGameMain{
  width:100% !important;
  max-width:100% !important;
  display:grid !important;
  grid-template-columns:46px minmax(0,1fr) !important;
  align-items:center !important;
  gap:12px !important;
  min-width:0 !important;
  color:#fff !important;
  text-decoration:none !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameIcon,
.gmHeaderCommandCenter .gmHeaderCommandGameIcon,
.gmHeaderCommandGameIcon{
  width:46px !important;
  height:46px !important;
  border-radius:14px !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameText,
.gmHeaderCommandCenter .gmHeaderCommandGameText,
.gmHeaderCommandGameText{
  width:100% !important;
  min-width:0 !important;
  max-width:100% !important;
  display:block !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,
.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,
.gmHeaderCommandGame .gmHeaderResultTitle{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
  word-break:normal !important;
  overflow-wrap:normal !important;
  hyphens:none !important;
  line-height:1.18 !important;
  font-size:14px !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultMeta,
.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultMeta,
.gmHeaderCommandGame .gmHeaderResultMeta{
  display:none !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,
.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,
.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,
.gmHeaderCommandGame .gmHeaderCommandActions,
.gmHeaderCommandGame .gmHeaderGameActions{
  width:auto !important;
  max-width:calc(100% - 58px) !important;
  margin:2px 0 0 58px !important;
  align-self:flex-start !important;
  display:flex !important;
  align-items:center !important;
  justify-content:flex-start !important;
  flex-wrap:wrap !important;
  gap:7px !important;
}
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandActions .gmHeaderGameAction,
.gmHeaderCommandCenter .gmHeaderCommandActions .gmHeaderGameAction,
.gmHeaderCommandActions .gmHeaderGameAction{
  height:28px !important;
  padding:0 10px !important;
  font-size:10.5px !important;
  line-height:1 !important;
}
@media(min-width:901px){
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameGrid,
  .gmHeaderCommandCenter .gmHeaderCommandGameGrid{
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    gap:10px !important;
  }
}
@media(max-width:900px){
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,
  .gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,
  .gmHeaderResult.gmHeaderCommandGame{
    min-height:96px !important;
    padding:13px !important;
  }
}
@media(max-width:520px){
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameMain,
  .gmHeaderCommandCenter .gmHeaderCommandGameMain,
  .gmHeaderCommandGameMain{
    grid-template-columns:42px minmax(0,1fr) !important;
    gap:10px !important;
  }
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameIcon,
  .gmHeaderCommandCenter .gmHeaderCommandGameIcon,
  .gmHeaderCommandGameIcon{
    width:42px !important;
    height:42px !important;
  }
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,
  .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,
  .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,
  .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,
  .gmHeaderCommandGame .gmHeaderCommandActions,
  .gmHeaderCommandGame .gmHeaderGameActions{
    margin-left:52px !important;
    max-width:calc(100% - 52px) !important;
  }
}
</style>


<style id="gm-command-filter-landing-guard">
.gmHeaderCommandHidden{display:none !important;}
.gmHeaderCommandActionHidden{display:none !important;}
</style>
<script id="gm-command-filter-landing-guard-script">
(function(){
  function run(){
    if(typeof window.gmHeaderFilterCommandCenter === 'function') window.gmHeaderFilterCommandCenter();
  }
  document.addEventListener('input', function(e){if(e.target && e.target.id === 'gmHeaderSearchInput') run();}, true);
  document.addEventListener('click', function(e){if(e.target && e.target.closest && e.target.closest('[data-gm-command-tab]')) setTimeout(run, 0);}, true);
})();
</script>

<style id="gm-command-filter-logic-final-landing-guard">
/* Landing guard, keeps Command Center filter hiding stronger than older landing overrides */
.gmHeaderCommandHidden,
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandHidden,
.gmHeaderCommandCenter .gmHeaderCommandHidden{
  display:none !important;
}
.gmHeaderCommandActionHidden,
.gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandActionHidden,
.gmHeaderCommandCenter .gmHeaderCommandActionHidden{
  display:none !important;
}
.gmHeaderCommandQuickCard.is-active{
  border-color:rgba(96,165,250,.60) !important;
  background:linear-gradient(180deg, rgba(37,99,235,.24), rgba(14,165,233,.08)) !important;
}
</style>



<!-- lb-mobile-instant style/script moved to the <head> styles section above
     (single source of truth), so it's active from first paint instead of only
     once the parser reaches this point deep in the body. -->

<style id="lb-particles-visible-final">
/* Final sichtbare Partikel, liegen sichtbar über dem dunklen Background, aber klicken nichts weg */
html body.landing,
html body.landing.gamingmarket{
  overflow-x:hidden !important;
}
html body.landing .gm-bg,
html body.landing .gm-gridlines{
  display:block !important;
  pointer-events:none !important;
}
html body.landing #gmStars{
  display:block !important;
  position:fixed !important;
  inset:0 !important;
  width:100vw !important;
  height:100vh !important;
  z-index:6 !important;
  pointer-events:none !important;
  overflow:hidden !important;
  opacity:1 !important;
  visibility:visible !important;
  contain:paint !important;
  mix-blend-mode:screen !important;
}
html body.landing .gm-star{
  display:block !important;
  position:absolute !important;
  left:var(--x,50vw) !important;
  top:var(--y,50vh) !important;
  width:var(--s,3px) !important;
  height:var(--s,3px) !important;
  min-width:2px !important;
  min-height:2px !important;
  border-radius:999px !important;
  background:rgba(255,255,255,.98) !important;
  box-shadow:0 0 10px rgba(255,255,255,.72), 0 0 24px rgba(96,165,250,.55), 0 0 42px rgba(99,102,241,.28) !important;
  opacity:var(--o,.78) !important;
  visibility:visible !important;
  transform:translate3d(0,0,0) !important;
  animation:lbVisibleParticleFloat var(--d,22s) linear infinite !important;
  animation-delay:var(--delay,0s) !important;
  will-change:transform,opacity !important;
}
html body.landing .gm-star::after{
  content:"" !important;
  position:absolute !important;
  left:50% !important;
  top:50% !important;
  width:calc(var(--s,3px) * 5) !important;
  height:1px !important;
  transform:translate(-50%,-50%) rotate(-34deg) !important;
  background:linear-gradient(90deg, rgba(96,165,250,0), rgba(255,255,255,.34), rgba(96,165,250,0)) !important;
  border-radius:999px !important;
}
html body.landing header,
html body.landing .site-header,
html body.landing .navbar,
html body.landing .gm-ctaBand,
html body.landing .lbCtaNew,
html body.landing .lbCta5,
html body.landing #gmCta,
html body.landing footer,
html body.landing .footer-main,
html body.landing .footer-games,
html body.landing .footer-bottom,
html body.landing [class*="footer"]{
  position:relative !important;
  z-index:20 !important;
  isolation:isolate !important;
}
@keyframes lbVisibleParticleFloat{
  0%{
    transform:translate3d(0,0,0) scale(.85) !important;
    opacity:.10 !important;
  }
  14%{
    opacity:var(--o,.78) !important;
  }
  72%{
    opacity:var(--o,.78) !important;
  }
  100%{
    transform:translate3d(var(--tx,-28vw), var(--ty,22vh), 0) scale(1.16) !important;
    opacity:.06 !important;
  }
}
@media(max-width:820px){
  html body.landing #gmStars{
    z-index:5 !important;
    opacity:.7 !important;
  }
}
html body.landing.lbParticlesHeroBlocked #gmStars{
  opacity:0 !important;
  visibility:hidden !important;
}
html body.landing.gamingmarket .lb-visualHero__sparks{
  display:none !important;
  opacity:0 !important;
  visibility:hidden !important;
}
@media(prefers-reduced-motion:reduce){
  html body.landing .gm-star{
    animation:none !important;
  }
}
</style>

<script id="lb-particles-visible-final-js">
(function(){
  // Decorative starfield is desktop-only. On mobile #gmStars is display:none
  // anyway, so building 14 animated DOM nodes + scroll/resize listeners there is
  // pure wasted work during the critical mobile load — skip it entirely.
  if(window.matchMedia && window.matchMedia('(max-width:820px)').matches) return;

  function boot(){
    var body = document.body;
    if(!body || !body.classList.contains('landing')) return;

    var holder = document.getElementById('gmStars');
    if(!holder){
      holder = document.createElement('div');
      holder.id = 'gmStars';
      holder.setAttribute('aria-hidden','true');
      body.insertBefore(holder, body.firstChild);
    }

    holder.removeAttribute('style');
    holder.innerHTML = '';
    holder.setAttribute('data-lb-particles-ready','visible');

    var isMobile = window.matchMedia && window.matchMedia('(max-width:820px)').matches;
    var count = isMobile ? 14 : 32;
    var frag = document.createDocumentFragment();

    for(var i = 0; i < count; i++){
      var p = document.createElement('span');
      p.className = 'gm-star';

      var size = (Math.random() * 2.8 + 1.8).toFixed(2) + 'px';
      var x = (Math.random() * 116 - 8).toFixed(2) + 'vw';
      var y = (Math.random() * 112 - 6).toFixed(2) + 'vh';
      var duration = (Math.random() * 18 + 18).toFixed(2) + 's';
      var delay = (-Math.random() * 34).toFixed(2) + 's';
      var opacity = (Math.random() * .42 + .48).toFixed(2);
      var tx = (Math.random() * 34 - 30).toFixed(2) + 'vw';
      var ty = (Math.random() * 26 + 8).toFixed(2) + 'vh';

      p.style.setProperty('--s', size);
      p.style.setProperty('--x', x);
      p.style.setProperty('--y', y);
      p.style.setProperty('--d', duration);
      p.style.setProperty('--delay', delay);
      p.style.setProperty('--o', opacity);
      p.style.setProperty('--tx', tx);
      p.style.setProperty('--ty', ty);
      frag.appendChild(p);
    }

    holder.appendChild(frag);
  }

  function syncHeroParticleBlock(){
    var body = document.body;
    if(!body) return;

    var hero = document.querySelector('.lb-visualHero, #top.gm-heroV4, section#top');
    if(!hero){
      body.classList.remove('lbParticlesHeroBlocked');
      return;
    }

    var rect = hero.getBoundingClientRect();
    var vh = window.innerHeight || document.documentElement.clientHeight || 0;
    var heroVisible = rect.bottom > 0 && rect.top < vh;

    body.classList.toggle('lbParticlesHeroBlocked', heroVisible);
  }

  var started = false;
  function start(){
    if(started) return;
    started = true;
    boot();
    syncHeroParticleBlock();
  }

  function scheduleStart(){
    if('requestIdleCallback' in window) requestIdleCallback(start, {timeout:1800});
    else setTimeout(start, 700);
  }

  if(document.readyState === 'complete') scheduleStart();
  else window.addEventListener('load', scheduleStart, {once:true});
  window.addEventListener('scroll', syncHeroParticleBlock, {passive:true});
  window.addEventListener('resize', syncHeroParticleBlock, {passive:true});
})();
</script>

<script id="lb-landing-mobile-menu-failsafe">
(function(){
  // Self-contained mobile menu opener for the landing page.
  //
  // The menu's "show" class is toggled by jQuery in website/js/main.js, which is
  // loaded at the very bottom of the page. On the landing page that chain is
  // unreliable (heavy inline scripts run before it), so we OWN opening here by
  // adding the same "show" class the CSS keys off. We deliberately DO NOT force
  // any inline positioning styles: the close button's own handler in header.php
  // runs in the capture phase with stopImmediatePropagation(), so it removes the
  // "show" class but never gets a chance to clear inline styles — leaving any we
  // set stuck on screen. Class-only toggling lets both open and close work.
  var nav = document.querySelector('.sidenav-mob');
  if(!nav) return;

  function openMenu(){
    nav.classList.add('show');
    document.body.classList.add('overlay','sidenav-open');
  }
  function closeMenu(){
    nav.classList.remove('show');
    document.body.classList.remove('overlay','sidenav-open');
  }

  document.addEventListener('click', function(e){
    var t = e.target;
    if(!t || !t.closest) return;
    if(t.closest('.menu-icon')){ e.preventDefault(); openMenu(); return; }
    if(t.closest('.sidenav-mob .close-sidenav, .sidenav-mob .mob-close, [data-sidenav-close]')){ closeMenu(); return; }
    // Tap outside the open panel (on the dimmed page) closes it.
    if(nav.classList.contains('show') && !t.closest('.sidenav-mob') && !t.closest('.menu-icon')){ closeMenu(); }
  }, false);
})();
</script>

<style id="lb-flat-buttons-no-gradient">
/* Single source of truth: every primary/accent button on the landing page uses one
   flat brand color instead of a gradient. Loaded last so it beats the older
   per-section gradient rules above. */
html body.landing .lbCta5BtnPrimary,
html body.landing .lbGames2MoreBtn,
html body.landing .gm-btnPrimary,
html body.landing .gmFaq-btnPrimary,
html body.landing .gmFaq-topBtnPrimary,
html body.landing .gm-lootPerks a,
html body.landing .lbFaq4 .gmFaq-tab.is-active,
html body.landing .gmFaq-tab.is-active,
html body.landing .gmFaq-tab:hover,
html body.landing .gm-helpTab.is-active,
html body.landing .gm-helpTab:hover,
html body.landing .lbCtaNewBtnPrimary,
html body.landing .gm-heroV4ActionPrimary,
html body.landing .lb-visualHero__btnPrimary{
  background:#6366F1 !important;
  background-image:none !important;
  border-color:#7477f5 !important;
  color:#fff !important;
}

html body.landing .lbCta5BtnPrimary:hover,
html body.landing .lbGames2MoreBtn:hover,
html body.landing .gm-btnPrimary:hover,
html body.landing .gmFaq-btnPrimary:hover,
html body.landing .gmFaq-topBtnPrimary:hover,
html body.landing .gm-lootPerks a:hover,
html body.landing .lbCtaNewBtnPrimary:hover,
html body.landing .gm-heroV4ActionPrimary:hover,
html body.landing .lb-visualHero__btnPrimary:hover{
  background:#5558e8 !important;
  background-image:none !important;
}

/* Ghost/secondary buttons: flat translucent, no gradient either. */
html body.landing .lbCta5BtnGhost,
html body.landing .gmFaq-btnGhost,
html body.landing .gmFaq-topBtnGhost,
html body.landing .gm-btnGhost,
html body.landing .gm-heroV4ActionGhost,
html body.landing .lb-visualHero__btnGhost,
html body.landing .gmFaq-tab,
html body.landing .gm-helpTab{
  background-image:none !important;
}
</style>
