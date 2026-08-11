<?php
$pageTitle = !empty($meta['title']) ? $meta['title'] : 'Apply';
$pageDescription = !empty($meta['description']) ? $meta['description'] : '';
$pageKeywords = !empty($meta['keywords']) ? $meta['keywords'] : '';
$requestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$requestPath = $requestPath ?: '/';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
$host = $_SERVER['HTTP_HOST'] ?? 'lolboost.gg';
$defaultCanonical = $scheme . '://' . $host . $requestPath;
$canonical = $meta['canonical'] ?? $defaultCanonical;
$robots = $meta['robots'] ?? 'index,follow';
$logoIconSvg = ASSET_URL . '/website/images/logo.svg?v10';
$logoIconPng = ASSET_URL . '/core/main/img/logos/PNG/icon-bg-64x64.png?v6';
$logoWordmarkSvg = ASSET_URL . '/website/images/Logo3.svg?v10';
?>
<!DOCTYPE html>
<html lang="<?= defined('CURRENT_LANGUAGE') ? CURRENT_LANGUAGE : 'en' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($pageDescription): ?>
        <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($pageKeywords): ?>
        <meta name="keywords" content="<?= htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="https://i.imgur.com/qcyweqm.png">
    <meta property="twitter:card" content="<?= !empty($meta['summary']) ? 'summary' : 'summary_large_image' ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="twitter:image" content="https://i.imgur.com/qcyweqm.png">
    <link rel="image_src" href="https://i.imgur.com/qcyweqm.png" />
    <meta name="theme-color" content="#6366F1">
    <link rel="alternate" hreflang="en" href="https://lolboost.gg">
    <link rel="alternate" hreflang="de" href="https://de.lolboost.gg">
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-596N9MJ3');</script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11473081744"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-11473081744');
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />
    <style>
html, body {
    margin: 0;
    padding: 0;
    min-height: 100%;
}

body.apply-page.apply-wizard-page {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(1000px 520px at 85% 0%, rgba(99, 102, 241, .22), transparent 58%),
        radial-gradient(720px 420px at 10% 82%, rgba(56, 189, 248, .12), transparent 60%),
        linear-gradient(180deg, #05030b 0%, #06040d 45%, #05040b 100%);
    color: #fff;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

* { box-sizing: border-box; }

.apply-custom-header {
    position: sticky;
    top: 0;
    z-index: 1100;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: rgba(4, 7, 18, .86);
    backdrop-filter: blur(18px);
}

.apply-custom-header__inner,
.apply-custom-footer__inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.apply-custom-header__inner {
    min-height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.apply-custom-header__brand,
.apply-custom-footer__brand {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.apply-brand-icon {
    width: 52px;
    height: 52px;
    display: block;
    object-fit: contain;
    flex: 0 0 auto;
    filter: drop-shadow(0 8px 22px rgba(99,102,241,.18));
}

.apply-brand-text {
    color: #fff;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: .08em;
    line-height: 1;
    text-transform: uppercase;
    white-space: nowrap;
}

.apply-custom-footer__brand .apply-brand-text {
    font-size: 18px;
}

.apply-custom-header__nav {
    display: flex;
    align-items: center;
    gap: 12px;
}

.apply-header-btn {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 16px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.76);
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    transition: all .16s ease;
}

.apply-header-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(255,255,255,.14);
    background: rgba(255,255,255,.06);
    color: #fff;
}

.apply-hero {
    padding-top: 72px;
}

.apply-main {
    padding-bottom: 90px;
}

.apply-custom-footer {
    border-top: 1px solid rgba(255,255,255,.06);
    background: rgba(4,7,18,.42);
}

.apply-custom-footer__inner {
    min-height: 86px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 24px;
}

.apply-custom-footer__links {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 22px;
    flex-wrap: wrap;
}

.apply-custom-footer__links a,
.apply-custom-footer__copy {
    color: rgba(255,255,255,.42);
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.apply-custom-footer__links a:hover {
    color: rgba(255,255,255,.8);
}

@media (max-width: 900px) {
    .apply-custom-footer__inner {
        grid-template-columns: 1fr;
        justify-items: center;
        padding-top: 18px;
        padding-bottom: 18px;
        text-align: center;
    }
}

@media (max-width: 640px) {
    .apply-custom-header__inner,
    .apply-custom-footer__inner {
        padding-left: 16px;
        padding-right: 16px;
    }

    .apply-custom-header__inner {
        min-height: 62px;
    }

    .apply-header-btn {
        min-height: 38px;
        padding: 0 14px;
        font-size: 12px;
    }

    .apply-hero {
        padding-top: 54px;
    }
}

/* ═══════════════════════════════════════════════
   APPLY PAGE — OPEN / IMMERSIVE REDESIGN
   No card-box, form floats on the dark background
═══════════════════════════════════════════════ */
.apply-wizard-page {
    font-size: 18px;
}

.apply-wizard-page .navbar,
.apply-wizard-page .navbar a,
.apply-wizard-page .navbar button,
.apply-wizard-page .navbar .dropdown-toggle {
    font-size: 15px;
}

/* Slightly larger defaults because the page is viewed at 0.88 zoom */
.apply-wizard-page p,
.apply-wizard-page li,
.apply-wizard-page span,
.apply-wizard-page a,
.apply-wizard-page button,
.apply-wizard-page input,
.apply-wizard-page select,
.apply-wizard-page textarea,
.apply-wizard-page label {
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

.apply-wizard-page {
    background: transparent;
    color: #fff;
}

/* ── SHELL / HERO ── */
.apply-shell {
    position: relative;
}

.apply-hero {
    position: relative;
    padding: 110px 0 18px;
    text-align: center;
    overflow: hidden;
}

.apply-hero::before {
    content: '';
    position: absolute;
    inset: -80px 0 0;
    z-index: 0;
    pointer-events: none;
    background:
        linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px),
        radial-gradient(900px 520px at 82% 8%, rgba(99, 102, 241, .26), transparent 60%),
        radial-gradient(700px 420px at 18% 88%, rgba(56, 189, 248, .14), transparent 62%),
        linear-gradient(180deg, rgba(16, 6, 30, .55) 0%, rgba(9, 5, 18, .18) 72%, rgba(5, 4, 11, 0) 100%);
    background-size: 44px 44px, 44px 44px, auto, auto, auto;
}

.apply-hero::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 120px;
    pointer-events: none;
    background: linear-gradient(180deg, rgba(5,4,11,0) 0%, rgba(5,4,11,.92) 100%);
}

.apply-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 980px;
    margin: 0 auto;
    padding: 0 24px;
}

.apply-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    border-radius: 999px;
    background: rgba(99,102,241,.12);
    border: 1px solid rgba(129,140,248,.24);
    color: #c7d2fe;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-bottom: 20px;
}

.apply-title {
    margin: 0;
    font-size: clamp(36px, 5.2vw, 78px);
    line-height: 1.02;
    font-weight: 950;
    letter-spacing: -.04em;
}

.apply-title .accent {
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 48%, #38bdf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.apply-subtitle {
    max-width: 720px;
    margin: 18px auto 0;
    font-size: clamp(18px, 1.55vw, 21px);
    line-height: 1.8;
    color: rgba(255,255,255,.52);
}

/* ── STEPS BAR (lives in hero) ── */
.apply-steps-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    max-width: 520px;
    margin: 44px auto 0;
    padding: 0 24px;
}

.apply-stp {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 7px;
    flex: 0 0 auto;
}

.apply-stp-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 900;
    background: rgba(255,255,255,.055);
    border: 1.5px solid rgba(255,255,255,.11);
    color: rgba(255,255,255,.30);
    transition: background .25s, border-color .25s, color .25s, box-shadow .25s;
    letter-spacing: 0;
    line-height: 1;
}

.apply-stp.active .apply-stp-circle {
    background: rgba(99,102,241,.20);
    border-color: #6366f1;
    color: #c7d2fe;
    box-shadow: 0 0 0 5px rgba(99,102,241,.13);
}

.apply-stp.done .apply-stp-circle {
    background: rgba(22,163,74,.16);
    border-color: rgba(74,222,128,.40);
    color: #86efac;
}

.apply-stp-label {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .11em;
    color: rgba(255,255,255,.28);
    white-space: nowrap;
    transition: color .25s;
}

.apply-stp.active .apply-stp-label { color: #818cf8; }
.apply-stp.done  .apply-stp-label  { color: rgba(74,222,128,.55); }

.apply-stp-line {
    flex: 1;
    height: 1.5px;
    background: rgba(255,255,255,.08);
    align-self: flex-start;
    margin-top: 19px;
    min-width: 40px;
    transition: background .25s;
}

.apply-stp-line.done { background: rgba(74,222,128,.30); }

/* Role-colored active step circle */
.apply-wizard-pane-wrap[data-active-role="booster"] .apply-stp.active .apply-stp-circle,
[data-active-role="booster"] .apply-stp.active .apply-stp-circle { background: rgba(167,139,250,.18); border-color: #a78bfa; color: #ddd6fe; box-shadow: 0 0 0 5px rgba(167,139,250,.10); }
.apply-wizard-pane-wrap[data-active-role="lol_booster"] .apply-stp.active .apply-stp-circle,
[data-active-role="lol_booster"] .apply-stp.active .apply-stp-circle { background: rgba(200,155,60,.20); border-color: #c89b3c; color: #f0c96a; box-shadow: 0 0 0 5px rgba(200,155,60,.12); }
.apply-wizard-pane-wrap[data-active-role="tft_booster"] .apply-stp.active .apply-stp-circle,
[data-active-role="tft_booster"] .apply-stp.active .apply-stp-circle { background: rgba(11,196,227,.18); border-color: #0bc4e3; color: #7ee8f5; box-shadow: 0 0 0 5px rgba(11,196,227,.10); }
.apply-wizard-pane-wrap[data-active-role="val_booster"] .apply-stp.active .apply-stp-circle,
[data-active-role="val_booster"] .apply-stp.active .apply-stp-circle { background: rgba(255,70,85,.18); border-color: #ff4655; color: #ff8a94; box-shadow: 0 0 0 5px rgba(255,70,85,.10); }
.apply-wizard-pane-wrap[data-active-role="gg_girl"]     .apply-stp.active .apply-stp-circle,
[data-active-role="gg_girl"]     .apply-stp.active .apply-stp-circle { background: rgba(99,102,241,.18); border-color: #6366f1; color: #c7d2fe; box-shadow: 0 0 0 5px rgba(99,102,241,.10); }
.apply-wizard-pane-wrap[data-active-role="seller"]      .apply-stp.active .apply-stp-circle,
[data-active-role="seller"]      .apply-stp.active .apply-stp-circle { background: rgba(74,222,128,.16); border-color: #4ade80; color: #86efac; box-shadow: 0 0 0 5px rgba(74,222,128,.10); }

/* ── MAIN CONTENT AREA (no card box) ── */
.apply-main {
    position: relative;
    z-index: 2;
    max-width: 1240px;
    margin: 0 auto;
    padding: 52px 24px 110px;
    overflow: visible;
}

.apply-grid {
    display: block;
}

.apply-sidebar {
    display: none !important;
}

/* The card shell is now invisible — it's just a layout wrapper */
.apply-card {
    background: transparent;
    border: none;
    box-shadow: none;
    backdrop-filter: none;
    padding: 0;
}

/* Sidebar remnants (hidden but kept for JS compat) */
.sidebar-title, .sidebar-copy, .step-list, .step-item,
.step-badge, .step-meta, .step-kicker, .step-name, .step-desc,
.sidebar-box { display: none; }

/* apply-card is now transparent — see above */

@media (max-width: 640px) {
    .apply-main { padding: 36px 16px 80px; }
}

.wizard-topbar {
    display: none;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-top: 8px;
}

@media (max-width: 900px) {
    .role-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 540px) {
    .role-grid {
        grid-template-columns: 1fr;
    }
}

.role-card {
    position: relative;
    padding: 18px 14px;
    border-radius: 22px;
    border: 1.5px solid rgba(255,255,255,.09);
    background: rgba(255,255,255,.025);
    cursor: pointer;
    text-align: center;
    transition: transform .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
    user-select: none;
}

.role-card:hover {
    transform: translateY(-3px);
    background: rgba(255,255,255,.045);
    border-color: rgba(255,255,255,.16);
}

.role-card.selected {
    border-color: var(--rc-color);
    background: var(--rc-bg);
    box-shadow: 0 12px 40px var(--rc-glow), 0 0 0 1px var(--rc-color);
}

.role-card input[type="radio"] {
    display: none;
}

.role-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.09);
    background: rgba(255,255,255,.045);
}

.role-card.selected .role-icon {
    background: var(--rc-icon-bg);
    border-color: var(--rc-color);
}

.role-name {
    font-size: 17px;
    font-weight: 900;
    letter-spacing: -.01em;
}

.role-sub {
    margin-top: 6px;
    font-size: 13px;
    color: rgba(255,255,255,.42);
    font-weight: 700;
}

[data-role="lol_booster"]  { --rc-color: #c89b3c; --rc-bg: rgba(200,155,60,.08); --rc-glow: rgba(200,155,60,.16); --rc-icon-bg: rgba(200,155,60,.16); }
[data-role="tft_booster"]  { --rc-color: #0bc4e3; --rc-bg: rgba(11,196,227,.08); --rc-glow: rgba(11,196,227,.15); --rc-icon-bg: rgba(11,196,227,.16); }
[data-role="val_booster"]  { --rc-color: #ff4655; --rc-bg: rgba(255,70,85,.08); --rc-glow: rgba(255,70,85,.15); --rc-icon-bg: rgba(255,70,85,.16); }
[data-role="gg_girl"]      { --rc-color: #6366f1; --rc-bg: rgba(99,102,241,.08); --rc-glow: rgba(99,102,241,.15); --rc-icon-bg: rgba(224,64,251,.16); }
[data-role="seller"]       { --rc-color: #4ade80; --rc-bg: rgba(74,222,128,.08); --rc-glow: rgba(74,222,128,.15); --rc-icon-bg: rgba(74,222,128,.16); }

.wizard-pane {
    display: none;
}

.wizard-pane.active {
    display: block;
    animation: fadePane .22s ease;
}

@keyframes fadePane {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.wizard-head {
    margin-bottom: 32px;
}

.wizard-pane[data-pane="5"] .wizard-head {
    max-width: 1180px;
    margin: 0 auto 32px;
}

.wizard-kicker {
    margin: 0 0 8px;
    font-size: 13px;
    color: rgba(255,255,255,.44);
    text-transform: uppercase;
    letter-spacing: .12em;
    font-weight: 800;
}

/* Step pill — "STEP 1 OF 4" badge */
.step-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.055);
    border: 1px solid rgba(255,255,255,.09);
    font-size: 10px;
    font-weight: 800;
    color: rgba(255,255,255,.38);
    text-transform: uppercase;
    letter-spacing: .11em;
    margin-bottom: 14px;
}

.wizard-head h4 {
    margin: 0 0 10px;
    font-size: clamp(34px, 4vw, 50px);
    font-weight: 950;
    letter-spacing: -.04em;
    line-height: 1.02;
}

.wizard-head p {
    margin: 0;
    color: rgba(255,255,255,.52);
    line-height: 1.75;
    font-size: 18px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    overflow: visible;
}

@media (max-width: 760px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

/* form-block is now borderless */
.form-block {
    margin-top: 24px;
    padding: 0;
    border-radius: 0;
    background: transparent;
    border: none;
    overflow: visible;
}

.form-block-title {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .13em;
    color: rgba(255,255,255,.28);
    font-weight: 800;
    margin: 0 0 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

.apply-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 14px;
}

.apply-field:last-child {
    margin-bottom: 0;
}

.apply-field label {
    display: inline-flex;
    align-items: flex-start;
    gap: 4px;
    flex-wrap: nowrap;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: rgba(255,255,255,.50);
}

.apply-field label .req {
    color: #fb7185;
    margin-left: 0;
    line-height: 1;
    transform: translateY(-1px);
    flex-shrink: 0;
}

.apply-input,
.apply-select,
.apply-textarea {
    width: 100%;
    border: 1.5px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.045);
    color: #fff;
    border-radius: 14px;
    padding: 17px 19px;
    font-size: 17px;
    font-weight: 600;
    font-family: inherit;
    outline: none;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}

.apply-input,
.apply-select,
.searchdrop-trigger {
    min-height: 56px;
}

.apply-input::placeholder,
.apply-textarea::placeholder {
    color: rgba(255,255,255,.24);
}

.apply-select {
    appearance: none;
    cursor: pointer;
}

.apply-select option {
    background: #131826;
    color: #fff;
}

.apply-input:focus,
.apply-select:focus,
.apply-textarea:focus {
    background: rgba(99,102,241,.07);
    border-color: rgba(129,140,248,.55);
    box-shadow: 0 0 0 4px rgba(99,102,241,.10);
}

.apply-textarea {
    min-height: 140px;
    resize: vertical;
}

.checkbox-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 18px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    margin-top: 6px;
}

.checkbox-card input[type="checkbox"] {
    margin-top: 2px;
    width: 18px;
    height: 18px;
    accent-color: #6366f1;
    cursor: pointer;
    flex-shrink: 0;
}

.checkbox-card label {
    margin: 0;
    cursor: pointer;
    color: rgba(255,255,255,.64);
    font-weight: 600;
    font-size: 17px;
    line-height: 1.75;
    text-transform: none;
    letter-spacing: 0;
}

/* ── VPN TOGGLE CARD ── */
.vpn-toggle-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    margin-top: 16px;
    flex-wrap: wrap;
}

.vpn-toggle-info {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
    min-width: 0;
}

.vpn-toggle-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(99,102,241,.12);
    border: 1px solid rgba(129,140,248,.20);
    font-size: 18px;
    color: #818cf8;
}

.vpn-toggle-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.vpn-toggle-label {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .10em;
    color: rgba(255,255,255,.42);
}

.vpn-toggle-sub {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,.62);
    line-height: 1.5;
}

.vpn-toggle-btns {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.vpn-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.38);
    font-size: 13px;
    font-weight: 800;
    font-family: inherit;
    cursor: pointer;
    transition: all .16s ease;
    letter-spacing: .02em;
}

.vpn-btn:hover {
    border-color: rgba(255,255,255,.20);
    color: rgba(255,255,255,.65);
    background: rgba(255,255,255,.07);
}

.vpn-btn-no.active {
    background: rgba(251,113,133,.12);
    border-color: rgba(251,113,133,.35);
    color: #fda4af;
}

.vpn-btn-yes.active {
    background: rgba(74,222,128,.12);
    border-color: rgba(74,222,128,.35);
    color: #86efac;
}

/* Region badge styles in dropdown */
.option-region-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 48px;
    padding: 2px 8px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .06em;
    background: rgba(99,102,241,.15);
    border: 1px solid rgba(129,140,248,.22);
    color: #a5b4fc;
    flex-shrink: 0;
}

.helper-note {
    margin-top: 14px;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(129,140,248,.16);
    color: rgba(255,255,255,.72);
    font-size: 17px;
    line-height: 1.85;
}

.review-card {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px 18px;
}

.review-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 7px;
    padding: 14px 16px;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    background: rgba(9, 7, 18, .98);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.02);
}

.review-row:last-child {
    border-bottom: 1px solid rgba(255,255,255,.06);
}

@media (max-width: 900px) {
    .review-card {
        grid-template-columns: 1fr;
    }
}

.review-key {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: rgba(255,255,255,.38);
    font-weight: 800;
    line-height: 1.3;
}

.review-value {
    color: rgba(255,255,255,.9);
    line-height: 1.45;
    white-space: pre-wrap;
    word-break: break-word;
    font-weight: 700;
    font-size: 15px;
}

.wizard-pane[data-pane="5"] .form-block {
    width: min(100%, 1180px);
    max-width: 1180px;
    margin-inline: auto;
    padding: 24px;
    border-radius: 24px;
    background: linear-gradient(180deg, rgba(11,8,22,.98) 0%, rgba(8,6,16,.98) 100%);
    border: 1px solid rgba(255,255,255,.05);
    box-shadow: 0 22px 60px rgba(0,0,0,.28);
    backdrop-filter: blur(8px);
}

.wizard-pane[data-pane="5"] .form-block-title {
    margin-bottom: 14px;
}

.wizard-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 40px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,.07);
}

