// ===== (original line 8894) =====

(function(){
  var nav = document.querySelector('.gmUnifiedNav');
  var menu = nav ? nav.querySelector('.gmUnifiedMega') : null;
  var trigger = nav ? nav.querySelector('.gmUnifiedTrigger') : null;
  if (!nav || !menu || !trigger) return;
  var timer = null;
  function place(){
    var rect = trigger.getBoundingClientRect();
    var navRect = nav.getBoundingClientRect();
    var width = Math.min(1180, window.innerWidth - 96);
    var left = Math.max(48, Math.min(rect.left - 52, window.innerWidth - width - 48));
    menu.style.width = width + 'px';
    menu.style.left = Math.round(left - navRect.left) + 'px';
  }
  function open(){ if(timer) clearTimeout(timer); place(); nav.classList.add('is-market-open'); }
  function close(){ timer = setTimeout(function(){ nav.classList.remove('is-market-open'); }, 160); }
  nav.addEventListener('mouseenter', open);
  nav.addEventListener('mouseleave', close);
  trigger.addEventListener('focus', open);
  nav.addEventListener('focusout', function(e){ if (!nav.contains(e.relatedTarget)) close(); });
  var browseToggle = nav.querySelector('.gmUnifiedBrowseToggle');
  var gamesSection = nav.querySelector('.gmUnifiedSection--games');
  if (browseToggle && gamesSection) {
    var browsePanel = nav.querySelector('#gmUnifiedBrowsePanel');
    var browseClose = nav.querySelector('.gmUnifiedBrowseClose');
    function setBrowseOpen(openNow){
      gamesSection.classList.toggle('is-browse-open', openNow);
      browseToggle.setAttribute('aria-expanded', openNow ? 'true' : 'false');
      if (browsePanel) browsePanel.setAttribute('aria-hidden', openNow ? 'false' : 'true');
    }
    browseToggle.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      setBrowseOpen(!gamesSection.classList.contains('is-browse-open'));
    });
    if (browseClose) browseClose.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      setBrowseOpen(false);
    });
    document.addEventListener('click', function(e){
      if (!nav.contains(e.target)) setBrowseOpen(false);
    });
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape') setBrowseOpen(false);
    });
  }
  var mini = nav.querySelector('[data-gm-header-search-open]');
  if (mini) mini.addEventListener('click', function(e){
    e.preventDefault();
    var opener = document.querySelector('[data-lb-search-open], [data-gm-search-open], .lb-header-search, .gmHeaderSearchTrigger');
    var overlay = document.getElementById('gmHeaderSearchOverlay');
    /* The full command-center script below owns opening/positioning.
       Do not pre-open it here, otherwise it flashes at the fallback position first. */
    if (overlay && overlay.querySelector('.gmHeaderCommandCenter')) { return; }
    var input = document.getElementById('gmHeaderSearchInput');
    if (overlay) { overlay.classList.add('is-open'); overlay.setAttribute('aria-hidden', 'false'); document.documentElement.classList.add('gm-search-open'); document.body.classList.add('gm-search-open'); setTimeout(function(){ if(input) input.focus(); }, 30); }
    else if (opener && opener.click) { opener.click(); }
  });
  window.addEventListener('resize', place);
  window.addEventListener('scroll', place, true);
  place();
})();

