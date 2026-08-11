<?php
$tk = $_GET['tk'] ?? null;
if ($tk):
?>
<style>
.am-rp-backdrop{
  position:fixed;inset:0;z-index:999999;
  display:flex;align-items:center;justify-content:center;padding:20px;
  background:rgba(2,4,12,.85);backdrop-filter:blur(10px);
}
.am-rp-box{
  width:min(420px,100%);background:#0a0b14;
  border:1px solid rgba(255,255,255,.09);border-radius:18px;
  box-shadow:0 32px 80px rgba(0,0,0,.72);
  padding:30px 28px 26px;position:relative;overflow:hidden;
}
.am-rp-box::before{
  content:"";position:absolute;left:10%;right:10%;top:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(88,101,242,.65),transparent);
}
.am-rp-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.am-rp-head h4{margin:0;font-size:17px;font-weight:700;color:#fff;letter-spacing:-.2px;}
.am-rp-close{
  width:32px;height:32px;border-radius:50%;
  border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06);
  color:rgba(255,255,255,.55);cursor:pointer;font-size:13px;
  display:flex;align-items:center;justify-content:center;
  transition:background .15s,color .15s,transform .2s;
}
.am-rp-close:hover{background:rgba(255,255,255,.12);color:#fff;transform:rotate(90deg);}
.am-rp-box label{display:block;margin-bottom:6px;font-size:11px;font-weight:700;
  letter-spacing:.6px;text-transform:uppercase;color:rgba(170,175,210,.65);}
.am-rp-box .pw-wrap{position:relative;display:flex;align-items:center;}
.am-rp-box input{
  width:100%;height:46px;border-radius:11px;
  border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.05);
  color:#fff;padding:0 44px 0 14px;font-size:14px;outline:none;
  transition:border-color .15s,box-shadow .15s;
}
.am-rp-box input:focus{border-color:rgba(88,101,242,.6);box-shadow:0 0 0 3px rgba(88,101,242,.13);}
.am-rp-box .pw-eye{
  position:absolute;right:8px;width:30px;height:30px;border:none;
  background:transparent;color:rgba(255,255,255,.35);cursor:pointer;
  display:flex;align-items:center;justify-content:center;font-size:14px;
  pointer-events:auto;z-index:10;transition:color .15s;
}
.am-rp-box .pw-eye:hover{color:#fff;}
.am-rp-box .am-btn{
  margin-top:18px;width:100%;height:46px;border-radius:11px;border:0;cursor:pointer;
  font-weight:700;font-size:14px;color:#fff;
  background:linear-gradient(135deg,#5865f2,#3b4ce8);
  box-shadow:0 8px 24px rgba(88,101,242,.3);transition:filter .15s,transform .15s;
}
.am-rp-box .am-btn:hover{filter:brightness(1.1);transform:translateY(-1px);}
.am-rp-box .form-error{margin-top:10px;border-radius:10px;padding:9px 12px;font-size:12.5px;}
@media(max-width:460px){.am-rp-box{padding:24px 18px 20px;}}
</style>

<div class="am-rp-backdrop" id="reset_overlay">
  <div class="am-rp-box">
    <div class="am-rp-head">
      <h4><?= t('Reset Password') ?></h4>
      <button class="am-rp-close" type="button" onclick="window.location.href='/'" aria-label="Close">✕</button>
    </div>
    <form class="ajax-form" action="<?= AJAX_URL ?>">
      <input type="hidden" name="action" value="client_reset_password">
      <input type="hidden" name="recovery_id" value="<?= htmlspecialchars($tk, ENT_QUOTES, 'UTF-8') ?>">
      <div class="form-group">
        <label><?= t('New Password') ?></label>
        <div class="pw-wrap">
          <input type="password" name="password" id="reset_password" placeholder="••••••••" minlength="6" required>
          <button type="button" class="pw-eye" data-target="#reset_password" aria-label="<?= t('Show password') ?>">
            <i class="far fa-eye"></i>
          </button>
        </div>
      </div>
      <button class="am-btn" type="submit">
        <span class="indicator-label"><?= t('Reset Password') ?></span>
        <span class="indicator-progress"><span class="loader"></span></span>
      </button>
      <div class="alert danger form-error" style="display:none"></div>
    </form>
  </div>
</div>
<?php endif; ?>


<?php
$authBrand = defined('SITE_NAME') ? SITE_NAME : 'lolboost.gg';
$authRequestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
$authRequestPath = (string)(parse_url($authRequestUri, PHP_URL_PATH) ?? '');
$authIsCheckout = (bool)preg_match('#^/checkout/[^/]+(?:/)?$#', $authRequestPath);
$authCheckoutReturnUrl = $authIsCheckout
  ? $authRequestPath . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')
  : '';
?>

<!-- Font Awesome wird hier bewusst nicht nochmal eingebunden, damit die Landing Page Icons nicht durch eine zweite FA Version überschrieben werden. -->
<style>
/* ══════════════════════════════════════════════════════
   AUTH MODAL v5
   Struktur rechte Seite:
   ┌─────────────────────────────────┐
   │ [•─────────────────────•]  TOP  │  ← lb-form-top (immer oben, kein flex-grow)
   │ Sign in                         │  ← lb-heading
   │ Welcome back…                   │
   │ [Login]  [Register]             │  ← nav-tabs
   ├─────────────────────────────────┤
   │ Continue with:                  │  ← lb-socials-row (Icon-Pills nebeneinander)
   │ [G Google]  [Discord]  [+ mehr] │    kompakt, eine Zeile
   │ ────── or email ──────          │
   │ [form fields…]                  │  ← flex-grow: 1, füllt restlichen Raum
   │ [Submit]                        │
   │ [Legal]                         │
   └─────────────────────────────────┘
   Login-Pane hat einen unsichtbaren Spacer (= 1 Feldgröße)
   damit beide Panes gleich hoch sind.
══════════════════════════════════════════════════════ */

#login_modal, #login_modal * { box-sizing: border-box; }

/* ── Tokens ── */
#login_modal {
  --c-left:   #07091a;
  --c-right:  #11131e;
  --c-modal:  #090915;
  --c-acc:    #5865f2;
  --c-acc-hi: #818cf8;
  --c-input:  #191b2d;
  --c-text:   #dde0f5;
  --c-muted:  rgba(148,156,205,.58);
}

/* ── Position & Größe ── */
#login_modal {
  position: fixed !important;
  inset: auto !important;
  top: 50% !important; left: 50% !important;
  transform: translate(-50%,-50%) !important;
  z-index: 2147483500 !important;
  width: min(1060px, calc(100vw - 24px)) !important;
  height: min(720px, calc(100dvh - 24px)) !important;
  margin: 0 !important; padding: 0 !important;
  background: var(--c-modal) !important;
  border: 1px solid rgba(88,101,242,.20) !important;
  border-radius: 24px !important;
  box-shadow:
    0 0 0 1px rgba(255,255,255,.02) inset,
    0 50px 130px rgba(0,0,0,.80),
    0 0 80px rgba(88,101,242,.05) !important;
  overflow: hidden !important;
  color: var(--c-text) !important;
}

/* scrollbar weg */
#login_modal, #login_modal * { scrollbar-width: none !important; }
#login_modal *::-webkit-scrollbar { display:none !important; width:0 !important; }

/* Leuchtlinie oben */
#login_modal::before {
  content:""; position:absolute;
  top:0; left:80px; right:80px; height:1px;
  background:linear-gradient(90deg,
    transparent,rgba(88,101,242,.15) 20%,
    rgba(88,101,242,.65) 50%,
    rgba(88,101,242,.15) 80%,transparent);
  z-index:5; pointer-events:none;
}

/* ── Close ── */
#login_modal .modal-header {
  position:absolute; right:18px; top:18px;
  z-index:30; border:0; background:transparent; padding:0; margin:0;
}
#login_modal .close-modal {
  width:36px; height:36px; border-radius:10px;
  border:1px solid rgba(255,255,255,.09);
  background:rgba(255,255,255,.05);
  color:rgba(255,255,255,.4); font-size:14px;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; transition:background .15s, color .15s, transform .2s;
}
#login_modal .close-modal:hover {
  background:rgba(255,255,255,.12); color:#fff; transform:rotate(90deg);
}

/* ── Shell ── */
#login_modal .modal-content {
  width:100% !important; height:100% !important;
  background:transparent !important; overflow:hidden !important; padding:0 !important;
}
#login_modal .lb-auth-shell {
  display:grid; grid-template-columns:350px 1fr;
  height:100%; overflow:hidden;
}

/* ══════════════════════════════
   LINKE SEITE
══════════════════════════════ */
#login_modal .lb-visual {
  position:relative; overflow:hidden;
  display:flex; flex-direction:column; justify-content:space-between;
  padding:42px 36px;
  background:var(--c-left);
  border-right:1px solid rgba(255,255,255,.05);
}
#login_modal .lb-visual::before {
  content:""; position:absolute; inset:0;
  background-image:
    linear-gradient(rgba(88,101,242,.055) 1px, transparent 1px),
    linear-gradient(90deg, rgba(88,101,242,.055) 1px, transparent 1px);
  background-size:44px 44px;
  mask-image:radial-gradient(ellipse 90% 80% at 40% 40%, black 20%, transparent 75%);
  pointer-events:none;
}
#login_modal .lb-visual::after {
  content:""; position:absolute;
  width:300px; height:300px; border-radius:50%;
  background:radial-gradient(circle, rgba(88,101,242,.11), transparent 68%);
  bottom:-100px; right:-80px; pointer-events:none;
}
#login_modal .lb-badge {
  position:relative; z-index:2;
  display:inline-flex; align-items:center; gap:7px; padding:6px 14px;
  border:1px solid rgba(88,101,242,.28); border-radius:999px;
  background:rgba(88,101,242,.09); color:var(--c-acc-hi);
  font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase;
  width:max-content;
}
#login_modal .lb-badge::before {
  content:""; width:5px; height:5px; border-radius:50%;
  background:var(--c-acc-hi); box-shadow:0 0 6px var(--c-acc-hi);
}
#login_modal .lb-rank-card {
  position:absolute; z-index:2; right:32px; top:96px;
  width:76px; height:76px; border-radius:22px;
  display:grid; place-items:center;
  background:linear-gradient(145deg,#5865f2,#3a46d8);
  box-shadow:0 18px 44px rgba(88,101,242,.32), inset 0 0 0 1px rgba(255,255,255,.15);
  transform:rotate(-7deg);
}
#login_modal .lb-rank-card i { color:#fff; font-size:30px; }
#login_modal .lb-copy { position:relative; z-index:2; }
#login_modal .lb-copy h2 {
  margin:0 0 14px; font-size:38px; font-weight:900;
  line-height:1.0; letter-spacing:-1.2px; color:#fff;
}
#login_modal .lb-copy h2 span { display:block; color:var(--c-acc-hi); }
#login_modal .lb-copy p { margin:0; color:var(--c-muted); font-size:15px; line-height:1.6; }
#login_modal .lb-stats {
  position:relative; z-index:2;
  display:grid; grid-template-columns:1fr 1fr; gap:10px;
}
#login_modal .lb-stat {
  padding:13px 15px; border:1px solid rgba(255,255,255,.06);
  border-radius:14px; background:rgba(255,255,255,.03);
}
#login_modal .lb-stat strong { display:block; color:#fff; font-size:21px; font-weight:800; line-height:1; }
#login_modal .lb-stat span { display:block; margin-top:5px; color:var(--c-muted); font-size:13px; font-weight:600; }
#login_modal .lb-stat:first-child { border-color:rgba(88,101,242,.22); }
#login_modal .lb-stat:first-child strong { color:var(--c-acc-hi); }

/* ══════════════════════════════════════════════════════
   RECHTE SEITE
   Aufbau: flex-column, KEIN justify-content:center
   → Inhalt startet immer oben, füllt nach unten
   → Tab-Content wächst mit flex-grow:1 und schiebt
     Submit + Legal nach unten
══════════════════════════════════════════════════════ */
#login_modal .lb-form-panel {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  padding: 30px 56px 24px;
  background: var(--c-right);
  overflow: hidden;   /* nur clip, kein pointer-event-problem durch isolation:isolate auf wrapper */
  height: 100%;
}

/* Logo (Desktop: unsichtbar) */
#login_modal .lb-logo { display:none; }
#login_modal .lb-logo-mark { display:none; }

