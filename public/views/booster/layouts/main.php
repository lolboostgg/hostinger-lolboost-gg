<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title><?= $meta['title'] ?></title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">

    <!-- FontAwesome -->
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

    <!-- CSS Front Template -->
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
                        "gray": { "100": "#f9fafc", "900": "#1e2022" }
                    },
                    "font": "Montserrat"
                }
            },
            "languageDirection": { "lang": "en" }
        }
    </script>

    <?= $this->section('styles') ?>
</head>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl<?= (defined('IS_EGIRL') && IS_EGIRL) ? ' eg-area' : '' ?>">

    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>

    <!-- ========== HEADER ========== -->
    <?= $this->insert('booster/layouts/partials/header') ?>
    <!-- ========== END HEADER ========== -->

    <!-- (Optional) legacy toast container (kept for compatibility) -->
    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed" style="top: 75px;"></div>

    <!-- ========== MAIN CONTENT ========== -->
    <main id="content" role="main" class="main">
        <!-- Navbar Vertical -->
        <?php if (defined('IS_EGIRL') && IS_EGIRL): ?>
        <?= $this->insert('booster/layouts/partials/egirl_sidebar') ?>
    <?php else: ?>
        <?= $this->insert('booster/layouts/partials/sidebar') ?>
    <?php endif; ?>
        <!-- End Navbar Vertical -->

        <!-- Content -->
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
            <?php endif; ?>

            <?= $this->section('content') ?>

            <?php if (isset($contain)): ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
        <!-- End Content -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->

    <!-- ========== SECONDARY CONTENTS ========== -->
    <!-- Welcome Message -->
    <div class="modal fade" id="welcomeMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-close">
                    <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body p-sm-5">
                    <div class="text-center">
                        <div class="w-75 w-sm-50 mx-auto mb-4">
                            <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-collaboration.svg" alt="Image Description" data-hs-theme-appearance="default">
                            <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-collaboration.svg" alt="Image Description" data-hs-theme-appearance="dark">
                            <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-collaboration.svg" alt="Image Description" data-hs-theme-appearance="light">
                        </div>

                        <h4 class="h1">Welcome to Front</h4>
                        <p>We're happy to see you in our community.</p>
                    </div>
                </div>

                <div class="modal-footer d-block text-center py-sm-5">
                    <small class="text-cap text-muted">Trusted by the world's best teams</small>
                    <div class="w-85 mx-auto">
                        <div class="row justify-content-between">
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/gitlab-gray.svg" alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/fitbit-gray.svg" alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/flow-xo-gray.svg" alt="Image Description">
                            </div>
                            <div class="col">
                                <img class="img-fluid" src="<?= ASSET_URL ?>/origin/dash/svg/brands/layar-gray.svg" alt="Image Description">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- End Welcome Message -->
    <!-- ========== END SECONDARY CONTENTS ========== -->

    <!-- JS Global Compulsory -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS Implementing Plugins -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/datatables.net.extensions/select/select.min.js"></script>

    <!-- JS Front -->
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>

    <script>
        const asset_url = '<?= ASSET_URL ?>';
        const ajax_url = '<?= AJAX_URL ?>';
        const base_url = '<?= BASE_URL ?>';
        const is_egirl = <?= (defined('IS_EGIRL') && IS_EGIRL) ? '1' : '0' ?>;
    </script>

    <script src="<?= ASSET_URL ?>/core/dash/js/main.js?v<?= rand(0, 34534) ?>"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js?<?= rand(0, 34534) ?>"></script>

    <!-- JS Plugins Init. -->
    <script>
        (function () {
            new HSSideNav('.js-navbar-vertical-aside').init();
            HSBsDropdown.init();
        })();
    </script>

    <!-- Style Switcher JS -->
    <script>
        (function () {
            const $dropdownBtn = document.getElementById('selectThemeDropdown');
            const $variants = document.querySelectorAll(`[aria-labelledby="selectThemeDropdown"] [data-icon]`);

            const setActiveStyle = function () {
                $variants.forEach($item => {
                    if ($item.getAttribute('data-value') === HSThemeAppearance.getOriginalAppearance()) {
                        $dropdownBtn.innerHTML = `<i class="${$item.getAttribute('data-icon')}" />`;
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

            window.addEventListener('on-hs-appearance-change', function () {
                setActiveStyle();
            });
        })();
    </script>
    <!-- End Style Switcher JS -->

    
    <style>
      .lb-request-modal .modal-content{background:#1f2226;border:1px solid rgba(255,255,255,.08);border-radius:18px;box-shadow:0 30px 80px rgba(0,0,0,.55);}
      .lb-request-modal .modal-header{padding:18px 18px 0 18px;border:0;}
      .lb-request-head{display:flex;align-items:center;gap:12px;width:100%;}
      .lb-request-icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(139,92,246,.14);border:1px solid rgba(139,92,246,.22);color:rgba(255,255,255,.92);box-shadow:0 16px 45px rgba(0,0,0,.45);}
      .lb-request-modal .modal-title{font-weight:900;letter-spacing:.01em;margin:0;}
      .lb-request-sub{font-size:.86rem;opacity:.72;margin-top:2px;}
      .lb-request-modal .modal-body{padding:14px 18px 10px 18px;}
      .lb-request-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:12px 14px;}
      .lb-request-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px 2px;}
      .lb-request-row+.lb-request-row{border-top:1px solid rgba(255,255,255,.06);}
      .lb-request-k{font-size:.72rem;letter-spacing:.10em;text-transform:uppercase;opacity:.65;}
      .lb-request-v{font-weight:800;text-align:right;max-width:68%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
      .lb-request-modal .modal-footer{padding:12px 18px 18px 18px;border-top:0;gap:10px;}
      .lb-request-modal .btn{border-radius:999px;}
      [data-theme="light"] .lb-request-modal .modal-content{background:#fff;border-color:rgba(0,0,0,.08);box-shadow:0 22px 60px rgba(0,0,0,.18);}
      [data-theme="light"] .lb-request-card{background:rgba(0,0,0,.03);border-color:rgba(0,0,0,.08);}
      [data-theme="light"] .lb-request-row+.lb-request-row{border-top-color:rgba(0,0,0,.06);}
    </style>

    <!-- Booster requested-order modal -->
    <div class="modal fade lb-request-modal" id="boosterRequestedOrderModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <div class="lb-request-head">
              <div class="lb-request-icon"><i class="fa-duotone fa-bolt"></i></div>
              <div class="flex-grow-1">
                <h5 class="modal-title" id="boosterRequestedOrderTitle">You are requested by Client.</h5>
                <div class="lb-request-sub">Claim it now or decline and return it to the panel.</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
          </div>
          <div class="modal-body">
            <div class="lb-request-card" id="boosterRequestedOrderRows"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-white" id="boosterRequestedDeclineBtn">Decline</button>
            <button type="button" class="btn btn-primary" id="boosterRequestedClaimBtn"><i class="fa-duotone fa-play me-2"></i>Claim Order</button>
          </div>
        </div>
      </div>
    </div>



    <?= $this->section('scripts') ?>

    <?php
    // Auto-open Booster Profile Setup checklist if something is missing (once per session)
    if (defined('BOOSTER_ID') && BOOSTER_ID && function_exists('render_booster_profile_setup_modal')) {
        render_booster_profile_setup_modal(BOOSTER_ID);
    }

    // GG-Girl setup guard: redirect to setup page if profile is incomplete
    if (defined('IS_EGIRL') && IS_EGIRL && defined('BOOSTER_ID') && BOOSTER_ID) {
        $__current_path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $__is_setup_page = (strpos($__current_path, 'egirl-setup') !== false);
        $__is_auth_page  = (strpos($__current_path, 'auth') !== false);

        if (!$__is_setup_page && !$__is_auth_page) {
            global $db;
            $__booster_row  = function_exists('db_get_row') ? db_get_row('boosters', ['id' => BOOSTER_ID]) : [];
            $__profile_row  = isset($db) ? $db->row("SELECT bio, languages, games FROM egirl_profiles WHERE egirl_id = ? LIMIT 1", BOOSTER_ID) : null;
            $__default_icon = 'https://lolboost.gg/public/uploads/icons/default.png';
            $__icon_url     = trim((string)($__booster_row['icon'] ?? $__default_icon));
            $__icon_done    = ($__icon_url !== '' && $__icon_url !== $__default_icon && strpos($__icon_url, '/uploads/icons/default.png') === false);
            $__cover_done   = !empty(trim((string)($__booster_row['cover'] ?? '')));
            $__has_payout   = isset($db) ? (bool)$db->row("SELECT id FROM booster_payout_methods WHERE booster_id = ? AND is_default = 1 LIMIT 1", BOOSTER_ID) : false;
            $__setup_done   = (!empty($__booster_row['discord_id']) || !empty($__booster_row['discord']))
                           && $__icon_done && $__cover_done
                           && !empty(trim((string)($__profile_row['bio'] ?? '')))
                           && !empty(trim((string)($__profile_row['languages'] ?? '')))
                           && !empty(trim((string)($__profile_row['games'] ?? '')))
                           && $__has_payout;

            if (!$__setup_done && function_exists('redirect_url')) {
                redirect_url('booster-area/egirl-setup');
                exit;
            }
        }
    }
    ?>


    <!-- ===========================
         LOLBOOST MODERN TOAST (NEW DESIGN)
         =========================== -->
    <style>
      /* container stays; wrapper is centered */
      .toast-container{
        z-index: 99999;
        pointer-events:none;
      }

      .lb-toast-wrap{
        position: fixed;
        top: 86px;
        left: 50%;
        transform: translateX(-50%);
        width: min(680px, calc(100vw - 32px));
        display: flex;
        flex-direction: column;
        gap: 12px;
        z-index: 99999;
        pointer-events:none;
      }

      /* BIG, HIGH-CONTRAST, GLASSY CARD */
      .lb-toast{
        --accent: #8b5cf6;
        --bg1: rgba(34, 24, 62, .92);
        --bg2: rgba(20, 34, 78, .90);
        --border: rgba(255,255,255,.10);

        pointer-events:auto;
        position: relative;
        overflow: hidden;

        border-radius: 18px;
        background:
          radial-gradient(420px 220px at 18% 5%, rgba(139,92,246,.42), transparent 60%),
          radial-gradient(360px 220px at 88% 110%, rgba(59,130,246,.26), transparent 62%),
          linear-gradient(135deg, var(--bg1), var(--bg2));
        border: 1px solid var(--border);
        box-shadow:
          0 38px 120px rgba(0,0,0,.70),
          0 0 0 1px rgba(0,0,0,.55) inset,
          0 0 70px rgba(139,92,246,.16);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);

        transform: translateY(-10px);
        opacity: 0;
        animation: lbToastIn .24s ease-out forwards, lbGlowPulse 1.8s ease-out 1;
      }

      @keyframes lbToastIn{
        to { transform: translateY(0); opacity: 1; }
      }

      @keyframes lbGlowPulse{
        0%{ box-shadow: 0 38px 120px rgba(0,0,0,.70), 0 0 0 1px rgba(0,0,0,.55) inset, 0 0 110px rgba(139,92,246,.32); }
        100%{ box-shadow: 0 38px 120px rgba(0,0,0,.70), 0 0 0 1px rgba(0,0,0,.55) inset, 0 0 70px rgba(139,92,246,.16); }
      }

      .lb-toast.lb-hide{
        animation: lbToastOut .18s ease-in forwards;
      }

      @keyframes lbToastOut{
        to { transform: translateY(-8px); opacity: 0; }
      }

      /* neon border */
      .lb-toast::before{
        content:"";
        position:absolute;
        inset:0;
        border-radius: 18px;
        padding: 1px;
        background: linear-gradient(135deg,
          rgba(139,92,246,.95),
          rgba(59,130,246,.60),
          rgba(99,102,241,.48)
        );
        -webkit-mask:
          linear-gradient(#000 0 0) content-box,
          linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: .65;
        pointer-events:none;
      }

      /* subtle texture */
      .lb-toast::after{
        content:"";
        position:absolute;
        inset:0;
        background:
          repeating-linear-gradient(
            135deg,
            rgba(255,255,255,.06) 0px,
            rgba(255,255,255,.06) 1px,
            rgba(255,255,255,0) 6px,
            rgba(255,255,255,0) 12px
          );
        opacity:.08;
        pointer-events:none;
      }

      /* top progress bar */
      .lb-toast__bar{
        position:absolute;
        top:0; left:0; right:0;
        height: 4px;
        background: rgba(255,255,255,.10);
        overflow:hidden;
      }
      .lb-toast__bar span{
        display:block;
        height:100%;
        width:100%;
        background: linear-gradient(90deg, rgba(139,92,246,1), rgba(59,130,246,.70), rgba(255,255,255,.18));
        transform-origin: left center;
        animation: lbDrain var(--lb-timeout, 25000ms) linear forwards;
      }
      @keyframes lbDrain{
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
      }

      /* CONTENT LAYOUT: icon | text | button | close */
      .lb-toast__inner{
        position: relative;
        z-index: 1;
        display:grid;
        grid-template-columns: 56px 1fr auto 44px;
        gap: 16px;
        padding: 18px 18px 18px 18px;
        align-items: center;
      }

      .lb-toast__icon{
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display:flex;
        align-items:center;
        justify-content:center;
        background: rgba(255,255,255,.09);
        border: 1px solid rgba(255,255,255,.14);
        box-shadow: 0 10px 26px rgba(0,0,0,.35);
      }
      .lb-toast__icon i{
        font-size: 22px;
        color: #fff;
        opacity: .95;
      }

      .lb-toast__content{
        min-width: 0;
        color: rgba(255,255,255,.95);
      }

      .lb-toast__title{
        font-size: 18px;
        font-weight: 950;
        line-height: 1.15;
        color:#fff;
        margin: 0;
      }

      .lb-toast__text{
        margin-top: 8px;
        font-size: 14.5px;
        color: rgba(255,255,255,.78);
        line-height: 1.35;
      }

      .lb-toast__btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 14px;
        text-decoration:none;
        font-size: 14px;
        font-weight: 950;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.10);
        color: #fff;
        transition: transform .12s ease, background .12s ease, border-color .12s ease, filter .12s ease;
        white-space: nowrap;
      }
      .lb-toast__btn:hover{
        transform: translateY(-1px);
        background: rgba(255,255,255,.14);
        border-color: rgba(255,255,255,.20);
        filter: brightness(1.05);
        color:#fff;
      }

      .lb-toast__btn--primary{
        background: linear-gradient(180deg, rgba(139,92,246,1), rgba(139,92,246,.70));
        border-color: rgba(139,92,246,.55);
        box-shadow: 0 18px 40px rgba(139,92,246,.22);
      }
      .lb-toast__btn--primary:hover{
        background: linear-gradient(180deg, rgba(139,92,246,1), rgba(139,92,246,.78));
      }

      .lb-toast__close{
        width: 44px;
        height: 44px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(0,0,0,.18);
        color: rgba(255,255,255,.80);
        display:flex;
        align-items:center;
        justify-content:center;
        cursor: pointer;
        transition: background .12s ease, transform .12s ease, filter .12s ease;
      }
      .lb-toast__close:hover{
        background: rgba(0,0,0,.26);
        transform: translateY(-1px);
        filter: brightness(1.08);
        color:#fff;
      }

      @media (max-width: 560px){
        .lb-toast-wrap{ top: 76px; width: calc(100vw - 22px); }
        .lb-toast__inner{
          grid-template-columns: 52px 1fr 40px;
          grid-template-rows: auto auto;
          align-items: start;
        }
        .lb-toast__btn{ grid-column: 1 / span 2; width: 100%; }
        .lb-toast__close{ grid-row: 1; grid-column: 3; justify-self: end; }
      }
</style>

    <script>
      // Ensure wrapper exists (center top)
      (function ensureLbToastWrap(){
        let wrap = document.querySelector('.lb-toast-wrap');
        if (wrap) return;
        wrap = document.createElement('div');
        wrap.className = 'lb-toast-wrap';
        document.body.appendChild(wrap);
      })();

      // Modern toast renderer (keeps the same function name used by notifier)
      window.create_order_toast = function(opts){
        const {
          accent = '#8b5cf6',
          title = 'New Order',
          messageHtml = '',
          href = null,
          hrefText = 'Open Order',
          iconClass = 'fa-solid fa-bolt',
          timeoutMs = 25000,
          onBtnClick = null,
        } = (opts || {});

        let wrap = document.querySelector('.lb-toast-wrap');
        if (!wrap) {
          wrap = document.createElement('div');
          wrap.className = 'lb-toast-wrap';
          try { document.body.appendChild(wrap); } catch(e) { return; }
        }

        const toast = document.createElement('div');
        toast.className = 'lb-toast';
        toast.style.setProperty('--accent', accent);
        toast.style.setProperty('--lb-timeout', `${timeoutMs}ms`);

        const buttonHtml = (href || onBtnClick)
          ? `<button type="button" class="lb-toast__btn lb-toast__btn--primary"${href && !onBtnClick ? ` data-lb-href="${href}"` : ''}>
               <i class="fa-solid fa-play"></i>
               <span>${hrefText}</span>
             </button>`
          : ``;

        toast.innerHTML = `
          <div class="lb-toast__bar"><span></span></div>
          <div class="lb-toast__inner">
            <div class="lb-toast__icon"><i class="${iconClass}"></i></div>

            <div class="lb-toast__content">
              <div class="lb-toast__title">${title}</div>
              <div class="lb-toast__text">${messageHtml}</div>
            </div>

            ${buttonHtml}

            <button class="lb-toast__close" type="button" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        `;

        const removeToast = () => {
          if (!toast.isConnected) return;
          toast.classList.add('lb-hide');
          setTimeout(() => { try { toast.remove(); } catch(e) {} }, 200);
        };

        toast.querySelector('.lb-toast__close').addEventListener('click', removeToast);

        // Button-Aktion: entweder Callback (z.B. Modal öffnen) oder Navigation
        const actionBtn = toast.querySelector('.lb-toast__btn--primary');
        if (actionBtn) {
          actionBtn.addEventListener('click', function () {
            removeToast();
            if (typeof onBtnClick === 'function') {
              setTimeout(onBtnClick, 50);
            } else {
              const targetHref = actionBtn.getAttribute('data-lb-href');
              if (targetHref) window.location.href = targetHref;
            }
          });
        }

        wrap.prepend(toast);
        setTimeout(removeToast, timeoutMs);
      };
    </script>

    <!-- =========================================================
         GLOBAL ORDER NOTIFIER (leader election + cross-tab broadcast)
         + safer URL handling + robust JSON parsing
         ========================================================= -->
    <script>
    (function () {
      if (window.__globalOrderNotifierStarted) return;
      window.__globalOrderNotifierStarted = true;

      if (typeof is_egirl !== 'undefined' && parseInt(is_egirl, 10) === 1) return;

      var __AJAX_URL = '<?= AJAX_URL ?>';
      var __BSTR_URL = '<?= defined("BSTR_URL") ? BSTR_URL : BASE_URL ?>';
      
      // Session start (per-tab). Used to avoid notifying about older orders.
      window.__lb_since_ts = window.__lb_since_ts || function(){
var v = 0;
        try { v = parseInt(sessionStorage.getItem('lb_session_start_ms') || '0', 10); } catch(e) {}
        if (!v) {
          v = Date.now();
          try { sessionStorage.setItem('lb_session_start_ms', String(v)); } catch(e) {}
        }
        return Math.floor(v / 1000);
      }

      var __ASSET_URL = '<?= ASSET_URL ?>';

      // WebSocket new_order events are broadcast to the booster room.
      // Keep the old server-side eligibility behavior on the client too,
      // so TFT-only boosters do not get LoL toasts, sounds or browser notifications.
      var __LB_ALLOWED_GAMES = <?= json_encode(array_values(array_filter(array_map(function($g){
        $g = strtolower(trim((string)$g));
        return preg_match('/^[a-z0-9_-]+$/', $g) ? $g : null;
      }, explode('|', (string)(defined('BOOSTER_DATA') && is_array(BOOSTER_DATA) ? (BOOSTER_DATA['games'] ?? '') : ''))))), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

      function lbCanonicalGameSlug(game) {
        var value = String(game || '').trim().toLowerCase().replace(/_/g, '-');
        var aliases = {
          'league-of-legends': 'lol',
          'lol-classic': 'lol-classic',
          'lolclassic': 'lol-classic',
          'valorant': 'val',
          'teamfight-tactics': 'tft',
          'wild-rift': 'lol-wild-rift',
          'wildrift': 'lol-wild-rift',
          'rl': 'rocket-league',
          'apex': 'apex-legends',
          'rivals': 'marvel-rivals',
          'ow2': 'overwatch-2',
          'overwatch': 'overwatch-2'
        };
        return aliases[value] || value;
      }
      __LB_ALLOWED_GAMES = Array.from(new Set((__LB_ALLOWED_GAMES || []).map(lbCanonicalGameSlug)));

      function lbOrderGameAllowed(data) {
        if (!data || data.is_egirl) return true;
        var game = lbCanonicalGameSlug(data.game);
        // Some realtime payloads only contain order_id. In that case do not block
        // the toast on the client; the AJAX validation below checks the form/game
        // against the logged-in booster before anything is shown.
        if (!game) return true;
        return Array.isArray(__LB_ALLOWED_GAMES) && __LB_ALLOWED_GAMES.indexOf(game) !== -1;
      }
      window.lbOrderGameAllowed = lbOrderGameAllowed;

      function now() { return Date.now(); }

      // Safer URL parsing (keep real URL; don't force origin/path)
      try {
        var ajaxU = new URL(__AJAX_URL, window.location.href);
        __AJAX_URL = ajaxU.href;

        var bstrU = new URL(__BSTR_URL, window.location.href);
        __BSTR_URL = bstrU.origin + bstrU.pathname.replace(/\/$/, '');
      } catch (e) {}

      // Tab ID (per-tab)
      var tabId = sessionStorage.getItem('orderNotifierTabId');
      if (!tabId) {
        tabId = 'tab_' + Math.random().toString(16).slice(2) + '_' + now();
        sessionStorage.setItem('orderNotifierTabId', tabId);
      }

      // Cross-tab channel
      var bc = null;
      try { if ('BroadcastChannel' in window) bc = new BroadcastChannel('order_notifier_channel'); } catch (e) {}

      function emitEvent(ev) {
        try { if (bc) bc.postMessage(ev); } catch (e) {}
        try { localStorage.setItem('orderNotifyEvent', JSON.stringify(ev)); } catch (e) {}
      }

      // Per-tab dedup for toast
      function alreadyShown(orderId, dedupeKey) {
        var key = 'shownOrder_' + (dedupeKey || orderId);
        if (sessionStorage.getItem(key) === '1') return true;
        sessionStorage.setItem(key, '1');
        return false;
      }

      function pingedOrderKey(orderId) {
        return 'lb_pinged_order_' + orderId;
      }

      function wasOrderPinged(orderId) {
        try { return localStorage.getItem(pingedOrderKey(orderId)) === '1'; } catch (e) { return false; }
      }

      function markOrderPinged(orderId) {
        try { localStorage.setItem(pingedOrderKey(orderId), '1'); } catch (e) {}
      }

      // =========================
      // AUDIO UNLOCK + RELIABLE PLAY
      // =========================
      var AUDIO_UNLOCK_KEY = 'lb_audio_unlocked';
      var audioUnlockedGlobal = false;
      try { audioUnlockedGlobal = (localStorage.getItem(AUDIO_UNLOCK_KEY) === '1'); } catch(e) {}

      function setAudioUnlocked(){
        audioUnlockedGlobal = true;
        try { localStorage.setItem(AUDIO_UNLOCK_KEY, '1'); } catch(e) {}
      }

      // Any gesture unlocks
      var __lb_onGestureUnlock = function(){
        setAudioUnlocked();
        try { replayPendingSound(); } catch(e) {}
        document.removeEventListener('click', __lb_onGestureUnlock);
        document.removeEventListener('keydown', __lb_onGestureUnlock);
        document.removeEventListener('touchstart', __lb_onGestureUnlock);
      };
      document.addEventListener('click', __lb_onGestureUnlock, { passive: true });
      document.addEventListener('keydown', __lb_onGestureUnlock, { passive: true });
      document.addEventListener('touchstart', __lb_onGestureUnlock, { passive: true });

      // reflect unlock from other tabs
      window.addEventListener('storage', function(e){
        if (e.key === AUDIO_UNLOCK_KEY && e.newValue === '1') audioUnlockedGlobal = true;
      });

      // expose manual unlock (optional)
      window.lb_enable_sound = function(){ setAudioUnlocked(); try { replayPendingSound(); } catch(e) {} };

      var PENDING_SOUND_KEY = 'lb_pending_sound';
      function soundPlayedKey(orderId){ return 'lb_sound_played_' + orderId; }

      function markSoundPlayed(orderId){
        try { localStorage.setItem(soundPlayedKey(orderId), '1'); } catch(e) {}
        try { localStorage.removeItem(PENDING_SOUND_KEY); } catch(e) {}
      }
      function isSoundPlayed(orderId){
        try { return localStorage.getItem(soundPlayedKey(orderId)) === '1'; } catch(e) { return false; }
      }

      function setPendingSound(payload){
        try { localStorage.setItem(PENDING_SOUND_KEY, JSON.stringify(payload)); } catch(e) {}
      }
      function getPendingSound(){
        try { return JSON.parse(localStorage.getItem(PENDING_SOUND_KEY) || 'null'); } catch(e) { return null; }
      }

      function tryPlaySound(orderType, orderId){
        if (orderId && isSoundPlayed(orderId)) return;
        var soundSrc = (orderType === 'request')
          ? (__ASSET_URL + '/core/dash/audio/request.mp3')
          : (__ASSET_URL + '/core/dash/audio/' + orderType + '.mp3');
        // Try to play sound even when this tab is in the background.
        // If the browser blocks playback, we queue it and retry later.
        if (!audioUnlockedGlobal) {
          setPendingSound({ orderType: orderType, orderId: orderId || null, ts: now() });
          return;
        }

        try {
          var audio = new Audio(soundSrc);
          audio.preload = 'auto';
          var p = audio.play();
          if (p && p.then) {
            p.then(function(){ if (orderId) markSoundPlayed(orderId); })
             .catch(function(){ setPendingSound({ orderType: orderType, orderId: orderId || null, ts: now() }); });
          } else {
            if (orderId) markSoundPlayed(orderId);
          }
        } catch(e){
          setPendingSound({ orderType: orderType, orderId: orderId || null, ts: now() });
        }
      }
      window.lbTryPlaySound = tryPlaySound;

      function replayPendingSound(){
        try {
          if (!audioUnlockedGlobal) return;
          var pending = getPendingSound();
          if (!pending || !pending.orderType) return;
          if (pending.ts && (now() - pending.ts) > 120000) { localStorage.removeItem(PENDING_SOUND_KEY); return; }
          tryPlaySound(pending.orderType, pending.orderId);
        } catch(e){}
      }
      window.addEventListener('focus', replayPendingSound);
      document.addEventListener('visibilitychange', replayPendingSound);


      // =========================
      // BROWSER NOTIFICATIONS
      // =========================
      var BROWSER_NOTIFY_PREFIX = 'lb_browser_notified_';
      var BROWSER_NOTIFY_ASKED_KEY = 'lb_browser_notify_permission_asked';

      function browserNotificationsAvailable(){
        return ('Notification' in window) && (window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost');
      }

      function markBrowserNotified(key){
        try { localStorage.setItem(BROWSER_NOTIFY_PREFIX + key, String(now())); } catch(e) {}
      }

      function wasBrowserNotified(key){
        try { return !!localStorage.getItem(BROWSER_NOTIFY_PREFIX + key); } catch(e) { return false; }
      }

      function requestBrowserNotificationPermission(){
        if (!browserNotificationsAvailable()) return;
        if (Notification.permission !== 'default') return;
        try { localStorage.setItem(BROWSER_NOTIFY_ASKED_KEY, '1'); } catch(e) {}
        try { Notification.requestPermission(); } catch(e) {}
      }

      // Browser permission must be requested after a user gesture in most browsers.
      var __lb_onGestureBrowserNotify = function(){
        requestBrowserNotificationPermission();
        document.removeEventListener('click', __lb_onGestureBrowserNotify);
        document.removeEventListener('keydown', __lb_onGestureBrowserNotify);
        document.removeEventListener('touchstart', __lb_onGestureBrowserNotify);
      };
      document.addEventListener('click', __lb_onGestureBrowserNotify, { passive: true });
      document.addEventListener('keydown', __lb_onGestureBrowserNotify, { passive: true });
      document.addEventListener('touchstart', __lb_onGestureBrowserNotify, { passive: true });

      // Optional manual trigger, for example from a settings button.
      window.lb_request_browser_notifications = requestBrowserNotificationPermission;

      function lbOpenClaimOrderLikeToast(oid, orderData){
        oid = parseInt(oid, 10) || 0;
        if (!oid) return;

        orderData = orderData || {};

        if (orderData.is_egirl) {
          var eggUrl = __BSTR_URL + '/egirl-panel';
          try { window.location.href = eggUrl; } catch(e) { window.location.assign(eggUrl); }
          return;
        }

        try {
          localStorage.setItem('lb_pending_claim_order', JSON.stringify({
            order_id: oid,
            name: orderData.name || '',
            details: orderData.details || '',
            client_username: orderData.client_username || '',
            client_icon: orderData.client_icon || '',
            ts: now()
          }));
        } catch(e) {}

        var targetUrl = __BSTR_URL + '/orders-panel?claim_order=' + encodeURIComponent(String(oid));
        var isOrdersPanel = /\/orders-panel\/?$/.test(window.location.pathname || '');

        if (!isOrdersPanel) {
          try { window.location.href = targetUrl; } catch(e) { window.location.assign(targetUrl); }
          return;
        }

        if (typeof window.lb_open_booster_claim_modal === 'function') {
          try {
            if (window.lb_open_booster_claim_modal(oid, orderData)) return;
          } catch(e) {}
        }

        try {
          if (window.location.href !== targetUrl) window.location.href = targetUrl;
        } catch(e) {
          window.location.href = targetUrl;
        }
      }

      function showBrowserOrderNotification(data, dedupeKey, isRealRepost){
        if (!data || !dedupeKey || !browserNotificationsAvailable()) return;
        if (Notification.permission !== 'granted') return;
        if (wasBrowserNotified(dedupeKey)) return;
        markBrowserNotified(dedupeKey);

        var oid = parseInt(data.order_id, 10) || 0;
        var title = isRealRepost ? 'Order reposted' : 'New Available Order!';
        var serviceName = data.name ? String(data.name) : 'New order';
        var orderDetails = data.details ? String(data.details) : '';
        var body = serviceName + (orderDetails ? ('\n' + orderDetails) : (oid ? ('\nOrder #' + oid) : ''));
        var targetUrl = data.is_egirl
          ? (__BSTR_URL + '/egirl-panel')
          : (oid ? (__BSTR_URL + '/orders-panel?claim_order=' + oid) : (__BSTR_URL + '/orders-panel'));

        try {
          var n = new Notification(title, {
            body: body,
            icon: __ASSET_URL + '/core/main/img/logos/PNG/icon-bg-64x64.png?v6',
            badge: __ASSET_URL + '/core/main/img/logos/PNG/icon-bg-64x64.png?v6',
            tag: 'lb_order_' + dedupeKey,
            renotify: true,
            requireInteraction: true,
            data: { url: targetUrl }
          });

          n.onclick = function(){
            try { window.focus(); } catch(e) {}
            try { lbOpenClaimOrderLikeToast(oid, data); } catch(e) { try { window.location.href = targetUrl; } catch(x) {} }
            try { n.close(); } catch(e) {}
          };
        } catch(e) {}
      }

      // Leader election so only ONE tab polls the server
      var LEADER_KEY = 'orderNotifierLeader';
      function getLeader() {
        try { return JSON.parse(localStorage.getItem(LEADER_KEY) || 'null'); } catch (e) { return null; }
      }
      function setLeader(obj) {
        try { localStorage.setItem(LEADER_KEY, JSON.stringify(obj)); } catch (e) {}
      }
      function tryBecomeLeader() {
        var leader = getLeader();
        var t = now();
        if (!leader || !leader.tabId || leader.expires < t || leader.tabId === tabId) {
          setLeader({ tabId: tabId, expires: t + 9000 });
          return true;
        }
        return leader.tabId === tabId;
      }

      tryBecomeLeader();
      setInterval(tryBecomeLeader, 2500);

      window.addEventListener('beforeunload', function () {
        var leader = getLeader();
        if (leader && leader.tabId === tabId) {
          try { localStorage.removeItem(LEADER_KEY); } catch (e) {}
        }
      });

      function postForm(url, dataObj) {
        var body = new URLSearchParams(dataObj);
        return fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: body,
          credentials: 'include',
          cache: 'no-store'
        }).then(function(r){ return r.text(); });
      }

      var __lbNotifyValidationInFlight = {};

      function validateOrderNotification(data, cb) {
        var oid = data && data.order_id ? parseInt(data.order_id, 10) : 0;
        if (!oid) { cb(null); return; }
        if (data.is_egirl) { cb(data); return; }

        var game = String(data.game || '').trim().toLowerCase();
        if (game && typeof window.lbOrderGameAllowed === 'function' && !window.lbOrderGameAllowed(data)) {
          cb(null);
          return;
        }

        if (__lbNotifyValidationInFlight[oid]) return;
        __lbNotifyValidationInFlight[oid] = true;

        postForm(__AJAX_URL, { action: 'booster_order_notify_payload', order_id: oid })
          .then(function(raw){
            var res = null;
            try { res = JSON.parse(String(raw || '').trim()); } catch(e) {}
            if (!res || !res.success || !res.eligible || !res.data) { cb(null); return; }
            var payload = Object.assign({}, data || {}, res.data || {}, { __notifyValidated: true });
            cb(payload);
          })
          .catch(function(){ cb(null); })
          .finally(function(){ delete __lbNotifyValidationInFlight[oid]; });
      }

      function showOrderToast(data) {
        if (!data || !data.order_id) return;
        // Only filter immediately when the payload already contains a game.
        // Payloads without game are validated server-side via booster_order_notify_payload.
        if (data.game && typeof window.lbOrderGameAllowed === 'function' && !window.lbOrderGameAllowed(data)) return;

        var orderId = parseInt(data.order_id, 10);
        if (!orderId) return;

        if (!data.is_egirl && data.__notifyValidated !== true) {
          validateOrderNotification(data, function(validData){
            if (validData) showOrderToast(validData);
          });
          return;
        }

        var backendSaysRepost = data.kind === 'repost';
        var isRealRepost = backendSaysRepost;
        var dedupeKey = isRealRepost && data.notif_id
          ? ('repost_' + data.notif_id)
          : ('order_' + orderId);

        if (alreadyShown(orderId, dedupeKey)) return;

        if (typeof window.create_order_toast === 'function') {
          window.create_order_toast({
            title: isRealRepost ? 'Order reposted' : 'New Order',
            messageHtml: isRealRepost
              ? `<b>${data.name || ''}</b> order <span style="opacity:.8">#${orderId}</span> was reposted to the panel`
              : `<b>${data.name || ''}</b> order <span style="opacity:.8">#${orderId}</span>`,
            hrefText: 'Claim Order',
            onBtnClick: (function(oid, orderData) {
              return function () {
                lbOpenClaimOrderLikeToast(oid, orderData);
              };
            })(orderId, data),
            iconClass: isRealRepost ? 'fa-solid fa-rotate-right' : 'fa-solid fa-bolt',
            accent: isRealRepost ? '#f59e0b' : '#8b5cf6',
            timeoutMs: 25000
          });
        }

        var soundType = (data && data.type) ? data.type : 'request';
        try { tryPlaySound(soundType, dedupeKey); } catch(e) {}
        try { showBrowserOrderNotification(data, dedupeKey, isRealRepost); } catch(e) {}

        if (!isRealRepost) {
          markOrderPinged(orderId);
        }

        // NOTE: panel refresh is intentionally NOT triggered here.
        // orders/panel.php attaches its own dedicated 'new_order' socket listener
        // that calls refreshOrdersPanel() reliably (independent of the toast dedupe
        // logic above). Calling refreshOrdersPanelSafe() here as well caused the
        // panel to run two overlapping refreshes for the same event, which could
        // insert the same order card twice.
      }

      window.lbShowOrderToast = showOrderToast;

      // Incoming broadcasts
      function handleIncoming(ev) {
        if (!ev) return;
        if (ev.sourceTabId && ev.sourceTabId === tabId) return;
        if (ev.type === 'new_order' && ev.data) showOrderToast(ev.data);
      }

      if (bc) bc.onmessage = function (msg) { handleIncoming(msg.data); };

      window.addEventListener('storage', function (e) {
        if (e.key === 'orderNotifyEvent' && e.newValue) {
          try { handleIncoming(JSON.parse(e.newValue)); } catch (err) {}
        }
      });

      // Cursor (shared)
      var lastOrderId = parseInt(localStorage.getItem("lastOrderId") || "0", 10) || 0;
            var lastNotifId = parseInt(localStorage.getItem("lastOrderNotifId") || "0", 10) || 0;
// Option A: Kein Full-Reload mehr. Falls noch ein alter pending_toast vorhanden ist, aufräumen.
      try { localStorage.removeItem('lb_pending_toast'); } catch(e) {}

      window.lbCheckNewOrdersOnce = function () {
        if (!tryBecomeLeader()) return;

        postForm(__AJAX_URL, { action: 'check_new_orders', last_order_id: lastOrderId, last_notif_id: lastNotifId, since_ts: window.__lb_since_ts() })
          .then(function(raw){
            if (!raw) return;
            var t = String(raw).trim();

            // if backend returns HTML/login/warnings -> not JSON
            if (!t || (t[0] !== '{' && t[0] !== '[')) return;

            var res;
            try { res = JSON.parse(t); } catch (e) { return; }
            if (!res || !res.success || !res.data) { return; }
            var data = res.data;

            // Repost-Event? (alte Order erneut gepusht)
            if (data.kind === 'repost' || data.kind === 'repost_skip') {
              var nid = parseInt(data.notif_id, 10);
              if (!nid) return;

              // Cursor immer weiterziehen, auch wenn skip
              if (nid > lastNotifId) {
                lastNotifId = nid;
                localStorage.setItem("lastOrderNotifId", String(lastNotifId));
              } else {
                return;
              }

              // Nur bei echten repost events toasten
              if (data.kind === 'repost') {
                showOrderToast(data);
                emitEvent({ type: 'new_order', sourceTabId: tabId, ts: now(), data: data });
              }
              return;
            }

            // Normal neue Order (Checkout)
            var newId = parseInt(data.order_id, 10);
            if (!newId) return;

            // Optional server timestamp guard (requires backend to return created_ts)
            var createdTs = data.created_ts ? parseInt(data.created_ts, 10) : 0;
            var sinceTs = window.__lb_since_ts();
            if (createdTs) { if (createdTs < sinceTs) return; }

            // If DB/order ids were reset, avoid getting stuck
            if (newId < lastOrderId) {
              lastOrderId = newId;
              localStorage.setItem("lastOrderId", String(lastOrderId));
              return;
            }
            if (newId <= lastOrderId) return;

            lastOrderId = newId;
            localStorage.setItem("lastOrderId", String(lastOrderId));


            // Show in leader tab
            showOrderToast(data);

            // Broadcast so other tabs also show toast
            emitEvent({ type: 'new_order', sourceTabId: tabId, ts: now(), data: data });
          })
          .catch(function(e){ console.log('Global notifier error:', e); });
      };

      // SAFE HYBRID MODE:
      // Fallback only when WebSocket is disconnected.
      // This prevents AJAX Dauerfeuer while keeping a backup for lost events.
      setInterval(function () {
        if (window.lbRealtimeConnected === true) return;
        window.lbCheckNewOrdersOnce();
      }, 60000);

    })();
    </script>

    <!-- =========================================================
         ORDERS PANEL AUTO-REFRESH (global)
         - Runs ONLY on the Orders Panel page (table id="orders_table")
         - Detects changes (new orders OR old orders moved back into the panel) via action=orders_panel_state
         ========================================================= -->
    <script>
    (function () {
      function isOrdersPanelPage(){
        return !!document.querySelector('#orders_table');
      }

      async function postForm(dataObj){
        const body = new URLSearchParams(dataObj);
        const r = await fetch(ajax_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body,
          credentials: 'include',
          cache: 'no-store'
        });
        return (await r.text()).trim();
      }

      let lastHash = '';
      try { lastHash = localStorage.getItem('lb_orders_panel_hash') || ''; } catch(e) {}

      async function checkPanelState(){
        if (!isOrdersPanelPage()) return;

        const raw = await postForm({ action: 'orders_panel_state' });
        if (!raw || raw[0] !== '{') return;

        let res;
        try { res = JSON.parse(raw); } catch(e) { return; }
        if (!res.success || !res.data || !res.data.hash) return;

        const newHash = String(res.data.hash);

        // Warm-up: set current hash once (no reload)
        if (!lastHash) {
          lastHash = newHash;
          try { localStorage.setItem('lb_orders_panel_hash', lastHash); } catch(e) {}
          return;
        }

        if (newHash !== lastHash) {
          lastHash = newHash;
          try { localStorage.setItem('lb_orders_panel_hash', lastHash); } catch(e) {}

          // Option A: Kein Full-Reload. Wenn verfügbar, nur den Orders-Panel-Inhalt aktualisieren.
          try { if (typeof window.lb_refresh_orders_panel === 'function') window.lb_refresh_orders_panel(); } catch(e) {}
        }
      }

      window.lbCheckOrdersPanelState = checkPanelState;
      setInterval(function(){ if (!window.lbRealtimeConnected) checkPanelState(); }, 180000);

      document.addEventListener('visibilitychange', function(){
        if (document.visibilityState === 'visible' && !window.lbRealtimeConnected) checkPanelState();
      });
    })();
    </script>

<script>
  (function () {
    // Heartbeat only. The booster picks the actual status in the header chip; this
    // keeps last_seen_at fresh so a forgotten "Online" decays on its own.
    // Deliberately NOT gated on document.visibilityState: boosters play in fullscreen,
    // the tab is hidden almost all the time, and that used to drop them offline.
    var PING_URL = '/dashboard_presence_ping.php';
    var INTERVAL_MS = 600000; // 10 min — the server keeps a 30 min grace window,
                              // so even two dropped pings stay well inside it
    var MIN_GAP_MS = 480000;
    var PRESENCE_ROLE = 'booster';
    var lastSentAt = 0;

    function sendPing() {
      var now = Date.now();
      if (now - lastSentAt < MIN_GAP_MS) return;
      lastSentAt = now;

      var fd = new FormData();
      fd.append('v', (document.visibilityState === 'visible') ? '1' : '0');
      fd.append('focus', (document.hasFocus && document.hasFocus()) ? '1' : '0');
      fd.append('user_type', PRESENCE_ROLE);
      fd.append('scope', PRESENCE_ROLE);

      // sendBeacon survives background-tab throttling and page unload.
      if (navigator.sendBeacon) {
        try {
          if (navigator.sendBeacon(PING_URL, fd)) return;
        } catch (e) {}
      }

      fetch(PING_URL, { method: 'POST', body: fd, credentials: 'include', keepalive: true })
        .catch(function(){});
    }

    sendPing();
    setInterval(sendPing, INTERVAL_MS);
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible') sendPing();
    });
  })();
  </script>


