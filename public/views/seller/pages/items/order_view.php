<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
require_once dirname(__DIR__) . '/_orders_shared.php';
$order  = $purchase ?? $order ?? [];
$buyer  = $buyer ?? null;
$details = $details ?? [];
$remaining = isset($remaining) ? (int)$remaining : null;

if (!function_exists('siov_get')) {
    function siov_get($row, $keys, $default = null) {
        foreach ((array)$keys as $key) {
            if (is_array($row) && array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') return $row[$key];
        }
        return $default;
    }
}
if (!function_exists('siov_money')) {
    function siov_money($value) {
        if ($value === null || $value === '') return 0.0;
        return round((float)$value / 100, 2);
    }
}
if (!function_exists('siov_sym')) {
    function siov_sym(): string {
        if (function_exists('util_format_currency_display'))
            return util_format_currency_display('EUR');
        return '€';
    }
}

$id        = (int)siov_get($order, ['id','purchase_id','order_id'], 0);
$itemId    = (int)siov_get($order, ['item_id'], 0);
$itemTitle = (string)siov_get($order, ['item_title','title','product_title','name'], 'Untitled Item');
$qty       = (int)siov_get($order, ['quantity','qty'], 1);
$unit      = siov_money(siov_get($order, ['unit_price','price_each'], 0));
$total     = siov_money(siov_get($order, ['price','total_price','order_total'], 0));
$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$payout    = siov_get($order, ['seller_payout','seller_amount','payout','earnings'], null);
$payout    = $payout !== null && $payout !== '' ? siov_money($payout) : round($total * (1 - ($effective_fee / 100)), 2);
$statusRaw = strtolower(trim((string)siov_get($order, ['status','order_status','delivery_status','state'], 'pending')));
$status    = in_array($statusRaw, ['completed','delivered','success','fulfilled'], true) ? 'Delivered'
           : (in_array($statusRaw, ['cancelled','canceled','failed','refunded','chargeback'], true) ? 'Cancelled' : 'Pending');
$server    = strtoupper((string)siov_get($order, ['server','region'], ''));
$type      = (string)siov_get($order, ['item_type','type','product_type'], 'Item');
$typeLabel = ucwords(str_replace(['-','_'], ' ', $type));
$buyerName   = (string)siov_get($buyer ?: $order, ['username','buyer_username','buyer_name','client_username','customer_name'], 'Guest');
$buyerEmail  = (string)siov_get($buyer ?: $order, ['email','buyer_email','customer_email'], '');
// clients.icon is stored inconsistently (full URL / path / bare filename / empty),
// so normalize it the same way the order lists do.
$buyerAvatar = sol_client_icon(siov_get($buyer ?: $order, ['icon','avatar','buyer_avatar','customer_avatar'], ''));
$orderCode   = (string)siov_get($order, ['order_code','invoice_id','payment_id','txn_id'], '');
$createdAt   = (string)siov_get($order, ['created_at','date_created','ordered_at','paid_at'], '');
$updatedAt   = (string)siov_get($order, ['updated_at','date_updated'], '');
$friendshipConfirmedAt = (string)siov_get($order, ['friendship_confirmed_at'], '');
$friendshipReadyAt     = (string)siov_get($order, ['friendship_ready_at'], '');
$images = siov_get($order, ['images','item_images'], []);
if (is_string($images)) { $tmp = json_decode($images, true); $images = is_array($tmp) ? $tmp : []; }
$images = is_array($images) ? $images : [];

// Type image
$typeImgStems = ['skins'=>'skins-item','chests-keys'=>'chest-item','orbs'=>'orbs-item',
                 'capsules'=>'capsules-item','event-pass'=>'event-pass-item','bundles'=>'bundle-item',
                 'tft-item'=>'tft-item','mystery-gift'=>null];
$typeMap = ['skins'=>'Skins','skin'=>'Skins','chests-keys'=>'Chests & Keys','chest-key'=>'Chests & Keys',
            'chest'=>'Chests & Keys','orbs'=>'Orbs','orb'=>'Orbs','capsules'=>'Capsules','capsule'=>'Capsules',
            'event-pass'=>'Event Pass','pass'=>'Event Pass','bundles'=>'Bundles','bundle'=>'Bundles',
            'tft-item'=>'TFT Item','tft'=>'TFT Item','mystery-gift'=>'Mystery Gift','gifting'=>'Mystery Gift'];
$typeLabelFull = $typeMap[strtolower(trim($type))] ?? $typeLabel;
$typeKey = trim(preg_replace('/[^a-z0-9]+/','-',strtolower($typeLabelFull)),'-');
$typeImgUrl = (array_key_exists($typeKey,$typeImgStems) && $typeImgStems[$typeKey])
    ? rtrim(ASSET_URL,'/').'/website/images/items/'.$typeImgStems[$typeKey].'.webp' : null;
$typeFaMap = ['skins'=>'fa-shirt','chests-keys'=>'fa-key','orbs'=>'fa-circle-nodes','capsules'=>'fa-capsules',
              'event-pass'=>'fa-ticket','bundles'=>'fa-gift','tft-item'=>'fa-chess-board','mystery-gift'=>'fa-sparkles'];
$typeFa = 'fa-solid '.($typeFaMap[$typeKey] ?? 'fa-tag');

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$badgeCls = $status === 'Delivered' ? 'av-status--active'
          : ($status === 'Cancelled' ? 'av-status--sold' : 'av-status--unlisted');
$statusIcon = $status === 'Delivered' ? 'fa-check' : ($status === 'Cancelled' ? 'fa-xmark' : 'fa-clock');
$sym = siov_sym();
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Item Order #' . $id . ' | LoLBoost.gg']]) ?>

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

/* Friendship */
.av-friendship { margin:12px 16px; padding:12px 14px; border-radius:12px; font-size:.82rem; }
.av-friendship--waiting   { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.5); }
.av-friendship--confirmed { background:rgba(74,222,128,.07);  border:1px solid rgba(74,222,128,.18); color:#4ade80; }
.av-friendship--timer { display:inline-flex; align-items:center; gap:.45rem; padding:8px 12px; border-radius:12px; background:rgba(250,204,21,.12); border:1px solid rgba(250,204,21,.30); color:#facc15; font-size:.8rem; font-weight:900; margin-top:8px; }

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
<?= $this->end() ?>

<div class="seller-item-order-view">

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
        <?php if ($typeImgUrl): ?>
          <img src="<?= $h($typeImgUrl) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
        <?php else: ?>
          <i class="<?= $h($typeFa) ?>" style="font-size:1.4rem;color:#a5b4fc;"></i>
        <?php endif; ?>
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 class="av-title"><?= $h($itemTitle) ?></h1>
          <span class="av-status <?= $badgeCls ?>"><i class="fa-solid <?= $statusIcon ?>" style="font-size:.55rem;"></i> <?= $h($status) ?></span>
        </div>
        <div class="av-sub">
          <?php if ($server): ?><span style="text-transform:uppercase;font-weight:700;"><?= $h($server) ?></span><span>·</span><?php endif; ?>
          <span>#<?= $id ?></span>
          <?php if ($orderCode): ?><span>·</span><span>Invoice #<?= $h($orderCode) ?></span><?php endif; ?>
          <span>·</span>
          <span><?= $createdAt ? date('d.m.Y', strtotime($createdAt)) : '—' ?></span>
        </div>
      </div>
    </div>

    <div class="av-actions">
      <a href="<?= BASE_URL ?>/seller-area/item-orders" class="av-btn-ghost">
        <i class="fa-solid fa-arrow-left"></i> Back
      </a>
      <?php if ($itemId > 0): ?>
      <a class="av-btn-primary" href="<?= BASE_URL ?>/seller-area/item/<?= $itemId ?>">
        <i class="fa-solid fa-box-open"></i> Open Item
      </a>
      <?php endif; ?>
      <?php if (empty($friendshipConfirmedAt)): ?>
      <button type="button" class="av-btn-success" id="confirmFriendshipBtn">
        <i class="fa-solid fa-user-check"></i> Confirm Friendship
      </button>
      <?php endif; ?>
      <?php if (!empty($purchase['client_id'])): ?>
      <?= sol_poke_client_button($id, 'item') ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><i class="<?= $h($typeFa) ?>" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($typeLabelFull) ?></strong></span>
    <?php if ($server): ?><span class="av-meta-pill"><i class="fa-solid fa-globe" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($server) ?></strong></span><?php endif; ?>
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
        <?php if ($buyer): ?>
        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
          <span class="av-chip-avi"><?= strtoupper(substr($buyerName,0,1)) ?><?php if ($buyerAvatar): ?><img src="<?= $h($buyerAvatar) ?>" alt="" onerror="this.remove()"><?php endif; ?></span>
          <?= $h($buyerName) ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="card-body chat-bg" id="chat_messages"></div>

      <div class="card-footer">
        <form class="row gx-2" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action"      value="seller_item_chat_send">
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
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-images me-2" style="color:#9f8cff;"></i>Item Gallery <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($images) ?> image<?= count($images)!==1?'s':'' ?></span></h4>
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


  <!-- RIGHT: Overview + Buyer + Friendship -->
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
            ['fa-solid fa-hashtag',     'Order ID',    '#'.$id,                          null],
            ['fa-solid fa-layer-group', 'Quantity',    $qty,                              null],
            ['fa-solid fa-tag',         'Unit Price',  $sym.number_format($unit,2),       null],
            ['fa-solid fa-gift',        'Type',        $typeLabelFull,                    null],
            ['fa-solid fa-globe',       'Server',      $server ?: '—',                    null],
            ['fa-solid fa-calendar',    'Created',     $createdAt ? date('d.m.Y H:i', strtotime($createdAt)) : '—', null],
        ];
        foreach ($statsGrid as [$ico, $lbl, $val, $clr]): ?>
        <div class="av-stat-item">
          <i class="<?= $ico ?> av-stat-ico" <?= $clr ? 'style="color:'.$clr.';"' : '' ?>></i>
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
          <?php if ($buyerAvatar): ?><img src="<?= $h($buyerAvatar) ?>" alt=""><?php else: ?><?= strtoupper(substr($buyerName,0,1)) ?><?php endif; ?>
        </div>
        <div>
          <div style="font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);"><?= $h($buyerName) ?></div>
        </div>
        <a href="#chat_messages" onclick="document.getElementById('chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'}); return false;"
           class="av-btn-success" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-solid fa-comments"></i> Chat
        </a>
      </div>

      <!-- Friendship status -->
      <?php if (!empty($friendshipConfirmedAt)): ?>
        <div class="av-friendship av-friendship--confirmed mx-2 mb-2">
          <div style="font-weight:800;"><i class="fa-solid fa-user-check me-1"></i> Friendship confirmed</div>
          <?php if (!empty($friendshipReadyAt)): ?>
            <div style="font-size:.75rem;margin-top:4px;opacity:.8;">Ready at: <?= date('d.m.Y H:i', strtotime($friendshipReadyAt)) ?></div>
          <?php endif; ?>
          <?php if ($remaining !== null && $remaining > 0): ?>
            <div class="av-friendship--timer" id="friendshipTimer" data-seconds="<?= $remaining ?>">
              <i class="fa-solid fa-clock"></i> <span id="friendshipTimerText"></span>
            </div>
          <?php else: ?>
            <div style="font-size:.75rem;margin-top:4px;color:#4ade80;font-weight:700;"><i class="fa-solid fa-gift me-1"></i> Gifting is now available!</div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="av-friendship av-friendship--waiting mx-2 mb-2">
          <i class="fa-solid fa-clock me-1"></i> Waiting for friendship to be confirmed.
          <?php if (empty($friendshipConfirmedAt)): ?>
          <div style="margin-top:8px;">
            <button type="button" class="av-btn-success" id="confirmFriendshipBtn2" style="font-size:.75rem;padding:5px 12px;">
              <i class="fa-solid fa-user-check"></i> Confirm Now
            </button>
          </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Item Details -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-gift" style="color:#a5b4fc;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Item Details</span>
      </div>
      <div class="av-stat-grid" style="grid-template-columns:1fr;">
        <?php
        $itemDetails = [
            ['fa-solid fa-hashtag',     'Item ID',           $itemId ?: '—'],
            ['fa-solid fa-tag',         'Type',              $typeLabelFull],
            ['fa-solid fa-globe',       'Server',            $server ?: '—'],
            ['fa-solid fa-clock',       'Friendship ready',  $friendshipReadyAt ? date('d.m.Y H:i', strtotime($friendshipReadyAt)) : '—'],
            ['fa-solid fa-user-check',  'Confirmed',         $friendshipConfirmedAt ? date('d.m.Y H:i', strtotime($friendshipConfirmedAt)) : 'No'],
            ['fa-solid fa-calendar',    'Updated',           $updatedAt ? date('d.m.Y H:i', strtotime($updatedAt)) : '—'],
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
  const AJAX_URL   = '<?= AJAX_URL ?>';
  const PURCHASE_ID = <?= $id ?>;
  const SELLER_ID  = <?= (int)($seller_data['id'] ?? 0) ?>;
  const user_type  = 'seller';
  const user_id    = SELLER_ID;

  document.querySelectorAll('.js-seller-poke-client').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      const oldHtml = btn.innerHTML;
      let cooldownStarted = false;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      function startCooldown(seconds) {
        let remaining = Math.max(1, parseInt(seconds, 10) || 300);
        cooldownStarted = true;
        function render() {
          const mins = Math.floor(remaining / 60);
          const secs = String(remaining % 60).padStart(2, '0');
          btn.innerHTML = '<i class="fa-solid fa-clock"></i> Poke again in ' + mins + ':' + secs;
          if (remaining-- <= 0) { clearInterval(timer); btn.disabled = false; btn.innerHTML = oldHtml; }
        }
        render();
        const timer = setInterval(render, 1000);
      }
      $.post(AJAX_URL, { action:'seller_poke_client', ref_type:'item', id:btn.dataset.id || PURCHASE_ID }, function(resp){
        let data = resp; try { if (typeof resp === 'string') data = JSON.parse(resp); } catch(e) {}
        if (data && data.sendToast && typeof create_toast === 'function') create_toast(data.sendToast.type || 'primary', data.sendToast.title || 'Notice', data.sendToast.message || 'Done');
        if (data && data.cooldown_seconds) startCooldown(data.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });
  const SELLER_NAME = <?= json_encode($h($seller_data['username'] ?? 'You')) ?>;
  const BUYER_NAME  = <?= json_encode($h($buyerName)) ?>;
  const SELLER_AVATAR = '<?= ICON_URL ?>/03ce541a1f4bf8b06c924439ffcc8173.png';
  const BUYER_AVATAR  = '<?= ICON_URL ?>/8515d2c8c74a3f9bae054026f6549d91.png';

  /* ── CHAT ── */
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
    const isMe=m.sender===user_type&&String(m.sender_id)===String(user_id);
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
    $.post(AJAX_URL,{action:'item_chat_load',purchase_id:PURCHASE_ID,viewer_role:'seller',mark_seen:markSeen&&chatIsVisible()?1:0})
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
    const $btn=$('#lbChatSendBtn'); $btn.prop('disabled',true).find('.indicator-label').addClass('d-none'); $btn.find('.indicator-progress').removeClass('d-none');
    $.ajax({url:AJAX_URL,method:'POST',data:new FormData(this),processData:false,contentType:false})
      .done((resp)=>{ let r=resp; try{if(typeof resp==='string')r=JSON.parse(resp);}catch(e){} document.getElementById('lbChatMessageInput').value=''; document.getElementById('lbChatImageInput').value=''; document.getElementById('lbChatImagePreviewWrap').classList.add('d-none'); if(r&&Array.isArray(r.messages)) renderMessages(r.messages); })
      .fail(()=>{ if(typeof create_toast==='function') create_toast('danger','Error','Could not send message.'); })
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

  // Confirm friendship (both buttons)
  function confirmFriendship(){
    $.ajax({type:'post',url:AJAX_URL,data:{action:'seller_item_friendship_confirm',purchase_id:PURCHASE_ID},
      success:function(resp){
        let d=resp; try{ if(typeof resp==='string') d=JSON.parse(resp); }catch(e){}
        if(d&&d.sendToast&&typeof create_toast==='function') create_toast(d.sendToast.type||'success',d.sendToast.title||'Done',d.sendToast.message||'');
        if(d&&(d.refreshPage||d.success)) window.location.reload();
      },
      error:function(){ if(typeof create_toast==='function') create_toast('danger','Error','Could not confirm friendship.'); }
    });
  }
  ['confirmFriendshipBtn','confirmFriendshipBtn2'].forEach(id=>{ const btn=document.getElementById(id); if(btn) btn.addEventListener('click',confirmFriendship); });

  // Friendship countdown
  const timer=document.getElementById('friendshipTimer');
  if(timer){
    let rem=parseInt(timer.getAttribute('data-seconds')||'0',10);
    const el=document.getElementById('friendshipTimerText');
    function tick(){ if(!el) return; if(rem<=0){el.textContent='Ready!';return;} const d=Math.floor(rem/86400),h=Math.floor((rem%86400)/3600),m=Math.floor((rem%3600)/60); el.textContent=d+'d '+h+'h '+m+'m remaining'; rem-=60; }
    tick(); setInterval(tick,60000);
  }

  // Item chat realtime, one event and one read path, matching Top Up.
  function unwrapRealtimePayload(raw){
    let data=raw||{};
    for(let i=0;i<3;i++){
      if(data&&data.data&&typeof data.data==='object') data=data.data;
      else if(data&&data.payload&&typeof data.payload==='object') data=data.payload;
      else break;
    }
    return data||{};
  }

  function handleItemRealtime(raw){
    const data=unwrapRealtimePayload(raw);
    const matches=String(data.order_id||'')==='itempurch_'+String(PURCHASE_ID)||String(data.purchase_id||'')===String(PURCHASE_ID);
    if(!matches) return;
    if(Array.isArray(data.messages)) renderMessages(data.messages);
    if(chatIsVisible()) setTimeout(function(){load_messages(true,true);},60);
  }

  function bindItemChatSocket(){
    const sock=window.lbSocket||window.socket||null;
    if(!sock) return;
    if(sock.__lbSellerItemHandler){ try{sock.off('item_chat_update',sock.__lbSellerItemHandler);}catch(e){} }
    sock.__lbSellerItemHandler=handleItemRealtime;
    const joinRooms=function(){ try{sock.emit('join','sellers');}catch(e){} };
    joinRooms();
    try{sock.on('connect',joinRooms);}catch(e){}
    try{sock.on('item_chat_update',handleItemRealtime);}catch(e){}
  }

  $(document).ready(function(){
    load_messages(true,true);
    bindItemChatSocket();
    setTimeout(bindItemChatSocket,350);
    setTimeout(bindItemChatSocket,1200);

    document.addEventListener('visibilitychange',function(){ if(document.visibilityState==='visible'&&peerUnreadPending&&chatIsVisible()) load_messages(true,true); });
    window.addEventListener('focus',function(){ if(peerUnreadPending&&chatIsVisible()) load_messages(true,true); });

    window.lbSellerItemChatFallback&&clearInterval(window.lbSellerItemChatFallback);
    window.lbSellerItemChatFallback=setInterval(function(){
      if(document.visibilityState!=='visible') return;
      if(window.lbRealtimeConnected===true) return;
      load_messages(false,true);
    },15000);
  });
})();
</script>
<?= $this->end() ?>
