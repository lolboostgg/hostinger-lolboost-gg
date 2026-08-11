<?php
$current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

function seller_nav_active(string $segment, string $current, bool $exact = false): string {
    if ($exact) {
        return ($current === ltrim($segment, '/')) ? ' active' : '';
    }
    return (strpos($current, ltrim($segment, '/')) !== false) ? ' active' : '';
}

// Accounts active on list + detail pages, but NOT on add
$accounts_active = (
    strpos($current_path, 'seller-area/accounts') !== false ||
    strpos($current_path, 'seller-area/account/') !== false
) && strpos($current_path, 'seller-area/accounts/add') === false
  && strpos($current_path, 'seller-area/account-orders') === false
  && strpos($current_path, 'seller-area/account-order/') === false ? ' active' : '';

$account_orders_active = (
    strpos($current_path, 'seller-area/account-orders') !== false ||
    strpos($current_path, 'seller-area/account-order/') !== false
) ? ' active' : '';

// Add Account active only on add page
$accounts_add_active = seller_nav_active('seller-area/accounts/add', $current_path);

// Items active on list + detail pages, but NOT on add and NOT on item-orders
$items_active = (
    strpos($current_path, 'seller-area/items') !== false ||
    strpos($current_path, 'seller-area/item/') !== false
) && strpos($current_path, 'seller-area/items/add') === false
  && strpos($current_path, 'seller-area/item-orders') === false
  && strpos($current_path, 'seller-area/item-order/') === false ? ' active' : '';

// Add Item active only on add page
$items_add_active = seller_nav_active('seller-area/items/add', $current_path, true);

// Item Orders active on list + detail pages
$item_orders_active = (
    strpos($current_path, 'seller-area/item-orders') !== false ||
    strpos($current_path, 'seller-area/item-order/') !== false
) ? ' active' : '';

// Top Ups active on seller top up listing pages
$topups_active = (
    strpos($current_path, 'seller-area/top-ups') !== false ||
    strpos($current_path, 'seller-area/topup/') !== false
) && strpos($current_path, 'seller-area/top-up-orders') === false
  && strpos($current_path, 'seller-area/top-up-order/') === false ? ' active' : '';

// Top Up Orders active on list + detail pages
$topup_orders_active = (
    strpos($current_path, 'seller-area/top-up-orders') !== false ||
    strpos($current_path, 'seller-area/top-up-order/') !== false
) ? ' active' : '';

// Digital Goods active on orders/listings/detail pages
$digital_goods_active = (strpos($current_path, 'seller-area/digital-goods') !== false) ? ' active' : '';
$seller_import_accounts_active = seller_nav_active('seller-area/import-accounts', $current_path);
$seller_import_items_active = seller_nav_active('seller-area/import-items', $current_path);
$seller_import_topups_active = seller_nav_active('seller-area/import-top-ups', $current_path);
$seller_api_active = seller_nav_active('seller-area/api', $current_path, true);
$seller_payout_requests_active = seller_nav_active('seller-area/payout-requests', $current_path, true);

// Chat Inbox active + unread badge count
$chat_inbox_active = (strpos($current_path, 'seller-area/chat') !== false) ? ' active' : '';
// Same source the realtime websocket update uses (ajax.php seller_chat_unread_count, via
// util_seller_chat_unread_count in functions.php) — scans the account/item/top-up chat JSON
// files the send handlers actually write to, plus the (currently unused) DB table, so the
// initial badge and the websocket-pushed one always agree.
$seller_id = (int)(defined('SELLER_ID') ? SELLER_ID : (SELLER_DATA['id'] ?? 0));
$sl_chat_unread = ($seller_id > 0 && function_exists('util_seller_chat_unread_count'))
    ? util_seller_chat_unread_count($seller_id)
    : 0;

// Balance
$sl_balance = (int)(SELLER_DATA['balance'] ?? 0);
$sl_eur = number_format($sl_balance / 100, 2, '.', '') . ' €';
?>

