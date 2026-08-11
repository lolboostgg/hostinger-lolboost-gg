<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Account #' . ($data['id'] ?? '') . ' - Admin Area | LoLBoost.gg', 'h1' => 'Account #' . ($data['id'] ?? ''), 'description' => 'Edit LoL Account.'], 'contain' => true]) ?>

<?php
/**
 * Admin: Premium Account View + Chat, ranked account style
 * Variablen: $data, $package, $client, $admin, $chat_messages
 */

$account      = $data ?? [];
$account_id   = (int)($account['id'] ?? 0);
$package      = $package ?? null;
$client       = $client ?? null;
$admin        = $admin ?? [];
$chat_msgs    = $chat_messages ?? [];

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$decodeAccountValue = fn($v) => html_entity_decode((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

$adminIcon    = trim((string)($admin['icon'] ?? ''));
$adminName    = trim((string)($admin['username'] ?? 'Admin'));
$adminInitial = strtoupper(substr($adminName, 0, 1));
$adminId      = (int)($admin['id'] ?? (defined('ADMIN_ID') ? ADMIN_ID : 0));

$clientIcon    = !empty($client['icon']) ? $client['icon'] : null;
$clientName    = (string)($client['username'] ?? 'Client');
$clientInitial = strtoupper(substr($clientName, 0, 1));

$pkg_name   = (string)($package['name'] ?? ('Account #' . $account_id));
$pkg_server = strtoupper((string)($package['server'] ?? ($account['server'] ?? '')));
$pkg_icon   = (string)($package['icon'] ?? '');
$pkg_price  = isset($package['price']) ? (float)$package['price'] : 0;
if ($pkg_price > 1000) { $pkg_price = $pkg_price / 100; }

$statusMap = [
    0 => ['label' => 'Available', 'cls' => 'si-available', 'icon' => 'fa-circle-check'],
    1 => ['label' => 'Sold', 'cls' => 'si-sold', 'icon' => 'fa-circle-xmark'],
    2 => ['label' => 'Banned', 'cls' => 'si-banned', 'icon' => 'fa-ban'],
    3 => ['label' => 'Cashflow', 'cls' => 'si-cashflow', 'icon' => 'fa-rotate'],
    4 => ['label' => 'Level not matching', 'cls' => 'si-level', 'icon' => 'fa-triangle-exclamation'],
    5 => ['label' => 'Logins not working', 'cls' => 'si-login', 'icon' => 'fa-key'],
];
$curStatus = (int)($account['status'] ?? 0);
$cur = $statusMap[$curStatus] ?? $statusMap[0];
$isSold = $curStatus === 1 || !empty($account['client_id']);

$rankNames = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
$divNames  = [1=>'IV',2=>'III',3=>'II',4=>'I'];
$rankKeyMap = ['unranked'=>0,'iron'=>1,'bronze'=>2,'silver'=>3,'gold'=>4,'platinum'=>5,'emerald'=>6,'diamond'=>7,'master'=>8,'grandmaster'=>9,'challenger'=>10];

$rankRaw = $package['current_rank'] ?? $package['rank_id'] ?? $package['rank'] ?? $package['tier'] ?? $account['current_rank'] ?? $account['rank'] ?? null;
$rankIndex = 0;
if (is_numeric($rankRaw)) {
    $rankIndex = max(0, min(10, (int)$rankRaw));
} else {
    $rankHaystack = strtolower((string)$rankRaw . ' ' . $pkg_name);
    foreach ($rankKeyMap as $key => $idx) {
        if (strpos($rankHaystack, $key) !== false) {
            $rankIndex = $idx;
            break;
        }
    }
}

$divisionRaw = $package['current_division'] ?? $package['division'] ?? $account['current_division'] ?? $account['division'] ?? null;
$divisionText = '';
if ($rankIndex >= 1 && $rankIndex <= 7) {
    if (is_numeric($divisionRaw) && isset($divNames[(int)$divisionRaw])) {
        $divisionText = $divNames[(int)$divisionRaw];
    } elseif (is_string($divisionRaw) && preg_match('/^(I|II|III|IV)$/i', trim($divisionRaw))) {
        $divisionText = strtoupper(trim($divisionRaw));
    } elseif (preg_match('/\b(I|II|III|IV)\b/i', $pkg_name, $m)) {
        $divisionText = strtoupper($m[1]);
    }
}
$rankText = $rankNames[$rankIndex] ?? 'Unranked';
if ($divisionText !== '') { $rankText .= ' ' . $divisionText; }
$rankIcon = function_exists('util_rank_img')
    ? util_rank_img('lol', 'mini', $rankIndex)
    : (defined('ASSET_URL') ? ASSET_URL : '') . '/core/main/img/lol/ranks/mini/' . $rankIndex . '.png';

$createdDate = !empty($account['created_at']) ? date('d.m.Y', strtotime((string)$account['created_at'])) : '—';
$updatedDate = !empty($account['updated_at']) ? date('d.m.Y H:i', strtotime((string)$account['updated_at'])) : '—';
$buyerDate   = !empty($account['sold_at']) ? date('d.m.Y H:i', strtotime((string)$account['sold_at'])) : $updatedDate;

$login = $decodeAccountValue($account['login'] ?? '');
$password = $decodeAccountValue($account['password'] ?? '');
$dataField = $decodeAccountValue($account['data'] ?? '');
?>

<style>
.admin-prem-chat{--av-accent:#9f8cff;--av-success:#4ade80;--av-danger:#fb7185;--av-warning:#fbbf24;}
.admin-prem-chat .card,.admin-prem-chat .av-sidebar-card,.admin-prem-chat .av-head{border-radius:.75rem!important;border:1px solid rgba(255,255,255,.07)!important;background:linear-gradient(180deg,rgba(37,40,42,.98),rgba(31,34,36,.98))!important;box-shadow:0 14px 34px rgba(0,0,0,.18)!important;overflow:hidden;}
.admin-prem-chat .card::before{display:none!important;}.admin-prem-chat .card-header,.admin-prem-chat .av-sc-header,.admin-prem-chat .av-chat-header{background:rgba(255,255,255,.018)!important;border-bottom:1px solid rgba(255,255,255,.06)!important;}
.admin-prem-chat .card-header-title,.admin-prem-chat .av-sc-title,.admin-prem-chat .av-chat-title{font-size:.78rem!important;font-weight:900!important;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.72)!important;}
.av-head{margin-bottom:20px;}.av-head-body{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid rgba(255,255,255,.06);}.av-title-wrap{display:flex;align-items:center;gap:14px;flex:1;min-width:0;}.av-rank-icon{width:52px;height:52px;border-radius:.6rem;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 10px 24px rgba(0,0,0,.14);}.av-rank-icon img{width:32px;height:32px;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));}.av-head h1{font-size:1.15rem!important;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;letter-spacing:-.02em;}.av-head-meta{font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;}.av-head-meta strong{color:rgba(255,255,255,.58);}.av-actions{display:flex;gap:7px;flex-wrap:wrap;align-items:center;}.av-btn-primary,.av-btn-warning,.av-btn-ghost{display:inline-flex;align-items:center;gap:.4rem;min-height:34px;padding:7px 13px;border-radius:.6rem;font-size:.83rem;font-weight:800;text-decoration:none;cursor:pointer;transition:background .12s,opacity .15s,transform .12s;}.av-btn-primary{background:linear-gradient(135deg,#6d5cff,#9f8cff);border:0;color:#fff;box-shadow:0 10px 24px rgba(109,92,255,.18);}.av-btn-primary:hover{opacity:.9;color:#fff;transform:translateY(-1px);}.av-btn-warning{background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.22);color:#fbbf24;}.av-btn-ghost{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.7);}.av-btn-ghost:hover{background:rgba(255,255,255,.09);color:#fff;}.av-meta-row{display:flex;flex-wrap:wrap;gap:8px;padding:13px 20px 15px;background:rgba(0,0,0,.08);}.av-meta-pill{display:inline-flex;align-items:center;gap:.35rem;padding:5px 11px;border-radius:999px;font-size:.75rem;font-weight:700;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.62);box-shadow:inset 0 1px 0 rgba(255,255,255,.04);}.av-meta-pill strong{color:rgba(255,255,255,.94);}.av-rank-mini{width:18px;height:18px;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.45));}.av-status{display:inline-flex;align-items:center;gap:.35rem;padding:4px 10px;border-radius:999px;font-size:.68rem;font-weight:800;letter-spacing:.02em;text-transform:uppercase;}.si-available{background:rgba(0,201,167,.12);color:#00c9a7;border:1px solid rgba(0,201,167,.28);}.si-sold{background:rgba(237,76,120,.13);color:#ed4c78;border:1px solid rgba(237,76,120,.28);}.si-banned{background:rgba(245,202,153,.12);color:#f5ca99;border:1px solid rgba(245,202,153,.28);}.si-cashflow{background:rgba(9,165,190,.12);color:#09a5be;border:1px solid rgba(9,165,190,.28);}.si-level{background:rgba(255,171,0,.12);color:#ffab00;border:1px solid rgba(255,171,0,.28);}.si-login{background:rgba(237,76,120,.13);color:#ed4c78;border:1px solid rgba(237,76,120,.28);}
.av-sc-header{display:flex;align-items:center;gap:8px;padding:12px 16px;}.av-sc-icon{width:26px;height:26px;border-radius:.55rem;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.1);font-size:.75rem;box-shadow:inset 0 1px 0 rgba(255,255,255,.05);}.av-sc-title{flex:1;}.av-admin-only-badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:99px;font-size:.62rem;font-weight:800;background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.2);color:#f87171;}.av-buyer-row{display:flex;align-items:center;gap:12px;padding:14px 16px;}.av-buyer-avi{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:900;flex-shrink:0;}.av-buyer-name{font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);}.av-buyer-sub{font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px;}.av-creds-list{padding:4px 0 6px;}.av-cred-item{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.055);}.av-cred-item:last-child{border-bottom:0;}.av-cred-left{display:flex;align-items:center;gap:7px;min-width:95px;flex-shrink:0;}.av-cred-ico{font-size:.65rem;color:rgba(255,255,255,.22);width:12px;text-align:center;}.av-cred-lbl{font-size:.72rem;font-weight:700;color:rgba(255,255,255,.38);white-space:nowrap;}.av-cred-right{display:flex;align-items:flex-start;justify-content:flex-end;gap:7px;min-width:0;flex:1;}.av-cred-val{font-family:monospace;font-size:.77rem;font-weight:700;color:rgba(255,255,255,.82);max-width:100%;overflow:visible;text-overflow:clip;white-space:nowrap;overflow-wrap:normal;word-break:normal;text-align:right;line-height:1.45;cursor:pointer;}.av-cred-item--data .av-cred-right{align-items:flex-start;}.av-cred-item--data .av-cred-val{white-space:normal;overflow-wrap:normal;word-break:normal;text-align:left;line-height:1.55;}.av-copy-btn{background:none;border:none;color:rgba(255,255,255,.2);cursor:pointer;padding:2px 3px;transition:color .12s;line-height:1;}.av-copy-btn:hover{color:#9f8cff;}.av-cred-item.is-copied .av-copy-btn,.av-cred-item.is-copied .av-cred-val{color:#4ade80;}.av-stat-grid{display:grid;grid-template-columns:1fr 1fr;}.av-stat-item{display:flex;align-items:center;gap:8px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.055);border-right:1px solid rgba(255,255,255,.055);}.av-stat-item:nth-child(even){border-right:0;}.av-stat-item:nth-last-child(-n+2){border-bottom:0;}.av-stat-ico{font-size:.65rem;color:rgba(255,255,255,.25);width:14px;flex-shrink:0;}.av-stat-lbl{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.04em;line-height:1;}.av-stat-val{font-size:.8rem;font-weight:800;color:rgba(255,255,255,.82);margin-top:2px;line-height:1.2;}.av-detail-box{white-space:pre-wrap;font-size:.875rem;line-height:1.65;color:#00d0e8;background:rgba(13,148,160,.14);border:1px solid rgba(13,148,160,.18);border-radius:.6rem;padding:1.25rem;}.pkg-form-row{display:grid;grid-template-columns:190px 1fr;align-items:center;gap:1rem;padding:.9rem 0;border-bottom:1px solid rgba(255,255,255,.06);}.pkg-form-row:last-child{border-bottom:none;}.pkg-form-label{font-size:.82rem;font-weight:700;color:#91989e;}.pkg-form-label small{display:block;font-size:.72rem;color:#555d65;font-weight:400;margin-top:.15rem;}
.av-chat-header{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;}#adminPremChatMessages{min-height:300px;max-height:480px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;scroll-behavior:smooth;background:linear-gradient(180deg,rgba(16,18,20,.26),rgba(16,18,20,.10));}#adminPremChatMessages::-webkit-scrollbar{width:5px;}#adminPremChatMessages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:3px;}.lb-chat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:240px;opacity:.4;gap:.5rem;text-align:center;}.lb-msg{display:flex;flex-direction:column;gap:.35rem;max-width:72%;margin:.4rem 0;}.lb-msg--end{align-self:flex-end;align-items:flex-end;}.lb-msg--start{align-self:flex-start;}.lb-msg__head{display:flex;align-items:flex-start;gap:.5rem;}.lb-msg__head--end{flex-direction:row-reverse;}.lb-msg__avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;}.lb-msg__name{font-size:.76rem;font-weight:700;color:rgba(255,255,255,.7);}.lb-msg__time{font-size:.67rem;color:rgba(255,255,255,.28);}.lb-msg__bubble{background:rgba(255,255,255,.07);border-radius:12px;padding:.55rem .8rem;font-size:.87rem;line-height:1.5;color:rgba(255,255,255,.88);word-break:break-word;}.lb-msg--end .lb-msg__bubble{background:rgba(109,92,255,.22);}.lb-badge{font-size:.6rem;font-weight:800;padding:1px 6px;border-radius:99px;text-transform:uppercase;}.lb-badge--admin{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);}.lb-badge--client{background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.22);}.lb-syswrap{width:100%;max-width:100%;margin:.55rem 0 .85rem;}.lb-sys{display:block;width:100%;background:rgba(109,92,255,.14);border:1px dashed rgba(159,140,255,.45);border-radius:18px;padding:1.15rem 1.25rem;font-size:1rem;font-weight:700;line-height:1.65;color:rgba(255,255,255,.96);text-align:left;white-space:pre-line;}.lb-sys-time{font-size:.78rem;opacity:.5;margin-top:.45rem;color:rgba(255,255,255,.55);}.btn-chat-icon{padding:0!important;width:36px;height:36px;display:inline-flex!important;align-items:center;justify-content:center;}.lb-chat-attach-preview{display:flex;align-items:center;gap:.75rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.5rem .75rem;}.lb-chat-attach-preview__thumb{width:46px;height:46px;border-radius:8px;overflow:hidden;flex-shrink:0;}.lb-chat-attach-preview__thumb img{width:100%;height:100%;object-fit:cover;}.lb-chat-attach-preview__meta{min-width:0;flex:1;}.lb-chat-attach-preview__title{font-weight:800;font-size:.82rem;}.lb-chat-attach-preview__name{font-size:.78rem;opacity:.8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}.lb-chat-attach-preview__remove{background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;padding:0 4px;font-size:.88rem;}
@media(max-width:991px){.pkg-form-row{grid-template-columns:1fr}.av-head-body{align-items:flex-start}.av-actions{width:100%}}
</style>

