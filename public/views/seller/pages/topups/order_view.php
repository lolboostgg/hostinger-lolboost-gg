<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
require_once dirname(__DIR__) . '/_orders_shared.php';

$p = is_array($purchase ?? null) ? $purchase : [];
$checkoutData = is_array($checkoutData ?? null) ? $checkoutData : [];
if (isset($checkoutData['fields'])) {
    if (is_string($checkoutData['fields'])) {
        $tmp = json_decode($checkoutData['fields'], true);
        if (is_array($tmp)) $checkoutData = $tmp;
    } elseif (is_array($checkoutData['fields'])) {
        $checkoutData = $checkoutData['fields'];
    }
}

if (!function_exists('lb_topup_h')) {
    function lb_topup_h($v): string {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('lb_topup_money_value')) {
    function lb_topup_money_value($cents): float {
        return round(((float)$cents) / 100, 2);
    }
}
if (!function_exists('lb_topup_amount')) {
    function lb_topup_amount($v, $u = ''): string {
        $n = is_numeric($v) ? rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') : (string)$v;
        return trim($n . ' ' . (string)$u);
    }
}
if (!function_exists('lb_topup_wait')) {
    function lb_topup_wait(array $r): string {
        $v = (int)($r['waiting_time_value'] ?? 0);
        $u = strtolower((string)($r['waiting_time_unit'] ?? 'minutes'));
        if ($v <= 0 && !empty($r['waiting_time_minutes'])) {
            $m = (int)$r['waiting_time_minutes'];
            if ($m % 1440 === 0) {
                $v = (int)($m / 1440);
                $u = 'days';
            } elseif ($m % 60 === 0) {
                $v = (int)($m / 60);
                $u = 'hours';
            } else {
                $v = $m;
                $u = 'minutes';
            }
        }
        if ($v <= 0) return 'Instant';
        $base = rtrim($u, 's');
        return $v . ' ' . $base . ($v === 1 ? '' : 's');
    }
}
if (!function_exists('lb_topup_asset_url')) {
    function lb_topup_asset_url($path): string {
        $path = trim((string)$path);
        if ($path === '') return '';
        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;
        if (defined('BASE_URL') && str_starts_with($path, '/public/')) return rtrim(BASE_URL, '/') . $path;
        $assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : rtrim(BASE_URL, '/') . '/public/assets';
        $path = preg_replace('#^/public/assets#', '', $path);
        $path = preg_replace('#^public/assets#', '', $path);
        return $assetUrl . '/' . ltrim($path, '/');
    }
}

$id = (int)($p['id'] ?? $p['purchase_id'] ?? 0);
$game = (string)(($p['game_name'] ?? '') ?: ($p['db_game_name'] ?? 'Game'));
$icon = lb_topup_asset_url((string)($p['game_icon'] ?? ''));
$offer = (string)($p['offer_title'] ?? $p['listing_offer_title'] ?? 'Top Up');
$amount = lb_topup_amount(
    $p['offer_amount'] ?? $p['listing_offer_amount'] ?? '',
    $p['offer_unit'] ?? $p['listing_offer_unit'] ?? ''
);
$qty = max(1, (int)($p['quantity'] ?? 1));
$region = (string)($p['region'] ?? 'Global');
$platform = (string)($p['platform'] ?? '');
$currency = strtoupper((string)($p['currency'] ?? 'EUR'));
$sym = $currency === 'USD' ? '$' : ($currency === 'GBP' ? '£' : '€');
$total = lb_topup_money_value($p['price'] ?? $p['total_price'] ?? 0);
$unit = $qty > 0 ? round($total / $qty, 2) : $total;

$effectiveFee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$payoutRaw = $p['seller_earnings'] ?? $p['seller_payout'] ?? null;
$payout = ($payoutRaw !== null && $payoutRaw !== '')
    ? lb_topup_money_value($payoutRaw)
    : round($total * (1 - ($effectiveFee / 100)), 2);

$statusRaw = strtolower(trim((string)($p['status'] ?? 'pending')));
$status = in_array($statusRaw, ['completed', 'delivered', 'success', 'fulfilled'], true)
    ? 'Delivered'
    : (in_array($statusRaw, ['cancelled', 'canceled', 'failed', 'refunded', 'chargeback'], true) ? 'Cancelled' : 'Pending');
$badgeCls = $status === 'Delivered' ? 'av-status--active' : ($status === 'Cancelled' ? 'av-status--sold' : 'av-status--unlisted');
$statusIcon = $status === 'Delivered' ? 'fa-check' : ($status === 'Cancelled' ? 'fa-xmark' : 'fa-clock');

$buyerRow = is_array($buyer ?? null) ? $buyer : [];
$clientName = (string)($buyerRow['username'] ?? $p['client_username'] ?? $p['buyer_username'] ?? 'Client');
$clientAvatar = lb_topup_asset_url((string)($buyerRow['icon'] ?? $buyerRow['avatar'] ?? $p['client_icon'] ?? $p['client_avatar'] ?? $p['buyer_avatar'] ?? ''));
$sellerRow = is_array($seller_data ?? null) ? $seller_data : [];
$sellerName = (string)($sellerRow['username'] ?? $p['seller_username'] ?? 'Seller');
$sellerAvatar = lb_topup_asset_url((string)($sellerRow['icon'] ?? $sellerRow['avatar'] ?? $p['seller_icon'] ?? $p['seller_avatar'] ?? ''));
$created = (string)($p['created_at'] ?? $p['ordered_at'] ?? '');
$orderCode = (string)($p['order_code'] ?? $p['invoice_id'] ?? $p['payment_id'] ?? '');

$initialTopupMessages = [];
$topupChatBase = defined('SYS_PATH') ? SYS_PATH : (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3));
$topupChatPath = rtrim((string)$topupChatBase, '/\\') . '/public/uploads/private/chat/selling_' . sha1('selling_topup_purchase_' . $id) . '.json';
if ($id > 0 && is_file($topupChatPath)) {
    $topupChatRaw = @file_get_contents($topupChatPath);
    $topupChatData = json_decode($topupChatRaw ?: '', true);
    $topupChatRows = [];
    if (is_array($topupChatData)) {
        $topupChatRows = isset($topupChatData['messages']) && is_array($topupChatData['messages'])
            ? $topupChatData['messages']
            : $topupChatData;
    }
    foreach ((array)$topupChatRows as $m) {
        if (!is_array($m) || !empty($m['deleted'])) continue;
        $sender = (string)($m['sender'] ?? $m['type'] ?? 'client');
        $createdAt = (string)($m['created_at'] ?? $m['time'] ?? '');
        $body = (string)($m['image_url'] ?? $m['body'] ?? $m['message'] ?? $m['content'] ?? '');
        $messageType = (string)($m['message_type'] ?? (!empty($m['image_url']) ? 'image' : 'text'));
        $initialTopupMessages[] = [
            'id' => (string)($m['id'] ?? uniqid('msg_', true)),
            'sender' => $sender,
            'sender_id' => (int)($m['sender_id'] ?? 0),
            'sender_name' => (string)($m['sender_name'] ?? ucfirst($sender)),
            'sender_avatar' => (string)($m['sender_avatar'] ?? $m['avatar'] ?? (strtolower($sender) === 'seller' ? $sellerAvatar : $clientAvatar)),
            'message_type' => $messageType,
            'body' => $body,
            'message' => $body,
            'created_at' => $createdAt,
            'created_at_fmt' => $createdAt !== '' && strtotime($createdAt) ? date('d.m.Y H:i', strtotime($createdAt)) : $createdAt,
        ];
    }
}
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Top Up Order #' . $id . ' | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<style>
.seller-topup-order-view .card{background:var(--bs-card-bg)!important;border:var(--bs-card-border-color) 1px solid!important;border-radius:22px!important;box-shadow:none!important}
.seller-topup-order-view .card::before{display:none!important}
.seller-topup-order-view .order-chat-card{overflow:hidden}
.av-head{border-radius:22px;overflow:hidden;margin-bottom:20px;border:1px solid var(--bs-card-border-color);background:#25282a}
.av-head-body{padding:20px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid var(--bs-card-border-color)}
.av-title{font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2}
.av-sub{font-size:.82rem;color:rgba(255,255,255,.5);margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.av-status{display:inline-flex;align-items:center;gap:.35rem;padding:4px 11px;border-radius:99px;font-size:.75rem;font-weight:800}
.av-status--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);color:#4ade80}
.av-status--sold{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.25);color:#fb7185}
.av-status--unlisted{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.25);color:#facc15}
.av-meta-row{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:6px;padding:14px 22px 16px}
.av-meta-pill{display:inline-flex;align-items:center;gap:.3rem;padding:4px 11px;border-radius:99px;font-size:.75rem;font-weight:700;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.7)}
.av-meta-pill strong{color:rgba(255,255,255,.92)}
.av-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.av-btn-success{display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:rgba(74,222,128,.14);border:1px solid rgba(74,222,128,.25);color:#4ade80;cursor:pointer;transition:background .12s}
.av-btn-success:hover{background:rgba(74,222,128,.22)}
.av-btn-success:disabled{cursor:not-allowed;opacity:.65}
.av-btn-primary,.av-btn-ghost{display:inline-flex;align-items:center;gap:.4rem;padding:7px 14px;border-radius:11px;font-size:.83rem;font-weight:800;text-decoration:none;cursor:pointer}
.av-btn-primary{background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;color:#fff}
.av-btn-primary:hover{opacity:.88;color:#fff}
.av-btn-ghost{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.7)}
.av-btn-ghost:hover{background:rgba(255,255,255,.09);color:#fff}
.av-chat-header{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color)}
.av-chat-title{font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9);display:flex;align-items:center;gap:.5rem}
#chat_messages{min-height:300px;max-height:480px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;scroll-behavior:smooth}
#chat_messages::-webkit-scrollbar{width:5px}
#chat_messages::-webkit-scrollbar-track{background:transparent}
#chat_messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:3px}
.lb-msg{display:flex;flex-direction:column;margin-bottom:.5rem;max-width:75%}
.lb-msg--start{align-self:flex-start}
.lb-msg--end{align-self:flex-end}
.lb-msg__head{display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem}
.lb-msg__head--end{flex-direction:row-reverse}
.lb-msg__avatar{width:1.75rem;height:1.75rem;border-radius:50%;object-fit:cover;flex-shrink:0;background:rgba(109,92,255,.18);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:900;color:#c4b5fd;overflow:hidden}.lb-msg__avatar img{width:100%;height:100%;object-fit:cover;display:block}
.lb-msg__name{font-weight:700;font-size:.8rem;line-height:1.3;display:flex;align-items:center;gap:.3rem}
.lb-msg__bubble{padding:.55rem .85rem;border-radius:.75rem;font-size:.875rem;line-height:1.55;word-break:break-word;background:rgba(255,255,255,.07)}
.lb-msg--end .lb-msg__bubble{background:rgba(99,102,241,.22)}
.lb-msg__stamp{font-size:.7rem;opacity:.55;margin-top:.2rem;display:flex;align-items:center;gap:5px}
.lb-msg--end .lb-msg__stamp{justify-content:flex-end;text-align:right}
.lb-msg__ticks.is-read{color:#818cf8;opacity:1}.lb-msg__ticks.is-delivered{color:rgba(255,255,255,.48);opacity:1}
.lb-msg__bubble img{max-width:280px;max-height:220px;border-radius:.5rem;display:block;cursor:pointer}
.lb-badge{display:inline-flex;align-items:center;padding:.1rem .4rem;border-radius:999px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.lb-badge--seller{background:rgba(99,102,241,.2);color:#818cf8}
.lb-badge--client{background:rgba(16,185,129,.15);color:#10b981}
.lb-badge--admin{background:rgba(245,158,11,.15);color:#f59e0b}
.lb-chat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:240px;opacity:.4;gap:.5rem;text-align:center}
.lb-chat-preview{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.4rem .7rem}
.lb-chat-preview img{width:2.5rem;height:2.5rem;object-fit:cover;border-radius:.35rem}
.lb-chat-preview__remove{background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;padding:0 .2rem}
.av-sidebar-card{border-radius:18px;border:1px solid rgba(255,255,255,.07);background:#25282a;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.2)}
.av-sc-header{display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02)}
.av-sc-icon{width:26px;height:26px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.1);font-size:.75rem}
.av-sc-title{font-size:.8rem;font-weight:900;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;flex:1}
.av-ov-earnings{display:flex;align-items:center;justify-content:space-around;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02)}
.av-ov-earn-item{text-align:center}
.av-ov-earn-label{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.av-ov-earn-val{font-size:.9rem;font-weight:900;color:rgba(255,255,255,.88)}
.av-ov-earn-sep{font-size:.9rem;color:rgba(255,255,255,.2);font-weight:300}
.av-stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:0}
.av-stat-item{display:flex;align-items:center;gap:8px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.04);border-right:1px solid rgba(255,255,255,.04)}
.av-stat-item:nth-child(even){border-right:0}
.av-stat-ico{font-size:.65rem;color:rgba(255,255,255,.25);width:14px;flex-shrink:0}
.av-stat-lbl{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.04em;line-height:1}
.av-stat-val{font-size:.8rem;font-weight:800;color:rgba(255,255,255,.82);margin-top:2px;line-height:1.2;word-break:break-word}
.av-buyer-row{display:flex;align-items:center;gap:12px;padding:14px 16px}
.av-buyer-avi{width:38px;height:38px;border-radius:12px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.22);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#4ade80;font-weight:900}
.av-buyer-avi img{width:100%;height:100%;object-fit:cover}
.av-details-list{display:flex;flex-direction:column}
.av-detail-row{display:grid;grid-template-columns:150px minmax(0,1fr);gap:12px;padding:11px 16px;border-bottom:1px solid rgba(255,255,255,.05)}
.av-detail-row:last-child{border-bottom:0}
.av-detail-key{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.04em}
.av-detail-value{font-size:.83rem;font-weight:750;color:rgba(255,255,255,.82);word-break:break-word}
@media(max-width:767px){.av-stat-grid{grid-template-columns:1fr}.av-stat-item{border-right:0}.av-detail-row{grid-template-columns:1fr;gap:4px}.lb-msg{max-width:88%}}
</style>
<?= $this->end() ?>

<div class="seller-topup-order-view">

  <div class="av-head mb-4">
    <div class="av-head-body">
      <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
          <?php if ($icon): ?>
            <img src="<?= lb_topup_h($icon) ?>" style="width:100%;height:100%;object-fit:contain;padding:6px;" alt="<?= lb_topup_h($game) ?>">
          <?php else: ?>
            <i class="fa-duotone fa-coins" style="font-size:1.4rem;color:#a5b4fc;"></i>
          <?php endif; ?>
        </div>
        <div style="min-width:0;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <h1 class="av-title"><?= lb_topup_h($offer) ?></h1>
            <span class="av-status <?= $badgeCls ?>"><i class="fa-solid <?= $statusIcon ?>" style="font-size:.55rem;"></i> <?= lb_topup_h($status) ?></span>
          </div>
          <div class="av-sub">
            <span style="font-weight:700;"><?= lb_topup_h($game) ?></span>
            <span>·</span>
            <span>Top Up Order #<?= $id ?></span>
            <?php if ($orderCode): ?><span>·</span><span>Invoice #<?= lb_topup_h($orderCode) ?></span><?php endif; ?>
            <span>·</span>
            <span><?= $created && strtotime($created) ? date('d.m.Y', strtotime($created)) : '—' ?></span>
          </div>
        </div>
      </div>
      <div class="av-actions">
        <a href="<?= BASE_URL ?>/seller-area/top-up-orders" class="av-btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <?php if (!empty($purchase['client_id'])): ?>
        <?= sol_poke_client_button($id, 'topup') ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="av-meta-row">
      <span class="av-meta-pill"><i class="fa-solid fa-coins" style="color:rgba(255,255,255,.4);"></i> <strong><?= lb_topup_h($amount ?: 'Top Up') ?></strong></span>
      <span class="av-meta-pill"><i class="fa-solid fa-globe" style="color:rgba(255,255,255,.4);"></i> <strong><?= lb_topup_h($region ?: 'Global') ?></strong></span>
      <?php if ($platform): ?><span class="av-meta-pill"><i class="fa-solid fa-desktop" style="color:rgba(255,255,255,.4);"></i> <strong><?= lb_topup_h($platform) ?></strong></span><?php endif; ?>
      <span class="av-meta-pill"><i class="fa-solid fa-layer-group" style="color:rgba(255,255,255,.4);"></i> Qty <strong><?= $qty ?></strong></span>
      <span class="av-meta-pill"><i class="fa-solid fa-clock" style="color:rgba(255,255,255,.4);"></i> <strong><?= lb_topup_h(lb_topup_wait($p)) ?></strong></span>
      <span class="av-meta-pill"><i class="fa-solid fa-sack-dollar" style="color:#4ade80;"></i> <strong style="color:#4ade80;"><?= $sym . number_format($payout, 2) ?></strong> Payout</span>
    </div>
  </div>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-lg-8">
      <div class="card order-chat-card mb-4">
        <div class="av-chat-header">
          <div class="av-chat-title"><i class="fa-duotone fa-comments" style="color:#9f8cff;"></i> Buyer Chat</div>
          <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(74,222,128,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($clientName, 0, 1)) ?></span>
            <?= lb_topup_h($clientName) ?>
          </div>
        </div>
        <div class="card-body chat-bg" id="chat_messages"></div>
        <div class="card-footer">
          <form class="row gx-2" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="seller_topup_chat_send">
            <input type="hidden" name="purchase_id" value="<?= $id ?>">
            <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none">
            <div class="col">
              <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message to the buyer">
            </div>
            <div class="col-auto d-flex align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-secondary" id="lbChatUploadBtn" title="Attach image"><i class="fa-duotone fa-paperclip"></i></button>
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

      <div class="card mb-4">
        <div class="av-chat-header">
          <div class="av-chat-title"><i class="fa-duotone fa-list-check" style="color:#93c5fd;"></i> Delivery Details</div>
        </div>
        <div class="av-details-list">
          <?php if ($checkoutData): ?>
            <?php foreach ($checkoutData as $k => $v): ?>
              <?php if (is_array($v) || is_object($v)) $v = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
              <div class="av-detail-row">
                <div class="av-detail-key"><?= lb_topup_h(ucwords(str_replace(['_', '-'], ' ', (string)$k))) ?></div>
                <div class="av-detail-value"><?= lb_topup_h($v) ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="padding:24px;text-align:center;color:rgba(255,255,255,.38);">No extra delivery details saved.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
          <span class="av-sc-title">Order Overview</span>
        </div>
        <div class="av-ov-earnings">
          <div class="av-ov-earn-item"><div class="av-ov-earn-label">Total</div><div class="av-ov-earn-val"><?= $sym . number_format($total, 2) ?></div></div>
          <div class="av-ov-earn-sep">→</div>
          <div class="av-ov-earn-item"><div class="av-ov-earn-label">Fee</div><div class="av-ov-earn-val" style="color:#fb7185;">−<?= $effectiveFee ?>%</div></div>
          <div class="av-ov-earn-sep">=</div>
          <div class="av-ov-earn-item"><div class="av-ov-earn-label">You Earn</div><div class="av-ov-earn-val" style="color:#4ade80;font-size:1rem;"><?= $sym . number_format($payout, 2) ?></div></div>
        </div>
        <div class="av-stat-grid">
          <?php
          $stats = [
              ['fa-solid fa-hashtag', 'Order ID', '#' . $id],
              ['fa-solid fa-gamepad', 'Game', $game],
              ['fa-solid fa-coins', 'Offer', $amount ?: $offer],
              ['fa-solid fa-layer-group', 'Quantity', $qty],
              ['fa-solid fa-tag', 'Unit Price', $sym . number_format($unit, 2)],
              ['fa-solid fa-globe', 'Region', $region ?: 'Global'],
              ['fa-solid fa-desktop', 'Platform', $platform ?: '—'],
              ['fa-solid fa-clock', 'Waiting Time', lb_topup_wait($p)],
              ['fa-solid fa-calendar', 'Created', $created && strtotime($created) ? date('d.m.Y H:i', strtotime($created)) : '—'],
          ];
          foreach ($stats as [$ico, $lbl, $val]): ?>
            <div class="av-stat-item">
              <i class="<?= $ico ?> av-stat-ico"></i>
              <div><div class="av-stat-lbl"><?= lb_topup_h($lbl) ?></div><div class="av-stat-val"><?= lb_topup_h((string)$val) ?></div></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-user-check" style="color:#4ade80;font-size:.72rem;"></i></span>
          <span class="av-sc-title">Buyer</span>
        </div>
        <div class="av-buyer-row">
          <div class="av-buyer-avi">
            <?php if ($clientAvatar): ?><img src="<?= lb_topup_h($clientAvatar) ?>" alt=""><?php else: ?><?= strtoupper(substr($clientName, 0, 1)) ?><?php endif; ?>
          </div>
          <div style="min-width:0;">
            <div style="font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= lb_topup_h($clientName) ?></div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.35);">Top Up customer</div>
          </div>
          <a href="#chat_messages" onclick="document.getElementById('chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'});return false;" class="av-btn-primary" style="font-size:.75rem;padding:5px 12px;margin-left:auto;"><i class="fa-solid fa-message"></i> Chat</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const box = document.getElementById('chat_messages');
  const form = document.getElementById('lbChatForm');
  const uploadBtn = document.getElementById('lbChatUploadBtn');
  const fileInput = document.getElementById('lbChatImageInput');
  const previewWrap = document.getElementById('lbChatImagePreviewWrap');
  const previewImg = document.getElementById('lbChatImagePreview');
  const previewRemove = document.getElementById('lbChatImageRemove');
  const pid = '<?= $id ?>';
  const ajaxUrl = window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>';
  if (!window.AJAX_URL) window.AJAX_URL = ajaxUrl;

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
      $.post(ajaxUrl, { action:'seller_poke_client', ref_type:'topup', id:btn.dataset.id || <?= $id ?> }, function(resp){
        let data = resp; try { if (typeof resp === 'string') data = JSON.parse(resp); } catch(e) {}
        if (data && data.sendToast && typeof create_toast === 'function') create_toast(data.sendToast.type || 'primary', data.sendToast.title || 'Notice', data.sendToast.message || 'Done');
        if (data && data.cooldown_seconds) startCooldown(data.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });

  // Register sending before websocket and read-receipt code.
  if (form && !form.dataset.lbSubmitBound) {
    form.dataset.lbSubmitBound = '1';
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      e.stopPropagation();

      const btn = document.getElementById('lbChatSendBtn');
      const label = btn ? btn.querySelector('.indicator-label') : null;
      const progress = btn ? btn.querySelector('.indicator-progress') : null;
      const messageField = document.getElementById('lbChatMessageInput');
      const textValue = messageField ? String(messageField.value || '').trim() : '';
      const hasFile = !!(fileInput && fileInput.files && fileInput.files.length);

      if (!textValue && !hasFile) return;

      if (btn) btn.disabled = true;
      if (label) label.classList.add('d-none');
      if (progress) progress.classList.remove('d-none');

      try {
        const fd = new FormData(form);
        fd.set('action', 'seller_topup_chat_send');
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

        form.reset();
        clearPreview();

        if (Array.isArray(data.messages)) {
          render(data.messages);
        } else if (data.chat_message && typeof data.chat_message === 'object') {
          render([data.chat_message]);
        } else if (data.message && typeof data.message === 'object') {
          render([data.message]);
        }

        const activeSocket = window.lbSocket || window.socket || null;
        if (activeSocket) {
          try {
            activeSocket.emit('topup_chat_init', {purchase_id: pid, area: 'seller'});
          } catch (socketError) {}
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
        if (label) label.classList.remove('d-none');
        if (progress) progress.classList.add('d-none');
        if (messageField) messageField.focus();
      }
    });
  }

  function esc(s){ return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function normaliseMessages(payload){
    if (!payload) return [];
    if (Array.isArray(payload)) return payload;
    const data = (payload.data && typeof payload.data === 'object') ? payload.data : payload;
    if (Array.isArray(data.messages)) return data.messages;
    if (data.chat_message && typeof data.chat_message === 'object') return [data.chat_message];
    if (data.message && typeof data.message === 'object') return [data.message];
    return [];
  }
  function roleBadge(sender){
    const key = String(sender || 'client').toLowerCase();
    const label = key === 'seller' ? 'Seller' : (key === 'admin' ? 'Admin' : 'Client');
    return '<span class="lb-badge lb-badge--' + (key === 'admin' ? 'admin' : (key === 'seller' ? 'seller' : 'client')) + '">' + label + '</span>';
  }
  const CLIENT_AVATAR = <?= json_encode($clientAvatar, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  const SELLER_AVATAR = <?= json_encode($sellerAvatar, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  function render(payload){
    if (!box) return;
    const messages = normaliseMessages(payload);
    if (!messages.length) {
      box.innerHTML = '<div class="lb-chat-empty"><i class="fa-duotone fa-comments fa-2x"></i><div>No messages yet</div><small>Send the buyer a message to get started.</small></div>';
      return;
    }
    box.innerHTML = messages.map(m => {
      const me = String(m.sender || '').toLowerCase() === 'seller';
      const bodyValue = m.body || m.content || m.message || '';
      const body = String(m.message_type || '').toLowerCase() === 'image'
        ? '<img src="' + esc(bodyValue) + '" alt="chat image">'
        : esc(bodyValue).replace(/\n/g, '<br>');
      const senderName = m.sender_name || (me ? 'Seller' : 'Client');
      const initial = esc(String(senderName).charAt(0).toUpperCase());
      const avatarUrl = String(m.sender_avatar || m.avatar || (me ? SELLER_AVATAR : CLIENT_AVATAR) || '');
      const avatarHtml = avatarUrl
        ? '<span class="lb-msg__avatar"><img src="' + esc(avatarUrl) + '" alt="' + esc(senderName) + '"></span>'
        : '<span class="lb-msg__avatar">' + initial + '</span>';
      return '<div class="lb-msg ' + (me ? 'lb-msg--end' : 'lb-msg--start') + '">' +
        '<div class="lb-msg__head ' + (me ? 'lb-msg__head--end' : '') + '">' +
          avatarHtml +
          '<div class="lb-msg__name">' + esc(senderName) + roleBadge(m.sender) + '</div>' +
        '</div>' +
        '<div class="lb-msg__bubble">' + body + '</div>' +
        '<div class="lb-msg__stamp">' + esc(m.created_at_fmt || m.time_fmt || m.created_at || '') + (me ? ((parseInt(m.seen_by_client ?? m.seen ?? 0,10)===1) ? '<span class="lb-msg__ticks is-read" title="Seen' + (m.read_at_fmt ? ' · ' + esc(m.read_at_fmt) : '') + '"><i class="fa-solid fa-check-double"></i></span>' : '<span class="lb-msg__ticks is-delivered" title="Delivered"><i class="fa-solid fa-check-double"></i></span>') : '') + '</div>' +
      '</div>';
    }).join('');
    box.scrollTop = box.scrollHeight;
  }

  const initialMessages = <?= json_encode($initialTopupMessages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]' ?>;
  render(initialMessages);
  function chatIsVisible(){
    if (document.visibilityState !== 'visible' || !box) return false;
    const rect = box.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  async function loadTopupMessages(markSeen = false){
    try {
      const body = new URLSearchParams();
      body.set('action', 'topup_chat_load');
      body.set('purchase_id', pid);
      if (markSeen) body.set('mark_seen', '1');
      const res = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: body.toString()
      });
      const data = await res.json();
      if (data && Array.isArray(data.messages)) render(data.messages);
    } catch (e) {
      // Loading or receipt updates must never break the chat.
    }
  }

  let topupSeenRequestRunning = false;
  let topupSeenRequested = false;

  async function markTopupSeen(force = false){
    if (topupSeenRequestRunning) return;
    if (!force && topupSeenRequested) return;
    if (!chatIsVisible()) return;
    if (!document.hasFocus()) return;

    topupSeenRequestRunning = true;
    try {
      await loadTopupMessages(true);
      topupSeenRequested = true;
    } finally {
      topupSeenRequestRunning = false;
    }
  }

  function registerSellerChatActivity(){
    if (!box) return;

    const triggerSeen = function(){
      markTopupSeen(false);
    };

    box.addEventListener('click', triggerSeen, {passive:true});
    box.addEventListener('scroll', triggerSeen, {passive:true});
    box.addEventListener('wheel', triggerSeen, {passive:true});
    box.addEventListener('touchstart', triggerSeen, {passive:true});

    const messageInput = document.getElementById('lbChatMessageInput');
    if (messageInput) {
      messageInput.addEventListener('focus', triggerSeen);
      messageInput.addEventListener('click', triggerSeen);
      messageInput.addEventListener('keydown', triggerSeen);
    }

    if (form) {
      form.addEventListener('click', triggerSeen, {passive:true});
    }
  }

  function payloadMatches(payload){
    const data = (payload && payload.data && typeof payload.data === 'object') ? payload.data : (payload || {});
    const purchaseId = String(data.purchase_id || data.id || '');
    const orderId = String(data.order_id || '');
    return purchaseId === String(pid) || orderId === ('topuppurch_' + String(pid));
  }

  function handleRealtime(payload){
    if (!payloadMatches(payload)) return;
    const data = (payload.data && typeof payload.data === 'object') ? payload.data : payload;
    const messages = normaliseMessages(data);
    if (messages.length) render(messages);
    else loadTopupMessages(false);
    if (document.visibilityState === 'visible' && !data.read_receipt_update) {
      setTimeout(markTopupSeen, 80);
    }
  }

  window.lbTopupSellerChatUpdate = handleRealtime;
  window.lbOrderViewChatUpdate = handleRealtime;

  let boundSocket = null;
  function bindTopupSocket(){
    const s = window.lbSocket || window.socket || null;
    if (!s || s === boundSocket) return;
    boundSocket = s;
    try { s.emit('join', 'sellers'); } catch(e) {}
    try { s.emit('join', 'seller_topuppurch_' + String(pid)); } catch(e) {}
    try { s.emit('topup_chat_init', {purchase_id: pid, area: 'seller'}); } catch(e) {}
    try { s.on('topup_chat_history', handleRealtime); } catch(e) {}
    try { s.on('chat_update', handleRealtime); } catch(e) {}
  }

  bindTopupSocket();
  registerSellerChatActivity();

  // The seller is actively viewing the order and the chat is visible.
  // Mark client messages as read after the page has settled.
  setTimeout(function(){ markTopupSeen(false); }, 350);
  setTimeout(function(){ markTopupSeen(false); }, 1200);

  let socketBindAttempts = 0;
  const socketBindTimer = setInterval(function(){
    socketBindAttempts++;
    bindTopupSocket();
    if (boundSocket || socketBindAttempts >= 20) clearInterval(socketBindTimer);
  }, 250);
  window.addEventListener('lb-socket-ready', bindTopupSocket);
  document.addEventListener('visibilitychange', function(){
    if (document.visibilityState === 'visible') {
      bindTopupSocket();
      topupSeenRequested = false;
      setTimeout(function(){ markTopupSeen(false); }, 250);
    }
  });
  window.addEventListener('focus', function(){
    topupSeenRequested = false;
    setTimeout(function(){ markTopupSeen(false); }, 120);
  });

  function clearPreview(){
    if (fileInput) fileInput.value = '';
    if (previewImg) previewImg.src = '';
    if (previewWrap) previewWrap.classList.add('d-none');
  }
  if (uploadBtn && fileInput) uploadBtn.addEventListener('click', () => fileInput.click());
  if (fileInput) fileInput.addEventListener('change', function(){
    if (!this.files || !this.files[0]) return clearPreview();
    if (previewImg) previewImg.src = URL.createObjectURL(this.files[0]);
    if (previewWrap) previewWrap.classList.remove('d-none');
  });
  if (previewRemove) previewRemove.addEventListener('click', clearPreview);
  document.addEventListener('paste', function(e){
    if (!fileInput || !e.clipboardData) return;
    const file = Array.from(e.clipboardData.files || []).find(f => /^image\//i.test(f.type));
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event('change'));
  });


})();
</script>
