<?php
$purchase_id = (int)($purchase['id'] ?? 0);

if (!function_exists('aiov_get')) {
    function aiov_get($row, $keys, $default = null) {
        foreach ((array)$keys as $key) {
            if (is_array($row) && array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') return $row[$key];
        }
        return $default;
    }
}
if (!function_exists('aiov_money')) {
    function aiov_money($value) {
        if ($value === null || $value === '') return 0.0;
        return round((float)$value / 100, 2);
    }
}

$purchase  = $purchase ?? [];
$details   = $details  ?? [];
$seller    = $seller   ?? null;
$buyer     = $buyer    ?? null;
$remaining = isset($remaining) ? (int)$remaining : null;

$id            = (int)aiov_get($purchase, ['id','purchase_id','order_id'], 0);
$itemId        = (int)aiov_get($purchase, ['item_id'], 0);
$itemTitle     = (string)aiov_get($purchase, ['item_title','title','product_title'], 'Untitled Item');
$qty           = (int)aiov_get($purchase, ['quantity','qty'], 1);
$total         = aiov_money(aiov_get($purchase, ['price','total_price','order_total'], 0));
$unit          = aiov_money(aiov_get($purchase, ['unit_price','price_each'], $purchase['price'] ?? 0));
$statusRaw     = strtolower(trim((string)aiov_get($purchase, ['status','order_status','delivery_status','state'], 'pending')));
$status        = in_array($statusRaw, ['completed','delivered','success','fulfilled'], true) ? 'Delivered'
               : (in_array($statusRaw, ['cancelled','canceled','failed','refunded','chargeback'], true) ? 'Cancelled' : 'Pending');
$server        = strtoupper((string)aiov_get($purchase, ['server','region'], ''));
$type          = (string)aiov_get($purchase, ['item_type','type','product_type'], 'Item');
$orderCode     = (string)aiov_get($purchase, ['order_code','invoice_id','payment_id','txn_id'], '');
$createdAt     = (string)aiov_get($purchase, ['created_at','date_created','ordered_at','paid_at'], '');
$updatedAt     = (string)aiov_get($purchase, ['updated_at','date_updated'], '');
$friendshipConfirmedAt = (string)aiov_get($purchase, ['friendship_confirmed_at'], '');
$friendshipReadyAt     = (string)aiov_get($purchase, ['friendship_ready_at'], '');

$typeMap = ['skins'=>'Skins','skin'=>'Skins','chests-keys'=>'Chests & Keys','chest-key'=>'Chests & Keys',
            'chest'=>'Chests & Keys','orbs'=>'Orbs','orb'=>'Orbs','capsules'=>'Capsules','capsule'=>'Capsules',
            'event-pass'=>'Event Pass','pass'=>'Event Pass','bundles'=>'Bundles','bundle'=>'Bundles',
            'tft-item'=>'TFT Item','tft'=>'TFT Item','mystery-gift'=>'Mystery Gift'];
$typeLabel     = ucwords(str_replace(['-','_'], ' ', $type));
$typeLabelFull = $typeMap[strtolower(trim($type))] ?? $typeLabel;
$typeKey       = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($typeLabelFull)), '-');
$typeImgStems  = ['skins'=>'skins-item','chests-keys'=>'chest-item','orbs'=>'orbs-item',
                  'capsules'=>'capsules-item','event-pass'=>'event-pass-item','bundles'=>'bundle-item',
                  'tft-item'=>'tft-item'];
$typeImgUrl    = (array_key_exists($typeKey,$typeImgStems) && $typeImgStems[$typeKey])
    ? rtrim(ASSET_URL,'/').'/website/images/items/'.$typeImgStems[$typeKey].'.webp' : null;
$typeFaMap     = ['skins'=>'fa-shirt','chests-keys'=>'fa-key','orbs'=>'fa-circle-nodes','capsules'=>'fa-capsules',
                  'event-pass'=>'fa-ticket','bundles'=>'fa-gift','tft-item'=>'fa-chess-board','mystery-gift'=>'fa-sparkles'];
$typeFa        = 'fa-solid '.($typeFaMap[$typeKey] ?? 'fa-tag');