<div class="admin-prem-chat">

<div class="av-head mb-4">
  <div class="av-head-body">
    <div class="av-title-wrap">
      <div class="av-rank-icon"><img src="<?= $h($rankIcon) ?>" alt=""></div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1><?= $h($pkg_name) ?></h1>
          <span class="av-status <?= $cur['cls'] ?>"><i class="fa-duotone <?= $cur['icon'] ?>"></i><?= $h($cur['label']) ?></span>
        </div>
        <div class="av-head-meta">
          <?php if ($pkg_server): ?><strong><?= $h($pkg_server) ?></strong><span>·</span><?php endif ?>
          <span>#<?= $account_id ?></span><span>·</span><span><?= $h($createdDate) ?></span>
          <?php if ($client): ?><span>·</span><i class="fa-duotone fa-user"></i><strong><?= $h($clientName) ?></strong><?php endif ?>
        </div>
      </div>
    </div>
    <div class="av-actions">
      <button type="button" class="av-btn-primary" data-bs-toggle="collapse" data-bs-target="#premiumEditBox" aria-expanded="false">
        <i class="fa-duotone fa-pen"></i> Edit
      </button>
      <?php if ($client): ?>
      <button type="button" class="av-btn-primary js-admin-poke-client" data-ref-type="premium_account" data-id="<?= $account_id ?>">
        <i class="fa-duotone fa-hand-point-up"></i> Poke Client
      </button>
      <?php endif ?>
      <a href="<?= ADMN_URL ?>/account-package/<?= (int)($account['package_id'] ?? 0) ?>" class="av-btn-ghost"><i class="fa-duotone fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="av-meta-row">
    <span class="av-meta-pill"><img src="<?= $h($rankIcon) ?>" class="av-rank-mini" alt=""><strong><?= $h($rankText) ?></strong> Rank</span>
    <?php if ($pkg_price > 0): ?><span class="av-meta-pill"><i class="fa-solid fa-euro-sign"></i><strong><?= number_format($pkg_price, 2) ?> €</strong> Price</span><?php endif ?>
    <?php if ($client): ?><span class="av-meta-pill"><i class="fa-solid fa-user" style="color:#4ade80;"></i><strong style="color:#4ade80;"><?= $h($clientName) ?></strong> Buyer</span><?php endif ?>
  </div>