<script>
(function(){
  if (window.__lbRequestedOrderModalInit) return;
  window.__lbRequestedOrderModalInit = true;
  var isBoosterArea = window.location.pathname.indexOf('/booster-area') !== -1;
  if (!isBoosterArea) return;

  var modalEl = document.getElementById('boosterRequestedOrderModal');
  if (!modalEl || typeof bootstrap === 'undefined') return;
  var modal = bootstrap.Modal.getOrCreateInstance(modalEl, {backdrop:'static', keyboard:false});
  var currentOrderId = 0;
  var pollBusy = false;
  var forcePendingAfterBusy = false;
  var TOAST_KEY = 'lb_booster_request_toast_order_id';
  var MODAL_KEY = 'lb_booster_request_modal_order_id';
  var BASELINE_KEY = 'lb_booster_request_baseline_order_id';
  var INIT_KEY = 'lb_booster_request_initialized';
  var DISMISSED_PREFIX = 'lb_booster_request_dismissed_at_';

  function getRequestSinceTs(){
    if (typeof window.__lb_since_ts === 'function') return window.__lb_since_ts();
    var v = 0;
    try { v = parseInt(sessionStorage.getItem('lb_session_start_ms') || '0', 10); } catch(e) {}
    if (!v) {
      v = Date.now();
      try { sessionStorage.setItem('lb_session_start_ms', String(v)); } catch(e) {}
    }
    return Math.floor(v / 1000);
  }

  function getStoredInt(key){
    try { return parseInt(sessionStorage.getItem(key) || '0', 10) || 0; } catch(e) { return 0; }
  }
  function setStoredInt(key, value){
    try { sessionStorage.setItem(key, String(parseInt(value || 0, 10) || 0)); } catch(e) {}
  }
  function clearStoredInt(key){
    try { sessionStorage.removeItem(key); } catch(e) {}
  }
  function getDismissedAt(orderId){
    try { return parseInt(localStorage.getItem(DISMISSED_PREFIX + orderId) || '0', 10) || 0; } catch(e) { return 0; }
  }
  function markDismissed(orderId){
    orderId = parseInt(orderId || 0, 10) || 0;
    if (!orderId) return;
    try { localStorage.setItem(DISMISSED_PREFIX + orderId, String(Math.floor(Date.now() / 1000))); } catch(e) {}
  }
  function clearDismissed(orderId){
    try { localStorage.removeItem(DISMISSED_PREFIX + orderId); } catch(e) {}
  }

  function esc(s){
    return String(s == null ? '' : s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }
  function row(label, value){
    if (!value) return '';
    return '<div class="lb-request-row"><div class="lb-request-k">'+esc(label)+'</div><div class="lb-request-v" title="'+esc(value)+'">'+esc(value)+'</div></div>';
  }
  function render(data){
    currentOrderId = parseInt(data.order_id || 0, 10) || 0;
    document.getElementById('boosterRequestedOrderTitle').textContent = 'You are requested by ' + (data.client_username || 'Client') + '.';
    var rows = Array.isArray(data.summary_rows) ? data.summary_rows.map(function(item){
      return row(item.label || '', item.value || '');
    }).join('') : '';
    document.getElementById('boosterRequestedOrderRows').innerHTML = rows;
  }
  function post(data){
    return fetch(ajax_url, {
      method:'POST', credentials:'include', cache:'no-store',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
      body:new URLSearchParams(data)
    }).then(function(r){ return r.text(); });
  }
  function handleAction(action){
    if (!currentOrderId) return;
    markDismissed(currentOrderId);
    post({action: action, order_id: currentOrderId}).then(function(raw){
      var res = {};
      try { res = JSON.parse(String(raw||'{}')); } catch(e) {}
      if (res.sendToast && typeof window.create_order_toast === 'function') {
        window.create_order_toast({
          title: res.sendToast.title || 'Update',
          messageHtml: esc(res.sendToast.message || ''),
          iconClass: (action === 'booster_accept_requested_order') ? 'fa-solid fa-check' : 'fa-solid fa-xmark',
          timeoutMs: 6000
        });
      }
      clearStoredInt(TOAST_KEY);
      clearStoredInt(MODAL_KEY);
      if (res.redirectUrl) { window.location.href = res.redirectUrl; return; }
      if (res.refreshPage) { window.location.reload(); return; }
      hideRequestedModal({ refreshPanel: true });
    });
  }
  document.getElementById('boosterRequestedClaimBtn').addEventListener('click', function(){ handleAction('booster_accept_requested_order'); });
  document.getElementById('boosterRequestedDeclineBtn').addEventListener('click', function(){ handleAction('booster_decline_requested_order'); });
  modalEl.addEventListener('hidden.bs.modal', function(){
    if (currentOrderId) {
      markDismissed(currentOrderId);
      currentOrderId = 0;
      clearStoredInt(MODAL_KEY);
      clearStoredInt(TOAST_KEY);
    }
  });

  function refreshOrdersPanelSafe(){
    try { if (typeof window.lb_refresh_orders_panel === 'function') window.lb_refresh_orders_panel(); } catch(e) {}
  }

  function hideRequestedModal(options){
    options = options || {};
    var prevOrderId = currentOrderId;
    currentOrderId = 0;
    clearStoredInt(MODAL_KEY);
    clearStoredInt(TOAST_KEY);
    try {
      if (modalEl.classList.contains('show')) modal.hide();
    } catch(e) {}
    if (options.refreshPanel || prevOrderId) {
      refreshOrdersPanelSafe();
    }
  }

  function isInitialized(){
    try { return sessionStorage.getItem(INIT_KEY) === '1'; } catch(e) { return false; }
  }
  function setInitialized(){
    try { sessionStorage.setItem(INIT_KEY, '1'); } catch(e) {}
  }
  function getBaselineOrderId(){
    return getStoredInt(BASELINE_KEY);
  }
  function setBaselineOrderId(orderId){
    setStoredInt(BASELINE_KEY, orderId);
  }
  function getRequestTimestamp(data){
    if (!data || typeof data !== 'object') return 0;
    var keys = ['requested_ts', 'created_ts', 'request_created_ts', 'assigned_ts', 'notif_ts'];
    for (var i = 0; i < keys.length; i++) {
      var value = parseInt(data[keys[i]] || 0, 10) || 0;
      if (value > 0) return value;
    }
    return 0;
  }

  function checkPending(forceLive){
    forceLive = forceLive === true;
    if (pollBusy) {
      if (forceLive) forcePendingAfterBusy = true;
      return;
    }
    pollBusy = true;
    post({action:'booster_pending_request_modal', since_ts: getRequestSinceTs()}).then(function(raw){
      pollBusy = false;
      if (forcePendingAfterBusy && !forceLive) {
        forcePendingAfterBusy = false;
        setTimeout(function(){ checkPending(true); }, 0);
      }
      var res = null;
      try { res = JSON.parse(String(raw||'null')); } catch(e) { res = null; }
      if (!res || !res.success) {
        hideRequestedModal({ refreshPanel: true });
        return;
      }
      if (!res.data || !res.data.order_id) {
        setInitialized();
        hideRequestedModal({ refreshPanel: true });
        return;
      }
      var oid = parseInt(res.data.order_id || 0, 10) || 0;
      if (!oid) return;
      var requestTs = getRequestTimestamp(res.data);
      var dismissedAt = getDismissedAt(oid);
      if (dismissedAt && (!requestTs || requestTs <= dismissedAt)) {
        setBaselineOrderId(oid);
        hideRequestedModal();
        return;
      }
      if (dismissedAt && requestTs > dismissedAt) {
        clearDismissed(oid);
      }
      if (!currentOrderId && (getStoredInt(MODAL_KEY) === oid || getStoredInt(TOAST_KEY) === oid)) {
        markDismissed(oid);
        setBaselineOrderId(oid);
        hideRequestedModal();
        return;
      }
      var openOrderMatch = window.location.pathname.match(/\/booster-area\/order\/(\d+)/);
      if (openOrderMatch && parseInt(openOrderMatch[1] || '0', 10) === oid) {
        markDismissed(oid);
        setBaselineOrderId(oid);
        hideRequestedModal();
        return;
      }

      var baselineOid = getBaselineOrderId();
      var sinceTs = getRequestSinceTs();

      if (!isInitialized() && !forceLive) {
        setInitialized();
        setBaselineOrderId(oid);
        hideRequestedModal({ refreshPanel: true });
        return;
      }
      if (forceLive && !isInitialized()) setInitialized();

      if (!baselineOid) {
        setBaselineOrderId(oid);
        baselineOid = oid;
      }

      if (!forceLive && requestTs && requestTs < sinceTs) {
        setBaselineOrderId(oid);
        hideRequestedModal({ refreshPanel: true });
        return;
      }

      if (!requestTs && baselineOid === oid && !currentOrderId) {
        render(res.data);
        // Only stay silent when this exact request was already announced before
        // (page reload). A brand new request must still toast + play the sound.
        if (getStoredInt(MODAL_KEY) !== oid) {
          setStoredInt(MODAL_KEY, oid);
          if (getStoredInt(TOAST_KEY) !== oid) {
            setStoredInt(TOAST_KEY, oid);
            if (typeof window.create_order_toast === 'function') {
              window.create_order_toast({
                title: 'Boost request',
                messageHtml: esc((res.data.client_username || 'Client') + ' requested you for order #' + oid),
                iconClass: 'fa-solid fa-bolt',
                timeoutMs: 8000
              });
            }
            try {
              if (typeof window.lbTryPlaySound === 'function') {
                window.lbTryPlaySound('request', 'requested_' + oid);
              }
            } catch(e) {}
          }
        }
        if (!modalEl.classList.contains('show')) modal.show();
        return;
      }

      var previousOrderId = currentOrderId;
      setBaselineOrderId(oid);
      render(res.data);
      if (previousOrderId && previousOrderId !== oid) {
        refreshOrdersPanelSafe();
      }
      if (getStoredInt(TOAST_KEY) !== oid) {
        setStoredInt(TOAST_KEY, oid);
        if (typeof window.create_order_toast === 'function') {
          window.create_order_toast({
            title: 'Boost request',
            messageHtml: esc((res.data.client_username || 'Client') + ' requested you for order #' + oid),
            iconClass: 'fa-solid fa-bolt',
            timeoutMs: 8000
          });
        }
        try {
          if (typeof window.lbTryPlaySound === 'function') {
            window.lbTryPlaySound('request', 'requested_' + oid);
          }
        } catch(e) {}
      }
      if (getStoredInt(MODAL_KEY) !== oid) {
        setStoredInt(MODAL_KEY, oid);
      }
      if (!modalEl.classList.contains('show')) modal.show();
    }).catch(function(){ pollBusy = false; });
  }
  window.lbCheckPendingRequestOnce = function(forceLive){ checkPending(forceLive === true); };

  // Check once on load. Realtime events trigger additional checks; AJAX is only
  // a slow fallback while the socket is unavailable.
  setTimeout(function(){ checkPending(); }, 1200);
  setInterval(function(){
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected === true) return;
    checkPending();
  }, 60000);
})();
</script>



