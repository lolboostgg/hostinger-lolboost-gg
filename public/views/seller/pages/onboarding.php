<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Seller Onboarding | LoLBoost.gg</title>
    <meta name="description" content="LoLBoost.gg Seller Onboarding">

    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&amp;display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">

    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">

    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme.min.css?v" data-hs-appearance="light" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="dark" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="default" as="style">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/core/dash/css/main.css?v<?= rand(0, 34534) ?>">

    <style data-hs-appearance-onload-styles="">* { transition: unset !important; } body { opacity: 0; }</style>

    <style>
        :root {
            --ob-card-bg: rgba(14, 16, 20, 0.72);
            --ob-border: rgba(255, 255, 255, 0.08);
            --ob-soft: rgba(255, 255, 255, 0.04);
            --ob-text: rgba(255, 255, 255, 0.88);
            --ob-muted: rgba(255, 255, 255, 0.55);
        }

        body.onboarding-page {
            min-height: 100vh;
            background: radial-gradient(900px 500px at 15% 15%, rgba(16, 140, 80, 0.22), transparent 60%),
                radial-gradient(900px 500px at 85% 20%, rgba(55, 125, 255, 0.15), transparent 55%),
                radial-gradient(700px 500px at 55% 95%, rgba(16, 140, 80, 0.12), transparent 55%),
                #0b0d11;
            color: var(--ob-text);
        }

        .onboarding-bg-noise {
            pointer-events: none; position: fixed; inset: 0; opacity: 0.08;
            mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='220' height='220' filter='url(%23n)' opacity='.35'/%3E%3C/svg%3E");
        }

        .onboarding-header {
            position: sticky; top: 0; z-index: 10;
            backdrop-filter: blur(12px);
            background: rgba(8, 10, 14, 0.55);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .onboarding-header .inner {
            max-width: 1240px; margin: 0 auto; padding: 16px 18px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .brand img { height: 34px; width: auto; }
        .brand .title { font-weight: 700; letter-spacing: .01em; line-height: 1.1; font-size: 14px; }
        .brand .subtitle { font-size: 12px; color: var(--ob-muted); margin-top: 2px; }
        .header-actions { display: flex; align-items: center; gap: 10px; }
        .pill {
            border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);
            color: rgba(255,255,255,0.85); border-radius: 999px; padding: 8px 12px;
            font-size: 12px; display: inline-flex; align-items: center; gap: 8px;
        }

        .onboarding-shell { max-width: 1240px; margin: 0 auto; padding: 22px 18px 40px; }
        .onboarding-grid { display: grid; grid-template-columns: 360px 1fr; gap: 18px; align-items: start; }

        .glass {
            background: var(--ob-card-bg); border: 1px solid var(--ob-border);
            border-radius: 18px; box-shadow: 0 22px 70px rgba(0,0,0,0.55);
            backdrop-filter: blur(12px);
        }
        .aside { position: sticky; top: 78px; padding: 16px; overflow: hidden; }
        .aside .progress-block {
            display: flex; gap: 12px; align-items: center; padding: 12px;
            border-radius: 14px; background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .ring {
            width: 52px; height: 52px; border-radius: 50%;
            background: conic-gradient(rgba(var(--bs-success-rgb,25,135,84), 0.95) var(--ring), rgba(255,255,255,0.10) 0);
            display: grid; place-items: center; flex: 0 0 52px;
        }
        .ring .dot {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(10,12,16,0.9); border: 1px solid rgba(255,255,255,0.10);
            display: grid; place-items: center; font-weight: 700; font-size: 13px;
        }
        .aside h5 { margin: 0; font-size: 14px; font-weight: 700; }
        .aside p { margin: 2px 0 0; color: var(--ob-muted); font-size: 12px; }

        .onboarding-stepper { margin-top: 14px; display: grid; gap: 10px; }
        .onboarding-stepper.step::before, .onboarding-stepper.step::after,
        .onboarding-stepper.step .step-item::before, .onboarding-stepper.step .step-item::after,
        .onboarding-stepper.step .step-content-wrapper::before, .onboarding-stepper.step .step-content-wrapper::after,
        .onboarding-stepper.step .step-icon::before, .onboarding-stepper.step .step-icon::after {
            content: none !important; display: none !important;
        }
        .onboarding-stepper .step-item { margin: 0 !important; }
        .onboarding-stepper .step-content-wrapper {
            display: flex; align-items: center; gap: 10px; padding: 12px;
            border-radius: 14px; border: 1px solid rgba(255,255,255,0.07);
            background: rgba(255,255,255,0.02); text-decoration: none; color: inherit;
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
        }
        .onboarding-stepper .step-content-wrapper:hover {
            transform: translateY(-1px); border-color: rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.03);
        }
        .onboarding-stepper .step-icon {
            width: 34px; height: 34px; border-radius: 12px; display: grid; place-items: center;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            font-weight: 700; color: rgba(255,255,255,0.9); flex: 0 0 34px;
        }
        .onboarding-stepper .step-title { font-weight: 700; font-size: 13px; display: block; line-height: 1.2; }
        .onboarding-stepper .step-subtitle { display: block; font-size: 12px; color: var(--ob-muted); margin-top: 2px; }

        .aside .mini-card {
            margin-top: 14px; padding: 12px; border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.07); background: rgba(255,255,255,0.02);
        }
        .checklist { margin: 0; padding-left: 18px; color: rgba(255,255,255,0.80); font-size: 12px; }
        .checklist li { margin: 6px 0; }

        .content { padding: 0; }
        .content .card-shell { padding: 18px; }
        .step-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; margin-bottom: 14px;
        }
        .step-header h2 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: .01em; }
        .step-header p { margin: 6px 0 0; color: var(--ob-muted); max-width: 62ch; }
        .badge-soft {
            background: rgba(25,135,84,0.16); border: 1px solid rgba(25,135,84,0.28);
            color: rgba(255,255,255,0.92); padding: 8px 12px; border-radius: 999px;
            font-size: 12px; white-space: nowrap;
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .divider { height: 1px; background: rgba(255,255,255,0.07); margin: 14px 0; }

        .onboarding-form .form-label {
            font-weight: 700; letter-spacing: .01em; color: rgba(255,255,255,0.85); margin-bottom: .5rem;
        }
        .onboarding-form .form-control,
        .onboarding-form .form-select {
            background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.92); border-radius: 14px; min-height: 46px; padding: 10px 12px;
        }
        .onboarding-form .form-control::placeholder { color: rgba(255,255,255,0.35); }
        .onboarding-form .form-control:focus,
        .onboarding-form .form-select:focus {
            border-color: rgba(25,135,84,0.65);
            box-shadow: 0 0 0 .18rem rgba(25,135,84,0.15);
            background: rgba(255,255,255,0.03);
        }
        .onboarding-form select.form-select option { background: #0f1115; color: rgba(255,255,255,0.92); }

        .cta-row {
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; margin-top: 16px;
        }
        .btn { border-radius: 14px; }
        .btn-primary { font-weight: 700; background: #198754; border-color: #198754; }
        .btn-primary:hover { background: #157347; border-color: #146c43; }
        .btn-secondary {
            background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.86);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.09); border-color: rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.92);
        }
        .cta-row .btn { min-height: 44px; padding: 10px 18px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }

        /* Upload tiles */
        .upload-tile {
            position: relative; padding: 14px; border-radius: 16px;
            border: 1px dashed rgba(255,255,255,0.18); background: rgba(255,255,255,0.02);
        }
        .upload-tile .top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
        .upload-tile .name { font-weight: 800; font-size: 13px; }
        .upload-tile .hint { color: var(--ob-muted); font-size: 12px; }

        .ob-file-hidden {
            position: absolute !important; left: -9999px !important; width: 1px !important;
            height: 1px !important; opacity: 0 !important; pointer-events: none !important;
        }
        .ob-dropzone {
            margin-top: 10px; border-radius: 14px; border: 1px dashed rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.02); padding: 14px; display: flex;
            align-items: center; justify-content: center; gap: 10px; cursor: pointer;
            user-select: none; outline: none; min-height: 86px;
        }
        .ob-dropzone .ob-dropzone-ico {
            width: 34px; height: 34px; border-radius: 12px; display: grid; place-items: center;
            border: 1px solid rgba(255,255,255,0.10); background: rgba(0,0,0,0.20); color: rgba(255,255,255,0.85);
        }
        .ob-dropzone .ob-dropzone-text { display: flex; flex-direction: column; line-height: 1.15; }
        .ob-dropzone .ob-dropzone-title { font-weight: 800; font-size: 12px; }
        .ob-dropzone .ob-dropzone-sub { font-size: 12px; color: rgba(255,255,255,0.60); margin-top: 2px; }
        .ob-dropzone:focus { box-shadow: 0 0 0 .25rem rgba(25,135,84,0.15); border-color: rgba(25,135,84,0.65); }
        .ob-dropzone.is-dragover { border-color: rgba(25,135,84,0.70); background: rgba(25,135,84,0.10); }

        .ob-upload-preview {
            display: none; margin-top: 10px; border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.10); background: rgba(0,0,0,0.22); overflow: hidden;
        }
        .ob-upload-preview img { width: 100%; height: auto; display: block; max-height: 260px; object-fit: contain; background: rgba(0,0,0,0.25); }
        .ob-upload-preview .ob-upload-actions {
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; padding: 10px; border-top: 1px solid rgba(255,255,255,0.08);
        }
        .ob-upload-preview .ob-upload-filename { font-size: 12px; color: rgba(255,255,255,0.70); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 75%; }

        /* TomSelect dark */
        .ts-wrapper .ts-control, .ts-wrapper.single .ts-control {
            background: rgba(255,255,255,0.02) !important; border: 1px solid rgba(255,255,255,0.10) !important;
            color: rgba(255,255,255,0.92) !important; border-radius: 14px !important; min-height: 46px; padding: 10px 12px;
        }
        .ts-wrapper.focus .ts-control { border-color: rgba(25,135,84,0.65) !important; box-shadow: 0 0 0 .25rem rgba(25,135,84,0.15) !important; }
        .ts-control input { color: rgba(255,255,255,0.92) !important; }
        .ts-wrapper .ts-control .item { color: rgba(255,255,255,0.92) !important; }
        .ts-dropdown {
            background: rgba(14,16,20,0.98) !important; border: 1px solid rgba(255,255,255,0.10) !important;
            border-radius: 14px !important; overflow: hidden; box-shadow: 0 16px 55px rgba(0,0,0,0.6);
        }
        .ts-dropdown .option { color: rgba(255,255,255,0.88) !important; padding: 10px 12px !important; }
        .ts-dropdown .option.active, .ts-dropdown .option:hover { background: rgba(25,135,84,0.18) !important; color: #fff !important; }

        /* Datepicker dark */
        .daterangepicker { background: rgba(14,16,20,0.98) !important; border: 1px solid rgba(255,255,255,0.10) !important; border-radius: 14px !important; box-shadow: 0 16px 55px rgba(0,0,0,0.6) !important; }
        .daterangepicker:before, .daterangepicker:after { display: none !important; }
        .daterangepicker .calendar-table { background: transparent !important; border: none !important; }
        .daterangepicker .calendar-table th, .daterangepicker .calendar-table td { color: rgba(255,255,255,0.88) !important; }
        .daterangepicker td.available:hover { background: rgba(25,135,84,0.18) !important; }
        .daterangepicker td.active, .daterangepicker td.active:hover { background: rgba(25,135,84,0.35) !important; }
        .daterangepicker .drp-buttons { border-top: 1px solid rgba(255,255,255,0.08) !important; }
        .daterangepicker select.monthselect, .daterangepicker select.yearselect {
            background: rgba(14,16,20,0.98) !important; color: rgba(255,255,255,0.88) !important;
            border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important;
        }

        .feature {
            padding: 14px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.07);
            background: rgba(255,255,255,0.02); display: flex; gap: 12px; align-items: flex-start;
        }
        .feature .ico {
            width: 40px; height: 40px; border-radius: 14px; display: grid; place-items: center;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            font-size: 16px; flex: 0 0 40px;
        }
        .feature .t { font-weight: 700; font-size: 13px; margin-bottom: 2px; }
        .feature .d { font-size: 12px; color: var(--ob-muted); line-height: 1.4; }

        .ob-scroll::-webkit-scrollbar { width: 8px; }
        .ob-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.14); border-radius: 999px; }
        .ob-scroll::-webkit-scrollbar-track { background: transparent; }

        @media (max-width: 991.98px) { .onboarding-grid { grid-template-columns: 1fr; } .aside { position: relative; top: 0; } .grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 575.98px) { .onboarding-shell { padding: 18px 12px 30px; } .onboarding-header .inner { padding: 14px 12px; } }
    </style>

    <script>
        window.hs_config = {"autopath":"@@autopath","deleteLine":"hs-builder:delete","deleteLine:build":"hs-builder:build-delete","deleteLine:dist":"hs-builder:dist-delete","previewMode":false,"vars":{"themeFont":"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap","version":"?v=1.0"},"layoutBuilder":{"extend":{"switcherSupport":true},"header":{"layoutMode":"default","containerMode":"container-fluid"},"sidebarLayout":"default"},"themeAppearance":{"layoutSkin":"default","sidebarSkin":"default","styles":{"colors":{"primary":"#198754","transparent":"transparent","white":"#fff","dark":"132144","gray":{"100":"#f9fafc","900":"#1e2022"}},"font":"Montserrat"}},"languageDirection":{"lang":"en"}};
    </script>