</div>

<div class="collapse mb-4" id="premiumEditBox">
  <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_update_account">
    <input type="hidden" name="id" value="<?= $account_id ?>">
    <div class="card">
      <div class="card-header" style="padding:14px 20px;"><h5 class="card-header-title mb-0"><i class="fa-duotone fa-pen-to-square me-2"></i>Edit Account</h5></div>
      <div class="card-body pt-2 pb-0">
        <div class="pkg-form-row"><label class="pkg-form-label">Login <small>Account email or username</small></label><input type="text" class="form-control" name="login" value="<?= $h($login) ?>"></div>
        <div class="pkg-form-row"><label class="pkg-form-label">Password <small>Account password</small></label><div class="input-group input-group-merge"><input type="text" class="form-control" name="password" id="passwordField" value="<?= $h($password) ?>"><button type="button" class="input-group-text" id="togglePw" style="cursor:pointer;"><i class="fa-duotone fa-eye" id="togglePwIcon"></i></button></div></div>
        <div class="pkg-form-row"><label class="pkg-form-label">Data <small>Email or recovery info</small></label><input type="text" class="form-control" name="data" value="<?= $h($dataField) ?>"></div>
        <div class="pkg-form-row"><label class="pkg-form-label">Status</label><div><div class="mb-2"><span class="av-status <?= $cur['cls'] ?>" id="statusPreview"><i class="fa-duotone <?= $cur['icon'] ?>"></i><?= $h($cur['label']) ?></span></div><select name="status" class="form-select" id="statusSelect"><?php foreach ($statusMap as $v => $s): ?><option value="<?= $v ?>" <?= $curStatus === $v ? 'selected' : '' ?>><?= $h($s['label']) ?></option><?php endforeach ?></select></div></div>
      </div>
      <div class="card-footer d-flex align-items-center gap-2"><button type="submit" class="btn btn-primary"><span class="indicator-label"><i class="fa-duotone fa-floppy-disk me-1"></i>Update Account</span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span></button></div>
    </div>
  </form>
