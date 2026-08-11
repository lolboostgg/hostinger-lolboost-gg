<?php
/**
 * Client: Purchased Marketplace Account View
 * Shows account details + chat with seller
 * Variables passed from route: $account, $seller, $chat_messages (optional)
 */

$account_id  = (int)($account['id'] ?? 0);
$seller      = $seller ?? null;
$page_title  = htmlspecialchars(html_entity_decode((string)($account['title'] ?? ('Account #S' . $account_id)), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$game        = strtolower((string)($account['game'] ?? ''));
if ($game !== 'val') $game = 'lol';
$isValorant  = ($game === 'val');

$lol_rank_labels = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
$lol_div_labels  = ['','IV','III','II','I'];
$game_data = [];
try { $game_data = json_decode($account['game_data'] ?? '[]', true) ?: []; } catch(Throwable $e) {}
if (!is_array($game_data)) $game_data = [];

if ($isValorant) {
    $rank_label = trim((string)($account['rank_label'] ?? ''));
    if ($rank_label === '') {
        $rank_label = function_exists('util_get_val_rank') ? (string)util_get_val_rank((int)($account['rank'] ?? 0)) : 'Unranked';
    }
    $div_label = '';
    $_rank_idx = (int)($account['rank'] ?? 0);
} else {
    $rank_label  = $lol_rank_labels[(int)($account['current_rank'] ?? 0)] ?? 'Unranked';
    $_rank_idx   = (int)($account['current_rank'] ?? 0);
    $div_label   = ($_rank_idx === 0 || $_rank_idx >= 8) ? '' : ($lol_div_labels[(int)($account['current_division'] ?? 0)] ?? '');
}

$images = [];
try { $images = json_decode($account['images'] ?? '[]', true) ?: []; } catch(Throwable $e) {}

$champs = !$isValorant && !empty($account['champions']) ? array_values(array_filter(explode('|', $account['champions']))) : [];
$roles  = !$isValorant && !empty($account['roles'])     ? array_values(array_filter(explode('|', $account['roles'])))     : [];
$skins  = !$isValorant && !empty($account['skins'])     ? array_values(array_filter(explode('|', $account['skins'])))     : [];
$agents = $isValorant && !empty($game_data['agents']) && is_array($game_data['agents']) ? array_values(array_filter(array_map('strval', $game_data['agents']))) : [];

// Display counts: manual list preferred, fallback to count-only fields
$valAgentsDisplayCount = count($agents) > 0
    ? count($agents)
    : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
       ? (int)$account['val_agent_count'] : 0);
$champsDisplayCount = count($champs) > 0
    ? count($champs)
    : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
       ? (int)$account['champion_count'] : 0);
$skinsDisplayCount = count($skins) > 0
    ? count($skins)
    : (isset($account['skin_count']) && $account['skin_count'] !== null && $account['skin_count'] !== ''
       ? (int)$account['skin_count'] : 0);

$decode_html = function($v) {
    $s = (string)($v ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $s) break;
        $s = $decoded;
    }
    return $s;
};
$h = fn($v) => htmlspecialchars($decode_html($v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$sellerUsernameForUrl = trim((string)($seller['username'] ?? ''));
$sellerProfileUrl = $sellerUsernameForUrl !== '' ? rtrim((defined('BASE_URL') ? BASE_URL : 'https://lolboost.gg'), '/') . '/sellers/' . rawurlencode($sellerUsernameForUrl) : '#';

$account_email_raw = trim((string)($account['email'] ?? ''));
$account_email_raw = str_replace("\xC2\xA0", ' ', $account_email_raw); // normalize non-breaking spaces
$account_email_raw = trim($account_email_raw);
$account_email_lc  = strtolower($account_email_raw);

// IMPORTANT:
// In the database an empty email field OR the literal value "unverified"
// means this is an unverified account. In that case the customer does not
// need email access, so no email tutorial/notice should be shown at all.
// For all other values, only continue when the field actually looks like an email.
$is_unverified_account_email = ($account_email_raw === '' || $account_email_lc === 'unverified');
$has_valid_account_email = (!$is_unverified_account_email && strpos($account_email_raw, '@') !== false);

$account_email_domain = '';
if ($has_valid_account_email) {
    $account_email_domain = strtolower(substr(strrchr($account_email_raw, '@'), 1));
}

$inboxes_domains = [
    'blondmail.com', 'chapsmail.com', 'clowmail.com', 'dropjar.com', 'fivermail.com',
    'getairmail.com', 'getmule.com', 'getnada.com', 'gjmpmail.com', 'gjvmail.com',
    'guysmail.com', 'inboxbear.com', 'replyloop.com', 'robot-mail.com', 'spicysoda.com',
    'tafmail.com', 'temptami.com', 'tupmail.com', 'vomoto.com',
];

$email_tutorial_type = '';
$email_tutorial_url = '';
$email_tutorial_title = '';

// Only check provider tutorials when a real email address is present.
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

$email_password_raw = (string)($account['email_password'] ?? '');
$email_password_raw = str_replace("\xC2\xA0", ' ', $email_password_raw); // normalize non-breaking spaces
$email_password_raw = trim($email_password_raw);
$email_password_lc = strtolower($email_password_raw);
$email_password_compact = preg_replace('/[^a-z0-9]+/i', '', $email_password_lc);

// Values sellers commonly use when no email password is available.
// Examples from the DB include "No Password", "NO", empty values, etc.
$missing_email_password_values = [
    '', '-', 'no', 'no password', 'none', 'null', 'unverified',
    'n/a', 'na', 'not provided', 'not available', 'no email password',
];
$missing_email_password_compact_values = [
    '', 'no', 'nopassword', 'none', 'null', 'unverified',
    'na', 'notprovided', 'notavailable', 'noemailpassword',
];
// Treat very short placeholder values like "f", "x", "ws", etc. as missing too.
// A real email password should have at least 5 visible characters.
$email_password_visible_length = strlen($email_password_raw);

$has_email_password = !(
    $email_password_visible_length < 5
    || in_array($email_password_lc, $missing_email_password_values, true)
    || in_array($email_password_compact, $missing_email_password_compact_values, true)
    || strpos($email_password_lc, 'no password') !== false
);

// Show pending notice only for a real non-trash email address without email password.
// Do not show it for empty/unverified/non-email values.
$show_email_change_notice = ($has_valid_account_email && $email_tutorial_type === '' && !$has_email_password);
?>
<?= $this->layout('client/layouts/main', ['meta' => [
    'title'       => $page_title . ' | LoLBoost.gg',
    'h1'          => 'Account Details',
    'description' => $isValorant ? 'View your purchased Valorant account details and chat with the seller.' : 'View your purchased LoL account details and chat with the seller.',
]]) ?>

<?= $this->start('styles') ?>
<style>
/* ── Base card overrides ── */
.client-account-view .card {
  background: var(--bs-card-bg) !important;
  border: var(--bs-card-border-width) solid var(--bs-card-border-color) !important;
  border-radius: 22px !important;
  box-shadow: none !important;
}
.client-account-view .card::before { display: none !important; }
.client-account-view .order-chat-card { overflow: hidden; }

/* ── Head card ── */
.av-head {
  border-radius: 22px; overflow: hidden; margin-bottom: 20px;
  border: 1px solid var(--bs-card-border-color);
  background: #25282a;
}
.av-head-body {
  padding: 20px 22px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px;
  border-bottom: 1px solid var(--bs-card-border-color);
}

/* Status pills */
.av-status { display: inline-flex; align-items: center; gap: .35rem; padding: 4px 11px; border-radius: 99px; font-size: .75rem; font-weight: 800; }
.av-status--purchased { background: rgba(99,102,241,.14); border: 1px solid rgba(99,102,241,.28); color: #a5b4fc; }

/* Meta pills row */
.av-meta-row { display: flex; flex-wrap: wrap; gap: 6px; padding: 14px 22px 16px; }
.av-meta-pill { display: inline-flex; align-items: center; gap: .3rem; padding: 4px 11px; border-radius: 99px; font-size: .75rem; font-weight: 700; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); color: rgba(255,255,255,.7); }
.av-meta-pill strong { color: rgba(255,255,255,.92); }

/* Action buttons */
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


/* ── Report Modal ── */
.rp-modal-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
  opacity: 0; pointer-events: none; transition: opacity .2s;
}
.rp-modal-overlay.is-open { opacity: 1; pointer-events: all; }
.rp-modal {
  width: 100%; max-width: 480px;
  background: #1e2022; border: 1px solid rgba(255,255,255,.1);
  border-radius: 20px; overflow: hidden;
  transform: translateY(16px) scale(.97); transition: transform .2s;
  box-shadow: 0 24px 60px rgba(0,0,0,.5);
}
.rp-modal-overlay.is-open .rp-modal { transform: translateY(0) scale(1); }
.rp-modal-header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.07);
  background: rgba(255,255,255,.02);
}
.rp-modal-icon {
  width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
  background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.22);
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; color: #f87171;
}
.rp-modal-title { font-size: .95rem; font-weight: 900; color: rgba(255,255,255,.9); flex: 1; }
.rp-modal-close { background: none; border: none; color: rgba(255,255,255,.3); cursor: pointer; padding: 4px; border-radius: 6px; transition: color .12s, background .12s; line-height: 1; }
.rp-modal-close:hover { color: #fff; background: rgba(255,255,255,.06); }
.rp-modal-body { padding: 20px; }
.rp-label { font-size: .72rem; font-weight: 800; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
.rp-problems { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.rp-problem-opt {
  display: flex; align-items: center; gap: 10px; padding: 10px 12px;
  border-radius: 11px; border: 1px solid rgba(255,255,255,.07);
  background: rgba(255,255,255,.03); cursor: pointer;
  transition: border-color .12s, background .12s;
}
.rp-problem-opt:hover { border-color: rgba(239,68,68,.3); background: rgba(239,68,68,.05); }
.rp-problem-opt.is-selected { border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.1); }
.rp-problem-opt input[type="radio"] { display: none; }
.rp-problem-ico { font-size: .8rem; width: 16px; text-align: center; color: rgba(255,255,255,.35); flex-shrink: 0; }
.rp-problem-opt.is-selected .rp-problem-ico { color: #f87171; }
.rp-problem-text { font-size: .82rem; font-weight: 700; color: rgba(255,255,255,.65); flex: 1; }
.rp-problem-opt.is-selected .rp-problem-text { color: rgba(255,255,255,.9); }
.rp-problem-check { width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.15); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .6rem; color: transparent; transition: all .12s; }
.rp-problem-opt.is-selected .rp-problem-check { border-color: #f87171; background: #f87171; color: #fff; }
.rp-details-wrap { margin-bottom: 16px; }
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

/* ── Sidebar cards ── */
.av-sidebar-card {
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,.07);
  background: #25282a;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(0,0,0,.2);
}
.av-sc-header {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255,255,255,.06);
  background: rgba(255,255,255,.02);
}
.av-sc-icon {
  width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(255,255,255,.1); font-size: .75rem;
}
.av-sc-title { font-size: .8rem; font-weight: 900; color: rgba(255,255,255,.75); text-transform: uppercase; letter-spacing: .06em; flex: 1; }

/* ── Credentials list ── */
.av-creds-list { padding: 4px 0 6px; }
.av-cred-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 8px 16px; border-bottom: 1px solid rgba(255,255,255,.04); overflow: visible; }
.av-cred-item:last-child { border-bottom: 0; }
.av-cred-left { display: flex; align-items: center; gap: 7px; min-width: 90px; }
.av-cred-ico  { font-size: .65rem; color: rgba(255,255,255,.22); width: 12px; text-align: center; flex-shrink: 0; }
.av-cred-lbl  { font-size: .72rem; font-weight: 700; color: rgba(255,255,255,.38); white-space: nowrap; }
.av-cred-right { display: flex; align-items: flex-start; gap: 5px; min-width: 0; flex: 1 1 auto; justify-content: flex-end; overflow: visible; }
.av-cred-val  { font-family: monospace; font-size: .77rem; font-weight: 700; color: rgba(255,255,255,.82); max-width: none; overflow: visible; text-overflow: clip; white-space: normal; overflow-wrap: anywhere; word-break: break-word; cursor: pointer; text-align: right; line-height: 1.35; }
.av-copy-btn  { background: none; border: none; color: rgba(255,255,255,.2); cursor: pointer; padding: 2px 3px; transition: color .12s; line-height: 1; flex-shrink: 0; }
.av-copy-btn:hover { color: #9f8cff; }

/* ── Stat grid 2-col ── */
.av-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.av-stat-item { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-bottom: 1px solid rgba(255,255,255,.04); border-right: 1px solid rgba(255,255,255,.04); }
.av-stat-item:nth-child(even) { border-right: 0; }
.av-stat-item:nth-last-child(-n+2) { border-bottom: 0; }
.av-stat-ico { font-size: .65rem; color: rgba(255,255,255,.25); width: 14px; flex-shrink: 0; }
.av-stat-lbl { font-size: .65rem; font-weight: 700; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: .04em; line-height: 1; }
.av-stat-val { font-size: .8rem; font-weight: 800; color: rgba(255,255,255,.82); margin-top: 2px; line-height: 1.2; }

/* ── Tags ── */
.av-tag-section { padding: 12px 16px; }
.av-tag-label { font-size: .65rem; font-weight: 800; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: .07em; margin-bottom: 7px; }
.av-tag-list  { display: flex; flex-wrap: wrap; gap: 5px; }
.av-tag { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 99px; font-size: .7rem; font-weight: 700; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.09); color: rgba(255,255,255,.6); }
.av-tag--role { background: rgba(109,92,255,.1); border-color: rgba(109,92,255,.25); color: #c4b5fd; }
.av-tag--more { background: rgba(255,255,255,.03); color: rgba(255,255,255,.3); }

/* ── Seller card ── */
.av-seller-row { display: flex; align-items: center; gap: 12px; padding: 14px 16px; }
.av-seller-main-link { display:flex; align-items:center; gap:12px; min-width:0; text-decoration:none; color:inherit; border-radius:12px; padding:3px; margin:-3px; transition:background .15s, opacity .15s; }
.av-seller-main-link:hover { background:rgba(255,255,255,.045); color:inherit; opacity:.96; }
.av-seller-main-link:hover .av-seller-name { color:#fff!important; text-decoration:underline; text-underline-offset:3px; }
.av-seller-avi { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .95rem; font-weight: 900; flex-shrink: 0; }

/* ── Tips card ── */
.av-tip-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,.04); }
.av-tip-item:last-child { border-bottom: 0; }
.av-tip-ico { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .75rem; }
.av-tip-title { font-size: .8rem; font-weight: 800; color: rgba(255,255,255,.85); margin-bottom: 1px; }
.av-tip-desc  { font-size: .73rem; color: rgba(255,255,255,.38); line-height: 1.4; }

