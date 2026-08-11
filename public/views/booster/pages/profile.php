<?php
// Normalize servers for display (DB: "euw|eune|na" -> ["euw","eune","na"])
if (!isset($data['servers']) || $data['servers'] === null) {
    $data['servers'] = [];
} elseif (is_string($data['servers'])) {
    $data['servers'] = array_values(array_filter(explode('|', $data['servers'])));
} elseif (!is_array($data['servers'])) {
    $data['servers'] = [];
}

// Normalize games (avoid PHP8 TypeErrors when using in_array/foreach)
if (!isset($data['games']) || $data['games'] === null) {
    $data['games'] = [];
} elseif (!is_array($data['games'])) {
    // stored as "lol|val|tft" in DB
    $data['games'] = array_values(array_filter(explode('|', (string)$data['games']), fn($v) => $v !== ''));
}
$lbDynamicGames = array_values(array_diff($data['games'], ['lol', 'val', 'tft', 'lol_classic']));
$lbDynamicProfileBoosterId = defined('BOOSTER_ID') ? (int)BOOSTER_ID : (int)($data['booster_id'] ?? 0);
$lbDynamicProfiles = lb_booster_game_profiles($lbDynamicProfileBoosterId);

// Game specific order limits shown in the booster dashboard.
$lb_order_limits = [
    'lol_solo' => (int)($data['lol_solo_order_limit'] ?? $data['solo_order_limit'] ?? 0),
    'lol_duo'  => (int)($data['lol_duo_order_limit'] ?? $data['duo_order_limit'] ?? 0),
    'val_solo' => (int)($data['val_solo_order_limit'] ?? $data['valorant_solo_order_limit'] ?? 0),
    'val_duo'  => (int)($data['val_duo_order_limit'] ?? $data['valorant_duo_order_limit'] ?? 0),
    'tft'      => (int)($data['tft_order_limit'] ?? 0),
];

// Normalize rank arrays (avoid PHP8 TypeErrors / undefined offsets)
$rankDefaults = [
    'lol_rank' => ['7', '4'], // Diamond IV
    'val_rank' => ['6', '3'], // Diamond I (VAL divisions are 3..1)
    'tft_rank' => ['7', '4'], // Diamond IV (TFT uses LoL-like tiers/divisions)
];

foreach ($rankDefaults as $key => $def) {
    if (!isset($data[$key]) || $data[$key] === null) {
        $data[$key] = $def;
        continue;
    }

    if (!is_array($data[$key])) {
        $parts = explode('|', (string)$data[$key]);
        $parts = array_values(array_filter($parts, fn($v) => $v !== ''));
        $data[$key] = $parts;
    }

    // pad missing parts
    if (!isset($data[$key][0]) || $data[$key][0] === '') $data[$key][0] = $def[0];
    if (!isset($data[$key][1]) || $data[$key][1] === '') $data[$key][1] = $def[1];
    // ensure numeric strings
    $data[$key][0] = (string)$data[$key][0];
    $data[$key][1] = (string)$data[$key][1];
}

// Helper: format server safely
$lb_servers_display = 'N/A';
if (!empty($data['servers'])) {
    $lb_servers_display = implode(', ', array_map(function ($s) {
        return function_exists('util_format_server') ? util_format_server($s) : $s;
    }, $data['servers']));
}

// Booster timezone (IANA timezone, e.g. "Europe/Berlin"). If not set -> N/A
$lb_timezone_raw = trim((string) ($data['timezone'] ?? ''));
if (function_exists('util_format_timezone_display')) {
    $lb_timezone_display = util_format_timezone_display($lb_timezone_raw);
} else {
    $lb_timezone_display = ($lb_timezone_raw !== '') ? $lb_timezone_raw : 'N/A';
}

$lb_cover_default  = ASSET_URL . '/core/main/img/banners/leona.jpeg';
$lb_cover_url      = trim((string)($data['cover'] ?? BOOSTER_DATA['cover'] ?? ''));
$lb_cover_url      = $lb_cover_url !== '' ? $lb_cover_url : $lb_cover_default;
$lb_cover_position = trim((string)($data['cover_position'] ?? BOOSTER_DATA['cover_position'] ?? $data['banner_position'] ?? BOOSTER_DATA['banner_position'] ?? '50% 50%'));
if ($lb_cover_position === '') $lb_cover_position = '50% 50%';

// Login sessions card data (from booster_session_logs)
if (!function_exists('lb_mask_ip')) {
    function lb_mask_ip($ip) {
        $ip = trim((string)$ip);
        if ($ip === '') return 'Unknown IP';
        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            $visible = array_slice($parts, 0, min(4, count($parts)));
            return implode(':', $visible) . ':****:****';
        }
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.***.***';
        }
        return $ip;
    }
}
if (!function_exists('lb_time_ago')) {
    function lb_time_ago($datetime) {
        if (empty($datetime)) return 'Unknown';

        try {
            if (is_numeric($datetime)) {
                $ts = (int)$datetime;
                $nowTs = time();
            } else {
                // DB timestamps are stored in UTC; calculate diff in UTC first.
                $dt = new DateTime((string)$datetime, new DateTimeZone('UTC'));
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $ts = $dt->getTimestamp();
                $nowTs = $now->getTimestamp();
            }
        } catch (Exception $e) {
            return 'Unknown';
        }

        $diff = $nowTs - $ts;
        if ($diff < 0) $diff = 0;
        if ($diff < 5) return 'Just now';
        if ($diff < 60) return $diff . ' seconds ago';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    }
}
if (!function_exists('lb_parse_user_agent')) {
    function lb_parse_user_agent($ua) {
        $ua = (string)$ua;
        $os = 'Unknown OS';
        $browser = 'Browser';

        if (stripos($ua, 'Windows') !== false) $os = 'Windows';
        elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) $os = 'iOS';
        elseif (stripos($ua, 'Android') !== false) $os = 'Android';
        elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) $os = 'macOS';
        elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';

        if (preg_match('/OPR\/([\d\.]+)/i', $ua) || stripos($ua, 'Opera') !== false) $browser = 'Opera';
        elseif (preg_match('/Edg\/([\d\.]+)/i', $ua)) $browser = 'Edge';
        elseif (preg_match('/CriOS\/([\d\.]+)/i', $ua) || preg_match('/Chrome\/([\d\.]+)/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/Firefox\/([\d\.]+)/i', $ua)) $browser = 'Firefox';
        elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false && stripos($ua, 'CriOS') === false) $browser = 'Safari';

        return [$os, $browser];
    }
}
$lb_current_booster_token = (string)($_COOKIE['booster_session_token'] ?? '');
$lb_session_history = db_get_rows('booster_sessions_history', [
    'booster_id' => BOOSTER_ID,
    'order' => 'created_at,DESC',
    'limit' => 4,
], true);

$lb_login_sessions = [];
$lb_marked_current = false;

if (!empty($lb_session_history)) {
    foreach ($lb_session_history as $lb_log) {
        $token = (string)($lb_log['token'] ?? '');
        $meta = json_decode((string)($lb_log['device_info'] ?? ''), true);
        if (!is_array($meta)) $meta = [];

        $ua = (string)($meta['ua'] ?? '');
        $os = trim((string)($meta['os'] ?? ''));
        $browser = trim((string)($meta['browser'] ?? ''));

        if ($os === '' || $browser === '') {
            [$uaOs, $uaBrowser] = lb_parse_user_agent($ua);
            if ($os === '') $os = $uaOs;
            if ($browser === '') $browser = $uaBrowser;
        }

        $isCurrent = false;
        if (
            !$lb_marked_current &&
            $lb_current_booster_token !== '' &&
            $token !== '' &&
            hash_equals($lb_current_booster_token, $token)
        ) {
            $isCurrent = true;
            $lb_marked_current = true;
        }

        $city = trim((string)($lb_log['city'] ?? ($meta['city'] ?? '')));
        $region = trim((string)($lb_log['region'] ?? ($meta['region'] ?? '')));
        $country = trim((string)($lb_log['country'] ?? ($meta['country'] ?? '')));

        $lb_login_sessions[] = [
            'token' => $token,
            'os' => $os !== '' ? $os : 'Unknown OS',
            'browser' => $browser !== '' ? $browser : 'Browser',
            'ip' => (string)($lb_log['ip_address'] ?? ''),
            'created_at' => (string)($lb_log['created_at'] ?? ''),
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'is_current' => $isCurrent,
        ];
    }
}