$images = aiov_get($purchase, ['images','item_images'], []);
if (is_string($images)) { $tmp = json_decode($images, true); $images = is_array($tmp) ? $tmp : []; }
$images = is_array($images) ? $images : [];

$sellerName    = (string)aiov_get($seller ?? $purchase, ['username','seller_username'], 'Seller');
$sellerId      = (int)aiov_get($seller ?? $purchase, ['id','seller_id'], 0);
$buyerName     = (string)aiov_get($buyer  ?? $purchase, ['username','client_username','buyer_username'], 'Client');
$buyerId       = (int)aiov_get($buyer  ?? $purchase, ['id','client_id','buyer_id'], 0);
$buyerAvatar   = (string)aiov_get($buyer  ?? $purchase, ['icon','avatar'], '');

$riotId = trim((string)($details['riot_game_name'] ?? '') . (!empty($details['riot_tagline']) ? '#' . $details['riot_tagline'] : ''));
$wantedGift = (string)($details['wanted_gift'] ?? '—');

$badgeCls   = $status === 'Delivered' ? 'av-status--active' : ($status === 'Cancelled' ? 'av-status--sold' : 'av-status--unlisted');
$statusIcon = $status === 'Delivered' ? 'fa-check' : ($status === 'Cancelled' ? 'fa-xmark' : 'fa-clock');

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Item Order #' . $id . ' | Admin Area']]) ?>

<?= $this->start('styles') ?>
<style>
.admin-item-order-view .card { background:var(--bs-card-bg)!important; border:var(--bs-card-border-color) 1px solid!important; border-radius:22px!important; box-shadow:none!important; }
.admin-item-order-view .card::before { display:none!important; }
.admin-item-order-view .order-chat-card { overflow:hidden; }

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
.av-btn-danger { display:inline-flex; align-items:center; gap:.4rem; padding:7px 16px; border-radius:11px; font-size:.83rem; font-weight:800; background:rgba(251,113,133,.14); border:1px solid rgba(251,113,133,.25); color:#fb7185; cursor:pointer; transition:background .12s; }
.av-btn-danger:hover { background:rgba(251,113,133,.22); }
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
.lb-msg--end .lb-msg__bubble { background:rgba(245,158,11,.18); }
.lb-msg__stamp { font-size:.7rem; opacity:.4; margin-top:.2rem; }
.lb-msg--end .lb-msg__stamp { text-align:right; }
.lb-msg__content img { max-width:240px; max-height:200px; border-radius:.5rem; display:block; margin-top:.4rem; cursor:pointer; }
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

/* Stat grid */
.av-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; }
.av-stat-item { display:flex; align-items:center; gap:8px; padding:9px 14px; border-bottom:1px solid rgba(255,255,255,.04); border-right:1px solid rgba(255,255,255,.04); }
.av-stat-item:nth-child(even) { border-right:0; }
.av-stat-item:nth-last-child(-n+2) { border-bottom:0; }
.av-stat-ico { font-size:.65rem; color:rgba(255,255,255,.25); width:14px; flex-shrink:0; }
.av-stat-lbl { font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.04em; line-height:1; }
.av-stat-val { font-size:.8rem; font-weight:800; color:rgba(255,255,255,.82); margin-top:2px; line-height:1.2; }

