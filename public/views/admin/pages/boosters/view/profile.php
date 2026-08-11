<?php
// Normalize booster games from legacy pipe strings, comma strings, JSON, or arrays.
$lbSelectedGames = $data['games'] ?? [];
if (is_string($lbSelectedGames)) {
  $lbGamesRaw = trim($lbSelectedGames);
  $lbGamesJson = json_decode($lbGamesRaw, true);
  if (is_array($lbGamesJson)) {
    $lbSelectedGames = $lbGamesJson;
  } else {
    $lbSelectedGames = preg_split('/[|,]+/', $lbGamesRaw, -1, PREG_SPLIT_NO_EMPTY);
  }
}
if (!is_array($lbSelectedGames)) {
  $lbSelectedGames = [];
}
$lbSelectedGames = array_values(array_unique(array_filter(array_map(static function ($game) {
  $game = strtolower(trim((string)$game));
  $aliases = [
    'league_of_legends' => 'lol',
    'league-of-legends' => 'lol',
    'leagueoflegends' => 'lol',
    'valorant' => 'val',
    'teamfight_tactics' => 'tft',
    'teamfight-tactics' => 'tft',
    'teamfighttactics' => 'tft',
    'lol-classic' => 'lol_classic',
    'league_classic' => 'lol_classic',
    'league-of-legends-classic' => 'lol_classic',
  ];
  return $aliases[$game] ?? $game;
}, $lbSelectedGames))));
$data['games'] = $lbSelectedGames;
$lbDynamicGames = array_values(array_diff($data['games'], ['lol', 'val', 'tft', 'lol_classic']));
$lbDynamicProfiles = lb_booster_game_profiles((int)($data['id'] ?? 0));

$lbBoostingGames = [];
try {
  global $db;
  $lbRows = $db->run(
    "SELECT DISTINCT g.name, g.slug, g.icon, g.sort_order
     FROM games g
     INNER JOIN game_services gs ON gs.game_id = g.id
     WHERE g.status = 1 AND gs.status = 1 AND gs.service_type = 'boosting'
     ORDER BY g.sort_order ASC, g.name ASC"
  );
  foreach ((array)$lbRows as $lbRow) {
    $lbSlug = strtolower(trim((string)($lbRow['slug'] ?? '')));
    if ($lbSlug === '') continue;
    $lbAliases = [
      'league-of-legends' => 'lol',
      'valorant' => 'val',
      'teamfight-tactics' => 'tft',
    ];
    $lbKey = $lbAliases[$lbSlug] ?? $lbSlug;
    $lbBoostingGames[$lbKey] = [
      'name' => (string)($lbRow['name'] ?? $lbSlug),
      'slug' => $lbSlug,
      'icon' => (string)($lbRow['icon'] ?? ''),
    ];
  }
} catch (Throwable $e) {}
$lbBoostingGames += [
  'lol' => ['name' => 'League of Legends', 'slug' => 'league-of-legends', 'icon' => '/public/assets/website/images/icons/league-of-legends.png'],
  'lol_classic' => ['name' => 'League of Legends Classic', 'slug' => 'league-of-legends-classic', 'icon' => '/public/assets/website/images/icons/league-of-legends.png'],
  'val' => ['name' => 'Valorant', 'slug' => 'valorant', 'icon' => '/public/assets/website/images/icons/valorant.png'],
  'tft' => ['name' => 'Teamfight Tactics', 'slug' => 'teamfight-tactics', 'icon' => '/public/assets/website/images/icons/teamfight-tactics.png'],
];

if (!isset($data['servers']) || $data['servers'] === null) {
  $data['servers'] = [];
} elseif (is_string($data['servers'])) {
  $data['servers'] = array_values(array_filter(explode('|', $data['servers'])));
}

// Ensure timezone key exists (NULL/'' means N/A)
if (!isset($data['timezone']) || $data['timezone'] === null) {
  $data['timezone'] = '';
}
// Backward compatible: if controller didn't split TFT limits yet
if (!isset($data['tft_tier_limit']) || !isset($data['tft_division_limit'])) {
  $tftLimitParts = isset($data['tft_rank_limit']) && strpos($data['tft_rank_limit'], '|') !== false
    ? explode('|', $data['tft_rank_limit'])
    : [null, null];
  $data['tft_tier_limit'] = $data['tft_tier_limit'] ?? ($tftLimitParts[0] ?? null);
  $data['tft_division_limit'] = $data['tft_division_limit'] ?? ($tftLimitParts[1] ?? null);
}

?>