if (empty($lb_login_sessions) && $lb_current_booster_token !== '') {
    $lb_current_session = db_get_row('booster_sessions', [
        'token' => $lb_current_booster_token,
        'booster_id' => BOOSTER_ID,
    ]);

    if (!empty($lb_current_session)) {
        $meta = json_decode((string)($lb_current_session['device_info'] ?? ''), true);
        if (!is_array($meta)) $meta = [];

        $ua = (string)(
            $meta['ua'] ??
            $_SERVER['HTTP_USER_AGENT'] ??
            ''
        );

        $os = trim((string)($meta['os']['name'] ?? $meta['os'] ?? ''));
        $browser = trim((string)($meta['client']['name'] ?? $meta['browser'] ?? ''));

        if ($os === '' || $browser === '') {
            [$uaOs, $uaBrowser] = lb_parse_user_agent($ua);
            if ($os === '') $os = $uaOs;
            if ($browser === '') $browser = $uaBrowser;
        }

        $lb_login_sessions[] = [
            'token' => (string)($lb_current_session['token'] ?? ''),
            'os' => $os !== '' ? $os : 'Unknown OS',
            'browser' => $browser !== '' ? $browser : 'Browser',
            'ip' => (string)($lb_current_session['ip_address'] ?? ''),
            'created_at' => (string)($lb_current_session['created_at'] ?? ''),
            'city' => '',
            'region' => '',
            'country' => '',
            'is_current' => true,
        ];
    }
}

?>

<style>
    .avatar {
        position: relative;
    }

    .edit-icon-container {
        width: 30px;
        height: 30px;
        background-color: #35383a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        bottom: 5px;
        right: 5px;
        border: 1px solid #ccc;
        cursor: pointer;
        border: none;
        outline: none;
        padding: 0;
    }

    .edit-icon-container i {
        color: white;
    }

    .edit-cover-container {
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.6);
        border: none;
        color: #fff;
        border-radius: 50%;
        padding: 8px;
        cursor: pointer;
    }


</style>

<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'My Profile - Booster Area | LoLBoost.gg'], 'contain' => false]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
    .avatar-upload {
        backdrop-filter: blur(5px);
        cursor: pointer;
    }

    .lb-ip-toggle {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        color: #fff;
    }

    .lb-ip-toggle:hover,
    .lb-ip-toggle:focus,
    .lb-ip-toggle:active,
    .lb-ip-toggle.active,
    .show > .lb-ip-toggle.dropdown-toggle {
        background: rgba(255,255,255,.1) !important;
        border-color: rgba(255,255,255,.18) !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    .lb-current-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .85rem;
        border-radius: 999px;
        border: 1px solid rgba(80, 170, 255, .35);
        background: rgba(58, 130, 246, .14);
        color: #4ea1ff;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        line-height: 1;
    }
</style>
<style>

/* --- LoLBoost compact profile polish --- */
.lb-profile-compact .card,
.lb-personal-compact .card{
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.025);
  border-radius:16px;
  box-shadow:0 18px 42px rgba(0,0,0,.18);
  overflow:hidden;
}
.lb-profile-compact .card-header,
.lb-personal-compact .card-header{
  min-height:auto;
  padding:1rem 1.25rem;
  border-bottom:1px solid rgba(255,255,255,.07);
  background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,0));
}
.lb-profile-compact .card-body,
.lb-personal-compact .card-body{ padding:1.15rem 1.25rem; }
.lb-profile-compact .card-footer,
.lb-personal-compact .card-footer{
  padding:1rem 1.25rem;
  border-top:1px solid rgba(255,255,255,.07);
  background:rgba(0,0,0,.08);
}
.lb-profile-compact .form-label,
.lb-personal-compact .form-label{
  font-size:.72rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:rgba(255,255,255,.56);
  font-weight:800;
}
.lb-profile-compact .form-control,
.lb-profile-compact .form-select,
.lb-personal-compact .form-control,
.lb-personal-compact .form-select{
  border-radius:12px;
  background:rgba(0,0,0,.18);
  border-color:rgba(255,255,255,.09);
}
.lb-profile-compact .form-control:focus,
.lb-profile-compact .form-select:focus,
.lb-personal-compact .form-control:focus,
.lb-personal-compact .form-select:focus{
  border-color:rgba(124,92,255,.55);
  box-shadow:0 0 0 .2rem rgba(124,92,255,.14);
}
.lb-profile-compact .row.mb-4,
.lb-personal-compact .row.mb-4{ margin-bottom:1rem!important; }
.lb-section-title{
  display:flex;align-items:center;gap:.55rem;
  margin:1.35rem 0 .95rem;
  padding:.65rem .8rem;
  border-radius:12px;
  background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.07);
  font-size:.82rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  color:rgba(255,255,255,.82);
}

.lb-section-title:first-child{margin-top:0;}
.lb-section-title img{
  width:22px;
  height:22px;
  object-fit:contain;
  flex:0 0 22px;
}

/* Keep native and Tom Select dropdowns dark */
.lb-profile-compact .form-select,
.lb-profile-compact select.form-select,
.lb-profile-compact .ts-control,
.lb-profile-compact .ts-dropdown{
  color-scheme:dark;
}
.lb-profile-compact select.form-select option{
  background:#1d2022;
  color:#f5f7fb;
}
.lb-profile-compact .ts-wrapper{
  position:relative;
}
.lb-profile-compact .ts-control{
  background:#1d2022!important;
  border-color:rgba(255,255,255,.1)!important;
  color:#f5f7fb!important;
}
.lb-profile-compact .ts-dropdown{
  z-index:10050!important;
  background:#1d2022!important;
  border:1px solid rgba(255,255,255,.12)!important;
  color:#f5f7fb!important;
  box-shadow:0 18px 45px rgba(0,0,0,.45)!important;
}
.lb-profile-compact .ts-dropdown .dropdown-input-wrap{
  background:#1d2022!important;
  border-bottom:1px solid rgba(255,255,255,.08)!important;
}
.lb-profile-compact .ts-dropdown .dropdown-input{
  background:#17191b!important;
  border-color:rgba(124,92,255,.45)!important;
  color:#fff!important;
}
.lb-profile-compact .ts-dropdown .option{
  background:#1d2022!important;
  color:rgba(255,255,255,.86)!important;
}
.lb-profile-compact .ts-dropdown .option.active,
.lb-profile-compact .ts-dropdown .option:hover{
  background:#292d30!important;
  color:#fff!important;
}
.lb-profile-compact .ts-dropdown .option.selected{
  display:none!important;
}

/* Unified Select design for all game-profile dropdowns */
.lb-unified-game-selects .card,
.lb-unified-game-selects .card-body{
  overflow:visible!important;
}
.lb-unified-game-selects .row{
  position:relative;
  z-index:1;
}
.lb-unified-game-selects .row.lb-select-row-open{
  z-index:10080!important;
}
.lb-unified-game-selects .tom-select-custom,
.lb-unified-game-selects .ts-wrapper{
  position:relative;
}
.lb-unified-game-selects .ts-wrapper.dropdown-active{
  z-index:10090!important;
}
.lb-unified-game-selects .ts-control{
  min-height:44px;
  padding:.65rem .85rem!important;
  border-radius:12px!important;
  background:#1d2022!important;
  border:1px solid rgba(255,255,255,.1)!important;
  color:#f5f7fb!important;
  box-shadow:none!important;
}
.lb-unified-game-selects .ts-wrapper.focus .ts-control,
.lb-unified-game-selects .ts-wrapper.dropdown-active .ts-control{
  border-color:rgba(124,92,255,.72)!important;
  box-shadow:0 0 0 .2rem rgba(124,92,255,.12)!important;
}
.lb-unified-game-selects .ts-control > input{
  color:#fff!important;
}
.lb-unified-game-selects .ts-control .item{
  background:#555b61!important;
  color:#fff!important;
  border:0!important;
  border-radius:4px!important;
}
.lb-unified-game-selects .ts-dropdown{
  z-index:10100!important;
  margin-top:5px!important;
  border-radius:10px!important;
  overflow:hidden!important;
}
.lb-unified-game-selects .ts-dropdown-content{
  max-height:300px;
}