<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset" style="display:flex;flex-direction:column;height:100%;">

            <a class="navbar-brand" href="https://lolboost.gg" aria-label="LoLBoost.gg">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png" alt="Logo" data-hs-theme-appearance="light">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="dark">
                <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="Logo" data-hs-theme-appearance="default">
                <img class="navbar-brand-logo-mini" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="Logo">
            </a>

            <button id="navbar-toggle" type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i id="collapse-icon" class="fa-solid fa-arrow-left-to-line"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i id="expand-icon" class="fa-solid fa-arrow-right-from-line"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Expand" style="display: none;"></i>
            </button>

            <div class="navbar-vertical-content" style="flex:1;display:flex;flex-direction:column;">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav" style="flex:1;">

                    <div class="nav-item">
                        <a class="nav-link<?= seller_nav_active('seller-area/dashboard', $current_path, true) ?>"
                           href="<?= BASE_URL ?>/seller-area/dashboard">
                            <i class="fa-duotone fa-objects-column nav-icon"></i>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link<?= seller_nav_active('seller-area/analytics', $current_path, true) ?>"
                           href="<?= BASE_URL ?>/seller-area/analytics">
                            <i class="fa-duotone fa-chart-line nav-icon"></i>
                            <span class="nav-link-title">Analytics</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link<?= $chat_inbox_active ?> seller-sidebar-chat-link"
                           href="<?= BASE_URL ?>/seller-area/chat"
                           id="sellerSidebarChatLink">
                            <i class="fa-duotone fa-comments nav-icon"></i>
                            <span class="nav-link-title">Chat Inbox</span>
                            <span class="seller-sidebar-chat-badge<?= $sl_chat_unread > 0 ? '' : ' d-none' ?>" id="sellerSidebarChatUnread">
                                <?= $sl_chat_unread > 99 ? '99+' : (int)$sl_chat_unread ?>
                            </span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Accounts</span>

                    <div class="nav-item">
                        <a class="nav-link<?= $accounts_active ?>"
                           href="<?= BASE_URL ?>/seller-area/accounts">
                            <i class="fa-duotone fa-database nav-icon"></i>
                            <span class="nav-link-title">My Accounts</span>
                        </a>
                    </div>
