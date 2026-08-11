<?php
$meta = $meta ?? ['title' => 'My Chats | LoLBoost.gg'];
$conversations = is_array($conversations ?? null) ? $conversations : [];
$h = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$fmtTime = function($ts){
    $ts = (int)$ts;
    if ($ts <= 0) return '';
    if (date('Y-m-d', $ts) === date('Y-m-d')) return date('H:i', $ts);
    if ($ts > strtotime('-7 days')) return date('D', $ts);
    return date('M j', $ts);
};
$first = $conversations[0] ?? null;
$accountCount = 0;
$itemCount = 0;
$topupCount = 0;
$boostingCount = 0;
foreach ($conversations as $c) {
    $refType = (string)($c['ref_type'] ?? '');
    $chatType = (string)($c['chat_type'] ?? '');
    if ($chatType === 'booster' || $refType === 'booster_order') $boostingCount++;
    elseif ($chatType === 'topup' || $refType === 'topup_purchase') $topupCount++;
    elseif ($refType === 'item_purchase') $itemCount++;
    else $accountCount++;
}
$conversationCount = count($conversations);
?>
<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>
<style>
html,body{overflow:hidden!important}.client-chat-page{--sc-bg:#1f2223;--sc-panel:#242728;--sc-panel-2:#202324;--sc-soft:#2b2f31;--sc-line:rgba(255,255,255,.075);--sc-text:#f5f7fb;--sc-muted:#9aa3ad;--sc-purple:#7367f0;--sc-green:#36d98a;--sc-blue:#5c7cfa;color:var(--sc-text);padding:8px 24px 60px 24px;height:calc(100vh - 70px);overflow:hidden}.client-chat-page *{box-sizing:border-box}.client-chat-page .client-chat-page-title{display:none}.client-chat-page .client-chat-shell{background:var(--sc-panel);border:1px solid var(--sc-line);border-radius:18px;overflow:hidden;height:100%;box-shadow:none;display:flex;flex-direction:column;max-width:1460px;margin:0 auto}.client-chat-page .client-chat-head{height:62px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid var(--sc-line);background:#222526;flex:0 0 auto}.client-chat-page .client-chat-title{display:flex;align-items:center;gap:12px;font-size:1.05rem;font-weight:800;color:#fff}.client-chat-page .client-chat-title i{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(115,103,240,.12);border:1px solid rgba(115,103,240,.24);color:#9d94ff;box-shadow:none}.client-chat-page .client-chat-actions{display:flex;align-items:center;gap:14px;font-size:.8rem;color:#aab2c0;font-weight:500}.client-chat-page .client-chat-count b{color:#c6c2ff}.client-chat-page .client-chat-status{display:inline-flex;align-items:center;gap:6px;color:var(--sc-green);font-weight:700}.client-chat-page .client-chat-status:before{content:"";width:8px;height:8px;border-radius:50%;background:var(--sc-green);box-shadow:0 0 0 3px rgba(54,217,138,.12)}.client-chat-page .client-chat-tabs{height:58px;display:flex;align-items:center;gap:22px;padding:0 18px;border-bottom:1px solid var(--sc-line);background:#242728;flex:0 0 auto}.client-chat-page .client-chat-tab{border:0;background:transparent;color:#b1b7c2;font-weight:800;font-size:.86rem;display:flex;align-items:center;gap:8px;padding:.55rem .8rem;border-radius:12px;cursor:pointer}.client-chat-page .client-chat-tab:hover{background:#2f3337;color:#fff}.client-chat-page .client-chat-tab.active{background:#34383d;color:#fff;box-shadow:none}.client-chat-page .client-chat-tab span{background:#34383d;color:#cdd3dc;border-radius:999px;font-size:.68rem;padding:.15rem .42rem;min-width:auto;height:auto;display:inline-flex;align-items:center;justify-content:center}.client-chat-page .client-chat-tab.active span{background:rgba(255,255,255,.13);color:#fff}.client-chat-page .client-chat-body{display:grid;grid-template-columns:330px 1fr;min-height:0;flex:1}.client-chat-page .client-chat-left{background:#202426;border-right:1px solid var(--sc-line);min-width:0;display:flex;flex-direction:column;overflow:hidden;min-height:0}.client-chat-page .client-notify-row{height:52px;display:flex;align-items:center;gap:10px;padding:0 16px;border-bottom:1px solid var(--sc-line);font-size:.82rem;color:#d3d7df;flex:0 0 auto}.client-chat-page .client-toggle{width:42px;height:22px;border:1px solid rgba(255,255,255,.08);border-radius:999px;background:#34393d;position:relative;display:inline-flex;align-items:center;padding:0;cursor:pointer;outline:0;transition:background .18s ease,border-color .18s ease;box-shadow:inset 0 0 0 1px rgba(0,0,0,.12);flex-shrink:0}.client-chat-page .client-toggle:before{content:"";position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#8f98a5;transition:left .18s ease,background .18s ease;box-shadow:0 2px 5px rgba(0,0,0,.35)}.client-chat-page .client-toggle:hover{border-color:rgba(255,255,255,.16)}.client-chat-page .client-toggle:focus-visible{box-shadow:0 0 0 3px rgba(115,103,240,.28)}.client-chat-page .client-toggle.on{background:rgba(54,217,138,.22);border-color:rgba(54,217,138,.35)}.client-chat-page .client-toggle.on:before{left:21px;background:var(--sc-green);box-shadow:0 0 0 3px rgba(54,217,138,.12),0 2px 6px rgba(0,0,0,.35)}.client-chat-page .client-chat-search{padding:14px 14px;border-bottom:1px solid var(--sc-line);position:relative;flex:0 0 auto}.client-chat-page .client-chat-search input{width:100%;height:44px;border-radius:12px;border:1px solid rgba(255,255,255,.09);background:#1e2123;color:#fff;padding:0 42px 0 14px;outline:0}.client-chat-page .client-chat-search input:focus{border-color:rgba(115,103,240,.38);box-shadow:0 0 0 3px rgba(115,103,240,.13)}.client-chat-page .client-chat-search i{position:absolute;right:28px;top:28px;color:#8f98a5}.client-chat-page .client-conv-list{overflow-y:auto;overflow-x:hidden;min-height:0;flex:1}.client-chat-page .client-conv-list::-webkit-scrollbar,.client-chat-page .client-messages::-webkit-scrollbar{width:5px}.client-chat-page .client-conv-list::-webkit-scrollbar-thumb,.client-chat-page .client-messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:10px}.client-chat-page .client-conv{display:flex;gap:12px;align-items:center;padding:11px 14px;border-bottom:1px solid var(--sc-line);color:#e8ebf1;text-decoration:none;cursor:pointer;position:relative;transition:background .12s}.client-chat-page .client-conv:hover{background:#272c2f}.client-chat-page .client-conv.active{background:#2c3035;box-shadow:inset 3px 0 0 var(--sc-purple)}.client-chat-page .client-conv-body{flex:1;min-width:0}.client-chat-page .client-conv-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px}.client-chat-page .client-conv-name{font-weight:800;font-size:.84rem;color:#edf0f7;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}.client-chat-page .client-conv-meta{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;gap:5px;flex-shrink:0}.client-chat-page .client-time{font-size:.67rem;color:#5e6a78;white-space:nowrap}.client-chat-page .client-conv-sub{display:flex;align-items:center;gap:5px;margin-bottom:4px}.client-chat-page .client-kind{font-size:.62rem;font-weight:900;text-transform:uppercase;border-radius:5px;padding:.12rem .4rem;letter-spacing:.04em}.client-chat-page .client-kind.request{background:rgba(255,171,0,.13);color:#f5c842;border:1px solid rgba(255,171,0,.25)}.client-chat-page .client-kind.order{background:rgba(54,217,138,.11);color:#45d48a;border:1px solid rgba(54,217,138,.22)}.client-chat-page .client-conv-ref{color:#4a5568;font-size:.7rem;font-weight:600}.client-chat-page .client-conv-title{color:#6b7a8d;font-size:.7rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px}.client-chat-page .client-last{font-size:.75rem;color:#8492a6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}.client-chat-page .client-avatar{width:42px;height:42px;border-radius:50%;overflow:hidden;background:#3b4046;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;box-shadow:none;min-width:42px}.client-chat-page .client-avatar img{width:100%;height:100%;object-fit:cover}.client-chat-page .client-unread{display:inline-flex;min-width:18px;height:18px;align-items:center;justify-content:center;border-radius:999px;background:#446dff;color:#fff;font-weight:900;font-size:.65rem}.client-chat-page .client-chat-help{display:none}.client-chat-page .client-chat-main{background:#202426;display:flex;flex-direction:column;min-width:0;min-height:0}.client-chat-page .client-chat-top{height:68px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid var(--sc-line);background:#202426;flex:0 0 auto}.client-chat-page .client-chat-peer{display:flex;gap:12px;align-items:center;min-width:0}.client-chat-page .client-chat-peer h3{margin:0;font-size:1rem;font-weight:900;color:#fff;line-height:1.1}.client-chat-page .client-chat-peer p{margin:3px 0 0;color:#9fb0d5;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.client-chat-page .client-open-source{border:1px solid rgba(255,255,255,.1);background:#303437;color:#fff!important;border-radius:10px;padding:.54rem .85rem;text-decoration:none;font-weight:800;font-size:.82rem;white-space:nowrap}.client-chat-page .client-open-source:hover{background:#393e42}.client-chat-page .client-messages{flex:1;min-height:0;overflow-y:auto;overflow-x:hidden;padding:18px;display:flex;flex-direction:column;gap:10px;background:#202426}.client-chat-page .client-empty{height:100%;display:flex;align-items:center;justify-content:center;text-align:center;color:#8d96a5;flex-direction:column;gap:8px;padding:30px}.client-chat-page .client-empty b{color:#fff}.client-chat-page .client-msg{display:flex;gap:10px;align-items:flex-end;max-width:76%}.client-chat-page .client-msg.me{margin-left:auto;flex-direction:row-reverse}.client-chat-page .client-msg-a{width:32px;height:32px;min-width:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#3b4046;color:#fff;font-size:.7rem;font-weight:900;overflow:hidden;flex-shrink:0}.client-chat-page .client-msg-a img{width:100%;height:100%;object-fit:cover}.client-chat-page .client-msg-inner{display:flex;flex-direction:column}.client-chat-page .client-msg.me .client-msg-inner{align-items:flex-end}.client-chat-page .client-msg-b{background:#2e3338;color:#e8ebf1;border-radius:16px 16px 16px 4px;padding:10px 14px;line-height:1.45;word-break:break-word}.client-chat-page .client-msg.me .client-msg-b{background:#7367f0;color:#fff;border-radius:16px 16px 4px 16px}.client-chat-page .client-msg-b img{max-width:310px;border-radius:10px;display:block}.client-chat-page .client-msg-t{font-size:.67rem;color:#6e7a8a;margin-top:4px;display:flex;align-items:center;gap:4px}.client-chat-page .client-msg.me .client-msg-t{flex-direction:row-reverse}.client-chat-page .client-msg-system{align-self:stretch;background:rgba(115,103,240,.07);border:1px solid rgba(115,103,240,.15);border-radius:10px;padding:10px 14px;font-size:.78rem;color:#9aa3b8;line-height:1.5}.client-chat-page .client-msg-system i{color:#7367f0;margin-right:6px}.client-chat-page .client-msg-system a{color:#7fb3ff;text-decoration:underline;word-break:break-all}.client-chat-page .client-msg-system strong{color:#d0d8f0;font-weight:700}.client-chat-page .client-compose{height:94px;border-top:1px solid var(--sc-line);padding:14px 16px 24px;display:flex;gap:10px;align-items:center;background:#202426;flex:0 0 auto}.client-chat-page .client-compose input[type=text]{flex:1;height:52px;border-radius:999px;border:1px solid rgba(255,255,255,.09);background:#1e2123;color:#fff;padding:0 18px;outline:0}.client-chat-page .client-compose input[type=text]:focus{border-color:rgba(115,103,240,.38);box-shadow:0 0 0 3px rgba(115,103,240,.13)}.client-chat-page .client-compose button{border:0;border-radius:14px;background:var(--sc-purple);color:#fff;width:52px;height:52px;font-size:17px;box-shadow:none;display:flex;align-items:center;justify-content:center;flex-shrink:0}.client-chat-page .client-compose button:hover{filter:brightness(1.08)}.client-chat-page .client-compose .img-btn{background:#303437;color:#cfd5df}.client-chat-page .client-preview{display:none;align-items:center;gap:10px;padding:9px 14px;background:#202426;border-top:1px solid var(--sc-line);flex:0 0 auto}.client-chat-page .client-preview.is-open{display:flex}.client-chat-page .client-preview img{width:46px;height:46px;object-fit:cover;border-radius:9px}.client-chat-page .client-preview button{margin-left:auto;border:0;background:#303437;color:#fff;border-radius:9px;width:30px;height:30px}.client-chat-page .client-msg-check{font-size:.62rem;transition:color .3s}.client-chat-page .client-msg-check.sent{color:rgba(255,255,255,.4)}.client-chat-page .client-msg-check.read{color:#36d98a}@media(max-width:991px){html,body{overflow:auto!important}.client-chat-page{height:auto;min-height:calc(100vh - 70px);padding:10px;overflow:visible}.client-chat-page .client-chat-shell{height:auto;min-height:calc(100vh - 90px)}.client-chat-page .client-chat-body{grid-template-columns:1fr}.client-chat-page .client-chat-head{height:auto;gap:10px;align-items:flex-start;padding:14px;flex-direction:column}.client-chat-page .client-chat-tabs{overflow-x:auto}.client-chat-page .client-chat-main{min-height:520px}.client-chat-page .client-conv-list{max-height:320px}.client-chat-page .client-chat-actions{display:none}.client-chat-page .mobile-list-hint{display:block;color:#94a3b8;font-size:12px}.client-chat-page .client-msg{max-width:88%}.client-chat-page .client-open-source{display:none!important}}
.client-chat-page .client-mark-all-read{border:1px solid rgba(255,255,255,.1);background:#303437;color:#fff;border-radius:10px;padding:.42rem .7rem;font-weight:800;font-size:.76rem;display:inline-flex;align-items:center;gap:6px;cursor:pointer}.client-chat-page .client-mark-all-read:hover{background:#393e42}.client-chat-page .client-mark-all-read:disabled{opacity:.55;cursor:not-allowed}
</style>

<div class="client-chat-page">
  <h1 class="client-chat-page-title">My Chats</h1>
  <div class="client-chat-shell">
    <div class="client-chat-head">
      <div class="client-chat-title"><i class="fa-duotone fa-messages"></i> Chat Inbox</div>
      <div class="client-chat-actions">
        <button type="button" class="client-mark-all-read" id="clientMarkAllRead"><i class="fa-duotone fa-check-double"></i> Mark all as read</button>
        <span class="client-chat-count"><b><?= (int)$conversationCount ?></b> conversation<?= $conversationCount === 1 ? '' : 's' ?></span>
        <span class="client-chat-status">Messages protected</span>
      </div>
    </div>

    <div class="client-chat-tabs">
      <button type="button" class="client-chat-tab active" data-filter="all"><i class="fa-solid fa-inbox"></i> All <span><?= (int)$conversationCount ?></span></button>
      <button type="button" class="client-chat-tab" data-filter="accounts"><i class="fa-solid fa-database"></i> Accounts <span><?= (int)$accountCount ?></span></button>
      <button type="button" class="client-chat-tab" data-filter="items"><i class="fa-solid fa-gift"></i> Items <span><?= (int)$itemCount ?></span></button>
      <button type="button" class="client-chat-tab" data-filter="topups"><i class="fa-solid fa-coins"></i> Top Ups <span><?= (int)$topupCount ?></span></button>
      <button type="button" class="client-chat-tab" data-filter="boosting"><i class="fa-solid fa-rocket"></i> Boosting <span><?= (int)$boostingCount ?></span></button>
    </div>

    <div class="client-chat-body">
      <aside class="client-chat-left">
        <div class="client-notify-row"><button type="button" id="clientNotifyToggle" class="client-toggle" aria-label="Toggle chat sound notifications"></button> <span id="clientNotifyLabel">Sound notifications</span></div>
        <div class="client-notify-row"><button type="button" id="clientUnreadOnlyToggle" class="client-toggle" aria-label="Toggle unread only filter"></button> <span>Unread only</span></div>
        <div class="client-chat-search">
          <input id="client-chat-search" placeholder="Search...">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="client-conv-list" id="client-conv-list">
          <?php if (empty($conversations)): ?>
            <div class="client-empty" style="min-height:260px">
              <i class="fa-duotone fa-comments" style="font-size:42px"></i>
              <b>No conversations yet</b>
              <small>Your account requests and orders will appear here.</small>
            </div>
          <?php else: foreach ($conversations as $i => $conv): ?>
            <?php
              $seller = $conv['seller_username'] ?? 'Seller';
              $initial = strtoupper(substr($seller, 0, 1));
              $last = trim((string)($conv['last_body'] ?? '')) !== '' ? mb_strimwidth((string)$conv['last_body'], 0, 62, '…') : 'No message yet';
              $status = $conv['request_status'] ?? 'paid';
              $chatType = $conv['chat_type'] ?? (($conv['ref_type'] ?? '') === 'booster_order' ? 'booster' : 'seller');
              $isRequest = $status === 'request';
              $isBooster = $chatType === 'booster';
              $labelDisplay = $isRequest ? 'ACCOUNT REQUEST' : ($isBooster ? 'BOOSTER CHAT' : 'ORDER');
              $kindClass = $isRequest ? 'request' : 'order';
              $active = $i === 0 ? ' active' : '';
            ?>
            <a href="#" class="client-conv<?= $active ?>"
               data-status="<?= $h($status) ?>"
               data-category="<?= $h($isBooster ? 'boosting' : (($conv['ref_type'] ?? '') === 'item_purchase' ? 'items' : 'accounts')) ?>"
               data-search="<?= $h(strtolower($seller . ' ' . ($conv['title'] ?? '') . ' #' . ($conv['ref_id'] ?? ''))) ?>"
               data-seller-id="<?= (int)($conv['seller_id'] ?? 0) ?>"
               data-ref-type="<?= $h($conv['ref_type'] ?? 'account') ?>"
               data-ref-id="<?= (int)($conv['ref_id'] ?? 0) ?>"
               data-chat-key="<?= $h($conv['chat_key'] ?? '') ?>"
               data-seller-name="<?= $h($seller) ?>"
               data-seller-icon="<?= $h($conv['seller_icon'] ?? '') ?>"
               data-chat-type="<?= $h($conv['chat_type'] ?? (($conv['ref_type'] ?? '') === 'booster_order' ? 'booster' : 'seller')) ?>"
               data-title="<?= $h($conv['title'] ?? '') ?>"
               data-label="<?= $h($labelDisplay) ?>"
               data-source-url="<?= $h($conv['source_url'] ?? '') ?>"
               data-source-label="<?= $isRequest ? 'View Shop Listing' : 'View Order' ?>"
               data-unread="<?= (int)($conv['unread_client'] ?? 0) ?>">
              <div class="client-avatar"><?php if(!empty($conv['seller_icon'])):?><img src="<?= $h($conv['seller_icon']) ?>" alt=""><?php else: ?><?= $h($initial) ?><?php endif; ?></div>
              <div class="client-conv-body">
                <div class="client-conv-top">
                  <span class="client-conv-name"><?= $h($seller) ?></span>
                  <span class="client-time"><?= $h($fmtTime($conv['last_message_at'] ?? 0)) ?></span>
                </div>
                <div class="client-conv-sub">
                  <span class="client-kind <?= $h($kindClass) ?>"><?= $isRequest ? 'Request' : ($isBooster ? 'Booster' : 'Order') ?></span>
                  <span class="client-conv-ref">#<?= (int)($conv['ref_id'] ?? 0) ?></span>
                  <?php if (!empty($conv['title'])): ?><span class="client-conv-title"><?= $h(mb_strimwidth($conv['title'], 0, 28, '…')) ?></span><?php endif; ?>
                </div>
                <div class="client-last"><?= $h($last) ?></div>
              </div>
              <div class="client-conv-meta"><?php if((int)($conv['unread_client'] ?? 0) > 0): ?><span class="client-unread"><?= (int)$conv['unread_client'] ?></span><?php endif; ?></div>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </aside>
      <section class="client-chat-main">
        <div class="client-chat-top" id="client-chat-top" style="display:<?= $first ? 'flex' : 'none' ?>">
          <div class="client-chat-peer"><div class="client-avatar" id="client-peer-avatar"></div><div style="min-width:0"><h3 id="client-peer-name">Seller</h3><p><span id="client-peer-label"></span> · <span id="client-peer-title"></span></p><span class="mobile-list-hint">You can select another chat above.</span></div></div>
          <a href="#" target="_blank" rel="noopener" class="client-open-source" id="client-source-link" style="display:none"><i class="fa-solid fa-up-right-from-square me-1"></i> <span id="client-source-label-text">View Listing</span></a>
        </div>
        <div class="client-messages" id="client-chat-messages"><div class="client-empty"><i class="fa-duotone fa-message-dots" style="font-size:54px"></i><b>Select a conversation</b><small>Pick an account request or order chat from the left.</small></div></div>
        <form class="client-compose" id="clientChatForm" autocomplete="off" style="display:<?= $first ? 'flex' : 'none' ?>">
          <input type="hidden" name="action" value="client_seller_chat_send"><input type="hidden" name="seller_id" id="clientSellerId"><input type="hidden" name="ref_type" id="clientRefType"><input type="hidden" name="ref_id" id="clientRefId"><input type="file" name="chat_image" id="clientChatImage" accept="image/*" hidden><button type="button" class="img-btn" id="clientImageBtn"><i class="fa-solid fa-paperclip"></i></button><input type="text" name="message" id="clientChatInput" placeholder="Type your message..."><button type="submit" id="clientChatSend"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="client-preview" id="clientPreview"><img src="" id="clientPreviewImg"><small id="clientPreviewName"></small><button type="button" id="clientPreviewRemove">×</button></div>
      </section>
    </div>
  </div>
</div>
<?= $this->start('scripts') ?>
<script>
window._clientIcon = <?= json_encode($client_icon ?? (CLIENT_DATA['icon'] ?? '')) ?>;
window._clientName = <?= json_encode($client_name ?? $client_username ?? (CLIENT_DATA['username'] ?? 'Me')) ?>;
</script>
<script>
(function(){
const AJAX_URL=<?= json_encode((defined('BASE_URL') ? BASE_URL : '') . '/ajax') ?>;
const list=document.getElementById('client-conv-list');let links=Array.from(document.querySelectorAll('.client-conv'));const box=document.getElementById('client-chat-messages'),form=document.getElementById('clientChatForm'),input=document.getElementById('clientChatInput'),send=document.getElementById('clientChatSend'),image=document.getElementById('clientChatImage'),imageBtn=document.getElementById('clientImageBtn'),preview=document.getElementById('clientPreview'),previewImg=document.getElementById('clientPreviewImg'),previewName=document.getElementById('clientPreviewName'),previewRemove=document.getElementById('clientPreviewRemove'),topbar=document.getElementById('client-chat-top'),peerAvatar=document.getElementById('client-peer-avatar'),peerName=document.getElementById('client-peer-name'),peerLabel=document.getElementById('client-peer-label'),peerTitle=document.getElementById('client-peer-title'),sourceLink=document.getElementById('client-source-link'),search=document.getElementById('client-chat-search'),markAllBtn=document.getElementById('clientMarkAllRead');
const notifyToggle=document.getElementById('clientNotifyToggle');
const notifyLabel=document.getElementById('clientNotifyLabel');
const notifyKey='lolboost_client_chat_sound_notifications';
const audioBase=(typeof asset_url!=='undefined'&&asset_url)?asset_url:'';
const chatNotif=new Audio(audioBase+'/core/dash/audio/new-message.mp3');
function notificationsEnabled(){return localStorage.getItem(notifyKey)!=='0';}
function setNotificationsEnabled(enabled){localStorage.setItem(notifyKey,enabled?'1':'0');notifyToggle?.classList.toggle('on',enabled);if(notifyLabel)notifyLabel.textContent=enabled?'Sound notifications':'Sound muted';if(enabled&&'Notification'in window&&Notification.permission==='default'){try{Notification.requestPermission();}catch(e){}}}
function playMessageSound(){if(!notificationsEnabled())return;try{chatNotif.currentTime=0;chatNotif.volume=0.6;chatNotif.play();}catch(e){}}
setNotificationsEnabled(notificationsEnabled());
notifyToggle?.addEventListener('click',function(){setNotificationsEnabled(!notificationsEnabled());});
let active=null,sig='';
function setSidebarUnread(n){n=parseInt(n||0,10)||0;const b=document.getElementById('clientSidebarChatBadge');if(!b)return;b.textContent=String(n>99?'99+':n);b.classList.toggle('d-none',n<=0);if(typeof window.setClientSidebarChatUnread==='function')window.setClientSidebarChatUnread(n);}
function reduceSidebarUnread(n){n=parseInt(n||0,10)||0;const b=document.getElementById('clientSidebarChatBadge');if(!b||n<=0)return;const cur=parseInt((b.textContent||'0').replace(/\D/g,''),10)||0;setSidebarUnread(Math.max(0,cur-n));}
function visibleInboxUnread(){return links.reduce((sum,l)=>sum+(parseInt(l.dataset.unread||'0',10)||0),0)}
window.getCanonicalClientInboxUnread=visibleInboxUnread;
function parseJson(r){return r.text().then(t=>{let raw=(t||'').trim();try{return JSON.parse(raw)}catch(e){let a=raw.indexOf('{'),b=raw.lastIndexOf('}');if(a!==-1&&b>a)return JSON.parse(raw.slice(a,b+1));throw new Error(raw?raw.slice(0,220):'Invalid response')}})}
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
function avatarHtml(icon,name){return icon?'<img src="'+esc(icon)+'" alt="">':esc((name||'S').slice(0,1).toUpperCase())}
function renderMsg(m){
  const sender=m.sender||m.type||'';
  if(sender==='system'||m.message_type==='system'){
    const el=document.createElement('div');
    el.className='client-msg-system';
    el.innerHTML='<i class="fa-solid fa-circle-info"></i> '+(m.body||m.content||m.message||m.raw||'');
    box.appendChild(el);return;
  }
  const isMe=m.sender_type==='client'||m.sender==='client',el=document.createElement('div');el.className='client-msg'+(isMe?' me':'');const sellerIcon=active?active.dataset.sellerIcon:'';const sellerName=active?active.dataset.sellerName:'S';const myIcon=m.sender_icon&&isMe?m.sender_icon:(window._clientIcon||'');const myName=window._clientName||'Me';function avatarInner(icon,name){return icon?'<img src="'+esc(icon)+'" alt="">':esc((name||'?').slice(0,1).toUpperCase())}const av=isMe?avatarInner(myIcon,myName):avatarInner(m.sender_icon||sellerIcon,sellerName);const msgBody=m.body||m.raw||m.message||m.content||'';const body=m.message_type==='image'||m.type==='image'?'<img src="'+esc(msgBody)+'" onclick="window.open(this.src,\'_blank\')">':esc(msgBody).replace(/\n/g,'<br>');const seenByPeer=isMe?(parseInt(m.seen_by_seller??m.seen_by_booster??m.seen??0,10)===1):false;const checkClass=seenByPeer?'read':'sent';const checkTitle=seenByPeer?('Seen'+(m.read_at_fmt?' · '+m.read_at_fmt:'')):'Delivered';const check=isMe?'<i class="fa-solid fa-check-double client-msg-check '+checkClass+'" title="'+checkTitle+'"></i>':'';el.innerHTML='<span class="client-msg-a">'+av+'</span><div class="client-msg-inner"><div class="client-msg-b">'+body+'</div><div class="client-msg-t">'+esc(m.created_at_fmt||'')+check+'</div></div>';box.appendChild(el)
}
function setActive(link,markRead){if(!link)return;active=link;sig='';markRead=!!markRead;links.forEach(l=>l.classList.toggle('active',l===link));document.getElementById('clientSellerId').value=link.dataset.sellerId||'';document.getElementById('clientRefType').value=link.dataset.refType||'account';document.getElementById('clientRefId').value=link.dataset.refId||'';if(topbar)topbar.style.display='flex';if(form)form.style.display='flex';peerAvatar.innerHTML=avatarHtml(link.dataset.sellerIcon,link.dataset.sellerName);peerName.textContent=link.dataset.sellerName||'Seller';peerLabel.textContent=link.dataset.label||'CHAT';peerTitle.textContent=link.dataset.title||'';if(link.dataset.sourceUrl){sourceLink.href=link.dataset.sourceUrl;sourceLink.style.display='inline-flex';const labelEl=document.getElementById('client-source-label-text');if(labelEl)labelEl.textContent=link.dataset.sourceLabel||'View Listing';}else sourceLink.style.display='none';const unreadNow=parseInt(link.dataset.unread||'0',10)||0;if(markRead&&unreadNow>0){link.querySelectorAll('.client-unread').forEach(x=>x.remove());link.dataset.unread='0';reduceSidebarUnread(unreadNow);}load(true,markRead)}
function load(force,markRead){if(!active)return;markRead=!!markRead;const chatType=active.dataset.chatType||'';const refType=active.dataset.refType||'';const refId=active.dataset.refId||'';const isBooster=(chatType==='booster'||refType==='booster_order');const isTopup=(chatType==='topup'||refType==='topup_purchase');const fd=new FormData();let action='client_seller_chat_load';if(isTopup)action='client_topup_chat_load';else if(isBooster)action='client_booster_chat_load';else if(refType==='account')action='seller_account_chat_load';else if(refType==='item_purchase')action='item_chat_load';else if(refType==='premium_account')action='client_premium_account_chat_load';fd.append('action',action);fd.append('viewer_role','client');if(isTopup||refType==='item_purchase')fd.append('purchase_id',refId);else if(isBooster){fd.append('order_id',refId);fd.append('chat_key',active.dataset.chatKey||'');}else if(refType==='account'||refType==='premium_account')fd.append('account_id',refId);else{fd.append('seller_id',active.dataset.sellerId||'');fd.append('ref_type',refType||'account');fd.append('ref_id',refId);}fd.append('sig',force?'':sig);fd.append('mark_read',markRead?'1':'0');fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin'}).then(parseJson).then(d=>{if(!d||d.success===false)throw new Error((d&&d.message)||'Chat could not be loaded');if(d.sig)sig=d.sig;if(d.messages===null)return;const prevCount=box.querySelectorAll('.client-msg').length;box.innerHTML='';(d.messages||[]).forEach(renderMsg);if(!(d.messages||[]).length)box.innerHTML='<div class="client-empty"><i class="fa-duotone fa-comments" style="font-size:54px"></i><b>No messages yet</b><small>Send a new message below.</small></div>';if(!force&&d.messages&&d.messages.length>prevCount)playMessageSound();box.scrollTop=box.scrollHeight}).catch(err=>{box.innerHTML='<div class="client-empty"><b>Chat could not be loaded</b><small>'+esc(err&&err.message?err.message:'Please try again.')+'</small></div>'})}
function convKeyFromLink(l){return (l?.dataset.chatType||'seller')+':' + (l?.dataset.refType||'account')+':' + (l?.dataset.refId||'')+':' + (l?.dataset.chatKey||'')}
function shortText(t,n){t=(t||'').toString();return t.length>n?t.slice(0,n-1)+'…':t}
function renderConv(c){const a=document.createElement('a');const status=c.request_status||'paid',isReq=status==='request',chatType=c.chat_type||((c.ref_type==='booster_order')?'booster':(c.ref_type==='topup_purchase'?'topup':'seller')),seller=c.seller_username||(chatType==='booster'?'Booster':(chatType==='topup'?'Seller':'Seller')),title=c.title||'',refId=parseInt(c.ref_id||0,10)||0;const unread=parseInt(c.unread_client||0,10)||0;const isTopup=(chatType==='topup'||c.ref_type==='topup_purchase');const kindText=isReq?'Request':(chatType==='booster'?'Booster':(isTopup?'Top Up':'Order'));a.href='#';a.className='client-conv';a.dataset.status=status;const category=(chatType==='booster'||c.ref_type==='booster_order')?'boosting':(isTopup?'topups':(c.ref_type==='item_purchase'?'items':'accounts'));a.dataset.category=category;a.dataset.search=(seller+' '+title+' #'+refId+' '+kindText+' '+category+' top up topup').toLowerCase();a.dataset.sellerId=c.seller_id||'';a.dataset.refType=c.ref_type||'account';a.dataset.refId=refId;a.dataset.chatKey=c.chat_key||'';a.dataset.chatType=chatType;a.dataset.sellerName=seller;a.dataset.sellerIcon=c.seller_icon||'';a.dataset.title=title;a.dataset.label=isReq?'ACCOUNT REQUEST':(chatType==='booster'?'BOOSTER CHAT':(isTopup?'TOP UP ORDER':'ORDER'));a.dataset.sourceUrl=c.source_url||'';a.dataset.sourceLabel=c.source_label||(isReq?'View Shop Listing':'View Order');a.dataset.unread=unread;const av=c.seller_icon?'<img src="'+esc(c.seller_icon)+'" alt="">':esc((seller||'S').slice(0,1).toUpperCase());a.innerHTML='<div class="client-avatar">'+av+'</div><div class="client-conv-body"><div class="client-conv-top"><span class="client-conv-name">'+esc(seller)+'</span><span class="client-time">'+esc(c.last_time_label||'')+'</span></div><div class="client-conv-sub"><span class="client-kind '+(isReq?'request':'order')+'">'+kindText+'</span><span class="client-conv-ref">#'+refId+'</span>'+(title?'<span class="client-conv-title">'+esc(shortText(title,28))+'</span>':'')+'</div><div class="client-last">'+esc(shortText(c.last_body||'No message yet',62))+'</div></div><div class="client-conv-meta">'+(unread>0?'<span class="client-unread">'+unread+'</span>':'')+'</div>';return a}
function syncConversationList(convs){if(!list||!Array.isArray(convs))return;const activeKey=active?convKeyFromLink(active):'';const frag=document.createDocumentFragment();convs.forEach(c=>{const el=renderConv(c);if(((c.chat_type||((c.ref_type==='booster_order')?'booster':'seller'))+':'+(c.ref_type||'account')+':'+(c.ref_id||'')+':'+(c.chat_key||''))===activeKey){el.classList.add('active');active=el}frag.appendChild(el)});if(!convs.length){list.innerHTML='<div class="client-empty" style="min-height:260px"><i class="fa-duotone fa-comments" style="font-size:42px"></i><b>No conversations yet</b><small>Your account requests and orders will appear here.</small></div>';}else{list.innerHTML='';list.appendChild(frag)}links=Array.from(document.querySelectorAll('.client-conv'));applyFilter()}
let inboxSig='';function refreshInboxList(){const fd=new FormData();fd.append('action','client_seller_chat_inbox_state');fd.append('sig',inboxSig);fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin',cache:'no-store'}).then(parseJson).then(d=>{if(!d||!d.success)return;const conversations=Array.isArray(d.conversations)?d.conversations:[];const canonicalUnread=conversations.reduce((sum,c)=>sum+(parseInt(c.unread_client||0,10)||0),0);setSidebarUnread(canonicalUnread);if(d.sig&&d.sig===inboxSig){applyFilter();return}inboxSig=d.sig||'';syncConversationList(conversations);const countEl=document.querySelector('.client-chat-count b');if(countEl)countEl.textContent=d.total||0;const tabs=document.querySelectorAll('.client-chat-tab span');if(tabs[0])tabs[0].textContent=d.total||0;if(tabs[1])tabs[1].textContent=(d.account_count ?? 0);if(tabs[2])tabs[2].textContent=(d.item_count ?? 0);if(tabs[3])tabs[3].textContent=(d.topup_count ?? 0);if(tabs[4])tabs[4].textContent=(d.boosting_count ?? 0);}).catch(()=>{})}

function markAllAsRead(){if(!markAllBtn)return;const oldText=markAllBtn.innerHTML;markAllBtn.disabled=true;markAllBtn.innerHTML='<i class="fa-duotone fa-spinner-third fa-spin"></i> Marking...';const fd=new FormData();fd.append('action','client_seller_chat_mark_all_read');fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin'}).then(parseJson).then(d=>{if(!d||!d.success){alert((d&&d.message)||'Could not mark chats as read.');return}document.querySelectorAll('.client-unread').forEach(x=>x.remove());document.querySelectorAll('.client-conv').forEach(l=>l.dataset.unread='0');setSidebarUnread(0);inboxSig='';refreshInboxList();if(active){sig='';load(false,false)}}).catch(err=>alert(err&&err.message?err.message:'Could not mark chats as read.')).finally(()=>{markAllBtn.disabled=false;markAllBtn.innerHTML=oldText})}
markAllBtn?.addEventListener('click',markAllAsRead);
list?.addEventListener('click',e=>{const a=e.target.closest('.client-conv');if(!a)return;e.preventDefault();setActive(a,true)});
document.querySelectorAll('.client-chat-tab').forEach(tab=>tab.addEventListener('click',()=>{document.querySelectorAll('.client-chat-tab').forEach(t=>t.classList.remove('active'));tab.classList.add('active');applyFilter()}));
const unreadOnlyToggle=document.getElementById('clientUnreadOnlyToggle');
const unreadOnlyKey='lolboost_client_chat_unread_only';
function unreadOnlyEnabled(){return localStorage.getItem(unreadOnlyKey)==='1';}
function setUnreadOnlyEnabled(enabled){localStorage.setItem(unreadOnlyKey,enabled?'1':'0');unreadOnlyToggle?.classList.toggle('on',enabled);applyFilter();}
setUnreadOnlyEnabled(unreadOnlyEnabled());
unreadOnlyToggle?.addEventListener('click',()=>{setUnreadOnlyEnabled(!unreadOnlyEnabled());});
search?.addEventListener('input',applyFilter);
function applyFilter(){const f=document.querySelector('.client-chat-tab.active')?.dataset.filter||'all',q=(search?.value||'').toLowerCase().trim();links.forEach(l=>{const okF=f==='all'||l.dataset.category===f,okQ=!q||(l.dataset.search||'').includes(q),okU=!unreadOnlyEnabled()||(parseInt(l.dataset.unread||'0',10)>0);l.style.display=okF&&okQ&&okU?'':'none'})}
imageBtn?.addEventListener('click',()=>image.click());
image?.addEventListener('change',function(){const f=this.files[0];if(!f)return;const r=new FileReader();r.onload=e=>previewImg.src=e.target.result;r.readAsDataURL(f);previewName.textContent=f.name;preview.classList.add('is-open')});
previewRemove?.addEventListener('click',()=>{image.value='';preview.classList.remove('is-open')});
form?.addEventListener('submit',e=>{e.preventDefault();if(!active)return;const text=(input.value||'').trim(),hasFile=image&&image.files[0];if(!text&&!hasFile)return;send.disabled=true;const chatType=active.dataset.chatType||'',refType=active.dataset.refType||'',refId=active.dataset.refId||'',isBooster=(chatType==='booster'||refType==='booster_order'),isTopup=(chatType==='topup'||refType==='topup_purchase');const fd=new FormData(form);let action='client_seller_chat_send';if(isTopup)action='client_topup_chat_send';else if(isBooster)action='client_order_chat_send';else if(refType==='account')action='client_account_chat_send';else if(refType==='item_purchase')action='client_item_chat_send';else if(refType==='premium_account')action='client_premium_account_chat_send';fd.set('action',action);fd.set('viewer_role','client');if(isTopup||refType==='item_purchase')fd.set('purchase_id',refId);else if(isBooster){fd.set('order_id',refId);fd.set('chat_key',active.dataset.chatKey||'');if(active.dataset.chatKey&&!(parseInt(refId||0,10)>0))fd.set('action','client_raw_booster_chat_send');}else if(refType==='account'||refType==='premium_account')fd.set('account_id',refId);else{fd.set('seller_id',active.dataset.sellerId||'');fd.set('ref_type',refType||'account');fd.set('ref_id',refId);}fetch(AJAX_URL,{method:'POST',body:fd,credentials:'same-origin'}).then(parseJson).then(d=>{if(!d||d.success===false||d.validationErrors||d.sendToast&&d.sendToast.type==='danger')throw new Error((d&&d.sendToast&&d.sendToast.message)||d.message||'Message could not be sent.');input.value='';if(image)image.value='';preview.classList.remove('is-open');sig='';load(true,false);refreshInboxList()}).catch(err=>alert(err&&err.message?err.message:'Message could not be sent.')).finally(()=>send.disabled=false)});
input?.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form.dispatchEvent(new Event('submit',{cancelable:true}))}});
let realtimeTimer=null;
function realtimeRefresh(){
  clearTimeout(realtimeTimer);
  realtimeTimer=setTimeout(function(){
    inboxSig='';
    refreshInboxList();
    if(active) load(false,false);
    if(typeof socket !== 'undefined') {
      try { socket.emit('chat_unread_subscribe', {role:'client'}); } catch(e){}
    }
  },80);
}
window.addEventListener('lb-chat-update', realtimeRefresh);
if(typeof socket !== 'undefined') {
  socket.on('client_chat_update', realtimeRefresh);
  socket.on('seller_chat_update', realtimeRefresh);
  socket.on('booster_chat_update', realtimeRefresh);
  socket.on('topup_chat_update', realtimeRefresh);
  socket.on('item_chat_update', realtimeRefresh);
  socket.on('account_chat_update', realtimeRefresh);
  socket.on('chat_unread_update', function(payload){
    if(payload && payload.ok !== false) refreshInboxList();
  });
}
setSidebarUnread(visibleInboxUnread());
if(links[0])setActive(links[0],false);
refreshInboxList();
})();
</script>
<?= $this->stop() ?>
