<?= $this->layout('admin/layouts/main', ['meta' => $meta ?? ['title' => 'Digital Good Order | Admin Area']]) ?>
<?php
$purchase = is_array($purchase ?? null) ? $purchase : [];
$chat = is_array($chat ?? null) ? $chat : [];
$images = is_array($images ?? null) ? $images : [];
$h = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$id = (int)($purchase['id'] ?? 0);
$status = strtoupper(trim((string)($purchase['status'] ?? 'UNPAID')));
$labels = ['UNPAID'=>'Unpaid','PENDING'=>'Pending','PAID'=>'Paid','PROCESSING'=>'Processing','DELIVERED'=>'Delivered','COMPLETED'=>'Completed','CANCELLED'=>'Cancelled','CANCELED'=>'Cancelled','REFUNDED'=>'Refunded','FAILED'=>'Failed'];
$statusLabel = $labels[$status] ?? ucfirst(strtolower($status));
$statusClass = strtolower($status === 'CANCELED' ? 'cancelled' : $status);
$title = (string)($purchase['item_title'] ?? 'Digital Good');
$brand = (string)($purchase['brand'] ?? '');
$quantity = max(1, (int)($purchase['quantity'] ?? 1));
$total = number_format(((int)($purchase['price'] ?? 0)) / 100, 2);
$unit = number_format(((int)($purchase['unit_price'] ?? 0)) / 100, 2);
$symbol = strtoupper((string)($purchase['currency'] ?? 'EUR')) === 'USD' ? '$' : '€';
$date = static function ($value): string {
    if (empty($value)) return '—';
    $timestamp = is_numeric($value) ? (int)$value : strtotime((string)$value);
    return $timestamp > 0 ? date('d-m-y', $timestamp) : '—';
};
$asset = static function ($path): string {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
    $path = preg_replace('#^/public/assets#i', '', $path);
    return (defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '') . '/' . ltrim((string)$path, '/');
};
$avatar = static function ($path, string $fallback = ''): string {
    $path = trim((string)$path);
    $base = defined('ICON_URL') ? rtrim((string)ICON_URL, '/') : 'https://lolboost.gg/public/uploads/icons';
    if ($path === '') return $fallback !== '' ? $base . '/' . ltrim($fallback, '/') : '';
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
    if (str_contains($path, '/')) return (defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '') . '/' . ltrim($path, '/');
    return $base . '/' . ltrim($path, '/');
};
$brandIcon = $asset($purchase['brand_icon'] ?? '');
$images = array_values(array_filter(array_map($asset, $images)));
$deliveryNote = trim((string)($purchase['delivery_note'] ?? ''));
$customerNote = trim((string)($purchase['customer_note'] ?? ''));
?>

