<?php
// Same source the realtime websocket update uses (ajax.php client_seller_chat_unread_count,
// via util_client_chat_unread_count in functions.php) — covers seller/marketplace chat
// (accounts, items, top-ups, premium accounts) AND booster/admin order chat. This used to be a
// hand-rolled scanner over legacy "selling_*.json" chat files only, so it silently missed
// unread messages on every other order type until the websocket round-trip corrected it.
$client_sidebar_chat_unread = 0;
?>
<style>
  /* Sidebar extras (2 elements): Discord + Safety report */
  #nav-card { margin: 18px 12px 12px; }
  /* Keep sidebar scroll working */
  .navbar-vertical-content { overflow-y: auto; }


  /* Card look: match dashboard dark surfaces */
  #nav-card .sbx-card {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .03);
    box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
    color: rgba(255,255,255,.88);
  }

  #nav-card .sbx-title { color: rgba(255,255,255,.92); }
  #nav-card .sbx-text  { color: rgba(255,255,255,.72); }

  /* Discord card */
  #nav-card .sbx-discord img {
    display: block;
    width: 100%;
    height: auto;
    filter: saturate(1.05);
  }

  /* Warning card (compact + subtle, not "white" looking) */
  #nav-card .sbx-warning {
    position: relative;
    padding: 12px 12px 10px;
    background: radial-gradient(120% 120% at 10% 0%, rgba(255, 77, 77, .10), rgba(255,255,255,.00));
  }
  #nav-card .sbx-warning:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: rgba(255, 77, 77, .85);
  }
  #nav-card .sbx-warning .sbx-title {
    font-weight: 800;
    font-size: 12.25px;
    line-height: 1.25;
    margin: 0 0 3px;
  }
  #nav-card .sbx-warning .sbx-text {
    font-size: 11.75px;
    line-height: 1.25;
  }
  #nav-card .sbx-warning i { color: rgba(255, 120, 120, .95); }

  #nav-card .sbx-actions { margin-top: 9px; }

  /* Buttons: lower height, dashboard-like (less saturated) */
  #nav-card .sbx-btn {
    border-radius: 12px !important;
    font-weight: 750;
    letter-spacing: .10px;
    padding: 7px 10px;
    font-size: 12.75px;
    line-height: 1.15;
  }

  /* Use a muted accent instead of bright blue */
  #nav-card .btn.btn-primary.sbx-btn {
    background: rgba(255, 255, 255, .06) !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    color: rgba(255,255,255,.92) !important;
    box-shadow: none !important;
  }
  #nav-card .btn.btn-primary.sbx-btn:hover {
    background: rgba(255, 255, 255, .08) !important;
    border-color: rgba(123, 97, 255, .55) !important;
  }
  #nav-card .btn.btn-primary.sbx-btn:active { transform: translateY(1px); }

  /* Mini actions (collapsed sidebar): use native nav-link look + Bootstrap tooltips */
#nav-mini { display:none; margin: 14px 0 12px; }
#nav-mini .nav-link { justify-content: center; }
#nav-mini .nav-link i { margin: 0; }

  .nav-link .client-sidebar-chat-badge {
    margin-left: auto;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #7367f0;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    line-height: 1;
    box-shadow: 0 0 0 3px rgba(115,103,240,.14), 0 6px 14px rgba(0,0,0,.25);
  }
  .navbar-vertical-aside-mini-mode .client-sidebar-chat-badge,
  .navbar-vertical-aside-mini-mode .nav-link-title + .client-sidebar-chat-badge {
    position: absolute;
    top: 5px;
    right: 8px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    font-size: 9px;
  }
</style>

