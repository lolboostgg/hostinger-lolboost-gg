<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GGirl Onboarding | LoLBoost.gg</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="dark" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="default" as="style">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/core/dash/css/main.css?v<?= rand(0,9999) ?>">
    <style data-hs-appearance-onload-styles>* { transition: unset !important; } body { opacity: 0; }</style>
    <style>
        :root {
            --ob-bg: #0b0d11;
            --ob-card: rgba(14,16,20,0.80);
            --ob-border: rgba(255,255,255,0.08);
            --ob-muted: rgba(255,255,255,0.50);
            --ob-text: rgba(255,255,255,0.90);
            --pink: #e91e8c;
            --pink-dim: rgba(233,30,140,0.18);
        }
        body.ob-page {
            min-height: 100vh;
            background: radial-gradient(900px 500px at 10% 10%, rgba(233,30,140,0.14), transparent 60%),
                        radial-gradient(700px 500px at 90% 20%, rgba(67,56,202,0.18), transparent 55%),
                        #0b0d11;
            color: var(--ob-text);
            font-family: 'Montserrat', sans-serif;
        }
        /* Header */
        .ob-header {
            position: sticky; top: 0; z-index: 20;
            backdrop-filter: blur(12px);
            background: rgba(8,10,14,0.60);
            border-bottom: 1px solid var(--ob-border);
        }
        .ob-header-inner {
            max-width: 1240px; margin: 0 auto;
            padding: 15px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .ob-brand { display: flex; align-items: center; gap: 12px; }
        .ob-brand img { height: 32px; }
        .ob-brand-title { font-weight: 700; font-size: 14px; }
        .ob-brand-sub { font-size: 12px; color: var(--ob-muted); margin-top: 2px; }
        .ob-pill {
            border: 1px solid var(--ob-border); background: rgba(255,255,255,0.03);
            color: rgba(255,255,255,0.85); border-radius: 999px;
            padding: 7px 12px; font-size: 12px;
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none;
        }
        /* Shell */
        .ob-shell { max-width: 1240px; margin: 0 auto; padding: 24px 20px 48px; }
        .ob-grid { display: grid; grid-template-columns: 320px 1fr; gap: 18px; align-items: start; }
        /* Glass card */
        .glass {
            background: var(--ob-card);
            border: 1px solid var(--ob-border);
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            backdrop-filter: blur(12px);
        }
        /* Aside */
        .ob-aside { position: sticky; top: 76px; padding: 16px; }
        .ob-progress-block {
            display: flex; gap: 12px; align-items: center;
            padding: 12px; border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .ob-ring {
            width: 52px; height: 52px; flex: 0 0 52px; border-radius: 50%;
            background: conic-gradient(var(--pink) var(--p, 33%), rgba(255,255,255,0.10) 0);
            display: grid; place-items: center;
        }
        .ob-ring-dot {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(10,12,16,0.92); border: 1px solid rgba(255,255,255,0.10);
            display: grid; place-items: center; font-weight: 700; font-size: 13px;
        }
        .ob-step-title { margin: 0; font-size: 14px; font-weight: 700; }
        .ob-step-desc { margin: 2px 0 0; color: var(--ob-muted); font-size: 12px; }
        /* Stepper nav */
        .ob-stepper { margin-top: 14px; display: grid; gap: 8px; }
        .ob-step-nav {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 12px; border-radius: 13px;
            border: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.02);
            cursor: pointer; transition: background .15s, border-color .15s;
        }
        .ob-step-nav:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.10); }
        .ob-step-nav.active { border-color: rgba(233,30,140,0.40); background: rgba(233,30,140,0.07); }
        .ob-step-nav.done { border-color: rgba(255,255,255,0.10); }
        .ob-step-ico {
            width: 32px; height: 32px; flex: 0 0 32px; border-radius: 10px;
            display: grid; place-items: center; font-weight: 700; font-size: 12px;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        }
        .ob-step-nav.active .ob-step-ico { background: var(--pink-dim); border-color: rgba(233,30,140,0.35); color: #f472b6; }
        .ob-step-nav.done .ob-step-ico { background: rgba(34,197,94,0.15); border-color: rgba(34,197,94,0.30); color: #4ade80; }
        .ob-step-lbl { font-weight: 700; font-size: 13px; display: block; line-height: 1.2; }
        .ob-step-sub { font-size: 11px; color: var(--ob-muted); margin-top: 2px; display: block; }
        /* Mini cards */
        .ob-mini { margin-top: 12px; padding: 12px; border-radius: 13px; border: 1px solid var(--ob-border); background: rgba(255,255,255,0.02); }
        .ob-checklist { margin: 0; padding-left: 18px; font-size: 12px; color: rgba(255,255,255,0.78); }
        .ob-checklist li { margin: 5px 0; }
        /* Content */
        .ob-content { padding: 0; }
        .ob-card-shell { padding: 20px; }
        /* Steps */
        .ob-step { display: none; }
        .ob-step.ob-active { display: block; }
        /* Step header */
        .ob-sh { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .ob-sh h2 { margin: 0; font-size: 20px; font-weight: 800; }
        .ob-sh p { margin: 5px 0 0; color: var(--ob-muted); }
        .ob-badge {
            background: rgba(233,30,140,0.15); border: 1px solid rgba(233,30,140,0.25);
            color: rgba(255,255,255,0.92); padding: 7px 12px; border-radius: 999px;
            font-size: 12px; white-space: nowrap;
        }
        /* Alert */
        .ob-alert {
            background: rgba(233,30,140,0.12); border: 1px solid rgba(233,30,140,0.28);
            color: rgba(255,255,255,0.92); border-radius: 13px; padding: 12px 14px;
            margin-bottom: 14px; font-size: 13px;
        }
        /* Two-col grid for rules/fines */
        .ob-two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .ob-panel { padding: 14px; border-radius: 14px; border: 1px solid var(--ob-border); background: rgba(255,255,255,0.02); }
        .ob-panel-title { font-weight: 800; font-size: 14px; margin-bottom: 2px; }
        .ob-panel-sub { font-size: 12px; color: var(--ob-muted); }
        .ob-scroll { max-height: 300px; overflow-y: auto; padding-right: 4px; }
        .ob-scroll::-webkit-scrollbar { width: 6px; }
        .ob-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 999px; }
        .ob-scroll ul { color: rgba(255,255,255,0.82); font-size: 13px; padding-left: 18px; margin: 0; }
        .ob-scroll li { margin: 6px 0; }
        .ob-scroll-title { font-weight: 800; font-size: 13px; margin: 10px 0 5px; }
        /* Divider */
        .ob-hr { height: 1px; background: var(--ob-border); margin: 16px 0; }
        /* Checks */
        .ob-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .form-check-input { accent-color: var(--pink); }
        /* CTA row */
        .ob-cta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 18px; }
        .ob-hint { font-size: 12px; color: var(--ob-muted); }
        /* Buttons */
        .btn { border-radius: 13px; font-family: 'Montserrat', sans-serif; font-weight: 600; }
        .btn-primary { background: var(--pink); border-color: var(--pink); }
        .btn-primary:hover { background: #c91878; border-color: #c91878; }
        .btn-primary:disabled, .btn-primary.ob-locked {
            background: rgba(233,30,140,0.35); border-color: transparent;
            cursor: not-allowed; opacity: 0.6;
        }
        .btn-secondary-ob {
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.86); padding: 10px 20px; border-radius: 13px;
            cursor: pointer; font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 14px;
        }
        .btn-secondary-ob:hover { background: rgba(255,255,255,0.09); }
        /* Form */
        .ob-form label { font-weight: 700; font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 6px; display: block; }
        .ob-form .form-control, .ob-form .form-select {
            background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.92); border-radius: 13px; min-height: 46px; padding: 10px 12px;
            font-family: 'Montserrat', sans-serif;
        }
        .ob-form .form-control::placeholder { color: rgba(255,255,255,0.30); }
        .ob-form .form-control:focus, .ob-form .form-select:focus {
            border-color: rgba(233,30,140,0.60); box-shadow: 0 0 0 .2rem rgba(233,30,140,0.15);
            background: rgba(255,255,255,0.03); outline: none;
        }
        .ob-form select option { background: #0f1115; color: rgba(255,255,255,0.92); }
        .ob-g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        /* Feature block */
        .ob-feature {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 14px; border-radius: 14px;
            border: 1px solid var(--ob-border); background: rgba(255,255,255,0.02);
        }
        .ob-feature-ico {
            width: 40px; height: 40px; flex: 0 0 40px; border-radius: 13px;
            display: grid; place-items: center; font-size: 16px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--ob-border);
        }
        .ob-feature-t { font-weight: 700; font-size: 13px; margin-bottom: 3px; }
        .ob-feature-d { font-size: 12px; color: var(--ob-muted); }
        /* Fine badge */
        .fine { display: inline-block; background: rgba(220,38,38,0.85); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 6px; margin-left: 4px; }
        /* Tom select dark */
        .ts-wrapper .ts-control { background: rgba(255,255,255,0.02) !important; border: 1px solid rgba(255,255,255,0.10) !important; color: rgba(255,255,255,0.92) !important; border-radius: 13px !important; min-height: 46px; }
        .ts-dropdown { background: rgba(14,16,20,0.98) !important; border: 1px solid rgba(255,255,255,0.10) !important; border-radius: 13px !important; }
        .ts-dropdown .option { color: rgba(255,255,255,0.88) !important; padding: 10px 12px !important; }
        .ts-dropdown .option.active, .ts-dropdown .option:hover { background: var(--pink-dim) !important; color: #fff !important; }
        /* Daterangepicker dark */
        .daterangepicker { background: rgba(14,16,20,0.98) !important; border: 1px solid rgba(255,255,255,0.10) !important; border-radius: 13px !important; }
        .daterangepicker .calendar-table { background: transparent !important; border: none !important; }
        .daterangepicker .calendar-table th, .daterangepicker .calendar-table td { color: rgba(255,255,255,0.88) !important; }
        .daterangepicker td.active { background: rgba(233,30,140,0.35) !important; }
        .daterangepicker select.monthselect, .daterangepicker select.yearselect { background: rgba(14,16,20,0.98) !important; color: rgba(255,255,255,0.88) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 8px !important; }
        /* Modal dark */
        .modal .modal-content { background: rgba(14,16,20,0.96); border: 1px solid var(--ob-border); }
        .modal .modal-header { border-bottom: 1px solid var(--ob-border); }
        .modal .list-group-item { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); color: rgba(255,255,255,0.88); font-size: 13px; }
        /* Responsive */
        @media (max-width: 991px) { .ob-grid { grid-template-columns: 1fr; } .ob-aside { position: relative; top: 0; } .ob-two { grid-template-columns: 1fr; } .ob-g2 { grid-template-columns: 1fr; } .ob-checks { grid-template-columns: 1fr; } }
        /* Toast container */
        .toast-container { top: 75px !important; }
    </style>
    <script>
        window.hs_config = {"autopath":"@@autopath","previewMode":false,"vars":{"themeFont":"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap","version":"?v=1.0"},"layoutBuilder":{"extend":{"switcherSupport":true},"header":{"layoutMode":"default","containerMode":"container-fluid"},"sidebarLayout":"default"},"themeAppearance":{"layoutSkin":"default","sidebarSkin":"default","styles":{"colors":{"primary":"#e91e8c","transparent":"transparent","white":"#fff","dark":"132144","gray":{"100":"#f9fafc","900":"#1e2022"}},"font":"Montserrat"}},"languageDirection":{"lang":"en"}};
    </script>
