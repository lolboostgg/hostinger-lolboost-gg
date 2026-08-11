<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $assetVersion = static function (string $relativePath): string {
            $publicRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $fullPath = $publicRoot . '/' . ltrim($relativePath, '/');
            return is_file($fullPath) ? (string)filemtime($fullPath) : '1';
        };
        $requestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
        $host = $_SERVER['HTTP_HOST'] ?? 'lolboost.gg';
        $canonical = $meta['canonical'] ?? ('https://' . $host . $requestPath);
    ?>
    <title><?= htmlspecialchars($meta['title'] ?? 'Checkout | LoLBoost', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= ASSET_URL ?>/core/main/img/logos/SVG/icon-bg.svg?v6">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
    <link href="<?= ASSET_URL ?>/core/main/plugins/fa/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/website/css/main.css?v=<?= $assetVersion('/public/assets/website/css/main.css') ?>">
    <?= $this->section('styles') ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/website/css/checkout-standalone.css?v=<?= $assetVersion('/public/assets/website/css/checkout-standalone.css') ?>">
</head>
<body class="<?= htmlspecialchars((string)($bodyClass ?? 'checkout'), ENT_QUOTES, 'UTF-8') ?> checkout-standalone">
    <div class="checkout-shell">
        <div class="checkout-site-header" role="banner">
            <a class="checkout-brand" href="<?= BASE_URL ?>" aria-label="LoLBoost main site">
                <img src="<?= ASSET_URL ?>/website/images/logo.svg" alt="LoLBoost">
                <span>LOLBOOST.GG</span>
            </a>
            <a class="checkout-back-link" href="<?= BASE_URL ?>">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span><?= t('Back to Main Site') ?></span>
            </a>
        </div>

        <main class="checkout-page-main">
            <?= $this->section('content') ?>
            <?= $this->insert('website/partials/auth') ?>
        </main>

        <div class="checkout-site-footer" role="contentinfo">
            <span>&copy; <?= date('Y') ?> LoLBoost.gg</span>
            <nav aria-label="Legal">
                <a href="<?= BASE_URL ?>/legal/terms"><?= t('Terms of Service') ?></a>
                <a href="<?= BASE_URL ?>/legal/privacy"><?= t('Privacy Policy') ?></a>
                <a href="<?= BASE_URL ?>/legal/imprint"><?= t('Imprint') ?></a>
            </nav>
        </div>
    </div>

    <div id="toast-container"></div>
    <script src="<?= ASSET_URL ?>/origin/dash/vendor/jquery/dist/jquery.min.js"></script>
    <script>
        const asset_url = '<?= ASSET_URL ?>';
        const ajax_url = '<?= AJAX_URL ?>';
        const base_url = '<?= BASE_URL ?>';
    </script>
    <script src="<?= ASSET_URL ?>/core/main/js/main.js?v=<?= $assetVersion('/public/assets/core/main/js/main.js') ?>"></script>
    <script src="<?= ASSET_URL ?>/website/js/main.js?v=<?= $assetVersion('/public/assets/website/js/main.js') ?>"></script>
    <?= $this->section('scripts') ?>
</body>
</html>
