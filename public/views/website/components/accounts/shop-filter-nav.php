<style id="lb-desktop-shop-filter-nav">
#lbShopFilterNav{display:none;}

@media (max-width:767px){
  html body.ranked-accounts-page section.lb-shop-hero > .lb-shop-hero__inner{
    min-height:126px!important;
    padding:24px 0 18px!important;
    gap:40px!important;
  }

  html body.ranked-accounts-page .page-zoom > main > .container{
    width:100%!important;
    max-width:100%!important;
    margin-left:0!important;
    margin-right:0!important;
    padding-left:10px!important;
    padding-right:10px!important;
    box-sizing:border-box!important;
  }

  html body.ranked-accounts-page .page-zoom > main > .container #shopFilterbar.shop-filterbar{
    width:calc(100% + 20px)!important;
    max-width:none!important;
    margin-left:-10px!important;
    margin-right:-10px!important;
    padding-top:12px!important;
    padding-bottom:12px!important;
    border-left:0!important;
    border-right:0!important;
    border-radius:0!important;
    box-sizing:border-box!important;
  }

  html body.ranked-accounts-page .page-zoom > main > .container #accountsGrid.accounts-grid{
    width:100%!important;
    max-width:none!important;
  }

  /* The opened filter sheet belongs to the viewport, not to the inset shop container. */
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar.shop-filterbar #shopFilters.shop-filterbar__form{
    position:fixed!important;
    inset:0!important;
    width:100dvw!important;
    min-width:0!important;
    max-width:100dvw!important;
    height:100dvh!important;
    min-height:100dvh!important;
    max-height:100dvh!important;
    margin:0!important;
    padding:14px 12px calc(18px + env(safe-area-inset-bottom))!important;
    border-radius:0!important;
    background:#050711!important;
    background-image:none!important;
    box-sizing:border-box!important;
    overflow-x:hidden!important;
    overflow-y:auto!important;
    overscroll-behavior:contain!important;
    transform:none!important;
    -webkit-backdrop-filter:none!important;
    backdrop-filter:none!important;
    isolation:isolate!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilterbar.shop-filterbar{
    background:#050711!important;
    background-image:none!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .lb-mobile-filter-sheet-head,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .lb-mobile-sort-section,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .shop-filterpill{
    width:100%!important;
    min-width:0!important;
    max-width:100%!important;
    box-sizing:border-box!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .lb-mobile-sort-section__options{
    width:100%!important;
    min-width:0!important;
    max-width:100%!important;
    overflow-x:auto!important;
    overflow-y:hidden!important;
    overscroll-behavior-x:contain!important;
  }

  /* Mobile facets: exactly two rows, then swipe horizontally. */
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .facet-scroll,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters #ddRank .facet-scroll,
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters #ddServer .facet-scroll{
    display:grid!important;
    grid-template-columns:none!important;
    grid-template-rows:repeat(2,minmax(38px,auto))!important;
    grid-auto-flow:column!important;
    grid-auto-columns:max-content!important;
    align-items:center!important;
    gap:8px!important;
    width:100%!important;
    min-width:0!important;
    max-width:100%!important;
    max-height:none!important;
    margin:0!important;
    padding:2px 2px 9px!important;
    overflow-x:auto!important;
    overflow-y:hidden!important;
    scrollbar-width:none!important;
    -webkit-overflow-scrolling:touch!important;
    scroll-snap-type:x proximity!important;
    overscroll-behavior-x:contain!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .facet-scroll::-webkit-scrollbar{
    display:none!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .facet-scroll .facet-item{
    width:max-content!important;
    max-width:none!important;
    margin:0!important;
    scroll-snap-align:start!important;
  }

  /* Keep the sheet controls above every facet while the sheet itself scrolls. */
  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .lb-mobile-filter-sheet-head{
    position:sticky!important;
    top:-14px!important;
    z-index:100004!important;
    display:flex!important;
    align-items:center!important;
    min-height:68px!important;
    margin:-14px -12px 14px!important;
    padding:calc(14px + env(safe-area-inset-top)) 12px 12px!important;
    width:calc(100% + 24px)!important;
    max-width:none!important;
    background:#080b16!important;
    border-bottom:1px solid rgba(129,140,248,.18)!important;
    box-shadow:0 12px 24px rgba(0,0,0,.38)!important;
    isolation:isolate!important;
  }

  html body.ranked-accounts-page.lb-shop-filters-open #shopFilters .lb-mobile-sort-section{
    position:relative!important;
    z-index:1!important;
  }

  /* The compact bar must stay opaque when cards move underneath it. */
  html body.ranked-accounts-page #shopFilterbar.shop-filterbar,
  html body.shop-mobile-filterbar-fixed.ranked-accounts-page #shopFilterbar.shop-filterbar,
  html body.shop-mobile-filterbar-fixed .ranked-accounts-page #shopFilterbar.shop-filterbar{
    background:#080b16!important;
    background-image:none!important;
    -webkit-backdrop-filter:none!important;
    backdrop-filter:none!important;
    box-shadow:0 10px 24px rgba(0,0,0,.34)!important;
    isolation:isolate!important;
  }

  html body.ranked-accounts-page #shopFilterbar .shop-filterbar__row{
    position:relative!important;
    z-index:2!important;
    background:#080b16!important;
  }
}