<aside
    class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white  ">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->

            <a class="navbar-brand" href="<?= BASE_URL ?>" aria-label="LoLBoost.gg">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="Logo"
                    data-hs-theme-appearance="light">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo"
                    data-hs-theme-appearance="dark">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo"
                    data-hs-theme-appearance="default">

                <img class="navbar-brand-logo-mini" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="Logo">
            </a>

            <!-- End Logo -->

            <!-- Navbar Vertical Toggle -->
            <button id="navbar-toggle" type="button"
                class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i id="collapse-icon" class="fa-solid fa-arrow-left-to-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i id="expand-icon" class="fa-solid fa-arrow-right-from-line"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Expand" style="display: none;"></i>
            </button>
            <!-- End Navbar Vertical Toggle -->

            <!-- Content -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

                    <?php if (BOOSTER_DATA): ?>
                        <div class="nav-item mb-3">
                            <a class="btn btn-primary w-100" href="/booster-area/dashboard">
                                <i class="fa-duotone fa-arrows-repeat nav-icon"></i>
                                <span class="nav-link-title">Switch to Booster</span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Collapse -->
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/overview" data-link="profile/overview"
                            data-placement="left">
                            <i class="fa-duotone fa-objects-column nav-icon"></i>
                            <span class="nav-link-title">Overview</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/chat" data-link="chat"
                            data-placement="left">
                            <i class="fa-duotone fa-messages nav-icon"></i>
                            <span class="nav-link-title">My Chats</span>
                            <span class="client-sidebar-chat-badge <?= (int)($client_sidebar_chat_unread ?? 0) > 0 ? '' : 'd-none' ?>" id="clientSidebarChatBadge"><?= (int)($client_sidebar_chat_unread ?? 0) ?></span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">My Orders</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/orders" data-link="orders"
                            data-placement="left">
                            <i class="fa-duotone fa-cart-shopping nav-icon"></i>
                            <span class="nav-link-title">My Orders</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Transactions</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/billing" data-link="payments"
                            data-placement="left">
                            <i class="fa-duotone fa-wallet nav-icon"></i>
                            <span class="nav-link-title">My Payments</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/coins-history" data-link="coins-history"
                            data-placement="left">
                            <i class="fa-duotone fa-coin nav-icon"></i>
                            <span class="nav-link-title">Coins History</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/rewards" data-link="rewards"
                            data-placement="left">
                            <i class="fa-duotone fa-gift nav-icon"></i>
                            <span class="nav-link-title">LB Rewards</span>
                        </a>
                    </div>
                    <?php if (function_exists('lb_referral_client_is_allowed') && lb_referral_client_is_allowed((int) CLIENT_ID)): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/referrals" data-link="referrals"
                            data-placement="left">
                            <i class="fa-duotone fa-share-nodes nav-icon"></i>
                            <span class="nav-link-title">Referrals</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <span class="dropdown-header mt-4">Profile</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/settings" data-link="settings"
                            data-placement="left">
                            <i class="fa-duotone fa-cog nav-icon"></i>
                            <span class="nav-link-title">Settings</span>
                        </a>
                    </div>
                </div>

                <!-- Sidebar extras (visible only when expanded; JS toggles #nav-card) -->
                <div id="nav-card">

                    <!-- 1) Warning: report private boosting (reward) -->
                    <div class="sbx-card mb-3">
                        <div class="sbx-warning">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px; opacity:.9;"></i>
                                <div>
                                    <p class="sbx-title">Did a booster contact you outside this platform?</p>
                                    <div class="sbx-text"> If we can identify the booster, you’ll receive up to €100 / $100 in store credit.</div>
                                </div>
                            </div>
                            <div class="sbx-actions">
                                <a href="#open-chat" class="btn btn-primary w-100 sbx-btn open-chat">
                                    Report now (Live Chat)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 2) Discord community join -->
                    <div class="sbx-card sbx-discord">
                        <img src="<?= ASSET_URL ?>/core/dash/img/banners/discord-banner.png" alt="Discord Banner">
                        <div class="p-3 text-center">
                            <a class="btn btn-primary w-100 sbx-btn" href="https://lolboost.gg/discord" target="_blank">
                                Join our Discord <i class="fa-brands fa-discord ms-1"></i>
                            </a>
                        </div>
                    </div>

                </div>
                
                <!-- Mini actions (visible when sidebar is collapsed) -->
<div id="nav-mini" class="nav nav-pills nav-vertical card-navbar-nav">
    <div class="nav-item">
        <a href="#open-chat" class="nav-link open-chat"
           data-bs-toggle="tooltip" data-bs-placement="right"
           title="Report private boosting (Big reward)">
            <i class="fa-solid fa-triangle-exclamation nav-icon"></i>
        </a>
    </div>
    <div class="nav-item">
        <a href="https://lolboost.gg/discord" target="_blank" class="nav-link"
           data-bs-toggle="tooltip" data-bs-placement="right"
           title="Join our Discord">
            <i class="fa-brands fa-discord nav-icon"></i>
        </a>
    </div>
</div>

<!-- End Content -->

            </div>
        </div>
</aside>

<script>
    var navbarToggler = document.getElementById('navbar-toggle');
    var collapseIcon = document.getElementById('collapse-icon');
    var expandIcon = document.getElementById('expand-icon');
    var navCard = document.getElementById('nav-card');
    var navMini = document.getElementById('nav-mini');

    // default: expanded
    if (navCard) navCard.style.display = 'block';
    if (navMini) navMini.style.display = 'none';

    navbarToggler.addEventListener('click', function () {
        var isCollapsed = (collapseIcon.style.display !== 'none'); // current state before toggle

        // Toggle icons (existing behavior)
        if (collapseIcon.style.display === 'none') {
            collapseIcon.style.display = 'inline';
            expandIcon.style.display = 'none';
        } else {
            collapseIcon.style.display = 'none';
            expandIcon.style.display = 'inline';
        }

        // Toggle our sidebar extras
        // When collapsing -> hide cards, show mini icons
        // When expanding  -> show cards, hide mini icons
        if (navCard && navMini) {
            if (isCollapsed) {
                // currently expanded -> collapse
                navCard.style.display = 'none';
                navMini.style.display = 'flex';
            } else {
                // currently collapsed -> expand
                navCard.style.display = 'block';
                navMini.style.display = 'none';
            }
        }
    });
