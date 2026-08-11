<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">

<?php
$lbDailyGiftReadyHeader = false;
$lbDailyGiftNextHeader = '';
try {
    if (defined('CLIENT_ID') && (int)CLIENT_ID > 0 && function_exists('db_get_row')) {
        $lbDailyBoxHeader = db_get_row('reward_boxes', [
            'is_daily' => 1,
            'status' => 1,
            'select' => 'id,slug,name,cooldown_hours'
        ], 1);

        if ($lbDailyBoxHeader && !empty($lbDailyBoxHeader['id'])) {
            $lbCooldownHeader = max(1, (int)($lbDailyBoxHeader['cooldown_hours'] ?? 24));
            $lbLastOpeningHeader = null;

            try {
                $lbLastOpeningHeader = db_get_row('reward_openings', [
                    'client_id' => (int)CLIENT_ID,
                    'box_id' => (int)$lbDailyBoxHeader['id'],
                    'order_by' => 'id DESC',
                    'select' => 'created_at'
                ], 1);
            } catch (Throwable $e) {
                $lbLastOpeningHeader = null;
            }

            if (!empty($lbLastOpeningHeader['created_at']) && strtotime((string)$lbLastOpeningHeader['created_at']) !== false) {
                $lbNextTsHeader = strtotime((string)$lbLastOpeningHeader['created_at']) + ($lbCooldownHeader * 3600);
                if ($lbNextTsHeader <= time()) {
                    $lbDailyGiftReadyHeader = true;
                } else {
                    $lbDailyGiftNextHeader = date('Y-m-d H:i:s', $lbNextTsHeader);
                }
            } else {
                $lbDailyGiftReadyHeader = true;
            }
        }
    }
} catch (Throwable $e) {
    $lbDailyGiftReadyHeader = false;
}

