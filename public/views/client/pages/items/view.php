<?php
$purchase    = $purchase ?? [];
$seller      = $seller   ?? null;
$details     = $details  ?? [];
$remaining   = $remaining ?? null;
$purchase_id = (int)($purchase['id'] ?? 0);

$type        = (string)($purchase['type'] ?? '');
$server      = strtoupper((string)($purchase['server'] ?? ''));
$status      = (string)($purchase['status'] ?? 'PAID');
$itemTitle   = (string)($purchase['item_title'] ?? $purchase['title'] ?? 'Item');

// Type helpers
$typeMap = ['skins'=>'Skins','skin'=>'Skins','chests-keys'=>'Chests & Keys','chest-key'=>'Chests & Keys',
            'chest'=>'Chests & Keys','orbs'=>'Orbs','orb'=>'Orbs','capsules'=>'Capsules','capsule'=>'Capsules',
            'event-pass'=>'Event Pass','pass'=>'Event Pass','bundles'=>'Bundles','bundle'=>'Bundles',
            'tft-item'=>'TFT Item','tft'=>'TFT Item','mystery-gift'=>'Mystery Gift','gifting'=>'Mystery Gift'];
$typeLabel = $typeMap[strtolower(trim($type))] ?? ucwords(str_replace(['-','_'],' ',$type));

$typeImgStems = ['skins'=>'skins-item','chests-keys'=>'chest-item','orbs'=>'orbs-item',
                 'capsules'=>'capsules-item','event-pass'=>'event-pass-item','bundles'=>'bundle-item',
                 'tft-item'=>'tft-item','mystery-gift'=>null];
$typeKey  = trim(preg_replace('/[^a-z0-9]+/','-',strtolower($typeLabel)),'-');
$typeImg  = (array_key_exists($typeKey,$typeImgStems) && $typeImgStems[$typeKey])
    ? rtrim(ASSET_URL,'/').'/website/images/items/'.$typeImgStems[$typeKey].'.webp' : null;
$typeFaMap = ['skins'=>'fa-shirt','chests-keys'=>'fa-key','orbs'=>'fa-circle-nodes','capsules'=>'fa-capsules',
              'event-pass'=>'fa-ticket','bundles'=>'fa-gift','tft-item'=>'fa-chess-board','mystery-gift'=>'fa-sparkles'];
$typeFa   = 'fa-solid '.($typeFaMap[$typeKey] ?? 'fa-tag');

// Currency
$_vc = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$_vs = function_exists('util_format_currency_display') ? util_format_currency_display($_vc) : ($_vc==='USD'?'$':'€');
$_vr = 1.0;
if ($_vc!=='EUR' && function_exists('get_exchange_rate')) { $r=(float)get_exchange_rate(); if($r>0)$_vr=$r; }
$priceFormatted = $_vs . number_format(((int)($purchase['price']??0))/100*$_vr, 2);

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<?= $this->layout('client/layouts/main', ['meta' => [
    'title'       => 'Item Order #' . $purchase_id . ' | LoLBoost.gg',
    'h1'          => 'Item Order',
    'description' => 'View your purchased item details and chat with the seller.',
]]) ?>