/* Dropdowns are appended to body, so they can never be clipped by cards or tabs */
.lb-profile-floating-dropdown{
  z-index:20000!important;
  background:#1d2022!important;
  border:1px solid rgba(255,255,255,.12)!important;
  color:#f5f7fb!important;
  border-radius:10px!important;
  box-shadow:0 18px 45px rgba(0,0,0,.5)!important;
  overflow:hidden!important;
}
.lb-profile-floating-dropdown .dropdown-input-wrap{
  background:#1d2022!important;
  border-bottom:1px solid rgba(255,255,255,.08)!important;
}
.lb-profile-floating-dropdown .dropdown-input{
  background:#17191b!important;
  border-color:rgba(124,92,255,.5)!important;
  color:#fff!important;
}
.lb-profile-floating-dropdown .option{
  background:#1d2022!important;
  color:rgba(255,255,255,.88)!important;
}
.lb-profile-floating-dropdown .option.active,
.lb-profile-floating-dropdown .option:hover{
  background:#2b2f33!important;
  color:#fff!important;
}
.lb-profile-floating-dropdown .option.selected{
  display:none!important;
}

/* Keep the left overview fixed regardless of which profile tab is open */
.lb-profile-sidebar-sticky{
  position:sticky!important;
  top:20px;
  align-self:flex-start;
  z-index:20;
}
@media(max-width:991.98px){
  .lb-profile-sidebar-sticky{
    position:static!important;
  }
}

