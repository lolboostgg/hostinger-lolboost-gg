<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  /* keep your existing helpers */
  .password-toggle { position: relative; }
  .password-toggle .form-control { padding-right: 3rem; }
  .password-toggle-btn {
    position: absolute; top: 50%; right: 0.625rem;
    margin-bottom: 0; padding: 0.5rem;
    transform: translateY(-50%);
    font-size: 1rem; line-height: 1; cursor: pointer;
  }
  .password-toggle-btn .password-toggle-indicator {
    transition: color 0.2s ease-in-out;
    color: #9397ad;
    font-family: "boxicons";
    font-size: 1.25em;
    font-style: normal;
  }
  .password-toggle-btn .password-toggle-indicator::before { content: "\ec0d"; }
  .password-toggle-btn .password-toggle-indicator:hover { color: #33354d; }
  .password-toggle-btn .password-toggle-check {
    position: absolute; left: 0; z-index: -1;
    width: 1rem; height: 1.25rem; opacity: 0;
  }
  .password-toggle-btn .password-toggle-check:checked~.password-toggle-indicator::before { content: "\eb0e"; }

  .avatar-upload { backdrop-filter: blur(5px); cursor: pointer; }

  /* ===== Upload Icon Modal (Client Dashboard / Order View Style) ===== */
  .lb-modal .modal-content{
    background: rgba(255,255,255,.03) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 18px !important;
    overflow: hidden;
    box-shadow: 0 18px 60px rgba(0,0,0,.55);
    position: relative;
  }
  .lb-modal .modal-content::before{
    content:"";
    position:absolute; inset:0;
    padding:1px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(109,92,255,.35), rgba(255,255,255,.06), rgba(176,92,255,.25));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    pointer-events:none;
    opacity: .9;
  }
  .lb-modal .modal-header{
    background: transparent !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    padding: 14px 16px;
  }
  .lb-modal .modal-title{
    color: rgba(255,255,255,.92);
    font-weight: 950;
    letter-spacing: .2px;
    display:flex;
    align-items:center;
    gap: 10px;
  }
  .lb-modal .modal-body{
    padding: 16px;
    color: rgba(255,255,255,.82);
  }
  .lb-modal .modal-footer{
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,.08) !important;
    padding: 12px 16px;
  }
  .lb-modal .btn-close{
    filter: invert(1);
    opacity: .85;
  }
  .lb-modal .btn-close:hover{ opacity: 1; }
  .lb-modal .form-label{
    color: rgba(255,255,255,.75);
    font-weight: 850;
    letter-spacing: .2px;
  }
  .lb-modal .form-control{
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.92) !important;
    border-radius: 14px !important;
  }
  .lb-modal .form-control:focus{
    border-color: rgba(109,92,255,.45) !important;
    box-shadow: 0 0 0 .18rem rgba(109,92,255,.18) !important;
  }
  .lb-modal .lb-file::file-selector-button,
  .lb-modal .lb-file::-webkit-file-upload-button{
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.92);
    border-radius: 12px;
    padding: 8px 12px;
    margin-right: 12px;
    font-weight: 900;
  }
  .lb-modal .lb-file:hover::file-selector-button,
  .lb-modal .lb-file:hover::-webkit-file-upload-button{
    background: rgba(255,255,255,.12);
    border-color: rgba(109,92,255,.45);
    box-shadow: 0 14px 30px rgba(109,92,255,.14);
  }
  .modal-backdrop.show{ opacity: .72; }

  /* ===== Top Boosters (Overview) ===== */

  .booster-card--compact .rank-box{
    width: 44px;
    height: 44px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius: 14px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
  }
  .booster-card--compact .rank_icon{
    width: 34px;
    height: 34px;
    object-fit: contain;
  }

  .booster-card--compact .bottom{
    margin-top: 10px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
  }
  .booster-card--compact .champions{
    display:flex;
    align-items:center;
    gap: 6px;
    min-width: 0;
  }
  .booster-card--compact .champion-icon{
    width: 24px;
    height: 24px;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid rgba(255,255,255,.15);
  }
  .booster-card--compact .more-champions-icon{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    height: 24px;
    min-width: 24px;
    padding: 0 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
  }

  /* Tooltip for "+X" champions */
  /* Floating tooltip (overlays everything, not clipped by cards) */
  .ov-floating-tooltip{
    position: fixed;
    z-index: 999999;
    max-width: min(520px, calc(100vw - 24px));
    background: rgba(16,16,22,.98);
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: 0 18px 60px rgba(0,0,0,.65);
    padding: 10px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 800;
    color: rgba(255,255,255,.94);
    line-height: 1.35;
    display: none;
    pointer-events: auto;
    backdrop-filter: blur(6px);
  }
  .ov-floating-tooltip.show{ display:block; }
  .ov-floating-tooltip .muted{ color: rgba(255,255,255,.75); font-weight: 750; }

  /* Disable the old pseudo-element tooltip (it gets clipped by card overflow) */
  .more-champions-icon[data-tooltip]::before,
  .more-champions-icon[data-tooltip]::after{
    content: none !important;
    display: none !important;
  }


  .more-champions-icon[data-tooltip]{
    position: relative;
    cursor: help;
  }
  .more-champions-icon[data-tooltip]::after{
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%);
    white-space: nowrap;
    max-width: 320px;
    overflow: hidden;
    text-overflow: ellipsis;

    background: rgba(16,16,22,.96);
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: 0 14px 40px rgba(0,0,0,.55);
    padding: 8px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 750;
    color: rgba(255,255,255,.92);

    opacity: 0;
    pointer-events: none;
    transition: opacity .12s ease, transform .12s ease;
    z-index: 40;
  }
  .more-champions-icon[data-tooltip]::before{
    content:"";
    position:absolute;
    left: 50%;
    bottom: calc(100% + 4px);
    transform: translateX(-50%);
    width: 10px;
    height: 10px;
    background: rgba(16,16,22,.96);
    border-left: 1px solid rgba(255,255,255,.10);
    border-top: 1px solid rgba(255,255,255,.10);
    transform: translateX(-50%) rotate(45deg);
    opacity: 0;
    pointer-events:none;
    transition: opacity .12s ease;
    z-index: 39;
  }
  .more-champions-icon[data-tooltip]:hover::after,
  .more-champions-icon[data-tooltip].is-open::after{
    opacity: 1;
    transform: translateX(-50%) translateY(-2px);
  }
  .more-champions-icon[data-tooltip]:hover::before,
  .more-champions-icon[data-tooltip].is-open::before{
    opacity: 1;
  }
  .booster-card--compact .lb-request-btn{
    border-radius: 12px;
    font-weight: 900;
    padding: .35rem .6rem;
    white-space: nowrap;
  }
  .lb-top-boosters-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
  @media (max-width: 576px){
    .lb-top-boosters-grid{ grid-template-columns: 1fr; }
  }
  .lb-top-boosters-grid .cover-link{ text-decoration:none; color:inherit; }

  /* Client Overview: Top Boosters grid (2 cards mobile, 3 cards desktop) */
  .ov-top-boosters-grid{
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
  @media (min-width: 992px){
    .ov-top-boosters-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
  }
  .ov-top-boosters-grid .cover-link{ text-decoration:none; color:inherit; }

  .booster-status-inline{
    display:inline-flex;
    align-items:center;
    gap: 6px;
    font-size: 11px;
    font-weight: 850;
    padding: 4px 8px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.05);
    white-space: nowrap;
  }
  .booster-status-inline .dot{ width: 8px; height: 8px; border-radius: 999px; display:inline-block; }
  .booster-status-inline.online{ border-color: rgba(0,220,150,.22); background: rgba(0,220,150,.10); }
  .booster-status-inline.online .dot{ background: #00dc96; }
  .booster-status-inline.offline{ border-color: rgba(255,90,90,.18); background: rgba(255,90,90,.08); }
  .booster-status-inline.offline .dot{ background: #ff5a5a; }

  .lb-top-boosters-grid .booster-card{
    position:relative;
    border-radius: 18px;
    overflow:hidden;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 14px 36px rgba(0,0,0,.25);
    transition: transform .15s ease, border-color .15s ease;
  }
  .lb-top-boosters-grid .booster-card:hover{
    transform: translateY(-2px);
    border-color: rgba(124,92,255,.35);
  }
  .lb-top-boosters-grid .booster-card .cover{
    height: 78px;
    background-size: cover;
    background-position: center;
    filter: saturate(1.05);
  }
  .lb-top-boosters-grid .booster-card .avatar{
    position: relative;
    width: 58px;
    height: 58px;
    margin-top: -28px;
    margin-left: 14px;
    border-radius: 999px;
    border: 3px solid rgba(10,12,20,.95);
    overflow:hidden;
    background: rgba(255,255,255,.06);
  }
  .lb-top-boosters-grid .booster-card .avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .lb-top-boosters-grid .booster-card .details{
    padding: 10px 14px 14px 14px;
  }
  .lb-top-boosters-grid .booster-card .top{
    display:flex;
    justify-content: space-between;
    align-items:flex-start;
    gap: 10px;
  }
  .lb-top-boosters-grid .booster-card h5{
    margin:0;
    font-size: 0.95rem;
    font-weight: 900;
    color: var(--ov-text);
    line-height: 1.15;
  }
  .lb-top-boosters-grid .booster-card .lb-orders-count{
    display:inline-flex;
    align-items:center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    color: rgba(255,255,255,.9);
    background: rgba(124,92,255,.15);
    border: 1px solid rgba(124,92,255,.22);
    white-space: nowrap;
  }
  .lb-top-boosters-grid .booster-card .rank_icon{ width: 40px; height: 40px; object-fit: contain; opacity: .95; }
  .lb-top-boosters-grid .booster-card .role-icon img{ width: 18px; height: 18px; opacity: .9; }
  .lb-top-boosters-grid .booster-card .mid{ display:flex; align-items:center; gap: 6px; margin-top: 10px; flex-wrap: wrap; }


  /* ===== Overview Top Boosters (Client Overview Grid) ===== */
  .ov-top-boosters-grid .booster-card{
    position:relative;
    border-radius: 18px;
    overflow:hidden;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 14px 36px rgba(0,0,0,.25);
    transition: transform .15s ease, border-color .15s ease;
    height: 100%;
  }
  .ov-top-boosters-grid .booster-card:hover{
    transform: translateY(-2px);
    border-color: rgba(124,92,255,.35);
  }
  .ov-top-boosters-grid .booster-card .cover{
    height: 72px !important;
    background-size: cover;
    background-position: center;
    filter: saturate(1.05);
  }
  .ov-top-boosters-grid .booster-card .avatar{
    position: relative;
    width: 54px;
    height: 54px;
    margin-top: -26px;
    margin-left: 14px;
    border-radius: 999px;
    border: 3px solid rgba(10,12,20,.95);
    overflow:hidden;
    background: rgba(255,255,255,.06);
  }
  .ov-top-boosters-grid .booster-card .avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .ov-top-boosters-grid .booster-card .details{
    padding: 10px 14px 14px 14px;
    display:flex;
    flex-direction: column;
    min-height: 0;
  }
  .ov-top-boosters-grid .booster-card .top{
    display:flex;
    justify-content: space-between;
    align-items:flex-start;
    gap: 10px;
  }
  .ov-top-boosters-grid .booster-card h5{
    margin:0;
    font-size: 0.95rem;
    font-weight: 900;
    color: var(--ov-text);
    line-height: 1.15;
  }
  .ov-top-boosters-grid .booster-card .mid{
    display:flex;
    align-items:center;
    gap: 6px;
    margin-top: 10px;
    flex-wrap: wrap;
  }
  .ov-top-boosters-grid .booster-card .bottom{
    margin-top: auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
  }
  .ov-top-boosters-grid .role-icon img{ width: 18px; height: 18px; opacity: .9; }

  /* Keep cards compact & consistent */
  .ov-top-boosters-grid .booster-card.booster-card--compact{
    min-height: 250px;
  }
  @media (max-width: 576px){
    .ov-top-boosters-grid .booster-card.booster-card--compact{ min-height: 240px; }
  }





/* ===== Top Boosters (Overview) — horizontal scroll (no Swiper) ===== */
/* Native scroll row so cards are FULL width and you can always swipe/drag to the side. */
.ov-top-boosters-wrap{ width: 100%; }

/* Header arrows (scroll controls) */
.ov-top-boosters-head{ gap: 12px; }
.ov-top-boosters-actions{
  display:flex;
  align-items:center;
  gap: 8px;
}


/* Filter toggle (Alle / Online) */
.ov-filter-toggle{  display:flex;  align-items:center;  gap: 6px;  padding: 4px;  border-radius: 14px;  background: rgba(255,255,255,.03);  border: 1px solid rgba(255,255,255,.08);}.ov-filter-btn{  height: 28px;  padding: 0 10px;  border-radius: 10px;  display:inline-flex;  align-items:center;  gap: 7px;  background: transparent;  border: 1px solid transparent;  color: rgba(255,255,255,.78);  font-size: 12px;  font-weight: 900;  letter-spacing: .2px;  transition: background .12s ease, border-color .12s ease, transform .12s ease, color .12s ease;}.ov-filter-btn:hover{  background: rgba(255,255,255,.06);  border-color: rgba(255,255,255,.10);  color: rgba(255,255,255,.92);}.ov-filter-btn.active{  background: rgba(124,92,255,.18);  border-color: rgba(124,92,255,.35);  color: rgba(255,255,255,.95);  box-shadow: 0 10px 28px rgba(124,92,255,.16);}.ov-filter-dot{  width: 8px;  height: 8px;  border-radius: 999px;  background: rgba(255,255,255,.22);  box-shadow: 0 0 0 2px rgba(18,18,24,.75);}.ov-filter-btn[data-ov-filter="online"] .ov-filter-dot{  background: rgba(58,220,138,.95);}.ov-top-boosters-list .ov-booster-card-link[hidden]{ display:none !important; }
.ov-scroll-btn{
  width: 34px;
  height: 34px;
  border-radius: 12px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.85);
  box-shadow: 0 10px 24px rgba(0,0,0,.22);
  transition: transform .12s ease, background .12s ease, border-color .12s ease, opacity .12s ease;
}
.ov-scroll-btn:hover{
  transform: translateY(-1px);
  background: rgba(255,255,255,.07);
  border-color: rgba(124,92,255,.35);
  color: rgba(255,255,255,.95);
}
.ov-scroll-btn:active{ transform: translateY(0); }
.ov-scroll-btn:disabled{
  opacity: .35;
  cursor: not-allowed;
  transform: none;
}
.ov-top-boosters-list{
  display: flex;
  gap: 14px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 2px 2px 10px 2px; /* bottom space for scrollbar on desktop */
  scroll-snap-type: x proximity;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-x: contain;
  /* Firefox */
  scrollbar-width: thin;
  scrollbar-color: rgba(124,92,255,.55) rgba(255,255,255,.06);
  cursor: grab;
  touch-action: pan-x;
}
.ov-top-boosters-list.dragging{ cursor: grabbing; scroll-behavior:auto; }
.ov-top-boosters-list.dragging,
.ov-top-boosters-list.dragging *{ user-select:none; -webkit-user-select:none; }
/* prevent browser image-drag while scrolling */
.ov-top-boosters-list img{ -webkit-user-drag: none; user-drag: none; }


/* WebKit (Chrome/Edge/Safari) scrollbar */
.ov-top-boosters-list::-webkit-scrollbar{ height: 10px; }
.ov-top-boosters-list::-webkit-scrollbar-track{
  background: rgba(255,255,255,.04);
  border-radius: 999px;
}
.ov-top-boosters-list::-webkit-scrollbar-thumb{
  background: linear-gradient(90deg, rgba(124,92,255,.30), rgba(124,92,255,.75));
  border-radius: 999px;
  border: 2px solid rgba(18,18,24,.85); /* gives the pill some breathing room */
}
.ov-top-boosters-list:hover::-webkit-scrollbar-thumb{
  background: linear-gradient(90deg, rgba(124,92,255,.45), rgba(124,92,255,.95));
}
.ov-top-boosters-list .cover-link{ text-decoration:none; color:inherit; display:block; cursor: pointer; outline: none; }

/* card width in the scroll row */
.ov-top-boosters-list .booster-card{
  flex: 0 0 auto;
  width: clamp(260px, 22vw, 360px);
  scroll-snap-align: start;
}
@media (max-width: 768px){
  .ov-top-boosters-list .booster-card{ width: clamp(240px, 78vw, 340px); }
}
@media (max-width: 420px){
  .ov-top-boosters-list .booster-card{ width: 92vw; }
}

/* Card styling for the no-slider layout */
.ov-top-boosters-list .booster-card{
  position:relative;
  border-radius: 18px;
  overflow:hidden;
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.08);
  box-shadow: 0 14px 36px rgba(0,0,0,.25);
  transition: transform .15s ease, border-color .15s ease;
  height: 100%;
}
.ov-top-boosters-list .booster-card:hover{
  transform: translateY(-2px);
  border-color: rgba(124,92,255,.35);
}
.ov-top-boosters-list .booster-card::before{
  content:"";
  position:absolute;
  inset:0;
  padding:1px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(109,92,255,.40), rgba(255,255,255,.06), rgba(176,92,255,.26));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
          mask-composite: exclude;
  pointer-events:none;
  opacity: .55;
}
.ov-top-boosters-list .booster-card::after{
  content:"";
  position:absolute;
  inset:-40px -40px auto -40px;
  height: 120px;
  background: radial-gradient(closest-side, rgba(109,92,255,.20), rgba(109,92,255,0));
  pointer-events:none;
  opacity: .35;
  transition: opacity .18s ease;
}
.ov-top-boosters-list .booster-card:hover::before{ opacity: .85; }
.ov-top-boosters-list .booster-card:hover::after{ opacity: .60; }

/* nicer cover readability */
.ov-top-boosters-list .booster-cover{
  position: relative;
}
.ov-top-boosters-list .booster-cover::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(180deg, rgba(10,12,20,.18), rgba(10,12,20,.55));
  pointer-events:none;
}

/* avatar polish */
.ov-top-boosters-list .avatar{
  box-shadow: 0 10px 26px rgba(0,0,0,.35);
}
.ov-top-boosters-list .avatar::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(109,92,255,.55), rgba(176,92,255,.35), rgba(255,255,255,.12));
  z-index: -1;
  filter: blur(.0px);
  opacity: .55;
}

/* roles row + icons cleaner */
.ov-top-boosters-list .mid{
  margin-top: 12px;
  gap: 8px;
  opacity: .92;
}
.ov-top-boosters-list .role-icon{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width: 26px;
  height: 26px;
  border-radius: 10px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.08);
}
.ov-top-boosters-list .role-icon img{ width: 18px; height: 18px; }

/* request button looks more premium */
.ov-top-boosters-list .lb-request-btn{
  border-radius: 14px;
  font-weight: 950;
  padding: .44rem .75rem;
  border: 1px solid rgba(109,92,255,.32);
  background: linear-gradient(135deg, rgba(109,92,255,.95), rgba(176,92,255,.78));
  box-shadow: 0 16px 34px rgba(109,92,255,.18);
}
.ov-top-boosters-list .lb-request-btn:hover{
  filter: brightness(1.04);
  box-shadow: 0 18px 42px rgba(109,92,255,.26);
}

/* This is the cover header image (fixed height) */
.ov-top-boosters-list .booster-cover{
  height: 72px;
  background-size: cover;
  background-position: center;
  filter: saturate(1.05);
}
/* hard reset in case any global `.cover` styles leak in */
.ov-top-boosters-list .booster-cover{
  padding: 0 !important;
  min-height: 0 !important;
  max-height: 72px !important;
}

.ov-top-boosters-list .avatar{
  position: relative;
  width: 54px;
  height: 54px;
  margin-top: -26px;
  margin-left: 14px;
  border-radius: 999px;
  border: 3px solid rgba(10,12,20,.95);
  overflow: visible;
  z-index: 30;

  background: rgba(255,255,255,.06);
}
.ov-top-boosters-list .avatar img{ width:100%; height:100%; object-fit: cover; border-radius: 999px; display:block; }

