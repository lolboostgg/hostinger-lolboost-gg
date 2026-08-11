<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'reviews-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ══════════════════════════════════════════════════════════
   REVIEWS PAGE — lolboost.gg  (v4 — animated, vivid)
══════════════════════════════════════════════════════════ */

/* ── Page shell: dynamic top offset via JS ── */
.rv-page {
    --rv-top: 80px; /* overridden by JS */
    padding-top: var(--rv-top);
    padding-bottom: 0;
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: #030817;
}

/* Ambient background glow */
.rv-page::before,
.rv-page::after { display:none; }

.rv-wrap {
    max-width: 1760px;
    margin: 0 auto;
    padding: 0 56px;
    position: relative;
    z-index: 1;
}
@media (max-width: 1400px) { .rv-wrap { padding: 0 36px; } }
@media (max-width: 640px)  { .rv-wrap { padding: 0 18px; } }

.rv-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: .78rem; font-weight: 900; letter-spacing: .12em; text-transform: uppercase;
    color: #c7c9ff;
    background: rgba(124,92,255,.13);
    border: 1px solid rgba(124,92,255,.28);
    border-radius: 999px; padding: 7px 17px; margin-bottom: 22px;
}
@keyframes rv-fade-up   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
@keyframes rv-fade-down { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:none; } }

/* ── Divider ── */
.rv-divider {
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 32px; opacity: .20;
}
.rv-divider-line { flex: 1; height: 1px; background: rgba(255,255,255,.15); }
.rv-divider-dot  { width: 5px; height: 5px; border-radius: 50%; background: rgba(255,255,255,.4); }

/* ── Grid ── */
.rv-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 52px;
    align-items: stretch; /* all cards in a row same height */
}
@media (max-width: 1300px) { .rv-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 900px)  { .rv-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 560px)  { .rv-grid { grid-template-columns: 1fr; } }

/* ── Card ── */
.rv-card {
    --ca: 99,102,241;
    --ca2: 139,92,246;
    background: #090f24;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    display: flex; flex-direction: column;
    height: 100%;
    position: relative; overflow: hidden;
    transition: border-color .22s, transform .22s, box-shadow .22s;
    opacity: 0;
    animation: rv-card-in .45s ease forwards;
}
.rv-card::before {
    content:''; position:absolute; left:0; top:16px; bottom:16px; width:3px;
    background: linear-gradient(180deg, rgba(var(--ca),1), rgba(var(--ca2),1));
    border-radius:0 3px 3px 0;
    transition: top .22s, bottom .22s;
}
.rv-card:hover {
    border-color: rgba(var(--ca),.30);
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(0,0,0,.35), inset 0 0 40px rgba(var(--ca),.03);
}
.rv-card:hover::before { top:8px; bottom:8px; }

/* stagger */
.rv-card:nth-child(1)  { animation-delay:.04s }
.rv-card:nth-child(2)  { animation-delay:.08s }
.rv-card:nth-child(3)  { animation-delay:.12s }
.rv-card:nth-child(4)  { animation-delay:.16s }
.rv-card:nth-child(5)  { animation-delay:.20s }
.rv-card:nth-child(6)  { animation-delay:.24s }
.rv-card:nth-child(7)  { animation-delay:.28s }
.rv-card:nth-child(8)  { animation-delay:.32s }
.rv-card:nth-child(9)  { animation-delay:.36s }
.rv-card:nth-child(10) { animation-delay:.40s }
.rv-card:nth-child(11) { animation-delay:.44s }
.rv-card:nth-child(12) { animation-delay:.48s }
.rv-card:nth-child(13) { animation-delay:.52s }
.rv-card:nth-child(14) { animation-delay:.56s }
.rv-card:nth-child(15) { animation-delay:.60s }
.rv-card:nth-child(16) { animation-delay:.64s }
.rv-card:nth-child(17) { animation-delay:.68s }
.rv-card:nth-child(18) { animation-delay:.72s }
@keyframes rv-card-in { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:none} }

