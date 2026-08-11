<?php
/** Account fields (must exist BEFORE rendering claim screen) */
$riotId = trim((string) ($data['ign'] ?? ''));
$login = trim((string) ($data['login'] ?? ''));
$password = trim((string) ($data['password'] ?? ''));

$riotMissing = ($riotId === '');
$loginMissing = ($login === '');
$passMissing = ($password === '');
$formId = (int) ($data['form_id'] ?? 0);
// Clash Boost is always played together with the customer.
if ($formId === 19) {
  $data['is_duo'] = 1;
}
$isDuo = ((int) ($data['is_duo'] ?? 0) === 1);
$gameKey = strtolower((string) ($data['game'] ?? ''));
$isCounterStrikeOrder = in_array($gameKey, ['cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'], true);

// Form groups for account display rules
$isCoachingForm = in_array($formId, [15, 16, 25], true) || (!empty($data['is_coaching']) && (int)($data['is_coaching'] ?? 0) === 1);
$isLolForm = in_array($formId, [1, 2, 3, 4, 9, 17, 18, 19, 20, 26], true);
$isLolOrder = $isLolForm; // alias used by the Riot ID modal/buttons below
$isValForm = in_array($formId, [5, 6, 7, 8], true);
$isTftForm = in_array($formId, [21, 22, 23, 24], true);

// Coaching + Duo orders only need Riot ID
$isRiotOnlyForm = ($isCoachingForm || $isDuo);
$credRiotOnly = $isRiotOnlyForm;

$showAccountCredentials = !$credRiotOnly;
$accountRiotOnly = !$showAccountCredentials;

if ($isValForm) {
  $accountUsernameLabel = 'VAL Username';
  $accountPasswordLabel = 'VAL Password';
} elseif ($isTftForm) {
  $accountUsernameLabel = 'TFT Username';
  $accountPasswordLabel = 'TFT Password';
} else {
  $accountUsernameLabel = 'Account Username';
  $accountPasswordLabel = 'Account Password';
}

/** Mask helper */
$lb_mask = function ($s, $keepStart = 2, $keepEnd = 2) {
  $s = (string) $s;
  $len = mb_strlen($s);
  if ($len <= ($keepStart + $keepEnd))
    return str_repeat('•', max(4, $len));
  return mb_substr($s, 0, $keepStart)
    . str_repeat('•', max(4, $len - ($keepStart + $keepEnd)))
    . mb_substr($s, -$keepEnd);
};

/** Client fallback (je nach Controller) */
$client = $client ?? ($data['client'] ?? null);

if (!function_exists('lb_client_presence_format_ago')) {
  function lb_client_presence_format_ago($seconds): string {
    $seconds = max(0, (int)$seconds);
    if ($seconds < 60) return 'just now';
    $mins = (int) floor($seconds / 60);
    if ($mins < 60) return $mins . ' Min' . ($mins === 1 ? '' : 's');
    $hours = (int) floor($mins / 60);
    if ($hours < 24) return $hours . ' Hour' . ($hours === 1 ? '' : 's');
    $days = (int) floor($hours / 24);
    if ($days < 30) return $days . ' Day' . ($days === 1 ? '' : 's');
    $months = (int) floor($days / 30);
    if ($months < 12) return $months . ' Month' . ($months === 1 ? '' : 's');
    $years = (int) floor($days / 365);
    return $years . ' Year' . ($years === 1 ? '' : 's');
  }
}

if (!function_exists('lb_client_presence_state')) {
  function lb_client_presence_state($clientId): array {
    $clientId = (int)$clientId;
    $state = [
      'known' => false,
      'online' => false,
      'last_seen_at' => null,
      'label' => 'Offline',
      'title' => 'No client presence/activity found yet',
      'age_seconds' => null,
    ];
    if ($clientId <= 0) return $state;
    global $db;
    try {
      $rows = $db->run(
        "SELECT created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age_seconds, device_info
         FROM client_session_logs
         WHERE client_id = {$clientId}
         ORDER BY CASE WHEN device_info LIKE '%dashboard_presence%' THEN 0 ELSE 1 END, created_at DESC, id DESC
         LIMIT 1"
      );
      $row = (!empty($rows) && is_array($rows)) ? reset($rows) : null;
      if (empty($row) || empty($row['created_at'])) return $state;
      $age = isset($row['age_seconds']) ? (int)$row['age_seconds'] : null;
      if ($age === null || $age < 0) {
        $lastSeenTs = strtotime((string)$row['created_at']);
        if (!$lastSeenTs) return $state;
        $age = max(0, time() - $lastSeenTs);
      }
      $state['known'] = true;
      $state['last_seen_at'] = (string)$row['created_at'];
      $state['age_seconds'] = $age;
      $state['online'] = ($age <= 120);
      $state['label'] = $state['online'] ? 'Online' : ('Last Seen for ' . lb_client_presence_format_ago($age));
      $state['title'] = $state['online'] ? 'Client is currently online' : ('Client last seen ' . lb_client_presence_format_ago($age) . ' ago');
    } catch (Throwable $e) {}
    return $state;
  }
}

if (!function_exists('lb_client_presence_badge_html')) {
  function lb_client_presence_badge_html($clientId): string {
    $presence = lb_client_presence_state($clientId);
    $cls = $presence['online'] ? 'is-online' : 'is-offline';
    $label = htmlspecialchars((string)$presence['label'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars((string)$presence['title'], ENT_QUOTES, 'UTF-8');
    return '<span class="lb-client-presence ' . $cls . '" title="' . $title . '"><span class="lb-client-presence__dot"></span><span>' . $label . '</span></span>';
  }
}


/** Notes laden */
$notes = $data['notes'] ?? db_get_rows('order_notes', ['order_id' => $data['id']]);
$hasNotes = !empty($notes);

/** Helpers */
$price = $data['price'];
$currency = util_format_currency_display($data['currency']);
$booster = get_booster_data(BOOSTER_ID);

/** =========================================
 *  BOOSTER CARD (show your own profile above chat)
 *  - Visible when YOU have accepted/claimed the order (order.booster_id === BOOSTER_ID)
 *  - Uses boosters + booster_profiles (same as client view)
 * ========================================= */
$myBoosterId = (int) (defined('BOOSTER_ID') ? BOOSTER_ID : 0);

// Presence (same logic as Checkout): the booster's own availability switch.
$lbBoosterOnline = function_exists('lb_booster_is_online') ? lb_booster_is_online($myBoosterId) : false;

$myBooster = [];

if (!empty($booster) && is_array($booster)) {
  $myBooster = (array) $booster;
} elseif ($myBoosterId) {
  $b = db_get_row('boosters', ['id' => $myBoosterId]);
  if (!empty($b)) $myBooster = array_merge($myBooster, (array) $b);
}

if ($myBoosterId) {
  $p = db_get_row('booster_profiles', ['booster_id' => $myBoosterId]);
  if (!empty($p)) $myBooster = array_merge($myBooster, (array) $p);
}

/** Robust list parser: supports "a|b|c", "a,b,c", or JSON array */
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

// Ranks
$lolranks = [
  1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum',
  6 => 'Emerald', 7 => 'Diamond', 8 => 'Master', 9 => 'Grandmaster', 10 => 'Challenger'
];

// Rank parsing: "10|Challenger" or "10"
$myRankId = 0;
if (!empty($myBooster['lol_rank'])) {
  $rawRank = (string) $myBooster['lol_rank'];
  if (str_contains($rawRank, '|')) {
    $parts = explode('|', $rawRank);
    $myRankId = (int) ($parts[0] ?? 0);
  } else {
    $myRankId = (int) $rawRank;
  }
}

// Rank parts (tier/div) for inline editing
$myRankRaw = (string) ($myBooster['lol_rank'] ?? '');
$myRankTier = $myRankId;
$myRankDiv = 0;
if (!empty($myRankRaw) && str_contains($myRankRaw, '|')) {
  $rp = explode('|', $myRankRaw);
  $myRankTier = (int) ($rp[0] ?? $myRankTier);
  $myRankDiv = (int) ($rp[1] ?? 0);
}

$myRankName = $lolranks[$myRankId] ?? 'Unranked';
$myRankIcon = ASSET_URL . '/core/main/img/lol/ranks/max/' . $myRankId . '.png';

// Lists
$myRolesAll = $lb_parse_list($myBooster['roles'] ?? '');
$myLangsAll = $lb_parse_list($myBooster['languages'] ?? '');
$myChampsAll = $lb_parse_list($myBooster['champions'] ?? '');

$myValAgentsAll = $lb_parse_list($myBooster['agents'] ?? '');
$myValAgentsLimited = array_values(array_filter(array_slice($myValAgentsAll, 0, 4)));
$myValAgentsRemaining = max(0, count($myValAgentsAll) - count($myValAgentsLimited));
$myValBannerOrder = in_array($formId, [5, 6, 7, 8, 16], true);

$myValAgentsData = [];
try {
  $myValAgentsJson = (defined('SYS_PATH') ? SYS_PATH : '') . '/public/uploads/lists/val-agents.json';
  if (defined('SYS_PATH') && file_exists($myValAgentsJson)) {
    $myValAgentsData = json_decode(file_get_contents($myValAgentsJson), true) ?? [];
  }
} catch (Throwable $e) {}

$myValRankNames = [0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver', 4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond', 7 => 'Ascendant', 8 => 'Immortal', 9 => 'Radiant'];
$myValRankRaw = trim((string) ($myBooster['val_rank'] ?? ($myBooster['valorant_rank'] ?? '')));
$myValRankTier = 0;
$myValRankDiv = 0;
if ($myValRankRaw !== '') {
  $myValRankParts = explode('|', $myValRankRaw);
  $myValRankTier = (int) ($myValRankParts[0] ?? 0);
  $myValRankDiv = (int) ($myValRankParts[1] ?? 0);
}
$myValRankName = $myValRankNames[$myValRankTier] ?? 'Unranked';
$myValRankDivSuffix = ($myValRankTier > 0 && $myValRankTier < 7 && $myValRankDiv > 0) ? ' ' . (['I', 'II', 'III', 'IV'][$myValRankDiv - 1] ?? '') : '';
$myValRankLabel = trim($myValRankName . $myValRankDivSuffix);
$myValRankIcon = ASSET_URL . '/core/main/img/val/ranks/mini/' . $myValRankTier . '.png';
$myBannerRankName = $myValBannerOrder ? $myValRankLabel : $myRankName;
$myBannerRankIcon = $myValBannerOrder ? $myValRankIcon : $myRankIcon;
$myBannerHasRank = $myValBannerOrder ? ($myValRankTier > 0) : !empty($myRankId);
$myBannerRankTitle = $myValBannerOrder ? 'Valorant Rank' : $myRankName;

// Limits (same feel as client booster-card)
$myRoles = array_values(array_filter(array_slice($myRolesAll, 0, 5)));
$myLangs = array_values(array_filter(array_slice($myLangsAll, 0, 5)));
$myChampsLimited = array_values(array_filter(array_slice($myChampsAll, 0, 4)));
$myChampsRemaining = max(0, count($myChampsAll) - count($myChampsLimited));

// Visuals
$myBoosterName = $myBooster['username'] ?? 'Booster';
$myBoosterIcon = $myBooster['icon'] ?? (defined('ICON_URL') ? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png') : '');
$myBoosterCover = $myBooster['cover'] ?? null;
$myBoosterCover = (!empty($myBoosterCover) ? $myBoosterCover : (ASSET_URL . '/core/main/img/banners/leona.jpeg'));

// Timezone display (booster_profiles.timezone)
$myBoosterTimezone = trim((string) ($myBooster['timezone'] ?? ''));
if (function_exists('util_format_timezone_display')) {
  $myBoosterTimezoneDisplay = (string) util_format_timezone_display($myBoosterTimezone);
} else {
  if ($myBoosterTimezone === '') {
    $myBoosterTimezoneDisplay = 'N/A';
  } else {
    try {
      $dt = new DateTime('now', new DateTimeZone($myBoosterTimezone));
      $myBoosterTimezoneDisplay = $myBoosterTimezone . ' (UTC' . $dt->format('P') . ')';
    } catch (Throwable $e) {
      $myBoosterTimezoneDisplay = $myBoosterTimezone;
    }
  }
}

// Public profile link (optional)
$myCanVisitProfile = (
  $myBoosterId > 0
  && (int) ($myBooster['verified'] ?? 0) === 1
  && (int) ($myBooster['show_profile'] ?? 0) === 1
);
$myBoosterProfileUrl = (defined('BASE_URL') ? (rtrim(BASE_URL, '/') . '/boosters/' . $myBoosterId) : ('/boosters/' . $myBoosterId));

// Show card only when this order is actually assigned to YOU
$showMyBoosterCard = ((int) ($data['booster_id'] ?? 0) === $myBoosterId) && in_array(($data['status'] ?? ''), ['IN_PROGRESS','PAUSED','COMPLETED'], true);


$lb_booster_is_ranked_5s = ((int)($data['form_id'] ?? 0) === RANKED_5S_FORM_ID || (string)($data['type'] ?? '') === 'ranked-5s');
$lb_booster_multi_candidate = in_array((int)($data['form_id'] ?? 0), [4, 19, 29], true);
$lb_booster_required_team_size = ($lb_booster_multi_candidate && function_exists('lb_multi_booster_required_count'))
  ? lb_multi_booster_required_count((int)($data['id'] ?? 0))
  : max(1, (int)($data['boosters'] ?? 1));
$lb_booster_is_multi_booster = $lb_booster_is_ranked_5s
  || ($lb_booster_multi_candidate && $lb_booster_required_team_size > 1);
$lb_booster_ranked_5s_boosters = [];
$lb_booster_ranked_5s_intro_boosters = [];

// Membership rows are authoritative for team visibility. Load them even when
// an older order does not expose the requested team size in the merged view.
if ($lb_booster_multi_candidate) {
  if (!empty($data['ranked_5s_boosters']) && is_array($data['ranked_5s_boosters'])) {
    $lb_booster_ranked_5s_boosters = $data['ranked_5s_boosters'];
  } else {
    try {
      global $db;
      $lb_booster_ranked_5s_boosters = $db->run(
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
      $lb_booster_ranked_5s_boosters = [];
    }
  }

  if (count($lb_booster_ranked_5s_boosters) > 1) {
    $lb_booster_is_multi_booster = true;
  }

  if (empty($lb_booster_ranked_5s_boosters) && $myBoosterId > 0) {
    $lb_booster_ranked_5s_boosters[] = [
      'booster_id' => $myBoosterId,
      'role' => '',
      'slot_no' => 1,
      'username' => $myBoosterName,
      'icon' => $myBoosterIcon,
    ];
  }
}

$lb_booster_build_ranked_5s_intro = function (array $src) use (&$lb_booster_ranked_5s_intro_boosters, $lb_parse_list, $lolranks, $myValRankNames) {
  $bid = (int)($src['booster_id'] ?? $src['id'] ?? 0);
  if ($bid <= 0) return;

  $joined = (array)$src;
  try {
    $b = db_get_row('boosters', ['id' => $bid], 1);
    if (!empty($b)) $joined = array_merge($joined, (array)$b);
    $profile = db_get_row('booster_profiles', ['booster_id' => $bid], 1);
    if (!empty($profile)) $joined = array_merge($joined, (array)$profile);
  } catch (Throwable $e) {}

  $rankLocalId = 0;
  if (!empty($joined['lol_rank'])) {
    $rawRank = (string)$joined['lol_rank'];
    if (str_contains($rawRank, '|')) {
      $parts = explode('|', $rawRank);
      $rankLocalId = (int)($parts[0] ?? 0);
    } else {
      $rankLocalId = (int)$rawRank;
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
  $valNameLocal = $myValRankNames[$valTierLocal] ?? 'Unranked';
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
  $tzDisplay = function_exists('util_format_timezone_display') ? (string)util_format_timezone_display($tz) : ($tz !== '' ? $tz : 'N/A');

  $canVisitProfile = ((int)($joined['verified'] ?? 0) === 1 && (int)($joined['show_profile'] ?? 0) === 1);

  $lb_booster_ranked_5s_intro_boosters[] = [
    'id' => $bid,
    'name' => $joined['username'] ?? ('Booster #' . $bid),
    'icon' => $joined['icon'] ?? (defined('ICON_URL') ? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png') : ''),
    'cover' => !empty($joined['cover']) ? $joined['cover'] : (ASSET_URL . '/core/main/img/banners/leona.jpeg'),
    'profile_url' => defined('BASE_URL') ? (rtrim(BASE_URL, '/') . '/boosters/' . $bid) : ('/boosters/' . $bid),
    'can_visit_profile' => $canVisitProfile,
    'rank_name' => $rankLocalName,
    'rank_icon' => $rankLocalIcon,
    'rank_title' => $rankLocalName,
    'has_rank' => ($rankLocalId > 0),
    'val_rank_name' => $valLabelLocal,
    'val_rank_icon' => $valIconLocal,
    'val_has_rank' => ($valTierLocal > 0),
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

if ($lb_booster_is_multi_booster && !empty($lb_booster_ranked_5s_boosters)) {
  foreach ($lb_booster_ranked_5s_boosters as $b5intro) {
    $lb_booster_build_ranked_5s_intro((array)$b5intro);
  }
}

$lb_show_ranked_5s_booster_profiles = $lb_booster_is_multi_booster
  && !empty($lb_booster_ranked_5s_intro_boosters)
  && in_array(($data['status'] ?? ''), ['IN_PROGRESS','PAUSED','COMPLETED'], true);

$earning = null;
if ($lb_booster_is_ranked_5s) {
  $data['booster_cut'] = 50;
  $earning = function_exists('lb_ranked_5s_booster_earning_cents')
    ? lb_ranked_5s_booster_earning_cents((array)$data)
    : (int)floor((((float)$price * 50) / 100) / max(1, (int)($data['boosters'] ?? 1)));
} elseif ($data['status'] === 'PAID') {
  $earning = calculate_booster_cut($data);
} elseif (in_array($data['status'], ['IN_PROGRESS', 'COMPLETED', 'PAUSED'], true)) {
  $cutPercent = (float) ($data['booster_cut'] ?? 0);
  $earning = ($cutPercent / 100) * (float) $price;
}
if ($lb_booster_is_multi_booster && !$lb_booster_is_ranked_5s) {
  $data['booster_cut'] = 50;
  $requiredTeamSize = function_exists('lb_multi_booster_required_count')
    ? lb_multi_booster_required_count((int)($data['id'] ?? 0))
    : max(1, (int)($data['boosters'] ?? 1));
  $earning = (int)floor((((float)$price * 50) / 100) / max(1, $requiredTeamSize));
}

$cut_meta = null;
$next_change_in = null;
if (!$lb_booster_is_multi_booster && ($data['status'] ?? '') === 'PAID' && function_exists('calculate_booster_cut_meta')) {
  $cut_meta = calculate_booster_cut_meta($data);
  $next_change_in = $cut_meta['next_change_in'] ?? null;
}

$statusKey = strtoupper($data['status'] ?? '');
$statusMap = [
  'COMPLETED' => ['COMPLETED', 'status-completed'],
  'IN_PROGRESS' => ['IN PROGRESS', 'status-inprogress'],
  'PAUSED' => ['PAUSED', 'status-paused'],
  'UNPAID' => ['UNPAID', 'status-unpaid'],
  'PAID' => ['PAID', 'status-paid'],
  'PROCESSING' => ['PROCESSING', 'status-processing'],
];
[$statusText, $statusClass] = $statusMap[$statusKey] ?? [str_replace('_', ' ', $statusKey), 'status-processing'];

// Completion proof uploaded -> waiting for admin approval
$existingCompletionProof = (string)($data['file_url'] ?? '');
if ($existingCompletionProof === '') {
  try {
    $proofRow = db_get_row('order_screenshots', ['order_id' => (int)($data['id'] ?? 0)], 1);
    if ($proofRow) $existingCompletionProof = (string)($proofRow['file_url'] ?? '');
  } catch (Throwable $e) {}
}
$hasCompletionProof = !empty($existingCompletionProof);
$isWaitingForApproval = ((int)($data['waiting_for_approval'] ?? 0) === 1 || $hasCompletionProof) && strtoupper($data['status'] ?? '') !== 'COMPLETED';

// ── Tracking-based auto-complete / completion proof rules ──
$trackingForms    = ['rank', 'win', 'arena', 'match', 'placement', 'normal', 'clash', 'pro-games'];
$formType         = strtolower((string)($data['form_type'] ?? $data['type'] ?? ''));

// These forms are always manually approved by admins, so boosters must attach proof.
$manualApprovalScreenshotFormIds = [5, 6, 7, 8, 16, 21, 22, 23, 24, 25, 27, 15, 17, 29];
$forceManualApproval = in_array($formId, $manualApprovalScreenshotFormIds, true);

// LoL Rank Boost solo orders also always need proof.
$isSoloRankBoost = ($formId === 1 && !$isDuo);

$trackingAvailable = in_array($formType, $trackingForms, true) && !$riotMissing;
// Riot API tracking is available for LoL orders with a Riot ID on a trackable form type.
$canTrack          = $trackingAvailable && $gameKey === 'lol';

// Completion screenshots are always required before completing an order.
// Riot tracking still verifies supported orders automatically, but the screenshot
// is collected up front as completion proof and chargeback protection.
$requiresCompletionScreenshot = true;
$screenshotRequired = true;
$showTrackingFallbackScreenshot = false;

$lbRanked5sTotalGames = 0;
$lbRanked5sCompletedGames = 0;
if (!empty($lb_booster_is_ranked_5s)) {
  foreach (['matches', 'games', 'total_games', 'amount'] as $lbR5GamesKey) {
    if (isset($data[$lbR5GamesKey]) && (int)$data[$lbR5GamesKey] > 0) {
      $lbRanked5sTotalGames = (int)$data[$lbR5GamesKey];
      break;
    }
  }
  try {
    $lbR5ProgressRow = db_get_row('order_progress', ['order_id' => (int)($data['id'] ?? 0)], 1) ?: [];
    foreach (['matches_completed', 'games_completed', 'completed_games'] as $lbR5DoneKey) {
      if (isset($lbR5ProgressRow[$lbR5DoneKey])) {
        $lbRanked5sCompletedGames = max($lbRanked5sCompletedGames, (int)$lbR5ProgressRow[$lbR5DoneKey]);
      }
    }
    $lbRanked5sCompletedGames = max($lbRanked5sCompletedGames, (int)($lbR5ProgressRow['wins'] ?? 0) + (int)($lbR5ProgressRow['losses'] ?? 0));
  } catch (Throwable $e) {}
}

/** Assignment date formatting for drop modal
 * Prefer claimed_at, but some orders (e.g., customer-requested booster / direct assignment)
 * may not have claimed_at filled. In that case we fall back to paid_at, then created_at.
 */
$lb_assignment_source = 'claimed_at';
$lb_claimed_at_raw = trim((string)($data['claimed_at'] ?? ''));

try {
  if ($lb_claimed_at_raw === '' || $lb_claimed_at_raw === '0000-00-00 00:00:00') {
    $orderRowForAssign = db_get_row('orders', ['id' => (int)$data['id']], 1);
    if ($orderRowForAssign) {
      $c = trim((string)($orderRowForAssign['claimed_at'] ?? ''));
      $p = trim((string)($orderRowForAssign['paid_at'] ?? ''));
      $cr = trim((string)($orderRowForAssign['created_at'] ?? ''));
      if ($c !== '' && $c !== '0000-00-00 00:00:00') { $lb_claimed_at_raw = $c; $lb_assignment_source = 'claimed_at'; }
      elseif ($p !== '' && $p !== '0000-00-00 00:00:00') { $lb_claimed_at_raw = $p; $lb_assignment_source = 'paid_at'; }
      elseif ($cr !== '' && $cr !== '0000-00-00 00:00:00') { $lb_claimed_at_raw = $cr; $lb_assignment_source = 'created_at'; }
    }
  }
} catch (Exception $e) {
  // ignore
}

$lb_claimed_at_backend = '';
$lb_claimed_at_display = '';
if ($lb_claimed_at_raw !== '' && $lb_claimed_at_raw !== '0000-00-00 00:00:00') {
  $ts = strtotime($lb_claimed_at_raw);
  if ($ts !== false) {
    $lb_claimed_at_backend = date('Y-m-d H:i:s', $ts);
    $lb_claimed_at_display = date('d.m.Y H:i:s', $ts);
  }
}



/**
 * Drop-order cooldown (20 minutes)
 *
 * IMPORTANT:
 * - Cooldown timestamp is tracked via orders.change_booster_last_request_at (marker).
 * - Banner must only be shown to the booster who submitted the latest drop request for this order,
 *   otherwise a newly assigned booster would inherit the cooldown message.
 */
$dropCooldownSeconds = 20 * 60;
$dropCooldownActive = false;
$dropCooldownRemaining = 0;
$dropCooldownRemainingMins = 0;

try {
  $orderIdForCooldown = (int)$data['id'];

  // Read timestamp marker from order
  $orderRowForCooldown = db_get_row('orders', ['id' => $orderIdForCooldown], 1);

  if ($orderRowForCooldown) {
    $lastReqAt = $orderRowForCooldown['change_booster_last_request_at'] ?? null;

    // Get booster_id of latest drop request (ordered by id to avoid dependency on created_at)
    global $db;
    $lastDropBoosterId = null;

    try {
      $lastDrop = $db->run(
        "SELECT booster_id FROM drop_requests WHERE order_id = ? ORDER BY id DESC LIMIT 1",
        $orderIdForCooldown
      );

      if (is_array($lastDrop) && !empty($lastDrop)) {
        $lastDropBoosterId = (int)($lastDrop[0]['booster_id'] ?? 0);
      }
    } catch (Exception $e) {
      $lastDropBoosterId = null;
    }

    // Only show cooldown to the booster who sent the latest request
    if ($lastDropBoosterId !== null && $lastDropBoosterId === (int)BOOSTER_DATA['id'] && !empty($lastReqAt)) {
      $lastTs = strtotime((string)$lastReqAt);

      if ($lastTs !== false) {
        $elapsed = time() - $lastTs;

        if ($elapsed >= 0 && $elapsed < $dropCooldownSeconds) {
          $dropCooldownActive = true;
          $dropCooldownRemaining = $dropCooldownSeconds - $elapsed;
          $dropCooldownRemainingMins = (int)ceil($dropCooldownRemaining / 60);
        }
      }
    }
  }
} catch (Exception $e) {
  // ignore
}
/** Options check (FIX: empty("0") problem) */
/** Options check (FIX + hide unselected options) */
$optionKeys = [
  'roles',
  'champions',
  'agents',
  'vpn_country',
  'is_priority',
  'is_streaming',
  'is_solo_only',
  'is_bonus_win',
  'is_coaching',
  'is_voice',
  'is_hidden_duo',
  'is_undercover_winrate',
  'is_moderate_kda'
];

$boolOptions = ['is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_coaching', 'is_voice', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda'];

$lb_option_descriptions = [
  'roles' => ['short' => 'Selected roles only.', 'full' => 'The booster should play only the roles selected by the customer whenever possible.'],
  'champions' => ['short' => 'Selected champions only.', 'full' => 'The booster should use only the champions selected by the customer whenever possible.'],
  'agents' => ['short' => 'Selected agents only.', 'full' => 'The booster should use only the selected Valorant agents whenever possible.'],
  'flash_position' => ['short' => 'Preferred Flash side.', 'full' => 'The booster should keep Flash on the position selected by the customer.'],
  'vpn_country' => ['short' => 'Specific VPN region.', 'full' => 'The booster should connect from the selected VPN country for extra account consistency.'],
  'is_priority' => ['short' => 'Finished much faster.', 'full' => 'Your order will be finished about twice as fast as a normal order.'],
  'is_streaming' => ['short' => 'Games are streamed privately.', 'full' => 'Your booster will privately stream the games while playing on the order.'],
  'is_solo_only' => ['short' => 'Booster plays solo only.', 'full' => 'Your booster will play solo only on your account and will not duo with any other account.'],
  'is_bonus_win' => ['short' => 'One extra win included.', 'full' => 'Once the target rank is reached, the booster will win one extra match as a bonus.'],
  'is_offline_mode' => ['short' => 'Stay less visible in client.', 'full' => 'The booster should appear offline in the game client whenever possible while working on the order.'],
  'is_coaching' => ['short' => 'Voice call while playing.', 'full' => 'The booster will join a call, give feedback, and help the customer play better.'],
  'is_voice' => ['short' => 'Voice communication enabled.', 'full' => 'The customer wants voice communication during the order.'],
  'is_hidden_duo' => ['short' => 'More discreet duo queue.', 'full' => 'The booster will play with the customer using more than one account to make the duo setup look more natural.'],
  'is_undercover_winrate' => ['short' => 'Keep winrate less suspicious.', 'full' => 'The booster will try to keep the win rate at 65% or below so the account does not look suspicious.'],
  'is_moderate_kda' => ['short' => 'Keep KDA more natural.', 'full' => 'The booster will keep the average KDA at 4.5 or below over the whole order.']
];

$lb_get_option_description = function (string $option, string $label = '') use ($lb_option_descriptions): array {
  $fallbackLabel = trim($label) !== '' ? trim($label) : 'Option';
  return $lb_option_descriptions[$option] ?? ['short' => $fallbackLabel . '.', 'full' => $fallbackLabel];
};

/**
 * Skip rules (wie vorher) in eine Funktion, damit wir sie überall identisch nutzen
 */
$lb_skip_option = function (string $option) use ($data, $isRiotOnlyForm): bool {
  $game = strtolower(trim((string)($data['game'] ?? '')));
  $isLol = in_array($game, ['lol', 'league-of-legends', 'lol-classic', 'league-of-legends-classic'], true);
  if (in_array($option, ['flash_position', 'is_offline_mode'], true) && !$isLol)
    return true;
  if ($data['game'] === 'val' && $option === 'flash_position')
    return true;
  if ($option === 'vpn_country' && ($data['is_duo'] || in_array((int) ($data['form_id'] ?? 0), [15, 16, 25], true)))
    return true;
  if (($data['is_duo'] || $isRiotOnlyForm) && ($option === 'flash_position' || $option === 'is_offline_mode'))
    return true;
  return false;
};

/**
 * “Ist diese Option wirklich ausgewählt / gesetzt?”
 * - Bool: nur (int) == 1
 * - Array: nur wenn nicht leer
 * - String: nicht leer, nicht [], nicht null, nicht "-", nicht "none/n/a/no/false"
 */
$lb_option_selected = function (string $option) use ($data, $boolOptions): bool {
  if (!array_key_exists($option, $data))
    return false;

  // Bool options: only when enabled
  if (in_array($option, $boolOptions, true)) {
    return isset($data[$option]) && (int) $data[$option] === 1;
  }

  $v = $data[$option];

  if (is_array($v)) {
    return !empty($v);
  }

  $s = trim((string) $v);
  if ($s === '' || $s === '[]')
    return false;

  $low = strtolower($s);
  if (in_array($low, ['null', 'none', 'n/a', 'na', '-', 'false', 'no'], true))
    return false;

  return true;
};

$hasExtraOptions = false;
foreach ($optionKeys as $option) {
  if ($lb_skip_option($option))
    continue;
  if ($lb_option_selected($option)) {
    $hasExtraOptions = true;
    break;
  }
}


/** Screens */
$isClaimScreen = in_array($data['status'], ['PAID', 'PROCESSING'], true);
$isChatScreen = in_array($data['status'], ['IN_PROGRESS', 'PAUSED', 'COMPLETED'], true);

$claimedAtValue = trim((string)($data['claimed_at'] ?? ''));
$isMultiBoosterOrder = in_array((int)($data['form_id'] ?? 0), [4, 19, 29], true)
  && max(1, (int)($data['boosters'] ?? 1)) > 1;
$isRanked5sMember = ((int)($data['form_id'] ?? 0) === 29 || $isMultiBoosterOrder)
  && ((int)($data['is_ranked_5s_member'] ?? 0) === 1
      || (int)($data['booster_id'] ?? 0) === (int)$myBoosterId);

// Defensive membership lookup for additional Ranked 5s boosters.
if (((int)($data['form_id'] ?? 0) === 29 || $isMultiBoosterOrder) && !$isRanked5sMember && $myBoosterId > 0) {
  try {
    $lbR5Membership = db_get_row('order_boosters', [
      'order_id' => (int)($data['id'] ?? 0),
      'booster_id' => $myBoosterId,
      'status' => 'ACTIVE'
    ], 1);
    if (!empty($lbR5Membership)) {
      $isRanked5sMember = true;
      $data['is_ranked_5s_member'] = 1;
      $data['booster_id'] = $myBoosterId;
      $data['ranked_5s_role'] = $lbR5Membership['role'] ?? ($data['ranked_5s_role'] ?? '');
      $data['ranked_5s_slot_no'] = (int)($lbR5Membership['slot_no'] ?? ($data['ranked_5s_slot_no'] ?? 0));
      $data['claimed_at'] = $lbR5Membership['claimed_at'] ?? ($data['claimed_at'] ?? null);
      if (in_array((string)($data['status'] ?? ''), ['PAID', 'PROCESSING'], true)) {
        $data['status'] = 'IN_PROGRESS';
        $isClaimScreen = false;
        $isChatScreen = true;
      }
    }
  } catch (Throwable $e) {}
}
$isPendingRequestedForMe = !$isRanked5sMember
  && in_array(($data['status'] ?? ''), ['PAID', 'PROCESSING', 'IN_PROGRESS'], true)
  && (int)($data['booster_id'] ?? 0) === (int)$myBoosterId
  && ($claimedAtValue === '' || $claimedAtValue === '0000-00-00 00:00:00');
$isOpenClaimableOrder = !$isRanked5sMember
  && in_array(($data['status'] ?? ''), ['PAID', 'PROCESSING'], true)
  && empty($data['booster_id']);

/** Render Notes Card (CLIENT-like) */
$renderOrderNotes = function () use ($notes) { ?>
  <div class="card">
    <div class="card-header">
      <h4 class="card-header-title">Notes</h4>
    </div>

    <div class="card-body">
      <?php if (!empty($notes)): ?>
        <div class="lb-notes-list">
          <?php foreach ($notes as $note): ?>
            <?php
            $type = strtolower((string) ($note['type'] ?? 'other'));
            $badge = 'Other';
            $badgeIcon = 'fa-note-sticky';
            if ($type === 'booster') {
              $badge = 'Admin Note';
              $badgeIcon = 'fa-gamepad-modern';
            }
            if ($type === 'client' || $type === 'customer') {
              $badge = 'Client';
              $badgeIcon = 'fa-user';
            }
            if ($type === 'admin') {
              $badge = 'Admin Note';
              $badgeIcon = 'fa-shield';
            }
            if ($type === 'system') {
              $badge = 'System';
              $badgeIcon = 'fa-gear';
            }
            ?>

            <div class="lb-note-item">
              <div class="lb-note-ico">
                <i class="fa-duotone <?= $badgeIcon ?>"></i>
              </div>

              <div class="lb-note-content">
                <div class="lb-note-text">
                  <?= $note['order_note'] /* bewusst unverändert (wie vorher), falls HTML gespeichert ist */ ?>
                </div>

                <div class="lb-note-meta">
                  <span class="lb-note-chip"><?= $badge ?></span>
                  <?php if (!empty($note['created_at'])): ?>
                    <span class="lb-note-chip"><?= htmlspecialchars((string) $note['created_at']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="lb-notes-empty">
          <div class="lb-notes-empty-ico">
            <i class="fa-duotone fa-circle-info" style="font-size:1.25rem;"></i>
          </div>
          <div class="lb-notes-empty-title">No Notes Found</div>
          <div class="lb-notes-empty-sub">
            Notes from client/admin/booster will appear here.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php }; ?>

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

        // Manual start: timer only runs after booster clicks Start Timer.
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

<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Orders #' . $data['id'] . ' - Booster Area | LoLBoost.gg']]) ?>

<div class="order-page-wrap">

  <!-- HEADER (CLIENT-LIKE) -->
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
              echo '<img class="boost-form-svg" src="' . htmlspecialchars($svgBaseUrl . '/' . $safe, ENT_QUOTES) . '" alt="">';
            } else {
              $fa = $icon !== '' ? $icon : 'fa-bolt';
              echo '<i class="fa-duotone ' . htmlspecialchars($fa, ENT_QUOTES) . '"></i>';
            }
          ?>
          <?php $lbHeaderGameIcon = util_game_icon_url((string)($data['game'] ?? '')); ?>
          <?php if ($lbHeaderGameIcon !== ''): ?>
            <img src="<?= htmlspecialchars($lbHeaderGameIcon, ENT_QUOTES) ?>" alt="" style="position:absolute;right:-5px;bottom:-5px;width:20px;height:20px;object-fit:contain;border-radius:6px;background:#11131a;border:2px solid #11131a;">
          <?php endif; ?>
        </div>

        <div class="lb-head__title w-100">
          <div class="lb-head__title-row">
            <div class="d-flex align-items-start justify-content-between w-100">
              <div class="d-flex gap-2">
                <h1 class="lb-head__h1">
                  <?= util_format_boost_overview($data['game'], $data['type'], $data) ?>
                </h1>
                <span class="lb-head__id d-none d-lg-inline">#<?= (int) $data['id'] ?></span>
              </div>
              <?php if (!empty($review)): ?>
                <button class="btn btn-sm btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#view_review_md">
                  <i class="fa-duotone fa-star-half-stroke me-1"></i> View Review
                </button>
              <?php endif; ?>
            </div>
          </div>

          <div class="lb-head__sub">
            <span class="lb-status <?= $statusClass ?>">
              <span class="lb-status__dot"></span>
              <?= $statusText ?>
            </span>
          </div>
        </div>
      </div>

      <div class="lb-head__actions">
        <!-- KEEP YOUR EXISTING ACTIONS (logic) -->
        <?php if ($data['status'] == "IN_PROGRESS"): ?>
          <!-- Desktop: keep the "Order Actions" button AND open the dropdown like before, while still toggling the actions card -->
          <div class="dropdown d-none d-lg-inline-block">
            <button type="button"
              id="lbOrderActionsBtn"
              class="btn btn-white btn-sm d-inline-flex align-items-center gap-2 lb-action-btn"
              aria-expanded="false">
              <i class="fa-duotone fa-sliders"></i>
              <span class="fw-semibold">Order Actions</span>
              <i class="fa-solid fa-chevron-down" style="font-size:.8rem; opacity:.7;"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end mt-1" id="lbOrderActionsDropdown">
              <span class="dropdown-header">Actions</span>

              <a class="dropdown-item" href="#" data-id="<?= $data['id'] ?>" data-action="poke_client">
                <i class="fa-duotone fa-bell-on dropdown-item-icon"></i> Notify Client
              </a>

              <?php if (BOOSTER_DATA['drop_tokens'] > 0): ?>
                <div>
                  <hr class="dropdown-divider">
                </div>
                <?php if (!empty($dropCooldownActive)): ?>
                  <span class="dropdown-item lb-dropdown-static" aria-disabled="true">
                    <i class="fa-duotone fa-clock dropdown-item-icon"></i> Admin team checking (<?= (int)$dropCooldownRemainingMins ?>m)
                  </span>
                <?php else: ?>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#request_drop_md">
                    <i class="fa-duotone fa-minus-hexagon dropdown-item-icon"></i> Drop Order
                  </a>
                <?php endif; ?>
              <?php endif; ?>

              <div>
                <hr class="dropdown-divider">
              </div>

              <?php if (!$isWaitingForApproval): ?>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#complete_order_md">
                  <i class="fa-duotone fa-square-check dropdown-item-icon"></i> Complete Order
                </a>
              <?php else: ?>
                <span class="dropdown-item lb-dropdown-static" aria-disabled="true">
                  <i class="fa-duotone fa-clock dropdown-item-icon"></i> Waiting for approval
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div class="dropdown nav-scroller-dropdown d-lg-none">
            <button type="button"
              class="btn btn-white btn-icon btn-sm d-inline-flex d-lg-none lb-action-btn lb-action-btn--icon"
              data-bs-toggle="dropdown" aria-expanded="false">

              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end mt-1">
              <span class="dropdown-header">Actions</span>

              <a class="dropdown-item" href="#" data-id="<?= $data['id'] ?>" data-action="poke_client">
                <i class="fa-duotone fa-bell-on dropdown-item-icon"></i> Notify Client
              </a>

              <?php if (BOOSTER_DATA['drop_tokens'] > 0): ?>
                <div>
                  <hr class="dropdown-divider">
                </div>
                <?php if (!empty($dropCooldownActive)): ?>
                <span class="dropdown-item lb-dropdown-static" aria-disabled="true">
                  <i class="fa-duotone fa-clock dropdown-item-icon"></i> Admin team checking (<?= (int)$dropCooldownRemainingMins ?>m)
                </span>
              <?php else: ?>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#request_drop_md">
                  <i class="fa-duotone fa-minus-hexagon dropdown-item-icon"></i> Drop Order
                </a>
              <?php endif; ?>
              <?php endif; ?>

              <div>
                <hr class="dropdown-divider">
              </div>

              <?php if (!$isWaitingForApproval): ?>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#complete_order_md">
                  <i class="fa-duotone fa-square-check dropdown-item-icon"></i> Complete Order
                </a>
              <?php else: ?>
                <span class="dropdown-item lb-dropdown-static" aria-disabled="true">
                  <i class="fa-duotone fa-clock dropdown-item-icon"></i> Waiting for approval
                </span>
              <?php endif; ?>

            </div>
          </div>

        <?php elseif ($isPendingRequestedForMe): ?>
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary btn-sm border-0" data-bs-toggle="modal" data-bs-target="#accept_requested_order_md">
              <i class="fa-duotone fa-check me-2"></i> Ready to play
            </button>
            <button class="btn btn-white btn-sm border" data-bs-toggle="modal" data-bs-target="#decline_requested_order_md">
              <i class="fa-duotone fa-xmark me-2"></i> Decline
            </button>
          </div>
        <?php elseif ($isOpenClaimableOrder): ?>
          <button class="btn btn-white btn-sm border" data-bs-toggle="modal" data-bs-target="#claim_order_md">
            <i class="fa-duotone fa-play me-2"></i> Claim Order
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Meta pills -->
    <div class="lb-head__meta">
      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Order</div>
        <div class="lb-meta-pill__v">#<?= (int) $data['id'] ?></div>
      </div>

      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Client</div>
        <?php $lbClientId = (int)($client['id'] ?? ($data['client_id'] ?? ($data['customer_id'] ?? 0))); ?>
        <div class="lb-meta-pill__v lb-client-meta">
          <span><?= htmlspecialchars($client['username'] ?? 'Unknown') ?></span>
          <?= lb_client_presence_badge_html($lbClientId) ?>
        </div>
      </div>


      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Earning</div>
        <div class="lb-meta-pill__v" id="js-earning">
          <div class="lb-earn-inline">
            <span class="lb-earn-amount" id="js-earning-amount"><?= is_null($earning) ? '-' : ($currency . util_format_price_display($earning)) ?></span>

            <span class="lb-chip lb-chip-timer" id="js-earning-timer-chip" title="Next cut change" <?= ($next_change_in === null ? 'style="display:none"' : '') ?>>
              <i class="fa-duotone fa-timer"></i>
              <span class="lb-timer-val" id="js-earning-timer" data-seconds="<?= (int) ($next_change_in ?? 0) ?>">
                <?php if ($next_change_in !== null) { $mm = str_pad((string) floor($next_change_in / 60), 2, '0', STR_PAD_LEFT); $ss = str_pad((string) ($next_change_in % 60), 2, '0', STR_PAD_LEFT); echo $mm . ':' . $ss; } ?>
              </span>
            </span>
          </div>
        </div>
      </div>

      <?php $disc = util_format_discount_display($data['id']); ?>
      <?php if (!empty($disc) && trim(strip_tags($disc)) !== '-'): ?>
        <div class="lb-meta-pill">
          <div class="lb-meta-pill__k">Coupon</div>
          <div class="lb-meta-pill__v">🏷️Special Price applied</div>
        </div>
      <?php endif; ?>


    </div>
  </div>


<?php if ($isClaimScreen): ?>

    <!-- CLAIM SCREEN: Overview / Options + Notes below -->
    <div class="row g-4 order-layout">

      <div class="<?= $hasExtraOptions ? 'col-xl-7 col-lg-7' : 'col-12' ?>">

        <!-- OVERVIEW (client-like) -->
        <div class="card lb-overview-card">
          <div class="card-header">
            <h4 class="card-header-title">Overview</h4>
          </div>

          <div class="card-body">
            <?php
            // pick first existing non-empty key from $data (allows "0"!)
            $lb_pick = function (array $keys) use ($data) {
              foreach ($keys as $k) {
                if (!array_key_exists($k, $data))
                  continue;
                $v = $data[$k];

                // arrays -> skip empty
                if (is_array($v)) {
                  if (empty($v))
                    continue;
                  $v = json_encode($v);
                }

                $s = trim((string) $v);
                if ($s === '' || $s === '[]' || strtolower($s) === 'null')
                  continue;

                return $s; // IMPORTANT: "0" stays valid
              }
              return null;
            };

            $startLp = $lb_pick(['start_lp', 'lp_start', 'start_lp_amount', 'starting_lp', 'start_points', 'startPoints']);
            $currentLp = $lb_pick(['current_lp', 'lp_current', 'current_lp_amount', 'current_points', 'currentPoints']);
            $endLp = $lb_pick(['end_lp', 'lp_end', 'end_lp_amount', 'target_lp', 'end_points', 'endPoints']);

            // In LoL/TFT only Master+ (tier 8) is LP based — below that the desired rank is
            // a division, so end_lp holds a leftover/0 value that must not be shown.
            $lbLpGame = strtolower(trim((string)($data['game'] ?? '')));
            if (in_array($lbLpGame, ['lol', 'league-of-legends', 'lol_classic', 'lol-classic', 'tft', 'teamfight-tactics'], true)
                && (int)($data['end_tier'] ?? 0) < 8) {
              $endLp = null;
            }
            $lpGain = $lb_pick(['lp_gain', 'lp_gained', 'gained_lp', 'gain_lp']);

            // fallback: compute gain if missing and numbers exist
            if ($lpGain === null && $startLp !== null && $currentLp !== null) {
              $a = (float) str_replace(',', '.', preg_replace('/[^0-9\.\-\,]/', '', $startLp));
              $b = (float) str_replace(',', '.', preg_replace('/[^0-9\.\-\,]/', '', $currentLp));
              $lpGain = (string) ($b - $a);
            }
            ?>
            <ul class="lb-ov-grid">

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

              <?php if (!$isCoachingForm): ?>
                <li class="lb-ov-item">
                  <div class="lb-ov-ico">🤝</div>
                  <div class="lb-ov-label">Play With Booster</div>
                  <div class="lb-ov-value"><?= util_format_option('is_duo', $data['is_duo'])[1] ?></div>
                </li>
              <?php endif; ?>

              <?php foreach (lb_order_view_purchase_fields($data) as $lbPurchaseField): ?>
                <li class="lb-ov-item">
                  <div class="lb-ov-ico"><i class="<?= esc($lbPurchaseField['icon']) ?>"></i></div>
                  <div class="lb-ov-label"><?= htmlspecialchars($lbPurchaseField['label'], ENT_QUOTES) ?></div>
                  <div class="lb-ov-value"><?= htmlspecialchars($lbPurchaseField['value'], ENT_QUOTES) ?></div>
                </li>
              <?php endforeach; ?>


              <?php if (!$isCounterStrikeOrder && $startLp !== null): ?>
                <li class="lb-ov-item">
                  <div class="lb-ov-ico"><i class="fa-duotone fa-signal-bars"></i></div>
                  <div class="lb-ov-label">Start LP</div>
                  <div class="lb-ov-value"><?= htmlspecialchars($startLp) ?></div>
                </li>
              <?php endif; ?>

              <?php if ($currentLp !== null): ?>
                <li class="lb-ov-item lb-ov-item--inline">
                  <div class="lb-ov-ico"><i class="fa-duotone fa-chart-line"></i></div>
                  <div class="lb-ov-label">Current LP</div>
                  <div class="lb-ov-value"><?= htmlspecialchars($currentLp) ?></div>
                </li>
              <?php endif; ?>

              <?php if ($lpGain !== null): ?>
                <li class="lb-ov-item">
                  <div class="lb-ov-ico"><i class="fa-duotone fa-arrow-trend-up"></i></div>
                  <div class="lb-ov-label">LP Gain</div>
                  <div class="lb-ov-value"><?= htmlspecialchars($lpGain) ?></div>
                </li>
              <?php endif; ?>

              <?php if ($endLp !== null): ?>
                <li class="lb-ov-item lb-ov-item--inline">
                  <div class="lb-ov-ico"><i class="fa-duotone fa-flag-checkered"></i></div>
                  <div class="lb-ov-label">End LP</div>
                  <div class="lb-ov-value"><?= htmlspecialchars($endLp) ?></div>
                </li>
              <?php endif; ?>
            </ul>
          </div>

        </div>
      </div>

      <?php if ($hasExtraOptions): ?>
        <div class="col-xl-5 col-lg-5">

          <!-- OPTIONS (client-like list) -->
          <div class="card lb-overview-card">
            <div class="card-header">
              <h4 class="card-header-title">Options</h4>
            </div>

            <div class="card-body">
              <ul class="lb-ov-grid">
                <?php foreach ($optionKeys as $option): ?>
                  <?php
                  $boolOptions = ['is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_coaching', 'is_voice', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda'];

                  if (!isset($data[$option]))
                    continue;

                  // Bool: nur anzeigen wenn true (1)
                  if (in_array($option, $boolOptions, true)) {
                    if ((int) $data[$option] !== 1)
                      continue;
                  } else {
                    // Text/Array: sauber prüfen
                    $v = $data[$option];
                    if (is_array($v)) {
                      if (empty($v))
                        continue;
                    } else {
                      $v = trim((string) $v);
                      if ($v === '' || $v === '[]' || strtolower($v) === 'null')
                        continue;
                    }
                  }
                  ?>


                  <?php
                  if ($lb_skip_option($option))
                    continue;

                  $ds_opt = util_format_option($option, $data[$option]);
                  $ico = util_format_option_emoji($option);

                  $rawVal = trim(strip_tags((string) $ds_opt[1]));
                  $valUp = strtoupper($rawVal);

                  $boolOptions = ['is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_coaching', 'is_voice', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda'];
                  $isBoolOpt = in_array($option, $boolOptions, true);

                  $inline = false;
                  $valueHtml = $ds_opt[1];
                  $manyIconOption = in_array($option, ['champions', 'agents', 'roles'], true);

                  if ($isBoolOpt) {
                    if ($valUp === 'YES' || $rawVal === '1') {
                      $valueHtml = '<span class="lb-yes-pill"><span class="lb-yes-dot"></span> YES</span>';
                    } elseif ($valUp === 'NO' || $rawVal === '0') {
                      $valueHtml = '<span class="lb-no-pill"><span class="lb-no-dot"></span> NO</span>';
                    }
                  }
                  ?>

                  <?php $optionDesc = $lb_get_option_description($option, $ds_opt[0]); ?>
                  <li class="lb-ov-item lb-ov-item--option lb-option-<?= htmlspecialchars($option, ENT_QUOTES) ?> <?= $inline ? 'lb-ov-item--inline' : '' ?> <?= $manyIconOption ? 'lb-ov-item--icons' : '' ?>">
                    <div class="lb-ov-ico"><?= $ico ?></div>
                    <div class="lb-ov-label-row">
                      <div class="lb-ov-label"><?= $ds_opt[0] ?></div>
                      <span class="lb-option-help" tabindex="0" data-tooltip="<?= htmlspecialchars($optionDesc['full'], ENT_QUOTES) ?>" aria-label="<?= htmlspecialchars($optionDesc['full'], ENT_QUOTES) ?>">
                        <i class="fa-duotone fa-circle-info"></i>
                      </span>
                    </div>
                    <div class="lb-ov-desc"><?= htmlspecialchars($optionDesc['short']) ?></div>
                    <div class="lb-ov-value"><?= $valueHtml ?></div>
                  </li>

                <?php endforeach; ?>

              </ul>
            </div>
          </div>

        </div>
      <?php endif; ?>

      <?php if ($hasNotes): ?>
        <div class="col-12">
          <?php $renderOrderNotes(); ?>
        </div>
      <?php endif; ?>

    </div>

  <?php else: ?>

    <!-- CHAT SCREEN -->
    <div class="row g-4 order-layout">

      <!-- LEFT -->
      <div class="col-xl-7 col-lg-7">

        
<?php if (!empty($dropCooldownActive)): ?>
  <div class="lb-state-alert lb-state-alert--success mb-3" role="alert">
    <div class="lb-state-alert__left">
      <div class="lb-state-alert__ico">
        <i class="fa-duotone fa-clock"></i>
      </div>
      <div class="lb-state-alert__txt">
        <div class="lb-state-alert__title">Admin team checking</div>
        <div class="lb-state-alert__sub">
          Your drop request was submitted. Please wait about <strong><?= (int)$dropCooldownRemainingMins ?> minute(s)</strong> before sending a new one.
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($isWaitingForApproval): ?>
          <div class="lb-state-alert lb-state-alert--success mb-3" role="alert">
            <div class="lb-state-alert__left">
              <div class="lb-state-alert__ico">
                <i class="fa-duotone fa-clock"></i>
              </div>
              <div class="lb-state-alert__txt">
                <div class="lb-state-alert__title">Waiting for approval</div>
                <div class="lb-state-alert__sub">Completion submitted — pending verification. <span class="opacity-75">ETA 30–60 mins.</span></div>
              </div>
            </div>
          </div>
        <?php endif; ?>



        <?php if ($data['status'] === 'PAUSED'): ?>
          <div class="lb-state-alert mb-3" role="alert">
            <div class="lb-state-alert__left">
              <div class="lb-state-alert__ico">
                <i class="fa-duotone fa-circle-pause"></i>
              </div>
              <div class="lb-state-alert__txt">
                <div class="lb-state-alert__title">Order paused</div>
                <div class="lb-state-alert__sub">This order is currently paused</div>
              </div>
            </div>
          </div>
        <?php endif; ?>


        <?php if ((int)($data['form_id'] ?? 0) === RANKED_5S_FORM_ID): ?>
          <div class="lb-discord-banner mb-3">
            <div class="lb-discord-banner__left">
              <svg class="lb-discord-banner__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36"><path fill="#5865f2" d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/></svg>
              <div class="lb-discord-banner__text">
                <strong>Ranked 5s Discord</strong>
                <ul class="lb-discord-banner__steps">
                  <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">1</span><span>Join the Discord server</span></li>
                  <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">2</span><span>Invite the customer to the voice channel, <a href="https://lolboost.gg/streaming" target="_blank" rel="noopener">lolboost.gg/streaming</a></span></li>
                  <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">3</span><span>Play the Ranked 5s games together in voice</span></li>
                  <li class="lb-discord-banner__step lb-discord-banner__step--warn"><span class="lb-discord-banner__step-no">4</span><span>No Discord DM, no adding, website chat only</span></li>
                </ul>
              </div>
            </div>
            <a class="lb-discord-banner__btn" href="https://lolboost.gg/streaming" target="_blank" rel="noopener">
              Join Server
            </a>
          </div>
        <?php endif; ?>

        <?php if (!empty($lb_show_ranked_5s_booster_profiles)): ?>
          <?php if (count($lb_booster_ranked_5s_intro_boosters) > 1): ?>
            <div class="lb-r5s-booster-tabs mb-3" role="tablist" aria-label="Order boosters">
              <?php foreach ($lb_booster_ranked_5s_intro_boosters as $idx => $introTab): ?>
                <?php $tabLane = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], (string)($introTab['lane'] ?? '')); ?>
                <button type="button" class="lb-r5s-booster-tab <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-booster-tab="<?= (int)$idx ?>">
                  <img src="<?= htmlspecialchars($introTab['icon']) ?>" alt="">
                  <span><?= htmlspecialchars($introTab['name']) ?></span>
                  <?php if ($tabLane !== ''): ?><small><?= htmlspecialchars($tabLane) ?></small><?php endif; ?>
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php foreach ($lb_booster_ranked_5s_intro_boosters as $idx => $intro): ?>
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
              $introChampsLimited = $intro['champs_limited'];
              $introChampsRemaining = $intro['champs_remaining'];
            ?>
            <div class="lb-r5s-booster-panel <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-booster-panel="<?= (int)$idx ?>">
              <div class="card booster-intro-card lb-r5s-team-card mb-4">
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

                        <div class="d-flex flex-wrap gap-2">
                          <?php if ($introHasRank): ?>
                            <div class="booster-rank-pill" title="<?= htmlspecialchars($introRankTitle) ?>">
                              <img src="<?= htmlspecialchars($introRankIcon) ?>" alt="Rank" class="lb-rank-img">
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
                    </div>

                    <div class="booster-intro-right">
                      <?php if ($introCanVisitProfile): ?>
                        <a class="visit-profile-btn" href="<?= htmlspecialchars($introProfileUrl) ?>" target="_blank" rel="noopener">
                          <i class="fa-duotone fa-user"></i>
                          <span>View Profile</span>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="booster-intro-cards">
                    <div class="booster-intro-block">
                      <div class="booster-intro-label">CHAMPIONS</div>
                      <div class="booster-intro-champs">
                        <?php if (!empty($introChampsLimited)): ?>
                          <?php foreach ($introChampsLimited as $champion): ?>
                            <img class="champ" src="<?= LOL_CHAMP_URL . '/' . rawurlencode((string)$champion) . '.png' ?>" alt="<?= htmlspecialchars($champion) ?>" title="<?= htmlspecialchars($champion) ?>">
                          <?php endforeach; ?>
                          <?php if ($introChampsRemaining > 0): ?><span class="more">+<?= $introChampsRemaining ?></span><?php endif; ?>
                        <?php else: ?>
                          <span class="na">N/A</span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="booster-intro-block">
                      <div class="booster-intro-label">LANE</div>
                      <div class="booster-intro-roles">
                        <?php if (!empty($introRoles)): ?>
                          <?php foreach ($introRoles as $role): ?>
                            <?php $roleLabel = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], (string)$role); ?>
                            <span class="role-pill" title="<?= htmlspecialchars($roleLabel) ?>">
                              <img src="<?= ASSET_URL . '/core/main/img/lol/roles/' . rawurlencode((string)$role) . '.png' ?>" alt="<?= htmlspecialchars($roleLabel) ?>">
                            </span>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="na">N/A</span>
                        <?php endif; ?>
                      </div>
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

<?php elseif (!empty($showMyBoosterCard)): ?>
<?php
$lb_isDuoPassFormB     = ((int)($data['form_id'] ?? 0) === 27);
$lb_isRanked5sBannerB  = ((int)($data['form_id'] ?? 0) === RANKED_5S_FORM_ID);
$lb_isStreamingBannerB = (!$isCoachingForm) && (!$lb_isRanked5sBannerB) && (!empty($data['is_streaming']) && (int)$data['is_streaming'] === 1);
$lb_isVoiceBannerB     = (!$isCoachingForm) && (!$lb_isRanked5sBannerB) && (!$lb_isStreamingBannerB) && ((!empty($data['is_voice']) && (int)$data['is_voice'] === 1) || $lb_isDuoPassFormB);
$lb_showDiscordBannerB = (!$lb_isRanked5sBannerB) && ($isCoachingForm || $lb_isStreamingBannerB || $lb_isVoiceBannerB);
if ($lb_showDiscordBannerB):
  $lb_discordUrl = ($lb_isDuoPassFormB && !$lb_isStreamingBannerB && !$isCoachingForm && !$lb_isRanked5sBannerB)
    ? 'https://lolboost.gg/coaching'
    : 'https://lolboost.gg/streaming';
?>
<div class="lb-discord-banner mb-3">
  <div class="lb-discord-banner__left">
    <svg class="lb-discord-banner__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36"><path fill="#5865f2" d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/></svg>
    <div class="lb-discord-banner__text">
      <strong><?= $lb_isRanked5sBannerB ? 'Ranked 5s Discord' : ($lb_isStreamingBannerB ? 'Streaming Order' : (($lb_isDuoPassFormB && !$isCoachingForm) ? 'Duo Pass Voice Order' : 'Coaching Order')) ?></strong>
      <ul class="lb-discord-banner__steps">
        <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">1</span><span>Join server</span></li>
        <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">2</span><span><?= $lb_isRanked5sBannerB ? 'Invite the customer to the voice channel' : 'Send customer invite' ?>, <a href="https://lolboost.gg/streaming" target="_blank" rel="noopener">lolboost.gg/streaming</a></span></li>
        <li class="lb-discord-banner__step"><span class="lb-discord-banner__step-no">3</span><span><?= $lb_isRanked5sBannerB ? 'Play the Ranked 5s games together in voice' : (($lb_isDuoPassFormB && !$isCoachingForm) ? 'Complete duo pass session in voice' : 'Complete coaching / streaming') ?></span></li>
        <li class="lb-discord-banner__step lb-discord-banner__step--warn"><span class="lb-discord-banner__step-no">4</span><span>No Discord DM, no adding, website chat only</span></li>
      </ul>
    </div>
  </div>
  <a class="lb-discord-banner__btn" href="<?= htmlspecialchars($lb_discordUrl) ?>" target="_blank" rel="noopener">
    Join Server
  </a>
</div>
<?php endif; ?>
  <!-- BOOSTER CARD (Your profile, editable) -->
  <div class="card booster-intro-card lb-booster-edit-card"
       data-lb-roles="<?= htmlspecialchars(implode('|', $myRolesAll)) ?>"
       data-lb-languages="<?= htmlspecialchars(implode('|', $myLangsAll)) ?>"
       data-lb-champions="<?= htmlspecialchars(implode('|', $myChampsAll)) ?>"
       data-lb-timezone="<?= htmlspecialchars($myBoosterTimezone) ?>"
       data-lb-rank-tier="<?= (int) $myRankTier ?>"
       data-lb-rank-div="<?= (int) $myRankDiv ?>">

    <div class="booster-intro-bg" id="lbBoosterCover" style="background-image:url('<?= htmlspecialchars($myBoosterCover) ?>');"></div>
    <button type="button" class="lb-edit-pen lb-edit-pen--cover" data-lb-edit="cover" title="Edit banner">
      <i class="fa-solid fa-pen"></i>
    </button>

    <div class="card-body booster-intro-body">

      <div class="booster-intro-top">
        <div class="booster-intro-left">
          <div class="booster-intro-avatar lb-editable" id="lbBoosterAvatar" data-lb-edit="icon">
            <span class="booster-intro-glow"></span>
            <img src="<?= htmlspecialchars($myBoosterIcon) ?>" alt="Booster Avatar">
            <button type="button" class="lb-edit-pen lb-edit-pen--avatar" data-lb-edit="icon" title="Edit avatar">
              <i class="fa-solid fa-pen"></i>
            </button>
          </div>

          <div class="booster-intro-main">
            <div class="booster-intro-name">
              <span><?= htmlspecialchars($myBoosterName) ?></span>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <?php if ($myBannerHasRank): ?>
                <div class="booster-rank-pill <?= $myValBannerOrder ? '' : 'lb-editable-pill' ?>" id="lbBoosterRankPill" <?= $myValBannerOrder ? '' : 'data-lb-edit="rank"' ?> title="<?= htmlspecialchars($myValBannerOrder ? $myBannerRankTitle : 'Edit rank') ?>">
                  <img src="<?= htmlspecialchars($myBannerRankIcon) ?>" alt="Rank" class="lb-rank-img">
                  <span class="lb-rank-name"><?= htmlspecialchars($myBannerRankName) ?></span>
                  <?php if (!$myValBannerOrder): ?>
                    <button type="button" class="lb-edit-pen lb-edit-pen--pill" data-lb-edit="rank" aria-label="Edit rank">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="booster-rank-pill lb-editable-pill" id="lbBoosterTimezonePill" data-lb-edit="timezone" title="Edit timezone">
                <i class="fa-duotone fa-clock"></i>
                <span class="lb-tz-name"><?= htmlspecialchars($myBoosterTimezoneDisplay) ?></span>
                <button type="button" class="lb-edit-pen lb-edit-pen--pill" data-lb-edit="timezone" aria-label="Edit timezone">
                  <i class="fa-solid fa-pen"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="booster-intro-right">
          <?php if (!empty($myCanVisitProfile)): ?>
            <a class="visit-profile-btn" href="<?= htmlspecialchars($myBoosterProfileUrl) ?>" target="_blank" rel="noopener">
              <i class="fa-duotone fa-user"></i>
              <span>View Profile</span>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="booster-intro-cards">
        <div class="booster-intro-block <?= $myValBannerOrder ? '' : 'lb-editable' ?>" id="<?= $myValBannerOrder ? 'lbBoosterAgents' : 'lbBoosterChamps' ?>" <?= $myValBannerOrder ? '' : 'data-lb-edit="champions"' ?>>
          <?php if (!$myValBannerOrder): ?>
            <button type="button" class="lb-edit-pen" data-lb-edit="champions" title="Edit champions"><i class="fa-solid fa-pen"></i></button>
          <?php endif; ?>
          <div class="booster-intro-label"><?= $myValBannerOrder ? 'AGENTS' : 'CHAMPIONS' ?></div>
          <div class="booster-intro-champs">
            <?php if ($myValBannerOrder): ?>
              <?php if (!empty($myValAgentsLimited)): ?>
                <?php foreach ($myValAgentsLimited as $agent):
                  $agentKey = trim((string) $agent);
                  $agentIcon = $myValAgentsData[$agentKey]['icon'] ?? '';
                  $agentName = $myValAgentsData[$agentKey]['name'] ?? $agentKey; ?>
                  <?php if ($agentIcon): ?>
                    <img class="champ" src="<?= htmlspecialchars($agentIcon) ?>" alt="<?= htmlspecialchars($agentName) ?>" title="<?= htmlspecialchars($agentName) ?>">
                  <?php else: ?>
                    <span class="booster-intro-tag"><?= htmlspecialchars($agentName) ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($myValAgentsRemaining > 0): ?>
                  <?php
                    $myValAgentsTooltip = [];
                    foreach ($myValAgentsAll as $agentTooltipRaw) {
                      $agentTooltipKey = trim((string) $agentTooltipRaw);
                      if ($agentTooltipKey === '') continue;
                      $myValAgentsTooltip[] = [
                        'name' => (string)($myValAgentsData[$agentTooltipKey]['name'] ?? $agentTooltipKey),
                        'icon' => (string)($myValAgentsData[$agentTooltipKey]['icon'] ?? ''),
                      ];
                    }
                  ?>
                  <span class="more js-lb-champs-tooltip" data-title="All agents" data-items='<?= htmlspecialchars(json_encode($myValAgentsTooltip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>+<?= $myValAgentsRemaining ?></span>
                <?php endif; ?>
              <?php else: ?>
                <span class="na">N/A</span>
              <?php endif; ?>
            <?php else: ?>
              <?php if (!empty($myChampsLimited)): ?>
                <?php foreach ($myChampsLimited as $champion): ?>
                  <img class="champ" src="<?= LOL_CHAMP_URL . '/' . $champion . '.png' ?>" alt="<?= htmlspecialchars($champion) ?>">
                <?php endforeach; ?>
                <?php if ($myChampsRemaining > 0): ?>
                  <?php
                    $myChampsTooltip = [];
                    foreach ($myChampsAll as $champTooltipRaw) {
                      $champTooltip = trim((string) $champTooltipRaw);
                      if ($champTooltip === '') continue;
                      $myChampsTooltip[] = [
                        'name' => $champTooltip,
                        'icon' => rtrim(LOL_CHAMP_URL, '/') . '/' . rawurlencode($champTooltip) . '.png',
                      ];
                    }
                  ?>
                  <span class="more js-lb-champs-tooltip" data-title="All champions" data-items='<?= htmlspecialchars(json_encode($myChampsTooltip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>+<?= $myChampsRemaining ?></span>
                <?php endif; ?>
              <?php else: ?>
                <span class="na">N/A</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="booster-intro-block <?= $myValBannerOrder ? '' : 'lb-editable' ?>" id="<?= $myValBannerOrder ? 'lbBoosterValorantRank' : 'lbBoosterRoles' ?>" <?= $myValBannerOrder ? '' : 'data-lb-edit="roles"' ?>>
          <?php if (!$myValBannerOrder): ?>
            <button type="button" class="lb-edit-pen" data-lb-edit="roles" title="Edit lanes"><i class="fa-solid fa-pen"></i></button>
          <?php endif; ?>
          <div class="booster-intro-label"><?= $myValBannerOrder ? 'VALORANT RANK' : 'LANES' ?></div>
          <?php if ($myValBannerOrder): ?>
            <div class="booster-intro-rank-mini">
              <?php if ($myValRankTier > 0): ?>
                <img class="rank-mini-icon" src="<?= htmlspecialchars($myValRankIcon) ?>" alt="<?= htmlspecialchars($myValRankLabel) ?>">
                <span><?= htmlspecialchars($myValRankLabel) ?></span>
              <?php else: ?>
                <span class="na">N/A</span>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="booster-intro-roles">
              <?php if (!empty($myRoles)): ?>
                <?php foreach ($myRoles as $role): ?>
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

        <div class="booster-intro-block lb-editable" id="lbBoosterLangs" data-lb-edit="languages">
          <button type="button" class="lb-edit-pen" data-lb-edit="languages" title="Edit languages"><i class="fa-solid fa-pen"></i></button>
          <div class="booster-intro-label">LANGUAGES</div>
          <div class="booster-intro-langs">
            <?php if (!empty($myLangs)): ?>
              <?php foreach ($myLangs as $language):
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

  <!-- Inline edit modal -->
  <div class="modal fade" id="lbBoosterEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content lb-edit-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="lbBoosterEditTitle">Edit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger d-none" id="lbBoosterEditError"></div>

          <form id="lbBoosterEditForm">
            <input type="hidden" name="field" id="lbEditField" value="">

            <div class="mb-3 d-none" data-lb-edit-pane="file">
              <label class="form-label" id="lbEditFileLabel">Upload image</label>
              <input type="file" class="form-control" name="image_url" id="lbEditFileInput" accept="image/*">
              <div class="form-text">PNG, JPG, WEBP</div>
            </div>

            <div class="mb-3 d-none" data-lb-edit-pane="timezone">
              <label class="form-label">Timezone</label>
              <select class="form-select" id="lbEditTimezoneSelect" name="timezone" autocomplete="off">
                <?php
                if (function_exists('util_load_timezones_select')) {
                  echo util_load_timezones_select($myBoosterTimezone);
                } else {
                  $tzList = \DateTimeZone::listIdentifiers();
                  echo '<option value="" ' . ($myBoosterTimezone === '' ? 'selected' : '') . '>N/A (not set)</option>';
                  foreach ($tzList as $tz) {
                    $sel = ($tz === $myBoosterTimezone) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($tz) . '" ' . $sel . '>' . htmlspecialchars($tz) . '</option>';
                  }
                }
                ?>
              </select>
              <small class="text-muted d-block mt-1">If not set, customers will see <strong>N/A</strong>.</small>
            </div>

            <div class="mb-3 d-none" data-lb-edit-pane="rank">
              <div class="row g-2">
                <div class="col-8">
                  <label class="form-label">Rank</label>
                  <select class="form-select" id="lbEditRankTier" name="rank_tier">
                    <option value="0">Unranked</option>
                    <option value="7">Diamond</option>
                    <option value="8">Master</option>
                    <option value="9">Grandmaster</option>
                    <option value="10">Challenger</option>
                  </select>
                </div>
                <div class="col-4">
                  <label class="form-label">Division</label>
                  <select class="form-select" id="lbEditRankDiv" name="rank_div">
                    <option value="0">—</option>
                    <option value="4">I</option>
                    <option value="3">II</option>
                    <option value="2">III</option>
                    <option value="1">IV</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mb-3 d-none" data-lb-edit-pane="champions">
              <label class="form-label">Champions</label>
              <div class="tom-select-custom">
                <select class="js-select form-select" id="lbEditChampionsSelect" name="champions[]" multiple autocomplete="off">
                  <?= function_exists('util_load_champions_select') ? util_load_champions_select($myChampsAll) : '' ?>
                </select>
              </div>
            </div>

            <div class="mb-3 d-none" data-lb-edit-pane="roles">
              <label class="form-label">Lanes</label>
              <div class="tom-select-custom">
                <select class="js-select form-select" id="lbEditRolesSelect" name="roles[]" multiple autocomplete="off">
                  <?= function_exists('util_load_roles_select') ? util_load_roles_select($myRolesAll) : '' ?>
                </select>
              </div>
            </div>

            <div class="mb-3 d-none" data-lb-edit-pane="languages">
              <label class="form-label">Languages</label>
              <div class="tom-select-custom">
                <select class="js-select form-select" id="lbEditLanguagesSelect" name="languages[]" multiple autocomplete="off">
                  <?= function_exists('util_load_languages_select') ? util_load_languages_select($myLangsAll) : '' ?>
                </select>
              </div>
              <small class="text-muted d-block mt-1">Select the languages you can support.</small>
            </div>

            <!-- Fallback text editor (kept for future fields) -->
            <div class="mb-3 d-none" data-lb-edit-pane="text">
              <label class="form-label" id="lbEditTextLabel">Value</label>
              <input type="text" class="form-control" name="value" id="lbEditTextInput" value="">
              <div class="form-text" id="lbEditTextHelp"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="lbBoosterEditSave">Save</button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($hasNotes): ?>
          <?php $renderOrderNotes(); ?>
        <?php endif; ?>

        <!-- CHAT -->
        <div class="card order-chat-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-header-title mb-0">Order Chat</h4>

            <?php if (in_array($data['status'], ['IN_PROGRESS', 'PAUSED'], true)): ?>
              <a class="btn btn-sm btn-primary lb-notify-btn lb-notify-btn--header" href="#" data-id="<?= $data['id'] ?>"
                data-action="poke_client" aria-label="Notify Customer">
                <i class="fa-duotone fa-bell-on me-1"></i> <span class="lb-notify-text">Notify Customer</span>
              </a>
            <?php endif; ?>
          </div>

          <?php if (!empty($data['is_bonus_win']) && in_array($data['status'], ['IN_PROGRESS', 'PAUSED'], true)): ?>
            <div class="lb-bonus-reminder" id="lbBonusReminder" data-order="<?= (int) $data['id'] ?>">
              <div class="lb-bonus-reminder__left">
                <span class="lb-bonus-reminder__icon"><i class="fa-duotone fa-sparkles"></i></span>
                <div class="lb-bonus-reminder__txt">
                  <div class="lb-bonus-reminder__k">BONUS WIN</div>
                  <div class="lb-bonus-reminder__q">BONUS WIN DONE?</div>
                </div>
              </div>

              <div class="lb-bonus-reminder__actions">
                <button type="button" class="btn btn-sm btn-white border" data-lb-bonus="later">Later</button>
                <button type="button" class="btn btn-sm btn-primary" data-lb-bonus="done">Done</button>
              </div>
            </div>
          <?php endif; ?>

          <div class="card-body chat-bg chat chat-content" id="chat_messages"></div>

          <?php
  // Chat stays open for 24h after completion, then locks automatically.
  $isCompleted = (($data['status'] ?? '') === 'COMPLETED');
  $completedAtStr = $data['completed_at'] ?? null; // expected DB format: YYYY-MM-DD HH:MM:SS
  $completedAtTs = $completedAtStr ? strtotime($completedAtStr) : null;

  $chatGraceSeconds = 24 * 60 * 60;
  $chatGraceActive = false;
  $chatSecondsLeft = 0;

  if ($isCompleted && $completedAtTs) {
    $chatSecondsLeft = max(0, ($completedAtTs + $chatGraceSeconds) - time());
    $chatGraceActive = ($chatSecondsLeft > 0);
    $chatLocked = !$chatGraceActive;
  } elseif ($isCompleted) {
    // If we don't have a completion timestamp, lock to be safe.
    $chatLocked = true;
  } else {
    $chatLocked = false;
  }
?>

          <div class="card-footer">
<?php if ($chatLocked): ?>
            <div class="lb-chat-locked-footer" role="status" aria-live="polite">
              <div class="lb-chat-locked-footer__icon" aria-hidden="true">
                <i class="fa-duotone fa-lock"></i>
              </div>
              <div class="lb-chat-locked-footer__text">
                <div class="lb-chat-locked-footer__title">Chat locked</div>
                <div class="lb-chat-locked-footer__sub">Order completed — chat closed (24h window expired).</div>
              </div>
            </div>
          <?php else: ?>
<?php if (!empty($chatGraceActive)): ?>
            <div class="lb-chat-grace-banner" role="status" aria-live="polite" data-seconds-left="<?= (int)$chatSecondsLeft ?>">
              <div class="lb-chat-grace-banner__icon" aria-hidden="true">
                <i class="fa-duotone fa-clock"></i>
              </div>
              <div class="lb-chat-grace-banner__text">
                <div class="lb-chat-grace-banner__title">Chat closing soon</div>
                <div class="lb-chat-grace-banner__sub">
                  Chat closing in <span class="lb-chat-grace-countdown">24 hours</span>. Make sure to ask the customer to leave a review. Have you said goodbye?
                </div>
              </div>
            </div>
<?php endif; ?>
<form class="row gx-2 gy-2 ajax-form align-items-center lb-chat-form mt-2 mt-sm-0" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" <?= $chatLocked ? 'data-chat-locked="1"' : '' ?>>
              <input type="hidden" name="action" value="booster_order_chat_send">
              <input type="hidden" name="order_id" value="<?= (int)$data['id'] ?>">

              <div class="col">
                <input type="text" id="lbChatMessage" name="message" class="form-control" placeholder="<?= $chatLocked ? 'Chat is locked — order completed' : 'Type your message' ?>"
                  <?= $chatLocked ? 'disabled' : '' ?>>
              </div>
<?php if (in_array($data['status'], ['IN_PROGRESS', 'PAUSED'], true)): ?>
                <!-- mobile notify button template (injected into the actions row on xs) -->
                <div class="d-none" id="lbChatNotifyMobileTmpl">
                  <a class="btn btn-sm btn-white border lb-notify-btn lb-notify-btn--mobile" href="#"
                    data-id="<?= (int)$data['id'] ?>" data-action="poke_client" aria-label="Notify Customer">
                    <i class="fa-duotone fa-bell-on"></i>
                  </a>
                </div>
              <?php endif; ?>

              <div class="col-auto">
                <div class="lb-chat-actions d-flex gap-2 justify-content-end">
                  <?php if (in_array($data['status'], ['IN_PROGRESS', 'PAUSED'], true)): ?>
                    <div class="d-sm-none" id="lbChatNotifyMobileSlot"></div>
                  <?php endif; ?>
                <input type="file" class="d-none" id="lbChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif" <?= $chatLocked ? 'disabled' : '' ?>>

                <button type="button" class="btn btn-sm btn-secondary btn-chat-icon" id="lbChatAttachBtn" <?= $chatLocked ? 'disabled' : '' ?> aria-label="Attach image">
                  <i class="fa-duotone fa-paperclip"></i>
                </button>

                <button type="button" class="btn btn-sm btn-secondary lb-emoji-btn d-none d-md-inline-flex" id="lbEmojiBtn" aria-label="Emojis" title="Emojis">
                  <i class="fa-regular fa-face-smile"></i>
                </button>

                <button type="submit" class="btn btn-sm btn-secondary btn-chat-icon" id="lbChatSendBtn" <?= $chatLocked ? 'disabled' : '' ?> aria-label="Send">
                  <span class="indicator-label">
                    <?php if ($chatLocked): ?>
                      <i class="fa-duotone fa-lock"></i>
                    <?php else: ?>
                      <i class="fa-duotone fa-paper-plane"></i>
                    <?php endif; ?>
                  </span>
                  <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                </button>
                </div>
              </div>




              <!-- ERROR (moved under message input) -->
              <div class="col-12">
                <div class="lb-chat-error text-danger small mt-1 d-none" id="lbChatError"></div>
              </div>

              <!-- Policy warning (moved under message input on mobile) -->
              <div class="col-12">
                <div class="lb-chat-policy-warn d-none mt-2" id="lbChatPolicyWarn" role="alert">
                  <div class="lb-chat-policy-warn__icon"><i class="fa-duotone fa-triangle-exclamation"></i></div>
                  <div class="lb-chat-policy-warn__body">
                    <div class="lb-chat-policy-warn__title">Keep everything on-site</div>
                    <div class="lb-chat-policy-warn__text">
                      Don’t ask customers to DM, add on Discord, or pay outside the website. All communication must stay in this chat.
                      <span class="lb-chat-policy-warn__matches" id="lbChatPolicyWarnMatches"></span>
                    </div>
                  </div>
                </div>
                <div class="lb-chat-policy-warn__preview small mt-1" id="lbChatPolicyWarnPreview"></div>
              </div>


              

              <div class="col-12">
<div class="lb-chat-attach-preview d-none mt-2" id="lbChatPreview">
                  <div class="lb-chat-attach-preview__thumb">
                    <img src="" alt="attachment preview" id="lbChatPreviewImg">
                  </div>
                  <div class="lb-chat-attach-preview__meta">
                    <div class="lb-chat-attach-preview__title">Image ready to send</div>
                    <div class="lb-chat-attach-preview__name" id="lbChatPreviewName"></div>
                  </div>
                  <button type="button" class="lb-chat-attach-preview__remove" id="lbChatRemoveBtn" aria-label="Remove attachment">
                    <i class="fa-duotone fa-xmark"></i>
                  </button>
                </div>
              </div>
            

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

</form>
          <?php endif; ?>
          </div>
        </div>

        <!-- Chat image modal (prevents opening a new tab) -->
        <div class="modal fade" id="lbChatImageModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content lb-img-modal">
              <div class="modal-header">
                <h5 class="modal-title">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body p-0">
                <img src="" alt="Chat image" id="lbChatImageModalImg" class="lb-img-modal__img">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-xl-5 col-lg-5">

        <?php
        $acc = db_get_row('order_accounts', ['order_id' => $data['id']]);

        $riotId = trim((string) ($acc['ign'] ?? ''));
        $login = trim((string) ($acc['login'] ?? ''));
        $password = trim((string) ($acc['password'] ?? ''));

        $riotMissing = ($riotId === '');
        $loginMissing = ($login === '');
        $passMissing = ($password === '');

        $lb_mask = function ($s, $keepStart = 2, $keepEnd = 2) {
          $s = (string) $s;
          $len = mb_strlen($s);
          if ($len <= ($keepStart + $keepEnd))
            return str_repeat('•', max(4, $len));
          return mb_substr($s, 0, $keepStart)
            . str_repeat('•', max(4, $len - ($keepStart + $keepEnd)))
            . mb_substr($s, -$keepEnd);
        };
        ?>

        <?php
        $lb_op_progress_data = is_array($data['progress'] ?? null) ? $data['progress'] : [];
        $lb_op_is_classic_rank = in_array(strtolower(trim((string)($data['game'] ?? ''))), ['lol_classic', 'lol-classic', 'league-of-legends-classic'], true);
        $lb_op_format_rank = static function ($tier, $division, $lp) use ($lb_op_is_classic_rank): string {
          if ($lb_op_is_classic_rank && is_numeric($tier)) {
            return util_lol_classic_rank_name((int)$tier);
          }
          $tier = trim((string) ($tier ?? ''));
          $division = trim((string) ($division ?? ''));
          $lp_val = ($lp === null || $lp === '') ? null : (int) $lp;
          if ($tier === '')
            return 'Unranked';
          $label = ucfirst(strtolower($tier));
          if ($division !== '')
            $label .= ' ' . $division;
          if ($lp_val !== null)
            $label .= ' · ' . $lp_val . ' LP';
          return $label;
        };
        $lb_op_tier_id_map = ['IRON' => 1, 'BRONZE' => 2, 'SILVER' => 3, 'GOLD' => 4, 'PLATINUM' => 5, 'EMERALD' => 6, 'DIAMOND' => 7, 'MASTER' => 8, 'GRANDMASTER' => 9, 'CHALLENGER' => 10];
        $lb_op_start_tier_raw = strtoupper(trim((string) ($lb_op_progress_data['start_tier'] ?? '')));
        $lb_op_current_tier_raw = strtoupper(trim((string) ($lb_op_progress_data['current_tier'] ?? '')));
        $lb_op_start_tier_id = $lb_op_is_classic_rank && is_numeric($lb_op_start_tier_raw) ? (int)$lb_op_start_tier_raw : ($lb_op_tier_id_map[$lb_op_start_tier_raw] ?? 0);
        $lb_op_current_tier_id = $lb_op_is_classic_rank && is_numeric($lb_op_current_tier_raw) ? (int)$lb_op_current_tier_raw : ($lb_op_tier_id_map[$lb_op_current_tier_raw] ?? 0);
        $lb_op_start_rank_img = $lb_op_is_classic_rank ? util_lol_classic_rank_img($lb_op_start_tier_id) : ASSET_URL . '/core/main/img/lol/ranks/max/' . $lb_op_start_tier_id . '.png';
        $lb_op_current_rank_img = $lb_op_is_classic_rank ? util_lol_classic_rank_img($lb_op_current_tier_id) : ASSET_URL . '/core/main/img/lol/ranks/max/' . $lb_op_current_tier_id . '.png';
        $lb_op_start_rank_text = $lb_op_format_rank($lb_op_progress_data['start_tier'] ?? null, $lb_op_progress_data['start_division'] ?? null, $lb_op_progress_data['start_lp'] ?? null);
        $lb_op_current_rank_text = $lb_op_format_rank($lb_op_progress_data['current_tier'] ?? null, $lb_op_progress_data['current_division'] ?? null, $lb_op_progress_data['current_lp'] ?? null);
        $lb_op_last_sync_text = !empty($lb_op_progress_data['last_sync_at']) ? (string) $lb_op_progress_data['last_sync_at'] : 'Never';
        $lb_op_wins_text = isset($lb_op_progress_data['wins']) ? (string) ((int) $lb_op_progress_data['wins']) : '0';
        $lb_op_losses_text = isset($lb_op_progress_data['losses']) ? (string) ((int) $lb_op_progress_data['losses']) : '0';
        $lb_op_wins_total = (int) ($lb_op_progress_data['wins'] ?? 0);
        $lb_op_losses_total = (int) ($lb_op_progress_data['losses'] ?? 0);
        $lb_op_record_games = $lb_op_wins_total + $lb_op_losses_total;
        $lb_op_winrate_pct = $lb_op_record_games > 0 ? number_format(($lb_op_wins_total / $lb_op_record_games) * 100, 1) . '%' : '–';
        $lb_op_winrate_bar_pct = $lb_op_record_games > 0 ? number_format(($lb_op_wins_total / $lb_op_record_games) * 100, 1) : '0';
        $lb_op_wr_bar_class = ($lb_op_record_games > 0 && ($lb_op_wins_total / $lb_op_record_games) >= 0.6) ? 'lb-op-wr-bar-fill--good' : '';
        $lb_op_record_tone = 'text-muted';
        if ($lb_op_record_games > 0) {
          $lb_op_record_tone = (($lb_op_wins_total / $lb_op_record_games) >= 0.6) ? 'text-success' : '';
        }
        $lb_op_form_id = (int) ($data['form_id'] ?? 0);
        $lb_op_is_win_boost_form = ($lb_op_form_id === 2);
        $lb_op_is_placements_form = ($lb_op_form_id === 3);
        $lb_op_is_pro_games_form = in_array($lb_op_form_id, [26, 35], true);
        $lb_op_is_duo_pass_form  = ($lb_op_form_id === 27);
        $lb_op_is_count_form = ($lb_op_is_win_boost_form || $lb_op_is_placements_form || $lb_op_is_pro_games_form);
        $lb_op_base_target = (int) ($data['matches'] ?? 0);
        // Win Boost (boost_forms.id = 2): progress is based on net wins.
        // Example: 3 wins / 3 losses = 0 wins done; target stays at the ordered wins.
        $lb_op_dynamic_target = $lb_op_base_target;
        $lb_op_count_played = $lb_op_is_win_boost_form
          ? max(0, $lb_op_wins_total - $lb_op_losses_total)
          : (($lb_op_is_pro_games_form || $lb_op_is_placements_form) ? $lb_op_record_games : $lb_op_wins_total);
        $lb_op_count_pct = ($lb_op_dynamic_target > 0) ? min(100.0, round(($lb_op_count_played / $lb_op_dynamic_target) * 100, 1)) : 0;
        $lb_op_count_label = $lb_op_is_placements_form ? 'Placements Played' : ($lb_op_is_pro_games_form ? 'Games Played' : 'Wins Played');
        $lb_op_count_done = ($lb_op_count_pct >= 100);

        $lb_op_duo_booked_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['booked_seconds'] : max(0, ((int)($data['hours'] ?? 0)) * 3600);
        $lb_op_duo_used_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['used_seconds'] : 0;
        $lb_op_duo_remaining_seconds = !empty($lb_duo_timer) ? (int) $lb_duo_timer['remaining_seconds'] : max(0, $lb_op_duo_booked_seconds - $lb_op_duo_used_seconds);
        $lb_op_duo_pct = $lb_op_duo_booked_seconds > 0 ? min(100.0, round(($lb_op_duo_used_seconds / $lb_op_duo_booked_seconds) * 100, 1)) : 0;
        $lb_op_duo_status = !empty($lb_duo_timer) ? (string) $lb_duo_timer['status_label'] : 'Not Started';
        $lb_op_duo_played_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($lb_op_duo_used_seconds) : (string) $lb_op_duo_used_seconds;
        $lb_op_duo_target_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($lb_op_duo_booked_seconds) : (string) $lb_op_duo_booked_seconds;
        $lb_op_duo_remaining_text = function_exists('lb_duo_timer_human') ? lb_duo_timer_human($lb_op_duo_remaining_seconds) : (string) $lb_op_duo_remaining_seconds;
        $lb_op_ign = trim((string) ($acc['ign'] ?? $data['ign'] ?? ''));
        $lb_op_booster_ign = trim((string) ($lb_op_progress_data['booster_ign'] ?? ''));
        $lb_op_is_duo_order = ((int)($data['is_duo'] ?? 0) === 1);
        ?>

        <?php if ($gameKey === 'lol'): // Riot API tracking (LoL only) ?>
        <!-- ORDER PROGRESS CARD -->
        <div class="card mb-4 lb-op-card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <div class="lb-op-header-ico">
                <i class="fa-duotone fa-chart-line-up"></i>
              </div>
              <h4 class="card-header-title mb-0">Order Progress</h4>
            </div>
            <?php if (!$lb_op_is_duo_pass_form): ?>
              <button type="button" class="lb-op-refresh-btn" id="refreshProgressBtn" aria-label="Refresh progress"
                title="Sync with Riot API">
                <i class="fa-duotone fa-arrows-rotate"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="card-body">

            <?php if ($lb_op_is_duo_pass_form): ?>
              <!-- Duo Pass time progress -->
              <div class="lb-op-count-row">
                <div class="lb-op-count-box">
                  <div class="lb-op-count-val" id="riotProgressPlayed"><?= esc($lb_op_duo_played_text) ?></div>
                  <div class="lb-op-count-label">Time Played</div>
                </div>
                <div class="lb-op-count-sep">/</div>
                <div class="lb-op-count-box">
                  <div class="lb-op-count-val lb-op-count-val--target" id="riotProgressTarget"><?= esc($lb_op_duo_target_text) ?></div>
                  <div class="lb-op-count-label">Target</div>
                </div>
              </div>
              <div class="lb-op-count-progress mb-2">
                <div class="lb-op-count-progress-fill<?= $lb_op_duo_pct >= 100 ? ' lb-op-count-progress-fill--done' : '' ?>"
                  id="riotProgressCountBar" style="width: <?= esc($lb_op_duo_pct) ?>%"></div>
              </div>
              <div class="lb-op-count-rank">
                <div class="lb-op-count-rank-copy">
                  <div class="lb-op-count-rank-kicker">Time Left</div>
                  <div class="lb-op-count-rank-name"><?= esc($lb_op_duo_remaining_text) ?> · <?= esc($lb_op_duo_status) ?></div>
                </div>
              </div>
            <?php elseif ($lb_op_is_count_form): ?>
              <!-- Count progress: Win Boost / Pro Games -->
              <div class="lb-op-count-row">
                <div class="lb-op-count-box">
                  <div class="lb-op-count-val" id="riotProgressPlayed"><?= esc($lb_op_count_played) ?></div>
                  <div class="lb-op-count-label"><?= esc($lb_op_count_label) ?></div>
                </div>
                <div class="lb-op-count-sep">/</div>
                <div class="lb-op-count-box">
                  <div class="lb-op-count-val lb-op-count-val--target" id="riotProgressTarget"><?= esc($lb_op_dynamic_target) ?></div>
                  <div class="lb-op-count-label">Target</div>
                </div>
              </div>
              <div class="lb-op-count-progress mb-2">
                <div class="lb-op-count-progress-fill<?= $lb_op_count_done ? ' lb-op-count-progress-fill--done' : '' ?>"
                  id="riotProgressCountBar" style="width: <?= esc($lb_op_count_pct) ?>%"></div>
              </div>
              <?php if ($lb_op_is_win_boost_form): ?>
                <div class="lb-op-count-rank">
                  <img class="lb-op-count-rank-img" id="riotProgressCurrentRankImg"
                    src="<?= htmlspecialchars($lb_op_current_rank_img, ENT_QUOTES) ?>" alt="">
                  <div class="lb-op-count-rank-copy">
                    <div class="lb-op-count-rank-kicker">Current Rank</div>
                    <div class="lb-op-count-rank-name" id="riotProgressCurrentRank"><?= esc($lb_op_current_rank_text) ?></div>
                  </div>
                </div>
              <?php endif; ?>
            <?php else: ?>

            <!-- Rank comparison -->
            <div class="lb-op-rank-row">
              <div class="lb-op-rank-box">
                <img class="lb-op-rank-img" id="riotProgressStartRankImg"
                  src="<?= htmlspecialchars($lb_op_start_rank_img, ENT_QUOTES) ?>" alt="">
                <div class="lb-op-rank-name" id="riotProgressStartRank"><?= esc($lb_op_start_rank_text) ?></div>
                <div class="lb-op-rank-label">Start</div>
              </div>
              <div class="lb-op-rank-arrow">
                <i class="fa-duotone fa-arrow-right-long"></i>
              </div>
              <div class="lb-op-rank-box lb-op-rank-box--current">
                <img class="lb-op-rank-img" id="riotProgressCurrentRankImg"
                  src="<?= htmlspecialchars($lb_op_current_rank_img, ENT_QUOTES) ?>" alt="">
                <div class="lb-op-rank-name" id="riotProgressCurrentRank"><?= esc($lb_op_current_rank_text) ?></div>
                <div class="lb-op-rank-label">Current</div>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!$lb_op_is_duo_pass_form): ?>
            <!-- W / L / WR stats -->
            <div class="lb-op-stats">
              <div class="lb-op-stat lb-op-stat--win">
                <div class="lb-op-stat-val" id="riotProgressWins"><?= esc($lb_op_wins_text) ?></div>
                <div class="lb-op-stat-lbl">Wins</div>
              </div>
              <div class="lb-op-stat lb-op-stat--loss">
                <div class="lb-op-stat-val" id="riotProgressLosses"><?= esc($lb_op_losses_text) ?></div>
                <div class="lb-op-stat-lbl">Losses</div>
              </div>
              <div class="lb-op-stat lb-op-stat--wr">
                <div class="lb-op-stat-val <?= esc($lb_op_record_tone) ?>" id="riotProgressRecord">
                  <?= esc($lb_op_winrate_pct) ?>
                </div>
                <div class="lb-op-stat-lbl">Winrate</div>
              </div>
            </div>

            <!-- WR bar -->
            <div class="lb-op-wr-bar">
              <div class="lb-op-wr-bar-fill <?= esc($lb_op_wr_bar_class) ?>" id="riotProgressWrBar"
                style="width:<?= esc($lb_op_winrate_bar_pct) ?>%"></div>
            </div>

            <?php endif; ?>

          <?php if (!$isCoachingForm): ?>
          <!-- Footer: last sync -->
            <div class="lb-op-footer">
              <div class="lb-op-footer-item">
                <span class="lb-op-footer-label">Last Sync</span>
                <span class="lb-op-footer-val" id="riotProgressLastSync"><?= esc($lb_op_last_sync_text) ?></span>
              </div>
            </div>

            <!-- Hidden cursor used by JS -->
            <span id="riotProgressLastMatch" hidden></span>

            <!-- Sync status message -->
            <div id="riotProgressSyncState" class="lb-op-sync-state" aria-live="polite"></div>

            <?php if (empty($lb_op_ign)): ?>
              <div class="lb-op-no-riot mt-3">
                <i class="fa-duotone fa-circle-info me-2"></i>
                Add Riot ID to enable automatic tracking.
              </div>
            <?php endif; ?>

            <?php if (!empty($lb_op_ign)): ?>
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

            <?php if ($lb_op_is_duo_order): ?>
              <div class="lb-op-duo-card <?= !empty($lb_op_booster_ign) ? 'lb-op-duo-card--set' : 'lb-op-duo-card--unset' ?>"
                id="openDuoAccountModalBtn" role="button">
                <div class="lb-op-duo-card__left">
                  <div class="lb-op-duo-card__icon">
                    <i class="fa-duotone fa-user-shield"></i>
                  </div>
                  <div class="lb-op-duo-card__body">
                    <div class="lb-op-duo-card__title">Your Duo Account</div>
                    <?php if (!empty($lb_op_booster_ign)): ?>
                      <div class="lb-op-duo-card__sub lb-op-duo-card__sub--set">
                        <i class="fa-solid fa-circle-check fa-xs"></i>
                        <?= htmlspecialchars($lb_op_booster_ign, ENT_QUOTES) ?>
                        <span class="lb-op-duo-card__tag">Stats tracked from your account</span>
                      </div>
                    <?php else: ?>
                      <div class="lb-op-duo-card__sub lb-op-duo-card__sub--unset">
                        <i class="fa-solid fa-circle-exclamation fa-xs"></i>
                        Not set — add your account to track your own stats
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-right lb-op-duo-card__arrow"></i>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <?php if (!empty($lb_op_ign)): ?>
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

        <!-- ACCOUNT (nur IN_PROGRESS) -->
        <?php if ($data['status'] === 'IN_PROGRESS'): ?>

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
            <?php if (!empty($lb_duo_timer) && (($data['status'] ?? '') === 'IN_PROGRESS')): ?>
            <div class="lb-dt-btn-row mt-3">
              <button type="button" class="lb-dt-btn lb-dt-btn-pause" id="lbdt-pause-btn"
                data-order-id="<?= (int)$data['id'] ?>"
                data-paused="<?= !empty($lb_duo_timer['is_paused']) ? '1' : '0' ?>"
                data-started="<?= !empty($lb_duo_timer['is_started']) ? '1' : '0' ?>">
                <?php if (empty($lb_duo_timer['is_started'])): ?>
                  <svg width="13" height="13" viewBox="0 0 13 13" fill="currentColor"><polygon points="1.5,1.5 11.5,6.5 1.5,11.5"/></svg>
                  Start Timer
                <?php elseif (!empty($lb_duo_timer['is_paused'])): ?>
                  <svg width="13" height="13" viewBox="0 0 13 13" fill="currentColor"><polygon points="1.5,1.5 11.5,6.5 1.5,11.5"/></svg>
                  Continue
                <?php else: ?>
                  <svg width="13" height="13" viewBox="0 0 13 13" fill="currentColor"><rect x="1.5" y="1.5" width="3.5" height="10" rx="1"/><rect x="8" y="1.5" width="3.5" height="10" rx="1"/></svg>
                  Pause
                <?php endif; ?>
              </button>
              <button type="button" class="lb-dt-btn lb-dt-btn-complete"
                data-bs-toggle="modal" data-bs-target="#complete_order_md">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5,6.5 5.5,10.5 11.5,2.5"/></svg>
                Complete Session
              </button>
            </div>
            <?php endif; ?>
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
          var pauseBtn = document.getElementById('lbdt-pause-btn');
          if(pauseBtn){
            pauseBtn.addEventListener('click', function(){
              var orderId = this.getAttribute('data-order-id');
              var isPaused = this.getAttribute('data-paused') === '1';
              var isStarted = this.getAttribute('data-started') === '1';
              var ajaxAction = !isStarted
                ? 'booster_start_duo_timer'
                : (isPaused ? 'booster_continue_duo_timer' : 'booster_pause_duo_timer');

              this.disabled = true;

              var fd = new FormData();
              fd.append('action', ajaxAction);
              fd.append('order_id', orderId);

              fetch('<?= AJAX_URL ?>', {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest'},
                body: fd
              }).then(function(r){ return r.json(); }).then(function(res){
                if(res && res.success){ location.reload(); }
                else { pauseBtn.disabled = false; }
              }).catch(function(){ pauseBtn.disabled = false; });
            });
          }
        })();
        </script>
        <?php endif; ?>
          <div class="card lb-overview-card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h4 class="card-header-title mb-0">Account</h4>
              <?php if (!empty($isLolOrder)): // Riot ID drives the API tracking (modal below uses the same flag) ?>
                <button type="button" class="btn btn-white btn-sm border lb-acc-trigger js-booster-riot-open">
                  <i class="fa-duotone fa-user-pen me-2"></i>
                  <span class="d-none d-sm-inline"><?= $riotMissing ? 'Add Riot ID' : 'Change Riot ID' ?></span>
                  <span class="d-sm-none"><?= $riotMissing ? 'Add' : 'Change' ?></span>
                </button>
              <?php endif; ?>
            </div>

            <div class="card-body">

              <?php
              $missing = [];
              if ($showAccountCredentials) {
                if ($loginMissing)
                  $missing[] = $accountUsernameLabel;
                if ($passMissing)
                  $missing[] = $accountPasswordLabel;
              }
              ?>

              <div class="lb-account-alert mb-3 <?= empty($missing) ? 'd-none' : '' ?>" id="lbAccountMissingAlert">
                <div class="lb-account-alert__left">
                  <div class="lb-account-alert__ico">
                    <i class="fa-duotone fa-triangle-exclamation"></i>
                  </div>
                  <div class="lb-account-alert__txt">
                    <div class="lb-account-alert__title">Logins missing</div>
                    <div class="lb-account-alert__sub">
                      Customer didn’t provide: <strong
                        id="lbMissingFields"><?= htmlspecialchars(implode(', ', $missing)) ?></strong>
                    </div>
                  </div>
                </div>

                <a class="btn btn-sm btn-primary lb-account-alert__btn" href="#" data-id="<?= (int) $data['id'] ?>"
                  data-action="poke_client">
                  <i class="fa-duotone fa-bell-on me-1"></i> Notify
                </a>
              </div>


              <ul class="lb-ov-grid" id="lbAccountGrid" data-order-id="<?= (int) $data['id'] ?>"
                data-game="<?= htmlspecialchars(strtolower((string) $data['game'])) ?>"
                data-form-id="<?= (int) ($data['form_id'] ?? 0) ?>"
                data-is-duo="<?= (int) ($data['is_duo'] ?? 0) ?>"
                data-riot-only="<?= (int) $accountRiotOnly ?>">


                <?php if ($gameKey === 'lol'): ?>
                <!-- Riot ID -->
                <li class="lb-ov-item lb-ov-item--secret <?= $riotMissing ? 'lb-ov-item--missing' : '' ?>" data-field="ign">
                  <div class="lb-ov-ico">🙂</div>
                  <div class="lb-ov-label">Riot ID</div>
                  <div class="lb-ov-value">
                    <?php if ($riotMissing): ?>
                      <span class="lb-missing-pill">
                        <i class="fa-duotone fa-triangle-exclamation"></i>
                        Customer didn’t provide yet
                      </span>
                    <?php else: ?>
                      <div class="lb-secret-field" data-secret="<?= htmlspecialchars($riotId, ENT_QUOTES) ?>"
                        data-masked="<?= htmlspecialchars($lb_mask($riotId), ENT_QUOTES) ?>">
                        <span class="lb-secret-text"><?= htmlspecialchars($lb_mask($riotId)) ?></span>

                        <button type="button" class="lb-secret-btn lb-secret-eye" aria-label="Reveal">
                          <i class="fa-duotone fa-eye"></i>
                        </button>

                        <button type="button" class="lb-secret-btn lb-secret-copy" aria-label="Copy">
                          <i class="fa-duotone fa-copy"></i>
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>
                </li>
                <?php endif; ?>

                <?php if ($showAccountCredentials): ?>

                  <!-- Account Username -->
                  <li class="lb-ov-item lb-ov-item--secret <?= $loginMissing ? 'lb-ov-item--missing' : '' ?>"
                    data-field="login">
                    <div class="lb-ov-ico">📛</div>
                    <div class="lb-ov-label"><?= htmlspecialchars($accountUsernameLabel) ?></div>
                    <div class="lb-ov-value">
                      <?php if ($loginMissing): ?>
                        <span class="lb-missing-pill">
                          <i class="fa-duotone fa-triangle-exclamation"></i>
                          Customer didn’t provide yet
                        </span>
                      <?php else: ?>
                        <div class="lb-secret-field" data-secret="<?= htmlspecialchars($login, ENT_QUOTES) ?>"
                          data-masked="<?= htmlspecialchars($lb_mask($login), ENT_QUOTES) ?>">
                          <span class="lb-secret-text"><?= htmlspecialchars($lb_mask($login)) ?></span>

                          <button type="button" class="lb-secret-btn lb-secret-eye" aria-label="Reveal">
                            <i class="fa-duotone fa-eye"></i>
                          </button>

                          <button type="button" class="lb-secret-btn lb-secret-copy" aria-label="Copy">
                            <i class="fa-duotone fa-copy"></i>
                          </button>
                        </div>
                      <?php endif; ?>
                    </div>
                  </li>

                  <!-- Account Password -->
                  <li class="lb-ov-item lb-ov-item--secret <?= $passMissing ? 'lb-ov-item--missing' : '' ?>"
                    data-field="password">
                    <div class="lb-ov-ico">🔑</div>
                    <div class="lb-ov-label"><?= htmlspecialchars($accountPasswordLabel) ?></div>
                    <div class="lb-ov-value">
                      <?php if ($passMissing): ?>
                        <span class="lb-missing-pill">
                          <i class="fa-duotone fa-triangle-exclamation"></i>
                          Customer didn’t provide yet
                        </span>
                      <?php else: ?>
                        <?php $pwMasked = str_repeat('•', max(8, mb_strlen($password))); ?>
                        <div class="lb-secret-field" data-secret="<?= htmlspecialchars($password, ENT_QUOTES) ?>"
                          data-masked="<?= htmlspecialchars($pwMasked, ENT_QUOTES) ?>">
                          <span class="lb-secret-text"><?= $pwMasked ?></span>

                          <button type="button" class="lb-secret-btn lb-secret-eye" aria-label="Reveal">
                            <i class="fa-duotone fa-eye"></i>
                          </button>

                          <button type="button" class="lb-secret-btn lb-secret-copy" aria-label="Copy">
                            <i class="fa-duotone fa-copy"></i>
                          </button>
                        </div>
                      <?php endif; ?>
                    </div>
                  </li>

                <?php endif; ?>

              </ul>

            </div>
          </div>

          <?php if (!empty($isLolOrder)): ?>
            <div class="modal fade lbx-modal lb-booster-riot-modal" id="boosterRiotIdModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content lbx-modal__content">
                  <div class="lbx-modal__header">
                    <div class="lbx-modal__headLeft">
                      <div class="lbx-modal__icon"><i class="fa-duotone fa-user-pen"></i></div>
                      <div>
                        <div class="lbx-modal__title"><?= $riotMissing ? 'Add Riot ID' : 'Change Riot ID' ?></div>
                        <div class="lbx-modal__sub">Required to start/continue this order.</div>
                      </div>
                    </div>

                    <button type="button" class="lbx-modal__close" data-bs-dismiss="modal" aria-label="Close">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>

                  <div class="lbx-modal__body">
                    <div class="lb-row">
                      <div class="lb-field">
                        <div class="lb-field-label"><span class="lb-ico">🙂</span><span>Riot ID</span></div>
                        <input type="text" id="booster_riot_id" class="lb-input" value="<?= htmlspecialchars($riotId ?? '', ENT_QUOTES) ?>" placeholder="Faker#1234" autocomplete="off" spellcheck="false" inputmode="text">
                        <div class="lb-riot-format-note"><i class="fa-duotone fa-circle-info"></i><span>Enter the client’s Riot ID exactly as shown in League, for example <strong>Faker#1234</strong>. We will show the account found below — please confirm it is the client’s before saving so live tracking works correctly.</span></div>
                        <div class="lb-riot-inline-error" id="booster_riot_error" aria-live="polite"></div>
                        <div class="lb-riot-preview" id="booster_riot_preview" aria-live="polite" hidden>
                          <div class="lb-riot-preview__avatar">
                            <img id="booster_riot_icon" src="" alt="Riot account icon" loading="lazy">
                            <i class="fa-duotone fa-user-magnifying-glass" id="booster_riot_icon_fallback"></i>
                          </div>
                          <div class="lb-riot-preview__body">
                            <div class="lb-riot-preview__label" id="booster_riot_preview_label">Riot account preview</div>
                            <div class="lb-riot-preview__name" id="booster_riot_name">Enter Riot ID to verify account</div>
                            <div class="lb-riot-preview__meta" id="booster_riot_meta">Example: Faker#1234</div>
                            <button type="button" class="lb-riot-confirm" id="booster_riot_confirm" hidden><i class="fa-solid fa-check me-1"></i> It's the client account</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="lbx-modal__footer">
                    <button type="button" class="lbx-modal__btn lbx-modal__btn--ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="lbx-modal__btn lbx-modal__btn--primary" id="booster_riot_save" disabled><i class="fa-duotone fa-floppy-disk me-1"></i> Save</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($lb_op_is_duo_order): ?>
        <!-- DUO ACCOUNT MODAL (custom, no Bootstrap modal wrapper) -->
        <div id="duoAccountModal" class="lbd-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none">
          <div class="lbd-backdrop" id="duoAccountBackdrop"></div>
          <div class="lbd-dialog">

            <!-- Header -->
            <div class="lbd-header">
              <div class="lbd-header__left">
                <div class="lbd-header__icon">
                  <i class="fa-duotone fa-user-group"></i>
                </div>
                <div>
                  <div class="lbd-header__title">Duo Account</div>
                  <div class="lbd-header__sub">Add the account you're currently playing on</div>
                </div>
              </div>
              <button type="button" class="lbd-close" id="duoAccountClose" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="lbd-body">

              <!-- Client row (read-only) -->
              <div class="lbd-account-row lbd-account-row--client">
                <div class="lbd-account-row__label">
                  <i class="fa-duotone fa-user fa-sm"></i> Client Account
                </div>
                <div class="lbd-account-row__value">
                  <?= !empty($lb_op_ign) ? htmlspecialchars($lb_op_ign, ENT_QUOTES) : '<span style="opacity:.4">—</span>' ?>
                  <span class="lbd-account-row__tag">Rank progress tracked here</span>
                </div>
              </div>

              <div class="lbd-sep"><i class="fa-solid fa-arrows-up-down"></i></div>

              <!-- Booster row (editable) -->
              <div class="lbd-account-row lbd-account-row--booster">
                <div class="lbd-account-row__label">
                  <i class="fa-duotone fa-user-shield fa-sm"></i> Your Account
                  <span class="lbd-badge">Currently playing on</span>
                </div>
                <div class="lbd-account-row__field">
                  <input type="text" id="duo_booster_riot_id" class="lbd-input"
                    value="<?= htmlspecialchars($lb_op_booster_ign, ENT_QUOTES) ?>"
                    placeholder="YourName#EUW" autocomplete="off" spellcheck="false">
                  <div class="lbd-note"><i class="fa-duotone fa-circle-info"></i> Enter your own Riot ID — KDA, champion and stats will be tracked from your account.</div>
                  <div class="lbd-error" id="duo_booster_riot_error"></div>

                  <!-- Preview -->
                  <div class="lbd-preview" id="duo_booster_riot_preview" hidden>
                    <div class="lbd-preview__avatar">
                      <img id="duo_booster_riot_icon" src="" alt="" loading="lazy">
                      <i class="fa-duotone fa-user-magnifying-glass" id="duo_booster_riot_icon_fallback"></i>
                    </div>
                    <div class="lbd-preview__body">
                      <div class="lbd-preview__label" id="duo_booster_riot_preview_label">Account preview</div>
                      <div class="lbd-preview__name" id="duo_booster_riot_name">Enter your Riot ID to verify</div>
                      <div class="lbd-preview__meta" id="duo_booster_riot_meta"></div>
                      <button type="button" class="lbd-confirm-btn" id="duo_booster_riot_confirm" hidden>
                        <i class="fa-solid fa-check"></i> It's my account
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <?php if (!empty($lb_op_booster_ign)): ?>
              <div class="lbd-current" id="lb_duo_current_wrap">
                <i class="fa-duotone fa-circle-check"></i>
                <span>Currently tracking: <strong><?= htmlspecialchars($lb_op_booster_ign, ENT_QUOTES) ?></strong></span>
                <button type="button" class="lbd-remove-btn" id="duo_booster_clear">
                  <i class="fa-solid fa-xmark"></i> Remove
                </button>
              </div>
              <?php endif; ?>

            </div>

            <!-- Footer -->
            <div class="lbd-footer">
              <button type="button" class="lbd-btn lbd-btn--ghost" id="duoAccountCancel">Cancel</button>
              <button type="button" class="lbd-btn lbd-btn--primary" id="duo_booster_riot_save" disabled>
                <i class="fa-duotone fa-floppy-disk"></i> Save
              </button>
            </div>

          </div>
        </div>
        <?php endif; ?>


        <?php if ($data['status'] === 'IN_PROGRESS' || in_array($data['status'], ['PAID', 'PROCESSING'], true)): ?>
          <!-- Desktop: actions card (client-view style). Toggled by the Order Actions button in the header. -->
          <div id="lbOrderActionsCardWrap" class="collapse show d-none d-lg-block">
          <div class="card lb-actions-card mb-3">
            <div class="card-header">
              <h4 class="card-header-title">Order Actions</h4>
            </div>

            <div class="card-body p-0">
              <div class="lb-actions-list">

                <?php if ($data['status'] === 'IN_PROGRESS'): ?>

                  <a class="lb-action-item" href="#" data-id="<?= (int) $data['id'] ?>" data-action="poke_client">
                    <span class="lb-action-ico"><i class="fa-duotone fa-bell-on"></i></span>
                    <span class="lb-action-txt">
                      <span class="lb-action-title">Notify Client</span>
                      <span class="lb-action-sub">Send a reminder / poke</span>
                    </span>
                    <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                  </a>

                  <?php if (BOOSTER_DATA['drop_tokens'] > 0): ?>
                    <?php if (!empty($dropCooldownActive)): ?>
                      <span class="lb-action-item lb-action-item--disabled" aria-disabled="true">
                        <span class="lb-action-ico"><i class="fa-duotone fa-clock"></i></span>
                        <span class="lb-action-txt">
                          <span class="lb-action-title">Admin team checking</span>
                          <span class="lb-action-sub"><?= (int) $dropCooldownRemainingMins ?>m remaining</span>
                        </span>
                        <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                      </span>
                    <?php else: ?>
                      <a class="lb-action-item lb-action-item--danger" href="#" data-bs-toggle="modal" data-bs-target="#request_drop_md">
                        <span class="lb-action-ico"><i class="fa-duotone fa-minus-hexagon"></i></span>
                        <span class="lb-action-txt">
                          <span class="lb-action-title">Drop Order</span>
                          <span class="lb-action-sub">Use a drop token</span>
                        </span>
                        <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                      </a>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if (!$isWaitingForApproval): ?>
                    <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#complete_order_md">
                      <span class="lb-action-ico"><i class="fa-duotone fa-square-check"></i></span>
                      <span class="lb-action-txt">
                        <span class="lb-action-title">Complete Order</span>
                        <span class="lb-action-sub"><?php
                          if ($forceManualApproval) {
                            echo 'Upload screenshot for admin approval';
                          } elseif ($isSoloRankBoost) {
                            echo 'Upload promotion screen proof to complete';
                          } elseif ($canTrack) {
                            echo 'Goal verified automatically via Riot tracking';
                          } else {
                            echo 'Upload screenshot for admin review';
                          }
                        ?></span>
                      </span>
                      <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                  <?php else: ?>
                    <span class="lb-action-item lb-action-item--disabled" aria-disabled="true">
                      <span class="lb-action-ico"><i class="fa-duotone fa-clock"></i></span>
                      <span class="lb-action-txt">
                        <span class="lb-action-title">Waiting for approval</span>
                        <span class="lb-action-sub">You already submitted completion</span>
                      </span>
                      <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                    </span>
                  <?php endif; ?>

                <?php else: ?>

                  <?php if ($isPendingRequestedForMe): ?>
                    <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#accept_requested_order_md">
                      <span class="lb-action-ico"><i class="fa-duotone fa-check"></i></span>
                      <span class="lb-action-txt">
                        <span class="lb-action-title">Ready to play</span>
                        <span class="lb-action-sub">Accept this requested order</span>
                      </span>
                      <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>

                    <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#decline_requested_order_md">
                      <span class="lb-action-ico"><i class="fa-duotone fa-xmark"></i></span>
                      <span class="lb-action-txt">
                        <span class="lb-action-title">Decline Order</span>
                        <span class="lb-action-sub">Put it back on the booster panel</span>
                      </span>
                      <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                  <?php else: ?>
                    <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#claim_order_md">
                      <span class="lb-action-ico"><i class="fa-duotone fa-play"></i></span>
                      <span class="lb-action-txt">
                        <span class="lb-action-title">Claim Order</span>
                        <span class="lb-action-sub">Start working on this order</span>
                      </span>
                      <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                  <?php endif; ?>

                <?php endif; ?>

              </div>
            </div>
          </div>
          </div>
        <?php endif; ?>

        <!-- OVERVIEW -->
        <?php if ($hasExtraOptions): ?>
          <div class="card lb-overview-card">



            <div class="card-header">
              <h4 class="card-header-title">Options</h4>
            </div>

            <div class="card-body">
              <ul class="lb-ov-grid">

                <?php foreach ($optionKeys as $option): ?>

                  <?php
                  if ($lb_skip_option($option))
                    continue;

                  // Bool-Optionen: nur anzeigen wenn == 1
                  if (in_array($option, $boolOptions, true) && (int) $data[$option] !== 1)
                    continue;

                  // WICHTIG: nicht empty() (weil "0" sonst wegfällt)
                  if (!array_key_exists($option, $data))
                    continue;

                  // WICHTIG: nicht empty() (weil "0" sonst wegfällt)
                  if (!array_key_exists($option, $data))
                    continue;
                  if (!in_array($option, $boolOptions, true)) {
                    $v = $data[$option];
                    if (is_array($v) && empty($v))
                      continue;
                    if (!is_array($v) && trim((string) $v) === '')
                      continue;
                  }

                  $ds_opt = util_format_option($option, $data[$option]);
                  $ico = util_format_option_emoji($option);

                  $rawVal = trim(strip_tags((string) $ds_opt[1]));
                  $valUp = strtoupper($rawVal);

                  $inline = false;
                  $valueHtml = $ds_opt[1];
                  $manyIconOption = in_array($option, ['champions', 'agents', 'roles'], true);

                  // YES/NO als Pills (auch wenn "0")
                  if (in_array($option, $boolOptions, true)) {
                    if ($valUp === 'YES' || $rawVal === '1' || (string) $data[$option] === '1') {
                      $valueHtml = '<span class="lb-yes-pill"><span class="lb-yes-dot"></span> YES</span>';
                    } elseif ($valUp === 'NO' || $rawVal === '0' || (string) $data[$option] === '0') {
                      $valueHtml = '<span class="lb-no-pill"><span class="lb-no-dot"></span> NO</span>';
                    }
                  }
                  ?>

                  <?php $optionDesc = $lb_get_option_description($option, $ds_opt[0]); ?>
                  <li class="lb-ov-item lb-ov-item--option lb-option-<?= htmlspecialchars($option, ENT_QUOTES) ?> <?= $inline ? 'lb-ov-item--inline' : '' ?> <?= $manyIconOption ? 'lb-ov-item--icons' : '' ?>">
                    <div class="lb-ov-ico"><?= $ico ?></div>
                    <div class="lb-ov-label-row">
                      <div class="lb-ov-label"><?= $ds_opt[0] ?></div>
                      <span class="lb-option-help" tabindex="0" data-tooltip="<?= htmlspecialchars($optionDesc['full'], ENT_QUOTES) ?>" aria-label="<?= htmlspecialchars($optionDesc['full'], ENT_QUOTES) ?>">
                        <i class="fa-duotone fa-circle-info"></i>
                      </span>
                    </div>
                    <div class="lb-ov-desc"><?= htmlspecialchars($optionDesc['short']) ?></div>
                    <div class="lb-ov-value"><?= $valueHtml ?></div>
                  </li>

                <?php endforeach; ?>

              </ul>
            </div>
          </div>
        <?php endif; ?>


        <div class="card lb-overview-card">
          <div class="card-header">
            <h4 class="card-header-title">Overview</h4>
          </div>

          <div class="card-body">
            <ul class="lb-ov-grid">
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

              <?php if (!$isCoachingForm): ?>
                <?php
                $duoHtml = util_format_option('is_duo', $data['is_duo'])[1];
                $duoRaw = trim(strip_tags((string) $duoHtml));
                $duoUp = strtoupper($duoRaw);

                if ($duoUp === 'YES' || $duoRaw === '1') {
                  $duoHtml = '<span class="lb-yes-pill"><span class="lb-yes-dot"></span> YES</span>';
                } elseif ($duoUp === 'NO' || $duoRaw === '0') {
                  $duoHtml = '<span class="lb-no-pill"><span class="lb-no-dot"></span> NO</span>';
                }
                ?>

                <li class="lb-ov-item">
                  <div class="lb-ov-ico">🤝</div>
                  <div class="lb-ov-label">Play With Booster</div>
                  <div class="lb-ov-value"><?= $duoHtml ?></div>
                </li>
              <?php endif; ?>

              <?php foreach (lb_order_view_purchase_fields($data) as $lbPurchaseField): ?>
                <li class="lb-ov-item">
                  <div class="lb-ov-ico"><i class="<?= esc($lbPurchaseField['icon']) ?>"></i></div>
                  <div class="lb-ov-label"><?= htmlspecialchars($lbPurchaseField['label'], ENT_QUOTES) ?></div>
                  <div class="lb-ov-value"><?= htmlspecialchars($lbPurchaseField['value'], ENT_QUOTES) ?></div>
                </li>
              <?php endforeach; ?>


              <?php
              // Extra overview details (LP etc.) – only show if value exists
              $ovExtraKeys = [
                'start_lp' => ['Start LP', 'fa-duotone fa-signal-bars'],
                'lp_gain' => ['LP Gain', 'fa-duotone fa-arrow-trend-up'],
                'current_lp' => ['Current LP', 'fa-duotone fa-chart-line'],
                'end_lp' => ['End LP', 'fa-duotone fa-flag-checkered'],
              ];
              if ($isCounterStrikeOrder) {
                unset($ovExtraKeys['start_lp'], $ovExtraKeys['current_lp'], $ovExtraKeys['end_lp'], $ovExtraKeys['lp_gain']);
              }
              // In LoL/TFT only Master+ (tier 8) is LP based — below that the desired rank is a
              // division and end_lp only holds a leftover/0 value.
              $lbLpGame2 = strtolower(trim((string)($data['game'] ?? '')));
              if (in_array($lbLpGame2, ['lol', 'league-of-legends', 'lol_classic', 'lol-classic', 'tft', 'teamfight-tactics'], true)
                  && (int)($data['end_tier'] ?? 0) < 8) {
                unset($ovExtraKeys['end_lp']);
              }
              ?>

              <?php foreach ($ovExtraKeys as $k => [$label, $iconCls]): ?>
                <?php
                if (!array_key_exists($k, $data))
                  continue;
                // Start LP is a band ("0-20") below Master and an exact points value above it,
                // so it needs the shared formatter instead of the raw column value.
                $val = $k === 'start_lp'
                  ? lb_order_start_lp_display($data)
                  : trim((string) $data[$k]);
                if ($val === '')
                  continue;
                ?>
                <li class="lb-ov-item <?= in_array($k, ['start_lp', 'lp_gain'], true) ? '' : 'lb-ov-item--inline' ?>">
                  <div class="lb-ov-ico"><i class="<?= $iconCls ?>"></i></div>
                  <div class="lb-ov-label"><?= $label ?></div>
                  <div class="lb-ov-value"><?= htmlspecialchars($val) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

  <?php endif; ?>

</div>

<!-- MODALS -->
<?php if ($data['status'] == "IN_PROGRESS"): ?>
  <div id="complete_order_md" class="modal fade lb-complete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
      <div class="modal-content">

        <form class="ajax-form m-0" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="booster_complete_order">
          <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

          <div class="modal-header">
            <div class="lb-complete-head">
              <div class="lb-complete-icon">
                <i class="fa-duotone fa-square-check"></i>
              </div>
              <div class="flex-grow-1">
                <h5 class="modal-title">Complete Order</h5>
                <div class="lb-complete-sub">
                  <?php if (!empty($lb_booster_is_ranked_5s)): ?>
                    Confirm the completed games and upload proof. Ranked 5s completions are checked by admin approval.
                  <?php elseif ($forceManualApproval): ?>
                    Upload completion proof. This order type is completed by admin approval.
                  <?php elseif ($isSoloRankBoost): ?>
                    Upload the promotion screen proof to finish this solo rank boost.
                  <?php elseif ($canTrack): ?>
                    Upload completion screenshot. Riot tracking will verify the goal automatically.
                  <?php else: ?>
                    Automatic Riot verification is not available, so please upload a screenshot for admin review.
                  <?php endif; ?>
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
          </div>

          <div class="modal-body">
            <?php
            $existingProof = $existingCompletionProof;
            $hasExistingProof = !empty($existingProof);
            ?>

            <?php if (!empty($lb_booster_is_ranked_5s)): ?>
              <div class="lb-complete-card mb-2" style="border-color:rgba(88,101,242,.28);background:rgba(88,101,242,.08);">
                <div class="d-flex align-items-start gap-3">
                  <div style="width:38px;height:38px;border-radius:12px;background:rgba(88,101,242,.18);border:1px solid rgba(88,101,242,.32);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa-brands fa-discord" style="color:#c7d2fe;font-size:1rem;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold mb-1" style="color:rgba(255,255,255,.92);">Ranked 5s completion check</div>
                    <div style="font-size:.82rem;color:rgba(255,255,255,.58);line-height:1.5;">
                      Enter how many games were completed. This order needs
                      <strong style="color:#fff;"><?= (int)$lbRanked5sTotalGames ?></strong> game<?= (int)$lbRanked5sTotalGames === 1 ? '' : 's' ?>.
                      Your screenshot will be sent to admin review.
                    </div>
                    <div class="mt-3" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                      <label class="d-block">
                        <span class="d-block fw-bold mb-1" style="font-size:.78rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.04em;">Completed games</span>
                        <input type="number" class="form-control" name="ranked_5s_games_completed" min="0" max="<?= max(1, (int)$lbRanked5sTotalGames) ?>" value="<?= max((int)$lbRanked5sCompletedGames, (int)$lbRanked5sTotalGames) ?>" style="border-radius:14px;" required>
                      </label>
                      <div>
                        <span class="d-block fw-bold mb-1" style="font-size:.78rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.04em;">Total games</span>
                        <div class="form-control" style="border-radius:14px;display:flex;align-items:center;font-weight:900;color:rgba(255,255,255,.9);background:rgba(255,255,255,.04);">
                          <?= (int)$lbRanked5sTotalGames ?>
                        </div>
                      </div>
                    </div>
                    <div class="form-check mt-3">
                      <input class="form-check-input" type="checkbox" name="ranked_5s_done_confirm" value="1" id="ranked5sDoneConfirm" required>
                      <label class="form-check-label" for="ranked5sDoneConfirm" style="font-size:.85rem;color:rgba(255,255,255,.72);">
                        I confirm all Ranked 5s games are completed and the screenshot proves it.
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($canTrack): ?>
            <!-- ── TRACKING MODE ── -->
            <div class="lb-complete-card mb-2" style="border-color:rgba(109,92,255,.25);background:rgba(109,92,255,.06);">
              <div class="d-flex align-items-start gap-3">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="fa-duotone fa-satellite-dish" style="color:#c4b5fd;font-size:1rem;"></i>
                </div>
                <div>
                  <div class="fw-bold mb-1" style="color:rgba(255,255,255,.9);">Riot Tracking Active</div>
                  <div style="font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.5;">
                    On <strong style="color:#c4b5fd;">Complete</strong> we verify the goal via Riot. If it's reached and your proof is attached, the order completes instantly — otherwise the screenshot goes to admin review.
                  </div>
                </div>
              </div>
            </div>

            <div class="lb-complete-card" id="lbCompleteTrackingStates">
              <!-- idle state -->
              <div id="lbTrackIdle">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="fa-duotone fa-circle-check" style="color:#4ade80;"></i>
                  <span class="fw-bold" style="font-size:.88rem;">Goal reached?</span>
                </div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.4);">
                  We'll check Riot automatically when you click Complete.
                </div>
              </div>
            </div>

            <?php $trackingProofInitiallyHidden = ($showTrackingFallbackScreenshot && !$hasExistingProof); ?>
            <!-- Completion screenshot: always required before completing the order -->
            <div class="lb-complete-card mt-3 <?= $trackingProofInitiallyHidden ? 'd-none' : '' ?>" id="lbCompleteScreenshotCard">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold" style="font-size:.88rem;">Completion screenshot</div>
                <span class="badge bg-soft-primary text-primary" id="lbCompleteRequiredBadge">Required</span>
              </div>
              <div id="lbCompletePreviewWrap" class="lb-complete-preview mb-2 <?= $hasExistingProof ? '' : 'd-none' ?>">
                <div class="lb-complete-preview__inner">
                  <img id="lbCompletePreviewImg" src="<?= htmlspecialchars($existingProof) ?>"
                    data-original="<?= htmlspecialchars($existingProof) ?>" alt=""
                    class="w-100 d-block" style="max-height:200px;object-fit:cover;">
                  <button type="button" class="btn btn-sm btn-white border lb-complete-preview__remove" id="lbCompleteRemoveBtn">
                    <i class="fa-duotone fa-trash-can me-1"></i> Remove
                  </button>
                </div>
              </div>
              <label class="lb-file lb-dropzone lb-dropzone--sm" for="file_url" id="lbCompleteDropZone">
                <div class="lb-dropzone__icon"><i class="fa-duotone fa-cloud-arrow-up"></i></div>
                <div class="lb-dropzone__title" style="font-size:.85rem;">Upload completion screenshot</div>
                <div class="lb-dropzone__sub">Drag &amp; drop or paste with <kbd>Ctrl</kbd>+<kbd>V</kbd></div>
                <div class="lb-dropzone__btn" id="lbCompleteChooseBtnText">Choose image</div>
                <div class="lb-dropzone__file" id="lbCompleteFileName">No file selected</div>
              </label>
              <input class="visually-hidden" type="file" name="file_url" id="file_url" accept="image/*">
              <div class="lb-complete-pill mt-2" style="font-size:.77rem;">
                <i class="fa-duotone fa-circle-info"></i>
                <span id="lbCompleteScreenshotHint">Please upload a clear screenshot showing the final result before submitting.</span>
              </div>
            </div>

            <?php else: ?>
            <!-- ── MANUAL MODE (non-tracking or missing Riot ID) ── -->
            <?php if (true): ?>
            <div id="lbCompletePreviewWrap" class="lb-complete-preview mb-3 <?= $hasExistingProof ? '' : 'd-none' ?>">
              <div class="lb-complete-preview__inner">
                <img id="lbCompletePreviewImg" src="<?= htmlspecialchars($existingProof) ?>"
                  data-original="<?= htmlspecialchars($existingProof) ?>" alt=""
                  class="w-100 d-block" style="max-height:240px;object-fit:cover;">
                <button type="button" class="btn btn-sm btn-white border lb-complete-preview__remove" id="lbCompleteRemoveBtn">
                  <i class="fa-duotone fa-trash-can me-1"></i> Remove
                </button>
              </div>
            </div>

            <div class="lb-complete-card">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold">Upload completion screenshot</div>
                <span class="badge bg-soft-primary text-primary">Required</span>
              </div>

              <label class="lb-file lb-dropzone" for="file_url" id="lbCompleteDropZone">
                <div class="lb-dropzone__icon"><i class="fa-duotone fa-cloud-arrow-up"></i></div>
                <div class="lb-dropzone__title">Upload completion screenshot</div>
                <div class="lb-dropzone__sub">
                  Click to select, drag &amp; drop here, or paste with
                  <kbd>Ctrl</kbd> + <kbd>V</kbd>.
                </div>
                <div class="lb-dropzone__btn" id="lbCompleteChooseBtnText">Choose image</div>
                <div class="lb-dropzone__file" id="lbCompleteFileName">No file selected</div>
              </label>

              <input class="visually-hidden" type="file" name="file_url" id="file_url" accept="image/*">

              <div class="lb-complete-pill">
                <i class="fa-duotone fa-circle-info mt-1"></i>
                <div>
                  Please upload a clear screenshot showing the promotion / final result.
                  <span class="lb-pill-muted">Tip: you can also paste a screenshot with <strong>Ctrl</strong> + <strong>V</strong>.</span>
                </div>
              </div>

              <?php if (!empty($data['is_bonus_win'])): ?>
                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" id="bonusDoneConfirm" required>
                  <label class="form-check-label" for="bonusDoneConfirm">
                    I confirm the <strong>Bonus Win</strong> is done.
                  </label>
                </div>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="lb-complete-card">
              <div class="d-flex align-items-center gap-3">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="fa-duotone fa-circle-check" style="color:#4ade80;font-size:1rem;"></i>
                </div>
                <div>
                  <div class="fw-bold mb-1" style="color:rgba(255,255,255,.9);">Completion confirmation</div>
                  <div style="font-size:.82rem;color:rgba(255,255,255,.55);line-height:1.5;">
                    By clicking <strong>Complete Order</strong>, you confirm the order was completed correctly. Riot/API or an admin will verify it.
                  </div>
                </div>
              </div>

              <?php if (!empty($data['is_bonus_win'])): ?>
                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" id="bonusDoneConfirm" required>
                  <label class="form-check-label" for="bonusDoneConfirm">
                    I confirm the <strong>Bonus Win</strong> is done.
                  </label>
                </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>


            <!-- Trustpilot: booster must send the review link to the customer -->
            <div class="lb-complete-card mt-2" style="border-color:rgba(0,182,122,.28);background:rgba(0,182,122,.07);">
              <div class="form-check m-0 d-flex align-items-start gap-2">
                <input class="form-check-input mt-1" type="checkbox" name="trustpilot_sent" value="1" id="trustpilotSentConfirm" required>
                <label class="form-check-label" for="trustpilotSentConfirm" style="font-size:.84rem;color:rgba(255,255,255,.75);line-height:1.45;">
                  <i class="fa-solid fa-star me-1" style="color:#00b67a;"></i>
                  I sent the customer our <strong>Trustpilot review link</strong> and asked them to rate their experience.
                </label>
              </div>
            </div>

            <!-- Result banner (shown after server response for tracking orders) -->
            <div id="lbCompleteResultBanner" class="d-none mt-3 p-3 rounded-3" style="font-size:.84rem;"></div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="lbCompleteSubmitBtn"
              <?php if ($requiresCompletionScreenshot && !$hasExistingProof): ?>disabled<?php endif; ?>>
              <span class="indicator-label">
                <?php if ($canTrack): ?>
                  <i class="fa-duotone fa-satellite-dish me-2"></i> Verify &amp; Complete
                <?php else: ?>
                  <i class="fa-duotone fa-square-check me-2"></i> Complete Order
                <?php endif; ?>
              </span>
              <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                <?= $canTrack ? 'Checking Riot…' : 'Loading...' ?>
              </span>
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>


  <div class="modal fade order-drop-modal" id="request_drop_md" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <form class="ajax-form m-0" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="booster_drop_order">
          <input type="hidden" name="order_id" value="<?= (int)$data['id'] ?>">

          <div class="modal-header">
            <div class="lb-drop-head">
              <div class="lb-drop-icon">
                <i class="fa-duotone fa-minus-hexagon"></i>
              </div>

              <div class="flex-grow-1">
                <h5 class="modal-title">Request Order Drop</h5>
                <div class="lb-drop-sub">Tell us why you need to drop this order. Be precise — it speeds up review.</div>

                <div class="lb-drop-steps mt-3">
                  <button type="button" class="lb-step-pill is-active" data-step-pill="1">DETAILS</button>
                  <button type="button" class="lb-step-pill" data-step-pill="2">EXPLANATION</button>
                </div>
              </div>

              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
          </div>

          <div class="modal-body">

            <!-- STEP 1 -->
            <div class="lb-drop-step is-active" data-step="1">
              <div class="lb-drop-card">
                <div class="lb-drop-card-title">Details</div>

                <div class="lb-drop-inline-alert alert alert-danger d-none" id="dropStep1Alert" role="alert"></div>

                <div class="row g-2">
                  <div class="col-12 col-md-6">
                    <label class="form-label">Assignment Date</label>
                    <input type="hidden" name="order_date" id="dropAssignmentDateValue" value="<?= htmlspecialchars($lb_claimed_at_backend) ?>" required>
                    <input type="text" class="form-control datetimepicker" id="dropAssignmentDate" autocomplete="off"
                      placeholder="DD.MM.YYYY HH:mm:ss" value="<?= htmlspecialchars($lb_claimed_at_display) ?>" readonly>
                    <div class="form-text">Defaults to <strong><?= htmlspecialchars($lb_assignment_source) ?></strong>. You can adjust if needed.</div>
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Drop Reason</label>
                    <select class="form-select lb-native-reason-select" name="reason_type" required tabindex="-1" aria-hidden="true">
                      <option value="" disabled selected>Select a reason</option>
                      <option value="Technical issues">Technical issues</option>
                      <option value="Unavailable due to personal reasons">Unavailable due to personal reasons</option>
                      <option value="Customer is playing on the account">Customer is playing on the account</option>
                      <option value="Wrong order type assigned">Wrong order type assigned</option>
                      <option value="Order too difficult or not as described">Order too difficult or not as described</option>
                      <option value="Other">Other</option>
                    </select>

                    <div class="lb-custom-select" data-lb-reason-select>
                      <button type="button" class="lb-custom-select__button" data-lb-reason-toggle aria-haspopup="listbox" aria-expanded="false">
                        <span class="lb-custom-select__label" data-lb-reason-label>Select a reason</span>
                        <span class="lb-custom-select__chev"><i class="fa-duotone fa-chevron-down"></i></span>
                      </button>

                      <div class="lb-custom-select__menu" data-lb-reason-menu role="listbox" aria-label="Drop Reason">
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Technical issues">
                          <span class="lb-custom-select__dot"></span>
                          <span>Technical issues</span>
                        </button>
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Unavailable due to personal reasons">
                          <span class="lb-custom-select__dot"></span>
                          <span>Unavailable due to personal reasons</span>
                        </button>
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Customer is playing on the account">
                          <span class="lb-custom-select__dot"></span>
                          <span>Customer is playing on the account</span>
                        </button>
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Wrong order type assigned">
                          <span class="lb-custom-select__dot"></span>
                          <span>Wrong order type assigned</span>
                        </button>
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Order too difficult or not as described">
                          <span class="lb-custom-select__dot"></span>
                          <span>Order too difficult or not as described</span>
                        </button>
                        <button type="button" class="lb-custom-select__option" role="option" data-value="Other">
                          <span class="lb-custom-select__dot"></span>
                          <span>Other</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Progress status</label>

                    <div class="row g-2 mb-2" id="dropProgressChoice">
                      <div class="col-12 col-md-6">
                        <input type="radio" class="btn-check" name="progress_state" id="dropHasProgress" autocomplete="off" checked>
                        <label class="lb-progress-choice lb-progress-ok" for="dropHasProgress">
                          <div class="d-flex align-items-start gap-2">
                            <div class="lb-progress-icon">✅</div>
                            <div class="flex-grow-1">
                              <div class="fw-semibold">Progress was made</div>
                              <div class="small text-muted">Select if any games were played or LP/MMR changed.</div>
                            </div>
                          </div>
                        </label>
                      </div>

                      <div class="col-12 col-md-6">
                        <input type="radio" class="btn-check" name="progress_state" id="dropNoProgress" autocomplete="off">
                        <label class="lb-progress-choice lb-progress-zero" for="dropNoProgress">
                          <div class="d-flex align-items-start gap-2">
                            <div class="lb-progress-icon">⚠️</div>
                            <div class="flex-grow-1">
                              <div class="fw-semibold text-danger">0 progress (nothing done)</div>
                              <div class="small text-muted">Only if <u>no games</u> were played and <u>no LP/MMR</u> changed.</div>
                            </div>
                          </div>
                        </label>
                      </div>
                    </div>

                    <input type="hidden" name="zero_progress" id="dropZeroProgressHidden" value="0">
                  </div>

                  <div class="col-12 d-none" id="dropZeroWarning">
                    <div class="alert alert-danger mb-0">
                      <strong>0 progress = NO games played / NO LP changes.</strong><br>
                      If you played even 1 game or changed LP/MMR, do <u>not</u> select 0 progress.
                    </div>
                  </div>

                  <div class="col-12 d-none" id="dropZeroConfirmWrap">
                    <label class="form-label text-danger">Type <strong>ZERO</strong> to confirm 0 progress</label>
                    <input type="text" class="form-control" id="dropZeroConfirmInput" name="zero_progress_confirm" placeholder="Type: ZERO" autocomplete="off">
                    <div class="form-text text-danger">Required to prevent accidental confirmations.</div>
                  </div>

                  <div class="col-12" id="dropProgressWrap">
                    <label class="form-label">Order Progress</label>
                    <input type="text" class="form-control" name="progress"
                      placeholder="Example: Started Gold II 12LP — Now Gold I (53 LP)" required>
                    <div class="form-text">Required unless you select <strong>0 progress</strong>.</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label">
                      OP.GG Link
                      <span class="badge bg-soft-primary text-primary ms-1" id="dropOpggBadge">Required</span>
                    </label>
                    <input type="text" class="form-control" name="progress_url"
                      placeholder="Required when progress was made (paste OP.GG / proof link)">
                    <div class="form-text">Required for <strong>Progress was made</strong>. Optional for <strong>0 progress</strong>.</div>
                  </div>

                </div>
              </div>
            </div>

            <!-- STEP 2 -->
            <div class="lb-drop-step" data-step="2">
              <div class="lb-drop-card">
                <div class="lb-drop-card-title">Explanation</div>

                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label">Explain Your Reason</label>
                    <textarea class="form-control" name="reason" rows="4" required
                      placeholder="What happened? What prevents you from finishing?"></textarea>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Additional Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" name="notes" rows="3"
                      placeholder="Anything else we should know?"></textarea>
                  </div>

                  <div class="col-12">
                    <div class="lb-drop-hint">
                      <i class="fa-duotone fa-circle-info"></i>
                      False information may lead to penalties. Please be honest and accurate.
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer lb-drop-footer">
            <div class="d-flex w-100 justify-content-between align-items-center gap-2">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-white d-none" data-step-back>Back</button>
              </div>

              <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-step-next>
                  <i class="fa-duotone fa-arrow-right me-2"></i>Next
                </button>

                <button type="submit" class="btn btn-primary d-none" data-step-submit>
                  <span class="indicator-label"><i class="fa-duotone fa-paper-plane me-2"></i>Send Request</span>
                  <span class="indicator-progress">
                    <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
                  </span>
                </button>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>

<?php elseif (in_array($data['status'], ['PAID', 'PROCESSING'], true)): ?>
  <?php if ($isPendingRequestedForMe): ?>
    <div id="accept_requested_order_md" class="modal fade lb-claim-modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
          <form class="ajax-form m-0" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="booster_accept_requested_order">
            <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

            <div class="modal-header">
              <div class="lb-claim-head">
                <div class="lb-claim-icon"><i class="fa-duotone fa-check"></i></div>
                <div class="flex-grow-1">
                  <h5 class="modal-title">Ready to play?</h5>
                  <div class="lb-claim-sub">Accept this requested order and start working now.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
            </div>

            <div class="modal-body">
              <div class="lb-claim-card">
                <div class="lb-claim-row"><div class="lb-claim-k">Order</div><div class="lb-claim-v">#<?= $data['id'] ?></div></div>
                <div class="lb-claim-row"><div class="lb-claim-k">Client</div><div class="lb-claim-v"><?= htmlspecialchars($client['username'] ?? 'Unknown') ?></div></div>
                <div class="lb-claim-row"><div class="lb-claim-k">Boost</div><div class="lb-claim-v"><?= util_format_boost_overview($data['game'], $data['type'], $data) ?></div></div>
                <div class="lb-claim-row"><div class="lb-claim-k">Earning</div><div class="lb-claim-v"><?= is_null($earning) ? '-' : ($currency . util_format_price_display($earning)) ?></div></div>
              </div>
              <div class="lb-claim-pill"><i class="fa-duotone fa-circle-info"></i> This will move the order to <b>In Progress</b> and confirm to the client that you are ready.</div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <span class="indicator-label"><i class="fa-duotone fa-check me-2"></i>Accept Order</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div id="decline_requested_order_md" class="modal fade lb-claim-modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
          <form class="ajax-form m-0" action="<?= AJAX_URL ?>">
            <input type="hidden" name="action" value="booster_decline_requested_order">
            <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

            <div class="modal-header">
              <div class="lb-claim-head">
                <div class="lb-claim-icon"><i class="fa-duotone fa-xmark"></i></div>
                <div class="flex-grow-1">
                  <h5 class="modal-title">Decline this request?</h5>
                  <div class="lb-claim-sub">The order will be moved back to the booster panel so another booster can take it.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
            </div>

            <div class="modal-body">
              <div class="lb-claim-pill"><i class="fa-duotone fa-triangle-exclamation"></i> Declining will remove you from this requested order immediately.</div>
              <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="declineConfirm" required>
                <label class="form-check-label" for="declineConfirm">I can’t start this order right now.</label>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger">
                <span class="indicator-label"><i class="fa-duotone fa-xmark me-2"></i>Decline Order</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php else: ?>
  <div id="claim_order_md" class="modal fade lb-claim-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
      <div class="modal-content">

        <form class="ajax-form m-0" action="<?= AJAX_URL ?>">
          <input type="hidden" name="action" value="booster_claim_order">
          <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

          <div class="modal-header">
            <div class="lb-claim-head">
              <div class="lb-claim-icon">
                <i class="fa-duotone fa-play"></i>
              </div>
              <div class="flex-grow-1">
                <h5 class="modal-title">Claim this order?</h5>
                <div class="lb-claim-sub">You’ll be assigned instantly and can start working right away.</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
          </div>

          <div class="modal-body">
            <div class="lb-claim-card">
              <div class="lb-claim-row">
                <div class="lb-claim-k">Order</div>
                <div class="lb-claim-v">#<?= $data['id'] ?></div>
              </div>

              <div class="lb-claim-row">
                <div class="lb-claim-k">Status</div>
                <div class="lb-claim-v"><?= $statusText ?></div>
              </div>

              <div class="lb-claim-row">
                <div class="lb-claim-k">Boost</div>
                <div class="lb-claim-v"><?= util_format_boost_overview($data['game'], $data['type'], $data) ?></div>
              </div>

              <div class="lb-claim-row">
                <div class="lb-claim-k">Client</div>
                <div class="lb-claim-v"><?= htmlspecialchars($client['username'] ?? 'Unknown') ?></div>
              </div>

              <div class="lb-claim-row">
                <div class="lb-claim-k">Earning</div>
                <div class="lb-claim-v" id="js-earning-modal">
                  <div class="lb-earn-inline">
                    <span class="lb-earn-amount" id="js-earning-modal-amount"><?= is_null($earning) ? '-' : ($currency . util_format_price_display($earning)) ?></span>

                    <span class="lb-chip lb-chip-timer" id="js-earning-modal-timer-chip" title="Next cut change" <?= ($next_change_in === null ? 'style="display:none"' : '') ?>>
                      <i class="fa-duotone fa-timer"></i>
                      <span class="lb-timer-val" id="js-earning-modal-timer" data-seconds="<?= (int) ($next_change_in ?? 0) ?>">
                        <?php if ($next_change_in !== null) { $mm = str_pad((string) floor($next_change_in / 60), 2, '0', STR_PAD_LEFT); $ss = str_pad((string) ($next_change_in % 60), 2, '0', STR_PAD_LEFT); echo $mm . ':' . $ss; } ?>
                      </span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="lb-claim-pill">
              <i class="fa-duotone fa-circle-info"></i>
              Claiming means you’re ready to begin. Please only claim if you can start soon.
            </div>

            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="claimConfirm" required>
              <label class="form-check-label" for="claimConfirm">
                I confirm I can start now and I understand I’ll be assigned to this order.
              </label>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>

            <button type="submit" class="btn btn-primary">
              <span class="indicator-label"><i class="fa-duotone fa-play me-2"></i>Claim Order</span>
              <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading...
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php if (!empty($review)): ?>
  <div id="view_review_md" class="modal fade lb-modal " tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header pb-4 border-bottom">
          <h5 class="modal-title" id="set_order_paidTitle">Client Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <h5>Communication</h5>
                <input class="rating-input" type="text" name="score[communication]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['communication']) . '"' : '' ?> readonly>
                <p class="text-muted mt-2 mb-0">
                  How good was the communication (updates, answers, friendliness)?
                </p>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <h5>Skill</h5>
                <input class="rating-input" type="text" name="score[skill]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['skill']) . '"' : '' ?> readonly>
                <p class="text-muted mt-2 mb-0">
                  How strong was the booster in-game (decisions, consistency)?
                </p>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <h5>Speed</h5>
                <input class="rating-input" type="text" name="score[speed]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['speed']) . '"' : '' ?> readonly>
                <p class="text-muted mt-2 mb-0">
                  How quickly was the order completed?
                </p>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <h5>Overall</h5>
                <input class="rating-input" type="text" name="score[overall]" <?= !empty($review) ? 'value="' . htmlspecialchars($review['overall']) . '"' : '' ?> readonly>
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
                    <input type="checkbox" class="btn-check" id="btn-check-<?= htmlspecialchars($highlight) ?>"
                      name="highlights[]" value="<?= htmlspecialchars($highlight) ?>" autocomplete="off" <?= !empty($review) && in_array($highlight, json_decode($review['highlights']) ?? [], true) ? 'checked' : '' ?> disabled>
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
                  placeholder="Share more details about your experience..."
                  readonly><?= !empty($review) ? htmlspecialchars($review['comments']) : '' ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<?= $this->start('scripts') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/hs-file-attach/dist/hs-file-attach.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/js/star-rating.min.js"
  type="text/javascript"></script>

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme JS files as mentioned below (and change the theme property of the plugin) -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-fas/theme.js"></script>

<script>
  // LIVE CLAIM SYNC: if another booster claims this order, disable claim + redirect (prevents "Invalid order")
  (function () {
    var orderId = <?= (int) $data['id'] ?>;
    var myBoosterId = <?= (int) (defined('BOOSTER_ID') ? BOOSTER_ID : 0) ?>;
    var POLL_MS = 3000;

    var modalEl = document.getElementById('claim_order_md');
    var claimBtn = document.querySelector('[data-bs-target="#claim_order_md"]');

    var timer = null;

    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }

    function postForm(dataObj) {
      var body = new URLSearchParams(dataObj);
      return fetch((typeof ajax_url !== 'undefined' ? ajax_url : '<?= AJAX_URL ?>'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body,
        credentials: 'include',
        cache: 'no-store'
      }).then(function (r) { return r.text(); });
    }

    function showClaimedToast() {
      try {
        if (typeof window.create_toast === 'function') {
          window.create_toast('danger', 'Order already claimed', 'Another booster has claimed this order.');
        } else if (typeof window.create_order_toast === 'function') {
          window.create_order_toast({
            title: 'Order already claimed',
            messageHtml: 'Another booster has claimed this order.',
            href: null,
            timeoutMs: 8000,
            iconClass: 'fa-solid fa-lock',
            accent: '#ef4444'
          });
        }
      } catch (e) {}
    }

    function disableClaimUI() {
      try {
        if (claimBtn) {
          claimBtn.disabled = true;
          claimBtn.classList.add('disabled');
          claimBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i> Already claimed';
        }
      } catch (e) {}
    }

    function hideModal() {
      if (!modalEl) return;
      try {
        var inst = bootstrap.Modal.getInstance(modalEl);
        if (!inst) inst = new bootstrap.Modal(modalEl);
        inst.hide();
      } catch (e) {}
    }

    function redirectOut() {
      stop();
      setTimeout(function () {
        try {
          var b = (typeof base_url !== 'undefined' ? base_url : '');
          b = (b || '').replace(/\/$/, '');
          window.location.href = (b ? (b + '/booster-area/orders') : '/booster-area/orders');
        } catch (e) {
          try { window.location.reload(); } catch (e2) {}
        }
      }, 900);
    }

    function check() {
      postForm({ action: 'check_order_status', order_id: orderId })
        .then(function (raw) {
          var res;
          try { res = JSON.parse(raw); } catch (e) { return; }
          if (!res || res.error) return;

          var isRanked5s = !!(res.is_ranked_5s || res.ranked_5s);
          var isMultiBooster = isRanked5s || !!res.is_multi_booster;
          var status = String(res.order_status || res.status || '');
          var boosterId = parseInt(res.booster_id || 0, 10);

          if (isMultiBooster && res.available === true) {
            return;
          }

          var claimable = isMultiBooster
            ? (status === 'PAID' || status === 'PROCESSING' || status === 'IN_PROGRESS')
            : (status === 'PAID' || status === 'PROCESSING');

          var takenByOther = !isMultiBooster && (boosterId && boosterId !== myBoosterId);

          if (takenByOther || !claimable) {
            disableClaimUI();
            hideModal();
            showClaimedToast();
            redirectOut();
          }
        })
        .catch(function () {});
    }

    var modalOpen = false;

    function start() {
      modalOpen = true;
      if (timer) return;
      // Claiming already broadcasts order_status_update and orders_panel_update, so
      // while the socket is up the event tells us the instant someone else takes the
      // order — faster than this poll ever could. The interval stays as a fallback
      // for a dropped socket, and skips itself whenever the socket is connected.
      timer = setInterval(function () {
        if (window.lbRealtimeConnected === true) return;
        check();
      }, POLL_MS);
      check();
    }

    function stopAll() {
      modalOpen = false;
      stop();
    }

    // Drive the check from the socket instead of the clock.
    function attachRealtimeClaimSync() {
      var socket = window.lbSocket;
      if (!socket || socket.__lbClaimSyncAttached) return;
      socket.__lbClaimSyncAttached = true;

      function onOrderEvent(data) {
        if (!modalOpen) return;
        var payload = (data && data.data && typeof data.data === 'object') ? data.data : (data || {});
        var eventOrderId = parseInt(payload.order_id || 0, 10);
        // Events carry an order id; ignore the ones for other orders. A missing id
        // means "something changed somewhere", so re-check to be safe.
        if (eventOrderId && eventOrderId !== orderId) return;
        check();
      }

      socket.on('order_status_update', onOrderEvent);
      socket.on('orders_panel_update', onOrderEvent);
    }

    attachRealtimeClaimSync();
    setTimeout(attachRealtimeClaimSync, 1000);
    setTimeout(attachRealtimeClaimSync, 3000);

    // If modal exists, poll only while it is open (less load).
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', start);
      modalEl.addEventListener('hidden.bs.modal', stopAll);
    } else if (claimBtn) {
      // Fallback: if claim button exists without modal for some reason, still poll.
      start();
    }
  })();
</script>


<?php if (in_array($data['status'], ['IN_PROGRESS', 'PAUSED', 'COMPLETED'], true)): ?>
<script>
  (function () {
    let msg_none = false;
    let chat_json = {};
    let order_id = <?= (int) $data['id'] ?>;
    let order_status = "<?= $data['status'] ?>";
    let user_type = "booster";
    let user_id = <?= (int) BOOSTER_ID ?>;

    // ✅ kompatibel mit JEDEM fetch_api
    let post_data = { order_id: order_id, order_view: 1 };

    var chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
    function message_sound() {
      try { chat_notif.volume = 0.6; chat_notif.play(); } catch (e) {}
    }

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

    // helpers for edit UI
    function escapeHtml(str){
      return String(str ?? '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
    }
    function htmlToPlain(html){
      const tmp = document.createElement('div');
      tmp.innerHTML = html ?? '';
      return (tmp.textContent || tmp.innerText || '').trimEnd();
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

    function isDeletedMessage(val) {
      return !!(val && (
        val.type === 'deleted' ||
        val.deleted == 1 ||
        val.deleted_at ||
        val.deleted_by ||
        (typeof val.content === 'string' && val.content.trim() === 'Message deleted.')
      ));
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
      if (document.visibilityState !== 'visible') return;
      if (!document.hasFocus || !document.hasFocus()) return;

      const lastIncomingId = getLastUnseenIncomingId(chat_json);
      if (!lastIncomingId) return;

      fetch_api('update_chat_seen', { order_id: order_id, id: lastIncomingId }).done(function () {});
}

function getLastNonDeletedId(chat_list) {
      const keys = Object.keys(chat_list || {});
      if (!keys.length) return null;
      // numeric sort for safety
      const ids = keys.map(k => parseInt(k, 10)).filter(n => !isNaN(n)).sort((a,b)=>a-b);
      for (let i = ids.length - 1; i >= 0; i--) {
        const id = String(ids[i]);
        const v = chat_list[id];
        if (!isDeletedMessage(v)) return id;
      }
      return null;
    }

    // ---- SCROLL: zuverlässig ans Ende (Timing-Fix) ----
    const chatWrap = document.getElementById('chat_messages');
    function scrollChatToBottom(force) {
      const el = chatWrap;
      if (!el) return;

      // force=true: immer runter (initial load)
      // force=false: nur wenn user nicht weit oben liest (optional)
      if (!force) {
        // Wenn User sehr weit oben ist, nicht aggressiv runterziehen:
        const distToBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
        if (distToBottom > 220) return;
      }

      requestAnimationFrame(() => {
        el.scrollTop = el.scrollHeight;
        setTimeout(() => { el.scrollTop = el.scrollHeight; }, 60);
      });
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
        return ' <span class="lb-msg__ticks text-primary" title="' + escapeAttr(title) + '"><i class="fa-solid fa-check-double"></i></span>';
      }
      if (delivered) {
        return ' <span class="lb-msg__ticks text-muted" title="Delivered"><i class="fa-solid fa-check-double"></i></span>';
      }
      return ' <span class="lb-msg__ticks text-muted" title="Sent"><i class="fa-solid fa-check"></i></span>';
    }

    function load_message(message_id, msg_data, isGrouped) {
      // SYSTEM MESSAGE
      if (msg_data.sender === 'system') {
        let timeTxt = '';
        try { if (msg_data.time) timeTxt = formatExactTime(msg_data.time); } catch (e) {}
        const content = formatSystemMessageContent(decodeHtmlEntities(msg_data.content));
        return `
          <div class="lb-sys-wrap">
            <div class="lb-sys">${content}</div>
            ${timeTxt ? `<div class="lb-sys-time">${timeTxt}</div>` : ``}
          </div>
        `;
      }

      const isMe = (msg_data.sender === 'booster' && String(msg_data.sender_id) === String(user_id));
      const alignClass = isMe ? 'lb-msg--end' : 'lb-msg--start';
      const headClass = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';

      const badge = getRoleBadge(msg_data.sender);
      const avatar = (msg_data.sender_icon && ('' + msg_data.sender_icon).length)
        ? msg_data.sender_icon
        : getFallbackAvatar(msg_data.sender);

      const name = isMe ? 'You' : (msg_data.sender_name || 'Unknown');

      let time = '';
      try { time = formatExactTime(msg_data.time); } catch (e) {}

      const isEdited = (msg_data.edited == 1 || msg_data.edited === true || msg_data.edited_at);
      const editedMark = isEdited ? ' <span class="lb-msg__edited">Edited</span>' : '';

      const content = decodeHtmlEntities(msg_data.content);

      let html = '<div class="lb-msg ' + alignClass + '">';

      if (!isGrouped) {
        html += ''
          + '<div class="' + headClass + '">'
          + '<img class="lb-msg__avatar" src="' + avatar + '" alt="avatar">'
          + '<div class="lb-msg__meta">'
          + '<div class="lb-msg__name">'
          + name + ' <span class="lb-badge ' + badge.cls + '">' + badge.label + '</span>'
          + '</div>'
          + '</div>'
          + '</div>';
      }

      html += '<div class="lb-msg__bubble" data-msg-id="' + message_id + '">'
        + '<div class="lb-msg__content">' + content + '</div>'
        + (isMe ? '<button type="button" class="lb-msg__edit" data-msg-id="' + message_id + '" title="Edit message"><i class="fa-regular fa-pen-to-square"></i></button>' : '')
        + '</div>';

      const ticks = isMe ? renderTicks(msg_data) : '';
      html += '<div class="lb-msg__stamp">' + time + (editedMark ? (' ' + editedMark) : '') + ticks + '</div>';

      html += '</div>';
      return html;
    }

    // ---- STATE ----
    let firstLoad = true;
    let lastRenderedLastId = null;
    let lastRenderedSig = null;

    function renderChat(chat_list) {
      // Wenn gerade ein Edit offen ist, NICHT re-rendern (sonst “springt” UI)
      if (document.querySelector('.lb-msg__bubble[data-editing="1"]')) return;

      let chat_html = '';
      let last_sender = "";
      let last_sender_id = 0;

      $.each(chat_list, function (key, val) {
        if (isDeletedMessage(val)) return true;
        const isGrouped = (val.sender === last_sender && String(val.sender_id) === String(last_sender_id));
        chat_html += load_message(key, val, isGrouped);
        last_sender = val.sender;
        last_sender_id = val.sender_id;
      });

      $('#chat_messages').html(chat_html);
    }

    function load_messages() {
      fetch_api('load_chat', post_data).done(function (response) {
        try { response = JSON.parse(response); } catch (e) { return; }

        const chat_list = response.messages || response.data || {};
        const keys = Object.keys(chat_list || {});
        const msg_count = keys.length;

        if (!msg_count) {
          if (msg_none === false) {
            $('#chat_messages').html('<div class="text-center"><h5 class="mt-5">No messages found.<br><br>Send one to get started!</h5></div>');
            msg_none = true;
          }
          return;
        }

        msg_none = false;

        // last non-deleted id
        const lastId = getLastNonDeletedId(chat_list);

        // Render nur wenn sich wirklich was geändert hat (neue msg ODER Status-Flags ändern sich)
        const lastMsgForSig = lastId ? chat_list[lastId] : null;
        const statusSig = lastMsgForSig ? [lastId, lastMsgForSig.notify, lastMsgForSig.seen, lastMsgForSig.seen_at, lastMsgForSig.edited, lastMsgForSig.deleted].join('|') : String(lastId || '');
        const shouldRender = (statusSig !== lastRenderedSig) || firstLoad;
        if (shouldRender) {
          chat_json = chat_list;
          renderChat(chat_list);
        }

        // Seen/notify logic + sound
        if (lastId) {
          const last_message = chat_list[lastId];

          // Wenn letzte Nachricht vom Booster selbst ist -> Read Badge
          if (last_message && last_message.sender == user_type && String(last_message.sender_id) === String(user_id)) {
            let message_read = '';
            if (last_message.seen == 1) {
              message_read = '<span class="text-muted fs-7 mb-1"><i class="fa-solid fa-check-double"></i> Read' + (last_message.seen_at ? (' • ' + formatExactTime(last_message.seen_at)) : '') + '</span>';
}
            let read_html = '<div class="d-flex justify-content-end mt-n1 mb-2 pe-1" id="message-read-status">' + message_read + '</div>';
            if ($("#message-read-status").length == 0) {
              $('#chat_messages').append(read_html);
            } else {
              $('#message-read-status').html(message_read);
            }
          } else if (last_message && last_message.notify == 0 && last_message.seen == 0) {
            // Mark seen
            fetch_api('update_chat_notify', { order_id: order_id, id: lastId }).done(function () {});
if (document.visibilityState === 'visible') { message_sound(); }
          }
        }

        // ✅ SCROLL: beim ersten Laden immer runter.
        // Bei neuen Nachrichten: runter (aber wenn user weit oben liest, nicht aggressiv)
        if (firstLoad) {
          scrollChatToBottom(true);
          firstLoad = false;
        } else if (lastId !== lastRenderedLastId) {
          scrollChatToBottom(false);
        }

        lastRenderedLastId = lastId;
      });
    }


    // ---- Chat image attach (file + paste) ----
    (function initChatImageAttach(){
      const form = document.getElementById('lbChatForm');
      if (!form) return;

      const msgInput = document.getElementById('lbChatMessage');
      const fileInput = document.getElementById('lbChatFile');
      const attachBtn = document.getElementById('lbChatAttachBtn');
      const preview = document.getElementById('lbChatPreview');
      const previewImg = document.getElementById('lbChatPreviewImg');
      const previewName = document.getElementById('lbChatPreviewName');
      const removeBtn = document.getElementById('lbChatRemoveBtn');
      const errBox = document.getElementById('lbChatError');
      const sendBtn = document.getElementById('lbChatSendBtn');

      // Move the mobile "Notify" button into the action row (so the input stays full width)
      try {
        const tmpl = document.getElementById('lbChatNotifyMobileTmpl');
        const slot = document.getElementById('lbChatNotifyMobileSlot');
        if (tmpl && slot && !slot.hasChildNodes()) {
          const a = tmpl.querySelector('a');
          if (a) slot.appendChild(a.cloneNode(true));
        }
      } catch (e) {}

      let previewUrl = null;

      // ---- Policy warning (suspicious off-platform / private boosting hints) ----
      const policyWarn = document.getElementById('lbChatPolicyWarn');
      const policyWarnMatches = document.getElementById('lbChatPolicyWarnMatches');
      const policyWarnPreview = document.getElementById('lbChatPolicyWarnPreview');

      // Weighted rules: we don't want to hard-block normal chat, we want to *flag* suspicious patterns.
      const POLICY_RULES = [
        // Off-platform contact / social platforms
        { id:'discord', label:'Discord', weight:3, sev:'med', re:/\bdiscord\b|discord\.gg|discordapp\.com/i },
        { id:'dm', label:'DM / PM', weight:2, sev:'med', re:/\b(dm|dms|pm|pms|pn|priv(?:at)?|direct message)\b/i },
        { id:'messaging', label:'Messenger apps', weight:3, sev:'med', re:/\b(telegram|whatsapp|signal|wechat|line|skype)\b/i },
        { id:'social', label:'Social media', weight:3, sev:'med', re:/\b(instagram|insta|ig|snap(?:chat)?|tiktok|facebook|fb|twitter|x\.com)\b/i },
        { id:'game_ids', label:'Game IDs', weight:2, sev:'low', re:/\b(riot\s?id|riotid|bnet|battle\.net|battlenet|steam|psn|xbox|gamertag|epic|ea\s?id)\b/i },

        // Contact info (high)
        { id:'email', label:'Email', weight:5, sev:'high', re:/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i },
        { id:'phone', label:'Phone number', weight:5, sev:'high', re:/\b(\+?\d[\d\s().-]{6,}\d)\b/i },

        // Off-site payments (high)
        { id:'paypal', label:'PayPal', weight:4, sev:'high', re:/\b(paypal|pp)\b/i },
        { id:'bank', label:'Bank transfer', weight:5, sev:'high', re:/\b(iban|swift|bank\s?transfer|überweisung|wire|rechnung|invoice)\b/i },
        { id:'crypto', label:'Crypto', weight:5, sev:'high', re:/\b(crypto|bitcoin|btc|eth|ethereum|usdt|wallet)\b/i },
        { id:'altpay', label:'Other payment apps', weight:4, sev:'high', re:/\b(venmo|cash\s?app|cashapp|revolut|wise)\b/i },
        { id:'giftcard', label:'Gift cards / codes', weight:3, sev:'med', re:/\b(gift\s?card|gutschein|code)\b/i },

        // Private deal / off-site boosting phrasing
        { id:'offsite', label:'Off-site / outside', weight:4, sev:'high', re:/\b(off[\s-]?site|outside (the )?(site|website|chat)|außerhalb|extern)\b/i },
        { id:'private_boost', label:'Private boosting', weight:4, sev:'high', re:/\b(private\s+boost(ing)?|privates?\s+boost(ing)?)\b/i },
        { id:'deal', label:'Deal / cheaper', weight:2, sev:'low', re:/\b(deal|cheaper|billiger|discount|rabatt|offer|angebot)\b/i },
        { id:'vouch', label:'Vouch / rep', weight:2, sev:'low', re:/\b(vouch|rep|reputation|middleman)\b/i },
        { id:'add_me', label:'Add / contact me', weight:3, sev:'med', re:/\b(add\s+me|add\s+mich|adden|freundanfrage|friend\s+request|contact\s+me|schreib\s+mir)\b/i }
      ];

      function escapeHtml(s){
        return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
      }

      function findPolicySignals(text){
        const t = String(text || '');
        if (!t.trim()) return { score:0, hits:[], spans:[] };

        const spans = [];
        const hits = [];
        let score = 0;

        for (const rule of POLICY_RULES){
          const re = new RegExp(rule.re.source, rule.re.flags.includes('g') ? rule.re.flags : (rule.re.flags + 'g'));
          let m;
          let addedRule = false;
          while ((m = re.exec(t)) && spans.length < 20){
            const matchText = m[0];
            const start = m.index;
            const end = start + matchText.length;
            spans.push({ start, end, text: matchText, rule });
            addedRule = true;
            // prevent infinite loops on zero-length (shouldn't happen, but safe)
            if (m.index === re.lastIndex) re.lastIndex++;
          }
          if (addedRule){
            score += Number(rule.weight || 0);
            hits.push(rule);
          }
        }

        // De-dupe hits by id (keep order)
        const seen = new Set();
        const uniqHits = [];
        for (const h of hits){
          if (seen.has(h.id)) continue;
          seen.add(h.id);
          uniqHits.push(h);
        }

        // Merge/normalize overlapping spans for preview highlighting
        spans.sort((a,b)=>a.start-b.start || b.end-a.end);
        const merged = [];
        for (const s of spans){
          if (!merged.length){ merged.push(s); continue; }
          const last = merged[merged.length-1];
          if (s.start <= last.end){
            // overlap -> extend end
            if (s.end > last.end) last.end = s.end;
            // keep "worst" severity for display
            const sevRank = {low:1, med:2, high:3};
            if ((sevRank[s.rule.sev]||1) > (sevRank[last.rule.sev]||1)) last.rule = s.rule;
          } else merged.push(s);
        }

        return { score, hits: uniqHits, spans: merged };
      }

      function highlightPreview(text, spans){
        const t = String(text||'');
        if (!t || !Array.isArray(spans) || !spans.length) return '';
        let out = '';
        let pos = 0;
        for (const s of spans){
          out += escapeHtml(t.slice(pos, s.start));
          const sev = s.rule && s.rule.sev ? s.rule.sev : 'med';
          out += '<span class="lb-chat-policy-mark" data-sev="'+sev+'">' + escapeHtml(t.slice(s.start, s.end)) + '</span>';
          pos = s.end;
        }
        out += escapeHtml(t.slice(pos));
        return out;
      }

      function renderPolicyWarning(state, rawText){
        if (!policyWarn) return;
        const has = state && (state.hits && state.hits.length);
        if (!has){
          policyWarn.classList.add('d-none');
          if (policyWarnMatches) policyWarnMatches.textContent = '';
          if (policyWarnPreview) policyWarnPreview.innerHTML = '';
          return;
        }

        policyWarn.classList.remove('d-none');

        // Chips + score hint
        if (policyWarnMatches){
          const chips = state.hits.slice(0, 8).map(h => {
            const sev = h.sev || 'med';
            return '<span class="lb-chat-policy-chip" data-sev="'+sev+'">'+escapeHtml(h.label)+'</span>';
          }).join('');
          policyWarnMatches.innerHTML = '<div class="lb-chat-policy-warn__chips">'+chips+(state.hits.length>8?'<span class="lb-chat-policy-chip" data-sev="low">…</span>':'')+'</div>';
        }

        if (policyWarnPreview){
          const preview = highlightPreview(rawText, state.spans);
          // show a short preview only (avoid giant blocks)
          const clipped = preview.length > 500 ? (preview.slice(0, 500) + '…') : preview;
          policyWarnPreview.innerHTML = clipped ? ('Preview: ' + clipped) : '';
        }
      }


      // Dashboard-style confirm modal for policy warning (replaces window.confirm)
      const policyConfirmModalEl = document.getElementById('lbPolicyConfirmModal');
      const policyConfirmSubtitle = document.getElementById('lbPolicyConfirmSubtitle');
      const policyConfirmText = document.getElementById('lbPolicyConfirmText');
      const policyConfirmChips = document.getElementById('lbPolicyConfirmChips');

      function openPolicyConfirmModal(state){
        return new Promise(function(resolve){
          // If modal markup is missing, fall back to native confirm
          if (!policyConfirmModalEl){
            const labels = (state && state.hits) ? state.hits.map(h=>h.label).join(', ') : '';
            const riskLine = (state && (state.score >= 9 || (state.hits||[]).some(h=>h.sev==='high'))) ? 'High-risk terms detected.' : 'Suspicious terms detected.';
            const msg = "⚠️ Keep everything on the website chat.\n\n" + riskLine + (labels ? ("\nDetected: " + labels) : "") + "\n\nDo you still want to send it?";
            const ok = window.confirm(msg);
            return resolve(!!ok);
          }

          const hits = (state && state.hits) ? state.hits.slice(0) : [];
          const isHigh = state && (state.score >= 9 || hits.some(h=>h.sev==='high'));

          if (policyConfirmSubtitle){
            policyConfirmSubtitle.textContent = isHigh ? 'High-risk off-platform / payment hints detected' : 'Suspicious off-platform hints detected';
          }
          if (policyConfirmText){
            policyConfirmText.innerHTML = isHigh
              ? 'Please keep <b>all communication & payments</b> on this website chat. Sharing contact/payment details outside the platform is not allowed.'
              : 'Reminder: keep everything on the website chat. Avoid moving the conversation to private DMs or external platforms.';
          }
          if (policyConfirmChips){
            policyConfirmChips.innerHTML = '';
            hits.slice(0, 10).forEach(function(h){
              const chip = document.createElement('span');
              chip.className = 'lb-policy-chip';
              chip.setAttribute('data-sev', h.sev || 'med');
              chip.innerHTML = '<i class="fa-solid fa-flag me-1"></i>' + escapeHtml(h.label || h.id || 'match');
              policyConfirmChips.appendChild(chip);
            });
          }

          let resolved = false;
          function done(val){
            if (resolved) return;
            resolved = true;
            resolve(!!val);
          }

          let inst = null;
          // Prefer Bootstrap modal if available; otherwise use a lightweight custom show/hide
          const hasBootstrap = (typeof bootstrap !== 'undefined' && bootstrap && bootstrap.Modal);
          if (hasBootstrap){
            try {
              inst = bootstrap.Modal.getInstance(policyConfirmModalEl);
              if (!inst) inst = new bootstrap.Modal(policyConfirmModalEl, { backdrop: true, keyboard: true, focus: true });
            } catch (e) { inst = null; }
          }

          // Lightweight controller when Bootstrap isn't present (or failed)
          let backdropEl = null;
          const manual = {
            show: function(){
              policyConfirmModalEl.style.display = 'block';
              // force reflow for transition
              policyConfirmModalEl.getBoundingClientRect();
              policyConfirmModalEl.classList.add('show');
              policyConfirmModalEl.removeAttribute('aria-hidden');
              policyConfirmModalEl.setAttribute('aria-modal', 'true');
              document.body.classList.add('modal-open');

              backdropEl = document.createElement('div');
              backdropEl.className = 'modal-backdrop fade show';
              document.body.appendChild(backdropEl);
            },
            hide: function(){
              policyConfirmModalEl.classList.remove('show');
              policyConfirmModalEl.setAttribute('aria-hidden', 'true');
              policyConfirmModalEl.removeAttribute('aria-modal');
              policyConfirmModalEl.style.display = 'none';
              document.body.classList.remove('modal-open');
              if (backdropEl && backdropEl.parentNode) backdropEl.parentNode.removeChild(backdropEl);
              backdropEl = null;
            }
          };

          const btnSend = policyConfirmModalEl.querySelector('[data-policy-send]');
          const btnCancel = policyConfirmModalEl.querySelector('[data-policy-cancel]');

          const onSend = function(){ try{ if (inst) inst.hide(); else manual.hide(); }catch(e){} done(true); };
          const onCancel = function(){ try{ if (inst) inst.hide(); else manual.hide(); }catch(e){} done(false); };
          const onHidden = function(){ if (!resolved) done(false); cleanup(); };

          let onKey = null;
          let onBackdropClick = null;

          function cleanup(){
            try { policyConfirmModalEl.removeEventListener('hidden.bs.modal', onHidden); } catch(e){}
            try { if (btnSend) btnSend.removeEventListener('click', onSend); } catch(e){}
            try { if (btnCancel) btnCancel.removeEventListener('click', onCancel); } catch(e){}
            try { if (onKey) document.removeEventListener('keydown', onKey); } catch(e){}
            try { if (onBackdropClick) policyConfirmModalEl.removeEventListener('mousedown', onBackdropClick); } catch(e){}
          }

          if (btnSend) btnSend.addEventListener('click', onSend);
          if (btnCancel) btnCancel.addEventListener('click', onCancel);

          if (inst){
            policyConfirmModalEl.addEventListener('hidden.bs.modal', onHidden);
          } else {
            onKey = function(ev){ if (ev.key === 'Escape'){ onCancel(); } };
            onBackdropClick = function(ev){ if (ev.target === policyConfirmModalEl){ onCancel(); } };
            document.addEventListener('keydown', onKey);
            policyConfirmModalEl.addEventListener('mousedown', onBackdropClick);
          }

          try { if (inst) inst.show(); else manual.show(); } catch(e) { done(false); }
        });
      }


      function setError(msg){
        if (!errBox) return;
        if (!msg){ errBox.classList.add('d-none'); errBox.textContent=''; return; }
        errBox.textContent = msg;
        errBox.classList.remove('d-none');
      }

      function clearFile(){
        if (previewUrl){ URL.revokeObjectURL(previewUrl); previewUrl = null; }
        if (fileInput) fileInput.value = '';
        if (preview){ preview.classList.add('d-none'); }
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
        // only react when focus is in chat input or chat form
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

      // Live policy hint while typing
      if (msgInput){
        const updatePolicyHint = function(){
          const _t = msgInput.value || '';
          const state = findPolicySignals(_t);
          renderPolicyWarning(state, _t);
        };
        msgInput.addEventListener('input', updatePolicyHint);
        msgInput.addEventListener('change', updatePolicyHint);
        // initial
        updatePolicyHint();
      }

      // Submit via AJAX with FormData (supports file)
      form.addEventListener('submit', async function(e){
        if (form.getAttribute('data-chat-locked') === '1') return;
        e.preventDefault();

        const msg = (msgInput && msgInput.value) ? msgInput.value.trim() : '';
        const hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

        if (!msg && !hasFile){
          setError('Please type a message or attach an image.');
          return;
        }

        // Policy warning: block spammy off-platform requests with a confirm step
        const state = findPolicySignals(msg);
        renderPolicyWarning(state, msg);
        if (state && state.hits && state.hits.length){
          const labels = state.hits.map(h=>h.label).join(', ');
          const riskLine = (state.score >= 9 || state.hits.some(h=>h.sev==='high')) ? 'High-risk terms detected.' : 'Suspicious terms detected.';
          const ok = await openPolicyConfirmModal(state);
          if (!ok) return;
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
          // Some endpoints return JSON, some return string. We don't depend on it.
          try { if (typeof resp === 'string') { JSON.parse(resp); } } catch(e){}
          if (msgInput) msgInput.value = '';
          clearFile();
          // Refresh chat list
          try { load_messages(); } catch(e){}
          try { scrollChatToBottom(false); } catch(e){}
        }).fail(function(xhr){
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

    // ---- Chat image modal (open in modal instead of new tab) ----
    (function initChatImageModal(){
      const modalEl = document.getElementById('lbChatImageModal');
      const modalImg = document.getElementById('lbChatImageModalImg');
      if (!modalEl || !modalImg) return;

      let bsModal = null;
      try {
        if (window.bootstrap && window.bootstrap.Modal) {
          bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl, { keyboard: true });
        }
      } catch (e) { bsModal = null; }

      // Clear src on close (frees memory + fixes iOS scroll issues)
      try {
        modalEl.addEventListener('hidden.bs.modal', function(){
          modalImg.src = '';
        });
      } catch (e) {}

      document.addEventListener('click', function(e){
        const img = e.target.closest('#chat_messages img, .lb-msg__content img');
        if (!img) return;
        // ignore avatars/stickers/icons
        if (img.classList.contains('lb-msg__avatar') || img.classList.contains('lb-chat-sticker')) return;

        // Only handle real chat attachments (must be within chat area)
        const inChat = !!img.closest('#chat_messages');
        if (!inChat) return;

        const link = img.closest('a');
        if (link) e.preventDefault();
        e.preventDefault();
        e.stopPropagation();

        const src = img.getAttribute('src') || '';
        if (!src) return;
        modalImg.src = src;
        if (bsModal) {
          bsModal.show();
        } else {
          // fallback
          window.open(src, '_blank');
        }
      }, true);
    })();

    // ---- Edit own message (booster) ----
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.lb-msg__edit');
      if (!btn) return;

      e.preventDefault();
      e.stopPropagation();

      const id = btn.getAttribute('data-msg-id');
      if (!id) return;

      const bubble = btn.closest('.lb-msg__bubble');
      if (!bubble) return;

      const contentEl = bubble.querySelector('.lb-msg__content');
      if (!contentEl) return;

      if (bubble.getAttribute('data-editing') === '1') return;
      bubble.setAttribute('data-editing', '1');

      const prevHtml = contentEl.innerHTML;
      const msg = (chat_json && chat_json[id] && chat_json[id].raw) ? chat_json[id].raw : htmlToPlain(prevHtml);

      contentEl.innerHTML = `
        <textarea class="lb-msg__editor" rows="3">${escapeHtml(String(msg))}</textarea>
        <div class="lb-msg__edit-actions">
          <button type="button" class="lb-msg__edit-cancel">Cancel</button>
          <button type="button" class="lb-msg__edit-save">Save</button>
        </div>
      `;

      const cancelBtn = bubble.querySelector('.lb-msg__edit-cancel');
      const saveBtn   = bubble.querySelector('.lb-msg__edit-save');
      const ta        = bubble.querySelector('.lb-msg__editor');

      cancelBtn && cancelBtn.addEventListener('click', function(ev){
        ev.preventDefault(); ev.stopPropagation();
        contentEl.innerHTML = prevHtml;
        bubble.setAttribute('data-editing','0');
      }, { once:true });

      saveBtn && saveBtn.addEventListener('click', function(ev){
        ev.preventDefault(); ev.stopPropagation();
        const nextText = (ta && ta.value ? ta.value : '').trim();
        if (!nextText) { ta && ta.focus(); return; }

        const idInt = parseInt(id, 10);
        const payload = Object.assign({}, { order_id: order_id }, {
          id: idInt,
          message_id: idInt,
          message: nextText
        });

        fetch_api('edit_chat_message', payload)
          .done(function(res){
            try { res = JSON.parse(res); } catch (e2) { res = { success:false, message: res }; }
            if (!res || res.success !== true) {
              contentEl.innerHTML = prevHtml;
              bubble.setAttribute('data-editing','0');
              if (typeof create_toast === 'function') create_toast('danger','Error', (res && res.message) ? res.message : 'Could not edit message.');
              return;
            }
            bubble.setAttribute('data-editing','0');
            // force refresh
            lastRenderedLastId = null;
            load_messages();
          })
          .fail(function(){
            contentEl.innerHTML = prevHtml;
            bubble.setAttribute('data-editing','0');
            if (typeof create_toast === 'function') create_toast('danger','Error','Could not edit message.');
          });
      }, { once:true });

      setTimeout(function(){ try { ta && ta.focus(); } catch(e3){} }, 0);
    }, true);

    // ---- status checker + safe hybrid realtime/polling ----
    function checkOrderStatusSoft() {
      fetch_api('check_order_status', post_data).done(function (resp) {
        try { resp = JSON.parse(resp); } catch(e){ return; }

        const isRanked5s = resp && (resp.is_ranked_5s === true || resp.is_ranked_5s === 1 || resp.is_ranked_5s === '1' || resp.ranked_5s === true);
        const isMultiBooster = isRanked5s || (resp && (resp.is_multi_booster === true || resp.is_multi_booster === 1 || resp.is_multi_booster === '1'));
        const nextStatus = String(resp.order_status || resp.status || '').toUpperCase();
        const currentStatus = String(order_status || '').toUpperCase();

        if (isMultiBooster && (resp.removed_from_order === true || resp.force_redirect === true)) {
          try {
            if (typeof create_toast === 'function') {
              create_toast('danger', 'Removed from order', resp.message || 'You are no longer assigned to this order.');
            }
          } catch(e) {}
          setTimeout(function () {
            window.location.href = resp.redirect_url || '/booster-area/orders';
          }, 500);
          return;
        }

        // Ranked 5s can change lanes/slot count while the order itself stays IN_PROGRESS.
        // Do not reload the whole detail page just because another booster joined.
        if (isMultiBooster) {
          if (resp.available === true || ['PAID','PROCESSING','IN_PROGRESS','PAUSED','COMPLETED'].includes(nextStatus)) {
            return;
          }
        }

        if (nextStatus && nextStatus !== currentStatus) {
          if (document.visibilityState === 'visible') { message_sound(); }
          setTimeout(function () { location.reload(); }, 1000);
        }
      });
    }

    window.lbOrderViewChatUpdate = function(data){
      if (!data || parseInt(data.order_id || 0, 10) === order_id) {
        load_messages();
      }
    };

    window.lbOrderViewStatusUpdate = function(data){
      if (!data || parseInt(data.order_id || 0, 10) === order_id) {
        if (data && String(data.type || '') === 'ranked_5s_booster_removed') {
          const removedId = parseInt(data.removed_booster_id || 0, 10);
          if (removedId && removedId === user_id) {
            try {
              if (typeof create_toast === 'function') {
                create_toast('danger', 'Removed from order', 'You are no longer assigned to this order.');
              }
            } catch(e) {}
            setTimeout(function(){ window.location.href = '/booster-area/orders'; }, 450);
            return;
          }
        }
        location.reload();
      }
    };

    $(document).ready(function () {
      // Only a real click inside the chat marks messages as Read
      $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

      load_messages();
    });

  })();
</script>
<?php endif; ?>



<script>
  $(document).ready(function () {
        // Only a real click inside the chat marks messages as Read
        $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

    new HSFileAttach('.js-file-attach');
    HSCore.components.HSTomSelect.init('.js-select');

    $('#dropAssignmentDate').daterangepicker({
      parentEl: '#request_drop_md',
      singleDatePicker: true,
      autoApply: true,
      timePicker: true,
      timePicker24Hour: true,
      timePickerSeconds: true,
      timePickerIncrement: 1,
      locale: { format: 'DD.MM.YYYY HH:mm:ss' }
    });

    // Keep hidden backend value in sync (YYYY-MM-DD HH:mm:ss)
    $('#dropAssignmentDate').on('apply.daterangepicker', function (ev, picker) {
      const backend = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
      const display = picker.startDate.format('DD.MM.YYYY HH:mm:ss');
      $('#dropAssignmentDateValue').val(backend);
      $(this).val(display);
    });

    // If prefilled via claimed_at, ensure hidden backend value exists
    (function(){
      const disp = ($('#dropAssignmentDate').val() || '').trim();
      const hid = ($('#dropAssignmentDateValue').val() || '').trim();
      if (disp && !hid && window.moment) {
        const m = window.moment(disp, 'DD.MM.YYYY HH:mm:ss', true);
        if (m.isValid()) $('#dropAssignmentDateValue').val(m.format('YYYY-MM-DD HH:mm:ss'));
      }
    })();

        // Live earning + timer (auto update at 00:00 without F5) — fixed (no freeze)
    const orderId = <?= (int) ($data['id'] ?? 0) ?>;

    const els = {
      amountTop: document.getElementById('js-earning-amount'),
      amountModal: document.getElementById('js-earning-modal-amount'),
      timerTop: document.getElementById('js-earning-timer'),
      timerModal: document.getElementById('js-earning-modal-timer'),
      timerTopChip: document.getElementById('js-earning-timer-chip'),
      timerModalChip: document.getElementById('js-earning-modal-timer-chip'),
    };

    let lastPrice = (els.amountTop ? els.amountTop.textContent.trim() : '');

    // --- Server time sync (prevents countdown drift) ---
    let serverOffsetMs = 0;        // serverNowMs = Date.now() + serverOffsetMs
    let timerEndAtServerMs = null; // absolute server timestamp (ms) when next step happens
    let lastRefreshTry = 0;
    const refreshCooldownMs = 2000;
    let pendingSync = false;
    const DEFAULT_STEP_SECONDS = 90; // booster cut step interval (seconds)

    function applyServerTs(ts) {
      const sec = parseInt(ts, 10);
      if (!Number.isFinite(sec) || sec <= 0) return;
      serverOffsetMs = (sec * 1000) - Date.now();
    }
    function serverNowMs() { return Date.now() + serverOffsetMs; }

    function formatMMSS(totalSeconds) {
      const s = Math.max(0, parseInt(totalSeconds, 10) || 0);
      const mm = String(Math.floor(s / 60)).padStart(2, '0');
      const ss = String(s % 60).padStart(2, '0');
      return `${mm}:${ss}`;
    }

    function bump(el) {
      if (!el) return;
      el.classList.remove('lb-price-bump');
      void el.offsetWidth;
      el.classList.add('lb-price-bump');
      el.addEventListener('animationend', () => el.classList.remove('lb-price-bump'), { once: true });
    }

    function showTimer(show) {
      if (els.timerTopChip) els.timerTopChip.style.display = show ? '' : 'none';
      if (els.timerModalChip) els.timerModalChip.style.display = show ? '' : 'none';

      // When hiding (max cut reached), clear any stale text
      if (!show) {
        if (els.timerTop) els.timerTop.textContent = '';
        if (els.timerModal) els.timerModal.textContent = '';
      }
    }

    function setTimer(seconds) {
      // Hide timer if backend says “no next change”
      if (seconds === null || typeof seconds === 'undefined') {
        timerEndAtServerMs = null;
        showTimer(false);
        return;
      }

      let sec = parseInt(seconds, 10);
      if (!Number.isFinite(sec) || sec <= 0) sec = 1;

      timerEndAtServerMs = serverNowMs() + (sec * 1000);
      showTimer(true);

      const txt = formatMMSS(sec);
      if (els.timerTop) { els.timerTop.textContent = txt; els.timerTop.setAttribute('data-seconds', String(sec)); }
      if (els.timerModal) { els.timerModal.textContent = txt; els.timerModal.setAttribute('data-seconds', String(sec)); }
    }

    function updatePriceDisplay(newVal) {
      const v = (newVal == null) ? '' : String(newVal).trim();
      if (!v) return;

      if (els.amountTop) els.amountTop.innerHTML = v;
      if (els.amountModal) els.amountModal.innerHTML = v;

      if (v !== lastPrice) {
        bump(els.amountTop);
        bump(els.amountModal);
        lastPrice = v;
      }
    }

    let fetchLock = 0;
    function fetchLatest(force = false) {
      const now = Date.now();
      if (!force && now - fetchLock < 800) return;
      fetchLock = now;

      $.ajax({
        url: '<?= AJAX_URL ?>',
        method: 'POST',
                data: { action: 'get_updated_prices', order_ids: [orderId], _ts: Date.now() },
        success: function (res) {
          if (typeof res === 'string') {
            try { res = JSON.parse(res); } catch (e) { res = null; }
          }
          if (!res || !res.success || !res.data || !res.data[0]) {
            // If we just crossed a step boundary, keep retrying briefly even though
            // the visible timer already restarted (prevents the earning from 'sticking').
            if (pendingSync) {
              setTimeout(() => fetchLatest(true), 1200);
            }
            return;
          }

          if (res.server_ts) applyServerTs(res.server_ts);

          const o = res.data[0];
          updatePriceDisplay(o.price);
          setTimer(o.next_change_in);
          lastRefreshTry = 0;
          pendingSync = false; // allow immediate refresh next time we hit 00:00
        },
        error: function () {
          if (timerEndAtServerMs && Math.ceil((timerEndAtServerMs - serverNowMs()) / 1000) <= 0) {
            setTimeout(() => fetchLatest(true), 1200);
          }
        }
      });
    }

    // --- INIT ---
    const initSec =
      parseInt((els.timerTop && els.timerTop.getAttribute('data-seconds')) || '', 10) ||
      parseInt((els.timerModal && els.timerModal.getAttribute('data-seconds')) || '', 10) ||
      0;

    if (initSec) setTimer(initSec);

    // Realtime price hook. The timer still refreshes exactly at 00:00.
    window.lbOrderViewPriceUpdate = function(data){
      if (!data || parseInt(data.order_id || 0, 10) === orderId) {
        fetchLatest(true);
      }
    };

    // Countdown tick + instant refresh at 00:00 (with cooldown + auto retry)
    setInterval(() => {
      if (!timerEndAtServerMs) return;

      const left = Math.ceil((timerEndAtServerMs - serverNowMs()) / 1000);

      if (left > 0) {
        const txt = formatMMSS(left);
        if (els.timerTop) els.timerTop.textContent = txt;
        if (els.timerModal) els.timerModal.textContent = txt;
        return;
      }

      // The server publishes price_update through WebSocket; only restart the visual countdown here.
      setTimer(DEFAULT_STEP_SECONDS);
    }, 1000);


});
</script>

<script>
  $(document).ready(function () {
        // Only a real click inside the chat marks messages as Read
        $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

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
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const card = document.querySelector('.lb-booster-edit-card');
    if (!card) return;

    const AJAX_URL = '<?= AJAX_URL ?>';
    const ASSET_URL = '<?= ASSET_URL ?>';
    const LOL_CHAMP_URL = '<?= defined('LOL_CHAMP_URL') ? LOL_CHAMP_URL : '' ?>';

    const modalEl = document.getElementById('lbBoosterEditModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const modal = new bootstrap.Modal(modalEl);

    const titleEl = document.getElementById('lbBoosterEditTitle');
    const errEl = document.getElementById('lbBoosterEditError');
    const fieldEl = document.getElementById('lbEditField');
    const saveBtn = document.getElementById('lbBoosterEditSave');

    const panes = Array.from(modalEl.querySelectorAll('[data-lb-edit-pane]'));
    const showPane = (name) => {
      panes.forEach(p => p.classList.toggle('d-none', p.getAttribute('data-lb-edit-pane') !== name));
    };

    const setError = (msg) => {
      if (!msg) { errEl.classList.add('d-none'); errEl.textContent = ''; return; }
      errEl.classList.remove('d-none');
      errEl.textContent = String(msg);
    };

    const parsePipeList = (v) => {
      if (!v) return [];
      const s = String(v).trim();
      if (!s) return [];
      if (s.startsWith('[')) {
        try {
          const j = JSON.parse(s);
          if (Array.isArray(j)) return j.filter(Boolean).map(x => String(x).trim()).filter(Boolean);
        } catch (e) {}
      }
      const sep = s.includes('|') ? '|' : (s.includes(',') ? ',' : null);
      if (!sep) return [s];
      return s.split(sep).map(x => x.trim()).filter(Boolean);
    };

    // Initialize TomSelect if available
    function initTom(selectEl) {
      if (!selectEl) return;
      if (selectEl.tomselect) return;
      if (typeof TomSelect === 'undefined') return;
      // keep minimal options; match your profile page feel
      new TomSelect(selectEl, {
        plugins: ['remove_button'],
        create: false,
        persist: false,
        maxItems: null
      });
    }

    const tzSelect = document.getElementById('lbEditTimezoneSelect');
    const champSelect = document.getElementById('lbEditChampionsSelect');
    const rolesSelect = document.getElementById('lbEditRolesSelect');
    const langsSelect = document.getElementById('lbEditLanguagesSelect');

    initTom(champSelect);
    initTom(rolesSelect);
    initTom(langsSelect);

    function setMultiSelect(selectEl, values) {
      if (!selectEl) return;
      const vals = Array.isArray(values) ? values : [];
      if (selectEl.tomselect) {
        selectEl.tomselect.setValue(vals, true);
        return;
      }
      Array.from(selectEl.options).forEach(opt => {
        opt.selected = vals.includes(opt.value);
      });
    }

    function getMultiSelectValues(selectEl) {
      if (!selectEl) return [];
      if (selectEl.tomselect) {
        const v = selectEl.tomselect.getValue();
        return Array.isArray(v) ? v : String(v).split(',').filter(Boolean);
      }
      return Array.from(selectEl.selectedOptions).map(o => o.value);
    }

    function toastFromResponse(resp) {
      const t = resp && resp.sendToast ? resp.sendToast : null;
      if (!t) return;
      if (typeof window.sendToast === 'function') { window.sendToast(t); return; }
      console.log('[Toast]', t.type, t.title, t.message);
    }

    function updateDataset(field, data) {
      if (!data) return;
      if (field === 'timezone' && data.timezone_raw !== undefined) card.dataset.lbTimezone = data.timezone_raw;
      if (field === 'rank' && data.rank_tier !== undefined) {
        card.dataset.lbRankTier = String(data.rank_tier || 0);
        card.dataset.lbRankDiv = String(data.rank_div || 0);
      }
      if (field === 'champions' && data.champions_raw !== undefined) card.dataset.lbChampions = data.champions_raw;
      if (field === 'roles' && data.roles_raw !== undefined) card.dataset.lbRoles = data.roles_raw;
      if (field === 'languages' && data.languages_raw !== undefined) card.dataset.lbLanguages = data.languages_raw;
    }

    function renderRoles(list) {
      const wrap = document.querySelector('#lbBoosterRoles .booster-intro-roles');
      if (!wrap) return;
      wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<span class="na">N/A</span>'; return; }
      list.slice(0,5).forEach(role => {
        const span = document.createElement('span');
        span.className = 'role-pill';
        span.title = role;
        const img = document.createElement('img');
        img.src = ASSET_URL + '/core/main/img/lol/roles/' + role + '.png';
        img.alt = role;
        span.appendChild(img);
        wrap.appendChild(span);
      });
    }

    function renderLangs(list) {
      const wrap = document.querySelector('#lbBoosterLangs .booster-intro-langs');
      if (!wrap) return;
      wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<span class="na">N/A</span>'; return; }
      list.slice(0,5).forEach(l => {
        const key = String(l).trim().toLowerCase();
        const img = document.createElement('img');
        img.className = 'flag';
        img.src = ASSET_URL + '/core/main/img/languages/' + encodeURIComponent(key) + '.png';
        img.alt = l;
        wrap.appendChild(img);
      });
    }

    function renderChamps(list) {
      const wrap = document.querySelector('#lbBoosterChamps .booster-intro-champs');
      if (!wrap) return;
      wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<span class="na">N/A</span>'; return; }
      const limited = list.slice(0, 4);
      limited.forEach(ch => {
        const img = document.createElement('img');
        img.className = 'champ';
        img.src = (LOL_CHAMP_URL ? (LOL_CHAMP_URL + '/' + encodeURIComponent(ch) + '.png') : '');
        img.alt = ch;
        img.title = ch;
        wrap.appendChild(img);
      });
      const remaining = Math.max(0, list.length - limited.length);
      if (remaining > 0) {
        const tooltipItems = list.map(ch => ({
          name: ch,
          icon: (LOL_CHAMP_URL ? (LOL_CHAMP_URL.replace(/\/$/, '') + '/' + encodeURIComponent(ch) + '.png') : '')
        }));
        const more = document.createElement('span');
        more.className = 'more js-lb-champs-tooltip';
        more.textContent = '+' + remaining;
        more.setAttribute('data-title', 'All champions');
        more.setAttribute('data-items', JSON.stringify(tooltipItems));
        more.setAttribute('tabindex', '0');
        wrap.appendChild(more);
        if (window.lbInitChampTooltips) window.lbInitChampTooltips();
      }
    }

    function renderRank(rankId, rankName, rankIcon) {
      const pill = document.getElementById('lbBoosterRankPill');
      if (!pill) return;
      const img = pill.querySelector('.lb-rank-img');
      const name = pill.querySelector('.lb-rank-name');
      if (img && rankIcon) img.src = rankIcon;
      if (name) name.textContent = rankName || 'Unranked';
    }

    function renderTimezone(tzDisplay) {
      const pill = document.getElementById('lbBoosterTimezonePill');
      if (!pill) return;
      const name = pill.querySelector('.lb-tz-name');
      if (name) name.textContent = tzDisplay || 'N/A';
    }

    function openEditor(field) {
      setError('');
      fieldEl.value = field;

      const pretty =
        (field === 'icon') ? 'Avatar' :
        (field === 'cover') ? 'Banner' :
        (field === 'rank') ? 'Rank' :
        (field === 'timezone') ? 'Timezone' :
        (field === 'champions') ? 'Champions' :
        (field === 'roles') ? 'Lanes' :
        (field === 'languages') ? 'Languages' :
        field.charAt(0).toUpperCase() + field.slice(1);

      titleEl.textContent = 'Edit ' + pretty;

      // reset file input
      const fileInput = document.getElementById('lbEditFileInput');
      if (fileInput) fileInput.value = '';

      // show correct pane + prefill values from dataset
      if (field === 'icon' || field === 'cover') {
        showPane('file');
        const lbl = document.getElementById('lbEditFileLabel');
        if (lbl) lbl.textContent = (field === 'icon') ? 'Upload avatar' : 'Upload banner';
      } else if (field === 'timezone') {
        showPane('timezone');
        if (tzSelect) tzSelect.value = card.dataset.lbTimezone || '';
      } else if (field === 'rank') {
        showPane('rank');
        const tierEl = document.getElementById('lbEditRankTier');
        const divEl = document.getElementById('lbEditRankDiv');
        if (tierEl) tierEl.value = card.dataset.lbRankTier || '0';
        if (divEl) divEl.value = card.dataset.lbRankDiv || '0';
      } else if (field === 'champions') {
        showPane('champions');
        setMultiSelect(champSelect, parsePipeList(card.dataset.lbChampions || ''));
      } else if (field === 'roles') {
        showPane('roles');
        setMultiSelect(rolesSelect, parsePipeList(card.dataset.lbRoles || ''));
      } else if (field === 'languages') {
        showPane('languages');
        setMultiSelect(langsSelect, parsePipeList(card.dataset.lbLanguages || ''));
      } else {
        showPane('text');
      }

      modal.show();
    }

    // click handlers (pencil)
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-lb-edit]');
      if (!btn) return;
      // only within our card
      if (!btn.closest('.lb-booster-edit-card')) return;
      e.preventDefault();
      const field = btn.getAttribute('data-lb-edit');
      if (!field) return;
      openEditor(field);
    });

    async function save() {
      const field = fieldEl.value;
      if (!field) return;

      setError('');
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving...';

      try {
        const fd = new FormData();
        fd.append('action', 'booster_profile_quick_update');
        fd.append('field', field);

        if (field === 'icon' || field === 'cover') {
          const file = document.getElementById('lbEditFileInput').files[0];
          if (!file) { setError('Please choose a file.'); return; }
          fd.append('image_url', file);
        } else if (field === 'rank') {
          fd.append('rank_tier', document.getElementById('lbEditRankTier').value);
          fd.append('rank_div', document.getElementById('lbEditRankDiv').value);
        } else if (field === 'timezone') {
          fd.append('value', tzSelect ? tzSelect.value : '');
        } else if (field === 'champions') {
          getMultiSelectValues(champSelect).forEach(v => fd.append('champions[]', v));
        } else if (field === 'roles') {
          getMultiSelectValues(rolesSelect).forEach(v => fd.append('roles[]', v));
        } else if (field === 'languages') {
          getMultiSelectValues(langsSelect).forEach(v => fd.append('languages[]', v));
        } else {
          fd.append('value', (document.getElementById('lbEditTextInput') || {}).value || '');
        }

        const res = await fetch(AJAX_URL, { method: 'POST', body: fd });
        const resp = await res.json().catch(() => null);
        if (!resp) { setError('Unexpected server response.'); return; }

        if (resp.validationErrors && resp.validationErrors[field]) {
          setError(resp.validationErrors[field]);
          toastFromResponse(resp);
          return;
        }

        if (resp.success === false) {
          setError(resp.message || 'Update failed.');
          toastFromResponse(resp);
          return;
        }

        toastFromResponse(resp);

        const data = resp.data || {};
        updateDataset(field, data);

        // UI update
        if (field === 'icon' && data.icon_url) {
          const img = document.querySelector('#lbBoosterAvatar img');
          if (img) img.src = data.icon_url;
        }
        if (field === 'cover' && data.cover_url) {
          const bg = document.getElementById('lbBoosterCover');
          if (bg) bg.style.backgroundImage = `url('${data.cover_url}')`;
        }
        if (field === 'timezone') {
          renderTimezone(data.timezone_display);
        }
        if (field === 'rank') {
          renderRank(data.rank_id, data.rank_name, data.rank_icon);
        }
        if (field === 'champions') {
          const list = Array.isArray(data.champions) ? data.champions : parsePipeList(card.dataset.lbChampions || '');
          renderChamps(list);
        }
        if (field === 'roles') {
          const list = Array.isArray(data.roles) ? data.roles : parsePipeList(card.dataset.lbRoles || '');
          renderRoles(list);
        }
        if (field === 'languages') {
          const list = Array.isArray(data.languages) ? data.languages : parsePipeList(card.dataset.lbLanguages || '');
          renderLangs(list);
        }

        modal.hide();
      } catch (err) {
        setError('Request failed.');
        console.error(err);
      } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';
      }
    }

    saveBtn.addEventListener('click', function () { save(); });
  });
</script>

<?= $this->end() ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('request_drop_md');
  if (!modalEl) return;

  const step1 = modalEl.querySelector('[data-step="1"]');
  const step2 = modalEl.querySelector('[data-step="2"]');

  const pill1 = modalEl.querySelector('[data-step-pill="1"]');
  const pill2 = modalEl.querySelector('[data-step-pill="2"]');

  const btnBack = modalEl.querySelector('[data-step-back]');
  const btnNext = modalEl.querySelector('[data-step-next]');
  const btnSubmit = modalEl.querySelector('[data-step-submit]');

  const formEl = modalEl.querySelector('form');

  const step1Alert = modalEl.querySelector('#dropStep1Alert');

  const dateDisplay = modalEl.querySelector('#dropAssignmentDate');
  const dateValue = modalEl.querySelector('#dropAssignmentDateValue');

  const reasonType = modalEl.querySelector('select[name="reason_type"]');
  const reasonCustom = modalEl.querySelector('[data-lb-reason-select]');
  const reasonButton = modalEl.querySelector('[data-lb-reason-toggle]');
  const reasonLabel = modalEl.querySelector('[data-lb-reason-label]');
  const reasonMenu = modalEl.querySelector('[data-lb-reason-menu]');
  const reasonOptions = reasonMenu ? Array.from(reasonMenu.querySelectorAll('[data-value]')) : [];

  const opggInput = modalEl.querySelector('input[name="progress_url"]');
  const opggBadge = modalEl.querySelector('#dropOpggBadge');

  const progressInput = modalEl.querySelector('input[name="progress"]');
  const progressWrap = modalEl.querySelector('#dropProgressWrap');

  const hasProgressRadio = modalEl.querySelector('#dropHasProgress');
  const noProgressRadio = modalEl.querySelector('#dropNoProgress');
  const zeroHidden = modalEl.querySelector('#dropZeroProgressHidden');

  const zeroWarn = modalEl.querySelector('#dropZeroWarning');
  const zeroConfirmWrap = modalEl.querySelector('#dropZeroConfirmWrap');
  const zeroConfirmInput = modalEl.querySelector('#dropZeroConfirmInput');

  const autoCard = modalEl.querySelector('#dropAutoProgressCard');
  const autoTitle = modalEl.querySelector('#dropAutoProgressTitle');
  const autoText = modalEl.querySelector('#dropAutoProgressText');
  const autoStats = modalEl.querySelector('#dropAutoProgressStats');
  const autoSummary = modalEl.querySelector('#dropAutoProgressSummary');
  const autoRemaining = modalEl.querySelector('#dropAutoProgressRemaining');
  const autoPayout = modalEl.querySelector('#dropAutoProgressPayout');
  const autoConfirmWrap = modalEl.querySelector('#dropAutoConfirmWrap');
  const autoConfirmCheck = modalEl.querySelector('#dropAutoConfirmCheck');
  const autoConfirmedHidden = modalEl.querySelector('#dropAutoProgressConfirmed');
  const autoProgressTo = modalEl.querySelector('#dropAutoProgressTo');
  const autoProgressType = modalEl.querySelector('#dropAutoProgressType');
  const autoProgressNote = modalEl.querySelector('#dropAutoProgressNote');
  const orderIdInput = modalEl.querySelector('input[name="order_id"]');

  const reasonTextarea = modalEl.querySelector('textarea[name="reason"]');

  let prevProgressValue = '';
  let autoProgressLoaded = false; // disabled: no auto progress
  let autoProgressLoading = false; // disabled: no auto progress

  function closeReasonSelect() {
    if (!reasonCustom) return;
    reasonCustom.classList.remove('is-open');
    if (reasonButton) reasonButton.setAttribute('aria-expanded', 'false');
  }

  function openReasonSelect() {
    if (!reasonCustom) return;
    reasonCustom.classList.add('is-open');
    if (reasonButton) reasonButton.setAttribute('aria-expanded', 'true');
  }

  function syncReasonSelectLabel() {
    if (!reasonType || !reasonLabel) return;
    const value = (reasonType.value || '').trim();
    reasonLabel.textContent = value || 'Select a reason';
    if (reasonCustom) reasonCustom.classList.toggle('has-value', !!value);
    reasonOptions.forEach(function (opt) {
      const active = (opt.dataset.value || '') === value;
      opt.classList.toggle('is-selected', active);
      opt.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  }

  function initReasonSelect() {
    if (!reasonType || !reasonCustom || !reasonButton) return;

    syncReasonSelectLabel();

    reasonButton.addEventListener('click', function () {
      if (reasonCustom.classList.contains('is-open')) closeReasonSelect();
      else openReasonSelect();
    });

    reasonOptions.forEach(function (opt) {
      opt.addEventListener('click', function () {
        reasonType.value = opt.dataset.value || '';
        reasonType.dispatchEvent(new Event('change', { bubbles: true }));
        syncReasonSelectLabel();
        clearInvalid(reasonType);
        closeReasonSelect();
        reasonButton.focus();
      });
    });

    reasonType.addEventListener('change', function () {
      syncReasonSelectLabel();
      clearInvalid(reasonType);
    });

    reasonButton.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openReasonSelect();
        const current = reasonOptions.find(function (opt) { return opt.classList.contains('is-selected'); });
        (current || reasonOptions[0] || reasonButton).focus();
      }
    });

    reasonOptions.forEach(function (opt, index) {
      opt.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          (reasonOptions[index + 1] || reasonOptions[0] || opt).focus();
        } else if (event.key === 'ArrowUp') {
          event.preventDefault();
          (reasonOptions[index - 1] || reasonOptions[reasonOptions.length - 1] || opt).focus();
        } else if (event.key === 'Escape') {
          event.preventDefault();
          closeReasonSelect();
          reasonButton.focus();
        }
      });
    });

    document.addEventListener('click', function (event) {
      if (!reasonCustom.contains(event.target)) closeReasonSelect();
    });
  }

  initReasonSelect();

  function showInlineAlert(msg) {
    if (!step1Alert) return;
    step1Alert.innerHTML = '<i class="fa-duotone fa-triangle-exclamation me-2"></i>' + msg;
    step1Alert.classList.remove('d-none');
  }

  function clearInlineAlert() {
    if (!step1Alert) return;
    step1Alert.classList.add('d-none');
    step1Alert.textContent = '';
  }

  function markInvalid(el, msg) {
    if (!el) return;
    el.classList.add('is-invalid');
    el.setAttribute('aria-invalid', 'true');

    const feedbackHost = (el === reasonType && reasonCustom) ? reasonCustom : el;
    if (el === reasonType && reasonCustom) {
      reasonCustom.classList.add('is-invalid');
      if (reasonButton) reasonButton.setAttribute('aria-invalid', 'true');
    }

    let fb = feedbackHost.parentElement ? feedbackHost.parentElement.querySelector('.invalid-feedback') : null;
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      if (feedbackHost.parentElement) feedbackHost.parentElement.appendChild(fb);
    }
    fb.textContent = msg || 'This field is required.';
  }

  function clearInvalid(el) {
    if (!el) return;
    el.classList.remove('is-invalid');
    el.removeAttribute('aria-invalid');

    const feedbackHost = (el === reasonType && reasonCustom) ? reasonCustom : el;
    if (el === reasonType && reasonCustom) {
      reasonCustom.classList.remove('is-invalid');
      if (reasonButton) reasonButton.removeAttribute('aria-invalid');
    }

    const fb = feedbackHost.parentElement ? feedbackHost.parentElement.querySelector('.invalid-feedback') : null;
    if (fb) fb.textContent = '';
  }

  function shakeModal() {
    const content = modalEl.querySelector('.modal-content');
    if (!content) return;
    content.classList.remove('lb-shake');
    // force reflow to restart animation
    void content.offsetWidth;
    content.classList.add('lb-shake');
  }

  function showModalToast(message, type) {
    const host = modalEl.querySelector('.modal-content');
    if (!host) return;

    let wrap = host.querySelector('.lb-modal-toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'lb-modal-toast-wrap';
      host.appendChild(wrap);
    }

    const t = document.createElement('div');
    t.className = 'lb-modal-toast ' + (type ? ('lb-toast-' + type) : 'lb-toast-danger');
    t.innerHTML = '<div class="lb-toast-title">Missing information</div><div class="lb-toast-msg"></div>';
    t.querySelector('.lb-toast-msg').textContent = message;
    wrap.appendChild(t);

    // animate in
    requestAnimationFrame(() => t.classList.add('is-in'));

    // auto dismiss
    window.setTimeout(() => {
      t.classList.remove('is-in');
      t.classList.add('is-out');
      window.setTimeout(() => t.remove(), 220);
    }, 2600);
  }

  function sendLocalToast(message) {
    // Never use browser alerts here (they look bad and break flow inside a modal)
    showInlineAlert(message);
    showModalToast(message, 'danger');
    shakeModal();
  }

  function scrollIntoViewInBody(el) {
    const body = modalEl.querySelector('.modal-body');
    if (!body || !el) return;

    const bodyRect = body.getBoundingClientRect();
    const elRect = el.getBoundingClientRect();

    const current = body.scrollTop;
    const top = (elRect.top - bodyRect.top) + current;
    const bottom = (elRect.bottom - bodyRect.top) + current;

    const viewBottom = current + body.clientHeight - 140; // leave space for sticky footer
    const pad = 16;

    if (bottom + pad > viewBottom) {
      body.scrollTo({ top: Math.max(0, bottom - body.clientHeight + 180), behavior: 'smooth' });
    } else if (top - pad < current) {
      body.scrollTo({ top: Math.max(0, top - 120), behavior: 'smooth' });
    }
  }

  function refreshFooterHeight() {
    const footer = modalEl.querySelector('.modal-footer');
    if (footer) {
      modalEl.style.setProperty('--lb-drop-footer-h', `${(footer.offsetHeight || 0) + 24}px`);
    }
  }


  function setAutoProgressState(state, payload) {
    // Auto progress disabled: always treat as unavailable (admin approval flow)
    payload = payload || {};
    autoProgressLoaded = false;
    autoProgressLoading = false;
    if (progressInput) {
      progressInput.readOnly = false;
      progressInput.required = true;
    }
  }

  async function loadAutoDropProgress() {
    // Auto progress disabled: do nothing, use manual flow with admin approval
  }

  function syncZeroUI() {
    const onZero = !!(noProgressRadio && noProgressRadio.checked);

    clearInlineAlert();
    clearInvalid(dateDisplay);
    clearInvalid(reasonType);
    clearInvalid(opggInput);
    clearInvalid(progressInput);
    clearInvalid(zeroConfirmInput);

    if (zeroHidden) zeroHidden.value = onZero ? '1' : '0';

    if (zeroWarn) zeroWarn.classList.toggle('d-none', !onZero);
    if (zeroConfirmWrap) zeroConfirmWrap.classList.toggle('d-none', !onZero);

    if (autoCard) autoCard.classList.toggle('d-none', onZero);

    // Progress input handling + restore
    if (progressInput) {
      if (onZero) {
        const cur = (progressInput.value || '').trim();
        if (cur && !/^0\b/i.test(cur)) prevProgressValue = cur;

        progressInput.value = '0 Progress';
        progressInput.readOnly = true;
        progressInput.required = false;
      } else {
        // Auto progress disabled: always manual, editable
        progressInput.readOnly = false;
        progressInput.required = true;

        if ((progressInput.value || '').trim().toLowerCase() === '0 progress') {
          progressInput.value = prevProgressValue || '';
        }
        prevProgressValue = '';
      }
    }

    // Hide progress field completely when 0 progress is selected (saves height)
    if (progressWrap) progressWrap.classList.toggle('d-none', onZero);

    // OP.GG always required for manual progress (auto progress disabled)
    const opggRequired = !onZero;
    if (opggInput) {
      opggInput.required = opggRequired;
      opggInput.setAttribute(
        'placeholder',
        'Required when progress was made (paste OP.GG / proof link)'
      );
    }
    if (opggBadge) {
      opggBadge.textContent = opggRequired ? 'Required' : 'Optional';
      opggBadge.classList.toggle('bg-soft-primary', opggRequired);
      opggBadge.classList.toggle('text-primary', opggRequired);
      opggBadge.classList.toggle('bg-soft-secondary', !opggRequired);
      opggBadge.classList.toggle('text-body', !opggRequired);
    }

    if (!onZero && zeroConfirmInput) zeroConfirmInput.value = '';

    refreshFooterHeight();
  }

  function validateStep1() {
    clearInlineAlert();

    const dateBackend = (dateValue ? dateValue.value : '').trim();
    if (!dateBackend) {
      markInvalid(dateDisplay, 'Assignment Date is required.');
      sendLocalToast('Please select the Assignment Date to continue.');
      if (dateDisplay) {
        dateDisplay.focus();
        scrollIntoViewInBody(dateDisplay);
      }
      return false;
    }

    const rt = (reasonType ? reasonType.value : '').trim();
    if (!rt) {
      markInvalid(reasonType, 'Please select a reason.');
      sendLocalToast('Please select a Drop Reason to continue.');
      const target = reasonButton || reasonType;
      if (target) {
        target.focus();
        scrollIntoViewInBody(target);
      }
      return false;
    }

    const onZero = !!(noProgressRadio && noProgressRadio.checked);

    if (!onZero) {
      // Auto progress disabled: always use manual flow with admin approval
      const pr = (progressInput ? progressInput.value : '').trim();
      if (!pr) {
        markInvalid(progressInput, 'Order Progress is required.');
        sendLocalToast('Please enter the Order Progress to continue.');
        if (progressInput) {
          progressInput.focus();
          scrollIntoViewInBody(progressInput);
        }
        return false;
      }

      const op = (opggInput ? opggInput.value : '').trim();
      if (!op) {
        markInvalid(opggInput, 'OP.GG / proof link is required when progress was made.');
        sendLocalToast('Please add an OP.GG / proof link (required when progress was made).');
        if (opggInput) {
          opggInput.focus();
          scrollIntoViewInBody(opggInput);
        }
        return false;
      }
    } else {
      const conf = (zeroConfirmInput ? zeroConfirmInput.value : '').trim().toUpperCase();
      if (conf !== 'ZERO') {
        markInvalid(zeroConfirmInput, 'Type ZERO to confirm.');
        sendLocalToast('To confirm 0 progress, please type ZERO.');
        if (zeroConfirmInput) {
          zeroConfirmInput.focus();
          scrollIntoViewInBody(zeroConfirmInput);
        }
        return false;
      }
    }

    return true;
  }

  function validateStep2() {
    const r = (reasonTextarea ? reasonTextarea.value : '').trim();
    if (!r) {
      markInvalid(reasonTextarea, 'Please explain your reason.');
      if (typeof window.sendToast === 'function') {
        window.sendToast({ type: 'danger', title: 'Missing information', message: 'Please explain your reason before sending.' });
      }
      return false;
    }
    return true;
  }

  function setStep(n) {
    const is1 = (n === 1);

    if (step1) step1.classList.toggle('is-active', is1);
    if (step2) step2.classList.toggle('is-active', !is1);

    if (pill1) pill1.classList.toggle('is-active', is1);
    if (pill2) pill2.classList.toggle('is-active', !is1);

    if (btnBack) btnBack.classList.toggle('d-none', is1);
    if (btnNext) btnNext.classList.toggle('d-none', !is1);
    if (btnSubmit) btnSubmit.classList.toggle('d-none', is1);

    refreshFooterHeight();
  }

  if (btnNext) btnNext.addEventListener('click', function () {
    if (!validateStep1()) return;
    setStep(2);
    setTimeout(() => refreshFooterHeight(), 50);
  });

  if (btnBack) btnBack.addEventListener('click', function () { setStep(1); });

  if (pill1) pill1.addEventListener('click', function () { setStep(1); });

  if (pill2) pill2.addEventListener('click', function () {
    if (!validateStep1()) return;
    setStep(2);
  });

  if (hasProgressRadio) hasProgressRadio.addEventListener('change', syncZeroUI);
  if (noProgressRadio) noProgressRadio.addEventListener('change', syncZeroUI);
  if (autoConfirmCheck) {
    autoConfirmCheck.addEventListener('change', function () {
      if (autoConfirmedHidden) autoConfirmedHidden.value = autoConfirmCheck.checked ? '1' : '0';
      syncZeroUI();
    });
  }

  if (dateDisplay) {
    dateDisplay.addEventListener('click', function () {
      // Some themes disable pointer events on readonly inputs; ensure click triggers picker
      try {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.daterangepicker) {
          window.jQuery(this).data('daterangepicker')?.show();
        }
      } catch (e) {}
    });
  }

  // Clear error states as user types
  if (reasonType) reasonType.addEventListener('change', function(){ clearInvalid(reasonType); clearInlineAlert(); });
  if (opggInput) opggInput.addEventListener('input', function(){ clearInvalid(opggInput); clearInlineAlert(); });
  if (progressInput) progressInput.addEventListener('input', function(){ clearInvalid(progressInput); clearInlineAlert(); });
  if (zeroConfirmInput) zeroConfirmInput.addEventListener('input', function(){ clearInvalid(zeroConfirmInput); clearInlineAlert(); });
  if (reasonTextarea) reasonTextarea.addEventListener('input', function(){ clearInvalid(reasonTextarea); });

  if (formEl) {
    formEl.addEventListener('submit', function (e) {
      if (!validateStep1()) {
        e.preventDefault();
        e.stopImmediatePropagation();
        setStep(1);
        return false;
      }
      if (!validateStep2()) {
        e.preventDefault();
        e.stopImmediatePropagation();
        setStep(2);
        return false;
      }
    });
  }

  modalEl.addEventListener('shown.bs.modal', function () {
    setStep(1);

    if (hasProgressRadio) hasProgressRadio.checked = true;
    if (noProgressRadio) noProgressRadio.checked = false;

    if (zeroHidden) zeroHidden.value = '0';
    if (zeroConfirmInput) zeroConfirmInput.value = '';

    // If date is prefilled (claimed_at), keep hidden value. Otherwise clear.
    if (dateDisplay && dateValue) {
      const disp = (dateDisplay.value || '').trim();
      const hid = (dateValue.value || '').trim();
      if (!disp) dateValue.value = '';
      if (disp && !hid && window.moment) {
        const m = window.moment(disp, 'DD.MM.YYYY HH:mm:ss', true);
        if (m.isValid()) dateValue.value = m.format('YYYY-MM-DD HH:mm:ss');
      }
    }

    setAutoProgressState('unavailable');
    syncZeroUI();
    refreshFooterHeight();
  });

  // Init
  syncZeroUI();
  refreshFooterHeight();
  window.addEventListener('resize', refreshFooterHeight);
});
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('complete_order_md');
    const inp = document.getElementById('file_url');
    const out = document.getElementById('lbCompleteFileName');
    const dropZone = document.getElementById('lbCompleteDropZone');

    const wrap = document.getElementById('lbCompletePreviewWrap');
    const img = document.getElementById('lbCompletePreviewImg');
    const btnRemove = document.getElementById('lbCompleteRemoveBtn');
    const btnText = document.getElementById('lbCompleteChooseBtnText');

    if (!inp || !out) return;

    let objectUrl = null;
    const originalSrc = img ? (img.getAttribute('data-original') || '') : '';

    function setFileName() {
      out.textContent = (inp.files && inp.files[0]) ? inp.files[0].name : 'No file selected';
    }

    function setChooseText() {
      if (!btnText) return;
      btnText.textContent = (inp.files && inp.files[0]) ? 'Change image' : 'Choose image';
    }

    function showPreviewFromFile(file) {
      if (!wrap || !img) return;
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = URL.createObjectURL(file);
      img.src = objectUrl;
      wrap.classList.remove('d-none');
      if (btnRemove) btnRemove.classList.remove('d-none');
    }

    function resetPreview() {
      // Clear selected file
      inp.value = '';
      setFileName();
      setChooseText();

      // Revert preview to original (if any), otherwise hide
      if (wrap && img) {
        if (objectUrl) {
          URL.revokeObjectURL(objectUrl);
          objectUrl = null;
        }
        if (originalSrc) {
          img.src = originalSrc;
          wrap.classList.remove('d-none');
        } else {
          wrap.classList.add('d-none');
        }
      }
      if (typeof updateSubmitForScreenshot === 'function') updateSubmitForScreenshot();
    }

    // Initial state
    setFileName();
    setChooseText();
    if (btnRemove) {
      // If no original proof and no selected file -> hide remove button
      if (!originalSrc) btnRemove.classList.add('d-none');
      btnRemove.addEventListener('click', resetPreview);
    }

    inp.addEventListener('change', function () {
      setFileName();
      setChooseText();
      const file = (inp.files && inp.files[0]) ? inp.files[0] : null;
      if (!file) {
        // No selection -> revert
        resetPreview();
        return;
      }
      if (!file.type || !file.type.startsWith('image/')) {
        // Not an image -> reset
        resetPreview();
        return;
      }
      showPreviewFromFile(file);
      updateSubmitForScreenshot();
    });

    // ---------- Drag & Drop + Paste (Ctrl+V) ----------
    function acceptImageFile(file) {
      if (!file) return;
      if (!file.type || !file.type.startsWith('image/')) return;

      // Programmatically set <input type="file"> using DataTransfer
      const dt = new DataTransfer();
      dt.items.add(file);
      inp.files = dt.files;

      // Trigger existing change handler (updates filename + preview)
      inp.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Drag & Drop
    if (dropZone) {
      const prevent = (e) => { e.preventDefault(); e.stopPropagation(); };

      ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
          prevent(e);
          dropZone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
          prevent(e);
          dropZone.classList.remove('is-dragover');
        });
      });

      dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
        if (!files || !files.length) return;
        acceptImageFile(files[0]);
      });
    }

    // Paste (Ctrl+V) – only while modal is open
    function onPaste(e) {
      const items = e.clipboardData && e.clipboardData.items ? e.clipboardData.items : [];
      for (const it of items) {
        if (it.kind === 'file') {
          const f = it.getAsFile();
          if (f && f.type && f.type.startsWith('image/')) {
            acceptImageFile(f);
            e.preventDefault();
            break;
          }
        }
      }
    }

    // When modal closes, drop the pending selection and restore original preview
    if (modalEl) {
      modalEl.addEventListener('hidden.bs.modal', resetPreview);
      modalEl.addEventListener('shown.bs.modal', () => document.addEventListener('paste', onPaste));
      modalEl.addEventListener('hidden.bs.modal', () => document.removeEventListener('paste', onPaste));

      // Reset result banner when modal re-opens
      modalEl.addEventListener('show.bs.modal', function () {
        const banner = document.getElementById('lbCompleteResultBanner');
        if (banner) { banner.className = 'd-none mt-3 p-3 rounded-3'; banner.innerHTML = ''; }
      });
    }

    // Screenshot is mandatory before completing every order.
    const submitBtn = document.getElementById('lbCompleteSubmitBtn');
    const screenshotCard = document.getElementById('lbCompleteScreenshotCard');
    const screenshotHint = document.getElementById('lbCompleteScreenshotHint');
    const requiredBadge = document.getElementById('lbCompleteRequiredBadge');
    const isTracking = <?= $canTrack ? 'true' : 'false' ?>;
    const requiresCompletionScreenshot = <?= $requiresCompletionScreenshot ? 'true' : 'false' ?>;
    let screenshotNowRequired = requiresCompletionScreenshot;

    function updateSubmitForScreenshot() {
      if (!submitBtn) return;
      submitBtn.disabled = screenshotNowRequired && !(inp.files && inp.files[0]) && !originalSrc;
    }

    function requireScreenshotNow(message) {
      screenshotNowRequired = true;
      if (screenshotCard) screenshotCard.classList.remove('d-none');
      if (requiredBadge) requiredBadge.textContent = 'Required';
      if (screenshotHint) screenshotHint.textContent = message || 'Please upload a clear screenshot showing the final result before submitting.';
      updateSubmitForScreenshot();
    }

    updateSubmitForScreenshot();
    inp.addEventListener('change', updateSubmitForScreenshot);

    // ── Handle tracking-specific server responses ──
    // The ajax-form plugin fires a custom event or we intercept via the global
    // sendToast / trackingResult fields coming back from the server.
    // We hook into the ajax-form success callback by watching for our custom
    // trackingResult field in the JSON response.
    if (isTracking && modalEl) {
      const form = modalEl.querySelector('form.ajax-form');
      if (form) {
        form.addEventListener('ajax:success', function (e) {
          const resp = e && e.detail ? e.detail : null;
          if (!resp) return;
          if (resp.trackingResult === 'screenshot_required') {
            requireScreenshotNow(resp.message || 'Automatic Riot tracking could not verify this order. Please upload a screenshot for admin review.');
            const banner = document.getElementById('lbCompleteResultBanner');
            if (banner) {
              banner.className = 'mt-3 p-3 rounded-3';
              banner.style.cssText = 'background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.25);color:rgba(255,255,255,.85);font-size:.83rem;';
              banner.innerHTML = '<div class="d-flex gap-2 align-items-start"><i class="fa-duotone fa-image mt-1" style="color:#fbbf24;flex-shrink:0;"></i>'
                + '<div><strong style="color:#fbbf24;">Screenshot required</strong><br>'
                + '<span style="color:rgba(255,255,255,.6);">Automatic Riot tracking did not complete this order. Upload proof to send it to admin review.</span></div></div>';
            }
            return;
          }
          if (resp.trackingResult === 'goal_not_reached') {
            // Show inline banner
            const banner = document.getElementById('lbCompleteResultBanner');
            if (banner) {
              banner.className = 'mt-3 p-3 rounded-3';
              banner.style.cssText = 'background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.25);color:rgba(255,255,255,.85);font-size:.83rem;';
              banner.innerHTML = '<div class="d-flex gap-2 align-items-start"><i class="fa-duotone fa-circle-exclamation mt-1" style="color:#fb7185;flex-shrink:0;"></i>'
                + '<div><strong style="color:#fb7185;">Goal not reached yet</strong><br>'
                + '<span style="color:rgba(255,255,255,.55);">Riot tracking could not verify your goal. The completion was sent to admin review.</span></div></div>';
            }
            if (resp.requireScreenshot) {
              requireScreenshotNow(resp.message || 'Please upload a screenshot for admin review.');
            }
          }
        });
      }
    }
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('lbBonusReminder');
    if (!el) return;

    const orderId = el.getAttribute('data-order');
    const kDone = 'lb_bonus_done_' + orderId;

    if (localStorage.getItem(kDone) === '1') {
      el.remove();
      return;
    }

    el.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-lb-bonus]');
      if (!btn) return;

      const action = btn.getAttribute('data-lb-bonus');
      if (action === 'done') {
        localStorage.setItem(kDone, '1');
      }
      el.remove();
    });
  });
</script>

<script>
  document.addEventListener('focusin', function (e) {
    const modal = e.target.closest('#request_drop_md');
    if (!modal) return;

    const body = modal.querySelector('.modal-body');
    if (!body || !body.contains(e.target)) return;

    const top = e.target.getBoundingClientRect().top - body.getBoundingClientRect().top + body.scrollTop;
    body.scrollTo({ top: Math.max(0, top - 80), behavior: 'smooth' });
  });
</script>

<script>
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.lb-secret');
    if (!btn) return;

    const textEl = btn.querySelector('.lb-secret__text');
    const eyeEl = btn.querySelector('.lb-secret__eye i');

    const masked = btn.getAttribute('data-masked') || '';
    const secret = btn.getAttribute('data-secret') || '';

    const isHidden = (btn.getAttribute('data-state') !== 'shown');

    if (isHidden) {
      if (textEl) textEl.textContent = secret;
      btn.setAttribute('data-state', 'shown');
      if (eyeEl) eyeEl.className = 'fa-duotone fa-eye-slash';
    } else {
      if (textEl) textEl.textContent = masked;
      btn.setAttribute('data-state', 'hidden');
      if (eyeEl) eyeEl.className = 'fa-duotone fa-eye';
    }

    // optional auto-hide nach 12s
    clearTimeout(btn._hideTimer);
    if (isHidden) {
      btn._hideTimer = setTimeout(() => {
        btn.setAttribute('data-state', 'hidden');
        if (textEl) textEl.textContent = masked;
        if (eyeEl) eyeEl.className = 'fa-duotone fa-eye';
      }, 12000);
    }
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('lbAccountGrid');
    if (!grid) return;

    const orderId = grid.getAttribute('data-order-id');
    const game = (grid.getAttribute('data-game') || '').toLowerCase();
    const formId = parseInt(grid.getAttribute('data-form-id') || '0', 10);
    const isDuo = grid.getAttribute('data-is-duo') === '1';
    const riotOnly = grid.getAttribute('data-riot-only') === '1';
    const isCoachingForm = [15, 16, 25].includes(formId);
    const isLolForm = [1, 2, 3, 4, 9, 17, 18, 19, 20, 26].includes(formId);
    const isValForm = [5, 6, 7, 8].includes(formId);
    const isTftForm = [21, 22, 23, 24].includes(formId);
    let usernameLabel = 'Account Username';
    let passwordLabel = 'Account Password';

    if (isValForm) {
      usernameLabel = 'VAL Username';
      passwordLabel = 'VAL Password';
    } else if (isTftForm) {
      usernameLabel = 'TFT Username';
      passwordLabel = 'TFT Password';
    }
    const alertEl = document.getElementById('lbAccountMissingAlert');
    const missingEl = document.getElementById('lbMissingFields');

    function pick(obj, keys) {
      for (const k of keys) {
        const v = obj?.[k];
        if (v === undefined || v === null) continue;
        const s = String(v).trim();
        if (!s || s === 'null' || s === 'undefined') continue;
        return s;
      }
      return '';
    }

    function maskGeneric(val) {
      if (!val) return '';
      const s = String(val);
      if (s.length <= 4) return '••••';
      return s.slice(0, 2) + '•••••' + s.slice(-2);
    }

    function maskPassword(val) {
      if (!val) return '';
      return '•'.repeat(Math.max(8, String(val).length));
    }

    function setItemMissing(li, missing) {
      li.classList.toggle('lb-ov-item--missing', !!missing);
      const value = li.querySelector('.lb-ov-value');
      if (!value) return;

      if (missing) {
        value.innerHTML = `
        <span class="lb-missing-pill">
          <i class="fa-duotone fa-triangle-exclamation"></i>
          Customer didn’t provide yet
        </span>
      `;
      }
    }

    function ensureSecretField(li, secret, masked) {
      const value = li.querySelector('.lb-ov-value');
      if (!value) return;

      let field = value.querySelector('.lb-secret-field');
      if (!field) {
        value.innerHTML = `
        <div class="lb-secret-field" data-secret="" data-masked="">
          <span class="lb-secret-text"></span>
          <button type="button" class="lb-secret-btn lb-secret-eye" aria-label="Reveal">
            <i class="fa-duotone fa-eye"></i>
          </button>
          <button type="button" class="lb-secret-btn lb-secret-copy" aria-label="Copy">
            <i class="fa-duotone fa-copy"></i>
          </button>
        </div>
      `;
        field = value.querySelector('.lb-secret-field');
      }

      field.dataset.secret = secret || '';
      field.dataset.masked = masked || '';

      const text = field.querySelector('.lb-secret-text');
      if (text) {
        const revealed = field.classList.contains('is-revealed');
        text.textContent = revealed ? (secret || '') : (masked || '');
      }
    }

    function updateMissingAlert(missingArr) {
      if (!alertEl) return;
      if (missingArr.length) {
        alertEl.classList.remove('d-none');
        if (missingEl) missingEl.textContent = missingArr.join(', ');
      } else {
        alertEl.classList.add('d-none');
      }
    }

    function updateAccountUI(payload) {
      const src = payload?.data || payload?.account || payload || {};

      const ign = pick(src, ['ign', 'riot_id', 'riotId', 'riot']);
      const login = pick(src, ['login','val_username','valorant_username','username','lol_username','lol_login','lol_user','lolUser','lolUsername']);
      const password = pick(src, ['password','val_password','valorant_password','pass','pw','lol_password','lol_pass','lolPassword']);

      const liIgn = grid.querySelector('li[data-field="ign"]');
      const liLog = grid.querySelector('li[data-field="login"]');
      const liPass = grid.querySelector('li[data-field="password"]');

      const missing = [];
      if (!riotOnly && !isCoachingForm && !isDuo) {
        if (!login) missing.push(usernameLabel);
        if (!password) missing.push(passwordLabel);
      }

      if (liIgn) {
        if (!ign) { setItemMissing(liIgn, true); }
        else { setItemMissing(liIgn, false); ensureSecretField(liIgn, ign, maskGeneric(ign)); }
      }

      if (liLog) {
        if (!login) { setItemMissing(liLog, true); }
        else { setItemMissing(liLog, false); ensureSecretField(liLog, login, maskGeneric(login)); }
      }

      if (liPass) {
        if (!password) { setItemMissing(liPass, true); }
        else { setItemMissing(liPass, false); ensureSecretField(liPass, password, maskPassword(password)); }
      }

      updateMissingAlert(missing);
      return missing.length === 0;
    }

    // Eye + Copy (delegation)
    document.addEventListener('click', async function (e) {
      const eye = e.target.closest('.lb-secret-eye');
      const copy = e.target.closest('.lb-secret-copy');

      if (eye) {
        const field = eye.closest('.lb-secret-field');
        if (!field) return;

        field.classList.toggle('is-revealed');
        const text = field.querySelector('.lb-secret-text');
        if (!text) return;

        const revealed = field.classList.contains('is-revealed');
        text.textContent = revealed ? field.dataset.secret : field.dataset.masked;

        clearTimeout(field._hideTimer);
        if (revealed) {
          field._hideTimer = setTimeout(() => {
            field.classList.remove('is-revealed');
            text.textContent = field.dataset.masked;
          }, 12000);
        }
        return;
      }

      if (copy) {
        const field = copy.closest('.lb-secret-field');
        if (!field) return;
        const secret = field.dataset.secret || '';
        if (!secret) return;

        try { await navigator.clipboard.writeText(secret); }
        catch (err) {
          const ta = document.createElement('textarea');
          ta.value = secret;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          ta.remove();
        }
      }
    });

    const boosterRiotModal = document.getElementById('boosterRiotIdModal');
    const boosterRiotInput = document.getElementById('booster_riot_id');
    const boosterRiotError = document.getElementById('booster_riot_error');
    const boosterRiotPreview = document.getElementById('booster_riot_preview');
    const boosterRiotIcon = document.getElementById('booster_riot_icon');
    const boosterRiotIconFallback = document.getElementById('booster_riot_icon_fallback');
    const boosterRiotName = document.getElementById('booster_riot_name');
    const boosterRiotMeta = document.getElementById('booster_riot_meta');
    const boosterRiotPreviewLabel = document.getElementById('booster_riot_preview_label');
    const boosterRiotConfirmBtn = document.getElementById('booster_riot_confirm');
    const boosterRiotSaveBtn = document.getElementById('booster_riot_save');

    function openBoosterRiotModal() {
      if (!boosterRiotModal) return;
      try {
        if (window.bootstrap && bootstrap.Modal) {
          bootstrap.Modal.getOrCreateInstance(boosterRiotModal, { backdrop: true, keyboard: true, focus: true }).show();
          return;
        }
      } catch (err) {
        // Fallback below. Some pages already initialize Bootstrap modals globally and can throw here.
      }
      if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        try { window.jQuery(boosterRiotModal).modal('show'); return; } catch (err) {}
      }
      boosterRiotModal.hidden = false;
      boosterRiotModal.removeAttribute('aria-hidden');
      boosterRiotModal.setAttribute('aria-modal', 'true');
      boosterRiotModal.style.display = 'block';
      boosterRiotModal.classList.add('show');
      document.body.classList.add('modal-open');
      if (!document.querySelector('.modal-backdrop.lb-booster-riot-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show lb-booster-riot-backdrop';
        document.body.appendChild(backdrop);
      }
      setTimeout(function(){ if (boosterRiotInput) { boosterRiotInput.focus(); boosterRiotInput.select(); } setBoosterRiotPreview('idle'); scheduleBoosterRiotPreview(); }, 30);
    }
    function closeBoosterRiotModal() {
      if (!boosterRiotModal) return;
      try {
        if (window.bootstrap && bootstrap.Modal) {
          const instance = bootstrap.Modal.getInstance(boosterRiotModal);
          if (instance) { instance.hide(); return; }
        }
      } catch (err) {}
      if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        try { window.jQuery(boosterRiotModal).modal('hide'); return; } catch (err) {}
      }
      boosterRiotModal.classList.remove('show');
      boosterRiotModal.style.display = 'none';
      boosterRiotModal.setAttribute('aria-hidden', 'true');
      boosterRiotModal.removeAttribute('aria-modal');
      document.body.classList.remove('modal-open');
      document.querySelectorAll('.modal-backdrop.lb-booster-riot-backdrop').forEach(function(el){ el.remove(); });
      setBoosterRiotError('');
      setBoosterRiotPreview('idle');
    }
    document.addEventListener('click', function(ev) {
      const trigger = ev.target.closest('.js-booster-riot-open');
      if (!trigger) return;
      if (!boosterRiotModal) return;
      ev.preventDefault();
      ev.stopPropagation();
      openBoosterRiotModal();
    });
    if (boosterRiotModal) {
      boosterRiotModal.addEventListener('click', function(ev){
        if (ev.target.closest('[data-bs-dismiss="modal"], .btn-close')) {
          ev.preventDefault();
          closeBoosterRiotModal();
        }
      });
    }

    let boosterRiotTimer = null, boosterRiotController = null, boosterRiotInFlight = null, boosterRiotLastValue = '';
    let boosterRiotVerifiedOk = false, boosterRiotVerifiedValue = '', boosterRiotConfirmedValue = '';

    function normalizeRiotId(value) { const parts = (value || '').split('#'); if (parts.length < 2) return (value || '').trim(); return parts[0].trim() + '#' + parts.slice(1).join('#'); }
    function isValidRiotId(value) { return /^[^#]{2,32}#.{2,16}$/.test(normalizeRiotId(value)); }
    function setBoosterRiotError(message) {
      if (!boosterRiotError || !boosterRiotInput) return;
      boosterRiotError.textContent = message || '';
      boosterRiotInput.classList.toggle('is-invalid', !!message);
    }
    function setBoosterRiotSaveLocked(locked) {
      if (!boosterRiotSaveBtn) return;
      boosterRiotSaveBtn.disabled = !!locked;
      boosterRiotSaveBtn.classList.toggle('is-disabled', !!locked);
    }
    function resetBoosterRiotConfirmation() {
      boosterRiotConfirmedValue = '';
      if (boosterRiotConfirmBtn) {
        boosterRiotConfirmBtn.hidden = true;
        boosterRiotConfirmBtn.disabled = false;
        boosterRiotConfirmBtn.classList.remove('is-confirmed');
        boosterRiotConfirmBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> It\'s the client account';
      }
      setBoosterRiotSaveLocked(true);
    }
    function markBoosterRiotConfirmed(riotId) {
      boosterRiotConfirmedValue = normalizeRiotId(riotId || boosterRiotInput?.value || '');
      if (boosterRiotConfirmBtn) {
        boosterRiotConfirmBtn.hidden = false;
        boosterRiotConfirmBtn.disabled = true;
        boosterRiotConfirmBtn.classList.add('is-confirmed');
        boosterRiotConfirmBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Confirmed';
      }
      setBoosterRiotError('');
      setBoosterRiotSaveLocked(false);
    }
    function setBoosterRiotPreview(state, data) {
      if (!boosterRiotPreview) return;
      if (state !== 'found') { boosterRiotVerifiedOk = false; boosterRiotVerifiedValue = ''; resetBoosterRiotConfirmation(); }
      boosterRiotPreview.hidden = false;
      boosterRiotPreview.classList.remove('is-loading','is-found','is-error','is-idle');
      boosterRiotPreview.classList.add('is-' + state);
      if (state === 'idle') {
        boosterRiotPreviewLabel.textContent = 'Riot account preview';
        boosterRiotName.textContent = 'Enter Riot ID to verify account';
        boosterRiotMeta.textContent = 'Example: Faker#1234';
        if (boosterRiotIcon) { boosterRiotIcon.removeAttribute('src'); boosterRiotIcon.style.display = 'none'; }
        if (boosterRiotIconFallback) boosterRiotIconFallback.style.display = 'grid';
        return;
      }
      if (state === 'loading') {
        boosterRiotPreviewLabel.textContent = 'Checking Riot account…';
        boosterRiotName.textContent = data?.riot_id || 'Looking up account';
        boosterRiotMeta.textContent = 'Please wait a moment.';
        if (boosterRiotIcon) { boosterRiotIcon.removeAttribute('src'); boosterRiotIcon.style.display = 'none'; }
        if (boosterRiotIconFallback) boosterRiotIconFallback.style.display = 'grid';
        return;
      }
      if (state === 'found') {
        boosterRiotVerifiedOk = true;
        boosterRiotVerifiedValue = normalizeRiotId(data?.riot_id || boosterRiotInput?.value || '');
        boosterRiotConfirmedValue = '';
        setBoosterRiotSaveLocked(true);
        setBoosterRiotError('');
        boosterRiotPreviewLabel.textContent = 'Account found';
        boosterRiotName.textContent = data?.riot_id || 'Riot account found';
        boosterRiotMeta.textContent = data?.summoner_level ? ('Level ' + data.summoner_level + ' · ' + (data.server || '').toUpperCase()) : ((data?.server || '').toUpperCase() + ' account');
        if (boosterRiotIcon && data?.profile_icon_url) { boosterRiotIcon.src = data.profile_icon_url; boosterRiotIcon.style.display = 'block'; if (boosterRiotIconFallback) boosterRiotIconFallback.style.display = 'none'; }
        if (boosterRiotConfirmBtn) { boosterRiotConfirmBtn.hidden = false; boosterRiotConfirmBtn.disabled = false; boosterRiotConfirmBtn.classList.remove('is-confirmed'); boosterRiotConfirmBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> It\'s the client account'; }
        return;
      }
      boosterRiotPreviewLabel.textContent = 'Riot ID not found';
      boosterRiotName.textContent = data?.riot_id || 'Please check the Riot ID';
      boosterRiotMeta.textContent = data?.message || 'Riot ID not found';
      setBoosterRiotError('Riot ID not found');
      if (boosterRiotIcon) { boosterRiotIcon.removeAttribute('src'); boosterRiotIcon.style.display = 'none'; }
      if (boosterRiotIconFallback) boosterRiotIconFallback.style.display = 'grid';
    }
    function verifyBoosterRiotId(riotId) {
      if (!boosterRiotInput || !boosterRiotPreview) return Promise.resolve(false);
      riotId = normalizeRiotId(riotId || boosterRiotInput.value || '');
      if (!isValidRiotId(riotId)) { setBoosterRiotError('Please enter the Riot ID in this format: Faker#1234'); setBoosterRiotPreview('error', { riot_id: riotId, message: 'Please enter the full Riot ID with #tag, e.g. Faker#1234.' }); return Promise.resolve(false); }
      if (boosterRiotVerifiedOk && boosterRiotVerifiedValue === riotId) return Promise.resolve(true);
      if (riotId === boosterRiotLastValue && boosterRiotInFlight) return boosterRiotInFlight;
      boosterRiotLastValue = riotId;
      if (boosterRiotController) boosterRiotController.abort();
      boosterRiotController = window.AbortController ? new AbortController() : null;
      setBoosterRiotError(''); setBoosterRiotPreview('loading', { riot_id: riotId });
      const fd = new FormData(); fd.append('action', 'booster_preview_riot_account'); fd.append('order_id', orderId); fd.append('riot_id', riotId);
      boosterRiotInFlight = fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin', signal: boosterRiotController ? boosterRiotController.signal : undefined })
        .then(function(res){ return res.json(); })
        .then(function(json){ if (json && json.ok) { setBoosterRiotPreview('found', json.account || { riot_id: riotId }); return true; } setBoosterRiotPreview('error', { riot_id: riotId, message: 'Riot ID not found' }); return false; })
        .catch(function(e){ if (e && e.name === 'AbortError') return false; setBoosterRiotPreview('error', { riot_id: riotId, message: 'Riot ID not found' }); return false; })
        .finally(function(){ boosterRiotInFlight = null; });
      return boosterRiotInFlight;
    }
    function scheduleBoosterRiotPreview() {
      if (!boosterRiotInput || !boosterRiotPreview) return;
      const riotId = normalizeRiotId(boosterRiotInput.value);
      clearTimeout(boosterRiotTimer);
      if (!riotId) { boosterRiotLastValue = ''; setBoosterRiotError(''); setBoosterRiotPreview('idle'); return; }
      if (!isValidRiotId(riotId)) { boosterRiotLastValue = ''; setBoosterRiotError('Please enter the Riot ID in this format: Faker#1234'); setBoosterRiotPreview('error', { riot_id: riotId, message: 'Please enter the full Riot ID with #tag, e.g. Faker#1234.' }); return; }

      // Do not re-run the Riot preview just because the input blurs when the
      // booster clicks the confirmation/save buttons. If the currently typed
      // Riot ID is already verified, keep the preview and confirmation state.
      if (boosterRiotVerifiedOk && boosterRiotVerifiedValue === riotId) {
        setBoosterRiotError('');
        if (boosterRiotConfirmedValue === riotId) setBoosterRiotSaveLocked(false);
        return;
      }

      boosterRiotVerifiedOk = false; boosterRiotVerifiedValue = ''; resetBoosterRiotConfirmation();
      boosterRiotTimer = setTimeout(function(){ verifyBoosterRiotId(riotId); }, 450);
    }
    if (boosterRiotModal) {
      boosterRiotModal.addEventListener('shown.bs.modal', function(){ if (boosterRiotInput) { boosterRiotInput.focus(); boosterRiotInput.select(); } setBoosterRiotPreview('idle'); scheduleBoosterRiotPreview(); });
      boosterRiotModal.addEventListener('hidden.bs.modal', function(){ setBoosterRiotError(''); setBoosterRiotPreview('idle'); });
    }
    if (boosterRiotInput) {
      boosterRiotInput.addEventListener('input', function(){ scheduleBoosterRiotPreview(); });
      boosterRiotInput.addEventListener('blur', function(){ boosterRiotInput.value = normalizeRiotId(boosterRiotInput.value); scheduleBoosterRiotPreview(); });
    }
    if (boosterRiotConfirmBtn) {
      boosterRiotConfirmBtn.addEventListener('mousedown', function(e){
        e.preventDefault();
      });
      boosterRiotConfirmBtn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        const riotId = normalizeRiotId(boosterRiotInput ? boosterRiotInput.value : '');
        if (!boosterRiotVerifiedOk || boosterRiotVerifiedValue !== riotId) { setBoosterRiotError('Please wait until the Riot account was found.'); return; }
        markBoosterRiotConfirmed(riotId);
      });
    }
    if (boosterRiotSaveBtn) {
      boosterRiotSaveBtn.addEventListener('mousedown', function(e){
        e.preventDefault();
      });
      boosterRiotSaveBtn.addEventListener('click', async function(e){
        e.preventDefault();
        const riotId = normalizeRiotId(boosterRiotInput ? boosterRiotInput.value : '');
        if (!isValidRiotId(riotId)) { setBoosterRiotError('Please enter the Riot ID in this format: Faker#1234'); if (boosterRiotInput) boosterRiotInput.focus(); return; }
        boosterRiotSaveBtn.disabled = true; boosterRiotSaveBtn.classList.add('is-loading');
        const verified = await verifyBoosterRiotId(riotId);
        if (!verified || !boosterRiotVerifiedOk || boosterRiotVerifiedValue !== riotId) { boosterRiotSaveBtn.classList.remove('is-loading'); setBoosterRiotSaveLocked(true); setBoosterRiotError('Riot ID not found'); setBoosterRiotPreview('error', { riot_id: riotId, message: 'Riot ID not found' }); if (boosterRiotInput) boosterRiotInput.focus(); return; }
        if (boosterRiotConfirmedValue !== riotId) { boosterRiotSaveBtn.classList.remove('is-loading'); setBoosterRiotSaveLocked(true); setBoosterRiotError('Please confirm the account before saving.'); if (boosterRiotConfirmBtn) boosterRiotConfirmBtn.focus(); return; }
        const fd = new FormData(); fd.append('action', 'booster_update_order_riot_id'); fd.append('order_id', orderId); fd.append('riot_id', riotId);
        fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function(res){ return res.json(); })
          .then(function(json){
            if (!json || !json.success) throw new Error((json && json.message) || 'Riot ID not found');
            fetchAccount();
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            document.querySelectorAll('.js-booster-riot-open').forEach(function(btn){ btn.innerHTML = '<i class="fa-duotone fa-user-pen"></i> Change Riot ID'; });
            if (typeof create_toast === 'function') create_toast('success', 'Saved', 'Riot ID saved successfully.');
            closeBoosterRiotModal();
          })
          .catch(function(err){ const msg = err && err.message ? err.message : 'Riot ID not found'; setBoosterRiotError(msg); if (typeof create_toast === 'function') create_toast('danger', 'Error', msg); })
          .finally(function(){ boosterRiotSaveBtn.classList.remove('is-loading'); if (boosterRiotConfirmedValue === riotId) setBoosterRiotSaveLocked(false); });
      });
    }

    let pollTimer = null;
    let hadMissingAtStart = false;
    let didAutoRefresh = false;


    function fetchAccount() {
      $.ajax({
        url: '<?= AJAX_URL ?>',
        method: 'POST',
        cache: false,
        data: { action: 'booster_order_account_get', order_id: orderId },
        success: function (resp) {
          try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch (e) { console.warn('bad json', resp); return; }

          console.log('ACCOUNT_GET resp:', resp);

          const ok = (resp?.success === true || resp?.success === 1 || resp?.success === '1' || resp?.status === 'success');

          // Einige Endpoints geben die Felder direkt auf Root zurück (ohne {success,data}).
          const hasFieldsAtRoot = !!resp && (
            resp.ign || resp.login || resp.password ||
            resp.riot_id || resp.riotId || resp.riot
          );

          if (!ok && !resp?.data && !resp?.account && !hasFieldsAtRoot) return;

          const allPresent = updateAccountUI(resp);
          // Fallback: wenn vorher etwas gefehlt hat und jetzt alles da ist,
          // refresh die Seite einmal, damit serverseitige Bereiche 100% synchron sind.
          if (allPresent && hadMissingAtStart && !didAutoRefresh) {
            didAutoRefresh = true;
            setTimeout(function(){ location.reload(); }, 300);
            return;
          }


          // ✅ wenn alles da ist: Polling stoppen
          if (allPresent && pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
          }
        }
      });
    }

    let accountRealtimeRefreshPending = false;
    window.lbOrderViewAccountUpdate = function(data){
      if (data && parseInt(data.order_id || 0, 10) !== parseInt(orderId, 10)) return;
      if (accountRealtimeRefreshPending) return;
      accountRealtimeRefreshPending = true;
      fetchAccount();
      setTimeout(function(){ accountRealtimeRefreshPending = false; }, 1000);
    };

    // Keep the order detail subscribed independently from the global dashboard
    // socket. This guarantees account updates even if the layout socket was
    // initialized before this page registered its handlers.
    (function bindOrderAccountRealtime(){
      if (window.__lbOrderAccountRealtimeBound) return;
      if (typeof io === 'undefined') {
        setTimeout(bindOrderAccountRealtime, 250);
        return;
      }
      window.__lbOrderAccountRealtimeBound = true;
      const orderSocket = io('https://socket.lolboost.gg', {
        transports: ['websocket'],
        withCredentials: true,
        reconnection: true,
        reconnectionAttempts: Infinity,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 10000,
        timeout: 20000
      });
      window.lbOrderSocket = orderSocket;
      orderSocket.on('connect', function(){
        orderSocket.emit('join', 'boosters');
        orderSocket.emit('booster:join', { area: 'booster-order', order_id: parseInt(orderId, 10) || 0 });
      });
      orderSocket.on('order_account_update', function(raw){
        const payload = raw && raw.data && typeof raw.data === 'object'
          ? Object.assign({}, raw, raw.data)
          : (raw || {});
        if (parseInt(payload.order_id || 0, 10) === parseInt(orderId, 10)) {
          window.lbOrderViewAccountUpdate(payload);
        }
      });
    })();
  });
</script>

<script>
  (function () {
    var asset_url = '<?= ASSET_URL ?>';
    var refreshProgressBtn = document.getElementById('refreshProgressBtn');
    var progressStartImgEl = document.getElementById('riotProgressStartRankImg');
    var progressCurrentImgEl = document.getElementById('riotProgressCurrentRankImg');
    var progressStartEl = document.getElementById('riotProgressStartRank');
    var progressCurrentEl = document.getElementById('riotProgressCurrentRank');
    var progressWinsEl = document.getElementById('riotProgressWins');
    var progressLossesEl = document.getElementById('riotProgressLosses');
    var progressRecordEl = document.getElementById('riotProgressRecord');
    var progressLastMatchEl = document.getElementById('riotProgressLastMatch');
    var progressLastSyncEl = document.getElementById('riotProgressLastSync');
    var progressWrBarEl = document.getElementById('riotProgressWrBar');
    var progressSyncStateEl = document.getElementById('riotProgressSyncState');
    var hasRiotId = <?= empty($lb_op_ign) ? 'false' : 'true' ?>;
    var boosterOrderStatus = <?= json_encode($data['status'] ?? '') ?>;
    var isRefreshingProgress = false;
    var lastProgressRefresh = 0;
    var PROGRESS_REFRESH_COOLDOWN = 30000; // ms
    <?php
      $lb_op_is_win_boost_form = $lb_op_is_win_boost_form ?? false;
      $lb_op_is_placements_form = $lb_op_is_placements_form ?? false;
      $lb_op_is_pro_games_form = $lb_op_is_pro_games_form ?? false;
      $lb_op_is_duo_pass_form = $lb_op_is_duo_pass_form ?? false;
      $lb_op_base_target = $lb_op_base_target ?? (int)($data['matches'] ?? 0);
      $lb_op_is_duo_order = $lb_op_is_duo_order ?? false;
    ?>
    var opIsWinBoostForm = <?= $lb_op_is_win_boost_form ? 'true' : 'false' ?>;
    var opIsPlacementsForm = <?= $lb_op_is_placements_form ? 'true' : 'false' ?>;
    var opIsProGamesForm = <?= $lb_op_is_pro_games_form ? 'true' : 'false' ?>;
    var opIsDuoPassForm = <?= $lb_op_is_duo_pass_form ? 'true' : 'false' ?>;
    var opBaseTarget = <?= (int) $lb_op_base_target ?>;
    var opIsClassicRank = <?= ($lb_op_is_classic_rank ?? false) ? 'true' : 'false' ?>;
    var classicRankNames = ['Unranked','Salt','Wood','Silver','Gold','Platinum','Diamond','Legend'];

    var rankTierIds = { IRON: 1, BRONZE: 2, SILVER: 3, GOLD: 4, PLATINUM: 5, EMERALD: 6, DIAMOND: 7, MASTER: 8, GRANDMASTER: 9, CHALLENGER: 10 };

    function tierToImgUrl(tier) {
      if (opIsClassicRank) {
        var classicId = Math.max(0, Math.min(7, parseInt(tier || 0, 10) || 0));
        return '/public/assets/website/images/lol-classic/ranks/' + classicRankNames[classicId].toLowerCase() + '.webp';
      }
      var id = rankTierIds[(tier || '').toString().toUpperCase().trim()] || 0;
      return asset_url + '/core/main/img/lol/ranks/max/' + id + '.png';
    }

    function formatRankValue(tier, division, lp) {
      if (opIsClassicRank) {
        var classicId = Math.max(0, Math.min(7, parseInt(tier || 0, 10) || 0));
        return classicRankNames[classicId];
      }
      var cleanTier = (tier || '').toString().trim();
      var cleanDiv = (division || '').toString().trim();
      var cleanLp = (lp != null ? lp : '').toString().trim();
      if (!cleanTier) return 'Unranked';
      var v = cleanTier.charAt(0).toUpperCase() + cleanTier.slice(1).toLowerCase();
      if (cleanDiv) v += ' ' + cleanDiv;
      if (cleanLp !== '') v += ' · ' + cleanLp + ' LP';
      return v;
    }

    function formatSyncDate(value) {
      var raw = (value || '').toString().trim();
      if (!raw) return 'Never';
      var d = new Date(raw.replace(' ', 'T'));
      if (isNaN(d.getTime())) return raw;
      return d.toLocaleString();
    }

    function formatRecordValue(wins, losses) {
      var w = parseInt(wins || 0, 10) || 0;
      var l = parseInt(losses || 0, 10) || 0;
      var g = w + l;
      if (g <= 0) return '–';
      return ((w / g) * 100).toFixed(1) + '%';
    }

    function applyRecordTone(wins, losses) {
      if (!progressRecordEl) return;
      var w = parseInt(wins || 0, 10) || 0;
      var l = parseInt(losses || 0, 10) || 0;
      var g = w + l;
      progressRecordEl.classList.remove('text-success', 'text-muted');
      if (g <= 0) { progressRecordEl.classList.add('text-muted'); return; }
      if ((w / g) >= 0.6) progressRecordEl.classList.add('text-success');
    }

    function setProgressSyncState(message, type, loading) {
      if (!progressSyncStateEl) return;
      progressSyncStateEl.classList.remove('text-muted', 'text-danger', 'text-success');
      var tone = type || 'muted';
      progressSyncStateEl.classList.add(tone === 'danger' ? 'text-danger' : (tone === 'success' ? 'text-success' : 'text-muted'));
      if (!message) { progressSyncStateEl.textContent = ''; return; }
      progressSyncStateEl.innerHTML = loading
        ? '<i class="fa-duotone fa-loader fa-spin me-2"></i>' + message
        : message;
    }

    function applyProgressData(progress) {
      if (!progress || typeof progress !== 'object') return;
      if (progressStartEl) progressStartEl.textContent = formatRankValue(progress.start_tier, progress.start_division, progress.start_lp);
      if (progressCurrentEl) progressCurrentEl.textContent = formatRankValue(progress.current_tier, progress.current_division, progress.current_lp);
      if (progressCurrentImgEl && progress.current_tier) progressCurrentImgEl.src = tierToImgUrl(progress.current_tier);
      if (progressWinsEl) progressWinsEl.textContent = String(parseInt(progress.wins || 0, 10) || 0);
      if (progressLossesEl) progressLossesEl.textContent = String(parseInt(progress.losses || 0, 10) || 0);
      if (progressRecordEl) progressRecordEl.textContent = formatRecordValue(progress.wins, progress.losses);
      applyRecordTone(progress.wins, progress.losses);
      if (progressWrBarEl) {
        var w = parseInt(progress.wins || 0, 10) || 0;
        var l = parseInt(progress.losses || 0, 10) || 0;
        var g = w + l;
        var pct = g > 0 ? (w / g) * 100 : 0;
        progressWrBarEl.style.width = pct.toFixed(1) + '%';
        progressWrBarEl.classList.remove('lb-op-wr-bar-fill--good');
        if (g > 0 && pct >= 60) progressWrBarEl.classList.add('lb-op-wr-bar-fill--good');
      }
      if (progressLastMatchEl) progressLastMatchEl.textContent = (progress.last_match_id || '').toString().trim();
      if (progressLastSyncEl) progressLastSyncEl.textContent = formatSyncDate(progress.last_sync_at);
      var playedEl = document.getElementById('riotProgressPlayed');
      var targetEl = document.getElementById('riotProgressTarget');
      var countBarEl = document.getElementById('riotProgressCountBar');
      if (opIsDuoPassForm) return;
      if (opIsWinBoostForm || opIsPlacementsForm || opIsProGamesForm) {
        var wbWins = parseInt(progress.wins || 0, 10) || 0;
        var wbLosses = parseInt(progress.losses || 0, 10) || 0;
        var played = opIsWinBoostForm ? Math.max(0, wbWins - wbLosses) : ((opIsProGamesForm || opIsPlacementsForm) ? (wbWins + wbLosses) : wbWins);
        var dynamicTarget = opBaseTarget;
        if (playedEl) playedEl.textContent = String(played);
        if (targetEl) targetEl.textContent = String(dynamicTarget);
        if (countBarEl) {
          var countPct = dynamicTarget > 0 ? Math.min(100, (played / dynamicTarget) * 100) : 0;
          countBarEl.style.width = countPct.toFixed(1) + '%';
          countBarEl.classList.toggle('lb-op-count-progress-fill--done', countPct >= 100);
        }
      }
    }

    function syncOrderProgress(options) {
      var opts = options || {};
      var silent = !!opts.silent;
      if (isRefreshingProgress) return;
      if (!silent) {
        var _now = Date.now();
        var _elapsed = _now - lastProgressRefresh;
        if (_elapsed < PROGRESS_REFRESH_COOLDOWN) {
          var _remaining = Math.ceil((PROGRESS_REFRESH_COOLDOWN - _elapsed) / 1000);
          if (typeof create_toast === 'function') create_toast('warning', 'Cooldown', 'Please wait ' + _remaining + 's before refreshing again.');
          return;
        }
        lastProgressRefresh = _now;
      }
      if (!hasRiotId) { setProgressSyncState('Riot ID missing. Tracking cannot run.', 'danger', false); return; }

      isRefreshingProgress = true;
      var btn = refreshProgressBtn;
      var icon = btn ? btn.querySelector('i') : null;
      if (btn) btn.disabled = true;
      if (icon) icon.classList.add('fa-spin');
      setProgressSyncState('Refreshing progress from Riot API…', 'muted', true);

      var fd = new FormData();
      fd.append('action', 'refresh_order_progress');
      fd.append('order_id', '<?= (int) ($data['id'] ?? 0) ?>');
      if (silent) fd.append('silent', '1');

      fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) {
          return res.text().then(function (text) {
            try { return JSON.parse(String(text || '').replace(/^\uFEFF/, '')); }
            catch (error) {
              console.error('Invalid tracking response:', text);
              throw new Error('Tracking returned an invalid server response. Please try again.');
            }
          });
        })
        .then(function (json) {
          if (!json || typeof json !== 'object') throw new Error('Invalid response while refreshing progress.');
          if (json.orderProgress) applyProgressData(json.orderProgress);
          if (json.ok) {
            setProgressSyncState('Progress updated successfully.', 'success', false);
            document.dispatchEvent(new CustomEvent('lbProgressSynced'));
          } else {
            setProgressSyncState((json.message || 'Failed to refresh progress.').toString(), 'danger', false);
          }
          if (json.sendToast && typeof window.create_toast === 'function') {
            create_toast(json.sendToast.type, json.sendToast.title, json.sendToast.message);
          }
        })
        .catch(function (e) {
          var msg = e && e.message ? e.message : 'Failed to refresh progress.';
          setProgressSyncState(msg, 'danger', false);
          if (!silent && typeof create_toast === 'function') create_toast('danger', 'Error', msg);
        })
        .finally(function () {
          isRefreshingProgress = false;
          if (btn) btn.disabled = false;
          if (icon) icon.classList.remove('fa-spin');
        });
    }

    if (refreshProgressBtn) {
      refreshProgressBtn.addEventListener('click', function () { syncOrderProgress({ silent: false }); });
    }

    if (hasRiotId && boosterOrderStatus === 'IN_PROGRESS') {
      setTimeout(function () { syncOrderProgress({ silent: true }); }, 300);
    }
  })();
</script>

<?php if ($lb_op_is_duo_order): ?>
<script>
  (function () {
    var ajaxUrl = '<?= AJAX_URL ?>';
    var orderId = <?= (int) ($data['id'] ?? 0) ?>;

    var overlay     = document.getElementById('duoAccountModal');
    var backdrop    = document.getElementById('duoAccountBackdrop');
    var closeBtn    = document.getElementById('duoAccountClose');
    var cancelBtn   = document.getElementById('duoAccountCancel');
    var openBtn     = document.getElementById('openDuoAccountModalBtn');
    var input       = document.getElementById('duo_booster_riot_id');
    var saveBtn     = document.getElementById('duo_booster_riot_save');
    var clearBtn    = document.getElementById('duo_booster_clear');
    var errorEl     = document.getElementById('duo_booster_riot_error');
    var previewEl   = document.getElementById('duo_booster_riot_preview');
    var previewLbl  = document.getElementById('duo_booster_riot_preview_label');
    var previewName = document.getElementById('duo_booster_riot_name');
    var previewMeta = document.getElementById('duo_booster_riot_meta');
    var previewIcon = document.getElementById('duo_booster_riot_icon');
    var previewIconFb = document.getElementById('duo_booster_riot_icon_fallback');
    var confirmBtn  = document.getElementById('duo_booster_riot_confirm');

    if (!overlay) return;

    // ── Open / Close ─────────────────────────────────────────────
    function openModal() {
      overlay.style.display = 'flex';
      overlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      setTimeout(function(){ overlay.classList.add('lbd-overlay--visible'); }, 10);
      if (input) { input.focus(); input.select(); }
      schedulePreview();
    }
    function closeModal() {
      overlay.classList.remove('lbd-overlay--visible');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      setTimeout(function(){ overlay.style.display = 'none'; }, 220);
      setError('');
      setPreview('idle');
    }

    if (openBtn)   openBtn.addEventListener('click', openModal);
    if (closeBtn)  closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop)  backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && overlay.classList.contains('lbd-overlay--visible')) closeModal(); });

    // ── Helpers ──────────────────────────────────────────────────
    var verifiedOk = false, verifiedVal = '', confirmedVal = '', inFlight = null, timer = null, controller = null;

    function norm(v) { return (v || '').trim().replace(/\s*#\s*/, '#'); }
    function valid(v) { return /^[^#]{2,32}#[A-Za-z0-9]{2,16}$/.test(v); }
    function setError(msg) {
      if (!errorEl) return;
      errorEl.textContent = msg || '';
      errorEl.style.display = msg ? 'block' : 'none';
    }
    function setSaveLocked(locked) { if (saveBtn) saveBtn.disabled = locked; }

    function setPreview(state, data) {
      if (!previewEl) return;
      data = data || {};
      if (state === 'idle') { previewEl.hidden = true; return; }
      previewEl.hidden = false;
      previewEl.className = 'lbd-preview' + (state === 'found' ? ' is-found' : state === 'error' ? ' is-error' : '');
      if (state === 'loading') {
        if (previewLbl)  previewLbl.textContent  = 'Checking…';
        if (previewName) previewName.textContent  = data.riot_id || '…';
        if (previewMeta) previewMeta.textContent  = '';
        if (confirmBtn)  confirmBtn.hidden = true;
        if (previewIcon) { previewIcon.removeAttribute('src'); previewIcon.style.display = 'none'; }
        if (previewIconFb) previewIconFb.style.display = 'grid';
        return;
      }
      if (state === 'found') {
        if (previewLbl)  previewLbl.textContent  = 'Account found ✓';
        if (previewName) previewName.textContent  = data.riot_id || data.ign || '';
        if (previewMeta) previewMeta.textContent  = 'Level ' + (data.summoner_level || '?') + (data.server ? ' · ' + data.server.toUpperCase() : '');
        if (confirmBtn)  confirmBtn.hidden = false;
        if (previewIcon && data.profile_icon_url) { previewIcon.src = data.profile_icon_url; previewIcon.style.display = 'block'; if (previewIconFb) previewIconFb.style.display = 'none'; }
        return;
      }
      if (state === 'error') {
        if (previewLbl)  previewLbl.textContent  = 'Not found';
        if (previewName) previewName.textContent  = data.riot_id || '';
        if (previewMeta) previewMeta.textContent  = data.message || 'Riot ID not found';
        if (confirmBtn)  confirmBtn.hidden = true;
        if (previewIcon) { previewIcon.removeAttribute('src'); previewIcon.style.display = 'none'; }
        if (previewIconFb) previewIconFb.style.display = 'grid';
      }
    }

    function markConfirmed(riotId) {
      confirmedVal = riotId;
      if (confirmBtn) { confirmBtn.style.background = 'rgba(74,222,128,.12)'; confirmBtn.style.borderColor = 'rgba(74,222,128,.35)'; confirmBtn.style.color = '#4ade80'; }
      setSaveLocked(false);
    }

    function verify(riotId) {
      riotId = norm(riotId || (input ? input.value : ''));
      if (!valid(riotId)) { setError('Format: YourName#TAG'); setPreview('error', { riot_id: riotId, message: 'Use format: Name#TAG' }); return Promise.resolve(false); }
      if (verifiedOk && verifiedVal === riotId) return Promise.resolve(true);
      if (controller) controller.abort();
      controller = window.AbortController ? new AbortController() : null;
      setError(''); setPreview('loading', { riot_id: riotId });
      var fd = new FormData();
      fd.append('action', 'booster_preview_riot_account');
      fd.append('order_id', orderId);
      fd.append('riot_id', riotId);
      inFlight = fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin', signal: controller ? controller.signal : undefined })
        .then(function(r){ return r.json(); })
        .then(function(json){
          if (json && json.ok) { verifiedOk = true; verifiedVal = riotId; setPreview('found', json.account || { riot_id: riotId }); return true; }
          setPreview('error', { riot_id: riotId, message: 'Riot ID not found' }); return false;
        })
        .catch(function(e){ if (e && e.name === 'AbortError') return false; setPreview('error', { riot_id: riotId, message: 'Riot ID not found' }); return false; })
        .finally(function(){ inFlight = null; });
      return inFlight;
    }

    function schedulePreview() {
      if (!input) return;
      var v = norm(input.value);
      clearTimeout(timer);
      if (!v) { setError(''); setPreview('idle'); return; }
      if (!valid(v)) { setError('Format: YourName#TAG'); return; }
      if (verifiedOk && verifiedVal === v) return;
      verifiedOk = false; verifiedVal = ''; confirmedVal = ''; setSaveLocked(true);
      timer = setTimeout(function(){ verify(v); }, 450);
    }

    function updateCard(ign) {
      if (!openBtn) return;
      var subEl = openBtn.querySelector('.lb-op-duo-card__sub');
      if (!subEl) return;
      if (ign) {
        openBtn.classList.remove('lb-op-duo-card--unset');
        openBtn.classList.add('lb-op-duo-card--set');
        subEl.className = 'lb-op-duo-card__sub lb-op-duo-card__sub--set';
        subEl.innerHTML = '<i class="fa-solid fa-circle-check fa-xs"></i> ' + ign + ' <span class="lb-op-duo-card__tag">Stats tracked from your account</span>';
        var iconEl = openBtn.querySelector('.lb-op-duo-card__icon');
        if (iconEl) { iconEl.style.background = 'rgba(74,222,128,.10)'; iconEl.style.borderColor = 'rgba(74,222,128,.25)'; iconEl.style.color = '#4ade80'; }
      } else {
        openBtn.classList.remove('lb-op-duo-card--set');
        openBtn.classList.add('lb-op-duo-card--unset');
        subEl.className = 'lb-op-duo-card__sub lb-op-duo-card__sub--unset';
        subEl.innerHTML = '<i class="fa-solid fa-circle-exclamation fa-xs"></i> Not set — add your account to track your own stats';
        var iconEl2 = openBtn.querySelector('.lb-op-duo-card__icon');
        if (iconEl2) { iconEl2.style.background = ''; iconEl2.style.borderColor = ''; iconEl2.style.color = ''; }
      }
    }

    if (input) {
      input.addEventListener('input', schedulePreview);
      input.addEventListener('blur', function(){ input.value = norm(input.value); schedulePreview(); });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('mousedown', function(e){ e.preventDefault(); });
      confirmBtn.addEventListener('click', function(e){
        e.preventDefault(); e.stopPropagation();
        var v = norm(input ? input.value : '');
        if (!verifiedOk || verifiedVal !== v) { setError('Please wait for account verification.'); return; }
        markConfirmed(v);
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('mousedown', function(e){ e.preventDefault(); });
      saveBtn.addEventListener('click', async function(e){
        e.preventDefault();
        var riotId = norm(input ? input.value : '');
        if (!valid(riotId)) { setError('Format: YourName#TAG'); return; }
        saveBtn.disabled = true; saveBtn.classList.add('is-loading');
        var ok = await verify(riotId);
        if (!ok) { saveBtn.classList.remove('is-loading'); setSaveLocked(true); setError('Riot ID not found'); return; }
        if (confirmedVal !== riotId) { saveBtn.classList.remove('is-loading'); setSaveLocked(true); setError('Please confirm the account first.'); if (confirmBtn) confirmBtn.focus(); return; }
        var fd = new FormData();
        fd.append('action', 'booster_set_duo_account');
        fd.append('order_id', orderId);
        fd.append('riot_id', riotId);
        fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function(r){ return r.json(); })
          .then(function(json){
            if (!json || !json.success) throw new Error((json && json.message) || 'Failed');
            updateCard(riotId);
            if (typeof create_toast === 'function') create_toast('success', 'Saved', json.message || 'Duo account saved.');
            closeModal();
          })
          .catch(function(err){ setError(err && err.message ? err.message : 'Failed to save.'); if (typeof create_toast === 'function') create_toast('danger', 'Error', err && err.message ? err.message : 'Failed to save.'); })
          .finally(function(){ saveBtn.classList.remove('is-loading'); setSaveLocked(confirmedVal !== riotId); });
      });
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', function(){
        if (!confirm('Remove duo account tracking? Games will fall back to client account stats.')) return;
        var fd = new FormData();
        fd.append('action', 'booster_set_duo_account');
        fd.append('order_id', orderId);
        fd.append('riot_id', '');
        fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function(r){ return r.json(); })
          .then(function(json){
            if (!json || !json.success) throw new Error((json && json.message) || 'Failed');
            if (input) input.value = '';
            verifiedOk = false; verifiedVal = ''; confirmedVal = '';
            setPreview('idle'); setSaveLocked(true);
            var wrap = document.getElementById('lb_duo_current_wrap');
            if (wrap) wrap.style.display = 'none';
            updateCard(null);
            if (typeof create_toast === 'function') create_toast('success', 'Removed', 'Duo account removed.');
          })
          .catch(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Failed to remove.'); });
      });
    }
  })();
</script>
<?php endif; ?>


<script>
  (function () {
    var asset_url = '<?= ASSET_URL ?>';
    var body = document.getElementById('lbMhBody');
    var pager = document.getElementById('lbMhPager');
    var pagerInfo = document.getElementById('lbMhPagerInfo');
    var prevBtn = document.getElementById('lbMhPrev');
    var nextBtn = document.getElementById('lbMhNext');
    var totalBadge = document.getElementById('lbMhTotal');
    var countBadge = document.getElementById('lbMhCountBadge');
    var modalEl = document.getElementById('matchHistoryModal');
    if (!body) return;

    var orderId = <?= (int) ($data['id'] ?? 0) ?>;
    var champUrl = '<?= rtrim(LOL_CHAMP_URL, '/') ?>';
    var roleUrl = asset_url + '/core/main/img/lol/roles/';
    var ROLE_MAP = { TOP: 'TopLane', JUNGLE: 'Jungle', MIDDLE: 'MidLane', BOTTOM: 'AdCarry', UTILITY: 'Support' };
    function roleFile(pos) { return ROLE_MAP[pos] || null; }
    var ajaxUrl = '<?= AJAX_URL ?>';
    var perPage = 20;
    var currentPage = 1;
    var loading = false;

    var QUEUE_NAMES = { 420: 'Ranked Solo', 440: 'Ranked Flex', 400: 'Normal Draft', 430: 'Normal Blind', 450: 'ARAM', 900: 'URF', 1020: 'One For All', 76: 'URF' };
    function queueName(id) { return QUEUE_NAMES[parseInt(id, 10)] || 'Match'; }

    function fmtDuration(secs) {
      var s = parseInt(secs, 10) || 0;
      var m = Math.floor(s / 60);
      var r = s % 60;
      return m + ':' + (r < 10 ? '0' : '') + r;
    }

    function fmtDate(raw) {
      if (!raw) return ['—', ''];
      var d = new Date(raw.toString().replace(' ', 'T'));
      if (isNaN(d.getTime())) return [raw, ''];
      return [
        d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }),
        d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })
      ];
    }

    function escHtml(value) {
      return (value || '').toString().replace(/[&<>"']/g, function (ch) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
      });
    }

    function boosterIconHtml(m) {
      var icon = (
        m.booster_icon ||
        m.booster_avatar ||
        m.booster_icon_url ||
        m.booster_image ||
        m.booster_img ||
        (m.booster && m.booster.icon) ||
        ''
      ).toString().trim();

      if (icon) {
        return '<span class="lb-mh-booster-ico lb-mh-booster-ico--img"><img class="lb-mh-booster-img" src="' + escHtml(icon) + '" alt="" loading="lazy" onerror="this.closest(\'.lb-mh-booster-ico\').innerHTML=\'<i class=&quot;fa-duotone fa-user-shield&quot;></i>\'"></span>';
      }

      return '<span class="lb-mh-booster-ico"><i class="fa-duotone fa-user-shield"></i></span>';
    }

    

    var MH_TIER_IDS = { IRON:1, BRONZE:2, SILVER:3, GOLD:4, PLATINUM:5, EMERALD:6, DIAMOND:7, MASTER:8, GRANDMASTER:9, CHALLENGER:10 };

    function fullRankSnapshotLabel(snapshot) {
      var cleaned = (snapshot || '').toString().trim();
      if (!cleaned) return '';
    
      // Accept both formats: "EMERALD II 67 LP" and "EMERALD II · 67 LP".
      cleaned = cleaned.replace(/\s*[·•|]\s*/g, ' ');
      cleaned = cleaned.replace(/\s+/g, ' ');
    
      // Important: match longer roman numerals first, otherwise "II" becomes "I".
      var m = cleaned.match(/^(IRON|BRONZE|SILVER|GOLD|PLATINUM|EMERALD|DIAMOND|MASTER|GRANDMASTER|CHALLENGER)\s*(IV|III|II|I)?\s*(?:(\d+)\s*LP)?/i);
      if (!m) return cleaned;
    
      var tierMap = {
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
    
      var tier = tierMap[m[1].toUpperCase()] || m[1];
      var div = m[2] ? (' ' + m[2].toUpperCase()) : '';
      var lp = m[3] ? (' ' + parseInt(m[3], 10) + ' LP') : '';
    
      return tier + div + lp;
    }

    function rankColHtml(m) {
      var rankSnap = (m.rank_snapshot || '').toString().trim();
      var rankDisplay = fullRankSnapshotLabel(rankSnap);
    
      if (!rankSnap) {
        return '<div class="lb-mh-rank-col"><span class="lb-mh-rank-name" style="opacity:.28">—</span></div>';
      }
    
      var tierWord = rankSnap.split(/\s+/)[0].toUpperCase().trim();
      var tierIconId = MH_TIER_IDS[tierWord] || 0;
      var rankImgUrl = asset_url + '/core/main/img/lol/ranks/max/' + tierIconId + '.png';
    
      return '<div class="lb-mh-rank-col lb-mh-rank-col--snap">'
        + '<div class="lb-mh-rank-inner">'
        + '<img class="lb-mh-rank-ico" src="' + rankImgUrl + '" alt="' + escHtml(tierWord) + '" loading="lazy" onerror="this.style.visibility=\'hidden\'">'
        + '<span class="lb-mh-rank-name" title="' + escHtml(rankSnap) + '">' + escHtml(rankDisplay) + '</span>'
        + '</div>'
        + '</div>';
    }


    function renderRows(rows) {
      if (!rows || rows.length === 0) {
        body.innerHTML = '<div class="lb-mh-placeholder">No matches tracked yet.</div>';
        return;
      }
      var html = '';
      for (var i = 0; i < rows.length; i++) {
        var m = rows[i];
        var isRemake = parseInt(m.is_remake || 0, 10) === 1 || (parseInt(m.duration || 0, 10) > 0 && parseInt(m.duration || 0, 10) < 300);
        var won = parseInt(m.won, 10) === 1;
        var rowCls = isRemake ? 'lb-mh-row--remake' : (won ? 'lb-mh-row--win' : 'lb-mh-row--loss');
        var badge = isRemake
          ? '<span class="lb-mh-badge lb-mh-badge--remake"><i class="fa-solid fa-rotate-left fa-xs me-1"></i>Remake</span>'
          : (won
            ? '<span class="lb-mh-badge lb-mh-badge--win"><i class="fa-solid fa-trophy fa-xs me-1"></i>Win</span>'
            : '<span class="lb-mh-badge lb-mh-badge--loss"><i class="fa-solid fa-skull fa-xs me-1"></i>Loss</span>');

        var champ = (m.champion || '').toString().trim();
        var champImg = champ
          ? '<img class="lb-mh-champ-img" src="' + champUrl + '/' + encodeURIComponent(champ) + '.png" alt="' + champ + '" loading="lazy" onerror="this.style.visibility=\'hidden\'">'
          : '<span class="lb-mh-champ-img"></span>';
        var champCol = '<div class="lb-mh-champ-col">' + champImg + '<div class="lb-mh-champ-info"><div class="lb-mh-champ-name">' + escHtml(champ || '—') + '</div><div class="lb-mh-queue">' + queueName(m.queue_id) + '</div></div></div>';

        var boosterName = (m.booster_name || '').toString().trim() || (m.booster_id ? ('#' + m.booster_id) : 'Unassigned');
        var boosterCol = '<div class="lb-mh-booster-col">' + boosterIconHtml(m) + '<span class="lb-mh-booster-info"><span class="lb-mh-booster-name">' + escHtml(boosterName) + '</span><span class="lb-mh-booster-sub">Booster</span></span></div>';

        var playModeRaw = (m.play_mode || m.match_type || '').toString().trim().toLowerCase();
        var isDuoMode = playModeRaw === 'duo';
        var modeText = isDuoMode ? 'Duo' : 'Solo';
        var statSubject = (m.stat_subject || '').toString().trim().toLowerCase();
        var statSubject = (m.stat_subject || '').toString().trim().toLowerCase();
        var hasDuoAccount = parseInt(m.has_duo_account || 0, 10) === 1;
        var modeSub = statSubject === 'client'
          ? 'Client stats'
          : (statSubject === 'booster'
            ? 'Booster stats'
            : (isDuoMode && hasDuoAccount ? 'Duo stats' : 'Booster stats'));
        var modeIcon = isDuoMode ? 'fa-user-group' : 'fa-user';
        var modeCol = '<div class="lb-mh-mode-col"><span class="lb-mh-mode-pill lb-mh-mode-pill--' + (isDuoMode ? 'duo' : 'solo') + '"><i class="fa-duotone ' + modeIcon + ' fa-xs"></i>' + modeText + '</span><span class="lb-mh-mode-sub">' + modeSub + '</span></div>';

        var pos = (m.position || '').toString().trim().toUpperCase();
        var roleFile_ = roleFile(pos);
        var roleLabel = pos ? (pos.charAt(0) + pos.slice(1).toLowerCase()) : '';
        var roleCol = roleFile_
          ? '<div class="lb-mh-role-col"><img class="lb-mh-role-img" src="' + roleUrl + roleFile_ + '.png" alt="' + roleLabel + '" onerror="this.style.visibility=\'hidden\'"><span>' + roleLabel + '</span></div>'
          : '<div class="lb-mh-role-col"><span style="opacity:.35">—</span></div>';

        var k = parseInt(m.kills, 10) || 0;
        var d = parseInt(m.deaths, 10) || 0;
        var a = parseInt(m.assists, 10) || 0;
        var kdaRatio = d === 0 ? 'Perfect' : ((k + a) / d).toFixed(2) + ' KDA';
        var kdaCol = '<div class="lb-mh-kda-col"><span class="lb-mh-kda">' + k + '<span class="lb-mh-kda-sep">/</span>' + d + '<span class="lb-mh-kda-sep">/</span>' + a + '</span><span class="lb-mh-kda-ratio">' + kdaRatio + '</span></div>';

        var durCol = '<div class="lb-mh-dur-col"><span class="lb-mh-dur">' + fmtDuration(m.duration) + '</span><span class="lb-mh-sub">Duration</span></div>';
        var rankCol = rankColHtml(m);

        var dp = fmtDate(m.played_at);
        var dateCol = '<div class="lb-mh-date-col"><span class="lb-mh-date">' + dp[0] + '</span><span class="lb-mh-time">' + dp[1] + '</span></div>';

        html += '<div class="lb-mh-row ' + rowCls + '"><div class="lb-mh-result">' + badge + '</div>' + champCol + boosterCol + modeCol + roleCol + kdaCol + durCol + rankCol + dateCol + '</div>';
      }
      body.innerHTML = html;
    }

    function loadPage(page) {
      if (loading) return;
      loading = true;
      body.style.opacity = '0.25';
      body.style.pointerEvents = 'none';
      if (prevBtn) prevBtn.disabled = true;
      if (nextBtn) nextBtn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'get_order_matches');
      fd.append('order_id', orderId);
      fd.append('page', page);
      fd.append('per_page', perPage);
      fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          if (!json.ok) throw new Error(json.message || 'Failed.');
          var meta = json.matches;
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
        })
        .catch(function () {
          body.style.opacity = '';
          body.style.pointerEvents = '';
          body.innerHTML = '<div class="lb-mh-placeholder">Failed to load matches. Please try again.</div>';
        })
        .finally(function () { loading = false; });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { loadPage(currentPage - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { loadPage(currentPage + 1); });
    if (modalEl) {
      modalEl.addEventListener('show.bs.modal', function () { loadPage(1); });
    }

    // Reload if the modal is already open after a Riot sync.
    document.addEventListener('lbProgressSynced', function () {
      if (modalEl && modalEl.classList.contains('show')) { loadPage(1); }
    });
  })();
</script>

<script>
(function(){
  function initRanked5sBoosterTabs(){
    const tabs = Array.from(document.querySelectorAll('[data-r5s-booster-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-r5s-booster-panel]'));
    if (!tabs.length || !panels.length) return;

    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        const idx = String(tab.getAttribute('data-r5s-booster-tab') || '0');
        tabs.forEach(function(t){
          t.classList.toggle('is-active', String(t.getAttribute('data-r5s-booster-tab') || '') === idx);
        });
        panels.forEach(function(panel){
          panel.classList.toggle('is-active', String(panel.getAttribute('data-r5s-booster-panel') || '') === idx);
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRanked5sBoosterTabs);
  } else {
    initRanked5sBoosterTabs();
  }
})();
</script>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">

<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/css/star-rating.min.css" media="all"
  rel="stylesheet" type="text/css" />

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme CSS files as mentioned below (and change the theme property of the plugin) -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-svg/theme.css" media="all"
  rel="stylesheet" type="text/css" />

<style>
  .lb-client-cell{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;}
  .lb-client-cell .avatar{flex:0 0 auto;}
  .lb-client-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
  .lb-client-presence{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.18rem .52rem;font-size:.72rem;font-weight:800;line-height:1;text-transform:none;letter-spacing:.01em;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.045);white-space:nowrap;}
  .lb-client-presence__dot{width:.45rem;height:.45rem;border-radius:50%;display:inline-block;box-shadow:0 0 0 3px rgba(255,255,255,.04);}
  .lb-client-presence.is-online{color:#45f0b1;border-color:rgba(45,212,138,.32);background:rgba(45,212,138,.10);}
  .lb-client-presence.is-online .lb-client-presence__dot{background:#27e6a1;box-shadow:0 0 0 3px rgba(39,230,161,.14),0 0 12px rgba(39,230,161,.45);}
  .lb-client-presence.is-offline{color:rgba(255,255,255,.62);border-color:rgba(255,255,255,.10);background:rgba(255,255,255,.045);}
  .lb-client-presence.is-offline .lb-client-presence__dot{background:rgba(255,255,255,.35);}


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

  .lb-r5s-booster-panel {
    display: none;
  }

  .lb-r5s-booster-panel.is-active {
    display: block;
  }

  .lb-r5s-team-card .booster-intro-block {
    position: relative;
  }

  /* =========================
   WRAP + BASIC CARDS (client-like)
========================= */
  .order-page-wrap {
    padding: 1rem;
  }

  @media (min-width:992px) {
    .order-page-wrap {
      padding: 1.75rem;
    }
  }

  .order-layout {
    --order-gap: 1.25rem;
  }

  .order-layout>[class*="col-"] {
    display: flex;
    flex-direction: column;
    gap: var(--order-gap);
  }

  .card {
    border-radius: 1rem;
    overflow: visible;
  }

  .card-header {
    padding: .8rem 1rem;
  }

  .card-body,
  .card-footer {
    padding: .85rem 1rem;
  }

  .card-header-title {
    font-size: .9rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 0;
  }

  /* Only cards that should clip */
  .order-chat-card,
  .lb-complete-modal .modal-content,
  .lb-claim-modal .modal-content,
  .lb-drop-modal .modal-content {
    overflow: hidden;
  }

    /* =========================
   BOOSTER INTRO CARD (Booster side)
   Same look as Client booster-card
========================= */

  /* Inline edit (booster card) */
  .lb-booster-edit-card { position: relative; }
  .lb-editable { position: relative; }
  .lb-editable-pill { position: relative; padding-right: 2.1rem; }
  .lb-edit-pen {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.45);
    color: #fff;
    display: grid;
    place-items: center;
    opacity: 0;
    transform: translateY(-2px);
    transition: .12s ease;
    z-index: 5;
  }
  .lb-editable:hover .lb-edit-pen,
  .lb-booster-edit-card:hover .lb-edit-pen--cover,
  .booster-intro-avatar:hover .lb-edit-pen--avatar,
  .lb-editable-pill:hover .lb-edit-pen--pill {
    opacity: 1;
    transform: translateY(0);
  }
  .lb-edit-pen:hover { filter: brightness(1.12); }
  .lb-edit-pen--cover { top: 12px; left: 12px; right: auto; opacity: 0; }
  .lb-edit-pen--avatar { top: auto; bottom: 6px; right: 6px; opacity: 0; }
  .lb-edit-pen--pill { top: 6px; right: 6px; transform: none; width: 26px; height: 26px; opacity: 0; }
  .lb-edit-modal { border-radius: 18px; border: 1px solid rgba(255,255,255,.10); background: rgba(24,24,28,.92); backdrop-filter: blur(10px); }
  .lb-edit-modal .modal-header { border-bottom: 1px solid rgba(255,255,255,.08); }
  .lb-edit-modal .modal-footer { border-top: 1px solid rgba(255,255,255,.08); }

  .booster-intro-card {
    container-type: inline-size;
    border-radius: 1.25rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
  }

  [data-theme="light"] .booster-intro-card {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }

  .booster-intro-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: .12;
    pointer-events: none;
  }

  .booster-intro-body {
    position: relative;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: .9rem;
  }

  .booster-intro-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-width: 0;
  }

  .booster-intro-left {
    display: flex;
    align-items: center;
    gap: .9rem;
    min-width: 0;
    flex: 1 1 auto;
  }

  .booster-intro-avatar {
    width: 68px;
    height: 68px;
    border-radius: 999px;
    overflow: hidden;
    flex: 0 0 auto;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 14px 45px rgba(0, 0, 0, .45);
    background: rgba(255, 255, 255, .03);
  }

  .booster-intro-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .booster-intro-glow {
    position: absolute;
    inset: -8px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(78, 161, 255, .28), transparent 65%);
    filter: blur(6px);
    z-index: -1;
  }

  .booster-intro-main { min-width: 0; }

  .booster-intro-name {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 950;
    font-size: 1.10rem;
    line-height: 1.1;
    min-width: 0;
  }

  .booster-intro-name span {
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  


/* Presence pill (Online/Offline) */
.lb-presence-pill{
  display:inline-flex;
  align-items:center;
  gap:.55rem;
  padding:.34rem .60rem;
  border-radius:999px;
  font-size:.74rem;
  font-weight:800;
  letter-spacing:.01em;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.14);
  color: rgba(255,255,255,.70);
  flex: 0 0 auto;
}
.lb-presence-dot{
  width: 12px;
  height: 12px;
  border-radius:50%;
  position:relative;
  background: rgba(255,255,255,.30); /* offline */
  box-shadow: 0 0 0 2px rgba(10,12,20,.70);
}
.lb-presence-pill.online{
  background: rgba(53,208,127,.14);
  border-color: rgba(53,208,127,.35);
  color: rgba(53,208,127,.95);
}
.lb-presence-dot.online{
  background:#35d07f;
  box-shadow: 0 0 0 2px rgba(10,12,20,.75), 0 0 14px rgba(53,208,127,.55);
}
@keyframes lbPresencePulse{
  0%{ transform: scale(.85); opacity:.55; }
  60%{ transform: scale(1.15); opacity:.12; }
  100%{ transform: scale(1.55); opacity:0; }
}
.lb-presence-dot.online::after{
  content:"";
  position:absolute;
  inset:-9px;
  border-radius:50%;
  border:2px solid rgba(53,208,127,.45);
  animation: lbPresencePulse 1.6s ease-in-out infinite;
}

.booster-rank-pill {
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
  }

  [data-theme="light"] .booster-rank-pill {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .10);
  }

  .booster-rank-pill img {
    width: 22px;
    height: 22px;
    border-radius: 8px;
  }

  .booster-rank-pill i,
  .booster-rank-pill svg {
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }

  .booster-intro-right {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  .visit-profile-btn {
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
  }

  .visit-profile-btn i { font-size: .95rem; opacity: .95; }

  .visit-profile-btn:hover {
    transform: translateY(-1px);
    background: rgba(78, 161, 255, .18);
    border-color: rgba(78, 161, 255, .35);
    color: #8fc2ff;
  }

  [data-theme="light"] .visit-profile-btn {
    color: #0d6efd;
    background: rgba(13, 110, 253, .10);
    border-color: rgba(13, 110, 253, .22);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
  }

  [data-theme="light"] .visit-profile-btn:hover {
    background: rgba(13, 110, 253, .14);
    border-color: rgba(13, 110, 253, .30);
    color: #0b5ed7;
  }

  .booster-intro-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
  }

  .booster-intro-block {
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(0, 0, 0, .14);
    border-radius: 14px;
    padding: .75rem .8rem;
    min-width: 0;
  }

  [data-theme="light"] .booster-intro-block {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .08);
  }

  .booster-intro-label {
    margin: 0 0 .55rem 0;
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .72;
    font-weight: 900;
  }

  .booster-intro-champs,
  .booster-intro-roles,
  .booster-intro-langs {
    display: flex;
    align-items: flex-start;
    align-content: flex-start;
    gap: .35rem;
    flex-wrap: wrap;
    max-width: 100%;
    min-width: 0;
  }

  .booster-intro-champs .champ {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
    flex: 0 0 auto;
  }

  .booster-intro-champs .more {
    padding: .14rem .5rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .78rem;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .05);
  }

  .booster-intro-champs .more {
    cursor: help;
    user-select: none;
  }

  .lb-champs-tooltip {
    position: fixed;
    z-index: 99999;
    width: min(380px, calc(100vw - 28px));
    max-height: 260px;
    overflow-y: auto;
    padding: 12px;
    border-radius: 16px;
    border: 1px solid rgba(124, 92, 255, .35);
    background: rgba(24, 25, 30, .98);
    box-shadow: 0 22px 70px rgba(0, 0, 0, .58), 0 0 0 1px rgba(255, 255, 255, .04) inset;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    opacity: 0;
    visibility: hidden;
    pointer-events: auto;
    transform: translateY(6px);
    transition: opacity .12s ease, transform .12s ease, visibility .12s ease;
    scrollbar-width: thin;
    scrollbar-color: rgba(124, 92, 255, .65) rgba(255, 255, 255, .06);
  }

  .lb-champs-tooltip.is-visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .lb-champs-tooltip::-webkit-scrollbar {
    width: 6px;
  }

  .lb-champs-tooltip::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, .06);
    border-radius: 999px;
  }

  .lb-champs-tooltip::-webkit-scrollbar-thumb {
    background: rgba(124, 92, 255, .65);
    border-radius: 999px;
  }

  .lb-champs-tooltip__title {
    margin: 0 0 10px;
    font-size: 11px;
    font-weight: 950;
    letter-spacing: .10em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .68);
  }

  .lb-champs-tooltip__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(34px, 1fr));
    gap: 8px;
  }

  .lb-champs-tooltip__item {
    width: 34px;
    height: 34px;
    border-radius: 11px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .055);
    overflow: hidden;
  }

  .lb-champs-tooltip__item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .lb-champs-tooltip__tag {
    width: 100%;
    min-height: 34px;
    padding: 0 8px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .055);
    font-size: 11px;
    font-weight: 900;
    color: rgba(255, 255, 255, .88);
    text-align: center;
  }

  .role-pill {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
  }

  .role-pill img {
    width: 26px;
    height: 26px;
    object-fit: contain;
  }

  .booster-intro-tag {
    padding: .34rem .65rem;
    border-radius: 999px;
    font-weight: 800;
    font-size: .78rem;
    line-height: 1;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .05);
  }

  .booster-intro-rank-mini {
    display: flex;
    align-items: center;
    gap: .45rem;
    flex-wrap: wrap;
    font-weight: 900;
  }

  .booster-intro-rank-mini .rank-mini-icon {
    width: 30px;
    height: 30px;
    object-fit: contain;
  }

  .booster-intro-langs .flag {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
  }

  .na { opacity: .7; font-weight: 800; }

  @media (max-width: 991.98px) {
    .booster-intro-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }

  @media (max-width: 575.98px) {
    .booster-intro-card.lb-booster-edit-card { max-width: 100%; }
    .booster-intro-body { padding: .9rem; gap: .75rem; }
    .booster-intro-top {
      align-items: stretch;
      flex-direction: column;
      gap: .75rem;
    }
    .booster-intro-left {
      width: 100%;
      align-items: center;
      gap: .75rem;
    }
    .booster-intro-main { flex: 1 1 auto; min-width: 0; }
    .booster-intro-avatar { width: 60px; height: 60px; }
    .booster-intro-name { font-size: 1.02rem; max-width: 100%; }
    .booster-intro-name span { max-width: 100%; }
    .booster-intro-right { width: 100%; justify-content: stretch; }
    .visit-profile-btn { width: 100%; justify-content: center; }
    .booster-rank-pill { max-width: 100%; min-width: 0; }
    .booster-rank-pill .lb-rank-name,
    .booster-rank-pill .lb-tz-name {
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .booster-intro-cards { grid-template-columns: 1fr; }
    .booster-intro-block { min-width: 0; }
    .booster-intro-label {
      white-space: normal;
      word-break: normal;
      overflow-wrap: normal;
    }
  }

  @container (max-width: 420px) {
    .booster-intro-card.lb-booster-edit-card { max-width: 100%; }
    .booster-intro-body { padding: .9rem; gap: .75rem; }
    .booster-intro-top {
      align-items: stretch;
      flex-direction: column;
      gap: .75rem;
    }
    .booster-intro-left {
      width: 100%;
      align-items: center;
      gap: .75rem;
    }
    .booster-intro-main { flex: 1 1 auto; min-width: 0; }
    .booster-intro-avatar { width: 60px; height: 60px; }
    .booster-intro-name { font-size: 1.02rem; max-width: 100%; }
    .booster-intro-name span { max-width: 100%; }
    .booster-intro-right { width: 100%; justify-content: stretch; }
    .visit-profile-btn { width: 100%; justify-content: center; }
    .booster-rank-pill { max-width: 100%; min-width: 0; }
    .booster-rank-pill .lb-rank-name,
    .booster-rank-pill .lb-tz-name {
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .booster-intro-cards { grid-template-columns: 1fr; }
    .booster-intro-block { min-width: 0; }
    .booster-intro-label {
      white-space: normal;
      word-break: normal;
      overflow-wrap: normal;
    }
  }

/* =========================
   HEADER (lb-head) — SAME AS CLIENT FEEL
========================= */
  .lb-head.card {
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, .07);
    background: rgba(255, 255, 255, .03);
    overflow: visible !important;
    position: relative;
  }

  [data-theme="light"] .lb-head.card {
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 22px 70px rgba(0, 0, 0, .18);
  }

  .lb-head__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1rem .85rem 1rem;
  }

  .lb-head__left {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    min-width: 0;
    flex: 1 1 auto;
  }

  .lb-head__icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    flex: 0 0 auto;
  }

  .lb-head__icon i {
    font-size: 1.35rem;
    opacity: .95;
  }
  .lb-head__icon img {
    width: 1.35rem;
    height: 1.35rem;
    display: block;
    opacity: .95;
  }


  .lb-head__title {
    min-width: 0;
  }

  .lb-head__title-row {
    display: flex;
    align-items: baseline;
    gap: .6rem;
    min-width: 0;
  }

  .lb-head__h1 {
    margin: 0;
    font-weight: 950;
    font-size: 1.15rem;
    line-height: 1.2;
    letter-spacing: .01em;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-head__id {
    font-weight: 900;
    font-size: .85rem;
    opacity: .55;
    white-space: nowrap;
  }

  .lb-head__sub {
    margin-top: .45rem;
  }

  .lb-head__actions {
    flex: 0 0 auto;
    display: flex;
    align-items: flex-start;
  }

  .lb-head__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    padding: .85rem 1rem 1rem 1rem;
    border-top: 1px solid rgba(255, 255, 255, .06);
  }

  [data-theme="light"] .lb-head__meta {
    border-top-color: rgba(0, 0, 0, .06);
  }

  .lb-meta-pill {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .55rem .75rem;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    min-width: 180px;
    max-width: 100%;
  }

  [data-theme="light"] .lb-meta-pill {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }

  .lb-meta-pill__k {
    font-weight: 950;
    font-size: .70rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .55;
    white-space: nowrap;
  }

  .lb-meta-pill__v {
    font-weight: 900;
    font-size: .92rem;
    opacity: .92;
    min-width: 0;
    overflow: hidden;
  }

  /* Earning inline (amount + timer) */
  .lb-earn-inline {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .55rem;
    min-width: 0;
  }

  .lb-earn-amount {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .lb-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .18rem .55rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .02em;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .05);
    white-space: nowrap;
    opacity: .92;
    flex: 0 0 auto;
  }

  [data-theme="light"] .lb-chip {
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .03);
  }

  .lb-chip i {
    font-size: .78rem;
    opacity: .85;
  }

  /* Price bump animation when earning updates */
  .lb-price-bump {
    animation: lbPriceBump .55s ease;
  }

  @keyframes lbPriceBump {
    0% { transform: scale(1); }
    22% { transform: scale(1.06); }
    100% { transform: scale(1); }
  }

  @media (max-width:575.98px) {
    .lb-head__top {
      padding: .85rem .85rem .70rem .85rem;
    }

    .lb-head__meta {
      padding: .70rem .85rem .85rem .85rem;
    }

    .lb-head__icon {
      width: 44px;
      height: 44px;
      border-radius: 13px;
    }

    .lb-head__title-row {
      flex-wrap: wrap !important;
      align-items: flex-start !important;
    }

    .lb-head__id {
      width: 100% !important;
      margin-top: .15rem;
    }

    .lb-head__h1 {
      font-size: 1.05rem;
      white-space: normal !important;
      display: -webkit-box !important;
      -webkit-box-orient: vertical !important;
      -webkit-line-clamp: 3 !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    /* meta pills two-line on mobile */
    .lb-meta-pill {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: .25rem !important;
      min-width: 0 !important;
      flex: 1 1 calc(50% - .55rem);
    }

    .lb-meta-pill__v {
      white-space: normal !important;
      overflow: visible !important;
      text-overflow: unset !important;
      word-break: break-word !important;
      overflow-wrap: anywhere !important;
      line-height: 1.15 !important;
    }
  }

  @media (max-width:420px) {
    .lb-meta-pill {
      flex: 1 1 100%;
    }
  }

  /* Status (lb-status) */
  .lb-status {
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
  }

  .lb-status__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: .95;
  }

  .lb-status.status-inprogress {
    color: #4ea1ff;
    border-color: rgba(78, 161, 255, .25);
    background: rgba(78, 161, 255, .12);
  }

  .lb-status.status-completed {
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }

  .lb-status.status-paused {
    color: #ffc44d;
    border-color: rgba(255, 196, 77, .22);
    background: rgba(255, 196, 77, .10);
  }

  .lb-status.status-unpaid {
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .20);
    background: rgba(255, 107, 107, .10);
  }

  .lb-status.status-paid {
    color: #b18cff;
    border-color: rgba(177, 140, 255, .22);
    background: rgba(177, 140, 255, .10);
  }

  .lb-status.status-processing {
    color: #9aa4b2;
    border-color: rgba(154, 164, 178, .20);
    background: rgba(154, 164, 178, .08);
  }

  /* Keep dropdowns nice */
  .lb-head .dropdown-menu {
    z-index: 1060;
  }

  .lb-head .dropdown-toggle::after {
    display: none !important;
  }

  /* =========================
   OVERVIEW / OPTIONS (client-like stacked list)
========================= */
  .lb-overview-card .card-body {
    padding: .75rem .85rem;
  }

  @media (max-width:575.98px) {
    .lb-overview-card .card-body {
      padding: .70rem .80rem;
    }
  }

  .lb-ov-grid {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr !important;
    gap: .55rem;
  }

  .lb-ov-item {
    display: grid;
    grid-template-columns: 44px 1fr auto;
    grid-template-rows: auto auto;
    align-items: center;
    column-gap: .75rem;
    row-gap: .04rem;
    padding: .58rem .75rem;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    min-width: 0;
    transition: .12s ease;
  }

  [data-theme="light"] .lb-ov-item {
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .08);
  }

  .lb-ov-item:hover {
    background: rgba(255, 255, 255, .04);
    border-color: rgba(255, 255, 255, .12);
    transform: translateY(-1px);
  }

  [data-theme="light"] .lb-ov-item:hover {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .12);
  }

  .lb-option-help {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .05);
    color: rgba(255, 255, 255, .72);
    font-size: .78rem;
    cursor: help;
    flex: 0 0 auto;
    transition: .14s ease;
  }

  [data-theme="light"] .lb-option-help {
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .04);
    color: rgba(0, 0, 0, .62);
  }

  .lb-option-help:hover,
  .lb-option-help:focus-visible {
    color: rgba(255, 255, 255, .96);
    border-color: rgba(124, 107, 255, .34);
    background: rgba(124, 107, 255, .14);
    outline: none;
  }

  [data-theme="light"] .lb-option-help:hover,
  [data-theme="light"] .lb-option-help:focus-visible {
    color: rgba(0, 0, 0, .82);
    border-color: rgba(124, 107, 255, .28);
    background: rgba(124, 107, 255, .10);
  }

  .lb-option-help::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%) translateY(6px);
    width: min(260px, calc(100vw - 48px));
    padding: .62rem .72rem;
    border-radius: 12px;
    background: rgba(12, 14, 21, .96);
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 16px 38px rgba(0, 0, 0, .34);
    color: rgba(255, 255, 255, .94);
    font-size: .76rem;
    font-weight: 700;
    line-height: 1.45;
    text-align: left;
    white-space: normal;
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    z-index: 30;
    transition: opacity .14s ease, transform .14s ease, visibility .14s ease;
  }

  .lb-option-help::before {
    content: '';
    position: absolute;
    left: 50%;
    bottom: calc(100% + 4px);
    transform: translateX(-50%) translateY(6px);
    border: 6px solid transparent;
    border-top-color: rgba(12, 14, 21, .96);
    opacity: 0;
    visibility: hidden;
    transition: opacity .14s ease, transform .14s ease, visibility .14s ease;
    z-index: 29;
  }

  .lb-option-help:hover::after,
  .lb-option-help:hover::before,
  .lb-option-help:focus-visible::after,
  .lb-option-help:focus-visible::before {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
  }

  .lb-ov-ico {
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
  }

  [data-theme="light"] .lb-ov-ico {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
  }

  .lb-ov-label-row {
    grid-column: 2;
    grid-row: 1;
    display: flex;
    align-items: center;
    gap: .45rem;
    min-width: 0;
  }

  .lb-ov-label {
    grid-column: 2;
    grid-row: 1;
    font-weight: 900;
    font-size: .95rem;
    line-height: 1.15;
    white-space: normal;
    min-width: 0;
  }

  .lb-ov-desc {
    display: none;
    grid-column: 2;
    grid-row: 2;
    font-size: .78rem;
    line-height: 1.35;
    opacity: .62;
    min-width: 0;
  }

  .lb-ov-value {
    grid-column: 2 / span 2;
    grid-row: 2;
    font-weight: 900;
    font-size: .90rem;
    opacity: .78;
    line-height: 1.2;
    white-space: normal !important;
    overflow-wrap: anywhere;
    max-width: 100%;
    min-width: 0;

    /* Many icons (champions/agents) should NEVER spill outside the card */
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap !important;
  }

  /* Prevent long icon rows from overflowing the card */
  .lb-ov-item { overflow: hidden; }
  .lb-ov-item--option {
    grid-template-columns: 44px 1fr auto;
    grid-template-rows: auto auto;
    align-items: center;
    overflow: visible;
    z-index: 1;
  }

  .lb-ov-item--option .lb-ov-desc {
    display: block;
  }

  .lb-ov-item--option .lb-ov-value {
    grid-column: 3;
    grid-row: 1 / span 2;
    align-self: center;
    justify-self: end;
    flex-wrap: nowrap !important;
  }
  .lb-ov-item:not(.lb-ov-item--option) .lb-yes-pill,
  .lb-ov-item:not(.lb-ov-item--option) .lb-no-pill {
    margin-left: auto;
  }

  .lb-ov-item:not(.lb-ov-item--option) .lb-ov-value:has(.lb-yes-pill),
  .lb-ov-item:not(.lb-ov-item--option) .lb-ov-value:has(.lb-no-pill) {
    grid-column: 3;
    grid-row: 1 / span 2;
    justify-self: end;
    align-self: center;
    text-align: right;
    white-space: nowrap !important;
  }
  .lb-ov-item--option:hover,
  .lb-ov-item--option:focus-within {
    z-index: 20;
  }
  .lb-ov-value img.rounded-circle {
    height: 20px !important;
    width: 20px !important;
    flex: 0 0 auto;
    margin: 0;
  }

  /* Large champion/agent/role selections in Order View: full-width wrapped icon grid. */
  .lb-ov-item--icons {
    grid-template-columns: 44px minmax(0, 1fr);
    grid-template-rows: auto auto auto;
    align-items: start;
    overflow: visible;
  }

  .lb-ov-item--icons .lb-ov-label-row,
  .lb-ov-item--icons .lb-ov-label {
    grid-column: 2;
    grid-row: 1;
    min-width: 0;
  }

  .lb-ov-item--icons .lb-ov-desc {
    grid-column: 2;
    grid-row: 2;
  }

  .lb-ov-item--icons .lb-ov-value {
    grid-column: 1 / -1;
    grid-row: 3;
    justify-self: stretch;
    align-self: start;
    width: 100%;
    max-width: 100%;
    padding-top: .38rem;
    display: flex;
    flex-wrap: wrap !important;
    justify-content: flex-start;
    white-space: normal !important;
    gap: .35rem;
    overflow: visible;
  }

  .lb-ov-item--icons .lb-ov-value img,
  .lb-ov-item--icons .lb-ov-value img.rounded-circle {
    width: 24px !important;
    height: 24px !important;
    flex: 0 0 auto;
    margin: 0 !important;
  }


  @media (max-width:575.98px) {
    .lb-ov-item {
      padding: .54rem .68rem;
    }

    .lb-ov-ico {
      width: 42px;
      height: 42px;
      border-radius: 15px;
    }

    .lb-ov-item--option {
      grid-template-columns: 42px 1fr auto;
    }
  }

  /* =========================
   NOTES LIST (client-like)
========================= */
  .lb-notes-list {
    display: flex;
    flex-direction: column;
    gap: .7rem;
  }

  .lb-note-item {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .85rem .95rem;
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    transition: .15s ease;
  }

  [data-theme="light"] .lb-note-item {
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .08);
  }

  .lb-note-item:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .045);
    border-color: rgba(255, 255, 255, .12);
  }

  [data-theme="light"] .lb-note-item:hover {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .12);
  }

  .lb-note-ico {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .18);
    color: rgba(255, 255, 255, .9);
  }

  [data-theme="light"] .lb-note-ico {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }

  .lb-note-content {
    min-width: 0;
    flex: 1;
  }

  .lb-note-text {
    font-weight: 800;
    font-size: .95rem;
    line-height: 1.35;
    color: rgba(255, 255, 255, .92);
    word-break: break-word;
    overflow-wrap: anywhere;
  }

  [data-theme="light"] .lb-note-text {
    color: rgba(0, 0, 0, .85);
  }

  .lb-note-meta {
    margin-top: .45rem;
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    font-size: .72rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .65;
  }

  .lb-note-chip {
    padding: .18rem .5rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
  }

  [data-theme="light"] .lb-note-chip {
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .03);
  }

  .lb-notes-empty {
    padding: 1.35rem 1rem;
    border-radius: 1rem;
    border: 1px dashed rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .02);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: left;
    gap: .55rem;
  }

  [data-theme="light"] .lb-notes-empty {
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .12);
  }

  .lb-notes-empty-ico {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .18);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .35);
  }

  [data-theme="light"] .lb-notes-empty-ico {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
  }

  .lb-notes-empty-title {
    font-weight: 900;
    font-size: 1.05rem;
  }

  .lb-notes-empty-sub {
    opacity: .72;
    max-width: 52ch;
    font-size: .9rem;
  }

  /* =========================
   CHAT BASE (kept)
========================= */
  .chat-bg {
    background: #1e2022;
    min-height: 18rem;
    max-height: 23rem;
  }

  [data-theme="light"] .chat-bg {
    background: #F9FAFC;
  }

  #chat_messages {
    max-height: 450px;
    overflow: auto;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scrollbar-width: thin;
    scrollbar-color: #3a4254 #25282a;
  }

  #chat_messages::-webkit-scrollbar {
    width: 6px;
  }

  #chat_messages::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 999px;
  }

  #chat_messages::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, .14);
    border-radius: 999px;
  }

  [data-theme="light"] #chat_messages::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, .18);
  }

  /* =========================
   CHAT REDESIGN (LoLBoost) (kept)
========================= */
  .lb-msg {
    display: flex;
    flex-direction: column;
    max-width: 82%;
  }

  .lb-msg--start {
    align-self: flex-start;
  }

  .lb-msg--end {
    align-self: flex-end;
  }

  .lb-msg__head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    opacity: .95;
  }

  .lb-msg__head--end {
    flex-direction: row-reverse;
    text-align: right;
  }

  .lb-msg__avatar {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    object-fit: cover;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 10px 25px rgba(0, 0, 0, .35);
    background: rgba(255, 255, 255, .03);
  }

  [data-theme="light"] .lb-msg__avatar {
    border-color: rgba(0, 0, 0, .10);
    box-shadow: 0 10px 25px rgba(0, 0, 0, .10);
  }

  .lb-msg__meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    line-height: 1.1;
  }

  .lb-msg__name {
    font-weight: 800;
    font-size: .92rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-msg__time {
    font-size: .74rem;
    color: rgba(255, 255, 255, .55);
  }

  [data-theme="light"] .lb-msg__time {
    color: rgba(0, 0, 0, .55);
  }

  .lb-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: .70rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-left: 8px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
  }

  [data-theme="light"] .lb-badge {
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .04);
  }

  .lb-badge--admin {
    color: #ff6b6b;
    background: rgba(255, 107, 107, .10);
  }

  .lb-badge--booster {
    color: #1fe6c6;
    background: rgba(31, 230, 198, .10);
  }

  .lb-badge--customer {
    color: #4ea1ff;
    background: rgba(78, 161, 255, .10);
  }

  .lb-badge--system {
    color: #b18cff;
    background: rgba(177, 140, 255, .10);
  }

  .lb-msg__bubble {
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
  }

  [data-theme="light"] .lb-msg__bubble {
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
  }

  .lb-msg--end .lb-msg__bubble {
    background: rgba(78, 161, 255, .10);
    border-color: rgba(78, 161, 255, .18);
  }

  [data-theme="light"] .lb-msg--end .lb-msg__bubble {
    background: rgba(78, 161, 255, .10);
    border-color: rgba(78, 161, 255, .25);
  }

  /* =========================
   SYSTEM MESSAGE = FULL WIDTH
========================= */
  .lb-sys-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    /* content centered */
    gap: 6px;
    margin: 2px 0;
  }

  .lb-sys {
    width: 100% !important;
    /* full width */
    max-width: 100% !important;
    /* override old max-width */
    align-self: stretch !important;

    padding: 12px 14px;
    border-radius: 16px;
    border: 1px dashed rgba(177, 140, 255, .38);
    background: rgba(177, 140, 255, .12);
    color: rgba(255, 255, 255, .90);
    font-weight: 800;
    font-size: .88rem;
    text-align: left;

    box-shadow: 0 16px 40px rgba(0, 0, 0, .25);
  }

  /* System message wrapper full width */
  .lb-sys-wrap {
    width: 100%;
    max-width: 100%;
    align-self: stretch;
    position: relative;
  }

  /* System bubble: give bottom padding so time fits inside */
  .lb-sys {
    position: relative;
    width: 100%;
    max-width: 100%;
    padding: 12px 14px 30px 14px;
    /* extra bottom space for time */
  }

  /* Time inside bubble bottom-left (client-like) */
  .lb-sys-time {
    position: absolute;
    left: 14px;
    bottom: 8px;
    font-size: .72rem;
    font-weight: 700;
    opacity: .55;
    letter-spacing: .02em;
    line-height: 1;
    pointer-events: none;
  }

  /* Light theme adjustment */
  [data-theme="light"] .lb-sys-time {
    color: rgba(0, 0, 0, .55);
  }


  /* Light theme */
  [data-theme="light"] .lb-sys {
    color: rgba(0, 0, 0, .82);
    background: rgba(177, 140, 255, .12);
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 12px 32px rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-sys {
    color: rgba(0, 0, 0, .80);
  }

  .daterangepicker {
    z-index: 200 !important;
  }

  /* =========================
   CLAIM MODAL (Premium) (kept)
========================= */
  .lb-claim-modal .modal-content {
    background: #1f2226;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, .55);
  }

  .lb-claim-modal .modal-header {
    padding: 18px 18px 0 18px;
  }

  .lb-claim-head {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
  }

  .lb-claim-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .18);
    color: rgba(255, 255, 255, .92);
    box-shadow: 0 16px 45px rgba(0, 0, 0, .45);
  }

  .lb-claim-modal .modal-title {
    font-weight: 900;
    letter-spacing: .01em;
    margin: 0;
  }

  .lb-claim-sub {
    font-size: .86rem;
    opacity: .7;
    margin-top: 2px;
  }

  .lb-claim-modal .modal-body {
    padding: 14px 18px 10px 18px;
  }

  .lb-claim-card {
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 14px;
    padding: 12px 14px;
  }

  .lb-claim-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 2px;
  }

  .lb-claim-row+.lb-claim-row {
    border-top: 1px solid rgba(255, 255, 255, .06);
  }

  .lb-claim-k {
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .65;
  }

  .lb-claim-v {
    font-weight: 800;
    text-align: right;
    max-width: 65%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .lb-claim-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 12px;
    margin-top: 12px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    font-size: .88rem;
    opacity: .9;
  }

  .lb-claim-modal .modal-footer {
    padding: 12px 18px 18px 18px;
    border-top: 0;
    gap: 10px;
  }

  .lb-claim-modal .btn {
    border-radius: 999px;
  }

  [data-theme="light"] .lb-claim-modal .modal-content {
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 22px 60px rgba(0, 0, 0, .18);
  }

  [data-theme="light"] .lb-claim-card {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .08);
  }

  [data-theme="light"] .lb-claim-row+.lb-claim-row {
    border-top-color: rgba(0, 0, 0, .06);
  }

  /* =========================
   MOBILE CHAT + NOTIFY (kept)
========================= */
  .order-chat-card #chat_messages {
    -webkit-overflow-scrolling: touch;
  }

  .lb-msg__bubble,
  .lb-sys {
    word-break: break-word;
    overflow-wrap: anywhere;
    white-space: normal;
  }

  .order-chat-card .lb-notify-btn {
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: .25rem;
  }

  @media (max-width: 575.98px) {
    .order-chat-card{ margin-bottom: 1rem !important; }
    .order-chat-card .card-header {
      padding: .65rem .85rem !important;
    }

    .order-chat-card .lb-notify-btn {
      width: 42px;
      height: 42px;
      padding: 0 !important;
      justify-content: center;
    }

    .order-chat-card .lb-notify-text {
      display: none !important;
    }

    .order-chat-card .card-body.chat-bg {
      padding: .75rem !important;
    }

    .order-chat-card #chat_messages {
      max-height: 58vh !important;
      padding: 12px !important;
      gap: 10px !important;
    }

    .lb-msg {
      max-width: 100% !important;
    }

    .lb-msg__avatar {
      width: 30px !important;
      height: 30px !important;
    }

    .lb-msg__bubble {
      padding: .72rem .9rem !important;
      border-radius: 14px !important;
      max-width: 92%;
    }

    .lb-msg--end .lb-msg__bubble {
      max-width: 92%;
    }

    .order-chat-card .card-footer {
      padding: .65rem .85rem !important;
    }

    .order-chat-card .card-footer form.row {
      flex-wrap: nowrap !important;
      align-items: center;
    }

    .order-chat-card .card-footer .form-control {
      min-height: 44px;
      border-radius: 999px;
    }

    .order-chat-card .card-footer .btn {
      min-height: 44px;
      width: 44px;
      padding: 0 !important;
      border-radius: 999px;
      display: grid;
      place-items: center;
    }
  }

  @media (max-width: 380px) {
    .order-chat-card #chat_messages {
      max-height: 54vh !important;
    }
  }

  @media (max-width: 575.98px) {
    .lb-notify-btn--header {
      display: none !important;
    }

    .order-chat-card .card-footer form.row {
      flex-wrap: nowrap !important;
      align-items: center;
    }

    .order-chat-card .card-footer .form-control {
      min-height: 44px;
      border-radius: 999px;
    }

    .lb-notify-btn--mobile,
    .order-chat-card .card-footer button.btn {
      width: 44px;
      height: 44px;
      padding: 0 !important;
      border-radius: 999px;
      display: grid;
      place-items: center;
    }

    .lb-notify-btn--mobile i {
      font-size: 1.05rem;
    }
  }

  /* =========================
   COMPLETE MODAL (kept)
========================= */
  .lb-complete-modal .modal-content {
    background: #1f2226;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, .55);
  }

  .lb-complete-modal .modal-header {
    padding: 18px 18px 0 18px;
    border-bottom: 0;
  }

  .lb-complete-head {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
  }

  .lb-complete-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .18);
    box-shadow: 0 16px 45px rgba(0, 0, 0, .45);
  }

  .lb-complete-modal .modal-title {
    font-weight: 900;
    letter-spacing: .01em;
    margin: 0;
  }

  .lb-complete-sub {
    font-size: .86rem;
    opacity: .7;
    margin-top: 2px;
  }

  .lb-complete-modal .modal-body {
    padding: 12px 16px 8px 16px;
  }

  .lb-complete-card {
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 14px;
    padding: 10px 12px;
  }

  .lb-file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 12px;
    border-radius: 14px;
    border: 1px dashed rgba(255, 255, 255, .16);
    background: rgba(255, 255, 255, .03);
    cursor: pointer;
    transition: transform .12s ease, border-color .12s ease;
  }

  /* DROPZONE (Complete Order) */
  .lb-dropzone{
    flex-direction: column;
    text-align: center;
    padding: 12px 14px;
    gap: 8px;
  }

  .lb-dropzone__icon{
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .18);
  }

  .lb-dropzone__title{
    font-weight: 900;
    font-size: .92rem;
  }

  .lb-dropzone__sub{
    opacity: .78;
    font-size: .82rem;
    line-height: 1.3;
  }

  .lb-dropzone kbd{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .82em;
    padding: .12em .45em;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.16);
    background: rgba(0,0,0,.25);
  }

  [data-theme="light"] .lb-dropzone kbd{
    border-color: rgba(0,0,0,.14);
    background: rgba(0,0,0,.06);
  }

  .lb-dropzone__btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(124, 92, 255, .92);
    color: #fff;
    font-weight: 800;
    min-width: 140px;
  }

  .lb-dropzone__file{
    font-weight: 800;
    opacity: .9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .lb-dropzone.is-dragover{
    border-color: rgba(78, 161, 255, .60);
    background: rgba(78, 161, 255, .08);
    transform: translateY(-1px);
  }

  .lb-file:hover {
    transform: translateY(-1px);
    border-color: rgba(78, 161, 255, .35);
  }

  .lb-file .lb-file-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }

  .lb-file .lb-file-ico {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .18);
  }

  .lb-file .lb-file-meta {
    min-width: 0;
  }

  .lb-file .lb-file-k {
    font-size: .72rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    opacity: .65;
  }

  .lb-file .lb-file-v {
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 240px;
  }

  .lb-complete-pill {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    margin-top: 12px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    font-size: .88rem;
    opacity: .92;
  }

  .lb-pill-muted{
    display:block;
    margin-top: 4px;
    opacity: .75;
    font-size: .86em;
  }

  .lb-complete-preview {
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    overflow: hidden;
    background: rgba(0, 0, 0, .18);
  }

  .lb-complete-preview__inner{
    position: relative;
  }
  .lb-complete-preview__remove{
    position:absolute;
    right:10px;
    bottom:10px;
    border-radius:999px;
    backdrop-filter: blur(8px);
  }

  .lb-complete-modal .modal-footer {
    padding: 12px 18px 18px 18px;
    border-top: 0;
    gap: 10px;
  }

  .lb-complete-modal .btn {
    border-radius: 999px;
  }

  @media (max-width:575.98px) {
    .lb-file .lb-file-v {
      max-width: 160px;
    }
  }

  [data-theme="light"] .lb-complete-modal .modal-content {
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 22px 60px rgba(0, 0, 0, .18);
  }

  [data-theme="light"] .lb-complete-card {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .08);
  }

  [data-theme="light"] .lb-file {
    border-color: rgba(0, 0, 0, .14);
    background: rgba(0, 0, 0, .02);
  }

  /* BONUS WIN REMINDER (kept) */
  .lb-bonus-reminder {
    margin: .65rem 1rem 0 1rem;
    padding: .6rem .7rem;
    border-radius: 14px;
    border: 1px solid rgba(177, 140, 255, .18);
    background: rgba(177, 140, 255, .10);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .6rem;
    box-shadow: 0 18px 45px rgba(0, 0, 0, .35);
  }

  .lb-bonus-reminder__left {
    display: flex;
    align-items: center;
    gap: .6rem;
    min-width: 0;
  }

  .lb-bonus-reminder__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .18);
    flex: 0 0 auto;
  }

  .lb-bonus-reminder__txt {
    min-width: 0;
    line-height: 1.1;
  }

  .lb-bonus-reminder__k {
    font-size: .70rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .7;
  }

  .lb-bonus-reminder__q {
    font-weight: 900;
    font-size: .88rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-bonus-reminder__actions {
    display: flex;
    gap: .4rem;
    flex: 0 0 auto;
  }

  .lb-bonus-reminder .btn {
    border-radius: 999px;
    padding: .32rem .6rem;
  }

  @media (max-width:420px) {
    .lb-bonus-reminder {
      margin: .55rem .75rem 0 .75rem;
    }

    .lb-bonus-reminder__q {
      font-size: .84rem;
    }

    .lb-bonus-reminder__actions .btn {
      padding: .28rem .5rem;
    }
  }

  /* =========================
   DROP MODAL (kept)
========================= */
  .modal.lb-drop-modal {
    z-index: 20000;
  }

  .lb-drop-modal .modal-dialog {
    max-width: 760px;
  }

  .lb-drop-modal .modal-content {
    position: relative;
    background: #1f2226;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, .55);

    display: flex;
    flex-direction: column;

    max-height: calc(100vh - 2rem);
    max-height: calc(100svh - 2rem);
    max-height: calc(100dvh - 2rem);
  }

  .lb-drop-modal .modal-header {
    flex: 0 0 auto;
    padding: 14px 16px 10px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: #1f2226;
  }

  .lb-drop-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    width: 100%;
  }

  .lb-drop-icon {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 107, 107, .10);
    border: 1px solid rgba(255, 107, 107, .18);
    box-shadow: 0 16px 45px rgba(0, 0, 0, .45);
  }

  .lb-drop-modal .modal-title {
    font-weight: 900;
    margin: 0;
  }

  .lb-drop-sub {
    font-size: .86rem;
    opacity: .7;
    margin-top: 2px;
  }

  .lb-drop-modal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
    padding: 12px 16px;
    background: #1f2226;
  }

  .lb-drop-modal .modal-footer {
    flex: 0 0 auto;
    padding: 10px 16px calc(14px + env(safe-area-inset-bottom)) 16px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    background: rgba(31, 34, 38, .92);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    gap: 10px;
    position: relative;
    z-index: 5;
  }

  .lb-drop-modal .btn {
    border-radius: 999px;
  }

  .lb-drop-grid {
    display: grid;
    gap: 12px;
  }

  @media (min-width: 992px) {
    .lb-drop-grid {
      grid-template-columns: 1fr 1fr;
      align-items: start;
    }

    .lb-drop-modal .modal-dialog.modal-dialog-centered {
      align-items: flex-start;
      min-height: calc(100% - 3.5rem);
    }

    .lb-drop-modal .modal-content {
      max-height: calc(100vh - 6rem);
      max-height: calc(100svh - 6rem);
      max-height: calc(100dvh - 6rem);
    }
  }

  .lb-drop-card {
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 14px;
    padding: 12px;
  }

  .lb-drop-card-title {
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .7;
    margin-bottom: 10px;
  }


  /* Custom dropdown for Drop Reason */
  .order-drop-modal .lb-native-reason-select{
    position:absolute !important;
    width:1px !important;
    height:1px !important;
    padding:0 !important;
    margin:0 !important;
    border:0 !important;
    opacity:0 !important;
    pointer-events:none !important;
  }

  .order-drop-modal .lb-custom-select{
    position:relative;
    z-index:25;
  }

  .order-drop-modal .lb-custom-select__button{
    width:100%;
    min-height:44px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    border:1px solid rgba(255,255,255,.09);
    border-radius:10px;
    background:rgba(12,14,17,.46);
    color:rgba(255,255,255,.70);
    padding:0 12px 0 14px;
    font-weight:600;
    text-align:left;
    transition:border-color .16s ease, background .16s ease, box-shadow .16s ease, color .16s ease;
  }

  .order-drop-modal .lb-custom-select.has-value .lb-custom-select__button{
    color:rgba(255,255,255,.92);
  }

  .order-drop-modal .lb-custom-select__button:hover,
  .order-drop-modal .lb-custom-select.is-open .lb-custom-select__button{
    background:rgba(255,255,255,.055);
    border-color:rgba(124,92,255,.45);
    box-shadow:0 0 0 3px rgba(124,92,255,.10);
  }

  .order-drop-modal .lb-custom-select.is-invalid .lb-custom-select__button{
    border-color:rgba(255,70,130,.65);
    box-shadow:0 0 0 3px rgba(255,70,130,.12);
  }

  .order-drop-modal .lb-custom-select__label{
    min-width:0;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
  }

  .order-drop-modal .lb-custom-select__chev{
    width:28px;
    height:28px;
    flex:0 0 28px;
    display:grid;
    place-items:center;
    border-radius:9px;
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.62);
    transition:transform .16s ease, color .16s ease, background .16s ease;
  }

  .order-drop-modal .lb-custom-select.is-open .lb-custom-select__chev{
    transform:rotate(180deg);
    color:#fff;
    background:rgba(124,92,255,.18);
  }

  .order-drop-modal .lb-custom-select__menu{
    position:absolute;
    left:0;
    right:0;
    top:calc(100% + 8px);
    display:none;
    padding:8px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.10);
    background:#171a1f;
    box-shadow:0 22px 55px rgba(0,0,0,.50);
    z-index:70;
    max-height:260px;
    overflow-y:auto;
  }

  .order-drop-modal .lb-custom-select.is-open .lb-custom-select__menu{
    display:block;
    animation:lbSelectIn .14s ease both;
  }

  .order-drop-modal .lb-custom-select__option{
    width:100%;
    display:flex;
    align-items:center;
    gap:10px;
    border:0;
    border-radius:10px;
    background:transparent;
    color:rgba(255,255,255,.82);
    padding:10px 10px;
    text-align:left;
    font-weight:650;
    line-height:1.18;
    transition:background .14s ease, color .14s ease, transform .14s ease;
  }

  .order-drop-modal .lb-custom-select__option:hover,
  .order-drop-modal .lb-custom-select__option:focus{
    outline:0;
    background:rgba(124,92,255,.16);
    color:#fff;
  }

  .order-drop-modal .lb-custom-select__option.is-selected{
    background:rgba(42,220,140,.12);
    color:#fff;
  }

  .order-drop-modal .lb-custom-select__dot{
    width:8px;
    height:8px;
    border-radius:999px;
    flex:0 0 8px;
    background:rgba(255,255,255,.24);
    box-shadow:0 0 0 4px rgba(255,255,255,.035);
  }

  .order-drop-modal .lb-custom-select__option.is-selected .lb-custom-select__dot{
    background:#2adc8c;
    box-shadow:0 0 0 4px rgba(42,220,140,.12);
  }

  @keyframes lbSelectIn{
    from{opacity:0; transform:translateY(-5px) scale(.985);}
    to{opacity:1; transform:translateY(0) scale(1);}
  }

  [data-theme="light"] .order-drop-modal .lb-custom-select__button{
    background:rgba(0,0,0,.035);
    border-color:rgba(0,0,0,.10);
    color:rgba(0,0,0,.62);
  }

  [data-theme="light"] .order-drop-modal .lb-custom-select.has-value .lb-custom-select__button{
    color:rgba(0,0,0,.86);
  }

  [data-theme="light"] .order-drop-modal .lb-custom-select__menu{
    background:#fff;
    border-color:rgba(0,0,0,.10);
    box-shadow:0 22px 55px rgba(0,0,0,.16);
  }

  [data-theme="light"] .order-drop-modal .lb-custom-select__option{
    color:rgba(0,0,0,.72);
  }

  [data-theme="light"] .order-drop-modal .lb-custom-select__option:hover,
  [data-theme="light"] .order-drop-modal .lb-custom-select__option:focus,
  [data-theme="light"] .order-drop-modal .lb-custom-select__option.is-selected{
    color:rgba(0,0,0,.90);
  }

  .lb-drop-modal textarea.form-control {
    min-height: 110px;
    resize: vertical;
  }

  .lb-drop-switch {
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  .lb-drop-hint {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid rgba(177, 140, 255, .18);
    background: rgba(177, 140, 255, .10);
    font-size: .88rem;
    opacity: .92;
  }

  .lb-drop-hint i {
    margin-top: 2px;
  }

  @media (max-width: 575.98px) {
    .lb-drop-modal .modal-dialog {
      margin: .75rem;
    }

    .lb-drop-modal .modal-header {
      padding: 12px 12px 8px 12px;
    }

    .lb-drop-modal .modal-body {
      padding: 10px 12px;
    }

    .lb-drop-modal .modal-footer {
      padding: 10px 12px calc(14px + env(safe-area-inset-bottom)) 12px;
    }

    .lb-drop-modal .modal-content {
      max-height: calc(100vh - 1.5rem);
      max-height: calc(100svh - 1.5rem);
      max-height: calc(100dvh - 1.5rem);
    }
  }

  [data-theme="light"] .lb-drop-modal .modal-content {
    background: #fff;
    border-color: rgba(0, 0, 0, .08);
    box-shadow: 0 22px 60px rgba(0, 0, 0, .18);
  }

  [data-theme="light"] .lb-drop-modal .modal-header,
  [data-theme="light"] .lb-drop-modal .modal-body {
    background: #fff;
  }

  [data-theme="light"] .lb-drop-card {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .08);
  }

  [data-theme="light"] .lb-drop-modal .modal-footer {
    background: rgba(255, 255, 255, .92);
    border-top-color: rgba(0, 0, 0, .08);
  }

  /* DROP WIZARD steps (kept) */
  .lb-drop-step {
    display: none;
  }

  .lb-drop-step.is-active {
    display: block;
  }

  .lb-drop-steps {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .lb-step-pill {
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .75);
    font-weight: 800;
    letter-spacing: .10em;
    font-size: .68rem;
    padding: 6px 10px;
    border-radius: 999px;
  }

  .lb-step-pill.is-active {
    background: rgba(78, 161, 255, .12);
    border-color: rgba(78, 161, 255, .25);
    color: #fff;
  }

  /* Modal: nie höher als Viewport (fix: Scroll immer möglich) */
  .order-drop-modal .modal-dialog {
    margin: 1rem auto;
    height: calc(100vh - 2rem);
    height: calc(100dvh - 2rem);
    height: calc(100svh - 2rem);
  }

  .order-drop-modal .modal-content {
    max-height: calc(100vh - 2rem);
    max-height: calc(100dvh - 2rem);
    max-height: calc(100svh - 2rem);
    height: 100%;
    overflow: hidden;
    border-radius: 18px;
  }

  .order-drop-modal { --lb-drop-footer-h: 110px; }

  .order-drop-modal .modal-body {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    min-height: 0;
    padding-bottom: var(--lb-drop-footer-h);
  }

  /* Inline validation alert (prettier than default bootstrap red) */
  .order-drop-modal .lb-drop-inline-alert{
    border-radius: 14px;
    border: 1px solid rgba(255, 70, 130, .28);
    background: rgba(255, 70, 130, .12);
    color: rgba(255,255,255,.92);
    padding: .75rem .85rem;
    box-shadow: 0 10px 24px rgba(0,0,0,.25);
  }

  /* In-modal toast (used for missing-fields feedback; avoids browser alert popups) */
  .order-drop-modal .lb-modal-toast-wrap{
    position: absolute;
    top: 74px;
    right: 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 5;
    pointer-events: none;
    max-width: min(420px, calc(100% - 36px));
  }

  .order-drop-modal .lb-modal-toast{
    border-radius: 14px;
    border: 1px solid rgba(255, 70, 130, .28);
    background: rgba(255, 70, 130, .12);
    padding: .70rem .85rem;
    box-shadow: 0 14px 34px rgba(0,0,0,.35);
    transform: translateY(-10px);
    opacity: 0;
    transition: transform .18s ease, opacity .18s ease;
  }

  .order-drop-modal .lb-modal-toast.is-in{
    transform: translateY(0);
    opacity: 1;
  }

  .order-drop-modal .lb-toast-title{
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: .72rem;
    opacity: .92;
    margin-bottom: .15rem;
  }

  .order-drop-modal .lb-toast-msg{
    font-size: .92rem;
    line-height: 1.25;
    opacity: .95;
  }

  /* Subtle shake on invalid next */
  .order-drop-modal .lb-shake{
    animation: lbShake .32s ease-in-out;
  }
  @keyframes lbShake{
    0%{ transform: translateX(0); }
    20%{ transform: translateX(-6px); }
    40%{ transform: translateX(6px); }
    60%{ transform: translateX(-4px); }
    80%{ transform: translateX(4px); }
    100%{ transform: translateX(0); }
  }

  /* Assignment Date should look like normal input (theme sometimes greys out readonly fields) */
  #dropAssignmentDate[readonly]{
    background: rgba(255,255,255,0.04);
    color: inherit;
    opacity: 1;
    pointer-events: auto;
    cursor: pointer;
  }

  /* Mobile: etwas tighter */
  @media (max-width: 576px) {
    .order-drop-modal .modal-dialog {
      margin: .75rem;
      height: calc(100vh - 1.5rem);
      height: calc(100dvh - 1.5rem);
      height: calc(100svh - 1.5rem);
    }

    .order-drop-modal .modal-content {
      max-height: calc(100vh - 1.5rem);
      max-height: calc(100dvh - 1.5rem);
      max-height: calc(100svh - 1.5rem);
      height: 100%;
      overflow: hidden;
      border-radius: 16px;
    }
  }

  /* YES / NO chip like client dashboard */
  .lb-yes-pill,
  .lb-no-pill {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .28rem .70rem;
    border-radius: 999px;
    font-weight: 950;
    font-size: .70rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
  }

  .lb-yes-pill {
    color: #1fe6c6;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .22);
  }

  .lb-yes-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #1fe6c6;
    box-shadow: 0 0 0 3px rgba(31, 230, 198, .12);
  }

  .lb-no-pill {
    color: #ff6b6b;
    background: rgba(255, 107, 107, .10);
    border: 1px solid rgba(255, 107, 107, .22);
  }

  .lb-no-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, .12);
  }

  /* inline layout (label links, chip rechts) */
  .lb-ov-item--inline {
    grid-template-rows: auto !important;
    align-items: center;
  }

  .lb-ov-item--inline .lb-ov-ico {
    grid-row: 1 !important;
  }

  .lb-ov-item--inline .lb-ov-label-row,
  .lb-ov-item--inline .lb-ov-label {
    grid-row: 1 !important;
    align-self: center;
  }

  .lb-ov-item--inline .lb-ov-desc {
    display: none;
  }

  .lb-ov-item--inline .lb-ov-value {
    grid-row: 1 !important;
    justify-content: flex-end !important;
    text-align: right;
    margin-left: auto;
  }

  .lb-ov-item--option.lb-ov-item--inline {
    grid-template-rows: auto auto !important;
    align-items: center;
  }

  .lb-ov-item--option.lb-ov-item--inline .lb-ov-ico {
    grid-row: 1 / span 2 !important;
  }

  .lb-ov-item--option.lb-ov-item--inline .lb-ov-label-row {
    grid-row: 1 !important;
  }

  .lb-ov-item--option.lb-ov-item--inline .lb-ov-desc {
    display: block;
    grid-row: 2 !important;
  }

  .lb-ov-item--option.lb-ov-item--inline .lb-ov-value {
    grid-column: 3 !important;
    grid-row: 1 / span 2 !important;
    margin-left: auto;
    justify-content: flex-end !important;
    text-align: right;
  }



  /* FIX: Roles / Champions / Agents need their own wrapped icon layout.
     This must stay AFTER the generic option/inline rules, otherwise
     .lb-ov-item--option .lb-ov-value forces the icons into the tiny right column. */
  .lb-ov-item--option.lb-ov-item--icons {
    grid-template-columns: 44px minmax(0, 1fr) !important;
    grid-template-rows: auto auto auto !important;
    align-items: start !important;
    overflow: visible !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-ico {
    grid-column: 1 !important;
    grid-row: 1 / span 3 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label-row,
  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label {
    grid-column: 2 !important;
    grid-row: 1 !important;
    min-width: 0 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-desc {
    display: block !important;
    grid-column: 2 !important;
    grid-row: 2 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-value {
    grid-column: 2 !important;
    grid-row: 3 !important;
    justify-self: start !important;
    align-self: start !important;
    justify-content: flex-start !important;
    width: 100% !important;
    max-width: 100% !important;
    margin-left: 0 !important;
    padding-top: .38rem !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: .35rem !important;
    white-space: normal !important;
    overflow: visible !important;
    text-align: left !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-value img,
  .lb-ov-item--option.lb-ov-item--icons .lb-ov-value img.rounded-circle {
    width: 24px !important;
    height: 24px !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    border-radius: 50% !important;
    object-fit: cover !important;
  }



  /* ALIGN FIX: keep icon options on the right like YES/NO pills, but allow wrapping. */
  .lb-ov-item--option.lb-ov-item--icons {
    grid-template-columns: 44px minmax(0, 1fr) minmax(160px, auto) !important;
    grid-template-rows: auto auto !important;
    align-items: center !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-ico {
    grid-column: 1 !important;
    grid-row: 1 / span 2 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label-row,
  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label {
    grid-column: 2 !important;
    grid-row: 1 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-desc {
    grid-column: 2 !important;
    grid-row: 2 !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-value {
    grid-column: 3 !important;
    grid-row: 1 / span 2 !important;
    justify-self: end !important;
    align-self: center !important;
    justify-content: flex-end !important;
    align-items: center !important;
    width: auto !important;
    max-width: 330px !important;
    margin-left: auto !important;
    padding-top: 0 !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: .35rem !important;
    text-align: right !important;
  }

  @media (max-width: 575.98px) {
    .lb-ov-item--option.lb-ov-item--icons {
      grid-template-columns: 44px minmax(0, 1fr) !important;
      grid-template-rows: auto auto auto !important;
    }

    .lb-ov-item--option.lb-ov-item--icons .lb-ov-value {
      grid-column: 2 !important;
      grid-row: 3 !important;
      justify-self: start !important;
      justify-content: flex-start !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      padding-top: .38rem !important;
      text-align: left !important;
    }
  }



  /* BALANCE FIX: keep long option titles on one line and make icon groups wrap evenly. */
  .lb-ov-item--option.lb-ov-item--icons {
    grid-template-columns: 44px minmax(210px, 1fr) auto !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label,
  .lb-ov-item--option.lb-ov-item--icons .lb-ov-label-row {
    white-space: nowrap !important;
  }

  .lb-ov-item--option.lb-ov-item--icons .lb-ov-value {
    max-width: 150px !important;
    min-width: 0 !important;
  }

  .lb-ov-item--option.lb-option-champions .lb-ov-value,
  .lb-ov-item--option.lb-option-agents .lb-ov-value {
    display: grid !important;
    grid-template-columns: repeat(5, 24px) !important;
    grid-auto-rows: 24px !important;
    gap: .35rem !important;
    justify-content: end !important;
    justify-items: center !important;
    align-items: center !important;
    width: max-content !important;
    max-width: 150px !important;
  }

  .lb-ov-item--option.lb-option-roles .lb-ov-value {
    max-width: 150px !important;
  }

  @media (max-width: 575.98px) {
    .lb-ov-item--option.lb-ov-item--icons {
      grid-template-columns: 44px minmax(0, 1fr) !important;
    }

    .lb-ov-item--option.lb-ov-item--icons .lb-ov-label,
    .lb-ov-item--option.lb-ov-item--icons .lb-ov-label-row {
      white-space: normal !important;
    }

    .lb-ov-item--option.lb-option-champions .lb-ov-value,
    .lb-ov-item--option.lb-option-agents .lb-ov-value {
      justify-content: start !important;
    }
  }

  /* =========================
   DROP MODAL: Mobile Fullscreen Wizard (fix clipping)
========================= */

  /* desktop remains nice */
  #request_drop_md .modal-dialog {
    max-width: 760px;
  }

  /* Make modal content a flex column so body can scroll and footer stays visible */
  #request_drop_md .modal-content {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 2rem);
    max-height: calc(100svh - 2rem);
    max-height: calc(100dvh - 2rem);
  }

  #request_drop_md .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
  }

  #request_drop_md .modal-footer {
    flex: 0 0 auto;
    position: sticky;
    bottom: 0;
    z-index: 5;
    padding-bottom: calc(14px + env(safe-area-inset-bottom));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
  }

  /* Mobile: FULLSCREEN */
  @media (max-width: 575.98px) {
    #request_drop_md .modal-dialog {
      margin: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      height: 100vh;
      height: 100svh;
      height: 100dvh;
    }

    #request_drop_md .modal-content {
      height: 100vh;
      height: 100svh;
      height: 100dvh;
      max-height: none !important;
      border-radius: 0 !important;
    }

    #request_drop_md .modal-header {
      padding: 12px 12px 8px 12px !important;
    }

    #request_drop_md .modal-body {
      padding: 10px 12px !important;
    }

    #request_drop_md .modal-footer {
      padding: 10px 12px calc(14px + env(safe-area-inset-bottom)) 12px !important;
      border-top: 1px solid rgba(255, 255, 255, .06);
      background: rgba(31, 34, 38, .92);
    }
  }

  /* Step pills: clickable and consistent */
  #request_drop_md .lb-step-pill {
    cursor: pointer;
  }

  /* =========================
   SECRET / MASKED VALUES (Account) — client-like
========================= */
  .lb-ov-item--reveal .lb-ov-value {
    justify-content: flex-end;
    width: 100%;
  }

  .lb-missing {
    display: inline-flex;
    align-items: center;
    padding: .30rem .65rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .78rem;
    letter-spacing: .02em;
    color: rgba(255, 255, 255, .70);
    border: 1px dashed rgba(255, 255, 255, .14);
    background: rgba(255, 255, 255, .03);
  }

  .lb-secret {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .48rem .6rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .90);
    font-weight: 950;
    cursor: pointer;
    text-align: left;
  }

  .lb-secret:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .14);
  }

  .lb-secret__text {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    opacity: .9;
    font-size: .92rem;
  }

  .lb-secret__eye {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .18);
    flex: 0 0 auto;
  }

  [data-theme="light"] .lb-missing {
    color: rgba(0, 0, 0, .55);
    border-color: rgba(0, 0, 0, .14);
    background: rgba(0, 0, 0, .03);
  }

  [data-theme="light"] .lb-secret {
    border-color: rgba(0, 0, 0, .10);
    background: rgba(0, 0, 0, .03);
    color: rgba(0, 0, 0, .78);
  }

  [data-theme="light"] .lb-secret__eye {
    border-color: rgba(0, 0, 0, .10);
    background: #fff;
  }

  /* Account: compact secret field + buttons (eye/copy) */
  .lb-ov-item--secret .lb-ov-value {
    width: 100%;
  }

  .lb-secret-field {
    width: 100%;
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .45rem .55rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(0, 0, 0, .14);
    min-width: 0;
  }

  .lb-secret-text {
    flex: 1 1 auto;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 950;
    font-size: .92rem;
    opacity: .92;
  }

  .lb-secret-btn {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    display: grid;
    place-items: center;
    color: rgba(255, 255, 255, .85);
    flex: 0 0 auto;
    transition: .12s ease;
  }

  .lb-secret-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(78, 161, 255, .28);
    background: rgba(78, 161, 255, .10);
  }

  .lb-secret-field.is-revealed .lb-secret-btn.lb-secret-eye {
    border-color: rgba(31, 230, 198, .28);
    background: rgba(31, 230, 198, .10);
  }

  /* Missing: more obvious */
  .lb-ov-item--missing {
    border-color: rgba(255, 107, 107, .25) !important;
    background: rgba(255, 107, 107, .06) !important;
  }

  .lb-missing-pill {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-weight: 950;
    font-size: .78rem;
    letter-spacing: .02em;
    color: #ff9a9a;
    border: 1px solid rgba(255, 107, 107, .28);
    background: rgba(255, 107, 107, .10);
    box-shadow: 0 16px 40px rgba(0, 0, 0, .25);
  }

  .lb-missing-pill i {
    opacity: .95;
  }

  /* Mobile: less wide / tighter */
  @media (max-width:575.98px) {
    .lb-secret-field {
      padding: .40rem .50rem;
    }

    .lb-secret-btn {
      width: 32px;
      height: 32px;
    }
  }

  /* Light theme */
  [data-theme="light"] .lb-secret-field {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-secret-btn {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }

  [data-theme="light"] .lb-ov-item--missing {
    background: rgba(255, 107, 107, .08) !important;
    border-color: rgba(255, 107, 107, .22) !important;
  }

  [data-theme="light"] .lb-missing-pill {
    color: #b93838;
    background: rgba(255, 107, 107, .12);
    border-color: rgba(255, 107, 107, .28);
  }

  .lb-account-alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .85rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 107, 107, .25);
    background: rgba(255, 107, 107, .08);
    box-shadow: 0 18px 45px rgba(0, 0, 0, .25);
  }

  .lb-account-alert__left {
    display: flex;
    align-items: center;
    gap: .7rem;
    min-width: 0;
  }

  .lb-account-alert__ico {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 107, 107, .14);
    border: 1px solid rgba(255, 107, 107, .25);
  }

  .lb-account-alert__txt {
    min-width: 0;
    line-height: 1.15;
  }

  .lb-account-alert__title {
    font-weight: 950;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: .72rem;
    opacity: .9;
  }

  .lb-account-alert__sub {
    font-weight: 800;
    font-size: .9rem;
    opacity: .85;
  }

  .lb-account-alert__btn {
    border-radius: 999px;
    white-space: nowrap;
  }

  @media (max-width:575.98px) {
    .lb-account-alert {
      flex-direction: column;
      align-items: stretch;
    }

    .lb-account-alert__btn {
      width: 100%;
    }
  }

  /* =========================
   STATE ALERT (Paused etc.) — client-like
========================= */
  .lb-state-alert {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .85rem;
    padding: .85rem .95rem;
    border-radius: 18px;
    border: 1px solid rgba(255, 196, 77, .22);
    background: rgba(255, 196, 77, .10);
    box-shadow: 0 18px 45px rgba(0, 0, 0, .25);
  }

  .lb-state-alert__left {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    min-width: 0;
  }

  .lb-state-alert__ico {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 196, 77, .16);
    border: 1px solid rgba(255, 196, 77, .25);
    flex: 0 0 auto;
  }

  .lb-state-alert__txt {
    min-width: 0;
    line-height: 1.15;
  }

  .lb-state-alert__title {
    font-weight: 950;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: .72rem;
    opacity: .9;
  }

  .lb-state-alert__sub {
    font-weight: 800;
    font-size: .92rem;
    opacity: .85;
    margin-top: .2rem;
  }

  /* Light theme */
  [data-theme="light"] .lb-state-alert {
    border-color: rgba(255, 196, 77, .35);
    background: rgba(255, 196, 77, .14);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
  }

    /* Success variant (Waiting for approval) */
  .lb-state-alert--success{
    border-color: rgba(34, 197, 94, .28) !important;
    background: rgba(34, 197, 94, .10) !important;
  }

  .lb-state-alert--success .lb-state-alert__ico{
    background: rgba(34, 197, 94, .16) !important;
    border-color: rgba(34, 197, 94, .28) !important;
  }

  
  /* Non-clickable but fully visible dropdown item */
  .lb-dropdown-static{
    pointer-events: none;
    cursor: default;
    opacity: 1 !important;
    color: inherit;
  }
[data-theme="light"] .lb-state-alert--success{
    border-color: rgba(34, 197, 94, .30) !important;
    background: rgba(34, 197, 94, .12) !important;
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
  }

[data-theme="light"] .lb-state-alert__ico {
    background: rgba(255, 196, 77, .18);
    border-color: rgba(255, 196, 77, .35);
  }

  /* Order Actions button = pill/rounded */
  .lb-action-btn {
    border-radius: 999px !important;
    padding: .48rem .85rem;
  }

  .lb-action-btn--icon {
    width: 42px;
    height: 42px;
    padding: 0 !important;
    border-radius: 999px !important;
    display: grid !important;
    place-items: center;
  }

  .star i {
    font-size: 20px;
  }

  .filled-stars,
  .empty-stars {
    display: flex;
    gap: 5px;
  }

  .filled-stars .star,
  .empty-stars .star {
    border: 1px solid #ffffff14;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    border-radius: 10px;
  }

  .rating-container .filled-stars {
    -webkit-text-stroke: 0;
    text-shadow: none;
    color: #ffc44d;
  }

  .rating-container .rating-stars:focus {
    outline: none;
  }

  .highlights .rounded-pill {
    border-radius: 9999px;
    padding: 6px 12px;
    background: #ffffff0d;
    border: 1px solid #ffffff1a;
    color: #ffffffb3;
    white-space: nowrap;
  }

  .highlights .rounded-pill,
  .highlights .rounded-pill:hover,
  .highlights .rounded-pill:focus,
  .highlights .rounded-pill:focus-visible,
  .highlights .rounded-pill:active,
  .highlights .show>.rounded-pill {
    background: #ffffff0d !important;
    border-color: #ffffff1a !important;
    color: #ffffffb3 !important;
    box-shadow: none !important;
    outline: none !important;
  }

  .highlights .rounded-pill:hover {
    background: #ffffff14 !important;
    border-color: #ffffff26 !important;
    color: #ffffffe6 !important;
  }

  .highlights .rounded-pill.active,
  .highlights .btn-check:checked+.rounded-pill,
  .highlights .btn-check:active+.rounded-pill {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    border-color: #6366f1 !important;
    color: #ffffff !important;
    box-shadow:
      0 0 0 1px #6366f1 inset,
      0 6px 16px rgba(99, 102, 241, 0.35),
      0 0 18px rgba(99, 102, 241, 0.45) !important;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.25);
  }

  .highlights .rounded-pill {
    transition: all .18s ease;
  }

  .btn-check:disabled+.btn,
  .btn-check[disabled]+.btn {
    opacity: 1 !important;
    cursor: not-allowed !important;
  }

  /* --- Booster chat edit --- */
  .lb-msg__bubble{ position: relative; padding-right: 46px; }
  .lb-msg__content{ width: 100%; }
  .lb-msg__edit{
    position:absolute;
    top:8px;
    right:8px;
    width:28px;
    height:28px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.90);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    opacity:0;
    transition:.12s ease;
    z-index: 50;
    pointer-events: auto;
  }
  .lb-msg__bubble:hover .lb-msg__edit{ opacity:1; }
  @media (hover:none) and (pointer:coarse){ .lb-msg__edit{ opacity:1; } }

  .lb-msg__edited{
    display:inline-block;
    margin-left:6px;
    padding:2px 8px;
    border-radius:999px;
    font-size:.68rem;
    line-height:1.2;
    opacity:.85;
    border:1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.06);
  }

.lb-msg__stamp{
    font-size:11px;
    color:rgba(255,255,255,.45);
    margin-top:4px;
    padding:0 6px;
}
.lb-msg--end .lb-msg__stamp{ text-align:right; }
.lb-msg--start .lb-msg__stamp{ text-align:left; }

  .lb-msg__editor{
    width:100%;
    min-height:72px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.92);
    padding:10px 12px;
    outline:none;
    resize: vertical;
  }
  .lb-msg__edit-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:10px;
  }
  .lb-msg__edit-cancel,
  .lb-msg__edit-save{
    border:1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.92);
    border-radius:10px;
    padding:6px 10px;
    cursor:pointer;
  }
  .lb-msg__edit-save{ background: rgba(124,92,255,.20); border-color: rgba(124,92,255,.30); }

.boost-form-svg {
  filter: brightness(0) invert(1);
}

/* Chat locked (Order completed) */
.lb-chat-locked-footer{
  display:flex;
  align-items:center;
  gap:.85rem;
  padding:.85rem 1rem;
  border-radius:16px;
  border:1px dashed rgba(255,255,255,.18);
  background: rgba(255,255,255,.06);
  backdrop-filter: blur(6px);
}
[data-theme="light"] .lb-chat-locked-footer{
  border-color: rgba(0,0,0,.14);
  background: rgba(0,0,0,.04);
}
.lb-chat-locked-footer__icon{
  width:36px;
  height:36px;
  display:grid;
  place-items:center;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.14);
  background: rgba(255,255,255,.08);
}
[data-theme="light"] .lb-chat-locked-footer__icon{
  border-color: rgba(0,0,0,.12);
  background: rgba(0,0,0,.06);
}
.lb-chat-locked-footer__title{
  font-weight:750;
  line-height:1.1;
}
.lb-chat-locked-footer__sub{
  font-size:.9rem;
  opacity:.85;
  margin-top:2px;
  line-height:1.2;
}
.order-chat-card .card-footer .form-control:disabled{
  cursor: not-allowed;
}
.order-chat-card .card-footer button.btn:disabled{
  cursor: not-allowed;
}



  /* =========================
     DROP MODAL: auto progress notice
  ========================= */
  #dropAutoProgressCard .lb-auto-progress-card{
    position:relative;
    overflow:hidden;
    border-radius:14px;
    padding:14px 16px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(18,20,23,.72);
    color:rgba(255,255,255,.92);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
  }
  #dropAutoProgressCard .lb-auto-progress-card::before{
    content:"";
    position:absolute;
    inset:0 auto 0 0;
    width:4px;
    background:rgba(99,102,241,.85);
  }
  #dropAutoProgressCard .lb-auto-progress-icon{
    width:34px;
    height:34px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex:0 0 auto;
    background:rgba(99,102,241,.12);
    border:1px solid rgba(99,102,241,.22);
    color:#a5b4fc;
  }
  #dropAutoProgressCard #dropAutoProgressText{
    color:rgba(255,255,255,.58);
    line-height:1.35;
    margin-top:2px;
  }
  #dropAutoProgressCard .lb-auto-progress-card--loading .lb-auto-progress-icon i{
    animation:lbAutoProgressSpin .9s linear infinite;
  }
  #dropAutoProgressCard .lb-auto-progress-card--ready{
    border-color:rgba(34,197,94,.26);
    background:linear-gradient(135deg, rgba(34,197,94,.12), rgba(18,20,23,.78));
  }
  #dropAutoProgressCard .lb-auto-progress-card--ready::before{
    background:rgba(34,197,94,.95);
  }
  #dropAutoProgressCard .lb-auto-progress-card--ready .lb-auto-progress-icon{
    background:rgba(34,197,94,.13);
    border-color:rgba(34,197,94,.24);
    color:#86efac;
  }
  #dropAutoProgressCard .lb-auto-progress-card--unavailable{
    border-color:rgba(245,158,11,.26);
    background:linear-gradient(135deg, rgba(245,158,11,.10), rgba(18,20,23,.78));
  }
  #dropAutoProgressCard .lb-auto-progress-card--unavailable::before{
    background:rgba(245,158,11,.95);
  }
  #dropAutoProgressCard .lb-auto-progress-card--unavailable .lb-auto-progress-icon{
    background:rgba(245,158,11,.13);
    border-color:rgba(245,158,11,.24);
    color:#fbbf24;
  }
  #dropAutoProgressCard .lb-auto-progress-card--unavailable #dropAutoProgressText{
    color:rgba(255,255,255,.64);
  }
  @keyframes lbAutoProgressSpin{
    from{transform:rotate(0deg)}
    to{transform:rotate(360deg)}
  }
  [data-theme="light"] #dropAutoProgressCard .lb-auto-progress-card{
    background:linear-gradient(135deg, rgba(99,102,241,.08), rgba(248,250,252,.98));
    border-color:rgba(15,23,42,.10);
    color:#111827;
    box-shadow:none;
  }
  [data-theme="light"] #dropAutoProgressCard #dropAutoProgressText{
    color:rgba(15,23,42,.60);
  }
  [data-theme="light"] #dropAutoProgressCard .lb-auto-progress-card--ready{
    background:linear-gradient(135deg, rgba(34,197,94,.10), rgba(248,250,252,.98));
    border-color:rgba(34,197,94,.22);
  }
  [data-theme="light"] #dropAutoProgressCard .lb-auto-progress-card--unavailable{
    background:linear-gradient(135deg, rgba(245,158,11,.12), rgba(248,250,252,.98));
    border-color:rgba(245,158,11,.25);
  }

  /* =========================
     DROP MODAL: progress choice cards
  ========================= */
  .lb-progress-choice{
    display:block;
    width:100%;
    padding:.9rem 1rem;
    border-radius:.9rem;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.02);
    cursor:pointer;
    transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
  }
  .lb-progress-choice:hover{
    background:rgba(255,255,255,.04);
  }
  .lb-progress-icon{
    width:1.75rem;
    height:1.75rem;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:.6rem;
    background:rgba(255,255,255,.05);
    flex:0 0 auto;
    margin-top:.05rem;
  }
  .btn-check:focus + .lb-progress-choice{
    box-shadow:0 0 0 .2rem rgba(99,102,241,.15);
  }
  #dropHasProgress:checked + .lb-progress-ok{
    border-color:rgba(34,197,94,.65);
    background:rgba(34,197,94,.08);
  }
  #dropHasProgress:checked + .lb-progress-ok .lb-progress-icon{
    background:rgba(34,197,94,.12);
  }
  #dropNoProgress:checked + .lb-progress-zero{
    border-color:rgba(239,68,68,.7);
    background:rgba(239,68,68,.08);
  }
  #dropNoProgress:checked + .lb-progress-zero .lb-progress-icon{
    background:rgba(239,68,68,.12);
  }


/* Chat grace period (24h after completion) */
.lb-chat-grace-banner{
  display:flex;
  align-items:flex-start;
  gap:.75rem;
  padding:.75rem .9rem;
  border-radius:14px;
  margin-bottom:.75rem;
  background: rgba(72, 187, 120, .10);
  border: 1px solid rgba(72, 187, 120, .22);
}
.lb-chat-grace-banner__icon{
  width:34px;
  height:34px;
  display:grid;
  place-items:center;
  border-radius:10px;
  background: rgba(72, 187, 120, .14);
  color: rgba(72, 187, 120, .95);
  flex:0 0 auto;
}
.lb-chat-grace-banner__title{
  font-weight:700;
  font-size:.95rem;
  line-height:1.1;
}
.lb-chat-grace-banner__sub{
  margin-top:.15rem;
  color: rgba(255,255,255,.78);
  font-size:.88rem;
  line-height:1.25;
}
.lb-chat-grace-countdown{
  font-weight:700;
  color: rgba(255,255,255,.92);
}

  /* =========================
     CHAT INPUT ACTION BUTTONS + IMAGE PREVIEW
  ========================= */
  .lb-chat-form #lbChatMessage{
    min-height: 44px;
  }
  .lb-chat-actions .btn-chat-icon{
    width: 40px;
    height: 40px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    line-height: 1;
  }
  .lb-chat-actions .btn-chat-icon i,
  .lb-chat-actions .btn-chat-icon svg{
    font-size: 16px;
    width: 16px;
    height: 16px;
  }
  
@media (max-width: 576px){
  /* Mobile: input + action buttons in ONE row (full-width input, buttons always visible) */
  #lbChatForm{
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    gap: 10px;

    /* Neutralize Bootstrap .row gutters/negative margins */
    --bs-gutter-x: 0 !important;
    --bs-gutter-y: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }

  /* Remove bootstrap column padding so we don't lose width */
  #lbChatForm > [class*="col-"]{
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin: 0 !important;
  }

  /* Message column must grow; make it a flex container so input can flex */
  #lbChatForm > .col-12{
    width: auto !important;
  }
  #lbChatForm > .col-12.col-sm{
    flex: 1 1 0% !important;
    min-width: 0 !important;
    display: flex !important;
    align-items: center !important;
  }

  /* Actions column stays compact */
  #lbChatForm > .col-12.col-sm-auto{
    flex: 0 0 auto !important;
    width: auto !important;
    max-width: max-content !important;
    display: flex !important;
    align-items: center !important;
  }

  /* Input takes all remaining width */
  #lbChatForm #lbChatMessage{
    flex: 1 1 0% !important;
    min-width: 0 !important;
    width: 100% !important;
    min-height: 48px;
    font-size: 16px; /* prevent iOS zoom */
    border-radius: 999px;
    padding-left: 16px;
    padding-right: 16px;
  }

  /* Action buttons in a compact row */
  #lbChatForm .lb-chat-actions{
    display: flex !important;
    flex: 0 0 auto !important;
    gap: 10px !important;
    justify-content: flex-end !important;
    align-items: center !important;
    margin: 0 !important;
  }

  #lbChatForm .btn-chat-icon{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    flex: 0 0 auto;
  }
}
  .lb-chat-attach-preview{
    display:flex;
    align-items:center;
    gap:12px;
    padding:10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
  }

  /* Chat policy warning (off-platform contact/payment) */
  .lb-chat-policy-warn{
    display:flex;
    align-items:flex-start;
    gap:.75rem;
    padding:.75rem .9rem;
    border-radius: 14px;
    background: rgba(255, 193, 7, .10);
    border: 1px solid rgba(255, 193, 7, .22);
  }
  .lb-chat-policy-warn__icon{
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    border-radius:10px;
    background: rgba(255, 193, 7, .14);
    color: rgba(255, 193, 7, .95);
    flex:0 0 auto;
  }
  .lb-chat-policy-warn__title{
    font-weight:800;
    font-size:.95rem;
    line-height:1.1;
  }
  .lb-chat-policy-warn__text{
    margin-top:.15rem;
    color: rgba(255,255,255,.82);
    font-size:.88rem;
    line-height:1.25;
  }
  .lb-chat-policy-warn__matches{
    display:inline-block;
    margin-left:.35rem;
    font-weight:800;
    color: rgba(255,255,255,.92);
  }
  
  .lb-chat-policy-warn__preview{
    color: rgba(255,255,255,.78);
  }

  /*
    Mobile: show policy warning as a toast overlay (must NOT push/shift the chat buttons).
    - We keep the markup in the form for a11y, but remove it from normal flow on xs.
  */
  @media (max-width: 575.98px){
    /* Hide policy warning completely on mobile */
    #lbChatPolicyWarn{ display: none !important; }
    #lbChatPolicyWarnPreview{ display: none !important; }
  }
  .lb-chat-policy-warn__chips{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:6px;
  }
  .lb-chat-policy-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:3px 8px;
    border-radius:999px;
    font-size:.78rem;
    line-height:1;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.88);
    white-space:nowrap;
  }
  .lb-chat-policy-chip[data-sev="high"]{
    background: rgba(220, 53, 69, .14);
    border-color: rgba(220, 53, 69, .22);
  }
  .lb-chat-policy-chip[data-sev="med"]{
    background: rgba(255, 193, 7, .12);
    border-color: rgba(255, 193, 7, .22);
  }
  .lb-chat-policy-mark{
    padding: 0 .18em;
    border-radius: .25em;
    background: rgba(255, 193, 7, .18);
    border: 1px solid rgba(255, 193, 7, .22);
  }
.lb-chat-attach-preview__thumb img{
    width:42px;
    height:42px;
    border-radius:10px;
    object-fit:cover;
    display:block;
  }
  .lb-chat-attach-preview__title{ font-weight: 600; font-size: 13px; }
  .lb-chat-attach-preview__name{ font-size: 12px; opacity: .8; }
  .lb-chat-attach-preview__remove{
    margin-left:auto;
    border:0;
    background:transparent;
    color: inherit;
    width:32px;
    height:32px;
    border-radius:10px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
  }
  .lb-chat-attach-preview__remove:hover{ background: rgba(255,255,255,.06); }

  /* Chat images in history should not be huge */
  /* Works even if the <img> has no special class */
  #chat_messages img:not(.lb-msg__avatar):not(.lb-chat-sticker),
  .lb-msg__content img:not(.lb-msg__avatar):not(.lb-chat-sticker),
  #chat_messages img.lb-chat-img,
  .lb-msg__content img.lb-chat-img{
    max-width: 320px;
    max-height: 320px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 12px;
    display:block;
    cursor: pointer;
  }

  /* Optional: slight zoom on hover for non-touch devices */
  @media (hover:hover){
    #chat_messages img:not(.lb-msg__avatar):hover,
    .lb-msg__content img:not(.lb-msg__avatar):hover{
      transform: scale(1.02);
      transition: transform .15s ease;
    }
  }

/* Avatars must stay small */
  #chat_messages img.lb-msg__avatar,
  .lb-msg__content img.lb-msg__avatar{
    width: 36px !important;
    height: 36px !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: contain;
  }


/* Emoji Picker (Order Chat) */
.lb-chat-form{ position: relative; }
.lb-emoji-picker{
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

.lb-emoji-picker__head{
  padding: 10px 10px 8px 10px;
  border-bottom: 1px solid rgba(255,255,255,.08);
}

.lb-emoji-picker__search{
  width: 100%;
  height: 34px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.06);
  color: rgba(255,255,255,.92);
  padding: 0 10px;
  outline: none;
}

/* Dashboard-style policy confirm modal */
#lbPolicyConfirmModal .modal-content.lb-modal-surface{
  background: rgba(18, 20, 26, .98);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 18px;
  box-shadow: 0 24px 80px rgba(0,0,0,.55);
  backdrop-filter: blur(10px);
}
#lbPolicyConfirmModal .modal-dialog{
  max-width: 560px;
}
#lbPolicyConfirmModal .modal-header{
  padding: 18px 18px 0 18px;
}
#lbPolicyConfirmModal .modal-body{
  padding: 14px 18px 6px 18px;
}
#lbPolicyConfirmModal .modal-footer{
  padding: 0 18px 18px 18px;
  gap: 10px;
}
#lbPolicyConfirmModal .btn.btn-icon{
  width: 38px;
  height: 38px;
  border-radius: 12px;
}
#lbPolicyConfirmModal .btn{
  border-radius: 14px;
}
.lb-modal-icon{
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(255, 193, 7, .14);
  border: 1px solid rgba(255, 193, 7, .22);
  color: rgba(255, 193, 7, .95);
  flex: 0 0 auto;
}
.lb-modal-title{
  font-size: 1.05rem;
  font-weight: 700;
  color: rgba(255,255,255,.92);
  line-height: 1.1;
}
.lb-modal-sub{
  margin-top: .15rem;
  font-size: .875rem;
  color: rgba(255,255,255,.62);
}
.lb-modal-note{
  padding: .85rem .95rem;
  border-radius: 14px;
  background: rgba(255,255,255,.04);
  border: 1px dashed rgba(255,255,255,.12);
  color: rgba(255,255,255,.78);
}
.lb-policy-chip{
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .38rem .65rem;
  border-radius: 999px;
  font-size: .8rem;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.86);
  margin: .15rem .25rem 0 0;
  user-select: none;
  white-space: nowrap;
}
.lb-policy-chip[data-sev="high"]{
  background: rgba(220,53,69,.12);
  border-color: rgba(220,53,69,.25);
}
.lb-policy-chip[data-sev="med"]{
  background: rgba(255,193,7,.10);
  border-color: rgba(255,193,7,.22);
}
.lb-policy-chip[data-sev="low"]{
  background: rgba(13,202,240,.10);
  border-color: rgba(13,202,240,.22);
}

.lb-emoji-picker__search::placeholder{
  color: rgba(255,255,255,.45);
}

.lb-emoji-picker__tabs{
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 10px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  overflow-x: auto;
  scrollbar-width: none;
}
.lb-emoji-picker__tabs::-webkit-scrollbar{ display:none; }

.lb-emoji-picker__tab{
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

.lb-emoji-picker__tab:hover{
  background: rgba(255,255,255,.10);
}

.lb-emoji-picker__tab.is-active{
  border-color: rgba(124,92,255,.55);
  box-shadow: 0 0 0 3px rgba(124,92,255,.18) inset;
  background: rgba(124,92,255,.12);
}

.lb-emoji-picker__grid{
  padding: 10px;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 6px;
  max-height: 260px;
  overflow: auto;
}

.lb-emoji{
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

.lb-emoji:hover{
  background: rgba(255,255,255,.12);
  transform: translateY(-1px);
}

.lb-emoji-picker__empty{
  padding: 14px;
  color: rgba(255,255,255,.65);
  font-size: .95rem;
}

/* hide emoji picker controls on mobile */
@media (max-width: 767.98px){
  #lbEmojiBtn{display:none !important;}
  #lbEmojiPicker{display:none !important;}
}




  /* =========================
     ORDER ACTIONS CARD (client-like)
  ========================= */
  .lb-actions-card {
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
  }

  [data-theme="light"] .lb-actions-card {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }

  .lb-actions-list {
    display: flex;
    flex-direction: column;
  }

  .lb-action-item {
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
    text-decoration: none;
  }

  .lb-action-item:first-child { border-top: 0; }

  .lb-action-item:hover {
    background: rgba(255, 255, 255, .05);
    transform: translateY(-1px);
    text-decoration: none;
  }

  [data-theme="light"] .lb-action-item { border-top-color: rgba(0, 0, 0, .06); }
  [data-theme="light"] .lb-action-item:hover { background: rgba(0, 0, 0, .03); }

  .lb-action-ico {
    width: 44px;
    height: 44px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    flex: 0 0 auto;
    font-size: 1.05rem;
  }

  [data-theme="light"] .lb-action-ico {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
  }

  .lb-action-txt { min-width: 0; flex: 1; }

  .lb-action-title {
    display: block;
    font-weight: 950;
    font-size: .98rem;
    line-height: 1.15;
  }

  .lb-action-sub {
    display: block;
    margin-top: .18rem;
    font-size: .82rem;
    opacity: .70;
  }

  .lb-action-go {
    opacity: .55;
    flex: 0 0 auto;
    transition: .15s ease;
  }

  .lb-action-item:hover .lb-action-go {
    opacity: .9;
    transform: translateX(1px);
  }

  .lb-action-item--danger .lb-action-title { color: #ffb1b1; }
  .lb-action-item--danger .lb-action-ico {
    background: rgba(255, 107, 107, .10);
    border-color: rgba(255, 107, 107, .22);
    color: #ffb1b1;
  }

  .lb-action-item--disabled {
    opacity: .65;
    pointer-events: none;
    transform: none !important;
  }


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
  .lb-dt-btn-row{display:flex;gap:8px;}
  .lb-dt-btn{flex:1;padding:9px 0;border-radius:8px;font-size:.78rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;border:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity .15s;}
  .lb-dt-btn:hover{opacity:.82;}
  .lb-dt-btn-pause{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12) !important;color:rgba(255,255,255,.85);}
  .lb-dt-btn-complete{background:linear-gradient(135deg,#00b894,#00cec9);color:#fff;flex:2;}
  @media (max-width:575.98px){.lb-dt-countdown{font-size:1.9rem;}}



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

  .lb-op-no-riot {
    font-size: .79rem;
    padding: 9px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .07);
    opacity: .60;
  }

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
    background: rgba(255, 255, 255, .07);
    border: 1px solid rgba(255, 255, 255, .12);
    color: rgba(255, 255, 255, .75);
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

  /* ── Duo Account card (Order Progress widget) ── */
  .lb-op-duo-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(109,92,255,.2);
    background: rgba(109,92,255,.06);
    cursor: pointer;
    color: inherit;
    transition: border-color .15s, background .15s;
  }
  .lb-op-duo-card:hover { border-color: rgba(109,92,255,.4); background: rgba(109,92,255,.10); }
  .lb-op-duo-card--set { border-color: rgba(74,222,128,.22); background: rgba(74,222,128,.05); }
  .lb-op-duo-card--set:hover { border-color: rgba(74,222,128,.42); background: rgba(74,222,128,.09); }
  .lb-op-duo-card__left { display: flex; align-items: center; gap: 11px; min-width: 0; }
  .lb-op-duo-card__icon {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    background: rgba(109,92,255,.15); border: 1px solid rgba(109,92,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem; color: #a78bfa; transition: all .15s;
  }
  .lb-op-duo-card--set .lb-op-duo-card__icon { background: rgba(74,222,128,.10); border-color: rgba(74,222,128,.25); color: #4ade80; }
  .lb-op-duo-card__body { min-width: 0; }
  .lb-op-duo-card__title { font-size: .78rem; font-weight: 800; margin-bottom: 3px; }
  .lb-op-duo-card__sub { display: flex; align-items: center; gap: 5px; font-size: .72rem; font-weight: 600; flex-wrap: wrap; }
  .lb-op-duo-card__sub--set { color: #4ade80; }
  .lb-op-duo-card__sub--unset { color: rgba(255,255,255,.38); }
  .lb-op-duo-card__tag { font-size: .67rem; opacity: .55; }
  .lb-op-duo-card__arrow { font-size: .68rem; opacity: .35; flex-shrink: 0; transition: transform .15s; }
  .lb-op-duo-card:hover .lb-op-duo-card__arrow { transform: translateX(3px); }

  /* ── Duo Account custom modal overlay ── */
  .lbd-overlay {
    position: fixed; inset: 0; z-index: 1060;
    display: none; align-items: center; justify-content: center;
    padding: 16px;
    opacity: 0; transition: opacity .2s ease;
  }
  .lbd-overlay--visible { opacity: 1; }
  .lbd-backdrop {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.72);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
  }
  .lbd-dialog {
    position: relative; z-index: 1;
    width: 100%; max-width: 560px;
    background: #1e2022;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 32px 96px rgba(0,0,0,.75), 0 0 0 1px rgba(255,255,255,.04);
    transform: translateY(12px) scale(.98);
    transition: transform .22s cubic-bezier(.34,1.28,.64,1);
    color: rgba(255,255,255,.92);
  }
  .lbd-overlay--visible .lbd-dialog { transform: translateY(0) scale(1); }

  /* Header */
  .lbd-header {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    padding: 18px 18px 14px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.025);
  }
  .lbd-header__left { display: flex; gap: 13px; align-items: flex-start; }
  .lbd-header__icon {
    width: 44px; height: 44px; border-radius: 14px; flex-shrink: 0;
    background: rgba(109,92,255,.15); border: 1px solid rgba(109,92,255,.25);
    display: grid; place-items: center; font-size: 1.1rem; color: #a78bfa;
  }
  .lbd-header__title { font-size: 1.15rem; font-weight: 900; line-height: 1.2; }
  .lbd-header__sub { margin-top: 5px; font-size: .86rem; opacity: .6; }
  .lbd-close {
    width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.7); display: grid; place-items: center;
    cursor: pointer; transition: background .12s, color .12s; font-size: .9rem;
  }
  .lbd-close:hover { background: rgba(255,255,255,.09); color: #fff; }

  /* Body */
  .lbd-body { padding: 16px 18px; display: flex; flex-direction: column; gap: 0; }

  /* Account rows */
  .lbd-account-row {
    border-radius: 14px; padding: 13px 15px;
    border: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.03);
  }
  .lbd-account-row--booster {
    border-color: rgba(109,92,255,.22);
    background: rgba(109,92,255,.05);
  }
  .lbd-account-row__label {
    display: flex; align-items: center; gap: 7px;
    font-size: .72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .07em; opacity: .55; margin-bottom: 8px;
  }
  .lbd-badge {
    font-size: .65rem; font-weight: 700; text-transform: none; letter-spacing: .02em;
    background: rgba(109,92,255,.2); border: 1px solid rgba(109,92,255,.35);
    color: #a78bfa; border-radius: 999px; padding: 2px 9px; opacity: 1;
  }
  .lbd-account-row__value {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    font-size: .92rem; font-weight: 700; opacity: .7; padding: 2px 0;
  }
  .lbd-account-row__tag { font-size: .70rem; opacity: .5; font-weight: 600; white-space: nowrap; }
  .lbd-sep {
    display: flex; align-items: center; justify-content: center;
    padding: 6px 0; color: rgba(255,255,255,.18); font-size: .78rem;
  }

  /* Input */
  .lbd-account-row__field { }
  .lbd-input {
    width: 100%; min-height: 46px; border-radius: 13px; padding: 11px 14px;
    background: rgba(0,0,0,.22); border: 1px solid rgba(255,255,255,.11);
    color: rgba(255,255,255,.92); outline: none; font-size: .9rem;
    transition: border-color .15s, box-shadow .15s;
  }
  .lbd-input::placeholder { opacity: .45; }
  .lbd-input:focus {
    border-color: rgba(167,139,250,.4);
    box-shadow: 0 0 0 3px rgba(167,139,250,.12);
  }
  .lbd-note {
    display: flex; gap: 7px; align-items: flex-start; margin-top: 9px;
    padding: 9px 12px; border-radius: 12px;
    background: rgba(255,196,77,.08); border: 1px solid rgba(255,196,77,.18);
    font-size: .80rem; color: rgba(255,255,255,.80); line-height: 1.4;
  }
  .lbd-note i { color: #ffc44d; margin-top: 1px; flex-shrink: 0; }
  .lbd-error {
    margin-top: 7px; color: #ff6b6b; font-size: .80rem;
    font-weight: 800; min-height: 16px; display: none;
  }

  /* Preview */
  .lbd-preview {
    display: flex; gap: 12px; align-items: center;
    margin-top: 11px; padding: 12px 13px; border-radius: 14px;
    background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09);
  }
  .lbd-preview[hidden] { display: none !important; }
  .lbd-preview.is-found { border-color: rgba(31,230,198,.25); background: rgba(31,230,198,.07); }
  .lbd-preview.is-error { border-color: rgba(255,107,107,.22); background: rgba(255,107,107,.07); }
  .lbd-preview__avatar {
    width: 50px; height: 50px; border-radius: 50%; flex-shrink: 0;
    background: rgba(0,0,0,.2); border: 2px solid rgba(255,255,255,.13);
    overflow: hidden; display: grid; place-items: center;
  }
  .lbd-preview__avatar img { width: 100%; height: 100%; object-fit: cover; }
  .lbd-preview__avatar i { color: rgba(255,255,255,.5); font-size: 1rem; }
  .lbd-preview__body { min-width: 0; }
  .lbd-preview__label { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 900; opacity: .5; }
  .lbd-preview__name { margin-top: 2px; font-weight: 900; font-size: .95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lbd-preview__meta { margin-top: 2px; font-size: .80rem; opacity: .65; }
  .lbd-confirm-btn {
    margin-top: 9px; display: inline-flex; align-items: center; gap: 5px;
    border: 0; border-radius: 999px; padding: 7px 14px;
    font-size: .78rem; font-weight: 900; color: #061e1a;
    background: linear-gradient(135deg, #1fe6c6, #7ef5e0);
    box-shadow: 0 8px 20px rgba(31,230,198,.18); cursor: pointer;
  }
  .lbd-confirm-btn[hidden] { display: none !important; }

  /* Currently tracking bar */
  .lbd-current {
    display: flex; align-items: center; gap: 8px; margin-top: 12px;
    background: rgba(74,222,128,.06); border: 1px solid rgba(74,222,128,.18);
    border-radius: 12px; padding: 10px 14px;
    font-size: .82rem; color: rgba(255,255,255,.75);
  }
  .lbd-current i { color: #4ade80; }
  .lbd-remove-btn {
    margin-left: auto; background: rgba(251,113,133,.08);
    border: 1px solid rgba(251,113,133,.2); color: #fb7185;
    border-radius: 8px; font-size: .74rem; font-weight: 700;
    padding: 4px 10px; cursor: pointer; transition: background .12s;
    display: inline-flex; align-items: center; gap: 4px;
  }
  .lbd-remove-btn:hover { background: rgba(251,113,133,.15); }

  /* Footer */
  .lbd-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 18px;
    border-top: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.02);
  }
  .lbd-btn {
    border-radius: 999px; padding: .58rem 1.1rem;
    font-weight: 900; font-size: .88rem; cursor: pointer;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.05); color: rgba(255,255,255,.9);
    transition: background .12s, border-color .12s;
  }
  .lbd-btn:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: #fff; }
  .lbd-btn--ghost { background: transparent; }
  .lbd-btn--primary {
    background: rgba(88,101,242,.85); border-color: transparent; color: #fff;
    box-shadow: 0 4px 16px rgba(88,101,242,.25);
  }
  .lbd-btn--primary:hover { background: rgba(88,101,242,1); }
  .lbd-btn:disabled, .lbd-btn[disabled] { opacity: .35; pointer-events: none; }


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

  [data-theme="light"] .lb-op-no-riot {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-view-history {
    border-top-color: rgba(0, 0, 0, .07);
  }

  [data-theme="light"] .lb-op-history-count {
    background: rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .12);
    color: rgba(0, 0, 0, .55);
  }

  /* =========================
     MATCH HISTORY MODAL (booster)
  ========================= */
  .lb-mh-modal .modal-dialog {
    --bs-modal-width: min(1150px, 98vw);
    max-width: min(1150px, 98vw) !important;
  }

  .lb-mh-list {
    transition: opacity .15s;
  }

  .lb-mh-modal .modal-content {
    border: 1px solid rgba(255, 255, 255, .08);
    background: #1f2226;
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
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, .75);
    font-size: .90rem;
    flex: 0 0 auto;
  }

  .lb-mh-list {
    width: 100%;
  }

  .lb-mh-list-head {
    display: grid;
    grid-template-columns: 82px minmax(145px,1fr) minmax(115px,.75fr) 88px 78px 100px 72px 180px 96px;
    align-items: center;
    padding: 7px 20px 7px 23px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(0, 0, 0, .20);
  }

  .lb-mh-list-head span {
    font-size: .66rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    opacity: .32;
    white-space: nowrap;
  }

  .lb-mh-row {
    display: grid;
    grid-template-columns: 82px minmax(145px,1fr) minmax(115px,.75fr) 88px 78px 100px 72px 180px 96px;
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
    border-left-color: rgba(56, 189, 248, .70);
    background: rgba(56, 189, 248, .035);
  }

  .lb-mh-row:hover {
    filter: brightness(1.09);
  }

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
    background: rgba(56, 189, 248, .12);
    border: 1px solid rgba(56, 189, 248, .24);
  }

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
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .65);
    font-size: .70rem;
  }


  .lb-mh-booster-ico--img {
    overflow: hidden;
    padding: 0;
    background: rgba(0, 0, 0, .28);
  }
  .lb-mh-booster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .lb-mh-rank-inner {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
  }
  .lb-mh-rank-ico {
    width: 22px;
    height: 22px;
    object-fit: contain;
    flex: 0 0 auto;
  }

  .lb-mh-booster-info { min-width: 0; }

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
    min-width: 0;
  }

  .lb-mh-rank-name {
    font-size: .74rem;
    font-weight: 800;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
  }

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

  .lb-mh-placeholder {
    text-align: center;
    padding: 44px 20px;
    opacity: .42;
    font-weight: 600;
    font-size: .82rem;
  }

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

  [data-theme="light"] .lb-mh-modal .modal-content {
    border-color: rgba(0, 0, 0, .10);
  }

  [data-theme="light"] .lb-mh-modal .modal-header {
    border-bottom-color: rgba(0, 0, 0, .07);
    background: rgba(0, 0, 0, .02);
  }

  [data-theme="light"] .lb-mh-header-ico {
    background: rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .12);
    color: rgba(0, 0, 0, .55);
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

</style>
<?= $this->end() ?>
<script id="lbBoosterEmojiPickerJS">
// Desktop emoji picker for order chat (Booster Orders View version - hidden on mobile)
document.addEventListener('DOMContentLoaded', function () {
  if (window.matchMedia('(max-width: 767.98px)').matches) return;

  const btn = document.getElementById('lbEmojiBtn');
  const picker = document.getElementById('lbEmojiPicker');
  const grid = document.getElementById('lbEmojiGrid');
  const search = document.getElementById('lbEmojiSearch');
  const tabs = document.getElementById('lbEmojiTabs');
  const input = document.getElementById('lbChatMessage');

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

</script>



<script>
/**
 * Poke/Notify cooldown: lock "Notify Client" for 5 minutes after click
 * Applies to all elements with data-action="poke_client" (header button, mobile dropdown, actions card).
 */
document.addEventListener('DOMContentLoaded', function () {
  const ORDER_ID = <?= (int) $data['id'] ?>;
  const COOLDOWN_MS = 5 * 60 * 1000;
  const LS_KEY = 'lb_poke_cooldown_until_' + ORDER_ID;

  function now() { return Date.now(); }

  function getUntil() {
    try {
      const v = localStorage.getItem(LS_KEY);
      const n = parseInt(v || '0', 10);
      return isFinite(n) ? n : 0;
    } catch (e) {
      return 0;
    }
  }

  function setUntil(ts) {
    try { localStorage.setItem(LS_KEY, String(ts)); } catch (e) {}
  }

  function clearUntil() {
    try { localStorage.removeItem(LS_KEY); } catch (e) {}
  }

  function remainingMs() {
    const until = getUntil();
    const rem = until - now();
    return rem > 0 ? rem : 0;
  }

  function fmtMMSS(ms) {
    const total = Math.ceil(ms / 1000);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return String(m).padStart(1,'0') + ':' + String(s).padStart(2,'0');
  }

  function toast(type, title, message) {
    try {
      if (typeof window.create_toast === 'function') return window.create_toast(type, title, message);
      if (typeof window.sendToast === 'function') return window.sendToast({ type, title, message });
      console.log('[Toast]', type, title, message);
    } catch (e) {}
  }

  function setDisabled(el, disabled, label) {
    if (!el) return;

    // Save originals once
    if (!el.dataset.lbOrigHtml) el.dataset.lbOrigHtml = el.innerHTML;
    if (!el.dataset.lbOrigAria) el.dataset.lbOrigAria = el.getAttribute('aria-disabled') || '';
    if (!el.dataset.lbOrigHref) el.dataset.lbOrigHref = el.getAttribute('href') || '';

    const isBtn = el.tagName === 'BUTTON';
    if (disabled) {
      el.classList.add('disabled', 'lb-cooldown-disabled');
      el.setAttribute('aria-disabled', 'true');
      if (isBtn) el.disabled = true;
      // prevent navigation/anchor jumping
      if (el.tagName === 'A') el.setAttribute('href', '#');
      // Update text in known structures
      const t1 = el.querySelector('.lb-notify-text');
      const t2 = el.querySelector('.lb-action-title');
      if (t1) t1.textContent = label;
      else if (t2) t2.textContent = label;
      else el.innerHTML = label;
      el.style.pointerEvents = 'none';
      el.style.opacity = '0.7';
    } else {
      el.classList.remove('disabled', 'lb-cooldown-disabled');
      if (el.dataset.lbOrigAria !== '') el.setAttribute('aria-disabled', el.dataset.lbOrigAria);
      else el.removeAttribute('aria-disabled');
      if (isBtn) el.disabled = false;
      if (el.tagName === 'A') el.setAttribute('href', el.dataset.lbOrigHref || '#');
      el.innerHTML = el.dataset.lbOrigHtml || el.innerHTML;
      el.style.pointerEvents = '';
      el.style.opacity = '';
    }
  }

  function applyState() {
    const rem = remainingMs();
    const locked = rem > 0;
    const label = locked ? ('Notify Client (' + fmtMMSS(rem) + ')') : 'Notify Client';

    // All poke_client triggers
    document.querySelectorAll('[data-action="poke_client"]').forEach(function (el) {
      setDisabled(el, locked, label);
    });

    // Extra: in case there are dedicated notify buttons without data-action (shouldn't happen)
    document.querySelectorAll('.lb-notify-btn[data-action="poke_client"]').forEach(function (el) {
      setDisabled(el, locked, label);
    });

    if (!locked) clearInterval(window.__lbPokeCooldownTimer);
  }

  // Tick countdown while locked
  function ensureTimer() {
    if (window.__lbPokeCooldownTimer) return;
    window.__lbPokeCooldownTimer = setInterval(function () {
      const rem = remainingMs();
      if (rem <= 0) {
        clearInterval(window.__lbPokeCooldownTimer);
        window.__lbPokeCooldownTimer = null;
        clearUntil();
      }
      applyState();
    }, 1000);
  }

  // Intercept click early so even external handlers won't fire while locked
  document.addEventListener('click', function (e) {
    const trigger = e.target && e.target.closest ? e.target.closest('[data-action="poke_client"]') : null;
    if (!trigger) return;

    const rem = remainingMs();
    if (rem > 0) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      toast('info', 'Please wait', 'You can notify the client again in ' + fmtMMSS(rem) + '.');
      applyState();
      ensureTimer();
      return;
    }

    // Start cooldown immediately (prevents spam-clicking)
    setUntil(now() + COOLDOWN_MS);
    applyState();
    ensureTimer();
    // allow the original action handler to continue
  }, true);

  // Initial state on load (in case user refreshed mid-cooldown)
  applyState();
  if (remainingMs() > 0) ensureTimer();
});
</script>

<script id="lbOrderActionsDesktopToggleJS">
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('lbOrderActionsBtn');
  var cardWrap = document.getElementById('lbOrderActionsCardWrap');
  var ddMenu = document.getElementById('lbOrderActionsDropdown');
  if (!btn || !cardWrap || !ddMenu || !window.bootstrap) return;

  // Ensure dropdown works even without data-bs-toggle attribute.
  var dd = bootstrap.Dropdown.getOrCreateInstance(btn, { autoClose: 'outside' });
  var collapse = bootstrap.Collapse.getOrCreateInstance(cardWrap, { toggle: false });

  btn.addEventListener('click', function (e) {
    // Keep button behavior: open dropdown like before + toggle the card
    e.preventDefault();
    dd.toggle();
    collapse.toggle();
  });

  // Close dropdown if any action was clicked
  ddMenu.addEventListener('click', function (e) {
    var item = e.target && e.target.closest ? e.target.closest('.dropdown-item') : null;
    if (!item) return;
    // If it opens a modal, let bootstrap handle; we just close dropdown.
    dd.hide();
  });
});
</script>

<style>
  /* small helper class for visual consistency */
  .lb-cooldown-disabled { cursor: not-allowed !important; }

  /* ── Discord Banner ── */
  .lb-discord-banner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    background: linear-gradient(180deg, rgba(88,101,242,.12) 0%, rgba(30,32,53,.96) 100%);
    border: 1px solid rgba(88,101,242,.35);
    border-radius: .9rem;
    padding: 1rem 1.1rem;
  }
  .lb-discord-banner__left {
    display: flex;
    align-items: flex-start;
    gap: .8rem;
    min-width: 0;
    flex: 1 1 auto;
  }
  .lb-discord-banner__logo {
    width: 30px;
    height: 22px;
    flex-shrink: 0;
    margin-top: .2rem;
  }
  .lb-discord-banner__text {
    display: flex;
    flex-direction: column;
    gap: .55rem;
    min-width: 0;
    flex: 1 1 auto;
  }
  .lb-discord-banner__text strong {
    font-size: .92rem;
    font-weight: 700;
    color: rgba(235,242,255,.97);
  }
  .lb-discord-banner__steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: .45rem;
  }
  .lb-discord-banner__step {
    display: flex;
    align-items: flex-start;
    gap: .55rem;
    font-size: .79rem;
    line-height: 1.45;
    color: rgba(235,242,255,.8);
  }
  .lb-discord-banner__step-no {
    width: 1.2rem;
    height: 1.2rem;
    min-width: 1.2rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(88,101,242,.22);
    border: 1px solid rgba(159,176,255,.22);
    color: #dfe5ff;
    font-size: .68rem;
    font-weight: 700;
    margin-top: .05rem;
  }
  .lb-discord-banner__step--warn {
    color: #ffcece;
  }
  .lb-discord-banner__step--warn .lb-discord-banner__step-no {
    background: rgba(255,107,107,.14);
    border-color: rgba(255,107,107,.28);
    color: #ffd7d7;
  }
  .lb-discord-banner__step a {
    color: #a9b8ff;
    text-decoration: none;
    font-weight: 600;
  }
  .lb-discord-banner__step a:hover {
    text-decoration: underline;
  }
  .lb-discord-banner__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    background: #5865f2;
    color: #fff !important;
    font-size: .8rem;
    font-weight: 700;
    padding: .62rem 1rem;
    border-radius: .65rem;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .15s;
    align-self: center;
  }
  .lb-discord-banner__btn:hover { background: #4752c4; color: #fff !important; }
  [data-theme="light"] .lb-discord-banner {
    background: linear-gradient(180deg, rgba(88,101,242,.08) 0%, #f4f6ff 100%);
    border-color: rgba(88,101,242,.24);
  }
  [data-theme="light"] .lb-discord-banner__text strong { color: #1e2340; }
  [data-theme="light"] .lb-discord-banner__step { color: rgba(30,35,64,.78); }
  [data-theme="light"] .lb-discord-banner__step--warn { color: #b13a3a; }
  @media (max-width: 767.98px) {
    .lb-discord-banner {
      flex-direction: column;
      align-items: stretch;
    }
    .lb-discord-banner__btn {
      width: 100%;
    }
  }
  [data-theme="light"] .lb-discord-banner__text strong { color: #111; }
  [data-theme="light"] .lb-discord-banner__step { color: #555; }
  [data-theme="light"] .lb-discord-banner__step strong { color: #222; }
  [data-theme="light"] .lb-discord-banner__step a { color: #4457d6; }
  @media (max-width: 991.98px) {
    .lb-discord-banner {
      flex-direction: column;
      align-items: stretch;
    }
    .lb-discord-banner__btn {
      width: 100%;
      justify-content: center;
    }
  }


  #boosterRiotIdModal .lbx-modal__content {
    background: #25282A !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255, 255, 255, .92) !important;
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
  }

  #boosterRiotIdModal .lbx-modal__header {
    padding: 16px 16px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  #boosterRiotIdModal .lbx-modal__headLeft {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  #boosterRiotIdModal .lbx-modal__icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
  }

  #boosterRiotIdModal .lbx-modal__title {
    font-weight: 900;
    font-size: 1.25rem;
    line-height: 1.15;
  }

  #boosterRiotIdModal .lbx-modal__sub {
    margin-top: 6px;
    opacity: .72;
    font-size: .92rem;
  }

  #boosterRiotIdModal .lbx-modal__close {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
  }

  #boosterRiotIdModal .lbx-modal__close:hover {
    background: rgba(255, 255, 255, .07);
  }

  #boosterRiotIdModal .lbx-modal__body {
    padding: 16px;
  }

  #boosterRiotIdModal .lb-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }

  #boosterRiotIdModal .lb-field {
    margin-bottom: 14px;
  }

  #boosterRiotIdModal .lb-field-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 900;
    font-size: .85rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    opacity: .85;
    margin-bottom: 8px;
  }

  #boosterRiotIdModal .lb-field-label .lb-ico {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
  }

  #boosterRiotIdModal .lb-input {
    width: 100%;
    min-height: 48px;
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    outline: none;
  }

  #boosterRiotIdModal .lb-input::placeholder {
    opacity: .55;
  }

  #boosterRiotIdModal .lb-input:focus {
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15);
  }

  #boosterRiotIdModal .lb-riot-format-note {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(255, 196, 77, .10);
    border: 1px solid rgba(255, 196, 77, .20);
    color: rgba(255,255,255,.88);
    font-size: .86rem;
    line-height: 1.35;
  }

  #boosterRiotIdModal .lb-riot-format-note i {
    color: #ffc44d;
    margin-top: 2px;
  }

  #boosterRiotIdModal .lb-riot-inline-error {
    margin-top: 8px;
    color: #ff6b6b;
    font-size: .82rem;
    font-weight: 800;
    min-height: 18px;
  }

  #boosterRiotIdModal .lb-input.is-invalid,
  #boosterRiotIdModal .is-invalid {
    border-color: rgba(255, 107, 107, .55) !important;
    box-shadow: 0 0 0 .22rem rgba(255, 107, 107, .12) !important;
  }

  #boosterRiotIdModal .lb-riot-preview {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-top: 12px;
    padding: 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.09);
  }

  #boosterRiotIdModal .lb-riot-preview[hidden] { display: none !important; }

  #boosterRiotIdModal .lb-riot-preview__avatar {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    overflow: hidden;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    background: rgba(0,0,0,.20);
    border: 2px solid rgba(255,255,255,.14);
  }

  #boosterRiotIdModal .lb-riot-preview__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  #boosterRiotIdModal .lb-riot-preview__avatar i {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    color: rgba(255,255,255,.55);
  }

  #boosterRiotIdModal .lb-riot-preview__body { min-width: 0; }
  #boosterRiotIdModal .lb-riot-preview__label { font-size: .70rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 900; opacity: .55; }
  #boosterRiotIdModal .lb-riot-preview__name { margin-top: 2px; font-weight: 950; font-size: .98rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  #boosterRiotIdModal .lb-riot-preview__meta { margin-top: 2px; font-size: .82rem; opacity: .70; }
  #boosterRiotIdModal .lb-riot-preview.is-found { border-color: rgba(31,230,198,.25); background: rgba(31,230,198,.07); }
  #boosterRiotIdModal .lb-riot-preview.is-error { border-color: rgba(255,107,107,.24); background: rgba(255,107,107,.08); }
  #boosterRiotIdModal .lb-riot-preview.is-loading .lb-riot-preview__avatar { animation: lbRiotPulse 1s ease-in-out infinite; }
  #boosterRiotIdModal .lb-riot-confirm { margin-top: 10px; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 999px; padding: 8px 12px; font-size: .78rem; font-weight: 900; color: #061e1a; background: linear-gradient(135deg, #1fe6c6, #7ef5e0); box-shadow: 0 10px 24px rgba(31,230,198,.18); }
  #boosterRiotIdModal .lb-riot-confirm[hidden] { display: none !important; }
  #boosterRiotIdModal .lb-riot-confirm.is-confirmed { color: #d8fff8; background: rgba(31,230,198,.16); border: 1px solid rgba(31,230,198,.35); box-shadow: none; }

  @media(min-width:992px) {
    #boosterRiotIdModal .modal-dialog {
      max-width: 720px;
    }
  }

  #boosterRiotIdModal .lbx-modal__footer {
    padding: 14px 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .02);
  }

  #boosterRiotIdModal .lbx-modal__btn {
    border-radius: 999px;
    padding: .60rem 1.05rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  #boosterRiotIdModal .lbx-modal__btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
  }

  #boosterRiotIdModal .lbx-modal__btn--ghost {
    background: transparent;
  }

  #boosterRiotIdModal .lbx-modal__btn--primary {
    background: rgba(88, 101, 242, .85);
    border-color: rgba(88, 101, 242, .35);
  }

  #boosterRiotIdModal .lbx-modal__btn--primary:hover {
    background: rgba(88, 101, 242, .95);
  }

  #boosterRiotIdModal .lbx-modal__btn:disabled,
  #boosterRiotIdModal .lbx-modal__btn.is-disabled {
    opacity: .55;
    cursor: not-allowed;
  }

  @keyframes lbRiotPulse { 0%,100%{opacity:.65} 50%{opacity:1} }

  /* Win Boost target mode: every win reduces target, every loss adds one */
  .lb-op-count-row { display:flex; align-items:center; justify-content:center; gap:10px; padding:14px; background:rgba(0,0,0,.16); border:1px solid rgba(255,255,255,.06); border-radius:16px; margin-bottom:10px; }
  .lb-op-count-box { display:flex; flex-direction:column; align-items:center; gap:4px; flex:1; }
  .lb-op-count-val { font-size:2rem; font-weight:900; line-height:1; font-variant-numeric:tabular-nums; }
  .lb-op-count-val--target { font-size:1.4rem; opacity:.48; }
  .lb-op-count-label { font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; opacity:.40; }
  .lb-op-count-sep { font-size:1.6rem; font-weight:300; opacity:.22; flex:0 0 auto; }
  .lb-op-count-progress { height:7px; background:rgba(255,255,255,.07); border-radius:999px; overflow:hidden; margin-bottom:10px; }
  .lb-op-count-progress-fill { height:100%; border-radius:999px; background:rgba(99,102,241,.55); transition:width .5s ease; }
  .lb-op-count-progress-fill--done { background:linear-gradient(90deg, rgba(34,197,94,.65) 0%, rgba(74,222,128,.90) 100%); }
  .lb-op-count-rank { display:flex; align-items:center; gap:10px; padding:11px 12px; margin-bottom:8px; border-radius:13px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.06); }
  .lb-op-count-rank-img { width:42px; height:42px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(0,0,0,.35)); flex:0 0 auto; }
  .lb-op-count-rank-copy { min-width:0; display:flex; flex-direction:column; gap:2px; }
  .lb-op-count-rank-kicker { font-size:.64rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; opacity:.40; }
  .lb-op-count-rank-name { font-size:.82rem; font-weight:800; line-height:1.2; word-break:break-word; }
  [data-theme="light"] .lb-op-count-row { background:rgba(0,0,0,.04); border-color:rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-count-progress { background:rgba(0,0,0,.09); }
  [data-theme="light"] .lb-op-count-rank { background:rgba(0,0,0,.03); border-color:rgba(0,0,0,.07); }</style>




<script>
(function () {
  function initChampTooltips() {
    const triggers = document.querySelectorAll('.js-lb-champs-tooltip');
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
      if (trigger.dataset.lbTooltipReady === '1') return;
      trigger.dataset.lbTooltipReady = '1';
      trigger.setAttribute('tabindex', '0');
      trigger.addEventListener('mouseenter', function () { show(trigger); });
      trigger.addEventListener('mousemove', function () { place(trigger); });
      trigger.addEventListener('mouseleave', scheduleHide);
      trigger.addEventListener('focus', function () { show(trigger); });
      trigger.addEventListener('blur', scheduleHide);
    });

    if (tooltip.dataset.lbTooltipReady !== '1') {
      tooltip.dataset.lbTooltipReady = '1';
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
    }
  }

  window.lbInitChampTooltips = initChampTooltips;
  document.addEventListener('DOMContentLoaded', initChampTooltips);
})();
</script>


<!-- Policy confirm modal (dashboard style) -->
<div class="modal fade" id="lbPolicyConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content lb-modal-surface">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <div class="lb-modal-icon"><i class="fa-duotone fa-triangle-exclamation"></i></div>
          <div class="me-3">
            <div class="lb-modal-title">Keep everything on the website chat</div>
            <div class="lb-modal-sub" id="lbPolicyConfirmSubtitle"></div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-icon btn-active-light" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body pt-3">
        <div class="lb-modal-note" id="lbPolicyConfirmText"></div>
        <div class="mt-3" id="lbPolicyConfirmChips"></div>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-policy-cancel>Cancel</button>
        <button type="button" class="btn btn-primary" data-policy-send>Send anyway</button>
      </div>
    </div>
  </div>
</div>
