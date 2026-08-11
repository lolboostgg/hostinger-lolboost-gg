<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'My Accounts | LoLBoost.gg', 'h1' => 'My Accounts', 'description' => 'View your purchased LoL accounts.']]) ?>
<?= $this->start('styles') ?>
<style>
/* ═══════════════════════════════════
   My Accounts — Design System
   ═══════════════════════════════════ */

/* ── Section header ── */
.cl-accounts-page .av-section-head {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
}
.cl-accounts-page .av-section-title {
  font-size: .72rem; font-weight: 900; text-transform: uppercase;
  letter-spacing: .09em; color: rgba(255,255,255,.38);
  display: flex; align-items: center; gap: 8px;
}
.cl-accounts-page .av-section-title i { color: #6d5cff; font-size: .85rem; }
.cl-accounts-page .av-section-title span {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 22px; height: 22px; padding: 0 7px; border-radius: 99px;
  font-size: .68rem; font-weight: 800;
  background: rgba(109,92,255,.15); border: 1px solid rgba(109,92,255,.25); color: #c4b5fd;
}

/* ── Card shell ── */
.av-acc-card {
  background: #25282a;
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 20px; overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,.22);
  transition: border-color .15s, transform .15s, box-shadow .2s;
  display: flex; flex-direction: column; height: 100%;
}
/* Push footer to bottom for equal card heights */
.av-acc-card__creds { }
.av-acc-card__spacer { flex: 1; }
.av-acc-card:hover {
  border-color: rgba(255,255,255,.13);
  transform: translateY(-3px);
  box-shadow: 0 12px 36px rgba(0,0,0,.35);
}
.av-acc-card--boost {
  border-color: rgba(250,204,21,.15);
  background: linear-gradient(160deg, rgba(250,204,21,.05) 0%, #25282a 60%);
}
.av-acc-card--boost:hover { border-color: rgba(250,204,21,.35); box-shadow: 0 12px 36px rgba(250,204,21,.1); }
.av-acc-card--marketplace {
  border-color: rgba(56,189,248,.15);
  background: linear-gradient(160deg, rgba(56,189,248,.05) 0%, #25282a 60%);
}
.av-acc-card--marketplace:hover { border-color: rgba(56,189,248,.35); box-shadow: 0 12px 36px rgba(56,189,248,.1); }

/* ── Card top bar ── */
.av-acc-card__top {
  display: flex; align-items: center; gap: 13px;
  padding: 16px 18px 14px;
  border-bottom: 1px solid rgba(255,255,255,.05);
  position: relative;
}
/* Accent line at top */
.av-acc-card--boost .av-acc-card__top::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, #facc15, #fde68a, transparent);
  border-radius: 20px 20px 0 0;
}
.av-acc-card--marketplace .av-acc-card__top::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, #38bdf8, #7dd3fc, transparent);
  border-radius: 20px 20px 0 0;
}

/* ── Rank icon ── */
.av-acc-card__rank-ico {
  width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
  background: rgba(250,204,21,.1); border: 1px solid rgba(250,204,21,.2);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 12px rgba(250,204,21,.1);
}
.av-acc-card--marketplace .av-acc-card__rank-ico {
  background: rgba(56,189,248,.1); border-color: rgba(56,189,248,.22);
  box-shadow: 0 2px 12px rgba(56,189,248,.1);
}
.av-acc-card__rank-ico img { width: 26px; height: 26px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,.5)); }

/* ── Game icon ── */
.av-acc-card__game-ico {
  width: 16px; height: 16px; border-radius: 5px; object-fit: cover; flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.1);
}

/* ── Buy dropdown ── */
.av-buy-switch { position: relative; }
.av-buy-switch__toggle { min-width: 132px; justify-content: center; }
.av-buy-switch__menu {
  position: absolute; right: 0; top: calc(100% + 8px); min-width: 200px; z-index: 30;
  padding: 8px; border-radius: 16px; background: #1e2022; border: 1px solid rgba(255,255,255,.09);
  box-shadow: 0 20px 48px rgba(0,0,0,.45); display: none;
}
.av-buy-switch.is-open .av-buy-switch__menu { display: block; }
.av-buy-switch__item {
  display: flex; align-items: center; gap: 11px; width: 100%; padding: 11px 12px;
  border-radius: 11px; text-decoration: none; color: rgba(255,255,255,.82);
  border: 1px solid transparent; background: transparent; font-size: .78rem; font-weight: 800;
  transition: all .12s;
}
.av-buy-switch__item:hover { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.09); color: #fff; }
.av-buy-switch__item img { width: 22px; height: 22px; border-radius: 7px; object-fit: cover; }
.av-buy-switch__meta { display:flex; flex-direction:column; align-items:flex-start; line-height:1.15; }
.av-buy-switch__meta small { font-size: .63rem; font-weight: 700; color: rgba(255,255,255,.3); margin-top: 2px; }

/* ── Card info block ── */
.av-acc-card__info { flex: 1; min-width: 0; }
.av-acc-card__title {
  font-size: .92rem; font-weight: 800; color: rgba(255,255,255,.92);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
}
.av-acc-card__sub {
  font-size: .7rem; color: rgba(255,255,255,.3); margin-top: 3px;
  display: flex; align-items: center; gap: 5px; flex-wrap: wrap;
}