// Hide the promotional banner while reading the page. It returns only near
// the top; the explicit close button continues to use the permanent hidden state.
(function () {
  var banner = document.getElementById('lbSaleBanner');
  var root = document.documentElement;
  if (!banner || !root) return;

  var hiddenByScroll = false;
  var framePending = false;

  function publishHeight(height) {
    root.style.setProperty('--lb-sale-h', height + 'px');
    root.style.setProperty('--mobile-banner-offset', height + 'px');
    // Mirror the exact close-button behavior. Some legacy header code writes
    // an inline !important top value, so the CSS variable alone cannot win.
    var navs = document.querySelectorAll('nav.navbar-top, nav.navbar-mobile, .navbar-top, .navbar-mobile');
    for (var i = 0; i < navs.length; i++) {
      var nav = navs[i];
      if (!nav) continue;
      var cs = window.getComputedStyle ? getComputedStyle(nav) : nav.currentStyle;
      if (cs && cs.position === 'fixed') nav.style.setProperty('top', height + 'px', 'important');
    }
    try {
      window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: height } }));
    } catch (e) {}
  }

  function update() {
    framePending = false;
    if (root.classList.contains('lb-sale-banner-hidden') || banner.style.display === 'none') return;

    var y = window.pageYOffset || root.scrollTop || 0;
    var shouldHide = hiddenByScroll ? y > 24 : y > 100;
    var desiredHeight = shouldHide ? 0 : (banner.offsetHeight || 0);
    var publishedHeight = parseFloat(root.style.getPropertyValue('--lb-sale-h')) || 0;
    if (shouldHide === hiddenByScroll && Math.abs(publishedHeight - desiredHeight) < 1) return;

    hiddenByScroll = shouldHide;
    root.classList.toggle('lb-sale-banner-scrolled', hiddenByScroll);
    publishHeight(desiredHeight);
  }

  function scheduleUpdate() {
    if (framePending) return;
    framePending = true;
    window.requestAnimationFrame(update);
  }

  window.addEventListener('scroll', scheduleUpdate, { passive: true });
  window.addEventListener('resize', scheduleUpdate, { passive: true });
  update();
})();

// ===== (original line 11261) =====

