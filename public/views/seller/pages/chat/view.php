<?php
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$conversation = is_array($conversation ?? null) ? $conversation : [];
$client = is_array($client ?? null) ? $client : [];
$kind = (string)($conversation['kind'] ?? 'account');
$kindLabel = (string)($conversation['kind_label'] ?? ucfirst($kind));
$refId = (int)($conversation['ref_id'] ?? 0);
$title = (string)($conversation['title'] ?? ($kindLabel . ' #' . $refId));
$clientName = (string)($client['username'] ?? 'Client');
$clientIcon = (string)($client['icon'] ?? '');
$clientInitial = strtoupper(substr($clientName, 0, 1));
$loadAction = (string)($conversation['load_action'] ?? 'seller_account_chat_load');
$sendAction = (string)($conversation['send_action'] ?? 'seller_account_chat_send');
$idField = (string)($conversation['id_field'] ?? 'account_id');
$idValue = (int)($conversation['id_value'] ?? $refId);
$sellerId = (int)($seller_data['id'] ?? 0);
$sellerName = (string)($seller_data['username'] ?? 'Seller');
$sellerIcon = (string)($seller_data['icon'] ?? '');
$extraFields = is_array($conversation['extra_fields'] ?? null) ? $conversation['extra_fields'] : [];
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Chat | LoLBoost.gg']]) ?>
<?= $this->start('styles') ?>
<style>
.seller-chat-view{--sc-bg:#1f2223;--sc-panel:#242728;--sc-panel-2:#202324;--sc-line:rgba(255,255,255,.075);--sc-text:#f5f7fb;--sc-muted:#9aa3ad;--sc-purple:#7367f0;--sc-green:#36d98a;color:var(--sc-text)}
.seller-chat-view .chat-shell{background:var(--sc-panel);border:1px solid var(--sc-line);border-radius:18px;overflow:hidden;min-height:calc(100vh - 155px)}
.seller-chat-view .chat-head{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 18px;border-bottom:1px solid var(--sc-line);background:#222526}.seller-chat-view .head-left{display:flex;align-items:center;gap:12px;min-width:0}.seller-chat-view .back-btn,.seller-chat-view .source-btn{border:1px solid rgba(255,255,255,.1);background:#303437;color:#fff!important;border-radius:10px;padding:.55rem .8rem;text-decoration:none;font-weight:800;font-size:.82rem}.seller-chat-view .avatar{width:42px;height:42px;border-radius:50%;overflow:hidden;background:#3b4046;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;flex:0 0 auto}.seller-chat-view .avatar img{width:100%;height:100%;object-fit:cover}.seller-chat-view .title{font-weight:900;color:#fff;font-size:1rem;line-height:1.1}.seller-chat-view .sub{font-size:.78rem;color:#9ca5b4;display:flex;align-items:center;gap:7px;flex-wrap:wrap}.seller-chat-view .kind{font-size:.64rem;font-weight:900;text-transform:uppercase;border-radius:999px;padding:.1rem .45rem;background:rgba(115,103,240,.16);color:#a69dff}.seller-chat-view .kind.item{background:rgba(54,217,138,.12);color:#60e6a5}.seller-chat-view .chat-body{height:calc(100vh - 315px);min-height:470px;background:#202426;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:10px}.seller-chat-view .chat-body::-webkit-scrollbar{width:6px}.seller-chat-view .chat-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:10px}.seller-chat-view .empty{margin:auto;text-align:center;color:#9aa3ad}.seller-chat-view .empty-icon{width:70px;height:70px;border-radius:20px;background:#292e33;border:1px solid var(--sc-line);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#8f86ff;font-size:1.6rem}.seller-chat-view .msg{display:flex;gap:9px;align-items:flex-end;max-width:76%}.seller-chat-view .msg.me{margin-left:auto;flex-direction:row-reverse}.seller-chat-view .msg.system{max-width:100%;align-self:center;display:block;text-align:center}.seller-chat-view .msg-avatar{width:30px;height:30px;border-radius:50%;overflow:hidden;background:#3b4046;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:900;color:#fff;flex:0 0 auto}.seller-chat-view .msg-avatar img{width:100%;height:100%;object-fit:cover}.seller-chat-view .bubble{background:#34383c;color:#fff;border-radius:16px 16px 16px 5px;padding:.65rem .85rem;font-size:.88rem;line-height:1.45;box-shadow:none}.seller-chat-view .me .bubble{background:#4b45a6;border-radius:16px 16px 5px 16px}.seller-chat-view .system .bubble{display:inline-block;background:rgba(255,255,255,.08);border-radius:999px;color:#c9ced8;font-size:.76rem;max-width:900px}.seller-chat-view .meta{font-size:.68rem;color:#7f8997;margin-top:3px}.seller-chat-view .me .meta{text-align:right}.seller-chat-view .bubble img{max-width:260px;border-radius:12px;display:block}.seller-chat-view .chat-input{border-top:1px solid var(--sc-line);background:#222526;padding:14px 18px}.seller-chat-view .input-row{display:flex;gap:9px;align-items:center}.seller-chat-view .chat-input input[type=text]{height:44px;background:#1e2123;border:1px solid rgba(255,255,255,.09);border-radius:12px;color:#fff;padding:0 14px;outline:0;width:100%}.seller-chat-view .icon-btn{width:42px;height:42px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:#303437;color:#dbe0e8;display:inline-flex;align-items:center;justify-content:center}.seller-chat-view .send-btn{background:#7367f0;color:#fff;border-color:#7367f0}.seller-chat-view .preview{margin-top:10px;display:flex;align-items:center;gap:10px;background:#1e2123;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:8px}.seller-chat-view .preview.d-none{display:none!important}.seller-chat-view .preview img{width:48px;height:48px;border-radius:9px;object-fit:cover}.seller-chat-view .preview button{margin-left:auto}.seller-chat-view .error{color:#ff6b8a;font-size:.78rem;margin-top:8px}.seller-chat-view .error.d-none{display:none}@media(max-width:768px){.seller-chat-view .chat-head{align-items:flex-start;flex-direction:column}.seller-chat-view .chat-body{height:calc(100vh - 365px);min-height:400px}.seller-chat-view .msg{max-width:90%}}.seller-chat-view .msg-ticks{display:inline-flex;align-items:center;margin-left:4px;font-size:.75rem;line-height:1}.seller-chat-view .msg-ticks.seen{color:#3dd68c}.seller-chat-view .msg-ticks.delivered,.seller-chat-view .msg-ticks.sent{color:rgba(255,255,255,.3)}
</style>
<?= $this->stop() ?>

<div class="seller-chat-view">
  <div class="chat-shell">
    <div class="chat-head">
      <div class="head-left">
        <a class="back-btn" href="<?= BASE_URL ?>/seller-area/chat"><i class="fa-solid fa-arrow-left me-1"></i> Inbox</a>
        <div class="avatar"><?php if ($clientIcon): ?><img src="<?= $h($clientIcon) ?>" alt=""><?php else: ?><?= $h($clientInitial) ?><?php endif; ?></div>
        <div style="min-width:0">
          <div class="title text-truncate"><?= $h($clientName) ?></div>
          <div class="sub"><span class="kind <?= $kind === 'item' ? 'item' : '' ?>"><?= $h($kindLabel) ?></span><span>#<?= $refId ?></span><span class="text-truncate"><?= $h($title) ?></span></div>
        </div>
      </div>
      <a class="source-btn" href="<?= $h($conversation['source_url'] ?? (BASE_URL . '/seller-area/chat')) ?>"><i class="fa-solid fa-up-right-from-square me-1"></i> Open <?= $h($kindLabel) ?></a>
    </div>

    <div class="chat-body" id="chat_messages">
      <div class="empty"><div class="empty-icon"><i class="fa-duotone fa-spinner-third fa-spin"></i></div><div>Loading messages...</div></div>
    </div>

    <div class="chat-input">
      <form id="sellerUnifiedChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $h($sendAction) ?>">
        <input type="hidden" name="<?= $h($idField) ?>" value="<?= $idValue ?>">
        <?php foreach ($extraFields as $extraName => $extraValue): ?><input type="hidden" name="<?= $h($extraName) ?>" value="<?= $h($extraValue) ?>"><?php endforeach; ?>
        <input type="file" class="d-none" id="chatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">
        <div class="input-row">
          <input type="text" name="message" id="chatMessage" placeholder="Type your message">
          <button type="button" class="icon-btn" id="attachBtn" title="Attach image"><i class="fa-duotone fa-paperclip"></i></button>
          <button type="submit" class="icon-btn send-btn" id="sendBtn" title="Send"><i class="fa-duotone fa-paper-plane"></i></button>
        </div>
        <div class="preview d-none" id="preview"><img src="" alt="" id="previewImg"><div><b>Image ready</b><br><small id="previewName"></small></div><button type="button" class="icon-btn" id="removeFile"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="error d-none" id="chatError"></div>
      </form>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  const AJAX = window.AJAX_URL || '<?= AJAX_URL ?>';
  const LOAD_ACTION = <?= json_encode($loadAction) ?>;
  const ID_FIELD = <?= json_encode($idField) ?>;
  const ID_VALUE = <?= (int)$idValue ?>;
  const EXTRA_FIELDS = <?= json_encode($extraFields) ?>;
  const SELLER_ID = <?= (int)$sellerId ?>;
  const SELLER_ICON = <?= json_encode($sellerIcon) ?>;
  const CLIENT_ICON = <?= json_encode($clientIcon) ?>;
  const CLIENT_INITIAL = <?= json_encode($clientInitial) ?>;
  const SELLER_INITIAL = <?= json_encode(strtoupper(substr($sellerName,0,1))) ?>;
  const chat = document.getElementById('chat_messages');
  const form = document.getElementById('sellerUnifiedChatForm');
  const input = document.getElementById('chatMessage');
  const file = document.getElementById('chatFile');
  const attach = document.getElementById('attachBtn');
  const preview = document.getElementById('preview');
  const previewImg = document.getElementById('previewImg');
  const previewName = document.getElementById('previewName');
  const removeFile = document.getElementById('removeFile');
  const err = document.getElementById('chatError');
  const notifyKey = 'lolboost_seller_chat_sound_notifications';
  const audioBase = (typeof asset_url !== 'undefined' && asset_url) ? asset_url : '';
  const chatNotif = new Audio(audioBase + '/core/dash/audio/new-message.mp3');
  let sig = '', previewUrl = null, loading = false, didInitMessages = false, destroyed = false;
  let seenMessageIds = new Set();

  function esc(s){ const d=document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
  function decode(s){ const t=document.createElement('textarea'); t.innerHTML = s == null ? '' : String(s); return t.value; }
  function time(ts){ ts = parseInt(ts || 0, 10); if(!ts) return ''; const d = new Date(ts*1000); return d.toLocaleDateString('de-DE') + ' ' + d.toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'}); }
  function avatar(sender, icon){ const fallback = sender === 'seller' ? SELLER_INITIAL : CLIENT_INITIAL; const src = icon || (sender === 'seller' ? SELLER_ICON : CLIENT_ICON); return src ? `<span class="msg-avatar"><img src="${esc(src)}" alt=""></span>` : `<span class="msg-avatar">${esc(fallback)}</span>`; }
  function content(m){ let c = m.content || m.message || m.raw || ''; if ((m.message_type || m.type) === 'image' && !String(c).includes('<img')) c = `<img src="${esc(m.message || m.raw || c)}" alt="image">`; return decode(c); }
  function ticks(m){
    // For account/item JSON chats seen_by_client is the authoritative read receipt.
    // For direct seller/client DB chats fall back to is_read/seen.
    const hasClientSeen = Object.prototype.hasOwnProperty.call(m, 'seen_by_client');
    const clientSeen = hasClientSeen && (m.seen_by_client == 1 || m.seen_by_client === true);
    const directSeen = !hasClientSeen && (m.is_read == 1 || m.is_read === '1' || m.seen == 1 || m.seen === true);
    if(clientSeen || directSeen) {
      const title = 'Read by client' + (m.read_at_fmt ? (' - ' + m.read_at_fmt) : '');
      return `<span class="msg-ticks seen" title="${esc(title)}"><i class="fa-solid fa-check-double"></i></span>`;
    }
    if(m.notify == 1) return '<span class="msg-ticks delivered" title="Delivered"><i class="fa-solid fa-check-double"></i></span>';
    return '<span class="msg-ticks sent" title="Sent"><i class="fa-solid fa-check"></i></span>';
  }

  function render(id, m){
    if(!m || m.deleted || m.type === 'deleted') return '';
    const sender = m.sender || m.type || 'client';
    const isSystem = sender === 'system';
    const isMe = sender === 'seller' && String(m.sender_id || '') === String(SELLER_ID || m.sender_id || '');
    const cls = isSystem ? 'msg system' : (isMe ? 'msg me' : 'msg');
    const av = isSystem ? '' : avatar(sender, m.sender_icon || '');
    const body = content(m);
    const ts = m.time || (m.created_at ? Date.parse(m.created_at)/1000 : 0);
    const tickHtml = isMe ? ticks(m) : '';
    return `<div class="${cls}" data-id="${esc(id)}">${av}<div><div class="bubble">${body}</div><div class="meta">${time(ts)}${tickHtml}</div></div></div>`;
  }
  function updateScroll(){ if(chat) chat.scrollTop = chat.scrollHeight; }
  function notificationsEnabled(){ return localStorage.getItem(notifyKey) !== '0'; }
  function playMessageSound(){ if(!notificationsEnabled()) return; try { chatNotif.currentTime = 0; chatNotif.volume = 0.6; chatNotif.play(); } catch(e){} }
  function showBrowserNotification(body){
    if(!notificationsEnabled() || !('Notification' in window) || Notification.permission !== 'granted' || document.visibilityState === 'visible') return;
    try { new Notification('New buyer message', { body: body || 'A buyer sent you a new message.' }); } catch(e){}
  }
  function isIncomingBuyerMessage(m){
    if(!m || m.deleted || m.type === 'deleted') return false;
    const sender = String(m.sender || m.type || 'client');
    return sender !== 'seller' && sender !== 'system' && sender !== 'admin';
  }
  function messageText(m){
    const raw = m.content || m.message || m.raw || '';
    return String(raw).replace(/<[^>]*>/g, '').trim().slice(0, 120) || 'A buyer sent you a new message.';
  }
  function load(markRead){
    if(destroyed || loading) return; loading = true;
    const fd = new FormData();
    fd.append('action', LOAD_ACTION);
    fd.append(ID_FIELD, String(ID_VALUE));
    fd.append('sig', sig);
    fd.append('mark_read', markRead ? '1' : '0');
    fd.append('viewer_role', 'seller');
    Object.keys(EXTRA_FIELDS || {}).forEach(k => fd.append(k, String(EXTRA_FIELDS[k])));
    fetch(AJAX, {method:'POST', body:fd, credentials:'same-origin'})
      .then(r => r.text())
      .then(resp => {
      loading = false;
      let r; try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){
        const raw=String(resp||''),a=raw.indexOf('{'),b=raw.lastIndexOf('}');
        if(a<0||b<=a){ if(chat) chat.innerHTML='<div class="empty"><div>Chat could not be loaded.</div></div>'; return; }
        try{r=JSON.parse(raw.slice(a,b+1));}catch(_){return;}
      }
      if(!r || r.success === false){ if(chat) chat.innerHTML='<div class="empty"><div>' + esc((r&&r.message)||'Chat could not be loaded.') + '</div></div>'; return; }
      const messages = r.messages || {};
      const keys = Object.keys(messages);
      const newSig = JSON.stringify(messages);
      if(newSig === sig) return;
      const incomingNew = [];
      keys.forEach(k => {
        if(!seenMessageIds.has(String(k)) && isIncomingBuyerMessage(messages[k])) incomingNew.push(messages[k]);
      });
      if(didInitMessages && incomingNew.length){
        playMessageSound();
        showBrowserNotification(messageText(incomingNew[incomingNew.length - 1]));
      }
      seenMessageIds = new Set(keys.map(k => String(k)));
      didInitMessages = true;
      sig = newSig;
      if(!keys.length){ chat.innerHTML = '<div class="empty"><div class="empty-icon"><i class="fa-duotone fa-comment-dots"></i></div><div>No messages yet.</div></div>'; return; }
      let html = '';
      keys.forEach(k => { html += render(k, messages[k]); });
      chat.innerHTML = html; updateScroll();
    }).catch(function(){ loading = false; });
  }
  function setError(msg){ if(!err) return; err.textContent = msg || ''; err.classList.toggle('d-none', !msg); }
  function clearPreview(){ if(previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null; if(file) file.value=''; preview?.classList.add('d-none'); }
  attach?.addEventListener('click', () => file?.click());
  file?.addEventListener('change', () => { const f = file.files && file.files[0]; if(!f){ clearPreview(); return; } if(previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = URL.createObjectURL(f); previewImg.src = previewUrl; previewName.textContent = f.name; preview.classList.remove('d-none'); });
  removeFile?.addEventListener('click', clearPreview);
  document.addEventListener('paste', e => { const items = e.clipboardData && e.clipboardData.items; if(!items || !file) return; for(const item of items){ if(item.type && item.type.indexOf('image/')===0){ const f=item.getAsFile(); const dt=new DataTransfer(); dt.items.add(f); file.files=dt.files; file.dispatchEvent(new Event('change')); break; } } });
  form?.addEventListener('submit', function(e){
    e.preventDefault(); setError('');
    if(!input.value.trim() && !(file.files && file.files.length)){ setError('Please type a message or attach an image.'); return; }
    const fd = new FormData(form);
    $.ajax({url: AJAX, method:'POST', data:fd, processData:false, contentType:false, success:function(resp){ let r; try{r=typeof resp==='string'?JSON.parse(resp):resp;}catch(e){r={}}; if(r.success){ input.value=''; clearPreview(); sig=''; load(false); input.focus(); } else { setError((r.sendToast && r.sendToast.message) || 'Message could not be sent.'); } }, error:function(){ setError('Message could not be sent.'); }});
  });
  if(notificationsEnabled() && 'Notification' in window && Notification.permission === 'default') { try { Notification.requestPermission(); } catch(e){} }
  const realtimeRefresh = function(){ load(false); };
  if(typeof socket !== 'undefined') {
    socket.on('seller_chat_update', realtimeRefresh);
    socket.on('topup_chat_update', realtimeRefresh);
    socket.on('seller_topup_chat_update', realtimeRefresh);
    socket.on('item_chat_update', realtimeRefresh);
    socket.on('account_chat_update', realtimeRefresh);
  }
  window.lbDestroySellerUnifiedChat = function(){
    destroyed = true;
    if(typeof socket !== 'undefined') {
      try { socket.off('seller_chat_update', realtimeRefresh); } catch(e){}
      try { socket.off('topup_chat_update', realtimeRefresh); } catch(e){}
      try { socket.off('seller_topup_chat_update', realtimeRefresh); } catch(e){}
      try { socket.off('item_chat_update', realtimeRefresh); } catch(e){}
      try { socket.off('account_chat_update', realtimeRefresh); } catch(e){}
    }
  };
  load(true);
})();
</script>
<?= $this->stop() ?>
