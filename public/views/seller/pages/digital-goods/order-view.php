<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
require_once dirname(__DIR__) . '/_orders_shared.php';

$purchase = is_array($purchase ?? null) ? $purchase : [];
$images = is_array($images ?? null) ? $images : [];
$id = (int)($purchase['id'] ?? 0);
$statusRaw = strtoupper(trim((string)($purchase['status'] ?? 'UNPAID')));
$title = (string)($purchase['item_title'] ?? 'Digital Good');
$buyerName = (string)($purchase['client_username'] ?? ('Buyer #' . (int)($purchase['client_id'] ?? 0)));
// clients.icon is stored inconsistently (full URL / path / bare filename / empty),
// so normalize it the same way the order lists do.
$buyerIcon = sol_client_icon($purchase['client_icon'] ?? '');
$qty = max(1, (int)($purchase['quantity'] ?? 1));
$totalCents = (int)($purchase['price'] ?? 0);
$unitCents = (int)($purchase['unit_price'] ?? 0);
$category = (string)($purchase['category_name'] ?? 'Digital Goods');
$brand = (string)($purchase['brand'] ?? '');
$itemId = (int)($purchase['item_id'] ?? 0);
$orderCode = (string)($purchase['order_code'] ?? $purchase['invoice_id'] ?? $purchase['payment_id'] ?? '');

// Review/rating data (shown once buyer has reviewed)
$reviewRating = 0;
$reviewComment = '';
$reviewCreatedAtRaw = '';

$applyReviewRow = static function ($row) use (&$reviewRating, &$reviewComment, &$reviewCreatedAtRaw): void {
    if (!is_array($row) || empty($row)) return;

    foreach (['review_rating', 'rating', 'seller_review_rating', 'dg_review_rating'] as $_rk) {
        if (isset($row[$_rk]) && is_numeric($row[$_rk])) {
            $reviewRating = max(0, min(5, (int)$row[$_rk]));
            break;
        }
    }

    foreach (['review_comment', 'comment', 'seller_review_comment', 'dg_review_comment'] as $_ck) {
        if (isset($row[$_ck]) && trim((string)$row[$_ck]) !== '') {
            $reviewComment = trim((string)$row[$_ck]);
            break;
        }
    }

    foreach (['review_created_at', 'reviewed_at', 'seller_review_created_at', 'dg_review_created_at', 'created_at'] as $_dk) {
        if (!empty($row[$_dk])) {
            $reviewCreatedAtRaw = (string)$row[$_dk];
            break;
        }
    }
};

// Reviews may only be shown for the exact completed purchase.
// Important: do NOT fallback by item_id + client_id, because the same buyer can buy the same
// digital good multiple times and an old review would then appear on a new/unpaid order.
$canShowBuyerReview = ($statusRaw === 'COMPLETED');

// 1) Use review fields only if the route/query already joined them into the current purchase.
if ($canShowBuyerReview) {
    $applyReviewRow($purchase);
}

// 2) Fallback: load the review from seller_reviews by exact digital-good purchase only.
if ($canShowBuyerReview && ($reviewRating < 1 || $reviewRating > 5) && $id > 0) {
    try {
        global $db;
        if (isset($db) && is_object($db) && method_exists($db, 'row')) {
            $reviewRow = $db->row(
                "SELECT rating, comment, created_at, approved
                 FROM seller_reviews
                 WHERE review_source = 'digital_good'
                   AND source_purchase_id = ?
                   AND COALESCE(approved, 1) = 1
                 ORDER BY created_at DESC, id DESC
                 LIMIT 1",
                $id
            );

            $applyReviewRow($reviewRow);
        }
    } catch (Throwable $e) {
        // Keep the order page working even if the review lookup fails.
    }
}

$hasReview = $canShowBuyerReview && $reviewRating >= 1 && $reviewRating <= 5;

$assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '';
$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;

    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#^public/assets#', '', $path);
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
$thumb = $brandIcon !== '' ? $brandIcon : (!empty($images[0]) ? $images[0] : '');
$thumbIsBrandIcon = $brandIcon !== '' && $thumb === $brandIcon;

$deliveryNote = (string)($purchase['delivery_note'] ?? '');
$createdAtRaw = (string)($purchase['created_at'] ?? '');
$paidAtRaw = (string)($purchase['paid_at'] ?? '');
$deliveredAtRaw = (string)($purchase['delivered_at'] ?? '');
$reviewCreatedAt = $reviewCreatedAtRaw !== '' ? date('d.m.Y H:i', strtotime($reviewCreatedAtRaw)) : null;

$h = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$sym = function_exists('util_format_currency_display') ? util_format_currency_display('EUR') : '€';

$total = round($totalCents / 100, 2);
$unit  = round($unitCents / 100, 2);
$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$payoutRaw = $purchase['seller_payout'] ?? $purchase['seller_amount'] ?? $purchase['payout'] ?? $purchase['earnings'] ?? null;
$payout = ($payoutRaw !== null && $payoutRaw !== '')
    ? round((float)$payoutRaw / 100, 2)
    : round($total * (1 - ($effective_fee / 100)), 2);

// Same three-state badge vocabulary the item order view uses.
$status = in_array($statusRaw, ['DELIVERED', 'COMPLETED'], true) ? 'Delivered'
        : (in_array($statusRaw, ['CANCELLED', 'REFUNDED', 'FAILED'], true) ? 'Cancelled' : 'Pending');
$badgeCls = $status === 'Delivered' ? 'av-status--active'
          : ($status === 'Cancelled' ? 'av-status--sold' : 'av-status--unlisted');
