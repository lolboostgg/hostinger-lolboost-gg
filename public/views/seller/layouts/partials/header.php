<?php
$sl_header_seller_id = (int)(defined('SELLER_ID') ? SELLER_ID : (SELLER_DATA['id'] ?? 0));
$sl_chat_unread = ($sl_header_seller_id > 0 && function_exists('util_seller_chat_unread_count'))
    ? util_seller_chat_unread_count($sl_header_seller_id)
    : 0;
?>
<style>
  .lb-seller-navdd .lb-seller-navdd-header{padding-left:1rem;margin-top:.5rem;}
  .lb-seller-navdd-scroll{max-height:min(60vh,480px);overflow-y:auto;}
  .lb-seller-navdd-scroll::-webkit-scrollbar{width:8px;}
  .lb-seller-navdd-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:999px;}
  .lb-seller-navdd .seller-sidebar-chat-badge{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 6px;border-radius:999px;background:#7367f0;color:#fff;font-size:11px;font-weight:800;line-height:18px;box-shadow:0 0 0 2px rgba(115,103,240,.18);}
  .lb-seller-navdd .seller-sidebar-chat-badge.d-none{display:none!important;}
  .lb-notif-btn{position:relative;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#e9ecef;text-decoration:none;}
  .lb-notif-btn:hover{background:rgba(255,255,255,.10);color:#fff;}
  .lb-notif-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;font-size:11px;line-height:18px;text-align:center;background:#8b5cf6;color:#fff;box-shadow:0 6px 16px rgba(0,0,0,.35);display:none;}
  .lb-notif-menu{width:392px;max-width:92vw;background:#2a2d30;border:1px solid rgba(255,255,255,.08);border-radius:16px;box-shadow:0 18px 48px rgba(0,0,0,.55);overflow:hidden;z-index:2000;}
  .lb-notif-head{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.08);}
  .lb-notif-titlebar{font-weight:700;color:#e9ecef;}
  .lb-notif-action{border:0;background:transparent;color:rgba(233,236,239,.75);font-weight:600;font-size:12px;padding:6px 8px;border-radius:10px;}
  .lb-notif-action:hover{background:rgba(255,255,255,.08);color:#fff;}
  .lb-notif-scroll{max-height:min(540px, 70vh);overflow:auto;}
  .lb-notif-scroll::-webkit-scrollbar{width:10px;}
  .lb-notif-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:999px;border:3px solid rgba(0,0,0,0);background-clip:padding-box;}
  .lb-notif-item{display:flex;gap:12px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06);text-decoration:none;color:inherit;}
  .lb-notif-item:last-child{border-bottom:0;}
  .lb-notif-item:hover{background:rgba(255,255,255,.06);}
  .lb-notif-icon{width:34px;height:34px;border-radius:12px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;flex:0 0 34px;color:#e9ecef;}
  .lb-notif-icon.is-account{background:rgba(99,102,241,.14);border-color:rgba(99,102,241,.3);color:#a5b4fc;}
  .lb-notif-icon.is-item{background:rgba(236,72,153,.13);border-color:rgba(236,72,153,.28);color:#f9a8d4;}
  .lb-notif-icon.is-topup{background:rgba(245,158,11,.14);border-color:rgba(245,158,11,.3);color:#fcd34d;}
  .lb-notif-icon.is-digital{background:rgba(14,165,233,.14);border-color:rgba(14,165,233,.3);color:#7dd3fc;}
  .lb-notif-icon.is-payout{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.3);color:#86efac;}
  .lb-notif-icon.is-message{background:rgba(139,92,246,.14);border-color:rgba(139,92,246,.3);color:#c4b5fd;}
  .lb-notif-icon.is-poke{background:rgba(249,115,22,.14);border-color:rgba(249,115,22,.3);color:#fdba74;}
  .lb-notif-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
  .lb-notif-title{font-weight:700;font-size:13px;line-height:1.15;margin:0;color:#f1f5f9;}
  .lb-notif-sub{font-size:12px;color:rgba(233,236,239,.65);margin:2px 0 0 0;}
  /* A sale only needs two numbers; the earned amount is the one that matters. */
  .lb-notif-sub.is-strong{color:#4ade80;font-weight:700;}
  .lb-notif-time{font-size:11px;color:rgba(233,236,239,.45);white-space:nowrap;margin-top:1px;}
  .lb-notif-unread{width:8px;height:8px;border-radius:999px;background:#8b5cf6;display:inline-block;margin-left:10px;box-shadow:0 6px 16px rgba(0,0,0,.35);}
  .lb-notif-right{display:flex;align-items:center;gap:8px;flex:0 0 auto;}
  .lb-notif-markread{border:0;background:rgba(255,255,255,.06);color:rgba(233,236,239,.75);width:26px;height:26px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 26px;}
  .lb-notif-markread:hover{background:rgba(255,255,255,.12);color:#fff;}
  .lb-notif-markread:disabled{opacity:.5;cursor:not-allowed;}
  .lb-notif-empty{padding:18px 14px;color:rgba(233,236,239,.65);text-align:center;}
  .lb-notif-foot{padding:10px 14px;border-top:1px solid rgba(255,255,255,.08);text-align:center;color:rgba(233,236,239,.55);font-size:12px;}
  @media (max-width:575.98px){.lb-notif-menu{position:fixed!important;top:62px!important;left:10px!important;right:10px!important;width:auto!important;max-width:none!important;transform:none!important;}}
</style>

<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white">
    <div class="navbar-nav-wrap">
        <div class="navbar-nav-wrap-content-start">
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="fa-duotone fa-left-from-line navbar-toggler-short-align"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="fa-duotone fa-right-from-line navbar-toggler-full-align"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
            </button>
        </div>

        <div class="navbar-nav-wrap-content-end">
            <ul class="navbar-nav">


                <!-- Notifications -->
                <li class="nav-item me-2">
                    <div class="dropdown">
                        <a class="lb-notif-btn" href="javascript:;" id="lbNotifDropdown"
                           data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <i class="fa-duotone fa-bell"></i>
                            <span class="lb-notif-badge" id="lbNotifBadge">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 lb-notif-menu" aria-labelledby="lbNotifDropdown">
                            <div class="lb-notif-head d-flex align-items-center justify-content-between">
                                <div class="lb-notif-titlebar">Notifications</div>
                                <button class="lb-notif-action" type="button" id="lbNotifMarkAll">Mark all as read</button>
                            </div>
                            <div id="lbNotifList" class="lb-notif-scroll">
                                <div class="lb-notif-empty"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading notifications...</div>
                            </div>
                            <div class="lb-notif-foot">That’s all for now</div>
                        </div>
                    </div>
                </li>

                <!-- Balance chip -->
                <?php
                    $sl_balance = (int)(SELLER_DATA['balance'] ?? 0);
                    $sl_eur = number_format($sl_balance / 100, 2, '.', '') . ' €';
                ?>
                <li class="nav-item me-2">
                    <div class="d-flex align-items-center gap-2 bg-light rounded-2 px-3 py-2"
                         data-bs-toggle="tooltip" data-bs-placement="bottom" title="Your available balance">
                        <i class="fa-duotone fa-sack-dollar"></i>
                        <span class="fw-bold"><?= $sl_eur ?></span>
                    </div>
                </li>

                <!-- Account dropdown -->
                <li class="nav-item">
                    <div class="dropdown">
                        <a class="navbar-dropdown-account-wrapper" href="javascript:;" id="sellerNavbarDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                            data-bs-dropdown-animation>
                            <div class="avatar avatar-sm avatar-circle">
                                <?php if (!empty(SELLER_DATA['icon'])): ?>
                                    <img class="avatar-img" src="<?= htmlspecialchars(SELLER_DATA['icon']) ?>" alt="<?= htmlspecialchars(SELLER_DATA['username']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <span class="avatar-initials avatar-sm-status avatar-status-success"
                                          style="background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;font-weight:700;font-size:.85rem;">
                                        <?= strtoupper(substr(SELLER_DATA['username'], 0, 1)) ?>
                                    </span>
                                <?php endif ?>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account lb-seller-navdd"
                            aria-labelledby="sellerNavbarDropdown" style="width: 18.5rem;">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <?php if (!empty(SELLER_DATA['icon'])): ?>
                                            <img class="avatar-img" src="<?= htmlspecialchars(SELLER_DATA['icon']) ?>" alt="<?= htmlspecialchars(SELLER_DATA['username']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <span style="background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;font-weight:700;font-size:.85rem;">
                                                <?= strtoupper(substr(SELLER_DATA['username'], 0, 1)) ?>
                                            </span>
                                        <?php endif ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3" style="min-width:0;">
                                        <h5 class="mb-0 text-truncate"><?= htmlspecialchars(SELLER_DATA['username']) ?></h5>
                                        <p class="card-text text-body text-truncate mb-0"><?= htmlspecialchars(SELLER_DATA['email']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>

                            <div class="lb-seller-navdd-scroll">
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/dashboard">
                                    <i class="fa-duotone fa-objects-column nav-icon"></i> Dashboard
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/analytics">
                                    <i class="fa-duotone fa-chart-line nav-icon"></i> Analytics
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/seller-area/chat">
                                    <span><i class="fa-duotone fa-comments nav-icon"></i> Chat Inbox</span>
                                    <span class="seller-sidebar-chat-badge ms-auto<?= $sl_chat_unread > 0 ? '' : ' d-none' ?>" id="sellerHeaderChatUnread">
                                        <?= $sl_chat_unread > 99 ? '99+' : (int)$sl_chat_unread ?>
                                    </span>
                                </a>

                                <span class="dropdown-header lb-seller-navdd-header">Accounts</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/accounts">
                                    <i class="fa-duotone fa-database nav-icon"></i> My Accounts
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/import-accounts">
                                    <i class="fa-duotone fa-file-import nav-icon"></i> Import Accounts
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/account-orders">
                                    <i class="fa-duotone fa-cart-shopping nav-icon"></i> Account Orders
                                </a>

                                <span class="dropdown-header lb-seller-navdd-header">Items</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/items">
                                    <i class="fa-duotone fa-gift nav-icon"></i> My Items
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/import-items">
                                    <i class="fa-duotone fa-file-import nav-icon"></i> Import Items
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/item-orders">
                                    <i class="fa-duotone fa-bag-shopping nav-icon"></i> Item Orders
                                </a>

                                <span class="dropdown-header lb-seller-navdd-header">Top Ups</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/top-ups">
                                    <i class="fa-duotone fa-coins nav-icon"></i> My Top Ups
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/import-top-ups">
                                    <i class="fa-duotone fa-file-import nav-icon"></i> Import Top Ups
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/top-up-orders">
                                    <i class="fa-duotone fa-receipt nav-icon"></i> Top Up Orders
                                </a>

                                <?php if (!empty(SELLER_DATA['can_list_digital_goods'])): ?>
                                <span class="dropdown-header lb-seller-navdd-header">Digital Goods</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/digital-goods/listings">
                                    <i class="fa-duotone fa-box-open nav-icon"></i> My Listings
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/digital-goods">
                                    <i class="fa-duotone fa-inbox nav-icon"></i> DG Orders
                                </a>
                                <?php endif; ?>

                                <span class="dropdown-header lb-seller-navdd-header">Wallet</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/payments">
                                    <i class="fa-duotone fa-money-bill-transfer nav-icon"></i> Payments
                                </a>

                                <span class="dropdown-header lb-seller-navdd-header">Seller</span>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/api">
                                    <i class="fa-duotone fa-key nav-icon"></i> Partner API
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/rules">
                                    <i class="fa-duotone fa-book nav-icon"></i> Seller Rules
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/seller-area/profile">
                                    <i class="fa-solid fa-gear nav-icon"></i> Settings
                                </a>
                            </div>

                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/seller-area/auth/logout">
                                <i class="fa-duotone fa-sign-out-alt nav-icon"></i> Sign out
                            </a>
                        </div>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</header>


<script>
(function(){
  const AJAX_URL = "<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>";
  const scope = 'seller';
  const sellerBase = "<?= BASE_URL ?>/seller-area";
  function escapeHtml(s){return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
  function parseData(d){if(!d) return {}; try{return (typeof d === 'string') ? JSON.parse(d) : d;}catch(e){return {};}}
  function maybeDecode(v){if(v===null||v===undefined) return ''; const s=String(v); if(/^[A-Za-z0-9+/=]+$/.test(s)&&s.length%4===0){try{const bin=atob(s); const bytes=Uint8Array.from(bin,c=>c.charCodeAt(0)); const d=new TextDecoder('utf-8',{fatal:true}).decode(bytes); if(d) return d;}catch(e){}} return s;}
  function decodeText(v){return v===null||v===undefined?'':maybeDecode(v).trim();}
  function moneyEur(v){let s=decodeText(v);if(!s)return '';s=s.replace(/EUR|\u20ac/gi,'').trim().replace(',','.');const n=Number(s);return Number.isFinite(n)?n.toFixed(2).replace('.',',')+' \u20ac':'';}
  function detail(parts){return parts.filter(Boolean).join(' · ');}
  // A sale only needs the two numbers a seller actually cares about.
  function saleLines(earnings,balance){return [earnings&&{text:'You earned '+earnings,strong:true}, balance&&{text:'New Balance '+balance}].filter(Boolean);}
  function lbDecodeMaybeBase64Number(v){if(v===null||v===undefined) return null; if(typeof v==='number') return v; let s=String(v).trim(); if(!s) return null; if(!/^-?\d+(?:\.\d+)?$/.test(s)&&/^[A-Za-z0-9+/=]+$/.test(s)&&(s.length%4===0)){try{const dec=atob(s); if(/^-?\d+(?:\.\d+)?$/.test(dec.trim())) s=dec.trim();}catch(e){}} s=s.replace(/EUR|€/gi,'').trim().replace(',','.'); const n=Number(s); return Number.isFinite(n)?n:null;}
  function lbFormatEurFromCents(v){const n=lbDecodeMaybeBase64Number(v); if(n===null) return ''; return (Math.round(n)/100).toFixed(2)+' €';}
  async function post(data){const form=new URLSearchParams(); form.append('scope',scope); Object.entries(data).forEach(([k,val])=>form.append(k,String(val))); const res=await fetch(AJAX_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:form.toString(),credentials:'same-origin',cache:'no-store'}); return await res.json();}
  function setBadge(n){const badge=document.getElementById('lbNotifBadge'); if(!badge) return; const v=parseInt(n||0,10); if(v>0){badge.textContent=v>99?'99+':String(v); badge.style.display='inline-block';}else{badge.textContent='0'; badge.style.display='none';}}
  function humanizeType(t){if(!t) return 'Notification'; return String(t).replace(/[_-]+/g,' ').replace(/\s+/g,' ').trim().replace(/\b\w/g,c=>c.toUpperCase());}
  function urlFromData(data){if(data.action_url) return maybeDecode(data.action_url); if(data.url) return maybeDecode(data.url); if(data.order_id||data.orderId) return sellerBase+'/item-orders'; return '';}
  function mapNotif(row){const type=row.type||'';const data=parseData(row.data);const created=row.created_at||'';const time=created?created.slice(0,16).replace('T',' '):'';const seen=parseInt(row.is_seen||0,10)===1;let title=humanizeType(type)||'Notification';let subtitle='';let lines=null;let icon='fa-solid fa-bell';let tone='';let url=urlFromData(data);const price=moneyEur(data.price||data.sale_price);const earnings=moneyEur(data.earnings||data.seller_cut);const balance=moneyEur(data.balance_after||data.balance);const fee=decodeText(data.fee_percent);const buyer=decodeText(data.buyer||data.client_name||data.client_username);const qty=decodeText(data.quantity);const product=decodeText(data.account_title||data.item_title||data.topup_title||data.title);const game=decodeText(data.game);const region=decodeText(data.region);
    if(type==='welcome_seller'){title='Seller account approved';subtitle='Your seller account is ready to use';icon='fa-solid fa-store';tone='is-account';}
    else if(type==='seller_payout_request'||type==='seller_payout_paid'||type==='seller_payout_rejected'){title=type==='seller_payout_paid'?'Payout paid':(type==='seller_payout_rejected'?'Payout rejected':'Payout requested');const eur=moneyEur(data.amount||data.total)||lbFormatEurFromCents(data.amount_cents||data.total_cents);subtitle=detail([eur&&('Amount '+eur),balance&&('Balance '+balance)])||'Seller payout status changed';icon=type==='seller_payout_rejected'?'fa-solid fa-circle-xmark':'fa-solid fa-money-bill-transfer';tone='is-payout';if(!url)url=sellerBase+'/payout';}
    else if(type==='seller_account_sold'||type==='account_order_paid'){title='Sold: Account'+(product?' - '+product:'');lines=saleLines(earnings,balance);icon='fa-solid fa-shield-halved';tone='is-account';if(!url)url=sellerBase+'/account-orders';}
    else if(type==='seller_item_sold'||type==='item_sold'||type==='item_order_paid'){title='Sold: Item'+(product?' - '+product:'');lines=saleLines(earnings,balance);icon='fa-solid fa-gift';tone='is-item';if(!url)url=sellerBase+'/item-orders';}
    else if(type==='topup_sold'){title='Sold: Top Up'+(product?' - '+product:'');lines=saleLines(earnings,balance);icon='fa-solid fa-bolt';tone='is-topup';if(!url)url=sellerBase+'/top-up-orders';}
    else if(type==='digital_good_sold'){title='Sold: DG'+(product?' - '+product:'');lines=saleLines(earnings,balance);icon='fa-solid fa-box-open-full';tone='is-digital';if(!url)url=sellerBase+'/digital-goods';}
    else if(type==='seller_chat_message'||type==='seller_new_message'){title='New customer message';const client=decodeText(data.client_name||data.client_username||data.customer);subtitle=client?(client+' sent you a message'):'You received a new customer message';icon='fa-solid fa-message-dots';tone='is-message';if(!url)url=sellerBase+'/chat';}
    else if(type==='seller_unread_message'){title='Unread customer message';const client=decodeText(data.client_name||data.client_username||data.customer);subtitle=client?(client+' has been waiting for your reply for more than 5 minutes'):'A customer message has been unread for more than 5 minutes';icon='fa-solid fa-comment-exclamation';tone='is-message';if(!url)url=maybeDecode(data.chat_url)||sellerBase+'/chat';}
    else if(type==='poke_seller'){title='Customer reminder';const client=decodeText(data.client_username||data.sender_name);const item=decodeText(data.title);subtitle=detail([client&&(client+' poked you'),item])||'A customer is waiting for your response';icon='fa-solid fa-hand-point-up';tone='is-poke';if(!url)url=sellerBase+'/chat';}
    else if(type==='invoice_paid'||type==='invoice_payment_succeeded'){title='Payment received';const eur=moneyEur(data.amount||data.total)||lbFormatEurFromCents(data.amount_cents||data.total_cents||data.paid_cents);subtitle='Invoice paid'+(eur?' · '+eur:'');icon='fa-solid fa-receipt';tone='is-payout';}
    else{subtitle=data.message?decodeText(data.message):(data.title?decodeText(data.title):humanizeType(type));}
    return{id:row.id,title,subtitle:subtitle||humanizeType(type),lines,icon,tone,url,time,seen};}
  function render(rows){const list=document.getElementById('lbNotifList');if(!list)return;if(!rows||!rows.length){list.innerHTML='<div class="lb-notif-empty">No notifications yet.</div>';return;}const items=rows.map(mapNotif);list.innerHTML=items.map(n=>{const href=n.url?n.url:'javascript:;';const target=n.url?'':' tabindex="-1"';return `<a class="lb-notif-item" data-id="${n.id}" href="${escapeHtml(href)}" ${target}><div class="lb-notif-icon ${escapeHtml(n.tone)}"><i class="${escapeHtml(n.icon)}"></i></div><div class="flex-grow-1" style="min-width:0;"><div class="lb-notif-row"><p class="lb-notif-title">${escapeHtml(n.title)}</p><div class="lb-notif-right"><div class="lb-notif-time">${escapeHtml(n.time)}</div>${!n.seen?'<span class="lb-notif-unread"></span>':''}${!n.seen?'<button class="lb-notif-markread" type="button" data-id="'+n.id+'" title="Mark as read"><i class="fa-solid fa-check"></i></button>':''}</div></div>${(n.lines&&n.lines.length)?n.lines.map(l=>`<p class="lb-notif-sub${l.strong?' is-strong':''}">${escapeHtml(l.text)}</p>`).join(''):`<p class="lb-notif-sub">${escapeHtml(n.subtitle)}</p>`}</div></a>`;}).join('');}
  function markNotifItemRead(item){if(!item)return;item.querySelectorAll('.lb-notif-unread,.lb-notif-markread').forEach(el=>el.remove());}
  const listEl=document.getElementById('lbNotifList'); if(listEl){listEl.addEventListener('click',async(e)=>{const btn=e.target.closest('.lb-notif-markread'); if(!btn) return; e.preventDefault(); e.stopPropagation(); const id=parseInt(btn.getAttribute('data-id')||'0',10); if(!id) return; btn.disabled=true; try{const r=await post({action:'notifications_mark_read',id}); if(r&&r.success){markNotifItemRead(btn.closest('.lb-notif-item')); const b=document.getElementById('lbNotifBadge'); const cur=b?parseInt(b.textContent||'0',10):0; if(cur>0) setBadgeCached(cur-1);}else btn.disabled=false;}catch(err){btn.disabled=false;}}); listEl.addEventListener('click',(e)=>{const a=e.target.closest('a.lb-notif-item'); if(!a||!a.querySelector('.lb-notif-unread')) return; const id=parseInt(a.getAttribute('data-id')||'0',10); if(!id) return; post({action:'notifications_mark_read',id}).then(r=>{if(r&&r.success){markNotifItemRead(a); const b=document.getElementById('lbNotifBadge'); const cur=b?parseInt(b.textContent||'0',10):0; if(cur>0) setBadgeCached(cur-1);}}).catch(()=>{});});}
  const notifCacheKey='lb_seller_notif_count_v1';
  const notifCacheMaxAge=60000;
  let badgeRequestRunning=false;
  function saveBadgeCache(n){try{sessionStorage.setItem(notifCacheKey,JSON.stringify({count:parseInt(n||0,10)||0,ts:Date.now()}));}catch(e){}}
  function loadBadgeCache(){try{const raw=sessionStorage.getItem(notifCacheKey);if(!raw)return null;const data=JSON.parse(raw);if(!data||typeof data.count==='undefined'||!data.ts)return null;return data;}catch(e){return null;}}
  function setBadgeCached(n){setBadge(n);saveBadgeCache(n);}
  async function refreshBadge(force){
    if(badgeRequestRunning) return;
    if(!force){
      if(document.visibilityState!=='visible') return;
    }
    badgeRequestRunning=true;
    try{const r=await post({action:'notifications_unread_count'});if(r&&r.success)setBadgeCached(r.unread||0);}finally{badgeRequestRunning=false;}
  }
  async function refreshList(){const r=await post({action:'notifications_list',limit:25,since_id:0}); if(r&&r.success){setBadgeCached(r.unread||0); render(r.items||[]);}}
  window.lbRefreshSellerNotificationBadge=function(){return refreshBadge(true).catch(()=>{});};
  const cachedBadge=loadBadgeCache();
  if(cachedBadge)setBadge(cachedBadge.count);
  refreshBadge(true).catch(()=>{});
  setInterval(()=>refreshBadge(false).catch(()=>{}),60000);
  document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')refreshBadge(true).catch(()=>{});});
  const dd=document.getElementById('lbNotifDropdown'); if(dd) dd.addEventListener('show.bs.dropdown',()=>refreshList().catch(()=>{})); const markAll=document.getElementById('lbNotifMarkAll'); if(markAll){markAll.addEventListener('click',async(e)=>{e.preventDefault(); markAll.disabled=true; try{const r=await post({action:'notifications_mark_all_read'}); if(r&&r.success){setBadgeCached(0); await refreshList();}}finally{markAll.disabled=false;}});}
})();
</script>
