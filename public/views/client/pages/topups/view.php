<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'Top Up Order | LoLBoost.gg', 'h1' => 'Top Up Order']]) ?>
<?php
$p = is_array($purchase ?? null) ? $purchase : [];
$checkoutData = is_array($checkoutData ?? null) ? $checkoutData : [];
if (isset($checkoutData['fields'])) {
    if (is_string($checkoutData['fields'])) { $tmp = json_decode($checkoutData['fields'], true); if (is_array($tmp)) $checkoutData = $tmp; }
    elseif (is_array($checkoutData['fields'])) { $checkoutData = $checkoutData['fields']; }
}
if (!function_exists('lb_to_h')) { function lb_to_h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('lb_to_money')) { function lb_to_money($cents,$cur='EUR'){ $sym = strtoupper((string)$cur)==='USD' ? '$' : '€'; return $sym . number_format(((int)$cents)/100, 2); } }
if (!function_exists('lb_to_amount')) { function lb_to_amount($v,$u=''){ $n = is_numeric($v) ? rtrim(rtrim(number_format((float)$v,2,'.',''),'0'),'.') : (string)$v; return trim($n . ' ' . (string)$u); } }
if (!function_exists('lb_to_wait')) { function lb_to_wait($r){ $v=(int)($r['waiting_time_value']??0); $u=strtolower((string)($r['waiting_time_unit']??'minutes')); if($v<=0 && !empty($r['waiting_time_minutes'])){$m=(int)$r['waiting_time_minutes']; if($m%1440===0){$v=(int)($m/1440);$u='days';}elseif($m%60===0){$v=(int)($m/60);$u='hours';}else{$v=$m;$u='minutes';}} if($v<=0){$v=0;$u='minutes';} $base=rtrim($u,'s'); return $v.' '.$base.($v===1?'':'s'); } }
$id = (int)($p['id'] ?? 0);
$game = ($p['game_name'] ?? '') ?: ($p['db_game_name'] ?? 'Game');
$icon = (string)($p['game_icon'] ?? '');
$offer = (string)($p['offer_title'] ?? $p['listing_offer_title'] ?? 'Top Up');
$amount = lb_to_amount($p['offer_amount'] ?? $p['listing_offer_amount'] ?? '', $p['offer_unit'] ?? $p['listing_offer_unit'] ?? '');
$status = strtoupper((string)($p['status'] ?? 'PAID'));
$statusCls = in_array(strtolower($status), ['paid','completed','delivered'], true) ? 'is-paid' : 'is-pending';
$price = (int)($p['price'] ?? 0);
$qty = max(1, (int)($p['quantity'] ?? 1));
$sellerName = (string)($p['seller_username'] ?? 'Seller');
$region = (string)($p['region'] ?? 'Global');
$platform = (string)($p['platform'] ?? '');
$created = (string)($p['created_at'] ?? '');
$topupMessages = [];
$__chatBase = defined('SYS_PATH') ? SYS_PATH : dirname(__DIR__, 2);
$__chatPath = $__chatBase . '/public/uploads/private/chat/selling_' . sha1('selling_topup_purchase_' . $id) . '.json';
if ($id > 0 && is_file($__chatPath)) {
    $__rawChat = @file_get_contents($__chatPath);
    $__chatData = json_decode($__rawChat ?: '', true);
    $__msgs = is_array($__chatData) && isset($__chatData['messages']) && is_array($__chatData['messages']) ? $__chatData['messages'] : [];
    $__iconCache = [];
    foreach ($__msgs as $__m) {
        if (!is_array($__m) || !empty($__m['deleted'])) continue;
        $__sender = strtolower((string)($__m['sender'] ?? $__m['type'] ?? 'seller'));
        $__senderId = (int)($__m['sender_id'] ?? 0);
        $__iconKey = $__sender . ':' . $__senderId;
        if (!isset($__iconCache[$__iconKey])) {
            $__storedIcon = trim((string)($__m['sender_icon'] ?? ''));
            if ($__storedIcon !== '') {
                $__iconCache[$__iconKey] = $__storedIcon;
            } elseif ($__senderId > 0 && function_exists('db_get_row')) {
                if ($__sender === 'client') $__row = db_get_row('clients', ['id' => $__senderId, 'select' => 'icon']);
                elseif ($__sender === 'seller') $__row = db_get_row('sellers', ['id' => $__senderId, 'select' => 'icon']);
                elseif ($__sender === 'admin') $__row = db_get_row('admins', ['id' => $__senderId, 'select' => 'icon']);
                else $__row = [];
                $__iconCache[$__iconKey] = !empty($__row['icon']) ? (string)$__row['icon'] : '';
            } else {
                $__iconCache[$__iconKey] = '';
            }
        }
        $__body = $__m['image_url'] ?? $__m['body'] ?? $__m['message'] ?? '';
        $topupMessages[] = [
            'id' => $__m['id'] ?? uniqid('msg_', true),
            'sender' => $__sender,
            'sender_id' => $__senderId,
            'sender_name' => $__m['sender_name'] ?? ucfirst($__sender),
            'sender_icon' => $__iconCache[$__iconKey] ?? '',
            'message_type' => $__m['message_type'] ?? (!empty($__m['image_url']) ? 'image' : 'text'),
            'body' => $__body,
            'created_at' => $__m['created_at'] ?? '',
            'created_at_fmt' => !empty($__m['created_at']) ? date('d.m.Y H:i', strtotime((string)$__m['created_at'])) : '',
            'seen' => (int)($__sender === 'client' ? ($__m['seen_by_seller'] ?? $__m['seen'] ?? 0) : ($__m['seen_by_client'] ?? $__m['seen'] ?? 0)),
            'seen_by_seller' => (int)($__m['seen_by_seller'] ?? ($__sender === 'seller' ? 1 : 0)),
            'seen_by_client' => (int)($__m['seen_by_client'] ?? ($__sender === 'client' ? 1 : 0)),
            'read_at' => $__m['read_at'] ?? null,
            'read_at_fmt' => !empty($__m['read_at']) ? date('d.m.Y H:i', strtotime((string)$__m['read_at'])) : '',
        ];
    }
}
?>
<style>
.lb-topup-order{max-width:1500px;margin:0 auto;padding:1.5rem}.lb-topup-back{display:inline-flex;align-items:center;gap:.45rem;margin-bottom:1rem;padding:.55rem 1rem;border-radius:12px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.35);color:#ddd6fe;text-decoration:none;font-weight:850}.lb-topup-back:hover{color:#fff;background:rgba(109,92,255,.28)}.lb-topup-head,.lb-topup-panel,.lb-topup-side{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:20px;overflow:hidden;box-shadow:0 16px 46px rgba(0,0,0,.22)}.lb-topup-head-main{padding:1.15rem 1.25rem;display:flex;align-items:center;gap:1rem}.lb-topup-icon{width:54px;height:54px;border-radius:14px;background:rgba(109,92,255,.16);border:1px solid rgba(109,92,255,.34);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#a78bfa;flex-shrink:0}.lb-topup-icon img{width:100%;height:100%;object-fit:contain;padding:6px}.lb-topup-title{margin:0;color:#fff;font-size:1.25rem;font-weight:950}.lb-topup-sub{display:flex;flex-wrap:wrap;gap:.4rem;color:#9ca3af;font-size:.82rem;margin-top:.2rem}.lb-topup-status{margin-left:auto;display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.35rem .75rem;font-size:.72rem;font-weight:950;text-transform:uppercase}.lb-topup-status.is-paid{background:rgba(34,197,94,.13);border:1px solid rgba(34,197,94,.28);color:#86efac}.lb-topup-status.is-pending{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.25);color:#fde047}.lb-topup-pills{padding:0 1.25rem 1rem;display:flex;gap:.45rem;flex-wrap:wrap}.lb-topup-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .65rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:#cbd5e1;font-size:.78rem;font-weight:800}.lb-topup-layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:1.25rem;align-items:start}.lb-topup-main{display:flex;flex-direction:column;gap:1.25rem}.lb-topup-card-head{padding:1rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between;gap:1rem}.lb-topup-card-title{margin:0;color:#fff;font-size:1.05rem;font-weight:950;display:flex;align-items:center;gap:.5rem}.lb-topup-card-sub{color:#9ca3af;font-size:.82rem;margin-top:.2rem}.lb-topup-chat-body{min-height:390px;max-height:520px;overflow:auto;padding:1rem 1.2rem;display:flex;flex-direction:column;gap:.65rem;background:rgba(0,0,0,.03)}.lb-topup-chat-form{display:flex;gap:.6rem;padding:.85rem 1rem;border-top:1px solid rgba(255,255,255,.08)}.lb-topup-input{flex:1;height:42px;border-radius:12px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.1);color:#fff;padding:0 .9rem;outline:none}.lb-topup-file{height:42px;min-width:42px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.11);display:flex;align-items:center;justify-content:center;color:#cbd5e1;cursor:pointer}.lb-topup-file input{display:none}.lb-topup-send{height:42px;border:0;border-radius:12px;background:#7c5cff;color:#fff;font-weight:950;padding:0 1rem}.lb-msg{max-width:74%;display:flex;flex-direction:column}.lb-msg.me{align-self:flex-end}.lb-msg.other{align-self:flex-start}.lb-bubble{padding:.65rem .85rem;border-radius:14px;background:rgba(255,255,255,.08);color:#fff;line-height:1.5;word-break:break-word}.lb-msg.me .lb-bubble{background:#7c5cff}.lb-bubble img{display:block;max-width:260px;max-height:220px;border-radius:12px}.lb-stamp{font-size:.7rem;color:#9ca3af;margin-top:.25rem}.lb-msg.me .lb-stamp{text-align:right}.lb-topup-side{position:sticky;top:88px}.lb-side-head{padding:1.05rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.08)}.lb-side-title{margin:0;color:#fff;font-size:1.25rem;font-weight:950}.lb-side-sub{color:#9ca3af;font-size:.83rem;margin-top:.2rem}.lb-side-row{display:flex;justify-content:space-between;gap:1rem;padding:.9rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.065)}.lb-side-row span{color:#9ca3af}.lb-side-row strong{text-align:right;color:#fff}.lb-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;padding:1rem 1.2rem}.lb-detail-box{background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:.85rem 1rem}.lb-detail-label{font-size:.64rem;font-weight:950;text-transform:uppercase;letter-spacing:.08em;color:#9fb3d1;margin-bottom:.4rem}.lb-detail-value{font-size:.9rem;font-weight:950;color:#fff;word-break:break-word}@media(max-width:1100px){.lb-topup-layout{grid-template-columns:1fr}.lb-topup-side{position:static}.lb-detail-grid{grid-template-columns:1fr}}@media(max-width:700px){.lb-topup-order{padding:1rem}.lb-topup-head-main{align-items:flex-start}.lb-topup-status{margin-left:0}.lb-topup-chat-form{flex-wrap:wrap}.lb-topup-input{min-width:100%;order:1}.lb-msg{max-width:88%}}