$statusIcon = $status === 'Delivered' ? 'fa-check' : ($status === 'Cancelled' ? 'fa-xmark' : 'fa-clock');

$statusLabel = match($statusRaw) {
    'PAID' => 'Paid - Deliver now',
    'PROCESSING' => 'Processing',
    'DELIVERED' => 'Delivered - Awaiting buyer',
    'COMPLETED' => 'Completed',
    'CANCELLED' => 'Cancelled',
    'REFUNDED' => 'Refunded',
    default => 'Unpaid',
};
$canDeliver = in_array($statusRaw, ['PAID', 'PROCESSING'], true);
?>
<?= $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => 'Digital Good Order #' . $id]]) ?>

<?= $this->start('styles') ?>
<style>
.seller-item-order-view .card { background:var(--bs-card-bg)!important; border:var(--bs-card-border-color) 1px solid!important; border-radius:22px!important; box-shadow:none!important; }
.seller-item-order-view .card::before { display:none!important; }
.seller-item-order-view .order-chat-card { overflow:hidden; }

/* Head */
.av-head { border-radius:22px; overflow:hidden; margin-bottom:20px; border:1px solid var(--bs-card-border-color); background:#25282a; }
.av-head-body { padding:20px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; border-bottom:1px solid var(--bs-card-border-color); }
.av-title { font-size:1.2rem; font-weight:950; color:rgba(255,255,255,.92); margin:0; line-height:1.2; }
.av-sub   { font-size:.82rem; color:rgba(255,255,255,.5); margin-top:4px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.av-status { display:inline-flex; align-items:center; gap:.35rem; padding:4px 11px; border-radius:99px; font-size:.75rem; font-weight:800; }
.av-status--active   { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.25);  color:#4ade80; }
.av-status--sold     { background:rgba(251,113,133,.12); border:1px solid rgba(251,113,133,.25); color:#fb7185; }
.av-status--unlisted { background:rgba(250,204,21,.12);  border:1px solid rgba(250,204,21,.25);  color:#facc15; }
.av-meta-row  { position:relative; z-index:1; display:flex; flex-wrap:wrap; gap:6px; padding:14px 22px 16px; }
.av-meta-pill { display:inline-flex; align-items:center; gap:.3rem; padding:4px 11px; border-radius:99px; font-size:.75rem; font-weight:700; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.7); }
.av-meta-pill strong { color:rgba(255,255,255,.92); }
.av-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.av-btn-primary { display:inline-flex; align-items:center; gap:.4rem; padding:7px 16px; border-radius:11px; font-size:.83rem; font-weight:800; background:linear-gradient(135deg,#6d5cff,#b05cff); border:none; color:#fff; cursor:pointer; transition:opacity .15s; text-decoration:none; }
.av-btn-primary:hover { opacity:.88; color:#fff; }
.av-btn-success { display:inline-flex; align-items:center; gap:.4rem; padding:7px 16px; border-radius:11px; font-size:.83rem; font-weight:800; background:rgba(74,222,128,.14); border:1px solid rgba(74,222,128,.25); color:#4ade80; cursor:pointer; transition:background .12s; }
.av-btn-success:hover { background:rgba(74,222,128,.22); }
.av-btn-ghost { display:inline-flex; align-items:center; gap:.4rem; padding:7px 14px; border-radius:11px; font-size:.83rem; font-weight:700; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.7); cursor:pointer; transition:background .12s; text-decoration:none; }
.av-btn-ghost:hover { background:rgba(255,255,255,.09); color:#fff; }
.av-btn-primary:disabled, .av-btn-success:disabled { opacity:.55; cursor:not-allowed; }

/* Gallery */
.av-gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:10px; }
.av-gallery-tile { position:relative; border-radius:12px; overflow:hidden; background:#0d0f1a; cursor:pointer; }
.av-gallery-tile img { width:100%; height:140px; object-fit:cover; display:block; transition:transform .3s; }
.av-gallery-tile:hover img { transform:scale(1.04); }
.av-gallery-main-badge { position:absolute; top:8px; left:8px; padding:2px 8px; border-radius:99px; background:rgba(109,92,255,.9); color:#fff; font-size:.68rem; font-weight:800; }

/* Chat */
.av-chat-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--bs-card-border-color); }
.av-chat-title  { font-size:.95rem; font-weight:900; color:rgba(255,255,255,.9); display:flex; align-items:center; gap:.5rem; }
#chat_messages { min-height:300px; max-height:480px; overflow-y:auto; padding:1rem 1.25rem; display:flex; flex-direction:column; scroll-behavior:smooth; }
#chat_messages::-webkit-scrollbar { width:5px; }
#chat_messages::-webkit-scrollbar-track { background:transparent; }
#chat_messages::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:3px; }
.lb-msg { display:flex; flex-direction:column; margin-bottom:.5rem; max-width:75%; }
.lb-msg--start { align-self:flex-start; }
.lb-msg--end   { align-self:flex-end;   }
.lb-msg__head  { display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem; }
.lb-msg__head--end { flex-direction:row-reverse; }
.lb-msg__avatar { width:1.75rem; height:1.75rem; border-radius:50%; object-fit:cover; flex-shrink:0; }
.lb-msg__name { font-weight:700; font-size:.8rem; line-height:1.3; display:flex; align-items:center; gap:.3rem; }
.lb-msg__bubble { padding:.55rem .85rem; border-radius:.75rem; font-size:.875rem; line-height:1.55; word-break:break-word; background:rgba(255,255,255,.07); }
.lb-msg--end .lb-msg__bubble { background:rgba(99,102,241,.22); }
.lb-msg__stamp { font-size:.7rem; opacity:.4; margin-top:.2rem; }
.lb-msg--end .lb-msg__stamp { text-align:right; }
.lb-msg__content img { max-width:240px; max-height:200px; border-radius:.5rem; display:block; margin-top:.4rem; cursor:pointer; }
.lb-msg__ticks { margin-left:.25rem; }
.lb-badge { display:inline-flex; align-items:center; padding:.1rem .4rem; border-radius:999px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.lb-badge--seller { background:rgba(99,102,241,.2);   color:#818cf8; }
.lb-badge--client { background:rgba(16,185,129,.15);  color:#10b981; }
.lb-badge--admin  { background:rgba(245,158,11,.15);  color:#f59e0b; }
.lb-badge--system { background:rgba(107,114,128,.15); color:#9ca3af; }
.lb-chat-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:240px; opacity:.4; gap:.5rem; text-align:center; }
.lb-chat-preview { display:inline-flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:.5rem; padding:.4rem .7rem; }
.lb-chat-preview img { width:2.5rem; height:2.5rem; object-fit:cover; border-radius:.35rem; }
.lb-chat-preview__remove { background:none; border:none; color:rgba(255,255,255,.5); cursor:pointer; padding:0 .2rem; }

/* Sidebar */
.av-sidebar-card { border-radius:18px; border:1px solid rgba(255,255,255,.07); background:#25282a; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,.2); }
.av-sc-header { display:flex; align-items:center; gap:8px; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02); }
.av-sc-icon { width:26px; height:26px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,.1); font-size:.75rem; }
.av-sc-title { font-size:.8rem; font-weight:900; color:rgba(255,255,255,.75); text-transform:uppercase; letter-spacing:.06em; flex:1; }
.av-sc-body { padding:14px 16px; }

/* Earnings bar */
.av-ov-earnings { display:flex; align-items:center; justify-content:space-around; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02); }
.av-ov-earn-item { text-align:center; }
.av-ov-earn-label { font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.av-ov-earn-val { font-size:.9rem; font-weight:900; color:rgba(255,255,255,.88); }
.av-ov-earn-sep { font-size:.9rem; color:rgba(255,255,255,.2); font-weight:300; }

/* Stat grid */
.av-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; }
.av-stat-item { display:flex; align-items:center; gap:8px; padding:9px 14px; border-bottom:1px solid rgba(255,255,255,.04); border-right:1px solid rgba(255,255,255,.04); }
.av-stat-item:nth-child(even) { border-right:0; }
.av-stat-item:nth-last-child(-n+2) { border-bottom:0; }
.av-stat-ico { font-size:.65rem; color:rgba(255,255,255,.25); width:14px; flex-shrink:0; }
.av-stat-lbl { font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.04em; line-height:1; }
.av-stat-val { font-size:.8rem; font-weight:800; color:rgba(255,255,255,.82); margin-top:2px; line-height:1.2; }

/* Buyer */
.av-buyer-row { display:flex; align-items:center; gap:12px; padding:14px 16px; }
.av-buyer-avi { width:38px; height:38px; border-radius:10px; background:rgba(74,222,128,.15); border:1px solid rgba(74,222,128,.25); display:flex; align-items:center; justify-content:center; font-size:.95rem; font-weight:900; color:#4ade80; flex-shrink:0; overflow:hidden; }
.av-buyer-avi img { width:100%; height:100%; object-fit:cover; }
/* Buyer chip in the chat header: initial as backdrop, avatar on top. */
.av-chip-avi { position:relative; overflow:hidden; width:18px; height:18px; border-radius:50%; background:rgba(74,222,128,.2); display:inline-flex; align-items:center; justify-content:center; font-size:.6rem; font-weight:900; flex-shrink:0; }
.av-chip-avi img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; border-radius:50%; }

/* Delivery (DG-specific, styled with the same vocabulary as the item friendship box) */
.av-delivery { margin:12px 16px; padding:12px 14px; border-radius:12px; font-size:.82rem; }
.av-delivery--waiting { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.5); }
.av-delivery--done    { background:rgba(74,222,128,.07);  border:1px solid rgba(74,222,128,.18); color:#4ade80; white-space:pre-wrap; word-break:break-word; }
.av-delivery textarea { width:100%; min-height:110px; margin-top:8px; padding:10px 12px; border-radius:11px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.11); color:#fff; resize:vertical; outline:none; font-size:.82rem; }

/* Review */
.av-review-summary { display:flex; align-items:flex-start; gap:12px; }
.av-review-score { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:rgba(251,191,36,.12); border:1px solid rgba(251,191,36,.25); color:#fbbf24; font-size:1rem; font-weight:950; }
.av-review-stars { display:flex; gap:3px; margin-bottom:5px; }
.av-review-stars i { font-size:.9rem; color:rgba(255,255,255,.18); }
.av-review-stars i.is-on { color:#fbbf24; }
.av-review-comment { margin-top:10px; padding:11px 13px; border-radius:12px; background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.075); color:rgba(255,255,255,.82); font-size:.84rem; line-height:1.55; white-space:pre-wrap; word-break:break-word; }
.av-review-empty { color:rgba(255,255,255,.42); font-size:.84rem; line-height:1.5; }

/* Lightbox */
#lbImgModal .modal-content { background:rgba(0,0,0,.85); border:none; }

@media (max-width:768px) {
  .av-head-body { padding:14px 16px; }
  .av-meta-row  { padding:10px 16px 12px; }
  #chat_messages { min-height:220px; max-height:340px; }
  .lb-msg { max-width:88%; }
  .av-gallery-grid { grid-template-columns:repeat(2,1fr); }
}
</style>
<?= $this->stop() ?>

<div class="seller-item-order-view">

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
        <?php if ($thumb): ?>
          <img src="<?= $h($thumb) ?>" style="width:100%;height:100%;object-fit:<?= $thumbIsBrandIcon ? 'contain' : 'cover' ?>;" alt="">
        <?php else: ?>
          <i class="<?= $h($categoryIcon) ?>" style="font-size:1.4rem;color:#a5b4fc;"></i>
        <?php endif; ?>
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 class="av-title"><?= $h($title) ?></h1>
          <span class="av-status <?= $badgeCls ?>"><i class="fa-solid <?= $statusIcon ?>" style="font-size:.55rem;"></i> <?= $h($status) ?></span>
        </div>
        <div class="av-sub">
          <?php if ($brand): ?><span style="text-transform:uppercase;font-weight:700;"><?= $h($brand) ?></span><span>·</span><?php endif; ?>
          <span>#<?= $id ?></span>
          <?php if ($orderCode): ?><span>·</span><span>Invoice #<?= $h($orderCode) ?></span><?php endif; ?>
          <span>·</span>
          <span><?= $createdAtRaw ? date('d.m.Y', strtotime($createdAtRaw)) : '—' ?></span>
        </div>
      </div>
    </div>

    <div class="av-actions">
      <a href="<?= BASE_URL ?>/seller-area/digital-goods" class="av-btn-ghost">
        <i class="fa-solid fa-arrow-left"></i> Back
      </a>
      <?php if ($itemId > 0): ?>
      <a class="av-btn-primary" href="<?= BASE_URL ?>/seller-area/digital-goods/listings/<?= $itemId ?>/edit">
        <i class="fa-solid fa-box-open"></i> Open Listing
      </a>
      <?php endif; ?>
      <?php if (!empty($purchase['client_id'])): ?>
      <?= sol_poke_client_button($id, 'digital_good') ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><i class="<?= $h($categoryIcon) ?>" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($category) ?></strong></span>
    <?php if ($brand): ?><span class="av-meta-pill"><i class="fa-solid fa-tag" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($brand) ?></strong></span><?php endif; ?>
    <span class="av-meta-pill"><i class="fa-solid fa-layer-group" style="color:rgba(255,255,255,.4);"></i> Qty <strong><?= $qty ?></strong></span>
    <span class="av-meta-pill"><i class="fa-solid fa-coins" style="color:rgba(255,255,255,.4);"></i> <strong><?= $sym . number_format($total, 2) ?></strong></span>
    <span class="av-meta-pill"><i class="fa-solid fa-sack-dollar" style="color:#4ade80;"></i> <strong style="color:#4ade80;"><?= $sym . number_format($payout, 2) ?></strong> Payout</span>
  </div>
</div>


<!-- ── 2-COLUMN LAYOUT ── -->
<div class="row g-4 align-items-start">

  <!-- LEFT: Chat + Gallery -->
  <div class="col-12 col-lg-8">

    <!-- Chat -->
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Buyer Chat
        </div>
        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
          <span class="av-chip-avi"><?= strtoupper(substr($buyerName, 0, 1)) ?><?php if ($buyerIcon): ?><img src="<?= $h($buyerIcon) ?>" alt="" onerror="this.remove()"><?php endif; ?></span>
          <?= $h($buyerName) ?>
        </div>
      </div>

      <div class="card-body chat-bg" id="chat_messages"></div>

      <div class="card-footer">
        <form class="row gx-2" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action"      value="seller_dg_chat_send">
          <input type="hidden" name="purchase_id" value="<?= $id ?>">
          <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none">
          <div class="col">
            <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message to the buyer">
          </div>
          <div class="col-auto d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="lbChatUploadBtn" title="Attach image">
              <i class="fa-duotone fa-paperclip"></i>
            </button>
            <button type="submit" class="btn btn-sm btn-primary" id="lbChatSendBtn">
              <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
              <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
          </div>
          <div class="col-12 mt-2 d-none" id="lbChatImagePreviewWrap">
            <div class="lb-chat-preview">
              <img id="lbChatImagePreview" src="" alt="preview">
              <button type="button" class="lb-chat-preview__remove" id="lbChatImageRemove"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
        </form>
        <div class="text-muted small mt-2">Tip: You can paste a screenshot with <strong>Ctrl + V</strong>.</div>
      </div>
    </div>

    <!-- Gallery -->
    <?php if (!empty($images)): ?>
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;">
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-images me-2" style="color:#9f8cff;"></i>Product Gallery <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?></span></h4>
      </div>
      <div class="card-body" style="padding:16px 20px;">
        <div class="av-gallery-grid">
          <?php foreach ($images as $i => $img): ?>
          <div class="av-gallery-tile" data-zoom="<?= $h((string)$img) ?>">
            <?php if ($i === 0): ?><div class="av-gallery-main-badge">MAIN</div><?php endif; ?>
            <img src="<?= $h((string)$img) ?>" alt="" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>


  <!-- RIGHT: Overview + Buyer + Delivery + Review -->
  <div class="col-12 col-lg-4">

    <!-- Earnings / Order Overview -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Order Overview</span>
      </div>
      <div class="av-ov-earnings">
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Total</div>
          <div class="av-ov-earn-val"><?= $sym . number_format($total, 2) ?></div>
        </div>
        <div class="av-ov-earn-sep">→</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Fee</div>
          <div class="av-ov-earn-val" style="color:#fb7185;">−<?= $effective_fee ?>%</div>
        </div>
        <div class="av-ov-earn-sep">=</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">You Earn</div>
          <div class="av-ov-earn-val" style="color:#4ade80;font-size:1rem;"><?= $sym . number_format($payout, 2) ?></div>
        </div>
      </div>
      <div class="av-stat-grid">
        <?php
        $statsGrid = [
            ['fa-solid fa-hashtag',     'Order ID',   '#' . $id],
            ['fa-solid fa-layer-group', 'Quantity',   $qty],
            ['fa-solid fa-tag',         'Unit Price', $sym . number_format($unit, 2)],
            ['fa-solid fa-gift',        'Type',       $category],
            ['fa-solid fa-circle-info', 'Status',     $statusLabel],
            ['fa-solid fa-calendar',    'Created',    $createdAtRaw ? date('d.m.Y H:i', strtotime($createdAtRaw)) : '—'],
        ];
        foreach ($statsGrid as [$ico, $lbl, $val]): ?>
        <div class="av-stat-item">
          <i class="<?= $ico ?> av-stat-ico"></i>
          <div>
            <div class="av-stat-lbl"><?= $lbl ?></div>
            <div class="av-stat-val"><?= $h((string)$val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Buyer -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-user-check" style="color:#4ade80;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Buyer</span>
      </div>
      <div class="av-buyer-row">
        <div class="av-buyer-avi">
          <?php if ($buyerIcon): ?><img src="<?= $h($buyerIcon) ?>" alt=""><?php else: ?><?= strtoupper(substr($buyerName, 0, 1)) ?><?php endif; ?>
        </div>
        <div>
          <div style="font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);"><?= $h($buyerName) ?></div>
        </div>
        <a href="#chat_messages" onclick="document.getElementById('chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'}); return false;"
           class="av-btn-success" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-solid fa-comments"></i> Chat
        </a>
      </div>

      <!-- Delivery status (DG counterpart of the item friendship box) -->
      <?php if ($deliveryNote !== ''): ?>
        <div class="av-delivery av-delivery--done mx-2 mb-2">
          <div style="font-weight:800;"><i class="fa-solid fa-box-open me-1"></i> Delivered</div>
          <div style="font-size:.8rem;margin-top:6px;color:rgba(255,255,255,.85);"><?= $h($deliveryNote) ?></div>
          <?php if ($deliveredAtRaw): ?>
            <div style="font-size:.75rem;margin-top:6px;opacity:.8;">Delivered at: <?= date('d.m.Y H:i', strtotime($deliveredAtRaw)) ?></div>
          <?php endif; ?>
        </div>
      <?php elseif ($canDeliver): ?>
        <div class="av-delivery av-delivery--waiting mx-2 mb-2">
          <i class="fa-solid fa-clock me-1"></i> Paid — the buyer is waiting for delivery.
          <textarea id="dgDeliveryNote" placeholder="Paste the code, account data, download link, or delivery instructions here..."></textarea>
          <div style="margin-top:8px;">
            <button type="button" class="av-btn-success" id="dgDeliverBtn" style="font-size:.75rem;padding:5px 12px;">
              <i class="fa-solid fa-box-open"></i> Mark as delivered
            </button>
          </div>
        </div>
      <?php else: ?>
        <div class="av-delivery av-delivery--waiting mx-2 mb-2">
          <i class="fa-solid fa-clock me-1"></i> Delivery is not available for this status.
        </div>
      <?php endif; ?>
    </div>

    <!-- Buyer Review -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(251,191,36,.12);border-color:rgba(251,191,36,.22);"><i class="fa-solid fa-star" style="color:#fbbf24;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Buyer Review</span>
      </div>
      <div class="av-sc-body">
        <?php if ($hasReview): ?>
          <div class="av-review-summary">
            <div class="av-review-score"><?= (int)$reviewRating ?>.0</div>
            <div style="min-width:0;flex:1;">
              <div class="av-review-stars" aria-label="<?= (int)$reviewRating ?> out of 5 stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fa-solid fa-star <?= $i <= $reviewRating ? 'is-on' : '' ?>"></i>
                <?php endfor; ?>
              </div>
              <div style="font-size:.8rem;color:rgba(255,255,255,.46);font-weight:700;">
                Reviewed by <?= $h($buyerName) ?><?= $reviewCreatedAt ? ' · ' . $h($reviewCreatedAt) : '' ?>
              </div>
            </div>
          </div>
          <?php if ($reviewComment !== ''): ?>
            <div class="av-review-comment"><?= $h($reviewComment) ?></div>
          <?php endif; ?>
        <?php else: ?>
          <div class="av-review-empty">No buyer review has been submitted for this order yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Product Details -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-gift" style="color:#a5b4fc;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Product Details</span>
      </div>
      <div class="av-stat-grid" style="grid-template-columns:1fr;">
        <?php
        $itemDetails = [
            ['fa-solid fa-hashtag',   'Product ID', $itemId ?: '—'],
            ['fa-solid fa-tag',       'Brand',      $brand ?: '—'],
            ['fa-solid fa-layer-group', 'Category', $category ?: '—'],
            ['fa-solid fa-credit-card', 'Paid',     $paidAtRaw ? date('d.m.Y H:i', strtotime($paidAtRaw)) : 'No'],
            ['fa-solid fa-box-open',  'Delivered',  $deliveredAtRaw ? date('d.m.Y H:i', strtotime($deliveredAtRaw)) : 'No'],
            ['fa-solid fa-calendar',  'Created',    $createdAtRaw ? date('d.m.Y H:i', strtotime($createdAtRaw)) : '—'],
        ];
        foreach ($itemDetails as [$ico, $lbl, $val]): ?>
        <div class="av-stat-item" style="border-right:0;">
          <i class="<?= $ico ?> av-stat-ico"></i>
          <div style="display:flex;justify-content:space-between;width:100%;gap:8px;">
            <div class="av-stat-lbl"><?= $lbl ?></div>
            <div class="av-stat-val" style="text-align:right;"><?= $h((string)$val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

</div>
</div>


<!-- Image Lightbox -->
<div class="modal fade" id="lbImgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:90vw;">
    <div class="modal-content" style="background:rgba(0,0,0,.85);border:none;">
      <div class="modal-body text-center p-2">
        <img src="" id="lbImgModalImg" alt="" style="max-width:100%;max-height:80vh;border-radius:.5rem;">
      </div>
      <div class="modal-footer justify-content-center py-2 border-0">
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?= $this->start('scripts') ?>
<script>
(function () {
  'use strict';
  const AJAX_URL    = (typeof ajax_url !== 'undefined') ? ajax_url : '<?= AJAX_URL ?>';
  const PURCHASE_ID = <?= $id ?>;
  const SELLER_ID   = <?= (int)($seller_data['id'] ?? 0) ?>;
  const user_type   = 'seller';
  const user_id     = SELLER_ID;
  const BUYER_NAME  = <?= json_encode($h($buyerName)) ?>;
  const SELLER_AVATAR = '<?= ICON_URL ?>/03ce541a1f4bf8b06c924439ffcc8173.png';
  const BUYER_AVATAR  = '<?= ICON_URL ?>/8515d2c8c74a3f9bae054026f6549d91.png';

  function toast(type, title, msg){ if (typeof create_toast === 'function') create_toast(type, title, msg); else alert(msg || title); }

  /* ── CHAT (same renderer as the item order view) ── */
  let msg_none = false;
  let chat_sig = '';
  let initial_load = true;
  let chatLoadRunning = false;
  let latestMessages = {};
  let lastLoadAt = 0;
  let peerUnreadPending = false;

  const chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
  function message_sound(){ try{ chat_notif.volume=0.6; chat_notif.play(); }catch(e){} }

  function decodeHtmlEntities(str){
    const t=document.createElement('textarea');
    t.innerHTML=str??'';
    return t.value.replace(/\\r\\n|\\n|\\r/g,'\n').replace(/\r\n|\r|\n/g,'<br>');
  }
  function formatTime(ts){ if(!ts) return ''; const d=new Date((parseInt(ts,10)||0)*1000); const p=n=>String(n).padStart(2,'0'); return `${p(d.getDate())}.${p(d.getMonth()+1)}.${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`; }
  function getRoleBadge(sender){ if(sender==='seller') return {cls:'lb-badge--seller',label:'Seller'}; if(sender==='admin') return {cls:'lb-badge--admin',label:'Admin'}; if(sender==='system') return {cls:'lb-badge--system',label:'System'}; return {cls:'lb-badge--client',label:'Buyer'}; }
  function getFallbackAvatar(sender){ return (sender==='seller'||sender==='admin') ? SELLER_AVATAR : BUYER_AVATAR; }
  function renderTicks(m){ const seen=Number(m.seen_by_client??0)===1; return ` <span class="lb-msg__ticks ${seen?'text-primary':'text-muted'}" title="${seen?'Read':'Delivered'}"><i class="fa-solid fa-check-double"></i></span>`; }

  function load_message(id,m,isGrouped){
    const time=formatTime(m.time), content=decodeHtmlEntities(m.content);
    if(m.sender==='system') return `<div style="text-align:center;margin:.5rem 0;"><span style="display:inline-block;background:rgba(255,255,255,.06);border-radius:999px;padding:.25rem .85rem;font-size:.76rem;opacity:.6;">${content}</span><div style="font-size:.68rem;opacity:.35;margin-top:.15rem;">${time}</div></div>`;
    const isMe=m.sender===user_type&&(!m.sender_id||String(m.sender_id)===String(user_id));
    const aCls=isMe?'lb-msg--end':'lb-msg--start', hCls=isMe?'lb-msg__head lb-msg__head--end':'lb-msg__head';
    const badge=getRoleBadge(m.sender), avatar=m.sender_icon?m.sender_icon:getFallbackAvatar(m.sender), name=isMe?'You':(m.sender_name||BUYER_NAME);
    let html=`<div class="lb-msg ${aCls}">`;
    if(!isGrouped) html+=`<div class="${hCls}"><img class="lb-msg__avatar" src="${avatar}" alt=""><div class="lb-msg__name">${name} <span class="lb-badge ${badge.cls}">${badge.label}</span></div></div>`;
    html+=`<div class="lb-msg__bubble"><div class="lb-msg__content">${content}</div></div>`;
    html+=`<div class="lb-msg__stamp">${time}${isMe?renderTicks(m):''}</div></div>`;
    return html;
  }

  function update_scroll(){ const el=document.getElementById('chat_messages'); if(el) el.scrollTop=el.scrollHeight; }
  function chatIsVisible(){ if(document.visibilityState!=='visible'||!document.hasFocus()) return false; const el=document.getElementById('chat_messages'); if(!el) return false; const r=el.getBoundingClientRect(); return r.bottom>0&&r.top<window.innerHeight; }

  function renderMessages(raw){
    const list={};
    $.each(raw||{},function(k,v){ if(v&&!v.deleted&&v.type!=='deleted'){ v.sender=String(v.sender||v.sender_type||v.from||'').toLowerCase(); list[k]=v; } });
    latestMessages=list;
    peerUnreadPending=Object.values(list).some(function(v){ return v.sender==='client'&&Number(v.seen_by_seller??0)!==1; });
    const cnt=Object.keys(list).length, sig=JSON.stringify(list);
    if(cnt>0){
      msg_none=false;
      if(sig!==chat_sig){ chat_sig=sig; let html='',ls='',lid=0; $.each(list,function(k,v){ const grouped=v.sender===ls&&String(v.sender_id)===String(lid); html+=load_message(k,v,grouped); ls=v.sender; lid=v.sender_id; }); $('#chat_messages').html(html); update_scroll(); }
      const keys=Object.keys(list), lm=list[keys[keys.length-1]];
      if(!initial_load&&lm&&lm.sender!==user_type&&Number(lm.notify??0)===0&&document.visibilityState==='visible') message_sound();
      initial_load=false;
    } else if(!msg_none){ $('#chat_messages').html('<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet. Start the conversation!</span></div>'); msg_none=true; }
  }

  function load_messages(force=false,markSeen=true){
    const now=Date.now();
    if(chatLoadRunning) return;
    if(!force&&now-lastLoadAt<500) return;
    chatLoadRunning=true; lastLoadAt=now;
    $.post(AJAX_URL,{action:'seller_dg_chat_load',purchase_id:PURCHASE_ID,viewer_role:'seller',mark_seen:markSeen&&chatIsVisible()?1:0})
      .done(function(resp){ let data; try{data=typeof resp==='string'?JSON.parse(resp):resp;}catch(e){return;} if(data&&data.success!==false) renderMessages(data.messages||[]); })
      .always(function(){chatLoadRunning=false;});
  }

  // Image attach + paste
  (function(){
    const uploadBtn=document.getElementById('lbChatUploadBtn');
    const fileInput=document.getElementById('lbChatImageInput');
    const previewW =document.getElementById('lbChatImagePreviewWrap');
    const previewI =document.getElementById('lbChatImagePreview');
    const removeBtn=document.getElementById('lbChatImageRemove');
    let pUrl=null;
    function showPreview(f){ if(!f||!f.type.startsWith('image/')) return; if(pUrl) URL.revokeObjectURL(pUrl); pUrl=URL.createObjectURL(f); if(previewI) previewI.src=pUrl; if(previewW) previewW.classList.remove('d-none'); }
    function clearPreview(){ if(pUrl){URL.revokeObjectURL(pUrl);pUrl=null;} if(fileInput) fileInput.value=''; if(previewI) previewI.src=''; if(previewW) previewW.classList.add('d-none'); }
    if(uploadBtn&&fileInput){ uploadBtn.addEventListener('click',()=>fileInput.click()); fileInput.addEventListener('change',()=>showPreview(fileInput.files&&fileInput.files[0])); }
    if(removeBtn) removeBtn.addEventListener('click',clearPreview);
    document.addEventListener('paste',function(e){
      const form=document.getElementById('lbChatForm'); if(!form||!fileInput) return;
      if(!form.contains(document.activeElement)) return;
      for(const it of (e.clipboardData?.items||[])){
        if(it.kind==='file'&&it.type.startsWith('image/')){
          const blob=it.getAsFile(); if(!blob) continue;
          const file=new File([blob],'pasted.png',{type:blob.type});
          const dt=new DataTransfer(); dt.items.add(file); fileInput.files=dt.files;
          showPreview(file); e.preventDefault(); break;
        }
      }
    });
  })();

  // Form submit
  $('#lbChatForm').on('submit',function(e){
    e.preventDefault();
    const msgInput=document.getElementById('lbChatMessageInput');
    const fileInput=document.getElementById('lbChatImageInput');
    const hasFile=fileInput&&fileInput.files&&fileInput.files.length>0;
    if(!(msgInput&&msgInput.value.trim())&&!hasFile){ toast('warning','Empty message','Please type a message or attach an image.'); return; }
    const $btn=$('#lbChatSendBtn'); $btn.prop('disabled',true).find('.indicator-label').addClass('d-none'); $btn.find('.indicator-progress').removeClass('d-none');
    $.ajax({url:AJAX_URL,method:'POST',data:new FormData(this),processData:false,contentType:false})
      .done((resp)=>{
        let r=resp; try{if(typeof resp==='string')r=JSON.parse(resp);}catch(e){}
        if(r&&r.success===false){ toast('danger','Error',(r.sendToast&&r.sendToast.message)||r.message||'Could not send message.'); return; }
        msgInput.value=''; fileInput.value=''; document.getElementById('lbChatImagePreviewWrap').classList.add('d-none');
        if(r&&r.messages) renderMessages(r.messages); else { chat_sig=''; load_messages(true,true); }
      })
      .fail(()=>{ toast('danger','Error','Could not send message.'); })
      .always(()=>{ $btn.prop('disabled',false).find('.indicator-label').removeClass('d-none'); $btn.find('.indicator-progress').addClass('d-none'); });
  });

  // Gallery lightbox
  document.querySelectorAll('.av-gallery-tile[data-zoom]').forEach(tile=>{
    tile.addEventListener('click',()=>{
      const src=tile.getAttribute('data-zoom'); if(!src) return;
      const img=document.getElementById('lbImgModalImg'); if(img) img.src=src;
      const modal=document.getElementById('lbImgModal'); if(modal&&window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    });
  });
  const lbMod=document.getElementById('lbImgModal');
  if(lbMod) lbMod.addEventListener('hidden.bs.modal',()=>{ const i=document.getElementById('lbImgModalImg'); if(i) i.src=''; });

  // Chat image lightbox
  document.addEventListener('click',function(e){
    const img=e.target.closest('#chat_messages img'); if(!img) return;
    e.preventDefault(); const modal=document.getElementById('lbImgModal'), mImg=document.getElementById('lbImgModalImg');
    if(!modal||!mImg) return; mImg.src=img.src;
    if(window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
  });

  /* ── Poke client (email + web notification, same flow as item/account orders) ── */
  document.querySelectorAll('.js-seller-poke-client').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      const oldHtml = btn.innerHTML;
      let cooldownStarted = false;
      function startCooldown(seconds) {
        let remaining = Math.max(1, parseInt(seconds, 10) || 300);
        cooldownStarted = true;
        btn.disabled = true;
        function render() {
          const mins = Math.floor(remaining / 60);
          const secs = String(remaining % 60).padStart(2, '0');
          btn.innerHTML = '<i class="fa-solid fa-clock"></i> Poke again in ' + mins + ':' + secs;
          if (remaining-- <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.innerHTML = oldHtml;
          }
        }
        render();
        const timer = setInterval(render, 1000);
      }
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      $.post(AJAX_URL, { action: 'seller_poke_client', ref_type: btn.getAttribute('data-ref-type') || 'digital_good', id: btn.getAttribute('data-id') || PURCHASE_ID }, function(resp){
        let d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
        if (d && d.sendToast) toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
        if (d && d.cooldown_seconds) startCooldown(d.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });

  /* ── Deliver ── */
  const deliverBtn = document.getElementById('dgDeliverBtn');
  if (deliverBtn) {
    deliverBtn.addEventListener('click', function(){
      let note = (document.getElementById('dgDeliveryNote') || {}).value || '';
      note = note.trim();
      if (!note && !confirm('Mark as delivered without delivery note?')) return;
      deliverBtn.disabled = true;
      deliverBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
      $.ajax({
        url: AJAX_URL, method: 'POST', dataType: 'json',
        data: { action: 'seller_dg_mark_delivered', purchase_id: PURCHASE_ID, delivery_note: note }
      }).done(function(r){
        if (r && r.success) {
          toast('success', 'Delivered', (r.sendToast && r.sendToast.message) ? r.sendToast.message : 'Order marked as delivered.');
          setTimeout(function(){ location.reload(); }, 650);
          return;
        }
        deliverBtn.disabled = false;
        deliverBtn.innerHTML = '<i class="fa-solid fa-box-open"></i> Mark as delivered';
        toast('danger', 'Error', (r && r.message) || 'Could not mark delivered.');
      }).fail(function(xhr, textStatus){
        // If PHP updated the order but returned non-JSON because of a warning/notice,
        // jQuery enters fail(parsererror). In that case reload instead of showing a false error.
        if (xhr && xhr.status === 200 && textStatus === 'parsererror') {
          toast('success', 'Delivered', 'Order marked as delivered.');
          setTimeout(function(){ location.reload(); }, 650);
          return;
        }
        deliverBtn.disabled = false;
        deliverBtn.innerHTML = '<i class="fa-solid fa-box-open"></i> Mark as delivered';
        toast('danger', 'Error', 'Could not mark delivered.');
      });
    });
  }

  /* ── Realtime: same shape as the item chat, on the dg_chat_update event ── */
  function unwrapRealtimePayload(raw){
    let data=raw||{};
    for(let i=0;i<3;i++){
      if(data&&data.data&&typeof data.data==='object') data=data.data;
      else if(data&&data.payload&&typeof data.payload==='object') data=data.payload;
      else break;
    }
    return data||{};
  }

  function handleDgRealtime(raw){
    const data=unwrapRealtimePayload(raw);
    const matches=String(data.order_id||'')==='dgpurch_'+String(PURCHASE_ID)||String(data.purchase_id||'')===String(PURCHASE_ID);
    if(!matches) return;
    if(data.messages) renderMessages(data.messages);
    if(chatIsVisible()) setTimeout(function(){load_messages(true,true);},60);
  }

  function bindDgChatSocket(){
    const sock=window.lbSocket||window.socket||null;
    if(!sock) return;
    if(sock.__lbSellerDgHandler){ try{sock.off('dg_chat_update',sock.__lbSellerDgHandler);}catch(e){} }
    sock.__lbSellerDgHandler=handleDgRealtime;
    const joinRooms=function(){ try{sock.emit('join','sellers');}catch(e){} };
    joinRooms();
    try{sock.on('connect',joinRooms);}catch(e){}
    try{sock.on('dg_chat_update',handleDgRealtime);}catch(e){}
  }

  $(document).ready(function(){
    load_messages(true,true);
    bindDgChatSocket();
    setTimeout(bindDgChatSocket,350);
    setTimeout(bindDgChatSocket,1200);

    document.addEventListener('visibilitychange',function(){ if(document.visibilityState==='visible'&&peerUnreadPending&&chatIsVisible()) load_messages(true,true); });
    window.addEventListener('focus',function(){ if(peerUnreadPending&&chatIsVisible()) load_messages(true,true); });

    window.lbSellerDgChatFallback&&clearInterval(window.lbSellerDgChatFallback);
    window.lbSellerDgChatFallback=setInterval(function(){
      if(document.visibilityState!=='visible') return;
      if(window.lbRealtimeConnected===true) return;
      load_messages(false,true);
    },15000);
  });
})();
</script>
<?= $this->stop() ?>
