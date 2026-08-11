<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title><?= $meta['title'] ?></title>

    <!-- Favicon -->

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">

    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

    <!-- CSS Front Template -->

    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme.min.css?v2.2" data-hs-appearance="light"
        as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v2.2" data-hs-appearance="dark"
        as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v2.2" data-hs-appearance="default"
        as="style">

    <link rel="stylesheet" href="<?= ASSET_URL ?>/core/dash/css/main.css?v<?= rand(0, 34534) ?>">
    
    
    <style data-hs-appearance-onload-styles>
        * {
            transition: unset !important;
        }

        body {
            opacity: 0;
        }
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
                "extend": {
                    "switcherSupport": true
                },
                "header": {
                    "layoutMode": "default",
                    "containerMode": "container-fluid"
                },
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
                        "gray": {
                            "100": "#f9fafc",
                            "900": "#1e2022"
                        }
                    },
                    "font": "Montserrat"
                }
            },
            "languageDirection": {
                "lang": "en"
            }
        }
    </script>
    <?= $this->section('styles') ?>
</head>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl">

    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>
    <!-- ========== HEADER ========== -->

    <?= $this->insert('admin/layouts/partials/header') ?>

    <!-- ========== END HEADER ========== -->
    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed"
        style="
    top: 75px;
">
    </div>
    <!-- ========== MAIN CONTENT ========== -->
    <main id="content" role="main" class="main">
        <!-- Navbar Vertical -->

        <?= $this->insert('admin/layouts/partials/sidebar') ?>

        <!-- End Navbar Vertical -->

        <!-- Content -->
        <div class="content container">

            <?php if (isset($contain)) : ?>
            <div class="row justify-content-lg-center">
                <div class="col-lg-10">
                    <?php endif ?>
                    <?php if (isset($meta['h1'])) : ?>
                    <div class="page-header-reset mb-5">
                        <div class="row align-items-center">
                            <div class="col-sm">
                                <h2 class="page-header-title"><?= $meta['h1'] ?></h2>
                                <p class="page-header-text"><?= $meta['description'] ?? null ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?= $this->section('content') ?>
                    <?php if (isset($contain)) : ?>
                </div>
            </div>
            <?php endif ?>
            <!-- End Row -->
        </div>
        <!-- End Content -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->

    <!-- ========== SECONDARY CONTENTS ========== -->
    <!-- Welcome Message -->
    <div class="modal fade" id="welcomeMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-close">
                    <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="bi-x-lg"></i>
                    </button>
                </div>
                <!-- End Header -->

                <!-- Body -->
                <div class="modal-body p-sm-5">
                    <div class="text-center">
                        <div class="w-75 w-sm-50 mx-auto mb-4">
                            <img class="img-fluid"
                                src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-collaboration.svg"
                                alt="Image Description" data-hs-theme-appearance="default">
                            <img class="img-fluid"
                                src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-collaboration.svg"
                                alt="Image Description" data-hs-theme-appearance="dark">
                            <img class="img-fluid"
                                src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-collaboration.svg"
                                alt="Image Description" data-hs-theme-appearance="light">
                        </div>

                        <h4 class="h1">Welcome to Front</h4>

                        <p>We're happy to see you in our community.</p>
                    </div>
                </div>
                <!-- End Body -->

                <!-- Footer -->
                <div class="modal-footer d-block text-center py-sm-5">
                    <small class="text-cap text-muted">Trusted by the world's best teams</small>

                    <div class="w-85 mx-auto">
                        <div class="row justify-content-between">
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/gitlab-gray.svg"
                                    alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/fitbit-gray.svg"
                                    alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/flow-xo-gray.svg"
                                    alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/layar-gray.svg"
                                    alt="Image Description">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Footer -->
            </div>
        </div>
    </div>

    <!-- End Welcome Message -->
    <!-- ========== END SECONDARY CONTENTS ========== -->

    <!-- JS Global Compulsory  -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS Implementing Plugins -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside.min.js">
    </script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables.net.extensions/select/select.min.js"></script>

    <!-- JS Front -->
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>
    <script>
        const asset_url = '<?= ASSET_URL ?>';
        const ajax_url = '<?= AJAX_URL ?>';
        const base_url = '<?= BASE_URL ?>';
    </script>
    <script src="<?= ASSET_URL ?>/core/dash/js/main.js?v<?= rand(0, 34534) ?>"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js?<?= rand(0, 34534) ?>"></script>

    <!-- JS Plugins Init. -->
    <script>
        (function() {


            // INITIALIZATION OF NAVBAR VERTICAL ASIDE
            // =======================================================
            new HSSideNav('.js-navbar-vertical-aside').init()


            // INITIALIZATION OF BOOTSTRAP DROPDOWN
            // =======================================================
            HSBsDropdown.init()
        })()
    </script>

    <!-- Style Switcher JS -->

    <script>
        (function() {
            // STYLE SWITCHER
            // =======================================================
            const $dropdownBtn = document.getElementById('selectThemeDropdown') // Dropdowon trigger
            const $variants = document.querySelectorAll(
                `[aria-labelledby="selectThemeDropdown"] [data-icon]`) // All items of the dropdown

            // Function to set active style in the dorpdown menu and set icon for dropdown trigger
            const setActiveStyle = function() {
                $variants.forEach($item => {
                    if ($item.getAttribute('data-value') === HSThemeAppearance.getOriginalAppearance()) {
                        $dropdownBtn.innerHTML = `<i class="${$item.getAttribute('data-icon')}" />`
                        return $item.classList.add('active')
                    }

                    $item.classList.remove('active')
                })
            }

            // Add a click event to all items of the dropdown to set the style
            $variants.forEach(function($item) {
                $item.addEventListener('click', function() {
                    HSThemeAppearance.setAppearance($item.getAttribute('data-value'))
                })
            })

            // Call the setActiveStyle on load page
            setActiveStyle()

            // Add event listener on change style to call the setActiveStyle function
            window.addEventListener('on-hs-appearance-change', function() {
                setActiveStyle()
            })
        })()
    </script>

    <!-- End Style Switcher JS -->

    <!-- Admin Presence Ping (online/offline) -->
    <script>
    (function () {
      const PING_URL = '/admin_presence_ping.php';
      // The seller dashboard treats an admin as online for 10 minutes after the
      // last ping, so 4 min leaves room for one dropped ping and still cuts the
      // request volume — and with it the new-MySQL-connections/sec — by 4x.
      const INTERVAL_MS = 240000;
      const MIN_GAP_MS = 120000;
      // Must outlive one interval, otherwise the cross-tab lock expires between
      // pings and every open tab starts pinging on its own timer again.
      const MASTER_TTL_MS = 300000;
      let lastSentAt = 0;

      function lbPresenceCanSend(lockKey, ttlMs) {
      if (document.visibilityState !== 'visible') return false;
      let tabIdKey = lockKey + '_tab_id';
      let tabId = sessionStorage.getItem(tabIdKey);
      if (!tabId) {
        tabId = Math.random().toString(36).slice(2) + Date.now().toString(36);
        sessionStorage.setItem(tabIdKey, tabId);
      }
      let now = Date.now();
      let lock = {};
      try { lock = JSON.parse(localStorage.getItem(lockKey) || '{}'); } catch(e) { lock = {}; }
      if (!lock.tabId || !lock.expires || lock.expires < now || lock.tabId === tabId) {
        try { localStorage.setItem(lockKey, JSON.stringify({ tabId: tabId, expires: now + ttlMs })); } catch(e) {}
        return true;
      }
      return false;
    }

      async function ping() {
        if (document.visibilityState !== 'visible') return;
        if (!lbPresenceCanSend('lb_admin_presence_master', MASTER_TTL_MS)) return;
        const now = Date.now();
        if (now - lastSentAt < MIN_GAP_MS) return;
        lastSentAt = now;

        if (window.lbSocket && window.lbSocket.connected) {
          try {
            window.lbSocket.emit('presence_ping', { role: 'admin', visible: 1, focus: (document.hasFocus && document.hasFocus()) ? 1 : 0, path: location.pathname });
            return;
          } catch (e) {}
        }

        try {
          await fetch(PING_URL, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ping=1&v=1&focus=' + ((document.hasFocus && document.hasFocus()) ? '1' : '0') + '&path=' + encodeURIComponent(location.pathname)
          });
        } catch (e) {}
      }

      ping();
      setInterval(ping, INTERVAL_MS);
      document.addEventListener('visibilitychange', ping);
      window.addEventListener('focus', ping);
    })();
    </script>

    <!-- Realtime (Socket.IO) — admin order views listen on this for live chat/status updates
         instead of constant AJAX polling. Pages define window.lbOrderViewChatUpdate etc. -->
    <script src="https://socket.lolboost.gg/socket.io/socket.io.js"></script>
    <script>
    (function(){
      if (window.__lbRealtimeInit) return;
      window.__lbRealtimeInit = true;
      window.lbRealtimeConnected = false;

      function startRealtime(){
        if (typeof io === 'undefined') {
          setTimeout(startRealtime, 500);
          return;
        }
        var socket = io('https://socket.lolboost.gg', {
          transports: ['websocket'],
          withCredentials: true,
          reconnection: true,
          reconnectionAttempts: Infinity,
          reconnectionDelay: 2000,
          reconnectionDelayMax: 30000,
          randomizationFactor: 0.5,
          timeout: 20000
        });
        window.lbSocket = socket;

        socket.on('connect', function(){
          window.lbRealtimeConnected = true;
          try { socket.emit('join', 'admins'); } catch(e) {}
        });

        socket.on('disconnect', function(){
          window.lbRealtimeConnected = false;
        });

        socket.on('chat_update', function(data){
          try { if (typeof window.lbOrderViewChatUpdate === 'function') window.lbOrderViewChatUpdate(data); } catch(e) {}
        });

        
        socket.on('notification_update', function(data){
          try { if (typeof window.lbRefreshNotificationBadge === 'function') window.lbRefreshNotificationBadge(data); } catch(e) {}
          try { if (typeof window.lbRefreshSellerChatBadge === 'function') window.lbRefreshSellerChatBadge(data); } catch(e) {}
        });

        socket.on('order_status_update', function(data){
          try { if (typeof window.lbOrderViewStatusUpdate === 'function') window.lbOrderViewStatusUpdate(data); } catch(e) {}
        });

        socket.on('order_account_update', function(data){
          try { if (typeof window.lbOrderViewAccountUpdate === 'function') window.lbOrderViewAccountUpdate(data); } catch(e) {}
        });

        socket.on('price_update', function(data){
          try { if (typeof window.lbOrderViewPriceUpdate === 'function') window.lbOrderViewPriceUpdate(data); } catch(e) {}
        });
      }

      window.addEventListener('load', function(){
        setTimeout(startRealtime, 250);
      });
    })();
    </script>

<?= $this->section('scripts') ?>
</body>

</html>
