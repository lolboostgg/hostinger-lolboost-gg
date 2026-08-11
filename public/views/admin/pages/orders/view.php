<?php

// Clash Boost (form ID 19) is always a Play with Booster / Duo order.
if ((int)($data['form_id'] ?? 0) === 19) {
    $data['is_duo'] = 1;
}

// Format Start LP as a range (0-20 / 21-40 / 41-60 / 61-80 / 81-100) instead of a single number.
// Keeps UI consistent with the LP range selector used in forms.
if (!function_exists('util_format_start_lp_range')) {
    function util_format_start_lp_range($start_lp): string {
        $lp = (int)$start_lp;
        if ($lp >= 0 && $lp <= 20) return '0-20 LP';
        if ($lp >= 21 && $lp <= 40) return '21-40 LP';
        if ($lp >= 41 && $lp <= 60) return '41-60 LP';
        if ($lp >= 61 && $lp <= 80) return '61-80 LP';
        if ($lp >= 81 && $lp <= 100) return '81-100 LP';
        // fallback for unexpected values
        return $lp . ' LP';
    }
}


if (!function_exists('util_is_lol_account_login_form')) {
    function util_is_lol_account_login_form($formId): bool {
        return in_array((int)$formId, [1, 2, 3, 4, 9, 17, 18, 19, 20, 26], true);
    }
}

if (!function_exists('util_is_valorant_account_login_form')) {
    function util_is_valorant_account_login_form($formId): bool {
        return in_array((int)$formId, [5, 6, 7, 8], true);
    }
}

if (!function_exists('util_is_tft_account_login_form')) {
    function util_is_tft_account_login_form($formId): bool {
        return in_array((int)$formId, [21, 22, 23, 24], true);
    }
}

if (!function_exists('util_is_coaching_account_form')) {
    function util_is_coaching_account_form($formId): bool {
        return in_array((int)$formId, [15, 16, 25], true);
    }
}

if (!function_exists('util_get_account_login_game_label')) {
    function util_get_account_login_game_label($formId, $game = null): string {
        $formId = (int)$formId;
        $game = strtolower((string)$game);

        if (util_is_valorant_account_login_form($formId) || $formId === 16 || $game === 'val') {
            return 'VAL';
        }

        if (util_is_tft_account_login_form($formId) || $formId === 25 || $game === 'tft') {
            return 'TFT';
        }

        return 'Account';
    }
}

if (!function_exists('util_is_riot_only_order_account')) {
    function util_is_riot_only_order_account(array $data): bool {
        $formId = (int)($data['form_id'] ?? 0);
        $isDuo = !empty($data['is_duo']);
        return util_is_coaching_account_form($formId) || $isDuo;
    }
}

$allowedEmails = [
    'r.machmueller@gmx.de',
    'justsromail@freenet.de',
    'hbilalshah@gmail.com',
    'duck_sauce@live.de',
    'lovely@lolboost.gg'
];
?>

<?php
if (!function_exists('lb_duo_timer_meta')) {
    function lb_duo_timer_meta(array $data): ?array {
        if ((int)($data['form_id'] ?? 0) !== 27) {
            return null;
        }

        $hours = (int)($data['hours'] ?? 0);
        if ($hours <= 0) {
            return null;
        }

        $booked = max(0, $hours * 3600);
        $spent = max(0, (int)($data['duo_timer_spent_seconds'] ?? 0));
        $status = strtoupper((string)($data['status'] ?? ''));
        $isPaused = (int)($data['is_paused'] ?? 0) === 1;
        $startedAt = trim((string)($data['duo_timer_started_at'] ?? ''));

        // Manual-start mode for Duo Pass:
        // timer starts ONLY when booster clicks Start Timer.
        if ($status === 'IN_PROGRESS' && !$isPaused && $startedAt !== '' && $startedAt !== '0000-00-00 00:00:00') {
            $ts = strtotime($startedAt);
            if ($ts !== false) {
                $spent += max(0, time() - $ts);
            }
        }

        $used = min($booked, max(0, $spent));
        $remaining = max(0, $booked - $used);
        $progress = $booked > 0 ? (int)round(($used / $booked) * 100) : 0;
        $isStarted = (($startedAt !== '' && $startedAt !== '0000-00-00 00:00:00') || $spent > 0);

        $state = 'Not Started';
        if ($status === 'COMPLETED') {
            $state = 'Finished';
        } elseif ($status === 'IN_PROGRESS' && $isPaused && $isStarted) {
            $state = 'Paused';
        } elseif ($status === 'IN_PROGRESS' && $isStarted) {
            $state = 'Running';
        } elseif ($status === 'IN_PROGRESS') {
            $state = 'Ready to Start';
        }

        return [
            'booked_seconds' => $booked,
            'used_seconds' => $used,
            'remaining_seconds' => $remaining,
            'progress_percent' => max(0, min(100, $progress)),
            'status_label' => $state,
            'is_running' => ($status === 'IN_PROGRESS' && !$isPaused && $isStarted),
            'is_paused' => $isPaused,
            'is_started' => $isStarted,
        ];
    }
}
if (!function_exists('lb_duo_timer_human')) {
    function lb_duo_timer_human(int $seconds): string {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;
        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $minutes);
        }
        if ($minutes > 0) {
            return sprintf('%dm %02ds', $minutes, $secs);
        }
        return sprintf('%ds', $secs);
    }
}
$lb_duo_timer = lb_duo_timer_meta($data);
?>

<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Orders #' . $data['id'] . ' - Admin Area | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/css/star-rating.min.css" media="all"
    rel="stylesheet" type="text/css" />

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme CSS files as mentioned below (and change the theme property of the plugin) -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-svg/theme.css" media="all"
    rel="stylesheet" type="text/css" />
<style>
/* =========================
   WRAP + CARDS
========================= */.admin-order-view .order-page-wrap{
    padding: 1rem;
  }@media (min-width:992px){.admin-order-view .order-page-wrap{
      padding: 1.75rem;
    }
  }.admin-order-view .card{
    border-radius: 1rem;
    overflow: visible;
  }/* wichtig für dropdowns */.admin-order-view .card-header{
    padding: .8rem 1rem;
  }.admin-order-view .card-body, .admin-order-view .card-footer{
    padding: .85rem 1rem;
  }/* Only cards that should clip */.admin-order-view .booster-intro-card, .admin-order-view .waiting-banner, .admin-order-view .order-chat-card{
    overflow: hidden;
  }.admin-order-view .card-header-title{
    font-size: .9rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 0;
  }/* =========================
   PAGE HEADER (premium)
========================= */.admin-order-view .page-header{
    padding: 1.4rem 1.6rem;
    margin-bottom: 1.4rem;
    border-radius: 1rem;
    background: transparent;
    border: none;
    box-shadow: none;
    position: relative;
  }.admin-order-view .page-header-top{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }.admin-order-view .page-header-left{
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    min-width: 0;
  }.admin-order-view .page-header-title-wrap{
    min-width: 0;
  }.admin-order-view .page-header-title{
    font-size: 1.25rem;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
  }.admin-order-view .page-header-actions .btn{
    border-radius: 999px;
  }@media (min-width:992px){.admin-order-view .page-header-actions{
      position: absolute;
      top: 1.35rem;
      right: 1.6rem;
      z-index: 5;
    }.admin-order-view .page-header-top{
      padding-right: 12rem;
    }
  }@media (max-width:767.98px){.admin-order-view .page-header{
      padding: 1.15rem 1rem;
      margin-bottom: 1.1rem;
    }.admin-order-view .page-header-title{
      font-size: .8rem;
    }
  }/* Meta chips */.admin-order-view .page-header-meta{
    display: flex;
    flex-wrap: wrap;
    gap: .75rem 1rem;
    margin-top: .85rem;
  }.admin-order-view .page-header-meta .meta-item{
    display: flex;
    flex-direction: column;
    gap: .15rem;
    padding: .55rem .7rem;
    border-radius: .9rem;
    border: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .04);
    min-width: 170px;
  }.admin-order-view [data-theme="light"] .page-header-meta .meta-item{
    border-color: rgba(0, 0, 0, .06);
    background: rgba(0, 0, 0, .03);
  }.admin-order-view .page-header-meta .meta-label{
    font-size: .70rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    opacity: .75;
  }.admin-order-view .page-header-meta .meta-value{
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.2;
  }@media (max-width:767.98px){.admin-order-view .page-header-meta{
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .65rem;
    }.admin-order-view .page-header-meta .meta-item{
      min-width: 0;
    }
  }@media (max-width:420px){.admin-order-view .page-header-meta{
      grid-template-columns: 1fr;
    }
  }/* Status pill (alt – falls du es noch nutzt) */.admin-order-view .order-status-pill{
    --c: #4ea1ff;
    --bg: rgba(78, 161, 255, .10);
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .34rem .78rem .34rem .62rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .70rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
    color: var(--c);
    background: var(--bg);
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 10px 30px rgba(0, 0, 0, .30), inset 0 1px 0 rgba(255, 255, 255, .05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
  }.admin-order-view .order-status-pill::before{
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: var(--c);
    opacity: .95;
  }/* Premium warning hint */.admin-order-view .lb-hint{
    background: rgba(255, 107, 107, .10);
    border: 1px solid rgba(255, 107, 107, .20);
    border-radius: 1rem;
    padding: .85rem 1rem;
    display: flex;
    gap: .75rem;
    align-items: flex-start;
  }.admin-order-view .lb-hint i{
    margin-top: .15rem;
  }/* =========================
   WAITING / CLAIMED BANNER
========================= */.admin-order-view .waiting-banner{
    border-radius: 1rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view [data-theme="light"] .waiting-banner{
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }.admin-order-view .waiting-banner .card-body{
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .85rem 1.15rem;
  }.admin-order-view .waiting-avatar-wrapper{
    width: 44px;
    height: 44px;
    position: relative;
  }.admin-order-view .waiting-avatar{
    width: 44px;
    height: 44px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .35);
    box-shadow: 0 0 0 6px rgba(78, 161, 255, .06);
  }.admin-order-view .waiting-pulse-ring{
    position: absolute;
    inset: -6px;
    border-radius: 999px;
    border: 2px solid rgba(78, 161, 255, .25);
    animation: waitingPulse 1.6s ease-in-out infinite;
  }@keyframes waitingPulse{0%{
      transform: scale(.85);
      opacity: .35;
    }60%{
      transform: scale(1.10);
      opacity: .10;
    }100%{
      transform: scale(1.18);
      opacity: 0;
    }
  }.admin-order-view .waiting-title{
    font-weight: 800;
    font-size: .95rem;
  }.admin-order-view .waiting-sub{
    font-size: .78rem;
    opacity: .80;
  }/* =========================
   BOOSTER INTRO (v3 clean)
   (REPLACE your current Booster Intro CSS with this block)
========================= */.admin-order-view .booster-intro-card{/* keep cover clipped */
    border-radius: 1.25rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
    
  }.admin-order-view [data-theme="light"] .booster-intro-card{
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }.admin-order-view .booster-intro-bg{
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: .12;
    pointer-events: none;
  }/* BODY */.admin-order-view .booster-intro-body{
    position: relative;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: .9rem;
  }/* TOP ROW */.admin-order-view .booster-intro-top{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-width: 0;
  }.admin-order-view .booster-intro-left{
    display: flex;
    align-items: center;
    gap: .9rem;
    min-width: 0;
  }.admin-order-view .booster-intro-avatar{
    width: 68px;
    height: 68px;
    border-radius: 999px;
    overflow: hidden;
    flex: 0 0 auto;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 14px 45px rgba(0, 0, 0, .45);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view .booster-intro-avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }.admin-order-view .booster-intro-glow{
    position: absolute;
    inset: -8px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(78, 161, 255, .28), transparent 65%);
    filter: blur(6px);
    z-index: -1;
  }.admin-order-view .booster-intro-main{
    min-width: 0;
  }.admin-order-view .booster-intro-name{
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 950;
    font-size: 1.10rem;
    line-height: 1.1;
    min-width: 0;
  }.admin-order-view .booster-intro-name span{
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }/* Rank pill under name */.admin-order-view .booster-rank-pill{
    margin-top: .45rem;
    padding-right: .45rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .55rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(0, 0, 0, .18);
    font-weight: 900;
    font-size: .80rem;
    opacity: .95;
    vertical-align: middle;
    line-height: 1;
  }.admin-order-view [data-theme="light"] .booster-rank-pill{
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .10);
  }.admin-order-view .booster-rank-pill img{
    width: 22px;
    height: 22px;
    border-radius: 8px;
  }
  .admin-order-view .booster-rank-pill i,
  .admin-order-view .booster-rank-pill svg{
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }/* RIGHT */.admin-order-view .booster-intro-right{
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }/* View Profile Button (keep your lolboost style) */.admin-order-view .visit-profile-btn{
    display: inline-flex;
    align-items: center;
    gap: .45rem;

    padding: .38rem .75rem;
    border-radius: 999px;

    font-weight: 950;
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    text-decoration: none;
    white-space: nowrap;

    color: #4ea1ff;
    background: rgba(78, 161, 255, .12);
    border: 1px solid rgba(78, 161, 255, .25);

    box-shadow: 0 10px 30px rgba(0, 0, 0, .22);
    transition: .15s ease;
  }.admin-order-view .visit-profile-btn i{
    font-size: .95rem;
    opacity: .95;
  }.admin-order-view .visit-profile-btn:hover{
    transform: translateY(-1px);
    background: rgba(78, 161, 255, .18);
    border-color: rgba(78, 161, 255, .35);
    color: #8fc2ff;
  }.admin-order-view [data-theme="light"] .visit-profile-btn{
    color: #0d6efd;
    background: rgba(13, 110, 253, .10);
    border-color: rgba(13, 110, 253, .22);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
  }.admin-order-view [data-theme="light"] .visit-profile-btn:hover{
    background: rgba(13, 110, 253, .14);
    border-color: rgba(13, 110, 253, .30);
    color: #0b5ed7;
  }/* BOTTOM 3 CARDS */.admin-order-view .booster-intro-cards{
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
  }.admin-order-view .booster-intro-block{
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(0, 0, 0, .14);
    border-radius: 14px;
    padding: .75rem .8rem;
    min-width: 0;
  }.admin-order-view [data-theme="light"] .booster-intro-block{
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .08);
  }.admin-order-view .booster-intro-label{
    margin: 0 0 .55rem 0;
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .72;
    font-weight: 900;
  }.admin-order-view .booster-intro-champs, .admin-order-view .booster-intro-roles, .admin-order-view .booster-intro-langs{
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap;
  }/* Champs */.admin-order-view .booster-intro-champs .champ{
    width: 28px;
    height: 28px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
  }.admin-order-view .booster-intro-champs .more{
    padding: .14rem .5rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .78rem;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .05);
  }/* Roles */.admin-order-view .role-pill{
    width: 30px;
    height: 30px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
  }.admin-order-view .role-pill img{
    width: 26px;
    height: 26px;
    object-fit: contain;
  }.admin-order-view .booster-intro-tag{
    padding: .34rem .65rem;
    border-radius: 999px;
    font-weight: 800;
    font-size: .78rem;
    line-height: 1;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .05);
  }.admin-order-view .booster-intro-rank-mini{
    display: flex;
    align-items: center;
    gap: .45rem;
    flex-wrap: wrap;
    font-weight: 900;
  }.admin-order-view .booster-intro-rank-mini .rank-mini-icon{
    width: 30px;
    height: 30px;
    object-fit: contain;
  }/* Languages */.admin-order-view .booster-intro-langs .flag{
    width: 28px;
    height: 28px;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
  }.admin-order-view .na{
    opacity: .7;
    font-weight: 800;
  }/* Responsive */@media (max-width: 991.98px){.admin-order-view .booster-intro-cards{
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }@media (max-width: 575.98px){.admin-order-view .booster-intro-top{
      align-items: flex-start;
    }.admin-order-view .booster-intro-avatar{
      width: 60px;
      height: 60px;
    }.admin-order-view .booster-intro-name{
      font-size: 1.02rem;
    }.admin-order-view .booster-intro-cards{
      grid-template-columns: 1fr;
    }
  }/* =========================
   MOBILE: View Profile oben rechts,
   Name + Rank eine Zeile drunter links
========================= */@media (max-width:575.98px){/* Top area als Grid: Row 1 = Avatar + Button, Row 2 = Name/Rank */.admin-order-view .booster-intro-top{
      display: grid !important;
      grid-template-columns: auto 1fr;
      grid-template-areas:
        "av btn"
        "main main";
      align-items: start !important;
      gap: .6rem .75rem;
    }/* Damit Avatar + Main direkt im Grid landen */.admin-order-view .booster-intro-left{
      display: contents !important;
    }.admin-order-view .booster-intro-avatar{
      grid-area: av;
      align-self: start;
    }.admin-order-view .booster-intro-right{
      grid-area: btn;
      justify-self: end;
      align-self: start;
    }/* Name + Rank komplett in die 2. Zeile */.admin-order-view .booster-intro-main{
      grid-area: main;
      margin-top: .15rem;
      min-width: 0;
    }/* Optional: etwas kompakter */.admin-order-view .booster-intro-name{
      font-size: 1.02rem;
    }.admin-order-view .booster-rank-pill{
      margin-top: .4rem;
    }
  }@media (max-width:575.98px){/* Rank größer auf Mobile */.admin-order-view .booster-rank-pill{/* vorher kleiner -> jetzt größer */
      font-size: 1rem !important;
      
      padding: .40rem .72rem !important;
      font-weight: 950 !important;
      line-height: 1.1 !important;
    }/* optional: Icon im Pill (falls vorhanden) auch leicht größer */.admin-order-view .booster-rank-pill i, .admin-order-view .booster-rank-pill svg{
      transform: scale(1.08);
      transform-origin: center;
    }
  }/* =========================
   CHAT (admin-like)
========================= */.admin-order-view .order-chat-card .chat-bg{
    background: #1e2022;
    border-radius: 0;
  }.admin-order-view [data-theme="light"] .order-chat-card .chat-bg{
    background: #F9FAFC;
  }.admin-order-view #chat_messages{
    height: clamp(340px, 55vh, 520px);
    min-height: 340px;
    overflow: auto;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scrollbar-width: thin;
    scrollbar-color: #3a4254 #25282a;
  }.admin-order-view #chat_messages::-webkit-scrollbar{
    width: 6px;
  }.admin-order-view #chat_messages::-webkit-scrollbar-track{
    background: transparent;
    border-radius: 999px;
  }.admin-order-view #chat_messages::-webkit-scrollbar-thumb{
    background: rgba(255, 255, 255, .14);
    border-radius: 999px;
  }.admin-order-view [data-theme="light"] #chat_messages::-webkit-scrollbar-thumb{
    background: rgba(0, 0, 0, .18);
  }

  /* Match dashboard scrollbar inside Edit Order modal */
  .admin-order-view #edit_order_md .modal-body{
    scrollbar-width: thin;
    scrollbar-color: #3a4254 transparent;
  }
  .admin-order-view #edit_order_md .modal-body::-webkit-scrollbar{
    width: 6px;
  }
  .admin-order-view #edit_order_md .modal-body::-webkit-scrollbar-track{
    background: transparent;
    border-radius: 999px;
  }
  .admin-order-view #edit_order_md .modal-body::-webkit-scrollbar-thumb{
    background: rgba(255, 255, 255, .14);
    border-radius: 999px;
  }
  .admin-order-view [data-theme="light"] #edit_order_md .modal-body::-webkit-scrollbar-thumb{
    background: rgba(0, 0, 0, .18);
  }
.admin-order-view .lb-msg{
    display: flex;
    flex-direction: column;
    max-width: 82%;
  }.admin-order-view .lb-msg--start{
    align-self: flex-start;
  }.admin-order-view .lb-msg--end{
    align-self: flex-end;
  }.admin-order-view .lb-msg__head{
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    opacity: .95;
  }.admin-order-view .lb-msg__head--end{
    flex-direction: row-reverse;
    text-align: right;
  }.admin-order-view .lb-msg__avatar{
    width: 36px;
    height: 36px;
    border-radius: 999px;
    object-fit: cover;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 10px 25px rgba(0, 0, 0, .35);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view [data-theme="light"] .lb-msg__avatar{
    border-color: rgba(0, 0, 0, .10);
    box-shadow: 0 10px 25px rgba(0, 0, 0, .10);
  }.admin-order-view .lb-msg__meta{
    flex: 1;
    min-width: 0;
    line-height: 1.1;
  }.admin-order-view .lb-msg__toprow{
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
  }.admin-order-view .lb-msg__name{
    font-weight: 900;
    font-size: .92rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }.admin-order-view .lb-msg__time{
    margin-left: auto;
    font-size: .74rem;
    color: rgba(255, 255, 255, .55);
    white-space: nowrap;
  }.admin-order-view [data-theme="light"] .lb-msg__time{
    color: rgba(0, 0, 0, .55);
  }@media (max-width:575.98px){.admin-order-view .lb-msg__toprow{
      flex-wrap: wrap;
      gap: 6px 10px;
    }.admin-order-view .lb-msg__time{
      margin-left: 0;
    }
  }.admin-order-view .lb-badge{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: .70rem;
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-left: 8px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
  }.admin-order-view [data-theme="light"] .lb-badge{
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .04);
  }.admin-order-view .lb-badge--admin{
    color: #ff6b6b;
    background: rgba(255, 107, 107, .10);
  }.admin-order-view .lb-badge--booster{
    color: #1fe6c6;
    background: rgba(31, 230, 198, .10);
  }.admin-order-view .lb-badge--customer{
    color: #4ea1ff;
    background: rgba(78, 161, 255, .10);
  }.admin-order-view .lb-badge--system{
    color: #b18cff;
    background: rgba(177, 140, 255, .10);
  }.admin-order-view .lb-msg__bubble{
    position: relative;
    padding: 12px 14px;
    border-radius: 14px;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .35);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    word-break: break-word;
    overflow-wrap: anywhere;
  }.admin-order-view [data-theme="light"] .lb-msg__bubble{
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
  }.admin-order-view .lb-msg--end .lb-msg__bubble{
    background: rgba(78, 161, 255, .10);
    border-color: rgba(78, 161, 255, .18);
  }.admin-order-view [data-theme="light"] .lb-msg--end .lb-msg__bubble{
    background: rgba(78, 161, 255, .10);
    border-color: rgba(78, 161, 255, .25);
  }

/* Deleted message bubble (Admin placeholder) */
.admin-order-view .lb-msg__bubble--deleted{
  background: rgba(255,255,255,.04) !important;
  border: 1px dashed rgba(255,255,255,.16) !important;
  box-shadow: none !important;
}
.admin-order-view .lb-msg__bubble--deleted{
  color: rgba(255,255,255,.70);
  font-style: italic;
}
.admin-order-view [data-theme="light"] .lb-msg__bubble--deleted{
  background: rgba(0,0,0,.03) !important;
  border-color: rgba(0,0,0,.16) !important;
  color: rgba(0,0,0,.65);
}

/* Admin delete message (hover) */
.admin-order-view .lb-msg__del{
  position:absolute;
  top:8px;
  right:8px;
  width:28px;
  height:28px;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.10);
  background:rgba(0,0,0,.25);
  color:rgba(255,255,255,.85);
  display:flex;
  align-items:center;
  justify-content:center;
  opacity:0;
  pointer-events:none;
  transition:.12s ease;
}
.admin-order-view .lb-msg--end .lb-msg__del{ left:8px; right:auto; }
.admin-order-view .lb-msg:hover .lb-msg__del{
  opacity:1;
  pointer-events:auto;
}
.admin-order-view .lb-msg__del:hover{
  background:rgba(255,107,107,.18);
  border-color:rgba(255,107,107,.35);
  color:#ffb3b3;
  transform: translateY(-1px);
}
.admin-order-view .lb-msg__del:focus{ outline:none; box-shadow:0 0 0 3px rgba(124,92,255,.18); }

/* Timestamp under every bubble (exact time) */.admin-order-view .lb-msg__stamp{
    margin-top: 6px;
    font-size: .72rem;
    opacity: .60;
    line-height: 1.1;
    padding: 0 2px;
  }.admin-order-view .lb-msg--end .lb-msg__stamp{
    text-align: right;
  }.admin-order-view .lb-msg--start .lb-msg__stamp{
    text-align: left;
  }/* System message full width */.admin-order-view .lb-syswrap{
    width: 100%;
    align-self: stretch;
  }.admin-order-view .lb-sys{
    width: 100%;
    max-width: 100%;
    padding: 10px 14px;
    border-radius: 14px;
    border: 1px dashed rgba(177, 140, 255, .35);
    background: rgba(177, 140, 255, .10);
    font-weight: 800;
    font-size: .86rem;
  }.admin-order-view .lb-sys-time{
    margin-top: 6px;
    font-size: .75rem;
    opacity: .65;
    color: rgba(255, 255, 255, .55);
  }.admin-order-view [data-theme="light"] .lb-sys-time{
    color: rgba(0, 0, 0, .55);
  }/* =========================
   MODALS (lb-modal)
========================= */.admin-order-view .modal-backdrop.show{
    opacity: .78 !important;
  }.admin-order-view .lb-modal .modal-dialog{
    max-width: 620px;
  }.admin-order-view .lb-modal .modal-lg{
    max-width: 800px;
  }@media (max-width:575.98px){.admin-order-view .lb-modal .modal-dialog{
      max-width: calc(100% - 1.5rem);
    }
  }/* Service Guide modal sizing
   - Desktop: compact like Add-on modal, no scroll needed (content scales down if needed)
   - Mobile: full-screen and uses iOS-safe viewport height (no scroll needed)
*/.admin-order-view :root{
    --lb-vh: 1vh;
  }.admin-order-view #serviceGuideModal .modal-content{
    display: flex;
    flex-direction: column;
  }.admin-order-view #serviceGuideModal .modal-body{
    flex: 1 1 auto;
    min-height: 0;
  }.admin-order-view #serviceGuideModal .modal-footer{
    flex: 0 0 auto;
  }/* Mobile full height (fixes iPhone Safari 100vh issues) */@media (max-width: 767.98px){.admin-order-view #serviceGuideModal .modal-dialog{
      width: 100vw;
      max-width: 100vw;
      height: calc(var(--lb-vh, 1vh) * 100);
      margin: 0;
    }.admin-order-view #serviceGuideModal .modal-content{
      height: calc(var(--lb-vh, 1vh) * 100) !important;
      border-radius: 0 !important;
      overflow: hidden;
    }.admin-order-view #serviceGuideModal .modal-header{
      padding: .95rem 1rem !important;
    }.admin-order-view #serviceGuideModal .modal-body{
      padding: .95rem 1rem .65rem !important;
      overflow-y: hidden;
    }.admin-order-view #serviceGuideModal .modal-footer{
      padding: .75rem 1rem calc(.85rem + env(safe-area-inset-bottom)) !important;
    }/* Make content fit without scrolling on iPhone */.admin-order-view #serviceGuideModal .sg-pill{
      font-size: .75rem;
      padding: .3rem .6rem;
    }.admin-order-view #serviceGuideModal .sg-subtitle{
      font-size: .9rem;
      line-height: 1.25;
    }.admin-order-view #serviceGuideModal .sg-guideline{
      padding: .75rem .9rem !important;
      margin-bottom: .6rem !important;
      border-radius: 14px;
    }.admin-order-view #serviceGuideModal .sg-num{
      width: 34px !important;
      height: 34px !important;
      font-size: .95rem !important;
    }.admin-order-view #serviceGuideModal .sg-text{
      font-size: .92rem !important;
      line-height: 1.24;
    }.admin-order-view #serviceGuideModal .sg-alert{
      padding: .75rem .9rem !important;
      margin-bottom: .7rem !important;
      border-radius: 14px;
    }.admin-order-view #serviceGuideModal .btn-service-guide{
      padding: .78rem 1rem !important;
      font-size: 1rem !important;
    }
  }/* Desktop: cap size similar to Add-on modal and ensure no scroll */@media (min-width: 768px){.admin-order-view #serviceGuideModal .modal-dialog{
      width: min(740px, calc(100vw - 2.5rem));
      max-width: 740px;
      margin: 1.25rem auto;
      max-height: min(760px, calc(var(--lb-vh, 1vh) * 100 - 5rem));
    }.admin-order-view #serviceGuideModal .modal-content{
      max-height: inherit;
      overflow: hidden;
    }.admin-order-view #serviceGuideModal .modal-body{
      overflow-y: hidden;
    }
  }/* Compact levels (applied by JS when needed) */.admin-order-view #serviceGuideModal.sg-compact-1 .modal-header{
    padding: .9rem 1rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .modal-body{
    padding: .85rem 1rem .55rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .modal-footer{
    padding: .75rem 1rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .sg-subtitle{
    font-size: .9rem;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .sg-guideline{
    padding: .75rem .95rem !important;
    margin-bottom: .65rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .sg-num{
    width: 34px !important;
    height: 34px !important;
    font-size: .95rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .sg-text{
    font-size: .92rem;
    line-height: 1.22;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .sg-alert{
    padding: .75rem .95rem !important;
    margin-bottom: .75rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-1 .btn-service-guide{
    padding: .75rem 1rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .modal-header{
    padding: .8rem .95rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .modal-body{
    padding: .75rem .95rem .5rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .modal-footer{
    padding: .7rem .95rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-pill{
    font-size: .72rem;
    padding: .25rem .55rem;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-subtitle{
    font-size: .85rem;
    line-height: 1.2;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-guideline{
    padding: .65rem .85rem !important;
    margin-bottom: .55rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-num{
    width: 30px !important;
    height: 30px !important;
    font-size: .9rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-text{
    font-size: .88rem;
    line-height: 1.18;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .sg-alert{
    padding: .65rem .85rem !important;
    margin-bottom: .65rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-2 .btn-service-guide{
    padding: .68rem 1rem !important;
    font-size: .98rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .modal-header{
    padding: .7rem .9rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .modal-body{
    padding: .65rem .9rem .45rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .modal-footer{
    padding: .65rem .9rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .sg-subtitle{
    font-size: .82rem;
    line-height: 1.15;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .sg-guideline{
    padding: .55rem .75rem !important;
    margin-bottom: .45rem !important;
    border-radius: 12px;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .sg-num{
    width: 26px !important;
    height: 26px !important;
    font-size: .85rem !important;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .sg-text{
    font-size: .84rem;
    line-height: 1.12;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .sg-alert{
    padding: .55rem .75rem !important;
    margin-bottom: .55rem !important;
    border-radius: 12px;
  }.admin-order-view #serviceGuideModal.sg-compact-3 .btn-service-guide{
    padding: .62rem 1rem !important;
    font-size: .95rem !important;
  }/* Fallback if viewport is extremely small */.admin-order-view #serviceGuideModal.sg-allow-scroll .modal-body{
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
  }.admin-order-view .lb-modal .modal-content{
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }.admin-order-view .lb-modal .modal-header{
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal .modal-footer{
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
  }.admin-order-view .lb-modal .modal-body{
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal .lb-modal-title{
    font-weight: 900;
    font-size: 1.05rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .55rem;
  }.admin-order-view .lb-modal .lb-modal-sub{
    margin: .2rem 0 0;
    font-size: .9rem;
    opacity: .7;
  }.admin-order-view .lb-modal .lb-field-title{
    font-weight: 900;
    margin-bottom: .55rem;
  }.admin-order-view .lb-modal textarea.form-control{
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: inherit;
  }.admin-order-view .lb-modal textarea.form-control::placeholder{
    opacity: .55;
  }/* Notes modals */.admin-order-view .lb-modal--note .modal-dialog{
    max-width: 720px;
  }@media (max-width:575.98px){.admin-order-view .lb-modal--note .modal-dialog{
      max-width: calc(100% - 1.5rem);
    }
  }.admin-order-view .lb-modal--note .lb-textarea{
    min-height: 160px;
    resize: vertical;
  }.admin-order-view .lb-modal--note .lb-helper{
    margin-top: .6rem;
    font-size: .85rem;
    opacity: .7;
  }/* Riot modal */.admin-order-view .lb-modal--riot .modal-dialog{
    max-width: 560px;
  }@media (max-width:575.98px){.admin-order-view .lb-modal--riot .modal-dialog{
      max-width: calc(100% - 1.5rem);
    }
  }.admin-order-view .lb-modal--riot .lb-field{
    margin-bottom: .9rem;
  }.admin-order-view .lb-modal--riot .lb-field-label{
    display: block;
    font-weight: 900;
    font-size: .85rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .45rem;
    opacity: .85;
  }.admin-order-view .lb-modal--riot .lb-input{
    min-height: 46px;
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    text-align: center;
  }.admin-order-view .lb-modal--riot .lb-input::placeholder{
    opacity: .55;
  }.admin-order-view .lb-modal--riot .lb-input:focus{
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15);
  }/* =========================
   LB MODAL HEADER (shared)
========================= */.admin-order-view .lb-modal .modal-header{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }.admin-order-view .lb-modal .lb-modal-head{
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    min-width: 0;
  }.admin-order-view .lb-modal .lb-modal-ico{
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    flex: 0 0 auto;
  }.admin-order-view .lb-modal .lb-modal-ico--tip{
    background: rgba(88, 101, 242, .14);
    border-color: rgba(88, 101, 242, .26);
    color: #cfd5ff;
  }.admin-order-view .lb-modal .lb-modal-headtxt{
    min-width: 0;
  }.admin-order-view .lb-modal .lb-modal-title{
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }.admin-order-view .lb-modal .lb-modal-sub{
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
    line-height: 1.35;
  }/* custom close button */.admin-order-view .lb-modal .lb-modal-x{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
    transition: .15s ease;
    flex: 0 0 auto;
  }.admin-order-view .lb-modal .lb-modal-x:hover{
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }/* Light theme */.admin-order-view [data-theme="light"] .lb-modal .lb-modal-x{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .70);
  }.admin-order-view [data-theme="light"] .lb-modal .lb-modal-x:hover{
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .14);
    color: rgba(0, 0, 0, .85);
  }/* =========================
   TOMSELECT / SELECT DARK
========================= */.admin-order-view .ts-wrapper{
    width: 100%;
  }.admin-order-view .ts-control{
    background: rgba(255, 255, 255, .04) !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 1rem !important;
    min-height: 46px;
    padding: .55rem .9rem !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
  }.admin-order-view .ts-control input, .admin-order-view .ts-control .item{
    color: rgba(255, 255, 255, .92) !important;
  }.admin-order-view .ts-control::after{
    border-color: rgba(255, 255, 255, .55) transparent transparent transparent !important;
  }.admin-order-view .ts-dropdown{
    background: #1f2226 !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 1rem !important;
    overflow: hidden;
    box-shadow: 0 18px 60px rgba(0, 0, 0, .55);
    z-index: 9999 !important;
  }.admin-order-view .ts-dropdown .option{
    color: rgba(255, 255, 255, .88) !important;
    padding: .65rem .9rem;
  }.admin-order-view .ts-dropdown .option.active, .admin-order-view .ts-dropdown .option:hover{
    background: rgba(255, 255, 255, .06) !important;
    color: rgba(255, 255, 255, .98) !important;
  }.admin-order-view .ts-dropdown .create{
    color: rgba(255, 255, 255, .75) !important;
  }.admin-order-view [data-theme="light"] .ts-control{
    background: #fff !important;
    border-color: rgba(0, 0, 0, .10) !important;
  }.admin-order-view [data-theme="light"] .ts-dropdown{
    background: #fff !important;
    border-color: rgba(0, 0, 0, .10) !important;
  }/* keep large height, but normal font */.admin-order-view .form-control-lg, .admin-order-view .form-select-lg, .admin-order-view .ts-control{
    font-size: 1rem !important;
  }/* =========================
   TIP MODAL (restore premium CSS)
========================= */.admin-order-view #send_tip_md .lb-tip-grid{
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    margin: .55rem 0 1rem;
  }.admin-order-view #send_tip_md .lb-tip-chip{
    border-radius: 999px;
    padding: .42rem .85rem;
    font-weight: 900;
    font-size: .9rem;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
    box-shadow: 0 10px 30px rgba(0, 0, 0, .22);
    transition: .15s ease;
  }.admin-order-view #send_tip_md .lb-tip-chip:hover{
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .18);
    color: #fff;
  }.admin-order-view #send_tip_md .lb-tip-chip.is-active{
    background: rgba(88, 101, 242, .22);
    border-color: rgba(88, 101, 242, .35);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .28);
    color: #fff;
  }.admin-order-view #send_tip_md .lb-tip-amount{
    display: grid;
    grid-template-columns: 46px 1fr 46px;
    gap: .6rem;
    align-items: center;
    margin-bottom: 1rem;
  }.admin-order-view #send_tip_md .lb-tip-amount .btn{
    width: 46px;
    height: 46px;
    border-radius: 14px;
    font-weight: 950;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
    transition: .15s ease;
  }.admin-order-view #send_tip_md .lb-tip-amount .btn:hover{
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .18);
    color: #fff;
  }.admin-order-view #send_tip_md #tip-amount{
    height: 46px;
    border-radius: 14px;
    text-align: center;
    font-weight: 900;
    letter-spacing: .02em;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view #send_tip_md #tip-amount:focus{
    border-color: rgba(88, 101, 242, .35);
    box-shadow: 0 0 0 .25rem rgba(88, 101, 242, .15);
    outline: 0;
  }.admin-order-view #send_tip_md textarea.form-control{
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view #send_tip_md textarea.form-control::placeholder{
    opacity: .55;
  }/* Light theme */.admin-order-view [data-theme="light"] #send_tip_md .lb-tip-chip{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .82);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
  }.admin-order-view [data-theme="light"] #send_tip_md .lb-tip-chip:hover{
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .14);
    color: rgba(0, 0, 0, .88);
  }.admin-order-view [data-theme="light"] #send_tip_md .lb-tip-chip.is-active{
    background: rgba(88, 101, 242, .14);
    border-color: rgba(88, 101, 242, .28);
    color: rgba(0, 0, 0, .90);
  }.admin-order-view [data-theme="light"] #send_tip_md .lb-tip-amount .btn{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }.admin-order-view [data-theme="light"] #send_tip_md #tip-amount{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .85);
  }.admin-order-view #send_tip_md .lb-btn{
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view #send_tip_md .lb-btn:hover{
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }.admin-order-view #send_tip_md .lb-btn-ghost{
    background: transparent;
  }.admin-order-view #send_tip_md .lb-btn-success{
    background: rgba(88, 101, 242, .22);
    border-color: rgba(88, 101, 242, .35);
    color: #fff;
  }.admin-order-view #send_tip_md .lb-btn-success:hover{
    background: rgba(88, 101, 242, .32);
    border-color: rgba(88, 101, 242, .45);
  }/* =========================
   OPTIONS (segmented + select)
========================= */.admin-order-view .card .lb-options-form{
    margin-top: .25rem;
  }.admin-order-view .lb-opt-row{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: .85rem 0;
  }.admin-order-view .lb-opt-row+.lb-opt-row{
    border-top: 1px solid rgba(255, 255, 255, .06);
  }.admin-order-view .lb-opt-left{
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    min-width: 0;
  }.admin-order-view .lb-opt-ico{
    width: 44px;
    height: 44px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    flex: 0 0 auto;
    font-size: 1.05rem;
  }.admin-order-view .lb-opt-ico-img{
    width: 22px;
    height: 22px;
    display: block;
    object-fit: contain;
  }.admin-order-view .lb-opt-text{
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }.admin-order-view .lb-opt-label{
    font-weight: 900;
    font-size: 1rem;
    white-space: nowrap;
  }.admin-order-view .lb-opt-sub{
    font-size: .86rem;
    opacity: .85;
    margin-top: 0;
  }
  .admin-order-view .lb-opt-sub .lb-opt-val{
    font-weight: 800;
    opacity: .95;
  }.admin-order-view .lb-opt-right{
    flex: 0 0 auto;
  }.admin-order-view .lb-opt-right--w{
    width: 320px;
  }@media (max-width:575.98px){.admin-order-view .lb-opt-right--w{
      width: 100%;
    }
  }.admin-order-view .lb-seg{
    width: 100%;
    display: flex;
    padding: 6px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    gap: 6px;
  }.admin-order-view .lb-seg-btn{
    flex: 1 1 auto;
    border: 0;
    padding: .55rem .8rem;
    border-radius: 999px;
    font-weight: 900;
    background: transparent;
    color: rgba(255, 255, 255, .80);
    transition: .15s ease;
    white-space: nowrap;
  }.admin-order-view .lb-seg-btn:hover{
    background: rgba(255, 255, 255, .06);
    color: #fff;
  }.admin-order-view .lb-seg-btn.is-active{
    background: rgba(78, 161, 255, .22);
    color: #fff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
  }.admin-order-view .lb-opt-select .form-select{
    width: 100%;
    min-height: 48px;
    border-radius: 999px;
    background-color: rgba(255, 255, 255, .04) !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    color: rgba(255, 255, 255, .90) !important;
  }.admin-order-view .lb-opt-select .form-select:focus{
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15) !important;
    border-color: rgba(177, 140, 255, .35) !important;
  }.admin-order-view .lb-opt-actions{
    display: flex;
    justify-content: flex-start;
    margin-top: 1rem;
  }.admin-order-view .lb-opt-save{
    border-radius: 14px;
    font-weight: 900;
  }@media (max-width:575.98px){.admin-order-view .lb-opt-actions{
      justify-content: stretch;
    }.admin-order-view .lb-opt-save{
      width: 100%;
    }
  }/* Responsive stacking */@media (max-width:767.98px){.admin-order-view .lb-opt-row{
      flex-direction: column;
      align-items: stretch;
      gap: .75rem;
    }.admin-order-view .lb-opt-left{
      width: 100%;
    }
  }/* Desktop fix */@media (min-width:992px){.admin-order-view .lb-opt-sub--desktop-hide{
      display: none !important;
    }.admin-order-view .lb-opt-right--w{
      width: 200px;
    }.admin-order-view .lb-seg{
      padding: 4px;
      gap: 4px;
    }.admin-order-view .lb-seg-btn{
      padding: .42rem .70rem;
      font-size: .88rem;
      line-height: 1.1;
    }.admin-order-view .lb-opt-label{
      font-size: .95rem;
    }/* VPN width = 200px */.admin-order-view .lb-opt-select{
      min-width: 0 !important;
    }.admin-order-view .lb-opt-right--w.lb-opt-select{
      width: 200px !important;
    }.admin-order-view .lb-opt-right--w.lb-opt-select .ts-wrapper, .admin-order-view .lb-opt-right--w.lb-opt-select .ts-control{
      width: 100% !important;
    }
  }/* Static options (Priority/Bonus/Hidden Duo): always one row */.admin-order-view .lb-opt-static .lb-opt-row--static{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .70rem 0;
  }.admin-order-view .lb-opt-static .lb-opt-row--static .lb-opt-left{
    min-width: 0;
    flex: 1 1 auto;
  }.admin-order-view .lb-opt-static .lb-opt-row--static .lb-opt-label{
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }.admin-order-view .lb-opt-static .lb-opt-row--static .lb-opt-right{
    flex: 0 0 auto;
    margin-left: auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    white-space: nowrap;
  }/* override global mobile stacking */@media (max-width:767.98px){.admin-order-view .lb-opt-static .lb-opt-row--static{
      flex-direction: row !important;
      align-items: center !important;
    }.admin-order-view .lb-opt-static .lb-opt-row--static .lb-opt-left{
      width: auto !important;
    }
  }/* value pill */.admin-order-view .lb-opt-pill{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .28rem .70rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    font-weight: 900;
    font-size: .78rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
  }.admin-order-view .lb-opt-dot{
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .35);
    box-shadow: 0 0 0 4px rgba(255, 255, 255, .06);
  }.admin-order-view .lb-opt-pill.is-yes{
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }.admin-order-view .lb-opt-pill.is-yes .lb-opt-dot{
    background: #1fe6c6;
    box-shadow: 0 0 0 4px rgba(31, 230, 198, .08);
  }.admin-order-view .lb-opt-pill.is-no{
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .22);
    background: rgba(255, 107, 107, .10);
  }.admin-order-view .lb-opt-pill.is-no .lb-opt-dot{
    background: #ff6b6b;
    box-shadow: 0 0 0 4px rgba(255, 107, 107, .08);
  }.admin-order-view .lb-opt-pill.is-neutral{
    color: rgba(255, 255, 255, .85);
  }
/* --- Options rows like Overview items --- */
.admin-order-view .lb-opt-row{
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.06);
  border-radius: 18px;
  padding: .95rem 1rem;
}
.admin-order-view .lb-opt-row:hover{
  background: rgba(255,255,255,.045);
}
.admin-order-view .lb-opt-row+.lb-opt-row{
  border-top: none;
  margin-top: .75rem;
}
.admin-order-view .lb-opt-text .lb-opt-label{
  font-weight: 700;
  letter-spacing: .2px;
}
.admin-order-view .lb-opt-val{
  color: rgba(255,255,255,.82);
  font-weight: 600;
}
@media (max-width: 768px){
  .admin-order-view .lb-opt-row{
    flex-direction: column;
    align-items: flex-start;
    gap: .65rem;
  }
  .admin-order-view .lb-opt-right{
    width: 100%;
    display: flex;
    justify-content: flex-start;
  }
  .admin-order-view .lb-opt-right .lb-opt-val{
    width: 100%;
  }
}

/* =========================
   NOTES LIST
========================= */.admin-order-view .lb-notes-list{
    display: flex;
    flex-direction: column;
    gap: .7rem;
  }.admin-order-view .lb-note-item{
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .85rem .95rem;
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    transition: .15s ease;
  }.admin-order-view .lb-note-item:hover{
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .045);
    border-color: rgba(255, 255, 255, .12);
  }.admin-order-view .lb-note-ico{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .18);
    color: rgba(255, 255, 255, .9);
  }.admin-order-view .lb-note-content{
    min-width: 0;
    flex: 1;
  }.admin-order-view .lb-note-text{
    font-weight: 800;
    font-size: .95rem;
    line-height: 1.35;
    color: rgba(255, 255, 255, .92);
    word-break: break-word;
    overflow-wrap: anywhere;
  }.admin-order-view .lb-note-meta{
    margin-top: .45rem;
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    font-size: .72rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .65;
  }.admin-order-view .lb-note-chip{
    padding: .18rem .5rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view .lb-note-actions{
    display: flex;
    gap: .45rem;
    flex: 0 0 auto;
  }.admin-order-view .lb-note-action{
    width: 38px;
    height: 38px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .9);
    transition: .15s ease;
  }.admin-order-view .lb-note-action:hover{
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .16);
    transform: translateY(-1px);
  }.admin-order-view .lb-note-action--danger{
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .22);
    background: rgba(255, 107, 107, .08);
  }.admin-order-view .lb-note-action--danger:hover{
    background: rgba(255, 107, 107, .12);
    border-color: rgba(255, 107, 107, .30);
  }.admin-order-view .lb-notes-empty{
    padding: 1.35rem 1rem;
    border-radius: 1rem;
    border: 1px dashed rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .02);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .55rem;
  }.admin-order-view .lb-notes-empty-ico{
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .18);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .35);
  }.admin-order-view .lb-notes-empty-title{
    font-weight: 900;
    font-size: 1.05rem;
  }.admin-order-view .lb-notes-empty-sub{
    opacity: .72;
    max-width: 52ch;
    font-size: .9rem;
  }@media (max-width:575.98px){.admin-order-view .lb-note-item{
      flex-direction: column;
    }.admin-order-view .lb-note-actions{
      width: 100%;
      justify-content: flex-end;
    }.admin-order-view .lb-note-btn{
      width: 100%;
      display: flex;
      justify-content: center;
    }
  }.admin-order-view [data-theme="light"] .lb-note-item{
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .08);
  }.admin-order-view [data-theme="light"] .lb-note-item:hover{
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .12);
  }.admin-order-view [data-theme="light"] .lb-note-text{
    color: rgba(0, 0, 0, .85);
  }.admin-order-view [data-theme="light"] .lb-note-ico, .admin-order-view [data-theme="light"] .lb-notes-empty-ico{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }.admin-order-view [data-theme="light"] .lb-note-action{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }.admin-order-view [data-theme="light"] .lb-notes-empty{
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .12);
  }/* =========================
   OVERVIEW (stacked list)
========================= */.admin-order-view .lb-overview-card .card-body{
    padding: .75rem .85rem;
  }@media (max-width:575.98px){.admin-order-view .lb-overview-card .card-body{
      padding: .70rem .80rem;
    }
  }.admin-order-view .lb-ov-grid{
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr !important;
    gap: .55rem;
  }.admin-order-view .lb-ov-item{
    display: grid;
    grid-template-columns: 44px 1fr;
    grid-template-rows: auto auto;
    align-items: start;
    column-gap: .75rem;
    row-gap: .20rem;
    padding: .62rem .75rem;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    min-width: 0;
    transition: .12s ease;
  }.admin-order-view .lb-ov-item:hover{
    background: rgba(255, 255, 255, .04);
    border-color: rgba(255, 255, 255, .12);
    transform: translateY(-1px);
  }.admin-order-view .lb-ov-ico{
    width: 44px;
    height: 44px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    font-size: 1.05rem;
    line-height: 1;
    grid-row: 1 / span 2;
  }.admin-order-view .lb-ov-label{
    grid-column: 2;
    grid-row: 1;
    font-weight: 900;
    font-size: .95rem;
    line-height: 1.15;
    white-space: normal;
    min-width: 0;
  }.admin-order-view .lb-ov-value{
    grid-column: 2;
    grid-row: 2;
    font-weight: 900;
    font-size: .90rem;
    opacity: .78;
    line-height: 1.2;
    white-space: normal;
    overflow-wrap: anywhere;
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
  }@media (max-width:575.98px){.admin-order-view .lb-ov-item{
      padding: .58rem .68rem;
    }.admin-order-view .lb-ov-ico{
      width: 42px;
      height: 42px;
      border-radius: 15px;
    }
  }.admin-order-view .lb-ov-pill{
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .22rem .55rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    font-weight: 900;
    font-size: .72rem;
    letter-spacing: .06em;
    text-transform: uppercase;
  }.admin-order-view .lb-ov-pill--no{
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .20);
    background: rgba(255, 107, 107, .10);
  }.admin-order-view .lb-ov-pill--yes{
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }/* =========================
   ORDER HEADER (lb-head)
========================= */.admin-order-view .lb-head.card{/* dropdown */
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, .07);
    background: rgba(255, 255, 255, .03);
      
    position: relative;
  }.admin-order-view .lb-head .dropdown-menu{
    z-index: 1060;
  }/* Dropdown-Caret/Haken weg (Header + Aktionen) */.admin-order-view .lb-head .dropdown-toggle::after, .admin-order-view @media (max-width:767.98px){.admin-order-view .lb-actions-btn.dropdown-toggle::after{
      display: none !important;
    }
  }.admin-order-view .lb-head__top{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1rem .85rem 1rem;
  }.admin-order-view .lb-head__left{
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    min-width: 0;
    flex: 1 1 auto;
  }.admin-order-view .lb-head__icon{
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    flex: 0 0 auto;
  }.admin-order-view .lb-head__icon i{
    font-size: 1.35rem;
    opacity: .95;
  }
  .lb-head__icon img{
    width: 1.35rem;
    height: 1.35rem;
    display: block;
  }
.admin-order-view .lb-head__title{
    min-width: 0;
  }.admin-order-view .lb-head__title-row{
    display: flex;
    align-items: baseline;
    gap: .6rem;
    min-width: 0;
  }.admin-order-view .lb-head__h1{
    margin: 0;
    font-weight: 950;
    font-size: 1.15rem;
    line-height: 1.2;
    letter-spacing: .01em;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }.admin-order-view .lb-head__id{
    font-weight: 900;
    font-size: .85rem;
    opacity: .55;
    white-space: nowrap;
  }.admin-order-view .lb-head__sub{
    margin-top: .45rem;
  }.admin-order-view .lb-head__actions{
    flex: 0 0 auto;
    display: flex;
    align-items: flex-start;
  }.admin-order-view .lb-head__meta{
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    padding: .85rem 1rem 1rem 1rem;
    border-top: 1px solid rgba(255, 255, 255, .06);
  }.admin-order-view .lb-meta-pill{
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .55rem .75rem;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    min-width: 180px;
    max-width: 100%;
  }.admin-order-view .lb-meta-pill__k{
    font-weight: 950;
    font-size: .70rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .55;
    white-space: nowrap;
  }.admin-order-view .lb-meta-pill__v{
    font-weight: 900;
    font-size: .92rem;
    opacity: .90;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }/* =========================
   MOBILE: Meta-Pills IMMER 2-zeilig (Label oben, Value unten)
========================= */@media (max-width: 575.98px){/* Pill selbst: untereinander statt nebeneinander */.admin-order-view .lb-meta-pill{
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: .25rem !important;
      min-width: 0 !important;
    }/* Label immer eigene Zeile */.admin-order-view .lb-meta-pill__k{
      display: block !important;
      width: 100% !important;
      white-space: nowrap !important;
      line-height: 1.05 !important;
    }/* Value immer darunter: NICHT abschneiden */.admin-order-view .lb-meta-pill__v{/* wrap erlauben *//* nicht verstecken *//* kein ... */
      display: block !important;
      width: 100% !important;

      white-space: normal !important;
      
          
      text-overflow: unset !important;
      
      line-height: 1.15 !important;
      word-break: break-word !important;
      overflow-wrap: anywhere !important;
    }
  }/* Status (lb-status) */.admin-order-view .lb-status{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .34rem .70rem;
    border-radius: 999px;
    font-weight: 950;
    font-size: .72rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .85);
  }.admin-order-view .lb-status__dot{
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: .95;
  }/* Status colors */.admin-order-view .lb-status.status-inprogress{
    color: #4ea1ff;
    border-color: rgba(78, 161, 255, .25);
    background: rgba(78, 161, 255, .12);
  }.admin-order-view .lb-status.status-completed{
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }.admin-order-view .lb-status.status-paused{
    color: #ffc44d;
    border-color: rgba(255, 196, 77, .22);
    background: rgba(255, 196, 77, .10);
  }.admin-order-view .lb-status.status-unpaid{
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .20);
    background: rgba(255, 107, 107, .10);
  }.admin-order-view .lb-status.status-paid{
    color: #b18cff;
    border-color: rgba(177, 140, 255, .22);
    background: rgba(177, 140, 255, .10);
  }.admin-order-view .lb-status.status-refund{
    color: #ff8a4c;
    border-color: rgba(255, 138, 76, .22);
    background: rgba(255, 138, 76, .10);
  }
  .admin-order-view .lb-status.status-processing{
    color: #9aa4b2;
    border-color: rgba(154, 164, 178, .20);
    background: rgba(154, 164, 178, .08);
  }.admin-order-view .lb-status.status-inprogress .lb-status__dot{
    background: #4ea1ff;
  }.admin-order-view .lb-status.status-completed .lb-status__dot{
    background: #1fe6c6;
  }.admin-order-view .lb-status.status-paused .lb-status__dot{
    background: #ffc44d;
  }.admin-order-view .lb-status.status-unpaid .lb-status__dot{
    background: #ff6b6b;
  }.admin-order-view .lb-status.status-paid .lb-status__dot{
    background: #b18cff;
  }.admin-order-view .lb-status.status-refund .lb-status__dot{
    background: #ff8a4c;
  }
  .admin-order-view .lb-status.status-processing .lb-status__dot{
    background: #9aa4b2;
  }/* Mobile: title clamp + ID unter title */@media (max-width:575.98px){.admin-order-view .lb-head__top{
      padding: .85rem .85rem .70rem .85rem;
    }.admin-order-view .lb-head__meta{
      padding: .70rem .85rem .85rem .85rem;
    }.admin-order-view .lb-head__icon{
      width: 44px;
      height: 44px;
      border-radius: 13px;
    }.admin-order-view .lb-head__title-row{
      flex-wrap: wrap !important;
      align-items: flex-start !important;
    }.admin-order-view .lb-head__id{
      width: 100% !important;
      margin-top: .15rem;
    }.admin-order-view .lb-head__h1{/* wichtig für clamp */
      font-size: 1.05rem;
      white-space: normal !important;
      display: -webkit-box !important;
      -webkit-box-orient: vertical !important;
      -webkit-line-clamp: 3 !important;
      overflow: hidden !important;
      
      text-overflow: ellipsis !important;
    }.admin-order-view .lb-meta-pill{
      min-width: 0;
      flex: 1 1 calc(50% - .55rem);
    }.admin-order-view .lb-meta-pill__v{
      font-size: .90rem;
    }
  }@media (min-width:768px){.admin-order-view .lb-meta-pill{
      min-width: 210px;
    }
  }/* =========================
   REVIEW CARD (final)
========================= */.admin-order-view .lb-review-card{
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
  }.admin-order-view [data-theme="light"] .lb-review-card{
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }.admin-order-view .lb-review-card .card-body{
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-review-body{
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }.admin-order-view .lb-review-top{
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;
  }.admin-order-view .lb-review-avatar{
    width: 56px;
    height: 56px;
    border-radius: 999px;
    overflow: hidden;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .45);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view .lb-review-avatar img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }.admin-order-view .lb-review-text{
    min-width: 0;
    flex: 1;
  }.admin-order-view .lb-review-pillrow{
    margin-bottom: .45rem;
  }.admin-order-view .lb-pill{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .34rem .75rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
  }.admin-order-view .lb-pill--success{
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .25);
    background: rgba(31, 230, 198, .10);
  }.admin-order-view .lb-dot{
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: currentColor;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, .08);
}.admin-order-view .lb-pill--yes{
  color: #1fe6c6;
  border-color: rgba(31, 230, 198, .25);
  background: rgba(31, 230, 198, .10);
}.admin-order-view .lb-pill--no{
  color: #ff7a8c;
  border-color: rgba(255, 122, 140, .25);
  background: rgba(255, 122, 140, .10);
}.admin-order-view .lb-opt-val{
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: .5rem;
  font-weight: 900;
  color: rgba(255, 255, 255, .88);
}
}.admin-order-view .lb-review-title{
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.2;
    margin: 0;
  }.admin-order-view .lb-review-name{
    color: #1fe6c6;
    text-shadow: 0 1px 0 rgba(0, 0, 0, .25);
  }.admin-order-view .lb-review-sub{
    margin-top: .25rem;
    opacity: .72;
    font-size: .92rem;
  }.admin-order-view .lb-review-tip{
    flex: 0 0 auto;
  }.admin-order-view .lb-review-tip .btn{
    border-radius: 999px;
    font-weight: 900;
  }/* bottom centered */.admin-order-view .lb-review-bottom{
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .6rem;
    padding-top: .85rem;
    border-top: 1px solid rgba(255, 255, 255, .06);
  }.admin-order-view .lb-review-stars-label{
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: .78rem;
    opacity: .75;
    text-align: center;
  }.admin-order-view .lb-review-stars{
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    flex-wrap: wrap;
  }.admin-order-view .lb-star{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    display: grid;
    place-items: center;
    transition: .15s ease;
    padding: 0;
  }.admin-order-view .lb-star:hover{
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .05);
    border-color: rgba(255, 255, 255, .16);
  }.admin-order-view .lb-star svg{
    width: 26px;
    height: 26px;
  }.admin-order-view .lb-star svg path{
    fill: transparent;
    stroke: rgba(31, 230, 198, .60);
    stroke-width: 2;
    transition: fill .12s ease, stroke .12s ease, filter .12s ease;
  }.admin-order-view .lb-star.is-on svg path{
    fill: rgba(31, 230, 198, 1);
    stroke: rgba(31, 230, 198, 1);
    filter: drop-shadow(0 8px 18px rgba(31, 230, 198, .22));
  }@media (max-width:575.98px){.admin-order-view .lb-review-top{
      flex-wrap: wrap;
      align-items: flex-start;
    }.admin-order-view .lb-review-tip{
      width: 100%;
    }.admin-order-view .lb-review-tip .btn{
      width: 100%;
      justify-content: center;
    }.admin-order-view .lb-review-title{
      font-size: .85rem;
    }.admin-order-view .lb-review-sub{
      font-size: .75rem;
    }
  }/* =========================
   SPECIFIC MODALS (scoped)
========================= *//* Change Booster Modal */.admin-order-view #client_change_booster_md .lbx-modal__content{
    background: #25282A !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255, 255, 255, .92) !important;
  }.admin-order-view #client_change_booster_md .lbx-modal__header{
    padding: 16px 16px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view #client_change_booster_md .lbx-modal__headLeft{
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }.admin-order-view #client_change_booster_md .lbx-modal__icon{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
  }.admin-order-view #client_change_booster_md .lbx-modal__title{
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.15;
  }.admin-order-view #client_change_booster_md .lbx-modal__sub{
    margin-top: 4px;
    opacity: .72;
    font-size: .9rem;
  }.admin-order-view #client_change_booster_md .lbx-modal__close{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
  }.admin-order-view #client_change_booster_md .lbx-modal__close:hover{
    background: rgba(255, 255, 255, .07);
  }.admin-order-view #client_change_booster_md .lbx-modal__body{
    padding: 16px;
  }.admin-order-view #client_change_booster_md .lbx-modal__label{
    font-size: .75rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    font-weight: 900;
    opacity: .7;
    margin-bottom: 10px;
  }.admin-order-view #client_change_booster_md .lbx-modal__control{
    width: 100%;
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    outline: none;
  }.admin-order-view #client_change_booster_md .lbx-modal__help{
    margin-top: 10px;
    font-size: .88rem;
    opacity: .70;
  }.admin-order-view #client_change_booster_md .lbx-modal__footer{
    padding: 14px 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .02);
  }.admin-order-view #client_change_booster_md .lbx-modal__btn{
    border-radius: 999px;
    padding: .55rem 1rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view #client_change_booster_md .lbx-modal__btn:hover{
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
  }.admin-order-view #client_change_booster_md .lbx-modal__btn--ghost{
    background: transparent;
  }.admin-order-view #client_change_booster_md .lbx-modal__btn--action{
    background: rgba(255, 255, 255, .08);
  }/* Account Logins Modal */.admin-order-view #account_logins_md .lbx-modal__content{
    background: #25282A !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255, 255, 255, .92) !important;
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
  }.admin-order-view #account_logins_md .lbx-modal__header{
    padding: 16px 16px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view #account_logins_md .lbx-modal__headLeft{
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }.admin-order-view #account_logins_md .lbx-modal__icon{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
  }.admin-order-view #account_logins_md .lbx-modal__title{
    font-weight: 900;
    font-size: 1.25rem;
    line-height: 1.15;
  }.admin-order-view #account_logins_md .lbx-modal__sub{
    margin-top: 6px;
    opacity: .72;
    font-size: .92rem;
  }.admin-order-view #account_logins_md .lbx-modal__close{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
  }.admin-order-view #account_logins_md .lbx-modal__close:hover{
    background: rgba(255, 255, 255, .07);
  }.admin-order-view #account_logins_md .lbx-modal__body{
    padding: 16px;
  }.admin-order-view #account_logins_md .lb-field{
    margin-bottom: 14px;
  }.admin-order-view #account_logins_md .lb-field-label{
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 900;
    font-size: .85rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    opacity: .85;
    margin-bottom: 8px;
  }.admin-order-view #account_logins_md .lb-field-label .lb-ico{
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
  }.admin-order-view #account_logins_md .lb-input{
    width: 100%;
    min-height: 48px;
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    outline: none;
  }.admin-order-view #account_logins_md .lb-input::placeholder{
    opacity: .55;
  }.admin-order-view #account_logins_md .lb-input:focus{
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15);
  }.admin-order-view #account_logins_md .lb-row{
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }@media(min-width:992px){.admin-order-view #account_logins_md .modal-dialog{
      max-width: 720px;
    }.admin-order-view #account_logins_md .lb-row.lb-row--2{
      grid-template-columns: 1fr 1fr;
    }
  }.admin-order-view #account_logins_md .lbx-modal__footer{
    padding: 14px 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .02);
  }.admin-order-view #account_logins_md .lbx-modal__btn{
    border-radius: 999px;
    padding: .60rem 1.05rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view #account_logins_md .lbx-modal__btn:hover{
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
  }.admin-order-view #account_logins_md .lbx-modal__btn--ghost{
    background: transparent;
  }.admin-order-view #account_logins_md .lbx-modal__btn--primary{
    background: rgba(88, 101, 242, .85);
    border-color: rgba(88, 101, 242, .35);
  }.admin-order-view #account_logins_md .lbx-modal__btn--primary:hover{
    background: rgba(88, 101, 242, .95);
  }/* Trigger button */.admin-order-view .lb-acc-trigger{
    border-radius: 999px !important;
    padding: .55rem .95rem !important;
    font-weight: 900;
    background: rgba(255, 255, 255, .04) !important;
    border-color: rgba(255, 255, 255, .10) !important;
    color: rgba(255, 255, 255, .92) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
  }.admin-order-view .lb-acc-trigger:hover{
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06) !important;
    border-color: rgba(255, 255, 255, .16) !important;
    color: #fff !important;
  }@media (max-width:575.98px){.admin-order-view .lb-acc-trigger{
      padding: .55rem .75rem !important;
    }
  }/* Account summary */.admin-order-view .lb-acc-summary{
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .95rem 1rem;
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }.admin-order-view .lb-acc-summary__icon{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    flex: 0 0 auto;
  }.admin-order-view .lb-acc-summary__text{
    min-width: 0;
    flex: 1;
  }.admin-order-view .lb-acc-summary__title{
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: .78rem;
    opacity: .85;
  }.admin-order-view .lb-acc-summary__sub{
    margin-top: .25rem;
    font-size: .92rem;
    opacity: .72;
  }.admin-order-view .lb-acc-summary__badge{
    padding: .35rem .7rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .72rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    flex: 0 0 auto;
  }

/* Account details (show Riot ID / Login / Password in card) */
.admin-order-view .lb-acc-details{
  display: grid;
  gap: .55rem;
  margin-top: .85rem;
}
.admin-order-view .lb-acc-row{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: .65rem .8rem;
  border-radius: .95rem;
  border: 1px solid rgba(255, 255, 255, .08);
  background: rgba(255, 255, 255, .02);
}
.admin-order-view [data-theme="light"] .lb-acc-row{
  border-color: rgba(0,0,0,.08);
  background: rgba(0,0,0,.02);
}
.admin-order-view .lb-acc-label{
  font-size: .70rem;
  text-transform: uppercase;
  letter-spacing: .10em;
  font-weight: 900;
  opacity: .70;
  white-space: nowrap;
}
.admin-order-view .lb-acc-value{
  font-size: .95rem;
  font-weight: 750;
  text-align: right;
  word-break: break-word;
}
.admin-order-view .lb-acc-actions{
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: .55rem;
  min-width: 0;
}
.admin-order-view .lb-acc-actions .lb-acc-value{
  cursor: pointer;
}
.admin-order-view .lb-acc-copy{
  width: 34px;
  height: 34px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .10);
  background: rgba(255, 255, 255, .04);
  color: rgba(255, 255, 255, .88);
  flex: 0 0 auto;
}
.admin-order-view .lb-acc-copy:hover{ background: rgba(255, 255, 255, .08); color: #fff; }
.admin-order-view .lb-acc-copy:disabled{ opacity: .45; cursor: not-allowed; }
.admin-order-view [data-theme="light"] .lb-acc-copy{
  border-color: rgba(0,0,0,.10);
  background: rgba(0,0,0,.04);
  color: rgba(0,0,0,.75);
}
.admin-order-view [data-theme="light"] .lb-acc-copy:hover{ background: rgba(0,0,0,.07); color: rgba(0,0,0,.90); }
.admin-order-view .lb-acc-value.is-missing{
  opacity: .55;
  font-weight: 650;
}

.admin-order-view .lb-duo-accounts-list{display:flex;flex-direction:column;gap:.65rem;}
.admin-order-view .lb-duo-account-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:.75rem;border:1px solid rgba(255,255,255,.08);border-radius:.85rem;background:rgba(255,255,255,.025);}
.admin-order-view [data-theme="light"] .lb-duo-account-row{border-color:rgba(0,0,0,.08);background:rgba(0,0,0,.025);}
.admin-order-view .lb-duo-account-main{min-width:0;}
.admin-order-view .lb-duo-account-booster{display:flex;align-items:center;gap:.45rem;font-weight:900;color:#fff;line-height:1.2;}
.admin-order-view [data-theme="light"] .lb-duo-account-booster{color:rgba(0,0,0,.88);}
.admin-order-view .lb-duo-account-riot{margin-top:.25rem;font-size:.82rem;color:rgba(255,255,255,.72);word-break:break-word;}
.admin-order-view [data-theme="light"] .lb-duo-account-riot{color:rgba(0,0,0,.62);}
.admin-order-view .lb-duo-account-meta{margin-top:.35rem;display:flex;align-items:center;flex-wrap:wrap;gap:.35rem;}
.admin-order-view .lb-duo-account-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .45rem;border-radius:999px;font-size:.68rem;font-weight:900;line-height:1;border:1px solid rgba(108,92,231,.35);background:rgba(108,92,231,.14);color:#c7bdff;white-space:nowrap;}
.admin-order-view .lb-duo-account-badge--active{border-color:rgba(0,184,148,.35);background:rgba(0,184,148,.14);color:#55efc4;}
.admin-order-view .lb-duo-account-badge--tracked{border-color:rgba(116,185,255,.35);background:rgba(116,185,255,.14);color:#a8d8ff;}
.admin-order-view .lb-duo-account-copy{flex:0 0 auto;width:2rem;height:2rem;border-radius:.7rem;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:rgba(255,255,255,.72);display:inline-flex;align-items:center;justify-content:center;}
.admin-order-view .lb-duo-account-copy:hover{background:rgba(255,255,255,.08);color:#fff;}
.admin-order-view .lb-duo-account-empty{padding:.85rem;border:1px dashed rgba(255,255,255,.12);border-radius:.85rem;color:rgba(255,255,255,.55);font-size:.82rem;}
.admin-order-view .lb-duo-accounts-card .card-body{padding:.85rem;}
.admin-order-view .lb-duo-accounts-open{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.8rem .9rem;border-radius:.85rem;border:1px solid rgba(108,92,231,.28);background:rgba(108,92,231,.10);color:#fff;font-weight:900;text-align:left;}
.admin-order-view .lb-duo-accounts-open:hover{background:rgba(108,92,231,.16);border-color:rgba(108,92,231,.42);color:#fff;}
.admin-order-view .lb-duo-accounts-open__left{display:flex;align-items:center;gap:.55rem;min-width:0;}
.admin-order-view .lb-duo-accounts-open__text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-order-view .lb-duo-accounts-open__count{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;min-width:1.65rem;height:1.35rem;padding:0 .45rem;border-radius:.5rem;background:rgba(255,255,255,.14);font-size:.75rem;font-weight:900;color:#fff;}
.admin-order-view .lb-duo-accounts-modal .modal-dialog{max-width:560px;}
.admin-order-view .lb-duo-accounts-modal .modal-body{max-height:min(70vh,620px);overflow-y:auto;}
.admin-order-view [data-theme="light"] .lb-duo-accounts-open{background:rgba(108,92,231,.08);color:rgba(0,0,0,.86);}
.admin-order-view [data-theme="light"] .lb-duo-accounts-open__count{background:rgba(0,0,0,.08);color:rgba(0,0,0,.75);} 
.admin-order-view .lb-acc-summary.is-saved{
    border-color: rgba(31, 230, 198, .20);
  }.admin-order-view .lb-acc-summary.is-saved .lb-acc-summary__badge{
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .25);
    background: rgba(31, 230, 198, .10);
  }.admin-order-view .lb-acc-summary.is-missing{
    border-color: rgba(255, 196, 77, .18);
  }.admin-order-view .lb-acc-summary.is-missing .lb-acc-summary__badge{
    color: #ffc44d;
    border-color: rgba(255, 196, 77, .22);
    background: rgba(255, 196, 77, .10);
  }@media (max-width:575.98px){.admin-order-view .lb-acc-summary{
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }/* Mobile: icon-only notify booster button */@media (max-width:575.98px){.admin-order-view .btn-notify-booster span{
      display: none;
    }.admin-order-view .btn-notify-booster{
      padding: .55rem .70rem;
      border-radius: 12px;
    }
  }/* =========================
   PAUSE MODAL (premium)
========================= */.admin-order-view .lb-modal--pause .modal-dialog{
    max-width: 560px;
  }.admin-order-view .lb-modal--pause .modal-content{
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }/* header */.admin-order-view .lb-modal--pause .modal-header{
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal--pause .lb-modal-head{
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }.admin-order-view .lb-modal--pause .lb-modal-ico{
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(255, 196, 77, .10);
    border: 1px solid rgba(255, 196, 77, .22);
    color: #ffc44d;
    flex: 0 0 auto;
  }.admin-order-view .lb-modal--pause .lb-modal-title{
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }.admin-order-view .lb-modal--pause .lb-modal-sub{
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
  }/* body */.admin-order-view .lb-modal--pause .modal-body{
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal--pause .lb-modal-warning{
    display: flex;
    gap: .85rem;
    align-items: flex-start;
    padding: .9rem 1rem;
    border-radius: 1rem;
    background: rgba(255, 196, 77, .10);
    border: 1px solid rgba(255, 196, 77, .20);
  }.admin-order-view .lb-modal--pause .lb-modal-warning i{
    margin-top: .1rem;
    color: #ffc44d;
    font-size: 1.1rem;
  }.admin-order-view .lb-modal--pause .lb-modal-warning-title{
    font-weight: 950;
    margin-bottom: .15rem;
  }.admin-order-view .lb-modal--pause .lb-modal-warning-sub{
    opacity: .8;
    font-size: .92rem;
    line-height: 1.35;
  }/* footer */.admin-order-view .lb-modal--pause .modal-footer{
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
    gap: .6rem;
  }/* buttons */.admin-order-view .lb-modal--pause .lb-btn{
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view .lb-modal--pause .lb-btn:hover{
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }.admin-order-view .lb-modal--pause .lb-btn-ghost{
    background: transparent;
  }.admin-order-view .lb-modal--pause .lb-btn-danger{
    background: rgba(255, 107, 107, .12);
    border-color: rgba(255, 107, 107, .25);
    color: #ffb1b1;
  }.admin-order-view .lb-modal--pause .lb-btn-danger:hover{
    background: rgba(255, 107, 107, .18);
    border-color: rgba(255, 107, 107, .35);
    color: #fff;
  }/* mobile */@media (max-width:575.98px){.admin-order-view .lb-modal--pause .modal-dialog{
      max-width: calc(100% - 1.25rem);
    }.admin-order-view .lb-modal--pause .modal-footer{
      flex-direction: column;
      align-items: stretch;
    }.admin-order-view .lb-modal--pause .lb-btn{
      width: 100%;
      justify-content: center;
    }
  }/* =========================
   RESUME MODAL (premium)
========================= */.admin-order-view .lb-modal--resume .modal-dialog{
    max-width: 560px;
  }.admin-order-view .lb-modal--resume .modal-content{
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }/* header */.admin-order-view .lb-modal--resume .modal-header{
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal--resume .lb-modal-head{
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }.admin-order-view .lb-modal--resume .lb-modal-ico{
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .22);
    color: #1fe6c6;
    flex: 0 0 auto;
  }.admin-order-view .lb-modal--resume .lb-modal-title{
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }.admin-order-view .lb-modal--resume .lb-modal-sub{
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
  }/* body */.admin-order-view .lb-modal--resume .modal-body{
    padding: 1rem 1.1rem;
  }.admin-order-view .lb-modal--resume .lb-modal-info{
    display: flex;
    gap: .85rem;
    align-items: flex-start;
    padding: .9rem 1rem;
    border-radius: 1rem;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .20);
  }.admin-order-view .lb-modal--resume .lb-modal-info i{
    margin-top: .1rem;
    color: #1fe6c6;
    font-size: 1.1rem;
  }.admin-order-view .lb-modal--resume .lb-modal-info-title{
    font-weight: 950;
    margin-bottom: .15rem;
  }.admin-order-view .lb-modal--resume .lb-modal-info-sub{
    opacity: .82;
    font-size: .92rem;
    line-height: 1.35;
  }/* footer */.admin-order-view .lb-modal--resume .modal-footer{
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
    gap: .6rem;
  }/* buttons */.admin-order-view .lb-modal--resume .lb-btn{
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }.admin-order-view .lb-modal--resume .lb-btn:hover{
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }.admin-order-view .lb-modal--resume .lb-btn-ghost{
    background: transparent;
  }.admin-order-view .lb-modal--resume .lb-btn-success{
    background: rgba(31, 230, 198, .12);
    border-color: rgba(31, 230, 198, .25);
    color: #bff7ee;
  }.admin-order-view .lb-modal--resume .lb-btn-success:hover{
    background: rgba(31, 230, 198, .18);
    border-color: rgba(31, 230, 198, .35);
    color: #fff;
  }/* mobile */@media (max-width:575.98px){.admin-order-view .lb-modal--resume .modal-dialog{
      max-width: calc(100% - 1.25rem);
    }.admin-order-view .lb-modal--resume .modal-footer{
      flex-direction: column;
      align-items: stretch;
    }.admin-order-view .lb-modal--resume .lb-btn{
      width: 100%;
      justify-content: center;
    }
  }.admin-order-view .payment-methods .payment-item{
    width: 100%;
    cursor: pointer;
    background: rgba(255, 255, 255, .03);
    border-radius: .75rem;
    transition: all .2s ease;
    border: 1px solid transparent;
  }/* Hover */.admin-order-view .payment-methods .payment-item:hover{
    background: rgba(255, 255, 255, .06);
  }/* Hide the radio but keep it accessible */.admin-order-view .payment-methods input[type="radio"]{
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }/* Selected state */.admin-order-view .payment-methods input[type="radio"]:checked+.content, .admin-order-view .payment-methods input[type="radio"]:checked~.badge{
    color: #fff;
  }.admin-order-view .payment-methods input[type="radio"]:checked~*{
    pointer-events: none;
  }.admin-order-view .payment-methods input[type="radio"]:checked{/* noop – intentionally empty */
    
  }/* Highlight the whole row when selected */.admin-order-view .payment-methods input[type="radio"]:checked~.content, .admin-order-view .payment-methods input[type="radio"]:checked~.badge{
    filter: none;
  }.admin-order-view .payment-methods input[type="radio"]:checked~*{
    filter: none;
  }.admin-order-view .payment-methods input[type="radio"]:checked~.badge{
    background: #0d6efd !important;
  }/* This is the important part */.admin-order-view .payment-methods label:has(input[type="radio"]:checked){
    background: rgba(13, 110, 253, .15);
    border-color: #0d6efd;
  }/* Rank + View Profile responsive layout */.admin-order-view .rank-visit-wrap{/* DESKTOP: untereinander */
    display: flex;
    flex-direction: column;
    
    align-items: center;
    gap: 8px;
  }/* MOBILE: nebeneinander */@media (max-width:575.98px){.admin-order-view .rank-visit-wrap{/* MOBILE: neben dem Rank-Icon */
      flex-direction: row;
      
      align-items: center;
      gap: 10px;
    }
  }/* Optional: sehr kleine Geräte -> darf umbrechen statt zu quetschen */@media (max-width:360px){.admin-order-view .rank-visit-wrap{
      flex-wrap: wrap;
    }
  }/* View Profile Button (LoLBoost style) */.admin-order-view .visit-profile-btn{
    display: inline-flex;
    align-items: center;
    gap: .45rem;

    padding: .38rem .75rem;
    border-radius: 999px;

    font-weight: 950;
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    text-decoration: none;
    white-space: nowrap;

    color: #4ea1ff;
    background: rgba(78, 161, 255, .12);
    border: 1px solid rgba(78, 161, 255, .25);

    box-shadow: 0 10px 30px rgba(0, 0, 0, .22);
    transition: .15s ease;
  }.admin-order-view .visit-profile-btn i{
    font-size: .95rem;
    opacity: .95;
  }.admin-order-view .visit-profile-btn:hover{
    transform: translateY(-1px);
    background: rgba(78, 161, 255, .18);
    border-color: rgba(78, 161, 255, .35);
    color: #8fc2ff;
  }/* Light theme */.admin-order-view [data-theme="light"] .visit-profile-btn{
    color: #0d6efd;
    background: rgba(13, 110, 253, .10);
    border-color: rgba(13, 110, 253, .22);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
  }.admin-order-view [data-theme="light"] .visit-profile-btn:hover{
    background: rgba(13, 110, 253, .14);
    border-color: rgba(13, 110, 253, .30);
    color: #0b5ed7;
  }/* =========================
   ORDER ACTIONS CARD (extra)
========================= */.admin-order-view .lb-actions-card{
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
  }.admin-order-view [data-theme="light"] .lb-actions-card{
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }.admin-order-view .lb-actions-list{
    display: flex;
    flex-direction: column;
  }.admin-order-view .lb-action-item{
    width: 100%;
    border: 0;
    background: transparent;
    color: inherit;
    text-align: left;
    padding: .85rem .95rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    border-top: 1px solid rgba(255, 255, 255, .06);
    transition: .15s ease;
  }.admin-order-view .lb-action-item:first-child{
    border-top: 0;
  }.admin-order-view .lb-action-item:hover{
    background: rgba(255, 255, 255, .05);
    transform: translateY(-1px);
  }.admin-order-view [data-theme="light"] .lb-action-item{
    border-top-color: rgba(0, 0, 0, .06);
  }.admin-order-view [data-theme="light"] .lb-action-item:hover{
    background: rgba(0, 0, 0, .03);
  }.admin-order-view .lb-action-ico{
    width: 44px;
    height: 44px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    flex: 0 0 auto;
    font-size: 1.05rem;
  }.admin-order-view [data-theme="light"] .lb-action-ico{
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
  }.admin-order-view .lb-action-txt{
    min-width: 0;
    flex: 1;
  }.admin-order-view .lb-action-title{
    display: block;
    font-weight: 950;
    font-size: .98rem;
    line-height: 1.15;
  }.admin-order-view .lb-action-sub{
    display: block;
    margin-top: .18rem;
    font-size: .82rem;
    opacity: .70;
  }.admin-order-view .lb-action-go{
    opacity: .55;
    flex: 0 0 auto;
    transition: .15s ease;
  }.admin-order-view .lb-action-item:hover .lb-action-go{
    opacity: .9;
    transform: translateX(1px);
  }.admin-order-view .lb-action-item--danger .lb-action-title{
    color: #ffb1b1;
  }.admin-order-view .lb-action-item--danger .lb-action-ico{
    background: rgba(255, 107, 107, .10);
    border-color: rgba(255, 107, 107, .22);
    color: #ffb1b1;
  }/* --- Desktop Emoji Picker (Order Chat) --- */.admin-order-view .lb-emoji-picker{
    position: absolute;
    right: 16px;
    bottom: 58px;
    z-index: 1075;
    background: rgba(33, 37, 41, 0.98);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 10px;
    width: 280px;
    max-width: calc(100vw - 32px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.55);
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }.admin-order-view .lb-emoji-picker .lb-emoji{
    background: transparent;
    border: 0;
    font-size: 22px;
    line-height: 1;
    padding: 6px;
    border-radius: 10px;
    cursor: pointer;
  }.admin-order-view .lb-emoji-picker .lb-emoji:hover{
    background: rgba(255, 255, 255, 0.06);
  }@media (max-width: 767.98px){.admin-order-view .lb-emoji-picker, .admin-order-view .lb-emoji-btn{
      display: none !important;
    }
  }.admin-order-view .star i{
    font-size: 20px;
  }.admin-order-view .filled-stars, .admin-order-view .empty-stars{
    display: flex;
    gap: 5px;
  }.admin-order-view .filled-stars .star, .admin-order-view .empty-stars .star{
    border: 1px solid #ffffff14;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    border-radius: 10px;
  }.admin-order-view .rating-container .filled-stars{
    -webkit-text-stroke: 0;
    text-shadow: none;
    color: #ffc44d;
  }.admin-order-view .rating-container .rating-stars:focus{
    outline: none;
  }.admin-order-view .highlights .rounded-pill{
    border-radius: 9999px;
    padding: 6px 12px;
    background: #ffffff0d;
    border: 1px solid #ffffff1a;
    color: #ffffffb3;
    white-space: nowrap;
  }.admin-order-view .highlights .rounded-pill, .admin-order-view .highlights .rounded-pill:hover, .admin-order-view .highlights .rounded-pill:focus, .admin-order-view .highlights .rounded-pill:focus-visible, .admin-order-view .highlights .rounded-pill:active, .admin-order-view .highlights .show>.rounded-pill{
    background: #ffffff0d !important;
    border-color: #ffffff1a !important;
    color: #ffffffb3 !important;
    box-shadow: none !important;
    outline: none !important;
  }.admin-order-view .highlights .rounded-pill:hover{
    background: #ffffff14 !important;
    border-color: #ffffff26 !important;
    color: #ffffffe6 !important;
  }.admin-order-view .highlights .rounded-pill.active, .admin-order-view .highlights .btn-check:checked+.rounded-pill, .admin-order-view .highlights .btn-check:active+.rounded-pill{
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    border-color: #6366f1 !important;
    color: #ffffff !important;
    box-shadow:
      0 0 0 1px #6366f1 inset,
      0 6px 16px rgba(99, 102, 241, 0.35),
      0 0 18px rgba(99, 102, 241, 0.45) !important;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.25);
  }.admin-order-view .highlights .rounded-pill{
    transition: all .18s ease;
  }.admin-order-view .btn-check:disabled+.btn, .admin-order-view .btn-check[disabled]+.btn{
    opacity: 1 !important;
    cursor: not-allowed !important;
  }

/* Stars / crown / pills (admin specific) */
@keyframes crownAnimation {0%{transform:scale(1);}50%{transform:scale(1.2);}100%{transform:scale(1);}}
.fad.fa-crown { animation: crownAnimation 1s infinite; }
.star i { font-size: 20px; }
.filled-stars, .empty-stars { display:flex; gap:5px; }
.filled-stars .star, .empty-stars .star {
  border: 1px solid #ffffff14;
  aspect-ratio: 1 / 1;
  display:flex; align-items:center; justify-content:center;
  padding:10px; border-radius:10px;
}
.rating-container .filled-stars { -webkit-text-stroke:0; text-shadow:none; color:#ffc44d; }
.rating-container .rating-stars:focus { outline:none; }

.admin-order-view .highlights .rounded-pill {
  border-radius: 9999px;
  padding: 6px 12px;
  background: #ffffff0d;
  border: 1px solid #ffffff1a;
  color: #ffffffb3;
  white-space: nowrap;
  transition: all .18s ease;
}
.admin-order-view .highlights .rounded-pill,
.admin-order-view .highlights .rounded-pill:hover,
.admin-order-view .highlights .rounded-pill:focus,
.admin-order-view .highlights .rounded-pill:focus-visible,
.admin-order-view .highlights .rounded-pill:active,
.admin-order-view .highlights .show>.rounded-pill {
  background: #ffffff0d !important;
  border-color: #ffffff1a !important;
  color: #ffffffb3 !important;
  box-shadow: none !important;
  outline: none !important;
}
.admin-order-view .highlights .rounded-pill:hover {
  background: #ffffff14 !important;
  border-color: #ffffff26 !important;
  color: #ffffffe6 !important;
}
.admin-order-view .highlights .rounded-pill.active,
.admin-order-view .highlights .btn-check:checked+.rounded-pill,
.admin-order-view .highlights .btn-check:active+.rounded-pill {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
  border-color: #6366f1 !important;
  color: #ffffff !important;
  box-shadow:
    0 0 0 1px #6366f1 inset,
    0 6px 16px rgba(99, 102, 241, 0.35),
    0 0 18px rgba(99, 102, 241, 0.45) !important;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.25);
}
.admin-order-view .btn-check:disabled+.btn,
.admin-order-view .btn-check[disabled]+.btn {
  opacity: 1 !important;
  cursor: not-allowed !important;
}
/* keep meta pill links clean */
.admin-order-view .lb-meta-pill__v a { color: inherit; text-decoration: none; }
.admin-order-view .lb-meta-pill__v a:hover { text-decoration: underline; }


/* =========================
   LEGACY chat renderer (temporary)
   - keeps existing admin chat bubbles readable until we switch JS to lb-msg like client
========================= */
.admin-order-view .chat-bg {
  background: #1e2022;
  min-height: 25rem;
}
.admin-order-view [data-theme="light"] .chat-bg {
  background: #F9FAFC;
  min-height: 15rem;
  overflow-y: scroll;
  max-height: 20rem;
}
.admin-order-view .chat .chat-message-body {
  background: #25282A;
  border-radius: 0.5rem;
  padding: 1rem;
  width: fit-content;
}
.admin-order-view [data-theme="light"] .chat .chat-message-body {
  background: #fff;
}
.admin-order-view .chat .chat-message { margin-bottom: 1rem; }
.admin-order-view .chat p { margin-bottom: 0; }
.admin-order-view .chat .chat-message.me {
  display:flex;
  flex-direction:column;
  align-items:end;
  text-align:end;
}
.admin-order-view .chat-message-meta .chat-message-meta-time{
  font-size:.75rem;
  color:#6c757d;
}



/* Notes (client-like) */
.admin-order-view .lb-notes-list{ display:flex; flex-direction:column; gap:.65rem; }
.admin-order-view .lb-note-item{
  display:flex;
  align-items:flex-start;
  gap:.75rem;
  padding:.8rem .85rem;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
}
.admin-order-view .lb-note-edit{
  width: 34px;
  height: 34px;
  border-radius: 12px;
  display:grid;
  place-items:center;
  background: rgba(0,0,0,.18);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.85);
  flex: 0 0 auto;
  text-decoration:none;
}
.admin-order-view .lb-note-edit:hover{ background: rgba(255,255,255,.06); color:#fff; }
.admin-order-view .lb-note-body{ min-width:0; flex:1 1 auto; }
.admin-order-view .lb-note-text{ font-weight:700; color: rgba(255,255,255,.90); }
.admin-order-view .lb-note-badge{
  flex:0 0 auto;
  margin-left:auto;
  padding:.35rem .6rem;
  border-radius: 999px;
  font-weight:900;
  font-size:.78rem;
  letter-spacing:.02em;
  background: rgba(78, 161, 255, .16);
  border: 1px solid rgba(78, 161, 255, .28);
  color: #bfe0ff;
  text-transform: capitalize;
}


/* =========================
   MOBILE POLISH (admin order view)
   - header title smaller
   - meta pills 2-column grid
   - options rows align nicer
   - notes look like client
========================= */
@media (max-width: 575.98px){
  .admin-order-view .lb-head__h1{ font-size: 1.0rem; }
  .admin-order-view .lb-head__meta{ gap: .5rem; padding: .75rem .85rem .9rem; }
  .admin-order-view .lb-meta-pill{ min-width: 0; flex: 1 1 calc(50% - .5rem); padding: .55rem .65rem; border-radius: 14px; }
  .admin-order-view .lb-meta-pill__k{ font-size: .66rem; }
  .admin-order-view .lb-meta-pill__v{ font-size: .82rem; }

  /* Options: keep icon+label row, values aligned under label start */
  .admin-order-view .lb-opt-row{ padding: .75rem 0; }
  .admin-order-view .lb-opt-label{ font-size: .95rem; white-space: normal; }
  .admin-order-view .lb-opt-right{ width: 100%; padding-left: calc(44px + .75rem); display:flex; justify-content:flex-start; }
  .admin-order-view .lb-opt-pill{ font-size: .78rem; padding: .32rem .55rem; }
  .admin-order-view .lb-opt-dot{ width: 8px; height: 8px; }
  .admin-order-view .lb-seg{ padding: 5px; }
  .admin-order-view .lb-seg-btn{ padding: .48rem .6rem; font-size: .85rem; }

  /* Notes list spacing */
  .admin-order-view .lb-notes-card .card-body{ padding: 1rem; }
  .admin-order-view .lb-note-item{ padding: .7rem .75rem; }
  .admin-order-view .lb-note-text{ font-size: .95rem; }
}


/* =========================
   ORDER ACTIONS (Desktop pill like Client)
========================= */
.admin-order-view .lb-order-actions-btn{
  display:inline-flex;
  align-items:center;
  gap:.55rem;
  padding:.55rem .9rem;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.10);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
}
.admin-order-view .lb-order-actions-btn:hover{
  background: rgba(255,255,255,.08);
}
.admin-order-view .lb-order-actions-btn__ico{
  width:28px;
  height:28px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  
}
.admin-order-view .lb-order-actions-btn__txt{
  font-weight:900;
  letter-spacing:.02em;
  font-size:.85rem;
}
.admin-order-view .lb-order-actions-btn__chev{
  opacity:.75;
  font-size:.85rem;
}
@media (max-width:991.98px){
  .admin-order-view .lb-order-actions-btn{ padding:.55rem .7rem; }
}

/* =========================
   PAUSED BANNER (fix icon + client-like)
========================= */
.admin-order-view .lb-banner{
  border-radius: 1rem;
  padding: .85rem 1rem;
  display:flex;
  gap:.85rem;
  align-items:flex-start;
}
.admin-order-view .lb-banner--paused{
  background: rgba(255, 196, 0, .10);
  border: 1px solid rgba(255, 196, 0, .22);
}
.admin-order-view .lb-banner--paused .lb-banner__icon{
  width:40px;
  height:40px;
  border-radius: 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(255, 196, 0, .14);
  border: 1px solid rgba(255, 196, 0, .22);
  color:#ffd36a;
  flex: 0 0 auto;
}
.admin-order-view .lb-banner__title{
  font-weight:900;
  margin-bottom:.1rem;
}
.admin-order-view .lb-banner__sub{
  opacity:.85;
}

/* =========================
   MODALS (Client-like)
========================= */
.admin-order-view .modal-backdrop.show{ opacity:.65; }
.admin-order-view .modal-dialog{ --lb-modal-w: 620px; }
@media (min-width:992px){
  .admin-order-view .modal-dialog{ max-width: var(--lb-modal-w); }
}
.admin-order-view .modal-content{
  background: rgba(28, 30, 34, .98);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 1.25rem;
  box-shadow: 0 25px 80px rgba(0,0,0,.55);
  overflow: hidden;
}
.admin-order-view .modal-header{
  padding: 1.05rem 1.25rem;
  border-bottom: 1px solid rgba(255,255,255,.08);
}
.admin-order-view .modal-title{
  font-weight: 900;
  letter-spacing: .01em;
}
.admin-order-view .modal-body{
  padding: 1.05rem 1.25rem;
}
.admin-order-view .modal-footer{
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(255,255,255,.08);
  gap: .6rem;
}
.admin-order-view .modal .btn-close{
  filter: invert(1) grayscale(1);
  opacity: .65;
}
.admin-order-view .modal .btn-close:hover{ opacity: .9; }

.admin-order-view .modal .form-control,
.admin-order-view .modal .form-select,
.admin-order-view .modal textarea{
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10) !important;
  color: inherit;
  border-radius: .85rem;
}
.admin-order-view .modal .form-control:focus,
.admin-order-view .modal .form-select:focus,
.admin-order-view .modal textarea:focus{
  box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .18);
  border-color: rgba(99, 102, 241, .55) !important;
}

/* Make native <select> dropdown dark (options list) */
.admin-order-view .modal select.form-select{
  color-scheme: dark;
}
.admin-order-view .modal select.form-select option,
.admin-order-view .modal select.form-select optgroup{
  background: #1f2226;
  color: #fff;
}
.admin-order-view [data-theme="light"] .modal select.form-select{
  color-scheme: light;
}
.admin-order-view [data-theme="light"] .modal select.form-select option,
.admin-order-view [data-theme="light"] .modal select.form-select optgroup{
  background: #fff;
  color: #111;
}

.admin-order-view .modal .btn.btn-primary{
  border-radius: .9rem;
  padding: .65rem 1rem;
  font-weight: 900;
}
.admin-order-view .modal .btn.btn-white{
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: inherit;
  border-radius: .9rem;
  padding: .65rem 1rem;
  font-weight: 800;
}
.admin-order-view .modal .btn-group .btn{
  border-radius: .85rem !important;
}
.admin-order-view .modal .btn-group .btn + .btn{
  margin-left: .4rem;
}

/* Add Booster TomSelect dropdown in modal (keep under select, above modal chrome) */
.admin-order-view #add_booster_md .ts-dropdown{
  z-index: 1066; /* modal is 1055 */
}


.boost-form-svg {
  filter: brightness(0) invert(1);
}



/* =========================
   REMOVE BOOSTER MODAL: desktop fit (no scrolling)
========================= */
#remove_booster_progress_payment_md .rb-auto-box{background: rgba(0,0,0,0.02);} 
#remove_booster_progress_payment_md .modal-content{border-radius: 1rem;}
@media (min-width: 992px){
  #remove_booster_progress_payment_md .modal-dialog{max-width: 980px;}
  #remove_booster_progress_payment_md .modal-header,
  #remove_booster_progress_payment_md .modal-footer{padding: .75rem 1rem;}
  #remove_booster_progress_payment_md .modal-body{padding: 1rem;}
  #remove_booster_progress_payment_md .mb-3{margin-bottom: .75rem !important;}
  #remove_booster_progress_payment_md .form-text{font-size: .78rem;}
}


/* =========================
   COMPLETE ORDER - PROOF PREVIEW (admin)
========================= */
.admin-order-view .lb-proof-preview{
  position: relative;
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(0,0,0,.18);
  height: 150px; /* compact preview */
}
.admin-order-view .lb-proof-preview img{
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}
.admin-order-view .lb-proof-actions{
  position: absolute;
  right: 12px;
  bottom: 12px;
  display: flex;
  gap: 10px;
}
.admin-order-view .lb-proof-actions .btn{
  border-radius: 999px;
  padding: .42rem .78rem;
  font-weight: 700;
  border: 1px solid rgba(255,255,255,.14);
  background: rgba(0,0,0,.38);
  color: #fff;
}
.admin-order-view .lb-proof-actions .btn:hover{
  background: rgba(255,255,255,.08);
}

/* =========================
   COMPLETE ORDER MODAL (admin) - booster-like polish
========================= */
#complete_order_md .modal-dialog{max-width: 760px;}
#complete_order_md .modal-content{
  border-radius: 20px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.02) 100%);
  border: 1px solid rgba(255,255,255,.08);
  box-shadow: 0 40px 120px rgba(0,0,0,.55);
}
#complete_order_md .modal-header{
  padding: 18px 18px 14px;
  border-bottom: 1px solid rgba(255,255,255,.06);
}
#complete_order_md .lb-modal-head{
  display: flex;
  gap: 12px;
  align-items: flex-start;
}
#complete_order_md .lb-modal-ico{
  width: 44px; height: 44px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(78,161,255,.10);
  border: 1px solid rgba(78,161,255,.22);
  box-shadow: 0 0 0 6px rgba(78,161,255,.06);
  flex: 0 0 auto;
}
#complete_order_md .lb-modal-title{
  font-size: 1.02rem;
  font-weight: 800;
  margin: 0;
}
#complete_order_md .lb-modal-sub{
  margin: 4px 0 0;
  font-size: .86rem;
  opacity: .75;
}
#complete_order_md .modal-body{padding: 14px 18px 16px;}
#complete_order_md .lb-section{
  border: 1px solid rgba(255,255,255,.06);
  background: rgba(0,0,0,.14);
  border-radius: 16px;
  padding: 14px;
}
#complete_order_md .lb-section-title{
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
#complete_order_md .lb-section-title span{
  font-weight: 800;
  font-size: .9rem;
}
#complete_order_md .btn-group-segment .btn{border-radius: 12px;}
#complete_order_md .modal-footer{
  padding: 14px 18px 18px;
  border-top: 1px solid rgba(255,255,255,.06);
}

/* Custom confirm (no browser confirm popup) */
#complete_order_md .lb-confirm-backdrop{
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,.55);
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 50;
}
#complete_order_md .lb-confirm-backdrop.is-open{display:flex;}
#complete_order_md .lb-confirm-card{
  width: min(420px, 100%);
  border-radius: 18px;
  background: rgba(25,27,31,.98);
  border: 1px solid rgba(255,255,255,.10);
  box-shadow: 0 25px 80px rgba(0,0,0,.6);
  padding: 16px;
}
#complete_order_md .lb-confirm-title{
  font-weight: 900;
  font-size: .95rem;
  margin-bottom: 6px;
}
#complete_order_md .lb-confirm-desc{
  font-size: .86rem;
  opacity: .75;
  margin-bottom: 14px;
}
#complete_order_md .lb-confirm-actions{
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
#complete_order_md .lb-confirm-actions .btn{
  border-radius: 999px;
  padding: .45rem .9rem;
  font-weight: 800;
}

@media (max-width: 576px){
  .admin-order-view .lb-proof-preview{height: 120px;}
  #complete_order_md .modal-dialog{margin: .75rem;}
  #complete_order_md .lb-modal-title{font-size: .95rem;}
}

/* === FIX: TomSelect selected tags (keep dark, not white) === */
.admin-order-view .ts-control .item{
  background: rgba(255,255,255,.06) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  color: rgba(255,255,255,.90) !important;
  border-radius: 10px !important;
  padding: .20rem .55rem !important;
  margin: .18rem .25rem .18rem 0 !important;
}
.admin-order-view .ts-control .item .remove{
  border-left: 1px solid rgba(255,255,255,.12) !important;
  color: rgba(255,255,255,.75) !important;
}
.admin-order-view .ts-control .item .remove:hover{
  background: rgba(255,255,255,.06) !important;
  color: rgba(255,255,255,.90) !important;
}

/* === FIX: Champions images never overflow their box === */
.admin-order-view .booster-intro-champs{
  display: flex;
  flex-wrap: wrap;
  gap: .35rem;
  max-width: 100%;
  overflow: hidden;
}
.admin-order-view .booster-intro-champs .champ{
  flex: 0 0 auto;
  width: 20px;
  height: 20px;
  border-radius: 6px;
}
.admin-order-view .booster-intro-champs .more{
  cursor: help;
  user-select: none;
}
.lb-champs-tooltip{
  position: fixed;
  z-index: 99999;
  width: min(380px, calc(100vw - 28px));
  max-height: 260px;
  overflow-y: auto;
  padding: 12px;
  border-radius: 16px;
  border: 1px solid rgba(124,92,255,.35);
  background: rgba(24,25,30,.98);
  box-shadow: 0 22px 70px rgba(0,0,0,.58), 0 0 0 1px rgba(255,255,255,.04) inset;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  opacity: 0;
  visibility: hidden;
  pointer-events: auto;
  transform: translateY(6px);
  transition: opacity .12s ease, transform .12s ease, visibility .12s ease;
  scrollbar-width: thin;
  scrollbar-color: rgba(124,92,255,.65) rgba(255,255,255,.06);
}
.lb-champs-tooltip.is-visible{
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
.lb-champs-tooltip::-webkit-scrollbar{
  width: 6px;
}
.lb-champs-tooltip::-webkit-scrollbar-track{
  background: rgba(255,255,255,.06);
  border-radius: 999px;
}
.lb-champs-tooltip::-webkit-scrollbar-thumb{
  background: rgba(124,92,255,.65);
  border-radius: 999px;
}
.lb-champs-tooltip__title{
  margin: 0 0 10px;
  font-size: 11px;
  font-weight: 950;
  letter-spacing: .10em;
  text-transform: uppercase;
  color: rgba(255,255,255,.68);
}
.lb-champs-tooltip__grid{
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(34px, 1fr));
  gap: 8px;
}
.lb-champs-tooltip__item{
  width: 34px;
  height: 34px;
  border-radius: 11px;
  display: grid;
  place-items: center;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.055);
  overflow: hidden;
}
.lb-champs-tooltip__item img{
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.lb-champs-tooltip__tag{
  width: 100%;
  min-height: 34px;
  padding: 0 8px;
  border-radius: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.055);
  font-size: 11px;
  font-weight: 900;
  color: rgba(255,255,255,.88);
  text-align: center;
}



/* === FIX: Options (Champions/Roles) never overflow their row === */
.admin-order-view .lb-opt-sub .lb-opt-val{
  max-width: 100%;
  overflow: hidden;
}
/* Make any champions/roles output wrap inside the box */
.admin-order-view .lb-opt-sub .lb-opt-val,
.admin-order-view .lb-opt-sub .lb-opt-val *{
  white-space: normal !important;
}
.admin-order-view .lb-opt-sub .lb-opt-val{
  display: flex;
  flex-wrap: wrap;
  gap: .35rem;
  align-items: center;
}
.admin-order-view .lb-opt-sub .lb-opt-val img{
  flex: 0 0 auto;
  max-width: 22px;
  height: 22px;
  border-radius: 6px;
}
/* If util_format_option outputs a list container, force it to wrap */
.admin-order-view .lb-opt-sub .lb-opt-val .champions,
.admin-order-view .lb-opt-sub .lb-opt-val .roles,
.admin-order-view .lb-opt-sub .lb-opt-val .champions-list,
.admin-order-view .lb-opt-sub .lb-opt-val .roles-list{
  display: flex !important;
  flex-wrap: wrap !important;
  gap: .35rem;
  max-width: 100%;
}

/* ==== Chat image sizing + attachment preview (admin) ==== */
.admin-order-view #chat_messages img.lb-msg__avatar{
  width: 36px;
  height: 36px;
  max-width: 36px;
  max-height: 36px;
  border-radius: 999px;
  object-fit: cover;
  cursor: default;
}
.admin-order-view #chat_messages img.lb-msg__avatar:hover{ transform: none; }

.admin-order-view #chat_messages img{
  max-width: 320px;
  max-height: 320px;
  width: auto;
  height: auto;
  border-radius: 10px;
  display: inline-block;
  cursor: pointer;
}
.admin-order-view #chat_messages img:hover{ transform: scale(1.01); }

/* chat icon buttons */
.admin-order-view .btn-chat-icon{
  width: 42px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: .8rem;
  padding: 0;
}

.admin-order-view .lb-chat-poke-btn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .25rem;
  border-radius: 999px;
  font-weight: 900;
  white-space: nowrap;
  padding: .42rem .8rem;
}
@media (max-width: 575.98px){
  .admin-order-view .lb-chat-poke-btn{
    padding: .38rem .65rem;
    font-size: .78rem;
  }
}

/* attachment preview */
.admin-order-view .lb-chat-attach-preview{
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .55rem .7rem;
  border-radius: .9rem;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.04);
}
.admin-order-view [data-theme="light"] .lb-chat-attach-preview{
  border-color: rgba(0,0,0,.08);
  background: rgba(0,0,0,.03);
}
.admin-order-view .lb-chat-attach-preview__thumb{
  width: 46px; height: 46px; border-radius: .75rem; overflow: hidden;
  border: 1px solid rgba(255,255,255,.10);
  flex: 0 0 auto;
}
.admin-order-view .lb-chat-attach-preview__thumb img{
  width: 100%; height: 100%; object-fit: cover;
}
.admin-order-view .lb-chat-attach-preview__meta{ min-width:0; flex:1; }
.admin-order-view .lb-chat-attach-preview__title{ font-weight: 800; font-size: .82rem; }
.admin-order-view .lb-chat-attach-preview__name{ font-size: .78rem; opacity:.8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.admin-order-view .lb-chat-attach-preview__remove{
  border: 0; background: transparent; color: inherit; opacity: .75;
  width: 34px; height: 34px; border-radius: .75rem;
  display: inline-flex; align-items:center; justify-content:center;
}
.admin-order-view .lb-chat-attach-preview__remove:hover{ opacity: 1; background: rgba(255,255,255,.06); }
.admin-order-view [data-theme="light"] .lb-chat-attach-preview__remove:hover{ background: rgba(0,0,0,.05); }


  .lb-duo-timer-card{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);overflow:hidden;}
  .lb-duo-timer-body{padding:18px 20px 16px !important;}
  .lb-dt-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
  .lb-dt-left{flex:1;}
  .lb-dt-label{font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px;}
  .lb-dt-countdown{font-size:2.4rem;font-weight:800;letter-spacing:.03em;color:#fff;line-height:1;font-variant-numeric:tabular-nums;}
  .lb-dt-sub{font-size:.78rem;color:rgba(255,255,255,.38);margin-top:5px;}
  .lb-dt-ring{position:relative;width:62px;height:62px;flex-shrink:0;margin-top:2px;}
  .lb-dt-ring svg{display:block;}
  .lb-dt-ring-pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#a29bfe;}
  .lb-dt-foot{display:flex;align-items:center;justify-content:space-between;padding-top:12px;margin-top:14px;border-top:1px solid rgba(255,255,255,.06);}
  .lb-dt-status{display:flex;align-items:center;gap:6px;}
  .lb-dt-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;background:rgba(255,255,255,.3);}
  .lb-dt-status-text{font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);}
  .lb-dt-elapsed{font-size:.78rem;color:rgba(255,255,255,.38);}
  @media (max-width:575.98px){.lb-dt-countdown{font-size:1.9rem;}}



/* =========================
     ORDER PROGRESS CARD
  ========================= */
  .lb-op-card .card-header {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
  }

  .lb-op-header-ico {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(99, 102, 241, .14);
    border: 1px solid rgba(99, 102, 241, .25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgba(130, 134, 255, .95);
    font-size: .88rem;
    flex: 0 0 auto;
  }

  .lb-op-refresh-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .65);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    padding: 0;
  }

  .lb-op-refresh-btn:hover {
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .18);
    color: rgba(255, 255, 255, .95);
  }

  .lb-op-refresh-btn:disabled {
    opacity: .45;
    pointer-events: none;
  }

  /* ─ rank strip ─ */
  .lb-op-rank-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px;
    background: rgba(0, 0, 0, .16);
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 16px;
    margin-bottom: 10px;
  }

  .lb-op-rank-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    min-width: 0;
  }

  .lb-op-rank-img {
    width: 48px;
    height: 48px;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .45));
    flex: 0 0 auto;
  }

  .lb-op-rank-box--current .lb-op-rank-img {
    width: 56px;
    height: 56px;
    filter: drop-shadow(0 4px 12px rgba(99, 102, 241, .35));
  }

  .lb-op-rank-name {
    font-size: .80rem;
    font-weight: 800;
    text-align: center;
    line-height: 1.2;
    word-break: break-word;
  }

  .lb-op-rank-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    opacity: .40;
  }

  .lb-op-rank-arrow {
    flex: 0 0 auto;
    opacity: .30;
    font-size: .85rem;
    display: flex;
    align-items: center;
  }

  /* ─ stats row ─ */
  .lb-op-stats {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 7px;
    margin-bottom: 8px;
  }

  .lb-op-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 10px 6px;
    border-radius: 13px;
    border: 1px solid rgba(255, 255, 255, .06);
    background: rgba(0, 0, 0, .12);
  }

  .lb-op-stat--win {
    border-color: rgba(34, 197, 94, .20);
    background: rgba(34, 197, 94, .06);
  }

  .lb-op-stat--loss {
    border-color: rgba(239, 68, 68, .20);
    background: rgba(239, 68, 68, .06);
  }

  .lb-op-stat-val {
    font-size: 1.05rem;
    font-weight: 900;
    line-height: 1;
  }

  .lb-op-stat--win .lb-op-stat-val {
    color: rgba(74, 222, 128, .95);
  }

  .lb-op-stat--loss .lb-op-stat-val {
    color: rgba(248, 113, 113, .95);
  }

  .lb-op-stat-lbl {
    font-size: .65rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    opacity: .40;
  }

  /* ─ winrate bar ─ */
  .lb-op-wr-bar {
    height: 5px;
    background: rgba(255, 255, 255, .07);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 14px;
  }

  .lb-op-wr-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: rgba(255, 255, 255, .20);
    transition: width .5s ease;
  }

  .lb-op-wr-bar-fill--good {
    background: linear-gradient(90deg, rgba(34, 197, 94, .65) 0%, rgba(74, 222, 128, .90) 100%);
  }

  /* ─ footer ─ */
  .lb-op-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    flex-wrap: wrap;
  }

  .lb-op-footer-item {
    display: flex;
    align-items: center;
    gap: 5px;
    min-width: 0;
  }

  .lb-op-footer-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    opacity: .38;
  }

  .lb-op-footer-val {
    font-size: .76rem;
    font-weight: 700;
    opacity: .72;
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* ─ sync state ─ */
  .lb-op-sync-state {
    font-size: .76rem;
    font-weight: 600;
    margin-top: 7px;
    min-height: 0;
    transition: color .2s;
  }

  .lb-op-sync-state:empty {
    display: none;
  }

  /* ─ warning / no-riot ─ */
  .lb-op-warning {
    font-size: .79rem;
    padding: 9px 12px;
    border-radius: 12px;
    background: rgba(234, 179, 8, .08);
    border: 1px solid rgba(234, 179, 8, .22);
    color: rgba(253, 213, 77, .90);
  }

  .lb-op-no-riot {
    font-size: .79rem;
    padding: 9px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .07);
    opacity: .60;
  }

  /* ─ light theme overrides ─ */
  [data-theme="light"] .lb-op-card .card-header {
    border-bottom-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-header-ico {
    background: rgba(99, 102, 241, .09);
    border-color: rgba(99, 102, 241, .18);
    color: rgba(79, 70, 229, .90);
  }

  [data-theme="light"] .lb-op-refresh-btn {
    border-color: rgba(0, 0, 0, .12);
    color: rgba(0, 0, 0, .50);
  }

  [data-theme="light"] .lb-op-refresh-btn:hover {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .18);
    color: rgba(0, 0, 0, .80);
  }

  [data-theme="light"] .lb-op-rank-row {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-stat {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-stat--win {
    background: rgba(34, 197, 94, .05);
    border-color: rgba(34, 197, 94, .18);
  }

  [data-theme="light"] .lb-op-stat--loss {
    background: rgba(239, 68, 68, .05);
    border-color: rgba(239, 68, 68, .18);
  }

  [data-theme="light"] .lb-op-wr-bar {
    background: rgba(0, 0, 0, .09);
  }

  [data-theme="light"] .lb-op-footer {
    border-top-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-warning {
    background: rgba(234, 179, 8, .06);
    color: rgba(133, 77, 14, .90);
  }

  [data-theme="light"] .lb-op-no-riot {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .07);
  }

  /* ─ count mode (win / placement / normal / match / clash / arena) ─ */
  .lb-op-count-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px;
    background: rgba(0, 0, 0, .16);
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 16px;
    margin-bottom: 10px;
  }

  .lb-op-count-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
  }

  .lb-op-count-val {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    font-variant-numeric: tabular-nums;
  }

  .lb-op-count-val--target {
    font-size: 1.4rem;
    opacity: .48;
  }

  .lb-op-count-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    opacity: .40;
  }

  .lb-op-count-sep {
    font-size: 1.6rem;
    font-weight: 300;
    opacity: .22;
    flex: 0 0 auto;
  }

  .lb-op-count-progress {
    height: 7px;
    background: rgba(255, 255, 255, .07);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 10px;
  }

  .lb-op-count-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: rgba(99, 102, 241, .55);
    transition: width .5s ease;
  }

  .lb-op-count-progress-fill--done {
    background: linear-gradient(90deg, rgba(34, 197, 94, .65) 0%, rgba(74, 222, 128, .90) 100%);
  }

  .lb-op-count-rank {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 12px;
    margin-bottom: 8px;
    border-radius: 13px;
    background: rgba(0, 0, 0, .12);
    border: 1px solid rgba(255, 255, 255, .06);
  }

  .lb-op-count-rank-img {
    width: 42px;
    height: 42px;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .35));
    flex: 0 0 auto;
  }

  .lb-op-count-rank-copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .lb-op-count-rank-kicker {
    font-size: .64rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    opacity: .40;
  }

  .lb-op-count-rank-name {
    font-size: .82rem;
    font-weight: 800;
    line-height: 1.2;
    word-break: break-word;
  }

  /* ─ coaching mode ─ */
  .lb-op-coaching-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 18px 14px 14px;
    background: rgba(0, 0, 0, .16);
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 16px;
    margin-bottom: 10px;
    text-align: center;
  }

  .lb-op-coaching-hours {
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1;
    font-variant-numeric: tabular-nums;
  }

  .lb-op-coaching-label {
    font-size: .70rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    font-weight: 700;
    opacity: .40;
  }

  .lb-op-coaching-note {
    font-size: .79rem;
    padding: 9px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .07);
    opacity: .60;
    text-align: center;
    margin-bottom: 8px;
  }

  /* ─ level / mastery mode ─ */
  .lb-op-level-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px;
    background: rgba(0, 0, 0, .16);
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 16px;
    margin-bottom: 10px;
  }

  .lb-op-level-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
  }

  .lb-op-level-ico {
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .09);
    border-radius: 14px;
    flex: 0 0 auto;
  }

  .lb-op-level-val {
    font-size: 1.1rem;
    font-weight: 900;
    text-align: center;
    line-height: 1.2;
    font-variant-numeric: tabular-nums;
  }

  .lb-op-level-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    opacity: .40;
  }

  /* light-theme additions for new modes */
  [data-theme="light"] .lb-op-count-row,
  [data-theme="light"] .lb-op-level-row,
  [data-theme="light"] .lb-op-coaching-info {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-count-progress {
    background: rgba(0, 0, 0, .09);
  }

  [data-theme="light"] .lb-op-count-rank {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-coaching-note {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-level-ico {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .08);
  }

  /* =========================
     MATCH HISTORY — TRIGGER LINK
  ========================= */
  .lb-op-view-history {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0 2px;
    margin-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    text-decoration: none;
    color: inherit;
    opacity: .60;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .01em;
    transition: opacity .15s;
  }

  .lb-op-view-history:hover {
    opacity: 1;
    color: inherit;
    text-decoration: none;
  }

  .lb-op-view-history-left {
    display: flex;
    align-items: center;
    gap: 7px;
  }

  .lb-op-view-history-left i {
    font-size: .82rem;
  }

  .lb-op-history-count {
    background: rgba(168, 85, 247, .13);
    border: 1px solid rgba(168, 85, 247, .22);
    color: rgba(196, 148, 255, .95);
    border-radius: 999px;
    font-size: .65rem;
    font-weight: 900;
    padding: 2px 8px;
    letter-spacing: .03em;
  }

  .lb-op-view-history-arrow {
    font-size: .68rem;
    opacity: .45;
    transition: transform .15s;
  }

  .lb-op-view-history:hover .lb-op-view-history-arrow {
    transform: translateX(3px);
  }

  [data-theme="light"] .lb-op-view-history {
    border-top-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-history-count {
    background: rgba(168, 85, 247, .08);
    border-color: rgba(168, 85, 247, .18);
    color: rgba(109, 40, 217, .90);
  }

  /* =========================
     MATCH HISTORY — MODAL
  ========================= */
  .lb-mh-modal .modal-dialog {
    --bs-modal-width: min(1240px, 98vw);
    max-width: min(1240px, 98vw) !important;
  }

  .lb-mh-list {
    transition: opacity .15s;
  }

  .lb-mh-modal .modal-content {
    border: 1px solid rgba(255, 255, 255, .08);
    background: var(--bs-card-bg, #111827);
    border-radius: 18px;
    overflow: hidden;
  }

  .lb-mh-modal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .02);
  }

  .lb-mh-modal .modal-body {
    padding: 0;
  }

  .lb-mh-header-ico {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: rgba(168, 85, 247, .14);
    border: 1px solid rgba(168, 85, 247, .25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgba(196, 148, 255, .95);
    font-size: .90rem;
    flex: 0 0 auto;
  }

  /* column header */
  .lb-mh-list-head {
    display: grid;
    grid-template-columns: 82px minmax(145px, 1fr) minmax(112px, .70fr) 86px 78px 104px 72px 145px 92px 72px;
    align-items: center;
    padding: 7px 20px 7px 23px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(0, 0, 0, .14);
  }

  .lb-mh-list-head span {
    font-size: .66rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    opacity: .32;
    white-space: nowrap;
  }

  /* list container */
  .lb-mh-list {
    width: 100%;
  }

  /* match row */
  .lb-mh-row {
    display: grid;
    grid-template-columns: 82px minmax(145px, 1fr) minmax(112px, .70fr) 86px 78px 104px 72px 145px 92px 72px;
    align-items: center;
    padding: 11px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .04);
    border-left: 3px solid transparent;
    transition: filter .12s;
  }

  .lb-mh-row:last-child {
    border-bottom: none;
  }

  .lb-mh-row--win {
    border-left-color: rgba(34, 197, 94, .65);
    background: rgba(34, 197, 94, .025);
  }

  .lb-mh-row--loss {
    border-left-color: rgba(239, 68, 68, .60);
    background: rgba(239, 68, 68, .02);
  }

  .lb-mh-row--remake {
    border-left-color: rgba(56, 189, 248, .75);
    background: rgba(56, 189, 248, .035);
  }

  .lb-mh-row:hover {
    filter: brightness(1.09);
  }

  /* result cell */
  .lb-mh-result {
    display: flex;
    align-items: center;
  }

  .lb-mh-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .lb-mh-badge--win {
    color: rgba(74, 222, 128, .95);
    background: rgba(34, 197, 94, .12);
    border: 1px solid rgba(34, 197, 94, .22);
  }

  .lb-mh-badge--loss {
    color: rgba(248, 113, 113, .95);
    background: rgba(239, 68, 68, .12);
    border: 1px solid rgba(239, 68, 68, .22);
  }

  .lb-mh-badge--remake {
    color: rgba(125, 211, 252, .98);
    background: rgba(56, 189, 248, .13);
    border: 1px solid rgba(56, 189, 248, .28);
  }

  /* champion cell */
  .lb-mh-champ-col {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }

  .lb-mh-champ-img {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .35);
    flex: 0 0 auto;
  }

  .lb-mh-champ-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
  }

  .lb-mh-champ-name {
    font-weight: 800;
    font-size: .82rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .lb-mh-queue {
    font-size: .68rem;
    font-weight: 600;
    opacity: .40;
    white-space: nowrap;
  }

  /* booster cell */
  .lb-mh-booster-col {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }

  .lb-mh-booster-ico {
    width: 24px;
    height: 24px;
    border-radius: 8px;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: rgba(168, 85, 247, .12);
    border: 1px solid rgba(168, 85, 247, .22);
    color: rgba(196, 148, 255, .95);
    font-size: .70rem;
  }

  .lb-mh-booster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .lb-mh-booster-name {
    display: block;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .78rem;
    font-weight: 800;
  }

  .lb-mh-booster-sub {
    display: block;
    font-size: .66rem;
    font-weight: 700;
    opacity: .38;
    text-transform: uppercase;
    letter-spacing: .05em;
  }


  /* mode cell */
  .lb-mh-mode-col {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    min-width: 0;
  }

  .lb-mh-mode-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: .66rem;
    line-height: 1;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
  }

  .lb-mh-mode-pill--solo {
    color: rgba(125, 211, 252, .96);
    background: rgba(56, 189, 248, .11);
    border: 1px solid rgba(56, 189, 248, .22);
  }

  .lb-mh-mode-pill--duo {
    color: rgba(196, 148, 255, .96);
    background: rgba(168, 85, 247, .12);
    border: 1px solid rgba(168, 85, 247, .24);
  }

  .lb-mh-mode-sub {
    display: block;
    max-width: 88px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .64rem;
    font-weight: 700;
    opacity: .38;
  }
  /* role cell */
  .lb-mh-role-col {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    font-weight: 700;
    opacity: .75;
  }

  .lb-mh-role-img {
    width: 20px;
    height: 20px;
    object-fit: contain;
    opacity: .85;
  }

  /* kda cell */
  .lb-mh-kda-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .lb-mh-kda {
    font-size: .88rem;
    font-weight: 900;
    font-variant-numeric: tabular-nums;
    letter-spacing: .01em;
  }

  .lb-mh-kda-sep {
    opacity: .28;
    margin: 0 2px;
    font-weight: 400;
  }

  .lb-mh-kda-ratio {
    font-size: .68rem;
    font-weight: 700;
    opacity: .42;
  }

  /* duration cell */
  .lb-mh-dur-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .lb-mh-dur {
    font-size: .82rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
  }

  .lb-mh-sub {
    font-size: .67rem;
    font-weight: 600;
    opacity: .38;
    text-transform: uppercase;
    letter-spacing: .05em;
  }

  .lb-mh-rank-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .lb-mh-rank-col--snap .lb-mh-rank-inner {
    display: flex;
    align-items: center;
    gap: 7px;
  }

  .lb-mh-rank-ico {
    width: 28px;
    height: 28px;
    flex: 0 0 auto;
    object-fit: contain;
    filter: drop-shadow(0 1px 3px rgba(0,0,0,.45));
  }

  .lb-mh-rank-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
  }

  .lb-mh-rank-name {
    font-size: .74rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
  }

  /* date cell */
  .lb-mh-date-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .lb-mh-date {
    font-size: .80rem;
    font-weight: 700;
  }

  .lb-mh-time {
    font-size: .70rem;
    opacity: .45;
    font-weight: 600;
  }

  /* empty / loading */
  .lb-mh-placeholder {
    text-align: center;
    padding: 44px 20px;
    opacity: .42;
    font-weight: 600;
    font-size: .82rem;
  }

  /* pagination */
  .lb-mh-pager {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 12px 20px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    font-size: .78rem;
    background: rgba(0, 0, 0, .08);
  }

  .lb-mh-pager-info {
    opacity: .42;
    font-weight: 600;
  }

  .lb-mh-pager-btns {
    display: flex;
    gap: 6px;
  }

  .lb-mh-pager-btn {
    padding: 5px 14px;
    border-radius: 9px;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, .10);
    color: inherit;
    font-size: .76rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .12s, border-color .12s;
  }

  .lb-mh-pager-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .18);
  }

  .lb-mh-pager-btn:disabled {
    opacity: .22;
    cursor: default;
  }



  /* admin backfill + row actions */
  .lb-mh-admin-tools {
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(0, 0, 0, .08);
  }

  .lb-mh-backfill-toggle {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: .76rem;
    font-weight: 800;
  }

  .lb-mh-backfill-form {
    display: none;
    margin-top: 12px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .025);
  }

  .lb-mh-backfill-form.is-open {
    display: block;
  }

  .lb-mh-backfill-grid {
    display: grid;
    grid-template-columns: minmax(220px, 1.2fr) minmax(120px, .55fr) minmax(300px, 1.65fr) minmax(140px, .6fr);
    gap: 10px;
    align-items: stretch;
  }

  .lb-mh-backfill-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
  }

  .lb-mh-backfill-field label {
    display: block;
    min-height: 15px;
    margin-bottom: 6px;
    font-size: .64rem;
    font-weight: 900;
    letter-spacing: .07em;
    text-transform: uppercase;
    opacity: .48;
  }

  .lb-mh-backfill-field .form-control,
  .lb-mh-backfill-field .form-select,
  .lb-mh-mode-trigger,
  .lb-datetime-trigger,
  #lbMhBackfillSubmit {
    height: 46px;
    min-height: 46px;
    border-radius: 14px;
    font-size: .78rem;
  }

  .lb-mh-backfill-field .form-control,
  .lb-mh-backfill-field .form-select {
    padding-top: 10px;
    padding-bottom: 10px;
  }

  .lb-datetime-trigger span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  #lbMhBackfillSubmit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-left: 14px;
    padding-right: 14px;
    font-weight: 900;
  }

  .lb-mh-mode-select {
    position: relative;
    width: 100%;
  }

  .lb-mh-mode-select__native {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
    pointer-events: none !important;
  }

  .lb-mh-mode-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 13px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.035));
    color: inherit;
    text-align: left;
    font-weight: 850;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
  }

  .lb-mh-mode-trigger:hover,
  .lb-mh-mode-select.is-open .lb-mh-mode-trigger {
    border-color: rgba(124, 92, 255, .45);
    box-shadow: 0 0 0 4px rgba(124, 92, 255, .12);
  }

  .lb-mh-mode-trigger i {
    color: #9b88ff;
    font-size: .82rem;
    transition: transform .18s ease;
  }

  .lb-mh-mode-select.is-open .lb-mh-mode-trigger i {
    transform: rotate(180deg);
  }

  .lb-mh-mode-menu {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 8px);
    z-index: 20020;
    display: none;
    padding: 6px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,.12);
    background: linear-gradient(180deg, #20232b, #171920);
    box-shadow: 0 18px 44px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.04);
  }

  .lb-mh-mode-select.is-open .lb-mh-mode-menu {
    display: block;
  }

  .lb-mh-mode-option {
    width: 100%;
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 10px;
    border: 0;
    border-radius: 11px;
    background: transparent;
    color: inherit;
    font-size: .78rem;
    font-weight: 850;
    text-align: left;
  }

  .lb-mh-mode-option:hover {
    background: rgba(124, 92, 255, .14);
  }

  .lb-mh-mode-option i {
    opacity: 0;
    color: #7ef5e0;
  }

  .lb-mh-mode-option.is-selected {
    background: linear-gradient(135deg, rgba(124,92,255,.95), rgba(79,140,255,.92));
    color: #fff;
  }

  .lb-mh-mode-option.is-selected i {
    opacity: 1;
  }

  .lb-mh-riot-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    padding: 9px 10px;
    border-radius: 13px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .035);
  }

  .lb-mh-riot-preview[hidden] { display: none !important; }

  .lb-mh-riot-preview.is-found {
    border-color: rgba(31, 230, 198, .30);
    background: rgba(31, 230, 198, .08);
  }

  .lb-mh-riot-preview.is-error {
    border-color: rgba(255, 107, 107, .28);
    background: rgba(255, 107, 107, .08);
  }

  .lb-mh-riot-preview.is-loading .lb-mh-riot-preview__avatar {
    animation: lbRiotPulse 1s ease-in-out infinite;
  }

  .lb-mh-riot-preview__avatar {
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    overflow: hidden;
    background: rgba(124, 92, 255, .12);
    border: 1px solid rgba(124, 92, 255, .22);
  }

  .lb-mh-riot-preview__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: none;
  }

  .lb-mh-riot-preview__avatar i {
    display: grid;
    place-items: center;
    color: #9b7cff;
    font-size: 1.05rem;
  }

  .lb-mh-riot-preview__body { min-width: 0; flex: 1 1 auto; }

  .lb-mh-riot-preview__label {
    font-size: .62rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .62;
  }

  .lb-mh-riot-preview__name {
    margin-top: 1px;
    font-size: .82rem;
    font-weight: 950;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-mh-riot-preview__meta {
    margin-top: 1px;
    font-size: .72rem;
    font-weight: 700;
    opacity: .72;
  }

  .lb-mh-riot-confirm {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-left: auto;
    padding: 7px 11px;
    border: 0;
    border-radius: 999px;
    background: linear-gradient(135deg, #1fe6c6, #7ef5e0);
    color: #061e1a;
    font-size: .72rem;
    font-weight: 950;
    box-shadow: 0 10px 24px rgba(31, 230, 198, .16);
    white-space: nowrap;
  }

  .lb-mh-riot-confirm[hidden] { display: none !important; }

  .lb-mh-riot-confirm.is-confirmed {
    border: 1px solid rgba(31, 230, 198, .36);
    background: rgba(31, 230, 198, .16);
    color: #d8fff8;
    box-shadow: none;
  }

  @keyframes lbRiotPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(.96); opacity: .72; }
  }

  .lb-datetime-wrap {
    position: relative;
  }

  .lb-datetime-wrap.is-open .lb-datetime-trigger {
    border-color: rgba(124, 92, 255, .52);
    box-shadow: 0 0 0 4px rgba(124, 92, 255, .14), 0 14px 34px rgba(11, 13, 22, .30);
  }

  .lb-datetime-trigger {
    width: 100%;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .03));
    box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
    color: inherit;
    font-size: .79rem;
    font-weight: 800;
    text-align: left;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
  }

  .lb-datetime-trigger:hover {
    border-color: rgba(124, 92, 255, .40);
    transform: translateY(-1px);
  }

  .lb-datetime-trigger i {
    flex: 0 0 auto;
    color: #9b88ff;
    font-size: .95rem;
    opacity: .92;
  }

  .lb-datetime-trigger .lb-datetime-placeholder {
    opacity: .45;
  }

  .lb-datetime-popover {
    display: none;
    position: fixed;
    z-index: 20010;
    top: 0;
    left: 0;
    width: min(360px, calc(100vw - 24px));
    padding: 14px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, .12);
    background: linear-gradient(180deg, #20232b 0%, #171920 100%);
    backdrop-filter: blur(18px);
    box-shadow: 0 26px 70px rgba(0, 0, 0, .52), inset 0 1px 0 rgba(255,255,255,.04);
    color: rgba(255,255,255,.96);
  }

  .lb-datetime-popover.is-open {
    display: block;
  }

  /* Date picker floats above the scrollable modal body so it is not clipped. */
  .lb-mh-modal .modal-content,
  .lb-mh-modal .modal-body {
    overflow: visible;
  }

  .lb-datetime-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    padding: 11px 12px;
    border-radius: 15px;
    border: 1px solid rgba(124, 92, 255, .20);
    background: linear-gradient(180deg, rgba(124, 92, 255, .14), rgba(124, 92, 255, .07));
  }

  .lb-datetime-summary__meta {
    min-width: 0;
  }

  .lb-datetime-summary__label {
    display: block;
    margin-bottom: 3px;
    font-size: .60rem;
    font-weight: 900;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .66;
  }

  .lb-datetime-summary__value {
    font-size: .75rem;
    font-weight: 900;
    line-height: 1.35;
    word-break: break-word;
  }

  .lb-datetime-summary__icon {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border-radius: 11px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .08);
    color: #a998ff;
    font-size: .92rem;
  }

  .lb-datetime-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
  }

  .lb-datetime-month {
    font-size: .94rem;
    font-weight: 950;
    letter-spacing: .01em;
  }

  .lb-datetime-nav {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 11px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: rgba(255, 255, 255, .045);
    color: inherit;
    transition: border-color .18s ease, background .18s ease, transform .18s ease;
  }

  .lb-datetime-nav:hover {
    border-color: rgba(124, 92, 255, .40);
    background: rgba(124, 92, 255, .12);
    transform: translateY(-1px);
  }

  .lb-datetime-weekdays,
  .lb-datetime-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
  }

  .lb-datetime-weekdays {
    margin-bottom: 6px;
  }

  .lb-datetime-weekdays span {
    text-align: center;
    font-size: .61rem;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
    opacity: .42;
  }

  .lb-datetime-day {
    height: 36px;
    border: 0;
    border-radius: 11px;
    background: transparent;
    color: inherit;
    font-size: .77rem;
    font-weight: 800;
    transition: background .16s ease, color .16s ease, transform .16s ease, opacity .16s ease;
  }

  .lb-datetime-day:hover {
    background: rgba(124, 92, 255, .14);
    transform: translateY(-1px);
  }

  .lb-datetime-day.is-muted {
    opacity: .23;
  }

  .lb-datetime-day.is-selected {
    background: linear-gradient(135deg, #7c5cff, #5a7dff);
    color: #fff;
    box-shadow: 0 10px 18px rgba(92, 110, 255, .26);
  }

  .lb-datetime-manual {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 14px;
  }

  .lb-datetime-field {
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: rgba(255, 255, 255, .035);
    padding: 10px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
  }

  .lb-datetime-field-label {
    display: block;
    margin-bottom: 8px;
    font-size: .63rem;
    font-weight: 900;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .54;
  }

  .lb-datetime-input {
    width: 100%;
    min-height: 40px;
    border-radius: 11px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: rgba(255, 255, 255, .045);
    color: inherit;
    padding: 8px 11px;
    font-size: .86rem;
    font-weight: 850;
    outline: none;
    transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
  }

  .lb-datetime-input:hover {
    border-color: rgba(255,255,255,.16);
  }

  .lb-datetime-input:focus {
    border-color: rgba(124, 92, 255, .56);
    box-shadow: 0 0 0 4px rgba(124, 92, 255, .14);
    background: rgba(255,255,255,.06);
  }

  .lb-datetime-input[data-picker-time] {
    cursor: text;
    font-variant-numeric: tabular-nums;
  }

  .lb-datetime-input[data-picker-time]::placeholder,
  .lb-datetime-input[data-picker-date]::placeholder {
    color: rgba(255,255,255,.38);
  }

  .lb-datetime-input::-webkit-calendar-picker-indicator {
    filter: invert(1) brightness(1.1);
    opacity: .9;
    cursor: pointer;
  }

  .lb-datetime-field-hint {
    display: block;
    margin-top: 7px;
    font-size: .62rem;
    font-weight: 700;
    opacity: .46;
  }

  .lb-datetime-timezone {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 13px;
    border: 1px solid rgba(124, 92, 255, .18);
    background: rgba(124, 92, 255, .09);
    padding: 10px 12px;
    font-size: .70rem;
    font-weight: 800;
    color: rgba(255,255,255,.86);
  }

  .lb-datetime-timezone i {
    color: #9a88ff;
  }

  .lb-datetime-actions {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-top: 12px;
  }

  .lb-datetime-actions button {
    min-height: 38px;
    border-radius: 999px;
    padding: 8px 14px;
    font-size: .73rem;
    font-weight: 900;
  }

  .lb-confirm-backdrop {
    position: fixed;
    inset: 0;
    z-index: 20000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(0, 0, 0, .58);
  }

  .lb-confirm-card {
    width: min(460px, 100%);
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: #1b1d21;
    box-shadow: 0 22px 70px rgba(0, 0, 0, .55);
    padding: 20px;
  }

  .lb-confirm-title {
    margin: 0 0 8px;
    font-size: 1rem;
    font-weight: 900;
  }

  .lb-confirm-message {
    margin: 0;
    font-size: .86rem;
    line-height: 1.55;
    opacity: .82;
  }

  .lb-confirm-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 18px;
  }

  [data-theme="light"] .lb-datetime-trigger,
  [data-theme="light"] .lb-datetime-nav,
  [data-theme="light"] .lb-datetime-field,
  [data-theme="light"] .lb-datetime-input {
    border-color: rgba(0, 0, 0, .09);
    background: rgba(0, 0, 0, .035);
  }

  [data-theme="light"] .lb-datetime-popover,
  [data-theme="light"] .lb-confirm-card {
    border-color: rgba(0, 0, 0, .10);
    background: linear-gradient(180deg, #ffffff 0%, #f4f6fb 100%);
    color: #111827;
  }

  [data-theme="light"] .lb-datetime-summary {
    border-color: rgba(124, 92, 255, .16);
    background: rgba(124, 92, 255, .08);
  }

  [data-theme="light"] .lb-datetime-timezone {
    border-color: rgba(124, 92, 255, .18);
    background: rgba(124, 92, 255, .08);
    color: rgba(0,0,0,.72);
  }

  .lb-mh-backfill-footer {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
  }

  .lb-mh-backfill-check {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1 1 320px;
    max-width: 480px;
    margin-top: 0;
    padding: 10px 12px;
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, .09);
    background: rgba(255, 255, 255, .035);
    cursor: pointer;
    transition: border-color .18s ease, background .18s ease, transform .18s ease;
  }

  .lb-mh-backfill-check:hover {
    border-color: rgba(124, 92, 255, .28);
    background: rgba(124, 92, 255, .06);
    transform: translateY(-1px);
  }

  .lb-mh-backfill-check input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .lb-mh-backfill-check__toggle {
    position: relative;
    flex: 0 0 auto;
    width: 46px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .08);
    box-shadow: inset 0 1px 2px rgba(0,0,0,.22);
    transition: background .18s ease, border-color .18s ease, box-shadow .18s ease;
  }

  .lb-mh-backfill-check__toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,.28);
    transition: transform .20s ease;
  }

  .lb-mh-backfill-check__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
  }

  .lb-mh-backfill-check__title {
    font-size: .78rem;
    font-weight: 900;
    line-height: 1.15;
  }

  .lb-mh-backfill-check__meta {
    font-size: .67rem;
    font-weight: 700;
    line-height: 1.35;
    opacity: .62;
  }

  .lb-mh-backfill-check input:checked + .lb-mh-backfill-check__toggle {
    border-color: rgba(115, 220, 200, .40);
    background: linear-gradient(135deg, #21d7bd, #7e83ff);
    box-shadow: 0 10px 20px rgba(33, 215, 189, .18);
  }

  .lb-mh-backfill-check input:checked + .lb-mh-backfill-check__toggle::after {
    transform: translateX(18px);
  }

  .lb-mh-backfill-check input:checked + .lb-mh-backfill-check__toggle + .lb-mh-backfill-check__text .lb-mh-backfill-check__title {
    color: #f6fffd;
  }

  .lb-mh-backfill-check input:checked + .lb-mh-backfill-check__toggle + .lb-mh-backfill-check__text .lb-mh-backfill-check__meta {
    opacity: .82;
  }

  [data-theme="light"] .lb-mh-backfill-check {
    border-color: rgba(0,0,0,.08);
    background: rgba(0,0,0,.03);
  }

  .lb-mh-backfill-state {
    margin-top: 0;
    padding-top: 10px;
    font-size: .74rem;
    font-weight: 700;
    opacity: .72;
  }


  .lb-mh-backfill-field--range { min-width: min(100%, 420px); }
  .lb-range-trigger { width: 100%; min-width: 0; }
  .lb-range-backdrop { position: fixed; inset: 0; z-index: 1095; display: flex; align-items: center; justify-content: center; padding: 22px; background: rgba(6, 8, 12, .68); backdrop-filter: blur(6px); }
  .lb-range-backdrop[hidden] { display: none !important; }
  .lb-range-card { width: min(96vw, 620px); border: 1px solid rgba(255,255,255,.12); border-radius: 22px; background: #202427; box-shadow: 0 26px 80px rgba(0,0,0,.46); color: #f4f6fb; overflow: hidden; }
  .lb-range-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; padding: 20px 22px 16px; border-bottom: 1px solid rgba(255,255,255,.08); }
  .lb-range-head h3 { margin: 0 0 5px; font-size: 20px; font-weight: 900; letter-spacing: -.02em; }
  .lb-range-head p { margin: 0; color: rgba(244,246,251,.66); font-size: 13px; font-weight: 700; }
  .lb-range-close { width: 42px; height: 42px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; background: rgba(255,255,255,.05); color: #fff; display: inline-flex; align-items: center; justify-content: center; }
  .lb-range-section { padding: 18px 22px 0; }
  .lb-range-step { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 12px; color: rgba(244,246,251,.75); font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
  .lb-range-step span { width: 22px; height: 22px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #7c5cff, #47ead5); color: #101316; font-size: 12px; font-weight: 1000; }
  .lb-range-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
  .lb-range-input { display: grid; gap: 7px; margin: 0; }
  .lb-range-input span { color: rgba(244,246,251,.6); font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
  .lb-range-input input { width: 100%; min-height: 46px; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; background: rgba(255,255,255,.05); color: #f6f7fb; padding: 0 13px; font-size: 14px; font-weight: 800; outline: none; }
  .lb-range-input input:focus { border-color: rgba(113,92,255,.7); box-shadow: 0 0 0 4px rgba(113,92,255,.16); }
  .lb-range-input input::-webkit-calendar-picker-indicator { display: none; opacity: 0; }
  .lb-range-presets--days { margin-top: 10px; }
  .lb-mh-backfill-field--range { min-width: 0; }
  .lb-range-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
  .lb-range-presets button { border: 1px solid rgba(255,255,255,.1); border-radius: 999px; background: rgba(255,255,255,.05); color: rgba(244,246,251,.78); font-size: 12px; font-weight: 900; padding: 8px 11px; }
  .lb-range-presets button:hover { background: rgba(124,92,255,.18); border-color: rgba(124,92,255,.35); color: #fff; }
  .lb-range-error { margin: 16px 22px 0; padding: 10px 12px; border: 1px solid rgba(255,87,87,.3); border-radius: 13px; background: rgba(255,87,87,.1); color: #ff8b8b; font-size: 13px; font-weight: 800; }
  .lb-range-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 20px 22px 22px; }
  [data-theme="light"] .lb-range-card { background: #ffffff; color: #19202b; border-color: rgba(15,23,42,.11); }
  [data-theme="light"] .lb-range-head, [data-theme="light"] .lb-range-input input, [data-theme="light"] .lb-range-close, [data-theme="light"] .lb-range-presets button { border-color: rgba(15,23,42,.11); }
  [data-theme="light"] .lb-range-head p, [data-theme="light"] .lb-range-step, [data-theme="light"] .lb-range-input span { color: rgba(15,23,42,.62); }
  [data-theme="light"] .lb-range-input input, [data-theme="light"] .lb-range-close, [data-theme="light"] .lb-range-presets button { background: rgba(15,23,42,.04); color: #19202b; }
  [data-theme="light"] .lb-range-input input::-webkit-calendar-picker-indicator { display: none; opacity: 0; }
  @media (max-width: 640px) { .lb-range-grid { grid-template-columns: 1fr; } .lb-range-actions { flex-wrap: wrap; } .lb-range-trigger { min-width: 0; } }

  @media (max-width: 575.98px) {
    .lb-datetime-manual {
      grid-template-columns: 1fr;
    }

    .lb-mh-backfill-check {
      flex-basis: 100%;
      max-width: none;
    }
  }

  .lb-mh-actions-col {
    display: flex;
    justify-content: flex-end;
  }

  .lb-mh-hide-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid rgba(248, 113, 113, .25);
    background: rgba(239, 68, 68, .08);
    color: rgba(248, 113, 113, .96);
    border-radius: 999px;
    padding: 5px 9px;
    font-size: .68rem;
    font-weight: 900;
    cursor: pointer;
    transition: filter .12s, opacity .12s;
  }

  .lb-mh-hide-btn:hover:not(:disabled) {
    filter: brightness(1.15);
  }

  .lb-mh-hide-btn:disabled {
    opacity: .45;
    cursor: default;
  }

  [data-theme="light"] .lb-mh-admin-tools {
    border-bottom-color: rgba(0, 0, 0, .07);
    background: rgba(0, 0, 0, .025);
  }

  [data-theme="light"] .lb-mh-backfill-form {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .025);
  }

  [data-theme="light"] .lb-mh-mode-trigger {
    border-color: rgba(0,0,0,.09);
    background: rgba(0,0,0,.035);
  }

  [data-theme="light"] .lb-mh-mode-menu {
    border-color: rgba(0,0,0,.10);
    background: linear-gradient(180deg, #ffffff, #f4f6fb);
  }

  @media (max-width: 1100px) {
    .lb-mh-backfill-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 700px) {
    .lb-mh-backfill-grid {
      grid-template-columns: 1fr;
    }
  }

  /* responsive */
  @media (max-width: 700px) {
    .lb-mh-list-head {
      grid-template-columns: 80px 1fr 80px;
    }

    .lb-mh-list-head span:nth-child(n+4) {
      display: none;
    }

    .lb-mh-row {
      grid-template-columns: 80px 1fr 80px;
    }

    .lb-mh-row>*:nth-child(n+4) {
      display: none;
    }
  }

  /* light theme */
  [data-theme="light"] .lb-mh-modal .modal-content {
    border-color: rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-mh-modal .modal-header {
    border-bottom-color: rgba(0, 0, 0, .07);
    background: rgba(0, 0, 0, .02);
  }

  [data-theme="light"] .lb-mh-header-ico {
    background: rgba(168, 85, 247, .08);
    border-color: rgba(168, 85, 247, .18);
    color: rgba(109, 40, 217, .90);
  }

  [data-theme="light"] .lb-mh-list-head {
    background: rgba(0, 0, 0, .04);
    border-bottom-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-mh-row {
    border-bottom-color: rgba(0, 0, 0, .05);
  }

  [data-theme="light"] .lb-mh-row--win {
    background: rgba(34, 197, 94, .03);
  }

  [data-theme="light"] .lb-mh-row--loss {
    background: rgba(239, 68, 68, .025);
  }

  [data-theme="light"] .lb-mh-champ-img {
    border-color: rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-mh-pager {
    border-top-color: rgba(0, 0, 0, .07);
    background: rgba(0, 0, 0, .03);
  }

  [data-theme="light"] .lb-mh-pager-btn {
    border-color: rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-mh-pager-btn:hover:not(:disabled) {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .16);
  }


  .lb-meta-pill--boosters .lb-meta-pill__v {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    line-height: 1.25;
  }


  .lb-r5s-booster-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
  }

  .lb-r5s-booster-tab {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    min-height: 42px;
    padding: .42rem .75rem .42rem .45rem;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.045);
    color: rgba(255,255,255,.78);
    font-weight: 900;
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease, transform .15s ease;
  }

  .lb-r5s-booster-tab:hover {
    transform: translateY(-1px);
    border-color: rgba(124,92,255,.46);
    background: rgba(124,92,255,.11);
  }

  .lb-r5s-booster-tab.is-active {
    color: #fff;
    border-color: rgba(124,92,255,.70);
    background: rgba(124,92,255,.20);
  }

  .lb-r5s-booster-tab img {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    object-fit: cover;
  }

  .lb-r5s-booster-tab small {
    padding: .14rem .45rem;
    border-radius: 999px;
    background: rgba(0,0,0,.20);
    color: rgba(255,255,255,.72);
    font-size: .68rem;
    font-weight: 950;
  }

  .lb-r5s-admin-booster-panel {
    display: none;
  }

  .lb-r5s-admin-booster-panel.is-active {
    display: block;
  }

  .lb-meta-pill--boosters .lb-meta-pill__v {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    line-height: 1.25;
  }


  .lb-r5s-remove-select {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .lb-r5s-remove-select .form-check {
    flex: 1 1 0;
    min-width: 0;
    margin: 0;
    padding: 0;
  }
  .lb-r5s-remove-select .form-check-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }
  .lb-r5s-remove-select .form-check-label {
    width: 100%;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 10px;
    border: 1px solid rgba(255,255,255,.09);
    background: rgba(255,255,255,.035);
    border-radius: 13px;
    font-weight: 850;
    cursor: pointer;
    transition: .16s ease;
  }
  .lb-r5s-remove-select .form-check-label:hover {
    border-color: rgba(124,92,255,.38);
    background: rgba(124,92,255,.10);
    transform: translateY(-1px);
  }
  .lb-r5s-remove-select .form-check-input:checked + .form-check-label {
    border-color: rgba(124,92,255,.72);
    background: rgba(124,92,255,.22);
    box-shadow: 0 0 0 3px rgba(124,92,255,.08) inset;
    color: #fff;
  }
  .lb-r5s-remove-select img {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    object-fit: cover;
    flex: 0 0 auto;
  }
  .lb-r5s-remove-select span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .lb-r5s-remove-select small {
    padding: 2px 7px;
    border-radius: 999px;
    background: rgba(124,92,255,.22);
    color: rgba(255,255,255,.80);
    font-size: 11px;
    font-weight: 950;
    flex: 0 0 auto;
  }
  .lb-r5s-edit-note {
    border: 1px solid rgba(124,92,255,.22);
    background: rgba(124,92,255,.09);
    color: rgba(255,255,255,.74);
    border-radius: 14px;
    padding: 10px 12px;
    font-size: .82rem;
    line-height: 1.35;
  }


  <?php if (!empty($lb_admin_is_ranked_5s)): ?>
  #edit_order_md .order-end-tier,
  #edit_order_md .order-end-division,
  #edit_order_md .order-lp-gain,
  #edit_order_md .order-hours,
  #edit_order_md .order-roles,
  #edit_order_md .order-champions,
  #edit_order_md .order-priority,
  #edit_order_md .order-streaming,
  #edit_order_md .order-solo-only,
  #edit_order_md .order-bonus-win,
  #edit_order_md .order-hidden-duo,
  #edit_order_md .order-flash-position,
  #edit_order_md .order-offline-mode {
    display: none !important;
  }
  #edit_order_md .order-duo {
    opacity: .55;
    pointer-events: none;
  }
  <?php endif; ?>


  #add_booster_md .modal-dialog {
    max-width: 660px;
  }

  #add_booster_md .modal-content {
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid rgba(139, 92, 246, .22);
    background:
      radial-gradient(circle at top left, rgba(124, 92, 255, .18), transparent 38%),
      linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.025));
  }

  .lb-r5s-add-modal-head {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 14px;
    border: 1px solid rgba(139, 92, 246, .24);
    border-radius: 18px;
    background:
      linear-gradient(135deg, rgba(124, 92, 255, .18), rgba(80, 184, 255, .06)),
      rgba(255,255,255,.035);
  }

  .lb-r5s-add-modal-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    color: #fff;
    background: linear-gradient(135deg, #7c5cff, #5c7cff);
    box-shadow: 0 12px 32px rgba(124, 92, 255, .32);
    font-size: 18px;
  }

  .lb-r5s-add-modal-title {
    color: #fff;
    font-weight: 950;
    letter-spacing: .01em;
    line-height: 1.15;
  }

  .lb-r5s-add-modal-sub {
    margin-top: 3px;
    color: rgba(255,255,255,.62);
    font-size: 13px;
    line-height: 1.35;
  }

  .lb-r5s-add-progress {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
  }

  .lb-r5s-add-progress span {
    height: 7px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.06);
  }

  .lb-r5s-add-progress span.is-filled {
    background: linear-gradient(90deg, #7c5cff, #4fd1c5);
    box-shadow: 0 0 18px rgba(124,92,255,.24);
  }

  .lb-r5s-add-label {
    margin-bottom: 9px;
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 950;
    text-transform: uppercase;
    letter-spacing: .08em;
  }

  .lb-r5s-lane-pick-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
  }

  .lb-r5s-lane-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .lb-r5s-lane-card {
    position: relative;
    min-height: 112px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 18px;
    background:
      radial-gradient(circle at top, rgba(124,92,255,.12), transparent 55%),
      rgba(255,255,255,.035);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 9px;
    cursor: pointer;
    color: rgba(255,255,255,.70);
    transition: transform .15s ease, border-color .15s ease, background .15s ease, color .15s ease;
  }

  .lb-r5s-lane-card:hover {
    transform: translateY(-2px);
    border-color: rgba(139,92,246,.48);
    background: rgba(139,92,246,.10);
    color: #fff;
  }

  .lb-r5s-lane-card__icon {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    background: rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.08);
  }

  .lb-r5s-lane-card__icon img {
    width: 30px;
    height: 30px;
    object-fit: contain;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,.45));
  }

  .lb-r5s-lane-card__name {
    font-size: 13px;
    font-weight: 950;
  }

  .lb-r5s-lane-card__check {
    position: absolute;
    top: 9px;
    right: 9px;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    background: rgba(124,92,255,.95);
    color: #fff;
    font-size: 11px;
    opacity: 0;
    transform: scale(.8);
    transition: opacity .15s ease, transform .15s ease;
  }

  .lb-r5s-lane-radio:checked + .lb-r5s-lane-card {
    border-color: rgba(139,92,246,.82);
    background:
      radial-gradient(circle at top, rgba(124,92,255,.26), transparent 60%),
      rgba(124,92,255,.12);
    color: #fff;
    box-shadow: inset 0 0 0 1px rgba(139,92,246,.20), 0 16px 34px rgba(0,0,0,.22);
  }

  .lb-r5s-lane-radio:checked + .lb-r5s-lane-card .lb-r5s-lane-card__check {
    opacity: 1;
    transform: scale(1);
  }

  .lb-r5s-booster-select-wrap .ts-control,
  .lb-r5s-booster-select-wrap .form-select {
    min-height: 52px;
    border-radius: 16px !important;
  }

  @media (max-width: 640px) {
    .lb-r5s-lane-pick-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .lb-r5s-lane-card {
      min-height: 96px;
    }
  }


  <?php if (!empty($lb_admin_is_ranked_5s)): ?>
  #edit_order_md .order-end-tier,
  #edit_order_md .order-end-division,
  #edit_order_md .order-start-lp,
  #edit_order_md .order-start-lp-manual,
  #edit_order_md .order-end-lp-manual,
  #edit_order_md .order-lp-gain,
  #edit_order_md .order-hours,
  #edit_order_md .order-coach-type,
  #edit_order_md .order-champions,
  #edit_order_md .order-queue-type,
  #edit_order_md .order-flash-position,
  #edit_order_md .order-priority,
  #edit_order_md .order-streaming,
  #edit_order_md .order-solo-only,
  #edit_order_md .order-bonus-win,
  #edit_order_md .order-hidden-duo,
  #edit_order_md .order-offline-mode,
  #edit_order_md .order-premium-coaching,
  #edit_order_md .order-options-heading {
    display: none !important;
  }

  #edit_order_md .order-roles label {
    font-size: 0 !important;
  }

  #edit_order_md .order-roles label::after {
    content: "Customer Role";
    font-size: .875rem;
  }

  #edit_order_md .order-roles {
    border-top: 1px solid rgba(255,255,255,.08);
    padding-top: 14px;
  }

  #edit_order_md .order-duo {
    opacity: .55;
    pointer-events: none;
  }
  <?php endif; ?>


  <?php if (!empty($lb_admin_is_ranked_5s)): ?>
  #edit_order_md .order-end-tier,
  #edit_order_md .order-end-division,
  #edit_order_md .order-start-lp,
  #edit_order_md .order-start-lp-manual,
  #edit_order_md .order-end-lp-manual,
  #edit_order_md .order-lp-gain,
  #edit_order_md .order-hours,
  #edit_order_md .order-coach-type,
  #edit_order_md .order-champions,
  #edit_order_md .order-queue-type,
  #edit_order_md .order-flash-position,
  #edit_order_md .order-priority,
  #edit_order_md .order-streaming,
  #edit_order_md .order-solo-only,
  #edit_order_md .order-bonus-win,
  #edit_order_md .order-hidden-duo,
  #edit_order_md .order-offline-mode,
  #edit_order_md .order-premium-coaching,
  #edit_order_md .order-login,
  #edit_order_md .order-password,
  #edit_order_md .order-undercover-winrate,
  #edit_order_md .order-moderate-kda,
  #edit_order_md .order-options-heading {
    display: none !important;
  }
  #edit_order_md .order-duo {
    opacity: .55;
    pointer-events: none;
  }
  <?php endif; ?>


  .rb-r5s-progress-card {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
  }
  .rb-r5s-progress-card .form-label {
    color: rgba(255,255,255,.68);
    font-size: 12px;
    font-weight: 900;
    margin-bottom: 7px;
  }
  @media (max-width: 640px) {
    .rb-r5s-progress-card {
      grid-template-columns: 1fr;
    }
  }


/* ── Discord Banner ── */
.admin-order-view .lb-discord-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.15rem;
  border-radius: 1rem;
  background: rgba(88,101,242,.10);
  border: 1px solid rgba(88,101,242,.28);
}
.admin-order-view .lb-discord-banner__left {
  display: flex;
  align-items: center;
  gap: .85rem;
  min-width: 0;
}
.admin-order-view .lb-discord-banner__logo {
  width: 38px;
  height: 30px;
  flex: 0 0 auto;
}
.admin-order-view .lb-discord-banner__text {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: .2rem;
}
.admin-order-view .lb-discord-banner__text strong {
  font-weight: 950;
  color: #fff;
  line-height: 1.1;
}
.admin-order-view .lb-discord-banner__text span {
  font-size: .86rem;
  color: rgba(255,255,255,.72);
  line-height: 1.35;
}
.admin-order-view .lb-discord-banner__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: .55rem .9rem;
  border-radius: 999px;
  background: #5865f2;
  color: #fff !important;
  font-weight: 950;
  text-decoration: none;
  white-space: nowrap;
  border: 1px solid rgba(255,255,255,.12);
}
.admin-order-view .lb-discord-banner__btn:hover { background: #4752c4; color: #fff !important; }
.admin-order-view [data-theme="light"] .lb-discord-banner { background: #eeeeff; border-color: rgba(88,101,242,.3); }
.admin-order-view [data-theme="light"] .lb-discord-banner__text strong { color: #111; }
.admin-order-view [data-theme="light"] .lb-discord-banner__text span { color: #555; }
@media (max-width:575.98px){
  .admin-order-view .lb-discord-banner { flex-direction: column; align-items: stretch; }
  .admin-order-view .lb-discord-banner__btn { width: 100%; }
}

</style>

<script>
  /**
   * Fallback AJAX handler:
   * If for any reason the global ajax-form handler is not bound, prevent navigation to /ajax
   * and handle forms with class="ajax-form" via XHR (same response contract: sendToast, playSound, redirectUrl, refreshPage, resetForm).
   */
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') return;

    function lbHandleAjaxResponse(res, $form) {
      let response = res;
      try {
        if (typeof res === 'string') response = JSON.parse(res);
      } catch (e) {
        // Not JSON, ignore
        response = null;
      }
      if (!response) return;

      if (response.resetForm && $form && $form[0]) {
        $form[0].reset();
      }

      if (response.sendToast && typeof window.create_toast === 'function') {
        create_toast(response.sendToast.type, response.sendToast.title, response.sendToast.message);
      }

      if (response.playSound) {
        try {
          var audio = new Audio(asset_url + '/core/dash/audio/' + response.playSound + '.mp3');
          audio.play();
        } catch (e) {}
      }

      if (response.redirectUrl) {
        setTimeout(function () {
          window.location.href = response.redirectUrl;
        }, 250);
      } else if (response.refreshPage && !(lbIsRanked5sOrder || response.is_ranked_5s || response.ranked_5s)) {
        setTimeout(function () {
          location.reload();
        }, 250);
      }
    }

    // Bind once
    if (!document.body.dataset.lbAjaxFormsBound) {
      document.body.dataset.lbAjaxFormsBound = "1";

      $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submit = $form.find('button[type="submit"], input[type="submit"]').first();

        // Basic loading state (compatible with your indicator-label / indicator-progress markup)
        if ($submit.length) {
          $submit.prop('disabled', true);
          const $label = $submit.find('.indicator-label');
          const $prog = $submit.find('.indicator-progress');
          if ($label.length && $prog.length) {
            $label.hide();
            $prog.show();
          }
        }

        $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          success: function (res) {
            lbHandleAjaxResponse(res, $form);
          },
          error: function () {
            if (typeof window.create_toast === 'function') {
              create_toast('danger', 'Error', 'Something went wrong. Please try again.');
            }
          },
          complete: function () {
            if ($submit.length) {
              $submit.prop('disabled', false);
              const $label = $submit.find('.indicator-label');
              const $prog = $submit.find('.indicator-progress');
              if ($label.length && $prog.length) {
                $prog.hide();
                $label.show();
              }
            }
          }
        });
      });
    }
  });
</script>



<script>
  // Admin: Apply edited order to what customer sees (updates order_original_data snapshot)
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('lb_sync_customer_view_btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
      const oid = this.getAttribute('data-order-id');
      if (!oid) return;

      const ok = confirm('Apply the current edited order to what the customer sees?\n\nThis will overwrite the customer snapshot.');
      if (!ok) return;

      const fd = new FormData();
      fd.append('action', 'admin_sync_customer_order_view');
      fd.append('order_id', oid);

      this.disabled = true;

      try {
        const res = await fetch("<?= AJAX_URL ?>", { method: 'POST', body: fd, credentials: 'same-origin' });
        let data = null;
        try { data = await res.json(); } catch (e) {
          const txt = await res.text();
          console.log('Non-JSON response:', txt);
        }

        if (data && data.sendToast && typeof window.create_toast === 'function') {
          window.create_toast(
            data.sendToast.type || 'success',
            data.sendToast.title || 'Success',
            data.sendToast.message || 'Updated.'
          );
        }

        if (data && data.refreshPage && !(lbIsRanked5sOrder || data.is_ranked_5s || data.ranked_5s)) {
          setTimeout(function () { location.reload(); }, 250);
        } else if (!data) {
          console.log('Unexpected response:', txt);
          alert('Unexpected response from server (see console).');
        }
      } catch (e) {
        console.error(e);
        if (typeof window.create_toast === 'function') {
          window.create_toast('danger', 'Error', 'Request failed.');
        } else {
          alert('Request failed.');
        }
      } finally {
        this.disabled = false;
      }
    });
  });
</script>




<?= $this->end() ?>


<?php
  $statusKey = strtoupper($data['status'] ?? '');
  $statusClass = match ($statusKey) {
    'COMPLETED' => 'status-completed',
    'IN_PROGRESS' => 'status-inprogress',
    'PAUSED' => 'status-paused',
    'UNPAID' => 'status-unpaid',
    'PAID' => 'status-paid',
    'REFUND' => 'status-refund',
    'REFUNDED' => 'status-refund',
    default => 'status-processing'
  };

  $clientName = $data['client']['username'] ?? 'Unknown';
  $boosterName = $data['booster']['username'] ?? 'Not Assigned';

  // --- Booster banner (same logic as client dashboard) ---
  $claimedBoosterId = (int) ($data['booster_id'] ?? 0);
  $claimedBooster = [];
  if ($claimedBoosterId) {
    $b = db_get_row('boosters', ['id' => $claimedBoosterId]);
    if (!empty($b)) $claimedBooster = array_merge($claimedBooster, (array) $b);

    $p = db_get_row('booster_profiles', ['booster_id' => $claimedBoosterId]);
    if (!empty($p)) $claimedBooster = array_merge($claimedBooster, (array) $p);
  }

  $lb_parse_list = function ($v) {
    if (empty($v)) return [];
    if (is_array($v)) return array_values(array_filter($v));
    $v = trim((string) $v);
    if ($v === '') return [];
    if (strlen($v) > 1 && $v[0] === '[') {
      $j = json_decode($v, true);
      if (is_array($j)) return array_values(array_filter($j));
    }
    if (str_contains($v, '|')) return array_values(array_filter(array_map('trim', explode('|', $v))));
    if (str_contains($v, ',')) return array_values(array_filter(array_map('trim', explode(',', $v))));
    return array_values(array_filter([$v]));
  };

  $lolranks = [
    1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum',
    6 => 'Emerald', 7 => 'Diamond', 8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger'
  ];

  $rankId = 0;
  if (!empty($claimedBooster['lol_rank'])) {
    $rawRank = (string) $claimedBooster['lol_rank'];
    if (str_contains($rawRank, '|')) {
      $rankParts = explode('|', $rawRank);
      $rankId = (int) ($rankParts[0] ?? 0);
    } else {
      $rankId = (int) $rawRank;
    }
  }
  $rankName = $lolranks[$rankId] ?? 'Unranked';
  $rankIcon = ASSET_URL . '/core/main/img/lol/ranks/max/' . $rankId . '.png';

  $roles = $lb_parse_list($claimedBooster['roles'] ?? '');
  $langs = $lb_parse_list($claimedBooster['languages'] ?? '');
  $champs = $lb_parse_list($claimedBooster['champions'] ?? '');

$valAgents = $lb_parse_list($claimedBooster['agents'] ?? '');
$valAgentsLimited = array_values(array_filter(array_slice($valAgents, 0, 4)));
$valAgentsRemaining = max(0, count($valAgents) - count($valAgentsLimited));
$isValBannerOrder = in_array((int) ($data['form_id'] ?? 0), [5, 6, 7, 8, 16], true);

$valAgentsData = [];
try {
  $valAgentsJson = (defined('SYS_PATH') ? SYS_PATH : '') . '/public/uploads/lists/val-agents.json';
  if (defined('SYS_PATH') && file_exists($valAgentsJson)) {
    $valAgentsData = json_decode(file_get_contents($valAgentsJson), true) ?? [];
  }
} catch (Throwable $e) {}

$valRankNames = [0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond', 7 => 'Ascendant', 8 => 'Immortal', 9 => 'Radiant'];
$valRankRaw = trim((string) ($claimedBooster['val_rank'] ?? ($claimedBooster['valorant_rank'] ?? '')));
$valRankTier = 0;
$valRankDiv = 0;
if ($valRankRaw !== '') {
  $valRankParts = explode('|', $valRankRaw);
  $valRankTier = (int) ($valRankParts[0] ?? 0);
  $valRankDiv = (int) ($valRankParts[1] ?? 0);
}
$valRankName = $valRankNames[$valRankTier] ?? 'Unranked';
$valRankDivSuffix = ($valRankTier > 0 && $valRankTier < 7 && $valRankDiv > 0) ? ' ' . (['I', 'II', 'III', 'IV'][$valRankDiv - 1] ?? '') : '';
$valRankLabel = trim($valRankName . $valRankDivSuffix);
$valRankIcon = ASSET_URL . '/core/main/img/val/ranks/mini/' . $valRankTier . '.png';
$bannerRankName = $isValBannerOrder ? $valRankLabel : $rankName;
$bannerRankIcon = $isValBannerOrder ? $valRankIcon : $rankIcon;
$bannerHasRank = $isValBannerOrder ? ($valRankTier > 0) : !empty($rankId);
$bannerRankTitle = $isValBannerOrder ? 'Valorant Rank' : $rankName;

  $roles = array_values(array_filter(array_slice($roles, 0, 5)));
  $langs = array_values(array_filter(array_slice($langs, 0, 5)));
  $champsLimited = array_values(array_filter(array_slice($champs, 0, 4)));
  $champsRemaining = max(0, count($champs) - count($champsLimited));

  $boosterName = $claimedBooster['username'] ?? $boosterName;
  $boosterIcon = $claimedBooster['icon'] ?? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png');
  $boosterCover = $claimedBooster['cover'] ?? null;
  $boosterCover = (!empty($boosterCover) ? $boosterCover : ASSET_URL . '/core/main/img/banners/leona.jpeg');

  // Booster timezone (stored in booster_profiles.timezone as IANA tz, e.g. "Europe/Berlin").
  // If not set, show "N/A".
  $boosterTimezone = trim((string) ($claimedBooster['timezone'] ?? ''));
  if (function_exists('util_format_timezone_display')) {
    $boosterTimezoneDisplay = (string) util_format_timezone_display($boosterTimezone);
  } else {
    if ($boosterTimezone === '') {
      $boosterTimezoneDisplay = 'N/A';
    } else {
      // Fallback: show tz + current UTC offset
      try {
        $dt = new DateTime('now', new DateTimeZone($boosterTimezone));
        $boosterTimezoneDisplay = $boosterTimezone . ' (UTC' . $dt->format('P') . ')';
      } catch (Throwable $e) {
        $boosterTimezoneDisplay = $boosterTimezone;
      }
    }
  }

  // Champion image base (fallback if constant not defined in admin)
  $lb_champ_url = defined('LOL_CHAMP_URL') ? LOL_CHAMP_URL : (ASSET_URL . '/core/main/img/lol/champions');

  $canVisitProfile = (
    $claimedBoosterId > 0
    && (int) ($claimedBooster['verified'] ?? 0) === 1
    && (int) ($claimedBooster['show_profile'] ?? 0) === 1
  );
  $boosterProfileUrl = ADMN_URL . '/booster/' . $claimedBoosterId;

  $lb_admin_is_ranked_5s = ((int)($data['form_id'] ?? 0) === 29 || (string)($data['type'] ?? '') === 'ranked-5s');
  $lb_admin_multi_candidate = in_array((int)($data['form_id'] ?? 0), [4, 19, 29], true);
  $lb_admin_required_boosters = ($lb_admin_multi_candidate && function_exists('lb_multi_booster_required_count'))
    ? lb_multi_booster_required_count((int)($data['id'] ?? 0))
    : max(1, (int)($data['boosters'] ?? 1));
  $lb_admin_is_multi_booster = $lb_admin_is_ranked_5s
    || ($lb_admin_multi_candidate && $lb_admin_required_boosters > 1);
  $lb_admin_ranked_5s_boosters = [];

  // Always load memberships for forms that support teams. Do not depend only
  // on the merged `boosters` view value; legacy orders may not expose it even
  // though multiple ACTIVE memberships exist.
  if ($lb_admin_multi_candidate) {
    if (!empty($data['ranked_5s_boosters']) && is_array($data['ranked_5s_boosters'])) {
      $lb_admin_ranked_5s_boosters = $data['ranked_5s_boosters'];
    } else {
      try {
        global $db;
        $lb_admin_ranked_5s_boosters = $db->run(
          "SELECT ob.booster_id, ob.role, ob.slot_no, ob.cut_percent, ob.claimed_at, b.username, b.icon
             FROM order_boosters ob
             INNER JOIN boosters b ON b.id = ob.booster_id
            WHERE ob.order_id = ?
              AND ob.status = 'ACTIVE'
              AND ob.booster_id IS NOT NULL
              AND ob.booster_id > 0
            ORDER BY ob.slot_no ASC, ob.id ASC",
          (int)($data['id'] ?? 0)
        ) ?: [];
      } catch (Throwable $e) {
        $lb_admin_ranked_5s_boosters = [];
      }
    }

    if (count($lb_admin_ranked_5s_boosters) > 1) {
      $lb_admin_is_multi_booster = true;
    }

    if (empty($lb_admin_ranked_5s_boosters) && $claimedBoosterId > 0) {
      $lb_admin_ranked_5s_boosters[] = [
        'booster_id' => $claimedBoosterId,
        'role' => '',
        'slot_no' => 1,
        'username' => $boosterName,
        'icon' => $boosterIcon ?? '',
      ];
    }
  }

  $lb_admin_ranked_5s_required_boosters = 1;
$lb_admin_ranked_5s_claimed_count = 0;
$lb_admin_ranked_5s_open_slots = false;
$lb_admin_ranked_5s_available_roles = [];
if (!empty($lb_admin_is_multi_booster)) {
    $lb_admin_ranked_5s_required_boosters = function_exists('lb_multi_booster_required_count')
      ? lb_multi_booster_required_count((int)($data['id'] ?? 0))
      : max(1, min(4, (int)($data['boosters'] ?? 1)));
    $lb_admin_ranked_5s_claimed_count = is_array($lb_admin_ranked_5s_boosters) ? count($lb_admin_ranked_5s_boosters) : 0;
    $lb_admin_ranked_5s_open_slots = $lb_admin_ranked_5s_claimed_count < $lb_admin_ranked_5s_required_boosters;
    $lb_admin_ranked_5s_customer_role = function_exists('lb_ranked_5s_normalize_role')
        ? lb_ranked_5s_normalize_role($data['roles'] ?? '')
        : trim((string)($data['roles'] ?? ''));
    $lb_admin_ranked_5s_available_roles = $lb_admin_is_ranked_5s && function_exists('lb_ranked_5s_available_roles')
        ? lb_ranked_5s_available_roles((int)($data['id'] ?? 0), $lb_admin_ranked_5s_customer_role)
        : [];
}

$lb_admin_all_boosters_meta_html = '';
  if ($lb_admin_is_multi_booster && !empty($lb_admin_ranked_5s_boosters)) {
    $metaParts = [];
    foreach ($lb_admin_ranked_5s_boosters as $b5meta) {
      $bid = (int)($b5meta['booster_id'] ?? 0);
      if ($bid <= 0) continue;
      $name = trim((string)($b5meta['username'] ?? ('Booster #' . $bid)));
      $lane = $lb_admin_is_ranked_5s
        ? str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], (string)($b5meta['role'] ?? ''))
        : '';
      $label = htmlspecialchars($name . ($lane !== '' ? ' (' . $lane . ')' : ''), ENT_QUOTES, 'UTF-8');
      $metaParts[] = '<a href="' . ADMN_URL . '/booster/' . $bid . '">' . $label . '</a>';
    }
    $lb_admin_all_boosters_meta_html = implode(', ', $metaParts);
  }

  $lb_admin_intro_boosters = [];
  $lb_admin_build_intro_booster = function (array $src) use (&$lb_admin_intro_boosters, $lb_parse_list, $lolranks, $valRankNames, $isValBannerOrder, $valAgentsData) {
    $bid = (int)($src['booster_id'] ?? $src['id'] ?? 0);
    if ($bid <= 0) return;

    $joined = (array)$src;
    try {
      $b = db_get_row('boosters', ['id' => $bid], 1);
      if (!empty($b)) $joined = array_merge($joined, (array)$b);
      $p = db_get_row('booster_profiles', ['booster_id' => $bid], 1);
      if (!empty($p)) $joined = array_merge($joined, (array)$p);
    } catch (Throwable $e) {}

    $rankLocalId = 0;
    if (!empty($joined['lol_rank'])) {
      $raw = (string)$joined['lol_rank'];
      if (str_contains($raw, '|')) {
        $parts = explode('|', $raw);
        $rankLocalId = (int)($parts[0] ?? 0);
      } else {
        $rankLocalId = (int)$raw;
      }
    }
    $rankLocalName = $lolranks[$rankLocalId] ?? 'Unranked';
    $rankLocalIcon = ASSET_URL . '/core/main/img/lol/ranks/max/' . $rankLocalId . '.png';

    $valRawLocal = trim((string)($joined['val_rank'] ?? ($joined['valorant_rank'] ?? '')));
    $valTierLocal = 0;
    $valDivLocal = 0;
    if ($valRawLocal !== '') {
      $valPartsLocal = explode('|', $valRawLocal);
      $valTierLocal = (int)($valPartsLocal[0] ?? 0);
      $valDivLocal = (int)($valPartsLocal[1] ?? 0);
    }
    $valNameLocal = $valRankNames[$valTierLocal] ?? 'Unranked';
    $valSuffixLocal = ($valTierLocal > 0 && $valTierLocal < 7 && $valDivLocal > 0) ? ' ' . (['I', 'II', 'III', 'IV'][$valDivLocal - 1] ?? '') : '';
    $valLabelLocal = trim($valNameLocal . $valSuffixLocal);
    $valIconLocal = ASSET_URL . '/core/main/img/val/ranks/mini/' . $valTierLocal . '.png';

    $lane = (string)($src['role'] ?? '');
    if (str_starts_with($lane, 'ClashSlot')) $lane = '';
    $rolesLocal = $lane !== '' ? [$lane] : $lb_parse_list($joined['roles'] ?? '');
    $rolesLocal = array_values(array_filter(array_slice($rolesLocal, 0, 5)));
    $langsLocal = array_values(array_filter(array_slice($lb_parse_list($joined['languages'] ?? ''), 0, 5)));
    $champsLocal = $lb_parse_list($joined['champions'] ?? '');
    $champsLimitedLocal = array_values(array_filter(array_slice($champsLocal, 0, 4)));
    $champsRemainingLocal = max(0, count($champsLocal) - count($champsLimitedLocal));
    $valAgentsLocal = $lb_parse_list($joined['agents'] ?? '');
    $valAgentsLimitedLocal = array_values(array_filter(array_slice($valAgentsLocal, 0, 4)));
    $valAgentsRemainingLocal = max(0, count($valAgentsLocal) - count($valAgentsLimitedLocal));

    $tz = trim((string)($joined['timezone'] ?? ''));
    if (function_exists('util_format_timezone_display')) {
      $tzDisplay = (string)util_format_timezone_display($tz);
    } else {
      $tzDisplay = $tz !== '' ? $tz : 'N/A';
    }

    $lb_admin_intro_boosters[] = [
      'id' => $bid,
      'name' => $joined['username'] ?? ('Booster #' . $bid),
      'icon' => $joined['icon'] ?? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png'),
      'cover' => !empty($joined['cover']) ? $joined['cover'] : (ASSET_URL . '/core/main/img/banners/leona.jpeg'),
      'profile_url' => ADMN_URL . '/booster/' . $bid,
      'can_visit_profile' => true,
      'rank_name' => $isValBannerOrder ? $valLabelLocal : $rankLocalName,
      'rank_icon' => $isValBannerOrder ? $valIconLocal : $rankLocalIcon,
      'rank_title' => $isValBannerOrder ? 'Valorant Rank' : $rankLocalName,
      'has_rank' => $isValBannerOrder ? ($valTierLocal > 0) : ($rankLocalId > 0),
      'timezone' => $tzDisplay,
      'roles' => $rolesLocal,
      'langs' => $langsLocal,
      'champs' => $champsLocal,
      'champs_limited' => $champsLimitedLocal,
      'champs_remaining' => $champsRemainingLocal,
      'val_agents' => $valAgentsLocal,
      'val_agents_limited' => $valAgentsLimitedLocal,
      'val_agents_remaining' => $valAgentsRemainingLocal,
      'lane' => $lane,
    ];
  };

  if ($lb_admin_is_multi_booster && !empty($lb_admin_ranked_5s_boosters)) {
    foreach ($lb_admin_ranked_5s_boosters as $b5intro) {
      $lb_admin_build_intro_booster((array)$b5intro);
    }
  } elseif ($claimedBoosterId > 0) {
    $lb_admin_build_intro_booster(array_merge($claimedBooster, ['booster_id' => $claimedBoosterId]));
  }

  $showClaimedBanner = (($claimedBoosterId > 0 || !empty($lb_admin_intro_boosters)) && in_array($data['status'], ['IN_PROGRESS', 'PAUSED', 'PAID', 'COMPLETED'], true));
  $showWaitingBanner = (empty($claimedBoosterId) && empty($lb_admin_intro_boosters) && in_array($data['status'], ['PAID', 'PROCESSING'], true));

  $price = $data['price'];
  $currency = util_format_currency_display($data['currency']);
  $priceDisplay = util_format_price_display($price);
  $priceText = $currency . $priceDisplay;

  // Show the real payable booster cut.
  // If no booster has accepted the order yet, show the current dynamic pool cut
  // instead of the empty stored snapshot, so Admin does not see 0,00.
  if (in_array($data['status'], ['PAID', 'PROCESSING', 'IN_PROGRESS', 'COMPLETED', 'PAUSED'], true)) {
    $hasAcceptedBooster = !empty($data['booster_id']);
    $storedCut = (isset($data['booster_cut']) && is_numeric($data['booster_cut']))
      ? (float) $data['booster_cut']
      : null;

    if (!$hasAcceptedBooster) {
      // PAID / PROCESSING orders are still open in the booster pool.
      // Use the same effective cut as the booster panel, so admin view
      // immediately reflects manual order cut changes above the dynamic max.
      if (function_exists('calculate_effective_booster_cut_percent')) {
        $cutPercent = (float) calculate_effective_booster_cut_percent($data);
      } elseif (function_exists('calculate_booster_cut')) {
        $dynamicCut = (float) calculate_booster_cut($data, 'percent');
        $cutPercent = ($storedCut !== null && $storedCut > $dynamicCut) ? $storedCut : $dynamicCut;
      } else {
        $cutPercent = $storedCut ?? 0.0;
      }
    } else {
      // Once a booster accepted the order, show the stored payout snapshot.
      $cutPercent = $storedCut ?? 0.0;
    }
    if (!empty($lb_admin_is_multi_booster)) {
      $cutPercent = 50.0;
    }

    $cutAmount = ((float) $cutPercent / 100) * (float) $price;
    $lb_ranked_5s_booster_count = 1;
    $lb_ranked_5s_cut_per_booster = null;
    if (!empty($lb_admin_is_multi_booster)) {
      $lb_ranked_5s_booster_count = (int)($data['boosters'] ?? 0);
      if ($lb_ranked_5s_booster_count <= 0) {
        try {
          $r5opts = db_get_row('order_options', ['order_id' => (int)($data['id'] ?? 0), 'select' => 'boosters,hours'], 1);
          if (is_array($r5opts)) {
            $lb_ranked_5s_booster_count = (int)($r5opts['boosters'] ?? $r5opts['hours'] ?? 1);
          }
        } catch (Throwable $e) {}
      }
      $lb_ranked_5s_booster_count = max(1, min(4, $lb_ranked_5s_booster_count > 0 ? $lb_ranked_5s_booster_count : 1));
      $lb_ranked_5s_cut_per_booster = $cutAmount / $lb_ranked_5s_booster_count;
    }
  } else {
    $cutAmount = null;
    $cutPercent = null;
    $lb_ranked_5s_booster_count = 1;
    $lb_ranked_5s_cut_per_booster = null;
  }
?>

<div class="order-page-wrap admin-order-view">

<div class="lb-head card mb-4">
  <div class="lb-head__top">
    <div class="lb-head__left">
      <div class="lb-head__icon" style="position:relative;">
        <?php
        // PHP < 8 fallback
        if (!function_exists('str_ends_with')) {
          function str_ends_with($haystack, $needle) {
            $haystack = (string)$haystack;
            $needle   = (string)$needle;
            if ($needle === '') return true;
            return substr($haystack, -strlen($needle)) === $needle;
          }
        }

        $icon = trim((string)($data['icon'] ?? ''));

        $svgBaseUrl = defined('ASSET_URL')
          ? (ASSET_URL . '/website/images/boost-forms/boost-type-icons')
          : '/public/assets/website/images/boost-forms/boost-type-icons';

        if ($icon !== '' && str_ends_with(strtolower($icon), '.svg')) {
          $safe = basename($icon); // nur Dateiname
          echo '<img class="boost-form-svg" src="' . htmlspecialchars($svgBaseUrl . '/' . $safe, ENT_QUOTES) . '" alt="" style="width:1.35rem;height:1.35rem;display:block;">';
        } else {
          echo '<i class="fa-duotone ' . htmlspecialchars($icon, ENT_QUOTES) . '" aria-hidden="true"></i>';
        }
        $lbHeaderGameIcon = util_game_icon_url((string)($data['game'] ?? ''));
        if ($lbHeaderGameIcon !== '') {
          echo '<img src="' . htmlspecialchars($lbHeaderGameIcon, ENT_QUOTES) . '" alt="" style="position:absolute;right:-5px;bottom:-5px;width:20px;height:20px;object-fit:contain;border-radius:6px;background:#11131a;border:2px solid #11131a;">';
        }
      ?></div>

      <div class="lb-head__title">
        <div class="lb-head__title-row">
          <h1 class="lb-head__h1">
            <?= util_format_boost_overview($data['game'], $data['type'], $data) ?>
          </h1>
          <span class="lb-head__id d-none d-lg-inline">#<?= (int) $data['id'] ?></span>
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
      <div class="page-header-actions d-flex align-items-center gap-2">
        <?php if (!empty($review)): ?>
                        <button type="button" class="btn btn-primary btn-sm lb-actions-btn" data-bs-toggle="modal"
                            data-bs-target="#leave_review_md">
                            <i class="fa-duotone fa-star-half-stroke me-1"></i> View Client Review
                        </button>
                    <?php endif; ?>
        <div class="dropdown nav-scroller-dropdown">
                        <button type="button" class="btn lb-order-actions-btn lb-actions-btn" id="profileDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="lb-order-actions-btn__ico" aria-hidden="true">
                              <i class="fa-solid fa-sliders"></i>
                            </span>
                            <span class="lb-order-actions-btn__txt d-none d-lg-inline">Order Actions</span>
                            <span class="lb-order-actions-btn__chev" aria-hidden="true">
                              <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="profileDropdown">
                            <span class="dropdown-header">Actions</span>
                            <?php switch ($data['status']) {
                                case 'UNPAID':
                                    ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#set_order_paid_md">
                                        <i class="fa-duotone fa-circle-check dropdown-item-icon"></i> Set Paid
                                    </a>
                                    <?php if (!empty($data['booster_id'])): ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#unassign_booster_md">
                                        <i class="fa-duotone fa-user-minus dropdown-item-icon"></i> Unassign Booster
                                    </a>
                                <?php endif; ?>
                                 <?php break;
                                
                                case 'PROCESSING': ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#set_order_unpaid_md">
                                        <i class="fa-duotone fa-circle-x dropdown-item-icon"></i> Set Unpaid
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_booster_md">
                                        <i class="fa-duotone fa-user-plus dropdown-item-icon"></i> Add Booster
                                    </a>
                                    <a class="dropdown-item" href="#" data-id="<?= $data['order_id'] ?>"
                                        data-action="admin_repost_order">
                                        <i class="fa-duotone fa-send dropdown-item-icon"></i> Repost Order
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pause_order_md">
                                        <i class="fa-duotone fa-pause dropdown-item-icon"></i> Pause Order
                                    </a>
                                    <?php break;
                                    case 'PAID': ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#set_order_unpaid_md">
                                        <i class="fa-duotone fa-circle-x dropdown-item-icon"></i> Set Unpaid
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_booster_md">
                                        <i class="fa-duotone fa-user-plus dropdown-item-icon"></i> Add Booster
                                    </a>
                                    <a class="dropdown-item" href="#" data-id="<?= $data['order_id'] ?>"
                                        data-action="admin_repost_order">
                                        <i class="fa-duotone fa-send dropdown-item-icon"></i> Repost Order
                                    </a>
                                    
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pause_order_md">
                                        <i class="fa-duotone fa-pause dropdown-item-icon"></i> Pause Order
                                    </a><?php break;
                                case 'IN_PROGRESS': ?>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#complete_order_md">
                                        <i class="fa-duotone fa-circle-check dropdown-item-icon"></i> Set Completed
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pause_order_md">
                                        <i class="fa-duotone fa-pause dropdown-item-icon"></i> Pause Order
                                    </a>
                                    <?php if (!empty($lb_admin_is_multi_booster) && !empty($lb_admin_ranked_5s_open_slots)): ?>
                                      <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_booster_md">
                                          <i class="fa-duotone fa-user-plus dropdown-item-icon"></i> Add Booster
                                      </a>
                                      <a class="dropdown-item" href="#" data-id="<?= $data['order_id'] ?>"
                                          data-action="admin_repost_order">
                                          <i class="fa-duotone fa-send dropdown-item-icon"></i> Repost Order
                                      </a>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="#"
                                        data-bs-toggle="modal" data-bs-target="#remove_booster_progress_payment_md"
                                        data-order-id="<?= (int)$data['order_id'] ?>"
                                        data-current-price="<?= util_format_price_input($data['price']) ?>">
                                        <i class="fa-duotone fa-circle-x dropdown-item-icon"></i> Remove Booster (Progress Payment)
                                    </a>
                                    <?php break;
                                case 'PAUSED': ?>
                                    <a class="dropdown-item" href="#" data-id="<?= $data['order_id'] ?>"
                                        data-action="admin_resume_order">
                                        <i class="fa-duotone fa-play dropdown-item-icon"></i> Resume Order
                                    </a>
                                    <?php break;
                                case 'COMPLETED': ?>
                                    <a class="dropdown-item" href="#" data-id="<?= $data['order_id'] ?>"
                                        data-action="admin_cancel_order_completion">
                                        <i class="fa-duotone fa-circle-x dropdown-item-icon"></i> Cancel Completion
                                    </a>
                                    <?php break; ?>

                            <?php } ?>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_order_md">
                                <i class="fa-duotone fa-pen dropdown-item-icon"></i> Edit Order
                            </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#admin_create_invoice_md">
                                <i class="fa-duotone fa-file-invoice dropdown-item-icon"></i> Create Invoice
                            </a>
                            <span class="dropdown-header">Refund</span>
                            <?php if (in_array($data['status'], ['REFUND','REFUNDED'], true)): ?>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#set_order_unpaid_md">
                                    <i class="fa-duotone fa-circle-x dropdown-item-icon"></i> Set Unpaid
                                </a>
                            <?php else: ?>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#set_order_refunded_md">
                                    <i class="fa-duotone fa-rotate-left dropdown-item-icon"></i> Set Refunded
                                </a>
                            <?php endif; ?>

                            <?php if (in_array(strtolower(ADMIN_DATA['email']), $allowedEmails)): ?>
                                <span class="dropdown-header">Danger Zone</span>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_order_md">
                                    <i class="fa-duotone fa-trash dropdown-item-icon"></i> Delete Order
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
      </div>
    </div>
  </div>

  <div class="lb-head__meta">
    <div class="lb-meta-pill">
      <div class="lb-meta-pill__k">Order</div>
      <div class="lb-meta-pill__v">#<?= (int) $data['id'] ?></div>
    </div>

    <div class="lb-meta-pill">
      <div class="lb-meta-pill__k">Client</div>
      <div class="lb-meta-pill__v">
        <a href="<?= ADMN_URL ?>/client/<?= $data['client_id'] ?>"><?= htmlspecialchars($clientName) ?></a>
      </div>
    </div>

    <div class="lb-meta-pill lb-meta-pill--boosters">
      <div class="lb-meta-pill__k"><?= !empty($lb_admin_is_ranked_5s) ? 'Boosters' : 'Booster' ?></div>
      <div class="lb-meta-pill__v">
        <?php if (!empty($lb_admin_is_multi_booster) && !empty($lb_admin_all_boosters_meta_html)): ?>
          <?= $lb_admin_all_boosters_meta_html ?>
        <?php elseif (!empty($data['booster_id'])): ?>
          <a href="<?= ADMN_URL ?>/booster/<?= $data['booster_id'] ?>"><?= htmlspecialchars($boosterName) ?></a>
        <?php else: ?>
          <span class="opacity-75">Not Assigned</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="lb-meta-pill">
      <div class="lb-meta-pill__k">Price</div>
      <div class="lb-meta-pill__v">
        <?= $priceText ?>
        <?php if (!empty($lb_admin_is_multi_booster) && !is_null($lb_ranked_5s_cut_per_booster)): ?>
          <span class="opacity-75 ms-1">
            (<?= $currency . util_format_price_display($lb_ranked_5s_cut_per_booster) ?> / Booster)
          </span>
        <?php elseif (!is_null($cutAmount) && !is_null($cutPercent)): ?>
          <span class="opacity-75 ms-1">
            (<?= $currency . util_format_price_display($cutAmount) ?>×<?= $cutPercent ?>%)
          </span>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($invoice['coins_used']) && (float) $invoice['coins_used'] != 0.00): ?>
      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Coins</div>
        <div class="lb-meta-pill__v"><i class="fa-duotone fa-coins me-1"></i><?= $invoice['coins_used'] ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($total_addon_price)): ?>
      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Add-Ons</div>
        <div class="lb-meta-pill__v">
          <?= util_format_currency_display($data['currency']) . util_format_price_display($total_addon_price) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="lb-meta-pill">
      <div class="lb-meta-pill__k">Coupon</div>
      <div class="lb-meta-pill__v">🏷️ <?= util_format_discount_display($data['id']) ?></div>
    </div>
  </div>
</div>

<?php if (!empty($original_order)): ?>
  <div class="card mb-4 lb-original-order-banner">
    <div class="card-body d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
      <div class="d-flex flex-column">
        <div class="d-flex align-items-center flex-wrap gap-2">
          <div class="text-uppercase fw-bold" style="letter-spacing:.08em;font-size:.8rem;">Original order</div>
          <span class="badge bg-secondary">Immutable</span>

          <?php if (!empty($original_meta) && !empty($original_meta['created_at'])): ?>
            <span class="text-muted small">Saved: <?= util_format_date_display($original_meta['created_at']) ?></span>
          <?php endif; ?>

          <?php
            $customer_is_synced = (!empty($customer_meta) && !empty($customer_meta['customer_updated_at']));
          ?>

          <span class="badge <?= $customer_is_synced ? 'bg-primary' : 'bg-dark' ?>">
            Customer sees <?= $customer_is_synced ? 'UPDATED' : 'ORIGINAL' ?>
          </span>

          <?php if ($customer_is_synced): ?>
            <span class="text-muted small">Customer synced: <?= util_format_date_display($customer_meta['customer_updated_at']) ?></span>
          <?php endif; ?>
        </div>

        <div class="mt-2">
          <div class="fw-semibold"><?= util_format_boost_overview($original_order['game'], $original_order['type'], $original_order) ?></div>
          <?php if (!empty($original_order['price']) && !empty($original_order['currency'])): ?>
            <div class="text-muted small mt-1">Price: <?= util_format_currency_display($original_order['currency']) . util_format_price_display($original_order['price']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="ms-lg-auto">
        <button type="button" class="btn btn-sm btn-outline-primary" id="lb_sync_customer_view_btn" data-order-id="<?= (int)$data['id'] ?>">
          Apply edits to customer
        </button>
      </div>
    </div>
  </div>
<?php endif; ?>


<div class="row g-4 align-items-start">
<div class="col-12 col-lg-8">
        <?php if ($data['status'] === 'PAUSED'): ?>
          <div class="lb-banner lb-banner--paused mb-3" role="alert">
            <div class="lb-banner__icon" aria-hidden="true">
              <svg class="lb-icon lb-icon--pause" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M7 5.5c0-.83.67-1.5 1.5-1.5h1c.83 0 1.5.67 1.5 1.5v13c0 .83-.67 1.5-1.5 1.5h-1C7.67 20 7 19.33 7 18.5v-13Zm6 0c0-.83.67-1.5 1.5-1.5h1c.83 0 1.5.67 1.5 1.5v13c0 .83-.67 1.5-1.5 1.5h-1c-.83 0-1.5-.67-1.5-1.5v-13Z" fill="currentColor"/>
              </svg>
            </div>
            <div class="lb-banner__text">
              <div class="lb-banner__title">This order is currently paused</div>
              <div class="lb-banner__sub">The booster is not expected to queue while the order is paused.</div>
            </div>
          </div>
        <?php endif; ?>

        
        

<?php if ((int)($data['form_id'] ?? 0) === 29): ?>
  <div class="lb-discord-banner mb-3">
    <div class="lb-discord-banner__left">
      <svg class="lb-discord-banner__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36"><path fill="#5865f2" d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/></svg>
      <div class="lb-discord-banner__text">
        <strong>Ranked 5s Discord</strong>
        <span>Customer and boosters should join Discord before the games and use the voice channel for the 5 stack.</span>
      </div>
    </div>
    <a class="lb-discord-banner__btn" href="https://lolboost.gg/streaming" target="_blank" rel="noopener">Open Discord Guide</a>
  </div>
<?php endif; ?>

<?php if ($showClaimedBanner): ?>
  <?php if (!empty($lb_admin_is_multi_booster) && count($lb_admin_intro_boosters) > 1): ?>
    <div class="lb-r5s-booster-tabs mb-3" role="tablist" aria-label="Order boosters">
      <?php foreach ($lb_admin_intro_boosters as $idx => $introTab): ?>
        <?php $tabLane = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], (string)($introTab['lane'] ?? '')); ?>
        <button type="button" class="lb-r5s-booster-tab <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-admin-booster-tab="<?= (int)$idx ?>">
          <img src="<?= htmlspecialchars($introTab['icon']) ?>" alt="">
          <span><?= htmlspecialchars($introTab['name']) ?></span>
          <?php if ($tabLane !== ''): ?><small><?= htmlspecialchars($tabLane) ?></small><?php endif; ?>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php foreach ($lb_admin_intro_boosters as $idx => $intro): ?>
    <?php
      $introName = $intro['name'];
      $introIcon = $intro['icon'];
      $introCover = $intro['cover'];
      $introProfileUrl = $intro['profile_url'];
      $introCanVisitProfile = !empty($intro['can_visit_profile']);
      $introHasRank = !empty($intro['has_rank']);
      $introRankTitle = $intro['rank_title'];
      $introRankIcon = $intro['rank_icon'];
      $introRankName = $intro['rank_name'];
      $introTimezone = $intro['timezone'];
      $introRoles = $intro['roles'];
      $introLangs = $intro['langs'];
      $introChamps = $intro['champs'];
      $introChampsLimited = $intro['champs_limited'];
      $introChampsRemaining = $intro['champs_remaining'];
      $introValAgents = $intro['val_agents'];
      $introValAgentsLimited = $intro['val_agents_limited'];
      $introValAgentsRemaining = $intro['val_agents_remaining'];
    ?>
    <div class="lb-r5s-admin-booster-panel <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-admin-booster-panel="<?= (int)$idx ?>">
      <div class="card booster-intro-card mb-4">
        <div class="booster-intro-bg" style="background-image:url('<?= htmlspecialchars($introCover) ?>');"></div>

        <div class="card-body booster-intro-body">
          <div class="booster-intro-top">
            <div class="booster-intro-left">
              <div class="booster-intro-avatar">
                <span class="booster-intro-glow"></span>
                <img src="<?= htmlspecialchars($introIcon) ?>" alt="Booster Avatar">
              </div>

              <div class="booster-intro-main">
                <div class="booster-intro-name">
                  <span><?= htmlspecialchars($introName) ?></span>
                </div>

                <?php if ($introHasRank): ?>
                  <div class="booster-rank-pill" title="<?= htmlspecialchars($introRankTitle) ?>">
                    <img src="<?= htmlspecialchars($introRankIcon) ?>" alt="Rank">
                    <span><?= htmlspecialchars($introRankName) ?></span>
                  </div>
                <?php endif; ?>

                <?php if (!empty($introTimezone)): ?>
                  <div class="booster-rank-pill" title="Booster Timezone">
                    <i class="fa-duotone fa-clock"></i>
                    <span><?= htmlspecialchars($introTimezone) ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="booster-intro-right">
              <?php if ($introCanVisitProfile): ?>
                <a class="visit-profile-btn" href="<?= htmlspecialchars($introProfileUrl) ?>">
                  <i class="fa-duotone fa-user"></i>
                  <span>View Profile</span>
                </a>
              <?php endif; ?>
            </div>
          </div>

          <div class="booster-intro-cards">
            <div class="booster-intro-block">
              <div class="booster-intro-label"><?= $isValBannerOrder ? 'AGENTS' : 'CHAMPIONS' ?></div>
              <div class="booster-intro-champs">
                <?php if ($isValBannerOrder): ?>
                  <?php if (!empty($introValAgentsLimited)): ?>
                    <?php foreach ($introValAgentsLimited as $agent):
                      $agentKey = trim((string) $agent);
                      $agentIcon = $valAgentsData[$agentKey]['icon'] ?? '';
                      $agentName = $valAgentsData[$agentKey]['name'] ?? $agentKey; ?>
                      <?php if ($agentIcon): ?>
                        <img class="champ" src="<?= htmlspecialchars($agentIcon) ?>" alt="<?= htmlspecialchars($agentName) ?>" title="<?= htmlspecialchars($agentName) ?>">
                      <?php else: ?>
                        <span class="booster-intro-tag"><?= htmlspecialchars($agentName) ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($introValAgentsRemaining > 0): ?><span class="more">+<?= $introValAgentsRemaining ?></span><?php endif; ?>
                  <?php else: ?>
                    <span class="na">N/A</span>
                  <?php endif; ?>
                <?php else: ?>
                  <?php if (!empty($introChampsLimited)): ?>
                    <?php foreach ($introChampsLimited as $champion): ?>
                      <img class="champ" src="<?= $lb_champ_url . '/' . $champion . '.png' ?>" alt="<?= htmlspecialchars($champion) ?>" title="<?= htmlspecialchars($champion) ?>">
                    <?php endforeach; ?>
                    <?php if ($introChampsRemaining > 0): ?><span class="more">+<?= $introChampsRemaining ?></span><?php endif; ?>
                  <?php else: ?>
                    <span class="na">N/A</span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>

            <div class="booster-intro-block">
              <div class="booster-intro-label"><?= $isValBannerOrder ? 'VALORANT RANK' : 'LANES' ?></div>
              <?php if ($isValBannerOrder): ?>
                <div class="booster-intro-rank-mini">
                  <?php if ($introHasRank): ?>
                    <img class="rank-mini-icon" src="<?= htmlspecialchars($introRankIcon) ?>" alt="<?= htmlspecialchars($introRankName) ?>">
                    <span><?= htmlspecialchars($introRankName) ?></span>
                  <?php else: ?>
                    <span class="na">N/A</span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="booster-intro-roles">
                  <?php if (!empty($introRoles)): ?>
                    <?php foreach ($introRoles as $role): ?>
                      <span class="role-pill" title="<?= htmlspecialchars($role) ?>">
                        <img src="<?= ASSET_URL . '/core/main/img/lol/roles/' . $role . '.png' ?>" alt="<?= htmlspecialchars($role) ?>">
                      </span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="na">N/A</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="booster-intro-block">
              <div class="booster-intro-label">LANGUAGES</div>
              <div class="booster-intro-langs">
                <?php if (!empty($introLangs)): ?>
                  <?php foreach ($introLangs as $language):
                    $langKey = strtolower(trim((string) $language)); ?>
                    <img class="flag" src="<?= ASSET_URL . '/core/main/img/languages/' . htmlspecialchars($langKey) . '.png' ?>" alt="<?= htmlspecialchars($language) ?>">
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="na">N/A</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php elseif ($showWaitingBanner): ?>
  <div class="card waiting-banner mb-4">
    <div class="card-body">
      <div class="waiting-avatar-wrapper">
        <span class="waiting-pulse-ring"></span>
        <div class="waiting-avatar">
          <i class="fa-duotone fa-user-clock"></i>
        </div>
      </div>

      <div class="flex-grow-1">
        <div class="waiting-title">Waiting for a booster</div>
        <div class="waiting-sub">You will be notified as soon as someone accepts it.</div>
      </div>
    </div>
  </div>
<?php endif; ?>

        <div class="card order-chat-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center gap-2">
            <h4 class="card-header-title mb-0">Order Chat</h4>
            <?php if (!empty($data['client_id'])): ?>
              <button type="button" class="btn btn-sm btn-primary js-admin-poke-client lb-chat-poke-btn" data-ref-type="order" data-id="<?= (int)$data['id'] ?>">
                <i class="fa-duotone fa-hand-point-up me-1"></i> Poke Client
              </button>
            <?php endif; ?>
          </div>

          <div class="card-body chat-bg" id="chat_messages"></div>

          
<div class="card-footer">
  <form class="row gx-2 align-items-center" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="admin_order_chat_send">
    <input type="hidden" name="order_id" value="<?= (int)$data['id'] ?>">

    <div class="col">
      <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message">
    </div>

    <div class="col-auto d-flex align-items-center gap-2">
      <input type="file" class="d-none" id="lbChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">

      <button type="button" class="btn btn-sm btn-secondary btn-chat-icon" id="lbChatAttachBtn" aria-label="Attach image" title="Attach image">
        <i class="fa-duotone fa-paperclip"></i>
      </button>

      <button type="button" class="btn btn-sm btn-secondary lb-emoji-btn d-none d-md-inline-flex" id="lbEmojiBtn" aria-label="Emojis" title="Emojis">
        <i class="fa-regular fa-face-smile"></i>
      </button>

      <button type="submit" class="btn btn-sm btn-primary btn-chat-icon" id="lbChatSendBtn" aria-label="Send" title="Send">
        <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
        <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
      </button>
    </div>

    <div class="col-12">
      <div class="lb-chat-error text-danger small mt-1 d-none" id="lbChatError"></div>

      <div class="lb-chat-attach-preview d-none mt-2" id="lbChatPreview">
        <div class="lb-chat-attach-preview__thumb">
          <img src="" alt="attachment preview" id="lbChatPreviewImg">
        </div>
        <div class="lb-chat-attach-preview__meta">
          <div class="lb-chat-attach-preview__title">Image ready to send</div>
          <div class="lb-chat-attach-preview__name" id="lbChatPreviewName"></div>
        </div>
        <button type="button" class="lb-chat-attach-preview__remove" id="lbChatRemoveBtn" aria-label="Remove attachment" title="Remove">
          <i class="fa-duotone fa-xmark"></i>
        </button>
      </div>

      <div class="text-muted small mt-2">
        Tip: You can also paste a screenshot with <strong>Ctrl</strong> + <strong>V</strong>.
      </div>
    </div>
  </form>

  <div id="lbEmojiPicker" class="lb-emoji-picker d-none" role="dialog" aria-label="Emoji Picker">
    <div class="lb-emoji-picker__head">
      <input type="text" id="lbEmojiSearch" class="lb-emoji-picker__search" placeholder="Search emojis…">
    </div>
    <div class="lb-emoji-picker__tabs" id="lbEmojiTabs">
      <button type="button" class="lb-emoji-picker__tab is-active" data-cat="recent" title="Recent">🕘</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="smileys" title="Smileys">😀</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="gestures" title="Gestures">🖐️</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="animals" title="Animals">🐱</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="food" title="Food">🍎</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="activities" title="Activities">⚽</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="travel" title="Travel">✈️</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="objects" title="Objects">💡</button>
      <button type="button" class="lb-emoji-picker__tab" data-cat="symbols" title="Symbols">❤️</button>
    </div>
    <div class="lb-emoji-picker__grid" id="lbEmojiGrid"></div>
  </div>
</div>
        </div>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-header-title mb-0">Booster Segments</h4>
    <span class="text-muted small">Auto-created from transfers / price reductions</span>
  </div>
  <div class="card-body">
    <?php
      $segments = db_get_rows('order_booster_segments', [
        'order_id' => $data['id'],
        'order'    => 'id,asc',
      ], true);

      $rankNames = [
        0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'GrandMaster',10=>'Challenger'
      ];
      $divNames = [1=>'IV',2=>'III',3=>'II',4=>'I'];

      $fmtMoney = function($cents){
        $c = (int)$cents;
        return '€' . number_format($c/100, 2, '.', '');
      };

      $fmtDate = function($dt){
        if (empty($dt)) return '-';
        $ts = strtotime($dt);
        if ($ts === false) return (string)$dt;
        return date('d-m-y H-i-s', $ts);
      };

      $fmtRank = function($tier, $div) use ($rankNames, $divNames) {
        if ($tier === null || $tier === '') return null;
        $t = (int)$tier;
        $name = $rankNames[$t] ?? ('Tier ' . $t);
        if ($t > 7) return $name; // Master+ no division
        $d = (int)$div;
        $roman = $divNames[$d] ?? null;
        return $roman ? ($name . ' ' . $roman) : $name;
      };

      $fmtProgress = function($seg) use ($fmtRank) {
        if (!empty($seg['progress_note'])) return $seg['progress_note'];
        $type = $seg['progress_type'] ?? 'custom';
        $from = !empty($seg['progress_from']) ? json_decode($seg['progress_from'], true) : null;
        $to   = !empty($seg['progress_to']) ? json_decode($seg['progress_to'], true) : null;

        if ($type === 'rank' && is_array($from) && is_array($to)) {
          
          $ft = $fmtRank($from['tier'] ?? null, $from['division'] ?? null);
          $tt = $fmtRank($to['tier'] ?? null, $to['division'] ?? null);
          $flp = $from['lp'] ?? null;
          $tlp = $to['lp'] ?? null;
          $left = $ft ?: '-';
          $right = $tt ?: '-';
          if ($flp !== null) $left .= ' [' . $flp . ' LP]';
          if ($tlp !== null) $right .= ' [' . $tlp . ' LP]';
          return $left . ' → ' . $right;

        }

        // Simple fallbacks for common types
        if ($type === 'win' && is_array($from) && is_array($to)) {
          $a = $from['wins'] ?? null; $b = $to['wins'] ?? null;
          if ($a !== null && $b !== null) return 'Wins ' . $a . ' → ' . $b;
        }
        if ($type === 'match' && is_array($from) && is_array($to)) {
          $a = $from['matches'] ?? null; $b = $to['matches'] ?? null;
          if ($a !== null && $b !== null) return 'Matches ' . $a . ' → ' . $b;
        }
        if ($type === 'hours' && is_array($from) && is_array($to)) {
          $a = $from['hours'] ?? null; $b = $to['hours'] ?? null;
          if ($a !== null && $b !== null) return 'Hours ' . $a . ' → ' . $b;
        }

        return '-';
      };

      // Booster cache
      $boosterCache = [];
      $getBoosterName = function($bid) use (&$boosterCache) {
        $bid = (int)$bid;
        if ($bid <= 0) return '—';
        if (!isset($boosterCache[$bid])) {
          $row = db_get_row('boosters', ['id' => $bid], true);
          $boosterCache[$bid] = $row ? ($row['username'] ?? ('#' . $bid)) : ('#' . $bid);
        }
        return $boosterCache[$bid];
      };

      $sumSegment = 0; $sumPayout = 0;
      if (!empty($segments)) {
        foreach ($segments as $s) { $sumSegment += (int)($s['segment_value_eur'] ?? 0); $sumPayout += (int)($s['payout_eur'] ?? 0); }
      }
    ?>

    <?php if (!empty($segments)): ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Date</th>
              <th>Booster</th>
              <th>Progress</th>
              <th class="text-end">Segment</th>
              <th class="text-end">Cut</th>
              <th class="text-end">Payout</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($segments as $seg): ?>
              <tr>
                <td class="text-muted small"><?= htmlspecialchars($fmtDate($seg['created_at'] ?? null)) ?></td>
                <td><strong><?= htmlspecialchars($getBoosterName($seg['booster_id'] ?? 0)) ?></strong></td>
                <td><?= htmlspecialchars($fmtProgress($seg)) ?></td>
                <td class="text-end"><?= $fmtMoney($seg['segment_value_eur'] ?? 0) ?></td>
                <td class="text-end"><?= (int)($seg['cut_percent'] ?? 0) ?>%</td>
                <td class="text-end"><strong><?= $fmtMoney($seg['payout_eur'] ?? 0) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Total</th>
              <th class="text-end"><?= $fmtMoney($sumSegment) ?></th>
              <th></th>
              <th class="text-end"><?= $fmtMoney($sumPayout) ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php else: ?>
      <div class="text-center">
        <i class="fa-duotone fa-info-circle fs-3 text-muted"></i>
        <span class="d-block text-center fw-bold mt-1">No Segments Found</span>
        <div class="text-muted small">Segments are created automatically when a booster is removed/transferred.</div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-header-title mb-0">Booster Payments</h4>
</div>
  <div class="card-body">
    <?php
      $payments = db_get_rows('booster_payments', [
        'note'  => (string)$data['id'],
        'order' => 'id,asc',
      ], true);

      $payByBooster = [];
      if (!empty($payments)) {
        foreach ($payments as $p) {
          $bid = (int)($p['booster_id'] ?? 0);
          if (!isset($payByBooster[$bid])) $payByBooster[$bid] = ['rows'=>[], 'net'=>0];
          $payByBooster[$bid]['rows'][] = $p;
          $payByBooster[$bid]['net'] += (int)($p['amount'] ?? 0);
        }
      }

      $fmtMoneyPay = function($cents, $currency = 'EUR'){
        $c = (int)$cents;
        $sym = $currency === 'EUR' ? '€' : util_format_currency_display($currency);
        return $sym . number_format($c/100, 2, '.', '');
      };
    ?>

    <?php if (!empty($payments)): ?>
      <?php foreach ($payByBooster as $bid => $g): ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <strong><?= htmlspecialchars($getBoosterName($bid)) ?></strong>
            <span>Net: <strong><?= $fmtMoneyPay($g['net'], $g['rows'][0]['currency'] ?? 'EUR') ?></strong></span>
          </div>
          <div class="table-responsive mt-2">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th class="text-end">Amount</th>
                  <th>Currency</th>
                  <th>Sender</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($g['rows'] as $r): ?>
                  <tr>
                    <td class="text-muted small"><?= htmlspecialchars($fmtDate($r['created_at'] ?? null)) ?></td>
                    <td><?= htmlspecialchars($r['type'] ?? '-') ?></td>
                    <td class="text-end"><?= $fmtMoneyPay($r['amount'] ?? 0, $r['currency'] ?? 'EUR') ?></td>
                    <td><?= htmlspecialchars($r['currency'] ?? '-') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($r['sender'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center">
        <i class="fa-duotone fa-info-circle fs-3 text-muted"></i>
        <span class="d-block text-center fw-bold mt-1">No Payments Found</span>
      </div>
    <?php endif; ?>
  </div>
</div>




<div class="card mb-4 lb-notes-card">
            <div class="card-header">
                <h4 class="card-header-title">Order Notes</h4>
            </div>
            <div class="card-body">
                <?php $notes = db_get_rows('order_notes', [
                    'order_id' => $data['id'],
                ]); ?>
                <?php if (!empty($notes)): ?>
                    <div class="lb-notes-list">
                        <?php foreach ($notes as $note): ?>
                            <div class="lb-note-item">
                                <a class="lb-note-edit" href="#" data-bs-toggle="modal" data-bs-target="#edit_note_md"
                                   data-note-id="<?= $note['id'] ?>"
                                   data-note-type="<?= htmlspecialchars($note['type'], ENT_QUOTES) ?>"
                                   data-note-body="<?= htmlspecialchars($note['order_note'], ENT_QUOTES) ?>"
                                   aria-label="Edit note">
                                    <i class="fa-duotone fa-pen-to-square"></i>
                                </a>
                                <div class="lb-note-body">
                                    <div class="lb-note-text"><?= $note['order_note'] ?></div>
                                </div>
                                <span class="lb-note-badge"><?= ucfirst($note['type']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fa-duotone fa-info-circle fs-3 text-muted"></i>
                        <span class="d-block text-center fw-bold mt-1">No Notes Found</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                    data-bs-target="#create_note_md">New Note</button>
            </div>
        </div>

        <div class="card lb-tip-card">
            <div class="card-header">
                <h4 class="card-header-title">Client Tip</h4>
            </div>
            <div class="card-body">
                <?php $tip = db_get_row('tips', ['order_id' => $data['id']]); ?>
                <?php if (!empty($tip)): ?>
                    <div class="tip-row">
                        <div class="tip-ico"><i class="fa-light fa-pen-circle"></i></div>
                        <div class="tip-txt">
                            <div class="tip-amount"><?php echo util_format_currency_display($tip['currency']) . util_format_price_display($tip['amount']); ?></div>
                            <div class="tip-desc"><?php echo htmlspecialchars($tip['description'] ?? '-', ENT_QUOTES); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fa-duotone fa-info-circle fs-3 text-muted"></i>
                        <span class="d-block text-center fw-bold mt-1">No Tip was Left for this Order</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</div>

<div class="col-12 col-lg-4 admin-order-sidebar">

<?php if (!empty($lb_duo_timer)): ?>
<?php
  $lbDtRemaining = (int)$lb_duo_timer['remaining_seconds'];
  $lbDtUsed      = (int)$lb_duo_timer['used_seconds'];
  $lbDtBooked    = (int)$lb_duo_timer['booked_seconds'];
  $lbDtProgress  = (int)$lb_duo_timer['progress_percent'];
  $lbDtRunning   = !empty($lb_duo_timer['is_running']) ? 'true' : 'false';
  $lbDtStatus    = htmlspecialchars((string)$lb_duo_timer['status_label'], ENT_QUOTES);
  $lbDtHours     = (int)($data['hours'] ?? 0);
?>
<div class="card lb-duo-timer-card mb-4">
  <div class="card-header">
    <h4 class="card-header-title"><i class="fa-duotone fa-hourglass-clock me-2"></i>Duo Pass Timer</h4>
  </div>
  <div class="card-body lb-duo-timer-body">
    <div class="lb-dt-top">
      <div class="lb-dt-left">
        <div class="lb-dt-label">Time Left</div>
        <div class="lb-dt-countdown" id="lbdt-countdown"><?= lb_duo_timer_human($lbDtRemaining) ?></div>
        <div class="lb-dt-sub">based on <?= $lbDtHours ?> hour<?= $lbDtHours !== 1 ? 's' : '' ?> booked</div>
      </div>
      <div class="lb-dt-ring">
        <svg width="62" height="62" viewBox="0 0 62 62">
          <circle cx="31" cy="31" r="25" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="4"/>
          <circle id="lbdt-ring" cx="31" cy="31" r="25" fill="none" stroke="#a29bfe" stroke-width="4"
            stroke-dasharray="157.1" stroke-dashoffset="<?= round(157.1 * (1 - $lbDtProgress / 100), 2) ?>"
            stroke-linecap="round" transform="rotate(-90 31 31)"/>
        </svg>
        <div class="lb-dt-ring-pct" id="lbdt-pct"><?= $lbDtProgress ?>%</div>
      </div>
    </div>
    <div class="lb-dt-foot">
      <div class="lb-dt-status">
        <span class="lb-dt-dot" id="lbdt-dot"></span>
        <span class="lb-dt-status-text" id="lbdt-status"><?= $lbDtStatus ?></span>
      </div>
      <div class="lb-dt-elapsed"><span id="lbdt-elapsed"><?= lb_duo_timer_human($lbDtUsed) ?></span> elapsed</div>
    </div>
  </div>
</div>
<script>
(function(){
  if(window.__lbDuoTimerInit) return;
  window.__lbDuoTimerInit = true;
  var rem = <?= $lbDtRemaining ?>;
  var elap = <?= $lbDtUsed ?>;
  var booked = <?= $lbDtBooked ?>;
  var running = <?= $lbDtRunning ?>;
  var statusLabel = '<?= $lbDtStatus ?>';
  function pad(n){ return String(n).padStart(2,'0'); }
  function fmt(s){
    s = Math.max(0, s);
    var h = Math.floor(s/3600), m = Math.floor((s%3600)/60), sc = s%60;
    if(h > 0) return pad(h)+':'+pad(m)+':'+pad(sc);
    if(m > 0) return pad(m)+':'+pad(sc);
    return '00:'+pad(sc);
  }
  function setStatus(label){
    var dot = document.getElementById('lbdt-dot');
    var txt = document.getElementById('lbdt-status');
    if(!dot || !txt) return;
    if(label === 'Running'){ dot.style.background='#00b894'; txt.style.color='#00b894'; }
    else if(label === 'Paused'){ dot.style.background='#fdcb6e'; txt.style.color='#fdcb6e'; }
    else if(label === 'Finished'){ dot.style.background='#e17055'; txt.style.color='#e17055'; }
    else { dot.style.background='rgba(255,255,255,.4)'; txt.style.color='rgba(255,255,255,.4)'; }
    txt.textContent = label;
  }
  function tick(){
    if(running && rem > 0){ rem = Math.max(0, rem-1); elap++; }
    var pct = booked > 0 ? Math.round(((booked-rem)/booked)*100) : 0;
    var cd = document.getElementById('lbdt-countdown');
    var el = document.getElementById('lbdt-elapsed');
    var pc = document.getElementById('lbdt-pct');
    var rg = document.getElementById('lbdt-ring');
    if(cd) cd.textContent = fmt(rem);
    if(el) el.textContent = fmt(elap);
    if(pc) pc.textContent = pct+'%';
    if(rg){
      var circ = 2*Math.PI*25;
      rg.setAttribute('stroke-dasharray', circ.toFixed(1));
      rg.setAttribute('stroke-dashoffset', (circ*(1-pct/100)).toFixed(1));
    }
    if(rem <= 0 && running){ running = false; setStatus('Finished'); }
  }
  setStatus(statusLabel);
  setInterval(tick, 1000);
})();
</script>
<?php endif; ?>

<?php if (strtolower((string) ($data['game'] ?? '')) === 'lol'): // Riot API tracking (LoL only) ?>
<div class="card mb-4 lb-op-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="lb-op-header-ico">
              <i class="fa-duotone fa-chart-line-up"></i>
            </div>
            <h4 class="card-header-title mb-0">Order Progress</h4>
          </div>
          <?php if (($data['type'] ?? 'rank') !== 'coaching'): ?>
          <button type="button" class="lb-op-refresh-btn" id="refreshProgressBtn" aria-label="Refresh progress"
            title="Sync with Riot API">
            <i class="fa-duotone fa-arrows-rotate"></i>
          </button>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php
          $progress_data = is_array($data['progress'] ?? null) ? $data['progress'] : [];

          // Always show the W/L record from order_matches, not from the cached order_progress row.
          // order_progress can stay stale after older sync logic, while order_matches already contains
          // the actual stored games shown in Match History.
          $lb_match_record = ['wins' => null, 'losses' => null];
          $lb_progress_order_id = (int)($data['id'] ?? 0);
          if ($lb_progress_order_id > 0) {
            try {
              if (function_exists('riot_get_order_match_record')) {
                $lb_match_record = riot_get_order_match_record($lb_progress_order_id, $db ?? null);
              } elseif (isset($db) && is_object($db) && method_exists($db, 'run')) {
                $lb_record_rows = $db->run(
                  "SELECT
                      COALESCE(SUM(CASE
                        WHEN (COALESCE(is_remake, 0) = 1
                              OR COALESCE(game_ended_in_early_surrender, 0) = 1
                              OR (COALESCE(duration, 0) > 0 AND COALESCE(duration, 0) < 300)) THEN 0
                        WHEN COALESCE(won, 0) = 1 THEN 1
                        ELSE 0
                      END), 0) AS wins,
                      COALESCE(SUM(CASE
                        WHEN (COALESCE(is_remake, 0) = 1
                              OR COALESCE(game_ended_in_early_surrender, 0) = 1
                              OR (COALESCE(duration, 0) > 0 AND COALESCE(duration, 0) < 300)) THEN 0
                        WHEN COALESCE(won, 0) = 0 THEN 1
                        ELSE 0
                      END), 0) AS losses
                   FROM order_matches
                   WHERE order_id = ?",
                  $lb_progress_order_id
                );
                $lb_match_record = is_array($lb_record_rows) ? ($lb_record_rows[0] ?? []) : [];
              }
            } catch (Throwable $e) {
              $lb_match_record = ['wins' => null, 'losses' => null];
            }
          }
          if (isset($lb_match_record['wins'])) {
            $progress_data['wins'] = (int)$lb_match_record['wins'];
          }
          if (isset($lb_match_record['losses'])) {
            $progress_data['losses'] = (int)$lb_match_record['losses'];
          }

          $op_is_classic_rank = in_array(strtolower(trim((string)($data['game'] ?? ''))), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true);
          $format_rank = static function ($tier, $division, $lp) use ($op_is_classic_rank): string {
            if ($op_is_classic_rank && is_numeric($tier)) {
              return util_lol_classic_rank_name((int)$tier);
            }
            $tier = trim((string) ($tier ?? ''));
            $division = trim((string) ($division ?? ''));
            $lp_val = ($lp === null || $lp === '') ? null : (int) $lp;

            if ($tier === '') {
              return 'Unranked';
            }

            $label = ucfirst(strtolower($tier));
            if ($division !== '') {
              $label .= ' ' . $division;
            }

            if ($lp_val !== null) {
              $label .= ' · ' . $lp_val . ' LP';
            }

            return $label;
          };

          $tier_id_map = [
            'IRON' => 1,
            'BRONZE' => 2,
            'SILVER' => 3,
            'GOLD' => 4,
            'PLATINUM' => 5,
            'EMERALD' => 6,
            'DIAMOND' => 7,
            'MASTER' => 8,
            'GRANDMASTER' => 9,
            'CHALLENGER' => 10,
          ];
          $start_tier_raw = strtoupper(trim((string) ($progress_data['start_tier'] ?? '')));
          $current_tier_raw = strtoupper(trim((string) ($progress_data['current_tier'] ?? '')));
          $start_tier_id = $op_is_classic_rank && is_numeric($start_tier_raw) ? (int)$start_tier_raw : ($tier_id_map[$start_tier_raw] ?? 0);
          $current_tier_id = $op_is_classic_rank && is_numeric($current_tier_raw) ? (int)$current_tier_raw : ($tier_id_map[$current_tier_raw] ?? 0);
          $start_rank_img = $op_is_classic_rank ? util_lol_classic_rank_img($start_tier_id) : ASSET_URL . '/core/main/img/lol/ranks/max/' . $start_tier_id . '.png';
          $current_rank_img = $op_is_classic_rank ? util_lol_classic_rank_img($current_tier_id) : ASSET_URL . '/core/main/img/lol/ranks/max/' . $current_tier_id . '.png';

          $start_rank_text = $format_rank(
            $progress_data['start_tier'] ?? null,
            $progress_data['start_division'] ?? null,
            $progress_data['start_lp'] ?? null
          );
          $current_rank_text = $format_rank(
            $progress_data['current_tier'] ?? null,
            $progress_data['current_division'] ?? null,
            $progress_data['current_lp'] ?? null
          );
          $last_sync_text = !empty($progress_data['last_sync_at'])
            ? (string) $progress_data['last_sync_at']
            : 'Never';
          $wins_text = isset($progress_data['wins']) ? (string) ((int) $progress_data['wins']) : '0';
          $losses_text = isset($progress_data['losses']) ? (string) ((int) $progress_data['losses']) : '0';
          $wins_total = (int) ($progress_data['wins'] ?? 0);
          $losses_total = (int) ($progress_data['losses'] ?? 0);
          $record_games = $wins_total + $losses_total;

          $winrate_pct = $record_games > 0
            ? number_format(($wins_total / $record_games) * 100, 1) . '%'
            : '–';
          $winrate_bar_pct = $record_games > 0
            ? number_format(($wins_total / $record_games) * 100, 1)
            : '0';
          $wr_bar_class = ($record_games > 0 && ($wins_total / $record_games) >= 0.6)
            ? 'lb-op-wr-bar-fill--good'
            : '';
          $record_tone_class = 'text-muted';
          if ($record_games > 0) {
            $record_tone_class = (($wins_total / $record_games) >= 0.6) ? 'text-success' : '';
          }

          $order_type      = (string) ($data['type'] ?? 'rank');
          $op_form_id      = (int) ($data['form_id'] ?? 0);
          $op_is_win_boost_form = ($op_form_id === 2);
          $op_is_placements_form = ($op_form_id === 3);
          $op_is_pro_games_form = in_array($op_form_id, [26, 35], true);
          $op_is_duo_pass_form  = ($op_form_id === 27);

          $op_mode = 'rank';
          if ($op_is_duo_pass_form) {
            $op_mode = 'duo_time';
          } elseif ($op_is_placements_form || $op_is_pro_games_form || in_array($order_type, ['win', 'arena', 'placement', 'normal', 'match', 'clash'], true)) {
            $op_mode = 'count';
          } elseif ($order_type === 'coaching') {
            $op_mode = 'coaching';
          } elseif (in_array($order_type, ['level', 'mastery'], true)) {
            $op_mode = 'level';
          }

          $op_is_win_type  = in_array($order_type, ['win', 'arena'], true) || $op_is_win_boost_form;
          $op_base_target = (int) ($data['matches'] ?? 0);
          // Win Boost (boost_forms.id = 2): progress is based on net wins.
          // Example: 3 wins / 3 losses = 0 wins done; target stays at the ordered wins.
          $op_target = $op_base_target;
          $op_hours_target = (int) ($data['hours'] ?? 0);
          $op_played = $op_is_win_boost_form
            ? max(0, $wins_total - $losses_total)
            : ($op_is_pro_games_form ? ($wins_total + $losses_total) : ($op_is_win_type ? $wins_total : ($wins_total + $losses_total)));
          $op_count_pct = ($op_target > 0) ? min(100.0, round(($op_played / $op_target) * 100, 1)) : 0;
          $op_level_start  = (string) ($data['start_tier'] ?? '');
          $op_level_end    = (string) ($data['end_tier'] ?? '');
          $count_label     = $op_is_placements_form ? 'Placements' : ($op_is_pro_games_form ? 'Games' : ($op_is_win_type ? 'Wins' : 'Games'));

          $op_duo_booked_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['booked_seconds'] : max(0, $op_hours_target * 3600);
          $op_duo_used_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['used_seconds'] : 0;
          $op_duo_remaining_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['remaining_seconds'] : max(0, $op_duo_booked_seconds - $op_duo_used_seconds);
          $op_duo_pct = $op_duo_booked_seconds > 0 ? min(100.0, round(($op_duo_used_seconds / $op_duo_booked_seconds) * 100, 1)) : 0;
          $op_duo_status = !empty($lb_duo_timer) ? (string) $lb_duo_timer['status_label'] : 'Not Started';
          $op_duo_played_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($op_duo_used_seconds) : (string) $op_duo_used_seconds;
          $op_duo_target_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($op_duo_booked_seconds) : (string) $op_duo_booked_seconds;
          $op_duo_remaining_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($op_duo_remaining_seconds) : (string) $op_duo_remaining_seconds;
          ?>

          <?php if ($op_mode === 'rank'): ?>
            <!-- Rank comparison -->
            <div class="lb-op-rank-row">
              <div class="lb-op-rank-box">
                <img class="lb-op-rank-img" id="riotProgressStartRankImg"
                  src="<?= htmlspecialchars($start_rank_img, ENT_QUOTES) ?>" alt="">
                <div class="lb-op-rank-name" id="riotProgressStartRank"><?= esc($start_rank_text) ?></div>
                <div class="lb-op-rank-label">Start</div>
              </div>
              <div class="lb-op-rank-arrow">
                <i class="fa-duotone fa-arrow-right-long"></i>
              </div>
              <div class="lb-op-rank-box lb-op-rank-box--current">
                <img class="lb-op-rank-img" id="riotProgressCurrentRankImg"
                  src="<?= htmlspecialchars($current_rank_img, ENT_QUOTES) ?>" alt="">
                <div class="lb-op-rank-name" id="riotProgressCurrentRank"><?= esc($current_rank_text) ?></div>
                <div class="lb-op-rank-label">Current</div>
              </div>
            </div>

            <!-- W / L / WR stats -->
            <div class="lb-op-stats">
              <div class="lb-op-stat lb-op-stat--win">
                <div class="lb-op-stat-val" id="riotProgressWins"><?= esc($wins_text) ?></div>
                <div class="lb-op-stat-lbl">Wins</div>
              </div>
              <div class="lb-op-stat lb-op-stat--loss">
                <div class="lb-op-stat-val" id="riotProgressLosses"><?= esc($losses_text) ?></div>
                <div class="lb-op-stat-lbl">Losses</div>
              </div>
              <div class="lb-op-stat lb-op-stat--wr">
                <div class="lb-op-stat-val <?= esc($record_tone_class) ?>" id="riotProgressRecord">
                  <?= esc($winrate_pct) ?>
                </div>
                <div class="lb-op-stat-lbl">Winrate</div>
              </div>
            </div>

          <?php elseif ($op_mode === 'duo_time'): ?>
            <!-- Duo Pass time progress -->
            <div class="lb-op-count-row">
              <div class="lb-op-count-box">
                <div class="lb-op-count-val" id="riotProgressPlayed"><?= esc($op_duo_played_text) ?></div>
                <div class="lb-op-count-label">Time Played</div>
              </div>
              <div class="lb-op-count-sep">/</div>
              <div class="lb-op-count-box">
                <div class="lb-op-count-val lb-op-count-val--target" id="riotProgressTarget"><?= esc($op_duo_target_text) ?></div>
                <div class="lb-op-count-label">Target</div>
              </div>
            </div>
            <div class="lb-op-count-progress mb-2">
              <div class="lb-op-count-progress-fill<?= $op_duo_pct >= 100 ? ' lb-op-count-progress-fill--done' : '' ?>"
                id="riotProgressCountBar" style="width: <?= esc($op_duo_pct) ?>%"></div>
            </div>
            <div class="lb-op-count-rank">
              <div class="lb-op-count-rank-copy">
                <div class="lb-op-count-rank-kicker">Time Left</div>
                <div class="lb-op-count-rank-name" id="riotProgressCurrentRank"><?= esc($op_duo_remaining_text) ?> · <?= esc($op_duo_status) ?></div>
              </div>
            </div>

          <?php elseif ($op_mode === 'count'): ?>
            <!-- Count progress: win / placements / normal / match / clash / arena / pro games -->
            <div class="lb-op-count-row">
              <div class="lb-op-count-box">
                <div class="lb-op-count-val" id="riotProgressPlayed"><?= esc($op_played) ?></div>
                <div class="lb-op-count-label"><?= esc($count_label) ?> Played</div>
              </div>
              <div class="lb-op-count-sep">/</div>
              <div class="lb-op-count-box">
                <div class="lb-op-count-val lb-op-count-val--target" id="riotProgressTarget"><?= esc($op_target) ?></div>
                <div class="lb-op-count-label">Target</div>
              </div>
            </div>
            <div class="lb-op-count-progress mb-2">
              <div class="lb-op-count-progress-fill<?= $op_count_pct >= 100 ? ' lb-op-count-progress-fill--done' : '' ?>"
                id="riotProgressCountBar" style="width: <?= esc($op_count_pct) ?>%"></div>
            </div>
            <?php if ($op_is_win_boost_form): ?>
              <div class="lb-op-count-rank">
                <img class="lb-op-count-rank-img" id="riotProgressCurrentRankImg"
                  src="<?= htmlspecialchars($current_rank_img, ENT_QUOTES) ?>" alt="">
                <div class="lb-op-count-rank-copy">
                  <div class="lb-op-count-rank-kicker">Current Rank</div>
                  <div class="lb-op-count-rank-name" id="riotProgressCurrentRank"><?= esc($current_rank_text) ?></div>
                </div>
              </div>
            <?php endif; ?>

            <!-- W / L / WR stats -->
            <div class="lb-op-stats">
              <div class="lb-op-stat lb-op-stat--win">
                <div class="lb-op-stat-val" id="riotProgressWins"><?= esc($wins_text) ?></div>
                <div class="lb-op-stat-lbl">Wins</div>
              </div>
              <div class="lb-op-stat lb-op-stat--loss">
                <div class="lb-op-stat-val" id="riotProgressLosses"><?= esc($losses_text) ?></div>
                <div class="lb-op-stat-lbl">Losses</div>
              </div>
              <div class="lb-op-stat lb-op-stat--wr">
                <div class="lb-op-stat-val <?= esc($record_tone_class) ?>" id="riotProgressRecord">
                  <?= esc($winrate_pct) ?>
                </div>
                <div class="lb-op-stat-lbl">Winrate</div>
              </div>
            </div>

          <?php elseif ($op_mode === 'coaching'): ?>
            <!-- Coaching: no Riot tracking -->
            <div class="lb-op-coaching-info">
              <div class="lb-op-coaching-hours"><?= esc($op_hours_target) ?></div>
              <div class="lb-op-coaching-label">Hours Purchased</div>
            </div>
            <div class="lb-op-coaching-note">
              <i class="fa-duotone fa-circle-info me-2"></i>
              Coaching orders don't have automatic Riot API tracking.
            </div>

          <?php elseif ($op_mode === 'level'): ?>
            <!-- Level / Mastery progression -->
            <div class="lb-op-level-row">
              <div class="lb-op-level-box">
                <div class="lb-op-level-ico">
                  <i class="fa-duotone <?= $order_type === 'mastery' ? 'fa-chess-knight' : 'fa-user-astronaut' ?>"></i>
                </div>
                <div class="lb-op-level-val"><?= esc($op_level_start) ?></div>
                <div class="lb-op-level-label">Start</div>
              </div>
              <div class="lb-op-rank-arrow">
                <i class="fa-duotone fa-arrow-right-long"></i>
              </div>
              <div class="lb-op-level-box">
                <div class="lb-op-level-ico">
                  <i class="fa-duotone <?= $order_type === 'mastery' ? 'fa-chess-knight' : 'fa-user-astronaut' ?>"></i>
                </div>
                <div class="lb-op-level-val"><?= esc($op_level_end) ?></div>
                <div class="lb-op-level-label">Target</div>
              </div>
            </div>

            <!-- W / L / WR stats -->
            <div class="lb-op-stats">
              <div class="lb-op-stat lb-op-stat--win">
                <div class="lb-op-stat-val" id="riotProgressWins"><?= esc($wins_text) ?></div>
                <div class="lb-op-stat-lbl">Wins</div>
              </div>
              <div class="lb-op-stat lb-op-stat--loss">
                <div class="lb-op-stat-val" id="riotProgressLosses"><?= esc($losses_text) ?></div>
                <div class="lb-op-stat-lbl">Losses</div>
              </div>
              <div class="lb-op-stat lb-op-stat--wr">
                <div class="lb-op-stat-val <?= esc($record_tone_class) ?>" id="riotProgressRecord">
                  <?= esc($winrate_pct) ?>
                </div>
                <div class="lb-op-stat-lbl">Winrate</div>
              </div>
            </div>

          <?php endif; ?>

          <?php if ($op_mode !== 'coaching'): ?>
            <!-- Footer: last sync -->
            <div class="lb-op-footer">
              <div class="lb-op-footer-item">
                <span class="lb-op-footer-label">Last Sync</span>
                <span class="lb-op-footer-val" id="riotProgressLastSync"><?= esc($last_sync_text) ?></span>
              </div>
            </div>

            <!-- Hidden cursor element (used by JS, not displayed) -->
            <span id="riotProgressLastMatch" hidden></span>

            <!-- Sync status -->
            <div id="riotProgressSyncState" class="lb-op-sync-state" aria-live="polite"></div>

            <?php if (!empty($riot_tracking_warning ?? null)): ?>
              <div class="lb-op-warning mt-3">
                <i class="fa-duotone fa-circle-exclamation me-2"></i>
                <?= esc($riot_tracking_warning) ?>
              </div>
            <?php elseif (empty(trim((string) ($data['ign'] ?? '')))): ?>
              <div class="lb-op-no-riot mt-3">
                <i class="fa-duotone fa-circle-info me-2"></i>
                Add Riot ID to enable automatic tracking.
              </div>
            <?php endif; ?>

            <?php if (!empty(trim((string) ($data['ign'] ?? '')))): ?>
              <a href="#" class="lb-op-view-history" id="openMatchHistoryModalBtn" data-bs-toggle="modal"
                data-bs-target="#matchHistoryModal">
                <div class="lb-op-view-history-left">
                  <i class="fa-duotone fa-swords"></i>
                  <span>Match History</span>
                  <span id="lbMhCountBadge" class="lb-op-history-count" style="display:none"></span>
                </div>
                <i class="fa-solid fa-chevron-right lb-op-view-history-arrow"></i>
              </a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty(trim((string) ($data['ign'] ?? '')))): ?>
        <div class="modal fade lb-mh-modal" id="matchHistoryModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                  <div class="lb-mh-header-ico">
                    <i class="fa-duotone fa-swords"></i>
                  </div>
                  <h4 class="modal-title mb-0">Match History</h4>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span id="lbMhTotal" class="badge bg-soft-secondary text-body fw-700 small" style="display:none"></span>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <div class="lb-mh-admin-tools">
                  <button type="button" class="btn btn-sm btn-outline-primary lb-mh-backfill-toggle" id="lbMhBackfillToggle">
                    <i class="fa-duotone fa-rotate"></i> Sync missing games
                  </button>
                  <div class="lb-mh-backfill-form" id="lbMhBackfillForm">
                    <div class="lb-mh-backfill-grid">
                      <div class="lb-mh-backfill-field">
                        <label for="lbMhBackfillRiotId">Riot ID</label>
                        <input type="text" class="form-control" id="lbMhBackfillRiotId" placeholder="BoosterName#EUW" autocomplete="off" spellcheck="false">
                      </div>
                      <div class="lb-mh-backfill-field">
                        <label for="lbMhBackfillMode">Mode</label>
                        <div class="lb-mh-mode-select" data-lb-mode-select>
                          <select class="form-select lb-mh-mode-select__native" id="lbMhBackfillMode" aria-hidden="true" tabindex="-1">
                            <option value="duo">Duo</option>
                            <option value="solo">Solo</option>
                          </select>
                          <button type="button" class="lb-mh-mode-trigger" data-lb-mode-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-lb-mode-label>Duo</span>
                            <i class="fa-duotone fa-chevron-down"></i>
                          </button>
                          <div class="lb-mh-mode-menu" data-lb-mode-menu role="listbox">
                            <button type="button" class="lb-mh-mode-option is-selected" data-lb-mode-option="duo" role="option" aria-selected="true">
                              <span>Duo</span><i class="fa-duotone fa-check"></i>
                            </button>
                            <button type="button" class="lb-mh-mode-option" data-lb-mode-option="solo" role="option" aria-selected="false">
                              <span>Solo</span><i class="fa-duotone fa-check"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="lb-mh-backfill-field lb-mh-backfill-field--range">
                        <label for="lbMhBackfillRangeOpen">Date &amp; time range</label>
                        <button type="button" class="lb-datetime-trigger lb-range-trigger" id="lbMhBackfillRangeOpen">
                          <span class="lb-datetime-placeholder" id="lbMhBackfillRangeLabel">Select Berlin date range and time</span>
                          <i class="fa-duotone fa-calendar-clock"></i>
                        </button>
                        <input type="hidden" id="lbMhBackfillStart">
                        <input type="hidden" id="lbMhBackfillEnd">
                      </div>
                      <div class="lb-range-backdrop" id="lbMhBackfillRangeModal" hidden>
                        <div class="lb-range-card" role="dialog" aria-modal="true" aria-labelledby="lbMhBackfillRangeTitle">
                          <div class="lb-range-head"><div><h3 id="lbMhBackfillRangeTitle">Sync date range</h3><p>First select the day range, then set the Berlin time window.</p></div><button type="button" class="lb-range-close" id="lbMhBackfillRangeClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>
                          <div class="lb-range-section"><div class="lb-range-step"><span>1</span> Choose day range</div><div class="lb-range-grid"><label class="lb-range-input"><span>From day</span><input type="text" id="lbMhRangeStartDate" inputmode="numeric" placeholder="DD.MM.YYYY" autocomplete="off"></label><label class="lb-range-input"><span>To day</span><input type="text" id="lbMhRangeEndDate" inputmode="numeric" placeholder="DD.MM.YYYY" autocomplete="off"></label></div><div class="lb-range-presets lb-range-presets--days"><button type="button" data-range-days="today">Today</button><button type="button" data-range-days="yesterday">Yesterday</button><button type="button" data-range-days="3">Last 3 days</button><button type="button" data-range-days="7">Last 7 days</button></div></div>
                          <div class="lb-range-section"><div class="lb-range-step"><span>2</span> Choose time</div><div class="lb-range-grid"><label class="lb-range-input"><span>Start time</span><input type="time" id="lbMhRangeStartTime" step="60"></label><label class="lb-range-input"><span>End time</span><input type="time" id="lbMhRangeEndTime" step="60"></label></div><div class="lb-range-presets"><button type="button" data-range-time="full">Full day</button><button type="button" data-range-time="evening">Evening</button><button type="button" data-range-time="now">Until now</button></div></div>
                          <div class="lb-range-error" id="lbMhRangeError" hidden></div>
                          <div class="lb-range-actions"><button type="button" class="btn btn-sm btn-outline-secondary" id="lbMhRangeClear">Clear</button><button type="button" class="btn btn-sm btn-outline-secondary" id="lbMhRangeCancel">Cancel</button><button type="button" class="btn btn-sm btn-primary" id="lbMhRangeApply"><i class="fa-duotone fa-check me-1"></i> Apply range</button></div>
                        </div>
                      </div>
                      <div class="lb-mh-backfill-field">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="lbMhBackfillSubmit">
                          <span class="lb-backfill-label"><i class="fa-duotone fa-arrows-rotate me-1"></i> Sync</span>
                          <span class="lb-backfill-loading" style="display:none"><i class="fa-duotone fa-loader fa-spin me-1"></i> Syncing</span>
                        </button>
                      </div>
                    </div>
                    <div class="lb-mh-riot-preview" id="lbMhBackfillRiotPreview" aria-live="polite" hidden>
                      <div class="lb-mh-riot-preview__avatar">
                        <img id="lbMhBackfillRiotIcon" src="" alt="Riot account icon" loading="lazy">
                        <i class="fa-duotone fa-user-magnifying-glass" id="lbMhBackfillRiotIconFallback"></i>
                      </div>
                      <div class="lb-mh-riot-preview__body">
                        <div class="lb-mh-riot-preview__label" id="lbMhBackfillRiotPreviewLabel">Account preview</div>
                        <div class="lb-mh-riot-preview__name" id="lbMhBackfillRiotName">Enter Riot ID to verify</div>
                        <div class="lb-mh-riot-preview__meta" id="lbMhBackfillRiotMeta"></div>
                      </div>
                      <button type="button" class="lb-mh-riot-confirm" id="lbMhBackfillRiotConfirm" hidden>
                        <i class="fa-solid fa-plus"></i> Save account
                      </button>
                    </div>
                    <div class="lb-mh-backfill-footer">
                      <label class="lb-mh-backfill-check">
                        <input type="checkbox" id="lbMhBackfillSaveDuo" value="1">
                        <span class="lb-mh-backfill-check__toggle" aria-hidden="true"></span>
                        <span class="lb-mh-backfill-check__text">
                          <span class="lb-mh-backfill-check__title">Save duo account while syncing</span>
                          <span class="lb-mh-backfill-check__meta">Store the verified Riot account directly while you sync missing games.</span>
                        </span>
                      </label>
                      <div class="lb-mh-backfill-state" id="lbMhBackfillState"></div>
                    </div>
                  </div>
                </div>
                <div class="lb-mh-list-head">
                  <span>Result</span>
                  <span>Champion</span>
                  <span>Booster</span>
                  <span>Mode</span>
                  <span>Role</span>
                  <span>KDA</span>
                  <span>Duration</span>
                  <span>Rank</span>
                  <span>Played</span>
                  <span>Actions</span>
                </div>
                <div class="lb-mh-list" id="lbMhBody">
                  <div class="lb-mh-placeholder"><i class="fa-duotone fa-loader fa-spin me-2"></i>Loading matches…</div>
                </div>
                <div class="lb-mh-pager" id="lbMhPager" style="display:none">
                  <span class="lb-mh-pager-info" id="lbMhPagerInfo"></span>
                  <div class="lb-mh-pager-btns">
                    <button type="button" class="lb-mh-pager-btn" id="lbMhPrev" disabled>← Prev</button>
                    <button type="button" class="lb-mh-pager-btn" id="lbMhNext" disabled>Next →</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <?php endif; ?>

      

<?php
$lbAdminOrderGame = strtolower((string)($data['game'] ?? ''));
$lbAdminIsLolClassic = in_array($lbAdminOrderGame, ['lol_classic', 'lol-classic'], true);
$lbAdminIsLolFamily = in_array($lbAdminOrderGame, ['lol', 'lol_classic', 'lol-classic'], true);
?>

<div class="card mb-4 lb-overview-card">

            <div class="card-header">
                <h4 class="card-header-title">Overview</h4>
            </div>

            <div class="card-body">
                <ul class="lb-ov-grid">

                    <?php if (true): // Shared overview for every supported game ?>
                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🎯</div>
                            <div class="lb-ov-label">Order Details</div>
                            <div class="lb-ov-value"><?= util_format_boost_overview($data['game'], $data['type'], $data) ?></div>
                        </li>

                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🎫</div>
                            <div class="lb-ov-label">Discount</div>
                            <div class="lb-ov-value"><?= util_format_discount_display($data['id']) ?></div>
                        </li>

                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🤝</div>
                            <div class="lb-ov-label">Play With Booster</div>
                            <div class="lb-ov-value"><span class="lb-pill <?= $data['is_duo'] ? 'lb-pill--yes' : 'lb-pill--no' ?>"><span class="lb-dot"></span><?= $data['is_duo'] ? 'YES' : 'NO' ?></span></div>
                        </li>

                        <?php foreach (lb_order_view_purchase_fields($data) as $lbPurchaseField): ?>
                            <li class="lb-ov-item">
                                <div class="lb-ov-ico"><i class="<?= esc($lbPurchaseField['icon']) ?>"></i></div>
                                <div class="lb-ov-label"><?= htmlspecialchars($lbPurchaseField['label'], ENT_QUOTES) ?></div>
                                <div class="lb-ov-value"><?= htmlspecialchars($lbPurchaseField['value'], ENT_QUOTES) ?></div>
                            </li>
                        <?php endforeach; ?>



                        <?php $lbAdminStartLp = lb_order_start_lp_display($data); ?>
                        <?php if ($lbAdminStartLp !== ''): ?>
                            <li class="lb-ov-item">
                                <div class="lb-ov-ico">🏁</div>
                                <div class="lb-ov-label">Start LP</div>
                                <div class="lb-ov-value"><?= htmlspecialchars($lbAdminStartLp, ENT_QUOTES) ?></div>
                            </li>
                        <?php endif; ?>

                        <?php if ((!$lbAdminIsLolClassic && (int)$data['end_tier'] === 8 || $lbAdminIsLolClassic && (int)$data['end_tier'] === 7) && !empty($data['end_lp'])): ?>
                            <li class="lb-ov-item">
                                <div class="lb-ov-ico">🚩</div>
                                <div class="lb-ov-label">End LP</div>
                                <div class="lb-ov-value"><?= (int)$data['end_lp'] ?> LP</div>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($data['lp_gain'])): ?>
                            <li class="lb-ov-item">
                                <div class="lb-ov-ico">📈</div>
                                <div class="lb-ov-label">LP Gain</div>
                                <div class="lb-ov-value"><?= htmlspecialchars((string)$data['lp_gain'], ENT_QUOTES) ?></div>
                            </li>
                        <?php endif; ?>

                    <?php elseif ($data['game'] === 'val'): ?>
                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🎯</div>
                            <div class="lb-ov-label">Order Details</div>
                            <div class="lb-ov-value"><?= util_format_boost_overview($data['game'], $data['type'], $data) ?></div>
                        </li>

                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🎫</div>
                            <div class="lb-ov-label">Discount</div>
                            <div class="lb-ov-value"><?= util_format_discount_display($data['id']) ?></div>
                        </li>

                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🤝</div>
                            <div class="lb-ov-label">Play With Booster</div>
                            <div class="lb-ov-value"><span class="lb-pill <?= $data['is_duo'] ? 'lb-pill--yes' : 'lb-pill--no' ?>"><span class="lb-dot"></span><?= $data['is_duo'] ? 'YES' : 'NO' ?></span></div>
                        </li>

                        <li class="lb-ov-item">
                            <div class="lb-ov-ico">🏁</div>
                            <div class="lb-ov-label">Start RR</div>
                            <div class="lb-ov-value"><?= (int)$data['start_rr'] ?> RR</div>
                        </li>

                        <?php if ($data['end_tier'] == 8): ?>
                            <li class="lb-ov-item">
                                <div class="lb-ov-ico">🚩</div>
                                <div class="lb-ov-label">End LP</div>
                                <div class="lb-ov-value"><?= (int)$data['end_lp'] ?> LP</div>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                </ul>
            </div>

        </div>

<div class="card mb-4">

            <div class="card-header">
                <h4 class="card-header-title">Options</h4>
            </div>

            <div class="card-body">
                <?php
                $options = ['roles', 'champions', 'agents', 'vpn_country', 'is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_coaching', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda'];
                $hasOption = false;
                ?>

                <?php foreach ($options as $option): ?>
                    <?php if (!empty($data[$option])): ?>
                        <?php
                        if ($data['game'] === 'val' && $option === 'flash_position') {
                            continue;
                        }

                        if ($option === 'vpn_country') {
                            if ($data['is_duo'] || $data['form_id'] == 15) {
                                continue;
                            }
                        }

                        // if is_duo is true, don't display flash_position and offline_mode
                        if ($data['is_duo'] && ($option === 'flash_position' || $option === 'is_offline_mode')) {
                            continue;
                        }
                        ?>
                        <?php
                        $ds_opt = util_format_option($option, $data[$option]);
                        $hasOption = true;
                        $optLabel = $ds_opt[0];
                        $optValueHtml = $ds_opt[1];
                        $optPlain = strtolower(trim(strip_tags($optValueHtml)));
                        ?>
                        <div class="lb-opt-row">
                            <div class="lb-opt-left">
                                <div class="lb-opt-ico">
                                    <span class="avatar-initials"><?= util_format_option_emoji($option) ?></span>
                                </div>
                                <div class="lb-opt-text">
                                    <div class="lb-opt-label"><?= $optLabel ?></div>
                                    <div class="lb-opt-sub">
                                      <?php if ($optPlain === 'yes' || $optPlain === 'no'): ?>
                                        <span class="lb-pill <?= $optPlain === 'yes' ? 'lb-pill--yes' : 'lb-pill--no' ?>">
                                            <span class="lb-dot"></span>
                                            <?= strtoupper($optPlain) ?>
                                        </span>
                                      <?php else: ?>
                                        <div class="lb-opt-val"><?= $optValueHtml ?></div>
                                      <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!$hasOption): ?>
                    <div class="text-center py-3">
                        <i class="fa-duotone fa-info-circle fs-3 text-muted"></i>
                        <span class="d-block text-center fw-bold mt-1">No Extra Options</span>
                    </div>
                <?php endif; ?>
            </div>

        </div>


        

        <?php
          $lbIsLolDuoOrder = (strtolower((string)($data['game'] ?? '')) === 'lol' && !empty($data['is_duo']));
          $lbDuoAccountRows = [];
          if ($lbIsLolDuoOrder) {
              $lbOrderId = (int)($data['id'] ?? 0);
              $lbClientPuuid = '';
              $lbBoosterPuuid = '';
              $lbBoosterIgn = '';
              $lbProgressRow = db_get_row('order_progress', ['order_id' => $lbOrderId], true) ?: [];
              if (!empty($lbProgressRow)) {
                  $lbClientPuuid = trim((string)($lbProgressRow['puuid'] ?? ''));
                  $lbBoosterPuuid = trim((string)($lbProgressRow['booster_puuid'] ?? ''));
                  $lbBoosterIgn = trim((string)($lbProgressRow['booster_ign'] ?? ''));
              }

              $lbNameCache = [];
              $lbGetBoosterLabel = function($bid) use (&$lbNameCache) {
                  $bid = (int)$bid;
                  if ($bid <= 0) return 'Unassigned booster';
                  if (!array_key_exists($bid, $lbNameCache)) {
                      $b = db_get_row('boosters', ['id' => $bid], true) ?: [];
                      $lbNameCache[$bid] = !empty($b['username']) ? (string)$b['username'] : ('#' . $bid);
                  }
                  return $lbNameCache[$bid];
              };

              $lbShortPuuid = static function($puuid): string {
                  $puuid = trim((string)$puuid);
                  if ($puuid === '') return '—';
                  return strlen($puuid) > 18 ? substr($puuid, 0, 10) . '…' . substr($puuid, -6) : $puuid;
              };

              $lbServer = trim((string)($data['server'] ?? ''));
              if ($lbServer === '') {
                  $lbOrderOptionsForServer = db_get_row('order_options', ['order_id' => $lbOrderId], true) ?: [];
                  $lbServer = trim((string)($lbOrderOptionsForServer['server'] ?? 'euw'));
              }

              // Old Duo booster accounts are only stored in order_matches as PUUID.
              // Resolve PUUID -> Riot ID so admins see the actual older accounts too.
              $lbResolvedRiotIds = [];
              $lbResolveRiotIdByPuuid = static function($puuid) use (&$lbResolvedRiotIds, $lbServer): string {
                  $puuid = trim((string)$puuid);
                  if ($puuid === '') return '';
                  if (array_key_exists($puuid, $lbResolvedRiotIds)) return $lbResolvedRiotIds[$puuid];

                  $lbResolvedRiotIds[$puuid] = '';
                  try {
                      if (!function_exists('riot_account_url') || !function_exists('riot_api_get')) {
                          return '';
                      }
                      $baseUrl = riot_account_url($lbServer !== '' ? $lbServer : 'euw');
                      $account = riot_api_get($baseUrl . '/riot/account/v1/accounts/by-puuid/' . rawurlencode($puuid), 1);
                      $gameName = trim((string)($account['gameName'] ?? ''));
                      $tagLine  = trim((string)($account['tagLine'] ?? ''));
                      if ($gameName !== '' && $tagLine !== '') {
                          $lbResolvedRiotIds[$puuid] = $gameName . '#' . $tagLine;
                      }
                  } catch (Throwable $e) {}

                  return $lbResolvedRiotIds[$puuid];
              };

              $lbDuoAccountKey = static function($boosterId, $puuid, $riotId): string {
                  $boosterId = (int)$boosterId;
                  $puuid = strtolower(trim((string)$puuid));
                  $riotId = strtolower(trim((string)$riotId));
                  if ($puuid !== '') return 'account_' . $boosterId . '_' . $puuid;
                  if ($riotId !== '') return 'account_' . $boosterId . '_' . md5($riotId);
                  return '';
              };

              $lbAddDuoRow = static function(&$rows, $key, $row) {
                  if ($key === '') $key = 'row_' . count($rows);
                  if (!isset($rows[$key])) { $rows[$key] = $row; return; }
                  $rows[$key]['games'] = max((int)($rows[$key]['games'] ?? 0), (int)($row['games'] ?? 0));
                  $rows[$key]['wins'] = max((int)($rows[$key]['wins'] ?? 0), (int)($row['wins'] ?? 0));
                  if (empty($rows[$key]['riot_id']) && !empty($row['riot_id'])) $rows[$key]['riot_id'] = $row['riot_id'];
                  if (empty($rows[$key]['puuid']) && !empty($row['puuid'])) $rows[$key]['puuid'] = $row['puuid'];
                  if (!empty($row['active'])) $rows[$key]['active'] = true;
                  if (!empty($row['tracked'])) $rows[$key]['tracked'] = true;
                  if (!empty($row['resolved'])) $rows[$key]['resolved'] = true;
                  if (!empty($row['last_played']) && (empty($rows[$key]['last_played']) || strcmp((string)$row['last_played'], (string)$rows[$key]['last_played']) > 0)) {
                      $rows[$key]['last_played'] = $row['last_played'];
                  }
              };

              if ($lbBoosterIgn !== '' || $lbBoosterPuuid !== '') {
                  $bid = (int)($data['booster_id'] ?? 0);
                  $lbAddDuoRow($lbDuoAccountRows, $lbDuoAccountKey($bid, $lbBoosterPuuid, $lbBoosterIgn), [
                      'booster_id' => $bid,
                      'booster' => $lbGetBoosterLabel($bid),
                      'riot_id' => $lbBoosterIgn,
                      'puuid' => $lbBoosterPuuid,
                      'games' => 0,
                      'wins' => 0,
                      'active' => true,
                      'tracked' => false,
                      'resolved' => false,
                      'last_played' => '',
                  ]);
              }

              try {
                  global $db;
                  if (isset($db) && is_object($db) && method_exists($db, 'run')) {
                      $matchRows = $db->run("
                          SELECT
                              COALESCE(om.booster_id, o.booster_id, 0) AS booster_id,
                              COALESCE(b.username, CONCAT('#', COALESCE(om.booster_id, o.booster_id, 0))) AS booster_name,
                              om.puuid,
                              COUNT(*) AS games,
                              SUM(CASE WHEN om.won = 1 THEN 1 ELSE 0 END) AS wins,
                              MAX(om.played_at) AS last_played
                          FROM order_matches om
                          LEFT JOIN orders o ON o.id = om.order_id
                          LEFT JOIN boosters b ON b.id = COALESCE(om.booster_id, o.booster_id)
                          WHERE om.order_id = {$lbOrderId}
                            AND COALESCE(om.is_remake, 0) = 0
                            AND COALESCE(om.play_mode, '') = 'duo'
                            AND COALESCE(om.puuid, '') <> ''
                          GROUP BY COALESCE(om.booster_id, o.booster_id, 0), b.username, om.puuid
                          ORDER BY last_played DESC
                      ") ?: [];

                      foreach ($matchRows as $mr) {
                          $mpuuid = trim((string)($mr['puuid'] ?? ''));
                          if ($mpuuid === '') continue;
                          $isClient = ($lbClientPuuid !== '' && hash_equals($lbClientPuuid, $mpuuid));
                          $isBooster = ($lbBoosterPuuid !== '' && hash_equals($lbBoosterPuuid, $mpuuid));
                          if ($isClient && !$isBooster) continue;
                          $bid = (int)($mr['booster_id'] ?? 0);
                          $riot = $isBooster ? $lbBoosterIgn : $lbResolveRiotIdByPuuid($mpuuid);
                          $lbAddDuoRow($lbDuoAccountRows, $lbDuoAccountKey($bid, $mpuuid, $riot), [
                              'booster_id' => $bid,
                              'booster' => (string)($mr['booster_name'] ?? $lbGetBoosterLabel($bid)),
                              'riot_id' => $riot,
                              'puuid' => $mpuuid,
                              'games' => (int)($mr['games'] ?? 0),
                              'wins' => (int)($mr['wins'] ?? 0),
                              'active' => $isBooster,
                              'tracked' => true,
                              'resolved' => ($riot !== ''),
                              'last_played' => (string)($mr['last_played'] ?? ''),
                          ]);
                      }
                  }
              } catch (Throwable $e) {}
          }
        ?>
        <?php if ($lbIsLolDuoOrder): ?>
        <div class="card mb-4 lb-duo-accounts-card">
          <div class="card-body">
            <button type="button" class="lb-duo-accounts-open" data-bs-toggle="modal" data-bs-target="#duoBoosterAccountsModal">
              <span class="lb-duo-accounts-open__left">
                <i class="fa-duotone fa-users-viewfinder"></i>
                <span class="lb-duo-accounts-open__text">Duo Accounts</span>
              </span>
              <span class="d-inline-flex align-items-center gap-2">
                <span class="lb-duo-accounts-open__count"><?= count($lbDuoAccountRows) ?></span>
                <i class="fa-solid fa-chevron-right"></i>
              </span>
            </button>
          </div>
        </div>

        <div class="modal fade lb-duo-accounts-modal" id="duoBoosterAccountsModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                  <div class="lb-mh-header-ico">
                    <i class="fa-duotone fa-users-viewfinder"></i>
                  </div>
                  <h4 class="modal-title mb-0">Duo Booster Accounts</h4>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-secondary"><?= count($lbDuoAccountRows) ?></span>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <?php if (!empty($lbDuoAccountRows)): ?>
                  <div class="lb-duo-accounts-list">
                    <?php foreach ($lbDuoAccountRows as $lbDuoRow): ?>
                      <?php
                        $lbRiotDisplay = trim((string)($lbDuoRow['riot_id'] ?? ''));
                        $lbPuuidDisplay = trim((string)($lbDuoRow['puuid'] ?? ''));
                        $lbCopyValue = $lbRiotDisplay !== '' ? $lbRiotDisplay : $lbPuuidDisplay;
                        $lbDisplayValue = $lbRiotDisplay !== '' ? $lbRiotDisplay : ('PUUID ' . $lbShortPuuid($lbPuuidDisplay));
                        $lbGames = (int)($lbDuoRow['games'] ?? 0);
                        $lbWins = (int)($lbDuoRow['wins'] ?? 0);
                        $lbLosses = max(0, $lbGames - $lbWins);
                      ?>
                      <div class="lb-duo-account-row">
                        <div class="lb-duo-account-main">
                          <div class="lb-duo-account-booster">
                            <i class="fa-duotone fa-user-helmet-safety"></i>
                            <span><?= htmlspecialchars((string)($lbDuoRow['booster'] ?? 'Booster'), ENT_QUOTES, 'UTF-8') ?></span>
                          </div>
                          <div class="lb-duo-account-riot"><?= htmlspecialchars($lbDisplayValue, ENT_QUOTES, 'UTF-8') ?></div>
                          <div class="lb-duo-account-meta">
                            <?php if (!empty($lbDuoRow['active'])): ?>
                              <span class="lb-duo-account-badge lb-duo-account-badge--active"><i class="fa-duotone fa-circle-check"></i> Saved</span>
                            <?php endif; ?>
                            <?php if (!empty($lbDuoRow['tracked'])): ?>
                              <span class="lb-duo-account-badge lb-duo-account-badge--tracked"><i class="fa-duotone fa-gamepad-modern"></i> <?= $lbGames ?> game<?= $lbGames === 1 ? '' : 's' ?></span>
                              <span class="lb-duo-account-badge"><?= $lbWins ?>W / <?= $lbLosses ?>L</span>
                            <?php endif; ?>
                            <?php if (!empty($lbDuoRow['resolved']) && empty($lbDuoRow['active'])): ?>
                              <span class="lb-duo-account-badge"><i class="fa-duotone fa-clock-rotate-left"></i> Old account</span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <button type="button" class="lb-duo-account-copy js-copy-login" data-copy="<?= htmlspecialchars($lbCopyValue, ENT_QUOTES, 'UTF-8') ?>" title="Copy account" <?= $lbCopyValue !== '' ? '' : 'disabled' ?>>
                          <i class="fa-duotone fa-copy"></i>
                        </button>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="lb-duo-account-empty">No booster Duo account has been saved or tracked for this order yet.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-header-title mb-0">Account</h4>
            <?php
              $isRiotOnly = util_is_riot_only_order_account($data);
              $loginGameLabel = util_get_account_login_game_label((int)($data['form_id'] ?? 0), (string)($data['game'] ?? ''));
              $accNoun = 'Account Logins';
              $hasRiot = !empty(trim((string)($data['login'] ?? ''))) && !empty(trim((string)($data['password'] ?? '')));
              $btnLong = $hasRiot ? "Edit {$accNoun}" : "Add {$accNoun}";
              $btnShort = $hasRiot ? "Edit" : "Add";
            ?>
            <button type="button" class="btn btn-white btn-sm border" id="openEditLoginsBtn"
              data-bs-toggle="modal" data-bs-target="#edit_order_md">
              <i class="fa-duotone fa-user-pen me-2"></i>
              <span class="d-none d-sm-inline"><?= $btnLong ?></span>
              <span class="d-sm-none"><?= $btnShort ?></span>
            </button>
          </div>

          <div class="card-body">

            <?php
              $riotIdVal = trim((string)($data['ign'] ?? ''));
              $loginVal  = trim((string)($data['login'] ?? ''));
              $passVal   = trim((string)($data['password'] ?? ''));

              $riotCopy = $riotIdVal !== '' ? htmlspecialchars($riotIdVal, ENT_QUOTES, 'UTF-8') : '';
              $loginCopy = $loginVal !== '' ? htmlspecialchars($loginVal, ENT_QUOTES, 'UTF-8') : '';
              $passCopy = $passVal !== '' ? htmlspecialchars($passVal, ENT_QUOTES, 'UTF-8') : '';
            ?>

            <div class="lb-acc-details" id="lbAdminAccountCard" data-order-id="<?= (int)$data['id'] ?>">
              <?php if (strtolower((string) ($data['game'] ?? '')) === 'lol'): // Riot ID drives the API tracking ?>
              <div class="lb-acc-row">
                <div class="lb-acc-label">Riot ID</div>
                <div class="lb-acc-actions">
                  <div class="lb-acc-value <?= $riotIdVal !== '' ? '' : 'is-missing' ?> js-copyable" data-copy="<?= $riotCopy ?>" title="Click to copy">
                    <?= $riotIdVal !== '' ? $riotCopy : '—' ?>
                  </div>
                  <button type="button" class="lb-acc-copy js-copy-login" data-copy="<?= $riotCopy ?>" aria-label="Copy Riot ID" <?= $riotIdVal !== '' ? '' : 'disabled' ?>>
                    <i class="fa-duotone fa-copy"></i>
                  </button>
                </div>
              </div>
              <?php endif; ?>

              <?php if (!$isRiotOnly): ?>
              <div class="lb-acc-row">
                <div class="lb-acc-label">Account Username</div>
                <div class="lb-acc-actions">
                  <div class="lb-acc-value <?= $loginVal !== '' ? '' : 'is-missing' ?> js-copyable" data-account-field="login" data-copy="<?= $loginCopy ?>" title="Click to copy">
                    <?= $loginVal !== '' ? $loginCopy : '—' ?>
                  </div>
                  <button type="button" class="lb-acc-copy js-copy-login" data-account-copy="login" data-copy="<?= $loginCopy ?>" aria-label="Copy <?= $loginGameLabel ?> Username" <?= $loginVal !== '' ? '' : 'disabled' ?>>
                    <i class="fa-duotone fa-copy"></i>
                  </button>
                </div>
              </div>

              <div class="lb-acc-row">
                <div class="lb-acc-label">Account Password</div>
                <div class="lb-acc-actions">
                  <div class="lb-acc-value <?= $passVal !== '' ? '' : 'is-missing' ?> js-copyable" data-account-field="password" data-copy="<?= $passCopy ?>" title="Click to copy">
                    <?= $passVal !== '' ? $passCopy : '—' ?>
                  </div>
                  <button type="button" class="lb-acc-copy js-copy-login" data-account-copy="password" data-copy="<?= $passCopy ?>" aria-label="Copy <?= $loginGameLabel ?> Password" <?= $passVal !== '' ? '' : 'disabled' ?>>
                    <i class="fa-duotone fa-copy"></i>
                  </button>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

</div>
</div>

<div id="set_order_paid_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="set_order_paidTitle">Set Order as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to set this order as paid?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form class="ajax-form" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="admin_set_order_paid">
                    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Yes, set as paid
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="set_order_unpaid_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="set_order_paidTitle">Set Order as Unpaid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to set this order as unpaid?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form class="ajax-form" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="admin_set_order_unpaid">
                    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Yes, set as unpaid
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="set_order_refunded_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="set_order_refundedTitle">Set Order as Refunded</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to set this order as refunded?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form class="ajax-form" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="admin_set_order_refunded">
                    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Yes, set as refunded
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="pause_order_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pause Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to pause this order?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
        <form class="ajax-form" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action" value="admin_pause_order">
          <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
          <input type="hidden" name="id" value="<?= $data['id'] ?>">
          <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Yes, pause order</span>
            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>



<div id="add_booster_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form class="ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_add_booster_to_order">
                <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                <input type="hidden" name="booster_id" id="admin_add_booster_hidden_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="set_order_paidTitle">Add Booster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                      <div class="lb-r5s-add-modal-head mb-3">
                        <div class="lb-r5s-add-modal-icon">
                          <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                          <div class="lb-r5s-add-modal-title">Ranked 5s Booster Slot</div>
                          <div class="lb-r5s-add-modal-sub">
                            <?= (int)$lb_admin_ranked_5s_claimed_count ?>/<?= (int)$lb_admin_ranked_5s_required_boosters ?> boosters assigned, choose an open lane for the next booster.
                          </div>
                        </div>
                      </div>

                      <div class="lb-r5s-add-progress mb-3" aria-hidden="true">
                        <?php for ($i = 1; $i <= (int)$lb_admin_ranked_5s_required_boosters; $i++): ?>
                          <span class="<?= $i <= (int)$lb_admin_ranked_5s_claimed_count ? 'is-filled' : '' ?>"></span>
                        <?php endfor; ?>
                      </div>

                      <label class="form-label lb-r5s-add-label">Open Lane</label>
                      <div class="lb-r5s-lane-pick-grid mb-4">
                        <?php foreach ($lb_admin_ranked_5s_available_roles as $idx => $lane): ?>
                          <?php
                            $laneRaw = (string)$lane;
                            $laneShort = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], $laneRaw);
                            $laneIcon = ASSET_URL . '/core/main/img/lol/roles/' . $laneRaw . '.png';
                          ?>
                          <input type="radio"
                                 class="lb-r5s-lane-radio"
                                 name="ranked_5s_lane"
                                 id="admin_r5s_lane_<?= htmlspecialchars($laneRaw) ?>"
                                 value="<?= htmlspecialchars($laneRaw) ?>"
                                 <?= $idx === 0 ? 'checked' : '' ?>>
                          <label class="lb-r5s-lane-card" for="admin_r5s_lane_<?= htmlspecialchars($laneRaw) ?>">
                            <span class="lb-r5s-lane-card__icon">
                              <img src="<?= htmlspecialchars($laneIcon) ?>" alt="<?= htmlspecialchars($laneShort) ?>">
                            </span>
                            <span class="lb-r5s-lane-card__name"><?= htmlspecialchars($laneShort) ?></span>
                            <span class="lb-r5s-lane-card__check"><i class="fa-solid fa-check"></i></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>

                    <label class="form-label lb-r5s-add-label">Select Booster</label>
                    <div class="tom-select-custom tom-select-custom-with-tags lb-r5s-booster-select-wrap">
                        <select name="booster_id_select" id="admin_add_booster_select" class="form-select js-select" autocomplete="off"
                            data-hs-tom-select-options='{
                        "placeholder": "Search booster..."}'>
                            <option value=""></option>
                            <?php
                              $lbAssignedBoosterIds = array_map(static fn($b) => (int)($b['booster_id'] ?? 0), (array)$lb_admin_ranked_5s_boosters);
                              foreach (db_get_rows('boosters', ['select' => 'username,id', 'is_banned' => 0]) as $booster):
                                if (in_array((int)$booster['id'], $lbAssignedBoosterIds, true)) continue;
                            ?>
                                <option value="<?= $booster['id'] ?>"><?= $booster['username'] ?></option>
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
                            Add Booster
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

<style>
#remove_booster_progress_payment_md .modal-dialog {
  max-width: 520px;
  margin-top: .75rem;
  margin-bottom: .75rem;
}
#remove_booster_progress_payment_md .modal-content {
  max-height: calc(100dvh - 24px);
  border-radius: 16px;
  overflow: hidden;
}
#remove_booster_progress_payment_md form.ajax-form {
  max-height: calc(100dvh - 24px);
  display: flex;
  flex-direction: column;
  min-height: 0;
}
#remove_booster_progress_payment_md .modal-body {
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
  padding: 0;
  scrollbar-width: thin;
}
#remove_booster_progress_payment_md .modal-footer {
  flex-shrink: 0;
  background: #25282A;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  max-height: none;
  overflow: visible;
  padding-right: 0;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select .form-check {
  flex: 1 1 calc(50% - 4px);
  min-width: 190px;
  margin: 0;
  padding: 0;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select .form-check-label {
  min-height: 42px;
  padding: 8px 10px;
  justify-content: flex-start;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select .form-check-label span {
  overflow: visible;
  text-overflow: clip;
  white-space: normal;
  line-height: 1.15;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select .form-check-label small {
  margin-left: auto;
}
#remove_booster_progress_payment_md .lb-r5s-remove-select .form-check-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
@media (min-width: 576px) {
  #remove_booster_progress_payment_md .lb-r5s-remove-select .form-check {
    flex: 1 1 calc(50% - 4px);
    min-width: 0;
  }
}
#remove_booster_progress_payment_md .rb-r5s-progress-card {
  gap: 8px;
}
#remove_booster_progress_payment_md .rb-r5s-progress-card .form-label {
  font-size: .72rem;
  margin-bottom: 4px;
}
#remove_booster_progress_payment_md .rb-r5s-progress-card .form-control,
#remove_booster_progress_payment_md #rb_new_total_price,
#remove_booster_progress_payment_md #rb_progress_note {
  min-height: 40px;
  padding-top: .45rem;
  padding-bottom: .45rem;
}
@media (max-height: 760px) {
  #remove_booster_progress_payment_md .modal-dialog { margin-top: .35rem; margin-bottom: .35rem; }
  #remove_booster_progress_payment_md .modal-content,
  #remove_booster_progress_payment_md form.ajax-form { max-height: calc(100dvh - 12px); }
  #remove_booster_progress_payment_md .rb-section { padding-top: 10px; padding-bottom: 10px; }
  #remove_booster_progress_payment_md .lb-r5s-remove-select { max-height: none; overflow: visible; }
}
#rb_riot_card { background: rgba(0,0,0,0.20); border-bottom: 1px solid rgba(255,255,255,0.07); }
#rb_riot_card .rb-riot-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 16px; border-bottom: 1px solid rgba(255,255,255,0.05);
}
#rb_riot_card .rb-riot-stats {
  display: flex; align-items: stretch; padding: 0;
}
#rb_riot_card .rb-stat-item {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 2px; flex: 1;
  padding: 9px 4px;
}
#rb_riot_card .rb-stat-item + .rb-stat-item { border-left: 1px solid rgba(255,255,255,0.06); }
#rb_riot_card .rb-stat-val { font-size: .90rem; font-weight: 800; line-height: 1; }
#rb_riot_card .rb-stat-lbl { font-size: .60rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; opacity: .35; }
#rb_riot_card .rb-stat-val--win  { color: rgba(74,222,128,.95); }
#rb_riot_card .rb-stat-val--loss { color: rgba(248,113,113,.95); }
#remove_booster_progress_payment_md .rb-section {
  padding: 14px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
#remove_booster_progress_payment_md .rb-section:last-child { border-bottom: none; }
#remove_booster_progress_payment_md .rb-label {
  font-size: .70rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; opacity: .40; margin-bottom: 8px;
}
</style>

<div id="remove_booster_progress_payment_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>">
        <input type="hidden" name="action" value="admin_remove_booster_from_order">
        <input type="hidden" name="id" id="rb_order_id" value="<?= (int)$data['id'] ?>">
        <input type="hidden" name="progress_to" id="rb_progress_to">
        <input type="hidden" name="progress_type" value="custom">
        <input type="hidden" name="manual_price_override" id="rb_manual_price_override" value="0">

        <!-- Header -->
        <div class="modal-header" style="padding:13px 18px; border-bottom:1px solid rgba(255,255,255,0.08); flex-shrink:0;">
          <div>
            <h5 class="modal-title mb-0" style="font-size:.93rem; font-weight:700;">Remove Booster</h5>
            <div class="text-muted" style="font-size:.73rem; margin-top:1px;">Progress Payment</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">


          <?php if (!empty($lb_admin_is_multi_booster) && !empty($lb_admin_ranked_5s_boosters)): ?>
            <div class="rb-section">
              <div class="rb-label">Booster to remove</div>
              <div class="lb-r5s-remove-select">
                <?php foreach ($lb_admin_ranked_5s_boosters as $idx => $r5remove): ?>
                  <?php
                    $r5removeId = (int)($r5remove['booster_id'] ?? 0);
                    if ($r5removeId <= 0) continue;
                    $r5removeLane = $lb_admin_is_ranked_5s ? str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], (string)($r5remove['role'] ?? '')) : '';
                    $r5removeName = trim((string)($r5remove['username'] ?? ('Booster #' . $r5removeId)));
                    $r5removeIcon = trim((string)($r5remove['icon'] ?? ''));
                  ?>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="ranked_5s_booster_id" id="r5s_remove_booster_<?= $r5removeId ?>" value="<?= $r5removeId ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="r5s_remove_booster_<?= $r5removeId ?>">
                      <?php if ($r5removeIcon !== ''): ?><img src="<?= htmlspecialchars($r5removeIcon) ?>" alt=""><?php endif; ?>
                      <span><?= htmlspecialchars($r5removeName) ?></span>
                      <?php if ($r5removeLane !== ''): ?><small><?= htmlspecialchars($r5removeLane) ?></small><?php endif; ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="form-text mt-2">Only the selected booster will be removed. All other team boosters stay assigned.</div>
            </div>

            <?php if (!empty($lb_admin_is_ranked_5s)): ?>
              <div class="rb-section rb-r5s-progress-section">
                <div class="rb-label">Ranked 5s Progress</div>
                <div class="rb-r5s-progress-card">
                  <div>
                    <label class="form-label">Total Games</label>
                    <input type="number" min="1" class="form-control" id="rb_r5s_total_games" value="<?= (int)($data['matches'] ?? 1) ?>" readonly>
                  </div>
                  <div>
                    <label class="form-label">Games completed</label>
                    <input type="number" min="0" max="<?= (int)($data['matches'] ?? 1) ?>" class="form-control" id="rb_r5s_games_completed" name="ranked_5s_games_completed" value="0">
                  </div>
                  <div>
                    <label class="form-label">Current total price</label>
                    <input type="text" class="form-control" id="rb_r5s_old_price" value="<?= util_format_price_input($data['price']) ?>" readonly>
                  </div>
                </div>
                <div class="form-text mt-2">Use Calculate price to reduce the order price by completed games. You can still edit the new total price manually afterwards.</div>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <!-- ① Riot API Card -->
          <?php
          $rb_form_id_for_riot = (int) ($data['form_id'] ?? 0);
          $rb_is_coaching_form = in_array($rb_form_id_for_riot, [15, 16, 25], true) || (($data['type'] ?? '') === 'coaching');
          if (strtolower((string) ($data['game'] ?? '')) === 'lol' && !empty(trim((string) ($data['ign'] ?? ''))) && !$rb_is_coaching_form): ?>
          <div id="rb_riot_card">
            <div class="rb-riot-header">
              <div style="display:flex; align-items:center; gap:8px;">
                <div class="lb-op-header-ico" style="width:26px;height:26px;font-size:.75rem;flex-shrink:0;"><i class="fa-duotone fa-chart-line-up"></i></div>
                <div>
                  <div style="font-weight:700; font-size:.80rem; line-height:1.1;">Riot API Progress</div>
                  <div id="rb_riot_rank_name" style="font-size:.74rem; color:rgba(255,255,255,0.45); margin-top:2px;">Loading…</div>
                </div>
              </div>
              <div style="display:flex; gap:7px; align-items:center;">
                <button type="button" class="lb-op-refresh-btn" id="rb_riot_refresh_btn" title="Refresh" style="width:28px;height:28px;">
                  <i class="fa-duotone fa-arrows-rotate" id="rb_riot_refresh_icon" style="font-size:.78rem;"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="rb_riot_use_btn" disabled style="font-size:.74rem; padding:4px 10px; white-space:nowrap;">
                  <i class="fa-duotone fa-arrow-down-to-bracket me-1"></i>Use this data
                </button>
              </div>
            </div>
            <div id="rb_riot_stats_row" class="rb-riot-stats" style="display:none;">
              <div class="rb-stat-item">
                <div class="rb-stat-val rb-stat-val--win" id="rb_riot_wins">—</div>
                <div class="rb-stat-lbl">Wins</div>
              </div>
              <div class="rb-stat-item">
                <div class="rb-stat-val rb-stat-val--loss" id="rb_riot_losses">—</div>
                <div class="rb-stat-lbl">Losses</div>
              </div>
              <div class="rb-stat-item">
                <div class="rb-stat-val" id="rb_riot_wr">—</div>
                <div class="rb-stat-lbl">Winrate</div>
              </div>
              <div class="rb-stat-item">
                <div class="rb-stat-val" id="rb_riot_sync" style="font-size:.68rem; opacity:.50;">—</div>
                <div class="rb-stat-lbl">Last Sync</div>
              </div>
            </div>
            <div id="rb_riot_wr_bar_wrap" style="display:none; height:3px; background:rgba(255,255,255,0.05);">
              <div id="rb_riot_wr_bar" style="height:100%; width:0%; background:rgba(255,255,255,0.18); transition:width .5s ease;"></div>
            </div>
          </div>
          <?php endif; ?>

          <!-- ② Progress reached -->
          <div class="rb-section">
            <div class="rb-label">Progress reached by booster</div>
            <div id="rb_dynamic_fields"></div>
            <div class="d-flex align-items-center gap-2 mt-3">
              <button type="button" class="btn btn-sm btn-primary" id="rb_calc_price_btn" style="padding:5px 14px;">
                <i class="fa-duotone fa-calculator me-1"></i> Calculate price
              </button>
              <div class="small text-muted" id="rb_calc_meta" style="display:none;"></div>
            </div>
          </div>

          <!-- ③ New total price -->
          <div class="rb-section">
            <div class="rb-label">New total price</div>
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" class="form-control" name="new_total_price" id="rb_new_total_price" placeholder="0.00" inputmode="decimal" autocomplete="off" style="max-width:160px; font-size:.95rem; font-weight:700;">
              <span class="text-muted" style="font-size:.78rem; flex:1;"><?= util_format_currency_display($data['currency']) ?> · Edit manually or use Calculate price</span>
            </div>
          </div>

          <!-- ④ Progress note -->
          <div class="rb-section">
            <div class="rb-label">Progress note <span style="opacity:.45; font-size:.68rem; text-transform:none; letter-spacing:0; font-weight:400;">(optional)</span></div>
            <input type="text" class="form-control" name="progress_note" id="rb_progress_note" placeholder="e.g. Gold IV 0LP → Gold IV 20LP" style="font-size:.82rem;">
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer" style="padding:11px 18px; border-top:1px solid rgba(255,255,255,0.08); flex-shrink:0;">
          <button type="button" class="btn btn-white btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger btn-sm">
            <span class="indicator-label"><i class="fa-duotone fa-circle-x me-1"></i>Remove Booster</span>
            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Loading...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<div id="complete_order_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="position:relative;">
            <form class="ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_set_order_completed">
                <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

                <div class="modal-header">
                    <div class="lb-modal-head">
                        <div class="lb-modal-ico" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="lb-modal-title">Complete order</h5>
                            <p class="lb-modal-sub">Review the proof screenshot and finish this order.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <?php if (!empty($data['file_url'])): ?>
                        <div class="lb-section mb-3">
                            <div class="lb-section-title">
                                <span>Proof screenshot</span>
                            </div>

                            <div class="lb-proof-preview">
                                <img src="<?= $data['file_url'] ?>" alt="Order proof screenshot">
                                <div class="lb-proof-actions">
                                    <a class="btn btn-sm" href="<?= $data['file_url'] ?>" target="_blank" rel="noopener">Open screenshot</a>
                                    <?php if (!empty($data['screenshot_id'])): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                id="lbAdminRemoveScreenshotBtn"
                                                data-screenshot-id="<?= (int) $data['screenshot_id'] ?>">
                                            Remove
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="lb-section mb-3">
                            <div class="lb-section-title">
                                <span>No proof screenshot uploaded</span>
                            </div>
                            <div class="text-muted small">You can still complete the order manually.</div>
                        </div>
                    <?php endif; ?>


                    <?php if (!empty($data['is_bonus_win'])): ?>
                        <div class="lb-section mb-3">
                            <div class="lb-hint">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div>
                                    <div class="fw-bold mb-1">Bonus Win required</div>
                                    <div class="small text-muted">This order includes a <b>Bonus Win</b>. Please make sure it is fully completed before finishing the order.</div>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="lbBonusWinConfirm" required>
                                <label class="form-check-label" for="lbBonusWinConfirm">
                                    I confirm the <b>Bonus Win</b> is completed.
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="lb-section">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span><?= !empty($lb_admin_is_ranked_5s) ? 'Earnings / Booster' : 'Booster cut' ?></span>
                        </label>

                        <?php $canEditCompleteCut = ((int) ADMIN_ID !== 3); ?>

                        <?php if ($canEditCompleteCut): ?>
                        <div>
                            <div class="btn-group btn-group-segment btn-group-md-vertical mb-2" role="group" aria-label="Cut type">
                                <input type="radio" class="btn-check" name="is_fixed" value="0" id="is_fixed1" autocomplete="off" checked>
                                <label class="btn btn-sm" for="is_fixed1"><?= !empty($lb_admin_is_ranked_5s) ? 'Cut % split by boosters' : 'Percentage (%)' ?></label>

                                <input type="radio" class="btn-check" name="is_fixed" value="1" id="is_fixed2" autocomplete="off">
                                <label class="btn btn-sm" for="is_fixed2"><?= !empty($lb_admin_is_ranked_5s) ? 'Fixed €/Booster' : 'Fixed Amount (€)' ?></label>
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="is_fixed" value="0">
                        <?php endif; ?>

                        <input type="number"
                               class="form-control border"
                               placeholder="<?= !empty($lb_admin_is_ranked_5s) ? 'e.g. 60% or 3.78€ per booster' : 'e.g: 15% or 8.40€' ?>"
                               name="cut"
                               step="0.01"
                               value="<?= $cutPercent ?>"
                               min="0"
                               <?= $canEditCompleteCut ? '' : 'readonly' ?>
                               required>
                        <?php if (!$canEditCompleteCut): ?>
                          <div class="form-text mt-2">The predefined cut is used and cannot be changed.</div>
                        <?php endif; ?>
                        <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                          <div class="form-text mt-2">
                            For Ranked 5s, percentage mode pays each assigned booster <b><?= $currency . util_format_price_display($lb_ranked_5s_cut_per_booster ?? 0) ?></b> based on the current cut.
                          </div>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Complete Order</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...</span>
                    </button>
                </div>
            </form>

            <!-- Custom confirm (instead of browser confirm) -->
            <div class="lb-confirm-backdrop" id="lbRemoveConfirm" aria-hidden="true">
                <div class="lb-confirm-card" role="dialog" aria-modal="true" aria-label="Remove screenshot">
                    <div class="lb-confirm-title">Remove this screenshot?</div>
                    <div class="lb-confirm-desc">This will permanently delete the uploaded proof.</div>
                    <div class="lb-confirm-actions">
                        <button type="button" class="btn btn-white btn-sm" id="lbRemoveCancelBtn">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm" id="lbRemoveConfirmBtn">Remove</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="admin_edit_order">
    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
    <div id="edit_order_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header pb-4 border-bottom">
                    <h5 class="modal-title" id="set_order_paidTitle">Edit Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-3 py-0">
                    <?php if ($lbAdminIsLolFamily || $data['game'] === 'tft'): ?>
                        <div class="accordion accordion-icon-toggle" id="panel-1">
                            <!--begin::Item-->
                            <div>
                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Type & Server</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                    <div class="col-lg-12 mb-3 fv-row order-type">
                                        <label class="fs-6 fw-bold form-label mb-2">Boost Type</label>
                                        <select class="form-select" name="orders-form_id"
                                            data-placeholder="Select a boost type">
                                            <?= util_load_boost_forms_select(
                                                $data['form_id'],
                                                $data['game'] === 'tft' ? 'tft' : ($lbAdminIsLolClassic ? 'lol_classic' : 'lol')
                                            ) ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row border-bottom mb-4">
                                    <div class="col-lg-4 mb-3 fv-row order-server">

                                        <label class="fs-6 fw-bold form-label mb-2">Server</label>
                                        <select class="form-select" name="order_options-server"
                                            data-placeholder="Select a server">
                                            <?= util_load_server_select($data['server'], $data['game'] === 'tft' ? 'tft' : 'lol') ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 mb-3 fv-row order-price">

                                        <label class="fs-6 fw-bold form-label mb-2">Price</label>
                                        <input class="form-control border form-control-solid no-arr"
                                            value="<?= util_format_price_input($data['price']) ?>" placeholder="29.99"
                                            step="0.01" min="0" type="number" name="orders-price">
                                        <button type="button" class="btn btn-sm btn-white mt-2 lb-edit-calc-price">
                                            <i class="fa-duotone fa-calculator me-1"></i> Calculate price
                                        </button>

                                    </div>

                                    <div class="col-lg-3 mb-3 fv-row order-cut">
                                        <?php $canEditBoosterCut = ((int) ADMIN_ID !== 3); ?>
                                        <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                                            <label class="fs-6 fw-bold form-label mb-2">Earning per Booster</label>
                                            <?php if ($canEditBoosterCut): ?>
                                                <input class="form-control border form-control-solid no-arr"
                                                    value="<?= htmlspecialchars($lb_admin_edit_earning_per_booster) ?>"
                                                    placeholder="3.78" step="0.01" min="0" type="number"
                                                    name="ranked_5s_earning_per_booster">
                                            <?php else: ?>
                                                <input type="hidden" name="ranked_5s_earning_per_booster"
                                                    value="<?= htmlspecialchars($lb_admin_edit_earning_per_booster) ?>">
                                                <input class="form-control border form-control-solid no-arr"
                                                    value="<?= htmlspecialchars($lb_admin_edit_earning_per_booster) ?>"
                                                    placeholder="3.78" type="text" disabled readonly>
                                            <?php endif; ?>
                                            <div class="form-text">Saved as per booster payout. The cut percent is calculated automatically.</div>
                                        <?php else: ?>
                                            <label class="fs-6 fw-bold form-label mb-2">Booster Cut %</label>
                                            <?php if ($canEditBoosterCut): ?>
                                                <input class="form-control border form-control-solid no-arr"
                                                    value="<?= $data['booster_cut'] ?>" placeholder="15%" type="text"
                                                    name="orders-booster_cut">
                                            <?php else: ?>
                                                <input type="hidden" name="orders-booster_cut" value="<?= $data['booster_cut'] ?>">
                                                <input class="form-control border form-control-solid no-arr"
                                                    value="<?= $data['booster_cut'] ?>" placeholder="15%" type="text"
                                                    disabled readonly>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-lg-2 col-md-6 mb-3 fv-row order-duo">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Duo</label>
                                        <input type="hidden" name="order_options-is_duo" value="<?= (!empty($lb_admin_is_ranked_5s) || (int)($data['form_id'] ?? 0) === 19) ? '1' : '0' ?>">
                                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                            <input class="form-check-input" name="order_options-is_duo" type="checkbox"
                                                value="1" <?= ($data['is_duo'] || !empty($lb_admin_is_ranked_5s) || (int)($data['form_id'] ?? 0) === 19) ? 'checked' : null ?> <?= ((int)($data['form_id'] ?? 0) === 19) ? 'disabled' : null ?>>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Body-->


                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Details</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row border-bottom mb-4">
                                    <div class="col-lg-8 mb-3 fv-row order-start-tier">
                                        <label class="fs-6 fw-bold form-label mb-2">Start Tier</label>
                                        <select class="form-select" name="order_options-start_tier"
                                            data-placeholder="Select a tier">
                                            <?php if ($lbAdminIsLolClassic): ?>
                                                <?php foreach ([0=>'Unranked',1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'] as $tierId => $tierName): ?>
                                                    <option value="<?= $tierId ?>" <?= (int)$data['start_tier'] === $tierId ? 'selected' : '' ?>><?= $tierName ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= util_load_lol_tier_select(0, 9, $data['start_tier']) ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 mb-3 fv-row order-start-division">
                                        <label class="fs-6 fw-bold form-label mb-2">Start Division</label>
                                        <select class="form-select" name="order_options-start_division"
                                            data-placeholder="Select a division">
                                            <?php if ($lbAdminIsLolClassic): ?>
                                                <?php foreach ([4=>'IV',3=>'III',2=>'II',1=>'I'] as $divisionId => $divisionName): ?>
                                                    <option value="<?= $divisionId ?>" <?= (int)$data['start_division'] === $divisionId ? 'selected' : '' ?>><?= $divisionName ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= util_load_lol_division_select($data['start_division']) ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 mb-3 fv-row order-start-lp-manual d-none">
                                        <label class="fs-6 fw-bold form-label mb-2">Start LP</label>
                                        <input type="text" class="form-control" data-kt-dialer-control="input"
                                            placeholder="0-20" name="order_options-start_lp"
                                            value="<?= $data['start_lp'] ?>" />
                                    </div>
                                    <div class="col-lg-8 mb-3 fv-row order-end-tier">
                                        <label class="fs-6 fw-bold form-label mb-2">End Tier</label>
                                        <select class="form-select" name="order_options-end_tier"
                                            data-placeholder="Select a tier">
                                            <?php if ($lbAdminIsLolClassic): ?>
                                                <?php foreach ([0=>'Unranked',1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'] as $tierId => $tierName): ?>
                                                    <option value="<?= $tierId ?>" <?= (int)$data['end_tier'] === $tierId ? 'selected' : '' ?>><?= $tierName ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= util_load_lol_tier_select(0, 10, $data['end_tier']) ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 mb-3 fv-row order-end-division">
                                        <label class="fs-6 fw-bold form-label mb-2">End Division</label>
                                        <select class="form-select" name="order_options-end_division"
                                            data-placeholder="Select a division">
                                            <?php if ($lbAdminIsLolClassic): ?>
                                                <?php foreach ([4=>'IV',3=>'III',2=>'II',1=>'I'] as $divisionId => $divisionName): ?>
                                                    <option value="<?= $divisionId ?>" <?= (int)$data['end_division'] === $divisionId ? 'selected' : '' ?>><?= $divisionName ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= util_load_lol_division_select($data['end_division']) ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 mb-3 fv-row order-end-lp-manual d-none">
                                        <label class="fs-6 fw-bold form-label mb-2">End LP</label>
                                        <input type="text" class="form-control" data-kt-dialer-control="input"
                                            placeholder="0-20" name="order_options-end_lp" value="<?= $data['end_lp'] ?>" />
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row order-start-lp">
                                        <label class="fs-6 fw-bold form-label mb-2">Start LP</label>
                                        <input type="text" class="form-control" data-kt-dialer-control="input"
                                            placeholder="0-20" name="order_options-start_lp"
                                            value="<?= $data['start_lp'] ?>" />
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row order-lp-gain">
                                        <label class="fs-6 fw-bold form-label mb-2">LP Gain</label>
                                        <input type="text" class="form-control" data-kt-dialer-control="input"
                                            placeholder="150" name="order_options-lp_gain"
                                            value="<?= $data['lp_gain'] ?>" />
                                    </div>
                                    <div class="col-lg-12 mb-3 fv-row order-arena">
                                        <label class="fs-6 fw-bold form-label mb-2">Arena</label>
                                        <select class="form-select" name="order_options-start_tier"
                                            data-placeholder="Select a Arena">
                                            <?= util_load_lol_arena_select(1, 5, $data['start_tier']) ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mb-3 fv-row order-start-level">
                                        <label class="fs-6 fw-bold form-label mb-2">Start Level</label>
                                        <select class="form-select" name="order_options-start_tier"
                                            data-placeholder="Select a Level">
                                            <?= util_load_lol_level_select(1, 10, $data['start_tier']) ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mb-3 fv-row order-start-level-clash">
                                        <label class="fs-6 fw-bold form-label mb-2">Start Tier</label>
                                        <select class="form-select" name="order_options-start_tier"
                                            data-placeholder="Select a Level">
                                            <?= util_load_lol_level_select(1, 4, $data['start_tier']) ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mb-3 fv-row order-end-level">
                                        <label class="fs-6 fw-bold form-label mb-2">End Level</label>
                                        <select class="form-select" name="order_options-end_tier"
                                            data-placeholder="Select a Level">
                                            <?= util_load_lol_level_select(1, 10, $data['end_tier']) ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-champion">
                                        <label class="fs-6 fw-bold form-label mb-2">Champion</label>
                                        <select class="js-select form-select" name="order_options-champions[]"
                                            data-placeholder="Select a champion">
                                            <option value="">
                                                Select a champion
                                            </option>
                                            <?= util_load_champions_select($data['champions'], ',') ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row order-start-level-manual">
                                        <label class="fs-6 fw-bold form-label mb-2">Start Level</label>
                                        <input type="number" class="form-control" data-kt-dialer-control="input"
                                            name="order_options-start_tier" value="<?= $data['start_tier'] ?>" />
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row order-end-level-manual">
                                        <label class="fs-6 fw-bold form-label mb-2">End Level</label>
                                        <input type="number" class="form-control" data-kt-dialer-control="input"
                                            name="order_options-end_tier" value="<?= $data['end_tier'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-matches">
                                        <label class="fs-6 fw-bold form-label mb-2">Matches Amount</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-matches" value="<?= $data['matches'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-wins">
                                        <label class="fs-6 fw-bold form-label mb-2">Wins</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-matches" value="<?= $data['matches'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-games">
                                        <label class="fs-6 fw-bold form-label mb-2">Games</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-matches" value="<?= $data['matches'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-normals">
                                        <label class="fs-6 fw-bold form-label mb-2">Normals</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-matches" value="<?= $data['matches'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-placements">
                                        <label class="fs-6 fw-bold form-label mb-2">Placements</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-matches" value="<?= $data['matches'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-hours">
                                        <label class="fs-6 fw-bold form-label mb-2">Hours</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-hours" value="<?= $data['hours'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-boosters">
                                        <label class="fs-6 fw-bold form-label mb-2">Boosters</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-hours" value="<?= $data['hours'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row
                                            order-coach-type">
                                        <label class="fs-6 fw-bold form-label mb-2">Coach Type</label>
                                        <select class="form-select" name="order_options-coach_type"
                                            data-placeholder="Select a coach type">
                                            <option value="Co-Pilot" <?= $data['coach_type'] == 'Co-Pilot' ? ' selected=""' : null ?>>
                                                Co-Pilot</option>
                                            <option value="VOD-Review" <?= $data['coach_type'] == 'VOD-Review' ? ' selected=""' : null ?>>
                                                VOD-Review</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end::Body-->

                                <!--begin::Header-->
                                <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                                  <h3 class="fs-4 fw-semibold my-3">Ranked 5s Details</h3>
                                <?php else: ?>
                                  <h3 class="fs-4 fw-semibold my-3 order-options-heading">Order Options</h3>
                                <?php endif; ?>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                    <div class="col-12 mb-3 fv-row order-roles">
                                        <label class="fs-6 fw-bold form-label mb-2"><?= !empty($lb_admin_is_ranked_5s) ? 'Customer Role' : 'Roles' ?></label>
                                        <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                                          <select class="form-select" name="order_options-roles">
                                            <?php
                                              $r5CurrentRole = trim((string)($data['roles'] ?? ''));
                                              $r5RoleOptions = [
                                                'TopLane' => 'Top',
                                                'Jungle' => 'Jungle',
                                                'MidLane' => 'Mid',
                                                'AdCarry' => 'ADC',
                                                'Support' => 'Support',
                                              ];
                                              foreach ($r5RoleOptions as $r5RoleValue => $r5RoleLabel):
                                            ?>
                                              <option value="<?= htmlspecialchars($r5RoleValue) ?>" <?= $r5CurrentRole === $r5RoleValue ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($r5RoleLabel) ?>
                                              </option>
                                            <?php endforeach; ?>
                                          </select>
                                          <div class="form-text">This is the customer lane. Booster lanes are handled separately.</div>
                                        <?php else: ?>
                                          <select class="js-select form-select" name="order_options-roles[]"
                                              data-placeholder="Select a role" multiple="multiple">
                                              <?= util_load_roles_select($data['roles'], ',') ?>
                                          </select>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-champions">
                                        <label class="fs-6 fw-bold form-label mb-2">Champions</label>
                                        <select class="js-select form-select" name="order_options-champions[]"
                                            data-placeholder="Select a champion" multiple="multiple">
                                            <?= util_load_champions_select($data['champions'], ',') ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-queue-type">
                                        <label class="fs-6 fw-bold form-label mb-2">Queue Type</label>
                                        <select class="form-select" name="order_options-queue_type"
                                            data-placeholder="Select a queue type">
                                            <?= util_load_queue_type_select($data['queue_type']) ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 fv-row order-flash-position d-none">
                                        <label class="fs-6 fw-bold form-label mb-2">Flash Position</label>
                                        <input type="text" class="form-control" name="order_options-flash_position"
                                            value="<?= $data['flash_position'] ?>" placeholder="Default, on D, on F" />
                                    </div>
                                </div>
                                <div class="row border-bottom mb-4">
                                    <div class="col-md-4 mb-3 fv-row order-priority">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Priority</label>
                                        <input type="hidden" name="order_options-is_priority" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_priority" type="checkbox"
                                                value="1" <?= $data['is_priority'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-streaming">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Streaming</label>
                                        <input type="hidden" name="order_options-is_streaming" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_streaming"
                                                type="checkbox" value="1" <?= $data['is_streaming'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-solo-only">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Solo only</label>
                                        <input type="hidden" name="order_options-is_solo_only" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_solo_only"
                                                type="checkbox" value="1" <?= $data['is_solo_only'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-bonus-win">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Bonus Win</label>
                                        <input type="hidden" name="order_options-is_bonus_win" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_bonus_win"
                                                type="checkbox" value="1" <?= $data['is_bonus_win'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-premium-coaching">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Voice Chat</label>
                                        <input type="hidden" name="order_options-is_coaching" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_coaching" type="checkbox"
                                                value="1" <?= $data['is_coaching'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-hidden-duo">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Hidden Duo</label>
                                        <input type="hidden" name="order_options-is_hidden_duo" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_hidden_duo"
                                                type="checkbox" value="1" <?= $data['is_hidden_duo'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-offline-mode d-none">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Offline Mode</label>
                                        <input type="hidden" name="order_options-is_offline_mode" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_offline_mode"
                                                type="checkbox" value="1" <?= $data['is_offline_mode'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-undercover-winrate">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Undercover Winrate</label>
                                        <input type="hidden" name="order_options-is_undercover_winrate" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_undercover_winrate"
                                                type="checkbox" value="1" <?= !empty($data['is_undercover_winrate']) ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 fv-row order-moderate-kda">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Moderate KDA</label>
                                        <input type="hidden" name="order_options-is_moderate_kda" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_moderate_kda"
                                                type="checkbox" value="1" <?= !empty($data['is_moderate_kda']) ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Body-->

                            <!--begin::Header-->
                            <h3 class="fs-4 fw-semibold my-3">Order Account</h3>
                            <!--end::Header-->

                            <!--begin::Body-->
                            <div class="row">
                                <div class="col-12 mb-3 fv-row<?= strtolower((string) ($data['game'] ?? '')) === 'lol' ? '' : ' d-none' ?>" <?= strtolower((string) ($data['game'] ?? '')) === 'lol' ? '' : 'aria-hidden="true"' ?>>
                                    <label class="fs-6 fw-bold form-label mb-2">Riot ID</label>
                                    <input type="text" class="form-control" name="order_accounts-ign"
                                        value="<?= $data['ign'] ?? null ?>" placeholder="Faker#0000" autocomplete="off" spellcheck="false">
                                    <div class="lb-admin-riot-tools" data-admin-riot-tools>
                                        <div class="lb-admin-riot-actions">
                                            <button type="button" class="btn btn-sm btn-soft-primary lb-admin-riot-check" data-admin-riot-check>
                                                <i class="fa-duotone fa-user-magnifying-glass me-1"></i> Find account
                                            </button>
                                            <button type="button" class="btn btn-sm btn-white lb-admin-riot-manual" data-admin-riot-manual>
                                                <i class="fa-duotone fa-keyboard me-1"></i> Manual entry
                                            </button>
                                            <span class="lb-admin-riot-hint">Admins can save manually even if Riot API cannot verify it.</span>
                                        </div>
                                        <div class="lb-admin-riot-preview is-idle" data-admin-riot-preview hidden>
                                            <div class="lb-admin-riot-preview__icon">
                                                <img src="" alt="" data-admin-riot-icon hidden>
                                                <i class="fa-duotone fa-user-magnifying-glass" data-admin-riot-icon-fallback></i>
                                            </div>
                                            <div class="lb-admin-riot-preview__body">
                                                <div class="lb-admin-riot-preview__label" data-admin-riot-label>Account preview</div>
                                                <div class="lb-admin-riot-preview__name" data-admin-riot-name>Enter Riot ID to verify</div>
                                                <div class="lb-admin-riot-preview__meta" data-admin-riot-meta></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3 fv-row order-login">
                                    <label class="fs-6 fw-bold form-label mb-2">Account Username</label>
                                    <input type="text" class="form-control" name="order_accounts-login"
                                        value="<?= $data['login'] ?? null ?>" placeholder="Account Username">
                                </div>
                                <div class="col-lg-6 mb-3 fv-row order-password">
                                    <label class="fs-6 fw-bold form-label mb-2">Account Password</label>
                                    <input type="text" class="form-control" name="order_accounts-password"
                                        value="<?= $data['password'] ?? null ?>" placeholder="********">
                                </div>

                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Item--->
                    <?php elseif ($data['game'] === 'val'): ?>
                    <div class="accordion accordion-icon-toggle" id="panel-1">
                        <!--begin::Item-->
                            <div class="mb-5">
                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Type & Server</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                    <div class="col-lg-6 mb-3 fv-row">
                                        <label class="fs-6 fw-bold form-label mb-2">Boost Type</label>
                                        <select class="form-select" name="orders-form_id"
                                            data-placeholder="Select a boost type">
                                            <?= util_load_boost_forms_select($data['form_id'], 'val') ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row">

                                        <label class="fs-6 fw-bold form-label mb-2">Server</label>
                                        <select class="form-select" name="order_options-server"
                                            data-placeholder="Select a server">
                                            <?= util_load_server_select($data['server'], 'val') ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-10 mb-3 fv-row">

                                        <label class="fs-6 fw-bold form-label mb-2">Price</label>
                                        <input class="form-control border form-control-solid no-arr"
                                            value="<?= util_format_price_input($data['price']) ?>" placeholder="29.99"
                                            step="0.01" min="0" type="number" name="orders-price">
                                        <button type="button" class="btn btn-sm btn-white mt-2 lb-edit-calc-price">
                                            <i class="fa-duotone fa-calculator me-1"></i> Calculate price
                                        </button>

                                    </div>
                                    <div class="col-lg-2 col-md-6 mb-3 fv-row">
                                        <label class="text-nowrap fs-6 fw-bold form-label mb-2">Duo</label>
                                        <input type="hidden" name="order_options-is_duo" value="0">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" name="order_options-is_duo" type="checkbox"
                                                value="1" <?= $data['is_duo'] ? 'checked' : null ?>>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Body-->


                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Details</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                    <?php if ($data['form_id'] != 16): ?>
                                        <div class="col-lg-8 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">Start Tier</label>
                                            <select class="form-select" name="order_options-start_tier"
                                                data-placeholder="Select a tier">
                                                <?= util_load_val_tier_select(0, 9, $data['start_tier']) ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">Start Division</label>
                                            <select class="form-select" name="order_options-start_division"
                                                data-placeholder="Select a division">
                                                <?= util_load_val_division_select($data['start_division']) ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-8 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">End Tier</label>
                                            <select class="form-select" name="order_options-end_tier"
                                                data-placeholder="Select a tier">
                                                <?= util_load_val_tier_select(0, 9, $data['end_tier']) ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">End Division</label>
                                            <select class="form-select" name="order_options-end_division"
                                                data-placeholder="Select a division">
                                                <?= util_load_val_division_select($data['end_division']) ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-6 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">Start RR</label>
                                            <input type="text" class="form-control" data-kt-dialer-control="input"
                                                placeholder="0-20" name="order_options-start_rr"
                                                value="<?= $data['start_rr'] ?>" />
                                        </div>
                                        <div class="col-12 mb-3 fv-row">
                                            <label class="fs-6 fw-bold form-label mb-2">Matches Amount</label>
                                            <input type="text" class="form-control text-center" placeholder="5"
                                                name="order_options-matches" value="<?= $data['matches'] ?>" />
                                        </div>
                                        <?php if (!empty($lb_admin_is_ranked_5s)): ?>
                                          <div class="col-12 mb-3 fv-row order-boosters">
                                            <label class="fs-6 fw-bold form-label mb-2">Boosters</label>
                                            <input type="number" class="form-control text-center" min="1" max="4"
                                                   name="order_options-boosters"
                                                   value="<?= (int)($data['boosters'] ?? 1) ?>">
                                            <div class="form-text">Ranked 5s uses this as the number of booster slots.</div>
                                          </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 mb-3 fv-row">
                                        <label class="fs-6 fw-bold form-label mb-2">Hours</label>
                                        <input type="text" class="form-control text-center" placeholder="5"
                                            name="order_options-hours" value="<?= $data['hours'] ?>" />
                                    </div>
                                    <div class="col-12 mb-3 fv-row">
                                        <label class="fs-6 fw-bold form-label mb-2">Coach Type</label>
                                        <select class="form-select" name="order_options-coach_type"
                                            data-placeholder="Select a coach type">
                                            <option value="normal" <?= $data['coach_type'] == 'normal' ? ' selected=""' : null ?>>
                                                Normal Coach</option>
                                            <option value="elite" <?= $data['coach_type'] == 'elite' ? ' selected=""' : null ?>>
                                                Elite Coach</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <!--end::Body-->

                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Options</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                    <div class="col-12 mb-3 fv-row">
                                        <label class="fs-6 fw-bold form-label mb-2">Agents</label>
                                        <select class="js-select form-select" name="order_options-agents[]"
                                            data-placeholder="Select an agent" multiple="multiple">
                                            <?= util_load_agents_select($data['agents'], ',') ?>
                                        </select>
                                    </div>
                                    <?php if ($data['form_id'] != 16): ?>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Priority</label>
                                            <input type="hidden" name="order_options-is_priority" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_priority" type="checkbox"
                                                    value="1" <?= $data['is_priority'] ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Streaming</label>
                                            <input type="hidden" name="order_options-is_streaming" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_streaming"
                                                    type="checkbox" value="1" <?= $data['is_streaming'] ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Solo only</label>
                                            <input type="hidden" name="order_options-is_solo_only" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_solo_only"
                                                    type="checkbox" value="1" <?= $data['is_solo_only'] ? 'checked' : null ?>>
                                            </div>
                                        </div>

                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Bonus Win</label>
                                            <input type="hidden" name="order_options-is_bonus_win" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_bonus_win"
                                                    type="checkbox" value="1" <?= $data['is_bonus_win'] ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Voice Chat</label>
                                            <input type="hidden" name="order_options-is_coaching" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_coaching" type="checkbox"
                                                    value="1" <?= $data['is_coaching'] ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Hidden Duo</label>
                                            <input type="hidden" name="order_options-is_hidden_duo" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_hidden_duo"
                                                    type="checkbox" value="1" <?= $data['is_hidden_duo'] ? 'checked' : null ?>>
                                            </div>
                                        </div>

                                        <div class="col mb-3 fv-row d-none" aria-hidden="true">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Offline Mode</label>
                                            <input type="hidden" name="order_options-is_offline_mode" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_offline_mode"
                                                    type="checkbox" value="1" <?= $data['is_offline_mode'] ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Undercover Winrate</label>
                                            <input type="hidden" name="order_options-is_undercover_winrate" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_undercover_winrate"
                                                    type="checkbox" value="1" <?= !empty($data['is_undercover_winrate']) ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                        <div class="col mb-3 fv-row">
                                            <label class="text-nowrap fs-6 fw-bold form-label mb-2">Moderate KDA</label>
                                            <input type="hidden" name="order_options-is_moderate_kda" value="0">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="order_options-is_moderate_kda"
                                                    type="checkbox" value="1" <?= !empty($data['is_moderate_kda']) ? 'checked' : null ?>>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!--end::Body-->

                                <!--begin::Header-->
                                <h3 class="fs-4 fw-semibold my-3">Order Account</h3>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="row">
                                        <div class="col-12 mb-3 fv-row d-none" aria-hidden="true">
                                            <label class="fs-6 fw-bold form-label mb-2">Riot ID</label>
                                        <input type="text" class="form-control" name="order_accounts-ign"
                                            value="<?= $data['ign'] ?? null ?>" placeholder="Faker#0000" autocomplete="off" spellcheck="false">
                                        <div class="lb-admin-riot-tools" data-admin-riot-tools>
                                            <div class="lb-admin-riot-actions">
                                                <button type="button" class="btn btn-sm btn-soft-primary lb-admin-riot-check" data-admin-riot-check>
                                                    <i class="fa-duotone fa-user-magnifying-glass me-1"></i> Find account
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white lb-admin-riot-manual" data-admin-riot-manual>
                                                    <i class="fa-duotone fa-keyboard me-1"></i> Manual entry
                                                </button>
                                                <span class="lb-admin-riot-hint">Admins can save manually even if Riot API cannot verify it.</span>
                                            </div>
                                            <div class="lb-admin-riot-preview is-idle" data-admin-riot-preview hidden>
                                                <div class="lb-admin-riot-preview__icon">
                                                    <img src="" alt="" data-admin-riot-icon hidden>
                                                    <i class="fa-duotone fa-user-magnifying-glass" data-admin-riot-icon-fallback></i>
                                                </div>
                                                <div class="lb-admin-riot-preview__body">
                                                    <div class="lb-admin-riot-preview__label" data-admin-riot-label>Account preview</div>
                                                    <div class="lb-admin-riot-preview__name" data-admin-riot-name>Enter Riot ID to verify</div>
                                                    <div class="lb-admin-riot-preview__meta" data-admin-riot-meta></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!util_is_riot_only_order_account($data)): ?>
                                    <div class="col-lg-6 mb-3 fv-row order-login">
                                        <label class="fs-6 fw-bold form-label mb-2">VAL Username</label>
                                        <input type="text" class="form-control" name="order_accounts-login"
                                            value="<?= $data['login'] ?? null ?>" placeholder="VAL Username">
                                    </div>
                                    <div class="col-lg-6 mb-3 fv-row order-password">
                                        <label class="fs-6 fw-bold form-label mb-2">VAL Password</label>
                                        <input type="text" class="form-control" name="order_accounts-password"
                                            value="<?= $data['password'] ?? null ?>" placeholder="********">
                                    </div>
                                    <?php endif; ?>

                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Item-->
                        </div>
                    <?php else: ?>
                        <?php
                        $_lbGenericGame = strtolower((string)($data['game'] ?? ''));
                        $_lbGenericFormType = (string)($data['type'] ?? '');
                        $_lbGenericIsRank = ($_lbGenericFormType === 'rank');
                        $_lbGenericRankCfg = ($_lbGenericIsRank && function_exists('lb_generic_game_rank_config'))
                            ? lb_generic_game_rank_config($_lbGenericGame)
                            : null;
                        $_lbGenericDivLabel = function (int $value, int $count): string {
                            if ($count === 4) return [1 => 'IV', 2 => 'III', 3 => 'II', 4 => 'I'][$value] ?? (string)$value;
                            if ($count === 3) return [1 => 'III', 2 => 'II', 3 => 'I'][$value] ?? (string)$value;
                            if ($count === 5) return [1 => '5', 2 => '4', 3 => '3', 4 => '2', 5 => '1'][$value] ?? (string)$value;
                            return (string)$value;
                        };
                        ?>
                        <div class="row pt-4">
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Boost Type</label>
                                <select class="form-select" name="orders-form_id" data-placeholder="Select a boost type">
                                    <?= util_load_boost_forms_select($data['form_id'], $data['game'] ?? '') ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Server</label>
                                <input class="form-control border form-control-solid" name="order_options-server" value="<?= htmlspecialchars((string)($data['server'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Platform</label>
                                <input class="form-control border form-control-solid" name="order_options-platform" value="<?= htmlspecialchars((string)($data['platform'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Queue Type / Ranked Marks</label>
                                <input class="form-control border form-control-solid" name="order_options-queue_type" value="<?= htmlspecialchars((string)($data['queue_type'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Games / Wins</label>
                                <input class="form-control border form-control-solid" type="number" min="0" name="order_options-matches" value="<?= htmlspecialchars((string)($data['matches'] ?? '')) ?>">
                            </div>

                            <?php if ($_lbGenericRankCfg !== null): ?>
                                <?php
                                $_lbGenericRanks = $_lbGenericRankCfg['ranks'] ?? [];
                                $_lbGenericRankDivs = $_lbGenericRankCfg['rank_divs'] ?? [];
                                $_lbGenericFlatTiers = array_map('strval', $_lbGenericRankCfg['flat_tiers'] ?? []);
                                $_lbGenericStartTier = (int)($data['start_tier'] ?? 0);
                                $_lbGenericEndTier = (int)($data['end_tier'] ?? 0);
                                $_lbGenericStartDivCount = (int)($_lbGenericRankDivs[$_lbGenericStartTier] ?? $_lbGenericRankDivs[(string)$_lbGenericStartTier] ?? 4);
                                $_lbGenericEndDivCount = (int)($_lbGenericRankDivs[$_lbGenericEndTier] ?? $_lbGenericRankDivs[(string)$_lbGenericEndTier] ?? 4);
                                $_lbGenericStartIsFlat = in_array((string)$_lbGenericStartTier, $_lbGenericFlatTiers, true);
                                $_lbGenericEndIsFlat = in_array((string)$_lbGenericEndTier, $_lbGenericFlatTiers, true);
                                ?>
                                <script>
                                  window.__lbGenericRankCfg = window.__lbGenericRankCfg || {};
                                  window.__lbGenericRankCfg['<?= md5($data['game'] ?? '') ?>'] = <?= json_encode([
                                    'ranks' => $_lbGenericRanks,
                                    'rank_divs' => $_lbGenericRankDivs,
                                    'flat_tiers' => $_lbGenericFlatTiers,
                                  ], JSON_UNESCAPED_UNICODE) ?>;
                                </script>
                                <div class="col-md-6 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Start Tier</label>
                                    <select class="form-select lb-generic-tier" data-lb-generic-role="start" data-lb-generic-game="<?= md5($data['game'] ?? '') ?>" name="order_options-start_tier">
                                        <?php foreach ($_lbGenericRanks as $_t => $_name): ?>
                                            <option value="<?= (int)$_t ?>" <?= (int)$_t === $_lbGenericStartTier ? 'selected' : '' ?>><?= htmlspecialchars((string)$_name, ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 lb-generic-division-wrap" data-lb-generic-role-wrap="start" style="<?= $_lbGenericStartIsFlat || $_lbGenericStartDivCount <= 0 ? 'display:none;' : '' ?>">
                                    <label class="fs-6 fw-bold form-label mb-2">Start Division</label>
                                    <select class="form-select lb-generic-division" data-lb-generic-role="start" name="order_options-start_division">
                                        <?php for ($_d = 1; $_d <= max(1, $_lbGenericStartDivCount); $_d++): ?>
                                            <option value="<?= $_d ?>" <?= $_d === (int)($data['start_division'] ?? 0) ? 'selected' : '' ?>><?= $_lbGenericDivLabel($_d, $_lbGenericStartDivCount) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Target Tier</label>
                                    <select class="form-select lb-generic-tier" data-lb-generic-role="end" data-lb-generic-game="<?= md5($data['game'] ?? '') ?>" name="order_options-end_tier">
                                        <?php foreach ($_lbGenericRanks as $_t => $_name): ?>
                                            <option value="<?= (int)$_t ?>" <?= (int)$_t === $_lbGenericEndTier ? 'selected' : '' ?>><?= htmlspecialchars((string)$_name, ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 lb-generic-division-wrap" data-lb-generic-role-wrap="end" style="<?= $_lbGenericEndIsFlat || $_lbGenericEndDivCount <= 0 ? 'display:none;' : '' ?>">
                                    <label class="fs-6 fw-bold form-label mb-2">Target Division</label>
                                    <select class="form-select lb-generic-division" data-lb-generic-role="end" name="order_options-end_division">
                                        <?php for ($_d = 1; $_d <= max(1, $_lbGenericEndDivCount); $_d++): ?>
                                            <option value="<?= $_d ?>" <?= $_d === (int)($data['end_division'] ?? 0) ? 'selected' : '' ?>><?= $_lbGenericDivLabel($_d, $_lbGenericEndDivCount) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <script>
                                (function () {
                                  function genericDivLabel(v, count) {
                                    v = parseInt(v, 10) || 0;
                                    if (count === 4) return ({1:'IV',2:'III',3:'II',4:'I'})[v] || String(v);
                                    if (count === 3) return ({1:'III',2:'II',3:'I'})[v] || String(v);
                                    if (count === 5) return ({1:'5',2:'4',3:'3',4:'2',5:'1'})[v] || String(v);
                                    return String(v);
                                  }
                                  document.querySelectorAll('select.lb-generic-tier').forEach(function (sel) {
                                    sel.addEventListener('change', function () {
                                      var role = sel.getAttribute('data-lb-generic-role');
                                      var cfg = (window.__lbGenericRankCfg || {})[sel.getAttribute('data-lb-generic-game')];
                                      if (!cfg) return;
                                      var t = sel.value;
                                      var count = parseInt((cfg.rank_divs || {})[t] ?? 4, 10) || 0;
                                      var isFlat = (cfg.flat_tiers || []).map(String).includes(String(t));
                                      var wrap = document.querySelector('.lb-generic-division-wrap[data-lb-generic-role-wrap="' + role + '"]');
                                      var divSel = document.querySelector('select.lb-generic-division[data-lb-generic-role="' + role + '"]');
                                      if (isFlat || count <= 0) {
                                        if (wrap) wrap.style.display = 'none';
                                        return;
                                      }
                                      if (wrap) wrap.style.display = '';
                                      if (divSel) {
                                        var opts = '';
                                        for (var d = 1; d <= count; d++) {
                                          opts += '<option value="' + d + '">' + genericDivLabel(d, count) + '</option>';
                                        }
                                        divSel.innerHTML = opts;
                                      }
                                    });
                                  });
                                })();
                                </script>
                            <?php else: ?>
                                <div class="col-md-3 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Start Tier</label>
                                    <input class="form-control border form-control-solid" type="number" min="0" name="order_options-start_tier" value="<?= htmlspecialchars((string)($data['start_tier'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Start Division</label>
                                    <input class="form-control border form-control-solid" type="number" min="0" name="order_options-start_division" value="<?= htmlspecialchars((string)($data['start_division'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Target Tier</label>
                                    <input class="form-control border form-control-solid" type="number" min="0" name="order_options-end_tier" value="<?= htmlspecialchars((string)($data['end_tier'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="fs-6 fw-bold form-label mb-2">Target Division</label>
                                    <input class="form-control border form-control-solid" type="number" min="0" name="order_options-end_division" value="<?= htmlspecialchars((string)($data['end_division'] ?? '')) ?>">
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Current LP / RP</label>
                                <input class="form-control border form-control-solid" name="order_options-start_lp" value="<?= htmlspecialchars((string)($data['start_lp'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Target LP / RP</label>
                                <input class="form-control border form-control-solid" name="order_options-end_lp" value="<?= htmlspecialchars((string)($data['end_lp'] ?? '')) ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="fs-6 fw-bold form-label mb-2">Price</label>
                                <input class="form-control border form-control-solid no-arr" value="<?= util_format_price_input($data['price']) ?>" step="0.01" min="0" type="number" name="orders-price">
                                <button type="button" class="btn btn-sm btn-white mt-2 lb-edit-calc-price">
                                    <i class="fa-duotone fa-calculator me-1"></i> Calculate price
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Edit Order
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="delete_order_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="set_order_paidTitle">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form class="ajax-form" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="admin_delete_order">
                    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Yes, delete
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="admin_create_note">
    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

    <div id="create_note_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="create_new_note_title">New Note</h5>
                </div>

                <div class="modal-body">
                    <div class="mb-4">
                        <label class="mb-2">Note Type</label>
                        <select class="form-select" name="order_note_type" data-placeholder="Select the type of note">
                            <option value="payment">Payment Note</option>
                            <option value="progress" selected>Progress Note</option>
                            <option value="booster">Booster Note</option>
                            <option value="client">Client Note</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Body</label>
                        <textarea class="form-control" name="note_body" rows="3" value=""
                            placeholder="Note order changes and updates"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Note</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="admin_edit_note">
    <input type="hidden" name="note_id" id="edit_note_id">

    <div id="edit_note_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_note_title">Edit Note</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="mb-2">Note Type</label>
                        <select class="form-select" name="order_note_type" data-placeholder="Select the type of note">
                            <option value="payment">Payment Note</option>
                            <option value="progress" selected>Progress Note</option>
                            <option value="booster">Booster Note</option>
                            <option value="client">Client Note</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <label class="mb-2">Body</label>
                    <textarea class="form-control" name="note_body" rows="3"
                        placeholder="Note order changes and updates"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_note_md"
                        data-note-id="">Delete Note</button>
                    <button type="submit" class="btn btn-primary">Edit Note</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="delete_note_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_note_title">Delete Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this note?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form class="ajax-form" action="<?= AJAX_URL ?>">
                    <input type="hidden" name="action" value="admin_delete_note">
                    <input type="hidden" name="note_id" id="delete_note_id">
                    <button type="submit" class="btn btn-danger">
                        <span class="indicator-label">
                            Yes, delete
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<div id="unassign_booster_md" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Unassign Booster</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to unassign the booster from this order?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>

        <form class="ajax-form" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action" value="admin_unassign_booster">
          <input type="hidden" name="order_id" value="<?= (int)$data['id'] ?>">
          <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
          <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Yes, unassign booster</span>
            <span class="indicator-progress">
              <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
            </span>
          </button>
        </form>

      </div>
    </div>
  </div>
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="admin_create_invoice">
    <input type="hidden" name="client_id" value="<?= $data['client_id'] ?>">
    <input type="hidden" name="order_id" value="<?= $data['order_id'] ?>">

    <div id="admin_create_invoice_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create Custom Invoice For This Order</h5>
                </div>
                <div class="modal-body">
                    <label class="fs-6 form-label mb-2">Amount to be charged:</label>
                    <div class="input-group mb-4">
                        <span class="input-group-text">&euro;</span>
                        <input type="text" class="form-control" name="amount" value='0' placeholder="0.00">
                    </div>
                    <div>
                        <label class="fs-6 form-label mb-2">Description</label>
                        <textarea class="form-control" name="description"
                            placeholder="Reason for charging the amount..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if (!empty($review)): ?>
    <form id="leave-review-form">
        <input type="hidden" name="action" value="admin_edit_review">
        <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
        <input type="hidden" name="booster_id" value="<?= $data['booster_id'] ?>">

        <div id="leave_review_md" class="modal fade lb-modal " tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header pb-4 border-bottom">
                        <h5 class="modal-title" id="set_order_paidTitle">View Client Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5>Communication</h5>
                                    <input class="rating-input" type="text" name="score[communication]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['communication']) . '"' : '' ?>>
                                    <p class="text-muted mt-2 mb-0">
                                        How good was the communication (updates, answers, friendliness)?
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5>Skill</h5>
                                    <input class="rating-input" type="text" name="score[skill]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['skill']) . '"' : '' ?>>
                                    <p class="text-muted mt-2 mb-0">
                                        How strong was the booster in-game (decisions, consistency)?
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5>Speed</h5>
                                    <input class="rating-input" type="text" name="score[speed]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['speed']) . '"' : '' ?>>
                                    <p class="text-muted mt-2 mb-0">
                                        How quickly was the order completed?
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5>Overall</h5>
                                    <input class="rating-input" type="text" name="score[overall]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['overall']) . '"' : '' ?>>
                                    <p class="text-muted mt-2 mb-0">
                                        Overall impression.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card p-3 mt-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5 class="d-flex justify-content-between">
                                        Highlights

                                        <span id="highlight-count" class="text-muted">
                                            0/3
                                        </span>
                                    </h5>

                                    <div class="highlights d-flex align-items-center gap-2 flex-wrap mt-2">
                                        <?php
                                        foreach (get_review_highlights() as $highlight): ?>
                                            <input type="checkbox" class="btn-check"
                                                id="btn-check-<?= htmlspecialchars($highlight) ?>" name="highlights[]"
                                                value="<?= htmlspecialchars($highlight) ?>" autocomplete="off"
                                                <?= !empty($review) && in_array($highlight, json_decode($review['highlights']) ?? [], true) ? 'checked' : '' ?>>
                                            <label class="btn rounded-pill" for="btn-check-<?= htmlspecialchars($highlight) ?>">
                                                <?= ucwords(str_replace('_', ' ', $highlight)) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card p-3 mt-3" style="background: rgba(255, 255, 255, .03);">
                                    <h5>Additional Comments (Optional)</h5>
                                    <textarea class="form-control lb-textarea" name="comments" rows="4"
                                        placeholder="Share more details about your experience..."><?= !empty($review) ? htmlspecialchars($review['comments']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <?php if ($review['approved'] == 0): ?>
                            <button type="button" class="btn btn-success lb-btn lb-btn-success" data-id="<?= $review['id'] ?>"
                                data-action="admin_approve_review">
                                Approve Review
                                <i class="fa-duotone fa-check ms-2"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-danger lb-btn lb-btn-warning" data-id="<?= $review['id'] ?>"
                                data-action="admin_disapprove_review">
                                Disapprove Review
                                <i class="fa-duotone fa-times ms-2"></i>
                            </button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary lb-btn lb-btn-success" id="addon_next_btn">
                            <div class="spinner-border d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>
                                Update Review
                                <i class="fa-duotone fa-paper-plane ms-2"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

</div>

<?= $this->start('scripts') ?>
<script>
  /**
   * Fallback AJAX handler:
   * If for any reason the global ajax-form handler is not bound, prevent navigation to /ajax
   * and handle forms with class="ajax-form" via XHR (same response contract: sendToast, playSound, redirectUrl, refreshPage, resetForm).
   */
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') return;

    function lbHandleAjaxResponse(res, $form) {
      let response = res;
      try {
        if (typeof res === 'string') response = JSON.parse(res);
      } catch (e) {
        // Not JSON, ignore
        response = null;
      }
      if (!response) return;

      if (response.resetForm && $form && $form[0]) {
        $form[0].reset();
      }

      if (response.sendToast && typeof window.create_toast === 'function') {
        create_toast(response.sendToast.type, response.sendToast.title, response.sendToast.message);
      }

      if (response.playSound) {
        try {
          var audio = new Audio(asset_url + '/core/dash/audio/' + response.playSound + '.mp3');
          audio.play();
        } catch (e) {}
      }

      if (response.redirectUrl) {
        setTimeout(function () {
          window.location.href = response.redirectUrl;
        }, 250);
      } else if (response.refreshPage && !(lbIsRanked5sOrder || response.is_ranked_5s || response.ranked_5s)) {
        setTimeout(function () {
          location.reload();
        }, 250);
      }
    }

    // ── Refresh Order Progress (Riot rank sync) ──────────────────────────
    <?php
      $op_mode = $op_mode ?? 'rank';
      $op_base_target = $op_base_target ?? (int)($data['matches'] ?? 0);
      $op_is_win_type = $op_is_win_type ?? false;
      $op_is_win_boost_form = $op_is_win_boost_form ?? false;
      $op_is_pro_games_form = $op_is_pro_games_form ?? false;
      $op_is_duo_pass_form = $op_is_duo_pass_form ?? false;
    ?>
    const refreshProgressBtn    = document.getElementById('refreshProgressBtn');
    const progressStartEl       = document.getElementById('riotProgressStartRank');
    const progressStartImgEl    = document.getElementById('riotProgressStartRankImg');
    const progressCurrentEl     = document.getElementById('riotProgressCurrentRank');
    const progressCurrentImgEl  = document.getElementById('riotProgressCurrentRankImg');
    const progressWinsEl        = document.getElementById('riotProgressWins');
    const progressLossesEl      = document.getElementById('riotProgressLosses');
    const progressRecordEl      = document.getElementById('riotProgressRecord');
    const progressLastMatchEl   = document.getElementById('riotProgressLastMatch');
    const progressLastSyncEl    = document.getElementById('riotProgressLastSync');
    const progressWrBarEl       = document.getElementById('riotProgressWrBar');
    const progressSyncStateEl   = document.getElementById('riotProgressSyncState');
    const hasRiotId   = <?= empty(trim((string) ($data['ign'] ?? ''))) ? 'false' : 'true' ?>;
    const opMode      = <?= json_encode($op_mode) ?>;
    const opTarget    = <?= (int) ($op_base_target ?? ($data['matches'] ?? 0)) ?>;
    const opIsWinType = <?= json_encode($op_is_win_type) ?>;
    const opIsWinBoostForm = <?= json_encode($op_is_win_boost_form ?? false) ?>;
    const opIsProGamesForm = <?= json_encode($op_is_pro_games_form ?? false) ?>;
    const opIsDuoPassForm = <?= json_encode($op_is_duo_pass_form ?? false) ?>;
    const opIsClassicRank = <?= json_encode($op_is_classic_rank ?? false) ?>;
    const classicRankNames = ['Unranked','Salt','Wood','Silver','Gold','Platinum','Diamond','Legend'];
    let isRefreshingProgress = false;
    let lastProgressRefresh = 0;
    const PROGRESS_REFRESH_COOLDOWN = 30000; // ms

    const rankTierIds = {
      IRON: 1, BRONZE: 2, SILVER: 3, GOLD: 4,
      PLATINUM: 5, EMERALD: 6, DIAMOND: 7,
      MASTER: 8, GRANDMASTER: 9, CHALLENGER: 10
    };

    function tierToImgUrl(tier) {
      if (opIsClassicRank) {
        const classicId = Math.max(0, Math.min(7, parseInt(tier || 0, 10) || 0));
        return '/public/assets/website/images/lol-classic/ranks/' + classicRankNames[classicId].toLowerCase() + '.webp';
      }
      const id = rankTierIds[(tier || '').toString().toUpperCase().trim()] ?? 0;
      return asset_url + '/core/main/img/lol/ranks/max/' + id + '.png';
    }

    function formatRankValue(tier, division, lp) {
      if (opIsClassicRank) {
        const classicId = Math.max(0, Math.min(7, parseInt(tier || 0, 10) || 0));
        return classicRankNames[classicId];
      }
      const cleanTier = (tier ?? '').toString().trim();
      const cleanDivision = (division ?? '').toString().trim();
      const cleanLp = (lp ?? '').toString().trim();

      if (!cleanTier) return 'Unranked';

      let value = cleanTier.charAt(0).toUpperCase() + cleanTier.slice(1).toLowerCase();
      if (cleanDivision) value += ` ${cleanDivision}`;
      if (cleanLp !== '') value += ` · ${cleanLp} LP`;
      return value;
    }

    function formatSyncDate(value) {
      const raw = (value ?? '').toString().trim();
      if (!raw) return 'Never';

      const normalized = raw.replace(' ', 'T');
      const parsed = new Date(normalized);
      if (Number.isNaN(parsed.getTime())) {
        return raw;
      }

      return parsed.toLocaleString();
    }

    function formatRecordValue(wins, losses) {
      const totalWins = parseInt(wins ?? 0, 10) || 0;
      const totalLosses = parseInt(losses ?? 0, 10) || 0;
      const totalGames = totalWins + totalLosses;

      if (totalGames <= 0) return '–';
      return `${((totalWins / totalGames) * 100).toFixed(1)}%`;
    }

    function applyRecordTone(wins, losses) {
      if (!progressRecordEl) return;

      const totalWins = parseInt(wins ?? 0, 10) || 0;
      const totalLosses = parseInt(losses ?? 0, 10) || 0;
      const totalGames = totalWins + totalLosses;

      progressRecordEl.classList.remove('text-success', 'text-muted');

      if (totalGames <= 0) {
        progressRecordEl.classList.add('text-muted');
        return;
      }

      if ((totalWins / totalGames) >= 0.6) {
        progressRecordEl.classList.add('text-success');
      }
    }

    function setProgressSyncState(message, type, loading) {
      if (!progressSyncStateEl) return;

      const tone = type || 'muted';
      const isLoading = !!loading;
      progressSyncStateEl.classList.remove('text-muted', 'text-danger', 'text-success');
      progressSyncStateEl.classList.add(
        tone === 'danger' ? 'text-danger' : (tone === 'success' ? 'text-success' : 'text-muted')
      );

      if (!message) {
        progressSyncStateEl.textContent = '';
        return;
      }

      progressSyncStateEl.innerHTML = isLoading
        ? `<i class="fa-duotone fa-loader fa-spin me-2"></i>${message}`
        : message;
    }

    function applyProgressData(progress) {
      if (!progress || typeof progress !== 'object') return;

      if (progressStartEl) {
        progressStartEl.textContent = formatRankValue(progress.start_tier, progress.start_division, progress.start_lp);
      }

      if (progressStartImgEl) {
        const startIcon = (progress.start_icon || progress.start_rank_icon || '').toString().trim();
        if (startIcon) {
          progressStartImgEl.src = startIcon;
        } else if (progress.start_tier) {
          progressStartImgEl.src = tierToImgUrl(progress.start_tier);
        }
      }

      if (progressCurrentEl) {
        progressCurrentEl.textContent = formatRankValue(progress.current_tier, progress.current_division, progress.current_lp);
      }

      if (progressCurrentImgEl) {
        const currentIcon = (progress.current_icon || progress.current_rank_icon || '').toString().trim();
        if (currentIcon) {
          progressCurrentImgEl.src = currentIcon;
        } else if (progress.current_tier) {
          progressCurrentImgEl.src = tierToImgUrl(progress.current_tier);
        }
      }

      if (progressWinsEl) {
        progressWinsEl.textContent = `${parseInt(progress.wins ?? 0, 10) || 0}`;
      }

      if (progressLossesEl) {
        progressLossesEl.textContent = `${parseInt(progress.losses ?? 0, 10) || 0}`;
      }

      if (progressRecordEl) {
        progressRecordEl.textContent = formatRecordValue(progress.wins, progress.losses);
      }
      applyRecordTone(progress.wins, progress.losses);

      if (progressWrBarEl) {
        const totalWins = parseInt(progress.wins ?? 0, 10) || 0;
        const totalLosses = parseInt(progress.losses ?? 0, 10) || 0;
        const totalGames = totalWins + totalLosses;
        const pct = totalGames > 0 ? (totalWins / totalGames) * 100 : 0;
        progressWrBarEl.style.width = pct.toFixed(1) + '%';
        progressWrBarEl.classList.remove('lb-op-wr-bar-fill--good');
        if (totalGames > 0 && pct >= 60) {
          progressWrBarEl.classList.add('lb-op-wr-bar-fill--good');
        }
      }

      if (progressLastMatchEl) {
        const lastMatchId = (progress.last_match_id ?? '').toString().trim();
        progressLastMatchEl.textContent = lastMatchId || '';
      }

      if (progressLastSyncEl) {
        progressLastSyncEl.textContent = formatSyncDate(progress.last_sync_at);
      }

      // Count mode: update played count, dynamic target + progress bar fill
      const playedEl   = document.getElementById('riotProgressPlayed');
      const targetEl   = document.getElementById('riotProgressTarget');
      const countBarEl = document.getElementById('riotProgressCountBar');
      const _w = parseInt(progress.wins ?? 0, 10) || 0;
      const _l = parseInt(progress.losses ?? 0, 10) || 0;
      if (opIsDuoPassForm) return;
      const played = opIsWinBoostForm ? Math.max(0, _w - _l) : (opIsProGamesForm ? (_w + _l) : (opIsWinType ? _w : (_w + _l)));
      const dynamicTarget = opTarget;

      if (playedEl) {
        playedEl.textContent = played;
      }

      if (targetEl) {
        targetEl.textContent = dynamicTarget;
      }

      if (countBarEl) {
        let pct = 0;
        if (opTarget > 0) {
          pct = Math.min(100, (played / opTarget) * 100);
        }
        countBarEl.style.width = pct.toFixed(1) + '%';
        countBarEl.classList.toggle('lb-op-count-progress-fill--done', pct >= 100);
      }
    }

    async function syncOrderProgress(options) {
      const opts = options || {};
      const silent = !!opts.silent;

      if (isRefreshingProgress) return;
      if (!silent) {
        const _now = Date.now();
        const _elapsed = _now - lastProgressRefresh;
        if (_elapsed < PROGRESS_REFRESH_COOLDOWN) {
          const _remaining = Math.ceil((PROGRESS_REFRESH_COOLDOWN - _elapsed) / 1000);
          if (typeof create_toast === 'function') create_toast('warning', 'Cooldown', 'Please wait ' + _remaining + 's before refreshing again.');
          return;
        }
        lastProgressRefresh = _now;
      }
      if (!hasRiotId) {
        setProgressSyncState('Riot ID missing. Tracking cannot run.', 'danger', false);
        return;
      }

      isRefreshingProgress = true;
      const btn = refreshProgressBtn;
      const icon = btn ? btn.querySelector('i') : null;

      if (btn) {
        btn.disabled = true;
      }
      if (icon) {
        icon.classList.add('fa-spin');
      }
      setProgressSyncState('Refreshing progress from Riot API...', 'muted', true);

      try {
        const fd = new FormData();
        fd.append('action', 'admin_refresh_order_progress');
        fd.append('order_id', '<?= (int) $data['id'] ?>');
        if (silent) {
          fd.append('silent', '1');
        }

        const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
        let json = null;
        try {
          json = await res.json();
        } catch (e) {
          json = null;
        }

        if (!json || typeof json !== 'object') {
          throw new Error('Invalid response while refreshing progress.');
        }

        if (json.orderProgress) {
          applyProgressData(json.orderProgress);
        }

        if (json.ok) {
          setProgressSyncState('Progress updated successfully.', 'success', false);
          document.dispatchEvent(new CustomEvent('lbProgressSynced'));
        } else {
          const failMessage = (json.message || 'Failed to refresh progress.').toString();
          setProgressSyncState(failMessage, 'danger', false);
        }

        if (json.sendToast && typeof window.create_toast === 'function') {
          create_toast(json.sendToast.type, json.sendToast.title, json.sendToast.message);
        }

        if (json.playSound) {
          try {
            var audio = new Audio(asset_url + '/core/dash/audio/' + json.playSound + '.mp3');
            audio.play();
          } catch (e) {}
        }
      } catch (e) {
        const message = e && e.message ? e.message : 'Failed to refresh progress.';
        setProgressSyncState(message, 'danger', false);
        if (!silent && typeof create_toast === 'function') {
          create_toast('danger', 'Error', message);
        }
      } finally {
        isRefreshingProgress = false;
        if (btn) {
          btn.disabled = false;
        }
        if (icon) {
          icon.classList.remove('fa-spin');
        }
      }
    }

    if (refreshProgressBtn) {
      refreshProgressBtn.addEventListener('click', function () {
        syncOrderProgress({ silent: false });
      });
    }

    if (opMode !== 'coaching' && hasRiotId && order_status === 'IN_PROGRESS') {
      setTimeout(function () {
        syncOrderProgress({ silent: true });
      }, 300);
    }

    // ── Match History ────────────────────────────────────────────────────
    (function () {
      const body       = document.getElementById('lbMhBody');
      const pager      = document.getElementById('lbMhPager');
      const pagerInfo  = document.getElementById('lbMhPagerInfo');
      const prevBtn    = document.getElementById('lbMhPrev');
      const nextBtn    = document.getElementById('lbMhNext');
      const totalBadge = document.getElementById('lbMhTotal');
      const countBadge = document.getElementById('lbMhCountBadge');
      const modalEl    = document.getElementById('matchHistoryModal');
      const backfillToggle = document.getElementById('lbMhBackfillToggle');
      const backfillForm   = document.getElementById('lbMhBackfillForm');
      const backfillSubmit = document.getElementById('lbMhBackfillSubmit');
      const backfillState  = document.getElementById('lbMhBackfillState');
      if (!body) return;

      const orderId  = <?= (int) $data['id'] ?>;
      const champUrl = '<?= rtrim(LOL_CHAMP_URL, '/') ?>';
      const roleUrl  = asset_url + '/core/main/img/lol/roles/';
      const ROLE_MAP  = {TOP:'TopLane',JUNGLE:'Jungle',MIDDLE:'MidLane',BOTTOM:'AdCarry',UTILITY:'Support'};
      function roleFile(pos) { return ROLE_MAP[pos] || null; }
      const perPage  = 20;
      let currentPage = 1;
      let loading     = false;

      const QUEUE_NAMES = {420:'Ranked Solo',440:'Ranked Flex',400:'Normal Draft',430:'Normal Blind',450:'ARAM',900:'URF',1020:'One For All',76:'URF'};
      function queueName(id) { return QUEUE_NAMES[parseInt(id, 10)] || 'Match'; }

      function fmtDuration(secs) {
        const s = parseInt(secs, 10) || 0;
        const m = Math.floor(s / 60);
        const r = s % 60;
        return m + ':' + String(r).padStart(2, '0');
      }

      function fmtDate(raw) {
        if (!raw) return ['—', ''];
        const d = new Date(raw.toString().replace(' ', 'T'));
        if (isNaN(d.getTime())) return [raw, ''];
        return [
          d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }),
          d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })
        ];
      }

      function escHtml(value) {
        return (value ?? '').toString().replace(/[&<>"']/g, function (ch) {
          return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
        });
      }

      function playAjaxSound(name) {
        if (!name) return;
        try {
          var audio = new Audio(asset_url + '/core/dash/audio/' + name + '.mp3');
          audio.play();
        } catch (e) {}
      }

      function showAjaxToast(json, fallbackType, fallbackTitle, fallbackMessage) {
        if (json && json.sendToast && typeof window.create_toast === 'function') {
          create_toast(json.sendToast.type, json.sendToast.title, json.sendToast.message);
        } else if (typeof window.create_toast === 'function' && fallbackMessage) {
          create_toast(fallbackType || 'success', fallbackTitle || 'Done', fallbackMessage);
        }
        if (json && json.playSound) playAjaxSound(json.playSound);
      }

      function setBackfillState(message, type) {
        if (!backfillState) return;
        backfillState.textContent = message || '';
        backfillState.className = 'lb-mh-backfill-state' + (type ? ' text-' + type : '');
      }

      const backfillRiotInput = document.getElementById('lbMhBackfillRiotId');
      const backfillRiotPreview = document.getElementById('lbMhBackfillRiotPreview');
      const backfillRiotPreviewLabel = document.getElementById('lbMhBackfillRiotPreviewLabel');
      const backfillRiotName = document.getElementById('lbMhBackfillRiotName');
      const backfillRiotMeta = document.getElementById('lbMhBackfillRiotMeta');
      const backfillRiotIcon = document.getElementById('lbMhBackfillRiotIcon');
      const backfillRiotIconFallback = document.getElementById('lbMhBackfillRiotIconFallback');
      const backfillRiotConfirm = document.getElementById('lbMhBackfillRiotConfirm');
      let backfillRiotTimer = null;
      let backfillRiotController = null;
      let backfillRiotInFlight = null;
      let backfillRiotLastValue = '';
      let backfillRiotVerifiedOk = false;
      let backfillRiotVerifiedValue = '';
      let backfillRiotConfirmedValue = '';

      function normalizeBackfillRiotId(value) {
        const parts = (value || '').split('#');
        if (parts.length < 2) return (value || '').trim();
        return parts[0].trim() + '#' + parts.slice(1).join('#').trim();
      }

      function isValidBackfillRiotId(value) {
        return /^[^#]{2,32}#.{2,16}$/.test(normalizeBackfillRiotId(value));
      }

      function resetBackfillRiotConfirmation() {
        backfillRiotConfirmedValue = '';
        if (backfillRiotConfirm) {
          backfillRiotConfirm.hidden = true;
          backfillRiotConfirm.disabled = false;
          backfillRiotConfirm.classList.remove('is-confirmed');
          backfillRiotConfirm.innerHTML = '<i class="fa-solid fa-plus"></i> Save account';
        }
      }

      function markBackfillRiotConfirmed(riotId) {
        backfillRiotConfirmedValue = normalizeBackfillRiotId(riotId || (backfillRiotInput ? backfillRiotInput.value : ''));
        if (backfillRiotConfirm) {
          backfillRiotConfirm.hidden = false;
          backfillRiotConfirm.disabled = true;
          backfillRiotConfirm.classList.add('is-confirmed');
          backfillRiotConfirm.innerHTML = '<i class="fa-solid fa-circle-check"></i> Saved';
        }
        setBackfillState('', '');
      }

      function setBackfillRiotPreview(state, data) {
        if (!backfillRiotPreview) return;
        data = data || {};
        if (state !== 'found') {
          backfillRiotVerifiedOk = false;
          backfillRiotVerifiedValue = '';
          resetBackfillRiotConfirmation();
        }
        if (state === 'idle') {
          backfillRiotPreview.hidden = true;
          backfillRiotPreview.classList.remove('is-loading', 'is-found', 'is-error');
          if (backfillRiotIcon) { backfillRiotIcon.removeAttribute('src'); backfillRiotIcon.style.display = 'none'; }
          if (backfillRiotIconFallback) backfillRiotIconFallback.style.display = 'grid';
          return;
        }
        backfillRiotPreview.hidden = false;
        backfillRiotPreview.classList.remove('is-loading', 'is-found', 'is-error');
        backfillRiotPreview.classList.add('is-' + state);

        if (state === 'loading') {
          if (backfillRiotPreviewLabel) backfillRiotPreviewLabel.textContent = 'Checking...';
          if (backfillRiotName) backfillRiotName.textContent = data.riot_id || 'Looking up account';
          if (backfillRiotMeta) backfillRiotMeta.textContent = 'Please wait a moment.';
          if (backfillRiotIcon) { backfillRiotIcon.removeAttribute('src'); backfillRiotIcon.style.display = 'none'; }
          if (backfillRiotIconFallback) backfillRiotIconFallback.style.display = 'grid';
          return;
        }

        if (state === 'found') {
          backfillRiotVerifiedOk = true;
          backfillRiotVerifiedValue = normalizeBackfillRiotId(data.riot_id || (backfillRiotInput ? backfillRiotInput.value : ''));
          backfillRiotConfirmedValue = '';
          if (backfillRiotPreviewLabel) backfillRiotPreviewLabel.textContent = 'Account found ✓';
          if (backfillRiotName) backfillRiotName.textContent = data.riot_id || data.ign || backfillRiotVerifiedValue;
          if (backfillRiotMeta) backfillRiotMeta.textContent = 'Level ' + (data.summoner_level || '?') + (data.server ? ' · ' + data.server.toUpperCase() : '');
          if (backfillRiotIcon && data.profile_icon_url) {
            backfillRiotIcon.src = data.profile_icon_url;
            backfillRiotIcon.style.display = 'block';
            if (backfillRiotIconFallback) backfillRiotIconFallback.style.display = 'none';
          } else {
            if (backfillRiotIcon) { backfillRiotIcon.removeAttribute('src'); backfillRiotIcon.style.display = 'none'; }
            if (backfillRiotIconFallback) backfillRiotIconFallback.style.display = 'grid';
          }
          if (backfillRiotConfirm) {
            backfillRiotConfirm.hidden = false;
            backfillRiotConfirm.disabled = false;
            backfillRiotConfirm.classList.remove('is-confirmed');
            backfillRiotConfirm.innerHTML = '<i class="fa-solid fa-plus"></i> Save account';
          }
          return;
        }

        if (backfillRiotPreviewLabel) backfillRiotPreviewLabel.textContent = 'Riot ID not found';
        if (backfillRiotName) backfillRiotName.textContent = data.riot_id || 'Please check the Riot ID';
        if (backfillRiotMeta) backfillRiotMeta.textContent = data.message || 'Riot ID not found';
        if (backfillRiotIcon) { backfillRiotIcon.removeAttribute('src'); backfillRiotIcon.style.display = 'none'; }
        if (backfillRiotIconFallback) backfillRiotIconFallback.style.display = 'grid';
      }

      function verifyBackfillRiotAccount(riotId) {
        riotId = normalizeBackfillRiotId(riotId || (backfillRiotInput ? backfillRiotInput.value : ''));
        if (!riotId || !backfillRiotPreview) return Promise.resolve(false);
        if (!isValidBackfillRiotId(riotId)) {
          setBackfillRiotPreview('error', { riot_id: riotId, message: 'Use format: Name#TAG' });
          return Promise.resolve(false);
        }
        if (backfillRiotVerifiedOk && backfillRiotVerifiedValue === riotId) return Promise.resolve(true);
        if (riotId === backfillRiotLastValue && backfillRiotInFlight) return backfillRiotInFlight;
        backfillRiotLastValue = riotId;
        if (backfillRiotController) backfillRiotController.abort();
        backfillRiotController = window.AbortController ? new AbortController() : null;
        setBackfillRiotPreview('loading', { riot_id: riotId });

        const fd = new FormData();
        fd.append('action', 'booster_preview_riot_account');
        fd.append('order_id', orderId);
        fd.append('riot_id', riotId);

        backfillRiotInFlight = fetch('<?= AJAX_URL ?>', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          signal: backfillRiotController ? backfillRiotController.signal : undefined
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (json && json.ok) {
              setBackfillRiotPreview('found', json.account || { riot_id: riotId });
              return true;
            }
            setBackfillRiotPreview('error', { riot_id: riotId, message: (json && json.message) || 'Riot ID not found' });
            return false;
          })
          .catch(function (e) {
            if (e && e.name === 'AbortError') return false;
            setBackfillRiotPreview('error', { riot_id: riotId, message: 'Riot ID not found' });
            return false;
          })
          .finally(function () { backfillRiotInFlight = null; });

        return backfillRiotInFlight;
      }

      function scheduleBackfillRiotPreview() {
        if (!backfillRiotInput) return;
        const riotId = normalizeBackfillRiotId(backfillRiotInput.value);
        clearTimeout(backfillRiotTimer);
        if (!riotId) {
          backfillRiotLastValue = '';
          setBackfillRiotPreview('idle');
          return;
        }
        if (!isValidBackfillRiotId(riotId)) {
          backfillRiotLastValue = '';
          setBackfillRiotPreview('error', { riot_id: riotId, message: 'Use format: Name#TAG' });
          return;
        }
        if (backfillRiotVerifiedOk && backfillRiotVerifiedValue === riotId) return;
        backfillRiotVerifiedOk = false;
        backfillRiotVerifiedValue = '';
        resetBackfillRiotConfirmation();
        backfillRiotTimer = setTimeout(function () { verifyBackfillRiotAccount(riotId); }, 450);
      }

      if (backfillRiotInput) {
        backfillRiotInput.addEventListener('input', scheduleBackfillRiotPreview);
        backfillRiotInput.addEventListener('blur', function () {
          backfillRiotInput.value = normalizeBackfillRiotId(backfillRiotInput.value);
          scheduleBackfillRiotPreview();
        });
      }

      async function saveBackfillRiotAccount(riotId) {
        riotId = normalizeBackfillRiotId(riotId || (backfillRiotInput ? backfillRiotInput.value : ''));
        if (!riotId) return;
        if (!backfillRiotVerifiedOk || backfillRiotVerifiedValue !== riotId) {
          setBackfillState('Please wait until the Riot account was found.', 'warning');
          return;
        }

        if (backfillRiotConfirm) {
          backfillRiotConfirm.disabled = true;
          backfillRiotConfirm.innerHTML = '<i class="fa-duotone fa-loader fa-spin"></i> Saving';
        }
        setBackfillState('Saving account to this order...', 'primary');

        try {
          const fd = new FormData();
          fd.append('action', 'admin_set_duo_account');
          fd.append('order_id', orderId);
          fd.append('riot_id', riotId);
          const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
          const json = await res.json();
          if (!json || !(json.success || json.ok)) throw new Error((json && json.message) || 'Could not save the account.');
          if (document.getElementById('lbMhBackfillSaveDuo')) document.getElementById('lbMhBackfillSaveDuo').checked = true;
          markBackfillRiotConfirmed(riotId);
          setBackfillState(json.message || 'Account saved. You can sync games now.', 'success');
          showAjaxToast(json, 'success', 'Account saved', json.message || 'Account saved to this order.');
          document.dispatchEvent(new CustomEvent('lbProgressSynced'));
        } catch (e) {
          if (backfillRiotConfirm) {
            backfillRiotConfirm.disabled = false;
            backfillRiotConfirm.classList.remove('is-confirmed');
            backfillRiotConfirm.innerHTML = '<i class="fa-solid fa-plus"></i> Save account';
          }
          setBackfillState((e && e.message) ? e.message : 'Could not save the account.', 'danger');
        }
      }

      if (backfillRiotConfirm) {
        backfillRiotConfirm.addEventListener('mousedown', function (e) { e.preventDefault(); });
        backfillRiotConfirm.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          saveBackfillRiotAccount(normalizeBackfillRiotId(backfillRiotInput ? backfillRiotInput.value : ''));
        });
      }

      function pad2(value) {
        return String(value).padStart(2, '0');
      }

      function formatPickerValue(date) {
        return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate()) + 'T' + pad2(date.getHours()) + ':' + pad2(date.getMinutes());
      }

      function parsePickerValue(value) {
        const match = String(value || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/);
        if (!match) return null;
        const year = parseInt(match[1], 10);
        const month = parseInt(match[2], 10);
        const day = parseInt(match[3], 10);
        const hour = parseInt(match[4], 10);
        const minute = parseInt(match[5], 10);
        if (!year || month < 1 || month > 12 || day < 1 || day > 31 || hour < 0 || hour > 23 || minute < 0 || minute > 59) return null;
        const date = new Date(year, month - 1, day, hour, minute, 0, 0);
        if (isNaN(date.getTime())) return null;
        return date;
      }

      function formatPickerLabel(value) {
        const date = parsePickerValue(value);
        if (!date) return 'Select Berlin date and time';
        return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
          + ', ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes())
          + ' · Berlin time';
      }

      function createDateTimePicker(key) {
        const wrap = document.querySelector('[data-lb-datetime="' + key + '"]');
        const trigger = document.querySelector('[data-lb-datetime-trigger="' + key + '"]');
        const popover = document.querySelector('[data-lb-datetime-popover="' + key + '"]');
        const input = document.getElementById(key === 'start' ? 'lbMhBackfillStart' : 'lbMhBackfillEnd');
        if (!wrap || !trigger || !popover || !input) return;

        const now = new Date();
        let selected = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), 0, 0);
        let view = new Date(selected.getFullYear(), selected.getMonth(), 1);
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

        function updateTrigger() {
          trigger.innerHTML = '<span' + (input.value ? '' : ' class="lb-datetime-placeholder"') + '>' + escHtml(formatPickerLabel(input.value)) + '</span><i class="fa-duotone fa-calendar-clock"></i>';
        }

        function setValue(date, updateHidden = true) {
          selected = new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), 0, 0);
          if (updateHidden) {
            input.value = formatPickerValue(selected);
            updateTrigger();
          }
        }

        function formatDateFieldValue(date) {
          return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
        }

        function formatTimeFieldValue(date) {
          return pad2(date.getHours()) + ':' + pad2(date.getMinutes());
        }

        function formatDateTextFieldValue(date) {
          return pad2(date.getDate()) + '.' + pad2(date.getMonth() + 1) + '.' + date.getFullYear();
        }

        function normalizeDateFieldValue(value) {
          const raw = String(value || '').trim();
          let match = raw.match(/^(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})$/);
          let day, month, year;

          if (match) {
            day = parseInt(match[1], 10);
            month = parseInt(match[2], 10);
            year = parseInt(match[3], 10);
          } else {
            match = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            if (!match) return null;
            year = parseInt(match[1], 10);
            month = parseInt(match[2], 10);
            day = parseInt(match[3], 10);
          }

          if (!year || month < 1 || month > 12 || day < 1 || day > 31) return null;

          const check = new Date(year, month - 1, day);
          if (check.getFullYear() !== year || check.getMonth() !== month - 1 || check.getDate() !== day) return null;

          return { year, month, day, normalized: pad2(day) + '.' + pad2(month) + '.' + year };
        }

        function parseDateFieldValue(value) {
          return normalizeDateFieldValue(value);
        }

        function normalizeTimeFieldValue(value) {
          let raw = String(value || '').trim().replace(/[^0-9:]/g, '');
          if (/^\d{1,2}$/.test(raw)) raw = raw + ':00';
          if (/^\d{3,4}$/.test(raw)) raw = raw.slice(0, -2) + ':' + raw.slice(-2);

          const match = raw.match(/^(\d{1,2}):(\d{1,2})$/);
          if (!match) return null;

          const hour = parseInt(match[1], 10);
          const minute = parseInt(match[2], 10);
          if (Number.isNaN(hour) || Number.isNaN(minute)) return null;
          if (hour < 0 || hour > 23 || minute < 0 || minute > 59) return null;

          return pad2(hour) + ':' + pad2(minute);
        }

        function parseTimeFieldValue(value) {
          const normalized = normalizeTimeFieldValue(value);
          if (!normalized) return null;
          const parts = normalized.split(':').map(Number);
          return { hour: parts[0], minute: parts[1], normalized };
        }

        function applyDateField(value, shouldRender = false) {
          const parts = parseDateFieldValue(value);
          if (!parts) return false;
          selected.setFullYear(parts.year, parts.month - 1, parts.day);
          view = new Date(parts.year, parts.month - 1, 1);
          setValue(selected);

          const dateField = popover.querySelector('[data-picker-date]');
          if (dateField) dateField.value = parts.normalized;

          if (shouldRender) {
            render();
            positionPopover();
          }
          return true;
        }

        function applyTimeField(value) {
          const parts = parseTimeFieldValue(value);
          if (!parts) return false;
          selected.setHours(parts.hour, parts.minute, 0, 0);
          setValue(selected);

          const timeField = popover.querySelector('[data-picker-time]');
          if (timeField) timeField.value = parts.normalized;

          return true;
        }

        function updateManualFields() {
          const dateField = popover.querySelector('[data-picker-date]');
          const timeField = popover.querySelector('[data-picker-time]');
          if (dateField) dateField.value = formatDateTextFieldValue(selected);
          if (timeField) timeField.value = formatTimeFieldValue(selected);
        }

        function positionPopover() {
          const rect = trigger.getBoundingClientRect();
          const margin = 12;
          const gap = 8;

          popover.style.maxHeight = 'calc(100vh - ' + (margin * 2) + 'px)';
          popover.style.overflowY = 'auto';

          const width = popover.offsetWidth || 318;
          const height = popover.offsetHeight || 320;
          const spaceBelow = window.innerHeight - rect.bottom - margin;
          const spaceAbove = rect.top - margin;

          let top = rect.bottom + gap;
          if (spaceBelow < height && spaceAbove > spaceBelow) {
            top = rect.top - height - gap;
          }

          top = Math.max(margin, Math.min(top, window.innerHeight - height - margin));
          let left = rect.left;
          left = Math.max(margin, Math.min(left, window.innerWidth - width - margin));

          popover.style.top = top + 'px';
          popover.style.left = left + 'px';
        }

        function openPicker() {
          document.querySelectorAll('.lb-datetime-wrap.is-open').forEach(function (el) {
            if (el !== wrap) el.classList.remove('is-open');
          });
          document.querySelectorAll('.lb-datetime-popover.is-open').forEach(function (el) {
            if (el !== popover) el.classList.remove('is-open');
          });

          const current = parsePickerValue(input.value);
          if (current) {
            selected = current;
            view = new Date(current.getFullYear(), current.getMonth(), 1);
          }

          if (popover.parentElement !== document.body) {
            document.body.appendChild(popover);
          }

          wrap.classList.add('is-open');
          popover.classList.add('is-open');
          render();
          positionPopover();
        }

        function closePicker() {
          wrap.classList.remove('is-open');
          popover.classList.remove('is-open');
        }

        function render() {
          const selectedDay = new Date(selected.getFullYear(), selected.getMonth(), selected.getDate()).getTime();
          const first = new Date(view.getFullYear(), view.getMonth(), 1);
          const firstWeekday = (first.getDay() + 6) % 7;
          const gridStart = new Date(first);
          gridStart.setDate(first.getDate() - firstWeekday);

          let days = '';
          for (let i = 0; i < 42; i++) {
            const d = new Date(gridStart);
            d.setDate(gridStart.getDate() + i);
            const dayKey = d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
            const isMuted = d.getMonth() !== view.getMonth();
            const isSelected = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime() === selectedDay;
            days += '<button type="button" class="lb-datetime-day' + (isMuted ? ' is-muted' : '') + (isSelected ? ' is-selected' : '') + '" data-picker-day="' + dayKey + '">' + d.getDate() + '</button>';
          }

          popover.innerHTML = ''
            + '<div class="lb-datetime-summary">'
            + '<div class="lb-datetime-summary__meta">'
            + '<span class="lb-datetime-summary__label">Selected</span>'
            + '<div class="lb-datetime-summary__value">' + escHtml(formatPickerLabel(formatPickerValue(selected))) + '</div>'
            + '</div>'
            + '<div class="lb-datetime-summary__icon"><i class="fa-duotone fa-calendar-clock"></i></div>'
            + '</div>'
            + '<div class="lb-datetime-head">'
            + '<button type="button" class="lb-datetime-nav" data-picker-prev aria-label="Previous month"><i class="fa-duotone fa-chevron-left"></i></button>'
            + '<div class="lb-datetime-month">' + monthNames[view.getMonth()] + ' ' + view.getFullYear() + '</div>'
            + '<button type="button" class="lb-datetime-nav" data-picker-next aria-label="Next month"><i class="fa-duotone fa-chevron-right"></i></button>'
            + '</div>'
            + '<div class="lb-datetime-weekdays">' + weekdays.map(function (d) { return '<span>' + d + '</span>'; }).join('') + '</div>'
            + '<div class="lb-datetime-days">' + days + '</div>'
            + '<div class="lb-datetime-manual">'
            + '<div class="lb-datetime-field">'
            + '<label class="lb-datetime-field-label">Date</label>'
            + '<input type="text" class="lb-datetime-input" data-picker-date inputmode="numeric" autocomplete="off" placeholder="DD.MM.YYYY" value="' + formatDateTextFieldValue(selected) + '">'
            + '</div>'
            + '<div class="lb-datetime-field">'
            + '<label class="lb-datetime-field-label">Time</label>'
            + '<input type="text" class="lb-datetime-input" data-picker-time inputmode="numeric" autocomplete="off" maxlength="5" placeholder="HH:MM" value="' + formatTimeFieldValue(selected) + '">'
            + '</div>'
            + '</div>'
            + '<div class="lb-datetime-timezone"><i class="fa-duotone fa-globe"></i><span>Timezone: Europe/Berlin (German time)</span></div>'
            + '<div class="lb-datetime-actions">'
            + '<button type="button" class="btn btn-sm btn-outline-secondary" data-picker-clear>Clear</button>'
            + '<button type="button" class="btn btn-sm btn-primary" data-picker-apply>Apply</button>'
            + '</div>';
        }

        trigger.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          if (wrap.classList.contains('is-open')) {
            closePicker();
          } else {
            openPicker();
          }
        });

        ['pointerdown', 'mousedown', 'mouseup', 'touchstart'].forEach(function (eventName) {
          popover.addEventListener(eventName, function (e) {
            e.stopPropagation();
          });
        });

        popover.addEventListener('click', function (e) {
          e.stopPropagation();

          const prev = e.target.closest('[data-picker-prev]');
          const next = e.target.closest('[data-picker-next]');
          const day = e.target.closest('[data-picker-day]');
          const clear = e.target.closest('[data-picker-clear]');
          const apply = e.target.closest('[data-picker-apply]');

          if (prev) {
            e.preventDefault();
            view.setMonth(view.getMonth() - 1);
            render();
            positionPopover();
            return;
          }

          if (next) {
            e.preventDefault();
            view.setMonth(view.getMonth() + 1);
            render();
            positionPopover();
            return;
          }

          if (day) {
            e.preventDefault();
            const parts = day.getAttribute('data-picker-day').split('-').map(Number);
            selected.setFullYear(parts[0], parts[1] - 1, parts[2]);
            view = new Date(parts[0], parts[1] - 1, 1);
            setValue(selected);
            render();
            positionPopover();
            return;
          }

          if (clear) {
            e.preventDefault();
            input.value = '';
            updateTrigger();
            closePicker();
            return;
          }

          if (apply) {
            e.preventDefault();
            const dateField = popover.querySelector('[data-picker-date]');
            const timeField = popover.querySelector('[data-picker-time]');
            if (dateField) {
              const dateValue = String(dateField.value || '').trim();
              if (dateValue === '' || !applyDateField(dateValue, false)) {
                dateField.focus();
                dateField.select();
                return;
              }
            }
            if (timeField) {
              const timeValue = String(timeField.value || '').trim();
              if (timeValue === '' || !applyTimeField(timeValue)) {
                timeField.focus();
                timeField.select();
                return;
              }
            }
            setValue(selected);
            closePicker();
          }
        });

        ['keydown', 'keyup', 'keypress', 'beforeinput', 'input', 'paste', 'cut', 'compositionstart', 'compositionupdate', 'compositionend'].forEach(function (eventName) {
          popover.addEventListener(eventName, function (e) {
            if (e.target.matches('[data-picker-time], [data-picker-date]')) {
              e.stopPropagation();
            }
          }, true);
        });

        popover.addEventListener('focusin', function (e) {
          if (e.target.matches('[data-picker-time], [data-picker-date]')) {
            e.stopPropagation();
            setTimeout(function () {
              try { e.target.select(); } catch (_) {}
            }, 0);
          }
        });

        popover.addEventListener('input', function (e) {
          if (e.target.matches('[data-picker-time]')) {
            // Fully manual HH:MM field. Do not auto-format, do not restore the old
            // value, and do not call setValue while the user is typing/deleting.
            let raw = String(e.target.value || '').replace(/[^0-9:]/g, '');
            if (raw.length > 5) raw = raw.slice(0, 5);
            e.target.value = raw;
            return;
          }
          if (e.target.matches('[data-picker-date]')) {
            // Fully manual DD.MM.YYYY field. Calendar day clicks still fill it, but
            // keyboard editing/deleting is left alone until Apply is pressed.
            let raw = String(e.target.value || '').replace(/[^0-9.\-/]/g, '');
            if (raw.length > 10) raw = raw.slice(0, 10);
            e.target.value = raw;
          }
        });

        popover.addEventListener('change', function (e) {
          // Do not validate/restore manual fields on change. The user must be able
          // to leave the field temporarily empty or half-written while editing.
          if (e.target.matches('[data-picker-time], [data-picker-date]')) {
            e.stopPropagation();
          }
        });

        popover.addEventListener('blur', function (e) {
          // Do not restore old values on blur. Validate only on Apply.
          if (e.target.matches('[data-picker-time], [data-picker-date]')) {
            e.stopPropagation();
          }
        }, true);

        window.addEventListener('resize', function () { if (wrap.classList.contains('is-open')) positionPopover(); });
        window.addEventListener('scroll', function () { if (wrap.classList.contains('is-open')) positionPopover(); }, true);

        updateTrigger();
      }


      function initBackfillRangeModal() {
        const openBtn = document.getElementById('lbMhBackfillRangeOpen');
        const label = document.getElementById('lbMhBackfillRangeLabel');
        const modal = document.getElementById('lbMhBackfillRangeModal');
        const closeBtn = document.getElementById('lbMhBackfillRangeClose');
        const cancelBtn = document.getElementById('lbMhRangeCancel');
        const clearBtn = document.getElementById('lbMhRangeClear');
        const applyBtn = document.getElementById('lbMhRangeApply');
        const errorBox = document.getElementById('lbMhRangeError');
        const startInput = document.getElementById('lbMhBackfillStart');
        const endInput = document.getElementById('lbMhBackfillEnd');
        const startDate = document.getElementById('lbMhRangeStartDate');
        const endDate = document.getElementById('lbMhRangeEndDate');
        const startTime = document.getElementById('lbMhRangeStartTime');
        const endTime = document.getElementById('lbMhRangeEndTime');
        if (!openBtn || !label || !modal || !startInput || !endInput || !startDate || !endDate || !startTime || !endTime) return;

        function todayDateValue() { const now = new Date(); return now.getFullYear() + '-' + pad2(now.getMonth() + 1) + '-' + pad2(now.getDate()); }
        function toIsoDate(d) { return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()); }
        function deDate(value) { const parts = String(value || '').split('-'); return parts.length === 3 ? (parts[2] + '.' + parts[1] + '.' + parts[0]) : ''; }
        function datePart(value) { const d = parsePickerValue(value); return d ? deDate(toIsoDate(d)) : ''; }
        function timePart(value) { const d = parsePickerValue(value); return d ? (pad2(d.getHours()) + ':' + pad2(d.getMinutes())) : ''; }
        function parseDateInput(value) {
          const raw = String(value || '').trim();
          let m = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
          if (m) return { y: +m[1], mo: +m[2], da: +m[3] };
          m = raw.match(/^(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})$/);
          if (m) return { y: +m[3], mo: +m[2], da: +m[1] };
          return null;
        }
        function normalizeDateTyping(input) {
          let raw = String(input.value || '').replace(/[^0-9.\-/]/g, '');
          if (raw.length > 10) raw = raw.slice(0, 10);
          input.value = raw;
        }
        function setDayRange(days) {
          const now = new Date();
          const end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
          const start = new Date(end);
          if (days === 'yesterday') { start.setDate(start.getDate() - 1); end.setDate(end.getDate() - 1); }
          else if (/^\d+$/.test(String(days))) { start.setDate(start.getDate() - (parseInt(days, 10) - 1)); }
          startDate.value = deDate(toIsoDate(start));
          endDate.value = deDate(toIsoDate(end));
        }
        function setError(message) { if (!errorBox) return; errorBox.hidden = !message; errorBox.textContent = message || ''; }

        function updateRangeLabel() {
          const s = parsePickerValue(startInput.value);
          const e = parsePickerValue(endInput.value);
          if (!s || !e) { label.className = 'lb-datetime-placeholder'; label.textContent = 'Select Berlin date range and time'; return; }
          label.className = '';
          const sd = s.getFullYear() + '-' + pad2(s.getMonth() + 1) + '-' + pad2(s.getDate());
          const ed = e.getFullYear() + '-' + pad2(e.getMonth() + 1) + '-' + pad2(e.getDate());
          if (sd === ed) label.textContent = deDate(sd) + ', ' + pad2(s.getHours()) + ':' + pad2(s.getMinutes()) + ' -> ' + pad2(e.getHours()) + ':' + pad2(e.getMinutes()) + ' · Berlin time';
          else label.textContent = deDate(sd) + ' ' + pad2(s.getHours()) + ':' + pad2(s.getMinutes()) + ' -> ' + deDate(ed) + ' ' + pad2(e.getHours()) + ':' + pad2(e.getMinutes()) + ' · Berlin time';
        }
        function fillFieldsFromHidden() { const today = deDate(todayDateValue()); startDate.value = datePart(startInput.value) || today; endDate.value = datePart(endInput.value) || startDate.value || today; startTime.value = timePart(startInput.value) || '00:00'; endTime.value = timePart(endInput.value) || '23:59'; setError(''); }
        function buildDate(value, time) { const partsObj = parseDateInput(value); if (!partsObj) return null; if (!/^\d{2}:\d{2}$/.test(String(time || ''))) return null; const t = String(time).split(':').map(Number); const d = new Date(partsObj.y, partsObj.mo - 1, partsObj.da, t[0], t[1], 0, 0); if (isNaN(d.getTime())) return null; if (d.getFullYear() !== partsObj.y || d.getMonth() !== partsObj.mo - 1 || d.getDate() !== partsObj.da) return null; return d; }
        function openModal() { fillFieldsFromHidden(); modal.hidden = false; setTimeout(function () { try { startDate.focus(); } catch (_) {} }, 0); }
        function closeModal() { modal.hidden = true; setError(''); }
        openBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); openModal(); });
        [closeBtn, cancelBtn].forEach(function (btn) { if (!btn) return; btn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); }); });
        clearBtn?.addEventListener('click', function (e) { e.preventDefault(); startInput.value = ''; endInput.value = ''; updateRangeLabel(); closeModal(); });
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal();
          const dayPreset = e.target.closest('[data-range-days]');
          if (dayPreset) { e.preventDefault(); setDayRange(dayPreset.getAttribute('data-range-days')); return; }
          const preset = e.target.closest('[data-range-time]');
          if (!preset) return;
          e.preventDefault();
          const mode = preset.getAttribute('data-range-time');
          if (mode === 'full') { startTime.value = '00:00'; endTime.value = '23:59'; }
          else if (mode === 'evening') { startTime.value = '18:00'; endTime.value = '23:59'; }
          else if (mode === 'now') { const now = new Date(); endDate.value = deDate(todayDateValue()); endTime.value = pad2(now.getHours()) + ':' + pad2(now.getMinutes()); }
        });
        [startDate, endDate].forEach(function (input) { input.addEventListener('input', function () { normalizeDateTyping(input); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
        applyBtn?.addEventListener('click', function (e) { e.preventDefault(); const s = buildDate(startDate.value, startTime.value); const end = buildDate(endDate.value, endTime.value); if (!s || !end) { setError('Please select valid dates and times.'); return; } if (end.getTime() < s.getTime()) { setError('The end date/time must be after the start date/time.'); return; } startInput.value = formatPickerValue(s); endInput.value = formatPickerValue(end); updateRangeLabel(); closeModal(); });
        updateRangeLabel();
      }

      function initBackfillModeDropdown() {
        const root = document.querySelector('[data-lb-mode-select]');
        if (!root) return;
        const select = root.querySelector('#lbMhBackfillMode');
        const trigger = root.querySelector('[data-lb-mode-trigger]');
        const label = root.querySelector('[data-lb-mode-label]');
        const options = Array.from(root.querySelectorAll('[data-lb-mode-option]'));
        if (!select || !trigger || !label || !options.length) return;

        function closeMode() {
          root.classList.remove('is-open');
          trigger.setAttribute('aria-expanded', 'false');
        }

        function setMode(value, notify) {
          const normalized = value === 'solo' ? 'solo' : 'duo';
          select.value = normalized;
          label.textContent = normalized === 'solo' ? 'Solo' : 'Duo';
          options.forEach(function (option) {
            const active = option.getAttribute('data-lb-mode-option') === normalized;
            option.classList.toggle('is-selected', active);
            option.setAttribute('aria-selected', active ? 'true' : 'false');
          });
          if (notify) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }

        trigger.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          const nextOpen = !root.classList.contains('is-open');
          document.querySelectorAll('.lb-mh-mode-select.is-open').forEach(function (el) {
            if (el !== root) el.classList.remove('is-open');
          });
          root.classList.toggle('is-open', nextOpen);
          trigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        });

        options.forEach(function (option) {
          option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setMode(option.getAttribute('data-lb-mode-option'), true);
            closeMode();
          });
        });

        document.addEventListener('click', function (e) {
          if (!root.contains(e.target)) closeMode();
        });

        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') closeMode();
        });

        setMode(select.value || 'duo', false);
      }

      function confirmHideGame() {
        return new Promise(function (resolve) {
          const existing = document.querySelector('.lb-confirm-backdrop');
          if (existing) existing.remove();
          const backdrop = document.createElement('div');
          backdrop.className = 'lb-confirm-backdrop';
          backdrop.innerHTML = ''
            + '<div class="lb-confirm-card" role="dialog" aria-modal="true" aria-labelledby="lbConfirmTitle">'
            + '<h3 class="lb-confirm-title" id="lbConfirmTitle">Hide game?</h3>'
            + '<p class="lb-confirm-message">This game will be hidden from match history, booster profile, performance, and leaderboard. It will remain stored in the database.</p>'
            + '<div class="lb-confirm-actions">'
            + '<button type="button" class="btn btn-sm btn-outline-secondary" data-confirm-cancel>Cancel</button>'
            + '<button type="button" class="btn btn-sm btn-danger" data-confirm-ok>Hide game</button>'
            + '</div>'
            + '</div>';
          document.body.appendChild(backdrop);
          function close(value) { backdrop.remove(); resolve(value); }
          backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop || e.target.closest('[data-confirm-cancel]')) close(false);
            if (e.target.closest('[data-confirm-ok]')) close(true);
          });
        });
      }

      createDateTimePicker('start');
      createDateTimePicker('end');
      initBackfillRangeModal();
      initBackfillModeDropdown();
      document.addEventListener('click', function (e) {
        const target = e.target;
        if (!target || typeof target.closest !== 'function') return;
        if (!target.closest('.lb-datetime-wrap') && !target.closest('.lb-datetime-popover')) {
          document.querySelectorAll('.lb-datetime-wrap.is-open').forEach(function (el) { el.classList.remove('is-open'); });
          document.querySelectorAll('.lb-datetime-popover.is-open').forEach(function (el) { el.classList.remove('is-open'); });
        }
      });

      function renderRows(rows) {
        if (!rows || rows.length === 0) {
          body.innerHTML = '<div class="lb-mh-placeholder">No matches tracked yet.</div>';
          return;
        }

        const html = rows.map(function (m) {
          const durationSeconds = parseInt(m.duration, 10) || 0;
          const resultText = (m.result || '').toString().trim().toLowerCase();
          const isRemake = parseInt(m.is_remake, 10) === 1
            || resultText === 'remake'
            || parseInt(m.game_ended_in_early_surrender, 10) === 1
            || (durationSeconds > 0 && durationSeconds < 300);
          const won    = !isRemake && (parseInt(m.won, 10) === 1 || parseInt(m.is_win, 10) === 1);
          const rowCls = isRemake ? 'lb-mh-row--remake' : (won ? 'lb-mh-row--win' : 'lb-mh-row--loss');
          const badge  = isRemake
            ? '<span class="lb-mh-badge lb-mh-badge--remake"><i class="fa-solid fa-rotate-left fa-xs"></i> Remake</span>'
            : (won
              ? '<span class="lb-mh-badge lb-mh-badge--win"><i class="fa-solid fa-trophy fa-xs"></i> Win</span>'
              : '<span class="lb-mh-badge lb-mh-badge--loss"><i class="fa-solid fa-skull fa-xs"></i> Loss</span>');

          const champ    = (m.champion || '').toString().trim();
          const champSafe = escHtml(champ);
          const champImg = champ
            ? `<img class="lb-mh-champ-img" src="${champUrl}/${encodeURIComponent(champ)}.png" alt="${champSafe}" loading="lazy" onerror="this.style.visibility='hidden'">`
            : `<span class="lb-mh-champ-img"></span>`;
          const champCol = `<div class="lb-mh-champ-col">${champImg}<div class="lb-mh-champ-info"><div class="lb-mh-champ-name">${champSafe || '—'}</div><div class="lb-mh-queue">${queueName(m.queue_id)}</div></div></div>`;

          const boosterName = (m.booster_name || '').toString().trim() || (m.booster_id ? ('#' + m.booster_id) : 'Unassigned');
          const boosterIcon = (
            m.booster_icon ||
            m.booster_avatar ||
            m.booster_icon_url ||
            m.booster_image ||
            m.booster_img ||
            (m.booster && m.booster.icon) ||
            ''
          ).toString().trim();
          const boosterIconHtml = boosterIcon
            ? `<span class="lb-mh-booster-ico"><img class="lb-mh-booster-img" src="${escHtml(boosterIcon)}" alt="${escHtml(boosterName)}" loading="lazy" onerror="this.parentNode.innerHTML='<i class=\'fa-duotone fa-user-shield\'></i>'"></span>`
            : `<span class="lb-mh-booster-ico"><i class="fa-duotone fa-user-shield"></i></span>`;
          const boosterCol = `<div class="lb-mh-booster-col">${boosterIconHtml}<span class="lb-mh-booster-info"><span class="lb-mh-booster-name">${escHtml(boosterName)}</span><span class="lb-mh-booster-sub">Booster</span></span></div>`;

          const playModeRaw = (m.play_mode || m.match_type || '').toString().trim().toLowerCase();
          const isDuoMode = playModeRaw === 'duo';
          const modeText = isDuoMode ? 'Duo' : 'Solo';
          const statSubject = (m.stat_subject || '').toString().trim().toLowerCase();
          const modeSub = statSubject === 'booster' ? 'Booster stats' : 'Client stats';
          const modeIcon = isDuoMode ? 'fa-user-group' : 'fa-user';
          const modeCol = `<div class="lb-mh-mode-col"><span class="lb-mh-mode-pill lb-mh-mode-pill--${isDuoMode ? 'duo' : 'solo'}"><i class="fa-duotone ${modeIcon} fa-xs"></i>${modeText}</span><span class="lb-mh-mode-sub">${modeSub}</span></div>`;

          const pos      = (m.position || '').toString().trim().toUpperCase();
          const roleFile_ = roleFile(pos);
          const roleLabel = pos.charAt(0) + pos.slice(1).toLowerCase();
          const roleCol = roleFile_
            ? `<div class="lb-mh-role-col"><img class="lb-mh-role-img" src="${roleUrl}${roleFile_}.png" alt="${roleLabel}" onerror="this.style.visibility='hidden'"><span>${roleLabel}</span></div>`
            : `<div class="lb-mh-role-col"><span style="opacity:.35">—</span></div>`;

          const k       = parseInt(m.kills,   10) || 0;
          const d       = parseInt(m.deaths,  10) || 0;
          const a       = parseInt(m.assists, 10) || 0;
          const kdaRatio = d === 0 ? 'Perfect' : ((k + a) / d).toFixed(2) + ' KDA';
          const kdaCol  = `<div class="lb-mh-kda-col"><span class="lb-mh-kda">${k}<span class="lb-mh-kda-sep">/</span>${d}<span class="lb-mh-kda-sep">/</span>${a}</span><span class="lb-mh-kda-ratio">${kdaRatio}</span></div>`;

          const durCol  = `<div class="lb-mh-dur-col"><span class="lb-mh-dur">${fmtDuration(durationSeconds)}</span><span class="lb-mh-sub">Duration</span></div>`;

          // rank_snapshot: use stored value when available;
          // fall back to order_start_tier + order_start_division for old rows without snapshot
          const _mhTierIds  = {IRON:1,BRONZE:2,SILVER:3,GOLD:4,PLATINUM:5,EMERALD:6,DIAMOND:7,MASTER:8,GRANDMASTER:9,CHALLENGER:10};
          const _mhTierNames = ['','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
          let rankSnap = (m.rank_snapshot || '').toString().trim();
          // Fallback: build rank label from order_start_tier / order_start_division when no snapshot stored
          if (!rankSnap && m.order_start_tier) {
            const fbName = _mhTierNames[parseInt(m.order_start_tier, 10)] || '';
            const fbDiv  = (m.order_start_division || '').toString().trim();
            if (fbName) rankSnap = fbName + (fbDiv ? ' ' + fbDiv : '');
          }
          function fullRankSnapshotLabel(snapshot) {
            const raw = (snapshot || '').toString().trim();
            if (!raw) return '';

            const cleaned = raw
              .replace(/\s+\u00b7\s+/g, ' ')
              .replace(/\s+/g, ' ')
              .trim();

            const m = cleaned.match(/^(IRON|BRONZE|SILVER|GOLD|PLATINUM|EMERALD|DIAMOND|MASTER|GRANDMASTER|CHALLENGER)\s*(IV|III|II|I)?\s*(?:(\d+)\s*LP)?/i);
            if (!m) return cleaned;

            const tierMap = {
              IRON: 'Iron',
              BRONZE: 'Bronze',
              SILVER: 'Silver',
              GOLD: 'Gold',
              PLATINUM: 'Platinum',
              EMERALD: 'Emerald',
              DIAMOND: 'Diamond',
              MASTER: 'Master',
              GRANDMASTER: 'Grandmaster',
              CHALLENGER: 'Challenger'
            };

            const tier = tierMap[m[1].toUpperCase()] || m[1];
            const div = m[2] ? (' ' + m[2].toUpperCase()) : '';
            const lp = m[3] ? (' ' + parseInt(m[3], 10) + ' LP') : '';

            return (tier + div + lp).trim();
          }


          let rankCol;
          if (rankSnap) {
            const rankParts  = rankSnap.split(' \u00b7 ');
            const rankMain   = rankParts[0] || '';
            const rankLp     = rankParts[1] || '';
            const rankDisplay = fullRankSnapshotLabel(rankSnap);
            const tierWord   = rankMain.split(' ')[0].toUpperCase().trim();
            const tierIconId = _mhTierIds[tierWord] ?? 0;
            const rankImgUrl = asset_url + '/core/main/img/lol/ranks/max/' + tierIconId + '.png';
            rankCol = '<div class="lb-mh-rank-col lb-mh-rank-col--snap">'
              + '<div class="lb-mh-rank-inner">'
              + '<img class="lb-mh-rank-ico" src="' + rankImgUrl + '" alt="' + escHtml(tierWord) + '" loading="lazy" onerror="this.style.visibility=\'hidden\'">'
              + '<div class="lb-mh-rank-text">'
              + '<span class="lb-mh-rank-name" title="' + escHtml(rankSnap) + '">' + escHtml(rankDisplay || rankMain) + '</span>'
              + '</div>'
              + '</div>'
              + '</div>';
          } else {
            rankCol = '<div class="lb-mh-rank-col"><span class="lb-mh-rank-name" style="opacity:.28">—</span></div>';
          }

          const [datePart, timePart] = fmtDate(m.played_at);
          const dateCol = `<div class="lb-mh-date-col"><span class="lb-mh-date">${datePart}</span><span class="lb-mh-time">${timePart}</span></div>`;

          const matchId = parseInt(m.id, 10) || 0;
          const actionsCol = `<div class="lb-mh-actions-col"><button type="button" class="lb-mh-hide-btn" data-mh-hide-id="${matchId}" title="Hide game from all stats"><i class="fa-duotone fa-eye-slash"></i> Hide</button></div>`;

          return `<div class="lb-mh-row ${rowCls}" data-match-row-id="${matchId}"><div class="lb-mh-result">${badge}</div>${champCol}${boosterCol}${modeCol}${roleCol}${kdaCol}${durCol}${rankCol}${dateCol}${actionsCol}</div>`;
        }).join('');

        body.innerHTML = html;
      }

      async function hideMatch(matchId, btn) {
        matchId = parseInt(matchId, 10) || 0;
        if (!matchId || loading) return;
        const confirmed = await confirmHideGame();
        if (!confirmed) return;

        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<i class="fa-duotone fa-loader fa-spin"></i> Hiding';
        }

        try {
          const fd = new FormData();
          fd.append('action', 'admin_delete_order_match');
          fd.append('match_id', matchId);

          const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
          const json = await res.json();
          if (!json.ok) throw new Error(json.message || 'Could not hide game.');

          const row = body.querySelector('[data-match-row-id="' + matchId + '"]');
          if (row) {
            row.style.opacity = '0.25';
            row.style.pointerEvents = 'none';
          }
          showAjaxToast(json, 'success', 'Hidden', json.message || 'Game hidden successfully.');
          document.dispatchEvent(new CustomEvent('lbProgressSynced'));
          loadPage(currentPage);
        } catch (e) {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
          }
          const message = e && e.message ? e.message : 'Could not hide game.';
          if (typeof window.create_toast === 'function') create_toast('danger', 'Error', message);
        }
      }

      async function runBackfillSync() {
        if (!backfillSubmit || loading) return;
        const riotId = normalizeBackfillRiotId(document.getElementById('lbMhBackfillRiotId')?.value || '');
        const playMode = (document.getElementById('lbMhBackfillMode')?.value || 'duo').trim();
        const startAt = (document.getElementById('lbMhBackfillStart')?.value || '').trim();
        const endAt = (document.getElementById('lbMhBackfillEnd')?.value || '').trim();
        const saveDuo = document.getElementById('lbMhBackfillSaveDuo')?.checked ? '1' : '0';

        if (!riotId) {
          setBackfillState('Please enter a Riot ID.', 'warning');
          if (backfillRiotInput) backfillRiotInput.focus();
          return;
        }

        if (riotId) {
          if (backfillRiotInput) backfillRiotInput.value = riotId;
          if (!isValidBackfillRiotId(riotId)) {
            setBackfillState('Please enter the Riot ID in this format: BoosterName#EUW.', 'warning');
            setBackfillRiotPreview('error', { riot_id: riotId, message: 'Use format: Name#TAG' });
            if (backfillRiotInput) backfillRiotInput.focus();
            return;
          }

          setBackfillState('Checking Riot account before sync...', 'primary');
          const verified = await verifyBackfillRiotAccount(riotId);
          if (!verified || !backfillRiotVerifiedOk || backfillRiotVerifiedValue !== riotId) {
            setBackfillState('Riot ID not found. Please check it and the selected server.', 'danger');
            if (backfillRiotInput) backfillRiotInput.focus();
            return;
          }

          // Account was verified. Saving is optional; the sync can still run immediately.
        }

        backfillSubmit.disabled = true;
        const label = backfillSubmit.querySelector('.lb-backfill-label');
        const loadingLabel = backfillSubmit.querySelector('.lb-backfill-loading');
        if (label) label.style.display = 'none';
        if (loadingLabel) loadingLabel.style.display = 'inline-flex';
        setBackfillState('Backfill sync is running…', 'primary');

        try {
          const fd = new FormData();
          fd.append('action', 'admin_backfill_order_matches');
          fd.append('order_id', orderId);
          fd.append('riot_id', riotId);
          fd.append('puuid', '');
          fd.append('play_mode', playMode);
          fd.append('start_at', startAt ? startAt.replace('T', ' ') + ':00' : '');
          fd.append('end_at', endAt ? endAt.replace('T', ' ') + ':00' : '');
          fd.append('save_duo_account', saveDuo);

          const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
          const json = await res.json();
          if (!json.ok) throw new Error(json.message || 'Backfill sync failed.');

          const checked = parseInt(json.matched_from_riot ?? 0, 10) || 0;
          const added = parseInt(json.inserted_visible ?? 0, 10) || 0;
          const hidden = parseInt(json.skipped_hidden ?? 0, 10) || 0;
          setBackfillState('Done: checked ' + checked + ' Riot matches, added +' + added + ' visible games' + (hidden ? ', skipped ' + hidden + ' hidden games' : '') + '.', 'success');
          showAjaxToast(json, 'success', 'Backfill Completed', json.message || 'Backfill sync completed.');
          document.dispatchEvent(new CustomEvent('lbProgressSynced'));
          loadPage(1);
        } catch (e) {
          const message = e && e.message ? e.message : 'Backfill sync failed.';
          setBackfillState(message, 'danger');
          if (typeof window.create_toast === 'function') create_toast('danger', 'Backfill failed', message);
        } finally {
          backfillSubmit.disabled = false;
          const label = backfillSubmit.querySelector('.lb-backfill-label');
          const loadingLabel = backfillSubmit.querySelector('.lb-backfill-loading');
          if (label) label.style.display = 'inline-flex';
          if (loadingLabel) loadingLabel.style.display = 'none';
        }
      }

      async function loadPage(page) {
        if (loading) return;
        loading = true;
        body.style.opacity = '0.25';
        body.style.pointerEvents = 'none';
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;

        try {
          const fd = new FormData();
          fd.append('action', 'get_order_matches');
          fd.append('order_id', orderId);
          fd.append('page', page);
          fd.append('per_page', perPage);

          const res  = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
          const json = await res.json();

          if (!json.ok) throw new Error(json.message || 'Failed to load matches.');

          const meta = json.matches;
          currentPage = meta.page;

          renderRows(meta.rows);
          body.style.opacity = '';
          body.style.pointerEvents = '';

          if (totalBadge) {
            totalBadge.textContent = meta.total + (meta.total === 1 ? ' match' : ' matches');
            totalBadge.style.display = 'inline-flex';
          }
          if (countBadge) {
            countBadge.textContent = meta.total;
            countBadge.style.display = meta.total > 0 ? 'inline-flex' : 'none';
          }

          if (meta.total > perPage) {
            pager.style.display = 'flex';
            pagerInfo.textContent = 'Page ' + meta.page + ' of ' + meta.pages;
            prevBtn.disabled = (meta.page <= 1);
            nextBtn.disabled = (meta.page >= meta.pages);
          } else {
            pager.style.display = 'none';
          }
        } catch (e) {
          body.style.opacity = '';
          body.style.pointerEvents = '';
          body.innerHTML = '<div class="lb-mh-placeholder">Failed to load matches. Please try again.</div>';
        } finally {
          loading = false;
        }
      }

      body.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-mh-hide-id]');
        if (!btn) return;
        hideMatch(btn.getAttribute('data-mh-hide-id'), btn);
      });

      if (backfillToggle && backfillForm) {
        backfillToggle.addEventListener('click', function () {
          backfillForm.classList.toggle('is-open');
        });
      }

      if (backfillSubmit) {
        backfillSubmit.addEventListener('click', runBackfillSync);
      }

      if (prevBtn) prevBtn.addEventListener('click', function () { loadPage(currentPage - 1); });
      if (nextBtn) nextBtn.addEventListener('click', function () { loadPage(currentPage + 1); });

      // Load (or reload) when modal starts opening so data is ready when animation finishes.
      if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () { loadPage(1); });
      }

      // Refresh if modal is already open when a Riot sync completes.
      document.addEventListener('lbProgressSynced', function () {
        if (modalEl && modalEl.classList.contains('show')) {
          loadPage(1);
        }
      });
    })();

    // Bind once
    if (!document.body.dataset.lbAjaxFormsBound) {
      document.body.dataset.lbAjaxFormsBound = "1";

      $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submit = $form.find('button[type="submit"], input[type="submit"]').first();

        // Basic loading state (compatible with your indicator-label / indicator-progress markup)
        if ($submit.length) {
          $submit.prop('disabled', true);
          const $label = $submit.find('.indicator-label');
          const $prog = $submit.find('.indicator-progress');
          if ($label.length && $prog.length) {
            $label.hide();
            $prog.show();
          }
        }

        $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          success: function (res) {
            lbHandleAjaxResponse(res, $form);
          },
          error: function () {
            if (typeof window.create_toast === 'function') {
              create_toast('danger', 'Error', 'Something went wrong. Please try again.');
            }
          },
          complete: function () {
            if ($submit.length) {
              $submit.prop('disabled', false);
              const $label = $submit.find('.indicator-label');
              const $prog = $submit.find('.indicator-progress');
              if ($label.length && $prog.length) {
                $prog.hide();
                $label.show();
              }
            }
          }
        });
      });
    }
  });
</script>


<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/js/star-rating.min.js"
    type="text/javascript"></script>

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme JS files as mentioned below (and change the theme property of the plugin) -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-fas/theme.js"></script>
<script>
      // Chat (premium grouped) — identical to client dashboard
  let msg_none = false;
  let chat_json = {};
  
  let chat_signature = '';
  let isLoadingMessages = false;
let order_id = <?= (int) $data['id'] ?>;
  let order_status = "<?= $data['status'] ?>";
  const lbIsRanked5sOrder = <?= ((int)($data['form_id'] ?? 0) === 29 || (string)($data['type'] ?? '') === 'ranked-5s') ? 'true' : 'false' ?>;
  let user_type = "admin";
  let user_id = <?= (int) ADMIN_ID ?>;

  const base_data = { order_id: order_id };

  var chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
  function message_sound() { chat_notif.volume = 0.6; chat_notif.play(); }

  function decodeHtmlEntities(str) {
    var txt = document.createElement("textarea");
    txt.innerHTML = str ?? '';
    return txt.value.replace(/\n/g, "<br>");
  }



  function escapeSystemHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatSystemMessageContent(content) {
    const raw = String(content ?? '');
    const plain = raw.replace(/<br\s*\/?>(\s*)/gi, '\n').trim();
    const prefix = 'Add-on payment received:';
    const idx = plain.indexOf(prefix);

    if (idx === -1) return raw;

    const before = plain.substring(0, idx).trim();
    const jsonPart = plain.substring(idx + prefix.length).trim();

    if (!jsonPart || jsonPart.charAt(0) !== '{') return raw;

    try {
      const data = JSON.parse(jsonPart);

      if (data && data.type === 'lp_correction') {
        const lpGain = String(data.lp_gain || '').replace(/\s+V\s+/i, ' / ').trim();
        const currentLp = String(data.current_lp || '0').trim();
        const icon = before.includes('✅') ? '✅ ' : '';

        return icon + 'Add-on payment received: LP Correction ('
          + escapeSystemHtml(lpGain || 'LP Gain')
          + ', Current LP: '
          + escapeSystemHtml(currentLp || '0')
          + ')';
      }
    } catch (e) {}

    return raw;
  }

  function formatExactTime(ts) {
    const m = moment.unix(parseInt(ts, 10) || 0);
    return m && m.isValid() ? m.format("DD.MM.YYYY HH:mm") : "";
  }

  function getRoleBadge(sender) {
    if (sender === 'admin') return { cls: 'lb-badge--admin', label: 'Admin' };
    if (sender === 'booster') return { cls: 'lb-badge--booster', label: 'Booster' };
    if (sender === 'system') return { cls: 'lb-badge--system', label: 'System' };
    return { cls: 'lb-badge--customer', label: 'Customer' };
  }

  function getFallbackAvatar(sender) {
    if (sender === 'admin') return '<?= ICON_URL ?>/03ce541a1f4bf8b06c924439ffcc8173.png';
    if (sender === 'booster') return '<?= ICON_URL ?>/25d1ea33c481dbacd2f2c294408d38cd.png';
    return '<?= ICON_URL ?>/8515d2c8c74a3f9bae054026f6549d91.png';
  }


  function escapeAttr(str){
    try { return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    catch(e){ return ''; }
  }

  function renderTicks(msg_data){
    const seen = (msg_data.seen == 1 || msg_data.seen === "1" || msg_data.seen === true);
    const delivered = seen || (msg_data.notify == 1 || msg_data.notify === "1" || msg_data.notify === true);

    if (seen) {
      const title = 'Read' + (msg_data.seen_at ? (' • ' + formatExactTime(msg_data.seen_at)) : '');
      return ` <span class="lb-msg__ticks text-primary" title="${escapeAttr(title)}"><i class="fa-solid fa-check-double"></i></span>`;
    }
    if (delivered) {
      return ` <span class="lb-msg__ticks text-muted" title="Delivered"><i class="fa-solid fa-check-double"></i></span>`;
    }
    return ` <span class="lb-msg__ticks text-muted" title="Sent"><i class="fa-solid fa-check"></i></span>`;
  }

  function load_message(message_id, msg_data, isGrouped) {
    const exactTime = formatExactTime(msg_data.time);

    if (msg_data.sender === 'system') {
      const content = formatSystemMessageContent(decodeHtmlEntities(msg_data.content));
      return `
      <div class="lb-syswrap">
        <div class="lb-sys">${content}</div>
        <div class="lb-sys-time">${exactTime}</div>
      </div>
    `;
    }

    const content = decodeHtmlEntities(msg_data.content);

    const isMe = (msg_data.sender === user_type && String(msg_data.sender_id) === String(user_id));
    const alignClass = isMe ? 'lb-msg--end' : 'lb-msg--start';
    const headClass = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';

    const badge = getRoleBadge(msg_data.sender);
    const avatar = (msg_data.sender_icon && ('' + msg_data.sender_icon).length)
      ? msg_data.sender_icon
      : getFallbackAvatar(msg_data.sender);

    const name = isMe ? 'You' : (msg_data.sender_name || 'Unknown');

    let html = `<div class="lb-msg ${alignClass}">`;

    if (!isGrouped) {
      html += `
      <div class="${headClass}">
        <img class="lb-msg__avatar" src="${avatar}" alt="avatar">
        <div class="lb-msg__meta">
          <div class="lb-msg__toprow">
            <div class="lb-msg__name">
              ${name}
              <span class="lb-badge ${badge.cls}">${badge.label}</span>
            </div></div>
        </div>
      </div>
    `;
    }

    const isDeleted = (msg_data.type === 'deleted' || msg_data.deleted == 1);
    const safeContent = isDeleted ? 'Message deleted.' : content;
    const bubbleCls = isDeleted ? 'lb-msg__bubble lb-msg__bubble--deleted' : 'lb-msg__bubble';

    html += `<div class="${bubbleCls}" data-msg-id="${message_id}">${safeContent}`;
    if (!isDeleted && msg_data.sender !== 'system') {
      html += `<button type="button" class="lb-msg__del" data-msg-id="${message_id}" title="Delete message"><i class="fa-duotone fa-trash"></i></button>`;
    }
    html += `</div>`;
    html += `<div class="lb-msg__stamp">${exactTime}${isMe ? renderTicks(msg_data) : ""}</div>`;
    html += `</div>`;
    return html;
  }

  function update_scroll() {
    const el = document.getElementById('chat_messages');
    if (!el) return;
    el.scrollTop = el.scrollHeight;
  }

  function update_message_notif(message_id) {
    fetch_api('update_chat_notify', Object.assign({}, base_data, { id: message_id })).done(function () {
  try{document.body.classList.add('admin-order-view');}catch(e){}
 });
  }

    function buildChatSignature(chat_list){
    try{
      const keys = Object.keys(chat_list || {});
      return keys.map(k => {
        const v = chat_list[k] || {};
        // include content + flags so edits/deletes also trigger refresh
        return [
          k,
          v.sender ?? '',
          v.sender_id ?? '',
          v.time ?? '',
          v.type ?? '',
          v.deleted ?? '',
          v.deleted_at ?? '',
          v.deleted_by ?? '',
          v.edited ?? '',
          v.edited_at ?? '',
          v.content ?? ''
                  , v.notify ?? ''
          , v.seen ?? ''
          , v.seen_at ?? ''
        ].join('~');
      }).join('||');
    } catch(e){
      return String(Date.now());
    }
  }

  

  // Mark messages as READ only on explicit user click AND only when tab is active/visible
  function getLastUnseenIncomingId(chat_list) {
    try {
      const keys = Object.keys(chat_list || {});
      for (let i = keys.length - 1; i >= 0; i--) {
        const k = keys[i];
        const m = chat_list[k];
        if (!m) continue;
        if (m.sender && m.sender !== user_type && m.sender !== 'system' && (m.seen == 0 || m.seen === "0" || !m.seen)) {
          return k;
        }
      }
    } catch (e) {}
    return null;
  }

  function mark_chat_read() {
    // must be visible + focused (other browser tab => no read)
    if (document.visibilityState !== 'visible') return;
    if (!document.hasFocus || !document.hasFocus()) return;

    const lastIncomingId = getLastUnseenIncomingId(chat_json);
    if (!lastIncomingId) return;

    fetch_api('update_chat_seen', Object.assign({}, base_data, { id: lastIncomingId })).done(function () { });
  }

function load_messages() {
    if (isLoadingMessages) return;
    isLoadingMessages = true;
    fetch_api('load_chat', Object.assign({}, base_data)).done(function (response) {
      isLoadingMessages = false;
      // A silent return here left the chat box completely blank — not even the
      // "No messages found" state — whenever the server answered with anything
      // that was not clean JSON (a PHP warning or fatal ahead of the payload).
      // Surface it instead, and keep the raw response for diagnosis.
      try { response = JSON.parse(response); } catch (e) {
        console.error('[order chat] load_chat returned non-JSON:', response);
        $('#chat_messages').html('<div class="text-center"><h5 class="mt-5">Chat could not be loaded.<br><br>Please reload the page.</h5></div>');
        return;
      }
      const chat_list = response.messages || {};
      const msg_count = Object.keys(chat_list).length;

      if (msg_count > 0) {
        msg_none = false;
        const sig = buildChatSignature(chat_list);

        // Re-render whenever anything changed (new messages, edits, deletes, seen flags, etc.)
        if (sig !== chat_signature) {
          chat_signature = sig;
          chat_json = chat_list;

          let chat_html = '';
          let last_sender = "";
          let last_sender_id = 0;

          $.each(chat_list, function (key, val) {
            const isGrouped = (val.sender === last_sender && String(val.sender_id) === String(last_sender_id));
            chat_html += load_message(key, val, isGrouped);

            last_sender = val.sender;
            last_sender_id = val.sender_id;
          });

          $('#chat_messages').html(chat_html);
          update_scroll();
        }

        const last_message_id = Object.keys(chat_list)[Object.keys(chat_list).length - 1];
        const last_message = chat_list[last_message_id];

        if (last_message && last_message.sender == user_type && String(last_message.sender_id) === String(user_id)) {
          let message_read = '';
          if (last_message.seen == 1) {
            message_read = '<span class="text-muted fs-7 mb-1"><i class="fa-solid fa-check-double"></i> Read' + (last_message.seen_at ? (' • ' + formatExactTime(last_message.seen_at)) : '') + '</span>';
}
          let read_html = '<div class="d-flex justify-content-end mt-n1 mb-2 pe-1" id="message-read-status">' + message_read + '</div>';
          if ($("#message-read-status").length == 0) {
            $('#chat_messages').append(read_html);
            update_scroll();
          } else {
            $('#message-read-status').html(message_read);
          }
        } else if (last_message && last_message.notify == 0 && last_message.seen == 0) {
          update_message_notif(last_message_id);
          if (document.visibilityState === 'visible') { message_sound(); }
        }

      } else {
        if (msg_none == false) {
          $('#chat_messages').html('<div class="text-center"><h5 class="mt-5">No messages found.<br><br>Send one to get started!</h5></div>');
          msg_none = true;
        }
        chat_json = {};
        chat_signature = '';
      }
    }).fail(function () { isLoadingMessages = false; });
  

  }

  // ---- Chat image attach (file + paste) ----
  (function initChatImageAttach(){
    const form = document.getElementById('lbChatForm');
    if (!form) return;

    const msgInput = document.getElementById('lbChatMessageInput');
    const fileInput = document.getElementById('lbChatFile');
    const attachBtn = document.getElementById('lbChatAttachBtn');
    const preview = document.getElementById('lbChatPreview');
    const previewImg = document.getElementById('lbChatPreviewImg');
    const previewName = document.getElementById('lbChatPreviewName');
    const removeBtn = document.getElementById('lbChatRemoveBtn');
    const errBox = document.getElementById('lbChatError');
    const sendBtn = document.getElementById('lbChatSendBtn');

    let previewUrl = null;

    function setError(msg){
      if (!errBox) return;
      if (!msg){ errBox.classList.add('d-none'); errBox.textContent=''; return; }
      errBox.textContent = msg;
      errBox.classList.remove('d-none');
    }

    function clearFile(){
      if (previewUrl){ URL.revokeObjectURL(previewUrl); previewUrl = null; }
      if (fileInput) fileInput.value = '';
      if (preview) preview.classList.add('d-none');
      if (previewImg) previewImg.src = '';
      if (previewName) previewName.textContent = '';
    }

    function showFile(file){
      if (!file) return clearFile();
      if (!/^image\/(png|jpe?g|gif)$/i.test(file.type)) {
        setError('Only PNG/JPG/JPEG/GIF images are allowed.');
        clearFile();
        return;
      }
      setError('');
      if (previewUrl){ URL.revokeObjectURL(previewUrl); }
      previewUrl = URL.createObjectURL(file);
      if (previewImg) previewImg.src = previewUrl;
      if (previewName) previewName.textContent = file.name || 'image';
      if (preview) preview.classList.remove('d-none');
    }

    if (attachBtn && fileInput){
      attachBtn.addEventListener('click', function(){
        setError('');
        fileInput.click();
      });
      fileInput.addEventListener('change', function(){
        showFile(fileInput.files && fileInput.files[0]);
      });
    }

    if (removeBtn){
      removeBtn.addEventListener('click', function(){
        setError('');
        clearFile();
      });
    }

    // Paste image (Ctrl+V) into message field
    document.addEventListener('paste', function(e){
      if (!fileInput || fileInput.disabled) return;
      const active = document.activeElement;
      const inChat = (active === msgInput) || (form.contains(active));
      if (!inChat) return;

      const items = (e.clipboardData && e.clipboardData.items) ? e.clipboardData.items : [];
      for (const it of items){
        if (it && it.type && it.type.indexOf('image/') === 0){
          const blob = it.getAsFile();
          if (!blob) continue;
          const file = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });

          const dt = new DataTransfer();
          dt.items.add(file);
          fileInput.files = dt.files;
          showFile(file);
          e.preventDefault();
          break;
        }
      }
    });

    // Submit via AJAX with FormData (supports file)
    form.addEventListener('submit', function(e){
      e.preventDefault();

      const msg = (msgInput && msgInput.value) ? msgInput.value.trim() : '';
      const hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

      if (!msg && !hasFile){
        setError('Please type a message or attach an image.');
        return;
      }

      setError('');
      if (sendBtn){
        sendBtn.disabled = true;
        const prog = sendBtn.querySelector('.indicator-progress');
        if (prog) prog.classList.remove('d-none');
      }

      const fd = new FormData(form);

      $.ajax({
        url: form.getAttribute('action'),
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false
      }).done(function(resp){
        try { if (typeof resp === 'string') { JSON.parse(resp); } } catch(e){}
        if (msgInput) msgInput.value = '';
        clearFile();
        try { load_messages(); } catch(e){}
        try { update_scroll(); } catch(e){}
      }).fail(function(){
        setError('Upload failed. Please try again.');
      }).always(function(){
        if (sendBtn){
          sendBtn.disabled = false;
          const prog = sendBtn.querySelector('.indicator-progress');
          if (prog) prog.classList.add('d-none');
        }
      });
    });
  })();

  // Admin: delete chat message
  $(document).on('click', '.lb-msg__del', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const messageId = $(this).data('msg-id');
    if (messageId === undefined || messageId === null || messageId === '') return;

    if (!confirm('Delete this message? This cannot be undone.')) return;

    // Optimistic remove from UI
    const $bubble = $(this).closest('.lb-msg__bubble');
    $bubble.html('<span style="opacity:.65;font-weight:700;">Message deleted.</span>');

    fetch_api('admin_delete_chat_message', Object.assign({}, base_data, { id: messageId }))
      .done(function (res) {
        try { res = JSON.parse(res); } catch (e) { res = { success: false, message: res }; }
        if (!res || res.success !== true) {
          // revert by reloading messages
          load_messages();
          if (typeof create_toast === 'function') {
            create_toast('danger', 'Error', (res && res.message) ? res.message : 'Could not delete message.');
          } else {
            alert((res && res.message) ? res.message : 'Could not delete message.');
          }
          return;
        }
        // Force re-render (delete doesn't change message count)
        chat_json = {};
        msg_none = false;
        load_messages();
      })
      .fail(function () {
        load_messages();
        if (typeof create_toast === 'function') {
          create_toast('danger', 'Error', 'Could not delete message.');
        } else {
          alert('Could not delete message.');
        }
      });
  });

function checkOrderStatusSoft() {
    fetch_api('check_order_status', Object.assign({}, base_data)).done(function(response) {
        try { response = JSON.parse(response); } catch(e){ return; }

        const isRanked5s = lbIsRanked5sOrder || !!(response.is_ranked_5s || response.ranked_5s);
        const nextStatus = String(response.order_status || response.status || '').toUpperCase();
        const currentStatus = String(order_status || '').toUpperCase();

        // Ranked 5s can receive lane/slot updates while staying active.
        // Do not reload the whole admin order view because another booster joined.
        if (isRanked5s && ['PAID','PROCESSING','IN_PROGRESS','PAUSED','COMPLETED'].includes(nextStatus)) {
            order_status = nextStatus || order_status;
            return;
        }

        if (nextStatus && nextStatus !== currentStatus) {
            order_status = nextStatus;
            if (document.visibilityState === 'visible') { message_sound(); }
            setTimeout(function() {
                location.reload();
            }, 1000);
        }
    });
}

window.lbOrderViewChatUpdate = function (data) {
  if (!data || parseInt(data.order_id || 0, 10) === parseInt(order_id, 10)) {
    load_messages();
  }
};

window.lbOrderViewStatusUpdate = function (data) {
  if (!data || parseInt(data.order_id || 0, 10) === parseInt(order_id, 10)) {
    location.reload();
  }
};

window.lbOrderViewAccountUpdate = function (data) {
  if (data && parseInt(data.order_id || 0, 10) !== parseInt(order_id, 10)) return;
  var card = document.getElementById('lbAdminAccountCard');
  if (!card || card.dataset.loading === '1') return;
  card.dataset.loading = '1';
  var fd = new FormData();
  fd.append('action', 'admin_order_account_get');
  fd.append('order_id', order_id);
  fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (!res || !res.success) return;
      ['login', 'password'].forEach(function(field){
        var value = String((res.data && res.data[field]) || '');
        var valueEl = card.querySelector('[data-account-field="' + field + '"]');
        var copyEl = card.querySelector('[data-account-copy="' + field + '"]');
        if (valueEl) {
          valueEl.textContent = value || '—';
          valueEl.dataset.copy = value;
          valueEl.classList.toggle('is-missing', !value);
        }
        if (copyEl) {
          copyEl.dataset.copy = value;
          copyEl.disabled = !value;
        }
      });
    })
    .finally(function(){ card.dataset.loading = '0'; });
};

$(document).ready(function() {
        load_messages();

        update_scroll();

        $(".nav .nav-item:first-child .nav-link").addClass('active');
        $(".nav .nav-item .nav-link").on('click', function() {
            $(".nav .nav-item .nav-link.active").removeClass('active');
            $(this).addClass('active');
        });
        var addClassOnScroll = function() {
            var windowTop = $(window).scrollTop();
            $('section[id]').each(function(index, elem) {
                var offsetTop = $(elem).offset().top;
                var outerHeight = $(this).outerHeight(true);

                if (windowTop > (offsetTop - 50) && windowTop < (offsetTop + outerHeight)) {
                    var elemId = $(elem).attr('id');
                    $(".nav .nav-item .nav-link.active").removeClass('active');
                    $(".nav .nav-item .nav-link[href='#" + elemId + "']").addClass('active');
                }
            });
        };

        $(window).on('scroll', function() {
            addClassOnScroll();
        });

        update_scroll();



    });
</script>

<script>
    let intervalId;
    $(document).ready(function() {
        HSCore.components.HSTomSelect.init('.js-select')
        // Chat is already loaded and kept in sync by the hybrid realtime/polling
        // setup above (load_messages() on init + chat_update socket event +
        // slow fallback timers). No separate interval needed here anymore.
    });

    $(document).on('click', 'a[data-bs-toggle="modal"][data-bs-target="#edit_note_md"]', function(e) {
        e.preventDefault();
        var noteId = $(this).data('note-id');
        var noteType = $(this).data('note-type');
        var noteBody = $(this).data('note-body');
        $('#edit_note_id').val(noteId);
        // Pre-fill body and type so editing keeps the note's original values
        // (e.g. a "client" note is not silently changed to "progress").
        $('#edit_note_md textarea[name="note_body"]').val(noteBody != null ? noteBody : '');
        var $type = $('#edit_note_md select[name="order_note_type"]');
        if (noteType) {
            $type.val(String(noteType).toLowerCase());
        }
    });

    $(document).on('click', 'button[data-bs-toggle="modal"][data-bs-target="#delete_note_md"]', function(e) {
        e.preventDefault();
        var noteId = $(this).data('note-id');
        // The "Delete Note" button inside the Edit modal has no own note id,
        // so fall back to the note currently being edited.
        if (!noteId) {
            noteId = $('#edit_note_id').val();
        }
        $('#delete_note_id').val(noteId);
    });
</script>

<script>
    function handleLoLFields(form_id) {
        let model = $('#edit_order_md');

        const orderServer = model.find('.order-server');
        const orderPrice = model.find('.order-price');
        const orderDuo = model.find('.order-duo');
        const orderStartTier = model.find('.order-start-tier');
        const orderStartDivision = model.find('.order-start-division');
        const orderEndTier = model.find('.order-end-tier');
        const orderEndDivision = model.find('.order-end-division');
        const orderStartLP = model.find('.order-start-lp');
        const orderStartLPManual = model.find('.order-start-lp-manual');
        const orderEndLPManual = model.find('.order-end-lp-manual');
        const orderLPGain = model.find('.order-lp-gain');
        const orderMatches = model.find('.order-matches');
        const orderHours = model.find('.order-hours');
        const orderCoachType = model.find('.order-coach-type');
        const orderRoles = model.find('.order-roles');
        const orderChampions = model.find('.order-champions');
        const orderQueueType = model.find('.order-queue-type');
        const orderFlashPosition = model.find('.order-flash-position');
        const orderPriority = model.find('.order-priority');
        const orderStreaming = model.find('.order-streaming');
        const orderSoloOnly = model.find('.order-solo-only');
        const orderBonusWin = model.find('.order-bonus-win');
        const orderPremiumCoaching = model.find('.order-premium-coaching');
        const orderOfflineMode = model.find('.order-offline-mode');
        const orderArena = model.find('.order-arena');
        const orderStartLevel = model.find('.order-start-level');
        const orderEndLevel = model.find('.order-end-level');
        const orderChampion = model.find('.order-champion');
        const orderStartLevelManual = model.find('.order-start-level-manual');
        const orderEndLevelManual = model.find('.order-end-level-manual');
        const orderStartClash = model.find('.order-start-level-clash')
        const orderWins = model.find('.order-wins');
        const orderGames = model.find('.order-games');
        const orderNormals = model.find('.order-normals');
        const orderPlacements = model.find('.order-placements');
        const orderBoosters = model.find('.order-boosters');
        const orderOptionsHeading = model.find('.order-options-heading');
        const orderHiddenDuo = model.find('.order-hidden-duo');
        const orderUndercoverWinrate = model.find('.order-undercover-winrate');
        const orderModerateKda = model.find('.order-moderate-kda');
        const orderLogin = model.find('.order-login');
        const orderPassword = model.find('.order-password');
        const orderGame = '<?= strtolower((string)($data['game'] ?? '')) ?>';
        const isLolClassicOrder = ['lol_classic', 'lol-classic', 'league-of-legends-classic'].includes(orderGame);
        const lolFormIds = ['1','2','3','4','9','17','18','19','20','26','30','31','32','34','35','36'];
        const valFormIds = ['5','6','7','8'];
        const tftFormIds = ['21','22','23','24'];
        const coachingFormIds = ['15','16','25'];

        function syncAccountFieldLabels(form_id) {
            const formKey = String(form_id);
            let loginLabel = 'Account Username';
            let passwordLabel = 'Account Password';

            if (valFormIds.includes(formKey) || formKey === '16' || orderGame === 'val') {
                loginLabel = 'VAL Username';
                passwordLabel = 'VAL Password';
            } else if (tftFormIds.includes(formKey) || formKey === '25' || orderGame === 'tft') {
                loginLabel = 'TFT Username';
                passwordLabel = 'TFT Password';
            }

            orderLogin.find('label').text(loginLabel);
            orderLogin.find('input').attr('placeholder', loginLabel);
            orderPassword.find('label').text(passwordLabel);
            orderPassword.find('input').attr('placeholder', passwordLabel);
        }

        function syncGameSpecificAccountFields(form_id) {
            const formKey = String(form_id);
            const riotOnly = coachingFormIds.includes(formKey);

            syncAccountFieldLabels(form_id);

            if (riotOnly) {
                hideAndDisable(orderLogin);
                hideAndDisable(orderPassword);
            } else {
                showAndEnable(orderLogin);
                showAndEnable(orderPassword);
            }
        }

        function handleIsDuo(value, form_id) {
            if (form_id == '1') {
                if (value == true) {
                    hideAndDisable(orderRoles);
                    hideAndDisable(orderChampions);
                    hideAndDisable(orderSoloOnly);
                    hideAndDisable(orderStreaming);
                    hideAndDisable(orderLogin);
                    hideAndDisable(orderPassword);
                    hideAndDisable(orderFlashPosition);
                    hideAndDisable(orderOfflineMode);
                    showAndEnable(orderHiddenDuo);

                    showAndEnable(orderPremiumCoaching)
                } else {
                    showAndEnable(orderRoles)
                    showAndEnable(orderChampions)
                    showAndEnable(orderSoloOnly)
                    showAndEnable(orderStreaming)
                    showAndEnable(orderLogin);
                    showAndEnable(orderPassword);
                    showAndEnable(orderFlashPosition);
                    showAndEnable(orderOfflineMode);
                    hideAndDisable(orderHiddenDuo);

                    hideAndDisable(orderPremiumCoaching);
                }
            } else if (form_id == '20') {
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                showAndEnable(orderSoloOnly)
                showAndEnable(orderStreaming)
                showAndEnable(orderBonusWin)
                showAndEnable(orderLogin);
                showAndEnable(orderPassword);
                showAndEnable(orderFlashPosition);
                showAndEnable(orderOfflineMode);

                hideAndDisable(orderPremiumCoaching);
            } else {
                if (value == true) {
                    hideAndDisable(orderRoles);
                    hideAndDisable(orderChampions);
                    hideAndDisable(orderSoloOnly);
                    hideAndDisable(orderStreaming);
                    hideAndDisable(orderLogin);
                    hideAndDisable(orderPassword);
                    hideAndDisable(orderFlashPosition);
                    hideAndDisable(orderOfflineMode);
                    showAndEnable(orderHiddenDuo);

                    showAndEnable(orderPremiumCoaching)
                } else {
                    showAndEnable(orderRoles)
                    showAndEnable(orderChampions)
                    showAndEnable(orderSoloOnly)
                    showAndEnable(orderStreaming)
                    showAndEnable(orderLogin);
                    showAndEnable(orderPassword);
                    showAndEnable(orderFlashPosition);
                    showAndEnable(orderOfflineMode);
                    hideAndDisable(orderHiddenDuo);

                    hideAndDisable(orderBonusWin);
                    hideAndDisable(orderPremiumCoaching);
                }
            }
        }

        function handleLPFields() {
            let startTier = parseInt(orderStartTier.find('option:selected').val());
            let endTier = parseInt(orderEndTier.find('option:selected').val());

            if (isLolClassicOrder) {
                // LoL Classic has divisions only for Salt through Diamond.
                // It has no LP fields, and Legend/Unranked have no division.
                hideAndDisable(orderStartLP);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                if (startTier >= 1 && startTier <= 6) {
                    showAndEnable(orderStartDivision);
                } else {
                    hideAndDisable(orderStartDivision);
                }

                if (String(form_id) === '30' && endTier >= 1 && endTier <= 6) {
                    showAndEnable(orderEndDivision);
                } else {
                    hideAndDisable(orderEndDivision);
                }
                return;
            }

            if (form_id == "1") {
                if (startTier == 0) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    hideAndDisable(orderStartLPManual);
                } else if (startTier >= 8) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    showAndEnable(orderStartLPManual);
                } else {
                    showAndEnable(orderStartLP)
                    showAndEnable(orderStartDivision)
                    hideAndDisable(orderStartLPManual);
                }

                if (endTier == 0) {
                    hideAndDisable(orderEndLPManual);
                    hideAndDisable(orderEndDivision);
                } else if (endTier >= 8) {
                    hideAndDisable(orderEndDivision);
                    showAndEnable(orderEndLPManual);
                } else {
                    showAndEnable(orderEndDivision)
                    hideAndDisable(orderEndLPManual);
                }
            } else if (form_id == "3") {
                if (startTier === 0) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    hideAndDisable(orderStartLPManual);
                } else if (startTier >= 8) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    hideAndDisable(orderStartLPManual);
                    hideAndDisable(orderEndDivision);
                    hideAndDisable(orderEndLPManual);
                } else {
                    showAndEnable(orderStartDivision)
                    hideAndDisable(orderStartLP)
                    hideAndDisable(orderStartLPManual);
                    hideAndDisable(orderEndDivision);
                    hideAndDisable(orderEndLPManual);
                }
            } else {
                if (startTier == 0) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    hideAndDisable(orderStartLPManual);
                } else if (startTier >= 8) {
                    hideAndDisable(orderStartLP);
                    hideAndDisable(orderStartDivision);
                    showAndEnable(orderStartLPManual);
                } else {
                    showAndEnable(orderStartLP)
                    showAndEnable(orderStartDivision)
                    hideAndDisable(orderStartLPManual);
                }
            }
        }

        hideAndDisable(orderArena);
        hideAndDisable(orderStartLevel);
        hideAndDisable(orderEndLevel);
        hideAndDisable(orderChampion);
        hideAndDisable(orderStartLevelManual);
        hideAndDisable(orderEndLevelManual);
        hideAndDisable(orderStartClash);
        hideAndDisable(orderWins);
        hideAndDisable(orderGames);
        hideAndDisable(orderNormals);
        hideAndDisable(orderPlacements);
        hideAndDisable(orderBoosters);
        orderOptionsHeading.show();
        showAndEnable(orderLogin);
        showAndEnable(orderPassword);
        syncGameSpecificAccountFields(form_id);

        switch (String(form_id)) {
            case "29": {
                showAndEnable(orderServer);
                showAndEnable(orderPrice);
                showAndEnable(orderDuo);
                showAndEnable(orderStartTier);
                showAndEnable(orderStartDivision);
                showAndEnable(orderMatches);
                showAndEnable(orderBoosters);
                showAndEnable(orderRoles);
                showAndEnable(orderLogin);
                showAndEnable(orderPassword);

                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                hideAndDisable(orderChampions);
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                hideAndDisable(orderPriority);
                hideAndDisable(orderStreaming);
                hideAndDisable(orderSoloOnly);
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderHiddenDuo);
                hideAndDisable(orderOfflineMode);
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderUndercoverWinrate);
                hideAndDisable(orderModerateKda);
                hideAndDisable(orderLogin);
                hideAndDisable(orderPassword);

                orderOptionsHeading.hide();

                orderDuo.find('input[type="checkbox"]').prop('checked', true);
                orderDuo.find('input[type="hidden"]').val('1');

                orderRoles.find('label').text('Customer Role');
                orderRoles.find('select').removeAttr('multiple').attr('data-placeholder', 'Customer Role');

                return;
            }

            case "30":
            case "1": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                showAndEnable(orderEndTier)
                showAndEnable(orderEndDivision)
                showAndEnable(orderStartLP)
                showAndEnable(orderLPGain)

                hideAndDisable(orderMatches);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                showAndEnable(orderBonusWin)
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)

                handleIsDuo(isDuo, form_id)
                handleLPFields();

                orderDuo.find('input[type="checkbox"]').on('change', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })

                orderStartTier.on('change', function() {
                    handleLPFields();
                })

                orderEndTier.on('change', function() {
                    handleLPFields();
                })

                break;
            }
            case "31":
            case "2": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                showAndEnable(orderLPGain)

                showAndEnable(orderWins)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                showAndEnable(orderBonusWin)
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)

                handleIsDuo(isDuo, form_id)
                handleLPFields();

                orderDuo.find('input[type="checkbox"]').on('change', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })

                orderStartTier.on('change', function() {
                    handleLPFields();
                })

                orderEndTier.on('change', function() {
                    handleLPFields();
                })

                break;
            }
            case "32":
            case "3": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderPlacements)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)

                handleIsDuo(isDuo, form_id)
                handleLPFields();

                orderDuo.find('input[type="checkbox"]').on('change', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })

                orderStartTier.on('change', function() {
                    handleLPFields();
                })

                break;
            }
            case "4":
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)

                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderNormals)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)

                handleIsDuo(isDuo, form_id)

                orderDuo.find('input[type="checkbox"]').on('change', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })

                break;
            case "9": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);
                hideAndDisable(orderLPGain);

                showAndEnable(orderMatches)
                hideAndDisable(orderGames);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                showAndEnable(orderStreaming)

                handleIsDuo(isDuo, form_id)

                orderDuo.find('input[type="checkbox"]').off('change.progames').on('change.progames', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })

                break;
            }
            case "33":
            case "15":
                showAndEnable(orderServer)
                showAndEnable(orderPrice)

                hideAndDisable(orderDuo);
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);

                hideAndDisable(orderMatches);
                showAndEnable(orderHours)
                showAndEnable(orderCoachType)

                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);

                hideAndDisable(orderPriority);
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly);
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                hideAndDisable(orderStreaming);

                hideAndDisable(orderLogin);
                hideAndDisable(orderPassword);

                break;
            case "17": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderArena)
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLevel);
                hideAndDisable(orderEndLevel);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderMatches)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly);
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                hideAndDisable(orderStreaming);

                handleIsDuo(isDuo, form_id)

                orderDuo.find('input[type="checkbox"]').on('change', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })
                break;
            }
            case "34":
            case "18":
                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo);
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);

                showAndEnable(orderStartLevel)
                showAndEnable(orderEndLevel)
                showAndEnable(orderChampion)

                hideAndDisable(orderMatches);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                showAndEnable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                hideAndDisable(orderRoles);
                hideAndDisable(orderChampions);
                showAndEnable(orderStreaming)

                break;
            case "19":
                orderOptionsHeading.hide();

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo);

                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLevel);
                hideAndDisable(orderEndLevel);
                hideAndDisable(orderChampion);
                hideAndDisable(orderStartLevelManual);
                hideAndDisable(orderEndLevelManual);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderStartClash)
                showAndEnable(orderWins)
                showAndEnable(orderBoosters)

                hideAndDisable(orderMatches);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                hideAndDisable(orderPriority);
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly);
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                hideAndDisable(orderRoles);
                hideAndDisable(orderChampions);
                hideAndDisable(orderStreaming);

                break;
            case "20": {
                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo);
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLevel);
                hideAndDisable(orderEndLevel);
                hideAndDisable(orderChampion);
                showAndEnable(orderStartLevelManual)
                showAndEnable(orderEndLevelManual)

                hideAndDisable(orderMatches);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);

                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                showAndEnable(orderBonusWin)
                showAndEnable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                showAndEnable(orderStreaming)

                break;
            }

            case "21": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                showAndEnable(orderEndTier)
                showAndEnable(orderEndDivision)
                showAndEnable(orderStartLP)
                showAndEnable(orderLPGain)

                hideAndDisable(orderMatches);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching)
                hideAndDisable(orderOfflineMode)
                hideAndDisable(orderRoles)
                hideAndDisable(orderChampions)
                hideAndDisable(orderStreaming)

                handleLPFields();
                syncGameSpecificAccountFields(form_id)
                orderDuo.find('input[type="checkbox"]').off('change.tft21').on('change.tft21', function() {
                    syncGameSpecificAccountFields(form_id);
                })
                orderStartTier.off('change.tft21').on('change.tft21', function() { handleLPFields(); })
                orderEndTier.off('change.tft21').on('change.tft21', function() { handleLPFields(); })
                break;
            }
            case "22": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                showAndEnable(orderLPGain)

                showAndEnable(orderWins)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching)
                hideAndDisable(orderOfflineMode)
                hideAndDisable(orderRoles)
                hideAndDisable(orderChampions)
                hideAndDisable(orderStreaming)

                handleLPFields();
                syncGameSpecificAccountFields(form_id)
                orderDuo.find('input[type="checkbox"]').off('change.tft22').on('change.tft22', function() {
                    syncGameSpecificAccountFields(form_id);
                })
                orderStartTier.off('change.tft22').on('change.tft22', function() { handleLPFields(); })
                break;
            }
            case "23": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderPlacements)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching)
                hideAndDisable(orderOfflineMode)
                hideAndDisable(orderRoles)
                hideAndDisable(orderChampions)
                hideAndDisable(orderStreaming)

                handleLPFields();
                syncGameSpecificAccountFields(form_id)
                orderDuo.find('input[type="checkbox"]').off('change.tft23').on('change.tft23', function() {
                    syncGameSpecificAccountFields(form_id);
                })
                orderStartTier.off('change.tft23').on('change.tft23', function() { handleLPFields(); })
                break;
            }
            case "24": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);

                showAndEnable(orderNormals)
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching)
                hideAndDisable(orderOfflineMode)
                hideAndDisable(orderRoles)
                hideAndDisable(orderChampions)
                hideAndDisable(orderStreaming)

                syncGameSpecificAccountFields(form_id)
                orderDuo.find('input[type="checkbox"]').off('change.tft24').on('change.tft24', function() {
                    syncGameSpecificAccountFields(form_id);
                })
                break;
            }
            case "25":
                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo);
                hideAndDisable(orderStartTier);
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderLPGain);
                hideAndDisable(orderMatches);
                showAndEnable(orderHours)
                showAndEnable(orderCoachType)
                hideAndDisable(orderQueueType);
                hideAndDisable(orderFlashPosition);
                hideAndDisable(orderPriority);
                hideAndDisable(orderBonusWin);
                hideAndDisable(orderSoloOnly);
                hideAndDisable(orderPremiumCoaching);
                hideAndDisable(orderOfflineMode);
                hideAndDisable(orderRoles)
                hideAndDisable(orderChampions)
                hideAndDisable(orderStreaming);
                hideAndDisable(orderLogin);
                hideAndDisable(orderPassword);
                break;
            case "35":
            case "26": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                showAndEnable(orderStartTier)
                hideAndDisable(orderStartDivision);
                hideAndDisable(orderEndTier);
                hideAndDisable(orderEndDivision);
                hideAndDisable(orderStartLP);
                hideAndDisable(orderStartLPManual);
                hideAndDisable(orderEndLPManual);
                hideAndDisable(orderLPGain);
                showAndEnable(orderMatches)
                hideAndDisable(orderGames);
                hideAndDisable(orderHours);
                hideAndDisable(orderCoachType);
                showAndEnable(orderQueueType)
                showAndEnable(orderFlashPosition)
                showAndEnable(orderPriority)
                hideAndDisable(orderBonusWin);
                showAndEnable(orderSoloOnly)
                showAndEnable(orderPremiumCoaching)
                showAndEnable(orderOfflineMode)
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                showAndEnable(orderStreaming)

                handleIsDuo(isDuo, form_id)
                orderDuo.find('input[type="checkbox"]').off('change.progames26').on('change.progames26', function() {
                    let value = $(this).is(':checked');
                    handleIsDuo(value, form_id);
                })
                break;
            }

            case "36": {
                const isDuo = true;
                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo)
                showAndEnable(orderStartTier)
                showAndEnable(orderStartDivision)
                hideAndDisable(orderEndTier)
                hideAndDisable(orderEndDivision)
                hideAndDisable(orderStartLP)
                hideAndDisable(orderLPGain)
                hideAndDisable(orderStartLPManual)
                hideAndDisable(orderEndLPManual)
                hideAndDisable(orderMatches)
                showAndEnable(orderHours)
                hideAndDisable(orderCoachType)
                hideAndDisable(orderQueueType)
                hideAndDisable(orderFlashPosition)
                hideAndDisable(orderBonusWin)
                hideAndDisable(orderSoloOnly)
                hideAndDisable(orderPremiumCoaching)
                hideAndDisable(orderOfflineMode)
                showAndEnable(orderPriority)
                showAndEnable(orderRoles)
                showAndEnable(orderChampions)
                hideAndDisable(orderStreaming)
                break;
            }

            case "5":
            case "6":
            case "7":
            case "8": {
                const isDuo = orderDuo.find('input[type="checkbox"]').is(':checked');

                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                showAndEnable(orderDuo)
                syncGameSpecificAccountFields(form_id)

                orderDuo.find('input[type="checkbox"]').off('change.valorantAccount').on('change.valorantAccount', function() {
                    syncGameSpecificAccountFields(form_id);
                })

                break;
            }
            case "16":
                showAndEnable(orderServer)
                showAndEnable(orderPrice)
                hideAndDisable(orderDuo);
                syncGameSpecificAccountFields(form_id)
                break;
            default:
                break;
        }
    }

    function hideAndDisable(element) {
        element.addClass('d-none');
        element.find('select, input, textarea').prop('disabled', true);
        element.find('input[type="checkbox"]').prop('checked', false);
    }

    function showAndEnable(element) {
        element.removeClass('d-none');
        element.find('select, input, textarea').prop('disabled', false);
    }

    $(document).ready(function() {
        $('#edit_order_md').on('shown.bs.modal', function() {
            let form_id = <?= $data['form_id'] ?>;
            let form_selector = $('#edit_order_md')
                .find('[name="orders-form_id"]');

            handleLoLFields(form_id);

            form_selector.on('change', function() {
                let selected_form_id = $(this).find(':selected').val();

                handleLoLFields(selected_form_id);
            })
        });

        let cut_amount = '<?= util_format_price_display($cutAmount) ?>';
        let cut_percent = '<?= $cutPercent ?>';

        $('#complete_order_md').find('[name="is_fixed"]').on('change', function() {
            let val = $(this).val(); // This gets '0' or '1' as string

            if (val === '0') {
                $('[name="cut"]').val(cut_percent);
            } else {
                $('[name="cut"]').val(cut_amount);
            }
        });
    })
</script>

<script>
  $(document).ready(function () {
    // Only a real click inside the chat marks messages as Read
    $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

    function ajax_response_handler(res) {
      let response = JSON.parse(res);

      if (response.resetForm) {
        $ajaxForm[0].reset();
      }

      if (response.sendToast) {
        create_toast(
          response.sendToast.type,
          response.sendToast.title,
          response.sendToast.message
        );
      }

      if (response.playSound) {
        var audio = new Audio(
          asset_url + '/core/dash/audio/' + response.playSound + '.mp3'
        );
        audio.play();
      }

      if (response.redirectUrl) {
        setTimeout(function () {
          window.location.href = response.redirectUrl;
        }, 1500);
      }

      if (response.refreshPage && !(lbIsRanked5sOrder || response.is_ranked_5s || response.ranked_5s)) {
        setTimeout(function () {
          location.reload();
        }, 1500);
      }
    }


    (function () {
      const POKE_COOLDOWN_SECONDS = 300;

      function getPokeStorageKey($btn) {
        const refType = $btn.data('ref-type') || 'order';
        const refId = $btn.data('id') || (typeof order_id !== 'undefined' ? order_id : <?= (int)$data['id'] ?>);
        return 'lb_admin_poke_client_' + refType + '_' + refId;
      }

      function parsePokeResponse(raw) {
        if (!raw) return null;
        if (typeof raw === 'object') return raw;

        try {
          return JSON.parse(raw);
        } catch (e) {}

        const str = String(raw);
        const start = str.indexOf('{');
        const end = str.lastIndexOf('}');
        if (start !== -1 && end !== -1 && end > start) {
          try {
            return JSON.parse(str.slice(start, end + 1));
          } catch (e) {}
        }

        return null;
      }

      function showPokeToast(type, title, message) {
        if (typeof create_toast === 'function') {
          create_toast(type || 'primary', title || 'Notice', message || 'Done');
        } else if (message) {
          alert(message);
        }
      }

      function playPokeSound(sound) {
        if (!sound) return;
        try { new Audio(asset_url + '/core/dash/audio/' + sound + '.mp3').play(); } catch (e) {}
      }

      function secondsLeft($btn) {
        const last = parseInt(localStorage.getItem(getPokeStorageKey($btn)) || '0', 10);
        if (!last) return 0;
        const elapsed = Math.floor((Date.now() - last) / 1000);
        return Math.max(0, POKE_COOLDOWN_SECONDS - elapsed);
      }

      function formatLeft(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return m + ':' + String(s).padStart(2, '0');
      }

      function startPokeCooldown($btn, seconds) {
        const cooldownSeconds = Math.max(1, parseInt(seconds || POKE_COOLDOWN_SECONDS, 10));
        const key = getPokeStorageKey($btn);
        const startedAt = Date.now() - ((POKE_COOLDOWN_SECONDS - cooldownSeconds) * 1000);
        localStorage.setItem(key, String(startedAt));
        updatePokeCooldown($btn);
      }

      function updatePokeCooldown($btn) {
        const oldHtml = $btn.data('old-html') || $btn.html();
        $btn.data('old-html', oldHtml);

        const left = secondsLeft($btn);
        if (left > 0) {
          $btn.prop('disabled', true).addClass('disabled').html('<i class="fa-duotone fa-clock me-1"></i> Wait ' + formatLeft(left));
          if (!$btn.data('cooldown-timer')) {
            const timer = setInterval(function () {
              if (!document.body.contains($btn[0])) {
                clearInterval(timer);
                return;
              }
              const nowLeft = secondsLeft($btn);
              if (nowLeft <= 0) {
                clearInterval(timer);
                $btn.data('cooldown-timer', null).prop('disabled', false).removeClass('disabled').html($btn.data('old-html'));
                return;
              }
              $btn.html('<i class="fa-duotone fa-clock me-1"></i> Wait ' + formatLeft(nowLeft));
            }, 1000);
            $btn.data('cooldown-timer', timer);
          }
          return true;
        }

        $btn.prop('disabled', false).removeClass('disabled').html(oldHtml);
        return false;
      }

      $('.js-admin-poke-client').each(function () {
        updatePokeCooldown($(this));
      });

      $(document).on('click', '.js-admin-poke-client', function (e) {
        e.preventDefault();

        const $btn = $(this);
        if ($btn.data('busy')) return;
        if (updatePokeCooldown($btn)) {
          showPokeToast('warning', 'Cooldown', 'Please wait 5 minutes before poking this client again.');
          return;
        }

        const oldHtml = $btn.data('old-html') || $btn.html();
        $btn.data('old-html', oldHtml);

        const ajaxUrl = '<?= AJAX_URL ?>';
        const refType = $btn.data('ref-type') || 'order';
        const refId = $btn.data('id') || (typeof order_id !== 'undefined' ? order_id : <?= (int)$data['id'] ?>);

        $btn.data('busy', 1).prop('disabled', true).addClass('disabled').html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
          url: ajaxUrl,
          method: 'POST',
          dataType: 'text',
          data: {
            action: 'admin_poke_client',
            ref_type: refType,
            id: refId,
            order_id: refId
          }
        }).done(function (raw) {
          const response = parsePokeResponse(raw);
          if (!response) {
            showPokeToast('danger', 'Error', 'Poke was sent, but the server response could not be read cleanly.');
            return;
          }

          if (response.sendToast) {
            showPokeToast(response.sendToast.type || 'primary', response.sendToast.title || 'Notice', response.sendToast.message || 'Done');
          }
          playPokeSound(response.playSound);

          const toastType = response.sendToast && response.sendToast.type ? response.sendToast.type : '';
          if (toastType === 'primary' || toastType === 'success' || response.cooldown_seconds) {
            startPokeCooldown($btn, response.cooldown_seconds || POKE_COOLDOWN_SECONDS);
          }
        }).fail(function (xhr) {
          const response = xhr && xhr.responseText ? parsePokeResponse(xhr.responseText) : null;
          if (response && response.sendToast) {
            showPokeToast(response.sendToast.type || 'danger', response.sendToast.title || 'Error', response.sendToast.message || 'Could not poke the client.');
            playPokeSound(response.playSound);
            if (response.cooldown_seconds) startPokeCooldown($btn, response.cooldown_seconds);
            return;
          }
          showPokeToast('danger', 'Error', 'Could not poke the client.');
        }).always(function () {
          $btn.data('busy', 0);
          if (!updatePokeCooldown($btn)) {
            $btn.prop('disabled', false).removeClass('disabled').html(oldHtml);
          }
        });
      });
    })();

    $(".rating-input").rating({
      min: 0,
      max: 5,
      step: 1,
      size: 'md',
      showClear: false,
      showCaption: false,
      theme: 'krajee-fas'
    });

    const boxes = document.querySelectorAll('.highlights input[type="checkbox"]');
    const counter = document.getElementById('highlight-count');

    function updateCounter() {
      const count = document.querySelectorAll('.highlights input[type="checkbox"]:checked').length;
      counter.textContent = `${count}/3`;
    }

    boxes.forEach(box => {
      box.addEventListener('change', e => {
        const checked = document.querySelectorAll('.highlights input[type="checkbox"]:checked');

        if (checked.length > 3) {
          e.target.checked = false;
          return;
        }

        updateCounter();
      });
    });

    updateCounter();

    $('#leave-review-form').on('submit', function (e) {
      e.preventDefault();
      const $form = $(this);

      const scores = ['communication', 'skill', 'speed', 'overall'];
      let valid = true;

      scores.forEach(score => {
        const val = $(this).find(`input[name="score[${score}]"]`).val();
        if (!val || isNaN(val) || parseInt(val, 10) < 1 || parseInt(val, 10) > 5) {
          valid = false;
        }
      });

      const highlights = $(this).find('.highlights input[type="checkbox"]:checked').length;
      if (highlights < 1) valid = false;

      if (!valid) {
        create_toast(
          'danger',
          'Incomplete Review',
          'Please provide a rating for all categories and select at least one highlight.'
        );
      } else {
        $.ajax({
          url: '<?= AJAX_URL ?>',
          type: 'POST',
          data: $form.serialize(),
          beforeSend: function () {
            $('#leave-review-form button[type="submit"] .spinner-border').removeClass('d-none');
            $('#leave-review-form button[type="submit"] span').hide();
            $('#leave-review-form button[type="submit"]').prop('disabled', true);
          },
          success: function (res) {
            ajax_response_handler(res);
          },
          error: function (xhr, status, error) {
            console.error(error);
            $('#leave-review-form button[type="submit"] .spinner-border').addClass('d-none');
            $('#leave-review-form button[type="submit"] span').show();
            $('#leave-review-form button[type="submit"]').prop('disabled', false);
            create_toast(
              'danger',
              'Error',
              'Something went wrong while submitting your review. Please try again.'
            );
          }
        });
      }
    });
  })
</script>

<style id="lbAdminStep3">
.admin-order-view .order-chat-card .card-footer{position:relative;}
.admin-order-view .admin-order-sidebar .list-unstyled.list-py-1{display:flex;flex-direction:column;gap:.65rem;margin:0;padding:0;}
.admin-order-view .admin-order-sidebar .list-unstyled.list-py-1>li{margin:0;padding:.75rem .85rem;border:1px solid rgba(255,255,255,.08);border-radius:.95rem;background:rgba(255,255,255,.03);transition:.15s ease;}
.admin-order-view .admin-order-sidebar .list-unstyled.list-py-1>li:hover{background:rgba(255,255,255,.05);transform:translateY(-1px);}
.admin-order-view [data-theme="light"] .admin-order-sidebar .list-unstyled.list-py-1>li{background:rgba(0,0,0,.02);border-color:rgba(0,0,0,.08);}
.admin-order-view [data-theme="light"] .admin-order-sidebar .list-unstyled.list-py-1>li:hover{background:rgba(0,0,0,.03);}
.admin-order-view .admin-order-sidebar .avatar.avatar-sm{width:38px;height:38px;border-radius:14px;}
.admin-order-view .admin-order-sidebar .avatar.avatar-light{background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.10);}
.admin-order-view [data-theme="light"] .admin-order-sidebar .avatar.avatar-light{background:#fff;border-color:rgba(0,0,0,.10);}
.admin-order-view .lb-modal .btn-close{filter:invert(1);opacity:.75;}
.admin-order-view .lb-modal .btn-close:hover{opacity:1;}
.admin-order-view [data-theme="light"] .lb-modal .btn-close{filter:none;opacity:.55;}

/* --- Step 4 fixes: modal dropdown visibility + z-index --- */
/* --- Step 5: modals (dark + not transparent) --- */
.admin-order-view .modal .modal-content,
body.admin-order-view .modal .modal-content{
  background:#25282A !important;
  border:1px solid rgba(255,255,255,.08) !important;
  border-radius:1.25rem !important;
  box-shadow:0 30px 90px rgba(0,0,0,.65) !important;
}
.admin-order-view .modal .modal-header,
body.admin-order-view .modal .modal-header{
  background:rgba(255,255,255,.03) !important;
  border-bottom:1px solid rgba(255,255,255,.08) !important;
}
.admin-order-view .modal .modal-footer,
body.admin-order-view .modal .modal-footer{
  background:rgba(255,255,255,.02) !important;
  border-top:1px solid rgba(255,255,255,.08) !important;
}
.admin-order-view .modal .btn-close{filter:invert(1);opacity:.75;}
.admin-order-view .modal .btn-close:hover{opacity:1;}
.admin-order-view .modal-backdrop.show{opacity:.55;}

/* TomSelect / Select2 dropdown above modal/backdrop */
.modal-open .ts-dropdown,
.modal-open .select2-container,
.modal-open .select2-dropdown{
  z-index: 20060 !important; /* > Bootstrap modal (1055) */
}

/* Fix specific modal bodies that were clipping */
.admin-order-view #add_booster_md .modal-body,
.admin-order-view #add_booster_md .modal-content{
  overflow: visible !important;
}


/* --- Desktop Emoji Picker (Order Chat) - Client Dashboard --- */
.admin-order-view .order-chat-card .card-footer{ position: relative; }

.admin-order-view .lb-emoji-btn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 34px;
  border-radius: 10px;
}

.admin-order-view .lb-emoji-picker{
  position: absolute;
  right: 12px;
  bottom: 64px;
  width: 360px;
  max-width: calc(100vw - 24px);
  background: rgba(35,38,43,.98);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 16px;
  box-shadow: 0 18px 60px rgba(0,0,0,.55);
  z-index: 1050;
  overflow: hidden;
}

.admin-order-view .lb-emoji-picker__head{
  padding: 10px 10px 8px 10px;
  border-bottom: 1px solid rgba(255,255,255,.08);
}

.admin-order-view .lb-emoji-picker__search{
  width: 100%;
  height: 34px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.06);
  color: rgba(255,255,255,.92);
  padding: 0 10px;
  outline: none;
}

.admin-order-view .lb-emoji-picker__search::placeholder{
  color: rgba(255,255,255,.45);
}

.admin-order-view .lb-emoji-picker__tabs{
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 10px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  overflow-x: auto;
  scrollbar-width: none;
}
.admin-order-view .lb-emoji-picker__tabs::-webkit-scrollbar{ display:none; }

.admin-order-view .lb-emoji-picker__tab{
  flex: 0 0 auto;
  height: 32px;
  min-width: 32px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.06);
  color: rgba(255,255,255,.9);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: .15s ease;
  user-select: none;
}

.admin-order-view .lb-emoji-picker__tab:hover{
  background: rgba(255,255,255,.10);
}

.admin-order-view .lb-emoji-picker__tab.is-active{
  border-color: rgba(124,92,255,.55);
  box-shadow: 0 0 0 3px rgba(124,92,255,.18) inset;
  background: rgba(124,92,255,.12);
}

.admin-order-view .lb-emoji-picker__grid{
  padding: 10px;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 6px;
  max-height: 260px;
  overflow: auto;
}

.admin-order-view .lb-emoji{
  height: 36px;
  border: 0;
  border-radius: 10px;
  background: rgba(255,255,255,.06);
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: .12s ease;
}

.admin-order-view .lb-emoji:hover{
  background: rgba(255,255,255,.12);
  transform: translateY(-1px);
}

.admin-order-view .lb-emoji-picker__empty{
  padding: 14px;
  color: rgba(255,255,255,.65);
  font-size: .95rem;
}

/* hide emoji picker controls on mobile */
@media (max-width: 767.98px){
  .admin-order-view #lbEmojiBtn{display:none !important;}
  .admin-order-view #lbEmojiPicker{display:none !important;}
}


/* --- Step 5: Notes + Tip redesign --- */
.admin-order-view .lb-notes-card .list-group{
  background: transparent;
  border: 0;
  display: flex;
  flex-direction: column;
  gap: .65rem;
  margin: 0;
  padding: 0;
}
.admin-order-view .lb-notes-card .list-group-item{
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: .95rem;
  padding: .85rem .9rem;
  color: inherit;
}
.admin-order-view .lb-notes-card .list-group-item a{
  color: inherit;
  opacity: .85;
}
.admin-order-view .lb-notes-card .list-group-item a:hover{opacity:1;}
.admin-order-view .lb-notes-card .badge{border-radius:999px;padding:.35rem .6rem;font-weight:800;}
.admin-order-view .lb-notes-card .card-footer{background:transparent;border-top:0;padding-top:.2rem;}

.admin-order-view .lb-tip-card .card-body{padding:.95rem 1rem;}
.admin-order-view .lb-tip-card .tip-row{
  display:flex;
  align-items:center;
  gap:.8rem;
  padding:.85rem .9rem;
  border:1px solid rgba(255,255,255,.08);
  border-radius:.95rem;
  background:rgba(255,255,255,.03);
}
.admin-order-view .lb-tip-card .tip-ico{
  width:38px;height:38px;border-radius:14px;
  display:grid;place-items:center;
  background:rgba(88,101,242,.14);
  border:1px solid rgba(88,101,242,.26);
  color:#cfd5ff;
  flex:0 0 auto;
}
.admin-order-view .lb-tip-card .tip-txt{min-width:0;}
.admin-order-view .lb-tip-card .tip-amount{font-weight:900;}
.admin-order-view .lb-tip-card .tip-desc{opacity:.7;font-size:.9rem;margin-top:.15rem;}


.admin-order-view .ts-dropdown,
.admin-order-view .tom-select-custom .ts-dropdown,
.admin-order-view .select2-container,
.admin-order-view .select2-dropdown {
  z-index: 3000 !important;
}

.admin-order-view .lb-emoji-picker {
  z-index: 3500;
}

body.admin-order-view .modal .ts-dropdown{position:absolute !important;}


/* =========================
   ORDER ACTIONS (Desktop pill like Client)
========================= */
.admin-order-view .lb-order-actions-btn{
  display:inline-flex;
  align-items:center;
  gap:.55rem;
  padding:.55rem .9rem;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.10);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
}
.admin-order-view .lb-order-actions-btn:hover{
  background: rgba(255,255,255,.08);
}
.admin-order-view .lb-order-actions-btn__ico{
  width:28px;
  height:28px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
 
}
.admin-order-view .lb-order-actions-btn__txt{
  font-weight:900;
  letter-spacing:.02em;
  font-size:.85rem;
}
.admin-order-view .lb-order-actions-btn__chev{
  opacity:.75;
  font-size:.85rem;
}
@media (max-width:991.98px){
  .admin-order-view .lb-order-actions-btn{ padding:.55rem .7rem; }
}

/* =========================
   PAUSED BANNER (fix icon + client-like)
========================= */
.admin-order-view .lb-banner{
  border-radius: 1rem;
  padding: .85rem 1rem;
  display:flex;
  gap:.85rem;
  align-items:flex-start;
}
.admin-order-view .lb-banner--paused{
  background: rgba(255, 196, 0, .10);
  border: 1px solid rgba(255, 196, 0, .22);
}
.admin-order-view .lb-banner--paused .lb-banner__icon{
  width:40px;
  height:40px;
  border-radius: 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(255, 196, 0, .14);
  border: 1px solid rgba(255, 196, 0, .22);
  color:#ffd36a;
  flex: 0 0 auto;
}
.admin-order-view .lb-banner__title{
  font-weight:900;
  margin-bottom:.1rem;
}
.admin-order-view .lb-banner__sub{
  opacity:.85;
}

/* =========================
   MODALS (Client-like)
========================= */
.admin-order-view .modal-backdrop.show{ opacity:.65; }
.admin-order-view .modal-dialog{ --lb-modal-w: 620px; }
@media (min-width:992px){
  .admin-order-view .modal-dialog{ max-width: var(--lb-modal-w); }
}
.admin-order-view .modal-content{
  background: rgba(28, 30, 34, .98);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 1.25rem;
  box-shadow: 0 25px 80px rgba(0,0,0,.55);
  overflow: hidden;
}
.admin-order-view .modal-header{
  padding: 1.05rem 1.25rem;
  border-bottom: 1px solid rgba(255,255,255,.08);
}
.admin-order-view .modal-title{
  font-weight: 900;
  letter-spacing: .01em;
}
.admin-order-view .modal-body{
  padding: 1.05rem 1.25rem;
}
.admin-order-view .modal-footer{
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(255,255,255,.08);
  gap: .6rem;
}
.admin-order-view .modal .btn-close{
  filter: invert(1) grayscale(1);
  opacity: .65;
}
.admin-order-view .modal .btn-close:hover{ opacity: .9; }

.admin-order-view .modal .form-control,
.admin-order-view .modal .form-select,
.admin-order-view .modal textarea{
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10) !important;
  color: inherit;
  border-radius: .85rem;
}
.admin-order-view .modal .form-control:focus,
.admin-order-view .modal .form-select:focus,
.admin-order-view .modal textarea:focus{
  box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .18);
  border-color: rgba(99, 102, 241, .55) !important;
}

/* Make native <select> dropdown dark (options list) */
.admin-order-view .modal select.form-select{
  color-scheme: dark;
}
.admin-order-view .modal select.form-select option,
.admin-order-view .modal select.form-select optgroup{
  background: #1f2226;
  color: #fff;
}
.admin-order-view [data-theme="light"] .modal select.form-select{
  color-scheme: light;
}
.admin-order-view [data-theme="light"] .modal select.form-select option,
.admin-order-view [data-theme="light"] .modal select.form-select optgroup{
  background: #fff;
  color: #111;
}

.admin-order-view .modal .btn.btn-primary{
  border-radius: .9rem;
  padding: .65rem 1rem;
  font-weight: 900;
}
.admin-order-view .modal .btn.btn-white{
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: inherit;
  border-radius: .9rem;
  padding: .65rem 1rem;
  font-weight: 800;
}
.admin-order-view .modal .btn-group .btn{
  border-radius: .85rem !important;
}
.admin-order-view .modal .btn-group .btn + .btn{
  margin-left: .4rem;
}

/* Add Booster TomSelect dropdown in modal (keep under select, above modal chrome) */
.admin-order-view #add_booster_md .ts-dropdown{
  z-index: 1066; /* modal is 1055 */
}


/* --- FIX: option values always left (mobile + desktop) --- */
.admin-order-view .lb-opt-text,
.admin-order-view .lb-opt-sub,
.admin-order-view .lb-opt-val{
  align-items: flex-start !important;
  text-align: left !important;
}

.admin-order-view .lb-opt-sub .lb-opt-val,
.admin-order-view .lb-opt-val{
  display: flex !important;
  justify-content: flex-start !important;
  width: 100% !important;
  flex: 0 0 auto !important;
}


/* Admin Edit Order: Riot ID lookup + manual save helper */
.admin-order-view .lb-admin-riot-tools{margin-top:.7rem;}
.admin-order-view .lb-admin-riot-actions{display:flex;align-items:center;flex-wrap:wrap;gap:.5rem;}
.admin-order-view .lb-admin-riot-check,.admin-order-view .lb-admin-riot-manual{border-radius:999px;font-weight:900;padding:.45rem .75rem;}
.admin-order-view .lb-admin-riot-hint{font-size:.78rem;color:rgba(255,255,255,.58);}
.admin-order-view .lb-admin-riot-preview{margin-top:.7rem;display:flex;align-items:center;gap:.75rem;padding:.8rem;border-radius:1rem;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);}
.admin-order-view .lb-admin-riot-preview.is-loading{border-color:rgba(116,185,255,.25);background:rgba(116,185,255,.08);}
.admin-order-view .lb-admin-riot-preview.is-found{border-color:rgba(31,230,198,.25);background:rgba(31,230,198,.09);}
.admin-order-view .lb-admin-riot-preview.is-warning{border-color:rgba(255,196,77,.28);background:rgba(255,196,77,.09);}
.admin-order-view .lb-admin-riot-preview.is-error{border-color:rgba(255,92,122,.28);background:rgba(255,92,122,.09);}
.admin-order-view .lb-admin-riot-preview.is-manual{border-color:rgba(177,140,255,.28);background:rgba(177,140,255,.09);}
.admin-order-view .lb-admin-riot-preview__icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;flex:0 0 auto;overflow:hidden;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);}
.admin-order-view .lb-admin-riot-preview__icon img{width:100%;height:100%;object-fit:cover;display:block;}
.admin-order-view .lb-admin-riot-preview__label{font-size:.72rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.62);}
.admin-order-view .lb-admin-riot-preview__name{margin-top:.08rem;font-weight:900;color:#fff;word-break:break-word;}
.admin-order-view .lb-admin-riot-preview__meta{margin-top:.12rem;font-size:.84rem;color:rgba(255,255,255,.70);}
.admin-order-view [data-theme="light"] .lb-admin-riot-hint{color:rgba(0,0,0,.52);}
.admin-order-view [data-theme="light"] .lb-admin-riot-preview__label{color:rgba(0,0,0,.58);}
.admin-order-view [data-theme="light"] .lb-admin-riot-preview__name{color:rgba(0,0,0,.88);}
.admin-order-view [data-theme="light"] .lb-admin-riot-preview__meta{color:rgba(0,0,0,.62);}

</style>
<script id="lbAdminStep3JS">

// Desktop emoji picker for order chat (Client Dashboard version - hidden on mobile)
document.addEventListener('DOMContentLoaded', function () {
  if (window.matchMedia('(max-width: 767.98px)').matches) return;

  const btn = document.getElementById('lbEmojiBtn');
  const picker = document.getElementById('lbEmojiPicker');
  const grid = document.getElementById('lbEmojiGrid');
  const search = document.getElementById('lbEmojiSearch');
  const tabs = document.getElementById('lbEmojiTabs');
  const input = document.getElementById('lbChatMessageInput');

  if (!btn || !picker || !grid || !tabs || !input) return;

  const RECENT_KEY = 'lb_recent_emojis_v1';
  const MAX_RECENT = 24;

  const EMOJIS = {
    smileys: [
      { e: '😀', k: 'grinning happy' }, { e: '😃', k: 'smile happy' }, { e: '😄', k: 'laugh happy' }, { e: '😁', k: 'grin' },
      { e: '😆', k: 'laugh' }, { e: '😅', k: 'sweat laugh' }, { e: '🤣', k: 'rofl' }, { e: '😂', k: 'joy tears' },
      { e: '🙂', k: 'slight smile' }, { e: '😉', k: 'wink' }, { e: '😊', k: 'blush' }, { e: '😍', k: 'heart eyes' },
      { e: '😘', k: 'kiss' }, { e: '😋', k: 'yum' }, { e: '😎', k: 'sunglasses cool' }, { e: '🤩', k: 'star struck' },
      { e: '🤔', k: 'thinking' }, { e: '😐', k: 'neutral' }, { e: '🙄', k: 'eyeroll' }, { e: '😴', k: 'sleep' },
      { e: '😢', k: 'cry' }, { e: '😭', k: 'sob' }, { e: '😡', k: 'angry' }, { e: '🤯', k: 'mind blown' }
    ],
    gestures: [
      { e: '👍', k: 'thumbs up' }, { e: '👎', k: 'thumbs down' }, { e: '👏', k: 'clap' }, { e: '🙌', k: 'raise hands' },
      { e: '🫶', k: 'heart hands' }, { e: '👊', k: 'fist' }, { e: '✊', k: 'raised fist' }, { e: '🤝', k: 'handshake' },
      { e: '🙏', k: 'pray please' }, { e: '🖐️', k: 'hand' }, { e: '✋', k: 'stop' }, { e: '👌', k: 'ok' },
      { e: '🤌', k: 'pinched fingers' }, { e: '🤞', k: 'fingers crossed' }, { e: '🤟', k: 'love you' }, { e: '🤙', k: 'call me' },
      { e: '👋', k: 'wave' }, { e: '💪', k: 'strong' }, { e: '🫡', k: 'salute' }, { e: '🖕', k: 'middle finger' }
    ],
    animals: [
      { e: '🐶', k: 'dog' }, { e: '🐱', k: 'cat' }, { e: '🐭', k: 'mouse' }, { e: '🐹', k: 'hamster' },
      { e: '🐰', k: 'rabbit' }, { e: '🦊', k: 'fox' }, { e: '🐻', k: 'bear' }, { e: '🐼', k: 'panda' },
      { e: '🐨', k: 'koala' }, { e: '🐯', k: 'tiger' }, { e: '🦁', k: 'lion' }, { e: '🐮', k: 'cow' },
      { e: '🐷', k: 'pig' }, { e: '🐸', k: 'frog' }, { e: '🐵', k: 'monkey' }, { e: '🦄', k: 'unicorn' },
      { e: '🐔', k: 'chicken' }, { e: '🐧', k: 'penguin' }, { e: '🐦', k: 'bird' }, { e: '🐢', k: 'turtle' }
    ],
    food: [
      { e: '🍎', k: 'apple' }, { e: '🍌', k: 'banana' }, { e: '🍇', k: 'grapes' }, { e: '🍓', k: 'strawberry' },
      { e: '🍑', k: 'peach' }, { e: '🍍', k: 'pineapple' }, { e: '🍉', k: 'watermelon' }, { e: '🍒', k: 'cherries' },
      { e: '🍔', k: 'burger' }, { e: '🍕', k: 'pizza' }, { e: '🌭', k: 'hotdog' }, { e: '🍟', k: 'fries' },
      { e: '🌮', k: 'taco' }, { e: '🍣', k: 'sushi' }, { e: '🍜', k: 'ramen' }, { e: '🍰', k: 'cake' },
      { e: '🍫', k: 'chocolate' }, { e: '🍩', k: 'donut' }, { e: '☕', k: 'coffee' }, { e: '🍺', k: 'beer' }
    ],
    activities: [
      { e: '⚽', k: 'soccer' }, { e: '🏀', k: 'basketball' }, { e: '🎮', k: 'game controller' }, { e: '🎯', k: 'dart' },
      { e: '🎲', k: 'dice' }, { e: '🎵', k: 'music' }, { e: '🎧', k: 'headphones' }, { e: '🎸', k: 'guitar' },
      { e: '🎬', k: 'movie' }, { e: '🏆', k: 'trophy win' }, { e: '🥇', k: 'gold medal' }, { e: '🔥', k: 'fire' },
      { e: '💯', k: '100' }, { e: '✨', k: 'sparkles' }, { e: '🎉', k: 'party' }, { e: '🎊', k: 'confetti' }
    ],
    travel: [
      { e: '✈️', k: 'airplane' }, { e: '🚗', k: 'car' }, { e: '🚕', k: 'taxi' }, { e: '🚌', k: 'bus' },
      { e: '🚆', k: 'train' }, { e: '🚀', k: 'rocket' }, { e: '🗺️', k: 'map' }, { e: '🏝️', k: 'island' },
      { e: '🏖️', k: 'beach' }, { e: '🏔️', k: 'mountain' }, { e: '🌋', k: 'volcano' }, { e: '🌆', k: 'city' },
      { e: '🏠', k: 'home' }, { e: '📍', k: 'pin location' }, { e: '🧳', k: 'luggage' }, { e: '⛱️', k: 'umbrella' }
    ],
    objects: [
      { e: '💡', k: 'idea light' }, { e: '📌', k: 'pin' }, { e: '📎', k: 'paperclip' }, { e: '🖊️', k: 'pen' },
      { e: '🗒️', k: 'notes' }, { e: '📷', k: 'camera' }, { e: '🔒', k: 'lock' }, { e: '🔑', k: 'key' },
      { e: '💻', k: 'laptop' }, { e: '🖥️', k: 'desktop' }, { e: '📱', k: 'phone' }, { e: '🕹️', k: 'joystick' },
      { e: '🎁', k: 'gift' }, { e: '⏰', k: 'alarm clock' }, { e: '🧠', k: 'brain' }, { e: '⚡', k: 'zap' }
    ],
    symbols: [
      { e: '❤️', k: 'heart' }, { e: '🧡', k: 'orange heart' }, { e: '💛', k: 'yellow heart' }, { e: '💚', k: 'green heart' },
      { e: '💙', k: 'blue heart' }, { e: '💜', k: 'purple heart' }, { e: '🖤', k: 'black heart' }, { e: '🤍', k: 'white heart' },
      { e: '✅', k: 'check' }, { e: '❌', k: 'cross' }, { e: '⚠️', k: 'warning' }, { e: '⭐', k: 'star' },
      { e: '🌟', k: 'glowing star' }, { e: '❓', k: 'question' }, { e: '❗', k: 'exclamation' }, { e: '🏁', k: 'finish flag' }
    ]
  };

  function getRecent(){
    try {
      const v = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
      return Array.isArray(v) ? v : [];
    } catch(e){ return []; }
  }

  function addRecent(emoji){
    const curr = getRecent().filter(x => x !== emoji);
    curr.unshift(emoji);
    const next = curr.slice(0, MAX_RECENT);
    try { localStorage.setItem(RECENT_KEY, JSON.stringify(next)); } catch(e){}
  }

  function setActiveTab(cat){
    tabs.querySelectorAll('.lb-emoji-picker__tab').forEach(b => {
      b.classList.toggle('is-active', b.dataset.cat === cat);
    });
  }

  function insertAtCursor(el, text){
    const start = el.selectionStart ?? el.value.length;
    const end = el.selectionEnd ?? el.value.length;
    const before = el.value.slice(0, start);
    const after = el.value.slice(end);
    el.value = before + text + after;
    const pos = start + text.length;
    try { el.setSelectionRange(pos, pos); } catch(e){}
    el.focus();
  }

  function flattenAll(){
    const out = [];
    Object.keys(EMOJIS).forEach(cat => {
      EMOJIS[cat].forEach(item => out.push({ ...item, cat }));
    });
    return out;
  }

  function render(cat){
    const q = (search.value || '').trim().toLowerCase();
    let list = [];

    if (q){
      list = flattenAll().filter(it => (it.k && it.k.includes(q)) || it.e.includes(q));
    } else if (cat === 'recent'){
      const rec = getRecent();
      list = rec.map(e => ({ e, k: 'recent', cat: 'recent' }));
    } else {
      list = (EMOJIS[cat] || []).slice();
    }

    grid.innerHTML = '';
    if (!list.length){
      grid.innerHTML = '<div class="lb-emoji-picker__empty">No emojis found.</div>';
      return;
    }

    list.forEach(it => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'lb-emoji';
      b.textContent = it.e;
      b.title = it.k || '';
      b.addEventListener('click', () => {
        insertAtCursor(input, it.e);
        addRecent(it.e);
        if (tabs.querySelector('.lb-emoji-picker__tab.is-active')?.dataset?.cat === 'recent' && !q){
          render('recent');
        }
      });
      grid.appendChild(b);
    });
  }

  function openPicker(){
    picker.classList.remove('d-none');
    render(tabs.querySelector('.lb-emoji-picker__tab.is-active')?.dataset?.cat || 'recent');
    setTimeout(() => search.focus(), 0);
  }
  function closePicker(){
    picker.classList.add('d-none');
    search.value = '';
  }
  function togglePicker(){
    if (picker.classList.contains('d-none')) openPicker();
    else closePicker();
  }

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    togglePicker();
  });

  tabs.addEventListener('click', function(e){
    const b = e.target.closest('.lb-emoji-picker__tab');
    if (!b) return;
    const cat = b.dataset.cat;
    setActiveTab(cat);
    render(cat);
    search.focus();
  });

  search.addEventListener('input', function(){
    const active = tabs.querySelector('.lb-emoji-picker__tab.is-active')?.dataset?.cat || 'recent';
    render(active);
  });

  document.addEventListener('click', function(e){
    if (picker.classList.contains('d-none')) return;
    if (picker.contains(e.target) || btn.contains(e.target)) return;
    closePicker();
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closePicker();
  });
});





/* Open Edit Order modal directly at the account fields */
(function () {
  const btn = document.getElementById('openEditLoginsBtn');
  if (!btn) return;
  btn.addEventListener('click', function () {
    window.setTimeout(function () {
      const field = document.querySelector('#edit_order_md [name="order_accounts-ign"]') ||
                    document.querySelector('#edit_order_md [name="ign"]');
      if (field) {
        try { field.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) {}
        field.focus();
      }
    }, 250);
  });
})();



/* Admin Edit Order: find Riot ID, but allow manual admin save */
(function () {
  const modal = document.getElementById('edit_order_md');
  if (!modal) return;

  function normalizeRiotId(value) {
    return String(value || '')
      .replace(/[＃♯]/g, '#')
      .replace(/\s*#\s*/g, '#')
      .replace(/[\u200B-\u200D\uFEFF]/g, '')
      .trim();
  }

  function validRiotId(value) {
    const v = normalizeRiotId(value);
    const idx = v.indexOf('#');
    if (idx <= 0 || idx !== v.lastIndexOf('#')) return false;
    const name = v.slice(0, idx).trim();
    const tag = v.slice(idx + 1).trim();
    return name.length >= 2 && name.length <= 32 && tag.length >= 2 && tag.length <= 16;
  }

  function currentServer() {
    const sel = modal.querySelector('[name="order_options-server"]');
    return sel ? String(sel.value || '').trim().toLowerCase() : '';
  }

  function orderId() {
    const el = modal.closest('form')?.querySelector('[name="order_id"]') || document.querySelector('form[action] [name="order_id"]');
    return el ? String(el.value || '').trim() : '';
  }

  function setPreview(tools, state, data) {
    const box = tools.querySelector('[data-admin-riot-preview]');
    const label = tools.querySelector('[data-admin-riot-label]');
    const name = tools.querySelector('[data-admin-riot-name]');
    const meta = tools.querySelector('[data-admin-riot-meta]');
    const icon = tools.querySelector('[data-admin-riot-icon]');
    const fallback = tools.querySelector('[data-admin-riot-icon-fallback]');
    if (!box || !label || !name || !meta) return;

    box.hidden = false;
    box.classList.remove('is-idle', 'is-loading', 'is-found', 'is-warning', 'is-error', 'is-manual');
    box.classList.add('is-' + state);

    if (icon) { icon.hidden = true; icon.removeAttribute('src'); }
    if (fallback) fallback.hidden = false;

    if (state === 'loading') {
      label.textContent = 'Checking Riot account...';
      name.textContent = data?.riot_id || 'Looking up account';
      meta.textContent = 'Checking selected order server' + (currentServer() ? ' (' + currentServer().toUpperCase() + ')' : '') + '.';
      return;
    }

    if (state === 'found') {
      const account = data?.account || data || {};
      label.textContent = 'Account found';
      name.textContent = account.riot_id || data?.riot_id || 'Riot account found';
      const srv = (account.server || currentServer() || '').toUpperCase();
      meta.textContent = account.summoner_level ? ('Level ' + account.summoner_level + (srv ? ' · ' + srv : '')) : (srv ? srv + ' account' : 'Verified account');
      if (icon && account.profile_icon_url) {
        icon.src = account.profile_icon_url;
        icon.hidden = false;
        if (fallback) fallback.hidden = true;
      }
      return;
    }

    if (state === 'warning') {
      const account = data?.account || {};
      label.textContent = data?.reason === 'wrong_server' ? 'Found on another server' : 'Not found on selected server';
      name.textContent = account.riot_id || data?.riot_id || 'Riot account not verified';
      meta.textContent = data?.message || ('No account found on ' + (currentServer() || 'selected server').toUpperCase() + '. You can still save manually as admin.');
      if (icon && account.profile_icon_url) {
        icon.src = account.profile_icon_url;
        icon.hidden = false;
        if (fallback) fallback.hidden = true;
      }
      return;
    }

    if (state === 'manual') {
      label.textContent = 'Manual entry';
      name.textContent = data?.riot_id || 'Riot ID will be saved manually';
      meta.textContent = 'Admin override: this field can be saved with the Edit Order button even when Riot API cannot verify it.';
      return;
    }

    label.textContent = 'Riot ID not found';
    name.textContent = data?.riot_id || 'Please check the Riot ID';
    meta.textContent = data?.message || 'Riot API could not verify this account. You can still save manually as admin.';
  }

  async function checkAccount(input, tools) {
    const riotId = normalizeRiotId(input.value);
    input.value = riotId;

    if (!riotId) {
      setPreview(tools, 'manual', { riot_id: 'Empty Riot ID' });
      return;
    }
    if (!validRiotId(riotId)) {
      setPreview(tools, 'error', { riot_id: riotId, message: 'Use Riot ID format: Name#TAG. Manual save is still possible for admins.' });
      return;
    }

    setPreview(tools, 'loading', { riot_id: riotId });

    const fd = new FormData();
    fd.append('action', 'admin_preview_riot_account');
    fd.append('order_id', orderId());
    fd.append('riot_id', riotId);
    const server = currentServer();
    if (server) fd.append('server', server);

    try {
      const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
      const json = await res.json().catch(() => null);
      if (json && json.ok && json.account) {
        input.value = normalizeRiotId(json.account.riot_id || riotId);
        setPreview(tools, 'found', json);
        return;
      }
      if (json && (json.reason === 'wrong_server' || json.reason === 'not_found_on_order_server')) {
        setPreview(tools, 'warning', Object.assign({ riot_id: riotId }, json));
        return;
      }
      setPreview(tools, 'error', { riot_id: riotId, message: (json && json.message) || 'Riot account not found. Manual save is still possible for admins.' });
    } catch (e) {
      setPreview(tools, 'error', { riot_id: riotId, message: 'Lookup failed. Manual save is still possible for admins.' });
    }
  }

  modal.addEventListener('click', function (e) {
    const checkBtn = e.target.closest('[data-admin-riot-check]');
    const manualBtn = e.target.closest('[data-admin-riot-manual]');
    if (!checkBtn && !manualBtn) return;

    const tools = (checkBtn || manualBtn).closest('[data-admin-riot-tools]');
    const group = tools ? tools.closest('.fv-row') : null;
    const input = group ? group.querySelector('[name="order_accounts-ign"]') : null;
    if (!tools || !input) return;

    e.preventDefault();
    if (manualBtn) {
      input.value = normalizeRiotId(input.value);
      setPreview(tools, 'manual', { riot_id: input.value || 'Manual Riot ID' });
      input.focus();
      return;
    }
    checkAccount(input, tools);
  });

  modal.addEventListener('input', function (e) {
    const input = e.target.closest('[name="order_accounts-ign"]');
    if (!input) return;
    const tools = input.closest('.fv-row')?.querySelector('[data-admin-riot-tools]');
    const box = tools?.querySelector('[data-admin-riot-preview]');
    if (box) box.hidden = true;
  });
})();


/* Copy: Riot ID / LoL username / password */
(function () {
  function toast(type, title, message) {
    if (typeof window.create_toast === 'function') {
      window.create_toast(type, title, message);
    }
  }

  async function copyToClipboard(text) {
    if (!text) return false;
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      }
    } catch (e) {}

    // Fallback
    try {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.setAttribute('readonly', '');
      ta.style.position = 'fixed';
      ta.style.top = '-1000px';
      ta.style.left = '-1000px';
      document.body.appendChild(ta);
      ta.select();
      const ok = document.execCommand('copy');
      document.body.removeChild(ta);
      return ok;
    } catch (e) {
      return false;
    }
  }

  document.addEventListener('click', async function (e) {
    const target = e.target.closest('.js-copy-login, .js-copyable');
    if (!target) return;

    const text = (target.getAttribute('data-copy') || '').trim();
    if (!text || target.hasAttribute('disabled')) return;

    e.preventDefault();
    const ok = await copyToClipboard(text);
    if (ok) toast('success', 'Copied', 'Copied to clipboard');
    else toast('danger', 'Copy failed', 'Could not copy to clipboard');
  });
})();


/* Add Booster: keep TomSelect dropdown inside the modal (under the control) */
(function () {
  const modalEl = document.getElementById('add_booster_md');
  if (!modalEl) return;

  function syncAddBoosterHidden() {
    const sel = modalEl.querySelector('#admin_add_booster_select');
    const hidden = modalEl.querySelector('#admin_add_booster_hidden_id');
    if (!sel || !hidden) return;
    let value = '';
    try {
      if (sel.tomselect) value = sel.tomselect.getValue();
    } catch (e) {}
    if (!value) value = sel.value || '';
    hidden.value = Array.isArray(value) ? (value[0] || '') : String(value || '');
  }

  function ensureDropdownParent() {
    const sel = modalEl.querySelector('select.js-select');
    if (!sel) return;

    const parent = sel.closest('.tom-select-custom') || modalEl;

    // If TomSelect is already initialized by HS, but dropdown ends up in the wrong place, re-init.
    if (sel.tomselect) {
      const ts = sel.tomselect;
      const dp = ts.settings && ts.settings.dropdownParent;
      const needsFix = (!dp || dp === document.body || (dp instanceof Element && !modalEl.contains(dp)));
      if (!needsFix) return;

      const currentValue = ts.getValue();
      try { ts.destroy(); } catch (e) {}

      if (window.TomSelect) {
        const inst = new window.TomSelect(sel, {
          placeholder: 'Search booster...',
          maxItems: 1,
          create: false,
          dropdownParent: parent,
          onChange: syncAddBoosterHidden
        });
        if (currentValue) inst.setValue(currentValue, true);
        syncAddBoosterHidden();
      }
      return;
    }

    // Not initialized yet: init safely if TomSelect exists.
    if (window.TomSelect) {
      new window.TomSelect(sel, {
        placeholder: 'Search booster...',
        maxItems: 1,
        create: false,
        dropdownParent: parent,
        onChange: syncAddBoosterHidden
      });
    }

    sel.addEventListener('change', syncAddBoosterHidden);
    syncAddBoosterHidden();
  }

  modalEl.addEventListener('shown.bs.modal', function () {
    window.setTimeout(ensureDropdownParent, 0);
  });

  modalEl.addEventListener('submit', function () {
    syncAddBoosterHidden();
  }, true);
})();

</script>

<script>
(function () {
  document.querySelectorAll('#edit_order_md .lb-edit-calc-price').forEach(function (button) {
    button.addEventListener('click', async function () {
      const form = button.closest('form');
      const priceInput = form?.querySelector('[name="orders-price"]');
      if (!form || !priceInput) return;

      const payload = new FormData(form);
      payload.set('action', 'admin_calc_edit_order_price');
      button.disabled = true;
      try {
        const response = await fetch("<?= AJAX_URL ?>", { method: 'POST', body: payload });
        const result = await response.json();
        if (!result || result.success !== true) {
          throw new Error(result?.message || 'Price calculation failed.');
        }
        priceInput.value = result.price;
        if (typeof create_toast === 'function') {
          create_toast('success', 'Calculated', 'The edited order price has been updated.');
        }
      } catch (error) {
        const message = error?.message || 'Price calculation failed.';
        if (typeof create_toast === 'function') create_toast('danger', 'Error', message);
        else alert(message);
      } finally {
        button.disabled = false;
      }
    });
  });
})();
</script>

<script>
// Progress Payment modal (dynamic fields based on boost form)
(function () {
  const md = document.getElementById('remove_booster_progress_payment_md');
  if (!md) return;

  const tiers = {
    0: 'Unranked', 1: 'Iron', 2: 'Bronze', 3: 'Silver', 4: 'Gold', 5: 'Platinum',
    6: 'Emerald', 7: 'Diamond', 8: 'Master', 9: 'Grandmaster', 10: 'Challenger'
  };
  const divisions = { 4: 'I', 3: 'II', 2: 'III', 1: 'IV' };

  // Division label for a given division count (mirrors lb_dynamic_division_label() in PHP),
  // used for dynamic (non-LoL/VAL/TFT) games whose rank config comes from the server.
  function genericDivisionLabel(value, count) {
    const v = parseInt(value, 10) || 0;
    if (count === 4) return ({ 1: 'IV', 2: 'III', 3: 'II', 4: 'I' })[v] || String(v);
    if (count === 3) return ({ 1: 'III', 2: 'II', 3: 'I' })[v] || String(v);
    if (count === 5) return ({ 1: '5', 2: '4', 3: '3', 4: '2', 5: '1' })[v] || String(v);
    return String(v);
  }

  // Build tier/division label maps + division-count lookup for a dynamic game's rank_config
  // (as returned by admin_get_progress_payment_context). Returns null when no config exists
  // (LoL/VAL/TFT keep using the hardcoded `tiers`/`divisions` maps above).
  function buildGenericRankMaps(rankCfg) {
    if (!rankCfg || !rankCfg.ranks) return null;
    const tierMap = {};
    Object.keys(rankCfg.ranks).forEach((k) => { tierMap[k] = rankCfg.ranks[k]; });
    const rankDivs = rankCfg.rank_divs || {};
    const divCountFor = (t) => {
      const raw = rankDivs[t] ?? rankDivs[String(t)];
      return (raw === undefined || raw === null) ? 4 : (parseInt(raw, 10) || 0);
    };
    const flatTiers = (rankCfg.flat_tiers || []).map(String);
    return { tierMap, divCountFor, flatTiers };
  }

  let ctx = null;
  let updateProgressNoteFn = null;

  function optHtml(options, selected) {
    return options.map(o => {
      const val = (typeof o === 'object') ? o.value : o;
      const label = (typeof o === 'object') ? o.label : o;
      const sel = (String(val) === String(selected)) ? 'selected' : '';
      return `<option value="${String(val).replace(/"/g,'&quot;')}" ${sel}>${label}</option>`;
    }).join('');
  }
  function stepperHtml(id, value) {
    const v = (typeof value === 'number' && Number.isFinite(value)) ? value : parseInt(value || 0, 10) || 0;
    return `
      <div class="rb-stepper" style="display:flex; align-items:center; gap:10px;">
        <button type="button" class="btn btn-sm btn-white" data-rb-step="-1" data-rb-target="${id}" style="min-width:38px;">−</button>
        <input type="number" class="form-control text-center" id="${id}" value="${v}" min="0" max="999" style="flex:1;">
        <button type="button" class="btn btn-sm btn-white" data-rb-step="1" data-rb-target="${id}" style="min-width:38px;">+</button>
      </div>
    `;
  }

  // Global stepper handler (negative allowed)
  document.addEventListener('click', function(ev){
    const b = ev.target.closest('[data-rb-step][data-rb-target]');
    if (!b) return;
    const id = b.getAttribute('data-rb-target');
    const step = parseFloat(b.getAttribute('data-rb-step') || '0');
    const el = document.getElementById(id);
    if (!el) return;
    const cur = parseFloat(el.value || '0');
    let next = cur + (Number.isFinite(step) ? step : 0);
    const min = parseFloat(el.getAttribute("min"));
    const max = parseFloat(el.getAttribute("max"));
    if (Number.isFinite(min) && next < min) next = min;
    if (Number.isFinite(max) && next > max) next = max;
    el.value = String(Math.round(next * 100) / 100);
  });

  function renderFields(context) {
    const wrap = document.getElementById('rb_dynamic_fields');
    if (!wrap) return;
    wrap.innerHTML = '';

    if (!context || !context.success) {
      wrap.innerHTML = `<div class="text-muted">Unable to load context.</div>`;
      return;
    }

    const formType = context.form_type;
    const o = context.options || {};

    const isRank = (formType === 'rank');
    const isWinLike = (formType === 'win' || formType === 'arena');
    const isGameCount = (formType === 'match' || formType === 'placement' || formType === 'normal' || formType === 'clash' || formType === 'pro-games' || parseInt(context.form_id || 0, 10) === 26);
    const isDuoPass = (formType === 'duo-pass' || parseInt(context.form_id || 0, 10) === 27);
    const isCoaching = (formType === 'coaching');
    const isLevelLike = (formType === 'mastery' || formType === 'level');

    if (isRank) {
      const isLolClassic = Boolean(context?.rank_config?.is_lol_classic)
        || [30, 31, 32].includes(parseInt(context.form_id || 0, 10))
        || ['lol_classic', 'lol-classic', 'league-of-legends-classic'].includes(String(context.game || '').toLowerCase());
      const genericMaps = buildGenericRankMaps(context.rank_config);
      const tierLabels = genericMaps ? genericMaps.tierMap : tiers;
      const tierOptions = Object.keys(tierLabels).map(k => ({ value: k, label: tierLabels[k] }));
      const lpOptions = (context.start_lp_options || []).map(v => ({ value: v, label: v }));
      const lpGainOptions = (context.lp_gain_options || []).map(v => ({ value: v, label: v }));

      // Division options depend on the (possibly per-tier) division count for dynamic games;
      // LoL/VAL keep their fixed 4-division select.
      const divCountForTier = (t) => genericMaps ? genericMaps.divCountFor(t) : 4;
      const divisionOptionsFor = (t) => {
        const count = divCountForTier(t);
        const opts = [];
        for (let v = 1; v <= count; v++) {
          opts.push({ value: v, label: genericMaps ? genericDivisionLabel(v, count) : (divisions[v] || String(v)) });
        }
        return opts;
      };
      const goalDivLabel = genericMaps
        ? genericDivisionLabel(o.end_division, divCountForTier(o.end_tier))
        : (divisions[o.end_division] || '');

      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label mb-1">Reached Tier</label>
            <select class="form-select" id="rb_reached_tier">${optHtml(tierOptions, o.start_tier)}</select>
          </div>
          <div class="col-6" id="rb_division_wrap">
            <label class="form-label mb-1">Reached Division</label>
            <select class="form-select" id="rb_reached_division">${optHtml(divisionOptionsFor(o.start_tier), o.start_division)}</select>
          </div>
          <div class="col-6" id="rb_lp_wrap">
            <label class="form-label mb-1">Reached LP (band)</label>
            <select class="form-select" id="rb_reached_lp">${optHtml(lpOptions, o.start_lp)}</select>
          </div>
          <div class="col-6" id="rb_lp_full_wrap" style="display:none;">
            <label class="form-label mb-1">Reached LP</label>
            <input type="number" class="form-control" id="rb_reached_lp_full" min="0" max="999999" value="${parseInt(o.start_lp_full ?? o.start_lp ?? 0, 10) || 0}">
          </div>
          <div class="col-6">
            <label class="form-label mb-1">LP Gain</label>
            <select class="form-select" id="rb_lp_gain">${optHtml(lpGainOptions, o.lp_gain)}</select>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Goal</label>
            <input type="text" class="form-control" value="${tierLabels[o.end_tier] || o.end_tier} ${goalDivLabel}" readonly style="background:#1f2430;color:#fff;border-color:rgba(255,255,255,0.08);">
          </div>
        </div>
      `;

      // Determine field visibility for a given reached tier:
      // - "flat" tiers (in rank_config.flat_tiers) have no division and no points input at all.
      // - tiers whose division count is 0 (Master+/open-ended, e.g. Apex Master) use a full-LP number input.
      // - everything else uses the normal division + LP-band selects.
      const fieldStateFor = (t) => {
        if (genericMaps) {
          const isFlat = genericMaps.flatTiers.includes(String(t));
          const divCount = genericMaps.divCountFor(t);
          if (isFlat) return 'flat';
          if (divCount <= 0) return 'open';
          return 'normal';
        }
        const isApexGame = String(context.game || '').toLowerCase() === 'apex-legends';
        if ((isApexGame && t >= 7) || (!isApexGame && (t > 7 || t === 0))) return 'open';
        return 'normal';
      };

      const applyFieldState = (t) => {
        const state = fieldStateFor(t);
        const divWrap = document.getElementById('rb_division_wrap');
        const lpWrap = document.getElementById('rb_lp_wrap');
        const lpFullWrap = document.getElementById('rb_lp_full_wrap');
        if (state === 'normal') {
          const sel = document.getElementById('rb_reached_division');
          if (sel) sel.innerHTML = optHtml(divisionOptionsFor(t), o.start_division);
          if (divWrap) divWrap.style.display = '';
          // LoL Classic has divisions, but no LP band or LP gain pricing.
          if (lpWrap) lpWrap.style.display = isLolClassic ? 'none' : '';
          if (lpFullWrap) lpFullWrap.style.display = 'none';
        } else if (state === 'open') {
          if (divWrap) divWrap.style.display = 'none';
          if (lpWrap) lpWrap.style.display = 'none';
          if (lpFullWrap) lpFullWrap.style.display = '';
        } else {
          // flat: no division, no LP of any kind for this tier.
          if (divWrap) divWrap.style.display = 'none';
          if (lpWrap) lpWrap.style.display = 'none';
          if (lpFullWrap) lpFullWrap.style.display = 'none';
        }
      };

      applyFieldState(parseInt(o.start_tier ?? 0, 10));

      if (isLolClassic) {
        const lpGainWrap = document.getElementById('rb_lp_gain')?.closest('.col-6');
        if (lpGainWrap) lpGainWrap.style.display = 'none';
      }

      const tierSel = document.getElementById('rb_reached_tier');
      if (tierSel) {
        tierSel.addEventListener('change', () => applyFieldState(parseInt(tierSel.value, 10)));
      }
      return;
    }

    if (isWinLike) {
      const isWinBoostForm = (parseInt(context.form_id || 0, 10) === 2);
      const label = isWinBoostForm ? 'Wins Completed (negative = Booster Fine)' : 'Wins Completed';
      const cur = parseInt(o.wins_completed ?? context.wins_completed ?? 0, 10) || 0;
      const totalWins = parseInt(context.wins_purchased ?? 0, 10);
      // Win Boost (form_id=2): allow negative values so the booster can be fined
      const stepperMin = isWinBoostForm ? '' : 'min="0"';
      const stepperMinAttr = isWinBoostForm ? -999 : 0;

      // Build stepper manually to allow negative min for win boost
      const stepperHtmlWin = `
        <div class="rb-stepper" style="display:flex; align-items:center; gap:10px;">
          <button type="button" class="btn btn-sm btn-white" data-rb-step="-1" data-rb-target="rb_wins_completed" style="min-width:38px;">−</button>
          <input type="number" class="form-control text-center" id="rb_wins_completed" value="${cur}" min="${stepperMinAttr}" max="${totalWins}" style="flex:1;">
          <button type="button" class="btn btn-sm btn-white" data-rb-step="1" data-rb-target="rb_wins_completed" style="min-width:38px;">+</button>
        </div>
      `;

      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label mb-1">${label}</label>
            ${stepperHtmlWin}
            <div class="form-text">Total wins purchased: ${totalWins}${isWinBoostForm ? ' · Negative value = price increases + booster fine' : ''}</div>
          </div>
        </div>
      `;
      return;
    }

    if (isGameCount) {
      let total = parseInt(context.matches_purchased ?? 0, 10);
      let label = 'Games Completed';
      if (formType === 'placement') label = 'Placement Matches Completed';
      if (formType === 'normal') label = 'Normal Games Completed';
      if (formType === 'clash') label = 'Clash Games Completed';
      if (formType === 'pro-games' || parseInt(context.form_id || 0, 10) === 26) label = 'Pro Games Done';

      const cur = parseInt(o.matches_completed ?? context.matches_completed ?? 0, 10) || 0;

      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label mb-1">${label}</label>
            ${stepperHtml('rb_matches_completed', cur).replace('max="999"', 'max="' + total + '"')}
            <div class="form-text">Total games purchased: ${total}</div>
          </div>
        </div>
      `;
      return;
    }

    if (isDuoPass) {
      const total = parseFloat(context.hours_purchased ?? o.hours ?? 0) || 0;
      const cur = parseFloat(context.duo_remaining_hours ?? o.hours ?? total) || total;
      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label mb-1">Remaining Time (hours)</label>
            <div class="rb-stepper" style="display:flex; align-items:center; gap:10px;">
              <button type="button" class="btn btn-sm btn-white" data-rb-step="-0.25" data-rb-target="rb_remaining_hours" style="min-width:38px;">−</button>
              <input type="number" class="form-control text-center" id="rb_remaining_hours" value="${Math.round(cur * 100) / 100}" min="0" max="${total}" step="0.25" style="flex:1;">
              <button type="button" class="btn btn-sm btn-white" data-rb-step="0.25" data-rb-target="rb_remaining_hours" style="min-width:38px;">+</button>
            </div>
            <div class="form-text">Booked time: ${total}h. Prefilled from timer sessions. New total = current price × remaining time / booked time.</div>
          </div>
        </div>
      `;
      return;
    }

    if (isCoaching) {
      const cur = parseInt(o.hours_completed ?? context.hours_completed ?? 0, 10) || 0;
      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label mb-1">Hours Completed</label>
            ${stepperHtml('rb_hours_completed', cur)}
            <div class="form-text">Total hours purchased: ${parseInt(context.hours_purchased ?? 0, 10)}</div>
          </div>
        </div>
      `;
      return;
    }

    if (isLevelLike) {
      const goal = context.goal_label || '';
      const cur = parseInt(o.reached_level ?? context.reached_level ?? o.start_tier ?? 0, 10) || 0;
      wrap.innerHTML = `
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label mb-1">Reached Level</label>
            ${stepperHtml('rb_reached_level', cur)}
            <div class="form-text">Goal: ${goal}</div>
          </div>
        </div>
      `;
      return;
    }

    wrap.innerHTML = `<div class="text-muted"><?= !empty($lb_admin_is_ranked_5s) ? 'Enter completed games and click Calculate price.' : 'Auto calculation is not available for this boost form yet.' ?></div>`;
  }



  async function fetchContext(orderId) {
    const fd = new FormData();
    fd.append('action', 'admin_get_progress_payment_context');
    fd.append('order_id', orderId);
    const res = await fetch("<?= AJAX_URL ?>", { method: 'POST', body: fd });
    return await res.json();
  }
  function buildProgressPayload(context) {
    const o = context.options || {};
    const formType = context.form_type;

    if (formType === 'rank') {
      const t = parseInt(document.getElementById('rb_reached_tier')?.value || o.start_tier || 0, 10);
      const d = parseInt(document.getElementById('rb_reached_division')?.value || o.start_division || 1, 10);
      const lp = document.getElementById('rb_reached_lp')?.value || o.start_lp || '0-20';
      const lpFull = parseInt(document.getElementById('rb_reached_lp_full')?.value || 0, 10);
      const lg = document.getElementById('rb_lp_gain')?.value || o.lp_gain;
      const isLolClassic = Boolean(context?.rank_config?.is_lol_classic)
        || [30, 31, 32].includes(parseInt(context.form_id || 0, 10))
        || ['lol_classic', 'lol-classic', 'league-of-legends-classic'].includes(String(context.game || '').toLowerCase());

      const genericMaps = buildGenericRankMaps(context.rank_config);
      let state = 'normal';
      if (genericMaps) {
        if (genericMaps.flatTiers.includes(String(t))) state = 'flat';
        else if (genericMaps.divCountFor(t) <= 0) state = 'open';
      } else if (String(context.game || '').toLowerCase() === 'apex-legends' && t >= 7) {
        state = 'open';
      }

      const payload = { start_tier: t };
      if (!isLolClassic) payload.lp_gain = lg;
      if (state === 'normal') {
        payload.start_division = d;
        if (!isLolClassic) payload.start_lp = lp;
      } else if (state === 'open') {
        payload.start_lp_full = lpFull;
      }
      return payload;
    }

    if (formType === 'win' || formType === 'arena') {
      return { wins_completed: parseInt(document.getElementById('rb_wins_completed')?.value ?? '0', 10) };
    }

    if (formType === 'match' || formType === 'placement' || formType === 'normal' || formType === 'clash' || formType === 'pro-games' || parseInt(context.form_id || 0, 10) === 26) {
      return { matches_completed: parseInt(document.getElementById('rb_matches_completed')?.value || 0, 10) };
    }

    if (formType === 'duo-pass' || parseInt(context.form_id || 0, 10) === 27) {
      return { remaining_hours: parseFloat(document.getElementById('rb_remaining_hours')?.value || 0) || 0 };
    }

    if (formType === 'mastery' || formType === 'level') {
      return { reached_level: parseInt(document.getElementById('rb_reached_level')?.value || 0, 10) };
    }

    if (formType === 'coaching') {
      return { hours_completed: parseInt(document.getElementById('rb_hours_completed')?.value || 0, 10) };
    }

    return {};
  }


  // Auto-fill progress note (e.g. "Wins 3 → 2 (-1)") when progress inputs change.
  function formatDelta(from, to) {
    const d = (to - from);
    return (d >= 0 ? '+' : '') + String(d);
  }

  function buildAutoNote(context, progressPayload) {
    const ft = context?.form_type || '';
    if (!progressPayload || typeof progressPayload !== 'object') return '';

    // Helpers for rank label formatting
    const genericMaps = buildGenericRankMaps(context?.rank_config);
    const tierName = (t) => {
      const n = parseInt(t ?? 0, 10) || 0;
      if (genericMaps) return genericMaps.tierMap[n] || genericMaps.tierMap[String(n)] || String(n || '');
      // Best-effort tier names (common LoL tiers). If your DB uses different numbering, it will still
      // fall back to the numeric value.
      const map = {
        1: 'Iron',
        2: 'Bronze',
        3: 'Silver',
        4: 'Gold',
        5: 'Platinum',
        6: 'Emerald',
        7: 'Diamond',
        8: 'Master',
        9: 'Grandmaster',
        10: 'Challenger'
      };
      return map[n] || String(n || '');
    };
    const divRoman = (d, t) => {
      const n = parseInt(d ?? 0, 10) || 0;
      if (genericMaps) return genericDivisionLabel(n, genericMaps.divCountFor(t));
      const map = { 1: 'IV', 2: 'III', 3: 'II', 4: 'I' };
      return map[n] || String(n || '');
    };
    const rankLabel = (p) => {
      if (!p || typeof p !== 'object') return '';
      const t = parseInt(p.start_tier ?? 0, 10) || 0;
      const isOpenEnded = genericMaps
        ? (!genericMaps.flatTiers.includes(String(t)) && genericMaps.divCountFor(t) <= 0)
        : (String(context?.game || '').toLowerCase() === 'apex-legends' && t >= 7);
      if (isOpenEnded) {
        const lpFull = parseInt(p.start_lp_full ?? 0, 10) || 0;
        return `${tierName(t)} ${lpFull} LP`.trim();
      }
      const d = parseInt(p.start_division ?? 0, 10) || 0;
      const lpBand = (p.start_lp ?? '').toString();
      const base = `${tierName(t)} ${divRoman(d, t)}`.trim();
      const isLolClassic = Boolean(context?.rank_config?.is_lol_classic)
        || [30, 31, 32].includes(parseInt(context?.form_id || 0, 10))
        || ['lol_classic', 'lol-classic', 'league-of-legends-classic'].includes(String(context?.game || '').toLowerCase());
      return (!isLolClassic && lpBand) ? `${base} (${lpBand} LP)` : base;
    };

    if (ft === 'win' || ft === 'arena') {
      // Baseline: last segment if present, otherwise the order's current stored completed value.
      const from = parseInt((context?.wins_from ?? context?.wins_completed) ?? 0, 10) || 0;
      const to = parseInt(progressPayload.wins_completed ?? 0, 10);
      const isWinBoostFine = (parseInt(context?.form_id || 0, 10) === 2 && to < 0);
      const fineSuffix = isWinBoostFine ? ' ⚠ Booster Fine' : '';
      return `Wins ${from} → ${to} (${formatDelta(from, to)})${fineSuffix}`;
    }
    if (ft === 'match' || ft === 'placement' || ft === 'normal' || ft === 'clash' || ft === 'pro-games' || parseInt(context?.form_id || 0, 10) === 26) {
      const from = parseInt((context?.matches_from ?? context?.matches_completed) ?? 0, 10) || 0;
      const to = parseInt(progressPayload.matches_completed ?? 0, 10) || 0;
      return `Games ${from} → ${to} (${formatDelta(from, to)})`;
    }
    if (ft === 'duo-pass' || parseInt(context?.form_id || 0, 10) === 27) {
      const total = parseFloat(context?.hours_purchased ?? 0) || 0;
      const remaining = parseFloat(progressPayload.remaining_hours ?? total) || 0;
      const completed = Math.max(0, total - remaining);
      return `Duo Pass ${completed.toFixed(2)}h done, ${remaining.toFixed(2)}h remaining`;
    }
    if (ft === 'coaching') {
      const from = parseInt((context?.hours_from ?? context?.hours_completed) ?? 0, 10) || 0;
      const to = parseInt(progressPayload.hours_completed ?? 0, 10) || 0;
      return `Hours ${from} → ${to} (${formatDelta(from, to)})`;
    }
    if (ft === 'mastery' || ft === 'level') {
      const from = parseInt((context?.level_from ?? context?.reached_level) ?? 0, 10) || 0;
      const to = parseInt(progressPayload.reached_level ?? 0, 10) || 0;
      return `Level ${from} → ${to} (${formatDelta(from, to)})`;
    }
    if (ft === 'rank') {
      // Baseline: last segment progress_to if available, otherwise order start fields.
      const pf = context?.progress_from || {};
      const base = (pf && Object.keys(pf).length)
        ? pf
        : (context?.options || context?.order_options || {});

      const fromLbl = rankLabel({
        start_tier: base.start_tier,
        start_division: base.start_division,
        start_lp: base.start_lp,
        start_lp_full: base.start_lp_full
      });
      const toLbl = rankLabel({
        start_tier: progressPayload.start_tier,
        start_division: progressPayload.start_division,
        start_lp: progressPayload.start_lp,
        start_lp_full: progressPayload.start_lp_full
      });
      if (!fromLbl && !toLbl) return '';
      return `Rank ${fromLbl} → ${toLbl}`.trim();
    }
    return '';
  }

  function attachAutoProgressNote(context) {
  const pn = document.getElementById('rb_progress_note');
  if (!pn) return;

  // Track whether staff typed a custom note.
  pn.addEventListener('input', function () {
    pn.dataset.userEdited = '1';
    pn.dataset.auto = '0';
  });

  const update = function (force = false) {
    if (!ctx || !ctx.success) return;

    // Don't overwrite a custom note unless forced AND it was previously auto-generated (or empty).
    const hasCustom = (pn.dataset.userEdited === '1' && pn.dataset.auto !== '1' && pn.value.trim() !== '');
    if (hasCustom && !force) return;
    if (hasCustom && force) return; // keep truly custom notes even on Calculate

    const payload = buildProgressPayload(ctx);
    const note = buildAutoNote(ctx, payload);
    if (!note) return;

    // Fill if empty, or if it was auto-filled before, or if forced.
    if (force || pn.value.trim() === '' || pn.dataset.auto === '1') {
      pn.value = note;
      pn.dataset.auto = '1';
      pn.dataset.userEdited = '0';
    }
  };

  // Watch all relevant progress inputs (created dynamically)
  const watchIds = [
    'rb_wins_completed',
    'rb_matches_completed',
    'rb_hours_completed',
    'rb_remaining_hours',
    'rb_reached_level',
    'rb_reached_tier',
    'rb_reached_division',
    'rb_reached_lp',
    'rb_reached_lp_full',
    'rb_lp_gain'
  ];

  watchIds.forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', () => update(false));
    el.addEventListener('change', () => update(false));
  });

  // Expose updater so Calculate can refresh the note
  updateProgressNoteFn = update;

  // Initial fill
  update(false);
}

  // ── Riot API Card in Remove Booster Modal ─────────────────────────────
  let rbRiotData = null; // stores last fetched riot progress for "Use this data"

  const rbRankTierIds = {
    IRON:1, BRONZE:2, SILVER:3, GOLD:4,
    PLATINUM:5, EMERALD:6, DIAMOND:7,
    MASTER:8, GRANDMASTER:9, CHALLENGER:10
  };

  function rbRiotShowLoading() {
    const rankName  = document.getElementById('rb_riot_rank_name');
    const statsRow  = document.getElementById('rb_riot_stats_row');
    const wrWrap    = document.getElementById('rb_riot_wr_bar_wrap');
    if (rankName)  rankName.textContent = 'Loading…';
    if (statsRow)  statsRow.style.display = 'none';
    if (wrWrap)    wrWrap.style.display   = 'none';
  }

  function rbRiotApplyCard(p) {
    const rankName  = document.getElementById('rb_riot_rank_name');
    const statsRow  = document.getElementById('rb_riot_stats_row');
    const wrWrap    = document.getElementById('rb_riot_wr_bar_wrap');

    // Rank name in header
    if (rankName) {
      const tier = (p.current_tier || '').toUpperCase().trim();
      if (tier) {
        const t = tier.charAt(0) + tier.slice(1).toLowerCase();
        const d = (p.current_division || '').trim();
        const lp = p.current_lp != null ? ' · ' + p.current_lp + ' LP' : '';
        rankName.textContent = t + (d ? ' ' + d : '') + lp;
      } else {
        rankName.textContent = 'Unranked';
      }
    }

    // Stats
    const w = parseInt(p.wins ?? 0, 10) || 0;
    const l = parseInt(p.losses ?? 0, 10) || 0;
    const total = w + l;
    const wrPct = total > 0 ? (w / total) * 100 : 0;

    const winsEl   = document.getElementById('rb_riot_wins');
    const lossesEl = document.getElementById('rb_riot_losses');
    const wrEl     = document.getElementById('rb_riot_wr');
    const syncEl   = document.getElementById('rb_riot_sync');
    if (winsEl)   winsEl.textContent   = String(w);
    if (lossesEl) lossesEl.textContent = String(l);
    if (wrEl)     wrEl.textContent     = total > 0 ? wrPct.toFixed(1) + '%' : '—';
    if (syncEl && p.last_sync_at) {
      // Show just time portion if same day, else short date
      const raw = p.last_sync_at.toString().replace(' ', 'T');
      const d = new Date(raw);
      syncEl.textContent = isNaN(d.getTime()) ? p.last_sync_at : d.toLocaleTimeString(undefined, {hour:'2-digit', minute:'2-digit'});
    }
    if (statsRow) statsRow.style.display = '';

    // WR bar
    const wrBar = document.getElementById('rb_riot_wr_bar');
    if (wrBar && wrWrap) {
      wrBar.style.width = wrPct.toFixed(1) + '%';
      wrBar.style.background = (total > 0 && wrPct >= 60)
        ? 'linear-gradient(90deg, rgba(34,197,94,.65), rgba(74,222,128,.90))'
        : 'rgba(255,255,255,0.18)';
      wrWrap.style.display = '';
    }
  }

  async function rbRiotRefresh(orderId) {
    const refreshBtn = document.getElementById('rb_riot_refresh_btn');
    const useBtn     = document.getElementById('rb_riot_use_btn');
    const icon       = document.getElementById('rb_riot_refresh_icon');
    if (refreshBtn) refreshBtn.disabled = true;
    if (useBtn)     useBtn.disabled = true;
    if (icon)       icon.classList.add('fa-spin');
    rbRiotShowLoading();

    try {
      const fd = new FormData();
      fd.append('action', 'admin_refresh_order_progress');
      fd.append('order_id', orderId);
      const res  = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
      const json = await res.json();

      if (json && json.orderProgress) {
        rbRiotData = json.orderProgress;
        rbRiotApplyCard(rbRiotData);
        if (useBtn) useBtn.disabled = false;
      } else {
        const loading = document.getElementById('rb_riot_loading');
        if (loading) { loading.style.display = ''; loading.innerHTML = '<i class="fa-duotone fa-circle-exclamation me-1"></i> Could not load Riot data.'; }
      }
    } catch(e) {
      const loading = document.getElementById('rb_riot_loading');
      if (loading) { loading.style.display = ''; loading.innerHTML = '<i class="fa-duotone fa-circle-exclamation me-1"></i> Error loading Riot data.'; }
    } finally {
      if (refreshBtn) refreshBtn.disabled = false;
      if (icon)       icon.classList.remove('fa-spin');
    }
  }

  function rbRiotUseData() {
    if (!rbRiotData || !ctx || !ctx.success) return;
    const ft = ctx.form_type;
    const ctxFormId = parseInt(ctx.form_id || 0, 10);

    const tierStrToId = {
      IRON:1, BRONZE:2, SILVER:3, GOLD:4,
      PLATINUM:5, EMERALD:6, DIAMOND:7,
      MASTER:8, GRANDMASTER:9, CHALLENGER:10
    };
    const divStrToId = { I:4, II:3, III:2, IV:1 };

    const riotW = parseInt(rbRiotData.wins ?? 0, 10) || 0;
    const riotL = parseInt(rbRiotData.losses ?? 0, 10) || 0;

    // ── Form ID 2: Win Boost ─────────────────────────────────────────
    // Target = remaining net wins. Each win -1, each loss +1.
    // wins_completed = wins - losses (net). Can be negative = booster fine.
    if (ctxFormId === 2) {
      const netWins = riotW - riotL;
      const winsEl = document.getElementById('rb_wins_completed');
      if (winsEl) {
        winsEl.value = String(netWins);
        winsEl.dispatchEvent(new Event('input'));
      }
      const label = netWins < 0 ? 'Net wins (' + riotW + 'W − ' + riotL + 'L = ' + netWins + ') → Booster Fine' : 'Net wins (' + riotW + 'W − ' + riotL + 'L = ' + netWins + ') applied.';
      if (typeof create_toast === 'function') create_toast(netWins < 0 ? 'warning' : 'success', 'Applied', label);
      return;
    }

    // ── Win / Arena (non-form-id-2) ──────────────────────────────────
    if (ft === 'win' || ft === 'arena') {
      const winsEl = document.getElementById('rb_wins_completed');
      if (winsEl) {
        winsEl.value = String(riotW);
        winsEl.dispatchEvent(new Event('input'));
      }
      if (typeof create_toast === 'function') create_toast('success', 'Applied', 'Riot wins (' + riotW + ') applied to Wins Completed.');
      return;
    }

    // ── Form ID 26: Pro Games ────────────────────────────────────────
    // Target = total games (W + L).
    if (ctxFormId === 26 || ft === 'pro-games') {
      const matchEl = document.getElementById('rb_matches_completed');
      if (matchEl) {
        const played = riotW + riotL;
        matchEl.value = String(played);
        matchEl.dispatchEvent(new Event('input'));
      }
      if (typeof create_toast === 'function') create_toast('success', 'Applied', 'Riot games (' + (riotW + riotL) + ') applied to Games Completed.');
      return;
    }

    // ── Rank Boost ───────────────────────────────────────────────────
    if (ft === 'rank') {
      const tierRaw  = (rbRiotData.current_tier || '').toUpperCase().trim();
      const divRaw   = (rbRiotData.current_division || '').toUpperCase().trim();
      const lpRaw    = rbRiotData.current_lp;
      const tierId   = tierStrToId[tierRaw] ?? 4;
      const divId    = divStrToId[divRaw] ?? 1;

      const tierSel = document.getElementById('rb_reached_tier');
      const divSel  = document.getElementById('rb_reached_division');
      const lpSel   = document.getElementById('rb_reached_lp');
      const lpFull  = document.getElementById('rb_reached_lp_full');

      if (tierSel) { tierSel.value = String(tierId); tierSel.dispatchEvent(new Event('change')); }
      setTimeout(() => {
        if (divSel)  { divSel.value  = String(divId);  divSel.dispatchEvent(new Event('change')); }
        const isApex = (tierId > 7 || tierId === 0);
        if (isApex && lpFull) {
          lpFull.value = String(parseInt(lpRaw ?? 0, 10) || 0);
          lpFull.dispatchEvent(new Event('input'));
        } else if (lpSel && lpRaw != null) {
          const lp = parseInt(lpRaw, 10) || 0;
          let bestOpt = null;
          Array.from(lpSel.options).forEach(opt => {
            const parts = opt.value.split('-').map(Number);
            if (parts.length === 2 && lp >= parts[0] && lp < parts[1]) bestOpt = opt.value;
          });
          if (bestOpt) lpSel.value = bestOpt;
          lpSel.dispatchEvent(new Event('change'));
        }
      }, 50);

      if (typeof create_toast === 'function') create_toast('success', 'Applied', 'Riot rank data applied to auto-calc fields.');
      return;
    }

    // ── Other game-count forms ───────────────────────────────────────
    if (ft === 'match' || ft === 'placement' || ft === 'normal' || ft === 'clash') {
      const matchEl = document.getElementById('rb_matches_completed');
      if (matchEl) {
        const played = riotW + riotL;
        matchEl.value = String(played);
        matchEl.dispatchEvent(new Event('input'));
      }
      if (typeof create_toast === 'function') create_toast('success', 'Applied', 'Riot game count (' + (riotW + riotL) + ') applied.');
      return;
    }

    if (typeof create_toast === 'function') create_toast('warning', 'Note', 'Auto-apply not available for this boost type. Please set manually.');
  }

  // Riot card: Refresh button
  const rbRiotRefreshBtn = document.getElementById('rb_riot_refresh_btn');
  if (rbRiotRefreshBtn) {
    rbRiotRefreshBtn.addEventListener('click', function () {
      const orderId = document.getElementById('rb_order_id')?.value || '<?= (int)$data['id'] ?>';
      rbRiotRefresh(orderId);
    });
  }

  // Riot card: Use this data button — apply fields + trigger Calculate
  const rbRiotUseBtn = document.getElementById('rb_riot_use_btn');
  if (rbRiotUseBtn) {
    rbRiotUseBtn.addEventListener('click', async function () {
      rbRiotUseData();
      // Small delay so DOM fields are set, then auto-calculate price
      setTimeout(() => {
        const calcBtn = document.getElementById('rb_calc_price_btn');
        if (calcBtn && !calcBtn.disabled) {
          calcBtn.click();
        }
      }, 120);
    });
  }

  function rbFormatCentsForInput(cents) {
    const n = parseInt(cents, 10);
    if (!Number.isFinite(n)) return '';
    return (Math.max(0, n) / 100).toFixed(2);
  }

  function rbNormalizeCalculatedPriceDisplay(displayValue, centsValue) {
    let cents = parseInt(centsValue, 10);
    const currentCents = ctx && ctx.current_total_cents ? parseInt(ctx.current_total_cents, 10) : 0;

    // Backend returns cents. If a legacy calculation accidentally came back 100x too large,
    // normalize it before it reaches the editable field.
    if (Number.isFinite(cents) && currentCents > 0 && cents > currentCents * 20) {
      cents = Math.round(cents / 100);
    }
    if (Number.isFinite(cents) && cents >= 0) {
      return rbFormatCentsForInput(cents);
    }

    const raw = String(displayValue || '').trim();
    const num = parseFloat(raw.replace(/[^\d,.-]/g, '').replace(',', '.'));
    if (Number.isFinite(num) && currentCents > 0 && Math.round(num * 100) > currentCents * 20) {
      return (num / 100).toFixed(2);
    }
    return raw;
  }

  md.addEventListener('show.bs.modal', async function (event) {
    const btn = event.relatedTarget;
    const orderId = btn?.getAttribute('data-order-id') || '<?= (int)$data['id'] ?>';
    const currentPrice = btn?.getAttribute('data-current-price') || '';

        try { md.dataset.rbCurrentPrice = currentPrice || ''; } catch(e) {}

    const oid = document.getElementById('rb_order_id');
    const np  = document.getElementById('rb_new_total_price');
    const pn  = document.getElementById('rb_progress_note');
    const ph  = document.getElementById('rb_progress_to');
    const pm  = document.getElementById('rb_calc_meta');

    if (oid) oid.value = orderId;
    if (np) np.value = currentPrice;
    const mo = document.getElementById('rb_manual_price_override');
    if (mo) mo.value = '0';
    if (pn) pn.value = '';
    if (ph) ph.value = '';
    if (pm) { pm.textContent = ''; pm.style.display = 'none'; }

    // Reset Riot card state
    rbRiotData = null;
    const useBtn = document.getElementById('rb_riot_use_btn');
    if (useBtn) useBtn.disabled = true;
    rbRiotShowLoading();

    try {
      ctx = await fetchContext(orderId);
      renderFields(ctx);
      attachAutoProgressNote(ctx);
      try { if (ctx && typeof ctx.cut_percent !== 'undefined') md.dataset.rbCutPercent = String(ctx.cut_percent); } catch(e) {}
      try {
        if (ctx && ctx.current_total_cents && np && document.getElementById('rb_manual_price_override')?.value !== '1') {
          const normalizedCurrent = rbFormatCentsForInput(ctx.current_total_cents);
          np.value = normalizedCurrent;
          md.dataset.rbCurrentPrice = normalizedCurrent;
        }
      } catch(e) {}
    } catch (e) {
      ctx = null;
      renderFields({ success: false });
    }

    // Auto-load Riot progress into the card
    const riotCard = document.getElementById('rb_riot_card');
    if (riotCard) {
      rbRiotRefresh(orderId);
    }
  });

  const calcBtn = document.getElementById('rb_calc_price_btn');
  if (calcBtn) {
    calcBtn.addEventListener('click', async function () {
      const isRanked5s = <?= !empty($lb_admin_is_ranked_5s) ? 'true' : 'false' ?>;
      const orderId = document.getElementById('rb_order_id')?.value;

      if (isRanked5s) {
        const totalGamesEl = document.getElementById('rb_r5s_total_games');
        const doneGamesEl = document.getElementById('rb_r5s_games_completed');
        const newPriceEl = document.getElementById('rb_new_total_price');
        const hiddenEl = document.getElementById('rb_progress_to');
        const manualOverrideEl = document.getElementById('rb_manual_price_override');
        const metaEl = document.getElementById('rb_calc_meta');

        const totalGames = Math.max(1, parseInt(totalGamesEl?.value || '1', 10));
        let doneGames = Math.max(0, parseInt(doneGamesEl?.value || '0', 10));
        doneGames = Math.min(doneGames, totalGames);

        const oldPrice = parseFloat(String(<?= json_encode(util_format_price_input($data['price'])) ?>).replace(',', '.')) || 0;
        const remainingGames = Math.max(0, totalGames - doneGames);
        const newPrice = oldPrice * (remainingGames / totalGames);
        const fixed = newPrice.toFixed(2);

        if (newPriceEl) newPriceEl.value = fixed;
        if (manualOverrideEl) manualOverrideEl.value = '0';

        const payload = {
          form_id: <?= 29 ?>,
          matches_completed: doneGames,
          games_completed: doneGames,
          total_games: totalGames,
          remaining_games: remainingGames
        };
        if (hiddenEl) hiddenEl.value = JSON.stringify(payload);

        if (metaEl) {
          metaEl.textContent = 'Ranked 5s: ' + doneGames + '/' + totalGames + ' games completed';
          metaEl.style.display = 'block';
        }

        if (typeof updateProgressNoteFn === 'function') {
          updateProgressNoteFn(true);
        }
        if (typeof create_toast === 'function') {
          create_toast('success', 'Calculated', 'Ranked 5s remaining total price has been filled in.');
        }
        return;
      }

      if (!ctx || !ctx.success) {
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Context not loaded.');
        return;
      }

      const hiddenEl = document.getElementById('rb_progress_to');
      const metaEl = document.getElementById('rb_calc_meta');
      const newPriceEl = document.getElementById('rb_new_total_price');

      const payload = buildProgressPayload(ctx);
      const payloadJson = JSON.stringify(payload);

      const fd = new FormData();
      fd.append('action', 'admin_calc_progress_payment_price');
      fd.append('order_id', orderId);
      fd.append('progress_to', payloadJson);

      calcBtn.disabled = true;
      let data = null;
      try {
        const res = await fetch("<?= AJAX_URL ?>", { method: 'POST', body: fd });
        data = await res.json();
      } catch (e) {
        // ignore
      }
      calcBtn.disabled = false;

      if (!data || !data.success) {
        const msg = data?.sendToast?.message || data?.message || 'Failed to calculate.';
        if (typeof create_toast === 'function') {
          create_toast('danger', 'Error', msg);
        } else {
          alert(msg);
        }
        return;
      }

      if (hiddenEl) hiddenEl.value = payloadJson;
      if (newPriceEl) newPriceEl.value = rbNormalizeCalculatedPriceDisplay(data.remaining_total_display, data.new_total_cents);
      const manualOverrideEl = document.getElementById('rb_manual_price_override');
      if (manualOverrideEl) manualOverrideEl.value = '0';

      if (metaEl) {
        const parts = [];
        if (data.discount_code) parts.push('Discount: ' + data.discount_code);
        if (typeof data.discount_percent === 'number') parts.push('Discount rate: ' + data.discount_percent + '%');
        // Coins are ignored for Progress Payment calculations in this workflow.
        metaEl.textContent = parts.length ? parts.join(' • ') : '';
        metaEl.style.display = parts.length ? 'block' : 'none';
      }

      if (typeof updateProgressNoteFn === 'function') {
        updateProgressNoteFn(true);
      }

if (typeof create_toast === 'function') {
        create_toast('success', 'Calculated', 'New total price has been filled in.');
      }
    });
  }



  // Mark the price as a manual admin override as soon as the field is edited by hand.
  const rbManualPriceEl = document.getElementById('rb_new_total_price');
  if (rbManualPriceEl) {
    rbManualPriceEl.addEventListener('input', function () {
      const manualOverrideEl = document.getElementById('rb_manual_price_override');
      if (manualOverrideEl) manualOverrideEl.value = '1';
      const metaEl = document.getElementById('rb_calc_meta');
      if (metaEl) {
        metaEl.textContent = 'Manual price override';
        metaEl.style.display = 'block';
      }
    });
  }
  // Ensure progress_to is sent even if staff forgets to click "Calculate":
  // On submit, we will serialize current selections into progress_to (if empty).
  try {
    const formEl = md.querySelector('form.ajax-form');
    if (formEl) {
      formEl.addEventListener('submit', function () {
        const hiddenEl = document.getElementById('rb_progress_to');
        if (hiddenEl && (!hiddenEl.value || hiddenEl.value.trim() === '') && ctx && ctx.success) {
          const payload = buildProgressPayload(ctx);
          hiddenEl.value = JSON.stringify(payload);
        }
      });
    }
  } catch (e) {
    // ignore
  }

})();
</script>

<script>
/**
 * Remove Booster: Confirmation overlay (always on top)
 * Fixes cases where a confirm modal can appear behind Bootstrap modals / backdrops.
 */
(function () {
  const OVERLAY_ID = 'rbRemoveConfirmOverlay';

  function ensureOverlay() {
    let ov = document.getElementById(OVERLAY_ID);
    if (ov) return ov;

    ov = document.createElement('div');
    ov.id = OVERLAY_ID;
    ov.style.cssText = [
      'position:fixed',
      'inset:0',
      'display:none',
      'align-items:center',
      'justify-content:center',
      'background:rgba(0,0,0,0.55)',
      'z-index:99999'
    ].join(';');

    ov.innerHTML = `
      <div style="width:min(520px, calc(100% - 32px)); background:#1f1f1f; border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:18px 18px 14px; box-shadow:0 24px 60px rgba(0,0,0,0.55);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;">
          <div style="font-weight:700; font-size:16px; color:#fff;">Confirm Remove Booster</div>
          <button type="button" data-rb-close style="background:transparent;border:0;color:rgba(255,255,255,0.65);font-size:20px;line-height:1;cursor:pointer;">×</button>
        </div>

        <div style="font-size:13px; color:rgba(255,255,255,0.75); margin-bottom:12px;">
          Please review the values below before removing the booster.
        </div>

        <div style="border-radius:12px; background:rgba(255,255,255,0.04); padding:12px 12px; margin-bottom:14px;">
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0;">
            <div style="color:rgba(255,255,255,0.7);">Current Order Price</div>
            <div style="color:#fff; font-weight:600;" data-rb-cur>-</div>
          </div>
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0;">
            <div style="color:rgba(255,255,255,0.7);">New Order Price</div>
            <div style="color:#fff; font-weight:600;" data-rb-new>-</div>
          </div>
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0; border-top:1px solid rgba(255,255,255,0.08); margin-top:6px;">
            <div style="color:rgba(255,255,255,0.7);">Booster Payment</div>
            <div style="color:#fff; font-weight:700;" data-rb-pay>-</div>
          </div>
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0;">
            <div style="color:rgba(255,255,255,0.7);">Price Source</div>
            <div style="color:#fff; font-weight:600;" data-rb-source>-</div>
          </div>
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0;">
            <div style="color:rgba(255,255,255,0.7);">Progress Note</div>
            <div style="color:#fff; font-weight:600; text-align:right; max-width:260px;" data-rb-note>-</div>
          </div>
          <div style="display:flex; justify-content:space-between; gap:10px; padding:6px 0;">
            <div style="color:rgba(255,255,255,0.7);">Order Change</div>
            <div style="color:#fff; font-weight:600; text-align:right;">Booster removed, status set to PAID</div>
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn btn-white btn-sm" data-rb-cancel>Cancel</button>
          <button type="button" class="btn btn-danger btn-sm" data-rb-confirm>Confirm & Remove</button>
        </div>
      </div>
    `;

    ov.addEventListener('click', (e) => {
      if (e.target === ov) hideOverlay();
    });
    ov.querySelector('[data-rb-close]')?.addEventListener('click', hideOverlay);
    ov.querySelector('[data-rb-cancel]')?.addEventListener('click', hideOverlay);

    document.body.appendChild(ov);
    return ov;
  }

  function showOverlay(values, onConfirm) {
    const ov = ensureOverlay();
    ov.querySelector('[data-rb-cur]').textContent = values.current ?? '-';
    ov.querySelector('[data-rb-new]').textContent = values.new ?? '-';
    ov.querySelector('[data-rb-pay]').textContent = values.payment ?? '-';
    ov.querySelector('[data-rb-source]').textContent = values.source ?? '-';
    ov.querySelector('[data-rb-note]').textContent = values.note ?? '-';

    const confirmBtn = ov.querySelector('[data-rb-confirm]');
    const handler = () => {
      confirmBtn.removeEventListener('click', handler);
      hideOverlay();
      onConfirm();
    };
    confirmBtn.addEventListener('click', handler);

    ov.style.display = 'flex';
    // Keep page from scrolling behind overlay
    document.documentElement.style.overflow = 'hidden';
  }

  function hideOverlay() {
    const ov = document.getElementById(OVERLAY_ID);
    if (!ov) return;
    ov.style.display = 'none';
    document.documentElement.style.overflow = '';
  }

  function parseMoneyToFloat(v) {
    if (typeof v !== 'string') return NaN;
    // "9.30" or "9,30" or "€ 9.30"
    const s = v.replace(/[^\d,.\-]/g, '').replace(',', '.');
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : NaN;
  }

  function fmtEUR(n) {
    if (!Number.isFinite(n)) return '-';
    try {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(n);
    } catch {
      return n.toFixed(2) + ' €';
    }
  }

  // Gate to avoid infinite recursion when we re-trigger submit
  let confirmedOnce = false;

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('#remove_booster_progress_payment_md button[type="submit"]');
    if (!btn) return;

    if (confirmedOnce) return; // allow through
    const md = document.getElementById('remove_booster_progress_payment_md');
    const form = md?.querySelector('form.ajax-form');
    if (!form) return;

    // Stop existing ajax listeners from firing
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    // Values
    const newPriceEl = document.getElementById('rb_new_total_price');
    const newPriceStr = (newPriceEl?.value || '').trim();

    // We stored current price at open by setting rb_new_total_price = current.
    // If user already edited, we try to read original from dataset if present.
    const curPriceStr =
      (md?.dataset?.rbCurrentPrice || '').trim() ||
      (btn?.dataset?.rbCurrentPrice || '').trim() ||
      (form?.dataset?.rbCurrentPrice || '').trim() ||
      ''; // fallback below

    const curFloat = parseMoneyToFloat(curPriceStr || newPriceStr); // fallback to newPrice if unknown
    const newFloat = parseMoneyToFloat(newPriceStr);

    // Try to compute payout if we can read cut %
    let cut = NaN;
    if (md?.dataset?.rbCutPercent) cut = parseFloat(md.dataset.rbCutPercent);
    if (!Number.isFinite(cut)) {
      const cutEl = md?.querySelector('[data-rb-cut-percent]');
      if (cutEl) cut = parseFloat(cutEl.getAttribute('data-rb-cut-percent'));
    }

    let payStr = 'Server-calculated';
    if (Number.isFinite(curFloat) && Number.isFinite(newFloat) && Number.isFinite(cut)) {
      const segment = curFloat - newFloat;
      const teamDivisor = <?= !empty($lb_admin_is_multi_booster) ? max(1, (int)$lb_admin_ranked_5s_required_boosters) : 1 ?>;
      const pay = (segment * (cut / 100)) / teamDivisor;
      payStr = fmtEUR(pay) + ' (pre-check)';
    }

    const manualOverride = document.getElementById('rb_manual_price_override')?.value === '1';
    const noteStr = (document.getElementById('rb_progress_note')?.value || '').trim();

    showOverlay(
      {
        current: (curPriceStr ? curPriceStr : (Number.isFinite(curFloat) ? fmtEUR(curFloat) : '-')),
        new: (newPriceStr ? newPriceStr : (Number.isFinite(newFloat) ? fmtEUR(newFloat) : '-')),
        payment: payStr,
        source: manualOverride ? 'Manual admin override' : 'Price JSON calculation',
        note: noteStr || 'Auto-generated / empty'
      },
      () => {
        confirmedOnce = true;
        // Let the existing ajax handler run as intended
        try {
          if (form.requestSubmit) {
            form.requestSubmit(btn);
          } else {
            btn.click();
          }
        } finally {
          // reset after stack clears
          setTimeout(() => { confirmedOnce = false; }, 0);
        }
      }
    );
  }, true); // CAPTURE to run before other handlers

})();
</script>

</script>




<script>
  // Admin: remove uploaded proof screenshot (custom confirm, no browser popup)
  document.addEventListener('DOMContentLoaded', function () {
    var removeBtn = document.getElementById('lbAdminRemoveScreenshotBtn');
    if (!removeBtn) return;

    var confirmBackdrop = document.getElementById('lbRemoveConfirm');
    var cancelBtn = document.getElementById('lbRemoveCancelBtn');
    var confirmBtn = document.getElementById('lbRemoveConfirmBtn');
    var sid = null;

    function openConfirm() {
      if (!confirmBackdrop) return;
      confirmBackdrop.classList.add('is-open');
      confirmBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closeConfirm() {
      if (!confirmBackdrop) return;
      confirmBackdrop.classList.remove('is-open');
      confirmBackdrop.setAttribute('aria-hidden', 'true');
    }

    removeBtn.addEventListener('click', function () {
      sid = removeBtn.getAttribute('data-screenshot-id');
      if (!sid) return;
      openConfirm();
    });

    if (cancelBtn) {
      cancelBtn.addEventListener('click', function () {
        closeConfirm();
      });
    }

    if (confirmBackdrop) {
      // click outside card closes
      confirmBackdrop.addEventListener('click', function (e) {
        if (e.target === confirmBackdrop) closeConfirm();
      });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        if (!sid) return;

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Removing...';

        var fd = new FormData();
        fd.append('screenshot_id', sid);

        fetch('<?= ADMN_URL ?>/orders/screenshot/delete', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        })
        .then(function (res) {
          if (!res.ok) throw new Error('Request failed');
          return res.text();
        })
        .then(function () {
          window.location.reload();
        })
        .catch(function () {
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Remove';
          closeConfirm();
          alert('Failed to remove screenshot.');
        });
      });
    }

    // When modal closes, also close confirm
    var modalEl = document.getElementById('complete_order_md');
    if (modalEl && typeof bootstrap !== 'undefined') {
      modalEl.addEventListener('hidden.bs.modal', function () {
        sid = null;
        if (confirmBtn) {
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Remove';
        }
        closeConfirm();
      });
    }
  });
</script>


<script>
  // Admin: Bonus Win confirmation in Complete Order modal
  document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('complete_order_md');
    if (!modalEl) return;

    var checkbox = modalEl.querySelector('#lbBonusWinConfirm');
    if (!checkbox) return; // Only rendered for bonus-win orders

    var submitBtn = modalEl.querySelector('button[type="submit"]');

    function syncSubmit() {
      if (!submitBtn) return;
      submitBtn.disabled = !checkbox.checked;
    }

    checkbox.addEventListener('change', syncSubmit);
    syncSubmit();

    if (typeof bootstrap !== 'undefined') {
      modalEl.addEventListener('shown.bs.modal', syncSubmit);
      modalEl.addEventListener('hidden.bs.modal', function () {
        checkbox.checked = false;
        syncSubmit();
      });
    }
  });
</script>



<script>
  /**
   * Fallback AJAX handler:
   * If for any reason the global ajax-form handler is not bound, prevent navigation to /ajax
   * and handle forms with class="ajax-form" via XHR (same response contract: sendToast, playSound, redirectUrl, refreshPage, resetForm).
   */
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') return;

    function lbHandleAjaxResponse(res, $form) {
      let response = res;
      try {
        if (typeof res === 'string') response = JSON.parse(res);
      } catch (e) {
        // Not JSON, ignore
        response = null;
      }
      if (!response) return;

      if (response.resetForm && $form && $form[0]) {
        $form[0].reset();
      }

      if (response.sendToast && typeof window.create_toast === 'function') {
        create_toast(response.sendToast.type, response.sendToast.title, response.sendToast.message);
      }

      if (response.playSound) {
        try {
          var audio = new Audio(asset_url + '/core/dash/audio/' + response.playSound + '.mp3');
          audio.play();
        } catch (e) {}
      }

      if (response.redirectUrl) {
        setTimeout(function () {
          window.location.href = response.redirectUrl;
        }, 250);
      } else if (response.refreshPage) {
        setTimeout(function () {
          location.reload();
        }, 250);
      }
    }

    // Bind once
    if (!document.body.dataset.lbAjaxFormsBound) {
      document.body.dataset.lbAjaxFormsBound = "1";

      $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submit = $form.find('button[type="submit"], input[type="submit"]').first();

        // Basic loading state (compatible with your indicator-label / indicator-progress markup)
        if ($submit.length) {
          $submit.prop('disabled', true);
          const $label = $submit.find('.indicator-label');
          const $prog = $submit.find('.indicator-progress');
          if ($label.length && $prog.length) {
            $label.hide();
            $prog.show();
          }
        }

        $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          success: function (res) {
            lbHandleAjaxResponse(res, $form);
          },
          error: function () {
            if (typeof window.create_toast === 'function') {
              create_toast('danger', 'Error', 'Something went wrong. Please try again.');
            }
          },
          complete: function () {
            if ($submit.length) {
              $submit.prop('disabled', false);
              const $label = $submit.find('.indicator-label');
              const $prog = $submit.find('.indicator-progress');
              if ($label.length && $prog.length) {
                $prog.hide();
                $label.show();
              }
            }
          }
        });
      });
    }
  });
</script>



<?= $this->end() ?>


<script>
/* Admin Order View: ensure TomSelect hides already-selected options in dropdown */
document.addEventListener('DOMContentLoaded', function () {
  if (!window.TomSelect) return;

  // Only target selects inside the Edit Order modal/content (class js-select is used in this view)
  document.querySelectorAll('.admin-order-view select.js-select').forEach(function (sel) {
    // Prevent double-init
    if (sel.tomselect) return;

    const isMultiple = sel.hasAttribute('multiple') || (sel.name && sel.name.endsWith('[]'));
    try {
      new TomSelect(sel, {
        plugins: isMultiple ? ['remove_button'] : [],
        persist: false,
        create: false,
        hideSelected: true,      // <-- important: selected options disappear from dropdown
        closeAfterSelect: !isMultiple,
        allowEmptyOption: true,
        maxOptions: 5000,
        onItemAdd: function() {
          if (this.control_input) {
            this.control_input.value = '';
          }
          this.setTextboxValue('');
          this.refreshOptions(false);
        },
        render: {
          option: function(data, escape) {
            // Keep default but ensure long labels don't overflow
            return '<div class="ts-option-item">' + escape(data.text) + '</div>';
          }
        }
      });
    } catch (e) {
      // Fail silently; we don't want to break admin view if TomSelect errors.
      console && console.warn && console.warn('TomSelect init failed:', e);
    }
  });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  const triggers = document.querySelectorAll('.admin-order-view .js-lb-champs-tooltip');
  if (!triggers.length) return;

  let tooltip = document.querySelector('.lb-champs-tooltip');
  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.className = 'lb-champs-tooltip';
    tooltip.innerHTML = '<div class="lb-champs-tooltip__title"></div><div class="lb-champs-tooltip__grid"></div>';
    document.body.appendChild(tooltip);
  }

  const titleEl = tooltip.querySelector('.lb-champs-tooltip__title');
  const gridEl = tooltip.querySelector('.lb-champs-tooltip__grid');
  let hideTimer = null;
  let activeTrigger = null;

  function esc(value) {
    return String(value || '').replace(/[&<>'"]/g, function (char) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'})[char];
    });
  }

  function readItems(trigger) {
    try {
      const parsed = JSON.parse(trigger.getAttribute('data-items') || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function render(trigger) {
    const items = readItems(trigger);
    titleEl.textContent = trigger.getAttribute('data-title') || 'All champions';
    gridEl.innerHTML = items.map(function (item) {
      const name = item && item.name ? item.name : '';
      const icon = item && item.icon ? item.icon : '';
      if (icon) {
        return '<span class="lb-champs-tooltip__item" title="' + esc(name) + '"><img src="' + esc(icon) + '" alt="' + esc(name) + '" loading="lazy"></span>';
      }
      return '<span class="lb-champs-tooltip__tag" title="' + esc(name) + '">' + esc(name) + '</span>';
    }).join('');
  }

  function place(trigger) {
    const rect = trigger.getBoundingClientRect();
    tooltip.classList.add('is-visible');
    const pad = 14;
    const tt = tooltip.getBoundingClientRect();
    let left = rect.left + rect.width / 2 - tt.width / 2;
    left = Math.max(pad, Math.min(left, window.innerWidth - tt.width - pad));
    let top = rect.bottom + 10;
    if (top + tt.height > window.innerHeight - pad) {
      top = Math.max(pad, rect.top - tt.height - 10);
    }
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
  }

  function show(trigger) {
    activeTrigger = trigger;
    clearTimeout(hideTimer);
    render(trigger);
    place(trigger);
  }

  function scheduleHide() {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(function () {
      tooltip.classList.remove('is-visible');
      activeTrigger = null;
    }, 140);
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('mouseenter', function () { show(trigger); });
    trigger.addEventListener('mousemove', function () { place(trigger); });
    trigger.addEventListener('mouseleave', scheduleHide);
    trigger.addEventListener('focus', function () { show(trigger); });
    trigger.addEventListener('blur', scheduleHide);
    trigger.setAttribute('tabindex', '0');
  });

  tooltip.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
  tooltip.addEventListener('mouseleave', scheduleHide);
  window.addEventListener('scroll', function (event) {
    if (event && tooltip.contains(event.target)) return;
    if (activeTrigger && tooltip.classList.contains('is-visible')) {
      place(activeTrigger);
    }
  }, true);
  window.addEventListener('resize', function () {
    if (activeTrigger && tooltip.classList.contains('is-visible')) {
      place(activeTrigger);
    }
  });
});
</script>


<!-- Chat Image Modal -->
<div class="modal fade" id="lbChatImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn btn-sm btn-icon btn-light position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <img id="lbChatImageModalImg" src="" alt="Chat image" style="max-width: 100%; max-height: 85vh; display: block; margin: 0 auto; border-radius: 12px;">
      </div>
    </div>
  </div>
</div>

<script>
/* Admin Order View: open chat images in modal (no new tab) */
document.addEventListener('DOMContentLoaded', function () {
  const chat = document.querySelector('.admin-order-view #chat_messages');
  const modalEl = document.getElementById('lbChatImageModal');
  const modalImg = document.getElementById('lbChatImageModalImg');
  if (!chat || !modalEl || !modalImg) return;

  function isAvatarOrIcon(img) {
    if (!img) return true;
    if (img.classList.contains('lb-msg__avatar')) return true;
    if (img.closest('.lb-msg__avatar')) return true;
    // guard against tiny ui icons that may be in chat header/controls
    const w = img.naturalWidth || img.width || 0;
    const h = img.naturalHeight || img.height || 0;
    if (w && h && w <= 64 && h <= 64) return true;
    return false;
  }

  // Capture clicks on IMG or A(IMG) inside chat
  chat.addEventListener('click', function (e) {
    const img = e.target.closest('img');
    if (!img || !chat.contains(img)) return;
    if (isAvatarOrIcon(img)) return;

    // If wrapped in a link, prevent navigation/new tab
    const link = e.target.closest('a');
    if (link && chat.contains(link)) {
      e.preventDefault();
    } else {
      e.preventDefault();
    }
    e.stopPropagation();

    const fullSrc = img.getAttribute('data-full') || img.getAttribute('data-src') || img.src;
    if (!fullSrc) return;

    modalImg.src = fullSrc;

    if (window.bootstrap && bootstrap.Modal) {
      const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
      inst.show();
    } else {
      // ultra-light fallback (shouldn't happen if Bootstrap is present)
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
      modalEl.removeAttribute('aria-hidden');
      document.body.classList.add('modal-open');
    }
  }, true);

  // Clear image on close to free memory
  modalEl.addEventListener('hidden.bs.modal', function () {
    modalImg.src = '';
  });
});
</script>







<script>
(function(){
  function initAdminRanked5sBoosterTabs(){
    const tabs = Array.from(document.querySelectorAll('[data-r5s-admin-booster-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-r5s-admin-booster-panel]'));
    if (!tabs.length || !panels.length) return;

    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        const idx = String(tab.getAttribute('data-r5s-admin-booster-tab') || '0');
        tabs.forEach(function(t){
          t.classList.toggle('is-active', String(t.getAttribute('data-r5s-admin-booster-tab') || '') === idx);
        });
        panels.forEach(function(panel){
          panel.classList.toggle('is-active', String(panel.getAttribute('data-r5s-admin-booster-panel') || '') === idx);
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminRanked5sBoosterTabs);
  } else {
    initAdminRanked5sBoosterTabs();
  }
})();
</script>
