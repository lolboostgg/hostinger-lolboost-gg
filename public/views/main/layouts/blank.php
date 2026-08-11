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
  <meta name="theme-color" content="#ffffff">
  <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css" />

  <!-- Vendor Styles -->
  <?= $this->section('styles') ?>

  <!-- Main Theme Styles + Bootstrap -->
  <link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/css/theme.css?v10.3">
  <link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/core/main/css/main.css?v=<?= rand(0, 9340) ?>">

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

    
</head>


<!-- Body -->

<body>



  <?= $this->section('content') ?>


        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-596N9MJ3"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->


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
  <script src="<?= ASSET_URL ?>/origin/main/vendor/parallax-js/dist/parallax.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.0/gsap.min.js"></script>
  <?= $this->section('scripts') ?>

  <!-- Main Theme Script -->
  <script src="<?= ASSET_URL ?>/origin/main/js/theme.min.js?v10"></script>
  <script src="<?= ASSET_URL ?>/core/main/js/main.js?v<?= rand(0, 58384) ?>"></script>
</body>

</html>