</div>

<div class="row g-4 align-items-start">
  <div class="col-12 col-lg-8">
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;"><h5 class="card-header-title mb-0"><i class="fa-duotone fa-align-left me-2"></i>Details</h5></div>
      <div class="card-body" style="padding:16px 20px;">
        <div class="acc-section-title" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6d747b;padding-bottom:.5rem;border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:1rem;">Delivery Instructions</div>
        <div class="av-detail-box"><?= $h((string)($package['description'] ?? $package['delivery_instructions'] ?? 'Email access and account details are provided after purchase. If the buyer needs help, use the support chat below.')) ?></div>
      </div>
    </div>

    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title"><i class="fa-duotone fa-comments" style="color:#9f8cff;"></i> Support Chat</div>
        <?php if ($client): ?>
        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
          <?php if ($clientIcon): ?><img src="<?= $h($clientIcon) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover;"><?php else: ?><span style="width:18px;height:18px;border-radius:50%;background:rgba(74,222,128,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= $h($clientInitial) ?></span><?php endif ?><?= $h($clientName) ?>
        </div>
        <?php endif ?>
      </div>

      <div id="adminPremChatMessages">
        <?php if (empty($chat_msgs)): ?>
          <div class="lb-chat-empty"><i class="fa-duotone fa-message-slash" style="font-size:2rem;"></i><span>No messages yet.</span></div>
        <?php else: ?>
          <?php $chatText = fn($v) => html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8'); foreach ($chat_msgs as $msg):
            $isDeleted  = !empty($msg['deleted']);
            $type       = strtolower((string)($msg['sender'] ?? $msg['sender_type'] ?? 'admin'));
            $text       = $chatText($msg['message'] ?? $msg['content'] ?? '');
            $senderName = (string)($msg['sender_name'] ?? ($type === 'admin' ? $adminName : $clientName));
            $msgIcon    = (string)($msg['sender_icon'] ?? '');
            $tsRaw      = $msg['time'] ?? null;
            $ts         = is_numeric($tsRaw) ? date('d.m.Y H:i', (int)$tsRaw) : '';
            if ($type === 'system'): ?>
              <div class="lb-syswrap"><div class="lb-sys"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div><?php if ($ts): ?><div class="lb-sys-time"><?= $h($ts) ?></div><?php endif ?></div><?php continue; endif;
            $isRight = ($type === 'admin');
            if ($type === 'admin') { $showIcon = $adminIcon ?: $msgIcon; $showInit = $adminInitial; $badgeCls = 'lb-badge--admin'; $badgeLabel = 'Admin'; }
            else { $showIcon = $msgIcon ?: $clientIcon; $showInit = $clientInitial; $badgeCls = 'lb-badge--client'; $badgeLabel = 'Client'; }
          ?>
          <div class="lb-msg <?= $isRight ? 'lb-msg--end' : 'lb-msg--start' ?>">
            <div class="lb-msg__head <?= $isRight ? 'lb-msg__head--end' : '' ?>">
              <?php if ($showIcon): ?><img class="lb-msg__avatar" src="<?= $h($showIcon) ?>" alt=""><?php else: ?><span class="lb-msg__avatar" style="background:<?= $isRight ? 'rgba(99,102,241,.3)' : 'rgba(74,222,128,.2)' ?>;color:<?= $isRight ? '#a5b4fc' : '#4ade80' ?>;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;"><?= $h($showInit) ?></span><?php endif ?>
              <div><div style="display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;<?= $isRight ? 'flex-direction:row-reverse;' : '' ?>"><span class="lb-msg__name"><?= $h($senderName) ?></span><span class="lb-badge <?= $badgeCls ?>"><?= $badgeLabel ?></span></div><span class="lb-msg__time"><?= $h($ts) ?></span></div>
            </div>
            <div class="lb-msg__bubble"><?php if ($isDeleted): ?><em style="opacity:.55;">Message deleted</em><?php elseif (preg_match('/\.(png|jpe?g|gif|webp)(\?.*)?$/i', $text)): ?><img src="<?= $h($text) ?>" style="max-width:260px;max-height:200px;border-radius:.5rem;display:block;cursor:pointer;" onclick="window.open(this.src,'_blank')"><?php else: ?><?= nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) ?><?php endif ?></div>
          </div>
          <?php endforeach ?>
        <?php endif ?>
      </div>

      <div class="card-footer">
        <form id="adminPremChatForm" class="row gx-2 align-items-center" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="admin_premium_account_chat_send"><input type="hidden" name="account_id" value="<?= $account_id ?>"><input type="hidden" name="send_as" value="admin">
          <div class="col"><input type="text" name="message" id="adminPremChatInput" class="form-control" placeholder="Type your message to the client" autocomplete="off"></div>
          <div class="col-auto d-flex align-items-center gap-2"><input type="file" class="d-none" id="adminPremChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif"><button type="button" class="btn btn-sm btn-secondary btn-chat-icon" id="adminPremAttachBtn" title="Attach image"><i class="fa-duotone fa-paperclip"></i></button><button type="submit" class="btn btn-sm btn-primary btn-chat-icon" id="adminPremSendBtn" title="Send"><span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span><span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span></button></div>
          <div class="col-12 mt-1"><div class="lb-chat-error text-danger small d-none" id="adminPremChatError"></div><div class="lb-chat-attach-preview d-none mt-2" id="adminPremChatPreview"><div class="lb-chat-attach-preview__thumb"><img src="" alt="" id="adminPremChatPreviewImg"></div><div class="lb-chat-attach-preview__meta"><div class="lb-chat-attach-preview__title">Image ready to send</div><div class="lb-chat-attach-preview__name" id="adminPremChatPreviewName"></div></div><button type="button" class="lb-chat-attach-preview__remove" id="adminPremChatRemoveBtn"><i class="fa-duotone fa-xmark"></i></button></div><div class="text-muted small mt-2">Tip: You can paste screenshots with <strong>Ctrl+V</strong>.</div></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="av-sidebar-card mb-3"><div class="av-sc-header"><span class="av-sc-icon" style="background:rgba(74,222,128,.1);border-color:rgba(74,222,128,.2);"><i class="fa-solid fa-user" style="color:#4ade80;"></i></span><span class="av-sc-title">Buyer</span></div><div class="av-buyer-row"><?php if ($client): ?><?php if ($clientIcon): ?><img src="<?= $h($clientIcon) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;" alt=""><?php else: ?><div class="av-buyer-avi" style="background:rgba(74,222,128,.14);color:#4ade80;border:1px solid rgba(74,222,128,.24);"><?= $h($clientInitial) ?></div><?php endif ?><div class="av-buyer-info" style="min-width:0;flex:1;"><div class="av-buyer-name"><?= $h($clientName) ?></div><div class="av-buyer-sub"><?= $h($client['email'] ?? '') ?></div></div><a href="<?= ADMN_URL ?>/client/<?= (int)($client['id'] ?? 0) ?>" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;"><i class="fa-duotone fa-user"></i> Profile</a><button type="button" class="av-btn-primary js-admin-poke-client" data-ref-type="premium_account" data-id="<?= $account_id ?>" style="font-size:.75rem;padding:5px 12px;"><i class="fa-duotone fa-hand-point-up"></i> Poke</button><?php else: ?><div style="font-size:.8rem;color:rgba(255,255,255,.35);font-weight:700;">No buyer assigned yet.</div><?php endif ?></div></div>

    <div class="av-sidebar-card mb-3"><div class="av-sc-header"><span class="av-sc-icon" style="background:rgba(239,68,68,.10);border-color:rgba(239,68,68,.2);"><i class="fa-solid fa-key" style="color:#f87171;"></i></span><span class="av-sc-title">Credentials</span><span class="av-admin-only-badge">Admin Only</span></div><div class="av-creds-list"><?php $creds = [['Login',$login,'fa-solid fa-user'],['Password',$password,'fa-solid fa-key'],['Data',$dataField,'fa-solid fa-database']]; foreach ($creds as [$lbl,$val,$ico]): $val = $decodeAccountValue($val); if (trim($val) === '') continue; $rowClass = ($lbl === 'Data') ? ' av-cred-item--data' : ''; ?><div class="av-cred-item<?= $rowClass ?>"><div class="av-cred-left"><i class="<?= $ico ?> av-cred-ico"></i><span class="av-cred-lbl"><?= $h($lbl) ?></span></div><div class="av-cred-right"><span class="av-cred-val js-copy-cred" data-copy="<?= $h($val) ?>" title="<?= $h($val) ?>"><?= $h($val) ?></span><button class="av-copy-btn js-copy-btn" type="button" data-copy="<?= $h($val) ?>"><i class="fa-duotone fa-copy"></i></button></div></div><?php endforeach ?></div></div>

    <div class="av-sidebar-card mb-3"><div class="av-sc-header"><span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;"></i></span><span class="av-sc-title">Overview</span></div><div class="av-stat-grid"><div class="av-stat-item"><img src="<?= $h($rankIcon) ?>" class="av-rank-mini" alt=""><div><div class="av-stat-lbl">Rank</div><div class="av-stat-val"><?= $h($rankText) ?></div></div></div><div class="av-stat-item"><i class="fa-solid fa-globe av-stat-ico"></i><div><div class="av-stat-lbl">Server</div><div class="av-stat-val"><?= $h($pkg_server ?: '—') ?></div></div></div><div class="av-stat-item"><i class="fa-solid fa-euro-sign av-stat-ico"></i><div><div class="av-stat-lbl">Price</div><div class="av-stat-val"><?= $pkg_price > 0 ? number_format($pkg_price, 2).' €' : '—' ?></div></div></div><div class="av-stat-item"><i class="fa-solid fa-id-card av-stat-ico"></i><div><div class="av-stat-lbl">Account ID</div><div class="av-stat-val">#<?= $account_id ?></div></div></div><div class="av-stat-item"><i class="fa-solid fa-circle-info av-stat-ico"></i><div><div class="av-stat-lbl">Status</div><div class="av-stat-val"><?= $h($cur['label']) ?></div></div></div><div class="av-stat-item"><i class="fa-solid fa-clock av-stat-ico"></i><div><div class="av-stat-lbl">Sold At</div><div class="av-stat-val"><?= $isSold ? $h($buyerDate) : '—' ?></div></div></div></div></div>

    <div class="av-sidebar-card mb-3"><div class="av-sc-header"><span class="av-sc-icon" style="background:rgba(255,255,255,.06);"><i class="fa-solid fa-bolt"></i></span><span class="av-sc-title">Quick Actions</span></div><div style="padding:12px;display:grid;gap:8px;"><a href="<?= ADMN_URL ?>/account-package/<?= (int)($account['package_id'] ?? 0) ?>" class="av-btn-ghost" style="justify-content:center;"><i class="fa-duotone fa-box"></i> Package</a><a href="<?= ADMN_URL ?>/account-packages" class="av-btn-ghost" style="justify-content:center;"><i class="fa-duotone fa-list"></i> All Packages</a><?php if ($client): ?><button type="button" class="av-btn-primary js-admin-poke-client" data-ref-type="premium_account" data-id="<?= $account_id ?>" style="justify-content:center;"><i class="fa-duotone fa-hand-point-up"></i> Poke Client</button><a href="<?= ADMN_URL ?>/client/<?= (int)($client['id'] ?? 0) ?>" class="av-btn-ghost" style="justify-content:center;"><i class="fa-duotone fa-user"></i> Buyer Profile</a><?php endif ?></div></div>
  </div>