/* Top Up chat, aligned with item order view */
.lb-topup-panel.lb-topup-chat-panel{overflow:hidden}.lb-topup-chat-panel .lb-topup-card-head{padding:14px 20px}.lb-topup-chat-panel .lb-topup-card-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9)}.lb-topup-chat-panel .lb-topup-card-sub{font-size:.82rem;color:rgba(255,255,255,.46)}.lb-topup-chat-body{min-height:300px;max-height:480px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;gap:0;background:rgba(0,0,0,.03);scroll-behavior:smooth}.lb-topup-chat-body::-webkit-scrollbar{width:5px}.lb-topup-chat-body::-webkit-scrollbar-track{background:transparent}.lb-topup-chat-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:3px}.lb-topup-chat-footer{padding:.85rem 1rem;border-top:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.04)}.lb-topup-chat-form{display:flex;align-items:center;gap:.6rem;padding:0;border-top:0}.lb-topup-input{flex:1;height:42px;border-radius:12px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.1);color:#fff;padding:0 .9rem;outline:none}.lb-topup-input:focus{border-color:rgba(124,92,255,.45)}.lb-topup-file{height:42px;min-width:42px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.11);display:flex;align-items:center;justify-content:center;color:#cbd5e1;cursor:pointer}.lb-topup-send{height:42px;min-width:48px;border:0;border-radius:12px;background:#7c5cff;color:#fff;font-weight:950;padding:0 1rem;display:inline-flex;align-items:center;justify-content:center}.lb-topup-send:disabled{opacity:.65;cursor:not-allowed}.lb-msg{display:flex;flex-direction:column;margin-bottom:.5rem;max-width:75%}.lb-msg--start{align-self:flex-start}.lb-msg--end{align-self:flex-end}.lb-msg__bubble{padding:.55rem .85rem;border-radius:.75rem;font-size:.875rem;line-height:1.55;word-break:break-word;background:rgba(255,255,255,.07);color:#fff}.lb-msg--end .lb-msg__bubble{background:#7c5cff}.lb-msg__stamp{font-size:.7rem;opacity:.55;margin-top:.2rem;color:#9ca3af}.lb-msg--end .lb-msg__stamp{text-align:right}.lb-msg__content img{max-width:240px;max-height:200px;border-radius:.5rem;display:block;cursor:pointer}.lb-chat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:240px;opacity:.45;gap:.5rem;text-align:center;color:#cbd5e1}.lb-chat-preview{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.4rem .7rem}.lb-chat-preview img{width:2.5rem;height:2.5rem;object-fit:cover;border-radius:.35rem}.lb-chat-preview__remove{background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;padding:0 .2rem}.lb-topup-chat-tip{color:#9ca3af;font-size:.78rem;margin-top:.55rem}@media(max-width:768px){.lb-topup-chat-body{min-height:220px;max-height:340px}.lb-msg{max-width:88%}.lb-topup-chat-form{gap:.5rem}.lb-topup-input{min-width:0;order:0}.lb-topup-send{padding:0 .85rem}}