(function(){
  var banner = document.getElementById('lbSaleBanner');
  if (!banner) return;
  // If banner is intentionally hidden via CSS toggle, do nothing
  if (window.getComputedStyle(banner).display === 'none') return;

  var bar = document.getElementById('lbSaleBar');
  var link = banner.getAttribute('data-link') || '/egirls';
  var closeBtns = banner.querySelectorAll('[data-lb-close]');

  // bump key so old caches don't keep "closed" unexpectedly
  var LS_KEY = 'lb_season_banner_closed_v12';

  function safeGetLS(k){ try { return localStorage.getItem(k); } catch(e){ return null; } }
  function safeSetLS(k,v){ try { localStorage.setItem(k,v); } catch(e){} }

  // Already closed?
  if (safeGetLS(LS_KEY) === '1') {
    banner.style.display = 'none';
    return;
  }

  // ---------- Keep banner ABOVE fixed headers (mobile + desktop) ----------
  var root = document.documentElement;

  // ── Clean CSS-var approach: set --lb-sale-h = banner height when visible ──
  function lbSetSaleVar(on) {
    var h = (on && banner) ? (banner.offsetHeight || 0) : 0;
    root.style.setProperty('--lb-sale-h', h + 'px');
    root.style.setProperty('--mobile-banner-offset', h + 'px');
  }

  // Banner is fixed at top:0 — navbar sits below it via --lb-sale-h
  // When scrolling, keep banner visible until user explicitly closes it
  // (no scroll-based hiding — banner only hides when closed)
  lbSetSaleVar(true);
  window.addEventListener('resize', function() { lbSetSaleVar(true); });
  window.requestAnimationFrame(function() { lbSetSaleVar(true); });
  setTimeout(function() { lbSetSaleVar(true); }, 100);

  // ---------- Mobile: whole bar is a link (except close) ----------
  function isMobile(){
    if (window.matchMedia) return window.matchMedia('(max-width: 560px)').matches;
    return (window.innerWidth || document.documentElement.clientWidth || 9999) <= 560;
  }

  if (bar) {
    if (bar.classList) bar.classList.add('is-link');
    else bar.className += ' is-link';

    bar.onclick = function(e){
      e = e || window.event;
      if (!isMobile()) return;

      // ignore clicks on close button
      var t = e.target || e.srcElement;
      while (t && t !== bar){
        if (t.getAttribute && t.getAttribute('data-lb-close') !== null) return;
        t = t.parentNode;
      }
      window.location.href = link;
    };
  }

  for (var i=0;i<closeBtns.length;i++){
    closeBtns[i].onclick = function(e){
      e = e || window.event;
      if (e.preventDefault) e.preventDefault();
      if (e.stopPropagation) e.stopPropagation();
      banner.style.display = 'none';
      safeSetLS(LS_KEY, '1');
      lbSetSaleVar(false);
      try { window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: 0 } })); } catch(err) {}
      return false;
    };
  }

  // ---------- Countdown ----------
  function parseEndsAt(){
    var raw = (banner.getAttribute('data-ends-at') || '');
    raw = raw.replace(/^\s+|\s+$/g,'');
    if (!raw) return null;

    // Prefer ISO like 2026-01-07T23:59:59 (iOS-safe manual parse)
    var iso = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T\s](\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/);
    if (iso){
      var y = parseInt(iso[1],10);
      var mo = parseInt(iso[2],10) - 1;
      var da = parseInt(iso[3],10);
      var hh = parseInt(iso[4] || '0',10);
      var mm = parseInt(iso[5] || '0',10);
      var ss = parseInt(iso[6] || '0',10);
      var d = new Date(y, mo, da, hh, mm, ss);
      if (!isNaN(d.getTime())) return d;
    }

    // Very forgiving: grab YYYY MM DD and optional HH MM SS from any format
    var m = raw.match(/(\d{4})\D(\d{1,2})\D(\d{1,2})(?:\D+(\d{1,2})\D(\d{1,2})(?:\D(\d{1,2}))?)?/);
    if (m){
      var y2 = parseInt(m[1],10);
      var mo2 = parseInt(m[2],10) - 1;
      var da2 = parseInt(m[3],10);
      var hh2 = parseInt(m[4] || '0',10);
      var mm2 = parseInt(m[5] || '0',10);
      var ss2 = parseInt(m[6] || '0',10);
      var d2 = new Date(y2, mo2, da2, hh2, mm2, ss2);
      if (!isNaN(d2.getTime())) return d2;
    }

    // Fallback native parsing
    var d3 = new Date(raw);
    if (!isNaN(d3.getTime())) return d3;

    return null;
  }

  var endsAt = parseEndsAt();
  var mobileEl = document.getElementById('lbSaleCountdownMobile');
  var dEl = document.getElementById('lbSaleD');
  var hEl = document.getElementById('lbSaleH');
  var mEl = document.getElementById('lbSaleM');
  var sEl = document.getElementById('lbSaleS');
  var mobDEl = document.getElementById('lbMobD');
  var mobHEl = document.getElementById('lbMobH');
  var mobMEl = document.getElementById('lbMobM');
  var mobSEl = document.getElementById('lbMobS');

  function pad2(n){ n = String(n); return (n.length < 2) ? ('0' + n) : n; }
  function setText(el, val){ if (el) el.textContent = val; }

  function tick(){
    if (!endsAt) {
      setText(dEl, '00'); setText(hEl, '00'); setText(mEl, '00'); setText(sEl, '00');
      setText(mobDEl, '00'); setText(mobHEl, '00'); setText(mobMEl, '00'); setText(mobSEl, '00');
      return;
    }

    var diff = endsAt.getTime() - (new Date()).getTime();
    if (!isFinite(diff) || diff < 0) diff = 0;

    var total = Math.floor(diff / 1000);
    var days = Math.floor(total / 86400);
    var hours = Math.floor((total % 86400) / 3600);
    var mins = Math.floor((total % 3600) / 60);
    var secs = total % 60;

    setText(dEl, String(days));
    setText(hEl, pad2(hours));
    setText(mEl, pad2(mins));
    setText(sEl, pad2(secs));

    setText(mobDEl, String(days));
    setText(mobHEl, pad2(hours));
    setText(mobMEl, pad2(mins));
    setText(mobSEl, pad2(secs));
  }

  tick();
  try { setInterval(tick, 1000); } catch(e){}
})();

// ===== (original line 12154) =====
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-596N9MJ3');

// ===== (original line 12364) =====