/* ── Dekorativer Trenner — immer erste Element ── */
#login_modal .lb-form-top {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 12px;
  flex-shrink: 0;
}
#login_modal .lb-form-top-line {
  flex:1; height:1px;
  background:linear-gradient(90deg,transparent,rgba(88,101,242,.20),transparent);
}
#login_modal .lb-form-top-dot {
  width:7px; height:7px; border-radius:50%;
  background:var(--c-acc);
  box-shadow:0 0 10px rgba(88,101,242,.7), 0 0 20px rgba(88,101,242,.3);
}

/* ── Heading — direkt unter dem Trenner ── */
#login_modal .lb-heading {
  text-align:center; color:#fff;
  font-size:26px; font-weight:800; letter-spacing:-.5px;
  margin:0 0 10px; line-height:1.1;
  flex-shrink: 0;
}
#login_modal .lb-heading small {
  display:block; font-size:15px; font-weight:400;
  color:var(--c-muted); margin-top:4px; letter-spacing:0;
}

/* ── Tabs — direkt unter Heading ── */
#login_modal .nav-tabs {
  display:grid; grid-template-columns:1fr 1fr; gap:4px;
  padding:4px;
  border:1px solid rgba(255,255,255,.07) !important;
  border-radius:12px !important;
  background:rgba(0,0,0,.30) !important;
  margin-bottom: 0;
  flex-shrink: 0;
}
#login_modal .nav-tabs a {
  height:46px; border-radius:9px;
  display:flex; align-items:center; justify-content:center; gap:8px;
  color:var(--c-muted); font-size:15px; font-weight:700;
  text-decoration:none; transition:background .15s, color .15s;
}
#login_modal .nav-tabs a.active {
  background:var(--c-acc) !important; color:#fff !important;
  box-shadow:0 4px 16px rgba(88,101,242,.30);
}
#login_modal .nav-tabs a:not(.active):hover { background:rgba(255,255,255,.07); color:#fff; }

/* ── Separator zwischen Tabs und Socials ── */
#login_modal .lb-tab-sep {
  display: flex;
  align-items: center;
  gap: 14px;
  margin: 12px 0;
  flex-shrink: 0;
}
#login_modal .lb-tab-sep-line {
  flex: 1; height: 1px;
  background: rgba(255,255,255,.07);
}
#login_modal .lb-tab-sep-text {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .8px;
  text-transform: uppercase;
  color: rgba(255,255,255,.22);
  white-space: nowrap;
}

/* ══════════════════════════════════════════════════════
   SOCIALS — kompakte Pill-Row statt zwei breite Buttons
   Jeder Button: Icon + Text, schmaler, alle in einer Zeile
══════════════════════════════════════════════════════ */
#login_modal .lb-socials {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 10px;
  flex-shrink: 0;
}

/* ── Socials: kompakte Badge-Pills, nur so breit wie Inhalt ── */
#login_modal .lb-social-btn {
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  padding: 0 14px;
  width: 100%;
  transition: transform .15s, box-shadow .2s, border-color .2s, background .15s;
}
#login_modal .lb-social-btn:hover { transform: translateY(-2px); }
#login_modal .lb-social-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
#login_modal .lb-social-btn i   { font-size: 16px; flex-shrink: 0; }

/* ── Google: dunkel mit echtem SVG-Multicolor-Icon ── */
#login_modal .lb-social-btn.google-login {
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.85);
  position: relative;
  overflow: hidden;
}
#login_modal .lb-social-btn.google-login:hover {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.22);
  box-shadow: 0 6px 18px rgba(0,0,0,.28);
  color: #fff;
}

/* ── Discord: tiefdunkles Indigo-Panel mit Neon-Rand ── */
#login_modal .lb-social-btn.discord-login {
  background: linear-gradient(145deg, #1e2047 0%, #2d3080 100%);
  border: 1px solid rgba(88,101,242,.45);
  color: #c5caff;
  position: relative;
  overflow: hidden;
  box-shadow:
    0 0 0 0 rgba(88,101,242,0),
    inset 0 1px 0 rgba(255,255,255,.08),
    0 6px 20px rgba(88,101,242,.20);
  transition: filter .15s, transform .12s, box-shadow .2s, border-color .2s;
}
#login_modal .lb-social-btn.discord-login::before {
  content: "";
  position: absolute; inset: 0;
  background: linear-gradient(145deg, rgba(88,101,242,.18) 0%, transparent 55%);
  pointer-events: none;
}
#login_modal .lb-social-btn.discord-login i {
  color: #7289da;
  -webkit-text-fill-color: #7289da;
  filter: drop-shadow(0 0 6px rgba(114,137,218,.6));
  font-size: 19px;
}
#login_modal .lb-social-btn.discord-login:hover {
  border-color: rgba(88,101,242,.80);
  color: #fff;
  transform: translateY(-2px);
  box-shadow:
    0 0 0 3px rgba(88,101,242,.18),
    inset 0 1px 0 rgba(255,255,255,.12),
    0 10px 28px rgba(88,101,242,.35);
}
#login_modal .lb-social-btn.discord-login:hover i {
  filter: drop-shadow(0 0 8px rgba(114,137,218,.9));
}

/* Divider */
#login_modal .lb-divider {
  display:flex; align-items:center; gap:12px;
  margin:0 0 10px;
  color:rgba(255,255,255,.22); font-size:12px; letter-spacing:.6px; text-transform:uppercase;
  flex-shrink: 0;
}
#login_modal .lb-divider::before,
#login_modal .lb-divider::after { content:""; flex:1; height:1px; background:rgba(255,255,255,.07); }

/* ══════════════════════════════════════════════════════
   TAB-CONTENT
   flex-grow:1 → füllt den gesamten verbleibenden Raum
   Beide Panes (login + register) sind display:flex,
   flex-direction:column, height:100%
   → Login hat einen flex-grow Spacer oben der das
     fehlende Feld (Username) kompensiert
══════════════════════════════════════════════════════ */
#login_modal .tab-content {
  flex: 1;                     /* nimmt restlichen Raum */
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}
#login_modal .tab-pane {
  display: none;
  flex-direction: column;
  height: 100%;
}
#login_modal .tab-pane.active {
  display: flex;
}
#login_modal .tab-pane form {
  display: flex;
  flex-direction: column;
  height: 100%;
}

/* Login-Spacer: unsichtbar, kompensiert fehlenden Username-Block */
#login_modal .login-field-spacer {
  flex-shrink: 0;
  /* label(12) + gap(6) + input(50) + group-mb(10) = 78px */
  height: 78px;
}

/* Formfelder */
#login_modal .form-group { margin-bottom:10px; flex-shrink: 0; }
#login_modal .form-group label {
  display:block; margin-bottom:6px;
  font-size:12px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
  color:rgba(148,156,205,.60);
}

/* Inputs */
#login_modal input[type="email"],
#login_modal input[type="text"],
#login_modal input[type="password"] {
  width:100%; height:50px;
  border-radius:11px;
  border:1px solid rgba(255,255,255,.09);
  background:var(--c-input);
  color:#fff; font-size:16px;
  padding:0 44px 0 14px; outline:none;
  transition:border-color .15s, box-shadow .15s;
}
#login_modal input:focus {
  border-color:rgba(88,101,242,.55) !important;
  box-shadow:0 0 0 3px rgba(88,101,242,.11) !important;
}
#login_modal input::placeholder { color:rgba(255,255,255,.2); }

/* Password Toggle — isolation:isolate damit overflow nicht pointer-events blockiert */
#login_modal .password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  isolation: isolate;   /* eigener stacking context */
}
#login_modal .password-wrapper input { padding-right: 44px; }
#login_modal .password-toggle {
  position: absolute;
  right: 8px;
  width: 34px; height: 34px;
  border-radius: 9px;
  border: none;
  background: rgba(255,255,255,.06);
  color: rgba(255,255,255,.40);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px;
  pointer-events: all !important;
  z-index: 999;
  position: absolute;   /* nochmal explicit */
  transition: background .15s, color .15s;
  /* Kein overflow:hidden Elternteil soll dies blockieren */
  -webkit-tap-highlight-color: transparent;
}
#login_modal .password-toggle:hover { background: rgba(255,255,255,.12); color: #fff; }
#login_modal .password-toggle:active { background: rgba(255,255,255,.16); }

/* Remember / Forgot */
#login_modal .remember-me {
  display:flex; align-items:center; gap:8px;
  margin-bottom:8px; color:var(--c-muted); font-size:14px;
  flex-shrink: 0;
}
#login_modal .remember-me input { accent-color:var(--c-acc); cursor:pointer; width:15px; height:15px; }
#login_modal .remember-me label { cursor:pointer; }
#login_modal .remember-me a,
#login_modal .lb-legal a { color:var(--c-acc-hi); text-decoration:none; transition:color .15s; }
#login_modal .remember-me a:hover,
#login_modal .lb-legal a:hover { color:#fff; text-decoration:underline; }
#login_modal .forgot-password-link { margin-left:auto; white-space:nowrap; }

/* Fester Abstand vor Submit (kein flex-grow mehr) */
#login_modal .form-push { flex: 0 0 auto; height: 12px; min-height: 0; }

/* Submit */
#login_modal .submit-btn {
  width:100%; height:46px; border:0; border-radius:12px;
  background:linear-gradient(160deg, #6672f5 0%, #4452e8 100%);
  color:#fff; font-size:15.5px; font-weight:700; cursor:pointer;
  box-shadow:0 10px 30px rgba(88,101,242,.35);
  position:relative; overflow:hidden; flex-shrink: 0;
  transition:filter .15s, transform .15s, box-shadow .15s;
}
#login_modal .submit-btn::before {
  content:""; position:absolute; top:0; left:0; right:0; height:50%;
  background:linear-gradient(180deg,rgba(255,255,255,.12),transparent);
  pointer-events:none;
}
#login_modal .submit-btn:hover {
  filter:brightness(1.10); transform:translateY(-1px);
  box-shadow:0 14px 36px rgba(88,101,242,.42);
}

/* Error */
#login_modal .form-error {
  border-radius:10px; padding:9px 13px; font-size:12.5px;
  background:rgba(239,68,68,.09); border:1px solid rgba(239,68,68,.22);
  color:#fca5a5; margin-bottom:11px; flex-shrink: 0;
}

/* Legal */
#login_modal .lb-legal {
  margin-top:8px; text-align:center; flex-shrink: 0;
  color:rgba(255,255,255,.22); font-size:12.5px; line-height:1.6;
}

/* ══ RESPONSIVE ══ */
@media (max-width:680px) {
  #login_modal {
    top:0 !important; left:0 !important; transform:none !important;
    width:100vw !important; height:100dvh !important;
    border-radius:0 !important; border:0 !important;
  }
  #login_modal .lb-auth-shell { grid-template-columns:1fr; }
  #login_modal .lb-visual { display:none; }
  #login_modal .lb-form-panel { padding:60px 20px 24px; }
  #login_modal .lb-logo { display:flex; }
  #login_modal .lb-form-top { display:none; }
  #login_modal .lb-socials { flex-direction:column; }
  #login_modal .lb-social-btn { flex:none; width:100%; }
  #login_modal .remember-me { flex-wrap:wrap; }
  #login_modal .forgot-password-link { margin-left:0; }
}

@media (min-width:681px) and (max-width:1000px) {
  #login_modal {
    width:min(840px, calc(100vw - 24px)) !important;
    height:min(720px, calc(100dvh - 24px)) !important;
  }
  #login_modal .lb-auth-shell { grid-template-columns:280px 1fr; }
  #login_modal .lb-form-panel { padding:28px 36px 24px; }
  #login_modal .lb-copy h2 { font-size:28px; }
  #login_modal .lb-stats { display:none; }
  #login_modal .lb-rank-card { width:64px; height:64px; border-radius:17px; top:82px; right:24px; }
  #login_modal .lb-rank-card i { font-size:24px; }
}

@media (min-width:681px) and (max-height:650px) {
  #login_modal .lb-stats, #login_modal .lb-copy p { display:none; }
  #login_modal .lb-form-panel { padding:20px 52px 16px; }
  #login_modal .lb-visual { padding:24px 28px; }
  #login_modal .lb-heading { margin-bottom:10px; }
  #login_modal .nav-tabs { margin-bottom:12px; }
  #login_modal .lb-socials { margin-bottom:10px; }
  #login_modal .lb-divider { margin-bottom:8px; }
  #login_modal .form-group { margin-bottom:8px; }
  #login_modal .remember-me { margin-bottom:8px; }
  #login_modal .login-field-spacer { height:58px; }
}
</style>




