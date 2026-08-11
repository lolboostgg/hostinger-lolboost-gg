<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Resource hints: warm up connections to third-party hosts before they're needed,
         this saves a DNS+TLS round trip on mobile for each one (biggest win on slow networks). -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://embed.tawk.to" crossorigin>
    <link rel="dns-prefetch" href="https://embed.tawk.to">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts moved out of main.css: an @import inside a stylesheet forces the
         browser to download+parse main.css first before it even discovers the font request,
         adding a full extra round trip before text can render. Loading it here lets it
         fetch in parallel with everything else (critical for first-time visitors on mobile). -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">

    <?php
        $bodyClassString = (string)($bodyClass ?? '');
        $isLandingPage = strpos($bodyClassString, 'landing') !== false;
        $isGameHubPage = strpos($bodyClassString, 'game-hub') !== false;
        $isServicesHubPage = strpos($bodyClassString, 'services-hub-page') !== false;
        $isLolBoostPage = strpos($bodyClassString, 'lol-boost') !== false;
        $needsEnhancedSelects = !$isLandingPage && !$isGameHubPage && !$isServicesHubPage;
        $assetVersion = static function (string $relativePath): string {
            $publicRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $fullPath = $publicRoot . '/' . ltrim($relativePath, '/');
            return is_file($fullPath) ? (string)filemtime($fullPath) : '1';
        };
    ?>

    <title><?= htmlspecialchars($meta['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></title>

    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <?php
        $requestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $requestPath = $requestPath ?: '/';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
        $host = $_SERVER['HTTP_HOST'] ?? 'lolboost.gg';
        $defaultCanonical = $scheme . '://' . $host . $requestPath;
        $canonical = $meta['canonical'] ?? $defaultCanonical;
        $robots = $meta['robots'] ?? 'index,follow';
    ?>

    <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
    <?php if ($isLandingPage): ?>
    <!-- The landing template contains a large inline style block before the hero markup.
         Preloading here lets the browser start the LCP image without waiting to parse it. -->
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/landing/lolboost-hero-multigame7.webp" media="(max-width: 820px)" fetchpriority="high">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/landing/lolboost-hero-multigame6.webp" media="(min-width: 821px)" fetchpriority="high">
    <!-- Prepare the first mobile game grid while the user is still viewing the hero.
         Low priority keeps these requests behind the hero's LCP image. -->
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/lol.webp" media="(max-width: 820px)" fetchpriority="low">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/val.webp" media="(max-width: 820px)" fetchpriority="low">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/lol-classic.webp" media="(max-width: 820px)" fetchpriority="low">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/tft.webp" media="(max-width: 820px)" fetchpriority="low">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/cod.webp" media="(max-width: 820px)" fetchpriority="low">
    <link rel="preload" as="image" href="<?= ASSET_URL ?>/website/images/banner/apex.webp" media="(max-width: 820px)" fetchpriority="low">
    <?php endif; ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($meta['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= ASSET_URL ?>/website/images/social-preview.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="<?= !empty($meta['summary']) ? 'summary' : 'summary_large_image' ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($meta['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="twitter:image" content="<?= ASSET_URL ?>/website/images/social-preview.png">
    <link rel="image_src" href="<?= ASSET_URL ?>/website/images/social-preview.png" />
    <meta name="theme-color" content="#6366F1">

    <!-- Font Awesome is icon-only and not needed for first paint: load it without blocking
         rendering (preload + swap media on load, noscript fallback keeps it working without JS). -->
    <link rel="preload" href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" as="style">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" media="print" onload="this.media='all'" />
    <noscript><link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" /></noscript>

    <!-- Styles -->
    <link rel="preload" href="<?= ASSET_URL ?>/website/css/main.css?v=<?= $assetVersion('/public/assets/website/css/main.css') ?>" as="style">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/website/css/main.css?v=<?= $assetVersion('/public/assets/website/css/main.css') ?>">
    <?php if ($needsEnhancedSelects): ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <?php endif; ?>
    <?php if (!$isLandingPage && !$isGameHubPage): ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/website/css/filterbar_addon.css?v=<?= $assetVersion('/public/assets/website/css/filterbar_addon.css') ?>">
    <?php endif; ?>

    <?= $this->section('styles') ?>

    <link rel="alternate" hreflang="en" href="https://lolboost.gg">
    <link rel="alternate" hreflang="de" href="https://de.lolboost.gg">

    
    
    <!-- Event snippet for Purchase NEW 2026 conversion page -->
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-596N9MJ3');</script>
<!-- End Google Tag Manager -->

    <!-- Google Ads / gtag (ONLY ONCE) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11473081744"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-11473081744');
    </script>

</head>

<body class="<?= $bodyClass ?>">
    <!-- GTM (noscript) BEST PRACTICE: direkt nach <body> -->
    <noscript>
      <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-596N9MJ3"
              height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>

    <div class="page-zoom">
<?= $this->insert('website/partials/header') ?>

    <main>
        <?php if ($isLolBoostPage): ?>
        <div class="lb-boost-zoom">
        <?php endif; ?>
        <?= $this->section('content') ?>
        <?php if ($isLolBoostPage): ?>
        </div>
        <?php endif; ?>
        <?= $this->insert('website/partials/auth') ?>
    </main>

    <div id="toast-container"></div>

    <?= $this->insert('website/partials/footer') ?>

    </div>

    <!-- jQuery: CDN + Fallback (verhindert 60s "Alles hängt") -->
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>

    <script>
        const asset_url = '<?= ASSET_URL ?>';
        const ajax_url  = '<?= AJAX_URL ?>';
        const base_url  = '<?= BASE_URL ?>';
    </script>

    <?php if ($needsEnhancedSelects): ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php endif; ?>

    <script src="<?= ASSET_URL ?>/core/main/js/main.js?v=<?= $assetVersion('/public/assets/core/main/js/main.js') ?>"></script>
    <script src="<?= ASSET_URL ?>/website/js/main.js?v=<?= $assetVersion('/public/assets/website/js/main.js') ?>"></script>

    <?= $this->section('scripts') ?>

    <!-- Live chat is loaded after the critical rendering phase or immediately on chat intent. -->
    <script>
      (function () {
        var loading = false;
        var loaded = false;
        var openAfterLoad = false;

        window.lbLoadTawk = function (openChat) {
          if (openChat) openAfterLoad = true;

          if (loaded) {
            if (openAfterLoad && window.Tawk_API && typeof window.Tawk_API.maximize === 'function') {
              openAfterLoad = false;
              window.Tawk_API.maximize();
            }
            return;
          }
          if (loading) return;
          loading = true;

          window.Tawk_API = window.Tawk_API || {};
          window.Tawk_LoadStart = new Date();
          var previousOnLoad = window.Tawk_API.onLoad;
          window.Tawk_API.onLoad = function () {
            loaded = true;
            if (typeof previousOnLoad === 'function') previousOnLoad();
            <?php if (defined('CLIENT_DATA') && !empty(CLIENT_DATA)) { ?>
            window.Tawk_API.setAttributes({
              name: <?= json_encode(CLIENT_DATA['username'] . ' - ' . CLIENT_DATA['id']) ?>,
              email: <?= json_encode(CLIENT_DATA['email']) ?>
            }, function () {});
            <?php } ?>
            if (openAfterLoad && typeof window.Tawk_API.maximize === 'function') {
              openAfterLoad = false;
              window.Tawk_API.maximize();
            }
          };

          var script = document.createElement('script');
          script.async = true;
          script.src = 'https://embed.tawk.to/67bb7c56c8da001911a6ba46/1ikq5rcpg';
          script.charset = 'UTF-8';
          script.crossOrigin = '*';
          script.onerror = function () { loading = false; };
          document.head.appendChild(script);
        };

        window.lbOpenLiveChat = function () {
          window.lbLoadTawk(true);
          return false;
        };

        document.addEventListener('click', function (event) {
          var target = event.target && event.target.closest
            ? event.target.closest('[data-tawk-mobile-support], [data-tawk-open], .sh-cs-chat')
            : null;
          if (target) window.lbLoadTawk(true);
        }, true);

        <?php if (!$isGameHubPage): ?>
        var scheduleChat = function () { window.lbLoadTawk(false); };
        window.addEventListener('load', function () {
          if ('requestIdleCallback' in window) {
            requestIdleCallback(scheduleChat, { timeout: <?= $isLandingPage ? '12000' : '5000' ?> });
          } else {
            setTimeout(scheduleChat, <?= $isLandingPage ? '10000' : '3500' ?>);
          }
        }, { once: true });
        <?php endif; ?>
      })();
    </script>
</body>
</html>