/* Immediate banner close fallback: keeps navbar/gamebar/filter offsets in sync without refresh. */
(function () {
  function visibleHeight(el) {
    if (!el) return 0;
    var cs = window.getComputedStyle ? getComputedStyle(el) : el.currentStyle;
    if (!cs || cs.display === 'none' || cs.visibility === 'hidden') return 0;
    return el.offsetHeight || 0;
  }

  function applySaleBannerOffset(forceZero) {
    var root = document.documentElement;
    var banner = document.getElementById('lbSaleBanner') || document.getElementById('lbGiveawayBanner');
    var h = forceZero ? 0 : visibleHeight(banner);

    root.style.setProperty('--lb-sale-h', h + 'px');
    root.style.setProperty('--mobile-banner-offset', h + 'px');
    if (h === 0 && root.classList) root.classList.add('lb-sale-banner-hidden');
    if (h > 0 && root.classList) root.classList.remove('lb-sale-banner-hidden');

    var navs = document.querySelectorAll('nav.navbar-top, nav.navbar-mobile, .navbar-top, .navbar-mobile');
    for (var i = 0; i < navs.length; i++) {
      var nav = navs[i];
      if (!nav) continue;
      var cs = window.getComputedStyle ? getComputedStyle(nav) : nav.currentStyle;
      if (cs && cs.position === 'fixed') {
        nav.style.setProperty('top', h + 'px', 'important');
      }
    }

    try { window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: h } })); } catch(e) {}
  }

  window.lbApplySaleBannerOffset = applySaleBannerOffset;

  document.addEventListener('click', function (e) {
    var t = e.target;
    while (t) {
      if (t.getAttribute && t.getAttribute('data-lb-close') !== null) {
        var banner = document.getElementById('lbSaleBanner') || document.getElementById('lbGiveawayBanner');
        if (banner) banner.style.setProperty('display', 'none', 'important');
        applySaleBannerOffset(true);
        setTimeout(function(){ applySaleBannerOffset(true); }, 25);
        setTimeout(function(){ applySaleBannerOffset(true); }, 150);
        break;
      }
      t = t.parentNode;
    }
  }, true);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ applySaleBannerOffset(false); });
  } else {
    applySaleBannerOffset(false);
  }
  window.addEventListener('resize', function(){ applySaleBannerOffset(false); });
})();

// ===== (original line 12432) =====

// ── Dynamic --lb-navbar-bottom ────────────────────────────────────────────
// Measures the real rendered bottom edge of whichever navbar is currently
// visible (.navbar-mobile or .navbar-top) and writes it into --lb-navbar-bottom
// on <html>. .lb-game-subnav / .lb-mobile-gamebar read this instead of a
// hardcoded px height, so they always sit right below the navbar even though
// the navbar's own height is vw-based and its scrolled/unscrolled padding
// (and the sale banner) change that height at runtime.
(function () {
  function isVisible(el) {
    if (!el) return false;
    var cs = getComputedStyle(el);
    return cs.display !== 'none' && cs.visibility !== 'hidden';
  }

  function updateNavbarBottom() {
    var root = document.documentElement;
    var navbar = document.querySelector('.navbar-mobile');
    if (!isVisible(navbar)) navbar = document.querySelector('.navbar-top');
    if (!isVisible(navbar)) return;

    var bottom = navbar.getBoundingClientRect().bottom;
    root.style.setProperty('--lb-navbar-bottom', Math.ceil(bottom) + 'px');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateNavbarBottom);
  } else {
    updateNavbarBottom();
  }
  setTimeout(updateNavbarBottom, 50);
  setTimeout(updateNavbarBottom, 200);
  window.addEventListener('resize', updateNavbarBottom);
  window.addEventListener('scroll', updateNavbarBottom, { passive: true });
  window.addEventListener('load', updateNavbarBottom);
  window.addEventListener('lb:banner-layout-update', updateNavbarBottom);

  // Follow the navbar's animated height continuously. Without this observer,
  // the subnav receives only the first and last scroll measurements while the
  // navbar padding is transitioning, which briefly opens a visible seam.
  if ('ResizeObserver' in window) {
    var observedNavbars = document.querySelectorAll('.navbar-top, .navbar-mobile');
    var navbarSizeObserver = new ResizeObserver(updateNavbarBottom);
    for (var i = 0; i < observedNavbars.length; i++) {
      navbarSizeObserver.observe(observedNavbars[i]);
    }
  }

  // .navbar-top's padding (and therefore its height) animates over 0.3s when
  // the .scrolled class toggles. A measurement taken exactly on the last
  // scroll event can land mid-transition and then never get corrected since
  // no further scroll events fire once scrolling stops. Re-measure once the
  // transition has had time to finish.
  var navbarBottomSettleTimer = null;
  window.addEventListener('scroll', function () {
    clearTimeout(navbarBottomSettleTimer);
    navbarBottomSettleTimer = setTimeout(updateNavbarBottom, 350);
  }, { passive: true });

  document.addEventListener('click', function (e) {
    var t = e.target;
    while (t) {
      if (t.getAttribute && t.getAttribute('data-lb-close') !== null) {
        setTimeout(updateNavbarBottom, 50);
        setTimeout(updateNavbarBottom, 150);
        break;
      }
      t = t.parentNode;
    }
  });
})();