<script src="https://socket.lolboost.gg/socket.io/socket.io.js"></script>
<script>
(function(){
  if (window.__lbRealtimeInit) return;
  window.__lbRealtimeInit = true;
  window.lbRealtimeConnected = false;

  function payloadOf(data){
    return data && data.data && typeof data.data === 'object'
      ? Object.assign({}, data, data.data)
      : (data || {});
  }

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
      // Role-wide order events (account/status/price) are published to this room.
      try { socket.emit('join', 'boosters'); } catch(e) {}
      try { socket.emit('booster:join', { area: 'booster' }); } catch(e) {}
      // Realtime connected. Do not fire AJAX checks here.
      // AJAX fallbacks are only used when the socket is disconnected.
    });

    socket.on('disconnect', function(){
      window.lbRealtimeConnected = false;
    });

    // orders/panel.php registers its own listeners for these events and refreshes
    // through a debounce + random jitter, so one broadcast does not hit every open
    // panel in the same instant. Refreshing from here as well bypassed all of that:
    // each event triggered one immediate full-page fetch PLUS the scheduled one.
    // Only refresh from the layout when the panel is not on the page (or has not
    // attached yet), otherwise leave it to the panel's own throttled handler.
    function panelHandlesItsOwnRefresh(){
      return !!(window.lbSocket && window.lbSocket.__lbPanelRefreshHandlersAttached);
    }

    socket.on('new_order', function(data){
      try {
        if (data && typeof window.lbOrderGameAllowed === 'function' && !window.lbOrderGameAllowed(data)) return;
        if (data && typeof window.lbShowOrderToast === 'function') window.lbShowOrderToast(data);
        else if (typeof window.lbCheckNewOrdersOnce === 'function') window.lbCheckNewOrdersOnce();
      } catch(e) {}
      if (panelHandlesItsOwnRefresh()) return;
      try { if (typeof window.lb_refresh_orders_panel === 'function') window.lb_refresh_orders_panel(); } catch(e) {}
    });

    socket.on('orders_panel_update', function(data){
      if (panelHandlesItsOwnRefresh()) return;
      try { if (typeof window.lb_refresh_orders_panel === 'function') window.lb_refresh_orders_panel(); } catch(e) {}
      // orders_panel_state only exists to detect changes the socket did not report.
      // With a live socket the event itself IS the change, so asking the server
      // again is a pure duplicate request.
      if (window.lbRealtimeConnected === true) return;
      try { if (typeof window.lbCheckOrdersPanelState === 'function') window.lbCheckOrdersPanelState(); } catch(e) {}
    });

    socket.on('booster_request', function(){
      try { if (typeof window.lbCheckPendingRequestOnce === 'function') window.lbCheckPendingRequestOnce(true); } catch(e) {}
    });

    socket.on('notification_update', function(){
      try { if (typeof window.lbRefreshNotificationBadge === 'function') window.lbRefreshNotificationBadge(); } catch(e) {}
    });

    // Order view realtime hooks. Individual pages may define these handlers.
    socket.on('chat_update', function(data){
      try { if (typeof window.lbOrderViewChatUpdate === 'function') window.lbOrderViewChatUpdate(payloadOf(data)); } catch(e) {}
    });

    socket.on('order_status_update', function(data){
      try { if (typeof window.lbOrderViewStatusUpdate === 'function') window.lbOrderViewStatusUpdate(payloadOf(data)); } catch(e) {}
      // Every status change on any order was broadcast to every booster, and each
      // one answered with an orders_panel_state request. With a live socket the
      // panel already learns about relevant changes through orders_panel_update,
      // so this only added load without adding information.
      if (window.lbRealtimeConnected === true) return;
      try { if (typeof window.lbCheckOrdersPanelState === 'function') window.lbCheckOrdersPanelState(); } catch(e) {}
    });

    socket.on('order_account_update', function(data){
      try { if (typeof window.lbOrderViewAccountUpdate === 'function') window.lbOrderViewAccountUpdate(payloadOf(data)); } catch(e) {}
    });

    socket.on('price_update', function(data){
      var payload = payloadOf(data);
      try { if (typeof window.lbOrderViewPriceUpdate === 'function') window.lbOrderViewPriceUpdate(payload); } catch(e) {}
      try { if (typeof window.lbOrdersPanelPriceUpdate === 'function') window.lbOrdersPanelPriceUpdate(payload); } catch(e) {}
    });
  }

  // The layout can be evaluated before or after `window.load`. Start in both
  // situations so booster order pages can never miss their socket connection.
  if (document.readyState === 'complete') {
    setTimeout(startRealtime, 0);
  } else {
    window.addEventListener('load', function(){ setTimeout(startRealtime, 0); }, { once: true });
  }
})();
</script>