</div>

</div><!-- .admin-prem-chat -->

<script>
(function () {

  /* Admin Poke Client */
  document.addEventListener('click', function (e) {
    var pokeBtn = e.target.closest('.js-admin-poke-client');
    if (!pokeBtn) return;
    e.preventDefault();
    if (pokeBtn.disabled) return;
    if (!confirm('Poke this client now?')) return;

    var oldHtml = pokeBtn.innerHTML;
    pokeBtn.disabled = true;
    pokeBtn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Poking...';

    $.post(AJAX_URL, {
      action: 'admin_poke_client',
      ref_type: pokeBtn.getAttribute('data-ref-type') || 'premium_account',
      id: pokeBtn.getAttribute('data-id') || <?= $account_id ?>
    }, function (resp) {
      var r = resp;
      try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (err) {}
      if (window.sendToast && r && r.sendToast) {
        window.sendToast(r.sendToast.type || 'primary', r.sendToast.title || '', r.sendToast.message || '');
      } else if (r && r.message) {
        alert(r.message);
      } else if (r && r.sendToast && r.sendToast.message) {
        alert(r.sendToast.message);
      }
      if (window.playSound && r && r.playSound) {
        window.playSound(r.playSound);
      }
    }).fail(function () {
      alert('Poke failed. Please try again.');
    }).always(function () {
      pokeBtn.disabled = false;
      pokeBtn.innerHTML = oldHtml;
    });
  });

  /* Credential copy */
  document.addEventListener('click', function (e) {
    var target = e.target.closest('.js-copy-cred, .js-copy-btn');
    if (!target) return;
    var value = target.getAttribute('data-copy') || '';
    if (!value) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).then(function () {
        var row = target.closest('.av-cred-item');
        if (row) {
          row.classList.add('is-copied');
          setTimeout(function () { row.classList.remove('is-copied'); }, 1200);
        }
      });
    }
  });

  /* ── Password toggle ── */
  var field = document.getElementById('passwordField');
  var btn   = document.getElementById('togglePw');
  var ico   = document.getElementById('togglePwIcon');
  if (btn && field) {
    btn.addEventListener('click', function () {
      var isText = field.type === 'text';
      field.type = isText ? 'password' : 'text';
      ico.className = isText ? 'fa-duotone fa-eye' : 'fa-duotone fa-eye-slash';
    });
  }

  /* ── Status preview ── */
  var sel     = document.getElementById('statusSelect');
  var preview = document.getElementById('statusPreview');
  var STATUS  = {
    0: { cls:'si-available', icon:'fa-circle-check',          label:'Available' },
    1: { cls:'si-sold',      icon:'fa-circle-xmark',          label:'Sold' },
    2: { cls:'si-banned',    icon:'fa-ban',                   label:'Banned' },
    3: { cls:'si-cashflow',  icon:'fa-rotate',                label:'Cashflow' },
    4: { cls:'si-level',     icon:'fa-triangle-exclamation',  label:'Level not matching' },
    5: { cls:'si-login',     icon:'fa-key',                   label:'Logins not working' },
  };
  if (sel && preview) {
    sel.addEventListener('change', function () {
      var s = STATUS[this.value] || STATUS[0];
      preview.className = 'status-indicator ' + s.cls;
      preview.innerHTML = '<i class="fa-duotone ' + s.icon + '" style="font-size:.75rem;"></i> ' + s.label;
    });
  }

  /* ── Chat ── */
  const chatEl    = document.getElementById('adminPremChatMessages');
  if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;

  const chatForm   = document.getElementById('adminPremChatForm');
  const chatInput  = document.getElementById('adminPremChatInput');
  const fileInput  = document.getElementById('adminPremChatFile');
  const attachBtn  = document.getElementById('adminPremAttachBtn');
  const preview2   = document.getElementById('adminPremChatPreview');
  const previewImg = document.getElementById('adminPremChatPreviewImg');
  const prevName   = document.getElementById('adminPremChatPreviewName');
  const removeBtn  = document.getElementById('adminPremChatRemoveBtn');
  const errBox     = document.getElementById('adminPremChatError');
  const sendBtn    = document.getElementById('adminPremSendBtn');
  const ACCOUNT_ID = <?= $account_id ?>;
  const AJAX_URL   = '<?= AJAX_URL ?>';
  let   previewUrl = null;
  let   lastTs     = <?= time() ?>;

  function setErr(msg) {
    if (!errBox) return;
    if (!msg) { errBox.classList.add('d-none'); errBox.textContent = ''; return; }
    errBox.textContent = msg; errBox.classList.remove('d-none');
  }
  function clearFile() {
    if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
    if (fileInput)  fileInput.value = '';
    if (preview2)   preview2.classList.add('d-none');
    if (previewImg) previewImg.src = '';
    if (prevName)   prevName.textContent = '';
  }
  function showFile(file) {
    if (!file) return clearFile();
    if (!/^image\/(png|jpe?g|gif)$/i.test(file.type)) { setErr('Only PNG/JPG/JPEG/GIF allowed.'); clearFile(); return; }
    setErr('');
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    if (previewImg) previewImg.src = previewUrl;
    if (prevName)   prevName.textContent = file.name || 'image';
    if (preview2)   preview2.classList.remove('d-none');
  }

  if (attachBtn && fileInput) {
    attachBtn.addEventListener('click', () => { setErr(''); fileInput.click(); });
    fileInput.addEventListener('change', () => showFile(fileInput.files && fileInput.files[0]));
  }
  if (removeBtn) removeBtn.addEventListener('click', () => { setErr(''); clearFile(); });

  document.addEventListener('paste', function (e) {
    if (!chatForm || !fileInput) return;
    const act = document.activeElement;
    if (!chatForm.contains(act) && act !== chatInput) return;
    const items = e.clipboardData ? e.clipboardData.items : [];
    for (const it of items) {
      if (it && it.type && it.type.indexOf('image/') === 0) {
        const blob = it.getAsFile();
        if (!blob) continue;
        const f = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });
        const dt = new DataTransfer(); dt.items.add(f); fileInput.files = dt.files;
        showFile(f); e.preventDefault(); break;
      }
    }
  });

  // Render single message (for live poll)
  function renderMsg(msg) {
    var sender = (msg.sender || msg.sender_type || 'admin').toLowerCase();
    var isRight = (sender === 'admin');
    var text   = msg.message || msg.content || '';
    var icon   = msg.sender_icon || '';
    var name   = msg.sender_name || (isRight ? 'Admin' : 'Client');
    var ts     = msg.time ? new Date(msg.time * 1000).toLocaleString('de-DE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '';
    var badgeCls   = isRight ? 'lb-badge--admin' : 'lb-badge--client';
    var badgeLabel = isRight ? 'Admin' : 'Client';
    var initial    = name.charAt(0).toUpperCase();

    if (sender === 'system') {
      return '<div class="lb-syswrap"><div class="lb-sys">' + _e(text) + '</div>' + (ts ? '<div class="lb-sys-time">' + ts + '</div>' : '') + '</div>';
    }
    var avi = icon
      ? '<img class="lb-msg__avatar" src="' + _e(icon) + '" alt="">'
      : '<span class="lb-msg__avatar" style="background:' + (isRight ? 'rgba(99,102,241,.3);color:#a5b4fc' : 'rgba(74,222,128,.2);color:#4ade80') + ';display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;">' + initial + '</span>';

    var bodyHtml = /\.(png|jpe?g|gif|webp)(\?.*)?$/i.test(text)
      ? '<img src="' + _e(text) + '" style="max-width:260px;max-height:200px;border-radius:.5rem;display:block;cursor:pointer;" onclick="window.open(this.src,\'_blank\')">'
      : _e(text).replace(/\n/g, '<br>');

    return '<div class="lb-msg ' + (isRight ? 'lb-msg--end' : 'lb-msg--start') + '">'
      + '<div class="lb-msg__head' + (isRight ? ' lb-msg__head--end' : '') + '">'
      +   avi
      +   '<div><div style="display:flex;align-items:center;gap:.3rem;' + (isRight ? 'flex-direction:row-reverse;' : '') + '"><span class="lb-msg__name">' + _e(name) + '</span><span class="lb-badge ' + badgeCls + '">' + badgeLabel + '</span></div><span class="lb-msg__time">' + ts + '</span></div>'
      + '</div>'
      + '<div class="lb-msg__bubble">' + bodyHtml + '</div>'
      + '</div>';
  }

  function _e(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function scrollBottom() { if (chatEl) chatEl.scrollTop = chatEl.scrollHeight; }

  // Poll for new messages from client
  function pollChat() {
    $.post(AJAX_URL, { action: 'admin_premium_account_chat_load', account_id: ACCOUNT_ID, last_ts: lastTs }, function(resp) {
      var r;
      try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){ return; }
      if (!r.success || !r.messages || !r.messages.length) return;
      var had_empty = chatEl && chatEl.querySelector('.lb-chat-empty');
      if (had_empty) chatEl.innerHTML = '';
      r.messages.forEach(function(msg) {
        if (msg.time) lastTs = Math.max(lastTs, parseInt(msg.time, 10));
        if (chatEl) chatEl.insertAdjacentHTML('beforeend', renderMsg(msg));
      });
      scrollBottom();
    });
  }
  window.lbOrderViewChatUpdate = function (data) {
    if (!data || data.order_id === ('premacct_' + ACCOUNT_ID)) {
      pollChat();
    }
  };

  setInterval(function () {
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected) return;
    pollChat();
  }, 6000);

  setInterval(function () {
    if (document.visibilityState === 'visible' && window.lbRealtimeConnected) return;
    pollChat();
  }, 60000);

  // Submit
  if (chatForm) {
    chatForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      var msg     = chatInput ? chatInput.value.trim() : '';
      var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
      if (!msg && !hasFile) { setErr('Please type a message or attach an image.'); return; }
      setErr('');
      if (sendBtn) {
        sendBtn.disabled = true;
        const prog = sendBtn.querySelector('.indicator-progress');
        if (prog) prog.classList.remove('d-none');
      }
      const fd = new FormData(chatForm);
      try {
        const res  = await fetch(AJAX_URL, { method:'POST', body:fd, credentials:'same-origin' });
        const json = await res.json().catch(() => ({}));
        if (chatInput) chatInput.value = '';
        clearFile();
        if (json.success || res.ok) {
          window.location.reload();
          return;
        }
      } catch(err) {
        setErr('Upload failed. Please try again.');
        console.error(err);
      } finally {
        if (sendBtn) {
          sendBtn.disabled = false;
          const prog = sendBtn.querySelector('.indicator-progress');
          if (prog) prog.classList.add('d-none');
        }
      }
    });
  }

  scrollBottom();
})();
</script>
