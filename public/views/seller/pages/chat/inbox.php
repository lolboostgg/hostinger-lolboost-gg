<?php
$chat_enabled = !empty($seller_data['allow_chat_requests']);
$conversations = is_array($conversations ?? null) ? $conversations : [];
$conversationCount = count($conversations);
$accountCount = count(array_filter($conversations, fn($c) => ($c['kind'] ?? '') === 'account'));
$itemCount = count(array_filter($conversations, fn($c) => ($c['kind'] ?? '') === 'item'));
$topupCount = count(array_filter($conversations, fn($c) => in_array(($c['kind'] ?? ''), ['topup','top_up','top-up'], true)));
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$fmtTime = function ($ts) {
    $ts = (int)$ts;
    if ($ts <= 0) return '';
    if (date('Y-m-d', $ts) === date('Y-m-d')) return date('H:i', $ts);
    if ($ts > strtotime('-7 days')) return date('D', $ts);
    return date('M j', $ts);
};
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Chat Inbox | LoLBoost.gg']]) ?>
<?= $this->start('styles') ?>
<style>
.seller-chat-page{--sc-bg:#1f2223;--sc-panel:#242728;--sc-panel-2:#202324;--sc-soft:#2b2f31;--sc-line:rgba(255,255,255,.075);--sc-text:#f5f7fb;--sc-muted:#9aa3ad;--sc-purple:#7367f0;--sc-green:#36d98a;--sc-blue:#5c7cfa;color:var(--sc-text)}
.seller-chat-page .chat-shell{background:var(--sc-panel);border:1px solid var(--sc-line);border-radius:18px;overflow:hidden;min-height:calc(100vh - 155px);box-shadow:none}
.seller-chat-page .chat-head{height:62px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid var(--sc-line);background:#222526}
.seller-chat-page .chat-title{display:flex;align-items:center;gap:12px;font-size:1.05rem;font-weight:800;color:#fff}.seller-chat-page .chat-title-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(115,103,240,.12);border:1px solid rgba(115,103,240,.24);color:#9d94ff}
.seller-chat-page .chat-head-right{display:flex;align-items:center;gap:14px}.seller-chat-page .chat-count{font-size:.8rem;color:#aab2c0}.seller-chat-page .chat-count .ok{color:var(--sc-green);font-weight:700}.seller-chat-page .chat-count .off{color:#ff6b8a;font-weight:700}.seller-chat-page .chat-settings{border:1px solid rgba(255,255,255,.1);background:#303437;color:#fff!important;border-radius:10px;padding:.54rem .85rem;text-decoration:none;font-weight:800;font-size:.82rem}
.seller-chat-page .chat-tabs{height:58px;display:flex;align-items:center;gap:22px;padding:0 18px;border-bottom:1px solid var(--sc-line);background:#242728}.seller-chat-page .chat-tab{border:0;background:transparent;color:#b1b7c2;font-weight:800;font-size:.86rem;display:flex;align-items:center;gap:8px;padding:.55rem .8rem;border-radius:12px}.seller-chat-page .chat-tab.active{background:#34383d;color:#fff}.seller-chat-page .chat-tab .badge{background:#34383d;color:#cdd3dc;border-radius:999px;font-size:.68rem;padding:.15rem .42rem}.seller-chat-page .chat-tab.active .badge{background:rgba(255,255,255,.13);color:#fff}
.seller-chat-page .chat-body{display:grid;grid-template-columns:330px 1fr;min-height:calc(100vh - 275px)}.seller-chat-page .chat-left{background:#202426;border-right:1px solid var(--sc-line);min-width:0}.seller-chat-page .notify-row{height:52px;display:flex;align-items:center;gap:10px;padding:0 16px;border-bottom:1px solid var(--sc-line);font-size:.82rem;color:#d3d7df}.seller-chat-page .toggle{width:42px;height:22px;border:1px solid rgba(255,255,255,.08);border-radius:999px;background:#34393d;position:relative;display:inline-flex;align-items:center;padding:0;cursor:pointer;outline:0;transition:background .18s ease,border-color .18s ease,box-shadow .18s ease;box-shadow:inset 0 0 0 1px rgba(0,0,0,.12)}.seller-chat-page .toggle:before{content:"";position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#8f98a5;transition:left .18s ease,background .18s ease,box-shadow .18s ease;box-shadow:0 2px 5px rgba(0,0,0,.35)}.seller-chat-page .toggle:hover{border-color:rgba(255,255,255,.16)}.seller-chat-page .toggle:focus-visible{box-shadow:0 0 0 3px rgba(115,103,240,.28)}.seller-chat-page .toggle.on{background:rgba(54,217,138,.22);border-color:rgba(54,217,138,.35)}.seller-chat-page .toggle.on:before{left:21px;background:var(--sc-green);box-shadow:0 0 0 3px rgba(54,217,138,.12),0 2px 6px rgba(0,0,0,.35)}
.seller-chat-page .search-row{padding:14px 14px;border-bottom:1px solid var(--sc-line);position:relative}.seller-chat-page .search-row input{width:100%;height:44px;border-radius:12px;border:1px solid rgba(255,255,255,.09);background:#1e2123;color:#fff;padding:0 42px 0 14px;outline:0}.seller-chat-page .search-row i{position:absolute;right:28px;top:28px;color:#8f98a5}.seller-chat-page .conv-list{max-height:calc(100vh - 390px);overflow:auto}.seller-chat-page .conv-list::-webkit-scrollbar{width:5px}.seller-chat-page .conv-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:10px}
.seller-chat-page .conv{display:grid;grid-template-columns:42px 1fr auto;gap:10px;align-items:center;padding:12px 14px;border-bottom:1px solid var(--sc-line);color:#e8ebf1;text-decoration:none}.seller-chat-page .conv:hover{background:#2b3033;color:#fff}.seller-chat-page .avatar{width:42px;height:42px;border-radius:50%;overflow:hidden;background:#3b4046;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff}.seller-chat-page .avatar img{width:100%;height:100%;object-fit:cover}.seller-chat-page .conv-name{font-weight:900;font-size:.85rem;color:#fff}.seller-chat-page .conv-sub{font-size:.72rem;color:#8e98a7;display:flex;gap:6px;align-items:center}.seller-chat-page .kind{font-size:.64rem;font-weight:900;text-transform:uppercase;border-radius:999px;padding:.08rem .38rem;background:rgba(115,103,240,.16);color:#a69dff}.seller-chat-page .kind.item{background:rgba(54,217,138,.12);color:#60e6a5}.seller-chat-page .kind.topup{background:rgba(96,165,250,.13);color:#93c5fd;border:1px solid rgba(96,165,250,.22)}.seller-chat-page .kind.request{background:rgba(255,171,0,.15);color:#ffca63;border:1px solid rgba(255,171,0,.22)}.seller-chat-page .last{margin-top:2px;font-size:.76rem;color:#b2bac6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:205px}.seller-chat-page .time{font-size:.68rem;color:#8a94a1;text-align:right}.seller-chat-page .unread{display:inline-flex;min-width:18px;height:18px;align-items:center;justify-content:center;border-radius:999px;background:#446dff;color:#fff;font-weight:900;font-size:.65rem;margin-top:4px}.seller-chat-page .empty-left{padding:48px 18px;text-align:center;color:#8d96a5}.seller-chat-page .empty-icon{width:70px;height:70px;border-radius:20px;background:#292e33;border:1px solid var(--sc-line);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;color:#8f86ff;font-size:1.7rem}.seller-chat-page .empty-left b{color:#fff}.seller-chat-page .conv.active{background:#303437;color:#fff;box-shadow:inset 3px 0 0 var(--sc-purple)}.seller-chat-page .chat-stage{background:#202426;display:flex;align-items:center;justify-content:center;text-align:center;padding:30px;color:#9ca6b6;min-width:0}.seller-chat-page .chat-stage h3{color:#fff;font-size:1.15rem;font-weight:900;margin:.25rem 0}.seller-chat-page .chat-stage p{margin:0;color:#9ca6b6}.seller-chat-page .chat-stage.has-chat{display:block;text-align:left;padding:0;overflow:hidden}.seller-chat-page .chat-stage.has-chat .seller-chat-view{height:100%;min-height:100%}.seller-chat-page .chat-stage.has-chat .seller-chat-view .chat-shell{border:0;border-radius:0;min-height:calc(100vh - 276px);height:100%;background:#202426}.seller-chat-page .chat-stage.has-chat .seller-chat-view .back-btn{display:none}.seller-chat-page .chat-stage.has-chat .seller-chat-view .chat-body{height:calc(100vh - 430px);min-height:360px}.seller-chat-page .stage-loader{margin:auto;text-align:center;color:#9ca6b6}.seller-chat-page .mobile-hint{display:none}@media(max-width:991px){.seller-chat-page .chat-body{grid-template-columns:1fr}.seller-chat-page .chat-stage{min-height:350px}.seller-chat-page .conv-list{max-height:none}.seller-chat-page .chat-head{height:auto;gap:10px;align-items:flex-start;padding:14px;flex-direction:column}.seller-chat-page .chat-tabs{overflow-x:auto}.seller-chat-page .mobile-hint{display:block}}
</style>
<?= $this->stop() ?>

<div class="seller-chat-page">
  <div class="chat-shell">
    <div class="chat-head">
      <div class="chat-title"><span class="chat-title-icon"><i class="fa-duotone fa-messages"></i></span> Chat Inbox</div>
      <div class="chat-head-right">
        <span class="chat-count"><b><?= (int)$conversationCount ?></b> conversations · <span class="<?= $chat_enabled ? 'ok' : 'off' ?>"><?= $chat_enabled ? 'Accepting requests' : 'Requests disabled' ?></span></span>
        <a class="chat-settings" href="<?= BASE_URL ?>/seller-area/profile#chat-settings"><i class="fa-solid fa-gear me-1"></i> Chat Settings</a>
      </div>
    </div>

    <div class="chat-tabs" id="chat-tabs">
      <button type="button" class="chat-tab active" data-filter="all"><i class="fa-solid fa-inbox"></i> All <span class="badge"><?= (int)$conversationCount ?></span></button>
      <button type="button" class="chat-tab" data-filter="account"><i class="fa-solid fa-database"></i> Accounts <span class="badge"><?= (int)$accountCount ?></span></button>
      <button type="button" class="chat-tab" data-filter="item"><i class="fa-solid fa-gift"></i> Items <span class="badge"><?= (int)$itemCount ?></span></button>
      <button type="button" class="chat-tab" data-filter="topup"><i class="fa-solid fa-coins"></i> Top Ups <span class="badge"><?= (int)$topupCount ?></span></button>
    </div>

    <div class="chat-body">
      <aside class="chat-left">
        <div class="notify-row"><button type="button" id="chatNotifyToggle" class="toggle on" aria-label="Toggle chat sound notifications"></button> <span id="chatNotifyLabel">Sound notifications</span></div>
        <div class="notify-row"><button type="button" id="sellerUnreadOnlyToggle" class="toggle" aria-label="Toggle unread only filter"></button> <span>Unread only</span></div>
        <div class="search-row"><input id="conv-search" placeholder="Search..."><i class="fa-solid fa-magnifying-glass"></i></div>
        <div class="conv-list" id="conv-list">
          <?php if (empty($conversations)): ?>
            <div class="empty-left"><div class="empty-icon"><i class="fa-duotone fa-comments"></i></div><b>No conversations yet</b><br><small>Account, item and top up order chats will appear here.</small></div>
          <?php else: foreach ($conversations as $conv): ?>
            <?php
              $kind = $conv['kind'] ?? 'account';
              if (in_array($kind, ['top_up','top-up'], true)) $kind = 'topup';
              $name = $conv['client_username'] ?? 'Client';
              $initial = strtoupper(substr($name, 0, 1));
              $last = trim((string)($conv['last_body'] ?? '')) !== '' ? mb_strimwidth((string)$conv['last_body'], 0, 62, '…') : 'No message yet';
              $unread = (int)($conv['unread_seller'] ?? 0);
              $requestStatus = $conv['request_status'] ?? '';
              $kindClass = trim(($kind === 'item' ? 'item' : '') . ' ' . ($kind === 'topup' ? 'topup' : '') . ' ' . ($requestStatus === 'request' ? 'request' : ''));
              $kindLabel = $conv['kind_label'] ?? ($kind === 'topup' ? 'Top Up' : ucfirst($kind));
            ?>
            <a class="conv" href="<?= $h($conv['url'] ?? '#') ?>" data-kind="<?= $h($kind) ?>" data-search="<?= $h(strtolower($name . ' ' . ($conv['title'] ?? '') . ' ' . ($conv['ref_id'] ?? ''))) ?>" data-unread="<?= (int)$unread ?>" data-signature="<?= $h(($conv['id'] ?? '') . '|' . ($conv['last_message_at'] ?? 0) . '|' . ($conv['last_body'] ?? '') . '|' . $unread) ?>">
              <div class="avatar"><?php if (!empty($conv['client_icon'])): ?><img src="<?= $h($conv['client_icon']) ?>" alt=""><?php else: ?><?= $h($initial) ?><?php endif; ?></div>
              <div style="min-width:0">
                <div class="conv-name text-truncate"><?= $h($name) ?></div>
                <div class="conv-sub"><span class="kind <?= $h($kindClass) ?>"><?= $h($kindLabel) ?></span><span>#<?= (int)($conv['ref_id'] ?? 0) ?></span></div>
                <div class="last"><?= $h($last) ?></div>
              </div>
              <div><div class="time"><?= $h($fmtTime($conv['last_message_at'] ?? 0)) ?></div><?php if ($unread > 0): ?><span class="unread"><?= $unread ?></span><?php endif; ?></div>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </aside>
      <section class="chat-stage" id="chat-stage">
        <div><div class="empty-icon"><i class="fa-duotone fa-message-dots"></i></div><h3>Select a conversation</h3><p>Pick an account, item or top up chat from the left to answer the buyer.</p><p class="mobile-hint mt-2">Tip: open a chat from the list above.</p></div>
      </section>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  const search = document.getElementById('conv-search');
  const tabs = document.querySelectorAll('.chat-tab');
  const unreadOnlyToggle = document.getElementById('sellerUnreadOnlyToggle');
  let unreadOnlyActive = false;
  function unreadOnlyEnabled(){ return unreadOnlyActive; }
  function setUnreadOnlyEnabled(enabled){ unreadOnlyActive = !!enabled; unreadOnlyToggle?.classList.toggle('on', unreadOnlyActive); unreadOnlyToggle?.setAttribute('aria-pressed', unreadOnlyActive ? 'true' : 'false'); apply(); }
  const list = document.getElementById('conv-list');
  const notifyToggle = document.getElementById('chatNotifyToggle');
  const notifyLabel = document.getElementById('chatNotifyLabel');
  const stage = document.getElementById('chat-stage');
  const notifyKey = 'lolboost_seller_chat_sound_notifications';
  const audioBase = (typeof asset_url !== 'undefined' && asset_url) ? asset_url : '';
  const chatNotif = new Audio(audioBase + '/core/dash/audio/new-message.mp3');
  let filter = 'all';
  let didInitSnapshot = false;
  let snapshot = getSnapshot(document);
  let activeChatUrl = '';

  function notificationsEnabled(){ return localStorage.getItem(notifyKey) !== '0'; }
  function setNotificationsEnabled(enabled){
    localStorage.setItem(notifyKey, enabled ? '1' : '0');
    notifyToggle?.classList.toggle('on', enabled);
    if (notifyLabel) notifyLabel.textContent = enabled ? 'Sound notifications' : 'Sound muted';
    if (enabled && 'Notification' in window && Notification.permission === 'default') { try { Notification.requestPermission(); } catch(e){} }
  }
  function playMessageSound(){ if(!notificationsEnabled()) return; try { chatNotif.currentTime = 0; chatNotif.volume = 0.6; chatNotif.play(); } catch(e){} }
  function showBrowserNotification(title, body){
    if(!notificationsEnabled() || !('Notification' in window) || Notification.permission !== 'granted' || document.visibilityState === 'visible') return;
    try { new Notification(title || 'New buyer message', { body: body || 'A buyer sent you a new message.' }); } catch(e){}
  }
  function isConversationRead(el){
    return false;
  }
  function clearUnread(el){
    if(!el) return;
    el.dataset.unread = '0';
    el.querySelectorAll('.unread').forEach(b => b.remove());
  }
  function markConversationRead(el){
    if(!el) return;
    clearUnread(el);
  }
  function applyReadStates(root){
    // Database state is authoritative; do not conceal server-unread rows locally.
  }
  function getSnapshot(root){
    const map = {};
    root.querySelectorAll('.conv').forEach(el => {
      const key = el.getAttribute('href') || el.dataset.search || Math.random().toString(36);
      const unread = isConversationRead(el) ? 0 : (parseInt(el.dataset.unread || '0', 10) || 0);
      map[key] = { sig: el.dataset.signature || '', unread: unread, name: (el.querySelector('.conv-name')?.textContent || 'Buyer').trim(), last: (el.querySelector('.last')?.textContent || '').trim() };
    });
    return map;
  }
  window.getCanonicalSellerInboxUnread = function(){
    return Array.from(document.querySelectorAll('#conv-list .conv')).reduce(function(sum, el){
      return sum + (parseInt(el.dataset.unread || '0', 10) || 0);
    }, 0);
  };
  function apply(){
    const q = (search?.value || '').toLowerCase().trim();
    document.querySelectorAll('.conv').forEach(el => {
      const elKind = (el.dataset.kind || '').replace('top_up','topup').replace('top-up','topup');
      const okKind = filter === 'all' || elKind === filter;
      const okSearch = !q || (el.dataset.search || '').includes(q);
      const okUnread = !unreadOnlyEnabled() || (parseInt(el.dataset.unread || '0', 10) > 0);
      el.style.display = okKind && okSearch && okUnread ? 'grid' : 'none';
    });
  }
  function setActiveLink(url){
    document.querySelectorAll('.conv').forEach(el => el.classList.toggle('active', (el.getAttribute('href') || '') === url));
  }
  function getChatMessageContainer(root){
    if(!root) return null;
    return root.querySelector('.seller-chat-view .chat-body')
      || root.querySelector('.seller-chat-view [data-chat-messages]')
      || root.querySelector('.seller-chat-view .messages')
      || root.querySelector('.seller-chat-view .chat-messages')
      || root.querySelector('.seller-chat-view .messages-list');
  }
  function refreshActiveChat(){
    if(!stage || !activeChatUrl || !stage.classList.contains('has-chat')) return;
    const focused = document.activeElement;
    if(focused && stage.contains(focused) && /^(INPUT|TEXTAREA|SELECT)$/.test(focused.tagName)) return;
    fetch(activeChatUrl, {credentials:'same-origin', cache:'no-store', headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextView = doc.querySelector('.seller-chat-view');
        const currentBox = getChatMessageContainer(stage);
        const nextBox = getChatMessageContainer(doc);
        if(!nextView || !currentBox || !nextBox) return;
        const wasNearBottom = (currentBox.scrollTop + currentBox.clientHeight) >= (currentBox.scrollHeight - 80);
        if(currentBox.innerHTML !== nextBox.innerHTML){
          currentBox.innerHTML = nextBox.innerHTML;
          if(wasNearBottom) currentBox.scrollTop = currentBox.scrollHeight;
          if(typeof playMessageSound === 'function') playMessageSound();
        }
        const currentHead = stage.querySelector('.seller-chat-view .chat-head');
        const nextHead = nextView.querySelector('.chat-head');
        if(currentHead && nextHead && currentHead.innerHTML !== nextHead.innerHTML){
          currentHead.innerHTML = nextHead.innerHTML;
        }
      }).catch(() => {});
  }
  function executeChatScripts(doc){
    doc.querySelectorAll('script').forEach(oldScript => {
      const txt = oldScript.textContent || '';
      if(txt.indexOf('sellerUnifiedChatForm') === -1 && txt.indexOf('LOAD_ACTION') === -1) return;
      const script = document.createElement('script');
      if(oldScript.src){
        script.src = oldScript.src;
      } else {
        // Patch: intercept fetch in the chat-view script so mark_read=1 is only
        // sent when the tab is visible (seller is actively viewing the chat),
        // never on background polls. We wrap fetch before injecting the script.
        const patched = `
(function(){
  const _origFetch = window.fetch;
  let _markReadSent = false;
  window._patchedChatFetch = function(input, init) {
    if(init && init.body instanceof FormData) {
      const action = init.body.get('action') || '';
      if(action === 'seller_direct_chat_load' || action === 'seller_chat_load') {
        const tabVisible = document.visibilityState !== 'hidden';
        if(!_markReadSent && tabVisible) {
          init.body.set('mark_read', '1');
          _markReadSent = true;
        } else {
          init.body.set('mark_read', '0');
        }
      }
    }
    return _origFetch(input, init);
  };
  // Also handle seller_chat_mark_seen — only call when tab is visible
  const _origFetch2 = window.fetch;
  window.fetch = function(input, init) {
    if(init && init.body instanceof FormData) {
      const action = init.body.get('action') || '';
      if(action === 'seller_chat_mark_seen' && document.visibilityState === 'hidden') {
        // Defer until tab becomes visible
        return new Promise((resolve, reject) => {
          function onVisible() {
            if(document.visibilityState !== 'hidden') {
              document.removeEventListener('visibilitychange', onVisible);
              _origFetch2(input, init).then(resolve).catch(reject);
            }
          }
          document.addEventListener('visibilitychange', onVisible);
        });
      }
    }
    return _origFetch2(input, init);
  };
})();
` + txt;
        script.textContent = patched;
      }
      document.body.appendChild(script);
      if(!oldScript.src) script.remove();
    });
  }
  function loadChatIntoStage(url, push){
    if(!stage || !url || url === '#') return;
    activeChatUrl = url;
    setActiveLink(url);
    stage.classList.add('has-chat');
    stage.innerHTML = '<div class="stage-loader"><div class="empty-icon"><i class="fa-duotone fa-spinner-third fa-spin"></i></div><div>Loading chat...</div></div>';
    fetch(url, {credentials:'same-origin', cache:'no-store', headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const view = doc.querySelector('.seller-chat-view');
        if(!view) { window.location.href = url; return; }
        let viewStyle = '';
        doc.querySelectorAll('style').forEach(st => { if((st.textContent || '').indexOf('.seller-chat-view') !== -1) viewStyle += st.outerHTML; });
        stage.classList.add('has-chat');
        stage.innerHTML = viewStyle + view.outerHTML;
        executeChatScripts(doc);
        if(push) history.replaceState(null, '', window.location.pathname + window.location.search);
      }).catch(() => { window.location.href = url; });
  }
  list?.addEventListener('click', function(e){
    const link = e.target.closest('.conv');
    if(!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button) return;
    e.preventDefault();
    markConversationRead(link);
    if (window.refreshSidebarChatUnread) {
      setTimeout(window.refreshSidebarChatUnread, 200);
    } else {
      window.dispatchEvent(new CustomEvent('seller-chat-unread-refresh'));
    }
    snapshot = getSnapshot(document);
    loadChatIntoStage(link.getAttribute('href'), true);
    setTimeout(refreshSellerConversationList, 700);
  });
  applyReadStates(document);
  search?.addEventListener('input', apply);
  tabs.forEach(btn => btn.addEventListener('click', function(){ tabs.forEach(x => x.classList.remove('active')); this.classList.add('active'); filter = this.dataset.filter || 'all'; apply(); }));
  setUnreadOnlyEnabled(false);
  unreadOnlyToggle?.addEventListener('click', function(){ setUnreadOnlyEnabled(!unreadOnlyEnabled()); });
  setNotificationsEnabled(notificationsEnabled());
  notifyToggle?.addEventListener('click', function(){ setNotificationsEnabled(!notificationsEnabled()); });

  let realtimeListTimer = null;
  function refreshSellerConversationList(){
    clearTimeout(realtimeListTimer);
    realtimeListTimer = setTimeout(function(){
      fetch('<?= BASE_URL ?>/seller-area/chat', {credentials:'same-origin', cache:'no-store', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.text()).then(html => {
          const doc = new DOMParser().parseFromString(html, 'text/html');
          const nextList = doc.querySelector('#conv-list');
          if(!nextList || !list) return;
          const oldSnapshot = snapshot;
          list.innerHTML = nextList.innerHTML;
          applyReadStates(list);
          if(activeChatUrl) setActiveLink(activeChatUrl);
          apply();
          snapshot = getSnapshot(document);
          Object.keys(snapshot).some(key => {
            const before = oldSnapshot[key];
            const after = snapshot[key];
            if(after && (!before || after.unread > before.unread)) {
              playMessageSound();
              showBrowserNotification(after.name, after.last);
              return true;
            }
            return false;
          });
        }).catch(() => {});
    }, 100);
  }
  function refreshSellerInboxRealtime(){
    refreshSellerConversationList();
    refreshActiveChat();
    if (window.refreshSidebarChatUnread) {
      window.refreshSidebarChatUnread();
    } else {
      window.dispatchEvent(new CustomEvent('seller-chat-unread-refresh'));
    }
  }

  window.addEventListener('lb-chat-update', refreshSellerInboxRealtime);

  if (typeof socket !== 'undefined') {
    socket.on('seller_chat_update', refreshSellerInboxRealtime);
    socket.on('topup_chat_update', refreshSellerInboxRealtime);
    socket.on('seller_topup_chat_update', refreshSellerInboxRealtime);
    socket.on('item_chat_update', refreshSellerInboxRealtime);
    socket.on('account_chat_update', refreshSellerInboxRealtime);
  }
})();
</script>
<?= $this->stop() ?>
