<?php
// public/views/website/components/checkout/digital_good.php
// Checkout summary component for invoices with order_type = digital_good.

$invoice = is_array($invoice ?? null) ? $invoice : [];
$data    = is_array($data ?? null) ? $data : [];

$h = static function ($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '';

$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;

    // Same normalization as digital-good view/shop pages.
    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#/public/assets/#', '/', $path);
    $path = '/' . ltrim((string)$path, '/');

    return $assetUrl . $path;
};

$currency = strtoupper((string)($invoice['currency'] ?? ($_SESSION['currency'] ?? ($data['currency'] ?? 'EUR'))));
$symbol = function_exists('util_format_currency_display')
    ? util_format_currency_display($currency)
    : ($currency === 'USD' ? '$' : '€');

$formatPrice = static function ($cents) use ($symbol): string {
    $cents = (int)($cents ?? 0);
    if (function_exists('util_format_price_display')) {
        return $symbol . util_format_price_display($cents);
    }
    return $symbol . number_format($cents / 100, 2, '.', ',');
};

$title        = trim((string)($data['title'] ?? $invoice['description'] ?? 'Digital Product'));
$brand        = trim((string)($data['brand'] ?? ''));
$category     = trim((string)($data['category_name'] ?? $data['category'] ?? 'Digital Goods'));
$region       = trim((string)($data['region'] ?? 'Global'));
$deliveryType = strtolower(trim((string)($data['delivery_type'] ?? 'manual')));
$deliveryLabel = ucfirst(str_replace('_', ' ', $deliveryType));
$sellerName   = trim((string)($data['seller_username'] ?? $data['seller_name'] ?? 'Seller'));

$unitPrice = (int)($data['price'] ?? 0);
$invoiceTotal = (int)($invoice['total_price'] ?? $invoice['price'] ?? $invoice['price_eur'] ?? 0);
if ($invoiceTotal <= 0 && $unitPrice > 0) {
    $invoiceTotal = $unitPrice;
}

$quantity = 1;
if ($unitPrice > 0 && $invoiceTotal > 0) {
    $quantity = max(1, (int)round($invoiceTotal / max(1, $unitPrice)));
}
if (!empty($data['_qty'])) {
    $quantity = max(1, (int)$data['_qty']);
}

$brandIconUrl = $normalizeAssetPath($data['brand_icon'] ?? '');
$categoryIcon = trim((string)($data['category_icon'] ?? $data['icon'] ?? 'fa-solid fa-layer-group'));
?>

<style>
.checkout-dg{display:flex;flex-direction:column}
.checkout-dg-product{display:flex;align-items:center;gap:15px;padding:5px 0 20px;margin-bottom:15px;border-bottom:1px solid rgba(255,255,255,.09)}
.checkout-dg-icon{width:58px;height:58px;flex:0 0 58px;padding:9px;border-radius:16px;object-fit:contain;background:linear-gradient(145deg,rgba(99,102,241,.2),rgba(139,92,246,.12));border:1px solid rgba(129,140,248,.3)}
.checkout-dg-icon-fallback{display:flex;align-items:center;justify-content:center;font-size:25px}
.checkout-dg-eyebrow{margin-bottom:7px;color:#9b8cff;font-size:11px;font-weight:900;line-height:1;text-transform:uppercase;letter-spacing:.09em}
.checkout-dg-title{color:#fff;font-size:19px;font-weight:900;line-height:1.25;overflow-wrap:anywhere}
.checkout-dg-brand{display:flex;align-items:center;gap:7px;margin-top:6px;color:rgba(255,255,255,.62);font-size:13px;font-weight:750}
.checkout-dg-brand i{color:#8176ff}
.checkout-dg-benefit{display:flex;align-items:flex-start;gap:10px;padding:8px 0;color:rgba(255,255,255,.9);font-size:13px;font-weight:750;line-height:1.4}
.checkout-dg-benefit i{width:17px;flex:0 0 17px;margin-top:2px;color:#a5b4fc;font-size:13px}
.checkout-dg-benefit small{display:block;margin-top:4px;color:rgba(255,255,255,.55);font-size:12px;font-weight:700}
@media(max-width:600px){.checkout-dg-product{gap:11px}.checkout-dg-icon{width:49px;height:49px;flex-basis:49px;border-radius:14px}.checkout-dg-title{font-size:16px}}
</style>

<div class="checkout-dg">
    <div class="checkout-dg-product">
        <?php if ($brandIconUrl !== ''): ?>
            <img class="checkout-dg-icon" src="<?= $h($brandIconUrl) ?>" alt="<?= $h($brand !== '' ? $brand : $title) ?>">
        <?php else: ?>
            <div class="checkout-dg-icon checkout-dg-icon-fallback"><i class="<?= $h($categoryIcon) ?>"></i></div>
        <?php endif; ?>

        <div>
            <div class="checkout-dg-eyebrow"><?= function_exists('t') ? t('Digital Product') : 'Digital Product' ?></div>
            <div class="checkout-dg-title"><?= $h($title) ?></div>
            <?php if ($brand !== ''): ?>
                <div class="checkout-dg-brand"><i class="fa-solid fa-tag"></i><span><?= $h($brand) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="order-options">
        <div class="option">
            <div class="title"><?= function_exists('t') ? t('Category') : 'Category' ?></div>
            <div class="value"><?= $h($category) ?></div>
        </div>
        <div class="option">
            <div class="title"><?= function_exists('t') ? t('Delivery') : 'Delivery' ?></div>
            <div class="value"><?= $h($deliveryLabel) ?></div>
        </div>
        <div class="option">
            <div class="title"><?= function_exists('t') ? t('Region') : 'Region' ?></div>
            <div class="value"><?= $h($region) ?></div>
        </div>
        <div class="option">
            <div class="title"><?= function_exists('t') ? t('Quantity') : 'Quantity' ?></div>
            <div class="value">x<?= (int)$quantity ?></div>
        </div>
    </div>

    <div class="checkout-dg-benefit">
        <i class="fa-solid fa-<?= $deliveryType === 'instant' ? 'bolt' : 'clock' ?>"></i>
        <span>
            <?php if ($deliveryType === 'instant'): ?>
                <?= function_exists('t') ? t('Your digital product will be delivered instantly after successful payment.') : 'Your digital product will be delivered instantly after successful payment.' ?>
            <?php else: ?>
                <?= function_exists('t') ? t('Your digital product will be delivered manually by the seller after successful payment.') : 'Your digital product will be delivered manually by the seller after successful payment.' ?>
            <?php endif; ?>
            <?php if ($sellerName !== ''): ?>
                <small><?= function_exists('t') ? t('Sold by') : 'Sold by' ?>: <?= $h($sellerName) ?></small>
            <?php endif; ?>
        </span>
    </div>
</div>