/* -- Email inbox tutorial -- */
.av-email-tutorial { border-radius: 18px; border: 1px solid rgba(99,102,241,.24); background: linear-gradient(135deg, rgba(99,102,241,.13), rgba(124,58,237,.08)); overflow: hidden; box-shadow: 0 18px 38px rgba(0,0,0,.16); }
.av-email-tutorial--notice { border-color: rgba(99,102,241,.30); background: linear-gradient(135deg, rgba(99,102,241,.14), rgba(124,58,237,.08)); }
.av-email-tutorial--notice .av-email-tutorial__title i { background: rgba(99,102,241,.20); color: #c4b5fd; border-color: rgba(99,102,241,.32); }
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

/* ── Gallery ── */
.av-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; padding: 16px 20px; }
.av-gallery-tile { position: relative; border-radius: 12px; overflow: hidden; background: #0d0f1a; cursor: pointer; }
.av-gallery-tile img { width: 100%; height: 130px; object-fit: cover; display: block; transition: transform .3s; }
.av-gallery-tile:hover img { transform: scale(1.04); }
.av-gallery-main-badge { position: absolute; top: 8px; left: 8px; padding: 2px 8px; border-radius: 99px; background: rgba(109,92,255,.9); color: #fff; font-size: .68rem; font-weight: 800; }

/* ── Chat ── */
.av-chat-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--bs-card-border-color); }
.av-chat-title  { font-size: .95rem; font-weight: 900; color: rgba(255,255,255,.9); display: flex; align-items: center; gap: .5rem; }
#chat_messages {
  min-height: 300px; max-height: 480px; overflow-y: auto;
  padding: 1rem 1.25rem; display: flex; flex-direction: column; scroll-behavior: smooth;
}
#chat_messages::-webkit-scrollbar { width: 5px; }
#chat_messages::-webkit-scrollbar-track { background: transparent; }
#chat_messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 3px; }