<style id="lb-auth-chat-seller-style-override">
/* Compact marketplace auth modal, aligned with the Digital Goods seller-chat login style. */
#login_modal,
#login_modal *{
  box-sizing:border-box!important;
}
#login_modal{
  --lb-auth-bg:#0b1023;
  --lb-auth-card:#0e1328;
  --lb-auth-card-2:#151a31;
  --lb-auth-line:rgba(124,92,255,.45);
  --lb-auth-line-soft:rgba(124,92,255,.22);
  --lb-auth-text:#ffffff;
  --lb-auth-muted:rgba(229,232,255,.62);
  --lb-auth-muted-2:rgba(229,232,255,.38);
  --lb-auth-primary:#7c4dff;
  --lb-auth-primary-2:#8b5cf6;
  --lb-auth-primary-3:#5b6cff;
  position:fixed!important;
  inset:auto!important;
  top:50%!important;
  left:50%!important;
  transform:translate(-50%,-50%)!important;
  z-index:2147483600!important;
  width:min(520px,calc(100vw - 32px))!important;
  height:auto!important;
  max-height:calc(100dvh - 32px)!important;
  padding:0!important;
  margin:0!important;
  border-radius:22px!important;
  color:var(--lb-auth-text)!important;
  background:linear-gradient(180deg,rgba(20,24,48,.98),rgba(9,13,30,.98))!important;
  border:1px solid var(--lb-auth-line)!important;
  box-shadow:0 42px 120px rgba(0,0,0,.82),0 0 0 1px rgba(255,255,255,.04) inset,0 0 90px rgba(124,92,255,.16)!important;
  overflow:hidden!important;
  isolation:isolate!important;
}
#login_modal::before{
  content:""!important;
  position:absolute!important;
  inset:0 0 auto 0!important;
  height:132px!important;
  z-index:0!important;
  background:
    radial-gradient(160px 120px at 88% 0%,rgba(255,255,255,.11),transparent 62%),
    radial-gradient(220px 160px at 18% -18%,rgba(124,92,255,.42),transparent 70%),
    linear-gradient(135deg,rgba(110,92,255,.42),rgba(37,31,86,.72))!important;
  pointer-events:none!important;
}
#login_modal::after{
  content:""!important;
  position:absolute!important;
  right:-70px!important;
  top:-80px!important;
  width:210px!important;
  height:210px!important;
  border-radius:999px!important;
  border:1px solid rgba(255,255,255,.08)!important;
  z-index:1!important;
  pointer-events:none!important;
}
#login_modal .modal-header{
  position:absolute!important;
  right:16px!important;
  top:16px!important;
  z-index:30!important;
  padding:0!important;
  margin:0!important;
  border:0!important;
  background:transparent!important;
}
#login_modal .close-modal{
  width:38px!important;
  height:38px!important;
  border-radius:12px!important;
  border:1px solid rgba(255,255,255,.10)!important;
  background:rgba(255,255,255,.10)!important;
  color:rgba(255,255,255,.72)!important;
  display:grid!important;
  place-items:center!important;
  font-size:15px!important;
  cursor:pointer!important;
  transition:background .18s ease,color .18s ease,transform .18s ease!important;
}
#login_modal .close-modal:hover{
  background:rgba(255,255,255,.18)!important;
  color:#fff!important;
  transform:rotate(90deg)!important;
}
#login_modal .modal-content{
  position:relative!important;
  z-index:2!important;
  width:100%!important;
  height:auto!important;
  max-height:calc(100dvh - 32px)!important;
  overflow-y:auto!important;
  background:transparent!important;
  padding:0!important;
  scrollbar-width:thin!important;
  scrollbar-color:rgba(124,92,255,.45) transparent!important;
}
#login_modal .modal-content::-webkit-scrollbar{width:6px!important;display:block!important;}
#login_modal .modal-content::-webkit-scrollbar-track{background:transparent!important;}
#login_modal .modal-content::-webkit-scrollbar-thumb{background:rgba(124,92,255,.45)!important;border-radius:999px!important;}
#login_modal .lb-auth-shell{
  display:block!important;
  height:auto!important;
  overflow:visible!important;
}
#login_modal .lb-visual,
#login_modal .lb-form-top,
#login_modal .lb-tab-sep,
#login_modal .login-field-spacer{
  display:none!important;
}
#login_modal .lb-form-panel{
  position:relative!important;
  z-index:2!important;
  display:flex!important;
  flex-direction:column!important;
  justify-content:flex-start!important;
  height:auto!important;
  overflow:visible!important;
  padding:28px 28px 26px!important;
  background:transparent!important;
}
#login_modal .lb-form-panel::before{
  content:""!important;
  width:54px!important;
  height:54px!important;
  border-radius:16px!important;
  margin:0 0 14px!important;
  display:block!important;
  background:
    linear-gradient(135deg,rgba(255,255,255,.20),rgba(255,255,255,0)),
    linear-gradient(135deg,var(--lb-auth-primary),var(--lb-auth-primary-2))!important;
  box-shadow:0 16px 36px rgba(124,92,255,.42),inset 0 1px 0 rgba(255,255,255,.20)!important;
}
#login_modal .lb-form-panel::after{
  content:"4ad"!important;
  font-family:Arial,sans-serif!important;
  font-weight:900!important;
  position:absolute!important;
  left:45px!important;
  top:43px!important;
  z-index:5!important;
  color:#fff!important;
  font-size:23px!important;
  line-height:1!important;
  pointer-events:none!important;
}
#login_modal .lb-logo{display:none!important;}
#login_modal .lb-heading{
  text-align:left!important;
  margin:-66px 52px 22px 70px!important;
  min-height:58px!important;
  display:flex!important;
  flex-direction:column!important;
  justify-content:center!important;
  color:#fff!important;
  font-size:25px!important;
  line-height:1.12!important;
  font-weight:950!important;
  letter-spacing:-.04em!important;
}
#login_modal .lb-heading small{
  display:block!important;
  margin-top:7px!important;
  color:rgba(255,255,255,.68)!important;
  font-size:14px!important;
  line-height:1.42!important;
  font-weight:750!important;
  letter-spacing:0!important;
}
#login_modal .nav-tabs{
  display:grid!important;
  grid-template-columns:1fr 1fr!important;
  gap:6px!important;
  width:100%!important;
  padding:6px!important;
  margin:0 0 20px!important;
  border-radius:18px!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid var(--lb-auth-line)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05)!important;
}
#login_modal .nav-tabs a{
  height:48px!important;
  border-radius:14px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:8px!important;
  color:rgba(255,255,255,.54)!important;
  font-size:15px!important;
  font-weight:900!important;
  text-decoration:none!important;
  transition:background .16s ease,color .16s ease,box-shadow .16s ease,transform .16s ease!important;
}
#login_modal .nav-tabs a.active{
  color:#fff!important;
  background:linear-gradient(135deg,#7457ff,#8d4cf6)!important;
  box-shadow:0 10px 24px rgba(124,92,255,.38),inset 0 1px 0 rgba(255,255,255,.18)!important;
}
#login_modal .nav-tabs a:not(.active):hover{
  color:#fff!important;
  background:rgba(255,255,255,.07)!important;
}
#login_modal .lb-socials{
  order:30!important;
  display:grid!important;
  grid-template-columns:1fr 1fr!important;
  gap:10px!important;
  margin:18px 0 0!important;
}
#login_modal .lb-divider{
  order:29!important;
  display:flex!important;
  align-items:center!important;
  gap:12px!important;
  margin:18px 0 0!important;
  color:rgba(255,255,255,.28)!important;
  font-size:12px!important;
  font-weight:950!important;
  letter-spacing:.08em!important;
  text-transform:uppercase!important;
}
#login_modal .lb-divider::before,
#login_modal .lb-divider::after{
  content:""!important;
  flex:1!important;
  height:1px!important;
  background:rgba(255,255,255,.09)!important;
}
#login_modal .lb-social-btn{
  height:48px!important;
  border-radius:14px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:9px!important;
  padding:0 14px!important;
  width:100%!important;
  color:#fff!important;
  font-size:14px!important;
  font-weight:950!important;
  text-decoration:none!important;
  transition:transform .16s ease,filter .16s ease,box-shadow .16s ease!important;
}
#login_modal .lb-social-btn:hover{transform:translateY(-2px)!important;}
#login_modal .lb-social-btn.google-login{
  background:#ef4338!important;
  border:1px solid rgba(255,255,255,.08)!important;
  box-shadow:0 12px 26px rgba(239,67,56,.22)!important;
}
#login_modal .lb-social-btn.discord-login{
  background:#5865f2!important;
  border:1px solid rgba(255,255,255,.08)!important;
  box-shadow:0 12px 26px rgba(88,101,242,.25)!important;
}
#login_modal .lb-social-btn.discord-login i{color:#fff!important;-webkit-text-fill-color:#fff!important;filter:none!important;}
#login_modal .tab-content{
  order:20!important;
  flex:0 0 auto!important;
  display:block!important;
  min-height:0!important;
  overflow:visible!important;
}
#login_modal .tab-pane{display:none!important;height:auto!important;}
#login_modal .tab-pane.active{display:block!important;}
#login_modal .tab-pane form{
  display:block!important;
  height:auto!important;
}
#login_modal .form-group{
  margin:0 0 16px!important;
}
#login_modal .form-group label{
  display:flex!important;
  align-items:center!important;
  gap:7px!important;
  margin:0 0 8px!important;
  color:#aeb5ff!important;
  font-size:13px!important;
  font-weight:950!important;
  letter-spacing:0!important;
  text-transform:none!important;
}
#login_modal .form-group label::before{
  font-family:Arial,sans-serif!important;
  font-weight:900!important;
  color:#8f86ff!important;
  font-size:13px!important;
}
#login_modal .form-group:has(input[type="email"]) label::before{content:"0e0"!important;}
#login_modal .form-group:has(input[name="username"]) label::before{content:"007"!important;}
#login_modal .form-group:has(input[type="password"]) label::before{content:"084"!important;}
#login_modal input[type="email"],
#login_modal input[type="text"],
#login_modal input[type="password"]{
  width:100%!important;
  height:54px!important;
  border-radius:15px!important;
  border:1px solid var(--lb-auth-line)!important;
  background:rgba(255,255,255,.055)!important;
  color:#fff!important;
  font-size:16px!important;
  font-weight:750!important;
  padding:0 48px 0 16px!important;
  outline:none!important;
  transition:border-color .16s ease,box-shadow .16s ease,background .16s ease!important;
}
#login_modal input[type="email"]:focus,
#login_modal input[type="text"]:focus,
#login_modal input[type="password"]:focus{
  border-color:rgba(142,101,255,.9)!important;
  background:rgba(255,255,255,.075)!important;
  box-shadow:0 0 0 4px rgba(124,92,255,.16)!important;
}
#login_modal input::placeholder{
  color:rgba(255,255,255,.32)!important;
  font-weight:800!important;
}
#login_modal .password-wrapper{
  position:relative!important;
  display:flex!important;
  align-items:center!important;
  isolation:isolate!important;
}
#login_modal .password-toggle{
  position:absolute!important;
  right:10px!important;
  top:50%!important;
  transform:translateY(-50%)!important;
  z-index:20!important;
  width:36px!important;
  height:36px!important;
  border-radius:11px!important;
  border:0!important;
  background:rgba(255,255,255,.08)!important;
  color:rgba(255,255,255,.58)!important;
  display:grid!important;
  place-items:center!important;
  cursor:pointer!important;
  font-size:16px!important;
  pointer-events:auto!important;
  transition:background .16s ease,color .16s ease!important;
}
#login_modal .password-toggle:hover{
  background:rgba(124,92,255,.22)!important;
  color:#fff!important;
}
#login_modal .remember-me{
  display:grid!important;
  grid-template-columns:auto 1fr auto!important;
  align-items:center!important;
  gap:9px!important;
  margin:2px 0 18px!important;
  color:rgba(255,255,255,.62)!important;
  font-size:13px!important;
  font-weight:800!important;
}
#login_modal .remember-me input[type="checkbox"]{
  appearance:none!important;
  -webkit-appearance:none!important;
  width:20px!important;
  height:20px!important;
  border-radius:7px!important;
  margin:0!important;
  cursor:pointer!important;
  border:1px solid rgba(142,101,255,.55)!important;
  background:rgba(255,255,255,.06)!important;
  display:grid!important;
  place-items:center!important;
  transition:background .16s ease,border-color .16s ease,box-shadow .16s ease!important;
}
#login_modal .remember-me input[type="checkbox"]::before{
  content:"00c"!important;
  font-family:Arial,sans-serif!important;
  font-weight:900!important;
  font-size:11px!important;
  color:#fff!important;
  transform:scale(0)!important;
  transition:transform .14s ease!important;
}
#login_modal .remember-me input[type="checkbox"]:checked{
  background:linear-gradient(135deg,#7457ff,#8d4cf6)!important;
  border-color:transparent!important;
  box-shadow:0 8px 18px rgba(124,92,255,.28)!important;
}
#login_modal .remember-me input[type="checkbox"]:checked::before{transform:scale(1)!important;}
#login_modal .remember-me label{
  cursor:pointer!important;
  line-height:1.35!important;
}
#login_modal .remember-me a,
#login_modal .lb-legal a{
  color:#c4b5fd!important;
  text-decoration:none!important;
  font-weight:950!important;
}
#login_modal .remember-me a:hover,
#login_modal .lb-legal a:hover{
  color:#fff!important;
  text-decoration:none!important;
}
#login_modal .forgot-password-link{
  margin-left:0!important;
  white-space:nowrap!important;
}
#login_modal .form-push{display:none!important;}
#login_modal .submit-btn{
  width:100%!important;
  height:54px!important;
  border:0!important;
  border-radius:15px!important;
  background:linear-gradient(135deg,#7357ff,#8b4df2)!important;
  color:#fff!important;
  font-size:16px!important;
  font-weight:950!important;
  box-shadow:0 16px 34px rgba(124,92,255,.34)!important;
  cursor:pointer!important;
  transition:transform .16s ease,filter .16s ease,box-shadow .16s ease!important;
}
#login_modal .submit-btn:hover{
  transform:translateY(-2px)!important;
  filter:brightness(1.07)!important;
  box-shadow:0 20px 42px rgba(124,92,255,.44)!important;
}
#login_modal .form-error{
  margin:0 0 14px!important;
  border-radius:14px!important;
  padding:11px 13px!important;
  font-size:13px!important;
  font-weight:800!important;
  color:#fecaca!important;
  background:rgba(239,68,68,.11)!important;
  border:1px solid rgba(239,68,68,.28)!important;
}
#login_modal .lb-legal{
  order:40!important;
  margin:18px 0 0!important;
  padding-top:14px!important;
  border-top:1px solid rgba(255,255,255,.08)!important;
  color:rgba(255,255,255,.38)!important;
  text-align:center!important;
  font-size:12px!important;
  line-height:1.55!important;
  font-weight:750!important;
}
#forgot_password_modal,
#guest_checkout_modal,
.am-small-modal{
  z-index:2147483650!important;
}
@media (max-width:560px){
  #login_modal{
    top:auto!important;
    bottom:0!important;
    left:0!important;
    transform:none!important;
    width:100vw!important;
    max-height:calc(100dvh - 10px)!important;
    border-radius:24px 24px 0 0!important;
    border-left:0!important;
    border-right:0!important;
    border-bottom:0!important;
  }
  #login_modal .modal-content{
    max-height:calc(100dvh - 10px)!important;
  }
  #login_modal .lb-form-panel{
    padding:24px 18px 22px!important;
  }
  #login_modal .lb-heading{
    margin:-62px 44px 20px 66px!important;
    font-size:23px!important;
  }
  #login_modal .lb-heading small{
    font-size:13px!important;
  }
  #login_modal .lb-socials{
    grid-template-columns:1fr!important;
  }
  #login_modal .remember-me{
    grid-template-columns:auto 1fr!important;
  }
  #login_modal .forgot-password-link{
    grid-column:2!important;
    justify-self:start!important;
    margin-top:2px!important;
  }
}
</style>