@media (min-width:1025px){
  body.ranked-accounts-page #shopFilterbar.shop-filterbar--sticky{
    position:relative!important;
    top:auto!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active nav.navbar-top,
  body.ranked-accounts-page.lb-shop-filter-nav-active #lbSaleBanner,
  body.ranked-accounts-page.lb-shop-filter-nav-active #lbGiveawayBanner,
  body.ranked-accounts-page.lb-shop-filter-nav-active .lb-game-subnav{
    display:none!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav{
    position:fixed!important;
    inset:0 0 auto 0!important;
    z-index:100050!important;
    display:block!important;
    width:100%!important;
    background:#050814!important;
    border-bottom:1px solid rgba(255,255,255,.09)!important;
    box-shadow:0 14px 38px rgba(0,0,0,.36)!important;
    animation:lbShopFilterNavIn .2s ease-out both;
  }

  @keyframes lbShopFilterNavIn{
    from{opacity:0;transform:translateY(-8px);}
    to{opacity:1;transform:translateY(0);}
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav #shopFilterbar{
    position:relative!important;
    inset:auto!important;
    width:100%!important;
    max-width:none!important;
    margin:0!important;
    padding:11px clamp(24px,4vw,76px)!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
    box-shadow:none!important;
    backdrop-filter:none!important;
    -webkit-backdrop-filter:none!important;
    overflow:visible!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav .shop-filterbar__form{
    width:min(1480px,100%)!important;
    margin:0 auto!important;
  }

  body.items-shop-page.lb-shop-filter-nav-active #lbShopFilterNav #shopFilters{
    width:min(1480px,100%)!important;
    margin:0 auto!important;
  }

  body.items-shop-page.lb-shop-filter-nav-active #lbShopFilterNav .ifb-row--top{
    flex-wrap:nowrap!important;
    justify-content:center!important;
    width:100%!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav .shop-filterbar__row{
    flex-wrap:nowrap!important;
    justify-content:center!important;
    width:100%!important;
    gap:9px!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav .shop-filterbar__search{
    flex:0 1 380px!important;
    width:clamp(280px,24vw,380px)!important;
    max-width:380px!important;
    min-width:280px!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav .shop-filterbar__actions{
    flex:0 0 auto!important;
    width:auto!important;
    margin-left:7px!important;
    padding-left:16px!important;
    gap:9px!important;
    position:relative!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav .shop-filterbar__actions::before{
    content:""!important;
    position:absolute!important;
    left:0!important;
    top:7px!important;
    bottom:7px!important;
    width:1px!important;
    background:rgba(255,255,255,.14)!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav #activeFilters{
    justify-content:flex-start!important;
    width:100%!important;
    margin-left:auto!important;
    margin-right:auto!important;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterNav #activeFilters:has(.active-filters__hint){
    display:none!important;
  }

  #lbShopFilterPlaceholder{
    display:none;
  }

  body.ranked-accounts-page.lb-shop-filter-nav-active #lbShopFilterPlaceholder{
    display:block;
    width:100%;
    height:calc(var(--lb-shop-filter-height,70px) + var(--lb-shop-hidden-chrome-height,0px));
  }
}
</style>

<script>
(function(){
  if(window.__lbDesktopShopFilterNavInstalled) return;
  window.__lbDesktopShopFilterNavInstalled=true;

  var body=document.body;
  var bar=document.getElementById('shopFilterbar');
  var nav=document.querySelector('nav.navbar-top');
  var desktop=window.matchMedia('(min-width:1025px)');
  if(!body||!bar||!nav) return;

  var originalParent=bar.parentNode;
  var originalNext=bar.nextSibling;
  var shell=document.createElement('div');
  var placeholder=document.createElement('div');
  var triggerY=0;
  var triggerReady=false;
  var active=false;
  var ticking=false;

  shell.id='lbShopFilterNav';
  shell.setAttribute('role','navigation');
  shell.setAttribute('aria-label','Shop filters');
  placeholder.id='lbShopFilterPlaceholder';
  shell.appendChild(document.createComment('Shop filter navigation'));
  body.appendChild(shell);

  function closeDropdowns(){
    var open=bar.querySelectorAll('.shop-dropdown.is-open');
    for(var i=0;i<open.length;i++) open[i].classList.remove('is-open');
    var expanded=bar.querySelectorAll('[aria-expanded="true"]');
    for(var j=0;j<expanded.length;j++) expanded[j].setAttribute('aria-expanded','false');
  }

  function activate(){
    if(active||!desktop.matches) return;
    active=true;
    var hiddenChromeHeight=0;
    [
      nav,
      document.getElementById('lbSaleBanner'),
      document.getElementById('lbGiveawayBanner'),
      document.querySelector('.lb-game-subnav')
    ].forEach(function(el){
      if(!el) return;
      var styles=window.getComputedStyle(el);
      if(styles.display==='none'||styles.visibility==='hidden') return;
      hiddenChromeHeight+=Math.max(0,Math.ceil(el.getBoundingClientRect().height||el.offsetHeight||0));
    });
    document.documentElement.style.setProperty('--lb-shop-filter-height',Math.ceil(bar.offsetHeight)+'px');
    document.documentElement.style.setProperty('--lb-shop-hidden-chrome-height',hiddenChromeHeight+'px');
    originalParent.insertBefore(placeholder,bar);
    shell.appendChild(bar);
    body.classList.add('lb-shop-filter-nav-active');
  }

  function deactivate(){
    if(!active) return;
    active=false;
    closeDropdowns();
    body.classList.remove('lb-shop-filter-nav-active');
    if(originalNext&&originalNext.parentNode===originalParent){
      originalParent.insertBefore(bar,originalNext);
    }else{
      originalParent.appendChild(bar);
    }
    if(placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
    document.documentElement.style.removeProperty('--lb-shop-filter-height');
    document.documentElement.style.removeProperty('--lb-shop-hidden-chrome-height');
  }

  function measure(){
    if(active) deactivate();
    if(!desktop.matches) return;
    /*
     * Switch only when the actual results reach the navigation. Using the
     * filterbar itself here made the navbar disappear while the user was still
     * scrolling through the hero, quick filters and result summary.
     */
    var listings=document.getElementById('accountsGrid')||document.getElementById('itemsGrid');
    if(!listings){
      triggerReady=false;
      return;
    }
    var listingsTop=listings.getBoundingClientRect().top+window.scrollY;
    var navHeight=Math.max(0,nav.getBoundingClientRect().height||nav.offsetHeight||0);
    triggerY=Math.max(0,listingsTop-navHeight);
    triggerReady=true;
  }

  function update(){
    ticking=false;
    if(!desktop.matches){
      deactivate();
      return;
    }
    if(!triggerReady) return;
    /*
     * Use separate enter/leave thresholds. Without this dead zone, tiny layout
     * and sub-pixel scroll changes at the boundary can toggle both navs rapidly.
     */
    if(!active&&window.scrollY>=triggerY+10) activate();
    else if(active&&window.scrollY<=Math.max(0,triggerY-64)) deactivate();
  }

  function requestUpdate(){
    if(ticking) return;
    ticking=true;
    window.requestAnimationFrame(update);
  }

  measure();
  update();
  document.addEventListener('DOMContentLoaded',function(){
    measure();
    requestUpdate();
  },{once:true});
  window.addEventListener('load',function(){
    measure();
    requestUpdate();
  },{once:true});
  window.addEventListener('scroll',requestUpdate,{passive:true});
  window.addEventListener('resize',function(){
    measure();
    requestUpdate();
  },{passive:true});
  if(desktop.addEventListener){
    desktop.addEventListener('change',function(){
      measure();
      requestUpdate();
    });
  }
})();
</script>