.lb-msg__head{display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem}
.lb-msg__head--end{flex-direction:row-reverse}
.lb-msg__avatar{width:1.75rem;height:1.75rem;border-radius:50%;object-fit:cover;flex-shrink:0;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12)}.lb-msg__avatar-fallback{width:1.75rem;height:1.75rem;border-radius:50%;flex-shrink:0;background:rgba(124,92,255,.18);border:1px solid rgba(124,92,255,.3);display:inline-flex;align-items:center;justify-content:center;color:#ddd6fe;font-size:.72rem;font-weight:950;text-transform:uppercase}
.lb-msg__name{font-weight:800;font-size:.8rem;line-height:1.3;display:flex;align-items:center;gap:.35rem;color:rgba(255,255,255,.88)}
.lb-badge{display:inline-flex;align-items:center;padding:.1rem .4rem;border-radius:999px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.lb-badge--seller{background:rgba(99,102,241,.2);color:#a5b4fc}
.lb-badge--client{background:rgba(16,185,129,.15);color:#34d399}
.lb-badge--admin{background:rgba(245,158,11,.15);color:#fbbf24}
.lb-msg.is-grouped{margin-top:-.18rem}
.lb-msg.is-grouped .lb-msg__bubble{margin-top:0}


.lb-read-receipt{display:inline-flex;align-items:center;margin-left:.3rem;font-size:.72rem;font-weight:900;letter-spacing:-.18em;color:rgba(255,255,255,.34);vertical-align:middle}.lb-read-receipt.is-seen{color:#8b7cff}.lb-read-receipt__label{letter-spacing:0;margin-left:.38rem;font-size:.66rem;font-weight:700;color:inherit}
/* Client Top Up view aligned with Item Order view */
.lb-topup-order{max-width:none;margin:0;padding:0}.lb-topup-layout{display:grid;grid-template-columns:minmax(0,7fr) minmax(330px,5fr);gap:1.5rem;align-items:start}.lb-topup-main{display:block}.lb-topup-head{grid-column:1/-1;margin-bottom:1.5rem;border-radius:22px;box-shadow:none}.lb-topup-head-main{padding:20px 22px}.lb-topup-pills{padding:14px 22px 16px;border-top:1px solid rgba(255,255,255,.08)}.lb-topup-panel,.lb-topup-side{border-radius:18px;box-shadow:none}.lb-topup-chat-panel{margin:0}.lb-topup-side{position:static}.lb-topup-back{margin:0;padding:7px 14px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.7)}.lb-topup-back:hover{background:rgba(255,255,255,.09);color:#fff}.lb-topup-head-actions{margin-left:auto;display:flex;align-items:center;gap:8px}.lb-topup-status{margin-left:0}.lb-topup-delivery{margin-top:1rem}@media(max-width:1100px){.lb-topup-layout{grid-template-columns:1fr}.lb-topup-head{grid-column:1}.lb-topup-side{position:static}}@media(max-width:700px){.lb-topup-order{padding:0}.lb-topup-head-main{padding:14px 16px;flex-wrap:wrap}.lb-topup-head-actions{width:100%;justify-content:space-between}.lb-topup-pills{padding:10px 16px 12px}}


.rp-modal-overlay{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;pointer-events:none;transition:opacity .2s}.rp-modal-overlay.is-open{opacity:1;pointer-events:all}.rp-modal{width:100%;max-width:480px;background:#1e2022;border:1px solid rgba(255,255,255,.1);border-radius:20px;overflow:hidden;transform:translateY(16px) scale(.97);transition:transform .2s;box-shadow:0 24px 60px rgba(0,0,0,.5)}.rp-modal-overlay.is-open .rp-modal{transform:none}.rp-modal-header{display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07)}.rp-modal-title{font-size:.95rem;font-weight:900;color:#fff;flex:1}.rp-modal-close{background:none;border:0;color:rgba(255,255,255,.4)}.rp-modal-body{padding:20px}.rp-problems{display:grid;gap:7px;margin-bottom:14px}.rp-problem-opt{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:11px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);cursor:pointer}.rp-problem-opt.is-selected{border-color:rgba(239,68,68,.5);background:rgba(239,68,68,.1)}.rp-problem-opt input{display:none}.rp-details{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:#fff;padding:10px 12px;resize:none}.rp-modal-footer{padding:0 20px 20px;display:flex;justify-content:flex-end;gap:8px}.rp-submit,.rp-cancel{padding:9px 16px;border-radius:11px;font-weight:800}.rp-submit{background:rgba(239,68,68,.18);border:1px solid rgba(239,68,68,.3);color:#f87171}.rp-cancel{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:#bbb}.rp-success{text-align:center;padding:28px 20px}.av-btn-danger{display:inline-flex;align-items:center;gap:.4rem;padding:7px 14px;border-radius:11px;font-size:.83rem;font-weight:700;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;cursor:pointer}.av-btn-ghost{display:inline-flex;align-items:center;gap:.4rem;padding:7px 14px;border-radius:11px;font-size:.83rem;font-weight:700;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:#ccc;text-decoration:none}.lb-topup-head-actions{margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap}.lb-topup-status{margin-left:0}
</style>
<div class="lb-topup-order">
  <section class="lb-topup-head">
    <div class="lb-topup-head-main">
      <div class="lb-topup-icon"><?php if($icon): ?><img src="<?= lb_to_h($icon) ?>" alt="<?= lb_to_h($game) ?>"><?php else: ?><i class="fa-duotone fa-coins"></i><?php endif; ?></div>
      <div style="min-width:0;flex:1"><div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap"><h1 class="lb-topup-title"><?= lb_to_h($offer) ?></h1><span class="lb-topup-status <?= $statusCls ?>"><?= lb_to_h($status) ?></span></div><div class="lb-topup-sub"><span>#<?= $id ?></span><span>·</span><span><?= lb_to_h($created ? date('d.m.Y', strtotime($created)) : '') ?></span><span>·</span><span><i class="fa-solid fa-store me-1"></i><?= lb_to_h($sellerName) ?></span></div></div>
      <div class="lb-topup-head-actions"><?php if (!empty($p['seller_id'])): ?><button type="button" class="lb-topup-back js-client-poke-seller" data-id="<?= $id ?>"><i class="fa-solid fa-hand-point-up"></i> Poke Seller</button><?php endif; ?><button type="button" class="av-btn-danger" id="reportProblemBtn"><i class="fa-solid fa-flag"></i> Report a Problem</button><a class="lb-topup-back" href="<?= BASE_URL ?>/profile/orders?type=topup"><i class="fa-solid fa-arrow-left"></i> Back</a></div>
    </div>
    <div class="lb-topup-pills"><span class="lb-topup-pill"><i class="fa-solid fa-coins"></i> <?= lb_to_h($amount ?: 'Top Up') ?></span><span class="lb-topup-pill"><i class="fa-solid fa-globe"></i> <?= lb_to_h($region ?: 'Global') ?></span><?php if($platform): ?><span class="lb-topup-pill"><i class="fa-solid fa-desktop"></i> <?= lb_to_h($platform) ?></span><?php endif; ?><span class="lb-topup-pill"><i class="fa-solid fa-layer-group"></i> Qty <?= $qty ?></span><span class="lb-topup-pill"><i class="fa-solid fa-clock"></i> <?= lb_to_h(lb_to_wait($p)) ?></span></div>
  </section>
  <div class="lb-topup-layout">
    <main class="lb-topup-main">
      <section class="lb-topup-panel lb-topup-chat-panel">
        <div class="lb-topup-card-head">
          <div>
            <h2 class="lb-topup-card-title"><i class="fa-duotone fa-comments" style="color:#9f8cff"></i> Seller Chat</h2>
            <div class="lb-topup-card-sub">Chat directly with the seller for this top up order.</div>
          </div>
          <span class="lb-topup-pill"><?= lb_to_h($sellerName) ?></span>
        </div>
        <div class="lb-topup-chat-body" id="topupChatMessages"></div>
        <div class="lb-topup-chat-footer">
          <form id="topupChatForm" class="lb-topup-chat-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="client_topup_chat_send">
            <input type="hidden" name="purchase_id" value="<?= $id ?>">
            <input type="file" name="chat_image" id="topupChatImageInput" accept="image/*" class="d-none">
            <button type="button" class="lb-topup-file" id="topupChatUploadBtn" title="Attach image"><i class="fa-duotone fa-paperclip"></i></button>
            <input class="lb-topup-input" name="message" id="topupChatMessageInput" placeholder="Type your message to the seller" autocomplete="off">
            <button class="lb-topup-send" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            <div class="w-100 mt-2 d-none" id="topupChatImagePreviewWrap">
              <div class="lb-chat-preview">
                <img id="topupChatImagePreview" src="" alt="preview">
                <button type="button" class="lb-chat-preview__remove" id="topupChatImageRemove"><i class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
          </form>
          <div class="lb-topup-chat-tip">Tip: You can paste a screenshot with <strong>Ctrl + V</strong>.</div>
        </div>
      </section>
      <section class="lb-topup-panel lb-topup-delivery"><div class="lb-topup-card-head"><div><h2 class="lb-topup-card-title"><i class="fa-duotone fa-list-check" style="color:#93c5fd"></i> Delivery Details</h2><div class="lb-topup-card-sub">Information needed to complete your top up.</div></div></div><div class="lb-detail-grid"><?php if($checkoutData): foreach($checkoutData as $k=>$v): if(is_array($v)) $v=json_encode($v); ?><div class="lb-detail-box"><div class="lb-detail-label"><?= lb_to_h(ucwords(str_replace(['_','-'],' ',(string)$k))) ?></div><div class="lb-detail-value"><?= lb_to_h($v) ?></div></div><?php endforeach; else: ?><div class="lb-detail-box"><div class="lb-detail-value">No extra details saved.</div></div><?php endif; ?></div></section>
    </main>
    <aside class="lb-topup-side"><div class="lb-side-head"><h2 class="lb-side-title">Order Overview</h2><div class="lb-side-sub">Payment and delivery</div></div><div class="lb-side-row"><span>Total</span><strong><?= lb_to_money($price,$p['currency']??'EUR') ?></strong></div><div class="lb-side-row"><span>Order ID</span><strong>#<?= $id ?></strong></div><div class="lb-side-row"><span>Game</span><strong><?= lb_to_h($game) ?></strong></div><div class="lb-side-row"><span>Seller</span><strong><?= lb_to_h($sellerName) ?></strong></div><div class="lb-side-row"><span>Waiting Time</span><strong><?= lb_to_h(lb_to_wait($p)) ?></strong></div><div class="lb-side-row"><span>Created</span><strong><?= lb_to_h($created) ?></strong></div></aside>
  </div>
</div>

<div class="rp-modal-overlay" id="rpOverlay" role="dialog" aria-modal="true">
  <div class="rp-modal">
    <div class="rp-modal-header"><i class="fa-solid fa-flag" style="color:#f87171"></i><div class="rp-modal-title">Report a Problem</div><button class="rp-modal-close" id="rpClose"><i class="fa-solid fa-xmark"></i></button></div>
    <div id="rpFormWrap"><div class="rp-modal-body"><div class="rp-problems">
      <?php foreach ([['delivery','Delivery is taking too long'],['wrong_amount','Wrong amount or product'],['seller','Seller is not responding'],['account','Problem with submitted details'],['other','Other problem']] as $rp): ?>
      <label class="rp-problem-opt" data-id="<?= $rp[0] ?>"><input type="radio" name="rp_issue"><i class="fa-solid fa-circle-exclamation"></i><span><?= $rp[1] ?></span></label>
      <?php endforeach; ?>
      </div><textarea class="rp-details" id="rpDetails" rows="4" maxlength="1000" placeholder="Describe the problem"></textarea></div>
      <div class="rp-modal-footer"><button class="rp-cancel" id="rpCancelBtn">Cancel</button><button class="rp-submit" id="rpSubmitBtn" disabled><i class="fa-solid fa-paper-plane"></i> Send Report</button></div>
    </div>
    <div class="rp-success" id="rpSuccessWrap" style="display:none"><i class="fa-solid fa-circle-check fa-2x" style="color:#4ade80"></i><h3 style="margin-top:10px">Report sent</h3><p style="color:#999">Support and the seller have been notified.</p><button class="rp-cancel" id="rpSuccessClose">Close</button></div>
  </div>
</div>
<script>
(function(){
  const box = document.getElementById('topupChatMessages');
  const form = document.getElementById('topupChatForm');
  const fileInput = document.getElementById('topupChatImageInput');
  const uploadBtn = document.getElementById('topupChatUploadBtn');
  const previewWrap = document.getElementById('topupChatImagePreviewWrap');
  const previewImg = document.getElementById('topupChatImagePreview');
  const removePreviewBtn = document.getElementById('topupChatImageRemove');
  const messageInput = document.getElementById('topupChatMessageInput');
  const pid = '<?= $id ?>';
  window.PURCHASE_ID = pid;
  const initialMessages = <?= json_encode($topupMessages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
  const ajaxUrl = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
  if (!window.AJAX_URL) window.AJAX_URL = ajaxUrl;

  document.querySelectorAll('.js-client-poke-seller').forEach(function(btn){
    btn.addEventListener('click',function(){
      if(btn.disabled)return;
      const oldHtml=btn.innerHTML;let cooldownStarted=false;
      btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      function startCooldown(seconds){
        let remaining=Math.max(1,parseInt(seconds,10)||300);cooldownStarted=true;
        function render(){const mins=Math.floor(remaining/60),secs=String(remaining%60).padStart(2,'0');btn.innerHTML='<i class="fa-solid fa-clock"></i> Poke again in '+mins+':'+secs;if(remaining--<=0){clearInterval(timer);btn.disabled=false;btn.innerHTML=oldHtml;}}
        render();const timer=setInterval(render,1000);
      }
      $.post(ajaxUrl,{action:'client_poke_seller',ref_type:'topup',id:btn.dataset.id||pid},function(resp){
        let data=resp;try{if(typeof resp==='string')data=JSON.parse(resp);}catch(e){}
        if(data&&data.sendToast&&typeof create_toast==='function')create_toast(data.sendToast.type||'primary',data.sendToast.title||'Notice',data.sendToast.message||'Done');
        if(data&&data.cooldown_seconds)startCooldown(data.cooldown_seconds);
      }).always(function(){if(!cooldownStarted){btn.disabled=false;btn.innerHTML=oldHtml;}});
    });
  });

  // Register sending before any realtime/read-receipt initialization.
  if (form && !form.dataset.lbSubmitBound) {
    form.dataset.lbSubmitBound = '1';
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      e.stopPropagation();

      const btn = form.querySelector('button[type="submit"]');
      const textValue = messageInput ? String(messageInput.value || '').trim() : '';
      const hasFile = !!(fileInput && fileInput.files && fileInput.files.length);

      if (!textValue && !hasFile) return;
      if (btn) btn.disabled = true;

      try {
        const fd = new FormData(form);
        fd.set('action', 'client_topup_chat_send');
        fd.set('purchase_id', pid);

        const response = await fetch(ajaxUrl, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: {'X-Requested-With': 'XMLHttpRequest'}
        });

        const raw = await response.text();
        let data = null;
        try {
          data = JSON.parse(raw);
        } catch (parseError) {
          console.error('Invalid Top Up chat response:', raw);
          throw new Error('Invalid server response');
        }

        if (!data || data.success !== true) {
          const errorMessage =
            (data && data.message) ||
            (data && data.sendToast && data.sendToast.message) ||
            'Message could not be sent.';
          throw new Error(errorMessage);
        }

        if (messageInput) messageInput.value = '';
        clearPreview();
        lastSig = '';

        if (Array.isArray(data.messages)) {
          upsertMessages(data.messages);
        } else if (data.chat_message && typeof data.chat_message === 'object') {
          appendMessage(data.chat_message);
        } else if (data.message && typeof data.message === 'object') {
          appendMessage(data.message);
        }
      } catch (error) {
        console.error('Top Up chat send failed:', error);
        const message = error && error.message ? error.message : 'Message could not be sent. Please try again.';
        if (typeof create_toast === 'function') {
          create_toast('danger', 'Message failed', message);
        } else {
          alert(message);
        }
      } finally {
        if (btn) btn.disabled = false;
        if (messageInput) messageInput.focus();
      }
    });
  }

  let lastSig = '';
  let previewUrl = null;

  function esc(s){
    return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  function normalizeText(s){
    return esc(s).replace(/\n/g, '<br>');
  }

  function clearPreview(){
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    if (fileInput) fileInput.value = '';
    if (previewImg) previewImg.src = '';
    if (previewWrap) previewWrap.classList.add('d-none');
  }

  function showPreview(file){
    if (!file || !file.type || !file.type.startsWith('image/')) return;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    if (previewImg) previewImg.src = previewUrl;
    if (previewWrap) previewWrap.classList.remove('d-none');
  }

  function senderLabel(sender, me){
    sender = String(sender || '').toLowerCase();
    if (me) return {name:'You', badge:'You', badgeCls:'lb-badge--client'};
    if (sender === 'admin') return {name:'LoLBoost Support', badge:'Admin', badgeCls:'lb-badge--admin'};
    if (sender === 'seller') return {name:'<?= lb_to_h($sellerName) ?>', badge:'Seller', badgeCls:'lb-badge--seller'};
    return {name:'Seller', badge:'Seller', badgeCls:'lb-badge--seller'};
  }

  function senderAvatarHtml(icon, name){
    icon = String(icon || '').trim();
    name = String(name || '?').trim();
    if (icon) return '<img class="lb-msg__avatar" src="' + esc(icon) + '" alt="">';
    return '<span class="lb-msg__avatar-fallback">' + esc((name.charAt(0) || '?').toUpperCase()) + '</span>';
  }

  function isSeenBySeller(message){
    return Number(message && (message.seen_by_seller ?? message.seen ?? message.is_read ?? 0)) === 1;
  }

  function readReceiptHtml(message, me){
    if (!me) return '';
    const seen = isSeenBySeller(message);
    const label = seen ? 'Read' : 'Delivered';
    return '<span class="lb-read-receipt' + (seen ? ' is-seen' : '') + '" title="' + label + '">✓✓<span class="lb-read-receipt__label">' + label + '</span></span>';
  }

  function render(list){
    if (!box) return;
    const sig = JSON.stringify(list || []);
    if (sig === lastSig) return;
    lastSig = sig;

    if (!list || !list.length) {
      box.innerHTML = '<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet. Start the conversation!</span></div>';
      return;
    }

    let lastSender = '';
    let lastSenderName = '';
    box.innerHTML = list.map(m => {
      const senderType = String(m.sender || '').toLowerCase();
      const me = senderType === 'client';
      const align = me ? 'lb-msg--end' : 'lb-msg--start';
      const body = m.message_type === 'image'
        ? '<img src="' + esc(m.body) + '" alt="chat image">'
        : normalizeText(m.body);
      const fallback = senderLabel(senderType, me);
      const senderNameRaw = me ? 'You' : (m.sender_name || fallback.name);
      const senderName = esc(senderNameRaw);
      const stamp = esc(m.created_at_fmt || m.created_at || '');
      const grouped = senderType === lastSender && String(senderNameRaw) === String(lastSenderName);
      lastSender = senderType;
      lastSenderName = senderNameRaw;
      const avatar = senderAvatarHtml(m.sender_icon || m.icon || '', senderNameRaw);
      const head = grouped ? '' : '<div class="lb-msg__head ' + (me ? 'lb-msg__head--end' : '') + '">' + avatar + '<div class="lb-msg__name">' + senderName + ' <span class="lb-badge ' + fallback.badgeCls + '">' + fallback.badge + '</span></div></div>';
      return '<div class="lb-msg ' + align + (grouped ? ' is-grouped' : '') + '">' + head + '<div class="lb-msg__bubble"><div class="lb-msg__content">' + body + '</div></div><div class="lb-msg__stamp">' + stamp + readReceiptHtml(m, me) + '</div></div>';
    }).join('');

    box.scrollTop = box.scrollHeight;
  }

  let currentMessages = Array.isArray(initialMessages) ? initialMessages.slice() : [];
  let topupReadActivated = false;
  let topupReadRequestRunning = false;

  function upsertMessages(list){
    if (!Array.isArray(list)) return;
    currentMessages = list;
    render(currentMessages);
  }

  function appendMessage(message){
    if (!message) return;
    const id = String(message.id || '');
    if (id && currentMessages.some(m => String(m.id || '') === id)) return;
    currentMessages.push(message);
    render(currentMessages);
  }

  window.lbTopupChatUpdate = function(data){
    data = data || {};
    const payload = (data.data && typeof data.data === 'object') ? data.data : data;
    const incomingPurchaseId = String(payload.purchase_id || payload.id || '');
    const incomingOrderId = String(payload.order_id || '');
    if (incomingPurchaseId && incomingPurchaseId !== String(pid)) return;
    if (!incomingPurchaseId && incomingOrderId && incomingOrderId !== ('topuppurch_' + String(pid))) return;

    if (Array.isArray(payload.messages)) upsertMessages(payload.messages);
    else if (payload.chat_message) appendMessage(payload.chat_message);
    else if (payload.message && typeof payload.message === 'object') appendMessage(payload.message);
    else loadTopupMessages(false);

    if (topupReadActivated && chatIsVisible() && !payload.read_receipt_update) {
      setTimeout(function(){ loadTopupMessages(true); }, 80);
    }
  };

  window.lbOrderViewChatUpdate = function(data){
    data = data || {};
    const payload = (data.data && typeof data.data === 'object') ? data.data : data;
    if (String(payload.order_id || '') !== ('topuppurch_' + String(pid)) && String(payload.purchase_id || '') !== String(pid)) return;
    window.lbTopupChatUpdate(payload);
  };

  async function loadTopupMessages(markSeen = false){
    if (markSeen && (!topupReadActivated || !chatIsVisible() || topupReadRequestRunning)) return;
    if (markSeen) topupReadRequestRunning = true;
    try {
      const fd = new FormData();
      fd.set('action', 'topup_chat_load');
      fd.set('purchase_id', pid);
      if (markSeen) fd.set('mark_seen', '1');
      const res = await fetch(ajaxUrl, {method:'POST', body:fd, credentials:'same-origin'});
      const data = await res.json().catch(() => null);
      if (data && data.success === true && Array.isArray(data.messages)) upsertMessages(data.messages);
    } catch (e) {
      console.warn('Top up chat load failed', e);
    } finally {
      if (markSeen) topupReadRequestRunning = false;
    }
  }

  let boundSocket = null;
  function bindTopupSocket(){
    const s = window.lbSocket || window.socket || null;
    if (!s || s === boundSocket) return;
    boundSocket = s;

    try { s.emit('join', 'clients'); } catch(e) {}
    try { s.emit('join', 'client_' + String(pid)); } catch(e) {}
    try { s.emit('topup_chat_init', {purchase_id: pid, area: 'client'}); } catch(e) {}

  }

  function chatIsVisible(){
    if (document.visibilityState !== 'visible' || !box) return false;
    const rect = box.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  async function markVisibleMessagesRead(){
    if (!topupReadActivated || !chatIsVisible()) return;
    await loadTopupMessages(true);
  }
  function activateTopupRead(){
    if (document.visibilityState !== 'visible') return;
    topupReadActivated = true;
    markVisibleMessagesRead();
  }
  function bindTopupReadInteraction(){
    [box, messageInput, form].forEach(function(el){
      if (!el || el.dataset.lbReadBound === '1') return;
      el.dataset.lbReadBound='1';
      ['pointerdown','click','touchstart','wheel','scroll','focusin','keydown'].forEach(function(ev){
        el.addEventListener(ev, activateTopupRead, {passive:true});
      });
    });
  }

  bindTopupSocket();
  let socketBindAttempts = 0;
  const socketBindTimer = setInterval(function(){
    socketBindAttempts++;
    bindTopupSocket();
    if (boundSocket || socketBindAttempts >= 20) clearInterval(socketBindTimer);
  }, 250);
  window.addEventListener('lb-socket-ready', bindTopupSocket);
  window.addEventListener('load', function(){
    bindTopupSocket();
    setTimeout(bindTopupSocket, 350);
    setTimeout(bindTopupSocket, 1200);
    bindTopupReadInteraction();
  });

  render(currentMessages);
  bindTopupReadInteraction();
  loadTopupMessages(false);
  document.addEventListener('visibilitychange', function(){
    if (document.visibilityState === 'visible') {
      bindTopupSocket();
    }
  });

  if (uploadBtn && fileInput) {
    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => showPreview(fileInput.files && fileInput.files[0]));
  }
  if (removePreviewBtn) removePreviewBtn.addEventListener('click', clearPreview);

  document.addEventListener('paste', function(e){
    if (!form || !fileInput || !form.contains(document.activeElement)) return;
    for (const item of (e.clipboardData && e.clipboardData.items ? e.clipboardData.items : [])) {
      if (item.kind === 'file' && item.type.startsWith('image/')) {
        const blob = item.getAsFile();
        if (!blob) continue;
        const file = new File([blob], 'pasted.png', {type: blob.type});
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showPreview(file);
        e.preventDefault();
        break;
      }
    }
  });



  document.addEventListener('click', function(e){
    const img = e.target.closest('#topupChatMessages img');
    if (!img) return;
    window.open(img.src, '_blank', 'noopener');
  });

})();
</script>
<script>
// Report-a-Problem modal runs in its own scope, so a chat script error can never block it.
(function(){
  const pid = '<?= $id ?>';
  const ajaxUrl = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
  (function(){
    const overlay=document.getElementById('rpOverlay'), openBtn=document.getElementById('reportProblemBtn'), closeBtn=document.getElementById('rpClose'), cancelBtn=document.getElementById('rpCancelBtn'), submitBtn=document.getElementById('rpSubmitBtn'), details=document.getElementById('rpDetails'), success=document.getElementById('rpSuccessWrap'), formWrap=document.getElementById('rpFormWrap');
    let issue='', label='';
    const close=()=>{overlay.classList.remove('is-open');document.body.style.overflow=''};
    openBtn?.addEventListener('click',()=>{overlay.classList.add('is-open');document.body.style.overflow='hidden'}); closeBtn?.addEventListener('click',close); cancelBtn?.addEventListener('click',close); document.getElementById('rpSuccessClose')?.addEventListener('click',close);
    document.querySelectorAll('.rp-problem-opt').forEach(o=>o.addEventListener('click',()=>{document.querySelectorAll('.rp-problem-opt').forEach(x=>x.classList.remove('is-selected'));o.classList.add('is-selected');issue=o.dataset.id||'';label=o.textContent.trim();submitBtn.disabled=!issue;}));
    submitBtn?.addEventListener('click',async()=>{if(!issue)return;submitBtn.disabled=true;submitBtn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Sending…';try{const fd=new FormData();fd.set('action','client_topup_report_problem');fd.set('purchase_id',pid);fd.set('issue',issue);fd.set('issue_label',label);fd.set('details',details?.value.trim()||'');const r=await fetch(ajaxUrl,{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.success)throw new Error(d.message||'Failed');formWrap.style.display='none';success.style.display='block';}catch(e){submitBtn.disabled=false;submitBtn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send Report';if(typeof create_toast==='function')create_toast('danger','Error','Could not send report. Please try again.');else alert('Could not send report.');}});
  })();
})();
</script>