<style id="lb-auth-blue-final-override">
/* Final LoLBoost blue auth styling */
#login_modal{
  --lb-auth-line:rgba(99,102,241,.48)!important;
  --lb-auth-line-soft:rgba(99,102,241,.24)!important;
  --lb-auth-primary:#6366f1!important;
  --lb-auth-primary-2:#4f46e5!important;
  --lb-auth-primary-3:#818cf8!important;
  background:linear-gradient(180deg,rgba(17,23,48,.98),rgba(8,12,28,.98))!important;
  border-color:rgba(99,102,241,.46)!important;
  box-shadow:0 42px 120px rgba(0,0,0,.82),0 0 0 1px rgba(255,255,255,.04) inset,0 0 90px rgba(99,102,241,.18)!important;
}
#login_modal::before{
  background:
    radial-gradient(190px 125px at 88% 0%,rgba(255,255,255,.10),transparent 62%),
    radial-gradient(240px 170px at 18% -18%,rgba(99,102,241,.45),transparent 70%),
    linear-gradient(135deg,rgba(99,102,241,.45),rgba(30,41,95,.76))!important;
}
#login_modal::after{border-color:rgba(255,255,255,.08)!important;}
#login_modal .lb-form-panel::before{
  background:
    linear-gradient(135deg,rgba(255,255,255,.20),rgba(255,255,255,0)),
    linear-gradient(135deg,#818cf8,#6366f1 52%,#4f46e5)!important;
  box-shadow:0 16px 36px rgba(99,102,241,.42),inset 0 1px 0 rgba(255,255,255,.20)!important;
}
#login_modal .lb-form-panel::after{
  content:"\f005"!important;
  font-family:"Font Awesome 6 Free","Font Awesome 5 Free",Arial,sans-serif!important;
  font-weight:900!important;
  left:45px!important;
  top:43px!important;
  color:#fff!important;
}
#login_modal .nav-tabs{
  border-color:rgba(99,102,241,.42)!important;
  background:rgba(10,16,36,.62)!important;
}
#login_modal .nav-tabs a.active{
  background:linear-gradient(135deg,#818cf8,#6366f1 52%,#4f46e5)!important;
  box-shadow:0 10px 24px rgba(99,102,241,.36),inset 0 1px 0 rgba(255,255,255,.18)!important;
}
#login_modal .nav-tabs a:not(.active):hover{background:rgba(99,102,241,.12)!important;}
#login_modal .form-group label{color:#c7d2fe!important;}
#login_modal .form-group label::before{
  font-family:"Font Awesome 6 Free","Font Awesome 5 Free",Arial,sans-serif!important;
  font-weight:900!important;
  color:#818cf8!important;
}
#login_modal .form-group:has(input[type="email"]) label::before{content:"\f0e0"!important;}
#login_modal .form-group:has(input[name="username"]) label::before{content:"\f007"!important;}
#login_modal .form-group:has(input[type="password"]) label::before{content:"\f084"!important;}
#login_modal input[type="email"],
#login_modal input[type="text"],
#login_modal input[type="password"]{
  border-color:rgba(99,102,241,.36)!important;
  background:rgba(255,255,255,.052)!important;
}
#login_modal input[type="email"]:focus,
#login_modal input[type="text"]:focus,
#login_modal input[type="password"]:focus{
  border-color:rgba(129,140,248,.95)!important;
  box-shadow:0 0 0 4px rgba(99,102,241,.16)!important;
}
#login_modal .password-toggle:hover{background:rgba(99,102,241,.22)!important;}
#login_modal .remember-me input[type="checkbox"]{border-color:rgba(129,140,248,.58)!important;}
#login_modal .remember-me input[type="checkbox"]::before{
  content:"\f00c"!important;
  font-family:"Font Awesome 6 Free","Font Awesome 5 Free",Arial,sans-serif!important;
  font-weight:900!important;
}
#login_modal .remember-me input[type="checkbox"]:checked{
  background:linear-gradient(135deg,#818cf8,#6366f1)!important;
  box-shadow:0 8px 18px rgba(99,102,241,.28)!important;
}
#login_modal .remember-me a,
#login_modal .lb-legal a{color:#818cf8!important;}
#login_modal .submit-btn{
  background:linear-gradient(135deg,#818cf8 0%,#6366f1 50%,#4f46e5 100%)!important;
  box-shadow:0 16px 34px rgba(99,102,241,.34)!important;
}
#login_modal .submit-btn:hover{box-shadow:0 20px 42px rgba(99,102,241,.46)!important;}
#login_modal .lb-social-btn.google-login{
  background:rgba(255,255,255,.07)!important;
  border:1px solid rgba(255,255,255,.12)!important;
  color:#fff!important;
  box-shadow:0 12px 26px rgba(0,0,0,.18)!important;
}
#login_modal .lb-social-btn.google-login:hover{
  background:rgba(99,102,241,.12)!important;
  border-color:rgba(129,140,248,.30)!important;
}
#login_modal .lb-social-btn.discord-login{
  background:linear-gradient(135deg,#6366f1,#4f46e5)!important;
  box-shadow:0 12px 26px rgba(99,102,241,.25)!important;
}
#login_modal .close-modal:hover{background:rgba(99,102,241,.18)!important;}
.am-rp-box .am-btn,
.am-small-modal .submit-btn{
  background:linear-gradient(135deg,#818cf8 0%,#6366f1 50%,#4f46e5 100%)!important;
  box-shadow:0 10px 28px rgba(99,102,241,.32)!important;
}
.am-small-modal{border-color:rgba(99,102,241,.24)!important;}
.am-small-modal::before{background:linear-gradient(180deg,transparent,#6366f1 40%,#6366f1 60%,transparent)!important;}
.am-small-modal::after{background:linear-gradient(90deg,transparent,rgba(99,102,241,.58),transparent)!important;}
.am-small-modal input:focus,
.am-rp-box input:focus{border-color:rgba(129,140,248,.7)!important;box-shadow:0 0 0 3px rgba(99,102,241,.13)!important;}
</style>

<div class="modal" id="login_modal">
  <div class="modal-header">
    <button type="button" class="close-modal" aria-label="<?= t('Close') ?>">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="modal-content">
    <div class="lb-auth-shell">

      <!-- ─ LINKE SEITE ─────────────────── -->
      <aside class="lb-visual" aria-hidden="true">
        <span class="lb-badge"><?= t('Secure Login') ?></span>
        <div class="lb-rank-card">
          <i class="fas fa-sign-in-alt"></i>
        </div>
        <div class="lb-copy">
          <h2><?= t('Your') ?><span><?= t('Account') ?></span></h2>
          <p><?= t('Sign in to access your account, messages, orders and saved settings.') ?></p>
        </div>
        <div class="lb-stats">
          <div class="lb-stat">
            <strong>24/7</strong>
            <span><?= t('Fast Access') ?></span>
          </div>
          <div class="lb-stat">
            <strong>4.9★</strong>
            <span><?= t('Protected') ?></span>
          </div>
        </div>
      </aside>

      <!-- ─ RECHTE SEITE ────────────────── -->
      <section class="lb-form-panel">

        <!-- 1. Trenner — IMMER oben, niemals verschoben -->
        <div class="lb-form-top">
          <div class="lb-form-top-line"></div>
          <div class="lb-form-top-dot"></div>
          <div class="lb-form-top-line"></div>
        </div>

        <!-- Mobile only -->
        <div class="lb-logo">
          <span><?= htmlspecialchars($authBrand, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="lb-logo-mark"><i class="fas fa-bolt"></i></span>
        </div>

        <!-- 2. Heading -->
        <div class="lb-heading"
             data-login-text="<?= htmlspecialchars(t('Sign in'), ENT_QUOTES, 'UTF-8') ?>"
             data-login-sub="<?= htmlspecialchars(t('Welcome back — enter your details below.'), ENT_QUOTES, 'UTF-8') ?>"
             data-register-text="<?= htmlspecialchars(t('Create account'), ENT_QUOTES, 'UTF-8') ?>"
             data-register-sub="<?= htmlspecialchars(t('Join us — it only takes a minute.'), ENT_QUOTES, 'UTF-8') ?>">
          <?= t('Sign in') ?><small><?= t('Welcome back — enter your details below.') ?></small>
        </div>

        <!-- 3. Tabs -->
        <div class="nav-tabs">
          <a href="#login-pane" class="active">
            <i class="fas fa-sign-in-alt"></i><?= t('Login') ?>
          </a>
          <a href="#register-pane">
            <i class="fas fa-user-plus"></i><?= t('Register') ?>
          </a>
        </div>

        <!-- 4. Separator + Socials -->
        <div class="lb-tab-sep">
          <div class="lb-tab-sep-line"></div>
          <span class="lb-tab-sep-text"><?= t('continue with') ?></span>
          <div class="lb-tab-sep-line"></div>
        </div>

        <div class="lb-socials">
          <a href="/auth/google" class="lb-social-btn google-login">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Google
          </a>
          <a href="/auth/discord" class="lb-social-btn discord-login">
            <i class="fab fa-discord"></i><?= t('Discord') ?>
          </a>
        </div>

        <!-- 5. Divider -->
        <div class="lb-divider"><?= t('or use email') ?></div>

        <!-- 6. Tab-Content: wächst, füllt Restfläche -->
        <div class="tab-content">

          <!-- LOGIN -->
          <div class="tab-pane active" id="login-pane">
            <form class="ajax-form" action="<?= AJAX_URL ?>">
              <input type="hidden" name="action" value="<?= $authIsCheckout ? 'auth_client_login' : 'auth_unified_login' ?>">
              <?php if ($authIsCheckout): ?>
                <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($authCheckoutReturnUrl, ENT_QUOTES, 'UTF-8') ?>">
              <?php endif; ?>
              <div class="alert danger form-error" style="display:none"></div>

              <!-- Spacer kompensiert das fehlende Username-Feld -->
              <div class="login-field-spacer"></div>

              <div class="form-group">
                <label><?= t('Email') ?></label>
                <input type="email" name="email" placeholder="you@example.com" required>
              </div>

              <div class="form-group">
                <label><?= t('Password') ?></label>
                <div class="password-wrapper">
                  <input type="password" name="password" id="login_password" placeholder="••••••••" required>
                  <button type="button" class="password-toggle" data-target="#login_password" aria-label="<?= t('Show password') ?>">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="remember-me">
                <input type="checkbox" name="remember_me" id="remember">
                <label for="remember"><?= t('Remember me') ?></label>
                <a href="javascript:void(0)" class="forgot-password-link" id="frg-pwd">
                  <?= t('Forgot password?') ?>
                </a>
              </div>

              <div class="form-push"></div>

              <button class="submit-btn" type="submit">
                <span class="indicator-label"><?= t('Sign in') ?></span>
                <span class="indicator-progress"><span class="loader small secondary block"></span></span>
              </button>
            </form>
          </div>

          <!-- REGISTER -->
          <div class="tab-pane" id="register-pane">
            <form class="ajax-form" action="<?= AJAX_URL ?>">
              <input type="hidden" name="action" value="auth_client_register">
              <div class="alert danger form-error" style="display:none"></div>

              <div class="form-group">
                <label><?= t('Username') ?></label>
                <input type="text" name="username" placeholder="<?= t('Your username') ?>" required>
              </div>

              <div class="form-group">
                <label><?= t('Email') ?></label>
                <input type="email" name="email" placeholder="mail@example.com" required>
              </div>

              <div class="form-group">
                <label><?= t('Password') ?></label>
                <div class="password-wrapper">
                  <input type="password" name="password" id="register_password" placeholder="••••••••" required>
                  <button type="button" class="password-toggle" data-target="#register_password" aria-label="<?= t('Show password') ?>">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="remember-me">
                <input type="checkbox" name="tos" id="tos" required>
                <label for="tos">
                  <?= t('I agree to the') ?>
                  <a href="<?= BASE_URL ?>/terms" target="_blank"><?= t('terms of service') ?></a>
                  <?= t('and') ?>
                  <a href="<?= BASE_URL ?>/privacy" target="_blank"><?= t('privacy policy') ?></a>
                </label>
              </div>

              <div class="form-push"></div>

              <button class="submit-btn" type="submit">
                <span class="indicator-label"><?= t('Create account') ?></span>
                <span class="indicator-progress"><span class="loader"></span></span>
              </button>
            </form>
          </div>

        </div><!-- /tab-content -->


      </section>
    </div>
  </div>
</div>


<script>
(function () {
  function syncAuthColorMode() {
    const modal = document.getElementById('login_modal');
    if (!modal) return;
    const registerPane = modal.querySelector('#register-pane');
    const activeTab    = modal.querySelector('.nav-tabs a.active');
    const isRegister   =
      (activeTab && activeTab.getAttribute('href') === '#register-pane') ||
      (registerPane && registerPane.classList.contains('active'));

    modal.classList.toggle('is-register', !!isRegister);
    modal.classList.toggle('is-login',    !isRegister);

    const badge = modal.querySelector('.lb-badge');
    if (badge) badge.textContent = isRegister ? 'New Account' : 'Secure Login';

    const copyTitle = modal.querySelector('.lb-copy h2');
    if (copyTitle) copyTitle.innerHTML = isRegister
      ? '<?= t("Create") ?><span><?= t("Account") ?></span>'
      : '<?= t("Your") ?><span><?= t("Account") ?></span>';

    const copyText = modal.querySelector('.lb-copy p');
    if (copyText) copyText.textContent = isRegister
      ? '<?= t("Register once and keep your details, orders and preferences in one place.") ?>'
      : '<?= t("Sign in to access your account, messages, orders and saved settings.") ?>';

    const icon = modal.querySelector('.lb-rank-card i');
    if (icon) icon.className = isRegister ? 'fas fa-user-plus' : 'fas fa-sign-in-alt';

    const stats = modal.querySelectorAll('.lb-stat');
    if (stats[0]) { stats[0].querySelector('strong').textContent = isRegister ? 'Quick' : 'Fast'; stats[0].querySelector('span').textContent = isRegister ? 'Setup' : 'Access'; }
    if (stats[1]) { stats[1].querySelector('strong').textContent = isRegister ? 'Easy' : 'Safe'; stats[1].querySelector('span').textContent = isRegister ? 'Start' : 'Protected'; }

    const heading = modal.querySelector('.lb-heading');
    if (heading) {
      const title = isRegister ? heading.dataset.registerText : heading.dataset.loginText;
      const sub   = isRegister ? heading.dataset.registerSub  : heading.dataset.loginSub;
      heading.innerHTML = title + (sub ? '<small>' + sub + '</small>' : '');
    }
  }

  document.addEventListener('DOMContentLoaded', syncAuthColorMode);
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#login_modal .nav-tabs a')) return;
    window.setTimeout(syncAuthColorMode, 0);
    window.setTimeout(syncAuthColorMode, 80);
  });
  syncAuthColorMode();

  // Password toggle
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.password-toggle, .pw-eye');
    if (!btn) return;
    const input = document.querySelector(btn.getAttribute('data-target'));
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) { icon.classList.toggle('fa-eye', !isPassword); icon.classList.toggle('fa-eye-slash', isPassword); }
  });
})();
</script>