/* Buyer / Seller rows */
.av-user-row { display:flex; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.05); }
.av-user-row:last-child { border-bottom:0; }
.av-user-avi { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; font-weight:900; flex-shrink:0; overflow:hidden; }
.av-user-avi--green { background:rgba(74,222,128,.15); border:1px solid rgba(74,222,128,.25); color:#4ade80; }
.av-user-avi--purple { background:rgba(109,92,255,.15); border:1px solid rgba(109,92,255,.25); color:#c4b5fd; }
.av-user-avi img { width:100%; height:100%; object-fit:cover; }
.av-user-link { color:#c4b5fd; text-decoration:none; font-weight:800; font-size:.85rem; }
.av-user-link:hover { color:#fff; text-decoration:underline; }
.av-user-sub { font-size:.74rem; color:rgba(255,255,255,.38); margin-top:2px; }

/* Friendship */
.av-friendship { margin:12px 16px; padding:12px 14px; border-radius:12px; font-size:.82rem; }
.av-friendship--waiting   { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.5); }
.av-friendship--confirmed { background:rgba(74,222,128,.07);  border:1px solid rgba(74,222,128,.18); color:#4ade80; }

/* Earnings bar */
.av-ov-earnings { display:flex; align-items:center; justify-content:space-around; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02); }
.av-ov-earn-item { text-align:center; }
.av-ov-earn-label { font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.av-ov-earn-val { font-size:.9rem; font-weight:900; color:rgba(255,255,255,.88); }
.av-ov-earn-sep { font-size:.9rem; color:rgba(255,255,255,.2); font-weight:300; }

/* Admin note/detail fields */
.av-detail-field { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:10px 16px; border-bottom:1px solid rgba(255,255,255,.04); }
.av-detail-field:last-child { border-bottom:0; }
.av-detail-label { font-size:.74rem; color:rgba(255,255,255,.38); font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
.av-detail-value { font-size:.82rem; color:rgba(255,255,255,.82); font-weight:700; text-align:right; word-break:break-word; }

@media(max-width:768px){
  .av-head-body{padding:14px 16px}
  .av-meta-row{padding:10px 16px 12px}
  #chat_messages{min-height:220px;max-height:340px}
  .lb-msg{max-width:88%}
  .av-gallery-grid{grid-template-columns:repeat(2,1fr)}
}
</style>
<?= $this->end() ?>

<div class="admin-item-order-view">

<!-- HEAD CARD -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
        <?php if (!empty($images[0])): ?>
          <img src="<?= $h((string)$images[0]) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
        <?php elseif ($typeImgUrl): ?>
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
          <span>Order #<?= $id ?></span>
          <?php if ($orderCode): ?><span>·</span><span>Invoice #<?= $h($orderCode) ?></span><?php endif; ?>
          <span>·</span>
          <span><?= $createdAt ? date('d.m.Y', strtotime($createdAt)) : '—' ?></span>
        </div>
      </div>
    </div>

    <div class="av-actions">
      <?php if ($buyer): ?>
      <button type="button" class="av-btn-primary js-admin-poke-client" data-ref-type="item" data-id="<?= $id ?>">
        <i class="fa-duotone fa-hand-point-up"></i> Poke Client
      </button>
      <?php endif; ?>
      <a href="<?= ADMN_URL ?>/item-orders" class="av-btn-ghost">
        <i class="fa-solid fa-arrow-left"></i> Back
      </a>
      <?php if ($itemId > 0): ?>
      <a class="av-btn-primary" href="<?= ADMN_URL ?>/item/<?= $itemId ?>">
        <i class="fa-solid fa-box-open"></i> View Item
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><i class="<?= $h($typeFa) ?>" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($typeLabelFull) ?></strong></span>
    <?php if ($server): ?><span class="av-meta-pill"><i class="fa-solid fa-globe" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($server) ?></strong></span><?php endif; ?>
    <span class="av-meta-pill"><i class="fa-solid fa-layer-group" style="color:rgba(255,255,255,.4);"></i> Qty <strong><?= $qty ?></strong></span>
    <span class="av-meta-pill"><i class="fa-solid fa-coins" style="color:rgba(255,255,255,.4);"></i> <strong>€<?= number_format($total, 2) ?></strong></span>
    <?php if (!empty($sellerName)): ?><span class="av-meta-pill"><i class="fa-solid fa-user" style="color:rgba(255,255,255,.4);"></i> Seller: <strong><?= $h($sellerName) ?></strong></span><?php endif; ?>
    <?php if (!empty($buyerName)): ?><span class="av-meta-pill"><i class="fa-solid fa-user-check" style="color:rgba(255,255,255,.4);"></i> Client: <strong><?= $h($buyerName) ?></strong></span><?php endif; ?>
  </div>
</div>


<!-- 2-COLUMN LAYOUT -->
<div class="row g-4 align-items-start">

  <!-- LEFT: Chat + Gallery -->
  <div class="col-12 col-lg-8">

    <!-- Chat -->
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Order Chat
          <span style="font-size:.73rem;color:rgba(255,255,255,.35);font-weight:600;">(Admin View)</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <?php if ($buyer): ?>
          <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(74,222,128,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($buyerName,0,1)) ?></span>
            <?= $h($buyerName) ?>
          </div>
          <?php endif; ?>
          <?php if ($seller): ?>
          <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(109,92,255,.10);border:1px solid rgba(109,92,255,.20);color:#c4b5fd;font-size:.75rem;font-weight:700;">
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(109,92,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($sellerName,0,1)) ?></span>
            <?= $h($sellerName) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body" id="chat_messages"></div>

      <div class="card-footer">
        <form class="row gx-2" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action"      value="admin_item_chat_send">
          <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
          <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none">
          <div class="col">
            <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Write a message as admin…">
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


  <!-- RIGHT: Overview + People + Details -->
  <div class="col-12 col-lg-4">

    <!-- Order Overview -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Order Overview</span>
      </div>
      <div class="av-ov-earnings">
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Total</div>
          <div class="av-ov-earn-val">€<?= number_format($total, 2) ?></div>
        </div>
        <div class="av-ov-earn-sep">·</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Status</div>
          <div class="av-ov-earn-val"><span class="av-status <?= $badgeCls ?>" style="font-size:.7rem;"><?= $h($status) ?></span></div>
        </div>
        <div class="av-ov-earn-sep">·</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Qty</div>
          <div class="av-ov-earn-val"><?= $qty ?></div>
        </div>
      </div>
      <div class="av-stat-grid">
        <?php
        $statsGrid = [
            ['fa-solid fa-hashtag',     'Order ID',    '#'.$id],
            ['fa-solid fa-tag',         'Unit Price',  '€'.number_format($unit,2)],
            ['fa-solid fa-gift',        'Type',        $typeLabelFull],
            ['fa-solid fa-globe',       'Server',      $server ?: '—'],
            ['fa-solid fa-calendar',    'Created',     $createdAt ? date('d.m.Y H:i', strtotime($createdAt)) : '—'],
            ['fa-solid fa-rotate',      'Updated',     $updatedAt ? date('d.m.Y H:i', strtotime($updatedAt)) : '—'],
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

    <!-- Seller & Client -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-users" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Participants</span>
      </div>
      <?php if (!empty($sellerName)): ?>
      <div class="av-user-row">
        <div class="av-user-avi av-user-avi--purple">
          <?= strtoupper(substr($sellerName,0,1)) ?>
        </div>
        <div style="flex:1;min-width:0;">
          <?php if ($sellerId): ?>
            <a class="av-user-link" href="<?= BASE_URL ?>/admin-area/seller/<?= $sellerId ?>"><?= $h($sellerName) ?></a>
          <?php else: ?>
            <div style="font-size:.85rem;font-weight:800;color:rgba(255,255,255,.9);"><?= $h($sellerName) ?></div>
          <?php endif; ?>
          <div class="av-user-sub">Seller</div>
        </div>
        <?php if ($sellerId): ?>
        <a href="<?= BASE_URL ?>/admin-area/seller/<?= $sellerId ?>" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($buyerName)): ?>
      <div class="av-user-row">
        <div class="av-user-avi av-user-avi--green">
          <?php if ($buyerAvatar): ?><img src="<?= $h($buyerAvatar) ?>" alt=""><?php else: ?><?= strtoupper(substr($buyerName,0,1)) ?><?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <?php if ($buyerId): ?>
            <a class="av-user-link" href="<?= BASE_URL ?>/admin-area/client/<?= $buyerId ?>"><?= $h($buyerName) ?></a>
          <?php else: ?>
            <div style="font-size:.85rem;font-weight:800;color:rgba(255,255,255,.9);"><?= $h($buyerName) ?></div>
          <?php endif; ?>
          <div class="av-user-sub">Client</div>
        </div>
        <?php if ($buyerId): ?>
        <a href="<?= BASE_URL ?>/admin-area/client/<?= $buyerId ?>" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Order Details -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-circle-info" style="color:#4ade80;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Order Details</span>
      </div>
      <div>
        <?php if (!empty($riotId)): ?>
        <div class="av-detail-field">
          <div class="av-detail-label">Riot ID</div>
          <div class="av-detail-value" style="color:#c4b5fd;"><?= $h($riotId) ?></div>
        </div>
        <?php endif; ?>
        <div class="av-detail-field">
          <div class="av-detail-label">Wanted Gift</div>
          <div class="av-detail-value"><?= $h($wantedGift) ?></div>
        </div>
        <div class="av-detail-field">
          <div class="av-detail-label">Friendship</div>
          <div class="av-detail-value">
            <?php if (!empty($friendshipConfirmedAt)): ?>
              <span style="color:#4ade80;"><i class="fa-solid fa-check me-1"></i>Confirmed</span>
            <?php else: ?>
              <span style="color:#facc15;"><i class="fa-solid fa-clock me-1"></i>Pending</span>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($friendshipConfirmedAt)): ?>
        <div class="av-detail-field">
          <div class="av-detail-label">Confirmed At</div>
          <div class="av-detail-value"><?= date('d.m.Y H:i', strtotime($friendshipConfirmedAt)) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($friendshipReadyAt)): ?>
        <div class="av-detail-field">
          <div class="av-detail-label">Gifting Ready</div>
          <div class="av-detail-value">
            <?= date('d.m.Y H:i', strtotime($friendshipReadyAt)) ?>
            <?php if ($remaining !== null && $remaining > 0): ?>
              <div style="font-size:.72rem;color:#facc15;margin-top:2px;" id="friendshipTimer" data-seconds="<?= $remaining ?>"><i class="fa-solid fa-clock"></i> <span id="friendshipTimerText"></span></div>
            <?php elseif ($remaining !== null): ?>
              <div style="font-size:.72rem;color:#4ade80;margin-top:2px;"><i class="fa-solid fa-gift"></i> Ready now!</div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php foreach ($details as $detailKey => $detailVal): if (in_array($detailKey, ['riot_game_name','riot_tagline','wanted_gift'])) continue; if (!$detailVal) continue; ?>
        <div class="av-detail-field">
          <div class="av-detail-label"><?= $h(ucwords(str_replace(['_','-'], ' ', $detailKey))) ?></div>
          <div class="av-detail-value"><?= $h((string)$detailVal) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div><!-- /col-lg-4 -->
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
  const AJAX_URL    = '<?= AJAX_URL ?>';
  const PURCHASE_ID = <?= $id ?>;
  const ADMIN_AVATAR = '<?= defined("ICON_URL") ? ICON_URL."/03ce541a1f4bf8b06c924439ffcc8173.png" : "" ?>';
  const USER_AVATAR  = '<?= defined("ICON_URL") ? ICON_URL."/8515d2c8c74a3f9bae054026f6549d91.png" : "" ?>';


  document.querySelectorAll('.js-admin-poke-client').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      var oldHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      $.post(AJAX_URL, { action: 'admin_poke_client', ref_type: btn.getAttribute('data-ref-type') || 'item', id: btn.getAttribute('data-id') || PURCHASE_ID }, function(resp){
        var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
        if (d && d.playSound) { try { new Audio(asset_url + '/core/dash/audio/' + d.playSound + '.mp3').play(); } catch(e) {} }
      }).always(function(){ btn.disabled = false; btn.innerHTML = oldHtml; });
    });
  });

  /* ── CHAT ── */
  let msg_none = false, chat_sig = '', initial_load = true;

  function decodeHtmlEntities(str){ const t=document.createElement('textarea'); t.innerHTML=str??''; return t.value.replace(/\n/g,'<br>'); }
  function formatTime(ts){ if(!ts) return ''; const d=new Date((parseInt(ts,10)||0)*1000); const p=n=>String(n).padStart(2,'0'); return `${p(d.getDate())}.${p(d.getMonth()+1)}.${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`; }
  function escapeAttr(s){ return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function getRoleBadge(sender){
    if(sender==='seller') return {cls:'lb-badge--seller',label:'Seller'};
    if(sender==='admin')  return {cls:'lb-badge--admin', label:'Admin'};
    if(sender==='system') return {cls:'lb-badge--system',label:'System'};
    return {cls:'lb-badge--client',label:'Client'};
  }
  function getFallbackAvatar(sender){ return (sender==='admin') ? ADMIN_AVATAR : USER_AVATAR; }

  function load_message(id, m, isGrouped){
    const t = formatTime(m.time), c = decodeHtmlEntities(m.content);
    if(m.sender==='system') return `<div style="text-align:center;margin:.5rem 0;"><span style="display:inline-block;background:rgba(255,255,255,.06);border-radius:999px;padding:.25rem .85rem;font-size:.76rem;opacity:.6;">${c}</span><div style="font-size:.68rem;opacity:.35;margin-top:.15rem;">${t}</div></div>`;
    const isMe=(m.sender==='admin');
    const aCls=isMe?'lb-msg--end':'lb-msg--start', hCls=isMe?'lb-msg__head lb-msg__head--end':'lb-msg__head';
    const badge=getRoleBadge(m.sender), avatar=(m.sender_icon&&(''+m.sender_icon).length)?m.sender_icon:getFallbackAvatar(m.sender);
    const name=isMe?'Admin':(m.sender_name||m.sender||'User');
    let html=`<div class="lb-msg ${aCls}">`;
    if(!isGrouped) html+=`<div class="${hCls}"><img class="lb-msg__avatar" src="${avatar}" alt=""><div class="lb-msg__name">${name} <span class="lb-badge ${badge.cls}">${badge.label}</span></div></div>`;
    html+=`<div class="lb-msg__bubble"><div class="lb-msg__content">${c}</div></div>`;
    html+=`<div class="lb-msg__stamp">${t}</div></div>`;
    return html;
  }

  function update_scroll(){ const el=document.getElementById('chat_messages'); if(el) el.scrollTop=el.scrollHeight; }

  function load_messages(){
    $.post(AJAX_URL,{action:'item_chat_load',purchase_id:PURCHASE_ID},function(resp){
      let r; try{ r=typeof resp==='string'?JSON.parse(resp):resp; }catch(e){return;}
      const raw=r.messages||{}, list={};
      $.each(raw,(k,v)=>{ if(v&&!v.deleted&&v.type!=='deleted') list[k]=v; });
      const cnt=Object.keys(list).length, sig=JSON.stringify(list);
      if(cnt>0){
        if(sig!==chat_sig){
          chat_sig=sig; let html='',ls='',lid=0;
          $.each(list,(k,v)=>{ const g=(v.sender===ls&&String(v.sender_id)===String(lid)); html+=load_message(k,v,g); ls=v.sender;lid=v.sender_id; });
          $('#chat_messages').html(html); update_scroll();
        }
        initial_load=false;
      } else {
        if(!msg_none){ $('#chat_messages').html('<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet.</span></div>'); msg_none=true; }
      }
    });
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
      .done(()=>{ document.getElementById('lbChatMessageInput').value=''; document.getElementById('lbChatImageInput').value=''; document.getElementById('lbChatImagePreviewWrap').classList.add('d-none'); load_messages(); update_scroll(); })
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

  // Friendship countdown timer
  (function(){
    const timerEl=document.getElementById('friendshipTimer');
    const timerText=document.getElementById('friendshipTimerText');
    if(!timerEl||!timerText) return;
    let remaining=parseInt(timerEl.getAttribute('data-seconds'),10)||0;
    function tick(){
      if(remaining<=0){ timerEl.innerHTML='<i class="fa-solid fa-gift"></i> Gifting is now available!'; timerEl.style.color='#4ade80'; return; }
      const d=Math.floor(remaining/86400),h=Math.floor((remaining%86400)/3600),m=Math.floor((remaining%3600)/60),s=remaining%60;
      const parts=[]; if(d>0) parts.push(d+'d'); if(h>0) parts.push(h+'h'); if(m>0) parts.push(m+'m'); parts.push(s+'s');
      timerText.textContent=parts.join(' ');
      remaining--; setTimeout(tick,1000);
    }
    tick();
  })();

  // Init
  load_messages();

  window.lbOrderViewChatUpdate = function (data) {
    if (!data || data.order_id === ('itempurch_' + PURCHASE_ID)) {
      load_messages();
    }
  };

  setInterval(function () {
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected) return;
    load_messages();
  }, 2000);

  setInterval(function () {
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected) return;
    load_messages();
  }, 60000);
})();
</script>
<?= $this->end() ?>