/* ── Status badge ── */
.av-acc-card__type-tag {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 99px; font-size: .67rem; font-weight: 900;
  flex-shrink: 0; text-transform: uppercase; letter-spacing: .05em;
}
.av-acc-card__type-tag--smurf {
  background: rgba(250,204,21,.1); border: 1px solid rgba(250,204,21,.25); color: #facc15;
}
.av-acc-card__type-tag--marketplace {
  background: rgba(56,189,248,.1); border: 1px solid rgba(56,189,248,.25); color: #38bdf8;
}

/* ── Stats grid ── */
.av-acc-card__stats {
  display: grid; grid-template-columns: repeat(3, 1fr);
  border-bottom: 1px solid rgba(255,255,255,.05);
  background: rgba(0,0,0,.1);
}
.av-acc-card__stat { padding: 10px 16px; border-right: 1px solid rgba(255,255,255,.05); }
.av-acc-card__stat:last-child { border-right: 0; }
.av-acc-card__stat-lbl {
  font-size: .58rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .07em; color: rgba(255,255,255,.25); margin-bottom: 4px;
}
.av-acc-card__stat-val {
  font-size: .82rem; font-weight: 800; color: rgba(255,255,255,.85);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Credential chips ── */
.av-acc-card__creds {
  padding: 12px 16px; display: grid; grid-template-columns: 1fr; gap: 7px;
  border-bottom: 1px solid rgba(255,255,255,.05);
}
.av-cred-chip {
  display: flex; align-items: flex-start; gap: 6px; padding: 7px 10px;
  border-radius: 9px; width: 100%; max-width: none;
  background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);
  font-size: .72rem; cursor: pointer; transition: border-color .12s, background .12s, box-shadow .12s;
}
.av-cred-chip:hover { background: rgba(109,92,255,.1); border-color: rgba(109,92,255,.3); box-shadow: 0 2px 8px rgba(109,92,255,.12); }
.av-cred-chip.copied { border-color: rgba(74,222,128,.35); background: rgba(74,222,128,.07); }
.av-cred-chip__lbl { color: rgba(255,255,255,.28); font-weight: 700; white-space: nowrap; flex-shrink: 0; font-size: .65rem; text-transform: uppercase; letter-spacing: .04em; }
.av-cred-chip__val { font-family: ui-monospace,monospace; font-weight: 700; color: rgba(255,255,255,.78); white-space: normal; overflow: visible; text-overflow: clip; overflow-wrap: anywhere; word-break: break-word; min-width: 0; flex: 1; line-height: 1.35; }
.av-cred-chip__ico { color: rgba(255,255,255,.18); flex-shrink: 0; font-size: .62rem; transition: color .12s; }
.av-cred-chip:hover .av-cred-chip__ico { color: #c4b5fd; }
.av-cred-chip.copied .av-cred-chip__ico { color: #4ade80; }

/* ── Footer ── */
.av-acc-card__footer {
  padding: 11px 16px;
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  background: rgba(0,0,0,.08);
}
.av-acc-card__date {
  font-size: .7rem; color: rgba(255,255,255,.25);
  display: flex; align-items: center; gap: 5px;
}
.av-acc-card__date::before {
  content: '';
  display: inline-block; width: 5px; height: 5px; border-radius: 50%;
  background: rgba(255,255,255,.15);
}

/* ── Type tag ── */
.av-acc-type-tag {
  font-size: .6rem; font-weight: 900; text-transform: uppercase;
  letter-spacing: .06em; color: rgba(255,255,255,.2);
}

/* ── Buttons ── */
.av-acc-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: 7px 15px; border-radius: 11px; font-size: .78rem; font-weight: 800;
  cursor: pointer; text-decoration: none; transition: all .12s;
}
.av-acc-btn:hover { transform: translateY(-1px); }
.av-acc-btn--primary { background: rgba(109,92,255,.22); border: 1px solid rgba(109,92,255,.32); color: #c4b5fd; }
.av-acc-btn--primary:hover { background: rgba(109,92,255,.35); border-color: rgba(109,92,255,.5); color: #e9d5ff; box-shadow: 0 4px 16px rgba(109,92,255,.2); }


.av-acc-card__top-main {
  display: flex; align-items: flex-start; gap: 13px; min-width: 0; flex: 1;
}
.av-acc-card__quick {
  display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0;
}
.av-acc-card__data-list {
  padding: 12px 16px;
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;
  border-bottom: 1px solid rgba(255,255,255,.05);
  background: rgba(0,0,0,.06);
}
.av-data-item {
  min-width: 0; padding: 9px 10px; border-radius: 12px;
  background: rgba(255,255,255,.025); border: 1px solid rgba(255,255,255,.06);
}
.av-data-item--wide { grid-column: 1 / -1; }
.av-data-item__lbl {
  font-size: .58rem; font-weight: 900; text-transform: uppercase; letter-spacing: .07em;
  color: rgba(255,255,255,.28); margin-bottom: 4px;
}
.av-data-item__val {
  font-size: .75rem; line-height: 1.35; font-weight: 750; color: rgba(255,255,255,.78);
  overflow-wrap: anywhere;
}
.av-acc-card__footer-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
.av-acc-btn--danger { background: rgba(239,68,68,.09); border: 1px solid rgba(239,68,68,.24); color: #f87171; }
.av-acc-btn--danger:hover { background: rgba(239,68,68,.16); border-color: rgba(239,68,68,.4); color: #fca5a5; box-shadow: 0 4px 16px rgba(239,68,68,.12); }

/* ── Report Modal ── */
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
.rp-modal-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,.07); display: flex; justify-content: flex-end; gap: 8px; }
.rp-cancel, .rp-submit { border: 0; border-radius: 11px; padding: 9px 14px; font-size: .82rem; font-weight: 800; cursor: pointer; transition: all .12s; }
.rp-cancel { background: rgba(255,255,255,.06); color: rgba(255,255,255,.68); }
.rp-cancel:hover { background: rgba(255,255,255,.1); color: #fff; }
.rp-submit { background: rgba(239,68,68,.9); color: #fff; }
.rp-submit:hover:not(:disabled) { background: #ef4444; transform: translateY(-1px); }
.rp-submit:disabled { opacity: .45; cursor: not-allowed; }
.rp-success { padding: 34px 20px; text-align: center; }
.rp-success-ico { font-size: 2rem; margin-bottom: 10px; }
.rp-success-title { font-size: 1rem; font-weight: 900; color: rgba(255,255,255,.9); margin-bottom: 4px; }
.rp-success-sub { font-size: .82rem; color: rgba(255,255,255,.45); }

/* ── Empty state ── */
.av-empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 56px 20px; text-align: center;
  border: 1px dashed rgba(255,255,255,.08); border-radius: 20px;
  background: rgba(255,255,255,.01);
}
.av-empty__ico   { font-size: 2.6rem; opacity: .18; margin-bottom: 12px; }
.av-empty__title { font-size: .95rem; font-weight: 800; color: rgba(255,255,255,.32); margin-bottom: 6px; }
.av-empty__sub   { font-size: .8rem; color: rgba(255,255,255,.18); }

/* ── Responsive ── */

/* Clean account overview */
.cl-accounts-page .row.g-3 { --bs-gutter-x: 18px; --bs-gutter-y: 18px; }
.av-acc-card { background: linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.012)), #202326; border-radius: 22px; }
.av-acc-card__top { align-items: flex-start; padding: 18px; }
.av-acc-card__quick { gap: 0; padding-top: 2px; }
.av-acc-card__footer { padding: 13px 16px; }
.av-acc-card__footer-actions .av-acc-btn--danger { opacity: .88; }
.av-smurf-panel { padding: 14px 16px 16px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; border-bottom: 1px solid rgba(255,255,255,.055); background: rgba(0,0,0,.055); }
.av-smurf-row { min-width: 0; position: relative; padding: 12px 42px 12px 13px; border-radius: 15px; background: rgba(255,255,255,.035); border: 1px solid rgba(255,255,255,.075); cursor: pointer; transition: border-color .12s, background .12s, transform .12s; }
.av-smurf-row:hover { background: rgba(109,92,255,.075); border-color: rgba(109,92,255,.26); transform: translateY(-1px); }
.av-smurf-row.copied { background: rgba(74,222,128,.07); border-color: rgba(74,222,128,.32); }
.av-smurf-row--data { grid-column: 1 / -1; cursor: default; }
.av-smurf-label { display: flex; align-items: center; gap: 7px; margin-bottom: 7px; color: rgba(255,255,255,.38); font-size: .62rem; font-weight: 900; text-transform: uppercase; letter-spacing: .075em; }
.av-smurf-value { color: rgba(255,255,255,.88); font-size: .82rem; line-height: 1.35; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 760; overflow-wrap: anywhere; }
.av-smurf-value--data { min-height: 42px; max-height: none; white-space: normal; }
.av-smurf-copy { position: absolute; top: 12px; right: 12px; width: 24px; height: 24px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.25); background: rgba(255,255,255,.035); border: 1px solid rgba(255,255,255,.06); }
.av-smurf-row:hover .av-smurf-copy { color: #c4b5fd; }
.av-smurf-row.copied .av-smurf-copy { color: #4ade80; }
@media (max-width: 768px) { .av-smurf-panel { grid-template-columns: 1fr; } .av-smurf-row--data { grid-column: auto; } }

@media (max-width: 576px) {
  .av-acc-card__top { align-items: flex-start; }
  .av-acc-card__top-main { align-items:flex-start; }
  .av-acc-card__quick { align-items:flex-start; width:100%; }
  .av-acc-card__data-list { grid-template-columns: 1fr; }
  .av-data-item--wide { grid-column: auto; }
  .av-acc-card__stats { grid-template-columns: repeat(2, 1fr); }
  .av-acc-card__stat:nth-child(2) { border-right: 0; }
  .av-acc-card__stat:nth-child(3) { border-top: 1px solid rgba(255,255,255,.05); border-right: 0; }
}
/* Never truncate account credentials */
.av-cred-chip__val { overflow: visible !important; text-overflow: clip !important; white-space: normal !important; overflow-wrap: anywhere !important; word-break: break-word !important; max-width: none !important; }
@media (max-width: 576px) {
  .av-cred-chip { align-items: flex-start; }
  .av-cred-chip__val { white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
}
</style>
<?= $this->end() ?>

<?php
$decode_html = function($v) {
    $s = (string)($v ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $s) break;
        $s = $decoded;
    }
    return $s;
};
$h = function ($v) use ($decode_html) {
    if ($v === null) return '';
    $s = $decode_html($v);
    if ($s === '' || $s === 'unverified' || $s === '-') return '';
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$accounts     = $accounts     ?? [];
$lol_accounts = $lol_accounts ?? [];
$lolGameIcon = ASSET_URL . '/website/images/icons/league-of-legends.png';
$valGameIcon = ASSET_URL . '/website/images/icons/valorant.png';

// Game / rank helpers
$lol_rank_labels = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
$lol_div_labels  = ['','IV','III','II','I'];
$gameOf = function(array $account) {
    $game = strtolower((string)($account['game'] ?? ''));
    if ($game === 'val' || (int)($account['game_id'] ?? 0) === 2) return 'val';
    return 'lol';
};
$lolRankStr = function($rank, $div) use ($lol_rank_labels, $lol_div_labels) {
    $i = (int)$rank;
    $r = $lol_rank_labels[$i] ?? 'Unranked';
    if ($i >= 1 && $i <= 7) $r .= ' ' . ($lol_div_labels[(int)$div] ?? '');
    return trim($r);
};
$valRankStr = function(array $account) {
    $rankLabel = trim((string)($account['rank_label'] ?? ''));
    if ($rankLabel !== '') return $rankLabel;
    $rank = (int)($account['rank'] ?? 0);
    if (function_exists('util_get_val_rank')) return (string)util_get_val_rank($rank);
    $fallback = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Diamond','Ascendant','Immortal','Radiant'];
    return $fallback[$rank] ?? 'Unranked';
};

// Merge — tag each with source table
$all = [];
foreach ($accounts as $a) {
    $all[] = ['_type' => 'boost'] + $a;
}
foreach ($lol_accounts as $a) {
    $all[] = ['_type' => 'marketplace'] + $a;
}

// Enrich boost accounts: look up package name + server from account_packages
$boostPackageIds = array_filter(array_unique(array_map(function($a) {
    return isset($a['_type']) && $a['_type'] === 'boost' ? (int)($a['package_id'] ?? 0) : 0;
}, $all)));
$packageMap = [];
if (!empty($boostPackageIds) && function_exists('db_get_rows')) {
    $pkgRows = db_get_rows('account_packages', []) ?: [];
    foreach ($pkgRows as $pkg) {
        $packageMap[(int)$pkg['id']] = $pkg;
    }
} elseif (!empty($boostPackageIds)) {
    // Fallback: direct DB query
    global $db;
    if (isset($db)) {
        $ids = implode(',', array_map('intval', $boostPackageIds));
        $pkgRows = $db->run("SELECT id, name, server FROM account_packages WHERE id IN ($ids)") ?: [];
        foreach ($pkgRows as $pkg) {
            $packageMap[(int)$pkg['id']] = $pkg;
        }
    }
}
// Attach package data to boost accounts
foreach ($all as &$a) {
    if (($a['_type'] ?? '') === 'boost' && !empty($a['package_id'])) {
        $pkg = $packageMap[(int)$a['package_id']] ?? null;
        if ($pkg) {
            $a['package_name'] = $pkg['name'] ?? '';
            $a['package_server'] = $pkg['server'] ?? '';
        }
    }
}
unset($a);

// Sort newest first (sold_at > created_at)
usort($all, function($a, $b) {
    $ta = strtotime($a['sold_at'] ?? $a['created_at'] ?? '2000-01-01');
    $tb = strtotime($b['sold_at'] ?? $b['created_at'] ?? '2000-01-01');
    return $tb - $ta;
});
?>

<div class="cl-accounts-page">

  <div class="av-section-head">
    <div class="av-section-title">
      <i class="fa-duotone fa-gamepad-modern"></i>
      All Accounts
      <span><?= count($all) ?></span>
    </div>
    <div class="av-buy-switch" data-buy-switch>
      <button type="button" class="av-acc-btn av-acc-btn--primary av-buy-switch__toggle" style="font-size:.72rem;padding:5px 11px;" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-plus"></i> Buy Account
      </button>
      <div class="av-buy-switch__menu">
        <a href="<?= BASE_URL ?>/lol/accounts" class="av-buy-switch__item">
          <img src="<?= htmlspecialchars($lolGameIcon, ENT_QUOTES, 'UTF-8') ?>" alt="League of Legends">
          <span class="av-buy-switch__meta">
            <span>League of Legends</span>
            <small>Marketplace Accounts</small>
          </span>
        </a>
        <a href="<?= BASE_URL ?>/val/accounts" class="av-buy-switch__item">
          <img src="<?= htmlspecialchars($valGameIcon, ENT_QUOTES, 'UTF-8') ?>" alt="Valorant">
          <span class="av-buy-switch__meta">
            <span>Valorant</span>
            <small>Marketplace Accounts</small>
          </span>
        </a>
      </div>
    </div>
  </div>

  <?php if (empty($all)): ?>
  <div class="av-empty">
    <div class="av-empty__ico"><i class="fa-duotone fa-gamepad-modern"></i></div>
    <div class="av-empty__title">No accounts yet</div>
    <div class="av-empty__sub">Your purchased and linked accounts will appear here.</div>
    <a href="<?= BASE_URL ?>/val/accounts" class="av-acc-btn av-acc-btn--primary" style="margin-top:14px;">
      <i class="fa-solid fa-store"></i> Browse Marketplace
    </a>
  </div>

  <?php else: ?>
  <div class="row g-3">

    <?php foreach ($all as $account):
      $isMarketplace = ($account['_type'] === 'marketplace');
      $game = $gameOf($account);

      // Status — accounts table uses status+sold_at, selling_accounts uses sold+active
      if ($isMarketplace) {
          $marketStatus = (int)($account['sold'] ?? 0);
          $isRefunded = ($marketStatus === 2);
          $isSold   = ($marketStatus === 1) || !empty($account['sold_at']);
          $isActive = (int)($account['active'] ?? 0) === 1;
          $badgeTxt = $isRefunded ? 'Refunded' : ($isSold ? 'Purchased' : ($isActive ? 'Active' : 'Inactive'));
      } else {
          $isSold   = !empty($account['sold_at']);
          $isActive = (int)($account['status'] ?? 0) === 1;
          $badgeTxt = $isSold ? 'Sold' : ($isActive ? 'Active' : 'Inactive');
      }
// For marketplace: Purchased uses its own purple class
if ($isMarketplace) {
    $badgeCls = $isRefunded ? 'inactive' : ($isSold ? 'purchased' : ($isActive ? 'active' : 'inactive'));
} else {
    $badgeCls = $isSold ? 'sold' : ($isActive ? 'active' : 'inactive');
}

      $cardId = ($isMarketplace ? 'S' : '') . (int)($account['id'] ?? 0);
      $date   = !empty($account['sold_at']) ? $account['sold_at'] : ($account['created_at'] ?? null);
    ?>
    <div class="col-12 col-xl-6 d-flex">
      <div class="av-acc-card w-100 <?= $isMarketplace ? 'av-acc-card--marketplace' : 'av-acc-card--boost' ?>">

        <!-- Top -->
        <div class="av-acc-card__top">
          <div class="av-acc-card__top-main">
            <div class="av-acc-card__rank-ico">
              <?php if ($isMarketplace && function_exists('util_rank_img')): ?>
                <?php $ri = $game === 'val' ? (int)($account['rank'] ?? 0) : (int)($account['current_rank'] ?? 0); ?>
                <img src="<?= htmlspecialchars(util_rank_img($game,'mini',$ri), ENT_QUOTES,'UTF-8') ?>" alt="">
              <?php elseif ($isMarketplace): ?>
                <i class="fa-duotone fa-trophy" style="font-size:1rem;color:#c4b5fd;"></i>
              <?php else: ?>
                <i class="fa-duotone fa-shield-halved" style="font-size:1.1rem;color:#818cf8;"></i>
              <?php endif ?>
            </div>

          <div class="av-acc-card__info">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
              <img class="av-acc-card__game-ico" src="<?= htmlspecialchars($game === 'val' ? $valGameIcon : $lolGameIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $game === 'val' ? 'Valorant' : 'League of Legends' ?>">
            <div class="av-acc-card__title">
              <?php
                if ($isMarketplace) {
                    $title = $h($account['title'] ?? '');
                    echo $title !== '' ? $title : ('Account #S' . (int)$account['id']);
                } else {
                    // Prefer package name, fall back to login, then ID
                    $pkgName = $h($account['package_name'] ?? $account['pkg_name'] ?? '');
                    $login   = $h($account['login'] ?? '');
                    echo $pkgName !== '' ? $pkgName : ($login !== '' ? $login : ('Account #' . (int)$account['id']));
                }
              ?>
            </div>
            </div>
            <div class="av-acc-card__sub">
              <?php
                // Determine server for both types
                $displayServer = '';
                if ($isMarketplace && !empty($account['server'])) {
                    $displayServer = strtoupper((string)$account['server']);
                } elseif (!$isMarketplace) {
                    // 1st: from package record
                    if (!empty($account['package_server'])) {
                        $displayServer = strtoupper((string)$account['package_server']);
                    } else {
                        // 2nd: parse from data field
                        $rawData = (string)($account['data'] ?? '');
                        if (preg_match('/\b(EUW|EUNE|NA|KR|BR|LAN|LAS|OCE|RU|TR|JP|SG|PH|TW|VN|TH)\b/i', $rawData, $m)) {
                            $displayServer = strtoupper($m[1]);
                        }
                    }
                }
              ?>
              <?php if ($displayServer !== ''): ?>
                <span style="font-weight:800;text-transform:uppercase;color:rgba(255,255,255,.55);"><?= htmlspecialchars($displayServer, ENT_QUOTES, 'UTF-8') ?></span>
                <span>·</span>
              <?php endif ?>
              <span>#<?= $cardId ?></span>
              <span>·</span>
              <span class="av-acc-type-tag"><?= $isMarketplace ? 'Marketplace' : 'Account' ?></span>
            </div>
          </div>
          </div>

          <div class="av-acc-card__quick">
            <span class="av-acc-card__type-tag av-acc-card__type-tag--<?= $isMarketplace ? 'marketplace' : 'smurf' ?>">
              <?php if ($isMarketplace): ?>
                <i class="fa-solid fa-ranking-star" style="font-size:.6rem;"></i> Ranked Account
              <?php else: ?>
                <i class="fa-solid fa-shield-halved" style="font-size:.6rem;"></i> Smurf Account
              <?php endif ?>
            </span>

          </div>
        </div>

        <!-- Stats -->
        <?php if ($isMarketplace): ?>
        <?php
          $gameData = [];
          if (!empty($account['game_data'])) {
              try { $gameData = json_decode((string)$account['game_data'], true, 512, JSON_THROW_ON_ERROR); }
              catch (Throwable $e) { $gameData = []; }
              if (!is_array($gameData)) $gameData = [];
          }
          $tfa = (int)($account['2fa'] ?? $account['twofa'] ?? 0);
          if ($game === 'val') {
              $rf = $valRankStr($account);
              $stat1Lbl = 'Rank';
              // Agents count: manual list from game_data, fallback to val_agent_count
              $agentsList = !empty($gameData['agents']) && is_array($gameData['agents'])
                  ? array_values(array_filter(array_map('strval', $gameData['agents']))) : [];
              $agentsCount = count($agentsList) > 0
                  ? count($agentsList)
                  : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
                     ? (int)$account['val_agent_count'] : 0);
              $stat2Lbl = 'Agents';
              $stat2Val = $agentsCount > 0 ? (string)$agentsCount : (string)($account['level'] ?? '—');
              $stat3Lbl = '2FA';
              $stat3Val = $tfa === 1 ? '<span style="color:#fbbf24;">⚠ On</span>' : '<span style="color:rgba(255,255,255,.3);">Off</span>';
          } else {
              $ri  = (int)($account['current_rank'] ?? 0);
              $rf  = $lolRankStr($ri, $account['current_division'] ?? 0);
              if (!empty($account['current_lp'])) $rf .= ' · ' . (int)$account['current_lp'] . 'LP';
              $stat1Lbl = 'Solo Rank';
              // Champions count: manual list fallback to champion_count
              $champsList = !empty($account['champions']) ? array_filter(explode('|', $account['champions'])) : [];
              $champsCount = count($champsList) > 0 ? count($champsList)
                  : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
                     ? (int)$account['champion_count'] : 0);
              $stat2Lbl = $champsCount > 0 ? 'Champs' : 'Level';
              $stat2Val = $champsCount > 0 ? (string)$champsCount : (string)($account['level'] ?? '—');
              $stat3Lbl = '2FA';
              $stat3Val = $tfa === 1 ? '<span style="color:#fbbf24;">⚠ On</span>' : '<span style="color:rgba(255,255,255,.3);">Off</span>';
          }
        ?>
        <div class="av-acc-card__stats">
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl"><?= htmlspecialchars($stat1Lbl, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="av-acc-card__stat-val"><?= htmlspecialchars($rf, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl"><?= htmlspecialchars($stat2Lbl, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="av-acc-card__stat-val"><?= htmlspecialchars($stat2Val, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl"><?= htmlspecialchars($stat3Lbl, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="av-acc-card__stat-val">
              <?= $stat3Val ?>
            </div>
          </div>
        </div>

        <?php else: ?>
        <?php
          $smurfRawData = html_entity_decode(trim((string)($account['data'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
          $smurfLogin = html_entity_decode(trim((string)($account['login'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
          $smurfPassword = html_entity_decode(trim((string)($account['password'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        ?>
        <div class="av-smurf-panel">
          <div class="av-smurf-row js-chip-copy" data-copy="<?= htmlspecialchars($smurfLogin, ENT_QUOTES, 'UTF-8') ?>">
            <div class="av-smurf-label"><i class="fa-solid fa-user"></i> Login</div>
            <div class="av-smurf-value"><?= $smurfLogin !== '' ? htmlspecialchars($smurfLogin, ENT_QUOTES, 'UTF-8') : '—' ?></div>
            <div class="av-smurf-copy"><i class="fa-duotone fa-copy"></i></div>
          </div>
          <div class="av-smurf-row js-chip-copy" data-copy="<?= htmlspecialchars($smurfPassword, ENT_QUOTES, 'UTF-8') ?>">
            <div class="av-smurf-label"><i class="fa-solid fa-key"></i> Password</div>
            <div class="av-smurf-value"><?= $smurfPassword !== '' ? htmlspecialchars($smurfPassword, ENT_QUOTES, 'UTF-8') : '—' ?></div>
            <div class="av-smurf-copy"><i class="fa-duotone fa-copy"></i></div>
          </div>
          <div class="av-smurf-row av-smurf-row--data js-chip-copy" data-copy="<?= htmlspecialchars($smurfRawData, ENT_QUOTES, 'UTF-8') ?>">
            <div class="av-smurf-label"><i class="fa-solid fa-circle-info"></i> Data</div>
            <div class="av-smurf-value av-smurf-value--data"><?= $smurfRawData !== '' ? nl2br(htmlspecialchars($smurfRawData, ENT_QUOTES, 'UTF-8')) : '—' ?></div>
            <div class="av-smurf-copy"><i class="fa-duotone fa-copy"></i></div>
          </div>
        </div>
        <?php endif ?>

        <!-- Spacer pushes footer down -->
        <div class="av-acc-card__spacer"></div>

        <?php if ($isMarketplace): ?>
        <!-- Credential chips -->
        <div class="av-acc-card__creds">
          <?php
          $credFields = ['in_game_name' => 'IGN', 'login' => 'Login', 'password' => 'PW', 'email' => 'Email', 'email_password' => 'Email PW'];
          $hasAny = false;
          foreach ($credFields as $field => $lbl):
              $raw = (string)($account[$field] ?? '');
              if ($raw === '' || $raw === 'unverified' || $raw === '-') continue;
              $hasAny = true;
              $safe = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
          ?>
          <div class="av-cred-chip js-chip-copy" data-copy="<?= $safe ?>" title="Copy <?= htmlspecialchars($lbl, ENT_QUOTES,'UTF-8') ?>">
            <span class="av-cred-chip__lbl"><?= $lbl ?></span>
            <span class="av-cred-chip__val"><?= $safe ?></span>
            <i class="fa-duotone fa-copy av-cred-chip__ico"></i>
          </div>
          <?php endforeach ?>
          <?php if (!$hasAny): ?>
            <span style="font-size:.72rem;color:rgba(255,255,255,.22);">No credentials available.</span>
          <?php endif ?>
        </div>
        <?php endif ?>

        <!-- Footer -->
        <div class="av-acc-card__footer">
          <span class="av-acc-card__date"><i class="fa-solid fa-calendar-days" style="font-size:.6rem;opacity:.5;"></i><?= $date ? date('d.m.Y', strtotime($date)) : '—' ?></span>
          <div class="av-acc-card__footer-actions">
            <?php if ($isSold): ?>
            <?php
              $viewUrl = $isMarketplace
                  ? (BASE_URL . '/account/' . (int)$account['id'])
                  : (BASE_URL . '/premium-account/' . (int)$account['id']);
            ?>
            <a href="<?= htmlspecialchars($viewUrl, ENT_QUOTES, 'UTF-8') ?>" class="av-acc-btn av-acc-btn--primary">
              <i class="fa-duotone fa-eye"></i> View
            </a>
            <?php endif ?>
            <button type="button" class="av-acc-btn av-acc-btn--danger js-report-problem"
              data-account-id="<?= (int)($account['id'] ?? 0) ?>"
              data-account-code="<?= htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8') ?>"
              data-account-type="<?= $isMarketplace ? 'Marketplace Ranked Account' : 'Smurf Account' ?>"
              data-account-title="<?= htmlspecialchars($isMarketplace ? (($account['title'] ?? '') ?: ('Account #S' . (int)$account['id'])) : (($account['package_name'] ?? $account['pkg_name'] ?? $account['login'] ?? '') ?: ('Account #' . (int)$account['id'])), ENT_QUOTES, 'UTF-8') ?>"
              data-admin-url="<?= htmlspecialchars(defined('ADMN_URL') ? (ADMN_URL . ($isMarketplace ? '/selling-account/' : '/account/') . (int)($account['id'] ?? 0)) : '', ENT_QUOTES, 'UTF-8') ?>">
              <i class="fa-solid fa-flag"></i> Report
            </button>
          </div>
        </div>

      </div>
    </div>
    <?php endforeach ?>

  </div>
  <?php endif ?>

</div>


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
            ['id' => 'creds_wrong',    'icon' => 'fa-solid fa-key',      'text' => 'Login credentials are incorrect'],
            ['id' => 'acc_banned',     'icon' => 'fa-solid fa-ban',      'text' => 'Account has been banned / suspended'],
            ['id' => 'acc_locked',     'icon' => 'fa-solid fa-lock',     'text' => 'Account is locked or inaccessible'],
            ['id' => 'data_missing',   'icon' => 'fa-solid fa-circle-info', 'text' => 'Account data is missing or incomplete'],
            ['id' => 'not_delivered',  'icon' => 'fa-solid fa-box-open', 'text' => 'Account was never delivered'],
            ['id' => 'other',          'icon' => 'fa-solid fa-ellipsis', 'text' => 'Other issue'],
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

<?= $this->start('scripts') ?>
<script>
(function () {
  var buySwitch = document.querySelector('[data-buy-switch]');
  if (buySwitch) {
    var toggle = buySwitch.querySelector('.av-buy-switch__toggle');
    toggle && toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = buySwitch.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!buySwitch.contains(e.target)) {
        buySwitch.classList.remove('is-open');
        toggle && toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  document.querySelectorAll('.js-chip-copy').forEach(chip => {
    chip.addEventListener('click', function () {
      const val = this.getAttribute('data-copy');
      if (!val) return;
      navigator.clipboard.writeText(val).then(() => {
        this.classList.add('copied');
        const ico = this.querySelector('.av-cred-chip__ico');
        if (ico) ico.className = 'fa-solid fa-check av-cred-chip__ico';
        setTimeout(() => {
          this.classList.remove('copied');
          if (ico) ico.className = 'fa-duotone fa-copy av-cred-chip__ico';
        }, 1600);
        if (typeof create_toast === 'function') create_toast('success', 'Copied', 'Copied to clipboard.');
      }).catch(() => {});
    });
  });

  /* ── REPORT A PROBLEM ── */
  (function () {
    const REPORT_AJAX = (window.AJAX_URL || '<?= defined('AJAX_URL') ? AJAX_URL : BASE_URL . '/ajax' ?>');
    const overlay         = document.getElementById('rpOverlay');
    const closeBtn        = document.getElementById('rpClose');
    const cancelBtn       = document.getElementById('rpCancelBtn');
    const submitBtn       = document.getElementById('rpSubmitBtn');
    const formWrap        = document.getElementById('rpFormWrap');
    const successWrap     = document.getElementById('rpSuccessWrap');
    const successCloseBtn = document.getElementById('rpSuccessClose');
    const detailsEl       = document.getElementById('rpDetails');
    const problemOpts     = document.querySelectorAll('.rp-problem-opt');
    const reportBtns      = document.querySelectorAll('.js-report-problem');

    if (!overlay || !submitBtn) return;

    let selectedIssue = null;
    let selectedAccount = null;

    function openModal(btn) {
      selectedAccount = {
        id: btn.getAttribute('data-account-id') || '',
        code: btn.getAttribute('data-account-code') || '',
        type: btn.getAttribute('data-account-type') || 'Account',
        title: btn.getAttribute('data-account-title') || 'Account',
        adminUrl: btn.getAttribute('data-admin-url') || ''
      };
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function closeModal() {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      setTimeout(() => {
        selectedIssue = null;
        selectedAccount = null;
        problemOpts.forEach(o => o.classList.remove('is-selected'));
        if (detailsEl) detailsEl.value = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Report';
        formWrap.style.display = '';
        successWrap.style.display = 'none';
      }, 220);
    }

    reportBtns.forEach(btn => btn.addEventListener('click', function(e) { e.preventDefault(); openModal(this); }));
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (successCloseBtn) successCloseBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal(); });

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

    submitBtn.addEventListener('click', async function() {
      if (!selectedIssue || !selectedAccount) return;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…';

      const issueLabel = submitBtn._label || selectedIssue;
      const details = detailsEl ? detailsEl.value.trim() : '';
      const clientId = <?= json_encode((string)(defined('CLIENT_ID') ? CLIENT_ID : 'unknown')) ?>;
      const clientName = <?= json_encode((string)(defined('CLIENT_DATA') && is_array(CLIENT_DATA) ? (CLIENT_DATA['username'] ?? 'Client') : 'Client')) ?>;
      const clientAdminUrl = <?= json_encode((defined('ADMN_URL') && defined('CLIENT_ID')) ? (ADMN_URL . '/client/' . (int)CLIENT_ID) : '') ?>;
      const accountNumber = selectedAccount.code || selectedAccount.id || '';
      const fields = [
        { name: '🎮 Account', value: `**${selectedAccount.title}** (#${accountNumber})`, inline: true },
        { name: '📦 Type', value: selectedAccount.type, inline: true },
        { name: '👤 Client', value: `**${clientName}** (#${clientId})`, inline: true },
        { name: '⚠️ Issue', value: issueLabel, inline: false },
        ...(details ? [{ name: '📝 Details', value: details.substring(0, 1000), inline: false }] : []),
      ];
      if (clientAdminUrl) fields.push({ name: '🔗 Client Page', value: `[Open Client in Admin Panel](${clientAdminUrl})`, inline: false });
      if (selectedAccount.adminUrl) fields.push({ name: '🔗 Admin', value: `[View Account in Admin Panel](${selectedAccount.adminUrl})`, inline: false });

      const payload = {
        username: 'Account Reports',
        embeds: [{
          title: '🚨 Account Problem Report',
          color: 0xef4444,
          fields: fields,
          footer: { text: 'Reported via lolboost.gg' },
          timestamp: new Date().toISOString(),
        }]
      };

      try {
        const fd = new FormData();
        fd.set('action', 'client_report_problem');
        fd.set('ref_type', /marketplace/i.test(selectedAccount.type || '') ? 'account' : 'premium_account');
        fd.set('ref_id', String(selectedAccount.id || ''));
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
<?= $this->end() ?>