<style>
/* ══════════════════════════════════════════════════
   KLEINE MODALS — Forgot Password + Guest Checkout
   Gleiches Design-System wie Login-Modal
   Schmale Cards, 3px Indigo-Streifen links, gleiche Tokens
══════════════════════════════════════════════════ */

.am-small-modal,
.am-small-modal * { box-sizing: border-box; }

.am-small-modal {
  position: fixed !important;
  inset: auto !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  z-index: 2147483500 !important;
  width: min(460px, calc(100vw - 24px)) !important;
  height: auto !important;
  max-height: calc(100dvh - 24px) !important;
  margin: 0 !important;
  padding: 0 !important;
  background: #11131e !important;
  border: 1px solid rgba(88,101,242,.18) !important;
  border-radius: 20px !important;
  box-shadow:
    0 0 0 1px rgba(255,255,255,.025) inset,
    0 40px 100px rgba(0,0,0,.75),
    0 0 60px rgba(88,101,242,.06) !important;
  overflow: hidden !important;
  color: #dde0f5 !important;
  scrollbar-width: none !important;
}
.am-small-modal *::-webkit-scrollbar { display: none !important; }

/* Leuchtlinie oben */
.am-small-modal::after {
  content: "";
  position: absolute;
  top: 0; left: 15%; right: 15%; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(88,101,242,.55), transparent);
  z-index: 4; pointer-events: none;
}

/* 3px Akzent-Streifen links */
.am-small-modal::before {
  content: "";
  position: absolute;
  left: 0; top: 20%; bottom: 20%; width: 3px;
  background: linear-gradient(180deg, transparent, #5865f2 40%, #5865f2 60%, transparent);
  border-radius: 0 2px 2px 0;
  z-index: 4; pointer-events: none;
}

/* Header */
.am-small-modal .modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 0;
  border: 0;
  background: transparent;
  margin: 0;
}
.am-small-modal .modal-header h4 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -.3px;
}
.am-small-modal .close-modal {
  width: 36px; height: 36px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.05);
  color: rgba(255,255,255,.4);
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; flex-shrink: 0;
  transition: background .15s, color .15s, transform .2s;
}
.am-small-modal .close-modal:hover {
  background: rgba(255,255,255,.12); color: #fff; transform: rotate(90deg);
}

/* Content */
.am-small-modal .modal-content {
  padding: 16px 24px 26px !important;
  background: transparent !important;
  overflow: hidden !important;
}

/* Sub-Heading / Beschreibung */
.am-small-modal .am-modal-sub {
  font-size: 14px;
  color: rgba(148,156,205,.55);
  margin: 4px 0 18px;
  line-height: 1.5;
}