<?php
  // Insurance (permanent frozen reserve)
  $lbInsuranceRequiredCents = 2500;
  if (isset($data['insurance_required_amount']) && $data['insurance_required_amount'] !== null && $data['insurance_required_amount'] !== '') {
    $lbInsuranceRequiredCents = (int)$data['insurance_required_amount'];
  } elseif (isset($data['insurance_required_override']) && $data['insurance_required_override'] !== null && $data['insurance_required_override'] !== '') {
    // fallback for older schema (amount in cents)
    $lbInsuranceRequiredCents = (int)$data['insurance_required_override'];
  }

  $lbBalanceCents = (int)($data['balance'] ?? 0);
  $lbFrozenCents = min($lbBalanceCents, $lbInsuranceRequiredCents);
  $lbAvailableCents = max($lbBalanceCents - $lbInsuranceRequiredCents, 0);

  if (!function_exists('lb_admin_mask_ip')) {
    function lb_admin_mask_ip($ip) {
      $ip = trim((string)$ip);
      if ($ip === '') return 'Unknown IP';
      return $ip; // Always show full IP for admins
    }
  }

  if (!function_exists('lb_admin_time_ago')) {
    function lb_admin_time_ago($datetime) {
      if (empty($datetime)) return 'Unknown';
      try {
        if (is_numeric($datetime)) {
          $ts = (int)$datetime;
          $nowTs = time();
        } else {
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

  if (!function_exists('lb_admin_parse_user_agent')) {
    function lb_admin_parse_user_agent($ua) {
      $ua = (string)$ua;
      $os = 'Unknown OS';
      $browser = 'Browser';

      if (stripos($ua, 'Windows') !== false) $os = 'Windows';
      elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) $os = 'iOS';
      elseif (stripos($ua, 'Android') !== false) $os = 'Android';
      elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) $os = 'Mac';
      elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';

      if (preg_match('/OPR\/([\d\.]+)/i', $ua) || stripos($ua, 'Opera') !== false) $browser = 'Opera';
      elseif (preg_match('/Edg\/([\d\.]+)/i', $ua)) $browser = 'Edge';
      elseif (preg_match('/CriOS\/([\d\.]+)/i', $ua) || preg_match('/Chrome\/([\d\.]+)/i', $ua)) $browser = 'Chrome';
      elseif (preg_match('/Firefox\/([\d\.]+)/i', $ua)) $browser = 'Firefox';
      elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false && stripos($ua, 'CriOS') === false) $browser = 'Safari';

      return [$os, $browser];
    }
  }

  $lbAdminCurrentSession = db_get_row('booster_sessions', [
    'booster_id' => (int)($data['id'] ?? 0),
  ], 1);

  $lbAdminCurrentToken = (string)($lbAdminCurrentSession['token'] ?? '');

  $lbAdminSessionHistory = db_get_rows('booster_sessions_history', [
    'booster_id' => (int)($data['id'] ?? 0),
    'order' => 'created_at,DESC',
    'limit' => 50,
  ], true);

  $lbAdminLoginSessions = [];
  if (!empty($lbAdminSessionHistory)) {
    // Build a map of token -> last ping from booster_session_logs
    $lbAdminSessionTokens = array_column($lbAdminSessionHistory, 'token');
    $lbAdminLastActive = [];
    if (!empty($lbAdminSessionTokens)) {
      foreach ($lbAdminSessionTokens as $lbTok) {
        if ($lbTok === '') continue;
        $lbLog = db_get_row('booster_session_logs', [
          'token' => $lbTok,
          'order' => 'created_at,DESC',
        ], 1);
        if ($lbLog) {
          $lbAdminLastActive[$lbTok] = (string)($lbLog['created_at'] ?? '');
        }
      }
    }

    // Presence is no longer written per ping into booster_session_logs, so the
    // still-active session takes its "last active" from boosters.last_seen_at.
    if ($lbAdminCurrentToken !== '' && function_exists('lb_booster_presence_state')) {
      $lbAdminPresence = lb_booster_presence_state((int)($data['id'] ?? 0));
      if (!empty($lbAdminPresence['last_seen_at'])) {
        $lbAdminLastActive[$lbAdminCurrentToken] = (string)$lbAdminPresence['last_seen_at'];
      }
    }

    foreach ($lbAdminSessionHistory as $lbRow) {
      $meta = json_decode((string)($lbRow['device_info'] ?? ''), true);
      if (!is_array($meta)) $meta = [];

      $ua = (string)($meta['ua'] ?? '');
      $os = trim((string)($meta['os'] ?? ''));
      $browser = trim((string)($meta['browser'] ?? ''));

      if ($os === '' || $browser === '') {
        [$uaOs, $uaBrowser] = lb_admin_parse_user_agent($ua);
        if ($os === '') $os = $uaOs;
        if ($browser === '') $browser = $uaBrowser;
      }

      $city = trim((string)($lbRow['city'] ?? ''));
      $country = trim((string)($lbRow['country'] ?? ''));
      $locationParts = array_values(array_filter([$city, $country], fn($v) => $v !== ''));
      $location = !empty($locationParts) ? implode(', ', $locationParts) : '';

      $lbTokKey = (string)($lbRow['token'] ?? '');
      $lbAdminLoginSessions[] = [
        'token' => $lbTokKey,
        'os' => $os !== '' ? $os : 'Unknown OS',
        'browser' => $browser !== '' ? $browser : 'Browser',
        'ip' => (string)($lbRow['ip_address'] ?? ''),
        'created_at' => (string)($lbRow['created_at'] ?? ''),
        'last_active' => $lbAdminLastActive[$lbTokKey] ?? '',
        'location' => $location,
        'is_current' => (
          $lbAdminCurrentToken !== '' &&
          $lbTokKey !== '' &&
          hash_equals($lbAdminCurrentToken, $lbTokKey)
        ),
      ];
    }
  }
?>
<style>
  .lb-admin-current-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .45rem .9rem;
    border-radius: 999px;
    border: 1px solid rgba(78, 161, 255, .45);
    background: rgba(58, 130, 246, .14);
    color: #4ea1ff;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    line-height: 1;
  }

  .lb-game-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: .65rem;
  }
  .lb-game-settings-card {
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 11px;
    background: rgba(255, 255, 255, .018);
    transition: opacity .18s ease, transform .18s ease, border-color .18s ease;
  }
  .lb-game-settings-card[hidden] { display: none !important; }
  .lb-game-settings-card__header {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .65rem .8rem;
    border-bottom: 1px solid rgba(255, 255, 255, .07);
    background: rgba(108, 92, 231, .055);
  }
  .lb-game-settings-card__header h5 { font-size: .88rem; }
  /* Keep every card header the same height so the fields below start on one line. */
  .lb-game-settings-card__header { min-height: 62px; }
  .lb-game-settings-card__header small {
    display: block;
    font-size: .7rem;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .lb-game-settings-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid rgba(124, 92, 255, .28);
    border-radius: 9px;
    background: rgba(124, 92, 255, .12);
  }
  .lb-game-settings-card__icon img {
    width: 20px;
    height: 20px;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,.28));
  }
  .lb-game-settings-card__body { padding: .75rem .8rem; }
  .lb-game-field + .lb-game-field,
  .lb-game-field + .row,
  .row + .lb-game-field { margin-top: .65rem; }
  .lb-game-field .form-label {
    margin-bottom: .3rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .2px;
    color: rgba(255, 255, 255, .62);
  }
  /* Rank limit: tier and division are one setting, so they share a single control
     group instead of two separately labelled, differently sized selects. */
  .lb-rank-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 108px;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0, 0, 0, .18);
  }
  .lb-rank-row .form-select {
    width: 100%;
    border: 0 !important;
    border-radius: 0 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  .lb-rank-row .form-select:focus { background-color: rgba(124, 92, 255, .10) !important; }
  /* A transparent select makes the native option list render light. The popup
     inherits the control's colors, so give the options an explicit dark scheme. */
  .lb-rank-row .form-select option {
    background-color: #1b1d26;
    color: #e9ecf5;
  }
  .lb-rank-row .form-select { color-scheme: dark; }
  .lb-rank-row__division { border-left: 1px solid rgba(255, 255, 255, .12); }
  .lb-rank-row__division .form-select { text-align: center; padding-left: .5rem; }
  .lb-rank-row select[disabled] { opacity: .35; cursor: not-allowed; }
  .lb-rank-hint {
    margin-top: .35rem;
    font-size: .7rem;
    color: rgba(255, 255, 255, .45);
  }
  /* Games picker: icon + name, in both the dropdown and the selected chips. */
  .lb-game-option {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-width: 0;
  }

  .lb-game-option-icon {
    width: 18px;
    height: 18px;
    object-fit: contain;
    border-radius: 4px;
    flex: 0 0 auto;
  }

  .lb-game-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    margin-left: .2rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .7);
    font-size: .82rem;
    line-height: 1;
    cursor: pointer;
    flex: 0 0 auto;
    transition: background .15s ease, color .15s ease;
  }

  .lb-game-remove:hover {
    background: rgba(255, 82, 82, .3);
    color: #fff;
  }

  /* The dropdown list has no chips, so no remove control there. */
  .ts-dropdown .lb-game-remove,
  .tom-select-custom .dropdown-menu .lb-game-remove { display: none; }

  /* Order limits: fixed two-column grid so the labels line up across all cards. */
  .lb-limit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .55rem;
  }
  .lb-limit-grid--single { grid-template-columns: minmax(0, 1fr); }
  .lb-limit-grid .form-label {
    display: block;
    margin-bottom: .3rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .2px;
    color: rgba(255, 255, 255, .62);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .lb-game-settings-empty {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .75rem .85rem;
    border: 1px dashed rgba(255, 255, 255, .14);
    border-radius: 11px;
    color: var(--bs-secondary-color);
  }
  .lb-game-settings-empty i { font-size: 1.15rem; }
  .lb-game-settings-empty strong { font-size: .84rem; }
  .lb-game-settings-empty span { font-size: .78rem; }
  .lb-game-settings-empty strong,
  .lb-game-settings-empty span { display: block; }
  .lb-game-settings-compact {
    margin-top: .65rem;
    padding: .7rem .8rem;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 11px;
    background: rgba(255, 255, 255, .018);
  }
  .lb-game-settings-compact[hidden] { display: none !important; }
  .lb-game-settings-compact__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .55rem;
  }
  .lb-game-settings-compact__header strong { font-size: .82rem; }
  .lb-game-settings-compact__header span { font-size: .72rem; color: var(--bs-secondary-color); }
  .lb-game-settings-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
  .lb-game-settings-chip {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .55rem;
    border: 1px solid rgba(124, 92, 255, .24);
    border-radius: 8px;
    background: rgba(124, 92, 255, .08);
    font-size: .75rem;
    font-weight: 600;
  }
  .lb-game-settings-chip[hidden] { display: none !important; }
  .lb-game-settings-chip img { width: 16px; height: 16px; object-fit: contain; }
  @media (max-width: 1199.98px) {
    .lb-game-settings-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
  }


.lb-profile-workspace-nav {
  display:flex;
  gap:.55rem;
  padding:.55rem;
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.025);
  border-radius:14px;
  position:sticky;
  top:78px;
  z-index:15;
  backdrop-filter:blur(12px);
}
.lb-profile-workspace-nav .nav-link {
  flex:1;
  display:flex;
  align-items:center;
  justify-content:flex-start;
  gap:.7rem;
  min-height:54px;
  padding:.7rem .9rem;
  border:1px solid rgba(255,255,255,.06) !important;
  border-radius:10px;
  color:rgba(255,255,255,.68) !important;
  background:rgba(255,255,255,.018) !important;
  font-weight:700;
  white-space:nowrap;
  box-shadow:none !important;
}
.lb-profile-workspace-nav .nav-link > i {
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:32px;
  height:32px;
  flex:0 0 32px;
  border-radius:9px;
  background:rgba(255,255,255,.05);
  color:#8f76ff;
}
.lb-profile-workspace-nav .nav-link:hover {
  color:#fff !important;
  background:rgba(255,255,255,.04) !important;
  border-color:rgba(255,255,255,.1) !important;
}
.lb-profile-workspace-nav .nav-link.active {
  color:#fff !important;
  background:linear-gradient(135deg,rgba(103,82,242,.22),rgba(124,92,255,.10)) !important;
  border-color:rgba(124,92,255,.42) !important;
  box-shadow:0 8px 22px rgba(64,45,170,.14) !important;
}
.lb-profile-workspace-nav .nav-link.active > i {
  background:rgba(124,92,255,.18);
  color:#b7a7ff;
}
.lb-profile-workspace-nav .nav-link small {
  display:block;
  font-size:.68rem;
  color:inherit;
  opacity:.65;
  font-weight:500;
}
.lb-profile-tab-pane { animation:lbProfileFade .18s ease; }
@keyframes lbProfileFade { from {opacity:.4;transform:translateY(4px)} to {opacity:1;transform:none} }
.lb-profile-section-intro {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:1rem;
  padding:1rem 1.1rem;
  border:1px solid rgba(255,255,255,.07);
  border-radius:12px;
  background:rgba(255,255,255,.02);
}
.lb-profile-section-intro h4 { margin:0 0 .2rem; }
.lb-profile-section-intro p { margin:0;color:var(--bs-secondary-color);font-size:.84rem; }
@media (max-width:767.98px) {
  .lb-profile-workspace-nav { position:static;overflow-x:auto;justify-content:flex-start; }
  .lb-profile-workspace-nav .nav-link { flex:0 0 auto;padding:.7rem 1rem; }
  .lb-profile-workspace-nav .nav-link small { display:none; }
}

