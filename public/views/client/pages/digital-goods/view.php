<?php
/* Client: Digital Good Order View — /profile/digital-goods/:id
   Account-view styled version.
*/
$purchase   = $purchase ?? [];
$images     = $images   ?? [];
$id         = (int)($purchase['id'] ?? 0);
$statusRaw  = strtoupper(trim((string)($purchase['status'] ?? 'UNPAID')));
$itemTitle  = (string)($purchase['item_title'] ?? 'Digital Good');
$brand      = (string)($purchase['brand'] ?? '');
$catName    = (string)($purchase['category_name'] ?? '');
$qty        = max(1, (int)($purchase['quantity'] ?? 1));
$unitCents  = (int)($purchase['unit_price'] ?? 0);
$totalCents = (int)($purchase['price'] ?? 0);
$validity   = (int)($purchase['validity_days'] ?? 0);
$region     = (string)($purchase['region'] ?? '');
$deliveryType = (string)($purchase['delivery_type'] ?? $purchase['item_delivery_type'] ?? 'manual');
$delivNote  = (string)($purchase['delivery_note'] ?? '');
$custNote   = (string)($purchase['customer_note'] ?? '');
$sellerName = (string)($purchase['seller_username'] ?? 'Seller');
$sellerId   = (int)($purchase['seller_id'] ?? 0);
$sellerIcon = (string)($purchase['seller_icon'] ?? (defined('ICON_URL') ? ICON_URL . '/default.png' : ''));
$createdAt  = !empty($purchase['created_at']) ? date('d M Y H:i', strtotime((string)$purchase['created_at'])) : '—';
$paidAt     = !empty($purchase['paid_at']) ? date('d M Y H:i', strtotime((string)$purchase['paid_at'])) : null;
$delivAt    = !empty($purchase['delivered_at']) ? date('d M Y H:i', strtotime((string)$purchase['delivered_at'])) : null;
$canConfirm = $statusRaw === 'DELIVERED';
$alreadyReviewed = !empty($already_reviewed ?? false);
$canReview  = !empty($can_review ?? false) || ($statusRaw === 'COMPLETED' && !$alreadyReviewed);

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '';
$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;

    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#/public/assets/#', '/', $path);
    $path = '/' . ltrim((string)$path, '/');

    return $assetUrl . $path;
};

if (empty($images)) {
    foreach (['images', 'item_images'] as $_imgKey) {
        if (!empty($purchase[$_imgKey])) {
            $_decoded = is_array($purchase[$_imgKey]) ? $purchase[$_imgKey] : json_decode((string)$purchase[$_imgKey], true);
            if (is_array($_decoded)) {
                $images = array_values(array_filter(array_map('strval', $_decoded)));
                break;
            }
        }
    }
}
$images = array_values(array_filter(array_map($normalizeAssetPath, (array)$images)));

$brandIcon = $normalizeAssetPath($purchase['brand_icon'] ?? $purchase['item_brand_icon'] ?? '');
$categoryIcon = trim((string)($purchase['category_icon'] ?? 'fa-solid fa-layer-group'));
$thumb = $brandIcon !== '' ? $brandIcon : (!empty($images[0]) ? $images[0] : null);
$thumbIsBrandIcon = $brandIcon !== '' && $thumb === $brandIcon;

$bannerSeed = strtolower($brand !== '' ? $brand : ($catName !== '' ? $catName : $itemTitle));
$bannerPalettes = [
    'youtube'     => ['#1a0000','#7f0000','#b91c1c'],
    'netflix'     => ['#0a0000','#7f0000','#991b1b'],
    'spotify'     => ['#001a0d','#14532d','#15803d'],
    'discord'     => ['#0d0f1f','#3730a3','#5865f2'],
    'twitch'      => ['#1a0033','#6d28d9','#9146ff'],
    'steam'       => ['#0a0e14','#1e3a5f','#1b73b4'],
    'xbox'        => ['#001a00','#14532d','#16a34a'],
    'playstation' => ['#00001a','#1e3a8a','#2563eb'],
    'amazon'      => ['#1a0d00','#92400e','#d97706'],
    'apple'       => ['#0a0a0a','#1c1c1e','#3a3a3c'],
    'google'      => ['#0d1117','#1e3a5f','#4285f4'],
    'microsoft'   => ['#001228','#1e3a5f','#0078d4'],
    'default'     => ['#0f0e27','#1e1b4b','#312e81'],
];
$bp = $bannerPalettes['default'];
foreach ($bannerPalettes as $k => $v) {
    if ($k !== 'default' && str_contains($bannerSeed, $k)) { $bp = $v; break; }
}
$bannerGrad = "linear-gradient(135deg,{$bp[0]} 0%,{$bp[1]} 48%,{$bp[2]} 100%)";

$currencyCode = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$currencySymbol = function_exists('util_format_currency_display') ? util_format_currency_display($currencyCode) : ($currencyCode === 'USD' ? '$' : '€');
$rate = 1.0;
if ($currencyCode !== 'EUR' && function_exists('get_exchange_rate')) {
    $tmpRate = (float)get_exchange_rate();
    if ($tmpRate > 0) $rate = $tmpRate;
}
$money = function($cents) use ($currencySymbol, $rate): string {
    return $currencySymbol . number_format(((int)$cents / 100) * $rate, 2);
};
$totalFormatted = $money($totalCents);
$unitFormatted  = $money($unitCents);

$statusLabel = match($statusRaw) {
    'DELIVERED'  => 'Delivered — Please confirm',
    'COMPLETED'  => 'Completed',
    'PAID'       => 'Paid — Awaiting Delivery',
    'PROCESSING' => 'Processing',
    'UNPAID'     => 'Unpaid',
    'CANCELLED'  => 'Cancelled',
    'REFUNDED'   => 'Refunded',
    default      => $statusRaw,
};
$statusCls = match($statusRaw) {
    'DELIVERED','COMPLETED' => 'av-status--success',
    'UNPAID'                => 'av-status--warning',
    'CANCELLED','REFUNDED'  => 'av-status--danger',
    default                 => 'av-status--purple',
};
?>
<?= $this->layout('client/layouts/main', ['meta' => [
    'title'       => $itemTitle . ' | LoLBoost.gg',
    'h1'          => 'Digital Good Order',
    'description' => 'View your digital good order and chat with the seller.',
]]) ?>

<?= $this->start('styles') ?>
<style>
/* ── Base card overrides ── */
.client-dg-view .card {
  background:var(--bs-card-bg)!important;
  border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;
  border-radius:22px!important;
  box-shadow:none!important;
}
.client-dg-view .card::before { display:none!important; }
.client-dg-view .order-chat-card { overflow:hidden; }