</head>
<body class="ob-page">
    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>
    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed"></div>

    <header class="ob-header">
        <div class="ob-header-inner">
            <div class="ob-brand">
                <img src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="LoLBoost.gg">
                <div>
                    <div class="ob-brand-title">GGirl Onboarding</div>
                    <div class="ob-brand-sub">Gaming Girl onboarding process</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <span class="ob-pill"><i class="fa-solid fa-shield-check"></i> Secure & Private</span>
                <a class="ob-pill" href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener"><i class="fa-solid fa-headset"></i> Support</a>
            </div>
        </div>
    </header>

    <main class="ob-shell">
        <div class="ob-grid">
            <!-- ASIDE -->
            <aside class="glass ob-aside">
                <div class="ob-progress-block">
                    <div class="ob-ring" id="obRing" style="--p:33%">
                        <div class="ob-ring-dot" id="obDot">1/3</div>
                    </div>
                    <div>
                        <h5 class="ob-step-title" id="obTitle">Step 1: Guidelines</h5>
                        <p class="ob-step-desc" id="obDesc">Read & accept the rules before you continue.</p>
                    </div>
                </div>

                <div class="ob-stepper">
                    <div class="ob-step-nav active" id="nav1" onclick="OB.tryGoTo(1)">
                        <div class="ob-step-ico" id="navico1">1</div>
                        <div><span class="ob-step-lbl">Guidelines</span><span class="ob-step-sub">Rules & fines</span></div>
                    </div>
                    <div class="ob-step-nav" id="nav2" onclick="OB.tryGoTo(2)">
                        <div class="ob-step-ico" id="navico2">2</div>
                        <div><span class="ob-step-lbl">Profile</span><span class="ob-step-sub">Personal details</span></div>
                    </div>
                    <div class="ob-step-nav" id="nav3" onclick="OB.tryGoTo(3)">
                        <div class="ob-step-ico" id="navico3">3</div>
                        <div><span class="ob-step-lbl">Finish</span><span class="ob-step-sub">Submit & done</span></div>
                    </div>
                </div>

                <div class="ob-mini">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <strong style="font-size:13px;">What you'll need</strong>
                        <span class="ob-pill" style="padding:5px 10px;">2–3 min</span>
                    </div>
                    <div style="height:1px;background:var(--ob-border);margin:10px 0;"></div>
                    <ul class="ob-checklist">
                        <li>Accurate personal information</li>
                        <li>Discord username</li>
                        <li>Takes only 2–3 minutes</li>
                    </ul>
                </div>

                <div class="ob-mini" style="margin-top:10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <strong style="font-size:13px;">Privacy</strong>
                        <span class="ob-pill" style="padding:5px 10px;"><i class="fa-solid fa-shield"></i> Secure</span>
                    </div>
                    <p style="margin:8px 0 0;font-size:12px;color:var(--ob-muted);">Your information is used only for onboarding and reviewed by our staff.</p>
                </div>
            </aside>

            <!-- MAIN CONTENT -->
            <section class="glass ob-content">
                <div class="ob-card-shell">

                    <!-- ═══════ STEP 1: Guidelines ═══════ -->
                    <div id="step1" class="ob-step ob-active">
                        <div class="ob-sh">
                            <div>
                                <h2>Guidelines & Policies</h2>
                                <p>Please read the full Rules & Fines for GamingGirls. You must confirm before continuing.</p>
                            </div>
                            <span class="ob-badge"><i class="fa-solid fa-shield-halved me-1"></i> Step 1 of 3</span>
                        </div>

                        <div class="ob-alert">
                            <strong>NEVER SHARE CLIENT INFO</strong> outside the official LoLBoost.gg platform chat.
                        </div>

                        <div class="ob-two">
                            <!-- Rules -->
                            <div class="ob-panel">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                    <div><div class="ob-panel-title">Rules</div><div class="ob-panel-sub">Complete rule list (must follow)</div></div>
                                    <a href="javascript:void(0)" class="ob-pill" data-bs-toggle="modal" data-bs-target="#rulesModal"><i class="fa-regular fa-file-lines"></i> Open</a>
                                </div>
                                <div class="ob-scroll">
                                    <div class="ob-scroll-title">General Rules</div>
                                    <ul>
                                        <li><strong>YOU MUST</strong> treat all clients with respect and professionalism at all times.</li>
                                        <li><strong>NOT ALLOWED</strong> to share any client info, screenshots, or conversations outside the platform.</li>
                                        <li><strong>NOT ALLOWED</strong> to contact clients on external platforms without staff approval.</li>
                                    </ul>
                                    <div class="ob-scroll-title">Session Management</div>
                                    <ul>
                                        <li><strong>YOU MUST</strong> confirm sessions within <strong>30 minutes</strong> of a booking request.</li>
                                        <li><strong>YOU MUST</strong> be online and ready at the agreed start time. Arrivals 10+ min late = no-show.</li>
                                        <li><strong>NOT ALLOWED</strong> to cancel sessions less than <strong>2 hours</strong> before start without a valid reason.</li>
                                        <li><strong>YOU MUST</strong> complete the full session duration. Early endings without client agreement are penalized.</li>
                                    </ul>
                                    <div class="ob-scroll-title">Behavior & Communication</div>
                                    <ul>
                                        <li><strong>YOU MUST</strong> maintain a friendly, positive and engaging attitude during sessions.</li>
                                        <li><strong>NOT ALLOWED</strong> to use offensive or inappropriate language toward clients or staff.</li>
                                        <li><strong>NOT ALLOWED</strong> to pressure or manipulate clients into tips, extending sessions, or purchases.</li>
                                        <li><strong>YOU MUST</strong> report client misconduct immediately via a Support Ticket.</li>
                                    </ul>
                                    <div class="ob-scroll-title">Platform Integrity</div>
                                    <ul>
                                        <li><strong>NOT ALLOWED</strong> to accept private payments or off-platform bookings. All transactions via LoLBoost.gg.</li>
                                        <li><strong>NOT ALLOWED</strong> to advertise other platforms or personal social media to clients.</li>
                                        <li><strong>YOU MUST</strong> open a Support Ticket if a client approaches you for private contact.</li>
                                    </ul>
                                    <div class="ob-scroll-title">Appearance & Sessions</div>
                                    <ul>
                                        <li><strong>YOU MUST</strong> maintain a presentable appearance during video sessions.</li>
                                        <li><strong>YOU MUST</strong> use stable internet and good audio/video quality.</li>
                                        <li><strong>NOT ALLOWED</strong> to be under the influence of alcohol or substances during sessions.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Fines -->
                            <div class="ob-panel">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                    <div><div class="ob-panel-title">Fines</div><div class="ob-panel-sub">Penalties (must understand)</div></div>
                                    <a href="javascript:void(0)" class="ob-pill" data-bs-toggle="modal" data-bs-target="#finesModal"><i class="fa-solid fa-receipt"></i> Open</a>
                                </div>
                                <div class="ob-scroll">
                                    <div style="font-size:12px;color:var(--ob-muted);margin-bottom:8px;">Fines apply when rules are broken or sessions are mishandled.</div>
                                    <ul>
                                        <li>Accepting off-platform payments or private bookings — <span class="fine">250€</span></li>
                                        <li>Sharing client info, conversations or screenshots outside the platform — <span class="fine">150€</span></li>
                                        <li>Contacting clients on external platforms without staff approval — <span class="fine">100€</span></li>
                                        <li>No-show for a confirmed session without prior notice — <span class="fine">50€</span></li>
                                        <li>Late cancellation (under 2 hours before session) without valid reason — <span class="fine">30€</span></li>
                                        <li>Ending a session early without client agreement — <span class="fine">25€</span></li>
                                        <li>Pressuring or guilt-tripping clients into tips or purchases — <span class="fine">25€</span></li>
                                        <li>Inappropriate or unprofessional behavior toward clients — <span class="fine">25€</span></li>
                                        <li>Advertising personal profiles or other platforms to clients — <span class="fine">20€</span></li>
                                        <li>Poor audio/video quality after repeated warnings — <span class="fine">10€</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="ob-hr"></div>

                        <div class="ob-checks">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chkRules">
                                <label class="form-check-label" for="chkRules" style="color:rgba(255,255,255,0.85);font-size:13px;">I have read the <strong>Rules</strong>.</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chkFines">
                                <label class="form-check-label" for="chkFines" style="color:rgba(255,255,255,0.85);font-size:13px;">I understand the <strong>Fines</strong>.</label>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="chkAll">
                            <label class="form-check-label" for="chkAll" style="color:rgba(255,255,255,0.85);font-size:13px;">I agree to follow all rules and accept the fines for violations.</label>
                        </div>

                        <div class="ob-cta">
                            <span class="ob-hint" id="step1Hint">Please check all boxes to continue.</span>
                            <button type="button" id="btnStep1Next" class="btn btn-primary ob-locked" onclick="OB.step1Next()">
                                Continue <i class="fas fa-chevron-right small ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══════ STEP 2: Profile ═══════ -->
                    <div id="step2" class="ob-step ob-form">
                        <div class="ob-sh">
                            <div>
                                <h2>Personal Details</h2>
                                <p>Fill in your personal details to complete the application.</p>
                            </div>
                            <span class="ob-badge"><i class="fa-solid fa-user-check me-1"></i> Step 2 of 3</span>
                        </div>

                        <div class="ob-g2">
                            <div class="mb-3">
                                <label>Full Name</label>
                                <input type="text" class="form-control" name="fullname" id="f_fullname" placeholder="Jane Doe" required>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" name="username" id="f_username" placeholder="Your display name" required>
                            </div>
                        </div>
                        <div class="ob-g2">
                            <div class="mb-3">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" id="f_email" placeholder="name@example.com" required>
                                <div id="emailError" style="display:none;color:#f87171;font-size:12px;margin-top:4px;">An account with this email already exists.</div>
                            </div>
                            <div class="mb-3">
                                <label>Date of Birth</label>
                                <input type="text" class="form-control" id="f_dob" name="dob" placeholder="DD-MM-YYYY" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" class="form-control" name="address" id="f_address" placeholder="Street, City, State, Zip" required>
                        </div>
                        <div class="mb-3">
                            <label>Country</label>
                            <select class="form-select" id="f_country" name="country" required>
                                <option value="" selected disabled>Select a country</option>
                                <option value="Afghanistan">Afghanistan</option><option value="Albania">Albania</option><option value="Algeria">Algeria</option><option value="Andorra">Andorra</option><option value="Angola">Angola</option><option value="Antigua and Barbuda">Antigua and Barbuda</option><option value="Argentina">Argentina</option><option value="Armenia">Armenia</option><option value="Australia">Australia</option><option value="Austria">Austria</option><option value="Azerbaijan">Azerbaijan</option><option value="Bahamas">Bahamas</option><option value="Bahrain">Bahrain</option><option value="Bangladesh">Bangladesh</option><option value="Barbados">Barbados</option><option value="Belarus">Belarus</option><option value="Belgium">Belgium</option><option value="Belize">Belize</option><option value="Benin">Benin</option><option value="Bhutan">Bhutan</option><option value="Bolivia">Bolivia</option><option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option><option value="Botswana">Botswana</option><option value="Brazil">Brazil</option><option value="Brunei">Brunei</option><option value="Bulgaria">Bulgaria</option><option value="Burkina Faso">Burkina Faso</option><option value="Burundi">Burundi</option><option value="Cambodia">Cambodia</option><option value="Cameroon">Cameroon</option><option value="Canada">Canada</option><option value="Cape Verde">Cape Verde</option><option value="Central African Republic">Central African Republic</option><option value="Chad">Chad</option><option value="Chile">Chile</option><option value="China">China</option><option value="Colombia">Colombia</option><option value="Comoros">Comoros</option><option value="Costa Rica">Costa Rica</option><option value="Croatia">Croatia</option><option value="Cuba">Cuba</option><option value="Cyprus">Cyprus</option><option value="Czech Republic">Czech Republic</option><option value="Denmark">Denmark</option><option value="Djibouti">Djibouti</option><option value="Dominica">Dominica</option><option value="Dominican Republic">Dominican Republic</option><option value="East Timor">East Timor</option><option value="Ecuador">Ecuador</option><option value="Egypt">Egypt</option><option value="El Salvador">El Salvador</option><option value="Equatorial Guinea">Equatorial Guinea</option><option value="Eritrea">Eritrea</option><option value="Estonia">Estonia</option><option value="Eswatini">Eswatini</option><option value="Ethiopia">Ethiopia</option><option value="Fiji">Fiji</option><option value="Finland">Finland</option><option value="France">France</option><option value="Gabon">Gabon</option><option value="Gambia">Gambia</option><option value="Georgia">Georgia</option><option value="Germany">Germany</option><option value="Ghana">Ghana</option><option value="Greece">Greece</option><option value="Grenada">Grenada</option><option value="Guatemala">Guatemala</option><option value="Guinea">Guinea</option><option value="Guinea-Bissau">Guinea-Bissau</option><option value="Guyana">Guyana</option><option value="Haiti">Haiti</option><option value="Honduras">Honduras</option><option value="Hungary">Hungary</option><option value="Iceland">Iceland</option><option value="India">India</option><option value="Indonesia">Indonesia</option><option value="Iran">Iran</option><option value="Iraq">Iraq</option><option value="Ireland">Ireland</option><option value="Israel">Israel</option><option value="Italy">Italy</option><option value="Jamaica">Jamaica</option><option value="Japan">Japan</option><option value="Jordan">Jordan</option><option value="Kazakhstan">Kazakhstan</option><option value="Kenya">Kenya</option><option value="Kiribati">Kiribati</option><option value="Kuwait">Kuwait</option><option value="Kyrgyzstan">Kyrgyzstan</option><option value="Laos">Laos</option><option value="Latvia">Latvia</option><option value="Lebanon">Lebanon</option><option value="Lesotho">Lesotho</option><option value="Liberia">Liberia</option><option value="Libya">Libya</option><option value="Liechtenstein">Liechtenstein</option><option value="Lithuania">Lithuania</option><option value="Luxembourg">Luxembourg</option><option value="Madagascar">Madagascar</option><option value="Malawi">Malawi</option><option value="Malaysia">Malaysia</option><option value="Maldives">Maldives</option><option value="Mali">Mali</option><option value="Malta">Malta</option><option value="Marshall Islands">Marshall Islands</option><option value="Mauritania">Mauritania</option><option value="Mauritius">Mauritius</option><option value="Mexico">Mexico</option><option value="Micronesia">Micronesia</option><option value="Moldova">Moldova</option><option value="Monaco">Monaco</option><option value="Mongolia">Mongolia</option><option value="Montenegro">Montenegro</option><option value="Morocco">Morocco</option><option value="Mozambique">Mozambique</option><option value="Myanmar">Myanmar</option><option value="Namibia">Namibia</option><option value="Nauru">Nauru</option><option value="Nepal">Nepal</option><option value="Netherlands">Netherlands</option><option value="New Zealand">New Zealand</option><option value="Nicaragua">Nicaragua</option><option value="Niger">Niger</option><option value="Nigeria">Nigeria</option><option value="North Korea">North Korea</option><option value="North Macedonia">North Macedonia</option><option value="Norway">Norway</option><option value="Oman">Oman</option><option value="Pakistan">Pakistan</option><option value="Palau">Palau</option><option value="Palestine">Palestine</option><option value="Panama">Panama</option><option value="Papua New Guinea">Papua New Guinea</option><option value="Paraguay">Paraguay</option><option value="Peru">Peru</option><option value="Philippines">Philippines</option><option value="Poland">Poland</option><option value="Portugal">Portugal</option><option value="Qatar">Qatar</option><option value="Romania">Romania</option><option value="Russia">Russia</option><option value="Rwanda">Rwanda</option><option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option><option value="Saint Lucia">Saint Lucia</option><option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option><option value="Samoa">Samoa</option><option value="San Marino">San Marino</option><option value="Sao Tome and Principe">Sao Tome and Principe</option><option value="Saudi Arabia">Saudi Arabia</option><option value="Senegal">Senegal</option><option value="Serbia">Serbia</option><option value="Seychelles">Seychelles</option><option value="Sierra Leone">Sierra Leone</option><option value="Singapore">Singapore</option><option value="Slovakia">Slovakia</option><option value="Slovenia">Slovenia</option><option value="Solomon Islands">Solomon Islands</option><option value="Somalia">Somalia</option><option value="South Africa">South Africa</option><option value="South Korea">South Korea</option><option value="South Sudan">South Sudan</option><option value="Spain">Spain</option><option value="Sri Lanka">Sri Lanka</option><option value="Sudan">Sudan</option><option value="Suriname">Suriname</option><option value="Sweden">Sweden</option><option value="Switzerland">Switzerland</option><option value="Syria">Syria</option><option value="Taiwan">Taiwan</option><option value="Tajikistan">Tajikistan</option><option value="Tanzania">Tanzania</option><option value="Thailand">Thailand</option><option value="Togo">Togo</option><option value="Tonga">Tonga</option><option value="Trinidad and Tobago">Trinidad and Tobago</option><option value="Tunisia">Tunisia</option><option value="Turkey">Turkey</option><option value="Turkmenistan">Turkmenistan</option><option value="Tuvalu">Tuvalu</option><option value="Uganda">Uganda</option><option value="Ukraine">Ukraine</option><option value="United Arab Emirates">United Arab Emirates</option><option value="United Kingdom">United Kingdom</option><option value="United States">United States</option><option value="Uruguay">Uruguay</option><option value="Uzbekistan">Uzbekistan</option><option value="Vanuatu">Vanuatu</option><option value="Vatican City">Vatican City</option><option value="Venezuela">Venezuela</option><option value="Vietnam">Vietnam</option><option value="Yemen">Yemen</option><option value="Zambia">Zambia</option><option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>

                        <div class="ob-cta">
                            <button type="button" class="btn-secondary-ob" onclick="OB.goTo(1)">
                                <i class="fas fa-chevron-left small me-1"></i> Back
                            </button>
                            <button type="button" id="btnStep2Submit" class="btn btn-primary" onclick="OB.step2Submit()">
                                Submit <i class="fas fa-chevron-right small ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══════ STEP 3: Finish ═══════ -->
                    <div id="step3" class="ob-step">
                        <div class="ob-sh">
                            <div>
                                <h2>Application Received! 🎉</h2>
                                <p>We've received your application. You'll get an update once reviewed.</p>
                            </div>
                            <span class="ob-badge"><i class="fa-solid fa-check me-1"></i> Done</span>
                        </div>

                        <div class="ob-feature" style="align-items:center;">
                            <div class="ob-feature-ico" style="font-size:18px;"><i class="fa-solid fa-shield-check text-success"></i></div>
                            <div>
                                <div class="ob-feature-t">Application submitted</div>
                                <div class="ob-feature-d">Typical review time: <strong>1–3 business days</strong>. Our team will contact you if anything is needed.</div>
                            </div>
                        </div>

                        <div class="ob-hr"></div>

                        <div class="ob-feature" style="align-items:center;">
                            <div class="ob-feature-ico"><i class="fa-solid fa-headset"></i></div>
                            <div style="flex:1;">
                                <div class="ob-feature-t">Need help?</div>
                                <div class="ob-feature-d">Join our <a href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener" class="text-info">Discord Support</a></div>
                            </div>
                            <span class="ob-pill"><i class="fa-solid fa-bolt"></i> Fast reply</span>
                        </div>
                    </div>

                </div><!-- /.ob-card-shell -->
            </section>
        </div><!-- /.ob-grid -->

        <!-- Rules Modal -->
        <div class="modal fade" id="rulesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rules</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="mb-2">General Rules</h6>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">YOU MUST treat all clients with respect and professionalism at all times.</li>
                            <li class="list-group-item">NOT ALLOWED to share any client information, screenshots, or conversations outside the official platform chat.</li>
                            <li class="list-group-item">NOT ALLOWED to contact clients on external platforms without explicit staff approval.</li>
                        </ul>
                        <h6 class="mb-2">Session Management</h6>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">YOU MUST confirm booked sessions within 30 minutes of receiving a booking request.</li>
                            <li class="list-group-item">YOU MUST be online and ready at the agreed session start time. Late arrivals over 10 minutes are considered a no-show.</li>
                            <li class="list-group-item">YOU MUST notify staff and the client as early as possible if you need to cancel or reschedule.</li>
                            <li class="list-group-item">NOT ALLOWED to cancel sessions less than 2 hours before the scheduled start without a valid reason.</li>
                            <li class="list-group-item">YOU MUST complete the full duration of a booked session. Early endings without client agreement are penalized.</li>
                        </ul>
                        <h6 class="mb-2">Behavior & Communication</h6>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">YOU MUST maintain a friendly, positive, and engaging attitude during all sessions.</li>
                            <li class="list-group-item">NOT ALLOWED to use offensive, discriminatory, or inappropriate language toward clients or staff.</li>
                            <li class="list-group-item">NOT ALLOWED to pressure, guilt-trip, or manipulate clients into tipping, extending sessions, or making purchases.</li>
                            <li class="list-group-item">YOU MUST report any client misconduct or uncomfortable situations to staff immediately via a Support Ticket.</li>
                        </ul>
                        <h6 class="mb-2">Platform Integrity</h6>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">NOT ALLOWED to accept private payments or off-platform bookings from clients.</li>
                            <li class="list-group-item">NOT ALLOWED to advertise other platforms, services, or personal social media profiles to clients.</li>
                            <li class="list-group-item">YOU MUST open a Support Ticket if a client approaches you for private bookings or off-platform contact.</li>
                            <li class="list-group-item">NOT ALLOWED to create fake reviews or manipulate the rating system in any way.</li>
                        </ul>
                        <h6 class="mb-2">Appearance & Sessions</h6>
                        <ul class="list-group mb-0">
                            <li class="list-group-item">YOU MUST maintain a presentable and appropriate appearance during video sessions.</li>
                            <li class="list-group-item">YOU MUST use a stable internet connection and good audio/video quality.</li>
                            <li class="list-group-item">NOT ALLOWED to be under the influence of alcohol or substances during sessions.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines Modal -->
        <div class="modal fade" id="finesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fines</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Accepting off-platform payments or private bookings</span><span class="badge bg-danger">250€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Sharing client info, conversations or screenshots outside the platform</span><span class="badge bg-danger">150€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Contacting clients on external platforms without staff approval</span><span class="badge bg-danger">100€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>No-show for a confirmed session without prior notice</span><span class="badge bg-danger">50€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Late cancellation (under 2 hours before session) without valid reason</span><span class="badge bg-danger">30€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Ending a session early without client agreement</span><span class="badge bg-danger">25€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Pressuring or guilt-tripping clients into tips or purchases</span><span class="badge bg-danger">25€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Inappropriate or unprofessional behavior toward clients</span><span class="badge bg-danger">25€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Advertising personal profiles or other platforms to clients</span><span class="badge bg-danger">20€</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span>Poor audio/video quality after repeated warnings</span><span class="badge bg-danger">10€</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Scripts -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/main.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js"></script>

    <script>
    // ═══════════════════════════════════════════════
    //  OB — custom step controller (no HSStepForm)
    // ═══════════════════════════════════════════════
    var OB = (function() {
        var currentStep = 1;
        var step2Submitted = false;

        var steps = {
            1: { el: 'step1', nav: 'nav1', ico: 'navico1', p: '33%', title: 'Step 1: Guidelines', desc: 'Read & accept the rules before you continue.' },
            2: { el: 'step2', nav: 'nav2', ico: 'navico2', p: '66%', title: 'Step 2: Profile',    desc: 'Fill in your personal details.' },
            3: { el: 'step3', nav: 'nav3', ico: 'navico3', p: '100%', title: 'Step 3: Done',      desc: 'Application submitted successfully.' }
        };

        function showStep(n) {
            for (var i = 1; i <= 3; i++) {
                var el = document.getElementById(steps[i].el);
                var nav = document.getElementById(steps[i].nav);
                var ico = document.getElementById(steps[i].ico);
                if (!el) continue;
                el.classList.toggle('ob-active', i === n);
                nav.classList.remove('active', 'done');
                if (i === n) {
                    nav.classList.add('active');
                    ico.innerHTML = i;
                } else if (i < n) {
                    nav.classList.add('done');
                    ico.innerHTML = '<i class="fa-solid fa-check" style="font-size:11px;color:#4ade80;"></i>';
                } else {
                    ico.innerHTML = i;
                }
            }
            var s = steps[n];
            document.getElementById('obRing').style.setProperty('--p', s.p);
            document.getElementById('obDot').textContent = n + '/3';
            document.getElementById('obTitle').textContent = s.title;
            document.getElementById('obDesc').textContent = s.desc;
            currentStep = n;
            window.scrollTo(0, 0);
        }

        function checkStep1Gate() {
            var ok = document.getElementById('chkRules').checked
                  && document.getElementById('chkFines').checked
                  && document.getElementById('chkAll').checked;
            var btn = document.getElementById('btnStep1Next');
            var hint = document.getElementById('step1Hint');
            if (ok) {
                btn.classList.remove('ob-locked');
                btn.disabled = false;
                if (hint) hint.style.display = 'none';
            } else {
                btn.classList.add('ob-locked');
                btn.disabled = false; // NOT html-disabled — we handle in onclick
                if (hint) hint.style.display = '';
            }
            return ok;
        }

        function step1Next() {
            if (!checkStep1Gate()) {
                if (typeof create_toast === 'function') {
                    create_toast('danger', 'Error', 'Please check all three boxes to continue.');
                }
                return;
            }
            showStep(2);
        }

        function validateStep2() {
            var fields = ['f_fullname','f_username','f_email','f_address'];
            for (var i = 0; i < fields.length; i++) {
                var el = document.getElementById(fields[i]);
                if (!el || !el.value.trim()) {
                    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Please fill in all required fields.');
                    el && el.focus();
                    return false;
                }
            }
            var email = document.getElementById('f_email').value.trim();
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Please enter a valid email address.');
                return false;
            }
            var country = document.getElementById('f_country').value;
            if (!country) {
                if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Please select your country.');
                return false;
            }
            var dob = document.getElementById('f_dob').value.trim();
            if (!dob) {
                if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Please enter your date of birth.');
                return false;
            }
            return true;
        }

        function step2Submit() {
            if (!validateStep2()) return;

            var btn = document.getElementById('btnStep2Submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

            var email = document.getElementById('f_email').value.trim();

            // Check email exists first
            $.ajax({
                type: 'POST',
                url: '<?= AJAX_URL ?>',
                data: { action: 'booster_email_exists', email: email },
                dataType: 'json',
                timeout: 7000,
                success: function(res) {
                    if (res && res.exists) {
                        document.getElementById('emailError').style.display = 'block';
                        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'An account with this email already exists.');
                        btn.disabled = false;
                        btn.innerHTML = 'Submit <i class="fas fa-chevron-right small ms-1"></i>';
                        return;
                    }
                    document.getElementById('emailError').style.display = 'none';
                    submitForm(btn);
                },
                error: function() {
                    submitForm(btn);
                }
            });
        }

        function submitForm(btn) {
            var formData = new FormData();
            formData.append('action', 'booster_application');
            formData.append('onboarding_token', '<?= htmlspecialchars((string)($onboarding_token ?? ''), ENT_QUOTES) ?>');
            formData.append('is_egirl', '1');
            formData.append('games', 'lol');
            formData.append('fullname', document.getElementById('f_fullname').value.trim());
            formData.append('username', document.getElementById('f_username').value.trim());
            formData.append('email', document.getElementById('f_email').value.trim());
            formData.append('dob', document.getElementById('f_dob').value.trim());
            formData.append('address', document.getElementById('f_address').value.trim());
            formData.append('country', document.getElementById('f_country').value);

            $.ajax({
                type: 'POST',
                url: '<?= AJAX_URL ?>',
                data: formData,
                dataType: 'text',
                cache: false,
                processData: false,
                contentType: false,
                success: function(response) {
                    btn.disabled = false;
                    btn.innerHTML = 'Submit <i class="fas fa-chevron-right small ms-1"></i>';
                    try {
                        var res = JSON.parse(response);
                        if (typeof ajax_response_handler === 'function') ajax_response_handler(res);
                        if (res.playSound !== 'error') {
                            step2Submitted = true;
                            showStep(3);
                        }
                    } catch(e) {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = String(response || '');
                        var realResponse = (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
                        if (typeof create_toast === 'function') create_toast('danger', 'Invalid server response', realResponse || 'The server returned an empty or invalid response.');
                        console.error('Invalid E-Girl onboarding response', response, e);
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    btn.disabled = false;
                    btn.innerHTML = 'Submit <i class="fas fa-chevron-right small ms-1"></i>';
                    var raw = String(xhr.responseText || '').trim();
                    var message = '';
                    if (raw) {
                        try {
                            var parsed = JSON.parse(raw);
                            message = (parsed.sendToast && parsed.sendToast.message) || parsed.message || parsed.error || '';
                        } catch (e) {
                            var tmp = document.createElement('div');
                            tmp.innerHTML = raw;
                            message = (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 1000);
                        }
                    }
                    if (!message) {
                        if (xhr.status === 403) message = 'The request was blocked with HTTP 403. Please reload the page and try again.';
                        else if (xhr.status === 429) message = 'Too many requests. Please wait briefly and try again.';
                        else if (xhr.status === 500) message = 'The server returned HTTP 500 while processing the application.';
                        else if (xhr.status === 502) message = 'The server returned HTTP 502 Bad Gateway.';
                        else if (xhr.status === 503) message = 'The server is temporarily unavailable, HTTP 503.';
                        else if (xhr.status === 504) message = 'The request timed out, HTTP 504 Gateway Timeout.';
                        else if (textStatus === 'timeout') message = 'The request timed out.';
                        else if (xhr.status === 0) message = 'The request could not reach the server. Check your connection and try again.';
                        else message = errorThrown || xhr.statusText || 'Request failed with HTTP ' + (xhr.status || 'unknown') + '.';
                    }
                    console.error('E-Girl onboarding failed', {status: xhr.status, response: xhr.responseText, textStatus: textStatus, errorThrown: errorThrown});
                    if (typeof create_toast === 'function') create_toast('danger', 'Error ' + (xhr.status || ''), message);
                }
            });
        }

        function goTo(n) {
            if (n < currentStep) { showStep(n); return; }
        }

        function tryGoTo(n) {
            if (n < currentStep) { showStep(n); return; }
            if (n === 2 && currentStep === 1) { step1Next(); return; }
        }

        // Init
        document.addEventListener('DOMContentLoaded', function() {
            // Checkbox listeners
            ['chkRules','chkFines','chkAll'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('change', checkStep1Gate);
            });
            checkStep1Gate();

            // Tom Select for country
            if (typeof TomSelect !== 'undefined') {
                new TomSelect('#f_country', { search: true, placeholder: 'Select a country', maxHeight: '220px' });
            }

            // Daterangepicker for dob
            if (typeof $ !== 'undefined' && $.fn.daterangepicker) {
                $('#f_dob').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoUpdateInput: false,
                    locale: { format: 'DD-MM-YYYY', cancelLabel: 'Clear' }
                });
                $('#f_dob').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY'));
                });
                $('#f_dob').on('cancel.daterangepicker', function() {
                    $(this).val('');
                });
            }

            showStep(1);
        });

        return { step1Next: step1Next, step2Submit: step2Submit, goTo: goTo, tryGoTo: tryGoTo };
    })();
    </script>
</body>
</html>
