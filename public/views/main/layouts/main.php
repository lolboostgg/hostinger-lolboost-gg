<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?= $meta['title'] ?></title>

  <!-- SEO Meta Tags -->
  <meta name="description" content="<?= $meta['description'] ?>">
  <meta name="keywords" content="<?= $meta['keywords'] ?>">

  <!-- Viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Favicon and Touch Icons -->
  <link rel="icon" type="image/svg+xml" sizes="64x64" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
  <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
  <meta property="robots" content="index, follow" />
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= $meta['title'] ?>">
  <meta property="og:description" content="<?= $meta['description'] ?>">
  <meta property="og:image" content="https://i.imgur.com/DQlZi4z.png">
  <!-- Twitter -->
  <meta property="twitter:card" content="<?= !empty($meta['summary']) ? 'summary' : 'summary_large_image' ?>">
  <meta property="twitter:title" content="<?= $meta['title'] ?>">
  <meta property="twitter:description" content="<?= $meta['description'] ?>">
  <meta property="twitter:image" content="https://i.imgur.com/DQlZi4z.png">
  <link rel="image_src" href="https://i.imgur.com/DQlZi4z.png" />
  <meta name="theme-color" content="#6366F1">
  <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

  <!-- Vendor Styles -->
  <?= $this->section('styles') ?>

  <!-- Main Theme Styles + Bootstrap -->
  <link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/css/custom.css?v5.4">
  <link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/css/theme.css?v10.3">
  <link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/core/main/css/main.css?v=<?= rand(0, 9340) ?>">
  <script type="text/javascript" src="<?= ASSET_URL ?>/origin/main/js/themeswitcher.js?v=1.3"></script>


  <!-- ThemeSwitcher -->
  <script type="text/javascript">
    (function () {
      const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;
      const userChoice = localStorage.getItem("theme");
      const defaultTheme = prefersDarkScheme ? "dark" : "light";
      const theme = userChoice || defaultTheme;
      document.documentElement.setAttribute("data-user-theme", theme);
    })();
  </script>
    

  <link rel="alternate" hreflang="en" href="https://lolboost.gg">
  <link rel="alternate" hreflang="de" href="https://de.lolboost.gg">
  
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-596N9MJ3');</script>
    <!-- End Google Tag Manager -->
    
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11473081744"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'AW-11473081744');
    </script>
    
    <script>
        (function(w,d,s,r,n){w.TrustpilotObject=n;w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)};
            a=d.createElement(s);a.async=1;a.src=r;a.type='text/java'+s;f=d.getElementsByTagName(s)[0];
            f.parentNode.insertBefore(a,f)})(window,document,'script', 'https://invitejs.trustpilot.com/tp.min.js', 'tp');
            tp('register', '2I5GtBMcPBJioQv4');
    </script>
  <!-- Body -->

<body>

  <header </header>
    <!-- Page wrapper for sticky footer -->
    <!-- Wraps everything except footer to push footer to the bottom of the page if there is little content -->
    <main class="page-wrapper">

      <!-- Navbar -->
      <?= $this->insert('main/partials/header', ['inv' => $inv ?? 0]) ?>
      <div class="toast-container d-flex flex-column justify-content-center align-items-center w-100 position-fixed"
        style="top: 95px;"></div>
      <div id="smooth-wrapper">
        <div id="smooth-content">
          <?= $this->section('content') ?>


          <!-- Footer -->
          <?= $this->insert('main/partials/footer') ?>
        </div>
      </div>
    </main>




    <!-- Back to top button -->
    <a href="#top" class="btn-scroll-top" data-scroll>
      <span class="btn-scroll-top-tooltip text-muted fs-sm me-2">Top</span>
      <i class="btn-scroll-top-icon fa-solid fa-angle-up"></i>
    </a>
    <?= $this->insert('main/pages/auth', ['reset_password' => $meta['reset_password'] ?? null]) ?>
    <!-- Vendor Scripts -->
    <script>
      const asset_url = '<?= ASSET_URL ?>';
      const ajax_url = '<?= AJAX_URL ?>';
      const base_url = '<?= BASE_URL ?>';
    </script>
    <script src="<?= ASSET_URL ?>/origin/main/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSET_URL ?>/origin/main/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
      integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.0/gsap.min.js"></script>
    <?= $this->section('scripts') ?>

    <!-- Main Theme Script -->
    <script src="<?= ASSET_URL ?>/origin/main/js/theme.min.js?v10"></script>
    <script src="<?= ASSET_URL ?>/core/main/js/main.js?v<?= rand(0, 58384) ?>"></script>

 <!--   <script src="//code.tidio.co/udfsz5ed6jd3de8ak15fecnbrhnsnkl1.js" async></script>
    <script>
      (function () {
        function onTidioChatApiReady() {
          window.tidioChatApi.show();
          <?php
          if (defined('CLIENT_DATA') && !empty(CLIENT_DATA)) {
            ?>
            tidioChatApi.setVisitorData({
              email: "<?= CLIENT_DATA['email'] ?>",
              name: "<?= CLIENT_DATA['username'] ?> - <?= CLIENT_DATA['id'] ?>",
            });
            <?php
          }
          ?>
        }

        if (window.tidioChatApi) {
          window.tidioChatApi.on('ready', onTidioChatApiReady);
        } else {
          document.addEventListener('tidioChat-ready', onTidioChatApiReady);
        }
        $('.modal').on('shown.bs.modal', function (event) {
          window.tidioChatApi.display(false);
        });
        $('.modal').on('hidden.bs.modal', function (event) {
          window.tidioChatApi.display(true);
        });
        $('.open-chat').on('click', function () {
          if (window.tidioChatApi) {
            window.tidioChatApi.show();
            window.tidioChatApi.open();
          }
        });

      })();
    </script>
    
    
  <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();

(function(){
  var s1=document.createElement("script"),
      s0=document.getElementsByTagName("script")[0];
  s1.async=true;
  s1.src='https://embed.tawk.to/67bb7c56c8da001911a6ba46/1ikq5rcpg';
  s1.charset='UTF-8';
  s1.setAttribute('crossorigin','*');
  s0.parentNode.insertBefore(s1,s0);
})();

// Visitor Data setzen, sobald API verfügbar ist
Tawk_API.onLoad = function(){
  <?php if (defined('CLIENT_DATA') && !empty(CLIENT_DATA)) { ?>
    Tawk_API.setAttributes({
      'name'  : "<?= CLIENT_DATA['username'] ?> - <?= CLIENT_DATA['id'] ?>",
      'email' : "<?= CLIENT_DATA['email'] ?>"
    }, function(error){});
  <?php } ?>
};
</script>
<!--End of Tawk.to Script-->
    
            <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-596N9MJ3"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
</body>

</html>