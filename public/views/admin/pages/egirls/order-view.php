<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<style>
/* Wider egirl admin pages, matched to booster admin layout. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>

<?= $this->start('styles') ?>
<style>
.admin-order-view .order-page-wrap{padding:1rem;}
@media (min-width:992px){.admin-order-view .order-page-wrap{padding:1.5rem;}}
.admin-order-view .card{border-radius:1rem;overflow:visible;}
.admin-order-view .card-header{padding:.8rem 1rem;}
.admin-order-view .card-body,.admin-order-view .card-footer{padding:.9rem 1rem;}
.admin-order-view .card-header-title{font-size:.9rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin:0;}

.admin-order-view .lb-status{
  display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .70rem;border-radius:999px;
  font-weight:950;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;
  border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:rgba(255,255,255,.85);
}
.admin-order-view .lb-status__dot{width:7px;height:7px;border-radius:50%;background:currentColor;}
.admin-order-view .lb-status.status-paid{color:#b18cff;border-color:rgba(177,140,255,.22);background:rgba(177,140,255,.10);}
.admin-order-view .lb-status.status-in_progress{color:#4ea1ff;border-color:rgba(78,161,255,.25);background:rgba(78,161,255,.12);}
.admin-order-view .lb-status.status-completed{color:#1fe6c6;border-color:rgba(31,230,198,.22);background:rgba(31,230,198,.10);}
.admin-order-view .lb-status.status-unpaid{color:#ff6b6b;border-color:rgba(255,107,107,.20);background:rgba(255,107,107,.10);}
.admin-order-view .lb-status.status-cancelled,.admin-order-view .lb-status.status-refunded{color:#ff8a4c;border-color:rgba(255,138,76,.22);background:rgba(255,138,76,.10);}

.admin-order-view .lb-head.card{border-radius:18px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);}
.admin-order-view .lb-head .dropdown-menu{z-index:1060;}
.admin-order-view .lb-head__top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1rem 1rem .85rem 1rem;}
.admin-order-view .lb-head__left{display:flex;align-items:flex-start;gap:.85rem;min-width:0;flex:1 1 auto;}
.admin-order-view .lb-head__icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.10);flex:0 0 auto;}
.admin-order-view .lb-head__icon i{font-size:1.35rem;opacity:.95;}
.admin-order-view .lb-head__title{min-width:0;}
.admin-order-view .lb-head__title-row{display:flex;align-items:baseline;gap:.6rem;min-width:0;}
.admin-order-view .lb-head__h1{margin:0;font-weight:950;font-size:1.15rem;line-height:1.2;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-order-view .lb-head__id{font-weight:900;font-size:.85rem;opacity:.55;white-space:nowrap;}
.admin-order-view .lb-head__sub{margin-top:.45rem;}
.admin-order-view .lb-head__actions{flex:0 0 auto;display:flex;align-items:flex-start;}
.admin-order-view .lb-head__meta{display:flex;flex-wrap:wrap;gap:.55rem;padding:.85rem 1rem 1rem 1rem;border-top:1px solid rgba(255,255,255,.06);}
.admin-order-view .lb-meta-pill{display:flex;align-items:center;gap:.55rem;padding:.55rem .75rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);min-width:160px;max-width:100%;}
.admin-order-view .lb-meta-pill__k{font-weight:950;font-size:.70rem;letter-spacing:.08em;text-transform:uppercase;opacity:.55;white-space:nowrap;}
.admin-order-view .lb-meta-pill__v{font-weight:900;font-size:.92rem;opacity:.90;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

.admin-order-view .lb-order-actions-btn{
  display:inline-flex;align-items:center;gap:.55rem;padding:.55rem .9rem;border-radius:999px;
  border:1px solid rgba(255,255,255,.10);box-shadow:inset 0 1px 0 rgba(255,255,255,.06);background:rgba(255,255,255,.04);
}
.admin-order-view .lb-order-actions-btn:hover{background:rgba(255,255,255,.08);}
.admin-order-view .lb-order-actions-btn__ico{width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;}
.admin-order-view .lb-order-actions-btn__txt{font-weight:900;letter-spacing:.02em;font-size:.85rem;}
.admin-order-view .lb-order-actions-btn__chev{opacity:.75;font-size:.85rem;}

.admin-order-view .booster-intro-card{border-radius:1.25rem;position:relative;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.03);overflow:hidden;}
.admin-order-view .booster-intro-bg{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.12;pointer-events:none;}
.admin-order-view .booster-intro-body{position:relative;padding:1rem 1.15rem;display:flex;flex-direction:column;gap:.9rem;}
.admin-order-view .booster-intro-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;min-width:0;}
.admin-order-view .booster-intro-left{display:flex;align-items:center;gap:.9rem;min-width:0;}
.admin-order-view .booster-intro-avatar{width:68px;height:68px;border-radius:999px;overflow:hidden;flex:0 0 auto;position:relative;border:1px solid rgba(255,255,255,.10);box-shadow:0 14px 45px rgba(0,0,0,.45);background:rgba(255,255,255,.03);}
.admin-order-view .booster-intro-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
.admin-order-view .booster-intro-glow{position:absolute;inset:-8px;border-radius:999px;background:radial-gradient(circle, rgba(168,85,247,.28), transparent 65%);filter:blur(6px);z-index:-1;}
.admin-order-view .booster-intro-main{min-width:0;}
.admin-order-view .booster-intro-name{display:flex;align-items:center;gap:.6rem;font-weight:950;font-size:1.10rem;line-height:1.1;min-width:0;}
.admin-order-view .booster-intro-name span{min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-order-view .booster-rank-pill{margin-top:.45rem;display:inline-flex;align-items:center;gap:.5rem;padding:.35rem .55rem;border-radius:999px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.18);font-weight:900;font-size:.80rem;opacity:.95;line-height:1;}
.admin-order-view .booster-rank-pill img{width:22px;height:22px;border-radius:999px;object-fit:cover;}
.admin-order-view .booster-rank-pill i{width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;line-height:1;}
.admin-order-view .booster-intro-right{flex:0 0 auto;display:flex;align-items:center;justify-content:flex-end;}
.admin-order-view .visit-profile-btn{
  display:inline-flex;align-items:center;gap:.45rem;padding:.38rem .75rem;border-radius:999px;
  font-weight:950;font-size:.72rem;letter-spacing:.10em;text-transform:uppercase;text-decoration:none;white-space:nowrap;
  color:#4ea1ff;background:rgba(78,161,255,.12);border:1px solid rgba(78,161,255,.25);box-shadow:0 10px 30px rgba(0,0,0,.22);transition:.15s ease;
}
.admin-order-view .visit-profile-btn:hover{transform:translateY(-1px);background:rgba(78,161,255,.18);border-color:rgba(78,161,255,.35);color:#8fc2ff;}
.admin-order-view .booster-intro-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem;}
.admin-order-view .booster-intro-block{border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.14);border-radius:14px;padding:.75rem .8rem;min-width:0;}
.admin-order-view .booster-intro-label{margin:0 0 .55rem 0;font-size:.72rem;letter-spacing:.10em;text-transform:uppercase;opacity:.72;font-weight:900;}
.admin-order-view .booster-intro-value{font-weight:900;font-size:.98rem;line-height:1.2;word-break:break-word;}
@media (max-width:991.98px){.admin-order-view .booster-intro-cards{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media (max-width:575.98px){
  .admin-order-view .lb-head__top{padding:.85rem .85rem .70rem .85rem;}
  .admin-order-view .lb-head__meta{padding:.70rem .85rem .85rem .85rem;}
  .admin-order-view .lb-head__title-row{flex-wrap:wrap!important;align-items:flex-start!important;}
  .admin-order-view .lb-head__id{width:100%!important;margin-top:.15rem;}
  .admin-order-view .lb-head__h1{font-size:1.05rem;white-space:normal!important;display:-webkit-box!important;-webkit-box-orient:vertical!important;-webkit-line-clamp:3!important;overflow:hidden!important;text-overflow:ellipsis!important;}
  .admin-order-view .lb-meta-pill{min-width:0;flex:1 1 calc(50% - .55rem);}
  .admin-order-view .booster-intro-top{display:grid!important;grid-template-columns:auto 1fr;grid-template-areas:"av btn" "main main";align-items:start!important;gap:.6rem .75rem;}
  .admin-order-view .booster-intro-left{display:contents!important;}
  .admin-order-view .booster-intro-avatar{grid-area:av;align-self:start;width:60px;height:60px;}
  .admin-order-view .booster-intro-right{grid-area:btn;justify-self:end;align-self:start;}
  .admin-order-view .booster-intro-main{grid-area:main;margin-top:.15rem;min-width:0;}
  .admin-order-view .booster-intro-name{font-size:1.02rem;}
  .admin-order-view .booster-intro-cards{grid-template-columns:1fr;}
}

.admin-order-view .order-chat-card .chat-bg{background:#1e2022;border-radius:0;}
.admin-order-view #chat_messages{height:clamp(340px,55vh,520px);min-height:340px;overflow:auto;padding:14px;display:flex;flex-direction:column;gap:12px;scrollbar-width:thin;scrollbar-color:#3a4254 #25282a;}
.admin-order-view #chat_messages::-webkit-scrollbar{width:6px;}
.admin-order-view #chat_messages::-webkit-scrollbar-track{background:transparent;border-radius:999px;}
.admin-order-view #chat_messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:999px;}

.admin-order-view .lb-msg{display:flex;flex-direction:column;max-width:82%;}
.admin-order-view .lb-msg--start{align-self:flex-start;}
.admin-order-view .lb-msg--end{align-self:flex-end;}
.admin-order-view .lb-msg__head{display:flex;align-items:center;gap:10px;margin-bottom:6px;opacity:.95;}
.admin-order-view .lb-msg__head--end{flex-direction:row-reverse;text-align:right;}
.admin-order-view .lb-msg__avatar{width:36px;height:36px;border-radius:999px;object-fit:cover;flex:0 0 auto;border:1px solid rgba(255,255,255,.10);box-shadow:0 10px 25px rgba(0,0,0,.35);background:rgba(255,255,255,.03);}
.admin-order-view .lb-msg__meta{flex:1;min-width:0;line-height:1.1;}
.admin-order-view .lb-msg__toprow{display:flex;align-items:center;gap:10px;width:100%;}
.admin-order-view .lb-msg__name{font-weight:900;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-order-view .lb-msg__time{margin-left:auto;font-size:.74rem;color:rgba(255,255,255,.55);white-space:nowrap;}
.admin-order-view .lb-badge{display:inline-flex;align-items:center;gap:6px;padding:2px 10px;border-radius:999px;font-size:.70rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;margin-left:8px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);}
.admin-order-view .lb-badge--admin{color:#ff6b6b;background:rgba(255,107,107,.10);}
.admin-order-view .lb-badge--booster{color:#1fe6c6;background:rgba(31,230,198,.10);}
.admin-order-view .lb-badge--customer{color:#4ea1ff;background:rgba(78,161,255,.10);}
.admin-order-view .lb-badge--system{color:#b18cff;background:rgba(177,140,255,.10);}
.admin-order-view .lb-msg__bubble{position:relative;padding:12px 14px;border-radius:14px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);box-shadow:0 14px 40px rgba(0,0,0,.35);backdrop-filter:blur(10px);word-break:break-word;overflow-wrap:anywhere;}
.admin-order-view .lb-msg--end .lb-msg__bubble{background:rgba(78,161,255,.10);border-color:rgba(78,161,255,.18);}
.admin-order-view .lb-msg__stamp{margin-top:6px;font-size:.72rem;opacity:.60;line-height:1.1;padding:0 2px;}
.admin-order-view .lb-msg--end .lb-msg__stamp{text-align:right;}
.admin-order-view .lb-msg--start .lb-msg__stamp{text-align:left;}
.admin-order-view .lb-syswrap{width:100%;align-self:stretch;}
.admin-order-view .lb-sys{width:100%;max-width:100%;padding:10px 14px;border-radius:14px;border:1px dashed rgba(177,140,255,.35);background:rgba(177,140,255,.10);font-weight:800;font-size:.86rem;}
.admin-order-view .lb-sys-time{margin-top:6px;font-size:.75rem;opacity:.65;color:rgba(255,255,255,.55);}

.admin-order-view .lb-overview-card .card-body{padding:.75rem .85rem;}
.admin-order-view .lb-ov-grid{list-style:none;padding:0;margin:0;display:grid;grid-template-columns:1fr!important;gap:.55rem;}
.admin-order-view .lb-ov-item{display:grid;grid-template-columns:44px 1fr;grid-template-rows:auto auto;align-items:start;column-gap:.75rem;row-gap:.20rem;padding:.62rem .75rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);min-width:0;transition:.12s ease;}
.admin-order-view .lb-ov-item:hover{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);transform:translateY(-1px);}
.admin-order-view .lb-ov-ico{width:44px;height:44px;border-radius:16px;display:grid;place-items:center;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.10);font-size:1.05rem;line-height:1;grid-row:1 / span 2;}
.admin-order-view .lb-ov-label{grid-column:2;grid-row:1;font-weight:900;font-size:.95rem;line-height:1.15;white-space:normal;min-width:0;}
.admin-order-view .lb-ov-value{grid-column:2;grid-row:2;font-weight:900;font-size:.90rem;opacity:.78;line-height:1.2;white-space:normal;overflow-wrap:anywhere;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

.admin-order-view .lb-client-card .client-row{
  display:flex;align-items:center;gap:.8rem;padding:.85rem .9rem;border:1px solid rgba(255,255,255,.08);border-radius:.95rem;background:rgba(255,255,255,.03);
}
.admin-order-view .lb-client-card .client-avatar{
  width:44px;height:44px;border-radius:999px;overflow:hidden;flex:0 0 auto;border:1px solid rgba(255,255,255,.10);box-shadow:0 10px 25px rgba(0,0,0,.35);background:rgba(255,255,255,.03);
}
.admin-order-view .lb-client-card .client-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
.admin-order-view .lb-client-card .client-name{font-weight:900;font-size:.95rem;line-height:1.15;}
.admin-order-view .lb-client-card .client-sub{opacity:.7;font-size:.82rem;margin-top:.12rem;}
.admin-order-view .lb-client-card .client-tag{margin-top:.45rem;display:inline-flex;align-items:center;gap:.4rem;padding:.26rem .6rem;border-radius:999px;border:1px solid rgba(88,101,242,.28);background:rgba(88,101,242,.14);color:#cfd5ff;font-weight:800;font-size:.72rem;}

.admin-order-view .lb-actions-card .btn{border-radius:.85rem;font-weight:900;}
.admin-order-view .lb-actions-grid{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;}
@media (max-width:575.98px){.admin-order-view .lb-actions-grid{grid-template-columns:1fr;}}
.admin-order-view .btn-lb-cancel{background:#f2c792;border-color:#f2c792;color:#000;}
.admin-order-view .btn-lb-refund{background:#8b8f98;border-color:#8b8f98;color:#fff;}
.admin-order-view .btn-lb-delete{background:#ec4b7a;border-color:#ec4b7a;color:#fff;}

.admin-order-view .lb-session-card .session-list{display:flex;flex-direction:column;gap:.65rem;}
.admin-order-view .lb-session-card .session-item{display:grid;grid-template-columns:44px 1fr;column-gap:.75rem;row-gap:.2rem;padding:.72rem .8rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);}
.admin-order-view .lb-session-card .session-ico{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.10);grid-row:1 / span 2;}
.admin-order-view .lb-session-card .session-label{font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;opacity:.65;font-weight:900;}
.admin-order-view .lb-session-card .session-value{font-weight:900;font-size:.95rem;line-height:1.25;word-break:break-word;}

.admin-order-view .dropdown-menu{background:#25282A;border:1px solid rgba(255,255,255,.10);border-radius:14px;box-shadow:0 24px 70px rgba(0,0,0,.50);padding:.4rem;}
.admin-order-view .dropdown-item{border-radius:10px;padding:.6rem .75rem;color:rgba(255,255,255,.88);font-weight:700;}
.admin-order-view .dropdown-item:hover{background:rgba(255,255,255,.06);color:#fff;}
.admin-order-view .dropdown-divider{border-color:rgba(255,255,255,.08);}

</style>
<?= $this->end() ?>

<?php
$order      = $order ?? [];
$messages   = $messages ?? [];
$id         = (int)($order['id'] ?? 0);
$statusRaw  = strtoupper($order['status'] ?? 'UNPAID');
$statusKey  = strtolower(str_replace(' ', '_', $statusRaw));
$priceCents = (int)($order['price'] ?? 0);
$svcName    = htmlspecialchars($order['service_title'] ?? 'E-Girl Session');
$gameName   = !empty($order['game']) ? strtoupper(htmlspecialchars($order['game'])) : '';
$egirlName  = htmlspecialchars($order['egirl_username'] ?? '—');
$egirlIcon  = $order['egirl_icon'] ?? '';
$egirlInit  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['egirl_username'] ?? 'E') ?: 'E', 0, 1));
$clientName = htmlspecialchars($order['client_username'] ?? '—');
$clientIcon = $order['client_icon'] ?? '';
$clientInit = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['client_username'] ?? 'C') ?: 'C', 0, 1));
$clientDiscord = htmlspecialchars($order['client_discord'] ?? '');
$lastMsgTime = 0;
if (!empty($messages)) {
    $last = end($messages);
    $lastMsgTime = (int)($last['time'] ?? 0);
}
$statusClass = 'status-' . $statusKey;

// The newer "LoL GGirl" boost flow (booked via /ggirls) doesn't have its own
// dedicated columns for mode/server/rank/assignment yet — it embeds them as a
// "DATA:{...}" json block at the end of client_notes. Parse that out here so
// we can show it as proper structured fields instead of a raw text dump.
$egOrderDetails = [];
$egNotesRaw = (string)($order['client_notes'] ?? '');
if (trim($egNotesRaw) !== '' && preg_match('/DATA:\s*(\{.*\})/is', $egNotesRaw, $egDataMatch)) {
    $egDecoded = json_decode(trim($egDataMatch[1]), true);
    if (is_array($egDecoded)) {
        $egOrderDetails = $egDecoded;
    }
}
$isLolGgirlBoost   = (($egOrderDetails['type'] ?? '') === 'lol_ggirl_boost');
$egClientFreeNotes = trim((string)($egOrderDetails['notes'] ?? ''));
?>

<div class="order-page-wrap admin-order-view">


<div class="lb-head card mb-4">
  <div class="lb-head__top">
    <div class="lb-head__left">
      <div class="lb-head__icon">
        <i class="fa-duotone fa-gamepad-modern"></i>
      </div>

      <div class="lb-head__title">
        <div class="lb-head__title-row">
          <h1 class="lb-head__h1"><?= $svcName ?></h1>
          <span class="lb-head__id d-none d-lg-inline">#EG<?= $id ?></span>
        </div>

        <div class="lb-head__sub">
          <span class="lb-status <?= $statusClass ?>">
            <span class="lb-status__dot"></span>
            <?= str_replace('_', ' ', $statusKey) ?>
          </span>
        </div>
      </div>
    </div>

    <div class="lb-head__actions">
      <div class="dropdown nav-scroller-dropdown">
        <button type="button" class="btn lb-order-actions-btn lb-actions-btn" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="lb-order-actions-btn__ico" aria-hidden="true">
            <i class="fa-duotone fa-sliders"></i>
          </span>
          <span class="lb-order-actions-btn__txt">Order Actions</span>
          <span class="lb-order-actions-btn__chev"><i class="fa-solid fa-angle-down"></i></span>
        </button>

        <div class="dropdown-menu dropdown-menu-end">
          <span class="dropdown-header">Actions</span>

          <?php if (!empty($order['client_id'])): ?>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="admin_poke_client">
            <input type="hidden" name="ref_type" value="egirl_order">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-duotone fa-hand-point-up me-2" style="color:#f472b6;"></i>Poke Client
            </button>
          </form>
          <?php endif; ?>

          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_unpaid">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-solid fa-wallet me-2" style="color:#ff6b6b;"></i>Set Unpaid
            </button>
          </form>

          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_paid">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-solid fa-badge-check me-2" style="color:#b18cff;"></i>Set Paid
            </button>
          </form>

          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_in_progress">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-solid fa-play me-2" style="color:#4ea1ff;"></i>Set In Progress
            </button>
          </form>

          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_completed">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-solid fa-flag-checkered me-2" style="color:#1fe6c6;"></i>Set Completed
            </button>
          </form>

          <?php if (empty($order['egirl_id'])): ?>
          <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_egirl_md">
            <i class="fa-solid fa-user-plus me-2" style="color:#1fe6c6;"></i>Add GG-Girl
          </a>
          <?php endif; ?>

          <?php if (!empty($order['egirl_id'])): ?>
          <form class="ajax-form" action="<?= AJAX_URL ?>" onsubmit="return confirm('Remove the assigned GG-Girl from this order? It will become available again in the booking panel.');">
            <input type="hidden" name="action" value="egirl_remove_assigned_girl">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item" style="color:#f472b6;">
              <i class="fa-solid fa-user-xmark me-2"></i>Remove GG-Girl from Order
            </button>
          </form>
          <?php endif; ?>

          <span class="dropdown-header">Refund</span>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_refunded">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item">
              <i class="fa-solid fa-rotate-left me-2" style="color:#f5ca99;"></i>Refund
            </button>
          </form>

          <span class="dropdown-header">Danger Zone</span>
          <form class="ajax-form" action="<?= AJAX_URL ?>" onsubmit="return confirm('Delete this E-Girl order permanently?');">
            <input type="hidden" name="action" value="egirl_delete_order">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="dropdown-item" style="color:#ff6b6b;">
              <i class="fa-solid fa-trash me-2"></i>Delete Order
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="lb-head__meta">
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Order</span>
      <span class="lb-meta-pill__v">#EG<?= $id ?></span>
    </div>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Client</span>
      <span class="lb-meta-pill__v">
        <?php if (!empty($order['client_id'])): ?>
          <a href="<?= ADMN_URL ?>/client/<?= (int)$order['client_id'] ?>" style="color:inherit;text-decoration:none;" onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'"><?= $clientName ?></a>
        <?php else: ?><?= $clientName ?><?php endif; ?>
      </span>
    </div>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">E-Girl</span>
      <span class="lb-meta-pill__v">
        <?php if (!empty($order['egirl_id'])): ?>
          <a href="<?= ADMN_URL ?>/egirl/<?= (int)$order['egirl_id'] ?>" style="color:inherit;text-decoration:none;" onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'"><?= $egirlName ?></a>
        <?php else: ?><?= $egirlName ?><?php endif; ?>
      </span>
    </div>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Price</span>
      <span class="lb-meta-pill__v">€<?= number_format($priceCents / 100, 2) ?></span>
    </div>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Duration</span>
      <span class="lb-meta-pill__v"><?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></span>
    </div>
    <?php if ($isLolGgirlBoost && !empty($egOrderDetails['rank_label'])): ?>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Rank</span>
      <span class="lb-meta-pill__v"><?= htmlspecialchars($egOrderDetails['rank_label']) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($isLolGgirlBoost && !empty($egOrderDetails['server'])): ?>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Server</span>
      <span class="lb-meta-pill__v"><?= htmlspecialchars(strtoupper($egOrderDetails['server'])) ?></span>
    </div>
    <?php endif; ?>
    <div class="lb-meta-pill">
      <span class="lb-meta-pill__k">Booked</span>
      <span class="lb-meta-pill__v"><?= !empty($order['created_at']) ? date('d.m.Y H:i', strtotime($order['created_at'])) : '—' ?></span>
    </div>
  </div>
</div>

<div class="row g-4 align-items-start">
  <div class="col-12 col-lg-8">

    <div class="card booster-intro-card mb-4">
      <div class="booster-intro-bg" style="background-image:url('<?= htmlspecialchars($egirlIcon ?: ASSET_URL . '/core/main/img/default-avatar.png') ?>');"></div>

      <div class="card-body booster-intro-body">
        <div class="booster-intro-top">
          <div class="booster-intro-left">
            <div class="booster-intro-avatar">
              <span class="booster-intro-glow"></span>
              <?php if ($egirlIcon): ?>
                <img src="<?= htmlspecialchars($egirlIcon) ?>" alt="E-Girl Avatar">
              <?php else: ?>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center fw-bold"><?= $egirlInit ?></div>
              <?php endif; ?>
            </div>

            <div class="booster-intro-main">
              <div class="booster-intro-name">
                <span><?= $egirlName ?></span>
              </div>

              <div class="booster-rank-pill" title="E-Girl">
                <i class="fa-duotone fa-headset"></i>
                <span>E-Girl Session</span>
              </div>

              <?php if ($gameName): ?>
              <div class="booster-rank-pill" title="Game">
                <i class="fa-duotone fa-gamepad-modern"></i>
                <span><?= $gameName ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="booster-intro-right">
            <?php if (!empty($order['egirl_id'])): ?>
              <a class="visit-profile-btn" href="<?= ADMN_URL ?>/egirl/<?= (int)$order['egirl_id'] ?>">
                <i class="fa-duotone fa-user"></i>
                <span>View Profile</span>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="booster-intro-cards">
          <div class="booster-intro-block">
            <div class="booster-intro-label">ORDER</div>
            <div class="booster-intro-value">#EG<?= $id ?></div>
          </div>
          <div class="booster-intro-block">
            <div class="booster-intro-label">CLIENT</div>
            <div class="booster-intro-value">
              <?php if (!empty($order['client_id'])): ?>
                <a href="<?= ADMN_URL ?>/client/<?= (int)$order['client_id'] ?>" style="color:inherit;text-decoration:none;border-bottom:1px dashed rgba(255,255,255,.3);"><?= $clientName ?></a>
              <?php else: ?><?= $clientName ?><?php endif; ?>
            </div>
          </div>
          <div class="booster-intro-block">
            <div class="booster-intro-label">DURATION</div>
            <div class="booster-intro-value"><?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></div>
          </div>
          <div class="booster-intro-block">
            <div class="booster-intro-label">BOOKED</div>
            <div class="booster-intro-value"><?= !empty($order['created_at']) ? date('d.m.Y', strtotime($order['created_at'])) : '—' ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card order-chat-card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-header-title"><i class="fa-duotone fa-messages me-2"></i>Order Chat</h5>
        <span class="badge bg-soft-secondary text-secondary"><?= count($messages) ?> msg</span>
      </div>

      <div class="chat-bg">
        <div id="chat_messages">
          <?php if (empty($messages)): ?>
            <div class="lb-syswrap">
              <div class="lb-sys">No messages yet</div>
            </div>
          <?php else:
              $lS = ''; $lSid = 0;
              foreach ($messages as $msg):
                  $senderType = $msg['sender'] ?? 'system';
                  $senderId   = (int)($msg['sender_id'] ?? 0);
                  $isClient   = ($senderType === 'client');
                  $isEgirl    = ($senderType === 'booster');
                  $isSystem   = ($senderType === 'system' || $senderType === 'admin');
                  $grp        = ($senderType === $lS && $senderId === $lSid);
                  $lS = $senderType; $lSid = $senderId;
                  $sN  = $isClient ? $clientName : ($isEgirl ? $egirlName : 'System');
                  $sI  = $msg['sender_icon'] ?? ($isClient ? $clientIcon : ($isEgirl ? $egirlIcon : ''));
                  $sX  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sN) ?: 'X', 0, 1));
                  $msgText   = html_entity_decode($msg['raw'] ?? strip_tags($msg['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                  $isImg     = ($msg['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $msgText);
                  $ts        = !empty($msg['time']) ? date('d.m.Y H:i', (int)$msg['time']) : '';
          ?>
            <?php if ($isSystem): ?>
              <div class="lb-syswrap">
                <div class="lb-sys"><?= nl2br(htmlspecialchars($msgText)) ?></div>
                <div class="lb-sys-time"><?= $ts ?></div>
              </div>
            <?php else: ?>
              <div class="lb-msg <?= $isClient ? 'lb-msg--start' : 'lb-msg--end' ?>">
                <?php if (!$grp): ?>
                <div class="lb-msg__head <?= $isClient ? '' : 'lb-msg__head--end' ?>">
                  <?php if ($sI): ?>
                    <img class="lb-msg__avatar" src="<?= htmlspecialchars($sI) ?>" alt="">
                  <?php else: ?>
                    <div class="lb-msg__avatar d-flex align-items-center justify-content-center"><?= $sX ?></div>
                  <?php endif; ?>

                  <div class="lb-msg__meta">
                    <div class="lb-msg__toprow">
                      <div class="lb-msg__name"><?= htmlspecialchars($sN) ?></div>
                      <span class="lb-badge <?= $isClient ? 'lb-badge--customer' : 'lb-badge--booster' ?>">
                        <?= $isClient ? 'Customer' : 'E-Girl' ?>
                      </span>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <div class="lb-msg__bubble">
                  <?php if ($isImg): ?>
                    <a href="<?= htmlspecialchars($msgText) ?>" target="_blank" rel="noopener">
                      <img src="<?= htmlspecialchars($msgText) ?>" style="max-width:200px;border-radius:8px;cursor:zoom-in" loading="lazy">
                    </a>
                  <?php else: ?>
                    <?= nl2br(htmlspecialchars($msgText)) ?>
                  <?php endif; ?>
                </div>
                <div class="lb-msg__stamp"><?= $ts ?></div>
              </div>
            <?php endif; ?>
          <?php endforeach; endif; ?>
        </div>

        <div class="eg-admin-footer" style="padding:.8rem 1rem;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:.5rem;align-items:center;">
          <textarea class="form-control" id="egAdminIn" rows="1" placeholder="Type your message" style="min-height:44px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.9);"></textarea>
          <button class="btn btn-primary" id="egAdminSend" style="border-radius:14px;min-width:46px;"><i class="fa-solid fa-paper-plane-top"></i></button>
        </div>
      </div>
    </div>

    <div class="card lb-session-card">
      <div class="card-header">
        <h5 class="card-header-title"><i class="fa-solid fa-layer-group me-2"></i>Session Details</h5>
      </div>
      <div class="card-body">
        <div class="session-list">
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-layer-group"></i></div>
            <div class="session-label">Service</div>
            <div class="session-value"><?= $svcName ?></div>
          </div>
          <?php if ($isLolGgirlBoost && !empty($egOrderDetails['mode_title'])): ?>
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-list-check"></i></div>
            <div class="session-label">Mode</div>
            <div class="session-value"><?= htmlspecialchars($egOrderDetails['mode_title']) ?></div>
          </div>
          <?php endif; ?>
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-clock"></i></div>
            <div class="session-label">Duration</div>
            <div class="session-value"><?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></div>
          </div>
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-euro-sign"></i></div>
            <div class="session-label">Price</div>
            <div class="session-value">€<?= number_format($priceCents / 100, 2) ?></div>
          </div>
          <?php if ($gameName): ?>
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-gamepad-modern"></i></div>
            <div class="session-label">Game</div>
            <div class="session-value"><?= $gameName ?></div>
          </div>
          <?php endif; ?>
          <?php if ($isLolGgirlBoost): ?>
            <?php if (!empty($egOrderDetails['server'])): ?>
            <div class="session-item">
              <div class="session-ico"><i class="fa-solid fa-server"></i></div>
              <div class="session-label">Server</div>
              <div class="session-value"><?= htmlspecialchars(strtoupper($egOrderDetails['server'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($egOrderDetails['rank_label'])): ?>
            <div class="session-item">
              <div class="session-ico"><i class="fa-solid fa-ranking-star"></i></div>
              <div class="session-label">Rank</div>
              <div class="session-value"><?= htmlspecialchars($egOrderDetails['rank_label']) ?></div>
            </div>
            <?php endif; ?>
            <div class="session-item">
              <div class="session-ico"><i class="fa-solid fa-user-check"></i></div>
              <div class="session-label">Assignment</div>
              <div class="session-value"><?= htmlspecialchars($egOrderDetails['egirl_name'] ?? ucwords(str_replace('_', ' ', (string)($egOrderDetails['assignment'] ?? 'Any Available')))) ?></div>
            </div>
            <?php if ($egClientFreeNotes !== ''): ?>
            <div class="session-item">
              <div class="session-ico"><i class="fa-solid fa-comment-lines"></i></div>
              <div class="session-label">Client Notes</div>
              <div class="session-value"><?= nl2br(htmlspecialchars($egClientFreeNotes)) ?></div>
            </div>
            <?php endif; ?>
          <?php elseif (!empty($order['client_notes'])): ?>
          <div class="session-item">
            <div class="session-ico"><i class="fa-solid fa-comment-lines"></i></div>
            <div class="session-label">Client Notes</div>
            <div class="session-value"><?= nl2br(htmlspecialchars(html_entity_decode($order['client_notes'], ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card lb-overview-card mb-4">
      <div class="card-header"><h5 class="card-header-title">Overview</h5></div>
      <div class="card-body">
        <ul class="lb-ov-grid">
          <li class="lb-ov-item">
            <div class="lb-ov-ico"><i class="fa-solid fa-hashtag"></i></div>
            <div class="lb-ov-label">Order Details</div>
            <div class="lb-ov-value"><?= $svcName ?></div>
          </li>
          <li class="lb-ov-item">
            <div class="lb-ov-ico"><i class="fa-solid fa-circle-dot"></i></div>
            <div class="lb-ov-label">Status</div>
            <div class="lb-ov-value"><span class="lb-status <?= $statusClass ?>"><span class="lb-status__dot"></span><?= str_replace('_', ' ', $statusKey) ?></span></div>
          </li>
          <li class="lb-ov-item">
            <div class="lb-ov-ico"><i class="fa-solid fa-euro-sign"></i></div>
            <div class="lb-ov-label">Total</div>
            <div class="lb-ov-value">€<?= number_format($priceCents / 100, 2) ?></div>
          </li>
          <li class="lb-ov-item">
            <div class="lb-ov-ico"><i class="fa-solid fa-calendar"></i></div>
            <div class="lb-ov-label">Booked</div>
            <div class="lb-ov-value"><?= !empty($order['created_at']) ? date('d.m.Y H:i', strtotime($order['created_at'])) : '—' ?></div>
          </li>
        </ul>
      </div>
    </div>

    <div class="card lb-client-card mb-4">
      <div class="card-header"><h5 class="card-header-title">Client</h5></div>
      <div class="card-body">
        <div class="client-row">
          <div class="client-avatar">
            <?php if ($clientIcon): ?>
              <img src="<?= htmlspecialchars($clientIcon) ?>" alt="">
            <?php else: ?>
              <div class="w-100 h-100 d-flex align-items-center justify-content-center fw-bold"><?= $clientInit ?></div>
            <?php endif; ?>
          </div>
          <div class="min-w-0 flex-grow-1">
            <div class="client-name"><?= $clientName ?></div>
            <div class="client-sub">Client</div>
            <?php if ($clientDiscord): ?>
            <div class="client-tag"><i class="fa-brands fa-discord"></i><?= $clientDiscord ?></div>
            <?php endif; ?>
          </div>
          <?php if (!empty($order['client_id'])): ?>
          <a href="<?= ADMN_URL ?>/client/<?= (int)$order['client_id'] ?>" class="btn btn-white btn-xs">
            <i class="fa-solid fa-external-link"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card lb-actions-card">
      <div class="card-header"><h5 class="card-header-title">Order Actions</h5></div>
      <div class="card-body d-flex flex-column gap-3">
        <?php if (!empty($order['client_id'])): ?>
        <form class="ajax-form" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action" value="admin_poke_client">
          <input type="hidden" name="ref_type" value="egirl_order">
          <input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="btn btn-sm w-100" style="background:rgba(244,114,182,.14);border:1px solid rgba(244,114,182,.32);color:#f472b6;">
            <i class="fa-duotone fa-hand-point-up me-1"></i>Poke Client
          </button>
        </form>
        <?php endif; ?>
        <?php if (empty($order['egirl_id'])): ?>
        <a href="#" data-bs-toggle="modal" data-bs-target="#add_egirl_md" class="btn btn-sm w-100" style="background:rgba(31,230,198,.14);border:1px solid rgba(31,230,198,.32);color:#1fe6c6;">
          <i class="fa-solid fa-user-plus me-1"></i>Add GG-Girl
        </a>
        <?php endif; ?>
        <?php if (!empty($order['egirl_id'])): ?>
        <form class="ajax-form" action="<?= AJAX_URL ?>" onsubmit="return confirm('Remove the assigned GG-Girl from this order? It will become available again in the booking panel.');">
          <input type="hidden" name="action" value="egirl_remove_assigned_girl">
          <input type="hidden" name="order_id" value="<?= $id ?>">
          <button type="submit" class="btn btn-sm w-100" style="background:rgba(244,114,182,.14);border:1px solid rgba(244,114,182,.32);color:#f472b6;">
            <i class="fa-solid fa-user-xmark me-1"></i>Remove GG-Girl from Order
          </button>
        </form>
        <?php endif; ?>
        <div class="lb-actions-grid">
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_unpaid">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-white btn-sm w-100">Set Unpaid</button>
          </form>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_paid">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-white btn-sm w-100">Set Paid</button>
          </form>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_in_progress">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-primary btn-sm w-100">In Progress</button>
          </form>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_completed">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-success btn-sm w-100">Completed</button>
          </form>
          <form class="ajax-form" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="egirl_set_refunded">
            <input type="hidden" name="order_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-sm btn-lb-refund w-100">Refund</button>
          </form>
          
          <form class="ajax-form" action="<?= AJAX_URL ?>" onsubmit="return confirm('Delete this E-Girl order permanently?');">
          <input type="hidden" name="action" value="egirl_delete_order">
          <input type="hidden" name="order_id" value="<?= $id ?>">
          <button type="submit" class="btn btn-sm btn-lb-delete w-100"><i class="fa-solid fa-trash me-1"></i>Delete Order</button>
        </form>
        </div>

       
      </div>
    </div>
  </div>
</div>

<div id="add_egirl_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form class="ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="egirl_assign_girl">
                <input type="hidden" name="order_id" value="<?= $id ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add GG-Girl</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="">Select GG-Girl</label>
                    <div class="tom-select-custom tom-select-custom-with-tags">
                        <select name="egirl_id" class="form-select js-select" autocomplete="off"
                            data-hs-tom-select-options='{
                        "placeholder": "Search GG-Girl..."}'>
                            <option value=""></option>
                            <?php foreach (db_get_rows('boosters', ['select' => 'username,id', 'is_egirl' => 1, 'is_banned' => 0], true) as $ggirl): ?>
                                <option value="<?= $ggirl['id'] ?>"><?= htmlspecialchars($ggirl['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Add GG-Girl
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    var AJAX = '<?= AJAX_URL ?>';
    var OID  = <?= $id ?>;
    var lastTime = <?= $lastMsgTime ?> || 0;
    var body = document.getElementById('chat_messages');
    var inp  = document.getElementById('egAdminIn');
    var snd  = document.getElementById('egAdminSend');

    if (body) {
        body.scrollTop = body.scrollHeight;
    }

    function escapeHtml(str){
        return String(str || '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function renderMsg(msg){
        if (!body || !msg) return;

        var senderType = msg.sender || 'system';
        var isClient   = senderType === 'client';
        var isEgirl    = senderType === 'booster';
        var isSystem   = senderType === 'system' || senderType === 'admin';

        var sN = isClient ? <?= json_encode($clientName) ?> : (isEgirl ? <?= json_encode($egirlName) ?> : 'System');
        if (msg.sender_username) sN = msg.sender_username;

        var sI = msg.sender_icon || (isClient ? <?= json_encode($clientIcon) ?> : (isEgirl ? <?= json_encode($egirlIcon) ?> : ''));
        var sX = (sN || 'X').replace(/[^A-Za-z0-9]/g,'').substring(0,1).toUpperCase() || 'X';
        var text = msg.raw || msg.content || '';
        var ts = msg.time ? new Date(msg.time * 1000) : new Date();
        var tsText = String(ts.getDate()).padStart(2,'0') + '.'
            + String(ts.getMonth()+1).padStart(2,'0') + '.'
            + ts.getFullYear() + ' '
            + String(ts.getHours()).padStart(2,'0') + ':'
            + String(ts.getMinutes()).padStart(2,'0');

        var isImg = (msg.type || '') === 'image' || /\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(text);

        if (isSystem) {
            body.insertAdjacentHTML('beforeend',
                '<div class="lb-syswrap">' +
                    '<div class="lb-sys">' + escapeHtml(text).replace(/\n/g,'<br>') + '</div>' +
                    '<div class="lb-sys-time">' + tsText + '</div>' +
                '</div>'
            );
        } else {
            var sideClass = isClient ? 'lb-msg--start' : 'lb-msg--end';
            var badgeClass = isClient ? 'lb-badge--customer' : 'lb-badge--booster';
            var badgeText = isClient ? 'Customer' : 'E-Girl';
            var avatar = sI
                ? '<img class="lb-msg__avatar" src="' + escapeHtml(sI) + '" alt="">'
                : '<div class="lb-msg__avatar d-flex align-items-center justify-content-center">' + escapeHtml(sX) + '</div>';

            var bubble = isImg
                ? '<a href="' + escapeHtml(text) + '" target="_blank" rel="noopener"><img src="' + escapeHtml(text) + '" style="max-width:200px;border-radius:8px;cursor:zoom-in" loading="lazy"></a>'
                : escapeHtml(text).replace(/\n/g,'<br>');

            body.insertAdjacentHTML('beforeend',
                '<div class="lb-msg ' + sideClass + '">' +
                    '<div class="lb-msg__head ' + (isClient ? '' : 'lb-msg__head--end') + '">' +
                        avatar +
                        '<div class="lb-msg__meta">' +
                            '<div class="lb-msg__toprow">' +
                                '<div class="lb-msg__name">' + escapeHtml(sN) + '</div>' +
                                '<span class="lb-badge ' + badgeClass + '">' + badgeText + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="lb-msg__bubble">' + bubble + '</div>' +
                    '<div class="lb-msg__stamp">' + tsText + '</div>' +
                '</div>'
            );
        }

        body.scrollTop = body.scrollHeight;
    }

    function sendMessage(){
        if (!inp || !snd) return;
        var txt = (inp.value || '').trim();
        if (!txt) return;

        snd.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', AJAX, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function(){
            snd.disabled = false;
            try {
                var res = JSON.parse(xhr.responseText || '{}');
                if (res && res.success) {
                    inp.value = '';
                    var nowTs = res.time || Math.floor(Date.now() / 1000);
                    lastTime = Math.max(lastTime, nowTs);
                    renderMsg({
                        sender: 'admin',
                        raw: txt,
                        type: 'text',
                        time: nowTs,
                        sender_username: 'Admin',
                        sender_icon: ''
                    });
                } else {
                    alert((res && res.message) ? res.message : 'Could not send message.');
                }
            } catch (e) {
                alert('Could not send message.');
            }
        };
        xhr.onerror = function(){
            snd.disabled = false;
            alert('Could not send message.');
        };
        xhr.send('action=admin_egirl_chat_send&order_id=' + encodeURIComponent(OID) + '&message=' + encodeURIComponent(txt));
    }

    function poll(){
        var xhr = new XMLHttpRequest();
        xhr.open('POST', AJAX, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function(){
            try {
                var res = JSON.parse(xhr.responseText || '{}');
                var arr = Array.isArray(res.messages) ? res.messages : [];
                if (!arr.length) return;
                arr.forEach(function(m){
                    renderMsg(m);
                    var mt = parseInt(m.time || 0, 10);
                    if (mt > lastTime) lastTime = mt;
                });
            } catch (e) {}
        };
        xhr.send('action=admin_egirl_chat_poll&order_id=' + encodeURIComponent(OID) + '&after_time=' + encodeURIComponent(lastTime));
    }

    if (snd) {
        snd.addEventListener('click', function(e){
            e.preventDefault();
            sendMessage();
        });
    }

    if (inp) {
        inp.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    window.lbOrderViewChatUpdate = function (data) {
        if (!data || data.order_id === ('eg_' + OID)) {
            poll();
        }
    };

    setInterval(function () {
        if (document.visibilityState !== 'visible') return;
        if (window.lbRealtimeConnected) return;
        poll();
    }, 2500);

    setInterval(function () {
        if (document.visibilityState === 'visible' && window.lbRealtimeConnected) return;
        poll();
    }, 60000);
})();
</script>
<?= $this->end() ?>