.ov-top-boosters-list .details{
  padding: 10px 14px 14px 14px;
  display:flex;
  flex-direction: column;
  min-height: 0;
}
.ov-top-boosters-list .top{
  display:flex;
  justify-content: space-between;
  align-items:flex-start;
  gap: 10px;
}
.ov-top-boosters-list h5{
  margin:0;
  font-size: 0.95rem;
  font-weight: 900;
  color: var(--ov-text);
  line-height: 1.15;
}
.ov-top-boosters-list .ov-booster-meta{
  margin-top: 8px;
  display:flex;
  flex-wrap:wrap;
  gap: 8px;
}
.ov-top-boosters-list .ov-meta-pill{
  display:inline-flex;
  align-items:center;
  gap: 6px;
  padding: 5px 9px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 850;
  line-height: 1;
  color: rgba(255,255,255,.86);
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.10);
  box-shadow: 0 10px 22px rgba(0,0,0,.18);
  white-space: nowrap;
}
.ov-top-boosters-list .ov-meta-pill i{
  font-size: 11px;
  opacity: .85;
}
.ov-top-boosters-list .ov-booster-timezone{
  margin-top: 7px;
  display:inline-flex;
  align-items:center;
  gap: 6px;
  max-width: 100%;
  padding: 5px 9px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 850;
  line-height: 1;
  color: rgba(255,255,255,.72);
  background: rgba(255,255,255,.045);
  border: 1px solid rgba(255,255,255,.09);
}
.ov-top-boosters-list .ov-booster-timezone i{
  color: rgba(129,140,248,.95);
  font-size: 11px;
}
.ov-top-boosters-list .ov-booster-timezone span{
  min-width:0;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.ov-top-boosters-list .mid{
  display:flex;
  align-items:center;
  gap: 6px;
  margin-top: 10px;
  flex-wrap: wrap;
}
.ov-top-boosters-list .bottom{
  margin-top: 22px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
}
.ov-top-boosters-list .role-icon img{ width: 18px; height: 18px; opacity: .9; }

/* keep compact height consistent */
.ov-top-boosters-list .booster-card.booster-card--compact{ min-height: 268px; }
@media (max-width: 576px){
  .ov-top-boosters-list .booster-card.booster-card--compact{ min-height: 252px; }
}





  /* Online badge + dot (Top Boosters) */
  .booster-card .avatar{ position: relative; }
  .booster-online-dot{
    position:absolute;
    z-index: 60;
    right: -2px;
    bottom: -2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255,255,255,.22);
    border: 3px solid rgba(10,12,20,.98);
    box-shadow: 0 0 0 2px rgba(255,255,255,.12), 0 10px 22px rgba(0,0,0,.35);
}
  .booster-online-dot.online{
    background:#35d07f;
    box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 0 0 0 rgba(53,208,127,.55);
    animation: boosterOnlinePulse 1.6s ease-out infinite;
  }
  .booster-online-badge{
    display:inline-flex;
    align-items:center;
    gap: 10px;
    margin-top: 8px;
    padding: 7px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    width: fit-content;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.72);
  }
  .booster-online-badge .dot{
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.22);
    border: 2px solid rgba(10,12,20,.95);
    box-shadow: 0 0 0 1px rgba(255,255,255,.10);
  }
  .booster-online-badge.online{
    color: rgba(53,208,127,.98);
    border-color: rgba(53,208,127,.25);
    background: rgba(53,208,127,.10);
    box-shadow: 0 10px 26px rgba(53,208,127,.08);
    animation: boosterBadgeGlow 2.2s ease-in-out infinite;
  }
  .booster-online-badge.online .dot{
    background:#35d07f;
    box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 0 0 0 rgba(53,208,127,.55);
    animation: boosterOnlinePulse 1.6s ease-out infinite;
  }
  @keyframes boosterOnlinePulse{
    0%{ box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 0 0 0 rgba(53,208,127,.55); }
    70%{ box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 0 0 12px rgba(53,208,127,0); }
    100%{ box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 0 0 0 rgba(53,208,127,0); }
  }
  @keyframes boosterBadgeGlow{
    0%,100%{ transform: translateY(0); }
    50%{ transform: translateY(-1px); }
  }

  /* ===== Fix: Avatar status dot above everything (keep filter pill dot visible) ===== */
  /* ensure avatar dot sits on top of the avatar image (and is not clipped/hidden) */
.booster-card .avatar{ position: relative; overflow: visible; z-index: 30; }
.booster-card .avatar img{ position: relative; z-index: 1; display:block; border-radius: 999px; }
.booster-online-dot{
  right: -2px !important;
  bottom: -2px !important;
  z-index: 60 !important;
  pointer-events: none;
}

</style>
<?= $this->stop() ?>

<style>
  [data-theme="light"] .text-white { color: #1e2022 !important; }
  [data-theme="light"] .wallet-points { color: #fff !important; }

  /* ===== Overview v4 — tighter + richer sidebar card (scoped) ===== */
  .client-overview-v4{
    --ov-surface: rgba(255,255,255,.03);
    --ov-surface-2: rgba(255,255,255,.02);
    --ov-border: rgba(255,255,255,.08);
    --ov-text: rgba(255,255,255,.92);
    --ov-muted: rgba(255,255,255,.62);
    --ov-accent: #6d5cff;
    --ov-accent-2: #b05cff;
    --ov-glow: rgba(109,92,255,.22);
  }

  .client-overview-v4 .card{
    background: var(--ov-surface) !important;
    border: 1px solid var(--ov-border) !important;
    border-radius: 18px !important;
    overflow: hidden;
    position: relative;
    box-shadow: 0 14px 40px rgba(0,0,0,.25);
  }

  .client-overview-v4 .card::before{
    content:"";
    position:absolute; inset:0;
    padding:1px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(109,92,255,.35), rgba(255,255,255,.06), rgba(176,92,255,.25));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    pointer-events:none;
    opacity: .85;
  }

  .client-overview-v4 .card:hover{
    box-shadow: 0 18px 55px rgba(0,0,0,.35);
    transform: translateY(-1px);
    transition: .18s ease;
  }

  .client-overview-v4 .card-header{
    background: transparent !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    padding: 14px 16px;
  }

  .client-overview-v4 .card-body{ padding: 16px; }
  .client-overview-v4 .ov-title{ font-weight: 850; letter-spacing: .2px; margin: 0; color: var(--ov-text); }
  .client-overview-v4 .ov-muted{ color: var(--ov-muted) !important; }
  .client-overview-v4 .ov-link{ color: rgba(255,255,255,.82); text-decoration: none; }
  .client-overview-v4 .ov-link:hover{ color: #fff; }

  .client-overview-v4 .ov-chip{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.10);
    color: var(--ov-text);
    font-weight: 800;
    font-size: .85rem;
    white-space: nowrap;
  }

  .client-overview-v4 .ov-chip.ov-chip-accent{
    background: linear-gradient(135deg, rgba(109,92,255,.22), rgba(176,92,255,.14));
    border-color: rgba(109,92,255,.35);
    box-shadow: 0 10px 26px rgba(109,92,255,.14);
  }

  .client-overview-v4 .ov-divider{
    height: 1px;
    background: rgba(255,255,255,.06);
    margin: 14px 0;
  }

  /* avatar/edit */
  .client-overview-v4 .avatar { position: relative; }
  .client-overview-v4 .edit-icon-container{
    width: 32px; height: 32px;
    background-color: rgba(53,56,58,.92);
    border-radius: 50%;
    display:flex; align-items:center; justify-content:center;
    position:absolute; bottom: 6px; right: 6px;
    cursor:pointer; border:none; outline:none; padding:0;
    box-shadow: 0 10px 22px rgba(0,0,0,.35);
  }
  .client-overview-v4 .edit-icon-container i{ color: #fff; font-size: .9rem; }

  .client-overview-v4 .profile-mini{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 16px;
  }

  .client-overview-v4 .profile-mini .left{
    display:flex; align-items:center; gap: 14px;
    min-width: 0;
  }

  .client-overview-v4 .profile-mini .name{
    font-size: 1.2rem;
    font-weight: 950;
    color: var(--ov-text);
    line-height: 1.1;
    margin: 0;
  }

  .client-overview-v4 .profile-mini .email{
    margin: 4px 0 0 0;
    color: var(--ov-muted);
    font-size: .9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
  }

  /* sidebar mini stats */
  .client-overview-v4 .ov-mini-grid{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .client-overview-v4 .ov-mini{
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 10px 10px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
  }
  .client-overview-v4 .ov-mini .k{
    color: var(--ov-muted);
    font-weight: 750;
    font-size: .85rem;
    display:flex;
    align-items:center;
    gap: 8px;
  }
  .client-overview-v4 .ov-mini .k i{
    width: 30px; height: 30px;
    border-radius: 12px;
    display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg, rgba(109,92,255,.24), rgba(176,92,255,.14));
    border: 1px solid rgba(109,92,255,.25);
    box-shadow: 0 10px 20px rgba(109,92,255,.08);
    color: rgba(255,255,255,.9);
  }
  .client-overview-v4 .ov-mini .v{
    color: var(--ov-text);
    font-weight: 950;
    font-size: .95rem;
    white-space: nowrap;
  }

  /* stat tiles on welcome card */
  .client-overview-v4 .ov-stat{
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 12px 12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
    min-height: 72px !important;
  }
  .client-overview-v4 .ov-stat:hover{
    border-color: rgba(109,92,255,.35);
    box-shadow: 0 14px 28px rgba(109,92,255,.08);
  }
  .client-overview-v4 .ov-stat .left{
    display:flex; align-items:center; gap: 10px;
    min-width: 0;
  }
  .client-overview-v4 .ov-stat .left i{
    width: 34px; height: 34px;
    border-radius: 12px;
    display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg, rgba(109,92,255,.28), rgba(176,92,255,.16));
    border: 1px solid rgba(109,92,255,.25);
    box-shadow: 0 10px 22px rgba(109,92,255,.10);
    color: rgba(255,255,255,.92);
  }
  .client-overview-v4 .ov-stat .label{
    font-weight: 900;
    color: var(--ov-text);
    font-size: .95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .client-overview-v4 .ov-stat .hint{
    font-size: .85rem;
    color: var(--ov-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .client-overview-v4 .ov-stat .value{
    font-weight: 950;
    color: var(--ov-text);
    white-space: nowrap;
  }

  /* Progress */
  .client-overview-v4 .ov-progress{
    height: 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    overflow:hidden;
    position: relative;
  }
  .client-overview-v4 .ov-progress > div{
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--ov-accent), var(--ov-accent-2));
    box-shadow: 0 10px 25px var(--ov-glow);
  }

  /* Private Boosting Report Notice */
  .client-overview-v4 .ov-report{
    margin-top: 14px;
    padding: 14px 14px;
    border-radius: 18px;
    background: rgba(255,70,70,.06);
    border: 1px solid rgba(255,70,70,.22);
    position: relative;
    overflow: hidden;
  }
  .client-overview-v4 .ov-report::before{
    content:"";
    position:absolute; inset:-2px;
    background: radial-gradient(800px 120px at 20% 0%, rgba(255,70,70,.28), transparent 60%),
                radial-gradient(600px 140px at 80% 100%, rgba(109,92,255,.22), transparent 55%);
    opacity: .9;
    pointer-events:none;
  }
  .client-overview-v4 .ov-report .inner{ position: relative; display:flex; gap: 12px; align-items:flex-start; }
  .client-overview-v4 .ov-report .ico{
    width: 40px; height: 40px; flex: 0 0 40px;
    border-radius: 14px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(255,70,70,.12);
    border: 1px solid rgba(255,70,70,.28);
    box-shadow: 0 14px 32px rgba(255,70,70,.10);
    color: rgba(255,255,255,.94);
  }
  .client-overview-v4 .ov-report .title{
    font-weight: 950;
    letter-spacing: .2px;
    color: rgba(255,255,255,.94);
    margin: 0 0 2px 0;
    line-height: 1.2;
  }
  .client-overview-v4 .ov-report .text{
    color: rgba(255,255,255,.78);
    font-weight: 750;
    margin: 0;
    line-height: 1.35;
  }
  .client-overview-v4 .ov-report .text b{ color: rgba(255,255,255,.92); }
  .client-overview-v4 .ov-report .actions{ margin-top: 10px; display:flex; gap: 10px; flex-wrap: wrap; }
  .client-overview-v4 .ov-report .btn-report{
    border-radius: 14px;
    font-weight: 950;
    box-shadow: 0 14px 34px rgba(255,70,70,.12);
  }
  .client-overview-v4 .ov-report .btn-report.btn-primary{
    background: linear-gradient(135deg, rgba(255,70,70,.92), rgba(176,92,255,.78));
    border: 0 !important;
  }
  .client-overview-v4 .ov-report .btn-report.btn-outline-light{
    border-color: rgba(255,255,255,.16) !important;
    background: rgba(255,255,255,.03);
  }
  @media (prefers-reduced-motion: no-preference){
    .client-overview-v4 .ov-report{
      animation: ovPulse 2.2s ease-in-out infinite;
    }
    @keyframes ovPulse{
      0%,100%{ transform: translateY(0); }
      50%{ transform: translateY(-1px); }
    }
  }

  /* Orders list */
  .client-overview-v4 .ov-order-list{ display:flex; flex-direction:column; gap: 10px; }
  .client-overview-v4 .ov-order-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 12px;
    padding: 12px 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
  }
  .client-overview-v4 .ov-order-row:hover{
    background: rgba(255,255,255,.035);
    border-color: rgba(109,92,255,.25);
    box-shadow: 0 12px 26px rgba(109,92,255,.06);
  }
  .client-overview-v4 .ov-order-left{
    display:flex; align-items:center;
    min-width: 0;
  }
  .client-overview-v4 .ov-order-left a{
    color: var(--ov-text);
    text-decoration:none;
    font-weight: 900;
    display:inline-block;
    min-width: 0;
  }
  .client-overview-v4 .ov-order-right{
    display:flex;
    align-items:center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .client-overview-v4 .badge{
    border-radius: 999px !important;
    padding: 6px 10px;
    font-weight: 900;
  }

  /* smaller pay button in recent orders */
  .client-overview-v4 .ov-pay-btn{
    padding: 7px 12px !important;
    font-weight: 950 !important;
    border-radius: 12px !important;
    white-space: nowrap;
  }


  /* unpaid badge with pay icon (Recent Orders) */
  .client-overview-v4 .ov-unpaid-badge{
    display:inline-flex;
    align-items:center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 950;
    font-size: .85rem;
    line-height: 1;
    color: rgba(255,255,255,.92);
    background: rgba(255, 82, 163, .12);
    border: 1px solid rgba(255, 82, 163, .32);
    box-shadow: 0 10px 24px rgba(255, 82, 163, .10);
    white-space: nowrap;
  }
  .client-overview-v4 .ov-unpaid-badge i{
    font-size: .95rem;
  }
  .client-overview-v4 .ov-recent-status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;font-weight:950;font-size:.72rem;line-height:1;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;border:1px solid rgba(255,255,255,.1)}
  .client-overview-v4 .ov-recent-status i{font-size:.72rem}
  .client-overview-v4 .ov-recent-status.unpaid{color:#fb7185;background:rgba(251,113,133,.1);border-color:rgba(251,113,133,.22)}
  .client-overview-v4 .ov-recent-status.paid{color:#60a5fa;background:rgba(96,165,250,.1);border-color:rgba(96,165,250,.22)}
  .client-overview-v4 .ov-recent-status.processing{color:#a78bfa;background:rgba(167,139,250,.1);border-color:rgba(167,139,250,.22)}
  .client-overview-v4 .ov-recent-status.inprogress{color:#93c5fd;background:rgba(147,197,253,.1);border-color:rgba(147,197,253,.2)}
  .client-overview-v4 .ov-recent-status.paused{color:#fbbf24;background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.22)}
  .client-overview-v4 .ov-recent-status.delivered{color:#2dd4bf;background:rgba(45,212,191,.1);border-color:rgba(45,212,191,.22)}
  .client-overview-v4 .ov-recent-status.completed{color:#4ade80;background:rgba(74,222,128,.1);border-color:rgba(74,222,128,.2)}
  .client-overview-v4 .ov-recent-status.refunded{color:#fb923c;background:rgba(251,146,60,.1);border-color:rgba(251,146,60,.22)}
  .client-overview-v4 .ov-recent-status.cancelled{color:#94a3b8;background:rgba(148,163,184,.1);border-color:rgba(148,163,184,.22)}
  .client-overview-v4 .ov-recent-status.failed{color:#ef4444;background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.22)}
  .client-overview-v4 .ov-recent-status.disputed{color:#f472b6;background:rgba(244,114,182,.1);border-color:rgba(244,114,182,.22)}
  .client-overview-v4 .ov-recent-status.chargeback{color:#e11d48;background:rgba(225,29,72,.1);border-color:rgba(225,29,72,.25)}




  /* ===== LB Rewards Lootbox Widget (Client Overview) ===== */
  .client-overview-v4 .ov-lootbox-card{
    position: relative;
    overflow: hidden;
    border-radius: 22px !important;
    background: radial-gradient(520px 220px at 82% 18%, rgba(124,92,255,.28), transparent 62%),
                linear-gradient(135deg, rgba(255,255,255,.045), rgba(255,255,255,.018)) !important;
  }
  .client-overview-v4 .ov-lootbox-card::after{
    content:"";
    position:absolute;
    inset:auto -90px -120px auto;
    width: 310px;
    height: 260px;
    background: radial-gradient(closest-side, rgba(129,140,248,.24), transparent 70%);
    pointer-events:none;
    opacity:.75;
  }
  .client-overview-v4 .ov-lootbox-inner{
    position: relative;
    z-index: 2;
    display:grid;
    grid-template-columns: minmax(0, 1fr) 170px;
    gap: 16px;
    align-items:center;
  }
  .client-overview-v4 .ov-lootbox-kicker{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-bottom:8px;
    color:#93b4ff;
    font-size:.76rem;
    font-weight:950;
    letter-spacing:.12em;
    text-transform:uppercase;
  }
  .client-overview-v4 .ov-lootbox-title{
    margin:0 0 6px;
    color:var(--ov-text);
    font-size:1.35rem;
    font-weight:950;
    letter-spacing:-.02em;
    line-height:1.1;
  }
  .client-overview-v4 .ov-lootbox-text{
    color:var(--ov-muted);
    font-size:.92rem;
    line-height:1.45;
    font-weight:750;
    margin:0;
    max-width:720px;
  }
  .client-overview-v4 .ov-lootbox-actions{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-top:14px;
  }
  .client-overview-v4 .ov-lootbox-balance{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:9px 12px;
    border-radius:999px;
    background:rgba(124,92,255,.16);
    border:1px solid rgba(124,92,255,.34);
    color:rgba(255,255,255,.94);
    font-weight:950;
    box-shadow:0 14px 32px rgba(124,92,255,.10);
  }
  .client-overview-v4 .ov-lootbox-balance img{
    width:20px;
    height:20px;
    object-fit:contain;
  }
  .client-overview-v4 .ov-daily-gift-btn.is-waiting{
    background: rgba(255,255,255,.065) !important;
    border-color: rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.74) !important;
    box-shadow: none !important;
    cursor: default;
  }
  .client-overview-v4 .ov-daily-gift-btn.is-waiting:hover{
    filter: none !important;
    transform: none !important;
    color: rgba(255,255,255,.78) !important;
  }
  .client-overview-v4 .ov-daily-gift-btn .ov-countdown{
    font-variant-numeric: tabular-nums;
    font-feature-settings: 'tnum';
    letter-spacing: .02em;
  }
  .client-overview-v4 .ov-lootbox-art{
    position:relative;
    min-height:150px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .client-overview-v4 .ov-lootbox-art::before{
    content:"";
    position:absolute;
    width:150px;
    height:150px;
    border-radius:42px;
    background:linear-gradient(135deg, rgba(124,92,255,.22), rgba(255,255,255,.045));
    border:1px solid rgba(255,255,255,.08);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.06), 0 22px 54px rgba(0,0,0,.22);
    transform:rotate(3deg);
  }
  .client-overview-v4 .ov-lootbox-art img{
    position:relative;
    z-index:2;
    width:150px;
    height:150px;
    object-fit:contain;
    filter:drop-shadow(0 18px 34px rgba(124,92,255,.26));
    transform:translateY(-2px);
  }
  .client-overview-v4 .ov-lootbox-minirow{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:10px;
    margin-top:14px;
  }
  .client-overview-v4 .ov-lootbox-mini{
    display:flex;
    align-items:center;
    gap:8px;
    min-width:0;
    padding:9px 10px;
    border-radius:14px;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.07);
    color:rgba(255,255,255,.82);
    font-size:.82rem;
    font-weight:850;
  }
  .client-overview-v4 .ov-lootbox-mini i{
    color:#93b4ff;
  }
  .client-overview-v4 .ov-sidebar-lootbox{
    display:flex;
    align-items:center;
    gap:13px;
  }
  .client-overview-v4 .ov-sidebar-lootbox img{
    width:72px;
    height:72px;
    object-fit:contain;
    border-radius:20px;
    background:linear-gradient(135deg, rgba(124,92,255,.18), rgba(255,255,255,.035));
    border:1px solid rgba(255,255,255,.08);
    padding:8px;
    filter:drop-shadow(0 14px 26px rgba(124,92,255,.18));
  }
  @media (max-width: 768px){
    .client-overview-v4 .ov-lootbox-inner{ grid-template-columns:1fr; }
    .client-overview-v4 .ov-lootbox-art{ min-height:110px; justify-content:flex-start; }
    .client-overview-v4 .ov-lootbox-art::before{ width:110px; height:110px; border-radius:30px; }
    .client-overview-v4 .ov-lootbox-art img{ width:116px; height:116px; }
    .client-overview-v4 .ov-lootbox-minirow{ grid-template-columns:1fr; }
  }

  /* Rewards */
  .client-overview-v4 .ov-reward-list{ display:flex; flex-direction:column; gap: 8px; }
  .client-overview-v4 .ov-reward-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
  }
  .client-overview-v4 .ov-reward-item:hover{
    border-color: rgba(109,92,255,.22);
    background: rgba(255,255,255,.028);
  }
  .client-overview-v4 .ov-reward-item .left{
    display:flex; align-items:center; gap: 10px;
    min-width: 0;
  }
  .client-overview-v4 .ov-reward-item .left span{
    font-weight: 900;
    color: var(--ov-text);
  }
  .client-overview-v4 .ov-reward-item .right{
    font-size: .85rem;
    color: var(--ov-muted);
    white-space: nowrap;
  }

  .client-overview-v4 .btn{
    border-radius: 12px !important;
    font-weight: 900;
  }
  .client-overview-v4 .btn-primary{
    background: #5b4bff !important;
    border-color: #5b4bff !important;
    box-shadow: 0 14px 32px rgba(91,75,255,.20);
  }

  .client-overview-v4 .ov-section-gap{ margin-top: 12px; }

  @media (max-width: 768px){
    .client-overview-v4 .profile-mini{ flex-direction: column; align-items:flex-start; }
    .client-overview-v4 .profile-mini .email{ max-width: 100%; }
    .client-overview-v4 .ov-mini-grid{ grid-template-columns: 1fr; }
  }
  @media (max-width: 576px){
    .client-overview-v4 .container-fluid{ padding-left: 0 !important; padding-right: 0 !important; }
  }

  /* Brighter outline buttons + hover */
  .client-overview-v4 .btn-outline-light{
    color: rgba(255,255,255,.92) !important;
    background: rgba(255,255,255,.04) !important;
    border-color: rgba(255,255,255,.18) !important;
  }
  .client-overview-v4 .btn-outline-light:hover,
  .client-overview-v4 .btn-outline-light:focus{
    color: #fff !important;
    background: rgba(255,255,255,.08) !important;
    border-color: rgba(109,92,255,.45) !important;
    box-shadow: 0 14px 30px rgba(109,92,255,.14);
    transform: translateY(-1px);
  }
  .client-overview-v4 .btn-outline-light:active{ transform: translateY(0px); }
  .client-overview-v4 .btn.btn-sm.btn-outline-light{
    padding: 6px 10px;
    font-weight: 900;
  }
  .client-overview-v4 .ov-link{ color: rgba(255,255,255,.86); }
  .client-overview-v4 .ov-link:hover{
    color: #fff;
    text-shadow: 0 10px 24px rgba(109,92,255,.18);
  }

  /* Align chips row */
  .client-overview-v4 .ov-chips-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
    flex-wrap: wrap;
  }
  .client-overview-v4 .ov-chips-row .ov-chip{
    line-height: 1;
    padding-top: 7px;
    padding-bottom: 7px;
  }

  /* Trustpilot stars */
  .client-overview-v4 .tp-stars svg{ cursor:pointer; transition: .2s; }

  /* (rest of your modal force styles kept as-is below) */
  #upload-icon-modal .lb-modal-dialog{ max-width: 520px; }
  #upload-icon-modal .lb-modal-content{
    background: rgba(255,255,255,.03) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    box-shadow: 0 18px 60px rgba(0,0,0,.55) !important;
    position: relative !important;
  }
  #upload-icon-modal .lb-modal-content::before{
    content:"" !important;
    position:absolute !important; inset:0 !important;
    padding:1px !important;
    border-radius: 18px !important;
    background: linear-gradient(135deg, rgba(109,92,255,.35), rgba(255,255,255,.06), rgba(176,92,255,.25)) !important;
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0) !important;
    -webkit-mask-composite: xor !important;
            mask-composite: exclude !important;
    pointer-events:none !important;
    opacity: .9 !important;
  }
  #upload-icon-modal .lb-modal-header{
    background: transparent !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    padding: 14px 16px !important;
  }
  #upload-icon-modal .modal-title{
    color: rgba(255,255,255,.92) !important;
    font-weight: 950 !important;
    letter-spacing: .2px !important;
    display:flex !important;
    align-items:center !important;
    gap: 10px !important;
  }
  #upload-icon-modal .lb-modal-body{
    padding: 16px !important;
    color: rgba(255,255,255,.82) !important;
  }
  #upload-icon-modal .lb-modal-footer{
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,.08) !important;
    padding: 12px 16px !important;
  }
  #upload-icon-modal .btn-close{
    filter: invert(1) !important;
    opacity: .85 !important;
  }
  #upload-icon-modal .btn-close:hover{ opacity: 1 !important; }
  #upload-icon-modal .form-label{
    color: rgba(255,255,255,.75) !important;
    font-weight: 850 !important;
  }
  #upload-icon-modal .form-control{
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: rgba(255,255,255,.92) !important;
    border-radius: 14px !important;
  }
  #upload-icon-modal .form-control:focus{
    border-color: rgba(109,92,255,.45) !important;
    box-shadow: 0 0 0 .18rem rgba(109,92,255,.18) !important;
  }
  #upload-icon-modal .lb-file::file-selector-button,
  #upload-icon-modal .lb-file::-webkit-file-upload-button{
    background: rgba(255,255,255,.08) !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    color: rgba(255,255,255,.92) !important;
    border-radius: 12px !important;
    padding: 8px 12px !important;
    margin-right: 12px !important;
    font-weight: 900 !important;
  }
  #upload-icon-modal .lb-file:hover::file-selector-button,
  #upload-icon-modal .lb-file:hover::-webkit-file-upload-button{
    background: rgba(255,255,255,.12) !important;
    border-color: rgba(109,92,255,.45) !important;
    box-shadow: 0 14px 30px rgba(109,92,255,.14) !important;
  }
  #upload-icon-modal .lb-modal-hint{
    color: rgba(255,255,255,.60) !important;
    font-weight: 800 !important;
    font-size:.9rem !important;
  }
  .modal-backdrop.show{ opacity: .72 !important; }
