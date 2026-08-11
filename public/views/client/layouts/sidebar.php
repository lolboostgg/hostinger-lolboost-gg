<?php
$client_sidebar_chat_unread = 0;

// Client unread counter: mirrored from the seller inbox logic.
// Counts seller messages that are unseen by the client and newer than the client's latest reply.
if (defined('CLIENT_ID') && (int) CLIENT_ID > 0) {
    $client_sidebar_id = (int) CLIENT_ID;
    $client_sidebar_chat_dir = (defined('SYS_PATH') ? SYS_PATH : dirname(__DIR__, 3)) . '/public/uploads/private/chat';

    $client_sidebar_msg_order = function (array $m, int $index): int {
        if (!empty($m['time']) && is_numeric($m['time'])) { return (int)$m['time']; }
        if (!empty($m['created_at'])) {
            $ts = strtotime((string)$m['created_at']);
            if ($ts !== false) { return (int)$ts; }
        }
        return $index;
    };

    $client_sidebar_sender = function (array $m): string {
        $sender = strtolower(trim((string)($m['sender_type'] ?? $m['sender'] ?? $m['from'] ?? '')));
        $type = strtolower(trim((string)($m['type'] ?? '')));
        if ($sender === '' && in_array($type, ['client', 'seller', 'system'], true)) { $sender = $type; }
        return $sender;
    };

    $client_sidebar_belongs = function (array $data, array $messages) use ($client_sidebar_id, $client_sidebar_sender): bool {
        if ((int)($data['client_id'] ?? 0) === $client_sidebar_id) { return true; }
        foreach ($messages as $m) {
            if (!is_array($m) || !empty($m['deleted'])) { continue; }
            if ($client_sidebar_sender($m) === 'client' && (int)($m['sender_id'] ?? 0) === $client_sidebar_id) { return true; }
        }
        return false;
    };

    $client_sidebar_count_file = function (string $chat_file) use ($client_sidebar_msg_order, $client_sidebar_sender, $client_sidebar_belongs): int {
        if (!is_file($chat_file)) { return 0; }
        $data = json_decode(@file_get_contents($chat_file) ?: '', true);
        if (!is_array($data) || empty($data['messages']) || !is_array($data['messages'])) { return 0; }
        $messages = array_values($data['messages']);
        if (!$client_sidebar_belongs($data, $messages)) { return 0; }

        $last_client_value = 0;
        foreach ($messages as $idx => $m) {
            if (!is_array($m) || !empty($m['deleted'])) { continue; }
            if ($client_sidebar_sender($m) === 'client') {
                $last_client_value = max($last_client_value, $client_sidebar_msg_order($m, $idx + 1));
            }
        }

        $count = 0;
        foreach ($messages as $idx => $m) {
            if (!is_array($m) || !empty($m['deleted'])) { continue; }
            if ($client_sidebar_sender($m) !== 'seller') { continue; }

            if (array_key_exists('seen_by_client', $m)) {
                $seen_by_client = (int)$m['seen_by_client'];
            } elseif (array_key_exists('seen', $m)) {
                $seen_by_client = (int)$m['seen'];
            } elseif (array_key_exists('is_read', $m)) {
                $seen_by_client = (int)$m['is_read'];
            } else {
                $seen_by_client = 1;
            }
            if ($seen_by_client === 1) { continue; }

            $message_value = $client_sidebar_msg_order($m, $idx + 1);
            if ($last_client_value > 0 && $message_value <= $last_client_value) { continue; }
            $count++;
        }
        return $count;
    };

    if (is_dir($client_sidebar_chat_dir)) {
        foreach (glob($client_sidebar_chat_dir . '/selling_*.json') ?: [] as $client_sidebar_chat_file) {
            $client_sidebar_chat_unread += $client_sidebar_count_file($client_sidebar_chat_file);
        }
    }
}
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
                            <?php if ((int)($client_sidebar_chat_unread ?? 0) > 0): ?>
                                <span class="client-sidebar-chat-badge" id="clientSidebarChatBadge"><?= (int)$client_sidebar_chat_unread ?></span>
                            <?php endif; ?>
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
                            <span class="nav-link-title">Payments</span>
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
                    <span class="dropdown-header mt-4">Profile</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile/settings" data-link="settings"
                            data-placement="left">
                            <i class="fa-duotone fa-cog nav-icon"></i>
                            <span class="nav-link-title">Settings</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-4">Buy new Boost</span>

                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/lol/rank-boost" data-link="lol/rank-boost"
                            data-placement="left">
                            <i class="fa-duotone fa-rocket-launch nav-icon"></i>
                            <span class="nav-link-title">LoL Boosting</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/lol/premium-accounts" data-link="lol/accounts"
                            data-placement="left">
                            <i class="fa-duotone fa-helmet-battle nav-icon"></i>
                            <span class="nav-link-title">LoL Accounts</span>
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