<?= $this->start('styles') ?>
<style>
.dgo{color:#f8fafc}.dgo a{text-decoration:none}.dgo-head,.dgo-card{background:#25282a;border:1px solid rgba(255,255,255,.08);box-shadow:0 3px 18px rgba(0,0,0,.16)}.dgo-head{padding:20px 22px;border-radius:20px;margin-bottom:18px}.dgo-head-main,.dgo-title,.dgo-actions,.dgo-pills{display:flex;align-items:center}.dgo-head-main{justify-content:space-between;gap:18px}.dgo-title{gap:14px;min-width:0}.dgo-icon{width:48px;height:48px;border-radius:13px;display:grid;place-items:center;overflow:hidden;flex:0 0 48px;background:linear-gradient(145deg,rgba(109,92,255,.3),rgba(139,92,246,.13));border:1px solid rgba(139,124,255,.32);color:#c4b5fd;font-size:19px}.dgo-icon img{width:100%;height:100%;object-fit:cover}.dgo-title h1{font-size:18px;margin:0 0 4px;font-weight:900}.dgo-muted{color:rgba(255,255,255,.43);font-size:11px}.dgo-actions{gap:8px}.dgo-btn{height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(255,255,255,.1);display:inline-flex;align-items:center;gap:7px;color:#e5e7eb;background:rgba(255,255,255,.04);font-size:11px;font-weight:850}.dgo-btn:hover{color:#fff;background:rgba(255,255,255,.08)}.dgo-pills{gap:8px;flex-wrap:wrap;margin-top:17px;padding-top:16px;border-top:1px solid rgba(255,255,255,.065)}.dgo-pill{padding:7px 11px;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);font-size:10px;font-weight:800;color:rgba(255,255,255,.72)}.dgo-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(310px,.85fr);gap:18px;align-items:start}.dgo-card{border-radius:17px;overflow:hidden;margin-bottom:18px}.dgo-card-head{min-height:51px;padding:0 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.07);font-size:12px;font-weight:900}.dgo-card-head i{color:#9d8cff;margin-right:7px}.dgo-body{padding:18px}.dgo-status{display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border-radius:999px;font-size:10px;font-weight:900}.dgo-status:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.dgo-status--unpaid,.dgo-status--failed,.dgo-status--cancelled,.dgo-status--refunded{color:#fb7185;background:rgba(244,63,94,.11);border:1px solid rgba(251,113,133,.28)}.dgo-status--pending,.dgo-status--processing{color:#fbbf24;background:rgba(245,158,11,.1);border:1px solid rgba(251,191,36,.24)}.dgo-status--paid{color:#60a5fa;background:rgba(59,130,246,.1);border:1px solid rgba(96,165,250,.24)}.dgo-status--delivered{color:#c084fc;background:rgba(168,85,247,.11);border:1px solid rgba(192,132,252,.25)}.dgo-status--completed{color:#4ade80;background:rgba(34,197,94,.1);border:1px solid rgba(74,222,128,.22)}.dgo-overview{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.dgo-field{padding:14px 17px;border-bottom:1px solid rgba(255,255,255,.055)}.dgo-field:nth-child(odd){border-right:1px solid rgba(255,255,255,.055)}.dgo-label{display:block;margin-bottom:5px;color:rgba(255,255,255,.34);font-size:9px;text-transform:uppercase;letter-spacing:.07em;font-weight:900}.dgo-value{font-size:12px;font-weight:800;color:#f8fafc;word-break:break-word}.dgo-person{display:flex;gap:12px;align-items:center}.dgo-avatar{width:39px;height:39px;border-radius:11px;display:grid;place-items:center;background:rgba(109,92,255,.15);border:1px solid rgba(139,124,255,.25);color:#b8adff;overflow:hidden}.dgo-avatar img{width:100%;height:100%;object-fit:cover}.dgo-person-name{font-size:12px;font-weight:900}.dgo-note{padding:13px 14px;border-radius:11px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.065);color:rgba(255,255,255,.7);font-size:11px;line-height:1.6;white-space:pre-wrap}.dgo-note+.dgo-note{margin-top:10px}.dgo-chat{height:440px;overflow:auto;padding:16px;background:rgba(0,0,0,.08)}.dgo-empty{height:100%;min-height:200px;display:grid;place-items:center;color:rgba(255,255,255,.3);font-size:12px}.dgo-msg{display:flex;gap:9px;margin-bottom:13px;max-width:78%}.dgo-msg--client{margin-left:auto;flex-direction:row-reverse}.dgo-msg--system{margin:12px auto;max-width:90%;justify-content:center}.dgo-msg-avatar{width:29px;height:29px;border-radius:50%;display:grid;place-items:center;flex:0 0 29px;background:#181a1c;color:#a99cff;font-size:10px}.dgo-bubble{padding:9px 11px;border-radius:4px 12px 12px 12px;background:#35393c;color:#e5e7eb;font-size:11px;line-height:1.5}.dgo-msg--client .dgo-bubble{border-radius:12px 4px 12px 12px;background:#5443a1}.dgo-msg--system .dgo-bubble{border-radius:999px;background:rgba(109,92,255,.1);border:1px solid rgba(139,124,255,.18);color:rgba(255,255,255,.55);text-align:center}.dgo-msg-meta{margin-top:5px;color:rgba(255,255,255,.3);font-size:8px}.dgo-chat-img{display:block;max-width:220px;max-height:180px;border-radius:8px;margin-top:7px}.dgo-gallery{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.dgo-gallery img{width:100%;height:86px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,.08)}@media(max-width:1000px){.dgo-grid{grid-template-columns:1fr}}@media(max-width:620px){.dgo-head-main{align-items:flex-start;flex-direction:column}.dgo-overview{grid-template-columns:1fr}.dgo-field:nth-child(odd){border-right:0}.dgo-actions{width:100%}.dgo-btn{flex:1;justify-content:center}.dgo-gallery{grid-template-columns:repeat(2,1fr)}}
.dgo-compose{padding:12px 14px;border-top:1px solid rgba(255,255,255,.07)}.dgo-compose-row{display:flex;gap:8px}.dgo-compose input[type=text]{min-width:0;flex:1;height:40px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:#1d1f21;color:#fff;padding:0 13px;outline:0}.dgo-send,.dgo-attach{height:40px;border:0;border-radius:10px;color:#fff;display:grid;place-items:center;cursor:pointer}.dgo-send{width:48px;background:linear-gradient(135deg,#6d5cff,#b05cff)}.dgo-attach{width:42px;background:rgba(255,255,255,.08)}.dgo-file{margin-top:7px;color:rgba(255,255,255,.5);font-size:10px}
.dgo-chat{height:auto;min-height:300px;max-height:480px}.dgo-msg--admin{margin-left:auto;flex-direction:row-reverse}.dgo-msg-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%}.dgo-role{display:inline-flex;margin-left:5px;padding:2px 6px;border-radius:999px;font-size:8px;font-weight:900;text-transform:uppercase}.dgo-role--seller{background:rgba(126,87,255,.18);color:#a98eff}.dgo-role--buyer{background:rgba(38,211,135,.15);color:#2ee392}.dgo-role--admin{background:rgba(255,181,54,.15);color:#ffc35c}.dgo-msg--system{width:100%;max-width:100%;margin:9px 0;display:block}.dgo-msg--system .dgo-bubble{display:block;width:100%;border-radius:16px;padding:13px 16px;text-align:left;background:rgba(109,92,255,.14);border:1px dashed rgba(159,140,255,.42);color:rgba(255,255,255,.9)}
.dgo-btn--primary{background:linear-gradient(135deg,#6d5cff,#b05cff);border-color:transparent;color:#fff;box-shadow:0 8px 22px rgba(109,92,255,.2)}.dgo-btn--warning{color:#fbbf24;background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3)}
.dgo-chat-people{display:flex;align-items:center;gap:7px}.dgo-person-chip{display:inline-flex;align-items:center;gap:7px;padding:5px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.1);font-size:10px;font-weight:850}.dgo-person-chip img,.dgo-person-chip span{width:18px;height:18px;border-radius:50%;object-fit:cover;display:grid;place-items:center}.dgo-person-chip--buyer{color:#35e692;background:rgba(34,197,94,.1);border-color:rgba(53,230,146,.22)}.dgo-person-chip--seller{color:#b69cff;background:rgba(109,92,255,.12);border-color:rgba(139,124,255,.25)}.dgo-msg--client{margin-left:0;flex-direction:row}.dgo-msg--client .dgo-bubble{background:#35393c;color:#f1f5f9;border-radius:4px 12px 12px}.dgo-msg--seller,.dgo-msg--admin{margin-left:auto;flex-direction:row-reverse}.dgo-msg--seller .dgo-bubble,.dgo-msg--admin .dgo-bubble{background:#5443a1;border-radius:12px 4px 12px 12px}.dgo-card-head{min-height:56px;height:auto}
.dgo-msg-content{display:flex;flex-direction:column;align-items:flex-start;min-width:0}.dgo-msg--seller .dgo-msg-content,.dgo-msg--admin .dgo-msg-content{align-items:flex-end}.dgo-msg-head{display:flex;align-items:center;gap:4px;margin-bottom:5px;color:#f1f5f9;font-size:10px;font-weight:850}.dgo-msg-stamp{margin-top:5px;color:rgba(255,255,255,.3);font-size:8px}
.dgo-chat{min-height:300px;max-height:480px;height:480px;padding:1rem 1.25rem;display:flex;flex-direction:column;scroll-behavior:smooth}.lb-msg{display:flex;flex-direction:column;margin-bottom:.5rem;max-width:75%}.lb-msg--start{align-self:flex-start}.lb-msg--end{align-self:flex-end}.lb-msg__head{display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem}.lb-msg__head--end{flex-direction:row-reverse}.lb-msg__avatar{width:1.75rem;height:1.75rem;border-radius:50%;object-fit:cover;flex-shrink:0}.lb-msg__name{font-weight:700;font-size:.8rem;line-height:1.3;display:flex;align-items:center;gap:.3rem}.lb-msg__bubble{padding:.55rem .85rem;border-radius:.75rem;font-size:.875rem;line-height:1.55;word-break:break-word;background:rgba(255,255,255,.07)}.lb-msg--end .lb-msg__bubble{background:rgba(245,158,11,.18)}.lb-msg__stamp{font-size:.7rem;opacity:.4;margin-top:.2rem}.lb-msg--end .lb-msg__stamp{text-align:right}.lb-msg__content img{max-width:240px;max-height:200px;border-radius:.5rem;display:block;margin-top:.4rem}.lb-badge{display:inline-flex;align-items:center;padding:.1rem .4rem;border-radius:999px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.lb-badge--seller{background:rgba(99,102,241,.2);color:#818cf8}.lb-badge--client{background:rgba(16,185,129,.15);color:#10b981}.lb-badge--admin{background:rgba(245,158,11,.15);color:#f59e0b}.lb-syswrap{width:100%;max-width:100%;margin:.55rem 0 .85rem}.lb-sys{display:block;width:100%;background:rgba(109,92,255,.14);border:1px dashed rgba(159,140,255,.45);border-radius:18px;padding:1.15rem 1.25rem;font-size:.9rem;font-weight:700;line-height:1.65;color:rgba(255,255,255,.9)}.lb-sys-time{font-size:.72rem;opacity:.45;margin-top:.35rem}
.atvCard{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:19px;overflow:hidden;margin-bottom:18px}.atvCardHead{min-height:56px;padding:0 18px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:900}.atvCardHead i{color:#9d8cff;margin-right:7px}.atvChat{height:480px;min-height:300px;max-height:480px;overflow:auto;padding:1rem 1.25rem;background:rgba(0,0,0,.08);display:flex;flex-direction:column;scroll-behavior:smooth}.atvChatPeople{display:flex;align-items:center;gap:7px}.atvPersonChip{display:inline-flex;align-items:center;gap:7px;padding:5px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.1);font-size:10px;font-weight:850}.atvPersonChip img,.atvPersonChip span{width:18px;height:18px;border-radius:50%;object-fit:cover;display:grid;place-items:center}.atvPersonChip.client{color:#35e692;background:rgba(34,197,94,.1);border-color:rgba(53,230,146,.22)}.atvPersonChip.seller{color:#b69cff;background:rgba(109,92,255,.12);border-color:rgba(139,124,255,.25)}.atvCompose{padding:12px 14px;border-top:1px solid rgba(255,255,255,.07)}.atvComposeRow{display:flex;gap:8px}.atvCompose input[type=text]{flex:1;min-width:0;height:40px;border:1px solid rgba(255,255,255,.1);border-radius:10px;background:#1d1f21;color:#fff;padding:0 13px;font-size:12px}.atvBtn{width:42px;height:40px;border:0;border-radius:10px;color:#fff;background:rgba(255,255,255,.08);display:grid;place-items:center;cursor:pointer}.atvBtn.send{width:48px;background:linear-gradient(135deg,#6d5cff,#b05cff)}.atvMeta{color:rgba(255,255,255,.43);font-size:11px}
.atvChat .lb-msg__bubble,.atvChat .lb-msg--end .lb-msg__bubble{padding:.65rem .9rem;background:#2b2d2f;color:#d6d8dc;border:1px solid rgba(255,255,255,.1);border-radius:.75rem}.atvChat .lb-msg__stamp{color:rgba(255,255,255,.4);opacity:1}
</style>
<?= $this->end() ?>

<div class="dgo">
  <section class="dgo-head">
    <div class="dgo-head-main">
      <div class="dgo-title">
        <div class="dgo-icon"><?php if ($brandIcon): ?><img src="<?= $h($brandIcon) ?>" alt=""><?php else: ?><i class="fa-duotone fa-gem"></i><?php endif; ?></div>
        <div><h1><?= $h($title) ?></h1><div class="dgo-muted">Digital Good Order #<?= $id ?> · <?= $h($brand ?: ($purchase['category_name'] ?? 'Digital Goods')) ?></div></div>
      </div>
      <div class="dgo-actions">
        <button type="button" class="dgo-btn dgo-btn--primary js-admin-poke-client" data-ref-type="digital_good" data-id="<?= $id ?>"><i class="fa-duotone fa-hand-point-up"></i> Poke Client</button>
        <?php if (!in_array($status, ['CANCELLED','CANCELED','REFUNDED'], true)): ?>
        <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Mark this order as unsold and reverse the seller payout?')">
          <input type="hidden" name="action" value="admin_marketplace_order_unsold"><input type="hidden" name="kind" value="digital_good"><input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="dgo-btn dgo-btn--warning"><i class="fa-duotone fa-rotate-left"></i> Unsold</button>
        </form>
        <?php endif; ?>
        <?php if (!empty($purchase['item_id'])): ?><a class="dgo-btn" href="<?= ADMN_URL ?>/digital-goods/listings/<?= (int)$purchase['item_id'] ?>/edit"><i class="fa-solid fa-pen"></i> Open listing</a><?php endif; ?>
        <a class="dgo-btn" href="<?= ADMN_URL ?>/digital-good-orders"><i class="fa-solid fa-arrow-left"></i> Back</a>
      </div>
    </div>
    <div class="dgo-pills">
      <span class="dgo-status dgo-status--<?= $h($statusClass) ?>"><?= $h($statusLabel) ?></span>
      <span class="dgo-pill"><i class="fa-solid fa-layer-group"></i> Qty <?= $quantity ?></span>
      <span class="dgo-pill"><i class="fa-solid fa-coins"></i> <?= $symbol . $total ?></span>
      <span class="dgo-pill"><i class="fa-solid fa-calendar"></i> <?= $h($date($purchase['created_at'] ?? null)) ?></span>
    </div>
  </section>

  <div class="dgo-grid">
    <main>
      <section class="atvCard">
        <div class="atvCardHead"><span><i class="fa-duotone fa-comments"></i>Buyer & Seller Chat <small class="atvMeta">(Admin View)</small></span><div class="atvChatPeople"><span class="atvPersonChip client"><img src="<?= $h($avatar($purchase['client_icon']??'','8515d2c8c74a3f9bae054026f6549d91.png')) ?>" alt=""><?= $h($purchase['client_username']??'Buyer') ?></span><span class="atvPersonChip seller"><img src="<?= $h($avatar($purchase['seller_icon']??'','03ce541a1f4bf8b06c924439ffcc8173.png')) ?>" alt=""><?= $h($purchase['seller_username']??'Seller') ?></span></div></div>
        <div class="atvChat" id="dgoChat">
          <?php if (!$chat): ?><div class="dgo-empty">No messages in this order yet.</div><?php endif; ?>
          <?php foreach ($chat as $message):
            $sender = strtolower((string)($message['sender'] ?? $message['type'] ?? 'system'));
            $sender = $sender === 'buyer' ? 'client' : $sender;
            $sender = in_array($sender, ['client','seller','admin','system'], true) ? $sender : 'system';
            $body = (string)($message['message'] ?? $message['text'] ?? $message['body'] ?? $message['content'] ?? '');
            $image = (string)($message['image_url'] ?? $message['image'] ?? '');
          ?>
          <div class="dgo-msg dgo-msg--<?= $h($sender) ?>">
            <?php if ($sender !== 'system'): ?><div class="dgo-msg-avatar"><i class="fa-solid <?= $sender === 'client' ? 'fa-user' : ($sender === 'admin' ? 'fa-shield-halved' : 'fa-store') ?>"></i></div><?php endif; ?>
            <div class="dgo-bubble">
              <?php if ($body !== ''): ?><div><?= nl2br($h($body)) ?></div><?php endif; ?>
              <?php if ($image !== ''): ?><a href="<?= $h($image) ?>" target="_blank" rel="noopener"><img class="dgo-chat-img" src="<?= $h($image) ?>" alt="Chat attachment"></a><?php endif; ?>
              <?php if ($sender !== 'system'): ?><div class="dgo-msg-meta"><?= $h($message['sender_name'] ?? ucfirst($sender)) ?> · <?= $h($date($message['created_at'] ?? $message['time'] ?? null)) ?></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <form class="atvCompose" id="dgoChatForm" enctype="multipart/form-data">
          <input type="hidden" name="action" value="admin_dg_chat_send">
          <input type="hidden" name="purchase_id" value="<?= $id ?>">
          <div class="atvComposeRow">
            <input type="text" name="message" id="dgoMessage" placeholder="Write a message as admin…">
            <label class="atvBtn" title="Attach image"><i class="fa-solid fa-paperclip"></i><input type="file" name="chat_image" id="dgoFile" accept="image/*" hidden></label>
            <button class="atvBtn send" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
          </div>
          <div class="atvMeta" id="dgoFileName" style="margin-top:6px"></div>
          <div class="atvMeta" style="margin-top:8px">Tip: You can paste a screenshot with <strong>Ctrl + V</strong>.</div>
        </form>
      </section>

      <?php if ($deliveryNote !== '' || $customerNote !== ''): ?>
      <section class="dgo-card"><div class="dgo-card-head"><span><i class="fa-duotone fa-box-check"></i>Delivery Information</span></div><div class="dgo-body">
        <?php if ($customerNote !== ''): ?><div class="dgo-note"><span class="dgo-label">Customer note</span><?= $h($customerNote) ?></div><?php endif; ?>
        <?php if ($deliveryNote !== ''): ?><div class="dgo-note"><span class="dgo-label">Seller delivery</span><?= $h($deliveryNote) ?></div><?php endif; ?>
      </div></section>
      <?php endif; ?>

      <?php if ($images): ?><section class="dgo-card"><div class="dgo-card-head"><span><i class="fa-duotone fa-images"></i>Listing Images</span></div><div class="dgo-body"><div class="dgo-gallery"><?php foreach ($images as $image): ?><a href="<?= $h($image) ?>" target="_blank" rel="noopener"><img src="<?= $h($image) ?>" alt=""></a><?php endforeach; ?></div></div></section><?php endif; ?>
    </main>

    <aside>
      <section class="dgo-card">
        <div class="dgo-card-head"><span><i class="fa-duotone fa-receipt"></i>Order Overview</span></div>
        <div class="dgo-overview">
          <div class="dgo-field"><span class="dgo-label">Order ID</span><span class="dgo-value">#<?= $id ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Invoice ID</span><span class="dgo-value"><?= !empty($purchase['invoice_id']) ? '#'.(int)$purchase['invoice_id'] : '—' ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Total</span><span class="dgo-value"><?= $symbol . $total ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Unit price</span><span class="dgo-value"><?= $symbol . $unit ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Quantity</span><span class="dgo-value"><?= $quantity ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Delivery</span><span class="dgo-value"><?= $h(ucfirst((string)($purchase['delivery_type'] ?? 'Manual'))) ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Created</span><span class="dgo-value"><?= $h($date($purchase['created_at'] ?? null)) ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Paid</span><span class="dgo-value"><?= $h($date($purchase['paid_at'] ?? null)) ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Delivered</span><span class="dgo-value"><?= $h($date($purchase['delivered_at'] ?? null)) ?></span></div>
          <div class="dgo-field"><span class="dgo-label">Completed</span><span class="dgo-value"><?= $h($date($purchase['completed_at'] ?? null)) ?></span></div>
        </div>
      </section>

      <?php foreach ([['Buyer','client_username','client_email','client_icon','fa-user','client_id'],['Seller','seller_username','seller_email','seller_icon','fa-store','seller_id']] as $person): ?>
      <section class="dgo-card"><div class="dgo-card-head"><span><i class="fa-duotone <?= $person[4] ?>"></i><?= $person[0] ?></span></div><div class="dgo-body"><div class="dgo-person">
        <div class="dgo-avatar"><?php $personIcon=$asset($purchase[$person[3]] ?? ''); if ($personIcon): ?><img src="<?= $h($personIcon) ?>" alt=""><?php else: ?><i class="fa-solid <?= $person[4] ?>"></i><?php endif; ?></div>
        <?php $personId=(int)($purchase[$person[5]] ?? 0); $profileUrl=$person[0]==='Seller' ? ADMN_URL.'/seller/'.$personId.'/profile' : ADMN_URL.'/client/'.$personId; ?>
        <div><div class="dgo-person-name"><?php if($personId): ?><a href="<?= $h($profileUrl) ?>" style="color:inherit"><?= $h($purchase[$person[1]] ?? $person[0]) ?></a><?php else: ?><?= $h($purchase[$person[1]] ?? $person[0]) ?><?php endif; ?></div><div class="dgo-muted"><?= $h($purchase[$person[2]] ?? 'No email') ?></div></div>
        <?php if($personId): ?><a class="dgo-btn" href="<?= $h($profileUrl) ?>" style="margin-left:auto"><i class="fa-duotone <?= $person[4] ?>"></i> Profile</a><?php endif; ?>
      </div></div></section>
      <?php endforeach; ?>
    </aside>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var form=document.getElementById('dgoChatForm'),file=document.getElementById('dgoFile'),fileName=document.getElementById('dgoFileName'),box=document.getElementById('dgoChat');
  var avatars={seller:<?= json_encode($avatar($purchase['seller_icon']??'','03ce541a1f4bf8b06c924439ffcc8173.png')) ?>,client:<?= json_encode($avatar($purchase['client_icon']??'','8515d2c8c74a3f9bae054026f6549d91.png')) ?>,admin:<?= json_encode($avatar('','03ce541a1f4bf8b06c924439ffcc8173.png')) ?>};
  function esc(v){var d=document.createElement('div');d.textContent=String(v==null?'':v);return d.innerHTML}
  function iconSrc(v,fallback){v=String(v||'').trim();if(!v)return fallback||'';if(/^(?:https?:)?\/\//i.test(v)||v.indexOf('/')!==-1)return v;return <?= json_encode(defined('ICON_URL')?rtrim((string)ICON_URL,'/').'/':'https://lolboost.gg/public/uploads/icons/') ?>+v}
  function stamp(v){if(!v)return'';var raw=String(v).trim(),d;if(/^\d{10,13}$/.test(raw)){var n=Number(raw);d=new Date(raw.length===10?n*1000:n)}else{var m=raw.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);d=m?new Date(Number(m[1]),Number(m[2])-1,Number(m[3]),Number(m[4]),Number(m[5])):new Date(raw)}if(isNaN(d.getTime()))return raw;var p=function(n){return String(n).padStart(2,'0')};return p(d.getDate())+'.'+p(d.getMonth()+1)+'.'+d.getFullYear()+' '+p(d.getHours())+':'+p(d.getMinutes())}
  function render(messages){if(!box)return;messages=(messages||[]).filter(function(m){return m&&!m.deleted});if(!messages.length){box.innerHTML='<div class="dgo-empty">No messages in this order yet.</div>';return}var last='';box.innerHTML=messages.map(function(m){var role=String(m.sender||m.type||'system').toLowerCase();if(role==='buyer')role='client';var body=String(m.message??m.text??m.body??m.content??''),image=String(m.image_url||m.image||''),name=m.sender_name||(role==='client'?'Buyer':role.charAt(0).toUpperCase()+role.slice(1)),icon=iconSrc(m.sender_icon,avatars[role]||''),time=stamp(m.created_at_fmt||m.created_at||m.time||''),grouped=last===role;last=role;if(role==='system')return '<div class="lb-syswrap"><div class="lb-sys">'+esc(body).replace(/\\n/g,'<br>')+'</div><div class="lb-sys-time">'+esc(time)+'</div></div>';var right=role==='seller'||role==='admin';return '<div class="lb-msg '+(right?'lb-msg--end':'lb-msg--start')+'">'+(grouped?'':'<div class="lb-msg__head '+(right?'lb-msg__head--end':'')+'"><img class="lb-msg__avatar" src="'+esc(icon)+'" alt=""><div class="lb-msg__name">'+esc(name)+' <span class="lb-badge lb-badge--'+role+'">'+(role==='client'?'Client':role.charAt(0).toUpperCase()+role.slice(1))+'</span></div></div>')+'<div class="lb-msg__bubble"><div class="lb-msg__content">'+esc(body).replace(/\\n/g,'<br>')+(image?'<a href="'+esc(image)+'" target="_blank"><img src="'+esc(image)+'" alt=""></a>':'')+'</div></div><div class="lb-msg__stamp">'+esc(time)+'</div></div>'}).join('');box.scrollTop=box.scrollHeight}
  document.querySelectorAll('.js-admin-poke-client').forEach(function(button){button.addEventListener('click',function(){button.disabled=true;var fd=new FormData;fd.set('action','admin_poke_client');fd.set('ref_type',button.dataset.refType);fd.set('id',button.dataset.id);fetch('<?= AJAX_URL ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){var t=d&&d.sendToast;if(t&&typeof create_toast==='function')create_toast(t.type||'primary',t.title||'Notice',t.message||'Done')}).finally(function(){button.disabled=false})})});
  if(file)file.addEventListener('change',function(){fileName.textContent=file.files&&file.files[0]?file.files[0].name:''});
  if(!form)return;
  form.addEventListener('submit',function(e){
    e.preventDefault();
    var button=form.querySelector('button[type=submit]');button.disabled=true;
    fetch('<?= AJAX_URL ?>',{method:'POST',body:new FormData(form),credentials:'same-origin'})
      .then(function(r){return r.json()})
      .then(function(d){
        if(d&&d.success){form.reset();fileName.textContent='';if(d.messages)render(d.messages);return}
        var message=d&&d.sendToast?d.sendToast.message:'Could not send message.';
        if(typeof create_toast==='function')create_toast('danger','Error',message);else alert(message);
      })
      .catch(function(){alert('Could not send message.')})
      .finally(function(){button.disabled=false});
  });
  function socketUpdate(data){data=data&&data.payload?data.payload:data;if(!data)return;if(Number(data.purchase_id||0)!==<?= $id ?>&&String(data.order_id||'')!=='dgpurch_<?= $id ?>')return;if(data.messages)render(data.messages)}
  render(<?= json_encode(array_values($chat ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
  function bindSocket(socket){if(!socket||socket.__adminDgOrderBound)return;socket.__adminDgOrderBound=true;socket.on('dg_chat_update',socketUpdate);try{socket.emit('dg_chat_init',{purchase_id:<?= $id ?>,area:'admin'})}catch(e){}}
  bindSocket(window.lbSocket);window.addEventListener('lb-socket-ready',function(e){bindSocket(e.detail&&e.detail.socket)});
})();
</script>
<?= $this->end() ?>