// ── Dynamic --lb-content-top ──────────────────────────────────────────────
// Measures the bottom edge of the lowest visible fixed bar and writes it
// into --lb-content-top on <html>. Works with or without gamebar, with or
// without the sale banner. Runs on load, resize and banner-close.
(function () {
  function updateContentTop() {
    var root = document.documentElement;

    function isVisible(el) {
      if (!el) return false;
      var cs = getComputedStyle(el);
      if (cs.display === 'none' || cs.visibility === 'hidden') return false;
      return true;
    }

    var isMobileViewport = window.innerWidth <= 1024;
    var bottom = 0;

    if (isMobileViewport) {
      // On mobile: stack banner + navbar + optional mobile gamebar
      var banner      = document.getElementById('lbSaleBanner') || document.getElementById('lbGiveawayBanner');
      var navbar      = document.querySelector('.navbar-mobile') || document.querySelector('.navbar-top');
      var mobileBar   = document.querySelector('.lb-mobile-gamebar');

      // Use the lowest measured bottom edge among all visible fixed bars
      var candidates = [banner, navbar, mobileBar];
      for (var i = 0; i < candidates.length; i++) {
        var el = candidates[i];
        if (!isVisible(el)) continue;
        var b = el.getBoundingClientRect().bottom;
        if (b > bottom) bottom = b;
      }
    } else {
      // Desktop: gamebar is fixed and lowest; navbar already accounts for banner via --lb-sale-h
      var gamebar = document.querySelector('.lb-game-subnav');
      var navbar  = document.querySelector('.navbar-top');

      if (document.body.classList.contains('lb-desktop-subnav-hidden')) {
        gamebar = null;
      }

      if (isVisible(gamebar)) {
        bottom = gamebar.getBoundingClientRect().bottom;
      } else if (isVisible(navbar)) {
        bottom = navbar.getBoundingClientRect().bottom;
      }
    }

    root.style.setProperty('--lb-content-top', Math.ceil(bottom) + 'px');

  }

  // Run on DOMContentLoaded, load, resize, scroll
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateContentTop);
  } else {
    updateContentTop();
  }
  // Extra passes to catch layout shifts from fonts/images/banner render
  setTimeout(updateContentTop, 50);
  setTimeout(updateContentTop, 200);
  window.addEventListener('resize', updateContentTop);
  window.addEventListener('scroll', updateContentTop, { passive: true });
  window.addEventListener('load', updateContentTop);
  window.addEventListener('lb:banner-layout-update', updateContentTop);

  // .lb-game-subnav's own top/background transition over 0.3s (it tracks
  // --lb-navbar-bottom, which itself settles asynchronously). A measurement
  // taken on the last scroll event can land mid-transition and then never
  // self-correct once scrolling stops. Re-measure after things settle.
  var contentTopSettleTimer = null;
  window.addEventListener('scroll', function () {
    clearTimeout(contentTopSettleTimer);
    contentTopSettleTimer = setTimeout(updateContentTop, 350);
  }, { passive: true });

  // Re-measure when the sale banner is closed
  document.addEventListener('click', function (e) {
    var t = e.target;
    while (t) {
      if (t.getAttribute && t.getAttribute('data-lb-close') !== null) {
        setTimeout(updateContentTop, 50);
        setTimeout(updateContentTop, 150);
        break;
      }
      t = t.parentNode;
    }
  });
})();