/* ── Head card, same language as account view ── */
.av-head {
  border-radius:22px; overflow:hidden; margin-bottom:20px;
  border:1px solid var(--bs-card-border-color);
  background:#25282a;
}
.av-head-body {
  padding:20px 22px; display:flex; align-items:center; justify-content:space-between;
  flex-wrap:wrap; gap:12px; border-bottom:1px solid var(--bs-card-border-color);
}
.av-product-icon {
  width:52px; height:52px; border-radius:14px; flex-shrink:0;
  background:rgba(139,92,246,.12); border:1px solid rgba(139,92,246,.22);
  display:flex; align-items:center; justify-content:center; overflow:hidden;
}
.av-product-icon img { width:70%; height:70%; object-fit:contain; display:block; }
.av-product-icon--cover img { width:100%; height:100%; object-fit:cover; }
.av-product-icon--brand { background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.16); box-shadow:0 8px 24px rgba(0,0,0,.28); }
.av-product-icon i { color:#c4b5fd; font-size:1.25rem; }

/* Status pills */
.av-status {
  display:inline-flex; align-items:center; gap:.35rem; padding:4px 11px; border-radius:99px;
  font-size:.75rem; font-weight:800;
}
.av-status--success { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.22); color:#4ade80; }
.av-status--warning { background:rgba(251,191,36,.12); border:1px solid rgba(251,191,36,.22); color:#fbbf24; }
.av-status--danger  { background:rgba(251,113,133,.12); border:1px solid rgba(251,113,133,.22); color:#fb7185; }
.av-status--purple  { background:rgba(139,92,246,.14); border:1px solid rgba(139,92,246,.28); color:#c4b5fd; }

/* Meta pills row */
.av-meta-row { display:flex; flex-wrap:wrap; gap:6px; padding:14px 22px 16px; }
.av-meta-pill {
  display:inline-flex; align-items:center; gap:.3rem; padding:4px 11px; border-radius:99px;
  font-size:.75rem; font-weight:700; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.7);
}
.av-meta-pill strong { color:rgba(255,255,255,.92); }

/* Action buttons */
.av-btn-ghost {
  display:inline-flex; align-items:center; gap:.4rem; padding:7px 14px; border-radius:11px;
  font-size:.83rem; font-weight:700; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.7);
  cursor:pointer; transition:background .12s; text-decoration:none;
}
.av-btn-ghost:hover { background:rgba(255,255,255,.09); color:#fff; }
.av-btn-primary {
  display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:9px 20px;
  border-radius:11px; font-size:.88rem; font-weight:900;
  background:linear-gradient(135deg,#8b5cf6,#c026d3); border:none; color:#fff; cursor:pointer;
  transition:opacity .15s; text-decoration:none;
}
.av-btn-primary:hover { opacity:.88; color:#fff; }

/* ── Sidebar cards ── */
.av-sidebar-card {
  border-radius:18px; border:1px solid rgba(255,255,255,.07); background:#25282a;
  overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,.2);
}
.av-sc-header {
  display:flex; align-items:center; gap:8px; padding:12px 16px;
  border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02);
}
.av-sc-icon {
  width:26px; height:26px; border-radius:8px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  border:1px solid rgba(255,255,255,.1); font-size:.75rem;
}
.av-sc-title {
  font-size:.8rem; font-weight:900; color:rgba(255,255,255,.75);
  text-transform:uppercase; letter-spacing:.06em; flex:1;
}
.av-detail-list { padding:4px 0 6px; }
.av-detail-item {
  display:flex; align-items:flex-start; gap:8px; padding:8px 16px;
  border-bottom:1px solid rgba(255,255,255,.04);
}
.av-detail-item:last-child { border-bottom:0; }
.av-detail-lbl {
  font-size:.72rem; font-weight:700; color:rgba(255,255,255,.35); min-width:92px;
  flex-shrink:0; padding-top:1px;
}
.av-detail-val { font-size:.82rem; font-weight:700; color:rgba(255,255,255,.85); word-break:break-word; }

/* Product preview */
.av-product-preview { padding:14px 16px 16px; }
.av-product-preview__img {
  width:100%; aspect-ratio:16/9; border-radius:14px; overflow:hidden;
  background:#0d0f1a; border:1px solid rgba(255,255,255,.07); margin-bottom:12px;
}
.av-product-preview__img img { width:100%; height:100%; object-fit:cover; display:block; }
.av-product-preview__banner {
  width:100%; aspect-ratio:16/9; border-radius:14px; overflow:hidden;
  border:1px solid rgba(255,255,255,.09); margin-bottom:12px; position:relative;
  display:flex; align-items:center; justify-content:center;
}
.av-product-preview__banner::before,
.av-product-preview__banner::after {
  content:''; position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.08);
}
.av-product-preview__banner::before { width:220px; height:220px; right:-60px; top:-90px; }
.av-product-preview__banner::after { width:110px; height:110px; left:24px; bottom:-58px; }
.av-product-preview__brand-icon {
  position:relative; z-index:2; width:76px; height:76px; border-radius:22px;
  display:flex; align-items:center; justify-content:center; overflow:hidden;
  background:rgba(255,255,255,.12); border:2px solid rgba(255,255,255,.20);
  box-shadow:0 12px 34px rgba(0,0,0,.38);
}
.av-product-preview__brand-icon img { width:52px; height:52px; object-fit:contain; display:block; }
.av-product-preview__brand-icon i { color:rgba(255,255,255,.82); font-size:1.65rem; }
.av-product-preview__empty {
  width:100%; height:100%; display:flex; align-items:center; justify-content:center;
  color:rgba(255,255,255,.2); font-size:2rem;
}

/* Delivery note box */
.dg-delivery-box {
  margin:0 16px 14px; padding:14px 16px; border-radius:14px;
  background:rgba(74,222,128,.07); border:1px solid rgba(74,222,128,.2);
}
.dg-delivery-box__label {
  font-size:.7rem; font-weight:900; text-transform:uppercase; letter-spacing:.06em;
  color:#4ade80; margin-bottom:6px; display:flex; align-items:center; gap:6px;
}
.dg-delivery-box__content {
  font-size:.88rem; color:rgba(255,255,255,.88); white-space:pre-wrap; word-break:break-word; line-height:1.6;
}

/* Getting started tips */
.av-tip-item { display:flex; align-items:flex-start; gap:10px; padding:10px 16px; border-bottom:1px solid rgba(255,255,255,.04); }
.av-tip-item:last-child { border-bottom:0; }
.av-tip-ico {
  width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center;
  flex-shrink:0; font-size:.75rem;
}
.av-tip-title { font-size:.8rem; font-weight:800; color:rgba(255,255,255,.85); margin-bottom:1px; }
.av-tip-desc { font-size:.73rem; color:rgba(255,255,255,.38); line-height:1.4; }

/* Gallery */
.av-gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:10px; padding:16px 20px; }
.av-gallery-tile { position:relative; border-radius:12px; overflow:hidden; background:#0d0f1a; cursor:pointer; }
.av-gallery-tile img { width:100%; height:130px; object-fit:cover; display:block; transition:transform .3s; }
.av-gallery-tile:hover img { transform:scale(1.04); }
.av-gallery-main-badge {
  position:absolute; top:8px; left:8px; padding:2px 8px; border-radius:99px;
  background:rgba(139,92,246,.9); color:#fff; font-size:.68rem; font-weight:800;
}

/* Chat */
.av-chat-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 20px; border-bottom:1px solid var(--bs-card-border-color);
}
.av-chat-title { font-size:.95rem; font-weight:900; color:rgba(255,255,255,.9); display:flex; align-items:center; gap:.5rem; }
#dg_chat_messages {
  min-height:300px; max-height:480px; overflow-y:auto; padding:1rem 1.25rem;
  display:flex; flex-direction:column; scroll-behavior:smooth;
}
#dg_chat_messages::-webkit-scrollbar { width:5px; }
#dg_chat_messages::-webkit-scrollbar-track { background:transparent; }
#dg_chat_messages::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:3px; }