</style>

<div class="row">
  <div class="col-lg-4">

    <!-- Sticky Block Start Point -->
    <div id="accountSidebarNav"></div>

    <!-- Sticky Cards (Overview + Actions) -->
    <div class="js-sticky-block" data-hs-sticky-block-options='{
        "parentSelector": "#accountSidebarNav",
        "breakpoint": "lg",
        "startPoint": "#accountSidebarNav",
        "endPoint": "#stickyBlockEndPoint",
        "stickyOffsetTop": 20
      }'>
      <!-- Card -->
      <div class="card mb-3 mb-lg-5">
      <!-- Header -->
      <div class="card-header">
        <h4 class="card-header-title">Overview</h4>
      </div>
      <!-- End Header -->

      <!-- Body -->
      <div class="card-body">
        <ul class="list-unstyled list-py-2 text-dark mb-0">
          <li class="pb-0"><span class="card-subtitle">Account</span></li>
          <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= $data['id'] ?></li>
          <li><i class="fa-duotone fa-wallet dropdown-item-icon"></i>
            <?= util_format_price_display($data['balance']) ?> EUR</li>
          <li><i class="fa-solid fa-lock dropdown-item-icon"></i>
            <span class="fw-semibold"><?= util_format_price_display($lbFrozenCents) ?> EUR</span>
            <span class="text-muted">Frozen (Insurance)</span>
          </li>
          <li><i class="fa-solid fa-sack-dollar dropdown-item-icon"></i>
            <span class="text-muted">Available balance:</span>
            <span class="fw-semibold"><?= util_format_price_display($lbAvailableCents) ?> EUR</span>
          </li>

          <li class="pt-4 pb-0"><span class="card-subtitle">Contact</span></li>
          <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= $data['email'] ?></li>
          <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= $data['discord'] ?></li>
        </ul>
      </div>
      <!-- End Body -->
    </div>
    
    <!-- Card -->
    <div class="card mb-3 mb-lg-5">
      <div class="card-header">
        <h4 class="card-header-title">Actions</h4>
      </div>

      <div class="card-body">
        <?php
          $is_verified = (int)($data['verified'] ?? 0) === 1;
          $is_banned   = (int)($data['is_banned'] ?? 0) === 1;
          $docs_complete = isset($data['docs_complete']) ? ((int)$data['docs_complete'] === 1) : null;
        ?>

        <!-- Status -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge bg-primary">
            <i class="fa-duotone fa-badge-check me-1"></i>
            <?= $is_verified ? 'Approved' : 'Not approved' ?>
          </span>

          <span class="badge bg-secondary">
            <i class="fa-duotone <?= $is_banned ? 'fa-lock' : 'fa-circle-check' ?> me-1"></i>
            <?= $is_banned ? 'Locked' : 'Active' ?>
          </span>

          <?php if ($docs_complete !== null): ?>
            <span class="badge bg-secondary">
              <i class="fa-duotone fa-id-card me-1"></i>
              <?= $docs_complete ? 'KYC complete' : 'KYC missing' ?>
            </span>
          <?php endif; ?>
        </div>

        <?php if (!$is_verified): ?>
          <form class="ajax-form mb-2" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="admin_verify_booster">
            <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
            <button type="submit" class="btn btn-success w-100">
              <i class="fa-duotone fa-badge-check me-1"></i> Verify & activate
            </button>
          </form>
          <p class="text-muted small mb-3">
            Approves the booster, unlocks the account, and sends login credentials by email.
          </p>
        <?php endif; ?>

        <form id="resendLoginForm" class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
          <input type="hidden" name="action" value="admin_resend_booster_login">
          <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

          <!-- Opens modal (submit happens only after confirmation) -->
          <button type="button" id="openResendLoginModal" class="btn btn-primary w-100">
            <i class="fa-duotone fa-key me-1"></i> Send new password
          </button>
        </form>

        <p class="text-muted small mt-2 mb-0">
          Generates a <b>new</b> password and re-sends the welcome email. The previous password will no longer work.
        </p>
