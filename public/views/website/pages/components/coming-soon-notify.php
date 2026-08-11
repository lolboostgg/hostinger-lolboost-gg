<?php
$csGame = (string)($game ?? ($gameSlug ?? ''));
$csGameName = (string)($gameName ?? ($gameConfig['name'] ?? ($meta['h1'] ?? ucwords(str_replace('-', ' ', $csGame)))));
$csService = (string)($service ?? 'service');
$csServiceLabel = ucwords(str_replace(['-', '_'], ' ', $csService));
$csTitle = (string)($title ?? 'Coming soon');
$csText = (string)($text ?? ('We are preparing ' . $csGameName . ' ' . $csService . '. Leave your email and we will notify you once listings are live.'));
$csAjax = defined('AJAX_URL') ? AJAX_URL : '/ajax';
$csId = 'lbComingSoonNotify_' . substr(md5($csGame . '|' . $csService . '|' . uniqid('', true)), 0, 10);

// Real game icon, passed in explicitly or resolved from gameConfig/DB.
$csGameIcon = (string)($gameIcon ?? ($gameConfig['icon'] ?? ''));
if ($csGameIcon === '' && function_exists('util_get_game_by_slug') && $csGame !== '') {
    $csGameIcon = (string)(util_get_game_by_slug($csGame)['icon'] ?? '');
}

// Fallback glyph if no artwork is available, tailored to the service type.
$csServiceKey = strtolower(str_replace([' ', '-'], '_', $csService));
$csFallbackIcon = 'fa-solid fa-wand-magic-sparkles';
if (strpos($csServiceKey, 'account') !== false)      $csFallbackIcon = 'fa-solid fa-user-shield';
elseif (strpos($csServiceKey, 'item') !== false)     $csFallbackIcon = 'fa-solid fa-gem';
elseif (strpos($csServiceKey, 'top') !== false)      $csFallbackIcon = 'fa-solid fa-coins';
elseif (strpos($csServiceKey, 'boost') !== false)    $csFallbackIcon = 'fa-solid fa-chess-knight';
?>
<div class="lb-cs2" id="<?= htmlspecialchars($csId, ENT_QUOTES) ?>" data-coming-soon-notify>
    <div class="lb-cs2__aurora" aria-hidden="true"></div>
    <div class="lb-cs2__grid" aria-hidden="true"></div>
    <div class="lb-cs2__particles" aria-hidden="true">
        <span></span><span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="lb-cs2__inner">
        <div class="lb-cs2__badge"><?= htmlspecialchars($csGameName, ENT_QUOTES) ?> · <?= htmlspecialchars($csServiceLabel, ENT_QUOTES) ?></div>

        <div class="lb-cs2__icon <?= $csGameIcon !== '' ? 'lb-cs2__icon--art' : '' ?>">
            <span class="lb-cs2__orbit"></span>
            <span class="lb-cs2__ring lb-cs2__ring--1"></span>
            <span class="lb-cs2__ring lb-cs2__ring--2"></span>
            <span class="lb-cs2__icon-core">
                <?php if ($csGameIcon !== ''): ?>
                    <img src="<?= htmlspecialchars($csGameIcon, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($csGameName, ENT_QUOTES) ?>" loading="lazy">
                <?php else: ?>
                    <i class="<?= htmlspecialchars($csFallbackIcon, ENT_QUOTES) ?>"></i>
                <?php endif; ?>
            </span>
            <span class="lb-cs2__sparkle lb-cs2__sparkle--1"><i class="fa-solid fa-sparkle"></i></span>
            <span class="lb-cs2__sparkle lb-cs2__sparkle--2"><i class="fa-solid fa-sparkle"></i></span>
        </div>

        <h2 class="lb-cs2__title"><?= htmlspecialchars($csTitle, ENT_QUOTES) ?></h2>
        <p class="lb-cs2__text"><?= htmlspecialchars($csText, ENT_QUOTES) ?></p>

        <form class="lb-cs2__form" method="post" action="<?= htmlspecialchars($csAjax, ENT_QUOTES) ?>" novalidate>
            <input type="hidden" name="action" value="coming_soon_notify">
            <input type="hidden" name="game_slug" value="<?= htmlspecialchars($csGame, ENT_QUOTES) ?>">
            <input type="hidden" name="game_name" value="<?= htmlspecialchars($csGameName, ENT_QUOTES) ?>">
            <input type="hidden" name="service_type" value="<?= htmlspecialchars($csService, ENT_QUOTES) ?>">
            <div class="lb-cs2__field">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="Your email address" required autocomplete="email">
                <button type="submit" class="lb-cs2__btn">
                    <span class="lb-cs2__btn-label"><i class="fa-solid fa-bell"></i> Notify me</span>
                    <span class="lb-cs2__btn-spinner" aria-hidden="true"></span>
                </button>
            </div>
        </form>
        <div class="lb-cs2__msg" aria-live="polite"></div>
    </div>