.lb-msg { display:flex; flex-direction:column; margin-bottom:.5rem; max-width:75%; }
.lb-msg--start { align-self:flex-start; }
.lb-msg--end { align-self:flex-end; }
.lb-msg__head { display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem; }
.lb-msg__head--end { flex-direction:row-reverse; }
.lb-msg__avatar { width:1.75rem; height:1.75rem; border-radius:50%; object-fit:cover; flex-shrink:0; }
.lb-msg__name { font-weight:700; font-size:.8rem; line-height:1.3; display:flex; align-items:center; gap:.3rem; }
.lb-msg__bubble {
  padding:.55rem .85rem; border-radius:.75rem; font-size:.875rem; line-height:1.55;
  word-break:break-word; background:rgba(255,255,255,.07); position:relative;
}
.lb-msg--end .lb-msg__bubble { background:rgba(139,92,246,.22); }
.lb-msg__stamp { font-size:.7rem; opacity:.4; margin-top:.2rem; }
.lb-msg--end .lb-msg__stamp { text-align:right; }
.lb-msg__content img { max-width:240px; max-height:200px; border-radius:.5rem; display:block; margin-top:.4rem; cursor:pointer; }
.lb-badge {
  display:inline-flex; align-items:center; padding:.1rem .4rem; border-radius:999px;
  font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
}
.lb-badge--seller { background:rgba(139,92,246,.2); color:#c4b5fd; }
.lb-badge--client { background:rgba(16,185,129,.15); color:#10b981; }
.lb-badge--admin { background:rgba(245,158,11,.15); color:#f59e0b; }
.lb-chat-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:240px; opacity:.4; gap:.5rem; text-align:center; }
.lb-system-note { display:flex; justify-content:center; margin:8px 0 12px; }
.lb-system-note__box {
  width:min(100%, 620px); display:flex; align-items:flex-start; gap:10px;
  padding:12px 14px; border-radius:14px;
  background:linear-gradient(180deg, rgba(34,197,94,.10), rgba(34,197,94,.06));
  border:1px solid rgba(74,222,128,.22);
  box-shadow:0 10px 24px rgba(0,0,0,.18);
}
.lb-system-note__icon {
  width:26px; height:26px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:rgba(74,222,128,.16); border:1px solid rgba(74,222,128,.28);
  color:#4ade80; font-size:.82rem; margin-top:1px;
}
.lb-system-note__body { min-width:0; flex:1; }
.lb-system-note__text { font-size:.86rem; line-height:1.55; color:rgba(255,255,255,.9); font-weight:700; }
.lb-system-note__stamp { margin-top:4px; font-size:.72rem; color:rgba(255,255,255,.38); }

/* Action button: danger */
.av-btn-danger {
  display:inline-flex; align-items:center; gap:.4rem; padding:7px 14px; border-radius:11px;
  font-size:.83rem; font-weight:700; background:rgba(239,68,68,.08);
  border:1px solid rgba(239,68,68,.2); color:#f87171;
  cursor:pointer; transition:background .12s; text-decoration:none;
}
.av-btn-danger:hover { background:rgba(239,68,68,.16); color:#fca5a5; }

/* ── Report Modal ── */
.rp-modal-overlay {
  position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,.65); backdrop-filter:blur(4px);
  display:flex; align-items:center; justify-content:center; padding:16px;
  opacity:0; pointer-events:none; transition:opacity .2s;
}
.rp-modal-overlay.is-open { opacity:1; pointer-events:all; }
.rp-modal {
  width:100%; max-width:480px;
  background:#1e2022; border:1px solid rgba(255,255,255,.1);
  border-radius:20px; overflow:hidden;
  transform:translateY(16px) scale(.97); transition:transform .2s;
  box-shadow:0 24px 60px rgba(0,0,0,.5);
}
.rp-modal-overlay.is-open .rp-modal { transform:translateY(0) scale(1); }
.rp-modal-header {
  display:flex; align-items:center; gap:10px;
  padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.07);
  background:rgba(255,255,255,.02);
}
.rp-modal-icon {
  width:32px; height:32px; border-radius:10px; flex-shrink:0;
  background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.22);
  display:flex; align-items:center; justify-content:center; font-size:.8rem; color:#f87171;
}
.rp-modal-title { font-size:.95rem; font-weight:900; color:rgba(255,255,255,.9); flex:1; }
.rp-modal-close {
  background:none; border:none; color:rgba(255,255,255,.3); cursor:pointer;
  padding:4px; border-radius:6px; transition:color .12s, background .12s; line-height:1;
}
.rp-modal-close:hover { color:#fff; background:rgba(255,255,255,.06); }
.rp-modal-body { padding:20px; }
.rp-label { font-size:.72rem; font-weight:800; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px; }
.rp-problems { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.rp-problem-opt {
  display:flex; align-items:center; gap:10px; padding:10px 12px;
  border-radius:11px; border:1px solid rgba(255,255,255,.07);
  background:rgba(255,255,255,.02); cursor:pointer; transition:all .12s;
}
.rp-problem-opt input[type="radio"] { display:none; }
.rp-problem-opt:hover { background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.12); }
.rp-problem-opt.is-selected { background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.25); }
.rp-problem-ico { font-size:.8rem; color:rgba(255,255,255,.35); width:16px; text-align:center; flex-shrink:0; }
.rp-problem-opt.is-selected .rp-problem-ico { color:#f87171; }
.rp-problem-text { flex:1; font-size:.82rem; font-weight:700; color:rgba(255,255,255,.75); }
.rp-problem-opt.is-selected .rp-problem-text { color:#fff; }
.rp-problem-check { color:rgba(239,68,68,.5); font-size:.75rem; opacity:0; transition:opacity .12s; }
.rp-problem-opt.is-selected .rp-problem-check { opacity:1; }
.rp-details-wrap { margin-top:4px; }
.rp-details {
  width:100%; padding:10px 12px; border-radius:11px; resize:none; outline:none;
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09);
  color:rgba(255,255,255,.82); font-size:.82rem; line-height:1.5;
}
.rp-details:focus { border-color:rgba(255,255,255,.18); }
.rp-modal-footer {
  display:flex; align-items:center; justify-content:flex-end; gap:8px;
  padding:14px 20px; border-top:1px solid rgba(255,255,255,.06);
  background:rgba(255,255,255,.01);
}
.rp-submit {
  display:inline-flex; align-items:center; gap:.4rem; padding:8px 18px; border-radius:11px;
  font-size:.82rem; font-weight:800; background:rgba(239,68,68,.15);
  border:1px solid rgba(239,68,68,.3); color:#f87171; cursor:pointer; transition:all .12s;
}
.rp-submit:not(:disabled):hover { background:rgba(239,68,68,.25); color:#fca5a5; }
.rp-submit:disabled { opacity:.45; cursor:not-allowed; }
.rp-cancel {
  display:inline-flex; align-items:center; gap:.4rem; padding:8px 16px; border-radius:11px;
  font-size:.82rem; font-weight:800; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.55); cursor:pointer; transition:all .12s;
}
.rp-cancel:hover { background:rgba(255,255,255,.08); }
.rp-success { text-align:center; padding:28px 20px; }
.rp-success-ico { font-size:2rem; margin-bottom:10px; }
.rp-success-title { font-size:1rem; font-weight:900; color:rgba(255,255,255,.9); margin-bottom:4px; }
.rp-success-sub { font-size:.8rem; color:rgba(255,255,255,.4); }

/* ── Confirm Delivery Modal ── */
.cd-modal { max-width:440px; }
.cd-modal-icon { background:rgba(74,222,128,.12); border-color:rgba(74,222,128,.24); color:#4ade80; }
.cd-modal-body { padding:22px 20px 18px; text-align:center; }
.cd-hero {
  width:64px; height:64px; margin:0 auto 14px; border-radius:22px;
  display:flex; align-items:center; justify-content:center; font-size:1.6rem; color:#4ade80;
  background:rgba(74,222,128,.10); border:1px solid rgba(74,222,128,.22);
  box-shadow:0 12px 30px rgba(74,222,128,.14);
}
.cd-title { font-size:1.02rem; font-weight:950; color:rgba(255,255,255,.92); margin-bottom:6px; }
.cd-sub { font-size:.84rem; line-height:1.55; color:rgba(255,255,255,.45); }
.cd-notes { display:flex; flex-direction:column; gap:8px; margin-top:16px; text-align:left; }
.cd-note {
  display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:12px;
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06);
  font-size:.78rem; line-height:1.45; color:rgba(255,255,255,.62);
}
.cd-note i { font-size:.78rem; margin-top:2px; flex-shrink:0; color:rgba(255,255,255,.35); }
.cd-note--warn { background:rgba(251,191,36,.06); border-color:rgba(251,191,36,.16); color:rgba(253,224,171,.75); }
.cd-note--warn i { color:#fbbf24; }
.cd-confirm {
  display:inline-flex; align-items:center; gap:.45rem; padding:8px 18px; border-radius:11px;
  font-size:.82rem; font-weight:800; background:rgba(74,222,128,.15);
  border:1px solid rgba(74,222,128,.3); color:#4ade80; cursor:pointer; transition:all .12s;
}
.cd-confirm:not(:disabled):hover { background:rgba(74,222,128,.25); color:#86efac; }
.cd-confirm:disabled { opacity:.5; cursor:not-allowed; }

/* Review form */
.dg-review-stars { display:flex; gap:6px; margin:8px 0 12px; }
.dg-review-stars i { font-size:1.4rem; color:rgba(255,255,255,.2); cursor:pointer; transition:color .12s; }
.dg-review-stars i.active, .dg-review-stars i:hover { color:#fbbf24; }

@media (max-width:768px) {
  .av-head-body { padding:14px 16px; }
  .av-meta-row { padding:10px 16px 12px; }
  #dg_chat_messages { min-height:220px; max-height:340px; }
  .lb-msg { max-width:88%; }
  .av-gallery-grid { grid-template-columns:repeat(2,1fr); }
}
</style>
<?= $this->end() ?>

<div class="client-dg-view">

  <!-- HEAD CARD -->
  <div class="av-head mb-4">
    <div class="av-head-body">
      <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
        <div class="av-product-icon <?= $thumbIsBrandIcon ? 'av-product-icon--brand' : 'av-product-icon--cover' ?>" style="<?= $thumbIsBrandIcon ? 'background:' . $h($bannerGrad) . ';' : '' ?>">
          <?php if ($thumb): ?><img src="<?= $h($thumb) ?>" alt=""><?php else: ?><i class="<?= $h($categoryIcon) ?>"></i><?php endif; ?>
        </div>
        <div style="min-width:0;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <h1 style="font-size:1.25rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;"><?= $h($itemTitle) ?></h1>
            <span class="av-status <?= $statusCls ?>">
              <i class="fa-solid <?= in_array($statusRaw, ['DELIVERED','COMPLETED'], true) ? 'fa-circle-check' : ($statusRaw === 'UNPAID' ? 'fa-clock' : 'fa-bag-shopping') ?>" style="font-size:.6rem;"></i>
              <?= $h($statusLabel) ?>
            </span>
          </div>
          <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <span>#<?= $id ?></span>
            <?php if ($catName): ?><span>·</span><span><?= $h($catName) ?></span><?php endif; ?>
            <?php if ($brand): ?><span>·</span><span><?= $h($brand) ?></span><?php endif; ?>
            <span>·</span><span><?= $h($createdAt) ?></span>
            <?php if ($sellerName): ?><span>·</span><span style="font-weight:700;"><i class="fa-solid fa-store me-1"></i><?= $h($sellerName) ?></span><?php endif; ?>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <button type="button" class="av-btn-danger" id="reportProblemBtn">
          <i class="fa-solid fa-flag"></i> Report a Problem
        </button>
        <?php if (!empty($purchase['seller_id'])): ?>
        <button type="button" class="av-btn-primary js-client-poke-seller" data-id="<?= (int)$id ?>">
          <i class="fa-solid fa-hand-point-up"></i> Poke Seller
        </button>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/digital-goods" class="av-btn-ghost"><i class="fa-solid fa-store"></i> Browse</a>
        <a href="<?= BASE_URL ?>/profile/digital-goods" class="av-btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="av-meta-row">
      <span class="av-meta-pill"><i class="fa-solid fa-euro-sign"></i> <strong><?= $h($totalFormatted) ?></strong></span>
      <span class="av-meta-pill"><i class="fa-solid fa-basket-shopping"></i> Qty: <strong><?= $qty ?></strong></span>
      <span class="av-meta-pill"><i class="fa-solid fa-truck-fast"></i> <strong><?= $h(ucfirst(str_replace('_',' ', $deliveryType ?: 'manual'))) ?></strong></span>
      <?php if ($region): ?><span class="av-meta-pill"><i class="fa-solid fa-globe"></i> <?= $h($region) ?></span><?php endif; ?>
      <?php if ($validity > 0): ?><span class="av-meta-pill"><i class="fa-solid fa-calendar-days"></i> <?= $validity ?> days</span><?php endif; ?>
      <?php if ($paidAt): ?><span class="av-meta-pill"><i class="fa-solid fa-check"></i> Paid <?= $h($paidAt) ?></span><?php endif; ?>
      <?php if ($delivAt): ?><span class="av-meta-pill"><i class="fa-solid fa-box-open"></i> Delivered <?= $h($delivAt) ?></span><?php endif; ?>
    </div>
  </div>

  <div class="row g-4 align-items-start">

    <!-- LEFT: chat + delivery + gallery -->
    <div class="col-12 col-lg-7">

      <?php if ($delivNote && in_array($statusRaw, ['DELIVERED','COMPLETED'], true)): ?>
      <div class="av-sidebar-card mb-4">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(74,222,128,.15);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-box-open" style="color:#4ade80;"></i></span>
          <span class="av-sc-title">Your Delivery</span>
        </div>
        <div class="dg-delivery-box">
          <div class="dg-delivery-box__label"><i class="fa-solid fa-circle-check"></i> Delivered by seller</div>
          <div class="dg-delivery-box__content"><?= $h($delivNote) ?></div>
        </div>
        <?php if ($canConfirm): ?>
        <div style="padding:0 16px 16px;">
          <button type="button" class="av-btn-primary w-100" id="dgConfirmBtn">
            <i class="fa-solid fa-circle-check"></i> Confirm Delivery
          </button>
        </div>
        <?php endif; ?>
      </div>
      <?php elseif ($canConfirm): ?>
      <div class="av-sidebar-card mb-4">
        <div style="padding:18px 16px;text-align:center;">
          <i class="fa-solid fa-truck-fast" style="font-size:2rem;color:#8b5cf6;opacity:.75;margin-bottom:10px;display:block;"></i>
          <div style="font-size:.95rem;font-weight:900;color:rgba(255,255,255,.86);margin-bottom:6px;">Your order has been delivered</div>
          <div style="font-size:.8rem;color:rgba(255,255,255,.38);margin-bottom:14px;">Please confirm once everything is correct.</div>
          <button type="button" class="av-btn-primary w-100" id="dgConfirmBtn">
            <i class="fa-solid fa-circle-check"></i> Confirm Delivery
          </button>
        </div>
      </div>
      <?php endif; ?>

      <!-- Chat -->
      <div class="card order-chat-card mb-4">
        <div class="av-chat-header">
          <div class="av-chat-title">
            <i class="fa-solid fa-comments" style="color:#c4b5fd;"></i>
            Seller Support Chat
          </div>
          <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(139,92,246,.10);border:1px solid rgba(139,92,246,.2);color:#c4b5fd;font-size:.75rem;font-weight:700;">
            <?php if ($sellerIcon): ?>
              <img src="<?= $h($sellerIcon) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover;" alt="">
            <?php endif; ?>
            <?= $h($sellerName ?: 'Seller') ?>
          </div>
        </div>

        <div class="card-body chat-bg" id="dg_chat_messages"></div>

        <div class="card-footer">
          <form id="dgChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:8px;">
            <input type="hidden" name="action" value="client_dg_chat_send">
            <input type="hidden" name="purchase_id" value="<?= $id ?>">
            <div style="display:flex;gap:8px;align-items:center;">
              <input type="text" name="message" id="dgChatInput" class="form-control" placeholder="Type your message..." style="flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:12px;padding:10px 13px;">
              <input type="file" class="d-none" id="dgChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">
              <button type="button" class="av-btn-ghost" id="dgChatAttachBtn" title="Attach image" style="padding:9px 12px;"><i class="fa-solid fa-paperclip"></i></button>
              <button type="submit" class="av-btn-primary" id="dgSendBtn" style="min-height:42px;white-space:nowrap;">
                <span class="indicator-label"><i class="fa-solid fa-paper-plane"></i></span>
                <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
              </button>
            </div>
            <div id="dgChatError" class="d-none" style="color:#fb7185;font-size:.82rem;"></div>
            <div id="dgChatPreview" style="display:none;align-items:center;gap:10px;padding:8px 12px;background:rgba(255,255,255,.05);border-radius:10px;border:1px solid rgba(255,255,255,.1);">
              <img id="dgChatPreviewImg" src="" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
              <div style="flex:1;min-width:0;">
                <div style="font-weight:800;font-size:.82rem;color:rgba(255,255,255,.85);">Image ready to send</div>
                <div id="dgChatPreviewName" style="font-size:.78rem;opacity:.7;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></div>
              </div>
              <button type="button" id="dgChatRemoveFile" style="background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;padding:4px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="text-muted small">Messages stay protected inside the platform. You can also paste a screenshot with <strong>Ctrl+V</strong>.</div>
          </form>
        </div>
      </div>

      <!-- Gallery -->
      <?php if (!empty($images)): ?>
      <div class="card mb-4">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color);">
          <h4 class="card-header-title mb-0" style="font-weight:700;font-size:1rem;">
            <i class="fa-solid fa-images me-2" style="color:#c4b5fd;"></i>Gallery
            <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?></span>
          </h4>
        </div>
        <div class="av-gallery-grid">
          <?php foreach ($images as $i => $img): ?>
          <div class="av-gallery-tile" data-zoom="<?= $h($img) ?>">
            <?php if ($i === 0): ?><div class="av-gallery-main-badge">MAIN</div><?php endif; ?>
            <img src="<?= $h($img) ?>" alt="" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($custNote): ?>
      <div class="card mb-4">
        <div class="card-header" style="padding:14px 20px;">
          <h4 class="card-header-title mb-0" style="font-weight:700;font-size:1rem;"><i class="fa-solid fa-note-sticky me-2"></i>Your Note</h4>
        </div>
        <div class="card-body" style="padding:16px 20px;">
          <p class="mb-0" style="font-size:.875rem;line-height:1.7;color:rgba(255,255,255,.7);"><?= nl2br($h($custNote)) ?></p>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- RIGHT: next steps + seller + order details -->
    <div class="col-12 col-lg-5">

      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.2);"><i class="fa-solid fa-lightbulb" style="color:#fbbf24;"></i></span>
          <span class="av-sc-title">Next Steps</span>
        </div>
        <div style="padding:4px 0 8px;">
          <div class="av-tip-item">
            <div class="av-tip-ico" style="background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.22);color:#c4b5fd;"><i class="fa-solid fa-comments"></i></div>
            <div><div class="av-tip-title">Use the seller chat</div><div class="av-tip-desc">Ask questions or coordinate manual delivery here.</div></div>
          </div>
          <div class="av-tip-item">
            <div class="av-tip-ico" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="av-tip-title">Confirm delivery</div><div class="av-tip-desc">When delivered, confirm only after checking everything. If you do not confirm within 24 hours, the order is completed automatically.</div></div>
          </div>
          <div class="av-tip-item">
            <div class="av-tip-ico" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24;"><i class="fa-solid fa-shield-halved"></i></div>
            <div><div class="av-tip-title">Stay protected</div><div class="av-tip-desc">Keep all communication and delivery inside the platform.</div></div>
          </div>
        </div>
      </div>

      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);"><i class="fa-solid fa-store"></i></span>
          <span class="av-sc-title">Seller</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;">
          <?php if ($sellerIcon): ?>
            <img src="<?= $h($sellerIcon) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.08);" alt="">
          <?php else: ?>
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(139,92,246,.15);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-weight:900;"><?= strtoupper(substr($sellerName ?: 'S', 0, 1)) ?></div>
          <?php endif; ?>
          <div style="min-width:0;flex:1;">
            <div style="font-size:.88rem;font-weight:900;color:rgba(255,255,255,.88);"><?= $h($sellerName) ?></div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px;">Your digital goods seller</div>
          </div>
          <a href="#dg_chat_messages" onclick="document.getElementById('dg_chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'});return false;" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;">
            <i class="fa-solid fa-comments"></i> Chat
          </a>
        </div>
      </div>

      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(139,92,246,.15);color:#c4b5fd;"><i class="fa-solid fa-receipt"></i></span>
          <span class="av-sc-title">Order Details</span>
        </div>
        <div class="av-detail-list">
          <div class="av-detail-item"><div class="av-detail-lbl">Order</div><div class="av-detail-val">#<?= $id ?></div></div>
          <div class="av-detail-item"><div class="av-detail-lbl">Quantity</div><div class="av-detail-val"><?= $qty ?></div></div>
          <div class="av-detail-item"><div class="av-detail-lbl">Unit Price</div><div class="av-detail-val"><?= $h($unitFormatted) ?></div></div>
          <div class="av-detail-item"><div class="av-detail-lbl">Total</div><div class="av-detail-val" style="color:#4ade80;font-size:.9rem;"><?= $h($totalFormatted) ?></div></div>
          <div class="av-detail-item"><div class="av-detail-lbl">Status</div><div class="av-detail-val"><span class="av-status <?= $statusCls ?>" style="font-size:.7rem;padding:3px 9px;"><?= $h($statusLabel) ?></span></div></div>
          <div class="av-detail-item"><div class="av-detail-lbl">Delivery</div><div class="av-detail-val"><?= $h(ucfirst(str_replace('_',' ', $deliveryType ?: 'manual'))) ?></div></div>
          <?php if ($region): ?><div class="av-detail-item"><div class="av-detail-lbl">Region</div><div class="av-detail-val"><?= $h($region) ?></div></div><?php endif; ?>
          <?php if ($validity > 0): ?><div class="av-detail-item"><div class="av-detail-lbl">Validity</div><div class="av-detail-val"><?= $validity ?> days</div></div><?php endif; ?>
          <div class="av-detail-item"><div class="av-detail-lbl">Created</div><div class="av-detail-val"><?= $h($createdAt) ?></div></div>
          <?php if ($paidAt): ?><div class="av-detail-item"><div class="av-detail-lbl">Paid</div><div class="av-detail-val"><?= $h($paidAt) ?></div></div><?php endif; ?>
          <?php if ($delivAt): ?><div class="av-detail-item"><div class="av-detail-lbl">Delivered</div><div class="av-detail-val"><?= $h($delivAt) ?></div></div><?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Report a Problem Modal -->
<div class="rp-modal-overlay" id="rpOverlay" role="dialog" aria-modal="true" aria-label="Report a Problem">
  <div class="rp-modal" id="rpModal">
    <div class="rp-modal-header">
      <div class="rp-modal-icon"><i class="fa-solid fa-flag"></i></div>
      <div class="rp-modal-title">Report a Problem</div>
      <button class="rp-modal-close" id="rpClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="rpFormWrap">
      <div class="rp-modal-body">
        <div class="rp-label">What's the issue?</div>
        <div class="rp-problems" id="rpProblems">
          <?php
          $problems = [
            ['id' => 'not_delivered',   'icon' => 'fa-solid fa-box-open',      'text' => 'Item was never delivered'],
            ['id' => 'wrong_item',      'icon' => 'fa-solid fa-triangle-exclamation', 'text' => 'Wrong item or region delivered'],
            ['id' => 'code_invalid',    'icon' => 'fa-solid fa-key',           'text' => 'Code / key is invalid or already used'],
            ['id' => 'seller_no_resp',  'icon' => 'fa-solid fa-comment-slash', 'text' => 'Seller is not responding'],
            ['id' => 'seller_rude',     'icon' => 'fa-solid fa-face-angry',    'text' => 'Seller behaviour / harassment'],
            ['id' => 'desc_mismatch',   'icon' => 'fa-solid fa-file-circle-xmark', 'text' => 'Description doesn\'t match the product'],
            ['id' => 'payment_issue',   'icon' => 'fa-solid fa-credit-card',   'text' => 'Payment / billing issue'],
            ['id' => 'other',           'icon' => 'fa-solid fa-ellipsis',      'text' => 'Other issue'],
          ];
          foreach ($problems as $p): ?>
          <label class="rp-problem-opt" data-id="<?= $p['id'] ?>">
            <input type="radio" name="rp_issue" value="<?= $p['id'] ?>">
            <i class="<?= $p['icon'] ?> rp-problem-ico"></i>
            <span class="rp-problem-text"><?= $p['text'] ?></span>
            <span class="rp-problem-check"><i class="fa-solid fa-check"></i></span>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="rp-details-wrap">
          <div class="rp-label">Additional details <span style="opacity:.5;font-weight:600;text-transform:none;letter-spacing:0;">(optional)</span></div>
          <textarea class="rp-details" id="rpDetails" rows="3" placeholder="Describe the problem in more detail…" maxlength="1000"></textarea>
        </div>
      </div>
      <div class="rp-modal-footer">
        <button class="rp-cancel" id="rpCancelBtn">Cancel</button>
        <button class="rp-submit" id="rpSubmitBtn" disabled>
          <i class="fa-solid fa-paper-plane"></i> Send Report
        </button>
      </div>
    </div>
    <div id="rpSuccessWrap" style="display:none;">
      <div class="rp-success">
        <div class="rp-success-ico">✅</div>
        <div class="rp-success-title">Report sent!</div>
        <div class="rp-success-sub">Our team has been notified and will look into this shortly.</div>
        <button class="rp-cancel" style="margin-top:16px;" id="rpSuccessClose">Close</button>
      </div>
    </div>
  </div>
</div>

<?php if ($canConfirm): ?>
<!-- Confirm Delivery Modal -->
<div class="rp-modal-overlay" id="cdOverlay" role="dialog" aria-modal="true" aria-label="Confirm Delivery">
  <div class="rp-modal cd-modal">
    <div class="rp-modal-header">
      <div class="rp-modal-icon cd-modal-icon"><i class="fa-solid fa-circle-check"></i></div>
      <div class="rp-modal-title">Confirm Delivery</div>
      <button class="rp-modal-close" id="cdClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="cd-modal-body">
      <div class="cd-hero"><i class="fa-solid fa-box-open"></i></div>
      <div class="cd-title">Did you receive everything?</div>
      <div class="cd-sub">Confirm only after you have checked your delivery and everything works as described.</div>
      <div class="cd-notes">
        <div class="cd-note"><i class="fa-solid fa-lock"></i><span>Confirming releases the order to the seller and marks it as completed.</span></div>
        <div class="cd-note cd-note--warn"><i class="fa-solid fa-triangle-exclamation"></i><span>This cannot be undone. If something is wrong, use <strong>Report a Problem</strong> instead.</span></div>
      </div>
    </div>
    <div class="rp-modal-footer">
      <button class="rp-cancel" id="cdCancelBtn">Cancel</button>
      <button class="cd-confirm" id="cdConfirmBtn">
        <i class="fa-solid fa-circle-check"></i> Yes, confirm
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Image lightbox -->
<div class="modal fade" id="dgImgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:90vw;">
    <div class="modal-content" style="background:rgba(0,0,0,.85);border:none;">
      <div class="modal-body text-center p-2">
        <img src="" id="dgImgModalImg" alt="" style="max-width:100%;max-height:80vh;border-radius:.5rem;">
      </div>
      <div class="modal-footer justify-content-center py-2 border-0">
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php if ($canReview && !$alreadyReviewed && $sellerId): ?>
<style>
.lb-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.10);border-radius:18px;}
.lb-modal .modal-header{padding:1.1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.07);}
.lb-modal .modal-footer{padding:.9rem 1.25rem;border-top:1px solid rgba(255,255,255,.07);}
.lb-modal .lb-modal-head{display:flex;align-items:flex-start;gap:.85rem;min-width:0;}
.lb-modal .lb-modal-ico{width:46px;height:46px;border-radius:16px;display:grid;place-items:center;background:rgba(139,92,246,.14);border:1px solid rgba(139,92,246,.26);color:#ddd6fe;flex:0 0 auto;}
.lb-modal .lb-modal-headtxt{min-width:0;}
.lb-modal .lb-modal-title{margin:0;font-weight:950;font-size:1.05rem;line-height:1.2;color:rgba(255,255,255,.92);}
.lb-modal .lb-modal-sub{margin:.25rem 0 0;opacity:.72;font-size:.9rem;line-height:1.35;color:rgba(255,255,255,.72);}
.lb-modal .lb-modal-x{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.85);transition:.15s ease;flex:0 0 auto;}
.lb-modal .lb-modal-x:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.16);color:#fff;transform:translateY(-1px);}
.lb-star-sr{width:42px;height:42px;border-radius:14px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.03);display:grid;place-items:center;transition:.15s ease;padding:0;cursor:pointer;}
.lb-star-sr:hover{transform:translateY(-1px);background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.16);}
.lb-star-sr svg{width:26px;height:26px;}
.lb-star-sr svg path{fill:transparent;stroke:rgba(251,191,36,.75);stroke-width:2;transition:fill .12s,stroke .12s,filter .12s;}
.lb-star-sr.is-on svg path{fill:#fbbf24;stroke:#fbbf24;filter:drop-shadow(0 8px 18px rgba(251,191,36,.24));}
/* Trustpilot stars use the Trustpilot brand green instead of the LB gold */
#sr_tp_stars .lb-star-sr{border-color:rgba(0,182,122,.48);background:rgba(0,182,122,.12);}
#sr_tp_stars .lb-star-sr:hover{border-color:rgba(0,182,122,.72);background:rgba(0,182,122,.20);}
#sr_tp_stars .lb-star-sr svg path{fill:rgba(0,182,122,.16);stroke:#00b67a;}
#sr_tp_stars .lb-star-sr.is-on svg path{fill:#00b67a;stroke:#00b67a;filter:drop-shadow(0 8px 18px rgba(0,182,122,.28));}
.sr-review-card{border-radius:16px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);}
.sr-suggestion-pill{border:1px solid rgba(139,92,246,.22);background:rgba(139,92,246,.10);color:#ddd6fe;border-radius:999px;padding:.45rem .8rem;font-size:.78rem;font-weight:700;line-height:1;transition:.15s ease;cursor:pointer;}
.sr-suggestion-pill:hover{background:rgba(139,92,246,.18);border-color:rgba(139,92,246,.34);transform:translateY(-1px);}
.sr-suggestion-pill.is-active{background:rgba(139,92,246,.24);border-color:rgba(139,92,246,.42);color:#fff;box-shadow:0 8px 18px rgba(139,92,246,.18);}
</style>

<div id="sr_completed_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-party-horn"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Purchase complete 🎉</h5>
            <p class="lb-modal-sub">How was your experience with <?= $h($sellerName) ?>?</p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($sellerIcon): ?>
                  <span class="avatar" style="width:44px;height:44px;"><img class="avatar-img" src="<?= $h($sellerIcon) ?>" alt=""></span>
                <?php else: ?>
                  <span class="avatar" style="width:44px;height:44px;background:rgba(139,92,246,.2);border-radius:50%;display:grid;place-items:center;font-weight:900;color:#ddd6fe;font-size:1.1rem;"><?= strtoupper(substr($sellerName ?: 'S', 0, 1)) ?></span>
                <?php endif; ?>
                <div>
                  <div class="fw-bold">Rate <?= $h($sellerName) ?></div>
                  <div class="text-muted small">Helps other buyers find great sellers.</div>
                </div>
              </div>
              <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#sr_leave_review_md">
                <i class="fa-duotone fa-star me-2"></i> Leave a Review
              </button>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="fw-bold mb-1">Review us on Trustpilot</div>
              <div class="text-muted small mb-3">Tap a star to open Trustpilot in a new tab.</div>
              <div id="sr_tp_stars" class="d-flex gap-2 mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" class="lb-star-sr" data-index="<?= $i ?>">
                    <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
                  </button>
                <?php endfor; ?>
              </div>
              <a class="btn btn-white border" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank" rel="noopener">
                <i class="fa-duotone fa-arrow-up-right-from-square me-2"></i> Open Trustpilot
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" id="sr_dismiss_btn" class="btn btn-link text-muted p-0" data-bs-dismiss="modal">I don't want to review now</button>
        <div class="small text-muted">You can review anytime from this page.</div>
      </div>
    </div>
  </div>
</div>

<div id="sr_leave_review_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-star"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Leave a Review</h5>
            <p class="lb-modal-sub"><?= $h($sellerName) ?></p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="sr-review-card p-3 mb-3">
          <div class="fw-bold mb-1">How would you rate <?= $h($sellerName) ?>?</div>
          <div class="text-muted small mb-3">1 = poor, 5 = excellent</div>
          <div id="sr_review_stars" class="d-flex gap-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" class="lb-star-sr" data-index="<?= $i ?>">
                <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
              </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" id="sr_rating_val" value="0">
        </div>
        <div class="sr-review-card p-3">
          <div class="fw-bold mb-2">Comment <span class="text-muted fw-normal" style="font-size:.82rem;">(Optional)</span></div>
          <textarea id="sr_comment_val" class="form-control" rows="4" placeholder="Share your experience..." style="resize:none;"></textarea>
          <div class="text-muted small mt-2 mb-2">Quick suggestions, tap one if you do not want to type everything manually.</div>
          <div id="sr_comment_suggestions" class="d-flex flex-wrap gap-2">
            <button type="button" class="sr-suggestion-pill" data-text="Fast delivery, great communication, and everything was exactly as described.">Fast delivery</button>
            <button type="button" class="sr-suggestion-pill" data-text="Very friendly seller, smooth transaction, and I would definitely buy again.">Friendly seller</button>
            <button type="button" class="sr-suggestion-pill" data-text="The digital good was exactly as described and the whole process was smooth and easy.">As described</button>
            <button type="button" class="sr-suggestion-pill" data-text="Good experience overall, quick support, and the purchase went without any issues.">Good overall</button>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <p id="sr_review_err" class="text-danger small mb-0"></p>
        <button type="button" id="sr_review_submit" class="btn btn-primary">
          Submit Review <i class="fa-duotone fa-paper-plane ms-2"></i>
          <span id="sr_review_spin" class="spinner-border spinner-border-sm d-none ms-1" role="status"></span>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->start('scripts') ?>
<script>
(function(){
  var AJAX_URL     = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= AJAX_URL ?>';
  var PURCHASE_ID  = <?= $id ?>;
  var CLIENT_ICON  = <?= json_encode((string)(CLIENT_DATA['icon'] ?? (defined('ICON_URL') ? ICON_URL.'/default.png' : ''))) ?>;
  var CLIENT_NAME  = <?= json_encode((string)(CLIENT_DATA['username'] ?? 'Me')) ?>;
  var SELLER_ICON  = <?= json_encode($sellerIcon) ?>;
  var SELLER_NAME  = <?= json_encode($sellerName) ?>;

  var msgNone = false, chatSig = '', isLoading = false;

  function escHtml(s){
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function toast(type, title, msg){ if (typeof create_toast === 'function') create_toast(type, title, msg); else alert(msg || title); }

  function formatTime(ts) {
    if (!ts) return '';
    var d = new Date(ts * 1000);
    var p = function(n){ return String(n).padStart(2,'0'); };
    return p(d.getDate())+'.'+p(d.getMonth()+1)+'.'+d.getFullYear()+' '+p(d.getHours())+':'+p(d.getMinutes());
  }

  function decodeHtml(str) {
    var t = document.createElement('textarea');
    t.innerHTML = str || '';
    return t.value.replace(/\n/g,'<br>');
  }

  function buildSig(list) {
    try {
      return Object.keys(list||{}).map(function(k){
        var v = list[k]||{};
        return [k,v.sender||'',v.sender_id||'',v.time||'',v.deleted||'',v.content||''].join('~');
      }).join('||');
    } catch(e){ return String(Date.now()); }
  }

  function renderMsg(id, m, grouped) {
    var sender  = m.sender || m.type || 'seller';
    var isMe    = (sender === 'client');
    var content = decodeHtml(m.content || m.message || '');
    var time    = formatTime(m.time);

    if (sender === 'system') {
      var systemText = content || '✅ Payment confirmed! The seller has been notified and will deliver shortly.';
      return ''
        + '<div class="lb-system-note">'
        + '  <div class="lb-system-note__box">'
        + '    <div class="lb-system-note__icon"><i class="fa-solid fa-check"></i></div>'
        + '    <div class="lb-system-note__body">'
        + '      <div class="lb-system-note__text">' + systemText + '</div>'
        + (time ? '<div class="lb-system-note__stamp">' + escHtml(time) + '</div>' : '')
        + '    </div>'
        + '  </div>'
        + '</div>';
    }

    var avatar  = isMe ? CLIENT_ICON : SELLER_ICON;
    var name    = isMe ? CLIENT_NAME : (m.sender_name || SELLER_NAME);
    var badgeCls = isMe ? 'lb-badge--client' : (sender === 'admin' ? 'lb-badge--admin' : 'lb-badge--seller');
    var badgeLbl = isMe ? 'Me' : (sender === 'admin' ? 'Support' : escHtml(name));
    var alignCls = isMe ? 'lb-msg--end' : 'lb-msg--start';
    var headCls  = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';

    var html = '<div class="lb-msg '+alignCls+'">';
    if (!grouped) {
      html += '<div class="'+headCls+'">'
            + '<img class="lb-msg__avatar" src="'+escHtml(avatar)+'" alt="">'
            + '<span class="lb-msg__name"><span class="lb-badge '+badgeCls+'">'+badgeLbl+'</span></span>'
            + '</div>';
    }
    html += '<div class="lb-msg__bubble lb-msg__content">'+content+'</div>';
    html += '<div class="lb-msg__stamp">'+escHtml(time)+'</div>';
    html += '</div>';
    return html;
  }

  /* ── Poke seller (email + Discord DM, same flow as account/item orders) ── */
  document.querySelectorAll('.js-client-poke-seller').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      var oldHtml = btn.innerHTML;
      var cooldownStarted = false;
      function startCooldown(seconds) {
        var remaining = Math.max(1, parseInt(seconds, 10) || 300);
        cooldownStarted = true;
        btn.disabled = true;
        function render() {
          var mins = Math.floor(remaining / 60);
          var secs = String(remaining % 60).padStart(2, '0');
          btn.innerHTML = '<i class="fa-solid fa-clock"></i> Poke again in ' + mins + ':' + secs;
          if (remaining-- <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.innerHTML = oldHtml;
          }
        }
        render();
        var timer = setInterval(render, 1000);
      }
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      $.post(AJAX_URL, { action: 'client_poke_seller', ref_type: 'digital_good', id: btn.getAttribute('data-id') || PURCHASE_ID }, function(resp){
        var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
        if (d && d.sendToast) toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
        if (d && d.cooldown_seconds) startCooldown(d.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });

  function loadChat() {
    if (isLoading) return;
    isLoading = true;
    $.post(AJAX_URL, {action:'client_dg_chat_load', purchase_id:PURCHASE_ID, mark_seen:1}, function(resp){
      isLoading = false;
      var r; try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){ return; }
      if (r && r.refreshPage) {
        setTimeout(function(){ location.reload(); }, 450);
        return;
      }
      var list = r.messages || {};
      var keys = Object.keys(list);
      var box  = document.getElementById('dg_chat_messages');
      if (!box) return;

      if (keys.length === 0) {
        if (!msgNone) {
          box.innerHTML = '<div class="lb-chat-empty"><i class="fa-solid fa-comments" style="font-size:2rem;opacity:.3;"></i><div style="font-size:.85rem;">No messages yet. Say hello!</div></div>';
          msgNone = true;
        }
        chatSig = ''; return;
      }

      msgNone = false;
      var sig = buildSig(list);
      if (sig === chatSig) return;
      chatSig = sig;

      var atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 50;
      var html = '';
      var lastSender = '', lastId = '';
      $.each(list, function(k, v){
        var grouped = (v.sender === lastSender && String(v.sender_id) === lastId);
        html += renderMsg(k, v, grouped);
        lastSender = v.sender; lastId = String(v.sender_id);
      });
      box.innerHTML = html;
      if (atBottom || msgNone) box.scrollTop = box.scrollHeight;
    }).fail(function(){ isLoading = false; });
  }

  // Realtime: mirror the seller side so a new seller message appears instantly
  // instead of after the next 30s poll.
  function handleDgRealtime(data) {
    if (!data) return;
    var matches = String(data.purchase_id || '') === String(PURCHASE_ID)
               || String(data.order_id || '') === 'dgpurch_' + String(PURCHASE_ID);
    if (matches) loadChat();
  }

  function bindDgChatSocket() {
    var sock = window.lbSocket || window.socket || null;
    if (!sock) return;
    if (sock.__lbClientDgHandler) {
      try { sock.off('dg_chat_update', sock.__lbClientDgHandler); } catch (e) {}
    }
    sock.__lbClientDgHandler = handleDgRealtime;
    var joinRooms = function () { try { sock.emit('join', 'clients'); } catch (e) {} };
    joinRooms();
    try { sock.on('connect', joinRooms); } catch (e) {}
    try { sock.on('dg_chat_update', handleDgRealtime); } catch (e) {}
  }

  loadChat();
  bindDgChatSocket();
  setTimeout(bindDgChatSocket, 350);
  setTimeout(bindDgChatSocket, 1200);
  setInterval(function(){ if(document.visibilityState === 'visible' && !window.lbRealtimeConnected) loadChat(); }, 30000);

  /* ── File attach / paste / send ── */
  (function(){
    var form      = document.getElementById('dgChatForm');
    var fileInput = document.getElementById('dgChatFile');
    var attachBtn = document.getElementById('dgChatAttachBtn');
    var preview   = document.getElementById('dgChatPreview');
    var prevImg   = document.getElementById('dgChatPreviewImg');
    var prevName  = document.getElementById('dgChatPreviewName');
    var removeBtn = document.getElementById('dgChatRemoveFile');
    var errBox    = document.getElementById('dgChatError');
    var sendBtn   = document.getElementById('dgSendBtn');
    var previewUrl = null;

    function setError(msg) {
      if (!errBox) return;
      if (!msg) { errBox.classList.add('d-none'); errBox.textContent=''; return; }
      errBox.textContent = msg; errBox.classList.remove('d-none');
    }
    function clearFile() {
      if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
      if (fileInput)  fileInput.value = '';
      if (preview)    preview.style.display = 'none';
      if (prevImg)    prevImg.src = '';
      if (prevName)   prevName.textContent = '';
    }
    function showFile(file) {
      if (!file) return clearFile();
      if (!/^image\/(png|jpe?g|gif)$/i.test(file.type)) { setError('Only PNG/JPG/JPEG/GIF allowed.'); clearFile(); return; }
      setError('');
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = URL.createObjectURL(file);
      if (prevImg)  prevImg.src = previewUrl;
      if (prevName) prevName.textContent = file.name || 'image';
      if (preview)  preview.style.display = 'flex';
    }

    if (attachBtn && fileInput) {
      attachBtn.addEventListener('click', function(){ setError(''); fileInput.click(); });
      fileInput.addEventListener('change', function(){ showFile(fileInput.files && fileInput.files[0]); });
    }
    if (removeBtn) removeBtn.addEventListener('click', function(){ setError(''); clearFile(); });

    document.addEventListener('paste', function(e){
      if (!fileInput || fileInput.disabled) return;
      var active = document.activeElement;
      if (!form || (!form.contains(active) && active !== document.getElementById('dgChatInput'))) return;
      var items = (e.clipboardData && e.clipboardData.items) ? e.clipboardData.items : [];
      for (var i=0; i<items.length; i++) {
        if (items[i] && items[i].type && items[i].type.indexOf('image/') === 0) {
          var blob = items[i].getAsFile(); if (!blob) continue;
          var file = new File([blob], 'pasted-image.png', {type: blob.type || 'image/png'});
          var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
          showFile(file); e.preventDefault(); break;
        }
      }
    });

    if (form) form.addEventListener('submit', function(e){
      e.preventDefault();
      var msgInput = document.getElementById('dgChatInput');
      var msg      = msgInput ? msgInput.value.trim() : '';
      var hasFile  = fileInput && fileInput.files && fileInput.files.length > 0;
      if (!msg && !hasFile) { setError('Please type a message or attach an image.'); return; }
      setError('');
      if (sendBtn) {
        sendBtn.disabled = true;
        var prog = sendBtn.querySelector('.indicator-progress');
        if (prog) prog.classList.remove('d-none');
        var lbl = sendBtn.querySelector('.indicator-label');
        if (lbl) lbl.classList.add('d-none');
      }
      var fd = new FormData(form);
      $.ajax({
        url: form.getAttribute('action'), method: 'POST',
        data: fd, processData: false, contentType: false
      }).done(function(resp){
        var r; try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){ r = {}; }
        if (sendBtn) {
          sendBtn.disabled = false;
          var prog2 = sendBtn.querySelector('.indicator-progress');
          if (prog2) prog2.classList.add('d-none');
          var lbl2 = sendBtn.querySelector('.indicator-label');
          if (lbl2) lbl2.classList.remove('d-none');
        }
        if (r && r.success) {
          if (msgInput) msgInput.value = '';
          clearFile();
          chatSig = '';
          loadChat();
        } else {
          var errMsg = (r && r.sendToast && r.sendToast.message) || (r && r.message) || 'Message could not be sent.';
          toast('danger', 'Error', errMsg);
        }
      }).fail(function(){
        if (sendBtn) {
          sendBtn.disabled = false;
          var prog3 = sendBtn.querySelector('.indicator-progress');
          if (prog3) prog3.classList.add('d-none');
          var lbl3 = sendBtn.querySelector('.indicator-label');
          if (lbl3) lbl3.classList.remove('d-none');
        }
        toast('danger', 'Error', 'Message could not be sent.');
      });
    });
  })();

  (function(){
    var cdOverlay = document.getElementById('cdOverlay');
    if (!cdOverlay) return;
    function cdOpen(){ cdOverlay.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    function cdClose(){ cdOverlay.classList.remove('is-open'); document.body.style.overflow = ''; }
    window.dgCloseConfirmModal = cdClose;
    $(document).on('click', '#dgConfirmBtn', function(e){ e.preventDefault(); cdOpen(); });
    var cdCloseBtn = document.getElementById('cdClose');
    var cdCancel   = document.getElementById('cdCancelBtn');
    if (cdCloseBtn) cdCloseBtn.addEventListener('click', cdClose);
    if (cdCancel)   cdCancel.addEventListener('click', cdClose);
    cdOverlay.addEventListener('click', function(e){ if (e.target === cdOverlay) cdClose(); });
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && cdOverlay.classList.contains('is-open')) cdClose();
    });
  })();

  $('#cdConfirmBtn').on('click', function(){
    var $btn = $(this);
    if ($btn.prop('disabled')) return;

    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Confirming...');

    function resetBtn(){
      $btn.prop('disabled', false).html('<i class="fa-solid fa-circle-check"></i> Yes, confirm');
      if (typeof window.dgCloseConfirmModal === 'function') window.dgCloseConfirmModal();
    }

    function finishSuccess(){
      if (typeof create_toast === 'function') {
        create_toast('success', 'Done!', 'Delivery confirmed.');
      }
      setTimeout(function(){ location.reload(); }, 500);
    }

    $.ajax({
      url: AJAX_URL,
      method: 'POST',
      data: {action:'client_dg_confirm_delivery', purchase_id:PURCHASE_ID},
      dataType: 'text'
    }).done(function(resp){
      var r = null;
      try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e) { r = null; }

      if (r && r.success) {
        finishSuccess();
        return;
      }

      // If the server updated the DB but an older/non-JSON response came back, avoid showing
      // a false error. Reloading will show the real COMPLETED status.
      if (!r && typeof resp === 'string' && resp.trim() !== '') {
        setTimeout(function(){ location.reload(); }, 500);
        return;
      }

      resetBtn();
      var msg = (r && r.message) ? r.message : 'Could not confirm delivery.';
      if (typeof create_toast === 'function') create_toast('danger', 'Error', msg);
      else alert(msg);
    }).fail(function(){
      // Same false-negative protection: the previous request may have completed successfully.
      setTimeout(function(){ location.reload(); }, 650);
    });
  });

  // Completed review modal (same flow as account view)
  (function(){
    var SELLER_ID = <?= (int)$sellerId ?>;
    var key = 'lb_dg_sr_popup_v2_' + PURCHASE_ID;
    function isDismissed(){ try { return localStorage.getItem(key) === '1'; } catch(e){ return false; } }
    function markDismissed(){ try { localStorage.setItem(key, '1'); } catch(e){} }

    function initCompletedReviewModal(){
      var completedEl = document.getElementById('sr_completed_md');
      if (!completedEl || !window.bootstrap || !SELLER_ID) return;
      var dismissBtn = document.getElementById('sr_dismiss_btn');
      if (dismissBtn) dismissBtn.addEventListener('click', markDismissed);
      completedEl.querySelectorAll('[data-bs-target="#sr_leave_review_md"]').forEach(function(btn){ btn.addEventListener('click', markDismissed); });
      setTimeout(function(){
        if (document.querySelector('.modal.show')) return;
        if (isDismissed()) return;
        bootstrap.Modal.getOrCreateInstance(completedEl).show();
      }, 700);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCompletedReviewModal); else initCompletedReviewModal();

    var tpWrap = document.getElementById('sr_tp_stars');
    if (tpWrap) {
      var tpStars = Array.from(tpWrap.querySelectorAll('.lb-star-sr'));
      tpStars.forEach(function(star){
        star.addEventListener('mouseover', function(){ var v=parseInt(this.dataset.index,10)||0; tpStars.forEach(function(x){ x.classList.toggle('is-on', (parseInt(x.dataset.index,10)||0) <= v); }); });
        star.addEventListener('mouseout', function(){ tpStars.forEach(function(x){ x.classList.remove('is-on'); }); });
        star.addEventListener('click', function(){ markDismissed(); window.open('https://www.trustpilot.com/evaluate/lolboost.gg?stars=' + this.dataset.index, '_blank'); });
      });
    }

    var rvWrap = document.getElementById('sr_review_stars');
    if (rvWrap) {
      var rvStars = Array.from(rvWrap.querySelectorAll('.lb-star-sr'));
      var hidden = document.getElementById('sr_rating_val');
      var selected = 0;
      rvStars.forEach(function(star){
        star.addEventListener('mouseover', function(){ var v=parseInt(this.dataset.index,10)||0; rvStars.forEach(function(x){ x.classList.toggle('is-on', (parseInt(x.dataset.index,10)||0) <= v); }); });
        star.addEventListener('mouseout', function(){ rvStars.forEach(function(x){ x.classList.toggle('is-on', (parseInt(x.dataset.index,10)||0) <= selected); }); });
        star.addEventListener('click', function(){ selected=parseInt(this.dataset.index,10)||0; if(hidden) hidden.value=selected; rvStars.forEach(function(x){ x.classList.toggle('is-on', (parseInt(x.dataset.index,10)||0) <= selected); }); });
      });
    }

    var suggestionWrap = document.getElementById('sr_comment_suggestions');
    var commentInput = document.getElementById('sr_comment_val');
    if (suggestionWrap && commentInput) {
      var suggestionButtons = Array.from(suggestionWrap.querySelectorAll('.sr-suggestion-pill'));
      suggestionButtons.forEach(function(btn){
        btn.addEventListener('click', function(){
          var text = (this.dataset.text || '').trim();
          if (!text) return;
          commentInput.value = text;
          commentInput.focus();
          try { commentInput.setSelectionRange(commentInput.value.length, commentInput.value.length); } catch(e){}
          suggestionButtons.forEach(function(x){ x.classList.remove('is-active'); });
          this.classList.add('is-active');
        });
      });
      commentInput.addEventListener('input', function(){
        var current = (commentInput.value || '').trim();
        suggestionButtons.forEach(function(btn){ btn.classList.toggle('is-active', current !== '' && current === (btn.dataset.text || '').trim()); });
      });
    }

    var submitBtn = document.getElementById('sr_review_submit');
    var submitSpin = document.getElementById('sr_review_spin');
    var errEl = document.getElementById('sr_review_err');
    if (submitBtn) submitBtn.addEventListener('click', function(){
      var rating = parseInt((document.getElementById('sr_rating_val') || {}).value || 0, 10);
      var comment = ((document.getElementById('sr_comment_val') || {}).value || '').trim();
      if (errEl) errEl.textContent = '';
      if (rating < 1 || rating > 5) { if (errEl) errEl.textContent = 'Please select a star rating.'; return; }
      submitBtn.disabled = true;
      if (submitSpin) submitSpin.classList.remove('d-none');

      var fd = new FormData();
      fd.append('action', 'client_dg_submit_review');
      fd.append('seller_id', SELLER_ID);
      fd.append('purchase_id', PURCHASE_ID);
      fd.append('rating', rating);
      fd.append('comment', comment);

      fetch(AJAX_URL || '/ajax', { method:'POST', body:fd, credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(res){
          var t = (res && res.sendToast) ? res.sendToast : {};
          var ok = (res && res.success === true) || t.type === 'success' || t.type === 'warning';
          if (ok) {
            markDismissed();
            var modalEl = document.getElementById('sr_leave_review_md');
            var m = modalEl && window.bootstrap ? bootstrap.Modal.getInstance(modalEl) : null;
            if (m) m.hide();
            if (typeof create_toast === 'function') create_toast(t.type || 'success', t.title || 'Done', t.message || 'Review submitted!');
            setTimeout(function(){ location.reload(); }, 700);
            return;
          }
          if (errEl) errEl.textContent = t.message || (res && res.message) || 'Something went wrong.';
          submitBtn.disabled = false;
          if (submitSpin) submitSpin.classList.add('d-none');
        })
        .catch(function(){
          if (errEl) errEl.textContent = 'Could not submit. Try again.';
          submitBtn.disabled = false;
          if (submitSpin) submitSpin.classList.add('d-none');
        });
    });
  })();

  document.querySelectorAll('.av-gallery-tile[data-zoom]').forEach(function(tile){
    tile.addEventListener('click', function(){
      var src = tile.getAttribute('data-zoom');
      var img = document.getElementById('dgImgModalImg');
      if (img) img.src = src;
      if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('dgImgModal')).show();
      } else {
        window.open(src, '_blank');
      }
    });
  });

  /* ── REPORT A PROBLEM ── */
  (function(){
    var REPORT_AJAX = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
    var overlay         = document.getElementById('rpOverlay');
    var openBtn         = document.getElementById('reportProblemBtn');
    var closeBtn        = document.getElementById('rpClose');
    var cancelBtn       = document.getElementById('rpCancelBtn');
    var submitBtn       = document.getElementById('rpSubmitBtn');
    var formWrap        = document.getElementById('rpFormWrap');
    var successWrap     = document.getElementById('rpSuccessWrap');
    var successCloseBtn = document.getElementById('rpSuccessClose');
    var detailsEl       = document.getElementById('rpDetails');
    var problemOpts     = document.querySelectorAll('.rp-problem-opt');
    var selectedIssue   = null;

    function openModal(){ overlay.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    function closeModal(){
      overlay.classList.remove('is-open'); document.body.style.overflow = '';
      setTimeout(function(){
        selectedIssue = null;
        problemOpts.forEach(function(o){ o.classList.remove('is-selected'); });
        if (detailsEl) detailsEl.value = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
        formWrap.style.display = ''; successWrap.style.display = 'none';
      }, 220);
    }

    if (openBtn)          openBtn.addEventListener('click', openModal);
    if (closeBtn)         closeBtn.addEventListener('click', closeModal);
    if (cancelBtn)        cancelBtn.addEventListener('click', closeModal);
    if (successCloseBtn)  successCloseBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e){ if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal(); });

    problemOpts.forEach(function(opt){
      opt.addEventListener('click', function(){
        problemOpts.forEach(function(o){ o.classList.remove('is-selected'); });
        this.classList.add('is-selected');
        this.querySelector('input[type="radio"]').checked = true;
        selectedIssue = this.getAttribute('data-id');
        submitBtn._label = (this.querySelector('.rp-problem-text') || {}).textContent || selectedIssue;
        submitBtn.disabled = false;
      });
    });

    submitBtn.addEventListener('click', async function(){
      if (!selectedIssue) return;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…';

      var issueLabel  = submitBtn._label || selectedIssue;
      var details     = detailsEl ? detailsEl.value.trim() : '';
      var itemTitle   = <?= json_encode($h($itemTitle)) ?>;
      var purchaseId  = <?= $id ?>;
      var sellerName  = <?= json_encode($h($sellerName)) ?>;
      var clientId    = <?= (int)(CLIENT_ID ?? 0) ?>;
      var orderUrl    = '<?= BASE_URL ?>/profile/digital-goods/' + purchaseId;
      var adminUrl    = '<?= ADMN_URL ?>/digital-goods/' + purchaseId;

      var payload = {
        username: 'DG Reports',
        embeds: [{
          title: '🚨 Digital Good Problem Report',
          color: 0xef4444,
          fields: [
            { name: '📦 Order',    value: '**' + itemTitle + '** (#' + purchaseId + ')', inline: true  },
            { name: '🏪 Seller',   value: sellerName,                                       inline: true  },
            { name: '👤 Client',   value: '#' + clientId,                                   inline: true  },
            { name: '⚠️ Issue',    value: issueLabel,                                        inline: false },
            ...(details ? [{ name: '📝 Details', value: details.substring(0, 1000), inline: false }] : []),
            { name: '🔗 Admin',    value: '[View in Admin Panel](' + adminUrl + ')',         inline: false },
          ],
          footer: { text: 'Reported via lolboost.gg' },
          timestamp: new Date().toISOString(),
        }]
      };

      try {
        var fd = new FormData();
        fd.set('action', 'client_report_problem');
        fd.set('ref_type', 'digital_good');
        fd.set('ref_id', String(purchaseId));
        fd.set('issue', selectedIssue);
        fd.set('issue_label', issueLabel);
        fd.set('details', details);
        var res = await fetch(REPORT_AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
        var d = await res.json();
        if (d && d.success) {
          formWrap.style.display = 'none'; successWrap.style.display = '';
        } else { throw new Error((d && d.message) ? d.message : 'Report failed'); }
      } catch(err) {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not send report. Please try again.');
      }
    });
  })();

})();
</script>
<?= $this->end() ?>