<?php
$lbReviewBooster = (!defined('IS_EGIRL') || !IS_EGIRL) && defined('BOOSTER_ID')
    ? db_get_row('boosters', ['id' => (int)BOOSTER_ID], 1)
    : false;
$lbReviewLastRaw = trim((string)($lbReviewBooster['last_order_check'] ?? ''));
if ($lbReviewLastRaw === '' || $lbReviewLastRaw === '0000-00-00 00:00:00') {
    $lbReviewLastRaw = trim((string)($lbReviewBooster['created_at'] ?? ''));
}
$lbReviewLastTs = ($lbReviewLastRaw !== '' && $lbReviewLastRaw !== '0000-00-00 00:00:00')
    ? strtotime($lbReviewLastRaw)
    : false;
$lbReviewRequired = $lbReviewBooster
    && $lbReviewLastTs !== false
    && $lbReviewLastTs < strtotime('-14 days');
?>
<?php if ($lbReviewRequired): ?>
<style>
.lb-review-lock .modal-content{border:1px solid rgba(239,68,68,.55);border-radius:20px;background:#202329;color:#fff;box-shadow:0 24px 80px rgba(0,0,0,.48),0 0 0 1px rgba(239,68,68,.08)}
.lb-review-lock .modal-body{padding:34px}.lb-review-icon{width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;background:rgba(239,68,68,.16);color:#f87171;font-size:28px;margin:0 auto 18px}.lb-review-lock h3{font-weight:750;text-align:center}.lb-review-lock p{color:rgba(255,255,255,.7);text-align:center;line-height:1.7}.lb-review-lock .btn{min-height:48px;border-radius:12px;font-weight:700}.lb-review-lock .btn-primary{background:#dc2626;border-color:#dc2626;box-shadow:0 8px 24px rgba(220,38,38,.24)}.lb-review-lock .btn-primary:hover,.lb-review-lock .btn-primary:focus{background:#b91c1c;border-color:#b91c1c}.lb-review-note{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);border-radius:12px;padding:13px 15px;font-size:13px;color:rgba(255,255,255,.78);margin:18px 0}
</style>
<div class="modal fade lb-review-lock" id="lbBoosterReviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body">
    <div class="lb-review-icon"><i class="fa-duotone fa-shield-exclamation"></i></div>
    <h3>Account verification required</h3>
    <p>Your booster account has not been active in the Orders Panel for more than 14 days. New order claiming is disabled until an administrator verifies your account again.</p>
    <div class="lb-review-note"><i class="fa-duotone fa-circle-info me-2"></i>You can still access and complete existing orders. Please open a Discord ticket for account verification.</div>
    <a class="btn btn-primary w-100" href="https://discord.com/channels/926928301807771708/1207383976239702087" target="_blank" rel="noopener"><i class="fa-brands fa-discord me-2"></i>Open Discord Ticket</a>
  </div></div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var el=document.getElementById('lbBoosterReviewModal');if(el&&window.bootstrap){bootstrap.Modal.getOrCreateInstance(el,{backdrop:'static',keyboard:false}).show();}});</script>
<?php endif; ?>

</body>
</html>