$lbClientCoinsHeader = 0;
$lbClientRewardPointsHeader = 0;
try {
    if (defined('CLIENT_ID') && (int)CLIENT_ID > 0 && function_exists('db_get_row')) {
        $lbClientBalanceHeader = db_get_row('clients', [
            'id' => (int)CLIENT_ID,
            'select' => 'points,reward_points'
        ], 1);
        if (is_array($lbClientBalanceHeader)) {
            $lbClientCoinsHeader = (float)($lbClientBalanceHeader['points'] ?? 0);
            $lbClientRewardPointsHeader = (float)($lbClientBalanceHeader['reward_points'] ?? 0);
        }
    } elseif (defined('CLIENT_DATA') && is_array(CLIENT_DATA)) {
        $lbClientCoinsHeader = (float)(CLIENT_DATA['points'] ?? 0);
        $lbClientRewardPointsHeader = (float)(CLIENT_DATA['reward_points'] ?? 0);
    }
} catch (Throwable $e) {
    if (defined('CLIENT_DATA') && is_array(CLIENT_DATA)) {
        $lbClientCoinsHeader = (float)(CLIENT_DATA['points'] ?? 0);
        $lbClientRewardPointsHeader = (float)(CLIENT_DATA['reward_points'] ?? 0);
    }
}
$lbHeaderFormatAmount = function($value): string {
    $value = (float)$value;
    $formatted = number_format($value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
};
?>

<style>
  .dropdown-divider {
    margin: 0.5rem 0;
    position: relative;
    text-align: center;
  }
  .dropdown-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    border-top: 1px solid #e9ecef;
    transform: translateY(-50%);
  }
  .dropdown-divider::after {
    content: 'or';
    position: relative;
    display: inline-block;
    padding: 0 0.5rem;
    background-color: #fff;
    color: #6c757d;
  }
  .dropdown-item{ display:flex; align-items:center; }
  .dropdown-item i{ margin-right:.5rem; }

  .navbar-nav-wrap-content-start{ gap:.5rem; }

  /* Notifications (dark dropdown like GameBoost) */
  .lb-notif-btn{position:relative;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#e9ecef;}
  .lb-notif-btn:hover{background:rgba(255,255,255,.10);}
  .lb-notif-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;font-size:11px;line-height:18px;text-align:center;background:#8b5cf6;color:#fff;box-shadow:0 6px 16px rgba(0,0,0,.35);display:none;}
  .lb-notif-menu{width:392px;max-width:92vw;background:#2a2d30;border:1px solid rgba(255,255,255,.08);border-radius:16px;box-shadow:0 18px 48px rgba(0,0,0,.55);overflow:hidden;;z-index:2000}
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
  .lb-notif-icon.daily-gift{background:linear-gradient(135deg,rgba(124,92,255,.25),rgba(143,178,255,.13));border-color:rgba(143,178,255,.28);color:#c9d8ff;}
  .lb-notif-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
  .lb-notif-title{font-weight:700;font-size:13px;line-height:1.15;margin:0;color:#f1f5f9;}
  .lb-notif-sub{font-size:12px;color:rgba(233,236,239,.65);margin:2px 0 0 0;}
  .lb-notif-cta{display:inline-flex;align-items:center;margin-top:6px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#fff;background:linear-gradient(135deg,rgba(124,92,255,.80),rgba(255,90,200,.70));border:1px solid rgba(255,255,255,.15);}
  .lb-notif-time{font-size:11px;color:rgba(233,236,239,.45);white-space:nowrap;margin-top:1px;}
    .lb-notif-unread{width:8px;height:8px;border-radius:999px;background:#8b5cf6;display:inline-block;margin-left:10px;box-shadow:0 6px 16px rgba(0,0,0,.35);}
    .lb-notif-right{display:flex;align-items:center;gap:8px;flex:0 0 auto;}
    .lb-notif-markread{border:0;background:rgba(255,255,255,.06);color:rgba(233,236,239,.75);width:26px;height:26px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 26px;}
    .lb-notif-markread:hover{background:rgba(255,255,255,.12);color:#fff;}
    .lb-notif-markread:disabled{opacity:.5;cursor:not-allowed;}
  .lb-notif-empty{padding:18px 14px;color:rgba(233,236,239,.65);text-align:center;}
  .lb-notif-foot{padding:10px 14px;border-top:1px solid rgba(255,255,255,.08);text-align:center;color:rgba(233,236,239,.55);font-size:12px;}

  .lb-header-balances{display:flex;align-items:center;gap:8px;margin-right:8px;}
  .lb-header-balance-pill{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 11px;border-radius:13px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#f8fafc;text-decoration:none;box-shadow:0 10px 24px rgba(0,0,0,.12);}
  .lb-header-balance-pill:hover{background:rgba(255,255,255,.10);color:#fff;text-decoration:none;border-color:rgba(139,92,246,.24);}
  .lb-header-balance-icon{width:24px;height:24px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:rgba(124,92,255,.16);border:1px solid rgba(124,92,255,.24);flex:0 0 24px;}
  .lb-header-balance-icon img{width:18px;height:18px;object-fit:contain;display:block;}
  .lb-header-balance-text{display:flex;flex-direction:column;line-height:1;}
  .lb-header-balance-value{font-size:13px;font-weight:950;color:#fff;white-space:nowrap;}
  .lb-header-balance-label{margin-top:3px;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.48);white-space:nowrap;}
  .lb-notif-markread.is-daily-dismiss{background:rgba(255,255,255,.08);}
  .lb-notif-markread.is-daily-dismiss:hover{background:rgba(239,68,68,.16);color:#fecaca;}
/* Mobile: keep notifications dropdown inside viewport */
@media (max-width: 575.98px) {
    .lb-notif-menu{
        position: fixed !important;
        top: 62px !important;
        left: 10px !important;
        right: 10px !important;
        width: auto !important;
        max-width: none !important;
        transform: none !important;
    }
}
</style>

<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white">
  <div class="navbar-nav-wrap">

    <!-- LEFT: Logo -> dann Collapse Button (wie du willst) -->
    <div class="navbar-nav-wrap-content-start d-flex align-items-center">

      <!-- Logo -->
      <a class="navbar-brand m-0" href="<?= BASE_URL ?>" aria-label="LoLBoost.gg">
        <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png?v6" alt="Logo" data-hs-theme-appearance="light">
        <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png?v6"  alt="Logo" data-hs-theme-appearance="dark">
        <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png?v6"  alt="Logo" data-hs-theme-appearance="default">
      </a>
      <!-- End Logo -->

      <!-- Navbar Vertical Toggle (NACH dem Logo) -->
      <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
        <i class="fa-duotone fa-left-from-line navbar-toggler-short-align"
           data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
           data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
        <i class="fa-duotone fa-right-from-line navbar-toggler-full-align"
           data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
           data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
      </button>
      <!-- End Navbar Vertical Toggle -->

    </div>

    <!-- RIGHT -->
    <div class="navbar-nav-wrap-content-end">
      <ul class="navbar-nav">

        <!-- Desktop Balances -->
        <li class="nav-item d-none d-lg-flex align-items-center">
          <div class="lb-header-balances">
            <a class="lb-header-balance-pill" href="<?= BASE_URL ?>/points-store" title="LB Coins">
              <span class="lb-header-balance-icon"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/coin_purple.png" alt="LB Coins"></span>
              <span class="lb-header-balance-text">
                <span class="lb-header-balance-value"><?= htmlspecialchars($lbHeaderFormatAmount($lbClientCoinsHeader), ENT_QUOTES) ?></span>
                <span class="lb-header-balance-label">LB Coins</span>
              </span>
            </a>
            <a class="lb-header-balance-pill" href="<?= BASE_URL ?>/profile/rewards" title="Reward Points">
              <span class="lb-header-balance-icon"><img src="<?= BASE_URL ?>/public/assets/website/images/coins/reward-points.png" alt="Reward Points"></span>
              <span class="lb-header-balance-text">
                <span class="lb-header-balance-value"><?= htmlspecialchars($lbHeaderFormatAmount($lbClientRewardPointsHeader), ENT_QUOTES) ?></span>
                <span class="lb-header-balance-label">Rewards</span>
              </span>
            </a>
          </div>
        </li>

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
              <div id="lbNotifList" class="lb-notif-scroll"></div>
              <div class="lb-notif-foot">That’s all for now</div>
            </div>
          </div>
        </li>

        <li class="nav-item">
          <div class="dropdown">
            <a class="navbar-dropdown-account-wrapper" href="javascript:;" id="accountNavbarDropdown"
               data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside" data-bs-dropdown-animation>
              <div class="avatar avatar-sm avatar-circle">
                <img class="avatar-img" src="<?= CLIENT_DATA['icon'] ?>" alt="<?= CLIENT_DATA['username'] ?>">
                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
              </div>
            </a>

            <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account"
                 aria-labelledby="accountNavbarDropdown" style="width: 17rem;">

              <div class="dropdown-item-text">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm avatar-circle">
                    <img class="avatar-img" src="<?= CLIENT_DATA['icon'] ?>" alt="<?= CLIENT_DATA['username'] ?>">
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="mb-0"><?= CLIENT_DATA['username'] ?></h5>
                    <p class="card-text text-body text-truncate"><?= CLIENT_DATA['email'] ?></p>
                  </div>
                </div>
              </div>

              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="<?= BASE_URL ?>/profile/settings">
                <i class="fa-duotone fa-cog nav-icon"></i> My Profile
              </a>
              <a class="dropdown-item" href="<?= BASE_URL ?>/profile/orders">
                <i class="fa-duotone fa-rocket-launch nav-icon"></i> My Orders
              </a>
              <a class="dropdown-item" href="<?= BASE_URL ?>/profile/billing">
                <i class="fa-duotone fa-wallet nav-icon"></i> My Payments
              </a>

              <!-- FIX: korrekt -->
              <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
                <i class="fad fa-camera nav-icon"></i> Change Picture
              </button>

              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="<?= BASE_URL ?>/logout">
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
  const AJAX_URL = "<?= AJAX_URL ?>";
  // This is the client-area header — scope is always 'client' regardless of
  // whether BOOSTER_DATA is also defined (users who can "Switch to Booster").
  const scope = 'client';
  const storageKey = 'lb_last_seen_notif_client';
  const orderBase = "<?= BASE_URL ?>";
  const lbDailyGiftReady = <?= !empty($lbDailyGiftReadyHeader) ? 'true' : 'false' ?>;
  const lbDailyGiftUrl = "<?= BASE_URL ?>/profile/rewards/daily-gift";
  const lbClientId = "<?= defined('CLIENT_ID') ? (int)CLIENT_ID : 0 ?>";
  const lbDailyGiftDismissKey = 'lb_daily_gift_dismissed_' + lbClientId + '_' + new Date().toISOString().slice(0,10);

  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  function isDailyGiftDismissed(){
    try { return localStorage.getItem(lbDailyGiftDismissKey) === '1'; } catch(e) { return false; }
  }

  function dailyGiftActive(){
    return !!lbDailyGiftReady && !isDailyGiftDismissed();
  }

  function calcUnreadWithDaily(unread){
    return (parseInt(unread||0,10) || 0) + (dailyGiftActive() ? 1 : 0);
  }

  function parseData(d){
    if(!d) return {};
    try{ return (typeof d === 'string') ? JSON.parse(d) : d; }catch(e){ return {}; }
  }

  function decodeB64(v){
    const s = String(v||'').trim();
    if(!s) return '';
    if(/^\d+$/.test(s)) return s; // plain numeric ids are never base64 encoded
    // Some notifications store plain values (e.g. egirl order ids). atob() on those
    // returns binary garbage, which produced links like /order/%C3%97%C2%8D.
    if(!/^[A-Za-z0-9+/]+={0,2}$/.test(s) || (s.length % 4) !== 0) return s;
    try {
      const bin = atob(s);
      // Reject anything that decoded into control/binary characters.
      if(/[\x00-\x08\x0b\x0c\x0e-\x1f]/.test(bin)) return s;
      // Decode as UTF-8 (not raw Latin-1 bytes) so multi-byte chars like emoji
      // don't turn into mojibake ("â€¢").
      const bytes = Uint8Array.from(bin, c => c.charCodeAt(0));
      return new TextDecoder('utf-8', {fatal:true}).decode(bytes);
    } catch(e){ return s; }
  }

function lbDecodeMaybeBase64Number(v){
  if(v === null || v === undefined) return null;
  if(typeof v === 'number') return v;
  let s = String(v).trim();
  if(!s) return null;

  // if plain number -> ok, else try base64 decode when it looks like base64
  if(!/^-?\d+(?:\.\d+)?$/.test(s) && /^[A-Za-z0-9+/=]+$/.test(s) && (s.length % 4 === 0)){
    try{
      const dec = atob(s);
      if(/^-?\d+(?:\.\d+)?$/.test(dec.trim())) s = dec.trim();
    }catch(e){}
  }

  s = s.replace(/EUR|€/gi,'').trim().replace(',','.');
  const n = Number(s);
  return Number.isFinite(n) ? n : null;
}

function lbFormatEurFromCents(v){
  const n = lbDecodeMaybeBase64Number(v);
  if(n === null) return '';
  // Stored as cents in DB/notifications
  const eur = Math.round(n) / 100;
  return eur.toFixed(2) + ' €';
}

async function post(data){
    const form = new URLSearchParams();
    form.append('scope', scope);
    Object.entries(data).forEach(([k,val])=>form.append(k, String(val)));
    const res = await fetch(AJAX_URL, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: form.toString()
    });
    return await res.json();
  }

  function setBadge(n){
    const badge = document.getElementById('lbNotifBadge');
    if(!badge) return;
    const v = parseInt(n||0,10);
    if(v > 0){
      badge.textContent = v > 99 ? '99+' : String(v);
      badge.style.display = 'inline-block';
    } else {
      badge.textContent = '0';
      badge.style.display = 'none';
    }
  }

  function humanizeType(t){
    if(!t) return '';
    return String(t)
      .replace(/[_-]+/g,' ')
      .replace(/\s+/g,' ')
      .trim()
      .replace(/\b\w/g, c => c.toUpperCase());
  }

  function mapNotif(row){
    const type = row.type || '';
    const data = parseData(row.data);
    const created = row.created_at || '';
    // DB stores UTC — parse as UTC and convert to local browser time
    let time = '';
    if(created){
      // "2026-05-15 04:42:00" → append Z or replace space with T to mark as UTC
      const utcStr = created.trim().replace(' ', 'T').replace(/(\.\d+)?$/, 'Z');
      try {
        const d = new Date(utcStr);
        if(!isNaN(d)){
          const pad = n => String(n).padStart(2,'0');
          time = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
               + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }
      } catch(e) {
        time = created.slice(0,16).replace('T',' ');
      }
    }
    const seen = parseInt(row.is_seen||0,10) === 1;
    const isClient  = (scope === 'client');
    const isBooster = (scope === 'booster');
    const isSeller  = (scope === 'seller');

    let title    = 'Notification';
    let subtitle = humanizeType(type);
    let icon     = 'fa-solid fa-bell';
    let url      = '';
    let cta      = '';   // optional call-to-action label shown below subtitle

    // order url
    const encodedOrderId = data.order_id || data.orderId || null;
    if(encodedOrderId){
      const oid = decodeB64(encodedOrderId);
      if(oid) url = orderBase + '/order/' + oid;
    }

    const oidNum = encodedOrderId ? (parseInt(decodeB64(encodedOrderId), 10) || 0) : 0;
    const oidStr = oidNum > 0 ? (' #' + oidNum) : '';

    const decodeName = v => v ? (decodeB64(String(v)).trim() || null) : null;
    const boosterName = decodeName(data.booster_username || data.booster || null);
    const clientName  = decodeName(data.client_username  || data.client  || data.customer || null);

    if(type === 'ds_msg_notif_client'){
      title    = 'New message';
      subtitle = boosterName ? (boosterName + ' sent you a message') : 'Your booster sent you a message';
      icon     = 'fa-solid fa-comment-dots';
    } else if(type === 'ds_msg_notif_booster'){
      title    = 'New message';
      subtitle = clientName ? (clientName + ' sent you a message') : 'A customer sent you a message';
      icon     = 'fa-solid fa-comment-dots';
    } else if(type === 'poke_client'){
      title    = 'You were poked';
      subtitle = (boosterName ? boosterName : 'Your booster') + ' poked you';
      icon     = 'fa-solid fa-hand-point-up';
    } else if(type === 'poke_booster'){
      title    = 'You were poked';
      subtitle = (clientName ? clientName : 'A customer') + ' poked you';
      icon     = 'fa-solid fa-hand-point-up';
    } else if(type === 'booster_money_added'){
      title    = 'Balance updated';
      const eur = lbFormatEurFromCents(data.amount);
      subtitle = 'Money added' + (eur ? ' (+' + eur + ')' : '');
      icon     = 'fa-solid fa-wallet';
    } else if(type === 'booster_money_fined'){
      title    = 'Balance updated';
      const eur = lbFormatEurFromCents(data.amount || data.money_fined || data.money_removed);
      subtitle = 'Money removed' + (eur ? ' (-' + eur + ')' : '');
      icon     = 'fa-solid fa-wallet';
    } else if(type === 'booster_balance_withdrawn'){
      title    = 'Balance updated';
      const eur = lbFormatEurFromCents(data.amount || data.withdrawn);
      subtitle = 'Withdrawal processed' + (eur ? ' (-' + eur + ')' : '');
      icon     = 'fa-solid fa-wallet';
    } else if(type === 'order_claimed'){
      title    = 'Order started';
      icon     = 'fa-solid fa-play';
      if(isClient){
        subtitle = boosterName
          ? (boosterName + ' claimed your order' + oidStr)
          : ('Your order' + oidStr + ' has been claimed');
      } else {
        subtitle = 'You claimed order' + oidStr;
      }
    } else if(type === 'order_paused'){
      title    = 'Order paused';
      subtitle = isClient
        ? ('Your order' + oidStr + ' has been paused')
        : ('Order' + oidStr + ' has been paused');
      icon     = 'fa-solid fa-pause';
    } else if(type === 'order_completed'){
      title = 'Order completed! 🎉';
      icon  = 'fa-solid fa-trophy';
      if(isBooster && (data.payout_cents || data.payout || data.amount)){
        const eur = lbFormatEurFromCents(data.payout_cents || data.payout || data.amount);
        subtitle = 'You earned' + (eur ? ' (+' + eur + ')' : '') + (oidStr ? ' · Order' + oidStr : '');
      } else if(isClient){
        subtitle = 'Your order' + oidStr + ' has been completed';
        cta      = '⭐ Leave a review';
        // url already points to /order/ID — review section is on that page
      } else {
        subtitle = 'Order' + oidStr + ' has been completed';
      }
    } else if(type === 'order_refunded'){
      title    = 'Order refunded';
      subtitle = 'Your order' + oidStr + ' has been refunded';
      icon     = 'fa-solid fa-rotate-left';
    } else if(type === 'order_ping'){
      title    = 'Order update';
      subtitle = 'You have a new update on order' + oidStr;
      icon     = 'fa-solid fa-bell';
    } else if(type === 'invoice_paid' || type === 'invoice_payment_succeeded'){
      title    = 'Payment received';
      const eur = lbFormatEurFromCents(data.amount || data.amount_cents || data.total || data.total_cents || data.paid_cents || data.paid_amount || null);
      subtitle = 'Invoice paid' + (eur ? ' (' + eur + ')' : '');
      icon     = 'fa-solid fa-receipt';
      if(!url) url = orderBase + '/profile/billing';
    } else if(type === 'client_custom_invoice'){
      title    = 'New invoice';
      subtitle = 'You have a new invoice to pay';
      icon     = 'fa-solid fa-file-invoice';
      if(!url) url = orderBase + '/profile/billing';
    } else if(type === 'booster_assigned'){
      title    = 'Booster assigned';
      icon     = 'fa-solid fa-user-check';
      if(isClient){
        subtitle = boosterName
          ? (boosterName + ' has been assigned to your order' + oidStr)
          : ('A booster has been assigned to your order' + oidStr);
      } else {
        subtitle = clientName
          ? ('You were assigned to an order from ' + clientName + oidStr)
          : ('You were assigned to order' + oidStr);
      }
    } else if(type === 'booster_removed'){
      title    = 'Boost update';
      icon     = 'fa-solid fa-user-minus';
      subtitle = isClient
        ? ('Your booster has been removed from order' + oidStr + '. A new one will be assigned soon.')
        : ('You were removed from order' + oidStr);
    } else if(type === 'booster_request' || type === 'booster_ready_request'){
      title    = 'Boost request';
      subtitle = clientName
        ? (clientName + ' requested you for order' + oidStr)
        : ('You received a boost request' + oidStr);
      icon     = 'fa-solid fa-bolt';
    } else if(type === 'booster_request_declined'){
      title    = 'Boost update';
      subtitle = isClient
        ? ('Your requested booster declined order' + oidStr)
        : ('You declined a boost request' + oidStr);
      icon     = 'fa-solid fa-user-xmark';
    } else if(type === 'egirl_session_started'){
      title    = 'Session started';
      subtitle = 'Your session' + oidStr + ' has started';
      icon     = 'fa-solid fa-circle-play';
      if(oidNum > 0) url = orderBase + '/egirl-order/' + oidNum;
    } else if(type === 'egirl_session_completed'){
      title    = 'Session completed! 🎉';
      subtitle = 'Your session' + oidStr + ' has been completed';
      icon     = 'fa-solid fa-circle-check';
      cta      = '⭐ Leave a review';
      // E-Girl sessions live in their own table, not under /order/:id
      if(oidNum > 0) url = orderBase + '/egirl-order/' + oidNum;
    } else if(type === 'welcome_seller'){
      title    = 'Seller account ready';
      subtitle = 'Your seller account has been approved';
      icon     = 'fa-solid fa-store';
    } else if(type === 'seller_payout_request'){
      title    = 'Payout requested';
      subtitle = 'Your payout request was created';
      icon     = 'fa-solid fa-money-bill-transfer';
    } else if(type === 'seller_payout_paid'){
      title    = 'Payout paid';
      subtitle = 'Your seller payout has been paid';
      icon     = 'fa-solid fa-circle-check';
    } else if(type === 'seller_payout_rejected'){
      title    = 'Payout rejected';
      subtitle = 'Your payout request was rejected';
      icon     = 'fa-solid fa-circle-xmark';
    } else if(type === 'seller_account_sold' || type === 'account_order_paid'){
      title    = 'Account sold';
      subtitle = 'You received a new account sale';
      icon     = 'fa-solid fa-cart-shopping';
    } else if(type === 'seller_item_sold' || type === 'item_order_paid'){
      title    = 'Item sold';
      subtitle = 'You received a new item sale';
      icon     = 'fa-solid fa-bag-shopping';
    } else if(type === 'seller_chat_message' || type === 'seller_new_message'){
      title    = 'New seller message';
      subtitle = 'You received a new customer message';
      icon     = 'fa-solid fa-messages';
    }

    return {id: row.id, title, subtitle, cta, icon, url, time, seen};
  }

  function render(rows){
    const list = document.getElementById('lbNotifList');
    if(!list) return;
    rows = Array.isArray(rows) ? rows.slice() : [];
    if(dailyGiftActive()){
      rows.unshift({
        id: 'daily-gift',
        __dailyGift: true,
        title: 'Daily Gift available',
        subtitle: 'Your free lootbox is ready to open.',
        cta: '🎁 Open Daily Gift',
        icon: 'fa-duotone fa-gift',
        url: lbDailyGiftUrl,
        time: 'Now',
        seen: false
      });
    }
    if(!rows.length){
      list.innerHTML = '<div class="lb-notif-empty">No notifications yet.</div>';
      return;
    }
    const items = rows.map(function(row){ return row.__dailyGift ? row : mapNotif(row); });
    list.innerHTML = items.map(n=>{
      const href = n.url ? n.url : 'javascript:;';
      const target = n.url ? '' : ' tabindex="-1"';
      const ctaHtml = n.cta ? `<span class="lb-notif-cta">${escapeHtml(n.cta)}</span>` : '';
      return `
        <a class="lb-notif-item" data-id="${n.id}" href="${href}" ${target}>
          <div class="lb-notif-icon ${n.id === 'daily-gift' ? 'daily-gift' : ''}"><i class="${escapeHtml(n.icon)}"></i></div>
          <div class="flex-grow-1" style="min-width:0;">
            <div class="lb-notif-row">
              <p class="lb-notif-title text-truncate">${escapeHtml(n.title)}</p>
              <div class="lb-notif-right"><div class="lb-notif-time">${escapeHtml(n.time)}</div>${!n.seen ? '<span class="lb-notif-unread"></span>' : ''}${n.__dailyGift ? '<button class="lb-notif-markread is-daily-dismiss" type="button" data-daily-dismiss="1" title="Dismiss"><i class="fa-solid fa-xmark"></i></button>' : (!n.seen ? '<button class="lb-notif-markread" type="button" data-id="'+n.id+'" title="Mark as read"><i class="fa-solid fa-check"></i></button>' : '')}</div>
            </div>
            <p class="lb-notif-sub">${escapeHtml(n.subtitle)}</p>
            ${ctaHtml}
          </div>
        </a>`;
    }).join('');
  }


  // per-notification "mark as read"
  const listEl = document.getElementById('lbNotifList');
  if(listEl){
    // click on check button
    listEl.addEventListener('click', async (e)=>{
      const btn = e.target.closest('.lb-notif-markread');
      if(!btn) return;
      e.preventDefault();
      e.stopPropagation();
      if(btn.getAttribute('data-daily-dismiss') === '1'){
        try { localStorage.setItem(lbDailyGiftDismissKey, '1'); } catch(err) {}
        const item = btn.closest('.lb-notif-item');
        if(item) item.remove();
        const b = document.getElementById('lbNotifBadge');
        const cur = b ? parseInt(b.textContent||'0',10) : 0;
        if(cur > 0) setBadge(cur - 1);
        if(listEl && !listEl.querySelector('.lb-notif-item')) listEl.innerHTML = '<div class="lb-notif-empty">No notifications yet.</div>';
        return;
      }
      const id = parseInt(btn.getAttribute('data-id')||'0', 10);
      if(!id) return;
      btn.disabled = true;
      try{
        const r = await post({action:'notifications_mark_read', id});
        if(r && r.success){
          const item = btn.closest('.lb-notif-item');
          if(item){
            item.querySelectorAll('.lb-notif-unread, .lb-notif-markread').forEach(el=>el.remove());
          }
          const b = document.getElementById('lbNotifBadge');
          const cur = b ? parseInt(b.textContent||'0',10) : 0;
          if(cur > 0) setBadge(cur - 1);
        } else {
          btn.disabled = false;
        }
      } catch(err){
        btn.disabled = false;
      }
    });

    // clicking the notification itself marks it read (non-blocking)
    listEl.addEventListener('click', (e)=>{
      const a = e.target.closest('a.lb-notif-item');
      if(!a) return;
      const unread = a.querySelector('.lb-notif-unread');
      if(!unread) return;
      if(a.getAttribute('data-id') === 'daily-gift'){
        try { localStorage.setItem(lbDailyGiftDismissKey, '1'); } catch(err) {}
        const b = document.getElementById('lbNotifBadge');
        const cur = b ? parseInt(b.textContent||'0',10) : 0;
        if(cur > 0) setBadge(cur - 1);
        return;
      }
      const id = parseInt(a.getAttribute('data-id')||'0', 10);
      if(!id) return;
      post({action:'notifications_mark_read', id}).then(r=>{
        if(r && r.success){
          a.querySelectorAll('.lb-notif-unread, .lb-notif-markread').forEach(el=>el.remove());
          const b = document.getElementById('lbNotifBadge');
          const cur = b ? parseInt(b.textContent||'0',10) : 0;
          if(cur > 0) setBadge(cur - 1);
        }
      }).catch(()=>{});
    });
  }

  async function refreshBadge(){
    const r = await post({action:'notifications_unread_count'});
    if(r && r.success) setBadge(calcUnreadWithDaily(r.unread));
  }

  async function refreshList(){
    const r = await post({action:'notifications_list', limit: 25, since_id: 0});
    if(r && r.success) {
      setBadge(calcUnreadWithDaily(r.unread));
      render(r.items||[]);
    }
  }

  // init
  refreshBadge().catch(()=>{});
  window.lbRefreshNotificationBadge = function(){ refreshBadge().catch(()=>{}); }; setInterval(()=>{ if(!window.lbRealtimeConnected && document.visibilityState !== 'hidden') refreshBadge().catch(()=>{}); }, 60000);

  const dd = document.getElementById('lbNotifDropdown');
  if(dd){
    dd.addEventListener('show.bs.dropdown', ()=>refreshList().catch(()=>{}));
  }

  const markAll = document.getElementById('lbNotifMarkAll');
  if(markAll){
    markAll.addEventListener('click', async (e)=>{
      e.preventDefault();
      const r = await post({action:'notifications_mark_all_read'});
      if(r && r.success){
        setBadge(0);
        render([]);
      }
    });
  }
})();
</script>

<!-- Upload Icon Modal -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_upload_profile_picture">

  <div id="upload-icon-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Icon</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <label for="image_url" class="js-file-attach form-label" data-hs-file-attach-options='{
            "textTarget": "[for=\"customFile\"]"
          }'>
            Upload your file
          </label>
          <input class="form-control" accept="image/*" type="file" name="image_url" id="image_url">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>

      </div>
    </div>
  </div>
</form>