@media (max-width: 640px) {
    .wizard-nav {
        flex-direction: column-reverse;
        align-items: stretch;
    }
}

.btn-ghost,
.btn-next,
.btn-submit {
    min-height: 56px;
    border-radius: 16px;
    border: 0;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: -.01em;
    transition: transform .14s ease, box-shadow .14s ease, opacity .14s ease;
}

.btn-ghost {
    padding: 0 20px;
    background: rgba(255,255,255,.05);
    color: #fff;
    border: 1px solid rgba(255,255,255,.08);
}

.btn-next,
.btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 0 24px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    box-shadow: 0 10px 40px rgba(99,102,241,.28);
}

.btn-ghost:hover,
.btn-next:hover,
.btn-submit:hover {
    transform: translateY(-2px);
}

.btn-ghost:hover {
    background: rgba(255,255,255,.08);
}

.btn-next:hover,
.btn-submit:hover {
    box-shadow: 0 16px 48px rgba(99,102,241,.36);
}

.btn-next:disabled,
.btn-submit:disabled,
.btn-ghost:disabled {
    opacity: .55;
    cursor: not-allowed;
    transform: none;
}

[data-active-role="lol_booster"] .btn-next,
[data-active-role="lol_booster"] .btn-submit { background: linear-gradient(135deg, #c89b3c, #a97016); box-shadow: 0 10px 40px rgba(200,155,60,.28); }
[data-active-role="tft_booster"] .btn-next,
[data-active-role="tft_booster"] .btn-submit { background: linear-gradient(135deg, #0bc4e3, #0891b2); box-shadow: 0 10px 40px rgba(11,196,227,.28); }
[data-active-role="val_booster"] .btn-next,
[data-active-role="val_booster"] .btn-submit { background: linear-gradient(135deg, #ff4655, #dc2626); box-shadow: 0 10px 40px rgba(255,70,85,.28); }
[data-active-role="gg_girl"] .btn-next,
[data-active-role="gg_girl"] .btn-submit { background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 10px 40px rgba(99,102,241,.28); }
[data-active-role="seller"] .btn-next,
[data-active-role="seller"] .btn-submit { background: linear-gradient(135deg, #4ade80, #16a34a); box-shadow: 0 10px 40px rgba(74,222,128,.28); }

.field-error {
    margin-top: 2px;
    font-size: 13px;
    color: #fda4af;
    display: none;
}

.apply-success {
    display: none;
    text-align: center;
    padding: 60px 20px;
}

.apply-success.visible {
    display: block;
}

.apply-success-badge {
    width: 88px;
    height: 88px;
    margin: 0 auto 24px;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    background: rgba(74,222,128,.12);
    border: 1px solid rgba(74,222,128,.24);
    box-shadow: 0 16px 60px rgba(22,163,74,.18);
}

.apply-success h3 {
    margin: 0 0 12px;
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 950;
    letter-spacing: -.04em;
}

.apply-success p {
    max-width: 580px;
    margin: 0 auto;
    color: rgba(255,255,255,.55);
    line-height: 1.8;
    font-size: 18px;
}

/* ── SECTION LABEL (replaces form-block-title) ── */
.section-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .13em;
    color: rgba(255,255,255,.28);
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

/* ── WIZARD STEPS (kept for JS compat, now invisible — real steps bar is in hero) ── */
.wizard-steps {
    display: none;
}

/* ── SEARCHABLE DROPDOWN ── */
.searchdrop-wrap {
    position: relative;
    width: 100%;
    overflow: visible;
    z-index: 40;
}

.searchdrop-wrap.open {
    z-index: 2147483000;
}

.apply-field,
.form-block,
.wizard-pane,
.wizard-pane.active {
    position: relative;
    overflow: visible;
}

.searchdrop-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    border: 1.5px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.045);
    color: #fff;
    border-radius: 14px;
    padding: 17px 19px;
    font-size: 17px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    outline: none;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    user-select: none;
    min-height: 50px;
}

.searchdrop-trigger:focus,
.searchdrop-trigger.open {
    background: rgba(99,102,241,.07);
    border-color: rgba(129,140,248,.55);
    box-shadow: 0 0 0 4px rgba(99,102,241,.10);
}

.searchdrop-trigger-tags {
    flex-wrap: nowrap;
    align-items: center;
    padding: 8px 16px;
    gap: 8px;
}

.searchdrop-display {
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.searchdrop-display.placeholder,
.searchdrop-placeholder {
    color: rgba(255,255,255,.24);
}

.searchdrop-arrow {
    font-size: 11px;
    color: rgba(255,255,255,.35);
    flex-shrink: 0;
    transition: transform .18s ease, color .15s;
    margin-left: auto;
}

.searchdrop-trigger.open .searchdrop-arrow {
    transform: rotate(180deg);
    color: #818cf8;
}

.searchdrop-tags {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
    flex: 1;
    align-items: center;
    min-width: 0;
    overflow: hidden;
}

.searchdrop-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px 3px 12px;
    border-radius: 999px;
    background: rgba(99,102,241,.22);
    border: 1px solid rgba(129,140,248,.30);
    color: #c7d2fe;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.searchdrop-tag-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    cursor: pointer;
    font-size: 9px;
    color: rgba(255,255,255,.7);
    flex-shrink: 0;
    transition: background .12s;
    border: none;
    padding: 0;
    font-family: inherit;
    line-height: 1;
}

.searchdrop-tag-remove:hover {
    background: rgba(255,70,85,.4);
    color: #fff;
}

.searchdrop-panel {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 100%;
    z-index: 2147483000;
    background: #131826;
    border: 1.5px solid rgba(129,140,248,.25);
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.55), 0 0 0 1px rgba(255,255,255,.04);
    overflow: hidden;
    animation: dropIn .16s ease;
}

@keyframes dropIn {
    from { opacity: 0; transform: translateY(-6px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.searchdrop-panel.open {
    display: block;
}

.searchdrop-search-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

.searchdrop-search-icon {
    font-size: 12px;
    color: rgba(255,255,255,.28);
    flex-shrink: 0;
}

.searchdrop-search {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    font-family: inherit;
}

.searchdrop-search::placeholder {
    color: rgba(255,255,255,.24);
}

.searchdrop-list {
    list-style: none;
    margin: 0;
    padding: 6px;
    max-height: 280px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(99,102,241,.4) transparent;
}

.searchdrop-list::-webkit-scrollbar { width: 4px; }
.searchdrop-list::-webkit-scrollbar-track { background: transparent; }
.searchdrop-list::-webkit-scrollbar-thumb { background: rgba(99,102,241,.4); border-radius: 999px; }

.searchdrop-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 13px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: rgba(255,255,255,.72);
    transition: background .12s, color .12s;
    user-select: none;
}

.searchdrop-option:hover {
    background: rgba(255,255,255,.07);
    color: #fff;
}

.searchdrop-option.selected {
    background: rgba(99,102,241,.14);
    color: #c7d2fe;
}

.searchdrop-option .option-check {
    width: 16px;
    height: 16px;
    border-radius: 5px;
    border: 1.5px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.04);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 9px;
    color: transparent;
    transition: background .12s, border-color .12s, color .12s;
}

.searchdrop-option.selected .option-check {
    background: #6366f1;
    border-color: #6366f1;
    color: #fff;
}

.searchdrop-option .option-flag {
    font-size: 16px;
    line-height: 1;
    flex-shrink: 0;
}

.searchdrop-empty {
    padding: 16px;
    text-align: center;
    color: rgba(255,255,255,.28);
    font-size: 14px;
}

/* ── MOBILE OPTIMIZATIONS ── */
@media (max-width: 640px) {
    body.apply-page.apply-wizard-page {
        overflow-x: hidden;
    }

    .apply-custom-header {
        backdrop-filter: blur(12px);
    }

    .apply-custom-header__inner {
        min-height: 58px;
        gap: 10px;
        padding-left: 12px;
        padding-right: 12px;
    }

    .apply-custom-header__brand,
    .apply-custom-header__nav {
        min-width: 0;
    }

    .apply-brand-icon {
        width: 40px;
        height: 40px;
    }

    .apply-brand-text {
        font-size: 16px;
        letter-spacing: .06em;
    }

    .apply-custom-footer__brand .apply-brand-text {
        font-size: 14px;
    }

    .apply-header-btn {
        min-height: 36px;
        padding: 0 12px;
        border-radius: 12px;
        font-size: 11px;
        gap: 6px;
        white-space: nowrap;
    }

    .apply-hero {
        padding: 56px 0 10px;
    }

    .apply-hero::before {
        inset: -30px 0 0;
        background-size: 30px 30px, 30px 30px, auto, auto, auto;
    }

    .apply-hero::after {
        height: 72px;
    }

    .apply-hero-inner {
        padding: 0 16px;
    }

    .apply-eyebrow {
        padding: 6px 12px;
        font-size: 11px;
        letter-spacing: .12em;
        margin-bottom: 16px;
    }

    .apply-title {
        font-size: clamp(24px, 9.2vw, 40px);
        line-height: 1.06;
        letter-spacing: -.035em;
    }

    .apply-subtitle {
        margin-top: 14px;
        font-size: 15px;
        line-height: 1.65;
        color: rgba(255,255,255,.60);
        max-width: 100%;
    }

    .apply-steps-bar {
        max-width: 100%;
        margin-top: 26px;
        padding: 0;
        gap: 0;
        overflow-x: auto;
        overflow-y: hidden;
        justify-content: flex-start;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .apply-steps-bar::-webkit-scrollbar {
        display: none;
    }

    .apply-stp {
        min-width: 64px;
        scroll-snap-align: center;
        gap: 6px;
    }

    .apply-stp-circle {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }

    .apply-stp-label {
        font-size: 10px;
        letter-spacing: .08em;
    }

    .apply-stp-line {
        min-width: 18px;
        margin-top: 17px;
    }

    .apply-main {
        padding: 26px 16px 56px;
    }

    .wizard-head {
        margin-bottom: 22px;
    }

    .step-pill-badge {
        padding: 4px 12px;
        font-size: 9px;
        margin-bottom: 12px;
    }

    .wizard-head h4 {
        font-size: clamp(22px, 8vw, 34px);
        line-height: 1.08;
        margin-bottom: 10px;
    }

    .wizard-head p {
        font-size: 15px;
        line-height: 1.65;
        color: rgba(255,255,255,.60);
    }

    .role-grid {
        grid-template-columns: 1fr;
        gap: 12px;
        margin-top: 4px;
    }

    .role-card {
        display: grid;
        grid-template-columns: 48px 1fr;
        align-items: center;
        text-align: left;
        gap: 0 14px;
        padding: 16px;
        border-radius: 18px;
    }

    .role-icon {
        width: 48px;
        height: 48px;
        margin: 0;
        border-radius: 14px;
        grid-row: span 2;
    }

    .role-name {
        font-size: 15px;
        line-height: 1.2;
    }

    .role-sub {
        margin-top: 2px;
        font-size: 12px;
    }

    .form-block {
        margin-top: 18px;
    }

    .form-block-title,
    .section-label {
        font-size: 11px;
        letter-spacing: .10em;
        margin-bottom: 14px;
        padding-bottom: 10px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .apply-field {
        gap: 6px;
        margin-bottom: 10px;
    }

    .apply-field label {
        font-size: 12px;
        line-height: 1.35;
        letter-spacing: .05em;
        white-space: normal;
        word-break: break-word;
    }

    .apply-input,
    .apply-select,
    .apply-textarea,
    .searchdrop-trigger {
        border-radius: 14px;
        font-size: 16px;
    }

    .apply-input,
    .apply-select,
    .searchdrop-trigger {
        min-height: 52px;
        padding: 15px 16px;
    }

    .apply-textarea {
        min-height: 124px;
        padding: 15px 16px;
    }

    .field-error {
        font-size: 12px;
        line-height: 1.35;
    }

    .searchdrop-trigger-tags {
        padding: 8px 14px;
    }

    .searchdrop-tags {
        flex-wrap: wrap;
        overflow: visible;
    }

    .searchdrop-tag {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .searchdrop-panel {
        border-radius: 16px;
    }

    .searchdrop-search-wrap {
        padding: 11px 14px;
    }

    .searchdrop-list {
        max-height: min(260px, 50vh);
        padding: 6px;
    }

    .searchdrop-option {
        align-items: flex-start;
        padding: 12px;
        font-size: 14px;
    }

    .vpn-toggle-card,
    .checkbox-card,
    .helper-note {
        border-radius: 16px;
        padding: 14px;
    }

    .vpn-toggle-card {
        align-items: stretch;
    }

    .vpn-toggle-info {
        gap: 12px;
    }

    .vpn-toggle-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 16px;
    }

    .vpn-toggle-label {
        font-size: 11px;
        letter-spacing: .08em;
    }

    .vpn-toggle-sub,
    .helper-note,
    .checkbox-card label {
        font-size: 14px;
        line-height: 1.6;
    }

    .vpn-toggle-btns {
        width: 100%;
        gap: 8px;
    }

    .vpn-btn {
        flex: 1 1 0;
        justify-content: center;
        min-height: 42px;
        padding: 0 12px;
        font-size: 13px;
    }

    .review-card {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .review-row {
        padding: 12px 14px;
        border-radius: 14px;
    }

    .review-key {
        font-size: 10px;
    }

    .review-value {
        font-size: 14px;
        line-height: 1.5;
    }

    .wizard-pane[data-pane="5"] .form-block {
        width: 100%;
        padding: 16px;
        border-radius: 18px;
        box-shadow: 0 14px 36px rgba(0,0,0,.24);
    }

    .wizard-nav {
        margin-top: 26px;
        padding-top: 18px;
        gap: 10px;
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .btn-ghost,
    .btn-next,
    .btn-submit {
        width: 100%;
        min-height: 54px;
        border-radius: 15px;
        font-size: 15px;
    }

    .apply-success {
        padding: 36px 0 10px;
    }

    .apply-success-badge {
        width: 74px;
        height: 74px;
        border-radius: 22px;
        font-size: 30px;
        margin-bottom: 18px;
    }

    .apply-success h3 {
        font-size: 28px;
        line-height: 1.08;
    }

    .apply-success p {
        font-size: 15px;
        line-height: 1.7;
    }

    .apply-custom-footer__inner {
        min-height: auto;
        gap: 14px;
        padding-top: 18px;
        padding-bottom: 24px;
    }

    .apply-custom-footer__links {
        gap: 14px 18px;
    }

    .apply-custom-footer__links a,
    .apply-custom-footer__copy {
        font-size: 12px;
    }
}

@media (max-width: 380px) {
    .apply-custom-header__inner,
    .apply-hero-inner,
    .apply-main,
    .apply-custom-footer__inner {
        padding-left: 12px;
        padding-right: 12px;
    }

    .apply-header-btn span {
        max-width: 84px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .apply-title {
        font-size: 22px;
    }

    .apply-subtitle,
    .wizard-head p,
    .helper-note,
    .checkbox-card label,
    .vpn-toggle-sub,
    .apply-success p {
        font-size: 14px;
    }

    .role-card {
        padding: 14px;
        gap: 0 12px;
    }

    .role-icon {
        width: 44px;
        height: 44px;
    }

    .searchdrop-trigger,
    .apply-input,
    .apply-select {
        min-height: 50px;
    }
}

    

/* LoLBoost.gg blue polish */
body.apply-page.apply-wizard-page {
    --lb-primary: #6366f1;
    --lb-primary-soft: rgba(99,102,241,.14);
    --lb-primary-border: rgba(129,140,248,.32);
}
.apply-title .accent,
.apply-brand-text {
    text-shadow: 0 12px 45px rgba(99,102,241,.24);
}
.apply-eyebrow,
.step-pill-badge,
.searchdrop-tag {
    border-color: rgba(129,140,248,.28);
}
.role-card.selected,
.boost-game-option.selected {
    box-shadow: 0 12px 42px rgba(99,102,241,.22), 0 0 0 1px rgba(129,140,248,.18);
}
</style>

<style>
/* ═══════ ENHANCED ANIMATIONS ═══════ */

/* Hero entrance */
@keyframes heroFadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes eyebrowShimmer {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}
@keyframes floatDot {
    0%,100% { transform: translateY(0) scale(1); opacity:.55; }
    50%      { transform: translateY(-18px) scale(1.08); opacity:.22; }
}
@keyframes pulseRing {
    0%   { box-shadow: 0 0 0 0 var(--ring-color, rgba(99,102,241,.5)); }
    70%  { box-shadow: 0 0 0 10px transparent; }
    100% { box-shadow: 0 0 0 0 transparent; }
}
@keyframes slideInPane {
    from { opacity: 0; transform: translateX(22px) scale(.985); }
    to   { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes slideInPaneBack {
    from { opacity: 0; transform: translateX(-22px) scale(.985); }
    to   { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes roleCardIn {
    from { opacity: 0; transform: translateY(16px) scale(.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes stepCirclePop {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.22); }
    70%  { transform: scale(.93); }
    100% { transform: scale(1); }
}
@keyframes checkDraw {
    from { transform: scale(0) rotate(-45deg); opacity: 0; }
    to   { transform: scale(1) rotate(0deg); opacity: 1; }
}
@keyframes btnShine {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}
@keyframes ripple {
    to { transform: scale(4); opacity: 0; }
}
@keyframes gradientShift {
    0%,100% { background-position: 0% 50%; }
    50%      { background-position: 100% 50%; }
}
@keyframes particleDrift {
    0%   { transform: translateY(0) translateX(0) scale(1); opacity: .7; }
    33%  { transform: translateY(-60px) translateX(20px) scale(1.1); opacity: .4; }
    66%  { transform: translateY(-30px) translateX(-15px) scale(.9); opacity: .6; }
    100% { transform: translateY(-90px) translateX(5px) scale(.8); opacity: 0; }
}

/* Hero section animate-in */
.apply-hero-inner { animation: heroFadeUp .7s cubic-bezier(.22,1,.36,1) both; }
.apply-eyebrow    { animation: heroFadeUp .6s .05s cubic-bezier(.22,1,.36,1) both; }
.apply-title      { animation: heroFadeUp .65s .12s cubic-bezier(.22,1,.36,1) both; }
.apply-subtitle   { animation: heroFadeUp .65s .22s cubic-bezier(.22,1,.36,1) both; }
.apply-steps-bar  { animation: heroFadeUp .65s .32s cubic-bezier(.22,1,.36,1) both; }

/* Eyebrow shimmer */
.apply-eyebrow {
    background: linear-gradient(90deg,
        rgba(99,102,241,.12) 0%,
        rgba(129,140,248,.24) 40%,
        rgba(199,210,254,.38) 50%,
        rgba(129,140,248,.24) 60%,
        rgba(99,102,241,.12) 100%);
    background-size: 200% auto;
    animation: heroFadeUp .6s .05s cubic-bezier(.22,1,.36,1) both,
               eyebrowShimmer 3.5s 1s linear infinite;
}

/* Animated background gradient in hero */
body.apply-page.apply-wizard-page {
    background:
        radial-gradient(1000px 520px at 85% 0%, rgba(99, 102, 241, .22), transparent 58%),
        radial-gradient(720px 420px at 10% 82%, rgba(56, 189, 248, .12), transparent 60%),
        linear-gradient(180deg, #05030b 0%, #06040d 45%, #05040b 100%);
    background-size: 200% 200%, 200% 200%, 100%;
    animation: gradientShift 12s ease infinite;
}

/* Role cards staggered entrance */
.role-card { opacity: 0; animation: roleCardIn .5s cubic-bezier(.22,1,.36,1) forwards; }
.role-card:nth-child(1) { animation-delay: .08s; }
.role-card:nth-child(2) { animation-delay: .16s; }
.role-card:nth-child(3) { animation-delay: .24s; }
.role-card:nth-child(4) { animation-delay: .32s; }
.role-card:nth-child(5) { animation-delay: .40s; }

/* Role card selected: micro-bounce */
.role-card.selected {
    animation: none !important;
    opacity: 1;
}

/* Wizard pane transitions */
.wizard-pane.active {
    animation: slideInPane .28s cubic-bezier(.22,1,.36,1);
}
.wizard-pane.active.from-back {
    animation: slideInPaneBack .28s cubic-bezier(.22,1,.36,1);
}

/* Step circle: pop when becoming active/done */
.apply-stp.active .apply-stp-circle {
    animation: stepCirclePop .35s cubic-bezier(.34,1.56,.64,1);
}
.apply-stp.done .apply-stp-circle {
    animation: checkDraw .28s cubic-bezier(.34,1.56,.64,1);
}

/* Step line: animate fill */
.apply-stp-line {
    transition: background .4s ease, width .4s ease;
    position: relative;
    overflow: hidden;
}
.apply-stp-line.done::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(74,222,128,.60), rgba(74,222,128,.20));
    animation: none;
}

/* Continue / Next button: animated gradient shine */
.btn-next, .btn-submit {
    background-size: 200% auto;
    transition: transform .14s ease, box-shadow .14s ease, background-position .5s ease;
    position: relative;
    overflow: hidden;
}
.btn-next::after, .btn-submit::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, transparent 20%, rgba(255,255,255,.12) 50%, transparent 80%);
    background-size: 200% auto;
    opacity: 0;
    transition: opacity .2s;
    pointer-events: none;
}
.btn-next:hover::after, .btn-submit:hover::after {
    opacity: 1;
    animation: btnShine 1s linear infinite;
}

/* Ripple on button click */
.btn-next .ripple, .btn-submit .ripple {
    position: absolute;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    margin-top: -20px;
    margin-left: -20px;
    background: rgba(255,255,255,.25);
    transform: scale(0);
    animation: ripple .5s linear;
    pointer-events: none;
}

/* Input focus: glow pulse */
.apply-input:focus,
.apply-select:focus,
.apply-textarea:focus,
.searchdrop-trigger.open {
    animation: none;
    transition: border-color .15s ease, background .15s ease, box-shadow .2s ease;
}

/* Floating background particles (injected via JS) */
.apply-particle {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: particleDrift linear infinite;
    z-index: 1;
}

/* Apply-step-bar line transition */
.apply-stp-line {
    transition: background .5s ease;
}

/* Success badge bounce */
@keyframes successBounce {
    0%   { transform: scale(.3) rotate(-15deg); opacity: 0; }
    60%  { transform: scale(1.15) rotate(5deg); opacity: 1; }
    80%  { transform: scale(.95) rotate(-2deg); }
    100% { transform: scale(1) rotate(0deg); }
}
.apply-success.visible .apply-success-badge {
    animation: successBounce .65s cubic-bezier(.34,1.56,.64,1) both;
}
.apply-success.visible h3 {
    animation: heroFadeUp .5s .2s cubic-bezier(.22,1,.36,1) both;
    opacity: 0;
}
.apply-success.visible p {
    animation: heroFadeUp .5s .35s cubic-bezier(.22,1,.36,1) both;
    opacity: 0;
}

/* ═══════════════════════════════════════════════
   BOOST GAME GRID — game picker on booster step
═══════════════════════════════════════════════ */

.boost-game-section {
    margin-bottom: 28px;
}

.boost-game-head {
    margin-bottom: 16px;
}

.boost-game-head label {
    display: block;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: rgba(255,255,255,.50);
    margin-bottom: 6px;
}

.boost-game-head label .req {
    color: #fb7185;
}

.boost-game-head p {
    margin: 0;
    font-size: 14px;
    color: rgba(255,255,255,.38);
    font-weight: 500;
    line-height: 1.6;
}

.boost-game-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 10px;
}

.boost-game-option {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 14px;
    border-radius: 14px;
    border: 1.5px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.62);
    font-size: 13.5px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    text-align: left;
    line-height: 1.3;
    transition: border-color .15s ease, background .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
    user-select: none;
    overflow: hidden;
}

.boost-game-option::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(99,102,241,.14) 0%, rgba(99,102,241,.08) 100%);
    opacity: 0;
    transition: opacity .15s ease;
    pointer-events: none;
}

.boost-game-option span {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 7px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 900;
    color: rgba(255,255,255,.30);
    letter-spacing: 0;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
    overflow: hidden;
}

.boost-game-option span img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 5px;
}

.boost-game-option:hover {
    border-color: rgba(129,140,248,.30);
    background: rgba(99,102,241,.07);
    color: rgba(255,255,255,.88);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99,102,241,.12);
}

.boost-game-option:hover::before {
    opacity: 1;
}

.boost-game-option:hover span {
    background: rgba(99,102,241,.18);
    border-color: rgba(129,140,248,.28);
    color: #c7d2fe;
}

.boost-game-option.selected {
    border-color: #6366f1;
    background: rgba(99,102,241,.13);
    color: #e0e7ff;
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(99,102,241,.22), 0 0 0 1px rgba(129,140,248,.20);
}

.boost-game-option.selected::before {
    opacity: 1;
}

.boost-game-option.selected span {
    background: rgba(99,102,241,.30);
    border-color: #818cf8;
    color: #c7d2fe;
}

/* Role-specific selected color theming */
[data-active-role="val_booster"] .boost-game-option:hover,
[data-active-role="val_booster"] .boost-game-option.selected {
    border-color: #ff4655;
    box-shadow: 0 8px 28px rgba(255,70,85,.18), 0 0 0 1px rgba(255,70,85,.20);
    background: rgba(255,70,85,.10);
    color: #fecdd3;
}
[data-active-role="val_booster"] .boost-game-option:hover span,
[data-active-role="val_booster"] .boost-game-option.selected span {
    background: rgba(255,70,85,.22);
    border-color: #ff4655;
    color: #fda4af;
}
[data-active-role="val_booster"] .boost-game-option::before {
    background: linear-gradient(135deg, rgba(255,70,85,.12) 0%, rgba(220,38,38,.07) 100%);
}

[data-active-role="lol_booster"] .boost-game-option:hover,
[data-active-role="lol_booster"] .boost-game-option.selected {
    border-color: #c89b3c;
    box-shadow: 0 8px 28px rgba(200,155,60,.18), 0 0 0 1px rgba(200,155,60,.20);
    background: rgba(200,155,60,.10);
    color: #fef3c7;
}
[data-active-role="lol_booster"] .boost-game-option:hover span,
[data-active-role="lol_booster"] .boost-game-option.selected span {
    background: rgba(200,155,60,.22);
    border-color: #c89b3c;
    color: #fcd34d;
}
[data-active-role="lol_booster"] .boost-game-option::before {
    background: linear-gradient(135deg, rgba(200,155,60,.12) 0%, rgba(169,112,22,.07) 100%);
}

[data-active-role="tft_booster"] .boost-game-option:hover,
[data-active-role="tft_booster"] .boost-game-option.selected {
    border-color: #0bc4e3;
    box-shadow: 0 8px 28px rgba(11,196,227,.18), 0 0 0 1px rgba(11,196,227,.20);
    background: rgba(11,196,227,.09);
    color: #cffafe;
}
[data-active-role="tft_booster"] .boost-game-option:hover span,
[data-active-role="tft_booster"] .boost-game-option.selected span {
    background: rgba(11,196,227,.20);
    border-color: #0bc4e3;
    color: #67e8f9;
}
[data-active-role="tft_booster"] .boost-game-option::before {
    background: linear-gradient(135deg, rgba(11,196,227,.12) 0%, rgba(8,145,178,.07) 100%);
}

/* Staggered entrance animation */
@keyframes gameOptionIn {
    from { opacity: 0; transform: translateY(10px) scale(.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.boost-game-option {
    opacity: 0;
    animation: gameOptionIn .35s cubic-bezier(.22,1,.36,1) forwards;
}

.boost-game-option:nth-child(1)  { animation-delay: .03s; }
.boost-game-option:nth-child(2)  { animation-delay: .06s; }
.boost-game-option:nth-child(3)  { animation-delay: .09s; }
.boost-game-option:nth-child(4)  { animation-delay: .12s; }
.boost-game-option:nth-child(5)  { animation-delay: .15s; }
.boost-game-option:nth-child(6)  { animation-delay: .18s; }
.boost-game-option:nth-child(7)  { animation-delay: .21s; }
.boost-game-option:nth-child(8)  { animation-delay: .24s; }
.boost-game-option:nth-child(9)  { animation-delay: .27s; }
.boost-game-option:nth-child(10) { animation-delay: .30s; }
.boost-game-option:nth-child(11) { animation-delay: .33s; }
.boost-game-option:nth-child(12) { animation-delay: .36s; }
.boost-game-option:nth-child(13) { animation-delay: .39s; }
.boost-game-option:nth-child(14) { animation-delay: .42s; }
.boost-game-option:nth-child(15) { animation-delay: .45s; }
.boost-game-option:nth-child(16) { animation-delay: .48s; }
.boost-game-option:nth-child(17) { animation-delay: .51s; }

.boost-game-option.selected {
    animation: none;
    opacity: 1;
}

/* Mobile */
@media (max-width: 640px) {
    .boost-game-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .boost-game-option {
        padding: 10px 12px;
        font-size: 12.5px;
        border-radius: 12px;
        gap: 8px;
    }

    .boost-game-option span {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        font-size: 9px;
    }
}

@media (max-width: 380px) {
    .boost-game-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 7px;
    }

    .boost-game-option {
        padding: 9px 10px;
        font-size: 12px;
    }
}
</style>
</head>
<body class="apply-page apply-wizard-page">
<noscript>
  <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-596N9MJ3" height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<header class="apply-custom-header">
    <div class="apply-custom-header__inner">
        <a href="/" class="apply-custom-header__brand" aria-label="LOLBOOST.GG">
            <img src="<?= $logoIconSvg ?>" alt="LOLBOOST.GG icon" class="apply-brand-icon">
            <span class="apply-brand-text">LOLBOOST.GG</span>
        </a>

        <div class="apply-custom-header__nav">
            <a href="/jobs" class="apply-header-btn">
                <i class="fa-solid fa-arrow-left"></i>
                <span><?= t('Back to Jobs') ?></span>
            </a>
        </div>
    </div>
</header>

<div class="apply-shell">
    <section class="apply-hero">
        <div class="apply-hero-inner">
            <div class="apply-eyebrow">
                <i class="fa-duotone fa-sparkles"></i>
                <?= t('Now hiring') ?>
            </div>
            <h1 class="apply-title">
                <?= t('Join the team that players') ?><br>
                <span class="accent"><?= t('actually remember') ?></span>
            </h1>
            <p class="apply-subtitle">
                <?= t('Pick your role, go step by step through the application, and send us everything we need in one clean flow. Most applications only take a few minutes.') ?>
            </p>

            <!-- Steps bar lives here in the hero, open in the page -->
            <div class="apply-steps-bar" id="applyStepsBar">
                <div class="apply-stp active" data-astp="1">
                    <div class="apply-stp-circle">1</div>
                    <div class="apply-stp-label"><?= t('Role') ?></div>
                </div>
                <div class="apply-stp-line" id="astpLine12"></div>
                <div class="apply-stp" data-astp="2">
                    <div class="apply-stp-circle">2</div>
                    <div class="apply-stp-label"><?= t('Personal') ?></div>
                </div>
                <div class="apply-stp-line" id="astpLine23"></div>
                <div class="apply-stp" data-astp="3">
                    <div class="apply-stp-circle">3</div>
                    <div class="apply-stp-label"><?= t('Details') ?></div>
                </div>
                <div class="apply-stp-line" id="astpLine34"></div>
                <div class="apply-stp" data-astp="4">
                    <div class="apply-stp-circle">4</div>
                    <div class="apply-stp-label"><?= t('About') ?></div>
                </div>
                <div class="apply-stp-line" id="astpLine45"></div>
                <div class="apply-stp" data-astp="5">
                    <div class="apply-stp-circle">5</div>
                    <div class="apply-stp-label"><?= t('Review') ?></div>
                </div>
            </div>
        </div>
    </section>

    <section class="apply-main" id="applyWizardRoot">
        <div class="apply-grid">
            <aside class="apply-sidebar">
                <!-- sidebar hidden, kept for JS compat -->
            </aside>

            <div class="apply-card apply-wizard-pane-wrap" data-active-role="">
                <!-- Horizontal step indicator -->
                <div class="wizard-steps" id="wizardSteps">
                    <div class="wstep active" data-wstep="1">
                        <div class="wstep-circle">1</div>
                        <div class="wstep-label"><?= t('Role') ?></div>
                    </div>
                    <div class="wstep-line" id="wline12"></div>
                    <div class="wstep" data-wstep="2">
                        <div class="wstep-circle">2</div>
                        <div class="wstep-label"><?= t('Personal') ?></div>
                    </div>
                    <div class="wstep-line" id="wline23"></div>
                    <div class="wstep" data-wstep="3">
                        <div class="wstep-circle">3</div>
                        <div class="wstep-label"><?= t('Details') ?></div>
                    </div>
                    <div class="wstep-line" id="wline34"></div>
                    <div class="wstep" data-wstep="4">
                        <div class="wstep-circle">4</div>
                        <div class="wstep-label"><?= t('About') ?></div>
                    </div>
                    <div class="wstep-line" id="wline45"></div>
                    <div class="wstep" data-wstep="5">
                        <div class="wstep-circle">5</div>
                        <div class="wstep-label"><?= t('Review') ?></div>
                    </div>
                </div>

                <div class="wizard-topbar">
                    <div class="topbar-copy">
                        <h3><?= t('Step by step application') ?></h3>
                        <p id="wizardIntro"><?= t('Start by selecting the role you want to apply for.') ?></p>
                    </div>
                    <div class="progress-wrap">
                        <div class="progress-line">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText"><?= t('Step 1 of 4') ?></div>
                    </div>
                </div>

                <form id="jobApplicationForm" method="POST" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="submit_job_application">
                    <input type="hidden" name="role" id="fieldRole" value="">
                    <input type="hidden" name="boost_game" id="fieldBoostGame" value="">

                    <div class="wizard-pane active" data-pane="1">
                        <div class="wizard-head">
                            <div class="step-pill-badge"><?= t('Step 1 of 4') ?></div>
                            <h4><?= t('Choose your role') ?></h4>
                            <p><?= t('Pick the role that best matches what you want to do with us. The form will instantly adapt to that role.') ?></p>
                        </div>

                        <div class="role-grid">
                            <label class="role-card" data-role="booster">
                                <input type="radio" name="role_select" value="booster">
                                <div class="role-icon">
                                    <i class="fa-duotone fa-bolt" style="font-size:24px;color:#a78bfa;"></i>
                                </div>
                                <div class="role-name">Booster</div>
                                <div class="role-sub">Choose your game later</div>
                            </label>

                            <label class="role-card" data-role="gg_girl">
                                <input type="radio" name="role_select" value="gg_girl">
                                <div class="role-icon">
                                    <img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt="GG Girl" style="width:30px;height:30px;object-fit:contain;">
                                </div>
                                <div class="role-name">GG Girl</div>
                                <div class="role-sub">Gaming Companion</div>
                            </label>

                            <label class="role-card" data-role="seller">
                                <input type="radio" name="role_select" value="seller">
                                <div class="role-icon">
                                    <i class="fa-duotone fa-store" style="font-size:22px;color:#4ade80;"></i>
                                </div>
                                <div class="role-name">Seller</div>
                                <div class="role-sub">Account Sales</div>
                            </label>
                        </div>

                        <div class="wizard-nav">
                            <button type="button" class="btn-ghost" disabled><?= t('Back') ?></button>
                            <button type="button" class="btn-next" id="btnToStep2">
                                <i class="fa-duotone fa-arrow-right"></i>
                                <?= t('Continue') ?>
                            </button>
                        </div>
                    </div>

                    <div class="wizard-pane" data-pane="2">
                        <div class="wizard-head">
                            <div class="step-pill-badge"><?= t('Step 2 of 4') ?></div>
                            <h4 id="pane2Title"><?= t('Personal information') ?></h4>
                            <p id="pane2Copy"><?= t('Tell us who you are and how we can reach you.') ?></p>
                        </div>

                        <div class="form-block">
                            <h5 class="form-block-title" id="personalSectionTitle"><?= t('Personal Information') ?></h5>
                            <div class="form-grid">
                                <div class="apply-field" id="wrapFullname">
                                    <label id="labelFullname"><?= t('Full Name') ?> <span class="req">*</span></label>
                                    <input type="text" name="fullname" class="apply-input" id="fieldFullname" placeholder="Max Mustermann" required>
                                    <div class="field-error" data-error-for="fullname"></div>
                                </div>

                                <div class="apply-field" id="wrapEmail">
                                    <label id="labelEmail"><?= t('Email Address') ?> <span class="req">*</span></label>
                                    <input type="email" name="email" class="apply-input" id="fieldEmail" placeholder="you@example.com" required>
                                    <div class="field-error" data-error-for="email"></div>
                                </div>

                                <div class="apply-field" id="wrapDiscord">
                                    <label id="labelDiscord"><?= t('Discord Tag') ?> <span class="req">*</span></label>
                                    <input type="text" name="discord_tag" class="apply-input" id="fieldDiscord" placeholder="username or user#1234" required>
                                    <div class="field-error" data-error-for="discord_tag"></div>
                                </div>

                                <div class="apply-field" id="wrapAge">
                                    <label id="labelAge"><?= t('Age') ?></label>
                                    <input type="number" name="age" class="apply-input" id="fieldAge" placeholder="18" min="16" max="60">
                                    <div class="field-error" data-error-for="age"></div>
                                </div>

                                <div class="apply-field" id="wrapCountry">
                                    <label id="labelCountry"><?= t('Country') ?></label>
                                    <div class="searchdrop-wrap" id="countryDropWrap">
                                        <div class="searchdrop-trigger" id="countryTrigger" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                            <span class="searchdrop-display" id="countryDisplay"><?= t('Select country…') ?></span>
                                            <i class="fa-solid fa-chevron-down searchdrop-arrow"></i>
                                        </div>
                                        <div class="searchdrop-panel" id="countryPanel" role="listbox">
                                            <div class="searchdrop-search-wrap">
                                                <i class="fa-solid fa-magnifying-glass searchdrop-search-icon"></i>
                                                <input type="text" class="searchdrop-search" id="countrySearch" placeholder="<?= t('Search country…') ?>" autocomplete="off">
                                            </div>
                                            <ul class="searchdrop-list" id="countryList"></ul>
                                        </div>
                                        <input type="hidden" name="country" id="fieldCountry">
                                    </div>
                                    <div class="field-error" data-error-for="country"></div>
                                </div>

                                <div class="apply-field" id="wrapLanguages">
                                    <label id="labelLanguages"><?= t('Languages') ?></label>
                                    <div class="searchdrop-wrap searchdrop-multi" id="langDropWrap">
                                        <div class="searchdrop-trigger searchdrop-trigger-tags" id="langTrigger" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                            <div class="searchdrop-tags" id="langTags">
                                                <span class="searchdrop-placeholder" id="langPlaceholder"><?= t('Select languages…') ?></span>
                                            </div>
                                            <i class="fa-solid fa-chevron-down searchdrop-arrow"></i>
                                        </div>
                                        <div class="searchdrop-panel" id="langPanel" role="listbox">
                                            <div class="searchdrop-search-wrap">
                                                <i class="fa-solid fa-magnifying-glass searchdrop-search-icon"></i>
                                                <input type="text" class="searchdrop-search" id="langSearch" placeholder="<?= t('Search language…') ?>" autocomplete="off">
                                            </div>
                                            <ul class="searchdrop-list" id="langList"></ul>
                                        </div>
                                        <input type="hidden" name="languages" id="fieldLanguages">
                                    </div>
                                    <div class="field-error" data-error-for="languages"></div>
                                </div>
                            </div>
                        </div>

                        <div class="wizard-nav">
                            <button type="button" class="btn-ghost" data-prev-step="1"><?= t('Back') ?></button>
                            <button type="button" class="btn-next" id="btnToStep3">
                                <i class="fa-duotone fa-arrow-right"></i>
                                <?= t('Continue') ?>
                            </button>
                        </div>
                    </div>

                    <div class="wizard-pane" data-pane="3">
                        <div class="wizard-head">
                            <div class="step-pill-badge"><?= t('Step 3 of 4') ?></div>
                            <h4 id="pane3Title"><?= t('Role details') ?></h4>
                            <p id="pane3Copy"><?= t('Now show us what you can offer and why you fit this role.') ?></p>
                        </div>

                        <div class="form-block">
                            <h5 class="form-block-title" id="detailsSectionTitle"><?= t('Details') ?></h5>

                            <div id="boosterFields">
                                <div class="boost-game-section" id="boostGameSection">
                                    <div class="boost-game-head">
                                        <label><?= t('Which game do you plan on boosting on?') ?> <span class="req">*</span></label>
                                        <p><?= t('Select the main game you want to boost for. You can mention additional games later in the form.') ?></p>
                                    </div>
                                    <div class="boost-game-grid" id="boostGameGrid">
                                        <?php
                                            $applyBoostGames = [];
                                            try {
                                                global $db;
                                                $applyBoostGames = $db->run(
                                                    "SELECT DISTINCT g.name, g.slug, g.icon
                                                     FROM games g
                                                     INNER JOIN game_services gs ON gs.game_id = g.id
                                                     WHERE g.status = 1
                                                       AND gs.status = 1
                                                       AND gs.service_type = 'boosting'
                                                     ORDER BY g.sort_order ASC, g.name ASC"
                                                ) ?: [];
                                            } catch (\Throwable $e) {
                                                $applyBoostGames = [];
                                            }
                                        ?>
                                        <?php foreach ($applyBoostGames as $applyGame): ?>
                                            <?php
                                                $applyGameName = (string)($applyGame['name'] ?? '');
                                                if ($applyGameName === '') continue;
                                                $applyGameIconUrl = function_exists('util_game_icon_url') ? util_game_icon_url((string)($applyGame['slug'] ?? '')) : '';
                                            ?>
                                            <button type="button" class="boost-game-option" data-game="<?= htmlspecialchars($applyGameName, ENT_QUOTES) ?>" data-slug="<?= htmlspecialchars((string)($applyGame['slug'] ?? ''), ENT_QUOTES) ?>">
                                                <span><?php if ($applyGameIconUrl !== ''): ?><img src="<?= htmlspecialchars($applyGameIconUrl, ENT_QUOTES) ?>" alt=""><?php endif; ?></span>
                                                <?= htmlspecialchars($applyGameName, ENT_QUOTES) ?>
                                            </button>
                                        <?php endforeach; ?>
                                        <button type="button" class="boost-game-option" data-game="Other" id="boostGameOtherBtn"><span><i class="fa-solid fa-ellipsis"></i></span>Other</button>
                                    </div>
                                    <div class="apply-field" id="wrapBoostGameOther" style="display:none;margin-top:10px;">
                                        <label><?= t('Please specify the game') ?></label>
                                        <input type="text" name="boost_game_other" class="apply-input" id="fieldBoostGameOther" placeholder="<?= t('Game name') ?>">
                                    </div>
                                    <div class="field-error" data-error-for="boost_game"></div>
                                </div>
                                <div class="form-grid">
                                    <div class="apply-field" id="wrapIngame">
                                        <label id="labelIngame"><?= t('In Game Name') ?></label>
                                        <input type="text" name="ingame_name" class="apply-input" id="fieldIngame" placeholder="YourSummonerName">
                                        <div class="field-error" data-error-for="ingame_name"></div>
                                    </div>

                                    <div class="apply-field" id="wrapServer">
                                        <label id="labelServer"><?= t('Server / Region') ?></label>
                                        <div class="searchdrop-wrap" id="serverDropWrap">
                                            <div class="searchdrop-trigger" id="serverTrigger" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                                <span class="searchdrop-display placeholder" id="serverDisplay"><?= t('Select Regionâ¦') ?></span>
                                                <i class="fa-solid fa-chevron-down searchdrop-arrow"></i>
                                            </div>
                                            <div class="searchdrop-panel" id="serverPanel" role="listbox">
                                                <ul class="searchdrop-list" id="serverList"></ul>
                                            </div>
                                            <input type="hidden" name="server_region" id="fieldServer">
                                        </div>
                                        <div class="field-error" data-error-for="server_region"></div>
                                    </div>

                                    <div class="apply-field" id="wrapCurrentRank">
                                        <label id="labelCurrentRank"><?= t('Current Rank') ?></label>
                                        <input type="text" name="current_rank" class="apply-input" id="fieldCurrentRank" placeholder="Diamond II">
                                        <div class="field-error" data-error-for="current_rank"></div>
                                    </div>

                                    <div class="apply-field" id="wrapPeakRank">
                                        <label id="labelPeakRank"><?= t('Peak Rank') ?></label>
                                        <input type="text" name="peak_rank" class="apply-input" id="fieldPeakRank" placeholder="Master 200LP">
                                        <div class="field-error" data-error-for="peak_rank"></div>
                                    </div>
                                </div>

                                <div class="apply-field" id="wrapChampPool" style="margin-top:16px;">
                                    <label id="labelChampPool"><?= t('Champion Pool / Main Roles') ?></label>
                                    <input type="text" name="champ_pool" class="apply-input" id="fieldChampPool" placeholder="Mid, ADC, Support">
                                    <div class="field-error" data-error-for="champ_pool"></div>
                                </div>

                                <div class="vpn-toggle-card" id="wrapVpn">
                                    <div class="vpn-toggle-info">
                                        <div class="vpn-toggle-icon">
                                            <i class="fa-duotone fa-shield-check"></i>
                                        </div>
                                        <div class="vpn-toggle-text">
                                            <span class="vpn-toggle-label" id="labelVpn"><?= t('VPN Usage') ?></span>
                                            <span class="vpn-toggle-sub" id="vpnToggleSub"><?= t('I have a VPN and will use it during boosting sessions') ?></span>
                                        </div>
                                    </div>
                                    <div class="vpn-toggle-btns" id="vpnToggleBtns">
                                        <button type="button" class="vpn-btn vpn-btn-no active" id="vpnBtnNo" data-val="">
                                            <i class="fa-solid fa-xmark"></i> No
                                        </button>
                                        <button type="button" class="vpn-btn vpn-btn-yes" id="vpnBtnYes" data-val="1">
                                            <i class="fa-solid fa-check"></i> Yes
                                        </button>
                                    </div>
                                    <input type="hidden" name="has_vpn" id="hasVpn" value="">
                                </div>
                            </div>
                        </div>

                        <div class="wizard-nav">
                            <button type="button" class="btn-ghost" data-prev-step="2"><?= t('Back') ?></button>
                            <button type="button" class="btn-next" id="btnToStep4">
                                <i class="fa-duotone fa-arrow-right"></i>
                                <?= t('Continue') ?>
                            </button>
                        </div>
                    </div>

                    <div class="wizard-pane" data-pane="4">
                        <div class="wizard-head">
                            <div class="step-pill-badge"><?= t('Step 4 of 5') ?></div>
                            <h4 id="pane4Title"><?= t('About you') ?></h4>
                            <p id="pane4Copy"><?= t('Tell us more about your availability, experience and motivation.') ?></p>
                        </div>

                        <div class="form-block">
                            <h5 class="form-block-title" id="aboutSectionTitle"><?= t('About You') ?></h5>

                            <div class="apply-field" id="wrapAvailability">
                                <label id="labelAvailability"><?= t('Availability') ?></label>
                                <input type="text" name="availability" class="apply-input" id="fieldAvailability" placeholder="Mon–Fri 6pm–midnight, weekends flexible">
                                <div class="field-error" data-error-for="availability"></div>
                            </div>

                            <div class="apply-field" id="wrapExperience">
                                <label id="labelExperience"><?= t('Previous Experience') ?></label>
                                <textarea name="experience" class="apply-textarea" id="fieldExperience" placeholder="<?= t('Tell us about any previous boosting, selling or gaming experience...') ?>"></textarea>
                                <div class="field-error" data-error-for="experience"></div>
                            </div>

                            <div class="apply-field" id="wrapMotivation">
                                <label id="labelMotivation"><?= t('Why do you want to join?') ?> <span class="req">*</span></label>
                                <textarea name="motivation" class="apply-textarea" id="fieldMotivation" placeholder="<?= t('Tell us why you would be a great fit for this role...') ?>" required></textarea>
                                <div class="field-error" data-error-for="motivation"></div>
                            </div>

                            <div class="apply-field" id="wrapReferral">
                                <label id="labelReferral"><?= t('How did you hear about us?') ?></label>
                                <input type="text" name="referral" class="apply-input" id="fieldReferral" placeholder="Discord, Reddit, YouTube, Friend">
                                <div class="field-error" data-error-for="referral"></div>
                            </div>

                            <div class="helper-note" id="roleApplyHint">
                                <?= t('Choose a role to see tailored hints here.') ?>
                            </div>
                        </div>

                        <div class="wizard-nav">
                            <button type="button" class="btn-ghost" data-prev-step="3"><?= t('Back') ?></button>
                            <button type="button" class="btn-next" id="btnToStep5">
                                <i class="fa-duotone fa-check"></i>
                                <?= t('Review application') ?>
                            </button>
                        </div>
                    </div>

                    <div class="wizard-pane" data-pane="5">
                        <div class="wizard-head">
                            <div class="step-pill-badge"><?= t('Step 5 of 5') ?></div>
                            <h4><?= t('Review & submit') ?></h4>
                            <p><?= t('Please check your details once more before sending your application.') ?></p>
                        </div>

                        <div class="form-block">
                            <h5 class="form-block-title"><?= t('Application summary') ?></h5>
                            <div class="review-card" id="reviewCard"></div>
                        </div>

                        <div class="wizard-nav">
                            <button type="button" class="btn-ghost" data-prev-step="4"><?= t('Back') ?></button>
                            <button type="submit" class="btn-submit" id="applySubmitBtn">
                                <i class="fa-duotone fa-paper-plane"></i>
                                <span><?= t('Submit Application') ?></span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="apply-success" id="applySuccess">
                    <div class="apply-success-badge">✅</div>
                    <h3><?= t('Application received') ?></h3>
                    <p><?= t('Thanks for applying. Our team will review your application and contact you via Discord or email as soon as possible. Keep an eye on your inbox and requests.') ?></p>
                </div>
            </div>
        </div>
    </section>
</div>

<footer class="apply-custom-footer">
    <div class="apply-custom-footer__inner">
        <a href="/" class="apply-custom-footer__brand" aria-label="LOLBOOST.GG">
            <img src="<?= $logoIconSvg ?>" alt="LOLBOOST.GG icon" class="apply-brand-icon">
            <span class="apply-brand-text">LOLBOOST.GG</span>
        </a>

        <div class="apply-custom-footer__links">
            <a href="/terms-and-conditions"><?= t('Terms of Service') ?></a>
            <a href="/privacy-policy"><?= t('Privacy Policy') ?></a>
            <a href="/contact"><?= t('Contact') ?></a>
        </div>

        <div class="apply-custom-footer__copy">
            © <?= date('Y') ?> LB Gaming Services LTD.
        </div>
    </div>
</footer>

<script>
/* ── SEARCHABLE DROPDOWN COMPONENT ── */
(function () {
    var COUNTRIES = [
        {v:'AF',l:'Afghanistan',f:'🇦🇫'},{v:'AL',l:'Albania',f:'🇦🇱'},{v:'DZ',l:'Algeria',f:'🇩🇿'},
        {v:'AD',l:'Andorra',f:'🇦🇩'},{v:'AO',l:'Angola',f:'🇦🇴'},{v:'AG',l:'Antigua & Barbuda',f:'🇦🇬'},
        {v:'AR',l:'Argentina',f:'🇦🇷'},{v:'AM',l:'Armenia',f:'🇦🇲'},{v:'AU',l:'Australia',f:'🇦🇺'},
        {v:'AT',l:'Austria',f:'🇦🇹'},{v:'AZ',l:'Azerbaijan',f:'🇦🇿'},{v:'BS',l:'Bahamas',f:'🇧🇸'},
        {v:'BH',l:'Bahrain',f:'🇧🇭'},{v:'BD',l:'Bangladesh',f:'🇧🇩'},{v:'BB',l:'Barbados',f:'🇧🇧'},
        {v:'BY',l:'Belarus',f:'🇧🇾'},{v:'BE',l:'Belgium',f:'🇧🇪'},{v:'BZ',l:'Belize',f:'🇧🇿'},
        {v:'BJ',l:'Benin',f:'🇧🇯'},{v:'BT',l:'Bhutan',f:'🇧🇹'},{v:'BO',l:'Bolivia',f:'🇧🇴'},
        {v:'BA',l:'Bosnia & Herzegovina',f:'🇧🇦'},{v:'BW',l:'Botswana',f:'🇧🇼'},{v:'BR',l:'Brazil',f:'🇧🇷'},
        {v:'BN',l:'Brunei',f:'🇧🇳'},{v:'BG',l:'Bulgaria',f:'🇧🇬'},{v:'BF',l:'Burkina Faso',f:'🇧🇫'},
        {v:'BI',l:'Burundi',f:'🇧🇮'},{v:'CV',l:'Cabo Verde',f:'🇨🇻'},{v:'KH',l:'Cambodia',f:'🇰🇭'},
        {v:'CM',l:'Cameroon',f:'🇨🇲'},{v:'CA',l:'Canada',f:'🇨🇦'},{v:'CF',l:'Central African Republic',f:'🇨🇫'},
        {v:'TD',l:'Chad',f:'🇹🇩'},{v:'CL',l:'Chile',f:'🇨🇱'},{v:'CN',l:'China',f:'🇨🇳'},
        {v:'CO',l:'Colombia',f:'🇨🇴'},{v:'KM',l:'Comoros',f:'🇰🇲'},{v:'CG',l:'Congo',f:'🇨🇬'},
        {v:'CR',l:'Costa Rica',f:'🇨🇷'},{v:'HR',l:'Croatia',f:'🇭🇷'},{v:'CU',l:'Cuba',f:'🇨🇺'},
        {v:'CY',l:'Cyprus',f:'🇨🇾'},{v:'CZ',l:'Czech Republic',f:'🇨🇿'},{v:'DK',l:'Denmark',f:'🇩🇰'},
        {v:'DJ',l:'Djibouti',f:'🇩🇯'},{v:'DO',l:'Dominican Republic',f:'🇩🇴'},{v:'EC',l:'Ecuador',f:'🇪🇨'},
        {v:'EG',l:'Egypt',f:'🇪🇬'},{v:'SV',l:'El Salvador',f:'🇸🇻'},{v:'GQ',l:'Equatorial Guinea',f:'🇬🇶'},
        {v:'ER',l:'Eritrea',f:'🇪🇷'},{v:'EE',l:'Estonia',f:'🇪🇪'},{v:'SZ',l:'Eswatini',f:'🇸🇿'},
        {v:'ET',l:'Ethiopia',f:'🇪🇹'},{v:'FJ',l:'Fiji',f:'🇫🇯'},{v:'FI',l:'Finland',f:'🇫🇮'},
        {v:'FR',l:'France',f:'🇫🇷'},{v:'GA',l:'Gabon',f:'🇬🇦'},{v:'GM',l:'Gambia',f:'🇬🇲'},
        {v:'GE',l:'Georgia',f:'🇬🇪'},{v:'DE',l:'Germany',f:'🇩🇪'},{v:'GH',l:'Ghana',f:'🇬🇭'},
        {v:'GR',l:'Greece',f:'🇬🇷'},{v:'GT',l:'Guatemala',f:'🇬🇹'},{v:'GN',l:'Guinea',f:'🇬🇳'},
        {v:'GW',l:'Guinea-Bissau',f:'🇬🇼'},{v:'GY',l:'Guyana',f:'🇬🇾'},{v:'HT',l:'Haiti',f:'🇭🇹'},
        {v:'HN',l:'Honduras',f:'🇭🇳'},{v:'HU',l:'Hungary',f:'🇭🇺'},{v:'IS',l:'Iceland',f:'🇮🇸'},
        {v:'IN',l:'India',f:'🇮🇳'},{v:'ID',l:'Indonesia',f:'🇮🇩'},{v:'IR',l:'Iran',f:'🇮🇷'},
        {v:'IQ',l:'Iraq',f:'🇮🇶'},{v:'IE',l:'Ireland',f:'🇮🇪'},{v:'IL',l:'Israel',f:'🇮🇱'},
        {v:'IT',l:'Italy',f:'🇮🇹'},{v:'JM',l:'Jamaica',f:'🇯🇲'},{v:'JP',l:'Japan',f:'🇯🇵'},
        {v:'JO',l:'Jordan',f:'🇯🇴'},{v:'KZ',l:'Kazakhstan',f:'🇰🇿'},{v:'KE',l:'Kenya',f:'🇰🇪'},
        {v:'KI',l:'Kiribati',f:'🇰🇮'},{v:'KW',l:'Kuwait',f:'🇰🇼'},{v:'KG',l:'Kyrgyzstan',f:'🇰🇬'},
        {v:'LA',l:'Laos',f:'🇱🇦'},{v:'LV',l:'Latvia',f:'🇱🇻'},{v:'LB',l:'Lebanon',f:'🇱🇧'},
        {v:'LS',l:'Lesotho',f:'🇱🇸'},{v:'LR',l:'Liberia',f:'🇱🇷'},{v:'LY',l:'Libya',f:'🇱🇾'},
        {v:'LI',l:'Liechtenstein',f:'🇱🇮'},{v:'LT',l:'Lithuania',f:'🇱🇹'},{v:'LU',l:'Luxembourg',f:'🇱🇺'},
        {v:'MG',l:'Madagascar',f:'🇲🇬'},{v:'MW',l:'Malawi',f:'🇲🇼'},{v:'MY',l:'Malaysia',f:'🇲🇾'},
        {v:'MV',l:'Maldives',f:'🇲🇻'},{v:'ML',l:'Mali',f:'🇲🇱'},{v:'MT',l:'Malta',f:'🇲🇹'},
        {v:'MR',l:'Mauritania',f:'🇲🇷'},{v:'MU',l:'Mauritius',f:'🇲🇺'},{v:'MX',l:'Mexico',f:'🇲🇽'},
        {v:'MD',l:'Moldova',f:'🇲🇩'},{v:'MC',l:'Monaco',f:'🇲🇨'},{v:'MN',l:'Mongolia',f:'🇲🇳'},
        {v:'ME',l:'Montenegro',f:'🇲🇪'},{v:'MA',l:'Morocco',f:'🇲🇦'},{v:'MZ',l:'Mozambique',f:'🇲🇿'},
        {v:'MM',l:'Myanmar',f:'🇲🇲'},{v:'NA',l:'Namibia',f:'🇳🇦'},{v:'NP',l:'Nepal',f:'🇳🇵'},
        {v:'NL',l:'Netherlands',f:'🇳🇱'},{v:'NZ',l:'New Zealand',f:'🇳🇿'},{v:'NI',l:'Nicaragua',f:'🇳🇮'},
        {v:'NE',l:'Niger',f:'🇳🇪'},{v:'NG',l:'Nigeria',f:'🇳🇬'},{v:'MK',l:'North Macedonia',f:'🇲🇰'},
        {v:'NO',l:'Norway',f:'🇳🇴'},{v:'OM',l:'Oman',f:'🇴🇲'},{v:'PK',l:'Pakistan',f:'🇵🇰'},
        {v:'PA',l:'Panama',f:'🇵🇦'},{v:'PG',l:'Papua New Guinea',f:'🇵🇬'},{v:'PY',l:'Paraguay',f:'🇵🇾'},
        {v:'PE',l:'Peru',f:'🇵🇪'},{v:'PH',l:'Philippines',f:'🇵🇭'},{v:'PL',l:'Poland',f:'🇵🇱'},
        {v:'PT',l:'Portugal',f:'🇵🇹'},{v:'QA',l:'Qatar',f:'🇶🇦'},{v:'RO',l:'Romania',f:'🇷🇴'},
        {v:'RU',l:'Russia',f:'🇷🇺'},{v:'RW',l:'Rwanda',f:'🇷🇼'},{v:'SA',l:'Saudi Arabia',f:'🇸🇦'},
        {v:'SN',l:'Senegal',f:'🇸🇳'},{v:'RS',l:'Serbia',f:'🇷🇸'},{v:'SL',l:'Sierra Leone',f:'🇸🇱'},
        {v:'SG',l:'Singapore',f:'🇸🇬'},{v:'SK',l:'Slovakia',f:'🇸🇰'},{v:'SI',l:'Slovenia',f:'🇸🇮'},
        {v:'SO',l:'Somalia',f:'🇸🇴'},{v:'ZA',l:'South Africa',f:'🇿🇦'},{v:'KR',l:'South Korea',f:'🇰🇷'},
        {v:'SS',l:'South Sudan',f:'🇸🇸'},{v:'ES',l:'Spain',f:'🇪🇸'},{v:'LK',l:'Sri Lanka',f:'🇱🇰'},
        {v:'SD',l:'Sudan',f:'🇸🇩'},{v:'SR',l:'Suriname',f:'🇸🇷'},{v:'SE',l:'Sweden',f:'🇸🇪'},
        {v:'CH',l:'Switzerland',f:'🇨🇭'},{v:'SY',l:'Syria',f:'🇸🇾'},{v:'TW',l:'Taiwan',f:'🇹🇼'},
        {v:'TJ',l:'Tajikistan',f:'🇹🇯'},{v:'TZ',l:'Tanzania',f:'🇹🇿'},{v:'TH',l:'Thailand',f:'🇹🇭'},
        {v:'TL',l:'Timor-Leste',f:'🇹🇱'},{v:'TG',l:'Togo',f:'🇹🇬'},{v:'TT',l:'Trinidad & Tobago',f:'🇹🇹'},
        {v:'TN',l:'Tunisia',f:'🇹🇳'},{v:'TR',l:'Turkey',f:'🇹🇷'},{v:'TM',l:'Turkmenistan',f:'🇹🇲'},
        {v:'UG',l:'Uganda',f:'🇺🇬'},{v:'UA',l:'Ukraine',f:'🇺🇦'},{v:'AE',l:'United Arab Emirates',f:'🇦🇪'},
        {v:'GB',l:'United Kingdom',f:'🇬🇧'},{v:'US',l:'United States',f:'🇺🇸'},{v:'UY',l:'Uruguay',f:'🇺🇾'},
        {v:'UZ',l:'Uzbekistan',f:'🇺🇿'},{v:'VE',l:'Venezuela',f:'🇻🇪'},{v:'VN',l:'Vietnam',f:'🇻🇳'},
        {v:'YE',l:'Yemen',f:'🇾🇪'},{v:'ZM',l:'Zambia',f:'🇿🇲'},{v:'ZW',l:'Zimbabwe',f:'🇿🇼'}
    ];

    var LANGUAGES = [
        {v:'af',l:'Afrikaans'},{v:'sq',l:'Albanian'},{v:'ar',l:'Arabic'},{v:'hy',l:'Armenian'},
        {v:'az',l:'Azerbaijani'},{v:'eu',l:'Basque'},{v:'be',l:'Belarusian'},{v:'bn',l:'Bengali'},
        {v:'bs',l:'Bosnian'},{v:'bg',l:'Bulgarian'},{v:'ca',l:'Catalan'},{v:'zh',l:'Chinese (Mandarin)'},
        {v:'zh-hk',l:'Chinese (Cantonese)'},{v:'hr',l:'Croatian'},{v:'cs',l:'Czech'},{v:'da',l:'Danish'},
        {v:'nl',l:'Dutch'},{v:'en',l:'English'},{v:'et',l:'Estonian'},{v:'fi',l:'Finnish'},
        {v:'fr',l:'French'},{v:'gl',l:'Galician'},{v:'ka',l:'Georgian'},{v:'de',l:'German'},
        {v:'el',l:'Greek'},{v:'gu',l:'Gujarati'},{v:'ht',l:'Haitian Creole'},{v:'he',l:'Hebrew'},
        {v:'hi',l:'Hindi'},{v:'hu',l:'Hungarian'},{v:'is',l:'Icelandic'},{v:'id',l:'Indonesian'},
        {v:'ga',l:'Irish'},{v:'it',l:'Italian'},{v:'ja',l:'Japanese'},{v:'kn',l:'Kannada'},
        {v:'kk',l:'Kazakh'},{v:'ko',l:'Korean'},{v:'ku',l:'Kurdish'},{v:'ky',l:'Kyrgyz'},
        {v:'lv',l:'Latvian'},{v:'lt',l:'Lithuanian'},{v:'lb',l:'Luxembourgish'},{v:'mk',l:'Macedonian'},
        {v:'ms',l:'Malay'},{v:'ml',l:'Malayalam'},{v:'mt',l:'Maltese'},{v:'mr',l:'Marathi'},
        {v:'mn',l:'Mongolian'},{v:'ne',l:'Nepali'},{v:'no',l:'Norwegian'},{v:'fa',l:'Persian (Farsi)'},
        {v:'pl',l:'Polish'},{v:'pt',l:'Portuguese'},{v:'pa',l:'Punjabi'},{v:'ro',l:'Romanian'},
        {v:'ru',l:'Russian'},{v:'sr',l:'Serbian'},{v:'sk',l:'Slovak'},{v:'sl',l:'Slovenian'},
        {v:'es',l:'Spanish'},{v:'sw',l:'Swahili'},{v:'sv',l:'Swedish'},{v:'tl',l:'Tagalog'},
        {v:'tg',l:'Tajik'},{v:'ta',l:'Tamil'},{v:'te',l:'Telugu'},{v:'th',l:'Thai'},
        {v:'tr',l:'Turkish'},{v:'tk',l:'Turkmen'},{v:'uk',l:'Ukrainian'},{v:'ur',l:'Urdu'},
        {v:'uz',l:'Uzbek'},{v:'vi',l:'Vietnamese'},{v:'cy',l:'Welsh'},{v:'yi',l:'Yiddish'}
    ];

    /* ── Shared: position a fixed panel under its trigger ── */
    function positionPanel(trigger, panel) {
        panel.style.position = 'absolute';
        panel.style.top = 'calc(100% + 8px)';
        panel.style.left = '0';
        panel.style.width = '100%';
        panel.style.zIndex = '99999';
    }

    /* ── Single-select: Country ── */
    function initCountryDrop() {
        var wrap    = document.getElementById('countryDropWrap');
        var trigger = document.getElementById('countryTrigger');
        var panel   = document.getElementById('countryPanel');
        var search  = document.getElementById('countrySearch');
        var list    = document.getElementById('countryList');
        var display = document.getElementById('countryDisplay');
        var hidden  = document.getElementById('fieldCountry');
        if (!wrap) return;

        function renderList(q) {
            var filtered = q ? COUNTRIES.filter(function(c){ return c.l.toLowerCase().indexOf(q.toLowerCase()) !== -1; }) : COUNTRIES;
            list.innerHTML = '';
            if (!filtered.length) {
                list.innerHTML = '<li class="searchdrop-empty">No results</li>';
                return;
            }
            filtered.forEach(function(c) {
                var li = document.createElement('li');
                li.className = 'searchdrop-option' + (hidden.value === c.v ? ' selected' : '');
                li.setAttribute('data-value', c.v);
                li.innerHTML = '<span class="option-flag">' + c.f + '</span>' + c.l;
                li.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hidden.value = c.v;
                    display.textContent = c.f + '  ' + c.l;
                    display.classList.remove('placeholder');
                    closePanel();
                });
                list.appendChild(li);
            });
        }

        function openPanel() {
            positionPanel(trigger, panel);
            wrap.classList.add('open');
            panel.classList.add('open');
            trigger.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            renderList('');
            setTimeout(function(){ search.focus(); }, 50);
        }

        function closePanel() {
            wrap.classList.remove('open');
            panel.classList.remove('open');
            trigger.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.contains('open') ? closePanel() : openPanel();
        });
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(); }
            if (e.key === 'Escape') closePanel();
        });
        search.addEventListener('input', function() { renderList(search.value); });
        search.addEventListener('click', function(e) { e.stopPropagation(); });
        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) closePanel();
        });
        window.addEventListener('scroll', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); }, true);
        window.addEventListener('resize', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); });

        display.textContent = '<?= t('Select country…') ?>';
        display.classList.add('placeholder');
        renderList('');
    }

    /* ── Multi-select: Languages ── */
    function initLangDrop() {
        var wrap    = document.getElementById('langDropWrap');
        var trigger = document.getElementById('langTrigger');
        var panel   = document.getElementById('langPanel');
        var search  = document.getElementById('langSearch');
        var list    = document.getElementById('langList');
        var tagsEl  = document.getElementById('langTags');
        var ph      = document.getElementById('langPlaceholder');
        var hidden  = document.getElementById('fieldLanguages');
        if (!wrap) return;

        var selected = [];

        function syncHidden() {
            hidden.value = selected.map(function(v){ return LANGUAGES.find(function(l){ return l.v===v; }).l; }).join(', ');
        }

        function renderTags() {
            tagsEl.innerHTML = '';
            if (!selected.length) {
                tagsEl.appendChild(ph);
                return;
            }
            selected.forEach(function(v) {
                var lang = LANGUAGES.find(function(l){ return l.v===v; });
                var tag = document.createElement('span');
                tag.className = 'searchdrop-tag';
                tag.innerHTML = lang.l + '<button type="button" class="searchdrop-tag-remove" data-v="' + v + '">✕</button>';
                tag.querySelector('.searchdrop-tag-remove').addEventListener('click', function(e) {
                    e.stopPropagation();
                    selected = selected.filter(function(s){ return s !== v; });
                    syncHidden(); renderTags(); renderList(search.value);
                });
                tagsEl.appendChild(tag);
            });
        }

        function renderList(q) {
            var filtered = q ? LANGUAGES.filter(function(l){ return l.l.toLowerCase().indexOf(q.toLowerCase()) !== -1; }) : LANGUAGES;
            list.innerHTML = '';
            if (!filtered.length) {
                list.innerHTML = '<li class="searchdrop-empty">No results</li>';
                return;
            }
            filtered.forEach(function(lang) {
                var li = document.createElement('li');
                var isSel = selected.indexOf(lang.v) !== -1;
                li.className = 'searchdrop-option' + (isSel ? ' selected' : '');
                li.setAttribute('data-value', lang.v);
                li.innerHTML = '<span class="option-check">' + (isSel ? '✓' : '') + '</span>' + lang.l;
                li.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var idx = selected.indexOf(lang.v);
                    if (idx === -1) { selected.push(lang.v); }
                    else { selected.splice(idx, 1); }
                    syncHidden(); renderTags(); renderList(search.value);
                });
                list.appendChild(li);
            });
        }

        function openPanel() {
            positionPanel(trigger, panel);
            wrap.classList.add('open');
            panel.classList.add('open');
            trigger.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            renderList('');
            setTimeout(function(){ search.focus(); }, 50);
        }

        function closePanel() {
            wrap.classList.remove('open');
            panel.classList.remove('open');
            trigger.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.contains('open') ? closePanel() : openPanel();
        });
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(); }
            if (e.key === 'Escape') closePanel();
        });
        search.addEventListener('input', function() { renderList(search.value); });
        search.addEventListener('click', function(e) { e.stopPropagation(); });
        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) closePanel();
        });
        window.addEventListener('scroll', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); }, true);
        window.addEventListener('resize', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); });

        renderTags();
        renderList('');
    }

    /* ── Single-select: Server/Region ── */
    function initServerDrop() {
        var REGIONS = [
            {v:'EUW',  l:'EUW',  sub:'Europe West'},
            {v:'EUNE', l:'EUNE', sub:'Europe Nordic & East'},
            {v:'NA',   l:'NA',   sub:'North America'},
            {v:'KR',   l:'KR',   sub:'Korea'},
            {v:'TR',   l:'TR',   sub:'Turkey'},
            {v:'LAS',  l:'LAS',  sub:'Latin America South'},
            {v:'LAN',  l:'LAN',  sub:'Latin America North'},
            {v:'BR',   l:'BR',   sub:'Brazil'},
            {v:'OCE',  l:'OCE',  sub:'Oceania'},
            {v:'JP',   l:'JP',   sub:'Japan'},
            {v:'RU',   l:'RU',   sub:'Russia'},
        ];
        var wrap    = document.getElementById('serverDropWrap');
        var trigger = document.getElementById('serverTrigger');
        var panel   = document.getElementById('serverPanel');
        var list    = document.getElementById('serverList');
        var display = document.getElementById('serverDisplay');
        var hidden  = document.getElementById('fieldServer');
        if (!wrap) return;

        function renderList() {
            list.innerHTML = '';
            REGIONS.forEach(function(r) {
                var li = document.createElement('li');
                li.className = 'searchdrop-option' + (hidden.value === r.v ? ' selected' : '');
                li.setAttribute('data-value', r.v);
                li.innerHTML = '<span class="option-region-badge">' + r.l + '</span><span style="color:rgba(255,255,255,.58);font-size:12px;">' + r.sub + '</span>';
                li.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hidden.value = r.v;
                    display.innerHTML = '<span class="option-region-badge" style="margin-right:8px;">' + r.l + '</span>' + r.sub;
                    display.classList.remove('placeholder');
                    closePanel();
                });
                list.appendChild(li);
            });
        }

        function openPanel() {
            positionPanel(trigger, panel);
            wrap.classList.add('open');
            panel.classList.add('open');
            trigger.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            renderList();
        }
        function closePanel() {
            wrap.classList.remove('open');
            panel.classList.remove('open');
            trigger.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.contains('open') ? closePanel() : openPanel();
        });
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(); }
            if (e.key === 'Escape') closePanel();
        });
        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) closePanel();
        });
        window.addEventListener('scroll', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); }, true);
        window.addEventListener('resize', function() { if (panel.classList.contains('open')) positionPanel(trigger, panel); });

        display.textContent = 'Select Region…';
        display.classList.add('placeholder');
    }

    /* ── VPN Toggle ── */
    function initVpnToggle() {
        var btnNo  = document.getElementById('vpnBtnNo');
        var btnYes = document.getElementById('vpnBtnYes');
        var hidden = document.getElementById('hasVpn');
        var card   = document.getElementById('wrapVpn');
        if (!btnNo || !btnYes) return;

        function setVpn(val) {
            hidden.value = val;
            if (val === '1') {
                btnYes.classList.add('active');
                btnNo.classList.remove('active');
                if (card) {
                    card.style.borderColor = 'rgba(74,222,128,.30)';
                    card.style.background  = 'rgba(74,222,128,.05)';
                }
            } else {
                btnNo.classList.add('active');
                btnYes.classList.remove('active');
                if (card) {
                    card.style.borderColor = '';
                    card.style.background  = '';
                }
            }
        }

        btnNo.addEventListener('click',  function() { setVpn('');  });
        btnYes.addEventListener('click', function() { setVpn('1'); });
        setVpn('');
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCountryDrop();
        initLangDrop();
        initServerDrop();
        initVpnToggle();
    });
    // Also init immediately in case DOM already loaded
    if (document.readyState !== 'loading') {
        initCountryDrop();
        initLangDrop();
        initServerDrop();
        initVpnToggle();
    }
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var currentStep = 1;
    var maxStep = 5;
    var selectedRole = '';
    var boosterRoles = ['lol_booster', 'tft_booster', 'val_booster'];

    var roleCards = document.querySelectorAll('.role-card');
    var panes = document.querySelectorAll('.wizard-pane');
    var stepItems = document.querySelectorAll('.step-item');
    var progressFill = document.getElementById('progressFill');
    var progressText = document.getElementById('progressText');
    var roleHintBox = document.getElementById('roleHintBox');
    var wizardIntro = document.getElementById('wizardIntro');
    var form = document.getElementById('jobApplicationForm');
    var fieldRole = document.getElementById('fieldRole');
    var reviewCard = document.getElementById('reviewCard');
    var successBox = document.getElementById('applySuccess');
    var applyCard = document.querySelector('.apply-card');
    var submitBtn = document.getElementById('applySubmitBtn');

    /* sync horizontal step dots */
    function syncHorizSteps(step) {
        /* old hidden wizard-steps (kept for compat) */
        document.querySelectorAll('[data-wstep]').forEach(function(el) {
            var n = Number(el.getAttribute('data-wstep'));
            el.classList.remove('active', 'done');
            if (n < step) {
                el.classList.add('done');
                var c = el.querySelector('.wstep-circle');
                if (c) c.textContent = '✓';
            } else if (n === step) {
                el.classList.add('active');
                var c = el.querySelector('.wstep-circle');
                if (c) c.textContent = String(n);
            } else {
                var c = el.querySelector('.wstep-circle');
                if (c) c.textContent = String(n);
            }
        });
        [{ id: 'wline12', done: step > 1 }, { id: 'wline23', done: step > 2 }, { id: 'wline34', done: step > 3 }, { id: 'wline45', done: step > 4 }].forEach(function(l) {
            var el = document.getElementById(l.id);
            if (el) el.classList.toggle('done', l.done);
        });

        /* NEW hero steps bar */
        document.querySelectorAll('[data-astp]').forEach(function(el) {
            var n = Number(el.getAttribute('data-astp'));
            el.classList.remove('active', 'done');
            if (n < step) {
                el.classList.add('done');
                var c = el.querySelector('.apply-stp-circle');
                if (c) c.textContent = '✓';
            } else if (n === step) {
                el.classList.add('active');
                var c = el.querySelector('.apply-stp-circle');
                if (c) c.textContent = String(n);
            } else {
                var c = el.querySelector('.apply-stp-circle');
                if (c) c.textContent = String(n);
            }
        });
        [{ id: 'astpLine12', done: step > 1 }, { id: 'astpLine23', done: step > 2 }, { id: 'astpLine34', done: step > 3 }, { id: 'astpLine45', done: step > 4 }].forEach(function(l) {
            var el = document.getElementById(l.id);
            if (el) el.classList.toggle('done', l.done);
        });
    }

    var roleConfigs = {
        booster: {
            hint: 'Booster applications are reviewed first, then strong applicants can be invited into a Discord trial.',
            intro: 'You selected Booster. Choose the game you want to boost, then add your account, rank and availability details.',
            pane2Title: 'Personal information',
            pane2Copy: 'Let us know who you are and where we can reach you.',
            pane3Title: 'Booster details',
            pane3Copy: 'Choose your boosting game and show us your rank history, region and strengths.',
            pane4Title: 'About you',
            pane4Copy: 'Tell us about your availability, previous experience and motivation as a booster.',
            personalSectionTitle: 'Personal Information',
            detailsSectionTitle: 'Booster Details',
            aboutSectionTitle: 'About You',
            fullnameLabel: "What's your first name?",
            fullnamePlaceholder: 'Shan',
            emailLabel: 'What is your email address?',
            emailPlaceholder: 'you@example.com',
            discordLabel: 'What is your Discord Tag?',
            discordPlaceholder: 'savorwolf',
            countryLabel: 'Which country are you from?',
            countryPlaceholder: 'United States',
            languagesLabel: 'Which languages do you speak?',
            languagesPlaceholder: 'English',
            ingameLabel: 'Profile / tracker link or main account name',
            ingamePlaceholder: 'op.gg, tracker.gg, armory link, or your main account name',
            serverLabel: 'What region / server do you play on?',
            currentRankLabel: 'What is your current rank?',
            currentRankPlaceholder: 'Grandmaster, Radiant, Champion, etc.',
            peakRankLabel: 'What is your peak rank?',
            peakRankPlaceholder: 'Challenger 1542 LP, Radiant, Rank 1, etc.',
            champPoolLabel: 'Main roles / characters / services you can boost',
            champPoolPlaceholder: 'Rank boost, duo, placements, roles, champions, agents, classes...',
            vpnLabel: 'I have a VPN and can use it when needed',
            availabilityLabel: 'When are you available?',
            availabilityPlaceholder: 'Mon to Fri after 6 PM, weekends anytime',
            experienceLabel: "Previous boosting experience / extra proof",
            experiencePlaceholder: 'Previous platforms, vouches, screenshots, tournament/team experience, anything relevant...',
            motivationLabel: 'Why do you want to join as Booster?',
            motivationPlaceholder: 'Tell us why you would be a strong fit for LOLBOOST.GG...',
            referralLabel: 'How did you hear about us?',
            referralPlaceholder: 'Discord, friend, social media, previous booster...',
            showAge: false,
            showServer: true,
            showVpn: true,
            showBoostGame: true,
            ingameRequired: true,
            peakRequired: true,
            motivationRequired: true
        },
        gg_girl: {
            hint: 'GG Girl applications are reviewed directly, without a separate trial step.',
            intro: 'You selected GG Girl. The form will focus more on communication, vibe and setup.',
            pane2Title: 'Personal information',
            pane2Copy: 'Start with the basics so we know who you are.',
            pane3Title: 'GG Girl details',
            pane3Copy: 'Tell us about your games, personality and setup.',
            pane4Title: 'About you',
            pane4Copy: 'Tell us about your availability, experience and why you want to join GamerGirls.',
            personalSectionTitle: 'Personal Information',
            detailsSectionTitle: 'Gaming Details',
            aboutSectionTitle: 'About You',
            fullnameLabel: "What's your first name?",
            fullnamePlaceholder: 'Gabriella',
            emailLabel: 'What is your email address?',
            emailPlaceholder: 'you@example.com',
            discordLabel: 'What is your Discord Tag?',
            discordPlaceholder: 'lunivexx',
            ageLabel: 'Your Age?',
            agePlaceholder: '25',
            countryLabel: 'Which country are you from?',
            countryPlaceholder: 'Sweden',
            languagesLabel: 'Which languages do you speak?',
            languagesPlaceholder: 'Swedish and English',
            ingameLabel: 'Main Games (e.g. LoL, Valorant, etc.)',
            ingamePlaceholder: 'Valorant, League of Legends, TFT',
            serverLabel: 'Primary platform or region',
            currentRankLabel: 'Current Rank(s)',
            currentRankPlaceholder: 'Diamond, Bronze, Silver',
            peakRankLabel: 'How long have you been gaming?',
            peakRankPlaceholder: '3 years',
            champPoolLabel: 'How would you describe your personality?',
            champPoolPlaceholder: 'Bubbly, outgoing, calm but energetic',
            vpnLabel: 'Do you have a good microphone?',
            availabilityLabel: 'Are you comfortable playing with clients?',
            availabilityPlaceholder: 'Yes / No',
            experienceLabel: 'Do you have experience in voice chat / streaming?',
            experiencePlaceholder: 'Yes / No, tell us a bit more if you want',
            motivationLabel: 'Why do you want to join GamerGirls?',
            motivationPlaceholder: 'To earn extra and have fun',
            referralLabel: 'Anything else we should know about you?',
            referralPlaceholder: 'Anything about your setup, webcam, vibe or experience',
            showAge: true,
            showServer: false,
            showVpn: true,
            ingameRequired: false,
            peakRequired: false,
            motivationRequired: true
        },
        seller: {
            hint: 'Seller applications are reviewed directly, strong applications can be accepted without a trial.',
            intro: 'You selected Seller. The form will focus more on store links, stock and sourcing details.',
            pane2Title: 'Personal information',
            pane2Copy: 'Tell us who you are and how we can contact you.',
            pane3Title: 'Seller details',
            pane3Copy: 'Add your services, links, sourcing and business background.',
            pane4Title: 'Business background',
            pane4Copy: 'Tell us about your availability, experience and why you want to work with us.',
            personalSectionTitle: 'Personal Information',
            detailsSectionTitle: 'Seller Details',
            aboutSectionTitle: 'Business Details',
            fullnameLabel: 'To get started, please state your full name',
            fullnamePlaceholder: 'Matt Ram',
            emailLabel: "What's your Email?",
            emailPlaceholder: 'you@example.com',
            discordLabel: "What's your username on that platform?",
            discordPlaceholder: 'yf1g',
            countryLabel: 'Where are you based?',
            countryPlaceholder: 'California USA',
            languagesLabel: 'What languages do you speak?',
            languagesPlaceholder: 'English',
            ingameLabel: 'What kind of services do you provide?',
            ingamePlaceholder: 'Boosting, Accounts, Coaching, Items',
            currentRankLabel: 'Please link your stores on other platforms',
            currentRankPlaceholder: 'https://yourstore.com',
            peakRankLabel: 'Second store or website link',
            peakRankPlaceholder: 'https://second-store.com',
            champPoolLabel: 'How do you source your products, and what is your current total stock?',
            champPoolPlaceholder: 'Legitly and boost by myself, current stock is ...',
            availabilityLabel: 'Are you currently working with other platforms?',
            availabilityPlaceholder: 'Yes / No, and which ones',
            experienceLabel: 'What is your preferred messaging platform?',
            experiencePlaceholder: 'Discord, Telegram, WhatsApp, etc.',
            motivationLabel: 'Hint: Help us get to know you, mention your stock, link your other stores, or add anything relevant.',
            motivationPlaceholder: 'Whatever accounts I need to boost, extra info, why we should work with you...',
            referralLabel: 'How did you hear about us?',
            referralPlaceholder: 'Friend, Discord, social media, marketplace...',
            showAge: false,
            showServer: false,
            showVpn: false,
            ingameRequired: false,
            peakRequired: false,
            motivationRequired: true
        }
    };

    // ── Per-game field overrides (only for the generic "Booster" role) ──
    // When a booster picks a game, these override the step-3 detail fields so the
    // questions are specific to that game. "Other" (and unknown) games fall back to
    // the generic booster labels (empty override object).
    var gameFieldConfigs = {
        lol: {
            ingameLabel: 'op.gg link or Summoner name',
            ingamePlaceholder: 'https://op.gg/summoners/... or Name#EUW',
            serverLabel: 'What region / server do you play on?',
            currentRankLabel: 'What is your current LoL rank?',
            currentRankPlaceholder: 'Emerald III',
            peakRankLabel: 'What is your peak LoL rank?',
            peakRankPlaceholder: 'Challenger 1200 LP',
            champPoolLabel: 'Main roles / champion pool',
            champPoolPlaceholder: 'Mid, Jungle — Ahri, Lee Sin, ...'
        },
        'wild-rift': {
            ingameLabel: 'Riot ID or profile',
            ingamePlaceholder: 'Name#TAG',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'Current Wild Rift rank',
            currentRankPlaceholder: 'Diamond II',
            peakRankLabel: 'Peak Wild Rift rank',
            peakRankPlaceholder: 'Challenger',
            champPoolLabel: 'Main lanes / champions',
            champPoolPlaceholder: 'Baron, Mid — Yasuo, Lee Sin, ...'
        },
        valorant: {
            ingameLabel: 'tracker.gg link or Riot ID',
            ingamePlaceholder: 'tracker.gg/valorant/... or Name#TAG',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'What is your current Valorant rank?',
            currentRankPlaceholder: 'Ascendant II',
            peakRankLabel: 'What is your peak Valorant rank?',
            peakRankPlaceholder: 'Radiant',
            champPoolLabel: 'Main agents / roles',
            champPoolPlaceholder: 'Duelist — Jett, Raze; Controller — Omen'
        },
        tft: {
            ingameLabel: 'lolchess.gg link or Riot ID',
            ingamePlaceholder: 'lolchess.gg/profile/... or Name#TAG',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'What is your current TFT rank?',
            currentRankPlaceholder: 'Diamond IV',
            peakRankLabel: 'What is your peak TFT rank?',
            peakRankPlaceholder: 'Grandmaster',
            champPoolLabel: 'Preferred comps / playstyle',
            champPoolPlaceholder: 'Flex, reroll, fast 8, hyperroll...'
        },
        apex: {
            ingameLabel: 'tracker.gg link or in-game name',
            ingamePlaceholder: 'apex.tracker.gg/... or your IGN',
            serverLabel: 'Platform / region',
            currentRankLabel: 'What is your current Apex rank?',
            currentRankPlaceholder: 'Diamond IV',
            peakRankLabel: 'What is your peak Apex rank?',
            peakRankPlaceholder: 'Predator #500',
            champPoolLabel: 'Main legends / roles',
            champPoolPlaceholder: 'Wraith, Bloodhound — IGL, Fragger...'
        },
        fortnite: {
            ingameLabel: 'Epic name or tracker link',
            ingamePlaceholder: 'fortnitetracker.com/... or EpicName',
            serverLabel: 'Platform / region',
            currentRankLabel: 'Current rank / arena',
            currentRankPlaceholder: 'Champion League, Unreal',
            peakRankLabel: 'Peak rank',
            peakRankPlaceholder: 'Unreal #1200',
            champPoolLabel: 'Modes / playstyle',
            champPoolPlaceholder: 'Zero Build, Ranked, Box fights...'
        },
        'marvel-rivals': {
            ingameLabel: 'Tracker link or in-game name',
            ingamePlaceholder: 'rivalstracker.com/... or your IGN',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'Current Marvel Rivals rank',
            currentRankPlaceholder: 'Grandmaster III',
            peakRankLabel: 'Peak rank',
            peakRankPlaceholder: 'Eternity / One Above All',
            champPoolLabel: 'Main heroes / roles',
            champPoolPlaceholder: 'Duelist, Vanguard, Strategist — heroes'
        },
        'rocket-league': {
            ingameLabel: 'Tracker link or in-game name',
            ingamePlaceholder: 'rocketleague.tracker.network/... or IGN',
            serverLabel: 'Platform / region',
            currentRankLabel: 'What is your current rank?',
            currentRankPlaceholder: 'Champion II',
            peakRankLabel: 'What is your peak rank?',
            peakRankPlaceholder: 'Grand Champion',
            champPoolLabel: 'Playlists you can boost',
            champPoolPlaceholder: '1v1, 2v2, 3v3, Tournaments'
        },
        overwatch: {
            ingameLabel: 'BattleTag or profile link',
            ingamePlaceholder: 'Name#1234',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'Current Overwatch 2 rank',
            currentRankPlaceholder: 'Diamond 3',
            peakRankLabel: 'Peak rank',
            peakRankPlaceholder: 'Grandmaster / Top 500',
            champPoolLabel: 'Main roles / heroes',
            champPoolPlaceholder: 'Tank, DPS, Support — heroes'
        },
        cs2: {
            ingameLabel: 'Steam / Faceit profile link',
            ingamePlaceholder: 'steamcommunity.com/... or faceit.com/...',
            serverLabel: 'What region do you play on?',
            currentRankLabel: 'Current rank / elo',
            currentRankPlaceholder: 'Faceit lvl 10, Premier 20k',
            peakRankLabel: 'Peak rank / elo',
            peakRankPlaceholder: 'Faceit 3000 elo',
            champPoolLabel: 'Roles / maps you main',
            champPoolPlaceholder: 'AWPer, Entry — Mirage, Inferno'
        },
        gta: {
            ingameLabel: 'Account / Social Club name or link',
            ingamePlaceholder: 'Your account name or profile link',
            showServer: false,
            currentRankLabel: 'Current level / progression',
            currentRankPlaceholder: 'Level, money, unlocks...',
            peakRankLabel: 'What can you offer?',
            peakRankPlaceholder: 'Money, RP, heists, unlocks',
            peakRequired: false,
            champPoolLabel: 'Services you can provide',
            champPoolPlaceholder: 'Money drops, level boosting, heists...'
        }
    };

    var activeGameOverrides = {};

    function normalizeGameKey(slug, name) {
        var s = (slug || '').toLowerCase(), n = (name || '').toLowerCase();
        if (s.indexOf('wild') !== -1 || n.indexOf('wild rift') !== -1) return 'wild-rift';
        if (s === 'league-of-legends' || (n.indexOf('league of legends') !== -1 && n.indexOf('wild') === -1)) return 'lol';
        if (s === 'valorant' || n.indexOf('valorant') !== -1) return 'valorant';
        if (s === 'teamfight-tactics' || n.indexOf('teamfight') !== -1 || n.indexOf('tft') !== -1) return 'tft';
        if (s.indexOf('apex') !== -1 || n.indexOf('apex') !== -1) return 'apex';
        if (s.indexOf('fortnite') !== -1 || n.indexOf('fortnite') !== -1) return 'fortnite';
        if (s.indexOf('marvel') !== -1 || n.indexOf('marvel') !== -1 || n.indexOf('rivals') !== -1) return 'marvel-rivals';
        if (s.indexOf('rocket') !== -1 || n.indexOf('rocket') !== -1) return 'rocket-league';
        if (s.indexOf('overwatch') !== -1 || n.indexOf('overwatch') !== -1) return 'overwatch';
        if (s.indexOf('counter') !== -1 || n.indexOf('counter') !== -1 || n.indexOf('cs2') !== -1 || n.indexOf('cs 2') !== -1) return 'cs2';
        if (s.indexOf('grand-theft') !== -1 || n.indexOf('grand theft') !== -1 || n.indexOf('gta') !== -1) return 'gta';
        return 'other';
    }

    // Re-apply just the step-3 game-detail fields using the merged config (base
    // booster + active game override). Does NOT touch selectedRole/fieldRole, so the
    // game→specific-role mapping (lol_booster/etc.) stays intact.
    function applyGameFields() {
        var c = cfg();
        setText('labelIngame', (c.ingameLabel || '<?= t('In Game Name') ?>') + (c.ingameRequired ? ' <span class="req">*</span>' : ''), true);
        setText('labelServer', c.serverLabel || '<?= t('Server / Region') ?>');
        setText('labelCurrentRank', c.currentRankLabel || '<?= t('Current Rank') ?>');
        setText('labelPeakRank', (c.peakRankLabel || '<?= t('Peak Rank') ?>') + (c.peakRequired ? ' <span class="req">*</span>' : ''), true);
        setText('labelChampPool', c.champPoolLabel || '<?= t('Champion Pool / Main Roles') ?>');
        setPlaceholder('fieldIngame', c.ingamePlaceholder || 'YourSummonerName');
        setPlaceholder('fieldCurrentRank', c.currentRankPlaceholder || 'Diamond II');
        setPlaceholder('fieldPeakRank', c.peakRankPlaceholder || 'Master 200LP');
        setPlaceholder('fieldChampPool', c.champPoolPlaceholder || 'Mid, ADC, Support');
        setVisible('wrapServer', c.showServer !== false);
        if (c.showServer === false) {
            var srv = document.getElementById('fieldServer');
            if (srv) srv.value = '';
            var sd = document.getElementById('serverDisplay');
            if (sd) { sd.textContent = 'Select Region…'; sd.classList.add('placeholder'); }
        }
        var ig = document.getElementById('fieldIngame'); if (ig) ig.required = !!c.ingameRequired;
        var pk = document.getElementById('fieldPeakRank'); if (pk) pk.required = !!c.peakRequired;
    }

    function cfg() {
        var base = roleConfigs[selectedRole] || {};
        // Merge active game overrides on top of the base booster config.
        if (selectedRole === 'booster' && activeGameOverrides) {
            return Object.assign({}, base, activeGameOverrides);
        }
        return base;
    }

    function pane(step) {
        return document.querySelector('.wizard-pane[data-pane="' + step + '"]');
    }

    function setPane(step) {
        currentStep = step;

        panes.forEach(function (el) {
            el.classList.toggle('active', Number(el.getAttribute('data-pane')) === step);
        });

        stepItems.forEach(function (item) {
            var itemStep = Number(item.getAttribute('data-step-item'));
            item.classList.remove('active', 'done');
            if (itemStep === step) item.classList.add('active');
            if (itemStep < step) item.classList.add('done');
        });

        var width = (step / maxStep) * 100;
        progressFill.style.width = width + '%';
        progressText.textContent = '<?= t('Step') ?> ' + step + ' <?= t('of') ?> ' + maxStep;

        var introMap = {
            1: '<?= t('Start by selecting the role you want to apply for.') ?>',
            2: '<?= t('Now fill in the personal basics so we can contact you.') ?>',
            3: '<?= t('Add your role-specific details and strengths.') ?>',
            4: '<?= t('Tell us more about your experience, availability and motivation.') ?>',
            5: '<?= t('Review your answers and submit when everything looks right.') ?>'
        };
        wizardIntro.textContent = introMap[step] || '';

        syncHorizSteps(step);

        window.requestAnimationFrame(function () {
            var root = document.getElementById('applyWizardRoot');
            if (root) {
                var offset = root.getBoundingClientRect().top + window.pageYOffset - 20;
                window.scrollTo({ top: offset, behavior: 'smooth' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    function setText(id, value, html) {
        var el = document.getElementById(id);
        if (!el) return;
        if (html) el.innerHTML = value || '';
        else el.textContent = value || '';
    }

    function setPlaceholder(id, value) {
        var el = document.getElementById(id);
        if (el) el.placeholder = value || '';
    }

    function setVisible(id, visible) {
        var el = document.getElementById(id);
        if (el) el.style.display = visible ? '' : 'none';
    }

    function resetFieldError(name) {
        var box = document.querySelector('.field-error[data-error-for="' + name + '"]');
        if (box) {
            box.style.display = 'none';
            box.textContent = '';
        }
    }

    function showFieldError(name, text) {
        var box = document.querySelector('.field-error[data-error-for="' + name + '"]');
        if (box) {
            box.style.display = 'block';
            box.textContent = text;
        }
    }

    function clearAllFieldErrors() {
        document.querySelectorAll('.field-error').forEach(function (el) {
            el.style.display = 'none';
            el.textContent = '';
        });
    }

    function applyRoleConfig(role) {
        selectedRole = role;
        fieldRole.value = role;
        applyCard.setAttribute('data-active-role', role);
        /* sync accent color on the new hero steps bar wrapper */
        var stepsBar = document.getElementById('applyStepsBar');
        if (stepsBar) stepsBar.setAttribute('data-active-role', role);

        var c = cfg();

        if (role !== 'booster') {
            selectedBoostGame = null;
            activeGameOverrides = {};
            var bg = document.getElementById('fieldBoostGame');
            if (bg) bg.value = '';
            document.querySelectorAll('.boost-game-option').forEach(function (btn) { btn.classList.remove('selected'); });
            var otherWrapReset = document.getElementById('wrapBoostGameOther');
            if (otherWrapReset) otherWrapReset.style.display = 'none';
        }

        setText('roleHintBox', c.hint || '<?= t('Choose a role first to see the exact expectations and review flow.') ?>');
        setText('roleApplyHint', c.hint || '');

        setText('pane2Title', c.pane2Title || '<?= t('Personal information') ?>');
        setText('pane2Copy', c.pane2Copy || '<?= t('Tell us who you are and how we can reach you.') ?>');
        setText('pane3Title', c.pane3Title || '<?= t('Role details') ?>');
        setText('pane3Copy', c.pane3Copy || '<?= t('Now show us what you can offer and why you fit this role.') ?>');
        setText('pane4Title', c.pane4Title || '<?= t('About you') ?>');
        setText('pane4Copy', c.pane4Copy || '<?= t('Tell us more about your availability, experience and motivation.') ?>');

        setText('personalSectionTitle', c.personalSectionTitle || '<?= t('Personal Information') ?>');
        setText('detailsSectionTitle', c.detailsSectionTitle || '<?= t('Details') ?>');
        setText('aboutSectionTitle', c.aboutSectionTitle || '<?= t('About You') ?>');

        setText('labelFullname', (c.fullnameLabel || '<?= t('Full Name') ?>') + ' <span class="req">*</span>', true);
        setText('labelEmail', (c.emailLabel || '<?= t('Email Address') ?>') + ' <span class="req">*</span>', true);
        setText('labelDiscord', (c.discordLabel || '<?= t('Discord Tag') ?>') + ' <span class="req">*</span>', true);
        setText('labelAge', c.ageLabel || '<?= t('Age') ?>');
        setText('labelCountry', c.countryLabel || '<?= t('Country') ?>');
        setText('labelLanguages', c.languagesLabel || '<?= t('Languages') ?>');
        setText('labelIngame', (c.ingameLabel || '<?= t('In Game Name') ?>') + (c.ingameRequired ? ' <span class="req">*</span>' : ''), true);
        setText('labelServer', c.serverLabel || '<?= t('Server / Region') ?>');
        setText('labelCurrentRank', c.currentRankLabel || '<?= t('Current Rank') ?>');
        setText('labelPeakRank', (c.peakRankLabel || '<?= t('Peak Rank') ?>') + (c.peakRequired ? ' <span class="req">*</span>' : ''), true);
        setText('labelChampPool', c.champPoolLabel || '<?= t('Champion Pool / Main Roles') ?>');
        setText('labelVpn', c.vpnLabel || '<?= t('I have a VPN and will use it during boosting sessions') ?>');
        setText('labelAvailability', c.availabilityLabel || '<?= t('Availability') ?>');
        setText('labelExperience', c.experienceLabel || '<?= t('Previous Experience') ?>');
        setText('labelMotivation', (c.motivationLabel || '<?= t('Why do you want to join?') ?>') + (c.motivationRequired !== false ? ' <span class="req">*</span>' : ''), true);
        setText('labelReferral', c.referralLabel || '<?= t('How did you hear about us?') ?>');

        setPlaceholder('fieldFullname', c.fullnamePlaceholder || 'Max Mustermann');
        setPlaceholder('fieldEmail', c.emailPlaceholder || 'you@example.com');
        setPlaceholder('fieldDiscord', c.discordPlaceholder || 'username or user#1234');
        setPlaceholder('fieldAge', c.agePlaceholder || '18');
        setPlaceholder('fieldIngame', c.ingamePlaceholder || 'YourSummonerName');
        setPlaceholder('fieldCurrentRank', c.currentRankPlaceholder || 'Diamond II');
        setPlaceholder('fieldPeakRank', c.peakRankPlaceholder || 'Master 200LP');
        setPlaceholder('fieldChampPool', c.champPoolPlaceholder || 'Mid, ADC, Support');
        setPlaceholder('fieldAvailability', c.availabilityPlaceholder || 'Mon–Fri 6pm–midnight, weekends flexible');
        setPlaceholder('fieldExperience', c.experiencePlaceholder || '<?= t('Tell us about any previous boosting, selling or gaming experience...') ?>');
        setPlaceholder('fieldMotivation', c.motivationPlaceholder || '<?= t('Tell us why you would be a great fit for this role...') ?>');
        setPlaceholder('fieldReferral', c.referralPlaceholder || 'Discord, Reddit, YouTube, Friend');

        setVisible('wrapAge', c.showAge === true);
        setVisible('boostGameSection', c.showBoostGame === true);
        setVisible('wrapServer', c.showServer === true);
        setVisible('wrapVpn', c.showVpn === true);

        if (!c.showServer) {
            var srv = document.getElementById('fieldServer');
            if (srv) srv.value = '';
            var sd = document.getElementById('serverDisplay');
            if (sd) { sd.textContent = 'Select Region…'; sd.classList.add('placeholder'); }
        }

        document.getElementById('fieldIngame').required = !!c.ingameRequired;
        document.getElementById('fieldPeakRank').required = !!c.peakRequired;
        document.getElementById('fieldMotivation').required = c.motivationRequired !== false;
    }

    function getValue(name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return '';
        if (el.type === 'checkbox') return el.checked ? 'Yes' : 'No';
        if (el.name === 'has_vpn' && el.type === 'hidden') return el.value === '1' ? 'Yes' : 'No';
        return (el.value || '').trim();
    }

    function validateStep(step) {
        clearAllFieldErrors();

        if (step === 1) {
            if (!selectedRole) {
                alert('<?= t('Please select a role first.') ?>');
                return false;
            }
            return true;
        }

        if (step === 2) {
            var ok = true;
            ['fullname', 'email', 'discord_tag'].forEach(function (field) {
                if (!getValue(field)) {
                    ok = false;
                    showFieldError(field, '<?= t('This field is required.') ?>');
                }
            });

            var email = getValue('email');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                ok = false;
                showFieldError('email', '<?= t('Please enter a valid email address.') ?>');
            }
            return ok;
        }

        if (step === 3) {
            var ok = true;
            var c = cfg();

            if (c.showBoostGame === true && !getValue('boost_game')) {
                ok = false;
                showFieldError('boost_game', '<?= t('Please select a game.') ?>');
            }

            if (c.ingameRequired && !getValue('ingame_name')) {
                ok = false;
                showFieldError('ingame_name', '<?= t('This field is required.') ?>');
            }

            if (c.peakRequired && !getValue('peak_rank')) {
                ok = false;
                showFieldError('peak_rank', '<?= t('This field is required.') ?>');
            }

            return ok;
        }

        if (step === 4) {
            var ok = true;
            var c = cfg();

            if (c.motivationRequired !== false && !getValue('motivation')) {
                ok = false;
                showFieldError('motivation', '<?= t('This field is required.') ?>');
            }

            return ok;
        }

        return true;
    }

    function buildReview() {
        var c = cfg();
        var rows = [
            ['<?= t('Role') ?>', (document.querySelector('.role-card.selected .role-name') || {}).textContent || '—'],
            ['<?= t('Boosting Game') ?>', getValue('boost_game') || '—'],
            [c.fullnameLabel || '<?= t('Full Name') ?>', getValue('fullname') || '—'],
            [c.emailLabel || '<?= t('Email Address') ?>', getValue('email') || '—'],
            [c.discordLabel || '<?= t('Discord Tag') ?>', getValue('discord_tag') || '—'],
            [c.countryLabel || '<?= t('Country') ?>', getValue('country') || '—'],
            [c.languagesLabel || '<?= t('Languages') ?>', getValue('languages') || '—']
        ];

        if (c.showAge === true) {
            rows.push([c.ageLabel || '<?= t('Age') ?>', getValue('age') || '—']);
        }

        rows.push([c.ingameLabel || '<?= t('In Game Name') ?>', getValue('ingame_name') || '—']);

        if (c.showServer === true) {
            rows.push([c.serverLabel || '<?= t('Server / Region') ?>', getValue('server_region') || '—']);
        }

        rows.push(
            [c.currentRankLabel || '<?= t('Current Rank') ?>', getValue('current_rank') || '—'],
            [c.peakRankLabel || '<?= t('Peak Rank') ?>', getValue('peak_rank') || '—'],
            [c.champPoolLabel || '<?= t('Champion Pool / Main Roles') ?>', getValue('champ_pool') || '—']
        );

        if (c.showVpn === true) {
            rows.push([c.vpnLabel || '<?= t('VPN / Setup') ?>', getValue('has_vpn') || 'No']);
        }

        rows.push(
            [c.availabilityLabel || '<?= t('Availability') ?>', getValue('availability') || '—'],
            [c.experienceLabel || '<?= t('Previous Experience') ?>', getValue('experience') || '—'],
            [c.motivationLabel || '<?= t('Why do you want to join?') ?>', getValue('motivation') || '—'],
            [c.referralLabel || '<?= t('How did you hear about us?') ?>', getValue('referral') || '—']
        );

        reviewCard.innerHTML = rows.map(function (row) {
            return '<div class="review-row"><div class="review-key">' + row[0] + '</div><div class="review-value">' + row[1] + '</div></div>';
        }).join('');
    }

    // The generic "Booster" role card (data-role="booster") is not itself a valid
    // submission value — submit_job_application() in ajax.php only accepts
    // lol_booster/tft_booster/val_booster/gg_girl/seller. Picking the game here
    // is what actually determines which of the three booster roles gets submitted.
    // Match on slug first, then fall back to a normalized game name so a differing
    // DB slug can't silently leave the role at the rejected "booster" value.
    function boostGameToRole(slug, name) {
        var s = (slug || '').toLowerCase();
        var n = (name || '').toLowerCase();
        if (s === 'league-of-legends' || (n.indexOf('league of legends') !== -1 && n.indexOf('wild') === -1)) return 'lol_booster';
        if (s === 'teamfight-tactics' || n.indexOf('teamfight') !== -1 || n.indexOf('tft') !== -1) return 'tft_booster';
        if (s === 'valorant' || n.indexOf('valorant') !== -1) return 'val_booster';
        return null;
    }

    document.querySelectorAll('.boost-game-option').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.boost-game-option').forEach(function (b) { b.classList.remove('selected'); });
            btn.classList.add('selected');
            selectedBoostGame = btn.getAttribute('data-game') || '';
            var hidden = document.getElementById('fieldBoostGame');
            var otherWrap = document.getElementById('wrapBoostGameOther');
            var otherInput = document.getElementById('fieldBoostGameOther');
            var isOther = selectedBoostGame === 'Other';
            if (otherWrap) otherWrap.style.display = isOther ? '' : 'none';
            if (isOther) {
                if (otherInput) otherInput.focus();
                if (hidden) hidden.value = otherInput ? otherInput.value : '';
            } else {
                if (hidden) hidden.value = selectedBoostGame;
            }

            if (selectedRole === 'booster' || boosterRoles.indexOf(selectedRole) !== -1) {
                var mappedRole = boostGameToRole(btn.getAttribute('data-slug'), selectedBoostGame);
                fieldRole.value = mappedRole || 'booster';
            }

            // Apply game-specific field labels/placeholders. "Other" + unknown games
            // → empty override → generic booster questions.
            var gameKey = normalizeGameKey(btn.getAttribute('data-slug'), selectedBoostGame);
            activeGameOverrides = gameFieldConfigs[gameKey] || {};
            applyGameFields();

            resetFieldError('boost_game');
        });
    });

    (function () {
        var otherInput = document.getElementById('fieldBoostGameOther');
        var hidden = document.getElementById('fieldBoostGame');
        if (otherInput && hidden) {
            otherInput.addEventListener('input', function () {
                hidden.value = otherInput.value;
                resetFieldError('boost_game');
            });
        }
    })();

    roleCards.forEach(function (card) {
        card.addEventListener('click', function () {
            roleCards.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            var radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            applyRoleConfig(card.getAttribute('data-role'));
        });
    });

    document.getElementById('btnToStep2').addEventListener('click', function () {
        if (!validateStep(1)) return;
        setPane(2);
    });

    document.getElementById('btnToStep3').addEventListener('click', function () {
        if (!validateStep(2)) return;
        setPane(3);
    });

    document.getElementById('btnToStep4').addEventListener('click', function () {
        if (!validateStep(3)) return;
        setPane(4);
    });

    document.getElementById('btnToStep5').addEventListener('click', function () {
        if (!validateStep(4)) return;
        buildReview();
        setPane(5);
    });

    document.querySelectorAll('[data-prev-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = Number(btn.getAttribute('data-prev-step'));
            setPane(target);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validateStep(4)) {
            setPane(4);
            return;
        }

        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = '<?= t('Submitting...') ?>';

        var formData = new FormData(form);
        var ajaxEndpoint = (typeof ajax_url !== 'undefined' && ajax_url) ? ajax_url : '<?= AJAX_URL ?>';

        fetch(ajaxEndpoint, {
            method: 'POST',
            body: formData
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(function (text) {
            var res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                console.error('Non JSON response:', text);
                throw new Error('invalid_json');
            }

            if (res && res.status === 'success') {
                form.style.display = 'none';
                successBox.classList.add('visible');
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = '<?= t('Submit Application') ?>';
                alert(res && res.message ? res.message : '<?= t('Something went wrong. Please try again.') ?>');
            }
        })
        .catch(function (err) {
            console.error('Apply submit error:', err);
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = '<?= t('Submit Application') ?>';
            alert('<?= t('Something went wrong. Please try again.') ?>');
        });
    });
});
</script>