<?= $this->start('styles') ?>
<style>
/* ── reuse account-view base styles ── */
.client-item-view .card { background:var(--bs-card-bg)!important; border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important; border-radius:22px!important; box-shadow:none!important; }
.client-item-view .card::before { display:none!important; }
.client-item-view .order-chat-card { overflow:hidden; }
.av-head { border-radius:22px; overflow:hidden; margin-bottom:20px; border:1px solid var(--bs-card-border-color); background:#25282a; }
.av-head-body { padding:20px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; border-bottom:1px solid var(--bs-card-border-color); }
.av-status { display:inline-flex; align-items:center; gap:.35rem; padding:4px 11px; border-radius:99px; font-size:.75rem; font-weight:800; }
.av-status--paid      { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.22); color:#4ade80; }
.av-status--unpaid    { background:rgba(251,191,36,.12); border:1px solid rgba(251,191,36,.22); color:#fbbf24; }
.av-status--cancelled { background:rgba(251,113,133,.12); border:1px solid rgba(251,113,133,.22); color:#fb7185; }
.av-status--default   { background:rgba(99,102,241,.14); border:1px solid rgba(99,102,241,.28); color:#a5b4fc; }
.av-meta-row { display:flex; flex-wrap:wrap; gap:6px; padding:14px 22px 16px; }
.av-meta-pill { display:inline-flex; align-items:center; gap:.3rem; padding:4px 11px; border-radius:99px; font-size:.75rem; font-weight:700; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.7); }
.av-meta-pill strong { color:rgba(255,255,255,.92); }
.av-btn-ghost { display:inline-flex; align-items:center; gap:.4rem; padding:7px 14px; border-radius:11px; font-size:.83rem; font-weight:700; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.7); cursor:pointer; transition:background .12s; text-decoration:none; }
.av-btn-ghost:hover { background:rgba(255,255,255,.09); color:#fff; }
.av-btn-danger { display:inline-flex; align-items:center; gap:.4rem; padding:7px 14px; border-radius:11px; font-size:.83rem; font-weight:700; background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.2); color:#f87171; cursor:pointer; transition:background .12s; text-decoration:none; }
.av-btn-danger:hover { background:rgba(239,68,68,.16); color:#fca5a5; }
/* sidebar */
.av-sidebar-card { border-radius:18px; border:1px solid rgba(255,255,255,.07); background:#25282a; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,.2); }
.av-sc-header { display:flex; align-items:center; gap:8px; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02); }
.av-sc-icon { width:26px; height:26px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,.1); font-size:.75rem; }
.av-sc-title { font-size:.8rem; font-weight:900; color:rgba(255,255,255,.75); text-transform:uppercase; letter-spacing:.06em; flex:1; }
/* detail rows */
.av-detail-list { padding:4px 0 6px; }
.av-detail-item { display:flex; align-items:flex-start; gap:8px; padding:8px 16px; border-bottom:1px solid rgba(255,255,255,.04); }
.av-detail-item:last-child { border-bottom:0; }
.av-detail-lbl { font-size:.72rem; font-weight:700; color:rgba(255,255,255,.35); min-width:90px; flex-shrink:0; padding-top:1px; }
.av-detail-val { font-size:.82rem; font-weight:700; color:rgba(255,255,255,.85); word-break:break-word; }
/* friendship status */
.av-friendship { margin:12px 16px; padding:12px 14px; border-radius:12px; font-size:.82rem; }
.av-friendship--waiting { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.5); }
.av-friendship--confirmed { background:rgba(74,222,128,.07); border:1px solid rgba(74,222,128,.18); color:#4ade80; }
/* tips */
.av-tip-item { display:flex; align-items:flex-start; gap:10px; padding:10px 16px; border-bottom:1px solid rgba(255,255,255,.04); }
.av-tip-item:last-child { border-bottom:0; }
.av-tip-ico { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.75rem; }
.av-tip-title { font-size:.8rem; font-weight:800; color:rgba(255,255,255,.85); margin-bottom:1px; }
.av-tip-desc  { font-size:.73rem; color:rgba(255,255,255,.38); line-height:1.4; }
/* seller card */
.av-seller-row { display:flex; align-items:center; gap:12px; padding:14px 16px; }
/* chat */
.av-chat-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--bs-card-border-color); }
.av-chat-title  { font-size:.95rem; font-weight:900; color:rgba(255,255,255,.9); display:flex; align-items:center; gap:.5rem; }
#chat_messages { min-height:300px; max-height:480px; overflow-y:auto; padding:1rem 1.25rem; display:flex; flex-direction:column; scroll-behavior:smooth; }
#chat_messages::-webkit-scrollbar { width:5px; }
#chat_messages::-webkit-scrollbar-track { background:transparent; }
#chat_messages::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:3px; }
.lb-msg { display:flex; flex-direction:column; margin-bottom:.5rem; max-width:75%; }
.lb-msg--start { align-self:flex-start; }
.lb-msg--end   { align-self:flex-end; }
.lb-msg__head  { display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem; }
.lb-msg__head--end { flex-direction:row-reverse; }
.lb-msg__avatar { width:1.75rem; height:1.75rem; border-radius:50%; object-fit:cover; flex-shrink:0; }
.lb-msg__name { font-weight:700; font-size:.8rem; line-height:1.3; display:flex; align-items:center; gap:.3rem; }
.lb-msg__bubble { padding:.55rem .85rem; border-radius:.75rem; font-size:.875rem; line-height:1.55; word-break:break-word; background:rgba(255,255,255,.07); }
.lb-msg--end .lb-msg__bubble { background:rgba(99,102,241,.22); }
.lb-msg__stamp { font-size:.7rem; opacity:.4; margin-top:.2rem; }
.lb-msg--end .lb-msg__stamp { text-align:right; }
.lb-read-receipt{display:inline-flex;align-items:center;margin-left:.3rem;font-size:.72rem;font-weight:900;letter-spacing:-.18em;color:rgba(255,255,255,.34);vertical-align:middle}.lb-read-receipt.is-seen{color:#8b7cff}.lb-read-receipt__label{letter-spacing:0;margin-left:.38rem;font-size:.66rem;font-weight:700;color:inherit}
.lb-msg__content img { max-width:240px; max-height:200px; border-radius:.5rem; display:block; margin-top:.4rem; cursor:pointer; }
.lb-badge { display:inline-flex; align-items:center; padding:.1rem .4rem; border-radius:999px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.lb-badge--seller { background:rgba(99,102,241,.2); color:#818cf8; }
.lb-badge--client { background:rgba(16,185,129,.15); color:#10b981; }
.lb-badge--admin  { background:rgba(245,158,11,.15); color:#f59e0b; }
.lb-chat-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:240px; opacity:.4; gap:.5rem; text-align:center; }
.lb-chat-preview { display:inline-flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:.5rem; padding:.4rem .7rem; }
.lb-chat-preview img { width:2.5rem; height:2.5rem; object-fit:cover; border-radius:.35rem; }
.lb-chat-preview__remove { background:none; border:none; color:rgba(255,255,255,.5); cursor:pointer; padding:0 .2rem; }
/* report modal */
.rp-modal-overlay { position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.65); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; pointer-events:none; transition:opacity .2s; }
.rp-modal-overlay.is-open { opacity:1; pointer-events:all; }
.rp-modal { width:100%; max-width:480px; background:#1e2022; border:1px solid rgba(255,255,255,.1); border-radius:20px; overflow:hidden; transform:translateY(16px) scale(.97); transition:transform .2s; box-shadow:0 24px 60px rgba(0,0,0,.5); }
.rp-modal-overlay.is-open .rp-modal { transform:translateY(0) scale(1); }
.rp-modal-header { display:flex; align-items:center; gap:10px; padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02); }
.rp-modal-icon { width:32px; height:32px; border-radius:10px; flex-shrink:0; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.22); display:flex; align-items:center; justify-content:center; font-size:.8rem; color:#f87171; }
.rp-modal-title { font-size:.95rem; font-weight:900; color:rgba(255,255,255,.9); flex:1; }
.rp-modal-close { background:none; border:none; color:rgba(255,255,255,.3); cursor:pointer; padding:4px; border-radius:6px; transition:color .12s,background .12s; line-height:1; }
.rp-modal-close:hover { color:#fff; background:rgba(255,255,255,.06); }
.rp-modal-body { padding:20px; }
.rp-label { font-size:.72rem; font-weight:800; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px; }
.rp-problems { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.rp-problem-opt { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:11px; border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.03); cursor:pointer; transition:border-color .12s,background .12s; }
.rp-problem-opt:hover { border-color:rgba(239,68,68,.3); background:rgba(239,68,68,.05); }
.rp-problem-opt.is-selected { border-color:rgba(239,68,68,.45); background:rgba(239,68,68,.1); }
.rp-problem-opt input[type="radio"] { display:none; }
.rp-problem-ico { font-size:.8rem; width:16px; text-align:center; color:rgba(255,255,255,.35); flex-shrink:0; }
.rp-problem-opt.is-selected .rp-problem-ico { color:#f87171; }
.rp-problem-text { font-size:.82rem; font-weight:700; color:rgba(255,255,255,.65); flex:1; }
.rp-problem-opt.is-selected .rp-problem-text { color:rgba(255,255,255,.9); }
.rp-problem-check { width:16px; height:16px; border-radius:50%; border:1.5px solid rgba(255,255,255,.15); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.6rem; color:transparent; transition:all .12s; }
.rp-problem-opt.is-selected .rp-problem-check { border-color:#f87171; background:#f87171; color:#fff; }
.rp-details-wrap { margin-bottom:16px; }
.rp-details { width:100%; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); border-radius:11px; color:rgba(255,255,255,.85); font-size:.83rem; padding:10px 12px; resize:none; outline:none; transition:border-color .12s; font-family:inherit; }
.rp-details:focus { border-color:rgba(239,68,68,.4); }
.rp-modal-footer { padding:0 20px 20px; display:flex; gap:8px; justify-content:flex-end; }
.rp-submit { display:inline-flex; align-items:center; gap:.4rem; padding:9px 20px; border-radius:11px; font-size:.84rem; font-weight:800; background:rgba(239,68,68,.18); border:1px solid rgba(239,68,68,.3); color:#f87171; cursor:pointer; transition:background .12s; }
.rp-submit:hover:not(:disabled) { background:rgba(239,68,68,.28); }
.rp-submit:disabled { opacity:.45; cursor:not-allowed; }
.rp-cancel { display:inline-flex; align-items:center; padding:9px 16px; border-radius:11px; font-size:.84rem; font-weight:700; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.6); cursor:pointer; transition:background .12s; }
.rp-cancel:hover { background:rgba(255,255,255,.08); }
.rp-success { text-align:center; padding:28px 20px; }
.rp-success-ico { font-size:2rem; margin-bottom:10px; }
.rp-success-title { font-size:1rem; font-weight:900; color:rgba(255,255,255,.9); margin-bottom:4px; }
.rp-success-sub { font-size:.8rem; color:rgba(255,255,255,.4); }
#lbImgModal .modal-content { background:rgba(0,0,0,.85); border:none; }
@media (max-width:768px) {
  .av-head-body { padding:14px 16px; }
  .av-meta-row  { padding:10px 16px 12px; }
  #chat_messages { min-height:220px; max-height:340px; }
  .lb-msg { max-width:88%; }
}
</style>
<?= $this->end() ?>

<?php
// Status badge class
$statusCls = match(strtoupper($status)) {
    'PAID','COMPLETED','READY_TO_GIFT','CHAT_OPENED','WAITING_FRIENDSHIP' => 'paid',
    'CANCELLED','REFUNDED' => 'cancelled',
    'UNPAID' => 'unpaid',
    default  => 'default',
};
?>

<div class="client-item-view">

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
        <?php if ($typeImg): ?>
          <img src="<?= $h($typeImg) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
        <?php else: ?>
          <i class="<?= $h($typeFa) ?>" style="font-size:1.4rem;color:#a5b4fc;"></i>
        <?php endif; ?>
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 style="font-size:1.15rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;"><?= $h($itemTitle) ?></h1>
          <span class="av-status av-status--<?= $statusCls ?>">
            <i class="fa-solid fa-circle-check" style="font-size:.6rem;"></i> <?= $h($status) ?>
          </span>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;">
          <?php if ($server): ?><span style="text-transform:uppercase;font-weight:700;"><?= $h($server) ?></span><span>·</span><?php endif; ?>
          <span>#<?= $purchase_id ?></span>
          <span>·</span>
          <span><?= date('d.m.Y', strtotime($purchase['created_at'] ?? 'now')) ?></span>
          <?php if ($seller): ?><span>·</span><span style="font-weight:700;"><i class="fa-solid fa-store me-1"></i><?= $h($seller['username'] ?? '—') ?></span><?php endif; ?>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <?php if (!empty($purchase['seller_id'])): ?>
      <button type="button" class="av-btn-ghost js-client-poke-seller" data-id="<?= $purchase_id ?>">
        <i class="fa-solid fa-hand-point-up"></i> Poke Seller
      </button>
      <?php endif; ?>
      <button type="button" class="av-btn-danger" id="reportProblemBtn">
        <i class="fa-solid fa-flag"></i> Report a Problem
      </button>
      <a href="<?= BASE_URL ?>/profile/items" class="av-btn-ghost">
        <i class="fa-solid fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <div class="av-meta-row">
    <?php if ($typeLabel): ?>
      <span class="av-meta-pill">
        <i class="<?= $h($typeFa) ?>" style="color:rgba(255,255,255,.4);"></i>
        <strong><?= $h($typeLabel) ?></strong>
      </span>
    <?php endif; ?>
    <?php if ($server): ?>
      <span class="av-meta-pill">
        <i class="fa-solid fa-globe" style="color:rgba(255,255,255,.4);"></i>
        <strong><?= $h($server) ?></strong>
      </span>
    <?php endif; ?>
    <span class="av-meta-pill">
      <i class="fa-solid fa-coins" style="color:rgba(255,255,255,.4);"></i>
      <strong><?= $priceFormatted ?></strong>
    </span>
    <?php if ((int)($purchase['quantity'] ?? 1) > 1): ?>
      <span class="av-meta-pill">
        <i class="fa-solid fa-layer-group" style="color:rgba(255,255,255,.4);"></i>
        <strong>x<?= (int)$purchase['quantity'] ?></strong>
      </span>
    <?php endif; ?>
    <?php if (!empty($purchase['requires_friendship_days'])): ?>
      <span class="av-meta-pill">
        <i class="fa-solid fa-clock" style="color:rgba(255,255,255,.4);"></i>
        <strong><?= (int)$purchase['requires_friendship_days'] ?> days</strong> delivery
      </span>
    <?php endif; ?>
  </div>
</div>


<!-- ── 2-COLUMN LAYOUT ── -->
<div class="row g-4 align-items-start">

  <!-- LEFT: Chat -->
  <div class="col-12 col-lg-7">
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Seller Support Chat
        </div>
        <?php if ($seller): ?>
        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(99,102,241,.10);border:1px solid rgba(99,102,241,.2);color:#a5b4fc;font-size:.75rem;font-weight:700;">
          <?php if (!empty($seller['icon'])): ?>
            <img src="<?= $h($seller['icon']) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(99,102,241,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($seller['username']??'S',0,1)) ?></span>
          <?php endif; ?>
          <?= $h($seller['username'] ?? '—') ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="card-body chat-bg" id="chat_messages"></div>

      <div class="card-footer">
        <form class="row gx-2 ajax-form" id="lbChatForm" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action"      value="client_item_chat_send">
          <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
          <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none">
          <div class="col">
            <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message">
          </div>
          <div class="col-auto d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="lbChatUploadBtn" title="Attach image">
              <i class="fa-duotone fa-paperclip"></i>
            </button>
            <button type="submit" class="btn btn-sm btn-primary">
              <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
              <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
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
  </div>

  <!-- RIGHT: Order details + friendship + tips + seller -->
  <div class="col-12 col-lg-5">

    <!-- Order Details -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-gift" style="color:#a5b4fc;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Order Details</span>
      </div>
      <div class="av-detail-list">
        <div class="av-detail-item">
          <span class="av-detail-lbl">Item</span>
          <span class="av-detail-val"><?= $h($itemTitle) ?></span>
        </div>
        <?php if ($typeLabel): ?>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Type</span>
          <span class="av-detail-val"><?= $h($typeLabel) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($server): ?>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Server</span>
          <span class="av-detail-val"><?= $h($server) ?></span>
        </div>
        <?php endif; ?>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Quantity</span>
          <span class="av-detail-val">x<?= (int)($purchase['quantity'] ?? 1) ?></span>
        </div>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Price</span>
          <span class="av-detail-val"><?= $priceFormatted ?></span>
        </div>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Status</span>
          <span class="av-detail-val">
            <span class="av-status av-status--<?= $statusCls ?>" style="font-size:.7rem;padding:2px 8px;"><?= $h($status) ?></span>
          </span>
        </div>
        <?php if (!empty($details['riot_game_name'])): ?>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Riot ID</span>
          <span class="av-detail-val"><?= $h($details['riot_game_name']) ?><?= !empty($details['riot_tagline']) ? '#'.$h($details['riot_tagline']) : '' ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($details['wanted_gift'])): ?>
        <div class="av-detail-item">
          <span class="av-detail-lbl">Wanted Gift</span>
          <span class="av-detail-val"><?= $h($details['wanted_gift']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Friendship status -->
      <?php if (!empty($purchase['friendship_confirmed_at'])): ?>
        <div class="av-friendship av-friendship--confirmed mx-2 mb-2">
          <div style="font-weight:800;margin-bottom:4px;"><i class="fa-solid fa-user-check me-1"></i> Friendship confirmed by seller</div>
          <?php if (!empty($purchase['friendship_ready_at'])): ?>
            <div style="font-size:.75rem;opacity:.8;">Ready at: <?= date('d.m.Y H:i', strtotime($purchase['friendship_ready_at'])) ?></div>
          <?php endif; ?>
          <div class="small" id="friendshipCountdown" data-ready-at="<?= $h($purchase['friendship_ready_at'] ?? '') ?>">
            <?= $remaining !== null && $remaining > 0 ? '' : 'Gifting is now available.' ?>
          </div>
        </div>
      <?php else: ?>
        <div class="av-friendship av-friendship--waiting mx-2 mb-2">
          <i class="fa-solid fa-clock me-1"></i> Waiting for seller to confirm friendship.
        </div>
      <?php endif; ?>
    </div>

    <!-- Tips -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.2);"><i class="fa-solid fa-lightbulb" style="color:#fbbf24;font-size:.72rem;"></i></span>
        <span class="av-sc-title">What happens next?</span>
      </div>
      <div style="padding:4px 0 8px;">
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <div>
            <div class="av-tip-title">Add the seller as friend</div>
            <div class="av-tip-desc">Search for the seller's Riot ID in-game and send a friend request.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80;">
            <i class="fa-solid fa-clock"></i>
          </div>
          <div>
            <div class="av-tip-title">Wait for friendship</div>
            <div class="av-tip-desc">Riot requires friends for <?= !empty($purchase['requires_friendship_days']) ? (int)$purchase['requires_friendship_days'] : '?' ?> days before gifting is possible.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24;">
            <i class="fa-solid fa-gift"></i>
          </div>
          <div>
            <div class="av-tip-title">Receive your gift</div>
            <div class="av-tip-desc">Once the friendship period is complete, the seller will send you the item.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;">
            <i class="fa-solid fa-headset"></i>
          </div>
          <div>
            <div class="av-tip-title">Need help?</div>
            <div class="av-tip-desc">Use the chat to contact your seller directly or report a problem.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Seller card -->
    <?php if ($seller): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.22);"><i class="fa-solid fa-store" style="color:#818cf8;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Seller</span>
      </div>
      <div class="av-seller-row">
        <?php if (!empty($seller['icon'])): ?>
          <img src="<?= $h($seller['icon']) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;" alt="">
        <?php else: ?>
          <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.25);display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:900;color:#818cf8;flex-shrink:0;">
            <?= strtoupper(substr($seller['username']??'S',0,1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <div style="font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);"><?= $h($seller['username'] ?? '') ?></div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px;">Your item seller</div>
        </div>
        <a href="#chat_messages" onclick="document.getElementById('chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'}); return false;"
           class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-duotone fa-comments"></i> Chat
        </a>
      </div>
    </div>
    <?php endif; ?>

  </div>

</div>
</div>


<!-- Report a Problem Modal -->
<div class="rp-modal-overlay" id="rpOverlay" role="dialog" aria-modal="true">
  <div class="rp-modal">
    <div class="rp-modal-header">
      <div class="rp-modal-icon"><i class="fa-solid fa-flag"></i></div>
      <div class="rp-modal-title">Report a Problem</div>
      <button class="rp-modal-close" id="rpClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="rpFormWrap">
      <div class="rp-modal-body">
        <div class="rp-label">What's the issue?</div>
        <div class="rp-problems" id="rpProblems">
          <?php
          $problems = [
            ['id'=>'not_delivered',  'icon'=>'fa-solid fa-box-open',      'text'=>'Item was never delivered'],
            ['id'=>'wrong_item',     'icon'=>'fa-solid fa-triangle-exclamation','text'=>'Wrong item was sent'],
            ['id'=>'seller_no_resp', 'icon'=>'fa-solid fa-comment-slash', 'text'=>'Seller is not responding'],
            ['id'=>'friendship_issue','icon'=>'fa-solid fa-user-xmark',   'text'=>'Friendship / gifting issue'],
            ['id'=>'seller_rude',    'icon'=>'fa-solid fa-face-angry',    'text'=>'Seller behaviour / harassment'],
            ['id'=>'refund_request', 'icon'=>'fa-solid fa-rotate-left',   'text'=>'I want a refund'],
            ['id'=>'other',          'icon'=>'fa-solid fa-ellipsis',      'text'=>'Other issue'],
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
        <button class="rp-submit" id="rpSubmitBtn" disabled><i class="fa-solid fa-paper-plane"></i> Send Report</button>
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

<!-- Image lightbox -->
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
  const PURCHASE_ID = <?= $purchase_id ?>;

  document.querySelectorAll('.js-client-poke-seller').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      var oldHtml=btn.innerHTML, cooldownStarted=false;
      btn.disabled=true;
      btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      function startCooldown(seconds){
        var remaining=Math.max(1,parseInt(seconds,10)||300);
        cooldownStarted=true;
        function render(){
          var mins=Math.floor(remaining/60),secs=String(remaining%60).padStart(2,'0');
          btn.innerHTML='<i class="fa-solid fa-clock"></i> Poke again in '+mins+':'+secs;
          if(remaining--<=0){clearInterval(timer);btn.disabled=false;btn.innerHTML=oldHtml;}
        }
        render();var timer=setInterval(render,1000);
      }
      $.post(AJAX_URL,{action:'client_poke_seller',ref_type:'item',id:btn.dataset.id||PURCHASE_ID},function(resp){
        var data=resp;try{if(typeof resp==='string')data=JSON.parse(resp);}catch(e){}
        if(data&&data.sendToast&&typeof create_toast==='function')create_toast(data.sendToast.type||'primary',data.sendToast.title||'Notice',data.sendToast.message||'Done');
        if(data&&data.cooldown_seconds)startCooldown(data.cooldown_seconds);
      }).always(function(){if(!cooldownStarted){btn.disabled=false;btn.innerHTML=oldHtml;}});
    });
  });
  const CLIENT_ID  = <?= (int)(CLIENT_ID ?? 0) ?>;
  const user_type  = 'client';
  const user_id    = CLIENT_ID;
  const SELLER_NAME   = <?= json_encode($h($seller['username'] ?? 'Seller')) ?>;
  const SELLER_AVATAR = '<?= ICON_URL ?>/03ce541a1f4bf8b06c924439ffcc8173.png';
  const CLIENT_AVATAR = '<?= ICON_URL ?>/8515d2c8c74a3f9bae054026f6549d91.png';

  /* ── CHAT ── */
  let msg_none = false, chat_json = {}, initial_load = true;
  const chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
  function message_sound() { try { chat_notif.volume=0.6; chat_notif.play(); } catch(e){} }

  function decodeHtmlEntities(str) {
    const txt=document.createElement('textarea'); txt.innerHTML=str??''; return txt.value.replace(/\n/g,'<br>');
  }
  function formatExactTime(ts) {
    const d=new Date((parseInt(ts,10)||0)*1000);
    if (isNaN(d)) return '';
    return d.toLocaleDateString('de-DE')+' '+d.toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'});
  }
  function getRoleBadge(sender) {
    if (sender==='seller') return {cls:'lb-badge--seller',label:'Seller'};
    if (sender==='admin')  return {cls:'lb-badge--admin', label:'Admin'};
    return {cls:'lb-badge--client',label:'You'};
  }
  function getFallbackAvatar(sender) {
    return (sender==='seller'||sender==='admin') ? SELLER_AVATAR : CLIENT_AVATAR;
  }

  function isMessageSeenByPeer(msg) {
    if (!msg) return false;
    if (msg.sender === 'client') return Number(msg.seen_by_seller ?? 0) === 1;
    return Number(msg.seen_by_client ?? 0) === 1;
  }
  function readReceiptHtml(msg, isMe) {
    if (!isMe) return '';
    const seen = isMessageSeenByPeer(msg);
    const label = seen ? 'Read' : 'Delivered';
    return `<span class="lb-read-receipt${seen ? ' is-seen' : ''}" title="${label}">✓✓<span class="lb-read-receipt__label">${label}</span></span>`;
  }

  function load_message(id, msg, isGrouped) {
    const exactTime = formatExactTime(msg.time);
    const content   = decodeHtmlEntities(msg.content);
    if (msg.sender==='system') return `<div style="text-align:center;margin:.5rem 0;"><span style="display:inline-block;background:rgba(255,255,255,.06);border-radius:999px;padding:.25rem .85rem;font-size:.76rem;opacity:.6;">${content}</span><div style="font-size:.68rem;opacity:.35;margin-top:.15rem;">${exactTime}</div></div>`;
    const isMe = (msg.sender===user_type && String(msg.sender_id)===String(user_id));
    const alignCls = isMe ? 'lb-msg--end' : 'lb-msg--start';
    const headCls  = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';
    const badge    = getRoleBadge(msg.sender);
    const avatar   = (msg.sender_icon && (''+msg.sender_icon).length) ? msg.sender_icon : getFallbackAvatar(msg.sender);
    const name     = isMe ? 'You' : (msg.sender_name||SELLER_NAME);
    let html = `<div class="lb-msg ${alignCls}">`;
    if (!isGrouped) {
      html += `<div class="${headCls}"><img class="lb-msg__avatar" src="${avatar}" alt=""><div class="lb-msg__name">${name} <span class="lb-badge ${badge.cls}">${badge.label}</span></div></div>`;
    }
    html += `<div class="lb-msg__bubble"><div class="lb-msg__content">${content}</div></div>`;
    html += `<div class="lb-msg__stamp">${exactTime}${readReceiptHtml(msg, isMe)}</div></div>`;
    return html;
  }

  function update_scroll() { const el=document.getElementById('chat_messages'); if(el) el.scrollTop=el.scrollHeight; }

  let chatLoadRunning = false;
  let latestChatMessages = {};
  let lastLoadAt = 0;
  let peerUnreadPending = false;

  function isChatVisible() {
    if (document.visibilityState !== 'visible' || !document.hasFocus()) return false;
    const chat = document.getElementById('chat_messages');
    if (!chat) return false;
    const rect = chat.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  function renderMessages(raw) {
    const list = {};
    $.each(raw || {}, function(k, v) {
      if (!v || v.deleted || v.type === 'deleted') return;
      v.sender = String(v.sender || v.sender_type || v.from || '').toLowerCase();
      list[k] = v;
    });

    latestChatMessages = list;
    peerUnreadPending = Object.values(list).some(function(v) {
      return v.sender !== 'client' && Number(v.seen_by_client ?? 0) !== 1;
    });
    const count = Object.keys(list).length;
    const sig = JSON.stringify(list);

    if (count > 0) {
      msg_none = false;
      if (sig !== window._chatSig) {
        window._chatSig = sig;
        chat_json = list;
        let html = '', lastSender = '', lastSenderId = 0;
        $.each(list, function(k, v) {
          const grouped = v.sender === lastSender && String(v.sender_id) === String(lastSenderId);
          html += load_message(k, v, grouped);
          lastSender = v.sender;
          lastSenderId = v.sender_id;
        });
        $('#chat_messages').html(html);
        update_scroll();
      }

      const keys = Object.keys(list);
      const lastMessage = list[keys[keys.length - 1]];
      if (!initial_load && lastMessage && lastMessage.sender !== user_type && Number(lastMessage.notify || 0) === 0 && document.visibilityState === 'visible') {
        message_sound();
      }
      initial_load = false;
    } else if (!msg_none) {
      $('#chat_messages').html('<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet. Start the conversation!</span></div>');
      msg_none = true;
    }
  }

  function load_messages(force = false, markSeen = true) {
    const now = Date.now();
    if (chatLoadRunning) return;
    if (!force && now - lastLoadAt < 500) return;

    chatLoadRunning = true;
    lastLoadAt = now;
    $.post(AJAX_URL, {
      action: 'item_chat_load',
      purchase_id: PURCHASE_ID,
      viewer_role: 'client',
      mark_seen: markSeen && isChatVisible() ? 1 : 0
    })
    .done(function(resp) {
      let data;
      try { data = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { return; }
      if (data && data.success !== false) renderMessages(data.messages || []);
    })
    .always(function() {
      chatLoadRunning = false;
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
  $(document).on('submit','#lbChatForm',function(e){
    e.preventDefault();
    const $btn=$('#lbChatForm [type=submit]');
    $btn.prop('disabled',true).find('.indicator-label').addClass('d-none');
    $btn.find('.indicator-progress').removeClass('d-none');
    $.ajax({url:AJAX_URL,method:'POST',data:new FormData(this),processData:false,contentType:false})
      .done((resp)=>{ let r=resp; try{if(typeof resp==='string')r=JSON.parse(resp);}catch(e){} document.getElementById('lbChatMessageInput').value=''; document.getElementById('lbChatImageInput').value=''; document.getElementById('lbChatImagePreviewWrap').classList.add('d-none'); if(r&&Array.isArray(r.messages)) renderMessages(r.messages); else load_messages(true); })
      .fail(()=>{ if(typeof create_toast==='function') create_toast('danger','Error','Could not send message.'); })
      .always(()=>{ $btn.prop('disabled',false).find('.indicator-label').removeClass('d-none'); $btn.find('.indicator-progress').addClass('d-none'); });
  });

  // Chat image lightbox
  document.addEventListener('click',function(e){
    const img=e.target.closest('#chat_messages img'); if(!img) return;
    e.preventDefault();
    const modal=document.getElementById('lbImgModal'), mImg=document.getElementById('lbImgModalImg');
    if(!modal||!mImg) return; mImg.src=img.src;
    if(window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
  });
  const lbMod=document.getElementById('lbImgModal');
  if(lbMod) lbMod.addEventListener('hidden.bs.modal',()=>{ const i=document.getElementById('lbImgModalImg'); if(i) i.src=''; });

  // Item chat realtime, one event and one read path, matching Top Up.
  function unwrapRealtimePayload(data) {
    let payload = data || {};
    for (let i = 0; i < 3; i++) {
      if (payload && payload.data && typeof payload.data === 'object') payload = payload.data;
      else if (payload && payload.payload && typeof payload.payload === 'object') payload = payload.payload;
      else break;
    }
    return payload || {};
  }

  function handleItemRealtime(raw) {
    const data = unwrapRealtimePayload(raw);
    const matches = String(data.order_id || '') === ('itempurch_' + PURCHASE_ID)
      || String(data.purchase_id || '') === String(PURCHASE_ID);
    if (!matches) return;

    if (Array.isArray(data.messages)) renderMessages(data.messages);

    // Only acknowledge when this browser window actually has focus and
    // there is a peer message that is still unread for the client.
    if (peerUnreadPending && isChatVisible()) {
      setTimeout(function() { load_messages(true, true); }, 60);
    }
  }

  function bindItemChatSocket() {
    const sock = window.lbSocket || window.socket || null;
    if (!sock) return;

    if (sock.__lbClientItemHandler) {
      try { sock.off('item_chat_update', sock.__lbClientItemHandler); } catch (e) {}
    }
    sock.__lbClientItemHandler = handleItemRealtime;

    const joinRooms = function() {
      try { sock.emit('join', 'clients'); } catch (e) {}
    };

    joinRooms();
    try { sock.on('connect', joinRooms); } catch (e) {}
    try { sock.on('item_chat_update', handleItemRealtime); } catch (e) {}
  }

  $(document).ready(function() {
    load_messages(true, true);
    update_scroll();
    bindItemChatSocket();
    setTimeout(bindItemChatSocket, 350);
    setTimeout(bindItemChatSocket, 1200);

    // Do not request on ordinary tab changes. Only acknowledge after returning
    // when a seller/admin message actually arrived while this tab was unfocused.
    const acknowledgePendingRead = function() {
      if (peerUnreadPending && isChatVisible()) load_messages(true, true);
    };
    window.addEventListener('focus', acknowledgePendingRead);
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') acknowledgePendingRead();
    });

    window.lbItemChatFallbackInterval && clearInterval(window.lbItemChatFallbackInterval);
    window.lbItemChatFallbackInterval = setInterval(function() {
      if (document.visibilityState !== 'visible') return;
      if (window.lbRealtimeConnected === true) return;
      load_messages(false, true);
    }, 60000);
  });

  /* ── Friendship countdown ── */
  const cd=document.getElementById('friendshipCountdown');
  if(cd&&cd.dataset.readyAt){
    function tick(){ const ready=new Date(cd.dataset.readyAt.replace(' ','T')).getTime(); const diff=ready-Date.now(); if(isNaN(ready)) return; if(diff<=0){cd.textContent='Gifting is now available.';return;} const d=Math.floor(diff/86400000),h=Math.floor((diff%86400000)/3600000),m=Math.floor((diff%3600000)/60000); cd.textContent=d+'d '+h+'h '+m+'m remaining'; }
    tick(); setInterval(tick,60000);
  }

  /* ── REPORT A PROBLEM ── */
  (function(){
    const REPORT_AJAX = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
    const overlay=document.getElementById('rpOverlay');
    const openBtn=document.getElementById('reportProblemBtn');
    const closeBtn=document.getElementById('rpClose');
    const cancelBtn=document.getElementById('rpCancelBtn');
    const submitBtn=document.getElementById('rpSubmitBtn');
    const formWrap=document.getElementById('rpFormWrap');
    const successWrap=document.getElementById('rpSuccessWrap');
    const successClose=document.getElementById('rpSuccessClose');
    const detailsEl=document.getElementById('rpDetails');
    const problemOpts=document.querySelectorAll('.rp-problem-opt');
    let selectedIssue=null;

    function openModal(){ overlay.classList.add('is-open'); document.body.style.overflow='hidden'; }
    function closeModal(){
      overlay.classList.remove('is-open'); document.body.style.overflow='';
      setTimeout(()=>{ selectedIssue=null; problemOpts.forEach(o=>o.classList.remove('is-selected')); if(detailsEl)detailsEl.value=''; submitBtn.disabled=true; submitBtn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send Report'; formWrap.style.display=''; successWrap.style.display='none'; },220);
    }
    if(openBtn) openBtn.addEventListener('click',openModal);
    [closeBtn,cancelBtn,successClose].forEach(b=>b&&b.addEventListener('click',closeModal));
    overlay.addEventListener('click',e=>{ if(e.target===overlay) closeModal(); });
    document.addEventListener('keydown',e=>{ if(e.key==='Escape'&&overlay.classList.contains('is-open')) closeModal(); });

    problemOpts.forEach(opt=>{
      opt.addEventListener('click',function(){
        problemOpts.forEach(o=>o.classList.remove('is-selected'));
        this.classList.add('is-selected');
        this.querySelector('input[type="radio"]').checked=true;
        selectedIssue=this.getAttribute('data-id');
        submitBtn._label=this.querySelector('.rp-problem-text')?.textContent||selectedIssue;
        submitBtn.disabled=false;
      });
    });

    submitBtn.addEventListener('click',async function(){
      if(!selectedIssue) return;
      submitBtn.disabled=true; submitBtn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Sending…';
      const issueLabel=submitBtn._label||selectedIssue;
      const details=detailsEl?detailsEl.value.trim():'';
      const itemTitle=<?= json_encode($h($itemTitle)) ?>;
      const purchaseId=PURCHASE_ID;
      const sellerName=<?= json_encode($h($seller['username'] ?? '—')) ?>;
      const adminUrl=`<?= ADMN_URL ?>/selling-item-purchase/${purchaseId}`;
      const payload={
        username:'Item Reports',
        embeds:[{
          title:'🚨 Item Order Problem Report',
          color:0xef4444,
          fields:[
            {name:'🎁 Item',    value:`**${itemTitle}** (#${purchaseId})`, inline:true},
            {name:'🏪 Seller',  value:sellerName,                          inline:true},
            {name:'👤 Client',  value:`#${CLIENT_ID}`,                     inline:true},
            {name:'⚠️ Issue',   value:issueLabel,                          inline:false},
            ...(details?[{name:'📝 Details',value:details.substring(0,1000),inline:false}]:[]),
            {name:'🔗 Admin',   value:`[View in Admin Panel](${adminUrl})`,inline:false},
          ],
          footer:{text:'Reported via lolboost.gg'},
          timestamp:new Date().toISOString(),
        }]
      };
      try {
        const fd=new FormData();
        fd.set('action','client_report_problem'); fd.set('ref_type','item'); fd.set('ref_id',String(purchaseId));
        fd.set('issue',selectedIssue); fd.set('issue_label',issueLabel); fd.set('details',details);
        const res=await fetch(REPORT_AJAX,{method:'POST',body:fd,credentials:'same-origin'});
        const d=await res.json();
        if(d&&d.success){ formWrap.style.display='none'; successWrap.style.display=''; }
        else throw new Error(d&&d.message?d.message:'Failed');
      } catch(err){
        console.error(err);
        submitBtn.disabled=false; submitBtn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send Report';
        if(typeof create_toast==='function') create_toast('danger','Error','Could not send report. Please try again.');
      }
    });
  })();

})();
</script>
<?= $this->end() ?>