</style>

<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>

<style>
  /* Overview tweaks: clickable Recent Orders rows + meta line */
  .client-overview-v4 .ov-order-row.ov-clickable{ cursor: pointer; }
  @media (prefers-reduced-motion: no-preference){
    .client-overview-v4 .ov-order-row.ov-clickable{
      transition: transform .14s ease, background .14s ease, border-color .14s ease, box-shadow .14s ease;
    }
    .client-overview-v4 .ov-order-row.ov-clickable:hover{ transform: translateY(-1px); }
  }
  .client-overview-v4 .ov-order-meta{
    color: rgba(255,255,255,.55);
    font-weight: 800;
    font-size: 11.5px;
    line-height: 1.25;
    margin-top: 2px;
  }
  .client-overview-v4 .ov-order-left .avatar{ position: relative; overflow: visible; }
  .client-overview-v4 .ov-order-game-badge{
    position: absolute;
    right: -4px;
    bottom: -4px;
    width: 17px;
    height: 17px;
    min-width: 17px;
    padding: 0;
    border-radius: 50%;
    border: 2px solid #1b1d1f;
    background: #1b1d1f;
    object-fit: cover;
    box-shadow: 0 6px 14px rgba(0,0,0,.45);
  }
  /* Non-boost order icons (item, digital good, top up) must stay inside the avatar */
  .client-overview-v4 .ov-order-left .avatar-initials{
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
    height:100%;
    overflow:hidden;
  }
  .client-overview-v4 .ov-order-type-img{
    width:22px;
    height:22px;
    max-width:22px;
    max-height:22px;
    object-fit:contain;
    border-radius:6px;
  }
  .client-overview-v4 .ov-order-meta .ov-oid{
    margin-left: 7px;
    opacity: .8;
    font-weight: 950;
    letter-spacing: .2px;
    color: rgba(255,255,255,.65);
  }

  /* #4 — Progress milestone markers */
  .client-overview-v4 .ov-progress-milestones{
    position: relative;
    height: 32px;
    margin-top: 4px;
    margin-bottom: 6px;
  }
  .client-overview-v4 .ov-ms-item{
    position: absolute;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
  }
  .client-overview-v4 .ov-ms-dot{
    width: 6px; height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,.16);
    border: 1.5px solid rgba(255,255,255,.12);
    transition: background .2s, box-shadow .2s;
  }
  .client-overview-v4 .ov-ms-item.done .ov-ms-dot{
    background: var(--ov-accent);
    border-color: var(--ov-accent);
    box-shadow: 0 0 7px rgba(109,92,255,.55);
  }
  .client-overview-v4 .ov-ms-item.current .ov-ms-dot{
    background: var(--ov-accent-2);
    border-color: var(--ov-accent-2);
    width: 9px; height: 9px;
    box-shadow: 0 0 10px rgba(176,92,255,.7);
  }
  .client-overview-v4 .ov-ms-label{
    font-size: 9px;
    font-weight: 800;
    color: rgba(255,255,255,.28);
    text-transform: capitalize;
    letter-spacing: .2px;
    white-space: nowrap;
  }
  .client-overview-v4 .ov-ms-item.done .ov-ms-label{ color: rgba(255,255,255,.52); }
  .client-overview-v4 .ov-ms-item.current .ov-ms-label{ color: var(--ov-accent-2); font-weight: 950; }

  /* Dismiss button removed - alert is always visible */

  /* #6 — Empty orders state */
  .client-overview-v4 .ov-empty-state{
    text-align: center;
    padding: 28px 16px;
  }
  .client-overview-v4 .ov-empty-ico{
    width: 52px; height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(109,92,255,.20), rgba(176,92,255,.12));
    border: 1px solid rgba(109,92,255,.22);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 20px;
    margin-bottom: 12px;
  }
  .client-overview-v4 .ov-empty-title{
    font-size: 1.05rem;
    font-weight: 950;
    color: var(--ov-text);
    margin-bottom: 5px;
  }
  .client-overview-v4 .ov-empty-sub{
    font-size: .88rem;
    color: var(--ov-muted);
    font-weight: 500;
  }

  /* #7 — Online boosters empty state */
  .client-overview-v4 .ov-boosters-empty-online{
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    color: var(--ov-muted);
    font-size: .88rem;
    font-weight: 700;
    margin-top: 8px;
  }
  .client-overview-v4 .ov-boosters-empty-online i{ font-size: 16px; flex-shrink: 0; }

  /* #9 — Coins examples row */
  .client-overview-v4 .ov-coins-examples{
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
  }
  .client-overview-v4 .ov-coins-examples span{
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 800;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.09);
    color: rgba(255,255,255,.75);
  }

  /* #10 — Trustpilot prompt text */
  .client-overview-v4 .ov-tp-prompt{
    font-size: .9rem;
    font-weight: 800;
    color: var(--ov-text);
  }

  /* #13 — Next rank teaser section */
  .client-overview-v4 .ov-next-rank-teaser{
    padding: 12px 14px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px dashed rgba(255,255,255,.10);
  }
  .client-overview-v4 .ov-nrt-label{
    font-size: .82rem;
    font-weight: 800;
    color: var(--ov-muted);
    display: flex;
    align-items: center;
    gap: 7px;
  }
  .client-overview-v4 .ov-reward-locked{
    opacity: .7;
  }
  .client-overview-v4 .ov-reward-locked .left span{
    color: var(--ov-muted) !important;
  }

  /* #15 — keyboard focus ring on booster scroll */
  .client-overview-v4 .ov-top-boosters-list:focus{
    outline: 2px solid rgba(109,92,255,.55);
    outline-offset: 2px;
    border-radius: 14px;
  }
  .client-overview-v4 .ov-top-boosters-list:focus-visible{
    outline: 2px solid rgba(109,92,255,.7);
  }



  /* ===== World Cup 2026 Prediction Contest Widget, Overview Native Style ===== */
  .client-overview-v4 .ov-wc-card{
    margin-bottom: 14px;
    border-radius: 18px !important;
    overflow: hidden;
    background: rgba(255,255,255,.03) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    box-shadow: 0 14px 40px rgba(0,0,0,.25);
  }
  .client-overview-v4 .ov-wc-card::after{
    content:"";
    position:absolute;
    inset:-80px -120px auto auto;
    width: 320px;
    height: 180px;
    background: radial-gradient(closest-side, rgba(109,92,255,.16), rgba(109,92,255,0));
    pointer-events:none;
    opacity:.7;
  }
  .client-overview-v4 .ov-wc-hero{
    position: relative;
    padding: 16px;
    z-index: 1;
  }
  .client-overview-v4 .ov-wc-hero::before{
    content:none !important;
  }
  .client-overview-v4 .ov-wc-inner{
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
    gap: 14px;
    align-items: stretch;
  }
  .client-overview-v4 .ov-wc-badge{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(109,92,255,.20), rgba(176,92,255,.11));
    border: 1px solid rgba(109,92,255,.32);
    color: rgba(255,255,255,.92);
    font-size: .76rem;
    font-weight: 950;
    letter-spacing: .25px;
    text-transform: uppercase;
    box-shadow: 0 10px 26px rgba(109,92,255,.10);
  }
  .client-overview-v4 .ov-wc-title{
    margin: 11px 0 6px;
    color: var(--ov-text);
    font-size: 1.28rem;
    line-height: 1.18;
    font-weight: 950;
    letter-spacing: .2px;
  }
  .client-overview-v4 .ov-wc-text{
    color: var(--ov-muted);
    font-size: .92rem;
    line-height: 1.42;
    font-weight: 750;
    margin: 0;
    max-width: 720px;
  }
  .client-overview-v4 .ov-wc-stats{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
  }
  .client-overview-v4 .ov-wc-stat{
    padding: 11px 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    min-height: 60px;
  }
  .client-overview-v4 .ov-wc-stat:hover{
    border-color: rgba(109,92,255,.28);
    box-shadow: 0 12px 26px rgba(109,92,255,.07);
  }
  .client-overview-v4 .ov-wc-stat .k{
    display:flex;
    align-items:center;
    gap: 7px;
    color: var(--ov-muted);
    font-size: .78rem;
    font-weight: 850;
    margin-bottom: 4px;
  }
  .client-overview-v4 .ov-wc-stat .k i{
    width: 24px;
    height: 24px;
    border-radius: 10px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background: linear-gradient(135deg, rgba(109,92,255,.22), rgba(176,92,255,.12));
    border: 1px solid rgba(109,92,255,.22);
    color: rgba(255,255,255,.88);
  }
  .client-overview-v4 .ov-wc-stat .v{
    color: var(--ov-text);
    font-size: 1.02rem;
    font-weight: 950;
    line-height: 1.1;
  }
  .client-overview-v4 .ov-wc-side{
    padding: 13px;
    border-radius: 18px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    gap: 12px;
    min-height: 100%;
  }
  .client-overview-v4 .ov-wc-prizes{
    display:flex;
    flex-direction:column;
    gap: 8px;
  }
  .client-overview-v4 .ov-wc-prize{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
    color: var(--ov-text);
    font-size: .84rem;
    font-weight: 900;
  }
  .client-overview-v4 .ov-wc-prize:hover{
    border-color: rgba(109,92,255,.22);
    background: rgba(255,255,255,.028);
  }
  .client-overview-v4 .ov-wc-prize strong{ color: rgba(255,255,255,.96); }
  .client-overview-v4 .ov-wc-progressline{ margin-top: 12px; }
  .client-overview-v4 .ov-wc-actions{
    display:flex;
    flex-wrap:wrap;
    gap: 9px;
    margin-top: 14px;
  }
  .client-overview-v4 .ov-wc-actions .btn{
    padding: 8px 12px !important;
  }
  .client-overview-v4 .ov-wc-coupon{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    width: fit-content;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(80,220,150,.08);
    border: 1px solid rgba(80,220,150,.18);
    color: rgba(190,255,222,.92);
    font-size: .8rem;
    font-weight: 900;
  }
  .client-overview-v4 .ov-wc-code-box{
    margin-top: 12px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(80,220,150,.055);
    border: 1px solid rgba(80,220,150,.16);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 10px;
  }
  .client-overview-v4 .ov-wc-code-box .label{
    color: rgba(190,255,222,.72);
    font-size: .72rem;
    font-weight: 950;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 3px;
  }
  .client-overview-v4 .ov-wc-code-box .code{
    color: rgba(255,255,255,.95);
    font-size: .92rem;
    font-weight: 950;
    letter-spacing: .025em;
    word-break: break-all;
  }
  .client-overview-v4 .ov-wc-copy-btn{
    flex: 0 0 auto;
    border: 1px solid rgba(80,220,150,.24) !important;
    background: rgba(80,220,150,.10) !important;
    color: rgba(210,255,232,.94) !important;
    box-shadow: none;
  }
  .client-overview-v4 .ov-wc-copy-btn:hover{
    background: rgba(80,220,150,.16) !important;
    border-color: rgba(80,220,150,.36) !important;
  }
  .client-overview-v4 .ov-wc-leaderboard-btn{
    background: #5b4bff !important;
    border-color: #5b4bff !important;
    box-shadow: 0 14px 32px rgba(91,75,255,.20);
  }

  /* ===== World Cup Leaderboard Modal, Overview Native Style ===== */
  .ov-wc-modal .modal-dialog{
    max-width: 860px;
  }
  .ov-wc-modal .modal-content{
    background: rgba(12,14,22,.98) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 18px !important;
    overflow: hidden;
    box-shadow: 0 22px 70px rgba(0,0,0,.72);
    color: rgba(255,255,255,.92);
    position: relative;
  }
  .ov-wc-modal .modal-content::before{
    content:"";
    position:absolute;
    inset:0;
    padding:1px;
    border-radius:18px;
    background: linear-gradient(135deg, rgba(109,92,255,.35), rgba(255,255,255,.06), rgba(176,92,255,.22));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    pointer-events:none;
    opacity:.88;
  }
  .ov-wc-modal .modal-header,
  .ov-wc-modal .modal-footer{
    border-color: rgba(255,255,255,.08) !important;
    background: transparent !important;
    position: relative;
    z-index: 1;
    padding: 14px 16px;
  }
  .ov-wc-modal .modal-body{
    position: relative;
    z-index: 1;
    padding: 16px;
  }
  .ov-wc-modal .modal-title{
    font-weight: 950;
    letter-spacing: .2px;
    color: rgba(255,255,255,.95);
    display:flex;
    align-items:center;
    gap: 9px;
  }
  .ov-wc-modal .ov-wc-modal-close{
    width: 38px;
    height: 38px;
    border-radius: 13px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color: rgba(255,255,255,.82);
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.10);
    transition: .14s ease;
  }
  .ov-wc-modal .ov-wc-modal-close:hover{
    color: #fff;
    background: rgba(255,255,255,.08);
    border-color: rgba(109,92,255,.35);
    transform: translateY(-1px);
  }
  .ov-wc-modal .ov-wc-board-summary{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
  }
  .ov-wc-modal .ov-wc-board-stat{
    padding: 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
  }
  .ov-wc-modal .ov-wc-board-stat:hover{
    border-color: rgba(109,92,255,.28);
    box-shadow: 0 12px 26px rgba(109,92,255,.07);
  }
  .ov-wc-modal .ov-wc-board-stat .k{
    color: var(--ov-muted, rgba(255,255,255,.62));
    font-size: .76rem;
    font-weight: 850;
    margin-bottom: 4px;
  }
  .ov-wc-modal .ov-wc-board-stat .v{
    color: rgba(255,255,255,.95);
    font-size: 1rem;
    font-weight: 950;
  }
  .ov-wc-modal .ov-wc-board{
    display:flex;
    flex-direction:column;
    gap: 8px;
    max-height: 420px;
    overflow-y: auto;
    padding-right: 6px;
    scrollbar-width: thin;
    scrollbar-color: rgba(109,92,255,.55) rgba(255,255,255,.05);
  }
  .ov-wc-modal .ov-wc-board::-webkit-scrollbar{ width: 8px; }
  .ov-wc-modal .ov-wc-board::-webkit-scrollbar-track{ background: rgba(255,255,255,.05); border-radius: 999px; }
  .ov-wc-modal .ov-wc-board::-webkit-scrollbar-thumb{ background: rgba(109,92,255,.58); border-radius: 999px; }
  .ov-wc-modal .ov-wc-discount-code{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(80,220,150,.08);
    border: 1px solid rgba(80,220,150,.18);
    color: rgba(190,255,222,.94);
    font-weight: 950;
    font-size: .84rem;
    max-width: 100%;
    word-break: break-all;
  }
  .ov-wc-modal .ov-wc-modal-copy{
    margin-top: 8px;
    padding: 6px 10px !important;
    font-size: .78rem !important;
    border-radius: 11px !important;
  }
  .ov-wc-modal .ov-wc-board-row{
    display:grid;
    grid-template-columns: 58px 1fr 90px 86px;
    align-items:center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
  }
  .ov-wc-modal .ov-wc-board-row:not(.ov-wc-board-head):hover{
    background: rgba(255,255,255,.03);
    border-color: rgba(109,92,255,.25);
  }
  .ov-wc-modal .ov-wc-board-row.is-me{
    background: rgba(109,92,255,.12);
    border-color: rgba(109,92,255,.35);
    box-shadow: 0 12px 28px rgba(109,92,255,.10);
  }
  .ov-wc-modal .ov-wc-board-row:nth-child(2){ border-color: rgba(242,204,109,.34); }
  .ov-wc-modal .ov-wc-board-row:nth-child(3){ border-color: rgba(207,212,221,.24); }
  .ov-wc-modal .ov-wc-board-row:nth-child(4){ border-color: rgba(205,127,50,.24); }
  .ov-wc-modal .ov-wc-board-rank{
    font-weight: 950;
    color: rgba(255,255,255,.94);
    display:flex;
    align-items:center;
    gap: 6px;
  }
  .ov-wc-modal .ov-wc-board-name{
    min-width:0;
    color: rgba(255,255,255,.92);
    font-weight: 900;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
  }
  .ov-wc-modal .ov-wc-board-points,
  .ov-wc-modal .ov-wc-board-correct{
    text-align:right;
    color: rgba(255,255,255,.88);
    font-weight: 950;
    white-space: nowrap;
  }
  .ov-wc-modal .ov-wc-board-head{
    background: transparent !important;
    border-color: transparent !important;
    padding-top: 0;
    padding-bottom: 3px;
    color: rgba(255,255,255,.48);
    font-size: .76rem;
    font-weight: 950;
    text-transform: uppercase;
    letter-spacing: .3px;
  }
  .ov-wc-modal .ov-wc-board-empty{
    padding: 22px;
    border-radius: 18px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
    text-align:center;
    color: rgba(255,255,255,.68);
    font-weight: 800;
  }
  @media (max-width: 576px){
    .ov-wc-modal .ov-wc-board-summary{ grid-template-columns: 1fr; }
    .ov-wc-modal .ov-wc-board-row{ grid-template-columns: 48px 1fr 76px; }
    .ov-wc-modal .ov-wc-board-correct{ display:none; }
  }
  @media (max-width: 768px){
    .client-overview-v4 .ov-wc-inner{ grid-template-columns: 1fr; }
    .client-overview-v4 .ov-wc-stats{ grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 420px){
    .client-overview-v4 .ov-wc-stats{ grid-template-columns: 1fr; }
  }

  /* progress bar entry animation */
  @media (prefers-reduced-motion: no-preference){
    .client-overview-v4 .ov-order-row{
      animation: ovRowIn .3s ease both;
    }
    .client-overview-v4 .ov-order-row:nth-child(1){ animation-delay: .04s; }
    .client-overview-v4 .ov-order-row:nth-child(2){ animation-delay: .08s; }
    .client-overview-v4 .ov-order-row:nth-child(3){ animation-delay: .12s; }
    .client-overview-v4 .ov-order-row:nth-child(4){ animation-delay: .16s; }
    .client-overview-v4 .ov-order-row:nth-child(5){ animation-delay: .20s; }
    @keyframes ovRowIn{
      from{ opacity:0; transform:translateY(6px); }
      to{   opacity:1; transform:translateY(0);   }
    }
    .client-overview-v4 .ov-progress > div{
      animation: ovProgressLoad .9s cubic-bezier(.4,0,.2,1) both .25s;
    }
    @keyframes ovProgressLoad{
      from{ width: 0% !important; }
    }
  }
</style>


<?php
// (PHP logic unchanged except: next_rank for last rank -> 'challenger' lowercase)
$client = [];
if (defined('CLIENT_DATA')) {
  $client = CLIENT_DATA;
} elseif (isset($CLIENT_DATA) && is_array($CLIENT_DATA)) {
  $client = $CLIENT_DATA;
} elseif (isset($client) && is_array($client)) {
  $client = $client;
}
if (!is_array($client)) $client = [];
$client += [
  'username' => 'Client',
  'email'    => '',
  'points'   => 0,
  'icon'     => (defined('ASSET_URL') ? ASSET_URL : '') . '/core/dash/img/160x160/img1.jpg'
];

$orders        = (isset($orders) && is_array($orders)) ? $orders : [];
$unpaid_orders = (isset($unpaid_orders) && is_array($unpaid_orders)) ? $unpaid_orders : [];
$orders_unpaid = (isset($orders_unpaid) && is_array($orders_unpaid)) ? $orders_unpaid : [];
$top_boosters = (isset($top_boosters) && is_array($top_boosters)) ? $top_boosters : [];

$current_rank = (isset($current_rank) && is_array($current_rank)) ? $current_rank : [];
$next_rank    = (isset($next_rank) && is_array($next_rank)) ? $next_rank : [];

$current_rank += ['name' => 'silver'];
$next_rank    += ['name' => 'gold'];

$rankOrder = ['silver','gold','platinum','diamond','master','grandmaster','challenger'];

$currName = strtolower((string)($current_rank['name'] ?? 'silver'));
$currIdx  = array_search($currName, $rankOrder, true);
if ($currIdx === false) $currIdx = 0;

if ($currIdx >= count($rankOrder) - 1) {
  $next_rank = ['name' => 'challenger'];
  $progress  = 100;
} else {
  $next_rank = ['name' => $rankOrder[$currIdx + 1]];
}

$progress = isset($progress) ? (float)$progress : 0.0;
if ($progress < 0) $progress = 0.0;
if ($progress > 100) $progress = 100.0;

if (!function_exists('getRankData')) {
  function getRankData($rank) {
    $rank = strtolower((string)$rank);
    $ranks = [
      'silver' => ['color' => '#7a959c', 'min' => 0, 'rewards' => ['2% Cashback']],
      'gold' => ['color' => '#f29421', 'min' => 150, 'rewards' => ['3% Cashback', 'Community Giveaways']],
      'platinum' => ['color' => '#10a9cb', 'min' => 250, 'rewards' => ['4% Cashback', 'Community Giveaway', 'Exclusive Giveaways']],
      'diamond' => ['color' => '#595cf2', 'min' => 400, 'rewards' => ['5% Cashback', 'Community Giveaway', 'Exclusive Giveaways', 'Priority Support']],
      'master' => ['color' => '#9c3ae7', 'min' => 600, 'rewards' => ['6% Cashback', 'Community Giveaway', 'Exclusive Giveaways', 'Priority Support', 'Free Priority Option']],
      'grandmaster' => ['color' => '#ed1c1c', 'min' => 850, 'rewards' => ['7% Cashback', 'Community Giveaway', 'Exclusive Giveaways', 'Priority Support', 'Free Priority Option', 'Free Streaming Option']],
      'challenger' => ['color' => '#f2cc6d', 'min' => 1000, 'rewards' => ['8% Cashback', 'Community Giveaway', 'Exclusive Giveaways', 'Priority Support', 'Free Priority Option', 'Free Streaming Option']]
    ];
    return $ranks[$rank] ?? ['color' => '#ccc', 'min' => 0, 'rewards' => []];
  }
}

$currRankData = getRankData($current_rank['name'] ?? 'silver');
$nextRankData = getRankData($next_rank['name'] ?? 'gold');

$tooltips = [
  'cashback' => 'You get a percentage of your money back every time you buy something.',
  'community giveaway' => 'Take part in events where you can win prizes together with other users.',
  'exclusive giveaways' => 'Get access to special prize events only available for top-ranked users.',
  'priority support' => 'Get help faster from our support team whenever you have questions.',
  'free priority' => 'Your orders will be processed faster without any extra cost.',
  'streaming' => 'Watch your booster playing live while your order is completed.'
];

if (!function_exists('rewardIconMini')) {
  function rewardIconMini($reward, $color) {
    if (stripos($reward, 'cashback') !== false) return "<i class='fa-duotone fa-coins' style='color:$color;'></i>";
    if (stripos($reward, 'exclusive giveaways') !== false) return "<i class='fa-duotone fa-gifts' style='color:$color;'></i>";
    if (stripos($reward, 'community giveaway') !== false) return "<i class='fa-duotone fa-gift' style='color:$color;'></i>";
    if (stripos($reward, 'priority support') !== false) return "<i class='fa-duotone fa-headset' style='color:$color;'></i>";
    if (stripos($reward, 'free priority') !== false || stripos($reward, 'streaming') !== false) return "<i class='fa-duotone fa-bolt-lightning' style='color:$color;'></i>";
    return "<i class='fa-duotone fa-award' style='color:$color;'></i>";
  }
}

if (!function_exists('rewardTooltip')) {
  function rewardTooltip($reward, $tooltips) {
    foreach ($tooltips as $key => $desc) {
      if (stripos($reward, $key) !== false) return $desc;
    }
    return 'Reward benefit for your rank.';
  }
}
?>

<div class="client-overview-v4">
  <div class="container-fluid" style="padding-left:0!important;padding-right:0!important;">

    <?php
      $unpaidFallback = $unpaid_orders ?? ($orders_unpaid ?? []);
      $allOrders = array_merge($unpaidFallback, $orders ?? []);

      // Recent Orders must show every purchase type, not only boost orders.
      // Each extra row carries a pre-rendered ov_* payload so the loop below can
      // display it without going through util_format_boost_overview().
      $ovClientId = defined('CLIENT_ID') ? (int)CLIENT_ID : 0;
      if ($ovClientId > 0) {
        global $db;
        $ovIcon = function (string $url, string $fallbackIcon): string {
          $url = trim($url);
          if ($url === '') return '<i class="fa-duotone ' . $fallbackIcon . '"></i>';
          if (!preg_match('~^https?://~i', $url) && defined('ASSET_URL')) {
            $url = ASSET_URL . '/' . ltrim((string)preg_replace('~^/?public/assets/?~', '', $url), '/');
          }
          return '<img class="ov-order-type-img" src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="">';
        };
        $ovExtra = [];

        // Items
        try {
          $rows = $db->run(
            "SELECT sip.id, sip.status, sip.price, sip.quantity, sip.currency, sip.created_at,
                    si.title AS item_title, g.name AS game_name, g.icon AS game_icon
               FROM selling_item_purchases sip
               LEFT JOIN selling_items si ON si.id = sip.item_id
               LEFT JOIN games g ON g.id = si.game_id
              WHERE sip.client_id = ? ORDER BY sip.created_at DESC LIMIT 12",
            $ovClientId) ?: [];
          foreach ($rows as $r) {
            $ovExtra[] = [
              'order_id' => 'item_' . (int)$r['id'], 'status' => (string)($r['status'] ?? 'PAID'),
              'created_at' => (string)($r['created_at'] ?? ''),
              'ov_title' => (string)($r['item_title'] ?? 'Item Order'),
              'ov_sub' => trim((string)($r['game_name'] ?? 'Item') . ' · x' . max(1, (int)($r['quantity'] ?? 1))),
              'ov_icon' => $ovIcon((string)($r['game_icon'] ?? ''), 'fa-gift'),
              'ov_url' => BASE_URL . '/profile/item/' . (int)$r['id'],
            ];
          }
        } catch (Throwable $e) {}

        // Digital Goods
        try {
          if (function_exists('dg_get_client_purchases')) {
            foreach (dg_get_client_purchases($ovClientId, '', 12, 0) as $r) {
              $ovExtra[] = [
                'order_id' => 'dg_' . (int)($r['id'] ?? 0), 'status' => (string)($r['status'] ?? 'PAID'),
                'created_at' => (string)($r['created_at'] ?? ''),
                'ov_title' => (string)($r['item_title'] ?? $r['title'] ?? 'Digital Good'),
                'ov_sub' => trim((string)($r['brand'] ?? $r['category_name'] ?? 'Digital Goods') . ' · x' . max(1, (int)($r['quantity'] ?? 1))),
                'ov_icon' => $ovIcon((string)($r['brand_icon'] ?? ''), 'fa-layer-group'),
                'ov_url' => BASE_URL . '/profile/digital-goods/' . (int)($r['id'] ?? 0),
              ];
            }
          }
        } catch (Throwable $e) {}

        // Top Ups
        try {
          $rows = $db->run(
            "SELECT p.id, p.status, p.price, p.currency, p.created_at, p.game_name, p.offer_title,
                    p.offer_amount, p.offer_unit, g.icon AS game_icon
               FROM selling_topup_purchases p
               LEFT JOIN games g ON g.id = p.game_id
              WHERE p.client_id = ? ORDER BY p.created_at DESC LIMIT 12",
            $ovClientId) ?: [];
          foreach ($rows as $r) {
            $amount = trim(trim((string)($r['offer_amount'] ?? '')) . ' ' . trim((string)($r['offer_unit'] ?? '')));
            $ovExtra[] = [
              'order_id' => 'topup_' . (int)$r['id'], 'status' => (string)($r['status'] ?? 'PAID'),
              'created_at' => (string)($r['created_at'] ?? ''),
              'ov_title' => (string)($r['offer_title'] ?? 'Top Up'),
              'ov_sub' => trim((string)($r['game_name'] ?? 'Top Up') . ($amount !== '' ? ' · ' . $amount : '')),
              'ov_icon' => $ovIcon((string)($r['game_icon'] ?? ''), 'fa-coins'),
              'ov_url' => BASE_URL . '/profile/top-up/' . (int)$r['id'],
            ];
          }
        } catch (Throwable $e) {}

        // Marketplace accounts
        try {
          foreach ((db_get_rows('selling_accounts', ['client_id' => $ovClientId, 'sold' => 1], 1) ?: []) as $r) {
            $server = trim((string)($r['server'] ?? ''));
            $ovExtra[] = [
              'order_id' => 'macc_' . (int)($r['id'] ?? 0), 'status' => 'PAID',
              'created_at' => (string)($r['sold_at'] ?? $r['created_at'] ?? ''),
              'ov_title' => (string)($r['title'] ?? 'Account'),
              'ov_sub' => trim('Ranked Account' . ($server !== '' ? ' · ' . strtoupper($server) : '')),
              'ov_icon' => '<i class="fa-duotone fa-ranking-star"></i>',
              'ov_url' => BASE_URL . '/profile/account/' . (int)($r['id'] ?? 0),
            ];
          }
        } catch (Throwable $e) {}

        // Smurf accounts
        try {
          foreach ((db_get_rows('accounts', ['client_id' => $ovClientId, 'status' => 1], 1) ?: []) as $r) {
            $pkg = !empty($r['package_id']) ? (db_get_row('account_packages', ['id' => (int)$r['package_id']], 1) ?: []) : [];
            $server = trim((string)($pkg['server'] ?? $r['server'] ?? ''));
            $ovExtra[] = [
              'order_id' => 'acc_' . (int)($r['id'] ?? 0), 'status' => 'PAID',
              'created_at' => (string)($r['sold_at'] ?? $r['created_at'] ?? ''),
              'ov_title' => (string)($pkg['name'] ?? $r['login'] ?? 'Smurf Account'),
              'ov_sub' => trim('Smurf Account' . ($server !== '' ? ' · ' . strtoupper($server) : '')),
              'ov_icon' => '<i class="fa-duotone fa-helmet-battle"></i>',
              'ov_url' => BASE_URL . '/premium-account/' . (int)($r['id'] ?? 0),
            ];
          }
        } catch (Throwable $e) {}

        // Companion (GG Girl) sessions
        try {
          $rows = $db->run(
            "SELECT eo.id, eo.status, eo.created_at, eo.service_title, b.username AS egirl_username
               FROM egirl_orders eo LEFT JOIN boosters b ON b.id = eo.egirl_id
              WHERE eo.client_id = ? ORDER BY eo.created_at DESC LIMIT 12",
            $ovClientId) ?: [];
          foreach ($rows as $r) {
            $ovExtra[] = [
              'order_id' => 'eg_' . (int)$r['id'], 'status' => (string)($r['status'] ?? 'PAID'),
              'created_at' => (string)($r['created_at'] ?? ''),
              'ov_title' => (string)($r['service_title'] ?? 'Companion Session'),
              'ov_sub' => trim('Companion · ' . (string)($r['egirl_username'] ?? 'GG Girl')),
              'ov_icon' => '<i class="fa-duotone fa-user-group"></i>',
              'ov_url' => BASE_URL . '/egirl-order/' . (int)$r['id'],
            ];
          }
        } catch (Throwable $e) {}

        $allOrders = array_merge($allOrders, $ovExtra);
      }

      $seen = [];
      $allOrders = array_values(array_filter($allOrders, function($r) use (&$seen) {
        $id = $r['order_id'] ?? null;
        if (!$id || isset($seen[$id])) return false;
        $seen[$id] = true;
        return true;
      }));
      usort($allOrders, function($a, $b) {
        $ta = $a['created_at'] ?? $a['created'] ?? null;
        $tb = $b['created_at'] ?? $b['created'] ?? null;
        if ($ta && $tb) return strtotime((string)$tb) <=> strtotime((string)$ta);
        return 0;
      });
      $activeCount = 0; $unpaidCount = 0;
      foreach ($allOrders as $o) {
        $su = strtoupper(trim((string)($o['status'] ?? '')));
        $ps = strtolower(trim((string)($o['payment_status'] ?? '')));
        $isU = ($su==='UNPAID') || in_array($ps,['unpaid','pending','awaiting_payment'])
               || (isset($o['is_paid']) && (int)$o['is_paid']===0)
               || (isset($o['paid'])    && (int)$o['paid']===0);
        if ($isU) $unpaidCount++;
        if (in_array($su,['PAID','IN_PROGRESS']) || strtolower($su)==='processing') $activeCount++;
      }
      $totalOrders = count($allOrders);
      $cashbackText = ''; $cashbackPct = '';
      foreach ($currRankData['rewards'] as $r) {
        if (stripos($r,'cashback')!==false) {
          $cashbackText = $r;
          if (preg_match('/(\d+(?:\.\d+)?)\s*%/',$r,$m)) $cashbackPct = $m[1].'%';
          break;
        }
      }
      if (!$cashbackText) $cashbackText='Cashback';
      if (!$cashbackPct)  $cashbackPct='On';
      $hour = (int)date('G');
      if ($hour<12) $greeting='Good morning';
      elseif ($hour<18) $greeting='Good afternoon';
      else $greeting='Good evening';
      $nextMin   = (int)($nextRankData['min']??0);
      $currMin   = (int)($currRankData['min']??0);
      $rangeSpan = max(1,$nextMin-$currMin);
      $pointsEarned = (int)round($rangeSpan*$progress/100);
      $pointsLeft   = max(0,$rangeSpan-$pointsEarned);
      $nextHint = $pointsLeft>0 ? $pointsLeft.' pts to go' : 'Almost there!';

      $wcRaw = [];
      $wcClientId = (int)($client['id'] ?? $client['client_id'] ?? $client['user_id'] ?? 0);

      if (isset($world_cup_prediction) && is_array($world_cup_prediction)) {
        $wcRaw = $world_cup_prediction;
      } elseif (isset($worldCupPrediction) && is_array($worldCupPrediction)) {
        $wcRaw = $worldCupPrediction;
      } elseif (isset($world_cup_participant) && is_array($world_cup_participant)) {
        $wcRaw = $world_cup_participant;
      } elseif (isset($worldCupParticipant) && is_array($worldCupParticipant)) {
        $wcRaw = $worldCupParticipant;
      } elseif (isset($world_cup_predictions) && is_array($world_cup_predictions)) {
        $wcRaw = isset($world_cup_predictions[$wcClientId]) && is_array($world_cup_predictions[$wcClientId]) ? $world_cup_predictions[$wcClientId] : $world_cup_predictions;
      } elseif (isset($worldCupPredictions) && is_array($worldCupPredictions)) {
        $wcRaw = isset($worldCupPredictions[$wcClientId]) && is_array($worldCupPredictions[$wcClientId]) ? $worldCupPredictions[$wcClientId] : $worldCupPredictions;
      }

      // Prefer the real World Cup prediction tables used by /world-cup-predictions.
      if (empty($wcRaw) && $wcClientId > 0) {
        try {
          global $db;
          if (isset($db) && is_object($db) && method_exists($db, 'run')) {
            $wcAggRows = $db->run(
              'SELECT COALESCE(SUM(points),0) AS points, COUNT(id) AS predictions, SUM(CASE WHEN points > 0 THEN 1 ELSE 0 END) AS correct_predictions FROM worldcup_predictions WHERE participant_type = ? AND participant_id = ?',
              'client', $wcClientId
            ) ?: [];
            $wcAgg = $wcAggRows[0] ?? [];
            if ((int)($wcAgg['predictions'] ?? 0) > 0) {
              $wcRaw = [
                'joined' => 1,
                'points' => (int)($wcAgg['points'] ?? 0),
                'predictions' => (int)($wcAgg['predictions'] ?? 0),
                'correct_predictions' => (int)($wcAgg['correct_predictions'] ?? 0),
                'status' => 'joined'
              ];
            }
          }
        } catch (Throwable $e) {
          // Keep the dashboard safe if the World Cup tables are not available yet.
        }
      }

      if (empty($wcRaw) && $wcClientId > 0 && function_exists('db_get_row')) {
        $wcPossibleTables = [
          'world_cup_predictions',
          'world_cup_prediction_participants',
          'world_cup_participants',
          'worldcup_predictions',
          'worldcup_participants',
          'prediction_contest_participants'
        ];
        $wcPossibleColumns = ['client_id', 'user_id', 'customer_id', 'participant_id'];

        foreach ($wcPossibleTables as $wcTable) {
          foreach ($wcPossibleColumns as $wcColumn) {
            try {
              $wcTry = db_get_row($wcTable, [$wcColumn => $wcClientId]);
              if (is_array($wcTry) && !empty($wcTry)) {
                $wcRaw = $wcTry;
                break 2;
              }
            } catch (Throwable $e) {
              // Keep the dashboard safe if a possible table or column does not exist.
            }
          }
        }
      }

      // Temporary safety fallback for client #641, remove this once the controller passes real contest data.
      if (empty($wcRaw) && $wcClientId === 641) {
        $wcRaw = [
          'joined' => 1,
          'points' => 0,
          'rank' => 0,
          'correct_predictions' => 0,
          'predictions' => 0,
          'distance_to_top_5' => 0,
          'status' => 'joined'
        ];
      }

      $wcJoined = !empty($wcRaw) && (int)($wcRaw['joined'] ?? $wcRaw['is_joined'] ?? $wcRaw['is_participant'] ?? 1) === 1;
      $wcPoints = (int)($wcRaw['points'] ?? $wcRaw['total_points'] ?? $wcRaw['score'] ?? 0);
      $wcRank = (int)($wcRaw['rank'] ?? $wcRaw['position'] ?? $wcRaw['place'] ?? 0);
      $wcParticipants = (int)($wcRaw['participants'] ?? $wcRaw['total_participants'] ?? $wcRaw['players'] ?? 0);
      $wcCorrect = (int)($wcRaw['correct_predictions'] ?? $wcRaw['correct'] ?? $wcRaw['hits'] ?? 0);
      $wcPredictions = (int)($wcRaw['predictions'] ?? $wcRaw['total_predictions'] ?? $wcRaw['tips'] ?? $wcRaw['submitted_predictions'] ?? 0);
      $wcTop5Distance = (int)($wcRaw['distance_to_top_5'] ?? $wcRaw['points_to_top_5'] ?? 0);
      $wcTop5 = $wcJoined && $wcRank > 0 && $wcRank <= 5;
      $wcPrizeMap = [1 => 50, 2 => 30, 3 => 20, 4 => 10, 5 => 5];
      $wcCurrentPrize = $wcTop5 ? ($wcPrizeMap[$wcRank] ?? 0) : 0;
      $wcProgress = min(100, max(0, $wcPoints));
      $wcUrl = BASE_URL . '/world-cup-predictions';
      $wcDiscountCode = trim((string)($wcRaw['discount_code'] ?? $wcRaw['coupon_code'] ?? $wcRaw['reward_code'] ?? ''));
      $wcReward = null;
      if ($wcClientId > 0) {
        try {
          global $db;
          if (isset($db) && is_object($db) && method_exists($db, 'run')) {
            $wcRewardRows = $db->run('SELECT * FROM worldcup_rewards WHERE client_id = ? LIMIT 1', $wcClientId) ?: [];
            $wcReward = $wcRewardRows[0] ?? null;
            if (is_array($wcReward) && !empty($wcReward['discount_code'])) {
              $wcDiscountCode = trim((string)$wcReward['discount_code']);
            }
          }
        } catch (Throwable $e) {
          // Reward code is optional on the overview and must never break the dashboard.
        }
      }

      $wcLeaderboard = [];
      foreach (['world_cup_leaderboard', 'wcLeaderboard', 'worldCupLeaderboard', 'leaderboard'] as $wcBoardVar) {
        if (isset($$wcBoardVar) && is_array($$wcBoardVar) && !empty($$wcBoardVar)) {
          $wcLeaderboard = $$wcBoardVar;
          break;
        }
      }

      if (empty($wcLeaderboard)) {
        try {
          global $db;
          if (isset($db) && is_object($db) && method_exists($db, 'run')) {
            $wcLeaderboard = $db->run("
              SELECT p.participant_type, p.participant_id,
                     CASE p.participant_type
                         WHEN 'client'  THEN COALESCE(NULLIF(c.username,''), c.email, CONCAT('Client#', c.id))
                         WHEN 'booster' THEN COALESCE(b.username, CONCAT('Booster#', b.id))
                     END AS name,
                     CASE p.participant_type
                         WHEN 'client'  THEN c.icon
                         WHEN 'booster' THEN b.icon
                     END AS icon,
                     COALESCE(SUM(p.points), 0) AS points,
                     COUNT(p.id) AS predictions,
                     SUM(CASE WHEN p.points > 0 THEN 1 ELSE 0 END) AS correct_predictions
              FROM worldcup_predictions p
              LEFT JOIN clients c  ON p.participant_type = 'client'  AND c.id  = p.participant_id
              LEFT JOIN boosters b ON p.participant_type = 'booster' AND b.id  = p.participant_id AND b.is_banned = 0
              WHERE (p.participant_type = 'client' AND c.id IS NOT NULL)
                 OR (p.participant_type = 'booster' AND b.id IS NOT NULL)
              GROUP BY p.participant_type, p.participant_id
              ORDER BY points DESC, predictions DESC, p.participant_id ASC
              LIMIT 500") ?: [];

            $wcTotalRows = $db->run("SELECT COUNT(DISTINCT CONCAT(participant_type,'_',participant_id)) AS cnt FROM worldcup_predictions") ?: [];
            $wcParticipants = max($wcParticipants, (int)($wcTotalRows[0]['cnt'] ?? 0));
          }
        } catch (Throwable $e) {
          // Fall back to controller data or generic helper tables below.
        }
      }

      if (empty($wcLeaderboard) && function_exists('db_get_rows')) {
        foreach (['world_cup_predictions_leaderboard', 'world_cup_2026_leaderboard', 'world_cup_participants', 'prediction_contest_participants'] as $wcBoardTable) {
          try {
            $wcRows = db_get_rows($wcBoardTable);
            if (is_array($wcRows) && !empty($wcRows)) {
              $wcLeaderboard = $wcRows;
              break;
            }
          } catch (Throwable $e) {
            // Keep the dashboard safe if the leaderboard table does not exist.
          }
        }
      }

      $wcMaskName = function($name): string {
        $name = trim((string)$name);
        if ($name === '') return 'P****r';
        $name = preg_replace('/\s+/', '', $name);
        if (function_exists('mb_strlen')) {
          $len = mb_strlen($name, 'UTF-8');
          $first = mb_substr($name, 0, 1, 'UTF-8');
          $last = $len > 1 ? mb_substr($name, -1, 1, 'UTF-8') : '';
        } else {
          $len = strlen($name);
          $first = substr($name, 0, 1);
          $last = $len > 1 ? substr($name, -1) : '';
        }
        return strtoupper($first) . '****' . $last;
      };

      $wcNormalizeBoardRow = function($row) use ($wcClientId, $wcMaskName) {
        $row = is_array($row) ? $row : [];
        $participantType = (string)($row['participant_type'] ?? 'client');
        $rowClientId = (int)($row['client_id'] ?? $row['user_id'] ?? $row['customer_id'] ?? $row['participant_id'] ?? 0);
        $rawName = trim((string)($row['username'] ?? $row['name'] ?? $row['client_name'] ?? $row['display_name'] ?? ($rowClientId === $wcClientId ? 'You' : 'Player')));
        $isMe = $participantType === 'client' && $rowClientId > 0 && $rowClientId === $wcClientId;
        return [
          'client_id' => $rowClientId,
          'participant_type' => $participantType,
          'name' => $isMe ? 'You' : ($participantType === 'booster' ? $rawName : $wcMaskName($rawName)),
          'rank' => (int)($row['rank'] ?? $row['position'] ?? $row['place'] ?? 0),
          'points' => (int)($row['points'] ?? $row['total_points'] ?? $row['score'] ?? 0),
          'correct' => (int)($row['correct_predictions'] ?? $row['correct'] ?? $row['hits'] ?? 0),
          'predictions' => (int)($row['predictions'] ?? $row['total_predictions'] ?? $row['tips'] ?? $row['submitted_predictions'] ?? 0),
          'is_me' => $isMe
        ];
      };

      $wcLeaderboard = array_values(array_map($wcNormalizeBoardRow, $wcLeaderboard));
      if ($wcJoined) {
        $wcHasMe = false;
        foreach ($wcLeaderboard as $wcBoardRow) {
          if (!empty($wcBoardRow['is_me'])) { $wcHasMe = true; break; }
        }
        if (!$wcHasMe) {
          $wcLeaderboard[] = [
            'client_id' => $wcClientId,
            'name' => 'You',
            'rank' => $wcRank,
            'points' => $wcPoints,
            'correct' => $wcCorrect,
            'predictions' => $wcPredictions,
            'is_me' => true
          ];
        }
      }

      usort($wcLeaderboard, function($a, $b) {
        if (($a['rank'] ?? 0) > 0 && ($b['rank'] ?? 0) > 0) return ($a['rank'] <=> $b['rank']);
        return (($b['points'] ?? 0) <=> ($a['points'] ?? 0)) ?: (($b['correct'] ?? 0) <=> ($a['correct'] ?? 0));
      });
      $wcRunningRank = 1;
      foreach ($wcLeaderboard as &$wcBoardRow) {
        if (empty($wcBoardRow['rank'])) $wcBoardRow['rank'] = $wcRunningRank;
        if (!empty($wcBoardRow['is_me'])) {
          $wcRank = $wcRank > 0 ? $wcRank : (int)$wcBoardRow['rank'];
        }
        $wcRunningRank++;
      }
      unset($wcBoardRow);
      $wcLeaderboardTop = $wcLeaderboard;
      $wcLeaderboardMe = null;
      foreach ($wcLeaderboard as $wcBoardRow) {
        if (!empty($wcBoardRow['is_me'])) { $wcLeaderboardMe = $wcBoardRow; break; }
      }

      // LB Rewards Daily Gift cooldown for overview buttons.
      $ovDailyGiftCanOpen = true;
      $ovDailyGiftNextAt = null;
      $ovDailyGiftNextIso = '';
      try {
        global $db, $pdo, $conn, $database;
        $ovClientId = (int)($client['id'] ?? (defined('CLIENT_ID') ? CLIENT_ID : 0));
        $ovDailyBox = null;
        $ovRows = [];
        $ovSql = "SELECT * FROM reward_boxes WHERE status = 1 AND (is_daily = 1 OR slug = 'daily-gift') ORDER BY is_daily DESC, sort_order ASC, id ASC LIMIT 1";
        if (isset($db) && is_object($db) && method_exists($db, 'run')) {
          $ovRows = $db->run($ovSql) ?: [];
        } elseif (isset($pdo) && $pdo instanceof \PDO) {
          $stmt = $pdo->query($ovSql); $ovRows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } elseif (isset($conn) && $conn instanceof \PDO) {
          $stmt = $conn->query($ovSql); $ovRows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } elseif (isset($database) && $database instanceof \PDO) {
          $stmt = $database->query($ovSql); $ovRows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }
        $ovDailyBox = is_array($ovRows) ? ($ovRows[0] ?? null) : null;
        if ($ovClientId > 0 && is_array($ovDailyBox) && !empty($ovDailyBox['id'])) {
          $ovDailyBoxId = (int)$ovDailyBox['id'];
          $ovCooldownHours = max(1, (int)($ovDailyBox['cooldown_hours'] ?? 24));
          $ovLastRows = [];
          $ovLastSql = "SELECT created_at FROM reward_openings WHERE client_id = ? AND box_id = ? ORDER BY id DESC LIMIT 1";
          if (isset($db) && is_object($db) && method_exists($db, 'run')) {
            $ovLastRows = $db->run($ovLastSql, $ovClientId, $ovDailyBoxId) ?: [];
          } elseif (isset($pdo) && $pdo instanceof \PDO) {
            $stmt = $pdo->prepare($ovLastSql); $stmt->execute([$ovClientId, $ovDailyBoxId]); $ovLastRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
          } elseif (isset($conn) && $conn instanceof \PDO) {
            $stmt = $conn->prepare($ovLastSql); $stmt->execute([$ovClientId, $ovDailyBoxId]); $ovLastRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
          } elseif (isset($database) && $database instanceof \PDO) {
            $stmt = $database->prepare($ovLastSql); $stmt->execute([$ovClientId, $ovDailyBoxId]); $ovLastRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
          }
          $ovLastAt = is_array($ovLastRows) ? (string)($ovLastRows[0]['created_at'] ?? '') : '';
          if ($ovLastAt !== '' && strtotime($ovLastAt) !== false) {
            $ovNextTs = strtotime($ovLastAt) + ($ovCooldownHours * 3600);
            if ($ovNextTs > time()) {
              $ovDailyGiftCanOpen = false;
              $ovDailyGiftNextAt = date('Y-m-d H:i:s', $ovNextTs);
              $ovDailyGiftNextIso = date('c', $ovNextTs);
            }
          }
        }
      } catch (Throwable $e) {
        $ovDailyGiftCanOpen = true;
        $ovDailyGiftNextAt = null;
        $ovDailyGiftNextIso = '';
      }

      $ovLbCoins = (float)($client['points'] ?? 0);
      $ovLbPoints = (float)($client['reward_points'] ?? 0);
      $ovFormatAmount = function($value): string {
        $value = (float)$value;
        $formatted = number_format($value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
      };

    ?>

    <style>
      .client-overview-v4 .ov2-hero{
        position:relative;
        border-radius:26px;
        padding:22px;
        margin-bottom:16px;
        overflow:hidden;
        background:linear-gradient(135deg,rgba(38,41,50,.96),rgba(25,27,32,.96));
        border:1px solid rgba(140,160,255,.18);
        box-shadow:0 18px 55px rgba(0,0,0,.28);
      }
      .client-overview-v4 .ov2-hero:before{
        content:"";
        position:absolute;
        inset:-120px -180px auto auto;
        width:420px;
        height:260px;
        background:radial-gradient(closest-side,rgba(109,92,255,.24),rgba(109,92,255,0));
        pointer-events:none;
      }
      .client-overview-v4 .ov2-hero-inner{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:stretch;}
      .client-overview-v4 .ov2-profile{display:flex;align-items:center;gap:14px;min-width:0;}
      .client-overview-v4 .ov2-avatar{width:58px;height:58px;border-radius:18px;overflow:hidden;position:relative;flex:0 0 58px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);box-shadow:0 14px 32px rgba(0,0,0,.22);}
      .client-overview-v4 .ov2-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
      .client-overview-v4 .ov2-edit{position:absolute;right:3px;bottom:3px;width:22px;height:22px;border-radius:50%;border:0;background:rgba(0,0,0,.72);color:#fff;display:flex;align-items:center;justify-content:center;padding:0;}
      .client-overview-v4 .ov2-title{margin:0;color:var(--ov-text);font-size:1.28rem;font-weight:950;line-height:1.15;letter-spacing:-.02em;}
      .client-overview-v4 .ov2-sub{margin-top:5px;color:var(--ov-muted);font-weight:750;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px;}
      .client-overview-v4 .ov2-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:16px;}
      .client-overview-v4 .ov2-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 11px;border-radius:999px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.88);font-weight:900;font-size:.84rem;text-decoration:none;white-space:nowrap;}
      .client-overview-v4 .ov2-pill:hover{color:#fff;border-color:rgba(109,92,255,.38);background:rgba(109,92,255,.11);}
      .client-overview-v4 .ov2-pill.primary{background:linear-gradient(135deg,rgba(109,92,255,.92),rgba(126,86,255,.92));border-color:rgba(126,86,255,.35);box-shadow:0 14px 32px rgba(109,92,255,.18);}
      .client-overview-v4 .ov2-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-top:18px;}
      .client-overview-v4 .ov2-stat{padding:12px;border-radius:16px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.075);}
      .client-overview-v4 .ov2-stat .k{display:flex;align-items:center;gap:7px;color:rgba(255,255,255,.54);font-size:.78rem;font-weight:850;margin-bottom:5px;}
      .client-overview-v4 .ov2-stat .k img{width:20px;height:20px;object-fit:contain;display:block;flex:0 0 20px;}
      .client-overview-v4 .ov2-stat .v{color:var(--ov-text);font-weight:950;font-size:1.02rem;line-height:1.1;}
      @media(max-width:1200px){.client-overview-v4 .ov2-stats{grid-template-columns:repeat(3,minmax(0,1fr));}}
      @media(max-width:576px){.client-overview-v4 .ov2-stats{grid-template-columns:repeat(2,minmax(0,1fr));}}
      .client-overview-v4 .ov2-rank{margin-top:16px;padding:14px;border-radius:18px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.075);}
      .client-overview-v4 .ov2-rank-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:9px;}
      .client-overview-v4 .ov2-rank-left{display:flex;align-items:center;gap:9px;color:var(--ov-text);font-weight:950;}
      .client-overview-v4 .ov2-rank-left img{width:24px;height:24px;object-fit:contain;}
      .client-overview-v4 .ov2-rank-next{color:var(--ov-muted);font-weight:850;font-size:.84rem;}
      .client-overview-v4 .ov2-daily{position:relative;padding:18px;border-radius:22px;background:linear-gradient(145deg,rgba(16,18,30,.72),rgba(13,14,22,.78));border:1px solid rgba(140,160,255,.18);display:flex;flex-direction:column;justify-content:space-between;min-height:100%;overflow:hidden;}
      .client-overview-v4 .ov2-daily:before{content:"";position:absolute;inset:-95px -85px auto auto;width:240px;height:180px;background:radial-gradient(closest-side,rgba(109,92,255,.34),rgba(109,92,255,0));pointer-events:none;}
      .client-overview-v4 .ov2-daily:after{content:"";position:absolute;inset:auto 14px 14px 14px;height:1px;background:linear-gradient(90deg,transparent,rgba(142,167,255,.20),transparent);pointer-events:none;opacity:.75;}
      .client-overview-v4 .ov2-daily-top{position:relative;z-index:1;display:grid;grid-template-columns:72px minmax(0,1fr);align-items:center;gap:14px;}
      .client-overview-v4 .ov2-daily-img{width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,rgba(109,92,255,.12),rgba(255,255,255,.035));border:1px solid rgba(142,167,255,.12);display:flex;align-items:center;justify-content:center;flex:0 0 72px;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 14px 32px rgba(0,0,0,.20);}
      .client-overview-v4 .ov2-daily-img img{max-width:88px;max-height:88px;object-fit:contain;filter:drop-shadow(0 18px 26px rgba(109,92,255,.24));}
      .client-overview-v4 .ov2-daily h3{font-size:1.1rem;color:var(--ov-text);font-weight:950;margin:0 0 4px;line-height:1.1;}
      .client-overview-v4 .ov2-daily p{margin:0;color:var(--ov-muted);font-weight:750;font-size:.86rem;line-height:1.35;max-width:240px;}
      .client-overview-v4 .ov2-daily-actions{position:relative;z-index:1;display:grid;grid-template-columns:1fr;gap:9px;margin-top:18px;}
      .client-overview-v4 .ov2-daily-actions .btn{min-height:46px;display:flex;align-items:center;justify-content:center;gap:8px;line-height:1.15;}
      .client-overview-v4 .ov2-daily-actions .btn-outline-light{background:rgba(255,255,255,.035)!important;border-color:rgba(255,255,255,.12)!important;color:rgba(255,255,255,.86)!important;box-shadow:none!important;}
      .client-overview-v4 .ov2-daily-actions .btn-outline-light:hover{background:rgba(109,92,255,.10)!important;border-color:rgba(109,92,255,.30)!important;color:#fff!important;}
      .client-overview-v4 .ov-daily-gift-btn.is-waiting{background:rgba(255,255,255,.055)!important;border-color:rgba(255,255,255,.10)!important;box-shadow:none!important;color:rgba(255,255,255,.78)!important;cursor:default;}
      .client-overview-v4 .ov2-section{border-radius:20px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.075);overflow:hidden;box-shadow:0 14px 34px rgba(0,0,0,.18);}
      .client-overview-v4 .ov2-section + .ov2-section{margin-top:14px;}
      .client-overview-v4 .ov2-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 16px;border-bottom:1px solid rgba(255,255,255,.065);}
      .client-overview-v4 .ov2-section-title{display:flex;align-items:center;gap:9px;color:var(--ov-text);font-weight:950;font-size:1.03rem;}
      .client-overview-v4 .ov2-section-title i{color:#8ea7ff;}
      .client-overview-v4 .ov2-section-body{padding:14px 16px;}
      .client-overview-v4 .ov2-orders{display:flex;flex-direction:column;gap:9px;}
      .client-overview-v4 .ov2-order{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:15px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);cursor:pointer;text-decoration:none;color:inherit;}
      .client-overview-v4 .ov2-order:hover{border-color:rgba(109,92,255,.28);background:rgba(109,92,255,.06);}
      .client-overview-v4 .ov2-order-main{display:flex;align-items:center;gap:10px;min-width:0;}
      .client-overview-v4 .ov2-order-ico{width:40px;height:40px;border-radius:14px;background:rgba(109,92,255,.13);border:1px solid rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;color:#fff;flex:0 0 40px;}
      .client-overview-v4 .ov2-order-title{font-weight:950;color:var(--ov-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
      .client-overview-v4 .ov2-order-meta{font-size:.78rem;color:var(--ov-muted);font-weight:800;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
      .client-overview-v4 .ov2-order-status{display:flex;align-items:center;gap:8px;justify-content:flex-end;flex-wrap:wrap;}
      .client-overview-v4 .ov2-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.88);font-weight:900;font-size:.78rem;white-space:nowrap;}
      .client-overview-v4 .ov2-badge.unpaid{background:rgba(255,82,82,.10);border-color:rgba(255,82,82,.25);color:#fca5a5;}
      .client-overview-v4 .ov2-empty{padding:30px 18px;text-align:center;color:var(--ov-muted);font-weight:800;}
      .client-overview-v4 .ov2-empty i{display:block;font-size:30px;margin-bottom:8px;color:rgba(142,167,255,.8);}
      .client-overview-v4 .ov2-side-stack{display:flex;flex-direction:column;gap:14px;}
      .client-overview-v4 .ov2-wallet{display:flex;align-items:center;justify-content:space-between;gap:12px;}
      .client-overview-v4 .ov2-wallet-left{display:flex;align-items:center;gap:12px;min-width:0;}
      .client-overview-v4 .ov2-wallet-icon{width:60px;height:60px;border-radius:20px;background:rgba(109,92,255,.10);border:1px solid rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;}
      .client-overview-v4 .ov2-wallet-icon img{width:44px;height:44px;object-fit:contain;}
      .client-overview-v4 .ov2-wallet-value{font-size:1.55rem;color:var(--ov-text);font-weight:950;line-height:1;}
      .client-overview-v4 .ov2-wallet-label{color:var(--ov-muted);font-weight:800;font-size:.84rem;margin-top:4px;}
      .client-overview-v4 .ov2-loyalty-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.055);}
      .client-overview-v4 .ov2-loyalty-row:last-child{border-bottom:0;}
      .client-overview-v4 .ov2-loyalty-left{display:flex;align-items:center;gap:10px;min-width:0;}
      .client-overview-v4 .ov2-loyalty-left i{width:32px;height:32px;border-radius:12px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;}
      .client-overview-v4 .ov2-loyalty-text{font-weight:900;color:var(--ov-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
      .client-overview-v4 .ov2-loyalty-status{color:var(--ov-muted);font-size:.78rem;font-weight:850;white-space:nowrap;}
      .client-overview-v4 .ov2-alert{position:relative;display:grid;grid-template-columns:42px minmax(0,1fr);gap:13px;padding:16px;border-radius:20px;background:linear-gradient(135deg,rgba(255,70,70,.060),rgba(109,92,255,.040));border:1px solid rgba(255,70,70,.18);overflow:hidden;}
      .client-overview-v4 .ov2-alert:before{content:"";position:absolute;inset:-70px -90px auto auto;width:180px;height:140px;background:radial-gradient(closest-side,rgba(255,70,70,.16),rgba(255,70,70,0));pointer-events:none;}
      .client-overview-v4 .ov2-alert i{position:relative;z-index:1;width:42px;height:42px;border-radius:15px;background:rgba(255,70,70,.10);border:1px solid rgba(255,70,70,.22);display:flex;align-items:center;justify-content:center;color:#fecaca;flex:0 0 42px;margin-top:1px;}
      .client-overview-v4 .ov2-alert .ov2-alert-content{position:relative;z-index:1;min-width:0;display:grid;gap:10px;}
      .client-overview-v4 .ov2-alert b{display:block;color:rgba(255,255,255,.94);font-size:.96rem;line-height:1.15;}
      .client-overview-v4 .ov2-alert p{margin:4px 0 0;color:rgba(255,255,255,.68);font-weight:750;font-size:.86rem;line-height:1.38;}
      .client-overview-v4 .ov2-alert .ov2-alert-actions{display:flex;align-items:center;justify-content:flex-start;}
      .client-overview-v4 .ov2-alert .btn-report-clean{min-height:38px;padding:8px 13px!important;border-radius:13px!important;display:inline-flex;align-items:center;gap:8px;}
      .client-overview-v4 .ov2-boosters-list{padding:2px 2px 10px;}
      .client-overview-v4 .ov2-boosters-list .booster-card{width:clamp(250px,24vw,330px);}
      .client-overview-v4 .ov2-mobile-bottom{display:none;}
      @media(max-width:991px){
        .client-overview-v4 .ov2-hero-inner{grid-template-columns:1fr;}
        .client-overview-v4 .ov2-stats{grid-template-columns:repeat(2,minmax(0,1fr));}
        .client-overview-v4 .ov2-daily-actions{grid-template-columns:1fr;}
      }

      /* Overview polish: Stay on platform + Trustpilot */
      .client-overview-v4 .ov2-alert{
        grid-template-columns:52px minmax(0,1fr) !important;
        align-items:center !important;
        gap:14px !important;
        padding:17px !important;
        border-radius:20px !important;
        background:linear-gradient(135deg,rgba(255,72,93,.075),rgba(109,92,255,.055)) !important;
        border:1px solid rgba(255,95,110,.18) !important;
        box-shadow:0 16px 42px rgba(0,0,0,.20), inset 0 1px 0 rgba(255,255,255,.035) !important;
      }
      .client-overview-v4 .ov2-alert:before{
        inset:-70px -90px auto auto !important;
        width:210px !important;
        height:150px !important;
        background:radial-gradient(closest-side,rgba(255,72,93,.18),rgba(255,72,93,0)) !important;
      }
      .client-overview-v4 .ov2-alert > i{
        width:52px !important;
        height:52px !important;
        border-radius:17px !important;
        margin-top:0 !important;
        background:rgba(255,82,82,.10) !important;
        border:1px solid rgba(255,118,118,.22) !important;
        color:#fecaca !important;
        font-size:18px !important;
        box-shadow:0 14px 34px rgba(255,70,70,.10) !important;
      }
      .client-overview-v4 .ov2-alert .ov2-alert-content{
        display:flex !important;
        align-items:center !important;
        justify-content:space-between !important;
        gap:16px !important;
      }
      .client-overview-v4 .ov2-alert b{
        font-size:1rem !important;
        letter-spacing:-.01em !important;
        margin-bottom:4px !important;
      }
      .client-overview-v4 .ov2-alert p{
        max-width:520px !important;
        margin:0 !important;
        color:rgba(255,255,255,.70) !important;
        font-size:.88rem !important;
        line-height:1.42 !important;
      }
      .client-overview-v4 .ov2-alert .ov2-alert-actions{
        flex:0 0 auto !important;
      }
      .client-overview-v4 .ov2-alert .btn-report-clean{
        min-height:42px !important;
        padding:10px 15px !important;
        border-radius:14px !important;
        background:linear-gradient(135deg,#ff5269,#6d5cff) !important;
        border:0 !important;
        box-shadow:0 16px 36px rgba(255,82,105,.16),0 12px 30px rgba(109,92,255,.18) !important;
        white-space:nowrap !important;
      }
      .client-overview-v4 .ov2-alert .btn-report-clean i{
        width:28px !important;
        height:28px !important;
        margin:0 !important;
        border-radius:11px !important;
        background:rgba(255,255,255,.12) !important;
        border:1px solid rgba(255,255,255,.13) !important;
        color:#fff !important;
        box-shadow:none !important;
        font-size:13px !important;
      }
      .client-overview-v4 .ov2-trustpilot-btn{
        justify-content:center !important;
        min-height:42px !important;
        border-color:rgba(0,182,122,.22) !important;
      }
      .client-overview-v4 .ov2-trustpilot-btn i{
        color:#00b67a !important;
        filter:drop-shadow(0 0 10px rgba(0,182,122,.25));
      }
      .client-overview-v4 .ov2-trustpilot-btn:hover{
        border-color:rgba(0,182,122,.42) !important;
        background:rgba(0,182,122,.08) !important;
      }
      @media(max-width:576px){
        .client-overview-v4 .ov2-alert{grid-template-columns:42px minmax(0,1fr) !important;align-items:flex-start !important;padding:15px !important;}
        .client-overview-v4 .ov2-alert > i{width:42px !important;height:42px !important;border-radius:15px !important;}
        .client-overview-v4 .ov2-alert .ov2-alert-content{display:grid !important;gap:12px !important;}
        .client-overview-v4 .ov2-alert .btn-report-clean{width:100% !important;justify-content:center !important;}
      }
      @media(max-width:576px){
        .client-overview-v4 .ov2-hero{padding:16px;border-radius:22px;}
        .client-overview-v4 .ov2-profile{align-items:flex-start;}
        .client-overview-v4 .ov2-stats{grid-template-columns:1fr 1fr;gap:8px;}
        .client-overview-v4 .ov2-stat{padding:10px;}
        .client-overview-v4 .ov2-order{align-items:flex-start;flex-direction:column;}
        .client-overview-v4 .ov2-order-status{justify-content:flex-start;}
        .client-overview-v4 .ov2-section-head{align-items:flex-start;flex-direction:column;}
        .client-overview-v4 .ov2-boosters-list .booster-card{width:82vw;}
      }
    </style>

    <div class="ov2-hero">
      <div class="ov2-hero-inner">
        <div>
          <div class="ov2-profile">
            <div class="ov2-avatar">
              <img src="<?= htmlspecialchars((string)$client['icon']) ?>" alt="<?= htmlspecialchars((string)$client['username']) ?>">
              <button class="ov2-edit" data-bs-toggle="modal" data-bs-target="#upload-icon-modal" aria-label="Edit icon"><i class="fa-solid fa-pen" style="font-size:.58rem;"></i></button>
            </div>
            <div style="min-width:0;">
              <h2 class="ov2-title"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars((string)$client['username']) ?>!</h2>
              <div class="ov2-sub"><?= htmlspecialchars((string)$client['email']) ?></div>
            </div>
          </div>

          <div class="ov2-actions">
            <a class="ov2-pill primary" href="<?= BASE_URL ?>/profile/orders"><i class="fa-duotone fa-cart-shopping"></i> My Orders</a>
            <a class="ov2-pill" href="<?= BASE_URL ?>/profile/rewards"><i class="fa-duotone fa-gift"></i> LB Rewards</a>
            <a class="ov2-pill" href="<?= BASE_URL ?>/profile/rewards/wins"><i class="fa-duotone fa-trophy-star"></i> My Wins</a>
            <a class="ov2-pill open-chat" href="#open-chat"><i class="fa-regular fa-messages"></i> Live Chat</a>
          </div>

          <div class="ov2-stats">
            <div class="ov2-stat"><div class="k"><i class="fa-duotone fa-bag-shopping"></i> Orders</div><div class="v"><?= (int)$totalOrders ?></div></div>
            <div class="ov2-stat"><div class="k"><i class="fa-duotone fa-rocket-launch"></i> Active</div><div class="v"><?= (int)$activeCount ?></div></div>
            <div class="ov2-stat"><div class="k"><i class="fa-duotone fa-sack-dollar"></i> Cashback</div><div class="v"><?= htmlspecialchars($cashbackPct) ?></div></div>
            <div class="ov2-stat points"><div class="k"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/reward-points.png" alt="Reward Points"> Reward Points</div><div class="v"><?= htmlspecialchars($ovFormatAmount($ovLbPoints)) ?></div></div>
            <div class="ov2-stat coins"><div class="k"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/coin_purple.png" alt="LB Coins"> LB Coins</div><div class="v"><?= htmlspecialchars($ovFormatAmount($ovLbCoins)) ?></div></div>
          </div>

          <div class="ov2-rank">
            <div class="ov2-rank-head">
              <div class="ov2-rank-left">
                <img src="https://lolboost.gg/public/assets/core/main/img/loyalty/<?= strtolower($current_rank['name']) ?>_icon.svg" alt="">
                <?= ucfirst($current_rank['name']) ?> Loyalty
              </div>
              <div class="ov2-rank-next">Next: <strong style="color:<?= $nextRankData['color'] ?>;"><?= ucfirst($next_rank['name']) ?></strong> · <?= htmlspecialchars($nextHint) ?></div>
            </div>
            <div class="ov-progress"><div style="width:<?= (float)$progress ?>%;"></div></div>
          </div>
        </div>

        <div class="ov2-daily">
          <div class="ov2-daily-top">
            <div class="ov2-daily-img"><img src="<?= BASE_URL ?>/public/assets/website/images/rewards/boxes/daily-gift.png" alt="Daily Gift"></div>
            <div style="min-width:0;">
              <h3>Daily Gift</h3>
              <p>Open your free reward box every 24 hours and collect Reward Points, coupons and perks.</p>
            </div>
          </div>
          <div class="ov2-daily-actions">
            <a class="btn btn-primary ov-daily-gift-btn<?= $ovDailyGiftCanOpen ? '' : ' is-waiting' ?>" href="<?= BASE_URL ?>/profile/rewards/daily-gift">
              <?php if ($ovDailyGiftCanOpen): ?>
                <i class="fa-duotone fa-sparkles me-2"></i>Open Daily Gift
              <?php else: ?>
                <i class="fa-duotone fa-clock me-2"></i>Waiting <span class="ov-countdown" data-ov-countdown="<?= htmlspecialchars($ovDailyGiftNextIso) ?>">--:--:--</span>
              <?php endif ?>
            </a>
            <a class="btn btn-outline-light" href="<?= BASE_URL ?>/profile/rewards"><i class="fa-duotone fa-box-open"></i> View all boxes</a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($unpaidCount > 0): ?>
      <div class="ov2-section mb-3" style="border-color:rgba(255,82,82,.18);background:rgba(255,82,82,.045);">
        <div class="ov2-section-body d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2" style="color:#fca5a5;font-weight:950;"><i class="fa-duotone fa-circle-exclamation"></i> You have <?= (int)$unpaidCount ?> unpaid order<?= $unpaidCount === 1 ? '' : 's' ?>.</div>
          <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>/profile/orders"><i class="fa-duotone fa-credit-card me-1"></i>Pay now</a>
        </div>
      </div>
    <?php endif ?>

    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <!-- Recent Orders -->
        <div class="card mb-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <i class="fa-duotone fa-rocket-launch"></i>
              <h5 class="ov-title" style="font-size:1.1rem;">Recent Orders</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="ov-filter-toggle" id="ovOrderFilterWrap">
                <button type="button" class="ov-filter-btn active" data-ov-order-filter="all">All</button>
                <button type="button" class="ov-filter-btn" data-ov-order-filter="unpaid">Unpaid</button>
                <button type="button" class="ov-filter-btn" data-ov-order-filter="in_progress">Active</button>
              </div>
              <a class="ov-link" href="<?= BASE_URL ?>/profile/orders">All Orders <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
          </div>
          <div class="card-body">
            <?php if (empty($allOrders)): ?>
              <div class="ov-empty-state">
                <div class="ov-empty-ico"><i class="fa-duotone fa-rocket-launch"></i></div>
                <div class="ov-empty-title">No orders yet</div>
                <div class="ov-empty-sub">Ready to climb? Get your first boost in minutes.</div>
                <a href="<?= BASE_URL ?>/lol/rank-boost" class="btn btn-primary mt-3">
                  <i class="fa-solid fa-bolt me-2"></i>Buy your first Boost
                </a>
              </div>
            <?php else: ?>
              <div class="ov-order-list">
                <?php
                  $unpaidFirst=[]; $activeFirst=[]; $paidRest=[];
                  foreach ($allOrders as $_row) {
                    $st=strtoupper(trim((string)($_row['status']??'')));
                    $ps=strtolower(trim((string)($_row['payment_status']??'')));
                    $isU=($st==='UNPAID')||in_array($ps,['unpaid','pending','awaiting_payment'])
                         ||(isset($_row['is_paid'])&&(int)$_row['is_paid']===0)
                         ||(isset($_row['paid'])&&(int)$_row['paid']===0);
                    $isA=!$isU && in_array($st,['PAID','IN_PROGRESS','PROCESSING','ACTIVE','CLAIMED','BOOSTING','STARTED'], true);
                    if ($isU) $unpaidFirst[]=$_row;
                    elseif ($isA) $activeFirst[]=$_row;
                    else $paidRest[]=$_row;
                  }
                  // Keep unpaid orders visible, but do not let them hide all active orders.
                  $displayOrders = array_slice(array_merge(
                    array_slice($unpaidFirst, 0, 3),
                    array_slice($activeFirst, 0, 3),
                    $paidRest,
                    array_slice($unpaidFirst, 3)
                  ),0,6);
                  foreach ($displayOrders as $row):
                    $oid=$row['order_id']??($row['id']??null);
                    $su=strtoupper(trim((string)($row['status']??'')));
                    $ps=strtolower(trim((string)($row['payment_status']??'')));
                    if ($su==='' && $ps!=='') $su=strtoupper($ps);
                    $isUnpaid=($su==='UNPAID')||in_array($ps,['unpaid','pending','awaiting_payment'])
                              ||(isset($row['is_paid'])&&(int)$row['is_paid']===0)
                              ||(isset($row['paid'])&&(int)$row['paid']===0);
                    $isActive=!$isUnpaid && in_array($su,['PAID','IN_PROGRESS','PROCESSING','ACTIVE','CLAIMED','BOOSTING','STARTED'], true);
                    $recentStatus=['label'=>'Processing','key'=>'processing','icon'=>'fa-spinner'];
                    if ($isUnpaid) $recentStatus=['label'=>'Unpaid','key'=>'unpaid','icon'=>'fa-credit-card'];
                    elseif (in_array($su,['PAID','PAYED'],true)) $recentStatus=['label'=>'Paid','key'=>'paid','icon'=>'fa-circle-check'];
                    elseif (in_array($su,['PAUSED','PAUSE','ON_HOLD','ONHOLD'],true)) $recentStatus=['label'=>'Paused','key'=>'paused','icon'=>'fa-pause'];
                    elseif (in_array($su,['INPROGRESS','IN_PROGRESS','PROGRESS','ACTIVE','CLAIMED','BOOSTING','STARTED'],true)) $recentStatus=['label'=>'In Progress','key'=>'inprogress','icon'=>'fa-loader'];
                    elseif (in_array($su,['DELIVERED','SHIPPED','FULFILLED'],true)) $recentStatus=['label'=>'Delivered','key'=>'delivered','icon'=>'fa-truck-fast'];
                    elseif (in_array($su,['COMPLETED','DONE','FINISHED'],true)) $recentStatus=['label'=>'Completed','key'=>'completed','icon'=>'fa-circle-check'];
                    elseif (in_array($su,['REFUND','REFUNDED','PARTIALLY_REFUNDED'],true)) $recentStatus=['label'=>$su==='PARTIALLY_REFUNDED'?'Partially Refunded':'Refunded','key'=>'refunded','icon'=>'fa-rotate-left'];
                    elseif (in_array($su,['CANCELLED','CANCELED'],true)) $recentStatus=['label'=>'Cancelled','key'=>'cancelled','icon'=>'fa-ban'];
                    elseif (in_array($su,['FAILED','PAYMENT_FAILED','EXPIRED'],true)) $recentStatus=['label'=>'Failed','key'=>'failed','icon'=>'fa-circle-xmark'];
                    elseif (in_array($su,['DISPUTED','DISPUTE'],true)) $recentStatus=['label'=>'Disputed','key'=>'disputed','icon'=>'fa-scale-balanced'];
                    elseif (in_array($su,['CHARGEBACK','CHARGED_BACK'],true)) $recentStatus=['label'=>'Chargeback','key'=>'chargeback','icon'=>'fa-shield-exclamation'];
                    $invoiceUuid=null;
                    if ($isUnpaid&&$oid&&!isset($row['ov_title'])) { $inv=db_get_row('invoices',['order_id'=>$oid]); $invoiceUuid=$inv['uuid']??null; }
                    if (isset($row['ov_title'])) {
                      // Non-boost order (item, digital good, top up, account, companion)
                      $orderHref=$row['ov_url'];
                      $iconHtml=$row['ov_icon'];
                      $titleHtml=htmlspecialchars((string)$row['ov_title'], ENT_QUOTES);
                      $metaLine=htmlspecialchars((string)$row['ov_sub'], ENT_QUOTES);
                    } else {
                    $orderHref = BASE_URL.'/order/'.$oid;
                    if ($isUnpaid&&$invoiceUuid) $orderHref=BASE_URL.'/checkout/'.$invoiceUuid;
                    $gameShort=util_game_display_name($row['game']??'');
                    $iconHtml=util_boost_form_icon_html($row['icon']??'',1.5,'text-body');
                    $gameBadgeUrl=function_exists('util_game_icon_url') ? util_game_icon_url($row['game']??'') : '';
                    if ($gameBadgeUrl!=='') $iconHtml .= '<img class="ov-order-game-badge" src="'.htmlspecialchars($gameBadgeUrl).'" alt="">';
                    $titleHtml=util_format_boost_overview($row['game']??'',$row['type']??'',$row);
                    $metaLine=htmlspecialchars(trim($gameShort.' '.($row['name']??'')), ENT_QUOTES);
                    }
                ?>
                <div class="ov-order-row ov-clickable" data-ov-status="<?= $isUnpaid ? 'unpaid' : ($isActive ? 'in_progress' : strtolower($su)) ?>">
                  <div class="ov-order-left">
                    <div style="min-width:0;">
                      <a class="d-flex align-items-center" href="<?= htmlspecialchars($orderHref) ?>">
                        <div class="avatar avatar-light avatar-rounded">
                          <span class="avatar-initials"><?= $iconHtml ?></span>
                        </div>
                        <div class="ms-3" style="min-width:0;">
                          <span class="d-block text-body h4 mb-0 fw-bold text-truncate"><?= $titleHtml ?></span>
                          <small class="text-muted d-block">
                            <?= $metaLine ?>
                            <?php if ($oid): ?><span style="opacity:.7;font-weight:800;margin-left:6px;">#<?= htmlspecialchars((string)(isset($row['ov_title']) ? preg_replace('~^[a-z]+_~','',(string)$oid) : $oid), ENT_QUOTES) ?></span><?php endif ?>
                          </small>
                        </div>
                      </a>
                    </div>
                  </div>
                  <div class="ov-order-right">
                    <?php if ($isUnpaid): ?>
                      <span class="ov-recent-status unpaid"><i class="fa-solid fa-credit-card"></i> Unpaid</span>
                      <?php if ($invoiceUuid): ?>
                        <a class="btn btn-primary btn-sm ov-pay-btn" href="<?= BASE_URL.'/checkout/'.htmlspecialchars($invoiceUuid) ?>">
                          <i class="fa-duotone fa-cart-shopping me-1"></i>Pay
                        </a>
                      <?php endif ?>
                    <?php else: ?>
                      <span class="ov-recent-status <?= htmlspecialchars($recentStatus['key'], ENT_QUOTES) ?>"><i class="fa-solid <?= htmlspecialchars($recentStatus['icon'], ENT_QUOTES) ?>"></i><?= htmlspecialchars($recentStatus['label'], ENT_QUOTES) ?></span>
                    <?php endif ?>
                  </div>
                </div>
                <?php endforeach ?>
              </div>
            <?php endif ?>

          </div>
        </div>

        <div class="ov2-section">
          <div class="ov2-section-head">
            <div class="ov2-section-title"><i class="fa-duotone fa-gift"></i> LB Rewards</div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <a class="ov-link" href="<?= BASE_URL ?>/profile/rewards/wins">My Wins</a>
              <a class="ov-link" href="<?= BASE_URL ?>/profile/rewards">Open boxes</a>
            </div>
          </div>
          <div class="ov2-section-body">
            <div class="row g-3 align-items-center">
              <div class="col-12 col-md-7">
                <div class="d-flex align-items-center gap-3">
                  <div class="ov2-wallet-icon"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/reward-points.png" alt="Reward Points"></div>
                  <div>
                    <div style="color:var(--ov-text);font-weight:950;font-size:1.1rem;">Collect Reward Points, open boxes, use rewards instantly</div>
                    <div class="ov-muted" style="font-weight:750;margin-top:4px;">Your Reward Points: <strong style="color:#fff;"><?= htmlspecialchars($ovFormatAmount($ovLbPoints)) ?></strong> · Win coupons, wallet credit and order perks.</div>
                  </div>
                </div>
                <div class="ov2-actions mt-3">
                  <a class="ov2-pill primary" href="<?= BASE_URL ?>/profile/rewards"><i class="fa-duotone fa-box-open"></i> Reward Boxes</a>
                  <a class="ov2-pill" href="<?= BASE_URL ?>/profile/rewards/wins"><i class="fa-duotone fa-list"></i> My Wins</a>
                </div>
              </div>
              <div class="col-12 col-md-5 text-center">
                <img src="<?= BASE_URL ?>/public/assets/website/images/rewards/boxes/starter-box.png" alt="Reward Box" style="max-width:180px;width:70%;filter:drop-shadow(0 22px 34px rgba(109,92,255,.22));">
              </div>
            </div>
          </div>
        </div>

        <?php if (!empty($top_boosters)): ?>
        <div class="ov2-section">
          <div class="ov2-section-head">
            <div class="ov2-section-title"><i class="fa-duotone fa-users"></i> Top Boosters</div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="ov-filter-toggle" data-ov-filter-controls="#ovTopBoostersRow">
                <button type="button" class="ov-filter-btn active" data-ov-filter="all"><span class="ov-filter-dot"></span> All</button>
                <button type="button" class="ov-filter-btn" data-ov-filter="online"><span class="ov-filter-dot"></span> Online</button>
              </div>
              <button type="button" class="ov-scroll-btn" data-ov-scroll="prev" data-ov-target="#ovTopBoostersRow" aria-label="Prev"><i class="fa-duotone fa-chevron-left"></i></button>
              <button type="button" class="ov-scroll-btn" data-ov-scroll="next" data-ov-target="#ovTopBoostersRow" aria-label="Next"><i class="fa-duotone fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="ov2-section-body">
            <?php
              $__onlineBoosterMap = function_exists('lb_booster_online_map') ? lb_booster_online_map() : [];
              $getOrdersCount=function($b){ if (!is_array($b)) return 0; foreach (['orders_count','order_count','orders','total_orders','orders_total','completed_orders','count'] as $k) { if (isset($b[$k])&&is_numeric($b[$k])) return (int)$b[$k]; } return 0; };
              $boostersSorted=$top_boosters;
              usort($boostersSorted,function($a,$b) use ($getOrdersCount){ return $getOrdersCount($b)<=>$getOrdersCount($a); });
              $boostersTop=array_slice($boostersSorted,0,20);
            ?>
            <div class="ov-top-boosters-wrap">
              <div class="ov-top-boosters-list ov2-boosters-list" id="ovTopBoostersRow" tabindex="0" role="list">
              <?php foreach ($boostersTop as $booster):
                $boosterId=(int)($booster['booster_id']??$booster['id']??0);
                $isOnline=!empty($__onlineBoosterMap[$boosterId]);
                $statusText=$isOnline?t('Online'):t('Offline');
                $statusClass=$isOnline?'online':'offline';
                $tzRaw=trim((string)($booster['timezone']??$booster['time_zone']??$booster['tz']??$booster['utc_offset']??''));
                $tzDisplay='';
                if ($tzRaw !== '') {
                  if (function_exists('util_format_timezone_display')) {
                    $tzDisplay=(string)util_format_timezone_display($tzRaw);
                  } else {
                    try {
                      if (class_exists('DateTimeZone')) {
                        $dt=new DateTime('now', new DateTimeZone($tzRaw));
                        $tzDisplay=$tzRaw.' (UTC'.$dt->format('P').')';
                      } else {
                        $tzDisplay=$tzRaw;
                      }
                    } catch (Throwable $e) {
                      $tzDisplay=$tzRaw;
                    }
                  }
                }
                $cover=$booster['cover']??(defined('ASSET_URL')?ASSET_URL.'/core/main/img/banners/leona.jpeg':'');
              ?>
              <div class="cover-link ov-booster-card-link" data-href="<?= BASE_URL.'/boosters/'.$boosterId ?>" data-ov-online="<?= $isOnline?1:0 ?>" data-target="_blank" role="link" tabindex="0">
                <div class="booster-card booster-card--compact">
                  <div class="booster-cover" style="background-image:url('<?= $cover ?>');"></div>
                  <div class="avatar"><img src="<?= $booster['icon']??'' ?>" alt=""><span class="booster-online-dot <?= $statusClass ?>"></span></div>
                  <div class="details">
                    <div class="top">
                      <div style="min-width:0;">
                        <div class="d-flex align-items-center gap-2"><h5 class="text-truncate mb-0"><?= htmlspecialchars($booster['username']??'Booster') ?></h5><span class="booster-status-inline <?= $statusClass ?>"><span class="dot"></span><span><?= $statusText ?></span></span></div>
                        <?php if ($tzDisplay !== ''): ?>
                          <div class="ov-booster-timezone" title="Booster timezone"><i class="fa-duotone fa-clock"></i><span><?= htmlspecialchars($tzDisplay) ?></span></div>
                        <?php endif ?>
                      </div>
                      <div class="rank-box">
                        <?php $rankImg=ASSET_URL.'/core/main/img/lol/ranks/max/0.png'; if (!empty($booster['lol_rank'])) { $rp=explode('|',$booster['lol_rank']); if (!empty($rp[0])) $rankImg=ASSET_URL.'/core/main/img/lol/ranks/max/'.$rp[0].'.png'; } ?>
                        <img class="rank_icon" src="<?= $rankImg ?>" alt="rank">
                      </div>
                    </div>
                    <div class="mid"><?php if (!empty($booster['roles'])) { foreach (explode('|',$booster['roles']) as $role) echo '<span class="role-icon"><img src="'.ASSET_URL.'/core/main/img/lol/roles/'.$role.'.png" alt="'.htmlspecialchars($role).'"></span>'; } else echo '<small>'.t('No roles').'</small>'; ?></div>
                    <div class="bottom"><a class="btn btn-sm btn-primary lb-request-btn" href="<?= BASE_URL.'/lol/rank-boost' ?>"><i class="fa-solid fa-bolt"></i> <?= t('Request') ?></a></div>
                  </div>
                </div>
              </div>
              <?php endforeach ?>
              </div>
              <div class="ov-boosters-empty-online" id="ovBoostersEmptyOnline" style="display:none;"><i class="fa-duotone fa-satellite-dish"></i><span>No boosters online right now, check back shortly!</span></div>
            </div>
          </div>
        </div>
        <?php endif ?>
      </div>

      <div class="col-12 col-lg-4">
        <div class="ov2-side-stack">
          <div class="ov2-section">
            <div class="ov2-section-head"><div class="ov2-section-title"><i class="fa-duotone fa-wallet"></i> Wallet</div><a class="ov-link" href="https://lolboost.gg/points-store" target="_blank">Store</a></div>
            <div class="ov2-section-body">
              <div class="ov2-wallet">
                <div class="ov2-wallet-left">
                  <div class="ov2-wallet-icon"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/coin_purple.png" alt="LB Coins"></div>
                  <div><div class="ov2-wallet-value"><?= htmlspecialchars($ovFormatAmount($ovLbCoins)) ?></div><div class="ov2-wallet-label">LB Coins (Store Credit)</div></div>
                </div>
                <a class="btn btn-sm btn-primary" href="https://lolboost.gg/points-store" target="_blank">Spend</a>
              </div>
            </div>
          </div>

          <div class="ov2-section">
            <div class="ov2-section-head"><div class="ov2-section-title"><i class="fa-duotone fa-gift"></i> Reward Points</div><a class="ov-link" href="<?= BASE_URL ?>/profile/rewards">Open boxes</a></div>
            <div class="ov2-section-body">
              <div class="ov2-wallet">
                <div class="ov2-wallet-left">
                  <div class="ov2-wallet-icon"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/reward-points.png" alt="Reward Points"></div>
                  <div><div class="ov2-wallet-value"><?= htmlspecialchars($ovFormatAmount($ovLbPoints)) ?></div><div class="ov2-wallet-label">Lootbox currency</div></div>
                </div>
                <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>/profile/rewards">Use</a>
              </div>
            </div>
          </div>

          <div class="ov2-section">
            <div class="ov2-section-head"><div class="ov2-section-title"><i class="fa-duotone fa-award"></i> Loyalty</div></div>
            <div class="ov2-section-body">
              <div class="ov2-chips-row mb-2"></div>
              <?php $shown=0; foreach ($currRankData['rewards'] as $reward): if ($shown>=4) break; $shown++; $tip=rewardTooltip($reward,$tooltips); ?>
              <div class="ov2-loyalty-row" data-bs-toggle="tooltip" title="<?= htmlspecialchars($tip) ?>">
                <div class="ov2-loyalty-left"><?= rewardIconMini($reward,$currRankData['color']) ?><div class="ov2-loyalty-text"><?= htmlspecialchars($reward) ?></div></div>
                <div class="ov2-loyalty-status">Unlocked</div>
              </div>
              <?php endforeach ?>
              <?php if (!empty($nextRankData['rewards'])): ?>
              <div class="ov-divider my-3"></div>
              <div class="ov-muted" style="font-weight:900;font-size:.86rem;margin-bottom:8px;">Next at <span style="color:<?= $nextRankData['color'] ?>;"><?= ucfirst($next_rank['name']) ?></span></div>
              <?php foreach (array_slice($nextRankData['rewards'],0,2) as $reward): ?>
              <div class="ov2-loyalty-row" style="opacity:.72;"><div class="ov2-loyalty-left"><?= rewardIconMini($reward,$nextRankData['color']) ?><div class="ov2-loyalty-text"><?= htmlspecialchars($reward) ?></div></div><div class="ov2-loyalty-status">Locked</div></div>
              <?php endforeach ?>
              <?php endif ?>
            </div>
          </div>

          <div class="ov2-alert">
            <i class="fa-duotone fa-shield-exclamation"></i>
            <div class="ov2-alert-content">
              <div>
                <b>Stay on platform</b>
                <p>If someone contacts you outside LoLBoost, report it through live chat. You may receive up to €100 / $100 store credit.</p>
              </div>
              <div class="ov2-alert-actions">
                <a href="#open-chat" class="btn btn-sm btn-primary open-chat btn-report-clean"><i class="fa-regular fa-flag"></i>Report now</a>
              </div>
            </div>
          </div>

          <div class="ov2-section">
            <div class="ov2-section-head"><div class="ov2-section-title"><i class="fa-brands fa-discord"></i> Community</div></div>
            <div class="ov2-section-body d-grid gap-2">
              <a class="btn btn-primary" href="https://lolboost.gg/discord" target="_blank"><i class="fa-brands fa-discord me-2"></i>Join Discord</a>
              <a class="btn btn-outline-light ov2-trustpilot-btn" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank"><i class="fa-duotone fa-star me-2"></i>Rate us on Trustpilot</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>


<!-- World Cup Leaderboard Modal -->
<div class="modal fade ov-wc-modal" id="world-cup-leaderboard-modal" tabindex="-1" aria-labelledby="worldCupLeaderboardModalLabel" aria-hidden="true" data-bs-theme="dark">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="worldCupLeaderboardModalLabel">
          <i class="fa-duotone fa-trophy-star me-2" style="color:#f2cc6d;"></i>World Cup 2026 Leaderboard
        </h5>
        <button type="button" class="ov-wc-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-duotone fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="ov-wc-board-summary">
          <div class="ov-wc-board-stat">
            <div class="k">Your Rank</div>
            <div class="v"><?= $wcJoined ? ($wcRank > 0 ? '#'.$wcRank : 'Not ranked yet') : 'Not joined yet' ?></div>
          </div>
          <div class="ov-wc-board-stat">
            <div class="k">Your Points</div>
            <div class="v"><?= $wcJoined ? $wcPoints.' pts' : '0 pts' ?></div>
          </div>
          <div class="ov-wc-board-stat">
            <div class="k">Top Reward</div>
            <div class="v">50 LB Coins</div>
          </div>
          <div class="ov-wc-board-stat">
            <div class="k">Your Discount Code</div>
            <div class="v">
              <?php if ($wcDiscountCode !== ''): ?>
                <span class="ov-wc-discount-code"><i class="fa-duotone fa-badge-percent"></i><?= htmlspecialchars($wcDiscountCode) ?></span>
                <button type="button" class="btn btn-sm btn-outline-light ov-wc-modal-copy js-wc-copy" data-copy="<?= htmlspecialchars($wcDiscountCode, ENT_QUOTES) ?>"><i class="fa-duotone fa-copy me-1"></i>Copy</button>
              <?php elseif ($wcJoined): ?>
                <span style="color:rgba(255,255,255,.62);font-size:.86rem;">Unlock after your first prediction</span>
              <?php else: ?>
                <span style="color:rgba(255,255,255,.62);font-size:.86rem;">Join to unlock</span>
              <?php endif ?>
            </div>
          </div>
        </div>

        <?php if (!empty($wcLeaderboardTop)): ?>
          <div class="ov-wc-board">
            <div class="ov-wc-board-row ov-wc-board-head">
              <div>Rank</div>
              <div>Player</div>
              <div style="text-align:right;">Points</div>
              <div style="text-align:right;">Correct</div>
            </div>
            <?php foreach ($wcLeaderboardTop as $wcBoardRow): ?>
              <div class="ov-wc-board-row<?= !empty($wcBoardRow['is_me']) ? ' is-me' : '' ?>">
                <div class="ov-wc-board-rank">
                  <?php if ((int)$wcBoardRow['rank'] === 1): ?><i class="fa-duotone fa-trophy" style="color:#f2cc6d;"></i><?php endif ?>
                  #<?= (int)$wcBoardRow['rank'] ?>
                </div>
                <div class="ov-wc-board-name"><?= htmlspecialchars($wcBoardRow['is_me'] ? 'You' : $wcBoardRow['name']) ?></div>
                <div class="ov-wc-board-points"><?= (int)$wcBoardRow['points'] ?> pts</div>
                <div class="ov-wc-board-correct"><?= (int)$wcBoardRow['correct'] ?></div>
              </div>
            <?php endforeach ?>

            <?php if (false && $wcLeaderboardMe && (int)$wcLeaderboardMe['rank'] > 10): ?>
              <div class="ov-wc-board-row ov-wc-board-head" style="margin-top:4px;">
                <div>...</div><div>Your Position</div><div></div><div></div>
              </div>
              <div class="ov-wc-board-row is-me">
                <div class="ov-wc-board-rank">#<?= (int)$wcLeaderboardMe['rank'] ?></div>
                <div class="ov-wc-board-name">You</div>
                <div class="ov-wc-board-points"><?= (int)$wcLeaderboardMe['points'] ?> pts</div>
                <div class="ov-wc-board-correct"><?= (int)$wcLeaderboardMe['correct'] ?></div>
              </div>
            <?php endif ?>
          </div>
        <?php else: ?>
          <div class="ov-wc-board-empty">
            <i class="fa-duotone fa-ranking-star d-block mb-2" style="font-size:28px;color:#9aa7ff;"></i>
            The leaderboard will appear here as soon as players start earning points.
          </div>
        <?php endif ?>
      </div>
      <div class="modal-footer">
        <a href="<?= $wcUrl ?>" class="btn btn-primary"><i class="fa-duotone fa-pen-to-square me-2"></i>Open Predictions</a>
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i class="fa-duotone fa-xmark me-2"></i>Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Upload Icon Modal (inserted) -->
<div class="modal fade" id="upload-icon-modal" tabindex="-1" aria-labelledby="uploadIconModalLabel" aria-hidden="true" data-bs-theme="dark">
  <div class="modal-dialog modal-dialog-centered lb-modal-dialog">
    <div class="modal-content lb-modal-content">

      <div class="modal-header lb-modal-header">
        <h5 class="modal-title" id="uploadIconModalLabel">
          <i class="fa-duotone fa-camera"></i> Upload Icon
        </h5>
        <button type="button" class="ov-wc-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-duotone fa-xmark"></i></button>
      </div>

      <form method="post" enctype="multipart/form-data" action="">
        <div class="lb-upload">
  <div class="lb-upload__head">
    <div>
      <div class="lb-upload__title">Upload completion screenshot</div>
    </div>
    <span class="lb-upload__req">Required</span>
  </div>

  <input
    class="lb-upload__input"
    type="file"
    id="lb_completion_file"
    name="icon"
    accept="image/png,image/jpeg,image/webp,image/gif"
    required
  />

  <label class="lb-drop" for="lb_completion_file">
    <div class="lb-drop__left">
      <div class="lb-drop__icon">
        <i class="fa-duotone fa-upload"></i>
      </div>

      <div class="lb-drop__meta">
        <div class="lb-drop__label">FILE</div>
        <div class="lb-drop__name" id="lb_completion_file_name">No file selected</div>
      </div>
    </div>

    <div class="lb-drop__btn">Choose</div>
  </label>

  <div class="lb-help">
    <i class="fa-duotone fa-circle-info"></i>
    <span>Please upload a clear screenshot showing the final result.</span>
  </div>
</div>

        <div class="modal-footer lb-modal-footer">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            Submit
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {

    function formatOverviewRewardCountdown(ms) {
      const total = Math.max(0, Math.floor(ms / 1000));
      const h = String(Math.floor(total / 3600)).padStart(2, "0");
      const m = String(Math.floor((total % 3600) / 60)).padStart(2, "0");
      const s = String(total % 60).padStart(2, "0");
      return h + ":" + m + ":" + s;
    }

    function tickOverviewRewardCountdowns() {
      let shouldReload = false;
      document.querySelectorAll("[data-ov-countdown]").forEach(function (el) {
        const targetRaw = el.getAttribute("data-ov-countdown") || "";
        const target = new Date(targetRaw).getTime();
        if (!target) return;
        const diff = target - Date.now();
        el.textContent = formatOverviewRewardCountdown(diff);
        if (diff <= 0) shouldReload = true;
      });
      if (shouldReload) {
        window.setTimeout(function () { window.location.reload(); }, 600);
      }
    }
    tickOverviewRewardCountdowns();
    window.setInterval(tickOverviewRewardCountdowns, 1000);

    document.querySelectorAll(".js-wc-copy").forEach(function (btn) {
      btn.addEventListener("click", async function () {
        const code = btn.getAttribute("data-copy") || "";
        if (!code) return;
        try {
          await navigator.clipboard.writeText(code);
        } catch (e) {
          const input = document.createElement("input");
          input.value = code;
          document.body.appendChild(input);
          input.select();
          document.execCommand("copy");
          input.remove();
        }
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-duotone fa-check me-1"></i>Copied';
        btn.classList.add("is-copied");
        setTimeout(function () {
          btn.innerHTML = oldHtml;
          btn.classList.remove("is-copied");
        }, 1600);
      });
    });

    // ── #10 Trustpilot stars: hover preview + click → redirect ──────────
    const stars   = document.querySelectorAll("#tp-stars .star");
    const tpHint  = document.getElementById("tp-hint");
    const labels  = ["Terrible","Poor","Average","Great","Excellent"];

    function highlightStars(n) {
      stars.forEach((s, i) => s.setAttribute("fill", i < n ? "#00b67a" : "none"));
    }
    stars.forEach((star, idx) => {
      star.addEventListener("mouseover", () => {
        highlightStars(idx + 1);
        if (tpHint) tpHint.textContent = labels[idx] + " — click to submit your review";
      });
      star.addEventListener("mouseout", () => {
        highlightStars(0);
        if (tpHint) tpHint.textContent = "Click a star to leave a review — it takes less than a minute.";
      });
      star.addEventListener("click", () => {
        window.open("https://www.trustpilot.com/evaluate/lolboost.gg", "_blank", "noopener");
      });
    });

    // ── #1 Scam alert dismiss (localStorage) ────────────────────────────
    // Scam alert is always visible - dismiss removed

    // ── #8 Avatar upload: also works via the edit-button on touch ────────
    const editBtn = document.querySelector(".edit-icon-container");
    if (editBtn) {
      // Ensure the hover overlay activates when focusing the button (keyboard/touch)
      editBtn.addEventListener("focus", function () {
        const overlay = document.querySelector(".avatar-upload");
        if (overlay) { overlay.classList.remove("d-none"); overlay.style.opacity = "1"; }
      });
      editBtn.addEventListener("blur", function () {
        const overlay = document.querySelector(".avatar-upload");
        if (overlay) { overlay.classList.add("d-none"); overlay.style.opacity = ""; }
      });
    }

  });
</script>

<?= $this->start('scripts') ?>
<script>
  $('.avatar').mouseover(function () {
    $('.avatar-upload').stop().fadeIn(100);
    $('.avatar-upload').removeClass('d-none');
  });

  $('.avatar').mouseout(function () {
    $('.avatar-upload').stop().fadeOut(200, function () {
      $(this).addClass('d-none');
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".open-chat").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();

        if (window.Tawk_API && typeof window.Tawk_API.maximize === "function") {
          window.Tawk_API.maximize();
          return;
        }

        const start = Date.now();
        const timer = setInterval(function () {
          if (window.Tawk_API && typeof window.Tawk_API.maximize === "function") {
            clearInterval(timer);
            window.Tawk_API.maximize();
          } else if (Date.now() - start > 6000) {
            clearInterval(timer);
          }
        }, 200);
      });
    });
  });
</script>

<script>
  // Top Boosters row: drag with mouse AND finger + floating tooltips for "+X" champions (not clipped).
  document.addEventListener("DOMContentLoaded", function () {

    // ===== Drag-to-scroll (Pointer Events: mouse + touch) =====
    document.querySelectorAll(".ov-top-boosters-list").forEach(function (row) {
      let isDown = false;
      let startX = 0;
      let startScrollLeft = 0;
      let moved = false;
      let pointerId = null;

      // interactive elements inside cards that should keep normal click behavior
      const isInteractive = (target) => !!target.closest("button, input, textarea, select, label, .lb-request-btn, .ov-scroll-btn, .btn, [data-no-drag]");

      const onPointerDown = function (e) {
        if (e.pointerType === "mouse" && e.button !== 0) return;
        if (isInteractive(e.target)) return;

        // prevent the browser from starting text selection / native drag
        e.preventDefault();

        isDown = true;
        moved = false;
        pointerId = e.pointerId;

        row.dataset.ovDragged = "0";
        row.classList.add("dragging");
        try { row.setPointerCapture(pointerId); } catch (err) {}

        startX = e.clientX;
        startScrollLeft = row.scrollLeft;
      };

      const onPointerMove = function (e) {
        if (!isDown || e.pointerId !== pointerId) return;
        const dx = e.clientX - startX;
        if (Math.abs(dx) > 4) {
          moved = true;
          row.dataset.ovDragged = "1";
        }
        row.scrollLeft = startScrollLeft - dx;
        if (moved) e.preventDefault();
      };

      const endDrag = function () {
        if (!isDown) return;
        isDown = false;
        row.classList.remove("dragging");
        try { row.releasePointerCapture(pointerId); } catch (err) {}
        pointerId = null;

        // clear dragged-flag shortly after to allow normal clicks again
        if (row.dataset.ovDragged === "1") {
          setTimeout(function(){ row.dataset.ovDragged = "0"; }, 120);
        }
      };

      // capture:true makes sure we still start dragging even if inner elements register handlers
      row.addEventListener("pointerdown", onPointerDown, { passive: false, capture: true });
      row.addEventListener("pointermove", onPointerMove, { passive: false });
      row.addEventListener("pointerup", endDrag);
      row.addEventListener("pointercancel", endDrag);

      // Make vertical wheel scroll horizontally when hovering the row (nice UX on desktop)
      row.addEventListener("wheel", function (e) {
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
        row.scrollLeft += e.deltaY;
        e.preventDefault();
      }, { passive: false });

      // Card click navigation (only when not dragged)
      row.querySelectorAll(".ov-booster-card-link[data-href]").forEach(function (card) {
        card.addEventListener("click", function (ev) {
          if (row.dataset.ovDragged === "1") {
            ev.preventDefault();
            ev.stopPropagation();
            return;
          }
          const href = card.getAttribute("data-href");
          if (!href) return;
          window.open(href, "_blank", "noopener");
        });

        card.addEventListener("keydown", function (ev) {
          if (ev.key !== "Enter") return;
          const href = card.getAttribute("data-href");
          if (href) window.open(href, "_blank", "noopener");
        });
      });
    });




    // ===== Arrow buttons (scroll left/right) =====
    const bindScrollButtons = function (target) {
      if (!target || !target.id) return;

      const prevBtn = document.querySelector('[data-ov-scroll="prev"][data-ov-target="#' + target.id + '"]');
      const nextBtn = document.querySelector('[data-ov-scroll="next"][data-ov-target="#' + target.id + '"]');

      const update = function () {
        const max = Math.max(0, target.scrollWidth - target.clientWidth);
        if (prevBtn) prevBtn.disabled = target.scrollLeft <= 4;
        if (nextBtn) nextBtn.disabled = target.scrollLeft >= (max - 4);
      };

      // expose for filters to re-evaluate after DOM changes
      target._ovUpdateButtons = update;

      const scrollByAmount = function (dir) {
        const amt = Math.max(240, Math.floor(target.clientWidth * 0.85));
        target.scrollBy({ left: dir === "prev" ? -amt : amt, behavior: "smooth" });
      };

      if (prevBtn) prevBtn.addEventListener("click", function () { scrollByAmount("prev"); });
      if (nextBtn) nextBtn.addEventListener("click", function () { scrollByAmount("next"); });

      target.addEventListener("scroll", update, { passive: true });
      window.addEventListener("resize", update);
      update();
    };

    document.querySelectorAll(".ov-top-boosters-list").forEach(function (el) {
      bindScrollButtons(el);
    });

    // ── #7 Online empty-state: show message when filter yields 0 results ──
    const boosterRow      = document.getElementById("ovTopBoostersRow");
    const boosterEmpty    = document.getElementById("ovBoostersEmptyOnline");

    function updateBoosterEmptyState(mode) {
      if (!boosterRow || !boosterEmpty) return;
      if (mode !== "online") { boosterEmpty.style.display = "none"; return; }
      const visible = boosterRow.querySelectorAll("[data-ov-online='1']:not([hidden])").length;
      boosterEmpty.style.display = visible === 0 ? "flex" : "none";
    }

    // ── #15 Keyboard arrow-key scrolling on booster row ──────────────────
    if (boosterRow) {
      boosterRow.addEventListener("keydown", function (e) {
        const amt = 200;
        if (e.key === "ArrowRight") { boosterRow.scrollBy({ left: amt, behavior: "smooth" }); e.preventDefault(); }
        if (e.key === "ArrowLeft")  { boosterRow.scrollBy({ left: -amt, behavior: "smooth" }); e.preventDefault(); }
      });
    }

    // ── #12 Recent Orders quick status filter ────────────────────────────
    const orderFilterWrap = document.getElementById("ovOrderFilterWrap");
    const orderList       = document.querySelector(".ov-order-list");

    if (orderFilterWrap && orderList) {
      orderFilterWrap.querySelectorAll("[data-ov-order-filter]").forEach(function (btn) {
        btn.addEventListener("click", function () {
          orderFilterWrap.querySelectorAll("[data-ov-order-filter]").forEach(function (b) { b.classList.remove("active"); });
          btn.classList.add("active");
          const mode = btn.getAttribute("data-ov-order-filter");
          orderList.querySelectorAll(".ov-order-row").forEach(function (row) {
            if (mode === "all") { row.hidden = false; return; }
            const status = (row.getAttribute("data-ov-status") || "").toLowerCase();
            row.hidden = (status !== mode);
          });
        });
      });
    }

    // ===== Filter: show Alle / Online only =====
    document.querySelectorAll('.ov-filter-toggle').forEach(function(toggle){
      const targetSel = toggle.getAttribute('data-ov-filter-controls');
      const target = targetSel ? document.querySelector(targetSel) : null;
      if (!target) return;

      const buttons = toggle.querySelectorAll('.ov-filter-btn');

      const apply = function(mode){
        target.querySelectorAll('[data-ov-online]').forEach(function(el){
          const online = (el.getAttribute('data-ov-online') === '1');
          el.hidden = (mode === 'online') ? !online : false;
        });
        target.scrollLeft = 0;
        if (typeof target._ovUpdateButtons === 'function') target._ovUpdateButtons();
      };

      buttons.forEach(function(btn){
        btn.addEventListener('click', function(){
          buttons.forEach(function(b){ b.classList.remove('active'); });
          btn.classList.add('active');
          const mode = btn.getAttribute('data-ov-filter') || 'all';
          apply(mode);
          if (typeof updateBoosterEmptyState === 'function') updateBoosterEmptyState(mode);
        });
      });
    });


    // ===== Floating tooltip for "+X" champions =====
    const tooltip = document.createElement("div");
    tooltip.className = "ov-floating-tooltip";
    document.body.appendChild(tooltip);

    let pinnedEl = null;
    let activeEl = null;
    let hideTimer = null;

    const setTooltipText = function (text) {
      // simple formatting: comma separated list -> wrap nicely
      tooltip.textContent = text || "";
    };

    const positionTooltip = function (anchor) {
      if (!anchor) return;
      const rect = anchor.getBoundingClientRect();

      tooltip.style.left = "0px";
      tooltip.style.top = "0px";
      tooltip.classList.add("show");

      // measure
      const tRect = tooltip.getBoundingClientRect();

      let left = rect.left + rect.width / 2 - tRect.width / 2;
      left = Math.max(12, Math.min(left, window.innerWidth - tRect.width - 12));

      // Prefer above; if not enough space, show below
      const aboveTop = rect.top - tRect.height - 10;
      const belowTop = rect.bottom + 10;
      let top = aboveTop;
      if (top < 12) top = Math.min(belowTop, window.innerHeight - tRect.height - 12);

      tooltip.style.left = left + "px";
      tooltip.style.top = top + "px";
    };

    const showTooltip = function (el) {
      if (!el) return;
      const text = el.getAttribute("data-tooltip") || "";
      if (!text.trim()) return;

      clearTimeout(hideTimer);
      activeEl = el;
      setTooltipText(text);
      tooltip.classList.add("show");
      positionTooltip(el);
    };

    const hideTooltip = function (force) {
      if (pinnedEl && !force) return;
      activeEl = null;
      tooltip.classList.remove("show");
    };

    const scheduleHide = function () {
      clearTimeout(hideTimer);
      hideTimer = setTimeout(function () { hideTooltip(false); }, 120);
    };

    // Hover (desktop)
    document.addEventListener("mouseover", function (e) {
      const el = e.target.closest(".more-champions-icon[data-tooltip]");
      if (!el) return;
      if (pinnedEl && pinnedEl !== el) return; // don't override pinned tooltip
      showTooltip(el);
    });

    document.addEventListener("mouseout", function (e) {
      const el = e.target.closest(".more-champions-icon[data-tooltip]");
      if (!el) return;
      if (pinnedEl) return;
      scheduleHide();
    });

    // Keep tooltip visible when hovering it
    tooltip.addEventListener("mouseenter", function () {
      clearTimeout(hideTimer);
    });
    tooltip.addEventListener("mouseleave", function () {
      if (!pinnedEl) scheduleHide();
    });

    // Tap/click to pin (mobile + desktop)
    document.addEventListener("click", function (e) {
      const el = e.target.closest(".more-champions-icon[data-tooltip]");
      if (!el) {
        pinnedEl = null;
        hideTooltip(true);
        return;
      }

      // toggle pin
      if (pinnedEl === el) {
        pinnedEl = null;
        hideTooltip(true);
      } else {
        pinnedEl = el;
        showTooltip(el);
      }

      e.preventDefault();
      e.stopPropagation();
    }, true);

    // Reposition on scroll/resize (page + horizontal row)
    const reposition = function () {
      const el = pinnedEl || activeEl;
      if (!el || !tooltip.classList.contains("show")) return;
      positionTooltip(el);
    };

    window.addEventListener("scroll", reposition, { passive: true });
    window.addEventListener("resize", reposition);
    document.querySelectorAll(".ov-top-boosters-list").forEach(function (row) {
      row.addEventListener("scroll", reposition, { passive: true });
    });

  });
</script>


<script>
  // Make Recent Orders rows clickable (without breaking inner links/buttons)
  document.addEventListener('click', function(e){
    const row = e.target.closest('.ov-order-row.ov-clickable');
    if (!row) return;
    if (e.target.closest('a, button, input, select, textarea, [role="button"]')) return;

    const link = row.querySelector('a[href]');
    if (link && link.href) window.location.href = link.href;
  });
</script>

<?= $this->stop('scripts') ?>