</div>
    </div>
    <!-- End Card -->
    <!-- End Sticky Cards -->
    </div>

  </div>

  <div class="col-lg-8">
    <div class="d-grid gap-3 gap-lg-5">
    <?php
      $lbLastCheckRaw = trim((string)($data['last_order_check'] ?? ''));
      $lbLastCheckTs = $lbLastCheckRaw !== '' ? strtotime($lbLastCheckRaw) : false;
      $lbReviewRequired = ($lbLastCheckTs === false || $lbLastCheckTs < strtotime('-14 days'));
    ?>
    <?php if ($lbReviewRequired): ?>
    <div class="card border border-danger">
      <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="fw-bold mb-1"><i class="fa-duotone fa-triangle-exclamation text-danger me-2"></i>Orders Panel Activity</div>
          <div class="text-muted small">
            <?= $lbLastCheckTs ? 'Last valid Orders Panel activity ' . htmlspecialchars(date('d.m.Y H:i', $lbLastCheckTs)) : 'No valid Orders Panel activity has been recorded.' ?>
            Account verification is required and order claiming is disabled.
          </div>
        </div>
        <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
          <input type="hidden" name="action" value="admin_mark_booster_checked">
          <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger"><i class="fa-duotone fa-user-check me-1"></i>Verify account</button>
        </form>
      </div>
    </div>
    <?php endif; ?>


      <ul class="nav lb-profile-workspace-nav" id="lbBoosterProfileTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="lb-account-tab" data-bs-toggle="tab" data-bs-target="#lb-account-pane" type="button" role="tab" aria-controls="lb-account-pane" aria-selected="true">
            <i class="fa-duotone fa-user-gear"></i><span>Account<small>Access, games and profile</small></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="lb-limits-tab" data-bs-toggle="tab" data-bs-target="#lb-limits-pane" type="button" role="tab" aria-controls="lb-limits-pane" aria-selected="false">
            <i class="fa-duotone fa-sliders"></i><span>Game Limits<small>Ranks and order slots</small></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="lb-games-tab" data-bs-toggle="tab" data-bs-target="#lb-games-pane" type="button" role="tab" aria-controls="lb-games-pane" aria-selected="false">
            <i class="fa-duotone fa-gamepad-modern"></i><span>Game Profiles<small>Ranks, roles and pools</small></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="lb-security-tab" data-bs-toggle="tab" data-bs-target="#lb-security-pane" type="button" role="tab" aria-controls="lb-security-pane" aria-selected="false">
            <i class="fa-duotone fa-shield-halved"></i><span>Security & Sessions<small>Devices and account access</small></span>
          </button>
        </li>
      </ul>

      <!-- One form spans the Account and Game Limits tabs: both save through
           admin_update_booster, which validates username/email/games together. -->
      <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="text" name="action" value="admin_update_booster" hidden>
        <input type="text" name="id" value="<?= $data['id'] ?>" hidden>

      <div class="tab-content" id="lbBoosterProfileTabContent">
        <div class="tab-pane fade show active lb-profile-tab-pane" id="lb-account-pane" role="tabpanel" aria-labelledby="lb-account-tab" tabindex="0">
          <div class="d-grid gap-3 gap-lg-5">

        <!-- Card -->
        <div class="card">
          <div class="card-header">
            <h4 class="card-header-title">Account Settings</h4>
          </div>

          <div class="card-body">
            <div class="row mb-4">
              <label for="usernameLabel" class="col-sm-3 col-form-label form-label">Username</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="username" value="<?= $data['username'] ?>"
                  id="usernameLabel" placeholder="Username" aria-label="Username">
              </div>
            </div>

            <div class="row mb-4">
              <label for="discordLabel" class="col-sm-3 col-form-label form-label">Discord</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="discord" value="<?= $data['discord'] ?>"
                  id="discordLabel" placeholder="Discord#0000" aria-label="Discord">
              </div>
            </div>

            <div class="row mb-4">
              <label for="discordIdLabel" class="col-sm-3 col-form-label form-label">Discord ID</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="discord_id"
                  value="<?= $data['discord_id'] ?>" id="discordIdLabel" placeholder="Discord ID"
                  aria-label="Discord ID">
              </div>
            </div>

            <div class="row mb-4">
              <label for="emailLabel" class="col-sm-3 col-form-label form-label">Email</label>
              <div class="col-sm-9">
                <input type="email" class="form-control" name="email" value="<?= $data['email'] ?>"
                  id="emailLabel" placeholder="Email address" aria-label="Email address">
              </div>
            </div>

            <div class="row mb-4">
              <label for="gamesLabel" class="col-sm-3 col-form-label form-label">Games</label>
              <div class="col-sm-9 tom-select-custom">
                <select class="js-select form-select" id="gamesLabel" name="games[]" multiple autocomplete="off"
                  data-hs-tom-select-options='{"hideSelected":true,"closeAfterSelect":false}'>
                  <?= util_load_games_select($data['games']) ?>
                </select>
              </div>
            </div>

            <div class="row mb-4">
              <label for="languagesLabel" class="col-sm-3 col-form-label form-label">Languages</label>
              <div class="col-sm-9 tom-select-custom">
                <select class="js-select form-select" id="languagesLabel" name="languages[]" multiple autocomplete="off">
                  <?= util_load_languages_select($data['languages']) ?>
                </select>
              </div>
            </div>

            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">LB Rank</label>
              <div class="col-sm-9">
                <div class="tom-select-custom">
                  <select class="js-select form-select" name="rank_id" autocomplete="off"
                    data-hs-tom-select-options='{"hideSearch": true}'>
                    <?= util_load_booster_ranks($data['rank_id']) ?>
                  </select>
                </div>
              </div>
            </div>



            <div class="row mb-4">
              <label class="col-sm-3 col-form-label form-label">Drop Tokens</label>
              <div class="col-sm-9">
                <input type="number" value="<?= $data['drop_tokens'] ?>" class="form-control"
                  name="drop_tokens" placeholder="3" aria-label="Drop Tokens">
              </div>
            </div>

            <div class="row mb-2">
              <label for="passwordLabel" class="col-sm-3 col-form-label form-label">Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control" name="password" id="passwordLabel"
                  placeholder="Leave empty to not change" aria-label="Password">
              </div>
            </div>

            <div class="row mt-4">
              <label class="col-sm-3 col-form-label form-label"></label>
              <div class="col-sm-9">
                <div class="form-check form-switch">
                  <input type="hidden" name="show_profile" value="0">
                  <input class="form-check-input" type="checkbox" role="switch" name="show_profile"
                    value="1" id="show_profile" <?= $data['show_profile'] == 1 ? 'checked' : null ?>>
                  <label class="form-check-label" for="show_profile">Show Profile on Boosters Page</label>
                </div>
              </div>
            </div>

            <div class="row mt-4">
              <label class="col-sm-3 col-form-label form-label"></label>
              <div class="col-sm-9">
                <div class="form-check form-switch">
                  <input type="hidden" name="boost_requests" value="0">
                  <input class="form-check-input" type="checkbox" role="switch" name="boost_requests"
                    value="1" id="boost_requests" <?= $data['boost_requests'] == 1 ? 'checked' : null ?>>
                  <label class="form-check-label" for="boost_requests">Receive Boosting/Coaching Requests</label>
                </div>
              </div>
            </div>

          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-primary">
              <span class="indicator-label">Update Settings</span>
              <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm align-middle"></span>
              </span>
              <span class="indicator-success">
                <i class="fa-regular fa-circle-check fs-3"></i>
              </span>
            </button>
          </div>
        </div>
          </div>
        </div>

        <div class="tab-pane fade lb-profile-tab-pane" id="lb-limits-pane" role="tabpanel" aria-labelledby="lb-limits-tab" tabindex="0">
          <div class="d-grid gap-3 gap-lg-5">
            <div class="lb-profile-section-intro">
              <div><h4>Game Limits</h4><p>Rank access and active order slots per game. Only the games selected in the Account tab are shown.</p></div>
              <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2"><i class="fa-duotone fa-bolt me-1"></i> Live preview</span>
            </div>

            <div class="card">
              <div class="card-body">
            <div class="row mb-4">
              <label for="orderLimitLabel" class="col-sm-3 col-form-label form-label">Normal Order Limit</label>
              <div class="col-sm-9">
                <input type="number" min="0" class="form-control" name="order_limit"
                  id="orderLimitLabel" value="<?= (int)($data['order_limit'] ?? 0) ?>"
                  placeholder="0" aria-label="Normal Order Limit">
                <div class="form-text">Fallback limit for standard orders and older order types.</div>
              </div>
            </div>

            <div class="lb-game-settings mt-4">
              <div class="lb-game-settings-grid">
                <section class="lb-game-settings-card" data-game-section="lol">
                  <div class="lb-game-settings-card__header">
                    <div class="lb-game-settings-card__icon"><img src="/public/assets/website/images/icons/league-of-legends.png" alt="League of Legends"></div>
                    <div>
                      <h5 class="mb-0">League of Legends</h5>
                      <small class="text-muted">Rank access and active order slots, including LoL Classic</small>
                    </div>
                  </div>
                  <div class="lb-game-settings-card__body">
                    <div class="lb-game-field">
                      <label class="form-label">Rank Limit</label>
                      <div class="lb-rank-row">
                        <select class="form-select" name="lol_tier_limit">
                          <option value="1" <?= $data['lol_tier_limit'] == 1 ? 'selected' : null ?>>Iron</option>
                          <option value="2" <?= $data['lol_tier_limit'] == 2 ? 'selected' : null ?>>Bronze</option>
                          <option value="3" <?= $data['lol_tier_limit'] == 3 ? 'selected' : null ?>>Silver</option>
                          <option value="4" <?= $data['lol_tier_limit'] == 4 ? 'selected' : null ?>>Gold</option>
                          <option value="5" <?= $data['lol_tier_limit'] == 5 ? 'selected' : null ?>>Platinum</option>
                          <option value="6" <?= $data['lol_tier_limit'] == 6 ? 'selected' : null ?>>Emerald</option>
                          <option value="7" <?= $data['lol_tier_limit'] == 7 ? 'selected' : null ?>>Diamond</option>
                          <option value="8" <?= $data['lol_tier_limit'] == 8 ? 'selected' : null ?>>Master</option>
                          <option value="9" <?= $data['lol_tier_limit'] == 9 ? 'selected' : null ?>>Grandmaster</option>
                          <option value="10" <?= $data['lol_tier_limit'] == 10 ? 'selected' : null ?>>Challenger</option>
                        </select>
                        <div class="lb-rank-row__division">
                          <select class="form-select" name="lol_division_limit">
                            <option value="4" <?= $data['lol_division_limit'] == 4 ? 'selected' : null ?>>I</option>
                            <option value="3" <?= $data['lol_division_limit'] == 3 ? 'selected' : null ?>>II</option>
                            <option value="2" <?= $data['lol_division_limit'] == 2 ? 'selected' : null ?>>III</option>
                            <option value="1" <?= $data['lol_division_limit'] == 1 ? 'selected' : null ?>>IV</option>
                          </select>
                        </div>
                      </div>
                      <div class="lb-rank-hint" data-rank-hint="lol" hidden>Master and above have no divisions.</div>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6 lb-game-field">
                        <label class="form-label">Solo Orders</label>
                        <input type="number" min="0" value="<?= (int)($data['lol_solo_order_limit'] ?? $data['solo_order_limit'] ?? 0) ?>" class="form-control" name="lol_solo_order_limit">
                      </div>
                      <div class="col-md-6 lb-game-field">
                        <label class="form-label">Duo Orders</label>
                        <input type="number" min="0" value="<?= (int)($data['lol_duo_order_limit'] ?? $data['duo_order_limit'] ?? 0) ?>" class="form-control" name="lol_duo_order_limit">
                      </div>
                    </div>
                  </div>
                </section>

                <section class="lb-game-settings-card" data-game-section="val">
                  <div class="lb-game-settings-card__header">
                    <div class="lb-game-settings-card__icon"><img src="/public/assets/website/images/icons/valorant.png" alt="Valorant"></div>
                    <div>
                      <h5 class="mb-0">Valorant</h5>
                      <small class="text-muted">Rank access and active order slots</small>
                    </div>
                  </div>
                  <div class="lb-game-settings-card__body">
                    <?php
                      // Boosters without a Valorant profile row have no limits stored yet.
                      $lbValTierLimit = (int)($data['val_tier_limit'] ?? 0);
                      $lbValDivisionLimit = (int)($data['val_division_limit'] ?? 0);
                    ?>
                    <div class="lb-game-field">
                      <label class="form-label">Rank Limit</label>
                      <div class="lb-rank-row">
                        <select class="form-select" name="val_tier_limit">
                          <option value="1" <?= $lbValTierLimit === 1 ? 'selected' : null ?>>Iron</option>
                          <option value="2" <?= $lbValTierLimit === 2 ? 'selected' : null ?>>Bronze</option>
                          <option value="3" <?= $lbValTierLimit === 3 ? 'selected' : null ?>>Silver</option>
                          <option value="4" <?= $lbValTierLimit === 4 ? 'selected' : null ?>>Gold</option>
                          <option value="5" <?= $lbValTierLimit === 5 ? 'selected' : null ?>>Platinum</option>
                          <option value="6" <?= $lbValTierLimit === 6 ? 'selected' : null ?>>Diamond</option>
                          <option value="7" <?= $lbValTierLimit === 7 ? 'selected' : null ?>>Ascendant</option>
                          <option value="8" <?= $lbValTierLimit === 8 ? 'selected' : null ?>>Immortal</option>
                          <option value="9" <?= $lbValTierLimit === 9 ? 'selected' : null ?>>Radiant</option>
                        </select>
                        <div class="lb-rank-row__division">
                          <select class="form-select" name="val_division_limit">
                            <option value="3" <?= $lbValDivisionLimit === 3 ? 'selected' : null ?>>I</option>
                            <option value="2" <?= $lbValDivisionLimit === 2 ? 'selected' : null ?>>II</option>
                            <option value="1" <?= $lbValDivisionLimit === 1 ? 'selected' : null ?>>III</option>
                          </select>
                        </div>
                      </div>
                      <div class="lb-rank-hint" data-rank-hint="val" hidden>Immortal and above have no divisions.</div>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6 lb-game-field">
                        <label class="form-label">Solo Orders</label>
                        <input type="number" min="0" value="<?= (int)($data['val_solo_order_limit'] ?? $data['order_limit'] ?? 0) ?>" class="form-control" name="val_solo_order_limit">
                      </div>
                      <div class="col-md-6 lb-game-field">
                        <label class="form-label">Duo Orders</label>
                        <input type="number" min="0" value="<?= (int)($data['val_duo_order_limit'] ?? $data['order_limit'] ?? 0) ?>" class="form-control" name="val_duo_order_limit">
                      </div>
                    </div>
                  </div>
                </section>

                <section class="lb-game-settings-card" data-game-section="tft">
                  <div class="lb-game-settings-card__header">
                    <div class="lb-game-settings-card__icon"><img src="/public/assets/website/images/icons/teamfight-tactics.png" alt="Teamfight Tactics"></div>
                    <div>
                      <h5 class="mb-0">Teamfight Tactics</h5>
                      <small class="text-muted">Rank access and active order slots</small>
                    </div>
                  </div>
                  <div class="lb-game-settings-card__body">
                    <div class="lb-game-field">
                      <label class="form-label">Rank Limit</label>
                      <div class="lb-rank-row">
                        <select class="form-select" name="tft_tier_limit">
                          <option value="1" <?= $data['tft_tier_limit'] == 1 ? 'selected' : null ?>>Iron</option>
                          <option value="2" <?= $data['tft_tier_limit'] == 2 ? 'selected' : null ?>>Bronze</option>
                          <option value="3" <?= $data['tft_tier_limit'] == 3 ? 'selected' : null ?>>Silver</option>
                          <option value="4" <?= $data['tft_tier_limit'] == 4 ? 'selected' : null ?>>Gold</option>
                          <option value="5" <?= $data['tft_tier_limit'] == 5 ? 'selected' : null ?>>Platinum</option>
                          <option value="6" <?= $data['tft_tier_limit'] == 6 ? 'selected' : null ?>>Emerald</option>
                          <option value="7" <?= $data['tft_tier_limit'] == 7 ? 'selected' : null ?>>Diamond</option>
                          <option value="8" <?= $data['tft_tier_limit'] == 8 ? 'selected' : null ?>>Master</option>
                          <option value="9" <?= $data['tft_tier_limit'] == 9 ? 'selected' : null ?>>Grandmaster</option>
                          <option value="10" <?= $data['tft_tier_limit'] == 10 ? 'selected' : null ?>>Challenger</option>
                        </select>
                        <div class="lb-rank-row__division">
                          <select class="form-select" name="tft_division_limit">
                            <option value="4" <?= $data['tft_division_limit'] == 4 ? 'selected' : null ?>>I</option>
                            <option value="3" <?= $data['tft_division_limit'] == 3 ? 'selected' : null ?>>II</option>
                            <option value="2" <?= $data['tft_division_limit'] == 2 ? 'selected' : null ?>>III</option>
                            <option value="1" <?= $data['tft_division_limit'] == 1 ? 'selected' : null ?>>IV</option>
                          </select>
                        </div>
                      </div>
                      <div class="lb-rank-hint" data-rank-hint="tft" hidden>Master and above have no divisions.</div>
                    </div>
                    <div class="lb-game-field">
                      <label class="form-label">Orders</label>
                      <input type="number" min="0" value="<?= (int)($data['tft_order_limit'] ?? $data['order_limit'] ?? 0) ?>" class="form-control" name="tft_order_limit">
                    </div>
                  </div>
                </section>


              </div>

              <div class="lb-game-settings-compact" id="lbOtherGameSettings" hidden>
                <div class="lb-game-settings-compact__header">
                  <strong><i class="fa-duotone fa-layer-group me-1"></i> Other selected games</strong>
                  <span>Use the Normal Order Limit above</span>
                </div>
                <div class="lb-game-settings-chips">
                  <?php foreach ($lbBoostingGames as $lbGameKey => $lbGameMeta): ?>
                    <?php if (in_array($lbGameKey, ['lol', 'val', 'tft'], true)) continue; ?>
                    <span class="lb-game-settings-chip" data-game-section="<?= htmlspecialchars($lbGameKey, ENT_QUOTES, 'UTF-8') ?>" hidden>
                      <?php if (!empty($lbGameMeta['icon'])): ?>
                        <img src="<?= htmlspecialchars($lbGameMeta['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                      <?php else: ?>
                        <i class="fa-duotone fa-gamepad-modern"></i>
                      <?php endif; ?>
                      <?= htmlspecialchars($lbGameMeta['name'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </div>

              <div id="lbNoGameSettings" class="lb-game-settings-empty" hidden>
                <i class="fa-duotone fa-gamepad-modern"></i>
                <div>
                  <strong>No game selected</strong>
                  <span>Select a game above to configure its rank and order limits.</span>
                </div>
              </div>
              <p class="text-muted small mt-3 mb-0">
                <i class="fa-duotone fa-circle-info me-1"></i> A limit of 0 blocks claims for that order type. Coaching orders are always excluded.
              </p>
            </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <span class="indicator-label">Update Limits</span>
                  <span class="indicator-progress">
                    <span class="spinner-border spinner-border-sm align-middle"></span>
                  </span>
                  <span class="indicator-success">
                    <i class="fa-regular fa-circle-check fs-3"></i>
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      </form>
      <!-- End Form -->

      <div class="tab-content">
        <div class="tab-pane fade lb-profile-tab-pane" id="lb-games-pane" role="tabpanel" aria-labelledby="lb-games-tab" tabindex="0">
          <div class="d-grid gap-3 gap-lg-5">
            <div class="lb-profile-section-intro">
              <div><h4>Game Profiles</h4><p>Only the booster details for the selected games are shown here.</p></div>
              <i class="fa-duotone fa-gamepad-modern fs-2 text-primary"></i>
            </div>

      <!-- Booster Profile Form -->
      <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="text" name="action" value="admin_update_booster_profile" hidden>
        <input type="text" name="id" value="<?= $data['id'] ?>" hidden>
        <input type="hidden" name="game_profiles" id="lbAdminGameProfilesJson" value="<?= htmlspecialchars(json_encode($lbDynamicProfiles), ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach (array_intersect($data['games'], ['lol', 'val', 'tft']) as $game): ?>
          <input type="text" name="<?= $game ?>" value="<?= $game ?>" hidden>
        <?php endforeach; ?>

        <div class="card">
          <div class="card-header">
            <h4 class="card-header-title">Booster Profile</h4>
          </div>

          <div class="card-body">
            <?php if (in_array('lol', $data['games'], true) || in_array('lol_classic', $data['games'], true)): ?>
              <h5 class="mb-4">League of Legends</h5>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Game Rank</label>
                <div class="col-sm-9 row mx-0">
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
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Champions</label>
                <div class="col-sm-9 tom-select-custom">
                  <select class="js-select form-select" name="champions[]" multiple autocomplete="off">
                    <?= util_load_champions_select($data['champions']) ?>
                  </select>
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Roles</label>
                <div class="col-sm-9 tom-select-custom">
                  <select class="js-select form-select" name="roles[]" multiple autocomplete="off">
                    <?= util_load_roles_select($data['roles']) ?>
                  </select>
                </div>
              </div>

              <div class="row mb-4">
                <label for="serversLabel" class="col-sm-3 col-form-label form-label">Servers</label>
                <div class="col-sm-9 tom-select-custom">
                  <select class="js-select form-select" id="serversLabel" name="servers[]" multiple autocomplete="off">
                    <?= util_load_servers_select($data['servers'] ?? []) ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            

            <?php /* The TFT rank limit lives in the "Game Limits" card of the Account
                      Settings form. The duplicate copy that used to sit here wrote the
                      same booster_limits.tft_rank_limit and silently overwrote it. */ ?>

            <?php if (in_array('val', $data['games'])): ?>
              <h5 class="mb-4 pt-4 border-top">Valorant</h5>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Game Rank</label>
                <div class="col-sm-9 row mx-0">
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
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label form-label">Agents</label>
                <div class="col-sm-9 tom-select-custom">
                  <select class="js-select form-select" name="agents[]" multiple autocomplete="off">
                    <?= util_load_agents_select($data['agents']) ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <?php foreach ($lbDynamicGames as $lbDynamicGame):
              $lbDynamicProfile = (array)($lbDynamicProfiles[$lbDynamicGame] ?? []);
              $lbDynamicConfig = lb_generic_game_rank_config($lbDynamicGame) ?? [];
              $lbDynamicSpecialties = lb_booster_game_specialty_options($lbDynamicGame);
              $lbDynamicIcon = util_game_icon_url($lbDynamicGame);
            ?>
              <div class="pt-4 mt-4 border-top">
                <h5 class="mb-4 d-flex align-items-center gap-2"><?php if ($lbDynamicIcon): ?><img src="<?= htmlspecialchars($lbDynamicIcon, ENT_QUOTES) ?>" alt="" style="width:28px;height:28px;object-fit:contain"><?php endif; ?><?= htmlspecialchars(util_game_display_name($lbDynamicGame), ENT_QUOTES) ?></h5>
                <div class="row mb-4 lb-admin-dynamic-game" data-dynamic-game="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>">
                  <label class="col-sm-3 col-form-label form-label">Game Rank</label>
                  <div class="col-sm-9 row mx-0">
                    <div class="col-9 ps-0"><select class="form-select js-dynamic-rank" name="game_rank_tier[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>]"><option value="0">Unranked</option><?php foreach ((array)($lbDynamicConfig['ranks'] ?? []) as $tier=>$rankName): ?><option value="<?= (int)$tier ?>" <?= (int)($lbDynamicProfile['rank_tier'] ?? 0)===(int)$tier?'selected':'' ?>><?= htmlspecialchars($rankName, ENT_QUOTES) ?></option><?php endforeach; ?></select></div>
                    <div class="col-3 px-0"><select class="form-select js-dynamic-division" name="game_rank_division[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>]"><option value="0">—</option><?php foreach ([1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V'] as $division=>$divisionName): ?><option value="<?= $division ?>" <?= (int)($lbDynamicProfile['rank_division'] ?? 0)===$division?'selected':'' ?>><?= $divisionName ?></option><?php endforeach; ?></select></div>
                  </div>
                </div>
                <?php if ($lbDynamicSpecialties): ?>
                <div class="row mb-4 lb-admin-dynamic-specialties" data-dynamic-game="<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>">
                  <label class="col-sm-3 col-form-label form-label"><?= htmlspecialchars($lbDynamicSpecialties[0]['label'] ?? 'Specialties', ENT_QUOTES) ?></label>
                  <div class="col-sm-9 tom-select-custom"><select class="js-select form-select js-dynamic-specialties" name="game_specialties[<?= htmlspecialchars($lbDynamicGame, ENT_QUOTES) ?>][]" multiple autocomplete="off"><?php foreach ($lbDynamicSpecialties as $specialty): ?><option value="<?= htmlspecialchars($specialty['key'], ENT_QUOTES) ?>" <?= in_array($specialty['key'], (array)($lbDynamicProfile['specialties'] ?? []), true)?'selected':'' ?>><?= htmlspecialchars($specialty['name'], ENT_QUOTES) ?></option><?php endforeach; ?></select></div>
                </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <!-- ✅ Timezone (match other selects: NOT TomSelect) -->
            <div class="row mb-4">
              <label for="timezoneLabel" class="col-sm-3 col-form-label form-label">Timezone</label>
              <div class="col-sm-9">
                <select class="form-select" id="timezoneLabel" name="timezone">
                  <?= util_load_timezones_select($data['timezone'] ?? '') ?>
                </select>
              </div>
            </div>
            <!-- ✅ End Timezone -->

            <div class="row mb-4">
              <label for="description" class="col-sm-3 col-form-label form-label">Description</label>
              <div class="col-sm-9">
                <textarea name="description" rows="3" class="form-control" id="description"
                  placeholder="Description"><?= $data['description'] ?></textarea>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-primary">
              <span class="indicator-label">Update Profile</span>
              <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm align-middle"></span>
              </span>
              <span class="indicator-success">
                <i class="fa-regular fa-circle-check fs-3"></i>
              </span>
            </button>
          </div>
        </div>
      </form>
      <script>
      (function(){
        var input = document.getElementById('lbAdminGameProfilesJson');
        var form = input ? input.closest('form') : null;
        if (!form) return;
        form.addEventListener('submit', function(){
          var profiles = {};
          form.querySelectorAll('.lb-admin-dynamic-game').forEach(function(row){
            var game = row.dataset.dynamicGame;
            var specialtyRow = form.querySelector('.lb-admin-dynamic-specialties[data-dynamic-game="' + game + '"]');
            var specialtySelect = specialtyRow ? specialtyRow.querySelector('.js-dynamic-specialties') : null;
            profiles[game] = {
              rank_tier: parseInt(row.querySelector('.js-dynamic-rank')?.value || '0', 10),
              rank_division: parseInt(row.querySelector('.js-dynamic-division')?.value || '0', 10),
              specialties: specialtySelect ? Array.from(specialtySelect.selectedOptions).map(function(option){ return option.value; }) : []
            };
          });
          input.value = JSON.stringify(profiles);
        }, true);
      })();
      </script>
      <!-- End Booster Profile Form -->
          </div>
        </div>

        <div class="tab-pane fade lb-profile-tab-pane" id="lb-security-pane" role="tabpanel" aria-labelledby="lb-security-tab" tabindex="0">
          <div class="d-grid gap-3 gap-lg-5">
            <div class="lb-profile-section-intro">
              <div><h4>Security & Sessions</h4><p>Review active devices and revoke access when needed.</p></div>
              <i class="fa-duotone fa-clock-rotate-left fs-2 text-primary"></i>
            </div>

      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title mb-0">Login Sessions
            <?php if (!empty($lbAdminLoginSessions)): ?>
              <span class="badge bg-secondary ms-2" style="font-size:.7rem;font-weight:600;"><?= count($lbAdminLoginSessions) ?></span>
            <?php endif; ?>
          </h4>
          <div class="d-flex align-items-center gap-2">
            <?php if (!empty($lbAdminLoginSessions)): ?>
              <button type="button" class="btn btn-sm btn-outline-primary" id="lbAdminViewAllLoginsBtn"
                data-bs-toggle="modal" data-bs-target="#lbAdminAllLoginsModal">
                <i class="fa-regular fa-list me-1"></i>View All Logins
              </button>
            <?php endif; ?>
            <button type="button" class="btn btn-danger btn-sm" id="lbAdminLogoutAllBtn"
              data-bs-toggle="modal" data-bs-target="#lbAdminLogoutAllModal">
              <i class="fa-regular fa-xmark me-1"></i>Logout All Devices
            </button>
          </div>
        </div>

        <div class="card-body pt-0">
          <?php if (!empty($lbAdminLoginSessions)): ?>
            <?php
              // Detect IP changes between consecutive sessions (newest first)
              $lbIpChangeFlags = [];
              for ($i = 0; $i < count($lbAdminLoginSessions); $i++) {
                $lbIpChangeFlags[$i] = false;
                if ($i > 0) {
                  $prevIp = $lbAdminLoginSessions[$i - 1]['ip'];
                  $currIp = $lbAdminLoginSessions[$i]['ip'];
                  if ($prevIp !== '' && $currIp !== '' && $prevIp !== $currIp) {
                    $lbIpChangeFlags[$i] = true;
                  }
                }
              }
              $lbPreviewSessions = array_slice($lbAdminLoginSessions, 0, 4);
            ?>
            <?php foreach ($lbPreviewSessions as $idx => $session): ?>
              <?php $ipChanged = $lbIpChangeFlags[$idx]; ?>
              <div class="d-flex align-items-center justify-content-between py-4 <?= $idx < count($lbPreviewSessions) - 1 ? 'border-bottom' : '' ?>"
                   style="border-color: rgba(255,255,255,.08) !important; gap: 20px;
                          <?= $ipChanged ? 'background: rgba(255,80,80,.05); border-radius: 8px; padding-left: 10px; padding-right: 10px;' : '' ?>">
                <div class="d-flex align-items-center gap-3">
                  <div class="d-flex align-items-center justify-content-center" style="width:64px;height:64px;border-radius:50%;border:1px solid <?= $ipChanged ? 'rgba(255,90,90,.4)' : 'rgba(255,255,255,.08)' ?>;background:<?= $ipChanged ? 'rgba(255,60,60,.1)' : 'rgba(255,255,255,.02)' ?>;flex-shrink:0;">
                    <i class="fa-regular fa-desktop fs-1 <?= $ipChanged ? 'text-danger' : 'text-white' ?>"></i>
                  </div>

                  <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                      <h4 class="mb-0"><?= htmlspecialchars($session['os']) ?> · <?= htmlspecialchars($session['browser']) ?></h4>
                      <?php if ($session['is_current']): ?>
                        <span class="lb-admin-current-pill">Current</span>
                      <?php endif; ?>
                      <?php if ($ipChanged): ?>
                        <span class="badge" style="background:rgba(255,60,60,.18);color:#ff6b6b;border:1px solid rgba(255,80,80,.35);font-size:.7rem;font-weight:700;letter-spacing:.05em;">
                          <i class="fa-solid fa-triangle-exclamation me-1"></i>IP CHANGE
                        </span>
                      <?php endif; ?>
                    </div>

                    <div class="text-body-secondary">
                      <?php
                        $lbDisplayTime = !empty($session['last_active']) ? $session['last_active'] : $session['created_at'];
                        $lbTimeLabel = !empty($session['last_active']) ? 'Last active' : 'Logged in';
                      ?>
                      <?= $lbTimeLabel ?> · <?= htmlspecialchars(lb_admin_time_ago($lbDisplayTime)) ?>
                      <?php if (!empty($session['location'])): ?>
                        <span class="mx-1">·</span><?= htmlspecialchars($session['location']) ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="text-end" style="min-width:200px;max-width:220px;width:100%;">
                  <div class="form-control text-center"
                       style="background: <?= $ipChanged ? 'rgba(255,60,60,.08)' : 'rgba(255,255,255,.02)' ?>;
                              border-color: <?= $ipChanged ? 'rgba(255,90,90,.35)' : 'rgba(255,255,255,.08)' ?>;
                              color: <?= $ipChanged ? '#ff8080' : 'var(--bs-secondary-color)' ?>;
                              padding-top:.85rem;padding-bottom:.85rem;font-family:monospace;font-size:.82rem;">
                    <?= htmlspecialchars($session['ip'] ?: 'Unknown IP') ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (count($lbAdminLoginSessions) > 4): ?>
              <div class="pt-3 text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#lbAdminAllLoginsModal">
                  <i class="fa-regular fa-list me-1"></i>View all <?= count($lbAdminLoginSessions) ?> sessions
                </button>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="py-4 text-body-secondary">No recent login sessions found.</div>
          <?php endif; ?>
        </div>
      </div>
          </div>
        </div>
      </div>

    </div>
    <div id="stickyBlockEndPoint"></div>
  </div>
</div>

<!-- All Logins Modal -->
<?php if (!empty($lbAdminLoginSessions)): ?>
<div class="modal fade" id="lbAdminAllLoginsModal" tabindex="-1" aria-hidden="true" aria-labelledby="lbAdminAllLoginsModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
    <div class="modal-content card border-0 shadow-lg">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h4 class="card-header-title mb-0" id="lbAdminAllLoginsModalLabel">
            <i class="fa-regular fa-shield-halved me-2 text-primary"></i>All Login Sessions
          </h4>
          <p class="text-body-secondary small mb-0 mt-1">
            <?= count($lbAdminLoginSessions) ?> session<?= count($lbAdminLoginSessions) !== 1 ? 's' : '' ?> recorded
            <?php
              $ipChanges = count(array_filter($lbIpChangeFlags));
              if ($ipChanges > 0):
            ?>
            · <span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= $ipChanges ?> IP change<?= $ipChanges !== 1 ? 's' : '' ?> detected</span>
            <?php endif; ?>
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead style="background:rgba(255,255,255,.03);position:sticky;top:0;z-index:1;">
              <tr>
                <th class="ps-4 py-3" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">#</th>
                <th class="py-3" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">Device</th>
                <th class="py-3" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">IP Address</th>
                <th class="py-3" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">Location</th>
                <th class="py-3" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">Time</th>
                <th class="py-3 pe-4" style="border-bottom:1px solid rgba(255,255,255,.08);color:var(--bs-secondary-color);font-weight:600;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">Flags</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lbAdminLoginSessions as $idx => $session): ?>
                <?php $ipChanged = $lbIpChangeFlags[$idx]; ?>
                <tr style="<?= $ipChanged ? 'background:rgba(255,60,60,.06);' : '' ?>border-bottom:1px solid rgba(255,255,255,.06);">
                  <td class="ps-4 py-3 text-body-secondary" style="width:40px;"><?= $idx + 1 ?></td>
                  <td class="py-3">
                    <div class="d-flex align-items-center gap-2">
                      <i class="fa-regular fa-desktop text-body-secondary"></i>
                      <div>
                        <div class="fw-semibold"><?= htmlspecialchars($session['os']) ?></div>
                        <div class="text-body-secondary small"><?= htmlspecialchars($session['browser']) ?></div>
                      </div>
                      <?php if ($session['is_current']): ?>
                        <span class="lb-admin-current-pill ms-1">Current</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="py-3">
                    <code style="background:<?= $ipChanged ? 'rgba(255,60,60,.12)' : 'rgba(255,255,255,.05)' ?>;color:<?= $ipChanged ? '#ff8080' : 'var(--bs-body-color)' ?>;padding:.25rem .55rem;border-radius:6px;font-size:.82rem;border:1px solid <?= $ipChanged ? 'rgba(255,90,90,.3)' : 'rgba(255,255,255,.08)' ?>;">
                      <?= htmlspecialchars($session['ip'] ?: 'Unknown') ?>
                    </code>
                  </td>
                  <td class="py-3 text-body-secondary">
                    <?php if (!empty($session['location'])): ?>
                      <i class="fa-regular fa-location-dot me-1"></i><?= htmlspecialchars($session['location']) ?>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3">
                    <?php
                      $lbModalDisplayTime = !empty($session['last_active']) ? $session['last_active'] : $session['created_at'];
                    ?>
                    <div class="fw-semibold"><?= htmlspecialchars(lb_admin_time_ago($lbModalDisplayTime)) ?></div>
                    <div class="text-body-secondary small" style="font-size:.75rem;">
                      Login: <?= htmlspecialchars($session['created_at']) ?>
                      <?php if (!empty($session['last_active'])): ?>
                        <br>Active: <?= htmlspecialchars($session['last_active']) ?>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="py-3 pe-4">
                    <?php if ($ipChanged): ?>
                      <span class="badge" style="background:rgba(255,60,60,.18);color:#ff6b6b;border:1px solid rgba(255,80,80,.35);font-size:.7rem;font-weight:700;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>IP CHANGE
                      </span>
                    <?php elseif ($session['is_current']): ?>
                      <span class="badge" style="background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.3);font-size:.7rem;font-weight:700;">ACTIVE</span>
                    <?php else: ?>
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer border-0 d-flex justify-content-between align-items-center">
        <p class="text-body-secondary small mb-0">
          Sessions sorted by most recent first. IP changes indicate logins from a different network.
        </p>
        <button type="button" class="btn btn-sm btn-white" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<!-- Resend Login Modal -->
<div class="modal fade" id="resendLoginModal" tabindex="-1" aria-hidden="true" aria-labelledby="resendLoginModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content card border-0 shadow-lg">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-header-title mb-0" id="resendLoginModalLabel">Send a new password?</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="card-body">
        <div class="d-flex gap-3">
          <div class="flex-shrink-0">
            <div class="avatar avatar-sm avatar-circle">
              <span class="avatar-initials bg-soft-primary text-primary">
                <i class="fa-duotone fa-key"></i>
              </span>
            </div>
          </div>
          <div class="flex-grow-1">
            <p class="mb-0">
              This will generate a new password and resend the login email.
              The old password will stop working.
            </p>
          </div>
        </div>
      </div>

      <div class="card-footer border-0 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmResendLogin" class="btn btn-primary">
          <i class="fa-duotone fa-paper-plane me-1"></i> Send
        </button>
      </div>
    </div>
  </div>
</div>



<script>
  (function () {
    function ready(fn) {
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      var openBtn = document.getElementById('openResendLoginModal');
      var form = document.getElementById('resendLoginForm');
      var modalEl = document.getElementById('resendLoginModal');
      var confirmBtn = document.getElementById('confirmResendLogin');

      if (!openBtn || !form || !modalEl || !confirmBtn || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

      openBtn.addEventListener('click', function (e) {
        e.preventDefault();
        modal.show();
      });

      confirmBtn.addEventListener('click', function () {
        modal.hide();
        // triggers any ajax-form handler via submit event
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
      });
    });
  })();
</script>


<script>
  (function () {
    function ready(fn) {
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      var gamesSelect = document.getElementById('gamesLabel');
      var sections = Array.prototype.slice.call(document.querySelectorAll('[data-game-section]'));
      var emptyState = document.getElementById('lbNoGameSettings');
      var otherGames = document.getElementById('lbOtherGameSettings');
      if (!gamesSelect || !sections.length) return;

      // The theme boots TomSelect itself, so wait for the instance and only then
      // swap in renderers that draw the game icon next to the name.
      (function decorateGamesPicker(tries) {
        var ts = gamesSelect.tomselect;
        if (!ts) {
          if (tries > 0) setTimeout(function () { decorateGamesPicker(tries - 1); }, 120);
          return;
        }

        function iconFor(value) {
          var opt = gamesSelect.querySelector('option[value="' + String(value).replace(/"/g, '\\"') + '"]');
          return (opt && opt.getAttribute('data-image')) || '';
        }
        function esc(v) {
          return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
          });
        }
        function markup(data, withRemove) {
          var src = iconFor(data.value);
          var img = src
            ? '<img class="lb-game-option-icon" src="' + esc(src) + '" alt="" onerror="this.style.display=\'none\'">'
            : '';
          // Overriding render.item drops the remove_button plugin's own X, so each
          // chip renders its own remove control (wired up below).
          var remove = withRemove
            ? '<span class="lb-game-remove" data-lb-remove="' + esc(data.value) + '" role="button" aria-label="Remove">&times;</span>'
            : '';
          return '<div class="lb-game-option">' + img + '<span>' + esc(data.text) + '</span>' + remove + '</div>';
        }

        ts.settings.render.option = function (data) { return markup(data, false); };
        ts.settings.render.item = function (data) { return markup(data, true); };

        if (!ts._lbRemoveBound) {
          ts._lbRemoveBound = true;
          ts.control.addEventListener('mousedown', function (event) {
            var trigger = event.target.closest('[data-lb-remove]');
            if (!trigger) return;
            event.preventDefault();
            event.stopPropagation();
            ts.removeItem(trigger.getAttribute('data-lb-remove'));
            ts.refreshOptions(false);
            gamesSelect.dispatchEvent(new Event('change', { bubbles: true }));
          });
        }

        ts.clearCache();
        ts.sync();
      })(40);

      function normalizeGame(value) {
        value = String(value || '').trim().toLowerCase().replace(/-/g, '_');
        if (value === 'valorant') return 'val';
        if (value === 'teamfight_tactics' || value === 'teamfighttactics') return 'tft';
        if (value.indexOf('classic') !== -1) return 'lol_classic';
        if (value === 'league_of_legends' || value === 'leagueoflegends') return 'lol';
        return value;
      }

      function selectedGames() {
        var values = [];
        if (gamesSelect.tomselect && typeof gamesSelect.tomselect.getValue === 'function') {
          var tomValue = gamesSelect.tomselect.getValue();
          values = Array.isArray(tomValue) ? tomValue : String(tomValue || '').split(',');
        } else {
          values = Array.prototype.slice.call(gamesSelect.selectedOptions || []).map(function (option) {
            return option.value;
          });
        }
        return values.map(normalizeGame).filter(Boolean);
      }

      function updateGameSections() {
        var activeGames = selectedGames();
        var visibleCount = 0;
        sections.forEach(function (section) {
          var game = normalizeGame(section.getAttribute('data-game-section'));
          var visible = activeGames.indexOf(game) !== -1;
          if (game === 'lol' && activeGames.indexOf('lol_classic') !== -1) visible = true;
          section.hidden = !visible;
          section.setAttribute('aria-hidden', visible ? 'false' : 'true');
          if (visible) visibleCount += 1;
        });
        if (otherGames) {
          otherGames.hidden = !otherGames.querySelector('[data-game-section]:not([hidden])');
        }
        if (emptyState) emptyState.hidden = visibleCount !== 0;
      }

      gamesSelect.addEventListener('change', updateGameSections);
      gamesSelect.addEventListener('input', updateGameSections);

      var attempts = 0;
      var tomSelectWatcher = window.setInterval(function () {
        attempts += 1;
        if (gamesSelect.tomselect) {
          gamesSelect.tomselect.settings.hideSelected = true;
          gamesSelect.tomselect.settings.closeAfterSelect = false;
          gamesSelect.tomselect.refreshOptions(false);
          gamesSelect.tomselect.on('change', updateGameSections);
          window.clearInterval(tomSelectWatcher);
        } else if (attempts > 30) {
          window.clearInterval(tomSelectWatcher);
        }
      }, 100);

      updateGameSections();
    });
  })();
</script>

<script>
  // Every multi-select on this page hides what is already selected, so the dropdown
  // only ever lists what can still be added (games, languages, champions, roles,
  // servers, agents, legends/heroes, ...). The theme boots TomSelect itself, so we
  // wait for the instances before switching the option.
  (function () {
    var tries = 40;
    (function apply() {
      var selects = Array.prototype.slice.call(document.querySelectorAll('select.js-select[multiple]'));
      var pending = selects.filter(function (select) { return !select.tomselect; });

      selects.forEach(function (select) {
        var ts = select.tomselect;
        if (!ts || ts._lbHideSelected) return;
        ts._lbHideSelected = true;
        ts.settings.hideSelected = true;
        ts.on('item_add', function () { ts.refreshOptions(false); });
        ts.on('item_remove', function () { ts.refreshOptions(false); });
        ts.refreshOptions(false);
      });

      if (pending.length && tries-- > 0) setTimeout(apply, 120);
    })();
  })();
</script>

<script>
  // Divisions only exist below the apex tiers. Disable (and visually explain) the
  // division select for Master+ instead of letting admins pick a value the backend
  // silently overwrites.
  (function () {
    document.addEventListener('DOMContentLoaded', function () {
      var apexFrom = { lol: 8, tft: 8, val: 8 }; // LoL/TFT: Master, VAL: Immortal
      Object.keys(apexFrom).forEach(function (game) {
        var tier = document.querySelector('select[name="' + game + '_tier_limit"]');
        var division = document.querySelector('select[name="' + game + '_division_limit"]');
        var hint = document.querySelector('[data-rank-hint="' + game + '"]');
        if (!tier || !division) return;

        function sync() {
          var isApex = parseInt(tier.value || '0', 10) >= apexFrom[game];
          division.disabled = isApex;
          if (hint) hint.hidden = !isApex;
        }
        tier.addEventListener('change', sync);
        sync();
      });
    });
  })();
</script>


<script>
  (function () {
    function ready(fn) {
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      var tabs = document.getElementById('lbBoosterProfileTabs');
      if (!tabs || typeof bootstrap === 'undefined' || !bootstrap.Tab) return;
      var storageKey = 'lbAdminBoosterProfileTab';
      var savedTarget = null;
      try { savedTarget = window.sessionStorage.getItem(storageKey); } catch (e) {}
      if (savedTarget) {
        var savedButton = tabs.querySelector('[data-bs-target="' + savedTarget + '"]');
        if (savedButton) bootstrap.Tab.getOrCreateInstance(savedButton).show();
      }
      tabs.addEventListener('shown.bs.tab', function (event) {
        var target = event.target.getAttribute('data-bs-target');
        try { window.sessionStorage.setItem(storageKey, target); } catch (e) {}
        window.setTimeout(function () {
          window.dispatchEvent(new Event('resize'));
        }, 50);
      });
    });
  })();
</script>

<!-- Logout All Devices Modal -->
<div class="modal fade" id="lbAdminLogoutAllModal" tabindex="-1" aria-hidden="true" aria-labelledby="lbAdminLogoutAllModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content card border-0 shadow-lg">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-header-title mb-0" id="lbAdminLogoutAllModalLabel">Logout all devices?</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="card-body">
        <div class="d-flex gap-3">
          <div class="flex-shrink-0">
            <div class="avatar avatar-sm avatar-circle">
              <span class="avatar-initials" style="background:rgba(220,53,69,.15);color:#dc3545;">
                <i class="fa-duotone fa-right-from-bracket"></i>
              </span>
            </div>
          </div>
          <div class="flex-grow-1">
            <p class="mb-0">
              This will immediately terminate all active sessions for this booster. They will be logged out on all devices.
            </p>
          </div>
        </div>
      </div>

      <div class="card-footer border-0 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="lbAdminLogoutAllConfirmBtn" class="btn btn-danger">
          <i class="fa-regular fa-right-from-bracket me-1"></i> Logout All
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    function ready(fn) {
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      var confirmBtn = document.getElementById('lbAdminLogoutAllConfirmBtn');
      var modalEl = document.getElementById('lbAdminLogoutAllModal');
      if (!confirmBtn || !modalEl || typeof bootstrap === 'undefined') return;

      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

      confirmBtn.addEventListener('click', function () {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Logging out...';

        var fd = new FormData();
        fd.append('action', 'admin_logout_booster_sessions');
        fd.append('id', '<?= (int)($data['id'] ?? 0) ?>');

        fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            modal.hide();
            if (res.reloadPage) {
              setTimeout(function () { window.location.reload(); }, 800);
            }
          })
          .catch(function () {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-regular fa-right-from-bracket me-1"></i> Logout All';
          });
      });
    });
  })();