// ===== (original line 12515) =====

/* Header-owned mobile navbar extras: move the language/currency pill into the sidenav. */
(function() {
    function findMobileMenu() {
        return document.querySelector('.sidenav-mob, .side-nav-mob, .sidenav-mobile, .side-nav-mobile, .mobile-menu, #mobileMenu, #mobile-menu');
    }

    function findMenuList(menu) {
        if (!menu) return null;
        return menu.querySelector('.sidenav-menu, .mobile-menu-list, .menu-list, [role="menu"], ul');
    }

    function moveSettingsUnderBlog() {
        var pill = document.getElementById('openSiteSettingsMobile');
        var menu = findMobileMenu();
        if (!pill || !menu) return;

        var footer = menu.querySelector('.sidenav-footer, .mobile-menu-footer, .menu-footer');
        var slot = menu.querySelector('.sidenav-footer .mobile-menu-settings-slot, .mobile-menu-footer .mobile-menu-settings-slot, .menu-footer .mobile-menu-settings-slot')
            || menu.querySelector('.mobile-menu-settings-slot');

        if (!slot) {
            slot = document.createElement('div');
            slot.className = 'mobile-menu-settings-slot';
        }

        if (pill.parentNode !== slot) {
            slot.appendChild(pill);
        }

        if (footer && !slot.closest('.sidenav-footer, .mobile-menu-footer, .menu-footer')) {
            footer.insertAdjacentElement('beforeend', slot);
            return;
        }

        var list = findMenuList(menu);
        if (!footer && list && list.nextElementSibling !== slot) {
            list.insertAdjacentElement('afterend', slot);
        } else if (!footer && !list && slot.parentNode !== menu) {
            menu.appendChild(slot);
        }
    }

    function initHeaderMobileNavbar() {
        moveSettingsUnderBlog();
        setTimeout(moveSettingsUnderBlog, 80);
        setTimeout(moveSettingsUnderBlog, 300);
        setTimeout(moveSettingsUnderBlog, 900);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeaderMobileNavbar);
    } else {
        initHeaderMobileNavbar();
    }
    window.addEventListener('load', initHeaderMobileNavbar);
    document.addEventListener('click', function(e) {
        if (e.target.closest('.close-sidenav, .close-sidenav-mob')) {
            if (typeof window.lbCloseMobileSidenav === 'function') {
                window.lbCloseMobileSidenav(e);
                return;
            }
            if (typeof lbStartMobileLoginSuppress === 'function') lbStartMobileLoginSuppress(420);
            else window.__lbSuppressMobileLoginUntil = Date.now() + 1400;
        }
        if (e.target.closest('.menu-icon, [aria-label="Menu"], [aria-label="Open menu"], .close-sidenav, .close-sidenav-mob')) {
            setTimeout(initHeaderMobileNavbar, 80);
            setTimeout(initHeaderMobileNavbar, 260);
        }
    });
    if (window.MutationObserver && document.documentElement) {
        new MutationObserver(function() { moveSettingsUnderBlog(); }).observe(document.documentElement, { childList: true, subtree: true });
    }
})();

// ===== (original line 12595) =====

