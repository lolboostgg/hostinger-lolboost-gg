<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $meta['title'] ?></title>

    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme.min.css?v2.2" data-hs-appearance="light" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v2.2" data-hs-appearance="dark" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v2.2" data-hs-appearance="default" as="style">

    <link rel="stylesheet" href="<?= ASSET_URL ?>/core/dash/css/main.css?v<?= rand(0, 34534) ?>">

    <style data-hs-appearance-onload-styles>
        * { transition: unset !important; }
        body { opacity: 0; }
    </style>

    <script>
        window.hs_config = {
            "autopath": "@@autopath",
            "deleteLine": "hs-builder:delete",
            "deleteLine:build": "hs-builder:build-delete",
            "deleteLine:dist": "hs-builder:dist-delete",
            "previewMode": false,
            "vars": {
                "themeFont": "https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap",
                "version": "?v=1.0"
            },
            "layoutBuilder": {
                "extend": { "switcherSupport": true },
                "header": { "layoutMode": "default", "containerMode": "container-fluid" },
                "sidebarLayout": "default"
            },
            "themeAppearance": {
                "layoutSkin": "default",
                "sidebarSkin": "default",
                "styles": {
                    "colors": {
                        "primary": "#377dff",
                        "transparent": "transparent",
                        "white": "#fff",
                        "dark": "132144",
                        "gray": { "100": "#f9fafc", "900": "#1e2022" }
                    },
                    "font": "Montserrat"
                }
            },
            "languageDirection": { "lang": "en" }
        };
    </script>

    <?= $this->section('styles') ?>
