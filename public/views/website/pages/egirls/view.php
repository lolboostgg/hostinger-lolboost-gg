<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'egirls-view']) ?>

<style>
/* ── reuse boosters-view header exactly ── */
.egirls-view header {
  height: clamp(220px, 35vh, 420px);
  background: url('<?= ASSET_URL ?>/website/images/boosters/view-header-bg.webp') center/cover no-repeat;
  display: flex; align-items: center; justify-content: start;
  position: relative; overflow: hidden;
}
.egirls-view header::after {
  content:''; position:absolute; inset:0;
  background:linear-gradient(120deg,rgba(13,1,24,.7) 0%,rgba(45,10,78,.4) 60%,transparent 100%);
}
.egirls-view header .content {
  position:relative; z-index:1;
  max-width: 42.917vw; margin-left: 4.167vw;
}
.egirls-view header h1 {
  font-size: 5.5vw; line-height: 1;
  margin-bottom: 1.2vw;
  text-transform: uppercase;
  font-family: 'superchargestraight', sans-serif;
  background:linear-gradient(135deg,#fff 0%,#e879f9 60%,#f472b6 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
}
.egirls-view header p {
  font-size: 1.042vw; line-height: 1.875vw; color:rgba(255,255,255,.65);
}

/* ── main content wrap — no outer card, tabs sit directly below the hero ── */
.egirls-view .main-content {
  margin: 2vw 4.167vw 4vw;
  padding: 0;
  position: relative;
}
.egirls-view .main-content .cover {
  height: 17vw; overflow:hidden;
  margin: -4.167vw -4.167vw 0 -4.167vw;
  border-radius: 1.5vw 1.5vw 0 0;
  background: linear-gradient(135deg,#1a0530 0%,#2d0a4e 50%,#1a0530 100%);
  background-size: cover; background-position: top center;
  position:relative;
}
.egirls-view .main-content .cover::after {
  content:''; position:absolute; inset:0;
  background: linear-gradient(180deg,transparent 40%,rgba(10,10,24,.8) 100%),
              radial-gradient(ellipse at 70% 50%,rgba(168,85,247,.25) 0%,transparent 65%);
}
/* animated particles in cover */
.egirls-view .main-content .cover::before {
  content:''; position:absolute; inset:0; z-index:1;
  background: url('<?= ASSET_URL ?>/website/images/boosters/view-header-bg.webp') center/cover no-repeat;
  opacity:.12;
}

.egirls-view .main-content .avatar {
  width: 12.5vw; height: 12.5vw;
  border-radius: 50%; overflow:hidden;
  border: .3vw solid rgba(168,85,247,.6);
  box-shadow: 0 0 2.5vw rgba(168,85,247,.4), 0 0 5vw rgba(236,72,153,.1);
  position: absolute; top: 5.5vw; left: 4vw;
  animation: eg-glow 3s ease-in-out infinite;
}
@keyframes eg-glow {
  0%,100%{box-shadow:0 0 2.5vw rgba(168,85,247,.4),0 0 5vw rgba(236,72,153,.1);}
  50%{box-shadow:0 0 4vw rgba(168,85,247,.65),0 0 8vw rgba(236,72,153,.2);}
}
.egirls-view .main-content .avatar img { width:100%; height:100%; object-fit:cover; }
.egirls-view .main-content .avatar-ph {
  width:12.5vw; height:12.5vw; border-radius:50%;
  background:linear-gradient(135deg,rgba(168,85,247,.3),rgba(236,72,153,.2));
  border:.3vw solid rgba(168,85,247,.5);
  position:absolute; top:5.5vw; left:4vw;
  display:flex; align-items:center; justify-content:center; font-size:4vw;
  box-shadow:0 0 2.5vw rgba(168,85,247,.4);
}
.egirls-view .online-dot {
  position:absolute; bottom:.4vw; right:.4vw; z-index:5;
  width:1.3vw; height:1.3vw; border-radius:50%; background:#22c55e;
  border:.2vw solid #0a0a18;
  animation:eg-pulse 2s ease-in-out infinite;
}
@keyframes eg-pulse {
  0%,100%{box-shadow:0 0 .7vw rgba(34,197,94,.7);}
  50%{box-shadow:0 0 1.6vw rgba(34,197,94,1),0 0 2.8vw rgba(34,197,94,.25);}
}

.egirls-view .main-content .details { margin-top: 8.5vw; }

/* top row: name + chips + button */
.egirls-view .main-content .details .top {
  display:flex; align-items:flex-start; justify-content:space-between; gap:2vw;
  padding-bottom: 1.8vw;
  border-bottom: .104vw solid rgba(114,110,142,.12);
}
.egirls-view .details .top .info h5 {
  font-size: 2.8vw; font-weight:700;
  display:flex; align-items:center; gap:.5vw; margin-bottom:.4vw;
  color:#fff;
}
.egirls-view .details .top .info h5 .verify-icon { color:#a855f7; font-size:.75em; }
.egirls-view .details .top .info h6 {
  font-size:1.1vw; color:rgba(255,255,255,.5); font-weight:400; margin-bottom:.8vw;
  display:flex; align-items:center; gap:.6vw;
}
.egirls-view .details .top .info .eg-chips { display:flex; flex-wrap:wrap; gap:.4vw; }
.egirls-view .eg-chip {
  display:inline-flex; align-items:center; gap:6px;
  font-size:clamp(11px,.8vw,13px); line-height:1; font-weight:800;
  text-transform:uppercase; letter-spacing:.04em;
  padding:7px 12px; border-radius:999px; white-space:nowrap;
  background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.78);
}
.egirls-view .eg-chip img { width:15px; height:15px; object-fit:contain; flex-shrink:0; }
.egirls-view .eg-chip__flag { width:16px; height:11px; object-fit:cover; border-radius:2px; flex-shrink:0; }
.egirls-view .eg-chip.voice { background:rgba(34,197,94,.1); border-color:rgba(34,197,94,.28); color:#22c55e; }
.egirls-view .eg-chip.online { background:rgba(34,197,94,.13); border-color:rgba(34,197,94,.35); color:#22c55e; }
.egirls-view .eg-chip.lol { background:rgba(200,170,60,.1); border-color:rgba(200,170,60,.28); color:#c8aa3c; }
.egirls-view .eg-chip.val { background:rgba(255,70,85,.1); border-color:rgba(255,70,85,.28); color:#ff6b77; }
.egirls-view .eg-chip.tft { background:rgba(100,180,255,.1); border-color:rgba(100,180,255,.28); color:#64b4ff; }
.egirls-view .eg-chip.tz { background:rgba(168,85,247,.07); border-color:rgba(168,85,247,.17); color:rgba(255,255,255,.5); }

/* Book button — lives in the hero actions row now */
.egirls-view .btn-book {
  display:inline-flex; align-items:center; gap:8px; flex-shrink:0;
  background:linear-gradient(135deg,#ec4899,#a855f7);
  color:#fff; border:none; border-radius:999px;
  padding:12px 22px; font-size:clamp(13px,.85vw,15px); font-weight:900;
  text-transform:uppercase; letter-spacing:.04em; line-height:1;
  cursor:pointer; text-decoration:none;
  box-shadow:0 10px 26px rgba(168,85,247,.4);
  transition:transform .18s ease, box-shadow .2s ease, opacity .15s ease;
}
.egirls-view .btn-book:hover {
  opacity:1; color:#fff;
  transform:translateY(-1px);
  box-shadow:0 12px 34px rgba(168,85,247,.55);
}

/* ── NAV TABS ── */
.egirls-view .nav-tabs {
  display:flex; gap:0; border-bottom:.104vw solid rgba(114,110,142,.12);
  margin-bottom:2vw; overflow-x:auto; scrollbar-width:none;
}
.egirls-view .nav-tabs::-webkit-scrollbar { display:none; }
.egirls-view .nav-tabs a {
  display:flex; align-items:center; gap:.4vw;
  font-size:1.05vw; font-weight:700; color:rgba(255,255,255,.45);
  padding:.9vw 1.4vw; border-bottom:.18vw solid transparent;
  text-decoration:none; white-space:nowrap;
  transition:color .15s, border-color .15s;
}
.egirls-view .nav-tabs a:hover { color:rgba(255,255,255,.75); }
.egirls-view .nav-tabs a.active { color:#a855f7; border-bottom-color:#a855f7; }
.egirls-view .nav-tabs a i { font-size:1vw; }

/* ── TAB CONTENT LAYOUT ── */
.egirls-view .tab-layout {
  display:grid; grid-template-columns:1fr 20vw; gap:2.5vw; align-items:start;
}
.egirls-view .tab-pane { display:none; }
.egirls-view .tab-pane.active { display:block; }

/* Section label */
.egirls-view .eg-section { margin-bottom:2vw; }
.egirls-view .eg-section-label {
  font-size:.88vw; font-weight:900; text-transform:uppercase; letter-spacing:.14em;
  color:#a855f7; margin-bottom:.85vw;
  display:flex; align-items:center; gap:.5vw;
}
.egirls-view .eg-section-label::before {
  content:''; width:.2vw; height:.85vw; border-radius:999px; flex-shrink:0;
  background:linear-gradient(180deg,#a855f7,#ec4899);
}
.egirls-view .eg-section-label::after { content:''; flex:1; height:1px; background:linear-gradient(90deg,rgba(168,85,247,.2),transparent); }

/* About */
.egirls-view .eg-bio { color:rgba(255,255,255,.68); font-size:1.2vw; line-height:1.85; }

/* Stat cards row */
.egirls-view .eg-stats-row { display:flex; gap:1vw; margin-bottom:2vw; flex-wrap:wrap; }
.egirls-view .eg-stat-card {
  flex:1; min-width:10vw;
  background:rgba(168,85,247,.07); border:.052vw solid rgba(168,85,247,.15);
  border-radius:.75vw; padding:1vw 1.2vw;
  display:flex; align-items:center; gap:.75vw;
}
.egirls-view .eg-stat-card .icon {
  width:2.4vw; height:2.4vw; border-radius:.5vw;
  background:rgba(168,85,247,.15); border:.052vw solid rgba(168,85,247,.25);
  display:flex; align-items:center; justify-content:center; font-size:1.2vw; flex-shrink:0;
  color:#c084fc;
}
.egirls-view .eg-stat-card .val { font-size:2vw; font-weight:900; color:#fff; line-height:1; }
.egirls-view .eg-stat-card .lbl { font-size:.78vw; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.06em; margin-top:.18vw; }

/* Service cards */
.egirls-view .eg-svc {
  background:rgba(255,255,255,.03); border:.052vw solid rgba(168,85,247,.1);
  border-radius:.85vw; padding:.95vw 1.25vw; margin-bottom:.55vw;
  display:flex; justify-content:space-between; align-items:center; gap:1.2vw;
  transition:border-color .18s,background .18s,box-shadow .18s; cursor:pointer;
}
.egirls-view .eg-svc:hover { border-color:rgba(168,85,247,.32); background:rgba(168,85,247,.05); }
.egirls-view .eg-svc.selected {
  border-color:rgba(168,85,247,.6); background:rgba(168,85,247,.08);
  box-shadow:0 0 0 .052vw rgba(168,85,247,.18) inset, 0 .35vw 1.6vw rgba(168,85,247,.1);
}
.egirls-view .eg-svc-l { flex:1; min-width:0; display:flex; align-items:center; gap:.85vw; }
.egirls-view .eg-svc-icon {
  width:2.6vw; height:2.6vw; border-radius:.52vw; flex-shrink:0;
  background:rgba(168,85,247,.1); border:.052vw solid rgba(168,85,247,.18);
  display:flex; align-items:center; justify-content:center; font-size:1.05vw;
}
.egirls-view .eg-svc-icon img { width:1.4em; height:1.4em; object-fit:contain; }
.egirls-view .eg-svc-badge {
  font-size:.72vw; font-weight:900; text-transform:uppercase; letter-spacing:.07em;
  padding:.15vw .5vw; border-radius:999px;
  background:rgba(168,85,247,.14); color:#c084fc; border:.052vw solid rgba(168,85,247,.25);
  display:inline-block; margin-bottom:.22vw;
}
.egirls-view .eg-svc-title { font-size:1.1vw; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.egirls-view .eg-svc-meta { font-size:.78vw; color:rgba(255,255,255,.32); margin-top:.1vw; }
.eg-filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: .4vw;
  margin-bottom: 1.2vw;
  padding-bottom: .85vw;
  border-bottom: .052vw solid rgba(168,85,247,.1);
}
.eg-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: .35vw;
  padding: .35vw .9vw;
  border-radius: 999px;
  border: .052vw solid rgba(168,85,247,.2);
  background: rgba(168,85,247,.06);
  color: rgba(255,255,255,.5);
  font-size: .82vw;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s;
  text-transform: uppercase;
  letter-spacing: .05em;
}
.eg-filter-btn:hover {
  border-color: rgba(168,85,247,.45);
  background: rgba(168,85,247,.12);
  color: rgba(255,255,255,.8);
}
.eg-filter-btn.active {
  border-color: #a855f7;
  background: rgba(168,85,247,.2);
  color: #e9d5ff;
}
.eg-filter-icon {
  width: .95em;
  height: .95em;
  object-fit: contain;
}
.eg-filter-count {
  background: rgba(168,85,247,.25);
  color: #c084fc;
  font-size: .7em;
  font-weight: 900;
  padding: .1em .45em;
  border-radius: 999px;
  margin-left: .15em;
  line-height: 1.4;
}
.eg-cat-section.hidden { display: none; }
@media(max-width:900px){
  .eg-filter-btn { font-size:11px; padding:5px 11px; gap:5px; }
  .eg-filter-bar { gap:6px; margin-bottom:16px; padding-bottom:14px; }
}
.egirls-view .eg-svc-voice-badge {
  display:inline-flex; align-items:center; gap:.3vw;
  font-size:.72vw; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
  padding:.2vw .55vw; border-radius:999px; margin-top:.3vw;
}
.egirls-view .eg-svc-voice-yes {
  background:rgba(34,197,94,.13); border:.052vw solid rgba(34,197,94,.4); color:#22c55e;
}
.egirls-view .eg-svc-voice-no {
  background:rgba(255,255,255,.05); border:.052vw solid rgba(255,255,255,.12); color:rgba(255,255,255,.35);
}
@media(max-width:900px){
  .eg-filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: .4vw;
  margin-bottom: 1.2vw;
  padding-bottom: .85vw;
  border-bottom: .052vw solid rgba(168,85,247,.1);
}
.eg-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: .35vw;
  padding: .35vw .9vw;
  border-radius: 999px;
  border: .052vw solid rgba(168,85,247,.2);
  background: rgba(168,85,247,.06);
  color: rgba(255,255,255,.5);
  font-size: .82vw;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s;
  text-transform: uppercase;
  letter-spacing: .05em;
}
.eg-filter-btn:hover {
  border-color: rgba(168,85,247,.45);
  background: rgba(168,85,247,.12);
  color: rgba(255,255,255,.8);
}
.eg-filter-btn.active {
  border-color: #a855f7;
  background: rgba(168,85,247,.2);
  color: #e9d5ff;
}
.eg-filter-icon {
  width: .95em;
  height: .95em;
  object-fit: contain;
}
.eg-filter-count {
  background: rgba(168,85,247,.25);
  color: #c084fc;
  font-size: .7em;
  font-weight: 900;
  padding: .1em .45em;
  border-radius: 999px;
  margin-left: .15em;
  line-height: 1.4;
}
.eg-cat-section.hidden { display: none; }
@media(max-width:900px){
  .eg-filter-btn { font-size:11px; padding:5px 11px; gap:5px; }
  .eg-filter-bar { gap:6px; margin-bottom:16px; padding-bottom:14px; }
}
.egirls-view .eg-svc-voice-badge { font-size:11px; padding:3px 8px; gap:4px; margin-top:4px; }
}
.egirls-view .eg-svc-r { text-align:right; flex-shrink:0; }
.egirls-view .eg-svc-price { font-size:1.75vw; font-weight:900; color:#fff; }
.egirls-view .eg-svc-select {
  display:block; margin-top:.25vw;
  background:linear-gradient(135deg,#a855f7,#ec4899);
  color:#fff; border:none; border-radius:.38vw;
  padding:.32vw .85vw; font-size:.75vw; font-weight:800; cursor:pointer; transition:opacity .15s;
}
.egirls-view .eg-svc-select:hover { opacity:.88; }

/* Availability */
.egirls-view .eg-avail { display:flex; flex-wrap:wrap; gap:.4vw; }
.egirls-view .eg-avail-slot {
  display:inline-flex; align-items:center; gap:.28vw; padding:.28vw .7vw;
  border-radius:999px; background:rgba(168,85,247,.07); border:.052vw solid rgba(168,85,247,.15);
  font-size:.88vw; color:rgba(255,255,255,.65); transition:background .15s,border-color .15s;
}
.egirls-view .eg-avail-slot:hover { background:rgba(168,85,247,.14); border-color:rgba(168,85,247,.3); }

/* Reviews */
.egirls-view .eg-review {
  background:rgba(255,255,255,.025); border:.052vw solid rgba(168,85,247,.08);
  border-radius:.7vw; padding:.85vw 1.05vw; margin-bottom:.5vw;
}
.egirls-view .eg-review .rev-h { display:flex; justify-content:space-between; align-items:center; margin-bottom:.28vw; }
.egirls-view .eg-review .rev-user { display:flex; align-items:center; gap:.4vw; }
.egirls-view .eg-review .rev-av { width:1.56vw; height:1.56vw; border-radius:50%; object-fit:cover; }
.egirls-view .eg-review .author { font-size:.95vw; font-weight:700; color:rgba(255,255,255,.8); }
.egirls-view .eg-review .stars { color:#f59e0b; font-size:.95vw; }
.egirls-view .eg-review .text { font-size:.88vw; color:rgba(255,255,255,.52); margin-top:.18vw; }
.egirls-view .eg-review .date { font-size:.75vw; color:rgba(255,255,255,.28); margin-top:.15vw; }

/* ── STICKY SUMMARY ── */
.egirls-view .eg-sum-wrap {
  position:sticky; top:5vw;
  max-height:calc(100vh - 6vw); overflow-y:auto; scrollbar-width:none;
}
.egirls-view .eg-sum-wrap::-webkit-scrollbar { display:none; }
/* Neutral dark surface with a hint of purple — keeps the panel sitting on the
   page background instead of glowing as a separate purple block. */
.egirls-view .eg-summary {
  background:
    radial-gradient(420px 220px at 0% 0%, rgba(168,85,247,.08), transparent 62%),
    linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.012));
  background-color:#0c1020;
  border:1px solid rgba(255,255,255,.08); border-radius:18px; overflow:hidden;
  box-shadow:0 18px 44px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.05);
  transition:border-color .25s, box-shadow .25s;
}
.egirls-view .eg-summary:hover { border-color:rgba(236,72,153,.22); box-shadow:0 22px 54px rgba(0,0,0,.48), inset 0 1px 0 rgba(255,255,255,.06); }
.egirls-view .eg-sum-bar { height:2px; background:linear-gradient(90deg,rgba(236,72,153,.55),rgba(168,85,247,.55),transparent); }
.egirls-view .eg-sum-body { padding:1.4vw; }
.egirls-view .eg-sum-prev {
  display:flex; align-items:center; gap:.7vw;
  padding-bottom:.9vw; margin-bottom:.9vw;
  border-bottom:1px solid rgba(255,255,255,.07);
}
.egirls-view .eg-sum-prev img,
.egirls-view .eg-sum-prev .eg-sum-ph {
  width:2.7vw; height:2.7vw; border-radius:50%; object-fit:cover;
  border:.12vw solid rgba(168,85,247,.42); flex-shrink:0;
}
.egirls-view .eg-sum-ph { background:rgba(168,85,247,.18); display:flex; align-items:center; justify-content:center; font-size:.9vw; }
.egirls-view .eg-sum-pname { font-size:1.05vw; font-weight:800; color:#fff; }
.egirls-view .eg-sum-prole { font-size:.78vw; color:rgba(255,255,255,.4); }
.egirls-view .eg-sum-title { font-size:1.35vw; font-weight:900; color:#fff; margin-bottom:.18vw; }
.egirls-view .eg-sum-sub { font-size:.88vw; color:rgba(255,255,255,.4); margin-bottom:.9vw; }
.egirls-view .eg-sum-label { font-size:.72vw; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:rgba(255,255,255,.35); display:block; margin-bottom:.28vw; }
.egirls-view .eg-sum-select,
.egirls-view .eg-sum-textarea {
  width:100%; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10);
  color:#fff; border-radius:12px; padding:.68vw .85vw; font-size:.95vw; margin-bottom:.75vw;
  font-family:inherit; resize:vertical;
  transition:border-color .2s;
}
.egirls-view .eg-sum-select:focus, .egirls-view .eg-sum-textarea:focus { border-color:rgba(236,72,153,.42); box-shadow:0 0 0 3px rgba(236,72,153,.10); outline:none; }
.egirls-view .eg-sum-select option { background:#130420; }
/* ── Custom Service Picker ── */
.eg-picker { position:relative; margin-bottom:.75vw; user-select:none; }
.eg-picker-btn {
  width:100%; display:flex; align-items:center; justify-content:space-between;
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10);
  border-radius:12px; padding:.65vw .85vw; cursor:pointer;
  transition:border-color .2s, background .2s; gap:.5vw;
}
.eg-picker-btn:hover { border-color:rgba(236,72,153,.34); background:rgba(255,255,255,.06); }
.eg-picker-btn .pname { font-size:.92vw; font-weight:700; color:#fff; flex:1; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.eg-picker-btn .pprice { font-size:.85vw; font-weight:900; color:#f472b6; white-space:nowrap; background:rgba(236,72,153,.12); border:.052vw solid rgba(236,72,153,.25); padding:.1vw .45vw; border-radius:999px; }
.eg-picker-btn .parrow { color:#a855f7; font-size:.75vw; flex-shrink:0; transition:transform .2s; }
.eg-picker.open .parrow { transform:rotate(180deg); }
.eg-picker-list {
  display:none; position:absolute; top:calc(100% + .3vw); left:0; right:0; z-index:9999;
  background:#10131f; border:1px solid rgba(255,255,255,.10);
  border-radius:14px; max-height:15vw; overflow-y:auto;
  box-shadow:0 18px 44px rgba(0,0,0,.55);
}
.eg-picker-list::-webkit-scrollbar { width:.3vw; }
.eg-picker-list::-webkit-scrollbar-track { background:rgba(168,85,247,.05); }
.eg-picker-list::-webkit-scrollbar-thumb { background:rgba(168,85,247,.4); border-radius:999px; }
.eg-picker.open .eg-picker-list { display:block; }
@media(max-width:900px){
  .eg-picker-list { max-height:220px; }
}
.eg-picker-item {
  display:flex; align-items:center; justify-content:space-between; gap:.8vw;
  padding:.7vw 1vw; cursor:pointer; border-bottom:1px solid rgba(255,255,255,.055);
  transition:background .15s;
}
.eg-picker-item:last-child { border:none; }
.eg-picker-item:hover { background:rgba(255,255,255,.06); }
.eg-picker-item.active { background:rgba(236,72,153,.11); }
.eg-picker-item .iname { font-size:.88vw; font-weight:700; color:#fff; }
.eg-picker-item .imeta { font-size:.65vw; color:rgba(255,255,255,.4); margin-top:.1vw; }
.eg-picker-item .iprice { font-size:1vw; font-weight:900; color:#f472b6; flex-shrink:0; }
@media(max-width:900px){
  .eg-picker-btn .pname{font-size:.9rem;}
  .eg-picker-btn .pprice{font-size:.82rem;}
  .eg-picker-btn .parrow{font-size:.75rem;}
  .eg-picker-item .iname{font-size:.86rem;}
  .eg-picker-item .imeta{font-size:.64rem;}
  .eg-picker-item .iprice{font-size:.95rem;}
}
.egirls-view .eg-sum-div { height:1px; background:rgba(255,255,255,.07); margin:.75vw 0; }
.egirls-view .eg-sum-total {
  display:flex; justify-content:space-between; align-items:center;
  background:rgba(255,255,255,.04); border-radius:12px; padding:.65vw .85vw; margin-bottom:.9vw;
  border:1px solid rgba(255,255,255,.08);
}
.egirls-view .eg-sum-total span { font-size:.92vw; color:rgba(255,255,255,.45); }
.egirls-view .eg-sum-total strong { font-size:1.75vw; font-weight:900; color:#fff; }
.egirls-view .eg-book-btn {
  width:100%; padding:.82vw;
  background:linear-gradient(135deg,#a855f7,#ec4899);
  color:#fff; border:none; border-radius:.62vw;
  font-weight:900; font-size:1.1vw; cursor:pointer;
  transition:opacity .15s,transform .1s,box-shadow .2s;
  animation:eg-btn-pulse 2.5s ease-in-out infinite;
}
@keyframes eg-btn-pulse {
  0%,100%{box-shadow:0 0 0 0 rgba(168,85,247,.32);}
  50%{box-shadow:0 0 0 .32vw rgba(168,85,247,.0);}
}
.egirls-view .eg-book-btn:hover { opacity:.9; transform:translateY(-.1vw); animation:none; box-shadow:0 .45vw 1.8vw rgba(168,85,247,.45); }
.egirls-view .eg-voice-note { display:flex; align-items:center; justify-content:center; gap:.28vw; font-size:.78vw; color:#22c55e; margin-top:.6vw; }

/* ── Voice Chat Toggle ── */
.egirls-view .eg-voice-toggle {
  margin-top:.75vw; border-radius:.6vw;
  border:.052vw solid rgba(34,197,94,.3);
  background:rgba(34,197,94,.07);
  padding:.65vw .85vw;
  transition:border-color .2s, background .2s;
}
.egirls-view .eg-voice-toggle.off {
  border-color:rgba(255,255,255,.1);
  background:rgba(255,255,255,.03);
}
.egirls-view .eg-voice-toggle-inner { display:flex; align-items:center; gap:.6vw; }
.egirls-view .eg-voice-icon {
  width:2vw; height:2vw; border-radius:.4vw; flex-shrink:0;
  background:rgba(34,197,94,.15); border:.052vw solid rgba(34,197,94,.3);
  display:flex; align-items:center; justify-content:center;
  font-size:.85vw; color:#22c55e; transition:background .2s, border-color .2s, color .2s;
}
.egirls-view .eg-voice-toggle.off .eg-voice-icon { background:rgba(255,255,255,.07); border-color:rgba(255,255,255,.12); color:rgba(255,255,255,.3); }
.egirls-view .eg-voice-label { font-size:.82vw; font-weight:700; color:#fff; display:block; line-height:1.2; }
.egirls-view .eg-voice-sub { font-size:.68vw; color:#22c55e; transition:color .2s; }
.egirls-view .eg-voice-toggle.off .eg-voice-sub { color:rgba(255,255,255,.3); }
.egirls-view .eg-voice-text { flex:1; min-width:0; }
.egirls-view .eg-voice-switch {
  width:2.4vw; height:1.25vw; border-radius:999px;
  background:#22c55e; cursor:pointer; position:relative;
  transition:background .2s; flex-shrink:0;
  box-shadow:0 0 .6vw rgba(34,197,94,.4);
}
.egirls-view .eg-voice-toggle.off .eg-voice-switch { background:rgba(255,255,255,.15); box-shadow:none; }
.egirls-view .eg-voice-knob {
  position:absolute; top:.12vw; left:.12vw;
  width:1vw; height:1vw; border-radius:50%;
  background:#fff; transition:left .2s;
}
.egirls-view .eg-voice-toggle.off .eg-voice-knob { left:calc(100% - 1.12vw); }


/* mobile readability helpers */
.egirls-view .eg-role-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:linear-gradient(135deg,rgba(168,85,247,.24),rgba(236,72,153,.18));
  border:1px solid rgba(168,85,247,.35);
  padding:7px 13px;border-radius:999px;
  font-size:clamp(11px,.8vw,13px); line-height:1; font-weight:800;
  text-transform:uppercase; letter-spacing:.04em;
  color:#fff;
}
.egirls-view .eg-role-badge i{color:#ec4899;font-size:.8em;}
.egirls-view .eg-tab-count{
  background:rgba(168,85,247,.25);color:#c084fc;font-size:.68vw;font-weight:900;
  padding:.15vw .52vw;border-radius:999px;margin-left:.2vw;line-height:1;
}
.egirls-view .eg-voice-val{font-size:.95vw;color:#22c55e;}
.egirls-view .eg-tab-link{
  display:inline-flex !important;
  align-items:center !important;
  gap:8px !important;
  margin-top:10px !important;
  padding:10px 14px !important;
  border-radius:999px !important;
  border:1px solid rgba(168,85,247,.22) !important;
  background:linear-gradient(135deg, rgba(168,85,247,.10), rgba(236,72,153,.06)) !important;
  color:#d8b4fe !important;
  text-decoration:none !important;
  font-size:.88vw !important;
  font-weight:800 !important;
  line-height:1 !important;
  transition:all .18s ease !important;
}
.egirls-view .eg-tab-link::after{
  content:"→";
  font-size:.95em;
  transition:transform .18s ease;
}
.egirls-view .eg-tab-link:hover{
  color:#fff !important;
  border-color:rgba(168,85,247,.38) !important;
  background:linear-gradient(135deg, rgba(168,85,247,.16), rgba(236,72,153,.10)) !important;
  box-shadow:0 8px 20px rgba(168,85,247,.14);
}
.egirls-view .eg-tab-link:hover::after{
  transform:translateX(2px);
}

/* stronger mobile layout polish */
@media(max-width:900px){
  .egirls-view .main-content{padding:18px !important;}
  .egirls-view .main-content .details{margin-top:58px;}
  .egirls-view .details .top .info h6{
    row-gap:8px !important;
    column-gap:6px !important;
    align-items:center !important;
  }
  .egirls-view .eg-role-badge{
    font-size:12px !important;
    padding:6px 10px !important;
    gap:6px !important;
    border-radius:999px !important;
    display:inline-flex !important;
    align-items:center !important;
  }
  .egirls-view .eg-tab-count{
    font-size:11px !important;
    padding:4px 8px !important;
    margin-left:6px !important;
  }
  .egirls-view .eg-voice-val{
    font-size:18px !important;
    line-height:1.15 !important;
    font-weight:900 !important;
  }
  .egirls-view .eg-tab-link{
    font-size:14px !important;
    padding:10px 14px !important;
    margin-top:12px !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:7px !important;
  }

  .egirls-view .eg-chips{
    gap:6px !important;
    margin-top:4px !important;
  }
  .egirls-view .eg-chip{
    font-size:11px !important;
    padding:5px 9px !important;
    gap:5px !important;
  }

  .egirls-view .nav-tabs{
    margin-bottom:20px !important;
  }
  .egirls-view .nav-tabs a{
    font-size:14px !important;
    padding:11px 12px !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:6px !important;
  }

  .egirls-view .eg-stats-row{
    gap:10px !important;
    margin-bottom:20px !important;
  }
  .egirls-view .eg-stat-card{
    min-width:calc(50% - 5px) !important;
    padding:14px 12px !important;
    gap:10px !important;
    border-radius:12px !important;
  }
  .egirls-view .eg-stat-card .val{
    font-size:22px !important;
    line-height:1.1 !important;
  }
  .egirls-view .eg-stat-card .lbl{
    font-size:11px !important;
    margin-top:4px !important;
    line-height:1.25 !important;
  }

  .egirls-view .eg-svc{
    padding:14px !important;
    border-radius:12px !important;
    margin-bottom:10px !important;
    align-items:flex-start !important;
  }
  .egirls-view .eg-svc-l{
    gap:10px !important;
  }
  .egirls-view .eg-svc-badge{
    font-size:10px !important;
    padding:3px 8px !important;
    margin-bottom:5px !important;
  }
  .egirls-view .eg-svc-title{
    font-size:15px !important;
    line-height:1.25 !important;
    white-space:normal !important;
  }
  .egirls-view .eg-svc-meta{
    font-size:12px !important;
    line-height:1.45 !important;
    margin-top:3px !important;
  }
  .egirls-view .eg-svc-price{
    font-size:24px !important;
    line-height:1 !important;
  }
  .egirls-view .eg-svc-select{
    font-size:12px !important;
    padding:7px 12px !important;
    border-radius:8px !important;
    margin-top:6px !important;
  }

  .egirls-view .eg-section{
    margin-bottom:22px !important;
  }
  .egirls-view .eg-section-label{
    margin-bottom:12px !important;
  }

  .egirls-view .eg-summary{
    border-radius:16px !important;
  }
  .egirls-view .eg-sum-body{
    padding:16px !important;
  }
  .egirls-view .eg-sum-label{
    font-size:11px !important;
    margin-bottom:7px !important;
  }
  .egirls-view .eg-sum-sub{
    font-size:13px !important;
    line-height:1.5 !important;
  }
  .egirls-view .eg-sum-total{
    margin-bottom:14px !important;
  }
  .egirls-view .eg-sum-total strong{
    font-size:30px !important;
  }
  .egirls-view .eg-book-btn{
    min-height:50px !important;
    font-size:16px !important;
    border-radius:12px !important;
  }
  .egirls-view .eg-voice-toggle{
    margin-top:12px !important;
    padding:12px !important;
    border-radius:10px !important;
  }
}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  /* Header */
  .egirls-view header { min-height:180px; }
  .egirls-view header h1 { font-size:2.4rem; line-height:1.1; }
  .egirls-view header p { font-size:.85rem; line-height:1.55; }

  /* Main card */
  .egirls-view .main-content { margin:14px 12px; border-radius:16px; padding:16px; }
  .egirls-view .main-content .cover { height:120px; border-radius:10px 10px 0 0; }
  .egirls-view .eg-heart { font-size:1rem; }
  .egirls-view .main-content .avatar { width:72px; height:72px; top:82px; left:16px; border-width:2px; }
  .egirls-view .main-content .avatar-ph { width:72px; height:72px; top:82px; left:16px; font-size:2rem; border-width:2px; }
  .egirls-view .online-dot { width:12px; height:12px; border-width:2px; bottom:2px; right:2px; }
  .egirls-view .main-content .details { margin-top:54px; }

  /* Name & info */
  .egirls-view .details .top { flex-direction:column; align-items:flex-start; gap:12px; padding-bottom:14px; }
  .egirls-view .details .top .info h5 { font-size:1.8rem !important; gap:6px; }
  .egirls-view .details .top .info h6 { font-size:.82rem; gap:6px; flex-wrap:wrap; margin-bottom:8px; }
  .egirls-view .eg-chip { font-size:.68rem; padding:3px 9px; gap:3px; }
  .egirls-view .eg-chip img { width:13px; height:13px; }
  .egirls-view .details .top .info h6 img { width:16px !important; height:11px !important; }
  .egirls-view .details .top .info h6 span span { font-size:.62rem !important; }
  .egirls-view .details .top .btn-book { width:100%; text-align:center; justify-content:center; font-size:.92rem; padding:11px 18px; border-radius:9px; }

  /* Nav tabs — scrollable, bigger touch targets */
  .egirls-view .nav-tabs {
    gap:0; overflow-x:auto; -webkit-overflow-scrolling:touch;
    scrollbar-width:none; padding-bottom:2px;
    border-bottom:1px solid rgba(168,85,247,.15);
    margin-bottom:18px;
  }
  .egirls-view .nav-tabs a { font-size:.8rem; padding:10px 12px; white-space:nowrap; }
  .egirls-view .nav-tabs a i { font-size:.78rem; }
  .egirls-view .eg-soon-badge { font-size:.6rem; padding:1px 5px; }

  /* Tab layout - single column */
  .egirls-view .tab-layout { grid-template-columns:1fr; gap:0; }

  /* Stats */
  .egirls-view .eg-stats-row { gap:8px; flex-wrap:wrap; }
  .egirls-view .eg-stat-card { flex:1; min-width:calc(50% - 4px); border-radius:10px; padding:11px 13px; gap:10px; }
  .egirls-view .eg-stat-card .icon { width:30px; height:30px; font-size:.85rem; border-radius:6px; }
  .egirls-view .eg-stat-card .val { font-size:1.25rem; }
  .egirls-view .eg-stat-card .lbl { font-size:.6rem; }

  /* Section labels */
  .egirls-view .eg-section { margin-bottom:18px; }
  .egirls-view .eg-section-label { font-size:.72rem; margin-bottom:10px; }
  .egirls-view .eg-section-label::before { width:3px; height:12px; }
  .egirls-view .eg-bio { font-size:.9rem; line-height:1.75; }

  /* Service cards */
  .egirls-view .eg-svc { border-radius:10px; padding:12px 14px; gap:10px; margin-bottom:8px; }
  .egirls-view .eg-svc-icon { width:36px; height:36px; font-size:1rem; border-radius:7px; }
  .egirls-view .eg-svc-badge { font-size:.6rem; padding:2px 7px; margin-bottom:3px; }
  .egirls-view .eg-svc-title { font-size:.92rem; }
  .egirls-view .eg-svc-meta { font-size:.65rem; }
  .egirls-view .eg-svc-price { font-size:1.1rem; }
  .egirls-view .eg-svc-select { font-size:.7rem; padding:5px 12px; border-radius:6px; margin-top:4px; }

  /* Availability */
  .egirls-view .eg-schedule-row { padding:10px 12px; gap:12px; border-radius:9px; }
  .egirls-view .eg-sched-day { min-width:80px; gap:6px; }
  .egirls-view .eg-sched-dot { width:7px; height:7px; }
  .egirls-view .eg-sched-dname { font-size:.83rem; }
  .egirls-view .eg-sched-today-badge { font-size:.56rem; padding:1px 6px; }
  .egirls-view .eg-sched-time { font-size:.7rem; padding:2px 8px; }
  .egirls-view .eg-sched-off { font-size:.7rem; }

  /* Reviews */
  .egirls-view .eg-review { border-radius:9px; padding:11px 12px; margin-bottom:7px; }
  .egirls-view .eg-review .author, .egirls-view .eg-review .stars { font-size:.8rem; }
  .egirls-view .eg-review .text { font-size:.75rem; }
  .egirls-view .eg-review .rev-av { width:22px; height:22px; }

  /* Summary — stacks below content */
  .egirls-view .eg-sum-wrap { position:static; max-height:none; overflow:visible; margin-top:20px; }
  .egirls-view .eg-summary { border-radius:14px; }
  .egirls-view .eg-sum-bar { height:3px; }
  .egirls-view .eg-sum-body { padding:16px; }
  .egirls-view .eg-sum-prev img, .egirls-view .eg-sum-ph { width:36px; height:36px; }
  .egirls-view .eg-sum-pname { font-size:.88rem; }
  .egirls-view .eg-sum-prole { font-size:.62rem; }
  .egirls-view .eg-sum-title { font-size:1.05rem; }
  .egirls-view .eg-sum-sub { font-size:.75rem; margin-bottom:12px; }
  .egirls-view .eg-sum-label { font-size:.65rem; }

  /* Picker */
  .eg-picker-btn .pname { font-size:.88rem; }
  .eg-picker-btn .pprice { font-size:.82rem; padding:2px 8px; }
  .eg-picker-btn .parrow { font-size:.75rem; }
  .eg-picker-item .iname { font-size:.86rem; }
  .eg-picker-item .imeta { font-size:.64rem; }
  .eg-picker-item .iprice { font-size:.92rem; }
  .eg-picker-btn { padding:10px 12px; border-radius:8px; }
  .eg-picker-list { border-radius:8px; }
  .eg-picker-item { padding:10px 13px; }

  .egirls-view .eg-sum-textarea { font-size:.85rem; padding:9px 11px; border-radius:8px; margin-bottom:10px; }
  .egirls-view .eg-sum-div { margin:10px 0; }
  .egirls-view .eg-sum-total { padding:10px 12px; border-radius:8px; margin-bottom:12px; }
  .egirls-view .eg-sum-total span { font-size:.8rem; }
  .egirls-view .eg-sum-total strong { font-size:1.3rem; }
  .egirls-view .eg-book-btn { font-size:.96rem; padding:13px; border-radius:10px; }
  .egirls-view .eg-voice-note { font-size:.7rem; margin-top:8px; }
  .egirls-view .eg-voice-toggle { padding:10px 12px; border-radius:8px; margin-top:10px; }
  .egirls-view .eg-voice-icon { width:28px; height:28px; font-size:.85rem; border-radius:6px; }
  .egirls-view .eg-voice-label { font-size:.78rem; }
  .egirls-view .eg-voice-sub { font-size:.65rem; }
  .egirls-view .eg-voice-switch { width:36px; height:20px; }
  .egirls-view .eg-voice-knob { width:16px; height:16px; top:2px; left:2px; }
  .egirls-view .eg-voice-toggle.off .eg-voice-knob { left:calc(100% - 18px); }

  /* Coming soon tooltip - show below on mobile */
  .egirls-view .eg-tab-soon::after { bottom:auto; top:calc(100% + 4px); font-size:.65rem; }
}

/* ══ GG-GIRL VIBE EXTRAS ══ */
/* Floating hearts animation in cover */
@keyframes eg-float-up {
  0%   { opacity:0; transform:translateY(0) scale(.8) rotate(-8deg); }
  15%  { opacity:1; }
  85%  { opacity:.7; }
  100% { opacity:0; transform:translateY(-5vw) scale(1.15) rotate(8deg); }
}
.egirls-view .eg-heart {
  position:absolute; bottom:1vw; z-index:2; font-size:1.5vw; pointer-events:none;
  animation:eg-float-up 4s ease-in-out infinite;
}
.egirls-view .eg-heart:nth-child(1){left:8%; animation-delay:0s; font-size:1.3vw; bottom:.5vw;}
.egirls-view .eg-heart:nth-child(2){left:22%; animation-delay:.7s; font-size:1.9vw; bottom:.8vw;}
.egirls-view .eg-heart:nth-child(3){left:38%; animation-delay:1.5s; font-size:1.1vw; bottom:.3vw;}
.egirls-view .eg-heart:nth-child(4){left:55%; animation-delay:2.2s; font-size:2vw; bottom:.6vw;}
.egirls-view .eg-heart:nth-child(5){left:70%; animation-delay:.3s; font-size:1.4vw; bottom:.9vw;}
.egirls-view .eg-heart:nth-child(6){right:10%; animation-delay:1.1s; font-size:1.7vw; bottom:.4vw;}

/* Pink glow on cover */
.egirls-view .main-content .cover {
  background:linear-gradient(135deg,#1a0530 0%,#3d0a6e 40%,#2d0a4e 70%,#1a0830 100%) !important;
}
/* Dynamic cover image */
.egirls-view .main-content .cover.has-cover {
  background: none !important;
}
.egirls-view .main-content .cover.has-cover img.cover-img {
  position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0;
}


/* Nav tab active pink underline gradient */
.egirls-view .nav-tabs a.active {
  color:#f472b6;
  border-bottom-color:#f472b6;
  background:linear-gradient(180deg,transparent,rgba(244,114,182,.05));
}


/* Summary bar pink */
.egirls-view .eg-sum-bar {
  background:linear-gradient(90deg,rgba(236,72,153,.55),rgba(168,85,247,.55),transparent) !important;
}

/* Book btn gradient reversed for contrast */
.egirls-view .eg-book-btn {
  background:linear-gradient(135deg,#ec4899,#a855f7) !important;
}

/* Pink service select btn */
.egirls-view .eg-svc-select {
  background:linear-gradient(135deg,#ec4899,#a855f7) !important;
}

/* Section label pink bar */
.egirls-view .eg-section-label::before {
  background:linear-gradient(180deg,#ec4899,#a855f7) !important;
}

/* Stat card hover glow */
.egirls-view .eg-stat-card:hover {
  border-color:rgba(236,72,153,.3);
  background:rgba(236,72,153,.07);
  transition:all .2s;
}

/* Glitter sparkle on avatar */
.egirls-view .main-content .avatar::after {
  content:'✨';
  position:absolute; top:-.5vw; right:-.3vw;
  font-size:1.2vw;
  animation:eg-sparkle 2s ease-in-out infinite;
}
@keyframes eg-sparkle {
  0%,100%{opacity:.5; transform:scale(.9) rotate(-10deg);}
  50%{opacity:1; transform:scale(1.1) rotate(10deg);}
}

/* Voice note pink */
.egirls-view .eg-voice-note { color:#f472b6 !important; }

/* Picker btn selected pink price */
.egirls-view .eg-svc-picker-btn .price-badge {
  background:linear-gradient(135deg,rgba(236,72,153,.15),rgba(168,85,247,.15));
  border-color:rgba(236,72,153,.35);
  color:#f472b6;
}

/* Avail slot hover pink */
.egirls-view .eg-avail-slot:hover {
  background:rgba(236,72,153,.12) !important;
  border-color:rgba(236,72,153,.3) !important;
  color:rgba(255,255,255,.85) !important;
}

/* ── Weekly Schedule Grid ── */
.egirls-view .eg-schedule-grid { display:flex; flex-direction:column; gap:.4vw; }
.egirls-view .eg-schedule-row {
  display:flex; align-items:center; gap:1.5vw;
  padding:.65vw 1vw; border-radius:.65vw;
  background:rgba(255,255,255,.025); border:.052vw solid rgba(168,85,247,.08);
  transition:background .15s, border-color .15s;
}
.egirls-view .eg-schedule-row:hover { background:rgba(168,85,247,.07); border-color:rgba(168,85,247,.2); }
.egirls-view .eg-schedule-row.today {
  background:linear-gradient(135deg,rgba(236,72,153,.1),rgba(168,85,247,.1));
  border-color:rgba(236,72,153,.35);
  box-shadow:0 0 1.5vw rgba(236,72,153,.1);
}
.egirls-view .eg-schedule-row.off { opacity:.45; }
.egirls-view .eg-sched-day {
  display:flex; align-items:center; gap:.5vw;
  min-width:8vw; flex-shrink:0;
}
.egirls-view .eg-sched-dot {
  width:.6vw; height:.6vw; border-radius:50%; flex-shrink:0;
  background:rgba(255,255,255,.2);
}
.egirls-view .eg-sched-dot.active { background:#a855f7; box-shadow:0 0 .5vw rgba(168,85,247,.7); }
.egirls-view .eg-schedule-row.today .eg-sched-dot.active { background:#ec4899; box-shadow:0 0 .5vw rgba(236,72,153,.7); }
.egirls-view .eg-sched-dname { font-size:.88vw; font-weight:700; color:rgba(255,255,255,.8); }
.egirls-view .eg-schedule-row.today .eg-sched-dname { color:#f472b6; }
.egirls-view .eg-sched-today-badge {
  font-size:.58vw; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  padding:.1vw .45vw; border-radius:999px;
  background:rgba(236,72,153,.2); border:.052vw solid rgba(236,72,153,.4); color:#f472b6;
}
.egirls-view .eg-sched-times { display:flex; flex-wrap:wrap; gap:.35vw; flex:1; }
.egirls-view .eg-sched-time {
  font-size:.75vw; color:rgba(255,255,255,.7);
  background:rgba(168,85,247,.08); border:.052vw solid rgba(168,85,247,.15);
  padding:.2vw .6vw; border-radius:999px;
}
.egirls-view .eg-schedule-row.today .eg-sched-time {
  background:rgba(236,72,153,.1); border-color:rgba(236,72,153,.25); color:rgba(255,255,255,.85);
}
.egirls-view .eg-sched-off { font-size:.75vw; color:rgba(255,255,255,.3); font-style:italic; }
.egirls-view .eg-timezone-note {
  font-size:.78vw; color:rgba(255,255,255,.35); margin-top:.8vw;
  display:flex; align-items:center; gap:.3vw;
}
@media(max-width:900px){
  .egirls-view .eg-schedule-row { padding:10px 13px; gap:14px; border-radius:9px; }
  .egirls-view .eg-sched-day { min-width:90px; gap:7px; }
  .egirls-view .eg-sched-dot { width:8px; height:8px; }
  .egirls-view .eg-sched-dname { font-size:.85rem; }
  .egirls-view .eg-sched-today-badge { font-size:.58rem; }
  .egirls-view .eg-sched-time { font-size:.72rem; padding:2px 8px; }
  .egirls-view .eg-sched-off { font-size:.72rem; }
  .egirls-view .eg-timezone-note { font-size:13px; margin-top:10px; gap:5px; }
}

/* ── Coming soon tab ── */
.egirls-view .eg-tab-soon {
  opacity:.5; cursor:default !important; position:relative;
}
.egirls-view .eg-tab-soon:hover { opacity:.75; color:rgba(255,255,255,.6) !important; }
.egirls-view .eg-soon-badge {
  font-size:.55vw; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  padding:.1vw .42vw; border-radius:999px; margin-left:.3vw;
  background:rgba(245,158,11,.15); border:.052vw solid rgba(245,158,11,.3); color:#f59e0b;
}
.egirls-view .eg-tab-soon::after {
  content: attr(title);
  position:absolute; bottom:calc(100% + .4vw); left:50%; transform:translateX(-50%);
  background:#1a0530; border:.052vw solid rgba(168,85,247,.3);
  color:rgba(255,255,255,.75); font-size:.65vw; padding:.3vw .7vw; border-radius:.4vw;
  white-space:nowrap; pointer-events:none; opacity:0; transition:opacity .2s;
  box-shadow:0 .3vw 1vw rgba(0,0,0,.4);
}
.egirls-view .eg-tab-soon:hover::after { opacity:1; }
@media(max-width:900px){
  .egirls-view .eg-soon-badge { font-size:.58rem; }
  .egirls-view .eg-tab-soon::after { font-size:.68rem; padding:4px 9px; border-radius:6px; }
}

/* ── Guidelines ── */
.egirls-view .eg-guideline-list { display:flex; flex-direction:column; gap:.65vw; }
.egirls-view .eg-guideline-item {
  display:flex; align-items:flex-start; gap:1vw;
  background:rgba(255,255,255,.025); border:.052vw solid rgba(168,85,247,.1);
  border-radius:.75vw; padding:1vw 1.2vw;
  transition:border-color .18s, background .18s;
}
.egirls-view .eg-guideline-item:hover { border-color:rgba(168,85,247,.22); background:rgba(168,85,247,.04); }
.egirls-view .eg-guideline-item.eg-guideline-important { border-color:rgba(168,85,247,.22); background:rgba(168,85,247,.05); }
.egirls-view .eg-guideline-item.eg-guideline-danger { border-color:rgba(239,68,68,.2); background:rgba(239,68,68,.04); }
.egirls-view .eg-guideline-item.eg-guideline-danger:hover { border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07); }
.egirls-view .eg-guideline-icon {
  width:2.4vw; height:2.4vw; border-radius:.5vw; flex-shrink:0;
  background:rgba(168,85,247,.12); border:.052vw solid rgba(168,85,247,.22);
  display:flex; align-items:center; justify-content:center;
  font-size:1.1vw; color:#c084fc;
}
.egirls-view .eg-guideline-item.eg-guideline-important .eg-guideline-icon { background:rgba(168,85,247,.18); border-color:rgba(168,85,247,.35); color:#d8b4fe; }
.egirls-view .eg-guideline-item.eg-guideline-danger .eg-guideline-icon { background:rgba(239,68,68,.12); border-color:rgba(239,68,68,.3); color:#f87171; }
.egirls-view .eg-guideline-body { flex:1; min-width:0; }
.egirls-view .eg-guideline-title { font-size:1vw; font-weight:800; color:#fff; margin-bottom:.28vw; }
.egirls-view .eg-guideline-text { font-size:.88vw; color:rgba(255,255,255,.55); line-height:1.65; }
.egirls-view .eg-guideline-warn {
  display:inline-flex; align-items:center; gap:.3vw;
  margin-top:.5vw; font-size:.75vw; font-weight:800; text-transform:uppercase; letter-spacing:.05em;
  color:#f87171; background:rgba(239,68,68,.1); border:.052vw solid rgba(239,68,68,.28);
  padding:.18vw .6vw; border-radius:999px;
}
@media(max-width:900px){
  .egirls-view .eg-guideline-list { gap:10px; }
  .egirls-view .eg-guideline-item { gap:12px; padding:14px; border-radius:12px; }
  .egirls-view .eg-guideline-icon { width:36px; height:36px; font-size:.9rem; border-radius:8px; }
  .egirls-view .eg-guideline-title { font-size:.95rem; margin-bottom:4px; }
  .egirls-view .eg-guideline-text { font-size:.83rem; }
  .egirls-view .eg-guideline-warn { font-size:.7rem; margin-top:8px; }
}

/* Booking-gate note — px based so it does not shrink to nothing on phones. */
.egirls-view .eg-guidelines-gate-note{
  margin-top:18px; padding:16px 18px;
  border:1px solid rgba(168,85,247,.2); border-radius:16px;
  background:linear-gradient(180deg,rgba(168,85,247,.08),rgba(168,85,247,.03));
}
.egirls-view .eg-gate-title{
  display:flex; align-items:center; gap:9px;
  font-size:16px; font-weight:800; color:#fff; margin-bottom:7px;
}
.egirls-view .eg-gate-title i{ color:#c084fc; font-size:15px; }
.egirls-view .eg-gate-text{ font-size:14px; line-height:1.65; color:rgba(255,255,255,.58); }
@media(max-width:900px){
  .egirls-view .eg-guidelines-gate-note{ margin-top:16px; padding:15px 16px; border-radius:14px; }
  .egirls-view .eg-gate-title{ font-size:15px; }
  .egirls-view .eg-gate-text{ font-size:13.5px; }
}

/* ── FAQ ── */
.egirls-view .eg-faq-list { display:flex; flex-direction:column; gap:.5vw; }
.egirls-view .eg-faq-item {
  background:rgba(255,255,255,.025); border:.052vw solid rgba(168,85,247,.1);
  border-radius:.75vw; overflow:hidden;
  transition:border-color .18s;
}
.egirls-view .eg-faq-item:hover { border-color:rgba(168,85,247,.25); }
.egirls-view .eg-faq-item.open { border-color:rgba(168,85,247,.35); background:rgba(168,85,247,.04); }
.egirls-view .eg-faq-q {
  display:flex; align-items:center; justify-content:space-between; gap:1vw;
  padding:.9vw 1.2vw; cursor:pointer;
  font-size:1vw; font-weight:700; color:rgba(255,255,255,.85);
  user-select:none;
}
.egirls-view .eg-faq-q:hover { color:#fff; }
.egirls-view .eg-faq-arrow { font-size:.75vw; color:#a855f7; flex-shrink:0; transition:transform .25s; }
.egirls-view .eg-faq-item.open .eg-faq-arrow { transform:rotate(180deg); }
.egirls-view .eg-faq-a {
  display:none; padding:0 1.2vw .9vw 1.2vw;
  font-size:.88vw; color:rgba(255,255,255,.55); line-height:1.7;
}
.egirls-view .eg-faq-item.open .eg-faq-a { display:block; }
@media(max-width:900px){
  .egirls-view .eg-faq-list { gap:8px; }
  .egirls-view .eg-faq-item { border-radius:12px; }
  .egirls-view .eg-faq-q { padding:14px 16px; font-size:.92rem; gap:12px; }
  .egirls-view .eg-faq-arrow { font-size:.72rem; }
  .egirls-view .eg-faq-a { padding:0 16px 14px 16px; font-size:.83rem; }
}


/* ── Guidelines Confirmation Modal ── */
.eg-guidelines-modal {
  position: fixed; inset: 0; z-index: 2000;
  display: none; align-items: center; justify-content: center;
  padding: 24px;
  background: rgba(5,4,14,.72);
  backdrop-filter: blur(8px);
}
.eg-guidelines-modal.is-open { display:flex; }
.eg-guidelines-modal__dialog {
  width: min(640px, 100%);
  background: linear-gradient(160deg,#130420 0%,#1e0838 100%);
  border: 1px solid rgba(168,85,247,.26);
  border-radius: 20px;
  box-shadow: 0 28px 80px rgba(0,0,0,.55);
  overflow: hidden;
}
.eg-guidelines-modal__bar { height:3px; background:linear-gradient(90deg,#ec4899,#a855f7,#ec4899); }
.eg-guidelines-modal__body { padding:24px 26px 22px; }
.eg-guidelines-modal__title { font-size:24px; font-weight:900; color:#fff; margin-bottom:8px; }
.eg-guidelines-modal__text { font-size:15px; line-height:1.7; color:rgba(255,255,255,.62); margin-bottom:18px; }
.eg-guidelines-modal__check {
  display:flex; align-items:flex-start; gap:12px; cursor:pointer;
  font-size:15px; line-height:1.65; color:rgba(255,255,255,.82);
  background:rgba(168,85,247,.05); border:1px solid rgba(168,85,247,.14);
  border-radius:14px; padding:14px 16px;
}
.eg-guidelines-modal__check { transition:border-color .16s ease, background .16s ease; }
.eg-guidelines-modal__check:hover { border-color:rgba(236,72,153,.34); background:rgba(168,85,247,.09); }
/* Native box is replaced by .eg-guidelines-modal__box so it matches the pink modal styling. */
.eg-guidelines-modal__check input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
.eg-guidelines-modal__box {
  flex-shrink:0; margin-top:1px; width:22px; height:22px; border-radius:7px;
  display:inline-flex; align-items:center; justify-content:center;
  background:rgba(10,6,20,.72); border:1.5px solid rgba(236,72,153,.38);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
  transition:background .16s ease, border-color .16s ease, box-shadow .16s ease, transform .12s ease;
}
.eg-guidelines-modal__box i { font-size:11px; color:#fff; opacity:0; transform:scale(.6); transition:opacity .14s ease, transform .14s ease; }
.eg-guidelines-modal__check:hover .eg-guidelines-modal__box { border-color:rgba(236,72,153,.62); }
.eg-guidelines-modal__check input:checked + .eg-guidelines-modal__box {
  background:linear-gradient(135deg,#ec4899,#a855f7); border-color:rgba(236,72,153,.85);
  box-shadow:0 6px 18px rgba(168,85,247,.36);
}
.eg-guidelines-modal__check input:checked + .eg-guidelines-modal__box i { opacity:1; transform:scale(1); }
.eg-guidelines-modal__check input:focus-visible + .eg-guidelines-modal__box { outline:2px solid rgba(236,72,153,.6); outline-offset:2px; }
.eg-guidelines-modal__check:active .eg-guidelines-modal__box { transform:scale(.94); }
.eg-guidelines-modal__error { display:none; color:#ff7b93; font-size:14px; margin-top:12px; }
.eg-guidelines-modal__actions { display:flex; justify-content:flex-end; gap:12px; margin-top:18px; }
.eg-guidelines-modal__btn { border:none; border-radius:12px; padding:12px 18px; font-size:15px; font-weight:800; cursor:pointer; }
.eg-guidelines-modal__btn--ghost { background:rgba(255,255,255,.06); color:rgba(255,255,255,.78); border:1px solid rgba(255,255,255,.1); }
.eg-guidelines-modal__btn--primary { background:linear-gradient(135deg,#ec4899,#a855f7); color:#fff; box-shadow:0 10px 28px rgba(168,85,247,.28); }
@media(max-width:900px){
  .eg-guidelines-modal { padding:16px; }
  .eg-guidelines-modal__body { padding:20px 18px 18px; }
  .eg-guidelines-modal__title { font-size:20px; }
  .eg-guidelines-modal__text, .eg-guidelines-modal__check { font-size:14px; }
  .eg-guidelines-modal__actions { flex-direction:column-reverse; }
  .eg-guidelines-modal__btn { width:100%; }
}

/* ═══════════════════════════════════════════════════════
   EG-HERO — full-bleed banner hero. Replaces the generic
   "GG-Girl Profile" text header + the separate cover-in-card;
   the egirl's own cover is now the header, with avatar, name,
   chips and the Book button overlaid directly on it.
═══════════════════════════════════════════════════════ */
.eg-hero{
  position:relative;
  width:100%;
  margin-top:var(--lb-content-top, 132px);
  height:clamp(300px, 30vw, 420px);
  overflow:hidden;
  isolation:isolate;
  background:linear-gradient(135deg,#1a0530 0%,#2d0a4e 50%,#1a0530 100%);
}
.eg-hero__banner-img{
  position:absolute; inset:0; z-index:0;
  width:100%; height:100%; object-fit:cover; display:block;
}
.eg-hero__hearts{ position:absolute; inset:0; z-index:0; display:flex; align-items:center; justify-content:space-evenly; font-size:2.4vw; opacity:.5; }
.eg-hero__scrim{
  position:absolute; inset:0; z-index:1;
  background:
    linear-gradient(180deg, rgba(26,5,48,.05) 0%, rgba(26,5,48,.35) 55%, #0a0a18 100%),
    linear-gradient(90deg, rgba(26,5,48,.7) 0%, transparent 45%);
}
.eg-hero__content{
  position:absolute; left:0; right:0; bottom:0; z-index:2;
  padding:0 4.167vw 2vw;
  display:flex; align-items:flex-end; gap:1.6vw; flex-wrap:wrap;
}
/* The hero avatar sits outside .main-content, so the round/border styling from
   the block above never applied — it rendered as a raw rectangle. */
.eg-hero__content .avatar,
.eg-hero__content .avatar-ph{
  position:relative !important; top:auto !important; left:auto !important;
  width:8vw; height:8vw; min-width:92px; min-height:92px;
  flex-shrink:0;
  border-radius:50%; overflow:hidden;
  border:3px solid rgba(168,85,247,.65);
  box-shadow:0 0 0 6px rgba(168,85,247,.10), 0 14px 40px rgba(0,0,0,.55);
  background:linear-gradient(135deg,rgba(168,85,247,.3),rgba(236,72,153,.2));
}
.eg-hero__content .avatar img{ width:100%; height:100%; object-fit:cover; display:block; }
.eg-hero__content .avatar-ph{ display:flex; align-items:center; justify-content:center; font-size:3vw; }
/* The dot must stay visible even though the avatar clips its children. */
.eg-hero__content .avatar,
.eg-hero__content .avatar-ph{ position:relative; }
.eg-hero__content .online-dot{
  position:absolute; bottom:6%; right:6%; z-index:5;
  width:16px; height:16px; border-radius:50%; background:#22c55e;
  border:3px solid #12081f;
}
.eg-hero__info{ flex:1; min-width:0; padding-bottom:.3vw; }
.eg-hero__name{
  font-size:clamp(28px, 2.6vw, 42px) !important; font-weight:800;
  line-height:1.05 !important; margin:0 0 .5vw !important;
  display:flex; align-items:center; gap:.5vw; flex-wrap:wrap;
  font-family:'superchargestraight',sans-serif; letter-spacing:.02em;
  background:linear-gradient(135deg,#fff 0%,#e879f9 55%,#f472b6 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
  text-shadow:0 12px 34px rgba(0,0,0,.4);
}
.eg-hero__name .verify-icon{ -webkit-text-fill-color:#a855f7; font-size:.7em; }
.eg-hero__chips{ display:flex; align-items:center; gap:.5vw; flex-wrap:wrap; }

/* ── Compact meta row ──
   With every boosting game selectable the chip wall grew to three lines.
   Languages and games collapse into icon-only badges with a hover tooltip. */
.eg-hero__meta{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-top:.5vw; }
.eg-mini-group{
  display:flex; align-items:center; gap:5px;
  padding:5px 9px; border-radius:999px;
  background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10);
}
.eg-mini{
  position:relative; display:inline-flex; align-items:center; justify-content:center;
  width:26px; height:26px; border-radius:50%;
  background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12);
  transition:transform .14s, border-color .14s, background .14s;
  cursor:default;
}
.eg-mini:hover{ transform:translateY(-2px); border-color:rgba(168,85,247,.55); background:rgba(168,85,247,.14); z-index:6; }
.eg-mini img{ width:16px; height:16px; object-fit:contain; }
.eg-mini.flag img{ width:18px; height:12px; object-fit:cover; border-radius:2px; }
.eg-mini.more{ font-size:11px; font-weight:900; color:rgba(255,255,255,.65); letter-spacing:.02em; }
/* Tooltip */
.eg-mini[data-tip]::after{
  content:attr(data-tip);
  position:absolute; bottom:calc(100% + 8px); left:50%; transform:translateX(-50%) translateY(4px);
  background:#161428; border:1px solid rgba(168,85,247,.3); color:#fff;
  font-size:11px; font-weight:700; letter-spacing:.02em; white-space:nowrap;
  padding:5px 9px; border-radius:7px; box-shadow:0 10px 26px rgba(0,0,0,.55);
  opacity:0; pointer-events:none; transition:opacity .14s, transform .14s;
}
.eg-mini[data-tip]:hover::after{ opacity:1; transform:translateX(-50%) translateY(0); }
.eg-mini-label{
  font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.09em;
  color:rgba(255,255,255,.34); padding-right:2px;
}
@media(max-width:900px){
  .eg-hero__meta{ margin-top:10px; }
  .eg-mini{ width:24px; height:24px; }
}
.eg-hero__actions{ display:flex; gap:.6vw; flex-wrap:wrap; align-items:center; flex-shrink:0; padding-bottom:.3vw; }

/* Card now starts directly with the nav tabs — no avatar overlap to clear */
.egirls-view .main-content .details{ margin-top:1.2vw !important; }

/* Mobile: banner stays a plain decorative strip — avatar/name/chips/button
   move below it onto a solid panel instead of overlaying the image. */
@media(max-width:900px){
  .eg-hero{
    height:auto;
    isolation:auto;
    overflow:visible;
  }
  .eg-hero__banner-img{
    position:static;
    height:130px;
    width:100%;
  }
  .eg-hero__hearts{ position:static; height:70px; }
  .eg-hero__scrim{ display:none; }
  .eg-hero__content{
    position:static;
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:12px;
    padding:16px;
    background:#0d1021;
    border-bottom:1px solid rgba(255,255,255,.07);
  }
  .eg-hero__content .avatar,
  .eg-hero__content .avatar-ph{ width:56px; height:56px; }
  .eg-hero__info{ flex:1; min-width:0; padding-bottom:0; }
  .eg-hero__name{ font-size:clamp(20px,6.5vw,26px) !important; }
  .eg-hero__actions{ width:100%; order:3; padding-bottom:0; }
  .eg-hero__actions .btn-book,
  .egirls-view .btn-book{
    width:100%;
    justify-content:center;
    font-size:14px !important;
    padding:12px 18px !important;
    gap:7px !important;
  }
  .egirls-view .main-content .details{ margin-top:16px !important; }
}

/* ---- Main content wrap — no outer card, tabs sit directly below the hero ---- */
.egirls-view .main-content{
  margin:2vw 4.167vw 4vw !important;
  padding:0 !important;
  background:transparent !important;
  border:none !important;
  box-shadow:none !important;
  border-radius:0 !important;
}
@media(max-width:900px){
  .egirls-view .main-content{ margin:1.2vw 16px 32px !important; padding:0 !important; }
}

/* ============================================================
   MOBILE POLISH — hero name, section labels, reviews, voice toggle
   ============================================================ */
@media(max-width:900px){
  /* Long handles like "littlebutterflygirl" overflowed the hero on phones. */
  .eg-hero__name{
    display:block !important;
    width:100% !important;
    min-width:0 !important;
    font-size:clamp(17px,5.6vw,24px) !important;
    line-height:1.12 !important;
    overflow-wrap:anywhere !important;
    word-break:break-word !important;
    hyphens:auto;
  }
  .eg-hero__name .verify-icon{ font-size:.62em !important; margin-left:5px; vertical-align:2px; }
  .eg-hero__info{ min-width:0 !important; overflow:hidden; }

  /* Section labels read as a pill on mobile instead of a thin all-caps line. */
  .egirls-view .eg-section-label{
    display:inline-flex !important;
    align-items:center !important;
    gap:8px !important;
    width:auto !important;
    padding:7px 13px !important;
    margin-bottom:14px !important;
    border-radius:999px !important;
    border:1px solid rgba(236,72,153,.24) !important;
    background:linear-gradient(135deg,rgba(236,72,153,.14),rgba(168,85,247,.08)) !important;
    color:#f9a8d4 !important;
    font-size:11.5px !important;
    letter-spacing:.1em !important;
  }
  .egirls-view .eg-section-label::before{
    width:6px !important; height:6px !important; border-radius:999px !important;
    background:#ec4899 !important;
    box-shadow:0 0 8px rgba(236,72,153,.7);
  }
  .egirls-view .eg-section-label::after{ display:none !important; }

  /* Reviews were barely legible at .8rem — bump the whole card up. */
  .egirls-view .eg-review{
    padding:14px !important;
    border-radius:14px !important;
    margin-bottom:10px !important;
    background:rgba(255,255,255,.035) !important;
    border:1px solid rgba(255,255,255,.08) !important;
  }
  .egirls-view .eg-review .rev-h{ margin-bottom:8px !important; gap:10px; }
  .egirls-view .eg-review .rev-av{ width:30px !important; height:30px !important; }
  .egirls-view .eg-review .author{ font-size:15px !important; font-weight:800 !important; color:#fff !important; }
  .egirls-view .eg-review .stars{ font-size:14px !important; letter-spacing:1px; }
  .egirls-view .eg-review .text{ font-size:14px !important; line-height:1.6 !important; color:rgba(255,255,255,.62) !important; }
  .egirls-view .eg-review .date{ font-size:12px !important; margin-top:8px !important; }

  /* Voice toggle: give it real touch targets and a clearer on/off read. */
  .egirls-view .eg-voice-toggle{
    margin-top:14px !important;
    padding:13px 14px !important;
    border-radius:14px !important;
  }
  .egirls-view .eg-voice-toggle-inner{ gap:12px !important; }
  .egirls-view .eg-voice-icon{
    width:38px !important; height:38px !important;
    border-radius:12px !important; font-size:15px !important;
  }
  .egirls-view .eg-voice-label{ font-size:14.5px !important; font-weight:800 !important; }
  .egirls-view .eg-voice-sub{ font-size:12.5px !important; margin-top:3px; display:block; }
  .egirls-view .eg-voice-switch{
    width:50px !important; height:28px !important; border-radius:999px !important;
  }
  .egirls-view .eg-voice-knob{
    width:22px !important; height:22px !important; top:3px !important; left:3px !important;
    box-shadow:0 2px 6px rgba(0,0,0,.35);
  }
  .egirls-view .eg-voice-toggle.off .eg-voice-knob{ left:calc(100% - 25px) !important; }
}

</style>

<?php
$egCurrency = $_SESSION['currency'] ?? 'EUR';
$egRate = (float)(function_exists('get_exchange_rate') ? get_exchange_rate() : 1);
if ($egRate <= 0) { $egRate = 1; }
$egSymbol = util_format_currency_display($egCurrency);
$egConvert = static function ($priceCents) use ($egCurrency, $egRate) {
    $priceCents = (int)$priceCents;
    if ($egCurrency === 'USD') {
        return (int) round($priceCents * $egRate);
    }
    return $priceCents;
};
$egFmt = static function ($priceCents) use ($egConvert) {
    return number_format($egConvert($priceCents) / 100, 2, '.', '');
};
$egDecodeText = static function ($text) {
    $text = (string)($text ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return $text;
};
?>

<!-- ══ HERO — banner is now the header ══ -->
<div class="eg-hero">
    <?php if (!empty($egirl['cover'])): ?>
        <img class="eg-hero__banner-img" src="<?= htmlspecialchars($egirl['cover']) ?>" alt="">
    <?php else: ?>
        <div class="eg-hero__hearts">
            <span class="eg-heart">💜</span>
            <span class="eg-heart">🩷</span>
            <span class="eg-heart">💜</span>
            <span class="eg-heart">✨</span>
            <span class="eg-heart">🩷</span>
            <span class="eg-heart">💜</span>
        </div>
    <?php endif; ?>
    <div class="eg-hero__scrim"></div>
    <div class="eg-hero__content">

        <!-- Avatar -->
        <?php if ($egirl['icon']): ?>
            <div class="avatar">
                <img src="<?= htmlspecialchars($egirl['icon']) ?>" alt="">
                <?php if ($egirl['is_online']): ?><div class="online-dot"></div><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="avatar-ph">
                👧
                <?php if ($egirl['is_online']): ?><div class="online-dot"></div><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="eg-hero__info">
            <h1 class="eg-hero__name">
                <?= htmlspecialchars($egirl['username']) ?>
                <i class="fa-solid fa-badge-check verify-icon"></i>
            </h1>
            <div class="eg-hero__chips">
                <span class="eg-role-badge">
                    <i class="fa-solid fa-star"></i>GG-Girl
                </span>
                <?php
                $langImgMap=['en'=>'en.png','de'=>'de.png','fr'=>'fr.png','es'=>'es.png','tr'=>'tr.png','pt'=>'pt.png','it'=>'it.png','pl'=>'pl.png','ru'=>'ru.webp','nl'=>'nl.png','sv'=>'sv.png','da'=>'da.webp','no'=>'no.webp','fi'=>'fi.webp','cs'=>'cz.webp','ro'=>'ro.png','hu'=>'hu.webp','uk'=>'uk.png','ar'=>'ar.png','zh'=>'chinese.png','ja'=>'ja.webp','ko'=>'ko.png','el'=>'el.png','hr'=>'hr.png','bg'=>'bg.webp','vn'=>'vn.webp','ph'=>'ph.webp','th'=>'th.webp'];
                $langNameMap=['en'=>'English','de'=>'German','fr'=>'French','es'=>'Spanish','tr'=>'Turkish','pt'=>'Portuguese','it'=>'Italian','pl'=>'Polish','ru'=>'Russian','nl'=>'Dutch','sv'=>'Swedish','da'=>'Danish','no'=>'Norwegian','fi'=>'Finnish','cs'=>'Czech','ro'=>'Romanian','hu'=>'Hungarian','uk'=>'Ukrainian','ar'=>'Arabic','zh'=>'Chinese','ja'=>'Japanese','ko'=>'Korean','el'=>'Greek','hr'=>'Croatian','bg'=>'Bulgarian','vn'=>'Vietnamese','ph'=>'Filipino','th'=>'Thai'];
                $langBase = ASSET_URL . '/core/main/img/languages/';
                $egLangList = [];
                if (!empty($egirl['languages'])) {
                    foreach (explode('|', $egirl['languages']) as $lc) {
                        $lc = trim($lc); if ($lc === '' || empty($langImgMap[$lc])) continue;
                        $egLangList[$lc] = $langNameMap[$lc] ?? strtoupper($lc);
                    }
                }

                // Ranks come from lol/val/tft columns plus the game_ranks JSON of every
                // other boosting game the girl selected in her dashboard profile.
                $rnks = function_exists('lb_egirl_game_ranks') ? lb_egirl_game_ranks($egirl) : [];
                $gameOpts = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
                $egGameList = [];
                foreach (explode('|', $egirl['games'] ?? '') as $g) {
                    $g = trim($g); if ($g === '') continue;
                    $gOpt = $gameOpts[$g] ?? null;
                    $egGameList[$g] = [
                        'icon'  => $gOpt['icon'] ?? (function_exists('util_game_icon_url') ? util_game_icon_url($g) : ''),
                        'label' => $gOpt['label'] ?? strtoupper($g),
                        'rank'  => $rnks[$g] ?? '',
                    ];
                }
                ?>
                <?php if ($egirl['is_online']): ?><span class="eg-chip online">● Online Now</span><?php endif; ?>
                <?php if (!empty($egirl['voice_chat'])): ?>
                    <span class="eg-chip voice"><i class="fa-solid fa-microphone" style="font-size:.75em;"></i> Voice Chat</span>
                <?php else: ?>
                    <span class="eg-chip" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08);color:rgba(255,255,255,.3);"><i class="fa-solid fa-microphone-slash" style="font-size:.75em;"></i> No Voice</span>
                <?php endif; ?>
                <?php if ($egirl['timezone']): ?><span class="eg-chip tz"><i class="fa-solid fa-globe" style="color:rgba(168,85,247,.55);"></i><?= htmlspecialchars($egirl['timezone']) ?></span><?php endif; ?>
            </div>

            <!-- Languages + games as icon groups so the header stays one line -->
            <div class="eg-hero__meta">
                <?php if ($egLangList): ?>
                <div class="eg-mini-group">
                    <span class="eg-mini-label"><?= t('Speaks') ?></span>
                    <?php
                    $langShown = array_slice($egLangList, 0, 6, true);
                    $langRest  = array_slice($egLangList, 6, null, true);
                    foreach ($langShown as $lc => $lname): ?>
                        <span class="eg-mini flag" data-tip="<?= htmlspecialchars($lname, ENT_QUOTES) ?>">
                            <img src="<?= $langBase . $langImgMap[$lc] ?>" alt="" onerror="this.closest('span').style.display='none'">
                        </span>
                    <?php endforeach; ?>
                    <?php if ($langRest): ?>
                        <span class="eg-mini more" data-tip="<?= htmlspecialchars(implode(', ', $langRest), ENT_QUOTES) ?>">+<?= count($langRest) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($egGameList): ?>
                <div class="eg-mini-group">
                    <span class="eg-mini-label"><?= t('Plays') ?></span>
                    <?php foreach ($egGameList as $g => $gi):
                        $tip = $gi['label'] . ($gi['rank'] !== '' ? ' · ' . $gi['rank'] : ''); ?>
                        <span class="eg-mini" data-tip="<?= htmlspecialchars($tip, ENT_QUOTES) ?>">
                            <?php if ($gi['icon'] !== ''): ?>
                                <img src="<?= htmlspecialchars($gi['icon'], ENT_QUOTES) ?>" alt="" onerror="this.style.display='none'">
                            <?php else: ?>
                                <i class="fa-solid fa-gamepad" style="font-size:11px;color:rgba(255,255,255,.5)"></i>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($services)): ?>
        <div class="eg-hero__actions">
            <a href="#book" class="btn-book" id="btnScrollBook">
                <i class="fa-solid fa-heart"></i> Book a Session
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ══ MAIN CONTENT CARD ══ -->
<div class="main-content">

    <div class="details">

        <!-- NAV TABS -->
        <div class="nav-tabs" id="egNavTabs">
            <a href="#tab-overview" class="active" data-tab="tab-overview">
                <i class="fa-solid fa-gauge-high"></i> Overview
            </a>
            <a href="#tab-services" data-tab="tab-services">
                <i class="fa-solid fa-layer-group"></i> Services
                <?php if (!empty($services)): ?><span class="eg-tab-count"><?= count($services) ?></span><?php endif; ?>
            </a>
            <?php if (!empty($availability)): ?>
            <a href="#tab-availability" data-tab="tab-availability">
                <i class="fa-solid fa-calendar-week"></i> Availability
            </a>
            <?php endif; ?>
            <?php if (!empty($reviews)): ?>
            <a href="#tab-reviews" data-tab="tab-reviews">
                <i class="fa-solid fa-star"></i> Reviews
                <span class="eg-tab-count"><?= count($reviews) ?></span>
            </a>
            <?php endif; ?>
            <a href="#tab-guidelines" data-tab="tab-guidelines">
                <i class="fa-solid fa-shield-halved"></i> <?= t('Guidelines') ?>
            </a>
            <a href="#tab-faq" data-tab="tab-faq">
                <i class="fa-solid fa-question-circle"></i> <?= t('FAQ') ?>
            </a>
        </div>

        <!-- TAB CONTENT + STICKY SUMMARY -->
        <div class="tab-layout" id="book">

            <!-- LEFT: Tab content -->
            <div>
                <!-- OVERVIEW TAB -->
                <div class="tab-pane active" id="tab-overview">
                    <?php if ($egirl['bio']): ?>
                        <div class="eg-section">
                            <div class="eg-section-label">About</div>
                            <p class="eg-bio"><?= nl2br(htmlspecialchars($egDecodeText($egirl['bio'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Stats row -->
                    <?php
                    global $db;
                    $eg_completed = (int)($db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'COMPLETED'", $egirl['egirl_id'] ?? $egirl['id'] ?? 0) ?? 0);
                    $eg_ongoing   = (int)($db->cell("SELECT COUNT(*) FROM egirl_orders WHERE egirl_id = ? AND status = 'IN_PROGRESS'", $egirl['egirl_id'] ?? $egirl['id'] ?? 0) ?? 0);
                    ?>
                    <div class="eg-stats-row">
                        <div class="eg-stat-card">
                            <div class="icon"><i class="fa-solid fa-check-circle"></i></div>
                            <div>
                                <div class="val"><?= $eg_completed ?></div>
                                <div class="lbl">Sessions Completed</div>
                            </div>
                        </div>
                        <?php if ($eg_ongoing > 0): ?>
                        <div class="eg-stat-card" style="border-color:rgba(34,197,94,.25);background:rgba(34,197,94,.06);">
                            <div class="icon" style="background:rgba(34,197,94,.15);border-color:rgba(34,197,94,.3);color:#22c55e;"><i class="fa-solid fa-circle-play"></i></div>
                            <div>
                                <div class="val" style="color:#22c55e;"><?= $eg_ongoing ?></div>
                                <div class="lbl">Session Ongoing</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php $eg_rev_count = (int)($egirl['review_count'] ?? 0); ?>
                        <div class="eg-stat-card">
                            <div class="icon"><i class="fa-solid fa-star"></i></div>
                            <div>
                                <?php if ($eg_rev_count > 0): ?>
                                    <div class="val"><?= number_format((float)$egirl['review_avg'], 1) ?></div>
                                    <div class="lbl"><?= $eg_rev_count ?> <?= $eg_rev_count === 1 ? 'Review' : 'Reviews' ?></div>
                                <?php else: ?>
                                    <div class="val">—</div>
                                    <div class="lbl">No reviews yet</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Featured services (only is_featured=1, max 3) -->
                    <?php
                    $tl=['hourly'=>'Hourly','per_game'=>'Per Game','rank_boost'=>'Rank Boost','coaching'=>'Coaching','just_chat'=>'Just Chat','custom'=>'Custom'];
                    $icons=['hourly'=>'🕐','per_game'=>'🎮','rank_boost'=>'🏆','coaching'=>'📚','just_chat'=>'💬','custom'=>'✨'];
                    // Slug of the icon file under /website/images/icons/ per game key stored on the service row.
                    $gimap = [
                        'lol' => 'league-of-legends', 'league-of-legends' => 'league-of-legends',
                        'val' => 'valorant',          'valorant' => 'valorant',
                        'tft' => 'teamfight-tactics', 'teamfight-tactics' => 'teamfight-tactics',
                    ];
                    $featuredSvcs = array_filter($services, fn($s) => !empty($s['is_featured']));
                    $featuredSvcs = array_slice(array_values($featuredSvcs), 0, 3);
                    // fallback: if no featured set, show first 3
                    if (empty($featuredSvcs)) {
                        $featuredSvcs = array_slice($services, 0, 3);
                    }
                    ?>
                    <?php if (!empty($featuredSvcs)): ?>
                    <div class="eg-section">
                        <div class="eg-section-label">Featured Services</div>
                        <?php foreach ($featuredSvcs as $svc): ?>
                            <?php $svcGame = strtolower($svc['game'] ?? ''); ?>
                            <div class="eg-svc js-svc-card" data-id="<?= $svc['id'] ?>" data-price="<?= $egFmt($svc['price_cents']) ?>" data-title="<?= htmlspecialchars(html_entity_decode($svc['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>" data-voice="<?= !empty($svc['includes_voice']) ? '1' : '0' ?>">
                                <div class="eg-svc-l">
                                    <div class="eg-svc-icon">
                                        <?php if ($svc['game'] && isset($gimap[$svcGame])): ?><img src="<?= ASSET_URL ?>/website/images/icons/<?= $gimap[$svcGame] ?>.png"><?php else: ?><?= $icons[$svc['type']]??'🎮' ?><?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="eg-svc-badge"><?= $tl[$svc['type']]??$svc['type'] ?></span>
                                        <div class="eg-svc-title"><?= html_entity_decode($svc['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                                        <?php if ($svc['description']): ?><div class="eg-svc-meta"><?= html_entity_decode($svc['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div><?php endif; ?>
                                        <div class="eg-svc-meta"><?= (int)$svc['unit_value'] ?> <?= htmlspecialchars($svc['unit_type']) ?></div>
                                        <?php if(!empty($svc['includes_voice'])): ?>
                                        <span class="eg-svc-voice-badge eg-svc-voice-yes"><i class="fa-solid fa-microphone"></i> Voice included</span>
                                        <?php else: ?>
                                        <span class="eg-svc-voice-badge eg-svc-voice-no"><i class="fa-solid fa-microphone-slash"></i> No voice</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="eg-svc-r">
                                    <div class="eg-svc-price"><?= $egSymbol ?><?= $egFmt($svc['price_cents']) ?></div>
                                    <button class="eg-svc-select">Select</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($services) > count($featuredSvcs)): ?>
                            <a href="#tab-services" class="eg-tab-link" data-tab="tab-services">View all <?= count($services) ?> services</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- SERVICES TAB — grouped by game with filter -->
                <div class="tab-pane" id="tab-services">
                    <?php
                    // Build game categories
                    $gameCategories = [
                        'lol' => ['label' => 'League of Legends', 'icon' => 'lol', 'svcs' => []],
                        'val' => ['label' => 'Valorant',          'icon' => 'val', 'svcs' => []],
                        'tft' => ['label' => 'TFT',               'icon' => 'tft', 'svcs' => []],
                        ''    => ['label' => 'General / Other',   'icon' => '',    'svcs' => []],
                    ];
                    foreach ($services as $svc) {
                        $g = strtolower($svc['game'] ?? '');
                        if (isset($gameCategories[$g])) {
                            $gameCategories[$g]['svcs'][] = $svc;
                        } else {
                            $gameCategories['']['svcs'][] = $svc;
                        }
                    }
                    // Only categories that actually have services
                    $activeCategories = array_filter($gameCategories, fn($c) => !empty($c['svcs']));
                    $multipleCategories = count($activeCategories) > 1;
                    ?>

                    <?php if ($multipleCategories): ?>
                    <!-- Filter bar — only shown when more than 1 category exists -->
                    <div class="eg-filter-bar" id="egFilterBar">
                        <button class="eg-filter-btn active" data-filter="all">
                            <i class="fa-solid fa-border-all"></i> All
                            <span class="eg-filter-count"><?= count($services) ?></span>
                        </button>
                        <?php foreach ($activeCategories as $gKey => $gCat): ?>
                        <button class="eg-filter-btn" data-filter="<?= htmlspecialchars($gKey === '' ? 'other' : $gKey) ?>">
                            <?php if ($gKey && isset($gimap[$gKey])): ?>
                                <img src="<?= ASSET_URL ?>/website/images/icons/<?= $gimap[$gKey] ?>.png" class="eg-filter-icon" alt="">
                            <?php else: ?>
                                <i class="fa-solid fa-shapes"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($gCat['label']) ?>
                            <span class="eg-filter-count"><?= count($gCat['svcs']) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($activeCategories as $gKey => $gCat): ?>
                    <div class="eg-section eg-cat-section" data-category="<?= htmlspecialchars($gKey === '' ? 'other' : $gKey) ?>">
                        <div class="eg-section-label" style="display:flex;align-items:center;gap:.5vw;">
                            <?php if ($gKey && isset($gimap[$gKey])): ?>
                                <img src="<?= ASSET_URL ?>/website/images/icons/<?= $gimap[$gKey] ?>.png" style="width:1.1em;height:1.1em;object-fit:contain;flex-shrink:0;" alt="<?= htmlspecialchars($gCat['label']) ?>">
                            <?php else: ?>
                                <i class="fa-solid fa-shapes" style="font-size:.9em;"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($gCat['label']) ?>
                            <span class="eg-tab-count"><?= count($gCat['svcs']) ?></span>
                        </div>
                        <?php foreach ($gCat['svcs'] as $svc): ?>
                            <?php $svcGame = strtolower($svc['game'] ?? ''); ?>
                            <div class="eg-svc js-svc-card" data-id="<?= $svc['id'] ?>" data-price="<?= $egFmt($svc['price_cents']) ?>" data-title="<?= htmlspecialchars(html_entity_decode($svc['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>" data-voice="<?= !empty($svc['includes_voice']) ? '1' : '0' ?>">
                                <div class="eg-svc-l">
                                    <div class="eg-svc-icon">
                                        <?php if ($svc['game'] && isset($gimap[$svcGame])): ?><img src="<?= ASSET_URL ?>/website/images/icons/<?= $gimap[$svcGame] ?>.png"><?php else: ?><?= $icons[$svc['type']]??'🎮' ?><?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="eg-svc-badge"><?= $tl[$svc['type']]??$svc['type'] ?></span>
                                        <div class="eg-svc-title"><?= html_entity_decode($svc['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                                        <?php if ($svc['description']): ?><div class="eg-svc-meta"><?= html_entity_decode($svc['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div><?php endif; ?>
                                        <div class="eg-svc-meta"><?= (int)$svc['unit_value'] ?> <?= htmlspecialchars($svc['unit_type']) ?></div>
                                        <?php if(!empty($svc['includes_voice'])): ?>
                                        <span class="eg-svc-voice-badge eg-svc-voice-yes"><i class="fa-solid fa-microphone"></i> Voice included</span>
                                        <?php else: ?>
                                        <span class="eg-svc-voice-badge eg-svc-voice-no"><i class="fa-solid fa-microphone-slash"></i> No voice</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="eg-svc-r">
                                    <div class="eg-svc-price"><?= $egSymbol ?><?= $egFmt($svc['price_cents']) ?></div>
                                    <button class="eg-svc-select">Select</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- AVAILABILITY TAB -->
                <?php if (!empty($availability)): ?>
                <div class="tab-pane" id="tab-availability">
                    <div class="eg-section">
                        <div class="eg-section-label">Weekly Schedule</div>
                        <?php
                        $dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                        $dnFull = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                        // Group slots by day
                        $byDay = [];
                        foreach ($availability as $slot) {
                            $byDay[(int)$slot['day_of_week']][] = $slot;
                        }
                        $todayDow = (int)date('w'); // 0=Sun
                        ?>
                        <div class="eg-schedule-grid">
                            <?php for ($d = 0; $d < 7; $d++):
                                $isToday = ($d === $todayDow);
                                $slots   = $byDay[$d] ?? [];
                                $hasSlots = !empty($slots);
                            ?>
                            <div class="eg-schedule-row<?= $isToday ? ' today' : '' ?><?= !$hasSlots ? ' off' : '' ?>">
                                <div class="eg-sched-day">
                                    <span class="eg-sched-dot<?= $hasSlots ? ' active' : '' ?>"></span>
                                    <span class="eg-sched-dname"><?= $dnFull[$d] ?></span>
                                    <?php if ($isToday): ?><span class="eg-sched-today-badge">Today</span><?php endif; ?>
                                </div>
                                <div class="eg-sched-times">
                                    <?php if ($hasSlots): ?>
                                        <?php foreach ($slots as $s): ?>
                                            <span class="eg-sched-time">🕐 <?= substr($s['time_from'],0,5) ?>–<?= substr($s['time_to'],0,5) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="eg-sched-off">Unavailable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <?php if ($egirl['timezone']): ?><div class="eg-timezone-note"><i class="fa-solid fa-globe" style="color:rgba(168,85,247,.5);"></i><?= htmlspecialchars($egirl['timezone']) ?></div><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- REVIEWS TAB -->
                <?php if (!empty($reviews)): ?>
                <div class="tab-pane" id="tab-reviews">
                    <div class="eg-section">
                        <div class="eg-section-label">Client Reviews</div>
                        <?php foreach ($reviews as $rev):
                            // 24h-grace placeholders render as a full 5-star card.
                            $revIsPlaceholder = !empty($rev['is_placeholder']);
                            $revRating  = $revIsPlaceholder ? 5 : (int)($rev['rating'] ?? 0);
                            $revComment = $revIsPlaceholder ? 'No Feedback left.' : (string)($rev['comment'] ?? '');
                            $revAuthor  = function_exists('util_mask_username')
                                ? util_mask_username($rev['client_username'] ?? '', 'Client')
                                : 'Client';
                        ?>
                            <div class="eg-review">
                                <div class="rev-h">
                                    <div class="rev-user">
                                        <?php if (!empty($rev['client_icon'])): ?><img src="<?= htmlspecialchars($rev['client_icon']) ?>" class="rev-av" alt=""><?php endif; ?>
                                        <span class="author"><?= htmlspecialchars($revAuthor) ?></span>
                                    </div>
                                    <span class="stars"><?= str_repeat('★',$revRating) ?><?= str_repeat('☆',5-$revRating) ?></span>
                                </div>
                                <?php if ($revComment !== ''): ?><div class="text"><?= htmlspecialchars($revComment) ?></div><?php endif; ?>
                                <div class="date"><?= date('M j, Y',strtotime($rev['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- GUIDELINES TAB -->
                <div class="tab-pane" id="tab-guidelines">
                    <div class="eg-section">
                        <div class="eg-section-label"><?= t('Community Guidelines') ?></div>
                        <p class="eg-bio" style="margin-bottom:1.5vw;"><?= t('Keep it chill and everyone has a good time. Here is what we expect from you when you book a session — break these and your order can be cancelled without a refund.') ?></p>

                        <div class="eg-guideline-list">

                            <div class="eg-guideline-item eg-guideline-important">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-hands-holding"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('Be Cool to Each Other') ?></div>
                                    <div class="eg-guideline-text"><?= t('There is a real person on the other side. Be friendly, be chill. Rude or toxic behaviour is not okay here.') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item eg-guideline-danger">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-ban"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('No Harassment. At All.') ?></div>
                                    <div class="eg-guideline-text"><?= t('Sexual comments, threats, insults or bullying end the session right away and get you banned for good. Same rules in game, in voice and in chat.') ?></div>
                                    <div class="eg-guideline-warn"><i class="fa-solid fa-exclamation-triangle"></i> <?= t('Session cancelled instantly — no refund') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item eg-guideline-danger">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-comment-dots"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('It Is Just Gaming') ?></div>
                                    <div class="eg-guideline-text"><?= t('No flirting, dating or anything sexual. You booked someone to play games with you — keep it at that or the session gets cancelled.') ?></div>
                                    <div class="eg-guideline-warn"><i class="fa-solid fa-exclamation-triangle"></i> <?= t('Session cancelled instantly — no refund') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-clock"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('Show Up On Time') ?></div>
                                    <div class="eg-guideline-text"><?= t('Agree on a time in the chat and stick to it. Something came up? Just say so early. Ghosting your sessions repeatedly can block you from booking again.') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-microphone-slash"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('Voice Chat Has Limits') ?></div>
                                    <div class="eg-guideline-text"><?= t('Voice is only there if the service says so. Do not push for more talk time than you paid for, and never pressure her into sharing private stuff.') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-lock"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('Privacy Stays Private') ?></div>
                                    <div class="eg-guideline-text"><?= t('Do not ask for real names, where she lives, socials or any contact outside the platform. Our team will never hand that out either.') ?></div>
                                </div>
                            </div>

                            <div class="eg-guideline-item">
                                <div class="eg-guideline-icon"><i class="fa-solid fa-flag"></i></div>
                                <div class="eg-guideline-body">
                                    <div class="eg-guideline-title"><?= t('Something Off? Tell Us') ?></div>
                                    <div class="eg-guideline-text"><?= t('If anything goes wrong during a session, ping our support instead of arguing it out yourself. We sort it out fast and fairly.') ?></div>
                                </div>
                            </div>

                        </div>

                        <div class="eg-guidelines-gate-note">
                            <div class="eg-gate-title"><i class="fa-solid fa-circle-check"></i><?= t('One quick confirmation') ?></div>
                            <div class="eg-gate-text">
                                <?= t('Before your booking goes through, a pop up asks you to confirm that you read these rules. Takes two seconds.') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ TAB -->
                <div class="tab-pane" id="tab-faq">
                    <div class="eg-section">
                        <div class="eg-section-label"><?= t('Frequently Asked Questions') ?></div>

                        <div class="eg-faq-list">

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('What exactly is a GG-Girl session?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('A GG-Girl session is a gaming companion experience. You play together with one of our GG-Girls in your favourite game — whether that is ranked, casual, or a custom mode. Sessions are friendly, fun, and 100%% gaming-focused.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('Is voice chat always included?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('Voice chat depends on the individual service. Each service card clearly shows whether voice is included or not. If voice is listed as included, you will play together using a voice channel. If not included, communication is text-based only.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('How do I start my session after booking?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('After your order is confirmed, you will receive instructions via the order chat. Coordinate with your GG-Girl on a start time and share your in-game details so she can invite you to the game.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('Can I cancel my order?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('You may request a cancellation before the session has started. Once a session is in progress, cancellations are handled on a case-by-case basis by our support team. Orders cancelled due to a violation of our guidelines are not eligible for a refund.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('What happens if the GG-Girl does not show up?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('If your GG-Girl is unavailable or does not respond within a reasonable time, please contact our support team immediately. You will be fully refunded or offered a replacement session.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('Is my account information safe?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('Yes. We never ask for your account password. Sessions are played together — your GG-Girl joins your lobby as a friend, she does not log into your account.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('Can I leave a review after my session?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('Absolutely! After your session is marked as completed, you will be able to leave a rating and a comment. Reviews help other clients find the right GG-Girl for them.') ?></div>
                            </div>

                            <div class="eg-faq-item">
                                <div class="eg-faq-q" onclick="egFaqToggle(this)">
                                    <span><?= t('What if I experience a problem during my session?') ?></span>
                                    <i class="fa-solid fa-chevron-down eg-faq-arrow"></i>
                                </div>
                                <div class="eg-faq-a"><?= t('Contact our support team straight away via the order chat or the help section. Do not try to resolve disputes directly — we are here to help and will handle every case fairly.') ?></div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Sticky Summary -->
            <div class="eg-sum-wrap">
                <div class="eg-summary">
                    <div class="eg-sum-bar"></div>
                    <div class="eg-sum-body">
                        <div class="eg-sum-prev">
                            <?php if ($egirl['icon']): ?><img src="<?= htmlspecialchars($egirl['icon']) ?>" alt=""><?php else: ?><div class="eg-sum-ph">👧</div><?php endif; ?>
                            <div>
                                <div class="eg-sum-pname"><?= htmlspecialchars($egirl['username']) ?></div>
                                <div class="eg-sum-prole">GG-Girl</div>
                            </div>
                        </div>
                        <div class="eg-sum-title">Book a Session</div>
                        <div class="eg-sum-sub">Choose a service and proceed to checkout.</div>

                        <?php if (empty($services)): ?>
                            <p style="color:rgba(255,255,255,.35);font-size:.75vw;text-align:center;padding:.8vw 0;">No services available.</p>
                        <?php else: ?>
                            <label class="eg-sum-label">Select Service</label>
                            <div class="eg-picker" id="egPicker">
                                <div class="eg-picker-btn" id="egPickerBtn">
                                    <span class="pname" id="egPickerName"><?= html_entity_decode($services[0]['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
                                    <span class="pprice" id="egPickerPrice"><?= $egSymbol ?><?= $egFmt($services[0]['price_cents']) ?></span>
                                    <i class="fa-solid fa-chevron-down parrow"></i>
                                </div>
                                <div class="eg-picker-list" id="egPickerList">
                                    <?php foreach ($services as $i => $svc): ?>
                                    <?php $svcTitleClean = html_entity_decode($svc['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                                    <div class="eg-picker-item<?= $i===0?' active':'' ?>"
                                         data-id="<?= (int)$svc['id'] ?>"
                                         data-price="<?= $egFmt($svc['price_cents']) ?>"
                                         data-title="<?= htmlspecialchars($svcTitleClean, ENT_QUOTES) ?>"
                                         data-meta="<?= htmlspecialchars(($tl[$svc['type']]??$svc['type']).' · '.(int)$svc['unit_value'].' '.$svc['unit_type'],ENT_QUOTES) ?>"
                                         data-voice="<?= !empty($svc['includes_voice']) ? '1' : '0' ?>">
                                        <div>
                                            <div class="iname"><?= htmlspecialchars($svcTitleClean) ?></div>
                                            <div class="imeta"><?= $tl[$svc['type']]??$svc['type'] ?> · <?= (int)$svc['unit_value'] ?> <?= htmlspecialchars($svc['unit_type']) ?></div>
                                        </div>
                                        <div class="iprice"><?= $egSymbol ?><?= $egFmt($svc['price_cents']) ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <label class="eg-sum-label">Notes (optional)</label>
                            <textarea id="bookNotes" class="eg-sum-textarea" rows="3" placeholder="Any specific requests..."></textarea>
                            <div class="eg-sum-div"></div>
                            <div class="eg-sum-total">
                                <span>Total</span>
                                <strong id="bookPriceDisplay"><?= $egSymbol ?><?= $egFmt($services[0]['price_cents']) ?></strong>
                            </div>
                            <button class="eg-book-btn" id="btnBookNow">
                                <i class="fa-solid fa-heart" style="font-size:.78em;margin-right:5px;"></i>Book Now
                            </button>
                            <div id="eg-booking-error" style="display:none;color:#ff6b6b;font-size:.78vw;text-align:center;margin-top:.45vw;"></div>
                            <?php if (!empty($egirl['voice_chat'])): ?>
                            <!-- Voice Chat Toggle -->
                            <div class="eg-voice-toggle" id="egVoiceToggle">
                                <div class="eg-voice-toggle-inner">
                                    <div class="eg-voice-icon"><i class="fa-solid fa-microphone"></i></div>
                                    <div class="eg-voice-text">
                                        <span class="eg-voice-label">Voice Chat</span>
                                        <span class="eg-voice-sub" id="egVoiceSub">Included in session</span>
                                    </div>
                                    <div class="eg-voice-switch" id="egVoiceSwitch" onclick="toggleVoice()">
                                        <div class="eg-voice-knob"></div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="bookVoiceChat" name="voice_chat" value="1">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- .tab-layout -->
    </div><!-- .details -->
</div><!-- .main-content -->


<div class="eg-guidelines-modal" id="egGuidelinesModal" aria-hidden="true">
    <div class="eg-guidelines-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="egGuidelinesModalTitle">
        <div class="eg-guidelines-modal__bar"></div>
        <div class="eg-guidelines-modal__body">
            <div class="eg-guidelines-modal__title" id="egGuidelinesModalTitle"><?= t('Please confirm before booking') ?></div>
            <div class="eg-guidelines-modal__text"><?= t('Please read these rules before you book a session.') ?></div>

            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                <div style="display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6;">
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#f472b6;margin-top:8px;"></i>
                    <span><?= t('Be respectful and polite.') ?></span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6;">
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#f472b6;margin-top:8px;"></i>
                    <span><?= t('No harassment, insults, or toxic behaviour.') ?></span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6;">
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#f472b6;margin-top:8px;"></i>
                    <span><?= t('No sexual, romantic, or personal requests.') ?></span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6;">
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#f472b6;margin-top:8px;"></i>
                    <span><?= t('Sessions are for gaming only.') ?></span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.78);font-size:14px;line-height:1.6;">
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#f472b6;margin-top:8px;"></i>
                    <span><?= t('If you break these rules, the session can be cancelled without a refund.') ?></span>
                </div>
            </div>

            <label class="eg-guidelines-modal__check">
                <input type="checkbox" id="egGuidelinesModalConfirm">
                <span class="eg-guidelines-modal__box" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                <span><?= t('I have read these rules and I agree to follow them.') ?></span>
            </label>
            <div class="eg-guidelines-modal__error" id="egGuidelinesModalError"><?= t('Please confirm that you have read and accepted the rules.') ?></div>
            <div class="eg-guidelines-modal__actions">
                <button type="button" class="eg-guidelines-modal__btn eg-guidelines-modal__btn--ghost" id="egGuidelinesModalCancel"><?= t('Cancel') ?></button>
                <button type="button" class="eg-guidelines-modal__btn eg-guidelines-modal__btn--primary" id="egGuidelinesModalContinue"><i class="fa-solid fa-check" style="margin-right:6px;"></i><?= t('Continue') ?></button>
            </div>
        </div>
    </div>
</div>

<?= $this->insert('website/components/get-started', ['variation' => 'two']) ?>

<?= $this->start('scripts') ?>
<script>
(function(){
    const AJAX = '<?= AJAX_URL ?>';
    const CURRENCY_SYMBOL = <?= json_encode($egSymbol) ?>;

    // Service state - driven by custom picker, no native select
    const firstPickerItem = document.querySelector('.eg-picker-item.active') || document.querySelector('.eg-picker-item');
    let currentServiceId    = firstPickerItem?.dataset.id || '';
    let currentServicePrice = firstPickerItem?.dataset.price || '0.00';
    let currentServiceTitle = firstPickerItem?.dataset.title || '';

    // ── Tab switching ──
    document.querySelectorAll('[data-tab]').forEach(link => {
        link.addEventListener('click', function(e){
            e.preventDefault();
            const target = this.dataset.tab;
            document.querySelectorAll('#egNavTabs a').forEach(a => a.classList.remove('active'));
            document.querySelector('#egNavTabs a[data-tab="'+target+'"]')?.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.getElementById(target)?.classList.add('active');
            document.getElementById('egNavTabs')?.scrollIntoView({behavior:'smooth', block:'nearest'});
        });
    });
    // Block coming soon tabs
    document.querySelectorAll('.eg-tab-soon').forEach(function(a){
        a.addEventListener('click', function(e){ e.preventDefault(); });
    });

    let guidelinesAccepted = false;

    function openGuidelinesTab() {
        const target = 'tab-guidelines';
        document.querySelectorAll('#egNavTabs a').forEach(a => a.classList.remove('active'));
        document.querySelector('#egNavTabs a[data-tab="'+target+'"]')?.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.getElementById(target)?.classList.add('active');
        document.getElementById(target)?.scrollIntoView({behavior:'smooth', block:'start'});
    }

    function goToBookingArea() {
        document.getElementById('book')?.scrollIntoView({behavior:'smooth', block:'start'});
    }

    // ── Scroll to guidelines first on hero button ──
    document.getElementById('btnScrollBook')?.addEventListener('click', function(e){
        e.preventDefault();
        if (!guidelinesAccepted) {
            openGuidelinesModal(function(){
                openGuidelinesTab();
                setTimeout(function(){ goToBookingArea(); }, 80);
            });
            return;
        }
        goToBookingArea();
    });

    // ── Service select sync ──
    function updatePrice(){
        document.getElementById('bookPriceDisplay').textContent = CURRENCY_SYMBOL + currentServicePrice;
        document.querySelectorAll('.js-svc-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.js-svc-card[data-id="'+currentServiceId+'"]').forEach(c => c.classList.add('selected'));
    }

    function selectService(id, price, title, voice) {
        currentServiceId    = id;
        currentServicePrice = price;
        currentServiceTitle = title;
        updatePrice();
        const lbl = document.getElementById('egPickerName');
        const prc = document.getElementById('egPickerPrice');
        if (lbl) lbl.textContent = title;
        if (prc) prc.textContent = CURRENCY_SYMBOL + price;
        document.querySelectorAll('.eg-picker-item').forEach(o => o.classList.toggle('active', o.dataset.id === id));
        document.querySelectorAll('.js-svc-card').forEach(c => c.classList.toggle('selected', c.dataset.id === id));
        // Show/hide voice toggle based on service
        const voiceToggleWrap = document.getElementById('egVoiceToggle');
        const voiceInput      = document.getElementById('bookVoiceChat');
        const hasVoice = (voice === '1' || voice === true);
        if (voiceToggleWrap) {
            voiceToggleWrap.style.display = hasVoice ? '' : 'none';
        }
        if (voiceInput) {
            voiceInput.value = hasVoice ? '1' : '0';
        }
        // Reset toggle to ON state when switching to a voice service
        if (hasVoice && voiceToggleWrap) {
            voiceToggleWrap.classList.remove('off');
            const sub = document.getElementById('egVoiceSub');
            if (sub) sub.textContent = 'Included in session';
        }
    }

    document.querySelectorAll('.js-svc-card').forEach(card => {
        card.addEventListener('click', function(){
            selectService(this.dataset.id, this.dataset.price, this.dataset.title, this.dataset.voice);
            if (window.innerWidth < 900)
                document.querySelector('.eg-summary')?.scrollIntoView({behavior:'smooth', block:'start'});
        });
    });
    const firstCard = document.querySelector('.js-svc-card');
    if (firstCard) {
        firstCard.classList.add('selected');
        // Init voice toggle for first service
        const voiceToggleWrap = document.getElementById('egVoiceToggle');
        const voiceInput      = document.getElementById('bookVoiceChat');
        const firstVoice = firstCard.dataset.voice === '1';
        if (voiceToggleWrap) voiceToggleWrap.style.display = firstVoice ? '' : 'none';
        if (voiceInput) voiceInput.value = firstVoice ? '1' : '0';
    }

    // ── Guidelines confirmation modal + Book Now ──
    const guidelinesModal = document.getElementById('egGuidelinesModal');
    const guidelinesModalConfirm = document.getElementById('egGuidelinesModalConfirm');
    const guidelinesModalError = document.getElementById('egGuidelinesModalError');
    const guidelinesModalContinue = document.getElementById('egGuidelinesModalContinue');
    const guidelinesModalCancel = document.getElementById('egGuidelinesModalCancel');
    let pendingGuidelinesAction = null;

    function closeGuidelinesModal(){
        guidelinesModal?.classList.remove('is-open');
        if (guidelinesModal) guidelinesModal.setAttribute('aria-hidden', 'true');
    }

    function openGuidelinesModal(action){
        pendingGuidelinesAction = action || null;
        if (guidelinesModalError) guidelinesModalError.style.display = 'none';
        if (guidelinesModalConfirm) guidelinesModalConfirm.checked = false;
        guidelinesModal?.classList.add('is-open');
        if (guidelinesModal) guidelinesModal.setAttribute('aria-hidden', 'false');
    }

    guidelinesModalConfirm?.addEventListener('change', function(){
        if (guidelinesModalError) guidelinesModalError.style.display = 'none';
    });

    guidelinesModalCancel?.addEventListener('click', function(){
        closeGuidelinesModal();
        pendingGuidelinesAction = null;
    });

    guidelinesModal?.addEventListener('click', function(e){
        if (e.target === guidelinesModal) {
            closeGuidelinesModal();
            pendingGuidelinesAction = null;
        }
    });

    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && guidelinesModal?.classList.contains('is-open')) {
            closeGuidelinesModal();
            pendingGuidelinesAction = null;
        }
    });

    guidelinesModalContinue?.addEventListener('click', function(){
        if (!guidelinesModalConfirm?.checked) {
            if (guidelinesModalError) guidelinesModalError.style.display = 'block';
            return;
        }
        guidelinesAccepted = true;
        closeGuidelinesModal();
        if (typeof pendingGuidelinesAction === 'function') {
            const action = pendingGuidelinesAction;
            pendingGuidelinesAction = null;
            action();
        }
    });

    function doBook(){
        const btn = document.getElementById('btnBookNow');
        const errEl = document.getElementById('eg-booking-error');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:11px;height:11px;border-width:2px;vertical-align:middle;margin-right:5px;"></span>Booking...';
        if (errEl) errEl.style.display = 'none';
        $.post(AJAX,{
            action:'egirl_book_service',
            service_id:currentServiceId,
            notes:document.getElementById('bookNotes')?.value||'',
            voice_chat:document.getElementById('bookVoiceChat')?.value||'0'
        },function(res){
            if(typeof res==='string'){try{res=JSON.parse(res);}catch(e){}}
            if(res.redirectUrl){ window.location.href=res.redirectUrl; }
            else {
                btn.disabled=false;
                btn.innerHTML='<i class="fa-solid fa-heart" style="font-size:.78em;margin-right:5px;"></i>Book Now';
                if(errEl&&res.sendToast?.message){errEl.textContent=res.sendToast.message;errEl.style.display='block';}
            }
        }).fail(function(){
            btn.disabled=false;
            btn.innerHTML='<i class="fa-solid fa-heart" style="font-size:.78em;margin-right:5px;"></i>Book Now';
        });
    }
    document.getElementById('btnBookNow')?.addEventListener('click', function(e){
        e.preventDefault();
        if (!guidelinesAccepted) {
            openGuidelinesModal(function(){
                openGuidelinesTab();
                setTimeout(function(){ doBook(); }, 80);
            });
            return;
        }
        doBook();
    });

    // ── Voice Chat Toggle ──
    // ── FAQ Toggle ──
    window.egFaqToggle = function(qEl) {
        const item = qEl.closest('.eg-faq-item');
        if (!item) return;
        item.classList.toggle('open');
    };

        window.toggleVoice = function(){
        const toggle = document.getElementById('egVoiceToggle');
        const input  = document.getElementById('bookVoiceChat');
        const sub    = document.getElementById('egVoiceSub');
        if (!toggle) return;
        const isOn = !toggle.classList.contains('off');
        toggle.classList.toggle('off', isOn);
        if (input) input.value = isOn ? '0' : '1';
        if (sub)   sub.textContent = isOn ? 'Not included' : 'Included in session';
    };

    // ── Custom Picker ──
    const picker     = document.getElementById('egPicker');
    const pickerBtn  = document.getElementById('egPickerBtn');
    const pickerList = document.getElementById('egPickerList');

    // Toggle open/close
    pickerBtn?.addEventListener('click', function(e){
        e.stopPropagation();
        picker.classList.toggle('open');
    });
    // Close on outside click
    document.addEventListener('click', function(){
        picker?.classList.remove('open');
    });
    pickerList?.addEventListener('click', function(e){ e.stopPropagation(); });

    // Item select
    pickerList?.querySelectorAll('.eg-picker-item').forEach(function(item){
        item.addEventListener('click', function(){
            selectService(this.dataset.id, this.dataset.price, this.dataset.title, this.dataset.voice);
            picker.classList.remove('open');
        });
    });

    // ── Open Guidelines from hash ──
    if (window.location.hash === '#tab-guidelines') {
        setTimeout(function(){
            openGuidelinesTab();
        }, 60);
    }

    // ── Category filter ──
    const filterBar = document.getElementById('egFilterBar');
    if (filterBar) {
        filterBar.querySelectorAll('.eg-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                filterBar.querySelectorAll('.eg-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                document.querySelectorAll('.eg-cat-section').forEach(section => {
                    if (filter === 'all' || section.dataset.category === filter) {
                        section.classList.remove('hidden');
                    } else {
                        section.classList.add('hidden');
                    }
                });
            });
        });
    }
})();
</script>
<?= $this->end() ?>
