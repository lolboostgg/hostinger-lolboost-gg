<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Onboarding | LoLBoost.gg</title>
    <meta name="description" content="LoLBoost.gg is a League of Legends boosting service that offers a wide range of services to help players improve their skills and climb the ranks.">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">

    <!-- CSS Implementing Plugins -->
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">

    <!-- CSS Front Template -->
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme.min.css?v" data-hs-appearance="light" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="dark" as="style">
    <link rel="preload" href="<?= ASSET_URL ?>/origin/dash/css/theme-dark.min.css?v" data-hs-appearance="default" as="style">

    <link rel="stylesheet" href="<?= ASSET_URL ?>/core/dash/css/main.css?v<?= rand(0, 34534) ?>">

    <style data-hs-appearance-onload-styles>
        * { transition: unset !important; }
        body { opacity: 0; }
    </style>

    <style>
        /*
          Premium onboarding (completely new structure):
          - Centered container
          - Glass aside with progress + checklist
          - Card-based step content
          - Dark dropdowns
        */

        :root {
            --ob-card-bg: rgba(14, 16, 20, 0.72);
            --ob-border: rgba(255, 255, 255, 0.08);
            --ob-soft: rgba(255, 255, 255, 0.04);
            --ob-text: rgba(255, 255, 255, 0.88);
            --ob-muted: rgba(255, 255, 255, 0.55);
        }

        body.onboarding-page {
            min-height: 100vh;
            background: radial-gradient(900px 500px at 15% 15%, rgba(67, 56, 202, 0.25), transparent 60%),
                radial-gradient(900px 500px at 85% 20%, rgba(55, 125, 255, 0.18), transparent 55%),
                radial-gradient(700px 500px at 55% 95%, rgba(124, 58, 237, 0.14), transparent 55%),
                #0b0d11;
            color: var(--ob-text);
        }

        .onboarding-bg-noise {
            pointer-events: none;
            position: fixed;
            inset: 0;
            opacity: 0.08;
            mix-blend-mode: overlay;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='220' height='220' filter='url(%23n)' opacity='.35'/%3E%3C/svg%3E");
        }

        .onboarding-header {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(12px);
            background: rgba(8, 10, 14, 0.55);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .onboarding-header .inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand img {
            height: 34px;
            width: auto;
        }

        .brand .title {
            font-weight: 700;
            letter-spacing: .01em;
            line-height: 1.1;
            font-size: 14px;
        }

        .brand .subtitle {
            font-size: 12px;
            color: var(--ob-muted);
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pill {
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(255, 255, 255, 0.03);
            color: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .onboarding-shell {
            max-width: 1240px;
            margin: 0 auto;
            padding: 22px 18px 40px;
        }

        .onboarding-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 18px;
            align-items: start;
        }

        .glass {
            background: var(--ob-card-bg);
            border: 1px solid var(--ob-border);
            border-radius: 18px;
            box-shadow: 0 22px 70px rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(12px);
        }

        .aside {
            position: sticky;
            top: 78px;
            padding: 16px;
            overflow: hidden;
        }

        .aside .progress-block {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .ring {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background:
                conic-gradient(rgba(var(--bs-primary-rgb), 0.95) var(--ring), rgba(255, 255, 255, 0.10) 0);
            display: grid;
            place-items: center;
            flex: 0 0 52px;
        }

        .ring .dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(10, 12, 16, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.10);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13px;
        }

        .aside h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }

        .aside p {
            margin: 2px 0 0;
            color: var(--ob-muted);
            font-size: 12px;
        }

        /* Stepper (re-skinned) */
        .onboarding-stepper {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        /*
          HS "step" component draws a vertical connector line by default.
          For this redesign we remove connector lines completely.
        */
        .onboarding-stepper.step {
            position: relative;
        }

        .onboarding-stepper.step::before,
        .onboarding-stepper.step::after,
        .onboarding-stepper.step .step-item::before,
        .onboarding-stepper.step .step-item::after,
        .onboarding-stepper.step .step-content-wrapper::before,
        .onboarding-stepper.step .step-content-wrapper::after,
        .onboarding-stepper.step .step-icon::before,
        .onboarding-stepper.step .step-icon::after {
            content: none !important;
            display: none !important;
        }
.onboarding-stepper .step-item {
            margin: 0 !important;
        }

        .onboarding-stepper .step-content-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            position: relative;
            z-index: 1;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.02);
            text-decoration: none;
            color: inherit;
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
        }

        .onboarding-stepper .step-content-wrapper:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.03);
        }

        .onboarding-stepper .step-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            flex: 0 0 34px;
        }

        .onboarding-stepper .step-title {
            font-weight: 700;
            font-size: 13px;
            display: block;
            line-height: 1.2;
        }

        .onboarding-stepper .step-subtitle {
            display: block;
            font-size: 12px;
            color: var(--ob-muted);
            margin-top: 2px;
        }

        .aside .mini-card {
            margin-top: 14px;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.02);
        }

        .checklist {
            margin: 0;
            padding-left: 18px;
            color: rgba(255, 255, 255, 0.80);
            font-size: 12px;
        }

        .checklist li { margin: 6px 0; }

        .content {
            padding: 0;
        }

        .content .card-shell {
            padding: 18px;
        }

        .step-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .step-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .01em;
        }

        .step-header p {
            margin: 6px 0 0;
            color: var(--ob-muted);
            max-width: 62ch;
        }

        .badge-soft {
            background: rgba(var(--bs-primary-rgb), 0.16);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.22);
            color: rgba(255, 255, 255, 0.92);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            white-space: nowrap;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .feature {
            padding: 14px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .feature .ico {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 16px;
            flex: 0 0 40px;
        }

        .feature .t {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .feature .d {
            font-size: 12px;
            color: var(--ob-muted);
            line-height: 1.4;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.07);
            margin: 14px 0;
        }

        .onboarding-form .form-label {
            font-weight: 700;
            letter-spacing: .01em;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: .5rem;
        }

        .onboarding-form .form-control,
        .onboarding-form .form-select {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: rgba(255, 255, 255, 0.92);
            border-radius: 14px;
            min-height: 46px;
        }

        .onboarding-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .onboarding-form .form-control:focus,
        .onboarding-form .form-select:focus {
            border-color: rgba(var(--bs-primary-rgb), 0.65);
            box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), 0.15);
            background: rgba(255, 255, 255, 0.03);
        }

        .cta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 16px;
        }

        .btn {
            border-radius: 14px;
        }

        .btn-primary {
            font-weight: 700;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.10);
            color: rgba(255, 255, 255, 0.86);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.92);
        }

        /* Upload tiles */
        .upload-tile {
            position: relative;
            padding: 14px;
            border-radius: 16px;
            border: 1px dashed rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-tile .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .upload-tile .name {
            font-weight: 800;
            font-size: 13px;
        }

        .upload-tile .hint {
            color: var(--ob-muted);
            font-size: 12px;
        }

        .upload-tile input[type="file"] {
            border-radius: 14px;
        }

        .ob-file-hidden {
            position: absolute !important;
            left: -9999px !important;
            width: 1px !important;
            height: 1px !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Paste/Drop area (drag & drop + Ctrl+V) */
        .ob-dropzone {
            margin-top: 10px;
            border-radius: 14px;
            border: 1px dashed rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.02);
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            outline: none;
            min-height: 86px;
        }

        .ob-dropzone .ob-dropzone-ico {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(0, 0, 0, 0.20);
            color: rgba(255, 255, 255, 0.85);
        }

        .ob-dropzone .ob-dropzone-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .ob-dropzone .ob-dropzone-title {
            font-weight: 800;
            font-size: 12px;
        }

        .ob-dropzone .ob-dropzone-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.60);
            margin-top: 2px;
        }

        .ob-dropzone:focus {
            box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), 0.15);
            border-color: rgba(var(--bs-primary-rgb), 0.65);
        }

        .ob-dropzone.is-dragover {
            border-color: rgba(var(--bs-primary-rgb), 0.70);
            background: rgba(var(--bs-primary-rgb), 0.10);
        }

        /* Upload preview + remove */
        .ob-upload-preview {
            display: none;
            margin-top: 10px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(0, 0, 0, 0.22);
            overflow: hidden;
        }

        .ob-upload-preview img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 260px;
            object-fit: contain;
            background: rgba(0, 0, 0, 0.25);
        }

        .ob-upload-preview .ob-upload-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .ob-upload-preview .ob-upload-filename {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.70);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 75%;
        }

        /* Dark dropdowns (Tom Select + native select fallback) */
        .ts-wrapper .ts-control,
        .ts-wrapper.single .ts-control {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.10) !important;
            color: rgba(255, 255, 255, 0.92) !important;
            border-radius: 14px !important;
            min-height: 46px;
            padding: 10px 12px;
        }

        .ts-wrapper.focus .ts-control,
        .ts-wrapper.single.focus .ts-control {
            border-color: rgba(var(--bs-primary-rgb), 0.65) !important;
            box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), 0.15) !important;
        }

        .ts-control input { color: rgba(255, 255, 255, 0.92) !important; }
        .ts-wrapper .ts-control .item { color: rgba(255, 255, 255, 0.92) !important; }

        .ts-dropdown {
            background: rgba(14, 16, 20, 0.98) !important;
            border: 1px solid rgba(255, 255, 255, 0.10) !important;
            border-radius: 14px !important;
            overflow: hidden;
            box-shadow: 0 16px 55px rgba(0, 0, 0, 0.6);
        }

        .ts-dropdown .option,
        .ts-dropdown .no-results,
        .ts-dropdown .create {
            color: rgba(255, 255, 255, 0.88) !important;
            padding: 10px 12px !important;
        }

        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background: rgba(var(--bs-primary-rgb), 0.18) !important;
            color: #fff !important;
        }

        .ts-dropdown .option.selected {
            background: rgba(var(--bs-primary-rgb), 0.24) !important;
            color: #fff !important;
        }

        .onboarding-form select.form-select,
        .onboarding-form select.form-control {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: rgba(255, 255, 255, 0.92) !important;
        }

        .onboarding-form select option {
            background: #0f1115;
            color: rgba(255, 255, 255, 0.92);
        }



        /* --- Visual polish (inputs + Tom Select tags + buttons) --- */
        .onboarding-form .form-control,
        .onboarding-form .form-select {
            padding: 10px 12px;
            line-height: 1.25;
        }

        /* Softer focus ring (less "thick" than bootstrap default) */
        .onboarding-form .form-control:focus,
        .onboarding-form .form-select:focus {
            box-shadow: 0 0 0 .18rem rgba(var(--bs-primary-rgb), 0.14);
        }

        /* Consistent button height and alignment */
        .cta-row .btn {
            min-height: 44px;
            padding: 10px 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Tom Select: nicer dark "chips" for multi select */
        .ts-wrapper.multi .ts-control {
            gap: 6px;
            flex-wrap: wrap;
        }

        .ts-wrapper.multi .ts-control > .item {
            background: rgba(var(--bs-primary-rgb), 0.18) !important;
            border: 1px solid rgba(var(--bs-primary-rgb), 0.30) !important;
            color: rgba(255, 255, 255, 0.92) !important;
            border-radius: 12px !important;
            padding: 4px 8px !important;
            margin: 2px 0 !important;
            text-shadow: none;
        }

        .ts-wrapper.multi .ts-control > .item .remove {
            border-left: 1px solid rgba(255, 255, 255, 0.16);
            margin-left: 8px;
            padding-left: 8px;
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .ts-wrapper.multi .ts-control > .item .remove:hover {
            color: rgba(255, 255, 255, 0.95) !important;
        }

        /* Hide placeholder text when items exist (prevents "Select a game" looking misaligned) */
        .ts-wrapper.multi.has-items .ts-control > input::placeholder,
        .ts-wrapper.multi .ts-control.has-items > input::placeholder {
            color: transparent !important;
        }

        /* Keep caret/input aligned vertically inside Tom Select */
        .ts-control {
            align-items: center;
        }

        .ts-control input {
            line-height: 1.2 !important;
        }

        /* Datepicker (daterangepicker) dark */
        .daterangepicker {
            background: rgba(14, 16, 20, 0.98) !important;
            border: 1px solid rgba(255, 255, 255, 0.10) !important;
            border-radius: 14px !important;
            box-shadow: 0 16px 55px rgba(0, 0, 0, 0.6) !important;
        }

        .daterangepicker:before,
        .daterangepicker:after {
            display: none !important;
        }

        .daterangepicker .calendar-table {
            background: transparent !important;
            border: none !important;
        }

        .daterangepicker .calendar-table th,
        .daterangepicker .calendar-table td {
            color: rgba(255, 255, 255, 0.88) !important;
        }

        .daterangepicker td.available:hover,
        .daterangepicker th.available:hover {
            background: rgba(var(--bs-primary-rgb), 0.18) !important;
        }

        .daterangepicker td.active,
        .daterangepicker td.active:hover {
            background: rgba(var(--bs-primary-rgb), 0.35) !important;
        }

        .daterangepicker .drp-buttons {
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .daterangepicker .drp-buttons .btn {
            border-radius: 10px;
        }

        

        /* Make month/year dropdowns dark */
        .daterangepicker select.monthselect,
        .daterangepicker select.yearselect {
            background: rgba(14, 16, 20, 0.98) !important;
            color: rgba(255, 255, 255, 0.88) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 10px !important;
        }

        .daterangepicker select.monthselect:focus,
        .daterangepicker select.yearselect:focus {
            outline: none !important;
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25) !important;
        }
/* Responsive */
        @media (max-width: 991.98px) {
            .onboarding-grid { grid-template-columns: 1fr; }
            .aside { position: relative; top: 0; }
            .grid-3 { grid-template-columns: 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 575.98px) {
            .onboarding-shell { padding: 18px 12px 30px; }
            .onboarding-header .inner { padding: 14px 12px; }
            .brand img { height: 30px; }
        }
    

        /* Scroll area polish */
        .ob-scroll::-webkit-scrollbar { width: 8px; }
        .ob-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.14);
            border-radius: 999px;
        }
        .ob-scroll::-webkit-scrollbar-track { background: transparent; }

        .modal .list-group-item {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.88);
        }
        .modal .modal-content {
            background: rgba(14, 16, 20, 0.92);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .modal .modal-header {
            border-bottom: 1px solid rgba(255,255,255,0.08);
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
</head>

<body class="onboarding-page">
    <script src="<?= ASSET_URL ?>/origin/dash/js/hs.theme-appearance.js"></script>

    <div class="onboarding-bg-noise"></div>

    <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed" style="top: 75px;"></div>

    <header class="onboarding-header">
        <div class="inner">
            <div class="brand">
                <img src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png" alt="LoLBoost.gg" />
                <div class="text-truncate">
                    <div class="title">Booster Verification Onboarding</div>
                    <div class="subtitle">Secure, fast, and transparent review process</div>
                </div>
            </div>

            <div class="header-actions">
                <span class="pill"><i class="fa-solid fa-lock"></i> Encrypted upload</span>
                <a class="pill text-decoration-none" href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener"><i class="fa-solid fa-headset"></i> Support</a>
            </div>
        </div>
    </header>

    <main role="main" class="onboarding-shell">
        <form class="js-step-form" action="<?= AJAX_URL ?>" data-hs-step-form-options='{
            "progressSelector": ".js-step-progress",
            "stepsSelector": "#basicVerStepFormContent",
            "endSelector": "#basicVerStepFinishBtn"
        }'>
            <input type="hidden" name="action" value="booster_application">
            <input type="hidden" name="onboarding_token" value="<?= htmlspecialchars((string)($onboarding_token ?? ''), ENT_QUOTES) ?>">

            <div class="onboarding-grid">
                <aside class="glass aside">
                    <div class="progress-block">
                        <div class="ring" id="obRing" style="--ring: 25%;">
                            <div class="dot" id="obStepDot">1/4</div>
                        </div>
                        <div>
                            <h5 id="obStepTitle">Step 1: Guidelines</h5>
                            <p id="obStepDesc">Read & accept the rules before you continue.</p>
                        </div>
                    </div>

                    <ul class="js-step-progress onboarding-stepper step mb-0" id="basicVerStepFormProgressPremium">
                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#step1"}'>
                                <span class="step-icon">1</span>
                                <span class="step-content">
                                    <span class="step-title">Guidelines</span>
                                    <span class="step-subtitle">Rules & fines</span>
                                </span>
                            </a>
                        </li>

                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#step2"}'>
                                <span class="step-icon">2</span>
                                <span class="step-content">
                                    <span class="step-title">Profile</span>
                                    <span class="step-subtitle">Personal details</span>
                                </span>
                            </a>
                        </li>

                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#step3"}'>
                                <span class="step-icon">3</span>
                                <span class="step-content">
                                    <span class="step-title">Documents</span>
                                    <span class="step-subtitle">ID & selfie upload</span>
                                </span>
                            </a>
                        </li>

                        <li class="step-item">
                            <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector":"#step4"}'>
                                <span class="step-icon">4</span>
                                <span class="step-content">
                                    <span class="step-title">Finish</span>
                                    <span class="step-subtitle">Submit & done</span>
                                </span>
                            </a>
                        </li>
                    </ul>

                    <div class="mini-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong style="font-size: 13px;">What you’ll need</strong>
                            <span class="badge-soft">3–5 min</span>
                        </div>
                        <div class="divider" style="margin: 10px 0;"></div>
                        <ul class="checklist">
                            <li>Government ID (front & back)</li>
                            <li>A clear selfie holding the ID</li>
                            <li>Accurate personal information</li>
                        </ul>
                    </div>

                    <div class="mini-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong style="font-size: 13px;">Privacy</strong>
                            <span class="pill" style="padding: 6px 10px;"><i class="fa-solid fa-shield"></i> Secure</span>
                        </div>
                        <p class="mb-0" style="margin-top: 8px;">Uploads are used only for verification and reviewed by our staff.</p>
                    </div>
                </aside>

                <section class="glass content">
                    <div class="card-shell" id="basicVerStepFormContent">
                        <!-- STEP 1 -->
                        <div id="step1" class="active formWizardStep">
                            <div class="step-header">
                                <div>
                                    <h2>Guidelines & Policies</h2>
                                    <p>Please read the full Rules & Fines. You must confirm before continuing.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-shield-halved me-1"></i> Required</span>
                            </div>

                            <div class="alert alert-danger mb-3" style="background: rgba(255, 77, 129, 0.14); border: 1px solid rgba(255, 77, 129, 0.28); color: rgba(255,255,255,0.92); border-radius: 14px;">
                                <strong>NEVER SHARE CUSTOMER INFO</strong> outside the Website Order Chat (Discord/PayPal).
                            </div>

                            <div class="grid-2">
                                <!-- RULES -->
                                <div class="glass p-3" style="border-radius: 16px;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div style="font-weight: 800; font-size: 14px;">Rules</div>
                                            <div style="color: rgba(255,255,255,0.55); font-size: 12px;">Complete rule list (must follow)</div>
                                        </div>
                                        <a href="javascript:void(0)" class="pill text-decoration-none" data-bs-toggle="modal" data-bs-target="#rulesModal">
                                            <i class="fa-regular fa-file-lines"></i> Open
                                        </a>
                                    </div>

                                    <div class="ob-scroll" style="max-height: 320px; overflow: auto; padding-right: 6px;">
                                        <div class="mb-3">
                                            <div style="font-weight: 800; margin-bottom: 6px;">General Rules</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> follow these rules while processing orders. Failure to do so will result in penalties.</li>
                                                <li><strong>NOT ALLOWED</strong> to share customer information outside the Website Order Chat (Discord/PayPal).</li>
                                            </ul>
                                        </div>

                                        <div class="mb-3">
                                            <div style="font-weight: 800; margin-bottom: 6px;">Order Management</div>
                                            <div style="font-weight: 700; margin: 8px 0 6px;">Solo Orders</div>
                                            <ul class="mb-2" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> start the boost within <strong>30 minutes</strong> of claiming a Solo order (if credentials are provided).</li>
                                                <li><strong>YOU MUST</strong> greet the customer in Order Chat, check the order specifics, and log in to the account to begin the boost.</li>
                                                <li><strong>YOU MUST</strong> update the customer regularly on the progress of long orders.</li>
                                                <li><strong>YOU MUST</strong> play at least <strong>7–10 games per day</strong> for Solo orders. Anything less is considered inactivity and subject to penalties.</li>
                                                <li><strong>YOU MUST</strong> continue the order within <strong>14 hours</strong> if paused. Delays beyond this timeframe are penalized.</li>
                                            </ul>

                                            <div style="font-weight: 700; margin: 10px 0 6px;">Duo Orders</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> schedule playtime with the customer through the Order Chat dashboard (not in the League Client or Discord).</li>
                                                <li><strong>NOT ALLOWED</strong> to delay scheduling or miss agreed playtimes. If conflicts arise, open a Boosting Ticket on Discord to drop the order.</li>
                                                <li><strong>YOU MUST</strong> use an account within <strong>2 divisions</strong> of the customer's rank (e.g., Diamond 4 → Emerald 2 to Diamond 2).</li>
                                                <li><strong>NOT ALLOWED</strong> to use the customer's account for Duo orders unless explicitly permitted by staff.</li>
                                            </ul>
                                        </div>

                                        <div class="mb-3">
                                            <div style="font-weight: 800; margin-bottom: 6px;">Boosting Rules</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> follow strictly to order specifics (specific role, specific champions, Solo Boost, Hidden Duo).</li>
                                                <li><strong>NOT ALLOWED</strong> to ask customers for permission to use different roles/champions.</li>
                                                <li><strong>YOU MUST</strong> achieve a <strong>70% win ratio minimum</strong> during the boost.</li>
                                                <li><strong>NOT ALLOWED</strong> to communicate with people on the customer's friend list.</li>
                                                <li><strong>NOT ALLOWED</strong> to use chat during the boost except for essential shot-calling.</li>
                                                <li><strong>YOU MUST</strong> fulfill Bonus Wins and Win Boost orders as specified:
                                                    <ul style="margin-top:6px;">
                                                        <li>+1 Bonus Win must always be delivered regardless of LP after promotion.</li>
                                                        <li>For Win Boosts, each loss requires +1 additional win.</li>
                                                    </ul>
                                                </li>
                                                <li><strong>YOU MUST</strong> follow placement match rules:
                                                    <ul style="margin-top:6px;">
                                                        <li>5 placements: 70% winrate (4W 1L); failure = full division compensation.</li>
                                                        <li>For fewer matches, replace losses with equivalent wins.</li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="mb-3">
                                            <div style="font-weight: 800; margin-bottom: 6px;">Behavior Rules</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>NOT ALLOWED</strong> to show toxic behavior toward customers, teammates, or staff.</li>
                                                <li><strong>NOT ALLOWED</strong> to add customers on any platform or accept private boosting.</li>
                                                <li><strong>YOU MUST</strong> open a Boosting Ticket if approached for private boosting.</li>
                                                <li><strong>NOT ALLOWED</strong> to message admins privately about work-related matters; use Discord Tickets.</li>
                                            </ul>
                                        </div>

                                        <div class="mb-3">
                                            <div style="font-weight: 800; margin-bottom: 6px;">Account and Performance</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> finish the boost and notify the customer upon completion. Include the Trustpilot link for feedback.</li>
                                                <li><strong>YOU MUST</strong> provide proper screenshots for each order type (Rank Boost, Win Boost, Placements).</li>
                                                <li><strong>NOT ALLOWED</strong> to abandon orders without penalty unless you have completed a specific win/loss ratio:
                                                    <ul style="margin-top:6px;">
                                                        <li>1 win / 0 losses: Paid in full, no penalty.</li>
                                                        <li>1 win / 1 loss: €0 penalty.</li>
                                                        <li>1 win / 2 losses: €20 penalty.</li>
                                                    </ul>
                                                </li>
                                                <li><strong>YOU MUST</strong> ensure acceptable performance during games:
                                                    <ul style="margin-top:6px;">
                                                        <li>Losses with a bad score require compensation games.</li>
                                                        <li>Losses with ACE or good scores do not.</li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="mb-0">
                                            <div style="font-weight: 800; margin-bottom: 6px;">Compensation and Pay</div>
                                            <ul class="mb-0" style="color: rgba(255,255,255,0.82);">
                                                <li><strong>YOU MUST</strong> fulfill additional wins for losses in Win Boosts.</li>
                                                <li><strong>YOU MUST</strong> follow these rules for promotions:
                                                    <ul style="margin-top:6px;">
                                                        <li>Bought Wins must be fulfilled normally up to Diamond 4.</li>
                                                        <li>At Diamond 4+, one win is deducted after a promotion if more than 1 win is pending.</li>
                                                    </ul>
                                                </li>
                                                <li><strong>YOU MUST</strong> provide compensation for losses during placements based on rules above.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- FINES -->
                                <div class="glass p-3" style="border-radius: 16px;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div style="font-weight: 800; font-size: 14px;">Fines</div>
                                            <div style="color: rgba(255,255,255,0.55); font-size: 12px;">Penalties (must understand)</div>
                                        </div>
                                        <a href="javascript:void(0)" class="pill text-decoration-none" data-bs-toggle="modal" data-bs-target="#finesModal">
                                            <i class="fa-solid fa-receipt"></i> Open
                                        </a>
                                    </div>

                                    <div class="ob-scroll" style="max-height: 320px; overflow: auto; padding-right: 6px;">
                                        <div class="mb-2" style="color: rgba(255,255,255,0.70); font-size: 12px;">
                                            Fines apply if you break the rules or ignore order policies.
                                        </div>

                                        <ul class="mb-0" style="color: rgba(255,255,255,0.86);">
                                            <li>Private Boosting or Coaching (including accepting tips for additional services) — <span class="badge bg-danger">Fine: 250€</span></li>
                                            <li>Using Third-Party Programs for taking orders or in-game activities — <span class="badge bg-danger">Fine: 200€</span></li>
                                            <li>Chat ban on the customer's account without admin notification — <span class="badge bg-danger">Fine: 100€</span></li>
                                            <li>Sharing contact information or communicating outside official channels — <span class="badge bg-danger">Fine: 75€</span></li>
                                            <li>Sharing orders with other boosters or platforms without admin notification — <span class="badge bg-danger">Fine: 50€</span></li>
                                            <li>Chat ban on the customer's account — <span class="badge bg-danger">Fine: 50€</span></li>
                                            <li>Failure to use a working VPN — <span class="badge bg-danger">Fine: 25€</span></li>
                                            <li>Inappropriate behavior during games or toward the customer (flaming, griefing, intentional feeding, begging for tips) — <span class="badge bg-danger">Fine: 25€</span></li>
                                            <li>Purchasing/using items from the customer's account without prior approval (Solo Boost) — <span class="badge bg-danger">Fine: 25€</span></li>
                                            <li>Chatting/messaging people on the customer's account (SoloQ Boost) — <span class="badge bg-danger">Fine: 20€</span></li>
                                            <li>Ignoring order specifics (champions, role, summoner spells, streaming, etc.) — <span class="badge bg-danger">Fine: 20€</span></li>
                                            <li>Failure to use Offline Chat Mode (Solo or DuoQ Boost) — <span class="badge bg-danger">Fine: 10€</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="grid-2" style="gap: 10px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeRulesRead">
                                    <label class="form-check-label" for="agreeRulesRead" style="color: rgba(255,255,255,0.85);">
                                        I have read the <strong>Rules</strong>.
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeFinesRead">
                                    <label class="form-check-label" for="agreeFinesRead" style="color: rgba(255,255,255,0.85);">
                                        I understand the <strong>Fines</strong>.
                                    </label>
                                </div>
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="agreeAll">
                                <label class="form-check-label" for="agreeAll" style="color: rgba(255,255,255,0.85);">
                                    I agree to follow all rules and accept the fines for violations.
                                </label>
                            </div>

                            <div class="cta-row">
                                <span class="text-muted" style="font-size: 12px;">You can’t continue until all boxes are checked.</span>
                                <button type="button" class="btn btn-primary" id="step1NextBtn" disabled data-hs-step-form-next-options='{"targetSelector":"#step2"}'>
                                    Continue <i class="fas fa-chevron-right small ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div id="step2" style="display:none;" class="formWizardStep onboarding-form">
                            <div class="step-header">
                                <div>
                                    <h2>Personal Details</h2>
                                    <p>This information is used for verification and payout compliance.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-user-check me-1"></i> Step 2</span>
                            </div>

                            <div class="grid-2">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fullname" placeholder="John Doe" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Booster Username</label>
                                    <input type="text" class="form-control" name="username" placeholder="Best Booster 123" required>
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="mb-3">
                                    <label class="form-label">Email address</label>
                                    <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                                    <div class="invalid-feedback" id="emailExistsFeedback">A booster with this email already exists.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" class="form-control" id="dob" name="dob" placeholder="DD-MM-YYYY" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Street, City, State, Zip" required>
                            </div>

                            <div class="grid-2">
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <select id="country" name="country" class="form-control" required>

                                        <option value="" selected disabled>Select a country</option>
<option value="Afghanistan">Afghanistan</option>
                                                    <option value="Albania">Albania</option>
                                                    <option value="Algeria">Algeria</option>
                                                    <option value="Andorra">Andorra</option>
                                                    <option value="Angola">Angola</option>
                                                    <option value="Antigua and Barbuda">Antigua and Barbuda
                                                    </option>
                                                    <option value="Argentina">Argentina</option>
                                                    <option value="Armenia">Armenia</option>
                                                    <option value="Australia">Australia</option>
                                                    <option value="Austria">Austria</option>
                                                    <option value="Azerbaijan">Azerbaijan</option>
                                                    <option value="Bahamas">Bahamas</option>
                                                    <option value="Bahrain">Bahrain</option>
                                                    <option value="Bangladesh">Bangladesh</option>
                                                    <option value="Barbados">Barbados</option>
                                                    <option value="Belarus">Belarus</option>
                                                    <option value="Belgium">Belgium</option>
                                                    <option value="Belize">Belize</option>
                                                    <option value="Benin">Benin</option>
                                                    <option value="Bhutan">Bhutan</option>
                                                    <option value="Bolivia">Bolivia</option>
                                                    <option value="Bosnia and Herzegovina">Bosnia and
                                                        Herzegovina
                                                    </option>
                                                    <option value="Botswana">Botswana</option>
                                                    <option value="Brazil">Brazil</option>
                                                    <option value="Brunei">Brunei</option>
                                                    <option value="Bulgaria">Bulgaria</option>
                                                    <option value="Burkina Faso">Burkina Faso</option>
                                                    <option value="Burundi">Burundi</option>
                                                    <option value="Cambodia">Cambodia</option>
                                                    <option value="Cameroon">Cameroon</option>
                                                    <option value="Canada">Canada</option>
                                                    <option value="Cape Verde">Cape Verde</option>
                                                    <option value="Central African Republic">Central African
                                                        Republic
                                                    </option>
                                                    <option value="Chad">Chad</option>
                                                    <option value="Chile">Chile</option>
                                                    <option value="China">China</option>
                                                    <option value="Colombia">Colombia</option>
                                                    <option value="Comoros">Comoros</option>
                                                    <option value="Costa Rica">Costa Rica</option>
                                                    <option value="Croatia">Croatia</option>
                                                    <option value="Cuba">Cuba</option>
                                                    <option value="Cyprus">Cyprus</option>
                                                    <option value="Czech Republic">Czech Republic</option>
                                                    <option value="Denmark">Denmark</option>
                                                    <option value="Djibouti">Djibouti</option>
                                                    <option value="Dominica">Dominica</option>
                                                    <option value="Dominican Republic">Dominican Republic
                                                    </option>
                                                    <option value="East Timor">East Timor</option>
                                                    <option value="Ecuador">Ecuador</option>
                                                    <option value="Egypt">Egypt</option>
                                                    <option value="El Salvador">El Salvador</option>
                                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                                    <option value="Eritrea">Eritrea</option>
                                                    <option value="Estonia">Estonia</option>
                                                    <option value="Eswatini">Eswatini</option>
                                                    <option value="Ethiopia">Ethiopia</option>
                                                    <option value="Fiji">Fiji</option>
                                                    <option value="Finland">Finland</option>
                                                    <option value="France">France</option>
                                                    <option value="Gabon">Gabon</option>
                                                    <option value="Gambia">Gambia</option>
                                                    <option value="Georgia">Georgia</option>
                                                    <option value="Germany">Germany</option>
                                                    <option value="Ghana">Ghana</option>
                                                    <option value="Greece">Greece</option>
                                                    <option value="Grenada">Grenada</option>
                                                    <option value="Guatemala">Guatemala</option>
                                                    <option value="Guinea">Guinea</option>
                                                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                                                    <option value="Guyana">Guyana</option>
                                                    <option value="Haiti">Haiti</option>
                                                    <option value="Honduras">Honduras</option>
                                                    <option value="Hungary">Hungary</option>
                                                    <option value="Iceland">Iceland</option>
                                                    <option value="India">India</option>
                                                    <option value="Indonesia">Indonesia</option>
                                                    <option value="Iran">Iran</option>
                                                    <option value="Iraq">Iraq</option>
                                                    <option value="Ireland">Ireland</option>
                                                    <option value="Israel">Israel</option>
                                                    <option value="Italy">Italy</option>
                                                    <option value="Jamaica">Jamaica</option>
                                                    <option value="Japan">Japan</option>
                                                    <option value="Jordan">Jordan</option>
                                                    <option value="Kazakhstan">Kazakhstan</option>
                                                    <option value="Kenya">Kenya</option>
                                                    <option value="Kiribati">Kiribati</option>
                                                    <option value="Kuwait">Kuwait</option>
                                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                                    <option value="Laos">Laos</option>
                                                    <option value="Latvia">Latvia</option>
                                                    <option value="Lebanon">Lebanon</option>
                                                    <option value="Lesotho">Lesotho</option>
                                                    <option value="Liberia">Liberia</option>
                                                    <option value="Libya">Libya</option>
                                                    <option value="Liechtenstein">Liechtenstein</option>
                                                    <option value="Lithuania">Lithuania</option>
                                                    <option value="Luxembourg">Luxembourg</option>
                                                    <option value="Madagascar">Madagascar</option>
                                                    <option value="Malawi">Malawi</option>
                                                    <option value="Malaysia">Malaysia</option>
                                                    <option value="Maldives">Maldives</option>
                                                    <option value="Mali">Mali</option>
                                                    <option value="Malta">Malta</option>
                                                    <option value="Marshall Islands">Marshall Islands</option>
                                                    <option value="Mauritania">Mauritania</option>
                                                    <option value="Mauritius">Mauritius</option>
                                                    <option value="Mexico">Mexico</option>
                                                    <option value="Micronesia">Micronesia</option>
                                                    <option value="Moldova">Moldova</option>
                                                    <option value="Monaco">Monaco</option>
                                                    <option value="Mongolia">Mongolia</option>
                                                    <option value="Montenegro">Montenegro</option>
                                                    <option value="Morocco">Morocco</option>
                                                    <option value="Mozambique">Mozambique</option>
                                                    <option value="Myanmar">Myanmar</option>
                                                    <option value="Namibia">Namibia</option>
                                                    <option value="Nauru">Nauru</option>
                                                    <option value="Nepal">Nepal</option>
                                                    <option value="Netherlands">Netherlands</option>
                                                    <option value="New Zealand">New Zealand</option>
                                                    <option value="Nicaragua">Nicaragua</option>
                                                    <option value="Niger">Niger</option>
                                                    <option value="Nigeria">Nigeria</option>
                                                    <option value="North Korea">North Korea</option>
                                                    <option value="North Macedonia">North Macedonia</option>
                                                    <option value="Norway">Norway</option>
                                                    <option value="Oman">Oman</option>
                                                    <option value="Pakistan">Pakistan</option>
                                                    <option value="Palau">Palau</option>
                                                    <option value="Palestine">Palestine</option>
                                                    <option value="Panama">Panama</option>
                                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                                    <option value="Paraguay">Paraguay</option>
                                                    <option value="Peru">Peru</option>
                                                    <option value="Philippines">Philippines</option>
                                                    <option value="Poland">Poland</option>
                                                    <option value="Portugal">Portugal</option>
                                                    <option value="Qatar">Qatar</option>
                                                    <option value="Romania">Romania</option>
                                                    <option value="Russia">Russia</option>
                                                    <option value="Rwanda">Rwanda</option>
                                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis
                                                    </option>
                                                    <option value="Saint Lucia">Saint Lucia</option>
                                                    <option value="Saint Vincent and the Grenadines">Saint
                                                        Vincent
                                                        and
                                                        the
                                                        Grenadines
                                                    </option>
                                                    <option value="Samoa">Samoa</option>
                                                    <option value="San Marino">San Marino</option>
                                                    <option value="Sao Tome and Principe">Sao Tome and Principe
                                                    </option>
                                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                                    <option value="Senegal">Senegal</option>
                                                    <option value="Serbia">Serbia</option>
                                                    <option value="Seychelles">Seychelles</option>
                                                    <option value="Sierra Leone">Sierra Leone</option>
                                                    <option value="Singapore">Singapore</option>
                                                    <option value="Slovakia">Slovakia</option>
                                                    <option value="Slovenia">Slovenia</option>
                                                    <option value="Solomon Islands">Solomon Islands</option>
                                                    <option value="Somalia">Somalia</option>
                                                    <option value="South Africa">South Africa</option>
                                                    <option value="South Korea">South Korea</option>
                                                    <option value="South Sudan">South Sudan</option>
                                                    <option value="Spain">Spain</option>
                                                    <option value="Sri Lanka">Sri Lanka</option>
                                                    <option value="Sudan">Sudan</option>
                                                    <option value="Suriname">Suriname</option>
                                                    <option value="Sweden">Sweden</option>
                                                    <option value="Switzerland">Switzerland</option>
                                                    <option value="Syria">Syria</option>
                                                    <option value="Taiwan">Taiwan</option>
                                                    <option value="Tajikistan">Tajikistan</option>
                                                    <option value="Tanzania">Tanzania</option>
                                                    <option value="Thailand">Thailand</option>
                                                    <option value="Togo">Togo</option>
                                                    <option value="Tonga">Tonga</option>
                                                    <option value="Trinidad and Tobago">Trinidad and Tobago
                                                    </option>
                                                    <option value="Tunisia">Tunisia</option>
                                                    <option value="Turkey">Turkey</option>
                                                    <option value="Turkmenistan">Turkmenistan</option>
                                                    <option value="Tuvalu">Tuvalu</option>
                                                    <option value="Uganda">Uganda</option>
                                                    <option value="Ukraine">Ukraine</option>
                                                    <option value="United Arab Emirates">United Arab Emirates
                                                    </option>
                                                    <option value="United Kingdom">United Kingdom</option>
                                                    <option value="United States">United States</option>
                                                    <option value="Uruguay">Uruguay</option>
                                                    <option value="Uzbekistan">Uzbekistan</option>
                                                    <option value="Vanuatu">Vanuatu</option>
                                                    <option value="Vatican City">Vatican City</option>
                                                    <option value="Venezuela">Venezuela</option>
                                                    <option value="Vietnam">Vietnam</option>
                                                    <option value="Yemen">Yemen</option>
                                                    <option value="Zambia">Zambia</option>
                                                    <option value="Zimbabwe">Zimbabwe</option>
</select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Games</label>
                                    <select class="form-select" id="games" name="games[]" multiple required>
                                        <?= util_load_games_select([]) ?>
                                    </select>
                                </div>
                            </div>

                            <div class="cta-row">
                                <button type="button" class="btn btn-secondary px-4" data-hs-step-form-prev-options='{"targetSelector":"#step1"}'>
                                    <i class="fas fa-chevron-left small me-1"></i> Back
                                </button>

                                <button type="button" class="btn btn-primary px-4" data-hs-step-form-next-options='{"targetSelector":"#step3"}'>
                                    Continue <i class="fas fa-chevron-right small ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div id="step3" style="display:none;" class="formWizardStep onboarding-form">
                            <div class="step-header">
                                <div>
                                    <h2>Document Upload</h2>
                                    <p>Upload clear photos. Avoid glare, blur, and cropped edges.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Step 3</span>
                            </div>

                            <div class="grid-2">
                                <div class="upload-tile">
                                    <div class="top">
                                        <div>
                                            <div class="name">ID Card (Front)</div>
                                            <div class="hint">Allowed formats: JPG, JPEG, PNG, WEBP • max. 8 MB • readable text</div>
                                        </div>
                                        <span class="pill" style="padding: 6px 10px;"><i class="fa-regular fa-id-card"></i></span>
                                    </div>
                                    <input type="file" class="form-control" name="id_card_front" accept="image/jpeg,image/png,image/webp" required />
                                </div>

                                <div class="upload-tile">
                                    <div class="top">
                                        <div>
                                            <div class="name">ID Card (Back)</div>
                                            <div class="hint">Allowed formats: JPG, JPEG, PNG, WEBP • max. 8 MB • no reflections, full frame</div>
                                        </div>
                                        <span class="pill" style="padding: 6px 10px;"><i class="fa-regular fa-id-card"></i></span>
                                    </div>
                                    <input type="file" class="form-control" name="id_card_back" accept="image/jpeg,image/png,image/webp" required />
                                </div>
                            </div>

                            <div class="upload-tile" style="margin-top:12px;">
                                <div class="top">
                                    <div>
                                        <div class="name">Selfie</div>
                                        <div class="hint">Allowed formats: JPG, JPEG, PNG, WEBP • max. 8 MB • hold your ID next to your face</div>
                                    </div>
                                    <span class="pill" style="padding: 6px 10px;"><i class="fa-solid fa-camera"></i></span>
                                </div>
                                <input type="file" class="form-control" name="selfie" accept="image/jpeg,image/png,image/webp" required />
                            </div>

                            <div class="cta-row">
                                <button type="button" class="btn btn-secondary px-4" data-hs-step-form-prev-options='{"targetSelector":"#step2"}'>
                                    <i class="fas fa-chevron-left small me-1"></i> Back
                                </button>

                                <button type="button" class="btn btn-primary px-4" data-hs-step-form-next-options='{"targetSelector":"#step4"}'>
                                    Submit <i class="fas fa-chevron-right small ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 4 -->
                        <div id="step4" style="display:none;" class="formWizardStep">
                            <div class="step-header">
                                <div>
                                    <h2>Submission Received</h2>
                                    <p>We’ve started the verification process. You’ll receive an update once reviewed.</p>
                                </div>
                                <span class="badge-soft"><i class="fa-solid fa-check me-1"></i> Done</span>
                            </div>

                            <div class="feature" style="align-items:center;">
                                <div class="ico" style="font-size:18px;"><i class="fa-solid fa-shield-check text-success"></i></div>
                                <div>
                                    <div class="t">Verification initiated</div>
                                    <div class="d">Typical review time: <strong>1–3 business days</strong>. Our team will contact you if anything is missing.</div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="feature" style="align-items:center;">
                                <div class="ico"><i class="fa-solid fa-headset"></i></div>
                                <div style="flex:1;">
                                    <div class="t">Need help?</div>
                                    <div class="d">Join our <a href="https://discord.com/channels/565193024439713792/1267451078157533318" target="_blank" rel="noopener" class="text-info">Discord Support</a></div>
                                </div>
                                <span class="pill"><i class="fa-solid fa-bolt"></i> Fast reply</span>
                            </div>
                        </div>

                        <button type="button" id="basicVerStepFinishBtn"></button>
                    </div>
                </section>
            </div>

            <!-- modals -->
            <div class="modal fade" id="rulesModal" tabindex="-1" aria-labelledby="rulesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rulesModalLabel">Rules</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger mb-3" style="background: rgba(255, 77, 129, 0.14); border: 1px solid rgba(255, 77, 129, 0.28); color: rgba(255,255,255,0.92); border-radius: 14px;">
                                <strong>NEVER SHARE CUSTOMER INFO</strong> outside the Website Order Chat (Discord/PayPal).
                            </div>

                            <h6 class="mb-2">General Rules</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">YOU MUST follow these rules while processing orders. Failure to do so will result in penalties.</li>
                                <li class="list-group-item">NOT ALLOWED to share customer information outside the Website Order Chat (Discord/PayPal).</li>
                            </ul>

                            <h6 class="mb-2">Order Management — Solo Orders</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">YOU MUST start the boost within 30 minutes of claiming a Solo order (if credentials are provided).</li>
                                <li class="list-group-item">YOU MUST greet the customer in Order Chat, check the order specifics, and log in to the account to begin the boost.</li>
                                <li class="list-group-item">YOU MUST update the customer regularly on the progress of long orders.</li>
                                <li class="list-group-item">YOU MUST play at least 7–10 games per day for Solo orders. Anything less is considered inactivity and subject to penalties.</li>
                                <li class="list-group-item">YOU MUST continue the order within 14 hours if paused. Delays beyond this timeframe are penalized.</li>
                            </ul>

                            <h6 class="mb-2">Order Management — Duo Orders</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">YOU MUST schedule playtime with the customer through the Order Chat dashboard (not in the League Client or Discord).</li>
                                <li class="list-group-item">NOT ALLOWED to delay scheduling or miss agreed playtimes. If conflicts arise, open a Boosting Ticket on Discord to drop the order.</li>
                                <li class="list-group-item">YOU MUST use an account within 2 divisions of the customer's rank (e.g., Diamond 4 → Emerald 2 to Diamond 2 accounts).</li>
                                <li class="list-group-item">NOT ALLOWED to use the customer's account for Duo orders unless explicitly permitted by staff.</li>
                            </ul>

                            <h6 class="mb-2">Boosting Rules</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">YOU MUST follow strictly to order specifics (specific role, specific champions, Solo Boost, Hidden Duo).</li>
                                <li class="list-group-item">NOT ALLOWED to ask customers for permission to use different roles/champions.</li>
                                <li class="list-group-item">YOU MUST achieve a 70% win ratio minimum during the boost.</li>
                                <li class="list-group-item">NOT ALLOWED to communicate with people on the customer's friend list.</li>
                                <li class="list-group-item">NOT ALLOWED to use chat during the boost except for essential shot-calling.</li>
                                <li class="list-group-item">YOU MUST fulfill Bonus Wins and Win Boost orders as specified:
                                    <ul class="mt-2 mb-0">
                                        <li>+1 Bonus Win must always be delivered regardless of LP after promotion.</li>
                                        <li>For Win Boosts, each loss requires +1 additional win.</li>
                                    </ul>
                                </li>
                                <li class="list-group-item">YOU MUST follow placement match rules:
                                    <ul class="mt-2 mb-0">
                                        <li>5 placements: 70% winrate (4W 1L); failure = full division compensation.</li>
                                        <li>For fewer matches, replace losses with equivalent wins.</li>
                                    </ul>
                                </li>
                            </ul>

                            <h6 class="mb-2">Behavior Rules</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">NOT ALLOWED to show toxic behavior toward customers, teammates, or staff.</li>
                                <li class="list-group-item">NOT ALLOWED to add customers on any platform or accept private boosting.</li>
                                <li class="list-group-item">YOU MUST open a Boosting Ticket if approached for private boosting.</li>
                                <li class="list-group-item">NOT ALLOWED to message admins privately about work-related matters; use Discord Tickets.</li>
                            </ul>

                            <h6 class="mb-2">Account and Performance</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item">YOU MUST finish the boost and notify the customer upon completion. Include the Trustpilot link for feedback.</li>
                                <li class="list-group-item">YOU MUST provide proper screenshots for each order type (Rank Boost, Win Boost, Placements).</li>
                                <li class="list-group-item">NOT ALLOWED to abandon orders without penalty unless you have completed a specific win/loss ratio:
                                    <ul class="mt-2 mb-0">
                                        <li>1 win / 0 losses: Paid in full, no penalty.</li>
                                        <li>1 win / 1 loss: €0 penalty.</li>
                                        <li>1 win / 2 losses: €20 penalty.</li>
                                    </ul>
                                </li>
                                <li class="list-group-item">YOU MUST ensure acceptable performance during games:
                                    <ul class="mt-2 mb-0">
                                        <li>Losses with a bad score require compensation games.</li>
                                        <li>Losses with ACE or good scores do not.</li>
                                    </ul>
                                </li>
                            </ul>

                            <h6 class="mb-2">Compensation and Pay</h6>
                            <ul class="list-group mb-0">
                                <li class="list-group-item">YOU MUST fulfill additional wins for losses in Win Boosts.</li>
                                <li class="list-group-item">YOU MUST follow these rules for promotions:
                                    <ul class="mt-2 mb-0">
                                        <li>Bought Wins must be fulfilled normally up to Diamond 4.</li>
                                        <li>At Diamond 4+, one win is deducted after a promotion if more than 1 win is pending.</li>
                                    </ul>
                                </li>
                                <li class="list-group-item">YOU MUST provide compensation for losses during placements based on rules above.</li>
                            </ul>
</div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="finesModal" tabindex="-1" aria-labelledby="finesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="finesModalLabel">Fines</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2" style="color: rgba(255,255,255,0.70); font-size: 13px;">
                                Fines apply if you break the rules or ignore order policies.
                            </div>

                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Private Boosting or Coaching (including accepting tips for additional services)</span>
                                    <span class="badge bg-danger">Fine: 250€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Using Third-Party Programs for taking orders or in-game activities</span>
                                    <span class="badge bg-danger">Fine: 200€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Chat ban on the customer's account without admin notification</span>
                                    <span class="badge bg-danger">Fine: 100€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Sharing contact information or communicating outside official channels</span>
                                    <span class="badge bg-danger">Fine: 75€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Sharing orders with other boosters or platforms without admin notification</span>
                                    <span class="badge bg-danger">Fine: 50€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Chat ban on the customer's account</span>
                                    <span class="badge bg-danger">Fine: 50€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Failure to use a working VPN</span>
                                    <span class="badge bg-danger">Fine: 25€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Inappropriate behavior during games or toward the customer (flaming, griefing, intentional feeding, begging for tips)</span>
                                    <span class="badge bg-danger">Fine: 25€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Purchasing/using items from the customer's account without prior approval (Solo Boost)</span>
                                    <span class="badge bg-danger">Fine: 25€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Chatting/messaging people on the customer's account (SoloQ Boost)</span>
                                    <span class="badge bg-danger">Fine: 20€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Ignoring order specifics (champions, role, summoner spells, streaming, etc.)</span>
                                    <span class="badge bg-danger">Fine: 20€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Failure to use Offline Chat Mode (Solo or DuoQ Boost)</span>
                                    <span class="badge bg-danger">Fine: 10€</span>
                                </li>
                            </ul>
</div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- JS Implementing Plugins -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-toggle-password/dist/js/hs-toggle-password.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-step-form/dist/hs-step-form.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>

    <!-- JS Front -->
    <script src="<?= ASSET_URL ?>/origin/dash/js/theme.min.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/main.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js"></script>

    <script>
        (function () {
            function setAside(step) {
                const ring = document.getElementById('obRing');
                const dot = document.getElementById('obStepDot');
                const title = document.getElementById('obStepTitle');
                const desc = document.getElementById('obStepDesc');

                const map = {
                    step1: { p: 25, t: 'Step 1: Guidelines', d: 'Read & accept the rules before you continue.' },
                    step2: { p: 50, t: 'Step 2: Profile', d: 'Fill in your personal details.' },
                    step3: { p: 75, t: 'Step 3: Documents', d: 'Upload ID photos and a selfie.' },
                    step4: { p: 100, t: 'Step 4: Finish', d: 'Submission completed.' },
                };

                const cfg = map[step] || map.step1;
                ring.style.setProperty('--ring', cfg.p + '%');
                dot.textContent = (step.replace('step','')) + '/4';
                title.textContent = cfg.t;
                desc.textContent = cfg.d;
            }
            // Agree gating (Step 1)
            const agreeRulesReadEl = document.getElementById('agreeRulesRead');
            const agreeFinesReadEl = document.getElementById('agreeFinesRead');
            const agreeAllEl = document.getElementById('agreeAll');
            const step1NextBtn = document.getElementById('step1NextBtn');

            function syncAgreeGate() {
                if (!step1NextBtn) return;
                const ok =
                    !!agreeRulesReadEl?.checked &&
                    !!agreeFinesReadEl?.checked &&
                    !!agreeAllEl?.checked;

                step1NextBtn.disabled = !ok;
            }

            [agreeRulesReadEl, agreeFinesReadEl, agreeAllEl].forEach((el) => {
                if (!el) return;
                el.addEventListener('change', syncAgreeGate);
                el.addEventListener('input', syncAgreeGate);
                el.addEventListener('click', syncAgreeGate);
            });

            if (window.jQuery) {
                jQuery('#agreeRulesRead, #agreeFinesRead, #agreeAll').on('change input', syncAgreeGate);
            }

            // Set initial state on load
            syncAgreeGate();

            // ------------------------------------------------------------
            // Step gating (no skipping + validate before moving forward)
            // ------------------------------------------------------------
            const stepOrder = ['step1', 'step2', 'step3', 'step4'];
            let step3Submitted = false;

            function getStepEl(stepId) {
                return document.getElementById(stepId);
            }

            function validateStep1(showToast = true) {
                const ok =
                    !!agreeRulesReadEl?.checked &&
                    !!agreeFinesReadEl?.checked &&
                    !!agreeAllEl?.checked;
                if (!ok && showToast) {
                    create_toast('danger', 'Error', 'Please accept all checkboxes to continue.');
                }
                return ok;
            }

                        function clearEmailExistsError() {
                const emailEl = document.querySelector('#step2 input[name="email"]');
                const fb = document.getElementById('emailExistsFeedback');
                if (emailEl) {
                    emailEl.setCustomValidity('');
                    emailEl.classList.remove('is-invalid');
                }
                if (fb) fb.style.display = 'none';
            }

            function showEmailExistsError() {
                const emailEl = document.querySelector('#step2 input[name="email"]');
                const fb = document.getElementById('emailExistsFeedback');
                if (emailEl) {
                    emailEl.setCustomValidity('Email already exists');
                    emailEl.classList.add('is-invalid');
                }
                if (fb) fb.style.display = 'block';
            }

            function checkBoosterEmailExists(email) {
                return new Promise((resolve) => {
                    if (!email) return resolve({ exists: false });
                    $.ajax({
                        type: 'post',
                        url: '<?= AJAX_URL ?>',
                        data: { action: 'booster_email_exists', email },
                        dataType: 'json',
                        timeout: 7000,
                        success: function (res) {
                            // Expected: { exists: true/false }
                            resolve({ exists: !!(res && res.exists) });
                        },
                        error: function () {
                            // Don't block user if endpoint isn't available
                            resolve({ exists: false, error: true });
                        }
                    });
                });
            }

function validateStep2(showToast = true, manageRequired = true) {
                const step2 = getStepEl('step2');
                if (!step2) return true;

                // Ensure required attributes are active for this step while validating
                if (manageRequired) {
                    enableRequiredFields(step2);
                }


            


                // Validate only step2 controls
                const controls = Array.from(step2.querySelectorAll('input, select, textarea'))
                    .filter((el) => el.type !== 'hidden' && !el.disabled);

                const ok = controls.every((el) => el.checkValidity());

                if (!ok) {
                    document.querySelector('form')?.classList.add('was-validated');
                    if (showToast) {
                        create_toast('danger', 'Error', 'Please fill out all required fields correctly.');
                    }

                    // Trigger browser validation UI for the first invalid control
                    const firstInvalid = controls.find((el) => !el.checkValidity());
                    if (showToast) {
                        firstInvalid?.reportValidity?.();
                    }
                }

                return ok;
            }

            function validateStep3Uploads(showToast = true) {
                const step3 = getStepEl('step3');
                if (!step3) return true;

                const inputs = Array.from(step3.querySelectorAll('input[type="file"][name]'));
                const ok = inputs.every((i) => i.files && i.files.length > 0);
                if (!ok && showToast) {
                    create_toast('danger', 'Error', 'Please upload all required images to continue.');
                }
                return ok;
            }

            function allowedMaxStepIndex() {
                // You can always be on step1
                if (!validateStep1(false)) return 0;
                // After step1 is ok, allow step2
                if (!validateStep2(false, false)) return 1;
                // After step2 is valid, allow step3
                if (!validateStep3Uploads(false)) return 2;
                // Step4 only after successful submit
                if (!step3Submitted) return 2;
                return 3;
            }

            // Disable forward navigation buttons until the step is valid (UX)
            const step2NextBtn = document.querySelector('#step2 [data-hs-step-form-next-options*="#step3"]');
            const step3NextBtn = document.querySelector('#step3 [data-hs-step-form-next-options*="#step4"]');

            if (step2NextBtn) step2NextBtn.id = 'step2NextBtn';
            if (step3NextBtn) step3NextBtn.id = 'step3SubmitBtn';

            function syncStep2Gate() {
                if (!step2NextBtn) return;
                // Only validate lightly (no toast) while typing
                step2NextBtn.disabled = !validateStep2(false, false);
            }

            function syncStep3Gate() {
                if (!step3NextBtn) return;
                step3NextBtn.disabled = !validateStep3Uploads(false);
            }

            // Watch step2 fields for validity
            const step2El = getStepEl('step2');
            if (step2El) {
                step2El.addEventListener('input', (e) => {
                    if (e && e.target && e.target.name === 'email') {
                        clearEmailExistsError();
                    }
                    setTimeout(syncStep2Gate, 0);
                }, true);
                step2El.addEventListener('change', () => setTimeout(syncStep2Gate, 0), true);
            }

            // Watch step3 uploads
            const step3El = getStepEl('step3');
            if (step3El) {
                step3El.addEventListener('change', () => setTimeout(syncStep3Gate, 0), true);
            }

            // Initial gate state
            syncStep2Gate();
            syncStep3Gate();

// Prevent hidden required fields from blocking step navigation
            function disableRequiredFields(stepEl) {
                if (!stepEl) return;
                stepEl.querySelectorAll('input, select, textarea').forEach((el) => {
                    // Skip hidden inputs (e.g., action field)
                    if (el.type === 'hidden') return;

                    if (el.hasAttribute('required')) {
                        el.dataset.obRequired = '1';
                        el.removeAttribute('required');
                    }
                });
            }

            function enableRequiredFields(stepEl) {
                if (!stepEl) return;
                stepEl.querySelectorAll('input, select, textarea').forEach((el) => {
                    if (el.dataset.obRequired === '1') {
                        el.setAttribute('required', 'required');
                    }
                });
            }

                        let lastActiveStepId = null;

            function isStepVisible(el) {
                if (!el) return false;
                const cs = window.getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden') return false;
                // offsetParent is null for display:none (and some fixed elements), so also check display above.
                return true;
            }

            function getCurrentStep(steps) {
                // In your original onboarding, HS toggles steps via style="display:none"
                // so we treat the first visible step as the active one.
                return Array.from(steps).find(isStepVisible) || steps[0] || null;
            }

            function syncActiveStepState() {
                const steps = document.querySelectorAll('#basicVerStepFormContent .formWizardStep');
                const current = getCurrentStep(steps);

                if (!current) return;

                if (current.id !== lastActiveStepId) {
                    steps.forEach((s) => {
                        if (s === current) {
                            enableRequiredFields(s);
                        } else {
                            disableRequiredFields(s);
                        }
                    });

                    setAside(current.id);
                    lastActiveStepId = current.id;
                }
            }

            // Initial: disable required on all steps except the currently visible one
            document.querySelectorAll('#basicVerStepFormContent .formWizardStep').forEach(disableRequiredFields);
            syncActiveStepState();

                        // Observe step changes (HS toggles steps via display/style changes in your original file)
            const stepsRoot = document.getElementById('basicVerStepFormContent');
            if (stepsRoot && window.MutationObserver) {
                const mo = new MutationObserver(() => {
                    // run twice to catch style writes in same tick
                    syncActiveStepState();
                    setTimeout(syncActiveStepState, 0);
                });
                mo.observe(stepsRoot, { subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
            }

            // Also sync after any explicit next/prev click (safe fallback)
            document.querySelectorAll('[data-hs-step-form-next-options], [data-hs-step-form-prev-options]').forEach((el) => {
                el.addEventListener('click', () => setTimeout(syncActiveStepState, 0));
            });

            // Prevent skipping steps via stepper clicks
            const stepper = document.getElementById('basicVerStepFormProgressPremium');
            if (stepper) {
                stepper.addEventListener('click', (e) => {
                    const link = e.target?.closest?.('[data-hs-step-form-next-options]');
                    if (!link) return;

                    let targetStepId = null;
                    try {
                        const parsed = JSON.parse(link.getAttribute('data-hs-step-form-next-options') || '{}');
                        targetStepId = (parsed.targetSelector || '').replace('#', '');
                    } catch (err) {}

                    if (!targetStepId) return;

                    const targetIdx = stepOrder.indexOf(targetStepId);
                    const maxIdx = allowedMaxStepIndex();

                    // Allow going backwards freely, but block going forward beyond what's allowed
                    if (targetIdx > maxIdx) {
                        e.preventDefault();
                        e.stopPropagation();
                        create_toast('danger', 'Error', 'Please complete the previous step(s) before continuing.');
                        return false;
                    }
                }, true);
            }

            // ------------------------------------------------------------
            // Upload UX: paste (Ctrl+V), preview, remove & replace
            // ------------------------------------------------------------
            let lastFocusedFileInput = null;

            function buildPreviewEl(inputEl) {
                const wrap = document.createElement('div');
                wrap.className = 'ob-upload-preview';

                const img = document.createElement('img');
                img.alt = 'Preview';

                const actions = document.createElement('div');
                actions.className = 'ob-upload-actions';

                const filename = document.createElement('div');
                filename.className = 'ob-upload-filename';
                filename.textContent = '';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-danger';
                btn.innerHTML = '<i class="fa-solid fa-trash"></i> Remove';

                actions.appendChild(filename);
                actions.appendChild(btn);
                wrap.appendChild(img);
                wrap.appendChild(actions);

                inputEl.insertAdjacentElement('afterend', wrap);

                return { wrap, img, filename, btn };
            }

            function setFileOnInput(inputEl, file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                inputEl.files = dt.files;
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function extractImageFileFromClipboard(e) {
                const items = Array.from(e.clipboardData?.items || []);
                const imageItem = items.find((it) => it.type && it.type.startsWith('image/'));
                if (!imageItem) return null;
                const blob = imageItem.getAsFile();
                if (!blob) return null;

                const ext = (blob.type || 'image/png').split('/')[1] || 'png';
                const named = new File([blob], `pasted_${Date.now()}.${ext}`, { type: blob.type || 'image/png' });
                return named;
            }

            function enhanceFileInput(inputEl) {
                if (!inputEl || inputEl.dataset.obEnhanced === '1') return;
                inputEl.dataset.obEnhanced = '1';

                // Hide the native file input (we use a nicer drop/paste area),
                // but keep it in the DOM for form submission + validation.
                inputEl.classList.add('ob-file-hidden');

                const tile = inputEl.closest('.upload-tile');
                if (tile) {
                    tile.tabIndex = 0; // allow focus
                    tile.setAttribute('role', 'group');
                    tile.setAttribute('aria-label', 'Upload area');
                }

                const ui = buildPreviewEl(inputEl);

                // Build an explicit drop/paste area so users know they can paste/drag.
                const dz = document.createElement('div');
                dz.className = 'ob-dropzone';
                dz.tabIndex = 0;
                dz.setAttribute('role', 'button');
                dz.setAttribute('aria-label', 'Paste or drag an image');
                dz.innerHTML = `
                    <div class="ob-dropzone-ico"><i class="fa-regular fa-clipboard"></i></div>
                    <div class="ob-dropzone-text">
                        <div class="ob-dropzone-title">Paste (Ctrl+V) or drag & drop</div>
                        <div class="ob-dropzone-sub">Click to choose a file</div>
                    </div>
                `;
                inputEl.insertAdjacentElement('beforebegin', dz);

                function clearPreview() {
                    ui.wrap.style.display = 'none';
                    ui.img.src = '';
                    ui.filename.textContent = '';
                    dz.style.display = 'flex';
                }

                function showPreview(file) {
                    if (!file) return clearPreview();
                    ui.wrap.style.display = 'block';
                    ui.filename.textContent = file.name || 'image';
                    const url = URL.createObjectURL(file);
                    ui.img.onload = () => URL.revokeObjectURL(url);
                    ui.img.src = url;
                    dz.style.display = 'none';
                }

                // initial
                if (inputEl.files && inputEl.files[0]) showPreview(inputEl.files[0]);

                inputEl.addEventListener('focus', () => { lastFocusedFileInput = inputEl; });
                inputEl.addEventListener('click', () => { lastFocusedFileInput = inputEl; });
                tile?.addEventListener('click', () => { lastFocusedFileInput = inputEl; });
                tile?.addEventListener('focus', () => { lastFocusedFileInput = inputEl; });
                tile?.addEventListener('mouseenter', () => { lastFocusedFileInput = inputEl; });
                dz.addEventListener('mouseenter', () => { lastFocusedFileInput = inputEl; });

                // Dropzone interactions
                dz.addEventListener('click', () => {
                    lastFocusedFileInput = inputEl;
                    inputEl.click();
                });
                dz.addEventListener('focus', () => { lastFocusedFileInput = inputEl; });

                const prevent = (e) => { e.preventDefault(); e.stopPropagation(); };
                ['dragenter', 'dragover'].forEach((evt) => dz.addEventListener(evt, (e) => {
                    prevent(e);
                    dz.classList.add('is-dragover');
                    lastFocusedFileInput = inputEl;
                }));
                ['dragleave', 'dragend', 'drop'].forEach((evt) => dz.addEventListener(evt, (e) => {
                    prevent(e);
                    dz.classList.remove('is-dragover');
                }));

                dz.addEventListener('drop', (e) => {
                    const files = Array.from(e.dataTransfer?.files || []);
                    const img = files.find((f) => (f.type || '').startsWith('image/')) || files[0];
                    if (!img) return;
                    setFileOnInput(inputEl, img);
                });

                // Paste directly on dropzone
                dz.addEventListener('paste', (e) => {
                    const file = extractImageFileFromClipboard(e);
                    if (!file) return;
                    e.preventDefault();
                    setFileOnInput(inputEl, file);
                });

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                // Inline error below the tile (created on demand)
                let errEl = tile ? tile.querySelector('.ob-upload-error') : null;
                function setUploadError(msg) {
                    if (!tile) return;
                    if (!errEl) {
                        errEl = document.createElement('div');
                        errEl.className = 'ob-upload-error text-danger small mt-2';
                        tile.appendChild(errEl);
                    }
                    errEl.textContent = msg;
                    errEl.style.display = 'block';
                }
                function clearUploadError() {
                    if (errEl) errEl.style.display = 'none';
                }

                inputEl.addEventListener('change', () => {
                    const file = inputEl.files && inputEl.files[0] ? inputEl.files[0] : null;

                    // Validate file type immediately (block svg/pdf/etc.)
                    if (file && !allowedTypes.includes((file.type || '').toLowerCase())) {
                        inputEl.value = '';
                        clearPreview();
                        setUploadError('Invalid file format. Allowed: JPG, JPEG, PNG, WEBP.');
                        create_toast('danger', 'Error', 'Invalid file format. Allowed: JPG, JPEG, PNG, WEBP.');
                        syncStep3Gate();
                        return;
                    }

                    if (file && file.size > 8 * 1024 * 1024) {
                        inputEl.value = '';
                        clearPreview();
                        setUploadError('This image is too large. Maximum size: 8 MB per image.');
                        create_toast('danger', 'Error', 'This image is too large. Maximum size: 8 MB per image.');
                        syncStep3Gate();
                        return;
                    }

                    clearUploadError();
                    showPreview(file);
                    syncStep3Gate();
                });

                ui.btn.addEventListener('click', () => {
                    inputEl.value = '';
                    clearPreview();
                    // keep required gating accurate
                    inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                });

                // Paste directly on tile (fallback)
                tile?.addEventListener('paste', (e) => {
                    const file = extractImageFileFromClipboard(e);
                    if (!file) return;
                    e.preventDefault();
                    setFileOnInput(inputEl, file);
                });
            }

            // Enhance all file inputs on step3
            document.querySelectorAll('#step3 input[type="file"]').forEach(enhanceFileInput);

            // Global paste: paste to the last focused upload input
            document.addEventListener('paste', (e) => {
                if (!lastFocusedFileInput) return;
                const file = extractImageFileFromClipboard(e);
                if (!file) return;
                e.preventDefault();
                setFileOnInput(lastFocusedFileInput, file);
            });

            // Step form
            new HSStepForm('.js-step-form', {
                preventNextStep: function ($el, settings) {
                    const steps = $el.querySelectorAll('#basicVerStepFormContent .formWizardStep');
                    const currentStep = getCurrentStep(steps);

                    if (currentStep) {
                        setAside(currentStep.id);
                    }

                    // Allow navigating BACK to completed steps from sidebar (don't validate current step)
                    try {
                        const triggerEl = document.activeElement;
                        const optsAttr = triggerEl?.getAttribute('data-hs-step-form-next-options') || triggerEl?.getAttribute('data-hs-step-form-prev-options');
                        if (optsAttr && currentStep) {
                            const opts = JSON.parse(optsAttr);
                            const targetSelector = opts?.targetSelector;
                            if (targetSelector) {
                                const targetStepEl = document.querySelector(targetSelector);
                                if (targetStepEl) {
                                    const stepsArr = Array.from(steps);
                                    const curIdx = stepsArr.findIndex(s => s.id === currentStep.id);
                                    const tgtIdx = stepsArr.findIndex(s => s.id === targetStepEl.id);
                                    if (curIdx !== -1 && tgtIdx !== -1 && tgtIdx < curIdx) {
                                        return Promise.resolve();
                                    }
                                }
                            }
                        }
                    } catch (e) {}

                    // Step 1 validation
                    if (currentStep && currentStep.id === 'step1') {
                        return new Promise((resolve, reject) => {
                            if (!validateStep1(true)) {
                                reject();
                                return;
                            }
                            resolve();
                        });
                    }

                    // Step 2 validation
                    if (currentStep && currentStep.id === 'step2') {
                        return new Promise(async (resolve, reject) => {
                            clearEmailExistsError();

                            if (!validateStep2(true)) {
                                reject();
                                return;
                            }

                            // Check if booster already exists for this email before continuing
                            const emailEl = document.querySelector('#step2 input[name="email"]');
                            const email = (emailEl?.value || '').trim();

                            const check = await checkBoosterEmailExists(email);
                            if (check && check.exists) {
                                showEmailExistsError();
                                create_toast('danger', 'Error', 'A booster with this email already exists.');
                                reject();
                                return;
                            }

                            resolve();
                        });
                    }

                    // Step 3 (submit) validation + AJAX
                    if (currentStep && currentStep.id === 'step3') {
                        return new Promise((resolve, reject) => {
                            const $form = $('form');

                            if ($form[0].checkValidity() === false) {
                                $form.addClass('was-validated');
                                create_toast('danger', 'Error', 'Please fill out all required fields correctly.');
                                reject();
                                return;
                            }

                            const formData = new FormData($form[0]);

                            $.ajax({
                                type: 'post',
                                url: '<?= AJAX_URL ?>',
                                data: formData,
                                dataType: 'text',
                                cache: false,
                                processData: false,
                                contentType: false,
                                beforeSend: function () {
                                    $form.find('button[type="submit"]').attr('data-indicator', 'on');
                                    $form.find('*:not(.disabled)').prop('disabled', true);
                                },
                                error: function (xhr, textStatus, errorThrown) {
                                    $form.find('button[type="submit"]').removeAttr('data-indicator');
                                    $form.find('*:not(.disabled)').prop('disabled', false);

                                    const raw = String(xhr.responseText || '').trim();
                                    let message = '';
                                    if (raw) {
                                        try {
                                            const parsed = JSON.parse(raw);
                                            message = parsed?.sendToast?.message || parsed?.message || parsed?.error || '';
                                        } catch (e) {
                                            const tmp = document.createElement('div');
                                            tmp.innerHTML = raw;
                                            message = (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 1000);
                                        }
                                    }
                                    if (!message) {
                                        if (xhr.status === 413) message = 'The combined upload is too large. Each image may be up to 8 MB.';
                                        else if (xhr.status === 403) message = 'The request was blocked with HTTP 403. Please reload the page and try again.';
                                        else if (xhr.status === 429) message = 'Too many requests. Please wait briefly and try again.';
                                        else if (xhr.status === 500) message = 'The server returned HTTP 500 while processing the application.';
                                        else if (xhr.status === 502) message = 'The server returned HTTP 502 Bad Gateway.';
                                        else if (xhr.status === 503) message = 'The server is temporarily unavailable, HTTP 503.';
                                        else if (xhr.status === 504) message = 'The upload timed out, HTTP 504 Gateway Timeout.';
                                        else if (textStatus === 'timeout') message = 'The upload request timed out.';
                                        else if (xhr.status === 0) message = 'The request could not reach the server. Check your connection and try again.';
                                        else message = errorThrown || xhr.statusText || `Request failed with HTTP ${xhr.status || 'unknown'}.`;
                                    }
                                    console.error('Booster onboarding failed', {status: xhr.status, response: xhr.responseText, textStatus, errorThrown});
                                    create_toast('danger', `Error ${xhr.status || ''}`.trim(), message);
                                    reject();
                                },
                                success: function (response) {
                                    $form.find('button[type="submit"]').removeAttr('data-indicator');
                                    $form.find('*:not(.disabled)').prop('disabled', false);
                                    $('#form-error').html('');
                                    $('#form-error').hide();

                                    let parsedResponse;
                                    try {
                                        parsedResponse = JSON.parse(response);
                                    } catch (e) {
                                        const tmp = document.createElement('div');
                                        tmp.innerHTML = String(response || '');
                                        const realResponse = (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
                                        create_toast('danger', 'Invalid server response', realResponse || 'The server returned an empty or invalid response.');
                                        console.error('Invalid booster onboarding response', response, e);
                                        reject();
                                        return;
                                    }

                                    ajax_response_handler(parsedResponse);
                                    if (parsedResponse.playSound == 'error') {
                                        reject();
                                    } else {
                                        step3Submitted = true;
                                        resolve();
                                    }
                                },
                            });
                        });
                    }

                    return Promise.resolve();
                },
                finish($el) {
                    const $successMessageTempalte = $el.querySelector('.js-success-message')?.cloneNode(true);
                    if ($successMessageTempalte) {
                        $successMessageTempalte.style.display = 'block';
                        $el.style.display = 'none';
                        $el.parentElement.appendChild($successMessageTempalte);
                    }
                }
            });

            // Update aside on load
            setAside('step1');

            // TomSelect
            new TomSelect('#games', {
                plugins: ['remove_button'],
                search: true,
                placeholder: 'Select games',
                maxHeight: '220px',
                allowEmptyOption: true,
                hideSelected: true,
                closeAfterSelect: false
            });

            new TomSelect('#country', {
                search: true,
                placeholder: 'Select a country',
                maxHeight: '220px'
            });

            // Date picker
            $('#dob').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                locale: { format: 'DD-MM-YYYY', cancelLabel: 'Clear' }
            });

            // Only set value when user picks a date
            $('#dob').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY')).trigger('input');
            });
            $('#dob').on('cancel.daterangepicker', function () {
                $(this).val('').trigger('input');
            });

            // Also update aside when clicking stepper items
            document.querySelectorAll('[data-hs-step-form-next-options]').forEach((el) => {
                el.addEventListener('click', () => {
                    const opt = el.getAttribute('data-hs-step-form-next-options');
                    if (!opt) return;
                    try {
                        const parsed = JSON.parse(opt);
                        if (parsed && parsed.targetSelector) {
                            setAside(parsed.targetSelector.replace('#',''));
                        }
                    } catch (e) {}
                });
            });
        })();
    </script>

    <!-- JS Plugins Init. -->
    <?= $this->section('scripts') ?>
</body>

</html>