/* ── Card header (stars + overall) ── */
.rv-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 0 22px;
}
.rv-card-stars { display:flex; gap:2px; font-size:1.5rem; color:#fbbf24; }
.rv-card-stars .empty { opacity:.18; }
.rv-card-date { font-size:1rem; opacity:.28; letter-spacing:.03em; }

/* ── Comment ── */
.rv-sect-comment {
    padding: 14px 20px 4px 22px;
    flex: 1;
}
.rv-comment {
    font-size: .90rem; line-height: 1.60; opacity: .78;
    font-style: italic; position: relative; padding-left: 16px;
    min-height: 44px;
}
.rv-comment::before {
    content:'"'; position:absolute; left:0; top:-4px;
    font-size: 1.6rem; color: rgba(var(--ca),1); opacity: .50;
    font-style: normal; line-height:1;
    transition: opacity .2s;
}
.rv-card:hover .rv-comment::before { opacity: .85; }
.rv-no-comment { min-height: 44px; }

/* ── Tags ── */
.rv-sect-tags {
    padding: 8px 20px 0 22px;
    display: flex; flex-direction: column; gap: 6px;
    min-height: 72px;
}
.rv-tag-row { display:flex; flex-wrap:wrap; gap:5px; align-items:center; }
.rv-tag {
    font-size:.68rem; font-weight:800; padding:3px 10px;
    border-radius:999px; white-space:nowrap; letter-spacing:.02em;
    display:inline-flex; align-items:center; gap:4px;
    transition: transform .15s;
}
.rv-tag:hover { transform:scale(1.05); }
.rv-tag--lol  { background:rgba(234,179,8,.10);  color:#fde68a; border:1px solid rgba(234,179,8,.22); }
.rv-tag--val  { background:rgba(239,68,68,.10);  color:#fca5a5; border:1px solid rgba(239,68,68,.22); }
.rv-tag--tft  { background:rgba(20,184,166,.10); color:#5eead4; border:1px solid rgba(20,184,166,.22); }
.rv-tag--type { background:rgba(255,255,255,.05); color:rgba(255,255,255,.50); border:1px solid rgba(255,255,255,.08); }
.rv-tag--rank { background:rgba(251,191,36,.08); color:rgba(253,224,71,.88); border:1px solid rgba(251,191,36,.20); }

/* ── Highlights ── */
.rv-sect-highlights {
    padding: 14px 20px 0 22px;
    display: flex; flex-wrap: wrap; gap: 5px;
    min-height: 36px;
    align-items: flex-start;
}
.rv-hl { font-size:.66rem; font-weight:700; padding:3px 9px; border-radius:999px; white-space:nowrap; text-transform:capitalize; transition:transform .15s; }
.rv-hl:hover { transform:scale(1.06); }
.rv-hl-0 { background:rgba(52,211,153,.08);  color:rgba(52,211,153,.88);  border:1px solid rgba(52,211,153,.18); }
.rv-hl-1 { background:rgba(99,102,241,.08);  color:rgba(165,180,252,.88); border:1px solid rgba(99,102,241,.18); }
.rv-hl-2 { background:rgba(251,191,36,.07);  color:rgba(251,191,36,.82);  border:1px solid rgba(251,191,36,.16); }
.rv-hl-3 { background:rgba(239,68,68,.07);   color:rgba(252,165,165,.82); border:1px solid rgba(239,68,68,.16); }

/* ── Divider ── */
.rv-card-divider {
    margin: 14px 20px 0 22px;
    height: 1px;
    background: rgba(255,255,255,.055);
}

/* ── People row ── */
.rv-sect-people {
    padding: 12px 20px 16px 22px;
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.rv-person-pair { display:flex; align-items:center; gap:8px; min-width:0; flex:1; }
.rv-person-pair + .rv-person-pair {
    justify-content: flex-end;
    padding-left: 10px;
    border-left: 1px solid rgba(255,255,255,.06);
}

.rv-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.07);
    border: 2px solid rgba(255,255,255,.12);
    overflow: hidden; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; color: rgba(255,255,255,.35);
    transition: transform .2s;
}
.rv-card:hover .rv-avatar { transform: scale(1.05); }
.rv-avatar img { width:100%; height:100%; object-fit:cover; }
.rv-avatar--booster {
    border-color: rgba(var(--ca),.50);
    background: rgba(var(--ca),.10);
    box-shadow: 0 0 0 2px rgba(var(--ca),.10);
}
.rv-avatar--client { border-color: rgba(255,255,255,.15); }

.rv-person-info { min-width:0; }
.rv-person-name { font-size:.82rem; font-weight:800; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90px; }
.rv-person-role { font-size:.65rem; opacity:.35; margin-top:1px; }
.rv-person-name--booster a { color:rgba(var(--ca),1); font-weight:800; text-decoration:none; filter:brightness(1.4); transition:filter .15s; }
.rv-person-name--booster a:hover { filter:brightness(1.8); }

/* ── Footer verified ── */
.rv-verified { display:flex; align-items:center; gap:4px; font-size:.68rem; color:#34d399; opacity:.70; }

/* ── Pagination ── */
.rv-pages { display:flex; align-items:center; justify-content:center; gap:7px; flex-wrap:wrap; margin-bottom:72px; }
.rv-pg {
    min-width:44px; height:44px; padding:0 14px;
    border-radius:12px; border:1px solid rgba(255,255,255,.09);
    background:rgba(255,255,255,.03); color:rgba(255,255,255,.48);
    font-size:.90rem; font-weight:700;
    display:inline-flex; align-items:center; justify-content:center;
    text-decoration:none; transition:all .16s ease;
}
.rv-pg:hover    { border-color:rgba(99,102,241,.42); color:#fff; background:rgba(99,102,241,.15); transform:translateY(-1px); }
.rv-pg.on       { background:linear-gradient(135deg, rgba(99,102,241,.90), rgba(139,92,246,.80)); border-color:rgba(99,102,241,.55); color:#fff; box-shadow:0 4px 18px rgba(99,102,241,.25); }
.rv-pg.off      { opacity:.18; pointer-events:none; }
.rv-pg-dots     { opacity:.28; font-size:.9rem; padding:0 4px; }

/* ── Monthly Review Timeline ── */
.rv-monthly {
    margin: 0 0 72px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 28px;
    position: relative;
    overflow: hidden;
}
.rv-monthly-inner {
    display: grid;
    grid-template-columns: minmax(280px, .82fr) minmax(0, 1.18fr);
    gap: 34px;
    padding: 42px 48px;
    align-items: center;
}
.rv-monthly-kicker {
    display:inline-flex;
    align-items:center;
    gap:8px;
    color:#a5b4fc;
    background:rgba(99,102,241,.12);
    border:1px solid rgba(99,102,241,.24);
    border-radius:999px;
    padding:7px 14px;
    font-size:.76rem;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.10em;
    margin-bottom:16px;
}
.rv-monthly-title {
    font-size: clamp(1.7rem, 2.6vw, 2.4rem);
    font-weight: 950;
    line-height: 1.08;
    letter-spacing: -.035em;
    margin: 0 0 12px;
}
.rv-monthly-title em {
    font-style: normal;
    color: #a78bfa;
}
.rv-monthly-copy {
    font-size: 1.02rem;
    line-height: 1.7;
    color: rgba(255,255,255,.58);
    max-width: 520px;
    margin: 0;
}
.rv-monthly-mini {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:22px;
}
.rv-monthly-pill {
    display:flex;
    flex-direction:column;
    gap:3px;
    min-width:136px;
    padding:14px 16px;
    border-radius:16px;
    background:rgba(255,255,255,.045);
    border:1px solid rgba(255,255,255,.09);
}
.rv-monthly-pill strong {
    font-size:1.35rem;
    line-height:1;
    color:#fff;
}
.rv-monthly-pill span {
    font-size:.70rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
    color:rgba(255,255,255,.38);
}
.rv-monthly-chart {
    position:relative;
    display:flex;
    align-items:flex-end;
    gap:12px;
    min-height:286px;
    padding:22px 18px 18px;
    border-radius:22px;
    background:rgba(0,0,0,.18);
    border:1px solid rgba(255,255,255,.08);
}
.rv-monthly-chart::before {
    content:'';
    position:absolute;
    left:18px;
    right:18px;
    top:22px;
    bottom:54px;
    background:
      linear-gradient(to top, rgba(255,255,255,.055) 1px, transparent 1px) 0 0 / 100% 25%,
      linear-gradient(to right, rgba(255,255,255,.035) 1px, transparent 1px) 0 0 / 16.66% 100%;
    opacity:.55;
    pointer-events:none;
}
.rv-month-bar {
    position:relative;
    z-index:1;
    flex:1 1 0;
    min-width:48px;
    height:220px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-end;
    gap:9px;
}
.rv-month-bar-fill-wrap {
    position:relative;
    width:100%;
    height:178px;
    display:flex;
    align-items:flex-end;
    justify-content:center;
}
.rv-month-bar-fill {
    width:min(46px, 78%);
    min-height:8px;
    border-radius:14px 14px 8px 8px;
    background:linear-gradient(180deg, rgba(var(--mb-ca),1), rgba(var(--mb-ca2),.78));
    box-shadow:0 14px 30px rgba(var(--mb-ca),.18), inset 0 1px 0 rgba(255,255,255,.26);
    transition:transform .18s ease, filter .18s ease;
}
.rv-month-bar:hover .rv-month-bar-fill {
    transform:translateY(-4px);
    filter:brightness(1.12);
}
.rv-month-count {
    position:absolute;
    top:-24px;
    left:50%;
    transform:translateX(-50%);
    font-size:.88rem;
    font-weight:950;
    color:#fff;
    white-space:nowrap;
    text-shadow:0 6px 18px rgba(0,0,0,.45);
}
.rv-month-label {
    font-size:.74rem;
    font-weight:900;
    color:rgba(255,255,255,.48);
    text-transform:uppercase;
    letter-spacing:.06em;
    white-space:nowrap;
}
.rv-month-empty {
    grid-column: 1/-1;
    width:100%;
    padding:44px 20px;
    text-align:center;
    color:rgba(255,255,255,.48);
    font-weight:800;
}
@media (max-width: 980px) {
    .rv-monthly-inner { grid-template-columns:1fr; padding:34px 28px; }
    .rv-monthly-chart { overflow-x:auto; justify-content:flex-start; scrollbar-width:thin; }
    .rv-month-bar { min-width:64px; }
}
@media (max-width: 600px) {
    .rv-monthly { border-radius:22px; margin-bottom:48px; }
    .rv-monthly-inner { padding:26px 18px; gap:24px; }
    .rv-monthly-copy { font-size:.92rem; }
    .rv-monthly-pill { min-width:calc(50% - 5px); padding:12px 13px; }
    .rv-monthly-chart { min-height:250px; padding:18px 14px 16px; }
    .rv-month-bar { height:200px; min-width:58px; }
    .rv-month-bar-fill-wrap { height:150px; }
}

/* ── Tablet ── */
@media (max-width: 900px) {
    .rv-card-header { padding: 14px 16px 0; }
    .rv-sect-highlights { padding: 12px 16px 0; }
    .rv-sect-comment    { padding: 10px 16px 4px; }
    .rv-sect-tags       { padding: 8px 16px 0; }
    .rv-sect-people     { padding: 10px 16px 14px; }
    .rv-card-divider    { margin: 12px 16px 0; }
}

/* ── Mobile card ── */
@media (max-width: 600px) {
    .rv-wrap { padding: 0 14px; }
    .rv-grid { gap: 18px; }
    .rv-card { border-radius: 18px; }
    .rv-card::before {
        left: 0; right: 0; top: 0; bottom: auto;
        width: auto; height: 3px;
        border-radius: 18px 18px 0 0;
        background: linear-gradient(90deg, rgba(var(--ca),1), rgba(var(--ca2),.6));
    }
    .rv-card-header { padding: 16px 16px 0 16px; align-items: center; }
    .rv-card-stars { font-size: 1.05rem; gap: 1px; }
    .rv-card-date  { font-size: .75rem; opacity: .30; }
    .rv-sect-highlights {
        padding: 10px 16px 0;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        min-height: unset;
        gap: 5px;
    }
    .rv-sect-highlights::-webkit-scrollbar { display: none; }
    .rv-hl { font-size: .65rem; padding: 4px 10px; flex-shrink: 0; border-radius: 999px; }
    .rv-sect-comment { padding: 10px 16px 0; }
    .rv-comment { font-size: .84rem; line-height: 1.55; padding-left: 14px; }
    .rv-comment::before { font-size: 1.3rem; top: -2px; }
    .rv-sect-tags {
        padding: 8px 16px 0;
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        min-height: unset;
        gap: 5px;
        align-items: center;
    }
    .rv-sect-tags::-webkit-scrollbar { display: none; }
    .rv-tag-row { display: contents; }
    .rv-tag { font-size: .65rem; padding: 4px 10px; flex-shrink: 0; }
    .rv-card-divider { margin: 12px 16px 0; }
    .rv-sect-people { padding: 10px 16px 14px; gap: 0; align-items: stretch; }
    .rv-person-pair { flex: 1; gap: 8px; align-items: center; min-width: 0; }
    .rv-person-pair + .rv-person-pair { padding-left: 12px; margin-left: 4px; }
    .rv-avatar { width: 36px; height: 36px; flex-shrink: 0; }
    .rv-person-name { font-size: .80rem; max-width: 80px; font-weight: 800; }
    .rv-person-role { font-size: .60rem; opacity: .32; margin-top: 1px; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
    .rv-verified { font-size: .62rem; gap: 3px; }
    .rv-pg { min-width: 36px; height: 36px; font-size: .80rem; }
}

/* ══════════════════════════════════════════════════════════
   Account-card visual system (cards)
══════════════════════════════════════════════════════════ */
.reviews-page{
    background:#030817;
}
.rv-card{
    --ca:79,110,247 !important;
    --ca2:142,165,255 !important;
    min-height:0;
    height:100%;
    border:1px solid rgba(255,255,255,.09);
    border-radius:24px;
    background:#090d1d;
    box-shadow:0 18px 48px rgba(0,0,0,.32),inset 0 1px 0 rgba(255,255,255,.035);
    overflow:hidden;
}
.rv-card::before,
.rv-card::after{
    display:none;
}
.rv-card:hover{
    transform:translateY(-4px);
    border-color:rgba(124,159,255,.32);
    background:#0a0f21;
    box-shadow:0 26px 65px rgba(0,0,0,.42),0 0 0 1px rgba(79,110,247,.06);
}
.rv-card-header{
    min-height:62px;
    padding:18px 18px 12px;
    border-bottom:1px solid rgba(255,255,255,.075);
    background:rgba(255,255,255,.018);
}
.rv-card-stars{
    gap:2px;
    font-size:1.05rem;
    text-shadow:none;
}
.rv-card-date{
    color:rgba(239,242,255,.48);
    font-size:.75rem;
    font-weight:750;
}
.rv-sect-highlights{
    min-height:52px;
    padding:14px 18px 0;
    gap:7px;
}
.rv-hl,
.rv-hl:first-child{
    min-height:27px;
    padding:6px 9px;
    border-radius:10px;
    background:rgba(255,255,255,.045) !important;
    border:1px solid rgba(255,255,255,.075) !important;
    color:rgba(239,242,255,.78) !important;
    font-size:.68rem;
    font-weight:750;
}
.rv-hl:first-child{
    color:#aebeff !important;
    border-color:rgba(124,159,255,.18) !important;
    background:rgba(79,110,247,.08) !important;
}
.rv-sect-comment{
    padding:16px 18px 8px;
}
.rv-comment{
    min-height:88px;
    max-height:112px;
    padding:0 8px 0 15px;
    color:rgba(239,242,255,.78);
    font-size:.86rem;
    line-height:1.62;
    overflow-y:auto;
    scrollbar-width:thin;
    scrollbar-color:rgba(124,159,255,.38) rgba(255,255,255,.035);
}
.rv-comment::before{
    top:-5px;
    color:#8ea5ff;
    font-size:1.5rem;
    opacity:.55;
}
.rv-comment::-webkit-scrollbar{ width:6px; }
.rv-comment::-webkit-scrollbar-track{ background:rgba(255,255,255,.045); border-radius:999px; }
.rv-comment::-webkit-scrollbar-thumb,
.rv-comment::-webkit-scrollbar-thumb:hover{
    background:rgba(124,159,255,.38);
    border-radius:999px;
}
.rv-no-comment{
    min-height:88px;
}
.rv-sect-tags{
    min-height:80px;
    padding:10px 18px 0;
    gap:7px;
}
.rv-tag-row{
    gap:7px;
}
.rv-tag,
.rv-tag--rank,
.rv-tag--lol,
.rv-tag--val,
.rv-tag--tft{
    min-height:28px;
    padding:6px 9px;
    border-radius:10px;
    background:rgba(255,255,255,.045) !important;
    border:1px solid rgba(255,255,255,.075) !important;
    color:rgba(239,242,255,.76) !important;
    font-size:.68rem;
    font-weight:750;
}
.rv-tag--rank{
    color:#c5d0ff !important;
    border-color:rgba(124,159,255,.16) !important;
    background:rgba(79,110,247,.07) !important;
}
.rv-tag--account-game{width:30px;padding-inline:7px!important;justify-content:center;}
.rv-tag--account-game img{width:15px!important;height:15px!important;object-fit:contain;}
.rv-tag--account-title{max-width:calc(100% - 37px);overflow:hidden;}
.rv-tag--account-title span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.rv-card-divider{
    display:none;
}
.rv-sect-people{
    min-height:64px;
    margin-top:16px;
    padding:12px 18px;
    border-top:1px solid rgba(255,255,255,.075);
    background:rgba(255,255,255,.018);
    gap:10px;
}
.rv-person-pair{
    gap:9px;
    min-width:0;
}
.rv-person-pair + .rv-person-pair{
    padding-left:10px;
    border-left:1px solid rgba(255,255,255,.075);
}
.rv-avatar{
    width:36px;
    height:36px;
    border:1px solid rgba(255,255,255,.12);
    box-shadow:none;
}
.rv-avatar--booster{
    border-color:rgba(124,159,255,.34);
    background:rgba(79,110,247,.08);
    box-shadow:0 0 0 2px rgba(79,110,247,.08);
}
.rv-person-name{
    max-width:90px;
    color:#fff;
    font-size:.76rem;
    font-weight:850;
}
.rv-person-role{
    color:rgba(239,242,255,.42);
    font-size:.57rem;
    font-weight:750;
    opacity:1;
}
.rv-person-name--booster a,
.rv-person-name--booster span{
    color:#9fb2ff !important;
}
.rv-monthly-inner{
    background:#090d1d;
    border-color:rgba(255,255,255,.09);
}
.rv-pg.on{
    background:#4f6ef7;
    border-color:rgba(124,159,255,.36);
    box-shadow:0 8px 20px rgba(79,110,247,.22);
}

/* Reviews 2026 — compact editorial layout (cards + feed) */
.reviews-page,.reviews-page main,.rv-page{background:#030817!important;}
.rv-page{padding-bottom:90px;}
.rv-wrap{width:min(1280px,calc(100% - 40px));max-width:none;margin:0 auto;}

.rv-eyebrow{
    margin:0 0 16px;
    padding:7px 11px;
    border:1px solid rgba(129,140,248,.22);
    border-radius:9px;
    background:#111936;
    color:#aab5ff;
    box-shadow:none;
}
.rv-divider{display:none;}

.rv-card-header{
    min-height:0;
    padding:14px 16px;
    background:#0b1124;
}
.rv-card-reviewer{display:flex;align-items:center;gap:9px;min-width:0;}
.rv-card-reviewer-copy{min-width:0;}
.rv-card-reviewer-name{color:#f4f6ff;font-size:.72rem;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rv-card-reviewer-meta{margin-top:2px;color:rgba(239,242,255,.36);font-size:.57rem;font-weight:700;}
.rv-card-score{
    display:inline-flex;align-items:center;gap:5px;flex:0 0 auto;
    padding:5px 8px;border:1px solid rgba(52,211,153,.18);border-radius:999px;
    background:#0b201d;color:#75e7bf;font-size:.64rem;font-weight:900;
}
.rv-card-score i{font-size:.55rem;}
.rv-card-stars{font-size:.92rem;}
.rv-card-date{font-size:.68rem;}
.rv-sect-highlights{
    min-height:0!important;
    padding:12px 16px 0;
}
.rv-sect-highlights:empty{display:none;}
.rv-hl,.rv-hl:first-child{min-height:25px;padding:5px 8px;border-radius:8px;font-size:.63rem;}
.rv-sect-comment{padding:15px 16px 8px;}
.rv-comment{
    min-height:0!important;
    max-height:none!important;
    padding:13px 14px 13px 27px;
    overflow:visible;
    border:1px solid rgba(255,255,255,.06);
    border-radius:11px;
    background:#060a17;
    font-size:.83rem;
    line-height:1.58;
}
.rv-comment::before{top:7px;left:10px;font-size:1.15rem;}
.rv-no-comment{display:none;min-height:0;}
.rv-sect-tags{
    min-height:0!important;
    padding:10px 16px 15px;
    gap:6px;
}
.rv-tag-row{gap:6px;}
.rv-tag,.rv-tag--rank,.rv-tag--lol,.rv-tag--val,.rv-tag--tft{
    min-height:26px;
    padding:5px 8px;
    border-radius:8px;
    font-size:.64rem;
}
.rv-sect-people{
    min-height:58px;
    margin-top:0;
    padding:10px 16px;
    background:#0b1124;
}
.rv-completed-label{color:rgba(239,242,255,.3);font-size:.5rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
.rv-completed-name{margin-top:1px;color:#eef2ff;font-size:.68rem;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rv-completed-name a{color:inherit!important;}
.rv-service-mini{
    display:inline-flex;align-items:center;max-width:130px;padding:5px 8px;
    border:1px solid rgba(249,115,22,.18);border-radius:999px;background:#21130d;
    color:#f5a56d;font-size:.57rem;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.rv-avatar{width:32px;height:32px;}
.rv-person-name{max-width:120px;font-size:.71rem;}
.rv-person-role{font-size:.54rem;}

.rv-pages{margin:26px 0 72px;}
.rv-pg{
    border-radius:10px;
    background:#080e21;
    box-shadow:none;
}
.rv-monthly{margin-top:0;}
.rv-monthly-inner{border-radius:22px;background:#080e21;box-shadow:none;}

/* Open, borderless and fluid page composition */
.rv-wrap{width:min(1480px,calc(100% - clamp(28px,5vw,88px)));}
.rv-eyebrow{border:0;background:#101735;}
.rv-feed-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 0 22px;}
.rv-feed-kicker{color:#929cff;font-size:.68rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;}
.rv-feed-title{margin:5px 0 0;color:#fff;font-size:clamp(25px,3vw,38px);font-weight:900;letter-spacing:-.035em;}
.rv-feed-copy{max-width:470px;margin:0;color:rgba(239,242,255,.45);font-size:.82rem;line-height:1.55;text-align:right;}

.rv-card{
    display:flex;
    margin:0;
    border:0;
    border-radius:15px;
    background:#080d1c;
    box-shadow:0 10px 28px rgba(0,0,0,.17);
}
.rv-card:hover{transform:translateY(-2px);border:0;background:#0a1021;box-shadow:0 16px 36px rgba(0,0,0,.25);}
.rv-card-header{padding:13px 14px;background:transparent;border-bottom:1px solid rgba(255,255,255,.045);}
.rv-avatar{border:0;background:#111a35;}
.rv-card-score{border:0;background:#09211d;}
.rv-hl,.rv-hl:first-child{border:0!important;background:#11182f!important;}
.rv-hl:first-child{background:#111b3d!important;}
.rv-sect-comment{display:flex;flex:1;min-height:0;padding:13px 14px 7px;}
.rv-comment{
    display:-webkit-box;
    width:100%;
    overflow:hidden;
    border:0;
    background:#050915;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:6;
}
.rv-sect-tags{padding:9px 14px 13px;}
.rv-tag,.rv-tag--rank,.rv-tag--lol,.rv-tag--val,.rv-tag--tft{
    border:0!important;
    background:#11172c!important;
}
.rv-tag--rank{background:#101a3a!important;}
.rv-tag--account-game{background:#11172c!important;}
.rv-sect-people{padding:10px 14px;border-top:1px solid rgba(255,255,255,.045);background:transparent;}
.rv-person-pair + .rv-person-pair{border-left:0;}
.rv-service-mini{border:0;background:#24150d;}

.rv-pages{margin:34px 0 88px;}
.rv-pg{border:0;background:#0b1229;}
.rv-monthly-inner{
    border:0;
    background:#080d1c;
    box-shadow:0 14px 40px rgba(0,0,0,.18);
}

@media(max-width:760px){
    .rv-feed-head{align-items:flex-start;flex-direction:column;}
    .rv-feed-copy{text-align:left;}
}

/* Reviews v6: one authoritative, centered and responsive grid layout */
.reviews-page .rv-page{
    width:100%;
    padding-top:var(--rv-top);
    background:#030817;
}
.reviews-page .rv-wrap{
    width:min(100%,1540px);
    max-width:none;
    margin-inline:auto;
    padding-inline:clamp(20px,4vw,64px);
}
.reviews-page .rv-feed-head{
    width:100%;
    margin:0 0 24px;
    padding-top:4px;
}
.reviews-page .rv-grid{
    display:grid !important;
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    align-items:stretch;
    width:100%;
    margin:0 0 42px;
    gap:20px;
    columns:unset !important;
}
.reviews-page .rv-card{
    display:flex;
    flex-direction:column;
    width:100%;
    height:390px;
    min-height:390px;
    margin:0;
    overflow:hidden;
    border:1px solid rgba(151,163,210,.10);
    border-radius:18px;
    background:#080e20;
    box-shadow:0 12px 32px rgba(0,0,0,.18);
}
.reviews-page .rv-card::before{display:none;}
.reviews-page .rv-card:hover{
    transform:translateY(-3px);
    border-color:rgba(129,140,248,.28);
    background:#0a1125;
    box-shadow:0 18px 42px rgba(0,0,0,.26);
}
.reviews-page .rv-card-header{
    flex:0 0 auto;
    min-height:66px;
    padding:14px 16px;
}
.reviews-page .rv-sect-highlights{
    flex:0 0 auto;
    min-height:0;
    padding:11px 16px 0;
}
.reviews-page .rv-sect-highlights:empty{display:none;}
.reviews-page .rv-sect-comment{
    display:flex;
    flex:1 1 auto;
    min-height:0;
    padding:12px 16px;
}
.reviews-page .rv-comment{
    align-self:stretch;
    min-height:74px;
    padding:14px;
    line-height:1.55;
    -webkit-line-clamp:6;
}
.reviews-page .rv-sect-tags{
    flex:0 0 auto;
    min-height:58px;
    padding:8px 16px 12px;
}
.reviews-page .rv-sect-people{
    flex:0 0 auto;
    min-height:64px;
    padding:10px 16px;
}
.reviews-page .rv-monthly{width:100%;}
.reviews-page .rv-monthly-inner{width:100%;}

.rv-completed-name a,
.rv-completed-name a:hover,
.rv-completed-name a:focus,
.rv-completed-name a:active,
.rv-person-name a,
.rv-person-name a:hover{
    color:#aeb9ff !important;
    text-decoration:none !important;
    border-bottom:0 !important;
}

@media(max-width:1050px){
    .reviews-page .rv-grid{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    }
}
@media(max-width:720px){
    .reviews-page .rv-wrap{padding-inline:16px;}
    .reviews-page .rv-feed-head{align-items:flex-start;flex-direction:column;}
    .reviews-page .rv-feed-copy{text-align:left;}
    .reviews-page .rv-grid{
        grid-template-columns:1fr !important;
        gap:14px;
    }
    .reviews-page .rv-card{
        height:auto;
        min-height:350px;
    }
    .reviews-page .rv-monthly-inner{grid-template-columns:1fr;}
}

/* ══════════════════════════════════════════════════════════
   HERO v11 — two clean rows: statement + proof band. No cards.
══════════════════════════════════════════════════════════ */
.rvx-hero{
    position:relative;
    width:100%;
    margin:0 0 26px;
    padding:clamp(18px,2.5vw,34px) 0 0;
}
.rvx-hero *{ box-sizing:border-box; }
.rvx-hero::before{
    content:'';
    position:absolute;
    top:-60px; right:-8%;
    width:52%; height:440px;
    background:radial-gradient(closest-side, rgba(79,110,247,.12), transparent 70%);
    pointer-events:none;
}

/* ── Row 1: statement left, spotlight right — top aligned ── */
.rvx-main{
    position:relative;
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);
    gap:clamp(32px,5vw,80px);
    align-items:center;
}

.rvx-copy{
    display:flex;
    flex-direction:column;
    min-width:0;
    animation:rv-fade-up .55s ease both;
}
.rvx-copy .rv-eyebrow{ align-self:flex-start; }
.rvx-copy h1{
    margin:0 0 14px;
    max-width:620px;
    color:#fff;
    font-size:clamp(2.3rem,3.8vw,3.9rem);
    font-weight:950;
    line-height:1.04;
    letter-spacing:-.05em;
}
.rvx-copy h1 em{ font-style:normal; color:#9da7ff; }
.rvx-copy > p{
    margin:0;
    max-width:540px;
    color:rgba(239,242,255,.56);
    font-size:1rem;
    line-height:1.7;
}

/* Spotlight: top-aligned quote, no box */
.rvx-spot{
    position:relative;
    display:flex;
    flex-direction:column;
    min-width:0;
    padding-top:6px;
    border:0;
    background:transparent;
    overflow:visible;
    animation:rv-fade-up .55s .1s ease both;
}
.rvx-spot-top{
    display:flex; align-items:center; gap:14px;
    margin-bottom:18px;
}
.rvx-spot-top > span{
    color:#9ca8ff; font-size:.64rem; font-weight:900;
    letter-spacing:.13em; text-transform:uppercase;
}
.rvx-spot-live{
    display:inline-flex; align-items:center; gap:6px;
    color:#49dba9; font-size:.62rem; font-weight:850; text-transform:uppercase; letter-spacing:.06em;
}
.rvx-spot-live::before{
    content:''; width:6px; height:6px; border-radius:50%; background:#49dba9;
    animation:rvx-ping 2.2s ease-out infinite;
}
@keyframes rvx-ping{
    0%{ box-shadow:0 0 0 0 rgba(73,219,169,.45); }
    70%{ box-shadow:0 0 0 8px rgba(73,219,169,0); }
    100%{ box-shadow:0 0 0 0 rgba(73,219,169,0); }
}

.rvx-spot-stage{ position:relative; min-height:212px; z-index:1; }
.rvx-spot-item{
    position:absolute; inset:0;
    display:flex; flex-direction:column; justify-content:flex-start;
    opacity:0; transform:translateY(8px);
    transition:opacity .22s ease, transform .22s ease;   /* outgoing: quick */
    pointer-events:none;
}
.rvx-spot-item.on{
    opacity:1; transform:none; pointer-events:auto;
    transition:opacity .45s ease .22s, transform .45s ease .22s;   /* incoming: waits for outgoing */
}
.rvx-spot-stars{ color:#fbbf24; font-size:1rem; letter-spacing:3px; margin-bottom:14px; }
.rvx-spot-stars .empty{ opacity:.2; }
.rvx-spot-text{
    position:relative;
    margin:0;
    padding-left:22px;
    color:rgba(246,248,255,.96);
    font-size:clamp(1.18rem,1.6vw,1.52rem);
    font-weight:600;
    line-height:1.55;
    letter-spacing:-.015em;
    display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:4; overflow:hidden;
}
.rvx-spot-text::before{
    content:'';
    position:absolute; left:0; top:4px; bottom:4px;
    width:3px; border-radius:999px;
    background:linear-gradient(180deg,#7988ff,rgba(121,136,255,.15));
}
.rvx-spot-meta{
    display:flex; align-items:center; justify-content:space-between; gap:14px;
    margin-top:20px; padding:0 0 0 20px;
    border:0;
}
.rvx-spot-user{ display:flex; align-items:center; gap:11px; min-width:0; }
.rvx-spot-user img{ width:38px; height:38px; border-radius:50%; object-fit:cover; flex:0 0 auto; background:#111a35; }
.rvx-spot-user strong{ display:block; color:#fff; font-size:.78rem; font-weight:850; }
.rvx-spot-user small{ display:block; margin-top:3px; color:rgba(255,255,255,.38); font-size:.62rem; }
.rvx-spot-user small i{ color:#34d399; margin-right:3px; }
.rvx-spot-service{
    max-width:150px; overflow:hidden;
    color:#9ca8ff; font-size:.62rem; font-weight:850;
    text-overflow:ellipsis; white-space:nowrap; flex:0 0 auto;
    text-transform:uppercase; letter-spacing:.06em;
}
.rvx-spot-nav{ display:flex; align-items:center; gap:10px; margin-top:20px; padding-left:22px; z-index:1; }
.rvx-spot-dot{
    position:relative;
    width:34px; height:34px; border-radius:50%; border:0; padding:0; cursor:pointer;
    overflow:hidden; flex:0 0 auto;
    background:#111a35;
    opacity:.35; filter:grayscale(.6);
    transition:opacity .2s, filter .2s, transform .2s, box-shadow .2s;
}
.rvx-spot-dot img{ width:100%; height:100%; object-fit:cover; display:block; }
.rvx-spot-dot:hover{ opacity:.7; }
.rvx-spot-dot.on{
    opacity:1; filter:none; transform:scale(1.08);
    box-shadow:0 0 0 2px #030817, 0 0 0 4px #7988ff;
}
.rvx-spot-dot:focus-visible{ outline:2px solid #9ca8ff; outline-offset:3px; }
.rvx-spot-bar{
    position:relative;
    width:56px; height:3px; margin-left:6px;
    border-radius:999px; background:rgba(255,255,255,.08);
    overflow:hidden; flex:0 0 auto;
}
.rvx-spot-bar i{
    position:absolute; inset:0; width:0;
    border-radius:inherit; background:#7988ff;
}
.rvx-spot-bar.run i{ animation:rvx-bar 6s linear forwards; }
@keyframes rvx-bar{ from{width:0} to{width:100%} }

/* ── Row 2: full-width proof band — one rhythm, hairline separators ── */
.rvx-band{
    display:flex;
    align-items:center;
    margin-top:clamp(28px,4vw,44px);
    padding:clamp(18px,2.2vw,26px) 0;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
}
.rvx-band > *{
    padding:2px clamp(22px,2.6vw,44px);
    border-left:1px solid rgba(255,255,255,.08);
}
.rvx-band > *:first-child{ padding-left:0; border-left:0; }
.rvx-band > *:last-child{ padding-right:0; }

.rvx-score{ display:flex; align-items:center; gap:13px; flex:0 0 auto; }
.rvx-score-num{
    color:#fff;
    font-size:2.7rem;
    font-weight:950;
    line-height:1;
    letter-spacing:-.04em;
    font-variant-numeric:tabular-nums;
}
.rvx-score-side{ display:flex; flex-direction:column; gap:4px; }
.rvx-score-stars{ color:#fbbf24; font-size:.85rem; letter-spacing:2px; line-height:1; }
.rvx-score-lbl{ color:rgba(239,242,255,.40); font-size:.60rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }

.rvx-stat{ display:flex; flex-direction:column; gap:6px; min-width:0; flex:0 0 auto; }
.rvx-stat b{
    color:#fff;
    font-size:1.7rem;
    font-weight:950;
    line-height:1;
    letter-spacing:-.02em;
    font-variant-numeric:tabular-nums;
    white-space:nowrap;
}
.rvx-stat b i{ font-style:normal; }
.rvx-stat--green b{ color:#34d399; }
.rvx-stat span{
    color:rgba(239,242,255,.38);
    font-size:.58rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.07em;
    white-space:nowrap;
}

/* Trustpilot as a stat column — same rhythm as the numbers */
.rvx-stat--tp b{ color:#20d59b; display:inline-flex; align-items:center; gap:8px; }
.rvx-tp-stars{ display:inline-flex; gap:2px; }
.rvx-tp-stars span{
    display:grid; place-items:center;
    width:16px; height:16px; border-radius:2px;
    background:#00b67a; color:#fff; font-size:.52rem; font-weight:900;
}
.rvx-stat--tp a{
    color:#20d59b; text-decoration:none;
    font-size:.58rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.07em; white-space:nowrap;
    transition:color .15s;
}
.rvx-stat--tp a:hover{ color:#5fe9bd; }
.rvx-stat--tp a:focus-visible{ outline:2px solid #20d59b; outline-offset:3px; border-radius:4px; }
.rvx-stat--tp a i{ font-size:.5rem; margin-left:3px; }

/* Distribution: takes the remaining width, vertically centered */
.rvx-dist{
    display:flex;
    flex-direction:column;
    justify-content:center;
    gap:6px;
    flex:1 1 auto;
    min-width:200px;
    margin:0; background:transparent;
}
.rvx-dist-row{ display:grid; grid-template-columns:24px minmax(0,1fr) 32px; gap:9px; align-items:center; }
.rvx-dist-row b{ color:rgba(239,242,255,.48); font-size:.60rem; font-weight:850; text-align:right; }
.rvx-dist-row b i{ color:#fbbf24; font-size:.48rem; margin-left:2px; }
.rvx-dist-track{ height:5px; border-radius:999px; background:rgba(255,255,255,.06); overflow:hidden; }
.rvx-dist-fill{
    display:block; height:100%; width:0; border-radius:inherit;
    background:#7988ff;
    transition:width .9s cubic-bezier(.2,.7,.2,1);
}
.rvx-dist-row:first-child .rvx-dist-fill{ background:#34d399; }
.rvx-dist-row small{ color:rgba(239,242,255,.35); font-size:.58rem; font-weight:800; text-align:right; font-variant-numeric:tabular-nums; }

/* ── Row 3: trust line ── */
.rvx-badges{
    display:flex;
    align-items:center;
    margin-top:20px;
    padding:0;
    border:0;
}
.rvx-badge{
    display:flex; align-items:center; gap:11px;
    padding:0 clamp(22px,2.6vw,44px);
    border:0; border-left:1px solid rgba(255,255,255,.06);
    background:transparent;
    min-width:0; flex:0 1 auto;
}
.rvx-badge:first-child{ padding-left:0; border-left:0; }
.rvx-badge > i{
    display:grid; place-items:center;
    width:30px; height:30px; flex:0 0 auto;
    border-radius:9px;
    font-size:.72rem;
    background:rgba(124,159,255,.10); color:#9ca8ff;
}
.rvx-badge--green > i{ background:rgba(52,211,153,.10); color:#34d399; }
.rvx-badge--gold  > i{ background:rgba(251,191,36,.09); color:#fbbf24; }
.rvx-badge p{ display:flex; flex-direction:column; gap:2px; margin:0; min-width:0; }
.rvx-badge strong{ color:#fff; font-size:.74rem; font-weight:850; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rvx-badge span{ color:rgba(255,255,255,.35); font-size:.62rem; line-height:1.4; }

@media (prefers-reduced-motion: reduce){
    .rvx-hero *{ animation:none !important; transition:none !important; }
    .rvx-dist-fill{ transition:none; }
    .rvx-spot-bar{ display:none; }
}
@media(max-width:1050px){
    .rvx-main{ grid-template-columns:1fr; gap:30px; }
    .rvx-hero::before{ display:none; }
    .rvx-spot{ padding-top:0; }
    .rvx-spot-stage{ min-height:210px; }
    .rvx-tp{ margin-left:0; }
}
@media(max-width:1100px){
    .rvx-band{ flex-wrap:wrap; row-gap:20px; }
    .rvx-dist{ flex-basis:100%; max-width:440px; padding-left:0; border-left:0; }
}
@media(max-width:820px){
    .rvx-band > *{ padding:2px 22px; }
    .rvx-band > *:nth-child(odd){ padding-left:0; border-left:0; }
    .rvx-badges{ flex-direction:column; align-items:flex-start; gap:14px; }
    .rvx-badge{ flex:none; padding:0; border-left:0; }
    .rvx-badge strong{ white-space:normal; }
}
@media(max-width:600px){
    .rvx-hero{ padding-top:16px; }
    .rvx-copy h1{ font-size:clamp(2rem,10vw,2.8rem); }
    .rvx-copy > p{ font-size:.92rem; }
    .rvx-score-num{ font-size:2.4rem; }
    .rvx-spot-text{ font-size:1rem; }
    .rvx-spot-meta{ align-items:flex-start; flex-direction:column; gap:10px; }
}

/* Reviews v12 — definitive page and card system */
.reviews-page .rv-page{
    color:#edf1ff;
    background:#040817 !important;
}
.reviews-page .rv-wrap{
    width:min(1480px,calc(100% - 48px));
    padding:0;
}
.rvx-hero{
    margin-bottom:58px;
    padding-top:clamp(32px,4vw,64px);
}
.rvx-hero::before{display:none;}
.rvx-main{
    grid-template-columns:minmax(0,.9fr) minmax(480px,1.1fr);
    gap:clamp(40px,6vw,96px);
    padding:0 2px 40px;
}
.rvx-copy h1{
    max-width:680px;
    font-size:clamp(2.6rem,3.8vw,4.15rem);
    line-height:1.02;
    letter-spacing:-.045em;
}
.rvx-copy h1 em{
    display:inline;
    margin:0;
    color:#aab4ff;
    background:none !important;
    -webkit-text-fill-color:currentColor !important;
}
.rvx-copy > p{max-width:610px;font-size:1.02rem;}
.rvx-spot{
    min-height:290px;
    padding:24px 26px 20px;
    border:1px solid rgba(139,154,224,.12);
    border-radius:20px;
    background:#090f22;
}
.rvx-spot-stage{min-height:180px;}
.rvx-spot-text{
    padding-left:0;
    max-height:150px;
    overflow-x:hidden;
    overflow-y:auto;
    font-size:clamp(1.05rem,1.35vw,1.28rem);
    font-weight:550;
    scrollbar-width:thin;
    scrollbar-color:rgba(129,144,255,.34) transparent;
    -webkit-line-clamp:unset;
}
.rvx-spot-text::before{display:none;}
.rvx-spot-text::-webkit-scrollbar{width:5px;}
.rvx-spot-text::-webkit-scrollbar-thumb{border-radius:999px;background:rgba(129,144,255,.34);}
.rvx-spot-meta{padding-left:0;}
.rvx-spot-nav{margin-top:14px;padding-left:0;}

/* Consolidated proof dashboard */
.rvx-band{
    display:grid;
    grid-template-columns:repeat(4,minmax(135px,.75fr)) minmax(300px,1.65fr);
    gap:0;
    margin:0;
    padding:0;
    overflow:hidden;
    border:0;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
    border-radius:0;
    background:transparent;
}
.rvx-band > *{
    min-height:124px;
    padding:22px 24px !important;
    border:0 !important;
    border-right:1px solid rgba(255,255,255,.06) !important;
}
.rvx-band > *:last-child{border-right:0 !important;}
.rvx-score{align-items:flex-start;flex-direction:column;gap:9px;}
.rvx-score-num{font-size:2.4rem;}
.rvx-score-side{gap:6px;}
.rvx-stat{justify-content:center;gap:9px;}
.rvx-stat b{font-size:1.75rem;}
.rvx-stat--tp b{align-items:flex-start;flex-direction:column;gap:7px;}
.rvx-dist{
    width:100%;
    max-width:none;
    min-width:0;
    padding-block:18px !important;
    justify-content:center;
}
.rvx-dist-row{grid-template-columns:25px minmax(80px,1fr) 34px;}
.rvx-dist-track{height:6px;}

.rvx-badges{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:0;
    margin-top:12px;
    border-bottom:1px solid rgba(255,255,255,.06);
}
.rvx-badge{
    min-height:70px;
    padding:14px 22px !important;
    border:0 !important;
    border-left:1px solid rgba(255,255,255,.06) !important;
    border-radius:0;
    background:transparent;
}
.rvx-badge:first-child{padding-left:0 !important;border-left:0 !important;}
.rvx-badge strong{font-size:.76rem;}
.rvx-badge span{font-size:.64rem;}

/* Reviews heading */
.reviews-page .rv-feed-head{
    align-items:flex-end;
    margin-bottom:20px;
    padding:0 2px;
}
.reviews-page .rv-feed-title{font-size:clamp(1.65rem,2.8vw,2.45rem);}

/* Three-column equal-height review cards */
.reviews-page .rv-grid{
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    gap:18px;
}
.reviews-page .rv-card{
    height:370px;
    min-height:370px;
    border:1px solid rgba(139,154,224,.10);
    border-radius:17px;
    background:#080e20;
    box-shadow:none;
}
.reviews-page .rv-card:hover{
    transform:translateY(-3px);
    border-color:rgba(129,144,255,.28);
    background:#0a1023;
    box-shadow:0 18px 42px rgba(0,0,0,.24);
}
.reviews-page .rv-card-header{
    min-height:64px;
    padding:13px 15px;
    border-bottom:1px solid rgba(255,255,255,.055);
    background:#0a1022;
}
.reviews-page .rv-card-reviewer-name{font-size:.76rem;}
.reviews-page .rv-card-reviewer-meta{font-size:.59rem;}
.reviews-page .rv-card-score{
    padding:6px 9px;
    background:#09231e;
    color:#6ee7bd;
}
.reviews-page .rv-sect-highlights{
    padding:11px 15px 0;
    gap:6px;
}
.reviews-page .rv-hl,
.reviews-page .rv-hl:first-child{
    min-height:24px;
    padding:5px 8px;
    border:0 !important;
    border-radius:7px;
    background:#11182e !important;
    color:#b9c5f8 !important;
}
.reviews-page .rv-sect-comment{
    flex:1 1 auto;
    padding:11px 15px 7px;
}
.reviews-page .rv-comment{
    min-height:0;
    height:100%;
    max-height:none;
    padding:13px 14px 13px 25px;
    border:0;
    border-radius:11px;
    background:#050a17;
    color:rgba(239,242,255,.78);
    font-size:.82rem;
    font-style:normal;
    line-height:1.55;
    display:block;
    overflow-x:hidden;
    overflow-y:auto;
    scrollbar-width:thin;
    scrollbar-color:rgba(129,144,255,.34) transparent;
    -webkit-line-clamp:unset;
}
.reviews-page .rv-comment::before{top:8px;left:10px;}
.reviews-page .rv-comment::-webkit-scrollbar{width:5px;}
.reviews-page .rv-comment::-webkit-scrollbar-track{background:transparent;}
.reviews-page .rv-comment::-webkit-scrollbar-thumb{
    border-radius:999px;
    background:rgba(129,144,255,.34);
}
.reviews-page .rv-sect-tags{
    min-height:52px;
    padding:7px 15px 11px;
}
.reviews-page .rv-tag,
.reviews-page .rv-tag--rank,
.reviews-page .rv-tag--lol,
.reviews-page .rv-tag--val,
.reviews-page .rv-tag--tft{
    min-height:24px;
    padding:5px 8px;
    border:0 !important;
    border-radius:7px;
    background:#11172b !important;
    color:#b8c2e9 !important;
}
.reviews-page .rv-tag--rank{background:#111a38 !important;color:#c5ceff !important;}
.reviews-page .rv-sect-people{
    min-height:62px;
    padding:10px 15px;
    border-top:1px solid rgba(255,255,255,.055);
    background:#0a1022;
}
.reviews-page .rv-completed-name a,
.reviews-page .rv-completed-name a:hover,
.reviews-page .rv-completed-name a:focus{
    color:#b8c4ff !important;
    text-decoration:none !important;
}
.reviews-page .rv-service-mini{
    border:0;
    background:#21150f;
    color:#f2a46f;
}

@media(max-width:1180px){
    .rvx-main{grid-template-columns:1fr;gap:26px;}
    .rvx-spot{min-height:270px;}
    .rvx-band{grid-template-columns:repeat(4,1fr);}
    .rvx-dist{grid-column:1/-1;border-top:1px solid rgba(255,255,255,.06) !important;}
    .rvx-band > :nth-child(4){border-right:0 !important;}
}
@media(max-width:900px){
    .reviews-page .rv-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;}
    .rvx-band{grid-template-columns:repeat(2,1fr);}
    .rvx-band > :nth-child(2){border-right:0 !important;}
    .rvx-band > :nth-child(-n+2){border-bottom:1px solid rgba(255,255,255,.06) !important;}
    .rvx-badges{grid-template-columns:1fr;border-bottom:0;}
    .rvx-badge,
    .rvx-badge:first-child{
        padding:13px 0 !important;
        border-left:0 !important;
        border-bottom:1px solid rgba(255,255,255,.06) !important;
    }
}
@media(max-width:620px){
    .reviews-page .rv-wrap{width:calc(100% - 28px);}
    .rvx-hero{margin-bottom:42px;padding-top:20px;}
    .rvx-main{padding-bottom:24px;}
    .rvx-copy h1{font-size:clamp(2.15rem,10vw,3rem);}
    .rvx-copy h1 em{display:inline;}
    .rvx-spot{min-height:300px;padding:18px;}
    .rvx-spot-meta{align-items:flex-start;flex-direction:column;}
    .rvx-band{grid-template-columns:1fr;}
    .rvx-band > *{
        min-height:92px;
        border-right:0 !important;
        border-bottom:1px solid rgba(255,255,255,.06) !important;
    }
    .rvx-band > *:last-child{border-bottom:0 !important;}
    .rvx-score{align-items:center;flex-direction:row;}
    .rvx-stat--tp b{align-items:center;flex-direction:row;}
    .reviews-page .rv-feed-head{align-items:flex-start;}
    .reviews-page .rv-grid{grid-template-columns:1fr !important;}
    .reviews-page .rv-card{height:auto;min-height:345px;}
}
</style>
<?= $this->stop() ?>

<?php
global $db;
$page     = max(1,(int)($_GET['page']??1)); // initial page; JS handles subsequent pages without URL change
$per_page = 18;
$offset   = ($page-1)*$per_page;

// Boosting reviews (reviews.overall) and GG-Girl reviews (egirl_reviews.rating) are
// shown together, so every aggregate reads from a union of both instead of one table.
$rv_all_scores = "
    SELECT overall AS score, created_at FROM reviews WHERE approved = 1
    UNION ALL
    SELECT rating  AS score, created_at FROM egirl_reviews WHERE approved = 1
    UNION ALL
    SELECT rating  AS score, created_at FROM seller_reviews WHERE approved = 1
";

$agg           = $db->run("SELECT COUNT(*) AS total, ROUND(AVG(score),1) AS avg FROM ({$rv_all_scores}) rv");
$total_reviews = (int)($agg[0]['total']??0);
$avg_overall   = (float)($agg[0]['avg']??0);
$total_pages   = max(1,(int)ceil($total_reviews/$per_page));

$dist_rows = $db->run("SELECT score AS overall, COUNT(*) AS cnt FROM ({$rv_all_scores}) rv GROUP BY score");
$dist=[];
foreach($dist_rows as $d) $dist[(int)$d['overall']]=(int)$d['cnt'];
$five_pct = $total_reviews>0 ? round(($dist[5]??0)/$total_reviews*100) : 0;


$rv_start_date = '2026-01-09';
$rv_month_rows = $db->run("
    SELECT DATE_FORMAT(created_at, '%Y-%m-01') AS month_key, COUNT(*) AS cnt
    FROM ({$rv_all_scores}) rv
    WHERE created_at >= '{$rv_start_date}'
    GROUP BY month_key
    ORDER BY month_key ASC
");
$rv_month_counts = [];
foreach (($rv_month_rows ?: []) as $mr) {
    $rv_month_counts[(string)$mr['month_key']] = (int)$mr['cnt'];
}
$rv_monthly = [];
try {
    $rv_cursor = new DateTime($rv_start_date);
    $rv_cursor->modify('first day of this month');
    $rv_end = new DateTime('first day of this month');
    while ($rv_cursor <= $rv_end) {
        $key = $rv_cursor->format('Y-m-01');
        $rv_monthly[] = [
            'key' => $key,
            'label' => $rv_cursor->format('M Y'),
            'short' => $rv_cursor->format('M'),
            'count' => (int)($rv_month_counts[$key] ?? 0),
        ];
        $rv_cursor->modify('+1 month');
    }
} catch (Throwable $e) {
    $rv_monthly = [];
}
$rv_month_max = 0;
$rv_month_total = 0;
foreach ($rv_monthly as $m) {
    $rv_month_max = max($rv_month_max, (int)$m['count']);
    $rv_month_total += (int)$m['count'];
}
$rv_month_peak = 0;
$rv_month_peak_label = '';
foreach ($rv_monthly as $m) {
    if ((int)$m['count'] >= $rv_month_peak) {
        $rv_month_peak = (int)$m['count'];
        $rv_month_peak_label = (string)$m['label'];
    }
}
$rv_month_avg = count($rv_monthly) > 0 ? round($rv_month_total / count($rv_monthly)) : 0;

// Page across both sources at once: order the combined id list first, then hydrate
// only the rows of the current page from their own table.
$rv_index = $db->run("
    SELECT 'boost'  AS src, id, created_at FROM reviews        WHERE approved = 1
    UNION ALL
    SELECT 'egirl'  AS src, id, created_at FROM egirl_reviews  WHERE approved = 1
    UNION ALL
    SELECT 'seller' AS src, id, created_at FROM seller_reviews WHERE approved = 1
    ORDER BY created_at DESC, id DESC
    LIMIT {$per_page} OFFSET {$offset}
") ?: [];

$rv_boost_ids  = [];
$rv_egirl_ids  = [];
$rv_seller_ids = [];
foreach ($rv_index as $rvRow) {
    $src = (string)($rvRow['src'] ?? '');
    if     ($src === 'egirl')  $rv_egirl_ids[]  = (int)$rvRow['id'];
    elseif ($src === 'seller') $rv_seller_ids[] = (int)$rvRow['id'];
    else                       $rv_boost_ids[]  = (int)$rvRow['id'];
}

$rv_boost_rows = [];
if (!empty($rv_boost_ids)) {
    $rv_boost_in = implode(',', array_map('intval', $rv_boost_ids));
    $rv_boost_rows = $db->run("
    SELECT r.*,
           r.booster_id,
           b.username  AS booster_name, b.icon AS booster_icon,
           o.client_id,
           c.username  AS client_username, c.icon AS client_icon,
           bf.name AS form_name, bf.game AS game, bf.type AS form_type,
           o.form_id,
           oo.start_tier, oo.end_tier, oo.start_division, oo.end_division,
           oo.start_lp, oo.end_lp, oo.start_rr, oo.end_rr,
           oo.server, oo.coach_type, oo.queue_type,
           oo.matches, oo.hours
    FROM reviews r
    LEFT JOIN boosters      b   ON b.id        = r.booster_id
    LEFT JOIN orders        o   ON o.id        = r.order_id
    LEFT JOIN clients       c   ON c.id        = o.client_id
    LEFT JOIN boost_forms   bf  ON bf.id       = o.form_id
    LEFT JOIN order_options oo  ON oo.order_id = r.order_id
    WHERE r.approved=1 AND r.id IN ({$rv_boost_in})
    ") ?: [];
}

$rv_egirl_rows = [];
if (!empty($rv_egirl_ids)) {
    $rv_egirl_in = implode(',', array_map('intval', $rv_egirl_ids));
    $rv_egirl_rows = $db->run("
        SELECT er.*, eg.username AS egirl_name, eg.icon AS egirl_icon,
               c.username AS client_username, c.icon AS client_icon,
               eo.service_title
        FROM egirl_reviews er
        LEFT JOIN boosters     eg ON eg.id = er.egirl_id
        LEFT JOIN clients      c  ON c.id  = er.client_id
        LEFT JOIN egirl_orders eo ON eo.id = er.egirl_order_id
        WHERE er.approved = 1 AND er.id IN ({$rv_egirl_in})
    ") ?: [];
}

$rv_seller_rows = [];
if (!empty($rv_seller_ids)) {
    $rv_seller_in = implode(',', array_map('intval', $rv_seller_ids));
    $rv_seller_rows = $db->run("
        SELECT sr.*, s.username AS seller_name, s.icon AS seller_icon, s.slug AS seller_slug,
               c.username AS client_username, c.icon AS client_icon,
               sa.id AS account_id, sa.title AS account_title, sa.game AS account_game
        FROM seller_reviews sr
        LEFT JOIN sellers s ON s.id = sr.seller_id
        LEFT JOIN clients c ON c.id = sr.client_id
        LEFT JOIN selling_accounts sa
               ON sa.id = COALESCE(
                    (
                        SELECT sae.id
                        FROM selling_accounts sae
                        WHERE sae.id = sr.purchase_id
                          AND sae.seller_id = sr.seller_id
                          AND sae.client_id = sr.client_id
                          AND sae.sold = 1
                        LIMIT 1
                    ),
                    (
                        SELECT sar.id
                        FROM selling_accounts sar
                        WHERE sar.seller_id = sr.seller_id
                          AND sar.client_id = sr.client_id
                          AND sar.sold = 1
                        ORDER BY ABS(TIMESTAMPDIFF(SECOND, COALESCE(sar.sold_at, sar.created_at), sr.created_at)) ASC
                        LIMIT 1
                    )
               )
        WHERE sr.approved = 1 AND sr.id IN ({$rv_seller_in})
    ") ?: [];
}

// Normalize every source into the shape the card markup below expects and restore
// the combined ordering from the index query.
$rv_by_key = [];
foreach ($rv_boost_rows as $row) {
    $rv_by_key['boost:' . (int)$row['id']] = $row;
}
foreach ($rv_egirl_rows as $row) {
    $rv_by_key['egirl:' . (int)$row['id']] = [
        'id'              => (int)$row['id'],
        'is_egirl_review' => true,
        'overall'         => (int)($row['rating'] ?? 5),
        'comments'        => (string)($row['comment'] ?? ''),
        'created_at'      => (string)($row['created_at'] ?? ''),
        'highlights'      => null,
        'booster_id'      => (int)($row['egirl_id'] ?? 0),
        'booster_name'    => (string)($row['egirl_name'] ?? ''),
        'booster_icon'    => (string)($row['egirl_icon'] ?? ''),
        'client_username' => (string)($row['client_username'] ?? ''),
        'client_icon'     => (string)($row['client_icon'] ?? ''),
        'form_name'       => trim((string)($row['service_title'] ?? '')) ?: 'GG-Girl Session',
        'form_type'       => '',
        'game'            => '',
    ];
}

foreach ($rv_seller_rows as $row) {
    $rv_by_key['seller:' . (int)$row['id']] = [
        'id'               => (int)$row['id'],
        'is_seller_review' => true,
        'seller_slug'      => (string)($row['seller_slug'] ?? ''),
        'overall'          => (int)($row['rating'] ?? 5),
        'comments'         => (string)($row['comment'] ?? ''),
        'created_at'       => (string)($row['created_at'] ?? ''),
        'highlights'       => null,
        'booster_id'       => (int)($row['seller_id'] ?? 0),
        'booster_name'     => (string)($row['seller_name'] ?? ''),
        'booster_icon'     => (string)($row['seller_icon'] ?? ''),
        'client_username'  => (string)($row['client_username'] ?? ''),
        'client_icon'      => (string)($row['client_icon'] ?? ''),
        'form_name'        => function_exists('lb_seller_review_service_label') ? lb_seller_review_service_label($row) : 'Marketplace Purchase',
        'form_type'        => '',
        'game'             => '',
        'account_id'       => (int)($row['account_id'] ?? 0),
        'account_title'    => (string)($row['account_title'] ?? ''),
        'account_game'     => (string)($row['account_game'] ?? ''),
    ];
}

$reviews_raw = [];
foreach ($rv_index as $rvRow) {
    $src = (string)($rvRow['src'] ?? '');
    $prefix = ($src === 'egirl' || $src === 'seller') ? $src . ':' : 'boost:';
    $key = $prefix . (int)$rvRow['id'];
    if (isset($rv_by_key[$key])) $reviews_raw[] = $rv_by_key[$key];
}

$tier_lbl  = fn(int $n)   => [1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'][$n]??'';
$div_lbl   = fn(int $n)   => [1=>'IV',2=>'III',3=>'II',4=>'I'][$n]??'';
$game_lbl  = fn(string $g)=> ['lol'=>'League of Legends','val'=>'Valorant','tft'=>'Teamfight Tactics'][strtolower($g)]??strtoupper($g);
$game_cls  = fn(string $g)=> ['lol'=>'rv-tag--lol','val'=>'rv-tag--val','tft'=>'rv-tag--tft'][strtolower($g)]??'rv-tag--type';
$stars_html= function(int $n):string{ $o=''; for($i=1;$i<=5;$i++) $o.='<span'.($i>$n?' class="empty"':'').'>★</span>'; return $o; };
$hl_lbl    = fn(string $h)=> ucwords(str_replace(['-','_'],[' ',' '],$h));
$hl_cls    = fn(int $i)   => 'rv-hl-'.($i%4);
$mask      = function(?string $name):string{
    $name=trim((string)$name); if($name==='') return 'Guest';
    $first=mb_strtoupper(mb_substr($name,0,1));
    preg_match('/\w/',strrev($name),$m); $last=$m[0]??'*';
    return $first.'****'.$last;
};
$icon_src  = function(?string $icon):string{
    $icon=trim((string)$icon); if($icon==='') return '';
    return str_starts_with($icon,'http') ? $icon : BASE_URL.'/public/uploads/'.$icon;
};
$build_url = function(int $p):string{ $q=$_GET; $q['page']=$p; return '/reviews?'.http_build_query($q); };
$default_avatar = BASE_URL.'/public/uploads/icons/default1.png';

    /* Game icon src */
    $game_icon_src = function(string $g): string {
        $g = strtolower($g);
        if ($g === 'lol') return '/public/assets/website/images/icons/league-of-legends.png';
        if ($g === 'val') return '/public/assets/website/images/icons/valorant.png';
        if ($g === 'tft') return '/public/assets/website/images/icons/teamfight-tactics.png';
        return '';
    };

    /* Rank icon: /core/main/img/lol/ranks/mini/{tier_num}.png */
    $rank_icon_src = function(int $n): string {
        if ($n < 1 || $n > 10) return '';
        return ASSET_URL . '/core/main/img/lol/ranks/mini/' . $n . '.png';
    };

    /* Hero spotlight: newest reviews on this page that carry a real comment (max 5). */
    $rvx_spotlight = [];
    foreach ($reviews_raw as $rvx_r) {
        if (count($rvx_spotlight) >= 5) break;
        if (trim((string)($rvx_r['comments'] ?? '')) !== '') $rvx_spotlight[] = $rvx_r;
    }
    if (empty($rvx_spotlight) && !empty($reviews_raw)) $rvx_spotlight[] = $reviews_raw[0];
?>

<!-- Dynamic top offset: measure actual navbar/banner height -->
<script>
(function(){
    function setTop(){
        var h = 0;
        ['[data-top-banner]','[class*="announcement"]','[class*="top-bar"]','[class*="sale-bar"]',
         '[class*="promo-bar"]','[id*="topBanner"]','[id*="saleBanner"]','nav','header']
        .forEach(function(sel){
            var el = document.querySelector(sel);
            if(el){
                var rect=el.getBoundingClientRect();
                if(rect.bottom>h) h=rect.bottom;
            }
        });
        document.querySelector('.rv-page').style.setProperty('--rv-top',(h+16)+'px');
    }
    document.addEventListener('DOMContentLoaded',setTop);
    window.addEventListener('resize',setTop);
})();
</script>

<div class="rv-page">
<div class="rv-wrap">

    <!-- ══════════ HERO v9 ══════════ -->
    <section class="rvx-hero">
        <div class="rvx-main">

            <!-- Left: statement -->
            <div class="rvx-copy">
                <div class="rv-eyebrow"><i class="fa-solid fa-shield-check"></i> <?= t('Verified reviews from completed orders') ?></div>
                <h1><?= t('See why players trust') ?> <em><?= t('LoLBoost.gg') ?></em></h1>
                <p><?= t('Every review is connected to a real order, so you can judge our boosters by what actually matters: communication, delivery speed, skill and reliability.') ?></p>
            </div>

            <!-- Right: live rotating spotlight -->
            <?php if(!empty($rvx_spotlight)): ?>
            <aside class="rvx-spot" id="rvxSpot" aria-label="<?= t('Latest verified reviews') ?>">
                <div class="rvx-spot-top">
                    <span><?= t('Latest verified reviews') ?></span>
                    <span class="rvx-spot-live"><?= t('Live') ?></span>
                </div>
                <div class="rvx-spot-stage">
                    <?php foreach($rvx_spotlight as $si => $sr):
                        $s_is_seller = !empty($sr['is_seller_review']);
                        $s_is_egirl  = !empty($sr['is_egirl_review']);
                        $s_icon = $icon_src($sr['client_icon']??'')?:$default_avatar;
                        $s_form = trim((string)($sr['form_name']??''));
                    ?>
                    <div class="rvx-spot-item<?= $si===0?' on':'' ?>">
                        <div class="rvx-spot-stars"><?= $stars_html((int)($sr['overall']??5)) ?></div>
                        <p class="rvx-spot-text"><?= htmlspecialchars(trim((string)($sr['comments']??'')) ?: t('Rated without a comment.')) ?></p>
                        <div class="rvx-spot-meta">
                            <div class="rvx-spot-user">
                                <img src="<?= htmlspecialchars($s_icon) ?>" alt="" loading="lazy" onerror="this.src='<?= htmlspecialchars($default_avatar) ?>'">
                                <div style="min-width:0;">
                                    <strong><?= htmlspecialchars($mask($sr['client_username']??'')) ?></strong>
                                    <small><i class="fa-solid fa-circle-check"></i><?= $s_is_seller ? t('Account customer') : ($s_is_egirl ? t('Session customer') : t('Verified order')) ?> · <?= date('j.n.Y',strtotime((string)($sr['created_at']??''))) ?></small>
                                </div>
                            </div>
                            <?php if($s_form!==''): ?><span class="rvx-spot-service" title="<?= htmlspecialchars($s_form) ?>"><?= htmlspecialchars($s_form) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($rvx_spotlight)>1): ?>
                <div class="rvx-spot-nav">
                    <?php foreach($rvx_spotlight as $si => $snav): $n_icon = $icon_src($snav['client_icon']??'')?:$default_avatar; ?>
                    <button class="rvx-spot-dot<?= $si===0?' on':'' ?>" data-idx="<?= $si ?>" aria-label="<?= t('Show review') ?> <?= $si+1 ?>">
                        <img src="<?= htmlspecialchars($n_icon) ?>" alt="" loading="lazy" onerror="this.src='<?= htmlspecialchars($default_avatar) ?>'">
                    </button>
                    <?php endforeach; ?>
                    <span class="rvx-spot-bar" id="rvxSpotBar"><i></i></span>
                </div>
                <?php endif; ?>
            </aside>
            <?php endif; ?>

        </div>

        <!-- Row 2: full-width proof band -->
        <div class="rvx-band">
            <div class="rvx-score">
                <span class="rvx-score-num" data-count="<?= number_format($avg_overall,1,'.','') ?>" data-decimals="1">0.0</span>
                <span class="rvx-score-side">
                    <span class="rvx-score-stars"><?php for($i=1;$i<=5;$i++) echo ($i<=round($avg_overall)?'★':'☆'); ?></span>
                    <span class="rvx-score-lbl"><?= t('Average Rating') ?></span>
                </span>
            </div>
            <div class="rvx-stat">
                <b data-count="<?= (int)$total_reviews ?>">0</b>
                <span><?= t('Total Public Reviews') ?></span>
            </div>
            <div class="rvx-stat rvx-stat--green">
                <b><i data-count="<?= (int)$five_pct ?>">0</i>%</b>
                <span><?= t('5-Star Reviews') ?></span>
            </div>
            <div class="rvx-stat rvx-stat--tp">
                <b>4.9 <span class="rvx-tp-stars"><?php for($i=0;$i<5;$i++) echo '<span>★</span>'; ?></span></b>
                <a href="https://www.trustpilot.com/review/lolboost.gg" target="_blank" rel="noopener">507 <?= t('reviews') ?> · Trustpilot <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
            <div class="rvx-dist" id="rvxDist">
                <?php for($star=5;$star>=1;$star--): $pct=$total_reviews>0 ? round(($dist[$star]??0)/$total_reviews*100) : 0; ?>
                <div class="rvx-dist-row">
                    <b><?= $star ?><i class="fa-solid fa-star"></i></b>
                    <div class="rvx-dist-track"><span class="rvx-dist-fill" data-pct="<?= $pct ?>"></span></div>
                    <small><?= $pct ?>%</small>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Bottom: trust badges -->
        <div class="rvx-badges">
            <div class="rvx-badge rvx-badge--green">
                <i class="fa-solid fa-circle-check"></i>
                <p><strong><?= t('Real completed orders') ?></strong><span><?= t('On-site reviews are collected since 9 January 2026') ?></span></p>
            </div>
            <div class="rvx-badge">
                <i class="fa-solid fa-comments"></i>
                <p><strong><?= t('Communication rated by customers') ?></strong><span><?= t('Feedback goes straight to the booster profile') ?></span></p>
            </div>
            <div class="rvx-badge rvx-badge--gold">
                <i class="fa-solid fa-star"></i>
                <p><strong><?= $five_pct ?>% <?= t('5-Star Reviews') ?></strong><span><?= t('Booster performance feedback') ?></span></p>
            </div>
        </div>
    </section>

    <div class="rv-feed-head">
        <div>
            <div class="rv-feed-kicker"><?= t('Latest verified reviews') ?></div>
            <h2 class="rv-feed-title"><?= t('What customers say after ordering') ?></h2>
        </div>
        <p class="rv-feed-copy"><?= t('Real feedback from completed marketplace purchases, boosting orders and gaming sessions.') ?></p>
    </div>

    <!-- Dynamic review columns -->
    <div class="rv-grid" id="rvGrid">
        <?php if(empty($reviews_raw)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;opacity:.4;"><?= t('No reviews yet.') ?></div>
        <?php else: foreach($reviews_raw as $r):
            $is_egirl  = !empty($r['is_egirl_review']);
            $is_seller = !empty($r['is_seller_review']);
            $game      = strtolower(trim((string)($r['game']??'')));
            $form_name = trim((string)($r['form_name']??''));
            $overall   = (int)($r['overall']??5);
            $comment   = trim((string)($r['comments']??''));
            $booster   = trim((string)($r['booster_name']??''));
            $bst_id    = (int)($r['booster_id']??0);
            $b_icon    = $icon_src($r['booster_icon']??'')?:$default_avatar;
            $c_icon    = $icon_src($r['client_icon']??'')?:$default_avatar;
            $client    = $mask($r['client_username']??'');
            $date_str  = date('j.n.Y',strtotime((string)($r['created_at']??'')));
            $highlights= [];
            if(!empty($r['highlights'])){ $hl=json_decode((string)$r['highlights'],true); if(is_array($hl)) $highlights=$hl; }
            $rank_label='';
            // Every boost form (win, placement, level, coaching, ...) gets the same
            // summary the rest of the site uses, so ranks are no longer limited to
            // orders that happen to have both a start and an end tier.
            $form_type = strtolower(trim((string)($r['form_type'] ?? '')));
            if($form_type!=='' && function_exists('util_format_boost_overview')){
                try{
                    $ov = trim(strip_tags(html_entity_decode((string)util_format_boost_overview($r['game']??'', $form_type, $r), ENT_QUOTES, 'UTF-8')));
                    // The game is already shown in its own tag below, so drop the "EUW - " prefix.
                    $ov = trim((string)preg_replace('/^[A-Za-z0-9 ]{1,24}\s-\s/', '', $ov));
                    $rank_label = trim(str_replace('>', '→', $ov));
                }catch(Throwable $e){ $rank_label=''; }
            }
            if($rank_label===''){
            if(!empty($r['start_tier'])&&!empty($r['end_tier'])){
                $rank_label=$tier_lbl((int)$r['start_tier']).' '.$div_lbl((int)$r['start_division']).' → '.$tier_lbl((int)$r['end_tier']).' '.$div_lbl((int)$r['end_division']);
            } elseif(!empty($r['matches'])){ $rank_label=$r['matches'].' Wins'; }
            elseif(!empty($r['hours'])){ $rank_label=$r['hours'].' Hours'; }
            }
            $account_title = '';
            $account_game_icon = '';
            if ($is_seller && (int)($r['account_id'] ?? 0) > 0) {
                $account_title = trim((string)($r['account_title'] ?? ''));
                $account_game = strtolower(trim((string)($r['account_game'] ?? '')));
                $account_game_icon = function_exists('util_game_icon_url') ? util_game_icon_url($account_game) : '';
            }
        ?>
        <?php
            // 8-colour accent palette — picked by booster_id for consistent colouring per booster
            $accents = [
                ['99,102,241',  '139,92,246'],   // indigo / violet
                ['236,72,153',  '244,114,182'],   // pink / rose
                ['16,185,129',  '52,211,153'],    // emerald / green
                ['245,158,11',  '251,191,36'],    // amber / yellow
                ['59,130,246',  '96,165,250'],    // blue / sky
                ['239,68,68',   '252,165,165'],   // red / rose-light
                ['168,85,247',  '196,181,253'],   // purple / lavender
                ['20,184,166',  '45,212,191'],    // teal / cyan
            ];
            $ac = $accents[$bst_id % 8];
        ?>
        <div class="rv-card" style="--ca:<?= $ac[0] ?>;--ca2:<?= $ac[1] ?>;">

            <!-- Compact reviewer header -->
            <div class="rv-card-header">
                <div class="rv-card-reviewer">
                    <div class="rv-avatar rv-avatar--client">
                        <img src="<?= htmlspecialchars($c_icon) ?>" alt="" loading="lazy" onerror="this.src='<?= htmlspecialchars($default_avatar) ?>'">
                    </div>
                    <div class="rv-card-reviewer-copy">
                        <div class="rv-card-reviewer-name"><?= htmlspecialchars($client) ?></div>
                        <div class="rv-card-reviewer-meta"><?= htmlspecialchars($date_str) ?> · <?= $is_seller ? t('Account customer') : ($is_egirl ? t('Session customer') : t('Verified order')) ?></div>
                    </div>
                </div>
                <span class="rv-card-score"><i class="fa-solid fa-star"></i><?= number_format($overall, 1) ?></span>
            </div>

            <!-- 1. Highlights -->
            <div class="rv-sect-highlights">
                <?php if(!empty($highlights)): foreach($highlights as $idx=>$hl): ?>
                    <span class="rv-hl <?= $hl_cls($idx) ?>"><?= htmlspecialchars($hl_lbl($hl)) ?></span>
                <?php endforeach; endif; ?>
            </div>

            <!-- 2. Comment -->
            <div class="rv-sect-comment">
                <?php if($comment!==''): ?>
                <div class="rv-comment"><?= htmlspecialchars($comment) ?></div>
                <?php else: ?>
                <div class="rv-no-comment"></div>
                <?php endif; ?>
            </div>

            <!-- 3. Rank + Type on same row, then Game alone -->
            <div class="rv-sect-tags">
                <!-- row 1: rank + type side by side -->
                <div class="rv-tag-row">
                    <?php if ($is_seller && $account_title !== ''): ?>
                    <span class="rv-tag rv-tag--account-game" title="<?= htmlspecialchars(function_exists('util_game_display_name') ? util_game_display_name($account_game) : $account_game) ?>">
                        <?php if ($account_game_icon !== ''): ?><img src="<?= htmlspecialchars($account_game_icon) ?>" alt=""><?php else: ?><i class="fa-solid fa-gamepad"></i><?php endif; ?>
                    </span>
                    <span class="rv-tag rv-tag--type rv-tag--account-title" title="<?= htmlspecialchars($account_title) ?>"><span><?= htmlspecialchars($account_title) ?></span></span>
                    <?php else: ?>
                    <?php if($rank_label!==''): ?>
                    <span class="rv-tag rv-tag--rank">
                        <?php if(!empty($r['start_tier'])): $si=$rank_icon_src((int)$r['start_tier']); if($si!==''):?><img src="<?= htmlspecialchars($si) ?>" alt="" style="width:13px;height:13px;object-fit:contain;"><?php endif; endif; ?>
                        <?= htmlspecialchars($rank_label) ?>
                        <?php if(!empty($r['end_tier'])): $ei=$rank_icon_src((int)$r['end_tier']); if($ei!==''):?><img src="<?= htmlspecialchars($ei) ?>" alt="" style="width:13px;height:13px;object-fit:contain;"><?php endif; endif; ?>
                    </span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                <!-- row 2: game alone -->
                <?php if(!$is_seller && $game!==''): $g_icon=$game_icon_src($game); ?>
                <div class="rv-tag-row">
                    <span class="rv-tag <?= $game_cls($game) ?>">
                        <?php if($g_icon!==''):?><img src="<?= htmlspecialchars($g_icon) ?>" alt="" style="width:12px;height:12px;object-fit:contain;"><?php endif; ?>
                        <?= htmlspecialchars($game_lbl($game)) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Divider -->
            <div class="rv-card-divider"></div>

            <!-- Provider footer -->
            <div class="rv-sect-people">
                <?php if($booster!==''): ?>
                <div class="rv-person-pair">
                    <div class="rv-avatar rv-avatar--booster">
                        <img src="<?= htmlspecialchars($b_icon) ?>" alt="" loading="lazy" onerror="this.src='<?= htmlspecialchars($default_avatar) ?>'">
                    </div>
                    <div style="min-width:0;">
                        <?php
                          if ($is_seller) {
                              $rv_profile_url = function_exists('seller_profile_url')
                                  ? seller_profile_url(['slug' => (string)($r['seller_slug'] ?? ''), 'username' => $booster])
                                  : '/sellers';
                          } elseif ($is_egirl) {
                              $rv_profile_url = '/egirls/' . $bst_id;
                          } else {
                              $rv_profile_url = '/boosters/' . $bst_id;
                          }
                        ?>
                        <div class="rv-completed-label"><?= $is_seller ? t('Sold by') : t('Completed by') ?></div>
                        <div class="rv-completed-name"><?php if($bst_id>0):?><a href="<?= htmlspecialchars($rv_profile_url) ?>"><?= htmlspecialchars($booster) ?></a><?php else: ?><?= htmlspecialchars($booster) ?><?php endif; ?></div>
                    </div>
                </div>
                <span class="rv-service-mini"><?= htmlspecialchars($form_name ?: ($is_seller ? 'Account' : 'Verified')) ?></span>
                <?php endif; ?>
                <?php if($booster===''): ?>
                <span class="rv-verified"><i class="fa-solid fa-circle-check"></i> <?= t('Verified') ?></span>
                <?php else: ?>
                <span class="rv-verified" style="display:none;"></span>
                <?php endif; ?>
            </div>

        </div><!-- /.rv-card -->
        <?php endforeach; endif; ?>
    </div>

    <!-- Pagination: JS-driven, URL stays /reviews -->
    <div class="rv-pages" id="rvPages" data-current="<?= $page ?>" data-total="<?= $total_pages ?>">
        <button class="rv-pg" id="rvPrev"><i class="fa-solid fa-chevron-left"></i></button>
        <div id="rvPageNums" style="display:contents;"></div>
        <button class="rv-pg" id="rvNext"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <!-- Monthly Review Timeline -->
    <section class="rv-monthly" aria-label="Monthly review overview">
        <div class="rv-monthly-inner">
            <div class="rv-monthly-copycol">
                <div class="rv-monthly-kicker"><i class="fa-solid fa-chart-column"></i> <?= t('Community Growth') ?></div>
                <h2 class="rv-monthly-title"><?= t('Reviews over time') ?> <em><?= t('since January 2026') ?></em></h2>
                <p class="rv-monthly-copy">
                    <?= t('A monthly overview of verified customer reviews collected since 9 January 2026. This shows how active the LoLBoost.gg community is over time.') ?>
                </p>
                <div class="rv-monthly-mini">
                    <div class="rv-monthly-pill">
                        <strong><?= number_format($rv_month_total) ?></strong>
                        <span><?= t('Since Jan 2026') ?></span>
                    </div>
                    <div class="rv-monthly-pill">
                        <strong><?= number_format($rv_month_avg) ?></strong>
                        <span><?= t('Monthly Avg') ?></span>
                    </div>
                    <div class="rv-monthly-pill">
                        <strong><?= number_format($rv_month_peak) ?></strong>
                        <span><?= htmlspecialchars($rv_month_peak_label ?: t('Peak Month')) ?></span>
                    </div>
                </div>
            </div>

            <div class="rv-monthly-chart" role="img" aria-label="Reviews per month bar chart">
                <?php if(empty($rv_monthly)): ?>
                    <div class="rv-month-empty"><?= t('No monthly review data yet.') ?></div>
                <?php else: ?>
                    <?php
                    $mb_accents = [
                        ['99,102,241', '139,92,246'],
                        ['168,85,247', '196,181,253'],
                        ['236,72,153', '244,114,182'],
                        ['59,130,246', '96,165,250'],
                        ['20,184,166', '45,212,191'],
                        ['16,185,129', '52,211,153'],
                        ['245,158,11', '251,191,36'],
                    ];
                    ?>
                    <?php foreach($rv_monthly as $idx => $m): ?>
                        <?php
                            $cnt = (int)$m['count'];
                            $height = $rv_month_max > 0 ? max(5, round(($cnt / $rv_month_max) * 100)) : 5;
                            $ma = $mb_accents[$idx % count($mb_accents)];
                        ?>
                        <div class="rv-month-bar" style="--mb-ca:<?= $ma[0] ?>;--mb-ca2:<?= $ma[1] ?>;" title="<?= htmlspecialchars($m['label'] . ': ' . $cnt . ' reviews') ?>">
                            <div class="rv-month-bar-fill-wrap">
                                <div class="rv-month-count"><?= number_format($cnt) ?></div>
                                <div class="rv-month-bar-fill" style="height:<?= (int)$height ?>%;"></div>
                            </div>
                            <div class="rv-month-label"><?= htmlspecialchars($m['short']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

</div>
</div>

<?= $this->insert('website/components/get-started', ['variation' => 'three']) ?>

<?= $this->start('scripts') ?>
<script>
(function(){
    /* ══════════ HERO v9: count-up, distribution, spotlight rotation ══════════ */
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Count-up numbers */
    function countUp(el){
        var target = parseFloat(el.dataset.count)||0;
        var decimals = parseInt(el.dataset.decimals)||0;
        if(reduced){ el.textContent = target.toLocaleString(undefined,{minimumFractionDigits:decimals,maximumFractionDigits:decimals}); return; }
        var dur = 1100, start = null;
        function step(ts){
            if(!start) start = ts;
            var p = Math.min((ts-start)/dur, 1);
            var eased = 1 - Math.pow(1-p, 3);
            var val = target * eased;
            el.textContent = val.toLocaleString(undefined,{minimumFractionDigits:decimals,maximumFractionDigits:decimals});
            if(p<1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function initHero(){
        document.querySelectorAll('.rvx-hero [data-count]').forEach(countUp);
        setTimeout(function(){
            document.querySelectorAll('.rvx-dist-fill').forEach(function(f){
                f.style.width = (parseInt(f.dataset.pct)||0) + '%';
            });
        }, 150);
    }
    if(document.readyState !== 'loading') initHero();
    else document.addEventListener('DOMContentLoaded', initHero);

    /* Spotlight rotation */
    var spot = document.getElementById('rvxSpot');
    if(spot){
        var items = spot.querySelectorAll('.rvx-spot-item');
        var dots  = spot.querySelectorAll('.rvx-spot-dot');
        var bar   = document.getElementById('rvxSpotBar');
        var idx = 0, timer = null;

        function runBar(){
            if(!bar || reduced) return;
            bar.classList.remove('run');
            void bar.offsetWidth;
            bar.classList.add('run');
        }
        function show(i){
            idx = (i + items.length) % items.length;
            items.forEach(function(el,k){ el.classList.toggle('on', k===idx); });
            dots.forEach(function(el,k){ el.classList.toggle('on', k===idx); });
        }
        function start(){
            if(reduced || items.length < 2) return;
            stop();
            runBar();
            timer = setInterval(function(){ show(idx+1); runBar(); }, 6000);
        }
        function stop(){
            if(timer){ clearInterval(timer); timer=null; }
            if(bar) bar.classList.remove('run');
        }

        dots.forEach(function(d){
            d.addEventListener('click', function(){ show(parseInt(d.dataset.idx)||0); start(); });
        });
        spot.addEventListener('mouseenter', stop);
        spot.addEventListener('mouseleave', start);
        start();
    }

    /* ══════════ Pagination (AJAX, URL stays /reviews) ══════════ */
    var pagesEl  = document.getElementById('rvPages');
    if(!pagesEl) return;
    var grid     = document.getElementById('rvGrid');
    var prevBtn  = document.getElementById('rvPrev');
    var nextBtn  = document.getElementById('rvNext');
    var numsEl   = document.getElementById('rvPageNums');
    var current  = parseInt(pagesEl.dataset.current)||1;
    var total    = parseInt(pagesEl.dataset.total)||1;

    function renderNums(){
        numsEl.innerHTML='';
        var pages=[];
        pages.push(1);
        for(var i=Math.max(2,current-2);i<=Math.min(total-1,current+2);i++) pages.push(i);
        if(total>1) pages.push(total);
        pages=pages.filter(function(v,i,a){return a.indexOf(v)===i;}).sort(function(a,b){return a-b;});
        var prev=0;
        pages.forEach(function(p){
            if(p-prev>1){var d=document.createElement('span');d.className='rv-pg-dots';d.textContent='…';numsEl.appendChild(d);}
            var btn=document.createElement('button');
            btn.className='rv-pg'+(p===current?' on':'');
            btn.textContent=p;
            btn.addEventListener('click',function(){goTo(p);});
            numsEl.appendChild(btn);
            prev=p;
        });
    }

    function goTo(p){
        if(p<1||p>total) return;
        var params = new URLSearchParams(window.location.search);
        params.set('page', p);

        grid.style.opacity='0'; grid.style.transform='translateY(10px)';
        fetch(window.location.pathname+'?'+params.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.text();})
        .then(function(html){
            var tmp=document.createElement('div');
            tmp.innerHTML=html;
            var newGrid=tmp.querySelector('#rvGrid');
            if(newGrid){
                grid.innerHTML=newGrid.innerHTML;
                // re-trigger animations
                grid.querySelectorAll('.rv-card').forEach(function(c,i){
                    c.style.animationDelay=(i*0.04)+'s';
                    c.style.animation='none';
                    void c.offsetWidth;
                    c.style.animation='rv-card-in .5s ease forwards';
                });
            }
            grid.style.opacity='1'; grid.style.transform='';
            current=p;
            renderNums();
            prevBtn.classList.toggle('off',current<=1);
            nextBtn.classList.toggle('off',current>=total);
            grid.scrollIntoView({behavior:'smooth',block:'start'});
        })
        .catch(function(){
            grid.style.opacity='1'; grid.style.transform='';
        });
    }

    prevBtn.addEventListener('click',function(){ goTo(current-1); });
    nextBtn.addEventListener('click',function(){ goTo(current+1); });

    // init
    renderNums();
    prevBtn.classList.toggle('off',current<=1);
    nextBtn.classList.toggle('off',current>=total);

    // smooth grid opacity transition
    grid.style.transition='opacity .28s ease, transform .28s ease';
})();
</script>
<?= $this->stop() ?>