</script>
<script>
(function(){
    var badge = document.getElementById('clientSidebarChatBadge');
    if (!badge) return;
    function setClientChatUnread(value) {
        var n = parseInt(value || 0, 10) || 0;
        currentUnread = n;
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.classList.toggle('d-none', n <= 0);
    }
    var boundSocket = null;
    var refreshTimer = null;
    var currentUnread = <?= (int)$client_sidebar_chat_unread ?>;
    var allowZeroUpdate = currentUnread <= 0;
    var authoritativeTimer = null;
    function refreshAuthoritativeUnread() {
        clearTimeout(authoritativeTimer);
        authoritativeTimer = setTimeout(function () {
            var fd = new FormData();
            fd.append('action', 'client_seller_chat_unread_count');
            fetch(typeof ajax_url !== 'undefined' ? ajax_url : '<?= AJAX_URL ?>', {
                method: 'POST', body: fd, credentials: 'same-origin', cache: 'no-store'
            }).then(function (r) { return r.json(); }).then(function (payload) {
                if (!payload || payload.success === false) return;
                allowZeroUpdate = true;
                var canonical = typeof window.getCanonicalClientInboxUnread === 'function'
                    ? window.getCanonicalClientInboxUnread()
                    : null;
                setClientChatUnread(canonical !== null ? canonical : (payload.unread != null ? payload.unread : payload.total));
            }).catch(function () {});
        }, 180);
    }
    function isIncomingClientMessage(payload) {
        if (!payload || payload.read_receipt_update || payload.deleted || payload.event === 'read') return false;
        var sender = String(payload.sender_type || payload.sender || payload.from_role || '').toLowerCase();
        return ['seller', 'booster', 'admin'].indexOf(sender) !== -1;
    }
    function subscribeUnread() {
        var socket = window.lbSocket || window.socket || null;
        if (!socket || !socket.connected) return;
        if (boundSocket !== socket) {
            boundSocket = socket;
            socket.on('chat_unread_update', function(payload){
                // ok === false means the relayed unread-count lookup on the socket server failed
                // (e.g. session/auth hiccup on that request) — a real "0" always has ok !== false,
                // so never let a failed lookup stomp a correct badge with zero.
                if (!payload || payload.role !== 'client' || payload.ok === false) return;
                var canonical = typeof window.getCanonicalClientInboxUnread === 'function'
                    ? window.getCanonicalClientInboxUnread()
                    : null;
                var nextUnread = canonical !== null ? canonical : (parseInt(payload.unread != null ? payload.unread : payload.total, 10) || 0);
                // The socket service may briefly receive no authenticated PHP session during
                // the first subscription and answer with a false zero. Never let that erase
                // the server-rendered count. A zero is accepted only after this tab has seen
                // an explicit client read-receipt event, or when the badge was already empty.
                if (nextUnread === 0 && currentUnread > 0 && !allowZeroUpdate) return;
                setClientChatUnread(nextUnread);
                allowZeroUpdate = nextUnread <= 0;
            });
            socket.on('chat_unread_refresh', function(){
                clearTimeout(refreshTimer);
                refreshTimer = setTimeout(function(){
                    try { socket.emit('chat_unread_subscribe', {role:'client'}); } catch(e) {}
                }, 120);
            });
            socket.on('connect', function(){
                try { socket.emit('chat_unread_subscribe', {role:'client'}); } catch(e) {}
            });
        }
        try { socket.emit('chat_unread_subscribe', {role:'client'}); } catch(e) {}
    }
    var attempts = 0;
    var timer = setInterval(function(){
        attempts++;
        subscribeUnread();
        if ((boundSocket && boundSocket.connected) || attempts >= 30) clearInterval(timer);
    }, 250);
    window.addEventListener('lb-chat-update', function(event){
        var payload = event && event.detail ? event.detail : {};
        if (payload.reader_role === 'client' || payload.viewer_role === 'client') {
            allowZeroUpdate = true;
        }
        if (isIncomingClientMessage(payload)) setClientChatUnread(currentUnread + 1);
        refreshAuthoritativeUnread();
    });
    window.addEventListener('load', function(){ subscribeUnread(); refreshAuthoritativeUnread(); });
    window.addEventListener('focus', subscribeUnread);
    window.addEventListener('lb-socket-ready', subscribeUnread);
    window.setClientSidebarChatUnread = setClientChatUnread;
    window.incrementClientSidebarChatUnread = function () { setClientChatUnread(currentUnread + 1); };
    window.refreshClientSidebarChatUnread = refreshAuthoritativeUnread;
})();
</script>
