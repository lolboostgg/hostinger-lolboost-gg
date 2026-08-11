<?= $this->layout('client/layouts/main', ['meta' => $meta ?? [
    'title' => 'Account Details | LoLBoost.gg',
    'h1' => 'Account Details',
    'description' => 'View your purchased account details and chat with support.',
]]) ?>

<?php
/**
 * Client: Premium Account View (accounts table)
 * Ranked account styled view with account details and support chat
 * Variables: $account, $package, $chat_messages, $meta
 */

$account_id  = (int)($account['id'] ?? 0);
$package     = $package ?? null;
$decode_html = function($v) {
    $s = (string)($v ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $s) break;
        $s = $decoded;
    }
    return $s;
};
$h           = fn($v) => htmlspecialchars($decode_html($v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$login       = (string)($account['login']    ?? '');
$password    = (string)($account['password'] ?? '');
$data_field  = (string)($account['data']     ?? '');

$pkg_name    = (string)($package['name']   ?? ($account['name'] ?? ('Account #' . $account_id)));
$pkg_server  = strtoupper((string)($package['server'] ?? ($account['server'] ?? '')));
$pkg_icon    = (string)($package['icon']   ?? ($account['icon'] ?? ''));

$status_map = [
    0 => ['label' => 'Available',          'cls' => 'av-status--purchased'],
    1 => ['label' => 'Sold / Assigned',    'cls' => 'av-status--purchased'],
    2 => ['label' => 'Banned',             'cls' => 'av-status--danger'],
    3 => ['label' => 'Cashflow',           'cls' => 'av-status--info'],
    4 => ['label' => 'Level not matching', 'cls' => 'av-status--warning'],
    5 => ['label' => 'Logins not working', 'cls' => 'av-status--warning'],
];
$status_raw  = (int)($account['status'] ?? 0);
$status_info = $status_map[$status_raw] ?? ['label' => 'Unknown', 'cls' => 'av-status--info'];

$client_data    = defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? CLIENT_DATA : [];
$client_icon    = (string)($client_data['icon'] ?? '');
$client_name    = (string)($client_data['username'] ?? 'You');
$client_id_safe = (int)(defined('CLIENT_ID') ? CLIENT_ID : 0);
$created_at     = (string)($account['sold_at'] ?? $account['created_at'] ?? '');
$created_label  = $created_at !== '' ? date('d.m.Y', strtotime($created_at)) : '';

$game = strtolower((string)($account['game'] ?? $package['game'] ?? 'lol'));
if (!in_array($game, ['lol', 'val'], true)) $game = 'lol';
$isValorant = ($game === 'val');

$lol_rank_labels = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
$lol_div_labels  = ['','IV','III','II','I'];
$rank_index = $isValorant ? (int)($account['rank'] ?? 0) : (int)($account['current_rank'] ?? $account['rank'] ?? 0);
if ($isValorant) {
    $rank_label = trim((string)($account['rank_label'] ?? ''));
    if ($rank_label === '') {
        $rank_label = function_exists('util_get_val_rank') ? (string)util_get_val_rank($rank_index) : 'Unranked';
    }
    $div_label = '';
} else {
    $rank_label = $lol_rank_labels[$rank_index] ?? (function_exists('util_get_lol_rank') ? (string)util_get_lol_rank($rank_index) : 'Unranked');
    $div_index = (int)($account['current_division'] ?? 0);
    $div_label = ($rank_index === 0 || $rank_index >= 8) ? '' : ($lol_div_labels[$div_index] ?? '');
}
$rank_full_label = trim($rank_label . ($div_label !== '' ? ' ' . $div_label : ''));
$rank_icon_src = '';
if (function_exists('util_rank_img')) {
    $rank_icon_src = util_rank_img($isValorant ? 'val' : 'lol', 'mini', $rank_index);
} elseif (defined('ASSET_URL')) {
    $rank_icon_src = ASSET_URL . '/core/main/img/lol/ranks/mini/' . max(0, $rank_index) . '.png';
}
$price_raw = $account['price'] ?? $package['price'] ?? null;
$price_label = is_numeric($price_raw) ? number_format(((int)$price_raw) / 100, 2) . ' €' : '';

$account_email_raw = trim(str_replace("\xC2\xA0", ' ', (string)($account['email'] ?? '')));
// Premium accounts usually have no dedicated email column — the credentials live in one
// free-text blob ("... email: microbicjah@yopmail.com birth date: ..."). Pull the address
// out of there so the inbox tutorial shows up for these accounts too.
if ($account_email_raw === '' || strpos($account_email_raw, '@') === false) {
    $account_data_blob = html_entity_decode((string)($account['data'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($account_data_blob !== '' && preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', $account_data_blob, $m)) {
        $account_email_raw = trim(rtrim($m[0], '.,;:'));
    }
}
$account_email_lc  = strtolower($account_email_raw);
$is_unverified_account_email = ($account_email_raw === '' || $account_email_lc === 'unverified');
$has_valid_account_email = (!$is_unverified_account_email && strpos($account_email_raw, '@') !== false);
$account_email_domain = $has_valid_account_email ? strtolower(substr(strrchr($account_email_raw, '@'), 1)) : '';
$inboxes_domains = [
    'blondmail.com', 'chapsmail.com', 'clowmail.com', 'dropjar.com', 'fivermail.com',
    'getairmail.com', 'getmule.com', 'getnada.com', 'gjmpmail.com', 'gjvmail.com',
    'guysmail.com', 'inboxbear.com', 'replyloop.com', 'robot-mail.com', 'spicysoda.com',
    'tafmail.com', 'temptami.com', 'tupmail.com', 'vomoto.com',
];
$email_tutorial_type = '';
$email_tutorial_url = '';
$email_tutorial_title = '';
if ($has_valid_account_email && $account_email_domain !== '') {
    if (in_array($account_email_domain, $inboxes_domains, true)) {
        $email_tutorial_type = 'inboxes';
        $email_tutorial_url = 'https://inboxes.com/';
        $email_tutorial_title = 'How to access this email inbox';
    } elseif ($account_email_domain === 'yopmail.com') {
        $email_tutorial_type = 'yopmail';
        $email_tutorial_url = 'https://yopmail.com/';
        $email_tutorial_title = 'How to access this YOPmail inbox';
    }
}
$email_password_raw = trim(str_replace("\xC2\xA0", ' ', (string)($account['email_password'] ?? '')));
$email_password_lc = strtolower($email_password_raw);
$email_password_compact = preg_replace('/[^a-z0-9]+/i', '', $email_password_lc);
$missing_email_password_values = ['', '-', 'no', 'no password', 'none', 'null', 'unverified', 'n/a', 'na', 'not provided', 'not available', 'no email password'];
$missing_email_password_compact_values = ['', 'no', 'nopassword', 'none', 'null', 'unverified', 'na', 'notprovided', 'notavailable', 'noemailpassword'];
$has_email_password = !(strlen($email_password_raw) < 5 || in_array($email_password_lc, $missing_email_password_values, true) || in_array($email_password_compact, $missing_email_password_compact_values, true) || strpos($email_password_lc, 'no password') !== false);
$show_email_change_notice = ($has_valid_account_email && $email_tutorial_type === '' && !$has_email_password);

?>

<?= $this->start('styles') ?>
<style>
.client-account-view .card {
  background: var(--bs-card-bg) !important;
  border: var(--bs-card-border-width) solid var(--bs-card-border-color) !important;
  border-radius: 22px !important;
  box-shadow: none !important;
}
.client-account-view .card::before { display: none !important; }
.client-account-view .order-chat-card { overflow: hidden; }

.av-head {
  border-radius: 22px;
  overflow: hidden;
  margin-bottom: 20px;
  border: 1px solid var(--bs-card-border-color);
  background: #25282a;
}
.av-head-body {
  padding: 20px 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  border-bottom: 1px solid var(--bs-card-border-color);
}
.av-head-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: rgba(109,92,255,.12);
  border: 1px solid rgba(109,92,255,.22);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.av-head-icon img { width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 1px 3px rgba(0,0,0,.5)); }
.av-head-title { font-size: 1.25rem; font-weight: 950; color: rgba(255,255,255,.92); margin: 0; line-height: 1.2; }
.av-head-sub { font-size: .8rem; color: rgba(255,255,255,.4); margin-top: 4px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.av-status { display: inline-flex; align-items: center; gap: .35rem; padding: 4px 11px; border-radius: 99px; font-size: .75rem; font-weight: 800; }
.av-status--purchased { background: rgba(99,102,241,.14); border: 1px solid rgba(99,102,241,.28); color: #a5b4fc; }
.av-status--danger { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25); color: #f87171; }
.av-status--warning { background: rgba(251,191,36,.12); border: 1px solid rgba(251,191,36,.24); color: #fbbf24; }
.av-status--info { background: rgba(56,189,248,.12); border: 1px solid rgba(56,189,248,.24); color: #7dd3fc; }
.av-meta-row { display: flex; flex-wrap: wrap; gap: 6px; padding: 14px 22px 16px; }
.av-meta-pill { display: inline-flex; align-items: center; gap: .3rem; padding: 4px 11px; border-radius: 99px; font-size: .75rem; font-weight: 700; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); color: rgba(255,255,255,.7); }
.av-meta-pill strong { color: rgba(255,255,255,.92); }
.av-btn-ghost { display: inline-flex; align-items: center; gap: .4rem; padding: 7px 14px; border-radius: 11px; font-size: .83rem; font-weight: 700; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09); color: rgba(255,255,255,.7); cursor: pointer; transition: background .12s; text-decoration: none; }
.av-btn-ghost:hover { background: rgba(255,255,255,.09); color: #fff; }

.av-btn-danger { display: inline-flex; align-items: center; gap: .4rem; padding: 7px 14px; border-radius: 11px; font-size: .83rem; font-weight: 700; background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171; cursor: pointer; transition: background .12s; text-decoration: none; }
.av-btn-danger:hover { background: rgba(239,68,68,.16); color: #fca5a5; }
.av-head-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
.av-btn-primary { display: inline-flex; align-items: center; gap: .4rem; padding: 7px 14px; border-radius: 11px; font-size: .83rem; font-weight: 700; background: rgba(99,102,241,.10); border: 1px solid rgba(99,102,241,.28); color: #c7d2fe; cursor: pointer; transition: background .12s, color .12s, border-color .12s; text-decoration: none; }
.av-btn-primary:hover { background: rgba(99,102,241,.18); border-color: rgba(129,140,248,.42); color: #eef2ff; }
.av-btn-primary i { color:#a5b4fc; font-size:.9rem; }
@media (max-width: 576px) {
  .av-head-actions { width:100%; justify-content:stretch; }
  .av-head-actions > * { flex:1 1 calc(50% - 4px); justify-content:center; }
  .av-head-actions > a.av-btn-ghost { flex-basis:100%; }
}

.av-rank-pill-img { width: 18px; height: 18px; object-fit: contain; filter: drop-shadow(0 1px 2px rgba(0,0,0,.45)); }
.av-email-tutorial { border-radius: 18px; border: 1px solid rgba(99,102,241,.24); background: linear-gradient(135deg, rgba(99,102,241,.13), rgba(124,58,237,.08)); overflow: hidden; box-shadow: 0 18px 38px rgba(0,0,0,.16); }
.av-email-tutorial--notice { border-color: rgba(99,102,241,.30); background: linear-gradient(135deg, rgba(99,102,241,.14), rgba(124,58,237,.08)); }
.av-email-tutorial__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.025); }
.av-email-tutorial__title { display: flex; align-items: center; gap: 9px; font-size: .95rem; font-weight: 950; color: rgba(255,255,255,.92); }
.av-email-tutorial__title i { width: 28px; height: 28px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; background: rgba(99,102,241,.18); color: #a5b4fc; border: 1px solid rgba(99,102,241,.28); }
.av-email-tutorial__sub { margin-top: 4px; font-size: .78rem; color: rgba(255,255,255,.48); line-height: 1.45; }
.av-email-tutorial__body { padding: 14px 18px 16px; }
.av-email-tutorial__email { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 9px 11px; border-radius: 12px; background: rgba(0,0,0,.18); border: 1px solid rgba(255,255,255,.07); font-size: .78rem; color: rgba(255,255,255,.55); margin-bottom: 12px; }
.av-email-tutorial__email code { color: rgba(255,255,255,.9); font-size: .8rem; background: transparent; padding: 0; }
.av-email-tutorial__steps { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.av-email-tutorial__step { display: flex; gap: 10px; align-items: flex-start; }
.av-email-tutorial__num { width: 22px; height: 22px; border-radius: 99px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; background: rgba(99,102,241,.16); border: 1px solid rgba(99,102,241,.28); color: #c4b5fd; font-size: .7rem; font-weight: 900; }
.av-email-tutorial__text { font-size: .8rem; color: rgba(255,255,255,.65); line-height: 1.45; }
.av-email-tutorial__text strong { color: rgba(255,255,255,.9); }
.av-email-tutorial__link { display: inline-flex; align-items: center; gap: .4rem; margin-top: 12px; padding: 7px 12px; border-radius: 10px; background: rgba(99,102,241,.16); border: 1px solid rgba(99,102,241,.28); color: #c4b5fd; text-decoration: none; font-size: .78rem; font-weight: 800; }
.av-email-tutorial__link:hover { background: rgba(99,102,241,.24); color: #fff; }
.lb-msg__stamp { font-size:.7rem; opacity:.48; margin-top:.2rem; }
.lb-msg--end .lb-msg__stamp { text-align:right; }
.lb-msg__ticks { margin-left:.28rem; white-space:nowrap; }
.lb-msg__ticks.is-read { color:#8b7cff; }
.lb-msg__ticks.is-delivered { color:rgba(255,255,255,.34); }
.rp-modal-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.65); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; opacity: 0; pointer-events: none; transition: opacity .2s; }
.rp-modal-overlay.is-open { opacity: 1; pointer-events: all; }
.rp-modal { width: 100%; max-width: 480px; background: #1e2022; border: 1px solid rgba(255,255,255,.1); border-radius: 20px; overflow: hidden; transform: translateY(16px) scale(.97); transition: transform .2s; box-shadow: 0 24px 60px rgba(0,0,0,.5); }
.rp-modal-overlay.is-open .rp-modal { transform: translateY(0) scale(1); }
.rp-modal-header { display: flex; align-items: center; gap: 10px; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.07); background: rgba(255,255,255,.02); }
.rp-modal-icon { width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0; background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.22); display: flex; align-items: center; justify-content: center; font-size: .8rem; color: #f87171; }
.rp-modal-title { font-size: .95rem; font-weight: 900; color: rgba(255,255,255,.9); flex: 1; }
.rp-modal-close { background: none; border: none; color: rgba(255,255,255,.3); cursor: pointer; padding: 4px; border-radius: 6px; transition: color .12s, background .12s; line-height: 1; }
.rp-modal-close:hover { color: #fff; background: rgba(255,255,255,.06); }
.rp-modal-body { padding: 20px; }
.rp-label { font-size: .72rem; font-weight: 800; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
.rp-problems { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.rp-problem-opt { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 11px; border: 1px solid rgba(255,255,255,.07); background: rgba(255,255,255,.03); cursor: pointer; transition: border-color .12s, background .12s; }
.rp-problem-opt:hover { border-color: rgba(239,68,68,.3); background: rgba(239,68,68,.05); }
.rp-problem-opt.is-selected { border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.1); }
.rp-problem-opt input[type="radio"] { display: none; }
.rp-problem-ico { font-size: .8rem; width: 16px; text-align: center; color: rgba(255,255,255,.35); flex-shrink: 0; }
.rp-problem-opt.is-selected .rp-problem-ico { color: #f87171; }
.rp-problem-text { font-size: .82rem; font-weight: 700; color: rgba(255,255,255,.65); flex: 1; }
.rp-problem-opt.is-selected .rp-problem-text { color: rgba(255,255,255,.9); }
.rp-problem-check { width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.15); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .6rem; color: transparent; transition: all .12s; }
.rp-problem-opt.is-selected .rp-problem-check { border-color: #f87171; background: #f87171; color: #fff; }
.rp-details { width: 100%; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09); border-radius: 11px; color: rgba(255,255,255,.85); font-size: .83rem; padding: 10px 12px; resize: none; outline: none; transition: border-color .12s; font-family: inherit; }
.rp-details:focus { border-color: rgba(239,68,68,.4); }
.rp-modal-footer { padding: 0 20px 20px; display: flex; gap: 8px; justify-content: flex-end; }
.rp-submit { display: inline-flex; align-items: center; gap: .4rem; padding: 9px 20px; border-radius: 11px; font-size: .84rem; font-weight: 800; background: rgba(239,68,68,.18); border: 1px solid rgba(239,68,68,.3); color: #f87171; cursor: pointer; transition: background .12s; }
.rp-submit:hover:not(:disabled) { background: rgba(239,68,68,.28); }
.rp-submit:disabled { opacity: .45; cursor: not-allowed; }
.rp-cancel { display: inline-flex; align-items: center; padding: 9px 16px; border-radius: 11px; font-size: .84rem; font-weight: 700; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09); color: rgba(255,255,255,.6); cursor: pointer; transition: background .12s; }
.rp-cancel:hover { background: rgba(255,255,255,.08); }
.rp-success { text-align: center; padding: 28px 20px; }
.rp-success-ico { font-size: 2rem; margin-bottom: 10px; }
.rp-success-title { font-size: 1rem; font-weight: 900; color: rgba(255,255,255,.9); margin-bottom: 4px; }
.rp-success-sub { font-size: .8rem; color: rgba(255,255,255,.4); }

.av-sidebar-card { border-radius: 18px; border: 1px solid rgba(255,255,255,.07); background: #25282a; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.2); }
.av-sc-header { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.02); }
.av-sc-icon { width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.1); font-size: .75rem; }
.av-sc-title { font-size: .8rem; font-weight: 900; color: rgba(255,255,255,.75); text-transform: uppercase; letter-spacing: .06em; flex: 1; }
.av-creds-list { padding: 4px 0 6px; }
.av-cred-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,.04); overflow: visible; }
.av-cred-item:last-child { border-bottom: 0; }
.av-cred-left { display: flex; align-items: center; gap: 7px; min-width: 100px; }
.av-cred-ico { font-size: .65rem; color: rgba(255,255,255,.22); width: 12px; text-align: center; flex-shrink: 0; }
.av-cred-lbl { font-size: .72rem; font-weight: 700; color: rgba(255,255,255,.38); white-space: nowrap; }
.av-cred-right { display: flex; align-items: flex-start; gap: 5px; min-width: 0; flex: 1 1 auto; justify-content: flex-end; overflow: visible; }
.av-cred-val { font-family: monospace; font-size: .77rem; font-weight: 700; color: rgba(255,255,255,.82); max-width: none; overflow: visible; text-overflow: clip; white-space: normal; overflow-wrap: anywhere; word-break: break-word; cursor: pointer; text-align: right; line-height: 1.35; }
.av-copy-btn { background: none; border: none; color: rgba(255,255,255,.2); cursor: pointer; padding: 2px 3px; transition: color .12s; line-height: 1; flex-shrink: 0; }
.av-copy-btn:hover { color: #9f8cff; }
.av-copy-ok { font-size: .68rem; color: #4ade80; font-weight: 800; display: none; }
.av-cred-item.copied .av-copy-ok { display: inline; }

.av-tip-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,.04); }
.av-tip-item:last-child { border-bottom: 0; }
.av-tip-ico { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .75rem; }
.av-tip-title { font-size: .8rem; font-weight: 800; color: rgba(255,255,255,.85); margin-bottom: 1px; }
.av-tip-desc { font-size: .73rem; color: rgba(255,255,255,.38); line-height: 1.4; }

.av-chat-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--bs-card-border-color); }
.av-chat-title { font-size: .95rem; font-weight: 900; color: rgba(255,255,255,.9); display: flex; align-items: center; gap: .5rem; }
#pa_chat_messages { min-height: 300px; max-height: 480px; overflow-y: auto; padding: 1rem 1.25rem; display: flex; flex-direction: column; scroll-behavior: smooth; }
#pa_chat_messages::-webkit-scrollbar { width: 5px; }
#pa_chat_messages::-webkit-scrollbar-track { background: transparent; }
#pa_chat_messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 3px; }
.lb-msg { display: flex; flex-direction: column; margin-bottom: .5rem; max-width: 75%; }
.lb-msg--start { align-self: flex-start; }
.lb-msg--end { align-self: flex-end; }
.lb-msg__head { display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
.lb-msg__head--end { flex-direction: row-reverse; }
.lb-msg__avatar { width: 1.75rem; height: 1.75rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.lb-msg__name { font-weight: 700; font-size: .8rem; line-height: 1.3; display: flex; align-items: center; gap: .3rem; }
.lb-msg__time { font-size: .72rem; opacity: .45; }
.lb-msg__bubble { padding: .55rem .85rem; border-radius: .75rem; font-size: .875rem; line-height: 1.55; word-break: break-word; background: rgba(255,255,255,.07); position: relative; color: rgba(255,255,255,.88); }
.lb-msg--end .lb-msg__bubble { background: rgba(99,102,241,.22); }
.lb-badge { display: inline-flex; align-items: center; padding: .1rem .4rem; border-radius: 999px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.lb-badge--admin { background: rgba(245,158,11,.15); color: #f59e0b; }
.lb-badge--client { background: rgba(16,185,129,.15); color: #10b981; }
.lb-badge--system { background: rgba(107,114,128,.15); color: #9ca3af; }
.lb-syswrap { width: 100%; margin: .75rem 0; }
/* Sized like the order-view chat — this bubble was noticeably larger than the rest. */
.lb-sys { display: block; width: 100%; background: rgba(109,76,255,.10); border: 1px dashed rgba(173,140,255,.35); border-radius: 14px; padding: 10px 14px; font-size: .86rem; font-weight: 800; line-height: 1.5; color: rgba(255,255,255,.92); text-align: left; box-shadow: inset 0 1px 0 rgba(255,255,255,.03); }
.lb-sys-time { font-size: .72rem; opacity: .55; margin-top: .35rem; padding-left: 2px; text-align: left; }
.lb-chat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 240px; opacity: .4; gap: .5rem; text-align: center; }
.lb-chat-preview { display: inline-flex; align-items: center; gap: .5rem; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: .5rem; padding: .4rem .7rem; }
.lb-chat-preview img { width: 2.5rem; height: 2.5rem; object-fit: cover; border-radius: .35rem; }
.lb-chat-preview__remove { background: none; border: none; color: rgba(255,255,255,.5); cursor: pointer; padding: 0 .2rem; }
.pa-chat-error { font-size: .78rem; color: #f87171; margin-top: 5px; display: none; }
@media (max-width: 768px) {
  .av-head-body { padding: 14px 16px; }
  .av-meta-row { padding: 10px 16px 12px; }
  #pa_chat_messages { min-height: 220px; max-height: 340px; }
  .lb-msg { max-width: 88%; }
}
/* Never truncate credentials */
.av-cred-val { overflow: visible !important; text-overflow: clip !important; white-space: normal !important; overflow-wrap: anywhere !important; word-break: break-word !important; max-width: none !important; }
@media (max-width: 576px) {
  .av-cred-item { flex-direction: column; align-items: stretch; }
  .av-cred-right { justify-content: flex-start; }
  .av-cred-val { text-align: left; }
}
</style>
<?= $this->end() ?>

<div class="client-account-view">

  <div class="av-head mb-4">
    <div class="av-head-body">
      <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
        <div class="av-head-icon">
          <?php if (!empty($rank_icon_src)): ?>
            <img src="<?= $h($rank_icon_src) ?>" alt="<?= $h($rank_full_label) ?>">
          <?php elseif (!empty($pkg_icon)): ?>
            <img src="<?= $h($pkg_icon) ?>" alt="">
          <?php else: ?>
            <i class="fa-duotone fa-gamepad-modern" style="font-size:1.35rem;color:#a5b4fc;"></i>
          <?php endif ?>
        </div>
        <div style="min-width:0;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <h1 class="av-head-title"><?= $h($pkg_name) ?></h1>
            <span class="av-status av-status--purchased"><i class="fa-solid fa-bag-shopping" style="font-size:.6rem;"></i> Purchased</span>
          </div>
          <div class="av-head-sub">
            <?php if ($pkg_server !== ''): ?><span style="text-transform:uppercase;font-weight:700;"><?= $h($pkg_server) ?></span><span>·</span><?php endif ?>
            <span>#<?= $account_id ?></span>
            <?php if ($created_label !== ''): ?><span>·</span><span><?= $h($created_label) ?></span><?php endif ?>
            <span>·</span><span style="font-weight:700;"><i class="fa-duotone fa-headset me-1"></i>lolboost.gg</span>
          </div>
        </div>
      </div>
      <div class="av-head-actions">
        <button type="button" class="av-btn-danger" id="reportProblemBtn"><i class="fa-solid fa-flag"></i> Report a Problem</button>
        <button type="button" class="av-btn-primary js-client-poke-seller" data-ref-type="premium_account" data-id="<?= $account_id ?>"><i class="fa-solid fa-hand-point-up"></i> Poke Seller</button>
        <a href="<?= BASE_URL ?>/profile/orders" class="av-btn-ghost"><i class="fa-duotone fa-arrow-left"></i> Back</a>
      </div>
    </div>
    <div class="av-meta-row">
      <span class="av-meta-pill">
        <?php if (!empty($rank_icon_src)): ?><img class="av-rank-pill-img" src="<?= $h($rank_icon_src) ?>" alt=""><?php else: ?><i class="fa-solid fa-trophy" style="color:rgba(255,255,255,.4);"></i><?php endif ?>
        <strong><?= $h($rank_full_label ?: 'Unranked') ?></strong>
      </span>
      <?php if ($price_label !== ''): ?><span class="av-meta-pill"><i class="fa-solid fa-euro-sign" style="color:rgba(255,255,255,.4);"></i> <strong><?= $h($price_label) ?></strong></span><?php endif ?>
      <?php if (!empty($account['level'])): ?><span class="av-meta-pill"><i class="fa-solid fa-arrow-up" style="color:rgba(255,255,255,.4);"></i> <strong>Lvl <?= $h($account['level']) ?></strong></span><?php endif ?>
    </div>
  </div>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-lg-7">

      <?php if ($email_tutorial_type !== ''): ?>
      <div class="av-email-tutorial mb-4">
        <div class="av-email-tutorial__head">
          <div>
            <div class="av-email-tutorial__title"><i class="fa-solid fa-envelope-open-text"></i><?= $h($email_tutorial_title) ?></div>
            <div class="av-email-tutorial__sub">This account uses a temporary email provider. Follow these steps to open the inbox and receive verification emails.</div>
          </div>
        </div>
        <div class="av-email-tutorial__body">
          <div class="av-email-tutorial__email">
            <span>Account email:</span>
            <code><?= $h($account_email_raw) ?></code>
            <button type="button" class="av-copy-btn js-pa-copy-email" data-value="<?= $h($account_email_raw) ?>" aria-label="Copy email"><i class="fa-duotone fa-copy"></i></button>
          </div>
          <?php if ($email_tutorial_type === 'inboxes'): ?>
            <ol class="av-email-tutorial__steps">
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">1</span><div class="av-email-tutorial__text">Open <strong>inboxes.com</strong>.</div></li>
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">2</span><div class="av-email-tutorial__text">Click <strong>Get my first inbox</strong>.</div></li>
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">3</span><div class="av-email-tutorial__text">Paste the full email address from above.</div></li>
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">4</span><div class="av-email-tutorial__text">You will get access to the inbox and can receive the verification email there.</div></li>
            </ol>
          <?php else: ?>
            <ol class="av-email-tutorial__steps">
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">1</span><div class="av-email-tutorial__text">Open <strong>yopmail.com</strong>.</div></li>
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">2</span><div class="av-email-tutorial__text">Enter the full email address from above.</div></li>
              <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">3</span><div class="av-email-tutorial__text">Open the inbox and check for Riot or account verification emails.</div></li>
            </ol>
          <?php endif ?>
          <a class="av-email-tutorial__link" href="<?= $h($email_tutorial_url) ?>" target="_blank" rel="noopener noreferrer">Open email provider <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
        </div>
      </div>
      <?php endif ?>

      <?php if ($show_email_change_notice): ?>
      <div class="av-email-tutorial av-email-tutorial--notice mb-4">
        <div class="av-email-tutorial__head">
          <div>
            <div class="av-email-tutorial__title"><i class="fa-solid fa-circle-info"></i>Email access pending</div>
            <div class="av-email-tutorial__sub">No email password was provided for this account email. Support will help you change the account email to your own address.</div>
          </div>
        </div>
        <div class="av-email-tutorial__body">
          <div class="av-email-tutorial__email">
            <span>Current account email:</span>
            <code><?= $h($account_email_raw) ?></code>
            <button type="button" class="av-copy-btn js-pa-copy-email" data-value="<?= $h($account_email_raw) ?>" aria-label="Copy email"><i class="fa-duotone fa-copy"></i></button>
          </div>
          <div class="av-email-tutorial__text">Please keep an eye on the support chat. Our team will guide you through the email change process there.</div>
        </div>
      </div>
      <?php endif ?>

      <div class="card order-chat-card mb-4">
        <div class="av-chat-header">
          <div class="av-chat-title"><i class="fa-duotone fa-comments" style="color:#9f8cff;"></i> Support Chat</div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.3);">Chat directly with our support team</div>
        </div>

        <div class="card-body chat-bg" id="pa_chat_messages">
          <?php if (empty($chat_messages)): ?>
            <div class="lb-chat-empty">
              <i class="fa-duotone fa-message-slash" style="font-size:2rem;"></i>
              <span>No messages yet.<br>Send a message to contact support.</span>
            </div>
          <?php else: ?>
            <?php foreach ($chat_messages as $msg):
              $sender      = strtolower((string)($msg['sender'] ?? $msg['sender_type'] ?? 'client'));
              $is_deleted  = !empty($msg['deleted']);
              $text_raw    = (string)($msg['message'] ?? $msg['content'] ?? '');
              $text_decoded = html_entity_decode($text_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
              $text        = preg_replace('/<br\s*\/?>/i', "\n", $text_decoded);
              $text        = strip_tags((string)$text);
              $icon        = (string)($msg['sender_icon'] ?? '');
              $name        = (string)($msg['sender_name'] ?? ($sender === 'admin' ? 'Support' : $client_name));
              $ts_raw      = $msg['time'] ?? null;
              $ts_fmt      = is_numeric($ts_raw) ? date('d.m.Y H:i', (int)$ts_raw) : '';
              $is_me       = ($sender === 'client');
              $align_cls   = $is_me ? 'lb-msg--end' : 'lb-msg--start';
              $badge_cls   = $sender === 'admin' ? 'lb-badge--admin' : ($sender === 'system' ? 'lb-badge--system' : 'lb-badge--client');
              $badge_label = $sender === 'admin' ? 'Support' : ($sender === 'system' ? 'System' : 'You');

              if ($sender === 'system'): ?>
                <div class="lb-syswrap">
                  <div class="lb-sys"><?= nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) ?></div>
                  <?php if ($ts_fmt): ?><div class="lb-sys-time"><?= $ts_fmt ?></div><?php endif ?>
                </div>
              <?php continue; endif ?>

              <div class="lb-msg <?= $align_cls ?>">
                <div class="lb-msg__head <?= $is_me ? 'lb-msg__head--end' : '' ?>">
                  <?php if ($icon): ?>
                    <img class="lb-msg__avatar" src="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                  <?php elseif ($is_me && $client_icon): ?>
                    <img class="lb-msg__avatar" src="<?= htmlspecialchars($client_icon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                  <?php else: ?>
                    <span class="lb-msg__avatar" style="background:rgba(99,102,241,.3);color:#a5b4fc;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;"><?= strtoupper(substr($name, 0, 1)) ?></span>
                  <?php endif ?>
                  <div>
                    <div style="display:flex;align-items:center;gap:.3rem;<?= $is_me ? 'flex-direction:row-reverse;' : '' ?>">
                      <span class="lb-msg__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="lb-badge <?= $badge_cls ?>"><?= $badge_label ?></span>
                    </div>
                    <span class="lb-msg__time"><?= $ts_fmt ?></span>
                  </div>
                </div>
                <div class="lb-msg__bubble">
                  <?php if ($is_deleted): ?>
                    <em style="opacity:.5;">Message deleted</em>
                  <?php elseif (preg_match('/\.(png|jpe?g|gif|webp)(\?.*)?$/i', $text)): ?>
                    <img src="<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>" style="max-width:260px;max-height:200px;border-radius:.5rem;display:block;cursor:pointer;" onclick="window.open(this.src,'_blank')">
                  <?php else: ?>
                    <?= nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) ?>
                  <?php endif ?>
                </div>
              </div>
            <?php endforeach ?>
          <?php endif ?>
        </div>

        <div class="card-footer">
          <form id="paChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="client_premium_account_chat_send">
            <input type="hidden" name="account_id" value="<?= $account_id ?>">
            <input type="file" name="chat_image" id="paChatImageInput" accept="image/*" class="d-none">

            <div class="row gx-2">
              <div class="col">
                <input type="text" name="message" id="paChatInput" class="form-control" placeholder="Type your message" autocomplete="off">
              </div>
              <div class="col-auto d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-secondary" id="paChatAttachBtn" aria-label="Attach image" title="Attach image"><i class="fa-duotone fa-paperclip"></i></button>
                <button type="submit" class="btn btn-sm btn-primary" id="paChatSendBtn"><i class="fa-duotone fa-paper-plane fs-5"></i></button>
              </div>
              <div class="col-12 mt-2 d-none" id="paChatImgPreview">
                <div class="lb-chat-preview">
                  <img id="paChatImgPreviewImg" src="" alt="preview">
                  <span id="paChatImgPreviewName" style="flex:1;"></span>
                  <button type="button" class="lb-chat-preview__remove" id="paChatImgRemove" aria-label="Remove"><i class="fa-solid fa-xmark"></i></button>
                </div>
              </div>
            </div>
            <div class="pa-chat-error" id="paChatError"></div>
          </form>
          <div class="text-muted small mt-2">Tip: You can paste a screenshot with <strong>Ctrl + V</strong>.</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.22);"><i class="fa-solid fa-key" style="color:#a5b4fc;font-size:.72rem;"></i></span>
          <span class="av-sc-title">Your Credentials</span>
        </div>
        <div class="av-creds-list">
          <?php
          $creds = [
              ['In-Game Name', 'fa-solid fa-gamepad', $account['in_game_name'] ?? ''],
              ['Login', 'fa-solid fa-user', $login],
              ['Password', 'fa-solid fa-key', $password],
              ['Account Email', 'fa-solid fa-envelope', $account['email'] ?? ''],
              ['Email Password', 'fa-solid fa-lock', $account['email_password'] ?? ''],
              ['Data', 'fa-solid fa-clipboard', $data_field],
          ];
          $has_cred = false;
          foreach ($creds as [$label, $icon, $val]):
              $val = (string)$val;
              if ($val === '') continue;
              $has_cred = true;
              $safe = $h($val);
          ?>
            <div class="av-cred-item js-pa-copy" data-value="<?= $safe ?>">
              <div class="av-cred-left"><i class="<?= $icon ?> av-cred-ico"></i><span class="av-cred-lbl"><?= $label ?></span></div>
              <div class="av-cred-right"><span class="av-cred-val" title="<?= $safe ?>"><?= nl2br($safe) ?></span><button type="button" class="av-copy-btn"><i class="fa-duotone fa-copy"></i></button><span class="av-copy-ok">Copied</span></div>
            </div>
          <?php endforeach ?>
          <?php if (!$has_cred): ?>
            <div style="text-align:center;padding:18px 16px;"><div style="font-size:.8rem;color:rgba(255,255,255,.3);font-weight:700;">No credentials available yet.</div></div>
          <?php endif ?>
        </div>
      </div>

      <div class="av-sidebar-card mb-3">
        <div class="av-sc-header">
          <span class="av-sc-icon" style="background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.2);"><i class="fa-solid fa-lightbulb" style="color:#fbbf24;font-size:.72rem;"></i></span>
          <span class="av-sc-title">Getting Started</span>
        </div>
        <div style="padding:4px 0 8px;">
          <div class="av-tip-item"><div class="av-tip-ico" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;"><i class="fa-solid fa-key"></i></div><div><div class="av-tip-title">Change your password</div><div class="av-tip-desc">Update it immediately in Riot account settings.</div></div></div>
          <div class="av-tip-item"><div class="av-tip-ico" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#10b981;"><i class="fa-solid fa-envelope"></i></div><div><div class="av-tip-title">Check account data</div><div class="av-tip-desc">Make sure the login, password and additional data work.</div></div></div>
          <div class="av-tip-item"><div class="av-tip-ico" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24;"><i class="fa-solid fa-shield"></i></div><div><div class="av-tip-title">Secure the account</div><div class="av-tip-desc">Enable additional security after taking ownership.</div></div></div>
          <div class="av-tip-item"><div class="av-tip-ico" style="background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-solid fa-headset"></i></div><div><div class="av-tip-title">Need help?</div><div class="av-tip-desc">Use the chat to contact support directly.</div></div></div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="rp-modal-overlay" id="rpOverlay" role="dialog" aria-modal="true" aria-label="Report a Problem">
  <div class="rp-modal" id="rpModal">
    <div class="rp-modal-header">
      <div class="rp-modal-icon"><i class="fa-solid fa-flag"></i></div>
      <div class="rp-modal-title">Report a Problem</div>
      <button class="rp-modal-close" id="rpClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="rpFormWrap">
      <div class="rp-modal-body">
        <div class="rp-label">What's the issue?</div>
        <div class="rp-problems" id="rpProblems">
          <?php
          $problems = [
            ['id' => 'creds_wrong', 'icon' => 'fa-solid fa-key', 'text' => 'Login credentials are incorrect'],
            ['id' => 'acc_banned', 'icon' => 'fa-solid fa-ban', 'text' => 'Account has been banned / suspended'],
            ['id' => 'acc_locked', 'icon' => 'fa-solid fa-lock', 'text' => 'Account is locked or inaccessible'],
            ['id' => 'rank_wrong', 'icon' => 'fa-solid fa-trophy', 'text' => 'Rank / stats don\'t match the listing'],
            ['id' => 'support_no_resp', 'icon' => 'fa-solid fa-comment-slash', 'text' => 'Support is not responding'],
            ['id' => 'not_delivered', 'icon' => 'fa-solid fa-box-open', 'text' => 'Account was never delivered'],
            ['id' => 'other', 'icon' => 'fa-solid fa-ellipsis', 'text' => 'Other issue'],
          ];
          foreach ($problems as $p): ?>
          <label class="rp-problem-opt" data-id="<?= $p['id'] ?>">
            <input type="radio" name="rp_issue" value="<?= $p['id'] ?>">
            <i class="<?= $p['icon'] ?> rp-problem-ico"></i>
            <span class="rp-problem-text"><?= $p['text'] ?></span>
            <span class="rp-problem-check"><i class="fa-solid fa-check"></i></span>
          </label>
          <?php endforeach ?>
        </div>
        <div class="rp-details-wrap">
          <div class="rp-label">Additional details <span style="opacity:.5;font-weight:600;text-transform:none;letter-spacing:0;">(optional)</span></div>
          <textarea class="rp-details" id="rpDetails" rows="3" placeholder="Describe the problem in more detail…" maxlength="1000"></textarea>
        </div>
      </div>
      <div class="rp-modal-footer">
        <button class="rp-cancel" id="rpCancelBtn">Cancel</button>
        <button class="rp-submit" id="rpSubmitBtn" disabled><i class="fa-solid fa-paper-plane"></i> Send Report</button>
      </div>
    </div>
    <div id="rpSuccessWrap" style="display:none;">
      <div class="rp-success">
        <div class="rp-success-ico">✅</div>
        <div class="rp-success-title">Report sent!</div>
        <div class="rp-success-sub">Our team has been notified and will look into this shortly.</div>
        <button class="rp-cancel" style="margin-top:16px;" id="rpSuccessClose">Close</button>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  const ACCOUNT_ID  = <?= $account_id ?>;
  const AJAX_URL    = '<?= AJAX_URL ?>';
  document.querySelectorAll('.js-client-poke-seller').forEach(function(btn){
    btn.addEventListener('click', function(){
      if (btn.disabled) return;
      var oldHtml = btn.innerHTML;
      var cooldownStarted = false;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      function startCooldown(seconds) {
        var remaining = Math.max(1, parseInt(seconds, 10) || 300);
        cooldownStarted = true;
        function render() {
          var mins = Math.floor(remaining / 60), secs = String(remaining % 60).padStart(2, '0');
          btn.innerHTML = '<i class="fa-solid fa-clock"></i> Poke again in ' + mins + ':' + secs;
          if (remaining-- <= 0) { clearInterval(timer); btn.disabled = false; btn.innerHTML = oldHtml; }
        }
        render(); var timer = setInterval(render, 1000);
      }
      $.post(AJAX_URL, { action: 'client_poke_seller', ref_type: btn.getAttribute('data-ref-type') || 'premium_account', id: btn.getAttribute('data-id') || ACCOUNT_ID }, function(resp){
        var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
        if (d && d.cooldown_seconds) startCooldown(d.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });

  const USER_ID     = <?= $client_id_safe ?>;
  const chatEl      = document.getElementById('pa_chat_messages');
  if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;

  document.querySelectorAll('.js-pa-copy').forEach(function (row) {
    row.addEventListener('click', function () {
      const val = row.getAttribute('data-value') || '';
      if (!val) return;
      navigator.clipboard.writeText(val).then(function () {
        row.classList.add('copied');
        setTimeout(function () { row.classList.remove('copied'); }, 1800);
      }).catch(function () {
        const ta = document.createElement('textarea');
        ta.value = val;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        row.classList.add('copied');
        setTimeout(function () { row.classList.remove('copied'); }, 1800);
      });
    });
  });

  const form       = document.getElementById('paChatForm');
  const input      = document.getElementById('paChatInput');
  const fileInput  = document.getElementById('paChatImageInput');
  const attachBtn  = document.getElementById('paChatAttachBtn');
  const preview    = document.getElementById('paChatImgPreview');
  const previewImg = document.getElementById('paChatImgPreviewImg');
  const previewName= document.getElementById('paChatImgPreviewName');
  const removeBtn  = document.getElementById('paChatImgRemove');
  const errBox     = document.getElementById('paChatError');
  const sendBtn    = document.getElementById('paChatSendBtn');
  let previewUrl = null;
  let lastTs = Math.floor(Date.now() / 1000);
  const notifAudio = new Audio('<?= defined("ASSET_URL") ? ASSET_URL : "" ?>/core/dash/audio/new-message.mp3');

  function setError(msg) {
    if (!errBox) return;
    errBox.style.display = msg ? 'block' : 'none';
    errBox.textContent = msg;
  }
  function clearFile() {
    if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
    if (fileInput) fileInput.value = '';
    if (preview) preview.classList.add('d-none');
    if (previewImg) previewImg.src = '';
    if (previewName) previewName.textContent = '';
  }
  function showFile(file) {
    if (!file || !/^image\//i.test(file.type)) { setError('Only images allowed.'); clearFile(); return; }
    setError('');
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    if (previewImg) previewImg.src = previewUrl;
    if (previewName) previewName.textContent = file.name || 'image';
    if (preview) preview.classList.remove('d-none');
  }
  if (attachBtn && fileInput) {
    attachBtn.addEventListener('click', function () { setError(''); fileInput.click(); });
    fileInput.addEventListener('change', function () { showFile(fileInput.files && fileInput.files[0]); });
  }
  if (removeBtn) removeBtn.addEventListener('click', function () { setError(''); clearFile(); });

  document.addEventListener('paste', function (e) {
    if (!form || !fileInput) return;
    const act = document.activeElement;
    if (!form.contains(act) && act !== input) return;
    const items = e.clipboardData ? e.clipboardData.items : [];
    for (var i = 0; i < items.length; i++) {
      const it = items[i];
      if (it && it.type && it.type.indexOf('image/') === 0) {
        const blob = it.getAsFile();
        if (!blob) continue;
        const f = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });
        const dt = new DataTransfer();
        dt.items.add(f);
        fileInput.files = dt.files;
        showFile(f);
        e.preventDefault();
        break;
      }
    }
  });

  function _esc(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function renderPremiumTicks(msg) {
    var sender = String(msg.sender || msg.sender_type || '').toLowerCase();
    if (sender !== 'client') return '';
    var seen = Number(msg.seen_by_seller ?? msg.is_read ?? 0) === 1;
    var label = seen ? 'Read' : 'Delivered';
    return '<span class="lb-msg__ticks ' + (seen ? 'is-read' : 'is-delivered') + '" title="' + label + '">✓✓ ' + label + '</span>';
  }
  function renderMsg(msg) {
    var sender = (msg.sender || msg.sender_type || 'client').toLowerCase();
    var isMe = (sender === 'client');
    var text = msg.message || msg.content || '';
    var icon = msg.sender_icon || '';
    var name = msg.sender_name || (isMe ? 'You' : 'Support');
    var ts = msg.time ? (new Date(msg.time * 1000)).toLocaleString('de-DE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '';
    if (sender === 'system') {
      var systemText = String(text || '')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<[^>]*>/g, '');
      return '<div class="lb-syswrap"><div class="lb-sys">' + _esc(systemText).replace(/\n/g, '<br>') + '</div>' + (ts ? '<div class="lb-sys-time">' + ts + '</div>' : '') + '</div>';
    }
    var alignCls = isMe ? 'lb-msg--end' : 'lb-msg--start';
    var badgeCls = sender === 'admin' ? 'lb-badge--admin' : 'lb-badge--client';
    var badgeLabel = sender === 'admin' ? 'Support' : 'You';
    var initial = name.charAt(0).toUpperCase();
    var avatarHtml = icon ? '<img class="lb-msg__avatar" src="' + _esc(icon) + '" alt="">' : '<span class="lb-msg__avatar" style="background:rgba(99,102,241,.3);color:#a5b4fc;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;">' + initial + '</span>';
    var bodyHtml = /\.(png|jpe?g|gif|webp)(\?.*)?$/i.test(text) ? '<img src="' + _esc(text) + '" style="max-width:260px;max-height:200px;border-radius:.5rem;display:block;cursor:pointer;" onclick="window.open(this.src,\'_blank\')">' : _esc(text).replace(/\n/g, '<br>');
    return '<div class="lb-msg ' + alignCls + '"><div class="lb-msg__head' + (isMe ? ' lb-msg__head--end' : '') + '">' + avatarHtml + '<div><div style="display:flex;align-items:center;gap:.3rem;' + (isMe ? 'flex-direction:row-reverse;' : '') + '"><span class="lb-msg__name">' + _esc(name) + '</span><span class="lb-badge ' + badgeCls + '">' + badgeLabel + '</span></div><span class="lb-msg__time">' + ts + '</span></div></div><div class="lb-msg__bubble">' + bodyHtml + '</div><div class="lb-msg__stamp">' + ts + (isMe ? renderPremiumTicks(msg) : '') + '</div></div>';
  }
  function scrollBottom() { if (chatEl) chatEl.scrollTop = chatEl.scrollHeight; }

  var lbPremiumChatReadActivated = false;
  var lbPremiumReadRequestRunning = false;
  function premiumChatCanMarkRead(){
    if (!lbPremiumChatReadActivated || document.visibilityState !== 'visible' || !chatEl) return false;
    var rect = chatEl.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }
  function activatePremiumChatRead(){
    if (document.visibilityState !== 'visible') return;
    lbPremiumChatReadActivated = true;
    if (!lbPremiumReadRequestRunning) pollMessages(true);
  }
  function bindPremiumChatRead(){
    [chatEl, input, form].forEach(function(el){
      if (!el || el.dataset.lbReadBound === '1') return;
      el.dataset.lbReadBound='1';
      ['pointerdown','click','touchstart','wheel','scroll','focusin','keydown'].forEach(function(ev){
        el.addEventListener(ev, activatePremiumChatRead, {passive:true});
      });
    });
  }

  function pollMessages(markSeen) {
    if (typeof $ === 'undefined') return;
    var payload = { action: 'client_premium_account_chat_load', account_id: ACCOUNT_ID, last_ts: lastTs };
    if (markSeen && premiumChatCanMarkRead()) payload.mark_seen = 1;
    if (payload.mark_seen) lbPremiumReadRequestRunning = true;
    $.post(AJAX_URL, payload, function (resp) {
      var r;
      try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e) { return; }
      if (!r.success || !r.messages || !r.messages.length) return;
      var hadEmpty = chatEl && chatEl.querySelector('.lb-chat-empty');
      if (hadEmpty) chatEl.innerHTML = '';
      r.messages.forEach(function (msg) {
        if (msg.time) lastTs = Math.max(lastTs, parseInt(msg.time, 10));
        if (chatEl) chatEl.insertAdjacentHTML('beforeend', renderMsg(msg));
        if (msg.sender === 'admin' || msg.sender_type === 'admin') {
          try { notifAudio.volume = 0.5; notifAudio.play(); } catch(e) {}
        }
      });
      scrollBottom();
    }).always(function(){ lbPremiumReadRequestRunning = false; });
  }
  window.lbOrderViewChatUpdate = function (data) {
    if (data && data.order_id && data.order_id !== ('acct_' + ACCOUNT_ID) && data.order_id !== ('premacct_' + ACCOUNT_ID)) return;
    if (data && Array.isArray(data.messages)) {
      if (chatEl) chatEl.innerHTML = '';
      data.messages.forEach(function(msg){ if(chatEl) chatEl.insertAdjacentHTML('beforeend', renderMsg(msg)); });
      scrollBottom();
      return;
    }
    pollMessages(premiumChatCanMarkRead());
  };

  bindPremiumChatRead();

  setInterval(function () {
    if (document.visibilityState !== 'visible') return;
    if (window.lbRealtimeConnected) return;
    pollMessages();
  }, 30000);


  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      var msg = input ? input.value.trim() : '';
      var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
      if (!msg && !hasFile) { setError('Please type a message or attach an image.'); return; }
      setError('');
      if (sendBtn) sendBtn.disabled = true;
      var fd = new FormData(form);
      try {
        var res = await fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' });
        var json = {};
        try { json = await res.json(); } catch(_) {}
        if (json.success || res.ok) {
          if (input) input.value = '';
          if (fileInput) fileInput.value = '';
          if (json && Array.isArray(json.messages)) window.lbOrderViewChatUpdate({order_id:'acct_' + ACCOUNT_ID,messages:json.messages});
          return;
        } else {
          setError('Could not send message. Please try again.');
        }
      } catch(err) {
        setError('Send failed. Please try again.');
        console.error(err);
      } finally {
        if (sendBtn) sendBtn.disabled = false;
      }
    });
  }


  document.querySelectorAll('.js-pa-copy-email').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const val = btn.getAttribute('data-value') || '';
      if (!val) return;
      navigator.clipboard.writeText(val).catch(function () {});
    });
  });

  (function () {
    const REPORT_AJAX = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
    const overlay = document.getElementById('rpOverlay');
    const openBtn = document.getElementById('reportProblemBtn');
    const closeBtn = document.getElementById('rpClose');
    const cancelBtn = document.getElementById('rpCancelBtn');
    const submitBtn = document.getElementById('rpSubmitBtn');
    const formWrap = document.getElementById('rpFormWrap');
    const successWrap = document.getElementById('rpSuccessWrap');
    const successCloseBtn = document.getElementById('rpSuccessClose');
    const detailsEl = document.getElementById('rpDetails');
    const problemOpts = document.querySelectorAll('.rp-problem-opt');
    let selectedIssue = null;
    function openModal() { if (!overlay) return; overlay.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    function closeModal() {
      if (!overlay) return;
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      setTimeout(function () {
        selectedIssue = null;
        problemOpts.forEach(function (o) { o.classList.remove('is-selected'); });
        if (detailsEl) detailsEl.value = '';
        if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report'; }
        if (formWrap) formWrap.style.display = '';
        if (successWrap) successWrap.style.display = 'none';
      }, 220);
    }
    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (successCloseBtn) successCloseBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) closeModal(); });
    problemOpts.forEach(function (opt) {
      opt.addEventListener('click', function () {
        problemOpts.forEach(function (o) { o.classList.remove('is-selected'); });
        opt.classList.add('is-selected');
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        selectedIssue = opt.getAttribute('data-id');
        if (submitBtn) {
          submitBtn._label = opt.querySelector('.rp-problem-text') ? opt.querySelector('.rp-problem-text').textContent : selectedIssue;
          submitBtn.disabled = false;
        }
      });
    });
    if (submitBtn) submitBtn.addEventListener('click', async function () {
      if (!selectedIssue) return;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…';
      const issueLabel = submitBtn._label || selectedIssue;
      const details = detailsEl ? detailsEl.value.trim() : '';
      const accountTitle = <?= json_encode($pkg_name) ?>;
      const accountId = <?= $account_id ?>;
      const clientId = <?= json_encode((string)(defined('CLIENT_ID') ? CLIENT_ID : 'unknown')) ?>;
      const clientName = <?= json_encode((string)(defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? (CLIENT_DATA['username'] ?? 'Client') : 'Client')) ?>;
      const clientAdminUrl = <?= json_encode((defined('ADMN_URL') && defined('CLIENT_ID')) ? (ADMN_URL . '/client/' . (int)CLIENT_ID) : '') ?>;
      const adminUrl = `<?= defined('ADMN_URL') ? ADMN_URL : '' ?>/account/${accountId}`;
      const payload = {
        username: 'Premium Account Reports',
        embeds: [{
          title: '🚨 Premium Account Problem Report',
          color: 0xef4444,
          fields: [
            { name: '🎮 Account', value: `**${accountTitle}** (#${accountId})`, inline: true },
            { name: '🏆 Rank', value: <?= json_encode($rank_full_label ?: 'Unranked') ?>, inline: true },
            { name: '👤 Client', value: `**${clientName}** (#${clientId})`, inline: true },
            { name: '⚠️ Issue', value: issueLabel, inline: false },
            ...(details ? [{ name: '📝 Details', value: details.substring(0, 1000), inline: false }] : []),
            ...(clientAdminUrl ? [{ name: '🔗 Client Page', value: `[Open Client in Admin Panel](${clientAdminUrl})`, inline: false }] : []),
            { name: '🔗 Admin', value: `[View Account in Admin Panel](${adminUrl})`, inline: false }
          ],
          footer: { text: 'Reported via lolboost.gg' },
          timestamp: new Date().toISOString()
        }]
      };
      try {
        const fd = new FormData();
        fd.set('action', 'client_report_problem');
        fd.set('ref_type', 'premium_account');
        fd.set('ref_id', String(accountId));
        fd.set('issue', selectedIssue);
        fd.set('issue_label', issueLabel);
        fd.set('details', details);
        const res = await fetch(REPORT_AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
        const d = await res.json();
        if (d && d.success) {
          if (formWrap) formWrap.style.display = 'none';
          if (successWrap) successWrap.style.display = '';
        } else {
          throw new Error((d && d.message) ? d.message : 'Report failed');
        }
      } catch (err) {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not send report. Please try again.');
      }
    });
  })();

  scrollBottom();
})();
</script>
<?= $this->end() ?>
