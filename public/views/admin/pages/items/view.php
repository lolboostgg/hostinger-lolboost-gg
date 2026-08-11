<?= $this->layout('admin/layouts/main', ['meta' => ['title' => ($meta['title'] ?? 'Item') . ' | Admin Area']]) ?>
<?php
$item     = $item     ?? null;
$purchase = $purchase ?? null;
$seller   = $seller   ?? null;
$buyer    = $buyer    ?? null;
$details  = $details  ?? [];

$isOrder  = !empty($purchase);
$images   = [];
if (!empty($item['images'])) {
    $tmp = json_decode((string)$item['images'], true);
    $images = is_array($tmp) ? $tmp : [];
}
$cover = $images[0] ?? (defined('ASSET_URL') ? ASSET_URL . '/public/uploads/icons/default2.png' : '');
$priceCents = (int)($item['price'] ?? 0);
$priceRaw   = $priceCents / 100;
$soldCount  = (int)($item['sold_count'] ?? 0);
$activeState= (int)($item['active'] ?? 1) === 1;
$status     = $isOrder ? ($purchase['status'] ?? 'pending') : ($soldCount > 0 ? 'Sold' : ($activeState ? 'Active' : 'Hidden'));
$badgeCls   = $isOrder
    ? (in_array(strtolower($status), ['completed','delivered']) ? 'iv-badge--active' : (in_array(strtolower($status), ['cancelled','canceled']) ? 'iv-badge--sold' : 'iv-badge--pending'))
    : ($status === 'Sold' ? 'iv-badge--sold' : ($status === 'Active' ? 'iv-badge--active' : 'iv-badge--hidden'));
?>