</head>

<body class="onboarding-page">
    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>

    <div class="onboarding-bg-noise"></div>
    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed" style="top:75px;"></div>

    <header class="onboarding-header">
        <div class="inner">
            <div class="brand">
                <img src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="LoLBoost.gg"/>
                <div class="text-truncate">
                    <div class="title">Seller Account Onboarding</div>
                    <div class="subtitle">Secure verification — takes 3–5 minutes</div>
                </div>
            </div>
            <div class="header-actions">
                <span class="pill"><i class="fa-solid fa-lock"></i> Encrypted upload</span>
                <a class="pill text-decoration-none" href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener"><i class="fa-solid fa-headset"></i> Support</a>
            </div>
        </div>
    </header>

    <main role="main" class="onboarding-shell">
        <form class="js-step-form" action="<?= AJAX_URL ?>" method="post" enctype="multipart/form-data" data-hs-step-form-options="{
            &#34;progressSelector&#34;: &#34;.js-step-progress&#34;,
            &#34;stepsSelector&#34;: &#34;#sellerObStepContent&#34;,
            &#34;endSelector&#34;: &#34;#sellerObFinishBtn&#34;
        }">
            <input type="hidden" name="action" value="seller_application">
            <input type="hidden" name="onboarding_status" value="completed">
            <input type="hidden" name="onboarding_token" value="<?= htmlspecialchars((string)($onboarding_token ?? ($_GET['t'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">

            <div class="onboarding-grid">
                <!-- ASIDE -->
                <aside class="glass aside">
                    <div class="progress-block">
                        <div class="ring" id="obRing" style="--ring: 33%;">
                            <div class="dot" id="obStepDot">1/3</div>
                        </div>
                        <div>
                            <h5 id="obStepTitle">Step 1: Your Details</h5>
                            <p id="obStepDesc">Fill in your personal information.</p>
                        </div>
                    </div>

                    <ul class="js-step-progress onboarding-stepper step mb-0" id="sellerObStepper">
                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options="{&#34;targetSelector&#34;:&#34;#step1&#34;}">
                                <span class="step-icon">1</span>
                                <span class="step-content">
                                    <span class="step-title">Personal Details</span>
                                    <span class="step-subtitle">Your info & contact</span>
                                </span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options="{&#34;targetSelector&#34;:&#34;#step2&#34;}">
                                <span class="step-icon">2</span>
                                <span class="step-content">
                                    <span class="step-title">Identity Documents</span>
                                    <span class="step-subtitle">ID & selfie upload</span>
                                </span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options="{&#34;targetSelector&#34;:&#34;#step3&#34;}">
                                <span class="step-icon">3</span>
                                <span class="step-content">
                                    <span class="step-title">Submitted</span>
                                    <span class="step-subtitle">Awaiting review</span>
                                </span>
                            </a>
                        </li>
                    </ul>

                    <div class="mini-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong style="font-size:13px;">What you'll need</strong>
                            <span class="badge-soft">3–5 min</span>
                        </div>
                        <div class="divider" style="margin:10px 0;"></div>
                        <ul class="checklist">
                            <li>Government-issued ID (front & back)</li>
                            <li>A clear selfie holding your ID</li>
                            <li>Valid email address for login</li>
                            <li>Your full name & address</li>
                        </ul>
                    </div>

                    <div class="mini-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong style="font-size:13px;">Privacy</strong>
                            <span class="pill" style="padding:6px 10px;"><i class="fa-solid fa-shield"></i> Secure</span>
                        </div>
                        <p class="mb-0" style="margin-top:8px;font-size:12px;">Documents are used only for identity verification and reviewed by our staff. They are never shared with third parties.</p>
                    </div>
                </aside>

                <!-- CONTENT -->
                <section class="glass content">
                    <div class="card-shell" id="sellerObStepContent">

                        <!-- STEP 1: Personal Details -->
                        <div id="step1" class="active formWizardStep onboarding-form">
                            <div class="step-header">
                                <div>
                                    <h2>Personal Details</h2>
                                    <p>This information is used for identity verification and your seller account setup.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-user-check me-1"></i> Step 1</span>
                            </div>

                            <div class="grid-2">
                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="fullname" placeholder="John Doe" required="">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Seller Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" placeholder="CoolSeller99" required="">
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" placeholder="name@example.com" required="">
                                    <div class="invalid-feedback" id="emailExistsFeedback">A seller with this email already exists.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="dob" name="dob" placeholder="DD-MM-YYYY" required="">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" placeholder="Street, City, State, ZIP" required="">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <select id="country" name="country" class="form-control" required="">
                                    <option value="" selected="" disabled="">Select a country</option>
                                    <option value="Afghanistan">Afghanistan</option><option value="Albania">Albania</option><option value="Algeria">Algeria</option><option value="Andorra">Andorra</option><option value="Angola">Angola</option><option value="Antigua and Barbuda">Antigua and Barbuda</option><option value="Argentina">Argentina</option><option value="Armenia">Armenia</option><option value="Australia">Australia</option><option value="Austria">Austria</option><option value="Azerbaijan">Azerbaijan</option><option value="Bahamas">Bahamas</option><option value="Bahrain">Bahrain</option><option value="Bangladesh">Bangladesh</option><option value="Barbados">Barbados</option><option value="Belarus">Belarus</option><option value="Belgium">Belgium</option><option value="Belize">Belize</option><option value="Benin">Benin</option><option value="Bolivia">Bolivia</option><option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option><option value="Botswana">Botswana</option><option value="Brazil">Brazil</option><option value="Bulgaria">Bulgaria</option><option value="Canada">Canada</option><option value="Chile">Chile</option><option value="China">China</option><option value="Colombia">Colombia</option><option value="Croatia">Croatia</option><option value="Cyprus">Cyprus</option><option value="Czech Republic">Czech Republic</option><option value="Denmark">Denmark</option><option value="Dominican Republic">Dominican Republic</option><option value="Ecuador">Ecuador</option><option value="Egypt">Egypt</option><option value="Estonia">Estonia</option><option value="Finland">Finland</option><option value="France">France</option><option value="Georgia">Georgia</option><option value="Germany">Germany</option><option value="Ghana">Ghana</option><option value="Greece">Greece</option><option value="Hungary">Hungary</option><option value="Iceland">Iceland</option><option value="India">India</option><option value="Indonesia">Indonesia</option><option value="Iran">Iran</option><option value="Iraq">Iraq</option><option value="Ireland">Ireland</option><option value="Israel">Israel</option><option value="Italy">Italy</option><option value="Jamaica">Jamaica</option><option value="Japan">Japan</option><option value="Jordan">Jordan</option><option value="Kazakhstan">Kazakhstan</option><option value="Kenya">Kenya</option><option value="Kuwait">Kuwait</option><option value="Latvia">Latvia</option><option value="Lebanon">Lebanon</option><option value="Lithuania">Lithuania</option><option value="Luxembourg">Luxembourg</option><option value="Malaysia">Malaysia</option><option value="Malta">Malta</option><option value="Mexico">Mexico</option><option value="Moldova">Moldova</option><option value="Morocco">Morocco</option><option value="Netherlands">Netherlands</option><option value="New Zealand">New Zealand</option><option value="Nigeria">Nigeria</option><option value="North Macedonia">North Macedonia</option><option value="Norway">Norway</option><option value="Pakistan">Pakistan</option><option value="Palestine">Palestine</option><option value="Panama">Panama</option><option value="Peru">Peru</option><option value="Philippines">Philippines</option><option value="Poland">Poland</option><option value="Portugal">Portugal</option><option value="Qatar">Qatar</option><option value="Romania">Romania</option><option value="Russia">Russia</option><option value="Saudi Arabia">Saudi Arabia</option><option value="Serbia">Serbia</option><option value="Singapore">Singapore</option><option value="Slovakia">Slovakia</option><option value="Slovenia">Slovenia</option><option value="South Africa">South Africa</option><option value="South Korea">South Korea</option><option value="Spain">Spain</option><option value="Sri Lanka">Sri Lanka</option><option value="Sweden">Sweden</option><option value="Switzerland">Switzerland</option><option value="Taiwan">Taiwan</option><option value="Thailand">Thailand</option><option value="Tunisia">Tunisia</option><option value="Turkey">Turkey</option><option value="Ukraine">Ukraine</option><option value="United Arab Emirates">United Arab Emirates</option><option value="United Kingdom">United Kingdom</option><option value="United States">United States</option><option value="Uruguay">Uruguay</option><option value="Uzbekistan">Uzbekistan</option><option value="Venezuela">Venezuela</option><option value="Vietnam">Vietnam</option>
                                </select>
                            </div>

                            <div class="cta-row">
                                <span class="text-muted" style="font-size:12px;">All fields marked * are required.</span>
                                <button type="button" class="btn btn-primary" id="step1NextBtn" data-hs-step-form-next-options="{&#34;targetSelector&#34;:&#34;#step2&#34;}">
                                    Continue <i class="fas fa-chevron-right small ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: Documents -->
                        <div id="step2" style="display:none;" class="formWizardStep onboarding-form">
                            <div class="step-header">
                                <div>
                                    <h2>Identity Documents</h2>
                                    <p>Upload clear photos. Avoid glare, blur, and cropped edges. JPG, PNG or WEBP only, maximum 8 MB per image.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Step 2</span>
                            </div>

                            <div class="grid-2">
                                <div class="upload-tile">
                                    <div class="top">
                                        <div>
                                            <div class="name">ID Card / Passport (Front)</div>
                                            <div class="hint">JPG, JPEG, PNG, WEBP • max. 8 MB • readable, full frame</div>
                                        </div>
                                        <span class="pill" style="padding:6px 10px;"><i class="fa-regular fa-id-card"></i></span>
                                    </div>
                                    <input type="file" class="form-control" name="id_card_front" accept="image/jpeg,image/png,image/webp" required=""/>
                                </div>

                                <div class="upload-tile">
                                    <div class="top">
                                        <div>
                                            <div class="name">ID Card / Passport (Back)</div>
                                            <div class="hint">JPG, JPEG, PNG, WEBP • max. 8 MB • no reflections</div>
                                        </div>
                                        <span class="pill" style="padding:6px 10px;"><i class="fa-regular fa-id-card"></i></span>
                                    </div>
                                    <input type="file" class="form-control" name="id_card_back" accept="image/jpeg,image/png,image/webp" required=""/>
                                </div>
                            </div>

                            <div class="upload-tile" style="margin-top:12px;">
                                <div class="top">
                                    <div>
                                        <div class="name">Selfie Holding Your ID</div>
                                        <div class="hint">JPG, JPEG, PNG, WEBP • max. 8 MB • hold your ID clearly next to your face</div>
                                    </div>
                                    <span class="pill" style="padding:6px 10px;"><i class="fa-solid fa-camera"></i></span>
                                </div>
                                <input type="file" class="form-control" name="selfie" accept="image/jpeg,image/png,image/webp" required=""/>
                            </div>

                            <div class="cta-row">
                                <button type="button" class="btn btn-secondary px-4" data-hs-step-form-prev-options="{&#34;targetSelector&#34;:&#34;#step1&#34;}">
                                    <i class="fas fa-chevron-left small me-1"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary px-4" id="step2SubmitBtn" data-hs-step-form-next-options="{&#34;targetSelector&#34;:&#34;#step3&#34;}">
                                    Submit Application <i class="fas fa-chevron-right small ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: Done -->
                        <div id="step3" style="display:none;" class="formWizardStep">
                            <div class="step-header">
                                <div>
                                    <h2>Application Submitted!</h2>
                                    <p>We've received your application and will review it within 1–3 business days.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-check me-1"></i> Done</span>
                            </div>

                            <div class="feature" style="align-items:center; margin-bottom:12px;">
                                <div class="ico" style="font-size:18px;"><i class="fa-solid fa-shield-check text-success"></i></div>
                                <div>
                                    <div class="t">Application under review</div>
                                    <div class="d">Typical review time: <strong>1–3 business days</strong>. Once approved, you'll receive your login credentials by email.</div>
                                </div>
                            </div>

                            <div class="feature" style="align-items:center;">
                                <div class="ico"><i class="fa-solid fa-envelope"></i></div>
                                <div>
                                    <div class="t">Check your email</div>
                                    <div class="d">Your login details will be sent to the email address you provided as soon as your application is approved.</div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="feature" style="align-items:center;">
                                <div class="ico"><i class="fa-solid fa-headset"></i></div>
                                <div style="flex:1;">
                                    <div class="t">Need help?</div>
                                    <div class="d">Join our <a href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener" class="text-success">Discord Support</a></div>
                                </div>
                                <span class="pill"><i class="fa-solid fa-bolt"></i> Fast reply</span>
                            </div>
                        </div>

                        <button type="button" id="sellerObFinishBtn"></button>
                    </div>
                </section>
            </div>
        </form>
    </main>

    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-step-form/dist/hs-step-form.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/main.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js"></script>

    <script>
    (function () {
        // ── Aside state ──
        function setAside(step) {
            const ring = document.getElementById('obRing');
            const dot  = document.getElementById('obStepDot');
            const title = document.getElementById('obStepTitle');
            const desc  = document.getElementById('obStepDesc');
            const map = {
                step1: { p: 33,  t: 'Step 1: Your Details',   d: 'Fill in your personal information.' },
                step2: { p: 66,  t: 'Step 2: Documents',      d: 'Upload your ID photos and selfie.' },
                step3: { p: 100, t: 'Step 3: Done!',          d: 'Application submitted successfully.' },
            };
            const cfg = map[step] || map.step1;
            ring.style.setProperty('--ring', cfg.p + '%');
            dot.textContent = (step.replace('step','')) + '/3';
            title.textContent = cfg.t;
            desc.textContent = cfg.d;
        }

        // ── Email check ──
        function clearEmailError() {
            const el = document.querySelector('#step1 input[name="email"]');
            const fb = document.getElementById('emailExistsFeedback');
            if (el) { el.setCustomValidity(''); el.classList.remove('is-invalid'); }
            if (fb) fb.style.display = 'none';
        }
        function showEmailError() {
            const el = document.querySelector('#step1 input[name="email"]');
            const fb = document.getElementById('emailExistsFeedback');
            if (el) { el.setCustomValidity('exists'); el.classList.add('is-invalid'); }
            if (fb) fb.style.display = 'block';
        }
        function checkEmailExists(email) {
            return new Promise((resolve) => {
                if (!email) return resolve({ exists: false });
                $.ajax({ type: 'post', url: '<?= AJAX_URL ?>', data: { action: 'seller_email_exists', email }, dataType: 'json', timeout: 7000,
                    success: (res) => resolve({ exists: !!(res && res.exists) }),
                    error: () => resolve({ exists: false })
                });
            });
        }

        // ── Step helpers ──
        const stepOrder = ['step1', 'step2', 'step3'];
        let step2Submitted = false;

        function isStepVisible(el) {
            if (!el) return false;
            return window.getComputedStyle(el).display !== 'none';
        }
        function getCurrentStep() {
            return Array.from(document.querySelectorAll('#sellerObStepContent .formWizardStep')).find(isStepVisible) || null;
        }

        function disableRequiredFields(stepEl) {
            if (!stepEl) return;
            stepEl.querySelectorAll('input,select,textarea').forEach(el => {
                if (el.type === 'hidden') return;
                if (el.hasAttribute('required')) { el.dataset.obRequired = '1'; el.removeAttribute('required'); }
            });
        }
        function enableRequiredFields(stepEl) {
            if (!stepEl) return;
            stepEl.querySelectorAll('input,select,textarea').forEach(el => {
                if (el.dataset.obRequired === '1') el.setAttribute('required', 'required');
            });
        }

        function validateStep1(toast = true) {
            const s = document.getElementById('step1');
            enableRequiredFields(s);
            const controls = Array.from(s.querySelectorAll('input,select,textarea')).filter(el => el.type !== 'hidden' && !el.disabled);
            const ok = controls.every(el => el.checkValidity());
            if (!ok && toast) { create_toast('danger', 'Error', 'Please fill out all required fields.'); controls.find(el => !el.checkValidity())?.reportValidity(); }
            return ok;
        }
        function validateStep2Uploads(toast = true) {
            const s = document.getElementById('step2');
            const inputs = Array.from(s.querySelectorAll('input[type="file"][name]'));
            const maxBytesPerImage = 8 * 1024 * 1024;

            const missing = inputs.find(input => !input.files || input.files.length === 0);
            if (missing) {
                if (toast) create_toast('danger', 'Error', 'Please upload all required images.');
                return false;
            }

            const oversized = inputs.find(input => input.files[0].size > maxBytesPerImage);
            if (oversized) {
                if (toast) create_toast('danger', 'Error', 'Each image may be up to 8 MB. Please choose a smaller image.');
                oversized.value = '';
                oversized.dispatchEvent(new Event('change', { bubbles: true }));
                return false;
            }

            return true;
        }

        // Disable all on init then sync
        document.querySelectorAll('#sellerObStepContent .formWizardStep').forEach(disableRequiredFields);

        let lastActiveStep = null;
        function syncActiveStep() {
            const steps = document.querySelectorAll('#sellerObStepContent .formWizardStep');
            const cur = getCurrentStep();
            if (!cur || cur.id === lastActiveStep) return;
            steps.forEach(s => s === cur ? enableRequiredFields(s) : disableRequiredFields(s));
            setAside(cur.id);
            lastActiveStep = cur.id;
        }
        syncActiveStep();

        const stepsRoot = document.getElementById('sellerObStepContent');
        if (stepsRoot && window.MutationObserver) {
            new MutationObserver(() => { syncActiveStep(); setTimeout(syncActiveStep, 0); })
                .observe(stepsRoot, { subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
        }

        // Stepper click updates aside
        document.querySelectorAll('[data-hs-step-form-next-options],[data-hs-step-form-prev-options]').forEach(el => {
            el.addEventListener('click', () => setTimeout(syncActiveStep, 0));
        });

        // Block skipping
        const stepper = document.getElementById('sellerObStepper');
        if (stepper) {
            stepper.addEventListener('click', (e) => {
                const link = e.target?.closest?.('[data-hs-step-form-next-options]');
                if (!link) return;
                let targetId = null;
                try { targetId = (JSON.parse(link.getAttribute('data-hs-step-form-next-options') || '{}').targetSelector || '').replace('#', ''); } catch (err) {}
                if (!targetId) return;
                const tgtIdx = stepOrder.indexOf(targetId);
                const maxIdx = step2Submitted ? 2 : (validateStep1(false) ? 1 : 0);
                if (tgtIdx > maxIdx) { e.preventDefault(); e.stopPropagation(); create_toast('danger', 'Error', 'Please complete the previous step first.'); return false; }
            }, true);
        }

        // ── Upload UX ──
        let lastFocusedFileInput = null;

        function buildPreviewEl(inputEl) {
            const wrap = document.createElement('div'); wrap.className = 'ob-upload-preview';
            const img = document.createElement('img'); img.alt = 'Preview';
            const actions = document.createElement('div'); actions.className = 'ob-upload-actions';
            const filename = document.createElement('div'); filename.className = 'ob-upload-filename';
            const btn = document.createElement('button'); btn.type = 'button'; btn.className = 'btn btn-sm btn-outline-danger'; btn.innerHTML = '<i class="fa-solid fa-trash"></i> Remove';
            actions.appendChild(filename); actions.appendChild(btn);
            wrap.appendChild(img); wrap.appendChild(actions);
            inputEl.insertAdjacentElement('afterend', wrap);
            return { wrap, img, filename, btn };
        }

        function setFileOnInput(inputEl, file) {
            const dt = new DataTransfer(); dt.items.add(file); inputEl.files = dt.files;
            inputEl.dispatchEvent(new Event('change', { bubbles: true }));
        }
        function extractImageFile(e) {
            const item = Array.from(e.clipboardData?.items || []).find(i => i.type?.startsWith('image/'));
            if (!item) return null;
            const blob = item.getAsFile(); if (!blob) return null;
            const ext = (blob.type || 'image/png').split('/')[1] || 'png';
            return new File([blob], `pasted_${Date.now()}.${ext}`, { type: blob.type });
        }

        function enhanceFileInput(inputEl) {
            if (!inputEl || inputEl.dataset.obEnhanced === '1') return;
            inputEl.dataset.obEnhanced = '1';
            inputEl.classList.add('ob-file-hidden');
            const tile = inputEl.closest('.upload-tile');
            if (tile) { tile.tabIndex = 0; }
            const ui = buildPreviewEl(inputEl);
            const dz = document.createElement('div');
            dz.className = 'ob-dropzone'; dz.tabIndex = 0;
            dz.innerHTML = `<div class="ob-dropzone-ico"><i class="fa-regular fa-clipboard"></i></div><div class="ob-dropzone-text"><div class="ob-dropzone-title">Paste (Ctrl+V) or drag & drop</div><div class="ob-dropzone-sub">Click to choose a file</div></div>`;
            inputEl.insertAdjacentElement('beforebegin', dz);

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            let errEl = null;
            function setErr(msg) { if (!tile) return; if (!errEl) { errEl = document.createElement('div'); errEl.className = 'text-danger small mt-2'; tile.appendChild(errEl); } errEl.textContent = msg; errEl.style.display = 'block'; }
            function clearErr() { if (errEl) errEl.style.display = 'none'; }

            function clearPreview() { ui.wrap.style.display = 'none'; ui.img.src = ''; ui.filename.textContent = ''; dz.style.display = 'flex'; }
            function showPreview(file) {
                if (!file) return clearPreview();
                ui.wrap.style.display = 'block'; ui.filename.textContent = file.name || 'image';
                const url = URL.createObjectURL(file); ui.img.onload = () => URL.revokeObjectURL(url); ui.img.src = url;
                dz.style.display = 'none';
            }

            [inputEl, tile, dz].forEach(el => {
                if (!el) return;
                el.addEventListener('focus', () => lastFocusedFileInput = inputEl);
                el.addEventListener('mouseenter', () => lastFocusedFileInput = inputEl);
                el.addEventListener('click', () => lastFocusedFileInput = inputEl);
            });
            dz.addEventListener('click', () => { lastFocusedFileInput = inputEl; inputEl.click(); });

            ['dragenter','dragover'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dz.classList.add('is-dragover'); lastFocusedFileInput = inputEl; }));
            ['dragleave','dragend','drop'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dz.classList.remove('is-dragover'); }));
            dz.addEventListener('drop', e => { const f = Array.from(e.dataTransfer?.files || []).find(f => f.type?.startsWith('image/')) || Array.from(e.dataTransfer?.files || [])[0]; if (f) setFileOnInput(inputEl, f); });
            dz.addEventListener('paste', e => { const f = extractImageFile(e); if (f) { e.preventDefault(); setFileOnInput(inputEl, f); } });
            tile?.addEventListener('paste', e => { const f = extractImageFile(e); if (f) { e.preventDefault(); setFileOnInput(inputEl, f); } });

            inputEl.addEventListener('change', () => {
                const file = inputEl.files?.[0] || null;
                if (file && !allowedTypes.includes((file.type || '').toLowerCase())) {
                    inputEl.value = ''; clearPreview(); setErr('Invalid format. JPG/PNG/WEBP only.');
                    create_toast('danger', 'Error', 'Invalid file format. JPG/PNG/WEBP only.'); return;
                }
                clearErr(); showPreview(file);
            });
            ui.btn.addEventListener('click', () => { inputEl.value = ''; clearPreview(); inputEl.dispatchEvent(new Event('change', { bubbles: true })); });
        }

        document.querySelectorAll('#step2 input[type="file"]').forEach(enhanceFileInput);
        document.addEventListener('paste', e => { if (!lastFocusedFileInput) return; const f = extractImageFile(e); if (f) { e.preventDefault(); setFileOnInput(lastFocusedFileInput, f); } });

        // ── HS Step Form ──
        new HSStepForm('.js-step-form', {
            preventNextStep: function ($el, settings) {
                const cur = getCurrentStep();
                if (cur) setAside(cur.id);

                // Allow going back freely
                try {
                    const triggerEl = document.activeElement;
                    const optsAttr = triggerEl?.getAttribute('data-hs-step-form-prev-options');
                    if (optsAttr) return Promise.resolve();
                } catch(e) {}

                if (cur?.id === 'step1') {
                    return new Promise(async (resolve, reject) => {
                        clearEmailError();
                        if (!validateStep1(true)) { reject(); return; }
                        const email = document.querySelector('#step1 input[name="email"]')?.value?.trim() || '';
                        const check = await checkEmailExists(email);
                        if (check.exists) { showEmailError(); create_toast('danger', 'Error', 'A seller with this email already exists.'); reject(); return; }
                        resolve();
                    });
                }

                if (cur?.id === 'step2') {
                    return new Promise((resolve, reject) => {
                        if (!validateStep2Uploads(true)) { reject(); return; }
                        const $form = $('.js-step-form').first();
                        const formData = new FormData($form[0]);
                        $.ajax({
                            type: 'post', url: '<?= AJAX_URL ?>', data: formData,
                            dataType: 'text', cache: false, processData: false, contentType: false,
                            beforeSend: () => { $form.find('button').prop('disabled', true); },
                            error: (xhr, textStatus, errorThrown) => {
                                $form.find('button').prop('disabled', false);

                                const getRealErrorMessage = () => {
                                    const rawResponse = String(xhr.responseText || '').trim();

                                    if (rawResponse) {
                                        try {
                                            const parsed = JSON.parse(rawResponse);
                                            const serverMessage = parsed?.sendToast?.message || parsed?.message || parsed?.error;
                                            if (serverMessage) return String(serverMessage);
                                        } catch (e) {
                                            const tmp = document.createElement('div');
                                            tmp.innerHTML = rawResponse;
                                            const plainResponse = (tmp.textContent || tmp.innerText || '')
                                                .replace(/\s+/g, ' ')
                                                .trim();
                                            if (plainResponse) return plainResponse.slice(0, 1000);
                                        }
                                    }

                                    if (xhr.status === 413) return 'The combined upload is too large. Each image may be up to 8 MB. The server post_max_size must allow all three images together.';
                                    if (xhr.status === 403) return 'The request was blocked with HTTP 403. Please reload the page and try again. If it continues, the security or Cloudflare rule is blocking the upload.';
                                    if (xhr.status === 429) return 'Too many requests. Please wait briefly and try again.';
                                    if (xhr.status === 500) return 'The server returned HTTP 500 while processing the application.';
                                    if (xhr.status === 502) return 'The server returned HTTP 502 Bad Gateway.';
                                    if (xhr.status === 503) return 'The server is temporarily unavailable, HTTP 503.';
                                    if (xhr.status === 504) return 'The upload timed out, HTTP 504 Gateway Timeout.';
                                    if (textStatus === 'timeout') return 'The upload request timed out.';
                                    if (xhr.status === 0) return 'The request could not reach the server. Check the internet connection or security challenge and try again.';

                                    return errorThrown || xhr.statusText || `Request failed with HTTP ${xhr.status || 'unknown'}.`;
                                };

                                const message = getRealErrorMessage();
                                console.error('Seller onboarding upload failed', {
                                    status: xhr.status,
                                    statusText: xhr.statusText,
                                    textStatus,
                                    errorThrown,
                                    response: xhr.responseText
                                });
                                create_toast('danger', `Error ${xhr.status || ''}`.trim(), message);
                                reject();
                            },
                            success: (response) => {
                                $form.find('button').prop('disabled', false);
                                let res;
                                try {
                                    res = JSON.parse(response);
                                } catch(e) {
                                    const tmp = document.createElement('div');
                                    tmp.innerHTML = String(response || '');
                                    const realResponse = (tmp.textContent || tmp.innerText || '')
                                        .replace(/\s+/g, ' ')
                                        .trim();
                                    create_toast('danger', 'Invalid server response', realResponse || 'The server returned an empty or invalid response.');
                                    console.error('Invalid seller onboarding response', response, e);
                                    reject();
                                    return;
                                }
                                // Only show toast if it's an error (don't show success toast - just advance)
                                if (res && res.sendToast && res.sendToast.type === 'danger') {
                                    ajax_response_handler(res); reject(); return;
                                }
                                if (res && res.playSound === 'error') { reject(); return; }
                                step2Submitted = true; resolve();
                            }
                        });
                    });
                }

                return Promise.resolve();
            },
            finish($el) {}
        });

        setAside('step1');

        new TomSelect('#country', { search: true, placeholder: 'Select a country', maxHeight: '220px' });

        $('#dob').daterangepicker({ singleDatePicker: true, showDropdowns: true, autoUpdateInput: false, locale: { format: 'DD-MM-YYYY', cancelLabel: 'Clear' } });
        $('#dob').on('apply.daterangepicker', function(ev, picker) { $(this).val(picker.startDate.format('DD-MM-YYYY')).trigger('input'); });
        $('#dob').on('cancel.daterangepicker', function() { $(this).val('').trigger('input'); });
    })();
    </script>
</body>
</html>