<div class="nav-item">
                        <a class="nav-link<?= $seller_import_accounts_active ?>"
                           href="<?= BASE_URL ?>/seller-area/import-accounts">
                            <i class="fa-duotone fa-file-import nav-icon"></i>
                            <span class="nav-link-title">Import Accounts</span>
                        </a>
                    </div>


                    <div class="nav-item">
                        <a class="nav-link<?= $account_orders_active ?>"
                           href="<?= BASE_URL ?>/seller-area/account-orders">
                            <i class="fa-duotone fa-cart-shopping nav-icon"></i>
                            <span class="nav-link-title">Account Orders</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Items</span>

                    <div class="nav-item">
                        <a class="nav-link<?= $items_active ?>"
                           href="<?= BASE_URL ?>/seller-area/items">
                            <i class="fa-duotone fa-gift nav-icon"></i>
                            <span class="nav-link-title">My Items</span>
                        </a>
                    </div>

                    <div class="nav-item"><a class="nav-link<?= $seller_import_items_active ?>" href="<?= BASE_URL ?>/seller-area/import-items"><i class="fa-duotone fa-file-import nav-icon"></i><span class="nav-link-title">Import Items</span></a></div>

                    <div class="nav-item">
                        <a class="nav-link<?= $item_orders_active ?>"
                           href="<?= BASE_URL ?>/seller-area/item-orders">
                            <i class="fa-duotone fa-bag-shopping nav-icon"></i>
                            <span class="nav-link-title">Item Orders</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Top Ups</span>

                    <div class="nav-item">
                        <a class="nav-link<?= $topups_active ?>"
                           href="<?= BASE_URL ?>/seller-area/top-ups">
                            <i class="fa-duotone fa-coins nav-icon"></i>
                            <span class="nav-link-title">My Top Ups</span>
                        </a>
                    </div>

                    <div class="nav-item"><a class="nav-link<?= $seller_import_topups_active ?>" href="<?= BASE_URL ?>/seller-area/import-top-ups"><i class="fa-duotone fa-file-import nav-icon"></i><span class="nav-link-title">Import Top Ups</span></a></div>

                    <div class="nav-item">
                        <a class="nav-link<?= $topup_orders_active ?>"
                           href="<?= BASE_URL ?>/seller-area/top-up-orders">
                            <i class="fa-duotone fa-receipt nav-icon"></i>
                            <span class="nav-link-title">Top Up Orders</span>
                        </a>
                    </div>

                    <?php if (!empty(SELLER_DATA['can_list_digital_goods'])): ?>
                    <span class="dropdown-header mt-4">Digital Goods</span>

                    <div class="nav-item">
                        <a class="nav-link<?= $digital_goods_active ?>"
                           href="<?= BASE_URL ?>/seller-area/digital-goods/listings">
                            <i class="fa-duotone fa-box-open nav-icon"></i>
                            <span class="nav-link-title">My Listings</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link<?= (strpos($current_path, 'seller-area/digital-goods') !== false && strpos($current_path, 'listings') === false) ? ' active' : '' ?>"
                           href="<?= BASE_URL ?>/seller-area/digital-goods">
                            <i class="fa-duotone fa-inbox nav-icon"></i>
                            <span class="nav-link-title">DG Orders</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <span class="dropdown-header mt-4">Wallet</span>

                    <div class="nav-item">
                        <div class="nav-link" style="cursor:default;">
                            <i class="fa-duotone fa-sack-dollar nav-icon"></i>
                            <span class="nav-link-title">
                                Balance
                                <span class="ms-auto fw-bold" style="color:#818cf8;font-size:.8rem;"><?= $sl_eur ?></span>
                            </span>
                        </div>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link<?= seller_nav_active('seller-area/payments', $current_path) ?>"
                           href="<?= BASE_URL ?>/seller-area/payments">
                            <i class="fa-duotone fa-money-bill-transfer nav-icon"></i>
                            <span class="nav-link-title">Payments</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link<?= $seller_payout_requests_active ?>"
                           href="<?= BASE_URL ?>/seller-area/payout-requests">
                            <i class="fa-duotone fa-wallet nav-icon"></i>
                            <span class="nav-link-title">Payout Requests</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Seller</span>

                    <div class="nav-item">
                        <a class="nav-link<?= $seller_api_active ?>"
                           href="<?= BASE_URL ?>/seller-area/api">
                            <i class="fa-duotone fa-key nav-icon"></i>
                            <span class="nav-link-title">Partner API</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link<?= seller_nav_active('seller-area/rules', $current_path) ?>"
                           href="<?= BASE_URL ?>/seller-area/rules">
                            <i class="fa-duotone fa-book nav-icon"></i>
                            <span class="nav-link-title">Seller Rules</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Account</span>

                    <div class="nav-item">
                        <a class="nav-link<?= seller_nav_active('seller-area/profile', $current_path) ?>"
                           href="<?= BASE_URL ?>/seller-area/profile">
                            <i class="fa-solid fa-gear nav-icon"></i>
                            <span class="nav-link-title">Settings</span>
                        </a>
                    </div>
                </div>

                <div style="padding:.75rem .75rem .85rem;border-top:1px solid rgba(255,255,255,.07);margin-top:.5rem;">
                    <div class="dropdown dropup">
                        <a href="javascript:;" id="sidebarUserDropdown"
                           data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                           style="display:flex;align-items:center;gap:.6rem;padding:.4rem .5rem;border-radius:10px;text-decoration:none;transition:background .15s;cursor:pointer;"
                           onmouseover="this.style.background='rgba(255,255,255,.05)';"
                           onmouseout="this.style.background='transparent';">

                            <?php if (!empty(SELLER_DATA['icon'])): ?>
                                <img src="<?= htmlspecialchars(SELLER_DATA['icon']) ?>"
                                     alt="<?= htmlspecialchars(SELLER_DATA['username']) ?>"
                                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            <?php else: ?>
                                <div style="width:34px;height:34px;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff;flex-shrink:0;">
                                    <?= strtoupper(substr(SELLER_DATA['username'], 0, 1)) ?>
                                </div>
                            <?php endif ?>

                            <div class="navbar-brand-logo flex-grow-1 overflow-hidden" style="min-width:0;">
                                <div class="fw-semibold text-truncate" style="font-size:.8rem;color:rgba(255,255,255,.9);line-height:1.25;"><?= htmlspecialchars(SELLER_DATA['username']) ?></div>
                                <div class="text-truncate" style="font-size:.7rem;color:rgba(255,255,255,.4);line-height:1.25;"><?= htmlspecialchars(SELLER_DATA['email']) ?></div>
                            </div>

                            <i class="fa-solid fa-chevron-up navbar-brand-logo" style="font-size:.65rem;color:rgba(255,255,255,.3);flex-shrink:0;"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account"
                             aria-labelledby="sidebarUserDropdown"
                             style="width:17rem;margin-bottom:.4rem;z-index:9999;">

                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty(SELLER_DATA['icon'])): ?>
                                        <img src="<?= htmlspecialchars(SELLER_DATA['icon']) ?>"
                                             alt="<?= htmlspecialchars(SELLER_DATA['username']) ?>"
                                             style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:36px;height:36px;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff;">
                                            <?= strtoupper(substr(SELLER_DATA['username'], 0, 1)) ?>
                                        </div>
                                    <?php endif ?>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="mb-0 text-truncate"><?= htmlspecialchars(SELLER_DATA['username']) ?></h5>
                                        <p class="card-text text-body text-truncate mb-0"><?= htmlspecialchars(SELLER_DATA['email']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/dashboard">
                                <i class="fa-duotone fa-objects-column nav-icon"></i> Dashboard
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/accounts">
                                <i class="fa-duotone fa-database nav-icon"></i> My Accounts
                            </a>
                            <a class="dropdown-item<?= $account_orders_active ?>" href="<?= BASE_URL ?>/seller-area/account-orders">
                                <i class="fa-duotone fa-cart-shopping nav-icon"></i> Account Orders
                            </a>
<a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/import-accounts">
                                <i class="fa-duotone fa-file-import nav-icon"></i> Import Accounts
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/items">
                                <i class="fa-duotone fa-gift nav-icon"></i> My Items
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/item-orders">
                                <i class="fa-duotone fa-bag-shopping nav-icon"></i> Item Orders
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/top-ups">
                                <i class="fa-duotone fa-coins nav-icon"></i> My Top Ups
                            </a>
                            <a class="dropdown-item<?= $topup_orders_active ?>" href="<?= BASE_URL ?>/seller-area/top-up-orders">
                                <i class="fa-duotone fa-receipt nav-icon"></i> Top Up Orders
                            </a>
                            <?php if (!empty(SELLER_DATA['can_list_digital_goods'])): ?>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/digital-goods/listings">
                                <i class="fa-duotone fa-box-open nav-icon"></i> Digital Goods
                            </a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/payments">
                                <i class="fa-duotone fa-money-bill-transfer nav-icon"></i> Payments
                            </a>
                            <a class="dropdown-item<?= $seller_payout_requests_active ?>" href="<?= BASE_URL ?>/seller-area/payout-requests">
                                <i class="fa-duotone fa-wallet nav-icon"></i> Payout Requests
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/api">
                                <i class="fa-duotone fa-key nav-icon"></i> Partner API
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/seller-area/chat">
                                <span><i class="fa-duotone fa-comments nav-icon"></i> Chat Inbox</span>
                                <span class="seller-sidebar-chat-badge ms-auto<?= $sl_chat_unread > 0 ? '' : ' d-none' ?>" id="sellerSidebarChatUnreadDropdown">
                                    <?= $sl_chat_unread > 99 ? '99+' : (int)$sl_chat_unread ?>
                                </span>
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/profile">
                                <i class="fa-solid fa-gear nav-icon"></i> Settings
                            </a>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/seller-area/auth/logout">
                                <i class="fa-duotone fa-right-from-bracket nav-icon"></i> Sign out
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</aside>

<style>
#sellerSidebarChatLink.seller-sidebar-chat-link{
    position:relative;
    padding-right:2.15rem;
}
#sellerSidebarChatLink .seller-sidebar-chat-badge{
    position:absolute;
    right:.72rem;
    top:50%;
    transform:translateY(-50%);
    z-index:3;
}
.seller-sidebar-chat-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:18px;
    height:18px;
    padding:0 6px;
    border-radius:999px;
    background:#7367f0;
    color:#fff;
    font-size:11px;
    font-weight:800;
    line-height:18px;
    margin-left:auto;
    box-shadow:0 0 0 2px rgba(115,103,240,.18);
}
.seller-sidebar-chat-badge.d-none{display:none!important;}
</style>
<script>
(function () {
    document.addEventListener('shown.bs.dropdown', function (e) {
        if (e.target.id !== 'sidebarUserDropdown') return;
        var menu = e.target.closest('.dropdown').querySelector('.dropdown-menu');
        var trigger = e.target;
        var rect = trigger.getBoundingClientRect();
        menu.style.position   = 'fixed';
        menu.style.top        = 'auto';
        menu.style.bottom     = (window.innerHeight - rect.top + 8) + 'px';
        menu.style.left       = (rect.left + 8) + 'px';
        menu.style.right      = 'auto';
        menu.style.transform  = 'none';
        menu.style.zIndex     = '9999';
    });
})();
(function () {
    var badges = Array.prototype.slice.call(document.querySelectorAll('.seller-sidebar-chat-badge'));
    if (!badges.length) return;
    function setSidebarChatUnread(value) {
        var n = parseInt(value || 0, 10) || 0;
        currentUnread = n;
        badges.forEach(function (badge) {
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.classList.toggle('d-none', n <= 0);
        });
    }
    var boundSocket = null;
    var subscribeTimer = null;
    var currentUnread = <?= (int)$sl_chat_unread ?>;
    var allowZeroUpdate = currentUnread <= 0;
    var authoritativeTimer = null;
    function refreshAuthoritativeUnread() {
        clearTimeout(authoritativeTimer);
        authoritativeTimer = setTimeout(function () {
            var fd = new FormData();
            fd.append('action', 'seller_chat_unread_count');
            fetch(typeof AJAX_URL !== 'undefined' ? AJAX_URL : '<?= AJAX_URL ?>', {
                method: 'POST', body: fd, credentials: 'same-origin', cache: 'no-store'
            }).then(function (r) { return r.json(); }).then(function (payload) {
                if (!payload || payload.success === false) return;
                var canonical = typeof window.getCanonicalSellerInboxUnread === 'function'
                    ? window.getCanonicalSellerInboxUnread()
                    : null;
                var nextUnread = parseInt(canonical !== null ? canonical : (payload.unread != null ? payload.unread : payload.total), 10) || 0;
                setSidebarChatUnread(nextUnread);
                // Only the authoritative AJAX result may confirm a real zero — arm the
                // guard from the value we actually just fetched, not unconditionally.
                // Otherwise a single refresh (even one that returns a nonzero count)
                // disarms the anti-stomp guard below and a later, possibly stale
                // socket "chat_unread_update" push can zero the badge right back out.
                allowZeroUpdate = nextUnread <= 0;
            }).catch(function () {});
        }, 180);
    }
    function isIncomingSellerMessage(payload) {
        if (!payload || payload.read_receipt_update || payload.deleted || payload.event === 'read') return false;
        var sender = String(payload.sender_type || payload.sender || payload.from_role || '').toLowerCase();
        return sender === 'client';
    }
    function subscribeUnread() {
        var socket = window.lbSocket || window.socket || null;
        if (!socket || !socket.connected) return;
        if (boundSocket !== socket) {
            boundSocket = socket;
            socket.on('chat_unread_update', function (payload) {
                // ok === false means the relayed unread-count lookup on the socket server failed
                // (e.g. session/auth hiccup on that request) — a real "0" always has ok !== false,
                // so never let a failed lookup stomp a correct badge with zero.
                if (!payload || payload.role !== 'seller' || payload.ok === false) return;
                var canonical = typeof window.getCanonicalSellerInboxUnread === 'function'
                    ? window.getCanonicalSellerInboxUnread()
                    : null;
                var nextUnread = canonical !== null ? canonical : (parseInt(payload.unread != null ? payload.unread : payload.total, 10) || 0);
                // The first websocket subscription can occasionally lose the PHP session
                // context and return a false zero. Keep the exact server-rendered count until
                // this tab receives an explicit seller read-receipt event.
                if (nextUnread === 0 && currentUnread > 0 && !allowZeroUpdate) return;
                setSidebarChatUnread(nextUnread);
                allowZeroUpdate = nextUnread <= 0;
            });
            socket.on('chat_unread_refresh', function () {
                clearTimeout(subscribeTimer);
                subscribeTimer = setTimeout(function () {
                    try { socket.emit('chat_unread_subscribe', {role:'seller'}); } catch (e) {}
                }, 120);
            });
            socket.on('connect', function () {
                try { socket.emit('chat_unread_subscribe', {role:'seller'}); } catch (e) {}
            });
        }
        try { socket.emit('chat_unread_subscribe', {role:'seller'}); } catch (e) {}
    }
    setSidebarChatUnread(<?= (int)$sl_chat_unread ?>);
    var attempts = 0;
    var waitForSocket = setInterval(function () {
        attempts++;
        subscribeUnread();
        if ((boundSocket && boundSocket.connected) || attempts >= 30) clearInterval(waitForSocket);
    }, 250);
    window.addEventListener('lb-chat-update', function (event) {
        var payload = event && event.detail ? event.detail : {};
        if (payload.reader_role === 'seller' || payload.viewer_role === 'seller') {
            allowZeroUpdate = true;
        }
        if (isIncomingSellerMessage(payload)) setSidebarChatUnread(currentUnread + 1);
        refreshAuthoritativeUnread();
    });
    window.addEventListener('load', function(){ subscribeUnread(); refreshAuthoritativeUnread(); });
    window.addEventListener('focus', subscribeUnread);
    window.addEventListener('lb-socket-ready', subscribeUnread);
    window.refreshSidebarChatUnread = refreshAuthoritativeUnread;
    window.incrementSellerSidebarChatUnread = function () { setSidebarChatUnread(currentUnread + 1); };
    window.setSidebarChatUnread = setSidebarChatUnread;
})();
</script>