/* Form-Felder — gleiche Tokens wie Login-Modal */
.am-small-modal .form-group { margin-bottom: 12px; }
.am-small-modal .form-group label {
  display: block; margin-bottom: 6px;
  font-size: 12px; font-weight: 700;
  letter-spacing: .6px; text-transform: uppercase;
  color: rgba(148,156,205,.60);
}
.am-small-modal input[type="email"],
.am-small-modal input[type="text"],
.am-small-modal input[type="password"] {
  width: 100%; height: 50px;
  border-radius: 11px;
  border: 1px solid rgba(255,255,255,.09);
  background: #191b2d;
  color: #fff; font-size: 16px;
  padding: 0 16px; outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.am-small-modal input:focus {
  border-color: rgba(88,101,242,.55) !important;
  box-shadow: 0 0 0 3px rgba(88,101,242,.11) !important;
}
.am-small-modal input::placeholder { color: rgba(255,255,255,.2); }

/* Remember-me / Checkbox */
.am-small-modal .remember-me {
  display: flex; align-items: flex-start; gap: 9px;
  margin: 4px 0 16px;
  color: rgba(148,156,205,.60); font-size: 13px; line-height: 1.5;
}
.am-small-modal .remember-me input[type="checkbox"] {
  width: 15px; height: 15px;
  margin-top: 2px; flex-shrink: 0;
  accent-color: #5865f2; cursor: pointer;
}
.am-small-modal .remember-me label { cursor: pointer; }
.am-small-modal .remember-me a {
  color: #818cf8; text-decoration: none;
}
.am-small-modal .remember-me a:hover { color: #fff; text-decoration: underline; }

/* Error */
.am-small-modal .form-error {
  border-radius: 10px; padding: 9px 13px; font-size: 13px;
  background: rgba(239,68,68,.09); border: 1px solid rgba(239,68,68,.22);
  color: #fca5a5; margin-top: 10px;
}

/* Submit */
.am-small-modal .submit-btn {
  width: 100%; height: 46px;
  border: 0; border-radius: 12px;
  background: linear-gradient(160deg, #6672f5 0%, #4452e8 100%);
  color: #fff; font-size: 15.5px; font-weight: 700; cursor: pointer;
  box-shadow: 0 10px 28px rgba(88,101,242,.32);
  position: relative; overflow: hidden;
  transition: filter .15s, transform .15s, box-shadow .15s;
  margin-top: 4px;
}
.am-small-modal .submit-btn::before {
  content: ""; position: absolute; top: 0; left: 0; right: 0; height: 48%;
  background: linear-gradient(180deg, rgba(255,255,255,.11), transparent);
  pointer-events: none;
}
.am-small-modal .submit-btn:hover {
  filter: brightness(1.10); transform: translateY(-1px);
  box-shadow: 0 14px 36px rgba(88,101,242,.40);
}

/* Legal */
.am-small-modal .am-legal {
  margin-top: 12px;
  text-align: center;
  color: rgba(255,255,255,.30);
  font-size: 12px;
  line-height: 1.6;
}
.am-small-modal .am-legal a {
  color: #818cf8;
  text-decoration: none;
  transition: color .15s;
}
.am-small-modal .am-legal a:hover { color: #fff; text-decoration: underline; }

@media (max-width: 520px) {
  .am-small-modal {
    top: auto !important; bottom: 0 !important;
    left: 0 !important; transform: none !important;
    width: 100vw !important;
    border-radius: 20px 20px 0 0 !important;
    border: 0 !important;
  }
  .am-small-modal::before { display: none; }
}
</style>


<!-- ══ FORGOT PASSWORD MODAL ═════════════════════════════ -->
<div class="modal am-small-modal" id="forgot_password_modal">
  <div class="modal-header">
    <h4><?= t('Forgot Password') ?></h4>
    <button type="button" class="close-modal" aria-label="<?= t('Close') ?>">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="modal-content">
    <p class="am-modal-sub"><?= t("Enter your email and we'll send you a reset link.") ?></p>
    <form class="ajax-form" action="<?= AJAX_URL ?>">
      <input type="hidden" name="action" value="client_forgot_password">
      <div class="form-group">
        <label><?= t('Email Address') ?></label>
        <input type="text" name="email" placeholder="email@example.com" required>
      </div>
      <button class="submit-btn" type="submit">
        <span class="indicator-label"><?= t('Send Reset Link') ?></span>
        <span class="indicator-progress"><span class="loader"></span></span>
      </button>
      <div class="alert danger form-error" style="display:none"></div>
      <p class="am-legal">
        <?= t('By continuing, you agree to the') ?>
        <a href="<?= BASE_URL ?>/terms" target="_blank"><?= t('Terms of Use') ?></a>
        <?= t('and') ?>
        <a href="<?= BASE_URL ?>/privacy" target="_blank"><?= t('Privacy Policy') ?></a>.
      </p>
    </form>
  </div>
</div>


<!-- ══ GUEST CHECKOUT MODAL ══════════════════════════════ -->
<div class="modal am-small-modal" id="guest_checkout_modal">
  <div class="modal-header">
    <h4><?= t('Continue as Guest') ?></h4>
    <button type="button" class="close-modal" aria-label="<?= t('Close') ?>">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="modal-content">
    <p class="am-modal-sub"><?= t("Enter your email — we'll send your order details and login there.") ?></p>
    <form class="ajax-form" action="<?= AJAX_URL ?>">
      <input type="hidden" name="action" value="auth_client_guest_login">
      <input type="hidden" name="tos" value="1">
      <div class="form-group">
        <input type="text" name="email" placeholder="your@email.com" required>
      </div>
      <button class="submit-btn" type="submit">
        <span class="indicator-label"><?= t('Continue') ?></span>
        <span class="indicator-progress"><span class="loader"></span></span>
      </button>
      <div class="alert danger form-error" style="display:none"></div>
      <p class="am-legal">
        <?= t('By continuing, you agree to the') ?>
        <a href="<?= BASE_URL ?>/terms" target="_blank"><?= t('Terms of Use') ?></a>
        <?= t('and') ?>
        <a href="<?= BASE_URL ?>/privacy" target="_blank"><?= t('Privacy Policy') ?></a>.
      </p>
    </form>
  </div>
</div>

<style id="lb-auth-focus-icons-final-fix">
/* Final focus layer: auth modal always sits above navbar and page content. */
.lb-auth-focus-backdrop{
  position:fixed!important;
  inset:0!important;
  z-index:2147483500!important;
  display:none!important;
  background:rgba(3,5,18,.82)!important;
  backdrop-filter:blur(18px)!important;
  -webkit-backdrop-filter:blur(18px)!important;
  pointer-events:auto!important;
}
.lb-auth-focus-backdrop.is-open{display:block!important;}
body.lb-auth-modal-open{overflow:hidden!important;}
body.lb-auth-modal-open .navbar,
body.lb-auth-modal-open .navbar-mobile,
body.lb-auth-modal-open .lb-mobile-gamebar,
body.lb-auth-modal-open .lb-mobile-bottomnav,
body.lb-auth-modal-open header,
body.lb-auth-modal-open .header,
body.lb-auth-modal-open .sticky-button{
  pointer-events:none!important;
}
#login_modal{
  z-index:2147483600!important;
  pointer-events:auto!important;
}
#login_modal .modal-content,
#login_modal .lb-form-panel,
#login_modal input,
#login_modal button,
#login_modal a{
  pointer-events:auto!important;
}

/* Icon fixes, use the FontAwesome already loaded in master.php.
   Do not load or override FontAwesome here, otherwise the landing page icon font can break. */
#login_modal .lb-form-panel::after{
  content:"✦"!important;
  font-family:Arial,sans-serif!important;
  font-weight:900!important;
  color:#fff!important;
}
#login_modal.is-register .lb-form-panel::after,
#login_modal.auth-register .lb-form-panel::after{content:"+"!important;}
#login_modal.is-login .lb-form-panel::after,
#login_modal.auth-login .lb-form-panel::after{content:"✦"!important;}
#login_modal .form-group label::before{
  content:""!important;
  display:none!important;
}
#login_modal .remember-me input[type="checkbox"]::before{
  content:"✓"!important;
  font-family:Arial,sans-serif!important;
  font-weight:900!important;
}
#login_modal .nav-tabs a i,
#login_modal .lb-social-btn i,
#login_modal .password-toggle i,
#login_modal .close-modal i{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  opacity:1!important;
  visibility:visible!important;
}
#login_modal .lb-social-btn.google-login svg{
  display:block!important;
  width:16px!important;
  height:16px!important;
  min-width:16px!important;
  opacity:1!important;
  visibility:visible!important;
}
#login_modal .lb-social-btn.discord-login i{
  font-weight:400!important;
  font-size:17px!important;
}

/* A little more focus polish. */
#login_modal{
  animation:lbAuthFocusIn .18s ease-out both;
}
@keyframes lbAuthFocusIn{
  from{opacity:.82;transform:translate(-50%,-47%) scale(.985);}
  to{opacity:1;transform:translate(-50%,-50%) scale(1);}
}
@media(max-width:560px){
  #login_modal{animation:lbAuthFocusInMobile .18s ease-out both;}
  @keyframes lbAuthFocusInMobile{
    from{opacity:.82;transform:translateY(16px);}
    to{opacity:1;transform:translateY(0);}
  }
}
</style>

<script id="lb-auth-focus-icons-final-js">
(function(){
  function getModal(){ return document.getElementById('login_modal'); }
  function ensureBackdrop(){
    var backdrop = document.querySelector('.lb-auth-focus-backdrop');
    if(!backdrop){
      backdrop = document.createElement('div');
      backdrop.className = 'lb-auth-focus-backdrop';
      document.body.appendChild(backdrop);
      backdrop.addEventListener('click', function(){
        var modal = getModal();
        if(!modal) return;
        var close = modal.querySelector('.close-modal');
        if(close) close.click();
      });
    }
    return backdrop;
  }
  function isVisible(el){
    if(!el) return false;
    var cs = window.getComputedStyle(el);
    return cs.display !== 'none' && cs.visibility !== 'hidden' && parseFloat(cs.opacity || '1') !== 0;
  }
  function setFocusState(){
    var modal = getModal();
    var open = isVisible(modal);
    var backdrop = ensureBackdrop();
    backdrop.classList.toggle('is-open', open);
    document.body.classList.toggle('lb-auth-modal-open', open);
    if(open && modal && modal.parentNode !== document.body){
      document.body.appendChild(modal);
    }
  }
  function syncMode(){
    var modal = getModal();
    if(!modal) return;
    var active = modal.querySelector('.nav-tabs a.active');
    var isRegister = active && active.getAttribute('href') === '#register-pane';
    modal.classList.toggle('is-register', !!isRegister);
    modal.classList.toggle('is-login', !isRegister);
  }
  function init(){
    ensureBackdrop();
    syncMode();
    setFocusState();
    var modal = getModal();
    if(!modal) return;
    var obs = new MutationObserver(function(){ syncMode(); setFocusState(); });
    obs.observe(modal, {attributes:true, attributeFilter:['style','class']});
    document.addEventListener('click', function(e){
      if(e.target.closest('#login_modal .nav-tabs a')) setTimeout(syncMode, 0);
      if(e.target.closest('[data-modal="#login_modal"], [data-target="#login_modal"], .open-login-modal, .login-modal-trigger, #frg-pwd')) setTimeout(setFocusState, 30);
      if(e.target.closest('#login_modal .close-modal')) setTimeout(setFocusState, 30);
    }, true);
    window.addEventListener('resize', setFocusState, {passive:true});
    setInterval(setFocusState, 400);
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>

<style id="lb-auth-hidden-until-open-fix">
/* Keep the auth modal completely hidden until the site's modal opener actually opens it. */
#login_modal:not(.show):not(.active):not(.is-open):not([style*="display: block"]):not([style*="display:block"]):not([style*="display: flex"]):not([style*="display:flex"]){
  display:none!important;
  visibility:hidden!important;
  opacity:0!important;
  pointer-events:none!important;
}
#login_modal.show,
#login_modal.active,
#login_modal.is-open,
#login_modal[style*="display: block"],
#login_modal[style*="display:block"],
#login_modal[style*="display: flex"],
#login_modal[style*="display:flex"]{
  visibility:visible!important;
  opacity:1!important;
  pointer-events:auto!important;
}
body:not(.lb-auth-modal-open) .lb-auth-focus-backdrop{
  display:none!important;
}
</style>

<script id="lb-auth-hidden-until-open-js">
(function(){
  function modalOpenBySource(modal){
    if(!modal) return false;
    var cls = modal.classList;
    if(cls.contains('show') || cls.contains('active') || cls.contains('is-open')) return true;
    var raw = (modal.getAttribute('style') || '').replace(/\s+/g, '').toLowerCase();
    return raw.indexOf('display:block') !== -1 || raw.indexOf('display:flex') !== -1 || raw.indexOf('display:grid') !== -1;
  }
  function getBackdrop(){ return document.querySelector('.lb-auth-focus-backdrop'); }
  function syncAuthOpenState(){
    var modal = document.getElementById('login_modal');
    var open = modalOpenBySource(modal);
    var backdrop = getBackdrop();
    if(backdrop) backdrop.classList.toggle('is-open', open);
    document.body.classList.toggle('lb-auth-modal-open', open);
    if(open && modal && modal.parentNode !== document.body){
      document.body.appendChild(modal);
    }
  }
  function init(){
    var modal = document.getElementById('login_modal');
    if(!modal) return;
    syncAuthOpenState();
    var obs = new MutationObserver(syncAuthOpenState);
    obs.observe(modal, {attributes:true, attributeFilter:['style','class']});
    document.addEventListener('click', function(e){
      if(e.target.closest('#login_modal .close-modal, [data-modal="#login_modal"], [data-target="#login_modal"], .open-login-modal, .login-modal-trigger')){
        setTimeout(syncAuthOpenState, 20);
        setTimeout(syncAuthOpenState, 160);
      }
    }, true);
    setInterval(syncAuthOpenState, 500);
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>

<style id="lb-auth-forgot-checkbox-redesign-final">
/* Final custom checkbox, no FontAwesome dependency */
#login_modal .remember-me input[type="checkbox"],
.am-small-modal .remember-me input[type="checkbox"]{
  appearance:none!important;
  -webkit-appearance:none!important;
  width:21px!important;
  height:21px!important;
  min-width:21px!important;
  border-radius:8px!important;
  margin:0!important;
  cursor:pointer!important;
  display:inline-grid!important;
  place-items:center!important;
  position:relative!important;
  background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025))!important;
  border:1px solid rgba(139,126,255,.55)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 0 0 3px rgba(124,92,255,.06)!important;
  transition:background .16s ease,border-color .16s ease,box-shadow .16s ease,transform .12s ease!important;
}
#login_modal .remember-me input[type="checkbox"]::before,
.am-small-modal .remember-me input[type="checkbox"]::before{
  content:""!important;
  width:9px!important;
  height:5px!important;
  border-left:2px solid #fff!important;
  border-bottom:2px solid #fff!important;
  transform:rotate(-45deg) scale(0)!important;
  transform-origin:center!important;
  margin-top:-2px!important;
  transition:transform .14s cubic-bezier(.2,1,.2,1)!important;
}
#login_modal .remember-me input[type="checkbox"]:checked,
.am-small-modal .remember-me input[type="checkbox"]:checked{
  background:linear-gradient(135deg,#6f5cff,#8c4df5)!important;
  border-color:rgba(255,255,255,.18)!important;
  box-shadow:0 0 0 4px rgba(124,92,255,.16),0 10px 22px rgba(124,92,255,.30)!important;
}
#login_modal .remember-me input[type="checkbox"]:checked::before,
.am-small-modal .remember-me input[type="checkbox"]:checked::before{
  transform:rotate(-45deg) scale(1)!important;
}
#login_modal .remember-me input[type="checkbox"]:hover,
.am-small-modal .remember-me input[type="checkbox"]:hover{
  border-color:rgba(178,166,255,.85)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.10),0 0 0 4px rgba(124,92,255,.12)!important;
}
#login_modal .remember-me label,
.am-small-modal .remember-me label{
  user-select:none!important;
}

/* Forgot password modal, redesigned in the same compact seller-chat style */
#forgot_password_modal.am-small-modal{
  width:min(520px,calc(100vw - 28px))!important;
  border-radius:24px!important;
  border:1px solid rgba(126,104,255,.45)!important;
  background:linear-gradient(180deg,#151730 0%,#0c1022 100%)!important;
  box-shadow:0 42px 120px rgba(0,0,0,.78),0 0 0 1px rgba(255,255,255,.04) inset,0 0 80px rgba(124,92,255,.12)!important;
  overflow:hidden!important;
  z-index:2147483600!important;
}
#forgot_password_modal.am-small-modal::before{
  display:none!important;
}
#forgot_password_modal.am-small-modal::after{
  content:""!important;
  position:absolute!important;
  inset:0 0 auto 0!important;
  height:118px!important;
  background:
    radial-gradient(280px 150px at 18% 0%,rgba(139,92,246,.35),transparent 65%),
    radial-gradient(230px 180px at 88% 8%,rgba(99,102,241,.22),transparent 72%),
    linear-gradient(135deg,rgba(124,92,255,.28),rgba(124,92,255,.04))!important;
  border-bottom:1px solid rgba(139,126,255,.20)!important;
  pointer-events:none!important;
  z-index:0!important;
}
#forgot_password_modal .modal-header{
  position:relative!important;
  z-index:2!important;
  padding:26px 28px 0!important;
  align-items:flex-start!important;
}
#forgot_password_modal .modal-header h4{
  position:relative!important;
  min-height:56px!important;
  display:flex!important;
  align-items:center!important;
  padding-left:72px!important;
  margin:0!important;
  color:#fff!important;
  font-size:23px!important;
  font-weight:950!important;
  letter-spacing:-.45px!important;
  line-height:1.1!important;
}
#forgot_password_modal .modal-header h4::before{
  content:"?"!important;
  position:absolute!important;
  left:0!important;
  top:0!important;
  width:56px!important;
  height:56px!important;
  border-radius:16px!important;
  display:grid!important;
  place-items:center!important;
  color:#fff!important;
  font-size:28px!important;
  font-weight:950!important;
  background:linear-gradient(135deg,#7357ff,#9b5cff)!important;
  box-shadow:0 16px 34px rgba(124,92,255,.34),inset 0 1px 0 rgba(255,255,255,.18)!important;
}
#forgot_password_modal .close-modal{
  width:40px!important;
  height:40px!important;
  border-radius:14px!important;
  border:1px solid rgba(255,255,255,.14)!important;
  background:rgba(255,255,255,.10)!important;
  color:#fff!important;
  font-size:15px!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08)!important;
}
#forgot_password_modal .modal-content{
  position:relative!important;
  z-index:2!important;
  padding:18px 28px 30px!important;
}
#forgot_password_modal .am-modal-sub{
  margin:16px 0 20px!important;
  color:rgba(225,228,255,.70)!important;
  font-size:15px!important;
  font-weight:650!important;
  line-height:1.55!important;
}
#forgot_password_modal .form-group{
  margin:0 0 16px!important;
}
#forgot_password_modal .form-group label{
  display:flex!important;
  align-items:center!important;
  gap:8px!important;
  margin-bottom:9px!important;
  color:#b8bfff!important;
  font-size:13px!important;
  font-weight:950!important;
  letter-spacing:0!important;
  text-transform:none!important;
}
#forgot_password_modal .form-group label::before{
  content:"@"!important;
  width:18px!important;
  height:18px!important;
  border-radius:6px!important;
  display:grid!important;
  place-items:center!important;
  color:#d8d4ff!important;
  font-size:12px!important;
  font-weight:950!important;
  background:rgba(124,92,255,.16)!important;
  border:1px solid rgba(139,126,255,.24)!important;
}
#forgot_password_modal input[type="email"],
#forgot_password_modal input[type="text"]{
  height:56px!important;
  border-radius:16px!important;
  border:1px solid rgba(139,126,255,.34)!important;
  background:rgba(255,255,255,.055)!important;
  color:#fff!important;
  font-size:16px!important;
  font-weight:750!important;
  padding:0 17px!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04)!important;
}
#forgot_password_modal input:focus{
  border-color:rgba(157,139,255,.95)!important;
  background:rgba(255,255,255,.075)!important;
  box-shadow:0 0 0 4px rgba(124,92,255,.16)!important;
}
#forgot_password_modal .submit-btn{
  height:56px!important;
  border-radius:16px!important;
  margin-top:4px!important;
  background:linear-gradient(135deg,#6b63ff,#8352f4)!important;
  color:#fff!important;
  font-size:16px!important;
  font-weight:950!important;
  box-shadow:0 18px 38px rgba(124,92,255,.34)!important;
}
#forgot_password_modal .submit-btn:hover{
  transform:translateY(-2px)!important;
  filter:brightness(1.07)!important;
  box-shadow:0 22px 46px rgba(124,92,255,.44)!important;
}
#forgot_password_modal .form-error{
  margin-top:12px!important;
  border-radius:14px!important;
}
#forgot_password_modal .am-legal{
  display:none!important;
}
#forgot_password_modal .modal-content form::after{
  content:"Check your inbox after submitting. The reset link is valid for a limited time.";
  display:block!important;
  margin-top:14px!important;
  padding:12px 14px!important;
  border-radius:14px!important;
  background:rgba(99,102,241,.085)!important;
  border:1px solid rgba(139,126,255,.18)!important;
  color:rgba(225,228,255,.62)!important;
  font-size:12.5px!important;
  font-weight:750!important;
  line-height:1.45!important;
  text-align:center!important;
}