</head>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl">

    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>

    <?= $this->insert('seller/layouts/partials/header') ?>

    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed" style="top: 75px;"></div>

    <main id="content" role="main" class="main">

        <?= $this->insert('seller/layouts/partials/sidebar') ?>

        <div class="content container">
            <?php if (isset($contain)): ?>
            <div class="row justify-content-lg-center">
                <div class="col-lg-10">
            <?php endif ?>

            <?php if (isset($meta['h1'])): ?>
            <div class="page-header-reset mb-5">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <h2 class="page-header-title"><?= $meta['h1'] ?></h2>
                        <p class="page-header-text"><?= $meta['description'] ?? null ?></p>
                    </div>
                </div>
            </div>
            <?php endif ?>

            <?= $this->section('content') ?>

            <?php if (isset($contain)): ?>
                </div>
            </div>
            <?php endif ?>
        </div>

    </main>

    <!-- JS Global -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables.net.extensions/select/select.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>

    <script>
        const asset_url  = '<?= ASSET_URL ?>';
        const ajax_url   = '<?= AJAX_URL ?>';
        const base_url   = '<?= BASE_URL ?>';
        const seller_url = '<?= BASE_URL ?>/seller-area';
        const AJAX_URL   = '<?= AJAX_URL ?>';
        const BASE_URL   = '<?= BASE_URL ?>';
    </script>

    <script src="<?= ASSET_URL ?>/core/dash/js/main.js?v<?= rand(0, 34534) ?>"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js?<?= rand(0, 34534) ?>"></script>

    <script>
        (function () {
            new HSSideNav('.js-navbar-vertical-aside').init();
            HSBsDropdown.init();
        })();
    </script>

    <script>
        (function () {
            const $dropdownBtn = document.getElementById('selectThemeDropdown');
            const $variants    = document.querySelectorAll('[aria-labelledby="selectThemeDropdown"] [data-icon]');

            const setActiveStyle = function () {
                $variants.forEach($item => {
                    if ($item.getAttribute('data-value') === HSThemeAppearance.getOriginalAppearance()) {
                        $dropdownBtn.innerHTML = '<i class="' + $item.getAttribute('data-icon') + '" />';
                        return $item.classList.add('active');
                    }
                    $item.classList.remove('active');
                });
            };

            $variants.forEach(function ($item) {
                $item.addEventListener('click', function () {
                    HSThemeAppearance.setAppearance($item.getAttribute('data-value'));
                });
            });

            setActiveStyle();
            window.addEventListener('on-hs-appearance-change', function () { setActiveStyle(); });
        })();
    </script>



    <script>
        (function () {
            var PING_URL = '/dashboard_presence_ping.php';
            // Public pages show a seller as online for 5 minutes after the last
            // ping, so 2.5 min survives one dropped ping and halves the request
            // volume (and the new MySQL connections each request opens).
            var INTERVAL_MS = 150000; // base heartbeat while visible
            var MIN_GAP_MS = 75000;   // never fire more than once per 75s, no matter how many
                                      // focus/visibilitychange events the browser throws at us
            // Without this, every open seller tab pinged on its own, so the request
            // (and MySQL connection) rate scaled with tab count. One tab holds the
            // lock; it expires so a closed tab can't block the others.
            var MASTER_TTL_MS = 210000;
            var lastSentAt = 0;

            function lbPresenceCanSend(lockKey, ttlMs) {
                if (document.visibilityState !== 'visible') return false;
                var tabIdKey = lockKey + '_tab_id';
                var tabId = sessionStorage.getItem(tabIdKey);
                if (!tabId) {
                    tabId = Math.random().toString(36).slice(2) + Date.now().toString(36);
                    sessionStorage.setItem(tabIdKey, tabId);
                }
                var now = Date.now();
                var lock = {};
                try { lock = JSON.parse(localStorage.getItem(lockKey) || '{}'); } catch (e) { lock = {}; }
                if (!lock.tabId || !lock.expires || lock.expires < now || lock.tabId === tabId) {
                    try { localStorage.setItem(lockKey, JSON.stringify({ tabId: tabId, expires: now + ttlMs })); } catch (e) {}
                    return true;
                }
                return false;
            }

            function sendPing() {
                var visible = (document.visibilityState === 'visible') ? '1' : '0';
                var focus = (document.hasFocus && document.hasFocus()) ? '1' : '0';
                if (visible !== '1') return;
                if (!lbPresenceCanSend('lb_seller_presence_master', MASTER_TTL_MS)) return;

                var now = Date.now();
                if (now - lastSentAt < MIN_GAP_MS) return;
                lastSentAt = now;

                var fd = new FormData();
                fd.append('v', visible);
                fd.append('focus', focus);
                fd.append('user_type', 'seller');

                fetch(PING_URL, { method: 'POST', body: fd, credentials: 'include', keepalive: true })
                    .catch(function(){});
            }

            sendPing();
            setInterval(sendPing, INTERVAL_MS);
            document.addEventListener('visibilitychange', sendPing);
            window.addEventListener('focus', sendPing);
        })();
    </script>

    <!-- Unified realtime chat bridge -->
    <script src="https://socket.lolboost.gg/socket.io/socket.io.js"></script>
    <script>
    (function(){
      if(window.__lbRealtimeInit)return; window.__lbRealtimeInit=true; window.lbRealtimeConnected=false; var lastEvents=new Map();
      function payloadOf(data){return data&&data.data&&typeof data.data==='object'?Object.assign({},data,data.data):(data||{});}
      function dispatchChat(data){var p=payloadOf(data),key=[p.order_id||'',p.purchase_id||'',p.account_id||'',p.message_id||'',p.read_receipt_update?'r':'',p.updated_at||''].join('|'),now=Date.now();if(key!=='|||||'&&lastEvents.has(key)&&now-lastEvents.get(key)<250)return;lastEvents.set(key,now);if(lastEvents.size>100)lastEvents.delete(lastEvents.keys().next().value);try{if(typeof window.lbOrderViewChatUpdate==='function')window.lbOrderViewChatUpdate(p);}catch(e){}try{if(typeof window.lbTopupSellerChatUpdate==='function'&&(String(p.order_id||'').indexOf('topuppurch_')===0||p.purchase_id))window.lbTopupSellerChatUpdate(p);}catch(e){}try{if(typeof window.lbRefreshSellerChatBadge==='function')window.lbRefreshSellerChatBadge(p);}catch(e){}try{window.dispatchEvent(new CustomEvent('lb-chat-update',{detail:p}));}catch(e){}}
      function startRealtime(){if(typeof io==='undefined'){setTimeout(startRealtime,500);return;}var socket=io('https://socket.lolboost.gg',{transports:['websocket','polling'],withCredentials:true,reconnection:true,reconnectionAttempts:Infinity,reconnectionDelay:1000,reconnectionDelayMax:10000});window.lbSocket=socket;socket.on('connect',function(){window.lbRealtimeConnected=true;try{socket.emit('join','sellers');}catch(e){}try{window.dispatchEvent(new CustomEvent('lb-socket-ready',{detail:{socket:socket}}));}catch(e){}});socket.on('disconnect',function(){window.lbRealtimeConnected=false;});['chat_update','client_chat_update','seller_chat_update','account_chat_update','item_chat_update','topup_chat_update','seller_topup_chat_update'].forEach(function(name){socket.on(name,dispatchChat);});socket.on('order_status_update',function(data){try{if(typeof window.lbOrderViewStatusUpdate==='function')window.lbOrderViewStatusUpdate(payloadOf(data));}catch(e){}});socket.on('notification_update',function(data){try{if(typeof window.lbRefreshSellerNotificationBadge==='function')window.lbRefreshSellerNotificationBadge(data);}catch(e){}});}
      window.addEventListener('load',function(){setTimeout(startRealtime,100);});
    })();
    </script>

    <?= $this->section('scripts') ?>
</body>

</html>