.lb-overview-list li:not(.pb-0):not(.pt-4){
  display:flex;align-items:center;gap:.55rem;
  padding:.52rem .65rem;
  margin:.18rem 0;
  border-radius:11px;
  color:rgba(255,255,255,.84);
}
.lb-overview-list .card-subtitle{
  font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.48);
}
.lb-overview-list .dropdown-item-icon{color:#8b5cf6!important;opacity:.95;}
.lb-profile-compact .btn-primary,
.lb-personal-compact .btn-primary{
  border-radius:12px;
  background:linear-gradient(135deg,#6d5efc,#8b5cf6);
  border-color:transparent;
  font-weight:700;
}
.lb-doc-grid{padding:0 1.25rem 1.25rem;}
.lb-doc-thumb{border-radius:14px;background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.08);}
.lb-doc-thumb img{height:150px;}
.lb-doc-thumb .lb-doc-meta{background:rgba(0,0,0,.12);}
.lb-upload-preview img{max-height:170px;}
.lb-profile-compact .border.rounded,
.lb-profile-compact .card .border.rounded{
  border-color:rgba(255,255,255,.08)!important;
  background:rgba(255,255,255,.03);
  border-radius:14px!important;
}
.lb-profile-compact #boosterReferralLink{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:.86rem;}
.lb-profile-compact .lb-ip-box{border-radius:12px!important;}

.lb-profile-nav{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:10px;border:1px solid rgba(255,255,255,.07);border-radius:16px;background:rgba(255,255,255,.025);margin-bottom:16px}
.lb-profile-nav-btn{display:flex;align-items:center;gap:10px;border:1px solid transparent;border-radius:12px;padding:11px 12px;background:transparent;color:rgba(255,255,255,.55);font-weight:800;text-align:left;transition:.15s ease}
.lb-profile-nav-btn i{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;background:rgba(255,255,255,.05)}
.lb-profile-nav-btn.active{color:#fff;background:rgba(124,92,255,.14);border-color:rgba(124,92,255,.36);box-shadow:0 10px 24px rgba(92,74,227,.12)}

.lb-sidebar-game-limit{display:flex!important;align-items:flex-start!important;gap:10px!important;padding:.72rem .65rem!important;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);margin:.35rem 0!important}
.lb-sidebar-game-limit img{width:20px;height:20px;object-fit:contain;flex:0 0 20px;margin-top:2px}
.lb-sidebar-game-limit__content{min-width:0;flex:1}
.lb-sidebar-game-limit__rank{font-weight:750;color:rgba(255,255,255,.9);line-height:1.25}
.lb-sidebar-game-limit__slots{margin-top:4px;font-size:.74rem;font-weight:750;color:#a78bfa;letter-spacing:.01em}
.lb-sidebar-game-limit__slots span{color:rgba(255,255,255,.28);margin:0 4px}
.lb-profile-section[hidden]{display:none!important}
.lb-limit-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.lb-limit-card{border:1px solid rgba(255,255,255,.08);border-radius:14px;background:rgba(255,255,255,.025);overflow:hidden}
.lb-limit-card-head{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.07);font-weight:850}
.lb-limit-card-head img{width:28px;height:28px;object-fit:contain;border-radius:7px}
.lb-limit-values{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding:12px}
.lb-limit-values.one{grid-template-columns:1fr}
.lb-limit-value{padding:10px 11px;border-radius:10px;background:rgba(0,0,0,.16);border:1px solid rgba(255,255,255,.06)}
.lb-limit-value span{display:block;font-size:.66rem;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.42);font-weight:800}
.lb-limit-value strong{display:block;margin-top:3px;font-size:1.05rem;color:#fff}
.lb-game-profile-tabs{display:flex;gap:8px;flex-wrap:wrap;padding:0 1.25rem 1rem}
.lb-game-tab{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:8px 12px;background:rgba(255,255,255,.025);color:rgba(255,255,255,.58);font-weight:800}
.lb-game-tab img{width:20px;height:20px;object-fit:contain}
.lb-game-tab.active{color:#fff;border-color:rgba(124,92,255,.4);background:rgba(124,92,255,.14)}
.lb-game-filter-item[hidden]{display:none!important}
@media(max-width:991.98px){.lb-profile-nav{grid-template-columns:repeat(2,minmax(0,1fr))}.lb-limit-grid{grid-template-columns:1fr}}

@media (max-width:991.98px){
  .lb-profile-compact .js-sticky-block,
  .lb-personal-compact .js-sticky-block{position:static!important;}
}

.lb-title-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:10px;margin-right:.65rem;background:rgba(124,92,255,.14);border:1px solid rgba(124,92,255,.28);color:#a78bfa;vertical-align:middle;}
.card-header-title{display:flex;align-items:center;}
</style>
<?= $this->end() ?>

<?php
require_once __DIR__ . '/_shared.php';
ob_start();
lb_render_booster_area_profile_header('profile');
$__lb_profile_header = ob_get_clean();
$__lb_profile_header = str_replace([' lb-profile-hero', 'lb-profile-hero '], ['', ''], $__lb_profile_header);
echo $__lb_profile_header;
?>

<!-- Content -->
<div class="row lb-profile-compact">
    <div class="col-lg-4">

        <!-- Sticky Block Start Point -->
        <div id="accountSidebarNav"></div>

        <!-- Card -->
        <div class="lb-profile-sidebar-sticky card mb-3 mb-lg-5" data-hs-sticky-block-options='{
            "parentSelector": "#accountSidebarNav",
            "breakpoint": "lg",
            "startPoint": "#accountSidebarNav",
            "endPoint": "#stickyBlockEndPoint",
            "stickyOffsetTop": 20
            }'>
            <!-- Header -->
            <div class="card-header">
                <h4 class="card-header-title"><span class="lb-title-icon"><i class="fa-solid fa-grid-2"></i></span>Overview</h4>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body">
                <ul class="list-unstyled list-py-2 text-dark mb-0 lb-overview-list">
                    <li class="pb-0"><span class="card-subtitle">Account</span></li>
                    <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= BOOSTER_ID ?></li>
                    <?php
                        $lb_balance_cents = (int)(BOOSTER_DATA['balance'] ?? 0);
                        $lb_insurance_required_cents = function_exists('booster_insurance_required_cents') ? booster_insurance_required_cents(BOOSTER_DATA) : 0;
                        $lb_frozen_cents = function_exists('booster_insurance_frozen_cents') ? booster_insurance_frozen_cents(BOOSTER_DATA) : 0;
                        $lb_available_cents = function_exists('booster_available_for_payout_cents') ? booster_available_for_payout_cents(BOOSTER_DATA) : max($lb_balance_cents - $lb_insurance_required_cents, 0);
                    ?>
                    <li>
                      <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-2">
                          <i class="fa-duotone fa-wallet dropdown-item-icon"></i>
                          <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right" title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
                            <span class="fw-semibold"><?= util_format_price_display($lb_balance_cents) ?> EUR</span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                          <i class="fa-duotone fa-shield-check dropdown-item-icon"></i>
                          <span class="fw-semibold" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right"
                                title="Total balance includes your available payout amount and your insurance reserve. Insurance reserve: held as security and paid out when you leave the company.">
                            <span class="text-muted fw-normal">Insurance:</span> <?= util_format_price_display($lb_frozen_cents) ?> EUR
                          </span>
                        </div>

                        <div class="ms-4 small text-muted">
                          Available for payout: <span class="text-dark fw-semibold"><?= util_format_price_display($lb_available_cents) ?> EUR</span>
                        </div>
                        <div class="ms-4 small text-muted">
                          Insurance is held as security and paid out when you leave the company.
                        </div>
                      </div>
                    </li>

                    <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
                    <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= BOOSTER_DATA['email'] ?></li>
                    <li>
                        <i class="fa-brands fa-discord dropdown-item-icon"></i>
                        <?= !empty(BOOSTER_DATA['discord']) ? BOOSTER_DATA['discord'] : 'N/A' ?>
                    </li>
                    <li>
                        <i class="fa-brands fa-discord dropdown-item-icon"></i>
                        <?= !empty(BOOSTER_DATA['discord_id']) ? BOOSTER_DATA['discord_id'] : 'N/A' ?>
                    </li>

                   <li class="pt-4 pb-0"><span class="card-subtitle">Limits</span></li>

                <?php if (in_array('lol', $data['games'], true)): ?>
                  <li class="lb-sidebar-game-limit">
                    <img src="/public/assets/website/images/icons/league-of-legends.png" alt="LoL">
                    <div class="lb-sidebar-game-limit__content">
                      <div class="lb-sidebar-game-limit__rank">LoL: <?= util_format_rank_advanced($data['lol_tier_limit'], $data['lol_division_limit'], 'lol') ?></div>
                      <div class="lb-sidebar-game-limit__slots"><?= $lb_order_limits['lol_solo'] ?> Solo <span>|</span> <?= $lb_order_limits['lol_duo'] ?> Duo</div>
                    </div>
                  </li>
                <?php endif; ?>

                <?php if (in_array('tft', $data['games'], true)): ?>
                  <li class="lb-sidebar-game-limit">
                    <img src="/public/assets/website/images/icons/teamfight-tactics.png" alt="TFT">
                    <div class="lb-sidebar-game-limit__content">
                      <div class="lb-sidebar-game-limit__rank">TFT: <?= util_format_rank_advanced($data['tft_tier_limit'], $data['tft_division_limit'], 'tft') ?></div>
                      <div class="lb-sidebar-game-limit__slots"><?= $lb_order_limits['tft'] ?> Order Slots</div>
                    </div>
                  </li>
                <?php endif; ?>

                <?php if (in_array('val', $data['games'], true)): ?>
                  <li class="lb-sidebar-game-limit">
                    <img src="/public/assets/website/images/icons/valorant.png" alt="VAL">
                    <div class="lb-sidebar-game-limit__content">
                      <div class="lb-sidebar-game-limit__rank">VAL: <?= util_format_rank_advanced($data['val_tier_limit'], $data['val_division_limit'], 'val') ?></div>
                      <div class="lb-sidebar-game-limit__slots"><?= $lb_order_limits['val_solo'] ?> Solo <span>|</span> <?= $lb_order_limits['val_duo'] ?> Duo</div>
                    </div>
                  </li>
                <?php endif; ?>

                    <!-- ✅ Servers -->
                    <li class="pt-4 pb-0"><span class="card-subtitle">Servers</span></li>
                    <li>
                        <i class="fa-duotone fa-globe dropdown-item-icon"></i>
                        <?= $lb_servers_display ?>
                    </li>
                    <!-- ✅ Timezone -->
                    <li class="pt-4 pb-0"><span class="card-subtitle">Timezone</span></li>
                    <li>
                        <i class="fa-duotone fa-clock dropdown-item-icon"></i>
                        <?= esc($lb_timezone_display) ?>
                    </li>
                    <!-- ✅ END -->
                </ul>

                <?php if (BOOSTER_DATA['discord'] == null): ?>
                    <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?>"
                        class="btn btn-primary btn-sm mt-4 btn-block w-100">
                        <i class="fa-brands fa-discord me-1"></i> Connect to Discord
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/discord/connect?booster_id=<?= BOOSTER_DATA['id'] ?>"
                        class="btn btn-primary btn-sm mt-4 btn-block w-100">
                        <i class="fa-brands fa-discord me-1"></i> Reconnect to Discord
                    </a>
                <?php endif; ?>
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>

    <div class="col-lg-8">
        <div class="lb-profile-nav" id="lbBoosterProfileNav">
            <button type="button" class="lb-profile-nav-btn active" data-profile-tab="account"><i class="fa-solid fa-user-gear"></i><span>Account</span></button>
            <button type="button" class="lb-profile-nav-btn" data-profile-tab="games"><i class="fa-solid fa-gamepad-modern"></i><span>Games & Limits</span></button>
            <button type="button" class="lb-profile-nav-btn" data-profile-tab="security"><i class="fa-solid fa-shield-halved"></i><span>Security</span></button>
            <button type="button" class="lb-profile-nav-btn" data-profile-tab="rewards"><i class="fa-solid fa-gift"></i><span>Rewards</span></button>
        </div>
        <div class="d-grid gap-3 gap-lg-4">

            <!-- Form -->
            <form class="form ajax-form lb-profile-section" data-profile-section="account" action="<?= AJAX_URL ?>" method="POST">
                <input type="text" name="action" value="booster_update_account" hidden>
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title"><span class="lb-title-icon"><i class="fa-solid fa-user-gear"></i></span>Account Settings</h4>
                    </div>
                    <!-- End Header -->
                    <div class="card-body">
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="usernameLabel" class="col-sm-3 col-form-label form-label">Username</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="username"
                                    value="<?= BOOSTER_DATA['username'] ?>" id="usernameLabel" placeholder="Username"
                                    aria-label="Username">
                            </div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="emailLabel" class="col-sm-3 col-form-label form-label">Email</label>

                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email"
                                    value="<?= BOOSTER_DATA['email'] ?>" id="emailLabel" placeholder="Email address"
                                    aria-label="Email address">
                            </div>
                        </div>
                        <div class="row mt-4">
                            <label for="languagesLabel" class="col-sm-3 col-form-label form-label">Languages</label>

                            <div class="col-sm-9 tom-select-custom">
                                <select class="js-select form-select" id="languagesLabel" name="languages[]" multiple
                                    autocomplete="off">
                                    <?= util_load_languages_select(BOOSTER_DATA['languages']) ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <label class="col-sm-3 col-form-label form-label"></label>
                            <div class="col-sm-9">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_profile" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="show_profile"
                                        value="1" id="show_profile" <?= BOOSTER_DATA['show_profile'] == 1 ? 'checked' : null ?>>
                                    <label class="form-check-label" for="show_profile">Show Profile on
                                        Boosters Page</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <label class="col-sm-3 col-form-label form-label"></label>
                            <div class="col-sm-9">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="boost_requests" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="boost_requests"
                                        value="1" id="boost_requests" <?= BOOSTER_DATA['boost_requests'] == 1 ? 'checked' : null ?>>
                                    <label class="form-check-label" for="boost_requests">Receive
                                        Boosting/Coaching Requests</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                    document.querySelectorAll('input[name^="pg_price_"], input[name^="duo_pass_price_"]').forEach(function(inp) {
                        inp.addEventListener('input', function() {
                            var val = parseFloat(this.value) || 0;
                            var preview = this.closest('.input-group').querySelector('.pg-earn-preview');
                            if (preview) preview.textContent = val > 0 ? '€' + (val/2).toFixed(2) + ' earn' : '—';
                        });
                    });
                    document.addEventListener('DOMContentLoaded', function() {
                        var btn = document.getElementById('lbToggleIpsBtn');
                        if (!btn) return;
                        var visible = false;
                        var label = btn.querySelector('span');
                        btn.addEventListener('click', function() {
                            visible = !visible;
                            document.querySelectorAll('.lb-ip-box').forEach(function(el) {
                                el.textContent = visible ? (el.dataset.ip || '') : (el.dataset.masked || '');
                            });
                            if (label) label.textContent = visible ? 'Hide IPs' : 'Show IPs';
                            var icon = btn.querySelector('i');
                            if (icon) icon.className = visible ? 'fa-regular fa-eye-slash me-1' : 'fa-regular fa-eye me-1';
                        });
                    });
                    </script>


                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Update Settings
                            </span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                            <span class="indicator-success">
                                <i class="fa-regular fa-circle-check fs-3"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <!-- End Card -->
            </form>
            <!-- End Form -->

            <!-- Form -->
            <form class="form ajax-form lb-profile-section" data-profile-section="account" id="boosterPasswordForm" action="<?= AJAX_URL ?>" method="POST" autocomplete="off">
                <input type="text" name="action" value="booster_update_password" hidden>
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title"><span class="lb-title-icon"><i class="fa-solid fa-lock"></i></span>Change Password</h4>
                    </div>
                    <!-- End Header -->
                    <div class="card-body">
                        <div class="row mb-4">
                            <label for="newPasswordLabel" class="col-sm-3 col-form-label form-label">New Password</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-merge">
                                    <input type="password" class="form-control" name="new_password"
                                        id="newPasswordLabel" placeholder="New password"
                                        aria-label="New password" autocomplete="new-password" minlength="6" required>
                                    <button class="btn btn-outline-secondary js-toggle-password" type="button"
                                        data-target="#newPasswordLabel" aria-label="Show password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Minimum 6 characters.</small>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="confirmPasswordLabel" class="col-sm-3 col-form-label form-label">Confirm Password</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-merge">
                                    <input type="password" class="form-control" name="confirm_password"
                                        id="confirmPasswordLabel" placeholder="Confirm new password"
                                        aria-label="Confirm new password" autocomplete="new-password" minlength="6" required>
                                    <button class="btn btn-outline-secondary js-toggle-password" type="button"
                                        data-target="#confirmPasswordLabel" aria-label="Show password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">

                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Update Password
                            </span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                            <span class="indicator-success">
                                <i class="fa-regular fa-circle-check fs-3"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <!-- End Card -->
            </form>


            <form class="form ajax-form lb-profile-section lb-unified-game-selects" data-profile-section="games" hidden action="<?= AJAX_URL ?>" method="POST">
                <input type="text" name="action" value="booster_update_profile" hidden>
                <input type="hidden" name="game_profiles" id="lbBoosterGameProfilesJson" value="<?= htmlspecialchars(json_encode($lbDynamicProfiles), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach (array_intersect($data['games'], ['lol', 'lol_classic', 'val', 'tft']) as $game): ?>
                    <input type="text" name="<?= $game ?>" value="<?= $game ?>" hidden>
                <?php endforeach; ?>
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title"><span class="lb-title-icon"><i class="fa-solid fa-gamepad-modern"></i></span>Booster Profile</h4>
                    </div>
                    <!-- End Header -->
                    <div class="lb-game-profile-tabs" id="lbGameProfileTabs">
                        <button type="button" class="lb-game-tab active" data-game-tab="general"><i class="fa-solid fa-sliders"></i>General</button>
                        <?php if (in_array('lol', $data['games'], true)): ?><button type="button" class="lb-game-tab" data-game-tab="lol"><img src="/public/assets/website/images/icons/league-of-legends.png" alt="">League of Legends</button><?php endif; ?>
                        <?php if (in_array('lol_classic', $data['games'], true)): ?><button type="button" class="lb-game-tab" data-game-tab="lol_classic"><img src="/public/assets/website/images/icons/lol-classic.png" alt="">LoL Classic</button><?php endif; ?>
                        <?php if (in_array('val', $data['games'], true)): ?><button type="button" class="lb-game-tab" data-game-tab="val"><img src="/public/assets/website/images/icons/valorant.png" alt="">Valorant</button><?php endif; ?>
                        <?php if (in_array('tft', $data['games'], true)): ?><button type="button" class="lb-game-tab" data-game-tab="tft"><img src="/public/assets/website/images/icons/teamfight-tactics.png" alt="">Teamfight Tactics</button><?php endif; ?>
                        <?php foreach ($lbDynamicGames as $lbDynamicGame): $lbDynamicIcon = util_game_icon_url($lbDynamicGame); ?>
                          <button type="button" class="lb-game-tab" data-game-tab="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>"><?php if ($lbDynamicIcon): ?><img src="<?= htmlspecialchars($lbDynamicIcon, ENT_QUOTES) ?>" alt=""><?php endif; ?><?= htmlspecialchars(util_game_display_name($lbDynamicGame), ENT_QUOTES) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-body" id="lbBoosterGameProfileBody">

                        <!-- ✅ NEW: Servers edit -->
                        <div class="row mb-4">
                            <label for="serversLabel" class="col-sm-3 col-form-label form-label">Servers</label>
                            <div class="col-sm-9 tom-select-custom">
                                <select class="js-select form-select" id="serversLabel" name="servers[]" multiple autocomplete="off">
                                    <?= util_load_servers_select($data['servers'] ?? []) ?>
                                </select>
                            </div>
                        </div>
                        <!-- ✅ END -->

                        <!-- ✅ NEW: Timezone edit -->
                        <div class="row mb-4">
                            <label for="timezoneLabel" class="col-sm-3 col-form-label form-label">Timezone</label>
                            <div class="col-sm-9">
                                <select class="form-select" id="timezoneLabel" name="timezone" autocomplete="off">
                                    <?php
                                    if (function_exists('util_load_timezones_select')) {
                                        echo util_load_timezones_select($lb_timezone_raw);
                                    } else {
                                        // Fallback: simple list
                                        $tzList = \DateTimeZone::listIdentifiers();
                                        echo '<option value="" ' . ($lb_timezone_raw === '' ? 'selected' : '') . '>N/A (not set)</option>';
                                        foreach ($tzList as $tz) {
                                            $sel = ($tz === $lb_timezone_raw) ? 'selected' : '';
                                            echo '<option value="' . esc($tz) . '" ' . $sel . '>' . esc($tz) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="text-muted d-block mt-1">If not set, customers will see <strong>N/A</strong>.</small>
                            </div>
                        </div>
                        <!-- ✅ END -->

                        <?php if (in_array('lol', $data['games'])): ?>
                            <div class="lb-section-title"><img src="/public/assets/website/images/icons/league-of-legends.png" alt="League of Legends">League of Legends</div>
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Game Rank</label>

                                <div class="col-sm-9 row mx-0">
                                    <!-- Select -->
                                    <div class="col-9 ps-0">
                                        <select class="form-select" name="lol_rank_0">
                                            <option value="7" <?= $data['lol_rank'][0] == 7 ? 'selected' : null ?>>Diamond</option>
                                            <option value="8" <?= $data['lol_rank'][0] == 8 ? 'selected' : null ?>>Master</option>
                                            <option value="9" <?= $data['lol_rank'][0] == 9 ? 'selected' : null ?>>Grandmaster</option>
                                            <option value="10" <?= $data['lol_rank'][0] == 10 ? 'selected' : null ?>>Challenger</option>
                                        </select>
                                    </div>
                                    <div class="col-3 px-0">
                                        <select class="form-select" name="lol_rank_1">
                                            <option value="4" <?= $data['lol_rank'][1] == 4 ? 'selected' : null ?>>I</option>
                                            <option value="3" <?= $data['lol_rank'][1] == 3 ? 'selected' : null ?>>II</option>
                                            <option value="2" <?= $data['lol_rank'][1] == 2 ? 'selected' : null ?>>III</option>
                                            <option value="1" <?= $data['lol_rank'][1] == 1 ? 'selected' : null ?>>IV</option>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Champions</label>

                                <div class="col-sm-9">
                                    <!-- Select -->
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" name="champions[]" multiple autocomplete="off">
                                            <?= util_load_champions_select($data['champions']) ?>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Roles</label>

                                <div class="col-sm-9">
                                    <!-- Select -->
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" name="roles[]" multiple autocomplete="off">
                                            <?= util_load_roles_select($data['roles']) ?>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                        <?php endif; ?>
                        
                        
                         <?php if (in_array('tft', $data['games'])): ?>
                            <div class="lb-section-title"><img src="/public/assets/website/images/icons/teamfight-tactics.png" alt="Teamfight Tactics">Teamfight Tactics</div>
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Game Rank</label>

                                <div class="col-sm-9 row mx-0">
                                    <!-- Select -->
                                    <div class="col-9 ps-0">
                                        <select class="form-select" name="tft_rank_0">
                                            <option value="7" <?= $data['tft_rank'][0] == 7 ? 'selected' : null ?>>Diamond</option>
                                            <option value="8" <?= $data['tft_rank'][0] == 8 ? 'selected' : null ?>>Master</option>
                                            <option value="9" <?= $data['tft_rank'][0] == 9 ? 'selected' : null ?>>Grandmaster</option>
                                            <option value="10" <?= $data['tft_rank'][0] == 10 ? 'selected' : null ?>>Challenger</option>
                                        </select>
                                    </div>
                                    <div class="col-3 px-0">
                                        <select class="form-select" name="tft_rank_1">
                                            <option value="4" <?= $data['tft_rank'][1] == 4 ? 'selected' : null ?>>I</option>
                                            <option value="3" <?= $data['tft_rank'][1] == 3 ? 'selected' : null ?>>II</option>
                                            <option value="2" <?= $data['tft_rank'][1] == 2 ? 'selected' : null ?>>III</option>
                                            <option value="1" <?= $data['tft_rank'][1] == 1 ? 'selected' : null ?>>IV</option>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                           
                            <!-- End Form Group -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                        <?php endif; ?>

                        <?php if (in_array('val', $data['games'])): ?>
                            <div class="lb-section-title"><img src="/public/assets/website/images/icons/valorant.png" alt="Valorant">Valorant</div>
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Game Rank</label>

                                <div class="col-sm-9 row mx-0">
                                    <!-- Select -->
                                    <div class="col-9 ps-0">
                                        <select class="form-select" name="val_rank_0">
                                            <option value="6" <?= $data['val_rank'][0] == 6 ? 'selected' : null ?>>Diamond</option>
                                            <option value="7" <?= $data['val_rank'][0] == 7 ? 'selected' : null ?>>Ascendant</option>
                                            <option value="8" <?= $data['val_rank'][0] == 8 ? 'selected' : null ?>>Immortal</option>
                                            <option value="9" <?= $data['val_rank'][0] == 9 ? 'selected' : null ?>>Radiant</option>
                                        </select>
                                    </div>
                                    <div class="col-3 px-0">
                                        <select class="form-select" name="val_rank_1">
                                            <option value="3" <?= $data['val_rank'][1] == 3 ? 'selected' : null ?>>I</option>
                                            <option value="2" <?= $data['val_rank'][1] == 2 ? 'selected' : null ?>>II</option>
                                            <option value="1" <?= $data['val_rank'][1] == 1 ? 'selected' : null ?>>III</option>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                            <!-- Form Group -->
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label form-label">Agents</label>

                                <div class="col-sm-9">
                                    <!-- Select -->
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" name="agents[]" multiple autocomplete="off">
                                            <?= util_load_agents_select($data['agents']) ?>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                            </div>
                            <!-- End Form Group -->
                        <?php endif; ?>

                        <?php foreach ($lbDynamicGames as $lbDynamicGame):
                          $lbDynamicProfile = (array)($lbDynamicProfiles[$lbDynamicGame] ?? []);
                          $lbDynamicConfig = lb_generic_game_rank_config($lbDynamicGame) ?? [];
                          $lbDynamicSpecialties = lb_booster_game_specialty_options($lbDynamicGame);
                          $lbDynamicIcon = util_game_icon_url($lbDynamicGame);
                        ?>
                          <div class="lb-section-title" data-dynamic-game-title="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>"><?php if ($lbDynamicIcon): ?><img src="<?= htmlspecialchars($lbDynamicIcon, ENT_QUOTES) ?>" alt=""><?php endif; ?><?= htmlspecialchars(util_game_display_name($lbDynamicGame), ENT_QUOTES) ?></div>
                          <div class="row mb-4 lb-dynamic-game-field" data-dynamic-game="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>">
                            <label class="col-sm-3 col-form-label form-label">Game Rank</label>
                            <div class="col-sm-9 row mx-0">
                              <div class="col-9 ps-0"><select class="form-select js-dynamic-rank" name="game_rank_tier[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>]"><option value="0">Unranked</option><?php foreach ((array)($lbDynamicConfig['ranks'] ?? []) as $tier => $rankName): ?><option value="<?= (int)$tier ?>" <?= (int)($lbDynamicProfile['rank_tier'] ?? 0)===(int)$tier?'selected':'' ?>><?= htmlspecialchars($rankName, ENT_QUOTES) ?></option><?php endforeach; ?></select></div>
                              <div class="col-3 px-0"><select class="form-select js-dynamic-division" name="game_rank_division[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>]"><option value="0">—</option><?php foreach ([1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V'] as $division=>$divisionName): ?><option value="<?= $division ?>" <?= (int)($lbDynamicProfile['rank_division'] ?? 0)===$division?'selected':'' ?>><?= $divisionName ?></option><?php endforeach; ?></select></div>
                            </div>
                          </div>
                          <?php if ($lbDynamicSpecialties): ?>
                          <div class="row mb-4 lb-dynamic-specialty-field" data-dynamic-game="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>">
                            <label class="col-sm-3 col-form-label form-label"><?= htmlspecialchars($lbDynamicSpecialties[0]['label'] ?? 'Specialties', ENT_QUOTES) ?></label>
                            <div class="col-sm-9 tom-select-custom"><select class="js-select form-select js-dynamic-specialties" name="game_specialties[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>][]" multiple autocomplete="off"><?php foreach ($lbDynamicSpecialties as $specialty): ?><option value="<?= htmlspecialchars($specialty['key'], ENT_QUOTES) ?>" <?= in_array($specialty['key'], (array)($lbDynamicProfile['specialties'] ?? []), true)?'selected':'' ?>><?= htmlspecialchars($specialty['name'], ENT_QUOTES) ?></option><?php endforeach; ?></select></div>
                          </div>
                          <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Form Group -->
                        <div class="row mb-4">
                            <label for="description" class="col-sm-3 col-form-label form-label">Description</label>
                            <div class="col-sm-9">
                                <textarea name="description" rows="3" class="form-control" id="description"
                                    placeholder="Description"><?= $data['description'] ?></textarea>
                            </div>
                        </div>
                        <!-- End Form Group -->

                        <?php if (in_array('lol', $data['games'])): ?>
                        <!-- Form Group: Pro Games Prices per Rank -->
                        <div class="row mb-4" data-game-section="lol">
                            <label class="col-sm-3 col-form-label form-label">
                                Pro Games Price
                                <small class="d-block text-muted fw-normal" style="font-size:.75rem;">Price per game per rank (€). Leave all empty to hide from Pro Games.</small>
                                <div class="d-flex align-items-start gap-2 mt-2 mb-0 py-2 px-3" style="font-size:.8rem;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                                    <i class="fa-solid fa-circle-info mt-1" style="flex-shrink:0;"></i>
                                   <span>You receive <strong>50% of the listed price</strong> per game. For example, if you set a game to <strong>€2.00</strong>, you will earn <strong>€1.00</strong> per game played.</span>
                                </div>
                            </label>
                            <div class="col-sm-9">
                                <?php
                                $pgRanks = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
                                $pgPrices = [];
                                if (!empty($data['pg_prices'])) {
                                    $pgPrices = is_array($data['pg_prices'])
                                        ? $data['pg_prices']
                                        : (json_decode($data['pg_prices'], true) ?? []);
                                }
                                foreach ($pgRanks as $pgTier => $pgRankName):
                                    $pgVal = isset($pgPrices[$pgTier]) && $pgPrices[$pgTier] > 0
                                        ? number_format($pgPrices[$pgTier] / 100, 2, '.', '')
                                        : '';
                                ?>
                                <div class="input-group mb-2">
                                    <span class="input-group-text" style="min-width:100px;">
                                        <img src="<?= util_rank_img('lol','mini',$pgTier) ?>" style="width:18px;height:18px;margin-right:6px;">
                                        <?= $pgRankName ?>
                                    </span>
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control"
                                        name="pg_price_<?= $pgTier ?>"
                                        min="0" max="100" step="0.01"
                                        placeholder="e.g. 5.00"
                                        value="<?= $pgVal ?>">
                                    <span class="input-group-text">/ game</span>
                                    <span class="input-group-text text-success fw-semibold" style="font-size:.8rem;min-width:90px;" title="Your earnings (50%)">
                                        <i class="fa-solid fa-arrow-right me-1" style="font-size:.65rem;"></i>
                                        <span class="pg-earn-preview"><?= $pgVal ? '&euro;'.number_format((float)$pgVal/2,2,'.','').' earn' : '—' ?></span>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <small class="text-muted">Set prices per rank. Leave a rank empty to not accept orders for that rank.</small>
                            </div>
                        </div>
                        <!-- End Form Group -->

                        <?php endif; ?>

                        <?php if (in_array('lol_classic', $data['games'], true)): ?>
                        <!-- Form Group: LoL Classic Pro Games Prices per Rank -->
                        <div class="row mb-4" data-game-section="lol_classic">
                            <label class="col-sm-3 col-form-label form-label">
                                LoL Classic Pro Games Price
                                <small class="d-block text-muted fw-normal" style="font-size:.75rem;">Price per game per Classic rank (€). Leave all empty to hide from Classic Pro Games.</small>
                                <div class="d-flex align-items-start gap-2 mt-2 mb-0 py-2 px-3" style="font-size:.8rem;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                                    <i class="fa-solid fa-circle-info mt-1" style="flex-shrink:0;"></i>
                                    <span>You receive <strong>50% of the listed price</strong> per game.</span>
                                </div>
                            </label>
                            <div class="col-sm-9">
                                <?php
                                $classicPgRanks = [1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'];
                                $profileServicePrices = [];
                                if (!empty($data['service_prices'])) {
                                    $profileServicePrices = is_array($data['service_prices'])
                                        ? $data['service_prices']
                                        : (json_decode($data['service_prices'], true) ?? []);
                                }
                                $classicPgPrices = (array)($profileServicePrices['lol_classic_pro_games'] ?? []);
                                foreach ($classicPgRanks as $classicTier => $classicRankName):
                                    $classicPgVal = !empty($classicPgPrices[$classicTier])
                                        ? number_format($classicPgPrices[$classicTier] / 100, 2, '.', '')
                                        : '';
                                ?>
                                <div class="input-group mb-2">
                                    <span class="input-group-text" style="min-width:100px;">
                                        <img src="<?= htmlspecialchars(util_lol_classic_rank_img($classicTier), ENT_QUOTES) ?>" style="width:18px;height:18px;margin-right:6px;" alt="">
                                        <?= $classicRankName ?>
                                    </span>
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control"
                                        name="classic_pg_price_<?= $classicTier ?>"
                                        min="0" max="100" step="0.01"
                                        placeholder="e.g. 5.00"
                                        value="<?= $classicPgVal ?>">
                                    <span class="input-group-text">/ game</span>
                                    <span class="input-group-text text-success fw-semibold" style="font-size:.8rem;min-width:90px;" title="Your earnings (50%)">
                                        <i class="fa-solid fa-arrow-right me-1" style="font-size:.65rem;"></i>
                                        <span class="pg-earn-preview"><?= $classicPgVal ? '&euro;'.number_format((float)$classicPgVal/2,2,'.','').' earn' : '—' ?></span>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <small class="text-muted">Set prices per Classic rank. Leave a rank empty to not accept orders for that rank.</small>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <script>
                    document.querySelectorAll('input[name^="pg_price_"], input[name^="classic_pg_price_"], input[name^="duo_pass_price_"]').forEach(function(inp) {
                        inp.addEventListener('input', function() {
                            var val = parseFloat(this.value) || 0;
                            var preview = this.closest('.input-group').querySelector('.pg-earn-preview');
                            if (preview) preview.textContent = val > 0 ? '€' + (val/2).toFixed(2) + ' earn' : '—';
                        });
                    });
                    </script>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Update Profile
                            </span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </span>
                            <span class="indicator-success">
                                <i class="fa-regular fa-circle-check fs-3"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <!-- End Card -->
            </form>


            <!-- Login Sessions Card -->
            <div class="card lb-profile-section" data-profile-section="security" hidden>
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-header-title mb-0"><span class="lb-title-icon"><i class="fa-solid fa-desktop"></i></span>Login Sessions</h4>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm lb-ip-toggle" id="lbToggleIpsBtn">
                                    <i class="fa-regular fa-eye me-1"></i><span>Show IPs</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" disabled>
                                    <i class="fa-regular fa-xmark me-1"></i>Logout All Devices
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (!empty($lb_login_sessions)): ?>
                                <?php foreach ($lb_login_sessions as $idx => $session): ?>
                                    <div class="d-flex align-items-center justify-content-between py-4 <?= $idx < count($lb_login_sessions) - 1 ? 'border-bottom' : '' ?>" style="border-color: rgba(255,255,255,.08) !important; gap: 20px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center" style="width:64px;height:64px;border-radius:50%;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02);flex-shrink:0;">
                                                <i class="fa-regular fa-desktop fs-1 text-white"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                    <h4 class="mb-0"><?= htmlspecialchars($session['os']) ?> · <?= htmlspecialchars($session['browser']) ?></h4>
                                                    <?php if ($session['is_current']): ?>
                                                        <span class="lb-current-pill">Current</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-body-secondary">Last active · <?= htmlspecialchars(lb_time_ago($session['created_at'])) ?></div>
                                                <?php
                                                    $lbLocationParts = array_values(array_filter([
                                                        trim((string)($session['city'] ?? '')),
                                                        trim((string)($session['country'] ?? '')),
                                                    ]));
                                                ?>
                                                <?php if (!empty($lbLocationParts)): ?>
                                                    <div class="text-body-secondary"><?= htmlspecialchars(implode(', ', $lbLocationParts)) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end" style="min-width:320px;max-width:320px;width:100%;">
                                            <div class="form-control text-center lb-ip-box" data-ip="<?= htmlspecialchars($session['ip'], ENT_QUOTES, 'UTF-8') ?>" data-masked="<?= htmlspecialchars(lb_mask_ip($session['ip']), ENT_QUOTES, 'UTF-8') ?>" style="background: rgba(255,255,255,.02);border-color: rgba(255,255,255,.08);color: var(--bs-secondary-color);padding-top:.85rem;padding-bottom:.85rem;">
                                                <?= htmlspecialchars(lb_mask_ip($session['ip'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="py-4 text-body-secondary">No recent login sessions found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
            <!-- End Login Sessions Card -->

        </div>


        <div class="mt-4 lb-profile-section" data-profile-section="rewards" hidden>
<?php
$lb_ref_settings = function_exists('lb_referral_get_settings')
    ? lb_referral_get_settings()
    : [
        'booster_reward_percent' => 5,
    ];

$lb_booster_ref = function_exists('lb_referral_get_dashboard_data')
    ? lb_referral_get_dashboard_data('booster', (int) BOOSTER_ID)
    : [
        'share_url' => '',
        'earnings_cents' => 0,
        'clicks' => 0,
        'signups' => 0,
        'purchases' => 0,
    ];

$lb_booster_reward_percent = (float) ($lb_ref_settings['booster_reward_percent'] ?? 5);
$lb_booster_share_url = (string) ($lb_booster_ref['share_url'] ?? '');
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="card-header-title mb-1"><span class="lb-title-icon"><i class="fa-solid fa-share-nodes"></i></span>Referral Program</h4>
                    <p class="card-text text-muted mb-0">Share your personal link and earn EUR balance for every paid order that comes in via your referral link.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-success small mb-1">Earnings</div>
                            <div class="fs-2 fw-bold">€<?= number_format(((int)($lb_booster_ref['earnings_cents'] ?? 0)) / 100, 2, ',', '.') ?></div>
                            <div class="text-muted small">Booster balance</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-primary small mb-1">Clicks</div>
                            <div class="fs-2 fw-bold"><?= (int)($lb_booster_ref['clicks'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-danger small mb-1">Signups</div>
                            <div class="fs-2 fw-bold"><?= (int)($lb_booster_ref['signups'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-warning small mb-1">Purchases</div>
                            <div class="fs-2 fw-bold"><?= (int)($lb_booster_ref['purchases'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>

                <label class="form-label">Your referral link</label>
                <div class="d-flex gap-2 flex-column flex-md-row">
                    <input
                        type="text"
                        readonly
                        class="form-control"
                        id="boosterReferralLink"
                        value="<?= htmlspecialchars($lb_booster_share_url, ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <button
                        type="button"
                        class="btn btn-primary px-4"
                        onclick="lbCopyReferralLink('boosterReferralLink', this)"
                    >
                        <i class="fa-regular fa-copy me-2"></i>Copy Link
                    </button>
                </div>

                <div class="alert alert-soft-primary mt-3 mb-0">
                    Reward config: <strong><?= rtrim(rtrim(number_format($lb_booster_reward_percent, 2, '.', ''), '0'), '.') ?>%</strong> of each completed referred order is credited to your EUR balance.
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Form -->
            <!-- End Form -->
        </div>
        <div id="stickyBlockEndPoint"></div>
    </div>
</div>
<!-- End Content -->

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-nav-scroller/dist/hs-nav-scroller.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>
<script>
    $(document).on('ready', function () {

        HSCore.components.HSTomSelect.init('.js-select');

        // Every multi-select on this page hides what is already selected, so the
        // dropdown only ever lists what can still be added (languages, champions,
        // roles, servers, agents, legends/heroes, ...).
        document.querySelectorAll('select.js-select[multiple]').forEach(function (select) {
            var ts = select.tomselect;
            if (!ts) return;
            ts.settings.hideSelected = true;
            ts.on('item_add', function () { ts.refreshOptions(false); });
            ts.on('item_remove', function () { ts.refreshOptions(false); });
            ts.refreshOptions(false);
        });

        // Use the same Tom Select UI for every dropdown in Games & Limits.
        // Dropdowns are attached to body so no card, tab or following field can cover them.
        document.querySelectorAll('.lb-unified-game-selects select.form-select, #languagesLabel').forEach(function (select) {
            var isMultiple = select.multiple;

            if (select.tomselect) {
                select.tomselect.destroy();
            }

            var tomSelect = new TomSelect(select, {
                create: false,
                hideSelected: true,
                closeAfterSelect: !isMultiple,
                maxItems: isMultiple ? null : 1,
                plugins: isMultiple ? ['remove_button'] : [],
                searchField: ['text'],
                dropdownParent: 'body',
                dropdownClass: 'ts-dropdown lb-profile-floating-dropdown',
                render: {
                    no_results: function () {
                        return '<div class="no-results">No results found</div>';
                    }
                }
            });

            tomSelect.on('item_add', function () {
                tomSelect.refreshOptions(false);
            });

            tomSelect.on('item_remove', function () {
                tomSelect.refreshOptions(false);
            });

            tomSelect.refreshOptions(false);
        });

        $('select[name="tier_limit"]').on('change', function () {
            if ($(this).val() >= 7) {
                $('select[name="division_limit"]').parent().addClass('d-none');
            } else {
                $('select[name="division_limit"]').parent().removeClass('d-none');
            }
        });
        $('select[name="tier_limit"]').trigger('change');

        $('.js-toggle-password').on('click', function () {
            const target = $(this).data('target');
            const input = $(target);
            const icon = $(this).find('i');

            if (!input.length) {
                return;
            }

            const isHidden = input.attr('type') === 'password';
            input.attr('type', isHidden ? 'text' : 'password');
            $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
            icon.toggleClass('fa-eye', !isHidden);
            icon.toggleClass('fa-eye-slash', isHidden);
        });

        let boosterPasswordSubmitPending = false;

        function resetBoosterPasswordForm() {
            const passwordForm = document.getElementById('boosterPasswordForm');
            if (!passwordForm) {
                return;
            }

            passwordForm.reset();
            passwordForm.querySelectorAll('input[type="password"], input[type="text"]').forEach(function (input) {
                if (['new_password', 'confirm_password'].indexOf(input.name) !== -1) {
                    input.value = '';
                    input.type = 'password';
                }
            });
            passwordForm.querySelectorAll('.js-toggle-password').forEach(function (button) {
                button.setAttribute('aria-label', 'Show password');
                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        function isBoosterPasswordRequest(settings) {
            if (!settings) {
                return boosterPasswordSubmitPending;
            }

            if (typeof settings.data === 'string') {
                return settings.data.indexOf('booster_update_password') !== -1 || boosterPasswordSubmitPending;
            }

            if (settings.data instanceof FormData) {
                return settings.data.get('action') === 'booster_update_password' || boosterPasswordSubmitPending;
            }

            if (settings.data && typeof settings.data === 'object') {
                return settings.data.action === 'booster_update_password' || boosterPasswordSubmitPending;
            }

            return boosterPasswordSubmitPending;
        }

        $('#boosterPasswordForm').on('submit', function () {
            boosterPasswordSubmitPending = true;
        });

        $(document).ajaxSuccess(function (event, xhr, settings) {
            if (!isBoosterPasswordRequest(settings)) {
                return;
            }

            let response = xhr.responseJSON;
            if (!response) {
                try {
                    response = JSON.parse(xhr.responseText || '{}');
                } catch (e) {
                    response = {};
                }
            }

            if (response && response.sendToast && response.sendToast.type === 'success') {
                resetBoosterPasswordForm();
            }
        });

        $(document).ajaxComplete(function (event, xhr, settings) {
            if (isBoosterPasswordRequest(settings)) {
                boosterPasswordSubmitPending = false;
            }
        });

        // INITIALIZATION OF NAV SCROLLER
        // =======================================================
        new HsNavScroller('.js-nav-scroller');

        // INITIALIZATION OF STICKY BLOCKS
        // =======================================================
        // Sidebar uses native CSS position: sticky because tab contents change height dynamically.

    });

    $('.avatar').mouseover(function () {
        $('.avatar-upload').stop().fadeIn(100);
        $('.avatar-upload').removeClass('d-none');
    });

    $('.avatar').mouseout(function () {
        $('.avatar-upload').stop().fadeOut(200, function () {
            $(this).addClass('d-none');
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var profileButtons = document.querySelectorAll('[data-profile-tab]');
    var profileSections = document.querySelectorAll('[data-profile-section]');
    function openProfileTab(tab) {
        profileButtons.forEach(function (button) { button.classList.toggle('active', button.dataset.profileTab === tab); });
        profileSections.forEach(function (section) { section.hidden = section.dataset.profileSection !== tab; });
        try { sessionStorage.setItem('boosterProfileTab', tab); } catch (e) {}
    }
    profileButtons.forEach(function (button) { button.addEventListener('click', function () { openProfileTab(button.dataset.profileTab); }); });
    var savedTab = 'account';
    try { savedTab = sessionStorage.getItem('boosterProfileTab') || 'account'; } catch (e) {}
    if (!document.querySelector('[data-profile-tab="' + savedTab + '"]')) savedTab = 'account';
    openProfileTab(savedTab);

    var body = document.getElementById('lbBoosterGameProfileBody');
    var gameButtons = document.querySelectorAll('[data-game-tab]');
    if (body && gameButtons.length) {
        var currentGame = 'general';
        Array.from(body.children).forEach(function (child) {
            // A block can pin itself to one or more tabs via data-game-section="lol,lol_classic".
            // Such an explicit assignment must not leak into the following siblings.
            if (child.dataset.gameSection) {
                child.classList.add('lb-game-filter-item');
                return;
            }
            if (child.classList.contains('lb-section-title')) {
                if (child.dataset.dynamicGameTitle) currentGame = child.dataset.dynamicGameTitle;
                else {
                var title = child.textContent.toLowerCase();
                if (title.indexOf('league of legends') !== -1) currentGame = 'lol';
                else if (title.indexOf('teamfight tactics') !== -1) currentGame = 'tft';
                else if (title.indexOf('valorant') !== -1) currentGame = 'val';
                }
            }
            child.classList.add('lb-game-filter-item');
            child.dataset.gameSection = currentGame;
        });
        var description = body.querySelector('#description');
        if (description && description.closest('.row')) description.closest('.row').dataset.gameSection = 'general';
        Array.from(body.querySelectorAll('.form-label')).forEach(function (label) {
            var row = label.closest('.row');
            // Skip rows that already declared their own tabs (e.g. the LoL Classic prices).
            if (!row || row.dataset.gameSection) return;
            if (label.textContent.indexOf('Pro Games Price') !== -1) row.dataset.gameSection = 'lol';
        });
        function openGameTab(game) {
            gameButtons.forEach(function (button) { button.classList.toggle('active', button.dataset.gameTab === game); });
            body.querySelectorAll('.lb-game-filter-item').forEach(function (item) {
                var sections = (item.dataset.gameSection || '').split(',');
                item.hidden = sections.indexOf(game) === -1;
            });
        }
        gameButtons.forEach(function (button) { button.addEventListener('click', function () { openGameTab(button.dataset.gameTab); }); });
        openGameTab('general');
    }

    var gameProfilesInput = document.getElementById('lbBoosterGameProfilesJson');
    var gameProfileForm = gameProfilesInput ? gameProfilesInput.closest('form') : null;
    function syncDynamicGameProfiles() {
        if (!gameProfilesInput) return;
        var profiles = {};
        document.querySelectorAll('.lb-dynamic-game-field').forEach(function(row) {
            var game = row.dataset.dynamicGame;
            var specialtyRow = document.querySelector('.lb-dynamic-specialty-field[data-dynamic-game="' + game + '"]');
            var specialtySelect = specialtyRow ? specialtyRow.querySelector('.js-dynamic-specialties') : null;
            profiles[game] = {
                rank_tier: parseInt(row.querySelector('.js-dynamic-rank')?.value || '0', 10),
                rank_division: parseInt(row.querySelector('.js-dynamic-division')?.value || '0', 10),
                specialties: specialtySelect ? Array.from(specialtySelect.selectedOptions).map(function(option){ return option.value; }) : []
            };
        });
        gameProfilesInput.value = JSON.stringify(profiles);
    }
    if (gameProfileForm) gameProfileForm.addEventListener('submit', syncDynamicGameProfiles, true);
});
</script>
<?= $this->end() ?>

<script>
function lbCopyReferralLink(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-regular fa-circle-check me-2"></i>Copied';
        setTimeout(() => { btn.innerHTML = oldHtml; }, 1800);
    });
}
</script>
