<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'points-store']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Points Store (same visual system as Lootboxes/Loyalty)
   ============================================================ */
.points-store main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.ps2-wrap{width:min(1400px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1;}

/* Dynamic backdrop */
.ps2-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden;background:
  radial-gradient(1200px 700px at 80% 6%, rgba(109,140,255,.20), transparent 60%),
  radial-gradient(900px 620px at 15% 12%, rgba(217,70,239,.10), transparent 58%),
  radial-gradient(1000px 700px at 50% 95%, rgba(56,189,248,.10), transparent 60%),
  linear-gradient(180deg,#0a0818 0%, #0e0c22 55%, #0a0818 100%);
}
.ps2-gridlines{position:fixed;inset:-2px;z-index:-1;pointer-events:none;opacity:.13;background-image:
  linear-gradient(to right, rgba(255,255,255,.06) 1px, transparent 1px),
  linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(closest-side at 50% 10%, black 0%, transparent 74%);
}
#psStars{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;mix-blend-mode:screen;}
.ps2-star{position:absolute;left:var(--x,50vw);top:var(--y,50vh);width:var(--s,3px);height:var(--s,3px);border-radius:999px;background:rgba(255,255,255,.95);box-shadow:0 0 10px rgba(255,255,255,.65),0 0 22px rgba(109,140,255,.45);opacity:var(--o,.7);transform:translate3d(0,0,0) scale(.85);animation:ps2Star var(--d,22s) linear infinite;animation-delay:var(--delay,0s);will-change:transform,opacity;}
@keyframes ps2Star{
  0%{transform:translate3d(0,0,0) scale(.85);opacity:.1;}
  14%{opacity:var(--o,.7);}
  72%{opacity:var(--o,.7);}
  100%{transform:translate3d(var(--tx,-26vw),var(--ty,20vh),0) scale(1.12);opacity:.05;}
}
@media(max-width:820px){#psStars{display:none;}}
@media(prefers-reduced-motion:reduce){.ps2-star{animation:none!important;}}

/* Card recipe */
.ps2-card{background:#13112a;border:1px solid rgba(109,140,255,.20);border-radius:20px;box-shadow:0 18px 46px rgba(0,0,0,.32);}

/* Buttons */
.ps2-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 22px;border-radius:12px;font-weight:800;font-size:.95rem;text-decoration:none;border:1px solid transparent;cursor:pointer;font-family:inherit;transition:transform .15s ease,filter .15s ease,border-color .15s ease,background .15s ease;}
.ps2-btn-primary{background:linear-gradient(135deg,#6d8cff,#7c5cff);color:#fff;box-shadow:0 14px 30px rgba(109,140,255,.3);}
.ps2-btn-primary:hover{color:#fff;filter:brightness(1.08);transform:translateY(-1px);}
.ps2-btn-ghost{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.85);}
.ps2-btn-ghost:hover{color:#fff;border-color:rgba(109,140,255,.4);background:rgba(109,140,255,.1);}
.ps2-btn-full{width:100%;}
.ps2-btn:disabled,.ps2-btn.is-disabled{opacity:.5;pointer-events:none;}

/* Hero — centered, same DNA as loyalty/lootboxes/landing */
.ps2-hero{position:relative;padding:8px 24px clamp(34px,4.5vw,54px);text-align:center;overflow:hidden;}
.ps2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 20px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.88);border:1px solid rgba(109,140,255,.4);background:rgba(15,14,32,.6);box-shadow:0 16px 40px rgba(0,0,0,.3),inset 0 1px 0 rgba(255,255,255,.08);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.ps2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);animation:ps2Pulse 2.2s ease-in-out infinite;}
@keyframes ps2Pulse{0%,100%{box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);}50%{box-shadow:0 0 0 9px rgba(109,140,255,.22),0 0 26px rgba(109,140,255,.85);}}
@media(prefers-reduced-motion:reduce){.ps2-eyebrow .dot{animation:none!important;}}
.ps2-hero-title{margin:0 auto 18px;max-width:820px;font-size:clamp(34px,5vw,58px);line-height:1.08;letter-spacing:-.02em;font-weight:950;color:#fff;text-transform:uppercase;text-shadow:0 20px 50px rgba(0,0,0,.5);}
.ps2-hero-title .accent{background-image:linear-gradient(92deg,#9db7ff 0%,#7c9bff 50%,#6d8cff 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6d8cff;}
.ps2-hero-sub{max-width:640px;margin:0 auto 28px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}
.ps2-hero-actions{display:flex;justify-content:center;align-items:center;gap:12px;flex-wrap:wrap;}
.ps2-balance{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(109,140,255,.3);background:rgba(109,140,255,.09);color:#fff;border-radius:12px;padding:0 16px;min-height:48px;font-weight:800;font-size:.92rem;}
.ps2-balance img{width:22px;height:22px;object-fit:contain;}
@media(max-width:640px){.ps2-hero-actions .ps2-btn{width:100%;max-width:340px;}}

/* Section rhythm */
.ps2-section{padding:34px 0;}
.ps2-section + .ps2-section{border-top:1px solid rgba(255,255,255,.06);}
.ps2-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.ps2-section-head h2{margin:0;font-size:clamp(20px,2.2vw,26px);font-weight:900;color:#fff;letter-spacing:-.01em;}
.ps2-section-sub{margin:6px 0 0;color:rgba(255,255,255,.5);font-size:.9rem;font-weight:650;}

/* Prize grid */
.ps2-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;}
.ps2-prize{position:relative;padding:20px;display:flex;flex-direction:column;transition:transform .18s ease,border-color .18s ease;}
.ps2-prize:hover{transform:translateY(-3px);border-color:rgba(109,140,255,.45);}
.ps2-prize-visual{height:118px;border-radius:16px;background:radial-gradient(circle at 50% 20%,rgba(109,140,255,.24),rgba(0,0,0,.1) 60%);display:flex;align-items:center;justify-content:center;margin-bottom:14px;}
.ps2-prize-visual img{width:100px;height:100px;object-fit:contain;filter:drop-shadow(0 16px 24px rgba(109,140,255,.26));}
.ps2-prize-name{font-size:1.02rem;font-weight:900;color:#fff;margin:0 0 8px;}
.ps2-prize-price{display:inline-flex;align-items:center;gap:6px;align-self:flex-start;border:1px solid rgba(109,140,255,.38);background:rgba(4,8,18,.94);border-radius:999px;padding:6px 12px;font-weight:900;font-size:.76rem;color:#dbeafe;margin-bottom:12px;}
.ps2-prize-price img{width:15px;height:15px;object-fit:contain;}
.ps2-prize-desc{font-size:.84rem;color:rgba(255,255,255,.55);line-height:1.55;margin-bottom:16px;flex:1;}

/* Cross-promo strip: earn more points via Loyalty */
.ps2-promo{position:relative;overflow:hidden;padding:clamp(24px,3.4vw,40px);display:flex;align-items:center;gap:clamp(20px,3vw,36px);}
.ps2-promo-icon{position:relative;z-index:1;width:76px;height:76px;min-width:76px;border-radius:22px;display:flex;align-items:center;justify-content:center;background:rgba(109,140,255,.16);border:1px solid rgba(109,140,255,.32);box-shadow:0 20px 50px rgba(109,140,255,.18);}
.ps2-promo-icon i{font-size:32px;color:#9db7ff;}
.ps2-promo-body{position:relative;z-index:1;flex:1;min-width:0;}
.ps2-promo-body h2{margin:0 0 8px;font-size:clamp(20px,2.2vw,28px);font-weight:900;color:#fff;letter-spacing:-.01em;}
.ps2-promo-body p{margin:0 0 16px;color:rgba(255,255,255,.55);font-size:.9rem;line-height:1.6;max-width:560px;}
@media(max-width:760px){.ps2-promo{flex-direction:column;text-align:center;}.ps2-promo-body p{max-width:none;}}
</style>
<?= $this->end() ?>

<div class="ps2-bg" aria-hidden="true"></div>
<div class="ps2-gridlines" aria-hidden="true"></div>
<div id="psStars" aria-hidden="true"></div>

<div class="ps2-wrap">

  <section class="ps2-hero">
    <div class="ps2-eyebrow"><span class="dot"></span><span><?= t('LoLBoost Rewards') ?></span></div>
    <h1 class="ps2-hero-title"><?= t('Redeem your points.') ?> <span class="accent"><?= t('Get real rewards.') ?></span></h1>
    <p class="ps2-hero-sub"><?= t('Exchange the points you earned from the Loyalty Program or from Lootboxes for vouchers, credit and marketplace perks.') ?></p>
    <div class="ps2-hero-actions">
      <?php if (CLIENT_DATA): ?>
        <div class="ps2-balance"><img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="<?= t('Points') ?>"> <?= number_format((float)CLIENT_DATA['points'], 0) ?> <?= t('Points') ?></div>
      <?php else: ?>
        <button type="button" class="ps2-btn ps2-btn-primary" data-login-trigger="1"><i class="fa-solid fa-right-to-bracket"></i><?= t('Login to see your balance') ?></button>
      <?php endif; ?>
      <a class="ps2-btn ps2-btn-ghost" href="<?= BASE_URL ?>/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Open Lootboxes') ?></a>
    </div>
  </section>

  <section class="ps2-section">
    <div class="ps2-section-head">
      <h2><?= t('Available rewards') ?></h2>
      <p class="ps2-section-sub"><?= t('Redeem instantly, no waiting.') ?></p>
    </div>
    <div class="ps2-grid">
      <?php foreach ($prizes as $prize):
        $canAfford = CLIENT_DATA && (float)$prize['points'] <= (float)CLIENT_DATA['points'];
      ?>
        <div class="ps2-card ps2-prize">
          <div class="ps2-prize-visual"><img src="<?= $prize['image'] ?>" alt="<?= htmlspecialchars($prize['name']) ?>"></div>
          <h3 class="ps2-prize-name"><?= $prize['name'] ?></h3>
          <span class="ps2-prize-price"><img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="<?= t('Points') ?>"><?= $prize['points'] ?> <?= t('Points') ?></span>
          <p class="ps2-prize-desc"><?= $prize['description'] ?></p>
          <?php if (CLIENT_DATA): ?>
            <form class="ajax-form" action="<?= AJAX_URL ?>" autocomplete="off">
              <input type="hidden" name="action" value="redeem_prize">
              <input type="hidden" name="prize_id" value="<?= $prize['id'] ?>">
              <input type="hidden" name="prize_points" value="<?= $prize['points'] ?>">
              <input type="hidden" name="client_id" value="<?= CLIENT_DATA['id'] ?>">
              <input type="hidden" name="client_points" value="<?= CLIENT_DATA['points'] ?>">
              <button type="submit" class="ps2-btn ps2-btn-primary ps2-btn-full <?= $canAfford ? '' : 'is-disabled' ?>" data-id="<?= $prize['id'] ?>" data-points="<?= $prize['points'] ?>" <?= $canAfford ? '' : 'disabled' ?>>
                <i class="fa-duotone fa-gift"></i><?= $canAfford ? t('Redeem') : t('Not enough points') ?>
              </button>
            </form>
          <?php else: ?>
            <button type="button" class="ps2-btn ps2-btn-primary ps2-btn-full" data-login-trigger="1"><i class="fa-solid fa-right-to-bracket"></i><?= t('Login to Redeem') ?></button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="ps2-section">
    <div class="ps2-card ps2-promo">
      <div class="ps2-promo-icon" aria-hidden="true"><i class="fa-solid fa-crown"></i></div>
      <div class="ps2-promo-body">
        <h2><?= t('Need more points?') ?></h2>
        <p><?= t('Every order earns Reward Points automatically through the Loyalty Program — up to 8% cashback once you rank up. Open Lootboxes for a chance at even more.') ?></p>
        <div class="ps2-hero-actions" style="justify-content:flex-start;margin:0;">
          <a class="ps2-btn ps2-btn-primary" href="<?= BASE_URL ?>/loyalty"><i class="fa-solid fa-crown"></i><?= t('See the Loyalty Program') ?></a>
          <a class="ps2-btn ps2-btn-ghost" href="<?= BASE_URL ?>/lootboxes"><i class="fa-solid fa-box-open"></i><?= t('Open Lootboxes') ?></a>
        </div>
      </div>
    </div>
  </section>

</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var holder = document.getElementById('psStars');
  if (holder && !window.matchMedia('(max-width:820px)').matches) {
    var frag = document.createDocumentFragment();
    for (var i = 0; i < 28; i++) {
      var p = document.createElement('span');
      p.className = 'ps2-star';
      p.style.setProperty('--s', (Math.random() * 2.6 + 1.6).toFixed(2) + 'px');
      p.style.setProperty('--x', (Math.random() * 116 - 8).toFixed(2) + 'vw');
      p.style.setProperty('--y', (Math.random() * 70 - 4).toFixed(2) + 'vh');
      p.style.setProperty('--d', (Math.random() * 18 + 18).toFixed(2) + 's');
      p.style.setProperty('--delay', (-Math.random() * 34).toFixed(2) + 's');
      p.style.setProperty('--o', (Math.random() * .4 + .45).toFixed(2));
      p.style.setProperty('--tx', (Math.random() * 30 - 26).toFixed(2) + 'vw');
      p.style.setProperty('--ty', (Math.random() * 22 + 8).toFixed(2) + 'vh');
      frag.appendChild(p);
    }
    holder.appendChild(frag);
  }
})();
</script>
<script>
(function(){
  function openPointsStoreLogin(event){
    if (event) event.preventDefault();

    var headerButton = document.getElementById('login-btn') ||
      document.getElementById('login-btn-mobile-header') ||
      document.querySelector('[data-bs-target="#login_modal"], [data-target="#login_modal"], .login-btn, .js-login-btn');

    if (headerButton) {
      headerButton.click();
      return;
    }

    var loginModal = document.getElementById('login_modal') || document.getElementById('loginModal');
    if (loginModal) {
      loginModal.classList.add('show', 'active', 'is-open');
      loginModal.style.display = 'block';
      document.body.classList.add('modal-open', 'auth-modal-open', 'login-modal-open');
      return;
    }

    window.location.href = '<?= BASE_URL ?>/login?redirectUrl=' + encodeURIComponent(window.location.href);
  }

  document.addEventListener('click', function(event){
    var trigger = event.target.closest && event.target.closest('[data-login-trigger="1"]');
    if (trigger) openPointsStoreLogin(event);
  }, true);
})();
</script>
<?= $this->end() ?>