<?php echo $this->start('styles'); ?>
<style>
.iv-wrap{display:flex;flex-direction:column;gap:18px}
.iv-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.22)}
.iv-hero{padding:24px}
.iv-hero-top{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.iv-hero-left{display:flex;gap:16px;align-items:center;min-width:0}
.iv-cover{width:84px;height:84px;border-radius:16px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0}
.iv-title{font-size:1.4rem;font-weight:900;color:#fff;margin:0 0 6px;line-height:1.15}
.iv-sub{color:rgba(255,255,255,.4);font-size:.85rem}
.iv-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
.iv-chip{display:inline-flex;align-items:center;gap:.35rem;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.72);font-size:.76rem;font-weight:800}
.iv-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;min-height:40px;padding:0 16px;border-radius:11px;border:1px solid rgba(255,255,255,.10);text-decoration:none;cursor:pointer;background:rgba(255,255,255,.04);color:#fff;font-weight:800}
.iv-btn:hover{background:rgba(255,255,255,.08);color:#fff}
.iv-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px}
.iv-section{padding:20px}
.iv-section-title{font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);font-weight:900;margin:0 0 12px}
.iv-gallery{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.iv-gallery img{width:100%;height:180px;object-fit:cover;border-radius:14px;background:rgba(255,255,255,.04)}
.iv-empty-gallery{height:220px;border:1px dashed rgba(255,255,255,.12);border-radius:16px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.28)}
.iv-desc{color:rgba(255,255,255,.82);line-height:1.7;font-size:.92rem;white-space:pre-wrap;word-break:break-word}
.iv-stat-list{display:flex;flex-direction:column;gap:0}
.iv-stat{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.iv-stat:last-child{border-bottom:none}
.iv-stat-label{color:rgba(255,255,255,.45);font-size:.83rem}
.iv-stat-value{color:#fff;font-weight:800;text-align:right}
.iv-badge{display:inline-flex;align-items:center;gap:.35rem;padding:6px 10px;border-radius:999px;font-size:.74rem;font-weight:900}
.iv-badge--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80}
.iv-badge--hidden{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15}
.iv-badge--sold{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185}
.iv-badge--pending{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15}
.iv-chat-wrap{display:flex;flex-direction:column;height:100%;gap:0}
.iv-chat-messages{flex:1;min-height:300px;max-height:480px;overflow:auto;display:flex;flex-direction:column;gap:10px;padding:4px 0;}
.iv-chat-form{margin-top:12px}
.iv-user-link{color:#c4b5fd;text-decoration:none;font-weight:700}
.iv-user-link:hover{color:#fff;text-decoration:underline}
@media(max-width:1200px){.iv-grid{grid-template-columns:1fr}}
@media(max-width:768px){.iv-gallery{grid-template-columns:repeat(2,minmax(0,1fr))}.iv-gallery img{height:140px}}
@media(max-width:520px){.iv-cover{width:68px;height:68px}.iv-title{font-size:1.1rem}.iv-gallery{grid-template-columns:1fr}}
</style>
<?php echo $this->end(); ?>

<div class="iv-wrap">
  <!-- Hero -->
  <div class="iv-card iv-hero">
    <div class="iv-hero-top">
      <div class="iv-hero-left">
        <?php if (!empty($cover)): ?>
          <img class="iv-cover" src="<?= htmlspecialchars($cover) ?>" alt="">
        <?php endif; ?>
        <div style="min-width:0">
          <h1 class="iv-title"><?= htmlspecialchars($item['title'] ?? ($isOrder ? 'Item Order' : 'Item')) ?></h1>
          <div class="iv-sub">
            #<?= (int)($item['id'] ?? 0) ?>
            <?php if (!empty($seller['username'])): ?>
              &nbsp;•&nbsp; Seller:
              <a class="iv-user-link" href="<?= BASE_URL ?>/admin-area/seller/<?= (int)($seller['id'] ?? 0) ?>">
                <?= htmlspecialchars($seller['username']) ?>
              </a>
            <?php endif; ?>
          </div>
          <div class="iv-meta">
            <?php if (!empty($item['server'])): ?><span class="iv-chip"><?= htmlspecialchars(strtoupper($item['server'])) ?></span><?php endif; ?>
            <?php if (!empty($item['type'])): ?><span class="iv-chip"><?= htmlspecialchars(ucwords(str_replace(['_','-'], ' ', $item['type']))) ?></span><?php endif; ?>
            <?php if (!$isOrder): ?>
              <span class="iv-chip">Stock <?= (int)($item['stock'] ?? 1) ?></span>
              <span class="iv-chip">Sold <?= $soldCount ?></span>
            <?php endif; ?>
            <span class="iv-badge <?= $badgeCls ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="iv-btn" href="<?= BASE_URL ?>/admin-area/items"><i class="fa-solid fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>

  <?php if (!$isOrder): ?>
  <!-- Listing View -->
  <div class="iv-grid">
    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Gallery</h2>
      <?php if (!empty($images)): ?>
        <div class="iv-gallery">
          <?php foreach ($images as $img): ?>
            <a href="<?= htmlspecialchars((string)$img) ?>" target="_blank" rel="noopener">
              <img src="<?= htmlspecialchars((string)$img) ?>" alt="">
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="iv-empty-gallery"><div>No images uploaded.</div></div>
      <?php endif; ?>

      <?php if (!empty($item['description'])): ?>
        <h2 class="iv-section-title" style="margin-top:20px">Description</h2>
        <div class="iv-desc"><?= htmlspecialchars((string)$item['description']) ?></div>
      <?php endif; ?>
    </div>

    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Overview</h2>
      <div class="iv-stat-list">
        <div class="iv-stat"><div class="iv-stat-label">Price</div><div class="iv-stat-value">€<?= number_format($priceRaw, 2) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Stock</div><div class="iv-stat-value"><?= (int)($item['stock'] ?? 1) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Min Qty</div><div class="iv-stat-value"><?= (int)($item['min_purchase_qty'] ?? 1) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Max Qty</div><div class="iv-stat-value"><?= !empty($item['max_purchase_qty']) ? (int)$item['max_purchase_qty'] : '—' ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Friendship Days</div><div class="iv-stat-value"><?= (int)($item['requires_friendship_days'] ?? 7) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Total Sold</div><div class="iv-stat-value"><?= $soldCount ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Created</div><div class="iv-stat-value"><?= !empty($item['created_at']) ? date('d.m.Y H:i', strtotime($item['created_at'])) : '—' ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Active</div><div class="iv-stat-value"><?= $activeState ? '<span style="color:#4ade80">Yes</span>' : '<span style="color:#fb7185">No</span>' ?></div></div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- Order View -->
  <div class="iv-grid">
    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Admin Chat View</h2>
      <div class="iv-chat-wrap">
        <div id="itemChatMessages" class="iv-chat-messages"></div>
        <div class="iv-chat-form">
          <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="admin_item_chat_send">
            <input type="hidden" name="purchase_id" value="<?= (int)$purchase['id'] ?>">
            <div class="row g-2">
              <div class="col"><textarea class="form-control" name="message" rows="3" placeholder="Write a message…"></textarea></div>
              <div class="col-12 col-md-auto"><input type="file" class="form-control" name="chat_image" accept="image/*"></div>
              <div class="col-12 col-md-auto"><button type="submit" class="btn btn-primary h-100">Send</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Order Details</h2>
      <div class="iv-stat-list">
        <div class="iv-stat">
          <div class="iv-stat-label">Seller</div>
          <div class="iv-stat-value">
            <?php if (!empty($seller['id'])): ?>
              <a class="iv-user-link" href="<?= BASE_URL ?>/admin-area/seller/<?= (int)$seller['id'] ?>"><?= htmlspecialchars($seller['username'] ?? 'Seller') ?></a>
            <?php else: ?><?= htmlspecialchars($seller['username'] ?? '—') ?><?php endif; ?>
          </div>
        </div>
        <div class="iv-stat">
          <div class="iv-stat-label">Client</div>
          <div class="iv-stat-value">
            <?php if (!empty($buyer['id'])): ?>
              <a class="iv-user-link" href="<?= BASE_URL ?>/admin-area/client/<?= (int)$buyer['id'] ?>"><?= htmlspecialchars($buyer['username'] ?? 'Client') ?></a>
            <?php else: ?><?= htmlspecialchars($buyer['username'] ?? '—') ?><?php endif; ?>
          </div>
        </div>
        <div class="iv-stat"><div class="iv-stat-label">Wanted Gift</div><div class="iv-stat-value"><?= htmlspecialchars($details['wanted_gift'] ?? '—') ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Status</div><div class="iv-stat-value"><span class="iv-badge <?= $badgeCls ?>"><?= htmlspecialchars(ucfirst($purchase['status'] ?? '')) ?></span></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Price</div><div class="iv-stat-value">€<?= number_format($priceRaw, 2) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Friendship Ready</div><div class="iv-stat-value"><?= !empty($purchase['friendship_ready_at']) ? date('d.m.Y H:i', strtotime($purchase['friendship_ready_at'])) : '—' ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Created</div><div class="iv-stat-value"><?= !empty($purchase['created_at']) ? date('d.m.Y H:i', strtotime($purchase['created_at'])) : '—' ?></div></div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php echo $this->start('scripts'); ?>
<script>
(function(){
  var container = document.getElementById('itemChatMessages');
  var purchaseId = <?= (int)($purchase['id'] ?? 0) ?>;
  if (!container || !purchaseId) return;
  function esc(str){ var d=document.createElement('div'); d.textContent=str||''; return d.innerHTML; }
  function render(messages){
    container.innerHTML='';
    (messages||[]).forEach(function(m){
      var wrap=document.createElement('div');
      wrap.style.maxWidth='78%';
      wrap.style.alignSelf=(m.sender==='admin'?'flex-end':'flex-start');
      wrap.innerHTML='<div style="font-size:12px;color:rgba(255,255,255,.45);margin-bottom:4px;">'+esc(m.sender_name||m.sender||'')+'</div>'
        +'<div style="padding:10px 12px;border-radius:12px;background:'+(m.sender==='admin'?'rgba(99,102,241,.2)':'rgba(255,255,255,.06)')+';">'
        +(m.message_type==='image'?m.content:esc(m.content||''))
        +'</div>';
      container.appendChild(wrap);
    });
    container.scrollTop=container.scrollHeight;
  }
  function load(){ $.post(AJAX_URL,{action:'item_chat_load',purchase_id:purchaseId},function(resp){ try{ if(typeof resp==='string') resp=JSON.parse(resp); if(resp&&resp.success) render(resp.messages||[]); }catch(e){} }); }
  load();

  window.lbOrderViewChatUpdate = function (data) {
    if (!data || data.order_id === ('itempurch_' + purchaseId)) {
      load();
    }
  };

  setInterval(function () {
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected) return;
    load();
  }, 30000);

  setInterval(function () {
    if (document.visibilityState === 'visible' && window.lbRealtimeConnected) return;
    load();
  }, 60000);
})();
</script>
<?php echo $this->end(); ?>