/* Help button: close mobile side menu first, then open Tawk immediately */
(function () {
  function closeMobileMenuForTawk() {
    try {
      if (typeof window.lbCloseMobileSidenav === 'function') {
        window.lbCloseMobileSidenav();
      }
    } catch (e) {}

    document.querySelectorAll(
      '.sidenav-mob, .side-nav-mob, .sidenav-mobile, .side-nav-mobile, .mobile-menu, #mobileMenu, #mobile-menu'
    ).forEach(function (menu) {
      menu.classList.remove('open', 'show', 'active', 'is-open', 'opened');
      menu.style.transform = '';
      menu.style.visibility = '';
      menu.style.opacity = '';
      menu.setAttribute('aria-hidden', 'true');
    });

    document.querySelectorAll(
      '.sidenav-backdrop, .mobile-menu-backdrop, .nav-backdrop, .sidebar-backdrop, .offcanvas-backdrop, .menu-backdrop, .sidenav-overlay, .mobile-menu-overlay, .nav-overlay, .sidebar-overlay, .offcanvas-overlay, .menu-overlay'
    ).forEach(function (overlay) {
      overlay.remove();
    });

    [
      'sidenav-open',
      'mobile-menu-open',
      'nav-open',
      'sidebar-open',
      'offcanvas-open',
      'overlay',
      'modal-open'
    ].forEach(function (cls) {
      document.body.classList.remove(cls);
      document.documentElement.classList.remove(cls);
    });
  }

  function openTawkDirectly() {
    document.body.classList.add('tawk-support-open');

    function run() {
      try {
        if (window.Tawk_API) {
          if (typeof window.Tawk_API.showWidget === 'function') {
            window.Tawk_API.showWidget();
          }
          if (typeof window.Tawk_API.maximize === 'function') {
            window.Tawk_API.maximize();
            return true;
          }
        }
      } catch (e) {}
      return false;
    }

    run();

    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (run() || tries >= 28) {
        clearInterval(timer);
      }
    }, 180);
  }

  function handleHelpClick(e) {
    var btn = e.target.closest('.mobile-menu-utility-btn--help, [data-tawk-mobile-support="1"]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    closeMobileMenuForTawk();

    setTimeout(openTawkDirectly, 80);
  }

  document.addEventListener('click', handleHelpClick, true);
})();

// ===== (original line 15425) =====