</script>


<?php if (defined('ADMIN_ID') && (int) ADMIN_ID === 3): ?>
<style>
  .lb-admin-id-3-balance-disabled {
    opacity: .45 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
  }
</style>
<script>
  (function () {
    function disableBalanceActions() {
      var candidates = document.querySelectorAll('a, button, [role="button"], .dropdown-item');

      candidates.forEach(function (element) {
        var label = (element.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
        var action = (element.getAttribute('data-action') || '').toLowerCase();
        var href = (element.getAttribute('href') || '').toLowerCase();
        var target = (element.getAttribute('data-bs-target') || element.getAttribute('data-target') || '').toLowerCase();

        var isBalanceAction =
          label === 'balance' ||
          label === 'add balance' ||
          label === 'edit balance' ||
          action.indexOf('balance') !== -1 ||
          href.indexOf('balance') !== -1 ||
          target.indexOf('balance') !== -1;

        if (!isBalanceAction) return;

        element.classList.add('lb-admin-id-3-balance-disabled');
        element.setAttribute('aria-disabled', 'true');
        element.setAttribute('tabindex', '-1');

        if ('disabled' in element) element.disabled = true;

        element.removeAttribute('href');
        element.removeAttribute('data-bs-toggle');
        element.removeAttribute('data-toggle');
        element.removeAttribute('data-bs-target');
        element.removeAttribute('data-target');
        element.removeAttribute('onclick');

        element.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
        }, true);
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', disableBalanceActions);
    } else {
      disableBalanceActions();
    }

    window.setTimeout(disableBalanceActions, 500);
  })();
</script>
<?php endif; ?>