/* Keep small modal above every page element and backdrop if site opens it */
body:has(#forgot_password_modal[style*="display: block"]) #forgot_password_modal,
body:has(#forgot_password_modal.show) #forgot_password_modal,
body:has(#forgot_password_modal.active) #forgot_password_modal{
  z-index:2147483600!important;
}

@media(max-width:520px){
  #forgot_password_modal.am-small-modal{
    width:100vw!important;
    left:0!important;
    bottom:0!important;
    top:auto!important;
    transform:none!important;
    border-radius:24px 24px 0 0!important;
    border-left:0!important;
    border-right:0!important;
    border-bottom:0!important;
  }
  #forgot_password_modal .modal-header{padding:22px 22px 0!important;}
  #forgot_password_modal .modal-content{padding:14px 22px 26px!important;}
  #forgot_password_modal .modal-header h4{font-size:21px!important;}
}
</style>

<script id="lb-auth-forgot-checkbox-redesign-js">
(function(){
  function syncForgotBackdrop(){
    var modal = document.getElementById('forgot_password_modal');
    if(!modal) return;
    var raw = (modal.getAttribute('style') || '').replace(/\s+/g,'').toLowerCase();
    var open = modal.classList.contains('show') || modal.classList.contains('active') || modal.classList.contains('is-open') || raw.indexOf('display:block') !== -1 || raw.indexOf('display:flex') !== -1 || raw.indexOf('display:grid') !== -1;
    var bd = document.querySelector('.lb-auth-forgot-backdrop');
    if(!bd){
      bd = document.createElement('div');
      bd.className = 'lb-auth-forgot-backdrop';
      bd.style.cssText = 'position:fixed;inset:0;z-index:2147483550;background:rgba(3,5,16,.82);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);display:none;';
      document.body.appendChild(bd);
      bd.addEventListener('click', function(){
        var close = modal.querySelector('.close-modal');
        if(close) close.click();
      });
    }
    bd.style.display = open ? 'block' : 'none';
    document.body.classList.toggle('lb-forgot-modal-open', open);
    if(open && modal.parentNode !== document.body) document.body.appendChild(modal);
  }
  function init(){
    var modal = document.getElementById('forgot_password_modal');
    if(modal){
      var obs = new MutationObserver(syncForgotBackdrop);
      obs.observe(modal,{attributes:true,attributeFilter:['style','class']});
    }
    document.addEventListener('click', function(e){
      if(e.target.closest('#frg-pwd, .forgot-password-link, #forgot_password_modal .close-modal')){
        setTimeout(syncForgotBackdrop,20);
        setTimeout(syncForgotBackdrop,160);
      }
    }, true);
    syncForgotBackdrop();
    setInterval(syncForgotBackdrop,600);
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>

<style id="lb-auth-single-checkbox-override">
/* Single custom checkbox, native input hidden, visual checkbox rendered on label only. */
#login_modal .remember-me,
.am-small-modal .remember-me{
  position:relative!important;
}
#login_modal .remember-me input[type="checkbox"],
.am-small-modal .remember-me input[type="checkbox"]{
  position:absolute!important;
  left:0!important;
  top:50%!important;
  transform:translateY(-50%)!important;
  width:22px!important;
  height:22px!important;
  min-width:22px!important;
  margin:0!important;
  opacity:0!important;
  appearance:none!important;
  -webkit-appearance:none!important;
  border:0!important;
  background:transparent!important;
  box-shadow:none!important;
  cursor:pointer!important;
  z-index:3!important;
}
#login_modal .remember-me input[type="checkbox"]::before,
#login_modal .remember-me input[type="checkbox"]::after,
.am-small-modal .remember-me input[type="checkbox"]::before,
.am-small-modal .remember-me input[type="checkbox"]::after{
  content:none!important;
  display:none!important;
}
#login_modal .remember-me label,
.am-small-modal .remember-me label{
  position:relative!important;
  padding-left:31px!important;
  min-height:22px!important;
  display:inline-flex!important;
  align-items:center!important;
  cursor:pointer!important;
}
#login_modal .remember-me label::before,
.am-small-modal .remember-me label::before{
  content:""!important;
  position:absolute!important;
  left:0!important;
  top:50%!important;
  width:22px!important;
  height:22px!important;
  border-radius:8px!important;
  transform:translateY(-50%)!important;
  background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.025))!important;
  border:1px solid rgba(139,126,255,.55)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 0 0 3px rgba(124,92,255,.06)!important;
  transition:background .16s ease,border-color .16s ease,box-shadow .16s ease!important;
}
#login_modal .remember-me label::after,
.am-small-modal .remember-me label::after{
  content:""!important;
  position:absolute!important;
  left:7px!important;
  top:50%!important;
  width:9px!important;
  height:5px!important;
  border-left:2px solid #fff!important;
  border-bottom:2px solid #fff!important;
  transform:translateY(-60%) rotate(-45deg) scale(0)!important;
  transform-origin:center!important;
  transition:transform .14s cubic-bezier(.2,1,.2,1)!important;
}
#login_modal .remember-me input[type="checkbox"]:checked + label::before,
.am-small-modal .remember-me input[type="checkbox"]:checked + label::before{
  background:linear-gradient(135deg,#6f5cff,#8c4df5)!important;
  border-color:rgba(255,255,255,.18)!important;
  box-shadow:0 0 0 4px rgba(124,92,255,.16),0 10px 22px rgba(124,92,255,.30)!important;
}
#login_modal .remember-me input[type="checkbox"]:checked + label::after,
.am-small-modal .remember-me input[type="checkbox"]:checked + label::after{
  transform:translateY(-60%) rotate(-45deg) scale(1)!important;
}
#login_modal .remember-me input[type="checkbox"]:focus-visible + label::before,
.am-small-modal .remember-me input[type="checkbox"]:focus-visible + label::before{
  outline:2px solid rgba(139,126,255,.75)!important;
  outline-offset:3px!important;
}
</style>

<style id="lb-auth-terms-spacing-final">
/* Fix spacing around terms/privacy links in custom checkbox labels. */
#login_modal .remember-me label a,
.am-small-modal .remember-me label a{
  margin-left:4px!important;
  margin-right:4px!important;
  display:inline-block!important;
}
#login_modal .remember-me label,
.am-small-modal .remember-me label{
  gap:0!important;
  white-space:normal!important;
}
</style>

<style id="lb-auth-mobile-rebuild">
/* =========================================================
   AUTH-MODAL — MOBILE NEUBAU (<=768px)
   Login + Register. Desktop bleibt komplett unberuehrt.
   ========================================================= */

.lb-auth-focus-backdrop{ z-index:2147483000 !important; }
html body #login_modal{ z-index:2147483100 !important; }

@media (max-width:768px){

  /* ---------- Backdrop weg: Sheet deckt alles ab ---------- */
  html body .lb-auth-focus-backdrop,
  html body .lb-auth-focus-backdrop.is-open{
    display:none !important; opacity:0 !important;
    visibility:hidden !important; pointer-events:none !important; z-index:-1 !important;
  }

  /* ---------- Sheet ---------- */
  html body #login_modal{
    position:fixed !important;
    inset:0 !important; top:0 !important; right:0 !important; bottom:0 !important; left:0 !important;
    transform:none !important; animation:none !important;
    width:100vw !important; max-width:100vw !important; min-width:0 !important;
    height:100dvh !important; max-height:100dvh !important;
    margin:0 !important; padding:0 !important;
    border:0 !important; border-radius:0 !important; box-shadow:none !important;
    background:
      radial-gradient(540px 320px at 88% -6%, rgba(99,102,241,.20), transparent 60%),
      linear-gradient(180deg,#0d0f24 0%,#090a1c 55%,#07081688 100%),
      #070816 !important;
    overflow:hidden !important;
    isolation:isolate !important; pointer-events:auto !important;
    display:flex !important; flex-direction:column !important;
  }
  html body #login_modal *{ pointer-events:auto !important; }
  html body #login_modal::before{ display:none !important; }

  html body #login_modal .modal-content,
  html body #login_modal .lb-auth-shell{
    flex:1 1 auto !important;
    display:flex !important; flex-direction:column !important;
    grid-template-columns:none !important;
    width:100% !important; max-width:100% !important;
    height:auto !important; min-height:0 !important;
    padding:0 !important; margin:0 !important;
    border-radius:0 !important; background:transparent !important;
    overflow:hidden !important;
  }
  html body #login_modal .lb-visual{ display:none !important; }

  /* ---------- Deko-Reste raus ---------- */
  /* ::before = lila 54px-Kachel, ::after = das absolut positionierte Icon darin.
     Beide sind auf das Desktop-Padding verdrahtet und sitzen mobil falsch. */
  html body #login_modal .lb-form-panel::before,
  html body #login_modal .lb-form-panel::after{
    content:none !important; display:none !important;
  }
  html body #login_modal .lb-badge,
  html body #login_modal .lb-logo,
  html body #login_modal .lb-logo-mark,
  html body #login_modal .lb-form-top,
  html body #login_modal .lb-form-top-line,
  html body #login_modal .lb-form-top-dot{ display:none !important; }

  /* ---------- Close ---------- */
  html body #login_modal .modal-header{
    position:absolute !important;
    top:calc(env(safe-area-inset-top) + 14px) !important;
    right:16px !important; left:auto !important;
    z-index:5 !important; padding:0 !important; margin:0 !important;
    background:transparent !important; border:0 !important;
  }
  html body #login_modal .close-modal{
    width:40px !important; height:40px !important; min-width:40px !important;
    border-radius:999px !important;
    border:1px solid rgba(255,255,255,.09) !important;
    background:rgba(255,255,255,.06) !important;
    color:rgba(255,255,255,.80) !important;
    font-size:14px !important;
    display:flex !important; align-items:center !important; justify-content:center !important;
    transform:none !important;
  }

  /* ---------- Panel ---------- */
  html body #login_modal .lb-form-panel{
    position:relative !important;
    flex:1 1 auto !important;
    width:100% !important; max-width:100% !important;
    height:auto !important; min-height:0 !important;
    margin:0 !important; border-radius:0 !important; background:transparent !important;
    padding:calc(env(safe-area-inset-top) + 78px) 22px calc(env(safe-area-inset-bottom) + 30px) !important;
    overflow-y:auto !important; overflow-x:hidden !important;
    -webkit-overflow-scrolling:touch !important;
    display:flex !important; flex-direction:column !important; justify-content:center !important;
  }

  /* ---------- Heading ---------- */
  html body #login_modal .lb-heading{
    font-size:28px !important; font-weight:800 !important;
    line-height:1.18 !important; letter-spacing:-.02em !important;
    margin:0 0 22px !important; color:#fff !important;
  }
  html body #login_modal .lb-heading small{
    display:block !important;
    font-size:14px !important; font-weight:500 !important; line-height:1.5 !important;
    margin-top:8px !important; color:rgba(150,158,205,.72) !important;
  }

  /* ---------- Tabs ---------- */
  html body #login_modal .nav-tabs{
    display:grid !important; grid-template-columns:1fr 1fr !important;
    gap:4px !important; padding:4px !important;
    width:100% !important; margin:0 0 20px !important;
    border-radius:14px !important;
  }
  html body #login_modal .nav-tabs a{
    height:46px !important; border-radius:11px !important;
    font-size:15px !important; font-weight:700 !important;
    display:flex !important; align-items:center !important; justify-content:center !important;
    gap:8px !important;
  }
  html body #login_modal .nav-tabs a i{ font-size:14px !important; }

  /* ---------- Felder ---------- */
  html body #login_modal .form-group{ margin:0 0 14px !important; }
  html body #login_modal .form-group label{
    display:block !important;
    font-size:13px !important; font-weight:700 !important;
    margin:0 0 7px !important; color:rgba(221,224,245,.92) !important;
  }
  html body #login_modal input[type="text"],
  html body #login_modal input[type="email"],
  html body #login_modal input[type="password"]{
    width:100% !important; height:52px !important;
    padding:0 16px !important;
    font-size:16px !important;              /* verhindert iOS-Autozoom */
    border-radius:13px !important;
  }
  html body #login_modal .password-toggle,
  html body #login_modal .pw-eye{ right:14px !important; }
  html body #login_modal .login-field-spacer{ display:none !important; }

  /* ---------- Checkbox-Zeile ---------- */
  html body #login_modal .remember-me{
    display:flex !important; align-items:flex-start !important;
    gap:10px !important; margin:4px 0 20px !important;
  }
  html body #login_modal .remember-me label{
    font-size:13px !important; line-height:1.5 !important; margin:0 !important;
    flex:1 1 auto !important;
  }
  html body #login_modal .forgot-password-link{
    font-size:13px !important; white-space:nowrap !important; margin-left:auto !important;
  }

  /* ---------- Submit ---------- */
  html body #login_modal button[type="submit"],
  html body #login_modal .btn-submit{
    width:100% !important; height:54px !important;
    margin:0 !important;
    font-size:16px !important; font-weight:800 !important;
    border-radius:14px !important;
  }

  /* ---------- Trenner + Socials ---------- */
  html body #login_modal .lb-divider,
  html body #login_modal .lb-tab-sep{ margin:20px 0 14px !important; }
  html body #login_modal .lb-socials{
    display:grid !important; grid-template-columns:1fr 1fr !important;
    gap:10px !important; width:100% !important; margin:0 !important;
  }
  html body #login_modal .lb-social-btn{
    width:100% !important; height:50px !important; margin:0 !important;
    border-radius:13px !important; font-size:14px !important;
  }
  html body #login_modal .lb-social-btn:hover{ transform:none !important; }

  /* ---------- Forgot-Modal: zentriert OHNE transform ---------- */
  html body #forgot_password_modal.am-small-modal{
    position:fixed !important;
    inset:0 !important; top:0 !important; right:0 !important; bottom:0 !important; left:0 !important;
    transform:none !important;
    margin:auto !important;
    width:calc(100vw - 28px) !important; max-width:calc(100vw - 28px) !important;
    height:-moz-fit-content !important; height:fit-content !important;
    max-height:calc(100dvh - 40px) !important;
    overflow-y:auto !important;
    border-radius:22px !important;
    z-index:2147483600 !important;
  }
  html body .lb-auth-forgot-backdrop{ z-index:2147483550 !important; }
  html body #forgot_password_modal input{
    height:52px !important; font-size:16px !important; border-radius:13px !important;
  }
  html body #forgot_password_modal button[type="submit"]{
    height:52px !important; font-size:16px !important; border-radius:14px !important;
  }
}
</style>