(function(){
  var overlay = document.getElementById('gmHeaderSearchOverlay');
  if(!overlay) return;
  var body = overlay.querySelector('.gmHeaderCommandBody');
  var firstSection = body ? body.querySelector('[data-gm-search-section]') : null;
  var allItems = Array.prototype.slice.call(overlay.querySelectorAll('[data-gm-search-item]'));
  var gameGrid = overlay.querySelector('.gmHeaderCommandGameGrid');
  var gameCards = gameGrid ? Array.prototype.slice.call(gameGrid.querySelectorAll('.gmHeaderCommandGame')) : [];
  var tabButtons = Array.prototype.slice.call(overlay.querySelectorAll('[data-gm-command-tab]'));
  var input = overlay.querySelector('#gmHeaderSearchInput');
  var toolbar, countEl, sortPopularBtn, sortAlphaBtn;
  var currentSort = 'popular';

  function normalized(v){ return (v || '').toString().trim().toLowerCase(); }

  function ensureToolbar(){
    if(!body || !firstSection || body.querySelector('.gmHeaderMarketplaceToolbar')) return;
    toolbar = document.createElement('div');
    toolbar.className = 'gmHeaderMarketplaceToolbar';
    toolbar.innerHTML = '<div class="gmHeaderMarketplaceCount">0 results</div><div class="gmHeaderMarketplaceSort"><a class="gmHeaderMarketplaceViewAll" data-gm-view-all href="/services">View all</a><button type="button" class="gmHeaderMarketplaceSortBtn is-active" data-gm-sort="popular">Popular</button><button type="button" class="gmHeaderMarketplaceSortBtn" data-gm-sort="az">A-Z</button></div>';
    body.insertBefore(toolbar, firstSection);
    countEl = toolbar.querySelector('.gmHeaderMarketplaceCount');
    sortPopularBtn = toolbar.querySelector('[data-gm-sort="popular"]');
    sortAlphaBtn = toolbar.querySelector('[data-gm-sort="az"]');
    [sortPopularBtn, sortAlphaBtn].forEach(function(btn){
      btn.addEventListener('click', function(){
        currentSort = btn.getAttribute('data-gm-sort') || 'popular';
        [sortPopularBtn, sortAlphaBtn].forEach(function(other){ other.classList.toggle('is-active', other === btn); });
        applySort();
      });
    });
  }

  function ensureCounts(){
    tabButtons.forEach(function(btn){
      var labelWrap = btn.querySelector('span:last-child');
      if(!labelWrap) return;
      var strong = labelWrap.querySelector('strong');
      if(!strong) return;
      var count = labelWrap.querySelector('.gmHeaderQuickCount');
      if(!count){
        count = document.createElement('span');
        count.className = 'gmHeaderQuickCount';
        labelWrap.appendChild(count);
      }
      var cat = normalized(btn.getAttribute('data-gm-command-tab')) || 'all';
      var total = allItems.filter(function(item){
        var cats = ' ' + normalized(item.getAttribute('data-gm-cats')) + ' ';
        if(cat === 'all') return true;
        if(cat === 'digital') return cats.indexOf(' digital ') !== -1;
        if(cat === 'topups') return item.classList.contains('gmHeaderCommandGame') && cats.indexOf(' topups ') !== -1;
        return cats.indexOf(' ' + cat + ' ') !== -1;
      }).length;
      count.textContent = total;
    });
  }

  function ensureCardTags(){
    gameCards.forEach(function(card, index){
      card.setAttribute('data-gm-initial-index', index);
      var title = card.querySelector('.gmHeaderResultTitle');
      if(title) card.setAttribute('data-gm-title', normalized(title.textContent));
      var tag = card.querySelector('.gmHeaderGameTopTag');
      if(!tag){
        tag = document.createElement('span');
        tag.className = 'gmHeaderGameTopTag';
        card.insertBefore(tag, card.firstChild);
      }
      var cats = ' ' + normalized(card.getAttribute('data-gm-cats')) + ' ';
      var text = 'Accounts';
      var icon = 'fa-shield-halved';
      if(cats.indexOf(' boosting ') !== -1 && cats.indexOf(' accounts ') === -1){ text = 'Boosting'; icon = 'fa-rocket'; }
      else if(cats.indexOf(' items ') !== -1 && cats.indexOf(' accounts ') === -1 && cats.indexOf(' boosting ') === -1){ text = 'Items'; icon = 'fa-gem'; }
      tag.innerHTML = '<i class="fas ' + icon + '" aria-hidden="true"></i><span>' + text + '</span>';
    });
  }

  function applySort(){
    if(!gameGrid) return;
    var cards = Array.prototype.slice.call(gameGrid.querySelectorAll('.gmHeaderCommandGame'));
    cards.sort(function(a, b){
      if(currentSort === 'az'){
        return normalized(a.getAttribute('data-gm-title')).localeCompare(normalized(b.getAttribute('data-gm-title')));
      }
      return parseInt(a.getAttribute('data-gm-initial-index') || '0', 10) - parseInt(b.getAttribute('data-gm-initial-index') || '0', 10);
    });
    cards.forEach(function(card){ gameGrid.appendChild(card); });
  }

  function updateVisibleCount(){
    if(!countEl) return;
    var visible = allItems.filter(function(item){ return !item.classList.contains('gmHeaderCommandHidden'); }).length;
    countEl.textContent = visible + ' results';
  }

  function updateViewAllButton(){
    if(!toolbar) return;
    var link = toolbar.querySelector('[data-gm-view-all]');
    if(!link) return;
    var active = overlay.querySelector('.gmHeaderCommandQuickCard.is-active[data-gm-command-tab]');
    var cat = normalized(active ? active.getAttribute('data-gm-command-tab') : 'all') || 'all';
    var urls = { all:'/services', boosting:'/services/boosting', accounts:'/services/accounts', topups:'/services/top-ups', items:'/services/items', digital:'/digital-goods' };
    var labels = { all:'View all Services', boosting:'View all Boosting', accounts:'View all Accounts', topups:'View all Top-ups', items:'View all Items', digital:'View all Digital Goods' };
    link.href = urls[cat] || '/services';
    link.textContent = labels[cat] || 'View all';
    link.style.display = cat === 'all' ? 'none' : '';
  }

  function refresh(){
    ensureToolbar();
    ensureCounts();
    ensureCardTags();
    applySort();
    updateVisibleCount();
    updateViewAllButton();
  }

  ensureToolbar();
  ensureCounts();
  ensureCardTags();
  refresh();

  tabButtons.forEach(function(btn){ btn.addEventListener('click', function(){ setTimeout(refresh, 0); }); });
  if(input) input.addEventListener('input', function(){ setTimeout(refresh, 0); });

  var observer = new MutationObserver(function(){ refresh(); });
  allItems.forEach(function(item){ observer.observe(item, { attributes:true, attributeFilter:['class'] }); });
})();