</div>

<style>
.lb-cs2{
    position:relative;
    overflow:visible;
    width:min(880px,100%);
    margin:72px auto 120px;
    padding:0;
    z-index:2;
}
.lb-cs2__inner{
    position:relative;
    z-index:2;
    padding:0 24px;
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;
    color:#fff;
}
.lb-cs2__aurora{
    position:absolute;
    inset:-60% -30% auto -30%;
    height:420px;
    background:
        radial-gradient(circle at 20% 30%,rgba(139,92,246,.28),transparent 60%),
        radial-gradient(circle at 80% 20%,rgba(56,189,248,.18),transparent 55%);
    filter:blur(30px);
    pointer-events:none;
    z-index:0;
    animation:lb-cs2-drift 12s ease-in-out infinite alternate;
}
@keyframes lb-cs2-drift{
    0%{transform:translate3d(0,0,0) scale(1);}
    100%{transform:translate3d(2%,3%,0) scale(1.06);}
}
.lb-cs2__grid{
    position:absolute;
    inset:0;
    background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);
    background-size:28px 28px;
    -webkit-mask-image:radial-gradient(60% 55% at 50% 0%,#000,transparent);
    mask-image:radial-gradient(60% 55% at 50% 0%,#000,transparent);
    pointer-events:none;
    z-index:0;
}
.lb-cs2__particles{position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:0;}
.lb-cs2__particles span{
    position:absolute;
    width:4px;height:4px;
    border-radius:50%;
    background:rgba(167,139,250,.7);
    box-shadow:0 0 10px rgba(167,139,250,.9);
    animation:lb-cs2-float 7s ease-in-out infinite;
}
.lb-cs2__particles span:nth-child(1){left:12%;top:70%;animation-delay:0s;}
.lb-cs2__particles span:nth-child(2){left:22%;top:25%;animation-delay:1.1s;background:rgba(56,189,248,.7);box-shadow:0 0 10px rgba(56,189,248,.9);}
.lb-cs2__particles span:nth-child(3){left:85%;top:65%;animation-delay:2.2s;}
.lb-cs2__particles span:nth-child(4){left:78%;top:20%;animation-delay:3.1s;background:rgba(56,189,248,.7);box-shadow:0 0 10px rgba(56,189,248,.9);}
.lb-cs2__particles span:nth-child(5){left:50%;top:12%;animation-delay:4s;}
.lb-cs2__particles span:nth-child(6){left:6%;top:38%;animation-delay:5.1s;background:rgba(56,189,248,.7);box-shadow:0 0 10px rgba(56,189,248,.9);}
@keyframes lb-cs2-float{
    0%,100%{transform:translate3d(0,0,0);opacity:.25;}
    50%{transform:translate3d(0,-16px,0);opacity:.95;}
}
.lb-cs2__badge{
    display:inline-flex;
    align-items:center;
    padding:9px 20px;
    border-radius:999px;
    background:rgba(167,139,250,.12);
    border:1px solid rgba(167,139,250,.32);
    color:#c7b9ff;
    font-size:13px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:34px;
    position:relative;
    z-index:1;
}
.lb-cs2__icon{
    position:relative;
    width:136px;
    height:136px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:32px;
    z-index:1;
}
.lb-cs2__orbit{
    position:absolute;
    inset:-18px;
    border-radius:50%;
    border:1px dashed rgba(167,139,250,.3);
    animation:lb-cs2-spin-slow 18s linear infinite;
}
.lb-cs2__orbit::before{
    content:"";
    position:absolute;
    top:-4px;
    left:50%;
    width:8px;height:8px;
    margin-left:-4px;
    border-radius:50%;
    background:#a78bfa;
    box-shadow:0 0 14px rgba(167,139,250,.9);
}
@keyframes lb-cs2-spin-slow{to{transform:rotate(360deg);}}
.lb-cs2__ring{
    position:absolute;
    inset:0;
    border-radius:50%;
    border:1px solid rgba(167,139,250,.35);
}
.lb-cs2__ring--1{animation:lb-cs2-pulse 2.6s ease-out infinite;}
.lb-cs2__ring--2{animation:lb-cs2-pulse 2.6s ease-out infinite 1.3s;}
@keyframes lb-cs2-pulse{
    0%{transform:scale(.75);opacity:.9;}
    100%{transform:scale(1.55);opacity:0;}
}
.lb-cs2__icon-core{
    position:relative;
    z-index:1;
    width:110px;
    height:110px;
    border-radius:30px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    box-shadow:0 22px 50px rgba(99,102,241,.42),inset 0 1px 0 rgba(255,255,255,.25);
    overflow:hidden;
}
.lb-cs2__icon-core i{font-size:42px;color:#fff;}
.lb-cs2__icon-core img{width:100%;height:100%;object-fit:cover;display:block;}
.lb-cs2__icon--art .lb-cs2__icon-core{background:linear-gradient(135deg,rgba(99,102,241,.35),rgba(139,92,246,.25));padding:4px;}
.lb-cs2__icon--art .lb-cs2__icon-core img{border-radius:26px;}
.lb-cs2__sparkle{
    position:absolute;
    color:#c7b9ff;
    font-size:17px;
    opacity:0;
    animation:lb-cs2-twinkle 3.2s ease-in-out infinite;
}
.lb-cs2__sparkle--1{top:-8px;right:2px;animation-delay:.4s;}
.lb-cs2__sparkle--2{bottom:2px;left:-10px;font-size:12px;animation-delay:1.8s;}
@keyframes lb-cs2-twinkle{
    0%,100%{opacity:0;transform:scale(.6) rotate(0deg);}
    50%{opacity:1;transform:scale(1.1) rotate(20deg);}
}
.lb-cs2__title{
    margin:0 0 16px;
    font-size:clamp(34px,4.4vw,48px);
    line-height:1.08;
    font-weight:1000;
    letter-spacing:-.03em;
    color:#fff;
    position:relative;
    z-index:1;
}
.lb-cs2__text{
    margin:0 0 38px;
    max-width:540px;
    color:rgba(235,229,255,.66);
    font-weight:600;
    line-height:1.65;
    font-size:17px;
    position:relative;
    z-index:1;
}
.lb-cs2__form{width:100%;max-width:520px;position:relative;z-index:1;}
.lb-cs2__field{
    position:relative;
    display:flex;
    align-items:center;
    height:66px;
    padding:0 8px 0 22px;
    border-radius:999px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.14);
    transition:border-color .18s ease,background .18s ease;
}
.lb-cs2__field:focus-within{
    border-color:rgba(167,139,250,.55);
    background:rgba(255,255,255,.07);
}
.lb-cs2__field > i{color:rgba(255,255,255,.4);font-size:16px;margin-right:12px;flex:0 0 auto;}
.lb-cs2__field input{
    flex:1;
    min-width:0;
    height:100%;
    border:0;
    outline:0;
    background:transparent;
    color:#fff;
    font-weight:700;
    font-size:16.5px;
}
.lb-cs2__field input::placeholder{color:rgba(255,255,255,.38);}
.lb-cs2__btn{
    position:relative;
    flex:0 0 auto;
    height:52px;
    border:0;
    border-radius:999px;
    padding:0 28px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    color:#fff;
    font-weight:900;
    font-size:15.5px;
    letter-spacing:.01em;
    box-shadow:0 14px 32px rgba(99,102,241,.32);
    cursor:pointer;
    white-space:nowrap;
    overflow:hidden;
}
.lb-cs2__btn::after{
    content:"";
    position:absolute;
    top:0;left:-60%;
    width:40%;height:100%;
    background:linear-gradient(120deg,transparent,rgba(255,255,255,.35),transparent);
    transform:skewX(-20deg);
    animation:lb-cs2-shimmer 3.4s ease-in-out infinite;
}
@keyframes lb-cs2-shimmer{
    0%{left:-60%;}
    45%,100%{left:130%;}
}
.lb-cs2__btn:hover{filter:brightness(1.08);}
.lb-cs2__btn:disabled{opacity:.75;cursor:not-allowed;}
.lb-cs2__btn-label{display:inline-flex;align-items:center;gap:8px;position:relative;z-index:1;}
.lb-cs2__btn-spinner{
    display:none;
    width:16px;height:16px;
    border-radius:50%;
    border:2px solid rgba(255,255,255,.4);
    border-top-color:#fff;
    animation:lb-cs2-spin .7s linear infinite;
}
.lb-cs2__btn.is-loading .lb-cs2__btn-label{display:none;}
.lb-cs2__btn.is-loading .lb-cs2__btn-spinner{display:inline-block;}
@keyframes lb-cs2-spin{to{transform:rotate(360deg);}}
.lb-cs2__msg{min-height:0;margin-top:14px;font-size:13px;font-weight:850;color:#c4b5fd;position:relative;z-index:1;display:none;align-items:center;justify-content:center;gap:8px;width:100%;max-width:520px;padding:11px 14px;border-radius:14px;border:1px solid rgba(167,139,250,.22);background:rgba(167,139,250,.08);box-shadow:0 12px 28px rgba(0,0,0,.18);}
.lb-cs2__msg.is-visible{display:flex;}
.lb-cs2__msg i{font-size:13px;flex:0 0 auto;}
.lb-cs2__msg.is-success{color:#86efac;background:rgba(34,197,94,.10);border-color:rgba(34,197,94,.28);}
.lb-cs2__msg.is-error{color:#fecaca;background:rgba(239,68,68,.10);border-color:rgba(248,113,113,.30);}
.lb-cs2__field.is-error{border-color:rgba(248,113,113,.62);background:rgba(239,68,68,.07);box-shadow:0 0 0 3px rgba(239,68,68,.10);}
.lb-cs2__field.is-success{border-color:rgba(34,197,94,.42);background:rgba(34,197,94,.06);}
.lb-cs2__perks{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:12px 28px;
    margin-top:32px;
    padding-top:28px;
    border-top:1px solid rgba(255,255,255,.08);
    width:100%;
    max-width:520px;
    position:relative;
    z-index:1;
}
.lb-cs2__perk{
    display:inline-flex;
    align-items:center;
    gap:9px;
    color:rgba(255,255,255,.5);
    font-size:13.5px;
    font-weight:700;
}
.lb-cs2__perk i{color:#a78bfa;font-size:13px;}

/* Empty pages use the clean version without grid or aurora backgrounds. */
.lb-cs2__grid,
.lb-cs2__aurora{
    display:none!important;
    background-image:none!important;
    -webkit-mask-image:none!important;
    mask-image:none!important;
}

@media(max-width:640px){
    .lb-cs2{width:100%;max-width:360px;margin:28px auto 58px;border-radius:0;}
    .lb-cs2__inner{padding:0 16px;border-radius:0;min-height:0;}
    .lb-cs2__badge{font-size:10px;line-height:1.2;padding:7px 12px;margin-bottom:18px;max-width:100%;white-space:normal;justify-content:center;}
    .lb-cs2__icon{width:86px;height:86px;margin-bottom:20px;}
    .lb-cs2__orbit{inset:-12px;}
    .lb-cs2__icon-core{width:70px;height:70px;border-radius:20px;}
    .lb-cs2__icon--art .lb-cs2__icon-core img{border-radius:17px;}
    .lb-cs2__icon-core i{font-size:28px;}
    .lb-cs2__title{font-size:34px;line-height:1.04;margin-bottom:12px;}
    .lb-cs2__text{font-size:14px;line-height:1.55;margin-bottom:24px;max-width:320px;}
    .lb-cs2__form{max-width:100%;}
    .lb-cs2__field{display:grid;grid-template-columns:18px minmax(0,1fr);align-items:center;height:auto;min-height:0;padding:13px;border-radius:24px;gap:0 10px;}
    .lb-cs2__field > i{grid-column:1;grid-row:1;margin:0;font-size:15px;align-self:center;}
    .lb-cs2__field input{grid-column:2;grid-row:1;width:100%;height:38px;min-height:38px;font-size:15.5px;line-height:38px;}
    .lb-cs2__btn{grid-column:1 / -1;width:100%;height:50px;margin-top:10px;padding:0 18px;font-size:15px;}
    .lb-cs2__msg{max-width:100%;margin-top:12px;padding:10px 12px;font-size:12.5px;line-height:1.35;text-align:left;justify-content:flex-start;}
    .lb-cs2__perks{flex-direction:column;align-items:flex-start;gap:10px;padding-left:8px;}
}
@media(max-width:380px){
    .lb-cs2{max-width:342px;}
    .lb-cs2__inner{padding-left:14px;padding-right:14px;}
    .lb-cs2__title{font-size:31px;}
    .lb-cs2__text{font-size:13.5px;}
}
</style>
<script>
(function(){
  var root=document.getElementById(<?= json_encode($csId) ?>); if(!root||root.dataset.bound==='1') return; root.dataset.bound='1';
  var form=root.querySelector('form'); var msg=root.querySelector('.lb-cs2__msg'); var btn=root.querySelector('.lb-cs2__btn'); var field=root.querySelector('.lb-cs2__field'); var email=form ? form.querySelector('input[name="email"]') : null;
  if(!form) return;

  function escText(text){
    return String(text || '').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});
  }
  function setMessage(type, text){
    if(!msg) return;
    var icon = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-circle-exclamation' : 'fa-spinner fa-spin');
    msg.className = 'lb-cs2__msg is-visible' + (type ? ' is-' + type : '');
    msg.innerHTML = '<i class="fa-solid ' + icon + '"></i><span>' + escText(text) + '</span>';
  }
  function clearMessage(){
    if(msg){msg.className='lb-cs2__msg';msg.textContent='';}
    if(field){field.classList.remove('is-error','is-success');}
  }
  function isValidEmail(value){
    value = String(value || '').trim();
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
  }

  if(email){email.addEventListener('input', clearMessage);}

  form.addEventListener('submit',function(e){
    e.preventDefault();
    var value = email ? String(email.value || '').trim() : '';

    if(!isValidEmail(value)){
      if(field){field.classList.add('is-error');field.classList.remove('is-success');}
      setMessage('error', value === '' ? 'Please enter your email address.' : 'Please enter a valid email address.');
      if(email) email.focus();
      return;
    }

    if(field){field.classList.remove('is-error','is-success');}
    setMessage('', 'Saving your email...');
    if(btn){btn.disabled=true;btn.classList.add('is-loading');}

    fetch(form.getAttribute('action')||'/ajax',{method:'POST',body:new FormData(form),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json().catch(function(){return {};});})
      .then(function(res){
        var ok=!!(res && (res.success || res.status==='success'));
        if(ok){
          if(field){field.classList.add('is-success');field.classList.remove('is-error');}
          setMessage('success', (res && (res.message || res.msg)) || "You're on the list. We'll notify you as soon as it's live.");
          form.reset();
        }else{
          if(field){field.classList.add('is-error');field.classList.remove('is-success');}
          setMessage('error', (res && (res.message || res.msg)) || 'Could not save your email. Please try again.');
        }
      })
      .catch(function(){
        if(field){field.classList.add('is-error');field.classList.remove('is-success');}
        setMessage('error', 'Could not save your email. Please try again.');
      })
      .finally(function(){if(btn){btn.disabled=false;btn.classList.remove('is-loading');}});
  });
})();
</script>