<script id="lb-auth-close-controller">
/* =========================================================
   Controller: Close / Tabs / Forgot / ESC.
   In auth.php existierte dafuer KEINE Logik — das haing an einem
   globalen Theme-Skript, das nicht mehr greift.
   ========================================================= */
(function(){
  'use strict';

  var OPEN_CLASSES = ['show','open','active','is-visible','in'];
  var BODY_CLASSES = ['lb-auth-modal-open','overlay','modal-open','auth-modal-open','login-modal-open'];

  function $(s){ return document.querySelector(s); }
  function loginModal(){ return document.getElementById('login_modal'); }
  function forgotModal(){ return document.getElementById('forgot_password_modal'); }
  function isMobile(){ return window.matchMedia('(max-width:768px)').matches; }

  /* Modal immer als letztes Body-Kind, damit es den Backdrop ueberlagert */
  function liftAboveBackdrop(){
    var m = loginModal();
    if(!m) return;
    var bd = $('.lb-auth-focus-backdrop');
    if(bd && bd.parentNode !== document.body) document.body.appendChild(bd);
    document.body.appendChild(m);
    /* Auf Mobile den Backdrop hart abschalten — Fallback, falls CSS nicht greift */
    if(bd) bd.style.display = isMobile() ? 'none' : '';
  }

  function closeLogin(){
    var m = loginModal();
    if(!m) return;
    OPEN_CLASSES.forEach(function(c){ m.classList.remove(c); });
    m.setAttribute('aria-hidden','true');
    m.style.display = 'none';
    m.style.pointerEvents = 'none';
    var bd = $('.lb-auth-focus-backdrop');
    if(bd){ bd.classList.remove('is-open'); bd.style.display = 'none'; }
    BODY_CLASSES.forEach(function(c){
      document.body.classList.remove(c);
      document.documentElement.classList.remove(c);
    });
    document.body.style.removeProperty('overflow');
  }

  function openLogin(){
    var m = loginModal();
    if(!m) return;
    liftAboveBackdrop();
    m.style.removeProperty('display');
    m.style.removeProperty('pointer-events');
    m.removeAttribute('hidden');
    m.setAttribute('aria-hidden','false');
    m.classList.add('show');
  }

  function switchTab(target){
    var m = loginModal();
    if(!m) return;
    m.querySelectorAll('.nav-tabs a').forEach(function(a){
      a.classList.toggle('active', a.getAttribute('href') === target);
    });
    m.querySelectorAll('.tab-pane').forEach(function(p){
      p.classList.toggle('active', ('#' + p.id) === target);
    });
    var isRegister = target === '#register-pane';
    m.classList.toggle('is-register', isRegister);
    m.classList.toggle('is-login', !isRegister);
    var panel = m.querySelector('.lb-form-panel');
    if(panel) panel.scrollTop = 0;
  }

  function openForgot(){
    var f = forgotModal();
    if(!f) return;
    closeLogin();
    if(f.parentNode !== document.body) document.body.appendChild(f);
    f.style.removeProperty('display');
    f.style.removeProperty('pointer-events');
    f.removeAttribute('hidden');
    f.setAttribute('aria-hidden','false');
    f.classList.add('show');
    document.body.classList.add('lb-forgot-modal-open');
  }

  function closeForgot(){
    var f = forgotModal();
    if(!f) return;
    OPEN_CLASSES.forEach(function(c){ f.classList.remove(c); });
    f.setAttribute('aria-hidden','true');
    f.style.display = 'none';
    f.style.pointerEvents = 'none';
    var bd = $('.lb-auth-forgot-backdrop');
    if(bd) bd.style.display = 'none';
    document.body.classList.remove('lb-forgot-modal-open');
    document.body.style.removeProperty('overflow');
  }

  window.lbOpenAuthModal    = openLogin;
  window.lbCloseAuthModal   = closeLogin;
  window.lbOpenForgotModal  = openForgot;
  window.lbCloseForgotModal = closeForgot;

  document.addEventListener('click', function(e){
    if(e.target.closest('#frg-pwd, .forgot-password-link')){
      e.preventDefault(); openForgot(); return;
    }
    if(e.target.closest('#forgot_password_modal .close-modal, #forgot_password_modal [data-dismiss="modal"]')){
      e.preventDefault(); e.stopPropagation(); closeForgot(); return;
    }
    if(e.target.closest('#login_modal .close-modal, #login_modal [data-dismiss="modal"]')){
      e.preventDefault(); e.stopPropagation(); closeLogin(); return;
    }
    var tab = e.target.closest('#login_modal .nav-tabs a');
    if(tab){ e.preventDefault(); switchTab(tab.getAttribute('href')); return; }
    if(e.target.classList && e.target.classList.contains('lb-auth-focus-backdrop')){ closeLogin(); return; }
    if(e.target.classList && e.target.classList.contains('lb-auth-forgot-backdrop')){ closeForgot(); return; }
  }, true);

  document.addEventListener('keydown', function(e){
    if(e.key !== 'Escape' && e.keyCode !== 27) return;
    var f = forgotModal();
    if(f && window.getComputedStyle(f).display !== 'none'){ closeForgot(); return; }
    var m = loginModal();
    if(m && window.getComputedStyle(m).display !== 'none'){ closeLogin(); }
  });

  function watch(){
    var m = loginModal();
    if(!m) return;
    new MutationObserver(function(){
      if(window.getComputedStyle(m).display !== 'none') liftAboveBackdrop();
    }).observe(m, {attributes:true, attributeFilter:['style','class']});
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', watch);
  else watch();
})();
</script>
