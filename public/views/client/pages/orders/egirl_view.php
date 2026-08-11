<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>

<?= $this->start('styles') ?>
<style>
.eg-sp{display:inline-flex;align-items:center;gap:.38rem;padding:.28rem .82rem;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;}
.eg-sp::before{content:"";width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.eg-sp.paid{background:rgba(177,140,255,.12);border:1px solid rgba(177,140,255,.3);color:#b18cff;}.eg-sp.paid::before{background:#b18cff;}
.eg-sp.in_progress{background:rgba(78,161,255,.1);border:1px solid rgba(78,161,255,.3);color:#4ea1ff;}.eg-sp.in_progress::before{background:#4ea1ff;box-shadow:0 0 0 3px rgba(78,161,255,.2);}
.eg-sp.completed{background:rgba(31,230,198,.1);border:1px solid rgba(31,230,198,.3);color:#1fe6c6;}.eg-sp.completed::before{background:#1fe6c6;}
.eg-sp.unpaid{background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.3);color:#ff6b6b;}.eg-sp.unpaid::before{background:#ff6b6b;}
.eg-sp.cancelled,.eg-sp.refunded{background:rgba(255,138,76,.1);border:1px solid rgba(255,138,76,.3);color:#ff8a4c;}.eg-sp.cancelled::before,.eg-sp.refunded::before{background:#ff8a4c;}

.eg-crumb{
    display:inline-flex;align-items:center;gap:.6rem;flex-wrap:wrap;
    margin-bottom:1rem;padding:.72rem 1rem;border-radius:18px;
    border:1px solid rgba(168,85,247,.16);
    background:rgba(255,255,255,.022);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.02),0 10px 24px rgba(0,0,0,.10);
}
.eg-crumb a{
    display:inline-flex;align-items:center;gap:.48rem;
    color:rgba(255,255,255,.82);text-decoration:none;font-weight:800;
}
.eg-crumb a i{color:rgba(255,255,255,.58);}
.eg-crumb a:hover{color:#fff;}
.eg-crumb .sep{
    width:26px;height:26px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;
    color:rgba(255,255,255,.58);background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);font-size:.68rem;
}
.eg-crumb .cur-id{
    display:inline-flex;align-items:center;padding:.34rem .68rem;border-radius:999px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);color:#fff;font-size:.78rem;font-weight:900;
}
.eg-crumb .cur-name{color:#fff;font-weight:900;}

.eg-head{border-radius:.85rem;overflow:hidden;margin-bottom:1.25rem;}
.eg-head-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.5rem;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,.07);}
.eg-head-left{display:flex;align-items:center;gap:.85rem;min-width:0;flex:1;}
.eg-head-icon{flex-shrink:0;width:2.6rem;height:2.6rem;border-radius:.65rem;background:rgba(168,85,247,.15);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#c084fc;overflow:hidden;}
.eg-head-icon img{width:100%;height:100%;object-fit:cover;border-radius:.55rem;}
.eg-head-h1{font-size:1.05rem;font-weight:900;color:rgba(255,255,255,.9);margin:0;line-height:1.25;}
.eg-head-id{font-size:.72rem;color:rgba(255,255,255,.28);margin-left:.35rem;font-weight:600;}
.eg-head-sub{margin-top:.2rem;display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;}
.eg-head-actions{display:flex;align-items:center;gap:.5rem;flex-shrink:0;}
.eg-head-meta{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;padding:.6rem 1.5rem;background:rgba(255,255,255,.015);}
.eg-mp{display:inline-flex;flex-direction:column;padding:.3rem .7rem;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);min-width:70px;}
.eg-mp .mpk{font-size:.59rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.28);}
.eg-mp .mpv{font-size:.79rem;font-weight:700;color:rgba(255,255,255,.82);margin-top:.06rem;}
.eg-mp .mpv.g{color:#1fe6c6;}

/* Timer card */
.eg-timer-card{
    background:linear-gradient(180deg, rgba(255,255,255,.028), rgba(255,255,255,.02));
    border:1px solid rgba(168,85,247,.14);border-radius:12px;padding:.9rem 1rem;
    box-shadow:0 10px 24px rgba(0,0,0,.08);
}
.eg-timer-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.7rem;}
.eg-timer-k{font-size:.66rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.42);}
.eg-timer-v{font-size:1.8rem;line-height:1;font-weight:900;color:#fff;margin-top:.2rem;}
.eg-timer-sub{font-size:.72rem;color:rgba(255,255,255,.38);margin-top:.2rem;}
.eg-timer-state{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;}
.eg-timer-state.running{color:#4ea1ff;}
.eg-timer-state.paused{color:#f59e0b;}
.eg-timer-state.finished{color:#1fe6c6;}
.eg-timer-state.idle{color:rgba(255,255,255,.45);}

/* Voice notice box */
.eg-voice-box{
    background:linear-gradient(135deg,rgba(255,255,255,.03),rgba(255,255,255,.02));
    border:1px solid rgba(88,101,242,.22);border-radius:12px;
    padding:.85rem 1.1rem;display:flex;align-items:flex-start;gap:.85rem;
    margin-bottom:.1rem;
}
.eg-voice-icon{
    width:38px;height:38px;border-radius:10px;flex-shrink:0;
    background:rgba(88,101,242,.18);display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;color:#818cf8;
}
.eg-voice-title{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.88);margin-bottom:.2rem;}
.eg-voice-sub{font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.5;}
.eg-voice-btn{
    display:inline-flex;align-items:center;gap:.35rem;
    margin-top:.55rem;padding:.38rem .9rem;border-radius:8px;
    font-size:.78rem;font-weight:800;text-decoration:none;
    background:rgba(88,101,242,.2);border:1px solid rgba(88,101,242,.35);color:#818cf8;
    transition:background .15s;
}
.eg-voice-btn:hover{background:rgba(88,101,242,.32);color:#a5b4fc;}

/* Report button */
.eg-report-btn{
    display:inline-flex;align-items:center;gap:.35rem;
    padding:.38rem .85rem;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;
    background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.22);color:rgba(255,107,107,.8);
    transition:background .15s;
}
.eg-report-btn:hover{background:rgba(255,107,107,.15);color:#ff6b6b;}
.eg-poke-btn{display:inline-flex;align-items:center;gap:.35rem;padding:.38rem .85rem;border-radius:8px;font-size:.78rem;font-weight:800;cursor:pointer;background:rgba(168,85,247,.10);border:1px solid rgba(168,85,247,.28);color:#c4b5fd;transition:background .15s;}
.eg-poke-btn:hover{background:rgba(168,85,247,.18);color:#fff;}
.eg-poke-btn:disabled{cursor:not-allowed;opacity:.7;background:rgba(168,85,247,.07);color:#a78bfa;}
.eg-rate-session-btn{width:100%;display:grid;grid-template-columns:42px 1fr auto;align-items:center;gap:11px;padding:11px 12px;border:1px solid rgba(168,85,247,.28);border-radius:12px;background:linear-gradient(135deg,rgba(124,58,237,.16),rgba(168,85,247,.07));color:#fff;text-align:left;transition:.18s}
.eg-rate-session-btn:hover{transform:translateY(-1px);border-color:rgba(192,132,252,.48);background:linear-gradient(135deg,rgba(124,58,237,.25),rgba(168,85,247,.11));box-shadow:0 10px 28px rgba(124,58,237,.13)}
.eg-rate-session-btn>span:first-child{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:11px;background:linear-gradient(135deg,#7c3aed,#9333ea);box-shadow:0 7px 20px rgba(124,58,237,.28);color:#fff}
.eg-rate-session-btn>span:nth-child(2){display:grid;gap:2px;min-width:0}.eg-rate-session-btn strong{font-size:.76rem;font-weight:900}.eg-rate-session-btn small{color:rgba(255,255,255,.42);font-size:.62rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.eg-rate-session-btn>i{color:#c4b5fd;font-size:.72rem}
.eg-poke-toast-wrap{position:fixed;top:82px;right:22px;z-index:11000;display:grid;gap:10px;width:min(360px,calc(100vw - 28px));pointer-events:none}
.eg-poke-toast{display:flex;align-items:flex-start;gap:12px;padding:14px 15px;border:1px solid rgba(168,85,247,.3);border-radius:12px;background:rgba(19,18,31,.98);box-shadow:0 18px 50px rgba(0,0,0,.45);animation:egPokeToastIn .2s ease;color:#fff;pointer-events:auto}
.eg-poke-toast.success{border-color:rgba(31,230,198,.32)}.eg-poke-toast.warning{border-color:rgba(245,158,11,.35)}.eg-poke-toast.danger{border-color:rgba(244,63,94,.35)}
.eg-poke-toast__icon{width:34px;height:34px;flex:0 0 34px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:rgba(168,85,247,.14);color:#c4b5fd}
.eg-poke-toast.success .eg-poke-toast__icon{background:rgba(31,230,198,.12);color:#1fe6c6}.eg-poke-toast.warning .eg-poke-toast__icon{background:rgba(245,158,11,.12);color:#fbbf24}.eg-poke-toast.danger .eg-poke-toast__icon{background:rgba(244,63,94,.12);color:#fb7185}
.eg-poke-toast__copy{min-width:0;flex:1}.eg-poke-toast__title{font-size:.82rem;font-weight:900}.eg-poke-toast__message{margin-top:3px;color:rgba(255,255,255,.58);font-size:.72rem;line-height:1.4}
@keyframes egPokeToastIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}

/* 2-col grid */
.eg-grid{display:grid;grid-template-columns:1fr 280px;gap:1.1rem;align-items:start;}
@media(max-width:991px){.eg-grid{grid-template-columns:1fr;}}

/* Detail list */
.eg-ov-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;}
.eg-ov-item{display:grid;grid-template-columns:1.4rem 1fr auto;align-items:center;gap:.5rem;padding:.55rem 0;border-bottom:1px solid rgba(255,255,255,.06);}
.eg-ov-item:last-child{border-bottom:0;}
.eg-ov-ico{font-size:.88rem;text-align:center;color:rgba(168,85,247,.45);}
.eg-ov-lbl{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:rgba(255,255,255,.3);}
.eg-ov-val{font-weight:700;font-size:.82rem;text-align:right;color:rgba(255,255,255,.78);}

/* Chat */
.eg-chat-bg{height:clamp(260px,40vh,400px);overflow-y:auto;padding:1rem 1.15rem;display:flex;flex-direction:column;gap:9px;background:linear-gradient(180deg, rgba(255,255,255,.026), rgba(255,255,255,.018));scrollbar-width:thin;scrollbar-color:rgba(168,85,247,.18) transparent;}
.eg-chat-bg::-webkit-scrollbar{width:3px;}.eg-chat-bg::-webkit-scrollbar-thumb{background:rgba(168,85,247,.25);border-radius:2px;}
.eg-m{display:flex;flex-direction:column;max-width:78%;}
.eg-m.me{align-self:flex-end;}.eg-m.them{align-self:flex-start;}
.eg-m-head{display:flex;align-items:center;gap:7px;margin-bottom:4px;}
.eg-m.me .eg-m-head{flex-direction:row-reverse;}
.eg-m-av{width:30px;height:30px;border-radius:50%;flex-shrink:0;overflow:hidden;border:1.5px solid rgba(168,85,247,.3);background:rgba(168,85,247,.1);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:800;color:#c084fc;}
.eg-m-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.eg-m-av.mine{border-color:rgba(78,161,255,.4);background:rgba(78,161,255,.1);color:#4ea1ff;}
.eg-m-name{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.35);display:flex;align-items:center;gap:.38rem;flex-wrap:wrap;}
.eg-m.me .eg-m-name{color:rgba(78,161,255,.5);}
.eg-role-badge{display:inline-flex;align-items:center;justify-content:center;padding:.12rem .46rem;border-radius:999px;font-size:.56rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);line-height:1.1}
.eg-role-admin{color:#ff8f8f;background:rgba(255,107,107,.10);border-color:rgba(255,107,107,.24);}
.eg-role-client{color:#7dc7ff;background:rgba(78,161,255,.10);border-color:rgba(78,161,255,.24);}
.eg-role-egirl{color:#f7a8d3;background:rgba(244,114,182,.12);border-color:rgba(244,114,182,.24);}
.eg-m-bbl{padding:.5rem .78rem;border-radius:11px;font-size:.83rem;line-height:1.5;word-break:break-word;color:rgba(255,255,255,.85);}
.eg-m.them .eg-m-bbl{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);border-top-left-radius:3px;}
.eg-m.me   .eg-m-bbl{background:rgba(78,161,255,.12);border:1px solid rgba(78,161,255,.2);border-top-right-radius:3px;}
.eg-m-ts{font-size:.62rem;color:rgba(255,255,255,.22);margin-top:.18rem;}
.eg-m.me .eg-m-ts{text-align:right;}
.eg-chat-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.45rem;color:rgba(255,255,255,.22);}
.eg-chat-empty i{font-size:1.7rem;color:rgba(168,85,247,.2);}
.eg-chat-footer{display:flex;gap:.5rem;align-items:flex-end;padding:.6rem .9rem;border-top:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.01);}
.eg-chat-ta{flex:1;resize:none;max-height:88px;min-height:36px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:.43rem .73rem;color:rgba(255,255,255,.88);font-size:.83rem;line-height:1.4;outline:none;font-family:inherit;transition:border-color .15s;}
.eg-chat-ta:focus{border-color:rgba(168,85,247,.4);}.eg-chat-ta::placeholder{color:rgba(255,255,255,.2);}.eg-chat-ta:disabled{opacity:.3;}
.eg-chat-send{width:36px;height:36px;border-radius:9px;flex-shrink:0;background:linear-gradient(135deg,#a855f7,#ec4899);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.84rem;transition:opacity .15s;}
.eg-chat-send:hover{opacity:.85;}.eg-chat-send:disabled{opacity:.3;cursor:not-allowed;}
.eg-mc{font-size:.66rem;font-weight:700;padding:.13rem .47rem;border-radius:999px;background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.18);color:#c084fc;}

.eg-sidebar{display:flex;flex-direction:column;gap:.85rem;}
.eg-eg-card{
    background:linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.02));
    border:1px solid rgba(168,85,247,.12);
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 10px 24px rgba(0,0,0,.08);
}
.eg-eg-banner{
    height:58px;
    background:linear-gradient(135deg, rgba(168,85,247,.28), rgba(236,72,153,.16));
}
.eg-eg-body{padding:0 1rem 1rem;}
.eg-eg-av-wrap{
    position:relative;
    width:max-content;
    margin-top:-22px;
}
.eg-eg-av{
    width:48px;height:48px;border-radius:50%;
    border:2.5px solid rgba(14,15,24,.95);
    background:rgba(255,255,255,.04);
    display:flex;align-items:center;justify-content:center;
    font-size:.95rem;font-weight:800;color:#c084fc;overflow:hidden;
}
.eg-eg-av img{width:100%;height:100%;object-fit:cover;display:block;border-radius:50%;}
.eg-eg-dot{
    position:absolute;right:2px;bottom:2px;width:10px;height:10px;border-radius:50%;
    background:#22c55e;border:2px solid rgba(14,15,24,.95);
}
.eg-eg-name{
    margin-top:.55rem;
    font-size:.95rem;
    font-weight:900;
    color:rgba(255,255,255,.92);
}
.eg-eg-role{
    font-size:.72rem;
    color:rgba(255,255,255,.38);
    margin-top:.15rem;
}
.eg-eg-tags{
    display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.8rem;margin-bottom:.9rem;
}
.eg-eg-tag{
    display:inline-flex;align-items:center;padding:.28rem .55rem;border-radius:999px;
    font-size:.68rem;font-weight:800;color:rgba(255,255,255,.72);
    background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
}
.eg-eg-link{
    display:inline-flex;align-items:center;gap:.45rem;padding:.55rem .85rem;border-radius:10px;
    text-decoration:none;font-size:.78rem;font-weight:800;color:#d8b4fe;
    background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.18);
    transition:.18s ease;
}
.eg-eg-link:hover{
    color:#fff;background:rgba(168,85,247,.14);border-color:rgba(168,85,247,.3);
}
.eg-pay-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.62rem 1.2rem;border-radius:10px;font-weight:800;font-size:.88rem;background:linear-gradient(135deg,#a855f7,#ec4899);border:none;color:#fff;cursor:pointer;text-decoration:none;transition:opacity .15s;width:100%;}
.eg-pay-btn:hover{opacity:.88;color:#fff;}

.eg-emoji{background:transparent!important;border:none!important;padding:5px;border-radius:8px;cursor:pointer;font-size:20px;line-height:1;transition:background .1s;}
.eg-emoji:hover{background:rgba(168,85,247,.15)!important;}
#egEmojiPicker button{box-shadow:none!important;outline:none!important;}

.eg-chat-img{max-width:180px;border-radius:8px;cursor:zoom-in;display:block;}
#chatImageModal .modal-content{background:rgba(12,10,22,.98);border:1px solid rgba(168,85,247,.2);}
#chatImageModal .modal-header{border-bottom:1px solid rgba(255,255,255,.08);}
#chatImageModal .modal-body{padding:1rem;display:flex;align-items:center;justify-content:center;min-height:220px;}
#chatImageModalImg{max-width:100%;max-height:78vh;border-radius:12px;display:block;box-shadow:0 16px 50px rgba(0,0,0,.45);}

/* ① Header cover banner */
.eg-head-cover{height:54px;background:linear-gradient(135deg,rgba(168,85,247,.3) 0%,rgba(236,72,153,.18) 60%,rgba(168,85,247,.08) 100%);position:relative;}
.eg-head-cover-tag{position:absolute;top:10px;left:16px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.3);}
.eg-head-cover-av{position:absolute;bottom:-18px;left:16px;width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,rgba(168,85,247,.4),rgba(236,72,153,.25));border:2.5px solid rgba(12,10,20,.95);display:flex;align-items:center;justify-content:center;font-size:1rem;overflow:hidden;}
.eg-head-cover-av img{width:100%;height:100%;object-fit:cover;display:block;border-radius:50%;}
.eg-head{position:relative;overflow:hidden;}
.eg-head-top{padding-left:68px!important;}
/* ② Timer progress ring + bar */
.eg-timer-ring{position:relative;flex-shrink:0;}
.eg-timer-ring svg{display:block;}
.eg-timer-ring-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#4ea1ff;}
.eg-timer-progress-bar{height:3px;background:rgba(168,85,247,.1);margin-top:.5rem;border-radius:2px;overflow:hidden;}
.eg-timer-progress-fill{height:100%;background:linear-gradient(90deg,#a855f7,#4ea1ff);border-radius:2px;transition:width .5s linear;}
/* ④ Voice badge */
.eg-voice-badge{display:inline-flex;align-items:center;gap:.28rem;padding:.18rem .55rem;border-radius:999px;font-size:.7rem;font-weight:700;}
.eg-voice-yes{background:rgba(31,230,198,.1);border:1px solid rgba(31,230,198,.25);color:#1fe6c6;}
.eg-voice-no{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.3);}
/* ⑥ GG-Girl card cover */
.eg-eg-banner{background:linear-gradient(135deg,rgba(168,85,247,.4),rgba(236,72,153,.22))!important;}
</style>
<?= $this->end() ?>

<?php

if (!function_exists('cv_eg_json_decode_if_possible')) {
  function cv_eg_json_decode_if_possible($v) {
    if (!is_string($v)) return [];
    $s = trim($v);
    if ($s === '' || ($s[0] !== '{' && $s[0] !== '[')) return [];
    $d = json_decode($s, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($d)) ? $d : [];
  }
}
if (!function_exists('cv_eg_order_pool')) {
  function cv_eg_order_pool(array $row): array {
    $pool = [];
    foreach (['data','details','meta','metadata','options','form_data','order_data','client_notes','notes'] as $k) {
      if (!array_key_exists($k, $row)) continue;
      if (is_array($row[$k])) $pool = array_merge($pool, $row[$k]);
      else {
        $decoded = cv_eg_json_decode_if_possible($row[$k]);
        if (!empty($decoded)) $pool = array_merge($pool, $decoded);
      }
    }
    foreach ($row as $k => $v) {
      if (!is_string($v)) continue;
      $decoded = cv_eg_json_decode_if_possible($v);
      if (!empty($decoded)) $pool = array_merge($pool, $decoded);
    }
    return $pool;
  }
}
if (!function_exists('cv_eg_pick')) {
  function cv_eg_pick(array $row, array $pool, array $keys, $default = '') {
    foreach ($keys as $k) {
      if (array_key_exists($k, $row) && trim((string)$row[$k]) !== '') return $row[$k];
      if (array_key_exists($k, $pool) && trim((string)$pool[$k]) !== '') return $pool[$k];
    }
    return $default;
  }
}
if (!function_exists('cv_eg_format_amount')) {
  function cv_eg_format_amount($amount): string {
    $n = (int)$amount;
    if ($n <= 0) $n = 1;
    return $n . ' ' . ($n === 1 ? 'Game' : 'Games');
  }
}
if (!function_exists('cv_eg_details')) {
  function cv_eg_details(array $row): array {
    $pool = cv_eg_order_pool($row);
    $mode = (string)cv_eg_pick($row, $pool, ['mode_title','service_title','mode','service_type'], 'Normal Draft Game');
    $mode = preg_replace('/^LoL\s+GGirl:\s*/i', '', trim($mode));
    $server = strtoupper((string)cv_eg_pick($row, $pool, ['server','region'], 'EUW'));
    $rank = (string)cv_eg_pick($row, $pool, ['rank_label','rank'], 'Unranked');
    $amount = cv_eg_format_amount(cv_eg_pick($row, $pool, ['amount','games','game_count','unit_value'], 1));
    $assignment = (string)cv_eg_pick($row, $pool, ['egirl_name','assignment','egirl_username'], 'Any Available');
    return [
      'mode' => $mode,
      'server' => $server,
      'rank' => $rank,
      'amount' => $amount,
      'assignment' => $assignment,
      'summary' => trim($server . ' - ' . $mode . ' - ' . $amount),
      'service' => 'LoL GGirl: ' . $mode . ' ' . $amount,
    ];
  }
}

$order      = $order   ?? [];
$messages   = $messages ?? [];
$review     = is_array($review ?? null) ? $review : null;
$id         = (int)($order['id'] ?? 0);
$statusRaw  = strtoupper($order['status'] ?? 'UNPAID');
$statusKey  = strtolower(str_replace(' ','_',$statusRaw));
$priceCents    = (int)($order['price'] ?? $order['price_cents'] ?? 0);
$orderCurrency = strtoupper((string)($order['currency'] ?? 'EUR'));
$orderSymbol   = $orderCurrency === 'USD' ? '$' : '€';
$orderSuffix   = $orderCurrency === 'USD' ? ' USD' : '';
$fmtPrice      = function(int $cents) use ($orderSymbol, $orderSuffix): string {
    return $orderSymbol . number_format($cents / 100, 2) . $orderSuffix;
};
$egD        = cv_eg_details($order);
$hasVoice   = true;
$svcName    = htmlspecialchars($egD['summary']);
$gameName   = !empty($order['game']) ? strtoupper(htmlspecialchars($order['game'])) : '';
$egirlName  = htmlspecialchars($order['egirl_username'] ?? '—');
$egirlIcon  = !empty($order['egirl_icon']) ? $order['egirl_icon'] : (ASSET_URL . '/website/images/gg-girl.svg');
$egirlInit  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $order['egirl_username']??'E')?:'E',0,1));
$myIcon     = CLIENT_DATA['icon'] ?? '';
$myName     = CLIENT_DATA['username'] ?? 'Me';
$myId       = (int)(CLIENT_ID ?? 0);
$myInit     = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $myName)?:'C',0,1));
$isChatLocked = in_array($statusRaw, ['CANCELLED','REFUNDED','UNPAID']);
$lastMsgTime  = 0;
if (!empty($messages)) { $last = end($messages); $lastMsgTime = (int)($last['time'] ?? 0); }

$invoice = null;
if ($statusRaw === 'UNPAID' && !empty($order['invoice_id'])) {
    global $db;
    $invoice = $db->row("SELECT uuid FROM invoices WHERE id = ? LIMIT 1", (int)$order['invoice_id']);
}
$payUrl = ($invoice && !empty($invoice['uuid'])) ? BASE_URL . '/checkout/' . $invoice['uuid'] : null;

$unitValueRaw = (float)($order['unit_value'] ?? 1);
$unitTypeRaw  = strtolower((string)($order['unit_type'] ?? 'hours'));
$durationSeconds = (int) round(
    in_array($unitTypeRaw, ['minute','minutes','min','mins'], true) ? ($unitValueRaw * 60) :
    (in_array($unitTypeRaw, ['day','days'], true) ? ($unitValueRaw * 86400) : ($unitValueRaw * 3600))
);
if ($durationSeconds <= 0) $durationSeconds = 3600;
$sessionStarted = !empty($order['claimed_at']) || !empty($order['started_at']) || !empty($order['session_end_at']);
$sessionEndAtRaw = $order['session_end_at'] ?? null;
$isPausedSession = !empty($order['is_paused']);
if (!$sessionStarted) {
    $remainingSeconds = $durationSeconds;
} elseif ($isPausedSession) {
    $remainingSeconds = (int)($order['paused_remaining_seconds'] ?? $durationSeconds);
} else {
    $remainingSeconds = !empty($sessionEndAtRaw) ? max(0, strtotime($sessionEndAtRaw) - time()) : $durationSeconds;
}
$timerStatusLabel = !$sessionStarted ? 'Not started' : ($isPausedSession ? 'Paused' : ($remainingSeconds > 0 ? 'Running' : 'Finished'));
$timerStateClass  = !$sessionStarted ? 'idle' : ($isPausedSession ? 'paused' : ($remainingSeconds > 0 ? 'running' : 'finished'));
$timerEndTs = !empty($sessionEndAtRaw) ? (int)strtotime($sessionEndAtRaw) : (time() + (int)$remainingSeconds);
?>

<div class="order-page-wrap">

<!-- Header card -->
<div class="card eg-head">
    <div class="eg-head-cover">
        <span class="eg-head-cover-tag">LoL GGirl Order</span>
        <div class="eg-head-cover-av"><img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt=""></div>
    </div>
    <div class="eg-head-top">
        <div class="eg-head-left">
            <div>
                <h1 class="eg-head-h1"><?= $svcName ?><span class="eg-head-id">#<?= $id ?></span></h1>
                <div class="eg-head-sub">
                    <span class="eg-sp <?= $statusKey ?>"><?= str_replace('_',' ',$statusRaw) ?></span>
                    <?php if($gameName):?><span style="font-size:.7rem;color:rgba(255,255,255,.28);font-weight:600"><?= $gameName ?></span><?php endif;?>
                </div>
            </div>
        </div>
        <div class="eg-head-actions">
            <?php if($payUrl):?>
            <a href="<?= htmlspecialchars($payUrl) ?>" class="eg-pay-btn" style="width:auto;padding:.42rem 1rem;font-size:.82rem">
                <i class="fa-duotone fa-cart-shopping"></i>Pay Now
            </a>
            <?php endif;?>
            <?php if(!$isChatLocked && !empty($order['egirl_id'])):?>
            <button type="button" class="eg-poke-btn js-client-poke-egirl">
                <i class="fa-duotone fa-hand-point-up"></i>Poke GG-Girl
            </button>
            <?php endif;?>
            <?php if(!$isChatLocked):?>
            <button type="button" class="eg-report-btn" data-bs-toggle="modal" data-bs-target="#reportModal">
                <i class="fa-duotone fa-flag"></i>Report Problem
            </button>
            <?php endif;?>
        </div>
    </div>
    <div class="eg-head-meta">
        <div class="eg-mp"><span class="mpk">Order</span><span class="mpv">#<?= $id ?></span></div>
        <div class="eg-mp"><span class="mpk">Price</span><span class="mpv g"><?= htmlspecialchars($fmtPrice($priceCents)) ?></span></div>
        <div class="eg-mp"><span class="mpk">GG-Girl</span><span class="mpv" style="font-size:.75rem"><?= $egirlName ?></span></div>
        <div class="eg-mp"><span class="mpk">Mode</span><span class="mpv"><?= htmlspecialchars($egD['mode']) ?></span></div>
        <div class="eg-mp"><span class="mpk">Server</span><span class="mpv"><?= htmlspecialchars($egD['server']) ?></span></div>
        <div class="eg-mp"><span class="mpk">Amount</span><span class="mpv"><?= htmlspecialchars($egD['amount']) ?></span></div>
        <?php if(!empty($order['created_at'])):?><div class="eg-mp"><span class="mpk">Booked</span><span class="mpv" style="font-size:.72rem"><?= date('d.m.Y',strtotime($order['created_at'])) ?></span></div><?php endif;?>
    </div>
</div>

<!-- 2-col grid -->
<div class="eg-grid">
    <div class="d-flex flex-column gap-3">

        <!-- Voice Order box -->
        <?php if(!$isChatLocked):?>
        <div class="eg-voice-box">
            <div class="eg-voice-icon"><i class="fa-brands fa-discord"></i></div>
            <div style="width:100%">
                <div class="eg-voice-title"><i class="fa-solid fa-microphone me-2" style="color:#818cf8;font-size:.85rem"></i>Voice Order</div>
                <div class="eg-voice-sub">
                    <ol style="margin:.35rem 0 0 1.05rem;padding:0;line-height:1.8">
                        <li>Join server</li>
                        <li>Send customer invite, <strong style="color:#c084fc">lolboost.gg/streaming</strong></li>
                        <li>Complete order with voice</li>
                        <li><strong style="color:#ff8f8f">No Discord DM, no adding, website chat only</strong></li>
                    </ol>
                </div>
                <a href="https://lolboost.gg/streaming" target="_blank" rel="noopener" class="eg-voice-btn">
                    <i class="fa-brands fa-discord"></i>Join Server
                </a>
            </div>
        </div>
        <?php endif;?>

        <!-- Chat or pay prompt -->
        <?php if($statusRaw === 'UNPAID' && $payUrl):?>
        <div class="card">
            <div class="card-body text-center" style="padding:2rem">
                <div style="font-size:2rem;margin-bottom:.75rem">💳</div>
                <h5 style="color:rgba(255,255,255,.85);margin-bottom:.4rem">Complete your payment to start chatting</h5>
                <p style="font-size:.85rem;color:rgba(255,255,255,.4);margin-bottom:1.2rem">Session and chat available after payment is confirmed.</p>
                <a href="<?= htmlspecialchars($payUrl) ?>" class="eg-pay-btn" style="display:inline-flex;width:auto;padding:.55rem 1.5rem">
                    <i class="fa-duotone fa-cart-shopping"></i>Pay <?= htmlspecialchars($fmtPrice($priceCents)) ?> Now
                </a>
            </div>
        </div>
        <?php elseif(!$isChatLocked):?>
        <div class="card" style="overflow:hidden">
            <div class="card-header d-flex align-items-center justify-content-between" style="padding:.7rem 1.1rem">
                <h5 class="card-header-title mb-0" style="font-size:.88rem">
                    <i class="fa-duotone fa-messages me-2" style="color:#a855f7"></i>Session Chat
                    <?php if($egirlName && $egirlName!=='—'):?><span style="font-size:.72rem;font-weight:600;color:rgba(255,255,255,.32);margin-left:.3rem">with <?= $egirlName ?></span><?php endif;?>
                </h5>
                <span class="eg-mc" id="chatMsgCount"><?= count($messages) ?> msg<?= count($messages)!==1?'s':'' ?></span>
            </div>
            <div>
                <div class="eg-chat-bg" id="egChatBody">
                    <?php if(empty($messages)):?>
                    <div class="eg-chat-empty" id="egChatEmpty"><i class="fa-duotone fa-messages"></i><span style="font-size:.8rem">No messages yet — say hi! 👋</span></div>
                    <?php else:
                        $lS='';$lSid=0;
                        foreach($messages as $msg):
                            $senderType=(string)($msg['sender'] ?? ($msg['sender_type'] ?? 'booster'));
                            $senderId=(int)($msg['sender_id']??0);
                            $mine=($senderType==='client'&&$senderId===$myId);
                            $grp=($senderType===$lS&&$senderId===$lSid);
                            $lS=$senderType;$lSid=$senderId;
                            $role = strtolower((string)$senderType);
                            if($mine){
                                $sN = 'You';
                                $sI = $myIcon;
                                $sX = $myInit;
                                $roleLabel = 'Client';
                                $roleClass = 'eg-role-client';
                            } elseif($role==='admin' || $role==='administrator'){
                                $sN = $msg['sender_name'] ?? 'Admin';
                                $sI = $msg['sender_icon'] ?? '';
                                $sX = 'A';
                                $roleLabel = 'Admin';
                                $roleClass = 'eg-role-admin';
                            } else {
                                $sN = $msg['sender_name'] ?? html_entity_decode($egirlName, ENT_QUOTES, 'UTF-8');
                                $sI = $msg['sender_icon'] ?? $egirlIcon;
                                $sX = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $sN)?:'E',0,1));
                                $roleLabel = 'GG-Girl';
                                $roleClass = 'eg-role-egirl';
                            }
                            $msgText=$msg['raw']??strip_tags($msg['content']??'');
                            $ts=!empty($msg['time'])?date('H:i',(int)$msg['time']):'';
                    ?>
                    <div class="eg-m <?= $mine?'me':'them' ?>">
                        <?php if(!$grp):?><div class="eg-m-head"><div class="eg-m-av <?= $mine?'mine':'' ?>"><?php if($sI):?><img src="<?= htmlspecialchars($sI)?>" alt=""><?php else:?><?= $sX ?><?php endif;?></div><div class="eg-m-name"><?= htmlspecialchars($sN) ?><span class="eg-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span></div></div><?php endif;?>
                        <div class="eg-m-bbl"><?php
                            $isImgMsg = ($msg['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $msgText);
                            if ($isImgMsg):
                        ?><img src="<?= htmlspecialchars($msgText) ?>" class="eg-chat-img" data-chat-image="<?= htmlspecialchars($msgText) ?>" loading="lazy" alt="Chat image"><?php else: ?><?= nl2br(htmlspecialchars($msgText)) ?><?php endif; ?></div>
                        <div class="eg-m-ts"><?= $ts ?></div>
                    </div>
                    <?php endforeach;endif;?>
                </div>
                <div class="eg-chat-footer" style="flex-direction:column;gap:.4rem;">
                    <div class="d-none" id="egImgPreviewWrap" style="padding:.2rem 0">
                        <div style="position:relative;display:inline-block;max-width:130px;border-radius:9px;overflow:hidden;border:1px solid rgba(168,85,247,.25);">
                            <img id="egImgPreview" src="" alt="" style="width:130px;height:82px;object-fit:cover;display:block;">
                            <button type="button" id="egImgRemove" style="position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,.75);border:none;color:#fff;font-size:.62rem;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <div style="display:flex;gap:.45rem;align-items:flex-end;width:100%">
                        <textarea class="eg-chat-ta" id="egChatIn" rows="1" placeholder="Type a message… (Enter to send)"></textarea>
                        <button type="button" id="egEmojiBtn" title="Emoji" style="width:34px;height:34px;border-radius:8px;flex-shrink:0;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <button type="button" id="egUploadBtn" title="Upload image" style="width:34px;height:34px;border-radius:8px;flex-shrink:0;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;">
                            <i class="fa-duotone fa-paperclip"></i>
                        </button>
                        <input type="file" id="egFileInput" accept="image/*" class="d-none">
                        <button type="button" class="eg-chat-send" id="egChatSend"><i class="fa-solid fa-paper-plane-top"></i></button>
                    </div>
                    <div id="egEmojiPicker" class="d-none" style="position:absolute;bottom:72px;right:14px;z-index:1075;background:rgba(22,16,38,.98);border:1px solid rgba(168,85,247,.22);border-radius:13px;padding:9px;width:274px;max-width:calc(100vw - 28px);box-shadow:0 20px 55px rgba(0,0,0,.55);display:flex;flex-wrap:wrap;gap:5px;">
<button type="button" class="eg-emoji" data-emoji="😀">😀</button><button type="button" class="eg-emoji" data-emoji="😁">😁</button><button type="button" class="eg-emoji" data-emoji="😂">😂</button><button type="button" class="eg-emoji" data-emoji="🤣">🤣</button><button type="button" class="eg-emoji" data-emoji="😊">😊</button><button type="button" class="eg-emoji" data-emoji="😉">😉</button><button type="button" class="eg-emoji" data-emoji="😍">😍</button><button type="button" class="eg-emoji" data-emoji="😘">😘</button><button type="button" class="eg-emoji" data-emoji="😎">😎</button><button type="button" class="eg-emoji" data-emoji="🤔">🤔</button><button type="button" class="eg-emoji" data-emoji="😴">😴</button><button type="button" class="eg-emoji" data-emoji="😭">😭</button><button type="button" class="eg-emoji" data-emoji="😡">😡</button><button type="button" class="eg-emoji" data-emoji="👍">👍</button><button type="button" class="eg-emoji" data-emoji="👎">👎</button><button type="button" class="eg-emoji" data-emoji="🙏">🙏</button><button type="button" class="eg-emoji" data-emoji="🙌">🙌</button><button type="button" class="eg-emoji" data-emoji="👏">👏</button><button type="button" class="eg-emoji" data-emoji="🎉">🎉</button><button type="button" class="eg-emoji" data-emoji="🔥">🔥</button><button type="button" class="eg-emoji" data-emoji="💯">💯</button><button type="button" class="eg-emoji" data-emoji="✅">✅</button><button type="button" class="eg-emoji" data-emoji="❌">❌</button><button type="button" class="eg-emoji" data-emoji="⚡">⚡</button><button type="button" class="eg-emoji" data-emoji="⭐">⭐</button><button type="button" class="eg-emoji" data-emoji="💙">💙</button><button type="button" class="eg-emoji" data-emoji="💚">💚</button><button type="button" class="eg-emoji" data-emoji="💛">💛</button><button type="button" class="eg-emoji" data-emoji="💜">💜</button><button type="button" class="eg-emoji" data-emoji="🫡">🫡</button><button type="button" class="eg-emoji" data-emoji="🤝">🤝</button><button type="button" class="eg-emoji" data-emoji="🥳">🥳</button><button type="button" class="eg-emoji" data-emoji="❤">❤</button><button type="button" class="eg-emoji" data-emoji="🎮">🎮</button><button type="button" class="eg-emoji" data-emoji="💪">💪</button><button type="button" class="eg-emoji" data-emoji="🏆">🏆</button><button type="button" class="eg-emoji" data-emoji="👑">👑</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>

        <!-- Session details -->
        <div class="card">
            <div class="card-header" style="padding:.7rem 1.1rem">
                <h5 class="card-header-title mb-0" style="font-size:.88rem"><i class="fa-duotone fa-scroll me-2" style="color:#a855f7"></i>Order Details</h5>
            </div>
            <div class="card-body" style="padding:.7rem 1.1rem">
                <ul class="eg-ov-list">
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-gamepad-modern"></i></span><span class="eg-ov-lbl">Mode</span><span class="eg-ov-val"><?= htmlspecialchars($egD['mode']) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-server"></i></span><span class="eg-ov-lbl">Server</span><span class="eg-ov-val"><?= htmlspecialchars($egD['server']) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-ranking-star"></i></span><span class="eg-ov-lbl">Rank</span><span class="eg-ov-val"><?= htmlspecialchars($egD['rank']) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-list-ol"></i></span><span class="eg-ov-lbl">Amount</span><span class="eg-ov-val"><?= htmlspecialchars($egD['amount']) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-user-check"></i></span><span class="eg-ov-lbl">Assignment</span><span class="eg-ov-val"><?= htmlspecialchars($egD['assignment']) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-<?= $orderCurrency === 'USD' ? 'dollar-sign' : 'euro-sign' ?>"></i></span><span class="eg-ov-lbl">Price</span><span class="eg-ov-val" style="color:#1fe6c6;font-weight:900"><?= htmlspecialchars($fmtPrice($priceCents)) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-calendar"></i></span><span class="eg-ov-lbl">Booked</span><span class="eg-ov-val" style="font-size:.76rem;color:rgba(255,255,255,.38)"><?= !empty($order['created_at'])?date('d.m.Y · H:i',strtotime($order['created_at'])):'—' ?></span></li>
                    <?php if(!empty($order['claimed_at'])):?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-play"></i></span><span class="eg-ov-lbl">Started</span><span class="eg-ov-val" style="font-size:.76rem;color:rgba(255,255,255,.38)"><?= date('d.m.Y · H:i',strtotime($order['claimed_at'])) ?></span></li><?php endif;?>
                    <?php if(!empty($order['completed_at'])):?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-flag-checkered"></i></span><span class="eg-ov-lbl">Completed</span><span class="eg-ov-val" style="font-size:.76rem;color:#1fe6c6"><?= date('d.m.Y · H:i',strtotime($order['completed_at'])) ?></span></li><?php endif;?>
                </ul>
            </div>
        </div>
    </div>

    <!-- RIGHT sidebar -->
    <div class="eg-sidebar">
        <?php if($payUrl):?><a href="<?= htmlspecialchars($payUrl) ?>" class="eg-pay-btn"><i class="fa-duotone fa-cart-shopping"></i>Pay <?= htmlspecialchars($fmtPrice($priceCents)) ?></a><?php endif;?>

        <div class="eg-eg-card">
            <div class="eg-eg-banner"></div>
            <div class="eg-eg-body">
                <div class="eg-eg-av-wrap">
                    <div class="eg-eg-av"><?php if($egirlIcon):?><img src="<?= htmlspecialchars($egirlIcon)?>" alt=""><?php else:?><?= $egirlInit ?><?php endif;?></div>
                    <span class="eg-eg-dot"></span>
                </div>

                <div class="eg-eg-name"><?= $egirlName ?></div>
                <div class="eg-eg-role">E-Girl</div>

                <div class="eg-eg-tags">
                    <span class="eg-eg-tag">Profile</span>
                </div>

                <a href="/egirls/<?= (int)($order['egirl_id'] ?? 757) ?>" class="eg-eg-link">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    View GG-Girl Profile
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="padding:.62rem .95rem"><h5 class="card-header-title mb-0" style="font-size:.8rem"><i class="fa-duotone fa-receipt me-2" style="color:#a855f7"></i>Summary</h5></div>
            <div class="card-body" style="padding:.55rem .95rem">
                <ul class="eg-ov-list">
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-hashtag"></i></span><span class="eg-ov-lbl">ID</span><span class="eg-ov-val">#<?= $id ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-circle-dot"></i></span><span class="eg-ov-lbl">Status</span><span class="eg-sp <?= $statusKey ?>" style="font-size:.59rem;padding:.09rem .45rem"><?= str_replace('_',' ',$statusRaw) ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-coins"></i></span><span class="eg-ov-lbl">Total</span><span class="eg-ov-val" style="color:#1fe6c6"><?= htmlspecialchars($fmtPrice($priceCents)) ?></span></li>
                </ul>
            </div>
        </div>

        <?php if($statusRaw === 'COMPLETED'): ?>
        <div class="card">
            <div class="card-body" style="padding:.85rem .95rem">
                <?php if($review): ?>
                    <div style="display:flex;align-items:center;gap:.65rem">
                        <span style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.12);color:#fbbf24;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-star"></i></span>
                        <div>
                            <div style="font-weight:800;font-size:.82rem">Your Review</div>
                            <div style="color:#fbbf24;font-size:.75rem"><?= str_repeat('★', (int)$review['rating']) ?><span style="color:rgba(255,255,255,.18)"><?= str_repeat('★', 5 - (int)$review['rating']) ?></span></div>
                            <div style="font-size:.67rem;color:#a7f3d0">Review submitted</div>
                        </div>
                    </div>
                <?php else: ?>
                    <button type="button" class="eg-rate-session-btn js-open-egirl-review">
                        <span><i class="fa-duotone fa-star"></i></span>
                        <span><strong>Rate your session</strong><small>Share your experience with <?= $egirlName ?></small></span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!$isChatLocked):?>
        <button type="button" class="eg-report-btn w-100 justify-content-center" data-bs-toggle="modal" data-bs-target="#reportModal" style="padding:.55rem">
            <i class="fa-duotone fa-flag"></i>Report a Problem
        </button>
        <?php endif;?>
    </div>
</div>

<?php if($statusRaw === 'COMPLETED'): ?>
<div class="modal fade" id="egirlCompletedModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content eg-completed-modal">
            <div class="modal-header">
                <div style="display:flex;align-items:center;gap:13px">
                    <span class="eg-completed-party"><i class="fa-duotone fa-party-horn"></i></span>
                    <div>
                        <h5 class="modal-title">GG! Session completed 🎉</h5>
                        <div class="small text-muted">Booking #<?= $id ?> · <?= htmlspecialchars($svcName) ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="eg-completed-choice">
                            <div style="display:flex;align-items:center;gap:12px">
                                <span class="eg-completed-avatar"><?php if($egirlIcon): ?><img src="<?= htmlspecialchars($egirlIcon) ?>" alt=""><?php else: ?><?= $egirlInit ?><?php endif; ?></span>
                                <div>
                                    <strong>Rate your GG-Girl</strong>
                                    <div class="small text-muted">Quick feedback helps us improve future sessions.</div>
                                </div>
                            </div>
                            <button type="button" class="btn eg-completed-review-btn mt-3 <?= !$review ? 'js-open-egirl-review' : '' ?>">
                                <i class="fa-duotone fa-star me-2"></i><?= $review ? 'Review submitted' : 'Leave a Review' ?>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="eg-completed-choice">
                            <strong>Review us on Trustpilot</strong>
                            <div class="small text-muted">Tap a star to open Trustpilot in a new tab.</div>
                            <div class="eg-trustpilot-stars mt-3" aria-label="Trustpilot rating">
                                <?php for($tpStar = 1; $tpStar <= 5; $tpStar++): ?>
                                <button type="button" data-rating="<?= $tpStar ?>" aria-label="<?= $tpStar ?> stars"><i class="fa-regular fa-star"></i></button>
                                <?php endfor; ?>
                            </div>
                            <a class="btn eg-trustpilot-btn mt-3" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank" rel="noopener">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Open Trustpilot
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="eg-customer-protection">
                            <span><i class="fa-duotone fa-shield-heart"></i></span>
                            <div><strong>Help us keep LoLBoost safe and fair</strong><div>If anyone tries to arrange a private session outside our platform, please report it to us.</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="justify-content:space-between">
                <button type="button" class="btn btn-secondary" id="egirlCompletedDismiss">I don't want to review now</button>
                <span class="small text-muted">You can review anytime from this order page.</span>
            </div>
        </div>
    </div>
</div>
<style>
.eg-completed-modal{background:#242728;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden}
.eg-completed-party{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:13px;background:rgba(99,102,241,.16);border:1px solid rgba(129,140,248,.25);color:#a5b4fc;font-size:1.15rem}
.eg-completed-choice{height:100%;padding:17px;border-radius:13px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.055)}
.eg-completed-avatar{width:46px;height:46px;flex:0 0 46px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(168,85,247,.15);color:#d8b4fe;font-weight:900;overflow:hidden}
.eg-completed-avatar img{width:100%;height:100%;object-fit:cover}.eg-completed-review-btn{background:#6d43ec;color:#fff;font-weight:800}.eg-completed-review-btn:hover{background:#7c55ee;color:#fff}
.eg-trustpilot-stars{display:flex;gap:8px}.eg-trustpilot-stars button{width:42px;height:42px;border-radius:12px;border:1px solid rgba(45,212,191,.25);background:rgba(255,255,255,.025);color:#2dd4bf;font-size:1.1rem}
.eg-trustpilot-stars button:hover,.eg-trustpilot-stars button.active{background:rgba(45,212,191,.12);color:#5eead4}.eg-trustpilot-btn{background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.07);color:#fff}
.eg-customer-protection{display:flex;align-items:center;gap:14px;padding:18px;border-radius:13px;background:linear-gradient(135deg,rgba(108,92,231,.18),rgba(0,194,255,.1));border:1px solid rgba(108,92,231,.45)}
.eg-customer-protection>span{width:42px;height:42px;flex:0 0 42px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:rgba(108,92,231,.2);color:#c9b8ff}.eg-customer-protection div div{margin-top:4px;color:rgba(255,255,255,.66);font-size:.78rem}
</style>
<?php endif; ?>

<?php if($statusRaw === 'COMPLETED' && !$review): ?>
<div class="modal fade" id="egirlReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered eg-review-dialog">
        <div class="modal-content eg-review-modal">
            <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
                <input type="hidden" name="action" value="egirl_submit_review">
                <input type="hidden" name="order_id" value="<?= $id ?>">
                <div class="eg-review-head">
                    <div class="eg-review-person">
                        <span class="eg-review-avatar"><?php if($egirlIcon): ?><img src="<?= htmlspecialchars($egirlIcon) ?>" alt=""><?php else: ?><?= $egirlInit ?><?php endif; ?></span>
                        <div>
                            <span class="eg-review-kicker">Session completed</span>
                            <h5>How was your session?</h5>
                            <p>Your feedback for <strong><?= $egirlName ?></strong> helps improve future matches.</p>
                        </div>
                    </div>
                    <button type="button" class="eg-review-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="eg-review-body">
                    <div class="eg-review-rating-label">Your rating</div>
                    <div class="eg-review-stars" role="radiogroup" aria-label="Rating">
                        <?php for($rating = 5; $rating >= 1; $rating--): ?>
                            <input type="radio" name="rating" id="egRating<?= $rating ?>" value="<?= $rating ?>" <?= $rating === 5 ? 'checked' : '' ?>>
                            <label for="egRating<?= $rating ?>" title="<?= $rating ?> star<?= $rating === 1 ? '' : 's' ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                    <div class="eg-review-rating-copy" id="egReviewRatingCopy">Excellent experience</div>
                    <div class="eg-review-comment">
                        <label>Tell us more <span>Optional</span></label>
                        <textarea class="form-control" name="comment" rows="4" maxlength="1000" placeholder="Tell us about your experience…"></textarea>
                        <small>Your review is checked before it appears publicly.</small>
                    </div>
                </div>
                <div class="eg-review-footer">
                    <button type="button" class="eg-review-cancel" data-bs-dismiss="modal">Maybe later</button>
                    <button type="submit" class="eg-review-submit"><i class="fa-solid fa-paper-plane"></i>Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.eg-review-dialog{max-width:540px}.eg-review-modal{overflow:hidden;border:1px solid rgba(168,85,247,.22);border-radius:20px;background:#1d2023;box-shadow:0 28px 90px rgba(0,0,0,.62)}
.eg-review-head{position:relative;padding:25px 26px 22px;background:radial-gradient(circle at 8% 0,rgba(168,85,247,.2),transparent 48%),linear-gradient(135deg,rgba(108,92,231,.1),rgba(31,230,198,.035));border-bottom:1px solid rgba(255,255,255,.07)}
.eg-review-person{display:flex;align-items:center;gap:15px;padding-right:30px}.eg-review-avatar{width:60px;height:60px;flex:0 0 60px;display:flex;align-items:center;justify-content:center;border-radius:16px;overflow:hidden;background:rgba(168,85,247,.16);border:1px solid rgba(192,132,252,.3);box-shadow:0 8px 28px rgba(168,85,247,.18);color:#d8b4fe;font-weight:900;font-size:1.15rem}
.eg-review-avatar img{width:100%;height:100%;object-fit:cover}.eg-review-kicker{display:block;margin-bottom:4px;color:#c084fc;font-size:.65rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.eg-review-head h5{margin:0;color:#fff;font-size:1.15rem;font-weight:900}.eg-review-head p{margin:5px 0 0;color:rgba(255,255,255,.48);font-size:.76rem;line-height:1.45}.eg-review-head p strong{color:rgba(255,255,255,.8)}
.eg-review-close{position:absolute;top:18px;right:18px;width:34px;height:34px;border:1px solid rgba(255,255,255,.08);border-radius:10px;background:rgba(255,255,255,.04);color:rgba(255,255,255,.48);transition:.15s}.eg-review-close:hover{background:rgba(255,255,255,.09);color:#fff}
.eg-review-body{padding:25px 26px}.eg-review-rating-label{text-align:center;color:rgba(255,255,255,.48);font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.eg-review-stars{display:flex;flex-direction:row-reverse;justify-content:center;gap:.65rem;margin-top:10px}.eg-review-stars input{position:absolute;opacity:0;pointer-events:none}
.eg-review-stars label{width:48px;height:48px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.08);border-radius:14px;background:rgba(255,255,255,.035);font-size:1.35rem;color:rgba(255,255,255,.14);cursor:pointer;transition:color .15s,transform .15s,background .15s,border-color .15s}
.eg-review-stars label:hover,.eg-review-stars label:hover~label,.eg-review-stars input:checked~label{color:#fbbf24;background:rgba(251,191,36,.09);border-color:rgba(251,191,36,.24)}.eg-review-stars label:hover{transform:translateY(-2px)}
.eg-review-rating-copy{margin-top:9px;text-align:center;color:#fbbf24;font-size:.73rem;font-weight:800}.eg-review-comment{margin-top:24px}.eg-review-comment label{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;color:rgba(255,255,255,.86);font-size:.78rem;font-weight:800}.eg-review-comment label span{padding:3px 8px;border-radius:999px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.35);font-size:.6rem;text-transform:uppercase;letter-spacing:.06em}
.eg-review-comment textarea.form-control{width:100%;min-height:116px;padding:14px 15px;resize:vertical;border:1px solid rgba(255,255,255,.09);border-radius:12px;outline:0;background:rgba(7,9,12,.45);color:#fff;font-size:.82rem;line-height:1.5;transition:.15s}.eg-review-comment textarea.form-control:focus{border-color:rgba(168,85,247,.5);box-shadow:0 0 0 3px rgba(168,85,247,.1);background:rgba(7,9,12,.55);color:#fff}.eg-review-comment textarea::placeholder{color:rgba(255,255,255,.25)}.eg-review-comment small{display:block;margin-top:7px;color:rgba(255,255,255,.3);font-size:.65rem}
.eg-review-footer{display:flex;justify-content:flex-end;gap:10px;padding:17px 26px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08)}.eg-review-cancel,.eg-review-submit{padding:10px 17px;border-radius:10px;font-size:.78rem;font-weight:850}.eg-review-cancel{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.055);color:rgba(255,255,255,.65)}.eg-review-submit{display:inline-flex;align-items:center;gap:8px;border:0;background:linear-gradient(135deg,#7c3aed,#9333ea);box-shadow:0 8px 24px rgba(124,58,237,.25);color:#fff}.eg-review-submit:hover{filter:brightness(1.1)}
@media(max-width:575px){.eg-review-head,.eg-review-body{padding-left:18px;padding-right:18px}.eg-review-avatar{width:52px;height:52px;flex-basis:52px}.eg-review-stars{gap:.35rem}.eg-review-stars label{width:42px;height:42px;border-radius:12px}.eg-review-footer{padding:15px 18px}.eg-review-submit,.eg-review-cancel{flex:1;justify-content:center}}
</style>
<?php endif; ?>

<!-- Report Problem Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-duotone fa-flag me-2" style="color:#ff6b6b"></i>Report a Problem</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.84rem;color:rgba(255,255,255,.5);margin-bottom:1rem">
                    Our support team will review your report and get back to you. Please be as specific as possible.
                </p>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.82rem;font-weight:700">Reason <span style="color:#ff6b6b">*</span></label>
                    <select class="form-select" id="reportReason">
                        <option value="">— Select a reason —</option>
                        <option value="GG-Girl did not show up">GG-Girl did not show up</option>
                        <option value="Session not started on time">Session not started on time</option>
                        <option value="Inappropriate behaviour">Inappropriate behaviour</option>
                        <option value="Quality of session was poor">Quality of session was poor</option>
                        <option value="Private contact attempt">GG-Girl tried to contact me privately</option>
                        <option value="Technical issues during session">Technical issues during session</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label" style="font-size:.82rem;font-weight:700">Additional details <span style="color:rgba(255,255,255,.3)">(optional)</span></label>
                    <textarea class="form-control" id="reportDetails" rows="3" placeholder="Describe what happened…" style="resize:none"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnSubmitReport"
                    style="background:linear-gradient(135deg,#ef4444,#ff6b6b);border:none;color:#fff;font-weight:800;padding:.5rem 1.2rem;border-radius:9px;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem">
                    <i class="fa-duotone fa-paper-plane"></i>Send Report
                </button>
            </div>
        </div>
    </div>
</div>

</div><!-- .order-page-wrap -->


<div class="modal fade" id="chatImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-duotone fa-image me-2" style="color:#a855f7"></i>Chat Image</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="chatImageModalImg" src="" alt="Chat image preview">
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    const AJAX    = '<?= AJAX_URL ?>';
    const SEND_ACTION='client_egirl_chat_send';
    const OID     = <?= $id ?>;
    const MY_ID   = <?= $myId ?>;
    const MY_NAME = <?= json_encode($myName) ?>;
    const MY_ICON = <?= json_encode($myIcon) ?>;
    const MY_INIT = <?= json_encode($myInit) ?>;
    const EG_NAME = <?= json_encode($order['egirl_username'] ?? 'E-Girl') ?>;
    const EG_ICON = <?= json_encode($egirlIcon) ?>;
    const EG_INIT = <?= json_encode($egirlInit) ?>;


    var EG_CHAT_SCOPE = 'client:' + OID;
    window.__lbEgirlChatScopes = window.__lbEgirlChatScopes || {};
    if (window.__lbEgirlChatScopes[EG_CHAT_SCOPE]) return;
    window.__lbEgirlChatScopes[EG_CHAT_SCOPE] = true;

    var lastTime = <?= $lastMsgTime ?>;

    const body = document.getElementById('egChatBody');
    const inp  = document.getElementById('egChatIn');
    const snd  = document.getElementById('egChatSend');
    const cnt  = document.getElementById('chatMsgCount');

    var egChatSending = false;
    var egChatReloading = false;
    var egRenderedMessages = Object.create(null);

    function egNormRole(role) {
        role = String(role || '').toLowerCase();
        if (role === 'e-girl' || role === 'egirl') return 'booster';
        return role;
    }
    function egNormText(text) {
        return String(text || '').replace(/\r\n/g, '\n').trim();
    }
    function egMessageSig(role, senderId, text) {
        return egNormRole(role) + '|' + String(parseInt(senderId || 0) || 0) + '|' + egNormText(text);
    }
    function egMessageKey(key, role, senderId, text, ts) {
        if (key) return 'uuid:' + String(key);
        return 'fallback:' + egMessageSig(role, senderId, text) + '|' + String(ts || '');
    }



    function egChatTextKey(v) {
        return String(v || '').replace(/\s+/g, ' ').trim();
    }
    function egDedupeDomMessages() {
        if (!body) return;
        var lastKey = '';
        var removed = false;
        Array.prototype.slice.call(body.querySelectorAll('.eg-m')).forEach(function(el){
            var side = el.classList.contains('me') ? 'me' : 'them';
            var b = el.querySelector('.eg-m-bbl');
            var t = el.querySelector('.eg-m-ts');
            var text = egChatTextKey(b ? b.innerText : el.innerText);
            var time = egChatTextKey(t ? t.innerText : '');
            if (!text) return;
            var key = side + '|' + text + '|' + time;
            if (key === lastKey) {
                el.remove();
                removed = true;
                return;
            }
            lastKey = key;
        });
        if (removed && cnt) {
            var n = body.querySelectorAll('.eg-m').length;
            cnt.textContent = n + ' msg' + (n !== 1 ? 's' : '');
        }
    }
    var egDedupeTimer = null;
    function egScheduleDedupe() {
        if (egDedupeTimer) clearTimeout(egDedupeTimer);
        egDedupeTimer = setTimeout(egDedupeDomMessages, 0);
    }
    if (body && window.MutationObserver) {
        new MutationObserver(egScheduleDedupe).observe(body, {childList:true, subtree:false});
        egScheduleDedupe();
    }

    const chatImageModalEl = document.getElementById('chatImageModal');
    const chatImageModalImg = document.getElementById('chatImageModalImg');
    const chatImageModal = (chatImageModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) ? new bootstrap.Modal(chatImageModalEl) : null;

    function escAttr(v){
        return String(v||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function renderChatImage(url){
        var safe = escAttr(url);
        return '<img src="'+safe+'" class="eg-chat-img" data-chat-image="'+safe+'" loading="lazy" alt="Chat image">';
    }
    document.addEventListener('click', function(e){
        var img = e.target.closest('.eg-chat-img');
        if(!img) return;
        e.preventDefault();
        var src = img.getAttribute('data-chat-image') || img.getAttribute('src');
        if(!src) return;
        if(chatImageModalImg) chatImageModalImg.src = src;
        if(chatImageModal) chatImageModal.show();
    });
    chatImageModalEl?.addEventListener('hidden.bs.modal', function(){
        if(chatImageModalImg) chatImageModalImg.src='';
    });
    if (body) body.scrollTop = body.scrollHeight;

    function mkAv(icon, init, mine) {
        var c = 'eg-m-av' + (mine ? ' mine' : '');
        return icon ? '<div class="' + c + '"><img src="' + icon.replace(/"/g,'&quot;') + '" alt=""></div>'
                    : '<div class="' + c + '">' + init + '</div>';
    }
    function roleBadge(role){
        role = String(role || '').toLowerCase();
        if(role === 'admin' || role === 'administrator') return '<span class="eg-role-badge eg-role-admin">Admin</span>';
        if(role === 'booster' || role === 'egirl' || role === 'e-girl') return '<span class="eg-role-badge eg-role-egirl">GG-Girl</span>';
        return '<span class="eg-role-badge eg-role-client">Client</span>';
    }
    function addMsg(text, mine, ts, icon, init, name, grp, role, key) {
        var renderedKey = egMessageKey(key, role, mine ? MY_ID : 0, text, ts);
        if (renderedKey && egRenderedMessages[renderedKey]) return false;
        if (renderedKey) egRenderedMessages[renderedKey] = true;
        var e = document.getElementById('egChatEmpty'); if (e) e.remove();
        var isImg=/\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(text)||text.indexOf('<img ')===0;
        var s=isImg
            ?renderChatImage(text)
            :text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        var h = '<div class="eg-m ' + (mine?'me':'them') + '">';
        if (!grp) h += '<div class="eg-m-head">' + mkAv(icon,init,mine) + '<div class="eg-m-name">' + (mine?'You':name.replace(/</g,'&lt;')) + roleBadge(role) + '</div></div>';
        h += '<div class="eg-m-bbl">' + s + '</div><div class="eg-m-ts">' + ts + '</div></div>';
        body.insertAdjacentHTML('beforeend', h);
        body.scrollTop = body.scrollHeight;
        if (cnt) { var n=(parseInt(cnt.textContent)||0)+1; cnt.textContent=n+' msg'+(n!==1?'s':''); }
        egScheduleDedupe();
        return true;
    }
    /* emoji picker */
    var egEmoji=document.getElementById('egEmojiBtn'),egPicker=document.getElementById('egEmojiPicker');
    if(egEmoji&&egPicker){
        egEmoji.addEventListener('click',function(e){e.stopPropagation();egPicker.classList.toggle('d-none');});
        egPicker.addEventListener('click',function(e){var b=e.target.closest('[data-emoji]');if(!b)return;var em=b.getAttribute('data-emoji');if(inp){var s=inp.selectionStart||0,en2=inp.selectionEnd||0,v=inp.value;inp.value=v.slice(0,s)+em+v.slice(en2);inp.setSelectionRange(s+em.length,s+em.length);inp.focus();}egPicker.classList.add('d-none');});
        document.addEventListener('click',function(e){if(egPicker.classList.contains('d-none'))return;if(!egPicker.contains(e.target)&&!egEmoji.contains(e.target))egPicker.classList.add('d-none');});
    }
    /* image upload + paste */
    var egUpBtn=document.getElementById('egUploadBtn'),egFileIn=document.getElementById('egFileInput');
    var egPrevWrap=document.getElementById('egImgPreviewWrap'),egPrevImg=document.getElementById('egImgPreview'),egRemBtn=document.getElementById('egImgRemove');
    var selectedFile=null;
    function setPreview(f){selectedFile=f||null;if(!egPrevWrap||!egPrevImg)return;if(!selectedFile){egPrevWrap.classList.add('d-none');egPrevImg.src='';if(egFileIn)egFileIn.value='';return;}egPrevImg.src=URL.createObjectURL(selectedFile);egPrevWrap.classList.remove('d-none');}
    if(egUpBtn&&egFileIn){egUpBtn.addEventListener('click',function(){egFileIn.click();});egFileIn.addEventListener('change',function(){var f=egFileIn.files&&egFileIn.files[0];if(f)setPreview(f);});}
    if(egRemBtn)egRemBtn.addEventListener('click',function(){setPreview(null);});
    function handlePaste(e){var items=(e.clipboardData&&e.clipboardData.items)?Array.from(e.clipboardData.items):[];var img=items.find(function(it){return it&&it.type&&it.type.indexOf('image/')===0;});if(!img)return;var f=img.getAsFile();if(!f)return;e.preventDefault();setPreview(f);}
    if(inp)inp.addEventListener('paste',handlePaste);else document.addEventListener('paste',handlePaste);
    /* send */
    function egChat_send(){
        if(!inp||!snd||egChatSending)return;
        var txt=inp.value.trim();
        if(!txt&&!selectedFile)return;
        egChatSending=true;
        snd.disabled=true;inp.disabled=true;
        function onDone(r){
            try{if(typeof r==='string')r=JSON.parse(r);}catch(e){r={};}
            inp.value='';inp.style.height='';
            setPreview(null);
            if(r&&r.success!==false){
                // No local reload here. The sent message is delivered back through WebSocket.
            }
            egChatSending=false;snd.disabled=false;inp.disabled=false;inp.focus();
        }
        function onFail(){egChatSending=false;snd.disabled=false;inp.disabled=false;inp.focus();}
        var xhr=new XMLHttpRequest();
        xhr.open('POST',AJAX,true);
        xhr.onload=function(){onDone(xhr.responseText);};
        xhr.onerror=onFail;
        if(selectedFile){
            var fd=new FormData();
            fd.append('action',SEND_ACTION);fd.append('order_id',OID);
            if(txt)fd.append('message',txt);
            fd.append('chat_image',selectedFile,selectedFile.name||'image.png');
            xhr.send(fd);
        }else{
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.send('action='+encodeURIComponent(SEND_ACTION)+'&order_id='+OID+'&message='+encodeURIComponent(txt));
        }
    }
    snd?.addEventListener('click',egChat_send);
    inp?.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();egChat_send();}});
    inp?.addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,88)+'px';});
<?php if(!$isChatLocked):?>
    function renderEgirlMessages(messages){
        if(!body) return;
        messages = Array.isArray(messages) ? messages : [];
        body.innerHTML = '';
        egRenderedMessages = Object.create(null);
        var pS='', pSid=0, maxTime=0, rendered=0;
        if(cnt) cnt.textContent = '0 msgs';
        if(!messages.length){
            body.innerHTML = '<div class="eg-chat-empty" id="egChatEmpty"><i class="fa-duotone fa-messages"></i><span style="font-size:.8rem">No messages yet — say hi! 👋</span></div>';
            lastTime = 0;
            return;
        }
        messages.forEach(function(m){
            var senderType = m.sender || m.sender_type || '';
            var mine = senderType==='client' && parseInt(m.sender_id)===MY_ID;
            var isAdmin = senderType==='admin' || senderType==='administrator';
            var icon = m.sender_icon || (mine ? MY_ICON : (isAdmin ? '' : EG_ICON));
            var init = mine ? MY_INIT : (isAdmin ? 'A' : EG_INIT);
            var name = mine ? MY_NAME : (m.sender_name || (isAdmin ? 'Admin' : EG_NAME));
            var grp  = (senderType===pS && parseInt(m.sender_id)===pSid);
            pS=senderType; pSid=parseInt(m.sender_id);
            var raw = m.raw || m.content || m.message || '';
            var t = m.time ? new Date(m.time*1000) : new Date();
            var ts = String(t.getHours()).padStart(2,'0')+':'+String(t.getMinutes()).padStart(2,'0');
            var role = (isAdmin ? 'admin' : (mine ? 'client' : 'egirl'));
            if(addMsg(raw, mine, ts, icon, init, name, grp, role, m.uuid || 'poll:'+senderType+':'+String(m.sender_id||0)+':'+String(m.time||0)+':'+egNormText(raw))) rendered++;
            maxTime = Math.max(maxTime, parseInt(m.time)||0);
        });
        lastTime = maxTime;
        if(cnt) cnt.textContent = rendered+' msg'+(rendered!==1?'s':'');
        egDedupeDomMessages();
        body.scrollTop = body.scrollHeight;
    }
    function reloadEgirlMessages(){
        if(egChatReloading) return;
        egChatReloading = true;
        $.ajax({url:AJAX,type:'POST',global:false,
            data:{action:'client_egirl_chat_poll',order_id:OID,after_time:0}})
        .done(function(r){
            if (typeof r==='string') try{r=JSON.parse(r);}catch(e){}
            renderEgirlMessages((r && r.messages) ? r.messages : []);
        }).always(function(){ egChatReloading = false; });
    }
    function pollEgirlMessages(){ reloadEgirlMessages(); }

    function egMatchRealtimeOrder(data) {
        if (!data) return true;
        var expected = 'eg_' + OID;
        var values = [data.order_id, data.eg_order_id, data.egirl_order_id, data.chat_order_id];
        for (var i = 0; i < values.length; i++) {
            if (values[i] === undefined || values[i] === null) continue;
            var v = String(values[i]);
            if (v === expected || v === String(OID)) return true;
            if (v.indexOf('eg_') === 0 && v.substring(3) === String(OID)) return true;
        }
        return false;
    }

    function egAppendRealtimeMessage(m) {
        if (!m || !body) return false;
        var senderType = String(m.sender || m.sender_type || '').toLowerCase();
        var senderId = parseInt(m.sender_id || 0) || 0;
        var mine = false;
        if (typeof MID !== 'undefined') {
            mine = senderId === MID && (senderType === 'booster' || senderType === 'egirl' || senderType === 'e-girl');
        } else if (typeof MY_ID !== 'undefined') {
            mine = senderId === MY_ID && senderType === 'client';
        }
        var isAdmin = senderType === 'admin' || senderType === 'administrator';
        var raw = (m.raw || m.content || m.message || '').toString();
        if (!raw) return false;
        var t = m.time ? new Date((parseInt(m.time) || 0) * 1000) : new Date();
        var ts = String(t.getHours()).padStart(2, '0') + ':' + String(t.getMinutes()).padStart(2, '0');
        var icon = m.sender_icon || '';
        var init = 'A';
        var name = m.sender_name || (isAdmin ? 'Admin' : 'User');
        var role = isAdmin ? 'admin' : senderType;
        if (typeof MID !== 'undefined') {
            icon = icon || (mine ? MI : (isAdmin ? '' : CI));
            init = mine ? MX : (isAdmin ? 'A' : CN.charAt(0).toUpperCase());
            name = mine ? 'You' : (m.sender_name || (isAdmin ? 'Admin' : CN));
            role = isAdmin ? 'admin' : (mine ? 'egirl' : 'client');
        } else if (typeof MY_ID !== 'undefined') {
            icon = icon || (mine ? MY_ICON : (isAdmin ? '' : EG_ICON));
            init = mine ? MY_INIT : (isAdmin ? 'A' : EG_INIT);
            name = mine ? MY_NAME : (m.sender_name || (isAdmin ? 'Admin' : EG_NAME));
            role = isAdmin ? 'admin' : (mine ? 'client' : 'egirl');
        }
        return addMsg(raw, mine, ts, icon, init, name, false, role, m.uuid || ('ws:' + senderType + ':' + senderId + ':' + String(m.time || 0) + ':' + egNormText(raw)));
    }

    function egRealtimeReload(data) {
        if (!egMatchRealtimeOrder(data)) return;
        var rendered = false;
        var list = [];
        if (data && Array.isArray(data.messages)) list = data.messages;
        else if (data && data.message) list = [data.message];
        else if (data && (data.raw || data.content)) list = [data];
        if (list.length) {
            list.forEach(function (m) { if (egAppendRealtimeMessage(m)) rendered = true; });
            if (rendered && typeof egDedupeDomMessages === 'function') egDedupeDomMessages();
            return;
        }
        // Fallback for older socket payloads only: one AJAX reload per WebSocket event, never interval polling.
        reloadEgirlMessages();
    }

    window.lbOrderViewChatUpdate = egRealtimeReload;

    function egBindRealtimeSocket() {
        var sock = window.lbSocket;
        if (!sock || sock.__lbEgirlOrderBound === OID) return true;
        sock.__lbEgirlOrderBound = OID;
        try { sock.off && sock.off('chat_update', egRealtimeReload); } catch (e) {}
        try { sock.on('chat_update', egRealtimeReload); } catch (e) {}
        try { sock.emit('join', 'boosters'); } catch (e) {}
        try { sock.emit('join', 'clients'); } catch (e) {}
        try { sock.emit('join', 'eg_' + OID); } catch (e) {}
        try { sock.emit('order:join', { order_id: 'eg_' + OID, egirl_order_id: OID, order_type: 'egirl_session' }); } catch (e) {}
        return true;
    }
    (function egBindRealtimeSocketRetry(i) {
        if (egBindRealtimeSocket()) return;
        if (i < 30) setTimeout(function () { egBindRealtimeSocketRetry(i + 1); }, 500);
    })(0);
    <?php endif;?>

    window.lbOrderViewStatusUpdate = function(data){
        data = data || {};
        var statusOrderId = String(data.egirl_order_id || data.order_id || '');
        if (statusOrderId.indexOf('eg_') === 0) statusOrderId = statusOrderId.substring(3);
        if (statusOrderId && statusOrderId !== String(OID)) return;
        if (window.__egirlStatusReloadPending) return;
        window.__egirlStatusReloadPending = true;
        var nextStatus = String((data && (data.status || data.order_status)) || '').toUpperCase();
        if (nextStatus === 'COMPLETED') {
            try { sessionStorage.setItem('egirl_just_completed_' + OID, '1'); } catch(e) {}
        }
        window.setTimeout(function(){ window.location.reload(); }, 350);
    };

    function showPokeToast(toast){
        toast = toast || {};
        var wrap = document.querySelector('.eg-poke-toast-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'eg-poke-toast-wrap';
            document.body.appendChild(wrap);
        }
        var type = ['success','warning','danger'].indexOf(toast.type) !== -1 ? toast.type : 'success';
        var item = document.createElement('div');
        item.className = 'eg-poke-toast ' + type;
        item.innerHTML = '<span class="eg-poke-toast__icon"><i class="fa-duotone fa-hand-point-up"></i></span><span class="eg-poke-toast__copy"><span class="eg-poke-toast__title"></span><span class="eg-poke-toast__message"></span></span>';
        item.querySelector('.eg-poke-toast__title').textContent = toast.title || 'GG-Girl poked';
        item.querySelector('.eg-poke-toast__message').textContent = toast.message || '';
        wrap.appendChild(item);
        window.setTimeout(function(){ item.remove(); }, 5500);
    }
    function handlePokeResp(r){
        r = r || {};
        if (r.sendToast) showPokeToast(r.sendToast);
    }
    var pokeStorageKey = 'egirl_poke_cooldown_' + OID;
    var pokeTimer = null;
    function startPokeCountdown(seconds){
        var endAt = Date.now() + (Math.max(1, parseInt(seconds || 300, 10)) * 1000);
        try { localStorage.setItem(pokeStorageKey, String(endAt)); } catch(e) {}
        renderPokeCountdown(endAt);
    }
    function renderPokeCountdown(endAt){
        if (pokeTimer) window.clearInterval(pokeTimer);
        var $btn = $('.js-client-poke-egirl');
        function tick(){
            var left = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));
            if (left <= 0) {
                window.clearInterval(pokeTimer);
                pokeTimer = null;
                try { localStorage.removeItem(pokeStorageKey); } catch(e) {}
                $btn.prop('disabled', false).removeClass('is-loading').html('<i class="fa-duotone fa-hand-point-up"></i>Poke GG-Girl');
                return;
            }
            var min = Math.floor(left / 60);
            var sec = String(left % 60).padStart(2, '0');
            $btn.prop('disabled', true).removeClass('is-loading').html('<i class="fa-duotone fa-clock"></i>Poke again in ' + min + ':' + sec);
        }
        tick();
        pokeTimer = window.setInterval(tick, 1000);
    }
    try {
        var savedPokeEnd = parseInt(localStorage.getItem(pokeStorageKey) || '0', 10);
        if (savedPokeEnd > Date.now()) renderPokeCountdown(savedPokeEnd);
        else localStorage.removeItem(pokeStorageKey);
    } catch(e) {}
    $('.js-client-poke-egirl').on('click', function(){
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true).addClass('is-loading');
        $.post(AJAX, { action: 'client_poke_egirl', order_id: OID }, function(r){
              handlePokeResp(r);
              if (r && (r.success || r.cooldown_seconds)) startPokeCountdown(r.cooldown_seconds || 300);
              else $btn.prop('disabled', false);
          }, 'json')
          .fail(function(xhr){
              var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
              if (!response && xhr && xhr.responseText) {
                  try { response = JSON.parse(xhr.responseText); } catch (e) {}
              }
              handlePokeResp(response || {sendToast:{title:'Error',message:'Could not send poke.'}});
              if (response && response.cooldown_seconds) startPokeCountdown(response.cooldown_seconds);
              else $btn.prop('disabled', false);
          })
          .always(function(){ $btn.removeClass('is-loading'); });
    });

    <?php if($statusRaw === 'COMPLETED'): ?>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.js-open-egirl-review').forEach(function(button){
            button.addEventListener('click', function(){
                var reviewEl = document.getElementById('egirlReviewModal');
                if (!reviewEl || !window.bootstrap) return;
                var currentModal = button.closest('.modal');
                if (currentModal) {
                    var currentInstance = bootstrap.Modal.getInstance(currentModal);
                    if (currentInstance) currentInstance.hide();
                }
                window.setTimeout(function(){
                    bootstrap.Modal.getOrCreateInstance(reviewEl).show();
                }, currentModal ? 280 : 0);
            });
        });
        var ratingCopy = document.getElementById('egReviewRatingCopy');
        var ratingLabels = {1:'Needs improvement',2:'Could be better',3:'Good session',4:'Great experience',5:'Excellent experience'};
        document.querySelectorAll('#egirlReviewModal input[name="rating"]').forEach(function(input){
            input.addEventListener('change', function(){
                if (ratingCopy) ratingCopy.textContent = ratingLabels[parseInt(input.value || '5', 10)] || ratingLabels[5];
            });
        });
        var modalEl = document.getElementById('egirlCompletedModal');
        if (!modalEl || !window.bootstrap) return;
        var dismissKey = 'egirl_completed_feedback_<?= $id ?>';
        var justCompleted = false;
        try {
            justCompleted = sessionStorage.getItem('egirl_just_completed_' + OID) === '1';
            sessionStorage.removeItem('egirl_just_completed_' + OID);
            if (justCompleted) localStorage.removeItem(dismissKey);
        } catch(e) {}
        var dismissed = false;
        try { dismissed = localStorage.getItem(dismissKey) === '1'; } catch(e) {}
        var completedModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var dismissBtn = document.getElementById('egirlCompletedDismiss');
        if (dismissBtn) dismissBtn.addEventListener('click', function(){
            try { localStorage.setItem(dismissKey, '1'); } catch(e) {}
            completedModal.hide();
        });
        modalEl.querySelectorAll('.js-open-egirl-review,a[href*="trustpilot.com"],.eg-trustpilot-stars button').forEach(function(el){
            el.addEventListener('click', function(){ try { localStorage.setItem(dismissKey, '1'); } catch(e) {} });
        });
        document.querySelectorAll('.eg-trustpilot-stars button').forEach(function(btn){
            btn.addEventListener('click', function(){
                var rating = parseInt(btn.getAttribute('data-rating') || '0', 10);
                document.querySelectorAll('.eg-trustpilot-stars button').forEach(function(star){
                    star.classList.toggle('active', parseInt(star.getAttribute('data-rating') || '0', 10) <= rating);
                });
                window.open('https://www.trustpilot.com/evaluate/lolboost.gg', '_blank', 'noopener');
            });
        });
        if (!dismissed || justCompleted) {
            window.setTimeout(function(){
                if (!document.querySelector('.modal.show')) completedModal.show();
            }, 650);
        }
    });
    <?php endif; ?>

    /* Report problem */
    document.getElementById('btnSubmitReport')?.addEventListener('click', function(){
        var reason  = document.getElementById('reportReason').value;
        var details = document.getElementById('reportDetails').value;
        if (!reason) { alert('Please select a reason.'); return; }
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending…';
        var btn = this;
        $.post(AJAX, {action:'client_egirl_report_problem', order_id:OID, reason:reason, details:details}, function(r){
            if (typeof r==='string') try{r=JSON.parse(r);}catch(e){}
            bootstrap.Modal.getInstance(document.getElementById('reportModal'))?.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-duotone fa-paper-plane"></i>Send Report';
        });
    });
})();
</script>
<?= $this->end() ?>