/* lb-msg bubbles */
.lb-msg { display: flex; flex-direction: column; margin-bottom: .5rem; max-width: 75%; }
.lb-msg--start { align-self: flex-start; }
.lb-msg--end   { align-self: flex-end; }
.lb-msg__head  { display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
.lb-msg__head--end { flex-direction: row-reverse; }
.lb-msg__avatar { width: 1.75rem; height: 1.75rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.lb-msg__name { font-weight: 700; font-size: .8rem; line-height: 1.3; display: flex; align-items: center; gap: .3rem; }
.lb-msg__time { font-size: .72rem; opacity: .45; }
.lb-msg__bubble { padding: .55rem .85rem; border-radius: .75rem; font-size: .875rem; line-height: 1.55; word-break: break-word; background: rgba(255,255,255,.07); position: relative; }
.lb-msg--end .lb-msg__bubble { background: rgba(99,102,241,.22); }
.lb-msg__bubble--deleted { opacity: .55; font-style: italic; }
.lb-msg__stamp { font-size: .7rem; opacity: .4; margin-top: .2rem; }
.lb-msg--end .lb-msg__stamp { text-align: right; }
.lb-msg__ticks { margin-left: .25rem; }
.lb-msg__content img { max-width: 240px; max-height: 200px; border-radius: .5rem; display: block; margin-top: .4rem; cursor: pointer; }
.lb-msg__edit { display: none; position: absolute; top: .3rem; right: .3rem; background: none; border: none; color: rgba(255,255,255,.3); cursor: pointer; padding: .1rem .3rem; border-radius: .3rem; font-size: .75rem; }
.lb-msg__bubble:hover .lb-msg__edit { display: inline-flex; }
.lb-msg__editor { width: 100%; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: inherit; border-radius: .5rem; padding: .4rem .6rem; font-size: .875rem; resize: none; }
.lb-msg__editor-actions { display: flex; gap: .5rem; margin-top: .4rem; }
.lb-msg__edited { font-size: .68rem; opacity: .45; margin-left: .35rem; }

/* badges */
.lb-badge { display: inline-flex; align-items: center; padding: .1rem .4rem; border-radius: 999px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.lb-badge--seller { background: rgba(99,102,241,.2); color: #818cf8; }
.lb-badge--client { background: rgba(16,185,129,.15); color: #10b981; }
.lb-badge--admin  { background: rgba(245,158,11,.15); color: #f59e0b; }
.lb-badge--system { background: rgba(107,114,128,.15); color: #9ca3af; }
.lb-syswrap { width: 100%; margin: .75rem 0; }
/* Sized like the order-view chat — this bubble was noticeably larger than the rest. */
.lb-sys { display: block; width: 100%; background: rgba(109, 76, 255, .10); border: 1px dashed rgba(173, 140, 255, .35); border-radius: 14px; padding: 10px 14px; font-size: .86rem; font-weight: 800; line-height: 1.5; color: rgba(255,255,255,.92); text-align: left; box-shadow: inset 0 1px 0 rgba(255,255,255,.03); }
.lb-sys-time { font-size: .72rem; opacity: .55; margin-top: .35rem; padding-left: 2px; text-align: left; }

/* attach preview */
.lb-chat-preview { display: inline-flex; align-items: center; gap: .5rem; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: .5rem; padding: .4rem .7rem; }
.lb-chat-preview img { width: 2.5rem; height: 2.5rem; object-fit: cover; border-radius: .35rem; }
.lb-chat-preview__remove { background: none; border: none; color: rgba(255,255,255,.5); cursor: pointer; padding: 0 .2rem; }
.lb-chat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 240px; opacity: .4; gap: .5rem; text-align: center; }

/* Lightbox */
#lbImgModal .modal-content { background: rgba(0,0,0,.85); border: none; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .av-head-body { padding: 14px 16px; }
  .av-meta-row  { padding: 10px 16px 12px; }
  #chat_messages { min-height: 220px; max-height: 340px; }
  .lb-msg { max-width: 88%; }
  .av-stat-grid { grid-template-columns: 1fr 1fr; }
  .av-gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .av-meta-pill { font-size: .7rem; padding: 3px 8px; }
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

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <img src="<?= util_rank_img($isValorant ? 'val' : 'lol', 'mini', $isValorant ? (int)($account['rank'] ?? 0) : (int)($account['current_rank'] ?? 0)) ?>" style="width:30px;height:30px;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));" alt="">
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 style="font-size:1.25rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;"><?= $page_title ?></h1>
          <span class="av-status av-status--purchased"><i class="fa-solid fa-bag-shopping" style="font-size:.6rem;"></i> Purchased</span>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;">
          <?php if (!empty($account['server'])): ?>
            <span style="text-transform:uppercase;font-weight:700;"><?= $h($account['server']) ?></span><span>·</span>
          <?php endif ?>
          <span>#S<?= $account_id ?></span>
          <span>·</span>
          <span><?= date('d.m.Y', strtotime($account['sold_at'] ?? $account['created_at'] ?? 'now')) ?></span>
          <?php if (!empty($seller)): ?>
            <span>·</span>
            <span style="font-weight:700;"><i class="fa-duotone fa-store me-1"></i><?= $h($seller['username'] ?? '—') ?></span>
          <?php endif ?>
        </div>
      </div>
    </div>

    <div class="av-head-actions">
      <button type="button" class="av-btn-danger" id="reportProblemBtn">
        <i class="fa-solid fa-flag"></i> Report a Problem
      </button>
              <?php if (!empty($seller['id'])): ?>
        <button type="button" class="av-btn-primary js-client-poke-seller" data-ref-type="account" data-id="<?= $account_id ?>"><i class="fa-solid fa-hand-point-up"></i> Poke Seller</button>
        <?php endif ?>
        <a href="<?= BASE_URL ?>/profile/accounts" class="av-btn-ghost">
        <i class="fa-duotone fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><i class="fa-solid fa-trophy" style="color:rgba(255,255,255,.4);"></i> <strong><?= $rank_label ?><?= $div_label ? ' '.$div_label : '' ?></strong></span>
    <?php if (!empty($account['flex_rank'])): ?>
      <?php
        $flex_label_h = $rank_labels[(int)($account['flex_rank'] ?? 0)] ?? 'Unranked';
        $_flidx = (int)($account['flex_rank'] ?? 0);
        $flex_div_h = ($_flidx === 0 || $_flidx >= 8) ? '' : ($div_labels[(int)($account['flex_division'] ?? 0)] ?? '');
      ?>
      <span class="av-meta-pill"><i class="fa-solid fa-shield" style="color:rgba(255,255,255,.4);"></i> <strong><?= $flex_label_h ?><?= $flex_div_h ? ' '.$flex_div_h : '' ?></strong> Flex</span>
    <?php endif ?>
    <?php if (!empty($account['level'])): ?>
      <span class="av-meta-pill"><i class="fa-solid fa-arrow-up" style="color:rgba(255,255,255,.4);"></i> <strong>Lvl <?= $h($account['level']) ?></strong></span>
    <?php endif ?>
    <span class="av-meta-pill"><i class="fa-solid fa-euro-sign" style="color:rgba(255,255,255,.4);"></i> <strong><?= number_format((int)($account['price'] ?? 0) / 100, 2) ?> €</strong></span>
    <?php if (!empty($account['2fa'])): ?>
      <span class="av-meta-pill"><i class="fa-solid fa-shield-halved" style="color:#fbbf24;"></i> <strong style="color:#fbbf24;">2FA Enabled</strong></span>
    <?php endif ?>
  </div>
</div>


<!-- ── 2-COLUMN LAYOUT ── -->
<div class="row g-4 align-items-start">

  <!-- ── LEFT: chat + gallery + description ── -->
  <div class="col-12 col-lg-7">

    <?php if ($email_tutorial_type !== ''): ?>
    <!-- Email Access Tutorial -->
    <div class="av-email-tutorial mb-4">
      <div class="av-email-tutorial__head">
        <div>
          <div class="av-email-tutorial__title">
            <i class="fa-solid fa-envelope-open-text"></i>
            <?= $h($email_tutorial_title) ?>
          </div>
          <div class="av-email-tutorial__sub">
            This account uses a temporary email provider. Follow these steps to open the inbox and receive verification emails.
          </div>
        </div>
      </div>
      <div class="av-email-tutorial__body">
        <div class="av-email-tutorial__email">
          <span>Account email:</span>
          <code><?= $h($account_email_raw) ?></code>
          <button type="button" class="av-copy-btn js-copy-btn" data-copy="<?= $h($account_email_raw) ?>" aria-label="Copy email">
            <i class="fa-duotone fa-copy"></i>
          </button>
        </div>

        <?php if ($email_tutorial_type === 'inboxes'): ?>
          <ol class="av-email-tutorial__steps">
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">1</span><div class="av-email-tutorial__text">Open <strong>inboxes.com</strong>.</div></li>
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">2</span><div class="av-email-tutorial__text">Click <strong>Get my first inbox</strong>.</div></li>
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">3</span><div class="av-email-tutorial__text">Paste the full email address from above.</div></li>
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">4</span><div class="av-email-tutorial__text">You will get access to the inbox and can receive the verification email there.</div></li>
          </ol>
        <?php elseif ($email_tutorial_type === 'yopmail'): ?>
          <ol class="av-email-tutorial__steps">
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">1</span><div class="av-email-tutorial__text">Open <strong>yopmail.com</strong>.</div></li>
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">2</span><div class="av-email-tutorial__text">Enter the full email address from above.</div></li>
            <li class="av-email-tutorial__step"><span class="av-email-tutorial__num">3</span><div class="av-email-tutorial__text">Open the inbox and check for Riot or account verification emails.</div></li>
          </ol>
        <?php endif ?>

        <a class="av-email-tutorial__link" href="<?= $h($email_tutorial_url) ?>" target="_blank" rel="noopener noreferrer">
          Open email provider <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>
      </div>
    </div>
    <?php endif ?>

    <?php if ($show_email_change_notice): ?>
    <!-- Email Change Notice -->
    <div class="av-email-tutorial av-email-tutorial--notice mb-4">
      <div class="av-email-tutorial__head">
        <div>
          <div class="av-email-tutorial__title">
            <i class="fa-solid fa-circle-info"></i>
            Email access pending
          </div>
          <div class="av-email-tutorial__sub">
            No email password was provided for this account email. The seller will contact you as soon as possible to change the account email to your own address.
          </div>
        </div>
      </div>
      <div class="av-email-tutorial__body">
        <div class="av-email-tutorial__email">
          <span>Current account email:</span>
          <code><?= $h($account_email_raw) ?></code>
          <button type="button" class="av-copy-btn js-copy-btn" data-copy="<?= $h($account_email_raw) ?>" aria-label="Copy email">
            <i class="fa-duotone fa-copy"></i>
          </button>
        </div>
        <div class="av-email-tutorial__text">
          Please keep an eye on the seller chat. The seller will guide you through the email change process there.
        </div>
      </div>
    </div>
    <?php endif ?>

    <!-- Chat -->
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Seller Support Chat
        </div>
        <?php if (!empty($seller)): ?>
        <a href="<?= $h($sellerProfileUrl) ?>" style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(99,102,241,.10);border:1px solid rgba(99,102,241,.2);color:#a5b4fc;font-size:.75rem;font-weight:700;text-decoration:none;" title="View seller profile">
          <?php if (!empty($seller['icon'])): ?>
            <img src="<?= $h($seller['icon']) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(99,102,241,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($seller['username'] ?? 'S', 0, 1)) ?></span>
          <?php endif ?>
          <?= $h($seller['username'] ?? '—') ?>
        </a>
        <?php endif ?>
      </div>

      <div class="card-body chat-bg" id="chat_messages"></div>

      <div class="card-footer">
        <form class="row gx-2" id="lbChatForm" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action"     value="client_account_chat_send">
          <input type="hidden" name="account_id" value="<?= $account_id ?>">
          <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none">

          <div class="col">
            <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message">
          </div>
          <div class="col-auto d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="lbChatUploadBtn" aria-label="Attach image" title="Attach image">
              <i class="fa-duotone fa-paperclip"></i>
            </button>
            <button type="submit" class="btn btn-sm btn-primary">
              <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
              <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
          </div>

          <div class="col-12 mt-2 d-none" id="lbChatImagePreviewWrap">
            <div class="lb-chat-preview">
              <img id="lbChatImagePreview" src="" alt="preview">
              <button type="button" class="lb-chat-preview__remove" id="lbChatImageRemove" aria-label="Remove">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </form>
        <div class="text-muted small mt-2">
          Tip: You can paste a screenshot with <strong>Ctrl + V</strong>.
        </div>
      </div>
    </div>

    <!-- Gallery -->
    <?php if (!empty($images)): ?>
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color);">
        <h4 class="card-header-title mb-0" style="font-weight:700;font-size:1rem;">
          <i class="fa-duotone fa-images me-2" style="color:#9f8cff;"></i>Gallery
          <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?></span>
        </h4>
      </div>
      <div class="av-gallery-grid">
        <?php foreach ($images as $i => $img): ?>
        <div class="av-gallery-tile" data-zoom="<?= $h($img) ?>">
          <?php if ($i === 0): ?><div class="av-gallery-main-badge">MAIN</div><?php endif ?>
          <img src="<?= $h($img) ?>" alt="" loading="lazy">
        </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Description -->
    <?php if (!empty($account['description'])): ?>
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;">
        <h4 class="card-header-title mb-0" style="font-weight:700;font-size:1rem;"><i class="fa-duotone fa-align-left me-2"></i>Description</h4>
      </div>
      <div class="card-body" style="padding:16px 20px;">
        <p class="mb-0" style="font-size:.875rem;line-height:1.7;color:rgba(255,255,255,.7);"><?= nl2br($h($account['description'])) ?></p>
      </div>
    </div>
    <?php endif ?>

  </div>
  <!-- ── LEFT end ── -->


  <!-- ── RIGHT: credentials + overview + seller + tips ── -->
  <div class="col-12 col-lg-5">

    <!-- Credentials -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.22);"><i class="fa-solid fa-key" style="color:#a5b4fc;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Your Credentials</span>
      </div>
      <div class="av-creds-list">
        <?php
        $creds = [
            'in_game_name'          => [$isValorant ? 'Riot ID' : 'In-Game Name',   'fa-solid fa-gamepad'],
            'login'                 => ['Login',           'fa-solid fa-user'],
            'password'              => ['Password',        'fa-solid fa-key'],
            'email'                 => ['Account Email',   'fa-solid fa-envelope'],
            'email_password'        => ['Email Password',  'fa-solid fa-lock'],
            'delivery_instructions' => ['Instructions',    'fa-solid fa-clipboard'],
        ];
        $has_cred = false;
        foreach ($creds as $field => [$label, $icon]):
            $val = $account[$field] ?? '';
            if (empty($val) || $val === 'unverified' || $val === '-') continue;
            $has_cred = true;
            $safe = $h($val);
        ?>
        <div class="av-cred-item">
          <div class="av-cred-left">
            <i class="<?= $icon ?> av-cred-ico"></i>
            <span class="av-cred-lbl"><?= $label ?></span>
          </div>
          <div class="av-cred-right">
            <span class="av-cred-val av-sensitive js-copy-cred" data-copy="<?= $safe ?>" title="<?= $safe ?>"><?= $safe ?></span>
            <button class="av-copy-btn js-copy-btn" data-copy="<?= $safe ?>" aria-label="Copy <?= $label ?>">
              <i class="fa-duotone fa-copy"></i>
            </button>
          </div>
        </div>
        <?php endforeach ?>
        <?php if (!$has_cred): ?>
          <div style="text-align:center;padding:18px 16px;">
            <div style="font-size:.8rem;color:rgba(255,255,255,.3);font-weight:700;">No credentials available yet.</div>
          </div>
        <?php endif ?>
        <?php if (!empty($account['2fa'])): ?>
        <div class="av-cred-item">
          <div class="av-cred-left"><i class="fa-solid fa-shield-halved av-cred-ico"></i><span class="av-cred-lbl">2FA</span></div>
          <div class="av-cred-right"><span class="badge" style="background:rgba(251,191,36,.15);color:#fbbf24;font-size:.7rem;">Enabled</span></div>
        </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Getting Started tips -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.2);"><i class="fa-solid fa-lightbulb" style="color:#fbbf24;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Getting Started</span>
      </div>
      <div style="padding:4px 0 8px;">
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;">
            <i class="fa-solid fa-key"></i>
          </div>
          <div>
            <div class="av-tip-title">Change your password</div>
            <div class="av-tip-desc">Update it immediately in Riot account settings.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#10b981;">
            <i class="fa-solid fa-envelope"></i>
          </div>
          <div>
            <div class="av-tip-title">Transfer the email</div>
            <div class="av-tip-desc">Link your own email for full account ownership.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24;">
            <i class="fa-solid fa-shield"></i>
          </div>
          <div>
            <div class="av-tip-title">Enable 2FA</div>
            <div class="av-tip-desc">Use the Riot Authenticator app for extra security.</div>
          </div>
        </div>
        <div class="av-tip-item">
          <div class="av-tip-ico" style="background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;">
            <i class="fa-solid fa-headset"></i>
          </div>
          <div>
            <div class="av-tip-title">Need help?</div>
            <div class="av-tip-desc">Use the chat to contact your seller directly.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Account Overview -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Account Overview</span>
      </div>
      <div class="av-stat-grid">
        <?php
        if ($isValorant) {
          $stats = [
            ['fa-solid fa-trophy',     'Rank',         $rank_label, null],
            ['fa-solid fa-chart-line', 'Peak Rank',    !empty($game_data['val_peak_rank']) ? (function_exists('util_get_val_rank') ? util_get_val_rank((int)$game_data['val_peak_rank']) : (string)$game_data['val_peak_rank']) : '—', null],
            ['fa-solid fa-globe',      'Server',       strtoupper((string)($account['server'] ?? '—')), null],
            ['fa-solid fa-arrow-up',   'Level',        $account['level'] ?? '—', null],
            ['fa-solid fa-bullseye',   'Act',          $game_data['val_act'] ?? '—', '#60a5fa'],
            ['fa-solid fa-desktop',    'Platform',     strtoupper((string)($game_data['val_platform'] ?? 'PC')), '#a78bfa'],
            ['fa-solid fa-burst',      'Valorant Points', number_format((int)($game_data['val_points'] ?? 0)), '#f59e0b'],
            ['fa-solid fa-gem',        'Radianite',    number_format((int)($game_data['val_radianite'] ?? 0)), '#22c55e'],
            ['fa-solid fa-gun',        'Weapon Skins', number_format((int)($game_data['val_weapon_skins'] ?? 0)), '#f472b6'],
            ['fa-solid fa-chart-line', 'Win Rate',     !empty($game_data['val_winrate']) ? (int)$game_data['val_winrate'].'%' : '—', null],
            ['fa-solid fa-shield-halved', 'Ranked Ready', !empty($game_data['val_ranked_ready']) ? 'Yes' : 'No', !empty($game_data['val_ranked_ready']) ? '#fbbf24' : null],
            ['fa-solid fa-bolt',       'Delivery',     ucfirst($account['delivery_type'] ?? '—'), '#fbbf24'],
          ];
        } else {
          $flex_label = $lol_rank_labels[(int)($account['flex_rank'] ?? 0)] ?? 'Unranked';
          $_flidx     = (int)($account['flex_rank'] ?? 0);
          $flex_div   = ($_flidx === 0 || $_flidx >= 8) ? '' : ($lol_div_labels[(int)($account['flex_division'] ?? 0)] ?? '');
          $stats = [
            ['fa-solid fa-trophy',       'Solo Rank',   $rank_label . ($div_label ? ' '.$div_label : '') . (!empty($account['current_lp']) ? ' · '.(int)$account['current_lp'].'LP' : ''), null],
            ['fa-solid fa-shield',       'Flex Rank',   $flex_label . ($flex_div ? ' '.$flex_div : ''), null],
            ['fa-solid fa-globe',        'Server',      strtoupper($account['server'] ?? '—'), null],
            ['fa-solid fa-arrow-up',     'Level',       $account['level'] ?? '—', null],
            ['fa-solid fa-gem',          'Blue Essence', number_format((int)($account['blue_essence'] ?? 0)), '#60a5fa'],
            ['fa-solid fa-coins',        'Riot Points',  number_format((int)($account['riot_points'] ?? 0)), '#a78bfa'],
            ['fa-solid fa-chart-line',   'Win Rate',    !empty($account['winrate_percent']) ? (int)$account['winrate_percent'].'%' : '—', null],
            ['fa-solid fa-bolt',         'Delivery',    ucfirst($account['delivery_type'] ?? '—'), '#fbbf24'],
          ];
        }
        foreach ($stats as [$ico, $lbl, $val, $clr]):
        ?>
        <div class="av-stat-item">
          <i class="<?= $ico ?> av-stat-ico" <?= $clr ? 'style="color:'.$clr.';"' : '' ?>></i>
          <div>
            <div class="av-stat-lbl"><?= $lbl ?></div>
            <div class="av-stat-val"><?= $h((string)$val) ?></div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>

    <!-- Seller card -->
    <?php if ($seller): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.22);"><i class="fa-solid fa-store" style="color:#818cf8;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Seller</span>
      </div>
      <div class="av-seller-row">
        <a href="<?= $h($sellerProfileUrl) ?>" class="av-seller-main-link" title="View seller profile">
          <?php if (!empty($seller['icon'])): ?>
            <img src="<?= $h($seller['icon']) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;" alt="">
          <?php else: ?>
            <div class="av-seller-avi" style="background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.25);">
              <?= strtoupper(substr($seller['username'] ?? 'S', 0, 1)) ?>
            </div>
          <?php endif ?>
          <div style="min-width:0;">
            <div class="av-seller-name" style="font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9);"><?= $h($seller['username'] ?? '') ?></div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px;">Your account seller</div>
          </div>
        </a>
        <a href="#chat_messages"
           onclick="document.getElementById('chat_messages')?.closest('.card')?.scrollIntoView({behavior:'smooth'}); return false;"
           class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-duotone fa-comments"></i> Chat
        </a>
      </div>
    </div>
    <?php endif ?>

    <?php if ($isValorant && (!empty($agents) || $valAgentsDisplayCount > 0 || !empty($skins))): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-crosshairs" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Valorant Extras</span>
      </div>
      <?php if (!empty($agents)): ?>
      <div class="av-tag-section">
        <div class="av-tag-label">Agents (<?= $valAgentsDisplayCount ?>)</div>
        <div class="av-tag-list">
          <?php foreach (array_slice($agents, 0, 18) as $agent): ?>
            <span class="av-tag"><?= $h(trim($agent)) ?></span>
          <?php endforeach ?>
          <?php if (count($agents) > 18): ?><span class="av-tag av-tag--more">+<?= count($agents)-18 ?> more</span><?php endif ?>
        </div>
      </div>
      <?php elseif ($valAgentsDisplayCount > 0): ?>
      <div class="av-tag-section">
        <div class="av-tag-label">Agents</div>
        <div style="padding:8px 0;font-size:.88rem;color:rgba(255,255,255,.6);font-weight:700;">
          <i class="fa-solid fa-crosshairs me-2" style="color:#fb923c;"></i><?= $valAgentsDisplayCount ?> agents unlocked
        </div>
      </div>
      <?php endif ?>
      <?php if (!empty($skins)): ?>
      <div class="av-tag-section" style="margin-top:6px;">
        <div class="av-tag-label">Skins</div>
        <div class="av-tag-list">
          <?php foreach (array_slice($skins, 0, 18) as $skin): ?>
            <span class="av-tag av-tag--role"><?= $h(trim($skin)) ?></span>
          <?php endforeach ?>
          <?php if (count($skins) > 18): ?><span class="av-tag av-tag--more">+<?= count($skins)-18 ?> more</span><?php endif ?>
        </div>
      </div>
      <?php endif ?>
    </div>
    <?php elseif (!$isValorant && (!empty($champs) || $champsDisplayCount > 0 || !empty($roles))): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-gamepad" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Champions & Roles</span>
      </div>
      <?php if (!empty($champs)): ?>
      <div class="av-tag-section">
        <div class="av-tag-label">Champions (<?= $champsDisplayCount ?>)</div>
        <div class="av-tag-list">
          <?php foreach (array_slice($champs, 0, 15) as $c): ?>
            <span class="av-tag"><?= $h(trim($c)) ?></span>
          <?php endforeach ?>
          <?php if (count($champs) > 15): ?><span class="av-tag av-tag--more">+<?= count($champs)-15 ?> more</span><?php endif ?>
        </div>
      </div>
      <?php elseif ($champsDisplayCount > 0): ?>
      <div class="av-tag-section">
        <div class="av-tag-label">Champions</div>
        <div style="padding:8px 0;font-size:.88rem;color:rgba(255,255,255,.6);font-weight:700;">
          <i class="fa-solid fa-shield-halved me-2" style="color:#34d399;"></i><?= $champsDisplayCount ?> champions unlocked
        </div>
      </div>
      <?php endif ?>
      <?php if (!empty($roles)): ?>
      <div class="av-tag-section" style="margin-top:6px;">
        <div class="av-tag-label">Roles</div>
        <div class="av-tag-list">
          <?php foreach ($roles as $r): ?>
            <span class="av-tag av-tag--role"><?= $h(trim($r)) ?></span>
          <?php endforeach ?>
        </div>
      </div>
      <?php endif ?>
    </div>
    <?php endif ?>

  </div>
  <!-- ── RIGHT end ── -->

</div><!-- /row -->
</div><!-- /client-account-view -->


<!-- Report a Problem Modal -->
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
            ['id' => 'creds_wrong',    'icon' => 'fa-solid fa-key',           'text' => 'Login credentials are incorrect'],
            ['id' => 'acc_banned',     'icon' => 'fa-solid fa-ban',           'text' => 'Account has been banned / suspended'],
            ['id' => 'acc_locked',     'icon' => 'fa-solid fa-lock',          'text' => 'Account is locked or inaccessible'],
            ['id' => 'rank_wrong',     'icon' => 'fa-solid fa-trophy',        'text' => 'Rank / stats don\'t match the listing'],
            ['id' => 'seller_no_resp', 'icon' => 'fa-solid fa-comment-slash', 'text' => 'Seller is not responding'],
            ['id' => 'seller_rude',    'icon' => 'fa-solid fa-face-angry',    'text' => 'Seller behaviour / harassment'],
            ['id' => 'not_delivered',  'icon' => 'fa-solid fa-box-open',      'text' => 'Account was never delivered'],
            ['id' => 'other',          'icon' => 'fa-solid fa-ellipsis',      'text' => 'Other issue'],
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
        <button class="rp-submit" id="rpSubmitBtn" disabled>
          <i class="fa-solid fa-paper-plane"></i> Send Report
        </button>
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

<!-- Image lightbox -->
<div class="modal fade" id="lbImgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:90vw;">
    <div class="modal-content" style="background:rgba(0,0,0,.85);border:none;">
      <div class="modal-body text-center p-2">
        <img src="" id="lbImgModalImg" alt="" style="max-width:100%;max-height:80vh;border-radius:.5rem;">
      </div>
      <div class="modal-footer justify-content-center py-2 border-0">
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script>
(function () {
  'use strict';

  /* ── CONFIG ── */
  const AJAX_URL   = '<?= AJAX_URL ?>';
  const ACCOUNT_ID = <?= $account_id ?>;
  const CLIENT_ID  = <?= (int)(CLIENT_ID ?? 0) ?>;
  const user_type  = 'client';
  const user_id    = CLIENT_ID;

  const SELLER_NAME   = <?= json_encode($h($seller['username'] ?? 'Seller')) ?>;
  const SELLER_AVATAR = <?= json_encode((string)($seller['icon'] ?? (ICON_URL . '/03ce541a1f4bf8b06c924439ffcc8173.png'))) ?>;
  const CLIENT_AVATAR = <?= json_encode((string)((defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? (CLIENT_DATA['icon'] ?? '') : '') ?: (ICON_URL . '/8515d2c8c74a3f9bae054026f6549d91.png'))) ?>;

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
      $.post(AJAX_URL, { action: 'client_poke_seller', ref_type: btn.getAttribute('data-ref-type') || 'account', id: btn.getAttribute('data-id') || ACCOUNT_ID }, function(resp){
        var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
        if (d && d.cooldown_seconds) startCooldown(d.cooldown_seconds);
      }).always(function(){ if (!cooldownStarted) { btn.disabled = false; btn.innerHTML = oldHtml; } });
    });
  });

  /* ── Copy credentials ── */
  document.querySelectorAll('.js-copy-btn, .js-copy-cred').forEach(el => {
    el.addEventListener('click', () => {
      const val = el.getAttribute('data-copy') || '';
      if (!val) return;
      navigator.clipboard.writeText(val).then(() => {
        const i = el.querySelector('i');
        if (i) { i.className = 'fa-solid fa-check'; setTimeout(() => i.className = 'fa-duotone fa-copy', 1500); }
        if (typeof create_toast === 'function') create_toast('success', 'Copied', 'Copied to clipboard.');
      }).catch(() => {});
    });
  });

  /* ── Gallery lightbox ── */
  document.querySelectorAll('.av-gallery-tile[data-zoom]').forEach(tile => {
    tile.addEventListener('click', () => {
      const src = tile.getAttribute('data-zoom');
      if (!src) return;
      const img = document.getElementById('lbImgModalImg');
      if (img) img.src = src;
      const modal = document.getElementById('lbImgModal');
      if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    });
  });
  const lbImgModal = document.getElementById('lbImgModal');
  if (lbImgModal) lbImgModal.addEventListener('hidden.bs.modal', () => {
    const img = document.getElementById('lbImgModalImg'); if (img) img.src = '';
  });

  /* ── CHAT ── */
  let msg_none       = false;
  let chat_json      = {};
  let initial_load   = true;

  const chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
  function message_sound() { try { chat_notif.volume = 0.6; chat_notif.play(); } catch(e){} }

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str ?? ''; return txt.value.replace(/\n/g, '<br>');
  }

  function formatExactTime(ts) {
    const m = moment.unix(parseInt(ts, 10) || 0);
    return (m && m.isValid()) ? m.format('DD.MM.YYYY HH:mm') : '';
  }

  function escapeAttr(str) {
    try { return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    catch(e){ return ''; }
  }

  function getRoleBadge(sender) {
    if (sender === 'seller') return { cls: 'lb-badge--seller', label: 'Seller' };
    if (sender === 'admin')  return { cls: 'lb-badge--admin',  label: 'Admin'  };
    if (sender === 'system') return { cls: 'lb-badge--system', label: 'System' };
    return { cls: 'lb-badge--client', label: 'You' };
  }

  function getFallbackAvatar(sender) {
    return (sender === 'seller' || sender === 'admin') ? SELLER_AVATAR : CLIENT_AVATAR;
  }

  function renderTicks(msg_data) {
    const sender = String(msg_data.sender || msg_data.sender_type || '').toLowerCase();
    const seen = sender === 'client'
      ? Number(msg_data.seen_by_seller ?? 0) === 1
      : Number(msg_data.seen_by_client ?? 0) === 1;
    const delivered = true;
    if (seen) {
      const title = 'Read' + (msg_data.seen_at ? (' • ' + formatExactTime(msg_data.seen_at)) : '');
      return ` <span class="lb-msg__ticks text-primary" title="${escapeAttr(title)}"><i class="fa-solid fa-check-double"></i></span>`;
    }
    if (delivered) return ` <span class="lb-msg__ticks text-muted" title="Delivered"><i class="fa-solid fa-check-double"></i></span>`;
    return ` <span class="lb-msg__ticks text-muted" title="Sent"><i class="fa-solid fa-check"></i></span>`;
  }

  function load_message(message_id, msg_data, isGrouped) {
    const exactTime = formatExactTime(msg_data.time);
    const content   = decodeHtmlEntities(msg_data.content);

    if (msg_data.sender === 'system') {
      return `<div class="lb-syswrap"><div class="lb-sys">${content}</div><div class="lb-sys-time">${exactTime}</div></div>`;
    }

    const isMe = (msg_data.sender === user_type && String(msg_data.sender_id) === String(user_id));
    const alignClass = isMe ? 'lb-msg--end' : 'lb-msg--start';
    const headClass  = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';
    const badge  = getRoleBadge(msg_data.sender);
    const senderRole = String(msg_data.sender || msg_data.sender_type || '').toLowerCase();
    const avatar = senderRole === 'client'
      ? CLIENT_AVATAR
      : (senderRole === 'seller' ? SELLER_AVATAR : ((msg_data.sender_icon && String(msg_data.sender_icon).length) ? msg_data.sender_icon : SELLER_AVATAR));
    const name = isMe
      ? 'You'
      : (senderRole === 'seller' ? SELLER_NAME : (msg_data.sender_name || 'Support'));

    let html = `<div class="lb-msg ${alignClass}">`;
    if (!isGrouped) {
      html += `<div class="${headClass}">
        <img class="lb-msg__avatar" src="${avatar}" alt="avatar">
        <div class="lb-msg__meta"><div class="lb-msg__toprow">
          <div class="lb-msg__name">${name} <span class="lb-badge ${badge.cls}">${badge.label}</span></div>
        </div></div>
      </div>`;
    }

    const isEdited   = (msg_data.edited == 1 || msg_data.edited_at);
    const editedMark = isEdited ? ' <span class="lb-msg__edited">Edited</span>' : '';

    html += `<div class="lb-msg__bubble" data-msg-id="${message_id}">`;
    html += `<div class="lb-msg__content">${content}</div>`;
    if (isMe) {
      html += `<button type="button" class="lb-msg__edit" data-msg-id="${message_id}" title="Edit"><i class="fa-duotone fa-pen-to-square"></i></button>`;
    }
    html += `</div>`;
    html += `<div class="lb-msg__stamp">${exactTime}${editedMark}${isMe ? renderTicks(msg_data) : ''}</div>`;
    html += `</div>`;
    return html;
  }

  function update_scroll() {
    const el = document.getElementById('chat_messages');
    if (el) el.scrollTop = el.scrollHeight;
  }

  // Edit own message
  $(document).on('click', '.lb-msg__edit', function(e) {
    e.preventDefault(); e.stopPropagation();
    const $bubble  = $(this).closest('.lb-msg__bubble');
    const $content = $bubble.find('.lb-msg__content');
    const id = $bubble.data('msg-id') || $(this).data('msg-id');
    if (!id) return;
    const prev = $content.html();
    function htmlToPlain(html) {
      const tmp = document.createElement('div');
      tmp.innerHTML = String(html||'').replace(/<br\s*\/?>/gi,'\n');
      return (tmp.textContent||tmp.innerText||'').trimEnd();
    }
    const startVal = (chat_json && chat_json[id] && chat_json[id].content) ? htmlToPlain(chat_json[id].content) : htmlToPlain(prev);
    const esc = s => String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    $bubble.addClass('is-editing');
    $content.html(`<textarea class="lb-msg__editor" rows="3">${esc(startVal)}</textarea>
      <div class="lb-msg__editor-actions">
        <button type="button" class="btn btn-sm btn-secondary lb-msg__cancel">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary lb-msg__save">Save</button>
      </div>`);
    const $ta = $content.find('.lb-msg__editor');
    setTimeout(() => { try { $ta.focus(); $ta[0].setSelectionRange($ta.val().length,$ta.val().length); } catch(e){} }, 0);
    function cleanup() { $bubble.removeClass('is-editing'); }
    $content.off('click','.lb-msg__cancel').on('click','.lb-msg__cancel',() => { $content.html(prev); cleanup(); });
    $content.off('click','.lb-msg__save').on('click','.lb-msg__save',function() {
      const newText = ($ta.val()||'').trim(); if (!newText) return;
      $.post(AJAX_URL, { action:'client_account_chat_edit', account_id: ACCOUNT_ID, id, message: newText }, function(res) {
        try { res = JSON.parse(res); } catch(e){}
        if (!res || res.success !== true) { $content.html(prev); cleanup(); if (typeof create_toast==='function') create_toast('danger','Error',(res&&res.message)||'Could not edit.'); return; }
        chat_json = {}; msg_none = false; load_messages();
      }).fail(() => { $content.html(prev); cleanup(); });
    });
  });


  let lbChatReadActivated = false;
  let lbChatReadRequestRunning = false;

  function lbChatCanMarkRead() {
    if (!lbChatReadActivated) return false;
    if (document.visibilityState !== 'visible') return false;
    if (!(document.hasFocus && document.hasFocus())) return false;
    const chat = document.getElementById('chat_messages');
    if (!chat) return false;
    const rect = chat.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  function lbHasUnreadPeerMessages() {
    const list = chat_json || {};
    return Object.keys(list).some(function(key) {
      const msg = list[key] || {};
      const sender = String(msg.sender || msg.sender_type || '').toLowerCase();
      if (!sender || sender === 'system' || sender === user_type) return false;
      if (user_type === 'client') return Number(msg.seen_by_client ?? 0) !== 1;
      return Number(msg.seen_by_seller ?? 0) !== 1;
    });
  }

  function lbApplyRealtimeMessages(rawList) {
    if (!Array.isArray(rawList)) return false;
    const chat_list = {};
    rawList.forEach(function(msg, idx) {
      if (!msg || msg.deleted == 1 || msg.type === 'deleted') return;
      const row = Object.assign({}, msg);
      const sender = String(row.sender || row.sender_type || '').toLowerCase();
      row.sender = sender || row.sender;
      row.seen = sender === user_type
        ? Number(row.seen_by_seller ?? 0)
        : Number(row.seen_by_client ?? 0);
      chat_list[String(row.id ?? idx)] = row;
    });
    chat_json = chat_list;
    let chat_html = '', last_sender = '', last_sender_id = 0;
    Object.keys(chat_list).forEach(function(key) {
      const val = chat_list[key];
      const isGrouped = val.sender === last_sender && String(val.sender_id) === String(last_sender_id);
      chat_html += load_message(key, val, isGrouped);
      last_sender = val.sender; last_sender_id = val.sender_id;
    });
    $('#chat_messages').html(chat_html);
    update_scroll();
    return true;
  }

  function lbActivateChatRead() {
    if (document.visibilityState !== 'visible') return;
    lbChatReadActivated = true;
    if (!lbHasUnreadPeerMessages() || lbChatReadRequestRunning) return;
    lbChatReadRequestRunning = true;
    $.post(AJAX_URL, {
      action: 'seller_account_chat_seen',
      account_id: ACCOUNT_ID,
      viewer_role: 'client'
    }, function(resp) {
      let data = resp;
      try { if (typeof resp === 'string') data = JSON.parse(resp); } catch(e) {}
      if (data && Array.isArray(data.messages)) lbApplyRealtimeMessages(data.messages);
    }).always(function() { lbChatReadRequestRunning = false; });
  }

  function load_messages(markSeen = false) {
    const payload = { action: 'seller_account_chat_load', account_id: ACCOUNT_ID, viewer_role: 'client' };
    if (markSeen && lbChatCanMarkRead()) payload.mark_seen = 1;
    if (payload.mark_seen) lbChatReadRequestRunning = true;
    $.post(AJAX_URL, payload, function(resp) {
      let response;
      try { response = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){ return; }

      const raw_list  = response.messages || {};
      const chat_list = {};
      $.each(raw_list, function(k, v) {
        if (!v) return;
        if (v.type === 'deleted' || v.deleted == 1) return;
        chat_list[k] = v;
      });

      const msg_count = Object.keys(chat_list).length;
      window.chat_json_sig = window.chat_json_sig || '';
      const new_sig = JSON.stringify(chat_list);

      if (msg_count > 0) {
        if (new_sig !== window.chat_json_sig) {
          window.chat_json_sig = new_sig;
          chat_json = chat_list;
          let chat_html = '', last_sender = '', last_sender_id = 0;
          $.each(chat_list, function(key, val) {
            const isGrouped = (val.sender === last_sender && String(val.sender_id) === String(last_sender_id));
            chat_html += load_message(key, val, isGrouped);
            last_sender = val.sender; last_sender_id = val.sender_id;
          });
          $('#chat_messages').html(chat_html);
          update_scroll();
        }

        const last_id  = Object.keys(chat_list).pop();
        const last_msg = chat_list[last_id];
        if (!initial_load && last_msg && last_msg.sender !== user_type && last_msg.notify == 0 && last_msg.seen == 0) {
          const tabVisible = document.visibilityState === 'visible';
          const windowFocused = !!(document.hasFocus && document.hasFocus());
          if (!tabVisible || !windowFocused) message_sound();
        }
        initial_load = false;
      } else {
        if (!msg_none) {
          $('#chat_messages').html('<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet. Start the conversation!</span></div>');
          msg_none = true;
        }
      }
    }).always(function(){ lbChatReadRequestRunning = false; });
  }

  // Image attach + paste
  (function () {
    const uploadBtn = document.getElementById('lbChatUploadBtn');
    const fileInput = document.getElementById('lbChatImageInput');
    const previewW  = document.getElementById('lbChatImagePreviewWrap');
    const previewI  = document.getElementById('lbChatImagePreview');
    const removeBtn = document.getElementById('lbChatImageRemove');
    let previewUrl  = null;

    function showPreview(file) {
      if (!file || !file.type.startsWith('image/')) return;
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = URL.createObjectURL(file);
      if (previewI) previewI.src = previewUrl;
      if (previewW) previewW.classList.remove('d-none');
    }
    function clearPreview() {
      if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
      if (fileInput) fileInput.value = '';
      if (previewI) previewI.src = '';
      if (previewW) previewW.classList.add('d-none');
    }
    if (uploadBtn && fileInput) {
      uploadBtn.addEventListener('click', () => fileInput.click());
      fileInput.addEventListener('change', () => showPreview(fileInput.files && fileInput.files[0]));
    }
    if (removeBtn) removeBtn.addEventListener('click', clearPreview);

    document.addEventListener('paste', function(e) {
      const form = document.getElementById('lbChatForm');
      if (!form || !fileInput) return;
      const active = document.activeElement;
      if (!form.contains(active)) return;
      for (const it of (e.clipboardData?.items || [])) {
        if (it.kind === 'file' && it.type.startsWith('image/')) {
          const blob = it.getAsFile(); if (!blob) continue;
          const file = new File([blob], 'pasted.png', { type: blob.type });
          const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
          showPreview(file); e.preventDefault(); break;
        }
      }
    });
  })();

  // Chat form submit
  $(document).on('submit', '#lbChatForm', function(e) {
    e.preventDefault();
    const $form  = $(this);
    const $btn   = $form.find('[type=submit]');
    const $prog  = $btn.find('.indicator-progress');
    const $label = $btn.find('.indicator-label');
    $btn.prop('disabled', true);
    $label.addClass('d-none'); $prog.removeClass('d-none');

    const fd = new FormData(this);
    $.ajax({
      url: AJAX_URL, method: 'POST', data: fd, processData: false, contentType: false
    }).done(function() {
      const msgInput = document.getElementById('lbChatMessageInput');
      if (msgInput) msgInput.value = '';
      const fi = document.getElementById('lbChatImageInput');
      if (fi) fi.value = '';
      const pw = document.getElementById('lbChatImagePreviewWrap');
      if (pw) pw.classList.add('d-none');
      load_messages(); update_scroll();
    }).fail(function() {
      if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not send message.');
    }).always(function() {
      $btn.prop('disabled', false);
      $label.removeClass('d-none'); $prog.addClass('d-none');
    });
  });

  // Chat image click → lightbox
  document.addEventListener('click', function(e) {
    const img = e.target.closest('#chat_messages img');
    if (!img) return;
    e.preventDefault();
    const modal = document.getElementById('lbImgModal');
    const mImg  = document.getElementById('lbImgModalImg');
    if (!modal || !mImg) return;
    mImg.src = img.src;
    if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
  });

  function handleAccountRealtime(raw) {
    let data = raw || {};
    for (let i = 0; i < 3; i++) {
      if (data && data.data && typeof data.data === 'object') data = data.data;
      else if (data && data.payload && typeof data.payload === 'object') data = data.payload;
      else break;
    }
    const matches = String(data.account_id || '') === String(ACCOUNT_ID)
      || String(data.order_id || '') === ('acct_' + String(ACCOUNT_ID));
    if (!matches) return;
    if (Array.isArray(data.messages)) {
      lbApplyRealtimeMessages(data.messages);
      // Mark only a genuinely new realtime message as read while this page is
      // already focused. No click, focus or tab event is bound to this action.
      if (document.visibilityState === 'visible' && document.hasFocus && document.hasFocus() && lbHasUnreadPeerMessages()) {
        lbActivateChatRead();
      }
      return;
    }
    // WebSocket payloads without messages are intentionally ignored.
    // They must never trigger an Ajax chat reload.
  }

  window.lbOrderViewChatUpdate = handleAccountRealtime;

  function bindAccountChatSocket() {
    const sock = window.lbSocket || window.socket || null;
    if (!sock || sock.__lbClientAccountBound === ACCOUNT_ID) return;
    sock.__lbClientAccountBound = ACCOUNT_ID;
    try { sock.emit('join', 'clients'); sock.emit('join', 'acct_' + String(ACCOUNT_ID)); } catch(e) {}
    try { sock.on('account_chat_update', handleAccountRealtime); } catch(e) {}
    try { sock.on('chat_update', handleAccountRealtime); } catch(e) {}
  }

  // Start chat updates
  $(document).ready(function() {
    // The initial page request may mark already visible messages as read.
    // Afterwards there are no click, focus or visibility based Ajax requests.
    lbChatReadActivated = true;
    load_messages(true);
    update_scroll();
    bindAccountChatSocket();
    setTimeout(bindAccountChatSocket, 350);
    setTimeout(bindAccountChatSocket, 1200);
    window.lbAccountChatInterval && clearInterval(window.lbAccountChatInterval);
    window.lbAccountChatInterval = null;
  });

  /* ── REPORT A PROBLEM ── */
  (function () {
    const REPORT_AJAX = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');

    const overlay         = document.getElementById('rpOverlay');
    const openBtn         = document.getElementById('reportProblemBtn');
    const closeBtn        = document.getElementById('rpClose');
    const cancelBtn       = document.getElementById('rpCancelBtn');
    const submitBtn       = document.getElementById('rpSubmitBtn');
    const formWrap        = document.getElementById('rpFormWrap');
    const successWrap     = document.getElementById('rpSuccessWrap');
    const successCloseBtn = document.getElementById('rpSuccessClose');
    const detailsEl       = document.getElementById('rpDetails');
    const problemOpts     = document.querySelectorAll('.rp-problem-opt');

    let selectedIssue = null;

    function openModal() {
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function closeModal() {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      setTimeout(() => {
        selectedIssue = null;
        problemOpts.forEach(o => o.classList.remove('is-selected'));
        if (detailsEl) detailsEl.value = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
        formWrap.style.display = '';
        successWrap.style.display = 'none';
      }, 220);
    }

    if (openBtn)          openBtn.addEventListener('click', openModal);
    if (closeBtn)         closeBtn.addEventListener('click', closeModal);
    if (cancelBtn)        cancelBtn.addEventListener('click', closeModal);
    if (successCloseBtn)  successCloseBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeModal();
    });
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });

    // Problem selection
    problemOpts.forEach(opt => {
      opt.addEventListener('click', function() {
        problemOpts.forEach(o => o.classList.remove('is-selected'));
        this.classList.add('is-selected');
        this.querySelector('input[type="radio"]').checked = true;
        selectedIssue = this.getAttribute('data-id');
        submitBtn._label = this.querySelector('.rp-problem-text')?.textContent || selectedIssue;
        submitBtn.disabled = false;
      });
    });

    // Submit → Discord webhook
    submitBtn.addEventListener('click', async function() {
      if (!selectedIssue) return;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…';

      const issueLabel   = submitBtn._label || selectedIssue;
      const details      = detailsEl ? detailsEl.value.trim() : '';
      const accountTitle = <?= json_encode($page_title) ?>;
      const accountId    = <?= $account_id ?>;
      const sellerName   = <?= json_encode($h($seller['username'] ?? '—')) ?>;
      const clientId     = CLIENT_ID;
      const accountUrl   = `<?= BASE_URL ?>/profile/accounts/${accountId}`;
      const adminUrl     = `<?= ADMN_URL ?>/selling-account/${accountId}`;

      const payload = {
        username: 'Account Reports',
        embeds: [{
          title: '🚨 Account Problem Report',
          color: 0xef4444,
          fields: [
            { name: '🎮 Account',    value: `**${accountTitle}** (#S${accountId})`, inline: true  },
            { name: '🏪 Seller',     value: sellerName,                              inline: true  },
            { name: '👤 Client',     value: `#${clientId}`,                          inline: true  },
            { name: '⚠️ Issue',      value: issueLabel,                              inline: false },
            ...(details ? [{ name: '📝 Details', value: details.substring(0, 1000), inline: false }] : []),
            { name: '🔗 Admin',      value: `[View in Admin Panel](${adminUrl})`, inline: false },
          ],
          footer: { text: `Reported via lolboost.gg` },
          timestamp: new Date().toISOString(),
        }]
      };

      try {
        const fd = new FormData();
        fd.set('action', 'client_report_problem');
        fd.set('ref_type', 'account');
        fd.set('ref_id', String(accountId));
        fd.set('issue', selectedIssue);
        fd.set('issue_label', issueLabel);
        fd.set('details', details);
        const res = await fetch(REPORT_AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
        const d = await res.json();
        if (d && d.success) {
          formWrap.style.display = 'none';
          successWrap.style.display = '';
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

})();
</script>
<?php
$can_review       = $can_review ?? false;
$already_reviewed = $already_reviewed ?? false;
$seller_id_rv     = (int)($seller['id'] ?? $account['seller_id'] ?? 0);
$account_id_rv    = (int)($account['id'] ?? 0);
$seller_name_rv   = htmlspecialchars($seller['username'] ?? 'the seller');
$seller_icon_rv   = htmlspecialchars($seller['icon'] ?? '');
?>
<?php if ($can_review && !$already_reviewed && $seller_id_rv): ?>
<style>
.lb-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.10);border-radius:18px;}
.lb-modal .modal-header{padding:1.1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.07);}
.lb-modal .modal-footer{padding:.9rem 1.25rem;border-top:1px solid rgba(255,255,255,.07);}
.lb-modal .lb-modal-head{display:flex;align-items:flex-start;gap:.85rem;min-width:0;}
.lb-modal .lb-modal-ico{width:46px;height:46px;border-radius:16px;display:grid;place-items:center;background:rgba(88,101,242,.14);border:1px solid rgba(88,101,242,.26);color:#cfd5ff;flex:0 0 auto;}
.lb-modal .lb-modal-headtxt{min-width:0;}
.lb-modal .lb-modal-title{margin:0;font-weight:950;font-size:1.05rem;line-height:1.2;}
.lb-modal .lb-modal-sub{margin:.25rem 0 0;opacity:.72;font-size:.9rem;line-height:1.35;}
.lb-modal .lb-modal-x{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.85);transition:.15s ease;flex:0 0 auto;}
.lb-modal .lb-modal-x:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.16);color:#fff;transform:translateY(-1px);}
.lb-star-sr{width:42px;height:42px;border-radius:14px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.03);display:grid;place-items:center;transition:.15s ease;padding:0;cursor:pointer;}
.lb-star-sr:hover{transform:translateY(-1px);background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.16);}
.lb-star-sr svg{width:26px;height:26px;}
.lb-star-sr svg path{fill:transparent;stroke:rgba(31,230,198,.60);stroke-width:2;transition:fill .12s,stroke .12s,filter .12s;}
.lb-star-sr.is-on svg path{fill:rgba(31,230,198,1);stroke:rgba(31,230,198,1);filter:drop-shadow(0 8px 18px rgba(31,230,198,.22));}
.sr-review-card{border-radius:16px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);}
.sr-suggestion-pill{border:1px solid rgba(88,101,242,.22);background:rgba(88,101,242,.10);color:#dbe1ff;border-radius:999px;padding:.45rem .8rem;font-size:.78rem;font-weight:700;line-height:1;transition:.15s ease;cursor:pointer;}
.sr-suggestion-pill:hover{background:rgba(88,101,242,.18);border-color:rgba(88,101,242,.34);transform:translateY(-1px);}
.sr-suggestion-pill.is-active{background:rgba(88,101,242,.24);border-color:rgba(88,101,242,.42);color:#fff;box-shadow:0 8px 18px rgba(88,101,242,.18);}
</style>

<!-- Completed Feedback Modal -->
<div id="sr_completed_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-party-horn"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Purchase complete 🎉</h5>
            <p class="lb-modal-sub">How was your experience with <?= $seller_name_rv ?>?</p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($seller_icon_rv): ?>
                  <span class="avatar" style="width:44px;height:44px;"><img class="avatar-img" src="<?= $seller_icon_rv ?>" alt=""></span>
                <?php else: ?>
                  <span class="avatar" style="width:44px;height:44px;background:rgba(88,101,242,.2);border-radius:50%;display:grid;place-items:center;font-weight:900;color:#cfd5ff;font-size:1.1rem;"><?= strtoupper(substr($seller['username'] ?? 'S', 0, 1)) ?></span>
                <?php endif; ?>
                <div>
                  <div class="fw-bold">Rate <?= $seller_name_rv ?></div>
                  <div class="text-muted small">Helps other buyers find great sellers.</div>
                </div>
              </div>
              <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#sr_leave_review_md">
                <i class="fa-duotone fa-star me-2"></i> Leave a Review
              </button>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="fw-bold mb-1">Review us on Trustpilot</div>
              <div class="text-muted small mb-3">Tap a star to open Trustpilot in a new tab.</div>
              <div id="sr_tp_stars" class="d-flex gap-2 mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" class="lb-star-sr" data-index="<?= $i ?>">
                    <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
                  </button>
                <?php endfor; ?>
              </div>
              <a class="btn btn-white border" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank" rel="noopener">
                <i class="fa-duotone fa-arrow-up-right-from-square me-2"></i> Open Trustpilot
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" id="sr_dismiss_btn" class="btn btn-link text-muted p-0" data-bs-dismiss="modal">I don't want to review now</button>
        <div class="small text-muted">You can review anytime from this page.</div>
      </div>
    </div>
  </div>
</div>

<!-- Leave Review Modal -->
<div id="sr_leave_review_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-star"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Leave a Review</h5>
            <p class="lb-modal-sub"><?= $seller_name_rv ?></p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="sr-review-card p-3 mb-3">
          <div class="fw-bold mb-1">How would you rate <?= $seller_name_rv ?>?</div>
          <div class="text-muted small mb-3">1 = poor, 5 = excellent</div>
          <div id="sr_review_stars" class="d-flex gap-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" class="lb-star-sr" data-index="<?= $i ?>">
                <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
              </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" id="sr_rating_val" value="0">
        </div>
        <div class="sr-review-card p-3">
          <div class="fw-bold mb-2">Comment <span class="text-muted fw-normal" style="font-size:.82rem;">(Optional)</span></div>
          <textarea id="sr_comment_val" class="form-control" rows="4" placeholder="Share your experience..." style="resize:none;"></textarea>
          <div class="text-muted small mt-2 mb-2">Quick suggestions, tap one if you do not want to type everything manually.</div>
          <div id="sr_comment_suggestions" class="d-flex flex-wrap gap-2">
            <button type="button" class="sr-suggestion-pill" data-text="Fast delivery, great communication, and everything was exactly as described.">Fast delivery</button>
            <button type="button" class="sr-suggestion-pill" data-text="Very friendly seller, smooth transaction, and I would definitely buy again.">Friendly seller</button>
            <button type="button" class="sr-suggestion-pill" data-text="The account was exactly as described and the whole process was smooth and easy.">As described</button>
            <button type="button" class="sr-suggestion-pill" data-text="Good experience overall, quick support, and the purchase went without any issues.">Good overall</button>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <p id="sr_review_err" class="text-danger small mb-0"></p>
        <button type="button" id="sr_review_submit" class="btn btn-primary">
          Submit Review <i class="fa-duotone fa-paper-plane ms-2"></i>
          <span id="sr_review_spin" class="spinner-border spinner-border-sm d-none ms-1" role="status"></span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const SELLER_ID   = <?= $seller_id_rv ?>;
  const ACCOUNT_ID  = <?= $account_id_rv ?>;
  const key         = 'lb_sr_popup_' + ACCOUNT_ID;
  const isDismissed  = ()=>{ try{return localStorage.getItem(key)==='1';}catch(e){return false;} };
  const markDismissed= ()=>{ try{localStorage.setItem(key,'1');}catch(e){} };

  document.addEventListener('DOMContentLoaded', function(){
    if(isDismissed()) return;
    const el=document.getElementById('sr_completed_md');
    if(!el||!window.bootstrap) return;
    const modal=bootstrap.Modal.getOrCreateInstance(el);
    const dismissBtn=document.getElementById('sr_dismiss_btn');
    if(dismissBtn) dismissBtn.addEventListener('click',markDismissed);
    el.querySelectorAll('[data-bs-target="#sr_leave_review_md"]').forEach(function(b){b.addEventListener('click',markDismissed);});
    setTimeout(function(){
      if(document.querySelector('.modal.show')) return;
      if(isDismissed()) return;
      modal.show();
    },700);
  });

  // Trustpilot stars
  const tpWrap=document.getElementById('sr_tp_stars');
  if(tpWrap){
    const tpStars=Array.from(tpWrap.querySelectorAll('.lb-star-sr'));
    tpStars.forEach(function(s){
      s.addEventListener('mouseover',function(){const v=parseInt(this.dataset.index);tpStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=v);});});
      s.addEventListener('mouseout',function(){tpStars.forEach(function(x){x.classList.remove('is-on');});});
      s.addEventListener('click',function(){markDismissed();window.open('https://www.trustpilot.com/evaluate/lolboost.gg?stars='+this.dataset.index,'_blank');});
    });
  }

  // Review stars
  const rvWrap=document.getElementById('sr_review_stars');
  if(rvWrap){
    const rvStars=Array.from(rvWrap.querySelectorAll('.lb-star-sr'));
    const hidden=document.getElementById('sr_rating_val');
    let selected=0;
    rvStars.forEach(function(s){
      s.addEventListener('mouseover',function(){const v=parseInt(this.dataset.index);rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=v);});});
      s.addEventListener('mouseout',function(){rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=selected);});});
      s.addEventListener('click',function(){selected=parseInt(this.dataset.index);if(hidden)hidden.value=selected;rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=selected);});});
    });
  }

  // Quick suggestions
  const suggestionWrap=document.getElementById('sr_comment_suggestions');
  const commentInput=document.getElementById('sr_comment_val');
  if(suggestionWrap && commentInput){
    const suggestionButtons=Array.from(suggestionWrap.querySelectorAll('.sr-suggestion-pill'));
    suggestionButtons.forEach(function(btn){
      btn.addEventListener('click',function(){
        const text=(this.dataset.text||'').trim();
        if(!text) return;
        commentInput.value=text;
        commentInput.focus();
        try{ commentInput.setSelectionRange(commentInput.value.length, commentInput.value.length); }catch(e){}
        suggestionButtons.forEach(function(x){ x.classList.remove('is-active'); });
        this.classList.add('is-active');
      });
    });
    commentInput.addEventListener('input',function(){
      const current=(commentInput.value||'').trim();
      suggestionButtons.forEach(function(btn){
        btn.classList.toggle('is-active', current !== '' && current === (btn.dataset.text||'').trim());
      });
    });
  }

  // Submit
  const submitBtn=document.getElementById('sr_review_submit');
  const submitSpin=document.getElementById('sr_review_spin');
  const errEl=document.getElementById('sr_review_err');
  if(submitBtn) submitBtn.addEventListener('click',function(){
    const rating=parseInt((document.getElementById('sr_rating_val')||{}).value||0);
    const comment=((document.getElementById('sr_comment_val')||{}).value||'').trim();
    if(errEl) errEl.textContent='';
    if(rating<1||rating>5){if(errEl)errEl.textContent='Please select a star rating.';return;}
    submitBtn.disabled=true;
    if(submitSpin) submitSpin.classList.remove('d-none');
    var fd=new FormData();
    fd.append('action','submit_seller_review');
    fd.append('seller_id',SELLER_ID);
    fd.append('purchase_id',ACCOUNT_ID);
    fd.append('rating',rating);
    fd.append('comment',comment);
    fetch(typeof AJAX_URL!=='undefined'?AJAX_URL:'/ajax',{method:'POST',body:fd,credentials:'same-origin'})
    .then(function(r){return r.json();})
    .then(function(res){
      var t=res.sendToast||{};
      if(t.type==='success'||t.type==='warning'){
        markDismissed();
        const m=bootstrap.Modal.getInstance(document.getElementById('sr_leave_review_md'));
        if(m) m.hide();
        if(typeof create_toast==='function') create_toast(t.type,t.title||'Done',t.message||'Review submitted!');
      } else {
        if(errEl) errEl.textContent=t.message||'Something went wrong.';
        submitBtn.disabled=false;
        if(submitSpin) submitSpin.classList.add('d-none');
      }
    })
    .catch(function(){
      if(errEl) errEl.textContent='Could not submit. Try again.';
      submitBtn.disabled=false;
      if(submitSpin) submitSpin.classList.add('d-none');
    });
  });
})();
</script>
<?php endif; ?>
<?= $this->end() ?>
