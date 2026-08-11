<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $meta['title'] ?></title>
    <meta name="description" content="<?= $meta['description'] ?>">

    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="shortcut icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css">

    <style>
        :root{
            --lb-bg:#0b0d15;
            --lb-panel:#171a21;
            --lb-panel-2:#20232c;
            --lb-border:rgba(255,255,255,.10);
            --lb-border-strong:rgba(255,255,255,.18);
            --lb-text:#fff;
            --lb-muted:rgba(255,255,255,.64);
            --lb-soft:rgba(255,255,255,.08);
            --lb-primary:#7c5cff;
            --lb-primary-2:#3a72ff;
            --lb-danger:#ff5b7c;
        }
        *{box-sizing:border-box}
        html,body{min-height:100%;margin:0}
        body{font-family:Montserrat,system-ui,-apple-system,Segoe UI,sans-serif;background:var(--lb-bg);color:var(--lb-text);overflow-x:hidden}
        a{color:inherit;text-decoration:none}
        button,input,textarea{font:inherit}
        .lb-auth-shell{min-height:100vh;display:grid;grid-template-columns:minmax(0,1.06fr) minmax(460px,.94fr);background:radial-gradient(circle at 75% 10%,rgba(124,92,255,.22),transparent 32%),#12151b}
        .lb-auth-hero{position:relative;min-height:100vh;display:flex;align-items:center;padding:clamp(48px,7vw,120px);overflow:hidden;background-image:linear-gradient(90deg,rgba(3,5,15,.72),rgba(5,7,20,.88)),url('<?= ASSET_URL ?>/core/main/img/banners/leona.jpeg');background-repeat:no-repeat;background-size:cover;background-position:center}
        .lb-auth-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 28% 36%,rgba(124,92,255,.20),transparent 28%),linear-gradient(180deg,rgba(0,0,0,.18),rgba(0,0,0,.62));pointer-events:none}
        .lb-auth-hero-inner{position:relative;z-index:1;max-width:720px}
        .lb-brand-row{display:flex;align-items:center;gap:16px;margin-bottom:54px}
        .lb-brand-logo{width:54px;height:54px;object-fit:contain;filter:drop-shadow(0 0 18px rgba(124,92,255,.55))}
        .lb-brand-text{font-size:22px;font-weight:900;letter-spacing:.03em;text-transform:uppercase}
        .lb-area-badge{display:inline-flex;align-items:center;gap:10px;margin-left:12px;padding:10px 18px;border-radius:999px;border:1px solid var(--lb-border-strong);background:rgba(255,255,255,.08);font-size:12px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.88)}
        .lb-hero-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;background:rgba(124,92,255,.12);border:1px solid rgba(124,92,255,.28);color:#c9bcff;font-size:11px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;margin-bottom:22px}
        .lb-hero-title{margin:0;font-size:clamp(52px,5.7vw,92px);line-height:.92;font-weight:900;letter-spacing:-.07em}
        .lb-hero-title span{color:#ad9bff;text-shadow:0 0 36px rgba(124,92,255,.34)}
        .lb-hero-copy{margin:24px 0 0;max-width:650px;color:rgba(255,255,255,.86);font-size:20px;line-height:1.55;font-weight:600}
        .lb-feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:46px;max-width:660px}
        .lb-feature-card{padding:18px 20px;border-radius:18px;background:rgba(255,255,255,.07);border:1px solid var(--lb-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
        .lb-feature-card strong{display:block;font-size:16px;font-weight:900;margin-bottom:8px}.lb-feature-card span{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.56)}
        .lb-auth-panel{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px;background:radial-gradient(circle at 50% 0%,rgba(124,92,255,.20),transparent 34%),linear-gradient(180deg,#171a22,#11161a)}
        .lb-auth-card{width:100%;max-width:480px;padding:44px;border-radius:28px;background:linear-gradient(180deg,rgba(35,38,48,.96),rgba(25,28,36,.96));border:1px solid var(--lb-border-strong);box-shadow:0 28px 80px rgba(0,0,0,.44),0 0 70px rgba(124,92,255,.12)}
        .lb-auth-title{text-align:center;margin:0 0 8px;font-size:46px;line-height:1;font-weight:900;letter-spacing:-.06em}.lb-auth-subtitle{text-align:center;margin:0 0 38px;color:var(--lb-muted);font-size:15px;font-weight:600}
        .lb-field{margin-bottom:22px}.lb-label{display:block;margin:0 0 10px;color:#fff;font-size:14px;font-weight:800}.lb-input-wrap{position:relative}.lb-input{width:100%;height:60px;border-radius:16px;border:1px solid rgba(255,255,255,.13);background:rgba(255,255,255,.055);color:#fff;padding:0 18px;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}.lb-input::placeholder{color:rgba(255,255,255,.35);font-weight:600}.lb-input:focus{border-color:rgba(124,92,255,.72);background:rgba(255,255,255,.075);box-shadow:0 0 0 4px rgba(124,92,255,.16)}
        .lb-password-toggle{position:absolute;right:8px;top:8px;width:44px;height:44px;border:1px solid rgba(255,255,255,.10);border-radius:12px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.70);display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.lb-password-toggle:hover{background:rgba(255,255,255,.10);color:#fff}
        .lb-check{display:flex;align-items:center;gap:10px;margin:4px 0 24px;color:rgba(255,255,255,.72);font-size:14px;font-weight:700}.lb-check input{appearance:none;width:18px;height:18px;border-radius:5px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.05);display:grid;place-items:center;cursor:pointer}.lb-check input:checked{background:linear-gradient(135deg,var(--lb-primary),var(--lb-primary-2));border-color:transparent}.lb-check input:checked::after{content:"✓";font-size:12px;font-weight:900;color:white}
        .lb-error{display:none;margin:0 0 18px;padding:13px 15px;border-radius:14px;background:rgba(255,91,124,.12);border:1px solid rgba(255,91,124,.28);color:#ffb6c4;font-size:13px;font-weight:700}.lb-submit{width:100%;height:64px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--lb-primary),var(--lb-primary-2));color:#fff;font-size:16px;font-weight:900;cursor:pointer;box-shadow:0 18px 44px rgba(90,75,255,.34);transition:transform .16s,filter .16s}.lb-submit:hover{transform:translateY(-1px);filter:brightness(1.06)}.indicator-progress,.indicator-success{display:none}
        .invalid-feedback{display:none;margin-top:8px;color:#ff9bad;font-size:12px;font-weight:700}.lb-auth-card .text-center{text-align:center}.lb-auth-card .mb-5{margin-bottom:38px}.lb-auth-card .mb-4{margin-bottom:22px}.lb-auth-card .display-5{margin:0 0 8px;font-size:46px;line-height:1;font-weight:900;letter-spacing:-.06em}.lb-auth-card p{color:var(--lb-muted);font-size:15px;font-weight:600;margin:0}.lb-auth-card label{display:block;margin:0 0 10px;color:#fff;font-size:14px;font-weight:800}.lb-auth-card label.lb-check{display:flex;margin:4px 0 24px}.lb-auth-card input[type=email],.lb-auth-card input[type=password],.lb-auth-card input[type=text],.lb-auth-card textarea{width:100%;height:60px;border-radius:16px;border:1px solid rgba(255,255,255,.13);background:rgba(255,255,255,.055);color:#fff;padding:0 18px;outline:none}.lb-auth-card textarea{height:auto;min-height:110px;padding:16px 18px}.input-group{position:relative}.input-group .input-group-text{position:absolute;right:8px;top:8px;width:44px;height:44px;border:1px solid rgba(255,255,255,.10);border-radius:12px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.70);display:inline-flex;align-items:center;justify-content:center}.form-check{display:flex;align-items:center;gap:10px}.form-check-input{appearance:none;width:18px!important;height:18px!important;border-radius:5px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.05);padding:0!important}.form-check-input:checked{background:linear-gradient(135deg,var(--lb-primary),var(--lb-primary-2));border-color:transparent}.form-check-label{margin:0!important;color:rgba(255,255,255,.72)!important}.alert-soft-danger{display:none;margin:0 0 18px;padding:13px 15px;border-radius:14px;background:rgba(255,91,124,.12);border:1px solid rgba(255,91,124,.28);color:#ffb6c4}.d-grid{display:block}.btn{width:100%;height:64px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--lb-primary),var(--lb-primary-2));color:#fff;font-size:16px;font-weight:900;cursor:pointer;box-shadow:0 18px 44px rgba(90,75,255,.34)}
        .lb-auth-footer-link{margin-top:22px;text-align:center;color:var(--lb-muted);font-size:13px;font-weight:700}.lb-auth-footer-link a{color:#c9bcff;font-weight:900}.lb-mini-panel{margin:0 0 24px;padding:16px;border-radius:18px;background:rgba(124,92,255,.10);border:1px solid rgba(124,92,255,.22)}.lb-mini-panel-title{margin:0 0 10px;color:#c9bcff;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.lb-benefit-list{list-style:none;margin:0;padding:0;display:grid;gap:8px}.lb-benefit-list li{display:flex;gap:9px;color:rgba(255,255,255,.68);font-size:13px;font-weight:700}.lb-benefit-list i{color:#44d7a8;margin-top:2px}.lb-success-state{text-align:center}.lb-success-icon{width:72px;height:72px;margin:0 auto 20px;border-radius:50%;display:grid;place-items:center;background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.30);color:#4ade80;font-size:28px}.lb-auth-card h3,.lb-auth-card h4{margin:0 0 8px;font-size:30px;line-height:1.1;font-weight:900;letter-spacing:-.04em}.lb-auth-card .small{font-size:13px}.lb-required{color:#ff7d99}.lb-link{color:#c9bcff;font-weight:900}
        @media(max-width:980px){
            /* Hero komplett ausblenden auf Mobile */
            .lb-auth-hero { display: none; }

            /* Shell: einfaches Flex-Layout, füllt echten Viewport */
            .lb-auth-shell {
                display: flex;
                flex-direction: column;
                grid-template-columns: unset;
                min-height: 100dvh;
                width: 100%;
                background: radial-gradient(circle at 50% 0%, rgba(124,92,255,.22), transparent 40%), #12151b;
            }

            /* Panel füllt gesamten Platz, zentriert die Card */
            .lb-auth-panel {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px 16px 40px;
                min-height: unset;
                width: 100%;
                box-sizing: border-box;
                background: transparent;
            }

            /* Card: fluid, keine fixen Breiten die beim Zoomen brechen */
            .lb-auth-card {
                width: 100%;
                max-width: min(480px, 100%);
                padding: clamp(22px, 6vw, 36px);
                border-radius: 22px;
                box-sizing: border-box;
            }

            /* Typografie */
            .lb-auth-title,
            .lb-auth-card .display-5 { font-size: clamp(32px, 9vw, 46px); }
            .lb-auth-subtitle,
            .lb-auth-card p { font-size: 14px; }
            .lb-auth-card label { font-size: 13px; }

            /* Inputs skalieren mit Zoom */
            .lb-auth-card input[type=email],
            .lb-auth-card input[type=password],
            .lb-auth-card input[type=text],
            .lb-input { height: 56px; font-size: 15px; }

            /* Submit */
            .lb-submit, .btn { height: 58px; font-size: 15px; }
        }
    </style>
</head>
<body>
    <main class="lb-auth-shell">
        <section class="lb-auth-hero">
            <div class="lb-auth-hero-inner">
                <div class="lb-brand-row">
                    <img class="lb-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon.svg" alt="LoLBoost.gg">
                    <div class="lb-brand-text">LOLBOOST.GG</div>
                    <span class="lb-area-badge"><i class="fa-solid fa-shield-halved"></i> Admin Area</span>
                </div>
                <div class="lb-hero-kicker"><i class="fa-solid fa-bolt"></i> Control Center</div>
                <h1 class="lb-hero-title">Admin <span>Control</span></h1>
                <p class="lb-hero-copy">Manage orders, clients, boosters and marketplace operations from one clean command center.</p>
                <div class="lb-feature-grid">
                    <div class="lb-feature-card"><strong>Secure</strong><span>Protected login</span></div>
                    <div class="lb-feature-card"><strong>Fast</strong><span>Dashboard access</span></div>
                    <div class="lb-feature-card"><strong>Live</strong><span>Real-time tools</span></div>
                </div>
            </div>
        </section>
        <section class="lb-auth-panel">
            <div class="lb-auth-card">
                <?= $this->section('content') ?>
            </div>
        </section>
    </main>
    <script>
        const asset_url = '<?= ASSET_URL ?>';
        const ajax_url = '<?= AJAX_URL ?>';
        const base_url = '<?= BASE_URL ?>';
    </script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery-migrate/dist/jquery-migrate.min.js"></script>
    <script src="<?= ASSET_URL ?>/core/dash/js/ajax.js?<?= rand(0, 34534) ?>"></script>
    <script>
        document.addEventListener('click', function(e){
            const toggle = e.target.closest('[data-lb-toggle-password], #password_target, #toggleApplyPw');
            if(!toggle) return;
            e.preventDefault();
            const selector = toggle.getAttribute('data-lb-toggle-password');
            const input = selector ? document.querySelector(selector) : (toggle.id === 'toggleApplyPw' ? document.getElementById('applyPassword') : document.getElementById('password'));
            if(!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            const icon = toggle.querySelector('i');
            if(icon) icon.className = input.type === 'password' ? 'fa-duotone fa-eye' : 'fa-duotone fa-eye-slash';
        });
    </script>
    <?= $this->section('scripts') ?>
</body>
</html>