<script>
/* ═══ ANIMATION ENHANCEMENTS ═══ */
(function(){
    // Ripple effect on next/submit buttons
    document.addEventListener('click', function(e){
        var btn = e.target.closest('.btn-next, .btn-submit');
        if (!btn || btn.disabled) return;
        var r = document.createElement('span');
        r.className = 'ripple';
        var rect = btn.getBoundingClientRect();
        r.style.left = (e.clientX - rect.left) + 'px';
        r.style.top  = (e.clientY - rect.top) + 'px';
        btn.appendChild(r);
        setTimeout(function(){ r.remove(); }, 520);
    });

    // Back button: add from-back class to incoming pane
    document.querySelectorAll('[data-prev-step]').forEach(function(btn){
        btn.addEventListener('click', function(){
            setTimeout(function(){
                var active = document.querySelector('.wizard-pane.active');
                if (active) active.classList.add('from-back');
                setTimeout(function(){
                    if (active) active.classList.remove('from-back');
                }, 300);
            }, 0);
        });
    });

    // Floating particles in hero
    var hero = document.querySelector('.apply-hero');
    if (hero) {
        var colors = [
            'rgba(99,102,241,.18)',
            'rgba(99,102,241,.14)',
            'rgba(56,189,248,.12)',
            'rgba(99,102,241,.16)',
            'rgba(74,222,128,.10)'
        ];
        for (var i = 0; i < 14; i++) {
            var p = document.createElement('div');
            p.className = 'apply-particle';
            var size = 3 + Math.random() * 8;
            p.style.cssText = [
                'width:' + size + 'px',
                'height:' + size + 'px',
                'left:' + (Math.random() * 100) + '%',
                'top:' + (20 + Math.random() * 80) + '%',
                'background:' + colors[Math.floor(Math.random() * colors.length)],
                'animation-duration:' + (5 + Math.random() * 10) + 's',
                'animation-delay:' + (-Math.random() * 8) + 's',
                'opacity:' + (.2 + Math.random() * .5)
            ].join(';');
            hero.appendChild(p);
        }
    }
})();
</script>
</body>
</html>
